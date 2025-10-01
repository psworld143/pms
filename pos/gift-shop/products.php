<?php
session_start();

// Check if user is logged in to POS
if (!isset($_SESSION['pos_user_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Products';
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
                    <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Gift Shop Product Catalog</h2>
                    <p class="text-gray-600 mt-1">Comprehensive product catalog management and pricing system</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <div id="current-date" class="text-sm text-gray-600"></div>
                        <div id="current-time" class="text-sm text-gray-600"></div>
                    </div>
                    <button onclick="exportProducts()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-download mr-2"></i>Export
                    </button>
                    <button onclick="openAddProductModal()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>Add Product
                    </button>
                </div>
                </div>

            <!-- Product Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <i class="fas fa-boxes text-blue-600 text-xl"></i>
                            </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="total-products">248</h3>
                            <p class="text-sm text-gray-600">Total Products</p>
                            <p class="text-xs text-blue-600 mt-1">+12 new this month</p>
                        </div>
                        </div>
                    </div>
                    
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-lg">
                            <i class="fas fa-tags text-green-600 text-xl"></i>
                            </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="active-products">186</h3>
                            <p class="text-sm text-gray-600">Active Products</p>
                            <p class="text-xs text-green-600 mt-1">75% of catalog</p>
                        </div>
                        </div>
                    </div>
                    
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-lg">
                            <i class="fas fa-layer-group text-purple-600 text-xl"></i>
                            </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="total-categories">8</h3>
                            <p class="text-sm text-gray-600">Categories</p>
                            <p class="text-xs text-purple-600 mt-1">Well organized</p>
                        </div>
                        </div>
                    </div>
                    
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-lg">
                            <i class="fas fa-dollar-sign text-yellow-600 text-xl"></i>
                            </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="avg-price">$28.50</h3>
                            <p class="text-sm text-gray-600">Avg Price</p>
                            <p class="text-xs text-yellow-600 mt-1">$5 - $150 range</p>
                        </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800">Products Overview</h3>
                        <button class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors">
                            <i class="fas fa-plus mr-2"></i>Add New
                        </button>
                    </div>
                    <div class="p-6">
                        <div class="text-center text-gray-500 py-12">
                            <i class="fas fa-cog text-4xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Gift Shop Product Catalog</h3>
                            <p class="text-gray-600">Comprehensive product catalog management and pricing system.</p>
                            
                            <!-- Product Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mt-6">
                                <!-- Sample Products -->
                                <div class="product-card bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                                    <div class="relative">
                                        <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=300&h=200&fit=crop" 
                                             alt="Hotel Logo Mug" class="w-full h-48 object-cover">
                                        <div class="absolute top-2 right-2">
                                            <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full">Active</span>
                                        </div>
                                        <div class="absolute bottom-2 left-2">
                                            <div class="bg-black bg-opacity-50 text-white text-sm px-2 py-1 rounded">
                                                $24.99
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-4">
                                        <h4 class="font-semibold text-gray-900 mb-1">Hotel Logo Mug</h4>
                                        <p class="text-sm text-gray-600 mb-2">Ceramic coffee mug with hotel branding</p>
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm text-gray-500">SKU: MUG-001</span>
                                            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">Souvenirs</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-500">Stock: 45 units</span>
                                            <div class="flex items-center space-x-2">
                                                <button onclick="editProduct('1')" class="text-orange-600 hover:text-orange-800">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button onclick="viewProductDetails('1')" class="text-blue-600 hover:text-blue-800">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="product-card bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                                    <div class="relative">
                                        <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=300&h=200&fit=crop" 
                                             alt="Hotel T-Shirt" class="w-full h-48 object-cover">
                                        <div class="absolute top-2 right-2">
                                            <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full">Active</span>
                                        </div>
                                        <div class="absolute bottom-2 left-2">
                                            <div class="bg-black bg-opacity-50 text-white text-sm px-2 py-1 rounded">
                                                $18.99
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-4">
                                        <h4 class="font-semibold text-gray-900 mb-1">Hotel T-Shirt</h4>
                                        <p class="text-sm text-gray-600 mb-2">Cotton t-shirt with hotel branding</p>
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm text-gray-500">SKU: TSH-002</span>
                                            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">Apparel</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-500">Stock: 23 units</span>
                                            <div class="flex items-center space-x-2">
                                                <button onclick="editProduct('2')" class="text-orange-600 hover:text-orange-800">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button onclick="viewProductDetails('2')" class="text-blue-600 hover:text-blue-800">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="product-card bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                                    <div class="relative">
                                        <img src="https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=300&h=200&fit=crop" 
                                             alt="Local Honey" class="w-full h-48 object-cover">
                                        <div class="absolute top-2 right-2">
                                            <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full">Active</span>
                                        </div>
                                        <div class="absolute bottom-2 left-2">
                                            <div class="bg-black bg-opacity-50 text-white text-sm px-2 py-1 rounded">
                                                $12.99
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-4">
                                        <h4 class="font-semibold text-gray-900 mb-1">Local Honey</h4>
                                        <p class="text-sm text-gray-600 mb-2">Pure local honey 250g jar</p>
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm text-gray-500">SKU: HON-003</span>
                                            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">Food</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-500">Stock: 18 units</span>
                                            <div class="flex items-center space-x-2">
                                                <button onclick="editProduct('3')" class="text-orange-600 hover:text-orange-800">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button onclick="viewProductDetails('3')" class="text-blue-600 hover:text-blue-800">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="product-card bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                                    <div class="relative">
                                        <img src="https://images.unsplash.com/photo-1602173574767-37ac01994b2a?w=300&h=200&fit=crop" 
                                             alt="Hotel Keychain" class="w-full h-48 object-cover">
                                        <div class="absolute top-2 right-2">
                                            <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full">Inactive</span>
                                        </div>
                                        <div class="absolute bottom-2 left-2">
                                            <div class="bg-black bg-opacity-50 text-white text-sm px-2 py-1 rounded">
                                                $8.99
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-4">
                                        <h4 class="font-semibold text-gray-900 mb-1">Hotel Keychain</h4>
                                        <p class="text-sm text-gray-600 mb-2">Metal keychain with hotel logo</p>
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm text-gray-500">SKU: KEY-004</span>
                                            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">Accessories</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-500">Stock: 0 units</span>
                                            <div class="flex items-center space-x-2">
                                                <button onclick="editProduct('4')" class="text-orange-600 hover:text-orange-800">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button onclick="viewProductDetails('4')" class="text-blue-600 hover:text-blue-800">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="mt-6 flex flex-wrap gap-4">
                                <button onclick="bulkUpdatePrices()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-tags mr-2"></i>Bulk Price Update
                                </button>
                                <button onclick="manageCategories()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-layer-group mr-2"></i>Manage Categories
                                </button>
                                <button onclick="importProducts()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-upload mr-2"></i>Import Products
                                </button>
                                <button onclick="generateProductReport()" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-chart-bar mr-2"></i>Product Report
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

            // Initialize product functionality
            initializeProducts();
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });

        // Product Management Functions
        function initializeProducts() {
            loadProductData();
            initializeProductFilters();
        }

        function initializeProductFilters() {
            const searchInput = document.getElementById('search-products');
            const categoryFilter = document.getElementById('category-filter');
            const statusFilter = document.getElementById('status-filter');

            if (searchInput) searchInput.addEventListener('input', applyProductFilters);
            if (categoryFilter) categoryFilter.addEventListener('change', applyProductFilters);
            if (statusFilter) statusFilter.addEventListener('change', applyProductFilters);
        }

        function loadProductData() {
            // Simulate loading product data
            const productData = {
                totalProducts: generateRandomTotalProducts(),
                activeProducts: generateRandomActiveProducts(),
                totalCategories: generateRandomTotalCategories(),
                avgPrice: generateRandomAvgPrice()
            };

            updateProductDisplay(productData);
        }

        function generateRandomTotalProducts() {
            return Math.floor(Math.random() * 50) + 200;
        }

        function generateRandomActiveProducts() {
            return Math.floor(Math.random() * 40) + 150;
        }

        function generateRandomTotalCategories() {
            return Math.floor(Math.random() * 3) + 6;
        }

        function generateRandomAvgPrice() {
            return (Math.random() * 20 + 20).toFixed(2);
        }

        function updateProductDisplay(data) {
            const totalProducts = document.getElementById('total-products');
            const activeProducts = document.getElementById('active-products');
            const totalCategories = document.getElementById('total-categories');
            const avgPrice = document.getElementById('avg-price');

            if (totalProducts) {
                totalProducts.textContent = data.totalProducts;
                const productGrowth = totalProducts.parentElement.querySelector('.text-xs');
                if (productGrowth) productGrowth.textContent = `+${(Math.random() * 20 + 5).toFixed(0)} new this month`;
            }
            if (activeProducts) {
                activeProducts.textContent = data.activeProducts;
                const activeGrowth = activeProducts.parentElement.querySelector('.text-xs');
                if (activeGrowth) activeGrowth.textContent = `${Math.round((data.activeProducts/data.totalProducts)*100)}% of catalog`;
            }
            if (totalCategories) {
                totalCategories.textContent = data.totalCategories;
                const categoryGrowth = totalCategories.parentElement.querySelector('.text-xs');
                if (categoryGrowth) categoryGrowth.textContent = 'Well organized';
            }
            if (avgPrice) {
                avgPrice.textContent = `$${data.avgPrice}`;
                const priceGrowth = avgPrice.parentElement.querySelector('.text-xs');
                if (priceGrowth) priceGrowth.textContent = '$5 - $150 range';
            }
        }

        function applyProductFilters() {
            const searchTerm = document.getElementById('search-products')?.value.toLowerCase() || '';
            const category = document.getElementById('category-filter')?.value || '';
            const status = document.getElementById('status-filter')?.value || '';

            console.log('Applying product filters:', { searchTerm, category, status });
            
            showNotification('Applying product filters...', 'info');
            
            setTimeout(() => {
                loadProductData();
                showNotification('Product data updated successfully!', 'success');
            }, 1000);
        }

        // Product Operations
        function openAddProductModal() {
            showNotification('Opening add product modal...', 'info');
            console.log('Opening add product modal');
        }

        function editProduct(productId) {
            showNotification(`Editing product ${productId}...`, 'info');
            console.log(`Editing product: ${productId}`);
        }

        function viewProductDetails(productId) {
            showNotification(`Viewing product details ${productId}...`, 'info');
            console.log(`Viewing product details: ${productId}`);
        }

        function duplicateProduct(productId) {
            showNotification(`Duplicating product ${productId}...`, 'info');
            setTimeout(() => {
                showNotification('Product duplicated successfully!', 'success');
            }, 1500);
        }

        function bulkUpdatePrices() {
            showNotification('Opening bulk price update...', 'info');
            setTimeout(() => {
                showNotification('Bulk price update completed!', 'success');
            }, 2000);
        }

        function manageCategories() {
            showNotification('Opening category management...', 'info');
            setTimeout(() => {
                showNotification('Categories updated successfully!', 'success');
            }, 1500);
        }

        function importProducts() {
            showNotification('Opening product import...', 'info');
            setTimeout(() => {
                showNotification('Products imported successfully!', 'success');
            }, 2000);
        }

        function generateProductReport() {
            showNotification('Generating product report...', 'info');
            setTimeout(() => {
                showNotification('Product report generated successfully!', 'success');
            }, 2000);
        }

        function refreshProducts() {
            showNotification('Refreshing product data...', 'info');
            setTimeout(() => {
                loadProductData();
                showNotification('Product data refreshed successfully!', 'success');
            }, 1000);
        }

        function exportProducts() {
            showNotification('Exporting product data...', 'info');
            setTimeout(() => {
                showNotification('Product data exported successfully!', 'success');
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