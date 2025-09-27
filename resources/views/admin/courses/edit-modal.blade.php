<!-- resources/views/admin/courses/edit-modal.blade.php -->
<div id="editCourseModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div id="modalContainer" class="bg-white text-gray-800 rounded-2xl shadow-2xl p-6 max-w-lg w-full mx-4 relative transition-colors duration-300">
        
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
        <h3 class="text-2xl font-bold mb-4 flex items-center gap-2 mt-8">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 113 3L12 15l-4 1 1-4 9.5-9.5z" />
            </svg>
            Edit Course
        </h3>

        <!-- Form -->
        <form id="modalEditForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            
            <!-- Title -->
            <div>
                <label class="block font-semibold mb-1">Title</label>
                <input type="text" id="modalEditTitle" name="title"
                    class="w-full px-3 py-2 rounded-lg bg-gray-100 border border-gray-300 text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                    required>
            </div>

            <!-- Status -->
            <div>
                <label class="block font-semibold mb-1">Status</label>
                <select id="modalEditStatus" name="status"
                    class="w-full px-3 py-2 rounded-lg bg-gray-100 border border-gray-300 text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                    required>
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                    <option value="archived">Archived</option>
                </select>
            </div>

            <!-- 2FA Step 1 -->
            <div id="modalEdit2FAStep1">
                <button type="button" onclick="requestEdit2FACode(true)" 
                    class="w-full bg-gradient-to-r from-blue-600 to-blue-400 text-white font-bold py-2 px-4 rounded-lg shadow hover:shadow-lg transition">
                    Send Verification Code to Email
                </button>
                <button type="button" onclick="closeEditModal()" 
                    class="w-full bg-gray-300 text-gray-800 py-2 px-4 rounded-lg hover:bg-gray-400 transition mt-2">
                    Cancel
                </button>
            </div>

            <!-- 2FA Step 2 -->
            <div id="modalEdit2FAStep2" class="hidden mt-3">
                <label class="block font-semibold mb-2">Enter Verification Code</label>
                <input type="text" id="modalEdit2FACodeInput"
                    class="w-full px-4 py-2 bg-gray-100 border border-gray-300 text-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition mb-3"
                    placeholder="Enter code">
                <div id="modalEdit2FAError" class="text-red-500 mt-2 text-center"></div>
                <div class="flex gap-2 justify-end">
                    <button type="button" onclick="closeEditModal()" 
                        class="bg-gray-200 text-gray-800 py-2 px-4 rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </button>
                    <button type="button" id="modalVerifySaveBtn" 
                        class="bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition">
                        Verify & Save
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById("modalContainer");
    const toggleBtn = document.getElementById("toggleModeBtn");
    let darkMode = false;

    toggleBtn.addEventListener("click", () => {
        darkMode = !darkMode;
        if (darkMode) {
            modal.classList.remove("bg-white", "text-gray-800");
            modal.classList.add("bg-gray-900", "text-gray-200");
            toggleBtn.textContent = "☀️";

            document.querySelectorAll("#modalContainer input, #modalContainer select").forEach(el => {
                el.classList.remove("bg-gray-100", "border-gray-300", "text-gray-800");
                el.classList.add("bg-gray-800", "border-gray-600", "text-gray-200");
            });
        } else {
            modal.classList.remove("bg-gray-900", "text-gray-200");
            modal.classList.add("bg-white", "text-gray-800");
            toggleBtn.textContent = "🌙";

            document.querySelectorAll("#modalContainer input, #modalContainer select").forEach(el => {
                el.classList.remove("bg-gray-800", "border-gray-600", "text-gray-200");
                el.classList.add("bg-gray-100", "border-gray-300", "text-gray-800");
            });
        }
    });
</script>
