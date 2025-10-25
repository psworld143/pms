// Housekeeping JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Update date and time
    updateDateTime();
    setInterval(updateDateTime, 1000);
    
    // Load room status overview
    loadRoomStatusOverview();
    
    // Load recent tasks
    loadRecentTasks();
});

// Update date and time
function updateDateTime() {
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
}

// Load room status overview
function loadRoomStatusOverview() {
    const container = document.getElementById('room-status-overview');
    if (!container) return;
    
    // Show loading
    container.innerHTML = '<div class="flex items-center justify-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div></div>';
    
    // Fetch room status overview
    fetch('../../api/get-room-status-overview.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayRoomStatusOverview(data.statuses);
            } else {
                container.innerHTML = '<div class="text-center py-8 text-gray-500">No room status data available</div>';
            }
        })
        .catch(error => {
            console.error('Error loading room status overview:', error);
            container.innerHTML = '<div class="text-center py-8 text-red-500">Error loading room status</div>';
        });
}

// Display room status overview
function displayRoomStatusOverview(statuses) {
    const container = document.getElementById('room-status-overview');
    
    if (statuses.length === 0) {
        container.innerHTML = '<div class="text-center py-8 text-gray-500">No room status data available</div>';
        return;
    }
    
    const statusCards = statuses.map(status => {
        const statusConfig = getStatusConfig(status.housekeeping_status);
        return `
            <div class="bg-white rounded-lg p-6 shadow-md border-l-4 ${statusConfig.borderColor}">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 ${statusConfig.bgColor} rounded-full flex items-center justify-center mr-3">
                            <i class="${statusConfig.icon} text-white"></i>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-800">${statusConfig.label}</h4>
                            <p class="text-sm text-gray-600">${status.count} rooms</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold ${statusConfig.textColor}">${status.count}</div>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="${statusConfig.progressColor} h-2 rounded-full" style="width: ${calculatePercentage(status.count, statuses)}%"></div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    container.innerHTML = statusCards;
}

// Get status configuration
function getStatusConfig(status) {
    const configs = {
        'clean': {
            label: 'Clean Rooms',
            icon: 'fas fa-check-circle',
            bgColor: 'bg-green-500',
            borderColor: 'border-green-500',
            textColor: 'text-green-600',
            progressColor: 'bg-green-500'
        },
        'dirty': {
            label: 'Dirty Rooms',
            icon: 'fas fa-times-circle',
            bgColor: 'bg-red-500',
            borderColor: 'border-red-500',
            textColor: 'text-red-600',
            progressColor: 'bg-red-500'
        },
        'maintenance': {
            label: 'Maintenance',
            icon: 'fas fa-tools',
            bgColor: 'bg-yellow-500',
            borderColor: 'border-yellow-500',
            textColor: 'text-yellow-600',
            progressColor: 'bg-yellow-500'
        }
    };
    
    return configs[status] || configs['clean'];
}

// Calculate percentage
function calculatePercentage(count, allStatuses) {
    const total = allStatuses.reduce((sum, status) => sum + status.count, 0);
    return total > 0 ? Math.round((count / total) * 100) : 0;
}

// Load recent tasks
function loadRecentTasks() {
    const container = document.getElementById('recent-tasks');
    if (!container) return;
    
    // Show loading
    container.innerHTML = '<div class="flex items-center justify-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div></div>';
    
    // Fetch recent tasks
    fetch('../../api/get-recent-housekeeping-tasks.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayRecentTasks(data.tasks);
            } else {
                container.innerHTML = '<div class="text-center py-8 text-gray-500">No recent tasks</div>';
            }
        })
        .catch(error => {
            console.error('Error loading recent tasks:', error);
            container.innerHTML = '<div class="text-center py-8 text-red-500">Error loading tasks</div>';
        });
}

// Display recent tasks
function displayRecentTasks(tasks) {
    const container = document.getElementById('recent-tasks');
    
    if (tasks.length === 0) {
        container.innerHTML = '<div class="text-center py-8 text-gray-500">No recent tasks</div>';
        return;
    }
    
    const tableHtml = `
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                ${tasks.map(task => `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">Room ${task.room_number}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">${formatTaskType(task.task_type)}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full ${getTaskStatusClass(task.status)}">
                                ${task.status.charAt(0).toUpperCase() + task.status.slice(1)}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${task.assigned_to_name || 'Unassigned'}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${formatDateTime(task.created_at)}
                        </td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
    
    container.innerHTML = tableHtml;
}

// Format task type
function formatTaskType(taskType) {
    const types = {
        'cleaning_completed': 'Cleaning Completed',
        'cleaning_required': 'Cleaning Required',
        'maintenance_request': 'Maintenance Request',
        'inspection': 'Room Inspection',
        'deep_cleaning': 'Deep Cleaning'
    };
    
    return types[taskType] || taskType.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
}

// Get task status class
function getTaskStatusClass(status) {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'in_progress': 'bg-blue-100 text-blue-800',
        'completed': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800'
    };
    
    return classes[status] || 'bg-gray-100 text-gray-800';
}

// Format date and time
function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Update room status
function updateRoomStatus(roomId, status, notes = '') {
    const data = {
        room_id: roomId,
        status: status,
        notes: notes
    };
    
    fetch('../../api/update-room-status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            HotelPMS.Utils.showNotification('Room status updated successfully!', 'success');
            // Reload data
            loadRoomStatusOverview();
            loadRecentTasks();
        } else {
            HotelPMS.Utils.showNotification(result.message || 'Error updating room status', 'error');
        }
    })
    .catch(error => {
        console.error('Error updating room status:', error);
        HotelPMS.Utils.showNotification('Error updating room status', 'error');
    });
}

// Create maintenance request
function createMaintenanceRequest(roomId, issueType, description, priority = 'medium') {
    const data = {
        room_id: roomId,
        issue_type: issueType,
        description: description,
        priority: priority
    };
    
    fetch('../../api/create-maintenance-request.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            HotelPMS.Utils.showNotification('Maintenance request created successfully!', 'success');
            // Reload data
            loadRoomStatusOverview();
            loadRecentTasks();
        } else {
            HotelPMS.Utils.showNotification(result.message || 'Error creating maintenance request', 'error');
        }
    })
    .catch(error => {
        console.error('Error creating maintenance request:', error);
        HotelPMS.Utils.showNotification('Error creating maintenance request', 'error');
    });
}

// Cleaning Schedule Functions
function scheduleCleaning() {
    // Open schedule cleaning modal
    showScheduleCleaningModal();
}

function showScheduleCleaningModal() {
    const modalHtml = `
        <div id="schedule-cleaning-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Schedule Cleaning</h3>
                        <button onclick="closeScheduleCleaningModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form id="schedule-cleaning-form" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Room Number *</label>
                            <select name="room_id" id="schedule_room_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Room</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cleaning Type *</label>
                            <select name="cleaning_type" id="cleaning_type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Type</option>
                                <option value="daily_cleaning">Daily Cleaning</option>
                                <option value="deep_cleaning">Deep Cleaning</option>
                                <option value="turn_down">Turn Down Service</option>
                                <option value="checkout_cleaning">Checkout Cleaning</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Scheduled Date *</label>
                            <input type="date" name="scheduled_date" id="scheduled_date" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Scheduled Time *</label>
                            <input type="time" name="scheduled_time" id="scheduled_time" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Assigned Staff</label>
                            <select name="assigned_to" id="assigned_to" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Auto-assign</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea name="notes" id="notes" rows="3" placeholder="Additional notes..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        <div class="flex justify-end space-x-4">
                            <button type="button" onclick="closeScheduleCleaningModal()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                                Schedule Cleaning
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Load rooms and staff
    loadRoomsForSchedule();
    loadStaffForSchedule();
    
    // Set default date to today
    document.getElementById('scheduled_date').value = new Date().toISOString().split('T')[0];
    
    // Setup form submission
    document.getElementById('schedule-cleaning-form').addEventListener('submit', function(e) {
        e.preventDefault();
        submitScheduleCleaning();
    });
}

function closeScheduleCleaningModal() {
    const modal = document.getElementById('schedule-cleaning-modal');
    if (modal) {
        modal.remove();
    }
}

function loadRoomsForSchedule() {
    fetch('../../api/get-rooms.php', {
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            const select = document.getElementById('schedule_room_id');
            result.rooms.forEach(room => {
                const option = document.createElement('option');
                option.value = room.id;
                option.textContent = `Room ${room.room_number}`;
                select.appendChild(option);
            });
        }
    })
    .catch(error => {
        console.error('Error loading rooms:', error);
    });
}

function loadStaffForSchedule() {
    fetch('../../api/get-housekeeping-staff.php', {
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            const select = document.getElementById('assigned_to');
            result.staff.forEach(staff => {
                const option = document.createElement('option');
                option.value = staff.id;
                option.textContent = `${staff.first_name} ${staff.last_name}`;
                select.appendChild(option);
            });
        }
    })
    .catch(error => {
        console.error('Error loading staff:', error);
    });
}

function submitScheduleCleaning() {
    const form = document.getElementById('schedule-cleaning-form');
    const formData = new FormData(form);
    
    const data = {
        room_id: formData.get('room_id'),
        cleaning_type: formData.get('cleaning_type'),
        scheduled_date: formData.get('scheduled_date'),
        scheduled_time: formData.get('scheduled_time'),
        assigned_to: formData.get('assigned_to') || null,
        notes: formData.get('notes')
    };
    
    // Validate required fields
    if (!data.room_id || !data.cleaning_type || !data.scheduled_date || !data.scheduled_time) {
        showNotification('Please fill in all required fields', 'error');
        return;
    }
    
    fetch('../../api/create-cleaning-schedule.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        credentials: 'same-origin',
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showNotification('Cleaning scheduled successfully!', 'success');
            closeScheduleCleaningModal();
            // Reload page to show new schedule
            window.location.reload();
        } else {
            showNotification(result.message || 'Error scheduling cleaning', 'error');
        }
    })
    .catch(error => {
        console.error('Error scheduling cleaning:', error);
        showNotification('Error scheduling cleaning', 'error');
    });
}

