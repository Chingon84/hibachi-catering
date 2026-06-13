@php $u = $u ?? auth()->user(); @endphp
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
