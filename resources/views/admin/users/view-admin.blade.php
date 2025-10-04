{{-- resources/views/admin/users/view-admin.blade.php --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <div class="flex flex-col md:flex-row gap-8 items-center md:items-start">
        
        {{-- Profile Picture / Initial --}}
        <div class="flex flex-col items-center gap-3">
            @php
                $filename = $user->profile_image ? basename($user->profile_image) : null;
                $streamUrl = $filename ? route('media.profile', ['filename' => $filename]) : null;
                $assetUrl = $user->profile_image ? asset('storage/'.$user->profile_image) : null;
            @endphp

            @if($user->profile_image)
                <img src="{{ $streamUrl }}" 
                     onerror="this.onerror=null;this.src='{{ $assetUrl }}';"
                     alt="Profile" 
                     class="w-32 h-32 rounded-full object-cover shadow-md border-4 border-indigo-100">
            @else
                <div class="w-32 h-32 rounded-full bg-gradient-to-br from-indigo-600 to-indigo-400 text-white flex items-center justify-center text-4xl font-semibold shadow-md">
                    {{ strtoupper(substr($user->name,0,1)) }}
                </div>
            @endif

            <span class="px-3 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-700 shadow-sm">
                {{ $user->role === 'super_admin' ? 'Super Admin' : 'School Admin' }}
            </span>
        </div>

        {{-- Profile Details --}}
        <div class="flex-1 w-full">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">User Information</h2>
            <div class="grid sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-medium text-gray-500">Full Name</label>
                    <p class="mt-1 px-3 py-2 rounded-lg bg-gray-50 border text-gray-800">{{ $user->display_name ?? $user->name }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Email</label>
                    <p class="mt-1 px-3 py-2 rounded-lg bg-gray-50 border text-gray-800">{{ $user->email }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Status</label>
                    <p class="mt-1 px-3 py-2 rounded-lg bg-gray-50 border text-gray-800">
                        <span class="px-2 py-1 rounded-full text-xs font-medium 
                            {{ $user->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ ucfirst($user->status) }}
                        </span>
                    </p>
                </div>
                @if($user->school)
                    <div>
                        <label class="block text-xs font-medium text-gray-500">School</label>
                        <p class="mt-1 px-3 py-2 rounded-lg bg-gray-50 border text-gray-800">{{ $user->school->name }}</p>
                    </div>
                @endif
                <div>
                    <label class="block text-xs font-medium text-gray-500">Last Login</label>
                    <p class="mt-1 px-3 py-2 rounded-lg bg-gray-50 border text-gray-800">
                        {{ $user->last_login_at ? $user->last_login_at->format('F d, Y h:i A') : '—' }}
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Account Created</label>
                    <p class="mt-1 px-3 py-2 rounded-lg bg-gray-50 border text-gray-800">
                        {{ $user->created_at?->format('F d, Y h:i A') }}
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Phone</label>
                    <p class="mt-1 px-3 py-2 rounded-lg bg-gray-50 border text-gray-800">{{ $user->phone ?: '—' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Address</label>
                    <p class="mt-1 px-3 py-2 rounded-lg bg-gray-50 border text-gray-800">{{ $user->address ?: '—' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
