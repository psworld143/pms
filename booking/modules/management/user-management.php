<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and has manager access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    header('Location: ../../login.php');
    exit();
}

// Set page title
$page_title = 'User Management';

// Get available roles dynamically
$available_roles = getUserRoles();

// Get user statistics
$user_stats = getUserStats();

// Include unified header and sidebar
include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';
?>
<!-- Main Content -->
<main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
        <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">User Management</h2>
        <div class="flex items-center space-x-4">
            <button onclick="openCreateUserModal()" class="bg-primary hover:bg-secondary text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-user-plus mr-2"></i>Add New User
            </button>
        </div>
    </div>

    <!-- User Statistics Dashboard -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-users text-blue-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Users</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo $user_stats['total_users']; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-user-check text-green-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Active Users</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo $user_stats['active_users']; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-user-times text-red-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Inactive Users</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo $user_stats['inactive_users']; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-chart-pie text-purple-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Roles</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo count($available_roles); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                <select id="role-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">All Roles</option>
                    <?php foreach ($available_roles as $role_key => $role_name): ?>
                        <option value="<?php echo $role_key; ?>"><?php echo $role_name; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="status-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" id="search-filter" placeholder="Search users..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div class="flex items-end">
                <button onclick="loadUsers()" class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Users Directory</h3>
        </div>
        
        <div id="users-table-container">
            <!-- Users table will be loaded here -->
        </div>
    </div>
</main>

<!-- Create/Edit User Modal -->
<div id="user-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-8 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-900" id="user-modal-title">Add New User</h3>
            <button onclick="closeUserModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form id="user-form" class="space-y-6">
            <input type="hidden" id="user_id" name="user_id">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                    <input type="text" name="name" id="name" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Username *</label>
                    <input type="text" name="username" id="username" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                           onblur="checkUsernameAvailability()">
                    <div id="username-feedback" class="text-sm mt-1 hidden"></div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                    <input type="email" name="email" id="email" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                           onblur="checkEmailAvailability()">
                    <div id="email-feedback" class="text-sm mt-1 hidden"></div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
                    <select name="role" id="role" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="">Select Role</option>
                        <?php foreach ($available_roles as $role_key => $role_name): ?>
                            <option value="<?php echo $role_key; ?>"><?php echo $role_name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div id="password-field">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                    <input type="password" name="password" id="password" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="is_active" id="is_active" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeUserModal()" 
                        class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-6 py-2 bg-primary text-white rounded-md hover:bg-secondary">
                    Save User
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-user-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <div class="flex items-center mb-6">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">Delete User</h3>
                <p class="text-sm text-gray-600">Are you sure you want to delete this user?</p>
            </div>
        </div>
        
        <div id="delete-user-info" class="mb-6 p-4 bg-gray-50 rounded-md">
            <!-- User info will be populated here -->
        </div>
        
        <div class="flex justify-end space-x-4">
            <button onclick="closeDeleteUserModal()" 
                    class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button onclick="confirmDeleteUser()" 
                    class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                Delete User
            </button>
        </div>
    </div>
</div>

<script>
let currentUserId = null;
let isEditMode = false;

// Load users on page load
document.addEventListener('DOMContentLoaded', function() {
    loadUsers();
    initializeUserForm();
});

