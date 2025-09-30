<!-- Edit User Modal -->
<div id="editUserModal" class="modal hidden">
    <div class="modal-content">
        <h2 class="text-lg font-bold mb-4">Edit User</h2>
        <form id="editUserForm" method="POST">
            @csrf
            @method('PUT')
            <p class="text-xs text-gray-500 mb-3">For security, please confirm your admin password to apply changes.</p>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" id="editUserName" class="border border-gray-300 rounded-lg px-3 py-2 w-full">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="editUserEmail" class="border border-gray-300 rounded-lg px-3 py-2 w-full">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Role</label>
                <select name="role" id="editUserRole" class="border border-gray-300 rounded-lg px-3 py-2 w-full">
                    <option value="instructor">Instructor</option>
                    <option value="student">Student</option>
                    <option value="school_admin">School Admin</option>
                    <option value="super_admin">Super Admin</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="editUserStatus" class="border border-gray-300 rounded-lg px-3 py-2 w-full">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Your Admin Password</label>
                <input type="password" name="admin_password" id="editAdminPassword" class="border border-gray-300 rounded-lg px-3 py-2 w-full" required>
            </div>
            @if(auth()->user()->isSuperAdmin())
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">School</label>
                <select name="school_id" id="editUserSchool" class="border border-gray-300 rounded-lg px-3 py-2 w-full">
                    <option value="">-- Select School --</option>
                    @foreach($schools as $s)
                        <option value="{{ $s->id }}" {{ $activeSchool && $activeSchool->id == $s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                    @endforeach
                </select>
                @if($activeSchool)
                    <p class="text-xs text-gray-500 mt-1">
                        Currently managing: <strong>{{ $activeSchool->name }}</strong>
                    </p>
                @endif
            </div>
            @endif
            <div class="flex justify-end space-x-2">
                <button type="button" class="px-4 py-2 bg-gray-300 rounded-lg" data-modal-close>Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Update</button>
            </div>
        </form>
    </div>
</div>