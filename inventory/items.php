<?php
/**
 * Inventory Items Management
 * Hotel PMS Training System for Students
 */

session_start();
require_once __DIR__ . '/config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // For testing purposes, set a default user ID
    $_SESSION['user_id'] = 1;
    // header('Location: login.php');
    // exit();
}

// Set page title
$page_title = 'Inventory Items';

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
        
        /* Responsive table styles */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .table-responsive table {
            min-width: 100%;
            table-layout: auto;
        }
        
        .table-responsive th,
        .table-responsive td {
            white-space: nowrap;
            min-width: 120px;
        }
        
        .table-responsive th:first-child,
        .table-responsive td:first-child {
            min-width: 200px;
        }
        
        .table-responsive th:last-child,
        .table-responsive td:last-child {
            min-width: 100px;
        }
        
        /* Mobile responsive adjustments */
        @media (max-width: 768px) {
            .table-responsive th,
            .table-responsive td {
                padding: 0.5rem 0.75rem;
                font-size: 0.875rem;
            }
            
            .table-responsive th:first-child,
            .table-responsive td:first-child {
                min-width: 150px;
            }
        }
        
        @media (max-width: 640px) {
            .table-responsive th,
            .table-responsive td {
                padding: 0.375rem 0.5rem;
                font-size: 0.75rem;
            }
            
            .table-responsive th:first-child,
            .table-responsive td:first-child {
                min-width: 120px;
            }
        }
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
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Inventory Items</h2>
                <div class="flex items-center space-x-4">
                    <button id="toggle-add-item-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-plus mr-2"></i>Add Item
                    </button>
                    <button id="import-items-btn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-upload mr-2"></i>Import Items
                    </button>
                </div>
            </div>

            <!-- Inventory Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-boxes text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Items</p>
                            <p class="text-2xl font-semibold text-gray-900" id="total-items">Loading...</p>
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
                            <p class="text-sm font-medium text-gray-500">In Stock</p>
                            <p class="text-2xl font-semibold text-gray-900" id="in-stock">Loading...</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Low Stock</p>
                            <p class="text-2xl font-semibold text-gray-900" id="low-stock">Loading...</p>
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
                            <p class="text-sm font-medium text-gray-500">Out of Stock</p>
                            <p class="text-2xl font-semibold text-gray-900" id="out-of-stock">Loading...</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-utensils text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">POS Products</p>
                            <p class="text-2xl font-semibold text-gray-900" id="pos-products-count">Loading...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Item Categories -->
            <div id="categories-container" class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Categories will be loaded dynamically -->
            </div>

            <!-- Add Item Form -->
            <div id="add-item-form" class="bg-white rounded-lg shadow p-6 mb-8 hidden">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Add New Inventory Item</h3>
                <form class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Item Name</label>
                            <input type="text" id="item-name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter item name" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <select id="item-category" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Select Category</option>
                                <option value="Food & Beverage">Food & Beverage</option>
                                <option value="Amenities">Amenities</option>
                                <option value="Cleaning Supplies">Cleaning Supplies</option>
                                <option value="Office Supplies">Office Supplies</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">SKU/Barcode</label>
                            <input type="text" id="item-sku" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter SKU or barcode">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Unit</label>
                            <select id="item-unit" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Select Unit</option>
                                <option value="Piece">Piece</option>
                                <option value="Box">Box</option>
                                <option value="Bottle">Bottle</option>
                                <option value="Pack">Pack</option>
                                <option value="Liter">Liter</option>
                                <option value="Kilogram">Kilogram</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Stock</label>
                            <input type="number" id="item-quantity" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter current stock" value="0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Stock Level</label>
                            <input type="number" id="item-minimum-stock" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter minimum level" value="0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Unit Cost</label>
                            <input type="number" id="item-cost-price" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter unit cost" value="0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
                            <input type="text" id="item-supplier" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter supplier name">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea id="item-description" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3" placeholder="Enter item description"></textarea>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                            Add Item
                        </button>
                    </div>
                </form>
            </div>

            <!-- Filter Tabs -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex space-x-1">
                        <button id="all-items-tab" class="px-4 py-2 text-sm font-medium text-blue-600 border-b-2 border-blue-600 bg-blue-50 rounded-t-lg">
                            All Items
                        </button>
                        <button id="inventory-items-tab" class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent">
                            Inventory Items
                        </button>
                        <button id="pos-products-tab" class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent">
                            POS Products
                        </button>
                    </div>
                </div>
            </div>

            <!-- Inventory Items Table -->
            <div id="inventory-items-section" class="bg-white rounded-lg shadow mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">Inventory Items</h3>
                        <div class="flex items-center space-x-2">
                            <button id="toggle-view-btn" class="hidden md:flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                                <i class="fas fa-th-large mr-2"></i>
                                <span id="view-toggle-text">Card View</span>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Table View -->
                <div id="table-view" class="table-responsive">
                    <table class="w-full divide-y divide-gray-200 table-auto">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Item</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Category</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">SKU</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Current Stock</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Min Level</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Unit Cost</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Status</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="inventory-items-tbody" class="bg-white divide-y divide-gray-200">
                            <!-- Inventory items will be loaded dynamically -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Card View -->
                <div id="card-view" class="hidden p-6">
                    <div id="inventory-items-cards" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Cards will be loaded dynamically -->
                    </div>
                </div>
            </div>

            <!-- POS Products Table -->
            <div id="pos-products-section" class="bg-white rounded-lg shadow mb-6" style="display: none;">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">POS Products</h3>
                </div>
                <div class="table-responsive">
                    <table class="w-full divide-y divide-gray-200 table-auto">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Product</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Category</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Price</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Cost</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Margin</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Status</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="pos-products-tbody" class="bg-white divide-y divide-gray-200">
                            <!-- POS products will be loaded here via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Combined Items Table (All Items) -->
            <div id="all-items-section" class="bg-white rounded-lg shadow" style="display: none;">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">All Items (Inventory + POS Products)</h3>
                </div>
                <div class="table-responsive">
                    <table class="w-full divide-y divide-gray-200 table-auto">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Item</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Type</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Category</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Price/Cost</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Stock/Status</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="all-items-tbody" class="bg-white divide-y divide-gray-200">
                            <!-- Combined items will be loaded here via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Include footer -->
        <?php include '../includes/pos-footer.php'; ?>
    </body>
