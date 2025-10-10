// Room Status JavaScript for Housekeeping Module
document.addEventListener('DOMContentLoaded', function() {
    initializeRoomStatus();
    updateDateTime();
    setInterval(updateDateTime, 1000);
});

function initializeRoomStatus() {
    // Initialize filters
    document.getElementById('status-filter').addEventListener('change', filterRooms);
    document.getElementById('housekeeping-filter').addEventListener('change', filterRooms);
    document.getElementById('search-room').addEventListener('input', filterRooms);
    
    // Initialize form handlers
    document.getElementById('update-status-form').addEventListener('submit', handleUpdateStatus);
    document.getElementById('maintenance-form').addEventListener('submit', handleMaintenanceRequest);
}

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

function filterRooms() {
    const statusFilter = document.getElementById('status-filter').value;
    const housekeepingFilter = document.getElementById('housekeeping-filter').value;
    const searchTerm = document.getElementById('search-room').value.toLowerCase();
    
    const rows = document.querySelectorAll('#rooms-table-body tr');
    
    rows.forEach(row => {
        const roomNumber = row.querySelector('td:first-child').textContent.toLowerCase();
        const status = row.querySelector('td:nth-child(3) span').textContent.toLowerCase();
        const housekeepingStatus = row.querySelector('td:nth-child(4) span').textContent.toLowerCase();
        
        const matchesStatus = !statusFilter || status.includes(statusFilter);
        const matchesHousekeeping = !housekeepingFilter || housekeepingStatus.includes(housekeepingFilter);
        const matchesSearch = !searchTerm || roomNumber.includes(searchTerm);
        
        if (matchesStatus && matchesHousekeeping && matchesSearch) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function updateHousekeepingStatus(roomId) {
    // Get room details from the table row
    const row = document.querySelector(`tr[data-room-id="${roomId}"]`);
    const roomNumber = row.querySelector('td:first-child div').textContent;
    
    // Populate modal
    document.getElementById('room_id').value = roomId;
    document.getElementById('room_number_display').value = roomNumber;
    
    // Show modal
    document.getElementById('update-status-modal').classList.remove('hidden');
}

function closeUpdateStatusModal() {
    document.getElementById('update-status-modal').classList.add('hidden');
    document.getElementById('update-status-form').reset();
}

function handleUpdateStatus(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const roomId = formData.get('room_id');
    const housekeepingStatus = formData.get('housekeeping_status');
    const notes = formData.get('notes');
    
    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';
    submitBtn.disabled = true;
    
    fetch('../../api/update-room-housekeeping-status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            HotelPMS.Utils.showNotification('Room status updated successfully!', 'success');
            closeUpdateStatusModal();
            // Reload the page to reflect changes
            setTimeout(() => location.reload(), 1000);
        } else {
            HotelPMS.Utils.showNotification(data.message || 'Error updating room status', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        HotelPMS.Utils.showNotification('Error updating room status', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function createMaintenanceRequest(roomId) {
    // Get room details from the table row
    const row = document.querySelector(`tr[data-room-id="${roomId}"]`);
    const roomNumber = row.querySelector('td:first-child div').textContent;
    
    // Populate modal
    document.getElementById('maintenance_room_id').value = roomId;
    document.getElementById('maintenance_room_number').value = roomNumber;
    
    // Show modal
    document.getElementById('maintenance-modal').classList.remove('hidden');
}

function closeMaintenanceModal() {
    document.getElementById('maintenance-modal').classList.add('hidden');
    document.getElementById('maintenance-form').reset();
}

function handleMaintenanceRequest(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating...';
    submitBtn.disabled = true;
    
    fetch('../../api/create-maintenance-request.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            HotelPMS.Utils.showNotification('Maintenance request created successfully!', 'success');
            closeMaintenanceModal();
        } else {
            HotelPMS.Utils.showNotification(data.message || 'Error creating maintenance request', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        HotelPMS.Utils.showNotification('Error creating maintenance request', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function viewRoomDetails(roomId) {
    // Show loading state
    const modal = document.getElementById('room-details-modal');
    const content = document.getElementById('room-details-content');
    
    content.innerHTML = `
        <div class="flex justify-center items-center py-8">
            <i class="fas fa-spinner fa-spin text-2xl text-blue-600"></i>
            <span class="ml-2 text-gray-600">Loading room details...</span>
        </div>
    `;
    
    modal.classList.remove('hidden');
    
    // Fetch room details
    fetch(`../../api/get-room-details.php?id=${roomId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayRoomDetails(data.room);
            } else {
                content.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-exclamation-triangle text-3xl text-red-500 mb-4"></i>
                        <p class="text-gray-600">Error loading room details: ${data.message}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-3xl text-red-500 mb-4"></i>
                    <p class="text-gray-600">Error loading room details. Please try again.</p>
                </div>
            `;
        });
}

function displayRoomDetails(room) {
    const content = document.getElementById('room-details-content');
    
    const statusBadgeClass = getStatusBadgeClass(room.status);
    const housekeepingBadgeClass = getHousekeepingStatusBadgeClass(room.housekeeping_status);
    
    content.innerHTML = `
        <div class="space-y-6">
            <!-- Room Header -->
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h4 class="text-2xl font-bold text-blue-800">Room ${room.room_number}</h4>
                        <p class="text-blue-600 font-medium">${room.room_type_name}</p>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-blue-600">₱${parseFloat(room.rate).toFixed(2)}</div>
                        <div class="text-sm text-blue-500">per night</div>
                    </div>
                </div>
            </div>
            
            <!-- Status Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h5 class="font-semibold text-gray-800 mb-3">Room Status</h5>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status:</span>
                            <span class="px-2 py-1 text-xs font-medium rounded-full ${statusBadgeClass}">
                                ${getStatusLabel(room.status)}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Housekeeping:</span>
                            <span class="px-2 py-1 text-xs font-medium rounded-full ${housekeepingBadgeClass}">
                                ${getHousekeepingStatusLabel(room.housekeeping_status)}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h5 class="font-semibold text-gray-800 mb-3">Room Information</h5>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Floor:</span>
                            <span class="font-medium">${room.floor}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Capacity:</span>
                            <span class="font-medium">${room.capacity} guests</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Amenities -->
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <h5 class="font-semibold text-gray-800 mb-3">Amenities</h5>
                <div class="flex flex-wrap gap-2">
                    ${room.amenities ? room.amenities.split(', ').map(amenity => 
                        `<span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm">${amenity}</span>`
                    ).join('') : '<span class="text-gray-500">No amenities listed</span>'}
                </div>
            </div>
            
            <!-- Timestamps -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h5 class="font-semibold text-gray-800 mb-3">Record Information</h5>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Created:</span>
                        <span class="ml-2 font-medium">${new Date(room.created_at).toLocaleString()}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Last Updated:</span>
                        <span class="ml-2 font-medium">${new Date(room.updated_at).toLocaleString()}</span>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function closeRoomDetailsModal() {
    document.getElementById('room-details-modal').classList.add('hidden');
}

// Helper functions for status badges and labels
function getStatusBadgeClass(status) {
    switch (status) {
        case 'available': return 'bg-green-100 text-green-800';
        case 'occupied': return 'bg-red-100 text-red-800';
        case 'reserved': return 'bg-yellow-100 text-yellow-800';
        case 'maintenance': return 'bg-orange-100 text-orange-800';
        case 'out_of_service': return 'bg-gray-100 text-gray-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function getStatusLabel(status) {
    switch (status) {
        case 'available': return 'Available';
        case 'occupied': return 'Occupied';
        case 'reserved': return 'Reserved';
        case 'maintenance': return 'Maintenance';
        case 'out_of_service': return 'Out of Service';
        default: return 'Unknown';
    }
}

function getHousekeepingStatusBadgeClass(status) {
    switch (status) {
        case 'clean': return 'bg-green-100 text-green-800';
        case 'dirty': return 'bg-red-100 text-red-800';
        case 'cleaning': return 'bg-yellow-100 text-yellow-800';
        case 'maintenance': return 'bg-blue-100 text-blue-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function getHousekeepingStatusLabel(status) {
    switch (status) {
        case 'clean': return 'Clean';
        case 'dirty': return 'Dirty';
        case 'cleaning': return 'Cleaning';
        case 'maintenance': return 'Maintenance';
        default: return 'Unknown';
    }
}

// Export functions for global access
window.updateHousekeepingStatus = updateHousekeepingStatus;
window.closeUpdateStatusModal = closeUpdateStatusModal;
window.createMaintenanceRequest = createMaintenanceRequest;
window.closeMaintenanceModal = closeMaintenanceModal;
window.viewRoomDetails = viewRoomDetails;
window.closeRoomDetailsModal = closeRoomDetailsModal;
