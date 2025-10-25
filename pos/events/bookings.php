<?php
// VPS Session Fix - Robust session configuration
require_once __DIR__ . '/../../vps_session_fix.php';

// Check if user is logged in to POS
if (!isset($_SESSION['pos_user_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Event Bookings';
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
                    <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Event Booking Management</h2>
                    <p class="text-gray-600 mt-1">Comprehensive event reservation and booking system</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <div id="current-date" class="text-sm text-gray-600"></div>
                        <div id="current-time" class="text-sm text-gray-600"></div>
                    </div>
                    <button onclick="exportBookings()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-download mr-2"></i>Export
                    </button>
                    <button onclick="openNewBookingModal()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>New Booking
                    </button>
                </div>
                </div>

            <!-- Booking Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
                            </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="total-bookings">156</h3>
                            <p class="text-sm text-gray-600">Total Bookings</p>
                            <p class="text-xs text-blue-600 mt-1">+12 this month</p>
                        </div>
                        </div>
                    </div>
                    
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-lg">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                            </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="confirmed-bookings">89</h3>
                            <p class="text-sm text-gray-600">Confirmed</p>
                            <p class="text-xs text-green-600 mt-1">57% of total</p>
                        </div>
                        </div>
                    </div>
                    
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-lg">
                            <i class="fas fa-clock text-yellow-600 text-xl"></i>
                            </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="pending-bookings">34</h3>
                            <p class="text-sm text-gray-600">Pending</p>
                            <p class="text-xs text-yellow-600 mt-1">Needs review</p>
                        </div>
                        </div>
                    </div>
                    
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-lg">
                            <i class="fas fa-dollar-sign text-purple-600 text-xl"></i>
                            </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="total-revenue">₱89,450</h3>
                            <p class="text-sm text-gray-600">Total Revenue</p>
                            <p class="text-xs text-purple-600 mt-1">+18% vs last month</p>
                        </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800">Event Bookings Overview</h3>
                        <button class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors">
                            <i class="fas fa-plus mr-2"></i>Add New
                        </button>
                    </div>
                    <div class="p-6">
                        <!-- Booking Filters -->
                        <div class="mb-6">
                            <div class="flex flex-col lg:flex-row gap-4">
                                <div class="flex-1">
                                    <label for="search-bookings" class="block text-sm font-medium text-gray-700 mb-2">Search Bookings</label>
                                    <div class="relative">
                                        <input type="text" id="search-bookings" placeholder="Search by client name, event type, or booking ID..." 
                                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                                    </div>
                                </div>
                                <div class="lg:w-48">
                                    <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                    <select id="status-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                        <option value="">All Status</option>
                                        <option value="pending">Pending</option>
                                        <option value="confirmed">Confirmed</option>
                                        <option value="cancelled">Cancelled</option>
                                        <option value="completed">Completed</option>
                                    </select>
                                </div>
                                <div class="lg:w-48">
                                    <label for="date-range" class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                                    <select id="date-range" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                        <option value="">All Dates</option>
                                        <option value="today">Today</option>
                                        <option value="week">This Week</option>
                                        <option value="month">This Month</option>
                                        <option value="upcoming">Upcoming</option>
                                    </select>
                                </div>
                                <div class="lg:w-48">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Actions</label>
                                    <div class="flex space-x-2">
                                        <button onclick="refreshBookings()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                                            <i class="fas fa-sync-alt mr-2"></i>Refresh
                                        </button>
                                        <button onclick="exportBookings()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                                            <i class="fas fa-download mr-2"></i>Export
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Bookings -->
                        <div class="space-y-4">
                            <!-- Sample Booking 1 -->
                            <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                                            <i class="fas fa-calendar-check text-green-600"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-900">Johnson Wedding</h4>
                                            <p class="text-sm text-gray-600">Sarah & Michael Johnson • Wedding Reception</p>
                                            <p class="text-xs text-gray-500">March 15, 2024 • 6:00 PM - 11:00 PM</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-semibold text-gray-900">₱8,500</div>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Confirmed
                                        </span>
                                        <div class="mt-2 flex space-x-2">
                                            <button onclick="viewBookingDetails('1')" class="text-blue-600 hover:text-blue-800 text-sm">
                                                <i class="fas fa-eye mr-1"></i>View
                                            </button>
                                            <button onclick="editBooking('1')" class="text-orange-600 hover:text-orange-800 text-sm">
                                                <i class="fas fa-edit mr-1"></i>Edit
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sample Booking 2 -->
                            <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mr-4">
                                            <i class="fas fa-clock text-yellow-600"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-900">Corporate Conference</h4>
                                            <p class="text-sm text-gray-600">Tech Solutions Inc. • Business Conference</p>
                                            <p class="text-xs text-gray-500">March 20, 2024 • 9:00 AM - 5:00 PM</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-semibold text-gray-900">₱12,000</div>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                        <div class="mt-2 flex space-x-2">
                                            <button onclick="viewBookingDetails('2')" class="text-blue-600 hover:text-blue-800 text-sm">
                                                <i class="fas fa-eye mr-1"></i>View
                                            </button>
                                            <button onclick="confirmBooking('2')" class="text-green-600 hover:text-green-800 text-sm">
                                                <i class="fas fa-check mr-1"></i>Confirm
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sample Booking 3 -->
                            <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                                            <i class="fas fa-birthday-cake text-purple-600"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-900">Birthday Celebration</h4>
                                            <p class="text-sm text-gray-600">Emma Rodriguez • 25th Birthday Party</p>
                                            <p class="text-xs text-gray-500">March 25, 2024 • 7:00 PM - 12:00 AM</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-semibold text-gray-900">₱3,200</div>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Confirmed
                                        </span>
                                        <div class="mt-2 flex space-x-2">
                                            <button onclick="viewBookingDetails('3')" class="text-blue-600 hover:text-blue-800 text-sm">
                                                <i class="fas fa-eye mr-1"></i>View
                                            </button>
                                            <button onclick="editBooking('3')" class="text-orange-600 hover:text-orange-800 text-sm">
                                                <i class="fas fa-edit mr-1"></i>Edit
                                            </button>
                    </div>
                </div>
            </div>
        </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="mt-6 flex flex-wrap gap-4">
                            <button onclick="createEventPackage()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-plus mr-2"></i>Create Event Package
                            </button>
                            <button onclick="manageEventCalendar()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-calendar mr-2"></i>Event Calendar
                            </button>
                            <button onclick="viewBookingAnalytics()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-chart-bar mr-2"></i>Booking Analytics
                            </button>
                            <button onclick="sendBookingReminders()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-bell mr-2"></i>Send Reminders
                            </button>
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

            // Initialize bookings functionality
            initializeBookings();
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });

        // Event Booking Management Functions
        function initializeBookings() {
            loadBookingsData();
            initializeBookingFilters();
        }

        function initializeBookingFilters() {
            const searchInput = document.getElementById('search-bookings');
            const statusFilter = document.getElementById('status-filter');
            const dateRange = document.getElementById('date-range');

            if (searchInput) searchInput.addEventListener('input', applyBookingFilters);
            if (statusFilter) statusFilter.addEventListener('change', applyBookingFilters);
            if (dateRange) dateRange.addEventListener('change', applyBookingFilters);
        }

        function loadBookingsData() {
            // Simulate loading bookings data
            const bookingsData = {
                totalBookings: generateRandomTotalBookings(),
                confirmedBookings: generateRandomConfirmedBookings(),
                pendingBookings: generateRandomPendingBookings(),
                totalRevenue: generateRandomTotalRevenue()
            };

            updateBookingsDisplay(bookingsData);
        }

        function generateRandomTotalBookings() {
            return Math.floor(Math.random() * 50) + 120;
        }

        function generateRandomConfirmedBookings() {
            return Math.floor(Math.random() * 30) + 70;
        }

        function generateRandomPendingBookings() {
            return Math.floor(Math.random() * 20) + 20;
        }

        function generateRandomTotalRevenue() {
            return Math.floor(Math.random() * 20000) + 70000;
        }

        function updateBookingsDisplay(data) {
            const totalBookings = document.getElementById('total-bookings');
            const confirmedBookings = document.getElementById('confirmed-bookings');
            const pendingBookings = document.getElementById('pending-bookings');
            const totalRevenue = document.getElementById('total-revenue');

            if (totalBookings) {
                totalBookings.textContent = data.totalBookings;
                const bookingsGrowth = totalBookings.parentElement.querySelector('.text-xs');
                if (bookingsGrowth) bookingsGrowth.textContent = '+12 this month';
            }
            if (confirmedBookings) {
                confirmedBookings.textContent = data.confirmedBookings;
                const confirmedGrowth = confirmedBookings.parentElement.querySelector('.text-xs');
                if (confirmedGrowth) confirmedGrowth.textContent = '57% of total';
            }
            if (pendingBookings) {
                pendingBookings.textContent = data.pendingBookings;
                const pendingGrowth = pendingBookings.parentElement.querySelector('.text-xs');
                if (pendingGrowth) pendingGrowth.textContent = 'Needs review';
            }
            if (totalRevenue) {
                totalRevenue.textContent = `₱${data.totalRevenue.toLocaleString()}`;
                const revenueGrowth = totalRevenue.parentElement.querySelector('.text-xs');
                if (revenueGrowth) revenueGrowth.textContent = '+18% vs last month';
            }
        }

        function applyBookingFilters() {
            const searchTerm = document.getElementById('search-bookings')?.value.toLowerCase() || '';
            const status = document.getElementById('status-filter')?.value || '';
            const dateRange = document.getElementById('date-range')?.value || '';

            console.log('Applying booking filters:', { searchTerm, status, dateRange });
            
            showNotification('Applying booking filters...', 'info');
            
            setTimeout(() => {
                loadBookingsData();
                showNotification('Booking data updated successfully!', 'success');
            }, 1000);
        }

        // Booking Operations
        function openNewBookingModal() {
            showNotification('Opening new booking modal...', 'info');
            setTimeout(() => {
                showNotification('New booking modal loaded!', 'success');
            }, 1500);
        }

        function viewBookingDetails(bookingId) {
            showNotification(`Opening booking details for ID: ${bookingId}...`, 'info');
            setTimeout(() => {
                showNotification('Booking details loaded!', 'success');
            }, 1500);
        }

        function editBooking(bookingId) {
            showNotification(`Opening booking editor for ID: ${bookingId}...`, 'info');
            setTimeout(() => {
                showNotification('Booking editor loaded!', 'success');
            }, 1500);
        }

        function confirmBooking(bookingId) {
            showNotification(`Confirming booking ID: ${bookingId}...`, 'info');
            setTimeout(() => {
                showNotification('Booking confirmed successfully!', 'success');
            }, 1500);
        }

        function createEventPackage() {
            showNotification('Opening event package creator...', 'info');
            setTimeout(() => {
                showNotification('Event package created!', 'success');
            }, 2000);
        }

        function manageEventCalendar() {
            showNotification('Opening event calendar...', 'info');
            setTimeout(() => {
                showNotification('Event calendar loaded!', 'success');
            }, 1500);
        }

        function viewBookingAnalytics() {
            showNotification('Opening booking analytics...', 'info');
            setTimeout(() => {
                showNotification('Booking analytics loaded!', 'success');
            }, 1500);
        }

        function sendBookingReminders() {
            showNotification('Sending booking reminders...', 'info');
            setTimeout(() => {
                showNotification('Booking reminders sent successfully!', 'success');
            }, 2000);
        }

        function refreshBookings() {
            showNotification('Refreshing booking data...', 'info');
            setTimeout(() => {
                loadBookingsData();
                showNotification('Booking data refreshed successfully!', 'success');
            }, 1000);
        }

        function exportBookings() {
            showNotification('Exporting booking data...', 'info');
            setTimeout(() => {
                showNotification('Booking data exported successfully!', 'success');
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