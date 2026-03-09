<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin – Orders Breakdown</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    :root{--bg:#f7f7f9;--surface:#fff;--surface-soft:#f8fafc;--border:#e5e7eb;--text:#111827;--muted:#6b7280;--brand:#b21e27;--brand-hover:#991b1b;--shadow:0 8px 26px rgba(15,23,42,.06)}
    body{background:var(--bg)}
    .page-wrap{max-width:1280px;margin:0 auto;padding:20px 16px 28px}
    .header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px}
    .title{font-size:30px;font-weight:800;letter-spacing:-.02em}
    .btn{display:inline-flex;align-items:center;justify-content:center;padding:9px 14px;border-radius:12px;border:1px solid var(--border);background:#fff;color:var(--text);font-weight:700;font-size:13px;transition:all .15s ease}
    .btn:hover{transform:translateY(-1px);box-shadow:0 6px 16px rgba(15,23,42,.08);border-color:#cbd5e1}
    .btn.secondary{background:#fff}
    .card{background:var(--surface);border:1px solid var(--border);border-radius:18px;box-shadow:var(--shadow)}
    .card-body{padding:22px}
    .layout-grid{display:grid;grid-template-columns:minmax(0,1.7fr) minmax(300px,1fr);gap:20px}
    @media (max-width:1050px){.layout-grid{grid-template-columns:1fr}}
    .totals-card{margin-top:20px}
    .section-title{margin:0 0 12px;font-size:14px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#475569}
    .search-label{display:block;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#64748b;margin-bottom:8px}
    .search-controls{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
    .search-wrap{position:relative;max-width:440px}
    .search-wrap:before{content:"\1F50D";position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:13px;color:#64748b;pointer-events:none}
    .input{width:100%;height:42px;border-radius:12px;border:1px solid var(--border);padding:10px 12px 10px 34px;background:#fff;transition:border-color .15s ease,box-shadow .15s ease}
    .date-input{height:42px;border-radius:12px;border:1px solid var(--border);padding:10px 12px;background:#fff;transition:border-color .15s ease,box-shadow .15s ease;min-width:170px}
    .date-input:focus{outline:0;border-color:#c7d2fe;box-shadow:0 0 0 3px rgba(79,70,229,.12)}
    .input:focus{outline:0;border-color:#c7d2fe;box-shadow:0 0 0 3px rgba(79,70,229,.12)}
    .search-helper{margin-top:6px;font-size:12px;color:var(--muted)}
    .search-results{margin-top:10px;display:flex;flex-direction:column;gap:8px}
    .search-result{padding:10px 12px;border:1px solid var(--border);border-radius:12px;background:#fff;display:flex;justify-content:space-between;align-items:center;font-size:12px;cursor:pointer;transition:all .15s ease}
    .search-result .name{font-weight:700;font-size:13px;color:var(--text)}
    .search-result .meta{color:var(--muted);font-size:12px;margin-left:12px;white-space:nowrap}
    .search-result:hover{border-color:#cbd5f5;background:#f8fafc;transform:translateY(-1px)}
    .search-result.is-selected{border-color:#b21e27;background:rgba(178,30,39,.08)}
    .selected-widgets{display:grid;grid-template-columns:1fr;gap:18px;margin-top:16px}
    .selected-items-area,.selected-summary-area{background:var(--surface-soft);border:1px solid #e8edf4;border-radius:14px;padding:14px}
    .selected-items-area h3,.selected-summary-area h3{margin:0 0 10px;font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#475569}
    .selected-empty{color:var(--muted);text-align:center;padding:18px 8px;border:1px dashed #d8e0ea;border-radius:10px;background:#fff}
    .selected-items-container{overflow:auto;max-height:430px;border-radius:10px}
    .selected-summary-container{overflow:auto;max-height:430px;border-radius:10px}
    .selected-items-table,.selected-summary-table,.table{width:100%;border-collapse:separate;border-spacing:0}
    .selected-items-table thead th,.selected-summary-table thead th,.table thead th{position:sticky;top:0;z-index:1;background:#f8fafc;color:#64748b;font-weight:800;font-size:11px;padding:10px 12px;text-transform:uppercase;letter-spacing:.06em;text-align:left;border-bottom:1px solid var(--border)}
    .selected-items-table tbody td,.selected-summary-table tbody td,.table tbody td{padding:10px 12px;border-bottom:1px solid #eef2f7;font-size:13px;vertical-align:middle}
    .selected-items-table tbody tr:nth-child(even):not(.client-heading):not(.client-empty) td,.selected-summary-table tbody tr:nth-child(even) td,.table tbody tr:nth-child(even) td{background:#fcfdff}
    .selected-items-table tbody tr:hover td,.selected-summary-table tbody tr:hover td,.table tbody tr:hover td{background:#f7faff}
    .selected-items-table tbody tr.client-heading td{background:#eef7f3;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:12px;position:relative;padding-right:36px}
    .selected-items-table tbody tr.client-heading td span{display:inline-flex;margin-left:8px;padding:2px 8px;border:1px solid #cde7dc;border-radius:999px;background:#fff;color:#065f46;font-size:11px;font-weight:700;text-transform:none}
    .selected-items-table tbody tr.client-empty td{color:#6b7280;font-style:italic}
    .selected-items-table tbody td.qty{padding-left:18px;text-align:right;font-variant-numeric:tabular-nums;font-weight:700;color:#0f172a}
    .selected-summary-table tbody td.qty{text-align:right;font-variant-numeric:tabular-nums;font-weight:800;color:#0f172a}
    .client-remove-icon{position:absolute;top:50%;right:10px;transform:translateY(-50%);display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border:1px solid #fbcaca;border-radius:999px;background:#fff;color:#b21e27;font-size:14px;line-height:1;font-weight:700;cursor:pointer;transition:.12s ease}
    .client-remove-icon:hover{background:#fee2e2;border-color:#fca5a5;color:#991b1b}
    .totals-card{border-radius:20px}
    .totals-card .card-body{padding:18px}
    .portion-header{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px}
    .portion-header h3{margin:0;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#475569}
    .portion-actions{display:flex;align-items:center;gap:8px}
    .portion-status{min-height:18px;font-size:12px;color:#64748b;font-weight:600}
    .portion-status.success{color:#166534}
    .portion-status.error{color:#b91c1c}
    .portion-edit-btn{appearance:none;border:1px solid #dbe2ea;background:#fff;color:#334155;border-radius:999px;cursor:pointer;font-weight:800;padding:8px 12px;font-size:11px;letter-spacing:.05em;text-transform:uppercase;transition:.15s ease}
    .portion-edit-btn:hover{background:#f8fafc;border-color:#cbd5e1}
    .portion-save-btn,.portion-add-btn{appearance:none;border:1px solid #dbe2ea;background:#fff;color:#334155;border-radius:999px;cursor:pointer;font-weight:800;transition:.15s ease}
    .portion-save-btn{padding:8px 14px;font-size:11px;letter-spacing:.06em;text-transform:uppercase;background:var(--brand);border-color:var(--brand);color:#fff}
    .portion-save-btn:hover{background:var(--brand-hover);border-color:var(--brand-hover)}
    .portion-save-btn.secondary{background:var(--brand);border-color:var(--brand);color:#fff}
    .portion-save-btn.secondary:hover{background:var(--brand-hover);border-color:var(--brand-hover)}
    .portion-add-btn{width:34px;height:34px;font-size:18px;line-height:1;background:#fff}
    .portion-add-btn:hover{background:#f8fafc;border-color:#cbd5e1}
    .portion-table-wrap{overflow:auto;max-height:500px;border:1px solid #e2e8f0;border-radius:16px;background:#fff}
    .totals-card .table{width:100%;min-width:860px;border-collapse:separate;border-spacing:0;table-layout:fixed}
    .totals-card .table thead th{position:sticky;top:0;z-index:2;background:#f8fafc;border-bottom:1px solid #e2e8f0;color:#64748b;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;padding:10px 10px;line-height:1.2}
    .totals-card .table tbody td{padding:8px 10px;vertical-align:middle;background:#fff}
    .totals-card .table tbody tr + tr td{border-top:1px solid #eef2f7}
    .totals-card .table thead th.qty-col,.totals-card .table tbody td.qty{width:80px;text-align:right}
    .totals-card .table thead th.unit-col,.totals-card .table tbody td.unit{width:96px;text-align:center}
    .totals-card .table thead th.item-col,.totals-card .table tbody td.item-col{width:auto;text-align:left}
    .totals-card .table thead th.total-col,.totals-card .table tbody td.total{width:96px;text-align:right}
    .totals-card .table thead th.ozs-col,.totals-card .table tbody td.ozs-col{width:112px;text-align:right}
    .totals-card .table thead th.lbs-col,.totals-card .table tbody td.lbs-col{width:112px;text-align:right}
    .totals-card .table tbody td.qty,.totals-card .table tbody td.total,.totals-card .table tbody td.ozs-col,.totals-card .table tbody td.lbs-col{font-variant-numeric:tabular-nums}
    .totals-card .table tbody td .num-input,
    .totals-card .table tbody td .text-input,
    .totals-card .table tbody td .unit-select{
      height:36px;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;
      padding:6px 10px;font-size:13px;font-weight:600;color:#0f172a;max-width:100%
    }
    .totals-card .table tbody td .num-input{text-align:right}
    .totals-card .table tbody td .text-input{text-align:left}
    .totals-card .table tbody td .unit-select{text-align:center;text-align-last:center;appearance:none;-moz-appearance:none;-webkit-appearance:none}
    .totals-card .table tbody td .unit-select.pc-selected{color:#b21e27;font-weight:700}
    .totals-card .table tbody td .num-input:focus-visible,
    .totals-card .table tbody td .text-input:focus-visible,
    .totals-card .table tbody td .unit-select:focus-visible{
      outline:none;border-color:#c7d2fe;box-shadow:0 0 0 3px rgba(99,102,241,.15);background:#fff
    }
    .totals-card .table tbody td .num-input::-webkit-outer-spin-button,
    .totals-card .table tbody td .num-input::-webkit-inner-spin-button{margin:0;-webkit-appearance:none}
    .totals-card .table tbody td.qty .num-input,
    .totals-card .table tbody td.total .num-input{width:80px;margin-left:auto}
    .totals-card .table tbody td.unit .unit-select{width:72px}
    .totals-card .table tbody td.item-col .text-input{width:min(100%, 480px)}
    .totals-card .table tbody td.ozs-col .text-input,
    .totals-card .table tbody td.lbs-col .text-input{width:96px;margin-left:auto}
    .totals-card .table tbody td.lbs-col .text-input{font-weight:700;color:#b21e27}
    .totals-card .table tbody td .is-locked{background:#f1f5f9;border-color:#e2e8f0;color:#64748b;box-shadow:none;cursor:not-allowed}
    .totals-card .table tbody td .is-locked:focus-visible{outline:none;box-shadow:none;border-color:#e2e8f0}
  </style>
</head>
@php
  $items = \App\Support\MenuLabel::primaryItems();
@endphp
<body>
  <div class="page-wrap">
    <div class="header">
      <h1 class="title">Orders Breakdown</h1>
      <a href="{{ route('admin.dashboard') }}" class="btn secondary">Dashboard</a>
    </div>

    <div class="layout-grid">
      <div class="card search-card">
        <div class="card-body">
          <label class="search-label" for="ordersSearch">Search</label>
          <div class="search-controls">
            <div class="search-wrap">
              <input type="text" class="input" id="ordersSearch" placeholder="Search confirmed clients" value="{{ (string) request('q', '') }}">
            </div>
            <input type="date" class="date-input" id="ordersDate" value="{{ (string) request('date', '') }}" aria-label="Select date">
          </div>
          <div class="search-helper">Type at least 2 characters to search.</div>
          <div id="ordersSearchResults" class="search-results muted">Type at least 2 characters to search</div>

          <div class="selected-widgets">
            <div class="selected-items-area" aria-label="Selected menu items">
              <h3>Menu Items</h3>
              <div id="selectedItemsContainer" class="selected-items-container">
                <div class="selected-empty">No menu items yet.</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <h3 class="section-title">Totals by Item</h3>
          <div id="selectedSummaryContainer" class="selected-summary-container">
            <div class="selected-empty">No totals yet.</div>
          </div>
        </div>
      </div>
    </div>

    <div class="card totals-card">
      <div class="card-body">
        <div class="portion-header">
          <h3>Portions</h3>
          <div class="portion-actions">
            <button type="button" class="portion-edit-btn" id="ordersEditBtn">Edit</button>
            <button type="button" class="portion-save-btn" id="ordersSaveBtn">Save</button>
            <button type="button" class="portion-add-btn" id="portionAddRowBtn" title="Add portion row">+</button>
          </div>
        </div>
        <div id="portionStatus" class="portion-status" aria-live="polite"></div>
        <div class="portion-table-wrap">
          <table class="table">
            <colgroup>
              <col style="width:80px">
              <col style="width:96px">
              <col>
              <col style="width:96px">
              <col style="width:112px">
              <col style="width:112px">
            </colgroup>
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
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const SEARCH_URL = "{{ route('admin.orders.breakdown.search') }}";
      const resultsBox = document.getElementById('ordersSearchResults');
      const searchInput = document.getElementById('ordersSearch');
      const dateInput = document.getElementById('ordersDate');
      const itemsContainer = document.getElementById('selectedItemsContainer');
      const summaryContainer = document.getElementById('selectedSummaryContainer');
      const portionTableBody = document.querySelector('.totals-card tbody');
      const portionAddBtn = document.getElementById('portionAddRowBtn');
      const editBtn = document.getElementById('ordersEditBtn');
      const saveBtn = document.getElementById('ordersSaveBtn');
      const portionStatus = document.getElementById('portionStatus');
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
      let isEditing = false;
      let lastSavedRows = [];
      const initialQuery = searchInput ? searchInput.value.trim() : '';
      const initialDate = dateInput ? dateInput.value : '';

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
        if (!isEditing) return;
        if (tableDirty) return;
        tableDirty = true;
        saveBtn.textContent = 'Save*';
        saveBtn.classList.add('secondary');
        saveBtn.disabled = false;
      }

      function markSaved() {
        if (!saveBtn) return;
        tableDirty = false;
        saveBtn.textContent = 'Save';
        saveBtn.classList.add('secondary');
        if (!isEditing) {
          saveBtn.disabled = true;
        }
      }

      function setPortionStatus(message, type = '') {
        if (!portionStatus) return;
        portionStatus.textContent = message || '';
        portionStatus.classList.remove('success', 'error');
        if (type) {
          portionStatus.classList.add(type);
        }
      }

      function setEditingMode(nextEditing) {
        isEditing = !!nextEditing;
        const controls = portionTableBody ? portionTableBody.querySelectorAll('[data-field]') : [];
        controls.forEach(ctrl => {
          ctrl.disabled = !isEditing;
          ctrl.classList.toggle('is-locked', !isEditing);
          if ((ctrl.dataset.field === 'lbpcs' || ctrl.dataset.field === 'lbs') && !isEditing) {
            ctrl.setAttribute('readonly', 'readonly');
          }
          if ((ctrl.dataset.field === 'lbpcs' || ctrl.dataset.field === 'lbs') && isEditing) {
            ctrl.removeAttribute('readonly');
          }
        });

        if (portionAddBtn) {
          portionAddBtn.disabled = !isEditing;
          portionAddBtn.style.opacity = isEditing ? '1' : '.55';
          portionAddBtn.style.cursor = isEditing ? 'pointer' : 'not-allowed';
        }
        if (saveBtn) {
          saveBtn.disabled = !isEditing;
        }
        if (editBtn) {
          editBtn.textContent = isEditing ? 'Cancel' : 'Edit';
        }
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
        lastSavedRows = ordered.map(row => ({ ...row }));
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
          setEditingMode(false);
        } catch (err) {
          console.warn('Unable to load saved portions table', err);
          setPortionStatus('Could not load saved portions.', 'error');
          markSaved();
          setEditingMode(false);
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
          setPortionStatus('Saved.', 'success');
          setEditingMode(false);
          return true;
        } catch (err) {
          console.error('Unable to save orders breakdown totals', err);
          setPortionStatus('Could not save portions. Try again.', 'error');
          return false;
        }
      }

      loadTableState();

      if (saveBtn) {
        saveBtn.addEventListener('click', async () => {
          await saveTableState();
        });
      }

      if (editBtn) {
        editBtn.addEventListener('click', () => {
          if (!isEditing) {
            setPortionStatus('');
            setEditingMode(true);
            return;
          }

          applyTableState(lastSavedRows);
          setEditingMode(false);
          setPortionStatus('Changes discarded.');
        });
      }

  getPortionRows().forEach(row => {
    attachRowListeners(row);
    updateRowOzs(row);
  });
  removeEmptyRows();

      if (portionAddBtn && portionTableBody) {
        portionAddBtn.addEventListener('click', () => {
          if (!isEditing) return;
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

      function syncFiltersUrl(q, date) {
        const url = new URL(window.location.href);
        if (q) url.searchParams.set('q', q);
        else url.searchParams.delete('q');
        if (date) url.searchParams.set('date', date);
        else url.searchParams.delete('date');
        window.history.replaceState({}, '', url.toString());
      }

      async function performSearch({ q = '', date = '', autoSelect = false } = {}) {
        const term = (q || '').trim();
        const selectedDate = (date || '').trim();

        if (!selectedDate && term.length < 2) {
          if (autoSelect) {
            selectedClients = [];
            renderSelectedItems();
          }
          setResultsMessage('Type at least 2 characters to search');
          return;
        }

        setResultsMessage('Searching…');
        syncFiltersUrl(term, selectedDate);

        try {
          const params = new URLSearchParams();
          if (term) params.set('q', term);
          if (selectedDate) params.set('date', selectedDate);
          const response = await fetch(`${SEARCH_URL}?${params.toString()}`, {
            headers: { 'Accept': 'application/json' },
          });
          if (!response.ok) {
            throw new Error('Search request failed');
          }
          const data = await response.json();
          renderResults(data);

          if (autoSelect) {
            selectedClients = (Array.isArray(data) ? data : [])
              .map(buildClientPayload)
              .filter(Boolean);
            renderSelectedItems();
          }
        } catch (error) {
          console.error('Orders breakdown search failed', error);
          setResultsMessage('Search failed. Try again.');
        }
      }

      if (searchInput) {
        searchInput.addEventListener('input', () => {
          const term = searchInput.value.trim();
          const selectedDate = dateInput ? dateInput.value : '';
          clearTimeout(searchTimer);
          if (!selectedDate && term.length < 2) {
            syncFiltersUrl('', '');
            setResultsMessage('Type at least 2 characters to search');
            selectedClients = [];
            renderSelectedItems();
            return;
          }
          searchTimer = setTimeout(() => performSearch({
            q: term,
            date: selectedDate,
            autoSelect: true,
          }), 250);
        });
      }

      if (dateInput) {
        dateInput.addEventListener('change', () => {
          const term = searchInput ? searchInput.value.trim() : '';
          const selectedDate = dateInput.value;
          clearTimeout(searchTimer);
          performSearch({
            q: term,
            date: selectedDate,
            autoSelect: true,
          });
        });
      }

      setResultsMessage('Type at least 2 characters to search');
      renderSelectedItems();
      setEditingMode(false);
      if (initialDate || initialQuery.length >= 2) {
        performSearch({
          q: initialQuery,
          date: initialDate,
          autoSelect: true,
        });
      } else {
        syncFiltersUrl(initialQuery, initialDate);
      }
    });
  </script>
</body>
</html>
