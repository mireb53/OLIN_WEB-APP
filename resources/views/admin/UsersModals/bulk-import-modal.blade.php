

<!-- Bulk Import Users Modal -->
<div id="bulkImportModal" class="modal hidden">
    <div class="modal-content">
        <h2 class="text-lg font-bold mb-4">Bulk Import Users</h2>
        <form action="{{ route('admin.users.bulk-import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Upload CSV/Excel File</label>
                <input 
                    type="file" 
                    name="file"
                    accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" 
                    class="border border-gray-300 rounded-lg px-3 py-2 w-full bg-white"
                    required
                >
            </div>
            <p class="text-sm text-gray-500 mb-4">
                Required columns: <strong>Name, Email, Password</strong> <br>
                Optional but recommended: <strong>Role, Status</strong> <br>
                <em>Valid Roles:</em> student, instructor, schoolAdmin, superAdmin <br>
                <em>Valid Status:</em> active, inactive, suspended
            </p>

            <div class="flex justify-end space-x-2">
                <button type="button" class="px-4 py-2 bg-gray-300 rounded-lg" data-modal-close>Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Import</button>
            </div>
        </form>
    </div>
</div>
