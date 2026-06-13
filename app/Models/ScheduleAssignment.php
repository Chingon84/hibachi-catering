<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class ScheduleAssignment extends Model
{
    protected $fillable = [
        'reservation_id',
        'user_id',
        'chef_1_id',
        'chef_2_id',
        'chef_3_id',
        'chef_4_id',
        'extra_chef_ids',
        'assistant_id',
        'confirm_by_id',
        'van',
        'leave_at',
        'time_to_drive',
        'chef_tip',
        'schedule_notes',
        'assigned_by',
        'assigned_at',
        'week_start_date',
        'priority_snapshot',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'week_start_date' => 'date',
            'priority_snapshot' => 'decimal:2',
            'leave_at' => 'datetime:H:i',
            'chef_tip' => 'decimal:2',
            'extra_chef_ids' => 'array',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function chef1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'chef_1_id');
    }

    public function chef2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'chef_2_id');
    }

    public function chef3(): BelongsTo
    {
        return $this->belongsTo(User::class, 'chef_3_id');
    }

    public function chef4(): BelongsTo
    {
        return $this->belongsTo(User::class, 'chef_4_id');
    }

    public function assistant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assistant_id');
    }

    public function confirmBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirm_by_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function assignedStaffSummaryRows(): Collection
    {
        $rows = collect([
            ['label' => 'Chef 1', 'value' => $this->chefNameFor(1), 'user_id' => $this->chef_1_id ?: $this->user_id],
            ['label' => 'Chef 2', 'value' => $this->chefNameFor(2), 'user_id' => $this->chef_2_id],
            ['label' => 'Chef 3', 'value' => $this->chefNameFor(3), 'user_id' => $this->chef_3_id],
        ]);

        $this->extraChefNamesByNumber()
            ->each(function (?string $name, int $chefNumber) use ($rows) {
                if ($chefNumber <= 3 || blank($name)) {
                    return;
                }

                $rows->push([
                    'label' => 'Chef '.$chefNumber,
                    'value' => $name,
                    'user_id' => $this->extraChefIdsByNumber()->get($chefNumber),
                ]);
            });

        $rows->push([
            'label' => 'Van',
            'value' => filled($this->van) ? (string) $this->van : null,
            'user_id' => null,
        ]);

        return $rows->map(function (array $row) {
            $row['value'] = filled($row['value'] ?? null) ? (string) $row['value'] : 'N/A';

            return $row;
        });
    }

    public function roleLabelsForUser(User|int $user): Collection
    {
        $userId = $user instanceof User ? (int) $user->id : (int) $user;
        $roles = collect();

        if ((int) ($this->chef_1_id ?: $this->user_id) === $userId) {
            $roles->push('Chef 1');
        }
        if ((int) $this->chef_2_id === $userId) {
            $roles->push('Chef 2');
        }
        if ((int) $this->chef_3_id === $userId) {
            $roles->push('Chef 3');
        }
        if ((int) $this->chef_4_id === $userId) {
            $roles->push('Chef 4');
        }

        foreach ((array) ($this->extra_chef_ids ?? []) as $chefNumber => $extraUserId) {
            if ((int) $extraUserId === $userId) {
                $roles->push('Chef '.(int) $chefNumber);
            }
        }

        if ((int) $this->assistant_id === $userId) {
            $roles->push('Assistant');
        }
        if ($roles->isEmpty() && (int) $this->user_id === $userId) {
            $roles->push('Staff');
        }

        return $roles->unique()->values();
    }

    public function hasAssignedStaff(): bool
    {
        return $this->assignedStaffSummaryRows()
            ->contains(fn (array $row) => ($row['value'] ?? 'N/A') !== 'N/A');
    }

    public function extraChefNamesByNumber(): Collection
    {
        $extraChefIds = (array) ($this->extra_chef_ids ?? []);

        if (blank($extraChefIds['4'] ?? null) && filled($this->chef_4_id)) {
            $extraChefIds['4'] = (int) $this->chef_4_id;
        }

        $extraChefIds = collect($extraChefIds)
            ->mapWithKeys(fn ($userId, $chefNumber) => [(int) $chefNumber => filled($userId) ? (int) $userId : null])
            ->filter(fn ($userId, int $chefNumber) => $chefNumber >= 4 && filled($userId))
            ->sortKeys();

        if ($extraChefIds->isEmpty()) {
            return collect();
        }

        $loadedUsers = collect();
        if ($extraChefIds->has(4) && $this->relationLoaded('chef4') && $this->chef4) {
            $loadedUsers->put(4, $this->chef4);
        }

        $missingIds = $extraChefIds
            ->reject(fn ($userId, int $chefNumber) => $loadedUsers->has($chefNumber))
            ->values()
            ->unique()
            ->all();

        $usersById = empty($missingIds)
            ? collect()
            : User::query()->whereIn('id', $missingIds)->get(['id', 'name'])->keyBy('id');

        return $extraChefIds->mapWithKeys(function (int $userId, int $chefNumber) use ($loadedUsers, $usersById) {
            $user = $loadedUsers->get($chefNumber) ?: $usersById->get($userId);

            return [$chefNumber => $user?->name];
        });
    }

    public function extraChefIdsByNumber(): Collection
    {
        $extraChefIds = (array) ($this->extra_chef_ids ?? []);

        if (blank($extraChefIds['4'] ?? null) && filled($this->chef_4_id)) {
            $extraChefIds['4'] = (int) $this->chef_4_id;
        }

        return collect($extraChefIds)
            ->mapWithKeys(fn ($userId, $chefNumber) => [(int) $chefNumber => filled($userId) ? (int) $userId : null])
            ->filter(fn ($userId, int $chefNumber) => $chefNumber >= 4 && filled($userId))
            ->sortKeys();
    }

    private function chefNameFor(int $chefNumber): ?string
    {
        $relation = match ($chefNumber) {
            1 => 'chef1',
            2 => 'chef2',
            3 => 'chef3',
            default => null,
        };

        if ($relation === null) {
            return null;
        }

        $user = $this->{$relation};

        if (!$user && $chefNumber === 1) {
            $user = $this->user;
        }

        return $user?->name;
    }
}
