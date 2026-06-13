<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reservation Rules</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    body{background:var(--bg)}
    .page-stack{display:grid;gap:18px}
    .page-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px}
    .page-title{margin:0;font-size:30px;line-height:1.05;letter-spacing:-.03em}
    .page-copy{margin:10px 0 0;max-width:860px;color:var(--muted);font-size:14px;line-height:1.65}
    .eyebrow{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;border:1px solid #e2e8f0;background:#fff;color:#64748b;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;margin-bottom:12px}
    .status-chip{display:inline-flex;align-items:center;padding:6px 10px;border-radius:999px;border:1px solid #e2e8f0;background:#f8fafc;color:#475569;font-size:11px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
    .status-chip.accent{border-color:#fecaca;background:#fff5f5;color:#b21e27}
    .status-chip.success{border-color:#bbf7d0;background:#ecfdf5;color:#166534}
    .status-chip.dark{border-color:#cbd5e1;background:#f8fafc;color:#334155}
    .status-chip.warning{border-color:#fed7aa;background:#fff7ed;color:#c2410c}
    .hero-grid{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(320px,1fr);gap:16px}
    .card,.section-card{display:grid;gap:16px;padding:20px;border:1px solid var(--border);border-radius:20px;background:#fff;box-shadow:0 10px 24px rgba(15,23,42,.05)}
    .card-title{margin:0;font-size:18px;line-height:1.2;color:#0f172a}
    .card-copy{margin:8px 0 0;color:#475569;font-size:14px;line-height:1.6}
    .status-row{display:flex;flex-wrap:wrap;gap:8px}
    .summary-list{display:grid;gap:12px}
    .summary-item{padding:14px;border:1px solid #e2e8f0;border-radius:16px;background:#f8fafc}
    .summary-label{font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8}
    .summary-value{margin-top:8px;font-size:15px;font-weight:700;color:#0f172a}
    .alert{border-radius:12px;padding:12px 14px;font-size:14px}
    .alert.success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
    .alert.error{background:#fee2e2;color:#7f1d1d;border:1px solid #fecaca}
    .section-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
    .section-card.full{grid-column:1 / -1}
    .field-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
    .field-grid.single{grid-template-columns:1fr}
    .field{display:grid;gap:6px}
    .field.full{grid-column:1 / -1}
    .field label{font-size:13px;font-weight:700;color:#334155}
    .field-help{font-size:12px;color:#94a3b8;line-height:1.5}
    .check-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
    .check-item{display:flex;align-items:flex-start;gap:10px;padding:12px 14px;border:1px solid #e2e8f0;border-radius:14px;background:#f8fafc}
    .check-item input{margin-top:2px}
    .check-label{font-size:14px;font-weight:600;color:#334155}
    .pill-note{display:inline-flex;align-items:center;padding:6px 10px;border-radius:999px;background:#f8fafc;border:1px solid #e2e8f0;color:#64748b;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
    .form-actions{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
    .action-row{display:flex;gap:10px;flex-wrap:wrap}
    .btn.ghost{background:#fff;color:#0f172a;border:1px solid #d7dde7;box-shadow:none}
    .btn.ghost:hover{background:#f8fafc;border-color:#cbd5e1}
    @media (max-width: 980px){
      .page-head{flex-direction:column}
      .hero-grid,.section-grid,.field-grid,.check-grid{grid-template-columns:1fr}
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="page-stack">
      <div class="page-head">
        <div>
          <div class="eyebrow">Reservation Settings</div>
          <h1 class="page-title">Reservation Rules</h1>
          <p class="page-copy">Manage booking limits, deposit requirements, travel fee defaults, gratuity rules, and event handling policies used across Hibachi Catering reservations.</p>
        </div>
        <div class="action-row">
          <a class="btn secondary" href="{{ $backUrl ?? route('admin.settings') }}">Back</a>
          <span class="status-chip success">Active</span>
        </div>
      </div>

      @if(session('ok'))
        <div class="alert success">{{ session('ok') }}</div>
      @endif

      @if($errors->any())
        <div class="alert error">
          <strong>Please review the reservation rule fields below.</strong>
          <div style="margin-top:6px">{{ $errors->first() }}</div>
        </div>
      @endif

      <div class="hero-grid">
        <section class="card">
          <div>
            <h2 class="card-title">Policy Workspace</h2>
            <p class="card-copy">These settings are now stored centrally for Hibachi Catering. They are available for future reservation and invoice logic, but this rollout does not force them into the existing live calculations.</p>
          </div>
          <div class="status-row">
            @foreach(($section['status'] ?? []) as $chip)
              <span class="status-chip {{ $chip['tone'] ?? 'dark' }}">{{ $chip['label'] }}</span>
            @endforeach
          </div>
          <div class="status-row">
            <span class="pill-note">Storage live</span>
            <span class="pill-note">No booking flow override yet</span>
            <span class="pill-note">Hibachi Catering only</span>
          </div>
        </section>

        <aside class="card">
          <div>
            <h2 class="card-title">Current Snapshot</h2>
            <p class="card-copy">Operational summary of the active reservation defaults.</p>
          </div>
          <div class="summary-list">
            <div class="summary-item">
              <div class="summary-label">Guests</div>
              <div class="summary-value">{{ old('minimum_guests', $rules['minimum_guests'] ?? '10') }} - {{ old('maximum_guests', $rules['maximum_guests'] ?? '1000') }}</div>
            </div>
            <div class="summary-item">
              <div class="summary-label">Deposit</div>
              <div class="summary-value">{{ old('deposit_percentage', $rules['deposit_percentage'] ?? '35.00') }}% · ${{ old('minimum_deposit_amount', $rules['minimum_deposit_amount'] ?? '500.00') }} minimum</div>
            </div>
            <div class="summary-item">
              <div class="summary-label">Travel / Gratuity</div>
              <div class="summary-value">${{ old('travel_fee_per_mile', $rules['travel_fee_per_mile'] ?? '3.00') }}/mile · {{ old('auto_gratuity_percentage', $rules['auto_gratuity_percentage'] ?? '18.00') }}%</div>
            </div>
          </div>
        </aside>
      </div>

      <form method="post" action="{{ route('admin.settings.reservation-rules.update') }}" class="page-stack" novalidate>
        @csrf

        <div class="section-grid">
          <section class="section-card">
            <div>
              <h2 class="card-title">Booking Limits</h2>
              <p class="card-copy">Reservation intake defaults and booking protection rules.</p>
            </div>
            <div class="field-grid">
              <div class="field">
                <label for="minimum_guests">Minimum guests *</label>
                <input id="minimum_guests" name="minimum_guests" type="number" min="1" class="input" value="{{ old('minimum_guests', $rules['minimum_guests'] ?? '10') }}">
              </div>
              <div class="field">
                <label for="maximum_guests">Maximum guests *</label>
                <input id="maximum_guests" name="maximum_guests" type="number" min="1" class="input" value="{{ old('maximum_guests', $rules['maximum_guests'] ?? '1000') }}">
              </div>
              <div class="field">
                <label for="default_reservation_status">Default reservation status *</label>
                <select id="default_reservation_status" name="default_reservation_status" class="select">
                  @foreach(($statusOptions ?? []) as $status)
                    <option value="{{ $status }}" @selected(old('default_reservation_status', $rules['default_reservation_status'] ?? 'Pending') === $status)>{{ $status }}</option>
                  @endforeach
                </select>
              </div>
              <div class="field">
                <label for="reservation_cutoff_hours">Reservation cutoff hours *</label>
                <input id="reservation_cutoff_hours" name="reservation_cutoff_hours" type="number" min="0" class="input" value="{{ old('reservation_cutoff_hours', $rules['reservation_cutoff_hours'] ?? '24') }}">
              </div>
              <div class="field">
                <label for="allow_same_day_booking">Allow same-day booking</label>
                <select id="allow_same_day_booking" name="allow_same_day_booking" class="select">
                  <option value="0" @selected((string) old('allow_same_day_booking', $rules['allow_same_day_booking'] ?? '0') === '0')>No</option>
                  <option value="1" @selected((string) old('allow_same_day_booking', $rules['allow_same_day_booking'] ?? '0') === '1')>Yes</option>
                </select>
                <div class="field-help">If enabled, keep the cutoff at 8 hours or more for same-day protection.</div>
              </div>
              <div class="field">
                <label for="allow_booking_without_deposit">Allow booking without deposit</label>
                <select id="allow_booking_without_deposit" name="allow_booking_without_deposit" class="select">
                  <option value="0" @selected((string) old('allow_booking_without_deposit', $rules['allow_booking_without_deposit'] ?? '0') === '0')>No</option>
                  <option value="1" @selected((string) old('allow_booking_without_deposit', $rules['allow_booking_without_deposit'] ?? '0') === '1')>Yes</option>
                </select>
              </div>
            </div>
          </section>

          <section class="section-card">
            <div>
              <h2 class="card-title">Deposit Rules</h2>
              <p class="card-copy">Deposit policy defaults used for reservation handling and internal office messaging.</p>
            </div>
            <div class="field-grid">
              <div class="field">
                <label for="deposit_required">Deposit required</label>
                <select id="deposit_required" name="deposit_required" class="select">
                  <option value="1" @selected((string) old('deposit_required', $rules['deposit_required'] ?? '1') === '1')>Yes</option>
                  <option value="0" @selected((string) old('deposit_required', $rules['deposit_required'] ?? '1') === '0')>No</option>
                </select>
              </div>
              <div class="field">
                <label for="mark_confirmed_after_deposit">Mark reservation confirmed after deposit</label>
                <select id="mark_confirmed_after_deposit" name="mark_confirmed_after_deposit" class="select">
                  <option value="1" @selected((string) old('mark_confirmed_after_deposit', $rules['mark_confirmed_after_deposit'] ?? '1') === '1')>Yes</option>
                  <option value="0" @selected((string) old('mark_confirmed_after_deposit', $rules['mark_confirmed_after_deposit'] ?? '1') === '0')>No</option>
                </select>
              </div>
              <div class="field">
                <label for="deposit_percentage">Deposit percentage *</label>
                <input id="deposit_percentage" name="deposit_percentage" type="number" min="0" max="100" step="0.01" class="input" value="{{ old('deposit_percentage', $rules['deposit_percentage'] ?? '35.00') }}">
              </div>
              <div class="field">
                <label for="minimum_deposit_amount">Minimum deposit amount *</label>
                <input id="minimum_deposit_amount" name="minimum_deposit_amount" type="number" min="0" step="0.01" class="input" value="{{ old('minimum_deposit_amount', $rules['minimum_deposit_amount'] ?? '500.00') }}">
              </div>
              <div class="field full">
                <label for="deposit_due_message">Deposit due message</label>
                <textarea id="deposit_due_message" name="deposit_due_message" class="input" rows="4">{{ old('deposit_due_message', $rules['deposit_due_message'] ?? '') }}</textarea>
              </div>
            </div>
          </section>

          <section class="section-card">
            <div>
              <h2 class="card-title">Travel Fee Rules</h2>
              <p class="card-copy">Travel defaults stored centrally for future use by booking and invoice flows.</p>
            </div>
            <div class="field-grid">
              <div class="field">
                <label for="base_zip_code">Base ZIP code *</label>
                <input id="base_zip_code" name="base_zip_code" class="input" value="{{ old('base_zip_code', $rules['base_zip_code'] ?? '92562') }}">
              </div>
              <div class="field">
                <label for="travel_fee_per_mile">Travel fee per mile *</label>
                <input id="travel_fee_per_mile" name="travel_fee_per_mile" type="number" min="0" step="0.01" class="input" value="{{ old('travel_fee_per_mile', $rules['travel_fee_per_mile'] ?? '3.00') }}">
              </div>
              <div class="field">
                <label for="free_travel_radius_miles">Free travel radius miles *</label>
                <input id="free_travel_radius_miles" name="free_travel_radius_miles" type="number" min="0" step="1" class="input" value="{{ old('free_travel_radius_miles', $rules['free_travel_radius_miles'] ?? '0') }}">
              </div>
              <div class="field">
                <label for="long_distance_threshold_miles">Long-distance threshold miles *</label>
                <input id="long_distance_threshold_miles" name="long_distance_threshold_miles" type="number" min="0" step="1" class="input" value="{{ old('long_distance_threshold_miles', $rules['long_distance_threshold_miles'] ?? '200') }}">
              </div>
              <div class="field full">
                <label for="long_distance_policy_note">Long-distance policy note</label>
                <textarea id="long_distance_policy_note" name="long_distance_policy_note" class="input" rows="4">{{ old('long_distance_policy_note', $rules['long_distance_policy_note'] ?? '') }}</textarea>
              </div>
            </div>
          </section>

          <section class="section-card">
            <div>
              <h2 class="card-title">Gratuity Rules</h2>
              <p class="card-copy">Central gratuity defaults for large events and service policy messaging.</p>
            </div>
            <div class="field-grid">
              <div class="field">
                <label for="auto_gratuity_enabled">Auto gratuity enabled</label>
                <select id="auto_gratuity_enabled" name="auto_gratuity_enabled" class="select">
                  <option value="1" @selected((string) old('auto_gratuity_enabled', $rules['auto_gratuity_enabled'] ?? '1') === '1')>Yes</option>
                  <option value="0" @selected((string) old('auto_gratuity_enabled', $rules['auto_gratuity_enabled'] ?? '1') === '0')>No</option>
                </select>
              </div>
              <div class="field">
                <label for="gratuity_label">Gratuity label *</label>
                <input id="gratuity_label" name="gratuity_label" class="input" value="{{ old('gratuity_label', $rules['gratuity_label'] ?? 'Service Charge / Gratuity') }}">
              </div>
              <div class="field">
                <label for="auto_gratuity_percentage">Auto gratuity percentage *</label>
                <input id="auto_gratuity_percentage" name="auto_gratuity_percentage" type="number" min="0" max="100" step="0.01" class="input" value="{{ old('auto_gratuity_percentage', $rules['auto_gratuity_percentage'] ?? '18.00') }}">
              </div>
              <div class="field">
                <label for="auto_gratuity_minimum_guests">Auto gratuity minimum guests *</label>
                <input id="auto_gratuity_minimum_guests" name="auto_gratuity_minimum_guests" type="number" min="1" class="input" value="{{ old('auto_gratuity_minimum_guests', $rules['auto_gratuity_minimum_guests'] ?? '30') }}">
              </div>
            </div>
          </section>

          <section class="section-card">
            <div>
              <h2 class="card-title">Extra Time &amp; Event Handling</h2>
              <p class="card-copy">Service duration defaults and internal policy notes for operational consistency.</p>
            </div>
            <div class="field-grid">
              <div class="field">
                <label for="included_service_hours">Included service hours *</label>
                <input id="included_service_hours" name="included_service_hours" type="number" min="1" step="1" class="input" value="{{ old('included_service_hours', $rules['included_service_hours'] ?? '3') }}">
              </div>
              <div class="field">
                <label for="extra_time_billing_increment_minutes">Extra time billing increment minutes *</label>
                <input id="extra_time_billing_increment_minutes" name="extra_time_billing_increment_minutes" type="number" min="1" step="1" class="input" value="{{ old('extra_time_billing_increment_minutes', $rules['extra_time_billing_increment_minutes'] ?? '30') }}">
              </div>
              <div class="field">
                <label for="extra_time_fee">Extra time fee *</label>
                <input id="extra_time_fee" name="extra_time_fee" type="number" min="0" step="0.01" class="input" value="{{ old('extra_time_fee', $rules['extra_time_fee'] ?? '50.00') }}">
              </div>
              <div class="field">
                <label>&nbsp;</label>
                <div class="pill-note">Stored only, not forced into live billing logic yet</div>
              </div>
              <div class="field full">
                <label for="setup_time_note">Setup time note</label>
                <textarea id="setup_time_note" name="setup_time_note" class="input" rows="4">{{ old('setup_time_note', $rules['setup_time_note'] ?? '') }}</textarea>
              </div>
              <div class="field full">
                <label for="late_customer_policy_note">Late customer policy note</label>
                <textarea id="late_customer_policy_note" name="late_customer_policy_note" class="input" rows="4">{{ old('late_customer_policy_note', $rules['late_customer_policy_note'] ?? '') }}</textarea>
              </div>
            </div>
          </section>

          <section class="section-card">
            <div>
              <h2 class="card-title">Required Booking Fields</h2>
              <p class="card-copy">Reference list for required intake fields. This does not yet override existing live form validation.</p>
            </div>
            @php
              $selectedRequiredFields = old('required_booking_fields', $rules['required_booking_fields'] ?? []);
            @endphp
            <div class="check-grid">
              @foreach(($requiredFieldOptions ?? []) as $value => $label)
                <label class="check-item">
                  <input type="checkbox" name="required_booking_fields[]" value="{{ $value }}" @checked(in_array($value, $selectedRequiredFields, true))>
                  <span class="check-label">{{ $label }}</span>
                </label>
              @endforeach
            </div>
          </section>

          <section class="section-card full">
            <div>
              <h2 class="card-title">Confirmation Messages</h2>
              <p class="card-copy">Default customer-facing and internal confirmation copy for the Hibachi Catering reservation workflow.</p>
            </div>
            <div class="field-grid single">
              <div class="field full">
                <label for="reservation_received_message">Reservation received message *</label>
                <textarea id="reservation_received_message" name="reservation_received_message" class="input" rows="4">{{ old('reservation_received_message', $rules['reservation_received_message'] ?? '') }}</textarea>
              </div>
              <div class="field full">
                <label for="deposit_required_message">Deposit required message *</label>
                <textarea id="deposit_required_message" name="deposit_required_message" class="input" rows="4">{{ old('deposit_required_message', $rules['deposit_required_message'] ?? '') }}</textarea>
              </div>
              <div class="field full">
                <label for="confirmation_message_after_deposit">Confirmation message after deposit *</label>
                <textarea id="confirmation_message_after_deposit" name="confirmation_message_after_deposit" class="input" rows="4">{{ old('confirmation_message_after_deposit', $rules['confirmation_message_after_deposit'] ?? '') }}</textarea>
              </div>
              <div class="field full">
                <label for="internal_admin_note">Internal admin note</label>
                <textarea id="internal_admin_note" name="internal_admin_note" class="input" rows="4">{{ old('internal_admin_note', $rules['internal_admin_note'] ?? '') }}</textarea>
              </div>
            </div>
          </section>
        </div>

        <div class="form-actions">
          <div class="field-help">These values are centrally stored and ready for future integration. Existing reservation calculations were intentionally left untouched in this pass.</div>
          <div class="action-row">
            <a class="btn secondary" href="{{ route('admin.reservations') }}">Open Reservations</a>
            <a class="btn secondary" href="{{ $backUrl ?? route('admin.settings') }}">Cancel</a>
            <button class="btn" type="submit">Save Changes</button>
          </div>
        </div>
      </form>

      <section class="card">
        <div>
          <h2 class="card-title">Recommended Defaults</h2>
          <p class="card-copy">Reset this module to the Hibachi Catering recommended reservation policy without affecting historical reservations or invoices.</p>
        </div>
        <form method="post" action="{{ route('admin.settings.reservation-rules.reset') }}" onsubmit="return confirm('Reset Reservation Rules to the recommended Hibachi Catering defaults?');">
          @csrf
          <div class="action-row">
            <button class="btn ghost" type="submit">Reset to Recommended Defaults</button>
          </div>
        </form>
      </section>
    </div>
  </div>
</body>
</html>
