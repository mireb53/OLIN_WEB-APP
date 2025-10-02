<!-- Delete Confirmation Modal -->
<div id="deleteUserModal" class="modal hidden">
    <div class="modal-content">
        <h2 class="text-lg font-bold mb-4 text-red-600">Confirm Delete</h2>
        <p class="text-gray-600 mb-2">Are you sure you want to delete this user? This action cannot be undone.</p>
        <p class="text-sm text-gray-500 mb-4">Type <strong>DELETE</strong> or the user's email to confirm.</p>
        <form id="deleteUserForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Confirm</label>
                <input type="text" name="confirm" id="deleteConfirmInput" class="border border-gray-300 rounded-lg px-3 py-2 w-full" placeholder="Type DELETE or email" required>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" class="px-4 py-2 bg-gray-300 rounded-lg" data-modal-close>Cancel</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg">Delete</button>
            </div>
        </form>
    </div>
</div>