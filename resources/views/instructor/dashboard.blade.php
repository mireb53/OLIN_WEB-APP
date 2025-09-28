<x-layout>
    <div class="min-h-screen bg-gray-50">
        {{-- Header Section --}}
        <div class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        {{-- Profile Image --}}
                        <div class="flex-shrink-0">
                            <div class="relative">
                                @if(Auth::user()->profile_image)
                                    <img class="h-16 w-16 rounded-full object-cover ring-4 ring-white shadow-md" 
                                         src="{{ route('media.profile', ['filename' => basename(Auth::user()->profile_image)]) }}" 
                                         alt="{{ Auth::user()->name }}'s profile image">
                                @else
                                    <div class="h-16 w-16 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center ring-4 ring-white shadow-md">
                                        <span class="text-white text-xl font-bold">
                                            {{ substr(Auth::user()->name, 0, 1) }}
                                        </span>
                                    </div>
                                @endif
                                {{-- Online status indicator --}}
                                <div class="absolute bottom-0 right-0 h-4 w-4 bg-green-400 border-2 border-white rounded-full"></div>
                            </div>
                        </div>
                        
                        {{-- Welcome Text --}}
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Welcome back, {{ Auth::user()->name }}!</h1>
                            <div class="flex items-center space-x-2 mt-1">
                                <p class="text-gray-600">{{ now()->format('l, F j, Y') }}</p>
                                <span class="text-gray-400">•</span>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 text-blue-600 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                                    </svg>
                                    <span class="text-blue-600 font-medium text-sm">Instructor Dashboard</span>
                                </div>
                            </div>
                            {{-- Optional: Add instructor title and department if available --}}
                            @if(Auth::user()->title || Auth::user()->department)
                                <div class="mt-1">
                                    <p class="text-sm text-gray-500">
                                        @if(Auth::user()->title)
                                            {{ Auth::user()->title }}
                                        @endif
                                        @if(Auth::user()->title && Auth::user()->department)
                                            •
                                        @endif
                                        @if(Auth::user()->department)
                                            {{ Auth::user()->department }}
                                        @endif
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    {{-- Quick Actions --}}
                    <div class="flex items-center space-x-3">                     
                        {{-- Profile Link --}}
                        <a href="{{ route('instructor.showProfile') }}" 
                           class="p-2 text-gray-400 hover:text-gray-600 transition-colors duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            {{-- Stats Overview --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                {{-- Active Courses --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Active Courses</p>
                            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $courses->where('status', 'published')->count() }}</p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.405 9.176 5 7 5 4.823 5 3.168 5.405 2 6.253v13C3.168 18.595 4.823 18 7 18c2.176 0 3.832.405 5 1.253m0-13C13.168 5.405 14.823 5 17 5c2.176 0 3.832.405 5 1.253v13C20.832 18.595 19.176 18 17 18c-2.177 0-3.832.405-5 1.253"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <span class="text-green-600 font-medium">{{ $courses->where('status', 'draft')->count() }} drafts</span>
                        <span class="text-gray-500 ml-2">waiting to publish</span>
                    </div>
                </div>

                {{-- Total Students --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Students</p>
                            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalStudents }}</p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <span class="text-green-600 font-medium">+18 new</span>
                        <span class="text-gray-500 ml-2">this week</span>
                    </div>
                </div>

                {{-- Pending Grading --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Pending Grading</p>
                            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $pendingGrading }}</p>
                        </div>
                        <div class="bg-orange-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        @if($urgentSubmissions > 0)
                            <span class="text-red-600 font-medium">{{ $urgentSubmissions }} urgent</span>
                            <span class="text-gray-500 ml-2">{{ $urgentSubmissions === 1 ? 'submission needs immediate grading' : 'submissions need immediate grading' }}</span>
                        @elseif($pendingGrading > 0)
                            <span class="text-orange-600 font-medium">{{ $pendingGrading }} awaiting</span>
                            <span class="text-gray-500 ml-2">{{ $pendingGrading === 1 ? 'submission to grade' : 'submissions to grade' }}</span>
                        @else
                            <span class="text-green-600 font-medium">All graded</span>
                            <span class="text-gray-500 ml-2">no submissions awaiting grades</span>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Classes This Week</p>
                            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $classesThisWeek }}</p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h.01M9 11h.01M13 11h.01M15 11h.01M5 15h.01M7 15h.01M11 15h.01M13 15h.01M17 15h.01M19 15h.01M5 19h.01M7 19h.01M11 19h.01M13 19h.01M17 19h.01M19 19h.01"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-col text-sm space-y-1">
                        <span class="text-purple-600 font-medium">
                            {{ $classesToday === 1 ? '1 class scheduled today' : $classesToday . ' classes scheduled today' }}
                        </span>
                        <span class="text-gray-500">
                            {{ $classesThisWeek === 1 ? '1 class scheduled this week' : $classesThisWeek . ' classes scheduled this week' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Additional Analytics Row --}}
            <div class="grid grid-cols-1 md:grid-cols-1 gap-1 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-600">Course Materials</h3>
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    {{-- Updated grid to display three columns --}}
                    <div class="grid grid-cols-3 gap-4">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-900">{{ $videoCount }}</p>
                            <p class="text-xs text-gray-600">Videos</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-900">{{ $documentCount }}</p>
                            <p class="text-xs text-gray-600">Documents</p>
                        </div>
                        <div class="text-center">
                            {{-- New section for other file types --}}
                            <p class="text-2xl font-bold text-gray-900">{{ $otherFileCount }}</p>
                            <p class="text-xs text-gray-600">Other Files</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Content Grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- My Courses Section (Take 2 columns on large screens) --}}
                <div class="lg:col-span-2">
                    @if ($courses->isEmpty())
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
                            <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.405 9.176 5 7 5 4.823 5 3.168 5.405 2 6.253v13C3.168 18.595 4.823 18 7 18c2.176 0 3.832.405 5 1.253m0-13C13.168 5.405 14.823 5 17 5c2.176 0 3.832.405 5 1.253v13C20.832 18.595 19.176 18 17 18c-2.177 0-3.832.405-5 1.253"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Start Creating Your First Course</h3>
                            <p class="text-gray-600 mb-6 max-w-md mx-auto">You haven't created any courses yet. Begin your teaching journey by creating your first course and sharing knowledge with students.</p>
                            <a href="{{ route('course.create') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Create Your First Course
                            </a>
                        </div>
                    @else
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                                <h2 class="text-xl font-semibold text-gray-900">My Courses</h2>
                                <a href="{{ route('course.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center space-x-2 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    <span>Create New Course</span>
                                </a>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                                    @foreach ($courses as $course)
                                        <a href="{{ route('courses.show', $course->id) }}" class="group block">
                                            <div class="bg-gray-50 rounded-lg border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all duration-200 overflow-hidden">
                                                {{-- Course Header --}}
                                                <div class="bg-gradient-to-r from-blue-500 to-purple-600 h-20 relative">
                                                    <div class="absolute top-3 right-3">
                                                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                                                            @if($course->status === 'published') bg-green-100 text-green-800
                                                            @elseif($course->status === 'draft') bg-yellow-100 text-yellow-800
                                                            @else bg-red-100 text-red-800
                                                            @endif">
                                                            {{ ucfirst($course->status) }}
                                                        </span>
                                                    </div>
                                                </div>

                                                {{-- Course Content --}}
                                                <div class="p-6">
                                                    <h3 class="text-lg font-semibold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">
                                                        {{ $course->title }}
                                                    </h3>
                                                    <p class="text-sm text-gray-600 mb-3">
                                                        {{ $course->course_code }}
                                                        @if($course->program)
                                                            â€¢ {{ $course->program->name }}
                                                        @endif
                                                    </p>
                                                    <p class="text-gray-700 text-sm leading-relaxed mb-4 line-clamp-2">
                                                        {{ Str::limit($course->description ?: 'No description provided for this course.', 80) }}
                                                    </p>

                                                    {{-- Course Stats --}}
                                                    <div class="flex items-center justify-between text-sm text-gray-500">
                                                        <div class="flex items-center space-x-4">
                                                            <span class="flex items-center">
                                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                                </svg>
                                                                {{ $course->students->count() }} students
                                                            </span>
                                                            <span class="flex items-center">
                                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                                </svg>
                                                                {{ $course->credits ?: 3 }} credits
                                                            </span>
                                                        </div>
                                                        <span class="text-blue-600 text-xs">View â†'</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">

                    {{-- Recent Activity --}}
                    {{-- <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="flex items-start space-x-3">
                                    <div class="bg-blue-100 p-2 rounded-full flex-shrink-0">
                                        <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs text-gray-900"><span class="font-medium">Sarah Johnson</span> submitted assignment</p>
                                        <p class="text-xs text-gray-500">Web Development â€¢ 15 min ago</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <div class="bg-green-100 p-2 rounded-full flex-shrink-0">
                                        <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs text-gray-900">You graded 8 assignments</p>
                                        <p class="text-xs text-gray-500">Database Design â€¢ 2 hours ago</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <div class="bg-yellow-100 p-2 rounded-full flex-shrink-0">
                                        <svg class="w-3 h-3 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs text-gray-900">3 new discussion posts</p>
                                        <p class="text-xs text-gray-500">Programming Logic â€¢ 4 hours ago</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <div class="bg-purple-100 p-2 rounded-full flex-shrink-0">
                                        <svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs text-gray-900">New module published</p>
                                        <p class="text-xs text-gray-500">Advanced JavaScript • Yesterday</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> --}}

                    {{-- Upcoming Deadlines --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Assessment with Deadlines</h3>
                        </div>
                        <div class="p-6">
                            @if ($upcomingAssessments->isEmpty())
                                <p class="text-sm text-gray-500 text-center">No upcoming deadlines.</p>
                            @else
                                {{-- Scrollable container with fixed height --}}
                                <div class="space-y-4 max-h-80 overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
                                    @foreach ($upcomingAssessments as $assessment)
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $assessment->title }}</p>
                                                <p class="text-xs text-gray-500">{{ $assessment->course->title }}</p>
                                            </div>
                                            @php
                                                $deadline = $assessment->unavailable_at;
                                                $now = now();
                                                $diffInDays = (int) $now->diffInDays($deadline, false); // Cast to integer to ensure whole number
                                                $deadlineText = '';
                                                $colorClass = '';

                                                if ($diffInDays === 0) {
                                                    $deadlineText = 'Today';
                                                    $colorClass = 'bg-red-100 text-red-800';
                                                } elseif ($diffInDays === 1) {
                                                    $deadlineText = 'Tomorrow';
                                                    $colorClass = 'bg-yellow-100 text-yellow-800';
                                                } elseif ($diffInDays > 1 && $diffInDays <= 7) {
                                                    $deadlineText = 'In ' . $diffInDays . ' days';
                                                    $colorClass = 'bg-yellow-100 text-yellow-800';
                                                } elseif ($diffInDays > 7) {
                                                    $deadlineText = $deadline->format('M j, Y');
                                                    $colorClass = 'bg-gray-100 text-gray-700';
                                                } elseif ($diffInDays === -1) {
                                                    $deadlineText = 'Yesterday';
                                                    $colorClass = 'bg-gray-100 text-gray-700';
                                                } elseif ($diffInDays < -1) {
                                                    $deadlineText = abs($diffInDays) . ' days ago';
                                                    $colorClass = 'bg-gray-100 text-gray-700';
                                                }
                                            @endphp
                                            <span class="text-xs px-2 py-1 rounded-full font-medium {{ $colorClass }}">
                                                {{ $deadlineText }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>