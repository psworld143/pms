// User Management dashboard logic
// Handles loading, filtering, creating, updating, and deleting users

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

    let currentUserId = null;
    let isEditMode = false;
    let debounceTimer = null;

    document.addEventListener('DOMContentLoaded', initializeUserManagement);

    function initializeUserManagement() {
        addFilterListeners();
        addFormListeners();
        addFieldValidationListeners();
        loadUsers();
    }

    function addFilterListeners() {
        const roleFilter = document.getElementById('role-filter');
        const statusFilter = document.getElementById('status-filter');
        const searchFilter = document.getElementById('search-filter');
        const filterButton = document.querySelector('button[onclick="loadUsers()"]');

        if (roleFilter) {
            roleFilter.addEventListener('change', loadUsers);
        }
        if (statusFilter) {
            statusFilter.addEventListener('change', loadUsers);
        }
        if (searchFilter) {
            searchFilter.addEventListener('keyup', () => debounce(loadUsers));
        }
        if (filterButton) {
            filterButton.addEventListener('click', loadUsers);
        }
    }

    function addFormListeners() {
        const form = document.getElementById('user-form');
        if (!form) return;

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const formData = new FormData(form);
            const payload = Object.fromEntries(formData.entries());

            const endpoint = isEditMode ? ENDPOINTS.update : ENDPOINTS.create;

            try {
                const response = await fetchJson(endpoint, {
                    method: 'POST',
                    body: JSON.stringify(payload),
                    headers: { 'Content-Type': 'application/json' }
                });

                if (response.success) {
                    showSuccess(response.message || 'User saved successfully');
                    closeUserModal();
                    loadUsers();
                } else {
                    throw new Error(response.message || 'Unable to save user');
                }
            } catch (error) {
                console.error('Error saving user:', error);
                showError(error.message || 'Unable to save user');
            }
        });
    }

    function addFieldValidationListeners() {
        const usernameField = document.getElementById('username');
        const emailField = document.getElementById('email');

        if (usernameField) {
            usernameField.addEventListener('blur', () => checkUsernameAvailability(usernameField.value));
        }
        if (emailField) {
            emailField.addEventListener('blur', () => checkEmailAvailability(emailField.value));
        }
    }

    async function loadUsers() {
        const container = document.getElementById('users-table-container');
        if (!container) return;

        setContainerLoading(container);

        const params = new URLSearchParams({
            role: getValue('role-filter'),
            status: getValue('status-filter'),
            search: getValue('search-filter')
        });

        try {
            const response = await fetchJson(`${ENDPOINTS.users}?${params.toString()}`);
            if (!response.success) {
                throw new Error(response.message || 'Unable to load users');
            }
            renderUsers(container, response.users || []);
        } catch (error) {
            console.error('Error loading users:', error);
            container.innerHTML = renderEmptyState('Unable to load users.');
            showError(error.message || 'Unable to load users');
        }
    }

    function renderUsers(container, users) {
        if (!users.length) {
            container.innerHTML = renderEmptyState('No users found with the current filters.');
            return;
        }

        const rows = users.map((user) => `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <div class="h-10 w-10 rounded-full bg-primary flex items-center justify-center">
                                <span class="text-sm font-medium text-white">${escapeHtml(user.name?.charAt(0)?.toUpperCase() || '?')}</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">${escapeHtml(user.name)}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(user.username)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(user.email)}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getRoleClass(user.role)}">
                        ${escapeHtml(getRoleLabel(user.role))}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        ${user.is_active ? 'Active' : 'Inactive'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${escapeHtml(formatDate(user.created_at))}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                    <button class="text-indigo-600 hover:text-indigo-900" data-action="edit" data-user="${user.id}">
                        <i class="fas fa-edit"></i>
                    </button>
                    ${Number(user.id) !== Number(getCurrentUserId()) ? `
                        <button class="text-red-600 hover:text-red-900" data-action="delete" data-user="${user.id}" data-username="${escapeAttribute(user.username)}" data-name="${escapeAttribute(user.name)}">
                            <i class="fas fa-trash"></i>
                        </button>
                    ` : ''}
                </td>
            </tr>
        `).join('');

        container.innerHTML = `
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        ${rows}
                    </tbody>
                </table>
            </div>
        `;

        container.querySelectorAll('[data-action="edit"]').forEach((button) => {
            button.addEventListener('click', () => editUser(button.dataset.user));
        });
        container.querySelectorAll('[data-action="delete"]').forEach((button) => {
            button.addEventListener('click', () => showDeleteUserModal(button.dataset.user, button.dataset.username, button.dataset.name));
        });
    }

    function openCreateUserModal() {
        isEditMode = false;
        currentUserId = null;

        const form = document.getElementById('user-form');
        if (form) {
            form.reset();
        }

        setText('user-modal-title', 'Add New User');
        setDisplay('password-field', 'block');
        setRequired('password', true);

        toggleModal('user-modal', true);
    }

    async function editUser(userId) {
        isEditMode = true;
        currentUserId = userId;
        setText('user-modal-title', 'Edit User');

        try {
            const response = await fetchJson(`${ENDPOINTS.details}?id=${encodeURIComponent(userId)}`);
            if (!response.success) {
                throw new Error(response.message || 'Unable to load user details');
            }

            populateUserForm(response.user);
            toggleModal('user-modal', true);
        } catch (error) {
            console.error('Error loading user details:', error);
            showError(error.message || 'Unable to load user details');
        }
    }

    function populateUserForm(user) {
        if (!user) return;

        setValue('user_id', user.id);
        setValue('name', user.name);
        setValue('username', user.username);
        setValue('email', user.email);
        setValue('role', user.role);
        setValue('is_active', user.is_active ? '1' : '0');

        setDisplay('password-field', 'none');
        setRequired('password', false);
    }

    function closeUserModal() {
        toggleModal('user-modal', false);
        currentUserId = null;
        isEditMode = false;
        setDisplay('password-field', 'block');
        setRequired('password', true);
    }

    function showDeleteUserModal(userId, username, name) {
        currentUserId = userId;
        setHtml('delete-user-info', `
            <p><strong>Name:</strong> ${escapeHtml(name)}</p>
            <p><strong>Username:</strong> ${escapeHtml(username)}</p>
            <p class="text-sm text-gray-600 mt-2">This action cannot be undone.</p>
        `);
        toggleModal('delete-user-modal', true);
    }

    function closeDeleteUserModal() {
        toggleModal('delete-user-modal', false);
        currentUserId = null;
    }

    async function confirmDeleteUser() {
        if (!currentUserId) return;

        try {
            const response = await fetchJson(ENDPOINTS.remove, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: currentUserId })
            });

            if (!response.success) {
                throw new Error(response.message || 'Unable to delete user');
            }

            showSuccess(response.message || 'User deleted successfully');
            closeDeleteUserModal();
            loadUsers();
        } catch (error) {
            console.error('Error deleting user:', error);
            showError(error.message || 'Unable to delete user');
        }
    }

    async function checkUsernameAvailability(username) {
        const feedback = document.getElementById('username-feedback');
        if (!username || !feedback) return;

        try {
            const response = await fetchJson(ENDPOINTS.checkUsername, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username })
            });

            const available = response.available;
            feedback.textContent = response.message || (available ? 'Username is available' : 'Username already exists');
            feedback.classList.toggle('text-green-600', available);
            feedback.classList.toggle('text-red-600', !available);
            feedback.classList.remove('hidden');
        } catch (error) {
            console.error('Error checking username:', error);
        }
    }

    async function checkEmailAvailability(email) {
        const feedback = document.getElementById('email-feedback');
        if (!email || !feedback) return;

        try {
            const response = await fetchJson(ENDPOINTS.checkEmail, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email })
            });

            const available = response.available;
            feedback.textContent = response.message || (available ? 'Email is available' : 'Email already exists');
            feedback.classList.toggle('text-green-600', available);
            feedback.classList.toggle('text-red-600', !available);
            feedback.classList.remove('hidden');
        } catch (error) {
            console.error('Error checking email:', error);
        }
    }

    function debounce(fn, delay = 400) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(fn, delay);
    }

    async function fetchJson(url, options = {}) {
        const response = await fetch(url, {
            credentials: 'include',
            ...options
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const payload = await response.json();
        if (payload.redirect) {
            window.location.href = payload.redirect;
        }
        return payload;
    }

    function setContainerLoading(container) {
        container.innerHTML = '
            <div class="p-6 text-center text-gray-500">
                <i class="fas fa-spinner fa-spin mr-2"></i>Loading users...
            </div>
        ';
    }

    function renderEmptyState(message) {
        return `
            <div class="p-8 text-center text-gray-500">
                <i class="fas fa-users text-4xl mb-4"></i>
                <p>${escapeHtml(message)}</p>
            </div>
        `;
    }

    function setDisplay(elementId, display) {
        const el = document.getElementById(elementId);
        if (el) {
            el.style.display = display;
        }
    }

    function setRequired(elementId, required) {
        const el = document.getElementById(elementId);
        if (el) {
            el.required = required;
        }
    }

    function setValue(elementId, value) {
        const el = document.getElementById(elementId);
        if (el) {
            el.value = value ?? '';
        }
    }

    function getValue(elementId) {
        const el = document.getElementById(elementId);
        return el ? el.value : '';
    }

    function setText(elementId, text) {
        const el = document.getElementById(elementId);
        if (el) {
            el.textContent = text;
        }
    }

    function setHtml(elementId, html) {
        const el = document.getElementById(elementId);
        if (el) {
            el.innerHTML = html;
        }
    }

    function toggleModal(elementId, show) {
        const el = document.getElementById(elementId);
        if (!el) return;
        el.classList.toggle('hidden', !show);
    }

    function getCurrentUserId() {
        return document.querySelector('body').getAttribute('data-current-user') || window.currentUserIdFromServer;
    }

    function getRoleClass(role) {
        const classes = {
            manager: 'bg-purple-100 text-purple-800',
            front_desk: 'bg-blue-100 text-blue-800',
            housekeeping: 'bg-green-100 text-green-800'
        };
        return classes[role] || 'bg-gray-100 text-gray-800';
    }

    function getRoleLabel(role) {
        const labels = {
            manager: 'Manager',
            front_desk: 'Front Desk',
            housekeeping: 'Housekeeping'
        };
        return labels[role] || role;
    }

    function formatDate(dateString) {
        if (typeof Utils !== 'undefined' && typeof Utils.formatDate === 'function') {
            return Utils.formatDate(dateString);
        }
        return new Date(dateString).toLocaleDateString();
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function escapeAttribute(value) {
        return String(value ?? '').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function showSuccess(message) {
        if (typeof Utils !== 'undefined' && typeof Utils.showNotification === 'function') {
            Utils.showNotification(message, 'success');
        }
    }

    function showError(message) {
        if (typeof Utils !== 'undefined' && typeof Utils.showNotification === 'function') {
            Utils.showNotification(message, 'error');
        }
    }

    // Expose modal functions globally for inline handlers
    window.openCreateUserModal = openCreateUserModal;
    window.editUser = editUser;
    window.closeUserModal = closeUserModal;
    window.showDeleteUserModal = showDeleteUserModal;
    window.closeDeleteUserModal = closeDeleteUserModal;
    window.confirmDeleteUser = confirmDeleteUser;

})();
