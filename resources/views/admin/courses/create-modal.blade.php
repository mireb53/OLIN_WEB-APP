<!-- resources/views/admin/courses/create-modal.blade.php -->
<div id="createCourseModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm hidden z-50 flex items-center justify-center">
    <div id="createCourseContent" class="rounded-2xl shadow-xl border p-8 max-w-4xl w-full mx-6 relative bg-gradient-to-br from-gray-900 via-gray-800 to-black text-gray-100 transition-colors duration-300">
        
        <!-- Close + Theme Toggle -->
        <div class="absolute top-4 right-4 flex items-center gap-3">
            <!-- Theme Toggle -->
            <button id="themeToggleBtn" 
                class="text-gray-300 hover:text-yellow-400 text-2xl transition transform hover:scale-110"
                aria-label="Toggle Theme">🌙</button>
            
            <!-- Close Button -->
            <button class="text-gray-300 hover:text-red-500 text-3xl transition transform hover:scale-110" 
                onclick="closeCreateModal()" aria-label="Close">&times;</button>
        </div>

        <!-- Title -->
        <h3 class="text-2xl font-bold mb-6 mt-6 text-cyan-400">⚡ Create New Course</h3>

        <!-- Error Display -->
        <div id="createFormError" class="text-red-400 font-semibold mb-3"></div>

        <div class="space-y-6">
            <!-- Instructor Lookup -->
            <div>
                <label class="block font-semibold mb-2 text-cyan-300">Instructor Email</label>
                <div class="flex gap-3">
                    <input id="lookupInstructorEmail" type="email" 
                        class="flex-1 px-4 py-2 bg-gray-800 border border-gray-600 rounded-lg text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cyan-500"
                        placeholder="instructor@example.com">
                    <button id="lookupInstructorBtn" 
                        class="px-5 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg font-semibold transition">Lookup</button>
                </div>
                <div id="instructorLookupResult" class="mt-2 text-sm text-gray-300 italic"></div>
            </div>

            <!-- Form -->
            <form id="createCourseForm" class="space-y-4">
                @csrf
                <input type="hidden" id="createInstructorId" name="instructor_id">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Course Name -->
                    <div>
                        <label class="block font-semibold mb-1 text-cyan-300">Course Name</label>
                        <input id="createTitle" name="title" type="text" 
                            class="w-full px-4 py-2 bg-gray-800 border border-gray-600 rounded-lg text-gray-100 focus:outline-none focus:ring-2 focus:ring-cyan-500" required>
                    </div>

                    <!-- Course Code -->
                    <div>
                        <label class="block font-semibold mb-1 text-cyan-300">Course Code</label>
                        <input id="createCode" name="course_code" type="text" 
                            class="w-full px-4 py-2 bg-gray-800 border border-gray-600 rounded-lg text-gray-100 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block font-semibold mb-1 text-cyan-300">Status</label>
                        <select id="createStatus" name="status" 
                            class="w-full px-4 py-2 bg-gray-800 border border-gray-600 rounded-lg text-gray-100 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>

                    <!-- Program -->
                    <div>
                        <label class="block font-semibold mb-1 text-cyan-300">Program</label>
                        <select id="createProgram" name="program_id" 
                            class="w-full px-4 py-2 bg-gray-800 border border-gray-600 rounded-lg text-gray-100 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                            <option value="">-- Select Program --</option>
                            @if(isset($programs))
                                @foreach($programs as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label class="block font-semibold mb-1 text-cyan-300">Description</label>
                        <textarea id="createDescription" name="description" rows="3" 
                            class="w-full px-4 py-2 bg-gray-800 border border-gray-600 rounded-lg text-gray-100 focus:outline-none focus:ring-2 focus:ring-cyan-500"></textarea>
                    </div>

                    <!-- Credits -->
                    <div>
                        <label class="block font-semibold mb-1 text-cyan-300">Credits</label>
                        <input id="createCredits" name="credits" type="number" step="0.5" 
                            class="w-full px-4 py-2 bg-gray-800 border border-gray-600 rounded-lg text-gray-100 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeCreateModal()" 
                        class="px-5 py-2 bg-gray-600 hover:bg-gray-500 rounded-lg text-gray-200 font-semibold transition">Cancel</button>
                    <button type="button" id="createCourseBtn" 
                        class="px-6 py-2 bg-gradient-to-r from-cyan-600 to-blue-600 text-white rounded-lg font-bold hover:scale-105 transition">⚡ Create Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Light Theme Styles -->
<style>
    .cyber-light {
        background: linear-gradient(to bottom right, #f9fafb, #f3f4f6) !important;
        color: #1f2937 !important;
        border: 1px solid #d1d5db !important;
        box-shadow: 0 4px 14px rgba(0,0,0,0.1) !important;
    }
    .cyber-light h3 { color: #0369a1 !important; }
    .cyber-light label { color: #374151 !important; }
    .cyber-light input,
    .cyber-light select,
    .cyber-light textarea {
        background: #ffffff !important;
        border: 1px solid #d1d5db !important;
        color: #111827 !important;
    }
    .cyber-light select option {
        background: #ffffff !important;
        color: #111827 !important;
    }
    .cyber-light #createFormError { color: #dc2626 !important; }
    .cyber-light #instructorLookupResult { color: #4b5563 !important; font-style: italic; }
</style>

<script>
    function openCreateModal() {
        document.getElementById('createCourseModal').classList.remove('hidden');
        document.getElementById('createFormError').textContent = '';
        document.getElementById('instructorLookupResult').textContent = '';
        document.getElementById('createInstructorId').value = '';
    }
    function closeCreateModal() { 
        document.getElementById('createCourseModal').classList.add('hidden'); 
    }

    document.getElementById('lookupInstructorBtn').addEventListener('click', function(){
        const email = document.getElementById('lookupInstructorEmail').value.trim();
        const resEl = document.getElementById('instructorLookupResult');
        resEl.textContent = '';
        if (!email) { resEl.textContent = 'Please enter an email.'; return; }
    fetch('{{ route('admin.courses.findInstructor') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ email })
        }).then(r => r.json())
        .then(data => {
            if (data.success && data.instructor) {
                document.getElementById('createInstructorId').value = data.instructor.id;
                resEl.textContent = 'Found instructor: ' + data.instructor.name + ' (' + data.instructor.email + ')';
            } else {
                resEl.textContent = data.message || 'Instructor not found.';
            }
        }).catch(() => resEl.textContent = 'Error looking up instructor.');
    });

    document.getElementById('createCourseBtn').addEventListener('click', function() {
        const instructorId = document.getElementById('createInstructorId').value;
        const title = document.getElementById('createTitle').value.trim();
        const course_code = document.getElementById('createCode').value.trim();
        const status = document.getElementById('createStatus').value;
        const program_id = document.getElementById('createProgram').value || null;
        const description = document.getElementById('createDescription').value.trim() || null;
        const credits = document.getElementById('createCredits').value || null;
        const errorEl = document.getElementById('createFormError');
        errorEl.textContent = '';

        if (!instructorId) { errorEl.textContent = 'Please lookup and select an instructor by email first.'; return; }
        if (!title) { errorEl.textContent = 'Course name is required.'; return; }

    fetch('{{ route('admin.courses.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ instructor_id: instructorId, title, course_code, status, program_id, description, credits })
        })
        .then(async (r) => {
            let data;
            try {
                data = await r.json();
            } catch (e) {
                throw new Error('Invalid server response');
            }

            if (!r.ok) {
                if (data && data.errors) {
                    const messages = Object.values(data.errors).flat().join(' ');
                    errorEl.textContent = messages;
                } else {
                    errorEl.textContent = data.message || 'Failed to create course.';
                }
                return;
            }

            if (data.success && data.course) {
                try {
                    const tb = document.getElementById('coursesTableBody');
                    const c = data.course;
                    const tr = document.createElement('tr');
                    tr.setAttribute('data-course-id', c.id);
                    tr.setAttribute('data-program-id', c.program_id || '');
                    tr.setAttribute('data-status', c.status || '');
                    tr.innerHTML = `
                        <td class="px-4 py-3 text-sm text-slate-800">${c.title}</td>
                        <td class="px-4 py-3 text-sm text-slate-700">${c.instructor ? c.instructor.name : 'N/A'}</td>
                        <td class="px-4 py-3 text-sm text-slate-700">0</td>
                        <td class="px-4 py-3 text-sm">${
                            c.status === 'published' ? 
                            '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Published</span>' : 
                            (c.status === 'draft' ? 
                            '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Draft</span>' : 
                            '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">Archived</span>')
                        }</td>
                        <td class="px-4 py-3 text-sm text-slate-600">${new Date(c.created_at).toISOString().split('T')[0]}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">${new Date(c.updated_at).toISOString().split('T')[0]}</td>
                        <td class="px-4 py-3 text-sm text-right space-x-2">
                            <button onclick="openViewModal(${c.id})" class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-slate-50 border border-slate-200 text-slate-700 hover:bg-slate-100"><i class="fas fa-eye"></i> View</button>
                            <button onclick="openEditModal(${c.id})" class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-blue-50 border border-blue-200 text-blue-700 hover:bg-blue-100"><i class="fas fa-edit"></i> Edit</button>
                            <button onclick="confirmDelete(${c.id}, '${(c.title||'').replace(/'/g, "\\'") }')" class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-red-50 border border-red-200 text-red-700 hover:bg-red-100"><i class="fas fa-trash-alt"></i> Delete</button>
                        </td>
                    `;
                    const firstDataRow = tb.querySelector('tr[data-course-id]');
                    if (firstDataRow) tb.insertBefore(tr, firstDataRow);
                    else tb.insertBefore(tr, document.getElementById('noResultsRow'));
                    closeCreateModal();
                    const nr = document.getElementById('noResultsRow'); if (nr) nr.classList.add('hidden');
                } catch (e) {
                    location.reload();
                }
            } else {
                errorEl.textContent = data.message || 'Failed to create course.';
            }
        })
        .catch(() => { errorEl.textContent = 'Error creating course.'; });
    });

    // Theme Toggle
    const themeBtn = document.getElementById('themeToggleBtn');
    const modalContent = document.getElementById('createCourseContent');

    themeBtn.addEventListener('click', () => {
        if (modalContent.classList.contains('cyber-light')) {
            modalContent.classList.remove('cyber-light');
            themeBtn.textContent = "🌙"; // Dark mode icon
        } else {
            modalContent.classList.add('cyber-light');
            themeBtn.textContent = "🔆"; // Light mode icon
        }
    });
</script>
