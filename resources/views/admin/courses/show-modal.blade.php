<div id="viewCourseModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-4xl w-full mx-4 border-t-8 border-indigo-600" style="max-height: 90vh;">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 id="viewCourseTitle" class="text-2xl font-bold text-slate-800"></h3>
                <p id="viewCourseCode" class="text-sm text-slate-500"></p>
            </div>
            <button onclick="closeViewModal()" class="text-gray-500 hover:text-gray-800 text-2xl">&times;</button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left Column: Core Details -->
            <div class="md:col-span-1 space-y-3 text-sm">
                <div>
                    <label class="font-semibold text-slate-600">Instructor:</label>
                    <p id="viewCourseInstructor" class="text-slate-800"></p>
                </div>
                <div>
                    <label class="font-semibold text-slate-600">Program:</label>
                    <p id="viewCourseProgram" class="text-slate-800"></p>
                </div>
                <div>
                    <label class="font-semibold text-slate-600">Status:</label>
                    <p><span id="viewCourseStatus" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold"></span></p>
                </div>
                <div>
                    <label class="font-semibold text-slate-600">Credits:</label>
                    <p id="viewCourseCredits" class="text-slate-800"></p>
                </div>
                <div>
                    <label class="font-semibold text-slate-600">Created:</label>
                    <p id="viewCourseCreated" class="text-slate-800"></p>
                </div>
                <div>
                    <a id="viewFullPageBtn" href="#" class="text-indigo-600 hover:underline">View Full Details Page &rarr;</a>
                </div>
            </div>

            <!-- Right Column: Tabs -->
            <div class="md:col-span-2">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                        <button class="tab-button active-tab" data-tab="overview">Overview</button>
                        <button class="tab-button" data-tab="topics">Topics</button>
                        <button class="tab-button" data-tab="materials">Materials</button>
                        <button class="tab-button" data-tab="assessments">Assessments</button>
                        <button class="tab-button" data-tab="students">Students</button>
                    </nav>
                </div>
                <div class="py-4 overflow-auto" style="max-height: calc(90vh - 250px);">
                    <div id="overview-content" class="tab-content space-y-2">
                        <h4 class="font-semibold text-slate-700">Description</h4>
                        <p id="viewCourseDescription" class="text-slate-600 leading-relaxed"></p>
                    </div>
                    <div id="topics-content" class="tab-content hidden"><ul id="topicsList" class="list-disc list-inside space-y-1 text-slate-600"></ul></div>
                    <div id="materials-content" class="tab-content hidden"><ul id="materialsList" class="space-y-2"></ul></div>
                    <div id="assessments-content" class="tab-content hidden"><ul id="assessmentsList" class="space-y-2"></ul></div>
                    <div id="students-content" class="tab-content hidden"><ul id="studentsList" class="space-y-2"></ul></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Replaced @apply with explicit utility equivalents for better editor compatibility */
    .tab-button { white-space: nowrap; padding: 0.75rem 0.25rem; border-bottom-width: 2px; font-weight: 500; font-size: 0.875rem; line-height: 1.25rem; color: #6b7280; transition: color .15s; }
    .tab-button:hover { color: #374151; border-color: #d1d5db; }
    .tab-button.active-tab { border-color: #6366f1; color: #4f46e5; }
</style>

<script>
    function renderCourseModal(course) {
        // --- Populate Header ---
        document.getElementById('viewCourseTitle').textContent = course.title;
        document.getElementById('viewCourseCode').textContent = course.course_code || 'N/A';

        // --- Populate Left Column ---
        document.getElementById('viewCourseInstructor').textContent = course.instructor ? course.instructor.name : 'N/A';
        document.getElementById('viewCourseProgram').textContent = course.program ? course.program.name : 'N/A';
        document.getElementById('viewCourseCredits').textContent = course.credits ?? 'N/A';
        document.getElementById('viewCourseCreated').textContent = course.created_at ? new Date(course.created_at).toLocaleDateString() : 'N/A';
        document.getElementById('viewFullPageBtn').href = `/admin/courses/${course.id}/details`;

        // --- Status Badge ---
        const statusSpan = document.getElementById('viewCourseStatus');
        statusSpan.textContent = course.status.charAt(0).toUpperCase() + course.status.slice(1);
        statusSpan.className = 'inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold'; // Reset
        if (course.status === 'published') statusSpan.classList.add('bg-green-100', 'text-green-800');
        else if (course.status === 'draft') statusSpan.classList.add('bg-yellow-100', 'text-yellow-800');
        else statusSpan.classList.add('bg-gray-100', 'text-gray-800');

        // --- Populate Right Column Tabs ---
        document.getElementById('viewCourseDescription').textContent = course.description || 'No description provided.';

        const topicsList = document.getElementById('topicsList');
        topicsList.innerHTML = (course.topics && course.topics.length)
            ? course.topics.map(t => `<li>${t.name}</li>`).join('')
            : '<li class="text-slate-500 italic">No topics found.</li>';

        const materialsList = document.getElementById('materialsList');
        materialsList.innerHTML = (course.materials && course.materials.length)
            ? course.materials.map(m => `<li class="p-2 bg-gray-50 rounded-md">${m.title} <span class="text-xs text-gray-500">(${m.material_type})</span></li>`).join('')
            : '<li class="text-slate-500 italic">No materials found.</li>';

        const assessmentsList = document.getElementById('assessmentsList');
        assessmentsList.innerHTML = (course.assessments && course.assessments.length)
            ? course.assessments.map(a => `<li class="p-2 bg-gray-50 rounded-md">${a.title} <span class="text-xs text-gray-500">(${a.type})</span></li>`).join('')
            : '<li class="text-slate-500 italic">No assessments found.</li>';

        const studentsList = document.getElementById('studentsList');
        studentsList.innerHTML = (course.students && course.students.length)
            ? course.students.map(s => `<li class="p-2 bg-gray-50 rounded-md">${s.name} <span class="text-xs text-gray-500">(${s.email})</span></li>`).join('')
            : '<li class="text-slate-500 italic">No students enrolled.</li>';

        // --- Tab Logic ---
        const tabButtons = document.querySelectorAll('#viewCourseModal .tab-button');
        const tabContents = document.querySelectorAll('#viewCourseModal .tab-content');
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                tabButtons.forEach(btn => btn.classList.remove('active-tab'));
                tabContents.forEach(content => content.classList.add('hidden'));
                button.classList.add('active-tab');
                document.getElementById(`${button.dataset.tab}-content`).classList.remove('hidden');
            });
        });
        // Set overview as default active tab
        if(tabButtons.length > 0) tabButtons[0].click();

        // --- Show Modal ---
        document.getElementById('viewCourseModal').classList.remove('hidden');
    }
</script>