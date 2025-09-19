<x-layoutAdmin>


    <x-slot name="header">
        <div class="mb-8">
            <h1 class="text-3xl font-semibold text-slate-700 mb-2">Reports & Logs</h1>
            <p class="text-slate-500 italic">Access system-wide analytics, usage reports, and activity logs.</p>
        </div>
    </x-slot>

    <main class="flex-1 p-4 md:p-8">
        {{-- Section 1 --}}
        <section class="bg-white rounded-xl p-8 mb-8 shadow-lg">
            <h2 class="text-xl font-semibold text-slate-700 mb-6 uppercase tracking-wider">
                Section 1: System Reports Overview
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 text-white p-8 rounded-xl text-center relative overflow-hidden">
                    <div class="absolute -top-1/2 -right-1/2 w-full h-full bg-radial-gradient-tl opacity-10 transform rotate-45"></div>
                    <div class="text-5xl font-bold mb-2 relative z-10">500GB</div>
                    <div class="text-lg opacity-90 relative z-10">Total Content Synced</div>
                    <button class="bg-white/20 border border-white/30 text-white py-2 px-4 rounded-md mt-4 cursor-pointer transition-all duration-300 ease-in-out hover:bg-white/30 hover:-translate-y-0.5 relative z-10">
                        View Details
                    </button>
                </div>

                <div class="bg-gradient-to-br from-pink-400 to-red-500 text-white p-8 rounded-xl text-center relative overflow-hidden">
                    <div class="absolute -top-1/2 -right-1/2 w-full h-full bg-radial-gradient-tl opacity-10 transform rotate-45"></div>
                    <div class="text-5xl font-bold mb-2 relative z-10">80%</div>
                    <div class="text-lg opacity-90 relative z-10">Total Storage Used</div>
                    <button class="bg-white/20 border border-white/30 text-white py-2 px-4 rounded-md mt-4 cursor-pointer transition-all duration-300 ease-in-out hover:bg-white/30 hover:-translate-y-0.5 relative z-10">
                        View Details
                    </button>
                </div>

                <div class="bg-gradient-to-br from-cyan-400 to-blue-500 text-white p-8 rounded-xl text-center relative overflow-hidden">
                    <div class="absolute -top-1/2 -right-1/2 w-full h-full bg-radial-gradient-tl opacity-10 transform rotate-45"></div>
                    <div class="text-5xl font-bold mb-2 relative z-10">150</div>
                    <div class="text-lg opacity-90 relative z-10">Peak Concurrent Users</div>
                    <button class="bg-white/20 border border-white/30 text-white py-2 px-4 rounded-md mt-4 cursor-pointer transition-all duration-300 ease-in-out hover:bg-white/30 hover:-translate-y-0.5 relative z-10">
                        View Details
                    </button>
                </div>
            </div>

            <button class="bg-indigo-500 text-white border-none py-3 px-6 rounded-lg cursor-pointer font-medium transition-all duration-300 ease-in-out hover:bg-indigo-600 hover:-translate-y-0.5">
                Export All Reports (CSV)
            </button>
        </section>

        {{-- Section 2 --}}
        <section class="bg-white rounded-xl p-8 mb-8 shadow-lg">
            <h2 class="text-xl font-semibold text-slate-700 mb-6 uppercase tracking-wider">
                Section 2: System Activity Logs
            </h2>

            <div class="flex flex-col md:flex-row gap-4 mb-8">
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-gray-700 text-sm">Filter Logs:</label>
                    <select class="py-3 px-4 border border-gray-300 rounded-md bg-white cursor-pointer transition-colors duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 w-full md:w-52">
                        <option>Select Type </option>
                        <option>User Activity</option>
                        <option>Sync</option>
                        <option>Error</option>
                        <option>Content Upload</option>
                    </select>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-gray-700 text-sm">Date Range:</label>
                    <select class="py-3 px-4 border border-gray-300 rounded-md bg-white cursor-pointer transition-colors duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 w-full md:w-52">
                        <option>Select Date Range </option>
                        <option>Today</option>
                        <option>Last 7 days</option>
                        <option>Last 30 days</option>
                        <option>Custom Range</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse bg-white rounded-xl overflow-hidden text-sm md:text-base">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="text-slate-700 p-4 text-left font-semibold uppercase tracking-wider text-sm border-b-2 border-slate-200">Timestamp</th>
                            <th class="text-slate-700 p-4 text-left font-semibold uppercase tracking-wider text-sm border-b-2 border-slate-200">Activity Type</th>
                            <th class="text-slate-700 p-4 text-left font-semibold uppercase tracking-wider text-sm border-b-2 border-slate-200">User</th>
                            <th class="text-slate-700 p-4 text-left font-semibold uppercase tracking-wider text-sm border-b-2 border-slate-200">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="hover:bg-gray-100 transition-colors duration-300">
                            <td class="p-4 border-b border-slate-200">2025-07-23 10:00 AM</td>
                            <td class="p-4 border-b border-slate-200"><span class="py-1 px-3 rounded-lg text-xs font-medium bg-green-100 text-green-700">Sync Success</span></td>
                            <td class="p-4 border-b border-slate-200">Instructor Ana</td>
                            <td class="p-4 border-b border-slate-200">'Biology Module' synced.</td>
                        </tr>
                        <tr class="hover:bg-gray-100 transition-colors duration-300">
                            <td class="p-4 border-b border-slate-200">2025-07-23 09:30 AM</td>
                            <td class="p-4 border-b border-slate-200"><span class="py-1 px-3 rounded-lg text-xs font-medium bg-blue-100 text-blue-700">User Login</span></td>
                            <td class="p-4 border-b border-slate-200">Admin Jane</td>
                            <td class="p-4 border-b border-slate-200">Admin logged in.</td>
                        </tr>
                        <tr class="hover:bg-gray-100 transition-colors duration-300">
                            <td class="p-4 border-b border-slate-200">2025-07-23 08:45 AM</td>
                            <td class="p-4 border-b border-slate-200"><span class="py-1 px-3 rounded-lg text-xs font-medium bg-yellow-100 text-yellow-700">Content Upload</span></td>
                            <td class="p-4 border-b border-slate-200">Instructor Mark</td>
                            <td class="p-4 border-b border-slate-200">'Physics Exam.pdf' uploaded.</td>
                        </tr>
                        <tr class="hover:bg-gray-100 transition-colors duration-300">
                            <td class="p-4 border-b border-slate-200">2025-07-23 07:15 AM</td>
                            <td class="p-4 border-b border-slate-200"><span class="py-1 px-3 rounded-lg text-xs font-medium bg-red-100 text-red-700">Sync Error</span></td>
                            <td class="p-4 border-b border-slate-200">System</td>
                            <td class="p-4 border-b border-slate-200">Failed to sync 'Course X' to 3 devices.</td>
                        </tr>
                        <tr class="hover:bg-gray-100 transition-colors duration-300">
                            <td class="p-4 border-b border-slate-200">2025-07-23 06:45 AM</td>
                            <td class="p-4 border-b border-slate-200"><span class="py-1 px-3 rounded-lg text-xs font-medium bg-blue-100 text-blue-700">User Login</span></td>
                            <td class="p-4 border-b border-slate-200">Student Sarah</td>
                            <td class="p-4 border-b border-slate-200">Student logged in from mobile device.</td>
                        </tr>
                        <tr class="hover:bg-gray-100 transition-colors duration-300">
                            <td class="p-4 border-b border-slate-200">2025-07-23 06:30 AM</td>
                            <td class="p-4 border-b border-slate-200"><span class="py-1 px-3 rounded-lg text-xs font-medium bg-green-100 text-green-700">Sync Success</span></td>
                            <td class="p-4 border-b border-slate-200">Instructor Bob</td>
                            <td class="p-4 border-b border-slate-200">'Chemistry Lab Notes' synced to 5 devices.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-center gap-1 mt-8">
                <button class="py-2 px-4 border border-slate-200 bg-white text-gray-700 rounded-md cursor-pointer transition-all duration-200 ease-in-out hover:bg-indigo-500 hover:text-white hover:border-indigo-500">&lt;</button>
                <button class="py-2 px-4 border border-indigo-500 bg-indigo-500 text-white rounded-md cursor-pointer transition-all duration-200 ease-in-out">1</button>
                <button class="py-2 px-4 border border-slate-200 bg-white text-gray-700 rounded-md cursor-pointer transition-all duration-200 ease-in-out hover:bg-indigo-500 hover:text-white hover:border-indigo-500">2</button>
                <button class="py-2 px-4 border border-slate-200 bg-white text-gray-700 rounded-md cursor-pointer transition-all duration-200 ease-in-out hover:bg-indigo-500 hover:text-white hover:border-indigo-500">3</button>
                <button class="py-2 px-4 border border-slate-200 bg-white text-gray-700 rounded-md cursor-pointer transition-all duration-200 ease-in-out hover:bg-indigo-500 hover:text-white hover:border-indigo-500">4</button>
                <button class="py-2 px-4 border border-slate-200 bg-white text-gray-700 rounded-md cursor-pointer transition-all duration-200 ease-in-out hover:bg-indigo-500 hover:text-white hover:border-indigo-500">5</button>
                <button class="py-2 px-4 border border-slate-200 bg-white text-gray-700 rounded-md cursor-pointer transition-all duration-200 ease-in-out hover:bg-indigo-500 hover:text-white hover:border-indigo-500">&gt;</button>
            </div>
        </section>
    </main>

</x-layoutAdmin>