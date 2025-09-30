<?php
/**
 * Inventory Transactions Management
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
$user_id = $_SESSION['user_id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_transaction') {
        try {
            $inventory_db->getConnection()->beginTransaction();
            
            // Add transaction record
            $stmt = $inventory_db->getConnection()->prepare("
                INSERT INTO inventory_transactions (item_id, transaction_type, quantity, unit_price, total_value, reason, reference_number, notes, user_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $total_value = $_POST['quantity'] * $_POST['unit_price'];
            $stmt->execute([
                $_POST['item_id'],
                $_POST['transaction_type'],
                $_POST['quantity'],
                $_POST['unit_price'],
                $total_value,
                $_POST['reason'],
                $_POST['reference_number'],
                $_POST['notes'],
                $user_id
            ]);
            
            // Update item quantity
            $quantity_change = $_POST['transaction_type'] === 'in' ? $_POST['quantity'] : -$_POST['quantity'];
            $stmt = $inventory_db->getConnection()->prepare("
                UPDATE inventory_items 
                SET quantity = quantity + ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$quantity_change, $_POST['item_id']]);
            
            $inventory_db->getConnection()->commit();
            $success_message = "Transaction recorded successfully!";
            
        } catch (PDOException $e) {
            $inventory_db->getConnection()->rollBack();
            $error_message = "Error recording transaction: " . $e->getMessage();
        }
    }
}

// Get filter parameters
$type_filter = $_GET['type'] ?? '';
$item_filter = $_GET['item'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Get items for filter dropdown
$items = [];
try {
    $stmt = $inventory_db->getConnection()->query("SELECT id, name FROM inventory_items WHERE status = 'active' ORDER BY name");
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error getting items: " . $e->getMessage());
}

// Get transactions with filters
$transactions = [];
try {
    $sql = "
        SELECT it.*, ii.name as item_name, ii.sku, u.name as user_name
        FROM inventory_transactions it
        JOIN inventory_items ii ON it.item_id = ii.id
        JOIN users u ON it.user_id = u.id
        WHERE 1=1
    ";
    $params = [];
    
    if ($type_filter) {
        $sql .= " AND it.transaction_type = ?";
        $params[] = $type_filter;
    }
    
    if ($item_filter) {
        $sql .= " AND it.item_id = ?";
        $params[] = $item_filter;
    }
    
    if ($date_from) {
        $sql .= " AND DATE(it.created_at) >= ?";
        $params[] = $date_from;
    }
    
    if ($date_to) {
        $sql .= " AND DATE(it.created_at) <= ?";
        $params[] = $date_to;
    }
    
    $sql .= " ORDER BY it.created_at DESC";
    
    $stmt = $inventory_db->getConnection()->prepare($sql);
    $stmt->execute($params);
    $transactions = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error getting transactions: " . $e->getMessage());
}

// Get transaction statistics
$stats = [
    'total_transactions' => count($transactions),
    'total_in' => 0,
    'total_out' => 0,
    'total_value' => 0
];

foreach ($transactions as $transaction) {
    if ($transaction['transaction_type'] === 'in') {
        $stats['total_in'] += $transaction['quantity'];
    } else {
        $stats['total_out'] += $transaction['quantity'];
    }
    $stats['total_value'] += $transaction['total_value'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Transactions - Hotel PMS Training</title>
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
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <a href="index.php" class="text-primary hover:text-secondary mr-4">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <i class="fas fa-exchange-alt text-primary text-2xl mr-3"></i>
                    <h1 class="text-2xl font-bold text-gray-900">Inventory Transactions</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="openAddModal()" class="bg-primary hover:bg-secondary text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-plus mr-2"></i>Record Transaction
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
                <a href="transactions.php" class="border-b-2 border-primary text-primary py-4 px-1 text-sm font-medium">
                    <i class="fas fa-exchange-alt mr-2"></i>Transactions
                </a>
                <a href="requests.php" class="text-gray-500 hover:text-gray-700 py-4 px-1 text-sm font-medium">
                    <i class="fas fa-clipboard-list mr-2"></i>Requests
                </a>
                <a href="training.php" class="text-gray-500 hover:text-gray-700 py-4 px-1 text-sm font-medium">
                    <i class="fas fa-graduation-cap mr-2"></i>Training
                </a>
                <a href="reports.php" class="text-gray-500 hover:text-gray-700 py-4 px-1 text-sm font-medium">
                    <i class="fas fa-chart-bar mr-2"></i>Reports
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <!-- Messages -->
        <?php if (isset($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <i class="fas fa-check-circle mr-2"></i><?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exchange-alt text-primary text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Transactions</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['total_transactions']); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-arrow-down text-green-500 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Items In</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['total_in']); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-arrow-up text-red-500 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Items Out</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['total_out']); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-dollar-sign text-blue-500 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Value</p>
                        <p class="text-2xl font-semibold text-gray-900">$<?php echo number_format($stats['total_value'], 2); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Transaction Type</label>
                        <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">All Types</option>
                            <option value="in" <?php echo $type_filter === 'in' ? 'selected' : ''; ?>>Stock In</option>
                            <option value="out" <?php echo $type_filter === 'out' ? 'selected' : ''; ?>>Stock Out</option>
                            <option value="adjustment" <?php echo $type_filter === 'adjustment' ? 'selected' : ''; ?>>Adjustment</option>
                            <option value="transfer" <?php echo $type_filter === 'transfer' ? 'selected' : ''; ?>>Transfer</option>
                            <option value="return" <?php echo $type_filter === 'return' ? 'selected' : ''; ?>>Return</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Item</label>
                        <select name="item" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">All Items</option>
                            <?php foreach ($items as $item): ?>
                                <option value="<?php echo $item['id']; ?>" <?php echo $item_filter == $item['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                        <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                        <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-primary hover:bg-secondary text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Transaction History (<?php echo count($transactions); ?>)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Value</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($transactions as $transaction): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo date('M j, Y', strtotime($transaction['created_at'])); ?>
                                    <div class="text-xs text-gray-500">
                                        <?php echo date('g:i A', strtotime($transaction['created_at'])); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($transaction['item_name']); ?></div>
                                    <div class="text-sm text-gray-500">SKU: <?php echo htmlspecialchars($transaction['sku']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $type_colors = [
                                        'in' => 'bg-green-100 text-green-800',
                                        'out' => 'bg-red-100 text-red-800',
                                        'adjustment' => 'bg-yellow-100 text-yellow-800',
                                        'transfer' => 'bg-blue-100 text-blue-800',
                                        'return' => 'bg-purple-100 text-purple-800'
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
                                    $<?php echo number_format($transaction['unit_price'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    $<?php echo number_format($transaction['total_value'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($transaction['user_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($transaction['reason']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Add Transaction Modal -->
    <div id="transactionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Record New Transaction</h3>
                </div>
                <form method="POST" class="p-6">
                    <input type="hidden" name="action" value="add_transaction">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Item *</label>
                            <select name="item_id" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select Item</option>
                                <?php foreach ($items as $item): ?>
                                    <option value="<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Transaction Type *</label>
                            <select name="transaction_type" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select Type</option>
                                <option value="in">Stock In</option>
                                <option value="out">Stock Out</option>
                                <option value="adjustment">Adjustment</option>
                                <option value="transfer">Transfer</option>
                                <option value="return">Return</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Quantity *</label>
                            <input type="number" name="quantity" required min="1" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Unit Price</label>
                            <input type="number" name="unit_price" step="0.01" min="0" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reference Number</label>
                            <input type="text" name="reference_number" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reason *</label>
                            <input type="text" name="reason" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea name="notes" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeModal()" 
                                class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-primary hover:bg-secondary text-white rounded-lg">
                            Record Transaction
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('transactionModal').classList.remove('hidden');
        }
        
        function closeModal() {
            document.getElementById('transactionModal').classList.add('hidden');
        }
        
        // Close modal when clicking outside
        document.getElementById('transactionModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
