<?php
session_start();
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);


/**
 * Room Status Management
 * Hotel PMS Training System for Students
 */

session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and has access (manager or front_desk only)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['manager', 'front_desk'])) {
    header('Location: ../../login.php');
    exit();
}

// Load room status data directly in PHP
try {
    // Get room status overview - count by actual room status, not housekeeping status
    $stmt = $pdo->query("
        SELECT 
            status,
            COUNT(*) as count
        FROM rooms 
        WHERE status IS NOT NULL
        GROUP BY status
        ORDER BY status
    ");
    $statusData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Initialize counts
    $availableCount = 0;
    $occupiedCount = 0;
    $maintenanceCount = 0;
    $cleaningCount = 0;
    
    foreach ($statusData as $status) {
        switch($status['status']) {
            case 'available':
                $availableCount = $status['count'];
                break;
            case 'occupied':
                $occupiedCount = $status['count'];
                break;
            case 'maintenance':
                $maintenanceCount = $status['count'];
                break;
            case 'cleaning':
                $cleaningCount = $status['count'];
                break;
        }
    }
    
    // Get all rooms (including capacity)
    $stmt = $pdo->query("
        SELECT 
            id,
            room_number,
            room_type,
            floor,
            capacity,
            rate,
            status,
            housekeeping_status,
            amenities
        FROM rooms 
        ORDER BY room_number ASC
    ");
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log('Error loading room status data: ' . $e->getMessage());
    $availableCount = $occupiedCount = $maintenanceCount = $cleaningCount = 0;
    $rooms = [];
}

// Set page title
$page_title = 'Room Status Management';

// Include header
include '../../includes/header-unified.php';
// Include sidebar
include '../../includes/sidebar-unified.php';
?>

        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Room Status Management</h2>
                <div class="flex items-center space-x-4">
                    <button onclick="testFunctions()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-test-tube mr-2"></i>Test
                    </button>
                    <button onclick="refreshRoomStatus()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-sync-alt mr-2"></i>Refresh
                    </button>
                </div>
            </div>

            <!-- Room Status Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-check-circle text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Available</p>
                            <p class="text-2xl font-semibold text-gray-900" id="available-count"><?php
session_start(); echo $availableCount; ?></p>
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
                            <p class="text-sm font-medium text-gray-500">Occupied</p>
                            <p class="text-2xl font-semibold text-gray-900" id="occupied-count"><?php
session_start(); echo $occupiedCount; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-tools text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Maintenance</p>
                            <p class="text-2xl font-semibold text-gray-900" id="maintenance-count"><?php
session_start(); echo $maintenanceCount; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-broom text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Cleaning</p>
                            <p class="text-2xl font-semibold text-gray-900" id="cleaning-count"><?php
session_start(); echo $cleaningCount; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- All Rooms -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">All Rooms</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Housekeeping</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="rooms-table-body">
                            <?php
session_start(); if (!empty($rooms)): ?>
                                <?php
session_start(); foreach ($rooms as $room): ?>
                                    <?php
session_start();
                                    $statusClass = '';
                                    switch($room['status']) {
                                        case 'available':
                                            $statusClass = 'bg-green-100 text-green-800';
                                            break;
                                        case 'occupied':
                                            $statusClass = 'bg-red-100 text-red-800';
                                            break;
                                        case 'maintenance':
                                            $statusClass = 'bg-yellow-100 text-yellow-800';
                                            break;
                                        default:
                                            $statusClass = 'bg-gray-100 text-gray-800';
                                    }
                                    
                                    $housekeepingClass = '';
                                    switch($room['housekeeping_status']) {
                                        case 'clean':
                                            $housekeepingClass = 'bg-green-100 text-green-800';
                                            break;
                                        case 'dirty':
                                            $housekeepingClass = 'bg-red-100 text-red-800';
                                            break;
                                        case 'maintenance':
                                            $housekeepingClass = 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'cleaning':
                                            $housekeepingClass = 'bg-blue-100 text-blue-800';
                                            break;
                                        default:
                                            $housekeepingClass = 'bg-gray-100 text-gray-800';
                                    }
                                    ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center">
                                                        <i class="fas fa-bed text-white"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">Room <?php
session_start(); echo htmlspecialchars($room['room_number']); ?></div>
                                                    <div class="text-sm text-gray-500">Floor <?php
session_start(); echo htmlspecialchars($room['floor']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php
session_start(); echo htmlspecialchars($room['room_type']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php
session_start(); echo $statusClass; ?>">
                                                <?php
session_start(); echo htmlspecialchars($room['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php
session_start(); echo $housekeepingClass; ?>">
                                                <?php
session_start(); echo htmlspecialchars($room['housekeeping_status'] ?? 'Unknown'); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center space-x-2">
                                                <!-- View Icon with Enhanced Design -->
                                                <button onclick="showRoomModal(<?php
session_start(); echo $room['id']; ?>, '<?php
session_start(); echo htmlspecialchars($room['room_number']); ?>', '<?php
session_start(); echo htmlspecialchars($room['floor']); ?>', '<?php
session_start(); echo htmlspecialchars($room['room_type']); ?>', '<?php
session_start(); echo htmlspecialchars($room['status']); ?>', '<?php
session_start(); echo htmlspecialchars($room['housekeeping_status']); ?>', <?php
session_start(); echo $room['capacity'] ?? 2; ?>)" 
                                                        class="group relative inline-flex items-center justify-center w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition-all duration-200 ease-in-out cursor-pointer"
                                                        title="View Room Details">
                                                    <i class="fas fa-eye text-sm group-hover:scale-110 transition-transform duration-200"></i>
                                                    <div class="absolute -top-2 -right-2 w-3 h-3 bg-blue-400 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                                                </button>
                                                
                                                <!-- Edit Icon with Enhanced Design -->
                                                <button onclick="editRoomStatus(<?php
session_start(); echo $room['id']; ?>, '<?php
session_start(); echo htmlspecialchars($room['room_number']); ?>', '<?php
session_start(); echo htmlspecialchars($room['housekeeping_status']); ?>', <?php
session_start(); echo $room['capacity'] ?? 2; ?>)" 
                                                        class="group relative inline-flex items-center justify-center w-10 h-10 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition-all duration-200 ease-in-out cursor-pointer"
                                                        title="Edit Room Status">
                                                    <i class="fas fa-edit text-sm group-hover:scale-110 transition-transform duration-200"></i>
                                                    <div class="absolute -top-2 -right-2 w-3 h-3 bg-emerald-400 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php
session_start(); endforeach; ?>
                            <?php
session_start(); else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-bed text-4xl mb-4"></i>
                                            <p class="text-lg font-medium">No rooms found</p>
                                            <p class="text-sm">Rooms will appear here when available</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php
session_start(); endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Room Details Modal -->
        <div id="room-details-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Room Details</h3>
                        <button onclick="closeRoomDetailsModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div id="room-details-content">
                        <!-- Room details will be loaded here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Scripts -->
        <script src="../../assets/js/main.js?v=<?php
session_start(); echo time(); ?>"></script>
        <script>
            // Simple working functions for room status
            function showRoomModal(roomId, roomNumber, floor, roomType, status, housekeepingStatus, capacity) {
                try {
                    console.log('Showing room modal for:', roomId);
                    
                    // Create beautiful modal HTML
                    const modalHtml = `
                        <div id="room-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 animate-fadeIn">
                            <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto transform transition-all duration-300 scale-100">
                                <!-- Header with Gradient Background -->
                                <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white p-6 rounded-t-2xl">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                                <i class="fas fa-bed text-2xl"></i>
                                            </div>
                                            <div>
                                                <h3 class="text-2xl font-bold">Room ${roomNumber}</h3>
                                                <p class="text-blue-100 text-sm">Floor ${floor} • ${roomType}</p>
                                            </div>
                                        </div>
                                        <button onclick="closeRoomModal()" class="w-8 h-8 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110">
                                            <i class="fas fa-times text-lg"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Content -->
                                <div class="p-6">
                                    <!-- Status Cards -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                        <div class="bg-gray-50 rounded-xl p-4 border-l-4 ${status === 'available' ? 'border-green-500' : 'border-red-500'}">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-10 h-10 ${status === 'available' ? 'bg-green-100' : 'bg-red-100'} rounded-full flex items-center justify-center">
                                                    <i class="fas ${status === 'available' ? 'fa-check-circle text-green-600' : 'fa-times-circle text-red-600'}"></i>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-600">Room Status</p>
                                                    <p class="text-lg font-semibold ${status === 'available' ? 'text-green-700' : 'text-red-700'}">${status.charAt(0).toUpperCase() + status.slice(1)}</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="bg-gray-50 rounded-xl p-4 border-l-4 ${housekeepingStatus === 'clean' ? 'border-green-500' : housekeepingStatus === 'dirty' ? 'border-red-500' : 'border-yellow-500'}">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-10 h-10 ${housekeepingStatus === 'clean' ? 'bg-green-100' : housekeepingStatus === 'dirty' ? 'bg-red-100' : 'bg-yellow-100'} rounded-full flex items-center justify-center">
                                                    <i class="fas ${housekeepingStatus === 'clean' ? 'fa-broom text-green-600' : housekeepingStatus === 'dirty' ? 'fa-exclamation-triangle text-red-600' : 'fa-tools text-yellow-600'}"></i>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-600">Housekeeping</p>
                                                    <p class="text-lg font-semibold ${housekeepingStatus === 'clean' ? 'text-green-700' : housekeepingStatus === 'dirty' ? 'text-red-700' : 'text-yellow-700'}">${housekeepingStatus.charAt(0).toUpperCase() + housekeepingStatus.slice(1)}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Room Details -->
                                    <div class="space-y-4">
                                        <h4 class="text-lg font-semibold text-gray-800 flex items-center">
                                            <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                            Room Information
                                        </h4>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="bg-blue-50 rounded-lg p-4">
                                                <div class="flex items-center space-x-2 mb-2">
                                                    <i class="fas fa-hashtag text-blue-600"></i>
                                                    <span class="font-medium text-gray-700">Room Number</span>
                                                </div>
                                                <p class="text-xl font-bold text-blue-800">${roomNumber}</p>
                                            </div>
                                            
                                            <div class="bg-purple-50 rounded-lg p-4">
                                                <div class="flex items-center space-x-2 mb-2">
                                                    <i class="fas fa-layer-group text-purple-600"></i>
                                                    <span class="font-medium text-gray-700">Floor</span>
                                                </div>
                                                <p class="text-xl font-bold text-purple-800">${floor}</p>
                                            </div>
                                            
                                            <div class="bg-indigo-50 rounded-lg p-4">
                                                <div class="flex items-center space-x-2 mb-2">
                                                    <i class="fas fa-home text-indigo-600"></i>
                                                    <span class="font-medium text-gray-700">Room Type</span>
                                                </div>
                                                <p class="text-xl font-bold text-indigo-800">${roomType.charAt(0).toUpperCase() + roomType.slice(1)}</p>
                                            </div>
                                            
                                            <div class="bg-emerald-50 rounded-lg p-4">
                                                <div class="flex items-center space-x-2 mb-2">
                                                    <i class="fas fa-users text-emerald-600"></i>
                                                    <span class="font-medium text-gray-700">Capacity</span>
                                                </div>
                                                <p class="text-xl font-bold text-emerald-800">${capacity || 2} ${(capacity || 2) === 1 ? 'Guest' : 'Guests'}</p>
                                            </div>
                                        </div>
                                        
                                        <!-- Amenities -->
                                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg p-4">
                                            <div class="flex items-center space-x-2 mb-3">
                                                <i class="fas fa-star text-yellow-500"></i>
                                                <span class="font-medium text-gray-700">Amenities</span>
                                            </div>
                                            <div class="flex flex-wrap gap-2">
                                                <span class="px-3 py-1 bg-white rounded-full text-sm font-medium text-gray-700 shadow-sm">WiFi</span>
                                                <span class="px-3 py-1 bg-white rounded-full text-sm font-medium text-gray-700 shadow-sm">Flat-screen TV</span>
                                                <span class="px-3 py-1 bg-white rounded-full text-sm font-medium text-gray-700 shadow-sm">Air Conditioning</span>
                                                <span class="px-3 py-1 bg-white rounded-full text-sm font-medium text-gray-700 shadow-sm">Mini Fridge</span>
                                                <span class="px-3 py-1 bg-white rounded-full text-sm font-medium text-gray-700 shadow-sm">Coffee Maker</span>
                                                <span class="px-3 py-1 bg-white rounded-full text-sm font-medium text-gray-700 shadow-sm">Safe</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Footer -->
                                <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end space-x-3">
                                    <button onclick="closeRoomModal()" class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium transition-all duration-200 hover:scale-105">
                                        <i class="fas fa-times mr-2"></i>Close
                                    </button>
                                    <button onclick="editRoomStatus(${roomId}, '${roomNumber}', '${housekeepingStatus}', ${capacity}); closeRoomModal();" class="px-6 py-2 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white rounded-lg font-medium transition-all duration-200 hover:scale-105">
                                        <i class="fas fa-edit mr-2"></i>Edit Status
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Add modal to page
                    document.body.insertAdjacentHTML('beforeend', modalHtml);
                    
                } catch (error) {
                    console.error('Error showing room modal:', error);
                    alert('Error showing room details: ' + error.message);
                }
            }
            
            function closeRoomModal() {
                const modal = document.getElementById('room-modal');
                if (modal) {
                    modal.remove();
                }
            }
            
            function editRoomStatus(roomId, roomNumber, currentStatus, capacity) {
                try {
                    console.log('Editing room status for:', roomId);
                    
                    // Create beautiful status selection modal
                    const statusModalHtml = `
                        <div id="status-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all duration-300 scale-100">
                                <!-- Header -->
                                <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 text-white p-6 rounded-t-2xl">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                            <i class="fas fa-edit text-xl"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-bold">Update Room Status</h3>
                                            <p class="text-emerald-100 text-sm">Room ${roomNumber}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Content -->
                                <div class="p-6">
                                    <div class="mb-4">
                                        <p class="text-gray-600 mb-4">Current Status: <span class="font-semibold text-gray-800">${currentStatus.charAt(0).toUpperCase() + currentStatus.slice(1)}</span></p>
                                        <p class="text-sm text-gray-500">Select new housekeeping status:</p>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-3">
                                        <button onclick="selectStatus('clean')" class="status-option p-4 rounded-xl border-2 border-gray-200 hover:border-green-500 hover:bg-green-50 transition-all duration-200 text-left group">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-8 h-8 bg-green-100 group-hover:bg-green-200 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-broom text-green-600"></i>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-gray-800">Clean</p>
                                                    <p class="text-xs text-gray-500">Room is clean</p>
                                                </div>
                                            </div>
                                        </button>
                                        
                                        <button onclick="selectStatus('dirty')" class="status-option p-4 rounded-xl border-2 border-gray-200 hover:border-red-500 hover:bg-red-50 transition-all duration-200 text-left group">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-8 h-8 bg-red-100 group-hover:bg-red-200 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-gray-800">Dirty</p>
                                                    <p class="text-xs text-gray-500">Needs cleaning</p>
                                                </div>
                                            </div>
                                        </button>
                                        
                                        <button onclick="selectStatus('maintenance')" class="status-option p-4 rounded-xl border-2 border-gray-200 hover:border-yellow-500 hover:bg-yellow-50 transition-all duration-200 text-left group">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-8 h-8 bg-yellow-100 group-hover:bg-yellow-200 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-tools text-yellow-600"></i>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-gray-800">Maintenance</p>
                                                    <p class="text-xs text-gray-500">Under repair</p>
                                                </div>
                                            </div>
                                        </button>
                                        
                                        <button onclick="selectStatus('cleaning')" class="status-option p-4 rounded-xl border-2 border-gray-200 hover:border-blue-500 hover:bg-blue-50 transition-all duration-200 text-left group">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-8 h-8 bg-blue-100 group-hover:bg-blue-200 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-spray-can text-blue-600"></i>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-gray-800">Cleaning</p>
                                                    <p class="text-xs text-gray-500">Being cleaned</p>
                                                </div>
                                            </div>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Footer -->
                                <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end space-x-3">
                                    <button onclick="closeStatusModal()" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium transition-all duration-200">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Add modal to page
                    document.body.insertAdjacentHTML('beforeend', statusModalHtml);
                    
                    // Store room data for later use
                    window.currentEditRoom = { id: roomId, number: roomNumber, currentStatus: currentStatus };
                    
                } catch (error) {
                    console.error('Error editing room status:', error);
                    alert('Error updating room status: ' + error.message);
                }
            }
            
            function selectStatus(newStatus) {
                try {
                    const room = window.currentEditRoom;
                    if (!room) return;
                    
                    // Find the room row and update it
                    const buttons = document.querySelectorAll('button[onclick*="editRoomStatus"]');
                    for (let button of buttons) {
                        if (button.onclick.toString().includes(room.id)) {
                            const row = button.closest('tr');
                            const statusCell = row.cells[3];
                            const statusSpan = statusCell.querySelector('span');
                            
                            // Update text
                            statusSpan.textContent = newStatus.toLowerCase();
                            
                            // Update color classes
                            statusSpan.className = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full';
                            switch(newStatus.toLowerCase()) {
                                case 'clean':
                                    statusSpan.classList.add('bg-green-100', 'text-green-800');
                                    break;
                                case 'dirty':
                                    statusSpan.classList.add('bg-red-100', 'text-red-800');
                                    break;
                                case 'maintenance':
                                    statusSpan.classList.add('bg-yellow-100', 'text-yellow-800');
                                    break;
                                case 'cleaning':
                                    statusSpan.classList.add('bg-blue-100', 'text-blue-800');
                                    break;
                            }
                            
                            // Update the onclick attribute for future edits
                            button.setAttribute('onclick', `editRoomStatus(${room.id}, '${room.number}', '${newStatus.toLowerCase()}')`);
                            
                            break;
                        }
                    }
                    
                    // Update counts
                    updateRoomCounts();
                    
                    // Close modal
                    closeStatusModal();
                    
                    // Show success notification
                    showSuccessNotification(`Room ${room.number} status updated to ${newStatus}!`);
                    
                } catch (error) {
                    console.error('Error selecting status:', error);
                    alert('Error updating room status: ' + error.message);
                }
            }
            
            function closeStatusModal() {
                const modal = document.getElementById('status-modal');
                if (modal) {
                    modal.remove();
                }
                window.currentEditRoom = null;
            }
            
            function showSuccessNotification(message) {
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full';
                notification.innerHTML = `
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-check-circle"></i>
                        <span>${message}</span>
                    </div>
                `;
                
                document.body.appendChild(notification);
                
                // Animate in
                setTimeout(() => {
                    notification.classList.remove('translate-x-full');
                }, 100);
                
                // Auto remove
                setTimeout(() => {
                    notification.classList.add('translate-x-full');
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                }, 3000);
            }
            
            function updateRoomCounts() {
                const rows = document.querySelectorAll('#rooms-table-body tr');
                let availableCount = 0, occupiedCount = 0, maintenanceCount = 0, cleaningCount = 0;
                
                rows.forEach(row => {
                    const status = row.cells[2].textContent.trim(); // Column 2 is room status, not housekeeping status
                    switch(status) {
                        case 'available': availableCount++; break;
                        case 'occupied': occupiedCount++; break;
                        case 'maintenance': maintenanceCount++; break;
                        case 'cleaning': cleaningCount++; break;
                    }
                });
                
                // Update the count displays
                const availableCountEl = document.getElementById('available-count');
                const occupiedCountEl = document.getElementById('occupied-count');
                const maintenanceCountEl = document.getElementById('maintenance-count');
                const cleaningCountEl = document.getElementById('cleaning-count');
                
                if (availableCountEl) availableCountEl.textContent = availableCount;
                if (occupiedCountEl) occupiedCountEl.textContent = occupiedCount;
                if (maintenanceCountEl) maintenanceCountEl.textContent = maintenanceCount;
                if (cleaningCountEl) cleaningCountEl.textContent = cleaningCount;
            }
            
            function refreshRoomStatus() {
                window.location.reload();
            }
            
            function testFunctions() {
                alert('JavaScript is working perfectly!\n\nFunctions available:\n- showRoomModal\n- editRoomStatus\n- refreshRoomStatus');
            }
            
            // Test on page load
            document.addEventListener('DOMContentLoaded', function() {
                console.log('Room status page loaded successfully');
                console.log('All functions are ready:', {
                    showRoomModal: typeof showRoomModal,
                    editRoomStatus: typeof editRoomStatus,
                    refreshRoomStatus: typeof refreshRoomStatus
                });
            });
        </script>
    </body>
</html>
