<?php
/**
 * Inventory Reports and Analytics
 * Hotel PMS Training System for Students
 */

session_start();
require_once __DIR__ . '/config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../booking/login.php');
    exit();
}

$inventory_db = new InventoryDatabase();

// Get date range parameters
$date_from = $_GET['date_from'] ?? date('Y-m-01'); // First day of current month
$date_to = $_GET['date_to'] ?? date('Y-m-d'); // Today

// Get inventory statistics
$stats = [
    'total_items' => 0,
    'total_value' => 0,
    'low_stock_count' => 0,
    'total_transactions' => 0,
    'in_transactions' => 0,
    'out_transactions' => 0
];

try {
    // Total items
    $stmt = $inventory_db->getConnection()->query("SELECT COUNT(*) as count FROM inventory_items WHERE status = 'active'");
    $stats['total_items'] = $stmt->fetch()['count'];
    
    // Total value
    $stmt = $inventory_db->getConnection()->query("SELECT SUM(quantity * unit_price) as total FROM inventory_items WHERE status = 'active'");
    $result = $stmt->fetch();
    $stats['total_value'] = $result['total'] ?? 0;
    
    // Low stock count
    $stmt = $inventory_db->getConnection()->query("SELECT COUNT(*) as count FROM inventory_items WHERE quantity <= minimum_stock AND status = 'active'");
    $stats['low_stock_count'] = $stmt->fetch()['count'];
    
    // Transaction statistics
    $stmt = $inventory_db->getConnection()->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN transaction_type = 'in' THEN 1 ELSE 0 END) as in_count,
            SUM(CASE WHEN transaction_type = 'out' THEN 1 ELSE 0 END) as out_count
        FROM inventory_transactions 
        WHERE DATE(created_at) BETWEEN ? AND ?
    ");
    $stmt->execute([$date_from, $date_to]);
    $transaction_stats = $stmt->fetch();
    $stats['total_transactions'] = $transaction_stats['total'];
    $stats['in_transactions'] = $transaction_stats['in_count'];
    $stats['out_transactions'] = $transaction_stats['out_count'];
    
} catch (PDOException $e) {
    error_log("Error getting statistics: " . $e->getMessage());
}

// Get low stock items
$low_stock_items = $inventory_db->getLowStockItems(10);

