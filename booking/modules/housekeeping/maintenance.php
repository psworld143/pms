<?php
session_start();
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);


/**
 * Maintenance Management
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

// Get maintenance statistics
try {
    // Active maintenance requests (reported, assigned, in_progress)
    $stmt = $pdo->query("SELECT COUNT(*) as active FROM maintenance_requests WHERE status IN ('reported', 'assigned', 'in_progress')");
    $activeRequests = $stmt->fetch()['active'];
    
    // Completed today
    $stmt = $pdo->query("SELECT COUNT(*) as completed_today FROM maintenance_requests WHERE status = 'completed' AND DATE(updated_at) = CURDATE()");
    $completedToday = $stmt->fetch()['completed_today'];
    
    // Pending approval (reported status)
    $stmt = $pdo->query("SELECT COUNT(*) as pending FROM maintenance_requests WHERE status = 'reported'");
    $pendingApproval = $stmt->fetch()['pending'];
    
    // Total cost (sum of estimated costs for all requests)
    $stmt = $pdo->query("SELECT SUM(COALESCE(estimated_cost, 0)) as total_cost FROM maintenance_requests");
    $totalCost = $stmt->fetch()['total_cost'] ?? 0;
    
    // Get all maintenance requests
    $stmt = $pdo->query("
        SELECT 
            mr.*,
            r.room_number,
            u.name as reported_by_name
        FROM maintenance_requests mr
        LEFT JOIN rooms r ON mr.room_id = r.id
        LEFT JOIN users u ON mr.reported_by = u.id
        ORDER BY mr.created_at DESC
    ");
    $maintenanceRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log('Error loading maintenance data: ' . $e->getMessage());
    $activeRequests = $completedToday = $pendingApproval = $totalCost = 0;
    $maintenanceRequests = [];
}

// Set page title
$page_title = 'Maintenance Management';

// Include header
include '../../includes/header-unified.php';
// Include sidebar
include '../../includes/sidebar-unified.php';
?>

        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Maintenance Management</h2>
                <div class="flex items-center space-x-4">
                    <button onclick="showNewMaintenanceModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-plus mr-2"></i>New Request
                    </button>
                </div>
            </div>

            <!-- Maintenance Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-tools text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Active Requests</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php
session_start(); echo $activeRequests; ?></p>
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
                            <p class="text-sm font-medium text-gray-500">Completed Today</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php
session_start(); echo $completedToday; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Pending Approval</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php
session_start(); echo $pendingApproval; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-peso-sign text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Cost</p>
                            <p class="text-2xl font-semibold text-gray-900">₱<?php
session_start(); echo number_format($totalCost, 2); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Maintenance Requests -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Recent Maintenance Requests</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reported By</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php
session_start(); if (!empty($maintenanceRequests)): ?>
                                <?php
session_start(); foreach ($maintenanceRequests as $request): 
                                    $priorityClass = '';
                                    $priorityText = '';
                                    switch($request['priority']) {
                                        case 'urgent':
                                            $priorityClass = 'bg-red-100 text-red-800';
                                            $priorityText = 'Urgent';
                                            break;
                                        case 'high':
                                            $priorityClass = 'bg-orange-100 text-orange-800';
                                            $priorityText = 'High';
                                            break;
                                        case 'medium':
                                            $priorityClass = 'bg-yellow-100 text-yellow-800';
                                            $priorityText = 'Medium';
                                            break;
                                        case 'low':
                                        default:
                                            $priorityClass = 'bg-blue-100 text-blue-800';
                                            $priorityText = 'Low';
                                            break;
                                    }
                                    
                                    $statusClass = '';
                                    $statusText = '';
                                    switch($request['status']) {
                                        case 'completed':
                                            $statusClass = 'bg-green-100 text-green-800';
                                            $statusText = 'Completed';
                                            break;
                                        case 'verified':
                                            $statusClass = 'bg-blue-100 text-blue-800';
                                            $statusText = 'Verified';
                                            break;
                                        case 'in_progress':
                                            $statusClass = 'bg-yellow-100 text-yellow-800';
                                            $statusText = 'In Progress';
                                            break;
                                        case 'assigned':
                                            $statusClass = 'bg-purple-100 text-purple-800';
                                            $statusText = 'Assigned';
                                            break;
                                        case 'reported':
                                        default:
                                            $statusClass = 'bg-gray-100 text-gray-800';
                                            $statusText = 'Reported';
                                            break;
                                    }
                                ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php
session_start(); echo htmlspecialchars($request['issue_type']); ?></div>
                                        <div class="text-sm text-gray-500"><?php
session_start(); echo htmlspecialchars(substr($request['description'], 0, 50)) . (strlen($request['description']) > 50 ? '...' : ''); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php
session_start(); echo htmlspecialchars($request['room_number'] ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php
session_start(); echo htmlspecialchars($request['reported_by_name'] ?? 'Unknown'); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php
session_start(); echo $priorityClass; ?>">
                                            <?php
session_start(); echo $priorityText; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php
session_start(); echo $statusClass; ?>">
                                            <?php
session_start(); echo $statusText; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="viewMaintenanceRequest(<?php
session_start(); echo $request['id']; ?>)" class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                                        <button onclick="updateMaintenanceStatus(<?php
session_start(); echo $request['id']; ?>, 'in_progress')" class="text-yellow-600 hover:text-yellow-900 mr-3">Start</button>
                                        <button onclick="updateMaintenanceStatus(<?php
session_start(); echo $request['id']; ?>, 'completed')" class="text-green-600 hover:text-green-900">Complete</button>
                                    </td>
                                </tr>
                                <?php
session_start(); endforeach; ?>
                            <?php
session_start(); else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-tools text-4xl mb-4"></i>
                                            <p class="text-lg font-medium">No maintenance requests found</p>
                                            <p class="text-sm">Create a new request to get started</p>
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

        <!-- New Maintenance Request Modal -->
        <div id="new-maintenance-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">New Maintenance Request</h3>
                        <button onclick="closeNewMaintenanceModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form id="new-maintenance-form" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Room Number *</label>
                            <select name="room_id" id="room_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Room</option>
                                <?php
session_start();
                                try {
                                    $stmt = $pdo->query("SELECT id, room_number FROM rooms ORDER BY room_number ASC");
                                    $rooms = $stmt->fetchAll();
                                    foreach ($rooms as $room) {
                                        echo '<option value="' . $room['id'] . '">' . htmlspecialchars($room['room_number']) . '</option>';
                                    }
                                } catch (PDOException $e) {
                                    error_log('Error loading rooms: ' . $e->getMessage());
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Issue Type *</label>
                            <select name="issue_type" id="issue_type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Issue Type</option>
                                <option value="plumbing">Plumbing</option>
                                <option value="electrical">Electrical</option>
                                <option value="hvac">HVAC</option>
                                <option value="appliance">Appliance</option>
                                <option value="furniture">Furniture</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                            <select name="priority" id="priority" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                            <textarea name="description" id="description" rows="3" required placeholder="Describe the issue..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Estimated Cost</label>
                            <input type="number" step="0.01" min="0" name="estimated_cost" id="estimated_cost" placeholder="0.00" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="flex justify-end space-x-4">
                            <button type="button" onclick="closeNewMaintenanceModal()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                                Create Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Scripts -->
        <script src="../../assets/js/main.js?v=<?php
session_start(); echo time(); ?>"></script>
        <script src="../../assets/js/housekeeping-maintenance.js?v=<?php
session_start(); echo time(); ?>"></script>
    </body>
</html>
