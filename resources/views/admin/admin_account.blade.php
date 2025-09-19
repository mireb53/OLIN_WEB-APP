{{-- resources/views/admin/admin_account.blade.php --}}
<x-layoutAdmin>
    @push('page_assets')
        @vite(['resources/css/admin/admin_account.css', 'resources/js/admin/admin_account.js'])
    @endpush
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-1">My Account (Admin)</h1>
    <p class="text-gray-600 text-lg italic">Manage your administrator profile, settings, and system preferences.</p>
</div>

<!-- Success Message -->
<div id="successMessage" class="hidden fixed bottom-6 right-6 z-50 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg transition-transform duration-300 transform translate-x-full">
    ✓ Administrator profile updated successfully!
</div>

<!-- A. My Profile Section -->
<section class="profile-card">
    <h2 class="section-title">👨‍💼 Administrator Profile</h2>
    
    <div class="profile-content">
        <div class="profile-avatar-section">
            <div class="admin-avatar">{{ strtoupper(substr(Auth::user()->first_name, 0, 1)) }}</div>
            <button id="changePhotoBtn" class="change-photo-btn">
                <span>✏️</span><span>Change Photo</span>
            </button>
            <div class="admin-badge">ADMINISTRATOR</div>
        </div>
        
        <div class="profile-form-grid">
            <div class="form-group">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" value="{{ Auth::user()->first_name ?? 'Admin' }}">
            </div>
            
            <div class="form-group">
                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" value="{{ Auth::user()->last_name ?? 'User' }}">
            </div>
            
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" value="{{ Auth::user()->email ?? 'admin@olin.com' }}" disabled>
                <small class="field-note">Email changes require IT verification</small>
            </div>
            
            <div class="form-group">
                <label for="role">Administrator Role:</label>
                <select id="role" disabled>
                    <option value="super_admin">Super Administrator</option>
                    <option value="admin" selected>Administrator</option>
                    <option value="sys_admin">System Administrator</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="admin_level">Admin Level:</label>
                <input type="text" id="admin_level" value="Level 1 - Full Access" disabled>
            </div>
            
            <div class="form-group">
                <label for="last_login">Last Login:</label>
                <input type="text" id="last_login" value="Today at 9:30 AM" disabled>
            </div>
            
            <div class="form-group">
                <label for="account_created">Account Created:</label>
                <input type="text" id="account_created" value="January 15, 2024" disabled>
            </div>
            
            <div class="form-group password-section">
                <button id="changePasswordBtn" class="change-password-btn">
                    <i class="fas fa-key"></i> Change Password
                </button>
                <small class="field-note">Requires current password verification</small>
            </div>
        </div>
    </div>
    
    <div class="profile-actions">
        <button id="saveProfileBtn" class="save-btn">
            <i class="fas fa-save"></i> Save Profile Changes
        </button>
    </div>
</section>

<!-- B. Administrator Settings Section -->
<section class="settings-card">
    <h2 class="section-title">⚙️ Administrator Settings</h2>
    
    <!-- Admin Notifications -->
    <div class="settings-group">
        <h3 class="settings-group-title">System Notifications</h3>
        <div class="settings-grid">
            <div class="toggle-setting">
                <span>System Alerts</span>
                <label class="toggle-switch">
                    <input type="checkbox" id="system_alerts" checked>
                    <div class="toggle-slider"></div>
                </label>
            </div>
            
            <div class="toggle-setting">
                <span>User Registrations</span>
                <label class="toggle-switch">
                    <input type="checkbox" id="user_registrations" checked>
                    <div class="toggle-slider"></div>
                </label>
            </div>
            
            <div class="toggle-setting">
                <span>Course Approval Requests</span>
                <label class="toggle-switch">
                    <input type="checkbox" id="course_approvals" checked>
                    <div class="toggle-slider"></div>
                </label>
            </div>
            
            <div class="toggle-setting">
                <span>System Maintenance</span>
                <label class="toggle-switch">
                    <input type="checkbox" id="maintenance_alerts" checked>
                    <div class="toggle-slider"></div>
                </label>
            </div>
            
            <div class="toggle-setting">
                <span>Security Alerts</span>
                <label class="toggle-switch">
                    <input type="checkbox" id="security_alerts" checked>
                    <div class="toggle-slider"></div>
                </label>
            </div>
        </div>
    </div>
    
    <!-- Dashboard Preferences -->
    <div class="settings-group">
        <h3 class="settings-group-title">Dashboard Preferences</h3>
        <div class="settings-form-grid">
            <div class="form-group">
                <label for="default_view">Default Dashboard View:</label>
                <select id="default_view">
                    <option value="system_overview" selected>System Overview</option>
                    <option value="recent_activity">Recent Activity</option>
                    <option value="user_analytics">User Analytics</option>
                    <option value="performance_metrics">Performance Metrics</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="refresh_interval">Data Refresh Interval:</label>
                <select id="refresh_interval">
                    <option value="real_time">Real-time</option>
                    <option value="5min" selected>5 minutes</option>
                    <option value="15min">15 minutes</option>
                    <option value="30min">30 minutes</option>
                </select>
            </div>
        </div>
    </div>
    
    <!-- Theme & Language -->
    <div class="settings-group">
        <h3 class="settings-group-title">Appearance & Language</h3>
        <div class="settings-form-grid">
            <div class="form-group">
                <label for="theme">Admin Theme:</label>
                <select id="theme">
                    <option value="light" selected>Light Theme</option>
                    <option value="dark">Dark Theme</option>
                    <option value="auto">Auto (System)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="language">System Language:</label>
                <select id="language">
                    <option value="english" selected>English</option>
                    <option value="filipino">Filipino</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="settings-note">
        <i class="fas fa-info-circle"></i>
        Settings are automatically saved and will take effect immediately.
    </div>
</section>

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

<!-- D. Administrator Log Out Section -->
<section class="logout-card">
    <h2 class="section-title">🔒 Administrator Session</h2>
    <div class="logout-content">
        <div class="logout-info">
            <h3>Secure Administrator Logout</h3>
            <p>Ensure all critical administrative tasks are completed before logging out.</p>
            <div class="session-info">
                <div class="session-item">
                    <i class="fas fa-clock"></i>
                    <span>Session started: Today at 9:30 AM</span>
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