<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
require_once '../../includes/session-config.php';
session_start();
require_once dirname(__DIR__, 2) . '/../includes/database.php';
require_once '../../includes/functions.php';
// Check if user is logged in and has front desk access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['front_desk', 'manager'])) {
    header('Location: ../../login.php');
    exit();
}

// Get room status data
$room_status_overview = getRoomStatusOverview();
$all_rooms = getAllRoomsWithStatus();
$room_status_options = getRoomStatusOptions();

// Set page title
$page_title = 'Room Status';

// Include unified header and sidebar
include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';
?>

        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-semibold text-gray-800">Room Status Overview</h2>
                <div class="text-right">
                    <div id="current-date" class="text-sm text-gray-600"></div>
                    <div id="current-time" class="text-sm text-gray-600"></div>
                </div>
            </div>

            <!-- Room Status Overview -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h3 class="text-xl font-semibold text-gray-800 mb-6">Room Status Summary</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); foreach ($room_status_overview as $overview): ?>
                        <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-lg font-semibold text-blue-800"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($overview['room_type']); ?></h4>
                                <span class="text-2xl font-bold text-blue-600"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $overview['total']; ?></span>
                            </div>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-green-600">Available:</span>
                                    <span class="font-medium"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $overview['available']; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-red-600">Occupied:</span>
                                    <span class="font-medium"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $overview['occupied']; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-yellow-600">Reserved:</span>
                                    <span class="font-medium"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $overview['reserved']; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-orange-600">Maintenance:</span>
                                    <span class="font-medium"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $overview['maintenance']; ?></span>
                                </div>
                            </div>
                        </div>
                    <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endforeach; ?>
                </div>
            </div>

            <!-- Room List -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-semibold text-gray-800">All Rooms</h3>
                        <div class="flex space-x-2">
                            <select id="status-filter" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Status</option>
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); foreach ($room_status_options as $key => $value): ?>
                                    <option value="<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $key; ?>"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $value; ?></option>
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endforeach; ?>
                            </select>
                            <input type="text" id="search-room" placeholder="Search rooms..." 
                                   class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Guest</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-out</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="rooms-table-body">
                            <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); foreach ($all_rooms as $room): ?>
                                <tr class="hover:bg-gray-50" data-room-id="<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $room['id']; ?>">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($room['room_number']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($room['room_type']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo getStatusBadgeClass($room['status']); ?>">
                                            <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo getStatusLabel($room['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900" id="guest-<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $room['id']; ?>">
                                            <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo getCurrentGuest($room['id']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" id="checkin-<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $room['id']; ?>">
                                        <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo getCheckInDate($room['id']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" id="checkout-<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $room['id']; ?>">
                                        <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo getCheckOutDate($room['id']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ₱<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format($room['rate'], 2); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button onclick="viewRoomDetails(<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $room['id']; ?>)" 
                                                    class="text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); if ($room['status'] === 'available'): ?>
                                                <button onclick="assignRoom(<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $room['id']; ?>)" 
                                                        class="text-green-600 hover:text-green-900">
                                                    <i class="fas fa-user-plus"></i>
                                                </button>
                                            <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endif; ?>
                                            <button onclick="createMaintenanceRequest(<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $room['id']; ?>)" 
                                                    class="text-orange-600 hover:text-orange-900">
                                                <i class="fas fa-tools"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Room Details Modal -->
    <div id="fd-room-details-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-8 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Room Details</h3>
                <button onclick="closeFdRoomDetailsModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="fd-room-details-content"></div>
        </div>
    </div>

    <script src="../../assets/js/main.js"></script>
    <script src="../../assets/js/front-desk-room-status.js?v=1"></script>
    
    <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); include '../../includes/footer.php'; ?>

<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
// Helper functions are now in functions.php
?>
