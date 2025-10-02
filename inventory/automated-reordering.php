<?php
/**
 * Automated Reordering System
 * Hotel PMS Training System - Inventory Module
 */

session_start();
require_once __DIR__ . '/config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Set page title
$page_title = 'Automated Reordering System';

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
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Automated Reordering System</h2>
                <div class="flex items-center space-x-4">
                    <button id="run-auto-reorder-btn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-play mr-2"></i>Run Auto Reorder
                    </button>
                    <button id="generate-po-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-file-invoice mr-2"></i>Generate PO
                    </button>
                </div>
            </div>

            <!-- Reorder Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Items Below Reorder Point</p>
                            <p class="text-2xl font-semibold text-gray-900" id="below-reorder-point">0</p>
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
                            <p class="text-sm font-medium text-gray-500">Pending Purchase Orders</p>
                            <p class="text-2xl font-semibold text-gray-900" id="pending-pos">0</p>
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
                            <p class="text-sm font-medium text-gray-500">Auto Reorder Rules</p>
                            <p class="text-2xl font-semibold text-gray-900" id="auto-reorder-rules">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-dollar-sign text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total PO Value (30d)</p>
                            <p class="text-2xl font-semibold text-gray-900" id="total-po-value">$0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reorder Rules Management -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-800">Reorder Rules</h3>
                    <button id="add-reorder-rule-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-plus mr-2"></i>Add Rule
                    </button>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reorder Point</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reorder Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lead Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Auto PO</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="reorder-rules-tbody" class="bg-white divide-y divide-gray-200">
                            <!-- Reorder rules will be loaded dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Items Below Reorder Point -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Items Below Reorder Point</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reorder Point</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Suggested Qty</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="below-reorder-tbody" class="bg-white divide-y divide-gray-200">
                            <!-- Items below reorder point will be loaded dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Purchase Orders -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Purchase Orders</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expected Delivery</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="purchase-orders-tbody" class="bg-white divide-y divide-gray-200">
                            <!-- Purchase orders will be loaded dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Add Reorder Rule Modal -->
        <div id="add-rule-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-800">Add Reorder Rule</h3>
                            <button id="close-rule-modal" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <form id="add-rule-form" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Item</label>
                                <select id="rule-item" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                    <option value="">Select Item</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Reorder Point</label>
                                    <input type="number" id="rule-reorder-point" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Reorder Quantity</label>
                                    <input type="number" id="rule-reorder-quantity" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Lead Time (Days)</label>
                                    <input type="number" id="rule-lead-time" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" value="7" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
                                    <select id="rule-supplier" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Select Supplier</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="rule-auto-po" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="rule-auto-po" class="ml-2 block text-sm text-gray-900">
                                    Automatically generate purchase orders
                                </label>
                            </div>
                            <div class="flex justify-end space-x-4 pt-4">
                                <button type="button" id="cancel-rule-btn" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                                    Add Rule
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Include footer -->
        <?php include '../includes/pos-footer.php'; ?>
    </body>
</html>

