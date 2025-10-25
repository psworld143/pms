<?php
session_start();

// Check if user is logged in to POS
if (!isset($_SESSION['pos_user_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Quick Sales Reports';
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
    <script src="../assets/js/pos-sidebar.js"></script>
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
                            <h1 class="text-2xl font-bold text-gray-800 mb-2">Quick Sales Reports</h1>
                            <p class="text-gray-600">Comprehensive analytics and reporting dashboard</p>
                        </div>
                        <div class="text-sm text-gray-500">
                            <div id="current-date" class="font-medium"></div>
                            <div id="current-time" class="text-xs"></div>
                        </div>
                    </div>
                </div>

                <!-- Key Performance Indicators -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                                <p id="total-revenue" class="text-2xl font-bold text-gray-900">₱89,450</p>
                                <p class="text-xs text-green-600">+18% from last month</p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-chart-line text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Orders Processed</p>
                                <p id="orders-processed" class="text-2xl font-bold text-gray-900">2,847</p>
                                <p class="text-xs text-green-600">+245 this week</p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-shopping-cart text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-orange-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Avg. Order Value</p>
                                <p id="avg-order-value" class="text-2xl font-bold text-gray-900">₱142.75</p>
                                <p class="text-xs text-orange-600">₱15.25 increase</p>
                            </div>
                            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-dollar-sign text-orange-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Customer Satisfaction</p>
                                <p id="customer-satisfaction" class="text-2xl font-bold text-gray-900">94.2%</p>
                                <p class="text-xs text-purple-600">+2.1% improvement</p>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-star text-purple-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reports Content -->
                <div class="p-6">
                    <!-- Report Filters -->
                    <div class="mb-6">
                        <div class="flex flex-col lg:flex-row gap-4">
                            <div class="flex-1">
                                <label for="report-type" class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                                <select id="report-type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    <option value="">Select Report Type</option>
                                    <option value="sales">Sales Report</option>
                                    <option value="inventory">Inventory Report</option>
                                    <option value="performance">Performance Report</option>
                                    <option value="customer">Customer Report</option>
                                    <option value="financial">Financial Report</option>
                                </select>
                            </div>
                            <div class="lg:w-48">
                                <label for="date-range" class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                                <select id="date-range" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    <option value="">Select Period</option>
                                    <option value="today">Today</option>
                                    <option value="week">This Week</option>
                                    <option value="month">This Month</option>
                                    <option value="quarter">This Quarter</option>
                                    <option value="year">This Year</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                            </div>
                            <div class="lg:w-48">
                                <label for="format" class="block text-sm font-medium text-gray-700 mb-2">Export Format</label>
                                <select id="format" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    <option value="pdf">PDF</option>
                                    <option value="excel">Excel</option>
                                    <option value="csv">CSV</option>
                                    <option value="print">Print</option>
                                </select>
                            </div>
                            <div class="lg:w-48">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Actions</label>
                                <div class="flex space-x-2">
                                    <button onclick="generateReport()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                                        <i class="fas fa-chart-bar mr-2"></i>Generate
                                    </button>
                                    <button onclick="refreshReports()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                                        <i class="fas fa-sync-alt mr-2"></i>Refresh
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Report Categories -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        <!-- Sales Performance -->
                        <div class="bg-white border border-gray-200 rounded-lg">
                            <div class="p-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                                    Sales Performance
                                </h3>
                            </div>
                            <div class="p-4">
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Daily Average</span>
                                        <span class="font-semibold text-gray-900">₱3,245</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Peak Hour Sales</span>
                                        <span class="font-semibold text-gray-900">2:00 PM - 4:00 PM</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Best Day</span>
                                        <span class="font-semibold text-gray-900">Saturday</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Growth Rate</span>
                                        <span class="font-semibold text-green-600">+18.5%</span>
                                    </div>
                                </div>
                                <button onclick="viewSalesReport()" class="w-full mt-4 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg transition-colors">
                                    <i class="fas fa-eye mr-2"></i>View Detailed Report
                                </button>
                            </div>
                        </div>

                        <!-- Top Products -->
                        <div class="bg-white border border-gray-200 rounded-lg">
                            <div class="p-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-trophy text-yellow-600 mr-2"></i>
                                    Top Products
                                </h3>
                            </div>
                            <div class="p-4">
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center">
                                            <div class="w-6 h-6 bg-yellow-500 rounded-full flex items-center justify-center text-white text-xs font-bold mr-2">1</div>
                                            <span class="text-sm text-gray-900">Coffee (Regular)</span>
                                        </div>
                                        <span class="font-semibold text-gray-900">₱12,750</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center">
                                            <div class="w-6 h-6 bg-gray-400 rounded-full flex items-center justify-center text-white text-xs font-bold mr-2">2</div>
                                            <span class="text-sm text-gray-900">Club Sandwich</span>
                                        </div>
                                        <span class="font-semibold text-gray-900">₱9,250</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center">
                                            <div class="w-6 h-6 bg-orange-500 rounded-full flex items-center justify-center text-white text-xs font-bold mr-2">3</div>
                                            <span class="text-sm text-gray-900">Souvenir T-shirt</span>
                                        </div>
                                        <span class="font-semibold text-gray-900">₱8,750</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center">
                                            <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center text-white text-xs font-bold mr-2">4</div>
                                            <span class="text-sm text-gray-900">Soft Drinks</span>
                                        </div>
                                        <span class="font-semibold text-gray-900">₱6,420</span>
                                    </div>
                                </div>
                                <button onclick="viewProductsReport()" class="w-full mt-4 bg-yellow-600 hover:bg-yellow-700 text-white py-2 rounded-lg transition-colors">
                                    <i class="fas fa-eye mr-2"></i>View Product Report
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Report Actions -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <button onclick="generateSalesReport()" class="bg-blue-600 hover:bg-blue-700 text-white p-4 rounded-lg transition-colors">
                            <div class="flex items-center">
                                <i class="fas fa-chart-bar text-2xl mr-3"></i>
                                <div>
                                    <h4 class="font-semibold">Sales Report</h4>
                                    <p class="text-sm opacity-90">Revenue & Performance</p>
                                </div>
                            </div>
                        </button>

                        <button onclick="generateInventoryReport()" class="bg-green-600 hover:bg-green-700 text-white p-4 rounded-lg transition-colors">
                            <div class="flex items-center">
                                <i class="fas fa-boxes text-2xl mr-3"></i>
                                <div>
                                    <h4 class="font-semibold">Inventory Report</h4>
                                    <p class="text-sm opacity-90">Stock & Movement</p>
                                </div>
                            </div>
                        </button>

                        <button onclick="generateFinancialReport()" class="bg-purple-600 hover:bg-purple-700 text-white p-4 rounded-lg transition-colors">
                            <div class="flex items-center">
                                <i class="fas fa-dollar-sign text-2xl mr-3"></i>
                                <div>
                                    <h4 class="font-semibold">Financial Report</h4>
                                    <p class="text-sm opacity-90">Profit & Loss</p>
                                </div>
                            </div>
                        </button>

                        <button onclick="generateCustomerReport()" class="bg-orange-600 hover:bg-orange-700 text-white p-4 rounded-lg transition-colors">
                            <div class="flex items-center">
                                <i class="fas fa-users text-2xl mr-3"></i>
                                <div>
                                    <h4 class="font-semibold">Customer Report</h4>
                                    <p class="text-sm opacity-90">Behavior & Trends</p>
                                </div>
                            </div>
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

            // Initialize reports functionality
            initializeReports();
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });

        // Reports Management Functions
        function initializeReports() {
            loadReportsData();
            initializeReportFilters();
        }

        function initializeReportFilters() {
            const reportType = document.getElementById('report-type');
            const dateRange = document.getElementById('date-range');
            const format = document.getElementById('format');

            if (reportType) reportType.addEventListener('change', applyReportFilters);
            if (dateRange) dateRange.addEventListener('change', applyReportFilters);
            if (format) format.addEventListener('change', applyReportFilters);
        }

        function loadReportsData() {
            // Simulate loading reports data
            const reportsData = {
                totalRevenue: generateRandomTotalRevenue(),
                ordersProcessed: generateRandomOrdersProcessed(),
                avgOrderValue: generateRandomAvgOrderValue(),
                customerSatisfaction: generateRandomCustomerSatisfaction()
            };

            updateReportsDisplay(reportsData);
        }

        function generateRandomTotalRevenue() {
            return Math.floor(Math.random() * 20000) + 80000;
        }

        function generateRandomOrdersProcessed() {
            return Math.floor(Math.random() * 500) + 2500;
        }

        function generateRandomAvgOrderValue() {
            return (Math.random() * 50 + 120).toFixed(2);
        }

        function generateRandomCustomerSatisfaction() {
            return (Math.random() * 5 + 90).toFixed(1);
        }

        function updateReportsDisplay(data) {
            const totalRevenue = document.getElementById('total-revenue');
            const ordersProcessed = document.getElementById('orders-processed');
            const avgOrderValue = document.getElementById('avg-order-value');
            const customerSatisfaction = document.getElementById('customer-satisfaction');

            if (totalRevenue) {
                totalRevenue.textContent = `₱${data.totalRevenue.toLocaleString()}`;
            }
            if (ordersProcessed) {
                ordersProcessed.textContent = data.ordersProcessed.toLocaleString();
            }
            if (avgOrderValue) {
                avgOrderValue.textContent = `₱${data.avgOrderValue}`;
            }
            if (customerSatisfaction) {
                customerSatisfaction.textContent = `${data.customerSatisfaction}%`;
            }
        }

        function applyReportFilters() {
            const reportType = document.getElementById('report-type')?.value || '';
            const dateRange = document.getElementById('date-range')?.value || '';
            const format = document.getElementById('format')?.value || '';

            console.log('Applying report filters:', { reportType, dateRange, format });
            
            showNotification('Applying report filters...', 'info');
            
            setTimeout(() => {
                loadReportsData();
                showNotification('Reports data updated successfully!', 'success');
            }, 1000);
        }

        // Report Operations
        function generateReport() {
            const reportType = document.getElementById('report-type')?.value || '';
            const dateRange = document.getElementById('date-range')?.value || '';
            const format = document.getElementById('format')?.value || '';

            if (!reportType) {
                showNotification('Please select a report type first', 'warning');
                return;
            }

            showNotification(`Generating ${reportType} report...`, 'info');
            setTimeout(() => {
                showNotification(`${reportType} report generated successfully!`, 'success');
            }, 2000);
        }

        function generateSalesReport() {
            showNotification('Generating sales report...', 'info');
            setTimeout(() => {
                showNotification('Sales report generated successfully!', 'success');
            }, 2000);
        }

        function generateInventoryReport() {
            showNotification('Generating inventory report...', 'info');
            setTimeout(() => {
                showNotification('Inventory report generated successfully!', 'success');
            }, 2000);
        }

        function generateFinancialReport() {
            showNotification('Generating financial report...', 'info');
            setTimeout(() => {
                showNotification('Financial report generated successfully!', 'success');
            }, 2000);
        }

        function generateCustomerReport() {
            showNotification('Generating customer report...', 'info');
            setTimeout(() => {
                showNotification('Customer report generated successfully!', 'success');
            }, 2000);
        }

        function viewSalesReport() {
            showNotification('Opening sales report...', 'info');
            setTimeout(() => {
                showNotification('Sales report loaded!', 'success');
            }, 1500);
        }

        function viewProductsReport() {
            showNotification('Opening products report...', 'info');
            setTimeout(() => {
                showNotification('Products report loaded!', 'success');
            }, 1500);
        }

        function refreshReports() {
            showNotification('Refreshing reports data...', 'info');
            setTimeout(() => {
                loadReportsData();
                showNotification('Reports data refreshed successfully!', 'success');
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
            console.log('Adding new quick sales entry...');
        }

        function searchRecords() {
            console.log('Searching quick sales records...');
        }

        function viewAnalytics() {
            console.log('Viewing quick sales analytics...');
        }
    </script>

    <?php include '../includes/pos-footer.php'; ?>
</body>
</html>