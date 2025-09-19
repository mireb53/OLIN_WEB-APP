<x-layoutAdmin>
    @push('page_assets')
        @vite([
            'resources/css/admin/course_management.css'
        ])
    @endpush

    <main class="flex-1 p-6">
        {{-- Page Header --}}
        <div class="page-header">
            <h1 class="page-title">Course Management</h1>
            <p class="page-description">
                View, manage, and configure all courses available in the OLIN system.
            </p>
        </div>

        {{-- Controls --}}
        <div class="controls-section">
            <div class="controls-row">
                <div class="search-box">
                    <input type="text" placeholder="Search Course by Name/ID" />
                </div>

                <select class="border border-gray-300 rounded-md px-3 pr-8 py-2 text-sm">
                    <option>Filter by Instructor</option>
                    <option>Mr. Santos</option>
                    <option>Ms. Reyes</option>
                    <option>Dr. Cruz</option>
                </select>

                <select class="border border-gray-300 rounded-md px-3 pr-8 py-2 text-sm">
                    <option>Filter by Status</option>
                    <option>Active</option>
                    <option>Archived</option>
                </select>

                <button class="btn-primary">+ Create New Course</button>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-lg shadow-md overflow-x-auto">
            <table class="course-table">
                <thead>
                    <tr>
                        <th>Course Name</th>
                        <th>Instructor</th>
                        <th>Students</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Active --}}
                    <tr>
                        <td><strong>Algebra 1</strong></td>
                        <td>Mr. Santos</td>
                        <td>45</td>
                        <td><span class="status-badge status-active">Active</span></td>
                        <td>2025-07-20</td>
                        <td>
                            <div class="action-buttons">
                                <a href="#" class="btn-small">View</a>
                                <a href="#" class="btn-small">Edit</a>
                                <a href="#" class="btn-small">Archive</a>
                            </div>
                        </td>
                    </tr>

                    {{-- Active --}}
                    <tr>
                        <td><strong>Calculus</strong></td>
                        <td>Ms. Reyes</td>
                        <td>30</td>
                        <td><span class="status-badge status-active">Active</span></td>
                        <td>2025-07-18</td>
                        <td>
                            <div class="action-buttons">
                                <a href="#" class="btn-small">View</a>
                                <a href="#" class="btn-small">Edit</a>
                                <a href="#" class="btn-small">Archive</a>
                            </div>
                        </td>
                    </tr>

                    {{-- Archived --}}
                    <tr>
                        <td><strong>Physics Lab</strong></td>
                        <td>Dr. Cruz</td>
                        <td>20</td>
                        <td><span class="status-badge status-archived">Archived</span></td>
                        <td>2025-06-15</td>
                        <td>
                            <div class="action-buttons">
                                <a href="#" class="btn-small">View</a>
                                <a href="#" class="btn-small">Edit</a>
                                <a href="#" class="btn-small">Activate</a>
                            </div>
                        </td>
                    </tr>

                    {{-- Active --}}
                    <tr>
                        <td><strong>Chemistry Notes</strong></td>
                        <td>Mr. Santos</td>
                        <td>28</td>
                        <td><span class="status-badge status-active">Active</span></td>
                        <td>2025-07-21</td>
                        <td>
                            <div class="action-buttons">
                                <a href="#" class="btn-small">View</a>
                                <a href="#" class="btn-small">Edit</a>
                                <a href="#" class="btn-small">Archive</a>
                            </div>
                        </td>
                    </tr>

                    {{-- Active --}}
                    <tr>
                        <td><strong>Biology Fundamentals</strong></td>
                        <td>Ms. Reyes</td>
                        <td>35</td>
                        <td><span class="status-badge status-active">Active</span></td>
                        <td>2025-07-19</td>
                        <td>
                            <div class="action-buttons">
                                <a href="#" class="btn-small">View</a>
                                <a href="#" class="btn-small">Edit</a>
                                <a href="#" class="btn-small">Archive</a>
                            </div>
                        </td>
                    </tr>

                    {{-- Active --}}
                    <tr>
                        <td><strong>World History</strong></td>
                        <td>Dr. Cruz</td>
                        <td>42</td>
                        <td><span class="status-badge status-active">Active</span></td>
                        <td>2025-07-22</td>
                        <td>
                            <div class="action-buttons">
                                <a href="#" class="btn-small">View</a>
                                <a href="#" class="btn-small">Edit</a>
                                <a href="#" class="btn-small">Archive</a>
                            </div>
                        </td>
                    </tr>

                    {{-- Pending (new row for approvals) --}}
                    <tr>
                        <td><strong>Introduction to Computer Programming</strong></td>
                        <td>Prof. Dela Cruz</td>
                        <td>—</td>
                        <td><span class="status-badge status-pending">Pending</span></td>
                        <td>2025-07-25</td>
                        <td>
                            <div class="action-buttons">
                                <a href="#" class="btn-small">View</a>
                                <a href="#" class="btn-small">Approve</a>
                                <a href="#" class="btn-small">Reject</a>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="pagination">
            <button disabled>← Previous</button>
            <button class="active">1</button>
            <button>2</button>
            <button>3</button>
            <button>Next →</button>
        </div>
    </main>
</x-layoutAdmin>
