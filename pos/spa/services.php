<?php
session_start();

// Check if user is logged in to POS
if (!isset($_SESSION['pos_user_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Spa Services';
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
                    <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Spa Services Management</h2>
                    <p class="text-gray-600 mt-1">Manage spa treatments, pricing, and service availability</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <div id="current-date" class="text-sm text-gray-600"></div>
                        <div id="current-time" class="text-sm text-gray-600"></div>
                    </div>
                    <button onclick="exportServices()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-download mr-2"></i>Export
                    </button>
                    <button onclick="openAddServiceModal()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>Add Service
                    </button>
                </div>
            </div>

            <!-- Service Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-lg">
                            <i class="fas fa-spa text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="total-services">24</h3>
                            <p class="text-sm text-gray-600">Total Services</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-lg">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="active-services">22</h3>
                            <p class="text-sm text-gray-600">Active Services</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <i class="fas fa-users text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="bookings-today">18</h3>
                            <p class="text-sm text-gray-600">Bookings Today</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-lg">
                            <i class="fas fa-dollar-sign text-yellow-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="revenue-today">$2,450</h3>
                            <p class="text-sm text-gray-600">Revenue Today</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex flex-col lg:flex-row gap-4">
                    <div class="flex-1">
                        <label for="search-services" class="block text-sm font-medium text-gray-700 mb-2">Search Services</label>
                        <div class="relative">
                            <input type="text" id="search-services" placeholder="Search by service name, description, or therapist..." 
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>
                    </div>
                    <div class="lg:w-48">
                        <label for="category-filter" class="block text-sm font-medium text-gray-700 mb-2">Service Category</label>
                        <select id="category-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="">All Categories</option>
                            <option value="massage">Massage</option>
                            <option value="facial">Facial</option>
                            <option value="body_treatment">Body Treatment</option>
                            <option value="wellness">Wellness</option>
                            <option value="nail_care">Nail Care</option>
                            <option value="hair_care">Hair Care</option>
                        </select>
                    </div>
                    <div class="lg:w-48">
                        <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="status-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="fully_booked">Fully Booked</option>
                        </select>
                    </div>
                    <div class="lg:w-48">
                        <label for="duration-filter" class="block text-sm font-medium text-gray-700 mb-2">Duration</label>
                        <select id="duration-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="">All Durations</option>
                            <option value="30">30 minutes</option>
                            <option value="60">60 minutes</option>
                            <option value="90">90 minutes</option>
                            <option value="120">120 minutes</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Service Categories Navigation -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Category Navigation</h3>
                <div class="flex flex-wrap gap-2">
                    <button onclick="filterByCategory('')" class="category-btn bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                        All Services
                    </button>
                    <button onclick="filterByCategory('massage')" class="category-btn bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                        Massage
                    </button>
                    <button onclick="filterByCategory('facial')" class="category-btn bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                        Facial
                    </button>
                    <button onclick="filterByCategory('body_treatment')" class="category-btn bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                        Body Treatment
                    </button>
                    <button onclick="filterByCategory('wellness')" class="category-btn bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                        Wellness
                    </button>
                    <button onclick="filterByCategory('nail_care')" class="category-btn bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                        Nail Care
                    </button>
                    <button onclick="filterByCategory('hair_care')" class="category-btn bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                        Hair Care
                    </button>
                </div>
                            </div>

            <!-- Services Grid -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <h3 class="text-lg font-semibold text-gray-800">Spa Services</h3>
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center space-x-2">
                                <label class="text-sm text-gray-600">View:</label>
                                <button onclick="setViewMode('grid')" id="grid-view-btn" class="view-mode-btn bg-purple-600 text-white p-2 rounded-lg">
                                    <i class="fas fa-th"></i>
                                </button>
                                <button onclick="setViewMode('list')" id="list-view-btn" class="view-mode-btn bg-gray-200 text-gray-600 p-2 rounded-lg hover:bg-gray-300">
                                    <i class="fas fa-list"></i>
                                </button>
                            </div>
                            <select id="sort-services" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                <option value="name">Sort by Name</option>
                                <option value="price">Sort by Price</option>
                                <option value="duration">Sort by Duration</option>
                                <option value="popularity">Sort by Popularity</option>
                            </select>
                            </div>
                        </div>
                    </div>
                    
                <div id="services-container" class="p-6">
                    <!-- Grid View -->
                    <div id="grid-view" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <!-- Sample Spa Services -->
                        <div class="service-card bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                            <div class="relative">
                                <img src="https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=300&h=200&fit=crop" 
                                     alt="Swedish Massage" class="w-full h-48 object-cover">
                                <div class="absolute top-2 right-2">
                                    <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full">Active</span>
                                </div>
                                <div class="absolute bottom-2 left-2">
                                    <span class="bg-purple-600 text-white text-xs px-2 py-1 rounded-full">60 min</span>
                                </div>
                            </div>
                            <div class="p-4">
                                <h4 class="font-semibold text-gray-900 mb-1">Swedish Massage</h4>
                                <p class="text-sm text-gray-600 mb-2">Relaxing full-body massage with long strokes</p>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-lg font-bold text-purple-600">$120</span>
                                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">Massage</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Therapist: Sarah</span>
                                    <div class="flex items-center space-x-2">
                                        <button onclick="editService('1')" class="text-purple-600 hover:text-purple-800">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="toggleServiceStatus('1')" class="text-green-600 hover:text-green-800">
                                            <i class="fas fa-toggle-on"></i>
                                        </button>
                                        <button onclick="deleteService('1')" class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="service-card bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                            <div class="relative">
                                <img src="https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=300&h=200&fit=crop" 
                                     alt="Deep Tissue Massage" class="w-full h-48 object-cover">
                                <div class="absolute top-2 right-2">
                                    <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full">Active</span>
                                </div>
                                <div class="absolute bottom-2 left-2">
                                    <span class="bg-purple-600 text-white text-xs px-2 py-1 rounded-full">90 min</span>
                                </div>
                            </div>
                            <div class="p-4">
                                <h4 class="font-semibold text-gray-900 mb-1">Deep Tissue Massage</h4>
                                <p class="text-sm text-gray-600 mb-2">Targeted pressure to relieve muscle tension</p>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-lg font-bold text-purple-600">$150</span>
                                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">Massage</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Therapist: Mike</span>
                                    <div class="flex items-center space-x-2">
                                        <button onclick="editService('2')" class="text-purple-600 hover:text-purple-800">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="toggleServiceStatus('2')" class="text-green-600 hover:text-green-800">
                                            <i class="fas fa-toggle-on"></i>
                                        </button>
                                        <button onclick="deleteService('2')" class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                            </div>
                        </div>
                    </div>
                    
                        <div class="service-card bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                            <div class="relative">
                                <img src="https://images.unsplash.com/photo-1616394584738-fc6e612e71b9?w=300&h=200&fit=crop" 
                                     alt="Anti-Aging Facial" class="w-full h-48 object-cover">
                                <div class="absolute top-2 right-2">
                                    <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full">Active</span>
                                </div>
                                <div class="absolute bottom-2 left-2">
                                    <span class="bg-purple-600 text-white text-xs px-2 py-1 rounded-full">75 min</span>
                                </div>
                            </div>
                            <div class="p-4">
                                <h4 class="font-semibold text-gray-900 mb-1">Anti-Aging Facial</h4>
                                <p class="text-sm text-gray-600 mb-2">Rejuvenating facial treatment with premium products</p>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-lg font-bold text-purple-600">$180</span>
                                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">Facial</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Therapist: Lisa</span>
                                    <div class="flex items-center space-x-2">
                                        <button onclick="editService('3')" class="text-purple-600 hover:text-purple-800">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="toggleServiceStatus('3')" class="text-green-600 hover:text-green-800">
                                            <i class="fas fa-toggle-on"></i>
                                        </button>
                                        <button onclick="deleteService('3')" class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="service-card bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                            <div class="relative">
                                <img src="https://images.unsplash.com/photo-1596178060819-d4b8d0a8b4b9?w=300&h=200&fit=crop" 
                                     alt="Hot Stone Therapy" class="w-full h-48 object-cover">
                                <div class="absolute top-2 right-2">
                                    <span class="bg-yellow-500 text-white text-xs px-2 py-1 rounded-full">Limited</span>
                                </div>
                                <div class="absolute bottom-2 left-2">
                                    <span class="bg-purple-600 text-white text-xs px-2 py-1 rounded-full">90 min</span>
                    </div>
                            </div>
                            <div class="p-4">
                                <h4 class="font-semibold text-gray-900 mb-1">Hot Stone Therapy</h4>
                                <p class="text-sm text-gray-600 mb-2">Heated stones for deep muscle relaxation</p>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-lg font-bold text-purple-600">$160</span>
                                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">Body Treatment</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Therapist: Anna</span>
                                    <div class="flex items-center space-x-2">
                                        <button onclick="editService('4')" class="text-purple-600 hover:text-purple-800">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="toggleServiceStatus('4')" class="text-green-600 hover:text-green-800">
                                            <i class="fas fa-toggle-on"></i>
                                        </button>
                                        <button onclick="deleteService('4')" class="text-red-600 hover:text-red-800">
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
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Therapist</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <img class="h-10 w-10 rounded-lg object-cover" src="https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=100&h=100&fit=crop" alt="">
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">Swedish Massage</div>
                                                    <div class="text-sm text-gray-500">Relaxing full-body massage</div>
                </div>
                    </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-900">Massage</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            60 min
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-medium text-purple-600">$120</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            Sarah
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                Active
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="editService('1')" class="text-purple-600 hover:text-purple-900 mr-3">Edit</button>
                                            <button onclick="toggleServiceStatus('1')" class="text-green-600 hover:text-green-900 mr-3">Toggle</button>
                                            <button onclick="deleteService('1')" class="text-red-600 hover:text-red-900">Delete</button>
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
        // Spa module functionality
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

            // Initialize spa services functionality
            initializeSpaServices();
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });

        // Spa Services Management Functions
        function initializeSpaServices() {
            initializeFilters();
            initializeViewControls();
            loadServiceStatistics();
        }

        function initializeFilters() {
            const searchInput = document.getElementById('search-services');
            const categoryFilter = document.getElementById('category-filter');
            const statusFilter = document.getElementById('status-filter');
            const durationFilter = document.getElementById('duration-filter');
            const sortServices = document.getElementById('sort-services');

            if (searchInput) searchInput.addEventListener('input', applyFilters);
            if (categoryFilter) categoryFilter.addEventListener('change', applyFilters);
            if (statusFilter) statusFilter.addEventListener('change', applyFilters);
            if (durationFilter) durationFilter.addEventListener('change', applyFilters);
            if (sortServices) sortServices.addEventListener('change', sortServices);
        }

        function initializeViewControls() {
            setViewMode('grid'); // Default to grid view
        }

        function loadServiceStatistics() {
            // Simulate loading service statistics
            const stats = {
                total: Math.floor(Math.random() * 30) + 20,
                active: Math.floor(Math.random() * 25) + 18,
                bookings: Math.floor(Math.random() * 25) + 10,
                revenue: Math.floor(Math.random() * 5000) + 1000
            };

            updateStatisticsDisplay(stats);
        }

        function updateStatisticsDisplay(stats) {
            const totalServices = document.getElementById('total-services');
            const activeServices = document.getElementById('active-services');
            const bookingsToday = document.getElementById('bookings-today');
            const revenueToday = document.getElementById('revenue-today');

            if (totalServices) totalServices.textContent = stats.total;
            if (activeServices) activeServices.textContent = stats.active;
            if (bookingsToday) bookingsToday.textContent = stats.bookings;
            if (revenueToday) revenueToday.textContent = `$${stats.revenue.toLocaleString()}`;
        }

        function applyFilters() {
            const searchTerm = document.getElementById('search-services')?.value.toLowerCase() || '';
            const categoryFilter = document.getElementById('category-filter')?.value || '';
            const statusFilter = document.getElementById('status-filter')?.value || '';
            const durationFilter = document.getElementById('duration-filter')?.value || '';

            const serviceCards = document.querySelectorAll('.service-card');
            let visibleCount = 0;

            serviceCards.forEach(card => {
                const serviceName = card.querySelector('h4')?.textContent.toLowerCase() || '';
                const serviceDescription = card.querySelector('p')?.textContent.toLowerCase() || '';
                const serviceCategory = card.querySelector('.text-xs.text-gray-500')?.textContent.toLowerCase() || '';
                const serviceDuration = card.querySelector('.bg-purple-600')?.textContent.toLowerCase() || '';
                const serviceStatus = card.querySelector('.bg-green-500, .bg-yellow-500, .bg-red-500')?.textContent.toLowerCase() || '';

                const matchesSearch = !searchTerm || 
                    serviceName.includes(searchTerm) || 
                    serviceDescription.includes(searchTerm);

                const matchesCategory = !categoryFilter || serviceCategory.includes(categoryFilter);
                const matchesStatus = !statusFilter || serviceStatus.includes(statusFilter);
                const matchesDuration = !durationFilter || serviceDuration.includes(durationFilter);

                if (matchesSearch && matchesCategory && matchesStatus && matchesDuration) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            console.log(`Showing ${visibleCount} spa services`);
        }

        function filterByCategory(category) {
            const categoryFilter = document.getElementById('category-filter');
            if (categoryFilter) {
                categoryFilter.value = category;
            }

            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.classList.remove('bg-purple-600', 'text-white');
                btn.classList.add('bg-gray-200', 'text-gray-700');
            });

            const selectedBtn = event?.target;
            if (selectedBtn && selectedBtn.classList.contains('category-btn')) {
                selectedBtn.classList.remove('bg-gray-200', 'text-gray-700');
                selectedBtn.classList.add('bg-purple-600', 'text-white');
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
                gridBtn?.classList.add('bg-purple-600', 'text-white');
                listBtn?.classList.remove('bg-purple-600', 'text-white');
                listBtn?.classList.add('bg-gray-200', 'text-gray-600');
            } else {
                gridView?.classList.add('hidden');
                listView?.classList.remove('hidden');
                listBtn?.classList.remove('bg-gray-200', 'text-gray-600');
                listBtn?.classList.add('bg-purple-600', 'text-white');
                gridBtn?.classList.remove('bg-purple-600', 'text-white');
                gridBtn?.classList.add('bg-gray-200', 'text-gray-600');
            }
        }

        function sortServices() {
            const sortBy = document.getElementById('sort-services')?.value || 'name';
            const container = document.getElementById('grid-view');
            const cards = Array.from(container?.querySelectorAll('.service-card') || []);

            cards.sort((a, b) => {
                switch(sortBy) {
                    case 'name':
                        return a.querySelector('h4')?.textContent.localeCompare(b.querySelector('h4')?.textContent || '') || 0;
                    case 'price':
                        const priceA = parseFloat(a.querySelector('.text-lg.font-bold')?.textContent.replace('$', '') || '0');
                        const priceB = parseFloat(b.querySelector('.text-lg.font-bold')?.textContent.replace('$', '') || '0');
                        return priceA - priceB;
                    case 'duration':
                        const durationA = parseInt(a.querySelector('.bg-purple-600')?.textContent.replace(' min', '') || '0');
                        const durationB = parseInt(b.querySelector('.bg-purple-600')?.textContent.replace(' min', '') || '0');
                        return durationA - durationB;
                    case 'popularity':
                        return Math.random() - 0.5;
                    default:
                        return 0;
                }
            });

            cards.forEach(card => container?.appendChild(card));
        }

        // Service Management Actions
        function openAddServiceModal() {
            showNotification('Opening add service modal...', 'info');
            console.log('Opening add service modal');
        }

        function editService(serviceId) {
            showNotification(`Editing spa service ${serviceId}...`, 'info');
            console.log(`Editing spa service: ${serviceId}`);
        }

        function toggleServiceStatus(serviceId) {
            const serviceCard = document.querySelector(`[onclick*="${serviceId}"]`)?.closest('.service-card');
            const statusBadge = serviceCard?.querySelector('.bg-green-500, .bg-yellow-500, .bg-red-500');
            
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
                
                showNotification(`Spa service ${serviceId} status updated to ${newText}`, 'success');
                loadServiceStatistics(); // Update stats
            }
        }

        function deleteService(serviceId) {
            if (confirm(`Are you sure you want to delete spa service ${serviceId}?`)) {
                const serviceCard = document.querySelector(`[onclick*="${serviceId}"]`)?.closest('.service-card');
                if (serviceCard) {
                    serviceCard.style.transition = 'opacity 0.3s ease';
                    serviceCard.style.opacity = '0';
                    setTimeout(() => {
                        serviceCard.remove();
                        loadServiceStatistics(); // Update stats
                    }, 300);
                }
                showNotification(`Spa service ${serviceId} deleted successfully`, 'success');
            }
        }

        function exportServices() {
            showNotification('Exporting spa services data...', 'info');
            setTimeout(() => {
                showNotification('Spa services exported successfully!', 'success');
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
                    notification.classList.add('bg-purple-500');
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
            console.log('Adding new spa entry...');
        }

        function searchRecords() {
            console.log('Searching spa records...');
        }

        function viewAnalytics() {
            console.log('Viewing spa analytics...');
        }
    </script>

    <?php include '../includes/pos-footer.php'; ?>