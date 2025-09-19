<x-layoutAdmin>
    <main class="flex-1 p-6">
        {{-- Page Header --}}
        <div class="page-header">
            <h1 class="page-title">View User</h1>
            <p class="page-description">Viewing details for {{ $user->name }}</p>
        </div>

        <div class="bg-white rounded-md shadow-sm p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold">User Details</h2>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn-primary">Edit User</a>
                    <a href="{{ route('admin.users.index') }}" class="btn-secondary">Back to Users</a>
                </div>
            </div>

            <div class="user-info mb-6">
                <div class="flex items-center">
                    <div class="user-avatar mr-4" style="background: linear-gradient(45deg, #667eea, #764ba2);">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">{{ $user->name }}</h3>
                        <p class="text-gray-600">{{ $user->email }}</p>
                        <div class="mt-2">
                            <span class="role-badge role-{{ $user->role }}">{{ ucfirst($user->role) }}</span>
                            <span class="status-badge status-{{ $user->status ?? 'active' }} ml-2">{{ ucfirst($user->status ?? 'active') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-medium mb-4">Account Information</h3>
                    <div class="space-y-4">
                        <div class="info-row">
                            <span class="info-label">User ID:</span>
                            <span class="info-value">{{ $user->id }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Name:</span>
                            <span class="info-value">{{ $user->name }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Email:</span>
                            <span class="info-value">{{ $user->email }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Role:</span>
                            <span class="info-value">{{ ucfirst($user->role) }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Status:</span>
                            <span class="info-value">{{ ucfirst($user->status ?? 'active') }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Verified:</span>
                            <span class="info-value">{{ $user->email_verified_at ? 'Yes' : 'No' }}</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-medium mb-4">Additional Details</h3>
                    <div class="space-y-4">
                        <div class="info-row">
                            <span class="info-label">Program:</span>
                            <span class="info-value">{{ $user->program ? $user->program->name : 'N/A' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Section:</span>
                            <span class="info-value">{{ $user->section ? $user->section->name : 'N/A' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Joined:</span>
                            <span class="info-value">{{ $user->created_at->format('F j, Y') }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Last Login:</span>
                            <span class="info-value">{{ $user->last_login_at ? $user->last_login_at->format('F j, Y g:i A') : 'Never' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($user->role === 'admin')
        <div class="bg-white rounded-md shadow-sm p-6 mb-6">
            <h3 class="text-lg font-medium mb-4">Admin Permissions</h3>
            <a href="{{ route('admin.users.permissions', $user) }}" class="btn-primary">Manage Permissions</a>
        </div>
        @endif

        @if($user->role === 'instructor')
        <div class="bg-white rounded-md shadow-sm p-6 mb-6">
            <h3 class="text-lg font-medium mb-4">Instructor Courses</h3>
            @if($user->taughtCourses && $user->taughtCourses->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($user->taughtCourses as $course)
                    <div class="bg-gray-50 p-4 rounded">
                        <h4 class="font-medium">{{ $course->name }}</h4>
                        <p class="text-sm text-gray-600">{{ $course->code }}</p>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-600">This instructor is not teaching any courses.</p>
            @endif
        </div>
        @endif

        @if($user->role === 'student')
        <div class="bg-white rounded-md shadow-sm p-6 mb-6">
            <h3 class="text-lg font-medium mb-4">Enrolled Courses</h3>
            @if($user->courses && $user->courses->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($user->courses as $course)
                    <div class="bg-gray-50 p-4 rounded">
                        <h4 class="font-medium">{{ $course->name }}</h4>
                        <p class="text-sm text-gray-600">{{ $course->code }}</p>
                        <p class="text-sm text-gray-600">Status: {{ ucfirst($course->pivot->status) }}</p>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-600">This student is not enrolled in any courses.</p>
            @endif
        </div>
        @endif
    </main>

    <style>
        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
        }

        .info-row {
            display: flex;
            border-bottom: 1px solid #e5e7eb;
            padding: 0.75rem 0;
        }

        .info-label {
            font-weight: 500;
            color: #6b7280;
            width: 120px;
        }

        .info-value {
            color: #111827;
            flex: 1;
        }

        .role-badge, .status-badge {
            display: inline-flex;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .role-admin {
            background-color: #c7d2fe;
            color: #4338ca;
        }

        .role-instructor {
            background-color: #bfdbfe;
            color: #1e40af;
        }

        .role-student {
            background-color: #bbf7d0;
            color: #166534;
        }

        .status-active {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-inactive {
            background-color: #fee2e2;
            color: #b91c1c;
        }
    </style>
</x-layoutAdmin>