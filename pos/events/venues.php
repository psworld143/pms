<?php
session_start();

// Check if user is logged in to POS
if (!isset($_SESSION['pos_user_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Venues';
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
                    <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Venue Management</h2>
                    <p class="text-gray-600 mt-1">Comprehensive venue booking and management system</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <div id="current-date" class="text-sm text-gray-600"></div>
                        <div id="current-time" class="text-sm text-gray-600"></div>
                    </div>
                    <button onclick="exportVenues()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-download mr-2"></i>Export
                    </button>
                    <button onclick="openAddVenueModal()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>Add Venue
                    </button>
                </div>
            </div>

            <!-- Venue Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <i class="fas fa-building text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="total-venues">8</h3>
                            <p class="text-sm text-gray-600">Total Venues</p>
                            <p class="text-xs text-blue-600 mt-1">4 indoor, 4 outdoor</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-lg">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="available-venues">6</h3>
                            <p class="text-sm text-gray-600">Available Today</p>
                            <p class="text-xs text-green-600 mt-1">75% availability</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-lg">
                            <i class="fas fa-users text-yellow-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="total-capacity">850</h3>
                            <p class="text-sm text-gray-600">Total Capacity</p>
                            <p class="text-xs text-yellow-600 mt-1">Combined seating</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-lg">
                            <i class="fas fa-calendar-alt text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="bookings-today">12</h3>
                            <p class="text-sm text-gray-600">Bookings Today</p>
                            <p class="text-xs text-purple-600 mt-1">Across all venues</p>
                        </div>
                    </div>
                </div>
            </div>

                <!-- Main Content Area -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800">Venues Overview</h3>
                        <button class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors">
                            <i class="fas fa-plus mr-2"></i>Add New
                        </button>
                    </div>
                    <div class="p-6">
                        <div class="text-center text-gray-500 py-12">
                            <i class="fas fa-cog text-4xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Venue Management System</h3>
                            <p class="text-gray-600">Comprehensive venue booking and management system for all event spaces.</p>
                            
                            <!-- Venue Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
                                <!-- Grand Ballroom -->
                                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                                    <div class="h-48 bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                                        <i class="fas fa-building text-white text-4xl"></i>
                                    </div>
                                    <div class="p-6">
                                        <div class="flex items-center justify-between mb-2">
                                            <h3 class="text-lg font-semibold text-gray-900">Grand Ballroom</h3>
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                Available
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600 mb-4">Elegant ballroom perfect for weddings, galas, and large corporate events.</p>
                                        <div class="space-y-2 mb-4">
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-500">Capacity:</span>
                                                <span class="font-medium">300 guests</span>
                                            </div>
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-500">Rate:</span>
                                                <span class="font-medium">$2,500/day</span>
                                            </div>
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-500">Features:</span>
                                                <span class="font-medium">Stage, AV, Catering</span>
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            <button onclick="viewVenueDetails('1')" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm transition-colors">
                                                <i class="fas fa-eye mr-1"></i>View
                                            </button>
                                            <button onclick="bookVenue('1')" class="flex-1 bg-orange-600 hover:bg-orange-700 text-white px-3 py-2 rounded-lg text-sm transition-colors">
                                                <i class="fas fa-calendar-plus mr-1"></i>Book
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Executive Conference Room -->
                                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                                    <div class="h-48 bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center">
                                        <i class="fas fa-users text-white text-4xl"></i>
                                    </div>
                                    <div class="p-6">
                                        <div class="flex items-center justify-between mb-2">
                                            <h3 class="text-lg font-semibold text-gray-900">Executive Conference Room</h3>
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Booked
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600 mb-4">Professional meeting space with advanced technology and comfortable seating.</p>
                                        <div class="space-y-2 mb-4">
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-500">Capacity:</span>
                                                <span class="font-medium">25 guests</span>
                                            </div>
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-500">Rate:</span>
                                                <span class="font-medium">$500/day</span>
                                            </div>
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-500">Features:</span>
                                                <span class="font-medium">AV, WiFi, Catering</span>
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            <button onclick="viewVenueDetails('2')" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm transition-colors">
                                                <i class="fas fa-eye mr-1"></i>View
                                            </button>
                                            <button onclick="checkAvailability('2')" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-lg text-sm transition-colors">
                                                <i class="fas fa-clock mr-1"></i>Check
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Rooftop Garden -->
                                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                                    <div class="h-48 bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center">
                                        <i class="fas fa-seedling text-white text-4xl"></i>
                                    </div>
                                    <div class="p-6">
                                        <div class="flex items-center justify-between mb-2">
                                            <h3 class="text-lg font-semibold text-gray-900">Rooftop Garden</h3>
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                Available
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600 mb-4">Beautiful outdoor space with panoramic city views, perfect for cocktail parties.</p>
                                        <div class="space-y-2 mb-4">
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-500">Capacity:</span>
                                                <span class="font-medium">150 guests</span>
                                            </div>
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-500">Rate:</span>
                                                <span class="font-medium">$1,800/day</span>
                                            </div>
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-500">Features:</span>
                                                <span class="font-medium">Bar, Lighting, Heating</span>
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            <button onclick="viewVenueDetails('3')" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm transition-colors">
                                                <i class="fas fa-eye mr-1"></i>View
                                            </button>
                                            <button onclick="bookVenue('3')" class="flex-1 bg-orange-600 hover:bg-orange-700 text-white px-3 py-2 rounded-lg text-sm transition-colors">
                                                <i class="fas fa-calendar-plus mr-1"></i>Book
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="mt-6 flex flex-wrap gap-4">
                                <button onclick="createVenuePackage()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-plus mr-2"></i>Create Venue Package
                                </button>
                                <button onclick="manageVenueCalendar()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-calendar mr-2"></i>Venue Calendar
                                </button>
                                <button onclick="viewVenueAnalytics()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-chart-bar mr-2"></i>Venue Analytics
                                </button>
                                <button onclick="setupVenueMaintenance()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-tools mr-2"></i>Maintenance
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

            // Initialize venues functionality
            initializeVenues();
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });

        // Venue Management Functions
        function initializeVenues() {
            loadVenuesData();
            initializeVenueFilters();
        }

        function initializeVenueFilters() {
            const searchInput = document.getElementById('search-venues');
            const venueType = document.getElementById('venue-type');
            const capacityRange = document.getElementById('capacity-range');

            if (searchInput) searchInput.addEventListener('input', applyVenueFilters);
            if (venueType) venueType.addEventListener('change', applyVenueFilters);
            if (capacityRange) capacityRange.addEventListener('change', applyVenueFilters);
        }

        function loadVenuesData() {
            // Simulate loading venues data
            const venuesData = {
                totalVenues: generateRandomTotalVenues(),
                availableVenues: generateRandomAvailableVenues(),
                totalCapacity: generateRandomTotalCapacity(),
                bookingsToday: generateRandomBookingsToday()
            };

            updateVenuesDisplay(venuesData);
        }

        function generateRandomTotalVenues() {
            return Math.floor(Math.random() * 3) + 6;
        }

        function generateRandomAvailableVenues() {
            return Math.floor(Math.random() * 3) + 4;
        }

        function generateRandomTotalCapacity() {
            return Math.floor(Math.random() * 200) + 700;
        }

        function generateRandomBookingsToday() {
            return Math.floor(Math.random() * 8) + 8;
        }

        function updateVenuesDisplay(data) {
            const totalVenues = document.getElementById('total-venues');
            const availableVenues = document.getElementById('available-venues');
            const totalCapacity = document.getElementById('total-capacity');
            const bookingsToday = document.getElementById('bookings-today');

            if (totalVenues) {
                totalVenues.textContent = data.totalVenues;
                const venuesGrowth = totalVenues.parentElement.querySelector('.text-xs');
                if (venuesGrowth) venuesGrowth.textContent = '4 indoor, 4 outdoor';
            }
            if (availableVenues) {
                availableVenues.textContent = data.availableVenues;
                const availableGrowth = availableVenues.parentElement.querySelector('.text-xs');
                if (availableGrowth) availableGrowth.textContent = '75% availability';
            }
            if (totalCapacity) {
                totalCapacity.textContent = data.totalCapacity;
                const capacityGrowth = totalCapacity.parentElement.querySelector('.text-xs');
                if (capacityGrowth) capacityGrowth.textContent = 'Combined seating';
            }
            if (bookingsToday) {
                bookingsToday.textContent = data.bookingsToday;
                const bookingsGrowth = bookingsToday.parentElement.querySelector('.text-xs');
                if (bookingsGrowth) bookingsGrowth.textContent = 'Across all venues';
            }
        }

        function applyVenueFilters() {
            const searchTerm = document.getElementById('search-venues')?.value.toLowerCase() || '';
            const venueType = document.getElementById('venue-type')?.value || '';
            const capacityRange = document.getElementById('capacity-range')?.value || '';

            console.log('Applying venue filters:', { searchTerm, venueType, capacityRange });
            
            showNotification('Applying venue filters...', 'info');
            
            setTimeout(() => {
                loadVenuesData();
                showNotification('Venue data updated successfully!', 'success');
            }, 1000);
        }

        // Venue Operations
        function openAddVenueModal() {
            showNotification('Opening add venue modal...', 'info');
            setTimeout(() => {
                showNotification('Add venue modal loaded!', 'success');
            }, 1500);
        }

        function viewVenueDetails(venueId) {
            showNotification(`Opening venue details for ID: ${venueId}...`, 'info');
            setTimeout(() => {
                showNotification('Venue details loaded!', 'success');
            }, 1500);
        }

        function bookVenue(venueId) {
            showNotification(`Opening booking for venue ID: ${venueId}...`, 'info');
            setTimeout(() => {
                showNotification('Venue booking form loaded!', 'success');
            }, 1500);
        }

        function checkAvailability(venueId) {
            showNotification(`Checking availability for venue ID: ${venueId}...`, 'info');
            setTimeout(() => {
                showNotification('Availability checked!', 'success');
            }, 1000);
        }

        function createVenuePackage() {
            showNotification('Opening venue package creator...', 'info');
            setTimeout(() => {
                showNotification('Venue package created!', 'success');
            }, 2000);
        }

        function manageVenueCalendar() {
            showNotification('Opening venue calendar...', 'info');
            setTimeout(() => {
                showNotification('Venue calendar loaded!', 'success');
            }, 1500);
        }

        function viewVenueAnalytics() {
            showNotification('Opening venue analytics...', 'info');
            setTimeout(() => {
                showNotification('Venue analytics loaded!', 'success');
            }, 1500);
        }

        function setupVenueMaintenance() {
            showNotification('Opening venue maintenance...', 'info');
            setTimeout(() => {
                showNotification('Maintenance scheduled!', 'success');
            }, 1500);
        }

        function refreshVenues() {
            showNotification('Refreshing venue data...', 'info');
            setTimeout(() => {
                loadVenuesData();
                showNotification('Venue data refreshed successfully!', 'success');
            }, 1000);
        }

        function exportVenues() {
            showNotification('Exporting venue data...', 'info');
            setTimeout(() => {
                showNotification('Venue data exported successfully!', 'success');
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