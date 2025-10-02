<?php
/**
 * Accounting Integration
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
                        <i class="fas fa-file-export mr-2"></i>Export Journal Entries
                    </button>
                </div>
            </div>

            <!-- Financial Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-dollar-sign text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Inventory Value</p>
                            <p class="text-2xl font-semibold text-gray-900" id="total-inventory-value">$0</p>
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
                            <p class="text-2xl font-semibold text-gray-900" id="monthly-purchases">$0</p>
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
                            <p class="text-2xl font-semibold text-gray-900" id="monthly-usage">$0</p>
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
                            <p class="text-2xl font-semibold text-gray-900" id="cogs-30d">$0</p>
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
        $('#total-inventory-value').text('$' + data.total_inventory_value.toLocaleString());
        $('#monthly-purchases').text('$' + data.monthly_purchases.toLocaleString());
        $('#monthly-usage').text('$' + data.monthly_usage.toLocaleString());
        $('#cogs-30d').text('$' + data.cogs_30d.toLocaleString());
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
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${entry.debit_amount > 0 ? '$' + parseFloat(entry.debit_amount).toFixed(2) : '-'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${entry.credit_amount > 0 ? '$' + parseFloat(entry.credit_amount).toFixed(2) : '-'}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                            ${statusText}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                        ${entry.status === 'pending' ? '<button class="text-green-600 hover:text-green-900">Post</button>' : ''}
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
                                return '$' + value.toLocaleString();
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
                                return '$' + value.toLocaleString();
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
        $.ajax({
            url: 'api/export-journal-entries.php',
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Create download link
                    const link = document.createElement('a');
                    link.href = response.download_url;
                    link.download = 'journal_entries_' + new Date().toISOString().split('T')[0] + '.csv';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                } else {
                    alert('Error exporting journal entries: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error exporting journal entries:', error);
                alert('Error exporting journal entries');
            }
        });
    }
    
    function editAccountMapping() {
        alert('Account mapping editor would open here');
    }
});
</script>
