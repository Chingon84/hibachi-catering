@extends('layouts.admin')

@section('title', 'Menu & Pricing Rules')

@push('styles')
<style>
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
    .hero-grid{display:grid;grid-template-columns:minmax(0,1.6fr) minmax(320px,1fr);gap:16px}
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
    .info-box{padding:14px 16px;border:1px solid #dbeafe;border-radius:16px;background:#eff6ff;color:#1e3a8a;font-size:14px;line-height:1.6}
    .warning-box{padding:14px 16px;border:1px solid #fed7aa;border-radius:16px;background:#fff7ed;color:#9a3412;font-size:14px;line-height:1.6}
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
    .preview-table-wrap{overflow-x:auto}
    .preview-table{width:100%;border-collapse:separate;border-spacing:0;min-width:760px}
    .preview-table th,.preview-table td{padding:11px 12px;text-align:left;border-bottom:1px solid #e2e8f0}
    .preview-table th{background:#f8fafc;font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#64748b}
    .preview-status{display:inline-flex;align-items:center;padding:6px 10px;border-radius:999px;border:1px solid #fed7aa;background:#fff7ed;color:#c2410c;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
    .live-action-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px}
    @media (max-width: 980px){
      .page-head{flex-direction:column}
      .hero-grid,.section-grid,.field-grid,.check-grid{grid-template-columns:1fr}
    }
</style>
@endpush

@section('content')
  <div class="container">
    <div class="page-stack">
      <div class="page-head">
        <div>
          <div class="eyebrow">Menu Settings</div>
          <h1 class="page-title">Menu &amp; Pricing Rules</h1>
          <p class="page-copy">Manage menu availability, pricing policies, approval rules, and future bulk pricing controls used across Hibachi Catering.</p>
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
          <strong>Please review the menu policy fields below.</strong>
          <div style="margin-top:6px">{{ $errors->first() }}</div>
        </div>
      @endif

      <div class="hero-grid">
        <section class="card">
          <div>
            <h2 class="card-title">Policy Workspace</h2>
            <p class="card-copy">This module stores Hibachi Catering menu and pricing policies centrally. It does not change live menu prices, categories, or item availability in the current Menu module.</p>
          </div>
          <div class="status-row">
            @foreach(($section['status'] ?? []) as $chip)
              <span class="status-chip {{ $chip['tone'] ?? 'dark' }}">{{ $chip['label'] }}</span>
            @endforeach
          </div>
          <div class="status-row">
            <span class="pill-note">Storage live</span>
            <span class="pill-note">No auto price updates</span>
            <span class="pill-note">Menu module preserved</span>
          </div>
        </section>

        <aside class="card">
          <div>
            <h2 class="card-title">Current Snapshot</h2>
            <p class="card-copy">Quick reference for the active policy defaults.</p>
          </div>
          <div class="summary-list">
            <div class="summary-item">
              <div class="summary-label">Editing</div>
              <div class="summary-value">{{ (string) old('enable_menu_price_editing', $rules['enable_menu_price_editing'] ?? '1') === '1' ? 'Price editing enabled' : 'Price editing disabled' }}</div>
            </div>
            <div class="summary-item">
              <div class="summary-label">Categories</div>
              <div class="summary-value">{{ count(old('active_menu_categories', $rules['active_menu_categories'] ?? [])) }} active policy categories</div>
            </div>
            <div class="summary-item">
              <div class="summary-label">Approvals</div>
              <div class="summary-value">{{ ucfirst(old('price_changes_require_approval_from_role', $rules['price_changes_require_approval_from_role'] ?? 'admin')) }} review · {{ count(old('approval_status_options', $rules['approval_status_options'] ?? [])) }} statuses</div>
            </div>
          </div>
        </aside>
      </div>

      <form method="post" action="{{ route('admin.settings.menu-pricing-rules.update') }}" class="page-stack" novalidate>
        @csrf
        <input type="hidden" name="bulk_increase_type" value="{{ old('bulk_increase_type', $rules['bulk_increase_type'] ?? 'percentage') }}">
        <input type="hidden" name="bulk_apply_to_category" value="{{ old('bulk_apply_to_category', $rules['bulk_apply_to_category'] ?? 'all') }}">
        <input type="hidden" name="bulk_price_amount" value="{{ old('bulk_price_amount', $rules['bulk_price_amount'] ?? '') }}">
        <input type="hidden" name="preview_changes_before_applying" value="{{ old('preview_changes_before_applying', $rules['preview_changes_before_applying'] ?? '1') }}">
        <input type="hidden" name="require_final_confirmation" value="{{ old('require_final_confirmation', $rules['require_final_confirmation'] ?? '1') }}">

        <div class="section-grid">
          <section class="section-card">
            <div>
              <h2 class="card-title">Menu Editing Policy</h2>
              <p class="card-copy">Govern who can change pricing and core catalog structure inside the Hibachi Catering menu workflow.</p>
            </div>
            <div class="field-grid">
              @php
                $boolFields = [
                  'enable_menu_price_editing' => 'Enable menu price editing',
                  'require_manager_approval_for_price_changes' => 'Require manager approval for price changes',
                  'allow_category_editing' => 'Allow category editing',
                  'allow_item_availability_changes' => 'Allow item availability changes',
                  'allow_deleting_menu_items' => 'Allow deleting menu items',
                  'require_confirmation_before_deleting_item' => 'Require confirmation before deleting item',
                ];
              @endphp
              @foreach($boolFields as $field => $label)
                <div class="field">
                  <label for="{{ $field }}">{{ $label }}</label>
                  <select id="{{ $field }}" name="{{ $field }}" class="select">
                    <option value="1" @selected((string) old($field, $rules[$field] ?? '0') === '1')>Yes</option>
                    <option value="0" @selected((string) old($field, $rules[$field] ?? '0') === '0')>No</option>
                  </select>
                </div>
              @endforeach
            </div>
          </section>

          <section class="section-card">
            <div>
              <h2 class="card-title">Active Menu Categories</h2>
              <p class="card-copy">Policy-level category visibility using the same category source as the live Menu module.</p>
            </div>
            @php $selectedCategories = old('active_menu_categories', $rules['active_menu_categories'] ?? []); @endphp
            <div class="check-grid">
              @foreach(($categoryOptions ?? []) as $value => $label)
                <label class="check-item">
                  <input type="checkbox" name="active_menu_categories[]" value="{{ $value }}" @checked(in_array($value, $selectedCategories, true))>
                  <span class="check-label">{{ $label }}</span>
                </label>
              @endforeach
            </div>
          </section>

          <section class="section-card">
            <div>
              <h2 class="card-title">Availability Rules</h2>
              <p class="card-copy">Central rules for unavailable and seasonal item behavior.</p>
            </div>
            <div class="field-grid">
              @php
                $availabilityFields = [
                  'hide_unavailable_items_from_booking_forms' => 'Hide unavailable items from booking forms',
                  'show_unavailable_items_in_admin' => 'Show unavailable items in admin',
                  'allow_temporary_unavailable_status' => 'Allow temporary unavailable status',
                  'allow_seasonal_items' => 'Allow seasonal items',
                  'require_unavailable_reason' => 'Require unavailable reason',
                ];
              @endphp
              @foreach($availabilityFields as $field => $label)
                <div class="field">
                  <label for="{{ $field }}">{{ $label }}</label>
                  <select id="{{ $field }}" name="{{ $field }}" class="select">
                    <option value="1" @selected((string) old($field, $rules[$field] ?? '0') === '1')>Yes</option>
                    <option value="0" @selected((string) old($field, $rules[$field] ?? '0') === '0')>No</option>
                  </select>
                </div>
              @endforeach
            </div>
          </section>

          <section class="section-card">
            <div>
              <h2 class="card-title">Pricing Change Rules</h2>
              <p class="card-copy">Approval and control defaults for future pricing governance logic.</p>
            </div>
            <div class="field-grid">
              @php
                $pricingFields = [
                  'require_reason_for_price_changes' => 'Require reason for price changes',
                  'store_price_change_history' => 'Store price change history',
                  'notify_admin_when_price_changes' => 'Notify admin when price changes',
                  'lock_prices_after_invoice_is_created' => 'Lock prices after invoice is created',
                  'lock_prices_after_reservation_is_confirmed' => 'Lock prices after reservation is confirmed',
                  'allow_custom_price_override' => 'Allow custom price override',
                  'require_manager_approval_for_custom_price_override' => 'Require manager approval for custom price override',
                ];
              @endphp
              @foreach($pricingFields as $field => $label)
                <div class="field">
                  <label for="{{ $field }}">{{ $label }}</label>
                  <select id="{{ $field }}" name="{{ $field }}" class="select">
                    <option value="1" @selected((string) old($field, $rules[$field] ?? '0') === '1')>Yes</option>
                    <option value="0" @selected((string) old($field, $rules[$field] ?? '0') === '0')>No</option>
                  </select>
                </div>
              @endforeach
            </div>
          </section>

          <section class="section-card">
            <div>
              <h2 class="card-title">Catering Menu Rules</h2>
              <p class="card-copy">Display and pricing policy toggles for the Hibachi Catering catalog experience.</p>
            </div>
            <div class="field-grid">
              @php
                $cateringFields = [
                  'enable_catering_menu' => 'Enable catering menu',
                  'allow_per_person_pricing' => 'Allow per-person pricing',
                  'allow_add_on_pricing' => 'Allow add-on pricing',
                  'allow_package_pricing' => 'Allow package pricing',
                  'show_menu_item_descriptions' => 'Show menu item descriptions',
                  'show_menu_item_images' => 'Show menu item images',
                ];
              @endphp
              @foreach($cateringFields as $field => $label)
                <div class="field">
                  <label for="{{ $field }}">{{ $label }}</label>
                  <select id="{{ $field }}" name="{{ $field }}" class="select">
                    <option value="1" @selected((string) old($field, $rules[$field] ?? '0') === '1')>Yes</option>
                    <option value="0" @selected((string) old($field, $rules[$field] ?? '0') === '0')>No</option>
                  </select>
                </div>
              @endforeach
            </div>
          </section>

          <section class="section-card">
            <div>
              <h2 class="card-title">Approval Workflow</h2>
              <p class="card-copy">Policy-level workflow defaults for pricing, deletion, and new item review.</p>
            </div>
            <div class="field-grid">
              <div class="field">
                <label for="price_changes_require_approval_from_role">Price changes require approval from role</label>
                <select id="price_changes_require_approval_from_role" name="price_changes_require_approval_from_role" class="select">
                  @foreach(($approvalRoleOptions ?? []) as $value => $label)
                    <option value="{{ $value }}" @selected(old('price_changes_require_approval_from_role', $rules['price_changes_require_approval_from_role'] ?? 'admin') === $value)>{{ $label }}</option>
                  @endforeach
                </select>
              </div>
              <div class="field">
                <label for="menu_item_deletion_requires_approval_from_role">Menu item deletion requires approval from role</label>
                <select id="menu_item_deletion_requires_approval_from_role" name="menu_item_deletion_requires_approval_from_role" class="select">
                  @foreach(($approvalRoleOptions ?? []) as $value => $label)
                    <option value="{{ $value }}" @selected(old('menu_item_deletion_requires_approval_from_role', $rules['menu_item_deletion_requires_approval_from_role'] ?? 'admin') === $value)>{{ $label }}</option>
                  @endforeach
                </select>
              </div>
              <div class="field">
                <label for="new_menu_item_requires_approval">New menu item requires approval</label>
                <select id="new_menu_item_requires_approval" name="new_menu_item_requires_approval" class="select">
                  <option value="1" @selected((string) old('new_menu_item_requires_approval', $rules['new_menu_item_requires_approval'] ?? '1') === '1')>Yes</option>
                  <option value="0" @selected((string) old('new_menu_item_requires_approval', $rules['new_menu_item_requires_approval'] ?? '1') === '0')>No</option>
                </select>
              </div>
              <div class="field full">
                <label>Approval status options</label>
                @php $selectedStatuses = old('approval_status_options', $rules['approval_status_options'] ?? []); @endphp
                <div class="check-grid">
                  @foreach(($approvalStatusOptions ?? []) as $value => $label)
                    <label class="check-item">
                      <input type="checkbox" name="approval_status_options[]" value="{{ $value }}" @checked(in_array($value, $selectedStatuses, true))>
                      <span class="check-label">{{ $label }}</span>
                    </label>
                  @endforeach
                </div>
              </div>
              <div class="field full">
                <label for="approval_note_field">Approval note field</label>
                <textarea id="approval_note_field" name="approval_note_field" class="input" rows="4">{{ old('approval_note_field', $rules['approval_note_field'] ?? '') }}</textarea>
              </div>
            </div>
          </section>
        </div>

        <div class="form-actions">
          <div class="field-help">This regular Save only stores policy settings. No existing menu item, category, price, reservation, or invoice data is modified here.</div>
          <div class="action-row">
            <a class="btn secondary" href="{{ route('admin.menu') }}">Open Menu</a>
            <a class="btn secondary" href="{{ $backUrl ?? route('admin.settings') }}">Cancel</a>
            <button class="btn" type="submit">Save Changes</button>
          </div>
        </div>
      </form>

      <section class="card">
        <div class="live-action-head">
          <div>
            <h2 class="card-title">Bulk Price Update</h2>
            <p class="card-copy">This is the only bulk pricing tool on this page. Preview and confirmation are required before any live menu price change.</p>
          </div>
          <span class="pill-note">Admin-only workflow</span>
        </div>
        <div class="info-box">Bulk pricing changes should be previewed and approved before applying to live menu items.</div>
        <div class="field-help">Audit log for bulk price updates will be added in a future rollout.</div>

        <form method="post" action="{{ route('admin.settings.menu-pricing-rules.preview') }}" class="page-stack" novalidate>
          @csrf
          <input type="hidden" name="preview_changes_before_applying" value="1">
          <input type="hidden" name="require_final_confirmation" value="1">
          <div class="field-grid">
            <div class="field">
              <label for="live_bulk_increase_type">Bulk increase type</label>
              <select id="live_bulk_increase_type" name="bulk_increase_type" class="select">
                <option value="fixed" @selected(old('bulk_increase_type', $rules['bulk_increase_type'] ?? 'percentage') === 'fixed')>Fixed amount</option>
                <option value="percentage" @selected(old('bulk_increase_type', $rules['bulk_increase_type'] ?? 'percentage') === 'percentage')>Percentage</option>
              </select>
            </div>
            <div class="field">
              <label for="live_bulk_apply_to_category">Apply to category</label>
              <select id="live_bulk_apply_to_category" name="bulk_apply_to_category" class="select">
                <option value="all" @selected(old('bulk_apply_to_category', $rules['bulk_apply_to_category'] ?? 'all') === 'all')>All active categories</option>
                @foreach(($categoryOptions ?? []) as $value => $label)
                  <option value="{{ $value }}" @selected(old('bulk_apply_to_category', $rules['bulk_apply_to_category'] ?? 'all') === $value)>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="field">
              <label for="live_bulk_price_amount">Amount</label>
              <input id="live_bulk_price_amount" name="bulk_price_amount" type="number" min="0.01" step="0.01" class="input" value="{{ old('bulk_price_amount', '') }}" required>
              <div class="field-help">Required for preview. Fixed amount adds directly; percentage increases current prices by that percent.</div>
            </div>
            <div class="field">
              <label>&nbsp;</label>
              <div class="pill-note">Preview is always required</div>
            </div>
          </div>
          <div class="action-row">
            <button class="btn" type="submit">Preview Price Changes</button>
          </div>
        </form>

        @if(!empty($bulkPreview['rows']))
          <div class="page-stack">
            <div>
              <h3 class="card-title" style="font-size:16px">Preview Changes</h3>
              <p class="card-copy">Review affected live menu items before applying changes.</p>
            </div>
            <div class="preview-table-wrap">
              <table class="preview-table">
                <thead>
                  <tr>
                    <th>Category</th>
                    <th>Item key</th>
                    <th>Item name</th>
                    <th>Current price</th>
                    <th>New price</th>
                    <th>Difference</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($bulkPreview['rows'] as $row)
                    <tr>
                      <td>{{ $row['category'] }}</td>
                      <td>{{ $row['key'] }}</td>
                      <td>{{ $row['name'] }}</td>
                      <td>${{ $row['current_price'] }}</td>
                      <td>${{ $row['new_price'] }}</td>
                      <td>{{ $row['difference'] }}</td>
                      <td><span class="preview-status">{{ $row['status'] }}</span></td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            <form method="post" action="{{ route('admin.settings.menu-pricing-rules.apply') }}" class="page-stack" novalidate>
              @csrf
              <input type="hidden" name="bulk_increase_type" value="{{ $bulkPreview['type'] }}">
              <input type="hidden" name="bulk_apply_to_category" value="{{ $bulkPreview['category'] }}">
              <input type="hidden" name="bulk_price_amount" value="{{ $bulkPreview['amount'] }}">
              <input type="hidden" name="preview_changes_before_applying" value="1">
              <input type="hidden" name="require_final_confirmation" value="1">
              <label class="check-item" style="max-width:440px">
                <input type="checkbox" name="bulk_apply_confirmation" value="1" required>
                <span class="check-label">I understand this will update live menu item prices.</span>
              </label>
              <div class="action-row">
                <button class="btn" type="submit">Apply Price Updates</button>
              </div>
            </form>
          </div>
        @endif
      </section>

      <section class="card">
        <div>
          <h2 class="card-title">Recommended Defaults</h2>
          <p class="card-copy">Reset this module to the Hibachi Catering recommended policy baseline without touching live menu data.</p>
        </div>
        <form method="post" action="{{ route('admin.settings.menu-pricing-rules.reset') }}" onsubmit="return confirm('Reset Menu & Pricing Rules to the recommended defaults?');">
          @csrf
          <div class="action-row">
            <button class="btn ghost" type="submit">Reset to Recommended Defaults</button>
          </div>
        </form>
      </section>
    </div>
  </div>
@endsection
