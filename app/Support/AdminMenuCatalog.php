<?php

namespace App\Support;

use App\Models\Menu;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminMenuCatalog
{
    public function grouped(bool $activeOnly = false): array
    {
        $databaseRows = $this->databaseRows($activeOnly);
        if ($databaseRows->isNotEmpty()) {
            $grouped = [];

            foreach ($databaseRows as $row) {
                $category = trim((string) ($row->category ?? ''));
                if ($category === '') {
                    $category = 'Uncategorized';
                }

                $name = MenuLabel::standardizeText(trim((string) ($row->name ?? '')));
                $key = trim((string) ($row->item_key ?? ''));
                if ($key === '') {
                    $key = Str::slug($name !== '' ? $name : ('menu-' . $row->id));
                }

                $grouped[$category][] = [
                    'key' => $key,
                    'name' => $name !== '' ? $name : MenuLabel::standardizeText($key),
                    'desc' => MenuLabel::standardizeText(trim((string) ($row->description ?? ''))),
                    'price' => round((float) ($row->price ?? 0), 2),
                ];
            }

            return $grouped;
        }

        return $this->configRows();
    }

    public function flat(bool $activeOnly = true): array
    {
        $flat = [];

        foreach ($this->grouped($activeOnly) as $category => $rows) {
            $category = trim((string) $category) !== '' ? trim((string) $category) : 'Uncategorized';

            foreach ((array) $rows as $row) {
                $key = trim((string) ($row['key'] ?? ''));
                $name = MenuLabel::standardizeText(trim((string) ($row['name'] ?? '')));
                if ($key === '' && $name === '') {
                    continue;
                }

                if ($key === '') {
                    $key = Str::slug($name);
                }

                $desc = MenuLabel::standardizeText(trim((string) ($row['desc'] ?? $row['description'] ?? '')));

                $flat[$key] = [
                    'id' => null,
                    'key' => $key,
                    'name' => $name !== '' ? $name : MenuLabel::standardizeText($key),
                    'price' => round((float) ($row['price'] ?? 0), 2),
                    'category' => $category,
                    'cat' => $category,
                    'desc' => $desc,
                    'description' => $desc,
                ];
            }
        }

        return $flat;
    }

    public function itemsCollection(bool $activeOnly = true): Collection
    {
        return collect($this->flat($activeOnly))
            ->map(fn (array $item) => (object) [
                'id' => $item['id'],
                'key' => $item['key'],
                'name' => $item['name'],
                'price' => $item['price'],
                'category' => $item['category'],
                'description' => $item['description'],
            ])
            ->values();
    }

    public function replaceFromGrouped(array $items): void
    {
        if (!Schema::hasTable('menus')) {
            throw new \RuntimeException('Menus table is not available.');
        }

        DB::transaction(function () use ($items) {
            Menu::query()->delete();

            $categorySort = 0;
            foreach ($items as $category => $rows) {
                $categoryName = trim((string) $category);
                if ($categoryName === '') {
                    $categoryName = 'Uncategorized';
                }

                $rowSort = 0;
                foreach ((array) $rows as $row) {
                    $key = trim((string) ($row['key'] ?? ''));
                    $name = MenuLabel::standardizeText(trim((string) ($row['name'] ?? '')));
                    if ($key === '' && $name !== '') {
                        $key = Str::slug($name);
                    }
                    if ($key === '' && $name === '') {
                        continue;
                    }

                    if ($name === '') {
                        $name = MenuLabel::standardizeText($key);
                    }

                    Menu::query()->create([
                        'item_key' => $key,
                        'name' => $name,
                        'description' => MenuLabel::standardizeText(trim((string) ($row['desc'] ?? $row['description'] ?? ''))),
                        'category' => $categoryName,
                        'category_sort' => $categorySort,
                        'price' => round((float) ($row['price'] ?? 0), 2),
                        'is_active' => true,
                        'sort' => $rowSort,
                    ]);

                    $rowSort++;
                }

                $categorySort++;
            }
        });
    }

    private function databaseRows(bool $activeOnly): Collection
    {
        if (!Schema::hasTable('menus')) {
            return collect();
        }

        $query = Menu::query();

        if ($activeOnly && Schema::hasColumn('menus', 'is_active')) {
            $query->where('is_active', true);
        }

        $query->orderBy($this->hasColumn('category_sort') ? 'category_sort' : 'category')
            ->orderBy('category')
            ->orderBy($this->hasColumn('sort') ? 'sort' : 'name')
            ->orderBy('name');

        $select = ['id', 'name', 'category', 'price'];
        if ($this->hasColumn('item_key')) {
            $select[] = 'item_key';
        }
        if ($this->hasColumn('description')) {
            $select[] = 'description';
        }

        return $query->get($select);
    }

    private function configRows(): array
    {
        $path = base_path('config/menu.php');

        try {
            $cfg = is_file($path) ? include $path : (array) config('menu');
            return is_array($cfg) ? $cfg : (array) config('menu');
        } catch (\Throwable $e) {
            return (array) config('menu');
        }
    }

    private function hasColumn(string $column): bool
    {
        static $columns = null;

        if ($columns === null) {
            $columns = Schema::hasTable('menus')
                ? collect(Schema::getColumnListing('menus'))->flip()
                : collect();
        }

        return $columns->has($column);
    }
}
