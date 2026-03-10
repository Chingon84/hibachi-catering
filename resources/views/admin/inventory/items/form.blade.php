<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $item->exists ? 'Edit Inventory Item' : 'Add Inventory Item' }}</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    .wrap{max-width:1100px;margin:24px auto;padding:0 12px 24px}
    .panel{padding:22px}
    .panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:20px}
    .title{margin:0;font-size:28px;line-height:1.08}
    .subtitle{margin:8px 0 0;color:var(--muted);font-size:14px}
    .grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
    .field{display:flex;flex-direction:column;gap:6px}
    .field.span-2{grid-column:1 / -1}
    .row{display:flex;gap:10px;margin-top:20px}
    .error{margin-bottom:12px;color:#b91c1c;font-weight:700}
    .hint{font-size:12px;color:#64748b}
    @media (max-width: 760px){.grid{grid-template-columns:1fr}.panel-head{flex-direction:column}}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card panel">
      <div class="panel-head">
        <div>
          <h1 class="title">{{ $item->exists ? 'Edit Inventory Item' : 'Add Inventory Item' }}</h1>
          <p class="subtitle">Manage warehouse stock, low stock thresholds, and van transfer compatibility from one record.</p>
        </div>
        <a class="btn secondary" href="{{ $item->exists ? route('admin.inventory.items.show', $item->id) : route('admin.inventory.items.index') }}">Back</a>
      </div>

      @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
      @endif

      <form method="post" action="{{ $item->exists ? route('admin.inventory.items.update', $item->id) : route('admin.inventory.items.store') }}">
        @csrf
        <div class="grid">
          <div class="field">
            <label class="label" for="name">Item Name</label>
            <input class="input" id="name" name="name" value="{{ old('name', $item->name) }}" required>
          </div>
          <div class="field">
            <label class="label" for="sku">SKU / Internal Code</label>
            <input class="input" id="sku" name="sku" value="{{ old('sku', $item->sku) }}">
          </div>
          <div class="field">
            <label class="label" for="category">Category</label>
            <select class="select" id="category" name="category" required>
              <option value="">Select category</option>
              @foreach ($categories as $category)
                <option value="{{ $category }}" {{ old('category', $item->category) === $category ? 'selected' : '' }}>{{ $category }}</option>
              @endforeach
            </select>
          </div>
          <div class="field">
            <label class="label" for="item_type">Item Type</label>
            <select class="select" id="item_type" name="item_type" required>
              <option value="">Select type</option>
              @foreach ($itemTypes as $itemType)
                <option value="{{ $itemType }}" {{ old('item_type', $item->item_type) === $itemType ? 'selected' : '' }}>{{ \Illuminate\Support\Str::headline($itemType) }}</option>
              @endforeach
            </select>
          </div>
          <div class="field">
            <label class="label" for="unit_type">Unit Type</label>
            <select class="select" id="unit_type" name="unit_type" required>
              @foreach ($unitTypes as $unitType)
                <option value="{{ $unitType }}" {{ old('unit_type', $item->unit_type ?: 'pieces') === $unitType ? 'selected' : '' }}>{{ ucfirst($unitType) }}</option>
              @endforeach
            </select>
          </div>
          <div class="field">
            <label class="label" for="status">Status</label>
            <select class="select" id="status" name="status" required>
              @foreach ($statuses as $status)
                <option value="{{ $status }}" {{ old('status', $item->status ?: 'active') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
              @endforeach
            </select>
          </div>
          <div class="field">
            <label class="label" for="current_stock">Current Stock</label>
            <input class="input" type="number" step="0.01" id="current_stock" name="current_stock" value="{{ old('current_stock', $item->current_stock ?? 0) }}" required>
          </div>
          <div class="field">
            <label class="label" for="minimum_stock">Minimum Stock</label>
            <input class="input" type="number" step="0.01" min="0" id="minimum_stock" name="minimum_stock" value="{{ old('minimum_stock', $item->minimum_stock ?? 0) }}" required>
          </div>
          <div class="field">
            <label class="label" for="reorder_level">Reorder Level</label>
            <input class="input" type="number" step="0.01" min="0" id="reorder_level" name="reorder_level" value="{{ old('reorder_level', $item->reorder_level) }}">
          </div>
          <div class="field">
            <label class="label" for="storage_location">Storage Location</label>
            <input class="input" id="storage_location" name="storage_location" value="{{ old('storage_location', $item->storage_location) }}">
          </div>
          <div class="field span-2">
            <label class="label" for="allow_van_assignment">Van Assignment</label>
            <input type="hidden" name="allow_van_assignment" value="0">
            <label style="display:flex;align-items:center;gap:10px;padding:10px 12px;border:1px solid var(--border);border-radius:10px;background:#fff">
              <input type="checkbox" name="allow_van_assignment" value="1" {{ old('allow_van_assignment', $item->allow_van_assignment) ? 'checked' : '' }}>
              <span>Allow transfer to van inventory for reusable equipment.</span>
            </label>
            <span class="hint">Consumables should remain warehouse-focused even if you track them here.</span>
          </div>
          <div class="field span-2">
            <label class="label" for="notes">Notes</label>
            <textarea class="input" id="notes" name="notes" rows="5" style="min-height:140px">{{ old('notes', $item->notes) }}</textarea>
          </div>
        </div>
        <div class="row">
          <a class="btn secondary" href="{{ $item->exists ? route('admin.inventory.items.show', $item->id) : route('admin.inventory.items.index') }}">Cancel</a>
          <button class="btn" type="submit">{{ $item->exists ? 'Save Changes' : 'Save Item' }}</button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
