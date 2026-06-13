{{-- resources/views/reservations/step3.blade.php --}}
@extends('layouts.app')

@section('title','Reservations - Step 3')
@section('content')
@include('reservations.progress')
@php
  $state   = $state ?? [];
  $cats    = $menuCategories ?? [];
  $selectedItems = $selectedItems ?? [];
  $TRAVEL  = (float)($state['travel_fee'] ?? 0);
  $GRAT    = isset($constants['GRATUITY']) ? (float)$constants['GRATUITY'] : 0.18;
  $TAX     = isset($constants['TAX']) ? (float)$constants['TAX'] : 0.1025;
  $maxQty = 10000;
@endphp

<div class="rs-card rs-stack-lg">
  @php $d = data_get($state,'date'); $dateFmt = $d ? \Carbon\Carbon::parse($d)->format('m/d/Y') : '—'; @endphp
  <div class="rs-summary">
    <div class="rs-summary-item">Guests: <b>{{ data_get($state,'guests','—') }}</b></div>
    <div class="rs-summary-item">Date: <b>{{ $dateFmt }}</b></div>
    <div class="rs-summary-item">Time: <b>{{ isset($state['time']) ? substr($state['time'],0,5) : '—' }}</b></div>
  </div>

  <form method="POST" action="{{ route('reservations.submit', ['step'=>3]) }}" id="menuForm">
    @csrf

    @if ($errors->any())
      <p class="rs-helper" style="color:#b91c1c;font-weight:600">{{ $errors->first() }}</p>
    @endif

    <div class="rs-step3-layout">
      <section class="rs-step3-main rs-section" style="margin-bottom:0;border-bottom:none;padding-bottom:0;">
        <h3 class="rs-section-head">Menu Selection</h3>
        @forelse($cats as $cat => $items)
          <div style="margin-bottom:1.25rem;">
            @if(!$loop->first)
              <div style="height:1px;background:#f3f4f6;margin:16px 0;"></div>
            @endif
            <h4 style="margin:0 0 4px;font-size:1rem;font-weight:600;color:#111827;">{{ $cat }}</h4>
            @php
              $catKey = strtoupper(preg_replace('/[^A-Z0-9]+/','_', $cat));
            @endphp
            @if($catKey === 'PACKAGES')
              <div class="rs-helper" style="margin-bottom:10px;">Mix and match packages. Enter people per package.</div>
            @elseif($catKey === 'STARTERS' || $catKey === 'EXTRAS')
              <div class="rs-helper" style="margin-bottom:10px;">Optional starters and extras to enhance your event.</div>
            @endif
            <div class="rs-menu-group">
              <div class="rs-menu-items">
                @foreach($items as $code => $it)
                  <div class="rs-menu-item">
                    @php
                      $dishName = trim((string) ($it['name'] ?? ''));
                      $nameMain = $dishName;
                      $nameDesc = null;
                      if (preg_match('/^(.*?)\s*(\([^)]*\))\s*$/u', $dishName, $m)) {
                        $nameMain = trim((string) ($m[1] ?? $dishName));
                        $nameDesc = trim((string) ($m[2] ?? ''));
                      }
                    @endphp
                    <div class="rs-menu-name {{ !empty($nameDesc) ? 'has-desc' : '' }}">
                      <span class="rs-menu-title">{{ $nameMain }}</span>
                      @if(!empty($nameDesc))
                        <span class="rs-menu-desc">{{ $nameDesc }}</span>
                      @endif
                    </div>
                    <div class="rs-menu-price">${{ number_format($it['price'],2) }}</div>
                    <div>
                      <input
                        type="number"
                        name="items[{{ $code }}]"
                        min="0"
                        max="{{ $maxQty }}"
                        step="1"
                        value="{{ old("items.$code", data_get($selectedItems, $code, 0)) }}"
                        class="rs-input qty-input rs-menu-qty"
                        data-price="{{ (float)$it['price'] }}"
                        data-cat="{{ $cat }}"
                        inputmode="numeric"
                        aria-label="Quantity for {{ $it['name'] }}"
                      >
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        @empty
          <p class="rs-helper">No menu configured yet.</p>
        @endforelse
      </section>

      <aside class="rs-step3-sidebar">
        <div class="rs-summary-card">
          <h3 class="rs-section-head" style="margin-bottom:10px;">Order Summary</h3>
          <div class="rs-summary-row">
            <span>Subtotal</span>
            <span id="subtotal">$0.00</span>
          </div>
          <div class="rs-summary-row">
            <span>Total items selected</span>
            <span id="totalQty">0</span>
          </div>
          <div class="rs-summary-row">
            <span>Travel fee</span>
            <span id="travel">${{ number_format($TRAVEL,2) }}</span>
          </div>
          <div class="rs-summary-row">
            <span>Gratuity ({{ $GRAT*100 }}%)</span>
            <span id="gratuity">$0.00</span>
          </div>
          <div class="rs-summary-row">
            <span>Tax ({{ $TAX*100 }}%)</span>
            <span id="tax">$0.00</span>
          </div>
          <div class="rs-summary-total">
            <span>Total</span>
            <span id="total">$0.00</span>
          </div>
          <button type="submit" class="btn rs-summary-cta" id="continueBtn">
            <span id="continueBtnText">Continue to Payment</span>
          </button>
        </div>
      </aside>
    </div>

    <div class="rs-actions">
      <div class="rs-actions-group">
        <a href="{{ route('reservations.step',['step'=>2]) }}" class="btn btn-secondary">Back</a>
      </div>
      <div></div>
    </div>
  </form>
