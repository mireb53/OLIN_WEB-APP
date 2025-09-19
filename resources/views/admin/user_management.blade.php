<x-layoutAdmin>
    @push('page_assets')
        @vite(['resources/css/admin/user_management.css'])
    @endpush

    <main class="flex-1 p-6">
        {{-- Page Header --}}
        <div class="page-header">
            <h1 class="page-title">User Management</h1>
            <p class="page-description">Manage user accounts, roles, and permissions across the OLIN system.</p>
        </div>

        {{-- Controls Section --}}
        <div class="controls-section">
            <div class="controls-row">
                <form method="GET" action="{{ route('admin.users.search') }}" class="flex items-center space-x-4">
                    <div class="search-box">
                        <input type="text" name="search" placeholder="Search User by Name/Email/ID" 
                               value="{{ request('search') }}" />
                    </div>

                    <select name="role" class="filter-select">
                        <option value="all">Filter by Role</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="instructor" {{ request('role') === 'instructor' ? 'selected' : '' }}>Instructor</option>
                        <option value="student" {{ request('role') === 'student' ? 'selected' : '' }}>Student</option>
                    </select>

                    <select name="status" class="filter-select">
                        <option value="all">Filter by Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>

                    <button type="submit" class="btn-primary">Search</button>
                </form>

                <a href="{{ route('admin.users.create') }}" class="btn-primary">+ Add New User</a>
            </div>
        </div>

        {{-- Success/Error Messages --}}
        @if(session('success'))
            <div class="alert alert-success mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error mb-4">
                {{ session('error') }}
            </div>
        @endif
        
        {{-- User Table --}}
        <div class="course-table-container">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>
                            @php
                                $gradients = [
                                    '#ff6b6b, #ee5a52',
                                    '#4ecdc4, #44d4cc',
                                    '#a8e6cf, #7fcdcd',
                                    '#ffd3a5, #fd9853',
                                    '#667eea, #764ba2',
                                    '#f093fb, #f5576c',
                                    '#4facfe, #00f2fe',
                                    '#43e97b, #38f9d7',
                                ];
                                $gradient = $gradients[array_rand($gradients)];
                            @endphp
                            <div class="user-avatar" style="background: linear-gradient(45deg, {{ $gradient }});">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <strong>{{ $user->name }}</strong>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td><span class="role-badge role-{{ $user->role }}">{{ ucfirst($user->role) }}</span></td>
                        <td><span class="status-badge status-{{ $user->status ?? 'active' }}">{{ ucfirst($user->status ?? 'active') }}</span></td>
                        <td>{{ $user->last_login_at ? date('Y-m-d', strtotime($user->last_login_at)) : 'Never' }}</td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('admin.users.show', $user) }}" class="btn-small btn-view">View</a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn-small btn-edit">Edit</a>
                                @if($user->status === 'active')
                                    <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-small btn-deactivate" 
                                                onclick="return confirm('Are you sure you want to deactivate this user?')">Deactivate</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-small btn-activate" 
                                                onclick="return confirm('Are you sure you want to activate this user?')">Activate</button>
                                    </form>
                                @endif
                                @if($user->role === 'admin')
                                    <a href="{{ route('admin.users.permissions', $user) }}" class="btn-small btn-permissions">Permissions</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8">
                            <div class="text-gray-500">
                                <i class="fas fa-users fa-3x mb-4"></i>
                                <p class="text-lg font-medium">No users found</p>
                                <p class="text-sm">{{ request()->hasAny(['search', 'role', 'status']) ? 'Try adjusting your search criteria.' : 'Start by adding your first user.' }}</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if(method_exists($users, 'links'))
            <div class="mt-6">
                {{ $users->links() }}
            </div>
        @else
            <div class="pagination">
                <button disabled>← Previous</button>
                <button class="active">1</button>
                <button>2</button>
                <button>3</button>
                <button>Next →</button>
            </div>
        @endif
    </main>
</x-layoutAdmin>
