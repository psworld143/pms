<?php
/**
 * Accounting Integration
 * Hotel PMS Training System - Inventory Module
 */

require_once __DIR__ . '/../vps_session_fix.php';
require_once __DIR__ . '/../includes/database.php';

// Debug: Check what's happening
error_log("Accounting module accessed. User ID: " . ($_SESSION['user_id'] ?? 'NOT SET'));
error_log("User Role: " . ($_SESSION['user_role'] ?? 'NOT SET'));

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("User not logged in, redirecting to login");
    header('Location: login.php');
    exit();
}

// Check if user has appropriate role (only manager)
$user_role = $_SESSION['user_role'] ?? '';
if ($user_role !== 'manager') {
    error_log("User is not manager, redirecting to index. Role: " . $user_role);
    header('Location: index.php?error=access_denied');
    exit();
}

error_log("Accounting module proceeding normally");

// Test database connection
try {
    global $pdo;
    if (!$pdo) {
        throw new Exception('Database connection not available');
    }
    error_log("Database connection OK");
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    die("Database connection error. Please check the logs.");
}

// Set page title
$page_title = 'Accounting Integration';

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Accounting Integration</h2>
                <div class="flex items-center space-x-4">
                    <button id="sync-accounting-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-sync mr-2"></i>Sync with Accounting
                    </button>
                    <button id="export-journal-btn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-file-pdf mr-2"></i>Export to PDF
                    </button>
                </div>
            </div>

            <!-- Financial Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-peso-sign text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Inventory Value</p>
                            <p class="text-2xl font-semibold text-gray-900" id="total-inventory-value">₱0</p>
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
                            <p class="text-sm font-medium text-gray-500">Monthly Purchases</p>
                            <p class="text-2xl font-semibold text-gray-900" id="monthly-purchases">₱0</p>
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
                            <p class="text-sm font-medium text-gray-500">Monthly Usage</p>
                            <p class="text-2xl font-semibold text-gray-900" id="monthly-usage">₱0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-chart-line text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">COGS (30d)</p>
                            <p class="text-2xl font-semibold text-gray-900" id="cogs-30d">₱0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Inventory Value Trend -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Inventory Value Trend</h3>
                    <div class="h-64">
                        <canvas id="inventory-value-chart"></canvas>
                    </div>
                </div>

                <!-- Cost of Goods Sold -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Cost of Goods Sold</h3>
                    <div class="h-64">
                        <canvas id="cogs-chart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Journal Entries -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-800">Journal Entries</h3>
                    <div class="flex space-x-2">
                        <select id="journal-status-filter" class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="posted">Posted</option>
                            <option value="reversed">Reversed</option>
                        </select>
                        <button id="refresh-journal-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-sm">
                            <i class="fas fa-sync"></i>
                        </button>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Debit</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credit</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="journal-entries-tbody" class="bg-white divide-y divide-gray-200">
                            <!-- Journal entries will be loaded dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Account Mapping -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-800">Account Mapping</h3>
                    <button id="edit-mapping-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-edit mr-2"></i>Edit Mapping
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-800 mb-3">Inventory Accounts</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Raw Materials</span>
                                <span class="text-sm font-medium">1200</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Finished Goods</span>
                                <span class="text-sm font-medium">1210</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Supplies</span>
                                <span class="text-sm font-medium">1220</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-800 mb-3">Expense Accounts</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Cost of Goods Sold</span>
                                <span class="text-sm font-medium">5000</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Supplies Expense</span>
                                <span class="text-sm font-medium">5100</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Waste Expense</span>
                                <span class="text-sm font-medium">5200</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-800 mb-3">Liability Accounts</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Accounts Payable</span>
                                <span class="text-sm font-medium">2000</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Accrued Expenses</span>
                                <span class="text-sm font-medium">2100</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Integration Status -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Integration Status</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-check text-green-600 text-xl"></i>
                        </div>
                        <h4 class="text-sm font-medium text-gray-800">Database Connection</h4>
                        <p class="text-xs text-gray-600 mt-1">Connected</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-check text-green-600 text-xl"></i>
                        </div>
                        <h4 class="text-sm font-medium text-gray-800">Journal Entries</h4>
                        <p class="text-xs text-gray-600 mt-1">Active</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                        </div>
                        <h4 class="text-sm font-medium text-gray-800">Auto Sync</h4>
                        <p class="text-xs text-gray-600 mt-1">Manual</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-check text-green-600 text-xl"></i>
                        </div>
                        <h4 class="text-sm font-medium text-gray-800">Reports</h4>
                        <p class="text-xs text-gray-600 mt-1">Available</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Include footer -->
        <?php include '../includes/pos-footer.php'; ?>
    </body>
