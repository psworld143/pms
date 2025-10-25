<?php
// VPS Session Fix - Robust session configuration
require_once __DIR__ . '/../../vps_session_fix.php';

// Check if user is logged in to POS
if (!isset($_SESSION['pos_user_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Room Service Reports';
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
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
                <div>
                    <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Room Service Reports & Analytics</h2>
                    <p class="text-gray-600 mt-1">Comprehensive reporting and analytics for room service operations</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <label for="date-range" class="text-sm text-gray-600">Date Range:</label>
                        <select id="date-range" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="week">This Week</option>
                            <option value="month" selected>This Month</option>
                            <option value="quarter">This Quarter</option>
                            <option value="year">This Year</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    <button onclick="refreshReports()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-sync-alt mr-2"></i>Refresh
                    </button>
                    <button onclick="exportReports()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-download mr-2"></i>Export
                    </button>
                </div>
            </div>

            <!-- Key Performance Indicators -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <i class="fas fa-dollar-sign text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="total-revenue">₱12,450</h3>
                            <p class="text-sm text-gray-600">Total Revenue</p>
                            <p class="text-xs text-green-600 mt-1">
                                <i class="fas fa-arrow-up"></i> +12.5% vs last month
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-lg">
                            <i class="fas fa-utensils text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="total-orders">284</h3>
                            <p class="text-sm text-gray-600">Total Orders</p>
                            <p class="text-xs text-green-600 mt-1">
                                <i class="fas fa-arrow-up"></i> +8.3% vs last month
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-lg">
                            <i class="fas fa-clock text-yellow-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="avg-delivery-time">18</h3>
                            <p class="text-sm text-gray-600">Avg Delivery Time (min)</p>
                            <p class="text-xs text-red-600 mt-1">
                                <i class="fas fa-arrow-down"></i> -2.1 min vs last month
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-lg">
                            <i class="fas fa-star text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="avg-rating">4.7</h3>
                            <p class="text-sm text-gray-600">Average Rating</p>
                            <p class="text-xs text-green-600 mt-1">
                                <i class="fas fa-arrow-up"></i> +0.2 vs last month
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Analytics Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Revenue Chart -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Revenue Trend</h3>
                        <div class="flex items-center space-x-2">
                            <button onclick="toggleChartType('revenue')" class="text-sm text-blue-600 hover:text-blue-800">
                                <i class="fas fa-chart-line"></i> Line
                            </button>
                            <button onclick="toggleChartType('revenue')" class="text-sm text-gray-600 hover:text-gray-800">
                                <i class="fas fa-chart-bar"></i> Bar
                            </button>
                        </div>
                    </div>
                    <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
                        <div class="text-center">
                            <i class="fas fa-chart-line text-4xl text-gray-400 mb-2"></i>
                            <p class="text-gray-600">Revenue Chart</p>
                            <p class="text-sm text-gray-500">Interactive chart showing daily revenue trends</p>
                        </div>
                    </div>
                </div>

                <!-- Order Volume Chart -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Order Volume</h3>
                        <div class="flex items-center space-x-2">
                            <button onclick="toggleChartType('orders')" class="text-sm text-blue-600 hover:text-blue-800">
                                <i class="fas fa-chart-bar"></i> Bar
                            </button>
                            <button onclick="toggleChartType('orders')" class="text-sm text-gray-600 hover:text-gray-800">
                                <i class="fas fa-chart-pie"></i> Pie
                            </button>
                        </div>
                    </div>
                    <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
                        <div class="text-center">
                            <i class="fas fa-chart-bar text-4xl text-gray-400 mb-2"></i>
                            <p class="text-gray-600">Order Volume Chart</p>
                            <p class="text-sm text-gray-500">Visualization of order distribution by time</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Popular Items and Performance Metrics -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Top Selling Items -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Top Selling Items</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-utensils text-blue-600"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Club Sandwich</p>
                                    <p class="text-sm text-gray-500">Main Course</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-gray-900">45</p>
                                <p class="text-sm text-gray-500">orders</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                        <div class="flex items-center">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-pizza-slice text-green-600"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Margherita Pizza</p>
                                    <p class="text-sm text-gray-500">Main Course</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-gray-900">38</p>
                                <p class="text-sm text-gray-500">orders</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-leaf text-yellow-600"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Caesar Salad</p>
                                    <p class="text-sm text-gray-500">Appetizer</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-gray-900">32</p>
                                <p class="text-sm text-gray-500">orders</p>
                        </div>
                    </div>
                    
                        <div class="flex items-center justify-between">
                        <div class="flex items-center">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-birthday-cake text-purple-600"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Chocolate Cake</p>
                                    <p class="text-sm text-gray-500">Dessert</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-gray-900">28</p>
                                <p class="text-sm text-gray-500">orders</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Performance -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Delivery Performance</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                                <span class="text-sm text-gray-600">On Time</span>
                            </div>
                            <span class="font-bold text-green-600">89%</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-yellow-500 rounded-full mr-3"></div>
                                <span class="text-sm text-gray-600">Slightly Late</span>
                            </div>
                            <span class="font-bold text-yellow-600">8%</span>
                    </div>
                    
                        <div class="flex items-center justify-between">
                        <div class="flex items-center">
                                <div class="w-3 h-3 bg-red-500 rounded-full mr-3"></div>
                                <span class="text-sm text-gray-600">Late</span>
                            </div>
                            <span class="font-bold text-red-600">3%</span>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Average Delivery Time</span>
                                <span class="font-medium">18 minutes</span>
                            </div>
                            <div class="flex justify-between text-sm mt-2">
                                <span class="text-gray-600">Fastest Delivery</span>
                                <span class="font-medium">8 minutes</span>
                            </div>
                            <div class="flex justify-between text-sm mt-2">
                                <span class="text-gray-600">Slowest Delivery</span>
                                <span class="font-medium">45 minutes</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Peak Hours -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Peak Hours</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">7:00 AM - 9:00 AM</span>
                            <div class="flex items-center">
                                <div class="w-20 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: 85%"></div>
                                </div>
                                <span class="text-sm font-medium">85%</span>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">12:00 PM - 2:00 PM</span>
                            <div class="flex items-center">
                                <div class="w-20 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-green-600 h-2 rounded-full" style="width: 92%"></div>
                                </div>
                                <span class="text-sm font-medium">92%</span>
                        </div>
                    </div>
                    
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">6:00 PM - 8:00 PM</span>
                        <div class="flex items-center">
                                <div class="w-20 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-yellow-600 h-2 rounded-full" style="width: 78%"></div>
                                </div>
                                <span class="text-sm font-medium">78%</span>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">9:00 PM - 11:00 PM</span>
                            <div class="flex items-center">
                                <div class="w-20 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-purple-600 h-2 rounded-full" style="width: 65%"></div>
                                </div>
                                <span class="text-sm font-medium">65%</span>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- Detailed Reports Table -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <h3 class="text-lg font-semibold text-gray-800">Detailed Reports</h3>
                        <div class="flex items-center space-x-3">
                            <select id="report-type" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="sales">Sales Report</option>
                                <option value="orders">Orders Report</option>
                                <option value="delivery">Delivery Report</option>
                                <option value="performance">Performance Report</option>
                            </select>
                            <button onclick="generateReport()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-chart-bar mr-2"></i>Generate Report
                        </button>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Order Value</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Delivery Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer Rating</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2024-01-15</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">45</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱1,250</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱27.78</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">16 min</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex text-yellow-400">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                        </div>
                                        <span class="ml-2 text-sm text-gray-600">4.8</span>
                                    </div>
                                </td>
                            </tr>
                            
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2024-01-14</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">52</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱1,380</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱26.54</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">18 min</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex text-yellow-400">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="far fa-star"></i>
                                        </div>
                                        <span class="ml-2 text-sm text-gray-600">4.6</span>
                                    </div>
                                </td>
                            </tr>
                            
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2024-01-13</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">38</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱980</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱25.79</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">20 min</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex text-yellow-400">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                        </div>
                                        <span class="ml-2 text-sm text-gray-600">4.7</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <button class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Previous
                            </button>
                            <button class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Next
                            </button>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing <span class="font-medium">1</span> to <span class="font-medium">10</span> of <span class="font-medium">97</span> results
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                    <button class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <button class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">1</button>
                                    <button class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-blue-50 text-sm font-medium text-blue-600">2</button>
                                    <button class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">3</button>
                                    <button class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Room service module functionality
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

            // Initialize reports functionality
            initializeReports();
        });

        // Reports Management Functions
        function initializeReports() {
            loadKPIData();
            initializeDateRange();
            initializeReportFilters();
        }

        function loadKPIData() {
            // Simulate loading KPI data
            const kpiData = {
                totalRevenue: Math.floor(Math.random() * 20000) + 10000,
                totalOrders: Math.floor(Math.random() * 500) + 200,
                avgDeliveryTime: Math.floor(Math.random() * 10) + 15,
                avgRating: (Math.random() * 1 + 4).toFixed(1)
            };

            updateKPIDisplay(kpiData);
        }

        function updateKPIDisplay(data) {
            const totalRevenue = document.getElementById('total-revenue');
            const totalOrders = document.getElementById('total-orders');
            const avgDeliveryTime = document.getElementById('avg-delivery-time');
            const avgRating = document.getElementById('avg-rating');

            if (totalRevenue) totalRevenue.textContent = `₱${data.totalRevenue.toLocaleString()}`;
            if (totalOrders) totalOrders.textContent = data.totalOrders;
            if (avgDeliveryTime) avgDeliveryTime.textContent = data.avgDeliveryTime;
            if (avgRating) avgRating.textContent = data.avgRating;
        }

        function initializeDateRange() {
            const dateRange = document.getElementById('date-range');
            if (dateRange) {
                dateRange.addEventListener('change', function() {
                    const selectedRange = this.value;
                    loadReportsByDateRange(selectedRange);
                    showNotification(`Loading reports for ${selectedRange}`, 'info');
                });
            }
        }

        function initializeReportFilters() {
            const reportType = document.getElementById('report-type');
            if (reportType) {
                reportType.addEventListener('change', function() {
                    const selectedType = this.value;
                    showNotification(`Switching to ${selectedType} report`, 'info');
                });
            }
        }

        function loadReportsByDateRange(range) {
            console.log(`Loading reports for date range: ${range}`);
            
            const rangeMultipliers = {
                'today': 0.1,
                'yesterday': 0.15,
                'week': 0.3,
                'month': 1,
                'quarter': 2.5,
                'year': 8
            };

            const multiplier = rangeMultipliers[range] || 1;
            const baseData = {
                totalRevenue: 12450,
                totalOrders: 284,
                avgDeliveryTime: 18,
                avgRating: 4.7
            };

            const adjustedData = {
                totalRevenue: Math.floor(baseData.totalRevenue * multiplier),
                totalOrders: Math.floor(baseData.totalOrders * multiplier),
                avgDeliveryTime: baseData.avgDeliveryTime,
                avgRating: baseData.avgRating
            };

            updateKPIDisplay(adjustedData);
        }

        function refreshReports() {
            showNotification('Refreshing reports...', 'info');
            
            const refreshBtn = event.target;
            const originalText = refreshBtn.innerHTML;
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Refreshing...';
            refreshBtn.disabled = true;

            setTimeout(() => {
                loadKPIData();
                refreshBtn.innerHTML = originalText;
                refreshBtn.disabled = false;
                showNotification('Reports refreshed successfully', 'success');
            }, 2000);
        }

        function exportReports() {
            const dateRange = document.getElementById('date-range')?.value || 'month';
            const reportType = document.getElementById('report-type')?.value || 'sales';
            
            showNotification(`Exporting ${reportType} report for ${dateRange}...`, 'info');
            
            setTimeout(() => {
                showNotification('Report exported successfully!', 'success');
                console.log(`Exporting ${reportType} report for ${dateRange}`);
            }, 1500);
        }

        function generateReport() {
            const reportType = document.getElementById('report-type')?.value || 'sales';
            const dateRange = document.getElementById('date-range')?.value || 'month';
            
            showNotification(`Generating ${reportType} report for ${dateRange}...`, 'info');
            
            setTimeout(() => {
                showNotification(`${reportType} report generated successfully`, 'success');
                updateReportTable(reportType);
            }, 2000);
        }

        function updateReportTable(reportType) {
            const table = document.querySelector('table tbody');
            if (!table) return;

            const reportData = {
                sales: [
                    { date: '2024-01-15', orders: 45, revenue: '$1,250', avgOrder: '$27.78', delivery: '16 min', rating: 4.8 },
                    { date: '2024-01-14', orders: 52, revenue: '$1,380', avgOrder: '$26.54', delivery: '18 min', rating: 4.6 },
                    { date: '2024-01-13', orders: 38, revenue: '$980', avgOrder: '$25.79', delivery: '20 min', rating: 4.7 }
                ],
                orders: [
                    { date: '2024-01-15', orders: 45, revenue: '$1,250', avgOrder: '$27.78', delivery: '16 min', rating: 4.8 },
                    { date: '2024-01-14', orders: 52, revenue: '$1,380', avgOrder: '$26.54', delivery: '18 min', rating: 4.6 },
                    { date: '2024-01-13', orders: 38, revenue: '$980', avgOrder: '$25.79', delivery: '20 min', rating: 4.7 }
                ],
                delivery: [
                    { date: '2024-01-15', orders: 45, revenue: '$1,250', avgOrder: '$27.78', delivery: '16 min', rating: 4.8 },
                    { date: '2024-01-14', orders: 52, revenue: '$1,380', avgOrder: '$26.54', delivery: '18 min', rating: 4.6 },
                    { date: '2024-01-13', orders: 38, revenue: '$980', avgOrder: '$25.79', delivery: '20 min', rating: 4.7 }
                ],
                performance: [
                    { date: '2024-01-15', orders: 45, revenue: '$1,250', avgOrder: '$27.78', delivery: '16 min', rating: 4.8 },
                    { date: '2024-01-14', orders: 52, revenue: '$1,380', avgOrder: '$26.54', delivery: '18 min', rating: 4.6 },
                    { date: '2024-01-13', orders: 38, revenue: '$980', avgOrder: '$25.79', delivery: '20 min', rating: 4.7 }
                ]
            };

            const data = reportData[reportType] || reportData.sales;
            
            table.innerHTML = '';
            
            data.forEach(row => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50';
                tr.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.date}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.orders}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.revenue}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.avgOrder}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.delivery}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex text-yellow-400">
                                ${generateStarRating(row.rating)}
                            </div>
                            <span class="ml-2 text-sm text-gray-600">${row.rating}</span>
                        </div>
                    </td>
                `;
                table.appendChild(tr);
            });
        }

        function generateStarRating(rating) {
            const fullStars = Math.floor(rating);
            const hasHalfStar = rating % 1 >= 0.5;
            let stars = '';
            
            for (let i = 0; i < fullStars; i++) {
                stars += '<i class="fas fa-star"></i>';
            }
            
            if (hasHalfStar) {
                stars += '<i class="fas fa-star-half-alt"></i>';
            }
            
            const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
            for (let i = 0; i < emptyStars; i++) {
                stars += '<i class="far fa-star"></i>';
            }
            
            return stars;
        }

        function toggleChartType(chartId) {
            const buttons = event.target.closest('.flex').querySelectorAll('button');
            buttons.forEach(btn => {
                btn.classList.remove('text-blue-600');
                btn.classList.add('text-gray-600');
            });
            event.target.classList.remove('text-gray-600');
            event.target.classList.add('text-blue-600');
            
            showNotification(`Switched to ${event.target.textContent.trim()} chart`, 'info');
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
                    notification.classList.add('bg-blue-500');
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
            console.log('Adding new room service entry...');
        }

        function searchRecords() {
            console.log('Searching room service records...');
        }

        function viewAnalytics() {
            console.log('Viewing room service analytics...');
        }
    </script>

    <?php include '../includes/pos-footer.php'; ?>
</body>
</html>