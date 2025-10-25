<?php
session_start();

// Check if user is logged in to POS
if (!isset($_SESSION['pos_user_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Performance Reports';
$user_role = $_SESSION['pos_user_role'];
$user_name = $_SESSION['pos_user_name'];
$is_demo_mode = isset($_SESSION['pos_demo_mode']) && $_SESSION['pos_demo_mode'];

// Include POS functions
require_once '../includes/pos-functions.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Hotel POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#667eea',
                        secondary: '#764ba2',
                        success: '#28a745',
                        danger: '#dc3545',
                        warning: '#ffc107',
                        info: '#17a2b8'
                    }
                }
            }
        }
    </script>
    <style>
        /* Responsive layout fixes */
        .main-content {
            margin-left: 0;
            position: relative;
            z-index: 1;
        }
        
        /* Ensure sidebar is above main content */
        #sidebar {
            z-index: 45 !important;
        }
        
        #sidebar-overlay {
            z-index: 35 !important;
        }
        
        @media (min-width: 1024px) {
            .main-content {
                margin-left: 16rem;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <!-- Sidebar Overlay for Mobile -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden" onclick="closeSidebar()"></div>
        
        <!-- Include POS-specific header and sidebar -->
        <?php include '../includes/pos-header.php'; ?>
        <?php include '../includes/pos-sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="main-content pt-20 px-4 pb-4 lg:px-6 lg:pb-6 flex-1 transition-all duration-300">
                <!-- Page Header -->
                <div class="mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                    <h1 class="text-2xl font-bold text-gray-800 mb-2">Performance Reports</h1>
                            <p class="text-gray-600">Comprehensive performance analytics and operational insights</p>
                        </div>
                        <div class="text-sm text-gray-500">
                            <div id="current-date" class="font-medium"></div>
                            <div id="current-time" class="text-xs"></div>
                        </div>
                    </div>
                </div>

                <!-- Performance KPIs -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">System Uptime</p>
                                <p id="system-uptime" class="text-2xl font-bold text-gray-900">99.8%</p>
                                <p class="text-xs text-blue-600">Last 30 days</p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-server text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Response Time</p>
                                <p id="response-time" class="text-2xl font-bold text-gray-900">0.85s</p>
                                <p class="text-xs text-green-600">Avg. load time</p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-tachometer-alt text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-orange-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Transaction Success</p>
                                <p id="transaction-success" class="text-2xl font-bold text-gray-900">98.5%</p>
                                <p class="text-xs text-orange-600">Success rate</p>
                            </div>
                            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-check-circle text-orange-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">User Satisfaction</p>
                                <p id="user-satisfaction" class="text-2xl font-bold text-gray-900">4.7/5</p>
                                <p class="text-xs text-purple-600">Rating average</p>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-star text-purple-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Reports Content -->
                <div class="p-6">
                    <!-- Report Filters -->
                    <div class="mb-6">
                        <div class="flex flex-col lg:flex-row gap-4">
                            <div class="flex-1">
                                <label for="performance-metric" class="block text-sm font-medium text-gray-700 mb-2">Performance Metric</label>
                                <select id="performance-metric" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    <option value="">Select Metric</option>
                                    <option value="system">System Performance</option>
                                    <option value="user">User Performance</option>
                                    <option value="transaction">Transaction Performance</option>
                                    <option value="operational">Operational Efficiency</option>
                                    <option value="financial">Financial Performance</option>
                                </select>
                            </div>
                            <div class="lg:w-48">
                                <label for="performance-period" class="block text-sm font-medium text-gray-700 mb-2">Time Period</label>
                                <select id="performance-period" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    <option value="">Select Period</option>
                                    <option value="today">Today</option>
                                    <option value="week">This Week</option>
                                    <option value="month">This Month</option>
                                    <option value="quarter">This Quarter</option>
                                    <option value="year">This Year</option>
                                </select>
                            </div>
                            <div class="lg:w-48">
                                <label for="performance-department" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                                <select id="performance-department" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    <option value="">All Departments</option>
                                    <option value="restaurant">Restaurant</option>
                                    <option value="spa">Spa Services</option>
                                    <option value="housekeeping">Housekeeping</option>
                                    <option value="front-desk">Front Desk</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
                            </div>
                            <div class="lg:w-48">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Actions</label>
                                <div class="flex space-x-2">
                                    <button onclick="generatePerformanceReport()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                                        <i class="fas fa-chart-bar mr-2"></i>Generate
                                    </button>
                                    <button onclick="refreshPerformanceData()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                                        <i class="fas fa-sync-alt mr-2"></i>Refresh
                        </button>
                    </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Analytics Charts -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        <!-- Department Performance -->
                        <div class="bg-white border border-gray-200 rounded-lg">
                            <div class="p-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-chart-bar text-blue-600 mr-2"></i>
                                    Department Performance
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Restaurant</span>
                                        <div class="flex items-center">
                                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                                <div class="bg-green-500 h-2 rounded-full" style="width: 92%"></div>
                                            </div>
                                            <span class="font-semibold text-gray-900">92%</span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Spa Services</span>
                                        <div class="flex items-center">
                                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                                <div class="bg-blue-500 h-2 rounded-full" style="width: 88%"></div>
                                            </div>
                                            <span class="font-semibold text-gray-900">88%</span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Housekeeping</span>
                                        <div class="flex items-center">
                                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                                <div class="bg-orange-500 h-2 rounded-full" style="width: 85%"></div>
                                            </div>
                                            <span class="font-semibold text-gray-900">85%</span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Front Desk</span>
                                        <div class="flex items-center">
                                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                                <div class="bg-purple-500 h-2 rounded-full" style="width: 90%"></div>
                                            </div>
                                            <span class="font-semibold text-gray-900">90%</span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Maintenance</span>
                                        <div class="flex items-center">
                                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                                <div class="bg-pink-500 h-2 rounded-full" style="width: 78%"></div>
                                            </div>
                                            <span class="font-semibold text-gray-900">78%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- System Performance Trends -->
                        <div class="bg-white border border-gray-200 rounded-lg">
                            <div class="p-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-chart-line text-green-600 mr-2"></i>
                                    System Performance Trends
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">CPU Usage</span>
                                        <span class="font-semibold text-blue-600">45%</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Memory Usage</span>
                                        <span class="font-semibold text-green-600">62%</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Disk Usage</span>
                                        <span class="font-semibold text-orange-600">38%</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Network Latency</span>
                                        <span class="font-semibold text-purple-600">12ms</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Active Connections</span>
                                        <span class="font-semibold text-pink-600">1,247</span>
                    </div>
                </div>
            </div>
        </div>
                    </div>

                    <!-- Performance Summary Table -->
                    <div class="bg-white border border-gray-200 rounded-lg">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-table text-purple-600 mr-2"></i>
                                Performance Metrics Summary
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metric</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trend</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">System Uptime</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">99.8%</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">99.5%</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">+0.3%</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Excellent</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Response Time</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">0.85s</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">1.0s</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-0.15s</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Excellent</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Transaction Success</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">98.5%</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">95.0%</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">+3.5%</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Excellent</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">User Satisfaction</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">4.7/5</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">4.0/5</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">+0.7</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Excellent</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Error Rate</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">0.2%</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">1.0%</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-0.8%</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Excellent</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="mt-6 flex flex-wrap gap-4">
                        <button onclick="exportPerformanceReport()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-download mr-2"></i>Export Report
                        </button>
                        <button onclick="viewDetailedMetrics()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-chart-line mr-2"></i>Detailed Metrics
                        </button>
                        <button onclick="schedulePerformanceAlert()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-bell mr-2"></i>Set Alerts
                        </button>
                        <button onclick="generateBenchmarkReport()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-chart-bar mr-2"></i>Benchmark Report
                        </button>
                    </div>
                </div>
        </main>
    </div>

    <script>
        // Sidebar functionality
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            if (sidebar.classList.contains('sidebar-open')) {
                sidebar.classList.remove('sidebar-open');
                overlay.classList.add('hidden');
            } else {
                sidebar.classList.add('sidebar-open');
                overlay.classList.remove('hidden');
            }
        }

        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.remove('sidebar-open');
            overlay.classList.add('hidden');
        }

        // Submenu toggle functionality
        function toggleSubmenu(menuId) {
            const submenu = document.getElementById('submenu-' + menuId);
            const chevron = document.getElementById('chevron-' + menuId);
            
            if (submenu.classList.contains('hidden')) {
                submenu.classList.remove('hidden');
                chevron.style.transform = 'rotate(180deg)';
            } else {
                submenu.classList.add('hidden');
                chevron.style.transform = 'rotate(0deg)';
            }
        }

        // Initialize sidebar on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-expand submenus with active items
            document.querySelectorAll('#sidebar ul[id^="submenu-"]').forEach(submenu => {
                const activeItem = submenu.querySelector('a.text-blue-600, a.active, a.text-primary, a.border-blue-500');
                if (activeItem) {
                    submenu.classList.remove('hidden');
                    const menuId = submenu.id.replace('submenu-', '');
                    const chevron = document.getElementById('chevron-' + menuId);
                    if (chevron) {
                        chevron.style.transform = 'rotate(180deg)';
                    }
                }
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                const sidebar = document.getElementById('sidebar');
                const sidebarToggle = document.querySelector('[onclick="toggleSidebar()"]');
                
                if (window.innerWidth < 1024 && 
                    !sidebar.contains(event.target) && 
                    !sidebarToggle.contains(event.target) && 
                    sidebar.classList.contains('sidebar-open')) {
                    closeSidebar();
                }
            });
        });

        // Initialize sidebar on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-expand submenus with active items
            document.querySelectorAll('#sidebar ul[id^="submenu-"]').forEach(submenu => {
                const activeItem = submenu.querySelector('a.text-blue-600, a.active, a.text-primary, a.border-blue-500');
                if (activeItem) {
                    submenu.classList.remove('hidden');
                    const menuId = submenu.id.replace('submenu-', '');
                    const chevron = document.getElementById('chevron-' + menuId);
                    if (chevron) {
                        chevron.style.transform = 'rotate(180deg)';
                    }
                }
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                const sidebar = document.getElementById('sidebar');
                const sidebarToggle = document.querySelector('[onclick="toggleSidebar()"]');
                
                if (window.innerWidth < 1024 && 
                    !sidebar.contains(event.target) && 
                    !sidebarToggle.contains(event.target) && 
                    sidebar.classList.contains('sidebar-open')) {
                    closeSidebar();
                }
            });

            // Initialize performance reports functionality
            initializePerformanceReports();
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });

        // Performance Reports Management Functions
        function initializePerformanceReports() {
            loadPerformanceData();
            initializePerformanceFilters();
        }

        function initializePerformanceFilters() {
            const metric = document.getElementById('performance-metric');
            const period = document.getElementById('performance-period');
            const department = document.getElementById('performance-department');

            if (metric) metric.addEventListener('change', applyPerformanceFilters);
            if (period) period.addEventListener('change', applyPerformanceFilters);
            if (department) department.addEventListener('change', applyPerformanceFilters);
        }

        function loadPerformanceData() {
            // Simulate loading performance data
            const performanceData = {
                systemUptime: generateRandomSystemUptime(),
                responseTime: generateRandomResponseTime(),
                transactionSuccess: generateRandomTransactionSuccess(),
                userSatisfaction: generateRandomUserSatisfaction()
            };

            updatePerformanceDisplay(performanceData);
        }

        function generateRandomSystemUptime() {
            return (Math.random() * 0.5 + 99.3).toFixed(1);
        }

        function generateRandomResponseTime() {
            return (Math.random() * 0.5 + 0.6).toFixed(2);
        }

        function generateRandomTransactionSuccess() {
            return (Math.random() * 3 + 96).toFixed(1);
        }

        function generateRandomUserSatisfaction() {
            return (Math.random() * 0.8 + 4.2).toFixed(1);
        }

        function updatePerformanceDisplay(data) {
            const systemUptime = document.getElementById('system-uptime');
            const responseTime = document.getElementById('response-time');
            const transactionSuccess = document.getElementById('transaction-success');
            const userSatisfaction = document.getElementById('user-satisfaction');

            if (systemUptime) {
                systemUptime.textContent = `${data.systemUptime}%`;
            }
            if (responseTime) {
                responseTime.textContent = `${data.responseTime}s`;
            }
            if (transactionSuccess) {
                transactionSuccess.textContent = `${data.transactionSuccess}%`;
            }
            if (userSatisfaction) {
                userSatisfaction.textContent = `${data.userSatisfaction}/5`;
            }
        }

        function applyPerformanceFilters() {
            const metric = document.getElementById('performance-metric')?.value || '';
            const period = document.getElementById('performance-period')?.value || '';
            const department = document.getElementById('performance-department')?.value || '';

            console.log('Applying performance filters:', { metric, period, department });
            
            showNotification('Applying performance filters...', 'info');
            
            setTimeout(() => {
                loadPerformanceData();
                showNotification('Performance data updated successfully!', 'success');
            }, 1000);
        }

        // Performance Report Operations
        function generatePerformanceReport() {
            const metric = document.getElementById('performance-metric')?.value || '';
            const period = document.getElementById('performance-period')?.value || '';
            const department = document.getElementById('performance-department')?.value || '';

            if (!metric) {
                showNotification('Please select a performance metric first', 'warning');
                return;
            }

            showNotification(`Generating ${metric} performance report...`, 'info');
            setTimeout(() => {
                showNotification('Performance report generated successfully!', 'success');
            }, 2000);
        }

        function exportPerformanceReport() {
            showNotification('Exporting performance report...', 'info');
            setTimeout(() => {
                showNotification('Performance report exported successfully!', 'success');
            }, 1500);
        }

        function viewDetailedMetrics() {
            showNotification('Opening detailed performance metrics...', 'info');
            setTimeout(() => {
                showNotification('Detailed metrics loaded!', 'success');
            }, 1500);
        }

        function schedulePerformanceAlert() {
            showNotification('Setting up performance alerts...', 'info');
            setTimeout(() => {
                showNotification('Performance alerts configured successfully!', 'success');
            }, 1500);
        }

        function generateBenchmarkReport() {
            showNotification('Generating benchmark report...', 'info');
            setTimeout(() => {
                showNotification('Benchmark report generated successfully!', 'success');
            }, 1500);
        }

        function refreshPerformanceData() {
            showNotification('Refreshing performance data...', 'info');
            setTimeout(() => {
                loadPerformanceData();
                showNotification('Performance data refreshed successfully!', 'success');
            }, 1000);
        }

        function updateDateTime() {
            const now = new Date();
            const dateElement = document.getElementById('current-date');
            const timeElement = document.getElementById('current-time');
            
            if (dateElement) {
                dateElement.textContent = now.toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            }
            
            if (timeElement) {
                timeElement.textContent = now.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
            }
        }

        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white max-w-sm transform transition-all duration-300 translate-x-full`;
            
            switch(type) {
                case 'success':
                    notification.classList.add('bg-green-500');
                    break;
                case 'error':
                    notification.classList.add('bg-red-500');
                    break;
                case 'warning':
                    notification.classList.add('bg-yellow-500');
                    break;
                default:
                    notification.classList.add('bg-orange-500');
            }
            
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);
            
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 4000);
        }

        // Legacy functions for compatibility
        function addNew() {
            console.log('Adding new reports entry...');
        }

        function searchRecords() {
            console.log('Searching reports records...');
        }

        function viewAnalytics() {
            console.log('Viewing reports analytics...');
        }
    </script>

    <?php include '../includes/pos-footer.php'; ?>
</body>
</html>