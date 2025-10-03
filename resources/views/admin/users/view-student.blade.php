{{-- resources/views/admin/users/view-student.blade.php --}}
<div class="bg-white rounded-xl shadow p-6 space-y-6">
    <div class="flex gap-6 items-start">
        @php
            $filename = $user->profile_image ? basename($user->profile_image) : null;
            $streamUrl = $filename ? route('media.profile', ['filename' => $filename]) : null;
            $assetUrl = $user->profile_image ? asset('storage/'.$user->profile_image) : null;
        @endphp
        <div class="flex flex-col items-center gap-3">
            @if($user->profile_image)
                <img src="{{ $streamUrl }}" onerror="this.onerror=null;this.src='{{ $assetUrl }}';" alt="Profile" class="w-28 h-28 rounded-full object-cover">
            @else
                <div class="w-28 h-28 rounded-full bg-green-600 text-white flex items-center justify-center text-3xl font-bold">
                    {{ strtoupper(substr($user->name,0,1)) }}
                </div>
            @endif
            <span class="px-3 py-1 text-xs rounded-full bg-green-100 text-green-700">Student</span>
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
            @if($user->program)
            <div>
                <label class="text-xs text-gray-500">Program</label>
                <div class="mt-1 p-2 rounded border bg-gray-50">{{ $user->program->name }}</div>
            </div>
            @endif
            @if($user->section)
            <div>
                <label class="text-xs text-gray-500">Section</label>
                <div class="mt-1 p-2 rounded border bg-gray-50">{{ $user->section->name }}</div>
            </div>
            @endif
            @if($user->school)
            <div>
                <label class="text-xs text-gray-500">School</label>
                <div class="mt-1 p-2 rounded border bg-gray-50">{{ $user->school->name }}</div>
            </div>
            @endif
            <div>
                <label class="text-xs text-gray-500">Phone</label>
                <div class="mt-1 p-2 rounded border bg-gray-50">{{ $user->phone ?: '—' }}</div>
            </div>
            <div>
                <label class="text-xs text-gray-500">Address</label>
                <div class="mt-1 p-2 rounded border bg-gray-50">{{ $user->address ?: '—' }}</div>
            </div>
            <div>
                <label class="text-xs text-gray-500">Last Login</label>
                <div class="mt-1 p-2 rounded border bg-gray-50">{{ $user->last_login_at ? $user->last_login_at->format('F d, Y h:i A') : '—' }}</div>
            </div>
            <div>
                <label class="text-xs text-gray-500">Account Created</label>
                <div class="mt-1 p-2 rounded border bg-gray-50">{{ $user->created_at?->format('F d, Y h:i A') }}</div>
            </div>
        </div>
    </div>

    <div class="grid sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-lg bg-green-50 border border-green-200">
            <div class="text-xs text-green-700">Enrolled Courses</div>
            <div class="text-2xl font-bold text-green-800">{{ $enrolledCount ?? ($user->courses->count()) }}</div>
        </div>
        <div class="p-4 rounded-lg bg-gray-50 border">
            <div class="text-xs text-gray-600">Status</div>
            <div class="text-2xl font-bold text-gray-800">{{ ucfirst($user->status) }}</div>
        </div>
        <div class="p-4 rounded-lg bg-gray-50 border">
            <div class="text-xs text-gray-600">Email Verified</div>
            <div class="text-2xl font-bold text-gray-800">{{ $user->email_verified_at ? 'Yes' : 'No' }}</div>
        </div>
    </div>

    @if($user->bio)
        <div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">About</h3>
            <div class="p-4 rounded border bg-gray-50 text-gray-700">{{ $user->bio }}</div>
        </div>
    @endif
</div>
