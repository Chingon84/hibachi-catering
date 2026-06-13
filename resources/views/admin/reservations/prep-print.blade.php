<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kitchen Prep Ticket</title>
  <style>
    *{box-sizing:border-box}
    :root{--ink:#000;--muted:#333;--line:#000}
    html,body{margin:0;padding:0;background:#fff;color:var(--ink)}
    body{font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:1.28;padding:14px}
    .ticket-wrap{width:80mm;max-width:380px;margin:0 auto}
    .toolbar{display:flex;justify-content:center;gap:8px;margin:0 auto 12px;max-width:380px}
    .btn{display:inline-flex;align-items:center;justify-content:center;border:1px solid #111;border-radius:6px;background:#111;color:#fff;text-decoration:none;font-weight:700;font-size:13px;padding:8px 12px;cursor:pointer}
    .btn.secondary{background:#fff;color:#111}
    .ticket{background:#fff}
    .brand{text-align:center;margin-bottom:8px}
    .brand img{display:block;width:auto;height:54px;max-width:150px;object-fit:contain;margin:0 auto 6px}
    .brand-name{font-size:16px;font-weight:800;text-transform:uppercase;letter-spacing:.04em}
    .rule{border:0;border-top:1px dashed var(--line);margin:9px 0}
    .title{text-align:center;font-size:14px;font-weight:900;text-transform:uppercase;letter-spacing:.06em;margin:0}
    .details{display:grid;grid-template-columns:74px minmax(0,1fr);gap:4px 6px;margin:10px 0}
    .label{font-weight:800}
    .value{font-weight:600;overflow-wrap:anywhere}
    .menu-head,.menu-row{display:grid;grid-template-columns:minmax(0,1fr) 46px;gap:8px;align-items:start}
    .menu-head{font-weight:900;text-transform:uppercase;border-bottom:1px solid var(--line);padding-bottom:4px;margin-bottom:5px}
    .menu-row{padding:5px 0;border-bottom:1px dotted #999;break-inside:avoid;page-break-inside:avoid}
    .item-name{font-weight:800;overflow-wrap:anywhere}
    .item-desc{font-size:12px;color:var(--muted);margin-top:2px;overflow-wrap:anywhere}
    .qty{text-align:right;font-weight:900}
    .empty{text-align:center;font-weight:700;padding:10px 0}
    @page{size:80mm auto;margin:4mm}
    @media print{
      html,body{width:80mm;background:#fff}
      body{padding:0;font-size:12px}
      .no-print{display:none!important}
      .ticket-wrap{width:72mm;max-width:none;margin:0}
      .brand img{height:46px;max-width:130px}
      .rule{margin:7px 0}
      .menu-row{padding:4px 0}
      a{color:inherit;text-decoration:none}
    }
  </style>
  @php
    $dateFmt = $r->date?->format('m/d/Y');
    $timeFmt = $r->time ? \Carbon\Carbon::parse($r->time)->format('g:i A') : null;
    $addressParts = collect([
      $r->address ?? null,
      $r->city ?? null,
      $r->state ?? null,
      $r->zip_code ?? null,
    ])->map(fn($part) => trim((string) $part))->filter()->values();
    $eventAddress = $addressParts->isNotEmpty() ? $addressParts->implode(', ') : 'N/A';
    $items = $r->items ?? collect();
    $backUrl = request('back') ?: route('admin.reservations');
  @endphp
</head>
<body>
  <div class="toolbar no-print">
    <a class="btn secondary" href="{{ $backUrl }}">Back</a>
    <button class="btn" type="button" onclick="window.print()">Print</button>
  </div>

  <main class="ticket-wrap ticket" aria-label="Kitchen prep ticket">
    <header class="brand">
      <img src="/assets/brand/logo.png" alt="Hibachi Catering" onerror="this.style.display='none'">
      <div class="brand-name">Hibachi Catering</div>
    </header>

    <hr class="rule">
    <h1 class="title">Kitchen Prep Ticket</h1>
    <hr class="rule">

    <section class="details" aria-label="Customer and event">
      <div class="label">Customer:</div><div class="value">{{ $r->customer_name ?: 'N/A' }}</div>
      <div class="label">Date:</div><div class="value">{{ $dateFmt ?: 'N/A' }}</div>
      <div class="label">Address:</div><div class="value">{{ $eventAddress }}</div>
      <div class="label">Guests:</div><div class="value">{{ $r->guests ?? 'N/A' }}</div>
      <div class="label">Time:</div><div class="value">{{ $timeFmt ?: 'N/A' }}</div>
      <div class="label">Setup:</div><div class="value">{{ $r->setup_color ?: 'N/A' }}</div>
      <div class="label">Type:</div><div class="value">{{ $r->event_type ?: 'N/A' }}</div>
    </section>

    <hr class="rule">
    <section aria-label="Menu">
      <div class="menu-head">
        <div>Menu</div>
        <div class="qty">Qty</div>
      </div>
      @forelse($items as $it)
        <div class="menu-row">
          <div>
            <div class="item-name">{{ $it->name_snapshot ?: 'Item' }}</div>
            @if(trim((string) ($it->description ?? '')) !== '')
              <div class="item-desc">{{ $it->description }}</div>
            @endif
          </div>
          <div class="qty">{{ $it->qty }}</div>
        </div>
      @empty
        <div class="empty">No menu items</div>
      @endforelse
    </section>
    <hr class="rule">
  </main>
</body>
</html>
