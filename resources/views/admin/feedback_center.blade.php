<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - Feedback Center</title>
  <link rel="stylesheet" href="/assets/admin.css">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    :root{
      --panel-border:#e7ebf3;
      --panel-shadow:0 18px 40px rgba(15,23,42,.06);
      --surface-soft:#fbfcfe;
      --text-strong:#0f172a;
      --tone-open-bg:#fef2f2;
      --tone-open-bd:#fecaca;
      --tone-open-tx:#b91c1c;
      --tone-review-bg:#fff7ed;
      --tone-review-bd:#fed7aa;
      --tone-review-tx:#b45309;
      --tone-resolved-bg:#ecfdf5;
      --tone-resolved-bd:#a7f3d0;
      --tone-resolved-tx:#047857;
      --tone-escalated-bg:#f5f3ff;
      --tone-escalated-bd:#ddd6fe;
      --tone-escalated-tx:#7c3aed;
      --tone-positive-bg:#eff6ff;
      --tone-positive-bd:#bfdbfe;
      --tone-positive-tx:#1d4ed8;
      --tone-neutral-bg:#f8fafc;
      --tone-neutral-bd:#e2e8f0;
      --tone-neutral-tx:#475569;
    }
    *{box-sizing:border-box}
    .container{width:calc(100vw - 24px);max-width:none;margin:20px 12px;padding:0 12px}
    .page-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:14px}
    .page-copy{max-width:760px}
    .view-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px}
    .view-tab{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 14px;border-radius:999px;border:1px solid #d8deea;background:#fff;color:#334155;font-size:13px;font-weight:800;text-decoration:none;line-height:1}
    .view-tab.active{background:#0f172a;border-color:#0f172a;color:#fff;box-shadow:0 12px 24px rgba(15,23,42,.14)}
    .eyebrow{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;border:1px solid #e6e9f2;background:#fff;color:#475569;font-size:12px;font-weight:700;letter-spacing:.04em;text-transform:uppercase}
    .title{margin:12px 0 6px;font-size:28px;line-height:1.08;letter-spacing:-.03em;color:var(--text-strong)}
    .subtitle{margin:0;color:#64748b;font-size:14px;max-width:640px}
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:42px;padding:0 16px;border-radius:12px;text-decoration:none;box-shadow:0 10px 24px rgba(178,30,39,.18)}
    .btn.secondary{box-shadow:none}
    .stats-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:14px;margin-bottom:14px}
    .stat-card{position:relative;overflow:hidden;padding:16px;border:1px solid var(--panel-border);border-radius:18px;background:linear-gradient(180deg,#fff 0%,#fbfcff 100%);box-shadow:var(--panel-shadow)}
    .stat-card::after{content:"";position:absolute;right:-18px;top:-18px;width:74px;height:74px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,.9) 0%,rgba(255,255,255,0) 70%)}
    .stat-top{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:16px}
    .stat-label{font-size:12px;font-weight:800;color:#475569;letter-spacing:.03em;text-transform:uppercase}
    .stat-value{font-size:30px;font-weight:800;line-height:1;letter-spacing:-.04em;color:var(--text-strong)}
    .stat-note{margin-top:8px;font-size:12px;color:#94a3b8}
    .stat-footer{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:10px}
    .trend-chip{display:inline-flex;align-items:center;gap:6px;padding:5px 8px;border-radius:999px;font-size:11px;font-weight:800}
    .trend-chip.up{background:#ecfdf5;color:#047857}
    .trend-chip.down{background:#fff7ed;color:#c2410c}
    .trend-chip.flat{background:#f8fafc;color:#475569}
    .trend-spark{display:flex;align-items:flex-end;gap:3px;height:18px}
    .trend-spark span{display:block;width:5px;border-radius:999px;background:#cbd5e1}
    .tone-open .trend-spark span{background:linear-gradient(180deg,#f87171 0%,#dc2626 100%)}
    .tone-positive .trend-spark span{background:linear-gradient(180deg,#60a5fa 0%,#2563eb 100%)}
    .tone-review .trend-spark span{background:linear-gradient(180deg,#fbbf24 0%,#d97706 100%)}
    .tone-neutral .trend-spark span{background:linear-gradient(180deg,#94a3b8 0%,#475569 100%)}
    .tone-escalated .trend-spark span{background:linear-gradient(180deg,#fb923c 0%,#ea580c 100%)}
    .tone-resolved .trend-spark span{background:linear-gradient(180deg,#4ade80 0%,#16a34a 100%)}
    .stat-icon{width:42px;height:42px;border-radius:14px;display:inline-flex;align-items:center;justify-content:center;border:1px solid transparent}
    .stat-icon svg{width:18px;height:18px}
    .tone-open .stat-icon{background:var(--tone-open-bg);border-color:var(--tone-open-bd);color:var(--tone-open-tx)}
    .tone-review .stat-icon{background:var(--tone-review-bg);border-color:var(--tone-review-bd);color:var(--tone-review-tx)}
    .tone-resolved .stat-icon{background:var(--tone-resolved-bg);border-color:var(--tone-resolved-bd);color:var(--tone-resolved-tx)}
    .tone-escalated .stat-icon{background:var(--tone-escalated-bg);border-color:var(--tone-escalated-bd);color:var(--tone-escalated-tx)}
    .tone-positive .stat-icon{background:var(--tone-positive-bg);border-color:var(--tone-positive-bd);color:var(--tone-positive-tx)}
    .tone-neutral .stat-icon{background:var(--tone-neutral-bg);border-color:var(--tone-neutral-bd);color:var(--tone-neutral-tx)}
    .surface-card{background:linear-gradient(180deg,#fff 0%,#fcfdff 100%);border:1px solid var(--panel-border);border-radius:18px;box-shadow:var(--panel-shadow)}
    .surface-body{padding:16px}
    .filter-card .surface-body{padding:14px 16px}
    .filter-bar{display:grid;grid-template-columns:minmax(280px,1.8fr) repeat(6,minmax(118px,.7fr)) auto;gap:10px;align-items:end}
    .field-label{display:block;margin:0 0 6px;font-size:11px;font-weight:800;color:#64748b;letter-spacing:.07em;text-transform:uppercase}
    .input,.select{width:100%;min-height:42px;border:1px solid #d8deea;border-radius:12px;background:#fff}
    .input:focus,.select:focus{outline:none;border-color:#c6d1e3;box-shadow:0 0 0 4px rgba(148,163,184,.14)}
    .filter-field.search-field{min-width:0}
    .filter-field.search-field .input{background:linear-gradient(180deg,#fff 0%,#fcfdff 100%)}
    .filter-actions{display:flex;align-items:end;gap:10px;justify-content:flex-end}
    .layout-grid{display:block;margin-top:12px}
    .toolbar-card{margin-top:10px}
    .toolbar-card .surface-body{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:12px 16px}
    .quick-pills{display:flex;flex-wrap:wrap;gap:8px;align-items:center;min-width:0}
    .toolbar-meta{display:flex;align-items:center;gap:10px;flex:0 0 auto;color:#64748b;font-size:12px;font-weight:700;white-space:nowrap}
    .toolbar-meta .meta-dot{width:4px;height:4px;border-radius:999px;background:#cbd5e1}
    .tab-strip{display:flex;flex-wrap:wrap;gap:8px}
    .tab-chip{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:999px;border:1px solid #d8deea;background:#fff;color:#334155;font-size:12px;font-weight:800;text-decoration:none;line-height:1}
    .tab-chip.active{background:#0f172a;border-color:#0f172a;color:#fff;box-shadow:0 10px 20px rgba(15,23,42,.14)}
    .content-card{overflow:hidden;border-top:1px solid #eef2f7}
    .section-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid #edf2f7}
    .section-title{margin:0;font-size:18px;font-weight:800;color:var(--text-strong)}
    .section-subtitle{margin:4px 0 0;color:#64748b;font-size:13px}
    .muted-kicker{font-size:12px;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em}
    .table-wrap{overflow:auto;border:1px solid #e8edf4;border-radius:16px;background:#fff}
    .data-table{width:100%;border-collapse:separate;border-spacing:0;min-width:860px}
    .queue-table{min-width:0;table-layout:fixed}
    .queue-table col.case{width:132px}
    .queue-table col.received{width:110px}
    .queue-table col.event-date{width:110px}
    .queue-table col.team-member{width:150px}
    .queue-table col.source{width:110px}
    .queue-table col.summary{width:auto}
    .queue-table col.status{width:112px}
    .queue-table col.owner{width:128px}
    .queue-table col.actions{width:220px}
    .data-table th,.data-table td{padding:13px 14px;text-align:left;vertical-align:top}
    .data-table thead th{background:#f8fafc;border-bottom:1px solid #e8edf4;color:#475569;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap}
    .data-table tbody tr{background:#fff;transition:background-color .16s ease}
    .data-table tbody tr:hover{background:#fafcff}
    .data-table tbody tr + tr td{border-top:1px solid #edf1f6}
    .data-table tbody tr.is-clickable{cursor:pointer}
    .cell-strong{font-weight:800;color:var(--text-strong)}
    .cell-copy{max-width:340px;color:#475569;line-height:1.55}
    .queue-table .cell-copy{max-width:none;white-space:normal;word-break:break-word}
    .cell-meta{display:block;color:#64748b;font-size:12px;margin-top:4px}
    .analytics-shell{display:grid;gap:16px}
    .analytics-header-card,.analytics-filter-card,.analytics-summary-card,.analytics-chart-card,.analytics-kpi-card{border:1px solid var(--panel-border);border-radius:16px;background:#fff;box-shadow:0 1px 2px rgba(15,23,42,.04)}
    .analytics-header-card{padding:24px;background:linear-gradient(180deg,#ffffff 0%,#fbfcff 100%)}
    .analytics-header-title{margin:0;font-size:28px;font-weight:800;letter-spacing:-.03em;color:var(--text-strong)}
    .analytics-header-copy{margin:8px 0 0;max-width:860px;color:#64748b;font-size:14px;line-height:1.6}
    .analytics-kpi-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}
    .analytics-kpi-card{padding:20px}
    .analytics-kpi-label{font-size:12px;font-weight:800;letter-spacing:.05em;text-transform:uppercase;color:#64748b}
    .analytics-kpi-value{margin-top:10px;font-size:30px;font-weight:700;letter-spacing:-.03em;color:var(--text-strong)}
    .analytics-kpi-note{margin-top:8px;color:#94a3b8;font-size:12px;line-height:1.5}
    .analytics-filter-card{padding:16px}
    .analytics-filter-grid{display:grid;grid-template-columns:minmax(240px,1.4fr) repeat(7,minmax(120px,.7fr)) auto;gap:12px;align-items:end}
    .analytics-actions{display:flex;flex-wrap:wrap;gap:10px;justify-content:flex-end}
    .analytics-summary-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}
    .analytics-summary-card{padding:18px}
    .analytics-summary-top{display:flex;align-items:flex-start;justify-content:space-between;gap:10px}
    .analytics-summary-label{font-size:12px;font-weight:800;letter-spacing:.05em;text-transform:uppercase;color:#64748b}
    .analytics-summary-icon{width:36px;height:36px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;background:#f8fafc;border:1px solid #e2e8f0;color:#475569}
    .analytics-summary-icon svg{width:16px;height:16px}
    .analytics-summary-value{margin-top:12px;font-size:28px;font-weight:700;letter-spacing:-.03em;color:var(--text-strong)}
    .analytics-summary-copy{margin-top:6px;color:#94a3b8;font-size:12px;line-height:1.5}
    .analytics-charts-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:24px}
    .analytics-chart-card{padding:24px}
    .analytics-chart-title{margin:0;font-size:18px;font-weight:700;color:var(--text-strong)}
    .analytics-chart-copy{margin:6px 0 0;color:#64748b;font-size:13px;line-height:1.5}
    .chart-wrap{position:relative;height:300px;margin-top:18px}
    .chart-wrap.compact{height:280px}
    .badge{display:inline-flex;align-items:center;justify-content:center;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:800;line-height:1;border:1px solid transparent;white-space:nowrap;box-shadow:inset 0 0 0 1px rgba(255,255,255,.2)}
    .badge.open,.badge.denied{background:#fff1f2;border-color:#fda4af;color:#be123c}
    .badge.unauthorized{background:#fee2e2;border-color:#f87171;color:#991b1b}
    .badge.escalated,.badge.urgent,.badge.watch{background:#fff7ed;border-color:#fdba74;color:#c2410c}
    .badge.in-review,.badge.pending,.badge.operational,.badge.medium{background:#fef3c7;border-color:#fbbf24;color:#92400e}
    .badge.logged,.badge.shared,.badge.neutral,.badge.alert{background:#f1f5f9;border-color:#cbd5e1;color:#475569}
    .badge.resolved,.badge.approved,.badge.authorized,.badge.healthy,.badge.worked{background:#ecfdf5;border-color:#86efac;color:#166534}
    .badge.positive,.badge.low,.badge.reviewed,.badge.trend,.badge.cancelled{background:#eff6ff;border-color:#93c5fd;color:#1d4ed8}
    .row-actions{display:flex;align-items:center;gap:6px;flex-wrap:wrap;white-space:nowrap}
    .action-link{display:inline-flex;align-items:center;justify-content:center;padding:7px 10px;border-radius:10px;border:1px solid #d8deea;background:#fff;color:#334155;font-size:12px;font-weight:700;text-decoration:none}
    .action-link:hover{background:#f8fafc}
    .action-link.primary{background:#0f172a;border-color:#0f172a;color:#fff}
    .action-link.primary:hover{background:#1e293b}
    .action-link.resolve{background:#ecfdf5;border-color:#86efac;color:#166534}
    .action-link.escalate{background:#fff7ed;border-color:#fdba74;color:#c2410c}
    .action-link.assign{background:#f8fafc;border-color:#cbd5e1;color:#334155}
    .detail-panel{position:fixed;top:18px;right:18px;bottom:18px;width:min(420px,calc(100vw - 28px));display:flex;flex-direction:column;gap:12px;z-index:40;pointer-events:none}
    .detail-shell{height:100%;display:flex;flex-direction:column;transform:translateX(calc(100% + 24px));opacity:0;transition:transform .22s ease,opacity .22s ease}
    .detail-panel.open{pointer-events:auto}
    .detail-panel.open .detail-shell{transform:translateX(0);opacity:1}
    .detail-overlay{position:fixed;inset:0;background:rgba(15,23,42,.22);backdrop-filter:blur(2px);opacity:0;pointer-events:none;transition:opacity .2s ease;z-index:35}
    .detail-overlay.open{opacity:1;pointer-events:auto}
    .detail-card{padding:16px}
    .detail-card.drawer{height:100%;overflow:auto;border-top-left-radius:22px;border-bottom-left-radius:22px;border-top-right-radius:18px;border-bottom-right-radius:18px;box-shadow:0 24px 60px rgba(15,23,42,.18)}
    .drawer-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:4px}
    .drawer-close{display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:12px;border:1px solid #d8deea;background:#fff;color:#334155;text-decoration:none;font-size:18px;line-height:1}
    .drawer-close:hover{background:#f8fafc}
    .detail-tag{display:inline-flex;align-items:center;gap:8px;margin-bottom:12px;padding:6px 10px;border-radius:999px;background:#f8fafc;border:1px solid #e2e8f0;color:#475569;font-size:12px;font-weight:700}
    .detail-title{margin:0 0 8px;font-size:18px;line-height:1.3;color:var(--text-strong)}
    .detail-copy{margin:0;color:#64748b;font-size:14px;line-height:1.6}
    .fact-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin:14px 0}
    .fact-card{padding:12px;border-radius:14px;background:#f8fafc;border:1px solid #e6ecf3}
    .fact-label{display:block;font-size:11px;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px}
    .fact-value{font-size:13px;font-weight:800;color:var(--text-strong)}
    .detail-block + .detail-block{margin-top:12px;padding-top:12px;border-top:1px solid #edf1f6}
    .detail-list{display:grid;gap:0;margin:0;padding:4px 0 0;list-style:none}
    .detail-list li{position:relative;display:grid;grid-template-columns:26px 1fr;gap:12px;padding:0 0 16px}
    .detail-list li:last-child{padding-bottom:0}
    .timeline-rail{position:relative;display:flex;justify-content:center}
    .timeline-rail::after{content:"";position:absolute;top:14px;bottom:-18px;left:50%;width:2px;transform:translateX(-50%);background:linear-gradient(180deg,#dbe4ee 0%,#eef2f7 100%)}
    .detail-list li:last-child .timeline-rail::after{display:none}
    .timeline-dot{width:12px;height:12px;border-radius:999px;background:#0f172a;box-shadow:0 0 0 4px #f8fafc;margin-top:2px;position:relative;z-index:1}
    .timeline-copy{font-size:13px;color:#475569;line-height:1.5;padding-bottom:2px}
    .timeline-date{display:block;margin-bottom:4px;font-size:11px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:#94a3b8}
    .preview-empty{padding:20px;border:1px dashed #d6dce8;border-radius:16px;background:linear-gradient(180deg,#fbfcff 0%,#f8fafc 100%)}
    .preview-skeleton{display:grid;gap:10px;margin-top:12px}
    .preview-skeleton span{display:block;height:10px;border-radius:999px;background:linear-gradient(90deg,#eef2f7 0%,#f8fafc 50%,#eef2f7 100%)}
    .preview-skeleton span:nth-child(1){width:68%}
    .preview-skeleton span:nth-child(2){width:92%}
    .preview-skeleton span:nth-child(3){width:76%}
    .empty-state{display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:28px 20px;min-height:220px;border:1px dashed #d6dce8;border-radius:18px;background:linear-gradient(180deg,#fbfcff 0%,#f8fafc 100%)}
    .empty-state.compact{min-height:196px;padding:24px 18px}
    .empty-icon{width:52px;height:52px;border-radius:16px;background:#fff;border:1px solid #e2e8f0;display:inline-flex;align-items:center;justify-content:center;color:#64748b;box-shadow:0 10px 24px rgba(15,23,42,.06)}
    .empty-icon svg{width:24px;height:24px}
    .empty-title{margin:14px 0 6px;font-size:18px;line-height:1.2;color:var(--text-strong)}
    .empty-copy{margin:0 0 14px;max-width:420px;color:#64748b;font-size:13px;line-height:1.6}
    .compact-empty-action{display:inline-flex;align-items:center;justify-content:center}
    .summary-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:14px}
    .summary-mini{padding:12px;border-radius:16px;background:#fff;border:1px solid #e8edf4}
    .summary-mini .value{font-size:22px;font-weight:800;color:var(--text-strong)}
    .summary-mini .label{font-size:12px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:.06em}
    .trend-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:14px}
    .trend-card{padding:14px;border-radius:16px;background:#fff;border:1px solid #e8edf4}
    .trend-card h4{margin:0 0 8px;font-size:14px;color:var(--text-strong)}
    .trend-bars{display:grid;gap:8px}
    .trend-bar-row{display:grid;grid-template-columns:100px 1fr 34px;gap:10px;align-items:center;font-size:12px;color:#475569}
    .trend-bar{height:8px;border-radius:999px;background:#e2e8f0;overflow:hidden}
    .trend-fill{height:100%;border-radius:999px;background:linear-gradient(90deg,#b21e27 0%,#ef4444 100%)}
    .alert-list{display:grid;gap:10px}
    .alert-card{padding:14px;border-radius:16px;border:1px solid #f3d2d7;background:linear-gradient(180deg,#fff 0%,#fff7f8 100%)}
    .alert-top{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:8px}
    .helper-list{display:grid;gap:8px;margin:0;padding-left:18px;color:#64748b}
    @media (max-width: 1280px){.stats-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.queue-table col.actions{width:180px}.filter-bar{grid-template-columns:minmax(220px,1.4fr) repeat(3,minmax(140px,.8fr)) repeat(3,minmax(128px,.7fr)) auto}.analytics-kpi-grid,.analytics-summary-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.analytics-filter-grid{grid-template-columns:repeat(4,minmax(0,1fr))}.analytics-charts-grid{grid-template-columns:1fr 1fr}}
    @media (max-width: 980px){.filter-bar{grid-template-columns:1fr 1fr 1fr}.trend-grid,.summary-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.queue-table{min-width:980px}.toolbar-card .surface-body{flex-direction:column;align-items:flex-start}.toolbar-meta{width:100%;justify-content:flex-start}.analytics-filter-grid,.analytics-charts-grid{grid-template-columns:1fr 1fr}.analytics-actions{justify-content:flex-start}}
    @media (max-width: 720px){.page-head{flex-direction:column;align-items:stretch}.filter-bar,.stats-grid,.trend-grid,.summary-grid,.analytics-kpi-grid,.analytics-summary-grid,.analytics-filter-grid,.analytics-charts-grid{grid-template-columns:1fr}.fact-grid{grid-template-columns:1fr}.container{padding:0 10px}.detail-panel{top:10px;right:10px;bottom:10px;width:calc(100vw - 20px)}.filter-actions{justify-content:flex-start}.toolbar-card .surface-body{padding:12px 14px}.chart-wrap,.chart-wrap.compact{height:240px}.analytics-chart-card,.analytics-header-card,.analytics-filter-card,.analytics-summary-card,.analytics-kpi-card{padding:18px}}
  </style>
</head>
<body>
  @php
    $queryBase = array_filter([
      'q' => $filters['q'] ?? '',
      'status' => $filters['status'] ?? '',
      'type' => $filters['type'] ?? '',
      'date' => $filters['date'] ?? '',
      'chef' => $filters['chef'] ?? '',
      'staff_type' => $filters['staff_type'] ?? '',
      'source' => $filters['source'] ?? '',
    ], fn ($value) => $value !== '');
    $viewQueryBase = array_filter([
      'view' => $viewMode ?? 'cases',
      'q' => $filters['q'] ?? '',
      'status' => $filters['status'] ?? '',
      'type' => $filters['type'] ?? '',
      'date' => $filters['date'] ?? '',
      'chef' => $filters['chef'] ?? '',
      'staff_type' => $filters['staff_type'] ?? '',
      'source' => $filters['source'] ?? '',
    ], fn ($value) => $value !== '');
    $quickTabs = [
      'all-cases' => 'All Cases',
      'complaints' => 'Complaints',
      'good-feedback' => 'Good Feedback',
      'van-feedback' => 'Van Issues',
      'attendance' => 'Attendance',
      'days-off' => 'Days Off',
      'alerts' => 'Alerts',
    ];
    $tabMeta = [
      'all-cases' => ['title' => 'Unified Operations Queue', 'subtitle' => 'The full team queue for complaints, positive feedback, van issues, attendance incidents, and follow-up ownership.', 'count' => $allCases->count()],
      'complaints' => ['title' => 'Complaints log', 'subtitle' => 'Workbook-aligned complaint queue with category, assistant, action, and resolution status.', 'count' => $complaints->count()],
      'good-feedback' => ['title' => 'Good feedback log', 'subtitle' => 'Positive service recognition captured by event date, source, and assistant.', 'count' => $goodFeedback->count()],
      'van-feedback' => ['title' => 'Van issues log', 'subtitle' => 'Fleet and equipment issues tied to operations, accountability, and action taken.', 'count' => $vanFeedback->count()],
      'attendance' => ['title' => 'Attendance log', 'subtitle' => 'Incident tracking with units, authorization state, manager, and notes.', 'count' => $attendance->count()],
      'days-off' => ['title' => 'Days off log', 'subtitle' => 'Request tracking for approvals, denied days, unauthorized time, and notes.', 'count' => $daysOff->count()],
      'alerts' => ['title' => 'Alerts and escalations', 'subtitle' => 'Unauthorized patterns, escalations, and urgent operational alerts.', 'count' => $alerts->count()],
      'chef-summary' => ['title' => 'Team performance summary', 'subtitle' => 'Per-team-member rollup across requests, feedback, complaints, van issues, and attendance.', 'count' => $chefSummaries->count()],
      'monthly-trends' => ['title' => 'Monthly trends', 'subtitle' => 'Month-over-month totals for days off, unauthorized time, good feedback, and complaints.', 'count' => $monthlyTrends->count()],
    ];
    $icons = [
      'open' => '<path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm1 15h-2v-2h2zm0-4h-2V7h2z"/>',
      'positive' => '<path d="m9.55 16.6-3.9-3.9 1.4-1.4 2.5 2.5 7.4-7.4 1.4 1.4z"/>',
      'review' => '<path d="M12 4a8 8 0 1 0 8 8h-2a6 6 0 1 1-6-6V4zm1 0h7v7h-2V7.4l-4.3 4.3-1.4-1.4L16.6 6H13V4z"/>',
      'neutral' => '<path d="M4 4h16v2H4zm0 7h16v2H4zm0 7h10v2H4z"/>',
      'escalated' => '<path d="M13 3 4 14h6l-1 7 9-11h-6l1-7z"/>',
      'resolved' => '<path d="m9.55 16.6-3.9-3.9 1.4-1.4 2.5 2.5 7.4-7.4 1.4 1.4z"/>',
    ];
    $previewStatusClass = strtolower(str_replace(' ', '-', $preview['status'] ?? 'neutral'));
    $createBackUrl = request()->fullUrl();
    $quickCreateGroups = [
      'Feedback' => [
        ['label' => 'Complaint', 'icon' => 'alert-triangle', 'icon_class' => 'text-red-500', 'href' => route('admin.feedback.create', ['type' => 'complaint', 'back' => $createBackUrl])],
        ['label' => 'Good Feedback', 'icon' => 'thumb-up', 'icon_class' => 'text-green-500', 'href' => route('admin.feedback.create', ['type' => 'good-feedback', 'back' => $createBackUrl])],
        ['label' => 'Recognition', 'icon' => 'award', 'icon_class' => 'text-yellow-500', 'href' => route('admin.feedback.create', ['type' => 'good-feedback', 'back' => $createBackUrl])],
      ],
      'Operations' => [
        ['label' => 'Van Feedback', 'icon' => 'truck', 'icon_class' => 'text-orange-500', 'href' => route('admin.feedback.create', ['type' => 'van-feedback', 'back' => $createBackUrl])],
        ['label' => 'Attendance Incident', 'icon' => 'clock', 'icon_class' => 'text-blue-500', 'href' => route('admin.feedback.create', ['type' => 'attendance', 'back' => $createBackUrl])],
        ['label' => 'Fleet', 'icon' => 'car', 'icon_class' => 'text-indigo-500', 'href' => route('admin.feedback.create', ['type' => 'van-feedback', 'back' => $createBackUrl])],
      ],
      'Management' => [
        ['label' => 'Manager Note', 'icon' => 'clipboard-document-list', 'icon_class' => 'text-purple-500', 'href' => route('admin.feedback.create', ['type' => 'attendance', 'back' => $createBackUrl])],
      ],
    ];
    $panelBaseQuery = array_filter([
      'view' => $viewMode ?? 'cases',
      'tab' => $activeTab,
      'q' => $filters['q'] ?? '',
      'status' => $filters['status'] ?? '',
      'type' => $filters['type'] ?? '',
      'date' => $filters['date'] ?? '',
      'chef' => $filters['chef'] ?? '',
      'staff_type' => $filters['staff_type'] ?? '',
      'source' => $filters['source'] ?? '',
    ], fn ($value) => $value !== '');
    $isPreviewOpen = !empty($filters['item']) && (!empty($preview['facts']) || !empty($preview['sections']) || !empty($preview['history']));
  @endphp
  <div class="container">
    @if (session('ok'))
      <div class="surface-card" style="margin-bottom:12px">
        <div class="surface-body" style="padding:14px 18px;color:#166534;background:#ecfdf5;border-radius:18px">
          {{ session('ok') }}
        </div>
      </div>
    @endif

    <div class="page-head">
      <div class="page-copy">
        <div class="eyebrow">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="M4 4h16a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H8l-4 3v-3H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2zm3 5h10v2H7V9zm0 4h7v2H7v-2z"/></svg>
          Operations module
        </div>
        <h1 class="title">Feedback Center</h1>
        <p class="subtitle">Track customer complaints, staff/service feedback, van issues, attendance incidents, and resolution trends from one internal operations center.</p>
      </div>
      <x-admin.new-feedback-menu :groups="$quickCreateGroups" button-label="New Feedback" />
    </div>

    <div class="view-tabs">
      <a class="view-tab {{ ($viewMode ?? 'cases') === 'cases' ? 'active' : '' }}" href="{{ route('admin.feedback', array_merge($viewQueryBase, ['view' => 'cases'])) }}">Cases</a>
      <a class="view-tab {{ ($viewMode ?? 'cases') === 'analytics' ? 'active' : '' }}" href="{{ route('admin.feedback', array_merge($viewQueryBase, ['view' => 'analytics'])) }}">Analytics</a>
    </div>

    @if(($viewMode ?? 'cases') === 'cases')
    <div class="stats-grid">
      @foreach($stats as $stat)
        <div class="stat-card tone-{{ $stat['tone'] }}">
          <div class="stat-top">
            <div class="stat-label">{{ $stat['label'] }}</div>
            <div class="stat-icon">
              <svg viewBox="0 0 24 24" fill="currentColor">{!! $icons[$stat['tone']] ?? $icons['neutral'] !!}</svg>
            </div>
          </div>
          <div class="stat-value">{{ number_format($stat['value']) }}</div>
          <div class="stat-note">{{ $stat['note'] }}</div>
          <div class="stat-footer">
            <span class="trend-chip {{ $stat['trend_direction'] ?? 'flat' }}">{{ $stat['trend'] ?? 'No change' }}</span>
            <div class="trend-spark" aria-hidden="true">
              @foreach(($stat['spark'] ?? [1,1,1,1,1,1]) as $point)
                <span style="height: {{ max(6, (int) $point * 4) }}px"></span>
              @endforeach
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <div class="surface-card filter-card">
      <div class="surface-body">
        <form class="filter-bar" method="get" action="{{ route('admin.feedback') }}">
          <input type="hidden" name="view" value="cases">
          <input type="hidden" name="tab" value="{{ $activeTab }}">
          <div class="filter-field search-field">
            <label class="field-label" for="feedback-search">Search</label>
            <input class="input" id="feedback-search" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search IDs, staff member, category, summary, or owner">
          </div>
          <div class="filter-field">
            <label class="field-label" for="feedback-status">Status</label>
            <select class="select" id="feedback-status" name="status">
              <option value="">All statuses</option>
              @foreach($statusOptions as $statusOpt)
                <option value="{{ $statusOpt }}" {{ ($filters['status'] ?? '') === $statusOpt ? 'selected' : '' }}>{{ $statusOpt }}</option>
              @endforeach
            </select>
          </div>
          <div class="filter-field">
            <label class="field-label" for="feedback-type">Type</label>
            <select class="select" id="feedback-type" name="type">
              <option value="">All record types</option>
              @foreach($typeOptions as $typeOpt)
                <option value="{{ $typeOpt }}" {{ ($filters['type'] ?? '') === $typeOpt ? 'selected' : '' }}>{{ $typeOpt }}</option>
              @endforeach
            </select>
          </div>
          <div class="filter-field">
            <label class="field-label" for="feedback-date">Date</label>
            <input class="input" id="feedback-date" type="date" name="date" value="{{ $filters['date'] ?? '' }}">
          </div>
          <div class="filter-field">
            <label class="field-label" for="feedback-chef">Team Member</label>
            <select class="select" id="feedback-chef" name="chef">
              <option value="">All staff</option>
              @foreach($chefOptions as $chefOpt)
                <option value="{{ $chefOpt }}" {{ ($filters['chef'] ?? '') === $chefOpt ? 'selected' : '' }}>{{ $chefOpt }}</option>
              @endforeach
            </select>
          </div>
          <div class="filter-field">
            <label class="field-label" for="feedback-staff-type">Staff Type</label>
            <select class="select" id="feedback-staff-type" name="staff_type">
              <option value="">All staff types</option>
              @foreach($staffTypeOptions as $staffTypeOpt)
                <option value="{{ $staffTypeOpt }}" {{ ($filters['staff_type'] ?? '') === $staffTypeOpt ? 'selected' : '' }}>{{ $staffTypeOpt }}</option>
              @endforeach
            </select>
          </div>
          <div class="filter-field">
            <label class="field-label" for="feedback-source">Source</label>
            <select class="select" id="feedback-source" name="source">
              <option value="">All sources</option>
              @foreach($sourceOptions as $sourceOpt)
                <option value="{{ $sourceOpt }}" {{ ($filters['source'] ?? '') === $sourceOpt ? 'selected' : '' }}>{{ $sourceOpt }}</option>
              @endforeach
            </select>
          </div>
          <div class="filter-actions">
            <button class="btn secondary" type="submit">Apply</button>
            <a href="{{ route('admin.feedback', ['view' => 'cases', 'tab' => $activeTab]) }}" class="btn secondary">Reset</a>
          </div>
        </form>
      </div>
    </div>

    <div class="surface-card toolbar-card">
      <div class="surface-body">
        <div class="quick-pills">
          @foreach($quickTabs as $tabKey => $tabLabel)
            <a class="tab-chip {{ $activeTab === $tabKey ? 'active' : '' }}" href="{{ route('admin.feedback', array_merge($queryBase, ['view' => 'cases', 'tab' => $tabKey])) }}">{{ $tabLabel }}</a>
          @endforeach
        </div>
        <div class="toolbar-meta">
          <span>{{ number_format($tabMeta[$activeTab]['count']) }} visible</span>
          <span class="meta-dot" aria-hidden="true"></span>
          <span>{{ $tabMeta[$activeTab]['title'] }}</span>
        </div>
      </div>
    </div>

    <div class="layout-grid">
      <div class="surface-card content-card">
        <div class="surface-body">
          <div class="section-head">
            <div>
              <h2 class="section-title">{{ $tabMeta[$activeTab]['title'] }}</h2>
              <p class="section-subtitle">{{ $tabMeta[$activeTab]['subtitle'] }}</p>
            </div>
            <div class="muted-kicker">{{ number_format($tabMeta[$activeTab]['count']) }} records</div>
          </div>

          @if($activeTab === 'all-cases')
            @if($allCases->isEmpty())
              <div class="empty-state compact">
                <div class="empty-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M4 4h16a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H8l-4 3v-3H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2zm3 5h10v2H7V9zm0 4h7v2H7v-2z"/></svg></div>
                <h3 class="empty-title">No feedback cases yet</h3>
                <p class="empty-copy">Customer issues, service feedback, and complaints will appear here.</p>
                <div class="compact-empty-action">
                  <x-admin.new-feedback-menu :groups="$quickCreateGroups" button-label="Create First Case" />
                </div>
              </div>
            @else
              <div class="table-wrap">
                <table class="data-table queue-table">
                  <colgroup>
                    <col class="case">
                    <col class="received">
                    <col class="event-date">
                    <col class="team-member">
                    <col class="source">
                    <col class="summary">
                    <col class="status">
                    <col class="owner">
                    <col class="actions">
                  </colgroup>
                  <thead>
                    <tr>
                      <th>Case</th>
                      <th>Received</th>
                      <th>Event Date</th>
                      <th>Team Member</th>
                      <th>Source</th>
                      <th>Summary</th>
                      <th>Status</th>
                      <th>Owner</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($allCases as $row)
                      <tr class="is-clickable" data-href="{{ route('admin.feedback', array_merge($queryBase, ['view' => 'cases', 'tab' => 'all-cases', 'item' => $row['id']])) }}">
                        <td><span class="cell-strong">{{ $row['id'] }}</span><span class="cell-meta">{{ $row['type'] }}</span></td>
                        <td>{{ \Carbon\Carbon::parse($row['date'])->format('M d, Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($row['event_date'])->format('M d, Y') }}</td>
                        <td class="cell-strong">{{ $row['chef'] }}@if(!empty($row['staff_type']))<span class="cell-meta">{{ $row['staff_type'] }}</span>@endif</td>
                        <td>{{ $row['source'] }}</td>
                        <td class="cell-copy">{{ $row['summary'] }}</td>
                        <td><span class="badge {{ strtolower(str_replace(' ', '-', $row['status'])) }}">{{ $row['status'] }}</span></td>
                        <td>{{ $row['owner'] }}</td>
                        <td>
                          <div class="row-actions">
                            <a class="action-link primary" href="{{ route('admin.feedback', array_merge($queryBase, ['view' => 'cases', 'tab' => 'all-cases', 'item' => $row['id']])) }}">Preview</a>
                            <a class="action-link assign" href="#">Assign</a>
                            <a class="action-link resolve" href="#">Resolve</a>
                            <a class="action-link escalate" href="#">Escalate</a>
                          </div>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @endif
          @elseif($activeTab === 'complaints')
            <div class="table-wrap">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Complaint ID</th>
                    <th>Event Date</th>
                    <th>Date Received</th>
                    <th>Staff Member</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Resolution Status</th>
                    <th>Assistant</th>
                    <th>Action Taken</th>
                  </tr>
                </thead>
                <tbody>
                    @forelse($complaints as $row)
                    <tr class="is-clickable" data-href="{{ route('admin.feedback', array_merge($queryBase, ['view' => 'cases', 'tab' => 'complaints', 'item' => $row['complaint_id']])) }}">
                      <td><span class="cell-strong">{{ $row['complaint_id'] }}</span><span class="cell-meta">Priority {{ $row['priority'] }}</span></td>
                      <td>{{ \Carbon\Carbon::parse($row['event_date'])->format('M d, Y') }}</td>
                      <td>{{ \Carbon\Carbon::parse($row['date_received'])->format('M d, Y') }}</td>
                      <td class="cell-strong">{{ $row['chef'] }}@if(!empty($row['staff_type']))<span class="cell-meta">{{ $row['staff_type'] }}</span>@endif</td>
                      <td>{{ $row['category'] }}</td>
                      <td class="cell-copy">{{ $row['description'] }}</td>
                      <td><span class="badge {{ strtolower(str_replace(' ', '-', $row['resolution_status'])) }}">{{ $row['resolution_status'] }}</span></td>
                      <td>{{ $row['assistant'] }}</td>
                      <td class="cell-copy">
                        {{ $row['action_taken'] }}
                        <span class="cell-meta">
                          <span class="row-actions" style="margin-top:8px">
                            <a class="action-link primary" href="{{ route('admin.feedback', array_merge($queryBase, ['view' => 'cases', 'tab' => 'complaints', 'item' => $row['complaint_id']])) }}">Preview</a>
                            <a class="action-link assign" href="#">Assign</a>
                            <a class="action-link resolve" href="#">Resolve</a>
                            <a class="action-link escalate" href="#">Escalate</a>
                          </span>
                        </span>
                      </td>
                    </tr>
                  @empty
                    <tr><td colspan="9" class="cell-copy">No complaints match the current filters.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          @elseif($activeTab === 'good-feedback')
            <div class="table-wrap">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Feedback ID</th>
                    <th>Event Date</th>
                    <th>Date Received</th>
                    <th>Staff Member</th>
                    <th>Source</th>
                    <th>Compliment</th>
                    <th>Assistant</th>
                  </tr>
                </thead>
                <tbody>
                    @forelse($goodFeedback as $row)
                    <tr class="is-clickable" data-href="{{ route('admin.feedback', array_merge($queryBase, ['view' => 'cases', 'tab' => 'good-feedback', 'item' => $row['feedback_id']])) }}">
                      <td><span class="cell-strong">{{ $row['feedback_id'] }}</span><span class="cell-meta"><span class="badge {{ strtolower(str_replace(' ', '-', $row['status'])) }}">{{ $row['status'] }}</span></span></td>
                      <td>{{ \Carbon\Carbon::parse($row['event_date'])->format('M d, Y') }}</td>
                      <td>{{ \Carbon\Carbon::parse($row['date_received'])->format('M d, Y') }}</td>
                      <td class="cell-strong">{{ $row['chef'] }}@if(!empty($row['staff_type']))<span class="cell-meta">{{ $row['staff_type'] }}</span>@endif</td>
                      <td>{{ $row['source'] }}</td>
                      <td class="cell-copy">{{ $row['compliment'] }}</td>
                      <td>{{ $row['assistant'] }}<span class="cell-meta"><a class="action-link" href="{{ route('admin.feedback', array_merge($queryBase, ['view' => 'cases', 'tab' => 'good-feedback', 'item' => $row['feedback_id']])) }}">Preview</a></span></td>
                    </tr>
                  @empty
                    <tr><td colspan="7" class="cell-copy">No positive feedback records match the current filters.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          @elseif($activeTab === 'van-feedback')
            <div class="table-wrap">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>VanFB ID</th>
                    <th>Event Date</th>
                    <th>Date Received</th>
                    <th>Staff Member</th>
                    <th>Van</th>
                    <th>Description</th>
                    <th>Action Taken</th>
                  </tr>
                </thead>
                <tbody>
                    @forelse($vanFeedback as $row)
                    <tr class="is-clickable" data-href="{{ route('admin.feedback', array_merge($queryBase, ['view' => 'cases', 'tab' => 'van-feedback', 'item' => $row['vanfb_id']])) }}">
                      <td><span class="cell-strong">{{ $row['vanfb_id'] }}</span><span class="cell-meta"><span class="badge {{ strtolower(str_replace(' ', '-', $row['status'])) }}">{{ $row['status'] }}</span></span></td>
                      <td>{{ \Carbon\Carbon::parse($row['event_date'])->format('M d, Y') }}</td>
                      <td>{{ \Carbon\Carbon::parse($row['date_received'])->format('M d, Y') }}</td>
                      <td class="cell-strong">{{ $row['chef'] }}@if(!empty($row['staff_type']))<span class="cell-meta">{{ $row['staff_type'] }}</span>@endif</td>
                      <td>{{ $row['van'] }}</td>
                      <td class="cell-copy">{{ $row['description'] }}</td>
                      <td class="cell-copy">{{ $row['action_taken'] }}<span class="cell-meta"><a class="action-link" href="{{ route('admin.feedback', array_merge($queryBase, ['view' => 'cases', 'tab' => 'van-feedback', 'item' => $row['vanfb_id']])) }}">Preview</a></span></td>
                    </tr>
                  @empty
                    <tr><td colspan="7" class="cell-copy">No van feedback records match the current filters.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          @elseif($activeTab === 'attendance')
            <div class="table-wrap">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Incident ID</th>
                    <th>Date</th>
                    <th>Staff Member</th>
                    <th>Incident Type</th>
                    <th>Units</th>
                    <th>Authorized</th>
                    <th>Manager</th>
                    <th>Notes</th>
                  </tr>
                </thead>
                <tbody>
                    @forelse($attendance as $row)
                    <tr class="is-clickable" data-href="{{ route('admin.feedback', array_merge($queryBase, ['view' => 'cases', 'tab' => 'attendance', 'item' => $row['incident_id']])) }}">
                      <td><span class="cell-strong">{{ $row['incident_id'] }}</span></td>
                      <td>{{ \Carbon\Carbon::parse($row['date'])->format('M d, Y') }}</td>
                      <td class="cell-strong">{{ $row['chef'] }}@if(!empty($row['staff_type']))<span class="cell-meta">{{ $row['staff_type'] }}</span>@endif</td>
                      <td>{{ $row['incident_type'] }}</td>
                      <td>{{ $row['units'] }}</td>
                      <td><span class="badge {{ $row['authorized'] ? 'authorized' : 'unauthorized' }}">{{ $row['authorized'] ? 'Yes' : 'No' }}</span></td>
                      <td>{{ $row['manager'] }}</td>
                      <td class="cell-copy">{{ $row['notes'] }}<span class="cell-meta"><a class="action-link" href="{{ route('admin.feedback', array_merge($queryBase, ['view' => 'cases', 'tab' => 'attendance', 'item' => $row['incident_id']])) }}">Preview</a></span></td>
                    </tr>
                  @empty
                    <tr><td colspan="8" class="cell-copy">No attendance incidents match the current filters.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          @elseif($activeTab === 'days-off')
            <div class="summary-grid">
              <div class="summary-mini"><div class="label">Total Requests</div><div class="value">{{ $daysOff->count() }}</div></div>
              <div class="summary-mini"><div class="label">Total Days</div><div class="value">{{ $daysOff->sum('days') }}</div></div>
              <div class="summary-mini"><div class="label">Approved Days</div><div class="value">{{ $daysOff->where('status', 'Approved')->sum('days') }}</div></div>
              <div class="summary-mini"><div class="label">Denied / Unapproved</div><div class="value">{{ $daysOff->where('status', 'Denied')->sum('days') + $daysOff->sum('unauthorized_days') }}</div></div>
            </div>
            <div class="table-wrap">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Request ID</th>
                    <th>Staff Member</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Days</th>
                    <th>Approved By</th>
                    <th>Notes</th>
                    <th>Unauthorized Days</th>
                  </tr>
                </thead>
                <tbody>
                    @forelse($daysOff as $row)
                    <tr class="is-clickable" data-href="{{ route('admin.feedback', array_merge($queryBase, ['view' => 'cases', 'tab' => 'days-off', 'item' => $row['request_id']])) }}">
                      <td><span class="cell-strong">{{ $row['request_id'] }}</span></td>
                      <td class="cell-strong">{{ $row['chef'] }}</td>
                      <td>{{ \Carbon\Carbon::parse($row['start_date'])->format('M d, Y') }}</td>
                      <td>{{ \Carbon\Carbon::parse($row['end_date'])->format('M d, Y') }}</td>
                      <td><span class="badge {{ strtolower(str_replace(' ', '-', $row['status'])) }}">{{ $row['status'] }}</span></td>
                      <td>{{ $row['days'] }}</td>
                      <td>{{ $row['approved_by'] }}</td>
                      <td class="cell-copy">{{ $row['notes'] }}<span class="cell-meta"><a class="action-link" href="{{ route('admin.feedback', array_merge($queryBase, ['view' => 'cases', 'tab' => 'days-off', 'item' => $row['request_id']])) }}">Preview</a></span></td>
                      <td><span class="badge {{ $row['unauthorized_days'] > 0 ? 'unauthorized' : 'authorized' }}">{{ $row['unauthorized_days'] }}</span></td>
                    </tr>
                  @empty
                    <tr><td colspan="9" class="cell-copy">No days off requests match the current filters.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          @elseif($activeTab === 'alerts')
            <div class="alert-list">
              @forelse($alerts as $row)
                <div class="alert-card">
                  <div class="alert-top">
                    <div>
                      <div class="cell-strong">{{ $row['type'] }}</div>
                      <div class="cell-meta">{{ $row['chef'] }} • {{ \Carbon\Carbon::parse($row['date'])->format('M d, Y') }}</div>
                    </div>
                    <div style="display:flex;gap:8px">
                      <span class="badge {{ strtolower(str_replace(' ', '-', $row['severity'])) }}">{{ $row['severity'] }}</span>
                      <span class="badge {{ strtolower(str_replace(' ', '-', $row['status'])) }}">{{ $row['status'] }}</span>
                    </div>
                  </div>
                  <div class="cell-copy">{{ $row['details'] }}</div>
                  <div style="margin-top:10px"><a class="action-link" href="{{ route('admin.feedback', array_merge($queryBase, ['view' => 'cases', 'tab' => 'alerts', 'item' => $row['alert_id']])) }}">Open alert context</a></div>
                </div>
              @empty
                <div class="empty-state">
                  <div class="empty-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M13 3 4 14h6l-1 7 9-11h-6l1-7z"/></svg></div>
                  <h3 class="empty-title">No active alerts</h3>
                  <p class="empty-copy">Unauthorized patterns and escalations will surface here when thresholds are met.</p>
                </div>
              @endforelse
            </div>
          @elseif($activeTab === 'chef-summary')
            <div class="table-wrap">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Chef</th>
                    <th>Requests</th>
                    <th>Total Days</th>
                    <th>Approved Days</th>
                    <th>Denied Days</th>
                    <th>Pending Days</th>
                    <th>Cancelled/Worked Days</th>
                    <th>Unauthorized Days</th>
                    <th>Good Feedback</th>
                    <th>Complaints</th>
                    <th>Van Issues</th>
                    <th>Net</th>
                    <th>Attendance Incidents</th>
                    <th>Unexcused Incidents</th>
                    <th>Unexcused Units</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($chefSummaries as $row)
                    <tr class="is-clickable" data-href="{{ route('admin.feedback', array_merge($queryBase, ['view' => 'cases', 'tab' => 'chef-summary', 'item' => $row['chef']])) }}">
                      <td class="cell-strong">{{ $row['chef'] }}<span class="cell-meta"><a class="action-link" href="{{ route('admin.feedback', array_merge($queryBase, ['view' => 'cases', 'tab' => 'chef-summary', 'item' => $row['chef']])) }}">Preview</a></span></td>
                      <td>{{ $row['requests'] }}</td>
                      <td>{{ $row['total_days'] }}</td>
                      <td>{{ $row['approved_days'] }}</td>
                      <td>{{ $row['denied_days'] }}</td>
                      <td>{{ $row['pending_days'] }}</td>
                      <td>{{ $row['cancelled_worked_days'] }}</td>
                      <td><span class="badge {{ $row['unauthorized_days'] > 0 ? 'unauthorized' : 'authorized' }}">{{ $row['unauthorized_days'] }}</span></td>
                      <td>{{ $row['good_feedback'] }}</td>
                      <td>{{ $row['complaints'] }}</td>
                      <td>{{ $row['van_issues'] }}</td>
                      <td><span class="badge {{ $row['net_score'] >= 0 ? 'healthy' : 'watch' }}">{{ $row['net_score'] }}</span></td>
                      <td>{{ $row['attendance_incidents'] }}</td>
                      <td>{{ $row['unexcused_incidents'] }}</td>
                      <td>{{ $row['unexcused_units'] }}</td>
                    </tr>
                  @empty
                    <tr><td colspan="15" class="cell-copy">No chef summary rows match the current filters.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          @elseif($activeTab === 'monthly-trends')
            <div class="trend-grid">
              @foreach($monthlyTrends as $row)
                <div class="trend-card">
                  <h4>{{ $row['label'] }}</h4>
                  <div class="trend-bars">
                    @php
                      $trendMetrics = [
                        'Days Off' => [$row['days_off'], max(1, $monthlyTrends->max('days_off'))],
                        'Unauthorized' => [$row['unauthorized_days'], max(1, $monthlyTrends->max('unauthorized_days'))],
                        'Good Feedback' => [$row['good_feedback'], max(1, $monthlyTrends->max('good_feedback'))],
                        'Complaints' => [$row['complaints'], max(1, $monthlyTrends->max('complaints'))],
                      ];
                    @endphp
                    @foreach($trendMetrics as $label => [$value, $max])
                      <div class="trend-bar-row">
                        <span>{{ $label }}</span>
                        <div class="trend-bar"><div class="trend-fill" style="width: {{ ($value / $max) * 100 }}%"></div></div>
                        <strong>{{ $value }}</strong>
                      </div>
                    @endforeach
                  </div>
                  <div style="margin-top:10px"><a class="action-link" href="{{ route('admin.feedback', array_merge($queryBase, ['view' => 'cases', 'tab' => 'monthly-trends', 'item' => $row['month']])) }}">Inspect month</a></div>
                </div>
              @endforeach
            </div>
            <div class="table-wrap">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Month</th>
                    <th>Days Off</th>
                    <th>Unauthorized Days</th>
                    <th>Good Feedback</th>
                    <th>Complaints</th>
                    <th>Attendance Incidents</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($monthlyTrends as $row)
                    <tr>
                      <td class="cell-strong">{{ $row['label'] }}</td>
                      <td>{{ $row['days_off'] }}</td>
                      <td><span class="badge {{ $row['unauthorized_days'] > 0 ? 'unauthorized' : 'authorized' }}">{{ $row['unauthorized_days'] }}</span></td>
                      <td>{{ $row['good_feedback'] }}</td>
                      <td>{{ $row['complaints'] }}</td>
                      <td>{{ $row['attendance_incidents'] }}</td>
                    </tr>
                  @empty
                    <tr><td colspan="6" class="cell-copy">No monthly trend rows match the current filters.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          @endif
        </div>
      </div>

      <div class="detail-overlay {{ $isPreviewOpen ? 'open' : '' }}" data-preview-close></div>
      <div class="detail-panel {{ $isPreviewOpen ? 'open' : '' }}" id="feedbackDetailPanel" aria-hidden="{{ $isPreviewOpen ? 'false' : 'true' }}">
        <div class="detail-shell">
        <div class="surface-card detail-card drawer">
          @if(empty($preview['facts']) && empty($preview['sections']) && empty($preview['history']))
            <div class="preview-empty">
              <div class="drawer-head">
                <div>
                  <div class="detail-tag">{{ $preview['tag'] }}</div>
                  <h3 class="detail-title">{{ $preview['title'] }}</h3>
                  <p class="detail-copy">{{ $preview['subtitle'] }}</p>
                </div>
                <a class="drawer-close" href="{{ route('admin.feedback', $panelBaseQuery) }}" aria-label="Close preview">&times;</a>
              </div>
              <div class="preview-skeleton" aria-hidden="true">
                <span></span>
                <span></span>
                <span></span>
              </div>
            </div>
          @else
            <div class="drawer-head">
              <div>
                <div class="detail-tag">
                  @if(($preview['status'] ?? '') !== '')
                    <span class="badge {{ $previewStatusClass }}">{{ $preview['status'] }}</span>
                  @endif
                  {{ $preview['tag'] }}
                </div>
                <h3 class="detail-title">{{ $preview['title'] }}</h3>
                <p class="detail-copy">{{ $preview['subtitle'] }}</p>
              </div>
              <a class="drawer-close" href="{{ route('admin.feedback', $panelBaseQuery) }}" aria-label="Close preview">&times;</a>
            </div>

            @if(!empty($preview['facts']))
              <div class="fact-grid">
                @foreach($preview['facts'] as $label => $value)
                  <div class="fact-card">
                    <span class="fact-label">{{ $label }}</span>
                    <span class="fact-value">{{ $value }}</span>
                  </div>
                @endforeach
              </div>
            @endif

            @foreach($preview['sections'] as $section)
              <div class="detail-block">
                <div class="muted-kicker" style="margin-bottom:8px">{{ $section['label'] }}</div>
                <p class="detail-copy">{{ $section['value'] }}</p>
              </div>
            @endforeach

            @if(!empty($preview['history']))
              <div class="detail-block">
                <div class="muted-kicker" style="margin-bottom:8px">Follow-up History</div>
                <ul class="detail-list">
                  @foreach($preview['history'] as $item)
                    <li>
                      <div class="timeline-rail">
                        <span class="timeline-dot"></span>
                      </div>
                      <div class="timeline-copy">
                        @if(!empty($item['date']))
                          <span class="timeline-date">{{ $item['date'] }}</span>
                        @endif
                        <strong style="display:block;color:#0f172a">{{ $item['title'] }}</strong>
                        {{ $item['note'] }}
                      </div>
                    </li>
                  @endforeach
                </ul>
              </div>
            @endif
          @endif
        </div>
        </div>
      </div>
    </div>
    @else
    @php
      $analyticsFromDate = request('analytics_from', $filters['date'] ?? '');
      $analyticsToDate = request('analytics_to', $filters['date'] ?? '');
      $normalizeStaffType = function ($type) {
        $value = strtolower(trim((string) $type));
        return match (true) {
          str_contains($value, 'chef') => 'Chefs',
          str_contains($value, 'office') => 'Office',
          str_contains($value, 'driver') => 'Drivers',
          str_contains($value, 'server') => 'Servers',
          str_contains($value, 'manager') => 'Managers',
          str_contains($value, 'fire') => 'Fire Show',
          default => null,
        };
      };

      $allPeople = collect([$complaints, $goodFeedback, $vanFeedback, $attendance, $daysOff, $alerts])
        ->flatten(1)
        ->pluck('chef')
        ->filter()
        ->unique()
        ->values();

      $typeChart = collect([
        ['label' => 'Complaint', 'value' => $complaints->count()],
        ['label' => 'Good Feedback', 'value' => $goodFeedback->count()],
        ['label' => 'Van Issue', 'value' => $vanFeedback->count()],
        ['label' => 'Attendance', 'value' => $attendance->count()],
        ['label' => 'Alert', 'value' => $alerts->count()],
        ['label' => 'Days Off', 'value' => $daysOff->count()],
      ]);

      $staffTypeOrder = ['Chefs', 'Office', 'Drivers', 'Servers', 'Managers', 'Fire Show'];
      $staffTypeCounts = collect($staffTypeOrder)->mapWithKeys(fn ($label) => [$label => 0]);
      $allCases->each(function ($row) use (&$staffTypeCounts, $normalizeStaffType) {
        $label = $normalizeStaffType($row['staff_type'] ?? null);
        if ($label) {
          $staffTypeCounts[$label] = ($staffTypeCounts[$label] ?? 0) + 1;
        }
      });

      $statusBreakdown = collect([
        'Open' => $complaints->where('resolution_status', 'Open')->count()
          + $vanFeedback->where('status', 'Open')->count()
          + $alerts->where('status', 'Open')->count(),
        'In Review' => $complaints->where('resolution_status', 'In Review')->count()
          + $vanFeedback->where('status', 'In Review')->count(),
        'Resolved' => $complaints->where('resolution_status', 'Resolved')->count()
          + $vanFeedback->where('status', 'Resolved')->count(),
        'Escalated' => $complaints->where('resolution_status', 'Escalated')->count()
          + $alerts->where('status', 'Escalated')->count(),
        'Unapproved' => $daysOff->where('status', 'Denied')->count()
          + $daysOff->where('status', 'Pending')->count()
          + $attendance->where('authorized', false)->count(),
      ]);

      $issueBreakdown = $complaints->pluck('category')
        ->merge($attendance->pluck('incident_type'))
        ->merge($alerts->pluck('type'))
        ->merge($vanFeedback->map(fn () => 'Van Issue'))
        ->merge($daysOff->map(fn () => 'Days Off Request'))
        ->filter()
        ->countBy()
        ->sortDesc()
        ->take(6);

      $filteredCaseCount = $allCases->count() + $daysOff->count() + $alerts->count();
      $averageNetScore = round((float) ($chefSummaries->avg('net_score') ?? 0), 1);
      $openWorkflow = $complaints->whereIn('resolution_status', ['Open', 'In Review', 'Escalated'])->count()
        + $vanFeedback->whereIn('status', ['Open', 'In Review'])->count()
        + $alerts->whereIn('status', ['Open', 'Escalated'])->count()
        + $daysOff->whereIn('status', ['Pending', 'Denied'])->count();
      $unauthorizedAlertTotal = $daysOff->sum('unauthorized_days') + $alerts->count();
      $resolvedCasesTotal = $complaints->where('resolution_status', 'Resolved')->count() + $vanFeedback->where('status', 'Resolved')->count();
      $netScoreTotal = $goodFeedback->count() - $complaints->count();
      $analyticsSummaryCards = [
        ['label' => 'Open Complaints', 'value' => $complaints->whereIn('resolution_status', ['Open', 'In Review', 'Escalated'])->count(), 'copy' => 'Cases currently needing follow-up.'],
        ['label' => 'Good Feedback', 'value' => $goodFeedback->count(), 'copy' => 'Positive customer and partner signals.'],
        ['label' => 'Van Issues', 'value' => $vanFeedback->count(), 'copy' => 'Fleet and equipment risk items.'],
        ['label' => 'Attendance Incidents', 'value' => $attendance->count(), 'copy' => 'Manager-reviewed attendance events.'],
        ['label' => 'Unauthorized Days / Alerts', 'value' => $unauthorizedAlertTotal, 'copy' => 'Combined time-off exceptions and alerts.'],
        ['label' => 'Resolved Cases', 'value' => $resolvedCasesTotal, 'copy' => 'Items closed with action recorded.'],
        ['label' => 'Net Score', 'value' => $netScoreTotal, 'copy' => 'Good feedback minus complaints.'],
        ['label' => 'Total Cases', 'value' => $filteredCaseCount, 'copy' => 'All filtered records across queues.'],
      ];
    @endphp

    <div class="analytics-shell">
      <div class="analytics-header-card">
        <h2 class="analytics-header-title">Team Operations Dashboard</h2>
        <p class="analytics-header-copy">Decision-support view for case volume, quality signals, operational risk, and team-wide trends using the same filters as the case queues.</p>
      </div>

      <div class="analytics-kpi-grid">
        <div class="analytics-kpi-card">
          <div class="analytics-kpi-label">Filtered Cases</div>
          <div class="analytics-kpi-value">{{ number_format($filteredCaseCount) }}</div>
          <div class="analytics-kpi-note">Records in the current operating window.</div>
        </div>
        <div class="analytics-kpi-card">
          <div class="analytics-kpi-label">Open Workflow</div>
          <div class="analytics-kpi-value">{{ number_format($openWorkflow) }}</div>
          <div class="analytics-kpi-note">Cases and exceptions still requiring action.</div>
        </div>
        <div class="analytics-kpi-card">
          <div class="analytics-kpi-label">Team Members</div>
          <div class="analytics-kpi-value">{{ number_format($allPeople->count()) }}</div>
          <div class="analytics-kpi-note">Unique people represented in the filtered records.</div>
        </div>
        <div class="analytics-kpi-card">
          <div class="analytics-kpi-label">Average Net Score</div>
          <div class="analytics-kpi-value">{{ number_format($averageNetScore, 1) }}</div>
          <div class="analytics-kpi-note">Average good feedback minus complaints across team summaries.</div>
        </div>
      </div>

      <div class="analytics-filter-card">
        <form class="analytics-filter-grid" method="get" action="{{ route('admin.feedback') }}" id="analyticsFilterForm">
          <input type="hidden" name="view" value="analytics">
          <input type="hidden" name="tab" value="{{ $activeTab }}">
          <input type="hidden" name="date" id="analytics-date-hidden" value="{{ $filters['date'] ?? '' }}">
          <div class="filter-field search-field">
            <label class="field-label" for="analytics-search">Search</label>
            <input class="input" id="analytics-search" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search summaries, IDs, or team members">
          </div>
          <div class="filter-field">
            <label class="field-label" for="analytics-from-date">From Date</label>
            <input class="input" id="analytics-from-date" type="date" value="{{ $analyticsFromDate }}">
          </div>
          <div class="filter-field">
            <label class="field-label" for="analytics-to-date">To Date</label>
            <input class="input" id="analytics-to-date" type="date" value="{{ $analyticsToDate }}">
          </div>
          <div class="filter-field">
            <label class="field-label" for="analytics-team-member">Team Member</label>
            <select class="select" id="analytics-team-member" name="chef">
              <option value="">All team members</option>
              @foreach($chefOptions as $chefOpt)
                <option value="{{ $chefOpt }}" {{ ($filters['chef'] ?? '') === $chefOpt ? 'selected' : '' }}>{{ $chefOpt }}</option>
              @endforeach
            </select>
          </div>
          <div class="filter-field">
            <label class="field-label" for="analytics-staff-type">Staff Type</label>
            <select class="select" id="analytics-staff-type" name="staff_type">
              <option value="">All staff types</option>
              @foreach($staffTypeOptions as $staffTypeOpt)
                <option value="{{ $staffTypeOpt }}" {{ ($filters['staff_type'] ?? '') === $staffTypeOpt ? 'selected' : '' }}>{{ $staffTypeOpt }}</option>
              @endforeach
            </select>
          </div>
          <div class="filter-field">
            <label class="field-label" for="analytics-source">Source</label>
            <select class="select" id="analytics-source" name="source">
              <option value="">All sources</option>
              @foreach($sourceOptions as $sourceOpt)
                <option value="{{ $sourceOpt }}" {{ ($filters['source'] ?? '') === $sourceOpt ? 'selected' : '' }}>{{ $sourceOpt }}</option>
              @endforeach
            </select>
          </div>
          <div class="filter-field">
            <label class="field-label" for="analytics-status">Status</label>
            <select class="select" id="analytics-status" name="status">
              <option value="">All statuses</option>
              @foreach($statusOptions as $statusOpt)
                <option value="{{ $statusOpt }}" {{ ($filters['status'] ?? '') === $statusOpt ? 'selected' : '' }}>{{ $statusOpt }}</option>
              @endforeach
            </select>
          </div>
          <div class="filter-field">
            <label class="field-label" for="analytics-case-type">Case Type</label>
            <select class="select" id="analytics-case-type" name="type">
              <option value="">All case types</option>
              @foreach($typeOptions as $typeOpt)
                <option value="{{ $typeOpt }}" {{ ($filters['type'] ?? '') === $typeOpt ? 'selected' : '' }}>{{ $typeOpt }}</option>
              @endforeach
            </select>
          </div>
          <div class="analytics-actions">
            <button class="btn secondary" type="submit">Apply</button>
            <a href="{{ route('admin.feedback', ['view' => 'analytics']) }}" class="btn secondary">Reset</a>
          </div>
        </form>
      </div>

      <div class="analytics-summary-grid">
        @foreach($analyticsSummaryCards as $summary)
          <div class="analytics-summary-card">
            <div class="analytics-summary-top">
              <div class="analytics-summary-label">{{ $summary['label'] }}</div>
              <div class="analytics-summary-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M4 6h16v2H4zm0 5h10v2H4zm0 5h7v2H4z"/></svg>
              </div>
            </div>
            <div class="analytics-summary-value">{{ number_format($summary['value']) }}</div>
            <div class="analytics-summary-copy">{{ $summary['copy'] }}</div>
          </div>
        @endforeach
      </div>

      <div class="analytics-charts-grid">
        <div class="analytics-chart-card">
          <h3 class="analytics-chart-title">Cases by Team Member</h3>
          <p class="analytics-chart-copy">Cross-role workload and issue concentration by person.</p>
          <div class="chart-wrap"><canvas id="casesByTeamMemberChart"></canvas></div>
        </div>
        <div class="analytics-chart-card">
          <h3 class="analytics-chart-title">Monthly Trend</h3>
          <p class="analytics-chart-copy">Total cases over time for the current filter set.</p>
          <div class="chart-wrap"><canvas id="monthlyTrendChart"></canvas></div>
        </div>
        <div class="analytics-chart-card">
          <h3 class="analytics-chart-title">Cases by Type</h3>
          <p class="analytics-chart-copy">Distribution across the core feedback center record types.</p>
          <div class="chart-wrap compact"><canvas id="caseTypeDistributionChart"></canvas></div>
        </div>
        <div class="analytics-chart-card">
          <h3 class="analytics-chart-title">Staff Type Breakdown</h3>
          <p class="analytics-chart-copy">Distribution across chefs, office, drivers, servers, managers, and fire show.</p>
          <div class="chart-wrap"><canvas id="staffTypeBreakdownChart"></canvas></div>
        </div>
        <div class="analytics-chart-card">
          <h3 class="analytics-chart-title">Status Breakdown</h3>
          <p class="analytics-chart-copy">Where the current workflow sits across open, review, resolved, escalated, and unapproved states.</p>
          <div class="chart-wrap compact"><canvas id="statusBreakdownChart"></canvas></div>
        </div>
        <div class="analytics-chart-card">
          <h3 class="analytics-chart-title">Top Repeated Issues</h3>
          <p class="analytics-chart-copy">Most common issue categories across complaints, attendance, fleet, alerts, and days off.</p>
          <div class="chart-wrap"><canvas id="topRepeatedIssuesChart"></canvas></div>
        </div>
      </div>
    </div>
    @endif
  </div>
  @if(($viewMode ?? 'cases') === 'analytics')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  @endif
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('tr.is-clickable[data-href]').forEach((row) => {
        row.addEventListener('click', (event) => {
          const interactive = event.target.closest('a, button, input, select, textarea, label');
          if (interactive) return;
          const href = row.getAttribute('data-href');
          if (href) window.location.href = href;
        });
      });

      document.querySelectorAll('[data-preview-close]').forEach((overlay) => {
        overlay.addEventListener('click', () => {
          const closeLink = document.querySelector('.drawer-close');
          if (closeLink) window.location.href = closeLink.getAttribute('href');
        });
      });

      if (typeof Chart !== 'undefined' && {{ ($viewMode ?? 'cases') === 'analytics' ? 'true' : 'false' }}) {
        const analytics = @json($analytics ?? []);
        const typeDistribution = @json(($typeChart ?? collect())->values()->all());
        const staffTypeBreakdown = @json(($staffTypeCounts ?? collect())->all());
        const statusBreakdown = @json(($statusBreakdown ?? collect())->all());
        const repeatedIssues = @json(($issueBreakdown ?? collect())->all());
        const baseGrid = '#e2e8f0';
        const textColor = '#475569';
        const strongColor = '#0f172a';

        const analyticsForm = document.getElementById('analyticsFilterForm');
        if (analyticsForm) {
          analyticsForm.addEventListener('submit', () => {
            const from = document.getElementById('analytics-from-date')?.value || '';
            const to = document.getElementById('analytics-to-date')?.value || '';
            const hidden = document.getElementById('analytics-date-hidden');
            if (hidden) {
              hidden.value = to || from;
            }
          });
        }

        const buildChart = (id, config) => {
          const node = document.getElementById(id);
          if (!node) return;
          new Chart(node, config);
        };

        buildChart('casesByTeamMemberChart', {
          type: 'bar',
          data: {
            labels: analytics.cases_by_team_member?.labels ?? [],
            datasets: [{
              label: 'Cases',
              data: analytics.cases_by_team_member?.values ?? [],
              backgroundColor: '#0f172a',
              borderRadius: 10,
              maxBarThickness: 28,
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
              x: { ticks: { color: textColor }, grid: { display: false } },
              y: { ticks: { color: textColor, precision: 0 }, grid: { color: baseGrid } }
            }
          }
        });

        buildChart('monthlyTrendChart', {
          type: 'line',
          data: {
            labels: analytics.monthly_trend?.labels ?? [],
            datasets: [{
              label: 'Cases',
              data: analytics.monthly_trend?.values ?? [],
              borderColor: '#2563eb',
              backgroundColor: 'rgba(37,99,235,0.12)',
              fill: true,
              tension: 0.35,
              pointRadius: 3,
              pointBackgroundColor: '#2563eb',
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
              x: { ticks: { color: textColor }, grid: { display: false } },
              y: { ticks: { color: textColor, precision: 0 }, grid: { color: baseGrid } }
            }
          }
        });

        buildChart('caseTypeDistributionChart', {
          type: 'doughnut',
          data: {
            labels: typeDistribution.map((item) => item.label),
            datasets: [{
              data: typeDistribution.map((item) => item.value),
              backgroundColor: ['#be123c', '#2563eb', '#d97706', '#475569', '#7c3aed', '#0f766e'],
              borderWidth: 0,
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
              legend: { position: 'bottom', labels: { color: strongColor, boxWidth: 10, usePointStyle: true, pointStyle: 'circle' } }
            }
          }
        });

        buildChart('staffTypeBreakdownChart', {
          type: 'bar',
          data: {
            labels: Object.keys(staffTypeBreakdown),
            datasets: [{
              label: 'Cases',
              data: Object.values(staffTypeBreakdown),
              backgroundColor: ['#0f172a', '#334155', '#475569', '#64748b', '#94a3b8', '#cbd5e1'],
              borderRadius: 10,
              maxBarThickness: 34,
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
              x: { ticks: { color: textColor }, grid: { display: false } },
              y: { ticks: { color: textColor, precision: 0 }, grid: { color: baseGrid } }
            }
          }
        });

        buildChart('statusBreakdownChart', {
          type: 'pie',
          data: {
            labels: Object.keys(statusBreakdown),
            datasets: [{
              data: Object.values(statusBreakdown),
              backgroundColor: ['#ef4444', '#f59e0b', '#10b981', '#8b5cf6', '#64748b'],
              borderWidth: 0,
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { position: 'bottom', labels: { color: strongColor, boxWidth: 10, usePointStyle: true, pointStyle: 'circle' } }
            }
          }
        });

        buildChart('topRepeatedIssuesChart', {
          type: 'bar',
          data: {
            labels: Object.keys(repeatedIssues),
            datasets: [{
              label: 'Occurrences',
              data: Object.values(repeatedIssues),
              backgroundColor: '#334155',
              borderRadius: 10,
              maxBarThickness: 22,
            }]
          },
          options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
              x: { ticks: { color: textColor, precision: 0 }, grid: { color: baseGrid } },
              y: { ticks: { color: textColor }, grid: { display: false } }
            }
          }
        });
      }
    });
  </script>
</body>
</html>
