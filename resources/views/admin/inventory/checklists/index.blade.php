                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   <!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin – Checklist Records</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    .page{display:grid;gap:16px}
    .hero{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap}
    .hero-copy{display:grid;gap:6px;max-width:760px}                          
    .hero-kicker{font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#64748b}
    .hero-title{margin:0;font-size:30px;line-height:1.05;font-weight:800;color:#0f172a}
    .hero-note{font-size:14px;line-height:1.6;color:#475569}
    .hero-actions{display:flex;gap:10px;flex-wrap:wrap}
    .filters-card,.table-card{border:1px solid #e2e8f0;border-radius:22px;background:#fff;box-shadow:0 16px 34px rgba(15,23,42,.05)}
    .filters-body,.table-body{padding:18px 20px}
    .quick-strip{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:14px}
    .quick-pills{display:flex;flex-wrap:wrap;gap:8px}
    .quick-chip{display:inline-flex;align-items:center;padding:7px 12px;border-radius:999px;border:1px solid #dbe2ea;background:#fff;color:#475569;text-decoration:none;font-size:12px;font-weight:700}
    .quick-chip.active{background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8}
    .toolbar-note{font-size:12px;color:#64748b}
    .filter-grid{display:grid;grid-template-columns:2fr repeat(6,minmax(0,1fr));gap:10px;align-items:end}
    .filter-grid .btn{width:100%;justify-content:center}
    .filter-grid .span-actions{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
    .table-card,.table-body{overflow:visible}
    .table-wrap{overflow-x:auto;overflow-y:visible;border:1px solid #e5e7eb;border-radius:18px;background:#fff}
    .sheet-table{width:100%;min-width:1480px;border-collapse:separate;border-spacing:0;font-size:11px;table-layout:auto}
    .sheet-table th,.sheet-table td{padding:5px 6px;text-align:left;vertical-align:middle;border-bottom:1px solid #eef2f7;white-space:nowrap}
    .sheet-table thead th{position:sticky;top:0;z-index:2;background:#f8fafc;color:#64748b;font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.03em;border-bottom:1px solid #e5e7eb;padding-top:5px;padding-bottom:5px}
    .sheet-table tbody tr:hover td{background:#fafcff}
    .sticky-col{position:sticky;left:0;background:#fff;z-index:1}
    .sticky-col-2{position:sticky;left:94px;background:#fff;z-index:1}
    .sticky-col-3{position:sticky;left:178px;background:#fff;z-index:1}
    .sheet-table thead .sticky-col,.sheet-table thead .sticky-col-2,.sheet-table thead .sticky-col-3{z-index:3;background:#f8fafc}
    .badge-col,.media-col,.actions-col{overflow:visible}
    .col-datetime{width:94px}
    .col-user{width:84px}
    .col-van{width:60px}
    .col-type{width:84px}
    .col-status{width:42px}
    .col-gas{width:48px}
    .col-num{width:44px}
    .col-grills-numbers{width:68px}
    .col-notes{width:108px}
    .col-clean{width:58px}
    .col-media{width:46px}
    .col-actions{width:112px}
    .badge{display:inline-flex;align-items:center;padding:3px 6px;border-radius:999px;font-size:9.5px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;white-space:nowrap}
    .badge.clean-pass,.badge.trip-complete{background:#dcfce7;color:#166534}
    .badge.clean-ok,.badge.trip-needs-review{background:#fef3c7;color:#92400e}
    .badge.clean-no,.badge.trip-missing-equipment,.badge.trip-damaged{background:#fee2e2;color:#b91c1c}
    .badge.type-dispatch{background:#dbeafe;color:#1d4ed8}
    .badge.type-return-check{background:#ede9fe;color:#6d28d9}
    .badge.type-maintenance{background:#e0f2fe;color:#0f766e}
    .status-compact{display:inline-flex;align-items:center;justify-content:center;min-width:18px;height:18px;padding:0 4px;border-radius:999px;font-size:9px;font-weight:800;letter-spacing:.02em}
    .status-compact.complete{background:#dcfce7;color:#166534}
    .cell-strong{font-weight:700;color:#0f172a;font-size:11px;line-height:1.15}
    .cell-muted{display:block;margin-top:1px;font-size:10px;line-height:1.15;color:#64748b;white-space:normal}
    .cell-truncate{display:block;max-width:96px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .num{text-align:center;font-size:10.5px;font-variant-numeric:tabular-nums}
    .center{text-align:center}
    .badge-wrap{display:inline-flex;align-items:center;justify-content:center;min-width:max-content}
    .thumb,.empty-thumb{width:28px;height:28px;border-radius:8px}
    .thumb{display:block;object-fit:cover;border:1px solid #e5e7eb;background:#fff}
    .empty-thumb{display:inline-flex;align-items:center;justify-content:center;border:1px dashed #cbd5e1;background:#f8fafc;color:#94a3b8;font-size:12px;text-align:center;padding:2px}
    .actions{display:flex;justify-content:center;white-space:nowrap}
    .action-trigger{display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:26px;padding:0 8px;border-radius:8px;border:1px solid #dbe2ea;background:#fff;color:#334155;cursor:pointer;font-size:14px;font-weight:800;line-height:1;box-shadow:0 1px 2px rgba(15,23,42,.03)}
    .action-trigger:hover{background:#f8fafc}
    .floating-menu{position:fixed;z-index:9999;display:none;min-width:128px;padding:6px;gap:2px;border:1px solid #dbe2ea;border-radius:12px;background:#fff;box-shadow:0 14px 34px rgba(15,23,42,.16)}
    .floating-menu.open{display:grid}
    .floating-menu form{margin:0}
    .action-link,.action-delete{display:flex;align-items:center;justify-content:flex-start;width:100%;min-height:30px;padding:0 10px;border-radius:8px;border:1px solid transparent;background:#fff;color:#334155;text-decoration:none;font-size:10.5px;font-weight:700;white-space:nowrap}
    .action-link:hover,.action-delete:hover{background:#f8fafc}
    .action-delete{color:#b91c1c}
    .empty-row{padding:16px;color:#64748b}
    @media (max-width: 1260px){
      .filter-grid{grid-template-columns:repeat(3,minmax(0,1fr))}
      .filter-grid .span-actions{grid-template-columns:repeat(2,minmax(0,1fr));grid-column:1 / -1}
    }
    @media (max-width: 760px){
      .filter-grid,.filter-grid .span-actions{grid-template-columns:1fr}
      .hero-title{font-size:26px}
      .filters-body,.table-body{padding-left:16px;padding-right:16px}
      .sticky-col,.sticky-col-2,.sticky-col-3{position:static}
      .sheet-table{min-width:1380px}
    }
  </style>
</head>
<body>
  @php
    $queryBase = request()->except('page');
    $quickPresets = [
        'today' => 'Today',
        'this-week' => 'This Week',
        'this-month' => 'This Month',
    ];
    $cleanClass = fn (?string $value) => match ($value) {
        'PASS' => 'clean-pass',
        'OK' => 'clean-ok',
        'NO' => 'clean-no',
        default => '',
    };
    $tripClass = fn (?string $value) => 'trip-' . \Illuminate\Support\Str::slug((string) $value);
    $typeClass = fn (?string $value) => 'type-' . \Illuminate\Support\Str::slug((string) $value);
  @endphp
  <div class="container">
    <div class="page">
      <div class="hero">
        <div class="hero-copy">
          <div class="hero-kicker">Inventory / Checklist Records</div>
          <h1 class="hero-title">Checklist Records</h1>
          <div class="hero-note">Spreadsheet-style operational log for dispatch, return, and maintenance checklists. Optimized for fast comparison, auditing, and day-to-day fleet review.</div>
        </div>
        <div class="hero-actions">
          @if(auth()->user()?->hasPermission('inventory.manage'))
            <a class="btn" href="{{ route('admin.inventory.checklists.create') }}">New Checklist</a>
          @endif
          <a class="btn secondary" href="{{ route('admin.inventory.checklists.export', array_merge($queryBase, ['format' => 'csv'])) }}">Export CSV</a>
          <a class="btn secondary" href="{{ route('admin.inventory.checklists.export', array_merge($queryBase, ['format' => 'excel'])) }}">Export Excel</a>
        </div>
      </div>

      @if (session('ok'))
        <div class="card"><div class="card-body" style="color:#166534;font-weight:700">{{ session('ok') }}</div></div>
      @endif

      @include('admin.inventory._subnav')

      <section class="filters-card">
        <div class="filters-body">
          <div class="quick-strip">
            <div class="quick-pills">
              @foreach ($quickPresets as $value => $label)
                <a class="quick-chip {{ $filters['preset'] === $value ? 'active' : '' }}" href="{{ route('admin.inventory.checklists.index', array_merge($queryBase, ['preset' => $value])) }}">{{ $label }}</a>
              @endforeach
              <a class="quick-chip {{ $filters['preset'] === '' ? 'active' : '' }}" href="{{ route('admin.inventory.checklists.index', array_merge($queryBase, ['preset' => ''])) }}">Custom</a>
            </div>
            <div class="toolbar-note">Quick filters keep the current filter context and update only the checklist date range.</div>
          </div>

          <form method="get" action="{{ route('admin.inventory.checklists.index') }}" class="filter-grid">
            <input type="hidden" name="preset" value="{{ $filters['preset'] }}">
            <div>
              <label class="label" for="q">Search</label>
              <input class="input" id="q" name="q" value="{{ $filters['q'] }}" placeholder="User, van, checklist type, trip status, notes">
            </div>
            <div>
              <label class="label" for="from">Start Date</label>
              <input class="input" id="from" type="date" name="from" value="{{ $filters['from'] }}">
            </div>
            <div>
              <label class="label" for="to">End Date</label>
              <input class="input" id="to" type="date" name="to" value="{{ $filters['to'] }}">
            </div>
            <div>
              <label class="label" for="van_number">Van</label>
              <select class="select" id="van_number" name="van_number">
                <option value="">All vans</option>
                @foreach ($vanOptions as $van)
                  <option value="{{ $van['value'] }}" {{ $filters['van_number'] === $van['value'] ? 'selected' : '' }}>{{ $van['label'] }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="label" for="checklist_type">Checklist Type</label>
              <select class="select" id="checklist_type" name="checklist_type">
                <option value="">All</option>
                @foreach ($checklistTypeOptions as $option)
                  <option value="{{ $option }}" {{ $filters['checklist_type'] === $option ? 'selected' : '' }}>{{ $option }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="label" for="clean">Clean</label>
              <select class="select" id="clean" name="clean">
                <option value="">All</option>
                @foreach ($cleanOptions as $option)
                  <option value="{{ $option }}" {{ $filters['clean'] === $option ? 'selected' : '' }}>{{ $option }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="label" for="trip_status">Trip Status</label>
              <select class="select" id="trip_status" name="trip_status">
                <option value="">All</option>
                @foreach ($tripStatusOptions as $option)
                  <option value="{{ $option }}" {{ $filters['trip_status'] === $option ? 'selected' : '' }}>{{ $option }}</option>
                @endforeach
              </select>
            </div>
            <div class="span-actions">
              <button class="btn secondary" type="submit">Apply</button>
              <a class="btn secondary" href="{{ route('admin.inventory.checklists.index') }}">Reset</a>
            </div>
          </form>
        </div>
      </section>

      <section class="table-card">
        <div class="table-body">
          <div class="table-wrap">
            <table class="sheet-table">
              <colgroup>
                <col class="col-datetime">
                <col class="col-user">
                <col class="col-van">
                <col class="col-type">
                <col class="col-status">
                <col class="col-gas">
                <col class="col-num">
                <col class="col-grills-numbers">
                <col class="col-num">
                <col class="col-num">
                <col class="col-num">
                <col class="col-num">
                <col class="col-num">
                <col class="col-num">
                <col class="col-num">
                <col class="col-clean">
                <col class="col-notes">
                <col class="col-media">
                <col class="col-media">
                <col class="col-actions">
              </colgroup>
              <thead>
                <tr>
                  <th class="sticky-col">Date &amp; Time</th>
                  <th class="sticky-col-2">User</th>
                  <th class="sticky-col-3">Van #</th>
                  <th class="center">Type</th>
                  <th class="center">Status</th>
                  <th>Gas</th>
                  <th>Grills</th>
                  <th>Grills #</th>
                  <th>Propane</th>
                  <th>Tables</th>
                  <th>Chairs</th>
                  <th>Covers</th>
                  <th>Dolly</th>
                  <th>Ramps</th>
                  <th>Mats</th>
                  <th class="center">Clean</th>
                  <th>Notes</th>
                  <th>Pic 1</th>
                  <th>Pic 2</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($records as $record)
                  <tr>
                    <td class="sticky-col">
                      <span class="cell-strong">{{ $record->date_time?->format('m/d H:i') ?: '—' }}</span>
                    </td>
                    <td class="sticky-col-2"><span class="cell-strong">{{ $record->user ?: '—' }}</span></td>
                    <td class="sticky-col-3"><span class="cell-strong">{{ $record->van_number ?: '—' }}</span></td>
                    <td class="center badge-col"><span class="badge-wrap"><span class="badge {{ $typeClass($record->checklist_type) }}">{{ $record->checklist_type ?: '—' }}</span></span></td>
                    <td class="center badge-col">
                      <span class="badge-wrap">
                        @if(($record->trip_status ?? '') === 'Complete')
                          <span class="status-compact complete" title="Complete">C</span>
                        @else
                          <span class="badge {{ $tripClass($record->trip_status) }}">{{ $record->trip_status ?: '—' }}</span>
                        @endif
                      </span>
                    </td>
                    <td class="num"><span class="cell-strong">{{ $record->gas_level ?: '—' }}</span></td>
                    <td class="num">{{ $record->grills ?? '—' }}</td>
                    <td><span class="cell-truncate" title="{{ $record->grills_numbers ?: '' }}">{{ $record->grills_numbers ?: '—' }}</span></td>
                    <td class="num">{{ $record->propane ?? '—' }}</td>
                    <td class="num">{{ $record->tables ?? '—' }}</td>
                    <td class="num">{{ $record->chairs ?? '—' }}</td>
                    <td class="num">{{ $record->chairs_covers ?? '—' }}</td>
                    <td class="num">{{ $record->dolly ?? '—' }}</td>
                    <td class="num">{{ $record->ramps ?? '—' }}</td>
                    <td class="num">{{ $record->mats ?? '—' }}</td>
                    <td class="center badge-col">
                      @if($record->clean)
                        <span class="badge-wrap"><span class="badge {{ $cleanClass($record->clean) }}">{{ $record->clean }}</span></span>
                      @else
                        —
                      @endif
                    </td>
                    <td><span class="cell-truncate" title="{{ $record->notes ?: '' }}">{{ $record->notes ?: '—' }}</span></td>
                    <td class="media-col">
                      @if($record->picture1)
                        <a href="{{ Storage::url($record->picture1) }}" target="_blank" rel="noopener">
                          <img class="thumb" src="{{ Storage::url($record->picture1) }}" alt="Checklist image 1">
                        </a>
                      @else
                        <span class="empty-thumb" aria-label="No image">🖼</span>
                      @endif
                    </td>
                    <td class="media-col">
                      @if($record->picture2)
                        <a href="{{ Storage::url($record->picture2) }}" target="_blank" rel="noopener">
                          <img class="thumb" src="{{ Storage::url($record->picture2) }}" alt="Checklist image 2">
                        </a>
                      @else
                        <span class="empty-thumb" aria-label="No image">🖼</span>
                      @endif
                    </td>
                    <td class="actions-col">
                      <div class="actions">
                        @if(auth()->user()?->hasPermission('inventory.manage'))
                          <button
                            class="action-trigger"
                            type="button"
                            aria-label="Open actions menu"
                            data-action-trigger
                            data-view-url="{{ route('admin.inventory.checklists.show', $record->id) }}"
                            data-edit-url="{{ route('admin.inventory.checklists.edit', $record->id) }}"
                            data-delete-url="{{ route('admin.inventory.checklists.delete', $record->id) }}"
                          >•••</button>
                        @endif
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="20" class="empty-row">No checklist records match the current filters.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </section>

      @include('admin.inventory._pager', ['paginator' => $records->withQueryString()])
    </div>
  </div>

  @if(auth()->user()?->hasPermission('inventory.manage'))
    <div id="checklist-action-menu" class="floating-menu" aria-hidden="true">
      <a id="checklist-action-view" class="action-link" href="#">View</a>
      <a id="checklist-action-edit" class="action-link" href="#">Edit</a>
      <form id="checklist-action-delete-form" method="post" action="#" onsubmit="return confirm('Delete this checklist record?')">
        @csrf
        <button class="action-delete" type="submit">Delete</button>
      </form>
    </div>

    <script>
      (() => {
        const menu = document.getElementById('checklist-action-menu');
        if (!menu) return;

        const viewLink = document.getElementById('checklist-action-view');
        const editLink = document.getElementById('checklist-action-edit');
        const deleteForm = document.getElementById('checklist-action-delete-form');
        let activeTrigger = null;

        const closeMenu = () => {
          menu.classList.remove('open');
          menu.setAttribute('aria-hidden', 'true');
          activeTrigger = null;
        };

        const openMenu = (trigger) => {
          activeTrigger = trigger;
          viewLink.href = trigger.dataset.viewUrl;
          editLink.href = trigger.dataset.editUrl;
          deleteForm.action = trigger.dataset.deleteUrl;

          menu.classList.add('open');
          menu.setAttribute('aria-hidden', 'false');

          const triggerRect = trigger.getBoundingClientRect();
          const menuRect = menu.getBoundingClientRect();
          const gap = 8;
          let left = triggerRect.right - menuRect.width;
          let top = triggerRect.top - menuRect.height - gap;

          if (left < 8) {
            left = 8;
          }

          if (top < 8) {
            top = triggerRect.bottom + gap;
          }

          if (top + menuRect.height > window.innerHeight - 8) {
            top = Math.max(8, window.innerHeight - menuRect.height - 8);
          }

          menu.style.left = `${left}px`;
          menu.style.top = `${top}px`;
        };

        document.querySelectorAll('[data-action-trigger]').forEach((trigger) => {
          trigger.addEventListener('click', (event) => {
            event.stopPropagation();

            if (activeTrigger === trigger && menu.classList.contains('open')) {
              closeMenu();
              return;
            }

            openMenu(trigger);
          });
        });

        document.addEventListener('click', (event) => {
          if (!menu.contains(event.target)) {
            closeMenu();
          }
        });

        document.addEventListener('keydown', (event) => {
          if (event.key === 'Escape') {
            closeMenu();
          }
        });

        window.addEventListener('resize', closeMenu);
        window.addEventListener('scroll', closeMenu, true);
      })();
    </script>
  @endif
</body>
</html>
