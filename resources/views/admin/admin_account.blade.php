{{-- resources/views/admin/admin_account.blade.php --}}
<x-layoutAdmin>
    @push('page_assets')
        @vite(['resources/css/admin/admin_account.css', 'resources/js/admin/admin_account.js'])
    @endpush

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-1">My Account (Admin)</h1>
        <p class="text-gray-600 text-lg italic">Manage your administrator profile, security, and notifications.</p>
    </div>

    <!-- Success Message -->
    <div id="successMessage" class="hidden fixed bottom-6 right-6 z-50 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg transition-transform duration-300 transform translate-x-full">
        ✓ Administrator profile updated successfully!
    </div>

    <!-- A. My Profile Section -->
    <section class="profile-card">
        <h2 class="section-title">👨‍💼 Administrator Profile</h2>
        
        @php
            $admin = $admin ?? Auth::user();
            $fullName = $admin?->name ?? '';
            $nameParts = $fullName ? explode(' ', $fullName, 2) : ['', ''];
            $firstNameVal = old('first_name', $nameParts[0] ?? '');
            $lastNameVal = old('last_name', $nameParts[1] ?? '');
            $roleLabel = method_exists($admin, 'isSuperAdmin') && $admin->isSuperAdmin()
                ? 'SuperAdmin - Multi-School Access'
                : ((method_exists($admin, 'isSchoolAdmin') && $admin->isSchoolAdmin())
                    ? 'SchoolAdmin - Single-School Access'
                    : ucfirst(str_replace('_',' ', $admin->role ?? 'admin')));

            // Resolve current school display text
            $currentSchoolText = 'No school selected';
            if (method_exists($admin, 'isSuperAdmin') && $admin->isSuperAdmin()) {
                $activeId = session('active_school');
                if ($activeId) {
                    $sch = \App\Models\School::find($activeId);
                    if ($sch) {
                        $currentSchoolText = $sch->name;
                    } else {
                        $currentSchoolText = 'Unknown School (ID: '.$activeId.')';
                    }
                }
            } elseif (method_exists($admin, 'isSchoolAdmin') && $admin->isSchoolAdmin()) {
                $sid = $admin->school_id ?? null;
                if ($sid) {
                    $sch = \App\Models\School::find($sid);
                    if ($sch) $currentSchoolText = $sch->name;
                }
            }
        @endphp

        @if ($errors->any())
            <div class="mb-4 p-3 rounded bg-red-50 border border-red-200 text-red-700">
                <ul class="list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (session('status'))
            <div class="mb-4 p-3 rounded bg-green-50 border border-green-200 text-green-800">
                {{ session('status') }}
            </div>
        @endif

    <form action="{{ route('admin.account.update') }}" method="POST" class="profile-content" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="profile-avatar-section">
                @if($admin?->profile_image)
                    @php
                        $filename = basename($admin->profile_image);
                        $streamUrl = route('media.profile', ['filename' => $filename]);
                        $assetUrl = asset('storage/'.$admin->profile_image);
                    @endphp
                    <img src="{{ $streamUrl }}" onerror="this.onerror=null;this.src='{{ $assetUrl }}';" alt="Profile" class="w-24 h-24 rounded-full object-cover">
                @else
                    <div class="admin-avatar">{{ strtoupper(substr($admin?->name ?? 'A', 0, 1)) }}</div>
                @endif
                <button id="changePhotoBtn" type="button" class="change-photo-btn">
                    <span>✏️</span><span>Change Photo</span>
                </button>
                @if($admin?->profile_image)
                    <button type="button" class="text-red-600 text-sm underline mt-2" onclick="document.getElementById('deletePhotoForm').submit()">Delete Photo</button>
                @endif
                <div class="admin-badge">ADMINISTRATOR</div>
            </div>
            
            <div class="profile-form-grid">
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" value="{{ $firstNameVal }}">
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" value="{{ $lastNameVal }}">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" value="{{ $admin?->email ?? 'admin@example.com' }}" disabled>
                    <small class="field-note">Email changes require IT verification</small>
                </div>
                
                <div class="form-group">
                    <label for="admin_role">Admin Role:</label>
                    <input type="text" id="admin_role" value="{{ $roleLabel }}" disabled>
                </div>

                @if(method_exists($admin,'isSuperAdmin') && $admin->isSuperAdmin())
                <div class="form-group">
                    <label for="current_school">Current School:</label>
                    <input type="text" id="current_school" 
                        value="{{ $currentSchoolText }}" 
                        disabled>
                </div>
                @endif
                
                <div class="form-group">
                    <label for="last_login">Last Login:</label>
                    <input type="text" id="last_login" 
                        value="{{ $admin && $admin->last_login_at ? $admin->last_login_at->format('F d, Y h:i A') : 'N/A' }}" 
                        disabled>
                </div>
                
                <div class="form-group">
                    <label for="account_created">Account Created:</label>
                    <input type="text" id="account_created" 
                        value="{{ $admin?->created_at ? $admin->created_at->format('F d, Y h:i A') : '' }}" 
                        disabled>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $admin?->phone) }}">
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" value="{{ old('address', $admin?->address) }}">
                </div>
                
                <div class="form-group password-section">
                    <button id="changePasswordBtn" type="button" class="change-password-btn">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                    <small class="field-note">Requires current password verification</small>
                </div>
            </div>
            <div class="profile-actions">
                <button id="saveProfileBtn" class="save-btn" type="submit">
                    <i class="fas fa-save"></i> Save Profile Changes
                </button>
            </div>
        </form>

        {{-- Hidden delete photo form (separate to avoid nested forms) --}}
        <form id="deletePhotoForm" action="{{ route('admin.account.deleteImage') }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>

        {{-- Hidden upload photo form (separate POST form to avoid PUT spoofing) --}}
        <form id="uploadPhotoForm" action="{{ route('admin.account.uploadImage') }}" method="POST" enctype="multipart/form-data" class="hidden" data-upload-url="{{ route('admin.account.uploadImage') }}">
            @csrf
            <input type="file" id="profile_image" name="profile_image" accept="image/*">
        </form>
        
    <form id="passwordForm" action="{{ route('admin.account.changePassword') }}" method="POST" class="mt-6 hidden">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium">Current Password</label>
                    <input type="password" name="current_password" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium">New Password</label>
                    <input type="password" name="password" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="save-btn"><i class="fas fa-key"></i> Update Password</button>
            </div>
        </form>
    </section>

    {{-- Contact fields moved inside profile form above --}}


    <!-- C. Administrator Help Section -->
