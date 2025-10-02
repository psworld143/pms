<?php
/**
 * Inventory Transactions
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
$page_title = 'Inventory Transactions';

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
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Inventory Transactions</h2>
                <div class="flex items-center space-x-4">
                    <button id="new-transaction-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-plus mr-2"></i>New Transaction
                    </button>
                    <button id="export-report-btn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-download mr-2"></i>Export Report
                    </button>
                </div>
            </div>

            <!-- Transaction Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-exchange-alt text-white"></i>
                            </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Transactions</p>
                            <p class="text-2xl font-semibold text-gray-900">1,247</p>
                        </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-arrow-up text-white"></i>
                            </div>
                    </div>
                    <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Stock In</p>
                            <p class="text-2xl font-semibold text-gray-900">856</p>
                        </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-arrow-down text-white"></i>
                            </div>
                    </div>
                    <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Stock Out</p>
                            <p class="text-2xl font-semibold text-gray-900">391</p>
                        </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-dollar-sign text-white"></i>
                            </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Value</p>
                            <p class="text-2xl font-semibold text-gray-900" id="total-transaction-value">$0</p>
                        </div>
                </div>
            </div>
        </div>

            <!-- Transaction Types -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Stock In -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Stock In</h3>
                        <i class="fas fa-arrow-up text-green-600 text-xl"></i>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Purchase Orders</p>
                                <p class="text-sm text-gray-500">New inventory received</p>
                            </div>
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">456</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Returns</p>
                                <p class="text-sm text-gray-500">Items returned to stock</p>
                            </div>
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">89</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                                <p class="font-medium text-gray-900">Adjustments</p>
                                <p class="text-sm text-gray-500">Stock level corrections</p>
                            </div>
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">23</span>
                        </div>
                    </div>
                </div>

                <!-- Stock Out -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Stock Out</h3>
                        <i class="fas fa-arrow-down text-red-600 text-xl"></i>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Room Service</p>
                                <p class="text-sm text-gray-500">Items used in rooms</p>
                            </div>
                            <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full">234</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Restaurant</p>
                                <p class="text-sm text-gray-500">Kitchen and dining items</p>
                            </div>
                            <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full">156</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                                <p class="font-medium text-gray-900">Waste/Damage</p>
                                <p class="text-sm text-gray-500">Items removed from stock</p>
                            </div>
                            <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full">45</span>
                        </div>
                    </div>
                </div>

                <!-- Transfer -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Transfers</h3>
                        <i class="fas fa-exchange-alt text-blue-600 text-xl"></i>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Between Locations</p>
                                <p class="text-sm text-gray-500">Inter-department transfers</p>
                            </div>
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full" id="between-locations-count">0</span>
            </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">External</p>
                                <p class="text-sm text-gray-500">Transfers to other properties</p>
        </div>
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full" id="external-transfers-count">0</span>
            </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Loan</p>
                                <p class="text-sm text-gray-500">Temporary item loans</p>
                                    </div>
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full" id="loan-transfers-count">0</span>
            </div>
        </div>
                </div>
            </div>
                    
            <!-- New Transaction Form -->
            <div id="new-transaction-form" class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">New Transaction</h3>
                <form class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Transaction Type</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option>Select Type</option>
                                <option>Stock In - Purchase</option>
                                <option>Stock In - Return</option>
                                <option>Stock In - Adjustment</option>
                                <option>Stock Out - Usage</option>
                                <option>Stock Out - Waste</option>
                                <option>Transfer - Internal</option>
                                <option>Transfer - External</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Item</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option>Select Item</option>
                                <option>Coffee Beans - CB-001</option>
                                <option>Hand Soap - HS-002</option>
                                <option>Towels - TW-003</option>
                                <option>Detergent - DT-004</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                            <input type="number" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter quantity">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Unit Cost</label>
                            <input type="number" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter unit cost">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">From Location</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option>Select Location</option>
                                <option>Main Storage</option>
                                <option>Kitchen</option>
                                <option>Housekeeping</option>
                                <option>Restaurant</option>
                                <option>External Supplier</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">To Location</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option>Select Location</option>
                                <option>Main Storage</option>
                                <option>Kitchen</option>
                                <option>Housekeeping</option>
                                <option>Restaurant</option>
                                <option>External Customer</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reference Number</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter reference number (PO, Invoice, etc.)">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3" placeholder="Enter transaction notes"></textarea>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                            Record Transaction
                        </button>
                    </div>
                </form>
            </div>

            <!-- Transactions Table -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Recent Transactions</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">From/To</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2024-01-15</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Stock In
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <div class="h-8 w-8 rounded-full bg-orange-500 flex items-center justify-center">
                                                <i class="fas fa-coffee text-white text-xs"></i>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">Coffee Beans</div>
                                            <div class="text-sm text-gray-500">CB-001</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">+50</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Supplier → Main Storage</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">PO-2024-001</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                                    <button class="text-green-600 hover:text-green-900">Edit</button>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2024-01-14</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Stock Out
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <div class="h-8 w-8 rounded-full bg-purple-500 flex items-center justify-center">
                                                <i class="fas fa-soap text-white text-xs"></i>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">Hand Soap</div>
                                            <div class="text-sm text-gray-500">HS-002</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-12</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Main Storage → Housekeeping</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">REQ-2024-045</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                                    <button class="text-green-600 hover:text-green-900">Edit</button>
                                </td>
                            </tr>
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
    loadTransactionData();
    
    function loadTransactionData() {
        // Load transaction statistics
        $.ajax({
            url: 'api/get-transaction-stats.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    updateTransactionStats(response.stats);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading transaction stats:', error);
            }
        });
    }
    
    function updateTransactionStats(stats) {
        $('#total-transaction-value').text('$' + stats.total_value.toLocaleString());
        $('#between-locations-count').text(stats.between_locations);
        $('#external-transfers-count').text(stats.external_transfers);
        $('#loan-transfers-count').text(stats.loan_transfers);
    }
    
    // Button event handlers
    $('#new-transaction-btn').click(function() {
        showNewTransactionModal();
    });
    
    $('#export-report-btn').click(function() {
        exportTransactionReport();
    });
    
    function showNewTransactionModal() {
        // Show the new transaction form (it's already in the HTML)
        $('html, body').animate({
            scrollTop: $('#new-transaction-form').offset().top - 100
        }, 500);
        
        // Focus on the first input
        $('#new-transaction-form input:first').focus();
    }
    
    function exportTransactionReport() {
        $.ajax({
            url: 'api/export-transaction-report.php',
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Create download link
                    const link = document.createElement('a');
                    link.href = response.download_url;
                    link.download = 'transaction_report_' + new Date().toISOString().split('T')[0] + '.csv';
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
});
</script>