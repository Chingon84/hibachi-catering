<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use HasFactory;

    public const TYPES = [
        'restock',
        'assigned_to_event',
        'returned_from_event',
        'damaged',
        'lost',
        'manual_adjustment',
        'transferred_to_van',
        'transferred_from_van',
    ];

    protected $fillable = [
        'inventory_item_id',
        'van_id',
        'movement_type',
        'quantity',
        'previous_stock',
        'new_stock',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'previous_stock' => 'decimal:2',
        'new_stock' => 'decimal:2',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function van(): BelongsTo
    {
        return $this->belongsTo(Van::class);
    }
}
