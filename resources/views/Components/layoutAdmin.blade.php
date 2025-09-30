<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>OLIN - Admin Page</title>
  <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
  <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

  <!-- Early script: set initial sidebar state from localStorage before rendering to avoid flicker -->
  <script>
    (function () {
      try {
        var state = localStorage.getItem('sidebarState') || 'expanded';
        document.documentElement.setAttribute('data-sidebar', state);
      } catch (e) {
        // localStorage might be disabled — fall back to expanded
        document.documentElement.setAttribute('data-sidebar', 'expanded');
      }
    })();
  </script>
  @vite(['resources/css/admin_layouts.css',
         'resources/css/app.css',
         'resources/js/app.js'])

  @stack('page_assets')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">

</head>

<body class="bg-gray-100 min-h-screen flex flex-col">
<header
  class="bg-[#096F4D] shadow-sm h-16 fixed top-0 w-full z-20 flex items-center justify-between px-4 sm:px-6 md:px-8">
  <div class="flex items-center">
    <!-- Mobile toggle -->
    <button id="menu-toggle"
            class="md:hidden p-2 text-white hover:text-gray-200">
      <i class="fa-solid fa-bars fa-lg"></i>
    </button>
    <div class="text-2xl font-extrabold text-white">OLIN</div>
  </div>

  <div class="flex items-center space-x-4">
    <div class="relative">
      <button id="notificationBtn" class="relative p-2 text-white hover:text-gray-200 transition-colors duration-200 focus:outline-none">
        <i class="fa-solid fa-bell fa-lg"></i>
        <span id="notificationBadge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 items-center justify-center hidden"></span>
      </button>
      <!-- Dropdown -->
      <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-100 overflow-hidden z-50">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
          <div class="flex items-center gap-2 text-gray-900 font-semibold">
            <i class="fas fa-bell text-blue-600"></i>
            Notifications
          </div>
          <div class="flex items-center gap-3">
            <button id="markAllReadBtn" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Mark all as read</button>
            <a href="{{ route('notifications.all') }}" class="text-xs text-gray-600 hover:text-gray-800">View All</a>
          </div>
        </div>
        <div id="notificationsLoading" class="px-4 py-4 text-center">
          <i class="fas fa-spinner fa-spin text-gray-400"></i>
          <p class="text-sm text-gray-500 mt-2">Loading notifications...</p>
        </div>
        <div id="noNotifications" class="hidden px-4 py-6 text-center">
          <i class="fas fa-bell-slash text-gray-300 text-2xl mb-2"></i>
          <p class="text-sm text-gray-500">No notifications yet</p>
        </div>
        <div id="notificationsContent" class="hidden max-h-80 overflow-y-auto divide-y divide-gray-100"></div>
      </div>
    </div>

    <div class="relative">
      @if(Auth::user()->profile_image)
        @php
          $fn = basename(Auth::user()->profile_image);
          $streamUrl = route('media.profile', ['filename' => $fn]);
          $assetUrl = asset('storage/' . Auth::user()->profile_image);
        @endphp
        <a href="{{ route('admin.account') }}">
          <img class="w-10 h-10 rounded-full object-cover cursor-pointer"
               src="{{ $streamUrl }}" onerror="this.onerror=null;this.src='{{ $assetUrl }}';"
               alt="{{ Auth::user()->name }}'s profile image">
        </a>
      @else
        <a href="{{ route('admin.account') }}"
           class="profile-icon text-white font-semibold rounded-full w-10 h-10 flex items-center justify-center">
          {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
        </a>
      @endif
    </div>
  </div>
</header>

<div class="flex pt-16">
  <!-- Sidebar: note we removed the hardcoded "expanded" class from markup.
       initial state is controlled via data-sidebar attribute set early. -->
  <aside id="sidebar"
         class="modern-sidebar fixed top-16 left-0 h-[calc(100%-4rem)] transition-all duration-300 ease-in-out overflow-y-auto z-10">
    <nav class="sidebar-nav">
      <div class="nav-section">
        <!-- Title + toggle -->
        <div class="nav-section-title">
          <span>Main</span>
          <div class="toggle-btn" id="sidebarToggle" title="Toggle Sidebar" role="button" aria-pressed="false">
            <i id="toggleIcon" class="fa-solid fa-bars"></i>
          </div>
        </div>

        <a href="{{ route('admin.dashboard') }}"
           class="nav-item {{ Request::routeIs('admin.dashboard') ? 'nav-item-active' : '' }}">
          <div class="nav-icon">
            <i class="fas fa-home"></i>
          </div>
          <span class="nav-text">Dashboard</span>
        </a>

         <a href="{{ route('admin.user_management') }}"
            class="nav-item {{ (Request::routeIs('admin.user_management') || Request::routeIs('admin.users.*')) ? 'nav-item-active' : '' }}">
          <div class="nav-icon">
            <i class="fas fa-users-cog"></i>
          </div>
          <span class="nav-text">User Management</span>
        </a>

        <a href="{{ route('admin.course_management') }}"
           class="nav-item {{ (Request::routeIs('admin.course_management') || Request::routeIs('admin.courseManagement*') || Request::routeIs('admin.courses.*')) ? 'nav-item-active' : '' }}">
          <div class="nav-icon">
            <i class="fas fa-book"></i>
          </div>
          <span class="nav-text">Course Management</span>
        </a>
      </div>

      <div class="nav-section">
        <div class="nav-section-title"><span>Admin Tools</span></div>

        <a href="{{ route('admin.settings') }}"
           class="nav-item {{ (Request::routeIs('admin.settings') || Request::routeIs('admin.settings.*')) ? 'nav-item-active' : '' }}">
          <div class="nav-icon">
            <i class="fas fa-cogs"></i>
          </div>
          <span class="nav-text">Settings</span>
        </a>

        <a href="{{ route('admin.reports_logs') }}"
           class="nav-item {{ (Request::routeIs('admin.reports_logs') || Request::routeIs('admin.reports.*') || Request::routeIs('admin.logs.*')) ? 'nav-item-active' : '' }}">
          <div class="nav-icon">
            <i class="fas fa-clipboard-list"></i>
          </div>
          <span class="nav-text">Reports & Logs</span>
        </a>
      </div>

      <!-- <div class="nav-section">
        <div class="nav-section-title"><span>Support</span></div>

        <a href="#" class="nav-item">
          <div class="nav-icon">
            <i class="fas fa-question-circle"></i>
          </div>
          <span class="nav-text">Help Center</span>
        </a>
      </div> -->
    </nav>
  </aside>

  <!-- Main Content: margin-left is controlled by [data-sidebar] on <html> -->
  <main class="flex-1 p-6 overflow-auto transition-all duration-300" id="mainContent">
    @isset($slot)
      {!! $slot !!}
    @else
      @yield('content')
    @endisset
  </main>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById("sidebar");
    const sidebarToggle = document.getElementById("sidebarToggle");
    const toggleIcon = document.getElementById("toggleIcon");
    const mainContent = document.getElementById("mainContent");
    const menuToggle = document.getElementById('menu-toggle');

    // Helper to apply UI state (keeps icon and data attribute in sync)
    function applyState(state) {
      document.documentElement.setAttribute('data-sidebar', state);
      // update icon
      if (state === 'collapsed') {
        toggleIcon.classList.remove('fa-bars');
        toggleIcon.classList.add('fa-table-columns');
        sidebarToggle.setAttribute('aria-pressed', 'true');
      } else {
        toggleIcon.classList.remove('fa-table-columns');
        toggleIcon.classList.add('fa-bars');
        sidebarToggle.setAttribute('aria-pressed', 'false');
      }
    }

    // Initialize (there should already be a data-sidebar attribute from early script,
    // but we re-read it and ensure the icon is correct)
    const initial = document.documentElement.getAttribute('data-sidebar') || 'expanded';
    applyState(initial);

    // Mobile hamburger (unchanged)
    if (menuToggle) {
      menuToggle.addEventListener('click', function () {
        sidebar.classList.toggle('-translate-x-full');
      });
    }

    // Only toggle button controls expansion/collapse
    sidebarToggle.addEventListener('click', function (e) {
      e.stopPropagation();
      const current = document.documentElement.getAttribute('data-sidebar') === 'collapsed' ? 'collapsed' : 'expanded';
      const next = current === 'collapsed' ? 'expanded' : 'collapsed';
      applyState(next);
      // persist
      try {
        localStorage.setItem('sidebarState', next);
      } catch (err) {
        // ignore storage errors
      }
    });

    // When clicking nav links (which cause page reload/navigation), save the current state
    // so the next page will restore it. This prevents the reload-from-server default from
    // resetting the sidebar.
    document.querySelectorAll('.nav-item').forEach(item => {
      item.addEventListener('click', function () {
        try {
          const cur = document.documentElement.getAttribute('data-sidebar') === 'collapsed' ? 'collapsed' : 'expanded';
          localStorage.setItem('sidebarState', cur);
        } catch (err) { /* ignore */ }
        // Do not prevent navigation — we only persist state.
      });
    });

    // Notifications wiring
    const notifBtn = document.getElementById('notificationBtn');
    const notifDropdown = document.getElementById('notificationDropdown');
    const markAllBtn = document.getElementById('markAllReadBtn');

    async function toggleNotificationDropdown(event) {
      if (!notifDropdown) return;
      event?.stopPropagation();
      const isHidden = notifDropdown.classList.contains('hidden');
      if (isHidden) {
        notifDropdown.classList.remove('hidden');
        try {
          await loadNotifications();
          // Mark as read when user opens the dropdown
          await markAllNotificationsAsRead();
        } catch (e) { /* ignore */ }
      } else {
        notifDropdown.classList.add('hidden');
      }
    }

    if (notifBtn) {
      notifBtn.addEventListener('click', toggleNotificationDropdown);
    }
    if (markAllBtn) {
      markAllBtn.addEventListener('click', function(e){ e.stopPropagation(); markAllNotificationsAsRead(); });
    }
    document.addEventListener('click', function(event){
      if (!notifDropdown || !notifBtn) return;
      if (!notifDropdown.contains(event.target) && !notifBtn.contains(event.target)) {
        notifDropdown.classList.add('hidden');
      }
    });

    // Initial load and periodic refresh
    loadNotifications();
    setInterval(loadNotifications, 10000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) loadNotifications(); });
  });
