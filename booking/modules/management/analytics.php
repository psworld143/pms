<?php
/**
 * Business Analytics Dashboard
 * Hotel PMS Training System for Students
 */

session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Check if user is logged in and has manager role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    header('Location: ../../login.php');
    exit();
}

// Set page title
$page_title = 'Business Analytics';

// Get analytics data
try {
    // Get dashboard statistics
    $dashboard_stats = getDashboardStats();
    
    // Get revenue growth (compare current month vs previous month)
    $current_month = date('Y-m');
    $previous_month = date('Y-m', strtotime('-1 month'));
    
    $stmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN DATE_FORMAT(created_at, '%Y-%m') = ? THEN total_amount ELSE 0 END) as current_revenue,
            SUM(CASE WHEN DATE_FORMAT(created_at, '%Y-%m') = ? THEN total_amount ELSE 0 END) as previous_revenue
        FROM billing 
        WHERE payment_status = 'paid'
    ");
    $stmt->execute([$current_month, $previous_month]);
    $revenue_data = $stmt->fetch();
    
    $current_revenue = $revenue_data['current_revenue'] ?? 0;
    $previous_revenue = $revenue_data['previous_revenue'] ?? 0;
    $revenue_growth = $previous_revenue > 0 ? round((($current_revenue - $previous_revenue) / $previous_revenue) * 100, 1) : 0;
    
    // Get average room rate
    $stmt = $pdo->query("
        SELECT AVG(total_amount) as avg_room_rate 
        FROM billing 
        WHERE payment_status = 'paid' 
        AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $avg_room_rate = $stmt->fetch()['avg_room_rate'] ?? 0;
    
    // Get guest satisfaction (simulated - would come from feedback system)
    $stmt = $pdo->query("
        SELECT COUNT(*) as total_guests 
        FROM reservations 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $total_guests = $stmt->fetch()['total_guests'] ?? 0;
    $guest_satisfaction = 4.2 + (min($total_guests / 100, 1) * 0.8); // Simulated rating based on guest volume
    
} catch (Exception $e) {
    error_log("Error getting analytics data: " . $e->getMessage());
    $dashboard_stats = ['occupancy_rate' => 0, 'today_revenue' => 0];
    $revenue_growth = 0;
    $avg_room_rate = 0;
    $guest_satisfaction = 4.0;
}

// Include header
include '../../includes/header-unified.php';
// Include sidebar
include '../../includes/sidebar-unified.php';
?>

        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Business Analytics</h2>
                <div class="flex items-center space-x-4">
                    <button onclick="refreshAnalytics()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                        <i class="fas fa-sync-alt"></i>
                        <span>Refresh</span>
                    </button>
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        <span id="current-date"></span>
                    </div>
                </div>
            </div>

            <!-- Analytics Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-chart-line text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Revenue Growth</p>
                            <p class="text-2xl font-semibold <?php echo $revenue_growth >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo $revenue_growth >= 0 ? '+' : ''; ?><?php echo number_format($revenue_growth, 1); ?>%
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-users text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Occupancy Rate</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($dashboard_stats['occupancy_rate'], 1); ?>%</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-star text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Guest Satisfaction</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($guest_satisfaction, 1); ?>/5</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-chart-pie text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Average Room Rate</p>
                            <p class="text-2xl font-semibold text-gray-900">$<?php echo number_format($avg_room_rate, 0); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Revenue Chart -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Revenue Trends (Last 30 Days)</h3>
                    <div class="h-64 relative">
                        <div id="revenueChartLoading" class="absolute inset-0 flex items-center justify-center bg-gray-50 rounded-lg">
                            <div class="text-center">
                                <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                                <p class="text-gray-500">Loading revenue data...</p>
                            </div>
                        </div>
                        <canvas id="revenueChart" class="hidden"></canvas>
                    </div>
                </div>

                <!-- Occupancy Chart -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Occupancy Analysis (Last 30 Days)</h3>
                    <div class="h-64 relative">
                        <div id="occupancyChartLoading" class="absolute inset-0 flex items-center justify-center bg-gray-50 rounded-lg">
                            <div class="text-center">
                                <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                                <p class="text-gray-500">Loading occupancy data...</p>
                            </div>
                        </div>
                        <canvas id="occupancyChart" class="hidden"></canvas>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Performance Metrics</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-bed text-blue-600 text-xl"></i>
                        </div>
                        <h4 class="font-semibold text-gray-800">Room Utilization</h4>
                        <p class="text-2xl font-bold text-blue-600"><?php echo number_format($dashboard_stats['occupancy_rate'], 1); ?>%</p>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-clock text-green-600 text-xl"></i>
                        </div>
                        <h4 class="font-semibold text-gray-800">Today's Revenue</h4>
                        <p class="text-2xl font-bold text-green-600">$<?php echo number_format($dashboard_stats['today_revenue'], 0); ?></p>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-utensils text-purple-600 text-xl"></i>
                        </div>
                        <h4 class="font-semibold text-gray-800">Total Rooms</h4>
                        <p class="text-2xl font-bold text-purple-600"><?php echo $dashboard_stats['total_rooms']; ?></p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Include footer -->
        <?php include '../../includes/footer.php'; ?>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Update current date
            document.getElementById('current-date').textContent = new Date().toLocaleDateString();

            // Initialize charts when page loads
            document.addEventListener('DOMContentLoaded', function() {
                loadRevenueChart();
                loadOccupancyChart();
            });

            // Refresh analytics data
            function refreshAnalytics() {
                // Show loading state
                const refreshBtn = document.querySelector('button[onclick="refreshAnalytics()"]');
                const originalText = refreshBtn.innerHTML;
                refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Refreshing...</span>';
                refreshBtn.disabled = true;

                // Reload the page to get fresh data
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }

            // Load Revenue Chart
            async function loadRevenueChart() {
                try {
                    const response = await fetch('../../api/get-revenue-data.php');
                    const data = await response.json();
                    
                    if (data.success) {
                        // Hide loading and show chart
                        document.getElementById('revenueChartLoading').classList.add('hidden');
                        document.getElementById('revenueChart').classList.remove('hidden');
                        
                        const ctx = document.getElementById('revenueChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: data.data.map(item => new Date(item.date).toLocaleDateString()),
                                datasets: [{
                                    label: 'Daily Revenue',
                                    data: data.data.map(item => item.revenue),
                                    borderColor: 'rgb(59, 130, 246)',
                                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                    tension: 0.4,
                                    fill: true
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(value) {
                                                return '$' + value.toLocaleString();
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    } else {
                        // Show error message
                        document.getElementById('revenueChartLoading').innerHTML = 
                            '<div class="text-center"><i class="fas fa-exclamation-triangle text-2xl text-red-400 mb-2"></i><p class="text-red-500">Error loading revenue data</p></div>';
                    }
                } catch (error) {
                    console.error('Error loading revenue chart:', error);
                    document.getElementById('revenueChartLoading').innerHTML = 
                        '<div class="text-center"><i class="fas fa-exclamation-triangle text-2xl text-red-400 mb-2"></i><p class="text-red-500">Error loading revenue data</p></div>';
                }
            }

            // Load Occupancy Chart
            async function loadOccupancyChart() {
                try {
                    const response = await fetch('../../api/get-occupancy-data.php');
                    const data = await response.json();
                    
                    if (data.success) {
                        // Hide loading and show chart
                        document.getElementById('occupancyChartLoading').classList.add('hidden');
                        document.getElementById('occupancyChart').classList.remove('hidden');
                        
                        const ctx = document.getElementById('occupancyChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: data.data.map(item => new Date(item.date).toLocaleDateString()),
                                datasets: [{
                                    label: 'Occupancy Rate (%)',
                                    data: data.data.map(item => item.occupancy_rate),
                                    backgroundColor: 'rgba(34, 197, 94, 0.8)',
                                    borderColor: 'rgb(34, 197, 94)',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        max: 100,
                                        ticks: {
                                            callback: function(value) {
                                                return value + '%';
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    } else {
                        // Show error message
                        document.getElementById('occupancyChartLoading').innerHTML = 
                            '<div class="text-center"><i class="fas fa-exclamation-triangle text-2xl text-red-400 mb-2"></i><p class="text-red-500">Error loading occupancy data</p></div>';
                    }
                } catch (error) {
                    console.error('Error loading occupancy chart:', error);
                    document.getElementById('occupancyChartLoading').innerHTML = 
                        '<div class="text-center"><i class="fas fa-exclamation-triangle text-2xl text-red-400 mb-2"></i><p class="text-red-500">Error loading occupancy data</p></div>';
                }
            }
        </script>
    </body>
</html>