</div>

<script>
(function(){
  const GRAT = {{ $GRAT }};
  const TAX  = {{ $TAX }};
  const travel = {{ $TRAVEL }};
  const maxQty = {{ $maxQty }};
  const $qtys = document.querySelectorAll('.qty-input');
  const $subtotal = document.getElementById('subtotal');
  const $gratuity = document.getElementById('gratuity');
  const $tax = document.getElementById('tax');
  const $total = document.getElementById('total');
  const $totalQty = document.getElementById('totalQty');
  const $continueBtn = document.getElementById('continueBtn');
  const $continueBtnText = document.getElementById('continueBtnText');

  const prevValues = {
    subtotal: 0,
    gratuity: 0,
    tax: 0,
    total: 0,
  };

  function fmt(n){ return '$' + (n).toFixed(2); }
  function clamp(v,min,max){ return Math.max(min, Math.min(max, v)); }

  function animateMoney($el, from, to){
    const start = performance.now();
    const duration = 180;
    const delta = to - from;
    $el.classList.add('is-updating');

    function tick(now){
      const t = clamp((now - start) / duration, 0, 1);
      const eased = 1 - Math.pow(1 - t, 3);
      $el.textContent = fmt(from + (delta * eased));
      if (t < 1) {
        requestAnimationFrame(tick);
      } else {
        $el.textContent = fmt(to);
        setTimeout(() => $el.classList.remove('is-updating'), 80);
      }
    }
    requestAnimationFrame(tick);
  }

  function setMoneyAnimated(key, $el, next){
    const prev = Number(prevValues[key] ?? 0);
    if (Math.abs(prev - next) < 0.005) {
      $el.textContent = fmt(next);
      return;
    }
    animateMoney($el, prev, next);
    prevValues[key] = next;
  }

  function updateSelectedRows(){
    $qtys.forEach(inp => {
      const qty = parseInt(inp.value || '0', 10);
      const row = inp.closest('.rs-menu-item');
      if (!row) return;
      row.classList.toggle('is-selected', qty > 0);
    });
  }

  function normalizeQtyInput(inp){
    const raw = String(inp.value || '').trim();
    if (raw === '') return 0;
    let qty = parseInt(raw, 10);
    if (!Number.isFinite(qty) || qty < 0) qty = 0;
    qty = clamp(qty, 0, maxQty);
    if (String(qty) !== raw) {
      inp.value = String(qty);
    }
    return qty;
  }

  function recalc(){
    let sub = 0;
    let totalQty = 0;
    $qtys.forEach(inp => {
      const qty = normalizeQtyInput(inp);
      const price = parseFloat(inp.dataset.price || '0');
      if(qty > 0) sub += qty * price;
      if(qty > 0) totalQty += qty;
    });

    const grat = sub * GRAT;
    // California catering tax: taxable base includes food/items subtotal, travel fee,
    // and mandatory gratuity/service charge. Voluntary tips are excluded.
    const taxableBase = Math.max(0, sub + travel + grat);
    const tax  = Math.round(Math.round(taxableBase * 100) * TAX) / 100;
    const tot  = sub + travel + grat + tax;

    setMoneyAnimated('subtotal', $subtotal, sub);
    setMoneyAnimated('gratuity', $gratuity, grat);
    setMoneyAnimated('tax', $tax, tax);
    setMoneyAnimated('total', $total, tot);
    if ($totalQty) $totalQty.textContent = String(totalQty);
    updateSelectedRows();
  }

  $qtys.forEach(inp => {
    inp.addEventListener('input', recalc);
    inp.addEventListener('blur', recalc);
  });
  if ($continueBtn) {
    document.getElementById('menuForm')?.addEventListener('submit', () => {
      recalc();
      $continueBtn.classList.add('is-loading');
      if ($continueBtnText) $continueBtnText.textContent = 'Processing...';
    });
  }
  recalc();
})();
</script>
@endsection
