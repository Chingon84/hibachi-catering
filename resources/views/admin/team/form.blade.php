<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $user->exists ? 'Edit Member' : 'Add Member' }}</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    body{background:var(--bg)}
    .wrap{max-width:980px;margin:24px auto;padding:0 12px 24px}
    .panel{padding:22px}
    .title{margin:0;font-size:26px;line-height:1.1}
    .subtitle{margin:8px 0 0;color:var(--muted);font-size:14px}
    .panel-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:20px}
    .form-stack{display:grid;gap:18px}
    .section-card{border:1px solid var(--border);border-radius:16px;background:linear-gradient(180deg,#fff,#fcfcfd);padding:18px}
    .section-head{margin-bottom:14px}
    .section-title{margin:0;font-size:15px;font-weight:700;letter-spacing:.01em;display:flex;align-items:center;gap:8px}
    .section-icon{display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;color:#475569}
    .section-copy{margin:6px 0 0;color:var(--muted);font-size:13px;line-height:1.5}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
    .field{display:flex;flex-direction:column;gap:6px}
    .field.span-2{grid-column:1 / -1}
    .label{font-weight:600;font-size:14px}
    .input, select{padding:10px 12px;border:1px solid var(--border);border-radius:10px;background:#fff}
    .password-group{display:grid;gap:12px}
    .security-layout{display:grid;grid-template-columns:minmax(0,1.5fr) minmax(260px,.9fr);gap:16px;align-items:start}
    .password-field{position:relative}
    .password-field .input{width:100%;padding-right:44px}
    .password-toggle{position:absolute;top:50%;right:12px;transform:translateY(-50%);background:none;border:none;padding:0;margin:0;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--muted);width:20px;height:20px}
    .password-toggle:focus-visible{outline:2px solid var(--brand);outline-offset:2px}
    .password-toggle svg{width:20px;height:20px}
    .password-toggle .eye-closed{display:none}
    .password-toggle[data-visible="true"]{color:var(--brand)}
    .password-toggle[data-visible="true"] .eye-open{display:none}
    .password-toggle[data-visible="true"] .eye-closed{display:inline}
    .row{display:flex;gap:10px;margin-top:18px}
    .error{color:#b21e27;font-size:14px;margin-bottom:12px}
    .hint{font-size:11.5px;color:#6b7280;line-height:1.45}
    .meta-card{padding:6px 0}
    .meta-kicker{margin:0 0 10px;font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#64748b}
    .meta-list{display:grid;gap:10px}
    .meta-item{display:flex;justify-content:space-between;gap:14px;padding-bottom:10px;border-bottom:1px solid #eef2f7}
    .meta-item:last-child{padding-bottom:0;border-bottom:0}
    .meta-label{font-size:12px;color:#6b7280}
    .meta-value{font-size:13px;font-weight:600;color:#111827;text-align:right}
    .meta-value.placeholder{color:#94a3b8;font-weight:500}
    @media (max-width: 860px){.security-layout{grid-template-columns:1fr}}
    @media (max-width: 760px){.grid{grid-template-columns:1fr}.panel-head{flex-direction:column}}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card panel">
      <div class="panel-head">
        <div>
          <h1 class="title">{{ $user->exists ? 'Edit Team Member' : 'Add Team Member' }}</h1>
          <p class="subtitle">Manage directory details, operational staff type, and admin access from one record.</p>
        </div>
        <a class="btn secondary" href="{{ route('admin.team.index') }}">Back to Team</a>
      </div>
      @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
      @endif
      @php
        $securityMeta = [
          ['label' => 'Last password change', 'value' => null],
          ['label' => 'Last login', 'value' => null],
          ['label' => 'Login attempts', 'value' => null],
        ];
      @endphp
      <form method="post" action="{{ $user->exists ? route('admin.team.update',$user->id) : route('admin.team.store') }}" data-has-errors="{{ $errors->any() ? '1' : '0' }}">
        @csrf
        <div class="form-stack">
          <section class="section-card">
            <div class="section-head">
              <h2 class="section-title">Directory Information</h2>
              <p class="section-copy">Core staff profile information used across Team, Feedback Center, and other operational modules.</p>
            </div>
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
                <label class="label" for="phone">Phone</label>
                <input class="input" id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
              </div>
              <div class="field">
                <label class="label" for="username">Username</label>
                <input class="input" id="username" name="username" value="{{ old('username', $user->username) }}">
              </div>
              <div class="field">
                <label class="label" for="staff_type">Staff Type</label>
                <select id="staff_type" name="staff_type">
                  @php $staffType = old('staff_type', $user->staff_type); @endphp
                  <option value="">Select type</option>
                  @foreach (($staffTypes ?? []) as $option)
                    <option value="{{ $option }}" {{ $staffType === $option ? 'selected' : '' }}>{{ $option }}</option>
                  @endforeach
                </select>
                <span class="hint">Use this to power chef-only and operational filters across admin modules.</span>
              </div>
              <div class="field span-2">
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
            </div>
          </section>

          <section class="section-card">
            <div class="section-head">
              <h2 class="section-title">
                <span class="section-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="4" y="11" width="16" height="9" rx="2"></rect>
                    <path d="M8 11V8a4 4 0 0 1 8 0v3"></path>
                  </svg>
                </span>
                <span>Security</span>
              </h2>
              <p class="section-copy">Manage password visibility, update credentials, and review recent account activity.</p>
            </div>
            <div class="security-layout">
              <div class="field span-2">
                <label class="label" for="password">{{ $user->exists ? 'New Password' : 'Password' }}</label>
                <div class="password-group">
                  <div class="password-field">
                    <input class="input" id="password" type="password" name="password" autocomplete="new-password" {{ $user->exists ? '' : 'required' }}>
                    <button class="password-toggle" type="button" aria-label="Show password" data-target="password" data-visible="false">
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
                  <div class="field" style="gap:6px">
                    <label class="label" for="password_confirmation">{{ $user->exists ? 'Confirm New Password' : 'Confirm Password' }}</label>
                    <div class="password-field">
                      <input class="input" id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" {{ $user->exists ? '' : 'required' }}>
                      <button class="password-toggle" type="button" aria-label="Show password confirmation" data-target="password_confirmation" data-visible="false">
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
                </div>
                <span class="hint">{{ $user->exists ? 'Leave both fields blank to keep the current password. If entered, the new password must match confirmation.' : 'Use at least 6 characters and confirm it below.' }}</span>
              </div>
              <aside class="meta-card">
                <p class="meta-kicker">Account Activity</p>
                <div class="meta-list">
                  @foreach ($securityMeta as $item)
                    <div class="meta-item">
                      <div class="meta-label">{{ $item['label'] }}</div>
                      <div class="meta-value {{ $item['value'] ? '' : 'placeholder' }}">{{ $item['value'] ?: 'Not tracked yet' }}</div>
                    </div>
                  @endforeach
                </div>
              </aside>
            </div>
          </section>

          <section class="section-card">
            <div class="section-head">
              <h2 class="section-title">Access</h2>
              <p class="section-copy">Control whether this team member can access the admin and whether the account is currently active.</p>
            </div>
            <div class="grid">
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
                  <option value="0" {{ (int)$act===0?'selected':'' }}>Inactive</option>
                </select>
              </div>
            </div>
          </section>
        </div>
        <div class="row">
          <a class="btn secondary" href="{{ route('admin.team.index') }}">Cancel</a>
          <button class="btn" type="submit">Save Changes</button>
        </div>
      </form>
    </div>
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
          btn.setAttribute('aria-label', makeVisible ? 'Hide password' : 'Show password');
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
