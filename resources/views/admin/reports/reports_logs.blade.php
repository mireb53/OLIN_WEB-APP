<x-layoutAdmin>
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-semibold">Reports & Logs</h1>
      @if($filtersApplied)
        <div class="text-sm text-gray-600 mt-1">
          {{-- Selected filters summary: Instructor · Program · Course --}}
          @if(!empty($selectedInstructorName))
            <span class="font-medium">{{ $selectedInstructorName }}</span>
          @endif
          @if(!empty($selectedProgramName))
            <span class="mx-2 text-gray-400">&middot;</span>
            <span>{{ $selectedProgramName }}</span>
          @endif
          @if(!empty($selectedCourseName))
            <span class="mx-2 text-gray-400">&middot;</span>
            <span class="text-gray-700">{{ $selectedCourseName }}</span>
          @endif
        </div>
      @endif
    </div>
    <div class="ml-4">
      <button id="openReportsFilter" onclick="openReportsModal()" class="bg-[#096F4D] text-white px-3 py-1 rounded">Open Filter Modal</button>
    </div>
  </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="bg-white p-4 rounded shadow">
      <div class="text-sm text-gray-500">Courses</div>
      <div class="text-2xl font-bold">{{ $filtersApplied ? $coursesCountFiltered : $coursesCountGlobal }}</div>
    </div>
    <div class="bg-white p-4 rounded shadow">
      <div class="text-sm text-gray-500">Materials</div>
      <div class="text-2xl font-bold">{{ $filtersApplied ? $materialsCountFiltered : $materialsCountGlobal }}</div>
    </div>
    <div class="bg-white p-4 rounded shadow">
      <div class="text-sm text-gray-500">Assessments</div>
      <div class="text-2xl font-bold">{{ $filtersApplied ? $assessmentsCountFiltered : $assessmentsCountGlobal }}</div>
    </div>
  </div>

  <!-- Enrollment & Grades -->
  <section class="bg-white p-6 rounded shadow mt-6">
    <h2 class="text-lg font-semibold mb-4">Enrollment & Grade Overview</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="p-4 border rounded">
        <div class="text-sm text-gray-500">Total Enrollments</div>
        <div class="text-2xl font-bold">{{ $filtersApplied ? $totalEnrollmentsFiltered : $totalEnrollmentsGlobal }}</div>
        <div class="text-sm text-gray-500">{{ $filtersApplied ? $activeEnrollmentsFiltered : $activeEnrollmentsGlobal }} active</div>
      </div>

      <div class="p-4 border rounded">
        <div class="text-sm text-gray-500">Total Submissions</div>
        <div class="text-2xl font-bold">{{ $filtersApplied ? $totalSubmissionsFiltered : $totalSubmissionsGlobal }}</div>
        <div class="text-sm text-gray-500">Recent: {{ $filtersApplied ? $recentSubmissionsFiltered->count() : '-' }}</div>
      </div>

      <div class="p-4 border rounded">
        <div class="text-sm text-gray-500">Average Grade (All)</div>
        <div class="text-2xl font-bold">{{ $filtersApplied ? ($avgGradeOverallFiltered ? number_format($avgGradeOverallFiltered,2) : '-') : ($avgGradeOverallGlobal ? number_format($avgGradeOverallGlobal,2) : '-') }}</div>
        <div class="text-sm text-gray-500">Top course avg below</div>
      </div>
    </div>
  </section>

  <section class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-6">
    <div class="bg-white p-6 rounded shadow">
      <h3 class="font-semibold mb-3">Top Courses by Enrollee</h3>
      <ul class="space-y-2">
  @foreach(($filtersApplied ? $topCoursesFiltered : $topCoursesGlobal)->slice(0,3) as $c)
          <li class="flex justify-between items-center border-b pb-2">
            <div>
              <div class="font-medium">{{ $c->title }}</div>
              <div class="text-xs text-gray-500">Course ID: {{ $c->id }}</div>
            </div>
            <div class="text-lg font-bold">{{ $c->students }}</div>
          </li>
        @endforeach
      </ul>
    </div>

    <div class="bg-white p-6 rounded shadow">
      <h3 class="font-semibold mb-3">Top Courses by Avg Grade</h3>
      <ul class="space-y-2">
  @foreach(($filtersApplied ? $avgGradeByCourseFiltered : $avgGradeByCourseGlobal)->slice(0,3) as $g)
          <li class="flex justify-between items-center border-b pb-2">
            <div>
              <div class="font-medium">{{ $g->title }}</div>
            </div>
            <div class="text-lg font-bold">{{ number_format($g->avg_grade,2) }}</div>
          </li>
        @endforeach
      </ul>
    </div>
  </section>

  <!-- Recent Submissions and Materials -->
  <section class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-6">
        <div class="bg-white p-6 rounded shadow lg:col-span-2">
      <h3 class="font-semibold mb-3">Recent Submissions</h3>
      @if($filtersApplied)
      <div id="recentSubmissionsContainer">
        <table class="min-w-full text-left text-sm">
          <thead class="text-gray-500 text-xs uppercase">
            <tr>
              <th class="px-2 py-1">Student</th>
              <th class="px-2 py-1">Assessment</th>
              <th class="px-2 py-1">Score</th>
              <th class="px-2 py-1">Submitted</th>
            </tr>
          </thead>
          <tbody>
            @foreach($recentSubmissionsFiltered as $rs)
              @php $p = intdiv($loop->index, 5); @endphp
              <tr class="border-t paginated-item" data-page="{{ $p }}">
                <td class="px-2 py-2">{{ $rs->student }}</td>
                <td class="px-2 py-2">{{ $rs->assessment }}</td>
                <td class="px-2 py-2">{{ $rs->score ?? '-' }}</td>
                <td class="px-2 py-2">{{ \Carbon\Carbon::parse($rs->submitted_at)->diffForHumans() }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
  <div class="mt-2 flex justify-end" id="recentSubmissionsPager"></div>
      </div>
      @else
        <div class="p-6 text-sm text-gray-500">Apply a filter to view recent submissions.</div>
      @endif
    </div>

      <div class="bg-white p-6 rounded shadow">
      <h3 class="font-semibold mb-3">Recent Materials</h3>
      @if($filtersApplied)
      <ul id="recentMaterialsList" class="space-y-2">
        @foreach($recentMaterialsFiltered as $m)
          @php $p = intdiv($loop->index, 5); @endphp
          <li class="flex justify-between items-center border-b pb-2 paginated-item" data-page="{{ $p }}">
            <div>
              <div class="font-medium">{{ $m->material }}</div>
              <div class="text-xs text-gray-500">Course: {{ $m->course }}</div>
            </div>
            <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($m->created_at)->diffForHumans() }}</div>
          </li>
        @endforeach
      </ul>
  <div class="mt-2 flex justify-end" id="recentMaterialsPager"></div>
      @else
        <div class="p-6 text-sm text-gray-500">Apply a filter to view recent materials.</div>
      @endif
    </div>
  </section>

  <!-- Topics & Verification -->
  <section class="bg-white p-6 rounded shadow mt-6">
    <div class="flex justify-between items-center">
      <div>
        <h3 class="font-semibold">Topics</h3>
        <div class="text-2xl font-bold">{{ $filtersApplied ? ($topicsCountFiltered ?? 0) : $topicsCountGlobal }}</div>
      </div>
      <div>
        <h3 class="font-semibold">Email Verification</h3>
        <div class="text-right">
          <div class="text-2xl font-bold">{{ $filtersApplied ? ($verifiedUsersFiltered ?? 0) : $verifiedUsersGlobal }} / {{ $filtersApplied ? ($totalUsersFiltered ?? 0) : $totalUsersGlobal }}</div>
          <div class="text-sm text-gray-500">Verified users</div>
        </div>
      </div>
    </div>
  </section>

    <div class="bg-white p-6 rounded shadow">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold mb-4">{{ $filtersApplied ? 'Submissions & Average Score (Filtered)' : 'Registrations & Comparisons' }}</h2>
          <div class="flex items-center space-x-2">
            <select id="chartRange" class="border rounded px-2 py-1 text-sm">
              <option value="7d">Last 7 days</option>
              <option value="30d">Last 30 days</option>
              <option value="365d">Last 365 days</option>
              <option value="today">Today</option>
              <option value="yesterday">Yesterday</option>
            </select>
            <input type="date" id="chartDatePicker" class="border rounded px-2 py-1 text-sm" />
          </div>
        </div>
      <canvas id="registrationsChart" height="120"></canvas>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="bg-white p-6 rounded shadow">
      <h3 class="font-semibold mb-3">Account Status by Role</h3>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <div class="text-sm text-gray-500 mb-2">Accounts marked <strong>Active</strong> ({{ $filtersApplied ? 'Filtered' : 'Global' }})</div>
          <ul class="space-y-2">
              @if($filtersApplied)
                {{-- When filtered, show full role counts as returned by the filtered query --}}
                @foreach($accountActiveUsers as $au)
                  <li class="flex justify-between">
                    <span class="capitalize">{{ $au->role }}</span>
                    <span class="font-medium">{{ $au->total }}</span>
                  </li>
                @endforeach
              @else
                @foreach($accountActiveUsersDefault as $au)
                  <li class="flex justify-between">
                    <span class="capitalize">{{ $au->role }}</span>
                    <span class="font-medium">{{ $au->total }}</span>
                  </li>
                @endforeach
              @endif
          </ul>
        </div>

        <div>
          <div class="text-sm text-gray-500 mb-2">Currently <strong>Online</strong> (last 15 minutes, {{ $filtersApplied ? 'Filtered' : 'Global' }})</div>
          <ul class="space-y-2">
            @if($filtersApplied)
              @foreach($onlineUsers as $ou)
                <li class="flex justify-between">
                  <span class="capitalize">{{ $ou->role }}</span>
                  <span class="font-medium">{{ $ou->total }}</span>
                </li>
              @endforeach
              @if(count($onlineUsers)===0)
                <li class="text-gray-400">No users online</li>
              @endif
            @else
              @foreach($onlineUsersDefault as $ou)
                <li class="flex justify-between">
                  <span class="capitalize">{{ $ou->role }}</span>
                  <span class="font-medium">{{ $ou->total }}</span>
                </li>
              @endforeach
              @if(count($onlineUsersDefault)===0)
                <li class="text-gray-400">No users online</li>
              @endif
            @endif
          </ul>
        </div>
      </div>
    </div>

    <div class="bg-white p-6 rounded shadow">
      <h3 class="font-semibold mb-3">Recent Sessions</h3>
      <div class="text-sm text-gray-500 mb-2">Last session entries ({{ $filtersApplied ? 'Filtered' : 'Global' }})</div>
      <div class="overflow-auto max-h-56">
        <table class="min-w-full text-left text-sm">
          <thead class="text-gray-500 text-xs uppercase">
            <tr>
              <th class="px-2 py-1">User ID</th>
              <th class="px-2 py-1">IP</th>
              <th class="px-2 py-1">Last Activity</th>
            </tr>
          </thead>
          <tbody>
            @php
              $recentToShow = $filtersApplied ? $recentSessions : $recentSessionsDefault;
            @endphp
            @foreach($recentToShow as $s)
              <tr class="border-t">
                <td class="px-2 py-2">{{ $s->user_id }} @if(isset($s->name)) - {{ $s->name }} @endif</td>
                <td class="px-2 py-2">{{ $s->ip_address }}</td>
                <td class="px-2 py-2">{{ \Carbon\Carbon::createFromTimestamp($s->last_activity)->setTimezone('Asia/Manila')->toDateTimeString() ?? '-' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>
@include('admin.reports.filter-modal')

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('registrationsChart').getContext('2d');
  @if(!$filtersApplied)
    const labels = {!! json_encode($labels) !!};
    const registrations = {!! json_encode($registrationsData ?? $data) !!};
    const activeAccounts = {!! json_encode($activeAccountsData ?? []) !!};
    const onlineCounts = {!! json_encode($onlineData ?? []) !!};
    const coursesCreated = {!! json_encode($coursesCreatedData ?? []) !!};

    new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'New Registrations',
            data: registrations,
            borderColor: '#16a34a',
            backgroundColor: 'rgba(16,163,74,0.08)',
            tension: 0.3,
            fill: true,
          },
          {
            label: 'Accounts Active',
            data: activeAccounts,
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37,99,235,0.06)',
            tension: 0.3,
            fill: true,
          },
          {
            label: 'Accounts Online',
            data: onlineCounts,
            borderColor: '#f59e0b',
            backgroundColor: 'rgba(245,158,11,0.06)',
            tension: 0.3,
            fill: true,
          },
          {
            label: 'Courses Created',
            data: coursesCreated,
            borderColor: '#7c3aed',
            backgroundColor: 'rgba(124,58,237,0.06)',
            tension: 0.3,
            fill: true,
          }
        ]
      },
      options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: { x: { grid: { display: false } }, y: { beginAtZero: true } }
      }
    });
    // expose chart instance for AJAX updates
    window._reportsChart = Chart.getChart(ctx.canvas) || Chart.instances[0];
  @else
    const labels = {!! json_encode($filteredLabels ?? []) !!};
    const submissions = {!! json_encode($submissionsSeries ?? []) !!};
    const avgScores = {!! json_encode($avgScoreSeries ?? []) !!};

    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [
          {
            type: 'bar',
            label: 'Submissions',
            data: submissions,
            backgroundColor: 'rgba(37,99,235,0.7)'
          },
          {
            type: 'line',
            label: 'Avg Score',
            data: avgScores,
            borderColor: '#f59e0b',
            backgroundColor: 'rgba(245,158,11,0.06)',
            yAxisID: 'y1',
            tension: 0.3,
            fill: false
          }
        ]
      },
      options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
          x: { stacked: false },
          y: { beginAtZero: true, position: 'left' },
          y1: { beginAtZero: true, position: 'right', grid: { display: false } }
        }
      }
    });
    window._reportsChart = Chart.getChart(ctx.canvas) || Chart.instances[0];
  @endif
