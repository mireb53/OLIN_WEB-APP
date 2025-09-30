// Admin Account JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin Account Page Loaded');

    // CRITICAL: Ensure all modals are hidden on page load
    ensureModalsAreHidden();
    
    // Initialize all functionality
    initializeButtons();
    initializeModalHandlers();
    initializeAdminSettings();
    initializeSecurityFeatures();
    initializeAutoSave();
});

// Ensure modals are hidden on page load (prevents auto-show)
function ensureModalsAreHidden() {
    const customModal = document.getElementById('customModal');
    const adminLogoutModal = document.getElementById('adminLogoutModal');
    
    if (customModal) {
        customModal.classList.add('hidden');
        customModal.classList.remove('flex');
    }
    
    if (adminLogoutModal) {
        adminLogoutModal.classList.add('hidden');
        adminLogoutModal.classList.remove('flex');
    }
    
    console.log('All modals forced to hidden state');
}

// Initialize modal button handlers
function initializeModalHandlers() {
    // Custom modal buttons
    document.getElementById('modalOkBtn')?.addEventListener('click', closeCustomModal);
    document.getElementById('modalCancelBtn')?.addEventListener('click', closeCustomModal);
}

function closeCustomModal() {
    const customModal = document.getElementById('customModal');
    if (customModal) {
        customModal.classList.add('hidden');
        customModal.classList.remove('flex');
    }
}

// 1. BUTTON INITIALIZATION
// ========================
function initializeButtons() {
    // Profile Section
    document.getElementById('changePhotoBtn')?.addEventListener('click', () => {
        const input = document.querySelector('#uploadPhotoForm #profile_image');
        input?.click();
    });
    // Toggle the real password form instead of showing an info modal
    document.getElementById('changePasswordBtn')?.addEventListener('click', () => {
        const f = document.getElementById('passwordForm');
        if (f) f.classList.toggle('hidden');
    });

    // Handle image upload via POST (avoid PUT from the other form)
    const uploadForm = document.getElementById('uploadPhotoForm');
    const uploadInput = uploadForm?.querySelector('#profile_image');
    if (uploadForm && uploadInput) {
        uploadInput.addEventListener('change', async () => {
            if (!uploadInput.files || uploadInput.files.length === 0) return;
            const url = uploadForm.getAttribute('data-upload-url');
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const formData = new FormData();
            formData.append('profile_image', uploadInput.files[0]);
            try {
                const resp = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token },
                    body: formData
                });
                if (!resp.ok) throw new Error('Upload failed');
                // Reload to display new image
                window.location.reload();
            } catch (e) {
                console.error(e);
                showInfoModal('Failed to upload image. Please try again.');
            }
        });
    }
    document.getElementById('saveProfileBtn')?.addEventListener('click', saveChanges);
    
    // Help Section
    document.getElementById('adminFaqsBtn')?.addEventListener('click', () => showInfoModal('Opening Administrator FAQ page with system-specific questions...'));
    document.getElementById('systemDocsBtn')?.addEventListener('click', () => showInfoModal('Opening technical documentation portal with API guides and system manuals...'));
    document.getElementById('contactSupportBtn')?.addEventListener('click', () => showInfoModal('Opening priority IT support form with administrator escalation...'));
    
    // Logout Section - ONLY show modal when this specific button is clicked
    document.getElementById('adminLogoutBtn')?.addEventListener('click', function() {
        console.log('Administrator Logout button clicked - showing confirmation modal');
        confirmAdminLogout();
    });
    document.getElementById('cancelLogoutBtn')?.addEventListener('click', cancelAdminLogout);
    document.getElementById('confirmLogoutBtn')?.addEventListener('click', executeAdminLogout);
}

// 2. MODAL & EVENT HANDLER FUNCTIONS
// ==================================

// Shows a simple information modal
function showInfoModal(message) {
    const customModal = document.getElementById('customModal');
    const modalContent = document.getElementById('modalContent');
    const modalCancelBtn = document.getElementById('modalCancelBtn');
    
    if (!customModal || !modalContent) {
        console.error('Custom modal elements not found');
        return;
    }
    
    modalContent.textContent = message;
    modalCancelBtn?.classList.add('hidden'); // Hide cancel button for info modals
    
    customModal.classList.remove('hidden');
    customModal.classList.add('flex');
}

function confirmAdminLogout() {
    console.log('Showing admin logout confirmation modal');
    const adminLogoutModal = document.getElementById('adminLogoutModal');
    if (adminLogoutModal) {
        adminLogoutModal.classList.remove('hidden');
        adminLogoutModal.classList.add('flex');
    } else {
        console.error('Admin logout modal not found');
    }
}

