@php
  $u = $u ?? auth()->user();
  $canViewOnlineUsers = $u && $u->hasRole(['owner', 'admin']);
@endphp
<div class="content-head">
  <button type="button" class="sidebar-toggle" data-sidebar-toggle aria-label="Toggle menu" aria-expanded="false">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18" stroke-linecap="round"/></svg>
  </button>
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
