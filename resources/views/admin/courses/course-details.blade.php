<x-layoutAdmin>
    <div class="fixed inset-0 z-0 bg-gray-950 dark:bg-gray-50 transition-colors duration-500">
        <div class="absolute inset-0 z-0">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiBmaWxsPSJub25lIi8+PHBhdGggZD0iTTIyIDIwdi0yaDJoMnYtMmgydjJoMnYyLTJoMnYyIDJoMnYyLS0yVjIwaC0ydi0yaC0yVjE4aC0ydi0yIC0yVjE2aC0yVjE0aC0ydjJoMnYyaDIiIHN0cm9rZT0iI0ZGRkZGRiIgc3Ryb2tlLW9wYWNpdHk9IjAuMDUiIGZpbGw9Im5vbmUiLz48L3N2Zz4=')] dark:bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiBmaWxsPSJub25lIi8+PHBhdGggZD0iTTIyIDIwdi0yaDJoMnYtMmgydjJoMnYyLTJoMnYyIDJoMnYyLS0yVjIwaC0ydi0yaC0yVjE4aC0ydi0yIC0yVjE2aC0yVjE0aC0ydjJoMnYyaDIiIHN0cm9rZT0iIzAwMDAwMCIgc3Ryb2tlLW9wYWNpdHk9IjAuMDUiIGZpbGw9Im5vbmUiLz48L3N2Zz4=')] opacity-20"></div>
        </div>
    </div>
    
    <main class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-gray-800/50 dark:bg-white/50 backdrop-blur-md rounded-[3rem] shadow-2xl border border-gray-700/30 dark:border-gray-200/30 overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-700/50 dark:border-gray-200/50 flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-300 to-purple-500 dark:from-blue-400 dark:to-purple-600">
                        {{ $course->title }}
                    </h1>
                    <p class="mt-2 text-sm text-gray-400 dark:text-gray-600 font-mono">
                        <span class="bg-gray-700 dark:bg-gray-200 text-gray-200 dark:text-gray-700 px-2 py-0.5 rounded-full">{{ $course->course_code ?? 'N/A' }}</span> • {{ $course->program ? $course->program->name : 'N/A' }}
                    </p>
                </div>
                <div class="mt-4 md:mt-0 flex flex-col md:flex-row gap-3">
                    <a href="{{ route('admin.course_management') }}" class="inline-flex items-center gap-2 px-6 py-3 border border-gray-600 dark:border-gray-300 rounded-full text-sm font-semibold text-gray-300 dark:text-gray-700 hover:bg-gray-700 dark:hover:bg-gray-200 transition-all duration-300 transform hover:scale-105">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        Back to Courses
                    </a>
                </div>
            </div>

            <div class="p-8 grid grid-cols-1 lg:grid-cols-4 gap-12">
                <div class="lg:col-span-3">
                    <nav class="flex space-x-2 border-b-2 border-gray-700 dark:border-gray-200 mb-8">
                        <button data-tab="overview" class="tab-btn px-6 py-3 text-lg font-semibold text-gray-400 dark:text-gray-600 hover:text-blue-400 dark:hover:text-blue-500 transition-colors relative after:absolute after:bottom-0 after:left-0 after:w-full after:h-1 after:bg-blue-400 dark:after:bg-blue-500 after:transform after:scale-x-0 after:transition-transform after:duration-300">
                            Overview
                        </button>
                        <button data-tab="topics" class="tab-btn px-6 py-3 text-lg font-semibold text-gray-400 dark:text-gray-600 hover:text-blue-400 dark:hover:text-blue-500 transition-colors relative after:absolute after:bottom-0 after:left-0 after:w-full after:h-1 after:bg-blue-400 dark:after:bg-blue-500 after:transform after:scale-x-0 after:transition-transform after:duration-300">
                            Topics
                        </button>
                        <button data-tab="materials" class="tab-btn px-6 py-3 text-lg font-semibold text-gray-400 dark:text-gray-600 hover:text-blue-400 dark:hover:text-blue-500 transition-colors relative after:absolute after:bottom-0 after:left-0 after:w-full after:h-1 after:bg-blue-400 dark:after:bg-blue-500 after:transform after:scale-x-0 after:transition-transform after:duration-300">
                            Materials
                        </button>
                        <button data-tab="assessments" class="tab-btn px-6 py-3 text-lg font-semibold text-gray-400 dark:text-gray-600 hover:text-blue-400 dark:hover:text-blue-500 transition-colors relative after:absolute after:bottom-0 after:left-0 after:w-full after:h-1 after:bg-blue-400 dark:after:bg-blue-500 after:transform after:scale-x-0 after:transition-transform after:duration-300">
                            Assessments
                        </button>
                        <button data-tab="students" class="tab-btn px-6 py-3 text-lg font-semibold text-gray-400 dark:text-gray-600 hover:text-blue-400 dark:hover:text-blue-500 transition-colors relative after:absolute after:bottom-0 after:left-0 after:w-full after:h-1 after:bg-blue-400 dark:after:bg-blue-500 after:transform after:scale-x-0 after:transition-transform after:duration-300">
                            Students
                        </button>
                    </nav>

                    <div class="space-y-12">
                        <section id="overview" class="tab-panel">
                            <h3 class="text-2xl font-bold text-white dark:text-gray-900 mb-4">Course Description</h3>
                            <p class="text-gray-300 dark:text-gray-700 leading-relaxed">{{ $course->description ?? 'No description provided.' }}</p>
                        </section>

                        <section id="topics" class="tab-panel hidden">
                            <h3 class="text-2xl font-bold text-white dark:text-gray-900 mb-4">Topics</h3>
                            <ul class="list-none space-y-4">
                                @forelse($course->topics as $topic)
                                    <li class="p-4 bg-gray-800/50 dark:bg-gray-100/50 rounded-lg flex items-center gap-4 border border-gray-700/50 dark:border-gray-200/50 transition-colors duration-200 transform hover:scale-105">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-400 dark:text-blue-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v5a1 1 0 102 0V7zM9 13a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd" /></svg>
                                        <span class="text-gray-200 dark:text-gray-800 font-medium">{{ $topic->name }}</span>
                                    </li>
                                @empty
                                    <li class="italic text-gray-500 p-4">No topics found.</li>
                                @endforelse
                            </ul>
                        </section>

                        <section id="materials" class="tab-panel hidden">
                            <h3 class="text-2xl font-bold text-white dark:text-gray-900 mb-4">Materials</h3>
                            <ul class="space-y-4">
                                @forelse($course->materials as $m)
                                    <li class="p-5 bg-gray-800/50 dark:bg-gray-100/50 rounded-lg flex items-center justify-between border border-gray-700/50 dark:border-gray-200/50 transition-colors duration-200 transform hover:scale-105">
                                        <div class="flex items-center gap-4">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-orange-400 dark:text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            <div>
                                                <div class="font-bold text-white dark:text-gray-900">{{ $m->title }}</div>
                                                <div class="text-xs text-gray-400 dark:text-gray-600 mt-1 font-mono">{{ $m->material_type }}</div>
                                            </div>
                                        </div>
                                        <div>
                                            @if($m->file_path)
                                                <a href="{{ route('materials.show', $m->id) }}" class="text-blue-400 dark:text-blue-500 hover:text-blue-300 dark:hover:text-blue-700 transition-colors font-bold">Open &rarr;</a>
                                            @endif
                                        </div>
                                    </li>
                                @empty
                                    <li class="italic text-gray-500 p-5">No materials found.</li>
                                @endforelse
                            </ul>
                        </section>

                        <section id="assessments" class="tab-panel hidden">
                            <h3 class="text-2xl font-bold text-white dark:text-gray-900 mb-4">Assessments</h3>
                            <ul class="space-y-4">
                                @forelse($course->assessments as $a)
                                    <li class="p-5 bg-gray-800/50 dark:bg-gray-100/50 rounded-lg flex items-center justify-between border border-gray-700/50 dark:border-gray-200/50 transition-colors duration-200 transform hover:scale-105">
                                        <div class="flex items-center gap-4">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-purple-400 dark:text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14v6m-3-3h6m-3-6V8m-3-3h6V2h-6a2 2 0 00-2 2v10zM7 7v10a2 2 0 002 2h10m-2 4a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2z" /></svg>
                                            <div>
                                                <div class="font-bold text-white dark:text-gray-900">{{ $a->title }}</div>
                                                <div class="text-xs text-gray-400 dark:text-gray-600 mt-1 font-mono">{{ $a->type }}</div>
                                            </div>
                                        </div>
                                        <div>
                                            <a href="#" class="text-blue-400 dark:text-blue-500 hover:text-blue-300 dark:hover:text-blue-700 transition-colors font-bold">View &rarr;</a>
                                        </div>
                                    </li>
                                @empty
                                    <li class="italic text-gray-500 p-5">No assessments found.</li>
                                @endforelse
                            </ul>
                        </section>

                        <section id="students" class="tab-panel hidden">
                <h3 class="text-2xl font-bold text-white dark:text-gray-900 mb-4">Enrolled Students</h3>
                <div class="space-y-4">
                     @forelse($course->students as $s)
                         <div class="p-5 bg-gray-800/50 dark:bg-gray-100/50 rounded-lg flex items-center gap-4 border border-gray-700/50 dark:border-gray-200/50 transition-colors duration-200 transform hover:scale-105">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-emerald-400 dark:text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                <div>
                    <div class="font-bold text-white dark:text-gray-900">{{ $s->name }}</div>
                    <div class="text-xs text-gray-400 dark:text-gray-600 mt-1 font-mono">{{ $s->email }}</div>
                </div>
            </div>
                  @empty
            <div class="italic text-gray-500 p-5">No students enrolled.</div>
                 @endforelse
                 </div>
            </section>
                    </div>
                </div>

                <aside class="lg:col-span-1 p-8 bg-blue-900/50 dark:bg-blue-100/50 rounded-[2rem] h-fit border border-blue-800/50 dark:border-blue-200/50 backdrop-blur-sm shadow-inner">
                    <h2 class="text-xl font-bold text-white dark:text-gray-900 mb-6">Course Info</h2>
                    <div class="space-y-6 text-sm text-gray-300 dark:text-gray-700">
                        <div class="flex items-center gap-4">
                            <span class="text-blue-400 dark:text-blue-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            </span>
                            <div>
                                <strong class="block text-white dark:text-gray-900">Instructor</strong>
                                <span class="text-gray-400 dark:text-gray-600">{{ $course->instructor ? $course->instructor->name : 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-blue-400 dark:text-blue-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v1a2 2 0 01-2 2h-2a2 2 0 01-2-2v-1m0 0V7m0 0h6m0 0a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V9a2 2 0 012-2z" /></svg>
                            </span>
                            <div>
                                <strong class="block text-white dark:text-gray-900">Credits</strong>
                                <span class="text-gray-400 dark:text-gray-600">{{ $course->credits ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-blue-400 dark:text-blue-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.007 12.007 0 002.944 12a12.007 12.007 0 002.438 6.956A11.955 11.955 0 0112 21.056a11.955 11.955 0 018.618-3.04A12.007 12.007 0 0021.056 12a12.007 12.007 0 00-2.438-6.956z" /></svg>
                            </span>
                            <div>
                                <strong class="block text-white dark:text-gray-900">Status</strong>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold ring-1 ring-inset {{ $course->status === 'published' ? 'bg-green-900/30 text-green-300 ring-green-300/20 dark:bg-green-50 dark:text-green-700 dark:ring-green-600/20' : ($course->status === 'draft' ? 'bg-yellow-900/30 text-yellow-300 ring-yellow-300/20 dark:bg-yellow-50 dark:text-yellow-700 dark:ring-yellow-600/20' : 'bg-gray-700/30 text-gray-300 ring-gray-300/10 dark:bg-gray-50 dark:text-gray-600 dark:ring-gray-500/10') }}">
                                    {{ ucfirst($course->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-blue-400 dark:text-blue-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h.01M9 16h.01M13 16h.01M15 16h.01M17 12h.01M7 12h.01M12 12h.01M3 21h18M3 5h18a2 2 0 012 2v14a2 2 0 01-2 2H3a2 2 0 01-2-2V7a2 2 0 012-2z" /></svg>
                            </span>
                            <div>
                                <strong class="block text-white dark:text-gray-900">Created</strong>
                                <span class="text-gray-400 dark:text-gray-600">{{ $course->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </main>

    <script>
        // Tab and Panel Logic
        const tabs = document.querySelectorAll('.tab-btn');
        const panels = document.querySelectorAll('.tab-panel');

        function setActiveTab(button) {
            tabs.forEach(btn => {
                btn.classList.remove('text-blue-400', 'dark:text-blue-500', 'after:scale-x-100');
                btn.classList.add('text-gray-400', 'dark:text-gray-600');
            });
            button.classList.remove('text-gray-400', 'dark:text-gray-600');
            button.classList.add('text-blue-400', 'dark:text-blue-500', 'after:scale-x-100');
        }

        tabs.forEach(btn => {
            btn.addEventListener('click', () => {
                panels.forEach(p => p.classList.add('hidden'));
                const targetPanel = document.getElementById(btn.getAttribute('data-tab'));
                if (targetPanel) {
                    targetPanel.classList.remove('hidden');
                    setActiveTab(btn);
                }
            });
        });

        // Set the first tab as active by default
        const firstTab = document.querySelector('.tab-btn');
        if (firstTab) {
            firstTab.click();
        }
    </script>
</x-layoutAdmin>