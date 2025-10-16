<?php
/**
 * Enhanced Reports
 * Hotel PMS Training System - Inventory Module
 */

require_once __DIR__ . '/../vps_session_fix.php';
require_once __DIR__ . '/../includes/database.php';

// Check if user is logged in and has manager role
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'manager') {
    header('Location: login.php?error=access_denied');
    exit();
}

// Set page title
$page_title = 'Enhanced Reports';

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Enhanced Reports</h2>
                <div class="flex items-center space-x-4">
                    <button id="export-enhanced-report-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-download mr-2"></i>Export Report
                    </button>
                    <button id="schedule-report-btn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-clock mr-2"></i>Schedule Report
                    </button>
                </div>
            </div>

            <!-- Report Filters -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Report Filters</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                        <select id="report-type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="usage-trends">Usage Trends</option>
                            <option value="cost-analysis">Cost Analysis</option>
                            <option value="supplier-performance">Supplier Performance</option>
                            <option value="waste-analysis">Waste Analysis</option>
                            <option value="efficiency-metrics">Efficiency Metrics</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                        <select id="date-range" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="last-7-days">Last 7 Days</option>
                            <option value="last-30-days">Last 30 Days</option>
                            <option value="last-3-months">Last 3 Months</option>
                            <option value="last-6-months">Last 6 Months</option>
                            <option value="this-year">This Year</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select id="category-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Categories</option>
                            <option value="Food & Beverage">Food & Beverage</option>
                            <option value="Amenities">Amenities</option>
                            <option value="Cleaning Supplies">Cleaning Supplies</option>
                            <option value="Office Supplies">Office Supplies</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button id="generate-enhanced-report-btn" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-chart-line mr-2"></i>Generate Report
                        </button>
                    </div>
                </div>
            </div>

            <!-- Usage Trends Chart -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Usage Trends (Last 30 Days)</h3>
                    <div class="h-72"><canvas id="usageTrendsChart"></canvas></div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Category Distribution</h3>
                    <div class="h-72"><canvas id="categoryDistributionChart"></canvas></div>
                </div>
            </div>

            <!-- Cost Analysis -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Monthly Cost Analysis</h3>
                    <div class="h-72"><canvas id="costAnalysisChart"></canvas></div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Top 10 Most Expensive Items</h3>
                    <div id="expensive-items-list" class="space-y-3">
                        <!-- Items will be loaded dynamically -->
                    </div>
                </div>
            </div>

            <!-- Supplier Performance -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Supplier Performance Analysis</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Orders</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">On-Time Delivery</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quality Rating</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Value</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Performance Score</th>
                            </tr>
                        </thead>
                        <tbody id="supplier-performance-tbody" class="bg-white divide-y divide-gray-200">
                            <!-- Supplier performance data will be loaded dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Efficiency Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-chart-line text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Inventory Turnover</p>
                            <p class="text-2xl font-semibold text-gray-900" id="inventory-turnover">4.2x</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Average Lead Time</p>
                            <p class="text-2xl font-semibold text-gray-900" id="average-lead-time">7.5 days</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-percentage text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Waste Percentage</p>
                            <p class="text-2xl font-semibold text-gray-900" id="waste-percentage">3.2%</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Analytics Table -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Detailed Analytics</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usage Rate</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost per Unit</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monthly Usage</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monthly Cost</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trend</th>
                            </tr>
                        </thead>
                        <tbody id="analytics-tbody" class="bg-white divide-y divide-gray-200">
                            <!-- Analytics data will be loaded dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Include footer -->
        <?php include '../includes/pos-footer.php'; ?>
    </div>
</body>
</html>

