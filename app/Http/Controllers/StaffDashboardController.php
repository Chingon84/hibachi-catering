<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\ScheduleAssignment;
use App\Models\StaffEventConfirmation;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class StaffDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $this->staffUser($request);
        $today = CarbonImmutable::today(config('app.timezone') ?: 'America/Los_Angeles');

        $todayEvents = $this->assignedReservationsQuery($user)
            ->whereDate('date', $today->toDateString())
            ->orderBy('time')
            ->get();

        $upcomingEvents = $this->assignedReservationsQuery($user)
            ->whereDate('date', '>', $today->toDateString())
            ->orderBy('date')
            ->orderBy('time')
            ->limit(10)
            ->get();

        $pastEvents = $this->assignedReservationsQuery($user)
            ->whereDate('date', '<', $today->toDateString())
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->limit(7)
            ->get();

        return view('staff.dashboard', [
            'user' => $user,
            'todayEvents' => $todayEvents,
            'upcomingEvents' => $upcomingEvents,
            'pastEvents' => $pastEvents,
        ]);
    }

    public function show(Request $request, Reservation $reservation)
    {
        $user = $this->staffUser($request);

        $reservation->load($this->reservationRelations());

        if (!$this->isAssignedToReservation($reservation, $user)) {
            abort(403, 'This event is not assigned to you.');
        }

        $this->confirmationFor($reservation, $user)->markViewed();
        $reservation->load('staffEventConfirmations');

        return view('staff.event-show', [
            'user' => $user,
            'reservation' => $reservation,
        ]);
    }

    public function confirm(Request $request, Reservation $reservation)
    {
        $user = $this->staffUser($request);

        $reservation->load($this->reservationRelations());

        if (!$this->isAssignedToReservation($reservation, $user)) {
            abort(403, 'This event is not assigned to you.');
        }

        if ($this->isCancelled($reservation)) {
            abort(403, 'Cancelled events cannot be confirmed.');
        }

        $this->confirmationFor($reservation, $user)->markConfirmed();
        app(NotificationService::class)->notifyStaffConfirmed($reservation, $user);

        return redirect()
            ->back()
            ->with('ok', 'Event confirmed successfully.');
    }

    private function staffUser(Request $request): User
    {
        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        if ((int) ($user->is_active ?? 1) !== 1) {
            abort(403, 'Your account is inactive.');
        }

        if (!method_exists($user, 'isStaffPortalUser') || !$user->isStaffPortalUser()) {
            abort(403, 'Staff portal access only.');
        }

        return $user;
    }

    private function assignedReservationsQuery(User $user): Builder
    {
        return Reservation::query()
            ->with($this->reservationRelations())
            ->whereHas('scheduleAssignment', function (Builder $query) use ($user) {
                $this->assignedToUserScope($query, (int) $user->id);
            })
            ->whereRaw("LOWER(COALESCE(status, '')) NOT LIKE ?", ['%cancel%'])
            ->whereRaw("LOWER(COALESCE(status, '')) NOT LIKE ?", ['%void%']);
    }

    private function assignedToUserScope(Builder $query, int $userId): void
    {
        $query->where(function (Builder $q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhere('chef_1_id', $userId)
                ->orWhere('chef_2_id', $userId)
                ->orWhere('chef_3_id', $userId)
                ->orWhere('chef_4_id', $userId)
                ->orWhere('assistant_id', $userId)
                ->orWhereJsonContains('extra_chef_ids', $userId);
        });
    }

    private function isAssignedToReservation(Reservation $reservation, User $user): bool
    {
        $assignment = $reservation->scheduleAssignment;

        if (!$assignment instanceof ScheduleAssignment) {
            return false;
        }

        $userId = (int) $user->id;
        $assignedIds = collect([
            $assignment->user_id,
            $assignment->chef_1_id,
            $assignment->chef_2_id,
            $assignment->chef_3_id,
            $assignment->chef_4_id,
            $assignment->assistant_id,
        ]);

        foreach ((array) ($assignment->extra_chef_ids ?? []) as $extraUserId) {
            $assignedIds->push($extraUserId);
        }

        return $assignedIds
            ->filter(fn ($assignedId) => filled($assignedId))
            ->map(fn ($assignedId) => (int) $assignedId)
            ->contains($userId);
    }

    private function reservationRelations(): array
    {
        return [
            'items',
            'staffEventConfirmations',
            'scheduleAssignment.user:id,name',
            'scheduleAssignment.chef1:id,name',
            'scheduleAssignment.chef2:id,name',
            'scheduleAssignment.chef3:id,name',
            'scheduleAssignment.chef4:id,name',
            'scheduleAssignment.assistant:id,name',
        ];
    }

    private function confirmationFor(Reservation $reservation, User $user): StaffEventConfirmation
    {
        return StaffEventConfirmation::query()->firstOrCreate(
            [
                'reservation_id' => $reservation->id,
                'user_id' => $user->id,
            ],
            [
                'status' => StaffEventConfirmation::STATUS_NOT_VIEWED,
            ]
        );
    }

    private function isCancelled(Reservation $reservation): bool
    {
        $status = strtolower((string) ($reservation->status ?? ''));

        return str_contains($status, 'cancel') || str_contains($status, 'void');
    }
}
