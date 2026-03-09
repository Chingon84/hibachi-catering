{{-- resources/views/reservations/step4.blade.php --}}
@extends('layouts.app')

@section('title', 'Reservations – Step 4')

@section('content')
  @include('reservations.progress')

  @php
    $state = $state ?? [];
    $estimate = $state['estimate'] ?? [];
    $total = (float)($estimate['total'] ?? 0);
    $depositPct = 0.20;
    $deposit = round($total * $depositPct, 2);
    $d = data_get($state,'date');
    $dateFmt = $d ? \Carbon\Carbon::parse($d)->format('m/d/Y') : '—';
  @endphp

  <div class="rs-card rs-stack-lg">
    <div class="rs-summary">
      <div class="rs-summary-item">Guests: <b>{{ data_get($state,'guests','—') }}</b></div>
      <div class="rs-summary-item">Date: <b>{{ $dateFmt }}</b></div>
      <div class="rs-summary-item">Time: <b>{{ \Illuminate\Support\Str::substr(data_get($state,'time',''),0,5) }}</b></div>
    </div>

    @if ($errors->any())
      <p class="rs-helper" style="color:#b91c1c;font-weight:600">{{ $errors->first() }}</p>
    @endif

    <section class="rs-section">
      <h3 class="rs-section-head">Payment</h3>
      <div class="rs-grid">
        <div class="rs-field">
          <div class="rs-info">
            <h4 style="margin:0 0 10px;font-size:1rem;font-weight:600;">Amount</h4>
            <div style="display:grid;gap:8px;font-size:.9rem;">
              <div style="display:flex;justify-content:space-between;"><span class="rs-helper">Total</span><b>${{ number_format($total,2) }}</b></div>
              <div style="display:flex;justify-content:space-between;"><span class="rs-helper">Deposit (20%)</span><b>${{ number_format($deposit,2) }}</b></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="rs-section">
      <h3 class="rs-section-head">Pay Deposit</h3>
      <form method="POST" action="{{ route('payments.checkout') }}" id="payDepositForm" class="rs-stack-lg">
        @csrf
        <div class="rs-info">
          <p class="rs-helper" style="margin:0 0 8px;">You’ll be redirected to a secure Stripe page to complete the payment.</p>
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <span style="font-weight:600;">Pay deposit now</span>
            <span style="font-weight:700;font-size:1.05rem;">${{ number_format($deposit,2) }}</span>
          </div>
        </div>
        <input type="hidden" name="deposit_amount" value="{{ number_format($deposit, 2, '.', '') }}">
        <input type="hidden" name="payment_type" value="deposit">
        <div class="rs-actions">
          <div class="rs-actions-group">
            <a class="btn btn-secondary" href="{{ route('reservations.step', ['step'=>3]) }}">Back</a>
          </div>
          <div class="rs-actions-group">
            <button type="submit" class="btn">Pay Deposit</button>
          </div>
        </div>
      </form>
    </section>

    <section>
      <h3 class="rs-section-head">Pay Full Amount</h3>
      <form method="POST" action="{{ route('payments.checkout') }}" id="payFullForm" class="rs-stack-lg">
        @csrf
        <div class="rs-info">
          <p class="rs-helper" style="margin:0 0 8px;">Prefer to pay the full amount now?</p>
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <span style="font-weight:600;">Full payment</span>
            <span style="font-weight:700;font-size:1.05rem;">${{ number_format($total,2) }}</span>
          </div>
        </div>
        <input type="hidden" name="deposit_amount" value="{{ number_format($total, 2, '.', '') }}">
        <input type="hidden" name="payment_type" value="full">
        <div class="rs-actions" style="margin-top:0;padding-top:0;border-top:none;">
          <div></div>
          <div class="rs-actions-group">
            <button type="submit" class="btn btn-dark">Pay Full Amount</button>
          </div>
        </div>
      </form>
    </section>
  </div>
@endsection
