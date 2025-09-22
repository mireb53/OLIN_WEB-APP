<x-layoutAdmin>
    @php($authUser = auth()->user())
    
    <main class="flex-1 p-4 md:p-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-semibold text-slate-700 mb-2">System Settings</h1>
            <p class="text-slate-500 italic">Configure settings for the OLIN platform and manage school information.</p>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        {{-- Debug Information for School --}}
        @if(config('app.debug') && $authUser && $authUser->isSuperAdmin())
            <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 text-gray-700 rounded-lg">
                <details>
                    <summary class="font-semibold cursor-pointer">Debug Information (Super Admin Only)</summary>
                    <div class="mt-3 text-sm font-mono">
                        <p><strong>Schools Count:</strong> {{ $schools->count() ?? 0 }}</p>
                        <p><strong>Active School ID:</strong> {{ Session::get('active_school') ?? 'None' }}</p>
                        <p><strong>Active School:</strong> {{ $activeSchool ? $activeSchool->name . ' (ID: ' . $activeSchool->id . ', Code: ' . ($activeSchool->code ?? 'MISSING') . ')' : 'None' }}</p>
                        <p><strong>Auth Status:</strong> {{ $authUser ? 'Logged in as ' . $authUser->name . ' (Role: ' . $authUser->role . ')' : 'Not authenticated' }}</p>
                    </div>
                </details>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                <div class="font-medium">Please correct the following errors:</div>
                <ul class="mt-2 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Section 1: School Management (Super Admin Only) --}}
        @if($authUser && $authUser->isSuperAdmin())
            <section class="bg-white rounded-xl p-6 md:p-8 shadow-lg border border-gray-200 mb-8">
                <div class="flex items-center mb-6">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                        <span class="text-blue-600 font-semibold">1</span>
                    </div>
                    <h2 class="text-xl font-semibold text-slate-700 uppercase tracking-wider">School Management</h2>
                </div>

                @php($schoolCount = $schools->count())
                
                {{-- No Schools Exist - Show Create Form --}}
                @if($schoolCount === 0)
                    <div class="p-6 bg-blue-50 border-2 border-dashed border-blue-200 rounded-lg text-center">
                        <div class="mb-4">
                            <svg class="w-12 h-12 text-blue-400 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h6M9 11.25h6m-6 4.5h6M5.25 21v-2.25a2.25 2.25 0 012.25-2.25h8.25a2.25 2.25 0 012.25 2.25V21" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-700">No Schools Found</h3>
                        <p class="text-slate-500 mt-1 mb-4">Get started by creating the first school for the platform.</p>
                        
                        <form id="createSchoolForm" action="{{ route('admin.settings.create-school') }}" method="POST" class="max-w-lg mx-auto text-left">
                            @csrf
                            <div class="mb-3">
                                <label for="new_school_name" class="block text-sm font-medium text-gray-700 mb-1">School Name <span class="text-red-500">*</span></label>
                                <input type="text" id="new_school_name" name="new_school_name" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="e.g., Olin College of Engineering">
                            </div>
                            <div class="mb-3">
                                <label for="new_school_address" class="block text-sm font-medium text-gray-700 mb-1">School Address</label>
                                <input type="text" id="new_school_address" name="new_school_address"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="1000 Olin Way, Needham, MA">
                            </div>
                            <div class="mb-4">
                                <label for="new_school_email" class="block text-sm font-medium text-gray-700 mb-1">Contact Email</label>
                                <input type="email" id="new_school_email" name="new_school_email"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="contact@olin.edu">
                            </div>
                            <button id="createSchoolBtn" type="submit" 
                                    class="w-full py-3 px-6 bg-green-600 text-white font-semibold rounded-lg shadow-md hover:bg-green-700 transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-green-500">
                                Create First School
                            </button>
                        </form>
                    </div>
                
                {{-- Schools Exist - Show Management Interface --}}
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                        {{-- School Selector --}}
                        <div>
                            <label for="school_id" class="block text-sm font-medium text-slate-600 mb-1">Select School to Manage</label>
                            <form id="school-selection-form" action="{{ route('admin.settings.select-school') }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                <select name="school_id" id="school_id" 
                                        class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        onchange="document.getElementById('school-selection-form').submit()">
                                    @foreach($schools as $school)
                                        <option value="{{ $school->id }}" @if($activeSchool && $school->id == $activeSchool->id) selected @endif>
                                            {{ $school->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <noscript>
                                    <button type="submit" class="py-2 px-4 bg-indigo-600 text-white rounded-lg">Switch</button>
                                </noscript>
                            </form>
                            <p class="text-xs text-slate-500 mt-2">Switching schools will reload the settings page.</p>
                        </div>

                        {{-- Create New School Form --}}
                        <div x-data="{ open: false }">
                            <button @click="open = !open" type="button" class="w-full text-left text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                <span x-show="!open"><svg class="w-4 h-4 inline mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>Create New School</span>
                                <span x-show="open"><svg class="w-4 h-4 inline mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6"></path></svg>Collapse Form</span>
                            </button>
                            <div x-show="open" x-collapse class="mt-4">
                                <form id="createSchoolForm" action="{{ route('admin.settings.create-school') }}" method="POST" class="p-4 bg-slate-50 border border-slate-200 rounded-lg">
                                    @csrf
                                    <p class="text-sm text-slate-600 mb-3">Add a new school to the platform.</p>
                                    <div class="space-y-3">
                                        <div>
                                            <label for="new_school_name_2" class="block text-xs font-medium text-gray-700 mb-1">School Name <span class="text-red-500">*</span></label>
                                            <input type="text" id="new_school_name_2" name="new_school_name" required class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g., Horizon Institute">
                                        </div>
                                        <div>
                                            <label for="new_school_address_2" class="block text-xs font-medium text-gray-700 mb-1">Address</label>
                                            <input type="text" id="new_school_address_2" name="new_school_address" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                        <div>
                                            <label for="new_school_email_2" class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                                            <input type="email" id="new_school_email_2" name="new_school_email" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                    </div>
                                    <div class="mt-4 text-right">
                                        <button id="createSchoolBtn" type="submit" class="py-2 px-4 bg-green-600 text-white font-semibold rounded-lg shadow-sm hover:bg-green-700 text-sm">Create</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </section>
        @endif

        <!-- Settings Form -->
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-8">
            @csrf
            @method('PUT')

            {{-- Section 2: School Information & Settings --}}
            @if($activeSchool || ($authUser && $authUser->isSchoolAdmin()))
            <section class="bg-white rounded-xl p-6 md:p-8 shadow-lg border border-gray-200">
                <div class="flex items-center mb-6">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                        <span class="text-blue-600 font-semibold">2</span>
                    </div>
                    <h2 class="text-xl font-semibold text-slate-700 uppercase tracking-wider">
                        Settings for: <span class="text-indigo-600">{{ $activeSchool->name ?? 'Your School' }}</span>
                    </h2>
                </div>
                
                {{-- School Information Fields --}}
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4 border-b pb-2">School Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {{-- School Name --}}
                        <div class="flex flex-col gap-2">
                            <label for="school_name" class="font-medium text-slate-600">School Name</label>
                            <input type="text" id="school_name" name="school_name" 
                                   value="{{ old('school_name', $activeSchool->name ?? '') }}"
                                   @if(!$canEditSchoolInfo) disabled @endif
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 disabled:bg-gray-100 disabled:cursor-not-allowed">
                            @if(!$canEditSchoolInfo)
                            <p class="text-xs text-slate-500 italic">Only the Head School Admin can edit this.</p>
                            @endif
                        </div>

                        {{-- School Address --}}
                        <div class="flex flex-col gap-2">
                            <label for="school_address" class="font-medium text-slate-600">School Address</label>
                            <input type="text" id="school_address" name="school_address" 
                                   value="{{ old('school_address', $activeSchool->address ?? '') }}"
                                   @if(!$canEditSchoolInfo) disabled @endif
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 disabled:bg-gray-100 disabled:cursor-not-allowed">
                        </div>

                        {{-- School Contact Email --}}
                        <div class="flex flex-col gap-2">
                            <label for="school_contact" class="font-medium text-slate-600">School Contact Email</label>
                            <input type="email" id="school_contact" name="school_contact" 
                                   value="{{ old('school_contact', $activeSchool->email ?? '') }}"
                                   @if(!$canEditSchoolInfo) disabled @endif
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 disabled:bg-gray-100 disabled:cursor-not-allowed">
                        </div>
                    </div>
                </div>

                {{-- Academic Periods/Semesters --}}
                <div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-4 border-b pb-2">Academic Period</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="flex flex-col gap-2">
                            <label for="current_semester" class="font-medium text-slate-600">Current Semester/Term</label>
                            <input type="text" id="current_semester" name="current_semester" 
                                   value="{{ old('current_semester', $schoolSettings->current_semester ?? ($globalSettings->current_semester ?? '')) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="e.g., 1st Semester">
                            <p class="text-xs text-slate-500">The current academic period.</p>
                        </div>
                        
                        <div class="flex flex-col gap-2">
                            <label for="academic_year" class="font-medium text-slate-600">Academic Year</label>
                            <input type="text" id="academic_year" name="academic_year" 
                                   value="{{ old('academic_year', $schoolSettings->academic_year ?? ($globalSettings->academic_year ?? '')) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="e.g., 2024-2025"
                                   pattern="\d{4}-\d{4}">
                            <p class="text-xs text-slate-500">Format: YYYY-YYYY.</p>
                        </div>
                        
                        <div class="flex flex-col gap-2">
                            <label for="start_date" class="font-medium text-slate-600">Semester Start Date</label>
                            <input type="date" id="start_date" name="start_date" 
                                   value="{{ old('start_date', $schoolSettings->start_date ? \Carbon\Carbon::parse($schoolSettings->start_date)->format('Y-m-d') : '') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        
                        <div class="flex flex-col gap-2">
                            <label for="end_date" class="font-medium text-slate-600">Semester End Date</label>
                            <input type="date" id="end_date" name="end_date" 
                                   value="{{ old('end_date', $schoolSettings->end_date ? \Carbon\Carbon::parse($schoolSettings->end_date)->format('Y-m-d') : '') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                </div>
            </section>
            @endif

            {{-- Section 3: System Preferences (Super Admin only) --}}
            @if($authUser && $authUser->isSuperAdmin())
            <section class="bg-white rounded-xl p-6 md:p-8 shadow-lg border border-gray-200">
                <div class="flex items-center mb-6">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                        <span class="text-purple-600 font-semibold">3</span>
                    </div>
                    <h2 class="text-xl font-semibold text-slate-700 uppercase tracking-wider">System Preferences</h2>
                    <span class="ml-3 px-3 py-1 bg-purple-100 text-purple-700 text-xs font-semibold rounded-full">Super Admin Only</span>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="flex flex-col gap-2">
                        <label for="platform_name" class="font-medium text-slate-600">Platform Name</label>
                        <input type="text" id="platform_name" name="platform_name" 
                               value="{{ old('platform_name', $globalSettings->platform_name ?? config('app.name')) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="text-xs text-slate-500">The name displayed across the platform.</p>
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <label for="default_language" class="font-medium text-slate-600">Default Language</label>
                        <select id="default_language" name="default_language" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="en" {{ old('default_language', $globalSettings->default_language ?? 'en') == 'en' ? 'selected' : '' }}>English</option>
                            {{-- Add other languages here --}}
                        </select>
                        <p class="text-xs text-slate-500">The default language for new users.</p>
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <label for="timezone" class="font-medium text-slate-600">Timezone</label>
                        <select id="timezone" name="timezone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            {{-- Example timezones --}}
                            <option value="UTC" {{ old('timezone', $globalSettings->timezone ?? 'UTC') == 'UTC' ? 'selected' : '' }}>UTC</option>
                            <option value="America/New_York" {{ old('timezone', $globalSettings->timezone ?? '') == 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                            <option value="Asia/Manila" {{ old('timezone', $globalSettings->timezone ?? '') == 'Asia/Manila' ? 'selected' : '' }}>Asia/Manila</option>
                        </select>
                        <p class="text-xs text-slate-500">Sets the default timezone for dates.</p>
                    </div>
                </div>
            </section>
            @endif

            {{-- Section 4: File Upload Limits --}}
            <section class="bg-white rounded-xl p-6 md:p-8 shadow-lg border border-gray-200">
                <div class="flex items-center mb-6">
                    <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center mr-4">
                        <span class="text-orange-600 font-semibold">4</span>
                    </div>
                    <h2 class="text-xl font-semibold text-slate-700 uppercase tracking-wider">File Upload Limits</h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex flex-col gap-2">
                        <label for="max_file_size" class="font-medium text-slate-600">Max File Size (MB)</label>
                        <input type="number" id="max_file_size" name="max_file_size" 
                               value="{{ old('max_file_size', $schoolSettings->max_file_size ?? ($globalSettings->max_file_size ?? '100')) }}"
                               min="1" max="1000"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="text-xs text-slate-500">
                            Set limit per file upload. 
                            @if($authUser->isSuperAdmin()) This can be a global setting or school-specific. @endif
                        </p>
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <label for="allowed_file_types" class="font-medium text-slate-600">Allowed File Types</label>
                        <input type="text" id="allowed_file_types" name="allowed_file_types" 
                               value="{{ old('allowed_file_types', $schoolSettings->allowed_file_types ?? ($globalSettings->allowed_file_types ?? '.pdf,.doc,.docx,.jpg,.png')) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder=".pdf,.doc,.docx,.jpg,.png">
                        <p class="text-xs text-slate-500">Comma-separated list of extensions.</p>
                    </div>
                </div>
            </section>

            {{-- Section 5: Email Templates (Super Admin only) --}}
            @if($authUser && $authUser->isSuperAdmin())
            <section class="bg-white rounded-xl p-6 md:p-8 shadow-lg border border-gray-200">
                <div class="flex items-center mb-6">
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-4">
                        <span class="text-red-600 font-semibold">5</span>
                    </div>
                    <h2 class="text-xl font-semibold text-slate-700 uppercase tracking-wider">Email Templates</h2>
                    <span class="ml-3 px-3 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full">Super Admin Only</span>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-slate-800">Customize System Emails</h3>
                            <p class="text-sm text-slate-600 mt-1">Modify the content of emails sent to users, such as verification and welcome messages.</p>
                        </div>
                        <a href="{{ route('admin.email-templates.index') }}" 
                           class="py-2 px-5 bg-slate-600 text-white font-semibold rounded-lg shadow-sm hover:bg-slate-700 transition-colors duration-300">
                           Manage Templates
                        </a>
                    </div>
                </div>
            </section>
            @endif

            {{-- Action Buttons --}}
            <div class="flex flex-col sm:flex-row gap-4 justify-end">
                <button type="reset" 
                        class="py-3 px-6 bg-gray-200 text-gray-700 font-semibold rounded-lg shadow-sm hover:bg-gray-300 transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-gray-400">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h5M4 19v-5h5"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 4h5v5M15 19h5v-5"></path>
                    </svg>
                    Cancel Changes
                </button>
                <button type="submit" 
                        class="py-3 px-6 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Save Settings
                </button>
            </div>
        </form>
    </main>

    {{-- JavaScript for Dynamic Behavior --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle form submission for create school forms
            function handleCreateSchoolSubmit(e) {
                const form = e.target;
                const schoolNameInput = form.querySelector('input[name="new_school_name"]');
                const schoolName = schoolNameInput ? schoolNameInput.value.trim() : '';
                
                if (!schoolName) {
                    e.preventDefault();
                    alert('School Name is required.');
                    return;
                }
                
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="inline-flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Creating...</span>';
                }
            }

            const createSchoolForm1 = document.getElementById('createSchoolForm');
            if (createSchoolForm1) {
                createSchoolForm1.addEventListener('submit', handleCreateSchoolSubmit);
            }
            
            // Academic year format validation
            const academicYearInput = document.getElementById('academic_year');
            if (academicYearInput) {
                academicYearInput.addEventListener('input', function() {
                    const value = this.value;
                    const pattern = /^\d{4}-\d{4}$/;
                    
                    if (value && !pattern.test(value)) {
                        this.setCustomValidity('Please use the format YYYY-YYYY (e.g., 2024-2025)');
                    } else {
                        this.setCustomValidity('');
                    }
                });
            }

            // File size validation
            const fileSizeInput = document.getElementById('max_file_size');
            if (fileSizeInput) {
                fileSizeInput.addEventListener('input', function() {
                    const value = parseInt(this.value);
                    if (value < 1 || value > 1000) {
                        this.setCustomValidity('File size must be between 1 and 1000 MB');
                    } else {
                        this.setCustomValidity('');
                    }
                });
            }

            // Date validation - ensure end date is after start date
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            
            if (startDateInput && endDateInput) {
                function validateDates() {
                    if (startDateInput.value && endDateInput.value) {
                        if (new Date(endDateInput.value) < new Date(startDateInput.value)) {
                            endDateInput.setCustomValidity('End date must be on or after the start date.');
                        } else {
                            endDateInput.setCustomValidity('');
                        }
                    }
                }

                startDateInput.addEventListener('change', validateDates);
                endDateInput.addEventListener('change', validateDates);
            }

            // Form submission confirmation for significant changes
            const settingsForm = document.querySelector('form[action="{{ route('admin.settings.update') }}"]');
            if (settingsForm) {
                const schoolNameInput = document.getElementById('school_name');
                settingsForm.addEventListener('submit', function(e) {
                    if (!schoolNameInput) return;

                    const schoolName = schoolNameInput.value;
                    const currentSchoolName = '{{ $activeSchool->name ?? "" }}';
                    
                    if (schoolName && currentSchoolName && schoolName !== currentSchoolName) {
                        const isSchoolAdmin = {{ $authUser->isSchoolAdmin() ? 'true' : 'false' }};
                        if (isSchoolAdmin) {
                            const confirmed = confirm(
                                'You are about to change the name of your school from "' + 
                                currentSchoolName + '" to "' + schoolName + '".\n\n' +
                                'This is a significant change. Are you sure you want to proceed?'
                            );
                            if (!confirmed) {
                                e.preventDefault();
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-layoutAdmin>
