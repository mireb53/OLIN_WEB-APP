<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>OLIN - Admin Page</title>
  
  @vite(['resources/css/admin_layouts.css',
         'resources/css/app.css',  
         'resources/js/app.js'])

  {{-- Page specific assets pushed from child views --}}
  @stack('page_assets')

  {{-- Assets --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">

</head>

<body class="bg-gray-50 min-h-screen flex flex-col">

  {{-- Header (design only updated) --}}
  <header class="bg-[#10B981] shadow-sm h-16 fixed top-0 w-full z-20 flex items-center justify-between px-4 sm:px-6 md:px-8">
    <div class="flex items-center">
      <button id="menu-toggle" class="md:hidden p-2 text-white hover:text-gray-200 focus:outline-none transition-colors duration-200 mr-4 relative z-40">
        <i class="fa-solid fa-bars fa-lg"></i>
      </button>
      <div class="text-2xl font-extrabold text-white">OLIN</div>
    </div>

    <div class="flex items-center space-x-4 relative z-40">
      <button class="relative p-2 text-white hover:text-gray-200 transition-colors duration-200 focus:outline-none">
        <i class="fa-solid fa-bell fa-lg"></i>
        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
      </button>

      <div class="relative">
        @if(Auth::user()->profile_image)
          <a href="{{ route('admin.account') }}">
            <img class="w-10 h-10 rounded-full object-cover cursor-pointer transition-transform duration-300 hover:scale-105"
              src="{{ asset('storage/' . Auth::user()->profile_image) }}" 
              alt="{{ Auth::user()->name }}'s profile image">
          </a>
        @else
          <a href="{{ route('admin.account') }}" 
            class="profile-icon text-white font-semibold rounded-full w-10 h-10 flex items-center justify-center cursor-pointer transition-transform duration-300 hover:scale-105">
            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
          </a>
        @endif
        <span class="absolute bottom-0 right-0 block w-3 h-3 bg-[#F9FAFB] rounded-full border-2 border-[#10B981]"></span>
      </div>
    </div>
  </header>

  {{-- Body with sidebar --}}
  <div class="flex pt-16">
    <aside id="sidebar"
      class="modern-sidebar fixed top-16 left-0 h-[calc(100%-4rem)] w-64 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out overflow-y-auto z-10">
      <nav class="sidebar-nav">
        <div class="nav-section">
          <div class="nav-section-title">Main</div>
          
          <a href="{{ route('admin.dashboard') }}" 
            class="nav-item {{ Request::routeIs('admin.dashboard') ? 'nav-item-active' : '' }}">
            <div class="nav-icon"><i class="fas fa-home"></i></div>
            <span class="nav-text">Dashboard</span>
          </a>
          
          <a href="{{ route('admin.user_management') }}" 
            class="nav-item {{ Request::routeIs('admin.user_management') ? 'nav-item-active' : '' }}">
            <div class="nav-icon"><i class="fas fa-users-cog"></i></div>
            <span class="nav-text">User Management</span>
          </a>
  
          <a href="{{ route('admin.course_management') }}" 
            class="nav-item {{ Request::routeIs('admin.course_management') ? 'nav-item-active' : '' }}">
            <div class="nav-icon"><i class="fas fa-book"></i></div>
            <span class="nav-text">Course Management</span>
          </a>
        </div>
  
        <div class="nav-section">
          <div class="nav-section-title">Admin Tools</div>
          
          <a href="{{ route('admin.settings') }}" 
            class="nav-item {{ Request::routeIs('admin.settings') ? 'nav-item-active' : '' }}">
            <div class="nav-icon"><i class="fas fa-cogs"></i></div>
            <span class="nav-text">Settings</span>
          </a>
          
          <a href="{{ route('admin.reports_logs') }}" 
            class="nav-item {{ Request::routeIs('admin.reports_logs') ? 'nav-item-active' : '' }}">
            <div class="nav-icon"><i class="fas fa-file-alt"></i></div>
            <span class="nav-text">Reports & Logs</span>
          </a>
        </div>
  
        <!-- <div class="nav-section">
          <div class="nav-section-title">Support</div>
          
          <a href="{{ route('admin.help') }}" 
            class="nav-item {{ Request::routeIs('admin.help') ? 'nav-item-active' : '' }}">
            <div class="nav-icon"><i class="fas fa-question-circle"></i></div>
            <span class="nav-text">Help Center</span>
          </a>
        </div> -->
      </nav>
    </aside>
  
    <main class="flex-1 p-6 overflow-auto ml-64">
      {{ $slot }}
    </main>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      document.getElementById('menu-toggle').addEventListener('click', function () {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('-translate-x-full');
      });
    });
  </script>
  
  {{-- Page specific scripts pushed from child views --}}
  @stack('scripts')
</body>
</html>
