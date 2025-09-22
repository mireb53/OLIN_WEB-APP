<!-- resources/views/admin/courses/create-modal.blade.php -->
<div id="createCourseModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-2xl w-full mx-4 border-t-8 border-green-600">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-2xl font-bold text-slate-800">Create New Course</h3>
            <button onclick="closeCreateModal()" class="text-gray-500 hover:text-gray-800 text-2xl">&times;</button>
        </div>

        <div id="createFormError" class="text-red-500 font-semibold mb-3 hidden p-3 bg-red-50 rounded-lg"></div>

        <form id="createCourseForm" class="space-y-4">
            @csrf
            <input type="hidden" id="createInstructorId" name="instructor_id">

            <!-- Instructor Lookup -->
            <div>
                <label class="block font-semibold mb-1 text-slate-700">Instructor Email</label>
                <div class="flex gap-2">
                    <input id="lookupInstructorEmail" type="email" class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="instructor@example.com">
                    <button type="button" id="lookupInstructorBtn" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Lookup</button>
                </div>
                <div id="instructorLookupResult" class="mt-2 text-sm text-slate-600"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Course Title -->
                <div>
                    <label class="block font-semibold mb-1 text-slate-700">Course Title</label>
                    <input id="createTitle" name="title" type="text" class="w-full px-4 py-2 border rounded-lg" required>
                </div>
                <!-- Course Code -->
                <div>
                    <label class="block font-semibold mb-1 text-slate-700">Course Code</label>
                    <input id="createCode" name="course_code" type="text" class="w-full px-4 py-2 border rounded-lg">
                </div>
                <!-- Status -->
                <div>
                    <label class="block font-semibold mb-1 text-slate-700">Status</label>
                    <select id="createStatus" name="status" class="w-full px-4 py-2 border rounded-lg bg-white">
                        <option value="published">Published</option>
                        <option value="draft" selected>Draft</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
                <!-- Program -->
                <div>
                    <label class="block font-semibold mb-1 text-slate-700">Program</label>
                    <select id="createProgram" name="program_id" class="w-full px-4 py-2 border rounded-lg bg-white">
                        <option value="">-- Select Program --</option>
                        @foreach($programs as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Credits -->
                <div>
                    <label class="block font-semibold mb-1 text-slate-700">Credits</label>
                    <input id="createCredits" name="credits" type="number" step="0.5" min="0" class="w-full px-4 py-2 border rounded-lg">
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block font-semibold mb-1 text-slate-700">Description</label>
                <textarea id="createDescription" name="description" rows="3" class="w-full px-4 py-2 border rounded-lg"></textarea>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeCreateModal()" class="px-5 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">Cancel</button>
                <button type="button" id="createCourseBtn" class="px-6 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700">Create Course</button>
            </div>
        </form>
    </div>
</div>

<script>
    function resetCreateModal() {
        document.getElementById('createCourseForm').reset();
        document.getElementById('createFormError').textContent = '';
        document.getElementById('createFormError').classList.add('hidden');
        document.getElementById('instructorLookupResult').textContent = '';
        document.getElementById('createInstructorId').value = '';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const lookupBtn = document.getElementById('lookupInstructorBtn');
        if(lookupBtn) {
            lookupBtn.addEventListener('click', function() {
                const email = document.getElementById('lookupInstructorEmail').value.trim();
                const resEl = document.getElementById('instructorLookupResult');
                resEl.textContent = 'Searching...';
                if (!email) { resEl.textContent = 'Please enter an email.'; return; }

                const url = new URL('{{ route("admin.courses.findInstructor") }}');
                url.searchParams.set('email', email);
                fetch(url.toString(), {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.instructor) {
                        document.getElementById('createInstructorId').value = data.instructor.id;
                        resEl.textContent = `✓ Found: ${data.instructor.name} (${data.instructor.email})`;
                        resEl.style.color = 'green';
                    } else {
                        document.getElementById('createInstructorId').value = '';
                        resEl.textContent = `✗ ${data.message || 'Instructor not found.'}`;
                        resEl.style.color = 'red';
                    }
                })
                .catch(() => {
                    document.getElementById('createInstructorId').value = '';
                    resEl.textContent = '✗ Error looking up instructor.';
                    resEl.style.color = 'red';
                });
            });
        }

        const createBtn = document.getElementById('createCourseBtn');
        if(createBtn) {
            createBtn.addEventListener('click', function() {
                const form = document.getElementById('createCourseForm');
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());
                const errorEl = document.getElementById('createFormError');
                errorEl.textContent = '';
                errorEl.classList.add('hidden');

                if (!data.instructor_id) {
                    errorEl.textContent = 'Please lookup and select an instructor by email first.';
                    errorEl.classList.remove('hidden');
                    return;
                }

                fetch('{{ route("admin.courses.store") }}', {
                    method: 'POST',
                    headers: CSRF_HEADERS,
                    body: JSON.stringify(data)
                })
                .then(async (r) => {
                    const response = await r.json();
                    if (!r.ok) throw response;
                    return response;
                })
                .then(data => {
                    if (data.success && data.course) {
                        if(window.prependCourseToTable) {
                            window.prependCourseToTable(data.course);
                        } else {
                            location.reload(); // fallback
                        }
                        closeCreateModal();
                    }
                })
                .catch(err => {
                    let errorMessage = 'Failed to create course. Please try again.';
                    if (err && err.errors) {
                        errorMessage = Object.values(err.errors).flat().join(' ');
                    } else if (err && err.message) {
                        errorMessage = err.message;
                    }
                    errorEl.textContent = errorMessage;
                    errorEl.classList.remove('hidden');
                });
            });
        }
    });
</script>
