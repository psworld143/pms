<?php
// VPS Session Fix - Robust session configuration
require_once __DIR__ . '/../vps_session_fix.php';

require_once __DIR__ . '/config/database.php';

// Check if user is logged in and has housekeeping role
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if user role is housekeeping
if ($_SESSION['user_role'] !== 'housekeeping') {
    header('Location: login.php?error=access_denied');
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Housekeeping Staff';
$user_role = $_SESSION['user_role'];

// Initialize inventory database
$inventory_db = new InventoryDatabase();

// Get housekeeping-specific statistics
$stats = [
    'my_requests' => 0,
    'pending_requests' => 0,
    'completed_tasks' => 0,
    'room_inventory_items' => 0
];

// Check if inventory tables exist
$tables_exist = $inventory_db->checkInventoryTables();

if ($tables_exist) {
    try {
        // Get my requests count
        $stmt = $inventory_db->getConnection()->prepare("SELECT COUNT(*) as count FROM inventory_requests WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $stats['my_requests'] = $stmt->fetch()['count'];
        
        // Get pending requests count
        $stmt = $inventory_db->getConnection()->query("SELECT COUNT(*) as count FROM inventory_requests WHERE status = 'pending'");
        $stats['pending_requests'] = $stmt->fetch()['count'];
        
        // Get completed tasks count (approved requests)
        $stmt = $inventory_db->getConnection()->query("SELECT COUNT(*) as count FROM inventory_requests WHERE status = 'approved'");
        $stats['completed_tasks'] = $stmt->fetch()['count'];
        
        // Get room inventory items count
        $stmt = $inventory_db->getConnection()->query("SELECT COUNT(*) as count FROM inventory_items WHERE category = 'Room Supplies' OR category = 'Housekeeping'");
        $stats['room_inventory_items'] = $stmt->fetch()['count'];
        
    } catch (PDOException $e) {
        error_log("Error getting housekeeping stats: " . $e->getMessage());
    }
}

// Get recent requests for this user
$recent_requests = [];
if ($tables_exist) {
    try {
        $stmt = $inventory_db->getConnection()->prepare("
            SELECT ir.*, ii.name as item_name, ii.unit_price 
            FROM inventory_requests ir 
            LEFT JOIN inventory_items ii ON ir.item_id = ii.id 
            WHERE ir.user_id = ? 
            ORDER BY ir.created_at DESC 
            LIMIT 5
        ");
        $stmt->execute([$user_id]);
        $recent_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting recent requests: " . $e->getMessage());
    }
}

// Get low stock room supplies
$low_stock_items = [];
if ($tables_exist) {
    try {
        $stmt = $inventory_db->getConnection()->query("
            SELECT name, current_stock, minimum_stock, unit_price 
            FROM inventory_items 
            WHERE (category = 'Room Supplies' OR category = 'Housekeeping') 
            AND current_stock <= minimum_stock 
            ORDER BY (current_stock / minimum_stock) ASC 
            LIMIT 5
        ");
        $low_stock_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting low stock items: " . $e->getMessage());
    }
}

// Demo data for when tables don't exist
if (empty($recent_requests)) {
    $recent_requests = [
        ['item_name' => 'Bath Towels', 'quantity_requested' => 20, 'status' => 'pending', 'created_at' => date('Y-m-d H:i:s')],
        ['item_name' => 'Shampoo Bottles', 'quantity_requested' => 15, 'status' => 'approved', 'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour'))],
        ['item_name' => 'Coffee Cups', 'quantity_requested' => 10, 'status' => 'pending', 'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))]
    ];
}

if (empty($low_stock_items)) {
    $low_stock_items = [
        ['name' => 'Bath Towels', 'current_stock' => 5, 'minimum_stock' => 10, 'unit_price' => 15.00],
        ['name' => 'Shampoo Bottles', 'current_stock' => 3, 'minimum_stock' => 8, 'unit_price' => 8.50],
        ['name' => 'Coffee Cups', 'current_stock' => 7, 'minimum_stock' => 15, 'unit_price' => 5.00]
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Housekeeping Dashboard - Hotel PMS Inventory</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#667eea',
                        secondary: '#764ba2',
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
        <!-- Include Header -->
        <?php include 'includes/inventory-header.php'; ?>
        
        <!-- Include Sidebar -->
        <?php include 'includes/sidebar-inventory.php'; ?>
        
        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <!-- Housekeeping Dashboard Header -->
            <div class="mb-6 sm:mb-8">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                            🧹 Housekeeping Dashboard
                        </h1>
                        <p class="text-gray-600 mt-1 sm:mt-2">
                            Daily supply management and room inventory updates
                        </p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm font-medium">
                            <i class="fas fa-user mr-1"></i>
                            <?php echo htmlspecialchars($user_name); ?>
                        </span>
                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                            <i class="fas fa-broom mr-1"></i>
                            Housekeeping Staff
                        </span>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
                <!-- My Requests -->
                <div class="bg-white rounded-lg shadow p-4 sm:p-6 border-l-4 border-purple-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-clipboard-list text-purple-500 text-xl sm:text-2xl"></i>
                        </div>
                        <div class="ml-3 sm:ml-4">
                            <p class="text-sm font-medium text-gray-500">My Requests</p>
                            <p class="text-xl sm:text-2xl font-semibold text-gray-900"><?php echo $stats['my_requests']; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Pending Requests -->
                <div class="bg-white rounded-lg shadow p-4 sm:p-6 border-l-4 border-orange-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock text-orange-500 text-xl sm:text-2xl"></i>
                        </div>
                        <div class="ml-3 sm:ml-4">
                            <p class="text-sm font-medium text-gray-500">Pending</p>
                            <p class="text-xl sm:text-2xl font-semibold text-gray-900"><?php echo $stats['pending_requests']; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Completed Tasks -->
                <div class="bg-white rounded-lg shadow p-4 sm:p-6 border-l-4 border-green-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-500 text-xl sm:text-2xl"></i>
                        </div>
                        <div class="ml-3 sm:ml-4">
                            <p class="text-sm font-medium text-gray-500">Completed</p>
                            <p class="text-xl sm:text-2xl font-semibold text-gray-900"><?php echo $stats['completed_tasks']; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Room Items -->
                <div class="bg-white rounded-lg shadow p-4 sm:p-6 border-l-4 border-blue-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-bed text-blue-500 text-xl sm:text-2xl"></i>
                        </div>
                        <div class="ml-3 sm:ml-4">
                            <p class="text-sm font-medium text-gray-500">Room Items</p>
                            <p class="text-xl sm:text-2xl font-semibold text-gray-900"><?php echo $stats['room_inventory_items']; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow mb-6 sm:mb-8">
                <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">🧹 Quick Actions</h3>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                        <a href="requests.php?action=create" class="bg-purple-500 hover:bg-purple-600 text-white p-4 rounded-lg text-center transition-colors">
                            <i class="fas fa-plus-circle text-2xl mb-2"></i>
                            <p class="font-medium">Create Request</p>
                            <p class="text-xs opacity-90">Request supplies</p>
                        </a>
                        <a href="transactions.php?action=record" class="bg-green-500 hover:bg-green-600 text-white p-4 rounded-lg text-center transition-colors">
                            <i class="fas fa-clipboard-check text-2xl mb-2"></i>
                            <p class="font-medium">Record Usage</p>
                            <p class="text-xs opacity-90">Log item usage</p>
                        </a>
                        <a href="room-inventory.php" class="bg-blue-500 hover:bg-blue-600 text-white p-4 rounded-lg text-center transition-colors">
                            <i class="fas fa-bed text-2xl mb-2"></i>
                            <p class="font-medium">Room Inventory</p>
                            <p class="text-xs opacity-90">Update room items</p>
                        </a>
                        <a href="requests.php" class="bg-orange-500 hover:bg-orange-600 text-white p-4 rounded-lg text-center transition-colors">
                            <i class="fas fa-list text-2xl mb-2"></i>
                            <p class="font-medium">View Requests</p>
                            <p class="text-xs opacity-90">Check status</p>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 sm:gap-8">
                <!-- Recent Requests -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">My Recent Requests</h3>
                            <a href="requests.php" class="text-sm text-purple-600 hover:text-purple-700">View All</a>
                        </div>
                    </div>
                    <div class="p-4 sm:p-6">
                        <div class="space-y-3">
                            <?php if (empty($recent_requests)): ?>
                                <div class="text-center py-8 text-gray-500">
                                    <i class="fas fa-clipboard-list text-4xl mb-4"></i>
                                    <p>No requests yet</p>
                                    <a href="requests.php?action=create" class="text-purple-600 hover:text-purple-700 text-sm">Create your first request</a>
                                </div>
                            <?php else: ?>
                                <?php foreach ($recent_requests as $request): ?>
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                <?php echo htmlspecialchars($request['item_name'] ?? 'Unknown Item'); ?>
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                Qty: <?php echo $request['quantity_requested'] ?? 0; ?> • 
                                                <?php echo date('M j, Y', strtotime($request['created_at'] ?? 'now')); ?>
                                            </p>
                                        </div>
                                        <div class="ml-3">
                                            <?php
                                            $status = $request['status'] ?? 'pending';
                                            $status_colors = [
                                                'pending' => 'bg-orange-100 text-orange-800',
                                                'approved' => 'bg-green-100 text-green-800',
                                                'rejected' => 'bg-red-100 text-red-800'
                                            ];
                                            $status_icons = [
                                                'pending' => 'fas fa-clock',
                                                'approved' => 'fas fa-check',
                                                'rejected' => 'fas fa-times'
                                            ];
                                            ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?php echo $status_colors[$status] ?? 'bg-gray-100 text-gray-800'; ?>">
                                                <i class="<?php echo $status_icons[$status] ?? 'fas fa-question'; ?> mr-1"></i>
                                                <?php echo ucfirst($status); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Alert -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">Low Stock Alert</h3>
                            <a href="items.php?filter=low_stock" class="text-sm text-red-600 hover:text-red-700">View All</a>
                        </div>
                    </div>
                    <div class="p-4 sm:p-6">
                        <div class="space-y-3">
                            <?php if (empty($low_stock_items)): ?>
                                <div class="text-center py-8 text-gray-500">
                                    <i class="fas fa-check-circle text-4xl mb-4 text-green-500"></i>
                                    <p>All room supplies are well stocked!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($low_stock_items as $item): ?>
                                    <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-200">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                <?php echo htmlspecialchars($item['name']); ?>
                                            </p>
                                            <div class="flex items-center mt-1">
                                                <div class="flex-1 bg-gray-200 rounded-full h-2 mr-3">
                                                    <div class="bg-red-500 h-2 rounded-full" style="width: <?php echo min(100, ($item['current_stock'] / $item['minimum_stock']) * 100); ?>%"></div>
                                                </div>
                                                <span class="text-xs text-gray-500">
                                                    <?php echo $item['current_stock']; ?>/<?php echo $item['minimum_stock']; ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                Low Stock
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily Tasks Checklist -->
            <div class="mt-6 sm:mt-8">
                <div class="bg-white rounded-lg shadow">
                    <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">📋 Daily Tasks Checklist</h3>
                    </div>
                    <div class="p-4 sm:p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <input type="checkbox" class="h-4 w-4 text-purple-600 rounded border-gray-300">
                                <label class="ml-3 text-sm text-gray-700">Check room supplies</label>
                            </div>
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <input type="checkbox" class="h-4 w-4 text-purple-600 rounded border-gray-300">
                                <label class="ml-3 text-sm text-gray-700">Update inventory usage</label>
                            </div>
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <input type="checkbox" class="h-4 w-4 text-purple-600 rounded border-gray-300">
                                <label class="ml-3 text-sm text-gray-700">Request low stock items</label>
                            </div>
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <input type="checkbox" class="h-4 w-4 text-purple-600 rounded border-gray-300">
                                <label class="ml-3 text-sm text-gray-700">Review pending requests</label>
                            </div>
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <input type="checkbox" class="h-4 w-4 text-purple-600 rounded border-gray-300">
                                <label class="ml-3 text-sm text-gray-700">Update room status</label>
                            </div>
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <input type="checkbox" class="h-4 w-4 text-purple-600 rounded border-gray-300">
                                <label class="ml-3 text-sm text-gray-700">Report maintenance issues</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- JavaScript for interactive features -->
    <script>
        // Handle URL parameters for quick actions
        function handleUrlParameters() {
            const urlParams = new URLSearchParams(window.location.search);
            const action = urlParams.get('action');
            
            if (action === 'create') {
                // Open create request modal or redirect
                window.location.href = 'requests.php?action=create';
            } else if (action === 'record') {
                // Open record usage modal or redirect
                window.location.href = 'transactions.php?action=record';
            }
        }
        
        // Call on page load
        document.addEventListener('DOMContentLoaded', function() {
            handleUrlParameters();
            
            // Add click handlers for checkboxes
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        this.parentElement.classList.add('bg-green-50', 'border-green-200');
                        this.parentElement.classList.remove('bg-gray-50');
                    } else {
                        this.parentElement.classList.remove('bg-green-50', 'border-green-200');
                        this.parentElement.classList.add('bg-gray-50');
                    }
                });
            });
        });
    </script>
</body>
</html>


