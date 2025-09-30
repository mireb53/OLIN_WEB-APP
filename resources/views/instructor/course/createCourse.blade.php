{{-- resources/views/courses/create.blade.php --}}

<x-layout>

    <h1 class="text-3xl font-bold text-gray-800 mb-6">Create New Course</h1>

    {{-- Display success message --}}
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    {{-- Display validation errors --}}
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Whoops!</strong>
            <span class="block sm:inline">There were some problems with your input.</span>
            <ul class="mt-3 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Course Information Card --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="flex justify-between items-center p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Course Information</h2>
            <button type="submit" form="createCourseForm" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50" id="submitCourseButton">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Create Course
            </button>
        </div>

        <form action="{{ route('course.store') }}" method="POST" id="createCourseForm">
            @csrf
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Course Name --}}
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Course Name</label>
                        <input type="text" id="title" name="title" value="{{ old('title') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="Introduction to Programming" required>
                    </div>

                    {{-- Course Code --}}
                    <div>
                        <label for="course_code" class="block text-sm font-medium text-gray-700 mb-2">Course Code</label>
                        <input type="text" id="course_code" name="course_code" value="{{ old('course_code') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="CS101" required>
                    </div>

                    {{-- Status --}}
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="status" name="status" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                            <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>

                    {{-- Department --}}
                    <div>
                        <label for="department" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                        <select id="department" name="department" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Select Department</option>
                            <option value="CCS" {{ old('department') == 'CCS' ? 'selected' : '' }}>CCS - College of Computer Studies</option>
                            <option value="CHS" {{ old('department') == 'CHS' ? 'selected' : '' }}>CHS - College of Health Sciences</option>
                            <option value="CAS" {{ old('department') == 'CAS' ? 'selected' : '' }}>CAS - College of Arts and Sciences</option>
                            <option value="CEA" {{ old('department') == 'CEA' ? 'selected' : '' }}>CEA - College of Engineering and Architecture</option>
                            <option value="CTHBM" {{ old('department') == 'CTHBM' ? 'selected' : '' }}>CTHBM - College of Tourism, Hospitality and Business Management</option>
                            <option value="CTDE" {{ old('department') == 'CTDE' ? 'selected' : '' }}>CTDE - College of Teacher Development and Education</option>
                        </select>
                    </div>

                    {{-- Program Name --}}
                    <div>
                        <label for="program_name" class="block text-sm font-medium text-gray-700 mb-2">Program</label>
                        <select id="program_name" name="program_name" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required disabled>
                            <option value="">Select Program</option>
                        </select>
                    </div>

                    {{-- Credits --}}
                    <div>
                        <label for="credits" class="block text-sm font-medium text-gray-700 mb-2">Credits (Optional)</label>
                        <input type="number" id="credits" name="credits" value="{{ old('credits') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="3" min="1">
                    </div>
                </div>

                {{-- Description --}}
                <div class="mt-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea id="description" name="description" rows="4" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                              placeholder="A comprehensive introduction to programming concepts using Python...">{{ old('description') }}</textarea>
                </div>
            </div>
        </form>
    </div>

    {{-- Cancel Link --}}
    <div class="flex justify-center">
        <a href="{{ route('instructor.dashboard') }}" class="text-blue-600 hover:text-blue-800 font-medium">
            Cancel
        </a>
    </div>

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

        // Handle department change
        document.getElementById('department').addEventListener('change', function() {
            const selectedDepartment = this.value;
            const programSelect = document.getElementById('program_name');
            
            // Clear existing options
            programSelect.innerHTML = '<option value="">Select Program</option>';
            
            if (selectedDepartment && DEPT_PROGRAMS[selectedDepartment]) {
                // Enable program select
                programSelect.disabled = false;
                
                // Add programs for selected department
                DEPT_PROGRAMS[selectedDepartment].forEach(function(program) {
                    const option = document.createElement('option');
                    option.value = program.code; // Store only the acronym as value
                    option.textContent = `${program.code} - ${program.name}`; // Display both acronym and full name
                    
                    // Check if this was the previously selected value (for old input)
                    if (program.code === '{{ old('program_name') }}') {
                        option.selected = true;
                    }
                    
                    programSelect.appendChild(option);
                });
            } else {
                // Disable program select if no department selected
                programSelect.disabled = true;
            }
        });

        // Initialize program dropdown if department is already selected (for old input)
        document.addEventListener('DOMContentLoaded', function() {
            const departmentSelect = document.getElementById('department');
            if (departmentSelect.value) {
                // Trigger change event to populate programs
                departmentSelect.dispatchEvent(new Event('change'));
            }
        });

        // Form submission handling
        document.getElementById('createCourseForm').addEventListener('submit', function() {
            const submitButton = document.getElementById('submitCourseButton');
            submitButton.setAttribute('disabled', 'disabled');
            submitButton.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Creating...';
        });
    </script>
</x-layout>