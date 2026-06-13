<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Review invoice {{ $invoice->invoice_number }}</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    body{background:#f7f7fb;color:#172033}
    .wrap{max-width:1120px;margin:22px auto;padding:0 18px}
    .header{display:flex;justify-content:space-between;gap:14px;align-items:center;margin-bottom:14px}
    .title{margin:0;font-size:24px}
    .actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
    .btn-purple{border:0;border-radius:9px;background:#5438ff;color:#fff;font-weight:850;padding:10px 14px;text-decoration:none;cursor:pointer}
    .btn-light{border:1px solid #cbd5e1;border-radius:9px;background:#fff;color:#334155;font-weight:750;padding:9px 12px;text-decoration:none;cursor:pointer}
    .grid{display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:18px;align-items:start}
    .paper{background:#fff;border:1px solid #e5e7eb;border-top:3px solid #45aa82;box-shadow:0 18px 45px rgba(15,23,42,.08);padding:32px}
    .paper-head{display:flex;justify-content:space-between;gap:22px}
    .logo{width:62px;height:62px;object-fit:contain}
    .meta{font-size:12px;line-height:1.5}
    h1{margin:0 0 16px;font-size:26px}
    .cols{display:grid;grid-template-columns:1fr 1fr;gap:28px;margin:26px 0;font-size:13px}
    table{width:100%;border-collapse:collapse}
    th,td{border-bottom:1px solid #e5e7eb;padding:9px 6px;text-align:left;font-size:13px}
    th:last-child,td:last-child{text-align:right}
    .totals{margin-left:auto;max-width:320px;margin-top:12px;font-size:14px}
    .row{display:flex;justify-content:space-between;padding:5px 0}
    .row.total{border-top:1px solid #e5e7eb;margin-top:6px;padding-top:10px;font-size:18px;font-weight:900}
    .side{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px}
    .side h2{font-size:16px;margin:0 0 12px}
    .kv{display:flex;justify-content:space-between;gap:12px;padding:7px 0;border-bottom:1px solid #f1f5f9;font-size:13px}
    .muted{color:#64748b}
    @media (max-width:900px){.grid,.cols{grid-template-columns:1fr}.header{align-items:flex-start;flex-direction:column}}
  </style>
  @php
    $fmt = fn($n) => '$'.number_format((float) $n, 2);
    $discountEnabled = (bool) ($invoice->discount_enabled ?? false);
    $travelFee = (float) ($invoice->travel_fee ?? 0);
    $serviceEnabled = (bool) ($invoice->service_charge_enabled ?? false);
    $gratuityEnabled = (bool) ($invoice->gratuity_enabled ?? false);
    $taxEnabled = (bool) ($invoice->tax_enabled ?? ((float) $invoice->tax > 0));
    $depositEnabled = (bool) ($invoice->deposit_enabled ?? ((float) $invoice->amount_paid > 0));
    $eventTime = $invoice->event_time ? \Carbon\Carbon::parse($invoice->event_time)->format('g:i A') : null;
  @endphp
</head>
<body>
  <div class="wrap">
    <div class="header">
      <h1 class="title">Review invoice</h1>
      <div class="actions">
        <a class="btn-light" href="{{ route('admin.invoices.edit', ['invoice' => $invoice]) }}">Back to edit</a>
        <a class="btn-light" href="{{ route('admin.invoices') }}">Save draft</a>
        @if($invoice->status === 'draft')
          <form method="post" action="{{ route('admin.invoices.finalize', ['invoice' => $invoice]) }}" style="display:inline">
            @csrf
            <button class="btn-purple" type="submit">Finalize invoice</button>
          </form>
        @endif
      </div>
    </div>

    <div class="grid">
      <main class="paper">
        <div class="paper-head">
          <div>
            <h1>Invoice</h1>
            <div class="meta">
              <strong>Invoice number</strong> {{ $invoice->invoice_number }}<br>
              <strong>Date of issue</strong> {{ $invoice->issue_date?->format('M j, Y') ?? '-' }}<br>
              <strong>Date due</strong> {{ $invoice->due_date?->format('M j, Y') ?? '-' }}
            </div>
          </div>
          <img class="logo" src="/assets/brand/logo.png" alt="Hibachi Catering" onerror="this.style.display='none'">
        </div>

        <div class="cols">
          <div><strong>Hibachi Catering</strong><br>9022 Pulsar Ct<br>Corona, California 92883<br>United States<br>+1 951-326-9602</div>
          <div>
            <strong>Bill to</strong><br>
            {{ $invoice->customer_name }}<br>
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
        </div>

        <table>
          <thead><tr><th>Description</th><th>Qty</th><th>Unit price</th><th>Amount</th></tr></thead>
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
          <p style="margin-top:30px;color:#b21e27;font-weight:850">{{ $invoice->memo }}</p>
        @endif
        @if($invoice->footer_note)
          <p class="muted" style="margin-top:30px;border-top:1px solid #e5e7eb;padding-top:14px">{{ $invoice->footer_note }}</p>
        @endif
      </main>

      <aside class="side">
        <h2>Summary</h2>
        <div class="kv"><span>Status</span><strong>{{ ucfirst(str_replace('_', ' ', $invoice->status)) }}</strong></div>
        <div class="kv"><span>Total</span><strong>{{ $fmt($invoice->total) }}</strong></div>
        <div class="kv"><span>Paid</span><strong>{{ $fmt($invoice->amount_paid) }}</strong></div>
        <div class="kv"><span>Balance</span><strong>{{ $fmt($invoice->balance) }}</strong></div>
        <div class="kv"><span>Collection</span><strong>{{ str_replace('_', ' ', $invoice->payment_collection) }}</strong></div>
      </aside>
    </div>
  </div>
</body>
</html>
