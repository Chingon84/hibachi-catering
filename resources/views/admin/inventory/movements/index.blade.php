<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin – Stock Movements</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    .page{display:grid;gap:14px}
    .subnav{display:flex;flex-wrap:wrap;gap:8px}
    .subnav a{display:inline-flex;align-items:center;padding:9px 12px;border-radius:999px;border:1px solid var(--border);background:#fff;color:#334155;text-decoration:none;font-size:12px;font-weight:700}
    .subnav a.active{background:#0f172a;border-color:#0f172a;color:#fff}
    .toolbar-grid{display:grid;grid-template-columns:2fr 1fr 1fr auto auto;gap:10px;align-items:end}
    .table-wrap{overflow:auto}
    .table-wide{width:100%;border-collapse:separate;border-spacing:0;min-width:1100px}
    .table-wide th,.table-wide td{padding:12px 14px;text-align:left;vertical-align:top}
    .table-wide thead th{background:#f8fafc;border-bottom:1px solid #e5e7eb;color:#64748b;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.06em}
    .table-wide tbody tr + tr td{border-top:1px solid #eef2f7}
    .delta.plus{color:#166534;font-weight:800}
    .delta.minus{color:#b91c1c;font-weight:800}
    .pager{display:flex;align-items:center;justify-content:flex-end;gap:10px;margin-top:14px}
    .pager .disabled{opacity:.55;pointer-events:none}
    .pager-meta{font-size:13px;color:#64748b}
    @media (max-width: 980px){.toolbar-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media (max-width: 760px){.toolbar-grid{grid-template-columns:1fr}}
  </style>
</head>
<body>
  <div class="container">
    <div class="page">
      <div class="header">
        <h1 class="title" style="margin-right:auto">Stock Movements</h1>
        @if(auth()->user()?->hasPermission('inventory.manage'))
          <a class="btn" href="{{ route('admin.inventory.movements.create') }}">Record Movement</a>
        @endif
      </div>

      @if (session('ok'))
        <div class="card"><div class="card-body" style="color:#166534;font-weight:700">{{ session('ok') }}</div></div>
      @endif

      @include('admin.inventory._subnav')

      <div class="card"><div class="card-body">
        <form method="get" action="{{ route('admin.inventory.movements.index') }}" class="toolbar-grid">
          <div>
            <label class="label" for="q">Search Item</label>
            <input class="input" id="q" name="q" value="{{ $filters['q'] }}" placeholder="Item name or SKU">
          </div>
          <div>
            <label class="label" for="movement_type">Movement Type</label>
            <select class="select" id="movement_type" name="movement_type">
              <option value="">All movement types</option>
              @foreach ($movementTypes as $movementType)
                <option value="{{ $movementType }}" {{ $filters['movement_type'] === $movementType ? 'selected' : '' }}>{{ \Illuminate\Support\Str::headline(str_replace('_', ' ', $movementType)) }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="label" for="reference_type">Reference Type</label>
            <input class="input" id="reference_type" name="reference_type" value="{{ $filters['reference_type'] }}" placeholder="event, van, purchase">
          </div>
          <button class="btn secondary" type="submit">Apply</button>
          <a class="btn secondary" href="{{ route('admin.inventory.movements.index') }}">Reset</a>
        </form>
      </div></div>

      <div class="card">
        <div class="card-body table-wrap">
          <table class="table-wide">
            <thead>
              <tr>
                <th>Date</th>
                <th>Item</th>
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
                @php $plus = $movement->new_stock >= $movement->previous_stock; @endphp
                <tr>
                  <td>{{ $movement->created_at?->format('m/d/Y g:i A') }}</td>
                  <td>
                    <strong>{{ $movement->item?->name ?: 'Inventory item removed' }}</strong>
                    <div class="muted">{{ $movement->item?->sku ?: 'No SKU' }}</div>
                  </td>
                  <td>{{ \Illuminate\Support\Str::headline(str_replace('_', ' ', $movement->movement_type)) }}</td>
                  <td><span class="delta {{ $plus ? 'plus' : 'minus' }}">{{ $plus ? '+' : '-' }}{{ rtrim(rtrim(number_format((float) $movement->quantity, 2), '0'), '.') }}</span></td>
                  <td>{{ rtrim(rtrim(number_format((float) $movement->previous_stock, 2), '0'), '.') }} -> {{ rtrim(rtrim(number_format((float) $movement->new_stock, 2), '0'), '.') }}</td>
                  <td>{{ $movement->reference_type ?: '—' }} @if($movement->reference_id) #{{ $movement->reference_id }} @endif @if($movement->van) • {{ $movement->van->name }} @endif</td>
                  <td>{{ $movement->notes ?: '—' }}</td>
                  <td>{{ $movement->user?->name ?: 'System' }}</td>
                </tr>
              @empty
                <tr><td colspan="8" class="muted">No stock movements match the current filters.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      @include('admin.inventory._pager', ['paginator' => $movements->withQueryString()])
    </div>
  </div>
</body>
</html>
