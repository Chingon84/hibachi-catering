<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $van->exists ? 'Edit Van' : 'Add Van' }}</title>
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
    @media (max-width: 760px){.grid{grid-template-columns:1fr}.panel-head{flex-direction:column}}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card panel">
      <div class="panel-head">
        <div>
          <h1 class="title">{{ $van->exists ? 'Edit Van' : 'Add Van' }}</h1>
          <p class="subtitle">Manage the core van record used by the operational loadout dashboard.</p>
        </div>
        <a class="btn secondary" href="{{ route('admin.inventory.vans.index') }}">Back</a>
      </div>

      @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
      @endif

      <form method="post" action="{{ $van->exists ? route('admin.inventory.vans.update', $van->id) : route('admin.inventory.vans.store') }}">
        @csrf
        <div class="grid">
          <div class="field">
            <label class="label" for="van_number">Van Number</label>
            <select class="select" id="van_number" name="van_number" required>
              <option value="">Select van number</option>
              @foreach (range(1, 20) as $number)
                <option value="{{ $number }}" {{ (string) old('van_number', $van->van_number) === (string) $number ? 'selected' : '' }}>Van {{ $number }}</option>
              @endforeach
            </select>
          </div>
          <div class="field">
            <label class="label" for="code">Internal Code</label>
            <input class="input" id="code" name="code" value="{{ old('code', $van->code) }}">
          </div>
          <div class="field">
            <label class="label" for="license_plate">License Plate</label>
            <input class="input" id="license_plate" name="license_plate" value="{{ old('license_plate', $van->license_plate) }}">
          </div>
          <div class="field">
            <label class="label" for="status">Status</label>
            <select class="select" id="status" name="status" required>
              @foreach ($statuses as $status)
                <option value="{{ $status }}" {{ old('status', $van->status ?: 'active') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
              @endforeach
            </select>
          </div>
          <div class="field span-2">
            <label class="label" for="notes">Notes</label>
            <textarea class="input" id="notes" name="notes" rows="5" style="min-height:140px">{{ old('notes', $van->notes) }}</textarea>
          </div>
        </div>
        <div class="row">
          <a class="btn secondary" href="{{ route('admin.inventory.vans.index') }}">Cancel</a>
          <button class="btn" type="submit">{{ $van->exists ? 'Save Changes' : 'Save Van' }}</button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
