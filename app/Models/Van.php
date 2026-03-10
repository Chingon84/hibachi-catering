<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Van extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = ['active', 'maintenance', 'inactive'];

    protected $fillable = [
        'van_number',
        'name',
        'code',
        'license_plate',
        'status',
        'notes',
    ];

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(VanInventoryItem::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function loadouts(): HasMany
    {
        return $this->hasMany(VanLoadout::class);
    }

    public function currentLoadout(): HasOne
    {
        return $this->hasOne(VanLoadout::class)->latestOfMany();
    }

    public function displayName(): string
    {
        return $this->van_number ? 'Van ' . $this->van_number : (string) $this->name;
    }
}
