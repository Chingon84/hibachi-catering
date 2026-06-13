<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\ScheduleAssignment;
use App\Models\SchedulePriorityLog;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class SchedulePriorityService
{
    public const STATUSES = ['Active', 'Standby', 'Suspended'];
    public const AVAILABILITY = ['Available', 'Requested Off', 'Unavailable'];
    public const TIERS = ['A', 'B', 'C', 'D'];
    public const SENIORITY_ORDER = [
        'Jonathan E',
        'Elvis',
        'Marco H',
        'Said',
        'Ariel',
        'Angel',
        'Agustin',
        'Isaac',
        'Carlos',
        'Yaveth',
        'Mr. Agustin',
        'Eric',
        'Manuel',
        'Max',
    ];

    public function currentWeekStart(?CarbonImmutable $date = null): CarbonImmutable
    {
        $base = $date ?: CarbonImmutable::today();
        return $base->startOfWeek(CarbonImmutable::MONDAY);
    }

    public function currentWeekEnd(?CarbonImmutable $date = null): CarbonImmutable
    {
        return $this->currentWeekStart($date)->endOfWeek(CarbonImmutable::SUNDAY);
    }

    public function filters(): array
    {
        return [
            'q' => trim((string) request('q', '')),
            'status' => trim((string) request('status', '')),
            'availability' => trim((string) request('availability', '')),
            'tier' => trim((string) request('tier', '')),
        ];
    }

    public function scoreRules(): array
    {
        return [
            'seniority' => 'Weighted from the seniority ladder. More senior chefs start with a stronger base score.',
            'reliability' => 'Weekly reliability score starts from the tracked record and can be updated as consistency changes.',
            'fair_rotation' => 'Chefs with more assignments this week receive a small downward adjustment to keep distribution fair when volume slows.',
            'penalties' => 'Missed shifts, requested days off, and late cancellations reduce weekly priority automatically.',
        ];
    }

    public function dashboardData(array $filters = []): array
    {
        $weekStart = $this->currentWeekStart();
        $weekEnd = $this->currentWeekEnd();

        $rows = $this->buildPriorityRows($weekStart, $weekEnd);
        $filtered = $this->applyFilters($rows, $filters)->values();

        $upcomingEvents = $this->upcomingEvents();
        $unassignedEvents = $upcomingEvents->where('assigned_user_id', null)->values();

        return [
            'filters' => $filters,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'chefRows' => $filtered,
            'allChefRows' => $rows,
            'upcomingEvents' => $upcomingEvents,
            'unassignedEvents' => $unassignedEvents,
            'kpis' => [
                'total_chefs' => $rows->count(),
                'available_this_week' => $rows->where('availability', 'Available')->count(),
                'low_priority_alerts' => $rows->filter(fn (array $row) => $row['priority_tier'] === 'D' || $row['status'] === 'Suspended')->count(),
                'unassigned_events' => $unassignedEvents->count(),
            ],
        ];
    }

    public function recommendedChefs(?int $reservationId = null): Collection
    {
        $rows = $this->buildPriorityRows($this->currentWeekStart(), $this->currentWeekEnd());

        if ($reservationId) {
            return $rows
                ->where('status', '!=', 'Suspended')
                ->where('availability', 'Available')
                ->values();
        }

        return $rows->values();
    }

    public function upcomingEvents(): Collection
    {
        $today = CarbonImmutable::today();

        $assignments = ScheduleAssignment::query()
            ->with(['user:id,name'])
            ->whereHas('reservation', fn ($query) => $query->whereDate('date', '>=', $today))
            ->get()
            ->keyBy('reservation_id');

        return Reservation::query()
            ->whereDate('date', '>=', $today)
            ->orderBy('date')
            ->orderBy('time')
            ->limit(60)
            ->get(['id', 'code', 'customer_name', 'date', 'time', 'status', 'city'])
            ->map(function (Reservation $reservation) use ($assignments) {
                $assignment = $assignments->get($reservation->id);

                return [
                    'id' => $reservation->id,
                    'label' => trim(implode(' • ', array_filter([
                        $reservation->code ?: 'Event #' . $reservation->id,
                        optional($reservation->date)->format('M d'),
                        $reservation->time,
                        $reservation->customer_name,
                    ]))),
                    'code' => $reservation->code ?: 'Event #' . $reservation->id,
                    'date' => optional($reservation->date)?->format('M d, Y'),
                    'time' => (string) $reservation->time,
                    'customer_name' => (string) $reservation->customer_name,
                    'status' => (string) $reservation->status,
                    'city' => (string) $reservation->city,
                    'assigned_user_id' => $assignment?->user_id,
                    'assigned_user_name' => $assignment?->user?->name,
                ];
            })
            ->values();
    }

    public function eventDetail(int $reservationId): ?array
    {
        return $this->upcomingEvents()->firstWhere('id', $reservationId);
    }

    public function persistAssignment(int $reservationId, int $userId, int $assignedBy, ?string $notes = null): void
    {
        $reservation = Reservation::query()->findOrFail($reservationId);
        $weekStart = $this->currentWeekStart(CarbonImmutable::parse($reservation->date));

        ScheduleAssignment::query()->updateOrCreate(
            ['reservation_id' => $reservation->id],
            [
                'user_id' => $userId,
                'assigned_by' => $assignedBy,
                'assigned_at' => now(),
                'week_start_date' => $weekStart,
                'priority_snapshot' => $this->finalScoreForChef($userId, $weekStart),
                'notes' => $notes,
            ]
        );

        $this->syncWeeklyAssignmentCounts($weekStart);
    }

    public function updateWeeklyLog(User $user, array $data): SchedulePriorityLog
    {
        $weekStart = $this->currentWeekStart();
        $log = SchedulePriorityLog::query()->firstOrNew([
            'user_id' => $user->id,
            'week_start_date' => $weekStart,
        ]);

        $defaults = $this->defaultWeeklyState($user, $weekStart);
        $eventsAssigned = $this->assignmentsByChef($weekStart, $this->currentWeekEnd($weekStart))->get($user->id, 0);

        $log->fill(array_merge($defaults, [
            'events_assigned' => $eventsAssigned,
        ], $data));

        $log->fairness_adjustment = $this->fairRotationAdjustment((int) $log->events_assigned);
        $log->penalty_points = $this->penaltyPoints((int) $log->missed_days, (int) $log->requested_days_off, (int) $log->late_cancellations);
        $log->final_priority_score = $this->computeFinalScore(
            $this->seniorityWeightFor($user),
            (float) $log->reliability_score,
            (float) $log->fairness_adjustment,
            (float) $log->penalty_points
        );
        $log->priority_tier = $this->tierForScore((float) $log->final_priority_score, (string) $log->status);
        $log->save();

        return $log;
    }

    public function syncWeeklyAssignmentCounts(CarbonImmutable $weekStart): void
    {
        $weekEnd = $this->currentWeekEnd($weekStart);
        $counts = $this->assignmentsByChef($weekStart, $weekEnd);

        User::query()
            ->where('staff_type', 'Chef')
            ->get()
            ->each(function (User $user) use ($weekStart, $counts) {
                $log = SchedulePriorityLog::query()->firstOrNew([
                    'user_id' => $user->id,
                    'week_start_date' => $weekStart,
                ]);

                $defaults = $this->defaultWeeklyState($user, $weekStart);
                $log->fill(array_merge($defaults, [
                    'events_assigned' => $counts->get($user->id, 0),
                ], $log->exists ? $log->only([
                    'status',
                    'availability_status',
                    'requested_days_off',
                    'missed_days',
                    'late_cancellations',
                    'reliability_score',
                    'notes',
                ]) : []));

                $log->fairness_adjustment = $this->fairRotationAdjustment((int) $log->events_assigned);
                $log->penalty_points = $this->penaltyPoints((int) $log->missed_days, (int) $log->requested_days_off, (int) $log->late_cancellations);
                $log->final_priority_score = $this->computeFinalScore(
                    $this->seniorityWeightFor($user),
                    (float) $log->reliability_score,
                    (float) $log->fairness_adjustment,
                    (float) $log->penalty_points
                );
                $log->priority_tier = $this->tierForScore((float) $log->final_priority_score, (string) $log->status);
                $log->save();
            });
    }

    private function buildPriorityRows(CarbonImmutable $weekStart, CarbonImmutable $weekEnd): Collection
    {
        $logs = SchedulePriorityLog::query()
            ->whereDate('week_start_date', $weekStart)
            ->get()
            ->keyBy('user_id');

        $assignmentCounts = $this->assignmentsByChef($weekStart, $weekEnd);

        return User::query()
            ->where('staff_type', 'Chef')
            ->orderBy('name')
            ->get(['id', 'name', 'staff_type', 'is_active'])
            ->map(function (User $user) use ($logs, $assignmentCounts, $weekStart) {
                $state = $logs->get($user->id);
                $defaults = $this->defaultWeeklyState($user, $weekStart);
                $eventsAssigned = $assignmentCounts->get($user->id, (int) ($state->events_assigned ?? 0));
                $seniorityWeight = $this->seniorityWeightFor($user);
                $reliabilityScore = (float) ($state->reliability_score ?? $defaults['reliability_score']);
                $requestedDaysOff = (int) ($state->requested_days_off ?? $defaults['requested_days_off']);
                $missedDays = (int) ($state->missed_days ?? $defaults['missed_days']);
                $lateCancellations = (int) ($state->late_cancellations ?? $defaults['late_cancellations']);
                $fairnessAdjustment = $this->fairRotationAdjustment($eventsAssigned);
                $penaltyPoints = $this->penaltyPoints($missedDays, $requestedDaysOff, $lateCancellations);
                $status = (string) ($state->status ?? $defaults['status']);
                $availability = (string) ($state->availability_status ?? $defaults['availability_status']);
                $finalScore = $this->computeFinalScore($seniorityWeight, $reliabilityScore, $fairnessAdjustment, $penaltyPoints);
                $tier = $this->tierForScore($finalScore, $status);

                return [
                    'user_id' => $user->id,
                    'chef_name' => $user->name,
                    'seniority_rank' => $this->seniorityRankFor($user),
                    'seniority_weight' => $seniorityWeight,
                    'reliability_score' => round($reliabilityScore, 1),
                    'events_this_week' => $eventsAssigned,
                    'requested_days_off' => $requestedDaysOff,
                    'missed_days' => $missedDays,
                    'late_cancellations' => $lateCancellations,
                    'missed_requested_display' => $missedDays . ' / ' . $requestedDaysOff,
                    'fair_rotation_adjustment' => round($fairnessAdjustment, 1),
                    'penalty_points' => round($penaltyPoints, 1),
                    'final_priority_score' => round($finalScore, 1),
                    'priority_tier' => $tier,
                    'availability' => $availability,
                    'status' => $status,
                    'notes' => (string) ($state->notes ?? ''),
                ];
            })
            ->sortByDesc('final_priority_score')
            ->values();
    }

    private function applyFilters(Collection $rows, array $filters): Collection
    {
        return $rows
            ->when(($filters['q'] ?? '') !== '', function (Collection $collection) use ($filters) {
                $needle = strtolower((string) $filters['q']);
                return $collection->filter(fn (array $row) => str_contains(strtolower($row['chef_name']), $needle));
            })
            ->when(($filters['status'] ?? '') !== '', fn (Collection $collection) => $collection->where('status', $filters['status']))
            ->when(($filters['availability'] ?? '') !== '', fn (Collection $collection) => $collection->where('availability', $filters['availability']))
            ->when(($filters['tier'] ?? '') !== '', fn (Collection $collection) => $collection->where('priority_tier', $filters['tier']));
    }

    private function assignmentsByChef(CarbonImmutable $weekStart, CarbonImmutable $weekEnd): Collection
    {
        return ScheduleAssignment::query()
            ->whereDate('week_start_date', $weekStart)
            ->whereHas('reservation', fn ($query) => $query->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()]))
            ->get(['user_id'])
            ->countBy('user_id');
    }

    private function finalScoreForChef(int $userId, CarbonImmutable $weekStart): float
    {
        $weekEnd = $this->currentWeekEnd($weekStart);
        $rows = $this->buildPriorityRows($weekStart, $weekEnd);
        return (float) ($rows->firstWhere('user_id', $userId)['final_priority_score'] ?? 0);
    }

    private function seniorityRankFor(User $user): int
    {
        $position = array_search($user->name, self::SENIORITY_ORDER, true);
        return $position === false ? count(self::SENIORITY_ORDER) + 1 : $position + 1;
    }

    private function seniorityWeightFor(User $user): float
    {
        $rank = $this->seniorityRankFor($user);
        return max(28, 60 - (($rank - 1) * 2.5));
    }

    private function fairRotationAdjustment(int $eventsAssigned): float
    {
        return max(-18, 8 - ($eventsAssigned * 3));
    }

    private function penaltyPoints(int $missedDays, int $requestedDaysOff, int $lateCancellations): float
    {
        return ($missedDays * 16) + ($requestedDaysOff * 4) + ($lateCancellations * 10);
    }

    private function computeFinalScore(float $seniorityWeight, float $reliabilityScore, float $fairnessAdjustment, float $penaltyPoints): float
    {
        return $seniorityWeight + $reliabilityScore + $fairnessAdjustment - $penaltyPoints;
    }

    private function tierForScore(float $score, string $status): string
    {
        if ($status === 'Suspended') {
            return 'D';
        }

        return match (true) {
            $score >= 125 => 'A',
            $score >= 105 => 'B',
            $score >= 85 => 'C',
            default => 'D',
        };
    }

    private function defaultWeeklyState(User $user, CarbonImmutable $weekStart): array
    {
        return [
            'status' => $user->is_active ? 'Active' : 'Suspended',
            'availability_status' => $user->is_active ? 'Available' : 'Unavailable',
            'events_assigned' => 0,
            'requested_days_off' => 0,
            'missed_days' => 0,
            'late_cancellations' => 0,
            'reliability_score' => 82,
            'fairness_adjustment' => 8,
            'penalty_points' => 0,
            'final_priority_score' => $this->computeFinalScore($this->seniorityWeightFor($user), 82, 8, 0),
            'priority_tier' => 'C',
            'notes' => 'Auto-generated weekly baseline for scheduling.',
            'week_start_date' => $weekStart,
        ];
    }
}
