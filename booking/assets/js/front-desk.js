/**
 * Front Desk Dashboard JavaScript
 * Hotel PMS - Front Desk Module
 */

// Front Desk Dashboard functionality
class FrontDeskDashboard {
    constructor() {
        this.init();
    }

    init() {
        this.loadRecentReservations();
        this.loadTodaySchedule();
        this.updateDateTime();
        this.setupEventListeners();
    }

    // Load recent reservations
    async loadRecentReservations() {
        try {
            const response = await fetch('../../api/get-recent-reservations.php');
            const data = await response.json();
            
            if (data.success) {
                this.displayRecentReservations(data.reservations);
            } else {
                console.error('Error loading recent reservations:', data.message);
            }
        } catch (error) {
            console.error('Error loading recent reservations:', error);
        }
    }

    // Display recent reservations
    displayRecentReservations(reservations) {
        const container = document.getElementById('recent-reservations');
        if (!container) return;

        if (reservations.length === 0) {
            container.innerHTML = `
                <div class="px-6 py-12 text-center text-gray-500">
                    <i class="fas fa-calendar text-4xl mb-4"></i>
                    <p>No recent reservations found</p>
                </div>
            `;
            return;
        }

        const table = `
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in</th>
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
                                Room ${reservation.room_number}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${this.formatDate(reservation.check_in_date)}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${this.getStatusClass(reservation.status)}">
                                    ${this.getStatusLabel(reservation.status)}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="viewReservation(${reservation.id})" class="text-blue-600 hover:text-blue-900 mr-3">
                                    View
                                </button>
                                ${reservation.status === 'confirmed' ? `
                                    <button onclick="checkInGuest(${reservation.id})" class="text-green-600 hover:text-green-900">
                                        Check In
                                    </button>
                                ` : ''}
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;

        container.innerHTML = table;
    }

    // Load today's schedule
    async loadTodaySchedule() {
        try {
            const response = await fetch('../../api/get-today-schedule.php');
            const data = await response.json();
            
            if (data.success) {
                this.displayTodaySchedule(data.schedule);
            } else {
                console.error('Error loading today\'s schedule:', data.message);
            }
        } catch (error) {
            console.error('Error loading today\'s schedule:', error);
        }
    }

    // Display today's schedule
    displayTodaySchedule(schedule) {
        const container = document.getElementById('today-schedule');
        if (!container) return;

        if (schedule.length === 0) {
            container.innerHTML = `
                <div class="text-center text-gray-500 py-8">
                    <i class="fas fa-calendar-day text-4xl mb-4"></i>
                    <p>No scheduled activities for today</p>
                </div>
            `;
            return;
        }

        const scheduleHtml = schedule.map(item => `
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas ${this.getScheduleIcon(item.type)} text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-900">${item.title}</p>
                        <p class="text-sm text-gray-500">${item.description}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-900">${item.time}</p>
                    <p class="text-xs text-gray-500">${item.status}</p>
                </div>
            </div>
        `).join('');

        container.innerHTML = scheduleHtml;
    }

    // Update date and time
    updateDateTime() {
        const updateTime = () => {
            const now = new Date();
            const dateElement = document.getElementById('current-date');
            const timeElement = document.getElementById('current-time');
            
            if (dateElement) {
                dateElement.textContent = now.toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            }
            
            if (timeElement) {
                timeElement.textContent = now.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
            }
        };

        updateTime();
        setInterval(updateTime, 1000);
    }

    // Setup event listeners
    setupEventListeners() {
        // Add any additional event listeners here
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

    getStatusClass(status) {
        const statusClasses = {
            'confirmed': 'bg-yellow-100 text-yellow-800',
            'checked_in': 'bg-green-100 text-green-800',
            'checked_out': 'bg-gray-100 text-gray-800',
            'cancelled': 'bg-red-100 text-red-800'
        };
        return statusClasses[status] || 'bg-gray-100 text-gray-800';
    }

    getStatusLabel(status) {
        const statusLabels = {
            'confirmed': 'Confirmed',
            'checked_in': 'Checked In',
            'checked_out': 'Checked Out',
            'cancelled': 'Cancelled'
        };
        return statusLabels[status] || status;
    }

    getScheduleIcon(type) {
        const icons = {
            'checkin': 'fa-sign-in-alt',
            'checkout': 'fa-sign-out-alt',
            'maintenance': 'fa-tools',
            'cleaning': 'fa-broom',
            'meeting': 'fa-users'
        };
        return icons[type] || 'fa-calendar';
    }
}

// Global functions for button actions
function viewReservation(reservationId) {
    window.location.href = `view-reservation.php?id=${reservationId}`;
}

function checkInGuest(reservationId) {
    window.location.href = `check-in.php?reservation_id=${reservationId}`;
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new FrontDeskDashboard();
});