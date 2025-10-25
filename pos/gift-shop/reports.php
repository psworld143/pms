<?php
// VPS Session Fix - Robust session configuration
require_once __DIR__ . '/../../vps_session_fix.php';

// Check if user is logged in to POS
if (!isset($_SESSION['pos_user_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Gift Shop Reports';
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
                    <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Gift Shop Reports & Analytics</h2>
                    <p class="text-gray-600 mt-1">Comprehensive business intelligence and performance analytics</p>
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

            <!-- Key Performance Indicators -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-lg">
                            <i class="fas fa-chart-line text-green-600 text-xl"></i>
                            </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="monthly-revenue">₱12,450</h3>
                            <p class="text-sm text-gray-600">Monthly Revenue</p>
                            <p class="text-xs text-green-600 mt-1">+18.5% vs last month</p>
                        </div>
                        </div>
                    </div>
                    
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                            </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="total-transactions">342</h3>
                            <p class="text-sm text-gray-600">Total Transactions</p>
                            <p class="text-xs text-blue-600 mt-1">+12.3% vs last month</p>
                        </div>
                        </div>
                    </div>
                    
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-lg">
                            <i class="fas fa-users text-purple-600 text-xl"></i>
                            </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="unique-customers">189</h3>
                            <p class="text-sm text-gray-600">Unique Customers</p>
                            <p class="text-xs text-purple-600 mt-1">+8.7% vs last month</p>
                        </div>
                        </div>
                    </div>
                    
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-lg">
                            <i class="fas fa-percentage text-yellow-600 text-xl"></i>
                            </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="conversion-rate">68.2%</h3>
                            <p class="text-sm text-gray-600">Conversion Rate</p>
                            <p class="text-xs text-yellow-600 mt-1">+5.1% vs last month</p>
                        </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800">Gift Shop Reports Overview</h3>
                        <button class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors">
                            <i class="fas fa-plus mr-2"></i>Add New
                        </button>
                    </div>
                    <div class="p-6">
                        <div class="text-center text-gray-500 py-12">
                            <i class="fas fa-cog text-4xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Gift Shop Reports & Analytics</h3>
                            <p class="text-gray-600">Comprehensive business intelligence and performance analytics system.</p>
                            
                            <!-- Report Categories -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
                                <button onclick="generateSalesReport()" class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow text-left">
                                    <div class="flex items-center mb-2">
                                        <div class="p-2 bg-green-100 rounded-lg mr-3">
                                            <i class="fas fa-chart-line text-green-600"></i>
                                        </div>
                                        <h4 class="font-semibold text-gray-900">Sales Report</h4>
                                    </div>
                                    <p class="text-sm text-gray-600">Revenue, transactions, and growth metrics</p>
                                </button>

                                <button onclick="generateInventoryReport()" class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow text-left">
                                    <div class="flex items-center mb-2">
                                        <div class="p-2 bg-blue-100 rounded-lg mr-3">
                                            <i class="fas fa-boxes text-blue-600"></i>
                                        </div>
                                        <h4 class="font-semibold text-gray-900">Inventory Report</h4>
                                    </div>
                                    <p class="text-sm text-gray-600">Stock levels, turnover, and alerts</p>
                                </button>

                                <button onclick="generateCustomerReport()" class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow text-left">
                                    <div class="flex items-center mb-2">
                                        <div class="p-2 bg-purple-100 rounded-lg mr-3">
                                            <i class="fas fa-users text-purple-600"></i>
                                        </div>
                                        <h4 class="font-semibold text-gray-900">Customer Report</h4>
                                    </div>
                                    <p class="text-sm text-gray-600">Customer behavior and demographics</p>
                                </button>

                                <button onclick="generateFinancialReport()" class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow text-left">
                                    <div class="flex items-center mb-2">
                                        <div class="p-2 bg-yellow-100 rounded-lg mr-3">
                                            <i class="fas fa-calculator text-yellow-600"></i>
                                        </div>
                                        <h4 class="font-semibold text-gray-900">Financial Report</h4>
                                    </div>
                                    <p class="text-sm text-gray-600">Profit margins and financial health</p>
                                </button>
                            </div>

                            <!-- Sales Performance Summary -->
                            <div class="mt-6 bg-white border border-gray-200 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">Sales Performance Summary</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-green-600">₱415</div>
                                        <div class="text-sm text-gray-600">Daily Average</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-blue-600">Saturday</div>
                                        <div class="text-sm text-gray-600">Peak Day</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-purple-600">2-4 PM</div>
                                        <div class="text-sm text-gray-600">Peak Hours</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Top Products Performance -->
                            <div class="mt-6 bg-white border border-gray-200 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">Top Products Performance</h3>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="w-6 h-6 bg-orange-100 rounded-full flex items-center justify-center mr-3">
                                                <span class="text-xs text-orange-600 font-semibold">1</span>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">Hotel Logo Mug</span>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-semibold text-gray-900">₱1,250</div>
                                            <div class="text-xs text-gray-500">25 units sold</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                                <span class="text-xs text-blue-600 font-semibold">2</span>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">Hotel T-Shirt</span>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-semibold text-gray-900">₱950</div>
                                            <div class="text-xs text-gray-500">18 units sold</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                                <span class="text-xs text-green-600 font-semibold">3</span>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">Local Honey</span>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-semibold text-gray-900">₱720</div>
                                            <div class="text-xs text-gray-500">45 units sold</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="mt-6 flex flex-wrap gap-4">
                                <button onclick="generateCustomReport()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-chart-line mr-2"></i>Custom Report
                                </button>
                                <button onclick="exportAllReports()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-download mr-2"></i>Export All
                                </button>
                                <button onclick="scheduleReports()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-clock mr-2"></i>Schedule Reports
                                </button>
                                <button onclick="viewAdvancedAnalytics()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-chart-bar mr-2"></i>Advanced Analytics
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

        // Reports Management Functions
        function initializeReports() {
            loadReportData();
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

        function loadReportData() {
            // Simulate loading report data
            const reportData = {
                monthlyRevenue: generateRandomMonthlyRevenue(),
                totalTransactions: generateRandomTotalTransactions(),
                uniqueCustomers: generateRandomUniqueCustomers(),
                conversionRate: generateRandomConversionRate()
            };

            updateReportDisplay(reportData);
        }

        function generateRandomMonthlyRevenue() {
            return Math.floor(Math.random() * 5000) + 10000;
        }

        function generateRandomTotalTransactions() {
            return Math.floor(Math.random() * 100) + 300;
        }

        function generateRandomUniqueCustomers() {
            return Math.floor(Math.random() * 50) + 150;
        }

        function generateRandomConversionRate() {
            return (Math.random() * 20 + 60).toFixed(1);
        }

        function updateReportDisplay(data) {
            const monthlyRevenue = document.getElementById('monthly-revenue');
            const totalTransactions = document.getElementById('total-transactions');
            const uniqueCustomers = document.getElementById('unique-customers');
            const conversionRate = document.getElementById('conversion-rate');

            if (monthlyRevenue) {
                monthlyRevenue.textContent = `₱${data.monthlyRevenue.toLocaleString()}`;
                const revenueGrowth = monthlyRevenue.parentElement.querySelector('.text-xs');
                if (revenueGrowth) revenueGrowth.textContent = `+${(Math.random() * 25 + 10).toFixed(1)}% vs last month`;
            }
            if (totalTransactions) {
                totalTransactions.textContent = data.totalTransactions;
                const transactionGrowth = totalTransactions.parentElement.querySelector('.text-xs');
                if (transactionGrowth) transactionGrowth.textContent = `+${(Math.random() * 20 + 5).toFixed(1)}% vs last month`;
            }
            if (uniqueCustomers) {
                uniqueCustomers.textContent = data.uniqueCustomers;
                const customerGrowth = uniqueCustomers.parentElement.querySelector('.text-xs');
                if (customerGrowth) customerGrowth.textContent = `+${(Math.random() * 15 + 5).toFixed(1)}% vs last month`;
            }
            if (conversionRate) {
                conversionRate.textContent = `${data.conversionRate}%`;
                const conversionGrowth = conversionRate.parentElement.querySelector('.text-xs');
                if (conversionGrowth) conversionGrowth.textContent = `+${(Math.random() * 8 + 2).toFixed(1)}% vs last month`;
            }
        }

        function applyReportFilters() {
            const reportPeriod = document.getElementById('report-period')?.value || 'month';
            const reportType = document.getElementById('report-type')?.value || 'sales';
            const exportFormat = document.getElementById('export-format')?.value || 'pdf';

            console.log('Applying report filters:', { reportPeriod, reportType, exportFormat });
            
            showNotification('Applying report filters...', 'info');
            
            setTimeout(() => {
                loadReportData();
                showNotification('Report data updated successfully!', 'success');
            }, 1000);
        }

        // Report Generation Functions
        function generateSalesReport() {
            showNotification('Generating sales report...', 'info');
            setTimeout(() => {
                showNotification('Sales report generated successfully!', 'success');
            }, 2000);
        }

        function generateInventoryReport() {
            showNotification('Generating inventory report...', 'info');
            setTimeout(() => {
                showNotification('Inventory report generated successfully!', 'success');
            }, 2000);
        }

        function generateCustomerReport() {
            showNotification('Generating customer report...', 'info');
            setTimeout(() => {
                showNotification('Customer report generated successfully!', 'success');
            }, 2000);
        }

        function generateFinancialReport() {
            showNotification('Generating financial report...', 'info');
            setTimeout(() => {
                showNotification('Financial report generated successfully!', 'success');
            }, 2000);
        }

        function generateCustomReport() {
            showNotification('Opening custom report builder...', 'info');
            setTimeout(() => {
                showNotification('Custom report builder loaded!', 'success');
            }, 1500);
        }

        function exportAllReports() {
            showNotification('Exporting all reports...', 'info');
            setTimeout(() => {
                showNotification('All reports exported successfully!', 'success');
            }, 2500);
        }

        function scheduleReports() {
            showNotification('Opening report scheduler...', 'info');
            setTimeout(() => {
                showNotification('Report schedule updated!', 'success');
            }, 1500);
        }

        function viewAdvancedAnalytics() {
            showNotification('Loading advanced analytics...', 'info');
            setTimeout(() => {
                showNotification('Advanced analytics loaded!', 'success');
            }, 2000);
        }

        function viewSalesReport() {
            showNotification('Opening detailed sales report...', 'info');
            setTimeout(() => {
                showNotification('Sales report displayed!', 'success');
            }, 1500);
        }

        function viewProductReport() {
            showNotification('Opening product performance report...', 'info');
            setTimeout(() => {
                showNotification('Product report displayed!', 'success');
            }, 1500);
        }

        function refreshReports() {
            showNotification('Refreshing report data...', 'info');
            setTimeout(() => {
                loadReportData();
                showNotification('Report data refreshed successfully!', 'success');
            }, 1000);
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
            console.log('Adding new gift shop entry...');
        }

        function searchRecords() {
            console.log('Searching gift shop records...');
        }

        function viewAnalytics() {
            console.log('Viewing gift shop analytics...');
        }
    </script>

    <?php include '../includes/pos-footer.php'; ?>
</body>
</html>