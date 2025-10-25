<?php
// VPS Session Fix - Robust session configuration
require_once __DIR__ . '/../../vps_session_fix.php';

// Check if user is logged in to POS
if (!isset($_SESSION['pos_user_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Event Services';
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
                    <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Event Services Management</h2>
                    <p class="text-gray-600 mt-1">Comprehensive event services catalog and booking system</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <div id="current-date" class="text-sm text-gray-600"></div>
                        <div id="current-time" class="text-sm text-gray-600"></div>
                    </div>
                    <button onclick="exportServices()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-download mr-2"></i>Export
                    </button>
                    <button onclick="openAddServiceModal()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>Add Service
                    </button>
                </div>
                </div>

            <!-- Service Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-lg">
                            <i class="fas fa-champagne-glasses text-purple-600 text-xl"></i>
                            </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="total-services">24</h3>
                            <p class="text-sm text-gray-600">Total Services</p>
                            <p class="text-xs text-purple-600 mt-1">8 categories</p>
                        </div>
                        </div>
                    </div>
                    
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-lg">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                            </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="active-services">18</h3>
                            <p class="text-sm text-gray-600">Active Services</p>
                            <p class="text-xs text-green-600 mt-1">75% availability</p>
                        </div>
                        </div>
                    </div>
                    
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
                            </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="bookings-today">12</h3>
                            <p class="text-sm text-gray-600">Today's Bookings</p>
                            <p class="text-xs text-blue-600 mt-1">+3 vs yesterday</p>
                        </div>
                        </div>
                    </div>
                    
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-lg">
                            <i class="fas fa-dollar-sign text-yellow-600 text-xl"></i>
                            </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="avg-service-price">₱450</h3>
                            <p class="text-sm text-gray-600">Avg Service Price</p>
                            <p class="text-xs text-yellow-600 mt-1">₱50 - $2,500 range</p>
                        </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800">Event Services Overview</h3>
                        <button class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors">
                            <i class="fas fa-plus mr-2"></i>Add New
                        </button>
                    </div>
                    <div class="p-6">
                        <div class="text-center text-gray-500 py-12">
                            <i class="fas fa-cog text-4xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Event Services Management</h3>
                            <p class="text-gray-600">Comprehensive event services catalog and booking system.</p>
                            
                            <!-- Service Categories -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
                                <!-- Catering Services -->
                                <div class="bg-white border border-gray-200 rounded-lg p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-utensils text-orange-600"></i>
                                            </div>
                                            <h3 class="text-lg font-semibold text-gray-800">Catering Services</h3>
                                        </div>
                                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">6 Active</span>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600">Wedding Catering</span>
                                            <span class="text-sm font-semibold text-gray-900">₱1,200</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600">Corporate Events</span>
                                            <span class="text-sm font-semibold text-gray-900">₱800</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600">Cocktail Reception</span>
                                            <span class="text-sm font-semibold text-gray-900">₱600</span>
                                        </div>
                                    </div>
                                    <button onclick="viewCateringServices()" class="w-full mt-4 bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                                        View All Catering
                                    </button>
                                </div>

                                <!-- Audio Visual Services -->
                                <div class="bg-white border border-gray-200 rounded-lg p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-video text-blue-600"></i>
                                            </div>
                                            <h3 class="text-lg font-semibold text-gray-800">Audio Visual</h3>
                                        </div>
                                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">4 Active</span>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600">Sound System</span>
                                            <span class="text-sm font-semibold text-gray-900">₱300</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600">Projector Setup</span>
                                            <span class="text-sm font-semibold text-gray-900">₱200</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600">LED Lighting</span>
                                            <span class="text-sm font-semibold text-gray-900">₱450</span>
                                        </div>
                                    </div>
                                    <button onclick="viewAVServices()" class="w-full mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                                        View All AV
                                    </button>
                                </div>

                                <!-- Entertainment Services -->
                                <div class="bg-white border border-gray-200 rounded-lg p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-music text-purple-600"></i>
                                            </div>
                                            <h3 class="text-lg font-semibold text-gray-800">Entertainment</h3>
                                        </div>
                                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">5 Active</span>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600">Live Band</span>
                                            <span class="text-sm font-semibold text-gray-900">₱1,500</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600">DJ Services</span>
                                            <span class="text-sm font-semibold text-gray-900">₱800</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600">Magician</span>
                                            <span class="text-sm font-semibold text-gray-900">₱400</span>
                                        </div>
                                    </div>
                                    <button onclick="viewEntertainmentServices()" class="w-full mt-4 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                                        View All Entertainment
                                    </button>
                                </div>

                                <!-- Photography Services -->
                                <div class="bg-white border border-gray-200 rounded-lg p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-pink-100 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-camera text-pink-600"></i>
                                            </div>
                                            <h3 class="text-lg font-semibold text-gray-800">Photography</h3>
                                        </div>
                                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">3 Active</span>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600">Event Photography</span>
                                            <span class="text-sm font-semibold text-gray-900">₱600</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600">Video Recording</span>
                                            <span class="text-sm font-semibold text-gray-900">₱800</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600">Photo Booth</span>
                                            <span class="text-sm font-semibold text-gray-900">₱350</span>
                                        </div>
                                    </div>
                                    <button onclick="viewPhotographyServices()" class="w-full mt-4 bg-pink-600 hover:bg-pink-700 text-white px-4 py-2 rounded-lg transition-colors">
                                        View All Photography
                                    </button>
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="mt-6 flex flex-wrap gap-4">
                                <button onclick="createServicePackage()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-plus mr-2"></i>Create Package
                                </button>
                                <button onclick="manageServicePricing()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-tags mr-2"></i>Manage Pricing
                                </button>
                                <button onclick="viewServiceAnalytics()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-chart-bar mr-2"></i>Service Analytics
                                </button>
                                <button onclick="importServices()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-upload mr-2"></i>Import Services
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

            // Initialize services functionality
            initializeServices();
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });

        // Event Services Management Functions
        function initializeServices() {
            loadServicesData();
            initializeServiceFilters();
        }

        function initializeServiceFilters() {
            const searchInput = document.getElementById('search-services');
            const categoryFilter = document.getElementById('category-filter');
            const statusFilter = document.getElementById('status-filter');

            if (searchInput) searchInput.addEventListener('input', applyServiceFilters);
            if (categoryFilter) categoryFilter.addEventListener('change', applyServiceFilters);
            if (statusFilter) statusFilter.addEventListener('change', applyServiceFilters);
        }

        function loadServicesData() {
            // Simulate loading services data
            const servicesData = {
                totalServices: generateRandomTotalServices(),
                activeServices: generateRandomActiveServices(),
                bookingsToday: generateRandomBookingsToday(),
                avgServicePrice: generateRandomAvgServicePrice()
            };

            updateServicesDisplay(servicesData);
        }

        function generateRandomTotalServices() {
            return Math.floor(Math.random() * 10) + 20;
        }

        function generateRandomActiveServices() {
            return Math.floor(Math.random() * 8) + 15;
        }

        function generateRandomBookingsToday() {
            return Math.floor(Math.random() * 15) + 8;
        }

        function generateRandomAvgServicePrice() {
            return Math.floor(Math.random() * 200) + 400;
        }

        function updateServicesDisplay(data) {
            const totalServices = document.getElementById('total-services');
            const activeServices = document.getElementById('active-services');
            const bookingsToday = document.getElementById('bookings-today');
            const avgServicePrice = document.getElementById('avg-service-price');

            if (totalServices) {
                totalServices.textContent = data.totalServices;
                const servicesGrowth = totalServices.parentElement.querySelector('.text-xs');
                if (servicesGrowth) servicesGrowth.textContent = '8 categories';
            }
            if (activeServices) {
                activeServices.textContent = data.activeServices;
                const activeGrowth = activeServices.parentElement.querySelector('.text-xs');
                if (activeGrowth) activeGrowth.textContent = '75% availability';
            }
            if (bookingsToday) {
                bookingsToday.textContent = data.bookingsToday;
                const bookingsGrowth = bookingsToday.parentElement.querySelector('.text-xs');
                if (bookingsGrowth) bookingsGrowth.textContent = '+3 vs yesterday';
            }
            if (avgServicePrice) {
                avgServicePrice.textContent = `₱${data.avgServicePrice}`;
                const priceGrowth = avgServicePrice.parentElement.querySelector('.text-xs');
                if (priceGrowth) priceGrowth.textContent = '$50 - $2,500 range';
            }
        }

        function applyServiceFilters() {
            const searchTerm = document.getElementById('search-services')?.value.toLowerCase() || '';
            const category = document.getElementById('category-filter')?.value || '';
            const status = document.getElementById('status-filter')?.value || '';

            console.log('Applying service filters:', { searchTerm, category, status });
            
            showNotification('Applying service filters...', 'info');
            
            setTimeout(() => {
                loadServicesData();
                showNotification('Service data updated successfully!', 'success');
            }, 1000);
        }

        // Service Operations
        function openAddServiceModal() {
            showNotification('Opening add service modal...', 'info');
            console.log('Opening add service modal');
        }

        function viewCateringServices() {
            showNotification('Opening catering services...', 'info');
            setTimeout(() => {
                showNotification('Catering services loaded!', 'success');
            }, 1500);
        }

        function viewAVServices() {
            showNotification('Opening audio visual services...', 'info');
            setTimeout(() => {
                showNotification('AV services loaded!', 'success');
            }, 1500);
        }

        function viewEntertainmentServices() {
            showNotification('Opening entertainment services...', 'info');
            setTimeout(() => {
                showNotification('Entertainment services loaded!', 'success');
            }, 1500);
        }

        function viewPhotographyServices() {
            showNotification('Opening photography services...', 'info');
            setTimeout(() => {
                showNotification('Photography services loaded!', 'success');
            }, 1500);
        }

        function createServicePackage() {
            showNotification('Opening service package creator...', 'info');
            setTimeout(() => {
                showNotification('Service package created!', 'success');
            }, 2000);
        }

        function manageServicePricing() {
            showNotification('Opening service pricing management...', 'info');
            setTimeout(() => {
                showNotification('Pricing updated!', 'success');
            }, 1500);
        }

        function viewServiceAnalytics() {
            showNotification('Opening service analytics...', 'info');
            setTimeout(() => {
                showNotification('Service analytics loaded!', 'success');
            }, 1500);
        }

        function importServices() {
            showNotification('Opening service import...', 'info');
            setTimeout(() => {
                showNotification('Services imported successfully!', 'success');
            }, 2000);
        }

        function refreshServices() {
            showNotification('Refreshing service data...', 'info');
            setTimeout(() => {
                loadServicesData();
                showNotification('Service data refreshed successfully!', 'success');
            }, 1000);
        }

        function exportServices() {
            showNotification('Exporting service data...', 'info');
            setTimeout(() => {
                showNotification('Service data exported successfully!', 'success');
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
            console.log('Adding new events entry...');
        }

        function searchRecords() {
            console.log('Searching events records...');
        }

        function viewAnalytics() {
            console.log('Viewing events analytics...');
        }
    </script>

    <?php include '../includes/pos-footer.php'; ?>
</body>
</html>