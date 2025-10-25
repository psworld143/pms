// Staff Management dashboard logic
// Handles loading, filtering, creating, updating, and deleting staff members

(function () {
    const API_BASE = '../../api/';
    const ENDPOINTS = {
        users: API_BASE + 'get-users.php',
        create: API_BASE + 'create-user.php',
        update: API_BASE + 'update-user.php',
        remove: API_BASE + 'delete-user.php',
        details: API_BASE + 'get-user-details.php',
        checkUsername: API_BASE + 'check-username.php',
        checkEmail: API_BASE + 'check-email.php'
    };

    const utils = window.HotelPMS?.Utils;

    let activeRequest = null;
    let editUserId = null;
    let debounceTimer = null;

    document.addEventListener('DOMContentLoaded', initializeStaffManagement);

    function initializeStaffManagement() {
        const tableBody = document.getElementById('staff-table-body');
        if (!tableBody) {
            return;
        }

        bindFilterControls();
        bindFormControls();
        bindModalControls();
        bindValidationControls();

        loadStaff();
    }

    function bindFilterControls() {
        const roleFilter = document.getElementById('staff-role-filter');
        const statusFilter = document.getElementById('staff-status-filter');
        const searchInput = document.getElementById('staff-search');
        const refreshButton = document.getElementById('staff-refresh-btn');

        if (roleFilter) {
            roleFilter.addEventListener('change', loadStaff);
        }
        if (statusFilter) {
            statusFilter.addEventListener('change', loadStaff);
        }
        if (searchInput) {
            searchInput.addEventListener('keyup', () => debounce(loadStaff));
        }
        if (refreshButton) {
            refreshButton.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                loadStaff();
            });
        }
    }

    function bindFormControls() {
        const form = document.getElementById('staff-form');
        if (!form) {
            return;
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const submitBtn = document.getElementById('staff-submit-btn');
            const submitText = document.getElementById('staff-submit-text');
            const loadingOverlay = document.getElementById('modal-loading');

            const password = document.getElementById('staff_password')?.value || '';
            const confirmPassword = document.getElementById('staff_confirm_password')?.value || '';

            if (!editUserId && password.length < 6) {
                showNotification('Password must be at least 6 characters long.', 'error');
                return;
            }

            if (password !== confirmPassword) {
                showNotification('Passwords do not match.', 'error');
                return;
            }

            const formData = new FormData(form);
            const payload = Object.fromEntries(formData.entries());
            payload.is_active = payload.is_active === '1' ? 1 : 0;

            if (editUserId) {
                payload.user_id = editUserId;
                if (!payload.password) {
                    delete payload.password;
                }
            }

            delete payload.confirm_password;

            try {
                setLoadingState(submitBtn, submitText, loadingOverlay, true, editUserId ? 'Saving...' : 'Adding...');

                const endpoint = editUserId ? ENDPOINTS.update : ENDPOINTS.create;
                const response = await fetchJson(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                if (!response.success) {
                    throw new Error(response.message || 'Unable to save staff member.');
                }

                showNotification(response.message || 'Staff member saved successfully.', 'success');
                closeAddStaffModal();
                loadStaff();
            } catch (error) {
                console.error('Error saving staff member:', error);
                showNotification(error.message || 'Unable to save staff member.', 'error');
            } finally {
                setLoadingState(submitBtn, submitText, loadingOverlay, false);
            }
        });
    }

    function bindModalControls() {
        const modal = document.getElementById('add-staff-modal');
        const cancelBtn = document.getElementById('staff-cancel-btn');
        const deleteModal = document.getElementById('delete-staff-modal');
        const deleteCancel = document.getElementById('delete-staff-cancel');
        const deleteConfirm = document.getElementById('delete-staff-confirm');

        if (modal) {
            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    closeAddStaffModal();
                }
            });
        }

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                if (modal && !modal.classList.contains('hidden')) {
                    closeAddStaffModal();
                }
                if (deleteModal && !deleteModal.classList.contains('hidden')) {
                    closeDeleteStaffModal();
                }
            }
        });

        if (cancelBtn) {
            cancelBtn.addEventListener('click', closeAddStaffModal);
        }

        if (deleteCancel) {
            deleteCancel.addEventListener('click', closeDeleteStaffModal);
        }

        if (deleteConfirm) {
            deleteConfirm.addEventListener('click', confirmDeleteStaff);
        }

        window.openAddStaffModal = openAddStaffModal;
        window.closeAddStaffModal = closeAddStaffModal;
    }

    function bindValidationControls() {
        const nameField = document.getElementById('staff_name');
        const usernameField = document.getElementById('staff_username');
        const emailField = document.getElementById('staff_email');
        const roleField = document.getElementById('staff_role');

        if (nameField) {
            nameField.addEventListener('blur', autoGenerateUsername);
        }

        if (usernameField) {
            usernameField.addEventListener('blur', () => checkUsername(usernameField.value));
        }

        if (emailField) {
            emailField.addEventListener('blur', () => checkEmail(emailField.value));
        }

        if (roleField) {
            roleField.addEventListener('change', syncDepartmentWithRole);
        }

        const confirmPassword = document.getElementById('staff_confirm_password');
        if (confirmPassword) {
            confirmPassword.addEventListener('input', () => {
                const password = document.getElementById('staff_password')?.value || '';
                if (confirmPassword.value && password !== confirmPassword.value) {
                    confirmPassword.classList.add('border-red-500');
                } else {
                    confirmPassword.classList.remove('border-red-500');
                }
            });
        }
    }

    async function loadStaff() {
        const tableBody = document.getElementById('staff-table-body');
        if (!tableBody) {
            return;
        }

        setTableLoading(tableBody);

        const params = new URLSearchParams({
            role: document.getElementById('staff-role-filter')?.value || '',
            status: document.getElementById('staff-status-filter')?.value || '',
            search: document.getElementById('staff-search')?.value || ''
        });

        try {
            activeRequest?.abort?.();
            activeRequest = new AbortController();

            const response = await fetchJson(`${ENDPOINTS.users}?${params.toString()}`, {
                signal: activeRequest.signal
            });

            if (!response.success) {
                throw new Error(response.message || 'Unable to load staff list.');
            }

            renderStaffTable(tableBody, response.users || []);
            updateStaffStats(response.users || []);
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }
            console.error('Error loading staff list:', error);
            tableBody.innerHTML = renderEmptyState('Unable to load staff list.');
            showNotification(error.message || 'Unable to load staff list.', 'error');
        }
    }

    function renderStaffTable(tableBody, staff) {
        if (!staff.length) {
            tableBody.innerHTML = renderEmptyState('No staff members match the current filters.');
            return;
        }

        const rows = staff.map((member) => `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <div class="h-10 w-10 rounded-full bg-primary flex items-center justify-center">
                                <span class="text-sm font-medium text-white">${escapeHtml((member.name || '?').charAt(0).toUpperCase())}</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">${escapeHtml(member.name)}</div>
                            <div class="text-sm text-gray-500">${escapeHtml(member.email)}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getRoleBadgeClass(member.role)}">
                        ${escapeHtml(getRoleLabel(member.role))}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${member.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        ${member.is_active ? 'Active' : 'Inactive'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${escapeHtml(formatDate(member.created_at))}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                    <button class="text-indigo-600 hover:text-indigo-900" data-action="edit" data-id="${member.id}">
                        <i class="fas fa-edit"></i>
                    </button>
                    ${Number(member.id) === Number(window.currentUserIdFromServer || 0) ? '' : `
                        <button class="text-red-600 hover:text-red-900" data-action="delete" data-id="${member.id}" data-name="${escapeAttribute(member.name)}" data-username="${escapeAttribute(member.username)}">
                            <i class="fas fa-trash"></i>
                        </button>
                    `}
                </td>
            </tr>
        `).join('');

        tableBody.innerHTML = rows;

        tableBody.querySelectorAll('[data-action="edit"]').forEach((button) => {
            button.addEventListener('click', () => openEditStaffModal(button.dataset.id));
        });

        tableBody.querySelectorAll('[data-action="delete"]').forEach((button) => {
            button.addEventListener('click', () => openDeleteStaffModal({
                id: button.dataset.id,
                name: button.dataset.name,
                username: button.dataset.username
            }));
        });
    }

    function updateStaffStats(staff) {
        const total = staff.length;
        const active = staff.filter((member) => Number(member.is_active) === 1).length;
        const inactive = total - active;
        const recent = staff.filter((member) => isCreatedWithin(member.created_at, 7)).length;

        setTextContent('staff-total-count', total.toString());
        setTextContent('staff-active-count', active.toString());
        setTextContent('staff-inactive-count', inactive.toString());
        setTextContent('staff-duty-count', active.toString());
        setTextContent('staff-training-count', inactive.toString());

        if (document.getElementById('staff-recent-count')) {
            setTextContent('staff-recent-count', recent.toString());
        }
    }

    async function openEditStaffModal(userId) {
        try {
            const response = await fetchJson(`${ENDPOINTS.details}?id=${encodeURIComponent(userId)}`);
            if (!response.success || !response.user) {
                throw new Error(response.message || 'Unable to load staff details.');
            }

            populateStaffForm(response.user);
            editUserId = response.user.id;
            showStaffModal('Edit Staff Member', false);
        } catch (error) {
            console.error('Error loading staff details:', error);
            showNotification(error.message || 'Unable to load staff details.', 'error');
        }
    }

    function populateStaffForm(user) {
        const form = document.getElementById('staff-form');
        if (!form || !user) {
            return;
        }

        form.reset();

        setFieldValue('staff_user_id', user.id);
        setFieldValue('staff_name', user.name);
        setFieldValue('staff_username', user.username);
        setFieldValue('staff_email', user.email);
        setFieldValue('staff_role', user.role);
        setFieldValue('staff_status', user.is_active ? '1' : '0');

        const hireField = document.getElementById('staff_hire_date');
        if (hireField) {
            hireField.value = user.created_at ? user.created_at.split(' ')[0] : '';
        }

        const passwordField = document.getElementById('staff_password');
        const confirmPasswordField = document.getElementById('staff_confirm_password');

        if (passwordField) {
            passwordField.value = '';
            passwordField.required = false;
        }
        if (confirmPasswordField) {
            confirmPasswordField.value = '';
            confirmPasswordField.required = false;
        }
    }

    function openDeleteStaffModal(user) {
        if (!user) return;

        editUserId = user.id;
        const infoContainer = document.getElementById('delete-staff-info');
        if (infoContainer) {
            infoContainer.innerHTML = `
                <p><strong>Name:</strong> ${escapeHtml(user.name)}</p>
                <p><strong>Username:</strong> ${escapeHtml(user.username)}</p>
                <p class="text-sm text-gray-500 mt-2">This action cannot be undone.</p>
            `;
        }

        toggleElement('delete-staff-modal', true);
    }

    async function confirmDeleteStaff() {
        if (!editUserId) {
            return;
        }

        try {
            const response = await fetchJson(ENDPOINTS.remove, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: editUserId })
            });

            if (!response.success) {
                throw new Error(response.message || 'Unable to delete staff member.');
            }

            showNotification(response.message || 'Staff member deleted successfully.', 'success');
            closeDeleteStaffModal();
            loadStaff();
        } catch (error) {
            console.error('Error deleting staff member:', error);
            showNotification(error.message || 'Unable to delete staff member.', 'error');
        }
    }

    function showStaffModal(title, isCreate) {
        const modalTitle = document.querySelector('#add-staff-modal h3');
        const submitText = document.getElementById('staff-submit-text');
        const passwordField = document.getElementById('staff_password');
        const confirmPasswordField = document.getElementById('staff_confirm_password');
        const hireField = document.getElementById('staff_hire_date');

        if (modalTitle) {
            modalTitle.textContent = title;
        }

        if (submitText) {
            submitText.textContent = isCreate ? 'Add Staff Member' : 'Save Changes';
        }

        if (passwordField) {
            passwordField.required = isCreate;
        }

        if (confirmPasswordField) {
            confirmPasswordField.required = isCreate;
        }

        if (isCreate && hireField) {
            hireField.value = new Date().toISOString().split('T')[0];
        }

        toggleElement('add-staff-modal', true);
    }

    function openAddStaffModal() {
        const form = document.getElementById('staff-form');
        if (form) {
            form.reset();
        }

        editUserId = null;
        setFieldValue('staff_user_id', '');

        const passwordField = document.getElementById('staff_password');
        const confirmPasswordField = document.getElementById('staff_confirm_password');
        if (passwordField) {
            passwordField.required = true;
        }
        if (confirmPasswordField) {
            confirmPasswordField.required = true;
        }

        showStaffModal('Add New Staff Member', true);
    }

    function closeAddStaffModal() {
        toggleElement('add-staff-modal', false);
        editUserId = null;
    }

    function closeDeleteStaffModal() {
        toggleElement('delete-staff-modal', false);
        editUserId = null;
    }

    function autoGenerateUsername() {
        const nameField = document.getElementById('staff_name');
        const usernameField = document.getElementById('staff_username');

        if (!nameField || !usernameField || usernameField.value) {
            return;
        }

        const parts = nameField.value.trim().toLowerCase().split(/\s+/);
        if (!parts.length) {
            return;
        }

        let username = parts[0];
        if (parts.length > 1) {
            username += parts[parts.length - 1].charAt(0);
        }
        usernameField.value = username.replace(/[^a-z0-9]/g, '');
    }

    function syncDepartmentWithRole() {
        const role = document.getElementById('staff_role')?.value;
        const departmentSelect = document.getElementById('staff_department');
        if (!role || !departmentSelect) {
            return;
        }

        const map = {
            manager: 'management',
            front_desk: 'front_desk',
            housekeeping: 'housekeeping',
            maintenance: 'maintenance',
            security: 'security',
            concierge: 'concierge',
            food_beverage: 'food_beverage'
        };

        if (map[role]) {
            departmentSelect.value = map[role];
        }
    }

    async function checkUsername(username) {
        if (!username) {
            return;
        }

        try {
            const response = await fetchJson(ENDPOINTS.checkUsername, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username })
            });

            const feedback = document.getElementById('username-feedback');
            if (feedback) {
                feedback.textContent = response.message || '';
                feedback.classList.toggle('hidden', !response.message);
                feedback.classList.toggle('text-green-600', response.available);
                feedback.classList.toggle('text-red-600', !response.available);
            }
        } catch (error) {
            console.error('Error checking username:', error);
        }
    }

    async function checkEmail(email) {
        if (!email) {
            return;
        }

        try {
            const response = await fetchJson(ENDPOINTS.checkEmail, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email })
            });

            const feedback = document.getElementById('email-feedback');
            if (feedback) {
                feedback.textContent = response.message || '';
                feedback.classList.toggle('hidden', !response.message);
                feedback.classList.toggle('text-green-600', response.available);
                feedback.classList.toggle('text-red-600', !response.available);
            }
        } catch (error) {
            console.error('Error checking email:', error);
        }
    }

    function setLoadingState(button, textContainer, overlay, isLoading, loadingLabel = 'Saving...') {
        if (button) {
            button.disabled = isLoading;
        }
        if (textContainer) {
            textContainer.innerHTML = isLoading ? `<i class="fas fa-spinner fa-spin mr-2"></i>${loadingLabel}` : textContainer.dataset.defaultText || textContainer.textContent;
            if (!textContainer.dataset.defaultText) {
                textContainer.dataset.defaultText = 'Add Staff Member';
            }
        }
        if (overlay) {
            overlay.classList.toggle('hidden', !isLoading);
        }
    }

    function setTableLoading(tableBody) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                    <i class="fas fa-spinner fa-spin mr-2"></i>Loading staff directory...
                </td>
            </tr>
        `;
    }

    function renderEmptyState(message) {
        return `
            <tr>
                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                    <i class="fas fa-users text-4xl mb-2"></i>
                    <p>${escapeHtml(message)}</p>
                </td>
            </tr>
        `;
    }

    function fetchJson(url, options = {}) {
        return fetch(url, {
            credentials: 'include',
            headers: {
                'X-API-Key': 'pms_users_api_2024',
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        }).then(async (response) => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            const payload = await response.json();
            if (payload.redirect) {
                window.location.href = payload.redirect;
                return Promise.reject(new Error('Redirecting'));
            }
            return payload;
        });
    }

    function getRoleLabel(role) {
        const roles = {
            manager: 'Manager',
            front_desk: 'Front Desk',
            housekeeping: 'Housekeeping',
            maintenance: 'Maintenance',
            security: 'Security',
            concierge: 'Concierge',
            food_beverage: 'Food & Beverage'
        };
        return roles[role] || role || 'Unknown';
    }

    function getRoleBadgeClass(role) {
        const classes = {
            manager: 'bg-purple-100 text-purple-800',
            front_desk: 'bg-blue-100 text-blue-800',
            housekeeping: 'bg-green-100 text-green-800',
            maintenance: 'bg-yellow-100 text-yellow-800',
            security: 'bg-red-100 text-red-800',
            concierge: 'bg-indigo-100 text-indigo-800',
            food_beverage: 'bg-pink-100 text-pink-800'
        };
        return classes[role] || 'bg-gray-100 text-gray-800';
    }

    function formatDate(value) {
        if (!value) return '';
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return value;
        }
        return date.toLocaleDateString();
    }

    function isCreatedWithin(value, days) {
        if (!value) return false;
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return false;
        }
        const diff = Date.now() - date.getTime();
        return diff <= days * 24 * 60 * 60 * 1000;
    }

    function setTextContent(elementId, text) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = text;
        }
    }

    function setFieldValue(elementId, value) {
        const element = document.getElementById(elementId);
        if (element) {
            element.value = value ?? '';
        }
    }

    function toggleElement(elementId, show) {
        const element = document.getElementById(elementId);
        if (element) {
            element.classList.toggle('hidden', !show);
            document.body.style.overflow = show ? 'hidden' : 'auto';
        }
    }

    function escapeHtml(value) {
        if (value == null) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function escapeAttribute(value) {
        if (value == null) return '';
        return String(value).replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function showNotification(message, type = 'info') {
        if (utils?.showNotification) {
            utils.showNotification(message, type);
        } else {
            alert(message);
        }
    }

    function debounce(fn, delay = 400) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(fn, delay);
    }

    window.closeDeleteStaffModal = closeDeleteStaffModal;
})();
