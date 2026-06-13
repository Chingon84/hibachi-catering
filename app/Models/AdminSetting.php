<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class AdminSetting extends Model
{
    protected $fillable = [
        'group_name',
        'key',
        'value',
    ];

    public static function valuesForGroup(string $group, array $defaults = []): array
    {
        if (!Schema::hasTable('admin_settings')) {
            return $defaults;
        }

        $stored = static::query()
            ->where('group_name', $group)
            ->pluck('value', 'key')
            ->all();

        return array_replace($defaults, $stored);
    }

    public static function storeGroupValues(string $group, array $values): void
    {
        if (empty($values)) {
            return;
        }

        $timestamp = now();

        static::query()->upsert(
            collect($values)->map(fn ($value, $key) => [
                'group_name' => $group,
                'key' => (string) $key,
                'value' => $value,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ])->values()->all(),
            ['group_name', 'key'],
            ['value', 'updated_at']
        );
    }
}
