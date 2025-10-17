<?php
/**
 * Room Inventory Management - Enhanced with Integrated Requests
 * Hotel PMS Training System - Inventory Module
 */

// Redirect to enhanced version
header('Location: enhanced-room-inventory.php');
exit();

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
                <div>
                    <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">
                        <?php if ($is_housekeeping): ?>
                            <i class="fas fa-bed mr-2 text-blue-600"></i>Room Inventory - Housekeeping
                        <?php else: ?>
                            <i class="fas fa-cogs mr-2 text-green-600"></i>Room Inventory Management
                        <?php endif; ?>
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">
                        <?php if ($is_housekeeping): ?>
                            View and update room inventory for the rooms you clean
                        <?php else: ?>
                            Complete inventory management and monitoring system
                        <?php endif; ?>
                    </p>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if ($is_housekeeping): ?>
                        <button id="check-rooms-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                            <i class="fas fa-clipboard-check mr-2"></i>Check Rooms
                        </button>
                        <button id="request-supplies-btn" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                            <i class="fas fa-shopping-cart mr-2"></i>Request Supplies
                        </button>
                    <?php else: ?>
                        <button id="audit-rooms-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                            <i class="fas fa-clipboard-check mr-2"></i>Audit Rooms
                        </button>
                        <button id="restock-rooms-btn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                            <i class="fas fa-boxes mr-2"></i>Restock Rooms
                        </button>
                        <button id="manage-items-btn" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                            <i class="fas fa-cog mr-2"></i>Manage Items
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Enhanced Room Inventory Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <?php if ($is_housekeeping): ?>
                    <!-- Housekeeping Statistics -->
                    <!-- Rooms to Clean Card -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 border border-blue-200 group">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                        <i class="fas fa-bed text-white text-lg"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-semibold text-blue-700 uppercase tracking-wide">My Rooms</p>
                                    <p class="text-3xl font-bold text-blue-900" id="my-rooms">Loading...</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="w-3 h-3 bg-blue-500 rounded-full animate-pulse"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Used Today Card -->
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 border border-green-200 group">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                        <i class="fas fa-clipboard-check text-white text-lg"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-semibold text-green-700 uppercase tracking-wide">Items Used</p>
                                    <p class="text-3xl font-bold text-green-900" id="items-used">Loading...</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Missing Items Card -->
                    <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 border border-yellow-200 group">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                        <i class="fas fa-exclamation-triangle text-white text-lg"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-semibold text-yellow-700 uppercase tracking-wide">Missing Items</p>
                                    <p class="text-3xl font-bold text-yellow-900" id="missing-items">Loading...</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="w-3 h-3 bg-yellow-500 rounded-full animate-pulse"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Requests Card -->
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 border border-purple-200 group">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                        <i class="fas fa-shopping-cart text-white text-lg"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-semibold text-purple-700 uppercase tracking-wide">My Requests</p>
                                    <p class="text-3xl font-bold text-purple-900" id="my-requests">Loading...</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="w-3 h-3 bg-purple-500 rounded-full animate-pulse"></div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Manager Statistics -->
                    <!-- Total Rooms Card -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 border border-blue-200 group">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                        <i class="fas fa-bed text-white text-lg"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-semibold text-blue-700 uppercase tracking-wide">Total Rooms</p>
                                    <p class="text-3xl font-bold text-blue-900" id="total-rooms">Loading...</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="w-3 h-3 bg-blue-500 rounded-full animate-pulse"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Fully Stocked Card -->
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 border border-green-200 group">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                        <i class="fas fa-check-circle text-white text-lg"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-semibold text-green-700 uppercase tracking-wide">Fully Stocked</p>
                                    <p class="text-3xl font-bold text-green-900" id="fully-stocked">Loading...</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Need Restocking Card -->
                    <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 border border-yellow-200 group">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                        <i class="fas fa-exclamation-triangle text-white text-lg"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-semibold text-yellow-700 uppercase tracking-wide">Need Restocking</p>
                                    <p class="text-3xl font-bold text-yellow-900" id="need-restocking">Loading...</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="w-3 h-3 bg-yellow-500 rounded-full animate-pulse"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Critical Stock Card -->
                    <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 border border-red-200 group">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                        <i class="fas fa-times-circle text-white text-lg"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-semibold text-red-700 uppercase tracking-wide">Critical Stock</p>
                                    <p class="text-3xl font-bold text-red-900" id="critical-stock">Loading...</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Enhanced Floor Selection -->
            <div class="bg-gradient-to-r from-white to-gray-50 rounded-xl shadow-lg p-6 mb-8 border border-gray-200">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-building mr-3 text-blue-600"></i>
                        Select Floor
                    </h3>
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Click a floor to view room inventory
                    </div>
                </div>
                <div class="flex flex-wrap gap-3" id="floor-buttons">
                    <!-- Floor buttons will be loaded dynamically -->
                    <div class="flex items-center justify-center w-full py-8 text-gray-500">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Loading floors...
                    </div>
                </div>
            </div>

            <!-- Enhanced Room Inventory Grid -->
            <div id="room-inventory-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <!-- Room cards will be loaded dynamically -->
                <div class="col-span-full flex items-center justify-center py-12 text-gray-500">
                    <div class="text-center">
                        <i class="fas fa-bed text-4xl mb-4 text-gray-300"></i>
                        <p class="text-lg font-medium">Select a floor to view room inventory</p>
                        <p class="text-sm">Choose a floor from the options above to see room details</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Room Details Modal -->
        <div id="room-details-modal" class="fixed inset-0 bg-black bg-opacity-60 z-50 hidden backdrop-blur-sm">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-6xl w-full max-h-[95vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0" id="modal-content">
                    <!-- Modal Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-6 text-white">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center space-x-3">
                                <div class="bg-white bg-opacity-20 p-3 rounded-xl">
                                    <i class="fas fa-bed text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-bold" id="modal-room-title">Room Details</h3>
                                    <p class="text-blue-100 text-sm">Complete inventory information</p>
                                </div>
                            </div>
                            <button id="close-modal" class="bg-white bg-opacity-20 hover:bg-opacity-30 p-3 rounded-xl transition-all duration-200 transform hover:scale-110">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Modal Body -->
                    <div class="p-6 bg-gray-50 max-h-[80vh] overflow-y-auto">
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
    let currentFloor = 2; // Track current floor (start with floor 2 since floor 1 has no rooms)
    
    loadRoomInventoryStats();
    loadFloors();
    
    // Load rooms for default floor (floor 2)
    loadRoomsForFloor(currentFloor);
    
    // Floor button click handler
    $(document).on('click', '.floor-btn', function() {
        const floorId = $(this).data('floor-id');
        currentFloor = floorId; // Update current floor
        loadRoomsForFloor(floorId);
        
        // Update active button
        $('.floor-btn').removeClass('bg-blue-600 text-white shadow-lg').addClass('bg-gray-200 text-gray-700 hover:bg-gray-300');
        $(this).removeClass('bg-gray-200 text-gray-700 hover:bg-gray-300').addClass('bg-blue-600 text-white shadow-lg');
    });
    
    // Room card click handler
    $(document).on('click', '.room-card', function() {
        const roomId = $(this).data('room-id');
        showRoomDetails(roomId);
    });
    
    // Individual room audit button handler
    $(document).on('click', '#audit-this-room-btn', function() {
        const roomId = $(this).data('room-id');
        auditSingleRoom(roomId);
    });
    
    // Close modal handler
    $('#close-modal').click(function() {
        $('#modal-content').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
        setTimeout(() => {
            $('#room-details-modal').addClass('hidden');
        }, 300);
    });
    
    // Close modal when clicking outside
    $('#room-details-modal').click(function(e) {
        if (e.target === this) {
            $('#modal-content').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
            setTimeout(() => {
                $(this).addClass('hidden');
            }, 300);
        }
    });
    
    // Audit rooms button
    $('#audit-rooms-btn').click(function() {
        startRoomAudit();
    });
    
    // Restock rooms button (Manager only)
    $('#restock-rooms-btn').click(function() {
        startRoomRestock();
    });
    
    // Housekeeping specific buttons
    $('#check-rooms-btn').click(function() {
        startRoomCheck();
    });
    
    $('#request-supplies-btn').click(function() {
        openRequestModal();
    });
    
    // Manager specific buttons
    $('#manage-items-btn').click(function() {
        window.location.href = 'items.php';
    });
    
    function loadRoomInventoryStats() {
        console.log('Loading room inventory stats...');
        const userRole = '<?php echo $user_role; ?>';
        const apiUrl = userRole === 'housekeeping' ? 'api/get-housekeeping-stats.php' : 'api/get-room-inventory-stats.php';
        
        $.ajax({
            url: apiUrl,
            method: 'GET',
            dataType: 'json',
            xhrFields: {
                withCredentials: true
            },
            success: function(response) {
                console.log('Room inventory stats response:', response);
                if (response.success) {
                    const stats = response.statistics;
                    if (userRole === 'housekeeping') {
                        $('#my-rooms').text(stats.my_rooms || 0);
                        $('#items-used').text(stats.items_used || 0);
                        $('#missing-items').text(stats.missing_items || 0);
                        $('#my-requests').text(stats.my_requests || 0);
                    } else {
                        $('#total-rooms').text(stats.total_rooms);
                        $('#fully-stocked').text(stats.fully_stocked);
                        $('#need-restocking').text(stats.need_restocking);
                        $('#critical-stock').text(stats.critical_stock);
                    }
                    console.log('Room inventory stats loaded successfully');
                } else {
                    console.error('Error loading room inventory stats:', response.message);
                    // Set error text based on role
                    if (userRole === 'housekeeping') {
                        $('#my-rooms').text('Error');
                        $('#items-used').text('Error');
                        $('#missing-items').text('Error');
                        $('#my-requests').text('Error');
                    } else {
                        $('#total-rooms').text('Error');
                        $('#fully-stocked').text('Error');
                        $('#need-restocking').text('Error');
                        $('#critical-stock').text('Error');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error loading room inventory stats:', error);
                console.error('Response:', xhr.responseText);
                // Set error text based on role
                if (userRole === 'housekeeping') {
                    $('#my-rooms').text('Error');
                    $('#items-used').text('Error');
                    $('#missing-items').text('Error');
                    $('#my-requests').text('Error');
                } else {
                    $('#total-rooms').text('Error');
                    $('#fully-stocked').text('Error');
                    $('#need-restocking').text('Error');
                    $('#critical-stock').text('Error');
                }
            }
        });
    }
    
    function loadFloors() {
        console.log('Loading floors...');
        $.ajax({
            url: 'api/get-hotel-floors.php',
            method: 'GET',
            dataType: 'json',
            xhrFields: {
                withCredentials: true
            },
            success: function(response) {
                console.log('Floors response:', response);
                if (response.success) {
                    const floorButtons = $('#floor-buttons');
                    floorButtons.empty();
                    
                    response.floors.forEach(function(floor, index) {
                        // Highlight floor 2 by default (since floor 1 has no rooms)
                        const isActive = floor.id === 2;
                        const buttonClass = isActive ? 'bg-blue-600 text-white shadow-lg' : 'bg-gray-200 text-gray-700 hover:bg-gray-300';
                        const button = `
                            <button class="floor-btn px-6 py-3 rounded-xl font-semibold transition-all duration-200 transform hover:scale-105 ${buttonClass}" data-floor-id="${floor.id}">
                                <i class="fas fa-layer-group mr-2"></i>
                                ${floor.floor_name || `Floor ${floor.floor_number}`}
                            </button>
                        `;
                        floorButtons.append(button);
                    });
                    console.log('Floors loaded successfully');
                } else {
                    console.error('Error loading floors:', response.message);
                    $('#floor-buttons').html('<div class="text-red-500">Error loading floors: ' + response.message + '</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error loading floors:', error);
                console.error('Response:', xhr.responseText);
                $('#floor-buttons').html('<div class="text-red-500">Error loading floors</div>');
            }
        });
    }
    
    function loadRoomsForFloor(floorId) {
        $.ajax({
            url: 'api/get-rooms-for-floor.php',
            method: 'GET',
            data: { floor_id: floorId },
            dataType: 'json',
            xhrFields: {
                withCredentials: true
            },
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
        
        if (rooms.length === 0) {
            grid.html(`
                <div class="col-span-full flex items-center justify-center py-12 text-gray-500">
                    <div class="text-center">
                        <i class="fas fa-bed text-4xl mb-4 text-gray-300"></i>
                        <p class="text-lg font-medium">No rooms found on this floor</p>
                        <p class="text-sm">Try selecting a different floor</p>
                    </div>
                </div>
            `);
            return;
        }
        
        rooms.forEach(function(room) {
            const statusClass = getStatusClass(room.status);
            const stockStatus = getStockStatus(room.stock_status);
            const stockIcon = getStockIcon(room.stock_status);
            
            const roomCard = `
                <div class="room-card bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 cursor-pointer transform hover:scale-105 border border-gray-200 group" data-room-id="${room.id}">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-xl font-bold text-gray-800 group-hover:text-blue-600 transition-colors">
                            <i class="fas fa-door-open mr-2"></i>Room ${room.room_number}
                        </h4>
                        <span class="px-3 py-1 text-xs font-bold rounded-full ${statusClass}">
                            ${room.status.charAt(0).toUpperCase() + room.status.slice(1)}
                        </span>
                    </div>
                    
                    <div class="space-y-3 mb-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Type:</span>
                            <span class="text-sm font-semibold text-gray-900 capitalize bg-gray-100 px-2 py-1 rounded">${room.room_type}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Stock Status:</span>
                            <span class="flex items-center font-semibold ${stockStatus.class}">
                                <i class="fas ${stockIcon} mr-1"></i>
                                ${stockStatus.text}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Items:</span>
                            <span class="text-sm font-bold text-blue-600 bg-blue-100 px-2 py-1 rounded-full">${room.total_items || 0}</span>
                        </div>
                    </div>
                    
                    <div class="pt-4 border-t border-gray-200">
                        <button class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 transform hover:scale-105 shadow-lg">
                            <i class="fas fa-eye mr-2"></i>View Details
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
    
    function getStockIcon(stockStatus) {
        switch(stockStatus) {
            case 'fully_stocked': return 'fa-check-circle';
            case 'needs_restocking': return 'fa-exclamation-triangle';
            case 'critical_stock': return 'fa-times-circle';
            default: return 'fa-question-circle';
        }
    }
    
    function showRoomDetails(roomId) {
        console.log('Loading room details for room ID:', roomId);
        $.ajax({
            url: 'api/get-room-details.php',
            method: 'GET',
            data: { room_id: roomId },
            dataType: 'json',
            xhrFields: {
                withCredentials: true
            },
            success: function(response) {
                console.log('Room details response:', response);
                if (response.success) {
                    displayRoomDetails(response.room);
                    $('#room-details-modal').removeClass('hidden');
                    // Add animation
                    setTimeout(() => {
                        $('#modal-content').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
                    }, 10);
                } else {
                    console.error('Error loading room details:', response.message);
                    alert('Error loading room details: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error loading room details:', error);
                console.error('Response:', xhr.responseText);
                alert('Error loading room details: ' + xhr.responseText);
            }
        });
    }
    
    function displayRoomDetails(room) {
        $('#modal-room-title').text(`Room ${room.room_number} - Inventory Details`);
        
        // Get status badge class
        const getStatusBadgeClass = (status) => {
            switch(status) {
                case 'available': return 'bg-green-100 text-green-800';
                case 'occupied': return 'bg-blue-100 text-blue-800';
                case 'maintenance': return 'bg-yellow-100 text-yellow-800';
                case 'out_of_service': return 'bg-red-100 text-red-800';
                default: return 'bg-gray-100 text-gray-800';
            }
        };
        
        // Get stock status info
        const getStockStatusInfo = (current, par) => {
            if (current === 0) return { text: 'Out of Stock', class: 'bg-red-100 text-red-800' };
            if (current < par) return { text: 'Low Stock', class: 'bg-yellow-100 text-yellow-800' };
            if (current >= par) return { text: 'In Stock', class: 'bg-green-100 text-green-800' };
            return { text: 'Unknown', class: 'bg-gray-100 text-gray-800' };
        };
        
        let content = `
            <!-- Room Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-6 rounded-xl text-white shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Room Number</p>
                            <p class="text-2xl font-bold">${room.room_number}</p>
                        </div>
                        <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                            <i class="fas fa-bed text-2xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-6 rounded-xl text-white shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium">Room Type</p>
                            <p class="text-xl font-bold capitalize">${room.room_type}</p>
                        </div>
                        <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                            <i class="fas fa-home text-2xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-green-500 to-green-600 p-6 rounded-xl text-white shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Status</p>
                            <p class="text-xl font-bold capitalize">${room.status}</p>
                        </div>
                        <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                            <i class="fas fa-check-circle text-2xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-orange-500 to-orange-600 p-6 rounded-xl text-white shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-100 text-sm font-medium">Max Occupancy</p>
                            <p class="text-2xl font-bold">${room.max_occupancy || 'N/A'}</p>
                        </div>
                        <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                            <i class="fas fa-users text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Room Details Section -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <div class="flex items-center mb-6">
                    <div class="bg-blue-100 p-3 rounded-lg mr-4">
                        <i class="fas fa-info-circle text-blue-600 text-xl"></i>
                    </div>
                    <h4 class="text-xl font-bold text-gray-800">Room Information</h4>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <span class="text-gray-600 font-medium">Room Type</span>
                            <span class="text-gray-900 font-semibold capitalize">${room.room_type}</span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <span class="text-gray-600 font-medium">Status</span>
                            <span class="px-3 py-1 rounded-full text-sm font-medium ${getStatusBadgeClass(room.status)}">${room.status}</span>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <span class="text-gray-600 font-medium">Max Occupancy</span>
                            <span class="text-gray-900 font-semibold">${room.max_occupancy || 'N/A'}</span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <span class="text-gray-600 font-medium">Last Audited</span>
                            <span class="text-gray-900 font-semibold">${room.last_audited ? new Date(room.last_audited).toLocaleDateString() : 'Never'}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Room Actions Section -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="bg-purple-100 p-3 rounded-lg mr-4">
                            <i class="fas fa-tasks text-purple-600 text-xl"></i>
                        </div>
                        <h4 class="text-xl font-bold text-gray-800">Room Actions</h4>
                    </div>
                    <div class="flex space-x-3">
                        <?php if ($is_housekeeping): ?>
                            <button id="check-room-btn" class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-6 py-3 rounded-xl font-semibold transition-all duration-200 transform hover:scale-105 shadow-lg" data-room-id="${room.id}">
                                <i class="fas fa-clipboard-check mr-2"></i>
                                Check Room
                            </button>
                            <button id="request-replacement-btn" class="bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white px-6 py-3 rounded-xl font-semibold transition-all duration-200 transform hover:scale-105 shadow-lg" data-room-id="${room.id}">
                                <i class="fas fa-shopping-cart mr-2"></i>
                                Request Items
                            </button>
                        <?php else: ?>
                            <button id="audit-this-room-btn" class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-6 py-3 rounded-xl font-semibold transition-all duration-200 transform hover:scale-105 shadow-lg" data-room-id="${room.id}">
                                <i class="fas fa-clipboard-check mr-2"></i>
                                Audit This Room
                            </button>
                            <button id="manage-room-items-btn" class="bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white px-6 py-3 rounded-xl font-semibold transition-all duration-200 transform hover:scale-105 shadow-lg" data-room-id="${room.id}">
                                <i class="fas fa-cog mr-2"></i>
                                Manage Items
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Inventory Items Section -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <div class="bg-green-100 p-3 rounded-lg mr-4">
                            <i class="fas fa-boxes text-green-600 text-xl"></i>
                        </div>
                        <h4 class="text-xl font-bold text-gray-800">Inventory Items</h4>
                    </div>
                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                        ${room.inventory_items ? room.inventory_items.length : 0} Items
                    </span>
                </div>

                <?php if ($is_manager): ?>
                    <!-- Assign Item (Manager only) -->
                    <div class="mb-6">
                        <button id="toggle-assign-form" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                            <i class="fas fa-plus-circle mr-1"></i>Add Item to Room
                        </button>
                        <div id="assign-item-panel" class="mt-3 hidden bg-gray-50 border border-gray-200 rounded p-4">
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                                <select id="assign-item-select" class="border border-gray-300 rounded px-2 py-2 md:col-span-2"></select>
                                <input id="assign-allocated" type="number" min="0" placeholder="Allocated" class="border border-gray-300 rounded px-2 py-2" />
                                <input id="assign-current" type="number" min="0" placeholder="Current" class="border border-gray-300 rounded px-2 py-2" />
                                <input id="assign-par" type="number" min="0" placeholder="Par" class="border border-gray-300 rounded px-2 py-2" />
                                <button id="assign-item-btn" data-room-id="${room.id}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded"><i class="fas fa-check mr-1"></i>Assign</button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                ${room.inventory_items && room.inventory_items.length > 0 ? `
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        ${room.inventory_items.map(item => {
                            const stockInfo = getStockStatusInfo(item.quantity_current, item.par_level);
                            return `
                                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 border border-gray-200 hover:shadow-lg transition-all duration-200">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex items-center">
                                            <div class="bg-blue-100 p-3 rounded-lg mr-3">
                                                <i class="fas fa-box text-blue-600"></i>
                                            </div>
                                            <div>
                                                <h5 class="font-bold text-gray-800 text-lg">${item.item_name}</h5>
                                                <p class="text-gray-500 text-sm">SKU: ${item.sku}</p>
                                            </div>
                                        </div>
                                        <span class="px-3 py-1 rounded-full text-xs font-medium ${stockInfo.class}">
                                            ${stockInfo.text}
                                        </span>
                                    </div>
                                    
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600 text-sm">Allocated:</span>
                                            <span class="font-semibold text-gray-800">${item.quantity_allocated} ${item.unit}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600 text-sm">Current:</span>
                                            <span class="font-semibold text-gray-800">${item.quantity_current} ${item.unit}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600 text-sm">Par Level:</span>
                                            <span class="font-semibold text-gray-800">${item.par_level} ${item.unit}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4 pt-4 border-t border-gray-200">
                                        <div class="flex justify-between items-center mb-3">
                                            <span class="text-gray-500 text-xs">Last Updated:</span>
                                            <span class="text-gray-500 text-xs">${item.last_updated ? new Date(item.last_updated).toLocaleDateString() : 'Never'}</span>
                                        </div>
                                        <?php if ($is_housekeeping): ?>
                                            <div class="flex space-x-2">
                                                <button onclick="updateItemStatus(${item.id}, 'used')" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs">
                                                    <i class="fas fa-check mr-1"></i>Used
                                                </button>
                                                <button onclick="updateItemStatus(${item.id}, 'missing')" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>Missing
                                                </button>
                                                <button onclick="updateItemStatus(${item.id}, 'damaged')" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">
                                                    <i class="fas fa-times mr-1"></i>Damaged
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <div class="flex space-x-2">
                                                <button onclick="editRoomItem(${item.id})" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs">
                                                    <i class="fas fa-edit mr-1"></i>Edit
                                                </button>
                                                <button onclick="removeRoomItem(${item.id})" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">
                                                    <i class="fas fa-trash mr-1"></i>Remove
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            `;
                        }).join('')}
                    </div>
                ` : `
                    <div class="text-center py-12">
                        <div class="bg-gray-100 p-6 rounded-full w-20 h-20 mx-auto mb-4 flex items-center justify-center">
                            <i class="fas fa-box-open text-gray-400 text-3xl"></i>
                        </div>
                        <h5 class="text-lg font-semibold text-gray-600 mb-2">No Inventory Items</h5>
                        <p class="text-gray-500 mb-4">This room doesn't have any inventory items assigned yet.</p>
                    </div>
                `}
            </div>
        `;
        
        $('#room-details-content').html(content);
        // Load items and bind handlers for assign panel
        if ($('#assign-item-select').length) {
            loadAssignableItems();
            $('#assign-item-btn').off('click').on('click', function() {
                const roomId = $(this).data('room-id');
                const itemId = $('#assign-item-select').val();
                const allocated = parseInt($('#assign-allocated').val() || '0', 10);
                const current = parseInt($('#assign-current').val() || '0', 10);
                const par = parseInt($('#assign-par').val() || '0', 10);
                if (!itemId) { alert('Please select an item'); return; }
                $.ajax({
                    url: 'api/assign-room-item.php',
                    method: 'POST',
                    dataType: 'json',
                    data: { room_id: roomId, item_id: itemId, allocated, current, par },
                    xhrFields: { withCredentials: true },
                    success: function(res){
                        if (res.success) {
                            alert('Item assigned successfully');
                            showRoomDetails(roomId);
                        } else {
                            alert('Error: ' + res.message);
                        }
                    },
                    error: function(xhr){
                        alert('Error: ' + xhr.responseText);
                    }
                });
            });
            $('#toggle-assign-form').off('click').on('click', function(){
                $('#assign-item-panel').toggleClass('hidden');
            });
        }
        
        // Add animation to modal
        setTimeout(() => {
            $('#modal-content').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
        }, 10);
    }

    function loadAssignableItems(){
        // Load a compact list of items for the select (schema-adaptive)
        $.ajax({
            url: 'api/list-items-simple.php',
            method: 'GET',
            dataType: 'json',
            success: function(resp){
                const sel = $('#assign-item-select');
                if (!sel.length) return;
                sel.empty();
                const items = resp.items || resp.data || [];
                sel.append('<option value="">Select Item</option>');
                items.slice(0, 200).forEach(function(it){
                    const id = it.id || it.item_id;
                    const name = it.label || it.item_name || it.name || ('Item #' + id);
                    sel.append(`<option value="${id}">${name}</option>`);
                });
            },
            error: function(){
                // Graceful fallback: minimal select
                const sel = $('#assign-item-select');
                if (sel.length) sel.html('<option value="">Unable to load items</option>');
            }
        });
    }
    
    function startRoomAudit() {
        if (confirm('Start room audit for all rooms? This will check inventory levels in all rooms.')) {
            $.ajax({
                url: 'api/start-room-audit.php',
                method: 'POST',
                dataType: 'json',
                xhrFields: {
                    withCredentials: true
                },
                success: function(response) {
                    console.log('Audit response:', response);
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
                    console.error('Response:', xhr.responseText);
                    alert('Error starting room audit: ' + xhr.responseText);
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
                xhrFields: {
                    withCredentials: true
                },
                success: function(response) {
                    console.log('Restock response:', response);
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
                    console.error('Response:', xhr.responseText);
                    alert('Error starting room restock: ' + xhr.responseText);
                }
            });
        }
    }
    
    function auditSingleRoom(roomId) {
        if (confirm('Audit this room\'s inventory? This will update the audit timestamp and log the activity.')) {
            // Show loading state
            const btn = $('#audit-this-room-btn');
            const originalText = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Auditing...').prop('disabled', true);
            
            $.ajax({
                url: 'api/audit-single-room.php',
                method: 'POST',
                data: { room_id: roomId },
                dataType: 'json',
                xhrFields: {
                    withCredentials: true
                },
                success: function(response) {
                    console.log('Single room audit response:', response);
                    if (response.success) {
                        alert(`Room ${response.room_number} audit completed successfully! ${response.items_audited} items audited.`);
                        // Refresh room details
                        showRoomDetails(roomId);
                        // Refresh statistics and room list
                        loadRoomInventoryStats();
                        loadRoomsForFloor(currentFloor);
                    } else {
                        alert('Error auditing room: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error auditing single room:', error);
                    console.error('Response:', xhr.responseText);
                    alert('Error auditing room: ' + xhr.responseText);
                },
                complete: function() {
                    // Restore button state
                    btn.html(originalText).prop('disabled', false);
                }
            });
        }
    }
    
    // Housekeeping Functions
    function startRoomCheck() {
        if (confirm('Start room check for your assigned rooms? This will help you track inventory status.')) {
            $.ajax({
                url: 'api/start-room-check.php',
                method: 'POST',
                dataType: 'json',
                xhrFields: {
                    withCredentials: true
                },
                success: function(response) {
                    console.log('Room check response:', response);
                    if (response.success) {
                        alert('Room check started successfully!');
                        loadRoomInventoryStats();
                        loadRoomsForFloor(currentFloor);
                    } else {
                        alert('Error starting room check: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error starting room check:', error);
                    alert('Error starting room check: ' + xhr.responseText);
                }
            });
        }
    }
    
    function openRequestModal() {
        // Create a simple request modal
        const modal = `
            <div id="request-modal" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-xl font-bold text-gray-800">Request Supplies</h3>
                    </div>
                    <div class="p-6">
                        <form id="request-form">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Item</label>
                                <select id="request-item" class="w-full border border-gray-300 rounded px-3 py-2" required>
                                    <option value="">Select Item</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                                <input type="number" id="request-quantity" class="w-full border border-gray-300 rounded px-3 py-2" min="1" required>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Room Number</label>
                                <input type="text" id="request-room" class="w-full border border-gray-300 rounded px-3 py-2" required>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                                <select id="request-reason" class="w-full border border-gray-300 rounded px-3 py-2" required>
                                    <option value="">Select Reason</option>
                                    <option value="missing">Missing</option>
                                    <option value="damaged">Damaged</option>
                                    <option value="low_stock">Low Stock</option>
                                    <option value="replacement">Replacement</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                <textarea id="request-notes" class="w-full border border-gray-300 rounded px-3 py-2" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="p-6 border-t border-gray-200 flex justify-end space-x-3">
                        <button id="cancel-request" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
                        <button id="submit-request" class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">Submit Request</button>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(modal);
        
        // Load items for request
        loadRequestItems();
        
        // Handle form submission
        $('#submit-request').click(function() {
            submitRequest();
        });
        
        // Handle cancel
        $('#cancel-request').click(function() {
            $('#request-modal').remove();
        });
        
        // Close on outside click
        $('#request-modal').click(function(e) {
            if (e.target === this) {
                $(this).remove();
            }
        });
    }
    
    function loadRequestItems() {
        $.ajax({
            url: 'api/list-items-simple.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                const select = $('#request-item');
                select.empty();
                select.append('<option value="">Select Item</option>');
                
                if (response.items || response.data) {
                    const items = response.items || response.data;
                    items.forEach(function(item) {
                        const id = item.id || item.item_id;
                        const name = item.label || item.item_name || item.name || ('Item #' + id);
                        select.append(`<option value="${id}">${name}</option>`);
                    });
                }
            },
            error: function() {
                $('#request-item').html('<option value="">Unable to load items</option>');
            }
        });
    }
    
    function submitRequest() {
        const itemId = $('#request-item').val();
        const quantity = $('#request-quantity').val();
        const room = $('#request-room').val();
        const reason = $('#request-reason').val();
        const notes = $('#request-notes').val();
        
        if (!itemId || !quantity || !room || !reason) {
            alert('Please fill in all required fields');
            return;
        }
        
        $.ajax({
            url: 'api/create-supply-request.php',
            method: 'POST',
            dataType: 'json',
            data: {
                item_id: itemId,
                quantity: quantity,
                room_number: room,
                reason: reason,
                notes: notes
            },
            xhrFields: {
                withCredentials: true
            },
            success: function(response) {
                if (response.success) {
                    alert('Request submitted successfully!');
                    $('#request-modal').remove();
                    loadRoomInventoryStats();
                } else {
                    alert('Error submitting request: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error submitting request: ' + xhr.responseText);
            }
        });
    }
    
    // Item Status Update Functions (Housekeeping)
    function updateItemStatus(itemId, status) {
        if (confirm(`Mark this item as ${status}?`)) {
            $.ajax({
                url: 'api/update-item-status.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    item_id: itemId,
                    status: status
                },
                xhrFields: {
                    withCredentials: true
                },
                success: function(response) {
                    if (response.success) {
                        alert(`Item marked as ${status} successfully!`);
                        // Refresh the room details
                        const roomId = $('.room-card.active').data('room-id');
                        if (roomId) {
                            showRoomDetails(roomId);
                        }
                        loadRoomInventoryStats();
                    } else {
                        alert('Error updating item status: ' + response.message);
                    }
                },
                error: function(xhr) {
                    alert('Error updating item status: ' + xhr.responseText);
                }
            });
        }
    }
    
    // Manager Functions
    function editRoomItem(itemId) {
        // Open edit modal for room item
        alert('Edit room item functionality - Item ID: ' + itemId);
    }
    
    function removeRoomItem(itemId) {
        if (confirm('Remove this item from the room? This action cannot be undone.')) {
            $.ajax({
                url: 'api/remove-room-item.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    item_id: itemId
                },
                xhrFields: {
                    withCredentials: true
                },
                success: function(response) {
                    if (response.success) {
                        alert('Item removed from room successfully!');
                        // Refresh the room details
                        const roomId = $('.room-card.active').data('room-id');
                        if (roomId) {
                            showRoomDetails(roomId);
                        }
                        loadRoomInventoryStats();
                    } else {
                        alert('Error removing item: ' + response.message);
                    }
                },
                error: function(xhr) {
                    alert('Error removing item: ' + xhr.responseText);
                }
            });
        }
    }
});
</script>
