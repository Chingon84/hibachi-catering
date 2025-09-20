{{-- resources/views/reservations/step3.blade.php --}}
@extends('layouts.app')

@section('title','Reservations - Step 3')
@section('content')
@include('reservations.progress')
@php
  $state   = $state ?? [];
  $cats    = $menuCategories ?? [];
  $TRAVEL  = (float)($state['travel_fee'] ?? 0);
  $GRAT    = isset($constants['GRATUITY']) ? (float)$constants['GRATUITY'] : 0.18;
  $TAX     = isset($constants['TAX']) ? (float)$constants['TAX'] : 0.1025;
@endphp

<div class="mx-auto max-w-5xl">
  <div class="bg-white shadow rounded-2xl p-6 md:p-8">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-semibold">Reservations</h1>
      <div class="text-sm text-gray-500">Step 3 of 5</div>
    </div>

    {{-- Resumen superior --}}
    @php $d = data_get($state,'date'); $dateFmt = $d ? \Carbon\Carbon::parse($d)->format('m/d/Y') : '—'; @endphp
    <div class="text-sm text-gray-700 mb-6">
      <b>Summary:</b>
      Guests: {{ data_get($state,'guests','—') }}
      <span class="mx-1">|</span>
      Date: {{ $dateFmt }}
      <span class="mx-1">|</span>
      Time: {{ isset($state['time']) ? substr($state['time'],0,5) : '—' }}
    </div>

    <form method="POST" action="{{ route('reservations.submit', ['step'=>3]) }}" id="menuForm">
      @csrf

      <div class="grid md:grid-cols-3 gap-6">
        <div class="md:col-span-2">
          {{-- LISTA DE CATEGORÍAS/ITEMS --}}
          @forelse($cats as $cat => $items)
            <div class="mb-6">
              @if(!$loop->first)
                <div style="height:1px;background:#eee;margin:20px 0;"></div>
              @endif
              <h2 class="font-semibold text-lg mb-1" style="margin:0 0 4px;">{{ $cat }}</h2>
              @php
                $catKey = strtoupper(preg_replace('/[^A-Z0-9]+/','_', $cat));
              @endphp
              @if($catKey === 'PACKAGES')
                <div class="text-xs text-gray-600 mb-3" style="color:#666;margin-bottom:10px;">Mix and match packages. Enter people per package.</div>
              @elseif($catKey === 'STARTERS' || $catKey === 'EXTRAS')
                <div class="text-xs text-gray-600 mb-3" style="color:#666;margin-bottom:10px;">Optional starters and extras to enhance your event.</div>
              @endif
              <div class="rounded-lg" style="border:1px solid #e5e7eb;border-radius:10px;">
                @foreach($items as $code => $it)
                  <div style="padding:12px {{ !$loop->first ? ';border-top:1px solid #eee;margin-top:0' : '' }}">
                    <div class="menu-row" style="display:flex;gap:16px;align-items:flex-start;justify-content:space-between;">
                      <div class="menu-row-left" style="flex:1;min-width:0;">
                        @php
                          $icon = '';
                          $isPkg = ($catKey === 'PACKAGES');
                          if ($isPkg) {
                            $nm = strtoupper($it['name'] ?? '');
                            if (str_contains($nm, 'DELUXE'))   { $icon = '💎'; }
                            elseif (str_contains($nm, 'PREMIUM')) { $icon = '⭐'; }
                            elseif (str_contains($nm, 'CLASSIC')) { $icon = '🔪'; }
                          } else {
                            $nm = strtoupper($it['name'] ?? '');
                            if (str_contains($nm, 'LOBSTER')) { $icon = '🦞'; }
                            elseif (str_contains($nm, 'SCALLOP')) { $icon = '🦪'; }
                            elseif (str_contains($nm, 'SHRIMP')) { $icon = '🍤'; }
                          }
                        @endphp
                        <div class="font-medium" style="font-weight:600;display:flex;align-items:center;gap:8px;">
                          @if($icon)
                            <span aria-hidden="true" style="font-size:16px;line-height:1;">{{ $icon }}</span>
                          @endif
                          <span>{{ $it['name'] }}</span>
                        </div>
                        @if(!empty($it['desc']))
                          <div class="text-xs text-gray-500 mt-1" style="color:#666; font-size:12px; line-height:1.4;">
                            {{ $it['desc'] }}
                          </div>
                        @endif
                      </div>
                      <div class="menu-row-right" style="min-width:200px;text-align:right;">
                        <div class="text-sm text-gray-500" style="font-size:12px;color:#666;">Price</div>
                        <div style="font-weight:600;">${{ number_format($it['price'],2) }} <span class="text-sm text-gray-500" style="font-size:12px;color:#666;">/ unit</span></div>
                        <div style="margin-top:6px;display:flex;gap:6px;align-items:center;justify-content:flex-end;">
                          <input
                            type="number"
                            name="items[{{ $code }}]"
                            min="0"
                            step="1"
                            value="0"
                            class="w-24 rounded border-gray-300 qty-input"
                            style="width:96px;padding:6px 8px;border:1px solid #ddd;border-radius:8px;text-align:center;"
                            data-price="{{ (float)$it['price'] }}"
                            data-cat="{{ $cat }}"
                            aria-label="Quantity for {{ $it['name'] }}"
                          >
                        </div>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          @empty
            <p class="text-gray-500">No menu configured yet.</p>
          @endforelse
        </div>

        {{-- RESUMEN / ESTIMADO --}}
        <div class="md:col-span-1">
          <div class="sticky top-6 space-y-4">
            <div class="rounded-xl border p-4">
              <h3 class="font-semibold mb-3">Estimate</h3>
              <style>
                .est{font-size:14px}
                .est-row{display:flex;align-items:center;justify-content:flex-end;gap:8px;margin:6px 0}
                .est-label{color:#444;text-align:right}
                .est-val{min-width:auto;text-align:right;font-variant-numeric:tabular-nums}
                .est-total{font-weight:700;font-size:18px}
                .est-sep{height:1px;background:#e5e7eb;margin:8px 0}
              </style>
              <div class="est">
                <div class="est-row"><span class="est-label">Subtotal</span><span class="est-val" id="subtotal">$0.00</span></div>
                <div class="est-row"><span class="est-label">Travel fee</span><span class="est-val" id="travel">${{ number_format($TRAVEL,2) }}</span></div>
                <div class="est-row"><span class="est-label">Gratuity ({{ $GRAT*100 }}%)</span><span class="est-val" id="gratuity">$0.00</span></div>
                <div class="est-row"><span class="est-label">Tax ({{ $TAX*100 }}%)</span><span class="est-val" id="tax">$0.00</span></div>
                <div class="est-sep"></div>
                <div class="est-row est-total"><span class="est-label">Total</span><span class="est-val" id="total">$0.00</span></div>
              </div>
              <div class="text-xs mt-2" id="pkgHint"></div>
            </div>

            <div class="flex gap-3" style="margin-top:12px;">
              <a href="{{ route('reservations.step',['step'=>2]) }}" class="btn btn-secondary">Back</a>
              <button type="submit" class="btn">Continue</button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
(function(){
  const GRAT = {{ $GRAT }};
  const TAX  = {{ $TAX }};
  const travel = {{ $TRAVEL }};
  const guests = {{ (int) (data_get($state,'guests',0) ?? 0) }};
  const $qtys = document.querySelectorAll('.qty-input');
  const $subtotal = document.getElementById('subtotal');
  const $gratuity = document.getElementById('gratuity');
  const $tax = document.getElementById('tax');
  const $total = document.getElementById('total');
  const $pkgHint = document.getElementById('pkgHint');

  function fmt(n){ return '$' + (n).toFixed(2); }

  function recalc(){
    let sub = 0;
    let pkgPeople = 0;
    $qtys.forEach(inp => {
      const qty = parseInt(inp.value || '0', 10);
      const price = parseFloat(inp.dataset.price || '0');
      if(qty > 0) sub += qty * price;
      if ((inp.dataset.cat || '') === 'Packages') {
        if(qty > 0) pkgPeople += qty;
      }
    });

    const grat = sub * GRAT;
    const tax  = sub * TAX;
    const tot  = sub + travel + grat + tax;

    $subtotal.textContent = fmt(sub);
    $gratuity.textContent = fmt(grat);
    $tax.textContent = fmt(tax);
    $total.textContent = fmt(tot);

    if ($pkgHint) {
      if (guests > 0) {
        const ok = (pkgPeople === guests);
        $pkgHint.textContent = `Packages people selected: ${pkgPeople} / Guests: ${guests}`;
        $pkgHint.style.color = ok ? '#16a34a' : (pkgPeople > guests ? '#b21e27' : '#d97706');
      } else {
        $pkgHint.textContent = '';
      }
    }
  }

  $qtys.forEach(inp => inp.addEventListener('input', recalc));
  recalc();
})();
</script>
@endsection
