<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Invoice {{ $invoice->invoice_number }}</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    body{background:#f3f4f6;color:#172033;padding:24px}
    .shell{max-width:980px;margin:0 auto}
    .toolbar{display:flex;justify-content:flex-end;gap:8px;margin-bottom:14px}
    .btn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none}
    .btn-download{gap:7px}
    .btn-download svg{width:16px;height:16px;stroke:currentColor;stroke-width:2.2;fill:none;stroke-linecap:round;stroke-linejoin:round}
    .sheet{background:#fff;border:1px solid #e5e7eb;border-top:4px solid #45aa82;box-shadow:0 22px 65px rgba(17,24,39,.10);padding:34px}
    .head{display:flex;justify-content:space-between;gap:24px;border-bottom:2px solid #111827;padding-bottom:20px}
    .logo{width:68px;height:68px;object-fit:contain}
    .brand{display:flex;gap:15px;align-items:flex-start}
    .brand-title{font-size:24px;font-weight:900}
    .small{font-size:13px;color:#4b5563;line-height:1.5}
    .meta{text-align:right}
    .meta-label{font-size:12px;font-weight:900;letter-spacing:.12em;color:#b8872f;text-transform:uppercase}
    .meta-number{font-size:30px;font-weight:950;margin:4px 0}
    .badge{display:inline-flex;border-radius:999px;border:1px solid #dbe2ea;background:#f8fafc;color:#475569;padding:6px 10px;font-size:12px;font-weight:900;text-transform:uppercase}
    .badge.open{background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe}
    .badge.paid{background:#ecfdf5;color:#047857;border-color:#a7f3d0}
    .badge.past_due{background:#fef2f2;color:#b91c1c;border-color:#fecaca}
    .badge.void{background:#f8fafc;color:#475569}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin:24px 0}
    .panel{border:1px solid #e5e7eb;border-radius:8px;padding:15px}
    .panel h2{font-size:13px;letter-spacing:.1em;text-transform:uppercase;margin:0 0 10px;color:#374151}
    table{width:100%;border-collapse:collapse}
    th{background:#111827;color:#fff;text-align:left;font-size:12px;text-transform:uppercase;letter-spacing:.08em;padding:10px}
    td{border-bottom:1px solid #e5e7eb;padding:10px;font-size:14px}
    th:last-child,td:last-child{text-align:right}
    .totals{max-width:360px;margin:18px 0 0 auto;border:1px solid #e5e7eb;border-radius:8px;padding:14px}
    .row{display:flex;justify-content:space-between;padding:6px 0}
    .row.total{border-top:1px solid #e5e7eb;margin-top:6px;padding-top:10px;font-size:18px;font-weight:900;color:#b21e27}
    .note{margin-top:20px;border-left:4px solid #b8872f;background:#fffaf0;border-radius:8px;padding:12px 14px;color:#b21e27;font-weight:850}
    body.pdf-mode{background:#fff;padding:0}
    body.pdf-mode .shell{max-width:none;margin:0}
    body.pdf-mode .sheet{border:0;border-top:4px solid #45aa82;box-shadow:none;padding:34px}
    body.pdf-mode .head{display:table;width:100%;table-layout:fixed;border-bottom:2px solid #111827;padding-bottom:20px;margin-bottom:0}
    body.pdf-mode .brand{display:table-cell;width:64%;vertical-align:top}
    body.pdf-mode .brand .logo{float:left;margin-right:15px}
    body.pdf-mode .meta{display:table-cell;width:36%;vertical-align:top;text-align:right}
    body.pdf-mode .grid{display:block;margin:24px 0;overflow:hidden}
    body.pdf-mode .grid:after{content:"";display:block;clear:both}
    body.pdf-mode .panel{float:left;width:47.5%;min-height:146px}
    body.pdf-mode .panel + .panel{float:right}
    body.pdf-mode .row{display:table;width:100%;table-layout:fixed}
    body.pdf-mode .row span{display:table-cell}
    body.pdf-mode .row span:last-child{text-align:right}
    @media print{body{background:#fff;padding:0}.toolbar{display:none}.sheet{border:0;box-shadow:none}}
    @media (max-width:720px){body{padding:12px}.sheet{padding:22px}.head,.grid{grid-template-columns:1fr;display:grid}.meta{text-align:left}}
  </style>
  @php
    $pdfMode = (bool) ($pdfMode ?? false);
    $logoPath = public_path('assets/brand/logo.png');
    $logoSrc = $pdfMode && is_file($logoPath)
      ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath))
      : '/assets/brand/logo.png';
    $fmt = fn($n) => '$'.number_format((float) $n, 2);
    $status = strtolower((string) $invoice->status);
    $discountEnabled = (bool) ($invoice->discount_enabled ?? false);
    $travelFee = (float) ($invoice->travel_fee ?? 0);
    $serviceEnabled = (bool) ($invoice->service_charge_enabled ?? false);
    $gratuityEnabled = (bool) ($invoice->gratuity_enabled ?? false);
    $taxEnabled = (bool) ($invoice->tax_enabled ?? ((float) $invoice->tax > 0));
    $depositEnabled = (bool) ($invoice->deposit_enabled ?? ((float) $invoice->amount_paid > 0));
    $eventTime = $invoice->event_time ? \Carbon\Carbon::parse($invoice->event_time)->format('g:i A') : null;
  @endphp
</head>
<body class="{{ $pdfMode ? 'pdf-mode' : '' }}">
  <div class="shell">
    @unless($pdfMode)
      <div class="toolbar">
        <a class="btn secondary" href="{{ route('admin.invoices') }}">Back</a>
        @if(!in_array($invoice->status, ['paid', 'void'], true) && auth()->user()?->hasPermission('reservations.manage'))
          <a class="btn secondary" href="{{ route('admin.invoices.edit', ['invoice' => $invoice]) }}">Edit</a>
        @endif
        <a class="btn secondary btn-download" href="{{ route('admin.invoices.download', ['invoice' => $invoice]) }}" aria-label="Download invoice PDF">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 3v11"></path>
            <path d="m7 10 5 5 5-5"></path>
            <path d="M5 21h14"></path>
          </svg>
          Download
        </a>
        <a class="btn" href="#" onclick="window.print();return false;">Print</a>
      </div>
    @endunless

    <main class="sheet">
      <header class="head">
        <div class="brand">
          <img class="logo" src="{{ $logoSrc }}" alt="Hibachi Catering" onerror="this.style.display='none'">
          <div>
            <div class="brand-title">Hibachi Catering</div>
            <div class="small">9022 Pulsar Ct, Corona, CA 92883<br>Phone: 951-326-9602 | Email: info@hibachicater.com<br>hibachicater.com</div>
          </div>
        </div>
        <div class="meta">
          <div class="meta-label">Invoice</div>
          <div class="meta-number">#{{ $invoice->invoice_number }}</div>
          <div class="small">Due {{ $invoice->due_date?->format('M j, Y') ?? '-' }}</div>
          <span class="badge {{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
        </div>
      </header>

      <section class="grid">
        <div class="panel">
          <h2>Bill to</h2>
          <strong>{{ $invoice->customer_name }}</strong><br>
          {{ $invoice->customer_phone }}<br>
          {{ $invoice->customer_email }}<br>
          @if($invoice->customer_address){{ $invoice->customer_address }}<br>@endif
          @if($invoice->event_date || $eventTime)
            Event: {{ $invoice->event_date?->format('M j, Y') }}{{ $eventTime ? ' at '.$eventTime : '' }}<br>
          @endif
          @if($invoice->event_guests)Guests: {{ $invoice->event_guests }}<br>@endif
          @if($invoice->event_type)Event type: {{ $invoice->event_type }}<br>@endif
          @if($invoice->setup_color)Setup color: {{ $invoice->setup_color }}@endif
        </div>
        <div class="panel">
          <h2>Invoice details</h2>
          Issue date: {{ $invoice->issue_date?->format('M j, Y') ?? '-' }}<br>
          Due date: {{ $invoice->due_date?->format('M j, Y') ?? '-' }}<br>
          Payment collection: {{ str_replace('_', ' ', $invoice->payment_collection) }}
        </div>
      </section>

      <table>
        <thead><tr><th>Description</th><th>Qty</th><th>Unit</th><th>Amount</th></tr></thead>
        <tbody>
          @foreach($invoice->items as $item)
            <tr>
              <td>{{ $item->description }}</td>
              <td>{{ (float) $item->quantity }}</td>
              <td>{{ $fmt($item->unit_price) }}</td>
              <td>{{ $fmt($item->amount) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>

      <div class="totals">
        <div class="row"><span>Subtotal</span><span>{{ $fmt($invoice->subtotal) }}</span></div>
        @if($discountEnabled)
          <div class="row"><span>Discount</span><span>- {{ $fmt($invoice->discount) }}</span></div>
        @endif
        @if($travelFee > 0.009)
          <div class="row"><span>Travel fee</span><span>{{ $fmt($travelFee) }}</span></div>
        @endif
        @if($serviceEnabled)
          <div class="row"><span>Service charge</span><span>{{ $fmt($invoice->service_charge) }}</span></div>
        @endif
        @if($gratuityEnabled)
          <div class="row"><span>Gratuity</span><span>{{ $fmt($invoice->gratuity) }}</span></div>
        @endif
        @if($taxEnabled)
          <div class="row"><span>Tax</span><span>{{ $fmt($invoice->tax) }}</span></div>
        @endif
        <div class="row"><span>Total</span><span>{{ $fmt($invoice->total) }}</span></div>
        @if($depositEnabled)
          <div class="row"><span>Deposit</span><span>- {{ $fmt($invoice->amount_paid) }}</span></div>
        @endif
        <div class="row total"><span>Amount due</span><span>{{ $fmt($invoice->balance) }}</span></div>
      </div>

      @if($invoice->memo)
        <div class="note">{{ $invoice->memo }}</div>
      @endif
      @if($invoice->footer_note)
        <p class="small" style="margin-top:18px">{{ $invoice->footer_note }}</p>
      @endif
    </main>
  </div>
</body>
</html>
