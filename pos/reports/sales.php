<?php
session_start();

// Check if user is logged in to POS
if (!isset($_SESSION['pos_user_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Sales Reports';
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
                            <h1 class="text-2xl font-bold text-gray-800 mb-2">Sales Reports</h1>
                            <p class="text-gray-600">Comprehensive sales analytics and reporting dashboard</p>
                        </div>
                        <div class="text-sm text-gray-500">
                            <div id="current-date" class="font-medium"></div>
                            <div id="current-time" class="text-xs"></div>
                        </div>
                    </div>
                </div>

                <!-- Sales KPIs -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                                <p id="total-revenue" class="text-2xl font-bold text-gray-900">₱156,750</p>
                                <p class="text-xs text-green-600">+22% from last month</p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-chart-line text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Orders</p>
                                <p id="total-orders" class="text-2xl font-bold text-gray-900">4,287</p>
                                <p class="text-xs text-green-600">+18% growth</p>
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
                                <p id="avg-order-value" class="text-2xl font-bold text-gray-900">₱182.45</p>
                                <p class="text-xs text-orange-600">₱24.50 increase</p>
                            </div>
                            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-dollar-sign text-orange-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Conversion Rate</p>
                                <p id="conversion-rate" class="text-2xl font-bold text-gray-900">78.5%</p>
                                <p class="text-xs text-purple-600">+5.2% improvement</p>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-percentage text-purple-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sales Reports Content -->
                <div class="p-6">
                    <!-- Report Filters -->
                    <div class="mb-6">
                        <div class="flex flex-col lg:flex-row gap-4">
                            <div class="flex-1">
                                <label for="sales-date-range" class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                                <select id="sales-date-range" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    <option value="">Select Period</option>
                                    <option value="today">Today</option>
                                    <option value="yesterday">Yesterday</option>
                                    <option value="week">This Week</option>
                                    <option value="month">This Month</option>
                                    <option value="quarter">This Quarter</option>
                                    <option value="year">This Year</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                            </div>
                            <div class="lg:w-48">
                                <label for="sales-category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                                <select id="sales-category" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    <option value="">All Categories</option>
                                    <option value="restaurant">Restaurant</option>
                                    <option value="spa">Spa Services</option>
                                    <option value="room-service">Room Service</option>
                                    <option value="gift-shop">Gift Shop</option>
                                    <option value="events">Events</option>
                                    <option value="quick-sales">Quick Sales</option>
                                </select>
                            </div>
                            <div class="lg:w-48">
                                <label for="sales-format" class="block text-sm font-medium text-gray-700 mb-2">Export Format</label>
                                <select id="sales-format" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    <option value="pdf">PDF</option>
                                    <option value="excel">Excel</option>
                                    <option value="csv">CSV</option>
                                    <option value="print">Print</option>
                                </select>
                            </div>
                            <div class="lg:w-48">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Actions</label>
                                <div class="flex space-x-2">
                                    <button onclick="generateSalesReport()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                                        <i class="fas fa-chart-bar mr-2"></i>Generate
                                    </button>
                                    <button onclick="refreshSalesData()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                                        <i class="fas fa-sync-alt mr-2"></i>Refresh
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sales Performance Charts -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        <!-- Revenue Trend -->
                        <div class="bg-white border border-gray-200 rounded-lg">
                            <div class="p-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                                    Revenue Trend (Last 7 Days)
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Monday</span>
                                        <div class="flex items-center">
                                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                                <div class="bg-blue-500 h-2 rounded-full" style="width: 65%"></div>
                                            </div>
                                            <span class="font-semibold text-gray-900">₱18,450</span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Tuesday</span>
                                        <div class="flex items-center">
                                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                                <div class="bg-blue-500 h-2 rounded-full" style="width: 78%"></div>
                                            </div>
                                            <span class="font-semibold text-gray-900">₱22,180</span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Wednesday</span>
                                        <div class="flex items-center">
                                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                                <div class="bg-blue-500 h-2 rounded-full" style="width: 85%"></div>
                                            </div>
                                            <span class="font-semibold text-gray-900">₱24,620</span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Thursday</span>
                                        <div class="flex items-center">
                                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                                <div class="bg-blue-500 h-2 rounded-full" style="width: 72%"></div>
                                            </div>
                                            <span class="font-semibold text-gray-900">₱20,890</span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Friday</span>
                                        <div class="flex items-center">
                                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                                <div class="bg-blue-500 h-2 rounded-full" style="width: 92%"></div>
                                            </div>
                                            <span class="font-semibold text-gray-900">₱26,750</span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Saturday</span>
                                        <div class="flex items-center">
                                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                                <div class="bg-blue-500 h-2 rounded-full" style="width: 95%"></div>
                                            </div>
                                            <span class="font-semibold text-gray-900">₱28,340</span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Sunday</span>
                                        <div class="flex items-center">
                                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                                <div class="bg-blue-500 h-2 rounded-full" style="width: 88%"></div>
                                            </div>
                                            <span class="font-semibold text-gray-900">₱25,520</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Top Categories -->
                        <div class="bg-white border border-gray-200 rounded-lg">
                            <div class="p-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-chart-pie text-green-600 mr-2"></i>
                                    Sales by Category
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center">
                                            <div class="w-4 h-4 bg-blue-500 rounded-full mr-3"></div>
                                            <span class="text-sm text-gray-900">Restaurant</span>
                                        </div>
                                        <span class="font-semibold text-gray-900">42% (₱65,835)</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center">
                                            <div class="w-4 h-4 bg-green-500 rounded-full mr-3"></div>
                                            <span class="text-sm text-gray-900">Spa Services</span>
                                        </div>
                                        <span class="font-semibold text-gray-900">28% (₱43,890)</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center">
                                            <div class="w-4 h-4 bg-orange-500 rounded-full mr-3"></div>
                                            <span class="text-sm text-gray-900">Room Service</span>
                                        </div>
                                        <span class="font-semibold text-gray-900">15% (₱23,512)</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center">
                                            <div class="w-4 h-4 bg-purple-500 rounded-full mr-3"></div>
                                            <span class="text-sm text-gray-900">Gift Shop</span>
                                        </div>
                                        <span class="font-semibold text-gray-900">10% (₱15,675)</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center">
                                            <div class="w-4 h-4 bg-pink-500 rounded-full mr-3"></div>
                                            <span class="text-sm text-gray-900">Events</span>
                                        </div>
                                        <span class="font-semibold text-gray-900">5% (₱7,838)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sales Summary Table -->
                    <div class="bg-white border border-gray-200 rounded-lg">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-table text-purple-600 mr-2"></i>
                                Sales Summary
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg. Order</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Growth</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Restaurant</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">1,847</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱65,835</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱178.45</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">+15%</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Spa Services</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">1,234</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱43,890</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱355.67</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">+28%</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Room Service</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">892</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱23,512</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱263.58</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">+12%</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Gift Shop</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">567</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱15,675</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱276.45</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">+8%</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Events</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">234</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱7,838</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱335.04</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">+35%</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="mt-6 flex flex-wrap gap-4">
                        <button onclick="exportSalesReport()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-download mr-2"></i>Export Report
                        </button>
                        <button onclick="viewDetailedAnalytics()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-chart-bar mr-2"></i>Detailed Analytics
                        </button>
                        <button onclick="scheduleReport()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-clock mr-2"></i>Schedule Report
                        </button>
                        <button onclick="shareReport()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-share mr-2"></i>Share Report
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

            // Initialize sales reports functionality
            initializeSalesReports();
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });

        // Sales Reports Management Functions
        function initializeSalesReports() {
            loadSalesData();
            initializeSalesFilters();
        }

        function initializeSalesFilters() {
            const dateRange = document.getElementById('sales-date-range');
            const category = document.getElementById('sales-category');
            const format = document.getElementById('sales-format');

            if (dateRange) dateRange.addEventListener('change', applySalesFilters);
            if (category) category.addEventListener('change', applySalesFilters);
            if (format) format.addEventListener('change', applySalesFilters);
        }

        function loadSalesData() {
            // Simulate loading sales data
            const salesData = {
                totalRevenue: generateRandomTotalRevenue(),
                totalOrders: generateRandomTotalOrders(),
                avgOrderValue: generateRandomAvgOrderValue(),
                conversionRate: generateRandomConversionRate()
            };

            updateSalesDisplay(salesData);
        }

        function generateRandomTotalRevenue() {
            return Math.floor(Math.random() * 50000) + 120000;
        }

        function generateRandomTotalOrders() {
            return Math.floor(Math.random() * 1000) + 3500;
        }

        function generateRandomAvgOrderValue() {
            return (Math.random() * 100 + 150).toFixed(2);
        }

        function generateRandomConversionRate() {
            return (Math.random() * 20 + 70).toFixed(1);
        }

        function updateSalesDisplay(data) {
            const totalRevenue = document.getElementById('total-revenue');
            const totalOrders = document.getElementById('total-orders');
            const avgOrderValue = document.getElementById('avg-order-value');
            const conversionRate = document.getElementById('conversion-rate');

            if (totalRevenue) {
                totalRevenue.textContent = `₱${data.totalRevenue.toLocaleString()}`;
            }
            if (totalOrders) {
                totalOrders.textContent = data.totalOrders.toLocaleString();
            }
            if (avgOrderValue) {
                avgOrderValue.textContent = `₱${data.avgOrderValue}`;
            }
            if (conversionRate) {
                conversionRate.textContent = `${data.conversionRate}%`;
            }
        }

        function applySalesFilters() {
            const dateRange = document.getElementById('sales-date-range')?.value || '';
            const category = document.getElementById('sales-category')?.value || '';
            const format = document.getElementById('sales-format')?.value || '';

            console.log('Applying sales filters:', { dateRange, category, format });
            
            showNotification('Applying sales filters...', 'info');
            
            setTimeout(() => {
                loadSalesData();
                showNotification('Sales data updated successfully!', 'success');
            }, 1000);
        }

        // Sales Report Operations
        function generateSalesReport() {
            const dateRange = document.getElementById('sales-date-range')?.value || '';
            const category = document.getElementById('sales-category')?.value || '';
            const format = document.getElementById('sales-format')?.value || '';

            if (!dateRange) {
                showNotification('Please select a date range first', 'warning');
                return;
            }

            showNotification(`Generating sales report for ${dateRange}...`, 'info');
            setTimeout(() => {
                showNotification('Sales report generated successfully!', 'success');
            }, 2000);
        }

        function exportSalesReport() {
            showNotification('Exporting sales report...', 'info');
            setTimeout(() => {
                showNotification('Sales report exported successfully!', 'success');
            }, 1500);
        }

        function viewDetailedAnalytics() {
            showNotification('Opening detailed sales analytics...', 'info');
            setTimeout(() => {
                showNotification('Detailed analytics loaded!', 'success');
            }, 1500);
        }

        function scheduleReport() {
            showNotification('Opening report scheduler...', 'info');
            setTimeout(() => {
                showNotification('Report scheduled successfully!', 'success');
            }, 1500);
        }

        function shareReport() {
            showNotification('Preparing report for sharing...', 'info');
            setTimeout(() => {
                showNotification('Report shared successfully!', 'success');
            }, 1500);
        }

        function refreshSalesData() {
            showNotification('Refreshing sales data...', 'info');
            setTimeout(() => {
                loadSalesData();
                showNotification('Sales data refreshed successfully!', 'success');
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