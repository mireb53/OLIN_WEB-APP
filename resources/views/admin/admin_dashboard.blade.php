<x-layoutAdmin>
    @push('styles')
    <style>
        /* Enhanced Quick Actions */
        .quick-action-btn {
            position: relative;
            overflow: hidden;
            transform-origin: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .quick-action-btn:hover {
            transform: translateY(-8px) scale(1.05);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        .quick-action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s ease;
        }
        .quick-action-btn:hover::before {
            left: 100%;
        }
        
        /* Card Hover Effects */
        .stat-card {
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        /* Announcement Animations */
        .announcement-item {
            transition: all 0.3s ease;
        }
        .announcement-item:hover {
            transform: translateX(8px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        /* Health Status Indicators */
        .health-card {
            position: relative;
            overflow: hidden;
        }
        .health-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }
        .health-card:hover::after {
            left: 100%;
        }
        
        /* Modal Animations */
        .modal-backdrop {
            backdrop-filter: blur(8px);
            animation: fadeIn 0.3s ease;
        }
        .modal-content {
            animation: slideUp 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        
        /* Toast Animations */
        .toast {
            animation: slideInRight 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), 
                      slideOutRight 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) 2.6s;
        }
        @keyframes slideInRight {
            from { transform: translateX(100%) scale(0.9); opacity: 0; }
            to { transform: translateX(0) scale(1); opacity: 1; }
        }
        @keyframes slideOutRight {
            from { transform: translateX(0) scale(1); opacity: 1; }
            to { transform: translateX(100%) scale(0.9); opacity: 0; }
        }
        
        /* Loading states */
        .loading { 
            opacity: 0.7; 
            pointer-events: none; 
            position: relative;
        }
        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 24px;
            height: 24px;
            margin: -12px 0 0 -12px;
            border: 3px solid #e5e7eb;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Scrollbar Styling */
        .overflow-y-auto::-webkit-scrollbar {
            width: 6px;
        }
        .overflow-y-auto::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }
        .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        /* Background Pattern */
        body {
            background-image: 
                radial-gradient(circle at 25% 25%, rgba(59, 130, 246, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(147, 51, 234, 0.05) 0%, transparent 50%);
        }
        
        /* Text Animations */
        .animate-fade-in {
            animation: fadeInUp 0.6s ease forwards;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
    @endpush
    
    <main class="flex-1 overflow-y-auto bg-gray-50 min-h-screen">
       <!-- Header Section -->
<div class="bg-white border-b border-gray-200 px-6 py-8">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold text-gray-900">Dashboard</h1>

                @if (Auth::user()->last_login_at == null)
                    <p class="mt-2 text-lg text-gray-600">
                        Welcome aboard, 
                        <span class="font-semibold text-blue-600">{{ Auth::user()->name }}</span>! 
                        Let’s get started.
                    </p>
                @else
                    <p class="mt-2 text-lg text-gray-600">
                        Welcome back, 
                        <span class="font-semibold text-blue-600">{{ Auth::user()->name }}</span>! 
                        Here’s your system overview.
                    </p>
                @endif
            </div>

            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <p class="text-sm text-gray-500">Current Time</p>
                    <p class="text-lg font-semibold text-gray-900">{{ now()->setTimezone(config('app.timezone'))->format('M d, Y - g:i A') }}</p>
                    <p class="text-xs text-gray-400">{{ config('app.timezone') }}</p>
                </div>

                @php $user = Auth::user(); @endphp
                @if((method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || (method_exists($user, 'isSchoolAdmin') && $user->isSchoolAdmin()))
                    <div class="hidden sm:flex items-center bg-blue-50 border border-blue-200 text-blue-700 rounded-full px-3 py-2" title="Active School Context">
                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path d="M10 2L2 6l8 4 8-4-8-4zm0 6l-6-3v5l6 3 6-3V5l-6 3zm-6 6v2l6 3 6-3v-2l-6 3-6-3z"/>
                        </svg>
                        <span class="font-medium">
                            @if(isset($activeSchool) && $activeSchool)
                                Monitoring: {{ $activeSchool->name }}
                            @else
                                No school selected
                            @endif
                        </span>
                        @if(!(isset($activeSchool) && $activeSchool))
                            <a href="{{ route('admin.settings') }}" class="ml-3 text-blue-600 hover:text-blue-800 underline text-sm">Manage</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>


        <div class="max-w-7xl mx-auto px-6 py-8">
            {{-- Quick Actions Section --}}
            <section class="mb-8">
                <div class="bg-white rounded-3xl p-8 shadow-xl border border-gray-100">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mr-4">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">Quick Actions</h2>
                                <p class="text-gray-600">Rapid access to essential functions</p>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <button onclick="openQuickAddUserModal()" class="quick-action-btn group relative overflow-hidden bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-2xl p-6 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl">
                            <div class="flex items-center justify-between">
                                <div class="text-left">
                                    <div class="flex items-center mb-2">
                                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        <span class="font-semibold text-lg">Add User</span>
                                    </div>
                                    <p class="text-blue-100 text-sm">Create new user account</p>
                                </div>
                                <div class="opacity-20 group-hover:opacity-30 transition-opacity">
                                    <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7L15 1H9C7.9 1 7 1.9 7 3V9C7 10.1 7.9 11 9 11H21C22.1 11 23 10.1 23 9ZM3 13V21C3 22.1 3.9 23 5 23H19C20.1 23 21 22.1 21 21V13H3Z"/>
                                    </svg>
                                </div>
                            </div>
                        </button>
                        <button onclick="openQuickAddCourseModal()" class="quick-action-btn group relative overflow-hidden bg-gradient-to-br from-green-500 to-green-600 text-white rounded-2xl p-6 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl">
                            <div class="flex items-center justify-between">
                                <div class="text-left">
                                    <div class="flex items-center mb-2">
                                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                        <span class="font-semibold text-lg">Add Course</span>
                                    </div>
                                    <p class="text-green-100 text-sm">Create new course</p>
                                </div>
                                <div class="opacity-20 group-hover:opacity-30 transition-opacity">
                                    <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M19,3H5C3.9,3 3,3.9 3,5V19C3,20.1 3.9,21 5,21H19C20.1,21 21,20.1 21,19V5C21,3.9 20.1,3 19,3M19,19H5V5H19V19Z"/>
                                    </svg>
                                </div>
                            </div>
                        </button>
                        <button onclick="openAnnouncementModal()" class="quick-action-btn group relative overflow-hidden bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-2xl p-6 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl">
                            <div class="flex items-center justify-between">
                                <div class="text-left">
                                    <div class="flex items-center mb-2">
                                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                                        </svg>
                                        <span class="font-semibold text-lg">Announcement</span>
                                    </div>
                                    <p class="text-purple-100 text-sm">Post new announcement</p>
                                </div>
                                <div class="opacity-20 group-hover:opacity-30 transition-opacity">
                                    <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12,2A3,3 0 0,1 15,5V11A3,3 0 0,1 12,14A3,3 0 0,1 9,11V5A3,3 0 0,1 12,2M19,11C19,14.53 16.39,17.44 13,17.93V21H11V17.93C7.61,17.44 5,14.53 5,11H7A5,5 0 0,0 12,16A5,5 0 0,0 17,11H19Z"/>
                                    </svg>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            </section>

            {{-- System Health & Security Summary --}}
            <section class="mb-8">
                <div class="bg-white rounded-3xl p-8 shadow-xl border border-gray-100">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-green-600 rounded-xl flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">System Health & Security</h2>
                            <p class="text-gray-600">Real-time monitoring and security overview</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {{-- Last Login (Hybrid format) --}}
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6 border border-blue-200">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center">
                                    <span class="text-2xl">👤</span>
                                </div>
                                @php $actor = Auth::user(); @endphp
                            </div>
                            <div>
                                <p class="text-sm font-medium text-blue-700 mb-1">Last Login</p>
                                @if($actor && $actor->last_login_at)
                                    @php
                                        // Determine role text: supports single 'role' field or roles relationship/Spatie
                                        $roleText = null;
                                        if(isset($actor->role) && $actor->role){
                                            $roleText = ucfirst(str_replace('_',' ', $actor->role));
                                        } elseif(method_exists($actor, 'roles')) {
                                            try {
                                                $names = $actor->roles()->pluck('name')->toArray();
                                                if(!empty($names)) { $roleText = implode(', ', $names); }
                                            } catch (\Throwable $e) { /* ignore */ }
                                        } elseif(method_exists($actor, 'getRoleNames')) {
                                            try {
                                                $names = $actor->getRoleNames();
                                                if($names && count($names)) { $roleText = implode(', ', $names->toArray()); }
                                            } catch (\Throwable $e) { /* ignore */ }
                                        }
                                        $roleText = $roleText ?: 'User';
                                    @endphp
                                    <p class="text-sm text-blue-900 font-semibold">
                                        {{ $actor->name }} ({{ $roleText }}) - {{ $actor->last_login_at->diffForHumans() }}
                                    </p>
                                @else
                                    <p class="text-sm text-blue-600">Never</p>
                                @endif
                            </div>
                        </div>

                        {{-- Active Users (Now) --}}
                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-6 border border-green-200">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center">
                                    <span class="text-2xl">👥</span>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-green-700 mb-1">Active Users (Currently Online)</p>
                                <p class="text-3xl font-bold text-green-900 mb-1">{{ $stats['active_users_now'] ?? 0 }}</p>
                                <p class="text-xs text-green-600">Active in last 15 minutes</p>
                            </div>
                        </div>

                        {{-- Failed Login Attempts (24h) --}}
                        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-2xl p-6 border border-red-200">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-red-500 rounded-xl flex items-center justify-center">
                                    <span class="text-2xl">🛡️</span>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-red-700 mb-1">Failed Logins (24h)</p>
                                <p class="text-3xl font-bold text-red-900 mb-1">{{ $stats['failed_logins_24h'] ?? 0 }}</p>
                                <p class="text-xs text-red-600">Security incidents</p>
                                <div class="mt-2">
                                    <a href="{{ route('admin.reports_logs') }}" class="text-xs text-red-700 hover:text-red-900 underline font-medium">View Details →</a>
                                </div>
                            </div>
                        </div>

                        
                    </div>
                </div>
            </section>

        {{-- School context banner removed; context now shown in header pill. --}}

     <section class="grid grid-cols-1 xl:grid-cols-4 gap-8 mb-10">
    {{-- Usage Statistics (3/4 width) --}}
    <div class="xl:col-span-3">
        <div class="bg-white rounded-3xl p-8 shadow-xl border border-gray-100">
            <div class="flex items-center mb-6">
                <div class="w-12 h-12 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Usage Statistics</h2>
                    <p class="text-gray-600">Current system metrics and activity</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-2xl p-6 border border-indigo-200 transform transition-all duration-200 hover:scale-105">
                    <div class="flex items-center justify-center w-16 h-16 bg-indigo-500 rounded-2xl mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="text-center">
                        <p class="text-sm font-medium text-indigo-700 mb-1">Instructors</p>
                        <p class="text-4xl font-bold text-indigo-900">{{ $stats['total_instructors'] ?? 0 }}</p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-6 border border-green-200 transform transition-all duration-200 hover:scale-105">
                    <div class="flex items-center justify-center w-16 h-16 bg-green-500 rounded-2xl mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                        </svg>
                    </div>
                    <div class="text-center">
                        <p class="text-sm font-medium text-green-700 mb-1">Students</p>
                        <p class="text-4xl font-bold text-green-900">{{ $stats['total_students'] ?? 0 }}</p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-2xl p-6 border border-amber-200 transform transition-all duration-200 hover:scale-105">
                    <div class="flex items-center justify-center w-16 h-16 bg-amber-500 rounded-2xl mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="text-center">
                        <p class="text-sm font-medium text-amber-700 mb-1">Active Courses</p>
                        <p class="text-4xl font-bold text-amber-900">{{ $stats['active_courses'] ?? 0 }}</p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-2xl p-6 border border-red-200 transform transition-all duration-200 hover:scale-105">
                    <div class="flex items-center justify-center w-16 h-16 bg-red-500 rounded-2xl mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <div class="text-center">
                        <p class="text-sm font-medium text-red-700 mb-1">Assessments</p>
                        <p class="text-4xl font-bold text-red-900">{{ $stats['total_assessments'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Announcements Panel (1/4 width) --}}
    <div class="xl:col-span-1">
        <div class="bg-white rounded-3xl p-6 shadow-xl border border-gray-100 h-full relative overflow-hidden">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Announcements</h3>
                </div>
               
            </div>
            <div class="space-y-4 max-h-96 overflow-y-auto">
                @forelse($announcements ?? [] as $announcement)
                    <div class="announcement-item p-4 bg-gray-50 rounded-xl border border-gray-100 transition-all duration-200 hover:shadow-md {{ $announcement->is_pinned ? 'bg-gradient-to-r from-purple-50 to-purple-100 border-purple-200' : '' }}">
                        @if($announcement->is_pinned)
                            <div class="flex items-center mb-2">
                                <svg class="w-4 h-4 text-purple-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4zM18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z"/>
                                </svg>
                                <span class="text-xs text-purple-600 font-semibold">PINNED</span>
                            </div>
                        @endif
                        <h4 class="font-semibold text-gray-900 text-sm mb-2">{{ $announcement->title }}</h4>
                        <p class="text-gray-600 text-xs leading-relaxed line-clamp-2">{{ $announcement->message }}</p>
                        <div class="flex items-center justify-between mt-3 pt-2 border-t border-gray-200">
                            <span class="text-xs text-gray-500">{{ $announcement->created_at->diffForHumans() }}</span>
                            @if($announcement->expires_at)
                                <span class="text-xs text-amber-600 bg-amber-50 px-2 py-1 rounded">Expires {{ $announcement->expires_at->diffForHumans() }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                            </svg>
                        </div>
                        <p class="text-gray-500 text-sm mb-3">No announcements yet</p>
                        <button onclick="openAnnouncementModal()" class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                            Post the first announcement
                        </button>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>

        @php
            $recentUsers = collect($recentActivities ?? [])->where('type','user_registration')->take(5);
            $recentCourses = collect($recentActivities ?? [])->where('type','course_creation')->take(5);
        @endphp

        {{-- Recent Activities Section --}}
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
            {{-- Recent User Registrations --}}
            <div class="bg-white rounded-3xl p-8 shadow-xl border border-gray-100">
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Recent User Registrations</h2>
                        <p class="text-gray-600">Latest user account creations</p>
                    </div>
                </div>
                <div class="space-y-4">
                    @forelse($recentUsers as $activity)
                        <div class="flex items-center p-4 bg-gradient-to-r from-indigo-50 to-indigo-100 rounded-xl border border-indigo-200 transition-all duration-200 hover:shadow-md">
                            <div class="w-12 h-12 bg-indigo-500 rounded-xl flex items-center justify-center mr-4">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-indigo-900 mb-1">{{ $activity['description'] }}</h3>
                                <p class="text-sm text-indigo-600">{{ $activity['time']->diffForHumans() }}</p>
                            </div>
                            <div class="w-2 h-2 bg-indigo-400 rounded-full"></div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="text-gray-500">No recent user registrations</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Recent Course Activity --}}
            <div class="bg-white rounded-3xl p-8 shadow-xl border border-gray-100">
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 bg-gradient-to-r from-amber-500 to-amber-600 rounded-xl flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Recent Course Activity</h2>
                        <p class="text-gray-600">Latest course developments</p>
                    </div>
                </div>
                <div class="space-y-4">
                    @forelse($recentCourses as $activity)
                        <div class="flex items-center p-4 bg-gradient-to-r from-amber-50 to-amber-100 rounded-xl border border-amber-200 transition-all duration-200 hover:shadow-md">
                            <div class="w-12 h-12 bg-amber-500 rounded-xl flex items-center justify-center mr-4">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-amber-900 mb-1">{{ $activity['description'] }}</h3>
                                <p class="text-sm text-amber-600">{{ $activity['time']->diffForHumans() }}</p>
                            </div>
                            <div class="w-2 h-2 bg-amber-400 rounded-full"></div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <p class="text-gray-500">No recent course activity</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        
    </div>

    {{-- Quick Add User Modal --}}
    <div id="quickAddUserModal" class="modal-backdrop fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen">
        <div class="modal-content bg-white rounded-2xl shadow-xl p-6 max-w-md w-full mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-900">🚀 Quick Add User</h3>
                <button onclick="closeQuickAddUserModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="quickAddUserForm" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                    <input type="text" name="name" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Role</label>
                    <select name="role" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="instructor">Instructor</option>
                        <option value="student">Student</option>
                        @if(auth()->user()->isSuperAdmin())
                        <option value="school_admin">School Admin</option>
                        <option value="super_admin">Super Admin</option>
                        @endif
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                    <input type="password" name="password" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeQuickAddUserModal()" class="px-4 py-2 text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Create User</button>
                </div>
            </form>
            <div class="mt-4 pt-4 border-t border-slate-200">
                <a href="{{ route('admin.user_management') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                    → Go to full User Management for advanced settings
                </a>
            </div>
        </div>
        </div>
    </div>

    {{-- Quick Add Course Modal --}}
    <div id="quickAddCourseModal" class="modal-backdrop fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen">
        <div class="modal-content bg-white rounded-2xl shadow-xl p-6 max-w-md w-full mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-900">🚀 Quick Add Course</h3>
                <button onclick="closeQuickAddCourseModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="quickAddCourseForm" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Course Name</label>
                    <input type="text" name="title" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Course Code</label>
                    <input type="text" name="course_code" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Program</label>
                    <select name="program_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                        <option value="">-- Select Program --</option>
                        @if(isset($programs))
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}">{{ $program->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Credits</label>
                    <input type="number" name="credits" min="1" max="10" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Instructor Email</label>
                    <div class="flex space-x-2">
                        <input type="email" id="instructorEmailLookup" class="flex-1 px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="instructor@example.com">
                        <button type="button" onclick="lookupInstructor()" class="px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700">Find</button>
                    </div>
                    <input type="hidden" name="instructor_id" id="selectedInstructorId">
                    <div id="instructorLookupResult" class="mt-2 text-sm text-slate-600"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeQuickAddCourseModal()" class="px-4 py-2 text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Create Course</button>
                </div>
            </form>
            <div class="mt-4 pt-4 border-t border-slate-200">
                <a href="{{ route('admin.course_management') }}" class="text-green-600 hover:text-green-800 text-sm">
                    → Go to full Course Management for advanced settings
                </a>
            </div>
        </div>
        </div>
    </div>

    {{-- Announcement Modal --}}
    <div id="announcementModal" class="modal-backdrop fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen">
            <div class="modal-content bg-white rounded-2xl shadow-xl p-6 max-w-lg w-full mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-900">📢 Post Announcement</h3>
                <button onclick="closeAnnouncementModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="announcementForm" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Title</label>
                    <input type="text" name="title" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Message</label>
                    <textarea name="message" rows="4" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500" required></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_pinned" class="mr-2 text-purple-600">
                            <span class="text-sm text-slate-700">Pin to top</span>
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Expires (optional)</label>
                        <input type="datetime-local" name="expires_at" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeAnnouncementModal()" class="px-4 py-2 text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">Post Announcement</button>
                </div>
            </form>
        </div>
        </div>
    </div>

    {{-- Success Toast --}}
    <div id="successToast" class="toast fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg hidden z-50">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span id="successToastMessage">Success!</span>
        </div>
    </div>

    {{-- Error Toast --}}
    <div id="errorToast" class="toast fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg hidden z-50">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L10 10.586l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <span id="errorToastMessage">Error occurred!</span>
        </div>
    </div>

<script>
// Quick Add User Modal Functions
function openQuickAddUserModal() {
    document.getElementById('quickAddUserModal').classList.remove('hidden');
}

function closeQuickAddUserModal() {
    document.getElementById('quickAddUserModal').classList.add('hidden');
    document.getElementById('quickAddUserForm').reset();
}

// Quick Add Course Modal Functions
function openQuickAddCourseModal() {
    document.getElementById('quickAddCourseModal').classList.remove('hidden');
}

function closeQuickAddCourseModal() {
    document.getElementById('quickAddCourseModal').classList.add('hidden');
    document.getElementById('quickAddCourseForm').reset();
    document.getElementById('instructorLookupResult').textContent = '';
    document.getElementById('selectedInstructorId').value = '';
}

// Announcement Modal Functions
function openAnnouncementModal() {
    document.getElementById('announcementModal').classList.remove('hidden');
}

function closeAnnouncementModal() {
    document.getElementById('announcementModal').classList.add('hidden');
    document.getElementById('announcementForm').reset();
}

// Toast Functions
function showSuccessToast(message) {
    const toast = document.getElementById('successToast');
    document.getElementById('successToastMessage').textContent = message;
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 3000);
}

function showErrorToast(message) {
    const toast = document.getElementById('errorToast');
    document.getElementById('errorToastMessage').textContent = message;
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 3000);
}

// Instructor Lookup Function
function lookupInstructor() {
    const email = document.getElementById('instructorEmailLookup').value.trim();
    const resultEl = document.getElementById('instructorLookupResult');
    
    if (!email) {
        resultEl.textContent = 'Please enter an email address.';
        return;
    }

    resultEl.textContent = 'Looking up instructor...';

    fetch('{{ route('admin.courses.findInstructor') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ email })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.instructor) {
            document.getElementById('selectedInstructorId').value = data.instructor.id;
            resultEl.textContent = `✓ Found: ${data.instructor.name} (${data.instructor.email})`;
            resultEl.className = 'mt-2 text-sm text-green-600';
        } else {
            resultEl.textContent = data.message || 'Instructor not found.';
            resultEl.className = 'mt-2 text-sm text-red-600';
        }
    })
    .catch(error => {
        resultEl.textContent = 'Error looking up instructor.';
        resultEl.className = 'mt-2 text-sm text-red-600';
    });
}

