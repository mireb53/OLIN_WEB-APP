{{-- resources/views/admin/users/show.blade.php --}}
<x-layoutAdmin>
    <main class="flex-1 p-6 bg-gray-50 min-h-screen">
        @php
            $role = $user->role;
            $backRole = in_array($user->role, ['super_admin','school_admin']) ? 'admin' : $user->role;
        @endphp

        {{-- Header --}}
        <div class="mb-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">User Profile</h1>
                <p class="text-sm text-gray-500">Manage user information and roles</p>
            </div>
            <a href="{{ route('admin.user_management', ['role' => $backRole]) }}"
               class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium rounded-lg bg-white border border-gray-200 text-gray-700 hover:bg-gray-100 shadow-sm transition">
                ← Back
            </a>
        </div>

        {{-- Alerts --}}
        @if (session('status'))
            <div class="mb-4 flex items-center gap-2 p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
                ✅ {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>⚠️ {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- User Profile --}}
        <div class="max-w-6xl mx-auto">
            @switch($role)
                @case('super_admin')
                @case('school_admin')
                    @include('admin.users.view-admin', ['user' => $user])
                    @break
                @case('instructor')
                    @include('admin.users.view-instructor', ['user' => $user, 'taughtCount' => $taughtCount ?? 0])
                    @break
                @case('student')
                    @include('admin.users.view-student', ['user' => $user, 'enrolledCount' => $enrolledCount ?? 0])
                    @break
                @default
                    <div class="p-6 bg-white rounded-xl shadow text-gray-600">
                        No viewer available for this role.
                    </div>
            @endswitch
        </div>
    </main>
</x-layoutAdmin>
