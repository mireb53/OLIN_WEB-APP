<!-- resources/views/admin/courses/edit-modal.blade.php -->
<div id="editCourseModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm hidden z-50 flex items-center justify-center">
    <div id="modalContainer" class="rounded-2xl shadow-xl border p-8 max-w-4xl w-full mx-6 relative bg-gradient-to-br from-gray-900 via-gray-800 to-black text-gray-100 transition-colors duration-300">
        
        <!-- Top Right Controls -->
        <div class="absolute top-4 right-4 flex items-center gap-2">
            <!-- Dark Mode Toggle -->
            <button type="button" id="toggleModeBtn" 
                class="text-2xl px-2 py-1 rounded-full hover:bg-gray-200 transition dark:hover:bg-gray-700">
                🌙
            </button>
            <!-- Close button -->
            <button class="text-gray-500 hover:text-red-500 text-2xl transition" onclick="closeEditModal()" aria-label="Close">&times;</button>
        </div>

        <!-- Title with futuristic edit icon -->
    <h3 class="text-2xl font-bold mb-6 mt-6 flex items-center gap-2 text-cyan-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 113 3L12 15l-4 1 1-4 9.5-9.5z" />
            </svg>
            Edit Course
        </h3>

        <!-- Form -->
        <form id="modalEditForm" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Title -->
                <div>
                    <label class="block font-semibold mb-2 text-cyan-300">Course Name</label>
                    <input type="text" id="modalEditTitle" name="title" class="w-full px-4 py-2 rounded-lg bg-gray-800 border border-gray-600 text-gray-100 focus:outline-none focus:ring-2 focus:ring-cyan-500 transition" required>
                </div>
                <!-- Enrollment Key -->
                <div>
                    <label class="block font-semibold mb-2 text-cyan-300">Enrollment Key</label>
                    <input type="text" id="modalEditCode" name="course_code" class="w-full px-4 py-2 rounded-lg bg-gray-800 border border-gray-600 text-gray-100 focus:outline-none focus:ring-2 focus:ring-cyan-500 transition" placeholder="e.g. CS101" required>
                </div>
                <!-- Status -->
                <div>
                    <label class="block font-semibold mb-2 text-cyan-300">Status</label>
                    <select id="modalEditStatus" name="status" class="w-full px-4 py-2 rounded-lg bg-gray-800 border border-gray-600 text-gray-100 focus:outline-none focus:ring-2 focus:ring-cyan-500 transition" required>
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
                <!-- Department -->
                <div>
                    <label class="block font-semibold mb-2 text-cyan-300">Department</label>
                    <select id="modalEditDepartment" name="department" class="w-full px-4 py-2 rounded-lg bg-gray-800 border border-gray-600 text-gray-100 focus:outline-none focus:ring-2 focus:ring-cyan-500 transition" required>
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
                    <label class="block font-semibold mb-2 text-cyan-300">Program</label>
                    <select id="modalEditProgram" name="program_name" class="w-full px-4 py-2 rounded-lg bg-gray-800 border border-gray-600 text-gray-100 focus:outline-none focus:ring-2 focus:ring-cyan-500 transition">
                        <option value="">-- Select Program --</option>
                    </select>
                </div>
                <!-- Credits -->
                <div>
                    <label class="block font-semibold mb-2 text-cyan-300">Credits</label>
                    <input type="number" step="0.5" id="modalEditCredits" name="credits" class="w-full px-4 py-2 rounded-lg bg-gray-800 border border-gray-600 text-gray-100 focus:outline-none focus:ring-2 focus:ring-cyan-500 transition" placeholder="e.g. 3">
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block font-semibold mb-2 text-cyan-300">Description</label>
                <textarea id="modalEditDescription" name="description" rows="4" class="w-full px-4 py-2 rounded-lg bg-gray-800 border border-gray-600 text-gray-100 focus:outline-none focus:ring-2 focus:ring-cyan-500 transition" placeholder="Optional course description..."></textarea>
            </div>

            <!-- 2FA Step 1 -->
            <div id="modalEdit2FAStep1" class="space-y-2">
                <button type="button" onclick="requestEdit2FACode(true)" class="w-full bg-gradient-to-r from-cyan-600 to-cyan-400 text-white font-bold py-2 px-4 rounded-lg shadow hover:shadow-lg transition">Send Verification Code to Email</button>
                <button type="button" onclick="closeEditModal()" class="w-full bg-gray-600 text-gray-200 py-2 px-4 rounded-lg hover:bg-gray-500 transition">Cancel</button>
            </div>

            <!-- 2FA Step 2 -->
            <div id="modalEdit2FAStep2" class="hidden mt-3">
                <label class="block font-semibold mb-2 text-cyan-300">Enter Verification Code</label>
                <input type="text" id="modalEdit2FACodeInput" class="w-full px-4 py-2 bg-gray-800 border border-gray-600 text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500 transition mb-3" placeholder="Enter code">
                <div id="modalEdit2FAError" class="text-red-400 mt-2 text-center"></div>
                <div class="flex gap-2 justify-end">
                    <button type="button" onclick="closeEditModal()" class="bg-gray-600 text-gray-200 py-2 px-4 rounded-lg hover:bg-gray-500 transition">Cancel</button>
                    <button type="button" id="modalVerifySaveBtn" class="bg-cyan-600 text-white py-2 px-4 rounded-lg hover:bg-cyan-500 transition">Verify & Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Department to Programs mapping with full names
    const EDIT_DEPT_PROGRAMS = {
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

    const modal = document.getElementById("modalContainer");
    const toggleBtn = document.getElementById("toggleModeBtn");
    let darkMode = false;
    
    // Function to populate programs based on department
    function populateProgramsByDepartment(department, selectedProgram = null) {
        const programSelect = document.getElementById('modalEditProgram');
        
        // Clear existing options
        programSelect.innerHTML = '<option value="">-- Select Program --</option>';
        
        if (department && EDIT_DEPT_PROGRAMS[department]) {
            // Add programs for selected department
            EDIT_DEPT_PROGRAMS[department].forEach(function(program) {
                const option = document.createElement('option');
                option.value = program.code; // Use program code for consistency
                option.textContent = `${program.code} - ${program.name}`; // Display both acronym and full name
                
                // Set as selected if this matches the current program
                if (selectedProgram && selectedProgram === program.code) {
                    option.selected = true;
                }
                
                programSelect.appendChild(option);
            });
        }
    }
    
    // Handle department change
    document.getElementById('modalEditDepartment').addEventListener('change', function() {
        const selectedDepartment = this.value;
        populateProgramsByDepartment(selectedDepartment);
    });
    
    // Function to set edit modal values (to be called from parent page)
    window.setEditModalValues = function(course) {
        // Set basic fields
        document.getElementById('modalEditTitle').value = course.title || '';
        document.getElementById('modalEditCode').value = course.course_code || '';
        document.getElementById('modalEditStatus').value = course.status || '';
        document.getElementById('modalEditDescription').value = course.description || '';
        document.getElementById('modalEditCredits').value = course.credits || '';
        
        // Set department
        document.getElementById('modalEditDepartment').value = course.department || '';
        
        // Populate programs based on department and set selected program
        if (course.department) {
            // Get the program name from the relationship or fallback to program field
            let programName = null;
            if (course.program && course.program.name) {
                programName = course.program.name;
            } else if (course.program_name) {
                programName = course.program_name;
            }
            
            populateProgramsByDepartment(course.department, programName);
        }
    };
    
    toggleBtn.addEventListener("click", () => {
        darkMode = !darkMode;
        if (darkMode) {
            modal.classList.remove('bg-gradient-to-br','from-gray-900','via-gray-800','to-black','text-gray-100');
            modal.classList.add('cyber-light','bg-white','text-gray-800');
            toggleBtn.textContent = '🔆';
        } else {
            modal.classList.remove('cyber-light','bg-white','text-gray-800');
            modal.classList.add('bg-gradient-to-br','from-gray-900','via-gray-800','to-black','text-gray-100');
            toggleBtn.textContent = '🌙';
        }
    });
</script>

<style>
    /* Light theme reuse similar to create modal */
    #modalContainer.cyber-light { background: linear-gradient(to bottom right, #f9fafb, #f3f4f6)!important; color:#1f2937!important; border:1px solid #d1d5db!important; box-shadow:0 4px 14px rgba(0,0,0,0.1)!important; }
    #modalContainer.cyber-light h3 { color:#0369a1!important; }
    #modalContainer.cyber-light label { color:#374151!important; }
    #modalContainer.cyber-light input, #modalContainer.cyber-light select, #modalContainer.cyber-light textarea { background:#ffffff!important; border:1px solid #d1d5db!important; color:#111827!important; }
    #modalContainer.cyber-light select option { background:#ffffff!important; color:#111827!important; }
    #modalContainer.cyber-light #modalEdit2FAError { color:#dc2626!important; }
</style>
