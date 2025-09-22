<x-layoutAdmin>
    <main class="flex-1 overflow-y-auto p-4 md:p-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900">Dashboard</h1>
            <p class="mt-2 text-slate-500 text-lg">Welcome back, {{ Auth::user()->name }}! Here's a quick overview of the system.</p>
        </div>

        {{-- School Context Banner --}}
        @if(Auth::user()->isSuperAdmin())
            @if(isset($needsSchoolSelection) && $needsSchoolSelection && $availableSchools->count() > 0)
                {{-- School Selection Required --}}
                <div class="mb-8 p-6 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="text-center">
                        <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h3 class="text-yellow-800 font-semibold text-lg mb-2">Please Select a School</h3>
                        <p class="text-yellow-600 text-sm mb-4">Choose which school's dashboard you'd like to view.</p>
                        
                        <form action="{{ route('admin.settings.select-school') }}" method="POST" class="inline-flex items-center gap-3">
                            @csrf
                            <select name="school_id" required class="py-2 px-4 border border-yellow-300 rounded-md bg-white focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                <option value="">-- Select School --</option>
                                @foreach($availableSchools as $school)
                                    <option value="{{ $school->id }}">{{ $school->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 transition-colors">
                                View Dashboard
                            </button>
                        </form>
                        
                        <div class="mt-4">
                            <a href="{{ route('admin.settings') }}" class="text-yellow-600 hover:text-yellow-800 text-sm underline">
                                Or manage schools in Settings
                            </a>
                        </div>
                    </div>
                </div>
            @elseif($activeSchool)
                {{-- Active School Selected --}}
                <div class="mb-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35.524 9.027 9.027 0 00-.4.04z"/></svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-blue-800 font-semibold">Dashboard for: {{ $activeSchool->name }}</h3>
                            <p class="text-blue-600 text-sm">All statistics and activities below are filtered for this school</p>
                        </div>
                        <div class="ml-auto flex items-center gap-3">
                            @if($availableSchools->count() > 1)
                                <form action="{{ route('admin.settings.select-school') }}" method="POST" class="inline-flex items-center gap-2">
                                    @csrf
                                    <select name="school_id" onchange="this.form.submit()" class="text-sm py-1 px-2 border border-blue-300 rounded-md bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                                        @foreach($availableSchools as $school)
                                            <option value="{{ $school->id }}" {{ $activeSchool && $activeSchool->id == $school->id ? 'selected' : '' }}>
                                                {{ $school->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            @endif
                            <a href="{{ route('admin.settings') }}" class="text-blue-600 hover:text-blue-800 text-sm underline">Manage Schools</a>
                        </div>
                    </div>
                </div>
            @endif
        @elseif(Auth::user()->isSchoolAdmin() && $activeSchool)
            {{-- School Admin School Banner --}}
            <div class="mb-8 p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35.524 9.027 9.027 0 00-.4.04z"/></svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-green-800 font-semibold">Your School: {{ $activeSchool->name }}</h3>
                        <p class="text-green-600 text-sm">School Administrator Dashboard</p>
                    </div>
                </div>
            </div>
        @elseif(Auth::user()->isSchoolAdmin() && !$activeSchool)
            {{-- School Admin without assigned school --}}
            <div class="mb-8 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-red-800 font-semibold">No School Assigned</h3>
                        <p class="text-red-600 text-sm">Please contact the Super Admin to assign you to a school.</p>
                    </div>
                </div>
            </div>
        @endif

        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="flex flex-col items-center justify-center bg-white rounded-2xl p-6 shadow-lg border border-gray-200 hover:shadow-2xl hover:-translate-y-1 transition-transform duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-indigo-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14c3.866 0 7 1.567 7 3.5V20H5v-2.5C5 15.567 8.134 14 12 14zM12 12a4 4 0 100-8 4 4 0 000 8z"/></svg>
                <div class="text-slate-500 mb-2 font-medium uppercase tracking-wide">Instructors</div>
                <div class="text-5xl font-extrabold text-slate-900">{{ $stats['total_instructors'] ?? 0 }}</div>
            </div>
            <div class="flex flex-col items-center justify-center bg-white rounded-2xl p-6 shadow-lg border border-gray-200 hover:shadow-2xl hover:-translate-y-1 transition-transform duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-green-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                <div class="text-slate-500 mb-2 font-medium uppercase tracking-wide">Students</div>
                <div class="text-5xl font-extrabold text-slate-900">{{ $stats['total_students'] ?? 0 }}</div>
            </div>
            <div class="flex flex-col items-center justify-center bg-white rounded-2xl p-6 shadow-lg border border-gray-200 hover:shadow-2xl hover:-translate-y-1 transition-transform duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-amber-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <div class="text-slate-500 mb-2 font-medium uppercase tracking-wide">Active Courses</div>
                <div class="text-5xl font-extrabold text-slate-900">{{ $stats['active_courses'] ?? 0 }}</div>
            </div>
            <div class="flex flex-col items-center justify-center bg-white rounded-2xl p-6 shadow-lg border border-gray-200 hover:shadow-2xl hover:-translate-y-1 transition-transform duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-red-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                <div class="text-slate-500 mb-2 font-medium uppercase tracking-wide">Assessments</div>
                <div class="text-5xl font-extrabold text-slate-900">{{ $stats['total_assessments'] ?? 0 }}</div>
            </div>
        </section>

        @php
            $recentUsers = collect($recentActivities ?? [])->where('type','user_registration')->take(5);
            $recentCourses = collect($recentActivities ?? [])->where('type','course_creation')->take(5);
        @endphp

        <section class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
                <h2 class="text-xl font-bold text-slate-900 mb-6">Recent User Registrations</h2>
                <div class="space-y-4">
                    @forelse($recentUsers as $activity)
                        <div class="flex items-center p-3 bg-slate-50 rounded-lg border border-slate-100">
                            <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-slate-900">{{ $activity['description'] }}</h3>
                                <p class="text-sm text-slate-500">{{ $activity['time']->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-slate-500 text-center py-8">No recent user registrations</p>
                    @endforelse
                </div>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
                <h2 class="text-xl font-bold text-slate-900 mb-6">Recent Course Activity</h2>
                <div class="space-y-4">
                    @forelse($recentCourses as $activity)
                        <div class="flex items-center p-3 bg-slate-50 rounded-lg border border-slate-100">
                            <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-slate-900">{{ $activity['description'] }}</h3>
                                <p class="text-sm text-slate-500">{{ $activity['time']->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-slate-500 text-center py-8">No recent course activity</p>
                    @endforelse
                </div>
            </div>
        </section>
    </main>
</x-layoutAdmin>