<script>
$(document).ready(function() {
    loadAutomatedReorderingData();
    loadItemsForRule();
    loadSuppliersForRule();
    
    // Event handlers
    $('#add-reorder-rule-btn').click(function() {
        $('#add-rule-modal').removeClass('hidden');
    });
    
    $('#close-rule-modal, #cancel-rule-btn').click(function() {
        $('#add-rule-modal').addClass('hidden');
    });
    
    $('#add-rule-form').submit(function(e) {
        e.preventDefault();
        addReorderRule();
    });
    
    $('#run-auto-reorder-btn').click(function() {
        runAutoReorder();
    });
    
    $('#generate-po-btn').click(function() {
        generatePurchaseOrder();
    });
    
    function loadAutomatedReorderingData() {
        $.ajax({
            url: 'api/get-automated-reordering-data.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    updateStatistics(response.statistics);
                    displayReorderRules(response.reorder_rules);
                    displayBelowReorderItems(response.below_reorder_items);
                    displayPurchaseOrders(response.purchase_orders);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading automated reordering data:', error);
            }
        });
    }
    
    function loadItemsForRule() {
        $.ajax({
            url: 'api/get-inventory-items.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const itemSelect = $('#rule-item');
                    response.inventory_items.forEach(function(item) {
                        itemSelect.append(`<option value="${item.id}">${item.name} (${item.sku})</option>`);
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading items:', error);
            }
        });
    }
    
    function loadSuppliersForRule() {
        $.ajax({
            url: 'api/get-suppliers.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const supplierSelect = $('#rule-supplier');
                    response.suppliers.forEach(function(supplier) {
                        supplierSelect.append(`<option value="${supplier.id}">${supplier.name}</option>`);
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading suppliers:', error);
            }
        });
    }
    
    function updateStatistics(stats) {
        $('#below-reorder-point').text(stats.below_reorder_point);
        $('#pending-pos').text(stats.pending_pos);
        $('#auto-reorder-rules').text(stats.auto_reorder_rules);
        $('#total-po-value').text('$' + stats.total_po_value.toLocaleString());
    }
    
    function displayReorderRules(rules) {
        const tbody = $('#reorder-rules-tbody');
        tbody.empty();
        
        rules.forEach(function(rule) {
            const statusClass = rule.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
            const statusText = rule.active ? 'Active' : 'Inactive';
            const autoPOClass = rule.auto_generate_po ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800';
            const autoPOText = rule.auto_generate_po ? 'Yes' : 'No';
            
            const row = `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${rule.item_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${rule.current_stock}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${rule.reorder_point}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${rule.reorder_quantity}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${rule.lead_time_days} days</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${autoPOClass}">
                            ${autoPOText}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                            ${statusText}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                        <button class="text-red-600 hover:text-red-900">Delete</button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }
    
    function displayBelowReorderItems(items) {
        const tbody = $('#below-reorder-tbody');
        tbody.empty();
        
        items.forEach(function(item) {
            const row = `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${item.name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.category_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.quantity}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.reorder_point}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.suggested_quantity}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.supplier_name || 'N/A'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button class="text-blue-600 hover:text-blue-900 mr-3">Create PO</button>
                        <button class="text-green-600 hover:text-green-900">Restock</button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }
    
    function displayPurchaseOrders(orders) {
        const tbody = $('#purchase-orders-tbody');
        tbody.empty();
        
        orders.forEach(function(order) {
            const statusClass = getStatusClass(order.status);
            const statusText = order.status.charAt(0).toUpperCase() + order.status.slice(1);
            
            const row = `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${order.po_number}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${order.supplier_name || 'N/A'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$${parseFloat(order.total_amount).toFixed(2)}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                            ${statusText}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${order.order_date || 'N/A'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${order.expected_delivery || 'N/A'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                        <button class="text-green-600 hover:text-green-900">Edit</button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }
    
    function getStatusClass(status) {
        switch(status) {
            case 'draft': return 'bg-gray-100 text-gray-800';
            case 'pending': return 'bg-yellow-100 text-yellow-800';
            case 'approved': return 'bg-blue-100 text-blue-800';
            case 'ordered': return 'bg-purple-100 text-purple-800';
            case 'received': return 'bg-green-100 text-green-800';
            case 'cancelled': return 'bg-red-100 text-red-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }
    
    function addReorderRule() {
        const formData = {
            item_id: $('#rule-item').val(),
            reorder_point: $('#rule-reorder-point').val(),
            reorder_quantity: $('#rule-reorder-quantity').val(),
            lead_time_days: $('#rule-lead-time').val(),
            supplier_id: $('#rule-supplier').val(),
            auto_generate_po: $('#rule-auto-po').is(':checked') ? 1 : 0
        };
        
        $.ajax({
            url: 'api/add-reorder-rule.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#add-rule-modal').addClass('hidden');
                    $('#add-rule-form')[0].reset();
                    loadAutomatedReorderingData();
                } else {
                    alert('Error adding reorder rule: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error adding reorder rule:', error);
                alert('Error adding reorder rule');
            }
        });
    }
    
    function runAutoReorder() {
        $.ajax({
            url: 'api/run-auto-reorder.php',
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Auto reorder completed successfully!');
                    loadAutomatedReorderingData();
                } else {
                    alert('Error running auto reorder: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error running auto reorder:', error);
                alert('Error running auto reorder');
            }
        });
    }
    
    function generatePurchaseOrder() {
        $.ajax({
            url: 'api/generate-purchase-order.php',
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Purchase order generated successfully!');
                    loadAutomatedReorderingData();
                } else {
                    alert('Error generating purchase order: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error generating purchase order:', error);
                alert('Error generating purchase order');
            }
        });
    }
});
</script>
