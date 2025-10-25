<?php
session_start();

// Check if user is logged in to POS
if (!isset($_SESSION['pos_user_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Event Reports';
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
                    <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Event Reports & Analytics</h2>
                    <p class="text-gray-600 mt-1">Comprehensive event business intelligence and performance analytics</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <div id="current-date" class="text-sm text-gray-600"></div>
                        <div id="current-time" class="text-sm text-gray-600"></div>
                    </div>
                    <button onclick="exportAllReports()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-download mr-2"></i>Export All
                    </button>
                    <button onclick="generateCustomReport()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-chart-line mr-2"></i>Custom Report
                    </button>
                </div>
                </div>

            <!-- Event Analytics KPIs -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
                            </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="total-events">89</h3>
                            <p class="text-sm text-gray-600">Total Events</p>
                            <p class="text-xs text-blue-600 mt-1">+15 this month</p>
                        </div>
                        </div>
                    </div>
                    
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-lg">
                            <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                            </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="total-revenue">$156,420</h3>
                            <p class="text-sm text-gray-600">Total Revenue</p>
                            <p class="text-xs text-green-600 mt-1">+22% vs last month</p>
                        </div>
                        </div>
                    </div>
                    
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-lg">
                            <i class="fas fa-users text-purple-600 text-xl"></i>
                            </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="total-guests">4,250</h3>
                            <p class="text-sm text-gray-600">Total Guests</p>
                            <p class="text-xs text-purple-600 mt-1">+18% vs last month</p>
                        </div>
                        </div>
                    </div>
                    
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-lg">
                            <i class="fas fa-star text-yellow-600 text-xl"></i>
                            </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="avg-rating">4.8</h3>
                            <p class="text-sm text-gray-600">Avg Rating</p>
                            <p class="text-xs text-yellow-600 mt-1">Out of 5.0</p>
                        </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800">Event Reports Overview</h3>
                        <button class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors">
                            <i class="fas fa-plus mr-2"></i>Add New
                        </button>
                    </div>
                    <div class="p-6">
                        <div class="text-center text-gray-500 py-12">
                            <i class="fas fa-cog text-4xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Event Reports & Analytics</h3>
                            <p class="text-gray-600">Comprehensive event business intelligence and performance analytics system.</p>
                            
                            <!-- Event Performance Summary -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                                <!-- Event Types Performance -->
                                <div class="bg-white border border-gray-200 rounded-lg p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-lg font-semibold text-gray-800">Event Types Performance</h3>
                                        <button onclick="viewEventTypeReport()" class="text-orange-600 hover:text-orange-800 text-sm">
                                            View Full Report
                                        </button>
                                    </div>
                                    <div class="space-y-4">
                                        <div class="flex justify-between items-center">
                                            <div class="flex items-center">
                                                <div class="w-4 h-4 bg-blue-500 rounded mr-3"></div>
                                                <span class="text-sm text-gray-600">Weddings</span>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-sm font-semibold text-gray-900">$45,200</div>
                                                <div class="text-xs text-gray-500">28 events</div>
                                            </div>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <div class="flex items-center">
                                                <div class="w-4 h-4 bg-green-500 rounded mr-3"></div>
                                                <span class="text-sm text-gray-600">Corporate Events</span>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-sm font-semibold text-gray-900">$38,600</div>
                                                <div class="text-xs text-gray-500">22 events</div>
                                            </div>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <div class="flex items-center">
                                                <div class="w-4 h-4 bg-purple-500 rounded mr-3"></div>
                                                <span class="text-sm text-gray-600">Birthday Parties</span>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-sm font-semibold text-gray-900">$28,400</div>
                                                <div class="text-xs text-gray-500">18 events</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Top Performing Venues -->
                                <div class="bg-white border border-gray-200 rounded-lg p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-lg font-semibold text-gray-800">Top Performing Venues</h3>
                                        <button onclick="viewVenueReport()" class="text-orange-600 hover:text-orange-800 text-sm">
                                            View Full Report
                                        </button>
                                    </div>
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <div class="w-6 h-6 bg-orange-100 rounded-full flex items-center justify-center mr-3">
                                                    <span class="text-xs text-orange-600 font-semibold">1</span>
                                                </div>
                                                <span class="text-sm font-medium text-gray-900">Grand Ballroom</span>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-sm font-semibold text-gray-900">$52,500</div>
                                                <div class="text-xs text-gray-500">21 events</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                                    <span class="text-xs text-blue-600 font-semibold">2</span>
                                                </div>
                                                <span class="text-sm font-medium text-gray-900">Rooftop Garden</span>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-sm font-semibold text-gray-900">$38,200</div>
                                                <div class="text-xs text-gray-500">17 events</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                                    <span class="text-xs text-green-600 font-semibold">3</span>
                                                </div>
                                                <span class="text-sm font-medium text-gray-900">Conference Room</span>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-sm font-semibold text-gray-900">$28,100</div>
                                                <div class="text-xs text-gray-500">24 events</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Report Actions -->
                            <div class="mt-6 flex flex-wrap gap-4">
                                <button onclick="generateRevenueReport()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-chart-line mr-2"></i>Revenue Report
                                </button>
                                <button onclick="generateBookingReport()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-calendar-check mr-2"></i>Booking Report
                                </button>
                                <button onclick="generateVenueReport()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-building mr-2"></i>Venue Report
                                </button>
                                <button onclick="generateCustomerReport()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-users mr-2"></i>Customer Report
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

            // Initialize reports functionality
            initializeReports();
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });

        // Event Reports Management Functions
        function initializeReports() {
            loadReportsData();
            initializeReportFilters();
        }

        function initializeReportFilters() {
            const reportPeriod = document.getElementById('report-period');
            const reportType = document.getElementById('report-type');
            const exportFormat = document.getElementById('export-format');

            if (reportPeriod) reportPeriod.addEventListener('change', applyReportFilters);
            if (reportType) reportType.addEventListener('change', applyReportFilters);
            if (exportFormat) exportFormat.addEventListener('change', applyReportFilters);
        }

        function loadReportsData() {
            // Simulate loading reports data
            const reportsData = {
                totalEvents: generateRandomTotalEvents(),
                totalRevenue: generateRandomTotalRevenue(),
                totalGuests: generateRandomTotalGuests(),
                avgRating: generateRandomAvgRating()
            };

            updateReportsDisplay(reportsData);
        }

        function generateRandomTotalEvents() {
            return Math.floor(Math.random() * 20) + 70;
        }

        function generateRandomTotalRevenue() {
            return Math.floor(Math.random() * 50000) + 120000;
        }

        function generateRandomTotalGuests() {
            return Math.floor(Math.random() * 1000) + 3500;
        }

        function generateRandomAvgRating() {
            return (Math.random() * 0.5 + 4.5).toFixed(1);
        }

        function updateReportsDisplay(data) {
            const totalEvents = document.getElementById('total-events');
            const totalRevenue = document.getElementById('total-revenue');
            const totalGuests = document.getElementById('total-guests');
            const avgRating = document.getElementById('avg-rating');

            if (totalEvents) {
                totalEvents.textContent = data.totalEvents;
                const eventsGrowth = totalEvents.parentElement.querySelector('.text-xs');
                if (eventsGrowth) eventsGrowth.textContent = '+15 this month';
            }
            if (totalRevenue) {
                totalRevenue.textContent = `$${data.totalRevenue.toLocaleString()}`;
                const revenueGrowth = totalRevenue.parentElement.querySelector('.text-xs');
                if (revenueGrowth) revenueGrowth.textContent = '+22% vs last month';
            }
            if (totalGuests) {
                totalGuests.textContent = data.totalGuests.toLocaleString();
                const guestsGrowth = totalGuests.parentElement.querySelector('.text-xs');
                if (guestsGrowth) guestsGrowth.textContent = '+18% vs last month';
            }
            if (avgRating) {
                avgRating.textContent = data.avgRating;
                const ratingGrowth = avgRating.parentElement.querySelector('.text-xs');
                if (ratingGrowth) ratingGrowth.textContent = 'Out of 5.0';
            }
        }

        function applyReportFilters() {
            const reportPeriod = document.getElementById('report-period')?.value || 'month';
            const reportType = document.getElementById('report-type')?.value || 'overview';
            const exportFormat = document.getElementById('export-format')?.value || 'pdf';

            console.log('Applying report filters:', { reportPeriod, reportType, exportFormat });
            
            showNotification('Applying report filters...', 'info');
            
            setTimeout(() => {
                loadReportsData();
                showNotification('Report data updated successfully!', 'success');
            }, 1000);
        }

        // Report Generation Functions
        function generateReport() {
            showNotification('Generating report...', 'info');
            setTimeout(() => {
                showNotification('Report generated successfully!', 'success');
            }, 2000);
        }

        function generateRevenueReport() {
            showNotification('Generating revenue report...', 'info');
            setTimeout(() => {
                showNotification('Revenue report generated successfully!', 'success');
            }, 2000);
        }

        function generateBookingReport() {
            showNotification('Generating booking report...', 'info');
            setTimeout(() => {
                showNotification('Booking report generated successfully!', 'success');
            }, 2000);
        }

        function generateVenueReport() {
            showNotification('Generating venue report...', 'info');
            setTimeout(() => {
                showNotification('Venue report generated successfully!', 'success');
            }, 2000);
        }

        function generateCustomerReport() {
            showNotification('Generating customer report...', 'info');
            setTimeout(() => {
                showNotification('Customer report generated successfully!', 'success');
            }, 2000);
        }

        function generateCustomReport() {
            showNotification('Opening custom report builder...', 'info');
            setTimeout(() => {
                showNotification('Custom report builder loaded!', 'success');
            }, 1500);
        }

        function viewEventTypeReport() {
            showNotification('Opening event type report...', 'info');
            setTimeout(() => {
                showNotification('Event type report loaded!', 'success');
            }, 1500);
        }

        function viewVenueReport() {
            showNotification('Opening venue performance report...', 'info');
            setTimeout(() => {
                showNotification('Venue report loaded!', 'success');
            }, 1500);
        }

        function viewAdvancedAnalytics() {
            showNotification('Opening advanced analytics...', 'info');
            setTimeout(() => {
                showNotification('Advanced analytics loaded!', 'success');
            }, 2000);
        }

        function refreshReports() {
            showNotification('Refreshing report data...', 'info');
            setTimeout(() => {
                loadReportsData();
                showNotification('Report data refreshed successfully!', 'success');
            }, 1000);
        }

        function exportAllReports() {
            showNotification('Exporting all reports...', 'info');
            setTimeout(() => {
                showNotification('All reports exported successfully!', 'success');
            }, 2500);
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