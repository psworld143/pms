<?php
session_start();

// Check if user is logged in to POS
if (!isset($_SESSION['pos_user_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Restaurant Menu Management';
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
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Restaurant Menu Management</h2>
                <div class="text-right">
                    <div id="current-date" class="text-sm text-gray-600"></div>
                    <div id="current-time" class="text-sm text-gray-600"></div>
                </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-primary">
                        <div class="flex items-center">
                            <div class="p-2 bg-primary bg-opacity-10 rounded-lg">
                                <i class="fas fa-plus text-primary text-lg"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-gray-900">Add New Item</h3>
                                <p class="text-xs text-gray-500">Create menu item</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-success">
                        <div class="flex items-center">
                            <div class="p-2 bg-success bg-opacity-10 rounded-lg">
                                <i class="fas fa-tags text-success text-lg"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-gray-900">Categories</h3>
                                <p class="text-xs text-gray-500">Manage categories</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-warning">
                        <div class="flex items-center">
                            <div class="p-2 bg-warning bg-opacity-10 rounded-lg">
                                <i class="fas fa-chart-line text-warning text-lg"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-gray-900">Popular Items</h3>
                                <p class="text-xs text-gray-500">View top sellers</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-info">
                        <div class="flex items-center">
                            <div class="p-2 bg-info bg-opacity-10 rounded-lg">
                                <i class="fas fa-print text-info text-lg"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-gray-900">Print Menu</h3>
                                <p class="text-xs text-gray-500">Generate menu PDF</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Menu Management Controls -->
                <div class="bg-white rounded-lg shadow mb-6">
                            <div class="p-4 border-b border-gray-200">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Menu Management</h3>
                                <p class="text-sm text-gray-600">Organize and manage your restaurant menu items</p>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <button onclick="openAddItemModal()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors flex items-center">
                                    <i class="fas fa-plus mr-2"></i>Add New Item
                                    </button>
                                <button onclick="exportMenu()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center">
                                    <i class="fas fa-download mr-2"></i>Export Menu
                                    </button>
                            </div>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Search and Filter -->
                            <div class="md:col-span-2">
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <div class="flex-1">
                                        <input type="text" id="search-items" placeholder="Search menu items..." 
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                    </div>
                                    <select id="category-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                        <option value="">All Categories</option>
                                        <option value="appetizers">Appetizers</option>
                                        <option value="main-courses">Main Courses</option>
                                        <option value="desserts">Desserts</option>
                                        <option value="beverages">Beverages</option>
                                        <option value="salads">Salads</option>
                                        <option value="soups">Soups</option>
                                        <option value="sides">Sides</option>
                                    </select>
                                    <select id="status-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                        <option value="">All Items</option>
                                        <option value="active">Active Only</option>
                                        <option value="inactive">Inactive Only</option>
                                    </select>
                                </div>
                            </div>
                            <!-- Quick Stats -->
                            <div class="flex justify-between items-center bg-gray-50 rounded-lg p-3">
                                <div class="text-center">
                                    <div class="text-lg font-semibold text-gray-800" id="total-items">0</div>
                                    <div class="text-xs text-gray-600">Total Items</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-lg font-semibold text-green-600" id="active-items">0</div>
                                    <div class="text-xs text-gray-600">Active</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-lg font-semibold text-gray-600" id="inactive-items">0</div>
                                    <div class="text-xs text-gray-600">Inactive</div>
                                </div>
                                        </div>
                                            </div>
                                        </div>
                                    </div>

                <!-- Menu Categories Navigation -->
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">Categories</h3>
                                        </div>
                    <div class="p-4">
                        <div class="flex flex-wrap gap-2" id="category-buttons">
                            <button onclick="filterByCategory('')" class="category-btn active px-4 py-2 rounded-lg bg-primary text-white font-medium">
                                <i class="fas fa-utensils mr-2"></i>All Items
                            </button>
                            <button onclick="filterByCategory('appetizers')" class="category-btn px-4 py-2 rounded-lg text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors">
                                <i class="fas fa-apple-alt mr-2"></i>Appetizers
                            </button>
                            <button onclick="filterByCategory('main-courses')" class="category-btn px-4 py-2 rounded-lg text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors">
                                <i class="fas fa-drumstick-bite mr-2"></i>Main Courses
                            </button>
                            <button onclick="filterByCategory('desserts')" class="category-btn px-4 py-2 rounded-lg text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors">
                                <i class="fas fa-ice-cream mr-2"></i>Desserts
                            </button>
                            <button onclick="filterByCategory('beverages')" class="category-btn px-4 py-2 rounded-lg text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors">
                                <i class="fas fa-coffee mr-2"></i>Beverages
                            </button>
                            <button onclick="filterByCategory('salads')" class="category-btn px-4 py-2 rounded-lg text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors">
                                <i class="fas fa-leaf mr-2"></i>Salads
                            </button>
                            <button onclick="filterByCategory('soups')" class="category-btn px-4 py-2 rounded-lg text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors">
                                <i class="fas fa-bowl-food mr-2"></i>Soups
                                                </button>
                            <button onclick="filterByCategory('sides')" class="category-btn px-4 py-2 rounded-lg text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors">
                                <i class="fas fa-plate-wheat mr-2"></i>Sides
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                <!-- Menu Items Display -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Menu Items</h3>
                            <p class="text-sm text-gray-600" id="items-count">Showing all items</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-2">
                                <label class="text-sm text-gray-600">Sort by:</label>
                                <select id="sort-items" class="px-3 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                                    <option value="name">Name (A-Z)</option>
                                    <option value="name-desc">Name (Z-A)</option>
                                    <option value="price">Price (Low to High)</option>
                                    <option value="price-desc">Price (High to Low)</option>
                                    <option value="category">Category</option>
                                    <option value="created">Date Added</option>
                                </select>
                                        </div>
                            <div class="flex items-center gap-2">
                                <label class="text-sm text-gray-600">View:</label>
                                <button onclick="setViewMode('grid')" id="grid-view" class="p-2 rounded-md bg-primary text-white">
                                    <i class="fas fa-th"></i>
                                                </button>
                                <button onclick="setViewMode('list')" id="list-view" class="p-2 rounded-md bg-gray-200 text-gray-600">
                                    <i class="fas fa-list"></i>
                                                </button>
                                            </div>
                                        </div>
                    </div>
                    <div class="p-4">
                        <!-- Loading State -->
                        <div id="loading-state" class="text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                            <p class="mt-2 text-gray-600">Loading menu items...</p>
                                    </div>

                        <!-- Empty State -->
                        <div id="empty-state" class="text-center py-12 hidden">
                            <i class="fas fa-utensils text-gray-400 text-6xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No menu items found</h3>
                            <p class="text-gray-600 mb-4">Get started by adding your first menu item</p>
                            <button onclick="openAddItemModal()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors">
                                <i class="fas fa-plus mr-2"></i>Add Menu Item
                                                </button>
                                            </div>
                        
                        <!-- Menu Items Grid -->
                        <div id="menu-items-container" class="hidden">
                            <div id="menu-items-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <!-- Dynamic content will be inserted here -->
                            </div>
                        </div>
                    </div>
                </div>
        </main>

    <!-- Add Item Modal -->
    <div id="add-item-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">Add New Menu Item</h3>
                    <button onclick="closeAddItemModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <form id="add-item-form" class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="item_name" class="block text-sm font-medium text-gray-700 mb-2">Item Name *</label>
                        <input type="text" id="item_name" name="item_name" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" 
                               placeholder="Enter item name">
                    </div>
                    
                    <div>
                        <label for="item_category" class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                        <select id="item_category" name="item_category" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="">Select Category</option>
                            <option value="appetizers">Appetizers</option>
                            <option value="main-courses">Main Courses</option>
                            <option value="desserts">Desserts</option>
                            <option value="beverages">Beverages</option>
                            <option value="salads">Salads</option>
                            <option value="soups">Soups</option>
                            <option value="sides">Sides</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="item_price" class="block text-sm font-medium text-gray-700 mb-2">Price *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <input type="number" id="item_price" name="item_price" required step="0.01" min="0"
                                   class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" 
                                   placeholder="0.00">
                        </div>
                    </div>
                    
                    <div>
                        <label for="item_cost" class="block text-sm font-medium text-gray-700 mb-2">Cost (Optional)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <input type="number" id="item_cost" name="item_cost" step="0.01" min="0"
                                   class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" 
                                   placeholder="0.00">
                        </div>
                    </div>
                    
                    <div>
                        <label for="item_sort_order" class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                        <input type="number" id="item_sort_order" name="item_sort_order" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" 
                               placeholder="0">
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="item_active" name="item_active" checked
                               class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                        <label for="item_active" class="ml-2 block text-sm text-gray-700">
                            Active (available for ordering)
                        </label>
                    </div>
                </div>
                
                <div>
                    <label for="item_description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea id="item_description" name="item_description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" 
                              placeholder="Enter item description"></textarea>
                </div>
                
                <div>
                    <label for="item_image" class="block text-sm font-medium text-gray-700 mb-2">Image URL (Optional)</label>
                    <input type="url" id="item_image" name="item_image"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" 
                           placeholder="https://example.com/image.jpg">
                </div>
                
                <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeAddItemModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-primary text-white rounded-md text-sm font-medium hover:bg-primary-dark">
                        Add Menu Item
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Global variables
        let allMenuItems = [];
        let filteredMenuItems = [];
        let currentCategory = '';
        let currentViewMode = 'grid';
        let currentSort = 'name';

        document.addEventListener('DOMContentLoaded', function() {
            loadMenuItems();
            initializeAddItemForm();
            initializeFilters();
            initializeViewControls();
        });

        function loadMenuItems() {
            showLoadingState();
            
            fetch('../api/get-menu-items.php')
                .then(response => response.json())
                .then(data => {
                    hideLoadingState();
                    if (data.success) {
                        allMenuItems = data.menu_items;
                        filteredMenuItems = [...allMenuItems];
                        displayMenuItems();
                        updateStats();
                    } else {
                        showNotification('Error loading menu items', 'error');
                        showEmptyState();
                    }
                })
                .catch(error => {
                    hideLoadingState();
                    console.error('Error loading menu items:', error);
                    showNotification('Error loading menu items', 'error');
                    showEmptyState();
                });
        }

        function displayMenuItems() {
            const container = document.getElementById('menu-items-grid');
            const emptyState = document.getElementById('empty-state');
            const menuContainer = document.getElementById('menu-items-container');
            
            if (!container) return;

            if (filteredMenuItems.length === 0) {
                showEmptyState();
                return;
            }

            hideEmptyState();
            menuContainer.classList.remove('hidden');

            // Sort items
            const sortedItems = sortMenuItems(filteredMenuItems);

            // Display items based on view mode
            if (currentViewMode === 'grid') {
                container.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6';
                container.innerHTML = sortedItems.map(item => createGridItemHTML(item)).join('');
            } else {
                container.className = 'space-y-4';
                container.innerHTML = sortedItems.map(item => createListItemHTML(item)).join('');
            }

            updateItemsCount();
        }

        function createGridItemHTML(item) {
            const statusClass = item.active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
            const statusText = item.active ? 'Active' : 'Inactive';
            
            return `
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow bg-white">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-800 text-lg">${escapeHtml(item.name)}</h4>
                            <span class="inline-block px-2 py-1 text-xs font-medium rounded-full ${statusClass}">
                                ${statusText}
                            </span>
                        </div>
                        <span class="text-primary font-bold text-xl">$${parseFloat(item.price).toFixed(2)}</span>
                    </div>
                    
                    <p class="text-sm text-gray-600 mb-4 min-h-[2.5rem]">${escapeHtml(item.description || 'No description available')}</p>
                    
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <span class="inline-block px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full capitalize">
                                ${escapeHtml(item.category.replace('-', ' '))}
                            </span>
                            ${item.cost > 0 ? `<span class="text-xs text-gray-500">Cost: $${parseFloat(item.cost).toFixed(2)}</span>` : ''}
                        </div>
                        
                        <div class="flex space-x-2">
                            <button onclick="editMenuItem(${item.id})" class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md transition-colors" title="Edit Item">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="toggleItemStatus(${item.id}, ${!item.active})" class="p-2 text-yellow-600 hover:text-yellow-800 hover:bg-yellow-50 rounded-md transition-colors" title="${item.active ? 'Deactivate' : 'Activate'} Item">
                                <i class="fas fa-${item.active ? 'eye-slash' : 'eye'}"></i>
                            </button>
                            <button onclick="deleteMenuItem(${item.id})" class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-md transition-colors" title="Delete Item">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        function createListItemHTML(item) {
            const statusClass = item.active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
            const statusText = item.active ? 'Active' : 'Inactive';
            
            return `
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow bg-white">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-4">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-800 text-lg">${escapeHtml(item.name)}</h4>
                                    <p class="text-sm text-gray-600 mt-1">${escapeHtml(item.description || 'No description available')}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="inline-block px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full capitalize">
                                        ${escapeHtml(item.category.replace('-', ' '))}
                                    </span>
                                    <span class="inline-block px-2 py-1 text-xs font-medium rounded-full ${statusClass}">
                                        ${statusText}
                                    </span>
                                    <span class="text-primary font-bold text-lg">$${parseFloat(item.price).toFixed(2)}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex space-x-2 ml-4">
                            <button onclick="editMenuItem(${item.id})" class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md transition-colors" title="Edit Item">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="toggleItemStatus(${item.id}, ${!item.active})" class="p-2 text-yellow-600 hover:text-yellow-800 hover:bg-yellow-50 rounded-md transition-colors" title="${item.active ? 'Deactivate' : 'Activate'} Item">
                                <i class="fas fa-${item.active ? 'eye-slash' : 'eye'}"></i>
                            </button>
                            <button onclick="deleteMenuItem(${item.id})" class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-md transition-colors" title="Delete Item">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        // Modal Functions
        function openAddItemModal() {
            document.getElementById('add-item-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeAddItemModal() {
            document.getElementById('add-item-modal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            document.getElementById('add-item-form').reset();
        }

        // Form Functions
        function initializeAddItemForm() {
            const form = document.getElementById('add-item-form');
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(form);
                const data = {
                    name: formData.get('item_name'),
                    description: formData.get('item_description'),
                    category: formData.get('item_category'),
                    price: formData.get('item_price'),
                    cost: formData.get('item_cost') || 0,
                    sort_order: formData.get('item_sort_order') || 0,
                    active: formData.has('item_active'),
                    image: formData.get('item_image')
                };

                // Validate required fields
                if (!data.name || !data.category || !data.price) {
                    showNotification('Please fill in all required fields', 'error');
                    return;
                }

                // Submit the form
                submitMenuItem(data);
            });
        }

        function submitMenuItem(data) {
            const submitBtn = document.querySelector('#add-item-form button[type="submit"]');
            const originalText = submitBtn.textContent;
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Adding...';

            fetch('../api/create-menu-item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showNotification('Menu item added successfully!', 'success');
                    closeAddItemModal();
                    loadMenuItems(); // Reload the menu items
                } else {
                    showNotification(result.message || 'Error adding menu item', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error adding menu item', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        }

        // Filter and Search Functions
        function initializeFilters() {
            // Search functionality
            document.getElementById('search-items').addEventListener('input', function(e) {
                applyFilters();
            });

            // Category filter
            document.getElementById('category-filter').addEventListener('change', function(e) {
                applyFilters();
            });

            // Status filter
            document.getElementById('status-filter').addEventListener('change', function(e) {
                applyFilters();
            });

            // Sort functionality
            document.getElementById('sort-items').addEventListener('change', function(e) {
                currentSort = e.target.value;
                displayMenuItems();
            });
        }

        function applyFilters() {
            const searchTerm = document.getElementById('search-items').value.toLowerCase();
            const categoryFilter = document.getElementById('category-filter').value;
            const statusFilter = document.getElementById('status-filter').value;

            filteredMenuItems = allMenuItems.filter(item => {
                const matchesSearch = item.name.toLowerCase().includes(searchTerm) ||
                                    item.description.toLowerCase().includes(searchTerm) ||
                                    item.category.toLowerCase().includes(searchTerm);
                
                const matchesCategory = !categoryFilter || item.category === categoryFilter;
                const matchesStatus = !statusFilter || 
                                    (statusFilter === 'active' && item.active) ||
                                    (statusFilter === 'inactive' && !item.active);

                return matchesSearch && matchesCategory && matchesStatus;
            });

            displayMenuItems();
        }

        function filterByCategory(category) {
            currentCategory = category;
            
            // Update category buttons
            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.classList.remove('active', 'bg-primary', 'text-white');
                btn.classList.add('bg-gray-100', 'text-gray-700');
            });
            
            event.target.classList.add('active', 'bg-primary', 'text-white');
            event.target.classList.remove('bg-gray-100', 'text-gray-700');
            
            // Update filter dropdown
            document.getElementById('category-filter').value = category;
            
            // Apply filters
            applyFilters();
        }

        // View Control Functions
        function initializeViewControls() {
            // View mode buttons
            document.getElementById('grid-view').addEventListener('click', () => setViewMode('grid'));
            document.getElementById('list-view').addEventListener('click', () => setViewMode('list'));
        }

        function setViewMode(mode) {
            currentViewMode = mode;
            
            const gridBtn = document.getElementById('grid-view');
            const listBtn = document.getElementById('list-view');
            
            if (mode === 'grid') {
                gridBtn.classList.add('bg-primary', 'text-white');
                gridBtn.classList.remove('bg-gray-200', 'text-gray-600');
                listBtn.classList.add('bg-gray-200', 'text-gray-600');
                listBtn.classList.remove('bg-primary', 'text-white');
            } else {
                listBtn.classList.add('bg-primary', 'text-white');
                listBtn.classList.remove('bg-gray-200', 'text-gray-600');
                gridBtn.classList.add('bg-gray-200', 'text-gray-600');
                gridBtn.classList.remove('bg-primary', 'text-white');
            }
            
            displayMenuItems();
        }

        // Sort Functions
        function sortMenuItems(items) {
            switch (currentSort) {
                case 'name':
                    return items.sort((a, b) => a.name.localeCompare(b.name));
                case 'name-desc':
                    return items.sort((a, b) => b.name.localeCompare(a.name));
                case 'price':
                    return items.sort((a, b) => parseFloat(a.price) - parseFloat(b.price));
                case 'price-desc':
                    return items.sort((a, b) => parseFloat(b.price) - parseFloat(a.price));
                case 'category':
                    return items.sort((a, b) => a.category.localeCompare(b.category));
                case 'created':
                    return items.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                default:
                    return items;
            }
        }

        // Stats and Display Functions
        function updateStats() {
            const totalItems = allMenuItems.length;
            const activeItems = allMenuItems.filter(item => item.active).length;
            const inactiveItems = totalItems - activeItems;

            document.getElementById('total-items').textContent = totalItems;
            document.getElementById('active-items').textContent = activeItems;
            document.getElementById('inactive-items').textContent = inactiveItems;
        }

        function updateItemsCount() {
            const countElement = document.getElementById('items-count');
            const total = allMenuItems.length;
            const showing = filteredMenuItems.length;
            
            if (showing === total) {
                countElement.textContent = `Showing all ${total} items`;
            } else {
                countElement.textContent = `Showing ${showing} of ${total} items`;
            }
        }

        // State Management Functions
        function showLoadingState() {
            document.getElementById('loading-state').classList.remove('hidden');
            document.getElementById('empty-state').classList.add('hidden');
            document.getElementById('menu-items-container').classList.add('hidden');
        }

        function hideLoadingState() {
            document.getElementById('loading-state').classList.add('hidden');
        }

        function showEmptyState() {
            document.getElementById('empty-state').classList.remove('hidden');
            document.getElementById('menu-items-container').classList.add('hidden');
        }

        function hideEmptyState() {
            document.getElementById('empty-state').classList.add('hidden');
        }

        // Item Management Functions
        function editMenuItem(id) {
            showNotification('Edit functionality coming soon', 'info');
        }

        function toggleItemStatus(id, newStatus) {
            const action = newStatus ? 'activate' : 'deactivate';
            if (confirm(`Are you sure you want to ${action} this menu item?`)) {
                showNotification(`${action} functionality coming soon`, 'info');
            }
        }

        function deleteMenuItem(id) {
            if (confirm('Are you sure you want to delete this menu item? This action cannot be undone.')) {
                showNotification('Delete functionality coming soon', 'info');
            }
        }

        // Export Function
        function exportMenu() {
            showNotification('Export functionality coming soon', 'info');
        }

        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white max-w-sm transform transition-all duration-300 translate-x-full`;
            
            // Set background color based on type
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
            
            // Animate in
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);
            
            // Remove after 5 seconds
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 5000);
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Close modal when clicking outside
        document.getElementById('add-item-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddItemModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAddItemModal();
            }
        });
    </script>

    <?php include '../includes/pos-footer.php'; ?>
