<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-md w-full mx-4 border-t-8 border-red-600 relative">
        <button
            type="button"
            class="absolute top-3 right-3 text-gray-400 hover:text-gray-600"
            onclick="document.getElementById('deleteModal').classList.add('hidden')"
            aria-label="Close Delete Modal"
        >
            ✕
        </button>

        <h2 class="text-2xl font-bold text-gray-800 mb-4">Confirm Delete</h2>
        <p class="text-gray-600 mb-6">
            Are you sure you want to delete this course? This action cannot be undone.
        </p>

        <div class="flex justify-end gap-3">
            <button
                type="button"
                onclick="document.getElementById('deleteModal').classList.add('hidden')"
                class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition"
            >
                Cancel
            </button>
            <button
                id="confirmDeleteBtn"
                type="button"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                aria-label="Confirm Delete Course"
            >
                Delete
            </button>
        </div>
    </div>
</div>
