<?php
/**
 * Inventory Items Management
 * Hotel PMS Training System for Students
 */

session_start();
require_once __DIR__ . '/config/database.php';

require_once __DIR__ . '/../vps_session_fix.php';
// Only manager can manage items
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'manager') {
    header('Location: index.php?error=access_denied');
    exit();
}

// Set page title
$page_title = 'Inventory Items';

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
        
        /* Responsive table styles */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .table-responsive table {
            min-width: 100%;
            table-layout: auto;
        }
        
        .table-responsive th,
        .table-responsive td {
            white-space: nowrap;
            min-width: 120px;
        }
        
        .table-responsive th:first-child,
        .table-responsive td:first-child {
            min-width: 200px;
        }
        
        .table-responsive th:last-child,
        .table-responsive td:last-child {
            min-width: 100px;
        }
        
        /* Mobile responsive adjustments */
        @media (max-width: 768px) {
            .table-responsive th,
            .table-responsive td {
                padding: 0.5rem 0.75rem;
                font-size: 0.875rem;
            }
            
            .table-responsive th:first-child,
            .table-responsive td:first-child {
                min-width: 150px;
            }
        }
        
        @media (max-width: 640px) {
            .table-responsive th,
            .table-responsive td {
                padding: 0.375rem 0.5rem;
                font-size: 0.75rem;
            }
            
            .table-responsive th:first-child,
            .table-responsive td:first-child {
                min-width: 120px;
            }
        }
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
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Inventory Items</h2>
                <div class="flex items-center space-x-4">
                    <button id="toggle-add-item-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-plus mr-2"></i>Add Item
                    </button>
                    <button id="import-items-btn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-upload mr-2"></i>Import Items
                    </button>
                </div>

                <!-- Edit Item Modal -->
                <div id="edit-item-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 backdrop-blur-sm">
                    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-4 transform transition-all duration-300 scale-95 opacity-0" id="edit-modal-content">
                        <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-t-xl flex justify-between items-center">
                            <div class="flex items-center">
                                <i class="fas fa-edit mr-3 text-xl"></i>
                                <h4 class="text-xl font-bold">Edit Inventory Item</h4>
                            </div>
                            <button class="text-white hover:text-gray-200 transition-colors duration-200 p-2 hover:bg-white hover:bg-opacity-20 rounded-full" onclick="closeEditModal()">
                                <i class="fas fa-times text-lg"></i>
                            </button>
                        </div>
                        <div class="p-6 space-y-6">
                            <input type="hidden" id="edit-item-id">
                            
                            <!-- Item Name -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">
                                    <i class="fas fa-tag mr-2 text-blue-500"></i>Item Name
                                </label>
                                <input id="edit-item-name" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md" placeholder="Enter item name">
                            </div>
                            
                            <!-- SKU and Cost -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700">
                                        <i class="fas fa-barcode mr-2 text-green-500"></i>SKU
                                    </label>
                                    <input id="edit-item-sku" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md" placeholder="Enter SKU">
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700">
                                        <i class="fas fa-dollar-sign mr-2 text-yellow-500"></i>Unit Cost (₱)
                                    </label>
                                    <input id="edit-item-cost" type="number" step="0.01" min="0" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md" placeholder="0.00">
                                </div>
                            </div>
                            
                            <!-- Stock Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700">
                                        <i class="fas fa-boxes mr-2 text-purple-500"></i>Current Stock
                                    </label>
                                    <input id="edit-item-qty" type="number" min="0" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md" placeholder="0">
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700">
                                        <i class="fas fa-exclamation-triangle mr-2 text-orange-500"></i>Minimum Stock
                                    </label>
                                    <input id="edit-item-min" type="number" min="0" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md" placeholder="0">
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">
                                    <i class="fas fa-align-left mr-2 text-indigo-500"></i>Description
                                </label>
                                <textarea id="edit-item-description" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md resize-none" placeholder="Enter item description"></textarea>
                            </div>
                        </div>
                        
                        <!-- Modal Footer -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl flex justify-end space-x-3">
                            <button class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500" onclick="closeEditModal()">
                                <i class="fas fa-times mr-2"></i>Cancel
                            </button>
                            <button class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg text-sm font-semibold transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-lg hover:shadow-xl" onclick="submitEditItem()">
                                <i class="fas fa-save mr-2"></i>Save Changes
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Restock Modal -->
                <div id="restock-item-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 backdrop-blur-sm">
                    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 transform transition-all duration-300 scale-95 opacity-0" id="restock-modal-content">
                        <div class="px-6 py-4 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-t-xl flex justify-between items-center">
                            <div class="flex items-center">
                                <i class="fas fa-plus-circle mr-3 text-xl"></i>
                                <h4 class="text-xl font-bold">Restock Item</h4>
                            </div>
                            <button class="text-white hover:text-gray-200 transition-colors duration-200 p-2 hover:bg-white hover:bg-opacity-20 rounded-full" onclick="closeRestockModal()">
                                <i class="fas fa-times text-lg"></i>
                            </button>
                        </div>
                        <div class="p-6 space-y-6">
                            <input type="hidden" id="restock-item-id">
                            
                            <!-- Item Information -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">
                                    <i class="fas fa-tag mr-2 text-blue-500"></i>Item
                                </label>
                                <input id="restock-item-name" type="text" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 text-gray-700 font-medium" readonly>
                            </div>
                            
                            <!-- Quantity and Unit -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700">
                                        <i class="fas fa-plus mr-2 text-green-500"></i>Add Quantity
                                    </label>
                                    <input id="restock-add-qty" type="number" min="1" value="10" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md">
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700">
                                        <i class="fas fa-ruler mr-2 text-purple-500"></i>Unit
                                    </label>
                                    <input id="restock-unit" type="text" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 text-gray-700" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Modal Footer -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl flex justify-end space-x-3">
                            <button class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500" onclick="closeRestockModal()">
                                <i class="fas fa-times mr-2"></i>Cancel
                            </button>
                            <button class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-lg text-sm font-semibold transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 shadow-lg hover:shadow-xl" onclick="submitRestockItem()">
                                <i class="fas fa-plus-circle mr-2"></i>Restock Item
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Inventory Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                <!-- Total Items Card -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 border border-blue-200 group">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                    <i class="fas fa-boxes text-white text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-semibold text-blue-700 uppercase tracking-wide">Total Items</p>
                                <p class="text-3xl font-bold text-blue-900" id="total-items">Loading...</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="w-3 h-3 bg-blue-500 rounded-full animate-pulse"></div>
                        </div>
                    </div>
                </div>

                <!-- In Stock Card -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 border border-green-200 group">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                    <i class="fas fa-check-circle text-white text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-semibold text-green-700 uppercase tracking-wide">In Stock</p>
                                <p class="text-3xl font-bold text-green-900" id="in-stock">Loading...</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Card -->
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 border border-yellow-200 group">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                    <i class="fas fa-exclamation-triangle text-white text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-semibold text-yellow-700 uppercase tracking-wide">Low Stock</p>
                                <p class="text-3xl font-bold text-yellow-900" id="low-stock">Loading...</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="w-3 h-3 bg-yellow-500 rounded-full animate-pulse"></div>
                        </div>
                    </div>
                </div>

                <!-- Out of Stock Card -->
                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 border border-red-200 group">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                    <i class="fas fa-times-circle text-white text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-semibold text-red-700 uppercase tracking-wide">Out of Stock</p>
                                <p class="text-3xl font-bold text-red-900" id="out-of-stock">Loading...</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
                        </div>
                    </div>
                </div>

                <!-- POS Products Card -->
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 border border-purple-200 group">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                    <i class="fas fa-shopping-cart text-white text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-semibold text-purple-700 uppercase tracking-wide">POS Products</p>
                                <p class="text-3xl font-bold text-purple-900" id="pos-products-count">Loading...</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="w-3 h-3 bg-purple-500 rounded-full animate-pulse"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Item Categories -->
            <div id="categories-container" class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Categories will be loaded dynamically -->
            </div>

            <!-- Add Item Form -->
            <div id="add-item-form" class="bg-white rounded-lg shadow p-6 mb-8 hidden">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Add New Inventory Item</h3>
                <form class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Item Name</label>
                            <input type="text" id="item-name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter item name" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <select id="item-category" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Select Category</option>
                                <option value="Food & Beverage">Food & Beverage</option>
                                <option value="Amenities">Amenities</option>
                                <option value="Cleaning Supplies">Cleaning Supplies</option>
                                <option value="Office Supplies">Office Supplies</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">SKU/Barcode</label>
                            <input type="text" id="item-sku" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter SKU or barcode">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Unit</label>
                            <select id="item-unit" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Select Unit</option>
                                <option value="Piece">Piece</option>
                                <option value="Box">Box</option>
                                <option value="Bottle">Bottle</option>
                                <option value="Pack">Pack</option>
                                <option value="Liter">Liter</option>
                                <option value="Kilogram">Kilogram</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Stock</label>
                            <input type="number" id="item-quantity" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter current stock" value="0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Stock Level</label>
                            <input type="number" id="item-minimum-stock" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter minimum level" value="0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Unit Cost</label>
                            <input type="number" id="item-cost-price" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter unit cost" value="0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
                            <input type="text" id="item-supplier" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter supplier name">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea id="item-description" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3" placeholder="Enter item description"></textarea>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                            Add Item
                        </button>
                    </div>
                </form>
            </div>

            <!-- Filter Tabs -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex space-x-1">
                        <button id="all-items-tab" class="px-4 py-2 text-sm font-medium text-blue-600 border-b-2 border-blue-600 bg-blue-50 rounded-t-lg">
                            All Items
                        </button>
                        <button id="inventory-items-tab" class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent">
                            Inventory Items
                        </button>
                        <button id="pos-products-tab" class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent">
                            POS Products
                        </button>
                    </div>
                </div>
            </div>

            <!-- Inventory Items Table -->
            <div id="inventory-items-section" class="bg-white rounded-lg shadow mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">Inventory Items</h3>
                        <div class="flex items-center space-x-2">
                            <button id="toggle-view-btn" class="hidden md:flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                                <i class="fas fa-th-large mr-2"></i>
                                <span id="view-toggle-text">Card View</span>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Table View -->
                <div id="table-view" class="table-responsive">
                    <table class="w-full divide-y divide-gray-200 table-auto">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Item</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Category</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">SKU</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Current Stock</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Min Level</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Unit Cost</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Status</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="inventory-items-tbody" class="bg-white divide-y divide-gray-200">
                            <!-- Inventory items will be loaded dynamically -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Card View -->
                <div id="card-view" class="hidden p-6">
                    <div id="inventory-items-cards" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Cards will be loaded dynamically -->
                    </div>
                </div>
            </div>

            <!-- POS Products Table -->
            <div id="pos-products-section" class="bg-white rounded-lg shadow mb-6" style="display: none;">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">POS Products</h3>
                </div>
                <div class="table-responsive">
                    <table class="w-full divide-y divide-gray-200 table-auto">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Product</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Category</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Price</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Cost</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Margin</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Status</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="pos-products-tbody" class="bg-white divide-y divide-gray-200">
                            <!-- POS products will be loaded here via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Combined Items Table (All Items) -->
            <div id="all-items-section" class="bg-white rounded-lg shadow" style="display: none;">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">All Items (Inventory + POS Products)</h3>
                </div>
                <div class="table-responsive">
                    <table class="w-full divide-y divide-gray-200 table-auto">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Item</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Type</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Category</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Price/Cost</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Stock/Status</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-auto">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="all-items-tbody" class="bg-white divide-y divide-gray-200">
                            <!-- Combined items will be loaded here via JavaScript -->
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
    // Load initial data
    loadInventoryStatistics();
    loadInventoryCategories();
    loadInventoryItems();
    
    // Tab switching functionality
    $('#all-items-tab').click(function() {
        switchTab('all-items');
        loadAllItems();
    });
    
    $('#inventory-items-tab').click(function() {
        switchTab('inventory-items');
        loadInventoryItems();
    });
    
    $('#pos-products-tab').click(function() {
        switchTab('pos-products');
        loadPOSProducts();
    });
    
    // View toggle functionality
    $('#toggle-view-btn').click(function() {
        toggleView();
    });
    
    function toggleView() {
        const tableView = $('#table-view');
        const cardView = $('#card-view');
        const toggleText = $('#view-toggle-text');
        
        if (tableView.is(':visible')) {
            // Switch to card view
            tableView.hide();
            cardView.removeClass('hidden').show();
            toggleText.text('Table View');
            $(this).find('i').removeClass('fa-th-large').addClass('fa-table');
            
            // Load cards if not already loaded
            if ($('#inventory-items-cards').children().length === 0) {
                loadInventoryItems();
            }
        } else {
            // Switch to table view
            cardView.hide();
            tableView.show();
            toggleText.text('Card View');
            $(this).find('i').removeClass('fa-table').addClass('fa-th-large');
        }
    }
    
    function switchTab(activeTab) {
        // Update tab styles
        $('.px-4.py-2').removeClass('text-blue-600 border-b-2 border-blue-600 bg-blue-50').addClass('text-gray-500 border-b-2 border-transparent');
        $('#' + activeTab + '-tab').removeClass('text-gray-500 border-b-2 border-transparent').addClass('text-blue-600 border-b-2 border-blue-600 bg-blue-50');
        
        // Show/hide sections
        $('#inventory-items-section, #pos-products-section, #all-items-section').hide();
        $('#' + activeTab + '-section').show();
    }
    
    function loadInventoryStatistics() {
        $.ajax({
            url: 'api/get-inventory-stats.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('Statistics API response:', response);
                if (response.success) {
                    const stats = response.statistics;
                    $('#total-items').text(stats.total_items.toLocaleString());
                    $('#in-stock').text(stats.in_stock.toLocaleString());
                    $('#low-stock').text(stats.low_stock.toLocaleString());
                    $('#out-of-stock').text(stats.out_of_stock.toLocaleString());
                    $('#pos-products-count').text(stats.pos_products.toLocaleString());
                } else {
                    console.error('Error loading inventory statistics:', response.message);
                    $('#total-items, #in-stock, #low-stock, #out-of-stock, #pos-products-count').text('Error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error loading statistics:', error);
                console.error('Response:', xhr.responseText);
                console.error('Status:', xhr.status);
                $('#total-items, #in-stock, #low-stock, #out-of-stock, #pos-products-count').text('Error');
            }
        });
    }
    
    function loadInventoryCategories() {
        $.ajax({
            url: 'api/get-inventory-stats.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayCategories(response.statistics.category_stats);
                } else {
                    console.error('Error loading category statistics:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error loading category statistics:', error);
            }
        });
    }
    
    function displayCategories(categoryStats) {
        const container = $('#categories-container');
        container.empty();
        
        categoryStats.forEach(function(category) {
            const statusClass = category.out_of_stock_count > 0 ? 'bg-red-100 text-red-800' : 
                               category.low_stock_count > 0 ? 'bg-yellow-100 text-yellow-800' : 
                               'bg-green-100 text-green-800';
            const statusText = category.out_of_stock_count > 0 ? 'Out of Stock' : 
                              category.low_stock_count > 0 ? 'Low Stock' : 
                              'In Stock';
            
            const categoryCard = `
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">${category.category_name}</h3>
                        <i class="${category.category_icon} text-xl" style="color: ${category.category_color}"></i>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Total Items</p>
                                <p class="text-sm text-gray-500">${category.item_count} items</p>
                            </div>
                            <span class="px-2 py-1 ${statusClass} text-xs font-semibold rounded-full">
                                ${statusText}
                            </span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-xs">
                            <div class="text-center p-2 bg-green-50 rounded">
                                <div class="font-semibold text-green-800">${category.in_stock_count || 0}</div>
                                <div class="text-green-600">In Stock</div>
                            </div>
                            <div class="text-center p-2 bg-yellow-50 rounded">
                                <div class="font-semibold text-yellow-800">${category.low_stock_count || 0}</div>
                                <div class="text-yellow-600">Low Stock</div>
                            </div>
                            <div class="text-center p-2 bg-red-50 rounded">
                                <div class="font-semibold text-red-800">${category.out_of_stock_count || 0}</div>
                                <div class="text-red-600">Out of Stock</div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.append(categoryCard);
        });
    }
    
    function loadInventoryItems() {
        $.ajax({
            url: 'api/get-inventory-items.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Store items globally for easy access
                    window.currentItems = response.inventory_items;
                    displayInventoryItems(response.inventory_items);
                } else {
                    console.error('Error loading inventory items:', response.message);
                    showNotification('Failed to load inventory items: ' + response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error loading inventory items:', error);
                showNotification('Failed to load inventory items. Please try again.', 'error');
            }
        });
    }
    
    function displayInventoryItems(items) {
        const tbody = $('#inventory-items-tbody');
        tbody.empty();
        
        items.forEach(function(item) {
            const statusClass = item.stock_status === 'out_of_stock' ? 'bg-red-100 text-red-800' : 
                               item.stock_status === 'low_stock' ? 'bg-yellow-100 text-yellow-800' : 
                               'bg-green-100 text-green-800';
            const statusText = item.stock_status === 'out_of_stock' ? 'Out of Stock' : 
                              item.stock_status === 'low_stock' ? 'Low Stock' : 
                              'In Stock';
            
            const row = `
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8">
                                <div class="h-8 w-8 rounded-full flex items-center justify-center" style="background-color: ${item.category_color || '#6B7280'}">
                                    <i class="${item.category_icon || 'fas fa-box'} text-white text-xs"></i>
                                </div>
                            </div>
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900 truncate max-w-xs">${item.name}</div>
                                <div class="text-xs text-gray-500 truncate max-w-xs">${item.description || 'No description'}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-3 py-4 text-sm text-gray-900">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            ${item.category_name || 'Uncategorized'}
                        </span>
                    </td>
                    <td class="px-3 py-4 text-sm text-gray-900 font-mono">${item.sku || 'N/A'}</td>
                    <td class="px-3 py-4 text-sm text-gray-900">
                        <div class="font-medium">${item.quantity}</div>
                        <div class="text-xs text-gray-500">${item.unit || ''}</div>
                    </td>
                    <td class="px-3 py-4 text-sm text-gray-900">
                        <div class="font-medium">${item.minimum_stock}</div>
                        <div class="text-xs text-gray-500">${item.unit || ''}</div>
                    </td>
                    <td class="px-3 py-4 text-sm text-gray-900 font-medium">₱${parseFloat(item.cost_price || item.unit_price || 0).toFixed(2)}</td>
                    <td class="px-3 py-4">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                            ${statusText}
                        </span>
                    </td>
                    <td class="px-3 py-4 text-sm font-medium">
                        <div class="flex space-x-1">
                            <button class="group relative inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-sm hover:shadow-md" onclick="openEditItem(${item.id})" title="Edit Item">
                                <i class="fas fa-edit mr-1.5"></i>
                                <span class="hidden sm:inline">Edit</span>
                            </button>
                            <button class="group relative inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 shadow-sm hover:shadow-md" onclick="restockItem(${item.id})" title="Restock Item">
                                <i class="fas fa-plus-circle mr-1.5"></i>
                                <span class="hidden sm:inline">Restock</span>
                            </button>
                            <button class="group relative inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 shadow-sm hover:shadow-md" onclick="deleteItem(${item.id})" title="Delete Item">
                                <i class="fas fa-trash-alt mr-1.5"></i>
                                <span class="hidden sm:inline">Delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
        
        // Also display cards if card view is active
        if ($('#card-view').is(':visible')) {
            displayInventoryItemsCards(items);
        }
    }
    
    function displayInventoryItemsCards(items) {
        const cardsContainer = $('#inventory-items-cards');
        cardsContainer.empty();
        
        items.forEach(function(item) {
            const statusClass = item.stock_status === 'out_of_stock' ? 'bg-red-100 text-red-800' : 
                               item.stock_status === 'low_stock' ? 'bg-yellow-100 text-yellow-800' : 
                               'bg-green-100 text-green-800';
            const statusText = item.stock_status === 'out_of_stock' ? 'Out of Stock' : 
                              item.stock_status === 'low_stock' ? 'Low Stock' : 
                              'In Stock';
            
            const card = `
                <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center">
                            <div class="h-10 w-10 rounded-full flex items-center justify-center mr-3" style="background-color: ${item.category_color || '#6B7280'}">
                                <i class="${item.category_icon || 'fas fa-box'} text-white"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 truncate">${item.name}</h4>
                                <p class="text-xs text-gray-500">${item.category_name || 'Uncategorized'}</p>
                            </div>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full ${statusClass}">
                            ${statusText}
                        </span>
                    </div>
                    
                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">SKU:</span>
                            <span class="font-mono text-gray-900">${item.sku || 'N/A'}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Current Stock:</span>
                            <span class="font-medium">${item.quantity} ${item.unit || ''}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Min Level:</span>
                            <span class="font-medium">${item.minimum_stock} ${item.unit || ''}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Unit Cost:</span>
                            <span class="font-medium">₱${parseFloat(item.cost_price || item.unit_price || 0).toFixed(2)}</span>
                        </div>
                    </div>
                    
                    <div class="flex space-x-2">
                        <button class="flex-1 text-blue-600 hover:text-blue-900 text-xs font-medium py-2 px-3 border border-blue-200 rounded-md hover:bg-blue-50" onclick="openEditItem(${item.id})">
                            Edit
                        </button>
                        <button class="flex-1 text-green-600 hover:text-green-900 text-xs font-medium py-2 px-3 border border-green-200 rounded-md hover:bg-green-50" onclick="restockItem(${item.id})">
                            Restock
                        </button>
                        <button class="flex-1 text-red-600 hover:text-red-900 text-xs font-medium py-2 px-3 border border-red-200 rounded-md hover:bg-red-50" onclick="deleteItem(${item.id})">
                            Delete
                        </button>
                    </div>
                </div>
            `;
            cardsContainer.append(card);
        });
    }
    
    function loadPOSProducts() {
        $.ajax({
            url: 'api/get-pos-products.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayPOSProducts(response.pos_products);
                } else {
                    console.error('Error loading POS products:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
            }
        });
    }
    
    function displayPOSProducts(products) {
        const tbody = $('#pos-products-tbody');
        tbody.empty();
        
        products.forEach(function(product) {
            const margin = ((product.price - product.cost) / product.price * 100).toFixed(1);
            const statusClass = product.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
            const statusText = product.active ? 'Active' : 'Inactive';
            
            const row = `
                <tr class="hover:bg-gray-50 transition-colors duration-200">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-full bg-gradient-to-r from-purple-500 to-purple-600 flex items-center justify-center shadow-lg">
                                    <i class="fas fa-utensils text-white text-sm"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900 truncate max-w-xs">${product.name}</div>
                                <div class="text-sm text-gray-500 truncate max-w-xs">${product.description || 'No description'}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span class="px-2 py-1 inline-flex text-xs font-medium bg-purple-100 text-purple-800 rounded-full capitalize">
                            ${product.category.replace('-', ' ')}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span class="font-bold text-lg text-green-600">₱${parseFloat(product.price).toFixed(2)}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span class="font-medium text-gray-600">₱${parseFloat(product.cost).toFixed(2)}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full ${parseFloat(margin) > 50 ? 'bg-green-100 text-green-800' : parseFloat(margin) > 25 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'}">
                            ${margin}%
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass} border">
                            <i class="fas ${product.active ? 'fa-check-circle' : 'fa-times-circle'} mr-1"></i>
                            ${statusText}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <button class="group relative inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-sm hover:shadow-md" onclick="openEditItem(${product.id})" title="Edit Product">
                                <i class="fas fa-edit mr-1.5"></i>
                                <span class="hidden sm:inline">Edit</span>
                            </button>
                            <button class="group relative inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 shadow-sm hover:shadow-md" onclick="restockItem(${product.id})" title="Restock Product">
                                <i class="fas fa-plus-circle mr-1.5"></i>
                                <span class="hidden sm:inline">Restock</span>
                            </button>
                            <button class="group relative inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 shadow-sm hover:shadow-md" onclick="deleteItem(${product.id})" title="Delete Product">
                                <i class="fas fa-trash-alt mr-1.5"></i>
                                <span class="hidden sm:inline">Delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }
    
    function loadAllItems() {
        const allItemsTbody = $('#all-items-tbody');
        allItemsTbody.empty();
        
        // Load inventory items
        $.ajax({
            url: 'api/get-inventory-items.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    response.inventory_items.forEach(function(item) {
                        const statusClass = item.stock_status === 'out_of_stock' ? 'bg-red-100 text-red-800' : 
                                           item.stock_status === 'low_stock' ? 'bg-yellow-100 text-yellow-800' : 
                                           'bg-green-100 text-green-800';
                        const statusText = item.stock_status === 'out_of_stock' ? 'Out of Stock' : 
                                          item.stock_status === 'low_stock' ? 'Low Stock' : 
                                          'In Stock';
            
            const combinedRow = `
                <tr class="hover:bg-gray-50 transition-colors duration-200">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-full flex items-center justify-center shadow-lg" style="background-color: ${item.category_color || '#6B7280'}">
                                    <i class="${item.category_icon || 'fas fa-box'} text-white text-sm"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900 truncate max-w-xs">${item.name}</div>
                                <div class="text-sm text-gray-500">Inventory Item</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                            <i class="fas fa-box mr-1"></i>Inventory
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span class="px-2 py-1 inline-flex text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                            ${item.category_name || 'Uncategorized'}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span class="font-bold text-lg">₱${parseFloat(item.cost_price || item.unit_price || 0).toFixed(2)}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass} border">
                            <i class="fas ${item.stock_status === 'out_of_stock' ? 'fa-times-circle' : item.stock_status === 'low_stock' ? 'fa-exclamation-triangle' : 'fa-check-circle'} mr-1"></i>
                            ${statusText}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <button class="group relative inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-sm hover:shadow-md" onclick="openEditItem(${item.id})" title="Edit Item">
                                <i class="fas fa-edit mr-1.5"></i>
                                <span class="hidden sm:inline">Edit</span>
                            </button>
                            <button class="group relative inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 shadow-sm hover:shadow-md" onclick="restockItem(${item.id})" title="Restock Item">
                                <i class="fas fa-plus-circle mr-1.5"></i>
                                <span class="hidden sm:inline">Restock</span>
                            </button>
                            <button class="group relative inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 shadow-sm hover:shadow-md" onclick="deleteItem(${item.id})" title="Delete Item">
                                <i class="fas fa-trash-alt mr-1.5"></i>
                                <span class="hidden sm:inline">Delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            allItemsTbody.append(combinedRow);
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error loading inventory items for combined view:', error);
            }
        });
        
        // Load POS products and add them to combined view
        $.ajax({
            url: 'api/get-pos-products.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    response.pos_products.forEach(function(product) {
                        const statusClass = product.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                        const statusText = product.active ? 'Active' : 'Inactive';
                        
                        const combinedRow = `
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-r from-purple-500 to-purple-600 flex items-center justify-center shadow-lg">
                                                <i class="fas fa-utensils text-white text-sm"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 truncate max-w-xs">${product.name}</div>
                                            <div class="text-sm text-gray-500">POS Product</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800 border border-purple-200">
                                        <i class="fas fa-utensils mr-1"></i>POS
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="px-2 py-1 inline-flex text-xs font-medium bg-purple-100 text-purple-800 rounded-full capitalize">
                                        ${product.category.replace('-', ' ')}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="font-bold text-lg text-green-600">₱${parseFloat(product.price).toFixed(2)}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass} border">
                                        <i class="fas ${product.active ? 'fa-check-circle' : 'fa-times-circle'} mr-1"></i>
                                        ${statusText}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button class="group relative inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-sm hover:shadow-md" onclick="openEditItem(${product.id})" title="Edit Product">
                                            <i class="fas fa-edit mr-1.5"></i>
                                            <span class="hidden sm:inline">Edit</span>
                                        </button>
                                        <button class="group relative inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 shadow-sm hover:shadow-md" onclick="restockItem(${product.id})" title="Restock Product">
                                            <i class="fas fa-plus-circle mr-1.5"></i>
                                            <span class="hidden sm:inline">Restock</span>
                                        </button>
                                        <button class="group relative inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 shadow-sm hover:shadow-md" onclick="deleteItem(${product.id})" title="Delete Product">
                                            <i class="fas fa-trash-alt mr-1.5"></i>
                                            <span class="hidden sm:inline">Delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                        allItemsTbody.append(combinedRow);
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error loading POS products for combined view:', error);
            }
        });
    }
    
    // Button event handlers
    $('#toggle-add-item-btn').click(function() {
        toggleAddItemForm();
    });
    
    $('#import-items-btn').click(function() {
        importItems();
    });
    
    // Add item form submission
    $('#add-item-form form').submit(function(e) {
        e.preventDefault();
        addNewItem();
    });
    
    function toggleAddItemForm() {
        const form = $('#add-item-form');
        const button = $('#toggle-add-item-btn');
        
        if (form.hasClass('hidden')) {
            form.removeClass('hidden');
            button.html('<i class="fas fa-times mr-2"></i>Cancel');
            button.removeClass('bg-blue-600 hover:bg-blue-700').addClass('bg-red-600 hover:bg-red-700');
            
            // Scroll to form
            $('html, body').animate({
                scrollTop: form.offset().top - 100
            }, 500);
            
            // Focus on first input
            form.find('input:first').focus();
        } else {
            form.addClass('hidden');
            button.html('<i class="fas fa-plus mr-2"></i>Add Item');
            button.removeClass('bg-red-600 hover:bg-red-700').addClass('bg-blue-600 hover:bg-blue-700');
            
            // Reset form
            form[0].reset();
        }
    }
    
    function importItems() {
        // Create file input for CSV import
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = '.csv';
        input.onchange = function(e) {
            const file = e.target.files[0];
            if (file) {
                uploadCSVFile(file);
            }
        };
        input.click();
    }
    
    function uploadCSVFile(file) {
        const formData = new FormData();
        formData.append('csv_file', file);
        
        $.ajax({
            url: 'api/import-items.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Items imported successfully! ' + response.imported_count + ' items imported.');
                    loadInventoryItems();
                    loadAllItems();
                } else {
                    alert('Error importing items: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error importing items:', error);
                alert('Error importing items');
            }
        });
    }
    
    function addNewItem() {
        const formData = {
            name: $('#item-name').val(),
            category: $('#item-category').val(),
            sku: $('#item-sku').val(),
            unit: $('#item-unit').val(),
            quantity: $('#item-quantity').val(),
            minimum_stock: $('#item-minimum-stock').val(),
            cost_price: $('#item-cost-price').val(),
            supplier: $('#item-supplier').val(),
            description: $('#item-description').val()
        };
        
        // Validate required fields
        if (!formData.name || !formData.category || !formData.unit) {
            alert('Please fill in all required fields (Name, Category, Unit)');
            return;
        }
        
        $.ajax({
            url: 'api/create-inventory-item.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Item added successfully!');
                    toggleAddItemForm(); // Hide form
                    loadInventoryItems();
                    loadAllItems();
                } else {
                    alert('Error adding item: ' + (response.message || ''));
                }
            },
            error: function(xhr, status, error) {
                let msg = 'Error adding item';
                try {
                    const resp = xhr.responseJSON || JSON.parse(xhr.responseText);
                    if (resp && resp.message) msg += ': ' + resp.message;
                } catch(e) {}
                alert(msg);
            }
        });
    }
    
    // Edit item (modal)
    window.openEditItem = function(itemId) {
        // Find the item data from the current items array
        const item = window.currentItems ? window.currentItems.find(i => i.id == itemId) : null;
        
        if (item) {
            $('#edit-item-id').val(itemId);
            $('#edit-item-name').val(item.name);
            $('#edit-item-sku').val(item.sku || '');
            $('#edit-item-qty').val(item.quantity);
            $('#edit-item-min').val(item.minimum_stock);
            $('#edit-item-cost').val(parseFloat(item.cost_price || item.unit_price || 0).toFixed(2));
            $('#edit-item-description').val(item.description || '');
        } else {
            // Fallback to table parsing if item not found in array
            const row = $(event.target).closest('tr');
            const name = row.find('td').eq(0).find('.text-sm.font-medium').text().trim();
            const sku = row.find('td').eq(2).text().trim();
            const qty = parseInt(row.find('td').eq(3).find('.font-medium').text()) || 0;
            const min = parseInt(row.find('td').eq(4).find('.font-medium').text()) || 0;
            const costText = row.find('td').eq(5).text().replace('₱','').replace(/,/g,'');
            const cost = parseFloat(costText) || 0;

            $('#edit-item-id').val(itemId);
            $('#edit-item-name').val(name);
            $('#edit-item-sku').val(sku === 'N/A' ? '' : sku);
            $('#edit-item-qty').val(qty);
            $('#edit-item-min').val(min);
            $('#edit-item-cost').val(cost.toFixed(2));
            $('#edit-item-description').val('');
        }

        // Show modal with animation
        $('#edit-item-modal').removeClass('hidden').addClass('flex');
        setTimeout(() => {
            $('#edit-modal-content').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
        }, 10);
    }

    window.closeEditModal = function() {
        $('#edit-modal-content').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
        setTimeout(() => {
            $('#edit-item-modal').addClass('hidden').removeClass('flex');
        }, 300);
    }

    window.submitEditItem = function() {
        const id = $('#edit-item-id').val();
        const name = $('#edit-item-name').val().trim();
        const quantity = parseInt($('#edit-item-qty').val()) || 0;
        const minimum_stock = parseInt($('#edit-item-min').val()) || 0;
        const sku = $('#edit-item-sku').val().trim();
        const cost_price = parseFloat($('#edit-item-cost').val()) || 0;
        const description = $('#edit-item-description').val().trim();

        // Validation
        if (!name) {
            alert('Please enter an item name');
            $('#edit-item-name').focus();
            return;
        }

        if (quantity < 0) {
            alert('Quantity cannot be negative');
            $('#edit-item-qty').focus();
            return;
        }

        if (minimum_stock < 0) {
            alert('Minimum stock cannot be negative');
            $('#edit-item-min').focus();
            return;
        }

        if (cost_price < 0) {
            alert('Cost price cannot be negative');
            $('#edit-item-cost').focus();
            return;
        }

        const payload = {
            id: id,
            name: name,
            quantity: quantity,
            minimum_stock: minimum_stock,
            sku: sku,
            cost_price: cost_price,
            description: description
        };

        // Show loading state
        const submitBtn = $('button[onclick="submitEditItem()"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Saving...').prop('disabled', true);

        $.post('api/update-inventory-item.php', payload, function(resp){
            if (resp.success) {
                // Show success message
                showNotification('Item updated successfully!', 'success');
                closeEditModal();
                loadInventoryItems();
                loadAllItems();
            } else {
                showNotification('Update failed: ' + (resp.message || 'Unknown error'), 'error');
            }
        }, 'json').fail(function(xhr){
            let msg = 'Update error';
            try {
                const resp = xhr.responseJSON || JSON.parse(xhr.responseText);
                if (resp && resp.message) msg += ': ' + resp.message;
            } catch(e) {}
            showNotification(msg, 'error');
        }).always(function() {
            // Reset button state
            submitBtn.html(originalText).prop('disabled', false);
        });
    }

    // Restock simple handler (adds quantity)
    window.restockItem = function(itemId) {
        // Find the item data from the current items array
        const item = window.currentItems ? window.currentItems.find(i => i.id == itemId) : null;
        
        if (item) {
            $('#restock-item-id').val(itemId);
            $('#restock-item-name').val(item.name);
            $('#restock-unit').val(item.unit || 'pcs');
        } else {
            // Fallback to table parsing
            const row = $(event.target).closest('tr');
            const name = row.find('td').eq(0).find('.text-sm.font-medium').text().trim();
            const unit = row.find('td').eq(3).find('.text-xs').text().trim();
            $('#restock-item-id').val(itemId);
            $('#restock-item-name').val(name);
            $('#restock-unit').val(unit);
        }
        
        $('#restock-add-qty').val(10);
        
        // Show modal with animation
        $('#restock-item-modal').removeClass('hidden').addClass('flex');
        setTimeout(() => {
            $('#restock-modal-content').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
        }, 10);
    }

    window.closeRestockModal = function() {
        $('#restock-modal-content').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
        setTimeout(() => {
            $('#restock-item-modal').addClass('hidden').removeClass('flex');
        }, 300);
    }

    window.submitRestockItem = function() {
        const id = $('#restock-item-id').val();
        const add = parseInt($('#restock-add-qty').val());
        
        if (isNaN(add) || add <= 0) { 
            showNotification('Please enter a valid quantity', 'error');
            $('#restock-add-qty').focus();
            return; 
        }

        // Show loading state
        const submitBtn = $('button[onclick="submitRestockItem()"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Restocking...').prop('disabled', true);

        $.post('api/update-inventory-item.php', { id: id, quantity: add }, function(resp){
            if (resp.success) {
                showNotification('Item restocked successfully!', 'success');
                closeRestockModal();
                loadInventoryItems();
                loadAllItems();
            } else {
                showNotification('Restock failed: ' + (resp.message || 'Unknown error'), 'error');
            }
        }, 'json').fail(function(xhr){
            let msg = 'Restock error';
            try {
                const resp = xhr.responseJSON || JSON.parse(xhr.responseText);
                if (resp && resp.message) msg += ': ' + resp.message;
            } catch(e) {}
            showNotification(msg, 'error');
        }).always(function() {
            // Reset button state
            submitBtn.html(originalText).prop('disabled', false);
        });
    }

    // Delete item
    window.deleteItem = function(itemId) {
        // Find item name for confirmation
        const item = window.currentItems ? window.currentItems.find(i => i.id == itemId) : null;
        const itemName = item ? item.name : 'this item';
        
        if (!confirm(`Are you sure you want to delete "${itemName}"?\n\nThis action cannot be undone.`)) return;
        
        // Show loading state on the delete button
        const deleteBtn = $(event.target).closest('button');
        const originalText = deleteBtn.html();
        deleteBtn.html('<i class="fas fa-spinner fa-spin mr-1.5"></i><span class="hidden sm:inline">Deleting...</span>').prop('disabled', true);
        
        $.post('api/delete-inventory-item.php', { id: itemId }, function(resp){
            if (resp.success) {
                showNotification('Item deleted successfully!', 'success');
                loadInventoryItems();
                loadAllItems();
            } else {
                showNotification('Delete failed: ' + (resp.message || 'Unknown error'), 'error');
                // Reset button state on error
                deleteBtn.html(originalText).prop('disabled', false);
            }
        }, 'json').fail(function(xhr){
            let msg = 'Delete error';
            try {
                const resp = xhr.responseJSON || JSON.parse(xhr.responseText);
                if (resp && resp.message) msg += ': ' + resp.message;
            } catch(e) {}
            showNotification(msg, 'error');
            // Reset button state on error
            deleteBtn.html(originalText).prop('disabled', false);
        });
    }

    // Notification system
    function showNotification(message, type = 'info') {
        const notification = $(`
            <div class="fixed top-4 right-4 z-50 max-w-sm w-full bg-white rounded-lg shadow-lg border-l-4 transform transition-all duration-300 translate-x-full" 
                 style="border-left-color: ${type === 'success' ? '#10B981' : type === 'error' ? '#EF4444' : type === 'warning' ? '#F59E0B' : '#3B82F6'}">
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas ${type === 'success' ? 'fa-check-circle text-green-500' : type === 'error' ? 'fa-exclamation-circle text-red-500' : type === 'warning' ? 'fa-exclamation-triangle text-yellow-500' : 'fa-info-circle text-blue-500'}"></i>
                        </div>
                        <div class="ml-3 w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900">${message}</p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none" onclick="$(this).closest('.fixed').remove()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `);
        
        $('body').append(notification);
        
        // Animate in
        setTimeout(() => {
            notification.removeClass('translate-x-full');
        }, 10);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.addClass('translate-x-full');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 5000);
    }
    
    // Handle URL parameters for quick actions
    function handleUrlParameters() {
        const urlParams = new URLSearchParams(window.location.search);
        const action = urlParams.get('action');
        const filter = urlParams.get('filter');
        
        if (action === 'add') {
            // Open add item modal
            openAddItemModal();
        } else if (filter === 'low_stock') {
            // Filter to show low stock items
            filterItems('low_stock');
        }
    }
    
    // Call on page load
    $(document).ready(function() {
        handleUrlParameters();
    });
});
</script>