<?php
// VPS Session Fix - Robust session configuration
require_once __DIR__ . '/../../vps_session_fix.php';

// Check if user is logged in to POS
if (!isset($_SESSION['pos_user_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Sales History';
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
                            <h1 class="text-2xl font-bold text-gray-800 mb-2">Sales History</h1>
                            <p class="text-gray-600">Complete transaction history and analytics</p>
                        </div>
                        <div class="text-sm text-gray-500">
                            <div id="current-date" class="font-medium"></div>
                            <div id="current-time" class="text-xs"></div>
                        </div>
                    </div>
                </div>

                <!-- Sales Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Sales</p>
                                <p id="total-sales" class="text-2xl font-bold text-gray-900">₱47,250</p>
                                <p class="text-xs text-green-600">+12% from last month</p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-chart-line text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Transactions</p>
                                <p id="total-transactions" class="text-2xl font-bold text-gray-900">1,247</p>
                                <p class="text-xs text-green-600">+28 this week</p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-receipt text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-orange-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Avg. Order</p>
                                <p id="avg-order" class="text-2xl font-bold text-gray-900">₱127.50</p>
                                <p class="text-xs text-orange-600">₱8.50 increase</p>
                            </div>
                            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-shopping-cart text-orange-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Top Category</p>
                                <p id="top-category" class="text-2xl font-bold text-gray-900">Beverages</p>
                                <p class="text-xs text-purple-600">35% of sales</p>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-star text-purple-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sales History Content -->
                <div class="p-6">
                    <!-- History Filters -->
                    <div class="mb-6">
                        <div class="flex flex-col lg:flex-row gap-4">
                            <div class="flex-1">
                                <label for="search-history" class="block text-sm font-medium text-gray-700 mb-2">Search History</label>
                                <div class="relative">
                                    <input type="text" id="search-history" placeholder="Search by transaction ID, items, or staff..." 
                                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                                </div>
                            </div>
                            <div class="lg:w-48">
                                <label for="period-filter" class="block text-sm font-medium text-gray-700 mb-2">Time Period</label>
                                <select id="period-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    <option value="">All Time</option>
                                    <option value="today">Today</option>
                                    <option value="week">This Week</option>
                                    <option value="month">This Month</option>
                                    <option value="quarter">This Quarter</option>
                                    <option value="year">This Year</option>
                                </select>
                            </div>
                            <div class="lg:w-48">
                                <label for="category-filter" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                                <select id="category-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    <option value="">All Categories</option>
                                    <option value="food">Food & Beverages</option>
                                    <option value="snacks">Snacks</option>
                                    <option value="souvenirs">Souvenirs</option>
                                </select>
                            </div>
                            <div class="lg:w-48">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Actions</label>
                                <div class="flex space-x-2">
                                    <button onclick="refreshHistory()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                                        <i class="fas fa-sync-alt mr-2"></i>Refresh
                                    </button>
                                    <button onclick="exportHistory()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                                        <i class="fas fa-download mr-2"></i>Export
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sales History Timeline -->
                    <div class="space-y-4">
                        <!-- Today's Sales -->
                        <div class="bg-white border border-gray-200 rounded-lg">
                            <div class="p-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-calendar-day text-blue-600 mr-2"></i>
                                    Today's Sales
                                    <span class="ml-2 text-sm font-normal text-gray-500">(December 20, 2024)</span>
                                </h3>
                            </div>
                            <div class="p-4">
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                                <i class="fas fa-check-circle text-green-600"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-medium text-gray-900">Morning Shift (8:00 AM - 4:00 PM)</h4>
                                                <p class="text-sm text-gray-600">Sarah Johnson • 45 transactions</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-semibold text-gray-900">₱12,450</div>
                                            <div class="text-sm text-gray-500">₱276.67 avg</div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                                <i class="fas fa-clock text-blue-600"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-medium text-gray-900">Evening Shift (4:00 PM - 12:00 AM)</h4>
                                                <p class="text-sm text-gray-600">Mike Chen • 32 transactions</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-semibold text-gray-900">₱8,750</div>
                                            <div class="text-sm text-gray-500">₱273.44 avg</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Weekly Summary -->
                        <div class="bg-white border border-gray-200 rounded-lg">
                            <div class="p-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-chart-bar text-green-600 mr-2"></i>
                                    Weekly Summary
                                    <span class="ml-2 text-sm font-normal text-gray-500">(December 16-22, 2024)</span>
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-7 gap-4">
                                    <div class="text-center p-3 bg-blue-50 rounded-lg">
                                        <div class="text-sm font-medium text-gray-600">Mon</div>
                                        <div class="text-lg font-bold text-blue-600">₱6,420</div>
                                        <div class="text-xs text-gray-500">28 trans</div>
                                    </div>
                                    <div class="text-center p-3 bg-green-50 rounded-lg">
                                        <div class="text-sm font-medium text-gray-600">Tue</div>
                                        <div class="text-lg font-bold text-green-600">₱7,850</div>
                                        <div class="text-xs text-gray-500">35 trans</div>
                                    </div>
                                    <div class="text-center p-3 bg-orange-50 rounded-lg">
                                        <div class="text-sm font-medium text-gray-600">Wed</div>
                                        <div class="text-lg font-bold text-orange-600">₱5,920</div>
                                        <div class="text-xs text-gray-500">24 trans</div>
                                    </div>
                                    <div class="text-center p-3 bg-purple-50 rounded-lg">
                                        <div class="text-sm font-medium text-gray-600">Thu</div>
                                        <div class="text-lg font-bold text-purple-600">₱8,340</div>
                                        <div class="text-xs text-gray-500">39 trans</div>
                                    </div>
                                    <div class="text-center p-3 bg-pink-50 rounded-lg">
                                        <div class="text-sm font-medium text-gray-600">Fri</div>
                                        <div class="text-lg font-bold text-pink-600">₱9,120</div>
                                        <div class="text-xs text-gray-500">42 trans</div>
                                    </div>
                                    <div class="text-center p-3 bg-yellow-50 rounded-lg">
                                        <div class="text-sm font-medium text-gray-600">Sat</div>
                                        <div class="text-lg font-bold text-yellow-600">₱11,580</div>
                                        <div class="text-xs text-gray-500">48 trans</div>
                                    </div>
                                    <div class="text-center p-3 bg-red-50 rounded-lg">
                                        <div class="text-sm font-medium text-gray-600">Sun</div>
                                        <div class="text-lg font-bold text-red-600">₱13,020</div>
                                        <div class="text-xs text-gray-500">52 trans</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Top Selling Items -->
                        <div class="bg-white border border-gray-200 rounded-lg">
                            <div class="p-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-trophy text-yellow-600 mr-2"></i>
                                    Top Selling Items This Week
                                </h3>
                            </div>
                            <div class="p-4">
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center text-white font-bold text-sm mr-3">1</div>
                                            <div>
                                                <h4 class="font-medium text-gray-900">Coffee (Regular)</h4>
                                                <p class="text-sm text-gray-600">Beverages • ₱85.00 each</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-semibold text-gray-900">156 sold</div>
                                            <div class="text-sm text-gray-500">₱13,260</div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-gray-400 rounded-full flex items-center justify-center text-white font-bold text-sm mr-3">2</div>
                                            <div>
                                                <h4 class="font-medium text-gray-900">Club Sandwich</h4>
                                                <p class="text-sm text-gray-600">Food • ₱185.00 each</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-semibold text-gray-900">89 sold</div>
                                            <div class="text-sm text-gray-500">₱16,465</div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center text-white font-bold text-sm mr-3">3</div>
                                            <div>
                                                <h4 class="font-medium text-gray-900">Souvenir T-shirt</h4>
                                                <p class="text-sm text-gray-600">Souvenirs • ₱350.00 each</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-semibold text-gray-900">45 sold</div>
                                            <div class="text-sm text-gray-500">₱15,750</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="mt-6 flex flex-wrap gap-4">
                        <button onclick="viewDetailedAnalytics()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-chart-line mr-2"></i>Detailed Analytics
                        </button>
                        <button onclick="generateSalesReport()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-file-alt mr-2"></i>Generate Report
                        </button>
                        <button onclick="viewTrendAnalysis()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-chart-area mr-2"></i>Trend Analysis
                        </button>
                        <button onclick="exportSalesData()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-download mr-2"></i>Export Data
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

            // Initialize history functionality
            initializeHistory();
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });

        // History Management Functions
        function initializeHistory() {
            loadHistoryData();
            initializeHistoryFilters();
        }

        function initializeHistoryFilters() {
            const searchInput = document.getElementById('search-history');
            const periodFilter = document.getElementById('period-filter');
            const categoryFilter = document.getElementById('category-filter');

            if (searchInput) searchInput.addEventListener('input', applyHistoryFilters);
            if (periodFilter) periodFilter.addEventListener('change', applyHistoryFilters);
            if (categoryFilter) categoryFilter.addEventListener('change', applyHistoryFilters);
        }

        function loadHistoryData() {
            // Simulate loading history data
            const historyData = {
                totalSales: generateRandomTotalSales(),
                totalTransactions: generateRandomTotalTransactions(),
                avgOrder: generateRandomAvgOrder(),
                topCategory: generateRandomTopCategory()
            };

            updateHistoryDisplay(historyData);
        }

        function generateRandomTotalSales() {
            return Math.floor(Math.random() * 10000) + 40000;
        }

        function generateRandomTotalTransactions() {
            return Math.floor(Math.random() * 200) + 1200;
        }

        function generateRandomAvgOrder() {
            return (Math.random() * 50 + 100).toFixed(2);
        }

        function generateRandomTopCategory() {
            const categories = ['Beverages', 'Food', 'Snacks', 'Souvenirs'];
            return categories[Math.floor(Math.random() * categories.length)];
        }

        function updateHistoryDisplay(data) {
            const totalSales = document.getElementById('total-sales');
            const totalTransactions = document.getElementById('total-transactions');
            const avgOrder = document.getElementById('avg-order');
            const topCategory = document.getElementById('top-category');

            if (totalSales) {
                totalSales.textContent = `₱${data.totalSales.toLocaleString()}`;
            }
            if (totalTransactions) {
                totalTransactions.textContent = data.totalTransactions.toLocaleString();
            }
            if (avgOrder) {
                avgOrder.textContent = `₱${data.avgOrder}`;
            }
            if (topCategory) {
                topCategory.textContent = data.topCategory;
            }
        }

        function applyHistoryFilters() {
            const searchTerm = document.getElementById('search-history')?.value.toLowerCase() || '';
            const period = document.getElementById('period-filter')?.value || '';
            const category = document.getElementById('category-filter')?.value || '';

            console.log('Applying history filters:', { searchTerm, period, category });
            
            showNotification('Applying history filters...', 'info');
            
            setTimeout(() => {
                loadHistoryData();
                showNotification('History data updated successfully!', 'success');
            }, 1000);
        }

        // History Operations
        function viewDetailedAnalytics() {
            showNotification('Opening detailed analytics...', 'info');
            setTimeout(() => {
                showNotification('Detailed analytics loaded!', 'success');
            }, 1500);
        }

        function generateSalesReport() {
            showNotification('Generating sales report...', 'info');
            setTimeout(() => {
                showNotification('Sales report generated successfully!', 'success');
            }, 2000);
        }

        function viewTrendAnalysis() {
            showNotification('Opening trend analysis...', 'info');
            setTimeout(() => {
                showNotification('Trend analysis loaded!', 'success');
            }, 1500);
        }

        function exportSalesData() {
            showNotification('Exporting sales data...', 'info');
            setTimeout(() => {
                showNotification('Sales data exported successfully!', 'success');
            }, 1500);
        }

        function refreshHistory() {
            showNotification('Refreshing history data...', 'info');
            setTimeout(() => {
                loadHistoryData();
                showNotification('History data refreshed successfully!', 'success');
            }, 1000);
        }

        function exportHistory() {
            showNotification('Exporting history data...', 'info');
            setTimeout(() => {
                showNotification('History data exported successfully!', 'success');
            }, 1500);
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