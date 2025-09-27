<x-layoutAdmin>
    <main class="flex-1 p-4 md:p-8 bg-gray-50 overflow-y-auto">
        <div class="w-full max-w-7xl mx-auto">
            <header class="mb-8 md:flex md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900">Course Management</h1>
                    <p class="text-slate-500 mt-1 max-w-xl">
                        Manage all courses in the system. View, edit, or delete courses with confirmation and 2FA.
                    </p>
                </div>
                <button type="button" onclick="openCreateModal()" class="mt-4 md:mt-0 inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-indigo-600 text-white font-semibold shadow-lg hover:bg-indigo-700 transition-colors duration-300">
                    <i class="fas fa-plus"></i>
                    <span>Create New Course</span>
                </button>
            </header>

            <section class="bg-white rounded-2xl p-6 shadow-xl border border-gray-200">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                    <div class="flex-1 flex flex-wrap gap-4 items-center">
                        <input id="courseSearch" name="q" type="text" value="{{ request('q', '') }}" placeholder="Search by Name or ID..." class="flex-1 min-w-[200px] px-5 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-shadow" />

                        <div class="flex gap-4">
                            <select id="programFilter" class="px-5 py-2.5 border border-gray-300 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-shadow">
                                @php $programsList = $programs ?? collect(); @endphp
                                <option value="" @if(!request()->has('program') || request('program')=='') selected @endif>All Programs</option>
                                @foreach($programsList as $prog)
                                    <option value="{{ $prog->id }}" @if((string)request('program','') === (string)$prog->id) selected @endif>{{ $prog->name }}</option>
                                @endforeach
                            </select>

                            <select id="statusFilter" class="px-5 py-2.5 border border-gray-300 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-shadow">
                                <option value="" @if(!request()->has('status') || request('status')=='') selected @endif>All Status</option>
                                <option value="published" @if(request('status')==='published') selected @endif>Published</option>
                                <option value="draft" @if(request('status')==='draft') selected @endif>Draft</option>
                                <option value="archived" @if(request('status')==='archived') selected @endif>Archived</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="w-full overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Course Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Instructor</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Students</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Last Updated</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="coursesTableBody" class="bg-white divide-y divide-gray-100">
                            @forelse($courses as $course)
                            <tr class="hover:bg-gray-50 transition-colors duration-200" data-course-id="{{ $course->id }}" data-program-id="{{ $course->program_id }}" data-status="{{ $course->status }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">{{ $course->title }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ optional($course->instructor)->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $course->students_count ?? 0 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($course->status === 'published')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Published</span>
                                    @elseif($course->status === 'draft')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Draft</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">Archived</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $course->updated_at->format('Y-m-d') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm space-x-2">
                                    <button onclick="openViewModal({{ $course->id }})" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors duration-200">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button onclick="openEditModal({{ $course->id }})" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors duration-200">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button onclick="confirmDelete({{ $course->id }}, '{{ addslashes($course->title) }}')" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 transition-colors duration-200">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-slate-500">No courses found.</td>
                            </tr>
                            @endforelse
                            <tr id="noResultsRow" class="hidden">
                                <td colspan="6" class="px-6 py-8 text-center text-slate-500">No courses match your search or filters.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @if(method_exists($courses, 'links'))
                <div class="mt-6 flex flex-col md:flex-row items-center justify-between">
                    <div class="text-sm text-slate-600 mb-4 md:mb-0">
                        Showing {{ $courses->firstItem() ?? 0 }} to {{ $courses->lastItem() ?? 0 }} of {{ $courses->total() }} courses
                    </div>
                    <div>{!! $courses->links() !!}</div>
                </div>
                @endif
            </section>

            {{-- Modals (Keep these as they were or apply the same styling principles) --}}
            @include('admin.courses.show-modal')
            @include('admin.courses.edit-modal')
            @include('admin.courses.create-modal')

            {{-- Delete Modal --}}
            <div id="deleteModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 z-50 hidden flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-auto relative border-t-8 border-red-600 transform transition-all scale-95 duration-300 ease-out">
                    <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-red-600 text-white rounded-full w-16 h-16 flex items-center justify-center shadow-lg">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-2 text-red-700 text-center mt-8">Delete Course</h3>
                    <p class="text-slate-600 mb-6 text-center">Are you sure you want to delete <span id="deleteCourseTitle" class="font-bold text-slate-800"></span>? This action cannot be undone.</p>
                    <div id="deleteStep1">
                        <button onclick="request2FACode()" class="w-full bg-gradient-to-r from-red-600 to-red-500 text-white font-bold py-3 px-4 rounded-xl shadow-md hover:from-red-700 hover:to-red-600 transition-colors mb-2">
                            Send Verification Code to Email
                        </button>
                        <button onclick="closeDeleteModal()" class="w-full bg-gray-200 text-gray-800 py-3 px-4 rounded-xl font-semibold hover:bg-gray-300 transition-colors">
                            Cancel
                        </button>
                    </div>
                    <div id="deleteStep2" class="hidden mt-4">
                        <label class="block text-slate-700 font-semibold mb-2">Enter Verification Code</label>
                        <input type="text" id="twoFACodeInput" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg mb-4 focus:outline-none focus:ring-2 focus:ring-red-600 transition-shadow" placeholder="Enter code">
                        <div id="twoFAError" class="text-red-500 text-center text-sm mb-4"></div>
                        <div class="flex gap-3">
                            <button onclick="submit2FACode()" class="flex-1 bg-gradient-to-r from-red-600 to-red-500 text-white py-2.5 rounded-xl font-bold hover:from-red-700 hover:to-red-600 transition-colors">
                                Verify & Delete
                            </button>
                            <button onclick="closeDeleteModal()" class="flex-1 bg-gray-200 text-gray-800 py-2.5 rounded-xl font-semibold hover:bg-gray-300 transition-colors">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
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
                fetch(`{{ route('admin.request-2fa') }}`, { method: 'POST', headers: CSRF_HEADERS, credentials: 'same-origin' })
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
                fetch(`{{ route('admin.verify-2fa') }}`, { method: 'POST', headers: CSRF_HEADERS, body: JSON.stringify({ code }), credentials: 'same-origin' })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        fetch(`{{ url('/admin/courses') }}/${deleteCourseId}`, { method: 'DELETE', headers: CSRF_HEADERS, credentials: 'same-origin' })
                        .then(r => r.json())
                        .then(resp => { if (resp.success) location.reload(); else document.getElementById('twoFAError').textContent = 'Delete failed.'; })
                        .catch(() => document.getElementById('twoFAError').textContent = 'Delete failed.');
                    } else {
                        document.getElementById('twoFAError').textContent = data.message || 'Invalid code.';
                    }
                })
                .catch(() => document.getElementById('twoFAError').textContent = 'Error verifying code.');
            }
            function openViewModal(courseId) {
                fetch(`{{ url('/admin/courses') }}/${courseId}`, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then(r => {
                    if (r.status === 401 || r.status === 403) {
                        throw new Error('auth');
                    }
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
                fetch(`{{ url('/admin/courses') }}/${courseId}/edit`, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then(r => r.json())
                .then(payload => {
                    const c = payload.course;
                    document.getElementById('modalEditTitle').value = c.title;
                    document.getElementById('modalEditStatus').value = c.status;
                    document.getElementById('modalEditForm').action = `{{ url('/admin/courses') }}/${courseId}`;
                    document.getElementById('editCourseModal').classList.remove('hidden');
                    document.getElementById('modalEdit2FAStep1').classList.remove('hidden');
                    document.getElementById('modalEdit2FAStep2').classList.add('hidden');
                    document.getElementById('modalEdit2FAError').textContent = '';
                })
                .catch(() => alert('Failed to load course for editing'));
            }
            function closeEditModal() { document.getElementById('editCourseModal').classList.add('hidden'); editingCourseId = null; }
            function requestEdit2FACode() {
                fetch(`{{ route('admin.request-2fa') }}`, { method: 'POST', headers: CSRF_HEADERS, credentials: 'same-origin' })
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
                const saveBtn = document.getElementById('modalVerifySaveBtn');
                if (saveBtn) {
                    saveBtn.addEventListener('click', function () {
                        const code = document.getElementById('modalEdit2FACodeInput').value;
                        fetch(`{{ route('admin.verify-2fa') }}`, { method: 'POST', headers: CSRF_HEADERS, body: JSON.stringify({ code }), credentials: 'same-origin' })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('modalEditForm').submit();
                            } else {
                                document.getElementById('modalEdit2FAError').textContent = data.message || 'Invalid code.';
                            }
                        })
                        .catch(() => document.getElementById('modalEdit2FAError').textContent = 'Error verifying code.');
                    });
                }
                const courseSearch = document.getElementById('courseSearch');
                const programFilter = document.getElementById('programFilter');
                const statusFilter = document.getElementById('statusFilter');
                let debounceTimer = null;
                function triggerServerFilter() {
                    const q = courseSearch ? courseSearch.value.trim() : '';
                    const program = programFilter ? programFilter.value : '';
                    const status = statusFilter ? statusFilter.value : '';
                    const params = new URLSearchParams(window.location.search);
                    if (q) params.set('q', q); else params.delete('q');
                    if (program) params.set('program', program); else params.delete('program');
                    if (status) params.set('status', status); else params.delete('status');
                    params.delete('page');
                    const newUrl = window.location.pathname + (params.toString() ? ('?' + params.toString()) : '');
                    window.location.href = newUrl;
                }
                [courseSearch, programFilter, statusFilter].forEach(el => {
                    if (!el) return;
                    el.addEventListener('input', () => {
                        clearTimeout(debounceTimer);
                        debounceTimer = setTimeout(triggerServerFilter, 500);
                    });
                    el.addEventListener('change', () => {
                        clearTimeout(debounceTimer);
                        debounceTimer = setTimeout(triggerServerFilter, 250);
                    });
                });
            });
        </script>
    </main>
</x-layoutAdmin>