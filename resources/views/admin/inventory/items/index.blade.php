<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin – Event Inventory</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    .page{display:grid;gap:14px}
    .subnav{display:flex;flex-wrap:wrap;gap:8px}
    .subnav a{display:inline-flex;align-items:center;padding:9px 12px;border-radius:999px;border:1px solid var(--border);background:#fff;color:#334155;text-decoration:none;font-size:12px;font-weight:700}
    .subnav a.active{background:#0f172a;border-color:#0f172a;color:#fff}
    .toolbar-grid{display:grid;grid-template-columns:2fr repeat(4,minmax(0,1fr)) auto auto;gap:10px;align-items:end}
    .table-wrap{overflow:auto}
    .inventory-table{width:100%;border-collapse:separate;border-spacing:0;min-width:1080px}
    .inventory-table th,.inventory-table td{padding:12px 14px;text-align:left;vertical-align:top}
    .inventory-table thead th{background:#f8fafc;border-bottom:1px solid #e5e7eb;color:#64748b;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.06em}
    .inventory-table tbody tr + tr td{border-top:1px solid #eef2f7}
    .inventory-table tbody tr.is-low{background:#fffaf0}
    .inventory-table tbody tr.is-out{background:#fff6f6}
    .item-name{font-weight:800;color:#0f172a;text-decoration:none}
    .meta{display:block;margin-top:4px;color:#64748b;font-size:12px}
    .badge{display:inline-flex;align-items:center;padding:5px 9px;border-radius:999px;font-size:12px;font-weight:800}
    .badge.healthy{background:#ecfdf5;color:#166534}
    .badge.low{background:#fff7ed;color:#9a3412}
    .badge.out{background:#fef2f2;color:#b91c1c}
    .badge.active{background:#eff6ff;color:#1d4ed8}
    .badge.inactive{background:#f1f5f9;color:#475569}
    .pager{display:flex;align-items:center;justify-content:flex-end;gap:10px;margin-top:14px}
    .pager .disabled{opacity:.55;pointer-events:none}
    .pager-meta{font-size:13px;color:#64748b}
    @media (max-width: 1080px){.toolbar-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media (max-width: 760px){.toolbar-grid{grid-template-columns:1fr}}
  </style>
</head>
<body>
  <div class="container">
    <div class="page">
      <div class="header">
        <h1 class="title" style="margin-right:auto">Event Inventory</h1>
        @if(auth()->user()?->hasPermission('inventory.manage'))
          <a class="btn" href="{{ route('admin.inventory.items.create') }}">Add Item</a>
        @endif
      </div>

      @if (session('ok'))
        <div class="card"><div class="card-body" style="color:#166534;font-weight:700">{{ session('ok') }}</div></div>
      @endif

      @include('admin.inventory._subnav')

      <div class="card"><div class="card-body">
        <form method="get" action="{{ route('admin.inventory.items.index') }}" class="toolbar-grid">
          <div>
            <label class="label" for="q">Search</label>
            <input class="input" id="q" name="q" value="{{ $filters['q'] }}" placeholder="Name, SKU, notes, location">
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
          <div>
            <label class="label" for="item_type">Item Type</label>
            <select class="select" id="item_type" name="item_type">
              <option value="">All types</option>
              @foreach ($itemTypes as $itemType)
                <option value="{{ $itemType }}" {{ $filters['item_type'] === $itemType ? 'selected' : '' }}>{{ \Illuminate\Support\Str::headline($itemType) }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="label" for="status">Status</label>
            <select class="select" id="status" name="status">
              <option value="">All statuses</option>
              @foreach ($statuses as $status)
                <option value="{{ $status }}" {{ $filters['status'] === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="label" for="stock">Stock State</label>
            <select class="select" id="stock" name="stock">
              <option value="">All</option>
              <option value="low" {{ $filters['stock'] === 'low' ? 'selected' : '' }}>Low stock</option>
              <option value="out" {{ $filters['stock'] === 'out' ? 'selected' : '' }}>Out of stock</option>
            </select>
          </div>
          <button class="btn secondary" type="submit">Apply</button>
          <a class="btn secondary" href="{{ route('admin.inventory.items.index') }}">Reset</a>
        </form>
      </div></div>

      <div class="card">
        <div class="card-body table-wrap">
          <table class="inventory-table">
            <thead>
              <tr>
                <th>Item</th>
                <th>Category</th>
                <th>Type</th>
                <th>Current Stock</th>
                <th>Minimum</th>
                <th>Status</th>
                <th>Van Eligible</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($items as $item)
                @php $stockStatus = $item->stockStatus(); @endphp
                <tr class="{{ $stockStatus === 'out' ? 'is-out' : ($stockStatus === 'low' ? 'is-low' : '') }}">
                  <td>
                    <a class="item-name" href="{{ route('admin.inventory.items.show', $item->id) }}">{{ $item->name }}</a>
                    <span class="meta">{{ $item->sku ?: 'No SKU' }} @if($item->storage_location) • {{ $item->storage_location }} @endif</span>
                  </td>
                  <td>{{ $item->category }}</td>
                  <td>{{ \Illuminate\Support\Str::headline($item->item_type) }}</td>
                  <td>{{ rtrim(rtrim(number_format((float) $item->current_stock, 2), '0'), '.') }} {{ $item->unit_type }}</td>
                  <td>{{ rtrim(rtrim(number_format((float) $item->minimum_stock, 2), '0'), '.') }}</td>
                  <td>
                    <span class="badge {{ $stockStatus }}">{{ $stockStatus === 'out' ? 'Out of stock' : ($stockStatus === 'low' ? 'Low stock' : 'Healthy') }}</span>
                    <span class="meta"><span class="badge {{ $item->status }}">{{ ucfirst($item->status) }}</span></span>
                  </td>
                  <td>{{ $item->canAssignToVan() ? 'Yes' : 'No' }}</td>
                  <td>
                    <div style="display:flex;gap:8px">
                      <a class="btn secondary" href="{{ route('admin.inventory.items.show', $item->id) }}">View</a>
                      @if(auth()->user()?->hasPermission('inventory.manage'))
                        <a class="btn secondary" href="{{ route('admin.inventory.items.edit', $item->id) }}">Edit</a>
                      @endif
                    </div>
                  </td>
                </tr>
              @empty
                <tr><td colspan="8" class="muted">No inventory items match the current filters.</td></tr>
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
