<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use HasFactory, SoftDeletes;

    public const UNIT_TYPES = ['pieces', 'boxes', 'packs', 'sets', 'cases', 'pairs', 'rolls'];
    public const ITEM_TYPES = ['consumable', 'reusable', 'linen', 'tableware', 'service equipment'];
    public const STATUSES = ['active', 'inactive'];
    public const CATEGORIES = [
        'Plates',
        'Napkins',
        'Cups',
        'Glassware',
        'Cutlery',
        'Bowls',
        'Linens',
        'Serving Equipment',
        'Event Setup',
        'Van Equipment',
        'Cleaning Supplies',
        'Other',
    ];

    protected $fillable = [
        'name',
        'sku',
        'category',
        'unit_type',
        'current_stock',
        'minimum_stock',
        'reorder_level',
        'item_type',
        'allow_van_assignment',
        'status',
        'storage_location',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'current_stock' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'allow_van_assignment' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class)->latest('created_at');
    }

    public function vanAssignments(): HasMany
    {
        return $this->hasMany(VanInventoryItem::class);
    }

    public function isLowStock(): bool
    {
        return (float) $this->current_stock <= (float) $this->minimum_stock;
    }

    public function isOutOfStock(): bool
    {
        return (float) $this->current_stock <= 0;
    }

    public function canAssignToVan(): bool
    {
        return $this->allow_van_assignment && $this->item_type !== 'consumable';
    }

    public function stockStatus(): string
    {
        if ($this->isOutOfStock()) {
            return 'out';
        }

        if ($this->isLowStock()) {
            return 'low';
        }

        return 'healthy';
    }
}