function cancelAdminLogout() {
    console.log('Admin logout cancelled');
    const adminLogoutModal = document.getElementById('adminLogoutModal');
    if (adminLogoutModal) {
        adminLogoutModal.classList.add('hidden');
        adminLogoutModal.classList.remove('flex');
    }
}

function executeAdminLogout() {
    console.log('Admin logout confirmed - submitting form');
    const adminLogoutModal = document.getElementById('adminLogoutModal');
    if (adminLogoutModal) {
        adminLogoutModal.classList.add('hidden');
        adminLogoutModal.classList.remove('flex');
    }
    
    logAdminAction('Administrator logout confirmed');
    document.getElementById('admin-logout-form')?.submit();
}

function saveChanges() {
    console.log('Save changes clicked');
    const successMessage = document.getElementById('successMessage');
    if (successMessage) {
        successMessage.classList.remove('hidden', 'translate-x-full');
        setTimeout(() => {
            successMessage.classList.add('translate-x-full');
            setTimeout(() => successMessage.classList.add('hidden'), 300);
        }, 3000);
    }
    logAdminAction('Profile changes saved');
}

// 3. SETTINGS AND OTHER INITIALIZATIONS
// =====================================
function initializeAdminSettings() {
    console.log('Initializing admin settings');
    loadAdminPreferences();
    setupNotificationToggles();
}

function initializeSecurityFeatures() {
    window.adminSecurityLog = {
        sessionStart: new Date(),
        actionsLog: [],
        securityLevel: 'Administrator'
    };
    logAdminAction('Admin account page loaded');
}

function initializeAutoSave() {
    const settingsInputs = document.querySelectorAll('#system_alerts, #user_registrations, #course_approvals, #maintenance_alerts, #security_alerts, #default_view, #refresh_interval, #theme, #language');
    settingsInputs.forEach(input => {
        input.addEventListener('change', function() {
            saveAdminSetting(this.name || this.id, this.type === 'checkbox' ? this.checked : this.value);
            showAutoSaveIndicator();
        });
    });
}

function loadAdminPreferences() {
    const preferences = {
        theme: localStorage.getItem('admin_theme') || 'light',
        language: localStorage.getItem('admin_language') || 'english',
        default_view: localStorage.getItem('admin_dashboard_view') || 'system_overview',
        refresh_interval: localStorage.getItem('admin_refresh_interval') || '5min'
    };
    Object.keys(preferences).forEach(key => {
        const element = document.getElementById(key);
        if (element) element.value = preferences[key];
    });
}

function setupNotificationToggles() {
    const notificationToggles = ['system_alerts', 'user_registrations', 'course_approvals', 'maintenance_alerts', 'security_alerts'];
    notificationToggles.forEach(toggleId => {
        const toggle = document.getElementById(toggleId);
        if (toggle) {
            const saved = localStorage.getItem(`admin_notification_${toggleId}`);
            if (saved !== null) toggle.checked = saved === 'true';
            toggle.addEventListener('change', () => handleNotificationToggle(toggleId, toggle.checked));
        }
    });
}

function handleNotificationToggle(settingId, isEnabled) {
    localStorage.setItem(`admin_notification_${settingId}`, isEnabled.toString());
    logAdminAction(`Toggled notification '${settingId}' to ${isEnabled}`);
    showAutoSaveIndicator();
}

function saveAdminSetting(settingName, value) {
    localStorage.setItem(`admin_${settingName}`, value);
    logAdminAction(`Saved setting '${settingName}' with value '${value}'`);
}

function logAdminAction(action) {
    if (window.adminSecurityLog) {
        console.log(`Admin Action: ${action}`);
        window.adminSecurityLog.actionsLog.push({ action, timestamp: new Date() });
    }
}

function showAutoSaveIndicator() {
    document.querySelector('.auto-save-indicator')?.remove();
    const indicator = document.createElement('div');
    indicator.className = 'auto-save-indicator';
    indicator.innerHTML = '<i class="fas fa-check"></i> Auto-saved';
    indicator.style.cssText = `
        position: fixed; top: 20px; right: 20px; background: linear-gradient(135deg, #059669 0%, #10b981 100%); 
        color: white; padding: 12px 20px; border-radius: 8px; font-size: 14px; font-weight: 600;
        z-index: 1000; box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3); display: flex;
        align-items: center; gap: 8px;
    `;
    document.body.appendChild(indicator);
    setTimeout(() => indicator.remove(), 2000);
}