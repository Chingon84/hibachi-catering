<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\ScheduleAssignment;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\SchedulePriorityService;
use App\Support\ReservationTotals;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function __construct(private readonly SchedulePriorityService $priorityService)
    {
    }

    public function index(Request $request): View
    {
        $selectedDate = $this->selectedDate($request);
        $today = CarbonImmutable::today();
        $weekStart = $selectedDate->startOfWeek(CarbonImmutable::SUNDAY);
        $weekEnd = $selectedDate->endOfWeek(CarbonImmutable::SATURDAY);
        $dayCounts = Reservation::query()
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->selectRaw('date, count(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $weekDays = collect(range(0, 6))->map(function (int $offset) use ($weekStart, $dayCounts, $selectedDate, $today) {
            $date = $weekStart->addDays($offset);

            return [
                'date' => $date,
                'count' => (int) ($dayCounts->get($date->toDateString(), 0)),
                'is_selected' => $date->isSameDay($selectedDate),
                'is_today' => $date->isSameDay($today),
            ];
        });

        $reservations = Reservation::query()
            ->with([
                'scheduleAssignment.chef1:id,name,staff_type,role,is_active',
                'scheduleAssignment.chef2:id,name,staff_type,role,is_active',
                'scheduleAssignment.chef3:id,name,staff_type,role,is_active',
                'scheduleAssignment.chef4:id,name,staff_type,role,is_active',
                'scheduleAssignment.assistant:id,name,staff_type,role,is_active',
                'scheduleAssignment.confirmBy:id,name,staff_type,role,is_active',
                'staffEventConfirmations',
                'payments',
            ])
            ->whereDate('date', $selectedDate->toDateString())
            ->orderBy('time')
            ->orderBy('customer_name')
            ->get()
            ->map(function (Reservation $reservation) {
                $reservation->schedule_totals = ReservationTotals::compute($reservation);

                return $reservation;
            });

        $teamMembers = $this->activeTeamMembers();

        return view('admin.schedule.index', [
            'selectedDate' => $selectedDate,
            'today' => $today,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'previousWeekDate' => $selectedDate->subWeek(),
            'nextWeekDate' => $selectedDate->addWeek(),
            'weekDays' => $weekDays,
            'reservations' => $reservations,
            'teamMembers' => $teamMembers,
            'chefOptions' => $this->chefFirstOptions($teamMembers),
            'officeOptions' => $this->officeOptions($teamMembers),
            'weeklyStaffCounts' => $this->weeklyStaffCounts($weekStart, $weekEnd),
        ]);
    }

    public function assign(Request $request)
    {
        $eventId = $request->integer('event');
        $selectedEvent = $eventId ? $this->priorityService->eventDetail($eventId) : null;

        return view('admin.schedule.assign', [
            'events' => $this->priorityService->upcomingEvents(),
            'selectedEvent' => $selectedEvent,
            'recommendedChefs' => $this->priorityService->recommendedChefs($eventId),
            'highlightChefId' => $request->integer('chef'),
            'weekStart' => $this->priorityService->currentWeekStart(),
        ]);
    }

    public function storeAssignment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reservation_id' => ['required', 'integer', 'exists:reservations,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->priorityService->persistAssignment(
            (int) $validated['reservation_id'],
            (int) $validated['user_id'],
            (int) $request->user()->id,
            $validated['notes'] ?? null
        );

        $reservation = Reservation::query()->findOrFail((int) $validated['reservation_id']);
        app(NotificationService::class)->notifyReservationAssigned($reservation, (int) $validated['user_id'], $request->user());

        return redirect()
            ->route('admin.schedule.assign', ['event' => $validated['reservation_id']])
            ->with('ok', 'Chef assigned using the current priority ranking.');
    }

    public function updateAssignment(Request $request, Reservation $reservation): JsonResponse
    {
        $validatedField = $request->validate([
            'field' => ['required', 'string', Rule::in($this->assignmentFields())],
        ]);

        $field = $validatedField['field'];

        $assignment = ScheduleAssignment::query()->firstOrNew([
            'reservation_id' => $reservation->id,
        ]);
        $beforeAssignedUserIds = $this->assignmentUserIds($assignment);

        if (!$assignment->exists) {
            $assignment->created_by = $request->user()?->id;
            $assignment->week_start_date = $this->priorityService->currentWeekStart(CarbonImmutable::parse($reservation->date));
        }

        if ($field === 'extra_chef_ids') {
            if (is_array($request->input('value'))) {
                $validatedValue = $request->validate([
                    'value' => ['nullable', 'array'],
                    'value.*' => ['nullable', 'integer', 'exists:users,id'],
                ]);

                $extraChefIds = [];
                foreach (($validatedValue['value'] ?? []) as $chefNumber => $chefId) {
                    $chefNumber = (int) $chefNumber;
                    if ($chefNumber < 4 || $chefNumber > 27) {
                        continue;
                    }
                    $extraChefIds[(string) $chefNumber] = $chefId !== null && $chefId !== '' ? (int) $chefId : null;
                }
            } else {
                $validatedValue = $request->validate([
                    'chef_number' => ['required', 'integer', 'min:4', 'max:27'],
                    'value' => ['nullable', 'integer', 'exists:users,id'],
                ]);

                $chefNumber = (string) $validatedValue['chef_number'];
                $value = $validatedValue['value'] ?? null;
                $extraChefIds = (array) ($assignment->extra_chef_ids ?? []);
                $extraChefIds[$chefNumber] = $value !== null && $value !== '' ? (int) $value : null;
            }

            ksort($extraChefIds, SORT_NUMERIC);

            $assignment->extra_chef_ids = $extraChefIds;
            $assignment->chef_4_id = $extraChefIds['4'] ?? null;
        } else {
            $validatedValue = $request->validate([
                'value' => $this->assignmentRules($field),
            ]);

            $value = $validatedValue['value'] ?? null;
            if ($value === '') {
                $value = null;
            }
            if (in_array($field, ['chef_1_id', 'chef_2_id', 'chef_3_id', 'assistant_id', 'confirm_by_id'], true) && $value !== null) {
                $value = (int) $value;
            }
            if ($field === 'chef_tip' && $value !== null) {
                $value = round((float) $value, 2);
            }

            $assignment->{$field} = $value;
        }

        $assignment->updated_by = $request->user()?->id;
        $assignment->save();

        $afterAssignedUserIds = $this->assignmentUserIds($assignment);
        $notifications = app(NotificationService::class);

        $afterAssignedUserIds
            ->diff($beforeAssignedUserIds)
            ->each(fn (int $userId) => $notifications->notifyReservationAssigned($reservation, $userId, $request->user()));

        $beforeAssignedUserIds
            ->diff($afterAssignedUserIds)
            ->each(fn (int $userId) => $notifications->notifyReservationRemoved($reservation, $userId, $request->user()));

        if (!$afterAssignedUserIds->isEmpty() && $afterAssignedUserIds->diff($beforeAssignedUserIds)->isEmpty() && !in_array($field, ['chef_1_id', 'chef_2_id', 'chef_3_id', 'extra_chef_ids', 'assistant_id'], true)) {
            $afterAssignedUserIds->each(fn (int $userId) => $notifications->notifyReservationUpdated($reservation, $userId, $request->user()));
        }

        return response()->json([
            'ok' => true,
            'field' => $field,
            'value' => $field === 'extra_chef_ids' ? $assignment->extra_chef_ids : $assignment->{$field},
            'message' => 'Saved',
        ]);
    }

    public function staffCounts(Request $request): JsonResponse
    {
        $selectedDate = $this->selectedDate($request);
        $weekStart = $selectedDate->startOfWeek(CarbonImmutable::SUNDAY);
        $weekEnd = $selectedDate->endOfWeek(CarbonImmutable::SATURDAY);

        return response()->json([
            'ok' => true,
            'week' => [
                'start' => $weekStart->toDateString(),
                'end' => $weekEnd->toDateString(),
                'label' => $weekStart->format('M j') . ' - ' . $weekEnd->format('M j'),
            ],
            'counts' => $this->weeklyStaffCounts($weekStart, $weekEnd)->values(),
        ]);
    }

    public function rules()
    {
        return view('admin.schedule.rules', [
            'weekStart' => $this->priorityService->currentWeekStart(),
            'rules' => $this->priorityService->scoreRules(),
        ]);
    }

    public function editScore(User $user)
    {
        abort_unless($user->staff_type === 'Chef', 404);

        $row = $this->priorityService
            ->dashboardData()
            ['allChefRows']
            ->firstWhere('user_id', $user->id);

        return view('admin.schedule.score', [
            'user' => $user,
            'row' => $row,
            'statuses' => SchedulePriorityService::STATUSES,
            'availabilityOptions' => SchedulePriorityService::AVAILABILITY,
            'weekStart' => $this->priorityService->currentWeekStart(),
        ]);
    }

    public function updateScore(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->staff_type === 'Chef', 404);

        $validated = $request->validate([
            'status' => ['required', Rule::in(SchedulePriorityService::STATUSES)],
            'availability_status' => ['required', Rule::in(SchedulePriorityService::AVAILABILITY)],
            'requested_days_off' => ['required', 'integer', 'min:0', 'max:7'],
            'missed_days' => ['required', 'integer', 'min:0', 'max:7'],
            'late_cancellations' => ['required', 'integer', 'min:0', 'max:7'],
            'reliability_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->priorityService->updateWeeklyLog($user, $validated);

        return redirect()->route('admin.schedule.index')->with('ok', 'Priority score inputs updated.');
    }

    private function selectedDate(Request $request): CarbonImmutable
    {
        $date = (string) $request->query('date', '');

        try {
            return $date !== '' ? CarbonImmutable::parse($date)->startOfDay() : CarbonImmutable::today();
        } catch (\Throwable $e) {
            return CarbonImmutable::today();
        }
    }

    private function activeTeamMembers(): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'staff_type', 'role', 'position', 'is_active']);
    }

    private function chefFirstOptions(Collection $teamMembers): Collection
    {
        return $teamMembers
            ->sortBy(fn (User $user) => ($this->isChefLike($user) ? '0-' : '1-') . strtolower((string) $user->name))
            ->values();
    }

    private function isChefLike(User $user): bool
    {
        $haystack = strtolower(trim(($user->staff_type ?? '') . ' ' . ($user->role ?? '') . ' ' . ($user->position ?? '')));

        return str_contains($haystack, 'chef');
    }

    private function officeOptions(Collection $teamMembers): Collection
    {
        return $teamMembers
            ->filter(function (User $user) {
                $role = strtolower(trim((string) ($user->role ?? '')));
                $staffType = strtolower(trim((string) ($user->staff_type ?? '')));
                $position = strtolower(trim((string) ($user->position ?? '')));
                $haystack = trim($role . ' ' . $staffType . ' ' . $position);

                $isOfficeAllowed = in_array($role, ['admin', 'owner', 'office'], true)
                    || str_contains($haystack, 'admin')
                    || str_contains($haystack, 'owner')
                    || str_contains($haystack, 'ceo')
                    || str_contains($haystack, 'office');

                $isOperationalCrew = str_contains($haystack, 'chef')
                    || str_contains($haystack, 'server')
                    || str_contains($haystack, 'driver');

                return $isOfficeAllowed && !$isOperationalCrew;
            })
            ->sortBy(fn (User $user) => strtolower((string) $user->name))
            ->values();
    }

    private function weeklyStaffCounts(CarbonImmutable $weekStart, CarbonImmutable $weekEnd): Collection
    {
        $assignments = ScheduleAssignment::query()
            ->whereHas('reservation', function ($query) use ($weekStart, $weekEnd) {
                $query
                    ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                    ->whereRaw("LOWER(COALESCE(status, '')) NOT LIKE ?", ['%cancel%'])
                    ->whereRaw("LOWER(COALESCE(status, '')) NOT LIKE ?", ['%void%']);
            })
            ->get();

        $counts = [];

        foreach ($assignments as $assignment) {
            $userIds = collect([
                $assignment->chef_1_id,
                $assignment->chef_2_id,
                $assignment->chef_3_id,
                $assignment->chef_4_id,
            ]);

            foreach ((array) ($assignment->extra_chef_ids ?? []) as $userId) {
                $userIds->push($userId);
            }

            $userIds
                ->filter(fn ($userId) => !blank($userId))
                ->map(fn ($userId) => (int) $userId)
                ->unique()
                ->each(function (int $userId) use (&$counts) {
                    $counts[$userId] = ($counts[$userId] ?? 0) + 1;
                });
        }

        if (empty($counts)) {
            return collect();
        }

        $users = User::query()
            ->whereIn('id', array_keys($counts))
            ->get(['id', 'name', 'staff_type', 'role'])
            ->keyBy('id');

        return collect($counts)
            ->map(function (int $count, int $userId) use ($users) {
                $user = $users->get($userId);

                return [
                    'id' => $userId,
                    'name' => $user?->name ?: 'User #' . $userId,
                    'count' => $count,
                    'staff_type' => (string) ($user?->staff_type ?? ''),
                    'role' => (string) ($user?->role ?? ''),
                ];
            })
            ->sort(function (array $a, array $b) {
                return $b['count'] <=> $a['count'] ?: strcasecmp($a['name'], $b['name']);
            })
            ->values();
    }

    private function assignmentFields(): array
    {
        return [
            'chef_1_id',
            'chef_2_id',
            'chef_3_id',
            'extra_chef_ids',
            'assistant_id',
            'confirm_by_id',
            'van',
            'leave_at',
            'time_to_drive',
            'chef_tip',
            'schedule_notes',
        ];
    }

    private function assignmentRules(string $field): array
    {
        return match ($field) {
            'chef_1_id', 'chef_2_id', 'chef_3_id', 'assistant_id', 'confirm_by_id' => ['nullable', 'integer', 'exists:users,id'],
            'leave_at' => ['nullable', 'date_format:H:i'],
            'chef_tip' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'schedule_notes' => ['nullable', 'string', 'max:2000'],
            'van', 'time_to_drive' => ['nullable', 'string', 'max:80'],
            default => ['nullable'],
        };
    }

    private function assignmentUserIds(ScheduleAssignment $assignment): Collection
    {
        $userIds = collect([
            $assignment->user_id,
            $assignment->chef_1_id,
            $assignment->chef_2_id,
            $assignment->chef_3_id,
            $assignment->chef_4_id,
            $assignment->assistant_id,
        ]);

        foreach ((array) ($assignment->extra_chef_ids ?? []) as $userId) {
            $userIds->push($userId);
        }

        return $userIds
            ->filter(fn ($userId) => filled($userId))
            ->map(fn ($userId) => (int) $userId)
            ->unique()
            ->values();
    }
}
