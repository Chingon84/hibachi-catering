<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Admin – Dashboard</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
  @php
    $u = auth()->user();
    $canViewOnlineUsers = $u && $u->hasRole(['owner', 'admin']);
  @endphp
  <div class="shell">
    @include('admin.partials.sidebar')
    <main class="content">
      @include('admin.partials.topbar')
      <div class="frame-wrap">
        <iframe id="pane" name="pane" class="frame" src="{{ route('admin.reservations') }}" title="Admin panel"></iframe>
      </div>
    </main>
    <div class="sidebar-scrim" data-sidebar-scrim></div>
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

  <script>
    // Mobile sidebar off-canvas drawer
    document.addEventListener('DOMContentLoaded', () => {
      const shell = document.querySelector('.shell');
      const toggle = document.querySelector('[data-sidebar-toggle]');
      const scrim = document.querySelector('[data-sidebar-scrim]');
      if (!shell) return;
      const setOpen = (open) => {
        shell.classList.toggle('sidebar-open', open);
        toggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
      };
      toggle?.addEventListener('click', () => setOpen(!shell.classList.contains('sidebar-open')));
      scrim?.addEventListener('click', () => setOpen(false));
      shell.querySelectorAll('.nav-item').forEach(a => a.addEventListener('click', () => setOpen(false)));
      window.addEventListener('keydown', (e) => { if (e.key === 'Escape') setOpen(false); });
    });
  </script>
</body>
</html>
