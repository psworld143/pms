<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/booking-paths.php';

booking_initialize_paths();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    header('Location: ' . booking_base() . 'login.php');
    exit();
}

$page_title = 'Analytics Dashboard';

$stats = getDashboardStats();
$recentActivities = getRecentActivities(10);
$breakdown = getRevenueBreakdown(30);
$sentiment = getGuestSentimentMetrics(90);

$automationCards = [
    [
        'title' => 'Notification Rules',
        'description' => 'Real-time alerts for overbooking, VIP arrivals, and payment delays.',
        'status' => 'Active'
    ],
    [
        'title' => 'Revenue Targets',
        'description' => 'Compare actual revenue vs. forecast with automated nudges.',
        'status' => 'Monitoring'
    ],
    [
        'title' => 'Housekeeping SLA',
        'description' => 'Track cleaning turnaround thresholds with workforce alerts.',
        'status' => 'Stable'
    ],
    [
        'title' => 'Guest Feedback Loop',
        'description' => 'Aggregate feedback and assign follow-up tasks automatically.',
        'status' => 'Review'
    ],
];

$asset_version = time();
$additional_js = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>' . "\n";
$additional_js .= '<script>window.analyticsDashboardBootstrap = ' . json_encode([
    'revenueBreakdown' => $breakdown,
    'guestSentiment' => $sentiment,
    'automation' => $automationCards,
]) . ';</script>' . "\n";
$additional_js .= '<script src="' . booking_url('assets/js/main.js?v=' . $asset_version) . '"></script>' . "\n";
$additional_js .= '<script src="' . booking_url('assets/js/analytics-dashboard.js?v=' . $asset_version) . '"></script>';

