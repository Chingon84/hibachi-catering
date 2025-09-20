<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Team Management</title>
  <style>
    :root{--bg:#f7f7fb;--text:#111827;--muted:#6b7280;--card:#fff;--border:#e5e7eb;--brand:#b21e27;--brand-hover:#9a1a22}
    body{margin:0;background:#fff;color:var(--text);font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif}
    .wrap{padding:16px}
    .head{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
    .title{margin:0;font-size:20px}
    .btn{display:inline-flex;align-items:center;gap:6px;padding:8px 12px;border-radius:10px;border:1px solid var(--brand);background:var(--brand);color:#fff;text-decoration:none}
    .btn.secondary{background:#fff;color:#111;border-color:var(--border)}
    table{width:100%;border-collapse:collapse;border:1px solid var(--border);border-radius:12px;overflow:hidden}
    th,td{padding:10px;border-bottom:1px solid var(--border);text-align:left}
    th{background:#f9fafb;font-weight:700}
    .badge{padding:2px 8px;border-radius:999px;border:1px solid var(--border);font-size:12px}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="head">
      <h1 class="title">Team Management</h1>
      <div style="display:flex;gap:8px">
        <a class="btn secondary" href="{{ route('admin.team.permissions') }}">Permissions Matrix</a>
        <a class="btn" href="{{ route('admin.team.create') }}">Add member</a>
      </div>
    </div>
    @if (session('ok'))
      <div style="margin:8px 0;color:#065f46">{{ session('ok') }}</div>
    @endif
    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Position</th>
          <th>Role</th>
          <th>Email</th>
          <th>Username</th>
          <th>Admin</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($users as $u)
        <tr>
          <td>{{ $u->name }}</td>
          <td>{{ $u->position }}</td>
          <td><span class="badge">{{ strtoupper($u->role) }}</span></td>
          <td>{{ $u->email }}</td>
          <td>{{ $u->username }}</td>
          <td>{!! $u->can_access_admin ? '<span class="badge" style="border-color:#059669;color:#065f46">YES</span>' : '<span class="badge" style="border-color:#b91c1c;color:#7f1d1d">NO</span>' !!}</td>
          <td>{!! $u->is_active ? '<span class="badge" style="border-color:#059669;color:#065f46">Active</span>' : '<span class="badge" style="border-color:#b91c1c;color:#7f1d1d">Disabled</span>' !!}</td>
          <td style="display:flex;gap:6px">
            <a class="btn secondary" href="{{ route('admin.team.edit', $u->id) }}">Edit</a>
            @if($u->role !== 'owner')
              <form method="post" action="{{ route('admin.team.toggle', $u->id) }}" onsubmit="return confirm('Toggle admin access?')">
                @csrf
                <button class="btn secondary" type="submit">Toggle Admin</button>
              </form>
              <form method="post" action="{{ route('admin.team.delete', $u->id) }}" onsubmit="return confirm('Delete this user?')">
                @csrf
                <button class="icon-btn danger" type="submit" title="Delete user" aria-label="Delete user">
                  <svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 3h6l1 2h5v2H3V5h5l1-2zm1 6h2v10h-2V9zm4 0h2v10h-2V9zM7 9h2v10H7V9z"/></svg>
                </button>
              </form>
            @else
              <button class="btn secondary" disabled title="Owner cannot be deleted or restricted">Protected</button>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center;color:var(--muted)">No team members yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</body>
</html>
