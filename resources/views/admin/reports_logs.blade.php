<x-layoutAdmin>
    <x-slot name="header">
        <div class="mb-8">
            <h1 class="text-3xl font-semibold text-slate-700 mb-2">Reports & Logs</h1>
            <p class="text-slate-500 italic">Access system-wide analytics, usage reports, and activity logs.</p>
        </div>
    </x-slot>

    <main class="flex-1 p-4 md:p-8">
        {{-- Tabs --}}
        <div class="mb-6 border-b border-slate-200">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                <li class="mr-2">
                    <button class="inline-block p-4 border-b-2 border-indigo-500 text-indigo-600">Reports</button>
                </li>
                <li class="mr-2">
                    <button class="inline-block p-4 border-b-2 border-transparent hover:border-slate-300 hover:text-slate-600">Logs</button>
                </li>
            </ul>
        </div>

        {{-- Section: Reports --}}
        <section class="bg-white rounded-xl p-8 mb-8 shadow-lg">
            <h2 class="text-xl font-semibold text-slate-700 mb-6 uppercase tracking-wider">
                Reports
            </h2>

            {{-- Filters --}}
            <div class="flex flex-col md:flex-row gap-4 mb-6">
                @if(Auth::user()->role === 'SuperAdmin')
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">School</label>
                        <select class="w-full md:w-48 py-2 px-3 border border-slate-300 rounded-md">
                            <option value="">All Schools</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->id }}">{{ $school->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Date Range</label>
                    <select class="w-full md:w-48 py-2 px-3 border border-slate-300 rounded-md">
                        <option>Last 7 days</option>
                        <option>Last 30 days</option>
                        <option>This Semester</option>
                        <option>Custom Range</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Course</label>
                    <select class="w-full md:w-48 py-2 px-3 border border-slate-300 rounded-md">
                        <option value="">All Courses</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Instructor</label>
                    <select class="w-full md:w-48 py-2 px-3 border border-slate-300 rounded-md">
                        <option value="">All Instructors</option>
                        @foreach($instructors as $instructor)
                            <option value="{{ $instructor->id }}">{{ $instructor->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Charts --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-slate-50 rounded-lg p-6 shadow-inner">
                    <h3 class="font-semibold text-slate-700 mb-4">Student Progress Summary</h3>
                    <canvas id="studentProgressChart" class="w-full h-64"></canvas>
                </div>
                <div class="bg-slate-50 rounded-lg p-6 shadow-inner">
                    <h3 class="font-semibold text-slate-700 mb-4">Instructor Activity Report</h3>
                    <canvas id="instructorActivityChart" class="w-full h-64"></canvas>
                </div>
                <div class="bg-slate-50 rounded-lg p-6 shadow-inner md:col-span-2">
                    <h3 class="font-semibold text-slate-700 mb-4">Course Completion Stats</h3>
                    <canvas id="courseCompletionChart" class="w-full h-72"></canvas>
                </div>
            </div>

            <div class="mt-6">
                <button class="bg-indigo-500 text-white py-2 px-5 rounded-lg hover:bg-indigo-600 transition">
                    Export Reports (CSV)
                </button>
            </div>
        </section>

        {{-- Section: Logs --}}
        <section class="bg-white rounded-xl p-8 shadow-lg">
            <h2 class="text-xl font-semibold text-slate-700 mb-6 uppercase tracking-wider">
                Activity Logs
            </h2>

            {{-- Filters --}}
            <div class="flex flex-col md:flex-row gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Filter by User</label>
                    <input type="text" placeholder="Search user..." class="w-full md:w-64 py-2 px-3 border border-slate-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Date Range</label>
                    <select class="w-full md:w-48 py-2 px-3 border border-slate-300 rounded-md">
                        <option>Today</option>
                        <option>Last 7 days</option>
                        <option>Last 30 days</option>
                        <option>Custom Range</option>
                    </select>
                </div>
            </div>

            {{-- Logs Table --}}
            <div class="overflow-x-auto">
                <table class="w-full border-collapse bg-white rounded-xl overflow-hidden text-sm md:text-base">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="text-slate-700 p-4 text-left font-semibold uppercase text-sm border-b">Timestamp</th>
                            <th class="text-slate-700 p-4 text-left font-semibold uppercase text-sm border-b">User</th>
                            <th class="text-slate-700 p-4 text-left font-semibold uppercase text-sm border-b">Action</th>
                            <th class="text-slate-700 p-4 text-left font-semibold uppercase text-sm border-b">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50">
                                <td class="p-4 border-b">{{ $log->created_at->format('Y-m-d h:i A') }}</td>
                                <td class="p-4 border-b">{{ $log->user->name }}</td>
                                <td class="p-4 border-b">{{ $log->action }}</td>
                                <td class="p-4 border-b">{{ $log->details }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-4 text-center text-slate-500">No logs available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex justify-between">
                <button class="bg-indigo-500 text-white py-2 px-5 rounded-lg hover:bg-indigo-600 transition">
                    Download Logs (CSV)
                </button>
                <div class="flex gap-1">
                    {{ $logs->links() }}
                </div>
            </div>
        </section>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const studentProgressCtx = document.getElementById('studentProgressChart').getContext('2d');
            const instructorActivityCtx = document.getElementById('instructorActivityChart').getContext('2d');
            const courseCompletionCtx = document.getElementById('courseCompletionChart').getContext('2d');

            let studentProgressChart = new Chart(studentProgressCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Submissions',
                        data: [],
                        borderColor: 'rgba(75, 192, 192, 1)',
                        tension: 0.1
                    }]
                }
            });

            let instructorActivityChart = new Chart(instructorActivityCtx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Materials Uploaded',
                        data: [],
                        backgroundColor: 'rgba(153, 102, 255, 0.6)'
                    }]
                }
            });

            let courseCompletionChart = new Chart(courseCompletionCtx, {
                type: 'doughnut',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Enrolled Students',
                        data: [],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.6)',
                            'rgba(54, 162, 235, 0.6)',
                            'rgba(255, 206, 86, 0.6)',
                            'rgba(75, 192, 192, 0.6)',
                            'rgba(153, 102, 255, 0.6)',
                            'rgba(255, 159, 64, 0.6)'
                        ]
                    }]
                }
            });

            function fetchChartData() {
                // You can get filter values here, e.g.,
                // const schoolId = document.querySelector('select[name="school_id"]').value;
                
                fetch('{{ route('admin.reports.data') }}') // Add filters as query params
                    .then(response => response.json())
                    .then(data => {
                        studentProgressChart.data.labels = data.studentProgress.labels;
                        studentProgressChart.data.datasets[0].data = data.studentProgress.data;
                        studentProgressChart.update();

                        instructorActivityChart.data.labels = data.instructorActivity.labels;
                        instructorActivityChart.data.datasets[0].data = data.instructorActivity.data;
                        instructorActivityChart.update();

                        courseCompletionChart.data.labels = data.courseCompletion.labels;
                        courseCompletionChart.data.datasets[0].data = data.courseCompletion.data;
                        courseCompletionChart.update();
                    });
            }

            fetchChartData();

            // Add event listeners to filters to refetch data
            // document.querySelector('select[name="school_id"]').addEventListener('change', fetchChartData);
        });
    </script>
</x-layoutAdmin>
