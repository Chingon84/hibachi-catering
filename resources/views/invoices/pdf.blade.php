<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body{ font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; color:#111; font-size:12px }
    h1,h2,h3{ margin:0 0 6px }
    h3{ font-size:13px }
    .row{ display:flex; justify-content:space-between; gap:12px }
    .box{ border:1px solid #e5e7eb; border-radius:8px; padding:8px; margin-top:10px }
    table{ width:100%; border-collapse:collapse; }
    th,td{ padding:4px 4px; border-bottom:1px solid #eee; }
    th{ text-align:left; background:#f3f4f6 }
    .right{ text-align:right }
  </style>
  <title>Invoice</title>
</head>
<body>
@php
  $r = $reservation;
  $items = $r?->items ?? collect();
  $date = $r?->date ? \Carbon\Carbon::parse($r->date)->format('m/d/Y') : '—';
  $time = $r?->time ? substr($r->time,0,5) : '—';
  $totals = \App\Support\ReservationTotals::compute($r);
  $subtotal = $totals['subtotal'];
  $travel   = $totals['travel'];
  $gratuity = $totals['gratuity'];
  $adjustments = $totals['adjustments'];
  $tax      = $totals['tax'];
  $total    = $totals['total'];
  $depositPaid = $totals['deposit_display'];
  $otherPaid   = $totals['additional_paid'];
  $paidTotal   = $totals['paid_total'];
  $balance     = $totals['balance'];
@endphp

<div class="row" style="align-items:center">
  <div>
    <h2 style="font-size:16px">Invoice #{{ $r->invoice_number ?? ($r->code ?? ('#'.$r->id)) }} <span style="color:#b21e27">· {{ $date }}</span></h2>
  </div>
  <div style="text-align:right">
    <div style="font-weight:700">Hibachi Catering</div>
    <div>9022 Pulsar Ct, Corona, CA 92883</div>
    <div>info@hibachicater.com &middot; 951-326-9602</div>
  </div>
  </div>

<div class="box">
  <h3>Bill To</h3>
  <div>{{ $r->customer_name ?? '—' }}</div>
  <div>{{ $r->company ?? '' }}</div>
  <div>{{ $r->address ?? '' }} {{ $r->city ?? '' }} {{ $r->zip_code ?? '' }}</div>
  <div>{{ $r->email ?? '' }} {{ $r->phone ? ' · '.$r->phone : '' }}</div>
</div>

<div class="box">
  <h3>Event</h3>
  <div>Date: {{ $date }} &nbsp; Time: {{ $time }} &nbsp; Guests: {{ $r->guests }}</div>
  <div>Type: {{ $r->event_type ?? '—' }} &nbsp; Setup color: {{ $r->setup_color ?? '—' }}</div>
</div>

<div class="box">
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
          <td>
            <div>{{ $it->name_snapshot }}</div>
          </td>
          <td style="color:#6b7280;font-size:11px">{{ $it->description }}</td>
          <td class="right">${{ number_format((float)$it->unit_price_snapshot,2) }}</td>
          <td class="right">{{ $it->qty }}</td>
          <td class="right">${{ number_format((float)$it->line_total,2) }}</td>
        </tr>
      @empty
        <tr><td colspan="4">No items.</td></tr>
      @endforelse
    </tbody>
  </table>

  <table style="margin-top:8px; font-size:11px">
    <tr><td class="right" style="width:80%"><strong>Subtotal</strong></td><td class="right">${{ number_format($subtotal,2) }}</td></tr>
    <tr><td class="right">Travel fee</td><td class="right">${{ number_format($travel,2) }}</td></tr>
    @if(!empty($adjustments))
      @foreach($adjustments as $a)
        <tr><td class="right">{{ $a['label'] ?? 'Adjustment' }}</td><td class="right">${{ number_format((float)($a['amount'] ?? 0),2) }}</td></tr>
      @endforeach
    @endif
    <tr><td class="right">Gratuity</td><td class="right">${{ number_format($gratuity,2) }}</td></tr>
    <tr><td class="right">Tax</td><td class="right">${{ number_format($tax,2) }}</td></tr>
    <tr><td class="right"><strong>Total</strong></td><td class="right"><strong>${{ number_format($total,2) }}</strong></td></tr>
    <tr><td class="right" style="color:#16a34a">Deposit paid</td><td class="right" style="color:#16a34a">-${{ number_format($depositPaid,2) }}</td></tr>
    @if ($otherPaid > 0)
      <tr><td class="right" style="color:#16a34a">Additional paid</td><td class="right" style="color:#16a34a">-${{ number_format($otherPaid,2) }}</td></tr>
    @endif
    <tr><td class="right" style="color:#16a34a">Total paid</td><td class="right" style="color:#16a34a">-${{ number_format($paidTotal,2) }}</td></tr>
    <tr><td class="right"><strong>Balance</strong></td><td class="right"><strong>${{ number_format($balance,2) }}</strong></td></tr>
  </table>
</div>

<div class="box">
  <div>Thank you for your business!</div>
  <div>Website: https://hibachicater.com</div>
  <div>Email: info@hibachicater.com</div>
  <div>Phone: 951-326-9602</div>
  <div style="margin-top:6px;color:#555">Please see your email for the secure link to pay any remaining balance.</div>
  </div>

</body>
</html>
