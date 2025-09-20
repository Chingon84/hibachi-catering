<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reservation – Event</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    :root{--brand:#b21e27;--muted:#6b7280;--border:#e5e7eb}
    .container{max-width:1100px;margin:20px auto;padding:0 12px}
    .head{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
    .title{font-size:22px;margin:0}
    .btn{appearance:none;border:0;background:var(--brand);color:#fff;border-radius:10px;padding:10px 14px;cursor:pointer;font-weight:600;text-decoration:none}
    .btn.secondary{background:#4b5563}
    .grid{display:grid;grid-template-columns:2fr 1fr;gap:12px}
    @media (max-width: 900px){.grid{grid-template-columns:1fr}}
    .card{background:#fff;border:1px solid var(--border);border-radius:14px;box-shadow:0 6px 18px rgba(0,0,0,.04)}
    .card-body{padding:14px}
    .row{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
    .input,.select,textarea{width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:10px;background:#fff}
    label{font-weight:600}
    .muted{color:var(--muted)}
    .inline{display:flex;gap:10px}
    .inline > div{flex:1}
    .tabs{display:flex;gap:18px;border-bottom:1px solid var(--border);margin:-4px -14px 12px;padding:0 14px}
    .tab{padding:10px 0;font-weight:600;color:#374151;text-decoration:none;border-bottom:2px solid transparent}
    .tab.active{border-color:#111}
    .kv{display:grid;grid-template-columns:1fr auto;gap:4px 12px}
    /* Small icon button (trash) */
    .icon-btn{appearance:none;border:0;background:#4b5563;color:#fff;border-radius:8px;padding:6px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer}
    .icon-btn:hover{background:#374151}
    .icon-btn.danger{background:#b21e27}
    .icon-btn.danger:hover{background:#9a1a22}
    .icon-btn svg{width:16px;height:16px;display:block}
    table th, table td{padding:6px 6px}
  </style>
  @php $fmt = fn($n)=>'$'.number_format((float)$n,2); @endphp
</head>
<body>
  <div class="container">
    <div class="head">
      <a href="{{ route('admin.calendar') }}" class="btn secondary">Back</a>
      <h1 class="title" style="margin:0 12px 0 auto">Event Details</h1>
      <button class="btn" form="eventForm">Save</button>
    </div>

    @if (session('ok'))
      <div class="card" style="margin-bottom:10px"><div class="card-body"><div class="alert success">{{ session('ok') }}</div></div></div>
    @endif
    @if ($errors->any())
      <div class="card" style="margin-bottom:10px"><div class="card-body"><div class="alert error">{{ $errors->first() }}</div></div></div>
    @endif

    <div class="grid">
      <div class="card"><div class="card-body">
        <nav class="tabs">
          <a class="tab active" href="#">Step 1</a>
        </nav>
        <form method="post" action="{{ route('admin.reservations.update',['id'=>$r->id]) }}" id="eventForm">
          @csrf
          <input type="hidden" name="back" value="{{ route('admin.reservations.event',['id'=>$r->id]) }}">
          <div class="inline" style="margin-bottom:10px">
            <div>
              <label>Date</label>
              <input class="input" type="date" name="date" value="{{ old('date', $r->date?->toDateString()) }}" required>
            </div>
            <div>
              <label>Time</label>
              <input class="input" type="time" name="time" value="{{ old('time', substr((string)$r->time,0,5)) }}" required>
            </div>
            <div>
              <label>Guests</label>
              <input class="input" type="number" min="1" name="guests" value="{{ old('guests',$r->guests) }}" required>
            </div>
          </div>
          <!-- Only Step 1 fields (Date, Time, Guests) are editable from calendar -->
        </form>
      </div></div>

      <div class="card"><div class="card-body">
        <h3 style="margin:0 0 8px">Deposit</h3>
        <div class="kv" style="margin-bottom:10px">
          <div>Deposit due</div><div><b>{{ $fmt($r->deposit_due ?? 0) }}</b></div>
          <div>Deposit paid</div><div><b style="color:#16a34a">{{ $fmt($r->deposit_paid ?? 0) }}</b></div>
          @php $balance = max(0, (float)($r->total ?? 0) - (float)($r->deposit_paid ?? 0)); @endphp
          <div>Balance</div><div><b>{{ $fmt($balance) }}</b></div>
        </div>
        <div><a class="btn secondary" href="{{ route('admin.reservations.invoice',['id'=>$r->id,'back'=>request()->fullUrl()]) }}">View Invoice</a></div>
      </div></div>
    </div>

    <div class="card"><div class="card-body">
      <h3 style="margin:0 0 8px">Menu Orders</h3>
      @php $its = $r->items ?? collect(); @endphp
      @if($its && $its->count())
        <form method="post" action="{{ route('admin.reservations.items.update',['id'=>$r->id]) }}">
          @csrf
          <table style="width:100%;border-collapse:collapse">
            <thead><tr><th>Item</th><th>Description</th><th style="text-align:right">Unit</th><th style="text-align:right">Qty</th><th style="text-align:right">Total</th><th></th></tr></thead>
            <tbody>
            @foreach($its as $it)
              <tr>
                <td>{{ $it->name_snapshot }}</td>
                <td>
                  <input type="text" name="desc[{{ $it->id }}]" value="{{ old('desc.'.$it->id, $it->description) }}" class="input" placeholder="Optional description">
                </td>
                <td style="text-align:right">{{ $fmt($it->unit_price_snapshot ?? 0) }}</td>
                <td style="text-align:right" nowrap>
                  <input type="number" name="items[{{ $it->id }}]" value="{{ $it->qty }}" min="0" style="width:80px;padding:6px 8px;border:1px solid #e5e7eb;border-radius:8px;text-align:center">
                </td>
                <td style="text-align:right">{{ $fmt($it->line_total ?? 0) }}</td>
                <td style="text-align:right">
                  <form method="post" action="{{ route('admin.reservations.items.delete',['id'=>$r->id,'itemId'=>$it->id]) }}" onsubmit="return confirm('Delete this item?')">
                    @csrf
                    <button class="icon-btn danger" type="submit" title="Delete item" aria-label="Delete item">
                      <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9 3h6l1 2h5v2H3V5h5l1-2zm1 6h2v10h-2V9zm4 0h2v10h-2V9zM7 9h2v10H7V9z"/></svg>
                    </button>
                  </form>
                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
          <div style="margin-top:10px;display:flex;justify-content:flex-end"><button class="btn" type="submit">Save item changes</button></div>
        </form>
      @else
        <div class="muted">No items.</div>
      @endif

      <div style="height:1px;background:#e5e7eb;margin:12px 0"></div>
      <h4 style="margin:0 0 8px">Add item</h4>
      <form method="post" action="{{ route('admin.reservations.items.add',['id'=>$r->id]) }}" style="display:grid;grid-template-columns:1fr 1fr 120px 120px auto;gap:8px;align-items:end">
        @csrf
        <div>
          <label style="font-weight:600">From menu</label>
          <select name="menu_key" class="input" style="width:100%">
            <option value="">Select</option>
            @foreach(($menuOptions ?? []) as $key=>$opt)
              <option value="{{ $key }}">{{ $opt['cat'] }} – {{ $opt['name'] }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label style="font-weight:600">Description</label>
          <input type="text" name="description" class="input" placeholder="Optional description">
        </div>
        <div>
          <label style="font-weight:600">Qty</label>
          <input type="number" name="qty" min="1" value="1" class="input" style="width:100%">
        </div>
        <div>
          <label style="font-weight:600">Custom price</label>
          <input type="number" step="0.01" name="custom_price" placeholder="0.00" class="input" style="width:100%">
        </div>
        <div>
          <label style="font-weight:600">Custom name</label>
          <input type="text" name="custom_name" placeholder="Optional name" class="input" style="width:100%">
        </div>
        <div style="grid-column: 1 / -1; display:flex;justify-content:flex-end"><button class="btn" type="submit">Add</button></div>
      </form>
    </div></div>
  </div>
</body>
</html>
