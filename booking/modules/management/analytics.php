<?php
session_start();
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);


/**
 * Business Analytics Dashboard
 * Hotel PMS Training System for Students
 */

session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/booking-paths.php';

booking_initialize_paths();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    header('Location: ' . booking_base() . 'login.php');
    exit();
}

// Set page title
$page_title = 'Business Analytics';

// Debug session info
error_log('Analytics page - Session ID: ' . session_id());
error_log('Analytics page - User ID: ' . ($_SESSION['user_id'] ?? 'NOT SET'));
error_log('Analytics page - User Role: ' . ($_SESSION['user_role'] ?? 'NOT SET'));

// Get analytics data with error handling
try {
    $analytics_kpis = getAnalyticsKpis();
    $revenue_breakdown = getRevenueBreakdown(30);
    $guest_sentiment = getGuestSentimentMetrics(90);
} catch (Exception $e) {
    error_log('Analytics data loading error: ' . $e->getMessage());
    // Provide default data structure
    $analytics_kpis = [
        'window_days' => 30,
        'total_rooms' => 0,
        'today_revenue' => 0,
        'yesterday_revenue' => 0,
        'revenue_growth_pct' => 0,
        'today_occupancy_pct' => 0,
        'average_occupancy_pct' => 0,
        'average_room_rate' => 0,
        'returning_guests_pct' => 0,
        'guest_satisfaction_score' => null,
        'positive_feedback_pct' => 0,
        'resolved_feedback_pct' => 0,
        'average_response_hours' => null,
        'room_nights' => 0,
        'reservation_revenue' => 0,
        'feedback_sample' => 0
    ];
    $revenue_breakdown = [];
    $guest_sentiment = [
        'sample_size' => 0,
        'complaints' => 0,
        'positive_pct' => 0,
        'resolved_pct' => 0,
        'average_response_hours' => null,
        'average_rating' => null,
        'top_drivers' => []
    ];
}

$asset_version = time();
$additional_js = '
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>window.analyticsBootstrap = ' . json_encode([
        'kpis' => $analytics_kpis,
        'revenueBreakdown' => $revenue_breakdown,
        'guestSentiment' => $guest_sentiment
    ]) . ';</script>
    <script src="' . booking_url('assets/js/main.js?v=' . $asset_version) . '"></script>
    <script src="' . booking_url('assets/js/analytics.js?v=' . $asset_version) . '"></script>
';

include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';
?>

<main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
        <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Business Analytics</h2>
        <div class="flex items-center space-x-4">
            <button id="analytics-refresh" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <i class="fas fa-sync-alt"></i>
                <span>Refresh</span>
            </button>
            <div class="text-sm text-gray-600">
                <i class="fas fa-calendar-alt mr-2"></i>
                <span id="analytics-current-date"></span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Revenue Breakdown</h3>
                <select id="revenueBreakdownRange" class="border-gray-300 rounded-md text-sm">
                    <option value="30">Last 30 days</option>
                    <option value="90">Last 90 days</option>
                    <option value="180">Last 6 months</option>
                </select>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2">Segment</th>
                            <th class="py-2">Occupancy</th>
                            <th class="py-2">ADR</th>
                            <th class="py-2">RevPAR</th>
                            <th class="py-2">Contribution</th>
                        </tr>
                    </thead>
                    <tbody id="revenueBreakdownBody">
                        <tr class="border-b">
                            <td colspan="5" class="py-4 text-center text-gray-400">Loading segments...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Guest Sentiment</h3>
                <span class="text-xs px-2 py-1 rounded-full bg-amber-50 text-amber-600">Insights</span>
            </div>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between items-center text-sm text-gray-600 mb-1">
                        <span>Positive Feedback</span>
                        <span id="guestSentimentPositiveValue">—</span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-gray-100">
                        <div id="guestSentimentPositiveBar" class="h-2 rounded-full bg-green-500" style="width: 0%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between items-center text-sm text-gray-600 mb-1">
                        <span>Average Response Time</span>
                        <span id="guestSentimentResponseValue">—</span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-gray-100">
                        <div id="guestSentimentResponseBar" class="h-2 rounded-full bg-blue-500" style="width: 0%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between items-center text-sm text-gray-600 mb-1">
                        <span>Resolved Escalations</span>
                        <span id="guestSentimentResolvedValue">—</span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-gray-100">
                        <div id="guestSentimentResolvedBar" class="h-2 rounded-full bg-purple-500" style="width: 0%"></div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 text-xs text-gray-500">
                    <div>Sample size: <span id="guestSentimentSample">—</span></div>
                    <div>Avg rating: <span id="guestSentimentRating">—</span></div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Top Improvement Drivers</h4>
                    <ul id="guestSentimentDrivers" class="text-xs text-gray-600 space-y-1">
                        <li class="text-gray-400">Loading drivers...</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

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
                    <p id="analytics-revenue-growth" class="text-2xl font-semibold text-gray-900">Loading...</p>
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
                    <p id="analytics-occupancy" class="text-2xl font-semibold text-gray-900">Loading...</p>
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
                    <p id="analytics-satisfaction" class="text-2xl font-semibold text-gray-900">Loading...</p>
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
                    <p id="analytics-room-rate" class="text-2xl font-semibold text-gray-900">Loading...</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Revenue trends (Last 30 Days)</h3>
            <div class="h-64 relative">
                <div id="analytics-revenue-loading" class="absolute inset-0 flex items-center justify-center bg-gray-50 rounded-lg">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                        <p class="text-gray-500">Loading revenue data...</p>
                    </div>
                </div>
                <canvas id="analytics-revenue-chart" class="hidden"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Occupancy Analysis (Last 30 Days)</h3>
            <div class="h-64 relative">
                <div id="analytics-occupancy-loading" class="absolute inset-0 flex items-center justify-center bg-gray-50 rounded-lg">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                        <p class="text-gray-500">Loading occupancy data...</p>
                    </div>
                </div>
                <canvas id="analytics-occupancy-chart" class="hidden"></canvas>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Performance Metrics</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-bed text-blue-600 text-xl"></i>
                </div>
                <h4 class="font-semibold text-gray-800">Room Utilization</h4>
                <p id="analytics-room-utilization" class="text-2xl font-bold text-blue-600">Loading...</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-clock text-green-600 text-xl"></i>
                </div>
                <h4 class="font-semibold text-gray-800">Today's Revenue</h4>
                <p id="analytics-today-revenue" class="text-2xl font-bold text-green-600">Loading...</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-user-friends text-yellow-600 text-xl"></i>
                </div>
                <h4 class="font-semibold text-gray-800">Returning Guests</h4>
                <p id="analytics-returning-guests" class="text-2xl font-bold text-yellow-600">Loading...</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mt-8">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Recent Activity</h3>
            <button id="analytics-activity-refresh" class="text-sm text-primary hover:text-secondary">
                <i class="fas fa-sync-alt mr-2"></i>Refresh
            </button>
        </div>
        <div id="analytics-recent-activity" class="space-y-4">
            <div class="bg-gray-50 rounded-lg p-4 text-center text-gray-500">
                <i class="fas fa-spinner fa-spin mr-2"></i>
                Loading recent activity...
            </div>
        </div>
    </div>
</main>

<?php
session_start(); include '../../includes/footer.php'; ?>
