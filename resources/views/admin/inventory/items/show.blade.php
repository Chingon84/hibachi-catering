<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin – Inventory Item</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    .page{display:grid;gap:14px}
    .subnav{display:flex;flex-wrap:wrap;gap:8px}
    .subnav a{display:inline-flex;align-items:center;padding:9px 12px;border-radius:999px;border:1px solid var(--border);background:#fff;color:#334155;text-decoration:none;font-size:12px;font-weight:700}
    .subnav a.active{background:#0f172a;border-color:#0f172a;color:#fff}
    .hero{display:grid;grid-template-columns:minmax(0,1.2fr) minmax(260px,.8fr);gap:14px}
    .panel{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:16px;box-shadow:0 10px 24px rgba(15,23,42,.04)}
    .eyebrow{font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#64748b}
    .name{margin:10px 0 0;font-size:28px;line-height:1.05}
    .copy{margin:8px 0 0;color:var(--muted);font-size:14px}
    .stat-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
    .stat{padding:12px;border:1px solid #e5e7eb;border-radius:14px;background:#fff}
    .stat strong{display:block;font-size:24px}
    .meta-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
    .meta{padding:12px;border:1px solid #e5e7eb;border-radius:14px;background:#fff}
    .meta-label{font-size:12px;color:#64748b;font-weight:700;text-transform:uppercase}
    .meta-value{margin-top:8px;font-size:14px;font-weight:700;color:#0f172a}
    .table-wrap{overflow:auto}
    .table-wide{width:100%;border-collapse:separate;border-spacing:0;min-width:920px}
    .table-wide th,.table-wide td{padding:12px 14px;text-align:left;vertical-align:top}
    .table-wide thead th{background:#f8fafc;border-bottom:1px solid #e5e7eb;color:#64748b;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.06em}
    .table-wide tbody tr + tr td{border-top:1px solid #eef2f7}
    .badge{display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;font-size:12px;font-weight:800}
    .badge.healthy{background:#ecfdf5;color:#166534}
    .badge.low{background:#fff7ed;color:#9a3412}
    .badge.out{background:#fef2f2;color:#b91c1c}
    .badge.good{background:#ecfdf5;color:#166534}
    .badge.damaged,.badge.needs-replacement{background:#fff7ed;color:#9a3412}
    .badge.missing{background:#fef2f2;color:#b91c1c}
    .pager{display:flex;align-items:center;justify-content:flex-end;gap:10px;margin-top:14px}
    .pager .disabled{opacity:.55;pointer-events:none}
    .pager-meta{font-size:13px;color:#64748b}
    @media (max-width: 900px){.hero,.meta-grid,.stat-grid{grid-template-columns:1fr}}
  </style>
</head>
<body>
  <div class="container">
    <div class="page">
      <div class="header">
        <h1 class="title" style="margin-right:auto">Inventory Item</h1>
        <a class="btn secondary" href="{{ route('admin.inventory.items.index') }}">Back to Event Inventory</a>
      </div>

      @if (session('ok'))
        <div class="card"><div class="card-body" style="color:#166534;font-weight:700">{{ session('ok') }}</div></div>
      @endif

      @include('admin.inventory._subnav')

      <div class="hero">
        <section class="panel">
          <div class="eyebrow">{{ $item->category }} @if($item->sku) • {{ $item->sku }} @endif</div>
          <h2 class="name">{{ $item->name }}</h2>
          <p class="copy">{{ $item->notes ?: 'No internal notes for this item yet.' }}</p>
          <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:16px">
            <span class="badge {{ $item->stockStatus() }}">{{ $item->stockStatus() === 'out' ? 'Out of stock' : ($item->stockStatus() === 'low' ? 'Low stock' : 'Healthy stock') }}</span>
            <span class="badge {{ $item->canAssignToVan() ? 'healthy' : 'low' }}">{{ $item->canAssignToVan() ? 'Van eligible' : 'Warehouse only' }}</span>
          </div>
        </section>
        <section class="panel">
          <div class="stat-grid">
            <div class="stat">
              <span class="eyebrow">Current Stock</span>
              <strong>{{ rtrim(rtrim(number_format((float) $item->current_stock, 2), '0'), '.') }}</strong>
              <span class="copy">{{ $item->unit_type }}</span>
            </div>
            <div class="stat">
              <span class="eyebrow">Minimum Stock</span>
              <strong>{{ rtrim(rtrim(number_format((float) $item->minimum_stock, 2), '0'), '.') }}</strong>
              <span class="copy">Alert threshold</span>
            </div>
          </div>
          @if(auth()->user()?->hasPermission('inventory.manage'))
            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px">
              <a class="btn" href="{{ route('admin.inventory.items.edit', $item->id) }}">Edit Item</a>
              <a class="btn secondary" href="{{ route('admin.inventory.movements.create', ['item_id' => $item->id]) }}">Record Movement</a>
              <form method="post" action="{{ route('admin.inventory.items.delete', $item->id) }}" onsubmit="return confirm('Archive this inventory item?')">
                @csrf
                <button class="btn secondary" type="submit">Archive</button>
              </form>
            </div>
          @endif
        </section>
      </div>

      <section class="panel">
        <h2 style="margin:0 0 14px;font-size:18px">Item Details</h2>
        <div class="meta-grid">
          <div class="meta"><div class="meta-label">Item Type</div><div class="meta-value">{{ \Illuminate\Support\Str::headline($item->item_type) }}</div></div>
          <div class="meta"><div class="meta-label">Status</div><div class="meta-value">{{ ucfirst($item->status) }}</div></div>
          <div class="meta"><div class="meta-label">Reorder Level</div><div class="meta-value">{{ $item->reorder_level !== null ? rtrim(rtrim(number_format((float) $item->reorder_level, 2), '0'), '.') : 'Not set' }}</div></div>
          <div class="meta"><div class="meta-label">Storage Location</div><div class="meta-value">{{ $item->storage_location ?: 'Not set' }}</div></div>
          <div class="meta"><div class="meta-label">Created By</div><div class="meta-value">{{ $item->creator?->name ?: 'System' }}</div></div>
          <div class="meta"><div class="meta-label">Updated By</div><div class="meta-value">{{ $item->updater?->name ?: 'System' }}</div></div>
        </div>
      </section>

      <section class="panel">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px">
          <h2 style="margin:0;font-size:18px">Van Assignments</h2>
          <a class="btn secondary" href="{{ route('admin.inventory.vans.index') }}">Open Van Inventory</a>
        </div>
        <div class="table-wrap">
          <table class="table-wide">
            <thead>
              <tr>
                <th>Van</th>
                <th>Assigned</th>
                <th>Present</th>
                <th>Condition</th>
                <th>Last Checked</th>
                <th>Checked By</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($vanAssignments as $assignment)
                <tr>
                  <td><a href="{{ route('admin.inventory.vans.show', $assignment->van_id) }}">{{ $assignment->van?->name }}</a></td>
                  <td>{{ rtrim(rtrim(number_format((float) $assignment->quantity_assigned, 2), '0'), '.') }}</td>
                  <td>{{ rtrim(rtrim(number_format((float) $assignment->quantity_present, 2), '0'), '.') }}</td>
                  <td><span class="badge {{ str_replace(' ', '-', $assignment->condition_status) }}">{{ ucfirst($assignment->condition_status) }}</span></td>
                  <td>{{ $assignment->last_checked_at?->format('m/d/Y g:i A') ?: 'Not checked' }}</td>
                  <td>{{ $assignment->checkedBy?->name ?: '—' }}</td>
                </tr>
              @empty
                <tr><td colspan="6" class="muted">This item is not assigned to any vans.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </section>

      <section class="panel">
        <h2 style="margin:0 0 14px;font-size:18px">Movement History</h2>
        <div class="table-wrap">
          <table class="table-wide">
            <thead>
              <tr>
                <th>Date</th>
                <th>Movement</th>
                <th>Quantity</th>
                <th>Stock Change</th>
                <th>Reference</th>
                <th>Notes</th>
                <th>Created By</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($movements as $movement)
                <tr>
                  <td>{{ $movement->created_at?->format('m/d/Y g:i A') }}</td>
                  <td>{{ \Illuminate\Support\Str::headline(str_replace('_', ' ', $movement->movement_type)) }}</td>
                  <td>{{ rtrim(rtrim(number_format((float) $movement->quantity, 2), '0'), '.') }}</td>
                  <td>{{ rtrim(rtrim(number_format((float) $movement->previous_stock, 2), '0'), '.') }} -> {{ rtrim(rtrim(number_format((float) $movement->new_stock, 2), '0'), '.') }}</td>
                  <td>{{ $movement->reference_type ? ucfirst($movement->reference_type) : '—' }}{{ $movement->van ? ' • ' . $movement->van->name : '' }}</td>
                  <td>{{ $movement->notes ?: '—' }}</td>
                  <td>{{ $movement->user?->name ?: 'System' }}</td>
                </tr>
              @empty
                <tr><td colspan="7" class="muted">No movement history recorded yet.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @include('admin.inventory._pager', ['paginator' => $movements->withQueryString()])
      </section>
    </div>
  </div>
</body>
</html>