<section class="help-card">
    <h2 class="section-title">🆘 Administrator Support</h2>
    
    <div class="help-content">
        <div class="help-options">
            <button id="adminFaqsBtn" class="help-option-btn">
                <div class="help-icon">❓</div>
                <div class="help-text">
                    <h3>Admin FAQs</h3>
                    <p>Administrator-specific frequently asked questions</p>
                </div>
            </button>
            
            <button id="systemDocsBtn" class="help-option-btn">
                <div class="help-icon">📚</div>
                <div class="help-text">
                    <h3>System Documentation</h3>
                    <p>Technical documentation, API guides, and system manuals</p>
                </div>
            </button>
            
            <button id="contactSupportBtn" class="help-option-btn priority-support">
                <div class="help-icon">🚨</div>
                <div class="help-text">
                    <h3>Contact IT Support</h3>
                    <p>Priority support for administrators with escalation</p>
                </div>
            </button>
        </div>
    </div>
</section>

    <!-- C. Administrator Log Out Section -->
    <section class="logout-card">
        <h2 class="section-title">🔒 Administrator Session</h2>
        <div class="logout-content">
            <div class="logout-info">
                <h3>Secure Administrator Logout</h3>
                <p>Ensure all critical administrative tasks are completed before logging out.</p>
                <div class="session-info">
                    <div class="session-item">
                        <i class="fas fa-clock"></i>
                        <span>Session started: {{ now()->format('F d, Y h:i A') }}</span>
                    </div>
                    <div class="session-item">
                        <i class="fas fa-shield-alt"></i>
                        <span>Security level: Administrator</span>
                    </div>
                </div>
            </div>
            <form id="admin-logout-form" action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="button" id="adminLogoutBtn" class="admin-logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Administrator Logout
                </button>
            </form>
        </div>
    </section>

    <!-- Admin Logout Confirmation Modal -->
    <div id="adminLogoutModal" class="admin-modal hidden">
        <div class="admin-modal-content">
            <div class="modal-header">
                <h3>🔐 Administrator Logout Confirmation</h3>
            </div>
            <div class="modal-body">
                <div class="logout-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>You are logging out as <strong>Administrator</strong></p>
                </div>
                <div class="logout-checklist">
                    <p><strong>Please ensure:</strong></p>
                    <ul>
                        <li>All critical system tasks are completed</li>
                        <li>No pending user approvals require immediate attention</li>
                        <li>System maintenance schedules are reviewed</li>
                        <li>Emergency contacts are notified if necessary</li>
                    </ul>
                </div>
            </div>
            <div class="modal-actions">
                <button id="cancelLogoutBtn" class="cancel-btn">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button id="confirmLogoutBtn" class="confirm-logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Confirm Administrator Logout
                </button>
            </div>
        </div>
    </div>

    <!-- Custom Modal for General Alerts -->
    <div id="customModal" class="custom-modal hidden">
        <div class="custom-modal-content">
            <div id="modalContent" class="modal-text"></div>
            <div id="modalButtons" class="modal-buttons">
                <button id="modalOkBtn" class="modal-ok-btn">OK</button>
                <button id="modalCancelBtn" class="modal-cancel-btn hidden">Cancel</button>
            </div>
        </div>
    </div>
</x-layoutAdmin>
