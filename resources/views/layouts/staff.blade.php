<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Staff Portal') - Hibachi Catering</title>
  <style>
    :root{--bg:#f6f7f9;--panel:#fff;--line:#e5e7eb;--text:#111827;--muted:#64748b;--brand:#b91c1c;--soft:#f8fafc}
    *{box-sizing:border-box}
    body{margin:0;background:var(--bg);color:var(--text);font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
    a{color:inherit;text-decoration:none}
    .staff-topbar{height:64px;background:#fff;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;padding:0 24px;position:sticky;top:0;z-index:10}
    .brand{display:flex;align-items:center;gap:12px;font-weight:900;letter-spacing:.02em}
    .brand img{width:34px;height:34px;object-fit:contain}
    .brand small{display:block;font-size:12px;color:var(--muted);font-weight:600;letter-spacing:0}
    .staff-user{display:flex;align-items:center;gap:14px;color:#334155;font-size:14px}
    .logout-btn{border:1px solid var(--line);background:#fff;border-radius:9px;padding:8px 12px;font-weight:800;cursor:pointer;color:#111827}
    .logout-btn:hover{background:#f8fafc}
    .staff-shell{max-width:1180px;margin:0 auto;padding:26px 18px 46px}
    .page-head{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;margin-bottom:20px}
    .page-head h1{margin:0;font-size:28px;line-height:1.1}
    .page-head p{margin:6px 0 0;color:var(--muted);font-size:14px}
    .section-title{font-size:13px;text-transform:uppercase;letter-spacing:.08em;color:#475569;font-weight:900;margin:24px 0 10px}
    .event-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:14px}
    .event-card,.detail-card{background:var(--panel);border:1px solid var(--line);border-radius:12px;box-shadow:0 1px 2px rgba(15,23,42,.04)}
    .event-card{padding:16px}
    .event-card.past{background:#f8fafc;border-color:#e2e8f0;color:#64748b}
    .event-card.past h2,.event-card.past .info-row dd{color:#475569}
    .event-card h2{margin:0;font-size:18px}
    .event-code{font-size:12px;color:var(--muted);margin-top:3px}
    .badge-row{display:flex;flex-wrap:wrap;gap:8px;margin:13px 0}
    .badge{background:#f1f5f9;border:1px solid #e2e8f0;border-radius:999px;padding:5px 9px;font-size:12px;font-weight:800;color:#334155}
    .badge.today{background:#dcfce7;border-color:#bbf7d0;color:#15803d}
    .badge.upcoming{background:#ecfdf5;border-color:#bbf7d0;color:#15803d}
    .badge.past{background:#f1f5f9;border-color:#e2e8f0;color:#64748b}
    .badge.invoice{background:#fff;border-color:#dbe2ea;color:#334155}
    .badge.invoice.paid,.badge.balance.paid{background:#dcfce7;border-color:#bbf7d0;color:#166534}
    .badge.invoice.unpaid,.badge.balance.due{background:#fee2e2;border-color:#fecaca;color:#991b1b}
    .badge.invoice.partial{background:#ffedd5;border-color:#fed7aa;color:#9a3412}
    .badge.tip{background:#fef9c3;border-color:#fde68a;color:#854d0e}
    .badge.confirmation.not-viewed{background:#f1f5f9;border-color:#e2e8f0;color:#64748b}
    .badge.confirmation.viewed{background:#dbeafe;border-color:#bfdbfe;color:#1d4ed8}
    .badge.confirmation.confirmed{background:#dcfce7;border-color:#bbf7d0;color:#15803d}
    .event-date{font-size:14px;font-weight:900;color:#15803d;margin-top:12px}
    .event-date.past{font-weight:700;color:#94a3b8}
    .info-list{display:grid;gap:8px;margin:0}
    .info-row{display:grid;grid-template-columns:116px minmax(0,1fr);gap:10px;font-size:14px}
    .info-row dt{color:var(--muted);font-weight:800}
    .info-row dd{margin:0;color:#111827;font-weight:650;min-width:0;overflow-wrap:anywhere}
    .mini-title{font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);font-weight:900;margin:15px 0 7px}
    .menu-list{margin:0;padding:0;list-style:none;display:grid;gap:6px}
    .menu-list li{display:flex;justify-content:space-between;gap:12px;border-top:1px solid #f1f5f9;padding-top:6px;font-size:14px}
    .menu-list span:first-child{font-weight:700}
    .menu-list span:last-child{color:#475569;font-weight:900;white-space:nowrap}
    .menu-table{margin-top:15px;border:1px solid #edf2f7;border-radius:10px;background:#fff;overflow:hidden}
    .menu-table-head,.menu-table-row{display:grid;grid-template-columns:minmax(0,1fr) 58px;align-items:center;gap:12px}
    .menu-table-head{background:#f8fafc;border-bottom:1px solid #e8edf4;color:var(--muted);font-size:12px;font-weight:900;letter-spacing:.08em;text-transform:uppercase}
    .menu-table-head span,.menu-table-row span{padding:8px 10px}
    .menu-table-head span:last-child,.menu-table-row span:last-child{text-align:right}
    .menu-table-row{font-size:14px;border-top:1px solid #f1f5f9}
    .menu-table-row:first-of-type{border-top:0}
    .menu-table-row span:first-child{font-weight:750;color:#111827;min-width:0;overflow-wrap:anywhere}
    .menu-table-row span:last-child{color:#243b53;font-weight:900;font-variant-numeric:tabular-nums}
    .menu-item-meta{display:block;margin-top:3px;color:#64748b;font-size:12px;font-weight:700;line-height:1.35}
    .card-actions{display:flex;flex-wrap:wrap;gap:8px;margin-top:14px}
    .btn-primary{display:inline-flex;align-items:center;justify-content:center;border-radius:9px;background:#111827;color:#fff;font-weight:900;font-size:13px;padding:9px 12px}
    .btn-secondary{display:inline-flex;align-items:center;justify-content:center;border-radius:9px;background:#fff;border:1px solid var(--line);font-weight:900;font-size:13px;padding:9px 12px;color:#111827}
    .btn-confirm{display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:9px;background:#15803d;color:#fff;font-weight:900;font-size:13px;padding:9px 12px;cursor:pointer}
    .btn-confirm:hover{background:#166534}
    .empty{background:#fff;border:1px dashed #cbd5e1;border-radius:12px;padding:22px;color:#64748b;font-weight:700}
    .alert-success{background:#ecfdf5;border:1px solid #bbf7d0;color:#166534;border-radius:10px;padding:10px 12px;font-size:14px;font-weight:800;margin-bottom:16px}
    .detail-card{padding:18px;margin-bottom:14px}
    .detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px 22px}
    .detail-card h2{margin:0 0 14px;font-size:18px}
    .notes-box{background:#f8fafc;border:1px solid #edf2f7;border-radius:10px;padding:12px;white-space:pre-wrap;color:#334155;font-size:14px}
    @media (max-width:720px){
      .staff-topbar{height:auto;padding:12px 14px;align-items:flex-start;gap:12px}
      .staff-user{align-items:flex-end;flex-direction:column;gap:8px}
      .staff-shell{padding:20px 12px 36px}
      .page-head{display:block}
      .event-grid,.detail-grid{grid-template-columns:1fr}
      .info-row{grid-template-columns:98px minmax(0,1fr)}
    }
  </style>
</head>
<body>
  <header class="staff-topbar">
    <a class="brand" href="{{ route('staff.dashboard') }}">
      <img src="/assets/brand/logo.png" alt="Hibachi Catering" onerror="this.style.display='none'">
      <span>Hibachi Catering<small>Staff Portal</small></span>
    </a>
    <div class="staff-user">
      <strong>{{ auth()->user()?->name }}</strong>
      <form method="post" action="{{ route('logout') }}">
        @csrf
        <button class="logout-btn" type="submit">Logout</button>
      </form>
    </div>
  </header>
  <main class="staff-shell">
    @if(session('ok'))
      <div class="alert-success">{{ session('ok') }}</div>
    @endif
    @yield('content')
  </main>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const presencePingUrl = @json(route('presence.ping'));
      const pingPresence = async () => {
        try {
          await fetch(presencePingUrl, {
            headers: {'Accept': 'application/json'},
            credentials: 'same-origin',
            keepalive: true,
          });
        } catch (e) {}
      };

      setInterval(pingPresence, 60000);
    });
  </script>
</body>
</html>
