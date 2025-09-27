<!-- Delete Confirmation Modal -->
<div id="deleteUserModal" class="modal hidden">
    <div class="modal-content">
        <h2 class="text-lg font-bold mb-4 text-red-600">Confirm Delete</h2>
        <p class="text-gray-600 mb-6">Are you sure you want to delete this user? This action cannot be undone.</p>
        <form id="deleteUserForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="flex justify-end space-x-2">
                <button type="button" class="px-4 py-2 bg-gray-300 rounded-lg" data-modal-close>Cancel</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg">Delete</button>
            </div>
        </form>
    </div>
</div>