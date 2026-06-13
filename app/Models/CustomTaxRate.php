<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomTaxRate extends Model
{
    protected $fillable = [
        'city_name',
        'city_key',
        'tax_rate',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tax_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public static function cityKey(string $cityName): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim($cityName)));
    }

    public function setCityNameAttribute(string $value): void
    {
        $cityName = preg_replace('/\s+/', ' ', trim($value));
        $this->attributes['city_name'] = $cityName;
        $this->attributes['city_key'] = self::cityKey($cityName);
    }
}
