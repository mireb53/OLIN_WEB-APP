<x-layoutAdmin>
  <main class="flex-1 overflow-y-auto p-4 md:p-8">
    <div class="w-full">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Course Management</h1>
                <p class="text-slate-500 mt-1">
                    Manage all courses in the system. View, edit, or delete courses with confirmation and 2FA.
                </p>
            </div>
        </div>

        <section class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">All Courses</h2>

            {{-- Search + Filters + Create --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                <div class="flex-1 flex gap-3 items-center">
                    <input id="courseSearch" name="q" type="text"
                           value="{{ request('q', '') }}"
                           placeholder="Search Course by Name/ID"
                           class="w-full md:w-96 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" />

                    <select id="programFilter" class="px-4 py-2 border rounded-lg bg-white">
                        @php $programsList = $programs ?? collect(); @endphp
                        <option value="" @if(!request()->has('program') || request('program')=='') selected @endif>
                            All Programs
                        </option>
                        @foreach($programsList as $prog)
                            <option value="{{ $prog->id }}" @if((string)request('program','') === (string)$prog->id) selected @endif>
                                {{ $prog->name }}
                            </option>
                        @endforeach
                    </select>

                    <select id="statusFilter" class="px-4 py-2 border rounded-lg bg-white">
                        <option value="" @if(!request()->has('status') || request('status')=='') selected @endif>All Status</option>
                        <option value="published" @if(request('status')==='published') selected @endif>Published</option>
                        <option value="draft" @if(request('status')==='draft') selected @endif>Draft</option>
                        <option value="archived" @if(request('status')==='archived') selected @endif>Archived</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="openCreateModal()"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700">
                        <i class="fas fa-plus"></i>
                        <span>Create New Course</span>
                    </button>
                </div>
            </div>

            {{-- Table --}}
            <div class="w-full overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-2 text-left text-sm font-bold text-slate-800 uppercase">Course Name</th>
                            <th class="px-4 py-2 text-left text-sm font-bold text-slate-800 uppercase">Instructor</th>
                            <th class="px-4 py-2 text-left text-sm font-bold text-slate-800 uppercase">Students</th>
                            <th class="px-4 py-2 text-left text-sm font-bold text-slate-800 uppercase">Status</th>
                            <th class="px-4 py-2 text-left text-sm font-bold text-slate-800 uppercase">Last Updated</th>
                            <th class="px-4 py-2 text-center text-sm font-bold text-slate-800 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="coursesTableBody" class="bg-white divide-y divide-gray-200">
                        @forelse($courses as $course)
                        <tr data-course-id="{{ $course->id }}" data-program-id="{{ $course->program_id }}" data-status="{{ $course->status }}">
                            <td class="px-4 py-3 text-sm text-slate-800">{{ $course->title }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ optional($course->instructor)->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $course->students_count ?? 0 }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if($course->status === 'published')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Published</span>
                                @elseif($course->status === 'draft')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Draft</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">Archived</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $course->updated_at->format('Y-m-d') }}</td>
                            <td class="px-4 py-3 text-sm text-right space-x-2">
                                <button aria-label="View Course" onclick="openViewModal({{ $course->id }})"
                                        class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-slate-50 border border-slate-200 text-slate-700 hover:bg-slate-100">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button aria-label="Edit Course" onclick="openEditModal({{ $course->id }})"
                                        class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-blue-50 border border-blue-200 text-blue-700 hover:bg-blue-100">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button aria-label="Delete Course" onclick="confirmDelete({{ $course->id }}, '{{ addslashes($course->title) }}')"
                                        class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-red-50 border border-red-200 text-red-700 hover:bg-red-100">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-slate-500">No courses found.</td>
                        </tr>
                        @endforelse
                        <tr id="noResultsRow" class="hidden">
                            <td colspan="6" class="px-4 py-6 text-center text-slate-500">No courses match your search or filters.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
                @if(method_exists($courses, 'links'))
                    <div class="flex items-center justify-between gap-4 flex-wrap">
                        <div class="text-sm text-slate-600">
                            Showing {{ $courses->firstItem() ?? 0 }} to {{ $courses->lastItem() ?? 0 }} of {{ $courses->total() }} courses
                        </div>
                        <div class="ml-auto">{!! $courses->links() !!}</div>
                    </div>
                @endif
            </div>
        </section>

        {{-- Modals --}}
        @include('admin.courses.show-modal')
        @include('admin.courses.edit-modal')
        @include('admin.courses.create-modal')
        @include('admin.courses.delete-modal')
    </div>

    <script>
const CSRF_HEADERS = {
    'X-CSRF-TOKEN': '{{ csrf_token() }}',
    'Content-Type': 'application/json',
    'Accept': 'application/json'
};

// --- Utility functions ---
function getRow(course) {
    const statusBadge = {
        published: '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Published</span>',
        draft: '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Draft</span>',
        archived: '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">Archived</span>'
    };

    return `
        <td class="px-4 py-3 text-sm text-slate-800">${course.title}</td>
        <td class="px-4 py-3 text-sm text-slate-700">${course.instructor ? course.instructor.name : 'N/A'}</td>
        <td class="px-4 py-3 text-sm text-slate-700">${course.students_count ?? 0}</td>
        <td class="px-4 py-3 text-sm">${statusBadge[course.status] || ''}</td>
        <td class="px-4 py-3 text-sm text-slate-600">${new Date(course.updated_at).toISOString().split('T')[0]}</td>
        <td class="px-4 py-3 text-sm text-right space-x-2">
            <button aria-label="View Course" onclick="openViewModal(${course.id})" class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-slate-50 border border-slate-200 text-slate-700 hover:bg-slate-100">
                <i class="fas fa-eye"></i> View
            </button>
            <button aria-label="Edit Course" onclick="openEditModal(${course.id})" class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-blue-50 border border-blue-200 text-blue-700 hover:bg-blue-100">
                <i class="fas fa-edit"></i> Edit
            </button>
            <button aria-label="Delete Course" onclick="confirmDelete(${course.id}, '${escape(course.title)}')" class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-red-50 border border-red-200 text-red-700 hover:bg-red-100">
                <i class="fas fa-trash-alt"></i> Delete
            </button>
        </td>
    `;
}

function prependCourseToTable(course) {
    const tableBody = document.getElementById('coursesTableBody');
    const noCoursesRow = tableBody.querySelector('td[colspan="6"]');
    if (noCoursesRow) noCoursesRow.parentElement.remove();
    const newRow = document.createElement('tr');
    newRow.dataset.courseId = course.id;
    newRow.innerHTML = getRow(course);
    tableBody.prepend(newRow);
}

function updateCourseInTable(course) {
    const row = document.querySelector(`tr[data-course-id="${course.id}"]`);
    if (row) row.innerHTML = getRow(course);
}

function removeCourseFromTable(courseId) {
    const row = document.querySelector(`tr[data-course-id="${courseId}"]`);
    if (row) row.remove();
}

// --- Delete Modal ---
let deleteCourseId = null;
function confirmDelete(courseId, courseTitle) {
    deleteCourseId = courseId;
    document.getElementById('deleteCourseTitle').textContent = unescape(courseTitle);
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

function request2FACode(action) {
    const errorElId = action === 'delete' ? 'twoFAError' : 'modalEdit2FAError';
    fetch(`{{ route('admin.request-2fa') }}`, { method: 'POST', headers: CSRF_HEADERS })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (action === 'delete') {
                document.getElementById('deleteStep1').classList.add('hidden');
                document.getElementById('deleteStep2').classList.remove('hidden');
            } else {
                document.getElementById('modalEdit2FAStep1').classList.add('hidden');
                document.getElementById('modalEdit2FAStep2').classList.remove('hidden');
            }
        } else {
            document.getElementById(errorElId).textContent = data.message || 'Unable to send code.';
        }
    })
    .catch(() => document.getElementById(errorElId).textContent = 'Error sending code.');
}

