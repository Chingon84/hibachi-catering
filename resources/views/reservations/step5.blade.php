{{-- resources/views/reservations/step5.blade.php --}}
@extends('layouts.app')

@section('title', 'Reservations – Step 5')

@section('content')
  @include('reservations.progress')

  @php
    $state = $state ?? [];
    $r = $reservation ?? null;
    $estimate = $state['estimate'] ?? [];
    if (!empty($state['deposit_amount'])) {
      $estimate['deposit_due'] = (float) $state['deposit_amount'];
      $estimate['deposit_paid_session'] = (float) $state['deposit_amount'];
    }
    $totals = \App\Support\ReservationTotals::compute($r, $estimate);
    $subtotal = $totals['subtotal'];
    $travel   = $totals['travel'];
    $gratuity = $totals['gratuity'];
    $tax      = $totals['tax'];
    $total    = $totals['total'];
    $adjustments = $totals['adjustments'];
    $depositPaid = max(0, min((float) ($totals['paid_total'] ?? 0), $total));
    $balance  = $totals['balance'];
    $fmtAdj = function($amount) {
      $sign = $amount < 0 ? '- $' : '$';
      return $sign . number_format(abs($amount), 2);
    };
    $fullName = trim(($state['first_name'] ?? '') . ' ' . ($state['last_name'] ?? '')) ?: ($r->customer_name ?? '');

    $dState = data_get($state,'date');
    $dateFmt = $dState ? \Carbon\Carbon::parse($dState)->format('m/d/Y') : ($r? ($r->date?->format('m/d/Y') ?? '—') : '—');
    $guestsSummary = data_get($state,'guests', $r->guests ?? '—');
    $timeSummary = data_get($state,'time');
    if ($timeSummary) { try { $timeSummary = \Carbon\Carbon::parse($timeSummary)->format('g:i A'); } catch (\Throwable $e) { $timeSummary = substr($timeSummary,0,5); } }
    if (!$timeSummary && $r && $r->time) { try { $timeSummary = \Carbon\Carbon::parse($r->time)->format('g:i A'); } catch (\Throwable $e) { $timeSummary = substr($r->time,0,5); } }
  @endphp

  <div class="rs-card rs-stack-lg">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
      <div style="display:flex;align-items:center;gap:12px;">
        <img src="/assets/brand/logo.png" alt="Hibachi Catering" style="height:42px;width:auto" onerror="this.style.display='none'">
        <div>
          <div style="font-weight:700;color:#111827;">Hibachi Catering</div>
          <div class="rs-helper">9022 Pulsar Ct, Corona, CA 92883</div>
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:8px;color:#16a34a;font-weight:700;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 2L15 8L22 9L17 14L18 21L12 18L6 21L7 14L2 9L9 8L12 2Z" fill="#16a34a"/></svg>
        <span>Payment received</span>
      </div>
    </div>

    <div class="rs-summary">
      <div class="rs-summary-item">Guests: <b>{{ $guestsSummary }}</b></div>
      <div class="rs-summary-item">Date: <b>{{ $dateFmt }}</b></div>
      <div class="rs-summary-item">Time: <b>{{ $timeSummary }}</b></div>
    </div>

    <section class="rs-section">
      <h3 class="rs-section-head">Confirmation</h3>
      <div class="rs-info" style="background:#ecfdf5;border-color:#d1fae5;color:#065f46;">
        <p style="margin:0 0 6px;font-weight:600;">Thank you for your reservation!</p>
        <p style="margin:0;">Our team will contact you a few days before your event to confirm all details. If you need updates, reach us at info@hibachicater.com or 951-326-9602.</p>
      </div>
    </section>

    <section class="rs-section">
      <div class="rs-grid">
        <div class="rs-field">
          <div class="rs-info">
            <h3 class="rs-section-head" style="margin-bottom:8px;">Client details</h3>
            <div style="display:grid;gap:5px;font-size:.8rem;">
              <div style="display:flex;justify-content:space-between;gap:8px;"><span class="rs-helper">Name</span><span>{{ $fullName ?: '—' }}</span></div>
              <div style="display:flex;justify-content:space-between;gap:8px;"><span class="rs-helper">Company</span><span>{{ $state['company'] ?? ($r->company ?? '—') }}</span></div>
              <div style="display:flex;justify-content:space-between;gap:8px;"><span class="rs-helper">Phone</span><span>{{ $state['phone'] ?? ($r->phone ?? '—') }}</span></div>
              <div style="display:flex;justify-content:space-between;gap:8px;"><span class="rs-helper">Email</span><span>{{ $state['email'] ?? ($r->email ?? '—') }}</span></div>
              <div style="display:flex;justify-content:space-between;gap:8px;"><span class="rs-helper">Address</span><span>{{ $state['address'] ?? ($r->address ?? '—') }}</span></div>
              <div style="display:flex;justify-content:space-between;gap:8px;"><span class="rs-helper">City/ZIP</span><span>{{ ($state['city'] ?? ($r->city ?? '')) }} {{ ($state['zip_code'] ?? ($r->zip_code ?? '')) }}</span></div>
            </div>
          </div>
        </div>

        <div class="rs-field">
          <div class="rs-info">
            <h3 class="rs-section-head" style="margin-bottom:8px;">Payment</h3>
            <div style="display:grid;gap:5px;font-size:.8rem;">
              <div style="display:flex;justify-content:space-between;gap:8px;"><span class="rs-helper">Subtotal</span><span>${{ number_format($subtotal,2) }}</span></div>
              <div style="display:flex;justify-content:space-between;gap:8px;"><span class="rs-helper">Travel fee</span><span>${{ number_format($travel,2) }}</span></div>
              @foreach($adjustments as $adj)
                <div style="display:flex;justify-content:space-between;gap:8px;"><span class="rs-helper">{{ $adj['label'] }}</span><span>{{ $fmtAdj($adj['amount']) }}</span></div>
              @endforeach
              <div style="display:flex;justify-content:space-between;gap:8px;"><span class="rs-helper">Gratuity</span><span>${{ number_format($gratuity,2) }}</span></div>
              <div style="display:flex;justify-content:space-between;gap:8px;"><span class="rs-helper">Tax</span><span>${{ number_format($tax,2) }}</span></div>
              <div style="height:1px;background:#e5e7eb;margin:4px 0"></div>
              <div style="display:flex;justify-content:space-between;gap:8px;font-weight:700;"><span>Total</span><span>${{ number_format($total,2) }}</span></div>
              <div style="display:flex;justify-content:space-between;gap:8px;color:#16a34a;font-weight:700;"><span>Deposit paid</span><span>- ${{ number_format($depositPaid,2) }}</span></div>
              <div style="display:flex;justify-content:space-between;gap:8px;font-weight:700;"><span>Balance</span><span>${{ number_format($balance,2) }}</span></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section>
      <h3 class="rs-section-head">Menu</h3>
      @php $items = $r?->items ?? collect(); @endphp
      @if($items && $items->count())
        <div style="overflow:auto;border:1px solid #e5e7eb;border-radius:14px;">
          <table style="width:100%;border-collapse:collapse;font-size:14px;background:#fff;">
            <thead>
              <tr style="background:#f8fafc;">
                <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:8px 10px;">Item</th>
                <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:8px 10px;">Description</th>
                <th style="text-align:right;border-bottom:1px solid #e5e7eb;padding:8px 10px;">Unit</th>
                <th style="text-align:right;border-bottom:1px solid #e5e7eb;padding:8px 10px;">Qty</th>
                <th style="text-align:right;border-bottom:1px solid #e5e7eb;padding:8px 10px;">Total</th>
              </tr>
            </thead>
            <tbody>
              @foreach($items as $it)
                <tr>
                  <td style="padding:8px 10px;border-bottom:1px solid #f3f4f6;">{{ $it->name_snapshot ?? $it->name ?? 'Item' }}</td>
                  <td style="padding:8px 10px;color:#6b7280;font-size:12px;border-bottom:1px solid #f3f4f6;">{{ $it->description }}</td>
                  <td style="padding:8px 10px;text-align:right;border-bottom:1px solid #f3f4f6;">${{ number_format((float)($it->unit_price_snapshot ?? $it->unit_price ?? 0),2) }}</td>
                  <td style="padding:8px 10px;text-align:right;border-bottom:1px solid #f3f4f6;">{{ $it->qty }}</td>
                  <td style="padding:8px 10px;text-align:right;border-bottom:1px solid #f3f4f6;">${{ number_format((float)($it->line_total ?? 0),2) }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <p class="rs-helper">No items found.</p>
      @endif
    </section>

    <div class="rs-actions">
      <div class="rs-actions-group">
        <a href="https://hibachicater.com" target="_blank" rel="noopener" class="rs-helper" style="text-decoration:underline;color:#b21e27;">hibachicater.com</a>
      </div>
      <div class="rs-actions-group">
        <a class="btn btn-secondary" href="{{ route('reservations.new') }}">New reservation</a>
        <a class="btn" href="#" onclick="window.print();return false;">Print</a>
      </div>
    </div>
  </div>
@endsection
