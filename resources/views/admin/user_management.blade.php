<x-layoutAdmin>
 @push('page_assets')
        @vite(['resources/css/admin/user_management.css'])
    @endpush

    <main class="flex-1 p-6">
        {{-- Alerts --}}
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">User Management</h1>
            <p class="text-gray-600">Manage user accounts, roles, and permissions across the OLIN system.</p>
        </div>

        {{-- Search + Filters --}}
        <div class="bg-white p-4 rounded-lg shadow flex flex-wrap gap-3 items-center mb-6">
            <form method="GET" action="{{ route('admin.user_management') }}" class="flex flex-wrap gap-3 w-full">
                <input type="hidden" name="role" value="{{ $role }}">

                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="Search User by Name/Email/ID" 
                    class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring focus:border-indigo-500"
                >

                <select 
                    name="role"
                    onchange="this.form.submit()"
                    class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring focus:border-indigo-500"
                >
                    <option value="">Filter by Role</option>
                    <option value="instructor" {{ $role === 'instructor' ? 'selected' : '' }}>Instructor</option>
                    <option value="student" {{ $role === 'student' ? 'selected' : '' }}>Student</option>
                    <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>

                <select 
                    name="status"
                    onchange="this.form.submit()"
                    class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring focus:border-indigo-500"
                >
                    <option value="">Filter by Status</option>
                    <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="suspended" {{ $status == 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>

                <div class="ml-auto flex gap-2">
                    <button 
                        type="button" 
                        data-modal-target="bulkImportModal"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg shadow hover:bg-gray-200"
                    >
                        Bulk Import/Export
                    </button>
                    <button 
                        type="button" 
                        data-modal-target="addUserModal"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700"
                    >
                        + Add New User
                    </button>
                </div>
            </form>
        </div>

        {{-- User Table --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            @if($users->isEmpty())
                <div class="p-8 text-center text-gray-500">No users found.</div>
            @else
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Name</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Email</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Role</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Status</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Last Login</th>
                            <th class="px-4 py-3 text-sm font-medium text-gray-600 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($users as $user)
                            <tr class="hover:bg-gray-50">
                                {{-- Name + Avatar + Online Indicator --}}
                                <td class="px-4 py-3 flex items-center space-x-3">
                                    <div class="w-8 h-8 relative">
                                        @php
                                            $fallbackBg = $loop->index % 4 == 0 ? 'bg-red-400' : ($loop->index % 4 == 1 ? 'bg-teal-400' : ($loop->index % 4 == 2 ? 'bg-green-400' : 'bg-orange-400'));
                                        @endphp
                                        @if(!empty($user->profile_image))
                                            <img
                                                src="{{ route('media.profile', ['filename' => basename($user->profile_image)]) }}"
                                                alt="{{ $user->name }} profile"
                                                class="w-8 h-8 rounded-full object-cover"
                                                onerror="this.style.display='none'; const f=this.nextElementSibling; if(f){ f.style.display='flex'; }"
                                            >
                                            <div class="absolute inset-0 rounded-full text-white font-bold items-center justify-center {{ $fallbackBg }}" style="display:none;">
                                                <div class="w-full h-full flex items-center justify-center">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                                            </div>
                                        @else
                                            <div class="w-8 h-8 rounded-full text-white font-bold flex items-center justify-center {{ $fallbackBg }}">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="font-medium text-gray-800">{{ $user->name }}</span>
                                        {{-- Online Status Indicator (Based on Session Activity) --}}
                                        @php
                                            $isOnline = in_array($user->id, $onlineUserIds ?? []);
                                        @endphp
                                        <div class="relative">
                                            <div class="w-3 h-3 rounded-full {{ $isOnline ? 'bg-green-500' : 'bg-gray-400' }}" 
                                                 title="{{ $isOnline ? 'Online (active in last 15 minutes)' : 'Offline' }}"></div>
                                            @if($isOnline)
                                                <div class="absolute -top-0.5 -right-0.5 w-2 h-2 bg-green-400 rounded-full animate-ping"></div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Email --}}
                                <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>

                                {{-- Role Badge --}}
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs rounded-md 
                                        {{ $user->role === 'admin' ? 'bg-red-100 text-red-600' : 
                                           ($user->role === 'instructor' ? 'bg-indigo-100 text-indigo-600' : 'bg-green-100 text-green-600') }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>

                                {{-- Status Badge --}}
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs rounded-md
                                        {{ $user->status === 'active' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </td>

                            {{-- Last Login --}}
<td class="px-4 py-3 text-gray-500 text-sm">
    @if($user->last_login_at)
        {{ \Carbon\Carbon::parse($user->last_login_at)->format('Y-m-d H:i') }}
    @else
        —
    @endif
</td>


                                {{-- Actions (Edit, Reset Password, Delete ONLY) --}}
                                <td class="px-4 py-3 text-center space-x-2">
                                    @can('update', $user)
                                    <button 
                                        type="button"
                                        class="px-3 py-1 text-sm bg-indigo-100 text-indigo-600 rounded hover:bg-indigo-200 edit-user-btn"
                                        data-user-id="{{ $user->id }}"
                                        data-user-name="{{ $user->name }}"
                                        data-user-email="{{ $user->email }}"
                                        data-user-role="{{ $user->role }}"
                                        data-user-status="{{ $user->status }}"
                                        data-user-first="{{ $user->first_name }}"
                                        data-user-last="{{ $user->last_name }}"
                                    >
                                        Edit
                                    </button>
                                    @endcan
                                    @can('view', $user)
                                    <a 
                                        href="{{ route('admin.users.show', $user->id) }}"
                                        class="px-3 py-1 text-sm bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200"
                                        title="View Profile"
                                    >
                                        View
                                    </a>
                                    @endcan
                                    @can('delete', $user)
                                    <button 
                                        type="button"
                                        data-modal-target="deleteUserModal"
                                        data-user-id="{{ $user->id }}"
                                        data-user-email="{{ $user->email }}"
                                        class="px-3 py-1 text-sm bg-red-100 text-red-600 rounded hover:bg-red-200 delete-user-btn"
                                    >
                                        Delete
                                    </button>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Pagination --}}
                <div class="p-4 border-t">
                    {{ $users->links('pagination::tailwind') }}
                </div>
            @endif
        </div>
    </main>

    {{-- Modals --}}
    @include('admin.UsersModals.add-user-modal')
    @include('admin.UsersModals.edit-user-modal')
    {{-- Reset password modal no longer used on this page --}}
    {{-- @include('admin.UsersModals.reset-password-modal') --}}
    @include('admin.UsersModals.delete-user-modal')
    @include('admin.UsersModals.bulk-import_export-modal')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    console.log('User Management JS loaded');
    const openModalButtons = document.querySelectorAll('[data-modal-target]');
    const closeModalButtons = document.querySelectorAll('[data-modal-close]');

    openModalButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const modalId = button.getAttribute('data-modal-target');
            const modal = document.getElementById(modalId);
            if (modal) modal.classList.remove('hidden');
        });
    });

    closeModalButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const modal = button.closest('.modal');
            if (modal) modal.classList.add('hidden');
        });
    });

    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.classList.add('hidden');
        });
    });

    document.querySelectorAll('.edit-user-btn').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const { userId, userName, userEmail, userRole, userStatus } = button.dataset;
            const modal = document.getElementById('editUserModal');
            const form = document.getElementById('editUserForm');
            form.action = `/admin/users/${userId}`;
            document.getElementById('editUserName').value = userName;
            document.getElementById('editUserEmail').value = userEmail;
            document.getElementById('editUserRole').value = userRole;
            document.getElementById('editUserStatus').value = userStatus;
            // First/Last names if present as data attrs; fallback empty
            const fn = button.getAttribute('data-user-first') || '';
            const ln = button.getAttribute('data-user-last') || '';
            const fnEl = document.getElementById('editUserFirstName');
            const lnEl = document.getElementById('editUserLastName');
            if (fnEl) fnEl.value = fn;
            if (lnEl) lnEl.value = ln;
            // Toggle admin password fields visibility based on current role
            const adminPwdFields = document.getElementById('adminPasswordFields');
            const np = document.getElementById('editUserNewPassword');
            const npc = document.getElementById('editUserNewPasswordConfirm');
            if (['school_admin','super_admin'].includes(userRole)) {
                adminPwdFields.classList.remove('hidden');
                if (np) np.required = true;
                if (npc) npc.required = true;
            } else {
                adminPwdFields.classList.add('hidden');
                if (np) np.required = false;
                if (npc) npc.required = false;
            }
            modal.classList.remove('hidden');
        });
    });

    // Reset Password button removed in favor of View Profile navigation

    document.querySelectorAll('.delete-user-btn').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const userId = button.dataset.userId;
            const userEmail = button.dataset.userEmail;
            const modal = document.getElementById('deleteUserModal');
            const form = document.getElementById('deleteUserForm');
            form.action = `/admin/users/${userId}`;
            const confirmInput = document.getElementById('deleteConfirmInput');
            if (confirmInput) confirmInput.placeholder = `Type DELETE or ${userEmail}`;
            modal.classList.remove('hidden');
        });
    });

    const addUserRoleSelect = document.getElementById('addUserRole');
    const bulkImportOption = document.getElementById('bulkImportOption');
    if (addUserRoleSelect) {
        addUserRoleSelect.addEventListener('change', function() {
            if (this.value === 'student') bulkImportOption.classList.remove('hidden');
            else bulkImportOption.classList.add('hidden');
        });
    }
    // Change listener for role to toggle admin password fields in edit modal
    const editRoleSelect = document.getElementById('editUserRole');
    if (editRoleSelect) {
        editRoleSelect.addEventListener('change', function() {
            const adminPwdFields = document.getElementById('adminPasswordFields');
            const np = document.getElementById('editUserNewPassword');
            const npc = document.getElementById('editUserNewPasswordConfirm');
            if (['school_admin','super_admin'].includes(this.value)) {
                adminPwdFields.classList.remove('hidden');
                if (np) np.required = true;
                if (npc) npc.required = true;
            } else {
                adminPwdFields.classList.add('hidden');
                // Clear and un-require when not needed
                if (np) { np.value = ''; np.required = false; }
                if (npc) { npc.value = ''; npc.required = false; }
            }
        });
    }
});
</script>
@endpush

</x-layoutAdmin>
