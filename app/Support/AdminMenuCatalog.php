<?php

namespace App\Support;

use App\Models\Menu;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
        DB::transaction(function () use ($items) {
            $submittedKeys = [];
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

                    Menu::query()->updateOrCreate(
                        ['item_key' => $key],
                        [
                            'name' => $name,
                            'description' => MenuLabel::standardizeText(trim((string) ($row['desc'] ?? $row['description'] ?? ''))),
                            'category' => $categoryName,
                            'category_sort' => $categorySort,
                            'price' => round((float) ($row['price'] ?? 0), 2),
                            'is_active' => true,
                            'sort' => $rowSort,
                        ]
                    );

                    $submittedKeys[] = $key;
                    $rowSort++;
                }

                $categorySort++;
            }

            // Items removed from the form are deactivated (not hard-deleted)
            // so historical references and IDs are preserved.
            if (!empty($submittedKeys)) {
                Menu::query()
                    ->whereNotIn('item_key', $submittedKeys)
                    ->update(['is_active' => false]);
            }
        });
    }

    private function databaseRows(bool $activeOnly): Collection
    {
        $query = Menu::query();

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        $query->orderBy('category_sort')
            ->orderBy('category')
            ->orderBy('sort')
            ->orderBy('name');

        return $query->get(['id', 'item_key', 'name', 'description', 'category', 'price']);
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

}
