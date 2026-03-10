<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin – Van Inventory</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    .page{display:grid;gap:14px}
    .subnav{display:flex;flex-wrap:wrap;gap:8px}
    .subnav a{display:inline-flex;align-items:center;padding:9px 12px;border-radius:999px;border:1px solid var(--border);background:#fff;color:#334155;text-decoration:none;font-size:12px;font-weight:700}
    .subnav a.active{background:#0f172a;border-color:#0f172a;color:#fff}
    .toolbar-grid{display:grid;grid-template-columns:2fr 1fr auto auto;gap:10px;align-items:end}
    .cards{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
    .van-card{background:linear-gradient(180deg,#ffffff 0%,#fbfcfe 100%);border:1px solid #e6ebf2;border-radius:18px;padding:14px;box-shadow:0 14px 32px rgba(15,23,42,.06);display:flex;flex-direction:column;gap:9px;min-height:288px}
    .van-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}
    .van-head-main{display:grid;gap:4px;min-width:0}
    .van-name{margin:0;font-size:24px;line-height:1;font-weight:800;letter-spacing:-.02em;color:#0f172a}
    .van-meta{margin:0;color:#7c8798;font-size:12px;line-height:1.35}
    .van-summary{display:flex;flex-wrap:wrap;gap:6px;color:#526072;font-size:12px;font-weight:600;line-height:1.4}
    .summary-dot{color:#c0c9d6}
    .status-pill{display:inline-flex;align-items:center;padding:3px 9px;border-radius:999px;font-size:11px;font-weight:800;letter-spacing:.01em;border:1px solid transparent}
    .status-pill.active{background:#ecfdf5;color:#166534}
    .status-pill.maintenance{background:#fff7ed;color:#9a3412}
    .status-pill.inactive{background:#f1f5f9;color:#475569}
    .status-pill.clean{background:#ecfdf5;color:#166534;border-color:#bbf7d0}
    .status-pill.dirty{background:#fef2f2;color:#b91c1c;border-color:#fecaca}
    .status-pill.neutral{background:#fff7ed;color:#9a3412;border-color:#fed7aa}
    .info-list{display:grid;gap:0;padding-top:0;flex:1}
    .info-row{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:10px;align-items:center;padding:5px 0;line-height:1.25}
    .info-row + .info-row{border-top:1px solid #edf1f6}
    .info-label{font-size:12px;font-weight:700;color:#7b8797;text-transform:none;line-height:1.2}
    .info-label.priority{font-weight:800;color:#526072}
    .info-value{font-size:13px;font-weight:800;color:#0f172a;text-align:right;letter-spacing:-.01em;line-height:1.2}
    .card-footer{display:flex;align-items:flex-end;justify-content:space-between;gap:12px;margin-top:auto;padding-top:10px;border-top:1px solid #edf1f6}
    .footer-meta{display:grid;gap:4px;min-width:0}
    .footer-line{font-size:11.5px;color:#7c8798;line-height:1.4}
    .footer-line strong{color:#526072;font-weight:700}
    .edit-loadout-btn{padding:8px 11px;font-size:12px;font-weight:700;border-radius:9px;line-height:1}
    .modal-backdrop{position:fixed;inset:0;background:rgba(15,23,42,.45);display:none;align-items:flex-start;justify-content:center;padding:28px 16px;z-index:40;overflow:auto}
    .modal-backdrop.open{display:flex}
    .modal{width:100%;max-width:1100px;margin:auto;background:#fff;border:1px solid var(--border);border-radius:22px;box-shadow:0 30px 80px rgba(15,23,42,.22);padding:20px}
    .modal-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:18px}
    .modal-title{margin:0;font-size:28px;line-height:1.08}
    .modal-copy{margin:8px 0 0;color:var(--muted);font-size:14px}
    .modal-form{display:grid;gap:14px}
    .section{display:grid;gap:10px}
    .section + .section{margin-top:14px}
    .section-title{margin:0;font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#64748b}
    .meta-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
    .timing-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
    .field{display:flex;flex-direction:column;gap:6px;min-width:0}
    .field.span-2{grid-column:1 / -1}
    .field.compact{max-width:130px}
    .field.compact-wide{max-width:180px}
    .field.notes-field textarea{width:100%}
    .status-select.clean{background:#ecfdf5;color:#166534;border-color:#bbf7d0}
    .status-select.neutral{background:#fff7ed;color:#9a3412;border-color:#fed7aa}
    .status-select.dirty{background:#fef2f2;color:#b91c1c;border-color:#fecaca}
    .multi-select{position:relative}
    .multi-select-trigger{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;min-height:46px;padding:10px 12px;border:1px solid var(--border);border-radius:10px;background:#fff;cursor:pointer}
    .multi-select-trigger:focus-visible{outline:2px solid var(--brand);outline-offset:2px}
    .multi-select-value{display:flex;flex-wrap:wrap;gap:6px;align-items:center}
    .multi-select-placeholder{color:#94a3b8;font-size:13px}
    .multi-chip{display:inline-flex;align-items:center;gap:6px;background:#fff;border:1px solid #d8deea;border-radius:999px;padding:5px 9px;font-size:12px;font-weight:700;color:#334155}
    .multi-select-caret{font-size:12px;color:#64748b;padding-top:4px}
    .multi-select-menu{position:absolute;top:calc(100% + 6px);left:0;right:0;display:none;max-height:220px;overflow:auto;padding:10px;border:1px solid #d8deea;border-radius:14px;background:#fff;box-shadow:0 20px 40px rgba(15,23,42,.12);z-index:5}
    .multi-select.open .multi-select-menu{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px}
    .multi-option{display:flex;align-items:center;gap:8px;padding:8px 10px;border:1px solid #e5e7eb;border-radius:10px;background:#fff;font-size:13px}
    .count-grid{display:grid;grid-template-columns:repeat(3,minmax(0,120px));gap:12px 18px;align-items:end;justify-content:flex-start}
    .count-grid .field{max-width:120px}
    .count-grid .input{width:96px;padding:9px 10px}
    .field.compact .input,.field.compact .select,.field.compact-wide .input,.field.compact-wide .select{width:100%}
    .field textarea.input{min-height:110px}
    .summary-panel{display:grid;gap:8px;padding:12px 14px;border:1px solid #e5e7eb;border-radius:14px;background:#fafcff}
    .summary-title{margin:0;font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#64748b}
    .summary-text{font-size:13px;color:#334155;line-height:1.5}
    .sticky-actions{position:sticky;bottom:0;display:flex;align-items:center;justify-content:space-between;gap:12px;padding-top:12px;margin-top:6px;background:linear-gradient(180deg,rgba(255,255,255,0) 0%,#fff 28%)}
    .sticky-actions .actions{display:flex;gap:10px;flex-wrap:wrap}
    .empty-state{padding:22px;border:1px dashed #d6dce8;border-radius:16px;background:#fafcff;color:#64748b;text-align:center}
    @media (max-width: 1180px){.cards{grid-template-columns:repeat(2,minmax(0,1fr))}.toolbar-grid,.meta-grid,.timing-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.multi-select.open .multi-select-menu{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media (max-width: 760px){.cards,.toolbar-grid,.meta-grid,.timing-grid{grid-template-columns:1fr}.count-grid{grid-template-columns:repeat(2,minmax(0,120px))}.multi-select.open .multi-select-menu{grid-template-columns:1fr}.modal{padding:18px}.sticky-actions{position:static;padding-top:0;background:none}.card-footer{align-items:flex-start;flex-direction:column}}
  </style>
</head>
<body>
  <div class="container">
    <div class="page">
      <div class="header">
        <h1 class="title" style="margin-right:auto">Van Inventory</h1>
        @if(auth()->user()?->hasPermission('inventory.manage'))
          <a class="btn secondary" href="{{ route('admin.inventory.vans.create') }}">Add Van</a>
          <button class="btn" type="button" id="openLoadoutModal">Update Van Loadout</button>
        @endif
      </div>

      @if (session('ok'))
        <div class="card"><div class="card-body" style="color:#166534;font-weight:700">{{ session('ok') }}</div></div>
      @endif

      @include('admin.inventory._subnav')

      <div class="card"><div class="card-body">
        <form method="get" action="{{ route('admin.inventory.vans.index') }}" class="toolbar-grid">
          <div>
            <label class="label" for="q">Search Vans</label>
            <input class="input" id="q" name="q" value="{{ $filters['q'] }}" placeholder="Number, code, plate">
          </div>
          <div>
            <label class="label" for="status">Fleet Status</label>
            <select class="select" id="status" name="status">
              <option value="">All statuses</option>
              @foreach ($statuses as $status)
                <option value="{{ $status }}" {{ $filters['status'] === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
              @endforeach
            </select>
          </div>
          <button class="btn secondary" type="submit">Apply</button>
          <a class="btn secondary" href="{{ route('admin.inventory.vans.index') }}">Reset</a>
        </form>
      </div></div>

      @if ($vans->isEmpty())
        <div class="empty-state">No vans match the current filters.</div>
      @else
        <div class="cards">
          @foreach ($vans as $van)
            @php
              $loadout = $van->currentLoadout;
              $grills = $loadout?->grills ?? [];
              $summaryBits = [];
              if (count($grills) > 0) { $summaryBits[] = count($grills) . ' grill' . (count($grills) === 1 ? '' : 's'); }
              if ((int) ($loadout->tables_count ?? 0) > 0) { $summaryBits[] = (int) $loadout->tables_count . ' table' . ((int) $loadout->tables_count === 1 ? '' : 's'); }
              if ((int) ($loadout->chairs_count ?? 0) > 0) { $summaryBits[] = (int) $loadout->chairs_count . ' chair' . ((int) $loadout->chairs_count === 1 ? '' : 's'); }
              $equipmentRows = [
                  ['label' => 'Grills', 'value' => count($grills) > 0 ? implode(', ', $grills) : null],
                  ['label' => 'Tables', 'value' => (int) ($loadout->tables_count ?? 0) > 0 ? (int) $loadout->tables_count : null],
                  ['label' => 'Chairs', 'value' => (int) ($loadout->chairs_count ?? 0) > 0 ? (int) $loadout->chairs_count : null],
                  ['label' => 'Propane Tanks', 'value' => (int) ($loadout->propane_tanks_count ?? 0) > 0 ? (int) $loadout->propane_tanks_count : null],
                  ['label' => 'Dolly', 'value' => (int) ($loadout->dolly_count ?? 0) > 0 ? (int) $loadout->dolly_count : null],
                  ['label' => 'Straps', 'value' => (int) ($loadout->straps_count ?? 0) > 0 ? (int) $loadout->straps_count : null],
                  ['label' => 'Floor Mats', 'value' => (int) ($loadout->floor_mats_count ?? 0) > 0 ? (int) $loadout->floor_mats_count : null],
                  ['label' => 'Trash Cans', 'value' => (int) ($loadout->trash_cans_count ?? 0) > 0 ? (int) $loadout->trash_cans_count : null],
                  ['label' => 'Heaters', 'value' => (int) ($loadout->heaters_count ?? 0) > 0 ? (int) $loadout->heaters_count : null],
                  ['label' => 'Buffet Warmers', 'value' => (int) ($loadout->buffet_warmers_count ?? 0) > 0 ? (int) $loadout->buffet_warmers_count : null],
              ];
              $equipmentRows = array_values(array_filter($equipmentRows, fn ($row) => $row['value'] !== null));
            @endphp
            <article class="van-card"
              data-van-id="{{ $van->id }}"
              data-van-number="{{ $van->van_number }}"
              data-van-status="{{ $loadout?->van_status ?? 'neutral' }}"
              data-loaded-by="{{ $loadout?->loaded_by_user_id }}"
              data-checked-by="{{ $loadout?->checked_by_user_id }}"
              data-checked-at="{{ $loadout?->checked_at?->format('Y-m-d\\TH:i') }}"
              data-event-date="{{ $loadout?->event_date?->toDateString() }}"
              data-reservation-id="{{ $loadout?->reservation_id }}"
              data-grills='@json($grills)'
              data-tables="{{ (int) ($loadout->tables_count ?? 0) }}"
              data-chairs="{{ (int) ($loadout->chairs_count ?? 0) }}"
              data-propane="{{ (int) ($loadout->propane_tanks_count ?? 0) }}"
              data-dolly="{{ (int) ($loadout->dolly_count ?? 0) }}"
              data-straps="{{ (int) ($loadout->straps_count ?? 0) }}"
              data-floor-mats="{{ (int) ($loadout->floor_mats_count ?? 0) }}"
              data-trash-cans="{{ (int) ($loadout->trash_cans_count ?? 0) }}"
              data-heaters="{{ (int) ($loadout->heaters_count ?? 0) }}"
              data-buffet-warmers="{{ (int) ($loadout->buffet_warmers_count ?? 0) }}"
              data-notes="{{ $loadout?->notes }}"
            >
              <div class="van-head">
                <div class="van-head-main">
                  <h2 class="van-name">Van #{{ $van->van_number ?: preg_replace('/\D+/', '', $van->displayName()) }}</h2>
                  @if($van->code || $van->license_plate)
                    <div class="van-meta">
                      @if($van->code)
                        <div>Fleet ID: {{ $van->code }}</div>
                      @endif
                      @if($van->license_plate)
                        <div>Plate: {{ $van->license_plate }}</div>
                      @endif
                    </div>
                  @else
                    <p class="van-meta">No fleet ID or plate recorded</p>
                  @endif
                  @if (count($summaryBits) > 0)
                    <div class="van-summary">
                      @foreach ($summaryBits as $bit)
                        <span>{{ $bit }}</span>
                        @if (!$loop->last)
                          <span class="summary-dot">•</span>
                        @endif
                      @endforeach
                    </div>
                  @endif
                </div>
                <span class="status-pill {{ $loadout?->van_status ?? 'neutral' }}">{{ ucfirst($loadout?->van_status ?? 'neutral') }}</span>
              </div>

              @if (count($equipmentRows) > 0)
                <div class="info-list">
                  @foreach ($equipmentRows as $row)
                    <div class="info-row">
                      <div class="info-label {{ in_array($row['label'], ['Grills', 'Tables', 'Chairs', 'Propane Tanks'], true) ? 'priority' : '' }}">{{ $row['label'] }}</div>
                      <div class="info-value">{{ $row['value'] }}</div>
                    </div>
                  @endforeach
                </div>
              @endif

              <div class="card-footer">
                <div class="footer-meta">
                  @if($loadout?->checked_at)
                    <div class="footer-line"><strong>Last checked:</strong> {{ $loadout->checked_at->format('m/d/Y g:i A') }}</div>
                  @endif
                  @if($loadout?->checkedBy)
                    <div class="footer-line"><strong>Checked by:</strong> {{ $loadout->checkedBy->name }}</div>
                  @endif
                  @if($loadout?->event_date || $loadout?->reservation)
                    <div class="footer-line">
                      <strong>Event:</strong>
                      @if($loadout?->event_date)
                        {{ $loadout->event_date->format('m/d/Y') }}
                      @endif
                      @if($loadout?->reservation)
                        {{ $loadout?->event_date ? ' • ' : '' }}{{ $loadout->reservation->customer_name }}@if($loadout->reservation->code) ({{ $loadout->reservation->code }})@endif
                      @endif
                    </div>
                  @endif
                </div>
                @if(auth()->user()?->hasPermission('inventory.manage'))
                  <button class="btn secondary edit-loadout-btn" type="button">Edit Loadout</button>
                @endif
              </div>
            </article>
          @endforeach
        </div>
      @endif
    </div>
  </div>

  @if(auth()->user()?->hasPermission('inventory.manage'))
    <div class="modal-backdrop {{ $errors->any() ? 'open' : '' }}" id="loadoutModal">
      <div class="modal" role="dialog" aria-modal="true" aria-labelledby="loadoutModalTitle">
        <div class="modal-head">
          <div>
            <h2 class="modal-title" id="loadoutModalTitle">Update Van Loadout</h2>
            <p class="modal-copy">Use one form to create or update the operational loadout for any van from the main dashboard.</p>
          </div>
          <button class="btn secondary" type="button" id="closeLoadoutModal">Close</button>
        </div>

        <form method="post" action="{{ route('admin.inventory.vans.loadout.store') }}" class="modal-form">
          @csrf
          <div class="section">
            <h3 class="section-title">Van Info</h3>
            <div class="meta-grid">
            <div class="field compact">
              <label class="label" for="loadout_van_number">Van Number</label>
              <select class="select" id="loadout_van_number" name="van_number" required>
                <option value="">Select van number</option>
                @foreach ($vanNumbers as $vanNumber)
                  <option value="{{ $vanNumber }}" {{ (string) old('van_number') === (string) $vanNumber ? 'selected' : '' }}>Van {{ $vanNumber }}</option>
                @endforeach
              </select>
            </div>
            <div class="field compact-wide">
              <label class="label" for="loadout_van_status">Van Status</label>
              <select class="select status-select {{ old('van_status', 'neutral') }}" id="loadout_van_status" name="van_status" required>
                @foreach (\App\Models\VanLoadout::VAN_STATUSES as $status)
                  <option value="{{ $status }}" {{ old('van_status', 'neutral') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                @endforeach
              </select>
            </div>
            <div class="field compact-wide">
              <label class="label" for="loadout_loaded_by_user_id">Loaded By</label>
              <select class="select" id="loadout_loaded_by_user_id" name="loaded_by_user_id">
                <option value="">Select team member</option>
                @foreach ($teamMembers as $member)
                  <option value="{{ $member->id }}" {{ (string) old('loaded_by_user_id') === (string) $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="field compact-wide">
              <label class="label" for="loadout_checked_by_user_id">Checked By</label>
              <select class="select" id="loadout_checked_by_user_id" name="checked_by_user_id">
                <option value="">Select team member</option>
                @foreach ($teamMembers as $member)
                  <option value="{{ $member->id }}" {{ (string) old('checked_by_user_id') === (string) $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                @endforeach
              </select>
            </div>
            </div>
          </div>

          <div class="section">
            <h3 class="section-title">Event / Timing</h3>
            <div class="timing-grid">
            <div class="field compact-wide">
              <label class="label" for="loadout_checked_at">Checked At</label>
              <input class="input" type="datetime-local" id="loadout_checked_at" name="checked_at" value="{{ old('checked_at') }}">
            </div>
            <div class="field compact-wide">
              <label class="label" for="loadout_event_date">Event Date</label>
              <input class="input" type="date" id="loadout_event_date" name="event_date" value="{{ old('event_date') }}">
            </div>
            <div class="field">
              <label class="label" for="loadout_reservation_id">Reservation Reference</label>
              <select class="select" id="loadout_reservation_id" name="reservation_id">
                <option value="">No reservation linked</option>
                @foreach ($reservationOptions as $reservation)
                  <option value="{{ $reservation->id }}" {{ (string) old('reservation_id') === (string) $reservation->id ? 'selected' : '' }}>
                    {{ $reservation->customer_name }} @if($reservation->date) • {{ $reservation->date->format('m/d/Y') }} @endif @if($reservation->code) • {{ $reservation->code }} @endif
                  </option>
                @endforeach
              </select>
            </div>
            </div>
          </div>

          <div class="section">
            <h3 class="section-title">Grills</h3>
            <div class="field span-2">
              <label class="label" for="grillMultiTrigger">Grill Selector</label>
              @php $selectedGrills = collect(old('grills', []))->map(fn ($grill) => (int) $grill)->sort()->values()->all(); @endphp
              <div class="multi-select" id="grillMultiSelect">
                <button class="multi-select-trigger" type="button" id="grillMultiTrigger" aria-expanded="false" aria-controls="grillMultiMenu">
                  <span class="multi-select-value" id="grillSelectedValue">
                    @if (count($selectedGrills) > 0)
                      @foreach ($selectedGrills as $grill)
                        <span class="multi-chip">Grill #{{ $grill }}</span>
                      @endforeach
                    @else
                      <span class="multi-select-placeholder">Select one or more grills</span>
                    @endif
                  </span>
                  <span class="multi-select-caret">Select</span>
                </button>
                <div class="multi-select-menu" id="grillMultiMenu">
                  @for ($grill = 1; $grill <= 30; $grill++)
                    <label class="multi-option">
                      <input type="checkbox" name="grills[]" value="{{ $grill }}" {{ in_array($grill, $selectedGrills, true) ? 'checked' : '' }}>
                      <span>Grill #{{ $grill }}</span>
                    </label>
                  @endfor
                </div>
              </div>
            </div>
          </div>

          <div class="section">
            <h3 class="section-title">Equipment Counts</h3>
            <div class="count-grid">
            @foreach ([
              'tables_count' => 'Tables',
              'chairs_count' => 'Chairs',
              'propane_tanks_count' => 'Propane Tanks',
              'dolly_count' => 'Dolly',
              'straps_count' => 'Straps',
              'floor_mats_count' => 'Floor Mats',
              'trash_cans_count' => 'Trash Cans',
              'heaters_count' => 'Heaters',
              'buffet_warmers_count' => 'Buffet Warmers',
            ] as $field => $label)
              <div class="field">
                <label class="label" for="loadout_{{ $field }}">{{ $label }}</label>
                <input class="input" type="number" min="0" id="loadout_{{ $field }}" name="{{ $field }}" value="{{ old($field, 0) }}" required>
              </div>
            @endforeach
          </div>
          </div>

          <div class="section">
          <div class="field notes-field">
            <label class="label" for="loadout_notes">Notes</label>
            <textarea class="input" id="loadout_notes" name="notes" rows="5" style="min-height:140px">{{ old('notes') }}</textarea>
          </div>
          </div>

          <div class="summary-panel" id="loadoutSummary">
            <h3 class="summary-title">Loadout Summary</h3>
            <div class="summary-text" id="loadoutSummaryText">Select a van and equipment details to preview the loadout.</div>
          </div>

          <div class="sticky-actions">
            <div class="summary-text">Review the loadout summary, then save.</div>
            <div class="actions">
              <button class="btn secondary" type="button" id="cancelLoadoutModal">Cancel</button>
              <button class="btn" type="submit">Save Loadout</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  @endif

  @if(auth()->user()?->hasPermission('inventory.manage'))
    <script>
      (function () {
        const modal = document.getElementById('loadoutModal');
        if (!modal) return;

        const openBtn = document.getElementById('openLoadoutModal');
        const closeBtn = document.getElementById('closeLoadoutModal');
        const cancelBtn = document.getElementById('cancelLoadoutModal');
        const grillMulti = document.getElementById('grillMultiSelect');
        const grillTrigger = document.getElementById('grillMultiTrigger');
        const grillSelectedValue = document.getElementById('grillSelectedValue');
        const summaryText = document.getElementById('loadoutSummaryText');
        const form = modal.querySelector('form');
        const fields = {
          vanNumber: document.getElementById('loadout_van_number'),
          vanStatus: document.getElementById('loadout_van_status'),
          loadedBy: document.getElementById('loadout_loaded_by_user_id'),
          checkedBy: document.getElementById('loadout_checked_by_user_id'),
          checkedAt: document.getElementById('loadout_checked_at'),
          eventDate: document.getElementById('loadout_event_date'),
          reservationId: document.getElementById('loadout_reservation_id'),
          tables: document.getElementById('loadout_tables_count'),
          chairs: document.getElementById('loadout_chairs_count'),
          propane: document.getElementById('loadout_propane_tanks_count'),
          dolly: document.getElementById('loadout_dolly_count'),
          straps: document.getElementById('loadout_straps_count'),
          floorMats: document.getElementById('loadout_floor_mats_count'),
          trashCans: document.getElementById('loadout_trash_cans_count'),
          heaters: document.getElementById('loadout_heaters_count'),
          buffetWarmers: document.getElementById('loadout_buffet_warmers_count'),
          notes: document.getElementById('loadout_notes'),
        };

        const equipmentFields = [
          ['Tables', fields.tables],
          ['Chairs', fields.chairs],
          ['Propane', fields.propane],
          ['Dolly', fields.dolly],
          ['Straps', fields.straps],
          ['Floor Mats', fields.floorMats],
          ['Trash Cans', fields.trashCans],
          ['Heaters', fields.heaters],
          ['Buffet Warmers', fields.buffetWarmers],
        ];

        const setOpen = (open) => {
          modal.classList.toggle('open', open);
          document.body.style.overflow = open ? 'hidden' : '';
        };

        const grillCheckboxes = () => Array.from(modal.querySelectorAll('input[name="grills[]"]'));

        const renderGrillSelection = () => {
          const selected = grillCheckboxes()
            .filter((box) => box.checked)
            .map((box) => Number(box.value))
            .sort((a, b) => a - b);

          if (selected.length === 0) {
            grillSelectedValue.innerHTML = '<span class="multi-select-placeholder">Select one or more grills</span>';
            return;
          }

          grillSelectedValue.innerHTML = selected.map((grill) => `<span class="multi-chip">Grill #${grill}</span>`).join('');
        };

        const syncStatusStyle = () => {
          fields.vanStatus.classList.remove('clean', 'neutral', 'dirty');
          if (fields.vanStatus.value) {
            fields.vanStatus.classList.add(fields.vanStatus.value);
          }
        };

        const renderSummary = () => {
          const vanLabel = fields.vanNumber.value ? `Van ${fields.vanNumber.value}` : 'No van selected';
          const selectedGrills = grillCheckboxes()
            .filter((box) => box.checked)
            .map((box) => Number(box.value))
            .sort((a, b) => a - b);
          const lines = [`${vanLabel} • ${fields.vanStatus.value ? fields.vanStatus.value.charAt(0).toUpperCase() + fields.vanStatus.value.slice(1) : 'No status'}`];

          if (selectedGrills.length > 0) {
            lines.push(`Grills: ${selectedGrills.join(', ')}`);
          }

          equipmentFields.forEach(([label, input]) => {
            const value = Number(input.value || 0);
            if (value > 0) {
              lines.push(`${label}: ${value}`);
            }
          });

          summaryText.textContent = lines.join(' • ');
        };

        const setGrillOpen = (open) => {
          grillMulti?.classList.toggle('open', open);
          grillTrigger?.setAttribute('aria-expanded', open ? 'true' : 'false');
        };

        const resetCheckboxes = () => {
          grillCheckboxes().forEach((box) => {
            box.checked = false;
          });
          renderGrillSelection();
          renderSummary();
        };

        const fillForm = (card) => {
          fields.vanNumber.value = card.dataset.vanNumber || '';
          fields.vanStatus.value = card.dataset.vanStatus || 'neutral';
          fields.loadedBy.value = card.dataset.loadedBy || '';
          fields.checkedBy.value = card.dataset.checkedBy || '';
          fields.checkedAt.value = card.dataset.checkedAt || '';
          fields.eventDate.value = card.dataset.eventDate || '';
          fields.reservationId.value = card.dataset.reservationId || '';
          fields.tables.value = card.dataset.tables || 0;
          fields.chairs.value = card.dataset.chairs || 0;
          fields.propane.value = card.dataset.propane || 0;
          fields.dolly.value = card.dataset.dolly || 0;
          fields.straps.value = card.dataset.straps || 0;
          fields.floorMats.value = card.dataset.floorMats || 0;
          fields.trashCans.value = card.dataset.trashCans || 0;
          fields.heaters.value = card.dataset.heaters || 0;
          fields.buffetWarmers.value = card.dataset.buffetWarmers || 0;
          fields.notes.value = card.dataset.notes || '';
          resetCheckboxes();
          let grills = [];
          try {
            grills = JSON.parse(card.dataset.grills || '[]');
          } catch (e) {}
          grillCheckboxes().forEach((box) => {
            box.checked = grills.includes(Number(box.value));
          });
          renderGrillSelection();
          syncStatusStyle();
          renderSummary();
        };

        openBtn?.addEventListener('click', () => {
          form.reset();
          resetCheckboxes();
          syncStatusStyle();
          setOpen(true);
        });

        closeBtn?.addEventListener('click', () => setOpen(false));
        cancelBtn?.addEventListener('click', () => setOpen(false));

        grillTrigger?.addEventListener('click', () => {
          setGrillOpen(!grillMulti.classList.contains('open'));
        });

        grillCheckboxes().forEach((box) => {
          box.addEventListener('change', () => {
            renderGrillSelection();
            renderSummary();
          });
        });

        Object.values(fields).forEach((field) => {
          field?.addEventListener('input', renderSummary);
          field?.addEventListener('change', renderSummary);
        });

        fields.vanStatus?.addEventListener('change', syncStatusStyle);

        modal.addEventListener('click', (event) => {
          if (event.target === modal) {
            setOpen(false);
            setGrillOpen(false);
          }
        });

        document.addEventListener('click', (event) => {
          if (!grillMulti?.contains(event.target)) {
            setGrillOpen(false);
          }
        });

        document.querySelectorAll('.edit-loadout-btn').forEach((button) => {
          button.addEventListener('click', () => {
            const card = button.closest('.van-card');
            if (!card) return;
            fillForm(card);
            setOpen(true);
          });
        });

        if (modal.classList.contains('open')) {
          setOpen(true);
        }

        syncStatusStyle();
        renderGrillSelection();
        renderSummary();
      })();
    </script>
  @endif
</body>
</html>