// Form Submissions
document.getElementById('quickAddUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route('admin.users.store') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(async response => {
        const data = await response.json().catch(() => ({}));
        if (response.ok || data.success) {
            showSuccessToast('User created successfully!');
            closeQuickAddUserModal();
            // Optionally refresh page or update UI
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(data.message || 'Failed to create user');
        }
    })
    .catch(error => {
        showErrorToast(error.message || 'Error creating user');
    });
});

document.getElementById('quickAddCourseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const instructorId = document.getElementById('selectedInstructorId').value;
    if (!instructorId) {
        showErrorToast('Please look up and select an instructor first.');
        return;
    }
    
    const formData = new FormData(this);
    
    fetch('{{ route('admin.courses.store') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(async response => {
        const data = await response.json().catch(() => ({}));
        if (response.ok || data.success) {
            showSuccessToast('Course created successfully!');
            closeQuickAddCourseModal();
            // Optionally refresh page or update UI
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(data.message || 'Failed to create course');
        }
    })
    .catch(error => {
        showErrorToast(error.message || 'Error creating course');
    });
});

document.getElementById('announcementForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route('admin.announcements.store') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(async response => {
        const data = await response.json().catch(() => ({}));
        if (response.ok || data.success) {
            showSuccessToast('Announcement posted successfully!');
            closeAnnouncementModal();
            // Optionally refresh page or update UI
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(data.message || 'Failed to post announcement');
        }
    })
    .catch(error => {
        showErrorToast(error.message || 'Error posting announcement');
    });
});
</script>
</x-layoutAdmin>
