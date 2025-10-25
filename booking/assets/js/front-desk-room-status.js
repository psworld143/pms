// Front Desk Room Status JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeFrontDeskRoomStatus();
    updateDateTime();
    setInterval(updateDateTime, 1000);
});

function initializeFrontDeskRoomStatus() {
    // Initialize filters
    document.getElementById('status-filter').addEventListener('change', filterRooms);
    document.getElementById('search-room').addEventListener('input', filterRooms);
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
    const searchTerm = document.getElementById('search-room').value.toLowerCase();
    
    const rows = document.querySelectorAll('#rooms-table-body tr');
    
    rows.forEach(row => {
        const roomNumber = row.querySelector('td:first-child').textContent.toLowerCase();
        const status = row.querySelector('td:nth-child(3) span').textContent.toLowerCase();
        
        const matchesStatus = !statusFilter || status.includes(statusFilter);
        const matchesSearch = !searchTerm || roomNumber.includes(searchTerm);
        
        if (matchesStatus && matchesSearch) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function viewRoomDetails(roomId) {
    const modal = document.getElementById('fd-room-details-modal');
    const content = document.getElementById('fd-room-details-content');
    content.innerHTML = `
        <div class="flex justify-center items-center py-8">
            <i class="fas fa-spinner fa-spin text-2xl text-blue-600"></i>
            <span class="ml-2 text-gray-600">Loading room details...</span>
        </div>
    `;
    modal.classList.remove('hidden');

    fetch(`../../api/get-room-details.php?id=${roomId}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) throw new Error(data.message || 'Failed');
            const room = data.room;
            const statusBadge = (s) => {
                switch (s) {
                    case 'available': return 'bg-green-100 text-green-800';
                    case 'occupied': return 'bg-red-100 text-red-800';
                    case 'reserved': return 'bg-yellow-100 text-yellow-800';
                    default: return 'bg-gray-100 text-gray-800';
                }
            };
            const housekeepingBadge = (s) => {
                switch (s) {
                    case 'clean': return 'bg-green-100 text-green-800';
                    case 'dirty': return 'bg-red-100 text-red-800';
                    case 'cleaning': return 'bg-yellow-100 text-yellow-800';
                    case 'maintenance': return 'bg-blue-100 text-blue-800';
                    default: return 'bg-gray-100 text-gray-800';
                }
            };

            content.innerHTML = `
                <div class="space-y-6">
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
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                            <h5 class="font-semibold text-gray-800 mb-3">Status</h5>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Room:</span>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full ${statusBadge(room.status)}">${room.status}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Housekeeping:</span>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full ${housekeepingBadge(room.housekeeping_status)}">${room.housekeeping_status}</span>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                            <h5 class="font-semibold text-gray-800 mb-3">Details</h5>
                            <div class="space-y-2">
                                <div class="flex justify-between"><span class="text-gray-600">Floor:</span><span class="font-medium">${room.floor}</span></div>
                                <div class="flex justify-between"><span class="text-gray-600">Capacity:</span><span class="font-medium">${room.capacity} guests</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-lg p-4">
                        <h5 class="font-semibold text-gray-800 mb-3">Amenities</h5>
                        <div class="flex flex-wrap gap-2">
                            ${(room.amenities || '').split(', ').filter(Boolean).map(a => `<span class=\"px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm\">${a}</span>`).join('') || '<span class="text-gray-500">No amenities listed</span>'}
                        </div>
                    </div>
                </div>
            `;
        })
        .catch(err => {
            console.error(err);
            content.innerHTML = `<div class="text-center py-8 text-red-600">Failed to load room details.</div>`;
        });
}

function closeFdRoomDetailsModal() {
    document.getElementById('fd-room-details-modal').classList.add('hidden');
}

function assignRoom(roomId) {
    // Redirect to the reservation page with the room pre-selected
    window.location.href = `new-reservation.php?room_id=${roomId}`;
}

function createMaintenanceRequest(roomId) {
    // Get room details from the table row
    const row = document.querySelector(`tr[data-room-id="${roomId}"]`);
    const roomNumber = row.querySelector('td:first-child div').textContent;
    
    // Show a simple notification for now
    Utils.showNotification(`Maintenance request feature for room ${roomNumber} coming soon!`, 'info');
}

// Export functions for global access
window.viewRoomDetails = viewRoomDetails;
window.assignRoom = assignRoom;
window.createMaintenanceRequest = createMaintenanceRequest;
window.closeFdRoomDetailsModal = closeFdRoomDetailsModal;
