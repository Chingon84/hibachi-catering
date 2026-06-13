<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Business Profile</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    body{background:var(--bg)}
    .page-stack{display:grid;gap:18px}
    .page-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px}
    .page-title{margin:0;font-size:30px;line-height:1.05;letter-spacing:-.03em}
    .page-copy{margin:10px 0 0;max-width:820px;color:var(--muted);font-size:14px;line-height:1.65}
    .eyebrow{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;border:1px solid #e2e8f0;background:#fff;color:#64748b;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;margin-bottom:12px}
    .status-row{display:flex;flex-wrap:wrap;gap:8px}
    .status-chip{display:inline-flex;align-items:center;padding:6px 10px;border-radius:999px;border:1px solid #e2e8f0;background:#f8fafc;color:#475569;font-size:11px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
    .status-chip.accent{border-color:#fecaca;background:#fff5f5;color:#b21e27}
    .status-chip.success{border-color:#bbf7d0;background:#ecfdf5;color:#166534}
    .status-chip.dark{border-color:#cbd5e1;background:#f8fafc;color:#334155}
    .hero-grid{display:grid;grid-template-columns:minmax(0,1.7fr) minmax(280px,1fr);gap:16px}
    .card{display:grid;gap:16px;padding:20px;border:1px solid var(--border);border-radius:20px;background:#fff;box-shadow:0 10px 24px rgba(15,23,42,.05)}
    .card-title{margin:0;font-size:18px;line-height:1.2;color:#0f172a}
    .card-copy{margin:8px 0 0;color:#475569;font-size:14px;line-height:1.6}
    .alert{border-radius:12px;padding:12px 14px;font-size:14px}
    .alert.success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
    .alert.error{background:#fee2e2;color:#7f1d1d;border:1px solid #fecaca}
    .section-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
    .section-card{display:grid;gap:16px;padding:20px;border:1px solid var(--border);border-radius:18px;background:#fff;box-shadow:0 10px 24px rgba(15,23,42,.05)}
    .field-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
    .field-grid.single{grid-template-columns:1fr}
    .field{display:grid;gap:6px}
    .field.full{grid-column:1 / -1}
    .field label{font-size:13px;font-weight:700;color:#334155}
    .field-help{font-size:12px;color:#94a3b8;line-height:1.5}
    .readonly-box{display:grid;gap:12px;padding:14px;border:1px dashed #d7dde7;border-radius:16px;background:#f8fafc}
    .logo-preview{display:flex;align-items:center;justify-content:center;min-height:140px;border:1px solid #e2e8f0;border-radius:16px;background:#fff}
    .logo-preview img{max-width:180px;max-height:60px;object-fit:contain}
    .muted-badge{display:inline-flex;align-items:center;padding:6px 10px;border-radius:999px;border:1px solid #e2e8f0;background:#f8fafc;color:#64748b;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
    .form-actions{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
    .support-list{display:grid;gap:12px}
    .support-item{padding:14px;border:1px solid #e2e8f0;border-radius:16px;background:#f8fafc}
    .support-label{font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8}
    .support-value{margin-top:8px;font-size:15px;font-weight:700;color:#0f172a}
    .custom-tax-trigger{width:100%;display:flex;align-items:center;justify-content:space-between;gap:14px;padding:14px;border:1px solid #dbe4ef;border-radius:14px;background:#f8fafc;color:#111827;text-align:left;cursor:pointer;transition:border-color .15s ease,background-color .15s ease,box-shadow .15s ease}
    .custom-tax-trigger:hover{border-color:#c8d4e3;background:#fff;box-shadow:0 8px 18px rgba(15,23,42,.06)}
    .custom-tax-trigger strong{display:block;font-size:14px;line-height:1.2;color:#0f172a}
    .custom-tax-trigger span{display:block;margin-top:4px;color:#64748b;font-size:12px;font-weight:650;line-height:1.35}
    .custom-tax-count{flex:0 0 auto;display:inline-flex;align-items:center;justify-content:center;min-width:34px;height:28px;padding:0 10px;border-radius:999px;border:1px solid #dbe4ef;background:#fff;color:#243b53;font-size:12px;font-weight:900}
    .tax-modal-backdrop{position:fixed;inset:0;background:rgba(15,23,42,.46);display:flex;align-items:center;justify-content:center;padding:24px;z-index:50}
    .tax-modal-backdrop[hidden]{display:none}
    .tax-modal{width:min(920px,100%);max-height:min(760px,calc(100vh - 48px));display:grid;grid-template-rows:auto auto 1fr auto;border:1px solid #dbe4ef;border-radius:18px;background:#fff;box-shadow:0 26px 70px rgba(15,23,42,.24);overflow:hidden}
    .tax-modal-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:18px 20px;border-bottom:1px solid #e8edf4}
    .tax-modal-title{margin:0;font-size:20px;line-height:1.2;color:#0f172a}
    .tax-modal-copy{margin:5px 0 0;color:#64748b;font-size:13px;line-height:1.5}
    .tax-modal-close{width:34px;height:34px;border:1px solid #dbe4ef;border-radius:10px;background:#fff;color:#334155;font-weight:900;cursor:pointer}
    .tax-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;padding:12px 20px;border-bottom:1px solid #edf2f7;background:#fbfdff}
    .tax-table-wrap{overflow:auto;padding:0 20px 16px}
    .tax-table{width:100%;min-width:520px;border:1px solid #dbe4ef;border-top:0;border-collapse:separate;border-spacing:0}
    .tax-table th{position:sticky;top:0;z-index:1;background:#f8fafc;border-top:1px solid #dbe4ef;border-bottom:1px solid #dbe4ef;color:#475569;font-size:11px;font-weight:900;letter-spacing:.08em;text-transform:uppercase;text-align:left;padding:8px 10px}
    .tax-table th:last-child,.tax-table td:last-child{text-align:right;width:86px}
    .tax-table td{border-bottom:1px solid #eef2f7;padding:4px 6px;background:#fff}
    .tax-table tr:last-child td{border-bottom:0}
    .tax-cell{width:100%;height:32px;border:1px solid transparent;border-radius:7px;background:#fff;color:#111827;font:inherit;font-size:13px;padding:6px 8px}
    .tax-cell:focus{outline:none;border-color:#cbd5e1;box-shadow:0 0 0 3px rgba(148,163,184,.14);background:#fbfdff}
    .tax-rate-input{text-align:right;font-variant-numeric:tabular-nums}
    .tax-delete{width:30px;height:30px;border:1px solid #fee2e2;border-radius:8px;background:#fff;color:#b91c1c;font-weight:900;cursor:pointer}
    .tax-delete:hover{background:#fef2f2}
    .tax-readonly{font-size:12px;font-weight:800;color:#64748b}
    .tax-modal-foot{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;padding:14px 20px;border-top:1px solid #e8edf4;background:#fff}
    .tax-modal-message{font-size:13px;font-weight:800;color:#64748b}
    .tax-modal-message.success{color:#166534}
    .tax-modal-message.error{color:#991b1b}
    .tax-empty-row{padding:22px;text-align:center;color:#64748b;font-weight:800;border:1px dashed #cbd5e1;border-radius:12px;background:#f8fafc}
    @media (max-width: 980px){
      .page-head{flex-direction:column}
      .hero-grid,.section-grid,.field-grid{grid-template-columns:1fr}
      .tax-modal-backdrop{align-items:stretch;padding:12px}
      .tax-modal{max-height:calc(100vh - 24px)}
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="page-stack">
      <div class="page-head">
        <div>
          <div class="eyebrow">Business Settings</div>
          <h1 class="page-title">Business Profile</h1>
          <p class="page-copy">Manage the company identity, contact details, location defaults, and brand information used across the admin panel.</p>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
          <a class="btn secondary" href="{{ $backUrl ?? route('admin.settings') }}">Back</a>
          <span class="status-chip success">Active</span>
        </div>
      </div>

      @if(session('ok'))
        <div class="alert success">{{ session('ok') }}</div>
      @endif

      @if($errors->any())
        <div class="alert error">
          <strong>Please review the highlighted fields.</strong>
          <div style="margin-top:6px">{{ $errors->first() }}</div>
        </div>
      @endif

      <div class="hero-grid">
        <section class="card">
          <div>
            <h2 class="card-title">Admin Control Center</h2>
            <p class="card-copy">This workspace stores the company profile defaults used by the admin panel. The logo upload area is intentionally placeholder-only for now; the rest of the profile fields save normally.</p>
          </div>
          <div class="status-row">
            @foreach(($section['status'] ?? []) as $chip)
              <span class="status-chip {{ $chip['tone'] ?? 'dark' }}">{{ $chip['label'] }}</span>
            @endforeach
          </div>
        </section>

        <aside class="card">
          <div>
            <h2 class="card-title">Current Defaults</h2>
            <p class="card-copy">Quick reference for the active profile values currently driving admin defaults.</p>
          </div>
          <div class="support-list">
            <div class="support-item">
              <div class="support-label">Business Name</div>
              <div class="support-value">{{ old('business_name', $profile['business_name'] ?? 'Hibachi Catering') }}</div>
            </div>
            <div class="support-item">
              <div class="support-label">HQ / Region</div>
              <div class="support-value">
                {{ old('hq_name', $profile['hq_name'] ?? 'Corona HQ') }}
                <span style="font-weight:600;color:#64748b">·</span>
                {{ old('state', $profile['state'] ?? 'CA') }}
              </div>
            </div>
            <div class="support-item">
              <div class="support-label">Tax / Time Zone</div>
              <div class="support-value">{{ old('default_tax_rate', $profile['default_tax_rate'] ?? '10.25') }}% · {{ old('timezone', $profile['timezone'] ?? 'America/Los_Angeles') }}</div>
            </div>
          </div>
        </aside>
      </div>

      <form method="post" action="{{ route('admin.settings.business-profile.update') }}" class="page-stack" novalidate>
        @csrf

        <div class="section-grid">
          <section class="section-card">
            <div>
              <h2 class="card-title">Company Identity</h2>
              <p class="card-copy">Primary business naming used across admin pages, billing, and internal references.</p>
            </div>
            <div class="field-grid">
              <div class="field">
                <label for="business_name">Business name *</label>
                <input id="business_name" name="business_name" class="input" value="{{ old('business_name', $profile['business_name'] ?? '') }}" required>
                <div class="field-help">Required display name used as the primary business identity.</div>
              </div>
              <div class="field">
                <label for="legal_business_name">Legal business name</label>
                <input id="legal_business_name" name="legal_business_name" class="input" value="{{ old('legal_business_name', $profile['legal_business_name'] ?? '') }}">
              </div>
              <div class="field">
                <label for="dba_name">DBA / Brand name</label>
                <input id="dba_name" name="dba_name" class="input" value="{{ old('dba_name', $profile['dba_name'] ?? '') }}">
              </div>
              <div class="field">
                <label for="hq_name">Main location / HQ name</label>
                <input id="hq_name" name="hq_name" class="input" value="{{ old('hq_name', $profile['hq_name'] ?? '') }}">
              </div>
            </div>
          </section>

          <section class="section-card">
            <div>
              <h2 class="card-title">Contact Information</h2>
              <p class="card-copy">Default contact channels for office operations and branded references.</p>
            </div>
            <div class="field-grid">
              <div class="field">
                <label for="business_phone">Business phone</label>
                <input id="business_phone" name="business_phone" class="input" value="{{ old('business_phone', $profile['business_phone'] ?? '') }}">
              </div>
              <div class="field">
                <label for="business_email">Business email</label>
                <input id="business_email" name="business_email" type="email" class="input" value="{{ old('business_email', $profile['business_email'] ?? '') }}">
              </div>
              <div class="field full">
                <label for="website">Website</label>
                <input id="website" name="website" type="url" class="input" value="{{ old('website', $profile['website'] ?? '') }}" placeholder="https://">
              </div>
            </div>
          </section>

          <section class="section-card">
            <div>
              <h2 class="card-title">Address &amp; Region</h2>
              <p class="card-copy">Headquarters and regional defaults used in operational references.</p>
            </div>
            <div class="field-grid">
              <div class="field full">
                <label for="business_address">Business address</label>
                <input id="business_address" name="business_address" class="input" value="{{ old('business_address', $profile['business_address'] ?? '') }}">
              </div>
              <div class="field">
                <label for="city">City</label>
                <input id="city" name="city" class="input" value="{{ old('city', $profile['city'] ?? '') }}">
              </div>
              <div class="field">
                <label for="state">State</label>
                <input id="state" name="state" class="input" value="{{ old('state', $profile['state'] ?? '') }}">
              </div>
              <div class="field">
                <label for="zip_code">ZIP code</label>
                <input id="zip_code" name="zip_code" class="input" value="{{ old('zip_code', $profile['zip_code'] ?? '') }}" placeholder="92883">
                <div class="field-help">Supports `12345` or `12345-6789`.</div>
              </div>
              <div class="field">
                <label for="country">Country</label>
                <input id="country" name="country" class="input" value="{{ old('country', $profile['country'] ?? '') }}">
              </div>
              <div class="field full">
                <label for="timezone">Time zone</label>
                <select id="timezone" name="timezone" class="select">
                  @foreach(($timezones ?? []) as $value => $label)
                    <option value="{{ $value }}" @selected(old('timezone', $profile['timezone'] ?? 'America/Los_Angeles') === $value)>{{ $label }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </section>

          <section class="section-card">
            <div>
              <h2 class="card-title">Financial Defaults</h2>
              <p class="card-copy">Core billing defaults that other settings modules can reuse later.</p>
            </div>
            <div class="field-grid single">
              <div class="field">
                <label for="default_tax_rate">Default tax rate *</label>
                <input id="default_tax_rate" name="default_tax_rate" type="number" min="0" max="15" step="0.01" class="input" value="{{ old('default_tax_rate', $profile['default_tax_rate'] ?? '10.25') }}">
                <div class="field-help">Stored as a percent value between 0.00 and 15.00.</div>
              </div>
              <div class="field">
                <button
                  class="custom-tax-trigger"
                  type="button"
                  data-custom-tax-open
                  data-can-manage="{{ $canManageCustomTax ? '1' : '0' }}"
                  data-rates='@json($customTaxRates)'
                  data-endpoint="{{ route('admin.settings.custom-tax-rates.store') }}"
                >
                  <span>
                    <strong>Custom Tax</strong>
                    <span>Manage custom tax rates by city</span>
                  </span>
                  <span class="custom-tax-count" data-custom-tax-count>{{ count($customTaxRates) }}</span>
                </button>
              </div>
            </div>
          </section>

          <section class="section-card">
            <div>
              <h2 class="card-title">Branding</h2>
              <p class="card-copy">Current brand preview is visible now. Upload flow remains intentionally placeholder-only in this first pass.</p>
            </div>
            <div class="readonly-box">
              <span class="muted-badge">Logo upload placeholder</span>
              <input class="input" type="file" disabled aria-disabled="true">
              <div class="field-help">Logo upload is not connected yet. This page preserves the placeholder so the route and layout are ready for the next phase.</div>
            </div>
            <div class="logo-preview">
              <img src="/assets/brand/logo.png" alt="Hibachi Catering logo" onerror="this.style.display='none'">
            </div>
          </section>

          <section class="section-card" style="grid-column:1 / -1">
            <div>
              <h2 class="card-title">Internal Notes</h2>
              <p class="card-copy">Administrative notes for internal use only.</p>
            </div>
            <div class="field-grid single">
              <div class="field full">
                <label for="admin_notes">Admin notes / internal notes</label>
                <textarea id="admin_notes" name="admin_notes" class="input" rows="6">{{ old('admin_notes', $profile['admin_notes'] ?? '') }}</textarea>
              </div>
            </div>
          </section>
        </div>

        <div class="form-actions">
          <div class="field-help">Business name, time zone, and tax rate are required. Upload flow remains placeholder-only.</div>
          <div style="display:flex;gap:10px;flex-wrap:wrap">
            <a class="btn secondary" href="{{ $backUrl ?? route('admin.settings') }}">Cancel</a>
            <button class="btn" type="submit">Save Changes</button>
          </div>
        </div>
      </form>

      <div class="tax-modal-backdrop" data-custom-tax-modal hidden>
        <section class="tax-modal" role="dialog" aria-modal="true" aria-labelledby="custom-tax-title">
          <header class="tax-modal-head">
            <div>
              <h2 class="tax-modal-title" id="custom-tax-title">Custom Tax Rates</h2>
              <p class="tax-modal-copy">Add city-specific tax rates used by reservations, invoices, and pricing calculations.</p>
            </div>
            <button class="tax-modal-close" type="button" data-custom-tax-close aria-label="Close custom tax rates">×</button>
          </header>
          <div class="tax-toolbar">
            <div class="tax-readonly" data-custom-tax-mode></div>
            <button class="btn secondary" type="button" data-custom-tax-add>Add Row</button>
          </div>
          <div class="tax-table-wrap">
            <table class="tax-table">
              <thead>
                <tr>
                  <th>City Name</th>
                  <th>Tax %</th>
                  <th></th>
                </tr>
              </thead>
              <tbody data-custom-tax-body></tbody>
            </table>
            <div class="tax-empty-row" data-custom-tax-empty hidden>No custom tax rates saved yet.</div>
          </div>
          <footer class="tax-modal-foot">
            <div class="tax-modal-message" data-custom-tax-message></div>
            <div style="display:flex;gap:10px;flex-wrap:wrap">
              <button class="btn secondary" type="button" data-custom-tax-close>Close</button>
              <button class="btn" type="button" data-custom-tax-save>Save Changes</button>
            </div>
          </footer>
        </section>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const trigger = document.querySelector('[data-custom-tax-open]');
      const modal = document.querySelector('[data-custom-tax-modal]');
      const tbody = document.querySelector('[data-custom-tax-body]');
      const empty = document.querySelector('[data-custom-tax-empty]');
      const addButton = document.querySelector('[data-custom-tax-add]');
      const saveButton = document.querySelector('[data-custom-tax-save]');
      const message = document.querySelector('[data-custom-tax-message]');
      const mode = document.querySelector('[data-custom-tax-mode]');
      const count = document.querySelector('[data-custom-tax-count]');
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

      if (!trigger || !modal || !tbody) return;

      const canManage = trigger.dataset.canManage === '1';
      const endpoint = trigger.dataset.endpoint;
      let rates = [];
      let dirty = false;

      try {
        rates = JSON.parse(trigger.dataset.rates || '[]').map(row => ({
          id: row.id || null,
          city_name: row.city_name || '',
          tax_rate: row.tax_rate || '',
          is_active: row.is_active !== false,
        }));
      } catch (e) {
        rates = [];
      }

      const setMessage = (text, tone = '') => {
        if (!message) return;
        message.textContent = text || '';
        message.className = `tax-modal-message ${tone}`.trim();
      };

      const markDirty = () => {
        dirty = true;
        setMessage('');
      };

      const render = () => {
        tbody.innerHTML = '';
        if (empty) empty.hidden = rates.length > 0;
        if (count) count.textContent = String(rates.length);
        if (mode) mode.textContent = canManage ? `${rates.length} custom ${rates.length === 1 ? 'city' : 'cities'}` : 'Read-only access';
        if (addButton) addButton.hidden = !canManage;
        if (saveButton) saveButton.hidden = !canManage;

        rates.forEach((row, index) => {
          const tr = document.createElement('tr');

          const cityCell = document.createElement('td');
          const cityInput = document.createElement('input');
          cityInput.className = 'tax-cell';
          cityInput.value = row.city_name || '';
          cityInput.placeholder = 'City name';
          cityInput.readOnly = !canManage;
          cityInput.addEventListener('input', () => {
            row.city_name = cityInput.value;
            markDirty();
          });
          cityCell.appendChild(cityInput);

          const rateCell = document.createElement('td');
          const rateInput = document.createElement('input');
          rateInput.className = 'tax-cell tax-rate-input';
          rateInput.type = 'number';
          rateInput.min = '0';
          rateInput.max = '100';
          rateInput.step = '0.01';
          rateInput.value = row.tax_rate || '';
          rateInput.placeholder = '0.00';
          rateInput.readOnly = !canManage;
          rateInput.addEventListener('input', () => {
            row.tax_rate = rateInput.value;
            markDirty();
          });
          rateCell.appendChild(rateInput);

          const actionCell = document.createElement('td');
          if (canManage) {
            const deleteButton = document.createElement('button');
            deleteButton.className = 'tax-delete';
            deleteButton.type = 'button';
            deleteButton.textContent = '×';
            deleteButton.setAttribute('aria-label', `Delete ${row.city_name || 'custom tax row'}`);
            deleteButton.addEventListener('click', () => {
              if (!window.confirm('Remove this custom tax row before saving?')) return;
              rates.splice(index, 1);
              markDirty();
              render();
            });
            actionCell.appendChild(deleteButton);
          }

          tr.append(cityCell, rateCell, actionCell);
          tbody.appendChild(tr);
        });
      };

      const closeModal = () => {
        if (dirty && !window.confirm('You have unsaved changes. Are you sure you want to close?')) {
          return;
        }
        modal.hidden = true;
        dirty = false;
        setMessage('');
      };

      trigger.addEventListener('click', () => {
        modal.hidden = false;
        setMessage('');
        render();
      });

      document.querySelectorAll('[data-custom-tax-close]').forEach(button => {
        button.addEventListener('click', closeModal);
      });

      addButton?.addEventListener('click', () => {
        if (!canManage) return;
        if (rates.length >= 1000) {
          setMessage('Custom Tax supports up to 1000 cities.', 'error');
          return;
        }
        rates.push({id: null, city_name: '', tax_rate: '', is_active: true});
        markDirty();
        render();
        tbody.querySelector('tr:last-child input')?.focus();
      });

      saveButton?.addEventListener('click', async () => {
        if (!canManage) return;
        setMessage('Saving...');
        saveButton.disabled = true;
        try {
          const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
              'Accept': 'application/json',
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
            },
            credentials: 'same-origin',
            body: JSON.stringify({rates}),
          });
          const payload = await response.json().catch(() => ({}));
          if (!response.ok) {
            const errors = payload.errors || {};
            const first = Object.values(errors).flat()[0] || payload.message || 'Unable to save custom tax rates.';
            setMessage(first, 'error');
            return;
          }
          rates = Array.isArray(payload.rates) ? payload.rates : rates;
          dirty = false;
          setMessage(payload.message || 'Custom tax rates saved successfully.', 'success');
          render();
        } catch (e) {
          setMessage('Unable to save custom tax rates.', 'error');
        } finally {
          saveButton.disabled = false;
        }
      });
    });
  </script>
</body>
</html>
