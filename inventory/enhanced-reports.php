<?php
/**
 * Enhanced Reports with Cost Analysis and Turnover
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
$page_title = 'Enhanced Reports & Analytics';

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
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Enhanced Reports & Analytics</h2>
                <div class="flex items-center space-x-4">
                    <button id="export-report-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
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
                            <option value="cost-analysis">Cost Analysis Report</option>
                            <option value="turnover-analysis">Turnover Analysis</option>
                            <option value="abc-analysis">ABC Analysis</option>
                            <option value="supplier-performance">Supplier Performance</option>
                            <option value="room-utilization">Room Utilization</option>
                            <option value="waste-analysis">Waste Analysis</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select id="category-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Categories</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                        <select id="date-range" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="7">Last 7 Days</option>
                            <option value="30" selected>Last 30 Days</option>
                            <option value="90">Last 3 Months</option>
                            <option value="365">Last Year</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button id="generate-report-btn" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-chart-bar mr-2"></i>Generate Report
                        </button>
                    </div>
                </div>
            </div>

            <!-- Key Performance Indicators -->
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
                                <i class="fas fa-chart-line text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Avg Turnover Rate</p>
                            <p class="text-2xl font-semibold text-gray-900" id="avg-turnover-rate">0%</p>
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
                            <p class="text-sm font-medium text-gray-500">Slow Moving Items</p>
                            <p class="text-2xl font-semibold text-gray-900" id="slow-moving-items">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-trash text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Waste Cost (30d)</p>
                            <p class="text-2xl font-semibold text-gray-900" id="waste-cost">$0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Cost Analysis Chart -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Cost Analysis by Category</h3>
                    <div class="h-64">
                        <canvas id="cost-analysis-chart"></canvas>
                    </div>
                </div>

                <!-- Turnover Analysis Chart -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Turnover Analysis</h3>
                    <div class="h-64">
                        <canvas id="turnover-chart"></canvas>
                    </div>
                </div>
            </div>

            <!-- ABC Analysis -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">ABC Analysis</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Annual Usage Value</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">% of Total Value</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cumulative %</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ABC Class</th>
                            </tr>
                        </thead>
                        <tbody id="abc-analysis-tbody" class="bg-white divide-y divide-gray-200">
                            <!-- ABC analysis data will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Supplier Performance -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Supplier Performance Analysis</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-green-600 mb-2" id="avg-delivery-time">0</div>
                        <div class="text-sm text-gray-600">Avg Delivery Time (Days)</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-blue-600 mb-2" id="on-time-delivery">0%</div>
                        <div class="text-sm text-gray-600">On-Time Delivery Rate</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-yellow-600 mb-2" id="quality-rating">0</div>
                        <div class="text-sm text-gray-600">Avg Quality Rating</div>
                    </div>
                </div>
            </div>

            <!-- Room Utilization Report -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Room Inventory Utilization</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Floor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Items</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilization Rate</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Restocked</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody id="room-utilization-tbody" class="bg-white divide-y divide-gray-200">
                            <!-- Room utilization data will be loaded here -->
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
    let costChart, turnoverChart;
    
    // Load initial data
    loadCategories();
    loadEnhancedReportData();
    initializeCharts();
    
    // Report generation
    $('#generate-report-btn').click(function() {
        generateReport();
    });
    
    // Export report
    $('#export-report-btn').click(function() {
        exportEnhancedReport();
    });
    
    // Schedule report
    $('#schedule-report-btn').click(function() {
        scheduleReport();
    });
    
    // Report type change
    $('#report-type').change(function() {
        updateReportFilters();
    });
    
    function loadCategories() {
        $.ajax({
            url: 'api/get-inventory-categories.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const categorySelect = $('#category-filter');
                    response.categories.forEach(function(category) {
                        categorySelect.append(`<option value="${category.id}">${category.name}</option>`);
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading categories:', error);
            }
        });
    }
    
    function loadEnhancedReportData() {
        $.ajax({
            url: 'api/get-enhanced-report-data.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    updateKPIs(response.kpis);
                    updateABCAnalysis(response.abc_analysis);
                    updateSupplierPerformance(response.supplier_performance);
                    updateRoomUtilization(response.room_utilization);
                    updateCharts(response.chart_data);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading enhanced report data:', error);
            }
        });
    }
    
    function updateKPIs(kpis) {
        $('#total-inventory-value').text('$' + kpis.total_inventory_value.toLocaleString());
        $('#avg-turnover-rate').text(kpis.avg_turnover_rate + '%');
        $('#slow-moving-items').text(kpis.slow_moving_items);
        $('#waste-cost').text('$' + kpis.waste_cost.toLocaleString());
    }
    
    function updateABCAnalysis(abcData) {
        const tbody = $('#abc-analysis-tbody');
        tbody.empty();
        
        abcData.forEach(function(item) {
            const abcClass = item.cumulative_percentage <= 80 ? 'A' : 
                           item.cumulative_percentage <= 95 ? 'B' : 'C';
            const classColor = abcClass === 'A' ? 'bg-red-100 text-red-800' : 
                              abcClass === 'B' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800';
            
            const row = `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${item.name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.category_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$${item.annual_usage_value.toLocaleString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.percentage_of_total.toFixed(2)}%</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.cumulative_percentage.toFixed(2)}%</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${classColor}">
                            Class ${abcClass}
                        </span>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }
    
    function updateSupplierPerformance(performance) {
        $('#avg-delivery-time').text(performance.avg_delivery_time);
        $('#on-time-delivery').text(performance.on_time_delivery_rate + '%');
        $('#quality-rating').text(performance.avg_quality_rating);
    }
    
    function updateRoomUtilization(utilization) {
        const tbody = $('#room-utilization-tbody');
        tbody.empty();
        
        utilization.forEach(function(room) {
            const utilizationRate = room.total_items > 0 ? ((room.used_items / room.total_items) * 100).toFixed(1) : 0;
            const statusClass = utilizationRate >= 80 ? 'bg-green-100 text-green-800' : 
                               utilizationRate >= 60 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800';
            const statusText = utilizationRate >= 80 ? 'High' : 
                              utilizationRate >= 60 ? 'Medium' : 'Low';
            
            const row = `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${room.room_number}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${room.floor_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${room.total_items}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${utilizationRate}%</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${room.last_restocked || 'Never'}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                            ${statusText}
                        </span>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }
    
    function initializeCharts() {
        // Cost Analysis Chart
        const costCtx = document.getElementById('cost-analysis-chart').getContext('2d');
        costChart = new Chart(costCtx, {
            type: 'doughnut',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: [
                        '#3B82F6',
                        '#10B981',
                        '#F59E0B',
                        '#EF4444',
                        '#8B5CF6',
                        '#06B6D4'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Turnover Chart
        const turnoverCtx = document.getElementById('turnover-chart').getContext('2d');
        turnoverChart = new Chart(turnoverCtx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Turnover Rate (%)',
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
                        max: 100
                    }
                }
            }
        });
    }
    
    function updateCharts(chartData) {
        // Update Cost Analysis Chart
        costChart.data.labels = chartData.cost_analysis.labels;
        costChart.data.datasets[0].data = chartData.cost_analysis.data;
        costChart.update();
        
        // Update Turnover Chart
        turnoverChart.data.labels = chartData.turnover.labels;
        turnoverChart.data.datasets[0].data = chartData.turnover.data;
        turnoverChart.update();
    }
    
    function generateReport() {
        const reportType = $('#report-type').val();
        const category = $('#category-filter').val();
        const dateRange = $('#date-range').val();
        
        $.ajax({
            url: 'api/generate-enhanced-report.php',
            method: 'POST',
            data: {
                report_type: reportType,
                category: category,
                date_range: dateRange
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update the report based on the generated data
                    console.log('Report generated successfully');
                } else {
                    console.error('Error generating report:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error generating report:', error);
            }
        });
    }
    
    function updateReportFilters() {
        const reportType = $('#report-type').val();
        
        // Show/hide relevant filters based on report type
        if (reportType === 'room-utilization') {
            $('#category-filter').closest('div').hide();
        } else {
            $('#category-filter').closest('div').show();
        }
    }
    
    function exportEnhancedReport() {
        const reportType = $('#report-type').val();
        const dateRange = $('#date-range').val();
        const category = $('#category-filter').val();
        
        $.ajax({
            url: 'api/export-enhanced-report.php',
            method: 'POST',
            data: {
                report_type: reportType,
                date_range: dateRange,
                category: category
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const link = document.createElement('a');
                    link.href = response.download_url;
                    link.download = 'enhanced_report_' + new Date().toISOString().split('T')[0] + '.csv';
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
    
    function scheduleReport() {
        const reportType = $('#report-type').val();
        const frequency = prompt('Enter frequency (daily, weekly, monthly):', 'weekly');
        
        if (!frequency) return;
        
        $.ajax({
            url: 'api/schedule-report.php',
            method: 'POST',
            data: {
                report_type: reportType,
                frequency: frequency
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Report scheduled successfully!');
                } else {
                    alert('Error scheduling report: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error scheduling report:', error);
                alert('Error scheduling report');
            }
        });
    }
});
</script>
