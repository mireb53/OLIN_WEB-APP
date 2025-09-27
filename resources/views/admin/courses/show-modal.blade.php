<div id="viewCourseModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center font-mono">
    <div id="modal-content" class="bg-slate-950 rounded-2xl shadow-[0_0_50px_rgba(0,255,255,0.3)] p-8 max-w-6xl w-full mx-4 relative overflow-hidden border border-cyan-800 transition-all duration-500" style="max-height:75vh;">
        
        <div class="absolute top-4 right-4 flex items-center space-x-4">
            <button id="theme-toggle" class="text-slate-400 p-2 rounded-full hover:text-cyan-400 transition-colors duration-300 focus:outline-none" aria-label="Toggle light/dark mode">
                <svg id="moon-icon" class="w-6 h-6 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                </svg>
                <svg id="sun-icon" class="w-6 h-6 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 12a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zm-4-9a5 5 0 110 10 5 5 0 010-10zM4.914 4.914a1 1 0 011.414 0l.707.707a1 1 0 11-1.414 1.414l-.707-.707a1 1 0 010-1.414zM16.414 16.414a1 1 0 010 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 10a1 1 0 011-1h1a1 1 0 110 2h-1a1 1 0 01-1-1zM3 10a1 1 0 011-1h1a1 1 0 110 2H4a1 1 0 01-1-1zM4.914 15.086a1 1 0 010 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM15.086 4.914a1 1 0 010 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0z"></path>
                </svg>
            </button>
            <button class="text-slate-400 text-4xl font-light hover:text-cyan-400 transition-colors duration-300 focus:outline-none" onclick="closeViewModal()" aria-label="Close">&times;</button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="col-span-1 p-6 bg-slate-900 rounded-xl border border-cyan-900 text-center lg:text-left transition-colors duration-500">
                <div class="mb-5">
                    <h3 id="viewCourseTitle" class="text-2xl lg:text-3xl font-bold text-cyan-400 break-words transition-colors duration-500">Course Title Placeholder</h3>
                    <p id="viewCourseCode" class="text-md text-slate-500 font-mono transition-colors duration-500">CODE-000</p>
                </div>
                <hr class="my-5 border-cyan-900">
                <div class="space-y-4 text-sm text-slate-400 transition-colors duration-500">
                    <p><strong>Instructor:</strong> <span id="viewCourseInstructor" class="font-medium text-white">N/A</span></p>
                    <p><strong>Program:</strong> <span id="viewCourseProgram" class="font-medium text-white">N/A</span></p>
                    <p><strong>Credits:</strong> <span id="viewCourseCredits" class="font-medium text-white">0</span></p>
                    <p><strong>Status:</strong> <span id="viewCourseStatus" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100/20 text-white">Draft</span></p>
                    <p><strong>Created:</strong> <span id="viewCourseCreated" class="font-medium text-white">N/A</span></p>
                </div>
            </div>

            <div class="col-span-1 lg:col-span-2">
                <!-- make the right side scrollable when content grows so modal height stays within viewport limit -->
                <div class="overflow-auto" style="max-height:calc(75vh - 6rem);">
                <div class="border-b-2 border-slate-700">
                    <nav class="-mb-0.5 flex space-x-6 lg:space-x-8" aria-label="Tabs">
                        <button class="tab-button active-tab" data-tab="overview">OVERVIEW</button>
                        <button class="tab-button" data-tab="topics">TOPICS</button>
                        <button class="tab-button" data-tab="materials">MATERIALS</button>
                        <button class="tab-button" data-tab="assessments">ASSESSMENTS</button>
                        <button class="tab-button" data-tab="students">STUDENTS</button>
                    </nav>
                </div>
                
                <div id="overview-content" class="tab-content py-6 space-y-4">
                    <h4 class="text-lg font-semibold text-blue-800 transition-colors duration-500">Course Description</h4>
                    <p id="viewCourseDescription" class="text-sm text-white leading-relaxed italic transition-colors duration-500">No description provided.</p>
                </div>
                    <div id="topics-content" class="tab-content py-6 hidden">
                    <h4 class="text-lg font-semibold text-blue-800 mb-4 transition-colors duration-500">Course Topics</h4>
                    <ul id="topicsList" class="space-y-2 list-disc list-inside text-sm text-slate-700 max-h-44 overflow-y-auto pr-2"></ul>
                </div>
                <div id="materials-content" class="tab-content py-6 hidden">
                    <h4 class="text-lg font-semibold text-blue-800 mb-4 transition-colors duration-500">Course Materials</h4>
                    <ul id="materialsList" class="space-y-3 text-slate-700 max-h-44 overflow-y-auto pr-2"></ul>
                </div>
                <div id="assessments-content" class="tab-content py-6 hidden">
                    <h4 class="text-lg font-semibold text-blue-800 mb-4 transition-colors duration-500">Course Assessments</h4>
                    <ul id="assessmentsList" class="space-y-3 text-slate-700 max-h-44 overflow-y-auto pr-2"></ul>
                </div>
                <div id="students-content" class="tab-content py-6 hidden">
                    <h4 class="text-lg font-semibold text-blue-800 mb-4 transition-colors duration-500">Enrolled Students</h4>
                    <div id="studentsList" class="space-y-3 max-h-56 overflow-y-auto pr-2 text-slate-700"></div>
                </div>

                <div class="mt-6 text-right pt-6">
                    <button id="viewFullPageBtn" onclick="(function(){ if(window.currentModalCourseId) window.location.href = `{{ url('/admin/courses') }}/${window.currentModalCourseId}/details`; })()" class="bg-blue-600 text-white py-2 px-6 rounded-full hover:bg-blue-500 transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-opacity-50">View Full Page</button>
            
                </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Base styles for the modal - Dark Mode (Default) */
    #modal-content {
        transition: all 0.5s ease;
    }

    /* Change the color of h4 elements in DARK MODE */
    #modal-content h4 {
        color: #4ECDC4; /* Your desired color for dark mode */
    }

    #modal-content .tab-button {
        color: #94A3B8;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 2px solid transparent;
        padding-top: 1rem;
        padding-bottom: 1rem;
        display: inline-flex;
        align-items: center;
        font-size: 0.875rem;
        background: none;
        outline: none;
        cursor: pointer;
    }
    
    #modal-content .tab-button.active-tab {
        color: #22D3EE;
        font-weight: 600;
        border-color: #22D3EE;
    }

    #modal-content .tab-button:hover {
        color: #22D3EE;
        border-color: #22D3EE;
    }

    /* Dark mode styles for the modal only - Light Mode (with .dark class) */
    #modal-content.dark {
        background-color: #F1F5F9; /* slate-100 */
        box-shadow: 0 0 40px rgba(0, 0, 0, 0.1);
        border-color: #CBD5E1; /* slate-300 */
    }
    #modal-content.dark #theme-toggle {
        color: #334155; /* slate-700 */
    }
    #modal-content.dark #theme-toggle:hover {
        color: #0F172A; /* slate-950 */
    }
    #modal-content.dark #viewCourseTitle { color: #0F172A; }
    #modal-content.dark #viewCourseCode { color: #64748B; } /* slate-500 */
    #modal-content.dark #viewCourseInstructor,
    #modal-content.dark #viewCourseProgram,
    #modal-content.dark #viewCourseCredits,
    #modal-content.dark #viewCourseCreated { color: #0F172A; }
    #modal-content.dark .bg-slate-900 {
        background-color: #E2E8F0; /* slate-200 */
        border-color: #CBD5E1; /* slate-300 */
    }
    #modal-content.dark .text-slate-400, #modal-content.dark .text-slate-300 { color: #475569; } /* slate-600 */
    #modal-content.dark .text-white { color: #0F172A; } /* slate-950 */
    #modal-content.dark .tab-button { color: #64748B; } /* slate-500 */
    #modal-content.dark .tab-button.active-tab, #modal-content.dark .tab-button:hover {
        color: #0F172A;
        border-color: #0F172A;
    }
    #modal-content.dark .border-slate-700 { border-color: #E2E8F0; } /* slate-200 */
    #modal-content.dark .bg-slate-700 { background-color: #CBD5E1; } /* slate-300 */
    #modal-content.dark .text-slate-300 { color: #0F172A; }
    #modal-content.dark .bg-slate-700:hover { background-color: #94A3B8; } /* slate-400 */
    #modal-content.dark #materialsList .text-slate-800,
    #modal-content.dark #assessmentsList .text-slate-800,
    #modal-content.dark #studentsList .text-slate-800 { color: #0F172A; }
    #modal-content.dark #materialsList .text-slate-500,
    #modal-content.dark #assessmentsList .text-slate-500,
    #modal-content.dark #studentsList .text-slate-500 { color: #64748B; }
    #modal-content.dark #materialsList svg { color: #0F172A; }
    #modal-content.dark #assessmentsList svg { color: #0F172A; }
    #modal-content.dark #studentsList svg { color: #0F172A; }
    #modal-content.dark .bg-gray-100\/20 {
        background-color: #0F172A;
        color: #F8FAFC;
    }
    #modal-content.dark #viewCourseDescription { color: #335055ff; }
    #modal-content.dark #materialsList li { background-color: #E2E8F0; border-color: #CBD5E1; }
    #modal-content.dark #assessmentsList li { background-color: #E2E8F0; border-color: #CBD5E1; }
    #modal-content.dark #studentsList div { background-color: #E2E8F0; border-color: #CBD5E1; }

    /* Change the color of h4 elements in LIGHT MODE */
    #modal-content.dark h4 {
        color: #0F172A; /* Your desired color for light mode */
    }
    #modal-content.dark #viewCourseDescription {
    color: #0F172A; /* Change to a dark slate color */
}
</style>

