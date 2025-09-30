<!-- resources/views/admin/reports/filter-modal.blade.php -->
<div id="reportsFilterModal" class="fixed inset-0 bg-black/60 hidden z-50 flex items-center justify-center">
  <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-black text-gray-100 rounded-2xl shadow-xl w-full max-w-2xl p-6 mx-4 relative">
    <div class="absolute top-4 right-4 flex items-center gap-3">
      <button onclick="closeReportsModal()" class="text-2xl text-gray-300 hover:text-red-400">&times;</button>
    </div>

    <h3 class="text-xl font-semibold text-cyan-400 mb-4">Filter Reports by Instructor</h3>

    <div id="reportsFilterError" class="text-red-400 mb-2"></div>

    <div class="space-y-4">
      <div>
        <label class="block text-sm text-gray-300 mb-1">Instructor Email</label>
        <div class="flex gap-3">
          <input id="reportsInstructorEmail" type="email" class="flex-1 px-4 py-2 bg-gray-800 border border-gray-700 rounded text-gray-100" placeholder="instructor@example.com">
          <button id="reportsLookupBtn" class="px-4 py-2 bg-cyan-600 rounded font-semibold">Lookup</button>
        </div>
        <div id="reportsInstructorResult" class="mt-2 text-sm text-gray-300 italic"></div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm text-gray-300 mb-1">Programs (by this instructor)</label>
          <select id="reportsProgramSelect" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded text-gray-100">
            <option value="">-- All Programs --</option>
          </select>
        </div>

        <div>
          <label class="block text-sm text-gray-300 mb-1">Courses (by this instructor)</label>
          <select id="reportsCourseSelect" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded text-gray-100">
            <option value="">-- All Courses --</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block text-sm text-gray-300 mb-1">Date Range</label>
        <select id="reportsRangeSelect" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded text-gray-100">
          <option value="today">Today</option>
          <option value="yesterday">Yesterday</option>
          <option value="7d" selected>Last 7 days</option>
          <option value="30d">Last 30 days</option>
          <option value="365d">Last 12 months</option>
        </select>
      </div>

      <div class="flex justify-end gap-3">
        <button onclick="closeReportsModal()" class="px-4 py-2 bg-gray-700 rounded">Cancel</button>
        <button id="reportsApplyBtn" class="px-4 py-2 bg-green-600 rounded font-semibold">Apply Filters</button>
      </div>
    </div>
  </div>
</div>

<script>
  function openReportsModal() { document.getElementById('reportsFilterModal').classList.remove('hidden'); }
  function closeReportsModal() { document.getElementById('reportsFilterModal').classList.add('hidden'); }

  document.getElementById('reportsLookupBtn').addEventListener('click', function(){
    const email = document.getElementById('reportsInstructorEmail').value.trim();
    const resEl = document.getElementById('reportsInstructorResult'); resEl.textContent='';
    if (!email) { resEl.textContent = 'Please enter an email.'; return; }
    fetch('{{ url('/admin/reports/lookup-instructor') }}', {
      method: 'POST', headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
      body: JSON.stringify({ email })
    }).then(r => r.json()).then(data => {
      if (data.success && data.instructor) {
        resEl.textContent = 'Found: '+data.instructor.name+' ('+data.instructor.email+')';
        // mark lookup success so Apply knows lookup was used
        window.reportsLookupSuccess = true;
        // populate programs and courses
        const psel = document.getElementById('reportsProgramSelect');
        const csel = document.getElementById('reportsCourseSelect');
        psel.innerHTML = '<option value="">-- All Programs --</option>';
        csel.innerHTML = '<option value="">-- All Courses --</option>';
        data.programs.forEach(p=>{ const o = document.createElement('option'); o.value=p.id; o.textContent=p.name; psel.appendChild(o); });
        data.courses.forEach(c=>{ const o = document.createElement('option'); o.value=c.id; o.textContent=c.title; csel.appendChild(o); });
      } else {
        resEl.textContent = data.message || 'Instructor not found.';
        window.reportsLookupSuccess = false;
      }
    }).catch(()=> resEl.textContent='Lookup failed');
  });

  document.getElementById('reportsApplyBtn').addEventListener('click', function(){
    const email = document.getElementById('reportsInstructorEmail').value.trim();
    const courseId = document.getElementById('reportsCourseSelect').value;
    const programId = document.getElementById('reportsProgramSelect').value;
    const range = document.getElementById('reportsRangeSelect').value;
    // require that Lookup was used when an email is present
    const resText = document.getElementById('reportsInstructorResult').textContent || '';
    const errorEl = document.getElementById('reportsFilterError');
    errorEl.textContent = '';
    // use lookup flag if available, otherwise fall back to checking result text
    const ok = (typeof window.reportsLookupSuccess !== 'undefined') ? window.reportsLookupSuccess : (resText.toLowerCase().includes('found:'));
    if (email && !ok) {
      errorEl.textContent = 'Please click Lookup and ensure instructor is found before applying filters.';
      return;
    }
    // redirect to reports page with params
    const params = new URLSearchParams();
    if (email) params.set('instructor_email', email);
    if (courseId) params.set('course_id', courseId);
    if (programId) params.set('program_id', programId);
    if (range) params.set('range', range);
    // Use instructor email to look up id on server via route if needed; we will redirect with email and let controller accept instructor_id or email
    window.location.href = '{{ url('/admin/reports') }}?'+params.toString();
  });
</script>
