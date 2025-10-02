<x-layoutAdmin>
    <main class="flex-1 overflow-y-auto p-4 md:p-8">
    <div class="w-full">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-8 text-white shadow-xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-4xl font-bold mb-2">Course Management</h1>
                            <p class="text-indigo-100 text-lg">Manage all courses in the system with advanced filtering and controls</p>
                        </div>
                        <div class="hidden md:block">
                            <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4">
                                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            <!-- Search & Filters Card -->
            <div class="bg-white rounded-2xl p-6 shadow-xl border border-gray-100 mb-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <!-- Left side: Search and filters -->
                    <div class="flex-1 flex flex-col sm:flex-row gap-4 items-stretch sm:items-center">
                        <div class="flex-1">
                            <input id="courseSearch" name="q" type="text" value="{{ request('q', '') }}" 
                                placeholder="Search courses by name, instructor, or program..." 
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 shadow-sm">
                        </div>
                        
                        <div class="flex gap-4">
                            <select id="departmentFilter" class="px-4 py-3 border-2 border-gray-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 shadow-sm hover:border-indigo-300 hover:shadow-md text-slate-700 font-medium min-w-[160px]">
                                <option value="" @if(!request()->has('department') || request('department')=='') selected @endif>All Departments</option>
                                @foreach(($departments ?? []) as $dept)
                                    <option value="{{ $dept }}" @if(request('department')===$dept) selected @endif>{{ $dept }}</option>
                                @endforeach
                            </select>

                            <select id="programFilter" class="px-4 py-3 border-2 border-gray-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 shadow-sm hover:border-indigo-300 hover:shadow-md text-slate-700 font-medium min-w-[160px]">
                                <option value="" selected>All Programs</option>
                            </select>

                            <select id="statusFilter" class="px-4 py-3 border-2 border-gray-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 shadow-sm hover:border-indigo-300 hover:shadow-md text-slate-700 font-medium min-w-[140px]">
                                <option value="" @if(!request()->has('status') || request('status')=='') selected @endif>All Status</option>
                                <option value="published" @if(request('status')==='published') selected @endif>Published</option>
                                <option value="draft" @if(request('status')==='draft') selected @endif>Draft</option>
                                <option value="archived" @if(request('status')==='archived') selected @endif>Archived</option>
                            </select>
                        </div>
                    </div>

                    <!-- Right side: Create button -->
                    <div class="flex items-center ml-6">
                        <button type="button" onclick="openCreateModal()" 
                            class="inline-flex items-center gap-3 px-6 py-3 rounded-xl bg-gradient-to-r from-green-600 to-emerald-600 text-white font-semibold hover:from-green-700 hover:to-emerald-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span>Create New Course</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- All Courses Header -->
            <div class="mb-4 flex items-center justify-between gap-4 flex-wrap">
                <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
                    <div class="w-1 h-8 bg-gradient-to-b from-indigo-600 to-purple-600 rounded-full"></div>
                    All Courses
                    <span class="text-sm font-normal text-slate-500 ml-2">
                        @if(isset($courses) && method_exists($courses, 'total'))
                            ({{ $courses->total() }} total)
                        @endif
                    </span>
                </h2>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="openExportImportModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white border border-slate-200 shadow-sm text-sm font-medium text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Export / Import
                    </button>
                </div>
            </div>

            <!-- Courses Table Card -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">

                <div class="p-6">
                    <div class="w-full">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b-2 border-gray-100">
                                    <th class="px-6 py-4 text-left text-sm font-bold text-slate-700 uppercase tracking-wider">Course Name</th>
                                    <th class="px-6 py-4 text-left text-sm font-bold text-slate-700 uppercase tracking-wider">Instructor</th>
                                    <th class="px-6 py-4 text-left text-sm font-bold text-slate-700 uppercase tracking-wider">Students</th>
                                    <th class="px-6 py-4 text-left text-sm font-bold text-slate-700 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-left text-sm font-bold text-slate-700 uppercase tracking-wider">Last Updated</th>
                                    <th class="px-6 py-4 text-center text-sm font-bold text-slate-700 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="coursesTableBody" class="divide-y divide-gray-100">
                                @forelse($courses as $course)
                                    @include('admin.courses.partials.course-row', ['course' => $course])
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253z"></path>
                                                </svg>
                                            </div>
                                            <h3 class="text-lg font-semibold text-slate-700 mb-2">No courses found</h3>
                                            <p class="text-slate-500 text-center max-w-md">No courses match your current filters. Try adjusting your search criteria or create a new course.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                                {{-- row shown when client-side filtering yields no results --}}
                                <tr id="noResultsRow" class="hidden">
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                </svg>
                                            </div>
                                            <h3 class="text-lg font-semibold text-slate-700 mb-2">No matches found</h3>
                                            <p class="text-slate-500 text-center max-w-md">No courses match your search or filters. Try different criteria.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                {{-- Pagination (server-side) --}}
                @if(method_exists($courses, 'links'))
                    <div class="pagination-container px-6 py-4 bg-white border-t border-gray-100">
                        <div class="flex items-center justify-end" id="paginationLinks">{!! $courses->links() !!}</div>
                    </div>
                    <div id="paginationStatus" class="px-6 pb-5 pt-2 text-xs text-slate-500" data-total="{{ $courses->total() }}">
                        Showing {{ $courses->firstItem() ?? 0 }} to {{ $courses->lastItem() ?? 0 }} of {{ $courses->total() }} courses
                    </div>
                @endif
            </div>

            {{-- include modals (partials live in this folder) --}}
            {{-- show-modal removed; direct navigation to details page now --}}
            @include('admin.courses.edit-modal')
            @include('admin.courses.create-modal')
            @include('admin.courses.export-import-modal')

            {{-- keep delete modal here for simplicity --}}
            <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
                <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-md w-full mx-4 border-t-8 border-red-600 relative">
                    <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-red-600 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg">
                        <i class="fas fa-exclamation-triangle fa-lg"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2 text-red-700 text-center mt-8">Delete Course</h3>
                    <p class="text-slate-600 mb-4 text-center">Are you sure you want to delete <span id="deleteCourseTitle" class="font-bold"></span>? This action cannot be undone.</p>
                    <div id="deleteStep1">
                        <button onclick="request2FACode()" class="w-full bg-gradient-to-r from-red-600 to-red-400 text-white font-bold py-2 px-4 rounded-lg mb-2">Send Verification Code to Email</button>
                        <button onclick="closeDeleteModal()" class="w-full bg-gray-100 text-gray-800 py-2 px-4 rounded-lg">Cancel</button>
                    </div>
                    <div id="deleteStep2" class="hidden mt-3">
                        <label class="block text-slate-700 font-semibold mb-2">Enter Verification Code</label>
                        <input type="text" id="twoFACodeInput" class="w-full px-4 py-2 border rounded-lg mb-4 focus:outline-none focus:ring-2 focus:ring-red-600" placeholder="Enter code">
                        <div id="twoFAError" class="text-red-500 mt-2 text-center"></div>
                        <div class="flex gap-2">
                            <button onclick="submit2FACode()" class="flex-1 bg-gradient-to-r from-red-600 to-red-400 text-white py-2 rounded-lg">Verify & Delete</button>
                            <button onclick="closeDeleteModal()" class="flex-1 bg-gray-100 text-gray-800 py-2 rounded-lg">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <style>
            /* Pagination refined (pill style) */
            .pagination-container nav { display:inline-block; }
            .pagination-container nav ul { display:flex; align-items:center; gap:.65rem; padding:0; margin:0; }
            .pagination-container nav ul li { list-style:none; }
            .pagination-container nav a,
            .pagination-container nav span[aria-current="page"] {
                position:relative; display:inline-flex; align-items:center; justify-content:center;
                min-width:40px; height:40px; padding:0 14px; border-radius:14px;
                font-size:.7rem; font-weight:600; letter-spacing:.5px; text-decoration:none;
                background:#ffffff; color:#475569; border:1px solid #e2e8f0;
                box-shadow:0 2px 4px rgba(0,0,0,0.04),0 1px 2px rgba(0,0,0,0.04);
                transition:all .25s ease; user-select:none;
            }
            /* Hover (non-active) adopts subtle gradient outline + text tint */
            .pagination-container nav a:hover:not([aria-disabled="true"]):not([aria-current]) {
                color:#4f46e5; border-color:#b4c2ff; box-shadow:0 6px 14px -4px rgba(99,102,241,.35); transform:translateY(-2px);
                background:#ffffff;
            }
            /* Active page: gradient pill reflecting header theme */
            .pagination-container nav span[aria-current="page"] {
                background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 55%,#9333ea 100%);
                color:#ffffff; border:1px solid #4f46e5;
                box-shadow:0 0 0 2px rgba(99,102,241,0.35),0 8px 18px -8px rgba(99,102,241,0.55);
            }
            .pagination-container nav span[aria-current="page"]::after {
                content:""; position:absolute; inset:0; border-radius:14px; pointer-events:none;
                background:linear-gradient(180deg,rgba(255,255,255,0.15),rgba(255,255,255,0));
            }
            /* Active focus ring */
            .pagination-container nav span[aria-current="page"]:focus-visible {
                outline:0; box-shadow:0 0 0 3px rgba(79,70,229,0.65),0 8px 18px -8px rgba(99,102,241,0.6);
            }
            /* Hover other pages to lightly tint background */
            .pagination-container nav a:not([aria-current]):hover {
                background:linear-gradient(135deg,#ffffff 0%,#f0f4ff 100%);
            }
            .pagination-container nav a[aria-disabled="true"],
            .pagination-container nav span[aria-disabled="true"] { opacity:.35; cursor:not-allowed; background:linear-gradient(#f1f5f9,#f1f5f9) padding-box,linear-gradient(135deg,#e2e8f0,#e2e8f0) border-box; color:#94a3b8; box-shadow:none; }
            .pagination-container nav a:active:not([aria-disabled="true"]) { transform:translateY(0); }
            .pagination-container nav a:focus-visible { outline:0; box-shadow:0 0 0 3px rgba(99,102,241,0.35); }
            .fading { opacity:.45 !important; transition:opacity .25s ease; }
            @keyframes blinkFade { 0%,100% {opacity:1;} 50% {opacity:.25;} }
            .blink-refresh { animation: blinkFade .45s ease-in-out; }
            /* Enhanced dropdown options styling */
            select option {
                padding: 12px 16px !important;
                font-weight: 500 !important;
                color: #334155 !important;
                background-color: #ffffff !important;
                border-bottom: 1px solid #f1f5f9 !important;
                transition: all 0.2s ease !important;
            }
            
            select option:hover {
                background-color: #f8fafc !important;
                background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%) !important;
                color: #4f46e5 !important;
                font-weight: 600 !important;
            }
            
            select option:checked,
            select option:selected {
                background-color: #4f46e5 !important;
                background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%) !important;
                color: #ffffff !important;
                font-weight: 600 !important;
                position: relative !important;
            }
            
            select option:first-child {
                border-top-left-radius: 8px !important;
                border-top-right-radius: 8px !important;
                border-top: none !important;
            }
            
            select option:last-child {
                border-bottom-left-radius: 8px !important;
                border-bottom-right-radius: 8px !important;
                border-bottom: none !important;
            }
            
            /* Enhanced dropdown container */
            select:focus {
                box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1), 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
            }
            
            /* Custom scrollbar for dropdown */
            select::-webkit-scrollbar {
                width: 6px;
            }
            
            select::-webkit-scrollbar-track {
                background: #f1f5f9;
                border-radius: 3px;
            }
            
            select::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 3px;
            }
            
            select::-webkit-scrollbar-thumb:hover {
                background: #94a3b8;
            }

            /* Smooth transitions for table updates */
            #coursesTableBody {
                transition: opacity 0.3s ease-in-out;
            }

            /* Loading spinner animation */
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

            .animate-spin {
                animation: spin 1s linear infinite;
            }
        </style>

        <script>
            // Expose programs for edit modal program select (server-provided in controller)
            window.__ADMIN_PROGRAMS__ = @json($programs ?? []);
            // CSRF header for fetch
            const CSRF_HEADERS = {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            };

            let deleteCourseId = null;
            function confirmDelete(courseId, courseTitle) {
                deleteCourseId = courseId;
                document.getElementById('deleteCourseTitle').textContent = courseTitle;
                document.getElementById('deleteStep1').classList.remove('hidden');
                document.getElementById('deleteStep2').classList.add('hidden');
                document.getElementById('twoFAError').textContent = '';
                document.getElementById('deleteModal').classList.remove('hidden');
            }
            function closeDeleteModal() {
                document.getElementById('deleteModal').classList.add('hidden');
                deleteCourseId = null;
                if (document.getElementById('twoFACodeInput')) document.getElementById('twoFACodeInput').value = '';
                document.getElementById('twoFAError').textContent = '';
            }

            function request2FACode() {
                fetch(`{{ route('admin.request2fa') }}`, { method: 'POST', headers: CSRF_HEADERS, credentials: 'same-origin' })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('deleteStep1').classList.add('hidden');
                        document.getElementById('deleteStep2').classList.remove('hidden');
                    } else {
                        document.getElementById('twoFAError').textContent = data.message || 'Unable to send code.';
                    }
                })
                .catch(() => document.getElementById('twoFAError').textContent = 'Error sending code.');
            }

            function submit2FACode() {
                const code = document.getElementById('twoFACodeInput').value;
                fetch(`{{ route('admin.verify2fa') }}`, { method: 'POST', headers: CSRF_HEADERS, body: JSON.stringify({ code }), credentials: 'same-origin' })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        // perform deletion
                        fetch(`{{ url('/admin/course-management') }}/${deleteCourseId}`, { method: 'DELETE', headers: CSRF_HEADERS, credentials: 'same-origin' })
                        .then(r => r.json())
                        .then(resp => { 
                            if (resp.success) {
                                const row = document.querySelector(`tr[data-course-id='${deleteCourseId}']`);
                                if(row){
                                    row.classList.add('opacity-0','transition','duration-300');
                                    setTimeout(()=>{ row.remove(); updateAfterRowChange && updateAfterRowChange(); },300);
                                }
                                closeDeleteModal();
                            } else {
                                document.getElementById('twoFAError').textContent = 'Delete failed.';
                            }
                        })
                        .catch(() => document.getElementById('twoFAError').textContent = 'Delete failed.');
                    } else {
                        document.getElementById('twoFAError').textContent = data.message || 'Invalid code.';
                    }
                })
                .catch(() => document.getElementById('twoFAError').textContent = 'Error verifying code.');
            }

            // View / Edit modal wiring (modal markup lives in partials)
            function openViewModal(courseId) {
                // include credentials so the session cookie is sent with the AJAX request
                fetch(`{{ url('/admin/course-management') }}/${courseId}`, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then(r => {
                    // handle common auth/permission responses first
                    if (r.status === 401 || r.status === 403) {
                        throw new Error('auth');
                    }
                    // if server returned non-JSON (for example a login/HTML page), throw to catch
                    const ct = r.headers.get('content-type') || '';
                    if (ct.indexOf('application/json') === -1) throw new Error('non-json');
                    return r.json();
                })
                .then(payload => {
                    if (payload && payload.success) {
                        if (window.renderCourseModal) window.renderCourseModal(payload.course);
                    } else {
                        alert('Failed to load course details');
                    }
                })
                .catch((err) => {
                    console.error('openViewModal error:', err);
                    if (err && err.message === 'auth') {
                        alert('Unable to load course: session expired or insufficient permissions. Please log in again.');
                    } else {
                        alert('Failed to load course details');
                    }
                });
            }
            function closeViewModal() { document.getElementById('viewCourseModal').classList.add('hidden'); }

            let editingCourseId = null;
            function openEditModal(courseId) {
                editingCourseId = courseId;
                fetch(`{{ url('/admin/course-management') }}/${courseId}/edit`, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then(r => r.json())
                .then(payload => {
                    const c = payload.course;
                    
                    // Use the new setEditModalValues function if available
                    if (window.setEditModalValues) {
                        window.setEditModalValues(c);
                    } else {
                        // Fallback for old method
                        document.getElementById('modalEditTitle').value = c.title || '';
                        if(document.getElementById('modalEditCode')) document.getElementById('modalEditCode').value = c.course_code || '';
                        document.getElementById('modalEditStatus').value = c.status;
                        if(document.getElementById('modalEditDepartment')) document.getElementById('modalEditDepartment').value = c.department || '';
                        if(document.getElementById('modalEditProgram')) document.getElementById('modalEditProgram').value = c.program_id || '';
                        if(document.getElementById('modalEditDescription')) document.getElementById('modalEditDescription').value = c.description || '';
                        if(document.getElementById('modalEditCredits')) document.getElementById('modalEditCredits').value = c.credits || '';
                    }
                    
                    document.getElementById('modalEditForm').action = `{{ url('/admin/course-management') }}/${courseId}`;
                    document.getElementById('editCourseModal').classList.remove('hidden');
                    document.getElementById('modalEdit2FAStep1').classList.remove('hidden');
                    document.getElementById('modalEdit2FAStep2').classList.add('hidden');
                    document.getElementById('modalEdit2FAError').textContent = '';
                })
                .catch(() => alert('Failed to load course for editing'));
            }
            function closeEditModal() { document.getElementById('editCourseModal').classList.add('hidden'); editingCourseId = null; }

            // ...chooseView removed; modal now contains a "View Full Page" button

            function requestEdit2FACode() {
                fetch(`{{ route('admin.request2fa') }}`, { method: 'POST', headers: CSRF_HEADERS, credentials: 'same-origin' })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('modalEdit2FAStep1').classList.add('hidden');
                        document.getElementById('modalEdit2FAStep2').classList.remove('hidden');
                    } else {
                        document.getElementById('modalEdit2FAError').textContent = data.message || 'Unable to send code.';
                    }
                })
                .catch(() => document.getElementById('modalEdit2FAError').textContent = 'Error sending code.');
            }

            document.addEventListener('DOMContentLoaded', function () {
                // Track last active query params for pagination reuse
                let lastQueryParams = new URLSearchParams(window.location.search);
                const PER_PAGE = 10; // threshold for hiding pagination

                window.updateAfterRowChange = function(){
                    const statusEl = document.getElementById('paginationStatus');
                    if(!statusEl) return;
                    const totalAttr = statusEl.getAttribute('data-total');
                    const visible = document.querySelectorAll('#coursesTableBody tr[data-course-id]').length;
                    // Keep original total, adjust showing range simply (assumes page start at 1)
                    statusEl.innerHTML = `Showing 1 to ${visible} of ${totalAttr || visible} courses`;
                    updatePaginationVisibility();
                };
                function updatePaginationVisibility(){
                    const body = document.getElementById('coursesTableBody');
                    const rows = body ? body.querySelectorAll('tr[data-course-id]').length : 0;
                    const pagWrap = document.querySelector('.pagination-container');
                    const statusLine = document.getElementById('paginationStatus');
                    const linksWrap = document.getElementById('paginationLinks');
                    const deptVal = document.getElementById('departmentFilter')?.value || '';
                    const progVal = document.getElementById('programFilter')?.value || '';
                    const statusVal = document.getElementById('statusFilter')?.value || '';
                    const filtersApplied = !!(deptVal || progVal || statusVal); // default = all empty
                    let hasSecondPage = false;
                    if(linksWrap){
                        hasSecondPage = !!linksWrap.querySelector('a[href*="page=2"], a[href*="page=3"], a[href*="page=4"]');
                    }
                    if(rows === 0){
                        if(pagWrap) pagWrap.classList.add('hidden');
                        if(statusLine) statusLine.classList.add('hidden');
                        return;
                    }
                    // If no filters applied (All Departments/Programs/Status) always show pagination + status
                    if(!filtersApplied){
                        if(pagWrap) pagWrap.classList.remove('hidden');
                        if(statusLine) statusLine.classList.remove('hidden');
                        return;
                    }
                    // Filters applied: hide only when single page (<= per page and no second page)
                    if(rows <= PER_PAGE && !hasSecondPage){
                        if(pagWrap) pagWrap.classList.add('hidden');
                        if(statusLine) statusLine.classList.add('hidden');
                    } else {
                        if(pagWrap) pagWrap.classList.remove('hidden');
                        if(statusLine) statusLine.classList.remove('hidden');
                    }
                }
                // Initial check
                updatePaginationVisibility();
                const saveBtn = document.getElementById('modalVerifySaveBtn');
                if (saveBtn) {
                    saveBtn.addEventListener('click', function () {
                        const code = document.getElementById('modalEdit2FACodeInput').value;
                        fetch(`{{ route('admin.verify2fa') }}`, { method: 'POST', headers: CSRF_HEADERS, body: JSON.stringify({ code }), credentials: 'same-origin' })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                // Submit via fetch so we can stay on page and update row without reload
                                const form = document.getElementById('modalEditForm');
                                const fd = new FormData(form);
                                fetch(form.action, {
                                    method: 'POST',
                                    headers: { 'Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}' },
                                    body: (()=>{ fd.append('_method','PUT'); return fd; })(),
                                    credentials:'same-origin'
                                }).then(r=>r.json())
                                .then(data=>{
                                    if(data.success && data.course){
                                        // Update row values in table if present
                                        try{
                                            const row = document.querySelector(`tr[data-course-id='${data.course.id}']`);
                                            if(row){
                                                const c = data.course;
                                                // Update the row attributes
                                                row.setAttribute('data-program-id', c.program_id || '');
                                                row.setAttribute('data-status', c.status || '');
                                                
                                                // Update the row content with the same structure as create modal
                                                row.innerHTML = `
                                                    <td class="px-6 py-5">
                                                        <div class="flex items-start">
                                                            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center mr-4 flex-shrink-0 mt-1">
                                                                <svg class="w-5 h-5 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253z"></path>
                                                                </svg>
                                                            </div>
                                                            <div class="flex-1 min-w-0">
                                                                <div class="text-sm font-semibold text-slate-800 leading-5 line-clamp-2 break-words">${c.title}</div>
                                                                <div class="text-xs text-slate-500 mt-1 truncate">${c.course_code || 'No code'}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-5">
                                                        <div class="flex items-center">
                                                            <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center mr-3">
                                                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                                </svg>
                                                            </div>
                                                            <div>
                                                                <div class="text-sm font-medium text-slate-700">${c.instructor ? c.instructor.name : 'N/A'}</div>
                                                                <div class="text-xs text-slate-500">${c.department || 'No Department'}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-5">
                                                        <div class="flex items-center">
                                                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                                </svg>
                                                            </div>
                                                            <span class="text-sm font-semibold text-slate-700">0</span>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-5">
                                                        ${
                                                            c.status === 'published' ? 
                                                            '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200"><div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>Published</span>' : 
                                                            (c.status === 'draft' ? 
                                                            '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 border border-yellow-200"><div class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></div>Draft</span>' : 
                                                            '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200"><div class="w-2 h-2 bg-gray-500 rounded-full mr-2"></div>Archived</span>')
                                                        }
                                                    </td>
                                                    <td class="px-6 py-5 text-sm text-slate-600">${new Date(c.updated_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</td>
                                                    <td class="px-6 py-5">
                                                        <div class="flex items-center justify-center space-x-2">
                                                            <a href="/admin/course-management/${c.id}/details-page" data-id="${c.id}" 
                                                                class="js-view-inline inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-slate-100 border border-slate-200 text-slate-700 hover:bg-slate-200 hover:border-slate-300 transition-all duration-150 text-xs font-medium">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                                </svg>
                                                                View
                                                            </a>
                                                            <button onclick="openEditModal(${c.id})" 
                                                                class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-blue-100 border border-blue-200 text-blue-700 hover:bg-blue-200 hover:border-blue-300 transition-all duration-150 text-xs font-medium">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                                </svg>
                                                                Edit
                                                            </button>
                                                            <button onclick="confirmDelete(${c.id}, '${(c.title||'').replace(/'/g, "\\'") }')" 
                                                                class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-red-100 border border-red-200 text-red-700 hover:bg-red-200 hover:border-red-300 transition-all duration-150 text-xs font-medium">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                                </svg>
                                                                Delete
                                                            </button>
                                                        </div>
                                                    </td>
                                                `;
                                            }
                                        }catch(e){
                                            console.error('Error updating row:', e);
                                        }
                                        closeEditModal();
                                    } else {
                                        document.getElementById('modalEdit2FAError').textContent = (data.errors? Object.values(data.errors).flat().join(', '): (data.message||'Update failed.'));
                                    }
                                }).catch(()=>{
                                    document.getElementById('modalEdit2FAError').textContent = 'Network error saving course.';
                                });
                            } else {
                                document.getElementById('modalEdit2FAError').textContent = data.message || 'Invalid code.';
                            }
                        })
                        .catch(() => document.getElementById('modalEdit2FAError').textContent = 'Error verifying code.');
                    });
                }
                // --- Client-side search & filter for the courses table ---
                const courseSearch = document.getElementById('courseSearch');
                const programFilter = document.getElementById('programFilter');
                const departmentFilter = document.getElementById('departmentFilter');
                const statusFilter = document.getElementById('statusFilter');
                const tableBody = document.getElementById('coursesTableBody');
                const noResultsRow = document.getElementById('noResultsRow');

                function normalizeText(s) { return (s || '').toString().toLowerCase(); }

                // For large datasets, use server-side filtering/pagination.
                // Use AJAX to update table content without page refresh
                let debounceTimer = null;
                function triggerServerFilter() {
                    const q = courseSearch ? courseSearch.value.trim() : '';
                    const program = programFilter ? programFilter.value : '';
                    const status = statusFilter ? statusFilter.value : '';
                    const department = departmentFilter ? departmentFilter.value : '';
                    
                    // Build query parameters
                    const params = new URLSearchParams();
                    if (q) params.set('q', q);
                    if (program) params.set('program', program);
                    if (status) params.set('status', status);
                    if (department) params.set('department', department);
                    
                    // Show loading state
                    const tableBody = document.getElementById('coursesTableBody');
                    if (tableBody) tableBody.classList.add('blink-refresh');
                    
                    // Make AJAX request to get filtered results
                    fetch(`{{ route('admin.courseManagement') }}?${params.toString()}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.html) {
                            // Update table body with new content with smooth transition
                            if (tableBody) {
                                tableBody.innerHTML = data.html;
                                bindInlineView();
                            }
                            
                            // Update URL without page refresh
                            const newUrl = window.location.pathname + (params.toString() ? ('?' + params.toString()) : '');
                            window.history.replaceState({}, '', newUrl);
                            
                            // Update pagination if provided
                            if (data.pagination) {
                                const paginationLinks = document.getElementById('paginationLinks');
                                if (paginationLinks) paginationLinks.innerHTML = addPrevNextIfMissing(data.pagination);
                            }
                            lastQueryParams = params;
                            updatePaginationVisibility();
                        } else {
                            // Handle case where no results or error
                            if (tableBody) tableBody.innerHTML = '<tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No courses found matching your criteria.</td></tr>';
                            updatePaginationVisibility();
                        }
                    })
                    .catch(error => {
                        console.error('Filter error:', error);
                        // Show error message
                        if (tableBody) tableBody.innerHTML = '<tr><td colspan="6" class="px-6 py-8 text-center text-red-500">Error loading courses. Please try again.</td></tr>';
                        
                        // Fallback to page refresh after a delay
                        setTimeout(() => {
                            const newUrl = window.location.pathname + (params.toString() ? ('?' + params.toString()) : '');
                            window.location.href = newUrl;
                        }, 2000);
                    });
                }

                // Department -> Program mapping (program names must match option data-name values)
                const DEPT_PROGRAMS = {
                    'CCS': ['BSIT','BSCS','BSIS','BLIS'],
                    'CHS': ['BSN','BSM'],
                    'CAS': ['BAELS','BS Math','BS Applied Math','BS DevCo','BSPA','BAHS'],
                    'CEA': ['BSCE','BSME','BSEE','BSECE'],
                    'CTHBM': ['BSOA','BSTM','BSHM','BSEM'],
                    'CTDE': ['BPEd','BCAEd','BSNEd','BTVTEd']
                };

                function rebuildProgramOptions() {
                    if(!programFilter) return;
                    const currentDept = departmentFilter ? departmentFilter.value : '';
                    const allowed = DEPT_PROGRAMS[currentDept] || [];
                    const selectedProgram = @json(request('program'));
                    // preserve first option (All Programs)
                    programFilter.innerHTML = '<option value="">All Programs</option>';
                    if(!allowed.length){
                        return; // no department chosen or no mapping; only All Programs
                    }
                    allowed.forEach(code => {
                        const opt = document.createElement('option');
                        opt.value = code; // use program code as value
                        opt.textContent = code;
                        if(selectedProgram === code) opt.selected = true;
                        programFilter.appendChild(opt);
                    });
                }

                rebuildProgramOptions();

                // When department changes (before triggering server filter) reset program selection to ensure consistent query
                if(departmentFilter){
                    departmentFilter.addEventListener('change', () => {
                        // reset program select in UI (server filter will run via shared handler below)
                        if(programFilter){ programFilter.value=''; }
                        rebuildProgramOptions();
                    });
                }

                [courseSearch, departmentFilter, programFilter, statusFilter].forEach(el => {
                    if (!el) return;
                    el.addEventListener('input', () => {
                        clearTimeout(debounceTimer);
                        debounceTimer = setTimeout(triggerServerFilter, 300); // Reduced from 500ms for faster response
                    });
                    el.addEventListener('change', () => {
                        clearTimeout(debounceTimer);
                        debounceTimer = setTimeout(triggerServerFilter, 150); // Reduced from 250ms for faster response
                    });
                });
                // Pagination click interception (AJAX)
                document.addEventListener('click', function(e){
                    const link = e.target.closest('.pagination-container a');
                    if(!link) return;
                    const href = link.getAttribute('href');
                    if(!href || href === '#') return;
                    e.preventDefault();
                    const url = new URL(href, window.location.origin);
                    const params = new URLSearchParams(url.search);
                    // merge filters
                    lastQueryParams.forEach((v,k)=>{ if(!params.has(k)) params.set(k,v); });
                    tableBody.classList.add('blink-refresh');
                    fetch(`{{ route('admin.courseManagement') }}?${params.toString()}`, { headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}, credentials:'same-origin' })
                        .then(r=>r.json())
                        .then(data=>{
                            if(data.success){
                                tableBody.innerHTML = data.html;
                                bindInlineView();
                                if(data.pagination){
                                    const paginationLinks = document.getElementById('paginationLinks');
                                    if(paginationLinks) paginationLinks.innerHTML = addPrevNextIfMissing(data.pagination);
                                }
                                if(data.status_html){
                                    const statusEl = document.getElementById('paginationStatus');
                                    if(statusEl) statusEl.innerHTML = data.status_html;
                                }
                                window.history.pushState({},'', window.location.pathname + '?' + params.toString());
                                lastQueryParams = params;
                                updatePaginationVisibility();
                            }
                        })
                        .catch(err=>console.error('Pagination AJAX error', err))
                        .finally(()=>setTimeout(()=>tableBody.classList.remove('blink-refresh'),450));
                });

                function addPrevNextIfMissing(html){
                    try{
                        if(/Prev|Next/i.test(html)) return html; // already present
                        const currentMatch = html.match(/aria-current="page"[^>]*>(\d+)</);
                        let current = currentMatch? parseInt(currentMatch[1]):1;
                        const pageNums = [...html.matchAll(/>\s*(\d+)\s*<\/a>/g)].map(m=>parseInt(m[1]));
                        const total = pageNums.length? Math.max(...pageNums): current;
                        const firstHref = html.match(/href="([^"]+page=\d+[^"}]*)"/);
                        const base = firstHref? firstHref[1].replace(/page=\d+/, 'page='): '?page=';
                        const prev = current>1? `<li><a href="${base+(current-1)}" aria-label="Previous Page">Prev</a></li>`: `<li><span aria-disabled="true">Prev</span></li>`;
                        const next = current<total? `<li><a href="${base+(current+1)}" aria-label="Next Page">Next</a></li>`: `<li><span aria-disabled="true">Next</span></li>`;
                        return html.replace(/<ul[^>]*>/,'$&'+prev).replace(/<\/ul>/, next+'$&');
                    }catch(e){ console.warn('addPrevNextIfMissing error', e); return html; }
                }

                function bindInlineView(){
                    // delegate for view links is global below; nothing extra now
                }
                bindInlineView();

                // Delegate view link to openViewModal instead of full navigation
                document.getElementById('coursesTableBody').addEventListener('click', function(e){
                    const viewLink = e.target.closest('a.js-view-inline');
                    if(!viewLink) return;
                    e.preventDefault();
                    const id = viewLink.getAttribute('data-id');
                    if(id) openViewModal(id);
                });
            });
        </script>
    </main>
</x-layoutAdmin>

