<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Http\Request;

class InventoryAlertController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'category' => trim((string) $request->query('category', '')),
        ];

        $items = InventoryItem::query()
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->where(function ($nested) use ($filters) {
                    $nested->where('name', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('sku', 'like', '%' . $filters['q'] . '%');
                });
            })
            ->when($filters['category'] !== '', fn ($query) => $query->where('category', $filters['category']))
            ->orderByRaw('current_stock <= 0 desc')
            ->orderByRaw('(minimum_stock - current_stock) desc')
            ->paginate(15);

        return view('admin.inventory.alerts.index', [
            'items' => $items,
            'filters' => $filters,
            'categories' => InventoryItem::CATEGORIES,
        ]);
    }
}
