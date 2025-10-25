<?php
/**
 * Inventory Categories Management
 * Hotel PMS Training System for Students
 */

session_start();
require_once __DIR__ . '/config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Set page title
$page_title = 'Inventory Categories';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Hotel Inventory System</title>
    <link rel="icon" type="image/png" href="../../assets/images/seait-logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        #sidebar { transition: transform 0.3s ease-in-out; }
        @media (max-width: 1023px) { #sidebar { transform: translateX(-100%); z-index: 50; } #sidebar.sidebar-open { transform: translateX(0); } }
        @media (min-width: 1024px) { #sidebar { transform: translateX(0) !important; } }
        #sidebar-overlay { transition: opacity 0.3s ease-in-out; z-index: 40; }
        .main-content { margin-left: 0; padding-top: 4rem; }
        @media (min-width: 1024px) { .main-content { margin-left: 16rem; } }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#10B981',
                        secondary: '#059669',
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
        
        <!-- Include unified inventory header and sidebar -->
        <?php include 'includes/inventory-header.php'; ?>
        <?php include 'includes/sidebar-inventory.php'; ?>

        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Inventory Categories</h2>
                <div class="flex items-center space-x-4">
                    <button id="add-category-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-plus mr-2"></i>Add Category
                    </button>
                    <button id="sort-categories-btn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-sort mr-2"></i>Sort Categories
                    </button>
                </div>
            </div>

            <!-- Category Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-tags text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Categories</p>
                            <p class="text-2xl font-semibold text-gray-900">24</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-boxes text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Items</p>
                            <p class="text-2xl font-semibold text-gray-900">1,247</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-chart-bar text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Avg Items/Category</p>
                            <p class="text-2xl font-semibold text-gray-900">52</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-dollar-sign text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Value</p>
                            <p class="text-2xl font-semibold text-gray-900" id="total-categories-value">$0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category Overview -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Top Categories -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Top Categories by Items</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-utensils text-orange-600"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Food & Beverage</p>
                                    <p class="text-sm text-gray-500">245 items</p>
                                </div>
                            </div>
                            <span class="text-lg font-semibold text-gray-900" id="category-value-1">$0</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-gift text-purple-600"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Amenities</p>
                                    <p class="text-sm text-gray-500">189 items</p>
                                </div>
                            </div>
                            <span class="text-lg font-semibold text-gray-900" id="category-value-2">$0</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-broom text-blue-600"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Cleaning Supplies</p>
                                    <p class="text-sm text-gray-500">156 items</p>
                                </div>
                            </div>
                            <span class="text-lg font-semibold text-gray-900" id="category-value-3">$0</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-clipboard text-green-600"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Office Supplies</p>
                                    <p class="text-sm text-gray-500">98 items</p>
                                </div>
                            </div>
                            <span class="text-lg font-semibold text-gray-900" id="category-value-4">$0</span>
                        </div>
                    </div>
                </div>

                <!-- Category Performance -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Category Performance</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">Food & Beverage</span>
                                <span class="text-gray-900">85%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-orange-500 h-2 rounded-full" style="width: 85%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">Amenities</span>
                                <span class="text-gray-900">72%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-purple-500 h-2 rounded-full" style="width: 72%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">Cleaning Supplies</span>
                                <span class="text-gray-900">68%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: 68%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">Office Supplies</span>
                                <span class="text-gray-900">45%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: 45%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Category Form -->
            <div id="add-category-form" class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Add New Category</h3>
                <form class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category Name</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter category name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Parent Category</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option>No Parent (Top Level)</option>
                                <option>Food & Beverage</option>
                                <option>Amenities</option>
                                <option>Cleaning Supplies</option>
                                <option>Office Supplies</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category Code</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter category code">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Icon</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option>Select Icon</option>
                                <option>fas fa-utensils (Food)</option>
                                <option>fas fa-gift (Gift)</option>
                                <option>fas fa-broom (Cleaning)</option>
                                <option>fas fa-clipboard (Office)</option>
                                <option>fas fa-tools (Tools)</option>
                                <option>fas fa-medkit (Medical)</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3" placeholder="Enter category description"></textarea>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label class="ml-2 block text-sm text-gray-900">
                            Make this category active
                        </label>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                            Add Category
                        </button>
                    </div>
                </form>
            </div>

            <!-- Categories Table -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">All Categories</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Value</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="categories-tbody" class="bg-white divide-y divide-gray-200">
                            <!-- Categories will be loaded dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Include footer -->
        <?php include '../includes/pos-footer.php'; ?>
    </body>
</html>

<script>
$(document).ready(function() {
    loadCategories();
    
    function loadCategories() {
        $.ajax({
            url: 'api/get-inventory-categories.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayCategories(response.categories);
                    updateCategoryOverview(response.categories);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading categories:', error);
            }
        });
    }
    
    function displayCategories(categories) {
        const tbody = $('#categories-tbody');
        tbody.empty();
        
        if (categories.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        No categories found
                    </td>
                </tr>
            `);
            return;
        }
        
        categories.forEach(function(category) {
            // Get category statistics
            getCategoryStats(category.id, function(stats) {
                const statusClass = category.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                const statusText = category.active ? 'Active' : 'Inactive';
                
                const row = `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-lg flex items-center justify-center" style="background-color: ${category.color}20;">
                                        <i class="${category.icon} text-lg" style="color: ${category.color};"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">${category.name}</div>
                                    <div class="text-sm text-gray-500">${category.description || 'No description'}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${category.id.toString().padStart(3, '0')}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${stats.item_count}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$${stats.total_value.toLocaleString()}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                                ${statusText}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button class="text-blue-600 hover:text-blue-900 mr-3" onclick="editCategory(${category.id})">Edit</button>
                            <button class="text-green-600 hover:text-green-900" onclick="viewCategoryItems(${category.id})">View Items</button>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });
        });
    }
    
    function getCategoryStats(categoryId, callback) {
        $.ajax({
            url: 'api/get-category-stats.php',
            method: 'GET',
            data: { category_id: categoryId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    callback(response.stats);
                } else {
                    callback({ item_count: 0, total_value: 0 });
                }
            },
            error: function() {
                callback({ item_count: 0, total_value: 0 });
            }
        });
    }
    
    window.editCategory = function(categoryId) {
        alert('Edit category functionality would open here for category ID: ' + categoryId);
    };
    
    window.viewCategoryItems = function(categoryId) {
        window.location.href = 'items.php?category=' + categoryId;
    };
    
    // Button event handlers
    $('#add-category-btn').click(function() {
        showAddCategoryModal();
    });
    
    $('#sort-categories-btn').click(function() {
        sortCategories();
    });
    
    function showAddCategoryModal() {
        // Show the add category form (it's already in the HTML)
        $('html, body').animate({
            scrollTop: $('#add-category-form').offset().top - 100
        }, 500);
        
        // Focus on the first input
        $('#add-category-form input:first').focus();
    }
    
    function sortCategories() {
        // Toggle sort order
        const currentOrder = $('#sort-categories-btn').data('order') || 'asc';
        const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
        
        $('#sort-categories-btn').data('order', newOrder);
        $('#sort-categories-btn').html(`<i class="fas fa-sort-${newOrder === 'asc' ? 'up' : 'down'} mr-2"></i>Sort Categories`);
        
        // Reload categories with new sort order
        loadCategories();
    }
    
    function updateCategoryOverview(categories) {
        let totalValue = 0;
        
        categories.forEach(function(category, index) {
            getCategoryStats(category.id, function(stats) {
                totalValue += stats.total_value;
                
                // Update individual category values
                if (index < 4) {
                    $('#category-value-' + (index + 1)).text('$' + stats.total_value.toLocaleString());
                }
                
                // Update total value when all categories are processed
                if (index === categories.length - 1) {
                    $('#total-categories-value').text('$' + totalValue.toLocaleString());
                }
            });
        });
    }
});
</script>
