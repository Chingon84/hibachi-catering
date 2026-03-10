<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Van;
use App\Services\InventoryStockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class InventoryMovementController extends Controller
{
    public function __construct(private readonly InventoryStockService $stockService)
    {
    }

    public function index(Request $request)
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'movement_type' => trim((string) $request->query('movement_type', '')),
            'reference_type' => trim((string) $request->query('reference_type', '')),
        ];

        $movements = InventoryMovement::query()
            ->with(['item:id,name,sku', 'user:id,name', 'van:id,name'])
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->whereHas('item', function ($itemQuery) use ($filters) {
                    $itemQuery->where('name', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('sku', 'like', '%' . $filters['q'] . '%');
                });
            })
            ->when($filters['movement_type'] !== '', fn ($query) => $query->where('movement_type', $filters['movement_type']))
            ->when($filters['reference_type'] !== '', fn ($query) => $query->where('reference_type', $filters['reference_type']))
            ->latest('created_at')
            ->paginate(20);

        return view('admin.inventory.movements.index', [
            'movements' => $movements,
            'filters' => $filters,
            'movementTypes' => InventoryMovement::TYPES,
        ]);
    }

    public function create(Request $request)
    {
        $itemId = $request->query('item_id');
        $movementType = (string) $request->query('movement_type', 'restock');

        return view('admin.inventory.movements.form', [
            'items' => InventoryItem::query()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'sku', 'current_stock']),
            'selectedItemId' => $itemId ? (int) $itemId : null,
            'selectedMovementType' => in_array($movementType, InventoryMovement::TYPES, true) ? $movementType : 'restock',
            'movementTypes' => InventoryMovement::TYPES,
            'vans' => Van::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'movement_type' => ['required', Rule::in(InventoryMovement::TYPES)],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'reference_type' => ['nullable', 'string', 'max:40'],
            'reference_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'allow_negative' => ['sometimes', 'boolean'],
            'adjustment_direction' => ['nullable', Rule::in(['increase', 'decrease'])],
            'van_id' => ['nullable', 'exists:vans,id'],
        ]);

        $item = InventoryItem::findOrFail($validated['inventory_item_id']);
        $van = !empty($validated['van_id']) ? Van::find($validated['van_id']) : null;

        if (in_array($validated['movement_type'], ['transferred_to_van', 'transferred_from_van'], true)) {
            if (!$item->canAssignToVan()) {
                return back()->withErrors(['movement_type' => 'Only reusable items marked for van assignment can be transferred to vans.'])->withInput();
            }
            if (!$van) {
                return back()->withErrors(['van_id' => 'Select a van for transfer movements.'])->withInput();
            }
        }

        try {
            $this->stockService->applyMovement(
                $item,
                $validated['movement_type'],
                (float) $validated['quantity'],
                Auth::id(),
                ($validated['reference_type'] ?? null) ?: null,
                !empty($validated['reference_id'] ?? null) ? (int) $validated['reference_id'] : null,
                $validated['notes'] ?? null,
                $van,
                (bool) $request->boolean('allow_negative'),
                $validated['adjustment_direction'] ?? null
            );
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['quantity' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('admin.inventory.movements.index')->with('ok', 'Stock movement recorded successfully.');
    }
}
