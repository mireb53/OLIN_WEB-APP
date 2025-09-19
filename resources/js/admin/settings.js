const customModal = document.getElementById('customModal');
const modalContent = document.getElementById('modalContent');
const modalOkBtn = document.getElementById('modalOkBtn');
const modalCancelBtn = document.getElementById('modalCancelBtn');
let resolveModal;

function showModal(message, isConfirmation = false) {
    return new Promise(resolve => {
        modalContent.innerText = message;
        if (isConfirmation) {
            modalCancelBtn.classList.remove('hidden');
        } else {
            modalCancelBtn.classList.add('hidden');
        }
        customModal.classList.remove('hidden');
        customModal.classList.add('flex');
        resolveModal = resolve;
    });
}

modalOkBtn.onclick = () => {
    customModal.classList.add('hidden');
    customModal.classList.remove('flex');
    resolveModal(true);
};

modalCancelBtn.onclick = () => {
    customModal.classList.add('hidden');
    customModal.classList.remove('flex');
    resolveModal(false);
};

function changePhoto() {
    showModal('Change Photo functionality would open a file picker dialog.');
}

function changePassword() {
    showModal('Change Password functionality would open a password change modal.');
}

function saveChanges() {
    const successMessage = document.getElementById('successMessage');
    successMessage.classList.remove('hidden');
    successMessage.classList.remove('translate-x-full');
    
    setTimeout(() => {
        successMessage.classList.add('translate-x-full');
        setTimeout(() => {
            successMessage.classList.add('hidden');
        }, 300);
    }, 3000);
    
    console.log('Profile updated:', {
        name: document.getElementById('name').value,
        email: document.getElementById('email').value,
        notifications: document.getElementById('notifications').checked,
        theme: document.getElementById('theme').value,
        language: document.getElementById('language').value
    });
}

function openFAQs() {
    showModal('Opening FAQs page...');
}

function contactSupport() {
    showModal('Opening contact support form...');
}