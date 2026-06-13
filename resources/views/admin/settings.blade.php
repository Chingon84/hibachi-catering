<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - Settings</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    body{background:var(--bg)}
    .settings-shell{display:grid;gap:20px}
    .settings-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:4px 0 2px}
    .settings-kicker{display:inline-flex;align-items:center;padding:6px 10px;border-radius:999px;border:1px solid #e2e8f0;background:#fff;color:#64748b;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
    .settings-copy{margin:12px 0 0;max-width:880px;color:var(--muted);font-size:14px;line-height:1.7}
    .settings-summary{display:flex;flex-wrap:wrap;gap:10px}
    .summary-pill{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border:1px solid #e2e8f0;border-radius:999px;background:#fff;color:#475569;font-size:12px;font-weight:700}
    .settings-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));align-items:stretch;gap:18px}
    .settings-card{display:grid;grid-template-rows:auto auto auto 1fr auto;gap:16px;padding:20px;min-height:360px;border:1px solid var(--border);border-radius:20px;background:linear-gradient(180deg,#fff 0%,#fcfcfe 100%);box-shadow:0 10px 24px rgba(15,23,42,.05)}
    .settings-card.priority{border-color:rgba(178,30,39,.18);box-shadow:0 14px 34px rgba(178,30,39,.08)}
    .settings-card.priority .card-accent{background:linear-gradient(90deg,#b21e27 0%,#d84a55 100%)}
    .card-accent{height:4px;width:72px;border-radius:999px;background:#e2e8f0}
    .card-kicker{font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#64748b}
    .card-title{margin:0;font-size:20px;line-height:1.15;color:#0f172a}
    .card-copy{margin:8px 0 0;color:#475569;font-size:14px;line-height:1.65}
    .card-status{display:flex;flex-wrap:wrap;gap:8px}
    .status-chip{display:inline-flex;align-items:center;padding:6px 10px;border-radius:999px;border:1px solid #e2e8f0;background:#f8fafc;color:#475569;font-size:11px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
    .status-chip.accent{border-color:#fecaca;background:#fff5f5;color:#b21e27}
    .status-chip.success{border-color:#bbf7d0;background:#ecfdf5;color:#166534}
    .status-chip.warning{border-color:#fed7aa;background:#fff7ed;color:#c2410c}
    .status-chip.dark{border-color:#cbd5e1;background:#f8fafc;color:#334155}
    .card-highlights{display:grid;align-content:start;gap:8px;padding:14px;border:1px solid #edf2f7;border-radius:16px;background:#f8fafc}
    .card-highlights-title{font-size:11px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#64748b}
    .highlight-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
    .highlight-item{display:flex;align-items:center;gap:8px;min-height:44px;padding:9px 10px;border:1px solid #e2e8f0;border-radius:12px;background:#fff;color:#334155;font-size:13px;font-weight:600}
    .highlight-dot{width:8px;height:8px;border-radius:999px;background:#b21e27;flex:0 0 8px}
    .card-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:auto}
    .btn.ghost{background:#fff;color:#0f172a;border:1px solid #d7dde7;box-shadow:none}
    .btn.ghost:hover{background:#f8fafc;border-color:#cbd5e1}
    .btn.disabled,.btn[aria-disabled="true"]{opacity:.55;pointer-events:none}
    .utility-row{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:14px 16px;border:1px solid var(--border);border-radius:18px;background:#fff}
    .utility-copy{color:#475569;font-size:14px;line-height:1.6}
    @media (max-width: 1480px){.settings-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media (max-width: 840px){
      .settings-head{flex-direction:column}
      .settings-grid{grid-template-columns:1fr}
      .highlight-list{grid-template-columns:1fr}
      .utility-row{flex-direction:column;align-items:flex-start}
    }
  </style>
</head>
<body>
  @php
    $user = auth()->user();
    $canManageTeam = (bool) ($user?->hasPermission('team.manage') ?? false);
    $canViewTeam = (bool) ($user?->hasPermission('team.view') ?? false);
    $canViewTrash = (bool) ($user?->hasPermission('trash.view') ?? false);
  @endphp
  <div class="container">
    <div class="settings-shell">
      <div class="settings-head">
        <div>
          <div class="settings-kicker">Admin Control Center</div>
          <p class="settings-copy">Administrative control center for the settings that matter right now: business identity, reservations, billing, staffing, permissions, security, and retention. Existing admin modules remain in place; this hub organizes the next layer without breaking current workflows.</p>
        </div>
        <a class="btn secondary" href="{{ route('admin.dashboard') }}">Back</a>
      </div>

      <div class="settings-summary">
        <span class="summary-pill">Settings Hub</span>
        <span class="summary-pill">{{ count($sections ?? []) }} sections</span>
        <span class="summary-pill">3 modules live</span>
        <span class="summary-pill">Current links preserved</span>
      </div>

      <div class="settings-grid">
        @foreach(($sections ?? []) as $section)
          <section class="settings-card {{ !empty($section['priority']) ? 'priority' : '' }}">
            <div class="card-accent"></div>
            <div>
              <div class="card-kicker">{{ $section['category'] }}</div>
              <h2 class="card-title">{{ $section['title'] }}</h2>
              <p class="card-copy">{{ $section['description'] }}</p>
            </div>

            <div class="card-status">
              @foreach(($section['status'] ?? []) as $chip)
                <span class="status-chip {{ $chip['tone'] ?? 'dark' }}">{{ $chip['label'] }}</span>
              @endforeach
            </div>

            <div class="card-highlights">
              <div class="card-highlights-title">Key Controls</div>
              <div class="highlight-list">
                @foreach(($section['highlights'] ?? []) as $item)
                  <div class="highlight-item">
                    <span class="highlight-dot"></span>
                    <span>{{ $item }}</span>
                  </div>
                @endforeach
              </div>
            </div>

            <div class="card-actions">
              @if(!empty($section['primaryAction']))
                <a class="btn" href="{{ $section['primaryAction']['url'] }}">{{ $section['primaryAction']['label'] }}</a>
              @endif
              @if(!empty($section['secondaryAction']))
                <a class="btn ghost" href="{{ $section['secondaryAction']['url'] }}">{{ $section['secondaryAction']['label'] }}</a>
              @endif
            </div>
          </section>
        @endforeach
      </div>

      <div class="utility-row">
        <div class="utility-copy">
          Business Profile, Reservation Rules, and Menu & Pricing Rules are now active. Access Control, Team Directory, and Trash continue to use their existing modules, while the remaining settings workspaces stay routed as placeholders so the admin panel can grow without dead ends.
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
          @if($canManageTeam)
            <a class="btn secondary" href="{{ route('admin.team.permissions') }}">Access Control</a>
          @endif
          @if($canViewTeam)
            <a class="btn secondary" href="{{ route('admin.team.index') }}">Team Directory</a>
          @endif
          @if($canViewTrash)
            <a class="btn secondary" href="{{ route('admin.trash') }}">Trash</a>
          @endif
          @unless($canManageTeam || $canViewTeam || $canViewTrash)
            <span class="summary-pill">No team access granted</span>
          @endunless
        </div>
      </div>
    </div>
  </div>
</body>
</html>
