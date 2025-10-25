<?php
// VPS Session Fix - Robust session configuration
require_once __DIR__ . '/../../vps_session_fix.php';

// Check if user is logged in to POS
if (!isset($_SESSION['pos_user_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Sales';
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
                    <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Gift Shop Sales & Analytics</h2>
                    <p class="text-gray-600 mt-1">Comprehensive sales tracking and transaction management system</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <div id="current-date" class="text-sm text-gray-600"></div>
                        <div id="current-time" class="text-sm text-gray-600"></div>
                    </div>
                    <button onclick="exportSales()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-download mr-2"></i>Export
                    </button>
                    <button onclick="processNewSale()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-shopping-cart mr-2"></i>New Sale
                    </button>
                </div>
            </div>

            <!-- Sales Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-lg">
                            <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="total-sales">₱8,420</h3>
                            <p class="text-sm text-gray-600">Total Sales</p>
                            <p class="text-xs text-green-600 mt-1">+15.3% vs last week</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="total-transactions">142</h3>
                            <p class="text-sm text-gray-600">Transactions</p>
                            <p class="text-xs text-blue-600 mt-1">+8.2% vs last week</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-lg">
                            <i class="fas fa-users text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="total-customers">89</h3>
                            <p class="text-sm text-gray-600">Customers</p>
                            <p class="text-xs text-purple-600 mt-1">+12.1% vs last week</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-lg">
                            <i class="fas fa-shopping-bag text-yellow-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="avg-transaction">₱59.30</h3>
                            <p class="text-sm text-gray-600">Avg Transaction</p>
                            <p class="text-xs text-yellow-600 mt-1">+6.5% vs last week</p>
                        </div>
                    </div>
                </div>
            </div>

                <!-- Main Content Area -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800">Sales Overview</h3>
                        <button class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors">
                            <i class="fas fa-plus mr-2"></i>Add New
                        </button>
                    </div>
                    <div class="p-6">
                        <div class="text-center text-gray-500 py-12">
                            <i class="fas fa-cog text-4xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Gift Shop Sales & Analytics</h3>
                            <p class="text-gray-600">Comprehensive sales tracking and transaction management system.</p>
                            
                            <!-- Recent Transactions -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
                                <div class="bg-white border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-shopping-cart text-green-600"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-gray-900">#TXN-001</h4>
                                                <p class="text-sm text-gray-600">John Smith</p>
                                                <p class="text-xs text-gray-500">2:30 PM • Cash</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-semibold text-gray-900">₱43.98</div>
                                            <button onclick="viewTransaction('TXN-001')" class="text-orange-600 hover:text-orange-800 text-sm">
                                                View
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-white border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-shopping-cart text-blue-600"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-gray-900">#TXN-002</h4>
                                                <p class="text-sm text-gray-600">Sarah Johnson</p>
                                                <p class="text-xs text-gray-500">2:15 PM • Card</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-semibold text-gray-900">₱21.98</div>
                                            <button onclick="viewTransaction('TXN-002')" class="text-orange-600 hover:text-orange-800 text-sm">
                                                View
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-white border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-shopping-cart text-purple-600"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-gray-900">#TXN-003</h4>
                                                <p class="text-sm text-gray-600">Mike Chen</p>
                                                <p class="text-xs text-gray-500">1:45 PM • Digital</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-semibold text-gray-900">₱78.50</div>
                                            <button onclick="viewTransaction('TXN-003')" class="text-orange-600 hover:text-orange-800 text-sm">
                                                View
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Top Selling Products -->
                            <div class="mt-6 bg-white border border-gray-200 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">Top Selling Products</h3>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-mug-hot text-orange-600"></i>
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
                                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-tshirt text-blue-600"></i>
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
                                            <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-key text-yellow-600"></i>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">Hotel Keychain</span>
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
                                <button onclick="generateSalesReport()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-chart-line mr-2"></i>Sales Report
                                </button>
                                <button onclick="viewCustomerAnalytics()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-users mr-2"></i>Customer Analytics
                                </button>
                                <button onclick="processRefund()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-undo mr-2"></i>Process Refund
                                </button>
                                <button onclick="viewSalesTrends()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-chart-bar mr-2"></i>Sales Trends
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
        </main>
    </div>

    <script>
        // Gift shop module functionality
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

            // Initialize sales functionality
            initializeSales();
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });

        // Sales Management Functions
        function initializeSales() {
            loadSalesData();
            initializeSalesFilters();
        }

        function initializeSalesFilters() {
            const searchInput = document.getElementById('search-sales');
            const dateRangeFilter = document.getElementById('date-range');
            const paymentMethodFilter = document.getElementById('payment-method');

            if (searchInput) searchInput.addEventListener('input', applySalesFilters);
            if (dateRangeFilter) dateRangeFilter.addEventListener('change', applySalesFilters);
            if (paymentMethodFilter) paymentMethodFilter.addEventListener('change', applySalesFilters);
        }

        function loadSalesData() {
            // Simulate loading sales data
            const salesData = {
                totalSales: generateRandomTotalSales(),
                totalTransactions: generateRandomTotalTransactions(),
                totalCustomers: generateRandomTotalCustomers(),
                avgTransaction: generateRandomAvgTransaction()
            };

            updateSalesDisplay(salesData);
        }

        function generateRandomTotalSales() {
            return Math.floor(Math.random() * 3000) + 6000;
        }

        function generateRandomTotalTransactions() {
            return Math.floor(Math.random() * 50) + 100;
        }

        function generateRandomTotalCustomers() {
            return Math.floor(Math.random() * 30) + 60;
        }

        function generateRandomAvgTransaction() {
            return (Math.random() * 30 + 40).toFixed(2);
        }

        function updateSalesDisplay(data) {
            const totalSales = document.getElementById('total-sales');
            const totalTransactions = document.getElementById('total-transactions');
            const totalCustomers = document.getElementById('total-customers');
            const avgTransaction = document.getElementById('avg-transaction');

            if (totalSales) {
                totalSales.textContent = `₱${data.totalSales.toLocaleString()}`;
                const salesGrowth = totalSales.parentElement.querySelector('.text-xs');
                if (salesGrowth) salesGrowth.textContent = `+${(Math.random() * 20 + 5).toFixed(1)}% vs last week`;
            }
            if (totalTransactions) {
                totalTransactions.textContent = data.totalTransactions;
                const transactionGrowth = totalTransactions.parentElement.querySelector('.text-xs');
                if (transactionGrowth) transactionGrowth.textContent = `+${(Math.random() * 15 + 5).toFixed(1)}% vs last week`;
            }
            if (totalCustomers) {
                totalCustomers.textContent = data.totalCustomers;
                const customerGrowth = totalCustomers.parentElement.querySelector('.text-xs');
                if (customerGrowth) customerGrowth.textContent = `+${(Math.random() * 20 + 5).toFixed(1)}% vs last week`;
            }
            if (avgTransaction) {
                avgTransaction.textContent = `₱${data.avgTransaction}`;
                const avgGrowth = avgTransaction.parentElement.querySelector('.text-xs');
                if (avgGrowth) avgGrowth.textContent = `+${(Math.random() * 10 + 2).toFixed(1)}% vs last week`;
            }
        }

        function applySalesFilters() {
            const searchTerm = document.getElementById('search-sales')?.value.toLowerCase() || '';
            const dateRange = document.getElementById('date-range')?.value || 'month';
            const paymentMethod = document.getElementById('payment-method')?.value || '';

            console.log('Applying sales filters:', { searchTerm, dateRange, paymentMethod });
            
            showNotification('Applying sales filters...', 'info');
            
            setTimeout(() => {
                loadSalesData();
                showNotification('Sales data updated successfully!', 'success');
            }, 1000);
        }

        // Sales Operations
        function processNewSale() {
            showNotification('Opening new sale interface...', 'info');
            console.log('Processing new sale');
        }

        function viewTransaction(transactionId) {
            showNotification(`Viewing transaction ${transactionId}...`, 'info');
            console.log(`Viewing transaction: ${transactionId}`);
        }

        function generateSalesReport() {
            showNotification('Generating comprehensive sales report...', 'info');
            setTimeout(() => {
                showNotification('Sales report generated successfully!', 'success');
            }, 2000);
        }

        function viewCustomerAnalytics() {
            showNotification('Opening customer analytics...', 'info');
            setTimeout(() => {
                showNotification('Customer analytics loaded successfully!', 'success');
            }, 1500);
        }

        function processRefund() {
            showNotification('Opening refund processing...', 'info');
            setTimeout(() => {
                showNotification('Refund processed successfully!', 'success');
            }, 2000);
        }

        function viewSalesTrends() {
            showNotification('Loading sales trends...', 'info');
            setTimeout(() => {
                showNotification('Sales trends displayed successfully!', 'success');
            }, 1500);
        }

        function refreshSales() {
            showNotification('Refreshing sales data...', 'info');
            setTimeout(() => {
                loadSalesData();
                showNotification('Sales data refreshed successfully!', 'success');
            }, 1000);
        }

        function exportSales() {
            showNotification('Exporting sales data...', 'info');
            setTimeout(() => {
                showNotification('Sales data exported successfully!', 'success');
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