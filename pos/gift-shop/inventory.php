<?php
// VPS Session Fix - Robust session configuration
require_once __DIR__ . '/../../vps_session_fix.php';

// Check if user is logged in to POS
if (!isset($_SESSION['pos_user_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Inventory';
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
                    <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Gift Shop Inventory Management</h2>
                    <p class="text-gray-600 mt-1">Comprehensive inventory tracking and stock management system</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <div id="current-date" class="text-sm text-gray-600"></div>
                        <div id="current-time" class="text-sm text-gray-600"></div>
                    </div>
                    <button onclick="exportInventory()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-download mr-2"></i>Export
                    </button>
                    <button onclick="openAddProductModal()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>Add Product
                    </button>
                </div>
            </div>

            <!-- Inventory Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <i class="fas fa-boxes text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="total-products">248</h3>
                            <p class="text-sm text-gray-600">Total Products</p>
                            <p class="text-xs text-blue-600 mt-1">+5 new this week</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-lg">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="in-stock">186</h3>
                            <p class="text-sm text-gray-600">In Stock</p>
                            <p class="text-xs text-green-600 mt-1">75% of total inventory</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-red-100 rounded-lg">
                            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="low-stock">12</h3>
                            <p class="text-sm text-gray-600">Low Stock</p>
                            <p class="text-xs text-red-600 mt-1">Need reorder</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-lg">
                            <i class="fas fa-dollar-sign text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="total-value">₱12,450</h3>
                            <p class="text-sm text-gray-600">Inventory Value</p>
                            <p class="text-xs text-purple-600 mt-1">+8.2% vs last month</p>
                        </div>
                    </div>
                </div>
            </div>

                <!-- Main Content Area -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800">Inventory Overview</h3>
                        <button class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors">
                            <i class="fas fa-plus mr-2"></i>Add New
                        </button>
                    </div>
                    <div class="p-6">
                        <div class="text-center text-gray-500 py-12">
                            <i class="fas fa-cog text-4xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Gift Shop Inventory Management</h3>
                            <p class="text-gray-600">Comprehensive inventory tracking and stock management system.</p>
                            
                            <!-- Inventory Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
                                <div class="bg-white border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center mb-3">
                                        <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=60&h=60&fit=crop" 
                                             alt="Hotel Logo Mug" class="w-12 h-12 rounded-lg object-cover mr-3">
                                        <div>
                                            <h4 class="font-semibold text-gray-900">Hotel Logo Mug</h4>
                                            <p class="text-sm text-gray-600">₱24.99</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">In Stock (45)</span>
                                        <div class="flex items-center space-x-2">
                                            <button onclick="editProduct('1')" class="text-orange-600 hover:text-orange-800">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="adjustStock('1')" class="text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-boxes"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-white border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center mb-3">
                                        <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=60&h=60&fit=crop" 
                                             alt="Hotel T-Shirt" class="w-12 h-12 rounded-lg object-cover mr-3">
                                        <div>
                                            <h4 class="font-semibold text-gray-900">Hotel T-Shirt</h4>
                                            <p class="text-sm text-gray-600">₱18.99</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">Low Stock (3)</span>
                                        <div class="flex items-center space-x-2">
                                            <button onclick="editProduct('2')" class="text-orange-600 hover:text-orange-800">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="adjustStock('2')" class="text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-boxes"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-white border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center mb-3">
                                        <img src="https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=60&h=60&fit=crop" 
                                             alt="Local Honey" class="w-12 h-12 rounded-lg object-cover mr-3">
                                        <div>
                                            <h4 class="font-semibold text-gray-900">Local Honey</h4>
                                            <p class="text-sm text-gray-600">₱12.99</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">In Stock (28)</span>
                                        <div class="flex items-center space-x-2">
                                            <button onclick="editProduct('3')" class="text-orange-600 hover:text-orange-800">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="adjustStock('3')" class="text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-boxes"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="mt-6 flex flex-wrap gap-4">
                                <button onclick="generateStockReport()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-chart-bar mr-2"></i>Stock Report
                                </button>
                                <button onclick="generateReorderList()" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>Reorder List
                                </button>
                                <button onclick="bulkUpdatePrices()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-tags mr-2"></i>Bulk Price Update
                                </button>
                                <button onclick="inventoryAudit()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-clipboard-check mr-2"></i>Inventory Audit
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
        </main>
    </div>

    <script>
        // Gift shop module functionality
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

            // Initialize inventory functionality
            initializeInventory();
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });

        // Inventory Management Functions
        function initializeInventory() {
            loadInventoryData();
            initializeInventoryFilters();
        }

        function initializeInventoryFilters() {
            const searchInput = document.getElementById('search-products');
            const categoryFilter = document.getElementById('category-filter');
            const stockFilter = document.getElementById('stock-filter');

            if (searchInput) searchInput.addEventListener('input', applyInventoryFilters);
            if (categoryFilter) categoryFilter.addEventListener('change', applyInventoryFilters);
            if (stockFilter) stockFilter.addEventListener('change', applyInventoryFilters);
        }

        function loadInventoryData() {
            // Simulate loading inventory data
            const inventoryData = {
                totalProducts: generateRandomTotalProducts(),
                inStock: generateRandomInStock(),
                lowStock: generateRandomLowStock(),
                totalValue: generateRandomTotalValue()
            };

            updateInventoryDisplay(inventoryData);
        }

        function generateRandomTotalProducts() {
            return Math.floor(Math.random() * 50) + 200;
        }

        function generateRandomInStock() {
            return Math.floor(Math.random() * 50) + 150;
        }

        function generateRandomLowStock() {
            return Math.floor(Math.random() * 20) + 5;
        }

        function generateRandomTotalValue() {
            return Math.floor(Math.random() * 5000) + 10000;
        }

        function updateInventoryDisplay(data) {
            const totalProducts = document.getElementById('total-products');
            const inStock = document.getElementById('in-stock');
            const lowStock = document.getElementById('low-stock');
            const totalValue = document.getElementById('total-value');

            if (totalProducts) {
                totalProducts.textContent = data.totalProducts;
                const newProducts = totalProducts.parentElement.querySelector('.text-xs');
                if (newProducts) newProducts.textContent = `+${Math.floor(Math.random() * 10)} new this week`;
            }
            if (inStock) {
                inStock.textContent = data.inStock;
                const stockPercentage = totalProducts ? Math.round((data.inStock / data.totalProducts) * 100) : 75;
                const stockInfo = inStock.parentElement.querySelector('.text-xs');
                if (stockInfo) stockInfo.textContent = `${stockPercentage}% of total inventory`;
            }
            if (lowStock) {
                lowStock.textContent = data.lowStock;
            }
            if (totalValue) {
                totalValue.textContent = `₱${data.totalValue.toLocaleString()}`;
                const valueGrowth = totalValue.parentElement.querySelector('.text-xs');
                if (valueGrowth) valueGrowth.textContent = `+${(Math.random() * 15 + 5).toFixed(1)}% vs last month`;
            }
        }

        function applyInventoryFilters() {
            const searchTerm = document.getElementById('search-products')?.value.toLowerCase() || '';
            const categoryFilter = document.getElementById('category-filter')?.value || '';
            const stockFilter = document.getElementById('stock-filter')?.value || '';

            console.log('Applying inventory filters:', { searchTerm, categoryFilter, stockFilter });
            
            // Filter product cards
            const productCards = document.querySelectorAll('.product-card, .bg-white.border');
            let visibleCount = 0;

            productCards.forEach(card => {
                const productName = card.querySelector('h4')?.textContent.toLowerCase() || '';
                const productPrice = card.querySelector('p')?.textContent.toLowerCase() || '';
                const stockStatus = card.querySelector('.bg-green-100, .bg-yellow-100, .bg-red-100')?.textContent.toLowerCase() || '';

                const matchesSearch = !searchTerm || 
                    productName.includes(searchTerm) || 
                    productPrice.includes(searchTerm);

                const matchesStock = !stockFilter || 
                    (stockFilter === 'in_stock' && stockStatus.includes('in stock')) ||
                    (stockFilter === 'low_stock' && stockStatus.includes('low stock')) ||
                    (stockFilter === 'out_of_stock' && stockStatus.includes('out of stock'));

                if (matchesSearch && matchesStock) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            console.log(`Showing ${visibleCount} products`);
        }

        // Product Management Functions
        function openAddProductModal() {
            showNotification('Opening add product modal...', 'info');
            console.log('Opening add product modal');
        }

        function editProduct(productId) {
            showNotification(`Editing product ${productId}...`, 'info');
            console.log(`Editing product: ${productId}`);
        }

        function adjustStock(productId) {
            showNotification(`Adjusting stock for product ${productId}...`, 'info');
            console.log(`Adjusting stock for product: ${productId}`);
        }

        function viewProductHistory(productId) {
            showNotification(`Viewing product history ${productId}...`, 'info');
            console.log(`Viewing product history: ${productId}`);
        }

        // Inventory Reports and Actions
        function generateStockReport() {
            showNotification('Generating comprehensive stock report...', 'info');
            setTimeout(() => {
                showNotification('Stock report generated successfully!', 'success');
            }, 2000);
        }

        function generateReorderList() {
            showNotification('Generating reorder list...', 'info');
            setTimeout(() => {
                showNotification('Reorder list generated successfully!', 'success');
            }, 1500);
        }

        function bulkUpdatePrices() {
            showNotification('Opening bulk price update...', 'info');
            setTimeout(() => {
                showNotification('Bulk price update completed!', 'success');
            }, 2000);
        }

        function inventoryAudit() {
            showNotification('Starting inventory audit...', 'info');
            setTimeout(() => {
                showNotification('Inventory audit completed!', 'success');
            }, 2500);
        }

        function refreshInventory() {
            showNotification('Refreshing inventory data...', 'info');
            setTimeout(() => {
                loadInventoryData();
                showNotification('Inventory data refreshed successfully!', 'success');
            }, 1000);
        }

        function exportInventory() {
            showNotification('Exporting inventory data...', 'info');
            setTimeout(() => {
                showNotification('Inventory data exported successfully!', 'success');
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

        function addNew() {
            console.log('Adding new gift shop entry...');
        }

        function searchRecords() {
            console.log('Searching gift shop records...');
        }

        function viewAnalytics() {
            console.log('Viewing gift shop analytics...');
        }
    </script>

    <?php include '../includes/pos-footer.php'; ?>
</body>
</html>