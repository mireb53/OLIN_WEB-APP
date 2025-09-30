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

                    <!-- Enrollment Key -->
                    <div>
                        <label class="block font-semibold mb-1 text-cyan-300">Enrollment Key</label>
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

                    <!-- Department -->
                    <div>
                        <label class="block font-semibold mb-1 text-cyan-300">Department</label>
                        <select id="createDepartment" name="department" 
                            class="w-full px-4 py-2 bg-gray-800 border border-gray-600 rounded-lg text-gray-100 focus:outline-none focus:ring-2 focus:ring-cyan-500" required>
                            <option value="">-- Select Department --</option>
                            <option value="CCS">CCS - College of Computer Studies</option>
                            <option value="CHS">CHS - College of Health Sciences</option>
                            <option value="CAS">CAS - College of Arts and Sciences</option>
                            <option value="CEA">CEA - College of Engineering and Architecture</option>
                            <option value="CTHBM">CTHBM - College of Tourism, Hospitality and Business Management</option>
                            <option value="CTDE">CTDE - College of Teacher Development and Education</option>
                        </select>
                    </div>

                    <!-- Program -->
                    <div>
                        <label class="block font-semibold mb-1 text-cyan-300">Program</label>
                        <select id="createProgram" name="program_name" 
                            class="w-full px-4 py-2 bg-gray-800 border border-gray-600 rounded-lg text-gray-100 focus:outline-none focus:ring-2 focus:ring-cyan-500" disabled>
                            <option value="">-- Select Program --</option>
                        </select>
                    </div>

                    <!-- Credits -->
                    <div>
                        <label class="block font-semibold mb-1 text-cyan-300">Credits</label>
                        <input id="createCredits" name="credits" type="number" step="0.5" 
                            class="w-full px-4 py-2 bg-gray-800 border border-gray-600 rounded-lg text-gray-100 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label class="block font-semibold mb-1 text-cyan-300">Description</label>
                        <textarea id="createDescription" name="description" rows="3" 
                            class="w-full px-4 py-2 bg-gray-800 border border-gray-600 rounded-lg text-gray-100 focus:outline-none focus:ring-2 focus:ring-cyan-500"></textarea>
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
    // Department to Programs mapping with full names
    const DEPT_PROGRAMS = {
        'CCS': [
            {code: 'BSIT', name: 'Bachelor of Science in Information Technology'},
            {code: 'BSCS', name: 'Bachelor of Science in Computer Science'},
            {code: 'BSIS', name: 'Bachelor of Science in Information Systems'},
            {code: 'BLIS', name: 'Bachelor of Library and Information Science'}
        ],
        'CHS': [
            {code: 'BSN', name: 'Bachelor of Science in Nursing'},
            {code: 'BSM', name: 'Bachelor of Science in Midwifery'}
        ],
        'CAS': [
            {code: 'BAELS', name: 'Bachelor of Arts in English Language Studies'},
            {code: 'BS Math', name: 'Bachelor of Science in Mathematics'},
            {code: 'BS Applied Math', name: 'Bachelor of Science in Applied Mathematics'},
            {code: 'BS DevCo', name: 'Bachelor of Science in Development Communication'},
            {code: 'BSPA', name: 'Bachelor of Science in Public Administration'},
            {code: 'BAHS', name: 'Bachelor of Arts in History Studies'}
        ],
        'CEA': [
            {code: 'BSCE', name: 'Bachelor of Science in Civil Engineering'},
            {code: 'BSME', name: 'Bachelor of Science in Mechanical Engineering'},
            {code: 'BSEE', name: 'Bachelor of Science in Electrical Engineering'},
            {code: 'BSECE', name: 'Bachelor of Science in Electronics and Communications Engineering'}
        ],
        'CTHBM': [
            {code: 'BSOA', name: 'Bachelor of Science in Office Administration'},
            {code: 'BSTM', name: 'Bachelor of Science in Tourism Management'},
            {code: 'BSHM', name: 'Bachelor of Science in Hotel Management'},
            {code: 'BSEM', name: 'Bachelor of Science in Entrepreneurial Management'}
        ],
        'CTDE': [
            {code: 'BPEd', name: 'Bachelor of Physical Education'},
            {code: 'BCAEd', name: 'Bachelor of Culture and Arts Education'},
            {code: 'BSNEd', name: 'Bachelor of Special Needs Education'},
            {code: 'BTVTEd', name: 'Bachelor of Technical-Vocational Teacher Education'}
        ]
    };

    function openCreateModal() {
        document.getElementById('createCourseModal').classList.remove('hidden');
        document.getElementById('createFormError').textContent = '';
        document.getElementById('instructorLookupResult').textContent = '';
        document.getElementById('createInstructorId').value = '';
        
        // Reset form fields
        document.getElementById('createDepartment').value = '';
        document.getElementById('createProgram').value = '';
        document.getElementById('createProgram').disabled = true;
        document.getElementById('createProgram').innerHTML = '<option value="">-- Select Program --</option>';
    }
    function closeCreateModal() { 
        document.getElementById('createCourseModal').classList.add('hidden'); 
    }

    // Handle department change
    document.getElementById('createDepartment').addEventListener('change', function() {
        const selectedDepartment = this.value;
        const programSelect = document.getElementById('createProgram');
        
        // Clear existing options
        programSelect.innerHTML = '<option value="">-- Select Program --</option>';
        
        if (selectedDepartment && DEPT_PROGRAMS[selectedDepartment]) {
            // Enable program select
            programSelect.disabled = false;
            
            // Add programs for selected department
            DEPT_PROGRAMS[selectedDepartment].forEach(function(program) {
                const option = document.createElement('option');
                
                // Use program code as value (will be sent as program_name to server)
                option.value = program.code;
                option.textContent = `${program.code} - ${program.name}`; // Display both acronym and full name
                
                programSelect.appendChild(option);
            });
        } else {
            // Disable program select if no department selected
            programSelect.disabled = true;
        }
    });

    document.getElementById('lookupInstructorBtn').addEventListener('click', function(){
        const email = document.getElementById('lookupInstructorEmail').value.trim();
        const resEl = document.getElementById('instructorLookupResult');
        resEl.textContent = '';
        if (!email) { resEl.textContent = 'Please enter an email.'; return; }
        fetch('{{ route('admin.courseManagement.findInstructor') }}', {
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
        const department = document.getElementById('createDepartment').value;
        const program_name = document.getElementById('createProgram').value || null;
        const description = document.getElementById('createDescription').value.trim() || null;
        const credits = document.getElementById('createCredits').value || null;
        const errorEl = document.getElementById('createFormError');
        errorEl.textContent = '';

        if (!instructorId) { errorEl.textContent = 'Please lookup and select an instructor by email first.'; return; }
        if (!title) { errorEl.textContent = 'Course name is required.'; return; }
        if (!department) { errorEl.textContent = 'Department is required.'; return; }
        if (!program_name) { errorEl.textContent = 'Program is required.'; return; }

        fetch('{{ route('admin.courseManagement.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ instructor_id: instructorId, title, course_code, status, department, program_name, description, credits })
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
                    tr.className = 'hover:bg-gray-50 transition-colors duration-150';
                    tr.innerHTML = `
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
                                <a href="/admin/course-management/${c.id}/details-page" 
                                    class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-slate-100 border border-slate-200 text-slate-700 hover:bg-slate-200 hover:border-slate-300 transition-all duration-150 text-xs font-medium">
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
