/**
 * Guest Management JavaScript
 * Hotel PMS - Front Desk Module
 */

class GuestManager {
    constructor() {
        this.init();
    }

    init() {
        // Don't load guests automatically since they're loaded in PHP
        this.setupEventListeners();
    }

    // Load guests
    async loadGuests() {
        try {
            const response = await fetch('../../api/get-guests.php', {
                credentials: 'same-origin'
            });
            const data = await response.json();
            
            if (data.success) {
                this.displayGuests(data.guests);
            } else {
                console.error('Error loading guests:', data.message);
                // Don't show error notification since data is already loaded in PHP
            }
        } catch (error) {
            console.error('Error loading guests:', error);
            // Don't show error notification since data is already loaded in PHP
        }
    }

    // Display guests
    displayGuests(guests) {
        const container = document.getElementById('guests-table-container');
        if (!container) return;

        if (guests.length === 0) {
            container.innerHTML = `
                <div class="px-6 py-12 text-center text-gray-500">
                    <i class="fas fa-users text-4xl mb-4"></i>
                    <p>No guests found</p>
                </div>
            `;
            return;
        }

        const table = `
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">VIP Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Visit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Stays</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    ${guests.map(guest => `
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center">
                                            <span class="text-white font-medium">
                                                ${guest.first_name.charAt(0).toUpperCase()}${guest.last_name.charAt(0).toUpperCase()}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">${guest.first_name} ${guest.last_name}</div>
                                        <div class="text-sm text-gray-500">ID: ${guest.id_number}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">${guest.email || 'N/A'}</div>
                                <div class="text-sm text-gray-500">${guest.phone || 'N/A'}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                ${guest.is_vip ? `
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-crown mr-1"></i>VIP
                                    </span>
                                ` : `
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Regular
                                    </span>
                                `}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${guest.last_visit ? this.formatDate(guest.last_visit) : 'Never'}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${guest.total_stays || 0}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button onclick="viewGuestDetails(${guest.id})" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="editGuest(${guest.id})" class="text-yellow-600 hover:text-yellow-900">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="addFeedback(${guest.id})" class="text-green-600 hover:text-green-900">
                                        <i class="fas fa-comment"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;

        container.innerHTML = table;
    }

    // Setup event listeners
    setupEventListeners() {
        // Guest form submission
        const guestForm = document.getElementById('guest-form');
        if (guestForm) {
            guestForm.addEventListener('submit', this.handleGuestSubmit.bind(this));
        }

        // Feedback form submission
        const feedbackForm = document.getElementById('feedback-form');
        if (feedbackForm) {
            feedbackForm.addEventListener('submit', this.handleFeedbackSubmit.bind(this));
        }

        // Search and filter functionality is now handled by HTML forms/onchange
        // No JavaScript event listeners needed
    }

