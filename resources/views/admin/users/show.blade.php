{{-- resources/views/admin/users/show.blade.php --}}
<x-layoutAdmin>
    <main class="flex-1 p-6">
    @php
        $role = $user->role;
    @endphp

    @php
        $backRole = in_array($user->role, ['super_admin','school_admin']) ? 'admin' : $user->role;
    @endphp
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">User Profile</h1>
        <a href="{{ route('admin.user_management', ['role' => $backRole]) }}" class="px-3 py-1.5 text-sm rounded bg-gray-100 text-gray-700 hover:bg-gray-200">← Back</a>
    </div>
    <p class="text-gray-600 mb-4">Viewing profile for: <strong>{{ $user->display_name ?? $user->name }}</strong> <span class="ml-2 px-2 py-0.5 text-xs rounded bg-gray-100 text-gray-700">{{ ucfirst(str_replace('_',' ', $user->role)) }}</span></p>

    @if (session('status'))
        <div class="mb-4 p-3 rounded bg-green-50 border border-green-200 text-green-800">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-4 p-3 rounded bg-red-50 border border-red-200 text-red-700">
            <ul class="list-disc pl-5 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="max-w-6xl mx-auto">
        {{-- Choose role-specific profile presentation --}}
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
                <div class="p-6 bg-white rounded shadow">No viewer available for this role.</div>
        @endswitch
    </div>

    </main>
</x-layoutAdmin>
