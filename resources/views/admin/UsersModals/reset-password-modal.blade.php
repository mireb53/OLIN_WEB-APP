<!-- Reset Password Modal -->
<div id="resetPasswordModal" class="modal hidden">
    <div class="modal-content">
        <h2 class="text-lg font-bold mb-4">Reset Password</h2>
        <form id="resetPasswordForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">New Password</label>
                <input type="password" name="password" class="border border-gray-300 rounded-lg px-3 py-2 w-full" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                <input type="password" name="password_confirmation" class="border border-gray-300 rounded-lg px-3 py-2 w-full" required>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" class="px-4 py-2 bg-gray-300 rounded-lg" data-modal-close>Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Reset</button>
            </div>
        </form>
    </div>
</div>