<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and has manager access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    header('Location: ../../login.php');
    exit();
}

// Set page title
$page_title = 'Room Management';

// Include unified header and sidebar
include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';
?>

<!-- Main Content -->
<main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
        <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Room Management</h2>
        <div class="flex items-center space-x-4">
            <button onclick="openCreateRoomModal()" class="bg-primary hover:bg-secondary text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-plus mr-2"></i>Add New Room
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Room Type</label>
                <select id="room-type-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">All Types</option>
                    <option value="standard">Standard</option>
                    <option value="deluxe">Deluxe</option>
                    <option value="suite">Suite</option>
                    <option value="presidential">Presidential</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="status-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">All Status</option>
                    <option value="available">Available</option>
                    <option value="occupied">Occupied</option>
                    <option value="reserved">Reserved</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="out_of_service">Out of Service</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Floor</label>
                <select id="floor-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">All Floors</option>
                    <option value="1">Floor 1</option>
                    <option value="2">Floor 2</option>
                    <option value="3">Floor 3</option>
                    <option value="4">Floor 4</option>
                    <option value="5">Floor 5</option>
                </select>
            </div>
            <div class="flex items-end">
                <button onclick="loadRooms()" class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
            </div>
        </div>
    </div>

    <!-- Rooms Table -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Rooms Directory</h3>
        </div>
        
        <div id="rooms-table-container">
            <!-- Rooms table will be loaded here -->
        </div>
    </div>
</main>

<!-- Create/Edit Room Modal -->
<div id="room-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-8 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-900" id="room-modal-title">Add New Room</h3>
            <button onclick="closeRoomModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form id="room-form" class="space-y-6">
            <input type="hidden" id="room_id" name="room_id">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Room Number *</label>
                    <input type="text" name="room_number" id="room_number" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Room Type *</label>
                    <select name="room_type" id="room_type" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="">Select Room Type</option>
                        <option value="standard">Standard Room</option>
                        <option value="deluxe">Deluxe Room</option>
                        <option value="suite">Suite</option>
                        <option value="presidential">Presidential Suite</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Floor *</label>
                    <input type="number" name="floor" id="floor" min="1" max="10" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Capacity *</label>
                    <input type="number" name="capacity" id="capacity" min="1" max="10" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rate (₱) *</label>
                    <input type="number" name="rate" id="rate" step="0.01" min="0" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" id="status" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="available">Available</option>
                        <option value="occupied">Occupied</option>
                        <option value="reserved">Reserved</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="out_of_service">Out of Service</option>
                    </select>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Amenities</label>
                <textarea name="amenities" id="amenities" rows="3" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                          placeholder="e.g., WiFi, TV, Mini Bar, Balcony..."></textarea>
            </div>
            
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeRoomModal()" 
                        class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-6 py-2 bg-primary text-white rounded-md hover:bg-secondary">
                    Save Room
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-room-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <div class="flex items-center mb-6">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">Delete Room</h3>
                <p class="text-sm text-gray-600">Are you sure you want to delete this room?</p>
            </div>
        </div>
        
        <div id="delete-room-info" class="mb-6 p-4 bg-gray-50 rounded-md">
            <!-- Room info will be populated here -->
        </div>
        
        <div class="flex justify-end space-x-4">
            <button onclick="closeDeleteRoomModal()" 
                    class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button onclick="confirmDeleteRoom()" 
                    class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                Delete Room
            </button>
        </div>
    </div>
</div>

<script>
let currentRoomId = null;
let isEditMode = false;

// Load rooms on page load
document.addEventListener('DOMContentLoaded', function() {
    loadRooms();
    initializeRoomForm();
});

