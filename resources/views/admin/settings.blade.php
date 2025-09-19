<x-layoutAdmin>
   

    <main class="flex-1 p-4 md:p-8">
        <div class="mb-8">
            <h1 class="text-3xl font-semibold text-slate-700 mb-2">System Settings</h1>
            <p class="text-slate-500 italic">Configure global settings for the OLIN platform.</p>
        </div>

        <!-- Success Message -->
        <div id="successMessage" class="hidden fixed bottom-6 right-6 z-50 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg transition-transform duration-300 transform translate-x-full">
            ✓ Settings have been saved successfully!
        </div>

        <!-- Section 1 -->
        <section class="bg-white rounded-xl p-6 md:p-8 mb-8 shadow-lg">
            <h2 class="text-xl font-semibold text-slate-700 mb-6 uppercase tracking-wider">Section 1: School Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-gray-700 text-sm" for="school-name">School Name:</label>
                    <input type="text" id="school-name" class="py-3 px-4 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500" placeholder="Enter school name">
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-gray-700 text-sm" for="school-address">Address:</label>
                    <input type="text" id="school-address" class="py-3 px-4 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500" placeholder="Enter school address">
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-gray-700 text-sm" for="school-contact">Contact Email:</label>
                    <input type="email" id="school-contact" class="py-3 px-4 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500" placeholder="Enter contact email">
                </div>
            </div>
        </section>

        <!-- Section 2 -->
        <section class="bg-white rounded-xl p-6 md:p-8 mb-8 shadow-lg">
            <h2 class="text-xl font-semibold text-slate-700 mb-6 uppercase tracking-wider">Section 2: Academic Periods/Semesters</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-gray-700 text-sm" for="current-semester">Current Semester:</label>
                    <select id="current-semester" class="py-3 px-4 border border-gray-300 rounded-md bg-white cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500">
                        <option>Select one</option>
                        <option>First Semester</option>
                        <option>Second Semester</option>
                        <option>Summer Session</option>
                    </select>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-gray-700 text-sm" for="academic-year">Academic Year:</label>
                    <input type="text" id="academic-year" class="py-3 px-4 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500" placeholder="e.g., 2024-2025">
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-gray-700 text-sm" for="start-date">Start Date:</label>
                    <input type="date" id="start-date" class="py-3 px-4 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500">
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-gray-700 text-sm" for="end-date">End Date:</label>
                    <input type="date" id="end-date" class="py-3 px-4 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500">
                </div>
            </div>
        </section>

        <!-- Section 3 -->
        <section class="bg-white rounded-xl p-6 md:p-8 mb-8 shadow-lg">
            <h2 class="text-xl font-semibold text-slate-700 mb-6 uppercase tracking-wider">Section 3: System Preferences</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-gray-700 text-sm" for="platform-name">Platform Name:</label>
                    <input type="text" id="platform-name" class="py-3 px-4 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500" value="OLIN" placeholder="Enter platform name">
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-gray-700 text-sm" for="default-language">Default Language:</label>
                    <select id="default-language" class="py-3 px-4 border border-gray-300 rounded-md bg-white cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500">
                        <option value="english" selected>English</option>
                        <option value="filipino">Filipino</option>
                        <option value="spanish">Spanish</option>
                        <option value="french">French</option>
                    </select>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-gray-700 text-sm" for="timezone">Timezone:</label>
                    <select id="timezone" class="py-3 px-4 border border-gray-300 rounded-md bg-white cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500">
                        <option value="asia/manila" selected>Asia/Manila</option>
                        <option value="utc">UTC</option>
                        <option value="america/new_york">America/New_York</option>
                    </select>
                </div>
            </div>
        </section>

        <!-- Section 4 -->
        <section class="bg-white rounded-xl p-6 md:p-8 mb-8 shadow-lg">
            <h2 class="text-xl font-semibold text-slate-700 mb-6 uppercase tracking-wider">Section 4: File Upload Limits</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-gray-700 text-sm" for="max-file-size">Max File Size (per file):</label>
                    <input type="text" id="max-file-size" class="py-3 px-4 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500" placeholder="e.g., 100 MB">
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-gray-700 text-sm" for="allowed-file-types">Allowed File Types:</label>
                    <input type="text" id="allowed-file-types" class="py-3 px-4 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500" placeholder="e.g., .pdf, .docx, .jpg">
                </div>
            </div>
        </section>

        <!-- Section 5 -->
        <section class="bg-white rounded-xl p-6 md:p-8 mb-8 shadow-lg">
            <h2 class="text-xl font-semibold text-slate-700 mb-6 uppercase tracking-wider">Section 5: Email Templates</h2>
            <div class="flex flex-col gap-2">
                <label class="font-semibold text-gray-700 text-sm">Manage Email Templates:</label>
                <p class="text-gray-600">Customize the email templates used for notifications, password resets, and user invitations.</p>
                <a href="#" class="text-indigo-600 hover:text-indigo-800 font-semibold mt-2 self-start">Go to Template Editor →</a>
            </div>
        </section>

        <!-- Buttons -->
        <div class="flex justify-end gap-4">
            <button type="button" class="py-3 px-6 bg-gray-200 text-gray-700 font-semibold rounded-lg shadow-sm hover:bg-gray-300 transition-colors duration-300" id="cancel-btn">Cancel</button>
            <button type="button" class="py-3 px-6 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 transition-colors duration-300" id="save-btn">Save Changes</button>
        </div>
    </main>
</x-layoutAdmin>
