<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Team</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    body{background:var(--bg)}
    .wrap{max-width:1380px;margin:24px auto;padding:0 12px 24px}
    .stack{display:grid;gap:18px}
    .head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px}
    .title{margin:0;font-size:28px;line-height:1.05}
    .subtitle{margin:8px 0 0;color:var(--muted);max-width:720px}
    .actions{display:flex;gap:8px;flex-wrap:wrap}
    .section{padding:22px}
    .summary-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
    .summary-card{padding:16px;border:1px solid var(--border);border-radius:14px;background:linear-gradient(180deg,#fff, #fafafb)}
    .summary-label{font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.04em}
    .summary-value{margin-top:10px;font-size:28px;font-weight:700}
    .summary-note{margin-top:6px;color:var(--muted);font-size:13px}
    .filters{display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:12px;align-items:end}
    .field{display:flex;flex-direction:column;gap:6px}
    .label{font-size:13px;font-weight:700;color:#374151}
    .input,.select{width:100%}
    .table-wrap{overflow:auto}
    .table{min-width:1080px}
    .table th,.table td{padding:14px 12px;vertical-align:middle}
    .table tbody tr:hover{background:#fafafa}
    .badge{display:inline-flex;align-items:center;padding:4px 10px;border-radius:999px;border:1px solid var(--border);font-size:12px;font-weight:700}
    .badge.staff-type{background:#f8fafc;color:#64748b;border-color:#e2e8f0;font-weight:600}
    .badge.staff-type.unassigned{background:#f8fafc;color:#94a3b8;border-color:#e5e7eb}
    .badge.role{background:#f8fafc;color:#334155;border-color:#dbe4ee}
    .badge.admin-yes,.badge.admin-no{padding:3px 8px;font-size:11px;font-weight:600;background:#f8fafc;color:#475569;border-color:#e2e8f0}
    .badge.status-active{background:#ecfdf5;color:#166534;border-color:#bbf7d0}
    .badge.status-inactive{background:#fef2f2;color:#991b1b;border-color:#fecaca}
    .muted{color:var(--muted)}
    .cell-title{font-weight:700}
    .cell-sub{margin-top:4px;font-size:12px;color:var(--muted)}
    .action-row{display:flex;gap:4px;align-items:center;flex-wrap:wrap}
    .row-action-btn{display:inline-flex;align-items:center;justify-content:center;min-height:30px;padding:0 10px;border-radius:8px;border:1px solid #e2e8f0;background:#fff;color:#475569;font-size:12px;font-weight:600;line-height:1;text-decoration:none;white-space:nowrap}
    .row-action-btn:hover{background:#f8fafc;border-color:#cbd5e1}
    .row-action-btn.button{appearance:none;-webkit-appearance:none;cursor:pointer}
    .row-action-badge{display:inline-flex;align-items:center;justify-content:center;min-height:30px;padding:0 10px;border-radius:999px;border:1px solid #e2e8f0;background:#f8fafc;color:#64748b;font-size:12px;font-weight:700;line-height:1;white-space:nowrap}
    .row-action-delete{width:30px;height:30px;border-radius:8px;border:1px solid #fecaca;background:#fff;color:#dc2626;display:inline-flex;align-items:center;justify-content:center;cursor:pointer}
    .row-action-delete:hover{background:#fef2f2;border-color:#fca5a5}
    .row-action-delete svg{width:14px;height:14px}
    @media (max-width: 1100px){.summary-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.filters{grid-template-columns:1fr 1fr}}
    @media (max-width: 760px){.head{flex-direction:column}.summary-grid,.filters{grid-template-columns:1fr}.table{min-width:920px}}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="stack">
      <div class="head">
        <div>
          <h1 class="title">Team</h1>
          <p class="subtitle">Central staff directory for chefs, assistants, managers, office staff, and admin operators. Permissions remain intact while the directory now supports operational workflows.</p>
        </div>
        <div class="actions">
          <a class="btn secondary" href="{{ route('admin.team.permissions') }}">Access Control</a>
          <a class="btn" href="{{ route('admin.team.create') }}">Add Member</a>
        </div>
      </div>

      <div class="card section">
        <div class="summary-grid">
          <div class="summary-card">
            <div class="summary-label">Total Staff</div>
            <div class="summary-value">{{ $users->count() }}</div>
            <div class="summary-note">Directory records in Team</div>
          </div>
          <div class="summary-card">
            <div class="summary-label">Active Chefs</div>
            <div class="summary-value">{{ $users->where('is_active', true)->where('staff_type', 'Chef')->count() }}</div>
            <div class="summary-note">Available for Feedback Center chef selectors</div>
          </div>
          <div class="summary-card">
            <div class="summary-label">Admin Access</div>
            <div class="summary-value">{{ $users->where('can_access_admin', true)->count() }}</div>
            <div class="summary-note">Users with dashboard access enabled</div>
          </div>
          <div class="summary-card">
            <div class="summary-label">Inactive Staff</div>
            <div class="summary-value">{{ $users->where('is_active', false)->count() }}</div>
            <div class="summary-note">Excluded from active operational filters</div>
          </div>
        </div>
      </div>

      <div class="card section">
        <form method="get" class="filters">
          <div class="field">
            <label class="label" for="q">Search</label>
            <input class="input" id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search by name, email, username, position, or phone">
          </div>
          <div class="field">
            <label class="label" for="staff_type">Staff Type</label>
            <select class="select" id="staff_type" name="staff_type">
              <option value="">All staff types</option>
              @foreach ($staffTypes as $staffType)
                <option value="{{ $staffType }}" {{ ($filters['staff_type'] ?? '') === $staffType ? 'selected' : '' }}>{{ $staffType }}</option>
              @endforeach
            </select>
          </div>
          <div class="field">
            <label class="label" for="status">Status</label>
            <select class="select" id="status" name="status">
              <option value="">All statuses</option>
              <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
              <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
          </div>
          <div class="actions">
            <button class="btn" type="submit">Apply Filters</button>
            <a class="btn secondary" href="{{ route('admin.team.index') }}">Reset</a>
          </div>
        </form>
      </div>

      <div class="card section">
        @if (session('ok'))
          <div style="margin-bottom:12px;color:#166534;font-weight:600">{{ session('ok') }}</div>
        @endif
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Staff Type</th>
                <th>Position</th>
                <th>Contact</th>
                <th>Role</th>
                <th>Admin</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($users as $u)
              <tr>
                <td>
                  <div class="cell-title"><a href="{{ route('admin.team.show', $u->id) }}" style="color:#0f172a;text-decoration:none">{{ $u->name }}</a></div>
                  <div class="cell-sub">{{ $u->username ?: 'No username assigned' }}</div>
                </td>
                <td><span class="badge staff-type {{ $u->staff_type ? '' : 'unassigned' }}">{{ $u->staff_type ?: 'Unassigned' }}</span></td>
                <td>{{ $u->position ?: '—' }}</td>
                <td>
                  <div>{{ $u->email }}</div>
                  <div class="cell-sub">{{ $u->phone ?: 'No phone on file' }}</div>
                </td>
                <td><span class="badge role">{{ strtoupper($u->role) }}</span></td>
                <td><span class="badge {{ $u->can_access_admin ? 'admin-yes' : 'admin-no' }}">{{ $u->can_access_admin ? 'Yes' : 'No' }}</span></td>
                <td><span class="badge {{ $u->is_active ? 'status-active' : 'status-inactive' }}">{{ $u->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td>
                  <div class="action-row">
                    <a class="row-action-btn" href="{{ route('admin.team.show', $u->id) }}">Profile</a>
                    <a class="row-action-btn" href="{{ route('admin.team.edit', $u->id) }}">Edit</a>
                    @if($u->role !== 'owner')
                      <form method="post" action="{{ route('admin.team.toggle', $u->id) }}" onsubmit="return confirm('Toggle admin access?')">
                        @csrf
                        <button class="row-action-btn button" type="submit">Access</button>
                      </form>
                      <form method="post" action="{{ route('admin.team.delete', $u->id) }}" onsubmit="return confirm('Delete this user?')">
                        @csrf
                        <button class="row-action-delete" type="submit" title="Delete user" aria-label="Delete user">
                          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 3h6l1 2h5v2H3V5h5l1-2zm1 6h2v10h-2V9zm4 0h2v10h-2V9zM7 9h2v10H7V9z"/></svg>
                        </button>
                      </form>
                    @else
                      <span class="row-action-badge" title="Owner cannot be deleted or restricted">Protected</span>
                    @endif
                  </div>
                </td>
              </tr>
              @empty
              <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:28px 12px">No team members match the current filters.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