function viewSchedule(scheduleId) {
    fetch(`../../api/get-cleaning-schedule.php?id=${scheduleId}`, {
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showScheduleDetailsModal(result.schedule);
        } else {
            showNotification(result.message || 'Error loading schedule details', 'error');
        }
    })
    .catch(error => {
        console.error('Error loading schedule:', error);
        showNotification('Error loading schedule details', 'error');
    });
}

function showScheduleDetailsModal(schedule) {
    const modalHtml = `
        <div id="schedule-details-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Schedule Details</h3>
                        <button onclick="closeScheduleDetailsModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Room:</label>
                            <p class="text-sm text-gray-900">Room ${schedule.room_number || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Cleaning Type:</label>
                            <p class="text-sm text-gray-900">${schedule.cleaning_type ? schedule.cleaning_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Scheduled Date:</label>
                            <p class="text-sm text-gray-900">${schedule.scheduled_date || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Scheduled Time:</label>
                            <p class="text-sm text-gray-900">${schedule.scheduled_time || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Status:</label>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full ${getScheduleStatusClass(schedule.status)}">
                                ${schedule.status ? schedule.status.charAt(0).toUpperCase() + schedule.status.slice(1) : 'N/A'}
                            </span>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Assigned To:</label>
                            <p class="text-sm text-gray-900">${schedule.assigned_to_name || 'Unassigned'}</p>
                        </div>
                        ${schedule.notes ? `
                        <div>
                            <label class="text-sm font-medium text-gray-500">Notes:</label>
                            <p class="text-sm text-gray-900">${schedule.notes}</p>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function closeScheduleDetailsModal() {
    const modal = document.getElementById('schedule-details-modal');
    if (modal) {
        modal.remove();
    }
}

function editSchedule(scheduleId) {
    // For now, just show a message - could be expanded to show edit modal
    showNotification('Edit functionality coming soon!', 'info');
}

function getScheduleStatusClass(status) {
    const classes = {
        'scheduled': 'bg-blue-100 text-blue-800',
        'in_progress': 'bg-yellow-100 text-yellow-800',
        'completed': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800'
    };
    
    return classes[status] || 'bg-gray-100 text-gray-800';
}

// Filter schedules
function filterSchedules() {
    const date = document.querySelector('input[type="date"]').value;
    const floor = document.querySelector('select').value;
    const status = document.querySelectorAll('select')[1].value;
    
    // Reload page with filter parameters
    const params = new URLSearchParams();
    if (date) params.append('date', date);
    if (floor) params.append('floor', floor);
    if (status) params.append('status', status);
    
    window.location.href = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
}

// Show notification
function showNotification(message, type = 'info') {
    // Use the main.js notification system if available
    if (window.HotelPMS && window.HotelPMS.Utils && window.HotelPMS.Utils.showNotification) {
        window.HotelPMS.Utils.showNotification(message, type);
    } else {
        // Fallback notification
        alert(message);
    }
}

// Quality Check Functions
function newQualityCheck() {
    showQualityCheckModal();
}

function showQualityCheckModal() {
    const modalHtml = `
        <div id="quality-check-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">New Quality Check</h3>
                        <button onclick="closeQualityCheckModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form id="quality-check-form" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Room Number *</label>
                            <select name="room_id" id="quality_room_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Room</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Check Type *</label>
                            <select name="check_type" id="check_type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Type</option>
                                <option value="routine">Routine Check</option>
                                <option value="deep">Deep Inspection</option>
                                <option value="post_cleaning">Post-Cleaning</option>
                                <option value="guest_complaint">Guest Complaint</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Inspector *</label>
                            <select name="inspector_id" id="inspector_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Inspector</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Overall Score *</label>
                            <select name="overall_score" id="overall_score" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Score</option>
                                <option value="5">Excellent (5)</option>
                                <option value="4">Good (4)</option>
                                <option value="3">Satisfactory (3)</option>
                                <option value="2">Needs Improvement (2)</option>
                                <option value="1">Poor (1)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Issues Found</label>
                            <textarea name="issues_found" id="issues_found" rows="3" placeholder="Describe any issues found..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Recommendations</label>
                            <textarea name="recommendations" id="recommendations" rows="3" placeholder="Any recommendations..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        <div class="flex justify-end space-x-4">
                            <button type="button" onclick="closeQualityCheckModal()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                                Submit Quality Check
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Load rooms and inspectors
    loadRoomsForQualityCheck();
    loadInspectorsForQualityCheck();
    
    // Setup form submission
    document.getElementById('quality-check-form').addEventListener('submit', function(e) {
        e.preventDefault();
        submitQualityCheck();
    });
}

function closeQualityCheckModal() {
    const modal = document.getElementById('quality-check-modal');
    if (modal) {
        modal.remove();
    }
}

function loadRoomsForQualityCheck() {
    fetch('../../api/get-rooms.php', {
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            const select = document.getElementById('quality_room_id');
            result.rooms.forEach(room => {
                const option = document.createElement('option');
                option.value = room.id;
                option.textContent = `Room ${room.room_number}`;
                select.appendChild(option);
            });
        }
    })
    .catch(error => {
        console.error('Error loading rooms:', error);
    });
}

function loadInspectorsForQualityCheck() {
    fetch('../../api/get-housekeeping-staff.php', {
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            const select = document.getElementById('inspector_id');
            result.staff.forEach(staff => {
                const option = document.createElement('option');
                option.value = staff.id;
                option.textContent = `${staff.first_name} ${staff.last_name}`;
                select.appendChild(option);
            });
        }
    })
    .catch(error => {
        console.error('Error loading inspectors:', error);
    });
}

function submitQualityCheck() {
    const form = document.getElementById('quality-check-form');
    const formData = new FormData(form);
    
    const data = {
        room_id: formData.get('room_id'),
        check_type: formData.get('check_type'),
        inspector_id: formData.get('inspector_id'),
        overall_score: formData.get('overall_score'),
        issues_found: formData.get('issues_found'),
        recommendations: formData.get('recommendations')
    };
    
    // Validate required fields
    if (!data.room_id || !data.check_type || !data.inspector_id || !data.overall_score) {
        showNotification('Please fill in all required fields', 'error');
        return;
    }
    
    fetch('../../api/create-quality-check.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        credentials: 'same-origin',
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showNotification('Quality check submitted successfully!', 'success');
            closeQualityCheckModal();
            // Reload page to show new check
            window.location.reload();
        } else {
            showNotification(result.message || 'Error submitting quality check', 'error');
        }
    })
    .catch(error => {
        console.error('Error submitting quality check:', error);
        showNotification('Error submitting quality check', 'error');
    });
}

function viewQualityCheckDetails(checkId) {
    fetch(`../../api/get-quality-check.php?id=${checkId}`, {
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showQualityCheckDetailsModal(result.check);
        } else {
            showNotification(result.message || 'Error loading quality check details', 'error');
        }
    })
    .catch(error => {
        console.error('Error loading quality check:', error);
        showNotification('Error loading quality check details', 'error');
    });
}

function showQualityCheckDetailsModal(check) {
    const modalHtml = `
        <div id="quality-check-details-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Quality Check Details</h3>
                        <button onclick="closeQualityCheckDetailsModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Room:</label>
                            <p class="text-sm text-gray-900">Room ${check.room_number || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Check Type:</label>
                            <p class="text-sm text-gray-900">${check.check_type ? check.check_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Inspector:</label>
                            <p class="text-sm text-gray-900">${check.inspector_name || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Overall Score:</label>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full ${getQualityScoreClass(check.overall_score)}">
                                ${check.overall_score ? check.overall_score + '/5' : 'N/A'}
                            </span>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Date:</label>
                            <p class="text-sm text-gray-900">${check.created_at ? new Date(check.created_at).toLocaleDateString() : 'N/A'}</p>
                        </div>
                        ${check.issues_found ? `
                        <div>
                            <label class="text-sm font-medium text-gray-500">Issues Found:</label>
                            <p class="text-sm text-gray-900">${check.issues_found}</p>
                        </div>
                        ` : ''}
                        ${check.recommendations ? `
                        <div>
                            <label class="text-sm font-medium text-gray-500">Recommendations:</label>
                            <p class="text-sm text-gray-900">${check.recommendations}</p>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function closeQualityCheckDetailsModal() {
    const modal = document.getElementById('quality-check-details-modal');
    if (modal) {
        modal.remove();
    }
}

function editQualityCheck(checkId) {
    showNotification('Edit functionality coming soon!', 'info');
}

function getQualityScoreClass(score) {
    if (score >= 4) return 'bg-green-100 text-green-800';
    if (score >= 3) return 'bg-yellow-100 text-yellow-800';
    return 'bg-red-100 text-red-800';
}

// Export functions for use in other modules
window.Housekeeping = {
    updateRoomStatus,
    createMaintenanceRequest,
    loadRoomStatusOverview,
    loadRecentTasks,
    scheduleCleaning,
    viewSchedule,
    editSchedule,
    filterSchedules,
    newQualityCheck,
    viewQualityCheckDetails,
    editQualityCheck
};
