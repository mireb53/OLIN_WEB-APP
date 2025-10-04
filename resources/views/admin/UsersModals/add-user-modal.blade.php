<!-- Add User Modal -->
<div id="addUserModal" class="modal hidden">
    <div class="modal-content">
        <h2 class="text-lg font-bold mb-4">Add User</h2>
        <form id="addUserForm" action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:outline-none focus:ring focus:border-indigo-500" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Role</label>
                <select name="role" id="addUserRole" class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:outline-none focus:ring focus:border-indigo-500" required>
                    <option value="student">Student</option>
                    <option value="instructor">Instructor</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">School</label>
                @if(auth()->user()->isSuperAdmin())
                    <select name="school_id" class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:outline-none focus:ring focus:border-indigo-500" required>
                        <option value="" @if(!$activeSchool) selected @endif>-- Select School --</option>
                        @foreach($schools as $s)
                            <option value="{{ $s->id }}" {{ $activeSchool && $activeSchool->id == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                    @if($activeSchool)
                        <p class="text-xs text-gray-500 mt-1">
                            Currently managing: <strong>{{ $activeSchool->name }}</strong>
                        </p>
                    @endif
                @else
                    <select name="school_id" class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:outline-none focus:ring focus:border-indigo-500" disabled>
                        @if(auth()->user()->school)
                            <option value="{{ auth()->user()->school->id }}" selected>
                                {{ auth()->user()->school->name }}
                            </option>
                        @else
                            <option value="" selected>-- No School Assigned --</option>
                        @endif
                    </select>
                    @if(auth()->user()->school)
                        <input type="hidden" name="school_id" value="{{ auth()->user()->school->id }}">
                    @endif
                @endif
                <p class="text-xs text-gray-500 mt-1">No password required for Student/Instructor. They will use Google login. On their first Google login, name fields auto-sync from Google.</p>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" class="px-4 py-2 bg-gray-300 rounded-lg" data-modal-close>Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Save</button>
            </div>
            <div id="bulkImportOption" class="mt-4 hidden">
                <button type="button" class="text-indigo-600 hover:underline" data-modal-target="bulkImportModal">Bulk Import Students</button>
            </div>
        </form>
    </div>
</div>