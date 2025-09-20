<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $user->exists ? 'Edit Member' : 'Add Member' }}</title>
  <style>
    :root{--bg:#f7f7fb;--text:#111827;--muted:#6b7280;--card:#fff;--border:#e5e7eb;--brand:#b21e27;--brand-hover:#9a1a22}
    body{margin:0;background:#fff;color:var(--text);font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif}
    .wrap{padding:16px;max-width:820px}
    .title{margin:0 0 8px;font-size:20px}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .field{display:flex;flex-direction:column;gap:6px}
    .label{font-weight:600}
    .input, select{padding:10px 12px;border:1px solid var(--border);border-radius:10px}
    .row{display:flex;gap:10px;margin-top:12px}
    .btn{display:inline-flex;align-items:center;gap:6px;padding:10px 14px;border-radius:10px;border:1px solid var(--brand);background:var(--brand);color:#fff;text-decoration:none}
    .btn.secondary{background:#fff;color:#111;border-color:var(--border)}
    .error{color:#b21e27;font-size:14px}
  </style>
</head>
<body>
  <div class="wrap">
    <h1 class="title">{{ $user->exists ? 'Edit Member' : 'Add Member' }}</h1>
    @if ($errors->any())
      <div class="error">{{ $errors->first() }}</div>
    @endif
    <form method="post" action="{{ $user->exists ? route('admin.team.update',$user->id) : route('admin.team.store') }}">
      @csrf
      <div class="grid">
        <div class="field">
          <label class="label" for="name">Full name</label>
          <input class="input" id="name" name="name" value="{{ old('name', $user->name) }}" required>
        </div>
        <div class="field">
          <label class="label" for="position">Position</label>
          <input class="input" id="position" name="position" value="{{ old('position', $user->position) }}">
        </div>
        <div class="field">
          <label class="label" for="email">Email</label>
          <input class="input" id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
        </div>
        <div class="field">
          <label class="label" for="username">Username</label>
          <input class="input" id="username" name="username" value="{{ old('username', $user->username) }}">
        </div>
        <div class="field">
          <label class="label" for="role">Role</label>
          <select id="role" name="role" required>
            @php $role = old('role', $user->role ?: 'staff'); @endphp
            @php $me = auth()->user(); @endphp
            <option value="owner" {{ $role==='owner'?'selected':'' }} {{ (!$me || !$me->isOwner()) ? 'disabled' : '' }}>Owner</option>
            <option value="admin" {{ $role==='admin'?'selected':'' }}>Admin</option>
            <option value="manager" {{ $role==='manager'?'selected':'' }}>Manager</option>
            <option value="office" {{ $role==='office'?'selected':'' }}>Office</option>
            <option value="staff" {{ $role==='staff'?'selected':'' }}>Staff</option>
            <option value="readonly" {{ $role==='readonly'?'selected':'' }}>Read Only</option>
          </select>
        </div>
        <div class="field">
          <label class="label" for="password">Password {{ $user->exists ? '(leave blank to keep)' : '' }}</label>
          <input class="input" id="password" type="password" name="password" {{ $user->exists ? '' : 'required' }}>
        </div>
        <div class="field">
          <label class="label" for="can_access_admin">Admin Access</label>
          <select id="can_access_admin" name="can_access_admin">
            @php $adm = old('can_access_admin', (int)$user->can_access_admin); @endphp
            <option value="1" {{ (int)$adm===1?'selected':'' }}>Yes</option>
            <option value="0" {{ (int)$adm===0?'selected':'' }}>No</option>
          </select>
        </div>
        <div class="field">
          <label class="label" for="is_active">Status</label>
          <select id="is_active" name="is_active">
            @php $act = old('is_active', (int)$user->is_active ?: 1); @endphp
            <option value="1" {{ (int)$act===1?'selected':'' }}>Active</option>
            <option value="0" {{ (int)$act===0?'selected':'' }}>Disabled</option>
          </select>
        </div>
      </div>
      <div class="row">
        <a class="btn secondary" href="{{ route('admin.team.index') }}">Cancel</a>
        <button class="btn" type="submit">Save</button>
      </div>
    </form>
  </div>
</body>
</html>
