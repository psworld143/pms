<?php
/**
 * Room Inventory Management
 * Hotel PMS Training System - Inventory Module
 */

session_start();
require_once __DIR__ . '/config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Set page title
$page_title = 'Room Inventory Management';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Hotel Inventory System</title>
    <link rel="icon" type="image/png" href="../../assets/images/seait-logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        #sidebar { transition: transform 0.3s ease-in-out; }
        @media (max-width: 1023px) { #sidebar { transform: translateX(-100%); z-index: 50; } #sidebar.sidebar-open { transform: translateX(0); } }
        @media (min-width: 1024px) { #sidebar { transform: translateX(0) !important; } }
        #sidebar-overlay { transition: opacity 0.3s ease-in-out; z-index: 40; }
        .main-content { margin-left: 0; padding-top: 4rem; }
        @media (min-width: 1024px) { .main-content { margin-left: 16rem; } }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#10B981',
                        secondary: '#059669',
                        success: '#28a745',
                        danger: '#dc3545',
                        warning: '#ffc107',
                        info: '#17a2b8'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <!-- Sidebar Overlay for Mobile -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden" onclick="closeSidebar()"></div>
        
        <!-- Include unified inventory header and sidebar -->
        <?php include 'includes/inventory-header.php'; ?>
        <?php include 'includes/sidebar-inventory.php'; ?>

        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Room Inventory Management</h2>
                <div class="flex items-center space-x-4">
                    <button id="audit-rooms-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-clipboard-check mr-2"></i>Audit Rooms
                    </button>
                    <button id="restock-rooms-btn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-boxes mr-2"></i>Restock Rooms
                    </button>
                </div>
            </div>

            <!-- Room Inventory Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-bed text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Rooms</p>
                            <p class="text-2xl font-semibold text-gray-900" id="total-rooms">Loading...</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-check-circle text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Fully Stocked</p>
                            <p class="text-2xl font-semibold text-gray-900" id="fully-stocked">Loading...</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Need Restocking</p>
                            <p class="text-2xl font-semibold text-gray-900" id="need-restocking">Loading...</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-times-circle text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Critical Stock</p>
                            <p class="text-2xl font-semibold text-gray-900" id="critical-stock">Loading...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Floor Selection -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Select Floor</h3>
                <div class="flex flex-wrap gap-2" id="floor-buttons">
                    <!-- Floor buttons will be loaded dynamically -->
                </div>
            </div>

            <!-- Room Inventory Grid -->
            <div id="room-inventory-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <!-- Room cards will be loaded dynamically -->
            </div>
        </main>

        <!-- Room Details Modal -->
        <div id="room-details-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-800" id="modal-room-title">Room Details</h3>
                            <button id="close-modal" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <div id="room-details-content">
                            <!-- Room details will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Include footer -->
        <?php include '../includes/pos-footer.php'; ?>
    </body>
</html>

<script>
$(document).ready(function() {
    loadRoomInventoryStats();
    loadFloors();
    
    // Load rooms for default floor (floor 1)
    loadRoomsForFloor(1);
    
    // Floor button click handler
    $(document).on('click', '.floor-btn', function() {
        const floorId = $(this).data('floor-id');
        loadRoomsForFloor(floorId);
        
        // Update active button
        $('.floor-btn').removeClass('bg-blue-600 text-white').addClass('bg-gray-200 text-gray-700');
        $(this).removeClass('bg-gray-200 text-gray-700').addClass('bg-blue-600 text-white');
    });
    
    // Room card click handler
    $(document).on('click', '.room-card', function() {
        const roomId = $(this).data('room-id');
        showRoomDetails(roomId);
    });
    
    // Close modal handler
    $('#close-modal').click(function() {
        $('#room-details-modal').addClass('hidden');
    });
    
    // Close modal when clicking outside
    $('#room-details-modal').click(function(e) {
        if (e.target === this) {
            $(this).addClass('hidden');
        }
    });
    
    // Audit rooms button
    $('#audit-rooms-btn').click(function() {
        startRoomAudit();
    });
    
    // Restock rooms button
    $('#restock-rooms-btn').click(function() {
        startRoomRestock();
    });
    
    function loadRoomInventoryStats() {
        $.ajax({
            url: 'api/get-room-inventory-stats.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const stats = response.statistics;
                    $('#total-rooms').text(stats.total_rooms);
                    $('#fully-stocked').text(stats.fully_stocked);
                    $('#need-restocking').text(stats.need_restocking);
                    $('#critical-stock').text(stats.critical_stock);
                } else {
                    console.error('Error loading room inventory stats:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error loading room inventory stats:', error);
            }
        });
    }
    
    function loadFloors() {
        $.ajax({
            url: 'api/get-hotel-floors.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const floorButtons = $('#floor-buttons');
                    floorButtons.empty();
                    
                    response.floors.forEach(function(floor, index) {
                        const buttonClass = index === 0 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700';
                        const button = `
                            <button class="floor-btn px-4 py-2 rounded-lg font-medium transition-colors ${buttonClass}" data-floor-id="${floor.id}">
                                ${floor.floor_name || `Floor ${floor.floor_number}`}
                            </button>
                        `;
                        floorButtons.append(button);
                    });
                } else {
                    console.error('Error loading floors:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error loading floors:', error);
            }
        });
    }
    
    function loadRoomsForFloor(floorId) {
        $.ajax({
            url: 'api/get-rooms-for-floor.php',
            method: 'GET',
            data: { floor_id: floorId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayRooms(response.rooms);
                } else {
                    console.error('Error loading rooms:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error loading rooms:', error);
            }
        });
    }
    
    function displayRooms(rooms) {
        const grid = $('#room-inventory-grid');
        grid.empty();
        
        rooms.forEach(function(room) {
            const statusClass = getStatusClass(room.status);
            const stockStatus = getStockStatus(room.stock_status);
            
            const roomCard = `
                <div class="room-card bg-white rounded-lg shadow p-6 cursor-pointer hover:shadow-lg transition-shadow" data-room-id="${room.id}">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-lg font-semibold text-gray-800">Room ${room.room_number}</h4>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full ${statusClass}">
                            ${room.status.charAt(0).toUpperCase() + room.status.slice(1)}
                        </span>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Type:</span>
                            <span class="text-gray-900 capitalize">${room.room_type}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Stock Status:</span>
                            <span class="font-medium ${stockStatus.class}">${stockStatus.text}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Items:</span>
                            <span class="text-gray-900">${room.total_items || 0}</span>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <button class="w-full bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm font-medium">
                            View Details
                        </button>
                    </div>
                </div>
            `;
            grid.append(roomCard);
        });
    }
    
    function getStatusClass(status) {
        switch(status) {
            case 'available': return 'bg-green-100 text-green-800';
            case 'occupied': return 'bg-blue-100 text-blue-800';
            case 'maintenance': return 'bg-yellow-100 text-yellow-800';
            case 'out_of_order': return 'bg-red-100 text-red-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }
    
    function getStockStatus(stockStatus) {
        switch(stockStatus) {
            case 'fully_stocked': return { class: 'text-green-600', text: 'Fully Stocked' };
            case 'needs_restocking': return { class: 'text-yellow-600', text: 'Needs Restocking' };
            case 'critical_stock': return { class: 'text-red-600', text: 'Critical Stock' };
            default: return { class: 'text-gray-600', text: 'Unknown' };
        }
    }
    
    function showRoomDetails(roomId) {
        $.ajax({
            url: 'api/get-room-details.php',
            method: 'GET',
            data: { room_id: roomId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayRoomDetails(response.room);
                    $('#room-details-modal').removeClass('hidden');
                } else {
                    console.error('Error loading room details:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error loading room details:', error);
            }
        });
    }
    
    function displayRoomDetails(room) {
        $('#modal-room-title').text(`Room ${room.room_number} - Inventory Details`);
        
        let content = `
            <div class="mb-6">
                <h4 class="text-lg font-semibold text-gray-800 mb-4">Room Information</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm text-gray-600">Room Type:</span>
                        <p class="font-medium">${room.room_type}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Status:</span>
                        <p class="font-medium capitalize">${room.status}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Max Occupancy:</span>
                        <p class="font-medium">${room.max_occupancy}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Last Audited:</span>
                        <p class="font-medium">${room.last_audited || 'Never'}</p>
                    </div>
                </div>
            </div>
            
            <div>
                <h4 class="text-lg font-semibold text-gray-800 mb-4">Inventory Items</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Allocated</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Par Level</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
        `;
        
        if (room.inventory_items && room.inventory_items.length > 0) {
            room.inventory_items.forEach(function(item) {
                const statusClass = item.quantity_current >= item.par_level ? 'bg-green-100 text-green-800' : 
                                   item.quantity_current > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800';
                const statusText = item.quantity_current >= item.par_level ? 'OK' : 
                                  item.quantity_current > 0 ? 'Low' : 'Empty';
                
                content += `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">${item.item_name}</div>
                            <div class="text-sm text-gray-500">${item.sku}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.quantity_allocated}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.quantity_current}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.par_level}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                                ${statusText}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button class="text-blue-600 hover:text-blue-900 mr-3">Restock</button>
                            <button class="text-green-600 hover:text-green-900">Audit</button>
                        </td>
                    </tr>
                `;
            });
        } else {
            content += `
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        No inventory items assigned to this room
                    </td>
                </tr>
            `;
        }
        
        content += `
                        </tbody>
                    </table>
                </div>
            </div>
        `;
        
        $('#room-details-content').html(content);
    }
    
    function startRoomAudit() {
        if (confirm('Start room audit for all rooms? This will check inventory levels in all rooms.')) {
            $.ajax({
                url: 'api/start-room-audit.php',
                method: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Room audit started successfully!');
                        loadRoomInventoryStats();
                        loadRoomsForFloor(currentFloor);
                    } else {
                        alert('Error starting room audit: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error starting room audit:', error);
                    alert('Error starting room audit');
                }
            });
        }
    }
    
    function startRoomRestock() {
        if (confirm('Start room restocking for all rooms? This will restock items below par levels.')) {
            $.ajax({
                url: 'api/start-room-restock.php',
                method: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Room restocking started successfully!');
                        loadRoomInventoryStats();
                        loadRoomsForFloor(currentFloor);
                    } else {
                        alert('Error starting room restock: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error starting room restock:', error);
                    alert('Error starting room restock');
                }
            });
        }
    }
});
</script>
