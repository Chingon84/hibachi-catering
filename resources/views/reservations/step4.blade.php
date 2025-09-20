{{-- resources/views/reservations/step4.blade.php --}}
@extends('layouts.app')

@section('title', 'Reservations – Step 4')

@section('content')
  @include('reservations.progress')

  @php
    $state = $state ?? [];
    $estimate = $state['estimate'] ?? [];
    $total = (float)($estimate['total'] ?? 0);
    $depositPct = 0.20; // 20% depósito requerido (puedes ajustar)
    $deposit = round($total * $depositPct, 2);
  @endphp

  <div class="card" style="max-width:900px;margin:0 auto;">
    <div class="card-body">
      <h2 style="margin:0 0 8px">Payment</h2>
      @if ($errors->any())
        <p style="color:#b21e27; font-weight:600; margin:4px 0 10px">{{ $errors->first() }}</p>
      @endif
      @php $d = data_get($state,'date'); $dateFmt = $d ? \Carbon\Carbon::parse($d)->format('m/d/Y') : '—'; @endphp
      <div style="color:#555; font-size:14px; margin-bottom:14px">
        <b>Summary:</b>
        Guests: {{ data_get($state,'guests','—') }} |
        Date: {{ $dateFmt }} |
        Time: {{ \Illuminate\Support\Str::substr(data_get($state,'time',''),0,5) }}
      </div>

      <div style="display:grid; grid-template-columns:1fr; gap:16px; align-items:start;">
        <div class="rounded-xl border" style="padding:16px">
          <h3 style="margin:0 0 8px">Amount</h3>
          <div style="font-size:14px">
            <div style="display:flex;justify-content:space-between;margin:6px 0"><span>Total</span><b>${{ number_format($total,2) }}</b></div>
            <div style="display:flex;justify-content:space-between;margin:6px 0"><span>Deposit (20%)</span><b>${{ number_format($deposit,2) }}</b></div>
          </div>
          <div style="height:1px;background:#eee;margin:10px 0"></div>
          <div style="display:grid;gap:8px">
            <div style="display:flex;justify-content:space-between;align-items:center">
              <span style="font-weight:600">Pay deposit now</span>
              <span style="font-weight:700;font-size:16px">${{ number_format($deposit,2) }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center">
              <span style="font-weight:600">Or pay full amount</span>
              <span style="font-weight:700;font-size:16px">${{ number_format($total,2) }}</span>
            </div>
          </div>
        </div>

        <form method="POST" action="{{ route('payments.checkout') }}" id="payDepositForm" style="background:#fff;border:1px solid #eee;border-radius:12px;padding:16px;">
          @csrf
          <div style="display:grid; gap:10px">
            <p style="color:#555">You’ll be redirected to a secure Stripe page to complete the payment.</p>
            <input type="hidden" name="deposit_amount" value="{{ number_format($deposit, 2, '.', '') }}">
          </div>

          <div style="margin-top:14px;display:flex;gap:10px;">
            <a class="btn btn-secondary" href="{{ route('reservations.step', ['step'=>3]) }}">Back</a>
            <button type="submit" class="btn">Pay Deposit</button>
          </div>
        </form>

        <form method="POST" action="{{ route('payments.checkout') }}" id="payFullForm" style="background:#fff;border:1px solid #eee;border-radius:12px;padding:16px;">
          @csrf
          <div style="display:grid; gap:10px">
            <p style="color:#555">Prefer to pay the full amount now?</p>
            <input type="hidden" name="deposit_amount" value="{{ number_format($total, 2, '.', '') }}">
          </div>

          <div style="margin-top:14px;display:flex;gap:10px;">
            <button type="submit" class="btn btn-dark">Pay Full Amount</button>
          </div>
        </form>

        
      </div>
    </div>
  </div>
@endsection
