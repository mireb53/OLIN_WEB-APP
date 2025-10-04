<x-layoutAdmin>
@push('styles')
<style>
  .card-hover { transition: transform .2s ease, box-shadow .2s ease; }
  .card-hover:hover { transform: translateY(-4px); box-shadow: 0 10px 24px rgba(0,0,0,.08); }
  .chip { display:inline-flex; align-items:center; padding:.25rem .5rem; font-size:.75rem; border-radius:9999px; }
  .chip-blue { background:#eff6ff; color:#1d4ed8; }
  .chip-amber { background:#fffbeb; color:#b45309; }
  .chip-green { background:#ecfdf5; color:#047857; }
  .rank-badge { width:1.75rem; height:1.75rem; border-radius:.5rem; display:flex; align-items:center; justify-content:center; font-weight:700; }
  .rank-1 { background:linear-gradient(135deg,#4f46e5,#7c3aed); color:#fff; }
  .rank-2 { background:#e0e7ff; color:#4338ca; }
  .rank-3 { background:#ede9fe; color:#6d28d9; }
</style>
@endpush
<div class="space-y-8">
  <div class="flex items-center justify-between">
    <div class="flex items-start space-x-3">
      <div class="w-12 h-12 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center">
        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
      </div>
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Reports & Logs</h1>
        <p class="text-gray-600">Insights across users, courses, sessions, and security</p>
      </div>
    </div>
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
    <div class="ml-4 flex items-center space-x-3">
      <button id="openReportsFilter" onclick="openReportsModal()" class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow card-hover">Filter</button>
      <button type="button" class="px-4 py-2 rounded-xl bg-gray-100 text-gray-600 shadow card-hover" title="Export (coming soon)" disabled>Export</button>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="p-6 rounded-2xl bg-gradient-to-br from-indigo-50 to-indigo-100 border border-indigo-200 card-hover">
      <div class="flex items-center justify-between mb-2">
        <div class="w-10 h-10 rounded-xl bg-indigo-500 flex items-center justify-center">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-7 7-4-4"/></svg>
        </div>
        <span class="chip chip-blue">Courses</span>
      </div>
      <div class="text-3xl font-extrabold text-indigo-900">{{ $filtersApplied ? $coursesCountFiltered : $coursesCountGlobal }}</div>
      <p class="text-xs text-indigo-700 mt-1">Published, draft, and archived courses included</p>
    </div>
    <div class="p-6 rounded-2xl bg-gradient-to-br from-emerald-50 to-emerald-100 border border-emerald-200 card-hover">
      <div class="flex items-center justify-between mb-2">
        <div class="w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        </div>
        <span class="chip chip-green">Materials</span>
      </div>
      <div class="text-3xl font-extrabold text-emerald-900">{{ $filtersApplied ? $materialsCountFiltered : $materialsCountGlobal }}</div>
      <p class="text-xs text-emerald-700 mt-1">Files and learning content uploaded</p>
    </div>
    <div class="p-6 rounded-2xl bg-gradient-to-br from-amber-50 to-amber-100 border border-amber-200 card-hover">
      <div class="flex items-center justify-between mb-2">
        <div class="w-10 h-10 rounded-xl bg-amber-500 flex items-center justify-center">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5h6M9 9h6m-6 4h6"/></svg>
        </div>
        <span class="chip chip-amber">Assessments</span>
      </div>
      <div class="text-3xl font-extrabold text-amber-900">{{ $filtersApplied ? $assessmentsCountFiltered : $assessmentsCountGlobal }}</div>
      <p class="text-xs text-amber-700 mt-1">Quizzes, exams, and assignments created</p>
    </div>
  </div>

  <!-- Enrollment & Grades -->
  <section class="bg-white p-8 rounded-2xl border border-gray-100 shadow mt-4 card-hover">
    <div class="flex items-center mb-6">
      <div class="w-10 h-10 rounded-xl bg-sky-500 flex items-center justify-center mr-3">
        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/></svg>
      </div>
      <div>
        <h2 class="text-xl font-bold text-gray-900">Enrollment & Grade Overview</h2>
        <p class="text-gray-600 text-sm">Key learning activity indicators</p>
      </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="p-5 rounded-xl border border-gray-200">
        <div class="text-sm text-gray-500">Total Enrollments</div>
        <div class="text-2xl font-bold">{{ $filtersApplied ? $totalEnrollmentsFiltered : $totalEnrollmentsGlobal }}</div>
        <div class="mt-2">
          <div class="w-full bg-gray-100 rounded-full h-2">
            @php
              $active = $filtersApplied ? ($activeEnrollmentsFiltered ?? 0) : ($activeEnrollmentsGlobal ?? 0);
              $totalE = $filtersApplied ? ($totalEnrollmentsFiltered ?? 0) : ($totalEnrollmentsGlobal ?? 0);
              $pct = $totalE > 0 ? min(100, round(($active / max(1,$totalE))*100)) : 0;
            @endphp
            <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $pct }}%"></div>
          </div>
          <div class="text-xs text-gray-500 mt-1">{{ $active }} active</div>
        </div>
      </div>
      <div class="p-5 rounded-xl border border-gray-200">
        <div class="text-sm text-gray-500">Total Submissions</div>
        <div class="text-2xl font-bold">{{ $filtersApplied ? $totalSubmissionsFiltered : $totalSubmissionsGlobal }}</div>
        <div class="text-xs text-gray-500 mt-1">Recent: {{ $filtersApplied ? $recentSubmissionsFiltered->count() : '-' }}</div>
      </div>
      <div class="p-5 rounded-xl border border-gray-200">
        <div class="text-sm text-gray-500">Average Grade (All)</div>
        <div class="text-2xl font-bold">{{ $filtersApplied ? ($avgGradeOverallFiltered ? number_format($avgGradeOverallFiltered,2) : '-') : ($avgGradeOverallGlobal ? number_format($avgGradeOverallGlobal,2) : '-') }}</div>
        @php $avg = $filtersApplied ? ($avgGradeOverallFiltered ?? 0) : ($avgGradeOverallGlobal ?? 0); $avgPct = max(0,min(100, round($avg))); @endphp
        <div class="w-full bg-gray-100 rounded-full h-2 mt-2"><div class="bg-amber-500 h-2 rounded-full" style="width: {{ $avgPct }}%"></div></div>
        <div class="text-xs text-gray-500 mt-1">Top course avg below</div>
      </div>
    </div>
  </section>

  <section class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow card-hover">
      <div class="flex items-center justify-between mb-3">
        <h3 class="font-semibold text-gray-900">Top Courses by Enrollee</h3>
        <span class="chip chip-blue">Top 3</span>
      </div>
      <ul class="space-y-3">
  @foreach(($filtersApplied ? $topCoursesFiltered : $topCoursesGlobal)->slice(0,3) as $c)
          <li class="flex justify-between items-center border-b pb-3">
            <div class="flex items-center space-x-3">
              <div class="rank-badge {{ $loop->index===0 ? 'rank-1' : ($loop->index===1 ? 'rank-2' : 'rank-3') }}">{{ $loop->iteration }}</div>
              <div>
                <div class="font-medium text-gray-900">{{ $c->title }}</div>
                <div class="text-xs text-gray-500">Course #{{ $c->id }}</div>
              </div>
            </div>
            <div class="text-lg font-bold text-indigo-700">{{ $c->students }}</div>
          </li>
        @endforeach
      </ul>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow card-hover">
      <div class="flex items-center justify-between mb-3">
        <h3 class="font-semibold text-gray-900">Top Courses by Avg Grade</h3>
        <span class="chip chip-amber">Top 3</span>
      </div>
      <ul class="space-y-3">
  @foreach(($filtersApplied ? $avgGradeByCourseFiltered : $avgGradeByCourseGlobal)->slice(0,3) as $g)
          <li class="flex justify-between items-center border-b pb-3">
            <div class="flex items-center space-x-3">
              <div class="rank-badge {{ $loop->index===0 ? 'rank-1' : ($loop->index===1 ? 'rank-2' : 'rank-3') }}">{{ $loop->iteration }}</div>
              <div class="font-medium text-gray-900">{{ $g->title }}</div>
            </div>
            <div class="text-lg font-bold text-amber-700">{{ number_format($g->avg_grade,2) }}</div>
          </li>
        @endforeach
      </ul>
    </div>
  </section>

  <!-- Recent Submissions and Materials -->
  <section class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow lg:col-span-2 card-hover">
      <div class="flex items-center justify-between mb-3">
        <h3 class="font-semibold text-gray-900">Recent Submissions</h3>
        @if($filtersApplied)
          <span class="chip chip-blue">Filtered</span>
        @else
          <span class="chip">Global</span>
        @endif
      </div>
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
              <tr class="border-t paginated-item hover:bg-gray-50" data-page="{{ $p }}">
                <td class="px-2 py-2 font-medium text-gray-900">{{ $rs->student }}</td>
                <td class="px-2 py-2">{{ $rs->assessment }}</td>
                <td class="px-2 py-2"><span class="chip chip-green">{{ $rs->score ?? '-' }}</span></td>
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

      <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow card-hover">
      <div class="flex items-center justify-between mb-3">
        <h3 class="font-semibold text-gray-900">Recent Materials</h3>
        @if($filtersApplied)
          <span class="chip chip-blue">Filtered</span>
        @else
          <span class="chip">Global</span>
        @endif
      </div>
      @if($filtersApplied)
      <ul id="recentMaterialsList" class="space-y-2">
        @foreach($recentMaterialsFiltered as $m)
          @php $p = intdiv($loop->index, 5); @endphp
          <li class="flex justify-between items-center border-b pb-2 paginated-item hover:bg-gray-50" data-page="{{ $p }}">
            <div>
              <div class="font-medium text-gray-900">{{ $m->material }}</div>
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
  <section class="bg-white p-6 rounded-2xl border border-gray-100 shadow mt-6 card-hover">
    <div class="flex justify-between items-center">
      <div>
        <h3 class="font-semibold">Topics</h3>
        <div class="text-2xl font-bold">{{ $filtersApplied ? ($topicsCountFiltered ?? 0) : $topicsCountGlobal }}</div>
      </div>
      <div>
        <h3 class="font-semibold">Email Verification</h3>
        <div class="text-right">
          <div class="text-2xl font-bold">{{ $filtersApplied ? ($verifiedUsersFiltered ?? 0) : $verifiedUsersGlobal }} / {{ $filtersApplied ? ($totalUsersFiltered ?? 0) : $totalUsersGlobal }}</div>
          @php
            $v = $filtersApplied ? ($verifiedUsersFiltered ?? 0) : ($verifiedUsersGlobal ?? 0);
            $t = $filtersApplied ? ($totalUsersFiltered ?? 0) : ($totalUsersGlobal ?? 0);
            $vp = $t > 0 ? round(($v/$t)*100) : 0;
          @endphp
          <div class="w-48 bg-gray-100 rounded-full h-2 ml-auto mt-2"><div class="bg-indigo-500 h-2 rounded-full" style="width: {{ $vp }}%"></div></div>
          <div class="text-sm text-gray-500 mt-1">Verified users ({{ $vp }}%)</div>
        </div>
      </div>
    </div>
  </section>

    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow card-hover">
        <div class="flex items-center justify-between mb-2">
          <div class="flex items-center space-x-2">
            <div class="w-8 h-8 rounded-lg bg-indigo-500 flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3v18m-7-7h14"/></svg>
            </div>
            <h2 class="text-lg font-semibold">{{ $filtersApplied ? 'Submissions & Average Score (Filtered)' : 'Registrations & Comparisons' }}</h2>
          </div>
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

  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow card-hover">
      <h3 class="font-semibold mb-3 text-gray-900">Account Status by Role</h3>
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

    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow card-hover">
      <h3 class="font-semibold mb-1 text-gray-900">Recent Sessions</h3>
      <div class="text-sm text-gray-500 mb-3">Last session entries ({{ $filtersApplied ? 'Filtered' : 'Global' }})</div>
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
              <tr class="border-t hover:bg-gray-50">
                <td class="px-2 py-2">{{ $s->user_id }} @if(isset($s->name)) - {{ $s->name }} @endif</td>
                <td class="px-2 py-2">
                  <span>{{ $s->ip_address }}</span>
                  @if(!empty($s->ip_address))
                    <button type="button" class="ml-2 text-xs text-indigo-600 underline" onclick="navigator.clipboard && navigator.clipboard.writeText('{{ $s->ip_address }}')">Copy</button>
                  @endif
                </td>
                <td class="px-2 py-2">{{ \Carbon\Carbon::createFromTimestamp($s->last_activity)->setTimezone(config('app.timezone'))->toDateTimeString() ?? '-' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- Failed Login Attempts Section --}}
  <section class="mb-6">
    <div class="bg-white rounded-3xl p-6 shadow border border-gray-100">
      <div class="flex items-center mb-4">
        <div class="w-10 h-10 bg-gradient-to-r from-red-500 to-red-600 rounded-xl flex items-center justify-center mr-3">
          <span class="text-xl">🛡️</span>
        </div>
        <div>
          <h2 class="text-xl font-bold text-gray-900">Failed Login Attempts</h2>
          <p class="text-gray-600 text-sm">Detailed records of failed authentication within the system</p>
        </div>
      </div>

      <form method="GET" class="mb-4 flex flex-col md:flex-row gap-3">
        <input type="date" name="from" value="{{ request('from') }}" class="border border-gray-300 rounded-xl px-4 py-2 w-full md:w-auto">
        <input type="date" name="to" value="{{ request('to') }}" class="border border-gray-300 rounded-xl px-4 py-2 w-full md:w-auto">
        <input type="text" name="user" value="{{ request('user') }}" placeholder="Search user/email" class="border border-gray-300 rounded-xl px-4 py-2 w-full md:w-1/3">
        <input type="text" name="ip" value="{{ request('ip') }}" placeholder="Filter by IP" class="border border-gray-300 rounded-xl px-4 py-2 w-full md:w-auto">
        <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700 shadow">Filter</button>
      </form>

      <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm border border-gray-200 rounded-xl overflow-hidden">
          <thead class="bg-red-100 text-red-800 font-semibold">
            <tr>
              <th class="px-4 py-3 border-b">Date/Time</th>
              <th class="px-4 py-3 border-b">User / Email</th>
              <th class="px-4 py-3 border-b">IP Address</th>
              <th class="px-4 py-3 border-b">Reason</th>
              <th class="px-4 py-3 border-b">Attempts</th>
            </tr>
          </thead>
          <tbody>
            @forelse(($failedLogins ?? []) as $log)
              <tr class="hover:bg-red-50">
                <td class="px-4 py-3 border-b">{{ \Carbon\Carbon::parse($log->created_at)->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s') }}</td>
                <td class="px-4 py-3 border-b">{{ $log->user_identifier ?? 'Unknown User' }}</td>
                <td class="px-4 py-3 border-b">{{ $log->ip_address ?? 'N/A' }}</td>
                <td class="px-4 py-3 border-b">{{ Str::headline($log->reason ?? 'failed') }}</td>
                <td class="px-4 py-3 border-b">{{ $log->attempts ?? 1 }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="px-4 py-6 text-center text-gray-500">No failed login attempts found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- System Logs Advanced Section -->
  <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow card-hover mt-6" id="systemLogsSection">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
      <div>
        <h2 class="text-lg font-semibold">System Logs</h2>
        <p class="text-xs text-gray-500">Latest application events (laravel.log) — newest first. Filter by level, date, or search.</p>
      </div>
      <div class="flex flex-wrap items-center gap-2 text-sm">
        <select id="logLevelFilter" class="border rounded px-2 py-1">
          <option value="">All Levels</option>
          <option value="ERROR">Error</option>
          <option value="WARNING">Warning</option>
          <option value="INFO">Info</option>
          <option value="DEBUG">Debug</option>
        </select>
        <input type="date" id="logDateFilter" class="border rounded px-2 py-1" />
        <input type="text" id="logSearch" placeholder="Search message/context" class="border rounded px-2 py-1 w-48" />
        <button id="logApplyFilters" type="button" class="bg-[#096F4D] text-white px-3 py-1 rounded">Apply</button>
        <button id="logClearFilters" type="button" class="border border-gray-300 px-3 py-1 rounded text-gray-600">Reset</button>
      </div>
    </div>
    <div class="overflow-x-auto border rounded" style="max-height:420px;">
      <table class="min-w-full text-left text-sm" id="systemLogsTable">
        <thead class="text-gray-500 text-xs uppercase sticky top-0 bg-gray-50">
          <tr>
            <th class="px-2 py-2 w-40">Timestamp</th>
            <th class="px-2 py-2 w-20">Level</th>
            <th class="px-2 py-2">Message</th>
            <th class="px-2 py-2 w-12">Details</th>
          </tr>
        </thead>
        <tbody id="systemLogsBody">
          @php $preview = $systemLogsPreview['data'] ?? []; @endphp
          @forelse($preview as $log)
            <tr class="border-t align-top">
              <td class="px-2 py-2 text-xs whitespace-nowrap">{{ $log['timestamp'] }}</td>
              <td class="px-2 py-2">
                <span class="text-[10px] font-semibold px-2 py-1 rounded inline-block
                  @if($log['level']==='ERROR') bg-red-100 text-red-700
                  @elseif($log['level']==='WARNING') bg-yellow-100 text-yellow-700
                  @elseif($log['level']==='DEBUG') bg-gray-200 text-gray-700
                  @else bg-green-100 text-green-700 @endif">{{ $log['level'] }}</span>
              </td>
              <td class="px-2 py-2 text-sm">
                <div class="font-medium break-words">{{ Str::limit($log['message'],180) }}</div>
                @if(!empty($log['context']))
                  <pre class="hidden whitespace-pre-wrap mt-2 text-xs bg-gray-50 p-2 rounded border context-block">{{ $log['context'] }}</pre>
                @endif
              </td>
              <td class="px-2 py-2 text-center">
                @if(!empty($log['context']))
                  <button class="toggle-context text-xs text-blue-600 underline" type="button">Show</button>
                @else
                  <span class="text-gray-300 text-xs">-</span>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="4" class="px-2 py-4 text-center text-gray-400">No log entries found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="flex justify-between items-center mt-3 text-xs" id="systemLogsFooter">
      <div id="systemLogsMeta" class="text-gray-500"></div>
      <div class="flex items-center gap-2" id="systemLogsPager"></div>
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

    // ---- System Logs dynamic loading ----
    const logsState = { page: 1, level: '', search: '', date: '', perPage: 25, pages: 1 };
    const logsBody = document.getElementById('systemLogsBody');
    const logsPager = document.getElementById('systemLogsPager');
    const logsMeta = document.getElementById('systemLogsMeta');
    const levelSel = document.getElementById('logLevelFilter');
    const searchInp = document.getElementById('logSearch');
    const dateInp = document.getElementById('logDateFilter');
    const btnApply = document.getElementById('logApplyFilters');
    const btnReset = document.getElementById('logClearFilters');

    function escapeHtml(str){ return str ? str.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c]||c)) : ''; }
    function truncate(str, n){ return (str && str.length>n)? str.slice(0,n-1)+'…' : (str||''); }

    function renderLogs(meta){
      if(!logsBody) return; // section may be conditionally present
      logsState.pages = meta.pages || 1;
      logsState.page = meta.page || 1;
      logsBody.innerHTML = '';
      if(!meta.data || meta.data.length===0){
        logsBody.innerHTML = '<tr><td colspan="4" class="px-2 py-4 text-center text-gray-400">No log entries found.</td></tr>';
      } else {
        meta.data.forEach(entry => {
          const tr = document.createElement('tr');
          tr.className = 'border-t align-top';
          const levelBadgeClass = entry.level==='ERROR' ? 'bg-red-100 text-red-700' : (entry.level==='WARNING' ? 'bg-yellow-100 text-yellow-700' : (entry.level==='DEBUG' ? 'bg-gray-200 text-gray-700' : 'bg-green-100 text-green-700'));
          tr.innerHTML = `
            <td class="px-2 py-2 text-xs whitespace-nowrap">${escapeHtml(entry.timestamp)}</td>
            <td class="px-2 py-2"><span class="text-[10px] font-semibold px-2 py-1 rounded inline-block ${levelBadgeClass}">${escapeHtml(entry.level)}</span></td>
            <td class="px-2 py-2 text-sm">
              <div class="font-medium break-words">${escapeHtml(truncate(entry.message,180))}</div>
              ${entry.context ? `<pre class="hidden whitespace-pre-wrap mt-2 text-xs bg-gray-50 p-2 rounded border context-block">${escapeHtml(entry.context)}</pre>`: ''}
            </td>
            <td class="px-2 py-2 text-center">${entry.context ? '<button class="toggle-context text-xs text-blue-600 underline" type="button">Show</button>' : '<span class="text-gray-300 text-xs">-</span>'}</td>`;
          logsBody.appendChild(tr);
        });
      }
      buildLogsPager();
      if (logsMeta) logsMeta.textContent = `${meta.total || 0} entries${logsState.pages>1?` · Page ${logsState.page}/${logsState.pages}`:''}`;
    }

    function buildLogsPager(){
      if(!logsPager) return;
      logsPager.innerHTML='';
      if(logsState.pages<=1) return;
      const mkBtn=(label,pg,disabled=false)=>{ const b=document.createElement('button'); b.textContent=label; b.className='px-2 py-1 border rounded '+(pg===logsState.page?'bg-gray-200':''); if(disabled){b.disabled=true; b.classList.add('opacity-50','cursor-not-allowed');} b.addEventListener('click',()=>{ if(pg!==logsState.page) { logsState.page=pg; fetchLogs(); }}); return b;};
      logsPager.appendChild(mkBtn('Prev', Math.max(1, logsState.page-1), logsState.page===1));
      const windowSize = 5;
      let start = Math.max(1, logsState.page - Math.floor(windowSize/2));
      let end = start + windowSize -1;
      if(end > logsState.pages){ end = logsState.pages; start = Math.max(1, end-windowSize+1); }
      for(let p=start; p<=end; p++){ logsPager.appendChild(mkBtn(p,p,false)); }
      logsPager.appendChild(mkBtn('Next', Math.min(logsState.pages, logsState.page+1), logsState.page===logsState.pages));
    }

    function fetchLogs(){
      const payload = { page: logsState.page, level: logsState.level, search: logsState.search, date: logsState.date, perPage: logsState.perPage, _token: '{{ csrf_token() }}' };
      fetch('{{ route('admin.reports.systemLogs') }}', { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body: JSON.stringify(payload) })
        .then(r=>r.json())
        .then(data=>{ renderLogs(data); })
        .catch(err=>console.error(err));
    }

    if(btnApply){
      btnApply.addEventListener('click', ()=>{ logsState.page=1; logsState.level=levelSel.value; logsState.search=searchInp.value.trim(); logsState.date=dateInp.value; fetchLogs(); });
    }
    if(btnReset){
      btnReset.addEventListener('click', ()=>{ levelSel.value=''; searchInp.value=''; dateInp.value=''; logsState.page=1; logsState.level=''; logsState.search=''; logsState.date=''; fetchLogs(); });
    }

    document.getElementById('systemLogsTable')?.addEventListener('click', function(e){
      if(e.target.classList.contains('toggle-context')){
        const pre = e.target.closest('tr').querySelector('.context-block');
        if(pre){ const showing = !pre.classList.contains('hidden'); pre.classList.toggle('hidden'); e.target.textContent = showing ? 'Show' : 'Hide'; }
      }
    });
  });
</script>


</x-layoutAdmin>
