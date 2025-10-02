<?php
/**
 * Inventory Reports
 * Hotel PMS Training System for Students
 */

session_start();
require_once __DIR__ . '/config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Set page title
$page_title = 'Inventory Reports';

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
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Inventory Reports</h2>
                <div class="flex items-center space-x-4">
                    <button id="export-report-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-download mr-2"></i>Export Report
                    </button>
                    <button id="print-report-btn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-print mr-2"></i>Print Report
                    </button>
                </div>
            </div>

            <!-- Report Summary -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-boxes text-white"></i>
                            </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Items</p>
                            <p class="text-2xl font-semibold text-gray-900">1,247</p>
                        </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-dollar-sign text-white"></i>
                            </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Value</p>
                            <p class="text-2xl font-semibold text-gray-900" id="total-inventory-value">$0</p>
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
                        <p class="text-sm font-medium text-gray-500">Low Stock Items</p>
                            <p class="text-2xl font-semibold text-gray-900">67</p>
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
                            <p class="text-2xl font-semibold text-gray-900">24</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Filters -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Report Filters</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option>Stock Level Report</option>
                            <option>Transaction Report</option>
                            <option>Value Report</option>
                            <option>Low Stock Report</option>
                            <option>Category Report</option>
                            <option>Supplier Report</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option>All Categories</option>
                            <option>Food & Beverage</option>
                            <option>Amenities</option>
                            <option>Cleaning Supplies</option>
                            <option>Office Supplies</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option>Last 7 Days</option>
                            <option>Last 30 Days</option>
                            <option>Last 3 Months</option>
                            <option>Last 6 Months</option>
                            <option>This Year</option>
                            <option>Custom Range</option>
                        </select>
        </div>
                    <div class="flex items-end">
                        <button id="generate-report-btn" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-chart-bar mr-2"></i>Generate
                        </button>
                </div>
                </div>
            </div>

            <!-- Report Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Stock Level Chart -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Stock Level Distribution</h3>
                    <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
                        <div class="text-center">
                            <i class="fas fa-chart-pie text-4xl text-gray-400 mb-4"></i>
                            <p class="text-gray-600">Stock level chart would be displayed here</p>
                            <p class="text-sm text-gray-500">Integration with chart library needed</p>
                        </div>
                    </div>
                </div>

                <!-- Category Value Chart -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Category Value Distribution</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-orange-500 rounded mr-3"></div>
                                <span class="text-sm text-gray-700">Food & Beverage</span>
                            </div>
                            <div class="flex items-center">
                                <span class="text-sm font-medium text-gray-900 mr-2">40%</span>
                                <div class="w-20 bg-gray-200 rounded-full h-2">
                                    <div class="bg-orange-500 h-2 rounded-full" style="width: 40%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-purple-500 rounded mr-3"></div>
                                <span class="text-sm text-gray-700">Amenities</span>
                            </div>
                            <div class="flex items-center">
                                <span class="text-sm font-medium text-gray-900 mr-2">27%</span>
                                <div class="w-20 bg-gray-200 rounded-full h-2">
                                    <div class="bg-purple-500 h-2 rounded-full" style="width: 27%"></div>
                </div>
            </div>
        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-blue-500 rounded mr-3"></div>
                                <span class="text-sm text-gray-700">Cleaning Supplies</span>
                            </div>
                            <div class="flex items-center">
                                <span class="text-sm font-medium text-gray-900 mr-2">19%</span>
                                <div class="w-20 bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full" style="width: 19%"></div>
                                </div>
                            </div>
                </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-green-500 rounded mr-3"></div>
                                <span class="text-sm text-gray-700">Office Supplies</span>
                                    </div>
                            <div class="flex items-center">
                                <span class="text-sm font-medium text-gray-900 mr-2">14%</span>
                                <div class="w-20 bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full" style="width: 14%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Reports -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Low Stock Alert</h3>
                        <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                    </div>
                    <div class="text-3xl font-bold text-yellow-600 mb-2">67 Items</div>
                    <div class="text-sm text-gray-600">Items below minimum stock level</div>
                    <button id="view-low-stock-btn" class="mt-3 w-full bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-2 rounded text-sm">
                        View Details
                    </button>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">High Value Items</h3>
                        <i class="fas fa-dollar-sign text-green-600"></i>
                                    </div>
                    <div class="text-3xl font-bold text-green-600 mb-2" id="high-value-items">$0</div>
                    <div class="text-sm text-gray-600">Top 10% most valuable items</div>
                    <button id="view-high-value-btn" class="mt-3 w-full bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm">
                        View Details
                    </button>
                                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Fast Moving</h3>
                        <i class="fas fa-chart-line text-blue-600"></i>
                        </div>
                    <div class="text-3xl font-bold text-blue-600 mb-2">234 Items</div>
                    <div class="text-sm text-gray-600">High turnover items this month</div>
                    <button id="view-fast-moving-btn" class="mt-3 w-full bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm">
                        View Details
                    </button>
            </div>
        </div>

            <!-- Detailed Report Table -->
            <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Inventory Summary Report</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min Level</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Cost</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Value</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="inventory-report-tbody" class="bg-white divide-y divide-gray-200">
                        <!-- Inventory report data will be loaded dynamically -->
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
    loadReportsData();
    
    function loadReportsData() {
        // Load inventory statistics
        $.ajax({
            url: 'api/get-inventory-stats.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    updateReportStats(response.statistics);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading inventory stats:', error);
            }
        });
        
        // Load inventory items for detailed report
        $.ajax({
            url: 'api/get-inventory-items.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayInventoryReport(response.inventory_items);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading inventory items:', error);
            }
        });
    }
    
    function updateReportStats(stats) {
        // Calculate total inventory value
        let totalValue = 0;
        stats.category_stats.forEach(function(category) {
            // This is a simplified calculation - in a real system, you'd get this from the API
            totalValue += category.item_count * 50; // Estimated average value per item
        });
        
        $('#total-inventory-value').text('$' + totalValue.toLocaleString());
        
        // Calculate high value items (top 10%)
        const highValueItems = Math.round(totalValue * 0.1);
        $('#high-value-items').text('$' + highValueItems.toLocaleString());
    }
    
    function displayInventoryReport(items) {
        const tbody = $('#inventory-report-tbody');
        tbody.empty();
        
        if (items.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                        No inventory items found
                    </td>
                </tr>
            `);
            return;
        }
        
        // Show only first 10 items for performance
        const displayItems = items.slice(0, 10);
        
        displayItems.forEach(function(item) {
            const statusClass = getStockStatusClass(item.stock_status);
            const statusText = getStockStatusText(item.stock_status);
            const totalValue = item.quantity * item.unit_price;
            
            const row = `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-full flex items-center justify-center" style="background-color: ${item.category_color}20;">
                                    <i class="${item.category_icon} text-lg" style="color: ${item.category_color};"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">${item.name}</div>
                                <div class="text-sm text-gray-500">${item.sku || 'N/A'}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.category_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.quantity}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.minimum_stock}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$${parseFloat(item.unit_price).toFixed(2)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$${totalValue.toFixed(2)}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                            ${statusText}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button class="text-blue-600 hover:text-blue-900 mr-3" onclick="viewItem(${item.id})">View</button>
                        <button class="text-green-600 hover:text-green-900" onclick="exportItem(${item.id})">Export</button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }
    
    function getStockStatusClass(status) {
        switch(status) {
            case 'in_stock': return 'bg-green-100 text-green-800';
            case 'low_stock': return 'bg-yellow-100 text-yellow-800';
            case 'out_of_stock': return 'bg-red-100 text-red-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }
    
    function getStockStatusText(status) {
        switch(status) {
            case 'in_stock': return 'In Stock';
            case 'low_stock': return 'Low Stock';
            case 'out_of_stock': return 'Out of Stock';
            default: return 'Unknown';
        }
    }
    
    window.viewItem = function(itemId) {
        window.location.href = 'items.php?id=' + itemId;
    };
    
    window.exportItem = function(itemId) {
        alert('Export functionality would be implemented here for item ID: ' + itemId);
    };
    
    // Button event handlers
    $('#export-report-btn').click(function() {
        exportInventoryReport();
    });
    
    $('#print-report-btn').click(function() {
        printInventoryReport();
    });
    
    $('#generate-report-btn').click(function() {
        generateCustomReport();
    });
    
    $('#view-low-stock-btn').click(function() {
        viewLowStockItems();
    });
    
    $('#view-high-value-btn').click(function() {
        viewHighValueItems();
    });
    
    $('#view-fast-moving-btn').click(function() {
        viewFastMovingItems();
    });
    
    function exportInventoryReport() {
        $.ajax({
            url: 'api/export-inventory-report.php',
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const link = document.createElement('a');
                    link.href = response.download_url;
                    link.download = 'inventory_report_' + new Date().toISOString().split('T')[0] + '.csv';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                } else {
                    alert('Error exporting report: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error exporting report:', error);
                alert('Error exporting report');
            }
        });
    }
    
    function printInventoryReport() {
        window.print();
    }
    
    function generateCustomReport() {
        const reportType = $('#report-type').val();
        const dateRange = $('#date-range').val();
        
        if (!reportType) {
            alert('Please select a report type');
            return;
        }
        
        // Reload data with filters
        loadReportsData();
        alert('Custom report generated successfully!');
    }
    
    function viewLowStockItems() {
        window.location.href = 'items.php?filter=low_stock';
    }
    
    function viewHighValueItems() {
        window.location.href = 'items.php?filter=high_value';
    }
    
    function viewFastMovingItems() {
        window.location.href = 'items.php?filter=fast_moving';
    }
});
</script>