<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffEventConfirmation extends Model
{
    public const STATUS_NOT_VIEWED = 'not_viewed';
    public const STATUS_VIEWED = 'viewed';
    public const STATUS_CONFIRMED = 'confirmed';

    protected $fillable = [
        'reservation_id',
        'user_id',
        'status',
        'viewed_at',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
            'confirmed_at' => 'datetime',
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

    public function markViewed(): void
    {
        if ($this->status === self::STATUS_CONFIRMED) {
            return;
        }

        $this->status = self::STATUS_VIEWED;
        $this->viewed_at ??= now();
        $this->save();
    }

    public function markConfirmed(): void
    {
        $this->status = self::STATUS_CONFIRMED;
        $this->viewed_at ??= now();
        $this->confirmed_at ??= now();
        $this->save();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_VIEWED => 'Viewed',
            default => 'Not viewed',
        };
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            self::STATUS_CONFIRMED => 'confirmed',
            self::STATUS_VIEWED => 'viewed',
            default => 'not-viewed',
        };
    }

    public function timestampLabel(): ?string
    {
        $timestamp = $this->status === self::STATUS_CONFIRMED ? $this->confirmed_at : $this->viewed_at;

        return $timestamp?->format('M j, g:i A');
    }

    public static function emptySummary(): array
    {
        return [
            'status' => self::STATUS_NOT_VIEWED,
            'label' => 'Not viewed',
            'tone' => 'not-viewed',
            'timestamp' => null,
            'confirmed' => false,
        ];
    }

    public function summary(): array
    {
        return [
            'status' => $this->status,
            'label' => $this->statusLabel(),
            'tone' => $this->statusTone(),
            'timestamp' => $this->timestampLabel(),
            'confirmed' => $this->status === self::STATUS_CONFIRMED,
        ];
    }
}
