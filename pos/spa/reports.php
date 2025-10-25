<?php
// VPS Session Fix - Robust session configuration
require_once __DIR__ . '/../../vps_session_fix.php';

// Check if user is logged in to POS
if (!isset($_SESSION['pos_user_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Spa Reports';
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
                    <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Spa Reports & Analytics</h2>
                    <p class="text-gray-600 mt-1">Comprehensive spa performance analytics and business intelligence</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <div id="current-date" class="text-sm text-gray-600"></div>
                        <div id="current-time" class="text-sm text-gray-600"></div>
                    </div>
                    <button onclick="exportReports()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-download mr-2"></i>Export
                    </button>
                    <button onclick="refreshReports()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-sync-alt mr-2"></i>Refresh
                    </button>
                </div>
            </div>

            <!-- Analytics Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-lg">
                            <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="total-revenue">₱24,580</h3>
                            <p class="text-sm text-gray-600">Total Revenue</p>
                            <p class="text-xs text-green-600 mt-1">+12.5% vs last month</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="total-appointments">342</h3>
                            <p class="text-sm text-gray-600">Total Appointments</p>
                            <p class="text-xs text-blue-600 mt-1">+8.2% vs last month</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-lg">
                            <i class="fas fa-users text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="total-clients">156</h3>
                            <p class="text-sm text-gray-600">Unique Clients</p>
                            <p class="text-xs text-purple-600 mt-1">+15.3% vs last month</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-lg">
                            <i class="fas fa-star text-yellow-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="avg-rating">4.7</h3>
                            <p class="text-sm text-gray-600">Average Rating</p>
                            <p class="text-xs text-yellow-600 mt-1">+0.3 vs last month</p>
                        </div>
                    </div>
                </div>
            </div>

                <!-- Main Content Area -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800">Spa Reports Overview</h3>
                        <button class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors">
                            <i class="fas fa-plus mr-2"></i>Add New
                        </button>
                    </div>
                    <div class="p-6">
                        <div class="text-center text-gray-500 py-12">
                            <i class="fas fa-cog text-4xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Spa Reports & Analytics</h3>
                            <p class="text-gray-600">Comprehensive spa performance analytics and business intelligence.</p>
                            
                            <!-- Quick Report Cards -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
                                <div class="bg-white border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center">
                                        <div class="p-2 bg-green-100 rounded-lg mr-3">
                                            <i class="fas fa-dollar-sign text-green-600"></i>
                                        </div>
                                        <div>
                                            <div class="text-lg font-semibold text-gray-900">₱24,580</div>
                                            <div class="text-sm text-gray-600">Total Revenue</div>
                                            <div class="text-xs text-green-600">+12.5% vs last month</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-white border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center">
                                        <div class="p-2 bg-blue-100 rounded-lg mr-3">
                                            <i class="fas fa-calendar-check text-blue-600"></i>
                                        </div>
                                        <div>
                                            <div class="text-lg font-semibold text-gray-900">342</div>
                                            <div class="text-sm text-gray-600">Appointments</div>
                                            <div class="text-xs text-blue-600">+8.2% vs last month</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-white border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center">
                                        <div class="p-2 bg-purple-100 rounded-lg mr-3">
                                            <i class="fas fa-users text-purple-600"></i>
                                        </div>
                                        <div>
                                            <div class="text-lg font-semibold text-gray-900">156</div>
                                            <div class="text-sm text-gray-600">Unique Clients</div>
                                            <div class="text-xs text-purple-600">+15.3% vs last month</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-white border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center">
                                        <div class="p-2 bg-yellow-100 rounded-lg mr-3">
                                            <i class="fas fa-star text-yellow-600"></i>
                                        </div>
                                        <div>
                                            <div class="text-lg font-semibold text-gray-900">4.7</div>
                                            <div class="text-sm text-gray-600">Avg Rating</div>
                                            <div class="text-xs text-yellow-600">+0.3 vs last month</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Report Actions -->
                            <div class="mt-6 flex flex-wrap gap-4">
                                <button onclick="generateRevenueReport()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-chart-line mr-2"></i>Revenue Report
                                </button>
                                <button onclick="generateAppointmentReport()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-calendar-alt mr-2"></i>Appointment Analytics
                                </button>
                                <button onclick="generateTherapistReport()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-user-md mr-2"></i>Therapist Performance
                                </button>
                                <button onclick="exportAllReports()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-download mr-2"></i>Export All
                                </button>
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

            // Initialize reports functionality
            initializeReports();
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });

        // Reports Management Functions
        function initializeReports() {
            loadReportsData();
            initializeReportFilters();
        }

        function initializeReportFilters() {
            const reportPeriod = document.getElementById('report-period');
            const reportType = document.getElementById('report-type');
            const comparisonPeriod = document.getElementById('comparison-period');

            if (reportPeriod) reportPeriod.addEventListener('change', applyReportFilters);
            if (reportType) reportType.addEventListener('change', applyReportFilters);
            if (comparisonPeriod) comparisonPeriod.addEventListener('change', applyReportFilters);
        }

        function loadReportsData() {
            // Simulate loading reports data
            const reportsData = {
                revenue: generateRandomRevenue(),
                appointments: generateRandomAppointments(),
                clients: generateRandomClients(),
                rating: generateRandomRating()
            };

            updateReportsDisplay(reportsData);
        }

        function generateRandomRevenue() {
            return {
                total: Math.floor(Math.random() * 10000) + 20000,
                growth: (Math.random() * 20 + 5).toFixed(1)
            };
        }

        function generateRandomAppointments() {
            return {
                total: Math.floor(Math.random() * 100) + 300,
                growth: (Math.random() * 15 + 5).toFixed(1)
            };
        }

        function generateRandomClients() {
            return {
                total: Math.floor(Math.random() * 50) + 120,
                growth: (Math.random() * 20 + 10).toFixed(1)
            };
        }

        function generateRandomRating() {
            return {
                average: (Math.random() * 0.5 + 4.5).toFixed(1),
                growth: (Math.random() * 0.5 + 0.1).toFixed(1)
            };
        }

        function updateReportsDisplay(data) {
            const totalRevenue = document.getElementById('total-revenue');
            const totalAppointments = document.getElementById('total-appointments');
            const totalClients = document.getElementById('total-clients');
            const avgRating = document.getElementById('avg-rating');

            if (totalRevenue) {
                totalRevenue.textContent = `₱${data.revenue.total.toLocaleString()}`;
                const revenueGrowth = totalRevenue.parentElement.querySelector('.text-xs');
                if (revenueGrowth) revenueGrowth.textContent = `+${data.revenue.growth}% vs last month`;
            }
            if (totalAppointments) {
                totalAppointments.textContent = data.appointments.total;
                const appointmentGrowth = totalAppointments.parentElement.querySelector('.text-xs');
                if (appointmentGrowth) appointmentGrowth.textContent = `+${data.appointments.growth}% vs last month`;
            }
            if (totalClients) {
                totalClients.textContent = data.clients.total;
                const clientGrowth = totalClients.parentElement.querySelector('.text-xs');
                if (clientGrowth) clientGrowth.textContent = `+${data.clients.growth}% vs last month`;
            }
            if (avgRating) {
                avgRating.textContent = data.rating.average;
                const ratingGrowth = avgRating.parentElement.querySelector('.text-xs');
                if (ratingGrowth) ratingGrowth.textContent = `+${data.rating.growth} vs last month`;
            }
        }

        function applyReportFilters() {
            const reportPeriod = document.getElementById('report-period')?.value || 'month';
            const reportType = document.getElementById('report-type')?.value || 'overview';
            const comparisonPeriod = document.getElementById('comparison-period')?.value || 'previous';

            console.log('Applying report filters:', { reportPeriod, reportType, comparisonPeriod });
            
            showNotification('Generating report with selected filters...', 'info');
            
            setTimeout(() => {
                loadReportsData();
                showNotification('Report updated successfully!', 'success');
            }, 1500);
        }

        function generateReport() {
            const reportType = document.getElementById('report-type')?.value || 'overview';
            const reportPeriod = document.getElementById('report-period')?.value || 'month';
            
            showNotification(`Generating ${reportType} report for ${reportPeriod}...`, 'info');
            
            setTimeout(() => {
                showNotification(`${reportType.charAt(0).toUpperCase() + reportType.slice(1)} report generated successfully!`, 'success');
            }, 2000);
        }

        function exportReport() {
            const reportType = document.getElementById('report-type')?.value || 'overview';
            
            showNotification(`Exporting ${reportType} report...`, 'info');
            
            setTimeout(() => {
                showNotification('Report exported successfully!', 'success');
            }, 1500);
        }

        // Specific Report Functions
        function generateRevenueReport() {
            showNotification('Generating comprehensive revenue report...', 'info');
            setTimeout(() => {
                showNotification('Revenue report generated successfully!', 'success');
            }, 2000);
        }

        function generateAppointmentReport() {
            showNotification('Generating appointment analytics report...', 'info');
            setTimeout(() => {
                showNotification('Appointment analytics report generated successfully!', 'success');
            }, 2000);
        }

        function generateTherapistReport() {
            showNotification('Generating therapist performance report...', 'info');
            setTimeout(() => {
                showNotification('Therapist performance report generated successfully!', 'success');
            }, 2000);
        }

        function exportAllReports() {
            showNotification('Exporting all spa reports...', 'info');
            setTimeout(() => {
                showNotification('All reports exported successfully!', 'success');
            }, 2500);
        }

        function refreshReports() {
            showNotification('Refreshing reports data...', 'info');
            setTimeout(() => {
                loadReportsData();
                showNotification('Reports data refreshed successfully!', 'success');
            }, 1000);
        }

        function exportReports() {
            exportAllReports();
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
</body>
</html>