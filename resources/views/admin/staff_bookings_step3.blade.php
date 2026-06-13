@extends('layouts.admin')

@section('title', 'Staff Bookings — Summary')

@push('styles')
  <style>
    table{width:100%;border-collapse:collapse}
    th,td{padding:8px 6px;border-bottom:1px solid var(--border);font-size:14px}
    .info-cols{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    @media (max-width: 900px){.info-cols{grid-template-columns:1fr}}
    .info-list{display:grid;gap:6px}
    .info-row{font-size:14px;color:#6b7280}
    .info-row b{color:#374151}
  </style>
@endpush

@php
  $d = $data ?? [];
  $lines = (array) ($d['selected_items'] ?? []);
  $calc = (array) ($d['calc'] ?? []);
  $fmt = fn($n)=>'$'.number_format((float)$n,2);
  $name = trim((($d['first_name'] ?? '') . ' ' . ($d['last_name'] ?? '')));
  $company = trim((string)($d['company'] ?? ''));
  $phone = $d['phone'] ?? '';
  $email = $d['email'] ?? '';
  $addrParts = array_filter([ trim((string)($d['address'] ?? '')), trim((string)($d['city'] ?? '')), trim((string)($d['zip'] ?? '')) ]);
  $fullAddr = implode(', ', $addrParts);
  $dateFmt = !empty($d['event_date'] ?? null) ? \Carbon\Carbon::parse($d['event_date'])->format('m/d/Y') : '—';
  $timeFmt = !empty($d['event_time'] ?? null) ? \Carbon\Carbon::parse($d['event_time'])->format('g:i A') : '—';
  $guests = (int)($d['guest_count'] ?? 0);
  $serving = ucfirst((string)($d['serving_style'] ?? ''));
  $eventT = $d['event_type'] ?? '';
  $color = $d['setup_color'] ?? '';
  $stairs = strtolower((string)($d['stairs'] ?? 'no')) === 'yes' ? 'Yes' : 'No';
  $heard = $d['heard_about'] ?? '';
  $handled = $d['handled_by'] ?? '';
@endphp

@section('content')
  <div class="container">
    <div class="card"><div class="card-body">
      <div style="margin-bottom:10px;color:#6b7280">Review customer details, menu items, fees and totals.</div>

      <div class="info-cols" style="margin-bottom:10px">
        <div class="info-list">
          <div class="info-row"><b>Date:</b> {{ $dateFmt }}</div>
          <div class="info-row"><b>Full Name:</b> {{ $name ?: '—' }}@if($company) <span class="muted"> — {{ $company }}</span>@endif</div>
          <div class="info-row"><b>Phone:</b> {{ $phone ?: '—' }}</div>
          <div class="info-row"><b>Email:</b> {{ $email ?: '—' }}</div>
          <div class="info-row"><b>Address:</b> {{ $fullAddr ?: '—' }}</div>
          <div class="info-row"><b>Serving style:</b> {{ $serving ?: '—' }}</div>
          <div class="info-row"><b>Setup Color:</b> {{ $color ?: '—' }}</div>
        </div>
        <div class="info-list">
          <div class="info-row"><b>Time:</b> {{ $timeFmt }}</div>
          <div class="info-row"><b>Guests:</b> {{ $guests ?: '—' }}</div>
          <div class="info-row"><b>Type of event:</b> {{ $eventT ?: '—' }}</div>
          <div class="info-row"><b>Heard about us:</b> {{ $heard ?: '—' }}</div>
          <div class="info-row"><b>Stairs:</b> {{ $stairs }}</div>
          <div class="info-row"><b>Additional info:</b> {{ ($d['agent_notes'] ?? '') ?: '—' }}</div>
          <div class="info-row"><b>Handled By:</b> {{ $handled ?: '—' }}</div>
        </div>
      </div>
      @if (!empty($lines))
        <table>
          <thead><tr><th>Item</th><th style="text-align:right">Unit</th><th style="text-align:right">Qty</th><th style="text-align:right">Total</th></tr></thead>
          <tbody>
            @foreach($lines as $it)
              <tr>
                <td>{{ $it['name'] }}</td>
                <td style="text-align:right">{{ $fmt($it['price']) }}</td>
                <td style="text-align:right">{{ $it['qty'] }}</td>
                <td style="text-align:right">{{ $fmt($it['total']) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif
      <div style="margin-top:12px;max-width:420px;margin-left:auto">
          <div style="display:flex;justify-content:space-between;margin:4px 0"><span>Subtotal</span><span>{{ $fmt($calc['subtotal'] ?? 0) }}</span></div>
          <div style="display:flex;justify-content:space-between;margin:4px 0"><span>Travel fee</span><span>{{ $fmt($calc['travel'] ?? 0) }}</span></div>
          @php $extras = (array)($calc['extras'] ?? []); @endphp
          @if(!empty($extras))
            @foreach($extras as $ex)
              <div style="display:flex;justify-content:space-between;margin:4px 0"><span>{{ $ex['label'] ?? 'Custom fee' }}</span><span>{{ $fmt($ex['amount'] ?? 0) }}</span></div>
            @endforeach
          @endif
          <div style="display:flex;justify-content:space-between;margin:4px 0"><span>Gratuity</span><span>{{ $fmt($calc['gratuity'] ?? 0) }}</span></div>
          <div style="display:flex;justify-content:space-between;margin:4px 0"><span>Tax</span><span>{{ $fmt($calc['tax'] ?? 0) }}</span></div>
          @if(isset($calc['discount']) && (float)$calc['discount'] > 0)
            <div style="display:flex;justify-content:space-between;margin:4px 0"><span>Discount</span><span>-{{ $fmt($calc['discount']) }}</span></div>
          @endif
          <div style="height:1px;background:#e5e7eb;margin:8px 0"></div>
          <div style="display:flex;justify-content:space-between;margin:4px 0;font-weight:700"><span>Total</span><span>{{ $fmt($calc['total'] ?? 0) }}</span></div>
          @if(isset($calc['paid']))
            <div style="display:flex;justify-content:space-between;margin:4px 0"><span>Deposit paid</span><span>{{ $fmt($calc['paid']) }}</span></div>
          @endif
          @if(isset($calc['payment_method']) || isset($calc['payment_date']))
            <div style="display:flex;justify-content:space-between;margin:4px 0"><span>Payment method</span><span>{{ $calc['payment_method'] ?? '—' }}</span></div>
            <div style="display:flex;justify-content:space-between;margin:4px 0"><span>Payment date</span><span>{{ $calc['payment_date'] ?? '—' }}</span></div>
          @endif
          <div style="height:1px;background:#e5e7eb;margin:8px 0"></div>
          <div style="display:flex;justify-content:space-between;margin:4px 0;font-weight:700"><span>Balance</span><span>{{ $fmt($calc['balance'] ?? 0) }}</span></div>
        </div>
      <div style="margin-top:12px;display:flex;gap:10px;justify-content:flex-end">
        <a class="btn secondary" href="{{ route('admin.staff_bookings.step2') }}">Back</a>
        <form method="post" action="{{ route('admin.staff_bookings.confirm') }}" style="margin:0">
          @csrf
          <button class="btn" type="submit">Confirm</button>
        </form>
      </div>
    </div></div>
  </div>
@endsection