// Load users
function loadUsers() {
    const role = document.getElementById('role-filter').value;
    const status = document.getElementById('status-filter').value;
    const search = document.getElementById('search-filter').value;
    
    fetch(`../../api/get-users.php?role=${role}&status=${status}&search=${search}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayUsers(data.users);
            } else {
                Utils.showNotification(data.message || 'Error loading users', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading users:', error);
            Utils.showNotification('Error loading users', 'error');
        });
}

// Display users in table
function displayUsers(users) {
    const container = document.getElementById('users-table-container');
    
    if (users.length === 0) {
        container.innerHTML = `
            <div class="p-8 text-center text-gray-500">
                <i class="fas fa-users text-4xl mb-4"></i>
                <p>No users found</p>
            </div>
        `;
        return;
    }
    
    const tableHTML = `
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
                    ${users.map(user => `
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-primary flex items-center justify-center">
                                            <span class="text-sm font-medium text-white">${user.name.charAt(0).toUpperCase()}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">${user.name}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${user.username}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${user.email}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getRoleClass(user.role)}">
                                    ${getRoleLabel(user.role)}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                    ${user.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${formatDate(user.created_at)}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button onclick="editUser(${user.id})" class="text-indigo-600 hover:text-indigo-900">
                                    <i class="fas fa-edit"></i>
                                </button>
                                ${user.id != <?php echo $_SESSION['user_id']; ?> ? `
                                <button onclick="showDeleteUserModal(${user.id}, '${user.username}', '${user.name}')" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                                ` : ''}
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
    
    container.innerHTML = tableHTML;
}

// Modal functions
function openCreateUserModal() {
    isEditMode = false;
    document.getElementById('user-modal-title').textContent = 'Add New User';
    document.getElementById('user-form').reset();
    document.getElementById('user_id').value = '';
    document.getElementById('password-field').style.display = 'block';
    document.getElementById('password').required = true;
    document.getElementById('user-modal').classList.remove('hidden');
}

function editUser(userId) {
    isEditMode = true;
    currentUserId = userId;
    document.getElementById('user-modal-title').textContent = 'Edit User';
    
    // Load user data
    fetch(`../../api/get-user-details.php?id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.user;
                document.getElementById('user_id').value = user.id;
                document.getElementById('name').value = user.name;
                document.getElementById('username').value = user.username;
                document.getElementById('email').value = user.email;
                document.getElementById('role').value = user.role;
                document.getElementById('is_active').value = user.is_active ? '1' : '0';
                
                // Hide password field for edit mode
                document.getElementById('password-field').style.display = 'none';
                document.getElementById('password').required = false;
                
                document.getElementById('user-modal').classList.remove('hidden');
            } else {
                Utils.showNotification(data.message || 'Error loading user details', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading user details:', error);
            Utils.showNotification('Error loading user details', 'error');
        });
}

function closeUserModal() {
    document.getElementById('user-modal').classList.add('hidden');
    currentUserId = null;
    isEditMode = false;
}

function showDeleteUserModal(userId, username, name) {
    currentUserId = userId;
    document.getElementById('delete-user-info').innerHTML = `
        <p><strong>Name:</strong> ${name}</p>
        <p><strong>Username:</strong> ${username}</p>
        <p class="text-sm text-gray-600 mt-2">This action cannot be undone.</p>
    `;
    document.getElementById('delete-user-modal').classList.remove('hidden');
}

function closeDeleteUserModal() {
    document.getElementById('delete-user-modal').classList.add('hidden');
    currentUserId = null;
}

function confirmDeleteUser() {
    if (!currentUserId) return;
    
    fetch('../../api/delete-user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            user_id: currentUserId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Utils.showNotification(data.message, 'success');
            loadUsers();
            closeDeleteUserModal();
        } else {
            Utils.showNotification(data.message || 'Error deleting user', 'error');
        }
    })
    .catch(error => {
        console.error('Error deleting user:', error);
        Utils.showNotification('Error deleting user', 'error');
    });
}

// Initialize form
function initializeUserForm() {
    document.getElementById('user-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        const url = isEditMode ? '../../api/update-user.php' : '../../api/create-user.php';
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Utils.showNotification(data.message, 'success');
                loadUsers();
                closeUserModal();
            } else {
                Utils.showNotification(data.message || 'Error saving user', 'error');
            }
        })
        .catch(error => {
            console.error('Error saving user:', error);
            Utils.showNotification('Error saving user', 'error');
        });
    });
}

// Utility functions
function getRoleClass(role) {
    const classes = {
        'manager': 'bg-purple-100 text-purple-800',
        'front_desk': 'bg-blue-100 text-blue-800',
        'housekeeping': 'bg-green-100 text-green-800'
    };
    return classes[role] || 'bg-gray-100 text-gray-800';
}

function getRoleLabel(role) {
    const labels = {
        'manager': 'Manager',
        'front_desk': 'Front Desk',
        'housekeeping': 'Housekeeping'
    };
    return labels[role] || role;
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString();
}
</script>

<?php include '../../includes/footer.php'; ?>
