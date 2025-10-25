<?php
// VPS Session Fix - Robust session configuration
require_once __DIR__ . '/../../vps_session_fix.php';

// Check if user is logged in to POS
if (!isset($_SESSION['pos_user_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Quick Items';
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
                    <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Quick Sale Items Management</h2>
                    <p class="text-gray-600 mt-1">Comprehensive inventory and product management for quick sales</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <div id="current-date" class="text-sm text-gray-600"></div>
                        <div id="current-time" class="text-sm text-gray-600"></div>
                    </div>
                    <button onclick="exportItems()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-download mr-2"></i>Export
                    </button>
                    <button onclick="openAddItemModal()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>Add Item
                    </button>
                </div>
                </div>

            <!-- Item Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <i class="fas fa-box text-blue-600 text-xl"></i>
                            </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="total-items">156</h3>
                            <p class="text-sm text-gray-600">Total Items</p>
                            <p class="text-xs text-blue-600 mt-1">12 categories</p>
                        </div>
                        </div>
                    </div>
                    
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-lg">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                            </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="active-items">142</h3>
                            <p class="text-sm text-gray-600">Active Items</p>
                            <p class="text-xs text-green-600 mt-1">91% availability</p>
                        </div>
                        </div>
                    </div>
                    
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-lg">
                            <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                            </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="low-stock">8</h3>
                            <p class="text-sm text-gray-600">Low Stock</p>
                            <p class="text-xs text-yellow-600 mt-1">Needs restocking</p>
                        </div>
                        </div>
                    </div>
                    
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-lg">
                            <i class="fas fa-dollar-sign text-purple-600 text-xl"></i>
                            </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="total-value">₱24,850</h3>
                            <p class="text-sm text-gray-600">Total Inventory Value</p>
                            <p class="text-xs text-purple-600 mt-1">+12% vs last month</p>
                        </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800">Quick Items Overview</h3>
                        <button class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors">
                            <i class="fas fa-plus mr-2"></i>Add New
                        </button>
                    </div>
                    <div class="p-6">
                        <div class="text-center text-gray-500 py-12">
                            <i class="fas fa-cog text-4xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Quick Sale Items Management</h3>
                            <p class="text-gray-600">Comprehensive inventory and product management for quick sales operations.</p>
                            
                            <!-- Item Categories -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
                                <!-- Food & Beverages -->
                                <div class="bg-white border border-gray-200 rounded-lg p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-utensils text-orange-600"></i>
                                            </div>
                                            <h3 class="text-lg font-semibold text-gray-800">Food & Beverages</h3>
                                        </div>
                                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">45 Items</span>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600">Sandwiches</span>
                                            <span class="text-sm font-semibold text-gray-900">₱120 - ₱250</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600">Coffee & Tea</span>
                                            <span class="text-sm font-semibold text-gray-900">₱85 - ₱180</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600">Soft Drinks</span>
                                            <span class="text-sm font-semibold text-gray-900">₱45 - ₱85</span>
                                        </div>
                                    </div>
                                    <button onclick="viewCategoryItems('food')" class="w-full mt-4 bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                                        View All Items
                                    </button>
                                </div>

                                <!-- Snacks -->
                                <div class="bg-white border border-gray-200 rounded-lg p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-cookie-bite text-yellow-600"></i>
                                            </div>
                                            <h3 class="text-lg font-semibold text-gray-800">Snacks</h3>
                                        </div>
                                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">38 Items</span>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600">Chips & Crackers</span>
                                            <span class="text-sm font-semibold text-gray-900">₱25 - ₱85</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600">Chocolate Bars</span>
                                            <span class="text-sm font-semibold text-gray-900">₱35 - ₱120</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600">Nuts & Seeds</span>
                                            <span class="text-sm font-semibold text-gray-900">₱65 - ₱150</span>
                                        </div>
                                    </div>
                                    <button onclick="viewCategoryItems('snacks')" class="w-full mt-4 bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg transition-colors">
                                        View All Items
                                    </button>
                                </div>

                                <!-- Souvenirs -->
                                <div class="bg-white border border-gray-200 rounded-lg p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-gift text-purple-600"></i>
                                            </div>
                                            <h3 class="text-lg font-semibold text-gray-800">Souvenirs</h3>
                                        </div>
                                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">28 Items</span>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600">Keychains</span>
                                            <span class="text-sm font-semibold text-gray-900">₱45 - ₱120</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600">T-Shirts</span>
                                            <span class="text-sm font-semibold text-gray-900">₱350 - ₱650</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600">Magnets</span>
                                            <span class="text-sm font-semibold text-gray-900">₱25 - ₱85</span>
                                        </div>
                                    </div>
                                    <button onclick="viewCategoryItems('souvenirs')" class="w-full mt-4 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                                        View All Items
                                    </button>
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="mt-6 flex flex-wrap gap-4">
                                <button onclick="createItemPackage()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-plus mr-2"></i>Create Package
                                </button>
                                <button onclick="manageItemPricing()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-tags mr-2"></i>Manage Pricing
                                </button>
                                <button onclick="viewItemAnalytics()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-chart-bar mr-2"></i>Item Analytics
                                </button>
                                <button onclick="importItems()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-upload mr-2"></i>Import Items
                                </button>
                            </div>
                        </div>
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

            // Initialize items functionality
            initializeItems();
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });

        // Quick Sale Items Management Functions
        function initializeItems() {
            loadItemsData();
            initializeItemFilters();
        }

        function initializeItemFilters() {
            const searchInput = document.getElementById('search-items');
            const categoryFilter = document.getElementById('category-filter');
            const statusFilter = document.getElementById('status-filter');

            if (searchInput) searchInput.addEventListener('input', applyItemFilters);
            if (categoryFilter) categoryFilter.addEventListener('change', applyItemFilters);
            if (statusFilter) statusFilter.addEventListener('change', applyItemFilters);
        }

        function loadItemsData() {
            // Simulate loading items data
            const itemsData = {
                totalItems: generateRandomTotalItems(),
                activeItems: generateRandomActiveItems(),
                lowStock: generateRandomLowStock(),
                totalValue: generateRandomTotalValue()
            };

            updateItemsDisplay(itemsData);
        }

        function generateRandomTotalItems() {
            return Math.floor(Math.random() * 50) + 120;
        }

        function generateRandomActiveItems() {
            return Math.floor(Math.random() * 30) + 110;
        }

        function generateRandomLowStock() {
            return Math.floor(Math.random() * 15) + 3;
        }

        function generateRandomTotalValue() {
            return Math.floor(Math.random() * 10000) + 18000;
        }

        function updateItemsDisplay(data) {
            const totalItems = document.getElementById('total-items');
            const activeItems = document.getElementById('active-items');
            const lowStock = document.getElementById('low-stock');
            const totalValue = document.getElementById('total-value');

            if (totalItems) {
                totalItems.textContent = data.totalItems;
                const itemsGrowth = totalItems.parentElement.querySelector('.text-xs');
                if (itemsGrowth) itemsGrowth.textContent = '12 categories';
            }
            if (activeItems) {
                activeItems.textContent = data.activeItems;
                const activeGrowth = activeItems.parentElement.querySelector('.text-xs');
                if (activeGrowth) activeGrowth.textContent = '91% availability';
            }
            if (lowStock) {
                lowStock.textContent = data.lowStock;
                const lowStockGrowth = lowStock.parentElement.querySelector('.text-xs');
                if (lowStockGrowth) lowStockGrowth.textContent = 'Needs restocking';
            }
            if (totalValue) {
                totalValue.textContent = `₱${data.totalValue.toLocaleString()}`;
                const valueGrowth = totalValue.parentElement.querySelector('.text-xs');
                if (valueGrowth) valueGrowth.textContent = '+12% vs last month';
            }
        }

        function applyItemFilters() {
            const searchTerm = document.getElementById('search-items')?.value.toLowerCase() || '';
            const category = document.getElementById('category-filter')?.value || '';
            const status = document.getElementById('status-filter')?.value || '';

            console.log('Applying item filters:', { searchTerm, category, status });
            
            showNotification('Applying item filters...', 'info');
            
            setTimeout(() => {
                loadItemsData();
                showNotification('Item data updated successfully!', 'success');
            }, 1000);
        }

        // Item Operations
        function openAddItemModal() {
            showNotification('Opening add item modal...', 'info');
            setTimeout(() => {
                showNotification('Add item modal loaded!', 'success');
            }, 1500);
        }

        function viewCategoryItems(category) {
            showNotification(`Opening ${category} items...`, 'info');
            setTimeout(() => {
                showNotification(`${category} items loaded!`, 'success');
            }, 1500);
        }

        function createItemPackage() {
            showNotification('Opening item package creator...', 'info');
            setTimeout(() => {
                showNotification('Item package created!', 'success');
            }, 2000);
        }

        function manageItemPricing() {
            showNotification('Opening item pricing management...', 'info');
            setTimeout(() => {
                showNotification('Item pricing updated!', 'success');
            }, 1500);
        }

        function viewItemAnalytics() {
            showNotification('Opening item analytics...', 'info');
            setTimeout(() => {
                showNotification('Item analytics loaded!', 'success');
            }, 1500);
        }

        function importItems() {
            showNotification('Opening item import...', 'info');
            setTimeout(() => {
                showNotification('Items imported successfully!', 'success');
            }, 2000);
        }

        function refreshItems() {
            showNotification('Refreshing item data...', 'info');
            setTimeout(() => {
                loadItemsData();
                showNotification('Item data refreshed successfully!', 'success');
            }, 1000);
        }

        function exportItems() {
            showNotification('Exporting item data...', 'info');
            setTimeout(() => {
                showNotification('Item data exported successfully!', 'success');
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