<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Access Control</title>
  <style>
    :root{--bg:#f8fafc;--text:#0f172a;--muted:#64748b;--card:#fff;--border:#e2e8f0;--brand:#b21e27;--brand-hover:#9a1a22;--soft:#f8fafc}
    *,:before,:after{box-sizing:border-box}
    body{margin:0;background:var(--bg);color:var(--text);font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
    .wrap{max-width:1180px;margin:0 auto;padding:20px}
    .head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:16px}
    .title{margin:0;font-size:28px;line-height:1.1;letter-spacing:-.03em}
    .subtitle{margin:8px 0 0;color:var(--muted);font-size:14px;line-height:1.6;max-width:760px}
    .btn{display:inline-flex;align-items:center;gap:6px;padding:10px 14px;border-radius:12px;border:1px solid var(--brand);background:var(--brand);color:#fff;text-decoration:none;font-size:14px;font-weight:600;cursor:pointer}
    .btn.secondary{background:#fff;color:#0f172a;border-color:var(--border)}
    .btn:disabled{opacity:.55;cursor:not-allowed}
    .flash{margin:0 0 16px;padding:12px 14px;border:1px solid #bbf7d0;border-radius:14px;background:#f0fdf4;color:#166534;font-size:14px;font-weight:600}
    .shell{border:1px solid var(--border);border-radius:20px;background:var(--card);padding:18px}
    .toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px}
    .search{width:100%;max-width:360px;padding:10px 12px;border:1px solid var(--border);border-radius:12px;background:#fff;color:var(--text);font-size:14px;line-height:1.4}
    .role-strip{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px}
    .role-chip{display:inline-flex;align-items:center;padding:8px 12px;border-radius:999px;border:1px solid var(--border);background:#fff;color:#334155;text-decoration:none;font-size:13px;font-weight:700}
    .role-chip.active{background:#0f172a;border-color:#0f172a;color:#fff;box-shadow:0 10px 20px rgba(15,23,42,.12)}
    .role-helper{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:14px;padding:12px 14px;border:1px solid var(--border);border-radius:16px;background:var(--soft)}
    .role-helper-title{font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--muted)}
    .role-helper-copy{margin-top:6px;font-size:14px;line-height:1.6;color:#334155}
    .owner-note{font-size:12px;font-weight:600;color:#92400e}
    .module-nav{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:16px}
    .module-nav a{color:#475569;text-decoration:none;font-size:13px;font-weight:600}
    .module-nav a:hover{color:#0f172a}
    .group{margin-bottom:16px;padding:14px 16px;border:1px solid var(--border);border-radius:18px;background:var(--card)}
    .group-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:8px}
    .group-title{margin:0;font-size:17px;letter-spacing:-.02em}
    .group-note{font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--muted)}
    .perm-list{border-top:1px solid var(--border)}
    .perm-row{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:14px 0;border-bottom:1px solid var(--border)}
    .perm-row:last-child{border-bottom:0;padding-bottom:0}
    .perm-main{min-width:0}
    .perm-label{font-size:14px;font-weight:600;color:#0f172a}
    .muted{color:var(--muted);font-size:12px;line-height:1.5}
    .toggle{position:relative;display:inline-flex;align-items:center;width:48px;height:28px;flex:0 0 auto}
    .toggle input{position:absolute;inset:0;opacity:0;cursor:pointer}
    .toggle-track{position:absolute;inset:0;border-radius:999px;background:#cbd5e1;transition:all .18s ease}
    .toggle-thumb{position:absolute;left:3px;top:3px;width:22px;height:22px;border-radius:999px;background:#fff;box-shadow:0 1px 3px rgba(15,23,42,.18);transition:all .18s ease}
    .toggle input:checked + .toggle-track{background:#b21e27}
    .toggle input:checked + .toggle-track + .toggle-thumb{transform:translateX(20px)}
    .toggle input:disabled{cursor:not-allowed}
    .toggle input:disabled + .toggle-track{background:#cbd5e1}
    .toggle input:disabled:checked + .toggle-track{background:#94a3b8}
    .tag{display:inline-flex;align-items:center;padding:5px 9px;border:1px solid var(--border);border-radius:999px;background:#fff;font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--muted)}
    .actions{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:18px}
    .placeholder{margin-top:18px;padding:14px 16px;border:1px dashed var(--border);border-radius:18px;background:#fff}
    .placeholder-title{font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--muted)}
    .placeholder-copy{margin-top:8px;font-size:14px;color:#475569}
    .hidden-row{display:none}
    @media (max-width: 720px){.wrap{padding:16px}.head,.toolbar,.actions,.role-helper,.perm-row{flex-direction:column;align-items:stretch}.toggle{align-self:flex-start}}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="head">
      <div>
        <h1 class="title">Access Control</h1>
        <p class="subtitle">Manage what each role can see and update across the admin system. Permissions are grouped by module and described in plain language for faster review.</p>
      </div>
      <div style="display:flex;gap:8px">
        <a class="btn secondary" href="{{ route('admin.team.index') }}">Back to Team</a>
      </div>
    </div>
    @if (session('ok'))
      <div class="flash">{{ session('ok') }}</div>
    @endif

    <form method="post" action="{{ route('admin.team.permissions.update') }}">
      @csrf
      <input type="hidden" name="selected_role" value="{{ $selectedRole }}">
      <div class="shell">
        <div class="role-strip">
          @foreach ($roles as $role)
            <a class="role-chip {{ $selectedRole === $role ? 'active' : '' }}" href="{{ route('admin.team.permissions', ['role' => $role]) }}">{{ ucfirst($role) }}</a>
          @endforeach
        </div>

        <div class="role-helper">
          <div>
            <div class="role-helper-title">Selected Role: {{ ucfirst($selectedRole) }}</div>
            <div class="role-helper-copy">{{ $roleSummaries[$selectedRole] ?? 'Configured access for this role.' }}</div>
          </div>
          @if($selectedRole === 'owner')
            <div class="owner-note">Owner has full access and cannot be restricted.</div>
          @endif
        </div>

        <div class="toolbar">
          <input class="search" id="permission-search" type="search" placeholder="Search permissions by module or action">
          <span class="tag">{{ count($groupedPermissions) }} modules</span>
        </div>

        <nav class="module-nav">
          @foreach ($groupedPermissions as $module => $items)
            <a href="#module-{{ \Illuminate\Support\Str::slug($module) }}">{{ $module }}</a>
          @endforeach
        </nav>

        @foreach ($groupedPermissions as $module => $items)
          <section class="group" id="module-{{ \Illuminate\Support\Str::slug($module) }}" data-permission-group>
            <div class="group-head">
              <h2 class="group-title">{{ $module }}</h2>
              <div class="group-note">{{ count($items) }} permissions</div>
            </div>
            <div class="perm-list">
              @foreach ($items as $perm)
                @php $checked = $selectedRole === 'owner' ? true : in_array($perm['key'], $assigned[$selectedRole] ?? []); @endphp
                <div class="perm-row" data-permission-row data-search="{{ strtolower($module . ' ' . $perm['label'] . ' ' . $perm['description'] . ' ' . $perm['key']) }}">
                  <div class="perm-main">
                    <div class="perm-label">{{ $perm['label'] }}</div>
                    <div class="muted">{{ $perm['description'] }}</div>
                  </div>
                  <label class="toggle" aria-label="{{ $perm['label'] }}">
                    <input
                      type="checkbox"
                      name="matrix[{{ $selectedRole }}][]"
                      value="{{ $perm['key'] }}"
                      {{ $checked ? 'checked' : '' }}
                      {{ $selectedRole === 'owner' ? 'disabled' : '' }}
                    >
                    <span class="toggle-track"></span>
                    <span class="toggle-thumb"></span>
                  </label>
                </div>
              @endforeach
            </div>
          </section>
        @endforeach

        <div class="actions">
          <div style="display:flex;gap:8px">
            <a class="btn secondary" href="{{ route('admin.team.index') }}">Back to Team</a>
            <a class="btn secondary" href="{{ route('admin.team.permissions', ['role' => $selectedRole]) }}">Cancel</a>
          </div>
          <button class="btn" type="submit" {{ $selectedRole === 'owner' ? 'disabled' : '' }}>Save Changes</button>
        </div>

        <section class="placeholder">
          <div class="placeholder-title">Individual Overrides</div>
          <div class="placeholder-copy">No user-specific access overrides configured yet.</div>
        </section>
      </div>
    </form>
  </div>
  <script>
    (() => {
      const input = document.getElementById('permission-search');
      if (!input) return;

      const groups = Array.from(document.querySelectorAll('[data-permission-group]'));
      input.addEventListener('input', () => {
        const term = input.value.trim().toLowerCase();

        groups.forEach((group) => {
          const rows = Array.from(group.querySelectorAll('[data-permission-row]'));
          let visible = 0;

          rows.forEach((row) => {
            const haystack = row.getAttribute('data-search') || '';
            const match = term === '' || haystack.includes(term);
            row.classList.toggle('hidden-row', !match);
            if (match) visible += 1;
          });

          group.classList.toggle('hidden-row', visible === 0);
        });
      });
    })();
  </script>
</body>
</html>