</html>

<script>
$(document).ready(function() {
    // Load initial data
    loadInventoryStatistics();
    loadInventoryCategories();
    loadInventoryItems();
    
    // Tab switching functionality
    $('#all-items-tab').click(function() {
        switchTab('all-items');
        loadAllItems();
    });
    
    $('#inventory-items-tab').click(function() {
        switchTab('inventory-items');
        loadInventoryItems();
    });
    
    $('#pos-products-tab').click(function() {
        switchTab('pos-products');
        loadPOSProducts();
    });
    
    // View toggle functionality
    $('#toggle-view-btn').click(function() {
        toggleView();
    });
    
    function toggleView() {
        const tableView = $('#table-view');
        const cardView = $('#card-view');
        const toggleText = $('#view-toggle-text');
        
        if (tableView.is(':visible')) {
            // Switch to card view
            tableView.hide();
            cardView.removeClass('hidden').show();
            toggleText.text('Table View');
            $(this).find('i').removeClass('fa-th-large').addClass('fa-table');
            
            // Load cards if not already loaded
            if ($('#inventory-items-cards').children().length === 0) {
                loadInventoryItems();
            }
        } else {
            // Switch to table view
            cardView.hide();
            tableView.show();
            toggleText.text('Card View');
            $(this).find('i').removeClass('fa-table').addClass('fa-th-large');
        }
    }
    
    function switchTab(activeTab) {
        // Update tab styles
        $('.px-4.py-2').removeClass('text-blue-600 border-b-2 border-blue-600 bg-blue-50').addClass('text-gray-500 border-b-2 border-transparent');
        $('#' + activeTab + '-tab').removeClass('text-gray-500 border-b-2 border-transparent').addClass('text-blue-600 border-b-2 border-blue-600 bg-blue-50');
        
        // Show/hide sections
        $('#inventory-items-section, #pos-products-section, #all-items-section').hide();
        $('#' + activeTab + '-section').show();
    }
    
    function loadInventoryStatistics() {
        $.ajax({
            url: 'api/get-inventory-stats.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const stats = response.statistics;
                    $('#total-items').text(stats.total_items.toLocaleString());
                    $('#in-stock').text(stats.in_stock.toLocaleString());
                    $('#low-stock').text(stats.low_stock.toLocaleString());
                    $('#out-of-stock').text(stats.out_of_stock.toLocaleString());
                    $('#pos-products-count').text(stats.pos_products.toLocaleString());
                } else {
                    console.error('Error loading inventory statistics:', response.message);
                    $('#total-items, #in-stock, #low-stock, #out-of-stock, #pos-products-count').text('Error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error loading statistics:', error);
                $('#total-items, #in-stock, #low-stock, #out-of-stock, #pos-products-count').text('Error');
            }
        });
    }
    
    function loadInventoryCategories() {
        $.ajax({
            url: 'api/get-inventory-stats.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayCategories(response.statistics.category_stats);
                } else {
                    console.error('Error loading category statistics:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error loading category statistics:', error);
            }
        });
    }
    
    function displayCategories(categoryStats) {
        const container = $('#categories-container');
        container.empty();
        
        categoryStats.forEach(function(category) {
            const statusClass = category.out_of_stock_count > 0 ? 'bg-red-100 text-red-800' : 
                               category.low_stock_count > 0 ? 'bg-yellow-100 text-yellow-800' : 
                               'bg-green-100 text-green-800';
            const statusText = category.out_of_stock_count > 0 ? 'Out of Stock' : 
                              category.low_stock_count > 0 ? 'Low Stock' : 
                              'In Stock';
            
            const categoryCard = `
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">${category.category_name}</h3>
                        <i class="${category.category_icon} text-xl" style="color: ${category.category_color}"></i>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Total Items</p>
                                <p class="text-sm text-gray-500">${category.item_count} items</p>
                            </div>
                            <span class="px-2 py-1 ${statusClass} text-xs font-semibold rounded-full">
                                ${statusText}
                            </span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-xs">
                            <div class="text-center p-2 bg-green-50 rounded">
                                <div class="font-semibold text-green-800">${category.in_stock_count}</div>
                                <div class="text-green-600">In Stock</div>
                            </div>
                            <div class="text-center p-2 bg-yellow-50 rounded">
                                <div class="font-semibold text-yellow-800">${category.low_stock_count}</div>
                                <div class="text-yellow-600">Low Stock</div>
                            </div>
                            <div class="text-center p-2 bg-red-50 rounded">
                                <div class="font-semibold text-red-800">${category.out_of_stock_count}</div>
                                <div class="text-red-600">Out of Stock</div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.append(categoryCard);
        });
    }
    
    function loadInventoryItems() {
        $.ajax({
            url: 'api/get-inventory-items.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayInventoryItems(response.inventory_items);
                } else {
                    console.error('Error loading inventory items:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error loading inventory items:', error);
            }
        });
    }
    
    function displayInventoryItems(items) {
        const tbody = $('#inventory-items-tbody');
        tbody.empty();
        
        items.forEach(function(item) {
            const statusClass = item.stock_status === 'out_of_stock' ? 'bg-red-100 text-red-800' : 
                               item.stock_status === 'low_stock' ? 'bg-yellow-100 text-yellow-800' : 
                               'bg-green-100 text-green-800';
            const statusText = item.stock_status === 'out_of_stock' ? 'Out of Stock' : 
                              item.stock_status === 'low_stock' ? 'Low Stock' : 
                              'In Stock';
            
            const row = `
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8">
                                <div class="h-8 w-8 rounded-full flex items-center justify-center" style="background-color: ${item.category_color || '#6B7280'}">
                                    <i class="${item.category_icon || 'fas fa-box'} text-white text-xs"></i>
                                </div>
                            </div>
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900 truncate max-w-xs">${item.name}</div>
                                <div class="text-xs text-gray-500 truncate max-w-xs">${item.description || 'No description'}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-3 py-4 text-sm text-gray-900">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            ${item.category_name || 'Uncategorized'}
                        </span>
                    </td>
                    <td class="px-3 py-4 text-sm text-gray-900 font-mono">${item.sku || 'N/A'}</td>
                    <td class="px-3 py-4 text-sm text-gray-900">
                        <div class="font-medium">${item.quantity}</div>
                        <div class="text-xs text-gray-500">${item.unit}</div>
                    </td>
                    <td class="px-3 py-4 text-sm text-gray-900">
                        <div class="font-medium">${item.minimum_stock}</div>
                        <div class="text-xs text-gray-500">${item.unit}</div>
                    </td>
                    <td class="px-3 py-4 text-sm text-gray-900 font-medium">₱${parseFloat(item.cost_price).toFixed(2)}</td>
                    <td class="px-3 py-4">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                            ${statusText}
                        </span>
                    </td>
                    <td class="px-3 py-4 text-sm font-medium">
                        <div class="flex flex-col space-y-1">
                            <button class="text-blue-600 hover:text-blue-900 text-xs">Edit</button>
                            <button class="text-green-600 hover:text-green-900 text-xs">Restock</button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
        
        // Also display cards if card view is active
        if ($('#card-view').is(':visible')) {
            displayInventoryItemsCards(items);
        }
    }
    
    function displayInventoryItemsCards(items) {
        const cardsContainer = $('#inventory-items-cards');
        cardsContainer.empty();
        
        items.forEach(function(item) {
            const statusClass = item.stock_status === 'out_of_stock' ? 'bg-red-100 text-red-800' : 
                               item.stock_status === 'low_stock' ? 'bg-yellow-100 text-yellow-800' : 
                               'bg-green-100 text-green-800';
            const statusText = item.stock_status === 'out_of_stock' ? 'Out of Stock' : 
                              item.stock_status === 'low_stock' ? 'Low Stock' : 
                              'In Stock';
            
            const card = `
                <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center">
                            <div class="h-10 w-10 rounded-full flex items-center justify-center mr-3" style="background-color: ${item.category_color || '#6B7280'}">
                                <i class="${item.category_icon || 'fas fa-box'} text-white"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 truncate">${item.name}</h4>
                                <p class="text-xs text-gray-500">${item.category_name || 'Uncategorized'}</p>
                            </div>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full ${statusClass}">
                            ${statusText}
                        </span>
                    </div>
                    
                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">SKU:</span>
                            <span class="font-mono text-gray-900">${item.sku || 'N/A'}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Current Stock:</span>
                            <span class="font-medium">${item.quantity} ${item.unit}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Min Level:</span>
                            <span class="font-medium">${item.minimum_stock} ${item.unit}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Unit Cost:</span>
                            <span class="font-medium">₱${parseFloat(item.cost_price).toFixed(2)}</span>
                        </div>
                    </div>
                    
                    <div class="flex space-x-2">
                        <button class="flex-1 text-blue-600 hover:text-blue-900 text-xs font-medium py-2 px-3 border border-blue-200 rounded-md hover:bg-blue-50">
                            Edit
                        </button>
                        <button class="flex-1 text-green-600 hover:text-green-900 text-xs font-medium py-2 px-3 border border-green-200 rounded-md hover:bg-green-50">
                            Restock
                        </button>
                    </div>
                </div>
            `;
            cardsContainer.append(card);
        });
    }
    
    function loadPOSProducts() {
        $.ajax({
            url: 'api/get-pos-products.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayPOSProducts(response.pos_products);
                } else {
                    console.error('Error loading POS products:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
            }
        });
    }
    
    function displayPOSProducts(products) {
        const tbody = $('#pos-products-tbody');
        tbody.empty();
        
        products.forEach(function(product) {
            const margin = ((product.price - product.cost) / product.price * 100).toFixed(1);
            const statusClass = product.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
            const statusText = product.active ? 'Active' : 'Inactive';
            
            const row = `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center">
                                    <i class="fas fa-utensils text-white"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">${product.name}</div>
                                <div class="text-sm text-gray-500">${product.description || 'No description'}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 capitalize">${product.category.replace('-', ' ')}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱${parseFloat(product.price).toFixed(2)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱${parseFloat(product.cost).toFixed(2)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${margin}%</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                            ${statusText}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                        <button class="text-green-600 hover:text-green-900">View</button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }
    
    function loadAllItems() {
        const allItemsTbody = $('#all-items-tbody');
        allItemsTbody.empty();
        
        // Load inventory items
        $.ajax({
            url: 'api/get-inventory-items.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    response.inventory_items.forEach(function(item) {
                        const statusClass = item.stock_status === 'out_of_stock' ? 'bg-red-100 text-red-800' : 
                                           item.stock_status === 'low_stock' ? 'bg-yellow-100 text-yellow-800' : 
                                           'bg-green-100 text-green-800';
                        const statusText = item.stock_status === 'out_of_stock' ? 'Out of Stock' : 
                                          item.stock_status === 'low_stock' ? 'Low Stock' : 
                                          'In Stock';
            
            const combinedRow = `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full flex items-center justify-center" style="background-color: ${item.category_color || '#6B7280'}">
                                                <i class="${item.category_icon || 'fas fa-box'} text-white"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">${item.name}</div>
                                <div class="text-sm text-gray-500">Inventory Item</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            Inventory
                        </span>
                    </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.category_name || 'Uncategorized'}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱${parseFloat(item.cost_price).toFixed(2)}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                                        ${statusText}
                                    </span>
                                </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                        <button class="text-green-600 hover:text-green-900">View</button>
                    </td>
                </tr>
            `;
            allItemsTbody.append(combinedRow);
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error loading inventory items for combined view:', error);
            }
        });
        
        // Load POS products and add them to combined view
        $.ajax({
            url: 'api/get-pos-products.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    response.pos_products.forEach(function(product) {
                        const statusClass = product.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                        const statusText = product.active ? 'Active' : 'Inactive';
                        
                        const combinedRow = `
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-purple-500 flex items-center justify-center">
                                                <i class="fas fa-utensils text-white"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">${product.name}</div>
                                            <div class="text-sm text-gray-500">POS Product</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                        POS
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 capitalize">${product.category.replace('-', ' ')}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱${parseFloat(product.price).toFixed(2)}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                                        ${statusText}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                    <button class="text-green-600 hover:text-green-900">View</button>
                                </td>
                            </tr>
                        `;
                        allItemsTbody.append(combinedRow);
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error loading POS products for combined view:', error);
            }
        });
    }
    
    // Button event handlers
    $('#toggle-add-item-btn').click(function() {
        toggleAddItemForm();
    });
    
    $('#import-items-btn').click(function() {
        importItems();
    });
    
    // Add item form submission
    $('#add-item-form form').submit(function(e) {
        e.preventDefault();
        addNewItem();
    });
    
    function toggleAddItemForm() {
        const form = $('#add-item-form');
        const button = $('#toggle-add-item-btn');
        
        if (form.hasClass('hidden')) {
            form.removeClass('hidden');
            button.html('<i class="fas fa-times mr-2"></i>Cancel');
            button.removeClass('bg-blue-600 hover:bg-blue-700').addClass('bg-red-600 hover:bg-red-700');
            
            // Scroll to form
            $('html, body').animate({
                scrollTop: form.offset().top - 100
            }, 500);
            
            // Focus on first input
            form.find('input:first').focus();
        } else {
            form.addClass('hidden');
            button.html('<i class="fas fa-plus mr-2"></i>Add Item');
            button.removeClass('bg-red-600 hover:bg-red-700').addClass('bg-blue-600 hover:bg-blue-700');
            
            // Reset form
            form[0].reset();
        }
    }
    
    function importItems() {
        // Create file input for CSV import
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = '.csv';
        input.onchange = function(e) {
            const file = e.target.files[0];
            if (file) {
                uploadCSVFile(file);
            }
        };
        input.click();
    }
    
    function uploadCSVFile(file) {
        const formData = new FormData();
        formData.append('csv_file', file);
        
        $.ajax({
            url: 'api/import-items.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Items imported successfully! ' + response.imported_count + ' items imported.');
                    loadInventoryItems();
                    loadAllItems();
                } else {
                    alert('Error importing items: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error importing items:', error);
                alert('Error importing items');
            }
        });
    }
    
    function addNewItem() {
        const formData = {
            name: $('#item-name').val(),
            category: $('#item-category').val(),
            sku: $('#item-sku').val(),
            unit: $('#item-unit').val(),
            quantity: $('#item-quantity').val(),
            minimum_stock: $('#item-minimum-stock').val(),
            cost_price: $('#item-cost-price').val(),
            supplier: $('#item-supplier').val(),
            description: $('#item-description').val()
        };
        
        // Validate required fields
        if (!formData.name || !formData.category || !formData.unit) {
            alert('Please fill in all required fields (Name, Category, Unit)');
            return;
        }
        
        $.ajax({
            url: 'api/create-inventory-item.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Item added successfully!');
                    toggleAddItemForm(); // Hide form
                    loadInventoryItems();
                    loadAllItems();
                } else {
                    alert('Error adding item: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error adding item:', error);
                alert('Error adding item');
            }
        });
    }
});
</script>