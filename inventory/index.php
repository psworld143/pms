<?php
// VPS Session Fix - Robust session configuration
require_once __DIR__ . '/../vps_session_fix.php';

require_once __DIR__ . '/config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'student';

// Initialize inventory database
$inventory_db = new InventoryDatabase();

// Get dashboard statistics
$stats = [
    'total_items' => 0,
    'low_stock_items' => 0,
    'total_value' => 0,
    'pending_requests' => 0
];

try {
    // Get total items count
    $stmt = $inventory_db->getConnection()->query("SELECT COUNT(*) as count FROM inventory_items WHERE status = 'active'");
    $stats['total_items'] = $stmt->fetch()['count'];
    
    // Get low stock items count
    $stmt = $inventory_db->getConnection()->query("SELECT COUNT(*) as count FROM inventory_items WHERE quantity <= minimum_stock AND status = 'active'");
    $stats['low_stock_items'] = $stmt->fetch()['count'];
    
    // Get total inventory value
    $stmt = $inventory_db->getConnection()->query("SELECT SUM(quantity * unit_price) as total FROM inventory_items WHERE status = 'active'");
    $result = $stmt->fetch();
    $stats['total_value'] = $result['total'] ?? 0;
    
    // Get pending requests count
    $stmt = $inventory_db->getConnection()->query("SELECT COUNT(*) as count FROM inventory_requests WHERE status = 'pending'");
    $stats['pending_requests'] = $stmt->fetch()['count'];
    
} catch (PDOException $e) {
    error_log("Error getting dashboard stats: " . $e->getMessage());
}

// Get recent transactions
$recent_transactions = [];
try {
    $stmt = $inventory_db->getConnection()->query("
        SELECT it.*, ii.name as item_name, u.name as user_name
        FROM inventory_transactions it
        JOIN inventory_items ii ON it.item_id = ii.id
        JOIN users u ON it.user_id = u.id
        ORDER BY it.created_at DESC
        LIMIT 10
    ");
    $recent_transactions = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error getting recent transactions: " . $e->getMessage());
}

// Get low stock items
$low_stock_items = $inventory_db->getLowStockItems(10);
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
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-boxes text-primary text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Items</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['total_items']); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
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

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-dollar-sign text-green-500 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Value</p>
                        <p class="text-2xl font-semibold text-gray-900">$<?php echo number_format($stats['total_value'], 2); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clock text-blue-500 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Pending Requests</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['pending_requests']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="items.php?action=add" class="bg-primary hover:bg-secondary text-white p-4 rounded-lg text-center transition-colors">
                        <i class="fas fa-plus text-2xl mb-2"></i>
                        <p class="font-medium">Add New Item</p>
                    </a>
                    <a href="transactions.php?action=add" class="bg-blue-500 hover:bg-blue-600 text-white p-4 rounded-lg text-center transition-colors">
                        <i class="fas fa-exchange-alt text-2xl mb-2"></i>
                        <p class="font-medium">Record Transaction</p>
                    </a>
                    <a href="requests.php?action=create" class="bg-yellow-500 hover:bg-yellow-600 text-white p-4 rounded-lg text-center transition-colors">
                        <i class="fas fa-clipboard-list text-2xl mb-2"></i>
                        <p class="font-medium">Create Request</p>
                    </a>
                    <a href="training.php" class="bg-purple-500 hover:bg-purple-600 text-white p-4 rounded-lg text-center transition-colors">
                        <i class="fas fa-graduation-cap text-2xl mb-2"></i>
                        <p class="font-medium">Start Training</p>
                    </a>
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
