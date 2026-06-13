<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Admin – Dashboard</title>
  <style>
    :root{--bg:#f7f7fb;--text:#111827;--muted:#6b7280;--card:#fff;--border:#e5e7eb;--brand:#b21e27;--brand-hover:#9a1a22}
    *{box-sizing:border-box}
    body{margin:0;background:var(--bg);color:var(--text);font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif}
    .shell{display:grid;grid-template-columns:280px 1fr;min-height:100vh}
    @media (max-width: 900px){.shell{grid-template-columns:1fr}}
    .sidebar{background:#fff;border-right:1px solid var(--border);display:flex;flex-direction:column;min-height:100vh}
    .side-head{display:flex;flex-direction:column;gap:2px;padding:16px;border-bottom:1px solid var(--border)}
    .side-title{font-size:13px;font-weight:700;letter-spacing:.08em;color:#111827}
    .side-subtitle{font-size:12px;color:#6b7280}
    .side-menu{padding:8px;display:flex;flex-direction:column;gap:6px;flex:1 1 auto}
    .nav-section{display:flex;flex-direction:column;gap:4px}
    .section-toggle{appearance:none;-webkit-appearance:none;width:100%;display:flex;align-items:center;gap:12px;padding:8px 12px;border-radius:10px;border:1px solid #eceff4;background:#fff;color:#111;text-align:left;min-height:48px;cursor:pointer;transition:border-color .15s,background-color .15s,box-shadow .15s}
    .section-toggle:hover{border-color:#d7dde7;background:#fbfcff;box-shadow:0 3px 10px rgba(15,23,42,.04)}
    .section-toggle.active-parent{border-color:rgba(178,30,39,.18);background:#fffafa}
    .section-title{font-size:12px;font-weight:800;line-height:1;letter-spacing:.08em;text-transform:uppercase}
    .section-children{display:flex;flex-direction:column;gap:4px;padding-left:12px;overflow:hidden;max-height:720px;opacity:1;transition:max-height .22s ease,opacity .18s ease,padding-top .22s ease,padding-bottom .22s ease}
    .nav-section.is-collapsed .section-children{max-height:0;opacity:0;padding-top:0;padding-bottom:0;pointer-events:none}
    .chevron{width:16px;height:16px;color:#64748b;margin-left:auto;flex:0 0 16px;transition:transform .18s ease}
    .nav-section.is-open .chevron{transform:rotate(90deg)}
    .nav-item{display:flex;align-items:flex-start;gap:12px;padding:8px 12px;border-radius:10px;border:1px solid #eceff4;background:#fff;color:#111;text-decoration:none;min-height:52px;transition:border-color .15s, background-color .15s, box-shadow .15s}
    .nav-item:hover{border-color:#d7dde7;background:#fbfcff;box-shadow:0 3px 10px rgba(15,23,42,.04)}
    .nav-item.active{border-color:rgba(178,30,39,.24);background:linear-gradient(180deg,#fff8f8 0%,#fff 100%);box-shadow:0 5px 12px rgba(178,30,39,.08)}
    .nav-copy{min-width:0}
    .nav-label{font-size:14px;font-weight:600;line-height:1.15;letter-spacing:-.01em}
    .nav-sub{color:var(--muted);font-size:12px;line-height:1.25;margin-top:2px}
    .icon{width:20px;height:20px;color:var(--brand);flex:0 0 20px;margin-top:1px}
    .side-user{margin-top:auto;padding:12px 16px;border-top:1px solid var(--border)}
    .side-user-name{font-size:14px;font-weight:600;line-height:1.2;color:#111827}
    .side-user-role{font-size:12px;line-height:1.2;color:#6b7280;margin-top:2px}
    .content{background:var(--bg);min-width:0}
    .content-head{display:flex;align-items:center;gap:14px;padding:16px}
    .title{font-size:22px;margin:0;flex:0 0 auto}
    .head-actions{margin-left:auto;display:flex;align-items:center;gap:14px;min-width:0}
    .logout-form{margin:0;flex:0 0 auto}
    .logout-button{padding:8px 12px;border-radius:10px;border:1px solid var(--border);background:#fff;cursor:pointer}
    .admin-online{display:flex;align-items:center;gap:12px;min-height:44px;max-width:min(660px,52vw);padding:6px 10px;border:1px solid var(--border);border-radius:14px;background:#fff;box-shadow:0 4px 14px rgba(15,23,42,.04);overflow:visible}
    .online-group{display:flex;align-items:center;gap:8px;min-width:0}
    .online-copy{min-width:0}
    .online-label{font-size:11px;font-weight:800;line-height:1.1;text-transform:uppercase;letter-spacing:.04em;color:#374151;white-space:nowrap}
    .online-count{font-size:11px;line-height:1.2;color:var(--muted);white-space:nowrap}
    .online-avatars{display:flex;align-items:center;min-width:0}
    .online-avatar{width:36px;height:36px;border-radius:999px;border:2px solid #fff;background:#f3f4f6;color:#374151;box-shadow:0 0 0 1px #dce3ec;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;line-height:1;position:relative;flex:0 0 36px;margin-left:-6px}
    .online-avatar:first-child{margin-left:0}
    .online-avatar img{width:100%;height:100%;border-radius:999px;object-fit:cover;display:block}
    .online-avatar::before{content:"";position:absolute;right:0;bottom:1px;width:9px;height:9px;border-radius:999px;background:#22c55e;border:2px solid #fff;box-shadow:0 0 0 1px rgba(34,197,94,.18)}
    .online-avatar.more::before{display:none}
    .online-avatar.more{background:#f8fafc;color:#475569;font-size:11px}
    .online-avatar[data-tooltip]:hover::after,.online-avatar[data-tooltip]:focus::after{content:attr(data-tooltip);position:absolute;left:50%;bottom:-38px;transform:translateX(-50%);padding:7px 9px;border:1px solid #dbe2ea;border-radius:9px;background:#fff;color:#111827;box-shadow:0 8px 22px rgba(15,23,42,.12);font-size:12px;font-weight:650;line-height:1.2;white-space:nowrap;z-index:20;pointer-events:none}
    .online-divider{width:1px;height:28px;background:#e5e7eb}
    .online-empty{font-size:12px;color:var(--muted);white-space:nowrap}
    .notification-center{position:relative;flex:0 0 auto}
    .notification-trigger{appearance:none;-webkit-appearance:none;position:relative;width:42px;height:42px;border:1px solid var(--border);border-radius:13px;background:#fff;color:#243b53;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 4px 14px rgba(15,23,42,.04);transition:border-color .15s,box-shadow .15s,background-color .15s}
    .notification-trigger:hover{border-color:#cbd5e1;background:#f8fafc;box-shadow:0 8px 18px rgba(15,23,42,.08)}
    .notification-trigger svg{width:19px;height:19px}
    .notification-badge{position:absolute;right:-5px;top:-5px;min-width:18px;height:18px;padding:0 5px;border-radius:999px;background:#243b53;color:#fff;border:2px solid #fff;font-size:10px;font-weight:800;line-height:14px;display:flex;align-items:center;justify-content:center}
    .notification-panel{position:absolute;right:0;top:50px;width:360px;max-width:calc(100vw - 32px);border:1px solid #dfe5ee;border-radius:16px;background:#fff;box-shadow:0 20px 44px rgba(15,23,42,.16);z-index:80;overflow:hidden}
    .notification-panel[hidden]{display:none}
    .notification-panel-head{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:13px 14px;border-bottom:1px solid #edf1f6}
    .notification-panel-title{font-size:13px;font-weight:850;color:#111827}
    .notification-panel-action{appearance:none;border:0;background:transparent;color:#243b53;font-size:12px;font-weight:800;cursor:pointer;padding:5px;border-radius:8px}
    .notification-panel-action:hover{background:#f1f5f9}
    .notification-panel-list{display:flex;flex-direction:column;max-height:390px;overflow:auto}
    .notification-panel-item{appearance:none;-webkit-appearance:none;border:0;border-bottom:1px solid #edf1f6;background:#fff;text-align:left;padding:12px 14px;display:grid;grid-template-columns:34px 1fr;gap:10px;cursor:pointer;color:inherit}
    .notification-panel-item:hover{background:#f8fafc}
    .notification-panel-item.unread{background:#f8fbff}
    .notification-panel-item:last-child{border-bottom:0}
    .notification-panel-icon{width:34px;height:34px;border-radius:11px;border:1px solid #dbe4ef;background:#f8fafc;color:#243b53;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:900;text-transform:uppercase}
    .notification-panel-copy{min-width:0}
    .notification-panel-name{font-size:13px;font-weight:800;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .notification-panel-message{font-size:12px;color:#64748b;margin-top:2px;line-height:1.35;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
    .notification-panel-meta{font-size:11px;color:#94a3b8;margin-top:5px}
    .notification-empty-state{padding:24px 14px;text-align:center;color:#64748b;font-size:12px}
    .notification-panel-foot{display:flex;justify-content:center;padding:10px;border-top:1px solid #edf1f6;background:#fbfdff}
    .notification-view-all{appearance:none;border:1px solid #dbe4ef;background:#fff;color:#243b53;border-radius:10px;min-height:34px;padding:7px 12px;font-size:12px;font-weight:800;cursor:pointer}
    .notification-view-all:hover{background:#f8fafc;border-color:#cbd5e1}
    .frame-wrap{padding:0 16px 16px}
    .frame{display:block;width:100%;min-height:calc(100vh - 72px);height:auto;border:1px solid var(--border);border-radius:14px;background:#fff}
    @media (max-width: 1180px){.admin-online{display:none}}
    @media (max-width: 720px){.notification-panel{right:-64px;width:330px}.head-actions{gap:8px}.notification-trigger{width:40px;height:40px}}
  </style>
</head>
<body>
  @php
    $u = auth()->user();
    $canViewOnlineUsers = $u && $u->hasRole(['owner', 'admin']);
  @endphp
  <div class="shell">
    <aside class="sidebar">
      <div class="side-head">
        <div class="side-title">HIBACHI ADMIN</div>
        <div class="side-subtitle">Corona HQ</div>
      </div>
      <nav class="side-menu">
        <div class="nav-section is-open" data-section="reservations">
          <button type="button" class="section-toggle" aria-expanded="true" aria-controls="section-reservations">
            <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M6 2h12a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2zm0 4v14h12V6H6zm2 3h8v2H8V9zm0 4h8v2H8v-2z"/></svg>
            <span class="section-title">Reservations</span>
            <svg class="chevron" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M7.25 4.75 12.5 10l-5.25 5.25-1.5-1.5L9.5 10 5.75 6.25l1.5-1.5z"/></svg>
          </button>
          <div class="section-children" id="section-reservations">
        @if($u && $u->hasPermission('reservations.view'))
        <a href="{{ route('admin.reservations') }}" target="pane" class="nav-item active" data-url="{{ route('admin.reservations') }}">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M6 2h12a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2zm0 4v14h12V6H6zm2 3h8v2H8V9zm0 4h8v2H8v-2z"/></svg>
          <div class="nav-copy">
            <div class="nav-label">Reservations</div>
            <div class="nav-sub">Bookings and invoices</div>
          </div>
        </a>
        @endif
        @if($u && $u->hasPermission('staff.view'))
        <a href="{{ url('/admin/staff-bookings') }}" target="pane" class="nav-item" data-url="{{ url('/admin/staff-bookings') }}">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5zm0 2c-4.4 0-8 2.2-8 5v1h16v-1c0-2.8-3.6-5-8-5z"/></svg>
          <div class="nav-copy">
            <div class="nav-label">Staff Booking</div>
            <div class="nav-sub">Internal scheduling</div>
          </div>
        </a>
        @endif
        @if($u && $u->hasPermission('calendar.view'))
        <a href="{{ url('/admin/calendar') }}" target="pane" class="nav-item" data-url="{{ url('/admin/calendar') }}">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M7 2h2v2h6V2h2v2h2a2 2 0 0 1 2 2v3H3V6a2 2 0 0 1 2-2h2V2zm15 8v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V10h20z"/></svg>
          <div class="nav-copy">
            <div class="nav-label">Calendar</div>
            <div class="nav-sub">View availability</div>
          </div>
        </a>
        @endif
        @if($u && $u->hasPermission('clients.view'))
        <a href="{{ url('/admin/clients') }}" target="pane" class="nav-item" data-url="{{ url('/admin/clients') }}">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5zm0 2c-5 0-9 2.5-9 5v1h18v-1c0-2.5-4-5-9-5z"/></svg>
          <div class="nav-copy">
            <div class="nav-label">Clients</div>
            <div class="nav-sub">Directory of customers</div>
          </div>
        </a>
        @endif
        @if($u && $u->hasPermission('reservations.view'))
        <a href="{{ route('admin.invoices') }}" target="pane" class="nav-item" data-url="{{ route('admin.invoices') }}">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M7 2h10a2 2 0 0 1 2 2v18l-3-1.5-3 1.5-3-1.5L7 22V4a2 2 0 0 1 2-2zm1 5h8V5H8v2zm0 4h8V9H8v2zm0 4h5v-2H8v2z"/></svg>
          <div class="nav-copy">
            <div class="nav-label">Invoices</div>
            <div class="nav-sub">Invoice list</div>
          </div>
        </a>
        @endif
          </div>
        </div>

        <div class="nav-section is-open" data-section="operations">
          <button type="button" class="section-toggle" aria-expanded="true" aria-controls="section-operations">
            <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M7 2h2v2h6V2h2v2h1a2 2 0 0 1 2 2v12a4 4 0 0 1-4 4H7a3 3 0 0 1-3-3V6a2 2 0 0 1 2-2h1V2zm11 8H6v9a1 1 0 0 0 1 1h9a2 2 0 0 0 2-2v-8zm-8 2h2v2h-2v-2zm4 0h2v2h-2v-2zm-4 4h6v2h-6v-2z"/></svg>
            <span class="section-title">Operations</span>
            <svg class="chevron" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M7.25 4.75 12.5 10l-5.25 5.25-1.5-1.5L9.5 10 5.75 6.25l1.5-1.5z"/></svg>
          </button>
          <div class="section-children" id="section-operations">
        @if($u && $u->hasPermission('schedule.view'))
        <a href="{{ route('admin.schedule.index') }}" target="pane" class="nav-item" data-url="{{ route('admin.schedule.index') }}">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M7 2h2v2h6V2h2v2h1a2 2 0 0 1 2 2v12a4 4 0 0 1-4 4H7a3 3 0 0 1-3-3V6a2 2 0 0 1 2-2h1V2zm11 8H6v9a1 1 0 0 0 1 1h9a2 2 0 0 0 2-2v-8zm-8 2h2v2h-2v-2zm4 0h2v2h-2v-2zm-4 4h6v2h-6v-2z"/></svg>
          <div class="nav-copy">
            <div class="nav-label">Schedule</div>
            <div class="nav-sub">Priority-based dispatch</div>
          </div>
        </a>
        @endif
        @if($u && $u->hasPermission('timeslots.view'))
        <a href="{{ route('admin.timeslots') }}" target="pane" class="nav-item" data-url="{{ route('admin.timeslots') }}">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M3 5a2 2 0 0 1 2-2h2v2h6V3h2a2 2 0 0 1 2 2v2H3V5zm0 4h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9zm4 3h6v2H7v-2zm0 4h10v2H7v-2z"/></svg>
          <div class="nav-copy">
            <div class="nav-label">Timeslots</div>
            <div class="nav-sub">Manage availability</div>
          </div>
        </a>
        @endif
        @if($u && $u->hasPermission('inventory.view'))
        <a href="{{ route('admin.inventory.dashboard') }}" target="pane" class="nav-item" data-url="{{ route('admin.inventory.dashboard') }}">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5v-9zm9-2.28L6 8.22l6 3 6-3-6-3zm-7 4.55v5.53l6 3v-5.53l-6-3zm8 8.53 6-3V9.77l-6 3v5.53z"/></svg>
          <div class="nav-copy">
            <div class="nav-label">Inventory</div>
            <div class="nav-sub">Warehouse and vans</div>
          </div>
        </a>
        @endif
        @if($u && $u->hasPermission('orders.view'))
        <a href="{{ route('admin.orders.breakdown') }}" target="pane" class="nav-item" data-url="{{ route('admin.orders.breakdown') }}">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M3 4h8v8H3zm0 10h8v6H3zm10-10h8v5h-8zm0 7h8v9h-8z"/></svg>
          <div class="nav-copy">
            <div class="nav-label">Order Breakdown</div>
            <div class="nav-sub">Sales overview</div>
          </div>
        </a>
        @endif
          </div>
        </div>

        <div class="nav-section is-open" data-section="management">
          <button type="button" class="section-toggle" aria-expanded="true" aria-controls="section-management">
            <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M4 6h16v2H4zm0 5h16v2H4zm0 5h10v2H4z"/></svg>
            <span class="section-title">Management</span>
            <svg class="chevron" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M7.25 4.75 12.5 10l-5.25 5.25-1.5-1.5L9.5 10 5.75 6.25l1.5-1.5z"/></svg>
          </button>
          <div class="section-children" id="section-management">
        @if($u && $u->hasPermission('menu.view'))
        <a href="{{ route('admin.menu') }}" target="pane" class="nav-item" data-url="{{ route('admin.menu') }}">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M4 6h16v2H4zm0 5h16v2H4zm0 5h10v2H4z"/></svg>
          <div class="nav-copy">
            <div class="nav-label">Menu</div>
            <div class="nav-sub">Edit menu & prices</div>
          </div>
        </a>
        @endif
        @if($u && $u->hasPermission('team.view'))
        <a href="{{ url('/admin/team') }}" target="pane" class="nav-item" data-url="{{ url('/admin/team') }}">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11a4 4 0 1 0-4-4 4 4 0 0 0 4 4zm-8 0a3 3 0 1 0-3-3 3 3 0 0 0 3 3zm8 2c-3.3 0-6 1.7-6 3.8V19h12v-2.2C22 14.7 19.3 13 16 13zM3 16.8V19h6v-2.2C9 15.6 7.3 15 5.5 15S2 15.6 2 16.8z"/></svg>
          <div class="nav-copy">
            <div class="nav-label">Team</div>
            <div class="nav-sub">Staff directory</div>
          </div>
        </a>
        @endif
        @if($u && $u->hasPermission('feedback.view'))
        <a href="{{ url('/admin/complains') }}" target="pane" class="nav-item" data-url="{{ url('/admin/complains') }}">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm1 15h-2v-2h2zm0-4h-2V7h2z"/></svg>
          <div class="nav-copy">
            <div class="nav-label">Feedback Center</div>
            <div class="nav-sub">Customer issues & service feedback</div>
          </div>
        </a>
        @endif
          </div>
        </div>

        <div class="nav-section is-open" data-section="admin">
          <button type="button" class="section-toggle" aria-expanded="true" aria-controls="section-admin">
            <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19.4 12.9a7.7 7.7 0 0 0 .1-1 7.7 7.7 0 0 0-.1-1l2.1-1.6a.5.5 0 0 0 .1-.6l-2-3.5a.5.5 0 0 0-.6-.2l-2.4 1a7.5 7.5 0 0 0-1.7-1l-.3-2.6A.5.5 0 0 0 14 1h-4a.5.5 0 0 0-.5.4L9.2 4a7.5 7.5 0 0 0-1.7 1l-2.4-1a.5.5 0 0 0-.6.2l-2 3.5a.5.5 0 0 0 .1.6L4.7 11a7.7 7.7 0 0 0-.1 1 7.7 7.7 0 0 0 .1 1L2.6 14.6a.5.5 0 0 0-.1.6l2 3.5a.5.5 0 0 0 .6.2l2.4-1a7.5 7.5 0 0 0 1.7 1l.3 2.6a.5.5 0 0 0 .5.4h4a.5.5 0 0 0 .5-.4l.3-2.6a7.5 7.5 0 0 0 1.7-1l2.4 1a.5.5 0 0 0 .6-.2l2-3.5a.5.5 0 0 0-.1-.6zm-7.4 2.1a3 3 0 1 1 3-3 3 3 0 0 1-3 3z"/></svg>
            <span class="section-title">Admin</span>
            <svg class="chevron" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M7.25 4.75 12.5 10l-5.25 5.25-1.5-1.5L9.5 10 5.75 6.25l1.5-1.5z"/></svg>
          </button>
          <div class="section-children" id="section-admin">
        @if($u && $u->hasPermission('financial.view'))
        <a href="{{ route('admin.reports.financial') }}" target="pane" class="nav-item" data-url="{{ route('admin.reports.financial') }}">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M4 18h16v2H4zm1-4h3v3H5zm5-6h3v9h-3zm5 2h3v7h-3z"/></svg>
          <div class="nav-copy">
            <div class="nav-label">Financial Overview</div>
            <div class="nav-sub">Profit &amp; loss dashboard</div>
          </div>
        </a>
        @endif
        @if($u && $u->hasPermission('reports.view'))
        <a href="{{ url('/admin/reports') }}" target="pane" class="nav-item" data-url="{{ url('/admin/reports') }}">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M3 3h18v2H3zm2 4h14v2H5zm-2 4h18v2H3zm2 4h10v2H5z"/></svg>
          <div class="nav-copy">
            <div class="nav-label">Reports</div>
            <div class="nav-sub">KPIs and exports</div>
          </div>
        </a>
        @endif
        @if($u && $u->hasPermission('settings.view'))
        <a href="{{ url('/admin/settings') }}" target="pane" class="nav-item" data-url="{{ url('/admin/settings') }}">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19.4 12.9a7.7 7.7 0 0 0 .1-1 7.7 7.7 0 0 0-.1-1l2.1-1.6a.5.5 0 0 0 .1-.6l-2-3.5a.5.5 0 0 0-.6-.2l-2.4 1a7.5 7.5 0 0 0-1.7-1l-.3-2.6A.5.5 0 0 0 14 1h-4a.5.5 0 0 0-.5.4L9.2 4a7.5 7.5 0 0 0-1.7 1l-2.4-1a.5.5 0 0 0-.6.2l-2 3.5a.5.5 0 0 0 .1.6L4.7 11a7.7 7.7 0 0 0-.1 1 7.7 7.7 0 0 0 .1 1L2.6 14.6a.5.5 0 0 0-.1.6l2 3.5a.5.5 0 0 0 .6.2l2.4-1a7.5 7.5 0 0 0 1.7 1l.3 2.6a.5.5 0 0 0 .5.4h4a.5.5 0 0 0 .5-.4l.3-2.6a7.5 7.5 0 0 0 1.7-1l2.4 1a.5.5 0 0 0 .6-.2l2-3.5a.5.5 0 0 0-.1-.6zm-7.4 2.1a3 3 0 1 1 3-3 3 3 0 0 1-3 3z"/></svg>
          <div class="nav-copy">
            <div class="nav-label">Settings</div>
            <div class="nav-sub">App configuration</div>
          </div>
        </a>
        @endif
        @if($u && $u->hasPermission('trash.view'))
        <a href="{{ route('admin.trash') }}" target="pane" class="nav-item" data-url="{{ route('admin.trash') }}">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M9 3h6l1 2h5v2H3V5h5l1-2zm1 6h2v10h-2V9zm4 0h2v10h-2V9zM7 9h2v10H7V9z"/></svg>
          <div class="nav-copy">
            <div class="nav-label">Trash</div>
            <div class="nav-sub">Recently deleted</div>
          </div>
        </a>
        @endif
          </div>
        </div>
      </nav>
      <div class="side-user">
        <div class="side-user-name">{{ $u?->name ?? 'Administrator' }}</div>
        <div class="side-user-role">Administrator</div>
      </div>
    </aside>
    <main class="content">
      <div class="content-head">
        <h1 class="title" id="paneTitle">Reservations</h1>
        <div class="head-actions">
          @if($canViewOnlineUsers)
            <section
              class="admin-online"
              data-online-users
              data-endpoint="{{ route('admin.online-users.index') }}"
              aria-label="Online users"
            >
              <div class="online-group" data-online-group="staff">
                <div class="online-copy">
                  <div class="online-label">Staff Online</div>
                  <div class="online-count" data-online-count="staff">0 active</div>
                </div>
                <div class="online-avatars" data-online-avatars="staff"></div>
              </div>
              <div class="online-divider" data-online-divider hidden></div>
              <div class="online-group" data-online-group="admins" hidden>
                <div class="online-copy">
                  <div class="online-label">Admins</div>
                  <div class="online-count" data-online-count="admins">0 active</div>
                </div>
                <div class="online-avatars" data-online-avatars="admins"></div>
              </div>
              <div class="online-empty" data-online-empty hidden>No staff online</div>
            </section>
          @endif
          <section
            class="notification-center"
            data-notification-center
            data-endpoint="{{ route('admin.notifications.recent') }}"
            data-read-all="{{ route('admin.notifications.read-all') }}"
            data-index="{{ route('admin.notifications.index') }}"
            aria-label="Notifications"
          >
            <button class="notification-trigger" type="button" data-notification-toggle aria-expanded="false" aria-label="Open notifications">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>
              <span class="notification-badge" data-notification-badge hidden>0</span>
            </button>
            <div class="notification-panel" data-notification-panel hidden>
              <div class="notification-panel-head">
                <div class="notification-panel-title">Notifications</div>
                <button class="notification-panel-action" type="button" data-notification-read-all>Mark all read</button>
              </div>
              <div class="notification-panel-list" data-notification-list>
                <div class="notification-empty-state">Loading notifications...</div>
              </div>
              <div class="notification-panel-foot">
                <button class="notification-view-all" type="button" data-notification-view-all>View all notifications</button>
              </div>
            </div>
          </section>
          <form class="logout-form" method="post" action="{{ route('logout') }}">
            @csrf
            <button class="logout-button">Logout</button>
          </form>
        </div>
      </div>
      <div class="frame-wrap">
        <iframe id="pane" name="pane" class="frame" src="{{ route('admin.reservations') }}" title="Admin panel"></iframe>
      </div>
    </main>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const pane = document.getElementById('pane');
      const title = document.getElementById('paneTitle');
      const sectionStorageKey = 'admin:sidebarSections';
      const minPaneHeight = () => Math.max(window.innerHeight - 72, 720);
      const onlineSeed = @json($onlineUsers);
      const presencePingUrl = @json(route('presence.ping'));
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
      const notificationRoot = document.querySelector('[data-notification-center]');
      const notificationIndexUrl = notificationRoot?.dataset?.index || '';
      let paneResizeObserver = null;
      let paneMutationObserver = null;
      const map = {
        'Reservations':'Reservations',
        'Staff Booking':'Staff Booking',
        'Staff Bookings':'Staff Bookings',
        'Invoices':'Invoices',
        'Timeslots':'Timeslots',
        'Calendar':'Calendar',
        'Schedule':'Schedule',
        'Clients':'Clients',
        'Menu':'Menu',
        'Reports':'Reports',
        'Financial Overview':'Financial Overview',
        'Inventory':'Inventory',
        'Order Breakdown':'Order Breakdown',
        'Orders Breakdown':'Orders Breakdown',
        'Team':'Team',
        'Settings':'Settings',
        'Feedback Center':'Feedback Center',
        'Trash':'Trash',
      };

      const renderOnlineUsers = (payload) => {
        const root = document.querySelector('[data-online-users]');
        if (!root || !payload) return;

        const staff = Array.isArray(payload.staff) ? payload.staff : [];
        const admins = Array.isArray(payload.admins) ? payload.admins : [];
        const total = Number(payload.total || staff.length + admins.length);
        const maxVisible = 6;
        const staffVisible = staff.slice(0, maxVisible);
        const adminSlots = Math.max(0, maxVisible - staffVisible.length);
        const adminsVisible = admins.slice(0, adminSlots);
        const visibleCount = staffVisible.length + adminsVisible.length;
        const overflow = Math.max(0, total - visibleCount);
        const staffGroup = root.querySelector('[data-online-group="staff"]');
        const adminsGroup = root.querySelector('[data-online-group="admins"]');
        const divider = root.querySelector('[data-online-divider]');
        const empty = root.querySelector('[data-online-empty]');

        const makeAvatar = (user) => {
          const avatar = document.createElement('span');
          avatar.className = 'online-avatar';
          avatar.tabIndex = 0;
          avatar.setAttribute('aria-label', `${user.name}, ${user.role}, ${user.last_seen_label}`);
          avatar.dataset.tooltip = `${user.name} | ${user.role} | ${user.last_seen_label}`;
          avatar.title = `${user.name} - ${user.role} - ${user.last_seen_label}`;

          if (user.photo_url) {
            const img = document.createElement('img');
            img.src = user.photo_url;
            img.alt = user.name;
            avatar.appendChild(img);
          } else {
            avatar.textContent = user.initials || 'U';
          }

          return avatar;
        };

        const renderGroup = (name, users, count) => {
          const group = root.querySelector(`[data-online-group="${name}"]`);
          const avatars = root.querySelector(`[data-online-avatars="${name}"]`);
          const counter = root.querySelector(`[data-online-count="${name}"]`);
          if (!group || !avatars || !counter) return;

          avatars.innerHTML = '';
          users.forEach(user => avatars.appendChild(makeAvatar(user)));
          counter.textContent = `${count} active`;
          group.hidden = count === 0;
        };

        renderGroup('staff', staffVisible, staff.length);
        renderGroup('admins', adminsVisible, admins.length);

        if (overflow > 0) {
          const targetName = staffVisible.length > 0 ? 'staff' : 'admins';
          const avatars = root.querySelector(`[data-online-avatars="${targetName}"]`);
          if (avatars) {
            const more = document.createElement('span');
            more.className = 'online-avatar more';
            more.textContent = `+${overflow}`;
            more.title = `${overflow} more online`;
            more.setAttribute('aria-label', `${overflow} more online`);
            avatars.appendChild(more);
          }
        }

        const showStaff = staff.length > 0;
        const showAdmins = admins.length > 0 && adminsVisible.length > 0;
        if (staffGroup) staffGroup.hidden = !showStaff;
        if (adminsGroup) adminsGroup.hidden = !showAdmins;
        if (divider) divider.hidden = !(showStaff && showAdmins);
        if (empty) empty.hidden = total > 0;
      };

      const refreshOnlineUsers = async () => {
        const root = document.querySelector('[data-online-users]');
        const endpoint = root?.dataset?.endpoint;
        if (!endpoint) return;

        try {
          const response = await fetch(endpoint, {
            headers: {'Accept': 'application/json'},
            credentials: 'same-origin',
          });
          if (response.ok) {
            renderOnlineUsers(await response.json());
          }
        } catch (e) {}
      };

      const pingPresence = async () => {
        if (!presencePingUrl) return;
        try {
          await fetch(presencePingUrl, {
            headers: {'Accept': 'application/json'},
            credentials: 'same-origin',
            keepalive: true,
          });
        } catch (e) {}
      };

      const notificationInitial = (type) => {
        const value = String(type || 'bell').replace('_', ' ');
        return value.charAt(0) || 'N';
      };

      const setNotificationBadge = (count) => {
        const badge = notificationRoot?.querySelector('[data-notification-badge]');
        if (!badge) return;
        const unread = Number(count || 0);
        badge.hidden = unread <= 0;
        badge.textContent = unread > 99 ? '99+' : String(unread);
      };

      const renderNotifications = (payload) => {
        if (!notificationRoot || !payload) return;
        const list = notificationRoot.querySelector('[data-notification-list]');
        const readAll = notificationRoot.querySelector('[data-notification-read-all]');
        const notifications = Array.isArray(payload.notifications) ? payload.notifications : [];
        setNotificationBadge(payload.unread || 0);
        if (readAll) readAll.hidden = Number(payload.unread || 0) <= 0;
        if (!list) return;

        if (notifications.length === 0) {
          list.innerHTML = '<div class="notification-empty-state">No notifications yet.</div>';
          return;
        }

        list.innerHTML = '';
        notifications.forEach(notification => {
          const item = document.createElement('button');
          item.type = 'button';
          item.className = `notification-panel-item${notification.read_at ? '' : ' unread'}`;
          item.dataset.url = notification.url || notificationIndexUrl;
          item.dataset.readUrl = notification.read_url || '';

          const icon = document.createElement('span');
          icon.className = 'notification-panel-icon';
          icon.textContent = notificationInitial(notification.type);

          const copy = document.createElement('span');
          copy.className = 'notification-panel-copy';

          const titleEl = document.createElement('span');
          titleEl.className = 'notification-panel-name';
          titleEl.textContent = notification.title || 'Notification';

          const messageEl = document.createElement('span');
          messageEl.className = 'notification-panel-message';
          messageEl.textContent = notification.message || '';

          const metaEl = document.createElement('span');
          metaEl.className = 'notification-panel-meta';
          metaEl.textContent = notification.created_label || '';

          copy.append(titleEl, messageEl, metaEl);
          item.append(icon, copy);
          list.appendChild(item);
        });
      };

      const refreshNotifications = async () => {
        if (!notificationRoot) return;
        const endpoint = notificationRoot.dataset.endpoint;
        if (!endpoint) return;

        try {
          const response = await fetch(endpoint, {
            headers: {'Accept': 'application/json'},
            credentials: 'same-origin',
          });
          if (response.ok) {
            renderNotifications(await response.json());
          }
        } catch (e) {}
      };

      const postNotificationAction = async (url) => {
        if (!url) return;
        try {
          await fetch(url, {
            method: 'POST',
            headers: {
              'Accept': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
            },
            credentials: 'same-origin',
          });
        } catch (e) {}
      };

      const closeNotificationPanel = () => {
        const panel = notificationRoot?.querySelector('[data-notification-panel]');
        const toggle = notificationRoot?.querySelector('[data-notification-toggle]');
        if (panel) panel.hidden = true;
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
      };

      const openNotificationsIndex = () => {
        if (!notificationIndexUrl || !pane) return;
        pane.src = notificationIndexUrl;
        title.textContent = 'Notifications';
        try { localStorage.setItem('admin:lastPaneUrl', notificationIndexUrl); } catch(e) {}
        closeNotificationPanel();
      };

      const readSectionState = () => {
        try {
          return JSON.parse(localStorage.getItem(sectionStorageKey) || '{}') || {};
        } catch(e) {
          return {};
        }
      };

      const writeSectionState = () => {
        try {
          const next = {};
          document.querySelectorAll('.nav-section').forEach(section => {
            const key = section.getAttribute('data-section');
            if (key) next[key] = !section.classList.contains('is-collapsed');
          });
          localStorage.setItem(sectionStorageKey, JSON.stringify(next));
        } catch(e) {}
      };

      const setSectionOpen = (section, open, persist = true) => {
        if (!section) return;
        section.classList.toggle('is-open', open);
        section.classList.toggle('is-collapsed', !open);
        section.querySelector('.section-toggle')?.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (persist) writeSectionState();
      };

      const applyStoredSectionState = () => {
        const stored = readSectionState();
        document.querySelectorAll('.nav-section').forEach(section => {
          const key = section.getAttribute('data-section');
          const open = key && Object.prototype.hasOwnProperty.call(stored, key) ? stored[key] !== false : true;
          setSectionOpen(section, open, false);
        });
      };

      const setActiveByUrl = (url) => {
        if (!url) return;
        if (notificationIndexUrl && url.indexOf(notificationIndexUrl) === 0) {
          document.querySelectorAll('.nav-item').forEach(a => a.classList.remove('active'));
          document.querySelectorAll('.section-toggle').forEach(btn => btn.classList.remove('active-parent'));
          title.textContent = 'Notifications';
          try { localStorage.setItem('admin:lastPaneUrl', url); } catch(e) {}
          return;
        }
        // store last pane url so reloads return to the same section
        try { localStorage.setItem('admin:lastPaneUrl', url); } catch(e) {}
        // highlight matching nav item and set title
        let matched = null;
        let matchedLength = -1;
        document.querySelectorAll('.nav-item').forEach(a => {
          const navUrl = a.getAttribute('data-url') || '';
          const isMatch = navUrl !== '' && url.indexOf(navUrl) === 0;
          if (isMatch && navUrl.length > matchedLength) {
            matched = a;
            matchedLength = navUrl.length;
          }
        });
        document.querySelectorAll('.nav-item').forEach(a => {
          a.classList.toggle('active', a === matched);
        });
        document.querySelectorAll('.section-toggle').forEach(btn => {
          btn.classList.remove('active-parent');
        });
        const activeSection = matched?.closest('.nav-section');
        if (activeSection) {
          setSectionOpen(activeSection, true);
          activeSection.querySelector('.section-toggle')?.classList.add('active-parent');
        }
        const label = matched?.querySelector('.nav-label')?.textContent?.trim() || '';
        title.textContent = map[label] || label || 'Dashboard';
      };

      const syncPaneHeight = () => {
        if (!pane) return;
        let nextHeight = minPaneHeight();
        try {
          const doc = pane.contentDocument;
          if (doc) {
            const body = doc.body;
            const html = doc.documentElement;
            nextHeight = Math.max(
              minPaneHeight(),
              body?.scrollHeight || 0,
              body?.offsetHeight || 0,
              html?.scrollHeight || 0,
              html?.offsetHeight || 0
            );
          }
        } catch (e) {}

        pane.style.height = `${nextHeight + 8}px`;
      };

      const disconnectPaneObservers = () => {
        paneResizeObserver?.disconnect();
        paneMutationObserver?.disconnect();
        paneResizeObserver = null;
        paneMutationObserver = null;
      };

      const attachPaneObservers = () => {
        disconnectPaneObservers();
        try {
          const doc = pane.contentDocument;
          if (!doc) return;
          const body = doc.body;
          const html = doc.documentElement;
          if ('ResizeObserver' in window) {
            paneResizeObserver = new ResizeObserver(() => syncPaneHeight());
            if (body) paneResizeObserver.observe(body);
            if (html && html !== body) paneResizeObserver.observe(html);
          }
          paneMutationObserver = new MutationObserver(() => syncPaneHeight());
          if (body) {
            paneMutationObserver.observe(body, {
              childList: true,
              subtree: true,
              attributes: true,
              characterData: true,
            });
          }
        } catch (e) {}
      };

      applyStoredSectionState();
      renderOnlineUsers(onlineSeed);
      refreshOnlineUsers();
      refreshNotifications();
      setInterval(refreshOnlineUsers, 30000);
      setInterval(refreshNotifications, 30000);
      setInterval(pingPresence, 60000);
      syncPaneHeight();
      window.addEventListener('resize', syncPaneHeight);

      document.querySelectorAll('.section-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
          const section = btn.closest('.nav-section');
          if (!section) return;
          const hasActiveChild = Boolean(section.querySelector('.nav-item.active'));
          const shouldOpen = section.classList.contains('is-collapsed') || hasActiveChild;
          setSectionOpen(section, shouldOpen);
        });
      });

      notificationRoot?.querySelector('[data-notification-toggle]')?.addEventListener('click', () => {
        const panel = notificationRoot.querySelector('[data-notification-panel]');
        const toggle = notificationRoot.querySelector('[data-notification-toggle]');
        if (!panel || !toggle) return;
        const isOpen = !panel.hidden;
        panel.hidden = isOpen;
        toggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
        if (isOpen === false) refreshNotifications();
      });

      notificationRoot?.querySelector('[data-notification-view-all]')?.addEventListener('click', openNotificationsIndex);

      notificationRoot?.querySelector('[data-notification-read-all]')?.addEventListener('click', async () => {
        await postNotificationAction(notificationRoot.dataset.readAll || '');
        await refreshNotifications();
      });

      notificationRoot?.querySelector('[data-notification-list]')?.addEventListener('click', async (event) => {
        const item = event.target.closest('.notification-panel-item');
        if (!item) return;
        const readUrl = item.dataset.readUrl || '';
        const targetUrl = item.dataset.url || notificationIndexUrl;
        await postNotificationAction(readUrl);
        if (targetUrl) {
          pane.src = targetUrl;
          setActiveByUrl(targetUrl);
        }
        closeNotificationPanel();
        refreshNotifications();
      });

      document.addEventListener('click', (event) => {
        if (!notificationRoot || notificationRoot.contains(event.target)) return;
        closeNotificationPanel();
      });

      // Restore last visited section on dashboard load
      try {
        const remembered = localStorage.getItem('admin:lastPaneUrl');
        if (remembered) {
          pane.src = remembered;
          setActiveByUrl(remembered);
        }
      } catch(e) {}

      // Track nav clicks
      document.querySelectorAll('.nav-item').forEach(a => {
        a.addEventListener('click', () => {
          const url = a.getAttribute('data-url');
          if (!url) return;
          setActiveByUrl(url);
          // Allow native navigation into iframe via target="pane".
          if (a.getAttribute('target') !== 'pane') {
            pane.src = url;
          }
        });
      });

      // When iframe navigates (including after POST redirects), update active state and remember URL
      pane.addEventListener('load', () => {
        try {
          const loc = pane.contentWindow?.location;
          if (loc && loc.href) setActiveByUrl(loc.href);
        } catch(e) {}
        attachPaneObservers();
        syncPaneHeight();
        window.requestAnimationFrame(syncPaneHeight);
        window.setTimeout(syncPaneHeight, 120);
        window.setTimeout(syncPaneHeight, 450);
      });
    });
  </script>
</body>
</html>
