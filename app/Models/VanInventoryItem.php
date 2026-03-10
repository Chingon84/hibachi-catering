<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VanInventoryItem extends Model
{
    use HasFactory;

    public const CONDITION_STATUSES = ['good', 'damaged', 'missing', 'needs replacement'];

    protected $fillable = [
        'van_id',
        'inventory_item_id',
        'quantity_assigned',
        'quantity_present',
        'condition_status',
        'last_checked_at',
        'checked_by_user_id',
        'notes',
    ];

    protected $casts = [
        'quantity_assigned' => 'decimal:2',
        'quantity_present' => 'decimal:2',
        'last_checked_at' => 'datetime',
    ];

    public function van(): BelongsTo
    {
        return $this->belongsTo(Van::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by_user_id');
    }

    public function isMissing(): bool
    {
        return $this->condition_status === 'missing' || (float) $this->quantity_present < (float) $this->quantity_assigned;
    }

    public function isDamaged(): bool
    {
        return in_array($this->condition_status, ['damaged', 'needs replacement'], true);
    }
}
