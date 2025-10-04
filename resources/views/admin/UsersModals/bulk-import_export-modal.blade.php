

<!-- Bulk Import/Export Users Modal -->
<div id="bulkImportModal" role="dialog" aria-modal="true" class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm hidden z-50">
    <div class="min-h-screen w-full flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-[92vw] max-w-lg sm:max-w-xl mx-4 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
            <h3 class="text-lg font-semibold">Export / Import Users</h3>
            <button type="button" data-modal-close class="p-2 rounded-lg hover:bg-white/20 transition" aria-label="Close"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <div class="p-6 space-y-8 max-h-[70vh] overflow-y-auto">
            <!-- Export Section -->
            <section>
                <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wide mb-3">Export</h4>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <button id="exportUsersJsonBtn" type="button" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-semibold shadow hover:bg-indigo-500 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/></svg>
                            Export JSON
                        </button>
                        <button id="exportUsersExcelBtn" type="button" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-slate-200 text-slate-700 text-sm font-semibold shadow hover:bg-slate-300 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16l4 4 4-4M12 12v8m8-4a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Export Excel
                        </button>
                    </div>
                    <div class="text-xs text-slate-500 leading-relaxed bg-slate-50 p-3 rounded-lg">
                        Exports users in the current scope. Super Admins are excluded for School Admins. Super Admin scope uses the active school when selected.
                    </div>
                </div>
            </section>
            <hr/>
            <!-- Import Section -->
            <section>
                <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wide mb-3">Import</h4>
                <div class="space-y-4">
                    <div>
                        <input type="file" id="importUsersFile" accept="application/json,.json" class="block w-full text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"/>
                    </div>
                    <div class="text-xs text-slate-500 leading-relaxed bg-slate-50 p-3 rounded-lg">
                        Import users from an exported JSON. Existing emails are skipped. Super Admins can import all roles; School Admins cannot import Super Admins.
                    </div>
                    <div>
                        <button id="importUsersBtn" type="button" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-green-600 text-white text-sm font-semibold shadow hover:bg-green-500 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6M20 20v-6h-6M4 10c4.418 0 8 3.582 8 8m4-12c-4.418 0-8 3.582-8 8"/></svg>
                            Import JSON
                        </button>
                    </div>
                    <div id="importUsersResult" class="text-xs font-medium mt-2"></div>
                </div>
            </section>
            <hr/>
            <!-- Legacy CSV/Excel Import -->
            <section>
                <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wide mb-3">Legacy CSV/Excel Import</h4>
                <form action="{{ route('admin.users.bulk-import') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div>
                        <input type="file" name="file" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" class="border border-gray-300 rounded-lg px-3 py-2 w-full bg-white" required>
                    </div>
                    <p class="text-xs text-gray-500">
                        Required columns: <strong>Name, Email, Password</strong>. Optional: <strong>Role, Status</strong>.
                    </p>
                    <div class="flex justify-end gap-2">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Import CSV/Excel</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function(){
        const modal = document.getElementById('bulkImportModal');
        const closeEls = modal?.querySelectorAll('[data-modal-close]') || [];
        // Close helpers
        function closeBulkImportModal(){ modal?.classList.add('hidden'); }
        // click X
        closeEls.forEach(btn => btn.addEventListener('click', closeBulkImportModal));
        // click outside content
        modal?.addEventListener('click', (e) => { if(e.target === modal){ closeBulkImportModal(); } });
        // ESC to close
        document.addEventListener('keydown', (e)=>{ if(e.key === 'Escape' && !modal.classList.contains('hidden')) closeBulkImportModal(); });

        const exportJsonBtn = document.getElementById('exportUsersJsonBtn');
        const exportExcelBtn = document.getElementById('exportUsersExcelBtn');
        const importBtn = document.getElementById('importUsersBtn');
        const importInput = document.getElementById('importUsersFile');
        const importRes = document.getElementById('importUsersResult');

        if(exportJsonBtn){
            exportJsonBtn.addEventListener('click', function(){
                // Preserve current filters in query if present (role/status)
                const params = new URLSearchParams(window.location.search);
                const url = '{{ route('admin.userManagement.export') }}' + (params.toString()? ('?'+params.toString()) : '');
                fetch(url, { credentials: 'same-origin' })
                    .then(r => r.blob())
                    .then(blob => { const a = document.createElement('a'); const dl = URL.createObjectURL(blob); a.href = dl; a.download = 'users-export-' + new Date().toISOString().replace(/[:T]/g,'-').slice(0,19) + '.json'; document.body.appendChild(a); a.click(); a.remove(); setTimeout(()=>URL.revokeObjectURL(dl), 1000); })
                    .catch(()=>alert('Export failed.'));
            });
        }
        if(exportExcelBtn){
            exportExcelBtn.addEventListener('click', function(){
                const btn = this; btn.disabled = true; const orig = btn.textContent; btn.textContent = 'Preparing...';
                fetch('{{ route('admin.userManagement.exportExcel') }}', { credentials: 'same-origin' })
                    .then(r => { if(!r.ok) throw new Error('HTTP '+r.status); return r.blob(); })
                    .then(blob => { const a=document.createElement('a'); const dl=URL.createObjectURL(blob); a.href=dl; a.download='users-export-'+ new Date().toISOString().replace(/[:T]/g,'-').slice(0,19)+'.xlsx'; document.body.appendChild(a); a.click(); a.remove(); setTimeout(()=>URL.revokeObjectURL(dl),1500); })
                    .catch(()=>alert('Excel export failed.'))
                    .finally(()=>{ btn.disabled=false; btn.textContent = orig; });
            });
        }
        if(importBtn){
            importBtn.addEventListener('click', function(){
                if(!importInput.files.length){ alert('Select a JSON file first.'); return; }
                const fd = new FormData(); fd.append('file', importInput.files[0]);
                fetch('{{ route('admin.userManagement.import') }}', { method:'POST', body: fd, headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, credentials:'same-origin' })
                    .then(r => r.json())
                    .then(data => {
                        if(!data.success){ importRes.className='text-xs font-medium text-red-600'; importRes.textContent = data.message || 'Import failed.'; return; }
                        const s = data.summary || {}; importRes.className='text-xs font-medium text-green-600'; importRes.textContent = `Created: ${s.created||0}, Skipped Existing: ${s.skipped||0}, Errors: ${(s.errors||[]).length}`;
                        // Optionally refresh page to see new users
                        setTimeout(()=>{ try { window.location.reload(); } catch(e){} }, 800);
                    })
                    .catch(()=>{ importRes.className='text-xs font-medium text-red-600'; importRes.textContent = 'Import failed.'; });
            });
        }
    });
</script>
