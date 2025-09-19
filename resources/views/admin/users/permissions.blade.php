<x-layoutAdmin>
    <main class="flex-1 p-6">
        {{-- Page Header --}}
        <div class="page-header">
            <h1 class="page-title">Admin Permissions</h1>
            <p class="page-description">Manage permissions for admin user {{ $user->name }}</p>
        </div>

        <div class="bg-white rounded-md shadow-sm p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">User Details</h2>
                <a href="{{ route('admin.users.index') }}" class="btn-secondary">Back to Users</a>
            </div>

            <div class="user-info mb-6">
                <div class="flex items-center">
                    <div class="user-avatar mr-4" style="background: linear-gradient(45deg, #667eea, #764ba2);">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">{{ $user->name }}</h3>
                        <p class="text-gray-600">{{ $user->email }}</p>
                        <span class="role-badge role-{{ $user->role }}">{{ ucfirst($user->role) }}</span>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @csrf
                @method('PUT')

                <div class="col-span-2">
                    <h3 class="text-lg font-medium mb-4">System Permissions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="permission-group">
                            <h4 class="font-medium mb-2">User Management</h4>
                            <div class="permission-checks space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[users][view]" checked class="mr-2">
                                    <span>View Users</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[users][create]" checked class="mr-2">
                                    <span>Create Users</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[users][edit]" checked class="mr-2">
                                    <span>Edit Users</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[users][delete]" checked class="mr-2">
                                    <span>Delete Users</span>
                                </label>
                            </div>
                        </div>

                        <div class="permission-group">
                            <h4 class="font-medium mb-2">Course Management</h4>
                            <div class="permission-checks space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[courses][view]" checked class="mr-2">
                                    <span>View Courses</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[courses][create]" checked class="mr-2">
                                    <span>Create Courses</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[courses][edit]" checked class="mr-2">
                                    <span>Edit Courses</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[courses][delete]" checked class="mr-2">
                                    <span>Delete Courses</span>
                                </label>
                            </div>
                        </div>

                        <div class="permission-group">
                            <h4 class="font-medium mb-2">System Settings</h4>
                            <div class="permission-checks space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[settings][view]" checked class="mr-2">
                                    <span>View Settings</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[settings][edit]" checked class="mr-2">
                                    <span>Edit Settings</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[logs][view]" checked class="mr-2">
                                    <span>View Logs</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-span-2">
                    <div class="flex justify-end mt-6">
                        <button type="button" class="btn-secondary mr-2" onclick="history.back()">Cancel</button>
                        <button type="submit" class="btn-primary">Save Permissions</button>
                    </div>
                </div>
            </form>
        </div>
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
        
        .permission-group {
            background-color: #f9fafb;
            padding: 1rem;
            border-radius: 0.5rem;
        }

        .role-badge {
            display: inline-flex;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-top: 0.5rem;
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
    </style>
</x-layoutAdmin>