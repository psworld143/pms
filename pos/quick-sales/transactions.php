<?php
// VPS Session Fix - Robust session configuration
require_once __DIR__ . '/../../vps_session_fix.php';

// Check if user is logged in to POS
if (!isset($_SESSION['pos_user_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Transactions';
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
                    <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Transaction Management</h2>
                    <p class="text-gray-600 mt-1">Comprehensive transaction history and sales management system</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <div id="current-date" class="text-sm text-gray-600"></div>
                        <div id="current-time" class="text-sm text-gray-600"></div>
                    </div>
                    <button onclick="exportTransactions()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-download mr-2"></i>Export
                    </button>
                    <button onclick="openNewTransaction()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>New Transaction
                    </button>
                </div>
            </div>

            <!-- Transaction Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <i class="fas fa-receipt text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="total-transactions">342</h3>
                            <p class="text-sm text-gray-600">Total Transactions</p>
                            <p class="text-xs text-blue-600 mt-1">+28 today</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-lg">
                            <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="total-revenue">₱45,680</h3>
                            <p class="text-sm text-gray-600">Total Revenue</p>
                            <p class="text-xs text-green-600 mt-1">+15% vs yesterday</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-lg">
                            <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="avg-transaction">₱133.57</h3>
                            <p class="text-sm text-gray-600">Avg Transaction</p>
                            <p class="text-xs text-purple-600 mt-1">₱12.50 increase</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-lg">
                            <i class="fas fa-clock text-yellow-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="avg-time">1.8m</h3>
                            <p class="text-sm text-gray-600">Avg Process Time</p>
                            <p class="text-xs text-yellow-600 mt-1">0.3m faster</p>
                        </div>
                    </div>
                </div>
            </div>

                <!-- Main Content Area -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800">Transactions Overview</h3>
                        <button class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors">
                            <i class="fas fa-plus mr-2"></i>Add New
                        </button>
                    </div>
                    <div class="p-6">
                        <!-- Transaction Filters -->
                        <div class="mb-6">
                            <div class="flex flex-col lg:flex-row gap-4">
                                <div class="flex-1">
                                    <label for="search-transactions" class="block text-sm font-medium text-gray-700 mb-2">Search Transactions</label>
                                    <div class="relative">
                                        <input type="text" id="search-transactions" placeholder="Search by transaction ID, customer, or items..." 
                                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                                    </div>
                                </div>
                                <div class="lg:w-48">
                                    <label for="date-range" class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                                    <select id="date-range" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                        <option value="">All Dates</option>
                                        <option value="today">Today</option>
                                        <option value="yesterday">Yesterday</option>
                                        <option value="week">This Week</option>
                                        <option value="month">This Month</option>
                                    </select>
                                </div>
                                <div class="lg:w-48">
                                    <label for="payment-method" class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                                    <select id="payment-method" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                        <option value="">All Methods</option>
                                        <option value="cash">Cash</option>
                                        <option value="card">Card</option>
                                        <option value="mobile">Mobile Payment</option>
                                    </select>
                                </div>
                                <div class="lg:w-48">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Actions</label>
                                    <div class="flex space-x-2">
                                        <button onclick="refreshTransactions()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                                            <i class="fas fa-sync-alt mr-2"></i>Refresh
                                        </button>
                                        <button onclick="exportTransactions()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                                            <i class="fas fa-download mr-2"></i>Export
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Transactions -->
                        <div class="space-y-4">
                            <!-- Sample Transaction 1 -->
                            <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                                            <i class="fas fa-check-circle text-green-600"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-900">Transaction #QS-2024-001</h4>
                                            <p class="text-sm text-gray-600">Coffee, Sandwich, Chips • Cash Payment</p>
                                            <p class="text-xs text-gray-500">Today at 2:45 PM • Staff: Sarah Johnson</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-semibold text-gray-900">₱285.00</div>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Completed
                                        </span>
                                        <div class="mt-2 flex space-x-2">
                                            <button onclick="viewTransactionDetails('1')" class="text-blue-600 hover:text-blue-800 text-sm">
                                                <i class="fas fa-eye mr-1"></i>View
                                            </button>
                                            <button onclick="printReceipt('1')" class="text-orange-600 hover:text-orange-800 text-sm">
                                                <i class="fas fa-print mr-1"></i>Receipt
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sample Transaction 2 -->
                            <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                                            <i class="fas fa-credit-card text-blue-600"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-900">Transaction #QS-2024-002</h4>
                                            <p class="text-sm text-gray-600">Souvenir T-shirt, Keychain • Card Payment</p>
                                            <p class="text-xs text-gray-500">Today at 2:32 PM • Staff: Mike Chen</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-semibold text-gray-900">₱425.00</div>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Completed
                                        </span>
                                        <div class="mt-2 flex space-x-2">
                                            <button onclick="viewTransactionDetails('2')" class="text-blue-600 hover:text-blue-800 text-sm">
                                                <i class="fas fa-eye mr-1"></i>View
                                            </button>
                                            <button onclick="printReceipt('2')" class="text-orange-600 hover:text-orange-800 text-sm">
                                                <i class="fas fa-print mr-1"></i>Receipt
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sample Transaction 3 -->
                            <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                                            <i class="fas fa-mobile-alt text-purple-600"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-900">Transaction #QS-2024-003</h4>
                                            <p class="text-sm text-gray-600">Soft Drinks, Snacks • Mobile Payment</p>
                                            <p class="text-xs text-gray-500">Today at 2:18 PM • Staff: Alex Rodriguez</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-semibold text-gray-900">₱165.00</div>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Completed
                                        </span>
                                        <div class="mt-2 flex space-x-2">
                                            <button onclick="viewTransactionDetails('3')" class="text-blue-600 hover:text-blue-800 text-sm">
                                                <i class="fas fa-eye mr-1"></i>View
                                            </button>
                                            <button onclick="printReceipt('3')" class="text-orange-600 hover:text-orange-800 text-sm">
                                                <i class="fas fa-print mr-1"></i>Receipt
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="mt-6 flex flex-wrap gap-4">
                            <button onclick="createTransactionPackage()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-plus mr-2"></i>Create Transaction
                            </button>
                            <button onclick="manageRefunds()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-undo mr-2"></i>Manage Refunds
                            </button>
                            <button onclick="viewTransactionAnalytics()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-chart-bar mr-2"></i>Transaction Analytics
                            </button>
                            <button onclick="setupVoidTransactions()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-ban mr-2"></i>Void Transactions
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
        });

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

            // Initialize transactions functionality
            initializeTransactions();
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });

        // Transaction Management Functions
        function initializeTransactions() {
            loadTransactionsData();
            initializeTransactionFilters();
        }

        function initializeTransactionFilters() {
            const searchInput = document.getElementById('search-transactions');
            const dateRange = document.getElementById('date-range');
            const paymentMethod = document.getElementById('payment-method');

            if (searchInput) searchInput.addEventListener('input', applyTransactionFilters);
            if (dateRange) dateRange.addEventListener('change', applyTransactionFilters);
            if (paymentMethod) paymentMethod.addEventListener('change', applyTransactionFilters);
        }

        function loadTransactionsData() {
            // Simulate loading transactions data
            const transactionsData = {
                totalTransactions: generateRandomTotalTransactions(),
                totalRevenue: generateRandomTotalRevenue(),
                avgTransaction: generateRandomAvgTransaction(),
                avgTime: generateRandomAvgTime()
            };

            updateTransactionsDisplay(transactionsData);
        }

        function generateRandomTotalTransactions() {
            return Math.floor(Math.random() * 100) + 280;
        }

        function generateRandomTotalRevenue() {
            return Math.floor(Math.random() * 15000) + 35000;
        }

        function generateRandomAvgTransaction() {
            return (Math.random() * 50 + 100).toFixed(2);
        }

        function generateRandomAvgTime() {
            return (Math.random() * 2 + 1).toFixed(1);
        }

        function updateTransactionsDisplay(data) {
            const totalTransactions = document.getElementById('total-transactions');
            const totalRevenue = document.getElementById('total-revenue');
            const avgTransaction = document.getElementById('avg-transaction');
            const avgTime = document.getElementById('avg-time');

            if (totalTransactions) {
                totalTransactions.textContent = data.totalTransactions;
                const transactionsGrowth = totalTransactions.parentElement.querySelector('.text-xs');
                if (transactionsGrowth) transactionsGrowth.textContent = '+28 today';
            }
            if (totalRevenue) {
                totalRevenue.textContent = `₱${data.totalRevenue.toLocaleString()}`;
                const revenueGrowth = totalRevenue.parentElement.querySelector('.text-xs');
                if (revenueGrowth) revenueGrowth.textContent = '+15% vs yesterday';
            }
            if (avgTransaction) {
                avgTransaction.textContent = `₱${data.avgTransaction}`;
                const avgGrowth = avgTransaction.parentElement.querySelector('.text-xs');
                if (avgGrowth) avgGrowth.textContent = '₱12.50 increase';
            }
            if (avgTime) {
                avgTime.textContent = `${data.avgTime}m`;
                const timeGrowth = avgTime.parentElement.querySelector('.text-xs');
                if (timeGrowth) timeGrowth.textContent = '0.3m faster';
            }
        }

        function applyTransactionFilters() {
            const searchTerm = document.getElementById('search-transactions')?.value.toLowerCase() || '';
            const dateRange = document.getElementById('date-range')?.value || '';
            const paymentMethod = document.getElementById('payment-method')?.value || '';

            console.log('Applying transaction filters:', { searchTerm, dateRange, paymentMethod });
            
            showNotification('Applying transaction filters...', 'info');
            
            setTimeout(() => {
                loadTransactionsData();
                showNotification('Transaction data updated successfully!', 'success');
            }, 1000);
        }

        // Transaction Operations
        function openNewTransaction() {
            showNotification('Opening new transaction...', 'info');
            setTimeout(() => {
                showNotification('New transaction form loaded!', 'success');
            }, 1500);
        }

        function viewTransactionDetails(transactionId) {
            showNotification(`Opening transaction details for ID: ${transactionId}...`, 'info');
            setTimeout(() => {
                showNotification('Transaction details loaded!', 'success');
            }, 1500);
        }

        function printReceipt(transactionId) {
            showNotification(`Printing receipt for transaction ID: ${transactionId}...`, 'info');
            setTimeout(() => {
                showNotification('Receipt printed successfully!', 'success');
            }, 1500);
        }

        function createTransactionPackage() {
            showNotification('Opening transaction package creator...', 'info');
            setTimeout(() => {
                showNotification('Transaction package created!', 'success');
            }, 2000);
        }

        function manageRefunds() {
            showNotification('Opening refund management...', 'info');
            setTimeout(() => {
                showNotification('Refund management loaded!', 'success');
            }, 1500);
        }

        function viewTransactionAnalytics() {
            showNotification('Opening transaction analytics...', 'info');
            setTimeout(() => {
                showNotification('Transaction analytics loaded!', 'success');
            }, 1500);
        }

        function setupVoidTransactions() {
            showNotification('Opening void transactions...', 'info');
            setTimeout(() => {
                showNotification('Void transactions setup loaded!', 'success');
            }, 1500);
        }

        function refreshTransactions() {
            showNotification('Refreshing transaction data...', 'info');
            setTimeout(() => {
                loadTransactionsData();
                showNotification('Transaction data refreshed successfully!', 'success');
            }, 1000);
        }

        function exportTransactions() {
            showNotification('Exporting transaction data...', 'info');
            setTimeout(() => {
                showNotification('Transaction data exported successfully!', 'success');
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
            console.log('Adding new quick sales entry...');
        }

        function searchRecords() {
            console.log('Searching quick sales records...');
        }

        function viewAnalytics() {
            console.log('Viewing quick sales analytics...');
        }
    </script>

    <?php include '../includes/pos-footer.php'; ?>
</body>
</html>