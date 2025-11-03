/**
 * Check-in Management JavaScript
 * Hotel PMS - Front Desk Module
 */

class CheckInManager {
    constructor() {
        this.init();
    }

    init() {
        this.loadPendingCheckins();
        this.setupEventListeners();
    }

    // Load pending check-ins
    async loadPendingCheckins() {
        try {
            const response = await fetch('../../api/get-pending-checkins.php');
            const data = await response.json();
            
            if (data.success) {
                this.displayPendingCheckins(data.reservations);
            } else {
                console.error('Error loading pending check-ins:', data.message);
            }
        } catch (error) {
            console.error('Error loading pending check-ins:', error);
        }
    }

    // Display pending check-ins
    displayPendingCheckins(reservations) {
        const container = document.getElementById('pending-checkins');
        if (!container) return;
        const safeReservations = Array.isArray(reservations) ? reservations : [];

        if (safeReservations.length === 0) {
            container.innerHTML = `
                <div class="px-6 py-12 text-center text-gray-500">
                    <i class="fas fa-calendar-check text-4xl mb-4"></i>
                    <p>No pending check-ins for today</p>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    ${safeReservations.map(reservation => {
                        const guestName = (reservation.guest_name && String(reservation.guest_name).trim())
                            || [reservation.first_name, reservation.last_name].filter(Boolean).join(' ').trim()
                            || 'Guest';
                        const initial = ((guestName || '').toString().trim().slice(0, 1) || '?').toUpperCase();
                        const contact = reservation.email || reservation.phone || '';
                        const roomNumber = reservation.room_number || reservation.room || '';
                        const reservationNumber = reservation.reservation_number || reservation.number || '';
                        const checkIn = reservation.check_in_date || reservation.check_in || reservation.checkin_date;
                        return `
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center">
                                            <span class="text-white font-medium">
                                                ${initial}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">${guestName}</div>
                                        <div class="text-sm text-gray-500">${contact}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${reservationNumber}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                Room ${roomNumber}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${this.formatDateTime(checkIn)}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="startCheckIn(${reservation.id})" class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-sign-in-alt mr-1"></i>Check In
                                </button>
                                <button onclick="viewReservationDetails(${reservation.id})" class="text-gray-600 hover:text-gray-900">
                                    <i class="fas fa-eye mr-1"></i>View
                                </button>
                            </td>
                        </tr>
                        `;
                    }).join('')}
                </tbody>
            </table>
        `;

        container.innerHTML = table;
    }

    // Setup event listeners
    setupEventListeners() {
        // Check-in form submission
        const checkinForm = document.getElementById('checkin-form');
        if (checkinForm) {
            checkinForm.addEventListener('submit', this.handleCheckInSubmit.bind(this));
        }

        // Search functionality
        const searchInputs = ['search_reservation', 'search_guest', 'search_date', 'search_status'];
        searchInputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input) {
                input.addEventListener('input', this.handleSearch.bind(this));
            }
        });
    }

    // Handle check-in form submission
    async handleCheckInSubmit(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const data = {
            reservation_id: formData.get('reservation_id'),
            room_key_issued: formData.get('room_key_issued'),
            welcome_amenities: formData.get('welcome_amenities'),
            special_instructions: formData.get('special_instructions')
        };

        try {
            const response = await fetch('../../api/process-checkin.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            
            if (result.success) {
                this.showNotification('Check-in completed successfully!', 'success');
                this.hideCheckInForm();
                this.loadPendingCheckins();
            } else {
                this.showNotification(result.message || 'Error processing check-in', 'error');
            }
        } catch (error) {
            console.error('Error processing check-in:', error);
            this.showNotification('Error processing check-in. Please try again.', 'error');
        }
    }

    // Handle search
    handleSearch() {
        const reservationNumber = document.getElementById('search_reservation').value;
        const guestName = document.getElementById('search_guest').value;
        const date = document.getElementById('search_date').value;
        const status = document.getElementById('search_status').value;

        this.searchReservations({
            reservation_number: reservationNumber,
            guest_name: guestName,
            date: date,
            status: status
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
                this.displayPendingCheckins(data.reservations);
            } else {
                console.error('Error searching reservations:', data.message);
            }
        } catch (error) {
            console.error('Error searching reservations:', error);
        }
    }

    // Start check-in process
    startCheckIn(reservationId) {
        this.loadReservationDetails(reservationId);
    }

    // Load reservation details for check-in
    async loadReservationDetails(reservationId) {
        try {
            const response = await fetch(`../../api/get-reservation-details.php?id=${reservationId}`);
            const data = await response.json();
            
            if (data.success) {
                this.populateCheckInForm(data.reservation);
                this.showCheckInForm();
            } else {
                this.showNotification('Error loading reservation details', 'error');
            }
        } catch (error) {
            console.error('Error loading reservation details:', error);
            this.showNotification('Error loading reservation details', 'error');
        }
    }

    // Populate check-in form
    populateCheckInForm(reservation) {
        document.getElementById('reservation_id').value = reservation.id;
        document.getElementById('guest_name').value = reservation.guest_name;
        document.getElementById('reservation_number').value = reservation.reservation_number;
        document.getElementById('room_number').value = reservation.room_number;
        document.getElementById('checkin_date').value = this.formatDate(reservation.check_in_date);
    }

    // Show check-in form
    showCheckInForm() {
        const formContainer = document.getElementById('checkin-form-container');
        if (formContainer) {
            formContainer.classList.remove('hidden');
            formContainer.scrollIntoView({ behavior: 'smooth' });
        }
    }

    // Hide check-in form
    hideCheckInForm() {
        const formContainer = document.getElementById('checkin-form-container');
        if (formContainer) {
            formContainer.classList.add('hidden');
        }
    }

    // Cancel check-in
    cancelCheckin() {
        this.hideCheckInForm();
        document.getElementById('checkin-form').reset();
    }

    // Helper methods
    formatDateTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toISOString().split('T')[0];
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
function searchReservations() {
    if (window.checkInManager) {
        window.checkInManager.handleSearch();
    }
}

function clearFilters() {
    document.getElementById('search_reservation').value = '';
    document.getElementById('search_guest').value = '';
    document.getElementById('search_date').value = '';
    document.getElementById('search_status').value = '';
    
    if (window.checkInManager) {
        window.checkInManager.loadPendingCheckins();
    }
}

function refreshData() {
    if (window.checkInManager) {
        window.checkInManager.loadPendingCheckins();
    }
}

function startCheckIn(reservationId) {
    if (window.checkInManager) {
        window.checkInManager.startCheckIn(reservationId);
    }
}

function viewReservationDetails(reservationId) {
    window.location.href = `view-reservation.php?id=${reservationId}`;
}

function cancelCheckin() {
    if (window.checkInManager) {
        window.checkInManager.cancelCheckin();
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.checkInManager = new CheckInManager();
});