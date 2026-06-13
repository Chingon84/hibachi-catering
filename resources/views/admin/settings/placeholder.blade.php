<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $section['title'] ?? 'Settings' }}</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    body{background:var(--bg)}
    .page-stack{display:grid;gap:18px}
    .page-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px}
    .page-title{margin:0;font-size:30px;line-height:1.05;letter-spacing:-.03em}
    .page-copy{margin:10px 0 0;max-width:760px;color:var(--muted);font-size:14px;line-height:1.65}
    .eyebrow{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;border:1px solid #e2e8f0;background:#fff;color:#64748b;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;margin-bottom:12px}
    .grid{display:grid;grid-template-columns:minmax(0,1.7fr) minmax(300px,1fr);gap:16px}
    .panel{display:grid;gap:16px;padding:20px;border:1px solid var(--border);border-radius:20px;background:#fff;box-shadow:0 10px 24px rgba(15,23,42,.05)}
    .panel-title{margin:0;font-size:18px;line-height:1.2;color:#0f172a}
    .panel-copy{margin:8px 0 0;color:#475569;font-size:14px;line-height:1.6}
    .status-row{display:flex;flex-wrap:wrap;gap:8px}
    .status-chip{display:inline-flex;align-items:center;padding:6px 10px;border-radius:999px;border:1px solid #e2e8f0;background:#f8fafc;color:#475569;font-size:11px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
    .status-chip.muted{border-color:#e2e8f0;background:#f8fafc;color:#64748b}
    .status-chip.accent{border-color:#fecaca;background:#fff5f5;color:#b21e27}
    .status-chip.success{border-color:#bbf7d0;background:#ecfdf5;color:#166534}
    .status-chip.warning{border-color:#fed7aa;background:#fff7ed;color:#c2410c}
    .status-chip.dark{border-color:#cbd5e1;background:#f8fafc;color:#334155}
    .field-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
    .field-item{display:flex;align-items:center;gap:10px;padding:12px 14px;border:1px solid #e2e8f0;border-radius:14px;background:#f8fafc;color:#334155;font-size:14px;font-weight:600}
    .field-bullet{width:8px;height:8px;border-radius:999px;background:#b21e27;flex:0 0 8px}
    .coming-card{display:grid;gap:10px;padding:16px;border:1px dashed #d7dde7;border-radius:16px;background:#f8fafc}
    .coming-title{font-size:12px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#64748b}
    .coming-copy{color:#475569;font-size:14px;line-height:1.6}
    .action-stack{display:flex;flex-wrap:wrap;gap:10px}
    .btn.ghost{background:#fff;color:#0f172a;border:1px solid #d7dde7;box-shadow:none}
    .btn.ghost:hover{background:#f8fafc;border-color:#cbd5e1}
    .live-link-list{display:grid;gap:10px}
    .live-link{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:12px 14px;border:1px solid #e2e8f0;border-radius:14px;background:#fff;color:#0f172a;text-decoration:none}
    .live-link:hover{background:#f8fafc;border-color:#cbd5e1}
    .meta-grid{display:grid;gap:12px}
    .meta-card{padding:14px;border:1px solid #e2e8f0;border-radius:16px;background:#fff}
    .meta-label{font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8}
    .meta-value{margin-top:8px;font-size:15px;font-weight:700;color:#0f172a}
    @media (max-width: 920px){
      .page-head{flex-direction:column}
      .grid{grid-template-columns:1fr}
      .field-list{grid-template-columns:1fr}
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="page-stack">
      <div class="page-head">
        <div>
          <div class="eyebrow">{{ $section['category'] ?? 'Settings' }}</div>
          <h1 class="page-title">{{ $section['title'] ?? 'Settings Section' }}</h1>
          <p class="page-copy">{{ $section['description'] ?? 'This settings workspace is being prepared.' }}</p>
        </div>
        <div class="action-stack">
          <a class="btn secondary" href="{{ $backUrl ?? route('admin.settings') }}">Back</a>
          <span class="status-chip accent">Coming Soon</span>
        </div>
      </div>

      <div class="grid">
        <section class="panel">
          <div>
            <h2 class="panel-title">Planned Controls</h2>
            <p class="panel-copy">This page is already routed and styled so the admin panel can grow safely. The controls below represent the first rollout scope for this settings area.</p>
          </div>

          <div class="status-row">
            @foreach(($section['status'] ?? []) as $chip)
              <span class="status-chip {{ $chip['tone'] ?? 'dark' }}">{{ $chip['label'] }}</span>
            @endforeach
          </div>

          <div class="field-list">
            @foreach(($section['fields'] ?? []) as $field)
              <div class="field-item">
                <span class="field-bullet"></span>
                <span>{{ $field }}</span>
              </div>
            @endforeach
          </div>

          <div class="coming-card">
            <div class="coming-title">Implementation Status</div>
            <div class="coming-copy">The route and view are live now, but persistence and business logic are intentionally deferred. This keeps the settings map professional and clickable without forcing premature database changes.</div>
          </div>
        </section>

        <aside class="panel">
          <div>
            <h2 class="panel-title">Workspace Notes</h2>
            <p class="panel-copy">Use this page as the rollout placeholder for the real settings UI. Existing linked modules remain fully functional while this section is under construction.</p>
          </div>

          <div class="meta-grid">
            <div class="meta-card">
              <div class="meta-label">Current State</div>
              <div class="meta-value">Structure ready</div>
            </div>
            <div class="meta-card">
              <div class="meta-label">Persistence</div>
              <div class="meta-value">Not connected yet</div>
            </div>
            <div class="meta-card">
              <div class="meta-label">Rollout Mode</div>
              <div class="meta-value">Coming Soon</div>
            </div>
          </div>

          @if(!empty($section['liveLinks']))
            <div>
              <h2 class="panel-title" style="font-size:16px">Live Links</h2>
              <p class="panel-copy">These modules already exist and remain the active source of truth until this settings workspace is implemented.</p>
            </div>
            <div class="live-link-list">
              @foreach($section['liveLinks'] as $link)
                <a class="live-link" href="{{ $link['url'] }}">
                  <span>{{ $link['label'] }}</span>
                  <span class="status-chip success">Live</span>
                </a>
              @endforeach
            </div>
          @endif
        </aside>
      </div>
    </div>
  </div>
</body>
</html>
