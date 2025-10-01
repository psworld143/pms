<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and has front desk access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['front_desk', 'manager'])) {
    header('Location: ../../login.php');
    exit();
}

// Set page title
$page_title = 'Enhanced Guest Management';

// Include unified header and sidebar
include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';
?>

<!-- Main Content -->
<main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
        <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Enhanced Guest Management</h2>
        <div class="flex items-center space-x-4">
            <button onclick="openCreateGuestModal()" class="bg-primary hover:bg-secondary text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-user-plus mr-2"></i>Add New Guest
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" id="search-filter" placeholder="Search guests..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">VIP Status</label>
                <select id="vip-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">All Guests</option>
                    <option value="1">VIP Guests</option>
                    <option value="0">Regular Guests</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="status-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="recent">Recent</option>
                    <option value="frequent">Frequent</option>
                </select>
            </div>
            <div class="flex items-end">
                <button onclick="loadGuests()" class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
            </div>
        </div>
    </div>

    <!-- Guests Table -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Guest Directory</h3>
        </div>
        
        <div id="guests-table-container">
            <!-- Guests table will be loaded here -->
        </div>
    </div>

    <!-- Pagination -->
    <div id="pagination-container" class="mt-6">
        <!-- Pagination will be loaded here -->
    </div>
</main>

<!-- Add/Edit Guest Modal -->
<div id="guest-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-8 max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-900" id="modal-title">Add New Guest</h3>
            <button onclick="closeGuestModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form id="guest-form" class="space-y-6">
            <input type="hidden" id="guest_id" name="guest_id">
            
            <!-- Personal Information -->
            <div class="border-b border-gray-200 pb-6">
                <h4 class="text-md font-medium text-gray-900 mb-4">Personal Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                        <input type="text" name="first_name" id="first_name" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                        <input type="text" name="last_name" id="last_name" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" id="email" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone *</label>
                        <input type="tel" name="phone" id="phone" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                        <input type="date" name="date_of_birth" id="date_of_birth" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nationality</label>
                        <input type="text" name="nationality" id="nationality" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                </div>
                
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                    <textarea name="address" id="address" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                </div>
            </div>
            
            <!-- Identification -->
            <div class="border-b border-gray-200 pb-6">
                <h4 class="text-md font-medium text-gray-900 mb-4">Identification</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ID Type *</label>
                        <select name="id_type" id="id_type" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="">Select ID Type</option>
                            <option value="passport">Passport</option>
                            <option value="driver_license">Driver's License</option>
                            <option value="national_id">National ID</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ID Number *</label>
                        <input type="text" name="id_number" id="id_number" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                </div>
            </div>
            
            <!-- Guest Preferences -->
            <div class="pb-6">
                <h4 class="text-md font-medium text-gray-900 mb-4">Guest Preferences</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_vip" id="is_vip" value="1" 
                                   class="rounded border-gray-300 text-primary focus:ring-primary">
                            <span class="ml-2 text-sm text-gray-700">VIP Guest</span>
                        </label>
                    </div>
                </div>
                
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Preferences</label>
                    <textarea name="preferences" id="preferences" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                              placeholder="e.g., Non-smoking room, High floor, Extra pillows..."></textarea>
                </div>
                
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Service Notes</label>
                    <textarea name="service_notes" id="service_notes" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                              placeholder="Special instructions or notes about this guest..."></textarea>
                </div>
            </div>
            
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeGuestModal()" 
                        class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-6 py-2 bg-primary text-white rounded-md hover:bg-secondary">
                    Save Guest
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-guest-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <div class="flex items-center mb-6">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">Delete Guest</h3>
                <p class="text-sm text-gray-600">Are you sure you want to delete this guest?</p>
            </div>
        </div>
        
        <div id="delete-guest-info" class="mb-6 p-4 bg-gray-50 rounded-md">
            <!-- Guest info will be populated here -->
        </div>
        
        <div class="flex justify-end space-x-4">
            <button onclick="closeDeleteGuestModal()" 
                    class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button onclick="confirmDeleteGuest()" 
                    class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                Delete Guest
            </button>
        </div>
    </div>
</div>

