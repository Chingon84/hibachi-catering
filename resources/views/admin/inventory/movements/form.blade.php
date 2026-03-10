<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin – Record Stock Movement</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    .wrap{max-width:980px;margin:24px auto;padding:0 12px 24px}
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
          <h1 class="title">Record Stock Movement</h1>
          <p class="subtitle">Log warehouse usage, event returns, manual adjustments, and warehouse-to-van transfers with full stock history.</p>
        </div>
        <a class="btn secondary" href="{{ route('admin.inventory.movements.index') }}">Back</a>
      </div>

      @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
      @endif

      <form method="post" action="{{ route('admin.inventory.movements.store') }}">
        @csrf
        <div class="grid">
          <div class="field">
            <label class="label" for="inventory_item_id">Inventory Item</label>
            <select class="select" id="inventory_item_id" name="inventory_item_id" required>
              <option value="">Select item</option>
              @foreach ($items as $item)
                <option value="{{ $item->id }}" {{ (string) old('inventory_item_id', $selectedItemId) === (string) $item->id ? 'selected' : '' }}>
                  {{ $item->name }} ({{ $item->sku ?: 'No SKU' }}) - {{ rtrim(rtrim(number_format((float) $item->current_stock, 2), '0'), '.') }} in stock
                </option>
              @endforeach
            </select>
          </div>
          <div class="field">
            <label class="label" for="movement_type">Movement Type</label>
            <select class="select" id="movement_type" name="movement_type" required onchange="toggleMovementFields(this.value)">
              @foreach ($movementTypes as $movementType)
                <option value="{{ $movementType }}" {{ old('movement_type', $selectedMovementType) === $movementType ? 'selected' : '' }}>{{ \Illuminate\Support\Str::headline(str_replace('_', ' ', $movementType)) }}</option>
              @endforeach
            </select>
          </div>
          <div class="field">
            <label class="label" for="quantity">Quantity</label>
            <input class="input" type="number" step="0.01" min="0.01" id="quantity" name="quantity" value="{{ old('quantity', 1) }}" required>
          </div>
          <div class="field" id="adjustment-direction-wrap">
            <label class="label" for="adjustment_direction">Adjustment Direction</label>
            <select class="select" id="adjustment_direction" name="adjustment_direction">
              <option value="increase" {{ old('adjustment_direction', 'increase') === 'increase' ? 'selected' : '' }}>Increase stock</option>
              <option value="decrease" {{ old('adjustment_direction') === 'decrease' ? 'selected' : '' }}>Decrease stock</option>
            </select>
          </div>
          <div class="field" id="van-wrap">
            <label class="label" for="van_id">Van</label>
            <select class="select" id="van_id" name="van_id">
              <option value="">Select van</option>
              @foreach ($vans as $van)
                <option value="{{ $van->id }}" {{ old('van_id') == $van->id ? 'selected' : '' }}>{{ $van->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="field">
            <label class="label" for="reference_type">Reference Type</label>
            <input class="input" id="reference_type" name="reference_type" value="{{ old('reference_type') }}" placeholder="event, van, purchase, manual">
          </div>
          <div class="field">
            <label class="label" for="reference_id">Reference ID</label>
            <input class="input" type="number" min="1" id="reference_id" name="reference_id" value="{{ old('reference_id') }}">
          </div>
          <div class="field span-2">
            <label class="label" for="notes">Notes</label>
            <textarea class="input" id="notes" name="notes" rows="5" style="min-height:140px">{{ old('notes') }}</textarea>
            <span class="hint">Transfers to and from vans are limited to reusable items marked as van-assignable.</span>
          </div>
          <div class="field span-2">
            <label style="display:flex;align-items:center;gap:10px;padding:10px 12px;border:1px solid var(--border);border-radius:10px;background:#fff">
              <input type="checkbox" name="allow_negative" value="1" {{ old('allow_negative') ? 'checked' : '' }}>
              <span>Allow movement to reduce stock below zero for exceptional admin adjustments.</span>
            </label>
          </div>
        </div>
        <div class="row">
          <a class="btn secondary" href="{{ route('admin.inventory.movements.index') }}">Cancel</a>
          <button class="btn" type="submit">Save Movement</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function toggleMovementFields(type) {
      const manualWrap = document.getElementById('adjustment-direction-wrap');
      const vanWrap = document.getElementById('van-wrap');
      const isManual = type === 'manual_adjustment';
      const isTransfer = type === 'transferred_to_van' || type === 'transferred_from_van';
      manualWrap.style.display = isManual ? '' : 'none';
      vanWrap.style.display = isTransfer ? '' : 'none';
    }
    toggleMovementFields(document.getElementById('movement_type').value);
  </script>
</body>
</html>
