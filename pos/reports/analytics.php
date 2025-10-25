<?php
// VPS Session Fix - Robust session configuration
require_once __DIR__ . '/../../vps_session_fix.php';

// Check if user is logged in to POS
if (!isset($_SESSION['pos_user_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Analytics Dashboard';
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
                <div class="mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800 mb-2">Analytics Dashboard</h1>
                            <p class="text-gray-600">Advanced business intelligence and predictive analytics</p>
                        </div>
                        <div class="text-sm text-gray-500">
                            <div id="current-date" class="font-medium"></div>
                            <div id="current-time" class="text-xs"></div>
                        </div>
                    </div>
                </div>

                <!-- Analytics KPIs -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Revenue Growth</p>
                                <p id="revenue-growth" class="text-2xl font-bold text-gray-900">+24.5%</p>
                                <p class="text-xs text-blue-600">vs last quarter</p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-chart-line text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Customer Retention</p>
                                <p id="customer-retention" class="text-2xl font-bold text-gray-900">87.3%</p>
                                <p class="text-xs text-green-600">Monthly rate</p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-users text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-orange-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Market Share</p>
                                <p id="market-share" class="text-2xl font-bold text-gray-900">12.8%</p>
                                <p class="text-xs text-orange-600">Local market</p>
                            </div>
                            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-chart-pie text-orange-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">ROI</p>
                                <p id="roi" class="text-2xl font-bold text-gray-900">18.7%</p>
                                <p class="text-xs text-purple-600">Return on investment</p>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-percentage text-purple-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Analytics Dashboard Content -->
                <div class="p-6">
                    <!-- Analytics Filters -->
                    <div class="mb-6">
                        <div class="flex flex-col lg:flex-row gap-4">
                            <div class="flex-1">
                                <label for="analytics-view" class="block text-sm font-medium text-gray-700 mb-2">Analytics View</label>
                                <select id="analytics-view" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    <option value="">Select Analytics View</option>
                                    <option value="overview">Overview Dashboard</option>
                                    <option value="financial">Financial Analytics</option>
                                    <option value="operational">Operational Analytics</option>
                                    <option value="customer">Customer Analytics</option>
                                    <option value="predictive">Predictive Analytics</option>
                                </select>
                            </div>
                            <div class="lg:w-48">
                                <label for="analytics-period" class="block text-sm font-medium text-gray-700 mb-2">Time Period</label>
                                <select id="analytics-period" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    <option value="">Select Period</option>
                                    <option value="realtime">Real-time</option>
                                    <option value="today">Today</option>
                                    <option value="week">This Week</option>
                                    <option value="month">This Month</option>
                                    <option value="quarter">This Quarter</option>
                                    <option value="year">This Year</option>
                                </select>
                            </div>
                            <div class="lg:w-48">
                                <label for="analytics-comparison" class="block text-sm font-medium text-gray-700 mb-2">Comparison</label>
                                <select id="analytics-comparison" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    <option value="">Compare With</option>
                                    <option value="previous">Previous Period</option>
                                    <option value="last-year">Last Year</option>
                                    <option value="industry">Industry Average</option>
                                    <option value="competitors">Competitors</option>
                                </select>
                            </div>
                            <div class="lg:w-48">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Actions</label>
                                <div class="flex space-x-2">
                                    <button onclick="generateAnalyticsReport()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                                        <i class="fas fa-chart-bar mr-2"></i>Generate
                                    </button>
                                    <button onclick="refreshAnalyticsData()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                                        <i class="fas fa-sync-alt mr-2"></i>Refresh
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Advanced Analytics Charts -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        <!-- Revenue Forecasting -->
                        <div class="bg-white border border-gray-200 rounded-lg">
                            <div class="p-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-chart-area text-blue-600 mr-2"></i>
                                    Revenue Forecasting
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Current Month</span>
                                        <span class="font-semibold text-gray-900">₱156,750</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Next Month (Forecast)</span>
                                        <span class="font-semibold text-blue-600">₱168,420</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">3-Month Projection</span>
                                        <span class="font-semibold text-green-600">₱485,680</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Confidence Level</span>
                                        <span class="font-semibold text-purple-600">87.5%</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Growth Trend</span>
                                        <span class="font-semibold text-orange-600">↑ +7.2%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Segmentation -->
                        <div class="bg-white border border-gray-200 rounded-lg">
                            <div class="p-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-users text-green-600 mr-2"></i>
                                    Customer Segmentation
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center">
                                            <div class="w-4 h-4 bg-blue-500 rounded-full mr-3"></div>
                                            <span class="text-sm text-gray-600">VIP Customers</span>
                                        </div>
                                        <span class="font-semibold text-gray-900">15% (234)</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center">
                                            <div class="w-4 h-4 bg-green-500 rounded-full mr-3"></div>
                                            <span class="text-sm text-gray-600">Regular Customers</span>
                                        </div>
                                        <span class="font-semibold text-gray-900">45% (702)</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center">
                                            <div class="w-4 h-4 bg-orange-500 rounded-full mr-3"></div>
                                            <span class="text-sm text-gray-600">New Customers</span>
                                        </div>
                                        <span class="font-semibold text-gray-900">25% (390)</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center">
                                            <div class="w-4 h-4 bg-purple-500 rounded-full mr-3"></div>
                                            <span class="text-sm text-gray-600">At-Risk Customers</span>
                                        </div>
                                        <span class="font-semibold text-gray-900">15% (234)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Business Intelligence Insights -->
                    <div class="bg-white border border-gray-200 rounded-lg">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-brain text-purple-600 mr-2"></i>
                                Business Intelligence Insights
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <h4 class="font-semibold text-blue-900 mb-2">Revenue Optimization</h4>
                                    <p class="text-sm text-blue-700">Peak hours (2-4 PM) generate 35% more revenue. Consider extending high-demand services during these times.</p>
                                </div>
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <h4 class="font-semibold text-green-900 mb-2">Customer Behavior</h4>
                                    <p class="text-sm text-green-700">VIP customers show 78% higher lifetime value. Focus retention efforts on this segment.</p>
                                </div>
                                <div class="bg-orange-50 p-4 rounded-lg">
                                    <h4 class="font-semibold text-orange-900 mb-2">Operational Efficiency</h4>
                                    <p class="text-sm text-orange-700">Spa services have 23% higher customer satisfaction. Consider expanding spa offerings.</p>
                                </div>
                                <div class="bg-purple-50 p-4 rounded-lg">
                                    <h4 class="font-semibold text-purple-900 mb-2">Market Trends</h4>
                                    <p class="text-sm text-purple-700">Wellness services trending +45% this quarter. Opportunity for premium pricing.</p>
                                </div>
                                <div class="bg-pink-50 p-4 rounded-lg">
                                    <h4 class="font-semibold text-pink-900 mb-2">Seasonal Patterns</h4>
                                    <p class="text-sm text-pink-700">Weekend bookings increase 67% during holiday seasons. Prepare for capacity planning.</p>
                                </div>
                                <div class="bg-yellow-50 p-4 rounded-lg">
                                    <h4 class="font-semibold text-yellow-900 mb-2">Cost Optimization</h4>
                                    <p class="text-sm text-yellow-700">Inventory turnover improved 12% this month. Optimize stock levels for better cash flow.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="mt-6 flex flex-wrap gap-4">
                        <button onclick="exportAnalyticsReport()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-download mr-2"></i>Export Analytics
                        </button>
                        <button onclick="createCustomDashboard()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-plus mr-2"></i>Custom Dashboard
                        </button>
                        <button onclick="scheduleAnalyticsReport()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-clock mr-2"></i>Schedule Reports
                        </button>
                        <button onclick="viewPredictiveAnalytics()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-crystal-ball mr-2"></i>Predictive Analytics
                        </button>
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

            // Initialize analytics functionality
            initializeAnalytics();
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });

        // Analytics Management Functions
        function initializeAnalytics() {
            loadAnalyticsData();
            initializeAnalyticsFilters();
        }

        function initializeAnalyticsFilters() {
            const analyticsView = document.getElementById('analytics-view');
            const analyticsPeriod = document.getElementById('analytics-period');
            const analyticsComparison = document.getElementById('analytics-comparison');

            if (analyticsView) analyticsView.addEventListener('change', applyAnalyticsFilters);
            if (analyticsPeriod) analyticsPeriod.addEventListener('change', applyAnalyticsFilters);
            if (analyticsComparison) analyticsComparison.addEventListener('change', applyAnalyticsFilters);
        }

        function loadAnalyticsData() {
            // Simulate loading analytics data
            const analyticsData = {
                revenueGrowth: generateRandomRevenueGrowth(),
                customerRetention: generateRandomCustomerRetention(),
                marketShare: generateRandomMarketShare(),
                roi: generateRandomROI()
            };

            updateAnalyticsDisplay(analyticsData);
        }

        function generateRandomRevenueGrowth() {
            return '+' + (Math.random() * 20 + 15).toFixed(1) + '%';
        }

        function generateRandomCustomerRetention() {
            return (Math.random() * 15 + 80).toFixed(1) + '%';
        }

        function generateRandomMarketShare() {
            return (Math.random() * 8 + 10).toFixed(1) + '%';
        }

        function generateRandomROI() {
            return (Math.random() * 10 + 15).toFixed(1) + '%';
        }

        function updateAnalyticsDisplay(data) {
            const revenueGrowth = document.getElementById('revenue-growth');
            const customerRetention = document.getElementById('customer-retention');
            const marketShare = document.getElementById('market-share');
            const roi = document.getElementById('roi');

            if (revenueGrowth) revenueGrowth.textContent = data.revenueGrowth;
            if (customerRetention) customerRetention.textContent = data.customerRetention;
            if (marketShare) marketShare.textContent = data.marketShare;
            if (roi) roi.textContent = data.roi;
        }

        function applyAnalyticsFilters() {
            const analyticsView = document.getElementById('analytics-view')?.value || '';
            const analyticsPeriod = document.getElementById('analytics-period')?.value || '';
            const analyticsComparison = document.getElementById('analytics-comparison')?.value || '';

            console.log('Applying analytics filters:', { analyticsView, analyticsPeriod, analyticsComparison });
            
            showNotification('Applying analytics filters...', 'info');
            
            setTimeout(() => {
                loadAnalyticsData();
                showNotification('Analytics data updated successfully!', 'success');
            }, 1000);
        }

        // Analytics Operations
        function generateAnalyticsReport() {
            showNotification('Generating comprehensive analytics report...', 'info');
            setTimeout(() => {
                showNotification('Analytics report generated successfully!', 'success');
            }, 2000);
        }

        function refreshAnalyticsData() {
            showNotification('Refreshing analytics data...', 'info');
            setTimeout(() => {
                loadAnalyticsData();
                showNotification('Analytics data refreshed successfully!', 'success');
            }, 1500);
        }

        function exportAnalyticsReport() {
            showNotification('Exporting analytics report to PDF...', 'info');
            setTimeout(() => {
                showNotification('Analytics report exported successfully!', 'success');
            }, 2000);
        }

        function createCustomDashboard() {
            showNotification('Opening custom dashboard builder...', 'info');
            setTimeout(() => {
                showNotification('Custom dashboard builder loaded!', 'success');
            }, 1500);
        }

        function scheduleAnalyticsReport() {
            showNotification('Setting up scheduled analytics reports...', 'info');
            setTimeout(() => {
                showNotification('Scheduled reports configured successfully!', 'success');
            }, 2000);
        }

        function viewPredictiveAnalytics() {
            showNotification('Loading predictive analytics models...', 'info');
            setTimeout(() => {
                showNotification('Predictive analytics dashboard opened!', 'success');
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
            console.log('Adding new analytics entry...');
        }

        function searchRecords() {
            console.log('Searching analytics records...');
        }

        function viewAnalytics() {
            console.log('Viewing analytics...');
        }
    </script>

    <?php include '../includes/pos-footer.php'; ?>
</body>
</html>