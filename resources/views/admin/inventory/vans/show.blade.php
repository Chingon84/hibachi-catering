<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin – Van Loadout</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    .page{display:grid;gap:14px}
    .subnav{display:flex;flex-wrap:wrap;gap:8px}
    .subnav a{display:inline-flex;align-items:center;padding:9px 12px;border-radius:999px;border:1px solid var(--border);background:#fff;color:#334155;text-decoration:none;font-size:12px;font-weight:700}
    .subnav a.active{background:#0f172a;border-color:#0f172a;color:#fff}
    .hero{display:grid;grid-template-columns:minmax(0,1.1fr) minmax(320px,.9fr);gap:14px}
    .layout{display:grid;grid-template-columns:minmax(0,1.25fr) minmax(320px,.85fr);gap:14px}
    .panel{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:16px;box-shadow:0 10px 24px rgba(15,23,42,.04)}
    .panel-title{margin:0;font-size:20px}
    .panel-copy{margin:8px 0 0;color:var(--muted);font-size:14px}
    .eyebrow{font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#64748b}
    .van-name{margin:8px 0 0;font-size:32px;line-height:1.04}
    .status-pill{display:inline-flex;align-items:center;padding:5px 10px;border-radius:999px;font-size:12px;font-weight:800}
    .status-pill.clean{background:#ecfdf5;color:#166534}
    .status-pill.dirty{background:#fef2f2;color:#b91c1c}
    .status-pill.neutral{background:#fff7ed;color:#9a3412}
    .status-pill.ready{background:#ecfdf5;color:#166534}
    .status-pill.review{background:#fff7ed;color:#9a3412}
    .summary-grid,.meta-grid,.count-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
    .summary-item,.meta-item,.count-item{padding:12px;border:1px solid #e5e7eb;border-radius:14px;background:#fff}
    .summary-label,.meta-label,.count-label{font-size:11px;font-weight:800;text-transform:uppercase;color:#64748b}
    .summary-value,.meta-value,.count-value{margin-top:8px;font-size:22px;font-weight:800;color:#0f172a}
    .summary-value.small,.meta-value.small{font-size:14px;font-weight:700;line-height:1.45}
    .form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
    .field{display:flex;flex-direction:column;gap:6px}
    .field.span-2{grid-column:1 / -1}
    .grill-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:8px}
    .grill-option{display:flex;align-items:center;gap:8px;padding:9px 10px;border:1px solid #e5e7eb;border-radius:12px;background:#fff}
    .table-wrap{overflow:auto}
    .table-wide{width:100%;border-collapse:separate;border-spacing:0;min-width:980px}
    .table-wide th,.table-wide td{padding:12px 14px;text-align:left;vertical-align:top}
    .table-wide thead th{background:#f8fafc;border-bottom:1px solid #e5e7eb;color:#64748b;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.06em}
    .table-wide tbody tr + tr td{border-top:1px solid #eef2f7}
    .pager{display:flex;align-items:center;justify-content:flex-end;gap:10px;margin-top:14px}
    .pager .disabled{opacity:.55;pointer-events:none}
    .pager-meta{font-size:13px;color:#64748b}
    @media (max-width: 1080px){.hero,.layout{grid-template-columns:1fr}.grill-grid{grid-template-columns:repeat(4,minmax(0,1fr))}}
    @media (max-width: 760px){.summary-grid,.meta-grid,.count-grid,.form-grid,.grill-grid{grid-template-columns:1fr}}
  </style>
</head>
<body>
  <div class="container">
    <div class="page">
      <div class="header">
        <h1 class="title" style="margin-right:auto">Van Loadout</h1>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
          <a class="btn secondary" href="{{ route('admin.inventory.vans.index') }}">Back to Vans</a>
          @if(auth()->user()?->hasPermission('inventory.manage'))
            <a class="btn" href="{{ route('admin.inventory.vans.edit', $van->id) }}">Edit Van</a>
          @endif
        </div>
      </div>

      @if (session('ok'))
        <div class="card"><div class="card-body" style="color:#166534;font-weight:700">{{ session('ok') }}</div></div>
      @endif

      @if ($errors->any())
        <div class="card"><div class="card-body" style="color:#b91c1c;font-weight:700">{{ $errors->first() }}</div></div>
      @endif

      @include('admin.inventory._subnav')

      <div class="hero">
        <section class="panel">
          <div class="eyebrow">Van Operational Profile</div>
          <h2 class="van-name">{{ $van->name }}</h2>
          <p class="panel-copy">{{ $van->code ?: 'No code' }} @if($van->license_plate) • {{ $van->license_plate }} @endif</p>
          <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px">
            <span class="status-pill {{ $loadout->van_status }}">{{ ucfirst($loadout->van_status) }}</span>
            <span class="status-pill {{ $loadout->isReady() ? 'ready' : 'review' }}">{{ $loadout->isReady() ? 'Ready for next event' : 'Needs review' }}</span>
          </div>
          <div class="meta-grid" style="margin-top:16px">
            <div class="meta-item">
              <div class="meta-label">Last Updated</div>
              <div class="meta-value small">{{ $loadout->exists ? $loadout->updated_at?->format('m/d/Y g:i A') : 'No loadout recorded' }}</div>
            </div>
            <div class="meta-item">
              <div class="meta-label">Checked By</div>
              <div class="meta-value small">{{ $loadout->checkedBy?->name ?: 'Not recorded' }}</div>
            </div>
            <div class="meta-item">
              <div class="meta-label">Loaded By</div>
              <div class="meta-value small">{{ $loadout->loadedBy?->name ?: 'Not recorded' }}</div>
            </div>
            <div class="meta-item">
              <div class="meta-label">Checked At</div>
              <div class="meta-value small">{{ $loadout->checked_at?->format('m/d/Y g:i A') ?: 'Not recorded' }}</div>
            </div>
          </div>
        </section>

        <aside class="panel">
          <h2 class="panel-title">Current Van Summary</h2>
          <p class="panel-copy">Office staff can use this read-only summary before assigning the van to an event.</p>
          <div class="summary-grid" style="margin-top:14px">
            <div class="summary-item">
              <div class="summary-label">Status</div>
              <div class="summary-value small">{{ ucfirst($loadout->van_status) }}</div>
            </div>
            <div class="summary-item">
              <div class="summary-label">Grills</div>
              <div class="summary-value small">{{ $loadout->grillSummary() }}</div>
            </div>
            <div class="summary-item"><div class="summary-label">Tables</div><div class="summary-value">{{ (int) $loadout->tables_count }}</div></div>
            <div class="summary-item"><div class="summary-label">Chairs</div><div class="summary-value">{{ (int) $loadout->chairs_count }}</div></div>
            <div class="summary-item"><div class="summary-label">Propane</div><div class="summary-value">{{ (int) $loadout->propane_tanks_count }}</div></div>
            <div class="summary-item"><div class="summary-label">Dolly</div><div class="summary-value">{{ (int) $loadout->dolly_count }}</div></div>
            <div class="summary-item"><div class="summary-label">Straps</div><div class="summary-value">{{ (int) $loadout->straps_count }}</div></div>
            <div class="summary-item"><div class="summary-label">Floor Mats</div><div class="summary-value">{{ (int) $loadout->floor_mats_count }}</div></div>
            <div class="summary-item"><div class="summary-label">Trash Cans</div><div class="summary-value">{{ (int) $loadout->trash_cans_count }}</div></div>
            <div class="summary-item"><div class="summary-label">Heaters</div><div class="summary-value">{{ (int) $loadout->heaters_count }}</div></div>
            <div class="summary-item"><div class="summary-label">Buffet Warmers</div><div class="summary-value">{{ (int) $loadout->buffet_warmers_count }}</div></div>
            <div class="summary-item">
              <div class="summary-label">Event</div>
              <div class="summary-value small">
                @if($loadout->reservation)
                  {{ $loadout->reservation->customer_name }} @if($loadout->reservation->date) • {{ $loadout->reservation->date->format('m/d/Y') }} @endif
                @elseif($loadout->event_date)
                  {{ $loadout->event_date->format('m/d/Y') }}
                @else
                  Not linked
                @endif
              </div>
            </div>
          </div>
        </aside>
      </div>

      <div class="layout">
        @if(auth()->user()?->hasPermission('inventory.manage'))
          <section class="panel">
            <h2 class="panel-title">Van Loadout Form</h2>
            <p class="panel-copy">Update the real loadout in one practical form without creating one inventory row per item.</p>

            <form method="post" action="{{ route('admin.inventory.vans.loadout.store') }}" style="margin-top:14px">
              @csrf
              <div class="form-grid">
                <div class="field">
                  <label class="label" for="van_number">Van Number</label>
                  <select class="select" id="van_number" name="van_number" required>
                    @foreach (range(1, 20) as $number)
                      <option value="{{ $number }}" {{ (string) old('van_number', $van->van_number) === (string) $number ? 'selected' : '' }}>Van {{ $number }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="field">
                  <label class="label" for="van_status">Van Status</label>
                  <select class="select" id="van_status" name="van_status" required>
                    @foreach (\App\Models\VanLoadout::VAN_STATUSES as $status)
                      <option value="{{ $status }}" {{ old('van_status', $loadout->van_status ?: 'neutral') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="field">
                  <label class="label" for="checked_at">Checked At</label>
                  <input class="input" type="datetime-local" id="checked_at" name="checked_at" value="{{ old('checked_at', $loadout->checked_at?->format('Y-m-d\\TH:i')) }}">
                </div>
                <div class="field">
                  <label class="label" for="loaded_by_user_id">Loaded By</label>
                  <select class="select" id="loaded_by_user_id" name="loaded_by_user_id">
                    <option value="">Select team member</option>
                    @foreach ($teamMembers as $member)
                      <option value="{{ $member->id }}" {{ (string) old('loaded_by_user_id', $loadout->loaded_by_user_id) === (string) $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="field">
                  <label class="label" for="checked_by_user_id">Checked By</label>
                  <select class="select" id="checked_by_user_id" name="checked_by_user_id">
                    <option value="">Select team member</option>
                    @foreach ($teamMembers as $member)
                      <option value="{{ $member->id }}" {{ (string) old('checked_by_user_id', $loadout->checked_by_user_id) === (string) $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="field">
                  <label class="label" for="event_date">Event Date</label>
                  <input class="input" type="date" id="event_date" name="event_date" value="{{ old('event_date', $loadout->event_date?->toDateString()) }}">
                </div>
                <div class="field">
                  <label class="label" for="reservation_id">Reservation Reference</label>
                  <select class="select" id="reservation_id" name="reservation_id">
                    <option value="">No reservation linked</option>
                    @foreach ($reservationOptions as $reservation)
                      <option value="{{ $reservation->id }}" {{ (string) old('reservation_id', $loadout->reservation_id) === (string) $reservation->id ? 'selected' : '' }}>
                        {{ $reservation->customer_name }} @if($reservation->date) • {{ $reservation->date->format('m/d/Y') }} @endif @if($reservation->code) • {{ $reservation->code }} @endif
                      </option>
                    @endforeach
                  </select>
                </div>
                <div class="field span-2">
                  <label class="label">Grills</label>
                  <div class="grill-grid">
                    @php $selectedGrills = collect(old('grills', $loadout->grills ?? []))->map(fn ($grill) => (int) $grill)->all(); @endphp
                    @foreach ($grillOptions as $grill)
                      <label class="grill-option">
                        <input type="checkbox" name="grills[]" value="{{ $grill }}" {{ in_array($grill, $selectedGrills, true) ? 'checked' : '' }}>
                        <span>Grill #{{ $grill }}</span>
                      </label>
                    @endforeach
                  </div>
                </div>
              </div>

              <div class="count-grid" style="margin-top:14px">
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
                    <label class="label" for="{{ $field }}">{{ $label }}</label>
                    <input class="input" type="number" min="0" id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $loadout->{$field} ?? 0) }}" required>
                  </div>
                @endforeach
              </div>

              <div class="field" style="margin-top:14px">
                <label class="label" for="notes">Notes</label>
                <textarea class="input" id="notes" name="notes" rows="5" style="min-height:140px">{{ old('notes', $loadout->notes) }}</textarea>
              </div>

              <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px">
                <button class="btn" type="submit">Save Loadout</button>
                <a class="btn secondary" href="{{ route('admin.inventory.vans.index') }}">Back to Vans</a>
              </div>
            </form>
          </section>
        @endif

        <aside class="panel">
          <h2 class="panel-title">Loadout Snapshot</h2>
          <p class="panel-copy">Operational summary for office and warehouse teams.</p>
          <div class="summary-grid" style="margin-top:14px">
            <div class="summary-item">
              <div class="summary-label">Ready Status</div>
              <div class="summary-value small">{{ $loadout->isReady() ? 'Ready for next event' : 'Review before dispatch' }}</div>
            </div>
            <div class="summary-item">
              <div class="summary-label">Notes</div>
              <div class="summary-value small">{{ $loadout->notes ?: 'No notes recorded.' }}</div>
            </div>
          </div>
        </aside>
      </div>

      <section class="panel">
        <h2 class="panel-title">Load History</h2>
        <p class="panel-copy">Historical snapshots of the van loadout for operations and accountability.</p>
        <div class="table-wrap" style="margin-top:14px">
          <table class="table-wide">
            <thead>
              <tr>
                <th>Date</th>
                <th>Van</th>
                <th>Status</th>
                <th>Grills</th>
                <th>Key Equipment</th>
                <th>Updated By</th>
                <th>Notes</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($history as $entry)
                <tr>
                  <td>{{ $entry->checked_at?->format('m/d/Y g:i A') ?: $entry->created_at?->format('m/d/Y g:i A') }}</td>
                  <td>{{ $van->name }}</td>
                  <td><span class="status-pill {{ $entry->van_status }}">{{ ucfirst($entry->van_status) }}</span></td>
                  <td>{{ $entry->grillSummary() }}</td>
                  <td>
                    Tables {{ $entry->tables_count }},
                    Chairs {{ $entry->chairs_count }},
                    Propane {{ $entry->propane_tanks_count }},
                    Heaters {{ $entry->heaters_count }}
                  </td>
                  <td>{{ $entry->checkedBy?->name ?: $entry->loadedBy?->name ?: 'System' }}</td>
                  <td>{{ $entry->notes ?: '—' }}</td>
                </tr>
              @empty
                <tr><td colspan="7" class="muted">No load history recorded yet for this van.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @include('admin.inventory._pager', ['paginator' => $history->withQueryString()])
      </section>
    </div>
  </div>
</body>
</html>
