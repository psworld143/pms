<?php
session_start();

// Check if user is logged in to POS
if (!isset($_SESSION['pos_user_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Room Service Menu';
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
                    <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Room Service Menu Management</h2>
                    <p class="text-gray-600 mt-1">Manage food and beverage items for room service orders</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="exportMenu()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-download mr-2"></i>Export Menu
                    </button>
                    <button onclick="openAddItemModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>Add Menu Item
                    </button>
                </div>
            </div>

            <!-- Menu Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <i class="fas fa-utensils text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="total-items">156</h3>
                            <p class="text-sm text-gray-600">Total Menu Items</p>
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
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-lg">
                            <i class="fas fa-tag text-yellow-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="categories-count">12</h3>
                            <p class="text-sm text-gray-600">Categories</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-lg">
                            <i class="fas fa-star text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="popular-items">8</h3>
                            <p class="text-sm text-gray-600">Popular Today</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex flex-col lg:flex-row gap-4">
                    <div class="flex-1">
                        <label for="search-menu" class="block text-sm font-medium text-gray-700 mb-2">Search Menu Items</label>
                        <div class="relative">
                            <input type="text" id="search-menu" placeholder="Search by name, description, or category..." 
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>
                    </div>
                    <div class="lg:w-48">
                        <label for="category-filter" class="block text-sm font-medium text-gray-700 mb-2">Category Filter</label>
                        <select id="category-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Categories</option>
                            <option value="appetizers">Appetizers</option>
                            <option value="main_courses">Main Courses</option>
                            <option value="desserts">Desserts</option>
                            <option value="beverages">Beverages</option>
                            <option value="breakfast">Breakfast</option>
                            <option value="snacks">Snacks</option>
                            <option value="alcohol">Alcohol</option>
                        </select>
                    </div>
                    <div class="lg:w-48">
                        <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-2">Status Filter</label>
                        <select id="status-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="out_of_stock">Out of Stock</option>
                        </select>
                    </div>
                    <div class="lg:w-48">
                        <label for="price-filter" class="block text-sm font-medium text-gray-700 mb-2">Price Range</label>
                        <select id="price-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Prices</option>
                            <option value="0-10">$0 - $10</option>
                            <option value="10-25">$10 - $25</option>
                            <option value="25-50">$25 - $50</option>
                            <option value="50+">$50+</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Menu Categories Navigation -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Category Navigation</h3>
                <div class="flex flex-wrap gap-2">
                    <button onclick="filterByCategory('')" class="category-btn bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        All Items
                    </button>
                    <button onclick="filterByCategory('appetizers')" class="category-btn bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                        Appetizers
                    </button>
                    <button onclick="filterByCategory('main_courses')" class="category-btn bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                        Main Courses
                    </button>
                    <button onclick="filterByCategory('desserts')" class="category-btn bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                        Desserts
                    </button>
                    <button onclick="filterByCategory('beverages')" class="category-btn bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                        Beverages
                    </button>
                    <button onclick="filterByCategory('breakfast')" class="category-btn bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                        Breakfast
                    </button>
                    <button onclick="filterByCategory('snacks')" class="category-btn bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                        Snacks
                    </button>
                    <button onclick="filterByCategory('alcohol')" class="category-btn bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                        Alcohol
                    </button>
                </div>
                            </div>

            <!-- Menu Items Grid -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <h3 class="text-lg font-semibold text-gray-800">Menu Items</h3>
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center space-x-2">
                                <label class="text-sm text-gray-600">View:</label>
                                <button onclick="setViewMode('grid')" id="grid-view-btn" class="view-mode-btn bg-blue-600 text-white p-2 rounded-lg">
                                    <i class="fas fa-th"></i>
                                </button>
                                <button onclick="setViewMode('list')" id="list-view-btn" class="view-mode-btn bg-gray-200 text-gray-600 p-2 rounded-lg hover:bg-gray-300">
                                    <i class="fas fa-list"></i>
                                </button>
                            </div>
                            <select id="sort-menu" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="name">Sort by Name</option>
                                <option value="price">Sort by Price</option>
                                <option value="category">Sort by Category</option>
                                <option value="popularity">Sort by Popularity</option>
                            </select>
                            </div>
                        </div>
                    </div>
                    
                <div id="menu-items-container" class="p-6">
                    <!-- Grid View -->
                    <div id="grid-view" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <!-- Sample Menu Items -->
                        <div class="menu-item-card bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                            <div class="relative">
                                <img src="https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=300&h=200&fit=crop" 
                                     alt="Club Sandwich" class="w-full h-48 object-cover">
                                <div class="absolute top-2 right-2">
                                    <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full">Active</span>
                                </div>
                            </div>
                            <div class="p-4">
                                <h4 class="font-semibold text-gray-900 mb-1">Club Sandwich</h4>
                                <p class="text-sm text-gray-600 mb-2">Fresh turkey, bacon, lettuce, tomato</p>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-lg font-bold text-blue-600">$12.99</span>
                                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">Main Course</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Prep: 15 min</span>
                                    <div class="flex items-center space-x-2">
                                        <button onclick="editMenuItem('1')" class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="toggleItemStatus('1')" class="text-green-600 hover:text-green-800">
                                            <i class="fas fa-toggle-on"></i>
                                        </button>
                                        <button onclick="deleteMenuItem('1')" class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="menu-item-card bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                            <div class="relative">
                                <img src="https://images.unsplash.com/photo-1513104890138-7c749659a591?w=300&h=200&fit=crop" 
                                     alt="Margherita Pizza" class="w-full h-48 object-cover">
                                <div class="absolute top-2 right-2">
                                    <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full">Active</span>
                                </div>
                            </div>
                            <div class="p-4">
                                <h4 class="font-semibold text-gray-900 mb-1">Margherita Pizza</h4>
                                <p class="text-sm text-gray-600 mb-2">Fresh mozzarella, tomato sauce, basil</p>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-lg font-bold text-blue-600">$16.99</span>
                                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">Main Course</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Prep: 20 min</span>
                                    <div class="flex items-center space-x-2">
                                        <button onclick="editMenuItem('2')" class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="toggleItemStatus('2')" class="text-green-600 hover:text-green-800">
                                            <i class="fas fa-toggle-on"></i>
                                        </button>
                                        <button onclick="deleteMenuItem('2')" class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                            </div>
                        </div>
                    </div>
                    
                        <div class="menu-item-card bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                            <div class="relative">
                                <img src="https://images.unsplash.com/photo-1551024506-0bccd828d307?w=300&h=200&fit=crop" 
                                     alt="Caesar Salad" class="w-full h-48 object-cover">
                                <div class="absolute top-2 right-2">
                                    <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full">Active</span>
                                </div>
                            </div>
                            <div class="p-4">
                                <h4 class="font-semibold text-gray-900 mb-1">Caesar Salad</h4>
                                <p class="text-sm text-gray-600 mb-2">Romaine lettuce, parmesan, croutons</p>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-lg font-bold text-blue-600">$9.99</span>
                                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">Appetizer</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Prep: 10 min</span>
                                    <div class="flex items-center space-x-2">
                                        <button onclick="editMenuItem('3')" class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="toggleItemStatus('3')" class="text-green-600 hover:text-green-800">
                                            <i class="fas fa-toggle-on"></i>
                                        </button>
                                        <button onclick="deleteMenuItem('3')" class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="menu-item-card bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                            <div class="relative">
                                <img src="https://images.unsplash.com/photo-1571091718767-18b5b1457add?w=300&h=200&fit=crop" 
                                     alt="Chocolate Cake" class="w-full h-48 object-cover">
                                <div class="absolute top-2 right-2">
                                    <span class="bg-yellow-500 text-white text-xs px-2 py-1 rounded-full">Limited</span>
                    </div>
                            </div>
                            <div class="p-4">
                                <h4 class="font-semibold text-gray-900 mb-1">Chocolate Cake</h4>
                                <p class="text-sm text-gray-600 mb-2">Rich chocolate cake with ganache</p>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-lg font-bold text-blue-600">$7.99</span>
                                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">Dessert</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Prep: 5 min</span>
                                    <div class="flex items-center space-x-2">
                                        <button onclick="editMenuItem('4')" class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="toggleItemStatus('4')" class="text-green-600 hover:text-green-800">
                                            <i class="fas fa-toggle-on"></i>
                                        </button>
                                        <button onclick="deleteMenuItem('4')" class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- List View (Hidden by default) -->
                    <div id="list-view" class="hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prep Time</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <img class="h-10 w-10 rounded-lg object-cover" src="https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=100&h=100&fit=crop" alt="">
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">Club Sandwich</div>
                                                    <div class="text-sm text-gray-500">Fresh turkey, bacon, lettuce, tomato</div>
                </div>
                    </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-900">Main Course</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-medium text-blue-600">$12.99</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                Active
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            15 min
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="editMenuItem('1')" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                            <button onclick="toggleItemStatus('1')" class="text-green-600 hover:text-green-900 mr-3">Toggle</button>
                                            <button onclick="deleteMenuItem('1')" class="text-red-600 hover:text-red-900">Delete</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
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
        });

        function addNew() {
            console.log('Adding new room service entry...');
        }

        function searchRecords() {
            console.log('Searching room service records...');
        }

        function viewAnalytics() {
            console.log('Viewing room service analytics...');
        }

        // Menu Management Functions
        function openAddItemModal() {
            showNotification('Opening add item modal...', 'info');
            console.log('Opening add item modal');
        }

        function editMenuItem(itemId) {
            showNotification(`Editing menu item ${itemId}...`, 'info');
            console.log(`Editing menu item: ${itemId}`);
        }

        function toggleItemStatus(itemId) {
            const itemCard = document.querySelector(`[onclick*="${itemId}"]`)?.closest('.menu-item-card');
            const statusBadge = itemCard?.querySelector('.bg-green-500, .bg-yellow-500, .bg-red-500');
            
            if (statusBadge) {
                const currentStatus = statusBadge.textContent.toLowerCase();
                let newClass, newText;
                
                if (currentStatus.includes('active')) {
                    newClass = 'bg-red-500';
                    newText = 'Inactive';
                } else {
                    newClass = 'bg-green-500';
                    newText = 'Active';
                }
                
                statusBadge.className = `${newClass} text-white text-xs px-2 py-1 rounded-full`;
                statusBadge.textContent = newText;
                
                showNotification(`Menu item ${itemId} status updated to ${newText}`, 'success');
            }
        }

        function deleteMenuItem(itemId) {
            if (confirm(`Are you sure you want to delete menu item ${itemId}?`)) {
                const itemCard = document.querySelector(`[onclick*="${itemId}"]`)?.closest('.menu-item-card');
                if (itemCard) {
                    itemCard.style.transition = 'opacity 0.3s ease';
                    itemCard.style.opacity = '0';
                    setTimeout(() => {
                        itemCard.remove();
                        loadMenuStatistics();
                    }, 300);
                }
                showNotification(`Menu item ${itemId} deleted successfully`, 'success');
            }
        }

        function exportMenu() {
            showNotification('Exporting menu data...', 'info');
            setTimeout(() => {
                showNotification('Menu exported successfully', 'success');
            }, 1500);
        }

        function filterByCategory(category) {
            const categoryFilter = document.getElementById('category-filter');
            if (categoryFilter) {
                categoryFilter.value = category;
            }

            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('bg-gray-200', 'text-gray-700');
            });

            const selectedBtn = event?.target;
            if (selectedBtn && selectedBtn.classList.contains('category-btn')) {
                selectedBtn.classList.remove('bg-gray-200', 'text-gray-700');
                selectedBtn.classList.add('bg-blue-600', 'text-white');
            }

            applyFilters();
        }

        function setViewMode(mode) {
            const gridView = document.getElementById('grid-view');
            const listView = document.getElementById('list-view');
            const gridBtn = document.getElementById('grid-view-btn');
            const listBtn = document.getElementById('list-view-btn');

            if (mode === 'grid') {
                gridView?.classList.remove('hidden');
                listView?.classList.add('hidden');
                gridBtn?.classList.remove('bg-gray-200', 'text-gray-600');
                gridBtn?.classList.add('bg-blue-600', 'text-white');
                listBtn?.classList.remove('bg-blue-600', 'text-white');
                listBtn?.classList.add('bg-gray-200', 'text-gray-600');
            } else {
                gridView?.classList.add('hidden');
                listView?.classList.remove('hidden');
                listBtn?.classList.remove('bg-gray-200', 'text-gray-600');
                listBtn?.classList.add('bg-blue-600', 'text-white');
                gridBtn?.classList.remove('bg-blue-600', 'text-white');
                gridBtn?.classList.add('bg-gray-200', 'text-gray-600');
            }
        }

        function applyFilters() {
            const searchTerm = document.getElementById('search-menu')?.value.toLowerCase() || '';
            const categoryFilter = document.getElementById('category-filter')?.value || '';
            const statusFilter = document.getElementById('status-filter')?.value || '';
            const priceFilter = document.getElementById('price-filter')?.value || '';

            const menuItems = document.querySelectorAll('.menu-item-card');
            let visibleCount = 0;

            menuItems.forEach(item => {
                const itemName = item.querySelector('h4')?.textContent.toLowerCase() || '';
                const itemDescription = item.querySelector('p')?.textContent.toLowerCase() || '';
                const itemCategory = item.querySelector('.text-xs.text-gray-500')?.textContent.toLowerCase() || '';
                const itemPrice = parseFloat(item.querySelector('.text-lg.font-bold')?.textContent.replace('$', '') || '0');
                const itemStatus = item.querySelector('.bg-green-500, .bg-yellow-500, .bg-red-500')?.textContent.toLowerCase() || '';

                const matchesSearch = !searchTerm || 
                    itemName.includes(searchTerm) || 
                    itemDescription.includes(searchTerm) || 
                    itemCategory.includes(searchTerm);

                const matchesCategory = !categoryFilter || itemCategory.includes(categoryFilter);
                const matchesStatus = !statusFilter || itemStatus.includes(statusFilter);
                const matchesPrice = !priceFilter || checkPriceRange(itemPrice, priceFilter);

                if (matchesSearch && matchesCategory && matchesStatus && matchesPrice) {
                    item.style.display = '';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            console.log(`Showing ${visibleCount} menu items`);
        }

        function checkPriceRange(price, range) {
            switch(range) {
                case '0-10': return price >= 0 && price <= 10;
                case '10-25': return price > 10 && price <= 25;
                case '25-50': return price > 25 && price <= 50;
                case '50+': return price > 50;
                default: return true;
            }
        }

        function loadMenuStatistics() {
            const stats = {
                total: Math.floor(Math.random() * 200) + 100,
                active: Math.floor(Math.random() * 150) + 100,
                categories: Math.floor(Math.random() * 15) + 8,
                popular: Math.floor(Math.random() * 15) + 5
            };

            const totalItems = document.getElementById('total-items');
            const activeItems = document.getElementById('active-items');
            const categoriesCount = document.getElementById('categories-count');
            const popularItems = document.getElementById('popular-items');

            if (totalItems) totalItems.textContent = stats.total;
            if (activeItems) activeItems.textContent = stats.active;
            if (categoriesCount) categoriesCount.textContent = stats.categories;
            if (popularItems) popularItems.textContent = stats.popular;
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
    </script>

    <?php include '../includes/pos-footer.php'; ?>
</body>
</html>