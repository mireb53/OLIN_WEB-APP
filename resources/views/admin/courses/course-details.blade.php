<x-layoutAdmin>
{{-- Responsive full-width container like course-management --}}
<main class="flex-1 overflow-y-auto p-4 md:p-8">
        
        {{-- Header Section with Course Info (Refined layout) --}}
        <div class="relative overflow-hidden rounded-2xl mb-8 bg-gradient-to-br from-indigo-50 via-white to-indigo-50 border border-indigo-100 shadow-sm">
            <div class="absolute inset-0 pointer-events-none opacity-40" style="background-image: radial-gradient(circle at 20% 20%, rgba(99,102,241,0.15), transparent 60%), radial-gradient(circle at 80% 60%, rgba(59,130,246,0.12), transparent 55%);"></div>
            <div class="relative p-6 md:p-8">
                <div class="flex flex-col gap-6">
                    <div class="flex flex-col md:flex-row md:items-start gap-4 md:gap-6">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <h1 class="text-3xl font-bold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-blue-600">
                                        {{ $course->title }}
                                    </h1>
                                    <div class="mt-2 flex flex-wrap items-center gap-3 text-sm">
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-white/70 backdrop-blur border border-indigo-100 text-indigo-700 font-medium font-mono text-xs">{{ $course->course_code ?? 'N/A' }}</span>
                                        <span class="text-gray-400">•</span>
                                        <span class="text-gray-700">{{ $course->program ? $course->program->name : 'N/A' }}</span>
                                    </div>
                                </div>
                                <div class="shrink-0 hidden md:block">
                                    <a href="{{ route('admin.courseManagement') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-medium bg-white text-gray-700 border border-indigo-200 shadow-sm hover:shadow-md hover:border-indigo-300 hover:bg-indigo-50 transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                                        Back to Courses
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="md:hidden">
                            <a href="{{ route('admin.courseManagement') }}" class="inline-flex w-full justify-center items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium bg-white text-gray-700 border border-indigo-200 shadow-sm hover:bg-indigo-50 transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                                Back to Courses
                            </a>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-white/70 backdrop-blur border border-indigo-100 hover:border-indigo-200 transition-colors">
                            <div class="p-2 rounded-lg bg-indigo-100 text-indigo-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Instructor</p>
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $course->instructor ? $course->instructor->name : 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-white/70 backdrop-blur border border-indigo-100 hover:border-indigo-200 transition-colors">
                            <div class="p-2 rounded-lg bg-indigo-100 text-indigo-600">
                                {{-- Credits (Academic Cap) Icon --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422A12.083 12.083 0 0112 21.5a12.083 12.083 0 01-6.16-10.922L12 14z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Credits</p>
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $course->credits ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-white/70 backdrop-blur border border-indigo-100 hover:border-indigo-200 transition-colors">
                            <div class="p-2 rounded-lg bg-indigo-100 text-indigo-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.007 12.007 0 002.944 12a12.007 12.007 0 002.438 6.956A11.955 11.955 0 0112 21.056a11.955 11.955 0 018.618-3.04A12.007 12.007 0 0021.056 12a12.007 12.007 0 00-2.438-6.956z" /></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Status</p>
                                <p class="text-sm font-medium">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $course->status === 'published' ? 'bg-green-100 text-green-700' : ($course->status === 'draft' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600') }}">
                                        {{ ucfirst($course->status) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-white/70 backdrop-blur border border-indigo-100 hover:border-indigo-200 transition-colors">
                            <div class="p-2 rounded-lg bg-indigo-100 text-indigo-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h.01M9 16h.01M13 16h.01M15 16h.01M17 12h.01M7 12h.01M12 12h.01M3 21h18M3 5h18a2 2 0 012 2v14a2 2 0 01-2 2H3a2 2 0 01-2-2V7a2 2 0 012-2z" /></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Created</p>
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $course->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 md:mt-2 flex flex-wrap gap-3">
                    {{-- Placeholder for potential future actions (edit, publish, etc.) --}}
                </div>
            </div>
        </div>

        {{-- Navigation Cards --}}
        <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-4">Course Management Areas</h2>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6 mb-8">
            @php
                $cardNavs = [
                    ['id' => 'overview', 'name' => 'Overview', 'description' => 'View Curriculum Overview.', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>'],
                    ['id' => 'topics', 'name' => 'Topics', 'description' => 'View List of Curriculum Topics.', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>'],
                    ['id' => 'materials', 'name' => 'Materials', 'description' => 'View Uploaded Resources (PDFs, etc.).', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2h2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v2M7 7h10" /></svg>'],
                    ['id' => 'assessments', 'name' => 'Assessments', 'description' => 'View Quizzes, Exams, etc.', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>'],
                    ['id' => 'students', 'name' => 'Students', 'description' => 'View Enrolled Students.', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-5-4h-1M9 20H4v-2a4 4 0 015-4h1m8-6a4 4 0 11-8 0 4 4 0 018 0z" /></svg>']
                ];
            @endphp

            @foreach($cardNavs as $nav)
                <button 
                    data-tab="{{ $nav['id'] }}" 
                    class="tab-btn card-nav-btn flex flex-col items-start p-6 rounded-xl transition duration-300 text-left border-2 border-gray-100 hover:border-indigo-400 transform hover:scale-[1.02] bg-gray-50 shadow-lg hover:shadow-xl hover:shadow-indigo-100"
                >
                    <div class="p-3 rounded-full mb-4 bg-indigo-500 text-white shadow-md">
                        {!! $nav['icon'] !!}
                    </div>
                    <h3 class="text-xl font-semibold mb-2 text-indigo-600">
                        {{ $nav['name'] }}
                    </h3>
                    <p class="text-sm text-gray-500">
                        {{ $nav['description'] }}
                    </p>
                </button>
            @endforeach
        </div>
        
        {{-- Content Sections --}}
        <!-- Overview Section -->
        <div id="overview-section" class="content-section mb-10">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Course Description</h2>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 min-h-[260px] flex">
                <div id="overview" class="tab-panel flex-1">
                    <p class="text-gray-700 leading-relaxed max-w-3xl">{{ $course->description ?? 'No description provided.' }}</p>
                </div>
            </div>
        </div>

        <!-- Topics Section -->
        <div id="topics-section" class="content-section hidden mb-10">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Topics</h2>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 min-h-[420px] flex flex-col">
                <div id="topics" class="tab-panel">
                    <div id="topics-container" class="grid md:grid-cols-2 gap-4 flex-1">
                        @forelse($course->topics as $index => $topic)
                            <div class="paginated-item p-4 bg-gray-50 rounded-lg flex items-center gap-4 border border-gray-100 shadow-sm transition-shadow duration-200 hover:shadow-md" data-index="{{ $loop->index }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v5a1 1 0 102 0V7zM9 13a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd" /></svg>
                                <span class="text-gray-800 font-medium">{{ $topic->name }}</span>
                            </div>
                        @empty
                            <div class="italic text-gray-500 p-4">No topics found.</div>
                        @endforelse
                    </div>
                    <div class="mt-6 flex justify-end"><div id="topics-pagination" class="pagination flex gap-2"></div></div>
                </div>
            </div>
        </div>

        <!-- Materials Section -->
        <div id="materials-section" class="content-section hidden mb-10">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Materials</h2>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 min-h-[420px] flex flex-col">
                <div id="materials" class="tab-panel">
                    <div id="materials-container" class="grid md:grid-cols-2 gap-4 flex-1">
                        @forelse($course->materials as $index => $m)
                            <div class="paginated-item p-5 bg-gray-50 rounded-lg flex items-center justify-between border border-gray-100 shadow-sm transition-shadow duration-200 hover:shadow-md" data-index="{{ $loop->index }}">
                                <div class="flex items-center gap-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    <div>
                                        <div class="font-bold text-gray-900">{{ $m->title }}</div>
                                        <div class="text-xs text-gray-500 mt-1 font-mono">{{ $m->material_type }}</div>
                                    </div>
                                </div>
                                <div>
                                    @if($m->file_path)
                                        <a href="{{ route('admin.materials.view', $m->id) }}" target="_blank" rel="noopener" class="text-blue-600 hover:text-blue-800 transition-colors font-bold">Open &rarr;</a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="italic text-gray-500 p-5">No materials found.</div>
                        @endforelse
                    </div>
                    <div class="mt-6 flex justify-end"><div id="materials-pagination" class="pagination flex gap-2"></div></div>
                </div>
            </div>
        </div>

        <!-- Assessments Section -->
        <div id="assessments-section" class="content-section hidden mb-10">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Assessments</h2>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 min-h-[420px] flex flex-col">
                <div id="assessments" class="tab-panel">
                    <div id="assessments-container" class="grid md:grid-cols-2 gap-4 flex-1">
                        @forelse($course->assessments as $index => $a)
                            <div class="paginated-item p-5 bg-gray-50 rounded-lg flex items-center justify-between border border-gray-100 shadow-sm transition-shadow duration-200 hover:shadow-md" data-index="{{ $loop->index }}">
                                <div class="flex items-center gap-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14v6m-3-3h6m-3-6V8m-3-3h6V2h-6a2 2 0 00-2 2v10zM7 7v10a2 2 0 002 2h10m-2 4a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2z" /></svg>
                                    <div>
                                        <div class="font-bold text-gray-900">{{ $a->title }}</div>
                                        <div class="text-xs text-gray-500 mt-1 font-mono">{{ $a->type }}</div>
                                    </div>
                                </div>
                                <div>
                                    <button type="button" data-assessment-id="{{ $a->id }}" class="view-assessment-btn text-blue-600 hover:text-blue-800 transition-colors font-bold">View &rarr;</button>
                                </div>
                            </div>
                        @empty
                            <div class="italic text-gray-500 p-5">No assessments found.</div>
                        @endforelse
                    </div>
                    <div class="mt-6 flex justify-end"><div id="assessments-pagination" class="pagination flex gap-2"></div></div>
                </div>
            </div>
        </div>

        <!-- Students Section -->
        <div id="students-section" class="content-section hidden mb-10">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Enrolled Students</h2>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 min-h-[420px] flex flex-col">
                <div id="students" class="tab-panel">
                    <div id="students-container" class="grid md:grid-cols-2 gap-4 flex-1">
                        @forelse($course->students as $index => $s)
                            <div class="paginated-item p-5 bg-gray-50 rounded-lg flex items-center gap-4 border border-gray-100 shadow-sm transition-shadow duration-200 hover:shadow-md" data-index="{{ $loop->index }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857A4 4 0 0015 9a4 4 0 10-6 0 4 4 0 00-1.644 7.143A3 3 0 002 18v2h5" /></svg>
                                <div>
                                    <div class="font-bold text-gray-900">{{ $s->name }}</div>
                                    <div class="text-xs text-gray-500 mt-1 font-mono">{{ $s->email }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="italic text-gray-500 p-5">No students enrolled.</div>
                        @endforelse
                    </div>
                    <div class="mt-6 flex justify-end"><div id="students-pagination" class="pagination flex gap-2"></div></div>
                </div>
            </div>
        </div>

    </main>

{{-- 5. Updated JavaScript for new card styles --}}
<script>
    // Modal Markup injected (kept outside main to avoid layout shifting)
    document.addEventListener('DOMContentLoaded', () => {
        const modalHtml = `
        <div id=\"assessmentModal\" class=\"fixed inset-0 hidden z-50\" aria-labelledby=\"assessmentModalTitle\" role=\"dialog\" aria-modal=\"true\">
            <div class=\"absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity\"></div>
            <div class=\"relative z-10 w-full h-full flex items-start md:items-center justify-center overflow-y-auto py-10 md:py-16\">
                <div class=\"modal-panel relative w-full max-w-4xl mx-auto bg-gradient-to-br from-white via-indigo-50 to-white border border-indigo-100 shadow-2xl rounded-2xl p-0 transform transition-all scale-95 opacity-0 flex flex-col max-h-[75vh]\">
                    <div class=\"px-6 md:px-8 pt-6 md:pt-8 pb-4 border-b border-indigo-100/60 flex items-start justify-between gap-6 shrink-0\">
                        <div class=\"min-w-0\">
                            <h3 id=\"assessmentModalTitle\" class=\"text-2xl font-bold tracking-tight text-indigo-700\"></h3>
                            <p class=\"mt-1 text-sm text-gray-600 font-mono\" id=\"assessmentMeta\"></p>
                        </div>
                        <div class=\"flex items-center gap-4\">
                            <button type=\"button\" id=\"assessmentModalClose\" aria-label=\"Close modal\" class=\"inline-flex items-center justify-center h-10 w-10 rounded-xl text-gray-500 hover:text-gray-700 hover:bg-white/70 focus:outline-none focus:ring-2 focus:ring-indigo-400/60 transition border border-transparent hover:border-indigo-200 bg-white/50 backdrop-blur\">
                                <svg xmlns=\"http://www.w3.org/2000/svg\" class=\"h-5 w-5\" fill=\"none\" viewBox=\"0 0 24 24\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M6 18L18 6M6 6l12 12\" /></svg>
                            </button>
                        </div>
                    </div>
                    <div class=\"modal-scroll custom-scroll flex-1 overflow-y-auto px-6 md:px-8 py-6 space-y-6\">
                        <div id=\"assessmentDescription\" class=\"prose prose-sm max-w-none text-gray-700\"></div>
                    <div id="assessmentFileWrapper" class="hidden">
                        <div class="flex items-center justify-between p-4 rounded-xl bg-white/80 border border-indigo-100 shadow-sm">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0118 9v10a2 2 0 01-2 2z" /></svg>
                                </span>
                                <div class="text-sm">
                                    <p class="font-semibold text-gray-800" id="assessmentFileLabel">Attached File</p>
                                    <p class="text-xs text-gray-500">Open the instructor provided activity / resource.</p>
                                </div>
                            </div>
                            <a id="assessmentFileLink" href="#" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-800 font-semibold text-sm px-3 py-2 rounded-lg bg-indigo-50 hover:bg-indigo-100 transition">
                                Open File →
                            </a>
                        </div>
                    </div>
                        <div id=\"assessmentQuestions\" class=\"space-y-5\"></div>
                    </div>
                    <div class=\"px-6 md:px-8 py-4 border-t border-indigo-100/60 flex justify-end gap-3 bg-white/60 backdrop-blur-sm rounded-b-2xl shrink-0\">
                        <button type=\"button\" id=\"assessmentModalCloseBottom\" class=\"inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold bg-indigo-600 text-white shadow hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500\">Close</button>
                    </div>
                </div>
            </div>
        </div>`;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    });

    // Tab and Panel Logic
    const tabs = document.querySelectorAll('.tab-btn');
    const panels = document.querySelectorAll('.tab-panel');
    const sections = document.querySelectorAll('.content-section');

    function setActiveTab(button) {
        tabs.forEach(btn => {
            // Reset classes for all buttons
            btn.classList.remove('active-card');
            
            // Reset icon background and border for all buttons
            const iconDiv = btn.querySelector('div');
            if (iconDiv) {
                iconDiv.classList.remove('bg-white', 'text-indigo-700', 'ring-2', 'ring-indigo-500', 'ring-inset');
                iconDiv.classList.add('bg-indigo-500', 'text-white');
            }
        });

        // Set active classes for the clicked button
        button.classList.add('active-card');
        
        // Highlight the icon background and text for the active button
        const activeIconDiv = button.querySelector('div');
        if (activeIconDiv) {
            activeIconDiv.classList.remove('bg-indigo-500', 'text-white');
            activeIconDiv.classList.add('bg-white', 'text-indigo-700', 'ring-2', 'ring-indigo-500', 'ring-inset');
        }
    }

    tabs.forEach(btn => {
        btn.addEventListener('click', () => {
            // Hide all sections
            sections.forEach(s => s.classList.add('hidden'));
            
            // Show the target section
            const targetSectionId = btn.getAttribute('data-tab') + '-section';
            const targetSection = document.getElementById(targetSectionId);
            if (targetSection) {
                targetSection.classList.remove('hidden');
                setActiveTab(btn);
            }
        });
    });

    // Set the default active tab on load
    const firstTab = document.querySelector('.tab-btn[data-tab="overview"]');
    if (firstTab) {
        // Manually trigger the state changes without a full click event
        sections.forEach(s => s.classList.add('hidden'));
        const targetSection = document.getElementById('overview-section');
        if (targetSection) {
            targetSection.classList.remove('hidden');
            setActiveTab(firstTab);
        }
    }

    // Simple client-side pagination for lists with .paginated-item and data-page attributes
    // New pagination logic with configurable per-page counts & two-column layout support
    const paginationConfig = {
        topics: 10,
        materials: 10,
        assessments: 10,
        students: 10
    };

    function initSectionPagination(section) {
        const perPage = paginationConfig[section] || 10;
        const container = document.querySelector(`#${section}-container`);
        if (!container) return;
        const items = Array.from(container.querySelectorAll('.paginated-item'));
        if (!items.length) return;
        const totalPages = Math.ceil(items.length / perPage);
        const pager = document.querySelector(`#${section}-pagination`);
        if (!pager) return;
        if (totalPages <= 1) { pager.innerHTML = ''; return; }

        let current = 0;

        function showPage(p) {
            current = p;
            items.forEach((item, idx) => {
                const inPage = idx >= p*perPage && idx < (p+1)*perPage;
                item.style.display = inPage ? 'flex' : 'none';
            });
            pager.querySelectorAll('.pagination-btn[data-page]').forEach(btn => {
                btn.classList.toggle('is-active', parseInt(btn.dataset.page) === p);
            });
            const prev = pager.querySelector('.pagination-prev');
            const next = pager.querySelector('.pagination-next');
            if (prev) prev.disabled = (p === 0);
            if (next) next.disabled = (p === totalPages - 1);
        }

        function buildBtn(label, extraClass, handler, pageValue) {
            const b = document.createElement('button');
            b.type = 'button';
            b.textContent = label;
            b.className = 'pagination-btn ' + extraClass;
            if (pageValue !== undefined) b.dataset.page = pageValue;
            b.addEventListener('click', handler);
            return b;
        }

        pager.innerHTML = '';
        pager.appendChild(buildBtn('Prev','pagination-prev', () => { if (current>0) showPage(current-1); }));
        for (let i=0;i<totalPages;i++) {
            pager.appendChild(buildBtn(String(i+1),'', () => showPage(i), i));
        }
        pager.appendChild(buildBtn('Next','pagination-next', () => { if (current<totalPages-1) showPage(current+1); }));
        showPage(0);
    }

    // Initialize pagination for each section
    document.addEventListener('DOMContentLoaded', function(){
        initSectionPagination('topics');
        initSectionPagination('materials');
        initSectionPagination('assessments');
        initSectionPagination('students');

        // Assessment modal logic
        const body = document.body;
        const openModal = () => {
            const modal = document.getElementById('assessmentModal');
            if(!modal) return; 
            modal.classList.remove('hidden');
            const panel = modal.querySelector('.modal-panel');
            requestAnimationFrame(()=>{
                panel.classList.remove('scale-95','opacity-0');
                panel.classList.add('scale-100','opacity-100');
            });
        };
        const closeModal = () => {
            const modal = document.getElementById('assessmentModal');
            if(!modal) return; 
            const panel = modal.querySelector('.modal-panel');
            panel.classList.add('scale-95','opacity-0');
            setTimeout(()=>{ modal.classList.add('hidden'); },150);
        };
        body.addEventListener('click', e => {
            if(e.target.matches('#assessmentModal, #assessmentModal .absolute.inset-0')) closeModal();
        });
        body.addEventListener('keydown', e => { if(e.key==='Escape') closeModal(); });
        body.addEventListener('click', e => {
            if(e.target.id==='assessmentModalClose' || e.target.id==='assessmentModalCloseBottom') closeModal();
        });
        let answersVisible = false;
        function renderAssessment(data){
            // Basic HTML escape helper to prevent injection in dynamically inserted text
            const esc = (str) => String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
            document.getElementById('assessmentModalTitle').textContent = data.title;
            document.getElementById('assessmentMeta').textContent = `${data.type.toUpperCase()} • ${data.questions.length} question${data.questions.length!==1?'s':''}${data.duration_minutes? ' • '+data.duration_minutes+' min':''}`;
            document.getElementById('assessmentDescription').innerHTML = data.description ? `<p>${data.description}</p>` : '';
            // File section
            const fileWrapper = document.getElementById('assessmentFileWrapper');
            if(data.has_file){
                const fileLink = document.getElementById('assessmentFileLink');
                fileLink.href = `{{ url('/admin/assessments') }}/${data.id}/file`;
                fileWrapper.classList.remove('hidden');
            } else { fileWrapper.classList.add('hidden'); }

            const qWrap = document.getElementById('assessmentQuestions');
            qWrap.innerHTML = '';
            if(!data.questions.length){
                qWrap.innerHTML = '<div class="text-sm text-gray-500 italic">No questions added.</div>';
            } else {
                data.questions.sort((a,b)=> (a.order||0)-(b.order||0)).forEach((q,i)=>{
                    const card = document.createElement('div');
                    card.className = 'rounded-xl border border-indigo-100 bg-white/70 backdrop-blur px-5 py-4 shadow-sm hover:shadow-md transition';
                    let answerBlock = '';
                    const hasOptions = q.options && q.options.length;
                    if(hasOptions){
                        const sortedOpts = q.options.sort((a,b)=> (a.order||0)-(b.order||0));
                        // Determine a single correct option strictly from DB data.
                        // Priority 1: explicit is_correct flag (first one only if multiple mistakenly set)
                        let correctIndex = null;
                        let flaggedIndices = [];
                        sortedOpts.forEach((opt, idx)=>{ if(opt.is_correct){ flaggedIndices.push(idx);} });
                        if(flaggedIndices.length){
                            correctIndex = flaggedIndices[0];
                        } else {
                            // Priority 2: q.correct_answer value (numeric index, 1-based index, letter, or matching text)
                            const raw = String(q.correct_answer ?? '').trim();
                            if(raw !== ''){
                                if(/^\d+$/.test(raw)){ // numeric
                                    const num = parseInt(raw,10);
                                    if(num >=0 && num < sortedOpts.length) correctIndex = num; // treat as 0-based
                                    else if(num-1 >=0 && (num-1) < sortedOpts.length) correctIndex = num-1; // treat as 1-based
                                } else if(/^[A-Za-z]$/.test(raw)) { // single letter
                                    const idx = raw.toUpperCase().charCodeAt(0) - 65;
                                    if(idx >=0 && idx < sortedOpts.length) correctIndex = idx;
                                } else { // fallback: text comparison (normalized)
                                    const normRaw = raw.toLowerCase().replace(/[^a-z0-9]+/g,'');
                                    for(let i=0;i<sortedOpts.length;i++){
                                        const normOpt = String(sortedOpts[i].text||'').toLowerCase().replace(/[^a-z0-9]+/g,'');
                                        if(normOpt === normRaw){ correctIndex = i; break; }
                                    }
                                    if(correctIndex===null){ // loose contains
                                        for(let i=0;i<sortedOpts.length;i++){
                                            if(String(sortedOpts[i].text||'').toLowerCase().includes(raw.toLowerCase())){ correctIndex = i; break; }
                                        }
                                    }
                                }
                            }
                        }
                        // Final fallback: if still null and only one option, mark that.
                        if(correctIndex === null && sortedOpts.length === 1) correctIndex = 0;
                        answerBlock = `<ul class=\"mt-3 space-y-1\">${sortedOpts.map((o,idxOpt)=>{
                            const letter = String.fromCharCode(65 + idxOpt);
                            const isCorrect = idxOpt === correctIndex;
                            return `<li class='flex gap-2 text-sm answer-option ${isCorrect? 'is-correct bg-green-50/60 border border-green-100 rounded-lg pr-2':''}' data-letter='${letter}' data-correct='${isCorrect}'>
                                <span class='w-5 h-5 flex items-center justify-center text-[10px] font-semibold rounded-full bg-indigo-100 text-indigo-700'>${letter}</span>
                                <span>${esc(o.text)}</span>
                                ${isCorrect? `<span class='correct-badge ml-2 inline-flex items-center gap-1 text-[10px] font-semibold tracking-wide px-2 py-0.5 rounded-full bg-green-100 text-green-700 border border-green-200'>Correct</span>`:''}
                            </li>`;
                        }).join('')}</ul>`;
                    } else if(q.correct_answer){
                        // Identification (no options, but a definitive correct answer)
                        answerBlock = `<div class=\"mt-3\">
                            <div class='text-xs font-semibold uppercase tracking-wide text-gray-500'>Answer</div>
                            <div class='mt-1 inline-flex items-center gap-2 text-sm answer-option is-correct bg-green-50/60 border border-green-100 rounded-lg px-2 py-1'>
                                <span class='px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-700 font-mono text-[10px]'>ID</span>
                                <span>${esc(q.correct_answer)}</span>
                                <span class='correct-badge ml-2 inline-flex items-center gap-1 text-[10px] font-semibold tracking-wide px-2 py-0.5 rounded-full bg-green-100 text-green-700 border border-green-200'>Correct</span>
                            </div>
                        </div>`;
                    } else {
                        // Essay (no options, no single correct answer)
                        answerBlock = `<div class=\"mt-3 text-sm italic text-gray-600\">Students will provide a written response.</div>`;
                    }
                    card.innerHTML = `<div class=\"flex items-start justify-between gap-4\"><h4 class=\"font-semibold text-indigo-700\">Q${i+1}. ${esc(q.text)}</h4><span class=\"text-xs font-mono px-2 py-1 rounded-lg bg-indigo-50 text-indigo-600\">${q.points ?? 0} pts</span></div>${answerBlock}`;
                    qWrap.appendChild(card);
                });
            }
            // Answers visible by default; no toggle button
        }
        // Removed toggle handler and visibility updater
        document.querySelectorAll('.view-assessment-btn').forEach(btn=>{
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-assessment-id');
                fetch(`{{ url('/admin/assessments') }}/${id}/details`, {headers:{'Accept':'application/json'}})
                    .then(r=> r.json())
                    .then(json => {
                        if(json.success){
                            renderAssessment(json.assessment);
                            openModal();
                        } else {
                            alert('Failed to load assessment.');
                        }
                    })
                    .catch(()=> alert('Error loading assessment.'));
            });
        });
    });

    // Add CSS for the active card state via JS class manipulation (since we can't use dynamic Blade classes easily)
    const style = document.createElement('style');
    style.textContent = `
        .active-card {background-color:#eef2ff!important;border-color:#6366f1!important;box-shadow:0 10px 15px -3px rgba(99,102,241,.2),0 4px 6px -2px rgba(99,102,241,.1)}
        .active-card h3{color:#4338ca!important}
        .active-card p{color:#6366f1!important}
        .pagination button.pagination-btn{position:relative;display:inline-flex;align-items:center;justify-content:center;height:2.25rem;min-width:2.25rem;padding:0 .75rem;font-size:.7rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;border-radius:.75rem;border:1px solid #e0e7ff;background:linear-gradient(to bottom,#ffffff,#f8fafc);color:#475569;box-shadow:0 1px 2px rgba(0,0,0,.05);transition:all .18s ease}
        .pagination button.pagination-btn:hover:not(:disabled){border-color:#c7d2fe;color:#1e3a8a;background:#f1f5ff}
        .pagination button.pagination-btn.is-active{background:linear-gradient(to bottom,#6366f1,#4f46e5);color:#fff;border-color:#4f46e5;box-shadow:0 4px 10px -2px rgba(79,70,229,.4)}
        .pagination button.pagination-btn:disabled{opacity:.4;cursor:not-allowed}
        .pagination button.pagination-prev, .pagination button.pagination-next{font-weight:500}
        @media (max-width:640px){.pagination button.pagination-btn{min-width:2rem;height:2rem;padding:0 .5rem;font-size:.65rem;border-radius:.5rem}}
        #assessmentModal .modal-panel.scale-100{transform:scale(1);}
        #assessmentModal .modal-panel{transition:transform .15s ease, opacity .15s ease;}
    #assessmentModal .custom-scroll{scrollbar-width:thin;scrollbar-color:#6366f1 #eef2ff;}
    #assessmentModal .custom-scroll::-webkit-scrollbar{width:10px;}
    #assessmentModal .custom-scroll::-webkit-scrollbar-track{background:linear-gradient(to bottom,#eef2ff,#f8fafc);border-radius:12px;}
    #assessmentModal .custom-scroll::-webkit-scrollbar-thumb{background:linear-gradient(180deg,#6366f1,#4f46e5);border-radius:12px;border:2px solid #eef2ff;}
    #assessmentModal .custom-scroll::-webkit-scrollbar-thumb:hover{background:linear-gradient(180deg,#4f46e5,#4338ca);}
    `;
    document.head.appendChild(style);
</script>

</x-layoutAdmin>
