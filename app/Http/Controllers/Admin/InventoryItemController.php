<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Services\InventoryStockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class InventoryItemController extends Controller
{
    public function __construct(private readonly InventoryStockService $stockService)
    {
    }

    public function index(Request $request)
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'category' => trim((string) $request->query('category', '')),
            'status' => trim((string) $request->query('status', '')),
            'item_type' => trim((string) $request->query('item_type', '')),
            'stock' => trim((string) $request->query('stock', '')),
        ];

        $items = InventoryItem::query()
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->where(function ($nested) use ($filters) {
                    $nested->where('name', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('sku', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('notes', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('storage_location', 'like', '%' . $filters['q'] . '%');
                });
            })
            ->when($filters['category'] !== '', fn ($query) => $query->where('category', $filters['category']))
            ->when($filters['status'] !== '', fn ($query) => $query->where('status', $filters['status']))
            ->when($filters['item_type'] !== '', fn ($query) => $query->where('item_type', $filters['item_type']))
            ->when($filters['stock'] === 'low', fn ($query) => $query->whereColumn('current_stock', '<=', 'minimum_stock'))
            ->when($filters['stock'] === 'out', fn ($query) => $query->where('current_stock', '<=', 0))
            ->orderBy('name')
            ->paginate(15);

        return view('admin.inventory.items.index', [
            'items' => $items,
            'filters' => $filters,
            'categories' => InventoryItem::CATEGORIES,
            'itemTypes' => InventoryItem::ITEM_TYPES,
            'statuses' => InventoryItem::STATUSES,
        ]);
    }

    public function create()
    {
        return view('admin.inventory.items.form', [
            'item' => new InventoryItem(),
            'categories' => InventoryItem::CATEGORIES,
            'unitTypes' => InventoryItem::UNIT_TYPES,
            'itemTypes' => InventoryItem::ITEM_TYPES,
            'statuses' => InventoryItem::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $item = $this->stockService->createItem(
            $this->validatedData($request),
            Auth::id()
        );

        return redirect()->route('admin.inventory.items.show', $item->id)->with('ok', 'Inventory item created successfully.');
    }

    public function show($id)
    {
        $item = InventoryItem::with(['creator:id,name', 'updater:id,name'])->findOrFail($id);
        $movements = $item->movements()->with(['user:id,name', 'van:id,name'])->paginate(12);
        $vanAssignments = $item->vanAssignments()->with(['van:id,name', 'checkedBy:id,name'])->orderByDesc('updated_at')->get();

        return view('admin.inventory.items.show', compact('item', 'movements', 'vanAssignments'));
    }

    public function edit($id)
    {
        return view('admin.inventory.items.form', [
            'item' => InventoryItem::findOrFail($id),
            'categories' => InventoryItem::CATEGORIES,
            'unitTypes' => InventoryItem::UNIT_TYPES,
            'itemTypes' => InventoryItem::ITEM_TYPES,
            'statuses' => InventoryItem::STATUSES,
        ]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $item = InventoryItem::findOrFail($id);

        $this->stockService->updateItem($item, $this->validatedData($request, $item), Auth::id());

        return redirect()->route('admin.inventory.items.show', $item->id)->with('ok', 'Inventory item updated successfully.');
    }

    public function destroy($id): RedirectResponse
    {
        InventoryItem::findOrFail($id)->delete();

        return redirect()->route('admin.inventory.items.index')->with('ok', 'Inventory item archived successfully.');
    }

    private function validatedData(Request $request, ?InventoryItem $item = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('inventory_items', 'sku')->ignore($item?->id)],
            'category' => ['required', 'string', Rule::in(InventoryItem::CATEGORIES)],
            'unit_type' => ['required', 'string', Rule::in(InventoryItem::UNIT_TYPES)],
            'current_stock' => ['required', 'numeric', 'min:0'],
            'minimum_stock' => ['required', 'numeric', 'min:0'],
            'reorder_level' => ['nullable', 'numeric', 'min:0'],
            'item_type' => ['required', 'string', Rule::in(InventoryItem::ITEM_TYPES)],
            'allow_van_assignment' => ['sometimes', 'boolean'],
            'status' => ['required', 'string', Rule::in(InventoryItem::STATUSES)],
            'storage_location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]) + [
            'allow_van_assignment' => (bool) $request->boolean('allow_van_assignment'),
        ];
    }
}