</script>

<script>
  // Simple client-side pagination for lists with .paginated-item and data-page attributes
  function initPagination(containerSelector, pagerSelector) {
    const items = document.querySelectorAll(containerSelector + ' .paginated-item');
    if (!items.length) return;
    let pages = {};
    items.forEach((it) => {
      const p = parseInt(it.dataset.page || '0', 10);
      pages[p] = pages[p] || [];
      pages[p].push(it);
    });
    const pageKeys = Object.keys(pages).map(n => parseInt(n,10)).sort((a,b)=>a-b);
    let current = pageKeys[0] || 0;

    function showPage(p) {
      items.forEach(it => it.style.display = 'none');
      (pages[p] || []).forEach(it => {
        if (it.tagName === 'TR') it.style.display = 'table-row';
        else it.style.display = 'flex';
      });
      // update pager buttons active state
      const pager = document.querySelector(pagerSelector);
      if (!pager) return;
      pager.querySelectorAll('button').forEach(btn => btn.classList.remove('bg-gray-200'));
      const active = pager.querySelector('[data-page="' + p + '"]');
      if (active) active.classList.add('bg-gray-200');
      current = p;
    }

    // build pager
    const pager = document.querySelector(pagerSelector);
    if (!pager) return;
    pager.innerHTML = '';
    const prev = document.createElement('button');
    prev.textContent = 'Prev';
    prev.className = 'px-2 py-1 mr-2 text-sm rounded border';
    prev.addEventListener('click', () => showPage(Math.max(pageKeys[0], current-1)));
    pager.appendChild(prev);

    pageKeys.forEach(p => {
      const b = document.createElement('button');
      b.textContent = (p+1).toString();
      b.dataset.page = p;
      b.className = 'px-2 py-1 mr-2 text-sm rounded';
      b.addEventListener('click', () => showPage(p));
      pager.appendChild(b);
    });

    const next = document.createElement('button');
    next.textContent = 'Next';
    next.className = 'px-2 py-1 text-sm rounded border';
    next.addEventListener('click', () => showPage(Math.min(pageKeys[pageKeys.length-1], current+1)));
    pager.appendChild(next);

    // show first page
    showPage(current);
  }

  document.addEventListener('DOMContentLoaded', function(){
    initPagination('#recentSubmissionsContainer', '#recentSubmissionsPager');
    initPagination('#recentMaterialsList', '#recentMaterialsPager');
    // Chart interactivity: fetch and update without page reload
    const chartRange = document.getElementById('chartRange');
    const chartPicker = document.getElementById('chartDatePicker');

    function postChartData(range, date) {
      const payload = {
        range: range,
        start: date || null,
        end: null,
        program_id: {{ json_encode($programFilter ?? null) }},
        instructor_id: {{ json_encode($instructorId ?? null) }},
        course_id: {{ json_encode($courseFilterId ?? null) }},
        _token: '{{ csrf_token() }}'
      };
      fetch('{{ route('admin.reports.chartData') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(payload)
      }).then(r => r.json()).then(data => {
        if (!window._reportsChart) return;
        window._reportsChart.data.labels = data.labels || [];
        window._reportsChart.data.datasets[0].data = data.registrations || [];
        window._reportsChart.data.datasets[1].data = data.activeAccounts || [];
        window._reportsChart.data.datasets[2].data = data.onlineCounts || [];
        window._reportsChart.data.datasets[3].data = data.coursesCreated || [];
        window._reportsChart.update();
      }).catch(err => console.error(err));
    }

    chartRange.addEventListener('change', (e) => {
      postChartData(e.target.value, null);
    });
    chartPicker.addEventListener('change', (e) => {
      // when a specific date is chosen, use range 'custom' and send start/end as same date
      postChartData('custom', e.target.value);
    });
  });
</script>


</x-layoutAdmin>
