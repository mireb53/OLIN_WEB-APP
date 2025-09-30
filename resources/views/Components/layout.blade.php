<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>OLIN System</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php $appBase = trim(request()->getBaseUrl(), '/'); @endphp
    <meta name="app-base" content="{{ $appBase }}">
    <style>
        body {
            font-family: "Inter", sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .sidebar {
            background-color: #334155; /* Slate-700 */
        }
        .sidebar-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 4px solid #3B82F6; /* Blue-500 */
        }

        .profile-icon {
            background: linear-gradient(135deg, #10B981 80%, #3B82F6 20%);
        }

        /* Profile icon (accent usage 10%) */
        /* .profile-icon {
        background: linear-gradient(135deg, #10B981 70%, #3B82F6 30%);
        } */

        #sidebar, .modern-sidebar {
            background: linear-gradient(180deg, #01183a 0%, #032762 100%);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
        }

        .sidebar-nav {
            padding: 20px 0;
            flex: 1;
        }

        .nav-section {
            margin-bottom: 32px;
        }

        .nav-section-title {
            color: #64748b;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
            padding: 0 20px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #cbd5e1;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            margin: 0 12px;
            border-radius: 10px;
        }

        /* Hover effect (subtle accent, not overpowering) */
        .nav-item:hover {
            background: rgba(59, 130, 246, 0.15);
            color: white;
            transform: translateX(4px);
        }

        /* .nav-item-active {
            background: linear-gradient(135deg, #10B981 0%, #3B82F6 50%, #7C3AED 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        } */
        .nav-item-active {
            background: linear-gradient(135deg, #3B82F6 0%, #2563eb 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); /* blue glow */
        }

        .nav-item-active:hover {
            transform: none;
        }

        .nav-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 16px;
        }

        .nav-text {
            font-weight: 500;
            font-size: 14px;
            flex: 1;
        }

        .nav-indicator {
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: currentColor;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .nav-item-active .nav-indicator {
            opacity: 1;
        }

        /* Scrollbar Styling */
        .modern-sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .modern-sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .modern-sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 2px;
        }

        .modern-sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        /* Mobile adjustments */
        @media (max-width: 768px) {
            .modern-sidebar {
                box-shadow: 8px 0 32px rgba(0, 0, 0, 0.3);
            }
        }
    </style>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <div class="bg-gray-800"></div>

    <header class="bg-[#3B82F6] shadow-sm h-16 fixed top-0 w-full z-20 flex items-center justify-between px-4 sm:px-6 md:px-8">

    <div class="flex items-center">
      <button id="menu-toggle" class="md:hidden p-2 text-white hover:text-gray-200 focus:outline-none transition-colors duration-200 mr-4 relative z-40">
        <i class="fa-solid fa-bars fa-lg"></i>
      </button>
      <div class="text-2xl font-extrabold text-white">OLIN</div>
    </div>

    <div class="flex items-center space-x-4 relative z-40">
      <button id="notificationBtn" class="relative p-2 text-white hover:text-gray-200 transition-colors duration-200 focus:outline-none">
        <i class="fa-solid fa-bell fa-lg"></i>
    <span id="notificationBadge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 items-center justify-center hidden"></span>
      </button>

      <div class="relative">
        @php
            $profileUrl = '#';
            if (Auth::check()) {
                $role = Auth::user()->role ?? null;
                if ($role === 'instructor') {
                    $profileUrl = route('instructor.showProfile');
                } elseif (in_array($role, ['super_admin','school_admin'])) {
                    $profileUrl = route('admin.account');
                }
            }
        @endphp
        @php
            $baseUrlPath = trim(request()->getBaseUrl(), '/');
            $headerImageUrl = Auth::user()->profile_image 
                ? route('media.profile', ['filename' => basename(Auth::user()->profile_image)])
                : null;
        @endphp
        @if(Auth::user()->profile_image)
            <a id="headerProfileLink" href="{{ $profileUrl }}">
             <img id="headerProfileImg" class="w-10 h-10 rounded-full object-cover cursor-pointer transition-transform duration-300 hover:scale-105"
                 src="{{ $headerImageUrl }}" 
                     alt="{{ Auth::user()->name }}'s profile image">
            </a>
        @else
            <a id="headerProfileLink" href="{{ $profileUrl }}" 
               class="profile-icon text-white font-semibold rounded-full w-10 h-10 flex items-center justify-center cursor-pointer transition-transform duration-300 hover:scale-105">
               <span id="headerProfileInitial">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
            </a>
        @endif
        <span class="absolute bottom-0 right-0 block w-3 h-3 bg-[#10B981] rounded-full border-2 border-[#3B82F6]"></span>
      </div>

    </div>
  </header>

    <div class="flex pt-16">
        <aside id="sidebar"
        class="modern-sidebar fixed top-16 left-0 h-[calc(100%-4rem)] w-64 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out overflow-y-auto z-10">

             <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Main</div>
                    
                    <a href="{{ route('instructor.dashboard') }}" 
                       class="nav-item {{ Request::routeIs('instructor.dashboard') ? 'nav-item-active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <span class="nav-text">Dashboard</span>
                        <div class="nav-indicator"></div>
                    </a>

                  

                    <a href="{{ route('instructor.myCourse') }}" 
                       class="nav-item {{ Request::routeIs('instructor.myCourse') ? 'nav-item-active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <span class="nav-text">Course Management</span>
                        <div class="nav-indicator"></div>
                    </a>

                    <a href="{{ route('instructor.studenManagement') }}" 
                       class="nav-item {{ Request::routeIs('instructor.studenManagement') ? 'nav-item-active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <span class="nav-text">Student Management</span>
                        <div class="nav-indicator"></div>
                    </a>

                    <a href="{{ route('instructor.studentProgress') }}" 
                       class="nav-item {{ Request::routeIs('instructor.studentProgress') ? 'nav-item-active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <span class="nav-text">Student Progress</span>
                        <div class="nav-indicator"></div>
                    </a>
                </div>

                <!-- <div class="nav-section">
                    <div class="nav-section-title">Analytics</div>
                    
                    <a href="#" 
                       class="nav-item">
                        <div class="nav-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <span class="nav-text">Reports</span>
                        <div class="nav-indicator"></div>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Support</div>
                    
                    <a href="#" 
                       class="nav-item">
                        <div class="nav-icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <span class="nav-text">Help Center</span>
                        <div class="nav-indicator"></div>
                    </a>
                </div> -->
            </nav>
        </aside>

        <main class="flex-1 p-6 overflow-auto ml-64">
            {{-- <div class="flex justify-start space-x-4 mb-6">
                <a href="{{ Route('course.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-300 ease-in-out flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    <span>Create New Course</span>
                </a>
                <button class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-300 ease-in-out flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2h8a2 2 0 012 2v10.586a1 1 0 01-1.707.707L15 15.586V6a4 4 0 00-4-4H9a4 4 0 00-4 4v9.586l-.293.293A1 1 0 014 15.586V5zM14 6H6v8h8V6z" clip-rule="evenodd" />
                    </svg>
                    <span>View All My Courses</span>
                </button>
                <button class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-300 ease-in-out flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                    </svg>
                    <span>View Reports</span>
                </button>
            </div>
             --}}
            <?php echo $slot ?>
        </main>
    </div>
    {{ $scripts ?? '' }}

    <!-- Notification Modal -->
    <div id="notificationModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeNotificationModal()"></div>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="w-full">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
                                    <i class="fas fa-bell text-blue-600 mr-2"></i>
                                    Notifications
                                </h3>
                                <div class="flex items-center space-x-2">
                                    <button id="markAllReadBtn" class="text-sm text-blue-600 hover:text-blue-800 transition-colors">
                                        Mark all as read
                                    </button>
                                    <button onclick="closeNotificationModal()" class="text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div id="notificationsList" class="max-h-96 overflow-y-auto">
                                <div id="notificationsLoading" class="flex items-center justify-center py-8">
                                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                                </div>
                                <div id="notificationsContent" class="hidden space-y-3">
                                    <!-- Notifications will be loaded here -->
                                </div>
                                <div id="noNotifications" class="hidden text-center py-8 text-gray-500">
                                    <i class="fas fa-bell-slash text-4xl mb-4 text-gray-300"></i>
                                    <p>No notifications yet</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .notification-item {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .notification-item:hover {
            background-color: #f9fafb;
        }

        .notification-item.unread {
            background-color: #eff6ff;
            border-color: #bfdbfe;
        }

        .notification-item.unread:hover {
            background-color: #dbeafe;
        }

        .notification-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .notification-icon.assessment_submitted {
            background-color: #7c3aed;
        }

        .notification-icon.student_enrolled {
            background-color: #10b981;
        }

        .unread-dot {
            width: 8px;
            height: 8px;
            background-color: #3b82f6;
            border-radius: 50%;
            flex-shrink: 0;
        }
    </style>

    <script>
        let notificationsData = [];
        
        document.addEventListener("DOMContentLoaded", function () {
            // Menu toggle functionality
            document.getElementById('menu-toggle').addEventListener('click', function () {
                const sidebar = document.getElementById('sidebar');
                sidebar.classList.toggle('-translate-x-full');
            });
            
            // Notification functionality
            document.getElementById('notificationBtn').addEventListener('click', function() {
                openNotificationModal();
            });
            
            document.getElementById('markAllReadBtn').addEventListener('click', function() {
                markAllNotificationsAsRead();
            });
            
            // Load notifications on page load
            loadNotifications();
            
            // Refresh notifications every 30 seconds
            setInterval(loadNotifications, 30000);
        });

        function openNotificationModal() {
            document.getElementById('notificationModal').classList.remove('hidden');
            loadNotifications();
        }

        function closeNotificationModal() {
            document.getElementById('notificationModal').classList.add('hidden');
        }

        function loadNotifications() {
            fetch('{{ route("instructor.notifications") }}')
                .then(response => response.json())
                .then(data => {
                    notificationsData = data.notifications;
                    displayNotifications(data.notifications);
                    updateNotificationBadge(data.unread_count);
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                });
        }

        function displayNotifications(notifications) {
            const loadingEl = document.getElementById('notificationsLoading');
            const contentEl = document.getElementById('notificationsContent');
            const noNotificationsEl = document.getElementById('noNotifications');
            
            loadingEl.classList.add('hidden');
            
            if (notifications.length === 0) {
                contentEl.classList.add('hidden');
                noNotificationsEl.classList.remove('hidden');
                return;
            }
            
            noNotificationsEl.classList.add('hidden');
            contentEl.classList.remove('hidden');
            
            contentEl.innerHTML = notifications.map(notification => `
                <div class="notification-item ${notification.read ? '' : 'unread'}" onclick="markAsRead('${notification.id}')">
                    <div class="flex items-start space-x-3">
                        <div class="notification-icon ${notification.type}">
                            ${getNotificationIcon(notification.type)}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-800 font-medium">${notification.description}</p>
                            ${notification.course ? `<p class="text-xs text-gray-500 mt-1">📚 ${notification.course}</p>` : ''}
                            <p class="text-xs text-gray-500 mt-1">🕐 ${formatDate(notification.date)}</p>
                        </div>
                        ${!notification.read ? '<div class="unread-dot"></div>' : ''}
                    </div>
                </div>
            `).join('');
        }

        function getNotificationIcon(type) {
            switch(type) {
                case 'assessment_submitted':
                    return '<i class="fas fa-paper-plane text-white text-sm"></i>';
                case 'student_enrolled':
                    return '<i class="fas fa-user-plus text-white text-sm"></i>';
                default:
                    return '<i class="fas fa-bell text-white text-sm"></i>';
            }
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffTime = Math.abs(now - date);
            const diffMinutes = Math.floor(diffTime / (1000 * 60));
            const diffHours = Math.floor(diffTime / (1000 * 60 * 60));
            const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
            
            if (diffMinutes < 1) {
                return 'Just now';
            } else if (diffMinutes < 60) {
                return `${diffMinutes} minute${diffMinutes > 1 ? 's' : ''} ago`;
            } else if (diffHours < 24) {
                return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
            } else if (diffDays === 1) {
                return 'Yesterday';
            } else if (diffDays < 7) {
                return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
            } else {
                return date.toLocaleDateString();
            }
        }

        function updateNotificationBadge(unreadCount) {
            const badge = document.getElementById('notificationBadge');
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
            fetch('{{ route("instructor.markNotificationAsRead") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    notification_id: notificationId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the notification in the local data
                    const notification = notificationsData.find(n => n.id === notificationId);
                    if (notification) {
                        notification.read = true;
                    }
                    
                    // Refresh the display
                    displayNotifications(notificationsData);
                    const unreadCount = notificationsData.filter(n => !n.read).length;
                    updateNotificationBadge(unreadCount);
                }
            })
            .catch(error => {
                console.error('Error marking notification as read:', error);
            });
        }

        function markAllNotificationsAsRead() {
            const unreadIds = notificationsData.filter(n => !n.read).map(n => n.id);
            
            if (unreadIds.length === 0) {
                return;
            }
            
            fetch('{{ route("instructor.markAllNotificationsAsRead") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    notification_ids: unreadIds
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mark all notifications as read in local data
                    notificationsData.forEach(n => n.read = true);
                    
                    // Refresh the display
                    displayNotifications(notificationsData);
                    updateNotificationBadge(0);
                }
            })
            .catch(error => {
                console.error('Error marking all notifications as read:', error);
            });
        }
    </script>
</body>
</html>