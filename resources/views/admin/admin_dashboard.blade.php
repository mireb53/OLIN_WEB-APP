<x-layoutAdmin>

    <main class="flex-1 overflow-y-auto p-4 md:p-8">
        <h1 class="text-3xl font-bold text-slate-900">Dashboard</h1>
        <p class="mt-2 mb-8 text-slate-500 text-lg">
            Welcome, Admin! Here is an overview of the system's health and activity.
        </p>

        <!-- System Overview & Key Metrics -->
        <section class="bg-white rounded-2xl p-8 mb-8 shadow-lg border border-gray-200">
            <h2 class="text-xl font-bold text-slate-900 mb-6 uppercase tracking-wide">
                System Overview & Key Metrics
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Courses -->
                <div class="bg-white rounded-2xl p-8 text-center transition-all duration-300 border border-gray-200 relative overflow-hidden hover:shadow-2xl">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-slate-900 to-slate-700"></div>
                    <div class="text-slate-500 mb-6 font-semibold text-lg">Total Courses</div>
                    <div class="text-6xl font-extrabold text-slate-900 my-4">{{ $stats['total_courses'] }}</div>
                    <div class="text-sm text-slate-600 mb-4">{{ $stats['active_courses'] }} Active</div>
                    <a href="{{ route('admin.course_management') }}" class="bg-gradient-to-r from-slate-900 to-slate-700 text-white font-semibold py-4 px-6 rounded-xl inline-block transition-all duration-300 uppercase tracking-wider hover:from-slate-700 hover:to-slate-900 hover:shadow-xl">
                        View All Courses
                    </a>
                </div>

                <!-- Total Users -->
                <div class="bg-white rounded-2xl p-8 text-center transition-all duration-300 border border-gray-200 relative overflow-hidden hover:shadow-2xl">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-slate-900 to-slate-700"></div>
                    <div class="text-slate-500 mb-6 font-semibold text-lg">Total Users</div>
                    <div class="text-6xl font-extrabold text-slate-900 my-4">{{ $stats['total_users'] }}</div>
                    <div class="text-sm text-slate-600 mb-4">{{ $stats['recent_registrations'] }} this week</div>
                    <a href="#" class="bg-gradient-to-r from-slate-900 to-slate-700 text-white font-semibold py-4 px-6 rounded-xl inline-block transition-all duration-300 uppercase tracking-wider hover:from-slate-700 hover:to-slate-900 hover:shadow-xl">
                        View All Users
                    </a>
                </div>

                <!-- Active Instructors -->
                <div class="bg-white rounded-2xl p-8 text-center transition-all duration-300 border border-gray-200 relative overflow-hidden hover:shadow-2xl">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-slate-900 to-slate-700"></div>
                    <div class="text-slate-500 mb-6 font-semibold text-lg">Instructors</div>
                    <div class="text-6xl font-extrabold text-slate-900 my-4">{{ $stats['total_instructors'] }}</div>
                    <div class="text-sm text-slate-600 mb-4">{{ $stats['total_students'] }} Students</div>
                    <a href="#" class="bg-gradient-to-r from-slate-900 to-slate-700 text-white font-semibold py-4 px-6 rounded-xl inline-block transition-all duration-300 uppercase tracking-wider hover:from-slate-700 hover:to-slate-900 hover:shadow-xl">
                        Manage Users
                    </a>
                </div>

                <!-- Storage Used -->
                <div class="bg-white rounded-2xl p-8 text-center transition-all duration-300 border border-gray-200 relative overflow-hidden hover:shadow-2xl">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-slate-900 to-slate-700"></div>
                    <div class="text-slate-500 mb-6 font-semibold text-lg">Storage Used</div>
                    <div class="text-3xl @if($systemHealth['storage_percentage'] > 80) text-red-600 @elseif($systemHealth['storage_percentage'] > 60) text-yellow-600 @else text-green-600 @endif font-extrabold my-4">
                        {{ $systemHealth['storage_used'] }} / {{ $systemHealth['storage_total'] }}
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mb-4">
                        <div class="@if($systemHealth['storage_percentage'] > 80) bg-red-600 @elseif($systemHealth['storage_percentage'] > 60) bg-yellow-600 @else bg-green-600 @endif h-2 rounded-full transition-all duration-500" style="width: {{ $systemHealth['storage_percentage'] }}%"></div>
                    </div>
                    <a href="#" class="bg-gradient-to-r from-slate-900 to-slate-700 text-white font-semibold py-4 px-6 rounded-xl inline-block transition-all duration-300 uppercase tracking-wider hover:from-slate-700 hover:to-slate-900 hover:shadow-xl">
                        View Storage Details
                    </a>
                </div>
            </div>
        </section>

        <!-- Admin Quick Actions + Recent Activity (Combined) -->
        <section class="bg-white rounded-2xl p-8 mb-8 shadow-lg border border-gray-200">
            <h2 class="text-xl font-bold text-slate-900 mb-6 uppercase tracking-wide">
                Quick Actions & Recent Activity
            </h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Admin Quick Actions -->
                <div>
                    <h3 class="mb-4 text-slate-900 font-semibold">Admin Quick Actions</h3>
                    <div class="flex flex-col gap-4">
                        <button onclick="openCreateCourseModal()" class="bg-gradient-to-r from-slate-900 to-slate-700 text-white font-semibold py-5 px-6 rounded-xl cursor-pointer text-base uppercase tracking-wider transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                            <i class="fas fa-plus mr-2"></i> Create New Course
                        </button>
                        <button onclick="openAddUserModal()" class="bg-gradient-to-r from-slate-900 to-slate-700 text-white font-semibold py-5 px-6 rounded-xl cursor-pointer text-base uppercase tracking-wider transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                            <i class="fas fa-user-plus mr-2"></i> Add New User
                        </button>
                        <a href="#" class="bg-gradient-to-r from-slate-900 to-slate-700 text-white font-semibold py-5 px-6 rounded-xl text-center cursor-pointer text-base uppercase tracking-wider transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                            <i class="fas fa-chart-bar mr-2"></i> View All Reports
                        </a>
                    </div>
                </div>

                <!-- Recent System Activity -->
                <div>
                    <h3 class="mb-4 text-slate-900 font-semibold">Recent System Activity</h3>
                    <div class="bg-slate-50 rounded-xl p-6">
                        @forelse($recentActivities as $activity)
                            <div class="flex items-center gap-4 py-4 @if(!$loop->last) border-b border-gray-200 @endif">
                                <div class="w-12 h-12 bg-slate-900 text-white rounded-xl flex items-center justify-center">
                                    <i class="{{ $activity['icon'] }}"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold">{{ $activity['description'] }}</div>
                                    <div class="text-slate-500 text-sm">{{ $activity['time']->diffForHumans() }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-slate-500">
                                <i class="fas fa-clock text-3xl mb-2"></i>
                                <p>No recent activity</p>
                            </div>
                        @endforelse
                        
                        <a href="#" class="mt-4 inline-block text-slate-900 font-semibold uppercase tracking-wider hover:underline text-sm">
                            View All Activities
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- System Health & Performance -->
        <section class="bg-white rounded-2xl p-8 mb-8 shadow-lg border border-gray-200">
            <h2 class="text-xl font-bold text-slate-900 mb-6 uppercase tracking-wide">
                System Health & Performance
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Server Status -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-slate-900">Server Status</h3>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-green-600 font-semibold">Online</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-slate-600">Uptime:</span>
                            <span class="font-semibold">{{ $systemHealth['server_uptime'] ?? '99.9%' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Response Time:</span>
                            <span class="font-semibold text-green-600">120ms</span>
                        </div>
                    </div>
                </div>

                <!-- Backup Status -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-slate-900">Backup Status</h3>
                        <i class="fas fa-database text-blue-600 text-xl"></i>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-slate-600">Last Backup:</span>
                            <span class="font-semibold">{{ $systemHealth['last_backup']->diffForHumans() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Status:</span>
                            <span class="font-semibold text-green-600">Successful</span>
                        </div>
                    </div>
                </div>

                <!-- Assessment Statistics -->
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-slate-900">Assessments</h3>
                        <i class="fas fa-clipboard-check text-purple-600 text-xl"></i>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-slate-600">Total:</span>
                            <span class="font-semibold">{{ $stats['total_assessments'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Submitted:</span>
                            <span class="font-semibold text-purple-600">{{ $stats['submitted_assessments'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Quick Statistics Grid -->
        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-500 text-sm font-medium">Weekly Registrations</p>
                        <p class="text-2xl font-bold text-slate-900">{{ $stats['recent_registrations'] }}</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <i class="fas fa-user-plus text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-500 text-sm font-medium">Active Courses</p>
                        <p class="text-2xl font-bold text-slate-900">{{ $stats['active_courses'] }}</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <i class="fas fa-book-open text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-500 text-sm font-medium">Storage Usage</p>
                        <p class="text-2xl font-bold 
                           @if($systemHealth['storage_percentage'] > 80) text-red-600 
                           @elseif($systemHealth['storage_percentage'] > 60) text-yellow-600 
                           @else text-green-600 @endif">
                           {{ $systemHealth['storage_percentage'] }}%
                        </p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-lg">
                        <i class="fas fa-hdd text-purple-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-500 text-sm font-medium">System Health</p>
                        <p class="text-2xl font-bold text-green-600">Excellent</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <i class="fas fa-heart text-green-600"></i>
                    </div>
                </div>
            </div>
        </section>

        <!-- Simple Modals for Quick Actions -->
        <div id="createCourseModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
            <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4">
                <h3 class="text-xl font-bold mb-4">Create New Course</h3>
                <p class="text-slate-600 mb-6">This feature will redirect you to the course creation page.</p>
                <div class="flex gap-4">
                    <button onclick="closeModal('createCourseModal')" class="flex-1 bg-gray-200 text-gray-800 py-2 px-4 rounded-lg hover:bg-gray-300">Cancel</button>
                    <button onclick="window.location.href='#'" class="flex-1 bg-slate-900 text-white py-2 px-4 rounded-lg hover:bg-slate-700">Continue</button>
                </div>
            </div>
        </div>

        <div id="addUserModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
            <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4">
                <h3 class="text-xl font-bold mb-4">Add New User</h3>
                <p class="text-slate-600 mb-6">This feature will redirect you to the user management page.</p>
                <div class="flex gap-4">
                    <button onclick="closeModal('addUserModal')" class="flex-1 bg-gray-200 text-gray-800 py-2 px-4 rounded-lg hover:bg-gray-300">Cancel</button>
                    <button onclick="window.location.href='#'" class="flex-1 bg-slate-900 text-white py-2 px-4 rounded-lg hover:bg-slate-700">Continue</button>
                </div>
            </div>
        </div>

        <script>
            function openCreateCourseModal() {
                document.getElementById('createCourseModal').classList.remove('hidden');
            }

            function openAddUserModal() {
                document.getElementById('addUserModal').classList.remove('hidden');
            }

            function closeModal(modalId) {
                document.getElementById(modalId).classList.add('hidden');
            }

            // Close modal when clicking outside
            document.querySelectorAll('[id$="Modal"]').forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        this.classList.add('hidden');
                    }
                });
            });
        </script>
    </main>

</x-layoutAdmin>
