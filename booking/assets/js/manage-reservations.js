/**
 * Manage Reservations JavaScript
 * Hotel PMS - Front Desk Module
 */

class ReservationManager {
    constructor() {
        this.init();
    }

    init() {
        this.loadReservations();
        this.setupEventListeners();
    }

    // Load reservations
    async loadReservations() {
        try {
            const response = await fetch('../../api/get-all-reservations.php');
            const data = await response.json();
            
            if (data.success) {
                this.displayReservations(data.reservations);
            } else {
                console.error('Error loading reservations:', data.message);
            }
        } catch (error) {
            console.error('Error loading reservations:', error);
        }
    }

    // Display reservations
    displayReservations(reservations) {
        const container = document.getElementById('reservations-list');
        if (!container) return;

        if (reservations.length === 0) {
            container.innerHTML = `
                <div class="px-6 py-12 text-center text-gray-500">
                    <i class="fas fa-calendar text-4xl mb-4"></i>
                    <p>No reservations found</p>
                </div>
            `;
            return;
        }

        const table = `
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reservation</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-out</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    ${reservations.map(reservation => `
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center">
                                            <span class="text-white font-medium">
                                                ${reservation.guest_name.charAt(0).toUpperCase()}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">${reservation.guest_name}</div>
                                        <div class="text-sm text-gray-500">${reservation.email || ''}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${reservation.reservation_number}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                Room ${reservation.room_number}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${this.formatDate(reservation.check_in_date)}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${this.formatDate(reservation.check_out_date)}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${this.getStatusClass(reservation.status)}">
                                    ${this.getStatusLabel(reservation.status)}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button onclick="viewReservation(${reservation.id})" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="editReservation(${reservation.id})" class="text-yellow-600 hover:text-yellow-900">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    ${reservation.status === 'confirmed' ? `
                                        <button onclick="checkInReservation(${reservation.id})" class="text-green-600 hover:text-green-900">
                                            <i class="fas fa-sign-in-alt"></i>
                                        </button>
                                    ` : ''}
                                    ${reservation.status === 'checked_in' ? `
                                        <button onclick="checkOutReservation(${reservation.id})" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-sign-out-alt"></i>
                                        </button>
                                    ` : ''}
                                    <button onclick="cancelReservation(${reservation.id})" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-times"></i>
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
        // Edit reservation form submission
        const editForm = document.getElementById('edit-reservation-form');
        if (editForm) {
            editForm.addEventListener('submit', this.handleEditReservation.bind(this));
        }

        // Search functionality
        const searchInputs = ['search_reservation', 'search_guest', 'search_status', 'search_date_range'];
        searchInputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input) {
                input.addEventListener('input', this.handleSearch.bind(this));
            }
        });
    }

    // Handle edit reservation form submission
    async handleEditReservation(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const data = {
            reservation_id: formData.get('reservation_id'),
            first_name: formData.get('first_name'),
            last_name: formData.get('last_name'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            check_in_date: formData.get('check_in_date'),
            check_out_date: formData.get('check_out_date'),
            adults: formData.get('adults'),
            children: formData.get('children'),
            room_type: formData.get('room_type'),
            special_requests: formData.get('special_requests')
        };

        try {
            const response = await fetch('../../api/update-reservation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            
            if (result.success) {
                this.showNotification('Reservation updated successfully!', 'success');
                this.closeEditModal();
                this.loadReservations();
            } else {
                this.showNotification(result.message || 'Error updating reservation', 'error');
            }
        } catch (error) {
            console.error('Error updating reservation:', error);
            this.showNotification('Error updating reservation. Please try again.', 'error');
        }
    }

    // Handle search
    handleSearch() {
        const reservationNumber = document.getElementById('search_reservation').value;
        const guestName = document.getElementById('search_guest').value;
        const status = document.getElementById('search_status').value;
        const dateRange = document.getElementById('search_date_range').value;

        this.searchReservations({
            reservation_number: reservationNumber,
            guest_name: guestName,
            status: status,
            date_range: dateRange
        });
    }

    // Search reservations
    async searchReservations(filters) {
        try {
            const params = new URLSearchParams();
            Object.keys(filters).forEach(key => {
                if (filters[key]) {
                    params.append(key, filters[key]);
                }
            });

            const response = await fetch(`../../api/search-reservations.php?${params.toString()}`);
            const data = await response.json();
            
            if (data.success) {
                this.displayReservations(data.reservations);
            } else {
                console.error('Error searching reservations:', data.message);
            }
        } catch (error) {
            console.error('Error searching reservations:', error);
        }
    }

    // Edit reservation
    editReservation(reservationId) {
        this.loadReservationForEdit(reservationId);
    }

    // Load reservation for editing
    async loadReservationForEdit(reservationId) {
        try {
            const response = await fetch(`../../api/get-reservation-details.php?id=${reservationId}`);
            const data = await response.json();
            
            if (data.success) {
                this.populateEditForm(data.reservation);
                this.showEditModal();
            } else {
                this.showNotification('Error loading reservation details', 'error');
            }
        } catch (error) {
            console.error('Error loading reservation details:', error);
            this.showNotification('Error loading reservation details', 'error');
        }
    }

    // Populate edit form
    populateEditForm(reservation) {
        document.getElementById('edit_reservation_id').value = reservation.id;
        document.getElementById('edit_first_name').value = reservation.first_name;
        document.getElementById('edit_last_name').value = reservation.last_name;
        document.getElementById('edit_email').value = reservation.email;
        document.getElementById('edit_phone').value = reservation.phone;
        document.getElementById('edit_check_in_date').value = this.formatDateForInput(reservation.check_in_date);
        document.getElementById('edit_check_out_date').value = this.formatDateForInput(reservation.check_out_date);
        document.getElementById('edit_adults').value = reservation.adults;
        document.getElementById('edit_children').value = reservation.children;
        document.getElementById('edit_room_type').value = reservation.room_type;
        document.getElementById('edit_special_requests').value = reservation.special_requests || '';
    }

    // Show edit modal
    showEditModal() {
        const modal = document.getElementById('edit-modal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    // Close edit modal
    closeEditModal() {
        const modal = document.getElementById('edit-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    // Cancel reservation
    cancelReservation(reservationId) {
        this.loadReservationForCancel(reservationId);
    }

    // Load reservation for cancellation
    async loadReservationForCancel(reservationId) {
        try {
            const response = await fetch(`../../api/get-reservation-details.php?id=${reservationId}`);
            const data = await response.json();
            
            if (data.success) {
                this.populateCancelModal(data.reservation);
                this.showCancelModal();
            } else {
                this.showNotification('Error loading reservation details', 'error');
            }
        } catch (error) {
            console.error('Error loading reservation details:', error);
            this.showNotification('Error loading reservation details', 'error');
        }
    }

    // Populate cancel modal
    populateCancelModal(reservation) {
        document.getElementById('cancel_reservation_number').textContent = reservation.reservation_number;
        document.getElementById('cancel_guest_name').textContent = reservation.guest_name;
        this.cancelReservationId = reservation.id;
    }

    // Show cancel modal
    showCancelModal() {
        const modal = document.getElementById('cancel-modal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    // Close cancel modal
    closeCancelModal() {
        const modal = document.getElementById('cancel-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    // Confirm cancel reservation
    async confirmCancelReservation() {
        if (!this.cancelReservationId) return;

        try {
            const response = await fetch('../../api/cancel-reservation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ reservation_id: this.cancelReservationId })
            });

            const result = await response.json();
            
            if (result.success) {
                this.showNotification('Reservation cancelled successfully!', 'success');
                this.closeCancelModal();
                this.loadReservations();
            } else {
                this.showNotification(result.message || 'Error cancelling reservation', 'error');
            }
        } catch (error) {
            console.error('Error cancelling reservation:', error);
            this.showNotification('Error cancelling reservation. Please try again.', 'error');
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

    formatDateForInput(dateString) {
        const date = new Date(dateString);
        return date.toISOString().split('T')[0];
    }

    getStatusClass(status) {
        const statusClasses = {
            'confirmed': 'bg-yellow-100 text-yellow-800',
            'checked_in': 'bg-green-100 text-green-800',
            'checked_out': 'bg-gray-100 text-gray-800',
            'cancelled': 'bg-red-100 text-red-800',
            'no_show': 'bg-gray-100 text-gray-800'
        };
        return statusClasses[status] || 'bg-gray-100 text-gray-800';
    }

    getStatusLabel(status) {
        const statusLabels = {
            'confirmed': 'Confirmed',
            'checked_in': 'Checked In',
            'checked_out': 'Checked Out',
            'cancelled': 'Cancelled',
            'no_show': 'No Show'
        };
        return statusLabels[status] || status;
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
function loadReservations() {
    if (window.reservationManager) {
        window.reservationManager.loadReservations();
    }
}

function searchReservations() {
    if (window.reservationManager) {
        window.reservationManager.handleSearch();
    }
}

function clearFilters() {
    document.getElementById('search_reservation').value = '';
    document.getElementById('search_guest').value = '';
    document.getElementById('search_status').value = '';
    document.getElementById('search_date_range').value = '';
    
    if (window.reservationManager) {
        window.reservationManager.loadReservations();
    }
}

function viewReservation(reservationId) {
    window.location.href = `view-reservation.php?id=${reservationId}`;
}

function editReservation(reservationId) {
    if (window.reservationManager) {
        window.reservationManager.editReservation(reservationId);
    }
}

function checkInReservation(reservationId) {
    window.location.href = `check-in.php?reservation_id=${reservationId}`;
}

function checkOutReservation(reservationId) {
    window.location.href = `check-out.php?reservation_id=${reservationId}`;
}

function cancelReservation(reservationId) {
    if (window.reservationManager) {
        window.reservationManager.cancelReservation(reservationId);
    }
}

function closeEditModal() {
    if (window.reservationManager) {
        window.reservationManager.closeEditModal();
    }
}

function closeCancelModal() {
    if (window.reservationManager) {
        window.reservationManager.closeCancelModal();
    }
}

function confirmCancelReservation() {
    if (window.reservationManager) {
        window.reservationManager.confirmCancelReservation();
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.reservationManager = new ReservationManager();
});