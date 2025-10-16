<?php
/**
 * Inventory Transactions
 * Hotel PMS Training System for Students
 */

require_once __DIR__ . '/../vps_session_fix.php';
require_once __DIR__ . '/../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if user has appropriate role (only manager and housekeeping)
$user_role = $_SESSION['user_role'] ?? '';
if (!in_array($user_role, ['housekeeping', 'manager'])) {
    header('Location: login.php?error=access_denied');
    exit();
}

// Set page title based on role
$page_title = $user_role === 'housekeeping' ? 'Inventory Transactions' : 'Inventory Transactions';

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
                <div>
                    <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">
                        Inventory Transactions
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">
                        <?php if ($user_role === 'housekeeping'): ?>
                            🧹 Record usage reports and manage inventory transactions
                        <?php else: ?>
                            👨‍💼 View all inventory transactions and usage reports
                        <?php endif; ?>
                    </p>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if ($user_role === 'housekeeping'): ?>
                        <button id="submit-usage-btn" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                            <i class="fas fa-clipboard-check mr-2"></i>Submit Usage Report
                        </button>
                        <button id="new-transaction-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                            <i class="fas fa-plus mr-2"></i>New Transaction
                        </button>
                    <?php else: ?>
                        <button id="export-report-btn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                            <i class="fas fa-download mr-2"></i>Export Report
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Role-based Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <?php if ($user_role === 'housekeeping'): ?>
                    <!-- Housekeeping Statistics -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                    <i class="fas fa-clipboard-list text-white"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">My Usage Reports</p>
                                <p class="text-2xl font-semibold text-gray-900" id="my-usage-reports">0</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                    <i class="fas fa-hand-holding text-white"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Pending Requests</p>
                                <p class="text-2xl font-semibold text-gray-900" id="pending-requests">0</p>
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
                                <p class="text-sm font-medium text-gray-500">Approved Requests</p>
                                <p class="text-2xl font-semibold text-gray-900" id="approved-requests">0</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-orange-500 rounded-md flex items-center justify-center">
                                    <i class="fas fa-exclamation-triangle text-white"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Low Stock Items</p>
                                <p class="text-2xl font-semibold text-gray-900" id="low-stock-items">0</p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Manager Statistics -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                    <i class="fas fa-exchange-alt text-white"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Transactions</p>
                                <p class="text-2xl font-semibold text-gray-900" id="total-transactions">0</p>
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
                                <p class="text-2xl font-semibold text-gray-900" id="total-in">0</p>
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
                                <p class="text-2xl font-semibold text-gray-900" id="total-out">0</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                    <i class="fas fa-clipboard-list text-white"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Usage Reports</p>
                                <p class="text-2xl font-semibold text-gray-900" id="total-usage-reports">0</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Role-based Action Cards -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <?php if ($user_role === 'housekeeping'): ?>
                    <!-- Housekeeping Action Cards -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Submit Usage Report</h3>
                            <i class="fas fa-clipboard-check text-purple-600 text-xl"></i>
                        </div>
                        <p class="text-sm text-gray-500">Record items used during cleaning (e.g., "Used 5 soaps in Room 203")</p>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">New Transaction</h3>
                            <i class="fas fa-plus text-blue-600 text-xl"></i>
                        </div>
                        <p class="text-sm text-gray-500">Record new inventory transactions and stock movements</p>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">View Reports</h3>
                            <i class="fas fa-chart-bar text-green-600 text-xl"></i>
                        </div>
                        <p class="text-sm text-gray-500">View your usage reports and transaction history</p>
                    </div>
                <?php else: ?>
                    <!-- Manager Action Cards -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">View Transactions</h3>
                            <i class="fas fa-eye text-blue-600 text-xl"></i>
                        </div>
                        <p class="text-sm text-gray-500">View all inventory transactions and usage reports</p>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Export Reports</h3>
                            <i class="fas fa-download text-green-600 text-xl"></i>
                        </div>
                        <p class="text-sm text-gray-500">Export transaction data for analysis and reporting</p>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Monitor Activity</h3>
                            <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                        </div>
                        <p class="text-sm text-gray-500">Monitor inventory activity and trends across departments</p>
                    </div>
                <?php endif; ?>
            </div>
                    
            <?php if ($user_role === 'housekeeping'): ?>
                <!-- Housekeeping Usage Report Form -->
                <div id="usage-report-form" class="bg-white rounded-lg shadow p-6 mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Submit Usage Report</h3>
                    <form id="submit-usage-form" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Item Used</label>
                                <select id="usage-item" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    <option value="">Select Item</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity Used</label>
                                <input id="usage-qty" type="number" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Enter quantity used">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Room Number</label>
                                <input id="usage-room" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="e.g., Room 203">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date Used</label>
                                <input id="usage-date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea id="usage-notes" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500" rows="3" placeholder="Additional notes about usage..."></textarea>
                        </div>
                        <div class="flex justify-end space-x-4">
                            <button type="button" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button id="usage-submit" type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md text-sm font-medium hover:bg-purple-700">
                                Submit Usage Report
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Housekeeping Transaction Form -->
                <div id="new-transaction-form" class="bg-white rounded-lg shadow p-6 mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">New Transaction</h3>
                    <form id="record-transaction-form" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Transaction Type</label>
                                <select id="tx-type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select Type</option>
                                    <option value="in">Stock In</option>
                                    <option value="out">Stock Out</option>
                                    <option value="adjustment">Adjustment</option>
                                    <option value="transfer">Transfer</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Item</label>
                                <select id="tx-item" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                                <input id="tx-qty" type="number" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter quantity">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Unit Cost</label>
                                <input id="tx-cost" type="number" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter unit cost">
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
                            <button id="tx-submit" type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                                Record Transaction
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Role-based Data Table -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">
                        <?php if ($user_role === 'housekeeping'): ?>
                            My Usage Reports & Transactions
                        <?php else: ?>
                            All Usage Reports & Transactions
                        <?php endif; ?>
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <?php if ($user_role === 'housekeeping'): ?>
                        <!-- Housekeeping Usage Reports & Transactions Table -->
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room/Location</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="housekeeping-tbody" class="bg-white divide-y divide-gray-200"></tbody>
                        </table>
                    <?php else: ?>
                        <!-- Manager Usage Reports & Transactions Table (read-only) -->
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room/Location</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                </tr>
                            </thead>
                            <tbody id="manager-tbody" class="bg-white divide-y divide-gray-200"></tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </main>

        <!-- Include footer -->
        <?php include '../includes/pos-footer.php'; ?>
    </body>
