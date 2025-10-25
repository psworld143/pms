/**
 * Housekeeping Task Management JavaScript
 */

// Fallback notification function in case Utils is not available
function showNotificationFallback(message, type = 'info') {
    console.log(`[${type.toUpperCase()}] ${message}`);
    alert(message);
}

// Safe notification function
function showNotification(message, type = 'info') {
    if (typeof HotelPMS !== 'undefined' && HotelPMS.Utils && HotelPMS.Utils.showNotification) {
        HotelPMS.Utils.showNotification(message, type);
    } else if (typeof Utils !== 'undefined' && Utils.showNotification) {
        Utils.showNotification(message, type);
    } else {
        showNotificationFallback(message, type);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Wait for HotelPMS.Utils to be available
    if (typeof HotelPMS === 'undefined' || !HotelPMS.Utils) {
        console.error('HotelPMS.Utils is not defined. Make sure main.js is loaded before housekeeping-tasks.js');
        // Still initialize with fallback notifications
    }
    initializeTasks();
});

function initializeTasks() {
    setupEventListeners();
    loadTasks();
}

function loadTasks() {
    // This function will be implemented to load tasks dynamically
    // For now, we'll just reload the page content
    fetch('../../api/get-recent-housekeeping-tasks.php', {
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            updateTaskList(result.tasks || []);
            updateTaskStats(result);
        }
    })
    .catch(error => {
        console.error('Error loading tasks:', error);
    });
}

