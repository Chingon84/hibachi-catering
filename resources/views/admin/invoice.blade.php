<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Invoice #{{ $r->invoice_number ?? ($r->code ?? ('#'.$r->id)) }}</title>
  <style>
    :root{--ink:#111827;--muted:#6b7280;--soft:#f7f7f8;--line:#e5e7eb;--red:#b21e27;--gold:#b8872f;--green:#047857;--amber:#b45309}
    *{box-sizing:border-box}
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:var(--ink);margin:0;background:#f3f4f6;padding:24px}
    .invoice-shell{max-width:980px;margin:0 auto}
    .toolbar{display:flex;justify-content:flex-end;gap:10px;margin-bottom:14px}
    .btn{display:inline-flex;align-items:center;justify-content:center;background:var(--red);color:#fff;border:0;border-radius:8px;padding:10px 14px;cursor:pointer;text-decoration:none;font-weight:750;font-size:14px}
    .btn.secondary{background:#4b5563}
    .invoice-sheet{background:#fff;border:1px solid var(--line);box-shadow:0 22px 65px rgba(17,24,39,.10);padding:34px}
    .topbar{display:flex;align-items:flex-start;justify-content:space-between;gap:28px;border-bottom:3px solid var(--ink);padding-bottom:22px}
    .brand{display:flex;align-items:center;gap:16px;min-width:0}
    .brand img{height:66px;width:auto;object-fit:contain}
    .brand-title{font-size:24px;font-weight:850;letter-spacing:.02em}
    .company-lines{color:var(--muted);font-size:13px;line-height:1.55;margin-top:4px}
    .invoice-meta{text-align:right;min-width:220px}
    .invoice-label{font-size:12px;text-transform:uppercase;letter-spacing:.12em;color:var(--gold);font-weight:850}
    .invoice-number{font-size:30px;font-weight:900;margin:4px 0}
    .invoice-date{color:var(--muted);font-weight:650}
    .badge{display:inline-flex;align-items:center;border-radius:999px;padding:7px 11px;font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.06em;border:1px solid;margin-top:10px}
    .badge.paid{background:#ecfdf5;color:var(--green);border-color:#a7f3d0}
    .badge.due{background:#fffbeb;color:var(--amber);border-color:#fcd34d}
    .section-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:22px}
    .panel{border:1px solid var(--line);border-radius:8px;padding:16px;background:#fff}
    .panel h3,.menu-panel h3,.qr-panel h3{font-size:13px;text-transform:uppercase;letter-spacing:.10em;color:#374151;margin:0 0 12px}
    .kv{display:grid;grid-template-columns:minmax(112px,35%) 1fr;gap:7px 12px;font-size:14px}
    .kv-label{color:var(--muted);font-weight:650}
    .kv-value{font-weight:650;min-width:0;overflow-wrap:anywhere}
    .menu-panel{margin-top:18px;border:1px solid var(--line);border-radius:8px;overflow:hidden;background:#fff}
    .menu-panel h3{padding:16px 16px 0}
    table{width:100%;border-collapse:collapse}
    th{background:#111827;color:#fff;text-align:left;font-size:12px;text-transform:uppercase;letter-spacing:.08em;padding:11px 12px}
    td{padding:11px 12px;border-bottom:1px solid var(--line);font-size:14px;vertical-align:top}
    tbody tr:last-child td{border-bottom:0}
    .muted{color:var(--muted)}
    .right{text-align:right}
    .desc{font-size:12px;color:var(--muted);margin-top:3px;line-height:1.35}
    .summary-wrap{display:grid;grid-template-columns:minmax(0,1fr) minmax(300px,380px);gap:18px;align-items:start;margin-top:18px}
    .payment-note{border-left:4px solid var(--gold);background:#fffaf0;border-radius:8px;padding:14px 16px;font-size:14px;line-height:1.5;color:#4a3512}
    .totals-card{border:1px solid var(--line);border-radius:8px;background:#fff;padding:16px}
    .totals-row{display:flex;justify-content:space-between;gap:16px;padding:6px 0;font-size:14px}
    .totals-row strong{font-weight:850}
    .totals-row.divider{border-top:1px solid var(--line);margin-top:6px;padding-top:10px}
    .totals-row.credit span:last-child{color:var(--green);font-weight:800}
    .totals-row.balance{margin-top:8px;padding:12px;border-radius:8px;background:#fff7f7;color:var(--red);font-size:18px;font-weight:900}
    .totals-row.balance.paid{background:#ecfdf5;color:var(--green)}
    .qr-panel{width:220px;margin:20px 0 0 0;border:1px solid var(--line);border-radius:8px;padding:10px 12px;text-align:left;background:#fff;break-inside:avoid;page-break-inside:avoid}
    .qr-panel h3{font-size:10px;letter-spacing:.08em;margin-bottom:7px}
    .qr-box{display:inline-flex;align-items:center;justify-content:center;width:96px;height:96px;border:1px solid var(--line);border-radius:7px;background:#fff;margin:0 0 7px}
    .qr-box svg{width:82px;height:82px;display:block}
    .qr-text{margin:0;color:var(--muted);font-size:10px;max-width:190px;line-height:1.35}
    .qr-url{margin-top:5px;font-size:8px;color:var(--muted);overflow-wrap:anywhere;line-height:1.25}
    .footer{margin-top:22px;padding-top:16px;border-top:1px solid var(--line);text-align:center;color:#374151;font-weight:750}
    @media (max-width:760px){
      body{padding:12px}
      .invoice-sheet{padding:20px}
      .topbar,.section-grid,.summary-wrap{grid-template-columns:1fr;display:grid}
      .invoice-meta{text-align:left;min-width:0}
      .brand{align-items:flex-start}
      .brand img{height:54px}
      .invoice-number{font-size:25px}
      .kv{grid-template-columns:1fr}
      .kv-label{margin-top:5px}
      th,td{padding:9px 8px}
    }
    @page{size:letter;margin:.45in}
    @media print{
      body{background:#fff;padding:0;-webkit-print-color-adjust:exact;print-color-adjust:exact}
      .no-print{display:none!important}
      .invoice-shell{max-width:none;margin:0}
      .invoice-sheet{border:0;box-shadow:none;padding:0;font-size:10px}
      .topbar{display:flex!important;align-items:flex-start;justify-content:space-between;gap:14px;padding-bottom:10px;border-bottom-width:2px}
      .brand{align-items:flex-start;gap:10px}
      .brand img{height:42px}
      .brand-title{font-size:18px}
      .company-lines{font-size:10px;line-height:1.35;margin-top:2px}
      .invoice-meta{text-align:right;min-width:160px}
      .invoice-label{font-size:9px}
      .invoice-number{font-size:22px;margin:2px 0}
      .invoice-date{font-size:11px}
      .badge{padding:4px 8px;font-size:9px;margin-top:5px}
      .section-grid{display:grid!important;grid-template-columns:1fr 1fr!important;gap:8px;margin-top:10px}
      .panel{padding:9px;border-radius:6px}
      .panel h3,.menu-panel h3,.qr-panel h3{font-size:9px;letter-spacing:.09em;margin:0 0 6px}
      .kv{grid-template-columns:78px 1fr!important;gap:3px 7px;font-size:10px}
      .kv-label{margin-top:0}
      .menu-panel{margin-top:8px;border-radius:6px}
      .menu-panel h3{padding:9px 9px 0}
      th{font-size:9px;padding:6px 7px}
      td{font-size:10px;padding:6px 7px}
      .desc{font-size:9px;margin-top:1px}
      .summary-wrap{display:grid!important;grid-template-columns:minmax(0,1fr) 265px!important;gap:8px;margin-top:8px}
      .payment-note{padding:8px 10px;font-size:10px;line-height:1.35;border-left-width:3px}
      .totals-card{padding:9px;border-radius:6px}
      .totals-row{font-size:10px;padding:3px 0}
      .totals-row.divider{margin-top:3px;padding-top:6px}
      .totals-row.balance{margin-top:5px;padding:7px;font-size:13px}
      .qr-panel{width:150px;margin:10px 0 0 0;padding:7px 8px;border-radius:6px}
      .qr-panel h3{font-size:8px;margin-bottom:5px}
      .qr-box{width:72px;height:72px;border-radius:5px;margin:0 0 4px}
      .qr-box svg{width:60px;height:60px}
      .qr-text{display:none}
      .qr-url{font-size:6px;line-height:1.15;margin-top:3px}
      .footer{margin-top:8px;padding-top:8px;font-size:11px}
      .panel,.menu-panel,.totals-card,.qr-panel{break-inside:avoid;page-break-inside:avoid}
      .summary-wrap{break-inside:avoid;page-break-inside:avoid}
      .footer{break-inside:avoid;page-break-inside:avoid}
      a{color:inherit;text-decoration:none}
    }
  </style>
  @php
    $fmt = fn($n) => '$'.number_format((float) $n, 2);
    $dateFmt = $r->date?->format('m/d/Y');
    $timeFmt = $r->time ? \Carbon\Carbon::parse($r->time)->format('g:i A') : null;
    $items = $r->items ?? collect();
    $totals = \App\Support\ReservationTotals::compute($r);
    $subtotal = $totals['subtotal'];
    $travel = $totals['travel'];
    $gratuity = $totals['gratuity'];
    $adj = $totals['adjustments'];
    $tax = $totals['tax'];
    $total = $totals['total'];
    $depositPaid = $totals['deposit_display'];
    $paidTotal = $totals['paid_total'];
    $balance = $totals['balance'];
    $isPaid = (float) $balance <= 0.009;
    $statusLabel = $isPaid ? 'Paid' : ((float) $paidTotal > 0.009 ? 'Partially Paid' : 'Pending');
  @endphp
</head>
<body>
  <div class="invoice-shell">
    <div class="toolbar no-print">
      @php $backUrl = request('back') ?? url()->previous() ?? route('admin.reservations'); @endphp
      <a href="{{ $backUrl }}" class="btn secondary">Back</a>
      <a href="#" class="btn" onclick="window.print(); return false;">Print</a>
    </div>

    <main class="invoice-sheet">
      <header class="topbar">
        <div class="brand">
          <img src="/assets/brand/logo.png" alt="Hibachi Catering" onerror="this.style.display='none'">
          <div>
            <div class="brand-title">Hibachi Catering</div>
            <div class="company-lines">
              9022 Pulsar Ct, Corona, CA 92883<br>
              Phone: <a href="tel:+19513269602">951-326-9602</a> |
              Email: <a href="mailto:info@hibachicater.com">info@hibachicater.com</a><br>
              <a href="https://hibachicater.com" target="_blank" rel="noopener">hibachicater.com</a>
            </div>
          </div>
        </div>
        <div class="invoice-meta">
          <div class="invoice-label">Invoice</div>
          <div class="invoice-number">#{{ $r->invoice_number ?? ($r->code ?? $r->id) }}</div>
          <div class="invoice-date">{{ $dateFmt ? 'Event Date: '.$dateFmt : 'Event Date: Pending' }}</div>
          <span class="badge {{ $isPaid ? 'paid' : 'due' }}">{{ $isPaid ? 'Paid in Full' : 'Balance Due' }}</span>
        </div>
      </header>

      <section class="section-grid">
        <div class="panel">
          <h3>Customer</h3>
          <div class="kv">
            <div class="kv-label">Customer name</div><div class="kv-value">{{ $r->customer_name ?? '-' }}</div>
            @if(!empty($r->company))
              <div class="kv-label">Company</div><div class="kv-value">{{ $r->company }}</div>
            @endif
            <div class="kv-label">Phone</div><div class="kv-value">{{ $r->phone ?? '-' }}</div>
            <div class="kv-label">Email</div><div class="kv-value">{{ $r->email ?? '-' }}</div>
            <div class="kv-label">Address</div><div class="kv-value">{{ trim((string) ($r->address ?? '')) ?: '-' }}</div>
            <div class="kv-label">City / ZIP</div><div class="kv-value">{{ trim(($r->city ?? '').' '.($r->zip_code ?? '')) ?: '-' }}</div>
          </div>
        </div>

        <div class="panel">
          <h3>Event</h3>
          <div class="kv">
            <div class="kv-label">Invoice #</div><div class="kv-value">{{ $r->invoice_number ?? '-' }}</div>
            <div class="kv-label">Date</div><div class="kv-value">{{ $dateFmt ?? '-' }}</div>
            <div class="kv-label">Time</div><div class="kv-value">{{ $timeFmt ?? '-' }}</div>
            <div class="kv-label">Guests</div><div class="kv-value">{{ $r->guests ?? '-' }}</div>
            <div class="kv-label">Event type</div><div class="kv-value">{{ $r->event_type ?? '-' }}</div>
            <div class="kv-label">Setup color</div><div class="kv-value">{{ $r->setup_color ?? '-' }}</div>
            <div class="kv-label">Stairs</div><div class="kv-value">{{ $r->stairs ? 'Yes' : 'No' }}</div>
            <div class="kv-label">Invoice status</div><div class="kv-value">{{ $statusLabel }}</div>
          </div>
        </div>
      </section>

      <section class="menu-panel">
        <h3>Menu</h3>
        <table>
          <thead>
            <tr>
              <th>Item</th>
              <th>Description</th>
              <th class="right">Unit</th>
              <th class="right">Qty</th>
              <th class="right">Total</th>
            </tr>
          </thead>
          <tbody>
            @forelse($items as $it)
              <tr>
                <td><strong>{{ $it->name_snapshot }}</strong></td>
                <td><div class="desc">{{ $it->description ?: '-' }}</div></td>
                <td class="right">{{ $fmt($it->unit_price_snapshot ?? 0) }}</td>
                <td class="right">{{ $it->qty }}</td>
                <td class="right"><strong>{{ $fmt($it->line_total ?? 0) }}</strong></td>
              </tr>
            @empty
              <tr><td colspan="5" class="muted">No items</td></tr>
            @endforelse
          </tbody>
        </table>
      </section>

      <section class="summary-wrap">
        <div class="payment-note">
          <strong>Payment status:</strong> {{ $statusLabel }}.
          @if($isPaid)
            This invoice is completely paid.
          @else
            Current balance due is {{ $fmt($balance) }}.
          @endif
        </div>

        <div class="totals-card">
          <div class="totals-row"><span>Subtotal</span><span>{{ $fmt($subtotal) }}</span></div>
          <div class="totals-row"><span>Travel fee</span><span>{{ $fmt($travel) }}</span></div>
          @foreach($adj as $a)
            <div class="totals-row"><span>{{ $a['label'] ?? 'Adjustment' }}</span><span>{{ $fmt($a['amount'] ?? 0) }}</span></div>
          @endforeach
          <div class="totals-row"><span>Gratuity</span><span>{{ $fmt($gratuity) }}</span></div>
          <div class="totals-row"><span>Tax</span><span>{{ $fmt($tax) }}</span></div>
          <div class="totals-row divider"><strong>Total</strong><strong>{{ $fmt($total) }}</strong></div>
          <div class="totals-row credit"><span>Deposit paid</span><span>- {{ $fmt($depositPaid) }}</span></div>
          <div class="totals-row credit"><span>Total paid</span><span>- {{ $fmt($paidTotal) }}</span></div>
          <div class="totals-row balance {{ $isPaid ? 'paid' : '' }}"><strong>Balance</strong><strong>{{ $fmt($balance) }}</strong></div>
        </div>
      </section>

      <section class="qr-panel">
        <h3>Scan to view invoice status</h3>
        <div class="qr-box">
          @if(!empty($qrCodeSvg))
            {!! $qrCodeSvg !!}
          @else
            <span class="muted">QR unavailable</span>
          @endif
        </div>
        @if(!empty($statusUrl))
          <div class="qr-url">{{ $statusUrl }}</div>
        @endif
      </section>

      <footer class="footer">
        Thank you for choosing Hibachi Catering &mdash; We look forward to making your event unforgettable!
      </footer>
    </main>
  </div>
</body>
</html>
