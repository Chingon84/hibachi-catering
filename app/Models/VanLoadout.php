<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VanLoadout extends Model
{
    use HasFactory;

    public const VAN_STATUSES = ['clean', 'dirty', 'neutral'];

    protected $fillable = [
        'van_id',
        'van_status',
        'grills',
        'tables_count',
        'chairs_count',
        'propane_tanks_count',
        'dolly_count',
        'straps_count',
        'floor_mats_count',
        'trash_cans_count',
        'heaters_count',
        'buffet_warmers_count',
        'notes',
        'loaded_by_user_id',
        'checked_by_user_id',
        'checked_at',
        'reservation_id',
        'event_date',
    ];

    protected $casts = [
        'grills' => 'array',
        'checked_at' => 'datetime',
        'event_date' => 'date',
    ];

    public function van(): BelongsTo
    {
        return $this->belongsTo(Van::class);
    }

    public function loadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'loaded_by_user_id');
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by_user_id');
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function grillSummary(): string
    {
        $grills = collect($this->grills ?? [])->sort()->values();

        if ($grills->isEmpty()) {
            return 'No grills assigned';
        }

        return $grills->map(fn ($grill) => 'Grill #' . $grill)->implode(', ');
    }

    public function isReady(): bool
    {
        return $this->van_status === 'clean' && !is_null($this->checked_at);
    }
}
