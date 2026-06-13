@extends('layouts.admin')

@section('title', 'Access Control')

@push('styles')
<style>
  /* Page-specific access-control layout. Core chrome from app.css. */
  .wrap{width:100%;max-width:none;margin:0;padding:20px 24px}
  .head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:16px}
  .access-title{margin:0;font-size:28px;line-height:1.1;letter-spacing:-.03em}
  .subtitle{margin:8px 0 0;color:var(--muted);font-size:14px;line-height:1.6;max-width:760px}
  .btn:disabled{opacity:.55;cursor:not-allowed}
  .flash{margin:0 0 16px;padding:12px 14px;border:1px solid #bbf7d0;border-radius:14px;background:#f0fdf4;color:#166534;font-size:14px;font-weight:600}
  .info-card{display:grid;gap:10px;margin:0 0 16px;padding:14px 16px;border:1px solid #dbeafe;border-radius:18px;background:#eff6ff}
  .info-card-title{font-size:12px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#1d4ed8}
  .info-card-copy{font-size:14px;line-height:1.6;color:#1e3a8a}
  .info-card-warning{display:inline-flex;align-items:center;padding:6px 10px;border-radius:999px;border:1px solid #fed7aa;background:#fff7ed;color:#c2410c;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
  .matrix-card{display:grid;gap:10px;margin:0 0 14px;padding:14px;border:1px solid var(--border);border-radius:18px;background:#fff}
  .matrix-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px}
  .matrix-title{margin:0;font-size:17px;line-height:1.15}
  .matrix-copy{margin:6px 0 0;color:var(--muted);font-size:13px;line-height:1.5;max-width:760px}
  .matrix-note{display:inline-flex;align-items:center;padding:5px 9px;border-radius:999px;border:1px solid #e2e8f0;background:#f8fafc;color:#64748b;font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
  .matrix-wrap{overflow-x:auto}
  .matrix-table{width:100%;border-collapse:separate;border-spacing:0;min-width:760px}
  .matrix-table th,.matrix-table td{padding:10px 12px;text-align:left;vertical-align:middle;border-bottom:1px solid var(--border)}
  .matrix-table th{font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);background:#f8fafc}
  .matrix-table tbody tr:last-child td{border-bottom:0}
  .matrix-module{font-size:13px;font-weight:700;color:#0f172a}
  .matrix-badge{display:inline-flex;align-items:center;gap:5px;padding:4px 9px;border-radius:999px;border:1px solid #e2e8f0;background:#fff;font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;white-space:nowrap;line-height:1}
  .matrix-badge.allowed{border-color:#bbf7d0;background:#ecfdf5;color:#166534}
  .matrix-badge.denied{border-color:#fecaca;background:#fef2f2;color:#991b1b}
  .matrix-badge.restricted{border-color:#cbd5e1;background:#f8fafc;color:#334155}
  .matrix-icon{font-size:11px;line-height:1}
  .shell{border:1px solid var(--border);border-radius:20px;background:var(--card);padding:18px}
  .toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px}
  .search{width:100%;max-width:360px;padding:10px 12px;border:1px solid var(--border);border-radius:12px;background:#fff;color:var(--text);font-size:14px;line-height:1.4}
  .role-strip{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px}
  .role-chip{display:inline-flex;align-items:center;padding:8px 12px;border-radius:999px;border:1px solid var(--border);background:#fff;color:#334155;text-decoration:none;font-size:13px;font-weight:700}
  .role-chip.active{background:#0f172a;border-color:#0f172a;color:#fff;box-shadow:0 10px 20px rgba(15,23,42,.12)}
  .role-helper{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:14px;padding:12px 14px;border:1px solid var(--border);border-radius:16px;background:var(--surface-2)}
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
  .toggle input:checked + .toggle-track{background:var(--brand)}
  .toggle input:checked + .toggle-track + .toggle-thumb{transform:translateX(20px)}
  .toggle input:disabled{cursor:not-allowed}
  .toggle input:disabled + .toggle-track{background:#cbd5e1}
  .toggle input:disabled:checked + .toggle-track{background:#94a3b8}
  .tag{display:inline-flex;align-items:center;padding:5px 9px;border:1px solid var(--border);border-radius:999px;background:#fff;font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--muted)}
  .access-actions{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:18px}
  .placeholder{margin-top:18px;padding:14px 16px;border:1px dashed var(--border);border-radius:18px;background:#fff}
  .placeholder-title{font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--muted)}
  .placeholder-copy{margin-top:8px;font-size:14px;color:#475569}
  .hidden-row{display:none}
  @media (max-width: 720px){.wrap{padding:16px}.head,.toolbar,.access-actions,.role-helper,.perm-row{flex-direction:column;align-items:stretch}.toggle{align-self:flex-start}}
</style>
@endpush

@section('content')
  <div class="wrap">
    <div class="head">
      <div>
        <h1 class="access-title">Access Control</h1>
        <p class="subtitle">Manage what each role can see and update across the admin system. Permissions are grouped by module and described in plain language for faster review.</p>
      </div>
      <div style="display:flex;gap:8px">
        <a class="btn secondary" href="{{ route('admin.settings') }}">Back to Settings</a>
      </div>
    </div>
    @if (session('ok'))
      <div class="flash">{{ session('ok') }}</div>
    @endif

    <section class="info-card">
      <div class="info-card-title">Live Workspace</div>
      <div class="info-card-copy">This is the live access-control workspace. Changes here affect what each role can view or manage.</div>
      <div>
        <span class="info-card-warning">Owner has full access and cannot be restricted.</span>
      </div>
    </section>

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

        <section class="matrix-card">
          <div class="matrix-head">
            <div>
              <h2 class="matrix-title">Permission Matrix</h2>
              <p class="matrix-copy">Quick overview of what each role can view, create, edit, delete, export, and approve across key admin modules.</p>
            </div>
            <span class="matrix-note">Read-only summary</span>
          </div>
          <div class="matrix-wrap">
            <table class="matrix-table">
              <thead>
                <tr>
                  <th>Module</th>
                  <th>View</th>
                  <th>Create</th>
                  <th>Edit</th>
                  <th>Delete</th>
                  <th>Export</th>
                  <th>Approve</th>
                </tr>
              </thead>
              <tbody>
                @foreach(($permissionMatrix ?? []) as $row)
                  <tr>
                    <td class="matrix-module">{{ $row['module'] }}</td>
                    @foreach(['view', 'create', 'edit', 'delete', 'export', 'approve'] as $column)
                      @php $entry = $row[$column] ?? ['type' => 'restricted', 'label' => 'N/A']; @endphp
                      <td>
                        <span class="matrix-badge {{ $entry['type'] }}">
                          <span class="matrix-icon">{{ $entry['type'] === 'allowed' ? '✓' : ($entry['type'] === 'denied' ? '✕' : '•') }}</span>
                          <span>{{ $entry['label'] }}</span>
                        </span>
                      </td>
                    @endforeach
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="muted" style="font-size:11px">This matrix is a summary. Use the role tabs below to review detailed module permissions.</div>
        </section>

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

        <div class="access-actions">
          <div style="display:flex;gap:8px">
            <a class="btn secondary" href="{{ route('admin.settings') }}">Back to Settings</a>
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
@endsection

@push('scripts')
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
@endpush