<script>
let currentGuestId = null;
let currentPage = 1;
let isEditMode = false;

// Load guests on page load
document.addEventListener('DOMContentLoaded', function() {
    loadGuests();
    initializeGuestForm();
});

// Load guests
function loadGuests(page = 1) {
    currentPage = page;
    const search = document.getElementById('search-filter').value;
    const vip = document.getElementById('vip-filter').value;
    const status = document.getElementById('status-filter').value;
    
    fetch(`../../api/get-guests.php?search=${search}&vip=${vip}&status=${status}&page=${page}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayGuests(data.guests);
                displayPagination(data.pagination);
            } else {
                Utils.showNotification(data.message || 'Error loading guests', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading guests:', error);
            Utils.showNotification('Error loading guests', 'error');
        });
}

// Display guests in table
function displayGuests(guests) {
    const container = document.getElementById('guests-table-container');
    
    if (guests.length === 0) {
        container.innerHTML = `
            <div class="p-8 text-center text-gray-500">
                <i class="fas fa-users text-4xl mb-4"></i>
                <p>No guests found</p>
            </div>
        `;
        return;
    }
    
    const tableHTML = `
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stays</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    ${guests.map(guest => `
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full ${guest.is_vip ? 'bg-yellow-400' : 'bg-primary'} flex items-center justify-center">
                                            <span class="text-sm font-medium text-white">${guest.first_name.charAt(0).toUpperCase()}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">${guest.first_name} ${guest.last_name}</div>
                                        ${guest.is_vip ? '<div class="text-xs text-yellow-600">VIP Guest</div>' : ''}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">${guest.email || 'No email'}</div>
                                <div class="text-sm text-gray-500">${guest.phone}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">${guest.id_type}</div>
                                <div class="text-sm text-gray-500">${guest.id_number}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getGuestStatusClass(guest.status)}">
                                    ${getGuestStatusLabel(guest.status)}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${guest.total_stays || 0}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button onclick="viewGuestDetails(${guest.id})" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="editGuest(${guest.id})" class="text-indigo-600 hover:text-indigo-900">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="toggleVIPStatus(${guest.id}, ${guest.is_vip})" class="text-yellow-600 hover:text-yellow-900">
                                    <i class="fas fa-star"></i>
                                </button>
                                <button onclick="showDeleteGuestModal(${guest.id}, '${guest.first_name} ${guest.last_name}')" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
    
    container.innerHTML = tableHTML;
}

// Display pagination
function displayPagination(pagination) {
    const container = document.getElementById('pagination-container');
    
    if (pagination.total_pages <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let paginationHTML = '<div class="flex items-center justify-between">';
    paginationHTML += `<div class="text-sm text-gray-700">Showing ${pagination.from} to ${pagination.to} of ${pagination.total} results</div>`;
    paginationHTML += '<div class="flex space-x-2">';
    
    // Previous button
    if (pagination.current_page > 1) {
        paginationHTML += `<button onclick="loadGuests(${pagination.current_page - 1})" class="px-3 py-1 border border-gray-300 rounded-md text-sm hover:bg-gray-50">Previous</button>`;
    }
    
    // Page numbers
    for (let i = Math.max(1, pagination.current_page - 2); i <= Math.min(pagination.total_pages, pagination.current_page + 2); i++) {
        const isActive = i === pagination.current_page;
        paginationHTML += `<button onclick="loadGuests(${i})" class="px-3 py-1 border border-gray-300 rounded-md text-sm ${isActive ? 'bg-primary text-white' : 'hover:bg-gray-50'}">${i}</button>`;
    }
    
    // Next button
    if (pagination.current_page < pagination.total_pages) {
        paginationHTML += `<button onclick="loadGuests(${pagination.current_page + 1})" class="px-3 py-1 border border-gray-300 rounded-md text-sm hover:bg-gray-50">Next</button>`;
    }
    
    paginationHTML += '</div></div>';
    container.innerHTML = paginationHTML;
}

// Modal functions
function openCreateGuestModal() {
    isEditMode = false;
    document.getElementById('modal-title').textContent = 'Add New Guest';
    document.getElementById('guest-form').reset();
    document.getElementById('guest_id').value = '';
    document.getElementById('guest-modal').classList.remove('hidden');
}

function editGuest(guestId) {
    isEditMode = true;
    currentGuestId = guestId;
    document.getElementById('modal-title').textContent = 'Edit Guest';
    
    // Load guest data
    fetch(`../../api/get-guest-details.php?id=${guestId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const guest = data.guest;
                document.getElementById('guest_id').value = guest.id;
                document.getElementById('first_name').value = guest.first_name;
                document.getElementById('last_name').value = guest.last_name;
                document.getElementById('email').value = guest.email || '';
                document.getElementById('phone').value = guest.phone;
                document.getElementById('date_of_birth').value = guest.date_of_birth || '';
                document.getElementById('nationality').value = guest.nationality || '';
                document.getElementById('address').value = guest.address || '';
                document.getElementById('id_type').value = guest.id_type;
                document.getElementById('id_number').value = guest.id_number;
                document.getElementById('is_vip').checked = guest.is_vip;
                document.getElementById('preferences').value = guest.preferences || '';
                document.getElementById('service_notes').value = guest.service_notes || '';
                
                document.getElementById('guest-modal').classList.remove('hidden');
            } else {
                Utils.showNotification(data.message || 'Error loading guest details', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading guest details:', error);
            Utils.showNotification('Error loading guest details', 'error');
        });
}

function closeGuestModal() {
    document.getElementById('guest-modal').classList.add('hidden');
    currentGuestId = null;
    isEditMode = false;
}

function showDeleteGuestModal(guestId, guestName) {
    currentGuestId = guestId;
    document.getElementById('delete-guest-info').innerHTML = `
        <p><strong>Guest Name:</strong> ${guestName}</p>
        <p class="text-sm text-gray-600 mt-2">This action cannot be undone. The guest will be permanently deleted from the system.</p>
    `;
    document.getElementById('delete-guest-modal').classList.remove('hidden');
}

function closeDeleteGuestModal() {
    document.getElementById('delete-guest-modal').classList.add('hidden');
    currentGuestId = null;
}

function confirmDeleteGuest() {
    if (!currentGuestId) return;
    
    fetch('../../api/delete-guest.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            guest_id: currentGuestId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Utils.showNotification(data.message, 'success');
            loadGuests(currentPage);
            closeDeleteGuestModal();
        } else {
            Utils.showNotification(data.message || 'Error deleting guest', 'error');
        }
    })
    .catch(error => {
        console.error('Error deleting guest:', error);
        Utils.showNotification('Error deleting guest', 'error');
    });
}

// Initialize form
function initializeGuestForm() {
    document.getElementById('guest-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        fetch('../../api/save-guest.php', {
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
                loadGuests(currentPage);
                closeGuestModal();
            } else {
                Utils.showNotification(data.message || 'Error saving guest', 'error');
            }
        })
        .catch(error => {
            console.error('Error saving guest:', error);
            Utils.showNotification('Error saving guest', 'error');
        });
    });
}

// Utility functions
function getGuestStatusClass(status) {
    const classes = {
        'active': 'bg-green-100 text-green-800',
        'recent': 'bg-blue-100 text-blue-800',
        'frequent': 'bg-purple-100 text-purple-800',
        'guest': 'bg-gray-100 text-gray-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}

function getGuestStatusLabel(status) {
    const labels = {
        'active': 'Active',
        'recent': 'Recent',
        'frequent': 'Frequent',
        'guest': 'Guest'
    };
    return labels[status] || status;
}

function viewGuestDetails(guestId) {
    Utils.showNotification('Guest details feature coming soon', 'info');
}

function toggleVIPStatus(guestId, isVip) {
    fetch('../../api/toggle-vip-status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            guest_id: guestId,
            is_vip: !isVip
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Utils.showNotification(data.message, 'success');
            loadGuests(currentPage);
        } else {
            Utils.showNotification(data.message || 'Error updating VIP status', 'error');
        }
    })
    .catch(error => {
        console.error('Error updating VIP status:', error);
        Utils.showNotification('Error updating VIP status', 'error');
    });
}
</script>

<?php include '../../includes/footer.php'; ?>
