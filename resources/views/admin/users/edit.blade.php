<x-layoutAdmin>
    <main class="flex-1 p-6">
        {{-- Page Header --}}
        <div class="page-header">
            <h1 class="page-title">Edit User</h1>
            <p class="page-description">Update information for {{ $user->name }}</p>
        </div>

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="bg-white rounded-md shadow-sm p-6 mb-6">
            @csrf
            @method('PUT')
            
            @if($errors->any())
                <div class="alert alert-error mb-6">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="form-group">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" id="name" name="name" class="form-input" value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-input" value="{{ old('email', $user->email) }}" required>
                </div>

                <div class="form-group">
                    <label for="role" class="form-label">User Role</label>
                    <select id="role" name="role" class="form-select" required>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="instructor" {{ old('role', $user->role) === 'instructor' ? 'selected' : '' }}>Instructor</option>
                        <option value="student" {{ old('role', $user->role) === 'student' ? 'selected' : '' }}>Student</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="program_id" class="form-label">Program</label>
                    <select id="program_id" name="program_id" class="form-select">
                        <option value="">None</option>
                        @foreach(\App\Models\Program::all() as $program)
                            <option value="{{ $program->id }}" {{ old('program_id', $user->program_id) == $program->id ? 'selected' : '' }}>
                                {{ $program->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="section_id" class="form-label">Section</label>
                    <select id="section_id" name="section_id" class="form-select">
                        <option value="">None</option>
                        @foreach(\App\Models\Section::all() as $section)
                            <option value="{{ $section->id }}" {{ old('section_id', $user->section_id) == $section->id ? 'selected' : '' }}>
                                {{ $section->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="border-t border-gray-200 mt-6 pt-6">
                <h3 class="text-lg font-medium mb-4">Change Password (optional)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" id="password" name="password" class="form-input" autocomplete="new-password">
                        <p class="text-sm text-gray-500 mt-1">Leave blank to keep current password</p>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" autocomplete="new-password">
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-6 space-x-2">
                <a href="{{ route('admin.users.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Update User</button>
            </div>
        </form>
    </main>

    <style>
        .form-group {
            margin-bottom: 1rem;
        }
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
        }
        .form-input, .form-select {
            width: 100%;
            border-radius: 0.375rem;
            border: 1px solid #d1d5db;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }
        .form-input:focus, .form-select:focus {
            outline: none;
            ring: 2px;
            ring-color: #3b82f6;
            border-color: #3b82f6;
        }
    </style>
</x-layoutAdmin>