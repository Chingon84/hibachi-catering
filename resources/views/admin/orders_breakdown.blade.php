<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin – Orders Breakdown</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    .table{border-collapse:collapse}
    .table thead th{background:var(--card);color:var(--muted);font-weight:600;text-transform:uppercase;font-size:11px;padding:4px;vertical-align:middle;text-align:center;line-height:1.2}
    .table tbody td{font-weight:500;font-size:12px;padding:4px;vertical-align:middle;text-align:center;line-height:1.2}
    .table tbody tr + tr td{border-top:1px solid var(--border)}
    .table thead th.qty-col,
    .table tbody td.qty{width:10%;padding-right:3px}
    .table thead th.unit-col,
    .table tbody td.unit{width:10%;padding-left:3px;padding-right:3px}
    .table thead th.item-col,
    .table tbody td.item-col{width:40%;padding-left:4px;padding-right:0;text-align:left}
    .table thead th.total-col,
    .table tbody td.total{width:18%;padding-left:0;padding-right:2px;position:relative;left:-110px}
    .table thead th.ozs-col,
    .table tbody td.ozs-col{width:14%;padding-left:2px;padding-right:0;position:relative;left:-110px}
    .table thead th.lbs-col,
    .table tbody td.lbs-col{width:12%;padding-left:2px;padding-right:0;text-align:center;position:relative;left:-110px;color:#b21e27}
    .table tbody td.qty,
    .table tbody td.unit,
    .table tbody td.total,
    .table tbody td.ozs-col,
    .table tbody td.lbs-col{font-variant-numeric:tabular-nums}
    .table tbody td.item-col .text-input{width:50%;max-width:none}
    .table tbody td.ozs-col .text-input{width:52px;max-width:52px}
    .table tbody td.lbs-col .text-input{width:52px;max-width:52px;text-align:center;color:#b21e27;font-weight:600}
    .table tbody td .num-input{width:52px;max-width:100%;padding:2px 3px;height:22px;line-height:1.2;border:0;border-bottom:1px solid var(--border);border-radius:0;background:#fff;text-align:center;font-size:12px;font-weight:500;-moz-appearance:textfield}
    .table tbody td.qty .num-input{width:48px}
    .table tbody td.total .num-input{width:60px}
    .table tbody td .num-input::-webkit-outer-spin-button,
    .table tbody td .num-input::-webkit-inner-spin-button{margin:0;-webkit-appearance:none}
    .table tbody td .num-input:focus{outline:none;border-bottom-color:var(--brand,#b21e27)}
    .table tbody td .num-input[readonly]{background:#f9fafb;color:#4b5563;cursor:default;border-bottom-color:#d1d5db}
    .table tbody td .text-input{width:100%;max-width:110px;padding:2px 3px;height:22px;line-height:1.2;border:0;border-bottom:1px solid var(--border);border-radius:0;background:#fff;font-size:12px;font-weight:500}
    .table tbody td .text-input:focus{outline:none;border-bottom-color:var(--brand,#b21e27)}
    .table tbody td .unit-select{width:40px;max-width:100%;padding:2px;height:22px;line-height:1.2;border:0;border-bottom:1px solid var(--border);border-radius:0;background:#fff;font-size:12px;font-weight:500;text-align:center;text-align-last:center;appearance:none;-moz-appearance:none;-webkit-appearance:none}
    .table tbody td .unit-select:focus{outline:none;border-bottom-color:var(--brand,#b21e27)}
    .table tbody td .unit-select.pc-selected{color:#b21e27;font-weight:600}
    .search-card{margin:0 auto 12px;max-width:1080px}
    .search-card label{display:block;font-size:12px;font-weight:600;color:var(--muted);margin-bottom:6px;text-transform:uppercase}
    .search-results{margin-top:8px;display:flex;flex-direction:column;gap:6px}
    .selected-widgets{display:flex;flex-direction:column;gap:16px;margin-top:12px}
    @media (min-width: 1100px){
      .selected-widgets{flex-direction:row;align-items:flex-start;gap:24px;flex-wrap:nowrap}
      .selected-items-area{flex:0 0 65%;max-width:65%}
      .selected-summary-area{flex:0 0 35%;max-width:35%}
    }
    .search-result{padding:8px 10px;border:1px solid var(--border);border-radius:10px;background:#fff;display:flex;justify-content:space-between;align-items:center;font-size:12px;cursor:pointer;transition:background .12s ease,border-color .12s ease}
    .search-result .name{font-weight:600;font-size:13px;color:var(--text)}
    .search-result .meta{color:var(--muted);font-size:12px;margin-left:12px;white-space:nowrap}
    .search-result:hover{border-color:#cbd5f5;background:#f8fafc}
    .search-result.is-selected{border-color:#b21e27;background:rgba(178,30,39,.08)}
    .selected-area{margin-top:12px}
    .selected-table{width:100%;border-collapse:separate;border-spacing:0;font-size:12px}
    .selected-table thead th{background:#f3f4f6;color:#374151;font-weight:600;font-size:11px;padding:6px 8px;text-transform:uppercase;letter-spacing:.03em;text-align:left}
    .selected-table tbody td{padding:6px 8px;border-top:1px solid var(--border)}
    .selected-table tbody tr:first-child td{border-top:0}
    .selected-table .muted{color:var(--muted);font-size:11px}
    .selected-remove{background:none;border:0;color:#b21e27;font-size:12px;cursor:pointer;text-decoration:underline;padding:0}
    .selected-remove:hover{color:#9a1a22}
    .selected-empty{color:var(--muted);text-align:center}
    .selected-items-area{margin-top:0;padding-top:10px;border-top:1px solid var(--border)}
    .selected-items-area h3{margin:0 0 8px;font-size:13px;font-weight:600;text-transform:uppercase;color:#374151}
    .selected-items-container{overflow-x:auto}
    .selected-items-table{width:100%;border-collapse:separate;border-spacing:0;font-size:12px}
    .selected-items-table thead th{background:#f3f4f6;color:#374151;font-weight:600;font-size:11px;padding:4px 6px;text-transform:uppercase;letter-spacing:.03em;text-align:left}
    .selected-items-table tbody td{padding:4px 6px;border-top:1px solid var(--border)}
    .selected-items-table tbody tr:first-of-type td{border-top:0}
    .selected-items-table tbody tr.client-heading td{background:#e8f9f2;font-weight:700;color:#111;text-transform:uppercase;font-size:13px;position:relative;padding-right:32px}
    .selected-items-table tbody tr.client-heading td span{color:#111;font-weight:700;margin-left:6px;font-size:11px;text-transform:none}
    .client-remove-icon{position:absolute;top:50%;right:8px;transform:translateY(-50%);display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border:1px solid #d1d5db;border-radius:999px;background:#fff;color:#b21e27;font-size:14px;line-height:1;font-weight:700;cursor:pointer}
    .client-remove-icon:hover{background:#fdecec;border-color:#fca5a5;color:#991b1b}
    .selected-items-table tbody tr.client-empty td{color:#6b7280;font-style:italic}
    .selected-items-table tbody td.qty{text-align:center;font-variant-numeric:tabular-nums;font-weight:600}
    .selected-summary-area{padding-top:10px;border-top:1px solid var(--border)}
    .selected-summary-area h3{margin:0 0 8px;font-size:13px;font-weight:600;text-transform:uppercase;color:#374151}
    .selected-summary-container{overflow-x:auto}
    .selected-summary-table{width:100%;border-collapse:separate;border-spacing:0;font-size:12px}
    .selected-summary-table thead th{background:#f3f4f6;color:#374151;font-weight:600;font-size:11px;padding:4px 6px;text-transform:uppercase;letter-spacing:.03em;text-align:left}
    .selected-summary-table tbody td{padding:4px 6px;border-top:1px solid var(--border)}
    .selected-summary-table tbody tr:first-of-type td{border-top:0}
    .selected-summary-table tbody td.qty{text-align:right;font-variant-numeric:tabular-nums;font-weight:600}
    .portion-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
    .portion-actions{display:flex;align-items:center;gap:6px}
    .portion-save-btn{appearance:none;border:1px solid var(--border);background:var(--card,#fff);color:var(--text,#374151);border-radius:6px;padding:4px 10px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;cursor:pointer;line-height:1.1;transition:background .12s ease,border-color .12s ease,color .12s ease}
    .portion-save-btn:hover{background:#f3f4f6;border-color:#d1d5db;color:#111}
    .portion-save-btn.secondary{background:var(--brand,#b21e27);border-color:var(--brand,#b21e27);color:#fff}
    .portion-add-btn{appearance:none;border:1px solid var(--border);background:#fff;color:#374151;border-radius:8px;width:30px;height:28px;font-size:18px;line-height:1;font-weight:600;cursor:pointer;transition:background .12s ease,border-color .12s ease,color .12s ease}
    .portion-add-btn:hover{background:#f3f4f6;border-color:#d1d5db;color:#111}
    .totals-card{max-width:760px;margin:0 auto 12px}
  </style>
</head>
@php
  $items = \App\Support\MenuLabel::primaryItems();
@endphp
<body>
  <div class="container">
    <div class="header">
      <h1 class="title" style="margin-right:auto">Orders Breakdown</h1>
      <a href="{{ route('admin.dashboard') }}" class="btn secondary">Dashboard</a>
    </div>

    <div class="card search-card">
      <div class="card-body">
        <label for="ordersSearch">Search</label>
        <input type="text" class="input" id="ordersSearch" placeholder="Search confirmed clients" style="max-width:320px">
        <div id="ordersSearchResults" class="search-results muted">Type at least 2 characters to search</div>
        <div class="selected-widgets">
          <div class="selected-items-area" aria-label="Selected menu items">
            <h3>Menu Items</h3>
            <div id="selectedItemsContainer" class="selected-items-container">
              <div class="selected-empty">No menu items yet.</div>
            </div>
          </div>
          <div class="selected-summary-area" aria-label="Items totals">
            <h3>Totals by Item</h3>
            <div id="selectedSummaryContainer" class="selected-summary-container">
              <div class="selected-empty">No totals yet.</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card totals-card">
      <div class="card-body">
        <div class="portion-header">
          <h3 style="margin:0;font-size:14px;font-weight:600;text-transform:uppercase;color:#374151">Portions</h3>
          <div class="portion-actions">
            <button type="button" class="portion-save-btn" id="ordersSaveBtn">Save</button>
            <button type="button" class="portion-add-btn" id="portionAddRowBtn" title="Add portion row">+</button>
          </div>
        </div>
        <table class="table">
          <thead>
            <tr>
              <th class="qty-col">Qty</th>
              <th class="unit-col">oz/pc</th>
              <th class="item-col">Item</th>
              <th class="total-col">Total</th>
              <th class="ozs-col">ozs</th>
              <th class="lbs-col">lbs</th>
            </tr>
          </thead>
          <tbody>
            @foreach($items as $item)
              @php
                $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $item));
                $rowKey = trim($slug, '-') . '-' . $loop->index;
              @endphp
              <tr data-item="{{ $rowKey }}" data-label="{{ $item }}">
                <td class="qty"><input type="number" class="num-input qty-input" value="0" min="0" data-field="qty"></td>
                <td class="unit">
                  <select class="unit-select unit-input" data-field="unit">
                    <option value="oz">oz</option>
                    <option value="pc">pc</option>
                  </select>
                </td>
                <td class="item-col"><input type="text" class="text-input item-name-input" value="{{ $item }}" data-field="label"></td>
                <td class="total"><input type="number" class="num-input total-input" value="0" min="0" step="0.01" data-field="total"></td>
              <td class="ozs-col"><input type="text" class="text-input lbpcs-input" value="" data-field="lbpcs" placeholder="0" readonly></td>
              <td class="lbs-col"><input type="text" class="text-input lbs-input" value="" data-field="lbs" placeholder="0" readonly></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const SEARCH_URL = "{{ route('admin.orders.breakdown.search') }}";
      const resultsBox = document.getElementById('ordersSearchResults');
      const searchInput = document.getElementById('ordersSearch');
      const itemsContainer = document.getElementById('selectedItemsContainer');
      const summaryContainer = document.getElementById('selectedSummaryContainer');
      const portionTableBody = document.querySelector('.totals-card tbody');
      const portionAddBtn = document.getElementById('portionAddRowBtn');
      const saveBtn = document.getElementById('ordersSaveBtn');
      const PORTIONS_URL = "{{ route('admin.orders.breakdown.portions') }}";
      const PORTIONS_SAVE_URL = "{{ route('admin.orders.breakdown.portions.save') }}";
      const CSRF_TOKEN = "{{ csrf_token() }}";
      const CATEGORY_ORDER = {
        'specials': 1,
        'combinations': 2,
        'seafood': 3,
        'starters': 4,
        'kids meals': 5,
        'packages': 6,
        'extras': 7,
        'custom': 8,
        'otros': 9,
      };
      const CATEGORY_PATTERNS = [
        { key: 'specials', patterns: [/special/i, /deluxe/i, /chef/i, /tomahawk/i] },
        { key: 'combinations', patterns: [/combo/i, /combination/i, /&/i] },
        { key: 'seafood', patterns: [/shrimp/i, /salmon/i, /scallop/i, /lobster/i, /tuna/i, /halib/i, /sea ?food/i, /fish/i] },
        { key: 'starters', patterns: [/salad/i, /soup/i, /edamame/i, /gyoza/i, /starter/i, /app(etizer)?/i] },
        { key: 'kids meals', patterns: [/kid/i, /child/i] },
        { key: 'packages', patterns: [/package/i, /pkg/i] },
        { key: 'extras', patterns: [/extra/i, /add/i, /upgrade/i, /heater/i, /chair/i, /rice/i, /noodle/i, /vegetarian/i, /vegetable/i] },
        { key: 'custom', patterns: [/custom/i, /per quote/i] },
      ];
      let searchTimer = null;
      let selectedClients = [];
      let tableDirty = false;
      let lastSummaryTotals = [];

      const escapeAttr = (value) => {
        if (typeof CSS !== 'undefined' && typeof CSS.escape === 'function') {
          return CSS.escape(value);
        }
        return (value || '').replace(/"/g, '\\"');
      };

      function getPortionRows() {
        return Array.from((portionTableBody || document).querySelectorAll('tr[data-item]'));
      }

      function buildPortionMeta() {
        return getPortionRows().map(row => {
        return {
          key: row.getAttribute('data-item') || '',
          row,
          qtyInput: row.querySelector('[data-field="qty"]'),
          unitInput: row.querySelector('[data-field="unit"]'),
          labelInput: row.querySelector('[data-field="label"]'),
          totalInput: row.querySelector('[data-field="total"]'),
          lbpcsInput: row.querySelector('[data-field="lbpcs"]'),
          lbsInput: row.querySelector('[data-field="lbs"]'),
          baseLabel: (row.getAttribute('data-label') || '').trim(),
        };
      });
      }

      function toNumber(value) {
        const num = parseFloat(value);
        return Number.isFinite(num) ? num : 0;
      }

      function formatOzs(value) {
        if (!Number.isFinite(value)) return '0';
        const rounded = Math.round(value * 100) / 100;
        return Number.isInteger(rounded) ? String(rounded) : rounded.toFixed(2);
      }

      function isRowEmpty(row) {
        if (!row) return false;
        const labelInput = row.querySelector('[data-field="label"]');
        const qtyInput = row.querySelector('[data-field="qty"]');
        const totalInput = row.querySelector('[data-field="total"]');
        const ozsInput = row.querySelector('[data-field="lbpcs"]');
        const lbsInput = row.querySelector('[data-field="lbs"]');
        const unitInput = row.querySelector('[data-field="unit"]');

        const label = (labelInput ? labelInput.value : '').trim();
        if (label.length > 0) {
          return false;
        }

        const hasQty = qtyInput ? toNumber(qtyInput.value) > 0 : false;
        const hasTotal = totalInput ? toNumber(totalInput.value) > 0 : false;
        const hasOzs = ozsInput ? toNumber(ozsInput.value) > 0 : false;
        const hasLbs = lbsInput ? toNumber(lbsInput.value) > 0 : false;
        const unitChanged = unitInput ? (unitInput.value && unitInput.value !== 'oz') : false;

        return !(hasQty || hasTotal || hasOzs || hasLbs || unitChanged);
      }

      function removeEmptyRows() {
        if (!portionTableBody) return;
        let removed = false;
        getPortionRows().forEach(row => {
          if (isRowEmpty(row)) {
            row.remove();
            removed = true;
          }
        });
        if (removed) {
          markDirty();
        }
      }

      function updateRowOzs(row) {
        if (!row) return;
        const qtyInput = row.querySelector('[data-field="qty"]');
        const totalInput = row.querySelector('[data-field="total"]');
        const ozsInput = row.querySelector('[data-field="lbpcs"]');
        const lbsInput = row.querySelector('[data-field="lbs"]');
        if (!ozsInput) return;
        const qty = toNumber(qtyInput ? qtyInput.value : 0);
        const total = toNumber(totalInput ? totalInput.value : 0);
        const ozsValue = qty * total;
        const display = formatOzs(ozsValue);
        if (ozsInput.value !== display) {
          ozsInput.value = display;
        }
        if (lbsInput) {
          if (ozsValue > 0) {
            const lbsValue = ozsValue / 16;
            const formatted = lbsValue.toFixed(2);
            if (lbsInput.value !== formatted) {
              lbsInput.value = formatted;
            }
            lbsInput.classList.add('has-value');
          } else {
            if (lbsInput.value !== '') {
              lbsInput.value = '';
            }
            lbsInput.classList.remove('has-value');
          }
        }
      }

      function attachRowListeners(row) {
        if (!row || row.dataset.listenersAttached === 'true') return;
        row.querySelectorAll('[data-field]').forEach(ctrl => {
          const handleValueChange = (event) => {
            if (ctrl.dataset.field === 'label') {
              row.setAttribute('data-label', ctrl.value || '');
            }
            if (ctrl.dataset.field === 'qty' || ctrl.dataset.field === 'total') {
              updateRowOzs(row);
            }
            if (ctrl.dataset.field === 'unit') {
              ctrl.classList.toggle('pc-selected', ctrl.value === 'pc');
            }
            markDirty();
            if (event && event.type === 'change') {
              removeEmptyRows();
            }
          };
          ctrl.addEventListener('input', handleValueChange);
          ctrl.addEventListener('change', handleValueChange);
        });
        row.dataset.listenersAttached = 'true';
        updateRowOzs(row);
        const unitSelect = row.querySelector('[data-field="unit"]');
        if (unitSelect) {
          unitSelect.classList.toggle('pc-selected', unitSelect.value === 'pc');
        }
      }

      function createPortionRow({ key, qty = '', unit = 'oz', label = '', total = '', lbpcs = '', lbs = '', silent = false } = {}) {
        if (!portionTableBody) return null;
        const rowKey = key || `custom-${Date.now()}`;
        const tr = document.createElement('tr');
        tr.setAttribute('data-item', rowKey);
        tr.setAttribute('data-label', label);
        tr.innerHTML = `
          <td class="qty"><input type="number" class="num-input qty-input" value="" min="0" data-field="qty"></td>
          <td class="unit">
            <select class="unit-select unit-input" data-field="unit">
              <option value="oz">oz</option>
              <option value="pc">pc</option>
            </select>
          </td>
          <td class="item-col"><input type="text" class="text-input item-name-input" value="" data-field="label" placeholder="Item name"></td>
          <td class="total"><input type="number" class="num-input total-input" value="" min="0" step="0.01" data-field="total"></td>
          <td class="ozs-col"><input type="text" class="text-input lbpcs-input" value="" data-field="lbpcs" placeholder="0" readonly></td>
          <td class="lbs-col"><input type="text" class="text-input lbs-input" value="" data-field="lbs" placeholder="0" readonly></td>
        `;
        portionTableBody.appendChild(tr);
        const qtyInput = tr.querySelector('[data-field="qty"]');
        const unitInput = tr.querySelector('[data-field="unit"]');
        const labelInput = tr.querySelector('[data-field="label"]');
        const totalInput = tr.querySelector('[data-field="total"]');
        const lbpcsInput = tr.querySelector('[data-field="lbpcs"]');
        const lbsInput = tr.querySelector('[data-field="lbs"]');
        if (qtyInput) qtyInput.value = qty;
        if (unitInput) unitInput.value = unit || 'oz';
        if (labelInput) labelInput.value = label;
        if (totalInput) totalInput.value = total;
        if (lbpcsInput) lbpcsInput.value = lbpcs;
        if (lbsInput) lbsInput.value = lbs;
        attachRowListeners(tr);
        if (!silent) markDirty();
        return tr;
      }

      function ensurePortionRow(key) {
        if (!portionTableBody) return null;
        const selector = `tr[data-item="${escapeAttr(key)}"]`;
        let row = portionTableBody.querySelector(selector);
        if (!row) {
          row = createPortionRow({ key, silent: true });
        }
        return row;
      }

      function markDirty() {
        if (!saveBtn) return;
        if (tableDirty) return;
        tableDirty = true;
        saveBtn.textContent = 'Save*';
        saveBtn.classList.add('secondary');
      }

      function markSaved() {
        if (!saveBtn) return;
        tableDirty = false;
        saveBtn.textContent = 'Save';
        saveBtn.classList.remove('secondary');
      }

      function collectTableState() {
        removeEmptyRows();
        const rows = [];
        buildPortionMeta().forEach((entry, index) => {
          let key = entry.key;
          if (!key || key === '') {
            key = `row-${Date.now()}-${index}`;
            if (entry.row) {
              entry.row.setAttribute('data-item', key);
            }
          }
          const qty = entry.qtyInput ? toNumber(entry.qtyInput.value) : 0;
          const unit = entry.unitInput ? entry.unitInput.value : 'oz';
          const label = entry.labelInput ? entry.labelInput.value.trim() : '';
          const total = entry.totalInput ? toNumber(entry.totalInput.value) : 0;
          const ozs = entry.lbpcsInput ? toNumber(entry.lbpcsInput.value) : 0;
          const lbs = entry.lbsInput ? toNumber(entry.lbsInput.value) : 0;

          if (entry.row) {
            entry.row.setAttribute('data-label', label);
          }

          rows.push({
            key,
            qty,
            unit,
            label,
            total,
            ozs,
            lbs,
            position: index,
          });
        });
        return rows;
      }

      function applyTableState(state) {
        if (!Array.isArray(state)) return;
        if (!portionTableBody) return;

        portionTableBody.innerHTML = '';
        const ordered = state.slice().sort((a, b) => (a.position ?? 0) - (b.position ?? 0));
        ordered.forEach((data, index) => {
          const row = createPortionRow({
            key: data.key,
            qty: data.qty ?? '',
            unit: data.unit ?? 'oz',
            label: data.label ?? '',
            total: data.total ?? '',
            lbpcs: data.ozs ?? '',
            lbs: data.lbs ?? '',
            silent: true,
          });
          if (row) {
            row.setAttribute('data-item', data.key);
            row.setAttribute('data-label', data.label ?? '');
            row.dataset.position = (data.position ?? index).toString();
          }
        });
        getPortionRows().forEach(row => updateRowOzs(row));
        removeEmptyRows();
        tableDirty = false;
        markSaved();
      }

      async function loadTableState() {
        try {
          const response = await fetch(PORTIONS_URL, {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
          });
          if (!response.ok) throw new Error(`HTTP ${response.status}`);
          const data = await response.json();
          applyTableState(Array.isArray(data) ? data : []);
        } catch (err) {
          console.warn('Unable to load saved portions table', err);
          markSaved();
        }
      }

      async function saveTableState() {
        try {
          const rows = collectTableState();
          const response = await fetch(PORTIONS_SAVE_URL, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': CSRF_TOKEN,
            },
            credentials: 'same-origin',
            body: JSON.stringify({ rows }),
          });
          if (!response.ok) throw new Error(`HTTP ${response.status}`);
          const data = await response.json();
          applyTableState(Array.isArray(data) ? data : []);
        } catch (err) {
          console.error('Unable to save orders breakdown totals', err);
        }
      }

      loadTableState();

      if (saveBtn) {
        saveBtn.addEventListener('click', async () => {
      await saveTableState();
    });
  }

  getPortionRows().forEach(row => {
    attachRowListeners(row);
    updateRowOzs(row);
  });
  removeEmptyRows();

      if (portionAddBtn && portionTableBody) {
        portionAddBtn.addEventListener('click', () => {
          createPortionRow({});
          if (lastSummaryTotals.length) {
            updatePortionTotals(lastSummaryTotals);
          }
        });
      }

      function setResultsMessage(message) {
        if (!resultsBox) return;
        resultsBox.classList.add('muted');
        resultsBox.innerHTML = `<div class="muted">${message}</div>`;
      }

      function sanitizeQty(value) {
        const num = Number(value);
        return Number.isFinite(num) ? num : 0;
      }

      function sanitizeClientItems(items) {
        if (!Array.isArray(items)) return [];
        return items.map(item => ({
          id: item.id,
          name: item.name || '—',
          description: item.description || '',
          qty: sanitizeQty(item.qty ?? 0),
        }));
      }

      function buildClientPayload(raw) {
        const id = Number(raw?.id);
        if (!Number.isFinite(id) || id <= 0) return null;
        return {
          id,
          name: raw.name || '—',
          email: raw.email || '',
          date: raw.date || '',
          time: raw.time || '',
          address: raw.address || '',
          city: raw.city || '',
          guests: raw.guests ?? '',
          items: sanitizeClientItems(raw.items || []),
        };
      }

      function updateResultHighlights() {
        if (!resultsBox) return;
        const selectedIds = new Set(selectedClients.map(client => Number(client.id)));
        resultsBox.querySelectorAll('.search-result').forEach(node => {
          const id = Number(node.dataset.id);
          if (selectedIds.has(id)) {
            node.classList.add('is-selected');
          } else {
            node.classList.remove('is-selected');
          }
        });
      }

      function removeClientById(id) {
        const targetId = Number(id);
        selectedClients = selectedClients.filter(client => Number(client.id) !== targetId);
        renderSelectedItems();
        updateResultHighlights();
      }

      function renderSelectedItems() {
        if (!itemsContainer) return;
        itemsContainer.innerHTML = '';
        if (summaryContainer) summaryContainer.innerHTML = '';

        if (!selectedClients.length) {
          itemsContainer.innerHTML = '<div class="selected-empty">No menu items yet.</div>';
          if (summaryContainer) summaryContainer.innerHTML = '<div class="selected-empty">No totals yet.</div>';
          lastSummaryTotals = [];
          updatePortionTotals([]);
          return;
        }

        const table = document.createElement('table');
        table.className = 'selected-items-table';
        table.innerHTML = '<thead><tr><th style="width:40%">Item</th><th style="width:40%">Description</th><th style="width:20%">Qty</th></tr></thead>';
        const tbody = document.createElement('tbody');

        selectedClients.forEach(client => {
          const headingRow = document.createElement('tr');
          headingRow.className = 'client-heading';
          const headingCell = document.createElement('td');
          headingCell.colSpan = 3;
          headingCell.textContent = client.name || '—';

          const metaParts = [
            client.date,
            client.time,
            client.guests ? `${client.guests} guests` : null,
            client.address,
            client.city,
          ].filter(Boolean);
          if (metaParts.length) {
            const metaSpan = document.createElement('span');
            metaSpan.textContent = metaParts.join(' • ');
            headingCell.appendChild(metaSpan);
          }

          const removeIcon = document.createElement('button');
          removeIcon.type = 'button';
          removeIcon.className = 'client-remove-icon';
          removeIcon.textContent = '−';
          removeIcon.setAttribute('aria-label', `Remove ${client.name || 'client'}`);
          removeIcon.addEventListener('click', () => removeClientById(client.id));
          headingCell.appendChild(removeIcon);

          headingRow.appendChild(headingCell);
          tbody.appendChild(headingRow);

          const items = Array.isArray(client.items) ? client.items : [];
          if (!items.length) {
            const emptyRow = document.createElement('tr');
            emptyRow.className = 'client-empty';
            const emptyCell = document.createElement('td');
            emptyCell.colSpan = 3;
            emptyCell.textContent = 'No items recorded.';
            emptyRow.appendChild(emptyCell);
            tbody.appendChild(emptyRow);
          } else {
            items.forEach(item => {
              const row = document.createElement('tr');
              const nameCell = document.createElement('td');
              nameCell.textContent = item.name || '—';
              const descCell = document.createElement('td');
              descCell.textContent = item.description || '—';
              const qtyCell = document.createElement('td');
              qtyCell.className = 'qty';
              qtyCell.textContent = item.qty;
              row.appendChild(nameCell);
              row.appendChild(descCell);
              row.appendChild(qtyCell);
              tbody.appendChild(row);
            });
          }
        });

        table.appendChild(tbody);
        itemsContainer.appendChild(table);

        renderSummaryTotals();
      }

      function renderSummaryTotals() {
        if (!summaryContainer) return;
        summaryContainer.innerHTML = '';
        const totals = new Map();
        selectedClients.forEach(client => {
          if (!Array.isArray(client.items)) return;
          client.items.forEach(item => {
            const name = (item?.name || '—').trim() || '—';
            const key = name.toLowerCase();
            const qty = sanitizeQty(item?.qty ?? 0);
            if (!qty) return;
            if (!totals.has(key)) totals.set(key, { name, qty: 0 });
            totals.get(key).qty += qty;
          });
        });

        if (!totals.size) {
          buildPortionMeta().forEach(entry => {
            const name = (entry.labelInput?.value || entry.baseLabel || '').trim();
            if (!name) return;
            const qty = sanitizeQty(entry.qtyInput?.value || 0);
            if (!qty) return;
            const key = name.toLowerCase();
            if (!totals.has(key)) totals.set(key, { name, qty: 0 });
            totals.get(key).qty += qty;
          });
        }

        if (!totals.size) {
          summaryContainer.innerHTML = '<div class="selected-empty">No totals yet.</div>';
          lastSummaryTotals = [];
          updatePortionTotals([]);
          return;
        }

        const ordered = Array.from(totals.values()).sort((a, b) => {
          const catA = CATEGORY_ORDER[categorizeItem(a.name)] || 99;
          const catB = CATEGORY_ORDER[categorizeItem(b.name)] || 99;
          if (catA !== catB) return catA - catB;
          return a.name.localeCompare(b.name);
        });

        const table = document.createElement('table');
        table.className = 'selected-summary-table';
        table.innerHTML = '<thead><tr><th style="width:70%">Item</th><th style="width:30%">Qty</th></tr></thead>';
        const tbody = document.createElement('tbody');
        ordered.forEach(({ name, qty }) => {
          const row = document.createElement('tr');
          const nameCell = document.createElement('td');
          nameCell.textContent = name;
          const qtyCell = document.createElement('td');
          qtyCell.className = 'qty';
          qtyCell.textContent = qty;
          row.appendChild(nameCell);
          row.appendChild(qtyCell);
          tbody.appendChild(row);
        });
        table.appendChild(tbody);
        summaryContainer.appendChild(table);
        lastSummaryTotals = ordered;
        updatePortionTotals(ordered);
      }

      function categorizeItem(name) {
        const label = (name || '').toLowerCase();
        for (const entry of CATEGORY_PATTERNS) {
          if (entry.patterns.some(regex => regex.test(label))) {
            return entry.key;
          }
        }
        return 'otros';
      }

      function normalizeLabel(value) {
        return (value || '')
          .toString()
          .trim()
          .toUpperCase()
          .replace(/[^A-Z0-9]+/g, ' ')
          .replace(/\s+/g, ' ')
          .trim();
      }

      function updatePortionTotals(summaryList) {
        const portionMeta = buildPortionMeta();
        const totalsMap = new Map();
        portionMeta.forEach(entry => totalsMap.set(entry.key, 0));

        summaryList.forEach(({ name, qty }) => {
          const normalizedName = normalizeLabel(name);
          if (!normalizedName) return;
          const haystack = ` ${normalizedName} `;
          portionMeta.forEach(entry => {
            const label = normalizeLabel(entry.labelInput?.value || entry.baseLabel);
            if (!label) return;
            const needle = ` ${label} `;
            if (haystack.includes(needle)) {
              const prev = totalsMap.get(entry.key) || 0;
              totalsMap.set(entry.key, prev + qty);
            }
          });
        });

        portionMeta.forEach(entry => {
          const totalInput = entry.totalInput;
          if (!totalInput) return;
          const nextVal = totalsMap.get(entry.key) || 0;
          const prevValNum = Number(totalInput.value || 0);
          const nextString = nextVal ? String(nextVal) : '0';
          if (nextVal !== prevValNum || totalInput.value !== nextString) {
            totalInput.value = nextString;
            markDirty();
          } else {
            totalInput.value = nextString;
          }
          updateRowOzs(entry.row);
        });
      }

      function addClient(raw) {
        const payload = buildClientPayload(raw);
        if (!payload) return;
        if (selectedClients.some(client => Number(client.id) === payload.id)) {
          return;
        }
        selectedClients.push(payload);
        renderSelectedItems();
        updateResultHighlights();
        if (searchInput) {
          searchInput.value = '';
        }
        setResultsMessage('Type at least 2 characters to search');
      }

      function renderResults(items) {
        if (!resultsBox) return;
        if (!Array.isArray(items) || !items.length) {
          setResultsMessage('No matches found');
          return;
        }

        resultsBox.classList.remove('muted');
        resultsBox.innerHTML = '';

        items.forEach(item => {
          const wrap = document.createElement('div');
          wrap.className = 'search-result';
          wrap.dataset.id = item.id;

          const name = document.createElement('div');
          name.className = 'name';
          name.textContent = item.name || '—';
          wrap.appendChild(name);

          const meta = document.createElement('div');
          meta.className = 'meta';
          const metaParts = [];
          if (item.date) metaParts.push(item.date);
          if (item.city) metaParts.push(item.city);
          if (item.time) metaParts.push(item.time);
          meta.textContent = metaParts.join(' • ');
          wrap.appendChild(meta);

          wrap.addEventListener('click', () => addClient(item));

          resultsBox.appendChild(wrap);
        });

        updateResultHighlights();
      }

      async function performSearch(term) {
        if (!term || term.length < 2) {
          setResultsMessage('Type at least 2 characters to search');
          return;
        }

        setResultsMessage('Searching…');

        try {
          const response = await fetch(`${SEARCH_URL}?q=${encodeURIComponent(term)}`, {
            headers: { 'Accept': 'application/json' },
          });
          if (!response.ok) {
            throw new Error('Search request failed');
          }
          const data = await response.json();
          renderResults(data);
        } catch (error) {
          console.error('Orders breakdown search failed', error);
          setResultsMessage('Search failed. Try again.');
        }
      }

      if (searchInput) {
        searchInput.addEventListener('input', () => {
          const term = searchInput.value.trim();
          clearTimeout(searchTimer);
          if (term.length < 2) {
            setResultsMessage('Type at least 2 characters to search');
            return;
          }
          searchTimer = setTimeout(() => performSearch(term), 250);
        });
      }

      setResultsMessage('Type at least 2 characters to search');
      renderSelectedItems();
    });
  </script>
</body>
</html>
