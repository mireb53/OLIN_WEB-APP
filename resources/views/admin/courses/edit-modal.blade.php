<!-- resources/views/admin/courses/edit-modal.blade.php -->
<div id="editCourseModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-2xl w-full mx-4 border-t-8 border-blue-600">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-2xl font-bold text-slate-800">Edit Course</h3>
            <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-800 text-2xl">&times;</button>
        </div>

        <div id="editFormError" class="text-red-500 font-semibold mb-3 hidden p-3 bg-red-50 rounded-lg"></div>

        <form id="editCourseForm" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" id="editInstructorId" name="instructor_id">

            <!-- Instructor Info (Readonly) -->
            <div>
                <label class="block font-semibold mb-1 text-slate-700">Instructor</label>
                <div id="editInstructorInfo" class="px-4 py-2 bg-gray-100 rounded-lg text-slate-600"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Course Title -->
                <div>
                    <label class="block font-semibold mb-1 text-slate-700">Course Title</label>
                    <input id="editTitle" name="title" type="text" class="w-full px-4 py-2 border rounded-lg" required>
                </div>
                <!-- Course Code -->
                <div>
                    <label class="block font-semibold mb-1 text-slate-700">Course Code</label>
                    <input id="editCode" name="course_code" type="text" class="w-full px-4 py-2 border rounded-lg">
                </div>
                <!-- Status -->
                <div>
                    <label class="block font-semibold mb-1 text-slate-700">Status</label>
                    <select id="editStatus" name="status" class="w-full px-4 py-2 border rounded-lg bg-white">
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
                <!-- Program -->
                <div>
                    <label class="block font-semibold mb-1 text-slate-700">Program</label>
                    <select id="editProgram" name="program_id" class="w-full px-4 py-2 border rounded-lg bg-white">
                        <option value="">-- Select Program --</option>
                        @foreach($programs as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Credits -->
                <div>
                    <label class="block font-semibold mb-1 text-slate-700">Credits</label>
                    <input id="editCredits" name="credits" type="number" step="0.5" min="0" class="w-full px-4 py-2 border rounded-lg">
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block font-semibold mb-1 text-slate-700">Description</label>
                <textarea id="editDescription" name="description" rows="3" class="w-full px-4 py-2 border rounded-lg"></textarea>
            </div>

            <!-- 2FA Step 1 -->
            <div id="modalEdit2FAStep1" class="pt-4">
                <button type="button" onclick="request2FACode('edit')" class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700">Request Code to Save</button>
            </div>

            <!-- 2FA Step 2 -->
            <div id="modalEdit2FAStep2" class="hidden pt-4">
                <label class="block text-slate-700 font-semibold mb-2">Enter Verification Code</label>
                <input type="text" id="modalEdit2FACodeInput" class="w-full px-4 py-2 border rounded-lg mb-2" placeholder="Enter code from email">
                <div id="modalEdit2FAError" class="text-red-500 mt-1 text-center"></div>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeEditModal()" class="px-5 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">Cancel</button>
                <button type="button" id="saveCourseBtn" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 disabled:bg-gray-400" disabled>Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    let editingCourseId = null;

    function populateEditModal(course) {
        editingCourseId = course.id;
        document.getElementById('editInstructorId').value = course.instructor_id;
        document.getElementById('editInstructorInfo').textContent = course.instructor ? `${course.instructor.name} (${course.instructor.email})` : 'N/A';
        document.getElementById('editTitle').value = course.title;
        document.getElementById('editCode').value = course.course_code;
        document.getElementById('editStatus').value = course.status;
        document.getElementById('editProgram').value = course.program_id;
        document.getElementById('editDescription').value = course.description || '';
        document.getElementById('editCredits').value = course.credits || '';
        
        document.getElementById('editFormError').textContent = '';
        document.getElementById('editFormError').classList.add('hidden');
        document.getElementById('modalEdit2FAStep1').classList.remove('hidden');
        document.getElementById('modalEdit2FAStep2').classList.add('hidden');
        document.getElementById('modalEdit2FACodeInput').value = '';
        document.getElementById('modalEdit2FAError').textContent = '';
        document.getElementById('saveCourseBtn').disabled = true;

        document.getElementById('editCourseModal').classList.remove('hidden');
    }

    function closeEditModal() { 
        document.getElementById('editCourseModal').classList.add('hidden');
        editingCourseId = null;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const saveBtn = document.getElementById('saveCourseBtn');
        if(saveBtn) {
            saveBtn.addEventListener('click', function() {
                const form = document.getElementById('editCourseForm');
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());
                const code = document.getElementById('modalEdit2FACodeInput').value;
                const errorEl = document.getElementById('editFormError');
                errorEl.textContent = '';
                errorEl.classList.add('hidden');

                fetch(`{{ route('admin.verify-2fa') }}`, {
                    method: 'POST',
                    headers: CSRF_HEADERS,
                    body: JSON.stringify({ code })
                })
                .then(r => r.json().then(authData => ({ ok: r.ok, ...authData })))
                .then(authData => {
                    if (!authData.ok || !authData.success) {
                        document.getElementById('modalEdit2FAError').textContent = authData.message || 'Invalid 2FA code.';
                        throw new Error(authData.message || 'Invalid 2FA code.');
                    }
                    
                    return fetch(`/admin/courses/${editingCourseId}`, {
                        method: 'PUT',
                        headers: CSRF_HEADERS,
                        body: JSON.stringify(data)
                    });
                })
                .then(r => r.json().then(courseData => ({ ok: r.ok, ...courseData })))
                .then(courseData => {
                    if (!courseData.ok) throw courseData;

                    if (courseData.success && courseData.course) {
                        if(window.updateCourseInTable) {
                            window.updateCourseInTable(courseData.course);
                        } else {
                            location.reload();
                        }
                        closeEditModal();
                    }
                })
                .catch(err => {
                    let errorMessage = 'Failed to update course.';
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

        const codeInput = document.getElementById('modalEdit2FACodeInput');
        if(codeInput) {
            codeInput.addEventListener('input', function() {
                document.getElementById('saveCourseBtn').disabled = this.value.trim().length < 6;
                document.getElementById('modalEdit2FAError').textContent = '';
            });
        }
    });
</script>