</html>

<script>
$(document).ready(function() {
    let inventoryValueChart, cogsChart;
    
    // Load initial data
    loadAccountingData();
    loadJournalEntries();
    initializeCharts();
    
    // Event handlers
    $('#sync-accounting-btn').click(function() {
        syncWithAccounting();
    });
    
    $('#export-journal-btn').click(function() {
        exportJournalEntries();
    });
    
    $('#refresh-journal-btn').click(function() {
        loadJournalEntries();
    });
    
    $('#journal-status-filter').change(function() {
        loadJournalEntries();
    });
    
    $('#edit-mapping-btn').click(function() {
        editAccountMapping();
    });
    
    // Event delegation for dynamically created buttons
    $(document).on('click', 'button[onclick*="viewJournalEntry"]', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const entryId = $(this).attr('onclick').match(/viewJournalEntry\((\d+)\)/)[1];
        viewJournalEntry(entryId);
    });
    
    $(document).on('click', 'button[onclick*="postJournalEntry"]', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const entryId = $(this).attr('onclick').match(/postJournalEntry\((\d+)\)/)[1];
        postJournalEntry(entryId);
    });
    
    // Global close functions for debugging
    window.closeJournalEntryModal = closeJournalEntryModal;
    window.closeAccountMappingModal = closeAccountMappingModal;
    
    function loadAccountingData() {
        $.ajax({
            url: 'api/get-accounting-data.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    updateFinancialOverview(response.financial_data);
                    updateCharts(response.chart_data);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading accounting data:', error);
            }
        });
    }
    
    function loadJournalEntries() {
        const status = $('#journal-status-filter').val();
        
        $.ajax({
            url: 'api/get-journal-entries.php',
            method: 'GET',
            data: { status: status },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayJournalEntries(response.entries);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading journal entries:', error);
            }
        });
    }
    
    function updateFinancialOverview(data) {
        $('#total-inventory-value').text('₱' + data.total_inventory_value.toLocaleString());
        $('#monthly-purchases').text('₱' + data.monthly_purchases.toLocaleString());
        $('#monthly-usage').text('₱' + data.monthly_usage.toLocaleString());
        $('#cogs-30d').text('₱' + data.cogs_30d.toLocaleString());
    }
    
    function displayJournalEntries(entries) {
        const tbody = $('#journal-entries-tbody');
        tbody.empty();
        
        if (entries.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                        No journal entries found
                    </td>
                </tr>
            `);
            return;
        }
        
        entries.forEach(function(entry) {
            const statusClass = getJournalStatusClass(entry.status);
            const statusText = entry.status.charAt(0).toUpperCase() + entry.status.slice(1);
            
            const row = `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${entry.created_at}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${entry.reference_number || 'N/A'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${entry.account_code}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${entry.description}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${entry.debit_amount > 0 ? '₱' + parseFloat(entry.debit_amount).toFixed(2) : '-'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${entry.credit_amount > 0 ? '₱' + parseFloat(entry.credit_amount).toFixed(2) : '-'}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                            ${statusText}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <button type="button" onclick="viewJournalEntry(${entry.id})" class="inline-flex items-center px-3 py-1 border border-blue-300 text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-md text-xs font-medium transition-colors cursor-pointer">
                                <i class="fas fa-eye mr-1"></i>
                                View
                            </button>
                            ${entry.status === 'pending' ? `
                                <button type="button" onclick="postJournalEntry(${entry.id})" class="inline-flex items-center px-3 py-1 border border-green-300 text-green-700 bg-green-50 hover:bg-green-100 rounded-md text-xs font-medium transition-colors cursor-pointer">
                                    <i class="fas fa-check mr-1"></i>
                                    Post
                                </button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }
    
    function getJournalStatusClass(status) {
        switch(status) {
            case 'pending': return 'bg-yellow-100 text-yellow-800';
            case 'posted': return 'bg-green-100 text-green-800';
            case 'reversed': return 'bg-red-100 text-red-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }
    
    function initializeCharts() {
        // Inventory Value Chart
        const inventoryCtx = document.getElementById('inventory-value-chart').getContext('2d');
        inventoryValueChart = new Chart(inventoryCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Inventory Value',
                    data: [],
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
        
        // COGS Chart
        const cogsCtx = document.getElementById('cogs-chart').getContext('2d');
        cogsChart = new Chart(cogsCtx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Cost of Goods Sold',
                    data: [],
                    backgroundColor: '#10B981'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
    
    function updateCharts(chartData) {
        // Update Inventory Value Chart
        inventoryValueChart.data.labels = chartData.inventory_value.labels;
        inventoryValueChart.data.datasets[0].data = chartData.inventory_value.data;
        inventoryValueChart.update();
        
        // Update COGS Chart
        cogsChart.data.labels = chartData.cogs.labels;
        cogsChart.data.datasets[0].data = chartData.cogs.data;
        cogsChart.update();
    }
    
    function syncWithAccounting() {
        $.ajax({
            url: 'api/sync-accounting.php',
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Accounting sync completed successfully!');
                    loadAccountingData();
                    loadJournalEntries();
                } else {
                    alert('Error syncing with accounting: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error syncing with accounting:', error);
                alert('Error syncing with accounting');
            }
        });
    }
    
    function exportJournalEntries() {
        // Show loading state
        const exportBtn = $('#export-journal-btn');
        const originalText = exportBtn.html();
        exportBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Generating PDF...');
        exportBtn.prop('disabled', true);
        
        $.ajax({
            url: 'api/export-journal-entries-pdf.php',
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                console.log('Export response:', response);
                if (response.success) {
                    // Open in new window for printing
                    window.open(response.download_url, '_blank');
                    
                    // Show success message
                    alert('Journal entries exported to PDF successfully!');
                } else {
                    alert('Error exporting journal entries: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error exporting journal entries:', error);
                console.error('Response text:', xhr.responseText);
                alert('Error exporting journal entries: ' + error);
            },
            complete: function() {
                // Restore button state
                exportBtn.html(originalText);
                exportBtn.prop('disabled', false);
            }
        });
    }
    
    function viewJournalEntry(entryId) {
        console.log('Viewing journal entry:', entryId);
        
        // Create mock data for demo (since we don't have real data yet)
        const mockEntry = createMockJournalEntry(entryId);
        showJournalEntryModal(mockEntry);
    }
    
    function createMockJournalEntry(entryId) {
        // Create mock data for demo purposes
        const mockEntries = [
            {
                id: entryId,
                reference_number: 'INV-' + (40 - Math.floor(Math.random() * 10)),
                account_code: Math.random() > 0.5 ? '5000' : '1200',
                description: Math.random() > 0.5 ? 'COGS - Sample Item' : 'Inventory Usage - Sample Item',
                debit_amount: Math.random() > 0.5 ? Math.floor(Math.random() * 5000) + 100 : 0,
                credit_amount: Math.random() > 0.5 ? 0 : Math.floor(Math.random() * 5000) + 100,
                status: 'posted',
                created_at: new Date().toISOString().replace('T', ' ').substring(0, 19)
            }
        ];
        
        return mockEntries[0];
    }
    
    function postJournalEntry(entryId) {
        if (confirm('Are you sure you want to post this journal entry?')) {
            $.ajax({
                url: 'api/post-journal-entry.php',
                method: 'POST',
                data: { id: entryId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Journal entry posted successfully');
                        loadJournalEntries();
                    } else {
                        alert('Error posting journal entry: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error posting journal entry:', error);
                    alert('Error posting journal entry');
                }
            });
        }
    }
    
    function showJournalEntryModal(entry) {
        const statusClass = getJournalStatusClass(entry.status);
        const statusText = entry.status.charAt(0).toUpperCase() + entry.status.slice(1);
        
        const modal = `
            <div id="journal-entry-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-10 mx-auto p-6 border w-11/12 md:w-4/5 lg:w-3/4 xl:w-2/3 shadow-xl rounded-lg bg-white">
                    <div class="mt-3">
                        <!-- Header -->
                        <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-200">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">Journal Entry Details</h3>
                                <p class="text-sm text-gray-600 mt-1">Reference: ${entry.reference_number || 'N/A'}</p>
                            </div>
                            <button onclick="closeJournalEntryModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                        
                        <!-- Content -->
                        <div class="space-y-6">
                            <!-- Basic Information -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                    Basic Information
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
                                        <p class="text-sm text-gray-900 bg-white px-3 py-2 rounded border">${entry.reference_number || 'N/A'}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Account Code</label>
                                        <p class="text-sm text-gray-900 bg-white px-3 py-2 rounded border">${entry.account_code}</p>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                    <p class="text-sm text-gray-900 bg-white px-3 py-2 rounded border min-h-[60px]">${entry.description}</p>
                                </div>
                            </div>
                            
                            <!-- Financial Information -->
                            <div class="bg-blue-50 rounded-lg p-4">
                                <h4 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-calculator text-blue-500 mr-2"></i>
                                    Financial Information
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="text-center">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Debit Amount</label>
                                        <div class="bg-white px-4 py-3 rounded-lg border-2 ${entry.debit_amount > 0 ? 'border-red-200' : 'border-gray-200'}">
                                            <p class="text-lg font-semibold ${entry.debit_amount > 0 ? 'text-red-600' : 'text-gray-500'}">
                                                ${entry.debit_amount > 0 ? '₱' + parseFloat(entry.debit_amount).toFixed(2) : 'No Debit'}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Credit Amount</label>
                                        <div class="bg-white px-4 py-3 rounded-lg border-2 ${entry.credit_amount > 0 ? 'border-green-200' : 'border-gray-200'}">
                                            <p class="text-lg font-semibold ${entry.credit_amount > 0 ? 'text-green-600' : 'text-gray-500'}">
                                                ${entry.credit_amount > 0 ? '₱' + parseFloat(entry.credit_amount).toFixed(2) : 'No Credit'}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Status and Date Information -->
                            <div class="bg-green-50 rounded-lg p-4">
                                <h4 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-clock text-green-500 mr-2"></i>
                                    Status & Timeline
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${statusClass}">
                                            <i class="fas fa-circle text-xs mr-2"></i>
                                            ${statusText}
                                        </span>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Created At</label>
                                        <p class="text-sm text-gray-900 bg-white px-3 py-2 rounded border">
                                            <i class="fas fa-calendar-alt text-gray-400 mr-2"></i>
                                            ${entry.created_at}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="mt-8 flex justify-end space-x-3 pt-4 border-t border-gray-200">
                            <button onclick="closeJournalEntryModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                                <i class="fas fa-times mr-2"></i>
                                Close
                            </button>
                            ${entry.status === 'pending' ? `
                                <button onclick="postJournalEntry(${entry.id}); closeJournalEntryModal();" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                                    <i class="fas fa-check mr-2"></i>
                                    Post Entry
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('body').append(modal);
        
        // Add click outside to close functionality
        $('#journal-entry-modal').on('click.journal-modal', function(e) {
            if (e.target === this) {
                closeJournalEntryModal();
            }
        });
        
        // Add escape key to close functionality
        $(document).on('keydown.journal-modal', function(e) {
            if (e.key === 'Escape' && $('#journal-entry-modal').length > 0) {
                closeJournalEntryModal();
            }
        });
    }
    
    function closeJournalEntryModal() {
        console.log('Closing journal entry modal');
        // Remove the modal
        $('#journal-entry-modal').remove();
        // Remove any event handlers
        $(document).off('keydown.journal-modal');
        $(document).off('click.journal-modal');
    }
    
    function editAccountMapping() {
        // Show account mapping editor modal
        const modal = `
            <div id="account-mapping-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-4/5 lg:w-3/4 xl:w-2/3 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium text-gray-900">Account Mapping Editor</h3>
                            <button onclick="closeAccountMappingModal()" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                        
                        <div class="space-y-6">
                            <!-- Inventory Accounts -->
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="text-md font-semibold text-gray-800 mb-4">Inventory Accounts</h4>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Raw Materials</span>
                                        <input type="text" value="1200" class="w-20 px-2 py-1 border border-gray-300 rounded text-sm">
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Finished Goods</span>
                                        <input type="text" value="1210" class="w-20 px-2 py-1 border border-gray-300 rounded text-sm">
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Supplies</span>
                                        <input type="text" value="1220" class="w-20 px-2 py-1 border border-gray-300 rounded text-sm">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Expense Accounts -->
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="text-md font-semibold text-gray-800 mb-4">Expense Accounts</h4>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Cost of Goods Sold</span>
                                        <input type="text" value="5000" class="w-20 px-2 py-1 border border-gray-300 rounded text-sm">
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Supplies Expense</span>
                                        <input type="text" value="5100" class="w-20 px-2 py-1 border border-gray-300 rounded text-sm">
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Waste Expense</span>
                                        <input type="text" value="5200" class="w-20 px-2 py-1 border border-gray-300 rounded text-sm">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Liability Accounts -->
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="text-md font-semibold text-gray-800 mb-4">Liability Accounts</h4>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Accounts Payable</span>
                                        <input type="text" value="2000" class="w-20 px-2 py-1 border border-gray-300 rounded text-sm">
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Accrued Expenses</span>
                                        <input type="text" value="2100" class="w-20 px-2 py-1 border border-gray-300 rounded text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 flex justify-end space-x-3">
                            <button onclick="closeAccountMappingModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Cancel
                            </button>
                            <button onclick="saveAccountMapping()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('body').append(modal);
        
        // Add click outside to close functionality
        $('#account-mapping-modal').on('click.mapping-modal', function(e) {
            if (e.target === this) {
                closeAccountMappingModal();
            }
        });
        
        // Add escape key to close functionality
        $(document).on('keydown.mapping-modal', function(e) {
            if (e.key === 'Escape' && $('#account-mapping-modal').length > 0) {
                closeAccountMappingModal();
            }
        });
    }
    
    function closeAccountMappingModal() {
        console.log('Closing account mapping modal');
        // Remove the modal
        $('#account-mapping-modal').remove();
        // Remove any event handlers
        $(document).off('keydown.mapping-modal');
        $(document).off('click.mapping-modal');
    }
    
    function saveAccountMapping() {
        console.log('Save account mapping clicked');
        // Collect all the account codes using better selectors
        const modal = $('#account-mapping-modal');
        const mappingData = {
            inventory: {
                raw_materials: modal.find('input[value="1200"]').val(),
                finished_goods: modal.find('input[value="1210"]').val(),
                supplies: modal.find('input[value="1220"]').val()
            },
            expense: {
                cogs: modal.find('input[value="5000"]').val(),
                supplies_expense: modal.find('input[value="5100"]').val(),
                waste_expense: modal.find('input[value="5200"]').val()
            },
            liability: {
                accounts_payable: modal.find('input[value="2000"]').val(),
                accrued_expenses: modal.find('input[value="2100"]').val()
            }
        };
        
        // Debug: Log the data being sent
        console.log('Saving account mapping data:', mappingData);
        
        $.ajax({
            url: 'api/save-account-mapping.php',
            method: 'POST',
            data: mappingData,
            dataType: 'json',
            success: function(response) {
                console.log('Save response:', response);
                if (response.success) {
                    alert('Account mapping saved successfully');
                    closeAccountMappingModal();
                } else {
                    alert('Error saving account mapping: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error saving account mapping:', error);
                console.error('Response text:', xhr.responseText);
                alert('Error saving account mapping: ' + error);
            }
        });
    }
});
</script>
