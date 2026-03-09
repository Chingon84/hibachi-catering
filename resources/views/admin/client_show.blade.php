<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - Client</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    :root{
      --bg:#f3f5f9;
      --surface:#ffffff;
      --surface-soft:#f8fafc;
      --border:#e5e7eb;
      --text:#0f172a;
      --muted:#64748b;
      --brand:#b21e27;
      --brand-hover:#991b1b;
      --shadow:0 10px 30px rgba(15,23,42,.06);
    }
    body{background:var(--bg);color:var(--text)}
    .dashboard-wrap{max-width:1680px;margin:0 auto;padding:16px}
    .card{background:var(--surface);border:1px solid var(--border);border-radius:20px;box-shadow:var(--shadow)}
    .card-body{padding:22px}
    .hero{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;flex-wrap:wrap}
    .hero-id{display:flex;align-items:flex-start;gap:14px;min-width:280px}
    .avatar{width:56px;height:56px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#111827,#334155);color:#fff;font-weight:800;letter-spacing:.4px;flex-shrink:0}
    .title-row{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
    .title-lg{font-size:30px;line-height:1.1;font-weight:800;margin:0}
    .sub{color:var(--muted);font-size:13px;margin-top:4px}
    .status-pill{display:inline-flex;align-items:center;padding:4px 10px;border-radius:999px;border:1px solid #dbe3ef;background:#f8fbff;color:#1e3a8a;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.4px}

    .actions{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end}
    .btn-link{display:inline-flex;align-items:center;justify-content:center;padding:9px 12px;border:1px solid var(--border);border-radius:12px;background:#fff;color:#0f172a;text-decoration:none;font-size:13px;font-weight:700;transition:.18s ease;cursor:pointer}
    .btn-link:hover{transform:translateY(-1px);border-color:#cbd5e1;box-shadow:0 6px 16px rgba(15,23,42,.08)}
    .btn-link:focus-visible{outline:2px solid #cbd5e1;outline-offset:2px}
    .btn-brand{background:var(--brand);border-color:var(--brand);color:#fff}
    .btn-brand:hover{background:var(--brand-hover);border-color:var(--brand-hover)}

    .client-shell{display:grid;grid-template-columns:320px minmax(0,1fr) 340px;gap:20px;align-items:start;margin-top:16px}
    @media (max-width:1320px){.client-shell{grid-template-columns:300px minmax(0,1fr)}}
    @media (max-width:1120px){.client-shell{grid-template-columns:1fr}}

    .section-title{margin:0 0 14px;font-size:17px;font-weight:800}
    .group-title{font-size:11px;font-weight:800;letter-spacing:.8px;color:#64748b;text-transform:uppercase;margin:16px 0 8px}
    .key-list{display:grid;gap:8px}
    .key-row{display:grid;grid-template-columns:120px 1fr;gap:10px;padding:8px 0;border-bottom:1px dashed #edf1f5}
    .key-row:last-child{border-bottom:0}
    .key-label{font-size:12px;color:#64748b}
    .key-value{font-size:13px;font-weight:600;color:#111827;word-break:break-word}
    .key-empty{color:#94a3b8;font-weight:500}

    .tabs{display:flex;gap:8px;margin-bottom:18px;flex-wrap:wrap}
    .tab{display:inline-flex;padding:8px 14px;border:1px solid #dbe3ef;border-radius:999px;background:#fff;color:#334155;text-decoration:none;font-weight:700;font-size:13px;transition:.16s ease}
    .tab:hover{border-color:#c8d3e5;background:#f8fafc}
    .tab.active{background:#e8eefc;border-color:#b8c7e8;color:#1e3a8a}

    .kpi-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
    @media (max-width:900px){.kpi-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media (max-width:640px){.kpi-grid{grid-template-columns:1fr}}
    .kpi{min-height:140px;border:1px solid #e7ebf1;border-radius:16px;background:linear-gradient(180deg,#ffffff 0%,#fbfcfe 100%);padding:16px}
    .kpi-label{font-size:12px;color:#64748b;margin-bottom:10px;font-weight:600}
    .kpi-val{font-size:26px;font-weight:800;line-height:1.1;letter-spacing:-.01em}
    .kpi-sub{font-size:12px;color:#64748b;margin-top:12px}

    .hs-wrap{background:var(--surface-soft);border:1px solid #e8edf3;border-radius:16px;padding:14px}
    .hs-subtabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px}
    .hs-subtab{display:inline-flex;align-items:center;padding:7px 12px;border:1px solid #d6dfeb;border-radius:999px;background:#fff;color:#334155;text-decoration:none;font-weight:700;font-size:12px;transition:.16s ease}
    .hs-subtab:hover{background:#f8fafc}
    .hs-subtab.active{background:#ebf1ff;border-color:#b9c9ee;color:#1d4ed8}
    .hs-topbar{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:12px}
    .hs-search{position:relative;min-width:240px;flex:1 1 260px;max-width:440px}
    .hs-search .search-icon{position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#64748b;pointer-events:none}
    .hs-search .input{padding-left:34px;width:100%}
    .hs-right{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
    .hs-dropdown{position:relative}
    .hs-menu{position:absolute;right:0;top:calc(100% + 6px);min-width:170px;background:#fff;border:1px solid #d8e0ea;border-radius:10px;box-shadow:0 12px 20px rgba(15,23,42,.08);padding:6px;display:none;z-index:30}
    .hs-menu button{display:block;width:100%;text-align:left;border:0;background:transparent;padding:8px 10px;border-radius:8px;cursor:pointer;font-size:13px}
    .hs-menu button:hover{background:#f1f5f9}

    .hs-month{margin:18px 0 10px;font-size:12px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:.6px}
    .timeline{position:relative;padding-left:20px}
    .timeline:before{content:"";position:absolute;left:6px;top:4px;bottom:4px;width:2px;background:#e2e8f0;border-radius:2px}
    .activity-card{position:relative;background:#fff;border:1px solid #dbe5ef;border-radius:14px;margin-bottom:10px;transition:.16s ease}
    .activity-card:hover{border-color:#c9d7e8;box-shadow:0 8px 16px rgba(15,23,42,.05)}
    .activity-card:before{content:"";position:absolute;left:-17px;top:18px;width:10px;height:10px;border-radius:50%;background:#94a3b8;border:2px solid #fff;box-shadow:0 0 0 2px #dbe5ef}
    .activity-head{display:flex;align-items:center;gap:10px;padding:11px 12px}
    .activity-toggle{width:24px;height:24px;border:1px solid #cbd5e1;border-radius:8px;background:#fff;cursor:pointer;color:#475569;display:inline-flex;align-items:center;justify-content:center}
    .activity-toggle .chev{transition:transform .12s ease}
    .activity-toggle[aria-expanded="true"] .chev{transform:rotate(90deg)}
    .activity-title{font-weight:700;color:#0f172a;font-size:14px}
    .activity-meta{margin-left:auto;font-size:11px;color:#64748b;display:flex;gap:8px;align-items:center;flex-wrap:wrap}
    .type-badge{display:inline-flex;align-items:center;padding:2px 8px;border-radius:999px;border:1px solid #dbeafe;background:#eff6ff;color:#1d4ed8;font-weight:800;letter-spacing:.3px}
    .activity-body{padding:0 12px 12px 46px}
    .activity-body[hidden]{display:none}
    .meta-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;margin-top:8px}
    @media (max-width:640px){.meta-grid{grid-template-columns:1fr}}
    .meta-cell{font-size:13px}
    .meta-cell b{display:block;font-size:11px;color:#64748b;font-weight:700;margin-bottom:2px;text-transform:uppercase;letter-spacing:.3px}
    .status{display:inline-block;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:700;border:1px solid}
    .status.confirmed{background:#ecfdf5;color:#065f46;border-color:#a7f3d0}
    .status.draft{background:#f3f4f6;color:#374151;border-color:#e5e7eb}
    .status.pending_payment{background:#fff7ed;color:#92400e;border-color:#fed7aa}
    .status.canceled{background:#fef2f2;color:#991b1b;border-color:#fecaca}
    .status.paid{background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe}

    .panel-head{display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:12px}
    .panel-title{margin:0;font-size:16px;font-weight:800}
    .photos-grid{display:grid;grid-template-columns:1fr;gap:10px}
    .photo-item{display:grid;grid-template-columns:72px minmax(0,1fr) auto;gap:10px;align-items:center;border:1px solid #e6ebf2;border-radius:12px;padding:8px;background:#fff}
    .photo-thumb{width:72px;height:72px;border-radius:10px;object-fit:cover;background:#f3f4f6;border:1px solid var(--border)}
    .photo-name{font-size:13px;font-weight:700;line-height:1.25;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .photo-meta{font-size:11px;color:#64748b;margin-top:4px}
    .btn-danger-ghost{color:#b91c1c;border-color:#fecaca;background:#fff}
    .btn-danger-ghost:hover{background:#fef2f2;border-color:#fca5a5}

    .empty-state{padding:14px;border:1px dashed #d1d9e6;border-radius:12px;background:#f8fafc;color:#64748b;font-size:13px;text-align:center}

    .modal{position:fixed;inset:0;display:none;align-items:center;justify-content:center;padding:20px;z-index:50;background:rgba(15,23,42,.45)}
    .modal.show{display:flex}
    .modal-card{width:min(620px, 100%);background:#fff;border:1px solid var(--border);border-radius:16px;box-shadow:0 18px 50px rgba(15,23,42,.2)}
    .modal-head{display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-bottom:1px solid var(--border)}
    .modal-body{padding:16px}
    .modal-foot{display:flex;justify-content:flex-end;gap:8px;padding:0 16px 16px}
    .input,.select,textarea{width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:12px;background:#fff}
    .field{margin-bottom:12px}
    .field label{display:block;font-size:12px;font-weight:700;color:#475569;margin-bottom:6px}
  </style>
  @php
    $money = fn($v) => '$'.number_format((float)$v, 2);
    $fmtDate = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('M d, Y') : '-';
    $displayName = $client->full_name ?: ($client->company ?: 'Unnamed client');
    $statusText = ucfirst((string) ($client->status ?: 'regular'));
    $initials = collect(explode(' ', trim($displayName)))->filter()->take(2)->map(fn($part) => strtoupper(substr($part, 0, 1)))->implode('');
    if ($initials === '') { $initials = 'CL'; }
    $f = $activityFilters ?? ['search'=>''];
    $activityTab = $activityTab ?? 'ACTIVITY';
    $groupedActivities = $activities->getCollection()->groupBy(function($a){ return optional($a->occurred_at)->format('F Y') ?: 'Unknown'; });
    $photos = $client->photos ?? collect();
    $emptyMark = fn($v) => filled($v) ? $v : '—';
  @endphp
</head>
<body>
  <div class="dashboard-wrap">
    @if(session('ok'))
      <div class="card" style="margin-bottom:12px"><div class="card-body" style="color:#065f46;background:#ecfdf5;border-radius:14px">{{ session('ok') }}</div></div>
    @endif
    @if($errors->any())
      <div class="card" style="margin-bottom:12px"><div class="card-body" style="color:#7f1d1d;background:#fee2e2;border-radius:14px">{{ $errors->first() }}</div></div>
    @endif

    <div class="card">
      <div class="card-body hero">
        <div class="hero-id">
          <div class="avatar">{{ $initials }}</div>
          <div>
            <div class="title-row">
              <h1 class="title-lg">{{ $displayName }}</h1>
              <span class="status-pill">{{ $statusText }}</span>
            </div>
            <div class="sub">{{ $client->email_primary ?: 'No primary email' }}</div>
          </div>
        </div>
        <div class="actions">
          <a class="btn-link" href="{{ route('admin.clients') }}">Back</a>
          <a class="btn-link" href="{{ route('admin.clients.edit', ['id' => $client->id]) }}">Edit</a>
          <button type="button" class="btn-link" data-open-modal="noteModal">Add Note</button>
          <a class="btn-link btn-brand" href="{{ route('admin.staff_bookings.step1') }}">Create Event</a>
          <a class="btn-link" href="{{ route('admin.reservations', ['q' => $client->email_primary]) }}">Send Invoice</a>
        </div>
      </div>
    </div>

    <div class="client-shell">
      <aside>
        <div class="card">
          <div class="card-body">
            <h3 class="section-title">Key information</h3>

            <div class="group-title">Contact</div>
            <div class="key-list">
              <div class="key-row"><span class="key-label">Primary phone</span><span class="key-value {{ $client->phone_primary ? '' : 'key-empty' }}">{{ $emptyMark($client->phone_primary) }}</span></div>
              <div class="key-row"><span class="key-label">Alternate phone</span><span class="key-value {{ $client->phone_alt ? '' : 'key-empty' }}">{{ $emptyMark($client->phone_alt) }}</span></div>
              <div class="key-row"><span class="key-label">Primary email</span><span class="key-value {{ $client->email_primary ? '' : 'key-empty' }}">{{ $emptyMark($client->email_primary) }}</span></div>
              <div class="key-row"><span class="key-label">Alternate email</span><span class="key-value {{ $client->email_alt ? '' : 'key-empty' }}">{{ $emptyMark($client->email_alt) }}</span></div>
            </div>

            <div class="group-title">Address</div>
            <div class="key-list">
              <div class="key-row"><span class="key-label">Street</span><span class="key-value {{ $client->address1_street ? '' : 'key-empty' }}">{{ $emptyMark($client->address1_street) }}</span></div>
              <div class="key-row"><span class="key-label">City / State / ZIP</span><span class="key-value {{ trim(collect([$client->address1_city, $client->address1_state, $client->address1_zip])->filter()->join(', ')) ? '' : 'key-empty' }}">{{ $emptyMark(trim(collect([$client->address1_city, $client->address1_state, $client->address1_zip])->filter()->join(', '))) }}</span></div>
            </div>

            <div class="group-title">Meta</div>
            <div class="key-list">
              <div class="key-row"><span class="key-label">Referral source</span><span class="key-value {{ $client->referral_source ? '' : 'key-empty' }}">{{ $emptyMark($client->referral_source) }}</span></div>
              <div class="key-row"><span class="key-label">Status</span><span class="key-value">{{ $statusText }}</span></div>
            </div>
          </div>
        </div>
      </aside>

      <main>
        <div class="card">
          <div class="card-body">
            <div class="tabs">
              <a class="tab {{ $tab === 'overview' ? 'active' : '' }}" href="{{ route('admin.clients.show', ['id'=>$client->id, 'tab'=>'overview']) }}">Overview</a>
              <a class="tab {{ $tab === 'activities' ? 'active' : '' }}" href="{{ route('admin.clients.show', ['id'=>$client->id, 'tab'=>'activities', 'activity_tab'=>$activityTab, 'search'=>$f['search'] ?? '']) }}">Activities</a>
            </div>

            @if($tab === 'overview')
              <div class="kpi-grid">
                <div class="kpi">
                  <div class="kpi-label">Total Events</div>
                  <div class="kpi-val">{{ $overview['total_events'] }}</div>
                  <div class="kpi-sub">Total events booked: {{ $overview['total_events_booked'] }}</div>
                </div>
                <div class="kpi">
                  <div class="kpi-label">Total Spent</div>
                  <div class="kpi-val">{{ $money($overview['total_spent']) }}</div>
                  <div class="kpi-sub">No. of cancelled events: {{ $overview['cancelled_events'] }}</div>
                </div>
                <div class="kpi">
                  <div class="kpi-label">Outstanding Balance</div>
                  <div class="kpi-val">{{ $money($overview['outstanding_balance']) }}</div>
                  <div class="kpi-sub">Unpaid balances &gt; $0</div>
                </div>
                <div class="kpi">
                  <div class="kpi-label">Last Event Date</div>
                  <div class="kpi-val" style="font-size:22px">{{ $fmtDate($overview['last_event_at']) }}</div>
                  <div class="kpi-sub">Days since last event: {{ $overview['days_since_last_event'] ?? '—' }}</div>
                </div>
                <div class="kpi">
                  <div class="kpi-label">Next Event Date</div>
                  <div class="kpi-val" style="font-size:22px">{{ $fmtDate($overview['next_event_at']) }}</div>
                  <div class="kpi-sub">Future events only</div>
                </div>
                <div class="kpi">
                  <div class="kpi-label">Events Not Booked Since Becoming Client</div>
                  <div class="kpi-val" style="font-size:22px">{{ $overview['days_since_client'] ?? '—' }} days</div>
                  <div class="kpi-sub">Client since: {{ $fmtDate($overview['client_since']) }}</div>
                </div>
              </div>
            @else
              <section class="hs-wrap">
                <div class="hs-subtabs">
                  <a class="hs-subtab {{ $activityTab==='ACTIVITY'?'active':'' }}" href="{{ route('admin.clients.show', ['id'=>$client->id,'tab'=>'activities','activity_tab'=>'ACTIVITY','search'=>$f['search'] ?? '']) }}">Activity</a>
                  <a class="hs-subtab {{ $activityTab==='NOTES'?'active':'' }}" href="{{ route('admin.clients.show', ['id'=>$client->id,'tab'=>'activities','activity_tab'=>'NOTES','search'=>$f['search'] ?? '']) }}">Notes ({{ $activityCounts['notes'] ?? 0 }})</a>
                  <a class="hs-subtab {{ $activityTab==='TASKS'?'active':'' }}" href="{{ route('admin.clients.show', ['id'=>$client->id,'tab'=>'activities','activity_tab'=>'TASKS','search'=>$f['search'] ?? '']) }}">Tasks ({{ $activityCounts['tasks'] ?? 0 }})</a>
                  <a class="hs-subtab {{ $activityTab==='EVENTS'?'active':'' }}" href="{{ route('admin.clients.show', ['id'=>$client->id,'tab'=>'activities','activity_tab'=>'EVENTS','search'=>$f['search'] ?? '']) }}">Events History ({{ $activityCounts['events'] ?? 0 }})</a>
                </div>

                <form method="get">
                  <input type="hidden" name="tab" value="activities">
                  <input type="hidden" name="activity_tab" value="{{ $activityTab }}">
                  <div class="hs-topbar">
                    <div class="hs-search">
                      <span class="search-icon">&#128269;</span>
                      <input class="input" name="search" value="{{ $f['search'] ?? '' }}" placeholder="Search activities">
                    </div>
                    <div class="hs-right">
                      <button type="button" class="btn-link btn-brand" data-open-modal="noteModal">Create Note</button>
                      @if($activityTab === 'TASKS')
                        <button type="button" class="btn-link" data-open-modal="taskModal">Create Task</button>
                      @endif
                      <div class="hs-dropdown">
                        <button type="button" class="btn-link" id="collapseAllBtn">Collapse all v</button>
                        <div class="hs-menu" id="collapseAllMenu">
                          <button type="button" data-collapse-mode="collapse">Collapse all</button>
                          <button type="button" data-collapse-mode="expand">Expand all</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </form>

                <div>
                  @php $cardIndex = 0; @endphp
                  @forelse($groupedActivities as $month => $monthItems)
                    <h4 class="hs-month">{{ $month }}</h4>
                    <div class="timeline">
                      @foreach($monthItems as $item)
                        @php
                          $cardId = $item->feed_key;
                          $isFirstCard = $cardIndex === 0;
                          $cardIndex++;
                        @endphp
                        <article class="activity-card">
                          <div class="activity-head">
                            <button type="button" class="activity-toggle" data-toggle-target="{{ $cardId }}" aria-expanded="{{ $isFirstCard ? 'true' : 'false' }}">
                              <span class="chev">&gt;</span>
                            </button>
                            <div>
                              <div class="activity-title">{{ $item->title }}</div>
                              <div class="sub">{{ optional($item->occurred_at)->format('M d, Y g:i A') }}</div>
                            </div>
                            <div class="activity-meta">
                              <span class="type-badge">{{ $item->type }}</span>
                              @if($item->status)
                                <span class="status {{ $item->status_key }}">{{ $item->status }}</span>
                              @endif
                            </div>
                          </div>

                          <div class="activity-body" id="{{ $cardId }}" @if(!$isFirstCard) hidden @endif>
                            @if(!empty($item->body))
                              <div style="white-space:pre-wrap;font-size:14px;margin-bottom:8px">{{ $item->body }}</div>
                            @endif

                            @if($item->type === 'TASK')
                              <div class="meta-grid">
                                <div class="meta-cell"><b>Due</b>{{ $item->due_at ? \Carbon\Carbon::parse($item->due_at)->format('M d, Y g:i A') : '—' }}</div>
                                <div class="meta-cell"><b>Assigned to</b>{{ $item->assigned_to_name ?: 'Unassigned' }}</div>
                                <div class="meta-cell"><b>Created by</b>{{ $item->created_by_name ?: 'System' }}</div>
                              </div>
                            @endif

                            @if($item->type === 'EVENT')
                              <div class="meta-grid">
                                <div class="meta-cell"><b>Guests</b>{{ $item->guests ?? '—' }}</div>
                                <div class="meta-cell"><b>Total</b>{{ $money($item->total ?? 0) }}</div>
                                <div class="meta-cell"><b>Paid</b>{{ $money($item->paid ?? 0) }}</div>
                                <div class="meta-cell"><b>Balance</b>{{ $money($item->balance ?? 0) }}</div>
                                <div class="meta-cell"><b>Invoice #</b>{{ $item->invoice_number ?: '—' }}</div>
                                <div class="meta-cell"><b>Event code</b>{{ $item->event_code ?: '—' }}</div>
                              </div>
                              <div class="row" style="margin-top:10px">
                                <a class="btn-link" href="{{ route('admin.reservations.show', ['id' => $item->event_id]) }}">View reservation</a>
                                @if($item->invoice_number)
                                  <a class="btn-link" href="{{ route('admin.reservations.invoice', ['id' => $item->event_id]) }}">View invoice</a>
                                @endif
                              </div>
                            @endif
                          </div>
                        </article>
                      @endforeach
                    </div>
                  @empty
                    <div class="empty-state">No activities found for current filters.</div>
                  @endforelse
                </div>

                <div style="margin-top:12px">{{ $activities->links() }}</div>
              </section>
            @endif
          </div>
        </div>
      </main>

      <aside>
        <div class="card" style="margin-bottom:14px">
          <div class="card-body">
            <div class="panel-head">
              <h3 class="panel-title">Photos</h3>
              <form id="uploadPhotosForm" method="post" action="{{ route('admin.clients.photos.store', ['client' => $client->id]) }}" enctype="multipart/form-data">
                @csrf
                <input id="photosInput" type="file" name="photos[]" accept=".jpg,.jpeg,.png,.webp,.heic" multiple style="display:none">
                <button type="button" class="btn-link btn-brand" id="uploadPhotosBtn">Upload Photos</button>
              </form>
            </div>

            @if($photos->isEmpty())
              <div class="empty-state">No photos yet.</div>
            @else
              <div class="photos-grid">
                @foreach($photos as $photo)
                  <div class="photo-item">
                    <a href="{{ asset('storage/'.$photo->path) }}" target="_blank" rel="noopener">
                      <img class="photo-thumb" src="{{ asset('storage/'.$photo->path) }}" alt="{{ $photo->original_name ?: 'Client photo' }}">
                    </a>
                    <div style="min-width:0">
                      <div class="photo-name">{{ $photo->original_name ?: basename((string) $photo->path) }}</div>
                      <div class="photo-meta">{{ $photo->created_at?->format('M d, Y g:i A') ?: '—' }}</div>
                    </div>
                    <form method="post" action="{{ route('admin.clients.photos.destroy', ['client' => $client->id, 'photo' => $photo->id]) }}" onsubmit="return confirm('Delete this photo?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn-link btn-danger-ghost">Delete</button>
                    </form>
                  </div>
                @endforeach
              </div>
            @endif
          </div>
        </div>

        <div class="card" id="notes">
          <div class="card-body">
            <div class="panel-head" style="margin-bottom:8px">
              <h3 class="panel-title">Notes</h3>
            </div>
            @if(!empty($client->internal_notes))
              <div style="white-space:pre-wrap;font-size:14px;line-height:1.6;color:#1f2937">{{ $client->internal_notes }}</div>
            @else
              <div class="empty-state">No notes yet.</div>
            @endif
          </div>
        </div>
      </aside>
    </div>
  </div>

  <div class="modal" id="noteModal" aria-hidden="true">
    <div class="modal-card">
      <div class="modal-head">
        <strong>Note</strong>
        <button type="button" class="btn-link" data-close-modal>&times;</button>
      </div>
      <form method="post" action="{{ route('admin.clients.notes.store', ['id' => $client->id]) }}">
        @csrf
        <div class="modal-body">
          <div class="field">
            <label>For {{ $displayName }}</label>
            <div class="sub">{{ $client->email_primary ?: 'No email' }}</div>
          </div>
          <div class="field">
            <label>Note</label>
            <textarea name="body" rows="7" placeholder="Start typing to leave a note..." required>{{ old('body') }}</textarea>
          </div>
          <label class="row" style="font-size:13px">
            <input type="checkbox" name="create_followup" value="1">
            Create a To-do task to follow up in X business days
          </label>
        </div>
        <div class="modal-foot">
          <button type="button" class="btn-link" data-close-modal>Cancel</button>
          <button type="submit" class="btn-link btn-brand">Create note</button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal" id="taskModal" aria-hidden="true">
    <div class="modal-card">
      <div class="modal-head">
        <strong>Create Task</strong>
        <button type="button" class="btn-link" data-close-modal>&times;</button>
      </div>
      <form method="post" action="{{ route('admin.clients.tasks.store', ['id' => $client->id]) }}">
        @csrf
        <div class="modal-body">
          <div class="field">
            <label>Title</label>
            <input class="input" name="title" value="{{ old('title') }}" required>
          </div>
          <div class="field">
            <label>Due date</label>
            <input class="input" type="datetime-local" name="due_at" value="{{ old('due_at') }}">
          </div>
          <div class="field">
            <label>Assigned to</label>
            <select class="select" name="assigned_to">
              <option value="">Unassigned</option>
              @foreach(($activityUsers ?? []) as $u)
                <option value="{{ $u->id }}" {{ (string)old('assigned_to') === (string)$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="field">
            <label>Description</label>
            <textarea name="description" rows="5">{{ old('description') }}</textarea>
          </div>
        </div>
        <div class="modal-foot">
          <button type="button" class="btn-link" data-close-modal>Cancel</button>
          <button type="submit" class="btn-link btn-brand">Create task</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    (function () {
      var collapseBtn = document.getElementById('collapseAllBtn');
      var collapseMenu = document.getElementById('collapseAllMenu');
      if (collapseBtn && collapseMenu) {
        collapseBtn.addEventListener('click', function () {
          collapseMenu.style.display = collapseMenu.style.display === 'block' ? 'none' : 'block';
        });
        document.addEventListener('click', function (e) {
          if (!collapseMenu.contains(e.target) && !collapseBtn.contains(e.target)) {
            collapseMenu.style.display = 'none';
          }
        });
        collapseMenu.querySelectorAll('[data-collapse-mode]').forEach(function (btn) {
          btn.addEventListener('click', function () {
            var expand = btn.getAttribute('data-collapse-mode') === 'expand';
            document.querySelectorAll('.activity-toggle').forEach(function (toggle) {
              var id = toggle.getAttribute('data-toggle-target');
              var body = document.getElementById(id);
              if (!body) return;
              toggle.setAttribute('aria-expanded', expand ? 'true' : 'false');
              if (expand) body.removeAttribute('hidden');
              else body.setAttribute('hidden', 'hidden');
            });
            collapseMenu.style.display = 'none';
          });
        });
      }

      document.querySelectorAll('.activity-toggle').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
          var id = toggle.getAttribute('data-toggle-target');
          var body = document.getElementById(id);
          if (!body) return;
          var expanded = toggle.getAttribute('aria-expanded') === 'true';
          toggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
          if (expanded) body.setAttribute('hidden', 'hidden');
          else body.removeAttribute('hidden');
        });
      });

      var uploadBtn = document.getElementById('uploadPhotosBtn');
      var uploadInput = document.getElementById('photosInput');
      var uploadForm = document.getElementById('uploadPhotosForm');
      if (uploadBtn && uploadInput && uploadForm) {
        uploadBtn.addEventListener('click', function () {
          uploadInput.click();
        });
        uploadInput.addEventListener('change', function () {
          if (uploadInput.files && uploadInput.files.length > 0) {
            uploadForm.submit();
          }
        });
      }

      function closeModal(el) {
        el.classList.remove('show');
        el.setAttribute('aria-hidden', 'true');
      }
      function openModal(el) {
        el.classList.add('show');
        el.setAttribute('aria-hidden', 'false');
      }

      document.querySelectorAll('[data-open-modal]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var id = btn.getAttribute('data-open-modal');
          var modal = document.getElementById(id);
          if (modal) openModal(modal);
        });
      });

      document.querySelectorAll('.modal').forEach(function (modal) {
        modal.addEventListener('click', function (e) {
          if (e.target === modal) closeModal(modal);
        });
      });
      document.querySelectorAll('[data-close-modal]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var modal = btn.closest('.modal');
          if (modal) closeModal(modal);
        });
      });
    })();
  </script>
</body>
</html>
