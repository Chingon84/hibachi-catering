<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchedulePriorityLog extends Model
{
    protected $fillable = [
        'user_id',
        'week_start_date',
        'status',
        'availability_status',
        'events_assigned',
        'requested_days_off',
        'missed_days',
        'late_cancellations',
        'reliability_score',
        'fairness_adjustment',
        'penalty_points',
        'final_priority_score',
        'priority_tier',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'week_start_date' => 'date',
            'events_assigned' => 'integer',
            'requested_days_off' => 'integer',
            'missed_days' => 'integer',
            'late_cancellations' => 'integer',
            'reliability_score' => 'decimal:2',
            'fairness_adjustment' => 'decimal:2',
            'penalty_points' => 'decimal:2',
            'final_priority_score' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
