<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin – Staff Bookings (Menu)</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    :root{--brand:#b21e27;--brand-hover:#9a1a22}
    body{font-size:15px}
    .title{font-size:22px;margin:0}
    .card{background:var(--card);border:1px solid var(--border);border-radius:14px;box-shadow:0 6px 18px rgba(0,0,0,.04)}
    .card-body{padding:16px}
    .grid{display:grid;gap:12px}
    .grid.cols-2{grid-template-columns:2fr 1fr}
    @media (max-width: 900px){.grid.cols-2{grid-template-columns:1fr}}
    .label{display:block;font-weight:600;margin-bottom:6px}
    .input,.select{padding:10px 12px;border:1px solid var(--border);border-radius:10px;background:#fff}
    .btn{appearance:none;border:0;background:var(--brand);color:#fff;border-radius:10px;padding:10px 14px;cursor:pointer;font-weight:600;text-decoration:none;display:inline-block}
    .btn:hover{background:var(--brand-hover)}
    .btn.secondary{background:#4b5563}
    .muted{color:var(--muted)}
    .mi-name{font-weight:600;font-size:14px}
    .mi-price{font-weight:600;font-size:14px;margin-right:10px}
    /* Small icon button (hover/click effects) */
    .icon-btn{appearance:none;border:1px solid #e5e7eb;background:#fff;color:#b21e27;border-radius:10px;width:34px;height:34px;line-height:1;font-size:18px;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:transform .12s ease, box-shadow .12s ease, border-color .12s ease, background .12s ease}
    .icon-btn:hover{transform:translateY(-1px);box-shadow:0 4px 10px rgba(178,30,39,.15);border-color:#d1d5db;background:#fff}
    .icon-btn:active{transform:translateY(0) scale(.98);box-shadow:0 2px 6px rgba(178,30,39,.15)}
  </style>
  @php
    $cats = $menuCategories ?? [];
    $TRAVEL = (float) ($travel_fee ?? 0);
    $GRAT = (float) ($constants['GRATUITY'] ?? 0.18);
    $TAX  = (float) ($constants['TAX'] ?? 0.1025);
    $guests = (int) ($guests ?? 0);
    $name = trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? ''));
    $dateFmt = !empty($data['event_date'] ?? null) ? \Carbon\Carbon::parse($data['event_date'])->format('m/d/Y') : '—';
    $timeFmt = !empty($data['event_time'] ?? null)
      ? \Carbon\Carbon::parse($data['event_time'])->format('g:i A')
      : '—';
    $calc  = (array) ($data['calc'] ?? []);
    $selected = (array) ($data['selected_items'] ?? []);
    $valTravel = number_format((float)($calc['travel'] ?? $TRAVEL), 2, '.', '');
    $valDisc   = number_format((float)($calc['discount'] ?? 0), 2, '.', '');
    $valGrat   = number_format((float)($calc['gratuity'] ?? 0), 2, '.', '');
    $valTax    = number_format((float)($calc['tax'] ?? 0), 2, '.', '');
    $valPaid   = number_format((float)($calc['paid'] ?? 0), 2, '.', '');
    $pmInit    = $calc['payment_method'] ?? old('payment_method');
    $pdInit    = $calc['payment_date'] ?? '';
    $extrasInit= (array) ($calc['extras'] ?? []);
  @endphp
</head>
<body>
  <div class="container">
    <div class="header"></div>

    <form method="post" action="{{ route('admin.staff_bookings.step2.submit') }}">
      @csrf
      <div class="grid cols-2">
        <div>
          <div class="card" style="margin-bottom:12px"><div class="card-body">
            @php
              $company = trim((string)($data['company'] ?? ''));
              $phone   = trim((string)($data['phone'] ?? ''));
              $email   = trim((string)($data['email'] ?? ''));
              $addrParts = array_filter([
                trim((string)($data['address'] ?? '')),
                trim((string)($data['city'] ?? '')),
                trim((string)($data['zip'] ?? '')),
              ]);
              $fullAddr = implode(', ', $addrParts);
              $serving  = ucfirst((string)($data['serving_style'] ?? ''));
              $eventT   = trim((string)($data['event_type'] ?? ''));
              $color    = trim((string)($data['setup_color'] ?? ''));
              $stairs   = strtolower((string)($data['stairs'] ?? 'no')) === 'yes' ? 'Yes' : 'No';
              $heard    = trim((string)($data['heard_about'] ?? ''));
              $handled  = trim((string)($data['handled_by'] ?? ''));
              $notes    = trim((string)($data['agent_notes'] ?? ''));
            @endphp
            <style>
              .info-cols{display:grid;grid-template-columns:1fr 1fr;gap:16px}
              @media (max-width: 840px){.info-cols{grid-template-columns:1fr}}
              .info-list{display:grid;gap:6px}
              .info-row{font-size:14px;color:#6b7280}
              .info-row b{color:#374151}
            </style>
            <div class="info-cols">
              <div class="info-list">
                <div class="info-row"><b>Date:</b> {{ $dateFmt }}</div>
                <div class="info-row"><b>Full Name:</b> {{ $name ?: '—' }}</div>
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
                <div class="info-row"><b>Additional info:</b> {{ $notes ?: '—' }}</div>
                <div class="info-row"><b>Handled By:</b> {{ $handled ?: '—' }}</div>
              </div>
            </div>
          </div></div>

          <div class="card"><div class="card-body">
            <h3 style="margin:0 0 8px">Add from Menu</h3>
            <div style="display:flex;gap:8px;align-items:end;flex-wrap:wrap">
              <div style="flex:1;min-width:260px">
                <label class="label">Item</label>
                <select id="menuSelect" class="select" style="width:100%">
                  <option value="">-- Select an item --</option>
                  @foreach($cats as $cat => $items)
                    <optgroup label="{{ $cat }}">
                    @foreach($items as $code => $it)
                      <option value="{{ $code }}" data-price="{{ (float)$it['price'] }}" data-name="{{ $it['name'] }}">{{ $it['name'] }} (${{ number_format($it['price'],2) }})</option>
                    @endforeach
                    </optgroup>
                  @endforeach
                </select>
              </div>
              <div>
                <label class="label">Qty</label>
                <input id="qtyInput" type="number" min="0" value="0" class="input" style="width:100px;text-align:center;padding:6px 8px">
              </div>
              <div>
                <button type="button" class="btn" id="addBtn">Add</button>
              </div>
            </div>

            <div style="margin-top:12px">
              <table class="table" style="width:100%" id="selTable">
                <thead>
                  <tr><th>Item</th><th style="text-align:right">Unit</th><th style="text-align:right">Qty</th><th style="text-align:right">Total</th><th></th></tr>
                </thead>
                <tbody>
                  @foreach($selected as $it)
                    <tr data-code="{{ $it['code'] }}" data-price="{{ (float)($it['price'] ?? 0) }}" data-cat="{{ $it['cat'] ?? '' }}">
                      <td>{{ $it['name'] }}</td>
                      <td style="text-align:right">${{ number_format((float)($it['price'] ?? 0),2) }}</td>
                      <td style="text-align:right"><input type="number" class="input qty" value="{{ (int)($it['qty'] ?? 0) }}" min="0" style="width:90px;text-align:center;padding:6px 8px"></td>
                      <td style="text-align:right" class="line">$0.00</td>
                      <td style="text-align:right"><button type="button" class="icon-btn remove" title="Remove item" aria-label="Remove item">−</button></td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div></div>
        </div>

        <div>
          <div class="card">
            <div class="card-body">
              <div style="display:flex;align-items:center;justify-content:space-between;margin:0 0 8px">
                <h3 style="margin:0">Estimate</h3>
                <button type="button" id="addExtraFee" class="icon-btn" title="Add custom fee">+</button>
              </div>
              <div style="font-size:14px">
                <div style="display:flex;justify-content:space-between;margin:6px 0"><span>Subtotal</span><span id="subtotal">$0.00</span></div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin:6px 0">
                  <label for="travelInput" style="display:inline-block">Travel fee</label>
                  <input id="travelInput" name="travel_fee" type="number" step="0.01" min="0" value="{{ $valTravel }}" class="input" style="width:120px;text-align:center;padding:6px 8px">
                </div>
                <div id="extrasWrap">
                  @foreach($extrasInit as $ex)
                    <div class="extra-row" style="display:flex;align-items:center;justify-content:space-between;margin:6px 0;gap:8px">
                      <input class="input" name="custom_fees[label][]" placeholder="Label (e.g., Service fee)" value="{{ $ex['label'] ?? 'Custom fee' }}" style="flex:1;min-width:140px">
                      <div style="display:flex;align-items:center;gap:8px">
                        <input class="input extra-amount" name="custom_fees[amount][]" type="number" step="0.01" min="0" value="{{ number_format((float)($ex['amount'] ?? 0),2,'.','') }}" style="width:120px;text-align:center;padding:6px 8px">
                        <button type="button" class="icon-btn extra-remove" title="Remove fee" aria-label="Remove fee">−</button>
                      </div>
                    </div>
                  @endforeach
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin:6px 0">
                  <label for="discountInput" style="display:inline-block">Discount</label>
                  <input id="discountInput" name="discount" type="number" step="0.01" min="0" value="{{ $valDisc }}" class="input" style="width:120px;text-align:center;padding:6px 8px">
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin:6px 0">
                  <label for="gratuityInput" style="display:inline-block">Gratuity ({{ $GRAT*100 }}%)</label>
                  <input id="gratuityInput" name="gratuity" type="number" step="0.01" min="0" value="{{ $valGrat }}" data-manual="1" class="input" style="width:120px;text-align:center;padding:6px 8px">
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin:6px 0">
                  <label for="taxInput" style="display:inline-block">Tax ({{ $TAX*100 }}%)</label>
                  <input id="taxInput" name="tax" type="number" step="0.01" min="0" value="{{ $valTax }}" data-manual="1" class="input" style="width:120px;text-align:center;padding:6px 8px">
                </div>
                <div style="height:1px;background:#e5e7eb;margin:8px 0"></div>
                <div style="display:flex;justify-content:space-between;margin:6px 0;font-weight:700"><span>Total</span><span id="total">$0.00</span></div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin:6px 0">
                  <label for="depositPaid" style="display:inline-block">Deposit paid</label>
                  <input id="depositPaid" name="deposit_paid" type="number" step="0.01" min="0" value="{{ $valPaid }}" class="input" style="width:120px;text-align:center;padding:6px 8px">
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin:6px 0">
                  <label for="paymentMethod" style="display:inline-block">Payment method</label>
                  <select id="paymentMethod" name="payment_method" class="select" style="width:160px">
                    @php $pm = $pmInit; @endphp
                    <option value="">Select…</option>
                    @foreach(['Pending','Zelle','Venmo','Paypal','Check','Cash','Other'] as $opt)
                      <option value="{{ $opt }}" {{ ($pm===$opt)?'selected':'' }}>{{ $opt }}</option>
                    @endforeach
                  </select>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin:6px 0">
                  <label for="paymentDate" style="display:inline-block">Payment date</label>
                  <input id="paymentDate" name="payment_date" type="date" value="{{ $pdInit }}" class="input" style="width:160px">
                </div>
                <div style="height:1px;background:#e5e7eb;margin:8px 0"></div>
                <div style="display:flex;justify-content:space-between;margin:6px 0;font-weight:700"><span>Balance</span><span id="balance">$0.00</span></div>
                <div id="pkgHint" class="muted" style="margin-top:6px"></div>
              </div>
              <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:12px">
                <a class="btn secondary" href="{{ route('admin.staff_bookings.step1') }}">Back</a>
                <button class="btn" type="submit">Next</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>

  <script>
    (function(){
      const GRAT = {{ $GRAT }};
      const TAX  = {{ $TAX }};
      const travelDefault = {{ $TRAVEL }};
      const guests = {{ (int) $guests }};
      const $subtotal = document.getElementById('subtotal');
      const $gratIn = document.getElementById('gratuityInput');
      const $taxIn = document.getElementById('taxInput');
      const $total = document.getElementById('total');
      const $pkgHint = document.getElementById('pkgHint');
      const $deposit = document.getElementById('depositPaid');
      const $balance = document.getElementById('balance');
      const $discount = document.getElementById('discountInput');
      const $selTable = document.getElementById('selTable').querySelector('tbody');
      const $addBtn = document.getElementById('addBtn');
      const $sel = document.getElementById('menuSelect');
      const $qty = document.getElementById('qtyInput');

      function fmt(n){ return '$' + (n).toFixed(2); }

      function addExtraRow(label='', amount='0.00'){
        const wrap = document.getElementById('extrasWrap');
        const row = document.createElement('div');
        row.className = 'extra-row';
        row.style.cssText = 'display:flex;align-items:center;justify-content:space-between;margin:6px 0;gap:8px';
        row.innerHTML = `
          <input class=\"input\" name=\"custom_fees[label][]\" placeholder=\"Label (e.g., Service fee)\" value=\"${label}\" style=\"flex:1;min-width:140px\">
          <div style=\"display:flex;align-items:center;gap:8px\">
            <input class=\"input extra-amount\" name=\"custom_fees[amount][]\" type=\"number\" step=\"0.01\" min=\"0\" value=\"${amount}\" style=\"width:120px;text-align:center;padding:6px 8px\">
            <button type=\"button\" class=\"icon-btn extra-remove\" title=\"Remove fee\" aria-label=\"Remove fee\">−</button>
          </div>`;
        wrap.appendChild(row);
        row.querySelector('.extra-amount').addEventListener('input', recalc);
        row.querySelector('.extra-remove').addEventListener('click', (e)=>{ e.preventDefault(); if(confirm('Remove this custom fee?')){ row.remove(); recalc(); }});
      }

      function recalc(){
        let sub = 0; let pkgPeople = 0;
        $selTable.querySelectorAll('tr').forEach(tr => {
          const price = parseFloat(tr.getAttribute('data-price') || '0');
          const qty = parseInt(tr.querySelector('.qty').value || '0', 10);
          const cat = tr.getAttribute('data-cat') || '';
          const line = price * qty;
          tr.querySelector('.line').textContent = fmt(line);
          if (qty > 0) {
            sub += line;
            if (cat === 'Packages') pkgPeople += qty;
          }
        });
        let autoGrat = sub * GRAT;
        let autoTax  = sub * TAX;
        if ($gratIn && $gratIn.dataset.manual !== '1') {
          $gratIn.value = autoGrat.toFixed(2);
        }
        if ($taxIn && $taxIn.dataset.manual !== '1') {
          $taxIn.value = autoTax.toFixed(2);
        }
        const grat = parseFloat(($gratIn?.value || '0')) || 0;
        const tax  = parseFloat(($taxIn?.value || '0')) || 0;
        const travel = parseFloat(document.getElementById('travelInput')?.value || travelDefault || 0) || 0;
        const disc = parseFloat(($discount?.value || '0')) || 0;
        let extras = 0;
        document.querySelectorAll('.extra-amount').forEach(inp => { const v = parseFloat(inp.value || '0'); if (v > 0) extras += v; });
        const tot  = Math.max(0, sub + travel + extras + grat + tax - disc);
        $subtotal.textContent = fmt(sub);
        // values shown directly in inputs
        $total.textContent = fmt(tot);
        const paid = parseFloat(($deposit?.value || '0')) || 0;
        if ($balance) $balance.textContent = fmt(Math.max(0, tot - paid));
        if ($pkgHint) {
          if (guests > 0) {
            const ok = (pkgPeople === guests);
            $pkgHint.textContent = `Packages selected: ${pkgPeople} / Guests: ${guests}`;
            $pkgHint.style.color = ok ? '#16a34a' : (pkgPeople > guests ? '#b21e27' : '#d97706');
          } else {
            $pkgHint.textContent = '';
          }
        }
      }

      function ensureHiddenInputs(){
        // Remove previous hidden inputs
        document.querySelectorAll('input[name^="items["]').forEach(n => n.remove());
        // Add current rows as items[code]=qty
        const form = document.querySelector('form');
        $selTable.querySelectorAll('tr').forEach(tr => {
          const code = tr.getAttribute('data-code');
          const qty = parseInt(tr.querySelector('.qty').value || '0', 10);
          if (!code || qty <= 0) return;
          const inp = document.createElement('input');
          inp.type = 'hidden';
          inp.name = `items[${code}]`;
          inp.value = String(qty);
          form.appendChild(inp);
        });
      }

      function addRow(code, name, price, cat){
        // If exists, increment qty
        const existing = $selTable.querySelector(`tr[data-code="${CSS.escape(code)}"]`);
        if (existing){
          const q = existing.querySelector('.qty');
          q.value = String(parseInt(q.value || '0',10) + parseInt($qty.value || '0',10));
          recalc();
          ensureHiddenInputs();
          return;
        }
        const tr = document.createElement('tr');
        tr.setAttribute('data-code', code);
        tr.setAttribute('data-price', String(price));
        tr.setAttribute('data-cat', cat || '');
        tr.innerHTML = `<td>${name}</td>
          <td style=\"text-align:right\">${fmt(price)}</td>
          <td style=\"text-align:right\"><input type=\"number\" class=\"input qty\" value=\"${parseInt($qty.value||'0',10)}\" min=\"0\" style=\"width:90px;text-align:center;padding:6px 8px\"></td>
          <td style=\"text-align:right\" class=\"line\">$0.00</td>
          <td style=\"text-align:right\"><a href=\"#\" class=\"link danger remove\">Remove</a></td>`;
        $selTable.appendChild(tr);
        tr.querySelector('.qty').addEventListener('input', () => { recalc(); ensureHiddenInputs(); });
        tr.querySelector('.remove').addEventListener('click', (e)=>{ e.preventDefault(); tr.remove(); recalc(); ensureHiddenInputs(); });
        recalc();
        ensureHiddenInputs();
      }

      $addBtn.addEventListener('click', () => {
        const opt = $sel.options[$sel.selectedIndex];
        if (!opt || !opt.value) return;
        const code = opt.value;
        const price = parseFloat(opt.getAttribute('data-price') || '0');
        const name = opt.getAttribute('data-name') || code;
        const cat = opt.parentElement && opt.parentElement.label ? opt.parentElement.label : '';
        addRow(code, name, price, cat);
      });

      // Ensure hidden inputs are in sync on submit
      document.querySelector('form').addEventListener('submit', () => {
        ensureHiddenInputs();
      });

      if ($deposit) $deposit.addEventListener('input', recalc);
      if ($gratIn) $gratIn.addEventListener('input', () => { $gratIn.dataset.manual = '1'; recalc(); });
      if ($taxIn) $taxIn.addEventListener('input', () => { $taxIn.dataset.manual = '1'; recalc(); });
      const $travelInput = document.getElementById('travelInput');
      if ($travelInput) $travelInput.addEventListener('input', recalc);
      if ($discount) $discount.addEventListener('input', recalc);
      const addExtra = document.getElementById('addExtraFee');
      if (addExtra) addExtra.addEventListener('click', (e)=>{ e.preventDefault(); addExtraRow(); recalc(); });
      recalc();
    })();
  </script>
</body>
</html>
