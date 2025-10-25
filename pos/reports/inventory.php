<?php
session_start();

// Check if user is logged in to POS
if (!isset($_SESSION['pos_user_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Inventory Reports';
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
                            <h1 class="text-2xl font-bold text-gray-800 mb-2">Inventory Reports</h1>
                            <p class="text-gray-600">Comprehensive inventory analytics and stock management reports</p>
                        </div>
                        <div class="text-sm text-gray-500">
                            <div id="current-date" class="font-medium"></div>
                            <div id="current-time" class="text-xs"></div>
                        </div>
                    </div>
                </div>

                <!-- Inventory KPIs -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Items</p>
                                <p id="total-items" class="text-2xl font-bold text-gray-900">2,847</p>
                                <p class="text-xs text-blue-600">+156 this month</p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-boxes text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">In Stock</p>
                                <p id="in-stock" class="text-2xl font-bold text-gray-900">2,234</p>
                                <p class="text-xs text-green-600">78.5% availability</p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-check-circle text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-orange-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Low Stock</p>
                                <p id="low-stock" class="text-2xl font-bold text-gray-900">89</p>
                                <p class="text-xs text-orange-600">Need reorder</p>
                            </div>
                            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-orange-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Out of Stock</p>
                                <p id="out-of-stock" class="text-2xl font-bold text-gray-900">23</p>
                                <p class="text-xs text-red-600">Urgent reorder</p>
                            </div>
                            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-times-circle text-red-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inventory Reports Content -->
                <div class="p-6">
                    <!-- Report Filters -->
                    <div class="mb-6">
                        <div class="flex flex-col lg:flex-row gap-4">
                            <div class="flex-1">
                                <label for="inventory-category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                                <select id="inventory-category" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    <option value="">All Categories</option>
                                    <option value="food">Food & Beverages</option>
                                    <option value="spa">Spa Products</option>
                                    <option value="cleaning">Cleaning Supplies</option>
                                    <option value="office">Office Supplies</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="linens">Linens & Towels</option>
                                </select>
                            </div>
                            <div class="lg:w-48">
                                <label for="inventory-status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select id="inventory-status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    <option value="">All Status</option>
                                    <option value="in-stock">In Stock</option>
                                    <option value="low-stock">Low Stock</option>
                                    <option value="out-of-stock">Out of Stock</option>
                                    <option value="discontinued">Discontinued</option>
                                </select>
                            </div>
                            <div class="lg:w-48">
                                <label for="inventory-location" class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                                <select id="inventory-location" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    <option value="">All Locations</option>
                                    <option value="main-storage">Main Storage</option>
                                    <option value="kitchen">Kitchen</option>
                                    <option value="spa-storage">Spa Storage</option>
                                    <option value="housekeeping">Housekeeping</option>
                                    <option value="office">Office</option>
                                </select>
                            </div>
                            <div class="lg:w-48">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Actions</label>
                                <div class="flex space-x-2">
                                    <button onclick="generateInventoryReport()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                                        <i class="fas fa-chart-bar mr-2"></i>Generate
                                    </button>
                                    <button onclick="refreshInventoryData()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                                        <i class="fas fa-sync-alt mr-2"></i>Refresh
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory Analytics Charts -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        <!-- Stock Levels -->
                        <div class="bg-white border border-gray-200 rounded-lg">
                            <div class="p-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-chart-bar text-blue-600 mr-2"></i>
                                    Stock Levels by Category
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Food & Beverages</span>
                                        <div class="flex items-center">
                                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                                <div class="bg-green-500 h-2 rounded-full" style="width: 85%"></div>
                                            </div>
                                            <span class="font-semibold text-gray-900">1,247 items</span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Spa Products</span>
                                        <div class="flex items-center">
                                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                                <div class="bg-blue-500 h-2 rounded-full" style="width: 72%"></div>
                                            </div>
                                            <span class="font-semibold text-gray-900">456 items</span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Cleaning Supplies</span>
                                        <div class="flex items-center">
                                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                                <div class="bg-orange-500 h-2 rounded-full" style="width: 68%"></div>
                                            </div>
                                            <span class="font-semibold text-gray-900">389 items</span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Linens & Towels</span>
                                        <div class="flex items-center">
                                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                                <div class="bg-purple-500 h-2 rounded-full" style="width: 91%"></div>
                                            </div>
                                            <span class="font-semibold text-gray-900">523 items</span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Office Supplies</span>
                                        <div class="flex items-center">
                                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                                <div class="bg-pink-500 h-2 rounded-full" style="width: 45%"></div>
                                            </div>
                                            <span class="font-semibold text-gray-900">232 items</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Movement Trends -->
                        <div class="bg-white border border-gray-200 rounded-lg">
                            <div class="p-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-chart-line text-green-600 mr-2"></i>
                                    Movement Trends (Last 30 Days)
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Items Added</span>
                                        <span class="font-semibold text-green-600">+156</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Items Removed</span>
                                        <span class="font-semibold text-red-600">-89</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Net Movement</span>
                                        <span class="font-semibold text-blue-600">+67</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Reorder Requests</span>
                                        <span class="font-semibold text-orange-600">23</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Stock Adjustments</span>
                                        <span class="font-semibold text-purple-600">45</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory Summary Table -->
                    <div class="bg-white border border-gray-200 rounded-lg">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-table text-purple-600 mr-2"></i>
                                Inventory Summary by Category
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Items</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">In Stock</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Low Stock</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Out of Stock</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Food & Beverages</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">1,247</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">1,089</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">45</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">8</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱125,750</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Spa Products</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">456</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">398</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">23</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">5</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱89,450</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Cleaning Supplies</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">389</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">312</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">12</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">7</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱45,680</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Linens & Towels</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">523</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">498</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">6</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱67,890</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Office Supplies</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">232</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">187</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">3</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">1</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱23,450</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="mt-6 flex flex-wrap gap-4">
                        <button onclick="exportInventoryReport()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-download mr-2"></i>Export Report
                        </button>
                        <button onclick="generateReorderReport()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-shopping-cart mr-2"></i>Reorder Report
                        </button>
                        <button onclick="viewStockMovement()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-chart-line mr-2"></i>Stock Movement
                        </button>
                        <button onclick="scheduleInventoryAudit()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-clipboard-check mr-2"></i>Schedule Audit
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

            // Initialize inventory reports functionality
            initializeInventoryReports();
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });

        // Inventory Reports Management Functions
        function initializeInventoryReports() {
            loadInventoryData();
            initializeInventoryFilters();
        }

        function initializeInventoryFilters() {
            const category = document.getElementById('inventory-category');
            const status = document.getElementById('inventory-status');
            const location = document.getElementById('inventory-location');

            if (category) category.addEventListener('change', applyInventoryFilters);
            if (status) status.addEventListener('change', applyInventoryFilters);
            if (location) location.addEventListener('change', applyInventoryFilters);
        }

        function loadInventoryData() {
            // Simulate loading inventory data
            const inventoryData = {
                totalItems: generateRandomTotalItems(),
                inStock: generateRandomInStock(),
                lowStock: generateRandomLowStock(),
                outOfStock: generateRandomOutOfStock()
            };

            updateInventoryDisplay(inventoryData);
        }

        function generateRandomTotalItems() {
            return Math.floor(Math.random() * 500) + 2500;
        }

        function generateRandomInStock() {
            return Math.floor(Math.random() * 400) + 2000;
        }

        function generateRandomLowStock() {
            return Math.floor(Math.random() * 50) + 70;
        }

        function generateRandomOutOfStock() {
            return Math.floor(Math.random() * 20) + 15;
        }

        function updateInventoryDisplay(data) {
            const totalItems = document.getElementById('total-items');
            const inStock = document.getElementById('in-stock');
            const lowStock = document.getElementById('low-stock');
            const outOfStock = document.getElementById('out-of-stock');

            if (totalItems) {
                totalItems.textContent = data.totalItems.toLocaleString();
            }
            if (inStock) {
                inStock.textContent = data.inStock.toLocaleString();
            }
            if (lowStock) {
                lowStock.textContent = data.lowStock.toLocaleString();
            }
            if (outOfStock) {
                outOfStock.textContent = data.outOfStock.toLocaleString();
            }
        }

        function applyInventoryFilters() {
            const category = document.getElementById('inventory-category')?.value || '';
            const status = document.getElementById('inventory-status')?.value || '';
            const location = document.getElementById('inventory-location')?.value || '';

            console.log('Applying inventory filters:', { category, status, location });
            
            showNotification('Applying inventory filters...', 'info');
            
            setTimeout(() => {
                loadInventoryData();
                showNotification('Inventory data updated successfully!', 'success');
            }, 1000);
        }

        // Inventory Report Operations
        function generateInventoryReport() {
            const category = document.getElementById('inventory-category')?.value || '';
            const status = document.getElementById('inventory-status')?.value || '';
            const location = document.getElementById('inventory-location')?.value || '';

            showNotification('Generating inventory report...', 'info');
            setTimeout(() => {
                showNotification('Inventory report generated successfully!', 'success');
            }, 2000);
        }

        function exportInventoryReport() {
            showNotification('Exporting inventory report...', 'info');
            setTimeout(() => {
                showNotification('Inventory report exported successfully!', 'success');
            }, 1500);
        }

        function generateReorderReport() {
            showNotification('Generating reorder report...', 'info');
            setTimeout(() => {
                showNotification('Reorder report generated successfully!', 'success');
            }, 1500);
        }

        function viewStockMovement() {
            showNotification('Opening stock movement report...', 'info');
            setTimeout(() => {
                showNotification('Stock movement report loaded!', 'success');
            }, 1500);
        }

        function scheduleInventoryAudit() {
            showNotification('Scheduling inventory audit...', 'info');
            setTimeout(() => {
                showNotification('Inventory audit scheduled successfully!', 'success');
            }, 1500);
        }

        function refreshInventoryData() {
            showNotification('Refreshing inventory data...', 'info');
            setTimeout(() => {
                loadInventoryData();
                showNotification('Inventory data refreshed successfully!', 'success');
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