<script>
$(document).ready(function() {
    loadEnhancedReportData();
    initializeCharts();
    
    // Button event handlers
    $('#generate-enhanced-report-btn').click(function() {
        generateEnhancedReport();
    });
    
    $('#export-enhanced-report-btn').click(function() {
        exportEnhancedReport();
    });
    
    $('#schedule-report-btn').click(function() {
        scheduleReport();
    });
    
    function loadEnhancedReportData() {
        // Load enhanced report data
        $.ajax({
            url: 'api/get-enhanced-report-data.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    updateCharts(response.data);
                    displayExpensiveItems(response.data.expensive_items);
                    displaySupplierPerformance(response.data.supplier_performance);
                    displayAnalyticsTable(response.data.analytics);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading enhanced report data:', error);
            }
        });
    }
    
    function initializeCharts() {
        // Initialize usage trends chart
        const usageCtx = document.getElementById('usageTrendsChart').getContext('2d');
        new Chart(usageCtx, {
            type: 'line',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                datasets: [{
                    label: 'Items Used',
                    data: [120, 150, 180, 160],
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });
        
        // Initialize category distribution chart
        const categoryCtx = document.getElementById('categoryDistributionChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: ['Food & Beverage', 'Amenities', 'Cleaning Supplies', 'Office Supplies'],
                datasets: [{
                    data: [35, 25, 20, 20],
                    backgroundColor: [
                        'rgb(239, 68, 68)',
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
        
        // Initialize cost analysis chart
        const costCtx = document.getElementById('costAnalysisChart').getContext('2d');
        new Chart(costCtx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Monthly Cost',
                    data: [12000, 15000, 18000, 16000, 20000, 22000],
                    backgroundColor: 'rgba(16, 185, 129, 0.8)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });
    }
    
    function updateCharts(data) {
        // Update charts with real data
        console.log('Updating charts with data:', data);
    }
    
    function displayExpensiveItems(items) {
        const container = $('#expensive-items-list');
        container.empty();
        
        items.forEach(function(item, index) {
            const itemHtml = `
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <span class="text-sm font-medium text-gray-900">${index + 1}. ${item.name}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-medium text-gray-900">$${item.cost.toFixed(2)}</span>
                        <div class="text-xs text-gray-500">${item.category}</div>
                    </div>
                </div>
            `;
            container.append(itemHtml);
        });
    }
    
    function displaySupplierPerformance(suppliers) {
        const tbody = $('#supplier-performance-tbody');
        tbody.empty();
        
        suppliers.forEach(function(supplier) {
            const performanceScore = Math.round(supplier.performance_score);
            const scoreClass = performanceScore >= 80 ? 'text-green-600' : 
                              performanceScore >= 60 ? 'text-yellow-600' : 'text-red-600';
            
            const row = `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${supplier.name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${supplier.total_orders}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${supplier.on_time_delivery}%</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${supplier.quality_rating}/5</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$${supplier.total_value.toLocaleString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium ${scoreClass}">${performanceScore}%</td>
                </tr>
            `;
            tbody.append(row);
        });
    }
    
    function displayAnalyticsTable(analytics) {
        const tbody = $('#analytics-tbody');
        tbody.empty();
        
        analytics.forEach(function(item) {
            const trendIcon = item.trend > 0 ? 'fas fa-arrow-up text-red-500' : 
                             item.trend < 0 ? 'fas fa-arrow-down text-green-500' : 
                             'fas fa-minus text-gray-500';
            
            const row = `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${item.name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.usage_rate} units/day</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$${item.cost_per_unit.toFixed(2)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.monthly_usage}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$${item.monthly_cost.toFixed(2)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <i class="${trendIcon}"></i> ${Math.abs(item.trend)}%
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }
    
    function generateEnhancedReport() {
        const reportType = $('#report-type').val();
        const dateRange = $('#date-range').val();
        const category = $('#category-filter').val();
        
        // Show loading state
        $('#generate-enhanced-report-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Generating...');
        
        // Simulate report generation
        setTimeout(function() {
            $('#generate-enhanced-report-btn').prop('disabled', false).html('<i class="fas fa-chart-line mr-2"></i>Generate Report');
            alert('Enhanced report generated successfully!');
        }, 2000);
    }
    
    function exportEnhancedReport() {
        $.ajax({
            url: 'api/export-enhanced-report.php',
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const link = document.createElement('a');
                    link.href = response.download_url;
                    link.download = 'enhanced_report_' + new Date().toISOString().split('T')[0] + '.pdf';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                } else {
                    alert('Error exporting report: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error exporting enhanced report:', error);
                alert('Error exporting report');
            }
        });
    }
    
    function scheduleReport() {
        alert('Report scheduling functionality would be implemented here');
    }
});
</script>