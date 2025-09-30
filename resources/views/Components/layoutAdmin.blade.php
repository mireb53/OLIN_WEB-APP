<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>OLIN - Admin Page</title>

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
    <button class="relative p-2 text-white hover:text-gray-200">
      <i class="fa-solid fa-bell fa-lg"></i>
      <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
    </button>

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
  });
</script>
@stack('scripts')
</body>
</html>