<script>
    function renderCourseModal(course) {
        document.getElementById('viewCourseTitle').textContent = course.title;
        document.getElementById('viewCourseCode').textContent = course.course_code || 'N/A';
        document.getElementById('viewCourseInstructor').textContent = course.instructor ? course.instructor.name : 'N/A';
        document.getElementById('viewCourseProgram').textContent = course.program ? course.program.name : 'N/A';
        document.getElementById('viewCourseCredits').textContent = course.credits ?? 'N/A';
        document.getElementById('viewCourseDescription').textContent = course.description || 'No description provided.';
        document.getElementById('viewCourseCreated').textContent = course.created_at ? new Date(course.created_at).toLocaleDateString() : 'N/A';

        // Status badge
        const statusSpan = document.getElementById('viewCourseStatus');
        const formattedStatus = course.status.charAt(0).toUpperCase() + course.status.slice(1);
        statusSpan.textContent = formattedStatus;
        // reset classes then apply base
        statusSpan.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold transition-colors duration-500';
        // pick text color variant depending on modal theme (dark vs light)
        const modalEl = document.getElementById('modal-content');
        const isDarkMode = modalEl && modalEl.classList.contains('dark');
        if (course.status === 'published') {
            statusSpan.classList.add('bg-green-500/20');
            statusSpan.classList.add(isDarkMode ? 'text-green-400' : 'text-green-700');
        } else if (course.status === 'draft') {
            statusSpan.classList.add('bg-yellow-500/20');
            statusSpan.classList.add(isDarkMode ? 'text-yellow-400' : 'text-yellow-700');
        } else {
            statusSpan.classList.add('bg-slate-500/20');
            statusSpan.classList.add(isDarkMode ? 'text-slate-400' : 'text-slate-700');
        }

        // Topics
        const topicsList = document.getElementById('topicsList');
        topicsList.innerHTML = '';
        if (course.topics && course.topics.length > 0) {
            course.topics.forEach(topic => {
                const li = document.createElement('li');
                li.className = 'text-slate-300 transition-colors duration-500';
                li.textContent = topic.name;
                topicsList.appendChild(li);
            });
        } else {
            topicsList.innerHTML = '<li class="text-slate-500 italic">No topics found.</li>';
        }

        // Materials
        const materialsList = document.getElementById('materialsList');
        materialsList.innerHTML = '';
        if (course.materials && course.materials.length > 0) {
            course.materials.forEach(material => {
                const li = document.createElement('li');
                li.className = 'flex items-center space-x-3 p-3 rounded-lg bg-slate-800 border border-cyan-900 transition-colors duration-500';
                li.innerHTML = `
                    <svg class="h-6 w-6 text-cyan-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zM6 4h7v4h4v12H6V4zm2 8h8v2H8v-2zm0 4h5v2H8v-2z" />
                    </svg>
                    <div>
                        <span class="text-sm font-medium text-white">${material.title}</span>
                        <span class="text-xs text-slate-400">(${material.material_type})</span>
                    </div>
                `;
                materialsList.appendChild(li);
            });
        } else {
            materialsList.innerHTML = '<li class="text-slate-500 italic">No materials found.</li>';
        }

        // Assessments
        const assessmentsList = document.getElementById('assessmentsList');
        assessmentsList.innerHTML = '';
        if (course.assessments && course.assessments.length > 0) {
            course.assessments.forEach(assessment => {
                const li = document.createElement('li');
                li.className = 'flex items-center space-x-3 p-3 rounded-lg bg-slate-800 border border-cyan-900 transition-colors duration-500';
                li.innerHTML = `
                    <svg class="h-6 w-6 text-fuchsia-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" />
                    </svg>
                    <div>
                        <span class="text-sm font-medium text-white">${assessment.title}</span>
                        <span class="text-xs text-slate-400">(${assessment.type})</span>
                    </div>
                `;
                assessmentsList.appendChild(li);
            });
        } else {
            assessmentsList.innerHTML = '<li class="text-slate-500 italic">No assessments found.</li>';
        }

        // Students (scrollable)
        const studentsList = document.getElementById('studentsList');
        studentsList.innerHTML = '';
        if (course.students && course.students.length > 0) {
            course.students.forEach(student => {
                const div = document.createElement('div');
                div.className = 'flex items-center gap-4 p-3 bg-slate-800 rounded-lg border border-cyan-900 transition-colors duration-500';
                div.innerHTML = `
                    <svg class="w-8 h-8 text-indigo-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a5 5 0 100 10 5 5 0 000-10zm0 14c-4.42 0-8 1.79-8 4v2h16v-2c0-2.21-3.58-4-8-4z" /></svg>
                    <div>
                        <span class="font-semibold text-white block">${student.name}</span>
                        <span class="text-xs text-slate-400">${student.email}</span>
                    </div>
                `;
                studentsList.appendChild(div);
            });
        } else {
            studentsList.innerHTML = '<div class="text-slate-500 italic">No students enrolled.</div>';
        }

        // Tab navigation logic
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                tabButtons.forEach(btn => btn.classList.remove('active-tab'));
                tabContents.forEach(content => content.classList.add('hidden'));
                button.classList.add('active-tab');
                const target = button.getAttribute('data-tab') + '-content';
                const el = document.getElementById(target);
                if (el) el.classList.remove('hidden');
            });
        });
        
        // Default to overview tab
        tabButtons[0].click();
        
        // store current course id for 'View Full Page' button
        window.currentModalCourseId = course.id || courseId;
        // Show modal
        document.getElementById('viewCourseModal').classList.remove('hidden');
    }

    // expose open/close for other scripts
    window.openViewModal = function(courseOrId) {
        if (courseOrId && typeof courseOrId === 'object') renderCourseModal(courseOrId);
    };
    window.closeViewModal = function() {
        document.getElementById('viewCourseModal').classList.add('hidden');
    };

    // Dark Mode Toggle Logic for MODAL ONLY
    (function setupThemeToggle(){
        const themeToggleBtn = document.getElementById('theme-toggle');
        const moonIcon = document.getElementById('moon-icon');
        const sunIcon = document.getElementById('sun-icon');
        const modalContent = document.getElementById('modal-content');

        // default: add 'dark' class for dark appearance, otherwise light
        const stored = localStorage.getItem('modalTheme') || 'dark';
        if (stored === 'dark') {
            modalContent.classList.add('dark');
            moonIcon.classList.remove('hidden');
            sunIcon.classList.add('hidden');
        } else {
            modalContent.classList.remove('dark');
            moonIcon.classList.add('hidden');
            sunIcon.classList.remove('hidden');
        }

        themeToggleBtn.addEventListener('click', () => {
            if (modalContent.classList.contains('dark')) {
                modalContent.classList.remove('dark');
                localStorage.setItem('modalTheme', 'light');
                moonIcon.classList.add('hidden');
                sunIcon.classList.remove('hidden');
            } else {
                modalContent.classList.add('dark');
                localStorage.setItem('modalTheme', 'dark');
                moonIcon.classList.remove('hidden');
                sunIcon.classList.add('hidden');
            }
        });
    })();
</script>