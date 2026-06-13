<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin – Clients</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    :root{--brand:#243b53;--brand-hover:#172b40;--brand-soft:#eef5fb;--brand-ring:#c7d6e8}
    .title{font-size:22px;margin:0}
    .container{
      width:100%;
      max-width:none;
      margin:0;
      padding:20px 24px;
    }
    .card{background:var(--card);border:1px solid var(--border);border-radius:14px;box-shadow:0 6px 18px rgba(0,0,0,.04)}
    .card-body{padding:16px}
    .btn{appearance:none;border:1px solid #243b53;background:linear-gradient(180deg,#2f4863,#243b53);color:#fff;border-radius:12px;padding:10px 14px;cursor:pointer;font-weight:750;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;box-shadow:0 8px 18px rgba(36,59,83,.18);transition:transform .16s ease, box-shadow .16s ease, border-color .16s ease, background .16s ease}
    .btn:hover{background:linear-gradient(180deg,#284059,#172b40);border-color:#172b40;transform:translateY(-1px);box-shadow:0 12px 24px rgba(36,59,83,.22)}
    .btn.secondary{background:linear-gradient(180deg,#fff,#f8fafc);border-color:#d9e1ec;color:#1f2937;box-shadow:0 1px 2px rgba(15,23,42,.04)}
    .btn.secondary:hover{background:#fff;border-color:#c5d0dd;box-shadow:0 8px 18px rgba(15,23,42,.08)}
    .input,.select{padding:10px 12px;border:1px solid var(--border);border-radius:10px;background:#fff;min-height:40px}
    .toolbar{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
    .metrics-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
    .metric-card{background:linear-gradient(180deg,#fff,#f8fafc);border:1px solid #e5e7eb;border-radius:14px;padding:14px 16px;box-shadow:0 10px 24px rgba(15,23,42,.04)}
    .metric-label{font-size:11px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#64748b}
    .metric-value{margin-top:8px;font-size:26px;font-weight:800;color:#0f172a;line-height:1}
    .metric-copy{margin-top:6px;font-size:12px;line-height:1.45;color:#94a3b8}
    .clients-filter-bar{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
    .clients-filter-bar .filter-control{flex:0 0 auto;min-width:0}
    .clients-filter-bar .filter-search{width:min(380px,100%)}
    .clients-filter-bar .filter-city{width:200px}
    .clients-filter-bar .filter-status{width:180px}
    .clients-filter-bar .filter-events{width:96px}
    .clients-filter-bar .filter-actions{display:flex;align-items:center;gap:8px;flex:0 0 auto}
    .clients-filter-bar .toolbar-tools{display:flex;align-items:center;gap:8px;margin-left:auto;flex:0 0 auto}
    .clients-filter-bar .btn{min-height:38px;padding:8px 12px}
    .table-wrap{overflow-x:auto;border:1px solid #eceff3;border-radius:12px}
    .table{width:100%;border-collapse:separate;border-spacing:0;table-layout:fixed}
    .table th,.table td{padding:8px 10px;text-align:left;font-size:13px;vertical-align:middle}
    .table thead th{position:sticky;top:0;background:#f3f4f6;color:#1f2937;border-bottom:1px solid var(--border);font-weight:700;white-space:nowrap;z-index:1}
    .table tbody tr{background:#fff;transition:background .15s ease}
    .table tbody tr:nth-child(even){background:#fafafa}
    .table tbody tr:hover{background:#f5f7fa}
    .table tbody tr.client-row{cursor:pointer}
    .table tbody tr.client-row:hover{background:#f6f9fc}
    .table tbody tr.client-row.is-clicking{background:#eef5fb}
    .table tbody tr.client-row:focus{outline:2px solid rgba(36,59,83,.24);outline-offset:-2px}
    .table tbody tr + tr td{border-top:1px solid var(--border)}
    .muted{color:var(--muted);font-size:13px}
    .count-badge{display:inline-flex;align-items:center;justify-content:center;min-width:32px;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:700;background:#eef2ff;color:#1e40af;border:1px solid #c7d2fe}
    .num{text-align:center;white-space:nowrap}
    .actions a{display:inline-flex;align-items:center;justify-content:center;min-height:30px;padding:5px 10px;border:1px solid #d9e1ec;border-radius:10px;background:#fff;color:#243b53;text-decoration:none;transition:background .16s ease, border-color .16s ease, box-shadow .16s ease, transform .16s ease}
    .actions{white-space:nowrap}
    .actions a{font-weight:700;font-size:12px}
    .actions a + a{margin-left:6px}
    .actions a:hover{background:#f8fafc;border-color:#c5d0dd;box-shadow:0 6px 14px rgba(15,23,42,.08);transform:translateY(-1px)}
    .toolbar-tools .icon-btn:hover{box-shadow:0 6px 14px rgba(15,23,42,.10)}
    .alert{border-radius:10px;padding:10px 12px;font-size:14px}
    .alert.success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
    .alert.error{background:#fee2e2;color:#7f1d1d;border:1px solid #fecaca}
    @media (min-width:1200px){
      .table-wrap{overflow-x:visible}
      .table th:nth-child(1), .table td:nth-child(1){width:64px}
      .table th:nth-child(2), .table td:nth-child(2){width:190px}
      .table th:nth-child(3), .table td:nth-child(3){width:180px}
      .table th:nth-child(4), .table td:nth-child(4){width:130px}
      .table th:nth-child(5), .table td:nth-child(5){width:110px}
      .table th:nth-child(6), .table td:nth-child(6){width:84px}
      .table th:nth-child(7), .table td:nth-child(7){width:84px}
      .table th:nth-child(8), .table td:nth-child(8){width:220px}
      .table th:nth-child(9), .table td:nth-child(9){width:150px}
      .table th:nth-child(10), .table td:nth-child(10){width:170px}
      .table th:nth-child(11), .table td:nth-child(11){width:120px}
    }
    @media (max-width:900px){
      .container{padding:16px}
      .clients-filter-bar .toolbar-tools{margin-left:0}
      .metrics-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
    }
    @media (max-width:640px){
      .metrics-grid{grid-template-columns:1fr}
    }
  </style>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const q = document.querySelector('#q');
      if (q) {
        q.addEventListener('keydown', (e) => { if (e.key === 'Enter') q.form.submit(); });
      }
    });
  </script>
  @php
    $status = $status ?? null;
    $q = $q ?? '';
  @endphp
</head>
<body>
  <div class="container">
    <div class="header">
      <a class="btn" href="{{ route('admin.clients.create') }}">New Client</a>
    </div>

    @if (session('ok'))
    <div class="card" style="margin-bottom:12px"><div class="card-body"><div class="alert success">{{ session('ok') }}</div></div></div>
    @endif

    <div class="metrics-grid" style="margin-bottom:12px">
      <div class="metric-card">
        <div class="metric-label">Total Clients</div>
        <div class="metric-value">{{ number_format($totalClients ?? 0) }}</div>
        <div class="metric-copy">All client records in the CRM.</div>
      </div>
      <div class="metric-card">
        <div class="metric-label">Active Clients</div>
        <div class="metric-value">{{ number_format($activeClients ?? 0) }}</div>
        <div class="metric-copy">Regular, VIP, celebrity, and preferred clients.</div>
      </div>
      <div class="metric-card">
        <div class="metric-label">Inactive Clients</div>
        <div class="metric-value">{{ number_format($inactiveClients ?? 0) }}</div>
        <div class="metric-copy">Clients not currently considered active.</div>
      </div>
      <div class="metric-card">
        <div class="metric-label">Total Events</div>
        <div class="metric-value">{{ number_format($totalEvents ?? 0) }}</div>
        <div class="metric-copy">Sum of recorded client event counts.</div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <form method="get" class="clients-filter-bar" action="{{ route('admin.clients') }}">
          <input class="input filter-control filter-search" id="q" name="q" placeholder="Search name, company, email, phone" value="{{ $q }}">
          <select name="city" class="select filter-control filter-city">
            <option value="">All cities</option>
            @foreach(($cityOptions ?? []) as $copt)
              <option value="{{ $copt }}" {{ ($city ?? '')===$copt ? 'selected' : '' }}>{{ $copt }}</option>
            @endforeach
          </select>
          <select name="status" class="select client-status {{ $status ? strtolower($status) : '' }} filter-control filter-status">
            <option value="">All statuses</option>
            @php $opts = $statusOptions ?? ['regular','vip','celebrity','blacklist','preferred']; @endphp
            @foreach($opts as $opt)
              <option value="{{ $opt }}" {{ $status===$opt?'selected':'' }}>{{ ucfirst($opt) }}</option>
            @endforeach
          </select>
          <input
            class="input filter-control filter-events"
            type="number"
            min="1"
            max="50"
            name="events"
            value="{{ $events ?? '' }}"
            placeholder="Events"
          >
          <div class="filter-actions">
            <button class="btn secondary" type="submit">Filter</button>
            <a class="btn secondary" href="{{ route('admin.clients') }}">Reset</a>
          </div>
          <div class="toolbar-tools">
            <a href="{{ route('admin.clients.template') }}" class="icon-btn" title="Download CSV template" aria-label="Download CSV template">T</a>
            <a href="{{ route('admin.clients.export', request()->query()) }}" class="icon-btn" title="Export CSV" aria-label="Export CSV">⇩</a>
            <form method="post" action="{{ route('admin.clients.import') }}" enctype="multipart/form-data" id="importForm" style="display:inline">
              @csrf
              <input type="file" name="file" id="importFile" accept=".csv" style="display:none" onchange="if(this.files.length){document.getElementById('importForm').submit();}">
              <button type="button" class="icon-btn" title="Import CSV" aria-label="Import CSV" onclick="document.getElementById('importFile').click()">⇧</button>
            </form>
          </div>
        </form>
      </div>
    </div>

    <div class="card" style="margin-top:12px">
      <div class="card-body">
        <div class="table-wrap">
          <table class="table" aria-label="Clients list">
            <thead>
              <tr>
                <th style="width:70px">ID</th>
                <th>Name</th>
                <th>Company</th>
                <th>City</th>
                <th>Date</th>
                <th class="num">EVENTS</th>
                <th class="num">Guests</th>
                <th>Email</th>
                <th>Phone</th>
                <th style="width:170px">Status</th>
                <th style="width:130px">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($list as $c)
                <tr class="client-row" data-href="{{ route('admin.clients.show', ['id'=>$c->id]) }}" tabindex="0" aria-label="Open client {{ $c->full_name ?: $c->id }}">
                  <td class="muted">#{{ $c->id }}</td>
                  <td style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $c->full_name ?: '—' }}</td>
                  <td style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $c->company ?: '—' }}</td>
                  <td style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $c->address1_city ?? $c->address2_city ?? '—' }}</td>
                  <td class="muted">
                    @php $ld = $c->last_event_date ?? null; @endphp
                    {{ $ld ? \Carbon\Carbon::parse($ld)->format('m/d/Y') : '—' }}
                  </td>
                  <td class="num"><span class="count-badge">{{ (int) ($c->events_count ?? 0) }}</span></td>
                  <td class="num">{{ !is_null($c->last_guests) ? (int) $c->last_guests : '—' }}</td>
                  <td style="max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $c->email_primary ?: '—' }}</td>
                  <td style="white-space:nowrap">{{ $c->phone_primary ?: '—' }}</td>
                  <td>
                    <form method="post" action="{{ route('admin.clients.status', ['id'=>$c->id]) }}" style="display:flex;align-items:center" data-row-action-ignore>
                      @csrf
                      @php $cur = strtolower($c->status); @endphp
                      <select name="status" class="select client-status {{ $cur }}" onchange="this.form.submit()" style="min-width:150px">
                        @foreach($opts as $opt)
                          <option value="{{ $opt }}" {{ strtolower($c->status)===strtolower($opt)?'selected':'' }}>{{ ucfirst($opt) }}</option>
                        @endforeach
                      </select>
                    </form>
                  </td>
                  <td class="actions" data-row-action-ignore>
                    <a href="{{ route('admin.clients.show', ['id'=>$c->id]) }}">Open</a>
                    <a href="{{ route('admin.clients.edit', ['id'=>$c->id]) }}">Edit</a>
                  </td>
                </tr>
              @empty
                <tr><td colspan="11" class="muted">No clients found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @include('admin.partials.pagination', ['paginator' => $list])
      </div>
    </div>
  </div>
  <script>
    const clientRowActionSelector = 'a,button,input,select,textarea,form,[data-row-action-ignore]';

    document.querySelectorAll('.client-row[data-href]').forEach(row => {
      row.addEventListener('mousedown', event => {
        if (event.target.closest(clientRowActionSelector)) return;
        row.classList.add('is-clicking');
      });

      row.addEventListener('mouseup', () => row.classList.remove('is-clicking'));
      row.addEventListener('mouseleave', () => row.classList.remove('is-clicking'));

      row.addEventListener('click', event => {
        if (event.target.closest(clientRowActionSelector)) return;
        window.location.href = row.dataset.href;
      });

      row.addEventListener('keydown', event => {
        if (!['Enter', ' '].includes(event.key)) return;
        if (event.target.closest(clientRowActionSelector)) return;
        event.preventDefault();
        row.classList.add('is-clicking');
        window.location.href = row.dataset.href;
      });
    });

    document.querySelectorAll(clientRowActionSelector).forEach(el => {
      el.addEventListener('click', event => event.stopPropagation());
    });
  </script>
</body>
</html>
