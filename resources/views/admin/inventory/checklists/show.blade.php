<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Checklist Detail</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    .page{display:grid;gap:14px}
    .breadcrumbs{display:flex;align-items:center;gap:8px;flex-wrap:wrap;font-size:12px;color:#64748b}
    .breadcrumbs a{color:#475569;text-decoration:none}
    .breadcrumbs a:hover{color:#0f172a}
    .hero{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;flex-wrap:wrap;padding:18px 20px;border:1px solid #e2e8f0;border-radius:22px;background:linear-gradient(180deg,#ffffff,#f8fafc);box-shadow:0 16px 34px rgba(15,23,42,.05)}
    .hero-copy{display:grid;gap:10px;max-width:840px}
    .hero-kicker{font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#64748b}
    .hero-title{margin:0;font-size:32px;line-height:1.02;font-weight:800;color:#0f172a}
    .hero-subline{display:flex;flex-wrap:wrap;gap:8px;font-size:13px;line-height:1.5;color:#64748b}
    .hero-subline strong{color:#334155}
    .hero-note{font-size:13px;line-height:1.55;color:#475569;max-width:720px}
    .hero-meta{display:flex;flex-wrap:wrap;gap:8px}
    .hero-actions{display:flex;gap:10px;flex-wrap:wrap}
    .badge{display:inline-flex;align-items:center;padding:6px 10px;border-radius:999px;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;white-space:nowrap}
    .badge.type{background:#dbeafe;color:#1d4ed8}
    .badge.status.complete{background:#dcfce7;color:#166534}
    .badge.status.review{background:#fef3c7;color:#92400e}
    .badge.status.problem{background:#fee2e2;color:#b91c1c}
    .badge.clean.pass{background:#dcfce7;color:#166534}
    .badge.clean.ok{background:#fef3c7;color:#92400e}
    .badge.clean.no{background:#fee2e2;color:#b91c1c}
    .badge.gas{background:#eef2ff;color:#4338ca}
    .flag-strip{display:flex;flex-wrap:wrap;gap:6px}
    .flag{display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;border:1px solid #e2e8f0;background:#f8fafc;color:#475569;font-size:11px;font-weight:700}
    .flag.alert{background:#fff7ed;border-color:#fed7aa;color:#c2410c}
    .flag.problem{background:#fef2f2;border-color:#fecaca;color:#b91c1c}
    .flag.muted{background:#f8fafc;border-color:#cbd5e1;color:#64748b}
    .summary-bar,.panel{border:1px solid #e2e8f0;border-radius:20px;background:#fff;box-shadow:0 16px 34px rgba(15,23,42,.05)}
    .summary-bar{display:grid;gap:12px;padding:14px 16px}
    .summary-row{display:flex;flex-wrap:wrap;gap:8px}
    .summary-chip{display:inline-flex;align-items:center;gap:8px;padding:9px 12px;border:1px solid #e2e8f0;border-radius:14px;background:#f8fafc;min-width:0}
    .summary-chip-label{font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#94a3b8}
    .summary-chip-value{font-size:13px;font-weight:700;color:#0f172a;white-space:nowrap}
    .layout{display:grid;grid-template-columns:minmax(0,1.05fr) minmax(0,.95fr);gap:16px}
    .panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:18px 20px 0}
    .panel-title{margin:0;font-size:17px;font-weight:800;color:#0f172a}
    .panel-copy{margin:4px 0 0;font-size:13px;line-height:1.55;color:#64748b}
    .panel-body{padding:18px 20px 20px}
    .equipment-vertical-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));column-gap:18px;row-gap:0;margin-bottom:14px}
    .equipment-vertical-list .item{display:flex;justify-content:space-between;align-items:center;gap:16px;padding:8px 0;font-size:13px;border-bottom:1px solid #f1f5f9}
    .equipment-vertical-list .item:first-child{padding-top:0}
    .equipment-vertical-list .item:last-child{padding-bottom:0}
    .equipment-vertical-list .item span{color:#6b7280}
    .equipment-vertical-list .item strong{color:#111827;font-weight:600;text-align:right}
    .accessory-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0 18px}
    .accessory-item{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:9px 0;border-bottom:1px solid #e5e7eb;font-size:13px}
    .accessory-item span{color:#6b7280}
    .accessory-item strong{color:#0f172a;font-weight:700;text-align:right}
    .notes-panel{display:grid;gap:12px}
    .note-card{padding:14px;border:1px solid #e2e8f0;border-radius:16px;background:#f8fafc}
    .note-label{font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#64748b}
    .note-copy{margin-top:8px;font-size:14px;line-height:1.65;color:#334155;white-space:pre-wrap}
    .empty-state{display:grid;gap:4px;padding:18px;border:1px dashed #cbd5e1;border-radius:16px;background:#f8fafc;color:#64748b;font-size:13px}
    .empty-state strong{font-size:13px;color:#334155}
    .evidence-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
    .evidence-card{display:grid;gap:10px;padding:14px;border:1px solid #e2e8f0;border-radius:18px;background:#fff}
    .evidence-label{font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#64748b}
    .evidence-link,.evidence-placeholder{display:flex;align-items:center;justify-content:center;border:1px solid #e2e8f0;border-radius:18px;background:#f8fafc;min-height:260px;overflow:hidden}
    .evidence-link img{display:block;width:100%;height:260px;object-fit:cover}
    .evidence-placeholder{display:grid;place-items:center;border-style:dashed;color:#94a3b8;font-size:13px;text-align:center}
    .meta-list{display:grid;gap:8px}
    .meta-row{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:9px 0;border-bottom:1px solid #e5e7eb}
    .meta-row:last-child{border-bottom:0}
    .meta-row span{font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.08em}
    .meta-row strong{font-size:13px;color:#0f172a}
    @media (max-width: 1200px){
      .layout{grid-template-columns:1fr}
    }
    @media (max-width: 760px){
      .equipment-vertical-list{grid-template-columns:1fr}
      .accessory-grid{grid-template-columns:1fr}
      .evidence-grid{grid-template-columns:1fr}
      .hero-title{font-size:26px}
      .panel-head,.panel-body{padding-left:16px;padding-right:16px}
      .hero{padding:16px}
      .summary-row{display:grid}
    }
  </style>
</head>
<body>
  @php
    $tripStatusClass = match ($record->trip_status) {
        'Complete' => 'complete',
        'Needs Review' => 'review',
        'Missing Equipment', 'Damaged' => 'problem',
        default => 'review',
    };
    $cleanClass = strtolower((string) $record->clean);
    $hasNotes = filled($record->notes);
    $missingEvidence = !$record->picture1 && !$record->picture2;
    $needsCleaning = $record->clean === 'NO';
    $tripNeedsAttention = $record->trip_status !== 'Complete';
  @endphp
  <div class="container">
    <div class="page">
      <div class="breadcrumbs">
        <a href="{{ route('admin.inventory.dashboard') }}">Inventory</a>
        <span>/</span>
        <a href="{{ route('admin.inventory.checklists.index') }}">Checklist Records</a>
        <span>/</span>
        <span>Checklist Detail</span>
      </div>

      <div class="hero">
        <div class="hero-copy">
          <div class="hero-kicker">Inventory / Read-only Detail</div>
          <h1 class="hero-title">{{ $record->van_number ?: 'Unassigned Van' }} &mdash; {{ $record->checklist_type ?: 'Dispatch' }}</h1>
          <div class="hero-subline">
            <span>{{ $record->date_time?->format('M d, Y • g:i A') ?: 'No date recorded' }}</span>
            <span>•</span>
            <span>Logged by <strong>{{ $record->user ?: 'System' }}</strong></span>
          </div>
          <div class="hero-note">Read-only operational detail for this checklist submission, including equipment counts, trip condition, audit history, and photo evidence.</div>
          <div class="hero-meta">
            <span class="badge status {{ $tripStatusClass }}">{{ $record->trip_status ?: 'Complete' }}</span>
            @if($record->clean)
              <span class="badge clean {{ $cleanClass }}">{{ $record->clean }}</span>
            @endif
            @if($record->gas_level)
              <span class="badge gas">Gas {{ $record->gas_level }}</span>
            @endif
          </div>
          <div class="flag-strip">
            @if($hasNotes)
              <span class="flag alert">Operational Notes</span>
            @endif
            @if($needsCleaning)
              <span class="flag problem">Cleaning Required</span>
            @endif
            @if($tripNeedsAttention)
              <span class="flag problem">Trip Needs Review</span>
            @endif
            @if($missingEvidence)
              <span class="flag muted">Missing Photo Evidence</span>
            @endif
          </div>
        </div>
        <div class="hero-actions">
          <a class="btn secondary" href="{{ route('admin.inventory.checklists.index') }}">Back to Records</a>
          @if(auth()->user()?->hasPermission('inventory.manage'))
            <a class="btn" href="{{ route('admin.inventory.checklists.edit', $record->id) }}">Edit Checklist</a>
          @endif
        </div>
      </div>

      @include('admin.inventory._subnav')

      <section class="summary-bar">
        <div class="summary-row">
          <div class="summary-chip">
            <span class="summary-chip-label">Van</span>
            <span class="summary-chip-value">{{ $record->van_number ?: '—' }}</span>
          </div>
          <div class="summary-chip">
            <span class="summary-chip-label">Type</span>
            <span class="summary-chip-value">{{ $record->checklist_type ?: '—' }}</span>
          </div>
          <div class="summary-chip">
            <span class="summary-chip-label">Status</span>
            <span class="summary-chip-value">{{ $record->trip_status ?: '—' }}</span>
          </div>
          <div class="summary-chip">
            <span class="summary-chip-label">Gas</span>
            <span class="summary-chip-value">{{ $record->gas_level ?: '—' }}</span>
          </div>
          <div class="summary-chip">
            <span class="summary-chip-label">Clean</span>
            <span class="summary-chip-value">{{ $record->clean ?: '—' }}</span>
          </div>
          <div class="summary-chip">
            <span class="summary-chip-label">Logged By</span>
            <span class="summary-chip-value">{{ $record->user ?: '—' }}</span>
          </div>
          <div class="summary-chip">
            <span class="summary-chip-label">Date &amp; Time</span>
            <span class="summary-chip-value">{{ $record->date_time?->format('M d g:i A') ?: '—' }}</span>
          </div>
        </div>
      </section>

      <div class="layout">
        <section class="panel">
          <div class="panel-head">
            <div>
              <h2 class="panel-title">Equipment Breakdown</h2>
              <p class="panel-copy">Structured inventory counts captured as part of this checklist record.</p>
            </div>
          </div>
          <div class="panel-body">
            <div class="equipment-vertical-list">
              <div class="item"><span>Grills</span><strong>{{ $record->grills }}</strong></div>
              <div class="item"><span>Grills #</span><strong>{{ $record->grills_numbers ?: '—' }}</strong></div>
              <div class="item"><span>Propane</span><strong>{{ $record->propane }}</strong></div>
              <div class="item"><span>Tables</span><strong>{{ $record->tables }}</strong></div>
              <div class="item"><span>Chairs</span><strong>{{ $record->chairs }}</strong></div>
              <div class="item"><span>Covers</span><strong>{{ $record->chairs_covers }}</strong></div>
            </div>

            <div style="margin-top:14px">
              <div class="accessory-grid">
                <div class="accessory-item"><span>Dolly</span><strong>{{ $record->dolly }}</strong></div>
                <div class="accessory-item"><span>Ramps</span><strong>{{ $record->ramps }}</strong></div>
                <div class="accessory-item"><span>Mats</span><strong>{{ $record->mats }}</strong></div>
                <div class="accessory-item"><span>Clean</span><strong>{{ $record->clean ?: '—' }}</strong></div>
              </div>
            </div>
          </div>
        </section>

        <section class="panel">
          <div class="panel-head">
            <div>
              <h2 class="panel-title">Audit Summary</h2>
              <p class="panel-copy">Operational metadata and timing context for this record.</p>
            </div>
          </div>
          <div class="panel-body">
            <div class="meta-list">
              <div class="meta-row"><span>Record ID</span><strong>#{{ $record->id }}</strong></div>
              <div class="meta-row"><span>Created</span><strong>{{ $record->created_at?->format('M d, Y g:i A') ?: '—' }}</strong></div>
              <div class="meta-row"><span>Updated</span><strong>{{ $record->updated_at?->format('M d, Y g:i A') ?: '—' }}</strong></div>
              <div class="meta-row"><span>Checklist Type</span><strong>{{ $record->checklist_type ?: '—' }}</strong></div>
              <div class="meta-row"><span>Trip Status</span><strong>{{ $record->trip_status ?: '—' }}</strong></div>
              <div class="meta-row"><span>Gas</span><strong>{{ $record->gas_level ?: '—' }}</strong></div>
            </div>
          </div>
        </section>
      </div>

      <section class="panel">
        <div class="panel-head">
          <div>
            <h2 class="panel-title">Notes &amp; Condition</h2>
            <p class="panel-copy">Operational comments, exceptions, and summary notes attached to this checklist.</p>
          </div>
        </div>
        <div class="panel-body">
          <div class="notes-panel">
            @if($hasNotes)
              <div class="note-card">
                <div class="note-label">Operational Notes</div>
                <div class="note-copy">{{ $record->notes }}</div>
              </div>
            @else
              <div class="empty-state">
                <strong>No operational notes recorded</strong>
                <span>No condition issues or extra checklist comments were reported for this record.</span>
              </div>
            @endif
          </div>
        </div>
      </section>

      <section class="panel">
        <div class="panel-head">
          <div>
            <h2 class="panel-title">Photo Evidence</h2>
            <p class="panel-copy">Larger preview cards for the images attached to this checklist record.</p>
          </div>
        </div>
        <div class="panel-body">
          <div class="evidence-grid">
            @foreach (['picture1' => 'Pic 1', 'picture2' => 'Pic 2'] as $field => $label)
              <div class="evidence-card">
                <div class="evidence-label">{{ $label }}</div>
                @if($record->{$field})
                  <a class="evidence-link" href="{{ \App\Support\UploadedFiles::url($record->{$field}) }}" target="_blank" rel="noopener">
                    <img src="{{ \App\Support\UploadedFiles::url($record->{$field}) }}" alt="{{ $label }}">
                  </a>
                @else
                  <div class="evidence-placeholder">
                    <strong style="font-size:13px;color:#475569">{{ $label }} not attached</strong>
                    <span>Photo evidence was not provided for this slot.</span>
                  </div>
                @endif
              </div>
            @endforeach
          </div>
        </div>
      </section>
    </div>
  </div>
</body>
</html>