function submitDelete2FACode() {
    const code = document.getElementById('twoFACodeInput').value;
    fetch(`{{ route('admin.verify-2fa') }}`, { method: 'POST', headers: CSRF_HEADERS, body: JSON.stringify({ code }) })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            fetch(`{{ url('/admin/courses') }}/${deleteCourseId}`, { method: 'DELETE', headers: CSRF_HEADERS })
            .then(r => r.json())
            .then(resp => {
                if (resp.success) {
                    removeCourseFromTable(deleteCourseId);
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

// --- View/Edit/Create Modal ---
function openViewModal(courseId) {
    fetch(`{{ url('/admin/courses') }}/${courseId}`, { headers: { 'Accept': 'application/json' }})
    .then(r => r.json())
    .then(payload => {
        if (payload.success && window.renderCourseModal) {
            window.renderCourseModal(payload.course);
        } else {
            document.getElementById('viewCourseError').textContent = '⚠ Failed to load course details.';
        }
    })
    .catch(() => document.getElementById('viewCourseError').textContent = '⚠ Failed to load course details.');
}
function closeViewModal() { document.getElementById('viewCourseModal').classList.add('hidden'); }

function openEditModal(courseId) {
    fetch(`{{ url('/admin/courses') }}/${courseId}/edit`, { headers: { 'Accept': 'application/json' }})
    .then(r => r.json())
    .then(payload => {
        if (payload.success && window.populateEditModal) {
            window.populateEditModal(payload.course);
        } else {
            document.getElementById('editCourseError').textContent = '⚠ Failed to load course for editing.';
        }
    })
    .catch(() => document.getElementById('editCourseError').textContent = '⚠ Failed to load course for editing.');
}
function closeEditModal() { if(window.resetEditModal) window.resetEditModal(); }

function openCreateModal() {
    if(window.resetCreateModal) window.resetCreateModal();
    document.getElementById('createCourseModal').classList.remove('hidden');
}
function closeCreateModal() { document.getElementById('createCourseModal').classList.add('hidden'); }

// --- Filtering ---
document.addEventListener('DOMContentLoaded', function () {
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
        window.location.href = `${window.location.pathname}?${params.toString()}`;
    }

    [courseSearch, programFilter, statusFilter].forEach(el => {
        if (!el) return;
        el.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(triggerServerFilter, 500);
        });
        el.addEventListener('change', () => {
            clearTimeout(debounceTimer);
            triggerServerFilter();
        });
    });
});
</script>

</main>

</x-layoutAdmin>