include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';
?>

        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-8">
                <div>
                    <h2 class="text-3xl font-semibold text-gray-800">Business Intelligence Overview</h2>
                    <p class="text-gray-500 text-sm">Holistic view of hotel performance across revenue, occupancy, and guest sentiment.</p>
                </div>
                <div class="flex items-center space-x-2 text-sm text-gray-500">
                    <i class="fas fa-clock"></i>
                    <span>Last updated: <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo date('M j, Y g:i A'); ?></span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm text-gray-500">Occupancy Rate</span>
                        <span class="text-xs px-2 py-1 rounded-full bg-indigo-50 text-indigo-600">Live</span>
                    </div>
                    <div class="flex items-baseline">
                        <span class="text-3xl font-semibold text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format($stats['occupancy_rate'], 1); ?>%</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Target: 75% average</p>
                </div>

                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm text-gray-500">Today's Revenue</span>
                        <span class="text-xs px-2 py-1 rounded-full bg-green-50 text-green-600">PHP</span>
                    </div>
                    <div class="flex items-baseline">
                        <span class="text-3xl font-semibold text-gray-900">₱<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format($stats['today_revenue'], 2); ?></span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Includes room and service charges.</p>
                </div>

                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm text-gray-500">Total Rooms</span>
                        <span class="text-xs px-2 py-1 rounded-full bg-blue-50 text-blue-600">Inventory</span>
                    </div>
                    <div class="flex items-baseline">
                        <span class="text-3xl font-semibold text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format($stats['total_rooms']); ?></span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Configured in room management.</p>
                </div>

                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm text-gray-500">Guests Checked In</span>
                        <span class="text-xs px-2 py-1 rounded-full bg-purple-50 text-purple-600">Operations</span>
                    </div>
                    <div class="flex items-baseline">
                        <span class="text-3xl font-semibold text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format($stats['occupied_rooms']); ?></span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Live count of occupied rooms.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Revenue Performance</h3>
                        <button data-chart="revenue" class="text-sm text-primary hover:underline refresh-chart">Refresh</button>
                    </div>
                    <div class="h-72 relative">
                        <div class="chart-loading absolute inset-0 flex items-center justify-center bg-gray-50 rounded-lg">
                            <div class="text-center">
                                <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                                <p class="text-gray-500 text-sm">Loading revenue data...</p>
                            </div>
                        </div>
                        <canvas id="analyticsRevenueChart" class="hidden"></canvas>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Occupancy Trend</h3>
                        <button data-chart="occupancy" class="text-sm text-primary hover:underline refresh-chart">Refresh</button>
                    </div>
                    <div class="h-72 relative">
                        <div class="chart-loading absolute inset-0 flex items-center justify-center bg-gray-50 rounded-lg">
                            <div class="text-center">
                                <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                                <p class="text-gray-500 text-sm">Loading occupancy data...</p>
                            </div>
                        </div>
                        <canvas id="analyticsOccupancyChart" class="hidden"></canvas>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
                <div class="xl:col-span-2 bg-white rounded-xl shadow p-6">
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
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); if (empty($breakdown)): ?>
                                    <tr class="border-b">
                                        <td colspan="5" class="py-4 text-center text-gray-400">No revenue segments available.</td>
                                    </tr>
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); else: ?>
                                    <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); foreach ($breakdown as $segment): ?>
                                        <tr class="border-b last:border-b-0">
                                            <td class="py-2 font-medium text-gray-800"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($segment['segment']); ?></td>
                                            <td class="py-2 text-gray-600"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format($segment['occupancy_pct'], 1); ?>%</td>
                                            <td class="py-2 text-gray-600">₱<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format($segment['adr'], 2); ?></td>
                                            <td class="py-2 text-gray-600">₱<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format($segment['revpar'], 2); ?></td>
                                            <td class="py-2 text-gray-600"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format($segment['contribution_pct'], 1); ?>%</td>
                                        </tr>
                                    <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endforeach; ?>
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Guest Sentiment</h3>
                        <span class="text-xs px-2 py-1 rounded-full bg-amber-50 text-amber-600">Insights</span>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between items-center text-sm text-gray-600 mb-1">
                                <span>Positive Feedback</span>
                                <span id="dashboardSentimentPositive"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format($sentiment['positive_pct'], 1); ?>%</span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-gray-100">
                                <div id="dashboardSentimentPositiveBar" class="h-2 rounded-full bg-green-500" style="width: <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo max(0, min(100, $sentiment['positive_pct'])); ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between items-center text-sm text-gray-600 mb-1">
                                <span>Response Time</span>
                                <span id="dashboardSentimentResponse"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $sentiment['average_response_hours'] !== null ? number_format($sentiment['average_response_hours'], 1) . ' hrs' : '—'; ?></span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-gray-100">
                                <div id="dashboardSentimentResponseBar" class="h-2 rounded-full bg-blue-500" style="width: <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $sentiment['average_response_hours'] !== null ? max(0, min(100, ($sentiment['average_response_hours'] / 12) * 100)) : 0; ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between items-center text-sm text-gray-600 mb-1">
                                <span>Resolved Escalations</span>
                                <span id="dashboardSentimentResolved"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format($sentiment['resolved_pct'], 1); ?>%</span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-gray-100">
                                <div id="dashboardSentimentResolvedBar" class="h-2 rounded-full bg-purple-500" style="width: <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo max(0, min(100, $sentiment['resolved_pct'])); ?>%"></div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3 text-xs text-gray-500">
                            <div>Sample size: <span id="dashboardSentimentSample"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format($sentiment['sample_size']); ?></span></div>
                            <div>Avg rating: <span id="dashboardSentimentRating"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $sentiment['average_rating'] !== null ? number_format($sentiment['average_rating'], 1) . '/5' : '—'; ?></span></div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Top Improvement Drivers</h4>
                            <ul id="dashboardSentimentDrivers" class="text-xs text-gray-600 space-y-1">
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); if (empty($sentiment['top_drivers'])): ?>
                                    <li class="text-gray-400">No key drivers identified.</li>
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); else: ?>
                                    <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); foreach ($sentiment['top_drivers'] as $driver): ?>
                                        <li>• <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($driver['category']); ?> (<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo (int)$driver['count']; ?>)</li>
                                    <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endforeach; ?>
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Recent Management Actions</h3>
                        <a href="audit-log.php" class="text-sm text-primary hover:underline">View Audit Log</a>
                    </div>
                    <div class="space-y-4 max-h-72 overflow-y-auto">
                        <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); if (empty($recentActivities)): ?>
                            <p class="text-sm text-gray-500">No recent actions recorded.</p>
                        <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); else: ?>
                            <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); foreach ($recentActivities as $activity): ?>
                                <div class="border rounded-lg px-4 py-3">
                                    <div class="flex justify-between text-sm">
                                        <span class="font-medium text-gray-800"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($activity['user_name']); ?></span>
                                        <span class="text-gray-500"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?></span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">Action: <span class="font-medium text-gray-800"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($activity['action']); ?></span></p>
                                    <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); if (!empty($activity['details'])): ?>
                                        <p class="text-xs text-gray-500 mt-1"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($activity['details']); ?></p>
                                    <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endif; ?>
                                </div>
                            <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endforeach; ?>
                        <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endif; ?>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Automation Summary</h3>
                        <a href="settings.php" class="text-sm text-primary hover:underline">Configure</a>
                    </div>
                    <div class="space-y-4">
                        <div id="automationSummary">
                            <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); foreach ($automationCards as $card): ?>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-700"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($card['title']); ?></p>
                                        <p class="text-xs text-gray-500"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($card['description']); ?></p>
                                    </div>
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-700 automation-status" data-status="<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo strtolower($card['status']); ?>"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($card['status']); ?></span>
                                </div>
                            <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

        </main>

<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); include '../../includes/footer.php'; ?>
