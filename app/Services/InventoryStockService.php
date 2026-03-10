<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Van;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryStockService
{
    public function createItem(array $data, ?int $userId = null): InventoryItem
    {
        return DB::transaction(function () use ($data, $userId) {
            $initialStock = (float) ($data['current_stock'] ?? 0);
            $data['created_by'] = $userId;
            $data['updated_by'] = $userId;

            $item = InventoryItem::create($data);

            if ($initialStock > 0) {
                $this->createMovement($item, [
                    'movement_type' => 'restock',
                    'quantity' => $initialStock,
                    'previous_stock' => 0,
                    'new_stock' => $initialStock,
                    'reference_type' => 'manual',
                    'notes' => 'Opening stock balance',
                    'created_by' => $userId,
                ]);
            }

            return $item;
        });
    }

    public function updateItem(InventoryItem $item, array $data, ?int $userId = null): InventoryItem
    {
        return DB::transaction(function () use ($item, $data, $userId) {
            $previousStock = (float) $item->current_stock;
            $newStock = (float) ($data['current_stock'] ?? $previousStock);

            $data['updated_by'] = $userId;
            $item->update($data);

            if (round($newStock, 2) !== round($previousStock, 2)) {
                $this->createMovement($item->fresh(), [
                    'movement_type' => 'manual_adjustment',
                    'quantity' => abs($newStock - $previousStock),
                    'previous_stock' => $previousStock,
                    'new_stock' => $newStock,
                    'reference_type' => 'manual',
                    'notes' => 'Stock adjusted from item maintenance form',
                    'created_by' => $userId,
                ]);
            }

            return $item->fresh();
        });
    }

    public function applyMovement(
        InventoryItem $item,
        string $movementType,
        float $quantity,
        ?int $userId = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null,
        ?Van $van = null,
        bool $allowNegative = false,
        ?string $adjustmentDirection = null
    ): InventoryMovement {
        return DB::transaction(function () use (
            $item,
            $movementType,
            $quantity,
            $userId,
            $referenceType,
            $referenceId,
            $notes,
            $van,
            $allowNegative,
            $adjustmentDirection
        ) {
            $item = InventoryItem::query()->lockForUpdate()->findOrFail($item->id);
            $previousStock = (float) $item->current_stock;
            $normalizedQuantity = abs($quantity);
            $delta = $this->resolveDelta($movementType, $normalizedQuantity, $adjustmentDirection);
            $newStock = $previousStock + $delta;

            if (!$allowNegative && $newStock < 0) {
                throw new InvalidArgumentException('Stock cannot be reduced below zero.');
            }

            $item->update([
                'current_stock' => $newStock,
                'updated_by' => $userId,
            ]);

            return $this->createMovement($item, [
                'movement_type' => $movementType,
                'quantity' => $normalizedQuantity,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'van_id' => $van?->id,
                'created_by' => $userId,
            ]);
        });
    }

    private function createMovement(InventoryItem $item, array $data): InventoryMovement
    {
        return $item->movements()->create($data);
    }

    private function resolveDelta(string $movementType, float $quantity, ?string $adjustmentDirection): float
    {
        return match ($movementType) {
            'restock', 'returned_from_event', 'transferred_from_van' => $quantity,
            'assigned_to_event', 'damaged', 'lost', 'transferred_to_van' => -$quantity,
            'manual_adjustment' => ($adjustmentDirection === 'decrease' ? -1 : 1) * $quantity,
            default => throw new InvalidArgumentException('Unsupported movement type.'),
        };
    }
}
