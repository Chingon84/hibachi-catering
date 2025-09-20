<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin – Reservations</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    .title{font-size:22px;margin:0}
    .inv{display:inline-block;padding:2px 8px;border-radius:999px;font-size:12px;font-weight:700;border:1px solid #e5e7eb;white-space:nowrap}
    .table td select{max-width:160px}
    .inv.paid{background:#ecfdf5;color:#065f46;border-color:#a7f3d0}
    .inv.pending{background:#fef9c3;color:#854d0e;border-color:#fde68a}
    .inv.overdue{background:#ede9fe;color:#6d28d9;border-color:#ddd6fe}
    .inv.cancelled{background:#fef2f2;color:#991b1b;border-color:#fecaca}
    .inv.refunded{background:#f3f4f6;color:#6b7280;border-color:#e5e7eb}
  </style>
  @php $fmt = fn($n)=>'$'.number_format((float)$n,2); @endphp
</head>
<body>
  <div class="container">
    <div class="header">
      <form method="get" class="toolbar" action="{{ route('admin.reservations') }}">
        <input class="input" type="date" name="d" value="{{ $d }}">
        <select name="status" class="select">
          <option value="">All statuses</option>
          @foreach(['draft','pending_payment','confirmed','canceled'] as $st)
            <option value="{{ $st }}" {{ $status===$st ? 'selected':'' }}>{{ ucfirst(str_replace('_',' ',$st)) }}</option>
          @endforeach
        </select>
        <select name="sort" class="select">
          @php $sortVal = $sort ?? request('sort','newest'); @endphp
          <option value="newest" {{ $sortVal==='newest' ? 'selected':'' }}>Newest first</option>
          <option value="oldest" {{ $sortVal==='oldest' ? 'selected':'' }}>Oldest first</option>
          <option value="event_desc" {{ $sortVal==='event_desc' ? 'selected':'' }}>Event time (latest→earliest)</option>
          <option value="event_asc" {{ $sortVal==='event_asc' ? 'selected':'' }}>Event time (earliest→latest)</option>
          <option value="invoice_desc" {{ $sortVal==='invoice_desc' ? 'selected':'' }}>Invoice # (high→low)</option>
          <option value="invoice_asc" {{ $sortVal==='invoice_asc' ? 'selected':'' }}>Invoice # (low→high)</option>
        </select>
        <input class="input" type="text" name="q" placeholder="Search name, code, email…" value="{{ $q }}" style="min-width:260px">
        <button class="btn secondary" type="submit">Filter</button>
      </form>
      <a href="http://127.0.0.1:8000/reservations/1" target="_blank" rel="noopener" class="btn" style="margin-left:auto">Resv Flow</a>
    </div>

    <div class="card">
      <div class="card-body">
        <table class="table" aria-label="Reservations list">
          <thead>
            <tr>
              <th>Invoice #</th>
              <th>Date</th>
              <th>Time</th>
              <th>Guests</th>
              <th>Customer</th>
              <th>Total</th>
              <th>Paid</th>
              <th>Balance</th>
              <th>Status</th>
              <th>Booked by</th>
              <th>Invoice</th>
              <th>Inv Status</th>
              <th style="width:60px">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($rows as $r)
              <tr>
                <td>{{ $r->invoice_number ?? "—" }}</td>
                <td>{{ $r->date?->format('m/d/Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($r->time)->format('g:i A') }}</td>
                <td>{{ $r->guests }}</td>
                <td>
                  <div><a href="{{ route('admin.reservations.show',['id'=>$r->id]) }}" style="color:inherit;text-decoration:underline">{{ $r->customer_name ?? '—' }}</a></div>
                  <div class="muted">{{ $r->email ?? '' }}</div>
                </td>
                @php $bal = max(0, (float)($r->total ?? 0) - (float)($r->deposit_paid ?? 0)); @endphp
                <td>{{ $fmt($r->total ?? 0) }}</td>
                <td>{{ $fmt($r->deposit_paid ?? 0) }}</td>
                <td>
                  @if($bal <= 0)
                    <span style="color:#16a34a;font-weight:700">{{ $fmt(0) }}</span>
                  @else
                    {{ $fmt($bal) }}
                  @endif
                </td>
                <td><span class="status {{ $r->status }}">{{ ucfirst(str_replace('_',' ',$r->status)) }}</span></td>
                <td>{{ $r->booked_by ?? '—' }}</td>
                <td>
                  <a href="{{ route('admin.reservations.invoice',['id'=>$r->id, 'back'=>request()->fullUrl()]) }}" title="View invoice" style="text-decoration:none;color:#b21e27">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM14 3.5L18.5 8H14V3.5zM8 12h8v2H8v-2zm0 4h8v2H8v-2zm0-8h4v2H8V8z"/>
                    </svg>
                  </a>
                  <a href="{{ route('admin.reservations.show',['id'=>$r->id, 'print'=>'menu', 'back'=>request()->fullUrl()]) }}" title="Print menu" aria-label="Print menu" style="text-decoration:none;color:#2563eb;margin-left:8px">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
                      <circle cx="5" cy="7" r="1.5" fill="currentColor"/>
                      <circle cx="5" cy="12" r="1.5" fill="currentColor"/>
                      <circle cx="5" cy="17" r="1.5" fill="currentColor"/>
                      <path d="M9 7h10M9 12h10M9 17h10" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/>
                    </svg>
                  </a>
                </td>
                <td>
                  <form method="post" action="{{ route('admin.reservations.invoice_status',['id'=>$r->id]) }}" style="display:flex;align-items:center;gap:8px">
                    @csrf
                    <input type="hidden" name="back" value="{{ request()->fullUrl() }}">
                    @php $ist = strtolower($r->invoice_status ?? 'pending'); @endphp
                    <select name="invoice_status" class="select inv {{ $ist }}" onchange="this.form.submit()">
                      @foreach(['paid'=>'Paid','pending'=>'Pending','overdue'=>'Overdue','cancelled'=>'Cancelled','refunded'=>'Refunded'] as $k=>$label)
                        <option value="{{ $k }}" {{ $ist===$k ? 'selected':'' }}>{{ $label }}</option>
                      @endforeach
                    </select>
                    
                  </form>
                </td>
                <td style="position:relative">
                  <div class="actions-menu" style="position:relative;display:inline-block">
                    <button type="button" class="icon-btn" aria-haspopup="true" aria-expanded="false" onclick="toggleMenu(this)" title="Actions" style="border:1px solid #e5e7eb;background:#fff;color:#374151;width:34px;height:34px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer">
                      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 7a2 2 0 110-4 2 2 0 010 4zm0 7a2 2 0 110-4 2 2 0 010 4zm0 7a2 2 0 110-4 2 2 0 010 4z"/></svg>
                    </button>
                    <div class="menu" style="display:none;position:absolute;right:0;z-index:10;background:#fff;border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.08);min-width:160px;overflow:hidden">
                      <a href="{{ route('admin.reservations.show',['id'=>$r->id]) }}" class="menu-item" style="display:block;padding:8px 12px;color:#111;text-decoration:none">Edit</a>
                      <a href="{{ route('admin.reservations.invoice',['id'=>$r->id, 'back'=>request()->fullUrl()]) }}" class="menu-item" style="display:block;padding:8px 12px;color:#111;text-decoration:none">View invoice</a>
                      <a href="{{ route('admin.reservations.invoice',['id'=>$r->id, 'back'=>request()->fullUrl()]) }}" class="menu-item" style="display:block;padding:8px 12px;color:#111;text-decoration:none" onclick="setTimeout(()=>window.print(),400)">Print</a>
                      @php
                        try { $payUrl = $r->code ? URL::signedRoute('invoice.pay', ['code'=>$r->code]) : ''; } catch (\Throwable $e) { $payUrl = ''; }
                      @endphp
                      @if(!empty($payUrl))
                        <button type="button" class="menu-item" style="display:block;width:100%;text-align:left;padding:8px 12px;color:#111;background:#fff;border:0;cursor:pointer" onclick="copyPayLink('{{ $payUrl }}', this)">Copy pay link</button>
                      @endif
                      <form method="post" action="{{ route('admin.reservations.delete',['id'=>$r->id]) }}" onsubmit="return confirm('Are you sure you want to delete this reservation?');">
                        @csrf
                        <input type="hidden" name="back" value="{{ request()->fullUrl() }}">
                        <button type="submit" class="menu-item" style="display:block;width:100%;text-align:left;padding:8px 12px;color:#b91c1c;background:#fff;border:0;cursor:pointer">Delete</button>
                      </form>
                    </div>
                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="11" class="muted">No reservations found.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <script>
    function copyPayLink(url, btn){
      if (!navigator.clipboard){
        const ta = document.createElement('textarea');
        ta.value = url; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta);
      } else {
        navigator.clipboard.writeText(url).catch(()=>{});
      }
      btn.textContent = 'Copied!'; setTimeout(()=>{ btn.textContent='Copy pay link'; }, 1200);
    }
    function closeAllMenus(){
      document.querySelectorAll('.actions-menu .menu').forEach(m => m.style.display = 'none');
      document.querySelectorAll('.actions-menu button[aria-expanded]')
        .forEach(b => b.setAttribute('aria-expanded','false'));
    }
    function toggleMenu(btn){
      const menu = btn.parentElement.querySelector('.menu');
      const shown = menu.style.display === 'block';
      closeAllMenus();
      menu.style.display = shown ? 'none' : 'block';
      btn.setAttribute('aria-expanded', shown ? 'false' : 'true');
    }
    document.addEventListener('click', (e)=>{
      const wrap = e.target.closest('.actions-menu');
      if (!wrap) closeAllMenus();
    });
  </script>
</body>
</html>
