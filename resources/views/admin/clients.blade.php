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
    .card{background:var(--card);border:1px solid var(--border);border-radius:14px;box-shadow:0 6px 18px rgba(0,0,0,.04)}
    .card-body{padding:16px}
    .btn{appearance:none;border:0;background:var(--brand);color:#fff;border-radius:10px;padding:10px 14px;cursor:pointer;font-weight:600;text-decoration:none;display:inline-block}
    .btn:hover{background:var(--brand-hover)}
    .btn.secondary{background:#4b5563}
    .btn.secondary:hover{background:#374151}
    .input,.select{padding:10px 12px;border:1px solid var(--border);border-radius:10px;background:#fff}
    .toolbar{display:flex;gap:10px;align-items:center}
    .table{width:100%;border-collapse:separate;border-spacing:0}
    .table th,.table td{padding:10px 12px;text-align:left;font-size:14px}
    .table thead th{background:#f3f4f6;color:#374151;border-bottom:1px solid var(--border);font-weight:700}
    .table tbody tr{background:#fff}
    .table tbody tr + tr td{border-top:1px solid var(--border)}
    .muted{color:var(--muted);font-size:13px}
    .status{display:inline-block;border-radius:999px;padding:4px 8px;font-size:12px;font-weight:700}
    .status.active{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
    .status.inactive{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb}
    .status.blocked{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
    .actions a{color:#b21e27;text-decoration:none}
    .actions a:hover{text-decoration:underline}
    .alert{border-radius:10px;padding:10px 12px;font-size:14px}
    .alert.success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
    .alert.error{background:#fee2e2;color:#7f1d1d;border:1px solid #fecaca}
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
          <button class="btn secondary" type="submit">Filter</button>
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
        <table class="table" aria-label="Clients list">
          <thead>
            <tr>
              <th style="width:70px">ID</th>
              <th>Name</th>
              <th>Company</th>
              <th>City</th>
              <th>Date</th>
              <th>Guests</th>
              <th>Email</th>
              <th>Phone</th>
              <th style="width:180px">Status</th>
              <th style="width:140px">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($list as $c)
              <tr>
                <td>{{ $c->id }}</td>
                <td>
                  <div style="font-weight:600">{{ $c->full_name ?: '—' }}</div>
                </td>
                <td>{{ $c->company }}</td>
                <td>{{ $c->address1_city ?? $c->address2_city ?? '—' }}</td>
                <td>
                  @php $ld = $c->last_event_date ?? null; @endphp
                  {{ $ld ? \Carbon\Carbon::parse($ld)->format('m/d/Y') : '—' }}
                </td>
                <td>{{ $c->last_guests ?? '—' }}</td>
                <td>{{ $c->email_primary }}</td>
                <td>{{ $c->phone_primary }}</td>
                <td>
                  <form method="post" action="{{ route('admin.clients.status', ['id'=>$c->id]) }}" style="display:flex;align-items:center;gap:8px">
                    @csrf
                    @php $cur = strtolower($c->status); @endphp
                    <select name="status" class="select client-status {{ $cur }}" onchange="this.form.submit()" style="min-width:160px">
                      @foreach($opts as $opt)
                        <option value="{{ $opt }}" {{ strtolower($c->status)===strtolower($opt)?'selected':'' }}>{{ ucfirst($opt) }}</option>
                      @endforeach
                    </select>
                  </form>
                </td>
                <td class="actions">
                  <a href="{{ route('admin.clients.edit', ['id'=>$c->id]) }}">Edit</a>
                  <form method="post" action="{{ route('admin.clients.delete', ['id'=>$c->id]) }}" style="display:inline" onsubmit="return confirm('Delete this client?')">
                    @csrf
                    <button type="submit" class="icon-btn danger" style="margin-left:6px" title="Delete client" aria-label="Delete client">
                      <svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 3h6l1 2h5v2H3V5h5l1-2zm1 6h2v10h-2V9zm4 0h2v10h-2V9zM7 9h2v10H7V9z"/></svg>
                    </button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="7" class="muted">No clients found.</td></tr>
            @endforelse
          </tbody>
        </table>

        <div style="margin-top:12px">{{ $list->links() }}</div>
      </div>
    </div>
  </div>
</body>
</html>
