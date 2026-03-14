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
      --panel-border:#e2e8f0;
      --panel-shadow:0 10px 30px rgba(15,23,42,.06);
      --text-strong:#0f172a;
      --surface:#ffffff;
      --surface-soft:#f8fafc;
      --accent:#0f172a;
      --accent-soft:#eef2ff;
      --accent-blue:#2563eb;
      --accent-green:#059669;
      --accent-amber:#d97706;
      --accent-red:#dc2626;
      --accent-violet:#7c3aed;
    }
    html{-webkit-text-size-adjust:100%;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
    *,*::before,*::after{box-sizing:border-box}
    body{background:#f8fafc;color:#0f172a;line-height:1.5;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;text-rendering:optimizeLegibility}
    .container{width:calc(100vw - 24px);max-width:none;margin:20px 12px;padding:0 12px}
    .dashboard-stack{display:grid;gap:24px}
    .page-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:0}
    .page-copy{max-width:760px}
    .eyebrow{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;border:1px solid #e2e8f0;background:#fff;color:#475569;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
    .title{margin:12px 0 4px;font-size:30px;line-height:1.05;letter-spacing:-.04em;color:var(--text-strong)}
    .subtitle{margin:0;color:#64748b;font-size:14px;line-height:1.55;max-width:700px}
    .surface-card{background:#fff;border:1px solid var(--panel-border);border-radius:20px;box-shadow:var(--panel-shadow)}
    .surface-body{padding:20px}
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:40px;padding:0 16px;border-radius:12px;text-decoration:none;box-shadow:0 10px 24px rgba(178,30,39,.18);font-family:inherit;font-size:14px;line-height:1.2;-webkit-appearance:none;appearance:none}
    .btn.secondary{box-shadow:none}
    .header-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;justify-content:flex-end}
    .header-slot{display:inline-flex;align-items:center;gap:8px;min-height:40px;padding:0 14px;border-radius:12px;border:1px dashed #cbd5e1;background:#fff;color:#64748b;font-size:12px;font-weight:700}
    .module-switch{display:inline-flex;gap:6px;padding:6px;border-radius:999px;background:#eef3fa;border:1px solid #d9e2ef}
    .module-switch a{display:inline-flex;align-items:center;justify-content:center;padding:10px 16px;border-radius:999px;color:#475569;font-size:13px;font-weight:800;text-decoration:none}
    .module-switch a.active{background:#0f172a;color:#fff;box-shadow:0 10px 20px rgba(15,23,42,.16)}
    .kpi-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}
    .kpi-card{padding:16px 20px;border-radius:16px;border:1px solid #e2e8f0;background:#fff}
    .kpi-label{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#64748b}
    .kpi-value{margin-top:6px;font-size:24px;font-weight:600;letter-spacing:-.03em;color:#0f172a;line-height:1}
    .kpi-copy{margin-top:4px;font-size:12px;color:#64748b}
    .filter-shell{display:grid;gap:16px}
    .filter-head{display:flex;align-items:center;justify-content:space-between;gap:12px}
    .filter-title{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:800;color:#0f172a}
    .filter-bar{display:grid;grid-template-columns:minmax(220px,1.7fr) repeat(7,minmax(118px,1fr)) auto auto;gap:12px;align-items:end}
    .quick-date-row{grid-column:1 / -1;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
    .quick-date-strip{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
    .quick-date-chip{display:inline-flex;align-items:center;justify-content:center;padding:7px 12px;border-radius:999px;border:1px solid #e2e8f0;background:#fff;color:#475569;font-size:12px;font-weight:700;line-height:1;text-decoration:none;transition:background-color .16s ease,color .16s ease,border-color .16s ease}
    .quick-date-chip:hover{background:#f8fafc}
    .quick-date-chip.active{background:#0f172a;border-color:#0f172a;color:#fff}
    .field-label{display:block;margin:0 0 6px;font-size:11px;font-weight:800;color:#64748b;letter-spacing:.07em;text-transform:uppercase}
    .input,.select{width:100%;height:40px;padding:0 12px;border:1px solid #d8deea;border-radius:12px;background:#fff;color:#0f172a;font-family:inherit;font-size:14px;line-height:1.4;-webkit-appearance:none;appearance:none}
    .input:focus,.select:focus{outline:none;border-color:#c6d1e3;box-shadow:0 0 0 4px rgba(148,163,184,.14)}
    .select{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='none' stroke='%2394a3b8' stroke-width='1.7'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='m6 8 4 4 4-4'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;background-size:14px 14px;padding-right:38px}
    .icon-field{position:relative}
    .icon-field .input,.icon-field .select{padding-left:38px}
    .field-icon{position:absolute;left:12px;bottom:11px;width:18px;height:18px;color:#94a3b8;pointer-events:none}
    .filter-actions{display:flex;align-items:flex-end;gap:8px}
    .filter-actions .action-link{min-height:40px}
    .section-shell{display:grid;gap:16px}
    .segmented-strip{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:0}
    .segmented-link{display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:999px;border:1px solid #e2e8f0;background:#fff;color:#334155;font-size:14px;font-weight:500;text-decoration:none;transition:background-color .16s ease,color .16s ease,border-color .16s ease}
    .segmented-link:hover{background:#f8fafc}
    .segmented-link.active{background:#0f172a;border-color:#0f172a;color:#fff;box-shadow:0 1px 2px rgba(15,23,42,.08)}
    .section-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:0}
    .section-title{margin:0;font-size:18px;font-weight:800;color:#0f172a}
    .section-subtitle{margin:4px 0 0;color:#64748b;font-size:13px;line-height:1.55;max-width:720px}
    .count-pill{display:inline-flex;align-items:center;justify-content:center;padding:8px 12px;border-radius:999px;background:#f8fafc;border:1px solid #e2e8f0;font-size:12px;font-weight:800;color:#475569}
    .stats-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px}
    .stat-card{position:relative;overflow:hidden;padding:12px 16px;border:1px solid var(--panel-border);border-radius:20px;background:linear-gradient(180deg,#fff 0%,#fbfcff 100%);box-shadow:var(--panel-shadow)}
    .stat-card::after{content:"";position:absolute;right:-18px;top:-18px;width:76px;height:76px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,.9) 0%,rgba(255,255,255,0) 70%)}
    .stat-top{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px}
    .stat-label{font-size:12px;font-weight:800;color:#475569;letter-spacing:.03em;text-transform:uppercase}
    .stat-value{font-size:24px;font-weight:800;line-height:1;letter-spacing:-.04em;color:#0f172a}
    .stat-note{margin-top:6px;font-size:12px;color:#94a3b8}
    .trend-chip{display:inline-flex;align-items:center;padding:5px 8px;border-radius:999px;font-size:11px;font-weight:800}
    .trend-chip.up{background:#ecfdf5;color:#047857}
    .trend-chip.down{background:#fff7ed;color:#c2410c}
    .trend-chip.flat{background:#f8fafc;color:#475569}
    .stat-icon{width:42px;height:42px;border-radius:14px;display:inline-flex;align-items:center;justify-content:center;border:1px solid transparent}
    .stat-icon svg{width:18px;height:18px}
    .tone-open .stat-icon{background:#fef2f2;border-color:#fecaca;color:#b91c1c}
    .tone-review .stat-icon{background:#fff7ed;border-color:#fed7aa;color:#b45309}
    .tone-resolved .stat-icon{background:#ecfdf5;border-color:#a7f3d0;color:#047857}
    .tone-escalated .stat-icon{background:#f5f3ff;border-color:#ddd6fe;color:#7c3aed}
    .tone-positive .stat-icon{background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8}
    .tone-neutral .stat-icon{background:#f8fafc;border-color:#e2e8f0;color:#475569}
    .analytics-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin-top:16px}
    .chart-card{padding:18px;border-radius:20px;border:1px solid var(--panel-border);background:linear-gradient(180deg,#fff 0%,#fcfdff 100%);box-shadow:var(--panel-shadow)}
    .chart-card.wide{grid-column:span 2}
    .chart-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:14px}
    .chart-title{margin:0;font-size:17px;font-weight:800;color:#0f172a}
    .chart-copy{margin:4px 0 0;font-size:13px;color:#64748b;line-height:1.6}
    .chart-note{font-size:12px;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em}
    .chart-canvas{position:relative;min-height:260px}
    .chart-canvas.compact{min-height:220px}
    .queue-layout{margin-top:0;min-width:0}
    .table-wrap{width:100%;min-width:0;overflow-x:auto;overflow-y:hidden;border:1px solid #e8edf4;border-radius:16px;background:#fff}
    .data-table{width:100%;border-collapse:separate;border-spacing:0;table-layout:fixed;min-width:0}
    .data-table th,.data-table td{padding:12px;text-align:left;vertical-align:top}
    .data-table thead th{background:#f8fafc;border-bottom:1px solid #e8edf4;color:#6b7280;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;white-space:nowrap}
    .data-table tbody tr{background:#fff;transition:background-color .16s ease}
    .data-table tbody tr:hover{background:#f9fafb}
    .data-table tbody tr + tr td{border-top:1px solid #edf1f6}
    .data-table tbody tr.is-clickable{cursor:pointer}
    .cell-strong{font-weight:800;color:#0f172a}
    .cell-copy{max-width:none;color:#475569;line-height:1.45;white-space:normal;word-break:break-word}
    .cell-meta{display:block;color:#64748b;font-size:12px;margin-top:3px}
    .cell-muted{color:#64748b;font-size:12px}
    .badge{display:inline-flex;align-items:center;justify-content:center;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:700;line-height:1.2;border:1px solid transparent;white-space:nowrap}
    .badge.open,.badge.pending,.badge.scheduled,.badge.medium{background:#fef3c7;border-color:#fde68a;color:#92400e}
    .badge.in-review,.badge.reviewed{background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8}
    .badge.escalated,.badge.urgent,.badge.watch,.badge.flagged,.badge.operational{background:#fff7ed;border-color:#fdba74;color:#c2410c}
    .badge.approved,.badge.resolved,.badge.authorized,.badge.healthy{background:#dcfce7;border-color:#86efac;color:#166534}
    .badge.rejected,.badge.denied,.badge.unauthorized{background:#fee2e2;border-color:#fca5a5;color:#991b1b}
    .badge.closed,.badge.logged,.badge.shared,.badge.neutral,.badge.alert,.badge.cancelled{background:#f1f5f9;border-color:#cbd5e1;color:#475569}
    .badge.positive,.badge.low{background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8}
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
    .detail-tag{display:inline-flex;align-items:center;gap:8px;margin-bottom:12px;padding:6px 10px;border-radius:999px;background:#f8fafc;border:1px solid #e2e8f0;color:#475569;font-size:12px;font-weight:700}
    .detail-title{margin:0 0 8px;font-size:18px;line-height:1.3;color:#0f172a}
    .detail-copy{margin:0;color:#64748b;font-size:14px;line-height:1.6}
    .fact-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin:14px 0}
    .fact-card{padding:12px;border-radius:14px;background:#f8fafc;border:1px solid #e6ecf3}
    .fact-label{display:block;font-size:11px;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px}
    .fact-value{font-size:13px;font-weight:800;color:#0f172a}
    .detail-block + .detail-block{margin-top:12px;padding-top:12px;border-top:1px solid #edf1f6}
    .detail-list{display:grid;gap:0;margin:0;padding:4px 0 0;list-style:none}
    .detail-list li{position:relative;display:grid;grid-template-columns:26px 1fr;gap:12px;padding:0 0 16px}
    .timeline-rail{position:relative;display:flex;justify-content:center}
    .timeline-rail::after{content:"";position:absolute;top:14px;bottom:-18px;left:50%;width:2px;transform:translateX(-50%);background:linear-gradient(180deg,#dbe4ee 0%,#eef2f7 100%)}
    .detail-list li:last-child .timeline-rail::after{display:none}
    .timeline-dot{width:12px;height:12px;border-radius:999px;background:#0f172a;box-shadow:0 0 0 4px #f8fafc;margin-top:2px;position:relative;z-index:1}
    .timeline-copy{font-size:13px;color:#475569;line-height:1.5;padding-bottom:2px}
    .timeline-date{display:block;margin-bottom:4px;font-size:11px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:#94a3b8}
    .workflow-summary{margin:14px 0 0;padding:14px;border-radius:16px;border:1px solid #e6ecf3;background:#f8fafc}
    .workflow-kicker{display:inline-flex;align-items:center;gap:8px;margin-bottom:8px;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#64748b}
    .workflow-meta{display:grid;gap:6px;margin-top:12px}
    .workflow-meta-row{display:flex;align-items:center;justify-content:space-between;gap:10px;font-size:12px;color:#64748b}
    .workflow-meta-row strong{color:#0f172a;font-weight:700}
    .workflow-form{display:grid;gap:12px;margin-top:14px}
    .workflow-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    .workflow-field label{display:block;margin:0 0 6px;font-size:11px;font-weight:800;color:#94a3b8;letter-spacing:.08em;text-transform:uppercase}
    .workflow-field input,.workflow-field select,.workflow-field textarea{width:100%;border:1px solid #d8deea;border-radius:12px;background:#fff;color:#0f172a}
    .workflow-field input,.workflow-field select{height:40px;padding:0 12px}
    .workflow-field textarea{min-height:96px;padding:10px 12px;resize:vertical;line-height:1.5}
    .workflow-field input:focus,.workflow-field select:focus,.workflow-field textarea:focus{outline:none;border-color:#c6d1e3;box-shadow:0 0 0 4px rgba(148,163,184,.14)}
    .workflow-field [data-team-member-option]{display:flex !important;align-items:center !important;gap:12px;margin:0 !important;font-size:14px !important;font-weight:400 !important;letter-spacing:0 !important;text-transform:none !important}
    .workflow-field [data-team-member-checkbox]{width:16px !important;height:16px !important;min-width:16px;flex:0 0 16px;display:inline-block !important;margin:0 !important;padding:0 !important;border-radius:4px;background:#fff;vertical-align:middle;align-self:center;border:1px solid #cbd5e1;box-shadow:none}
    .workflow-actions{display:flex;flex-wrap:wrap;gap:8px}
    .workflow-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:36px;padding:0 14px;border:1px solid #d8deea;border-radius:10px;background:#fff;color:#334155;font-size:12px;font-weight:700}
    .workflow-btn:hover{background:#f8fafc}
    .workflow-btn.primary{background:#0f172a;border-color:#0f172a;color:#fff}
    .workflow-btn.primary:hover{background:#1e293b}
    .workflow-btn.resolve{background:#ecfdf5;border-color:#86efac;color:#166534}
    .workflow-btn.escalate{background:#fff7ed;border-color:#fdba74;color:#c2410c}
    .workflow-details-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:14px}
    .workflow-empty{font-size:13px;color:#94a3b8}
    .empty-state{display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:42px 20px;border:1px dashed #d6dce8;border-radius:18px;background:linear-gradient(180deg,#fbfcff 0%,#f8fafc 100%)}
    .empty-title{margin:16px 0 6px;font-size:20px;line-height:1.2;color:#0f172a}
    .empty-copy{margin:0 0 18px;max-width:420px;color:#64748b;font-size:14px;line-height:1.6}
    @media (max-width: 1380px){
      .filter-bar{grid-template-columns:minmax(220px,1.4fr) repeat(4,minmax(140px,1fr)) auto auto}
      .analytics-grid{grid-template-columns:1fr}
      .chart-card.wide{grid-column:auto}
      .stats-grid{grid-template-columns:repeat(3,minmax(0,1fr))}
    }
    @media (max-width: 980px){
      .page-head,.filter-head{flex-direction:column;align-items:stretch}
      .header-actions{justify-content:flex-start}
      .kpi-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
      .stats-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
      .filter-bar{grid-template-columns:1fr 1fr}
      .filter-actions{grid-column:span 2}
    }
    @media (max-width: 720px){
      .container{padding:0 10px}
      .dashboard-stack{gap:18px}
      .kpi-grid,.filter-bar,.fact-grid{grid-template-columns:1fr}
      .stats-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
      .quick-date-row{align-items:flex-start}
      .workflow-grid,.workflow-details-grid{grid-template-columns:1fr}
      .filter-actions{grid-column:auto}
      .module-switch{width:100%}
      .module-switch a{flex:1}
      .data-table{min-width:860px}
      .detail-panel{top:10px;right:10px;bottom:10px;width:calc(100vw - 20px)}
    }
    @media (max-width: 540px){
      .surface-body{padding:16px}
      .title{font-size:26px}
      .page-head{gap:12px}
      .header-actions>*{width:100%}
    }
  </style>
</head>
<body>
  @php
    $queryBase = array_filter([
      'view' => $activeView,
      'tab' => $activeTab,
      'q' => $filters['q'] ?? '',
      'status' => $filters['status'] ?? '',
      'type' => $filters['type'] ?? '',
      'date' => $filters['date'] ?? '',
      'from' => $filters['from'] ?? '',
      'to' => $filters['to'] ?? '',
      'chef' => $filters['chef'] ?? '',
      'staff_type' => $filters['staff_type'] ?? '',
      'source' => $filters['source'] ?? '',
    ], fn ($value) => $value !== '');
    $viewBase = array_filter($queryBase, fn ($value, $key) => $key !== 'item', ARRAY_FILTER_USE_BOTH);
    $caseTabs = [
      'complaints' => 'Complaints',
      'good-feedback' => 'Good Feedback',
      'van-feedback' => 'Van Issues',
      'attendance' => 'Attendance',
      'days-off' => 'Days Off',
      'alerts' => 'Alerts',
    ];
    $icons = [
      'open' => '<path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm1 15h-2v-2h2zm0-4h-2V7h2z"/>',
      'positive' => '<path d="m9.55 16.6-3.9-3.9 1.4-1.4 2.5 2.5 7.4-7.4 1.4 1.4z"/>',
      'review' => '<path d="M12 4a8 8 0 1 0 8 8h-2a6 6 0 1 1-6-6V4zm1 0h7v7h-2V7.4l-4.3 4.3-1.4-1.4L16.6 6H13V4z"/>',
      'neutral' => '<path d="M4 4h16v2H4zm0 7h16v2H4zm0 7h10v2H4z"/>',
      'escalated' => '<path d="M13 3 4 14h6l-1 7 9-11h-6l1-7z"/>',
      'resolved' => '<path d="m9.55 16.6-3.9-3.9 1.4-1.4 2.5 2.5 7.4-7.4 1.4 1.4z"/>',
    ];
    $createBackUrl = request()->fullUrl();
    $quickCreateGroups = [
      'Feedback' => [
        ['label' => 'Complaint', 'icon' => 'alert-triangle', 'icon_class' => 'text-red-500', 'href' => route('admin.feedback.create', ['type' => 'complaint', 'back' => $createBackUrl])],
        ['label' => 'Good Feedback', 'icon' => 'thumb-up', 'icon_class' => 'text-green-500', 'href' => route('admin.feedback.create', ['type' => 'good-feedback', 'back' => $createBackUrl])],
      ],
      'Operations' => [
        ['label' => 'Van Feedback', 'icon' => 'truck', 'icon_class' => 'text-orange-500', 'href' => route('admin.feedback.create', ['type' => 'van-feedback', 'back' => $createBackUrl])],
        ['label' => 'Attendance Incident', 'icon' => 'clock', 'icon_class' => 'text-blue-500', 'href' => route('admin.feedback.create', ['type' => 'attendance', 'back' => $createBackUrl])],
      ],
    ];
    $panelBaseQuery = array_filter($queryBase, fn ($value, $key) => $key !== 'item', ARRAY_FILTER_USE_BOTH);
    $previewStatusClass = strtolower(str_replace(' ', '-', $preview['status'] ?? 'neutral'));
    $isPreviewOpen = $activeView === 'cases' && !empty($filters['item']) && (!empty($preview['facts']) || !empty($preview['sections']) || !empty($preview['history']));
    $avgNetScore = $teamSummaries->count() ? round($teamSummaries->avg('net_score'), 1) : 0;
    $complaintsPendingCount = $complaints->where('resolution_status', 'Pending')->count();
    $complaintsInReviewCount = $complaints->where('resolution_status', 'In Review')->count();
    $complaintsEscalatedCount = $complaints->where('resolution_status', 'Escalated')->count();
    $complaintsResolvedCount = $complaints->whereIn('resolution_status', ['Resolved', 'Closed'])->count();
    $activeComplaintsCount = $complaintsPendingCount + $complaintsInReviewCount + $complaintsEscalatedCount;
    $daysOffPendingCount = $daysOff->where('status', 'Pending')->count();
    $daysOffApprovedCount = $daysOff->where('status', 'Approved')->count();
    $daysOffUnauthorizedCount = $daysOff->where('unauthorized_days', '>', 0)->count();
    $daysOffSort = $filters['sort'] ?? '';
    $daysOffDirection = $filters['direction'] ?? 'desc';
    $daysOffSortIcon = function (string $column) use ($daysOffSort, $daysOffDirection) {
      if ($daysOffSort !== $column) {
        return '<svg viewBox="0 0 20 20" width="12" height="12" fill="currentColor" aria-hidden="true" style="opacity:.45"><path d="M10 5 6.5 8.5h7L10 5Zm0 10 3.5-3.5h-7L10 15Z"/></svg>';
      }

      return $daysOffDirection === 'asc'
        ? '<svg viewBox="0 0 20 20" width="12" height="12" fill="currentColor" aria-hidden="true"><path d="M10 5 6.5 8.5h7L10 5Z"/></svg>'
        : '<svg viewBox="0 0 20 20" width="12" height="12" fill="currentColor" aria-hidden="true"><path d="m10 15 3.5-3.5h-7L10 15Z"/></svg>';
    };
    $daysOffSortLink = function (string $column) use ($viewBase, $daysOffSort, $daysOffDirection) {
      $direction = $daysOffSort === $column && $daysOffDirection === 'asc' ? 'desc' : 'asc';

      return route('admin.feedback', array_merge($viewBase, [
        'view' => 'cases',
        'tab' => 'days-off',
        'sort' => $column,
        'direction' => $direction,
      ]));
    };
    $workflowBackUrl = request()->fullUrl();
    $workflowUpdateRoute = route('admin.feedback.workflow.update');
  @endphp
  <div class="container">
    @if (session('ok'))
      <div class="surface-card" style="margin-bottom:12px">
        <div class="surface-body" style="padding:14px 18px;color:#166534;background:#ecfdf5;border-radius:18px">
          {{ session('ok') }}
        </div>
      </div>
    @endif

    <div class="dashboard-stack">
      <div class="page-head">
        <div class="page-copy">
          <div class="eyebrow">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="M4 4h16a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H8l-4 3v-3H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2zm3 5h10v2H7V9zm0 4h7v2H7v-2z"/></svg>
            Operations module
          </div>
          <h1 class="title">Feedback Center</h1>
          <p class="subtitle">Operations case management and performance analytics.</p>
        </div>
        <div class="header-actions">
          <div class="module-switch">
            <a href="{{ route('admin.feedback', array_merge($viewBase, ['view' => 'cases'])) }}" class="{{ $activeView === 'cases' ? 'active' : '' }}">Cases</a>
            <a href="{{ route('admin.feedback', array_merge($viewBase, ['view' => 'analytics'])) }}" class="{{ $activeView === 'analytics' ? 'active' : '' }}">Analytics</a>
          </div>
          <div class="header-slot">Export / Reports</div>
          <a
            href="{{ route('admin.feedback.create', ['type' => 'days-off', 'back' => request()->fullUrl()]) }}"
            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
          >
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
            </svg>
            New Days Off Request
          </a>
          <x-admin.new-feedback-menu :groups="$quickCreateGroups" />
        </div>
      </div>

      @if($activeView === 'cases' && $complaintsEscalatedCount > 0)
        <div class="surface-card" style="margin-top:12px;border-color:#fed7aa;background:#fff7ed">
          <div class="surface-body" style="padding:10px 14px;display:flex;align-items:center;justify-content:space-between;gap:12px">
            <div style="display:flex;align-items:center;gap:10px;color:#9a3412">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true"><path d="M13 3 4 14h6l-1 7 9-11h-6l1-7Z"/></svg>
              <span style="font-size:13px;font-weight:700">Escalated complaints require attention.</span>
            </div>
            <span style="font-size:12px;font-weight:700;color:#c2410c">{{ number_format($complaintsEscalatedCount) }} active</span>
          </div>
        </div>
      @endif

      <div class="surface-card">
        <div class="surface-body">
          <div class="filter-shell">
            <div class="filter-head">
              <div class="filter-title">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5.25h18M6.75 12h10.5M10.5 18.75h3"/></svg>
                Filters
              </div>
              <div class="cell-muted">Use the same filters across case queues and analytics.</div>
            </div>
            <form class="filter-bar" method="get" action="{{ route('admin.feedback') }}" data-quick-date-form>
              <input type="hidden" name="view" value="{{ $activeView }}">
              <input type="hidden" name="tab" value="{{ $activeTab }}">
              <div class="quick-date-row">
                <div class="quick-date-strip">
                  <button class="quick-date-chip" type="button" data-quick-range="today">Today</button>
                  <button class="quick-date-chip" type="button" data-quick-range="this-week">This Week</button>
                  <button class="quick-date-chip" type="button" data-quick-range="last-week">Last Week</button>
                  <button class="quick-date-chip" type="button" data-quick-range="this-month">This Month</button>
                  <button class="quick-date-chip" type="button" data-quick-range="last-month">Last Month</button>
                  <button class="quick-date-chip" type="button" data-quick-range="custom">Custom</button>
                </div>
                <div class="cell-muted">Operational weeks run Tuesday through Monday.</div>
              </div>
              <div class="icon-field">
                <label class="field-label" for="feedback-search">Search</label>
                <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/></svg>
                <input class="input" id="feedback-search" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search IDs, team member, summary, or owner">
              </div>
              <div class="icon-field">
                <label class="field-label" for="feedback-from">Date From</label>
                <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 2v4m8-4v4M3 10h18M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/></svg>
                <input class="input" id="feedback-from" type="date" name="from" value="{{ $filters['from'] ?? '' }}">
              </div>
              <div class="icon-field">
                <label class="field-label" for="feedback-to">Date To</label>
                <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 2v4m8-4v4M3 10h18M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/></svg>
                <input class="input" id="feedback-to" type="date" name="to" value="{{ $filters['to'] ?? '' }}">
              </div>
              <div>
                <label class="field-label" for="feedback-chef">Team Member</label>
                <select class="select pl-3 pr-10" id="feedback-chef" name="chef">
                  <option value="">All team members</option>
                  @foreach($chefOptions as $chefOpt)
                    <option value="{{ $chefOpt }}" {{ ($filters['chef'] ?? '') === $chefOpt ? 'selected' : '' }}>{{ $chefOpt }}</option>
                  @endforeach
                </select>
              </div>
              <div>
                <label class="field-label" for="feedback-staff-type">Staff Type</label>
                <select class="select" id="feedback-staff-type" name="staff_type">
                  <option value="">All staff types</option>
                  @foreach($staffTypeOptions as $staffType)
                    <option value="{{ $staffType }}" {{ ($filters['staff_type'] ?? '') === $staffType ? 'selected' : '' }}>{{ $staffType }}</option>
                  @endforeach
                </select>
              </div>
              <div>
                <label class="field-label" for="feedback-source">Source</label>
                <select class="select" id="feedback-source" name="source">
                  <option value="">All sources</option>
                  @foreach($sourceOptions as $sourceOpt)
                    <option value="{{ $sourceOpt }}" {{ ($filters['source'] ?? '') === $sourceOpt ? 'selected' : '' }}>{{ $sourceOpt }}</option>
                  @endforeach
                </select>
              </div>
              <div>
                <label class="field-label" for="feedback-status">Status</label>
                <select class="select" id="feedback-status" name="status">
                  <option value="">All statuses</option>
                  @foreach($statusOptions as $statusOpt)
                    <option value="{{ $statusOpt }}" {{ ($filters['status'] ?? '') === $statusOpt ? 'selected' : '' }}>{{ $statusOpt }}</option>
                  @endforeach
                </select>
              </div>
              <div class="filter-actions">
                <button class="action-link primary" type="submit">Apply</button>
              </div>
              <div class="filter-actions">
                <a class="action-link" href="{{ route('admin.feedback', ['view' => $activeView, 'tab' => $activeTab]) }}">Reset</a>
              </div>
            </form>
          </div>
        </div>
      </div>

    @if($activeView === 'cases')
      <div class="surface-card">
        <div class="surface-body">
          <div class="section-shell">
            <div class="segmented-strip">
              @foreach($caseTabs as $tabKey => $tabLabel)
                <a class="segmented-link {{ $activeTab === $tabKey ? 'active' : '' }}" href="{{ route('admin.feedback', array_merge($viewBase, ['view' => 'cases', 'tab' => $tabKey])) }}">
                  {{ $tabLabel }}
                  <span style="opacity:.72">{{ $tabMeta[$tabKey]['count'] ?? 0 }}</span>
                </a>
              @endforeach
            </div>

            @if($activeTab === 'complaints')
              <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                  <h2 class="text-[18px] font-semibold tracking-[-0.02em] text-slate-900">Complaints Management</h2>
                  <p class="mt-1 text-sm leading-6 text-slate-500">Track customer issues, ownership, escalation, and resolution status in one operational queue.</p>
                </div>
                <div class="flex flex-wrap items-center justify-end gap-2">
                  <div class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-slate-50 px-2 py-1 text-[10px] font-medium text-slate-400">
                    {{ number_format($tabMeta[$activeTab]['count'] ?? 0) }} visible
                  </div>
                </div>
              </div>
              <div class="mt-3 grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                  <div class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Total Complaints</div>
                  <div class="mt-1 text-xl font-semibold text-slate-900">{{ number_format($complaints->count()) }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                  <div class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">In Review</div>
                  <div class="mt-1 text-xl font-semibold text-amber-700">{{ number_format($complaintsInReviewCount) }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                  <div class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Escalated</div>
                  <div class="mt-1 text-xl font-semibold text-rose-700">{{ number_format($complaintsEscalatedCount) }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                  <div class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Resolved</div>
                  <div class="mt-1 text-xl font-semibold text-emerald-700">{{ number_format($complaintsResolvedCount) }}</div>
                </div>
              </div>
            @elseif($activeTab === 'days-off')
              <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                  <h2 class="text-[18px] font-semibold tracking-[-0.02em] text-slate-900">Days Off Management</h2>
                  <p class="mt-1 text-sm leading-6 text-slate-500">Track approvals, pending requests, denied time off, and unauthorized absences.</p>
                </div>
                <div class="flex flex-wrap items-center justify-end gap-2">
                  <div class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-slate-50 px-2 py-1 text-[10px] font-medium text-slate-400">
                    {{ number_format($tabMeta[$activeTab]['count'] ?? 0) }} visible
                  </div>
                </div>
              </div>
              <div class="mt-3 grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                  <div class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Total Requests</div>
                  <div class="mt-1 text-xl font-semibold text-slate-900">{{ number_format($daysOff->count()) }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                  <div class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Pending</div>
                  <div class="mt-1 text-xl font-semibold text-amber-700">{{ number_format($daysOffPendingCount) }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                  <div class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Approved</div>
                  <div class="mt-1 text-xl font-semibold text-emerald-700">{{ number_format($daysOffApprovedCount) }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                  <div class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Unauthorized Flags</div>
                  <div class="mt-1 text-xl font-semibold text-rose-700">{{ number_format($daysOffUnauthorizedCount) }}</div>
                </div>
              </div>
            @else
              <div class="section-head">
                <div>
                  <h2 class="section-title">{{ $tabMeta[$activeTab]['title'] ?? 'Cases' }}</h2>
                  <p class="section-subtitle">{{ $tabMeta[$activeTab]['subtitle'] ?? 'Operational case management across the team.' }}</p>
                </div>
                <div class="count-pill">{{ number_format($tabMeta[$activeTab]['count'] ?? 0) }} visible</div>
              </div>
            @endif

            <div class="queue-layout">
              @if($activeTab === 'complaints')
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                  <div class="overflow-x-auto">
                    <table class="min-w-[1240px] w-full table-fixed">
                      <colgroup>
                        <col class="w-28">
                        <col class="w-52">
                        <col class="w-28">
                        <col class="w-32">
                        <col class="w-20">
                        <col class="w-24">
                        <col class="w-32">
                        <col class="min-w-[300px] w-full">
                        <col class="w-20">
                      </colgroup>
                      <thead class="bg-slate-50/80">
                        <tr>
                          <th class="w-28 whitespace-nowrap border-b border-slate-200 px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Complaint ID</th>
                          <th class="w-52 whitespace-nowrap border-b border-slate-200 px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Team Members</th>
                          <th class="w-28 whitespace-nowrap border-b border-slate-200 px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Event Date</th>
                          <th class="w-32 whitespace-nowrap border-b border-slate-200 px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Source</th>
                          <th class="w-20 whitespace-nowrap border-b border-slate-200 px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Priority</th>
                          <th class="w-24 whitespace-nowrap border-b border-slate-200 px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Status</th>
                          <th class="w-32 whitespace-nowrap border-b border-slate-200 px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Owner</th>
                          <th class="min-w-[300px] w-full whitespace-nowrap border-b border-slate-200 px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Summary</th>
                          <th class="w-20 whitespace-nowrap border-b border-slate-200 px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Edit</th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse($complaints as $row)
                          @php
                            $complaintTeamMembers = collect($row['team_members'] ?? [])
                                ->map(fn ($member) => is_array($member) ? (string) ($member['label'] ?? $member['name'] ?? $member['value'] ?? '') : (string) $member)
                                ->map(fn ($member) => trim($member))
                                ->filter()
                                ->values();
                            if ($complaintTeamMembers->isEmpty() && !empty($row['chef'])) {
                                $complaintTeamMembers = collect([(string) $row['chef']]);
                            }
                            $visibleComplaintMembers = $complaintTeamMembers->take(3);
                            $remainingComplaintMemberNames = $complaintTeamMembers->slice(3)->values();
                            $remainingComplaintMembers = max($complaintTeamMembers->count() - 3, 0);
                            $complaintStatusClass = match (strtolower($row['resolution_status'])) {
                                'resolved' => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200',
                                'closed' => 'bg-slate-100 text-slate-700 ring-1 ring-inset ring-slate-200',
                                'pending' => 'bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-200',
                                'in review' => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200',
                                'escalated' => 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200',
                                default => 'bg-slate-100 text-slate-700 ring-1 ring-inset ring-slate-200',
                            };
                            $complaintPriorityClass = match (strtolower($row['priority'])) {
                                'high' => 'text-rose-700 bg-rose-50 ring-1 ring-inset ring-rose-200',
                                'low' => 'text-slate-700 bg-slate-100 ring-1 ring-inset ring-slate-200',
                                default => 'text-amber-700 bg-amber-50 ring-1 ring-inset ring-amber-200',
                            };
                          @endphp
                          <tr class="group is-clickable cursor-pointer border-b border-slate-100 transition-colors hover:bg-slate-50/70" data-href="{{ route('admin.feedback', array_merge($queryBase, ['tab' => 'complaints', 'item' => $row['complaint_id']])) }}">
                            <td class="align-middle whitespace-nowrap px-4 py-3">
                              <span class="text-sm font-semibold text-slate-900 transition-colors group-hover:text-slate-950">{{ $row['complaint_id'] }}</span>
                            </td>
                            <td class="align-middle px-4 py-3">
                              @if($visibleComplaintMembers->isNotEmpty())
                                <span class="text-sm font-semibold text-slate-900 transition-colors group-hover:text-slate-950">{{ $visibleComplaintMembers->implode(', ') }}</span>
                                @if($remainingComplaintMembers > 0)
                                  <span class="relative ml-1 inline-flex items-center group/overflow">
                                    <span class="cursor-pointer text-sm font-medium text-slate-500">+{{ $remainingComplaintMembers }}</span>
                                    <span class="pointer-events-none absolute left-1/2 top-full z-50 mt-2 hidden -translate-x-1/2 whitespace-nowrap rounded-md bg-slate-900 px-3 py-2 text-xs font-medium text-white shadow-lg group-hover/overflow:block">
                                      {!! $remainingComplaintMemberNames->map(fn ($member) => e($member))->implode('<br>') !!}
                                    </span>
                                  </span>
                                @endif
                              @else
                                <span class="text-sm font-medium text-slate-400">—</span>
                              @endif
                            </td>
                            <td class="align-middle whitespace-nowrap px-4 py-3 text-sm text-slate-700">{{ \Carbon\Carbon::parse($row['event_date'])->format('M d, Y') }}</td>
                            <td class="align-middle whitespace-nowrap px-4 py-3 text-sm text-slate-700">{{ $row['category'] }}</td>
                            <td class="align-middle px-4 py-3">
                              <span class="inline-flex items-center whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium {{ $complaintPriorityClass }}">{{ $row['priority'] }}</span>
                            </td>
                            <td class="align-middle px-4 py-3">
                              <span class="inline-flex items-center whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium {{ $complaintStatusClass }}">{{ $row['resolution_status'] }}</span>
                            </td>
                            <td class="align-middle px-4 py-3 text-sm leading-5 text-slate-700">{{ $row['assistant'] ?: 'Unassigned' }}</td>
                            <td class="align-top px-4 py-3">
                              <div class="max-w-none">
                                <p class="text-sm text-slate-600 leading-6 line-clamp-2">{{ $row['description'] }}</p>
                              </div>
                            </td>
                            <td class="align-middle px-4 py-3 text-center">
                              <a class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 transition-colors hover:bg-slate-50" href="{{ route('admin.feedback', array_merge($queryBase, ['tab' => 'complaints', 'item' => $row['complaint_id']])) }}">Edit</a>
                            </td>
                          </tr>
                        @empty
                          <tr>
                            <td colspan="9" class="px-4 py-6 text-sm text-slate-500">No complaints match the current filters.</td>
                          </tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>
                </div>
              @elseif($activeTab === 'good-feedback')
                <div class="table-wrap">
                  <table class="data-table">
                    <thead>
                      <tr>
                        <th>Feedback ID</th>
                        <th>Event Date</th>
                        <th>Date Received</th>
                        <th>Team Member</th>
                        <th>Source</th>
                        <th>Recognition</th>
                        <th>Assistant</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($goodFeedback as $row)
                        <tr class="is-clickable" data-href="{{ route('admin.feedback', array_merge($queryBase, ['tab' => 'good-feedback', 'item' => $row['feedback_id']])) }}">
                          <td><span class="cell-strong">{{ $row['feedback_id'] }}</span><span class="cell-meta"><span class="badge {{ strtolower(str_replace(' ', '-', $row['status'])) }}">{{ $row['status'] }}</span></span></td>
                          <td>{{ \Carbon\Carbon::parse($row['event_date'])->format('M d, Y') }}</td>
                          <td>{{ \Carbon\Carbon::parse($row['date_received'])->format('M d, Y') }}</td>
                          <td class="cell-strong">{{ $row['chef'] }}@if(!empty($row['staff_type']))<span class="cell-meta">{{ $row['staff_type'] }}</span>@endif</td>
                          <td>{{ $row['source'] }}</td>
                          <td class="cell-copy">{{ $row['compliment'] }}</td>
                          <td>{{ $row['assistant'] }}<span class="cell-meta"><a class="action-link" href="{{ route('admin.feedback', array_merge($queryBase, ['tab' => 'good-feedback', 'item' => $row['feedback_id']])) }}">Preview</a></span></td>
                        </tr>
                      @empty
                        <tr><td colspan="7" class="cell-copy">No good feedback records match the current filters.</td></tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              @elseif($activeTab === 'van-feedback')
                <div class="table-wrap">
                  <table class="data-table">
                    <thead>
                      <tr>
                        <th>Van Issue ID</th>
                        <th>Event Date</th>
                        <th>Date Received</th>
                        <th>Team Member</th>
                        <th>Van</th>
                        <th>Description</th>
                        <th>Action Taken</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($vanFeedback as $row)
                        <tr class="is-clickable" data-href="{{ route('admin.feedback', array_merge($queryBase, ['tab' => 'van-feedback', 'item' => $row['vanfb_id']])) }}">
                          <td><span class="cell-strong">{{ $row['vanfb_id'] }}</span><span class="cell-meta"><span class="badge {{ strtolower(str_replace(' ', '-', $row['status'])) }}">{{ $row['status'] }}</span></span></td>
                          <td>{{ \Carbon\Carbon::parse($row['event_date'])->format('M d, Y') }}</td>
                          <td>{{ \Carbon\Carbon::parse($row['date_received'])->format('M d, Y') }}</td>
                          <td class="cell-strong">{{ $row['chef'] }}@if(!empty($row['staff_type']))<span class="cell-meta">{{ $row['staff_type'] }}</span>@endif</td>
                          <td>{{ $row['van'] }}</td>
                          <td class="cell-copy">{{ $row['description'] }}</td>
                          <td class="cell-copy">{{ $row['action_taken'] }}<span class="cell-meta"><a class="action-link" href="{{ route('admin.feedback', array_merge($queryBase, ['tab' => 'van-feedback', 'item' => $row['vanfb_id']])) }}">Preview</a></span></td>
                        </tr>
                      @empty
                        <tr><td colspan="7" class="cell-copy">No van issues match the current filters.</td></tr>
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
                        <th>Team Member</th>
                        <th>Incident Type</th>
                        <th>Units</th>
                        <th>Authorized</th>
                        <th>Manager</th>
                        <th>Notes</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($attendance as $row)
                        <tr class="is-clickable" data-href="{{ route('admin.feedback', array_merge($queryBase, ['tab' => 'attendance', 'item' => $row['incident_id']])) }}">
                          <td><span class="cell-strong">{{ $row['incident_id'] }}</span></td>
                          <td>{{ \Carbon\Carbon::parse($row['date'])->format('M d, Y') }}</td>
                          <td class="cell-strong">{{ $row['chef'] }}@if(!empty($row['staff_type']))<span class="cell-meta">{{ $row['staff_type'] }}</span>@endif</td>
                          <td>{{ $row['incident_type'] }}</td>
                          <td>{{ $row['units'] }}</td>
                          <td><span class="badge {{ $row['authorized'] ? 'authorized' : 'unauthorized' }}">{{ $row['authorized'] ? 'Yes' : 'No' }}</span></td>
                          <td>{{ $row['manager'] }}</td>
                          <td class="cell-copy">{{ $row['notes'] }}<span class="cell-meta"><a class="action-link" href="{{ route('admin.feedback', array_merge($queryBase, ['tab' => 'attendance', 'item' => $row['incident_id']])) }}">Preview</a></span></td>
                        </tr>
                      @empty
                        <tr><td colspan="8" class="cell-copy">No attendance incidents match the current filters.</td></tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              @elseif($activeTab === 'days-off')
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                  <div class="overflow-x-auto">
                  <table class="min-w-[1260px] w-full table-fixed">
                    <colgroup>
                      <col class="w-28">
                      <col class="w-44">
                      <col class="w-32">
                      <col class="w-32">
                      <col class="w-24">
                      <col class="w-16">
                      <col class="w-36">
                      <col class="min-w-[280px] w-full">
                      <col class="w-20">
                      <col class="w-16">
                    </colgroup>
                    <thead class="bg-slate-50/80">
                        <tr>
                          <th class="w-24 whitespace-nowrap border-b border-slate-200 px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Request ID</th>
                          <th class="w-44 whitespace-nowrap border-b border-slate-200 px-4 py-3 text-left">
                            <a class="inline-flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.12em] {{ $daysOffSort === 'chef' ? 'text-slate-700' : 'text-slate-500 hover:text-slate-700' }}" href="{{ $daysOffSortLink('chef') }}">
                              Team Member
                              {!! $daysOffSortIcon('chef') !!}
                            </a>
                          </th>
                          <th class="w-32 whitespace-nowrap border-b border-slate-200 px-4 py-3 text-left">
                            <a class="inline-flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.12em] {{ $daysOffSort === 'start_date' ? 'text-slate-700' : 'text-slate-500 hover:text-slate-700' }}" href="{{ $daysOffSortLink('start_date') }}">
                              Start Date
                              {!! $daysOffSortIcon('start_date') !!}
                            </a>
                          </th>
                          <th class="w-32 whitespace-nowrap border-b border-slate-200 px-4 py-3 text-left">
                            <a class="inline-flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.12em] {{ $daysOffSort === 'end_date' ? 'text-slate-700' : 'text-slate-500 hover:text-slate-700' }}" href="{{ $daysOffSortLink('end_date') }}">
                              End Date
                              {!! $daysOffSortIcon('end_date') !!}
                            </a>
                          </th>
                          <th class="w-24 whitespace-nowrap border-b border-slate-200 px-4 py-3 text-left">
                            <a class="inline-flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.12em] {{ $daysOffSort === 'status' ? 'text-slate-700' : 'text-slate-500 hover:text-slate-700' }}" href="{{ $daysOffSortLink('status') }}">
                              Status
                              {!! $daysOffSortIcon('status') !!}
                            </a>
                          </th>
                          <th class="w-16 whitespace-nowrap border-b border-slate-200 px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Days</th>
                          <th class="w-36 whitespace-nowrap border-b border-slate-200 px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Approved By</th>
                          <th class="min-w-[280px] w-full whitespace-nowrap border-b border-slate-200 px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Notes</th>
                          <th class="w-20 whitespace-nowrap border-b border-slate-200 px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Edit</th>
                          <th class="w-16 whitespace-nowrap border-b border-slate-200 px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Flags</th>
                        </tr>
                    </thead>
                    <tbody>
                      @forelse($daysOff as $row)
                        @php
                          $statusClass = match (strtolower($row['status'])) {
                              'approved' => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200',
                              'pending' => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200',
                              'denied', 'rejected' => 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200',
                              'cancelled', 'closed' => 'bg-slate-100 text-slate-700 ring-1 ring-inset ring-slate-200',
                              default => 'bg-slate-100 text-slate-700 ring-1 ring-inset ring-slate-200',
                          };
                          $unauthorizedClass = $row['unauthorized_days'] > 0
                              ? 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200'
                              : 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200';
                        @endphp
                        <tr class="group is-clickable cursor-pointer border-b border-slate-100 transition-colors hover:bg-slate-50/70" data-href="{{ route('admin.feedback', array_merge($queryBase, ['tab' => 'days-off', 'item' => $row['request_id']])) }}">
                          <td class="align-middle whitespace-nowrap px-4 py-3">
                            <span class="whitespace-nowrap text-sm font-semibold text-slate-900 transition-colors group-hover:text-slate-950">{{ $row['request_id'] }}</span>
                          </td>
                          <td class="align-middle whitespace-nowrap px-4 py-3">
                            <span class="text-sm font-semibold text-slate-900 transition-colors group-hover:text-slate-950">{{ $row['chef'] }}</span>
                            @if(!empty($row['staff_type']))
                              <span class="font-medium text-slate-500"> · {{ $row['staff_type'] }}</span>
                            @endif
                          </td>
                          <td class="align-middle whitespace-nowrap px-4 py-3 text-sm text-slate-700">{{ \Carbon\Carbon::parse($row['start_date'])->format('M d, Y') }}</td>
                          <td class="align-middle whitespace-nowrap px-4 py-3 text-sm text-slate-700">{{ \Carbon\Carbon::parse($row['end_date'])->format('M d, Y') }}</td>
                          <td class="align-middle px-4 py-3">
                            <span class="inline-flex items-center whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium {{ $statusClass }}">{{ $row['status'] }}</span>
                          </td>
                          <td class="align-middle px-4 py-3 text-center tabular-nums text-sm font-medium text-slate-800">{{ $row['days'] }}</td>
                          <td class="align-middle px-4 py-3 text-sm leading-5 text-slate-700">{{ $row['approved_by'] }}</td>
                          <td class="align-top px-4 py-3">
                            <div class="max-w-none">
                              <p class="text-sm text-slate-600 leading-6 line-clamp-2">{{ $row['notes'] }}</p>
                            </div>
                          </td>
                          <td class="align-middle px-4 py-3 text-center">
                            <a class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 transition-colors hover:bg-slate-50" href="{{ route('admin.feedback', array_merge($queryBase, ['tab' => 'days-off', 'item' => $row['request_id']])) }}">Edit</a>
                          </td>
                          <td class="align-top px-4 py-3 text-center">
                            <span class="inline-flex min-w-[28px] items-center justify-center rounded-full px-2 py-0.5 text-xs font-semibold tabular-nums {{ $unauthorizedClass }}">{{ $row['unauthorized_days'] }}</span>
                          </td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="10" class="px-4 py-6 text-sm text-slate-500">No time-off records match the current filters.</td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                  </div>
                </div>
              @elseif($activeTab === 'alerts')
                <div class="table-wrap">
                  <table class="data-table">
                    <thead>
                      <tr>
                        <th>Alert ID</th>
                        <th>Date</th>
                        <th>Team Member</th>
                        <th>Alert Type</th>
                        <th>Severity</th>
                        <th>Status</th>
                        <th>Details</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($alerts as $row)
                        <tr class="is-clickable" data-href="{{ route('admin.feedback', array_merge($queryBase, ['tab' => 'alerts', 'item' => $row['alert_id']])) }}">
                          <td><span class="cell-strong">{{ $row['alert_id'] }}</span></td>
                          <td>{{ \Carbon\Carbon::parse($row['date'])->format('M d, Y') }}</td>
                          <td class="cell-strong">{{ $row['chef'] }}@if(!empty($row['staff_type']))<span class="cell-meta">{{ $row['staff_type'] }}</span>@endif</td>
                          <td>{{ $row['type'] }}</td>
                          <td><span class="badge {{ strtolower(str_replace(' ', '-', $row['severity'])) }}">{{ $row['severity'] }}</span></td>
                          <td><span class="badge {{ strtolower(str_replace(' ', '-', $row['status'])) }}">{{ $row['status'] }}</span></td>
                          <td class="cell-copy">{{ $row['details'] }}<span class="cell-meta"><a class="action-link" href="{{ route('admin.feedback', array_merge($queryBase, ['tab' => 'alerts', 'item' => $row['alert_id']])) }}">Preview</a></span></td>
                        </tr>
                      @empty
                        <tr><td colspan="7" class="cell-copy">No alerts match the current filters.</td></tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    @else
      <div class="surface-card">
        <div class="surface-body">
          <div class="section-head">
            <div>
              <h2 class="section-title">Team Operations Dashboard</h2>
              <p class="section-subtitle">Analytics reacts to the same search, date range, team member, staff type, source, and status filters used by the case queues.</p>
            </div>
            <div class="count-pill">{{ number_format($totalFilteredCases) }} total filtered cases</div>
          </div>

          <div class="stats-grid">
            @foreach($stats as $stat)
              <div class="stat-card tone-{{ $stat['tone'] }}">
                <div class="stat-top">
                  <div class="stat-label">{{ $stat['label'] }}</div>
                  <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="currentColor">{!! $icons[$stat['tone']] ?? $icons['neutral'] !!}</svg>
                  </div>
                </div>
                <div class="stat-value">{{ is_numeric($stat['value']) ? number_format($stat['value']) : $stat['value'] }}</div>
                <div class="stat-note">{{ $stat['note'] }}</div>
                <div style="margin-top:10px">
                  <span class="trend-chip {{ $stat['trend_direction'] ?? 'flat' }}">{{ $stat['trend'] ?? 'No change' }}</span>
                </div>
              </div>
            @endforeach
          </div>

          <div class="analytics-grid">
            <div class="chart-card">
              <div class="chart-head">
                <div>
                  <h3 class="chart-title">Team Performance by Member</h3>
                  <p class="chart-copy">Highest-to-lowest team activity based on selected operational metric.</p>
                </div>
                <div class="chart-note">By metric</div>
              </div>
              <div class="mt-3 flex flex-wrap gap-2" data-team-performance-switcher>
                <button class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-3 py-1.5 text-xs font-medium text-white shadow-sm" type="button" data-team-performance-tab="complaints">Complaints</button>
                <button class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50" type="button" data-team-performance-tab="good-feedback">Good Feedback</button>
                <button class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50" type="button" data-team-performance-tab="van-feedback">Van Issues</button>
                <button class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50" type="button" data-team-performance-tab="attendance">Attendance</button>
                <button class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50" type="button" data-team-performance-tab="days-off">Days Off</button>
              </div>
              <div class="chart-canvas compact" style="margin-top:12px"><canvas id="teamPerformanceByMemberChart"></canvas></div>
            </div>

            <div class="chart-card">
              <div class="chart-head">
                <div>
                  <h3 class="chart-title">Team Recognition & Alerts</h3>
                  <p class="chart-copy">Top performers and the employees with the highest complaint volume.</p>
                </div>
                <div class="chart-note">Leaderboard</div>
              </div>
              <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3">
                  <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Top Good Feedback</div>
                  <p class="mt-1 text-sm text-slate-500">Employees receiving the most positive feedback.</p>
                  <div class="mt-3 space-y-2">
                    @forelse($topGoodFeedbackLeaderboard as $index => $entry)
                      <div class="flex items-center justify-between gap-3 rounded-lg bg-white px-3 py-2">
                        <div class="flex items-center gap-3 min-w-0">
                          <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-50 text-xs font-semibold text-emerald-700">{{ $index + 1 }}</span>
                          <span class="truncate text-sm font-medium text-slate-900">{{ $entry['name'] }}</span>
                        </div>
                        <span class="shrink-0 text-sm font-semibold text-emerald-700">⭐ {{ $entry['count'] }}</span>
                      </div>
                    @empty
                      <div class="rounded-lg bg-white px-3 py-2 text-sm text-slate-500">No good feedback matches the current filters.</div>
                    @endforelse
                  </div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3">
                  <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Top Complaints</div>
                  <p class="mt-1 text-sm text-slate-500">Employees associated with the most complaints.</p>
                  <div class="mt-3 space-y-2">
                    @forelse($topComplaintsLeaderboard as $index => $entry)
                      <div class="flex items-center justify-between gap-3 rounded-lg bg-white px-3 py-2">
                        <div class="flex items-center gap-3 min-w-0">
                          <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-rose-50 text-xs font-semibold text-rose-700">{{ $index + 1 }}</span>
                          <span class="truncate text-sm font-medium text-slate-900">{{ $entry['name'] }}</span>
                        </div>
                        <span class="shrink-0 text-sm font-semibold text-rose-700">⚠ {{ $entry['count'] }}</span>
                      </div>
                    @empty
                      <div class="rounded-lg bg-white px-3 py-2 text-sm text-slate-500">No complaints match the current filters.</div>
                    @endforelse
                  </div>
                </div>
              </div>
            </div>

            <div class="chart-card">
              <div class="chart-head">
                <div>
                  <h3 class="chart-title">Cases by Type</h3>
                  <p class="chart-copy">Current filtered mix across all supported Feedback Center record types.</p>
                </div>
                <div class="chart-note">Portfolio</div>
              </div>
              <div class="chart-canvas compact"><canvas id="casesByTypeChart"></canvas></div>
            </div>

            <div class="chart-card">
              <div class="chart-head">
                <div>
                  <h3 class="chart-title">Cases by Staff Type</h3>
                  <p class="chart-copy">Which staff groups are associated with the most cases in the current filter set.</p>
                </div>
                <div class="chart-note">Coverage</div>
              </div>
              <div class="chart-canvas compact"><canvas id="staffTypeBreakdownChart"></canvas></div>
            </div>

            <div class="chart-card">
              <div class="chart-head">
                <div>
                  <h3 class="chart-title">Status Breakdown</h3>
                  <p class="chart-copy">Workflow-oriented distribution across pending, in review, escalated, resolved, and closed states.</p>
                </div>
                <div class="chart-note">Workflow</div>
              </div>
              <div class="chart-canvas compact"><canvas id="statusBreakdownChart"></canvas></div>
            </div>

            <div class="chart-card">
              <div class="chart-head">
                <div>
                  <h3 class="chart-title">Monthly Trend</h3>
                  <p class="chart-copy">Overall case volume trend by core workflow type over time.</p>
                </div>
                <div class="chart-note">Time series</div>
              </div>
              <div class="chart-canvas compact"><canvas id="monthlyTrendChart"></canvas></div>
            </div>

            <div class="chart-card">
              <div class="chart-head">
                <div>
                  <h3 class="chart-title">Complaint Categories</h3>
                  <p class="chart-copy">Top complaint categories by frequency in the current filtered complaint set.</p>
                </div>
                <div class="chart-note">Root causes</div>
              </div>
              <div class="chart-canvas compact"><canvas id="complaintCategoriesChart"></canvas></div>
            </div>

            <div class="chart-card">
              <div class="chart-head">
                <div>
                  <h3 class="chart-title">Employee Performance Score</h3>
                  <p class="chart-copy">Composite performance score based on feedback, complaints, and operational incidents.</p>
                </div>
                <div class="chart-note">Score</div>
              </div>
              <div class="chart-canvas compact"><canvas id="employeePerformanceScoreChart"></canvas></div>
            </div>

            <div class="chart-card wide">
              <div class="chart-head">
                <div>
                  <h3 class="chart-title">Net Score Trend</h3>
                  <p class="chart-copy">Good feedback minus complaints, trended over time for quality visibility.</p>
                </div>
                <div class="chart-note">Quality</div>
              </div>
              <div class="chart-canvas"><canvas id="netScoreTrendChart"></canvas></div>
            </div>

            <div class="chart-card wide">
              <div class="chart-head">
                <div>
                  <h3 class="chart-title">Operational Risk Trend</h3>
                  <p class="chart-copy">Attendance incidents, unauthorized days, and van issues in one risk view.</p>
                </div>
                <div class="chart-note">Risk signals</div>
              </div>
              <div class="chart-canvas"><canvas id="operationalIncidentsTrendChart"></canvas></div>
            </div>
          </div>
        </div>
      </div>
    @endif

  </div>

  @if($activeView === 'cases')
    <div class="detail-overlay {{ $isPreviewOpen ? 'open' : '' }}" data-preview-close></div>
    <div class="detail-panel {{ $isPreviewOpen ? 'open' : '' }}" aria-hidden="{{ $isPreviewOpen ? 'false' : 'true' }}">
      <div class="detail-shell">
        <div class="surface-card detail-card drawer">
        @if(empty($preview['facts']) && empty($preview['sections']) && empty($preview['history']))
            <div class="empty-state" style="padding:28px 20px">
              <h3 class="empty-title">Select a case</h3>
              <p class="empty-copy">Open any row in the current queue to inspect context, handoff details, and case history without shrinking the queue.</p>
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
            @if(!empty($preview['editable']))
              <div class="workflow-summary">
                <div class="workflow-kicker">Case Summary</div>
                <p class="detail-copy" style="color:#0f172a;font-weight:700">{{ $preview['title'] }}</p>
                <div class="workflow-meta">
                  <div class="workflow-meta-row"><span>Case Type</span><strong>{{ $preview['case_type'] ?? 'Case' }}</strong></div>
                  <div class="workflow-meta-row"><span>{{ ($preview['item_group'] ?? '') === 'complaints' ? 'Team Members' : 'Team Member' }}</span><strong>{{ $preview['team_member'] ?? 'Unassigned' }}</strong></div>
                  <div class="workflow-meta-row"><span>{{ ($preview['item_group'] ?? '') === 'complaints' ? 'Complaint ID' : 'Case ID' }}</span><strong>{{ $preview['item_id'] ?? 'N/A' }}</strong></div>
                  @if(!empty($preview['priority']))
                    <div class="workflow-meta-row"><span>Priority</span><strong>{{ $preview['priority'] }}</strong></div>
                  @endif
                </div>
              </div>

              <form class="workflow-form" method="post" action="{{ $workflowUpdateRoute }}">
                @csrf
                <input type="hidden" name="item_id" value="{{ $preview['item_id'] ?? '' }}">
                <input type="hidden" name="item_group" value="{{ $preview['item_group'] ?? '' }}">
                <input type="hidden" name="back" value="{{ $workflowBackUrl }}">

                <div class="workflow-actions">
                  <button class="workflow-btn primary" type="submit" name="workflow_action" value="save">Save changes</button>
                </div>

                @if(($preview['item_group'] ?? '') === 'complaints')
                  <div class="detail-block">
                    <div class="fact-label" style="margin-bottom:8px">Complaint Details</div>
                    <div class="workflow-grid">
                      <div class="workflow-field" style="grid-column:1 / -1">
                        @include('admin.partials.team-members-multiselect', [
                          'fieldId' => 'complaint-team-members',
                          'fieldName' => 'team_members',
                          'fieldLabel' => 'Team Members',
                          'options' => $preview['team_member_options'] ?? [],
                          'selected' => $preview['team_members'] ?? [],
                          'max' => 7,
                          'placeholder' => 'Search active team members...',
                        ])
                      </div>
                      <div class="workflow-field">
                        <label for="complaint-status">Status</label>
                        <select id="complaint-status" name="status">
                          @foreach(($preview['status_options'] ?? []) as $option)
                            <option value="{{ $option }}" {{ ($preview['status'] ?? '') === $option ? 'selected' : '' }}>{{ $option }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="workflow-field">
                        <label for="complaint-owner">Owner</label>
                        <input id="complaint-owner" list="complaint-owner-options" name="owner" value="{{ $preview['owner'] ?? '' }}">
                        <datalist id="complaint-owner-options">
                          @foreach(($preview['owner_options'] ?? []) as $option)
                            <option value="{{ $option }}"></option>
                          @endforeach
                        </datalist>
                      </div>
                      <div class="workflow-field">
                        <label for="complaint-source">Source</label>
                        <input id="complaint-source" type="text" name="source" value="{{ $preview['source'] ?? '' }}">
                      </div>
                      <div class="workflow-field" style="grid-column:1 / -1">
                        <label for="complaint-summary">Summary</label>
                        <textarea id="complaint-summary" name="summary" placeholder="Summarize the complaint and customer impact...">{{ $preview['summary'] ?? '' }}</textarea>
                      </div>
                    </div>
                  </div>
                @elseif(($preview['item_group'] ?? '') === 'days-off')
                  <div class="detail-block">
                    <div class="fact-label" style="margin-bottom:8px">Days Off Details</div>
                    <div class="workflow-grid">
                      <div class="workflow-field">
                        <label for="days-off-start-date">Start Date</label>
                        <input id="days-off-start-date" type="date" name="start_date" value="{{ $preview['start_date'] ?? '' }}">
                      </div>
                      <div class="workflow-field">
                        <label for="days-off-end-date">End Date</label>
                        <input id="days-off-end-date" type="date" name="end_date" value="{{ $preview['end_date'] ?? '' }}">
                      </div>
                      <div class="workflow-field">
                        <label for="days-off-status">Status</label>
                        <select id="days-off-status" name="status">
                          @foreach(($preview['status_options'] ?? []) as $option)
                            <option value="{{ $option }}" {{ ($preview['status'] ?? '') === $option ? 'selected' : '' }}>{{ $option }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="workflow-field">
                        <label for="days-off-days">Days</label>
                        <input id="days-off-days" type="text" name="days" value="{{ $preview['days'] ?? '' }}" readonly>
                      </div>
                      <div class="workflow-field" style="grid-column:1 / -1">
                        <label for="days-off-approved-by">Approved By</label>
                        <input id="days-off-approved-by" list="days-off-approver-options" name="approved_by" value="{{ $preview['approved_by'] ?? '' }}">
                        <datalist id="days-off-approver-options">
                          @foreach(($preview['approved_by_options'] ?? []) as $option)
                            <option value="{{ $option }}"></option>
                          @endforeach
                        </datalist>
                      </div>
                    </div>
                  </div>
                @endif

                @if(!empty($preview['facts']))
                  <div class="detail-block">
                    <div class="fact-label" style="margin-bottom:8px">Details</div>
                    <div class="workflow-details-grid">
                      @foreach($preview['facts'] as $label => $value)
                        <div class="fact-card">
                          <span class="fact-label">{{ $label }}</span>
                          <span class="fact-value">{{ $value }}</span>
                        </div>
                      @endforeach
                    </div>
                  </div>
                @endif

                @foreach($preview['sections'] as $section)
                  <div class="detail-block">
                    <div class="fact-label" style="margin-bottom:8px">{{ $section['label'] }}</div>
                    <p class="detail-copy">{{ filled($section['value'] ?? '') ? $section['value'] : 'No action recorded yet.' }}</p>
                  </div>
                @endforeach

                <div class="detail-block">
                  <div class="fact-label" style="margin-bottom:8px">{{ ($preview['item_group'] ?? '') === 'complaints' ? 'Internal Note / Action Taken' : 'Notes' }}</div>
                  <div class="workflow-field">
                    <textarea id="workflow-note" name="internal_note" placeholder="{{ ($preview['item_group'] ?? '') === 'complaints' ? 'Document follow-up actions, owner handoff, or resolution notes...' : 'Update request notes...' }}">{{ ($preview['item_group'] ?? '') === 'complaints' ? ($preview['internal_note'] ?? '') : ($preview['notes'] ?? '') }}</textarea>
                  </div>
                </div>
              </form>
            @else
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
                  <div class="fact-label" style="margin-bottom:8px">{{ $section['label'] }}</div>
                  <p class="detail-copy">{{ filled($section['value'] ?? '') ? $section['value'] : 'No action recorded yet.' }}</p>
                </div>
              @endforeach
            @endif

            <div class="detail-block">
              <div class="fact-label" style="margin-bottom:8px">Follow-up History</div>
              @if(!empty($preview['history']))
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
                        {{ filled($item['note'] ?? '') ? $item['note'] : 'No action recorded yet.' }}
                      </div>
                    </li>
                  @endforeach
                </ul>
              @else
                <p class="workflow-empty">No action recorded yet.</p>
              @endif
            </div>
          @endif
        </div>
      </div>
    </div>
  @endif

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

      const syncDaysOffDays = () => {
        const startInput = document.getElementById('days-off-start-date');
        const endInput = document.getElementById('days-off-end-date');
        const daysInput = document.getElementById('days-off-days');
        if (!startInput || !endInput || !daysInput) return;

        const startValue = startInput.value;
        const endValue = endInput.value;
        if (!startValue || !endValue) {
          daysInput.value = '';
          return;
        }

        const toUtcTime = (value) => {
          const parts = value.split('-').map(Number);
          if (parts.length !== 3 || parts.some((part) => Number.isNaN(part))) {
            return null;
          }
          return Date.UTC(parts[0], parts[1] - 1, parts[2]);
        };

        const startTime = toUtcTime(startValue);
        const endTime = toUtcTime(endValue);
        if (startTime === null || endTime === null || endTime < startTime) {
          daysInput.value = '';
          return;
        }

        const diffDays = Math.floor((endTime - startTime) / 86400000) + 1;
        daysInput.value = String(diffDays);
      };

      ['days-off-start-date', 'days-off-end-date'].forEach((id) => {
        const input = document.getElementById(id);
        if (!input) return;
        input.addEventListener('input', syncDaysOffDays);
        input.addEventListener('change', syncDaysOffDays);
      });
      syncDaysOffDays();

      document.querySelectorAll('[data-team-members-field]').forEach((field) => {
        const searchInput = field.querySelector('.team-members-search');
        const toggleButton = field.querySelector('[data-team-members-toggle]');
        const panel = field.querySelector('[data-team-members-panel]');
        const pills = field.querySelector('[data-selected-pills]');
        const message = field.querySelector('[data-team-members-message]');
        const checkboxes = Array.from(field.querySelectorAll('[data-team-member-checkbox]'));
        const options = Array.from(field.querySelectorAll('[data-team-member-option]'));
        const max = Number(field.dataset.max || 7);
        let selected = [];

        try {
          selected = JSON.parse(field.dataset.selected || '[]');
        } catch (error) {
          selected = [];
        }

        selected = selected.filter(Boolean).slice(0, max);

        const render = () => {
          pills.innerHTML = '';
          message.textContent = selected.length >= max ? `Maximum ${max} team members per complaint.` : '';

          if (selected.length === 0) {
            const empty = document.createElement('span');
            empty.className = 'text-sm text-slate-400';
            empty.textContent = 'No team members selected yet.';
            pills.appendChild(empty);
          }

          selected.forEach((name) => {
            const pill = document.createElement('span');
            pill.className = 'inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700';
            pill.textContent = name;

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'text-slate-400 hover:text-slate-700';
            remove.textContent = '×';
            remove.addEventListener('click', () => {
              selected = selected.filter((member) => member !== name);
              syncFromState();
            });
            pill.appendChild(remove);
            pills.appendChild(pill);
          });
        };

        const syncFromState = () => {
          checkboxes.forEach((checkbox) => {
            checkbox.checked = selected.includes(checkbox.value);
            checkbox.disabled = !checkbox.checked && selected.length >= max;
          });
          render();
        };

        toggleButton?.addEventListener('click', () => {
          panel?.classList.toggle('hidden');
          if (panel && !panel.classList.contains('hidden')) {
            searchInput?.focus();
          }
        });

        checkboxes.forEach((checkbox) => {
          checkbox.addEventListener('change', () => {
            if (checkbox.checked) {
              if (selected.length >= max) {
                checkbox.checked = false;
                message.textContent = `Maximum ${max} team members per complaint.`;
                return;
              }
              selected.push(checkbox.value);
            } else {
              selected = selected.filter((member) => member !== checkbox.value);
            }
            selected = [...new Set(selected)];
            syncFromState();
          });
        });

        searchInput?.addEventListener('input', () => {
          const query = searchInput.value.trim().toLowerCase();
          options.forEach((option) => {
            const name = option.dataset.memberName?.toLowerCase() || '';
            option.classList.toggle('hidden', query !== '' && !name.includes(query));
          });
          if (panel?.classList.contains('hidden')) {
            panel.classList.remove('hidden');
          }
        });

        document.addEventListener('click', (event) => {
          if (!field.contains(event.target)) {
            panel?.classList.add('hidden');
          }
        });

        syncFromState();
      });

      document.querySelectorAll('[data-quick-date-form]').forEach((form) => {
        const fromInput = form.querySelector('input[name="from"]');
        const toInput = form.querySelector('input[name="to"]');
        const chips = Array.from(form.querySelectorAll('[data-quick-range]'));
        if (!fromInput || !toInput || chips.length === 0) {
          return;
        }

        const formatDate = (date) => {
          const year = date.getFullYear();
          const month = String(date.getMonth() + 1).padStart(2, '0');
          const day = String(date.getDate()).padStart(2, '0');
          return `${year}-${month}-${day}`;
        };

        const startOfTuesdayWeek = (baseDate) => {
          const date = new Date(baseDate);
          date.setHours(12, 0, 0, 0);
          const day = date.getDay();
          const offset = (day - 2 + 7) % 7;
          date.setDate(date.getDate() - offset);
          return date;
        };

        const getRange = (rangeKey) => {
          const today = new Date();
          today.setHours(12, 0, 0, 0);

          switch (rangeKey) {
            case 'today':
              return { from: formatDate(today), to: formatDate(today) };
            case 'this-week': {
              const start = startOfTuesdayWeek(today);
              const end = new Date(start);
              end.setDate(start.getDate() + 6);
              return { from: formatDate(start), to: formatDate(end) };
            }
            case 'last-week': {
              const end = startOfTuesdayWeek(today);
              end.setDate(end.getDate() - 1);
              const start = new Date(end);
              start.setDate(end.getDate() - 6);
              return { from: formatDate(start), to: formatDate(end) };
            }
            case 'this-month': {
              const start = new Date(today.getFullYear(), today.getMonth(), 1, 12);
              const end = new Date(today.getFullYear(), today.getMonth() + 1, 0, 12);
              return { from: formatDate(start), to: formatDate(end) };
            }
            case 'last-month': {
              const start = new Date(today.getFullYear(), today.getMonth() - 1, 1, 12);
              const end = new Date(today.getFullYear(), today.getMonth(), 0, 12);
              return { from: formatDate(start), to: formatDate(end) };
            }
            default:
              return null;
          }
        };

        const syncQuickDateState = () => {
          const currentFrom = fromInput.value;
          const currentTo = toInput.value;
          chips.forEach((chip) => chip.classList.remove('active'));

          const matchingChip = chips.find((chip) => {
            const key = chip.dataset.quickRange;
            if (key === 'custom') {
              return false;
            }
            const range = getRange(key);
            return range && range.from === currentFrom && range.to === currentTo;
          });

          if (matchingChip) {
            matchingChip.classList.add('active');
          } else if (currentFrom !== '' || currentTo !== '') {
            const customChip = chips.find((chip) => chip.dataset.quickRange === 'custom');
            customChip?.classList.add('active');
          }
        };

        chips.forEach((chip) => {
          chip.addEventListener('click', () => {
            const key = chip.dataset.quickRange || 'custom';
            if (key === 'custom') {
              chip.classList.add('active');
              fromInput.focus();
              return;
            }

            const range = getRange(key);
            if (!range) {
              return;
            }

            fromInput.value = range.from;
            toInput.value = range.to;
            syncQuickDateState();
            form.requestSubmit();
          });
        });

        fromInput.addEventListener('change', syncQuickDateState);
        toInput.addEventListener('change', syncQuickDateState);
        syncQuickDateState();
      });

      if (!window.Chart || @json($activeView !== 'analytics')) {
        return;
      }

      const charts = @json($analyticsCharts);
      const commonScales = {
        x: {
          grid: { display: false },
          ticks: { color: '#64748b', font: { size: 11, weight: '600' } },
        },
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(148,163,184,0.14)' },
          ticks: { color: '#64748b', font: { size: 11, weight: '600' } },
        },
      };
      const doughnutOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              boxWidth: 10,
              boxHeight: 10,
              usePointStyle: true,
              pointStyle: 'circle',
              color: '#475569',
              padding: 16,
            },
          },
        },
      };
      const axisOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: commonScales,
      };
      const lineOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            labels: {
              boxWidth: 10,
              boxHeight: 10,
              usePointStyle: true,
              pointStyle: 'circle',
              color: '#475569',
              padding: 16,
            },
          },
        },
        scales: commonScales,
      };

      const mountChart = (id, type, data, options) => {
        const el = document.getElementById(id);
        if (!el) return;
        new Chart(el.getContext('2d'), { type, data, options });
      };

      const teamPerformanceChartEl = document.getElementById('teamPerformanceByMemberChart');
      const teamPerformanceTabs = Array.from(document.querySelectorAll('[data-team-performance-tab]'));
      let teamPerformanceChart = null;
      if (teamPerformanceChartEl && charts.teamPerformanceByMember) {
        const setActiveTeamPerformanceMetric = (metric) => {
          const metricData = charts.teamPerformanceByMember[metric] ?? charts.teamPerformanceByMember.complaints;
          if (!metricData) return;

          teamPerformanceTabs.forEach((tab) => {
            const active = tab.dataset.teamPerformanceTab === metric;
            tab.className = active
              ? 'inline-flex items-center gap-2 rounded-full bg-slate-900 px-3 py-1.5 text-xs font-medium text-white shadow-sm'
              : 'inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50';
          });

          if (!teamPerformanceChart) {
            teamPerformanceChart = new Chart(teamPerformanceChartEl.getContext('2d'), {
              type: 'bar',
              data: metricData,
              options: axisOptions,
            });
            return;
          }

          teamPerformanceChart.data = metricData;
          teamPerformanceChart.update();
        };

        teamPerformanceTabs.forEach((tab) => {
          tab.addEventListener('click', () => setActiveTeamPerformanceMetric(tab.dataset.teamPerformanceTab || 'complaints'));
        });

        setActiveTeamPerformanceMetric('complaints');
      }

      mountChart('monthlyTrendChart', 'line', charts.monthlyTrend, lineOptions);
      mountChart('casesByTypeChart', 'doughnut', charts.casesByType, doughnutOptions);
      mountChart('staffTypeBreakdownChart', 'bar', charts.staffTypeBreakdown, axisOptions);
      mountChart('statusBreakdownChart', 'doughnut', charts.statusBreakdown, doughnutOptions);
      mountChart('complaintCategoriesChart', 'bar', charts.complaintCategories, {
        ...axisOptions,
        indexAxis: 'y',
      });
      mountChart('employeePerformanceScoreChart', 'bar', charts.employeePerformanceScore, {
        ...axisOptions,
        indexAxis: 'y',
      });
      mountChart('netScoreTrendChart', 'line', charts.netScoreTrend, lineOptions);
      mountChart('operationalIncidentsTrendChart', 'line', charts.operationalIncidentsTrend, lineOptions);
    });
  </script>
</body>
</html>