// Load rooms
function loadRooms() {
    const roomType = document.getElementById('room-type-filter').value;
    const status = document.getElementById('status-filter').value;
    const floor = document.getElementById('floor-filter').value;
    
    fetch(`../../api/get-rooms.php?room_type=${roomType}&status=${status}&floor=${floor}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayRooms(data.rooms);
            } else {
                Utils.showNotification(data.message || 'Error loading rooms', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading rooms:', error);
            Utils.showNotification('Error loading rooms', 'error');
        });
}

// Display rooms in table
function displayRooms(rooms) {
    const container = document.getElementById('rooms-table-container');
    
    if (rooms.length === 0) {
        container.innerHTML = `
            <div class="p-8 text-center text-gray-500">
                <i class="fas fa-bed text-4xl mb-4"></i>
                <p>No rooms found</p>
            </div>
        `;
        return;
    }
    
    const tableHTML = `
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Floor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    ${rooms.map(room => `
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">${room.room_number}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getRoomTypeClass(room.room_type)}">
                                    ${getRoomTypeLabel(room.room_type)}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                Floor ${room.floor}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${room.capacity} ${room.capacity === 1 ? 'guest' : 'guests'}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ₱${parseFloat(room.rate).toFixed(2)}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusClass(room.status)}">
                                    ${getStatusLabel(room.status)}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button onclick="editRoom(${room.id})" class="text-indigo-600 hover:text-indigo-900">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="showDeleteRoomModal(${room.id}, '${room.room_number}')" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
    
    container.innerHTML = tableHTML;
}

// Modal functions
function openCreateRoomModal() {
    isEditMode = false;
    document.getElementById('room-modal-title').textContent = 'Add New Room';
    document.getElementById('room-form').reset();
    document.getElementById('room_id').value = '';
    document.getElementById('room-modal').classList.remove('hidden');
}

function editRoom(roomId) {
    isEditMode = true;
    currentRoomId = roomId;
    document.getElementById('room-modal-title').textContent = 'Edit Room';
    
    // Load room data
    fetch(`../../api/get-room-details.php?id=${roomId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const room = data.room;
                document.getElementById('room_id').value = room.id;
                document.getElementById('room_number').value = room.room_number;
                document.getElementById('room_type').value = room.room_type;
                document.getElementById('floor').value = room.floor;
                document.getElementById('capacity').value = room.capacity;
                document.getElementById('rate').value = room.rate;
                document.getElementById('status').value = room.status;
                document.getElementById('amenities').value = room.amenities || '';
                
                document.getElementById('room-modal').classList.remove('hidden');
            } else {
                Utils.showNotification(data.message || 'Error loading room details', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading room details:', error);
            Utils.showNotification('Error loading room details', 'error');
        });
}

function closeRoomModal() {
    document.getElementById('room-modal').classList.add('hidden');
    currentRoomId = null;
    isEditMode = false;
}

function showDeleteRoomModal(roomId, roomNumber) {
    currentRoomId = roomId;
    document.getElementById('delete-room-info').innerHTML = `
        <p><strong>Room Number:</strong> ${roomNumber}</p>
        <p class="text-sm text-gray-600 mt-2">This action cannot be undone.</p>
    `;
    document.getElementById('delete-room-modal').classList.remove('hidden');
}

function closeDeleteRoomModal() {
    document.getElementById('delete-room-modal').classList.add('hidden');
    currentRoomId = null;
}

function confirmDeleteRoom() {
    if (!currentRoomId) return;
    
    fetch('../../api/delete-room.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            room_id: currentRoomId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Utils.showNotification(data.message, 'success');
            loadRooms();
            closeDeleteRoomModal();
        } else {
            Utils.showNotification(data.message || 'Error deleting room', 'error');
        }
    })
    .catch(error => {
        console.error('Error deleting room:', error);
        Utils.showNotification('Error deleting room', 'error');
    });
}

// Initialize form
function initializeRoomForm() {
    document.getElementById('room-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        const url = isEditMode ? '../../api/update-room.php' : '../../api/create-room.php';
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Utils.showNotification(data.message, 'success');
                loadRooms();
                closeRoomModal();
            } else {
                Utils.showNotification(data.message || 'Error saving room', 'error');
            }
        })
        .catch(error => {
            console.error('Error saving room:', error);
            Utils.showNotification('Error saving room', 'error');
        });
    });
}

// Utility functions
function getRoomTypeClass(type) {
    const classes = {
        'standard': 'bg-gray-100 text-gray-800',
        'deluxe': 'bg-blue-100 text-blue-800',
        'suite': 'bg-purple-100 text-purple-800',
        'presidential': 'bg-yellow-100 text-yellow-800'
    };
    return classes[type] || 'bg-gray-100 text-gray-800';
}

function getRoomTypeLabel(type) {
    const labels = {
        'standard': 'Standard',
        'deluxe': 'Deluxe',
        'suite': 'Suite',
        'presidential': 'Presidential'
    };
    return labels[type] || type;
}

function getStatusClass(status) {
    const classes = {
        'available': 'bg-green-100 text-green-800',
        'occupied': 'bg-red-100 text-red-800',
        'reserved': 'bg-yellow-100 text-yellow-800',
        'maintenance': 'bg-orange-100 text-orange-800',
        'out_of_service': 'bg-gray-100 text-gray-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}

function getStatusLabel(status) {
    const labels = {
        'available': 'Available',
        'occupied': 'Occupied',
        'reserved': 'Reserved',
        'maintenance': 'Maintenance',
        'out_of_service': 'Out of Service'
    };
    return labels[status] || status;
}
</script>

<?php include '../../includes/footer.php'; ?>