    // Handle guest form submission
    async handleGuestSubmit(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const data = {
            guest_id: formData.get('guest_id'),
            first_name: formData.get('first_name'),
            last_name: formData.get('last_name'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            date_of_birth: formData.get('date_of_birth'),
            nationality: formData.get('nationality'),
            address: formData.get('address'),
            id_type: formData.get('id_type'),
            id_number: formData.get('id_number'),
            is_vip: formData.get('is_vip') ? 1 : 0,
            preferences: formData.get('preferences'),
            service_notes: formData.get('service_notes')
        };

        try {
            const url = data.guest_id ? '../../api/update-guest.php' : '../../api/create-guest.php';
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify(data)
            });

            const result = await response.json();
            
            if (result.success) {
                this.showNotification(data.guest_id ? 'Guest updated successfully!' : 'Guest created successfully!', 'success');
                this.closeGuestModal();
                this.loadGuests();
            } else {
                this.showNotification(result.message || 'Error saving guest', 'error');
            }
        } catch (error) {
            console.error('Error saving guest:', error);
            this.showNotification('Error saving guest. Please try again.', 'error');
        }
    }

    // Handle feedback form submission
    async handleFeedbackSubmit(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const data = {
            guest_id: formData.get('guest_id'),
            reservation_id: formData.get('reservation_id'),
            feedback_type: formData.get('feedback_type'),
            category: formData.get('category'),
            rating: formData.get('rating'),
            comments: formData.get('comments')
        };

        try {
            const response = await fetch('../../api/create-feedback.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify(data)
            });

            const result = await response.json();
            
            if (result.success) {
                this.showNotification('Feedback submitted successfully!', 'success');
                this.closeFeedbackModal();
            } else {
                this.showNotification(result.message || 'Error submitting feedback', 'error');
            }
        } catch (error) {
            console.error('Error submitting feedback:', error);
            this.showNotification('Error submitting feedback. Please try again.', 'error');
        }
    }

    // Handle search
    handleSearch() {
        const searchTerm = document.getElementById('search-input').value;
        this.searchGuests(searchTerm);
    }

    // Handle filter
    handleFilter() {
        const vipFilter = document.getElementById('vip-filter').value;
        const statusFilter = document.getElementById('status-filter').value;
        
        this.filterGuests({
            vip: vipFilter,
            status: statusFilter
        });
    }

    // Search guests
    async searchGuests(searchTerm) {
        try {
            const response = await fetch(`../../api/search-guests.php?search=${encodeURIComponent(searchTerm)}`, {
                credentials: 'same-origin'
            });
            const data = await response.json();
            
            if (data.success) {
                this.displayGuests(data.guests);
            } else {
                console.error('Error searching guests:', data.message);
                // Don't show error notification, just log it
            }
        } catch (error) {
            console.error('Error searching guests:', error);
            // Don't show error notification, just log it
        }
    }

    // Filter guests
    async filterGuests(filters) {
        try {
            const params = new URLSearchParams();
            Object.keys(filters).forEach(key => {
                if (filters[key]) {
                    params.append(key, filters[key]);
                }
            });

            const response = await fetch(`../../api/filter-guests.php?${params.toString()}`, {
                credentials: 'same-origin'
            });
            const data = await response.json();
            
            if (data.success) {
                this.displayGuests(data.guests);
            } else {
                console.error('Error filtering guests:', data.message);
                // Don't show error notification, just log it
            }
        } catch (error) {
            console.error('Error filtering guests:', error);
            // Don't show error notification, just log it
        }
    }

    // Add new guest
    addNewGuest() {
        this.showGuestModal();
    }

    // Edit guest
    editGuest(guestId) {
        this.loadGuestForEdit(guestId);
    }

    // Load guest for editing
    async loadGuestForEdit(guestId) {
        try {
            const response = await fetch(`../../api/get-guest-details.php?id=${guestId}`, {
                credentials: 'same-origin'
            });
            const data = await response.json();
            
            if (data.success) {
                this.populateGuestForm(data.guest);
                this.showGuestModal();
            } else {
                this.showNotification('Error loading guest details', 'error');
            }
        } catch (error) {
            console.error('Error loading guest details:', error);
            this.showNotification('Error loading guest details', 'error');
        }
    }

    // Populate guest form
    populateGuestForm(guest) {
        document.getElementById('guest_id').value = guest.id;
        document.getElementById('first_name').value = guest.first_name;
        document.getElementById('last_name').value = guest.last_name;
        document.getElementById('email').value = guest.email || '';
        document.getElementById('phone').value = guest.phone || '';
        document.getElementById('date_of_birth').value = guest.date_of_birth || '';
        document.getElementById('nationality').value = guest.nationality || '';
        document.getElementById('address').value = guest.address || '';
        document.getElementById('id_type').value = guest.id_type || '';
        document.getElementById('id_number').value = guest.id_number || '';
        document.getElementById('is_vip').checked = guest.is_vip == 1;
        document.getElementById('preferences').value = guest.preferences || '';
        document.getElementById('service_notes').value = guest.service_notes || '';
        
        // Update modal title
        document.getElementById('modal-title').textContent = 'Edit Guest';
    }

    // View guest details
    viewGuestDetails(guestId) {
        this.loadGuestDetails(guestId);
    }

    // Load guest details
    async loadGuestDetails(guestId) {
        try {
            const response = await fetch(`../../api/get-guest-details.php?id=${guestId}`, {
                credentials: 'same-origin'
            });
            const data = await response.json();
            
            if (data.success) {
                this.displayGuestDetails(data.guest);
                this.showGuestDetailsModal();
            } else {
                this.showNotification('Error loading guest details', 'error');
            }
        } catch (error) {
            console.error('Error loading guest details:', error);
            this.showNotification('Error loading guest details', 'error');
        }
    }

    // Display guest details
    displayGuestDetails(guest) {
        const container = document.getElementById('guest-details-content');
        if (!container) return;

        const details = `
            <div class="space-y-6">
                <div class="border-b border-gray-200 pb-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Full Name</label>
                            <p class="text-sm text-gray-900">${guest.first_name} ${guest.last_name}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Email</label>
                            <p class="text-sm text-gray-900">${guest.email || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Phone</label>
                            <p class="text-sm text-gray-900">${guest.phone || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Date of Birth</label>
                            <p class="text-sm text-gray-900">${guest.date_of_birth || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Nationality</label>
                            <p class="text-sm text-gray-900">${guest.nationality || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">VIP Status</label>
                            <p class="text-sm text-gray-900">
                                ${guest.is_vip ? '<span class="text-yellow-600 font-semibold">VIP Guest</span>' : 'Regular Guest'}
                            </p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="text-sm font-medium text-gray-500">Address</label>
                        <p class="text-sm text-gray-900">${guest.address || 'N/A'}</p>
                    </div>
                </div>

                <div class="border-b border-gray-200 pb-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Identification</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">ID Type</label>
                            <p class="text-sm text-gray-900">${guest.id_type || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">ID Number</label>
                            <p class="text-sm text-gray-900">${guest.id_number || 'N/A'}</p>
                        </div>
                    </div>
                </div>

                <div class="border-b border-gray-200 pb-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Preferences & Notes</h4>
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Guest Preferences</label>
                            <p class="text-sm text-gray-900">${guest.preferences || 'No preferences recorded'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Service Notes</label>
                            <p class="text-sm text-gray-900">${guest.service_notes || 'No service notes'}</p>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Stay History</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Total Stays</label>
                            <p class="text-sm text-gray-900">${guest.total_stays || 0}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Last Visit</label>
                            <p class="text-sm text-gray-900">${guest.last_visit ? this.formatDate(guest.last_visit) : 'Never'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Total Spent</label>
                            <p class="text-sm text-gray-900">₱${(guest.total_spent || 0).toFixed(2)}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;

        container.innerHTML = details;
    }

    // Add feedback
    addFeedback(guestId) {
        document.getElementById('feedback_guest_id').value = guestId;
        this.showFeedbackModal();
    }

    // Show guest modal
    showGuestModal() {
        const modal = document.getElementById('guest-modal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    // Close guest modal
    closeGuestModal() {
        const modal = document.getElementById('guest-modal');
        if (modal) {
            modal.classList.add('hidden');
            document.getElementById('guest-form').reset();
            document.getElementById('modal-title').textContent = 'Add New Guest';
        }
    }

    // Show guest details modal
    showGuestDetailsModal() {
        const modal = document.getElementById('guest-details-modal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    // Close guest details modal
    closeGuestDetailsModal() {
        const modal = document.getElementById('guest-details-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    // Show feedback modal
    showFeedbackModal() {
        const modal = document.getElementById('feedback-modal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    // Close feedback modal
    closeFeedbackModal() {
        const modal = document.getElementById('feedback-modal');
        if (modal) {
            modal.classList.add('hidden');
            document.getElementById('feedback-form').reset();
        }
    }

    // Helper methods
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }

    showNotification(message, type) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white max-w-sm transform transition-all duration-300 translate-x-full`;
        
        // Set background color based on type
        switch(type) {
            case 'success':
                notification.classList.add('bg-green-500');
                break;
            case 'error':
                notification.classList.add('bg-red-500');
                break;
            case 'warning':
                notification.classList.add('bg-yellow-500');
                break;
            default:
                notification.classList.add('bg-blue-500');
        }
        
        notification.textContent = message;
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Remove after 5 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 5000);
    }
}

