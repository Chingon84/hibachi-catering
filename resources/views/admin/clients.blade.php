<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin – Clients</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    :root{--brand:#b21e27;--brand-hover:#9a1a22}
    .title{font-size:22px;margin:0}
    .container{
      width:calc(100vw - 24px);
      max-width:none;
      margin:20px 12px;
      padding:0 12px;
    }
    .card{background:var(--card);border:1px solid var(--border);border-radius:14px;box-shadow:0 6px 18px rgba(0,0,0,.04)}
    .card-body{padding:16px}
    .btn{appearance:none;border:0;background:var(--brand);color:#fff;border-radius:10px;padding:10px 14px;cursor:pointer;font-weight:600;text-decoration:none;display:inline-block}
    .btn:hover{background:var(--brand-hover)}
    .btn.secondary{background:#4b5563}
    .btn.secondary:hover{background:#374151}
    .input,.select{padding:10px 12px;border:1px solid var(--border);border-radius:10px;background:#fff;min-height:40px}
    .toolbar{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
    .table-wrap{overflow-x:auto;border:1px solid #eceff3;border-radius:12px}
    .table{width:100%;border-collapse:separate;border-spacing:0;table-layout:fixed}
    .table th,.table td{padding:8px 10px;text-align:left;font-size:13px;vertical-align:middle}
    .table thead th{position:sticky;top:0;background:#f3f4f6;color:#1f2937;border-bottom:1px solid var(--border);font-weight:700;white-space:nowrap;z-index:1}
    .table tbody tr{background:#fff;transition:background .15s ease}
    .table tbody tr:nth-child(even){background:#fafafa}
    .table tbody tr:hover{background:#f5f7fa}
    .table tbody tr + tr td{border-top:1px solid var(--border)}
    .muted{color:var(--muted);font-size:13px}
    .count-badge{display:inline-flex;align-items:center;justify-content:center;min-width:32px;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:700;background:#eef2ff;color:#1e40af;border:1px solid #c7d2fe}
    .num{text-align:center;white-space:nowrap}
    .actions a{color:#b21e27;text-decoration:none}
    .actions{white-space:nowrap}
    .actions a{font-weight:600;font-size:13px}
    .actions a + a{margin-left:10px}
    .actions a:hover{text-decoration:underline}
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
      .container{padding:0 12px}
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

    <div class="card">
      <div class="card-body">
        <form method="get" class="toolbar" action="{{ route('admin.clients') }}">
          <input class="input" id="q" name="q" placeholder="Search name, company, email, phone" value="{{ $q }}" style="min-width:260px">
          <select name="city" class="select" style="min-width:160px">
            <option value="">All cities</option>
            @foreach(($cityOptions ?? []) as $copt)
              <option value="{{ $copt }}" {{ ($city ?? '')===$copt ? 'selected' : '' }}>{{ $copt }}</option>
            @endforeach
          </select>
          <select name="status" class="select client-status {{ $status ? strtolower($status) : '' }}">
            <option value="">All statuses</option>
            @php $opts = $statusOptions ?? ['regular','vip','celebrity','blacklist','preferred']; @endphp
            @foreach($opts as $opt)
              <option value="{{ $opt }}" {{ $status===$opt?'selected':'' }}>{{ ucfirst($opt) }}</option>
            @endforeach
          </select>
          <input
            class="input"
            type="number"
            min="1"
            max="50"
            name="events"
            value="{{ $events ?? '' }}"
            placeholder="Events"
            style="width:120px"
          >
          <button class="btn secondary" type="submit">Filter</button>
          <a class="btn secondary" href="{{ route('admin.clients') }}">Reset</a>
          <div style="margin-left:auto;display:flex;gap:8px">
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
                <tr>
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
                    <form method="post" action="{{ route('admin.clients.status', ['id'=>$c->id]) }}" style="display:flex;align-items:center">
                      @csrf
                      @php $cur = strtolower($c->status); @endphp
                      <select name="status" class="select client-status {{ $cur }}" onchange="this.form.submit()" style="min-width:150px">
                        @foreach($opts as $opt)
                          <option value="{{ $opt }}" {{ strtolower($c->status)===strtolower($opt)?'selected':'' }}>{{ ucfirst($opt) }}</option>
                        @endforeach
                      </select>
                    </form>
                  </td>
                  <td class="actions">
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

        <div style="margin-top:12px">{{ $list->links() }}</div>
      </div>
    </div>
  </div>
</body>
</html>
