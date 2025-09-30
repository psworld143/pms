<?php
/**
 * Inventory Items Management
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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_item') {
        try {
            $stmt = $inventory_db->getConnection()->prepare("
                INSERT INTO inventory_items (name, sku, category_id, description, quantity, minimum_stock, unit_price, cost_price, supplier, location, unit, barcode)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $_POST['name'],
                $_POST['sku'],
                $_POST['category_id'],
                $_POST['description'],
                $_POST['quantity'],
                $_POST['minimum_stock'],
                $_POST['unit_price'],
                $_POST['cost_price'],
                $_POST['supplier'],
                $_POST['location'],
                $_POST['unit'],
                $_POST['barcode']
            ]);
            
            $success_message = "Item added successfully!";
        } catch (PDOException $e) {
            $error_message = "Error adding item: " . $e->getMessage();
        }
    }
    
    if ($action === 'update_item') {
        try {
            $stmt = $inventory_db->getConnection()->prepare("
                UPDATE inventory_items 
                SET name = ?, sku = ?, category_id = ?, description = ?, minimum_stock = ?, unit_price = ?, cost_price = ?, supplier = ?, location = ?, unit = ?, barcode = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $_POST['name'],
                $_POST['sku'],
                $_POST['category_id'],
                $_POST['description'],
                $_POST['minimum_stock'],
                $_POST['unit_price'],
                $_POST['cost_price'],
                $_POST['supplier'],
                $_POST['location'],
                $_POST['unit'],
                $_POST['barcode'],
                $_POST['item_id']
            ]);
            
            $success_message = "Item updated successfully!";
        } catch (PDOException $e) {
            $error_message = "Error updating item: " . $e->getMessage();
        }
    }
}

// Get filter parameters
$category_filter = $_GET['category'] ?? '';
$search_filter = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? 'active';

// Get categories for filter dropdown
$categories = [];
try {
    $stmt = $inventory_db->getConnection()->query("SELECT * FROM inventory_categories WHERE active = 1 ORDER BY name");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error getting categories: " . $e->getMessage());
}

// Get items with filters
$items = $inventory_db->getInventoryItems($category_filter, $search_filter);

// Filter by status if specified
if ($status_filter !== 'all') {
    $items = array_filter($items, function($item) use ($status_filter) {
        return $item['status'] === $status_filter;
    });
}

// Get item details for editing
$edit_item = null;
if (isset($_GET['edit'])) {
    try {
        $stmt = $inventory_db->getConnection()->prepare("SELECT * FROM inventory_items WHERE id = ?");
        $stmt->execute([$_GET['edit']]);
        $edit_item = $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error getting item for edit: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Items - Hotel PMS Training</title>
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
                    <i class="fas fa-box text-primary text-2xl mr-3"></i>
                    <h1 class="text-2xl font-bold text-gray-900">Inventory Items</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="openAddModal()" class="bg-primary hover:bg-secondary text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-plus mr-2"></i>Add Item
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
                <a href="items.php" class="border-b-2 border-primary text-primary py-4 px-1 text-sm font-medium">
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

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search_filter); ?>" 
                               placeholder="Search items..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                            <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            <option value="discontinued" <?php echo $status_filter === 'discontinued' ? 'selected' : ''; ?>>Discontinued</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-primary hover:bg-secondary text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Items Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Inventory Items (<?php echo count($items); ?>)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($items as $item): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['name']); ?></div>
                                        <div class="text-sm text-gray-500">SKU: <?php echo htmlspecialchars($item['sku']); ?></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary text-white">
                                        <?php echo htmlspecialchars($item['category_name']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo $item['quantity']; ?> <?php echo htmlspecialchars($item['unit']); ?></div>
                                    <?php if ($item['quantity'] <= $item['minimum_stock']): ?>
                                        <div class="text-xs text-red-600">Low Stock!</div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    $<?php echo number_format($item['unit_price'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $status_colors = [
                                        'active' => 'bg-green-100 text-green-800',
                                        'inactive' => 'bg-yellow-100 text-yellow-800',
                                        'discontinued' => 'bg-red-100 text-red-800'
                                    ];
                                    $color_class = $status_colors[$item['status']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $color_class; ?>">
                                        <?php echo ucfirst($item['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="editItem(<?php echo htmlspecialchars(json_encode($item)); ?>)" 
                                            class="text-primary hover:text-secondary mr-3">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="viewItem(<?php echo $item['id']; ?>)" 
                                            class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Add/Edit Item Modal -->
    <div id="itemModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Add New Item</h3>
                </div>
                <form method="POST" class="p-6">
                    <input type="hidden" name="action" id="formAction" value="add_item">
                    <input type="hidden" name="item_id" id="itemId">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Item Name *</label>
                            <input type="text" name="name" id="itemName" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">SKU</label>
                            <input type="text" name="sku" id="itemSku" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                            <select name="category_id" id="itemCategory" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Unit</label>
                            <input type="text" name="unit" id="itemUnit" value="pcs" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                            <input type="number" name="quantity" id="itemQuantity" min="0" value="0" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Stock</label>
                            <input type="number" name="minimum_stock" id="itemMinStock" min="0" value="10" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Unit Price</label>
                            <input type="number" name="unit_price" id="itemUnitPrice" step="0.01" min="0" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cost Price</label>
                            <input type="number" name="cost_price" id="itemCostPrice" step="0.01" min="0" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
                            <input type="text" name="supplier" id="itemSupplier" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                            <input type="text" name="location" id="itemLocation" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea name="description" id="itemDescription" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Barcode</label>
                            <input type="text" name="barcode" id="itemBarcode" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeModal()" 
                                class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-primary hover:bg-secondary text-white rounded-lg">
                            Save Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add New Item';
            document.getElementById('formAction').value = 'add_item';
            document.getElementById('itemId').value = '';
            document.getElementById('itemModal').classList.remove('hidden');
            
            // Reset form
            document.querySelector('#itemModal form').reset();
            document.getElementById('itemQuantity').value = '0';
            document.getElementById('itemMinStock').value = '10';
            document.getElementById('itemUnit').value = 'pcs';
        }
        
        function editItem(item) {
            document.getElementById('modalTitle').textContent = 'Edit Item';
            document.getElementById('formAction').value = 'update_item';
            document.getElementById('itemId').value = item.id;
            document.getElementById('itemName').value = item.name;
            document.getElementById('itemSku').value = item.sku || '';
            document.getElementById('itemCategory').value = item.category_id;
            document.getElementById('itemQuantity').value = item.quantity;
            document.getElementById('itemMinStock').value = item.minimum_stock;
            document.getElementById('itemUnitPrice').value = item.unit_price;
            document.getElementById('itemCostPrice').value = item.cost_price || '';
            document.getElementById('itemSupplier').value = item.supplier || '';
            document.getElementById('itemLocation').value = item.location || '';
            document.getElementById('itemUnit').value = item.unit || 'pcs';
            document.getElementById('itemDescription').value = item.description || '';
            document.getElementById('itemBarcode').value = item.barcode || '';
            document.getElementById('itemModal').classList.remove('hidden');
        }
        
        function closeModal() {
            document.getElementById('itemModal').classList.add('hidden');
        }
        
        function viewItem(itemId) {
            // Implement view functionality
            alert('View item details for ID: ' + itemId);
        }
        
        // Close modal when clicking outside
        document.getElementById('itemModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
