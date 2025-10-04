<div id="exportImportModal" role="dialog" aria-modal="true" class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm hidden z-50">
  <div class="min-h-screen w-full flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-2xl w-[92vw] max-w-lg sm:max-w-xl mx-4 overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
      <h3 class="text-lg font-semibold">Export / Import Courses</h3>
      <button onclick="closeExportImportModal()" class="p-2 rounded-lg hover:bg-white/20 transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
    </div>
    <div class="p-6 space-y-8 max-h-[70vh] overflow-y-auto">
      <!-- Export Section -->
      <section>
        <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wide mb-3">Export</h4>
        <div class="space-y-4">
          <div class="flex items-center gap-4">
            <label class="flex items-center gap-2 text-sm font-medium text-slate-700"><input type="radio" name="exportScope" value="all" checked class="text-indigo-600 focus:ring-indigo-500"/> All Courses</label>
            <label class="flex items-center gap-2 text-sm font-medium text-slate-700"><input type="radio" name="exportScope" value="single" class="text-indigo-600 focus:ring-indigo-500"/> Specific Course</label>
          </div>
          <div>
            <select id="exportCourseSelect" class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 disabled:opacity-40 disabled:cursor-not-allowed" disabled>
              <option value="">Select a course...</option>
            </select>
          </div>
          <div>
            <button id="exportCoursesBtn" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-semibold shadow hover:bg-indigo-500 transition">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/></svg>
              Export JSON
            </button>
            <button id="exportCoursesExcelBtn" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-slate-200 text-slate-700 text-sm font-semibold shadow hover:bg-slate-300 transition ml-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16l4 4 4-4M12 12v8m8-4a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              Export Excel
            </button>
          </div>
        </div>
      </section>
      <hr/>
      <!-- Import Section -->
      <section>
        <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wide mb-3">Import</h4>
        <div class="space-y-4">
          <div>
            <input type="file" id="importFileInput" accept="application/json,.json" class="block w-full text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"/>
          </div>
          <div class="text-xs text-slate-500 leading-relaxed bg-slate-50 p-3 rounded-lg">
            Re-import a previously exported courses JSON file. Existing courses are matched by course_code and updated. New ones are created. Instructor link restored if the instructor email exists.
          </div>
          <div>
            <button id="importCoursesBtn" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-green-600 text-white text-sm font-semibold shadow hover:bg-green-500 transition">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6M20 20v-6h-6M4 10c4.418 0 8 3.582 8 8m4-12c-4.418 0-8 3.582-8 8"/></svg>
              Import JSON
            </button>
          </div>
          <div id="importResult" class="text-xs font-medium mt-2"></div>
        </div>
      </section>
    </div>
  </div>
  </div>
</div>

<script>
  // Populate course select on first open (lazy load) using existing table + API fallback if needed
  let __EXPORT_SELECT_POPULATED__ = false;
  const __coursesModal = document.getElementById('exportImportModal');
  function openExportImportModal(){
    __coursesModal.classList.remove('hidden');
    if(!__EXPORT_SELECT_POPULATED__) {
      const select = document.getElementById('exportCourseSelect');
      // Gather from current DOM first
      document.querySelectorAll('#coursesTableBody tr[data-course-id]').forEach(tr => {
        const id = tr.getAttribute('data-course-id');
        const title = tr.querySelector('td div.text-sm.font-semibold, .course-title')?.textContent?.trim();
        if(id && title){
          const opt = document.createElement('option');
            opt.value = id; opt.textContent = title; select.appendChild(opt);
        }
      });
      __EXPORT_SELECT_POPULATED__ = true;
    }
  }
  function closeExportImportModal(){ __coursesModal.classList.add('hidden'); }
  // Close on overlay click
  __coursesModal.addEventListener('click', (e)=>{ if(e.target === __coursesModal){ closeExportImportModal(); } });
  // Close on ESC
  document.addEventListener('keydown', (e)=>{ if(e.key === 'Escape' && !__coursesModal.classList.contains('hidden')) closeExportImportModal(); });
  document.querySelectorAll('input[name="exportScope"]').forEach(r => {
    r.addEventListener('change', () => {
      const sel = document.getElementById('exportCourseSelect');
      sel.disabled = (document.querySelector('input[name="exportScope"]:checked').value !== 'single');
    });
  });
  document.getElementById('exportCoursesBtn').addEventListener('click', function(){
    const scope = document.querySelector('input[name="exportScope"]:checked').value;
    let url = "{{ route('admin.courseManagement.export') }}";
    if(scope === 'single') {
      const cid = document.getElementById('exportCourseSelect').value;
      if(!cid){ alert('Choose a course to export.'); return; }
      url += '?course_id=' + encodeURIComponent(cid);
    }
    fetch(url, { credentials:'same-origin' })
      .then(r => r.blob())
      .then(blob => {
        const a = document.createElement('a');
        const dlUrl = URL.createObjectURL(blob);
        a.href = dlUrl; a.download = 'courses-export-' + new Date().toISOString().replace(/[:T]/g,'-').slice(0,19) + (scope==='single' ? '-single' : '-all') + '.json';
        document.body.appendChild(a); a.click(); a.remove(); setTimeout(()=>URL.revokeObjectURL(dlUrl), 1000);
      })
      .catch(()=>alert('Export failed.'));
  });
  document.getElementById('exportCoursesExcelBtn').addEventListener('click', function(){
    // Always exports ALL courses (full dataset, ignoring current filters)
    const btn = this;
    btn.disabled = true; const original = btn.textContent; btn.textContent = 'Preparing...';
    fetch("{{ route('admin.courseManagement.exportExcel') }}", { credentials:'same-origin' })
      .then(r => {
        if(!r.ok){ throw new Error('HTTP '+r.status); }
        return r.blob();
      })
      .then(blob => {
        const a = document.createElement('a');
        const dlUrl = URL.createObjectURL(blob);
        a.href = dlUrl; a.download = 'courses-export-' + new Date().toISOString().replace(/[:T]/g,'-').slice(0,19) + '.xlsx';
        document.body.appendChild(a); a.click(); a.remove(); setTimeout(()=>URL.revokeObjectURL(dlUrl), 1500);
      })
      .catch(()=>alert('Excel export failed.'))
      .finally(()=>{ btn.disabled=false; btn.textContent = original; });
  });
  document.getElementById('importCoursesBtn').addEventListener('click', function(){
    const input = document.getElementById('importFileInput');
    if(!input.files.length){ alert('Select a JSON file first.'); return; }
    const fd = new FormData();
    fd.append('file', input.files[0]);
    fetch("{{ route('admin.courseManagement.import') }}", { method:'POST', body: fd, headers:{ 'X-CSRF-TOKEN':'{{ csrf_token() }}' }, credentials:'same-origin' })
      .then(r => r.json())
      .then(data => {
        const res = document.getElementById('importResult');
        if(!data.success){ res.className='text-xs font-medium text-red-600'; res.textContent = data.message || 'Import failed.'; return; }
        const s = data.summary;
        res.className='text-xs font-medium text-green-600';
        res.textContent = `Import ${data.mode==='single'?'(single restore)':'(bulk add)'} OK. Created: ${s.created}, Skipped Existing: ${s.skipped_existing}, Errors: ${s.errors.length}`;
        // Refresh table (AJAX) so new courses appear without reload if global triggerServerFilter exists
        if(typeof triggerServerFilter === 'function'){ try { triggerServerFilter(); } catch(e){} }
      })
      .catch(()=>{ const res = document.getElementById('importResult'); res.className='text-xs font-medium text-red-600'; res.textContent='Import failed.'; });
  });
</script>
