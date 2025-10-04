{{-- resources/views/admin/users/view-instructor.blade.php --}}
<div class="bg-white rounded-2xl shadow-md p-8 space-y-8">

    <!-- Profile Section -->
    <div class="flex flex-col md:flex-row gap-8 items-center md:items-start">
        @php
            $filename = $user->profile_image ? basename($user->profile_image) : null;
            $streamUrl = $filename ? route('media.profile', ['filename' => $filename]) : null;
            $assetUrl = $user->profile_image ? asset('storage/'.$user->profile_image) : null;
        @endphp

        <!-- Avatar -->
        <div class="flex flex-col items-center gap-4">
            @if($user->profile_image)
                <img src="{{ $streamUrl }}" onerror="this.onerror=null;this.src='{{ $assetUrl }}';"
                     alt="Profile"
                     class="w-32 h-32 rounded-full object-cover ring-4 ring-indigo-100 shadow">
            @else
                <div class="w-32 h-32 rounded-full bg-indigo-600 text-white flex items-center justify-center text-4xl font-bold shadow">
                    {{ strtoupper(substr($user->name,0,1)) }}
                </div>
            @endif
            <span class="px-3 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-700 shadow-sm">
                Instructor
            </span>
        </div>

        <!-- Info Grid -->
        <div class="grid md:grid-cols-2 gap-6 flex-1 w-full">
            <div>
                <label class="text-xs uppercase tracking-wide text-gray-500">Full Name</label>
                <div class="mt-1 p-3 rounded-lg bg-gray-50 border text-gray-800 font-medium">
                    {{ $user->display_name ?? $user->name }}
                </div>
            </div>
            <div>
                <label class="text-xs uppercase tracking-wide text-gray-500">Email</label>
                <div class="mt-1 p-3 rounded-lg bg-gray-50 border text-gray-800">
                    {{ $user->email }}
                </div>
            </div>
            @if($user->title)
            <div>
                <label class="text-xs uppercase tracking-wide text-gray-500">Title</label>
                <div class="mt-1 p-3 rounded-lg bg-gray-50 border text-gray-800">{{ $user->title }}</div>
            </div>
            @endif
            @if($user->department)
            <div>
                <label class="text-xs uppercase tracking-wide text-gray-500">Department</label>
                <div class="mt-1 p-3 rounded-lg bg-gray-50 border text-gray-800">{{ $user->department }}</div>
            </div>
            @endif
            @if($user->school)
            <div>
                <label class="text-xs uppercase tracking-wide text-gray-500">School</label>
                <div class="mt-1 p-3 rounded-lg bg-gray-50 border text-gray-800">{{ $user->school->name }}</div>
            </div>
            @endif
            <div>
                <label class="text-xs uppercase tracking-wide text-gray-500">Phone</label>
                <div class="mt-1 p-3 rounded-lg bg-gray-50 border text-gray-800">{{ $user->phone ?: '—' }}</div>
            </div>
            <div>
                <label class="text-xs uppercase tracking-wide text-gray-500">Address</label>
                <div class="mt-1 p-3 rounded-lg bg-gray-50 border text-gray-800">{{ $user->address ?: '—' }}</div>
            </div>
            <div>
                <label class="text-xs uppercase tracking-wide text-gray-500">Last Login</label>
                <div class="mt-1 p-3 rounded-lg bg-gray-50 border text-gray-800">
                    {{ $user->last_login_at ? $user->last_login_at->format('F d, Y h:i A') : '—' }}
                </div>
            </div>
            <div>
                <label class="text-xs uppercase tracking-wide text-gray-500">Account Created</label>
                <div class="mt-1 p-3 rounded-lg bg-gray-50 border text-gray-800">
                    {{ $user->created_at?->format('F d, Y h:i A') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Biography -->
    <div>
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Biography</h3>
        <div class="p-5 rounded-lg bg-gray-50 border text-gray-700 leading-relaxed shadow-sm min-h-[80px]">
            {{ $user->bio ?: 'No biography provided.' }}
        </div>
    </div>

    <!-- Stats (Three Horizontal Cards in One Row) -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    <!-- Courses Taught -->
    <div class="bg-indigo-50 border border-indigo-200 rounded-xl shadow-sm p-6">
        <div class="text-sm font-medium text-indigo-700">Courses Taught</div>
        <div class="mt-2 text-3xl font-bold text-indigo-900">
            {{ $taughtCount ?? ($user->taughtCourses->count()) }}
        </div>
        <p class="text-xs text-indigo-600 mt-1">Total assigned courses</p>
    </div>

    <!-- Status -->
    <div class="bg-green-50 border border-green-200 rounded-xl shadow-sm p-6">
        <div class="text-sm font-medium text-green-700">Status</div>
        <div class="mt-2 text-3xl font-bold text-green-900">
            {{ ucfirst($user->status) }}
        </div>
        <p class="text-xs text-green-600 mt-1">Account status</p>
    </div>

    <!-- Email Verified -->
    <div class="bg-gray-50 border border-gray-200 rounded-xl shadow-sm p-6">
        <div class="text-sm font-medium text-gray-600">Email Verified</div>
        <div class="mt-2 text-3xl font-bold text-gray-900">
            {{ $user->email_verified_at ? 'Yes' : 'No' }}
        </div>
        <p class="text-xs text-gray-500 mt-1">Verification status</p>
    </div>

</div>