</script>
<!-- Notifications helpers and modal -->
<script>
  let notificationsData = [];

  function loadNotifications() {
    const loadingEl = document.getElementById('notificationsLoading');
    const contentEl = document.getElementById('notificationsContent');
    const noNotificationsEl = document.getElementById('noNotifications');
    if (loadingEl) loadingEl.classList.remove('hidden');
    if (contentEl) contentEl.classList.add('hidden');
    if (noNotificationsEl) noNotificationsEl.classList.add('hidden');

    return fetch('/notifications', {
      method: 'GET',
      headers: { 'Accept': 'application/json' },
      credentials: 'same-origin'
    }).then(async (response) => {
      if (!response.ok) throw new Error('HTTP ' + response.status);
      return response.json();
    }).then(data => {
      notificationsData = Array.isArray(data.notifications) ? data.notifications : [];
      displayNotifications(notificationsData);
      updateNotificationBadge(Number(data.unread_count || 0));
    }).catch(err => {
      console.error('Failed to load notifications', err);
    }).finally(() => {
      if (loadingEl) loadingEl.classList.add('hidden');
    });
  }

  function displayNotifications(notifications) {
    const contentEl = document.getElementById('notificationsContent');
    const noNotificationsEl = document.getElementById('noNotifications');
    if (!contentEl || !noNotificationsEl) return;

    if (!notifications || notifications.length === 0) {
      contentEl.classList.add('hidden');
      noNotificationsEl.classList.remove('hidden');
      contentEl.innerHTML = '';
      return;
    }

    noNotificationsEl.classList.add('hidden');
    contentEl.classList.remove('hidden');
    contentEl.innerHTML = notifications.map(n => `
      <button class="w-full text-left px-4 py-3 hover:bg-gray-50 ${n.is_read ? '' : 'bg-blue-50'}" onclick="markAsRead('${n.id}')">
        <div class="flex items-start gap-3">
          <div class="shrink-0 flex items-center justify-center h-7 w-7 rounded-full bg-blue-600 text-white">
            ${getNotificationIcon(n.type)}
          </div>
          <div class="min-w-0">
            <p class="text-sm text-gray-900 font-medium line-clamp-1">${escapeHtml(n.title ?? 'Notification')}</p>
            <p class="text-xs text-gray-600 mt-0.5 line-clamp-2">${escapeHtml(n.message ?? '')}</p>
            <p class="text-[11px] text-gray-500 mt-1">🕐 ${formatDate(n.created_at)}</p>
          </div>
        </div>
      </button>
    `).join('');
  }

  function getNotificationIcon(type) {
    switch(type) {
      case 'new_registration': return '<i class="fas fa-user-plus text-white text-sm"></i>';
      case 'security_alert': return '<i class="fas fa-shield-alt text-white text-sm"></i>';
      case 'system_alert': return '<i class="fas fa-exclamation-triangle text-white text-sm"></i>';
      default: return '<i class="fas fa-bell text-white text-sm"></i>';
    }
  }

  function updateNotificationBadge(unreadCount) {
    const badge = document.getElementById('notificationBadge');
    if (!badge) return;
    if (unreadCount > 0) {
      badge.textContent = unreadCount > 9 ? '9+' : unreadCount;
      badge.classList.remove('hidden');
      badge.classList.add('flex');
    } else {
      badge.classList.add('hidden');
      badge.classList.remove('flex');
    }
  }

  function markAsRead(notificationId) {
    fetch(`/notifications/mark/${notificationId}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      credentials: 'same-origin'
    }).then(async (response) => {
      if (!response.ok) throw new Error('HTTP ' + response.status);
      return response.json();
    }).then(data => {
      notificationsData = notificationsData.map(n => n.id == notificationId ? { ...n, is_read: true } : n);
      displayNotifications(notificationsData);
      updateNotificationBadge(Number(data.unread_count ?? notificationsData.filter(n => !n.is_read).length));
    }).catch(err => console.error('Failed to mark as read', err));
  }

  function markAllNotificationsAsRead() {
    const unreadIds = notificationsData.filter(n => !n.is_read).map(n => n.id);
    if (unreadIds.length === 0) return;
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    Promise.all(unreadIds.map(id => fetch(`/notifications/mark/${id}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
      credentials: 'same-origin'
    }))).then(() => {
      notificationsData = notificationsData.map(n => ({ ...n, is_read: true }));
      displayNotifications(notificationsData);
      updateNotificationBadge(0);
    }).catch(err => console.error('Failed to mark all as read', err));
  }

  function formatDate(dateString) {
    const d = new Date(dateString);
    return d.toLocaleString();
  }

  function escapeHtml(text) {
    if (text == null) return '';
    return String(text)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/\"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // Future Enhancements (commented out for now):
  // - Auto-refresh every 1–2 minutes (already refreshing every 10s; adjust as needed for production hosting):
  //   setInterval(loadNotifications, 60 * 1000); // 1 minute
  // - Real-time push via Laravel Echo + Pusher/Socket.io:
  //   window.Echo.private(`notifications.user.${USER_ID}`)
  //     .listen('NotificationCreated', (e) => { loadNotifications(); });
  // - Email fallback for important alerts is handled at event/listener level
</script>

<!-- Notification Dropdown markup lives near the bell button above -->

@stack('scripts')
</body>
</html>
