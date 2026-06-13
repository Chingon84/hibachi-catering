<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Van;
use App\Models\VanChecklist;

class InventoryDashboardController extends Controller
{
    public function index()
    {
        $totalInventoryItems = InventoryItem::query()->count();
        $lowStockItems = InventoryItem::query()
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->count();
        $outOfStockItems = InventoryItem::query()
            ->where('current_stock', '<=', 0)
            ->count();
        $totalVans = Van::query()->count();
        $vansWithMissingEquipment = Van::query()
            ->with('currentLoadout')
            ->get()
            ->filter(fn (Van $van) => !$van->currentLoadout || $van->currentLoadout->van_status !== 'clean')
            ->count();
        $recentMovements = InventoryMovement::query()
            ->with(['item:id,name,sku', 'user:id,name', 'van:id,name'])
            ->latest('created_at')
            ->limit(8)
            ->get();
        $todayChecklists = VanChecklist::query()
            ->whereDate('date_time', today())
            ->count();
        $vansWithIssues = VanChecklist::query()
            ->where(function ($query) {
                $query->where('trip_status', '!=', 'Complete')
                    ->orWhere('clean', 'NO')
                    ->orWhere(function ($notes) {
                        $notes->whereNotNull('notes')
                            ->where('notes', '!=', '');
                    });
            })
            ->distinct('van_number')
            ->count('van_number');
        $cleaningFailures = VanChecklist::query()
            ->where('clean', 'NO')
            ->count();
        $missingEvidence = VanChecklist::query()
            ->whereNull('picture1')
            ->whereNull('picture2')
            ->count();
        $recentChecklistActivity = VanChecklist::query()
            ->latest('date_time')
            ->latest('id')
            ->limit(6)
            ->get();

        return view('admin.inventory.dashboard', compact(
            'totalInventoryItems',
            'lowStockItems',
            'outOfStockItems',
            'totalVans',
            'vansWithMissingEquipment',
            'recentMovements',
            'todayChecklists',
            'vansWithIssues',
            'cleaningFailures',
            'missingEvidence',
            'recentChecklistActivity'
        ));
    }
}
