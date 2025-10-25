/**
 * Maintenance Management JavaScript
 * Version: 2.0 - Fixed showNotification errors
 */

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize immediately - showNotification function is defined above
    initializeMaintenance();
});

function initializeMaintenance() {
    setupEventListeners();
}

// Fallback notification function
function showNotification(message, type = 'info') {
    // Try Utils.showNotification first
    if (typeof Utils !== 'undefined' && Utils.showNotification) {
        Utils.showNotification(message, type);
        return;
    }
    
    // Try window.Utils.showNotification
    if (typeof window.Utils !== 'undefined' && window.Utils.showNotification) {
        window.Utils.showNotification(message, type);
        return;
    }
    
    // Fallback to console and alert
    console.log(`[${type.toUpperCase()}] ${message}`);
    
    // Create a simple notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-md shadow-lg z-50 ${
        type === 'error' ? 'bg-red-500 text-white' :
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'warning' ? 'bg-yellow-500 text-black' :
        'bg-blue-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Remove notification after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}

function setupEventListeners() {
    // New maintenance button
    const newMaintenanceBtn = document.querySelector('button[onclick="showNewMaintenanceModal()"]');
    if (newMaintenanceBtn) {
        newMaintenanceBtn.addEventListener('click', function() {
            showNewMaintenanceModal();
        });
    }
    
    // Form submission
    const form = document.getElementById('new-maintenance-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitMaintenanceForm();
        });
    }
}

function showNewMaintenanceModal() {
    const modal = document.getElementById('new-maintenance-modal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function closeNewMaintenanceModal() {
    const modal = document.getElementById('new-maintenance-modal');
    if (modal) {
        modal.classList.add('hidden');
        // Reset form
        const form = document.getElementById('new-maintenance-form');
        if (form) {
            form.reset();
        }
    }
}

function submitMaintenanceForm() {
    const form = document.getElementById('new-maintenance-form');
    const formData = new FormData(form);
    
    const data = {
        room_id: formData.get('room_id'),
        issue_type: formData.get('issue_type'),
        priority: formData.get('priority'),
        description: formData.get('description'),
        estimated_cost: formData.get('estimated_cost') || 0
    };
    
    // Validate required fields
    if (!data.room_id || !data.issue_type || !data.description) {
        showNotification('Please fill in all required fields', 'error');
        return;
    }
    
    fetch('../../api/create-maintenance-request.php', {
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
            showNotification('Maintenance request created successfully!', 'success');
            closeNewMaintenanceModal();
            // Refresh the page to show new request
            window.location.reload();
        } else {
            showNotification('Error creating maintenance request: ' + result.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error creating maintenance request:', error);
        showNotification('Error creating maintenance request', 'error');
    });
}

function viewMaintenanceRequest(requestId) {
    fetch(`../../api/get-maintenance-request.php?id=${requestId}`, {
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showMaintenanceDetailsModal(result.request);
        } else {
            showNotification('Error loading maintenance request details', 'error');
        }
    })
    .catch(error => {
        console.error('Error loading maintenance request details:', error);
        showNotification('Error loading maintenance request details', 'error');
    });
}

function showMaintenanceDetailsModal(request) {
    const modalHtml = `
        <div id="maintenance-details-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Maintenance Request Details</h3>
                        <button onclick="closeMaintenanceDetailsModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Issue Type</label>
                            <p class="text-sm text-gray-900">${request.issue_type || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Room</label>
                            <p class="text-sm text-gray-900">${request.room_number || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Reported By</label>
                            <p class="text-sm text-gray-900">${request.reported_by_name || 'Unknown'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Priority</label>
                            <p class="text-sm text-gray-900">${request.priority || 'Normal'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Status</label>
                            <p class="text-sm text-gray-900">${request.status || 'Pending'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Estimated Cost</label>
                            <p class="text-sm text-gray-900">₱${request.estimated_cost ? parseFloat(request.estimated_cost).toFixed(2) : '0.00'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Description</label>
                            <p class="text-sm text-gray-900">${request.description || 'No description'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Created</label>
                            <p class="text-sm text-gray-900">${request.created_at ? new Date(request.created_at).toLocaleString() : 'N/A'}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function closeMaintenanceDetailsModal() {
    const modal = document.getElementById('maintenance-details-modal');
    if (modal) {
        modal.remove();
    }
}

function updateMaintenanceStatus(requestId, newStatus) {
    if (!confirm(`Are you sure you want to change this maintenance request status to ${newStatus}?`)) {
        return;
    }
    
    fetch('../../api/update-maintenance-status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            request_id: requestId,
            status: newStatus
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showNotification(`Maintenance request status updated to ${newStatus}`, 'success');
            // Refresh the page to show updated status
            window.location.reload();
        } else {
            showNotification('Error updating maintenance status: ' + result.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error updating maintenance status:', error);
        showNotification('Error updating maintenance status', 'error');
    });
}

// Export functions for global access
window.showNewMaintenanceModal = showNewMaintenanceModal;
window.closeNewMaintenanceModal = closeNewMaintenanceModal;
window.submitMaintenanceForm = submitMaintenanceForm;
window.viewMaintenanceRequest = viewMaintenanceRequest;
window.closeMaintenanceDetailsModal = closeMaintenanceDetailsModal;
window.updateMaintenanceStatus = updateMaintenanceStatus;
window.showNotification = showNotification; // Export notification function globally