<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin – Inventory</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    .page{display:grid;gap:14px}
    .subnav{display:flex;flex-wrap:wrap;gap:8px}
    .subnav a{display:inline-flex;align-items:center;padding:9px 12px;border-radius:999px;border:1px solid var(--border);background:#fff;color:#334155;text-decoration:none;font-size:12px;font-weight:700}
    .subnav a.active{background:#0f172a;border-color:#0f172a;color:#fff}
    .stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
    .stat-card,.panel{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:16px;box-shadow:0 10px 24px rgba(15,23,42,.04)}
    .stat-label{font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em}
    .stat-value{margin-top:10px;font-size:30px;font-weight:800;color:#0f172a}
    .stat-copy{margin-top:8px;font-size:12px;color:#94a3b8}
    .layout{display:grid;grid-template-columns:minmax(0,1.4fr) minmax(320px,.9fr);gap:14px}
    .panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:14px}
    .panel-title{margin:0;font-size:18px}
    .panel-copy{margin:6px 0 0;color:var(--muted);font-size:13px}
    .action-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
    .quick-link{display:block;padding:14px;border:1px solid var(--border);border-radius:14px;background:#fff;text-decoration:none;color:#111827}
    .quick-link strong{display:block}
    .quick-link span{display:block;margin-top:6px;color:#64748b;font-size:13px}
    .movement-list{display:grid;gap:10px}
    .movement-row{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;padding:12px 14px;border:1px solid #e5e7eb;border-radius:14px;background:#fff}
    .movement-title{font-weight:700}
    .movement-meta{margin-top:4px;color:#64748b;font-size:12px}
    .delta.plus{color:#166534;font-weight:800}
    .delta.minus{color:#b91c1c;font-weight:800}
    .status-pill{display:inline-flex;align-items:center;padding:4px 9px;border-radius:999px;font-size:12px;font-weight:800}
    .status-pill.warn{background:#fff7ed;color:#9a3412}
    .status-pill.danger{background:#fef2f2;color:#b91c1c}
    .status-pill.neutral{background:#eff6ff;color:#1d4ed8}
    @media (max-width: 1080px){.stats{grid-template-columns:repeat(2,minmax(0,1fr))}.layout{grid-template-columns:1fr}}
    @media (max-width: 760px){.stats,.action-grid{grid-template-columns:1fr}}
  </style>
</head>
<body>
  <div class="container">
    <div class="page">
      <div class="header">
        <h1 class="title" style="margin-right:auto">Inventory</h1>
        @if(auth()->user()?->hasPermission('inventory.manage'))
          <a class="btn" href="{{ route('admin.inventory.items.create') }}">Add Inventory Item</a>
        @endif
      </div>

      @include('admin.inventory._subnav')

      <div class="stats">
        <div class="stat-card">
          <div class="stat-label">Total Inventory Items</div>
          <div class="stat-value">{{ number_format($totalInventoryItems) }}</div>
          <div class="stat-copy">Tracked warehouse and event inventory records.</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Low Stock Items</div>
          <div class="stat-value">{{ number_format($lowStockItems) }}</div>
          <div class="stat-copy">Items at or below minimum stock threshold.</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Out Of Stock</div>
          <div class="stat-value">{{ number_format($outOfStockItems) }}</div>
          <div class="stat-copy">Items that cannot currently be allocated.</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Total Vans</div>
          <div class="stat-value">{{ number_format($totalVans) }}</div>
          <div class="stat-copy">Fleet units with operational loadout profiles.</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Vans Needing Attention</div>
          <div class="stat-value">{{ number_format($vansWithMissingEquipment) }}</div>
          <div class="stat-copy">Vans without a clean current loadout snapshot.</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Recent Movements</div>
          <div class="stat-value">{{ number_format($recentMovements->count()) }}</div>
          <div class="stat-copy">Latest warehouse adjustments and van transfers.</div>
        </div>
      </div>

      <div class="layout">
        <section class="panel">
          <div class="panel-head">
            <div>
              <h2 class="panel-title">Recent Stock Movements</h2>
              <p class="panel-copy">Every warehouse stock change is logged here for auditability.</p>
            </div>
            <a class="btn secondary" href="{{ route('admin.inventory.movements.index') }}">View All</a>
          </div>

          @if ($recentMovements->isEmpty())
            <div class="muted">No stock movements recorded yet.</div>
          @else
            <div class="movement-list">
              @foreach ($recentMovements as $movement)
                @php
                  $direction = in_array($movement->movement_type, ['restock', 'returned_from_event', 'transferred_from_van'], true) ? 'plus' : 'minus';
                @endphp
                <div class="movement-row">
                  <div>
                    <div class="movement-title">{{ $movement->item?->name ?: 'Inventory item removed' }}</div>
                    <div class="movement-meta">
                      {{ \Illuminate\Support\Str::headline(str_replace('_', ' ', $movement->movement_type)) }}
                      @if($movement->van)
                        • {{ $movement->van->name }}
                      @endif
                      • {{ $movement->created_at?->format('m/d/Y g:i A') }}
                      • {{ $movement->user?->name ?: 'System' }}
                    </div>
                  </div>
                  <div class="delta {{ $direction }}">{{ $direction === 'plus' ? '+' : '-' }}{{ rtrim(rtrim(number_format((float) $movement->quantity, 2), '0'), '.') }}</div>
                </div>
              @endforeach
            </div>
          @endif
        </section>

        <aside class="panel">
          <div class="panel-head">
            <div>
              <h2 class="panel-title">Quick Actions</h2>
              <p class="panel-copy">Operational shortcuts for warehouse and fleet workflows.</p>
            </div>
          </div>
          <div class="action-grid">
            <a class="quick-link" href="{{ route('admin.inventory.items.create') }}">
              <strong>Add inventory item</strong>
              <span>Create a new warehouse stock record.</span>
            </a>
            <a class="quick-link" href="{{ route('admin.inventory.vans.index') }}">
              <strong>Update van loadout</strong>
              <span>Open a van profile and record grills and equipment counts.</span>
            </a>
            <a class="quick-link" href="{{ route('admin.inventory.movements.create') }}">
              <strong>Record stock movement</strong>
              <span>Log restocks, event usage, and adjustments.</span>
            </a>
            <a class="quick-link" href="{{ route('admin.inventory.alerts.index') }}">
              <strong>View low stock alerts</strong>
              <span>Prioritize items that need replenishment.</span>
            </a>
          </div>

          <div style="display:grid;gap:10px;margin-top:14px">
            <span class="status-pill warn">{{ $lowStockItems }} low stock item{{ $lowStockItems === 1 ? '' : 's' }}</span>
            <span class="status-pill danger">{{ $outOfStockItems }} out of stock item{{ $outOfStockItems === 1 ? '' : 's' }}</span>
            <span class="status-pill neutral">{{ $vansWithMissingEquipment }} van{{ $vansWithMissingEquipment === 1 ? '' : 's' }} need attention</span>
          </div>
        </aside>
      </div>
    </div>
  </div>
</body>
</html>
