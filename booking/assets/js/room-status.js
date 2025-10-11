/**
 * Room Status Management JavaScript
 */

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Room status JavaScript loaded');
    // Data is loaded in PHP, no initialization needed
});

function setupEventListeners() {
    // Refresh button
    const refreshBtn = document.querySelector('button[onclick="refreshRoomStatus()"]');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            refreshRoomStatus();
        });
    }
}

// Data loading functions removed - data is now loaded in PHP

function getStatusBadgeClass(status) {
    switch(status) {
        case 'available':
            return 'bg-green-100 text-green-800';
        case 'occupied':
            return 'bg-red-100 text-red-800';
        case 'maintenance':
            return 'bg-yellow-100 text-yellow-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function getHousekeepingBadgeClass(status) {
    switch(status) {
        case 'clean':
            return 'bg-green-100 text-green-800';
        case 'dirty':
            return 'bg-red-100 text-red-800';
        case 'maintenance':
            return 'bg-yellow-100 text-yellow-800';
        case 'cleaning':
            return 'bg-blue-100 text-blue-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function refreshRoomStatus() {
    console.log('Refreshing room status...');
    // Since data is loaded in PHP, we just reload the page
    window.location.reload();
}

function viewRoomDetails(roomId) {
    try {
        console.log('Viewing room details for ID:', roomId);
        
        // Get room data from the table row
        const roomRow = document.querySelector(`button[onclick="viewRoomDetails(${roomId})"]`).closest('tr');
        if (!roomRow) {
            alert('Room data not found');
            return;
        }
        
        // Extract room data from the table
        const roomNumber = roomRow.querySelector('.text-sm.font-medium.text-gray-900').textContent.replace('Room ', '');
        const floor = roomRow.querySelector('.text-sm.text-gray-500').textContent.replace('Floor ', '');
        const roomType = roomRow.cells[1].textContent.trim();
        const status = roomRow.cells[2].textContent.trim();
        const housekeepingStatus = roomRow.cells[3].textContent.trim();
        
        // Create room object
        const room = {
            id: roomId,
            room_number: roomNumber,
            floor: floor,
            room_type: roomType,
            status: status,
            housekeeping_status: housekeepingStatus,
            amenities: 'WiFi, Flat-screen TV, Air Conditioning, Mini Fridge, Coffee Maker, Safe'
        };
        
        showRoomDetailsModal(room);
    } catch (error) {
        console.error('Error in viewRoomDetails:', error);
        alert('Error loading room details: ' + error.message);
    }
}

function showRoomDetailsModal(room) {
    try {
        const modal = document.getElementById('room-details-modal');
        const content = document.getElementById('room-details-content');
        
        if (!modal || !content) {
            alert('Modal elements not found');
            return;
        }
        
        content.innerHTML = `
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h4 class="text-lg font-medium text-gray-900">Room ${room.room_number}</h4>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full ${getStatusBadgeClass(room.status)}">
                        ${room.status}
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Type</label>
                        <p class="text-sm text-gray-900">${room.room_type}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Floor</label>
                        <p class="text-sm text-gray-900">${room.floor}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Capacity</label>
                        <p class="text-sm text-gray-900">2 guests</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Rate</label>
                        <p class="text-sm text-gray-900">₱180.00</p>
                    </div>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Housekeeping Status</label>
                    <p class="text-sm text-gray-900">${room.housekeeping_status || 'Not set'}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Amenities</label>
                    <p class="text-sm text-gray-900">${room.amenities || 'None listed'}</p>
                </div>
            </div>
        `;
        
        modal.classList.remove('hidden');
    } catch (error) {
        console.error('Error in showRoomDetailsModal:', error);
        alert('Error showing room details: ' + error.message);
    }
}

function closeRoomDetailsModal() {
    const modal = document.getElementById('room-details-modal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

function updateRoomStatus(roomId) {
    try {
        console.log('Updating room status for ID:', roomId);
        
        // Get current housekeeping status from the table
        const roomRow = document.querySelector(`button[onclick="updateRoomStatus(${roomId})"]`).closest('tr');
        if (!roomRow) {
            alert('Room data not found');
            return;
        }
        
        const currentStatus = roomRow.cells[3].textContent.trim();
        
        // Show a prompt with current status
        const newStatus = prompt(`Enter new housekeeping status for Room ${roomId}:\n\nCurrent: ${currentStatus}\n\nOptions: clean, dirty, maintenance, cleaning`, currentStatus);
        
        if (!newStatus) return;
        
        const validStatuses = ['clean', 'dirty', 'maintenance', 'cleaning'];
        if (!validStatuses.includes(newStatus.toLowerCase())) {
            alert('Invalid status. Please use: clean, dirty, maintenance, or cleaning');
            return;
        }
        
        // Update the status in the table immediately (optimistic update)
        const statusCell = roomRow.cells[3];
        const statusSpan = statusCell.querySelector('span');
        
        // Update the text
        statusSpan.textContent = newStatus.toLowerCase();
        
        // Update the CSS class
        statusSpan.className = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full ' + getHousekeepingBadgeClass(newStatus.toLowerCase());
        
        // Update the status overview counts
        updateStatusCounts();
        
        alert(`Room ${roomId} status updated to ${newStatus}`);
        
    } catch (error) {
        console.error('Error in updateRoomStatus:', error);
        alert('Error updating room status: ' + error.message);
    }
}

function updateStatusCounts() {
    // Count rooms by housekeeping status
    const rows = document.querySelectorAll('#rooms-table-body tr');
    let cleanCount = 0, dirtyCount = 0, maintenanceCount = 0, cleaningCount = 0;
    
    rows.forEach(row => {
        const status = row.cells[3].textContent.trim();
        switch(status) {
            case 'clean': cleanCount++; break;
            case 'dirty': dirtyCount++; break;
            case 'maintenance': maintenanceCount++; break;
            case 'cleaning': cleaningCount++; break;
        }
    });
    
    // Update the count displays
    const availableCount = document.getElementById('available-count');
    const occupiedCount = document.getElementById('occupied-count');
    const maintenanceCountEl = document.getElementById('maintenance-count');
    const cleaningCountEl = document.getElementById('cleaning-count');
    
    if (availableCount) availableCount.textContent = cleanCount;
    if (occupiedCount) occupiedCount.textContent = dirtyCount;
    if (maintenanceCountEl) maintenanceCountEl.textContent = maintenanceCount;
    if (cleaningCountEl) cleaningCountEl.textContent = cleaningCount;
}

function testFunctions() {
    alert('JavaScript is working! Functions available:\n' +
          'viewRoomDetails: ' + (typeof window.viewRoomDetails) + '\n' +
          'updateRoomStatus: ' + (typeof window.updateRoomStatus) + '\n' +
          'refreshRoomStatus: ' + (typeof window.refreshRoomStatus));
}

// Export functions for global access
window.refreshRoomStatus = refreshRoomStatus;
window.viewRoomDetails = viewRoomDetails;
window.closeRoomDetailsModal = closeRoomDetailsModal;
window.updateRoomStatus = updateRoomStatus;
window.showRoomDetailsModal = showRoomDetailsModal;
window.testFunctions = testFunctions;

// Test if functions are available
console.log('Functions attached to window:', {
    refreshRoomStatus: typeof window.refreshRoomStatus,
    viewRoomDetails: typeof window.viewRoomDetails,
    updateRoomStatus: typeof window.updateRoomStatus,
    closeRoomDetailsModal: typeof window.closeRoomDetailsModal
});