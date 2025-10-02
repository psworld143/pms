<?php
/**
 * Mobile Interface for Housekeeping Staff
 * Hotel PMS Training System - Inventory Module
 */

session_start();
require_once __DIR__ . '/config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if user has housekeeping role
$user_role = $_SESSION['role'] ?? 'student';
if (!in_array($user_role, ['housekeeping', 'manager', 'student'])) {
    header('Location: index.php');
    exit();
}

// Set page title
$page_title = 'Housekeeping Mobile Interface';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title><?php echo $page_title; ?> - Hotel Inventory System</title>
    <link rel="icon" type="image/png" href="../../assets/images/seait-logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Mobile-specific styles */
        body { 
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -khtml-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
        }
        
        .mobile-card {
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }
        
        .swipe-indicator {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            opacity: 0.5;
        }
        
        .scan-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .scan-button:active {
            transform: scale(0.95);
        }
        
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        
        .status-available { background-color: #10B981; }
        .status-occupied { background-color: #3B82F6; }
        .status-maintenance { background-color: #F59E0B; }
        .status-out-of-order { background-color: #EF4444; }
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
<body class="bg-gray-100 min-h-screen">
    <!-- Mobile Header -->
    <header class="bg-gradient-to-r from-primary to-secondary text-white p-4 shadow-lg sticky top-0 z-50">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-mobile-alt text-xl mr-3"></i>
                <div>
                    <h1 class="text-lg font-semibold">Housekeeping</h1>
                    <p class="text-sm opacity-90"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Staff'); ?></p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button id="scan-btn" class="scan-button text-white p-2 rounded-full">
                    <i class="fas fa-qrcode text-lg"></i>
                </button>
                <button id="refresh-btn" class="bg-white bg-opacity-20 text-white p-2 rounded-full">
                    <i class="fas fa-sync-alt text-lg"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Quick Stats -->
    <div class="p-4">
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-bed text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">My Rooms</p>
                        <p class="text-xl font-semibold text-gray-900" id="my-rooms-count">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Need Restock</p>
                        <p class="text-xl font-semibold text-gray-900" id="need-restock-count">0</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floor Selection -->
    <div class="px-4 mb-4">
        <div class="bg-white rounded-lg p-4 shadow-sm">
            <h3 class="text-sm font-medium text-gray-700 mb-3">Select Floor</h3>
            <div class="flex space-x-2 overflow-x-auto pb-2" id="floor-buttons">
                <!-- Floor buttons will be loaded dynamically -->
            </div>
        </div>
    </div>

    <!-- Room List -->
    <div class="px-4 pb-20">
        <div class="space-y-3" id="room-list">
            <!-- Room cards will be loaded dynamically -->
        </div>
    </div>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 px-4 py-2">
        <div class="flex justify-around">
            <button id="nav-rooms" class="flex flex-col items-center py-2 text-primary">
                <i class="fas fa-bed text-lg mb-1"></i>
                <span class="text-xs">Rooms</span>
            </button>
            <button id="nav-cart" class="flex flex-col items-center py-2 text-gray-400">
                <i class="fas fa-shopping-cart text-lg mb-1"></i>
                <span class="text-xs">Cart</span>
            </button>
            <button id="nav-tasks" class="flex flex-col items-center py-2 text-gray-400">
                <i class="fas fa-tasks text-lg mb-1"></i>
                <span class="text-xs">Tasks</span>
            </button>
            <button id="nav-profile" class="flex flex-col items-center py-2 text-gray-400">
                <i class="fas fa-user text-lg mb-1"></i>
                <span class="text-xs">Profile</span>
            </button>
        </div>
    </nav>

    <!-- Room Details Modal -->
    <div id="room-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-end justify-center min-h-screen p-0">
            <div class="bg-white rounded-t-2xl w-full max-h-[80vh] overflow-y-auto">
                <div class="p-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800" id="modal-room-title">Room Details</h3>
                        <button id="close-room-modal" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                <div class="p-4" id="room-modal-content">
                    <!-- Room details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Barcode Scanner Modal -->
    <div id="scanner-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg w-full max-w-sm">
                <div class="p-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800">Scan Barcode</h3>
                        <button id="close-scanner-modal" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                <div class="p-4 text-center">
                    <div class="w-48 h-48 bg-gray-100 rounded-lg mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-qrcode text-4xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-600 mb-4">Point camera at barcode</p>
                    <button id="manual-scan-btn" class="w-full bg-blue-600 text-white py-2 rounded-lg">
                        Manual Entry
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        let currentFloor = 1;
        
        // Load initial data
        loadMobileStats();
        loadFloors();
        loadRoomsForFloor(currentFloor);
        
        // Navigation handlers
        $('#nav-rooms').click(function() {
            updateNavigation('rooms');
        });
        
        $('#nav-cart').click(function() {
            updateNavigation('cart');
        });
        
        $('#nav-tasks').click(function() {
            updateNavigation('tasks');
        });
        
        $('#nav-profile').click(function() {
            updateNavigation('profile');
        });
        
        // Floor selection
        $(document).on('click', '.floor-btn', function() {
            const floorId = $(this).data('floor-id');
            currentFloor = floorId;
            loadRoomsForFloor(floorId);
            
            // Update active button
            $('.floor-btn').removeClass('bg-primary text-white').addClass('bg-gray-200 text-gray-700');
            $(this).removeClass('bg-gray-200 text-gray-700').addClass('bg-primary text-white');
        });
        
        // Room card click
        $(document).on('click', '.room-card', function() {
            const roomId = $(this).data('room-id');
            showRoomDetails(roomId);
        });
        
        // Scanner button
        $('#scan-btn').click(function() {
            $('#scanner-modal').removeClass('hidden');
        });
        
        // Close modals
        $('#close-room-modal, #close-scanner-modal').click(function() {
            $('#room-modal, #scanner-modal').addClass('hidden');
        });
        
        // Refresh button
        $('#refresh-btn').click(function() {
            loadMobileStats();
            loadRoomsForFloor(currentFloor);
        });
        
        function updateNavigation(active) {
            $('.flex.flex-col.items-center.py-2').removeClass('text-primary').addClass('text-gray-400');
            $(`#nav-${active}`).removeClass('text-gray-400').addClass('text-primary');
        }
        
        function loadMobileStats() {
            $.ajax({
                url: 'api/get-mobile-stats.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#my-rooms-count').text(response.stats.my_rooms);
                        $('#need-restock-count').text(response.stats.need_restock);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading mobile stats:', error);
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
                            const buttonClass = index === 0 ? 'bg-primary text-white' : 'bg-gray-200 text-gray-700';
                            const button = `
                                <button class="floor-btn px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap ${buttonClass}" data-floor-id="${floor.id}">
                                    ${floor.floor_name || `Floor ${floor.floor_number}`}
                                </button>
                            `;
                            floorButtons.append(button);
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading floors:', error);
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
                        displayMobileRooms(response.rooms);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading rooms:', error);
                }
            });
        }
        
        function displayMobileRooms(rooms) {
            const roomList = $('#room-list');
            roomList.empty();
            
            rooms.forEach(function(room) {
                const statusClass = getStatusClass(room.status);
                const stockStatus = getStockStatus(room.stock_status);
                
                const roomCard = `
                    <div class="room-card mobile-card bg-white rounded-lg p-4 shadow-sm border-l-4 ${statusClass}">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center">
                                <span class="status-indicator status-${room.status}"></span>
                                <h4 class="text-lg font-semibold text-gray-800">Room ${room.room_number}</h4>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600 capitalize">${room.room_type}</p>
                                <p class="text-xs ${stockStatus.class}">${stockStatus.text}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-boxes mr-1"></i>
                                <span>${room.total_items || 0} items</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-chevron-right text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                `;
                roomList.append(roomCard);
            });
        }
        
        function getStatusClass(status) {
            switch(status) {
                case 'available': return 'border-green-500';
                case 'occupied': return 'border-blue-500';
                case 'maintenance': return 'border-yellow-500';
                case 'out_of_order': return 'border-red-500';
                default: return 'border-gray-500';
            }
        }
        
        function getStockStatus(stockStatus) {
            switch(stockStatus) {
                case 'fully_stocked': return { class: 'text-green-600', text: 'Stocked' };
                case 'needs_restocking': return { class: 'text-yellow-600', text: 'Low Stock' };
                case 'critical_stock': return { class: 'text-red-600', text: 'Empty' };
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
                        displayMobileRoomDetails(response.room);
                        $('#room-modal').removeClass('hidden');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading room details:', error);
                }
            });
        }
        
        function displayMobileRoomDetails(room) {
            $('#modal-room-title').text(`Room ${room.room_number}`);
            
            let content = `
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-600">Status:</span>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 capitalize">${room.status}</span>
                    </div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-600">Type:</span>
                        <span class="text-sm font-medium capitalize">${room.room_type}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Max Occupancy:</span>
                        <span class="text-sm font-medium">${room.max_occupancy}</span>
                    </div>
                </div>
                
                <div class="border-t pt-4">
                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Inventory Items</h4>
                    <div class="space-y-2">
            `;
            
            if (room.inventory_items && room.inventory_items.length > 0) {
                room.inventory_items.forEach(function(item) {
                    const statusClass = item.quantity_current >= item.par_level ? 'text-green-600' : 
                                       item.quantity_current > 0 ? 'text-yellow-600' : 'text-red-600';
                    const statusText = item.quantity_current >= item.par_level ? 'OK' : 
                                      item.quantity_current > 0 ? 'Low' : 'Empty';
                    
                    content += `
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">${item.item_name}</p>
                                <p class="text-xs text-gray-500">${item.quantity_current}/${item.par_level} ${item.unit}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs ${statusClass} font-medium">${statusText}</p>
                                <button class="mt-1 text-xs bg-blue-600 text-white px-2 py-1 rounded">
                                    Restock
                                </button>
                            </div>
                        </div>
                    `;
                });
            } else {
                content += `
                    <div class="text-center py-4 text-gray-500">
                        <i class="fas fa-box-open text-2xl mb-2"></i>
                        <p class="text-sm">No inventory items assigned</p>
                    </div>
                `;
            }
            
            content += `
                    </div>
                </div>
            `;
            
            $('#room-modal-content').html(content);
        }
    });
    </script>
</body>
</html>