// Get recent transactions
$recent_transactions = [];
try {
    $stmt = $inventory_db->getConnection()->prepare("
        SELECT it.*, ii.name as item_name, u.name as user_name
        FROM inventory_transactions it
        JOIN inventory_items ii ON it.item_id = ii.id
        JOIN users u ON it.user_id = u.id
        WHERE DATE(it.created_at) BETWEEN ? AND ?
        ORDER BY it.created_at DESC
        LIMIT 20
    ");
    $stmt->execute([$date_from, $date_to]);
    $recent_transactions = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error getting recent transactions: " . $e->getMessage());
}

// Get category breakdown
$category_breakdown = [];
try {
    $stmt = $inventory_db->getConnection()->query("
        SELECT c.name as category_name, 
               COUNT(ii.id) as item_count,
               SUM(ii.quantity * ii.unit_price) as total_value
        FROM inventory_categories c
        LEFT JOIN inventory_items ii ON c.id = ii.category_id AND ii.status = 'active'
        WHERE c.active = 1
        GROUP BY c.id, c.name
        ORDER BY total_value DESC
    ");
    $category_breakdown = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error getting category breakdown: " . $e->getMessage());
}

// Get top items by value
$top_items = [];
try {
    $stmt = $inventory_db->getConnection()->query("
        SELECT name, quantity, unit_price, (quantity * unit_price) as total_value
        FROM inventory_items 
        WHERE status = 'active'
        ORDER BY total_value DESC
        LIMIT 10
    ");
    $top_items = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error getting top items: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Reports - Hotel PMS Training</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <a href="index.php" class="text-primary hover:text-secondary mr-4">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <i class="fas fa-chart-bar text-primary text-2xl mr-3"></i>
                    <h1 class="text-2xl font-bold text-gray-900">Inventory Reports</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="exportReport()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-download mr-2"></i>Export Report
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex space-x-8">
                <a href="index.php" class="text-gray-500 hover:text-gray-700 py-4 px-1 text-sm font-medium">
                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                </a>
                <a href="items.php" class="text-gray-500 hover:text-gray-700 py-4 px-1 text-sm font-medium">
                    <i class="fas fa-box mr-2"></i>Items
                </a>
                <a href="transactions.php" class="text-gray-500 hover:text-gray-700 py-4 px-1 text-sm font-medium">
                    <i class="fas fa-exchange-alt mr-2"></i>Transactions
                </a>
                <a href="requests.php" class="text-gray-500 hover:text-gray-700 py-4 px-1 text-sm font-medium">
                    <i class="fas fa-clipboard-list mr-2"></i>Requests
                </a>
                <a href="training.php" class="text-gray-500 hover:text-gray-700 py-4 px-1 text-sm font-medium">
                    <i class="fas fa-graduation-cap mr-2"></i>Training
                </a>
                <a href="reports.php" class="border-b-2 border-primary text-primary py-4 px-1 text-sm font-medium">
                    <i class="fas fa-chart-bar mr-2"></i>Reports
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <!-- Date Range Filter -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                        <input type="date" name="date_from" value="<?php echo $date_from; ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                        <input type="date" name="date_to" value="<?php echo $date_to; ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-primary hover:bg-secondary text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-filter mr-2"></i>Apply Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

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
                        <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Low Stock Items</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['low_stock_count']); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exchange-alt text-blue-500 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Transactions</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['total_transactions']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Analytics -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Category Breakdown Chart -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Inventory by Category</h3>
                </div>
                <div class="p-6">
                    <canvas id="categoryChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Transaction Types Chart -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Transaction Types</h3>
                </div>
                <div class="p-6">
                    <canvas id="transactionChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Detailed Reports -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Low Stock Items -->
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
                                <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($item['name']); ?></p>
                                        <p class="text-sm text-gray-500">Current: <?php echo $item['quantity']; ?> | Min: <?php echo $item['minimum_stock']; ?></p>
                                    </div>
                                    <span class="text-red-600 font-medium">Reorder</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Top Items by Value -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Top Items by Value</h3>
                </div>
                <div class="p-6">
                    <?php if (empty($top_items)): ?>
                        <p class="text-gray-500 text-center py-4">No items found</p>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($top_items as $item): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($item['name']); ?></p>
                                        <p class="text-sm text-gray-500">Qty: <?php echo $item['quantity']; ?> | Price: $<?php echo number_format($item['unit_price'], 2); ?></p>
                                    </div>
                                    <span class="text-primary font-medium">$<?php echo number_format($item['total_value'], 2); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white rounded-lg shadow mt-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Recent Transactions</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($recent_transactions as $transaction): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo date('M j, Y', strtotime($transaction['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($transaction['item_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $type_colors = [
                                        'in' => 'bg-green-100 text-green-800',
                                        'out' => 'bg-red-100 text-red-800',
                                        'adjustment' => 'bg-yellow-100 text-yellow-800'
                                    ];
                                    $color_class = $type_colors[$transaction['transaction_type']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $color_class; ?>">
                                        <?php echo ucfirst($transaction['transaction_type']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $transaction['quantity']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    $<?php echo number_format($transaction['total_value'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($transaction['user_name']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        // Category Breakdown Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: [<?php echo implode(',', array_map(function($cat) { return '"' . $cat['category_name'] . '"'; }, $category_breakdown)); ?>],
                datasets: [{
                    data: [<?php echo implode(',', array_map(function($cat) { return $cat['total_value']; }, $category_breakdown)); ?>],
                    backgroundColor: [
                        '#10B981', '#3B82F6', '#F59E0B', '#EF4444', '#8B5CF6', '#6B7280'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Transaction Types Chart
        const transactionCtx = document.getElementById('transactionChart').getContext('2d');
        new Chart(transactionCtx, {
            type: 'bar',
            data: {
                labels: ['Stock In', 'Stock Out', 'Adjustments'],
                datasets: [{
                    label: 'Transactions',
                    data: [<?php echo $stats['in_transactions']; ?>, <?php echo $stats['out_transactions']; ?>, <?php echo $stats['total_transactions'] - $stats['in_transactions'] - $stats['out_transactions']; ?>],
                    backgroundColor: ['#10B981', '#EF4444', '#F59E0B']
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        function exportReport() {
            // Implement export functionality
            alert('Export functionality will be implemented');
        }
    </script>
</body>
</html>
