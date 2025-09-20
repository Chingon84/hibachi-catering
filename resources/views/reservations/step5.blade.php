{{-- resources/views/reservations/step5.blade.php --}}
@extends('layouts.app')

@section('title', 'Reservations – Step 5')

@section('content')
  @include('reservations.progress')

  @php
    $state = $state ?? [];
    $r = $reservation ?? null;
    $estimate = $state['estimate'] ?? [];
    // Prefer DB totals if present; fallback to session estimate
    $subtotal = (float)($r->subtotal ?? ($estimate['subtotal'] ?? 0));
    $travel   = (float)($r->travel_fee ?? ($estimate['travel'] ?? 0));
    $gratuity = (float)($r->gratuity ?? ($estimate['gratuity'] ?? 0));
    $tax      = (float)($r->tax ?? ($estimate['tax'] ?? 0));
    $total    = (float)($r->total ?? ($estimate['total'] ?? 0));
    // Payment breakdown: treat deposit as up to deposit_due; any extra as general paid
    // If DB has 0 (default), fallback to session 'deposit_amount' then 20%
    $depositDue   = (float)($r->deposit_due ?? 0);
    if ($depositDue <= 0) {
      $depositDue = (float) (data_get($state, 'deposit_amount', 0) ?: round($total * 0.20, 2));
    }
    $paidTotal    = (float)($r->deposit_paid ?? 0); // total received to date
    $depositPaid  = min($paidTotal, $depositDue);
    $paidNow      = max(0, round($paidTotal - $depositPaid, 2)); // other payments beyond deposit
    $balance      = (float)($r->balance ?? max(0, round($total - $paidTotal, 2)));
    $fullName = trim(($state['first_name'] ?? '') . ' ' . ($state['last_name'] ?? '')) ?: ($r->customer_name ?? '');
  @endphp

  <div class="card" style="max-width:1000px;margin:0 auto;">
    <div class="card-body">
      <div class="brand" style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
        <img src="/assets/brand/logo.png" alt="Hibachi Catering" style="height:42px;width:auto" onerror="this.style.display='none'">
        <div>
          <div style="font-weight:700">Hibachi Catering</div>
          <div style="color:#6b7280">9022 Pulsar Ct, Corona, CA 92883</div>
          <div style="color:#6b7280">
            Email: <a href="mailto:info@hibachicater.com" style="color:inherit">info@hibachicater.com</a>
            &nbsp;|&nbsp; Phone: <a href="tel:+19513269602" style="color:inherit">951-326-9602</a>
            &nbsp;|&nbsp; <a href="https://hibachicater.com" target="_blank" rel="noopener" style="color:inherit;text-decoration:underline">hibachicater.com</a>
          </div>
        </div>
      </div>

      <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:10px">
        <h2 style="margin:0">Confirmation</h2>
        <div title="Confirmed" style="display:flex;align-items:center;gap:8px;color:#16a34a;font-weight:700">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2L15 8L22 9L17 14L18 21L12 18L6 21L7 14L2 9L9 8L12 2Z" fill="#16a34a"/>
          </svg>
          <span>Payment received</span>
        </div>
      </div>

      @php
        // Robust summary: fallback to DB when session is empty
        $dState = data_get($state,'date');
        $dateFmt = $dState ? \Carbon\Carbon::parse($dState)->format('m/d/Y') : ($r? ($r->date?->format('m/d/Y') ?? '—') : '—');
        $guestsSummary = data_get($state,'guests', $r->guests ?? '—');
        $timeSummary = data_get($state,'time');
        if ($timeSummary) { try { $timeSummary = \Carbon\Carbon::parse($timeSummary)->format('g:i A'); } catch (\Throwable $e) { $timeSummary = substr($timeSummary,0,5); } }
        if (!$timeSummary && $r && $r->time) { try { $timeSummary = \Carbon\Carbon::parse($r->time)->format('g:i A'); } catch (\Throwable $e) { $timeSummary = substr($r->time,0,5); } }
      @endphp
      <div style="color:#555; font-size:14px; margin-bottom:14px">
        <b>Summary:</b>
        Guests: {{ $guestsSummary }} |
        Date: {{ $dateFmt }} |
        Time: {{ $timeSummary }}
      </div>

      <div class="rounded-xl" style="border:1px solid #d1fae5;background:#ecfdf5;border-radius:12px;padding:14px;margin-bottom:16px;color:#065f46">
        <h3 style="margin:0 0 6px; color:#065f46">Thank you for your reservation!</h3>
        <p style="margin:4px 0">We are excited to be part of your special event. Our team will contact you a few days before your event to confirm all details and ensure everything is perfect.</p>
        <p style="margin:4px 0">If you have any additional requests or updates, feel free to reach us at <a href="mailto:info@hibachicater.com" style="color:#065f46;text-decoration:underline">info@hibachicater.com</a> or <a href="tel:+19513269602" style="color:#065f46;text-decoration:underline">951-326-9602</a>.</p>
        
      </div>

      <style>
        .kv{font-size:12px}
        .kv-row{display:flex;justify-content:space-between;gap:8px;padding:2px 0}
        .kv-label{color:#374151}
        .kv-val{text-align:right}
      </style>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
        <div class="rounded-xl" style="border:1px solid #eee;border-radius:12px;padding:14px">
          <h3 style="margin:0 0 8px; font-size:14px">Client details</h3>
          <div class="kv">
            <div class="kv-row"><div class="kv-label">Name</div><div class="kv-val">{{ $fullName ?: '—' }}</div></div>
            <div class="kv-row"><div class="kv-label">Company</div><div class="kv-val">{{ $state['company'] ?? ($r->company ?? '—') }}</div></div>
            <div class="kv-row"><div class="kv-label">Phone</div><div class="kv-val">{{ $state['phone'] ?? ($r->phone ?? '—') }}</div></div>
            <div class="kv-row"><div class="kv-label">Email</div><div class="kv-val">{{ $state['email'] ?? ($r->email ?? '—') }}</div></div>
            <div class="kv-row"><div class="kv-label">Address</div><div class="kv-val">{{ $state['address'] ?? ($r->address ?? '—') }}</div></div>
            <div class="kv-row"><div class="kv-label">City/ZIP</div><div class="kv-val">{{ ($state['city'] ?? ($r->city ?? '')) }} {{ ($state['zip_code'] ?? ($r->zip_code ?? '')) }}</div></div>
          </div>
          <div style="height:1px;background:#eee;margin:10px 0"></div>
          <h3 style="margin:0 0 8px; font-size:14px">Event</h3>
          <div class="kv">
            <div class="kv-row"><div class="kv-label">Type</div><div class="kv-val">{{ $state['event_type'] ?? ($r->event_type ?? '—') }}</div></div>
            <div class="kv-row"><div class="kv-label">Setup color</div><div class="kv-val">{{ $state['setup_color'] ?? ($r->setup_color ?? '—') }}</div></div>
            <div class="kv-row"><div class="kv-label">Stairs</div><div class="kv-val">{{ ($state['stairs'] ?? ($r->stairs ?? false)) ? 'Yes' : 'No' }}</div></div>
            <div class="kv-row"><div class="kv-label">Notes</div><div class="kv-val" style="max-width:60ch;text-align:left">{{ $state['notes'] ?? ($r->notes ?? '—') }}</div></div>
          </div>
        </div>

        <div class="rounded-xl" style="border:1px solid #eee;border-radius:12px;padding:14px">
          <h3 style="margin:0 0 8px; font-size:14px">Payment</h3>
          <div class="kv">
            <div class="kv-row"><div class="kv-label">Subtotal</div><div class="kv-val">${{ number_format($subtotal,2) }}</div></div>
            <div class="kv-row"><div class="kv-label">Travel fee</div><div class="kv-val">${{ number_format($travel,2) }}</div></div>
            <div class="kv-row"><div class="kv-label">Gratuity</div><div class="kv-val">${{ number_format($gratuity,2) }}</div></div>
            <div class="kv-row"><div class="kv-label">Tax</div><div class="kv-val">${{ number_format($tax,2) }}</div></div>
            <div style="height:1px;background:#eee;margin:8px 0"></div>
            <div class="kv-row"><div class="kv-label"><b>Total</b></div><div class="kv-val"><b>${{ number_format($total,2) }}</b></div></div>
            <div class="kv-row" style="color:#16a34a"><div class="kv-label">Deposit paid</div><div class="kv-val"><b>- ${{ number_format($depositPaid,2) }}</b></div></div>
            <div class="kv-row" style="color:#16a34a"><div class="kv-label">Paid</div><div class="kv-val"><b>- ${{ number_format($paidNow,2) }}</b></div></div>
            <div class="kv-row"><div class="kv-label"><b>Balance</b></div><div class="kv-val"><b>${{ number_format($balance,2) }}</b></div></div>
          </div>
        </div>
      </div>

      <div class="rounded-xl" style="border:1px solid #eee;border-radius:12px;padding:14px;margin-top:16px">
          <h3 style="margin:0 0 8px; font-size:14px">Menu</h3>
        @php $items = $r?->items ?? collect(); @endphp
        @if($items && $items->count())
          <div style="overflow:auto">
            <table style="width:100%;border-collapse:collapse;font-size:14px">
              <thead>
                <tr>
                  <th style="text-align:left;border-bottom:1px solid #eee;padding:6px 6px">Item</th>
                  <th style="text-align:left;border-bottom:1px solid #eee;padding:6px 6px">Description</th>
                  <th style="text-align:right;border-bottom:1px solid #eee;padding:6px 6px">Unit</th>
                  <th style="text-align:right;border-bottom:1px solid #eee;padding:6px 6px">Qty</th>
                  <th style="text-align:right;border-bottom:1px solid #eee;padding:6px 6px">Total</th>
                </tr>
              </thead>
              <tbody>
                @foreach($items as $it)
                  <tr>
                    <td style="padding:6px 6px">
                      <div>{{ $it->name_snapshot ?? $it->name ?? 'Item' }}</div>
                    </td>
                    <td style="padding:6px 6px; color:#6b7280; font-size:12px">{{ $it->description }}</td>
                    <td style="padding:6px 6px;text-align:right">${{ number_format((float)($it->unit_price_snapshot ?? $it->unit_price ?? 0),2) }}</td>
                    <td style="padding:6px 6px;text-align:right">{{ $it->qty }}</td>
                    <td style="padding:6px 6px;text-align:right">${{ number_format((float)($it->line_total ?? 0),2) }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <p style="color:#666">No items found.</p>
        @endif
      </div>

      <div style="margin-top:16px;display:flex;gap:10px;justify-content:space-between;align-items:center">
        <a href="https://hibachicater.com" target="_blank" rel="noopener" style="text-decoration:underline;color:#b21e27">hibachicater.com</a>
        <div style="display:flex;gap:10px;">
          <a class="btn btn-secondary" href="{{ route('reservations.new') }}">New reservation</a>
          <a class="btn" href="#" onclick="window.print();return false;">Print</a>
        </div>
      </div>
    </div>
  </div>
@endsection
