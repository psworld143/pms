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
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
    <script></script>
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
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
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
                                <select id="usage-room" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    <option value="">Select Room</option>
                                </select>
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
                if (response && response.success) {
                    const s = response.data || response.stats || response.statistics || {};
                    updateHousekeepingStats(s);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading housekeeping stats:', error);
            }
        });
    }
    
    function updateHousekeepingStats(stats) {
        $('#my-usage-reports').text((stats.usage_reports ?? stats.total_items) ?? 0);
        $('#pending-requests').text(stats.pending_requests ?? 0);
        $('#approved-requests').text(stats.approved_requests ?? 0);
        $('#low-stock-items').text((stats.missing_items ?? stats.low_stock_items) ?? 0);
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
                                <button class="view-usage-btn inline-flex items-center px-3 py-1.5 rounded-md bg-gray-50 text-gray-700 hover:bg-gray-100 mr-2"
                                    data-id="${r.id}" data-item="${(r.item_name || '').replace(/"/g,'&quot;')}" data-quantity="${r.quantity || ''}"
                                    data-room="${(r.room || '').replace(/"/g,'&quot;')}" data-date="${r.date_used || r.created_at || ''}" data-notes="${(r.notes || '').replace(/"/g,'&quot;')}">
                                    <i class="fas fa-eye mr-1"></i>View
                                </button>
                                <button class="edit-usage-btn inline-flex items-center px-3 py-1.5 rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 mr-2"
                                    data-id="${r.id}" data-item-id="${r.item_id || ''}" data-quantity="${r.quantity || ''}"
                                    data-room="${r.room || ''}" data-date="${r.date_used || ''}" data-notes="${(r.notes || '').replace(/"/g,'&quot;')}">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </button>
                                <button class="delete-usage-btn inline-flex items-center px-3 py-1.5 rounded-md bg-red-50 text-red-700 hover:bg-red-100"
                                    data-id="${r.id}">
                                    <i class="fas fa-trash mr-1"></i>Delete
                                </button>
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
                                        <button class="view-tx-btn inline-flex items-center px-3 py-1.5 rounded-md bg-gray-50 text-gray-700 hover:bg-gray-100 mr-2"
                                            data-id="${tx.id}" data-type="${tx.transaction_type || ''}" data-created="${tx.created_at || ''}"
                                            data-item="${(tx.item_name || '').replace(/"/g,'&quot;')}" data-qty="${qty}" data-location="${(tx.location || '').replace(/"/g,'&quot;')}"
                                            data-notes="${(tx.notes || '').replace(/"/g,'&quot;')}" data-ref="${(tx.reference || '').replace(/"/g,'&quot;')}">
                                            <i class="fas fa-eye mr-1"></i>View
                                        </button>
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
        // rooms for dropdown
        $.getJSON('api/get-all-rooms.php', function(resp){
            const sel = $('#usage-room');
            if (!sel.length) return;
            sel.empty();
            sel.append('<option value="">Select Room</option>');
            (resp.rooms || []).forEach(function(r){
                const label = (r.room_number || r.id) + (r.room_type ? (' • ' + r.room_type) : '');
                sel.append(`<option value="${r.room_number || r.id}">${label}</option>`);
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
                    // Open PDF in a new tab for print
                    window.open(response.download_url, '_blank');
                } else {
                    alert('Error exporting report: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                let msg = 'Error exporting report';
                try { const j = JSON.parse(xhr.responseText || '{}'); if (j.debug) msg += ': ' + j.debug; } catch(e) {}
                console.error('Error exporting report:', error);
                alert(msg);
            }
        });
    }
});

// Styled edit/delete modals
function buildModal(html){
    const wrapper = document.createElement('div');
    wrapper.innerHTML = `
    <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-lg rounded-lg shadow-xl overflow-hidden">${html}</div>
    </div>`;
    document.body.appendChild(wrapper);
    return wrapper;
}

function openViewUsageModal(data){
    const modal = buildModal(`
        <div class=\"px-5 py-4 border-b border-gray-200 flex items-center justify-between\"> 
            <h3 class=\"text-lg font-semibold text-gray-800\"><i class=\"fas fa-eye mr-2 text-gray-600\"></i>Usage Report</h3>
            <button class=\"close-modal text-gray-500 hover:text-gray-700\"><i class=\"fas fa-times\"></i></button>
        </div>
        <div class=\"p-5 space-y-3 text-sm text-gray-700\">
            <div><span class=\"font-medium\">Item:</span> ${data.item || 'N/A'}</div>
            <div><span class=\"font-medium\">Quantity:</span> ${data.quantity || 0}</div>
            <div><span class=\"font-medium\">Room:</span> ${data.room || ''}</div>
            <div><span class=\"font-medium\">Date:</span> ${(data.date || '').substring(0,10)}</div>
            <div><span class=\"font-medium\">Notes:</span> ${data.notes || ''}</div>
        </div>
        <div class=\"px-5 py-4 border-t border-gray-200 flex justify-end\">
            <button class=\"close-modal px-4 py-2 border rounded-md\">Close</button>
        </div>`);
    $(modal).on('click', '.close-modal', function(){ document.body.removeChild(modal); });
}

function openViewTransactionModal(data){
    const modal = buildModal(`
        <div class=\"px-5 py-4 border-b border-gray-200 flex items-center justify-between\"> 
            <h3 class=\"text-lg font-semibold text-gray-800\"><i class=\"fas fa-eye mr-2 text-gray-600\"></i>Transaction</h3>
            <button class=\"close-modal text-gray-500 hover:text-gray-700\"><i class=\"fas fa-times\"></i></button>
        </div>
        <div class=\"p-5 space-y-3 text-sm text-gray-700\">
            <div><span class=\"font-medium\">Type:</span> ${data.type || ''}</div>
            <div><span class=\"font-medium\">Date:</span> ${data.created || ''}</div>
            <div><span class=\"font-medium\">Item:</span> ${data.item || ''}</div>
            <div><span class=\"font-medium\">Quantity:</span> ${data.qty || ''}</div>
            <div><span class=\"font-medium\">Location:</span> ${data.location || ''}</div>
            <div><span class=\"font-medium\">Reference:</span> ${data.ref || ''}</div>
            <div><span class=\"font-medium\">Notes:</span> ${data.notes || ''}</div>
        </div>
        <div class=\"px-5 py-4 border-t border-gray-200 flex justify-end\">
            <button class=\"close-modal px-4 py-2 border rounded-md\">Close</button>
        </div>`);
    $(modal).on('click', '.close-modal', function(){ document.body.removeChild(modal); });
}

function openEditUsageModal(data){
    const modal = buildModal(`
        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800"><i class="fas fa-edit mr-2 text-blue-600"></i>Edit Usage Report</h3>
            <button class="close-modal text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-5 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Item</label>
                <select id="edit-item" class="w-full px-3 py-2 border rounded-md"></select>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                    <input id="edit-qty" type="number" min="1" class="w-full px-3 py-2 border rounded-md" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date Used</label>
                    <input id="edit-date" type="date" class="w-full px-3 py-2 border rounded-md" />
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Room</label>
                <input id="edit-room" type="text" class="w-full px-3 py-2 border rounded-md" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea id="edit-notes" rows="3" class="w-full px-3 py-2 border rounded-md"></textarea>
            </div>
        </div>
        <div class="px-5 py-4 border-t border-gray-200 flex justify-end space-x-3">
            <button class="close-modal px-4 py-2 border rounded-md">Cancel</button>
            <button id="save-edit-usage" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save</button>
        </div>`);

    // load items
    $.getJSON('api/list-items-simple.php', function(resp){
        const sel = $(modal).find('#edit-item');
        sel.empty(); sel.append('<option value="">Select Item</option>');
        (resp.items || []).forEach(function(it){ sel.append(`<option value="${it.id}">${it.label}</option>`); });
        if (data.item_id) sel.val(String(data.item_id));
    });

    $(modal).find('#edit-qty').val(data.quantity || '');
    $(modal).find('#edit-date').val((data.date_used || '').substring(0,10));
    $(modal).find('#edit-room').val(data.room || '');
    $(modal).find('#edit-notes').val(data.notes || '');

    $(modal).on('click', '.close-modal', function(){ document.body.removeChild(modal); });
    $(modal).find('#save-edit-usage').on('click', function(){
        const payload = {
            id: data.id,
            item_id: $(modal).find('#edit-item').val(),
            quantity: $(modal).find('#edit-qty').val(),
            room: $(modal).find('#edit-room').val(),
            date_used: $(modal).find('#edit-date').val(),
            notes: $(modal).find('#edit-notes').val()
        };
    $.post('api/update-usage-report.php', payload, function(r){
        if (r && r.success) { document.body.removeChild(modal); if (typeof loadHousekeepingTransactions === 'function') { loadHousekeepingTransactions(); } }
            else { alert('Error: ' + (r && r.message)); }
        }, 'json');
    });
}

function openDeleteUsageModal(id){
    const modal = buildModal(`
        <div class="px-5 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800"><i class="fas fa-trash mr-2 text-red-600"></i>Delete Usage Report</h3>
        </div>
        <div class="p-5 text-gray-700">Are you sure you want to delete this usage report? This action cannot be undone.</div>
        <div class="px-5 py-4 border-t border-gray-200 flex justify-end space-x-3">
            <button class="close-modal px-4 py-2 border rounded-md">Cancel</button>
            <button id="confirm-delete-usage" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Delete</button>
        </div>`);
    $(modal).on('click', '.close-modal', function(){ document.body.removeChild(modal); });
    $(modal).find('#confirm-delete-usage').on('click', function(){
        $.post('api/delete-usage-report.php', { id }, function(r){
            if (r && r.success) { document.body.removeChild(modal); if (typeof loadHousekeepingTransactions === 'function') { loadHousekeepingTransactions(); } }
            else { alert('Error: ' + (r && r.message)); }
        }, 'json');
    });
}

// Delegated button handlers
$(document).on('click', '.edit-usage-btn', function(){
    const btn = $(this);
    openEditUsageModal({
        id: parseInt(btn.data('id'), 10),
        item_id: btn.data('item-id'),
        quantity: btn.data('quantity'),
        room: btn.data('room'),
        date_used: btn.data('date'),
        notes: btn.data('notes')
    });
});

$(document).on('click', '.delete-usage-btn', function(){
    openDeleteUsageModal(parseInt($(this).data('id'), 10));
});

$(document).on('click', '.view-usage-btn', function(){
    const b = $(this);
    openViewUsageModal({
        id: parseInt(b.data('id'), 10),
        item: b.data('item'),
        quantity: b.data('quantity'),
        room: b.data('room'),
        date: b.data('date'),
        notes: b.data('notes')
    });
});

$(document).on('click', '.view-tx-btn', function(){
    const b = $(this);
    openViewTransactionModal({
        id: parseInt(b.data('id'), 10),
        type: b.data('type'),
        created: b.data('created'),
        item: b.data('item'),
        qty: b.data('qty'),
        location: b.data('location'),
        notes: b.data('notes'),
        ref: b.data('ref')
    });
});

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