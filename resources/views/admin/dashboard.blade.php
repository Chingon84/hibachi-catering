<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin – Dashboard</title>
  <style>
    :root{--bg:#f7f7fb;--text:#111827;--muted:#6b7280;--card:#fff;--border:#e5e7eb;--brand:#b21e27;--brand-hover:#9a1a22}
    *{box-sizing:border-box}
    body{margin:0;background:var(--bg);color:var(--text);font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif}
    .shell{display:grid;grid-template-columns:280px 1fr;min-height:100vh}
    @media (max-width: 900px){.shell{grid-template-columns:1fr}}
    .sidebar{background:#fff;border-right:1px solid var(--border)}
    .side-head{display:flex;align-items:center;gap:10px;padding:16px;border-bottom:1px solid var(--border)}
    .side-head img{height:30px;width:auto}
    .side-menu{padding:10px}
    .nav-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:12px;border:1px solid var(--border);background:#fff;color:#111;text-decoration:none;margin-bottom:10px;transition:all .15s}
    .nav-item:hover{border-color:#d1d5db;box-shadow:0 4px 12px rgba(0,0,0,.05)}
    .nav-item.active{border-color:var(--brand);box-shadow:0 6px 16px rgba(178,30,39,.15)}
    .nav-label{font-weight:700}
    .nav-sub{color:var(--muted);font-size:12px}
    .icon{width:22px;height:22px;color:var(--brand)}
    .content{background:var(--bg)}
    .content-head{display:flex;align-items:center;justify-content:space-between;padding:16px}
    .title{font-size:22px;margin:0}
    .frame-wrap{padding:0 16px 16px}
    .frame{width:100%;height:calc(100vh - 72px);border:1px solid var(--border);border-radius:14px;background:#fff}
  </style>
</head>
<body>
  <div class="shell">
    <aside class="sidebar">
      <div class="side-head">
        <img src="/assets/brand/logo.png" alt="Hibachi Catering" onerror="this.style.display='none'">
        <div style="font-weight:700">Dashboard</div>
      </div>
      <nav class="side-menu">
        @php $u = auth()->user(); @endphp
        @if($u && $u->hasPermission('reservations.view'))
        <a href="{{ route('admin.reservations') }}" target="pane" class="nav-item active" data-url="{{ route('admin.reservations') }}">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M6 2h12a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2zm0 4v14h12V6H6zm2 3h8v2H8V9zm0 4h8v2H8v-2z"/></svg>
          <div>
            <div class="nav-label">Reservations</div>
            <div class="nav-sub">Bookings and invoices</div>
          </div>
        </a>
        @endif
        @if($u && $u->hasPermission('staff.view'))
        <a href="{{ url('/admin/staff-bookings') }}" target="pane" class="nav-item" data-url="{{ url('/admin/staff-bookings') }}">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5zm0 2c-4.4 0-8 2.2-8 5v1h16v-1c0-2.8-3.6-5-8-5z"/></svg>
          <div>
            <div class="nav-label">Staff Bookings</div>
            <div class="nav-sub">Internal scheduling</div>
          </div>
        </a>
        @endif
        @if($u && $u->hasPermission('calendar.view'))
        <a href="{{ url('/admin/calendar') }}" target="pane" class="nav-item" data-url="{{ url('/admin/calendar') }}">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M7 2h2v2h6V2h2v2h2a2 2 0 0 1 2 2v3H3V6a2 2 0 0 1 2-2h2V2zm15 8v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V10h20z"/></svg>
          <div>
            <div class="nav-label">Calendar</div>
            <div class="nav-sub">View availability</div>
          </div>
        </a>
        @endif
        @if($u && $u->hasPermission('timeslots.view'))
        <a href="{{ route('admin.timeslots') }}" target="pane" class="nav-item" data-url="{{ route('admin.timeslots') }}">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M3 5a2 2 0 0 1 2-2h2v2h6V3h2a2 2 0 0 1 2 2v2H3V5zm0 4h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9zm4 3h6v2H7v-2zm0 4h10v2H7v-2z"/></svg>
          <div>
            <div class="nav-label">Timeslots</div>
            <div class="nav-sub">Manage availability</div>
          </div>
        </a>
        @endif
        @if($u && $u->hasPermission('clients.view'))
        <a href="{{ url('/admin/clients') }}" target="pane" class="nav-item" data-url="{{ url('/admin/clients') }}">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5zm0 2c-5 0-9 2.5-9 5v1h18v-1c0-2.5-4-5-9-5z"/></svg>
          <div>
            <div class="nav-label">Clients</div>
            <div class="nav-sub">Directory of customers</div>
          </div>
        </a>
        @endif
        @if($u && $u->hasPermission('menu.view'))
        <a href="{{ route('admin.menu') }}" target="pane" class="nav-item" data-url="{{ route('admin.menu') }}">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M4 6h16v2H4zm0 5h16v2H4zm0 5h10v2H4z"/></svg>
          <div>
            <div class="nav-label">Menu</div>
            <div class="nav-sub">Edit menu & prices</div>
          </div>
        </a>
        @endif
        @if($u && $u->hasPermission('reports.view'))
        <a href="{{ url('/admin/reports') }}" target="pane" class="nav-item" data-url="{{ url('/admin/reports') }}">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M3 3h18v2H3zm2 4h14v2H5zm-2 4h18v2H3zm2 4h10v2H5z"/></svg>
          <div>
            <div class="nav-label">Reports</div>
            <div class="nav-sub">KPIs and exports</div>
          </div>
        </a>
        @endif
        @if($u && $u->hasPermission('orders.view'))
        <a href="{{ route('admin.orders.breakdown') }}" target="pane" class="nav-item" data-url="{{ route('admin.orders.breakdown') }}">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M3 4h8v8H3zm0 10h8v6H3zm10-10h8v5h-8zm0 7h8v9h-8z"/></svg>
          <div>
            <div class="nav-label">Orders Breakdown</div>
            <div class="nav-sub">Sales overview</div>
          </div>
        </a>
        @endif
        @if($u && $u->hasPermission('team.view'))
        <a href="{{ url('/admin/team') }}" target="pane" class="nav-item" data-url="{{ url('/admin/team') }}">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11a4 4 0 1 0-4-4 4 4 0 0 0 4 4zm-8 0a3 3 0 1 0-3-3 3 3 0 0 0 3 3zm8 2c-3.3 0-6 1.7-6 3.8V19h12v-2.2C22 14.7 19.3 13 16 13zM3 16.8V19h6v-2.2C9 15.6 7.3 15 5.5 15S2 15.6 2 16.8z"/></svg>
          <div>
            <div class="nav-label">Team Management</div>
            <div class="nav-sub">Users and roles</div>
          </div>
        </a>
        @endif
        @if($u && $u->hasPermission('settings.view'))
        <a href="{{ url('/admin/settings') }}" target="pane" class="nav-item" data-url="{{ url('/admin/settings') }}">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19.4 12.9a7.7 7.7 0 0 0 .1-1 7.7 7.7 0 0 0-.1-1l2.1-1.6a.5.5 0 0 0 .1-.6l-2-3.5a.5.5 0 0 0-.6-.2l-2.4 1a7.5 7.5 0 0 0-1.7-1l-.3-2.6A.5.5 0 0 0 14 1h-4a.5.5 0 0 0-.5.4L9.2 4a7.5 7.5 0 0 0-1.7 1l-2.4-1a.5.5 0 0 0-.6.2l-2 3.5a.5.5 0 0 0 .1.6L4.7 11a7.7 7.7 0 0 0-.1 1 7.7 7.7 0 0 0 .1 1L2.6 14.6a.5.5 0 0 0-.1.6l2 3.5a.5.5 0 0 0 .6.2l2.4-1a7.5 7.5 0 0 0 1.7 1l.3 2.6a.5.5 0 0 0 .5.4h4a.5.5 0 0 0 .5-.4l.3-2.6a7.5 7.5 0 0 0 1.7-1l2.4 1a.5.5 0 0 0 .6-.2l2-3.5a.5.5 0 0 0-.1-.6zm-7.4 2.1a3 3 0 1 1 3-3 3 3 0 0 1-3 3z"/></svg>
          <div>
            <div class="nav-label">Settings</div>
            <div class="nav-sub">App configuration</div>
          </div>
        </a>
        @endif
        @if($u && $u->hasPermission('complains.view'))
        <a href="{{ url('/admin/complains') }}" target="pane" class="nav-item" data-url="{{ url('/admin/complains') }}">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm1 15h-2v-2h2zm0-4h-2V7h2z"/></svg>
          <div>
            <div class="nav-label">Complains</div>
            <div class="nav-sub">Customer issues</div>
          </div>
        </a>
        @endif
        @if($u && $u->hasPermission('trash.view'))
        <a href="{{ route('admin.trash') }}" target="pane" class="nav-item" data-url="{{ route('admin.trash') }}">
          <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M9 3h6l1 2h5v2H3V5h5l1-2zm1 6h2v10h-2V9zm4 0h2v10h-2V9zM7 9h2v10H7V9z"/></svg>
          <div>
            <div class="nav-label">Trash</div>
            <div class="nav-sub">Recently deleted</div>
          </div>
        </a>
        @endif
      </nav>
    </aside>
    <main class="content">
      <div class="content-head">
        <h1 class="title" id="paneTitle">Reservations</h1>
        <form method="post" action="{{ route('logout') }}" style="margin:0">
          @csrf
          <button style="padding:8px 12px;border-radius:10px;border:1px solid var(--border);background:#fff;cursor:pointer">Logout</button>
        </form>
      </div>
      <div class="frame-wrap">
        <iframe id="pane" name="pane" class="frame" src="{{ route('admin.reservations') }}" title="Admin Content"></iframe>
      </div>
    </main>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const pane = document.getElementById('pane');
      const title = document.getElementById('paneTitle');
      const map = {
        'Reservations':'Reservations',
        'Staff Bookings':'Staff Bookings',
        'Timeslots':'Timeslots',
        'Calendar':'Calendar',
        'Clients':'Clients',
        'Menu':'Menu',
        'Reports':'Reports',
        'Orders Breakdown':'Orders Breakdown',
        'Team Management':'Team Management',
        'Settings':'Settings',
        'Complains':'Complains',
        'Trash':'Trash',
      };

      const setActiveByUrl = (url) => {
        if (!url) return;
        // store last pane url so reloads return to the same section
        try { localStorage.setItem('admin:lastPaneUrl', url); } catch(e) {}
        // highlight matching nav item and set title
        let matched = null;
        document.querySelectorAll('.nav-item').forEach(a => {
          const navUrl = a.getAttribute('data-url') || '';
          const isMatch = url.indexOf(navUrl) === 0; // starts with
          a.classList.toggle('active', isMatch);
          if (isMatch) matched = a;
        });
        const label = matched?.querySelector('.nav-label')?.textContent?.trim() || '';
        title.textContent = map[label] || label || 'Dashboard';
      };

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
      });
    });
  </script>
</body>
</html>
