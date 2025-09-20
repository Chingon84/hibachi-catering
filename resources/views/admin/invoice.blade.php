<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Invoice #{{ $r->invoice_number ?? ($r->code ?? ('#'.$r->id)) }}</title>
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#111;margin:0;padding:24px}
    .wrap{max-width:900px;margin:0 auto}
    .brand{display:flex;align-items:center;gap:12px;margin-bottom:12px}
    .brand img{height:48px;width:auto}
    .head{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;position:relative}
    .title-center{position:absolute;left:50%;top:0;transform:translateX(-50%);text-align:center;width:auto}
    .muted{color:#6b7280}
    .box{border:1px solid #e5e7eb;border-radius:12px;padding:12px;margin-top:10px}
    .kv{font-size:13px}
    .kv-row{display:flex;align-items:baseline;justify-content:flex-start;gap:6px;padding:2px 0}
    .kv-label{color:#374151}
    .kv-label::after{content: ":"; margin: 0 6px 0 2px}
    .kv-val{text-align:left}
    table{width:100%;border-collapse:collapse}
    th,td{padding:6px 8px;border-bottom:1px solid #e5e7eb;font-size:14px}
    th{text-align:left}
    .totals{width:auto;max-width:420px;font-size:12px}
    .totals td{border:0;padding:2px 6px}
    .right{text-align:right}
    .btn{background:#b21e27;color:#fff;border:0;border-radius:8px;padding:8px 12px;cursor:pointer;text-decoration:none}
    @media print {.no-print{display:none}}
    .two-col{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    @media (max-width: 720px){.two-col{grid-template-columns:1fr}}
  </style>
  @php $fmt = fn($n)=>'$'.number_format((float)$n,2); @endphp
</head>
<body>
  <div class="wrap">
    <div class="brand">
      <img src="/assets/brand/logo.png" alt="Hibachi Catering" onerror="this.style.display='none'">
      <div>
        <div style="font-weight:700">Hibachi Catering</div>
        <div class="muted">9022 Pulsar Ct, Corona, CA 92883</div>
        <div class="muted">
          Email: <a href="mailto:info@hibachicater.com" style="color:inherit">info@hibachicater.com</a>
          &nbsp;|&nbsp; Phone: <a href="tel:+19513269602" style="color:inherit">951-326-9602</a>
          &nbsp;|&nbsp; <a href="https://hibachicater.com" target="_blank" rel="noopener" style="color:inherit;text-decoration:underline">hibachicater.com</a>
        </div>
      </div>
    </div>
    <div class="head">
      @php $dateFmt = $r->date?->format('m/d/Y'); @endphp
      <div></div>
      <div class="no-print">
        @php $backUrl = request('back') ?? url()->previous() ?? route('admin.reservations'); @endphp
        <a href="{{ $backUrl }}" class="btn" style="background:#4b5563;margin-right:8px">Back</a>
        <a href="#" class="btn" onclick="window.print(); return false;">Print</a>
      </div>
      <div class="title-center">
        <h2 style="margin:0;font-size:18px">Invoice #{{ $r->invoice_number ?? ($r->code ?? ('#'.$r->id)) }}
          @if($dateFmt)
            <span style="color:#b21e27"> · {{ $dateFmt }}</span>
          @endif
        </h2>
      </div>
    </div>

    <div class="two-col">
      <div class="box">
        <h3 style="margin:0 0 6px; font-size:14px">Customer</h3>
        <div class="kv">
          <div class="kv-row"><div class="kv-label">Customer name</div><div class="kv-val">{{ $r->customer_name ?? '—' }}</div></div>
          @if(!empty($r->company))
            <div class="kv-row"><div class="kv-label">Company</div><div class="kv-val">{{ $r->company }}</div></div>
          @endif
          <div class="kv-row"><div class="kv-label">Date</div><div class="kv-val">{{ $r->date?->format('m/d/Y') ?? '—' }}</div></div>
          <div class="kv-row"><div class="kv-label">Phone</div><div class="kv-val">{{ $r->phone ?? '—' }}</div></div>
          <div class="kv-row"><div class="kv-label">Email</div><div class="kv-val">{{ $r->email ?? '—' }}</div></div>
          <div class="kv-row"><div class="kv-label">Address</div><div class="kv-val">{{ trim(($r->address ?? '')) ?: '—' }}</div></div>
          <div class="kv-row"><div class="kv-label">City</div><div class="kv-val">{{ $r->city ?? '—' }}</div></div>
          <div class="kv-row"><div class="kv-label">ZIP</div><div class="kv-val">{{ $r->zip_code ?? '—' }}</div></div>
        </div>
      </div>

      <div class="box">
        <h3 style="margin:0 0 6px; font-size:14px">Event</h3>
        <div class="kv">
          <div class="kv-row"><div class="kv-label">Invoice #</div><div class="kv-val">{{ $r->invoice_number ?? '—' }}</div></div>
          <div class="kv-row"><div class="kv-label">Guests</div><div class="kv-val">{{ $r->guests }}</div></div>
          <div class="kv-row"><div class="kv-label">Time</div><div class="kv-val">{{ \Carbon\Carbon::parse($r->time)->format('g:i A') }}</div></div>
          <div class="kv-row"><div class="kv-label">Setup color</div><div class="kv-val">{{ $r->setup_color ?? '—' }}</div></div>
          <div class="kv-row"><div class="kv-label">Event type</div><div class="kv-val">{{ $r->event_type ?? '—' }}</div></div>
          <div class="kv-row"><div class="kv-label">Stairs</div><div class="kv-val">{{ $r->stairs ? 'Yes' : 'No' }}</div></div>
          <div class="kv-row"><div class="kv-label">How did you hear</div><div class="kv-val">{{ $r->heard_about ?? '—' }}</div></div>
          @if($r->notes)
            <div class="kv-row"><div class="kv-label">Notes</div><div class="kv-val" style="max-width:60ch;text-align:left">{{ $r->notes }}</div></div>
          @endif
        </div>
      </div>
    </div>

    <div class="box">
      <h3 style="margin:0 0 6px; font-size:14px">Menu</h3>
      @php $items = $r->items ?? collect(); @endphp
      <table>
        <thead><tr><th>Item</th><th>Description</th><th class="right">Unit</th><th class="right">Qty</th><th class="right">Total</th></tr></thead>
        <tbody>
          @forelse($items as $it)
            <tr>
              <td>
                <div>{{ $it->name_snapshot }}</div>
              </td>
              <td style="color:#6b7280;font-size:12px">{{ $it->description }}</td>
              <td class="right">{{ $fmt($it->unit_price_snapshot ?? 0) }}</td>
              <td class="right">{{ $it->qty }}</td>
              <td class="right">{{ $fmt($it->line_total ?? 0) }}</td>
            </tr>
          @empty
            <tr><td colspan="4" class="muted">No items</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @php
      $subtotal = (float)($r->subtotal ?? $items->sum('line_total'));
      $travel   = (float)($r->travel_fee ?? 0);
      $gratuity = (float)($r->gratuity ?? round($subtotal*0.18,2));
      $adj      = is_array($r->invoice_adjustments ?? null) ? $r->invoice_adjustments : [];
      $adjSum   = array_reduce($adj, fn($c,$a)=> $c + (float)($a['amount'] ?? 0), 0.0);
      $tax      = (float) round(max(0, $subtotal + $adjSum) * 0.1025, 2);
      $total    = (float) round($subtotal+$travel+$gratuity+$tax+$adjSum,2);
      $paidTotal   = (float)($r->deposit_paid ?? 0);
      $depositDue  = (float)($r->deposit_due ?? 0);
      if ($depositDue <= 0) { $depositDue = round($total * 0.20, 2); }
      $depositPaid = min($paidTotal, $depositDue);
      $otherPaid   = max(0, round($paidTotal - $depositPaid, 2));
      $balance     = max(0, round($total - $paidTotal, 2));
  @endphp

    <div class="box" style="display:flex;justify-content:space-between;gap:16px;align-items:flex-start">
      @php
        $payUrl = null;
        try { if (!empty($r->code) && $balance > 0) { $payUrl = URL::temporarySignedRoute('invoice.pay', now()->addHours(72), ['code'=>$r->code]); } } catch (\Throwable $e) {}
      @endphp
      @if($payUrl)
        <div class="no-print" style="min-width:200px;text-align:center">
          <div style="font-weight:600;margin-bottom:6px">Pay Balance</div>
          <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($payUrl) }}" alt="Pay QR" style="border:1px solid #e5e7eb;border-radius:8px">
          <div style="font-size:12px;color:#6b7280;margin-top:6px">Scan to pay (expires in 72h)</div>
          <div style="margin-top:6px;word-break:break-all">
            <a href="{{ $payUrl }}" target="_top" rel="noopener" style="font-size:12px;color:#b21e27;text-decoration:underline">Open payment link</a>
          </div>
        </div>
      @endif
      <table class="totals" style="margin-left:auto">
        <tr><td>Subtotal</td><td class="right">{{ $fmt($subtotal) }}</td></tr>
        <tr><td>Travel fee</td><td class="right">{{ $fmt($travel) }}</td></tr>
        @if(!empty($adj))
          @foreach($adj as $a)
            <tr><td>{{ $a['label'] ?? 'Adjustment' }}</td><td class="right">{{ $fmt($a['amount'] ?? 0) }}</td></tr>
          @endforeach
        @endif
        <tr><td>Gratuity</td><td class="right">{{ $fmt($gratuity) }}</td></tr>
        <tr><td>Tax</td><td class="right">{{ $fmt($tax) }}</td></tr>
        <tr><td style="border-top:1px solid #e5e7eb; padding-top:4px"><b>Total</b></td><td class="right" style="border-top:1px solid #e5e7eb; padding-top:4px"><b>{{ $fmt($total) }}</b></td></tr>
        <tr><td class="muted">Deposit paid</td><td class="right" style="color:#16a34a">- {{ $fmt($depositPaid) }}</td></tr>
        <tr><td class="muted">Paid</td><td class="right" style="color:#16a34a">- {{ $fmt($otherPaid) }}</td></tr>
        <tr><td><b>Balance</b></td><td class="right"><b>{{ $fmt($balance) }}</b></td></tr>
      </table>
    </div>
  </div>
    <div class="footer" style="text-align:center;margin-top:24px;padding-top:12px;border-top:1px solid #e5e7eb;color:#374151">
      <div>Thank you for choosing Hibachi Catering – We look forward to making your event unforgettable!</div>
    </div>
  </div>
</body>
</html>
