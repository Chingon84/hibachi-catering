<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin – Low Stock Alerts</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    .page{display:grid;gap:14px}
    .subnav{display:flex;flex-wrap:wrap;gap:8px}
    .subnav a{display:inline-flex;align-items:center;padding:9px 12px;border-radius:999px;border:1px solid var(--border);background:#fff;color:#334155;text-decoration:none;font-size:12px;font-weight:700}
    .subnav a.active{background:#0f172a;border-color:#0f172a;color:#fff}
    .toolbar-grid{display:grid;grid-template-columns:2fr 1fr auto auto;gap:10px;align-items:end}
    .table-wrap{overflow:auto}
    .table-wide{width:100%;border-collapse:separate;border-spacing:0;min-width:960px}
    .table-wide th,.table-wide td{padding:12px 14px;text-align:left;vertical-align:top}
    .table-wide thead th{background:#f8fafc;border-bottom:1px solid #e5e7eb;color:#64748b;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.06em}
    .table-wide tbody tr + tr td{border-top:1px solid #eef2f7}
    .badge{display:inline-flex;align-items:center;padding:5px 9px;border-radius:999px;font-size:12px;font-weight:800}
    .badge.low{background:#fff7ed;color:#9a3412}
    .badge.out{background:#fef2f2;color:#b91c1c}
    .pager{display:flex;align-items:center;justify-content:flex-end;gap:10px;margin-top:14px}
    .pager .disabled{opacity:.55;pointer-events:none}
    .pager-meta{font-size:13px;color:#64748b}
    @media (max-width: 900px){.toolbar-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media (max-width: 760px){.toolbar-grid{grid-template-columns:1fr}}
  </style>
</head>
<body>
  <div class="container">
    <div class="page">
      <div class="header">
        <h1 class="title" style="margin-right:auto">Low Stock Alerts</h1>
        <a class="btn secondary" href="{{ route('admin.inventory.dashboard') }}">Back to Inventory</a>
      </div>

      @include('admin.inventory._subnav')

      <div class="card"><div class="card-body">
        <form method="get" action="{{ route('admin.inventory.alerts.index') }}" class="toolbar-grid">
          <div>
            <label class="label" for="q">Search</label>
            <input class="input" id="q" name="q" value="{{ $filters['q'] }}" placeholder="Item name or SKU">
          </div>
          <div>
            <label class="label" for="category">Category</label>
            <select class="select" id="category" name="category">
              <option value="">All categories</option>
              @foreach ($categories as $category)
                <option value="{{ $category }}" {{ $filters['category'] === $category ? 'selected' : '' }}>{{ $category }}</option>
              @endforeach
            </select>
          </div>
          <button class="btn secondary" type="submit">Apply</button>
          <a class="btn secondary" href="{{ route('admin.inventory.alerts.index') }}">Reset</a>
        </form>
      </div></div>

      <div class="card">
        <div class="card-body table-wrap">
          <table class="table-wide">
            <thead>
              <tr>
                <th>Item</th>
                <th>Category</th>
                <th>Current Stock</th>
                <th>Minimum Stock</th>
                <th>Shortage</th>
                <th>Status</th>
                <th>Quick Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($items as $item)
                @php
                  $out = (float) $item->current_stock <= 0;
                  $shortage = max(0, (float) $item->minimum_stock - (float) $item->current_stock);
                @endphp
                <tr>
                  <td>
                    <strong>{{ $item->name }}</strong>
                    <div class="muted">{{ $item->sku ?: 'No SKU' }}</div>
                  </td>
                  <td>{{ $item->category }}</td>
                  <td>{{ rtrim(rtrim(number_format((float) $item->current_stock, 2), '0'), '.') }} {{ $item->unit_type }}</td>
                  <td>{{ rtrim(rtrim(number_format((float) $item->minimum_stock, 2), '0'), '.') }}</td>
                  <td>{{ rtrim(rtrim(number_format($shortage, 2), '0'), '.') }}</td>
                  <td><span class="badge {{ $out ? 'out' : 'low' }}">{{ $out ? 'Out of stock' : 'Low stock' }}</span></td>
                  <td><a class="btn secondary" href="{{ route('admin.inventory.movements.create', ['item_id' => $item->id, 'movement_type' => 'restock']) }}">Restock</a></td>
                </tr>
              @empty
                <tr><td colspan="7" class="muted">No low stock items right now.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      @include('admin.inventory._pager', ['paginator' => $items->withQueryString()])
    </div>
  </div>
</body>
</html>
