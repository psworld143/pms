<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    header('Location: ../../login.php');
    exit();
}

$page_title = 'Analytics Dashboard';

$stats = getDashboardStats();
$recentActivities = getRecentActivities(10);

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
                    <span>Last updated: <?php echo date('M j, Y g:i A'); ?></span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm text-gray-500">Occupancy Rate</span>
                        <span class="text-xs px-2 py-1 rounded-full bg-indigo-50 text-indigo-600">Live</span>
                    </div>
                    <div class="flex items-baseline">
                        <span class="text-3xl font-semibold text-gray-900"><?php echo number_format($stats['occupancy_rate'], 1); ?>%</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Target: 75% average</p>
                </div>

                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm text-gray-500">Today's Revenue</span>
                        <span class="text-xs px-2 py-1 rounded-full bg-green-50 text-green-600">PHP</span>
                    </div>
                    <div class="flex items-baseline">
                        <span class="text-3xl font-semibold text-gray-900">₱<?php echo number_format($stats['today_revenue'], 2); ?></span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Includes room and service charges.</p>
                </div>

                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm text-gray-500">Total Rooms</span>
                        <span class="text-xs px-2 py-1 rounded-full bg-blue-50 text-blue-600">Inventory</span>
                    </div>
                    <div class="flex items-baseline">
                        <span class="text-3xl font-semibold text-gray-900"><?php echo number_format($stats['total_rooms']); ?></span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Configured in room management.</p>
                </div>

                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm text-gray-500">Guests Checked In</span>
                        <span class="text-xs px-2 py-1 rounded-full bg-purple-50 text-purple-600">Operations</span>
                    </div>
                    <div class="flex items-baseline">
                        <span class="text-3xl font-semibold text-gray-900"><?php echo number_format($stats['occupied_rooms']); ?></span>
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
                                <tr class="border-b">
                                    <td class="py-2">Group Bookings</td>
                                    <td>68%</td>
                                    <td>₱6,200</td>
                                    <td>₱4,216</td>
                                    <td>35%</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="py-2">Corporate</td>
                                    <td>72%</td>
                                    <td>₱5,800</td>
                                    <td>₱4,176</td>
                                    <td>28%</td>
                                </tr>
                                <tr>
                                    <td class="py-2">Leisure</td>
                                    <td>64%</td>
                                    <td>₱4,950</td>
                                    <td>₱3,168</td>
                                    <td>37%</td>
                                </tr>
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
                                <span>78%</span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-gray-100">
                                <div class="h-2 rounded-full bg-green-500" style="width: 78%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between items-center text-sm text-gray-600 mb-1">
                                <span>Response Time</span>
                                <span>2.5 hrs</span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-gray-100">
                                <div class="h-2 rounded-full bg-blue-500" style="width: 65%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between items-center text-sm text-gray-600 mb-1">
                                <span>Resolved Escalations</span>
                                <span>92%</span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-gray-100">
                                <div class="h-2 rounded-full bg-purple-500" style="width: 92%"></div>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Top Improvement Drivers</h4>
                            <ul class="text-xs text-gray-600 space-y-1">
                                <li>• Housekeeping response time</li>
                                <li>• Breakfast menu variety</li>
                                <li>• Mobile check-in feedback</li>
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
                        <?php if (empty($recentActivities)): ?>
                            <p class="text-sm text-gray-500">No recent actions recorded.</p>
                        <?php else: ?>
                            <?php foreach ($recentActivities as $activity): ?>
                                <div class="border rounded-lg px-4 py-3">
                                    <div class="flex justify-between text-sm">
                                        <span class="font-medium text-gray-800"><?php echo htmlspecialchars($activity['user_name']); ?></span>
                                        <span class="text-gray-500"><?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?></span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">Action: <span class="font-medium text-gray-800"><?php echo htmlspecialchars($activity['action']); ?></span></p>
                                    <?php if (!empty($activity['details'])): ?>
                                        <p class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($activity['details']); ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Automation Summary</h3>
                        <a href="settings.php" class="text-sm text-primary hover:underline">Configure</a>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-700">Notification Rules</p>
                                <p class="text-xs text-gray-500">Real-time alerts for overbooking, VIP arrivals, and payment delays.</p>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-600">Active</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-700">Revenue Targets</p>
                                <p class="text-xs text-gray-500">Compares actual revenue vs. forecast with automated nudges.</p>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-600">Monitoring</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-700">Housekeeping SLA</p>
                                <p class="text-xs text-gray-500">Tracks cleaning turnaround thresholds with workforce alerts.</p>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-600">Stable</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-700">Guest Feedback Loop</p>
                                <p class="text-xs text-gray-500">Aggregates feedback and assigns follow-up tasks automatically.</p>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-600">Review</span>
                        </div>
                    </div>
                </div>
            </div>

        </main>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const loaders = document.querySelectorAll('.chart-loading');
            const revenueCanvas = document.getElementById('analyticsRevenueChart');
            const occupancyCanvas = document.getElementById('analyticsOccupancyChart');

            function showChart(canvas, loader) {
                if (loader) loader.classList.add('hidden');
                if (canvas) canvas.classList.remove('hidden');
            }

            function loadChart(canvasId, loaderEl, endpoint, label, color) {
                if (!canvasId) return;
                fetch(endpoint)
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success || !data.data) {
                            throw new Error('Failed to load data');
                        }
                        showChart(canvasId, loaderEl);
                        const ctx = canvasId.getContext('2d');
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: data.data.map(item => new Date(item.date).toLocaleDateString()),
                                datasets: [{
                                    label,
                                    data: data.data.map(item => item.value ?? item.occupancy_rate ?? item.revenue),
                                    borderColor: color,
                                    backgroundColor: color.replace('1)', '0.1)'),
                                    fill: true,
                                    tension: 0.3
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } }
                            }
                        });
                    })
                    .catch(() => {
                        if (loaderEl) {
                            loaderEl.innerHTML = '<div class="text-center text-sm text-red-500">Unable to load chart data</div>';
                        }
                    });
            }

            const [revenueLoader, occupancyLoader] = loaders;
            loadChart(revenueCanvas, revenueLoader, '../../api/get-revenue-data.php', 'Revenue', 'rgba(59,130,246,1)');
            loadChart(occupancyCanvas, occupancyLoader, '../../api/get-occupancy-data.php', 'Occupancy Rate', 'rgba(16,185,129,1)');

            document.querySelectorAll('.refresh-chart').forEach(btn => {
                btn.addEventListener('click', () => {
                    const chartType = btn.getAttribute('data-chart');
                    if (chartType === 'revenue') {
                        revenueLoader.classList.remove('hidden');
                        revenueCanvas.classList.add('hidden');
                        loadChart(revenueCanvas, revenueLoader, '../../api/get-revenue-data.php', 'Revenue', 'rgba(59,130,246,1)');
                    } else if (chartType === 'occupancy') {
                        occupancyLoader.classList.remove('hidden');
                        occupancyCanvas.classList.add('hidden');
                        loadChart(occupancyCanvas, occupancyLoader, '../../api/get-occupancy-data.php', 'Occupancy Rate', 'rgba(16,185,129,1)');
                    }
                });
            });
        });
        </script>

<?php include '../../includes/footer.php'; ?>
