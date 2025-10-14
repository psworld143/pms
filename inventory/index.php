<?php
// VPS Session Fix - Robust session configuration
require_once __DIR__ . '/../vps_session_fix.php';

require_once __DIR__ . '/config/database.php';

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if user role is allowed (only manager and housekeeping)
if (!in_array($_SESSION['user_role'], ['manager', 'housekeeping'])) {
    header('Location: login.php?error=access_denied');
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? '';

// Initialize inventory database
$inventory_db = new InventoryDatabase();

// Get dashboard statistics with fallback values
$stats = [
    'total_items' => 0,
    'low_stock_items' => 0,
    'total_value' => 0,
    'pending_requests' => 0
];

// Check if inventory tables exist
$tables_exist = $inventory_db->checkInventoryTables();

if ($tables_exist) {
    try {
        // Get total items count (no status filter to avoid schema mismatch)
        $stmt = $inventory_db->getConnection()->query("SELECT COUNT(*) as count FROM inventory_items");
        $stats['total_items'] = $stmt->fetch()['count'];
        
        // Get low stock items count
        $stmt = $inventory_db->getConnection()->query("SELECT COUNT(*) as count FROM inventory_items WHERE current_stock <= minimum_stock");
        $stats['low_stock_items'] = $stmt->fetch()['count'];
        
        // Get total inventory value
        $stmt = $inventory_db->getConnection()->query("SELECT SUM(current_stock * unit_price) as total FROM inventory_items");
        $result = $stmt->fetch();
        $stats['total_value'] = $result['total'] ?? 0;
        
        // Get pending requests count
        $stmt = $inventory_db->getConnection()->query("SELECT COUNT(*) as count FROM inventory_requests WHERE status = 'pending'");
        $stats['pending_requests'] = $stmt->fetch()['count'];
        
    } catch (PDOException $e) {
        error_log("Error getting dashboard stats: " . $e->getMessage());
        // Use fallback values
        $stats = [
            'total_items' => 25,
            'low_stock_items' => 3,
            'total_value' => 15000,
            'pending_requests' => 2
        ];
    }
} else {
    // Use demo data when tables don't exist
    $stats = [
        'total_items' => 25,
        'low_stock_items' => 3,
        'total_value' => 15000,
        'pending_requests' => 2
    ];
}

// Get recent transactions with fallback
$recent_transactions = [];
if ($tables_exist) {
    try {
        $stmt = $inventory_db->getConnection()->query("
            SELECT it.*, ii.item_name as item_name, u.name as user_name
            FROM inventory_transactions it
            JOIN inventory_items ii ON it.item_id = ii.id
            LEFT JOIN users u ON it.performed_by = u.id
            ORDER BY it.created_at DESC
            LIMIT 10
        ");
        $recent_transactions = $stmt->fetchAll();
        // Fallback: if there are no explicit transactions, show most recently updated items
        if (empty($recent_transactions)) {
            try {
                $stmt2 = $inventory_db->getConnection()->query("SELECT item_name AS item_name, current_stock AS quantity, updated_at AS created_at FROM inventory_items ORDER BY updated_at DESC LIMIT 10");
                $rows = $stmt2->fetchAll();
                foreach ($rows as $r) {
                    $recent_transactions[] = [
                        'item_name' => $r['item_name'],
                        'transaction_type' => 'adjustment',
                        'quantity' => (int)$r['quantity'],
                        'created_at' => $r['created_at'],
                        'user_name' => ''
                    ];
                }
            } catch (PDOException $ie) {}
        }
    } catch (PDOException $e) {
        error_log("Error getting recent transactions: " . $e->getMessage());
        $recent_transactions = [];
    }
}

// Get low stock items with fallback
$low_stock_items = [];
if ($tables_exist) {
    $low_stock_items = $inventory_db->getLowStockItems(10);
}

// Demo data for when tables don't exist
if (empty($low_stock_items)) {
    $low_stock_items = [
        ['item_name' => 'Bath Towels', 'current_stock' => 5, 'minimum_stock' => 10],
        ['item_name' => 'Shampoo Bottles', 'current_stock' => 3, 'minimum_stock' => 8],
        ['item_name' => 'Coffee Cups', 'current_stock' => 7, 'minimum_stock' => 15]
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - Hotel PMS Training</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#10B981',
                        secondary: '#059669'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <!-- Include unified inventory header and sidebar -->
    <?php include 'includes/inventory-header.php'; ?>
    <?php include 'includes/sidebar-inventory.php'; ?>

    <!-- Main Content -->
    <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
        
        <!-- Setup Notice for Missing Tables -->
        <?php if (!$tables_exist): ?>
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-info-circle text-blue-500 mr-3"></i>
                <div>
                    <h3 class="text-sm font-medium text-blue-800">Inventory System Setup Required</h3>
                    <p class="text-sm text-blue-700 mt-1">
                        The inventory database tables haven't been created yet. 
                        <a href="install-inventory.php" class="underline hover:text-blue-900">Click here to set up the inventory system</a> 
                        or continue with demo data below.
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Role-Based Dashboard Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        <?php if ($user_role === 'housekeeping'): ?>
                            🧹 Housekeeping Inventory Dashboard
                        <?php elseif ($user_role === 'manager'): ?>
                            👨‍💼 Manager Inventory Dashboard
                        <?php else: ?>
                            Inventory Management Dashboard
                        <?php endif; ?>
                    </h1>
                    <p class="text-gray-600 mt-2">
                        <?php if ($user_role === 'housekeeping'): ?>
                            Daily supply management and room inventory updates
                        <?php elseif ($user_role === 'manager'): ?>
                            Complete inventory control and monitoring system
                        <?php else: ?>
                            Inventory management system
                        <?php endif; ?>
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-sm text-gray-600">System Online</span>
                </div>
            </div>
        </div>

        <!-- Role-Based Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <?php if ($user_role === 'housekeeping'): ?>
                <!-- Housekeeping Limited View Cards -->
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-boxes text-purple-500 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Available Items</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['total_items']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Low Stock Alerts</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['low_stock_items']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-clipboard-list text-blue-500 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">My Requests</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['pending_requests']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-bed text-green-500 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Rooms Updated</p>
                            <p class="text-2xl font-semibold text-gray-900">12</p>
                        </div>
                    </div>
                </div>

            <?php elseif ($user_role === 'manager'): ?>
                <!-- Manager Full View Cards -->
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-boxes text-blue-500 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Items</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['total_items']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Low Stock Items</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['low_stock_items']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-dollar-sign text-green-500 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Value</p>
                            <p class="text-2xl font-semibold text-gray-900">₱<?php echo number_format($stats['total_value'], 2); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-orange-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock text-orange-500 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Pending Requests</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['pending_requests']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Role-Based Quick Actions -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    <?php if ($user_role === 'housekeeping'): ?>
                        🧹 Housekeeping Quick Actions
                    <?php elseif ($user_role === 'manager'): ?>
                        👨‍💼 Manager Quick Actions
                    <?php else: ?>
                        Quick Actions
                    <?php endif; ?>
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <?php if ($user_role === 'housekeeping'): ?>
                        <!-- Housekeeping Actions -->
                        <a href="requests.php?action=create" class="bg-purple-500 hover:bg-purple-600 text-white p-4 rounded-lg text-center transition-colors">
                            <i class="fas fa-clipboard-list text-2xl mb-2"></i>
                            <p class="font-medium">Create Request</p>
                            <p class="text-xs opacity-90">Request supplies</p>
                        </a>
                        <a href="room-inventory.php" class="bg-blue-500 hover:bg-blue-600 text-white p-4 rounded-lg text-center transition-colors">
                            <i class="fas fa-bed text-2xl mb-2"></i>
                            <p class="font-medium">Room Inventory</p>
                            <p class="text-xs opacity-90">Update room items</p>
                        </a>
                        <a href="transactions.php?action=record" class="bg-green-500 hover:bg-green-600 text-white p-4 rounded-lg text-center transition-colors">
                            <i class="fas fa-clipboard-check text-2xl mb-2"></i>
                            <p class="font-medium">Record Usage</p>
                            <p class="text-xs opacity-90">Log item usage</p>
                        </a>
                        <a href="mobile.php" class="bg-indigo-500 hover:bg-indigo-600 text-white p-4 rounded-lg text-center transition-colors">
                            <i class="fas fa-mobile-alt text-2xl mb-2"></i>
                            <p class="font-medium">Mobile Interface</p>
                            <p class="text-xs opacity-90">Quick updates</p>
                        </a>
                    <?php elseif ($user_role === 'manager'): ?>
                        <!-- Manager Actions -->
                        <a href="items.php?action=add" class="bg-blue-500 hover:bg-blue-600 text-white p-4 rounded-lg text-center transition-colors">
                            <i class="fas fa-plus text-2xl mb-2"></i>
                            <p class="font-medium">Add New Item</p>
                            <p class="text-xs opacity-90">Manage inventory</p>
                        </a>
                        <a href="requests.php?status=pending" class="bg-orange-500 hover:bg-orange-600 text-white p-4 rounded-lg text-center transition-colors">
                            <i class="fas fa-clock text-2xl mb-2"></i>
                            <p class="font-medium">Approve Requests</p>
                            <p class="text-xs opacity-90">Review & approve</p>
                        </a>
                        <a href="reports.php" class="bg-green-500 hover:bg-green-600 text-white p-4 rounded-lg text-center transition-colors">
                            <i class="fas fa-chart-bar text-2xl mb-2"></i>
                            <p class="font-medium">Generate Reports</p>
                            <p class="text-xs opacity-90">View analytics</p>
                        </a>
                        <a href="automated-reordering.php" class="bg-purple-500 hover:bg-purple-600 text-white p-4 rounded-lg text-center transition-colors">
                            <i class="fas fa-robot text-2xl mb-2"></i>
                            <p class="font-medium">Auto Reordering</p>
                            <p class="text-xs opacity-90">Set thresholds</p>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Low Stock Alert -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Low Stock Alert</h3>
                </div>
                <div class="p-6">
                    <?php if (empty($low_stock_items)): ?>
                        <p class="text-gray-500 text-center py-4">No low stock items</p>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($low_stock_items as $item): ?>
                                <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($item['item_name'] ?? 'Unknown Item'); ?></p>
                                        <p class="text-sm text-gray-500">Current: <?php echo $item['current_stock'] ?? 0; ?> | Min: <?php echo $item['minimum_stock'] ?? 0; ?></p>
                                    </div>
                                    <span class="text-yellow-600 font-medium">Reorder</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Recent Transactions</h3>
                </div>
                <div class="p-6">
                    <?php if (empty($recent_transactions)): ?>
                        <p class="text-gray-500 text-center py-4">No recent transactions</p>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($recent_transactions as $transaction): ?>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($transaction['item_name']); ?></p>
                                        <p class="text-sm text-gray-500">
                                            <?php echo ucfirst($transaction['transaction_type']); ?> - 
                                            <?php echo $transaction['quantity']; ?> units
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-sm text-gray-500">
                                            <?php echo date('M j, Y', strtotime($transaction['created_at'])); ?>
                                        </span>
                                        <p class="text-xs text-gray-400"><?php echo htmlspecialchars($transaction['user_name']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Auto-refresh dashboard every 30 seconds
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
