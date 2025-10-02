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
    .password-field{position:relative}
    .password-toggle{position:absolute;top:50%;right:12px;transform:translateY(-50%);background:none;border:none;padding:0;margin:0;display:flex;align-items:center;cursor:pointer;color:var(--muted)}
    .password-toggle:focus-visible{outline:2px solid var(--brand);outline-offset:2px}
    .password-toggle svg{width:20px;height:20px}
    .password-toggle .eye-closed{display:none}
    .password-toggle[data-visible="true"]{color:var(--brand)}
    .password-toggle[data-visible="true"] .eye-open{display:none}
    .password-toggle[data-visible="true"] .eye-closed{display:inline}
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
    <form method="post" action="{{ $user->exists ? route('admin.team.update',$user->id) : route('admin.team.store') }}" data-has-errors="{{ $errors->any() ? '1' : '0' }}">
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
          @php
            $passwordPlaceholder = $passwordPlaceholder ?? '********';
            $passwordValue = session()->has('team_form_password')
              ? session('team_form_password')
              : ($user->exists ? $passwordPlaceholder : '');
          @endphp
          <div class="password-field">
            <input class="input" id="password" type="password" name="password" autocomplete="new-password" value="{{ $passwordValue }}" {{ $user->exists ? '' : 'required' }}>
            <button class="password-toggle" type="button" aria-label="Mostrar contraseña" data-target="password" data-visible="false">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <g class="eye-open">
                  <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z" />
                  <circle cx="12" cy="12" r="3" />
                </g>
                <g class="eye-closed">
                  <path d="M17.94 17.94A10.9 10.9 0 0 1 12 19c-7 0-11-7-11-7a19.4 19.4 0 0 1 4.86-5.55" />
                  <path d="M9.88 9.88A3 3 0 0 0 9 12a3 3 0 0 0 5.19 2.12" />
                  <line x1="1" y1="1" x2="23" y2="23" />
                </g>
              </svg>
            </button>
          </div>
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
  <script>
    (function(){
      document.querySelectorAll('.password-toggle').forEach(function(btn){
        var targetId = btn.getAttribute('data-target');
        var input = document.getElementById(targetId);
        if (!input) return;

        var toggleState = function(makeVisible){
          input.type = makeVisible ? 'text' : 'password';
          btn.dataset.visible = makeVisible ? 'true' : 'false';
          btn.setAttribute('aria-label', makeVisible ? 'Ocultar contraseña' : 'Mostrar contraseña');
        };

        btn.addEventListener('click', function(){
          var makeVisible = input.type === 'password';
          toggleState(makeVisible);
        });
      });
    })();
  </script>
</body>
</html>