// Global functions
function addNewGuest() {
    if (window.guestManager) {
        window.guestManager.addNewGuest();
    }
}

function editGuest(guestId) {
    if (window.guestManager) {
        window.guestManager.editGuest(guestId);
    }
}

function viewGuestDetails(guestId) {
    if (window.guestManager) {
        window.guestManager.viewGuestDetails(guestId);
    }
}

function addFeedback(guestId) {
    if (window.guestManager) {
        window.guestManager.addFeedback(guestId);
    }
}

function closeGuestModal() {
    if (window.guestManager) {
        window.guestManager.closeGuestModal();
    }
}

function closeGuestDetailsModal() {
    if (window.guestManager) {
        window.guestManager.closeGuestDetailsModal();
    }
}

function closeFeedbackModal() {
    if (window.guestManager) {
        window.guestManager.closeFeedbackModal();
    }
}

// Guest action functions
async function viewGuest(guestId) {
    try {
        const response = await fetch(`../../api/get-guest-details.php?id=${guestId}`, {
            credentials: 'same-origin'
        });
        const data = await response.json();
        
        if (data.success) {
            showGuestDetailsModal(data.guest);
        } else {
            showNotification('Error loading guest details: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Error viewing guest:', error);
        showNotification('Error loading guest details', 'error');
    }
}

async function editGuest(guestId) {
    try {
        const response = await fetch(`../../api/get-guest-details.php?id=${guestId}`, {
            credentials: 'same-origin'
        });
        const data = await response.json();
        
        if (data.success) {
            showGuestModal(data.guest);
        } else {
            showNotification('Error loading guest details: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Error editing guest:', error);
        showNotification('Error loading guest details', 'error');
    }
}

async function deleteGuest(guestId) {
    if (!confirm('Are you sure you want to delete this guest? This action cannot be undone.')) {
        return;
    }
    
    try {
        const response = await fetch('../../api/delete-guest.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ id: guestId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Guest deleted successfully', 'success');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showNotification('Error deleting guest: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Error deleting guest:', error);
        showNotification('Error deleting guest', 'error');
    }
}

// Show guest details modal
function showGuestDetailsModal(guest) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
    modal.innerHTML = `
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Guest Details</h3>
                    <button onclick="closeGuestDetailsModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">First Name</label>
                        <p class="mt-1 text-sm text-gray-900">${guest.first_name || 'N/A'}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Last Name</label>
                        <p class="mt-1 text-sm text-gray-900">${guest.last_name || 'N/A'}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <p class="mt-1 text-sm text-gray-900">${guest.email || 'N/A'}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone</label>
                        <p class="mt-1 text-sm text-gray-900">${guest.phone || 'N/A'}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">VIP Status</label>
                        <p class="mt-1 text-sm text-gray-900">
                            ${guest.is_vip ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"><i class="fas fa-crown mr-1"></i>VIP</span>' : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Regular</span>'}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Total Stays</label>
                        <p class="mt-1 text-sm text-gray-900">${guest.total_stays || 0}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Total Spent</label>
                        <p class="mt-1 text-sm text-gray-900">$${(guest.total_spent || 0).toFixed(2)}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Last Visit</label>
                        <p class="mt-1 text-sm text-gray-900">${guest.last_visit || 'N/A'}</p>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <button onclick="closeGuestDetailsModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Close
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

// Close guest details modal
function closeGuestDetailsModal() {
    const modal = document.querySelector('.fixed.inset-0.bg-gray-600');
    if (modal) {
        modal.remove();
    }
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-md shadow-lg ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    notification.innerHTML = `
        <div class="flex items-center">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Don't initialize GuestManager to prevent API calls
    // window.guestManager = new GuestManager();
});