function updateTaskList(tasks) {
    const tasksList = document.getElementById('tasksList');
    if (!tasksList) return;
    
    if (tasks.length === 0) {
        tasksList.innerHTML = '<div class="text-center py-8 text-gray-500">No tasks found.</div>';
        return;
    }
    
    tasksList.innerHTML = tasks.map(task => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                ${task.task_type ? task.task_type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A'}
                ${task.notes ? '<br><span class="text-xs text-gray-500">' + task.notes + '</span>' : ''}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${task.room_number || 'N/A'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${task.assigned_to_name || 'Unassigned'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${task.scheduled_time ? new Date(task.scheduled_time).toLocaleString() : 'N/A'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs font-medium rounded-full ${getStatusClass(task.status)}">
                    ${task.status ? task.status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'Pending'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button onclick="viewTask(${task.id})" class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                ${task.status === 'pending' ? `<button onclick="updateTaskStatus(${task.id}, 'in_progress')" class="text-yellow-600 hover:text-yellow-900 mr-3">Start</button>` : ''}
                ${task.status === 'in_progress' ? `<button onclick="updateTaskStatus(${task.id}, 'completed')" class="text-green-600 hover:text-green-900 mr-3">Complete</button>` : ''}
            </td>
        </tr>
    `).join('');
}

function getStatusClass(status) {
    switch(status) {
        case 'completed': return 'bg-green-100 text-green-800';
        case 'in_progress': return 'bg-yellow-100 text-yellow-800';
        case 'pending': return 'bg-gray-100 text-gray-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function updateTaskStats(data) {
    // Update the statistics cards if they exist
    const totalTasks = document.querySelector('[data-stat="total"]');
    const completedTasks = document.querySelector('[data-stat="completed"]');
    const inProgressTasks = document.querySelector('[data-stat="in_progress"]');
    const overdueTasks = document.querySelector('[data-stat="overdue"]');
    
    if (totalTasks) totalTasks.textContent = data.count || 0;
    if (completedTasks) completedTasks.textContent = data.tasks ? data.tasks.filter(t => t.status === 'completed').length : 0;
    if (inProgressTasks) inProgressTasks.textContent = data.tasks ? data.tasks.filter(t => t.status === 'in_progress').length : 0;
    if (overdueTasks) overdueTasks.textContent = data.tasks ? data.tasks.filter(t => t.status === 'overdue').length : 0;
}

function setupEventListeners() {
    // New task button
    const newTaskBtn = document.querySelector('button[onclick="showNewTaskModal()"]');
    if (newTaskBtn) {
        newTaskBtn.addEventListener('click', function() {
            showNewTaskModal();
        });
    }
    
    // Form submission
    const form = document.getElementById('new-task-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitTaskForm();
        });
    }
}

function showNewTaskModal() {
    const modal = document.getElementById('new-task-modal');
    if (modal) {
        modal.classList.remove('hidden');
        // Set default date to today
        const today = new Date().toISOString().split('T')[0];
        const dateInput = document.getElementById('scheduled_date');
        if (dateInput) {
            dateInput.value = today;
        }
    }
}

function closeNewTaskModal() {
    const modal = document.getElementById('new-task-modal');
    if (modal) {
        modal.classList.add('hidden');
        // Reset form and re-enable submit button
        const form = document.getElementById('new-task-form');
        if (form) {
            form.reset();
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = 'Create Task';
            }
        }
    }
}

function submitTaskForm() {
    const form = document.getElementById('new-task-form');
    const submitButton = form.querySelector('button[type="submit"]');
    
    // Prevent double submission
    if (submitButton.disabled) {
        return;
    }
    
    // Disable submit button
    submitButton.disabled = true;
    submitButton.textContent = 'Creating...';
    
    const formData = new FormData(form);
    
    const data = {
        room_id: formData.get('room_id'),
        task_type: formData.get('task_type'),
        assigned_to: formData.get('assigned_to'),
        scheduled_date: formData.get('scheduled_date'),
        scheduled_time: formData.get('scheduled_time'),
        notes: formData.get('notes')
    };
    
    // Validate required fields
    if (!data.room_id || !data.task_type || !data.scheduled_date || !data.scheduled_time) {
        showNotification('Please fill in all required fields', 'error');
        // Re-enable button
        submitButton.disabled = false;
        submitButton.textContent = 'Create Task';
        return;
    }
    
    // Combine date and time
    const scheduledDateTime = data.scheduled_date + ' ' + data.scheduled_time + ':00';
    
    // Set the hidden field for form submission
    document.getElementById('scheduled_time_combined').value = scheduledDateTime;
    
    const taskData = {
        room_id: parseInt(data.room_id),
        task_type: data.task_type,
        assigned_to: data.assigned_to ? parseInt(data.assigned_to) : null,
        scheduled_time: scheduledDateTime,
        notes: data.notes || ''
    };
    
    console.log('Submitting task data:', taskData);
    
    // Try fetch first, fallback to form submission
    const timeoutId = setTimeout(() => {
        console.log('Fetch timeout, trying form submission...');
        // Fallback to form submission
        form.submit();
    }, 5000); // 5 second timeout

    fetch('../../api/create-housekeeping-task.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: JSON.stringify(taskData)
    })
    .then(response => {
        clearTimeout(timeoutId);
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(result => {
        console.log('Task creation result:', result);
        if (result.success) {
            showNotification('✅ Successfully created a new task!', 'success');
            closeNewTaskModal();
            // Refresh the task list without page reload
            loadTasks();
        } else {
            showNotification('❌ Error creating task: ' + result.message, 'error');
            // Re-enable button
            submitButton.disabled = false;
            submitButton.textContent = 'Create Task';
        }
    })
    .catch(error => {
        clearTimeout(timeoutId);
        console.error('Fetch error, trying form submission:', error);
        // Fallback to form submission
        form.submit();
    });
}

function viewTask(taskId) {
    fetch(`../../api/get-task-details.php?id=${taskId}`, {
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showTaskDetailsModal(result.task);
        } else {
            showNotification('Error loading task details', 'error');
        }
    })
    .catch(error => {
        console.error('Error loading task details:', error);
        showNotification('Error loading task details', 'error');
    });
}

function showTaskDetailsModal(task) {
    const modalHtml = `
        <div id="task-details-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Task Details</h3>
                        <button onclick="closeTaskDetailsModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Task Type</label>
                            <p class="text-sm text-gray-900">${task.task_type ? task.task_type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Room</label>
                            <p class="text-sm text-gray-900">${task.room_number || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Assigned To</label>
                            <p class="text-sm text-gray-900">${task.staff_name || 'Unassigned'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Priority</label>
                            <p class="text-sm text-gray-900">${task.priority || 'Normal'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Scheduled</label>
                            <p class="text-sm text-gray-900">${task.scheduled_time ? new Date(task.scheduled_time).toLocaleString() : 'N/A'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Status</label>
                            <p class="text-sm text-gray-900">${task.status || 'Pending'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Notes</label>
                            <p class="text-sm text-gray-900">${task.notes || 'No notes'}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function closeTaskDetailsModal() {
    const modal = document.getElementById('task-details-modal');
    if (modal) {
        modal.remove();
    }
}

function updateTaskStatus(taskId, newStatus) {
    if (!confirm(`Are you sure you want to change this task status to ${newStatus}?`)) {
        return;
    }
    
    fetch('../../api/update-task-status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            task_id: taskId,
            status: newStatus
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showNotification(`Task status updated to ${newStatus}`, 'success');
            // Refresh the task list without page reload
            loadTasks();
        } else {
            showNotification('Error updating task status: ' + result.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error updating task status:', error);
        showNotification('Error updating task status', 'error');
    });
}

// Export functions for global access
window.showNewTaskModal = showNewTaskModal;
window.closeNewTaskModal = closeNewTaskModal;
window.submitTaskForm = submitTaskForm;
window.viewTask = viewTask;
window.closeTaskDetailsModal = closeTaskDetailsModal;
window.updateTaskStatus = updateTaskStatus;
window.showNotification = showNotification; // Export showNotification globally

// Test function availability
console.log('housekeeping-tasks.js loaded successfully');