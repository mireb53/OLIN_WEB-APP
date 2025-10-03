{{-- resources/views/admin/users/view-admin.blade.php --}}
<div class="bg-white rounded-xl shadow p-6">
    <div class="flex gap-6 items-start">
        <div class="flex flex-col items-center gap-3">
            @php
                $filename = $user->profile_image ? basename($user->profile_image) : null;
                $streamUrl = $filename ? route('media.profile', ['filename' => $filename]) : null;
                $assetUrl = $user->profile_image ? asset('storage/'.$user->profile_image) : null;
            @endphp
            @if($user->profile_image)
                <img src="{{ $streamUrl }}" onerror="this.onerror=null;this.src='{{ $assetUrl }}';" alt="Profile" class="w-28 h-28 rounded-full object-cover">
            @else
                <div class="w-28 h-28 rounded-full bg-indigo-600 text-white flex items-center justify-center text-3xl font-bold">
                    {{ strtoupper(substr($user->name,0,1)) }}
                </div>
            @endif
            <span class="px-3 py-1 text-xs rounded-full bg-red-100 text-red-700">Administrator</span>
        </div>

        <div class="grid md:grid-cols-2 gap-4 flex-1">
            <div>
                <label class="text-xs text-gray-500">Full Name</label>
                <div class="mt-1 p-2 rounded border bg-gray-50">{{ $user->display_name ?? $user->name }}</div>
            </div>
            <div>
                <label class="text-xs text-gray-500">Email</label>
                <div class="mt-1 p-2 rounded border bg-gray-50">{{ $user->email }}</div>
            </div>
            <div>
                <label class="text-xs text-gray-500">Admin Role</label>
                <div class="mt-1 p-2 rounded border bg-gray-50">{{ $user->role === 'super_admin' ? 'SuperAdmin' : 'SchoolAdmin' }}</div>
            </div>
            <div>
                <label class="text-xs text-gray-500">Status</label>
                <div class="mt-1 p-2 rounded border bg-gray-50">{{ ucfirst($user->status) }}</div>
            </div>
            @if($user->school)
            <div>
                <label class="text-xs text-gray-500">School</label>
                <div class="mt-1 p-2 rounded border bg-gray-50">{{ $user->school->name }}</div>
            </div>
            @endif
            <div>
                <label class="text-xs text-gray-500">Last Login</label>
                <div class="mt-1 p-2 rounded border bg-gray-50">{{ $user->last_login_at ? $user->last_login_at->format('F d, Y h:i A') : '—' }}</div>
            </div>
            <div>
                <label class="text-xs text-gray-500">Account Created</label>
                <div class="mt-1 p-2 rounded border bg-gray-50">{{ $user->created_at?->format('F d, Y h:i A') }}</div>
            </div>
            <div>
                <label class="text-xs text-gray-500">Phone</label>
                <div class="mt-1 p-2 rounded border bg-gray-50">{{ $user->phone ?: '—' }}</div>
            </div>
            <div>
                <label class="text-xs text-gray-500">Address</label>
                <div class="mt-1 p-2 rounded border bg-gray-50">{{ $user->address ?: '—' }}</div>
            </div>
        </div>
    </div>
</div>