</html>

<script>
$(document).ready(function() {
    const userRole = '<?php echo $user_role; ?>';
    
    // Load data based on user role
    if (userRole === 'housekeeping') {
        loadHousekeepingData();
        loadHousekeepingTransactions();
    } else {
        loadTransactionData();
        loadManagerTransactions();
    }
    
    loadItemsForForm();
    
    function loadHousekeepingData() {
        // Load housekeeping-specific statistics
        $.ajax({
            url: 'api/get-housekeeping-stats.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    updateHousekeepingStats(response.stats);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading housekeeping stats:', error);
            }
        });
    }
    
    function updateHousekeepingStats(stats) {
        $('#my-usage-reports').text(stats.usage_reports || 0);
        $('#pending-requests').text(stats.pending_requests || 0);
        $('#approved-requests').text(stats.approved_requests || 0);
        $('#low-stock-items').text(stats.low_stock_items || 0);
    }
    
    function loadHousekeepingTransactions() {
        // Load both usage reports and transactions for housekeeping
        $.ajax({
            url: 'api/get-usage-reports.php',
            method: 'GET',
            dataType: 'json',
            success: function(resp) {
                if (!resp.success) return;
                const usageRows = (resp.reports || []).map(function(r) {
                    return `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${r.created_at || ''}</td>
                            <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">Usage</span></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${r.item_name || 'N/A'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${r.quantity || 0}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${r.room || ''}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${r.notes || ''}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button class="text-blue-600 hover:text-blue-900 mr-3" onclick="openEditUsage(${r.id})">Edit</button>
                                <button class="text-red-600 hover:text-red-900" onclick="deleteUsage(${r.id})">Delete</button>
                            </td>
                        </tr>`;
                }).join('');
                
                // Load transactions
                $.ajax({
                    url: 'api/get-transactions.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(txResp) {
                        if (!txResp.success) return;
                        const txRows = (txResp.transactions || []).map(function(tx) {
                            const badge = tx.transaction_type === 'out' ? 'bg-red-100 text-red-800' : (tx.transaction_type === 'in' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800');
                            const qty = (tx.transaction_type === 'out' ? '-' : '+') + Math.abs(tx.quantity || 0);
                            return `
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${tx.created_at || ''}</td>
                                    <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${badge}">${tx.transaction_type || ''}</span></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${tx.item_name || 'N/A'}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${qty}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${tx.location || ''}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${tx.notes || ''}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                                        <button class="text-red-600 hover:text-red-900" onclick="deleteTransaction(${tx.id})">Delete</button>
                                    </td>
                                </tr>`;
                        }).join('');
                        
                        $('#housekeeping-tbody').html(usageRows + txRows);
                    }
                });
            }
        });
    }

    function loadManagerTransactions() {
        // Load all usage reports and transactions for manager (read-only)
        $.ajax({
            url: 'api/get-usage-reports.php',
            method: 'GET',
            dataType: 'json',
            success: function(resp) {
                if (!resp.success) return;
                const usageRows = (resp.reports || []).map(function(r) {
                    return `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${r.created_at || ''}</td>
                            <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">Usage</span></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${r.user_id || 'Unknown'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${r.item_name || 'N/A'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${r.quantity || 0}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${r.room || ''}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${r.notes || ''}</td>
                        </tr>`;
                }).join('');
                
                // Load transactions
                $.ajax({
                    url: 'api/get-transactions.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(txResp) {
                        if (!txResp.success) return;
                        const txRows = (txResp.transactions || []).map(function(tx) {
                            const badge = tx.transaction_type === 'out' ? 'bg-red-100 text-red-800' : (tx.transaction_type === 'in' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800');
                            const qty = (tx.transaction_type === 'out' ? '-' : '+') + Math.abs(tx.quantity || 0);
                            return `
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${tx.created_at || ''}</td>
                                    <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${badge}">${tx.transaction_type || ''}</span></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${tx.performed_by || 'Unknown'}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${tx.item_name || 'N/A'}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${qty}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${tx.location || ''}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${tx.notes || ''}</td>
                                </tr>`;
                        }).join('');
                        
                        $('#manager-tbody').html(usageRows + txRows);
                    }
                });
            }
        });
    }
    
    function getStatusBadge(status) {
        switch(status) {
            case 'pending': return { class: 'bg-yellow-100 text-yellow-800', text: 'Pending' };
            case 'approved': return { class: 'bg-green-100 text-green-800', text: 'Approved' };
            case 'rejected': return { class: 'bg-red-100 text-red-800', text: 'Rejected' };
            case 'completed': return { class: 'bg-blue-100 text-blue-800', text: 'Completed' };
            default: return { class: 'bg-gray-100 text-gray-800', text: status };
        }
    }
    
    function loadTransactionData() {
        // Load transaction statistics for managers
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
        $('#total-usage-reports').text(stats.usage_reports || 0);
        $('#total-transactions').text(stats.total_transactions || 0);
        $('#total-in').text(stats.total_in || 0);
        $('#total-out').text(stats.total_out || 0);
    }

    function loadRecentTransactions(){
        $.ajax({
            url: 'api/get-transactions.php',
            method: 'GET',
            dataType: 'json',
            success: function(resp){
                if (!resp.success) return;
                const rows = (resp.transactions || []).map(function(tx){
                    const badge = tx.transaction_type === 'out' ? 'bg-red-100 text-red-800' : (tx.transaction_type === 'in' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800');
                    const qty = (tx.transaction_type === 'out' ? '-' : '+') + Math.abs(tx.quantity || 0);
                    return `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${tx.created_at || ''}</td>
                            <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${badge}">${tx.transaction_type || ''}</span></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${tx.item_name || 'N/A'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${qty}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${tx.location || ''}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${tx.reference || ''}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button class="text-blue-600 hover:text-blue-900 mr-2">View</button>
                                <button class="text-red-600 hover:text-red-900" onclick="deleteTransaction(${tx.id})">Delete</button>
                            </td>
                        </tr>`;
                }).join('');
                $('#transactions-tbody').html(rows);
            }
        });
    }
    
    // Removed pending requests from this page
    
    function getPriorityBadge(priority) {
        switch(priority) {
            case 'urgent': return { class: 'bg-red-100 text-red-800', text: 'Urgent' };
            case 'high': return { class: 'bg-orange-100 text-orange-800', text: 'High' };
            case 'medium': return { class: 'bg-yellow-100 text-yellow-800', text: 'Medium' };
            case 'low': return { class: 'bg-green-100 text-green-800', text: 'Low' };
            default: return { class: 'bg-gray-100 text-gray-800', text: priority };
        }
    }

    function loadItemsForForm(){
        $.getJSON('api/list-items-simple.php', function(resp){
            const selectors = ['#tx-item', '#usage-item'];
            selectors.forEach(function(sel) {
                const element = $(sel);
                if (element.length) {
                    element.empty();
                    element.append('<option value="">Select Item</option>');
                    (resp.items || []).forEach(function(it){ 
                        element.append(`<option value="${it.id}">${it.label}</option>`); 
                    });
                }
            });
        });
    }

    // Housekeeping form handlers
    $('#submit-usage-form').on('submit', function(e){
        e.preventDefault();
        const payload = {
            item_id: $('#usage-item').val(),
            quantity: $('#usage-qty').val(),
            room: $('#usage-room').val(),
            date_used: $('#usage-date').val(),
            notes: $('#usage-notes').val()
        };
        
        $.ajax({
            url: 'api/submit-usage-report.php',
            method: 'POST',
            data: payload,
            dataType: 'json',
            success: function(r){
                if (r.success){
                    loadHousekeepingData();
                    loadHousekeepingTransactions();
                    alert('Usage report submitted successfully!');
                    $('#submit-usage-form')[0].reset();
                } else {
                    alert('Error: ' + r.message);
                }
            },
            error: function(xhr){ alert('Error: ' + xhr.responseText); }
        });
    });
    
    // Manager form handlers
    $('#record-transaction-form').on('submit', function(e){
        e.preventDefault();
        const payload = { 
            type: $('#tx-type').val(), 
            item_id: $('#tx-item').val(), 
            quantity: $('#tx-qty').val(), 
            unit_cost: $('#tx-cost').val() 
        };
        $.ajax({
            url: 'api/record-transaction.php',
            method: 'POST',
            data: payload,
            dataType: 'json',
            success: function(r){
                if (r.success){
                    loadHousekeepingData();
                    loadHousekeepingTransactions();
                    alert('Transaction recorded successfully!');
                    $('#record-transaction-form')[0].reset();
                } else {
                    alert('Error: ' + r.message);
                }
            },
            error: function(xhr){ alert('Error: ' + xhr.responseText); }
        });
    });
    
    // Button event handlers
    $('#submit-usage-btn').click(function() {
        $('html, body').animate({
            scrollTop: $('#usage-report-form').offset().top - 100
        }, 500);
        $('#usage-report-form input:first').focus();
    });
    
    $('#new-transaction-btn').click(function() {
        $('html, body').animate({
            scrollTop: $('#new-transaction-form').offset().top - 100
        }, 500);
        $('#new-transaction-form input:first').focus();
    });
    
    $('#export-report-btn').click(function() {
        exportTransactionReport();
    });
    
    function exportTransactionReport() {
        $.ajax({
            url: 'api/export-transaction-report.php',
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
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

// Usage report edit/delete
function openEditUsage(id) {
    // Minimal inline prompt editor for speed; can be improved to modal later
    const itemId = prompt('New Item ID (leave blank to keep):');
    const qty = prompt('New Quantity (leave blank to keep):');
    const room = prompt('Room (leave blank to keep):');
    const dateUsed = prompt('Date Used YYYY-MM-DD (leave blank to keep):');
    const notes = prompt('Notes (leave blank to keep):');
    const payload = { id };
    if (itemId) payload.item_id = itemId;
    if (qty) payload.quantity = qty;
    if (room) payload.room = room;
    if (dateUsed) payload.date_used = dateUsed;
    if (notes) payload.notes = notes;
    $.post('api/update-usage-report.php', payload, function(r){
        if (r && r.success) { loadHousekeepingTransactions(); }
        else { alert('Error: ' + (r && r.message)); }
    }, 'json');
}

function deleteUsage(id) {
    if (!confirm('Delete this usage report?')) return;
    $.post('api/delete-usage-report.php', { id }, function(r){
        if (r && r.success) { loadHousekeepingTransactions(); }
        else { alert('Error: ' + (r && r.message)); }
    }, 'json');
}

// Global function for approving requests
function approveRequest(requestId) {
    const notes = prompt('Add notes for approval (optional):');
    if (notes !== null) {
        $.ajax({
            url: 'api/approve-request.php',
            method: 'POST',
            data: { request_id: requestId, action: 'approve', notes: notes },
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    alert('Request approved successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + r.message);
                }
            },
            error: function(xhr) {
                alert('Error: ' + xhr.responseText);
            }
        });
    }
}

// Global function for rejecting requests
function rejectRequest(requestId) {
    const notes = prompt('Reason for rejection:');
    if (notes !== null && notes.trim() !== '') {
        $.ajax({
            url: 'api/approve-request.php',
            method: 'POST',
            data: { request_id: requestId, action: 'reject', notes: notes },
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    alert('Request rejected successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + r.message);
                }
            },
            error: function(xhr) {
                alert('Error: ' + xhr.responseText);
            }
        });
    }
}

// Global function for deleting transactions
function deleteTransaction(transactionId) {
    if (confirm('Are you sure you want to delete this transaction? This action cannot be undone.')) {
        $.ajax({
            url: 'api/delete-transaction.php',
            method: 'POST',
            data: { transaction_id: transactionId },
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    alert('Transaction deleted successfully!');
                    loadHousekeepingTransactions();
                } else {
                    alert('Error: ' + r.message);
                }
            },
            error: function(xhr) {
                alert('Error: ' + xhr.responseText);
            }
        });
    }
    
    // Handle URL parameters for quick actions
    function handleUrlParameters() {
        const urlParams = new URLSearchParams(window.location.search);
        const action = urlParams.get('action');
        
        if (action === 'record') {
            // Open record usage modal for housekeeping
            <?php if ($user_role === 'housekeeping'): ?>
                openUsageReportModal();
            <?php endif; ?>
        }
    }
    
    // Call on page load
    $(document).ready(function() {
        handleUrlParameters();
    });
}
</script>