<?php
// Use training session bridge to support both Booking and POS users
require_once __DIR__ . '/training-session-bridge.php';

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// $user_id, $user_name are already set by training-session-bridge.php

// Get user training progress
$progress_data = getUserTrainingProgress($user_id);
$stats = $progress_data['stats'] ?? ['total_attempts' => 0, 'completed_attempts' => 0, 'avg_score' => 0, 'total_minutes' => 0];
$recent_activity = $progress_data['recent_activity'] ?? [];
$certificates = $progress_data['certificates'] ?? [];

// Calculate additional metrics
$completion_rate = $stats['total_attempts'] > 0 ? round(($stats['completed_attempts'] / $stats['total_attempts']) * 100) : 0;
$total_hours = round(($stats['total_minutes'] ?? 0) / 60, 1);

// Get dynamic score distribution
$score_distribution = getScoreDistribution($user_id) ?? [];

// Get progress over time data
$progress_over_time = getProgressOverTime($user_id) ?? [];

// Get scenario-specific progress
$scenario_progress = getScenarioSpecificProgress($user_id) ?? [];

// Get leaderboard data
$leaderboard = [];
try {
    $stmt = $pdo->query("
        SELECT 
            u.user_name,
            COUNT(ta.id) as total_attempts,
            COUNT(CASE WHEN ta.status = 'completed' THEN 1 END) as completed_attempts,
            AVG(CASE WHEN ta.status = 'completed' THEN ta.score END) as avg_score,
            SUM(CASE WHEN ta.status = 'completed' THEN ta.duration_minutes ELSE 0 END) as total_minutes
        FROM users u
        LEFT JOIN training_attempts ta ON u.user_id = ta.user_id
        WHERE u.user_role IN ('front_desk', 'housekeeping', 'manager')
        GROUP BY u.user_id, u.user_name
        HAVING total_attempts > 0
        ORDER BY avg_score DESC, completed_attempts DESC
        LIMIT 10
    ");
    $leaderboard = $stmt->fetchAll();
} catch (Exception $e) {
    // Handle error silently
}

$page_title = 'Staff Progress';
include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';
?>

        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <!-- Page Header -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                <h1 class="text-2xl font-bold text-gray-900">Staff Progress</h1>
                <p class="text-gray-600 mt-1">Track your training progress and achievements</p>
                    </div>
            <div class="flex items-center space-x-4">
                <div class="flex space-x-2">
                    <button onclick="refreshProgressData()" class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors text-sm">
                        <i class="fas fa-sync-alt mr-1"></i>Refresh
                        </button>
                    <button onclick="exportProgressReport()" class="px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors text-sm">
                        <i class="fas fa-download mr-1"></i>Export
                        </button>
                    </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Welcome back,</p>
                    <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($user_name); ?></p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full flex items-center justify-center">
                    <i class="fas fa-chart-line text-white text-xl"></i>
                </div>
                    </div>
                </div>
            </div>

    <!-- Progress Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm border p-6">
                    <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-play-circle text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Attempts</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $stats['total_attempts']; ?></p>
                        </div>
                    </div>
                </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
                    <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Completed</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $stats['completed_attempts']; ?></p>
                        </div>
                    </div>
                </div>
        
        <div class="bg-white rounded-lg shadow-sm border p-6">
                    <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-star text-yellow-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Average Score</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo round($stats['avg_score']); ?>%</p>
                        </div>
                    </div>
                </div>
        
        <div class="bg-white rounded-lg shadow-sm border p-6">
                    <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Time Spent</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $total_hours; ?>h</p>
                        </div>
                    </div>
                </div>
            </div>

    <!-- Progress Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Completion Rate -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Completion Rate</h3>
            <div class="flex items-center justify-center">
                <div class="relative w-32 h-32">
                    <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-gray-200" stroke="currentColor" stroke-width="3" fill="none"
                              d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        <path class="text-blue-600" stroke="currentColor" stroke-width="3" fill="none"
                              stroke-dasharray="<?php echo $completion_rate; ?>, 100"
                              d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-2xl font-bold text-gray-900"><?php echo $completion_rate; ?>%</span>
                            </div>
                        </div>
                    </div>
            <p class="text-center text-sm text-gray-600 mt-4"><?php echo $stats['completed_attempts']; ?> of <?php echo $stats['total_attempts']; ?> scenarios completed</p>
            </div>

        <!-- Score Distribution -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Score Distribution</h3>
            <div class="space-y-3">
                <?php 
                $ranges = [
                    ['min' => 90, 'max' => 100, 'label' => '90-100%', 'color' => 'green', 'class' => 'bg-green-500'],
                    ['min' => 80, 'max' => 89, 'label' => '80-89%', 'color' => 'blue', 'class' => 'bg-blue-500'],
                    ['min' => 70, 'max' => 79, 'label' => '70-79%', 'color' => 'yellow', 'class' => 'bg-yellow-500'],
                    ['min' => 0, 'max' => 69, 'label' => 'Below 70%', 'color' => 'red', 'class' => 'bg-red-500']
                ];
                
                foreach ($ranges as $range): 
                    $count = $score_distribution[$range['min'] . '-' . $range['max']] ?? 0;
                    $total_scores = array_sum($score_distribution);
                    $percentage = $total_scores > 0 ? round(($count / $total_scores) * 100) : 0;
                ?>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600"><?php echo $range['label']; ?></span>
                    <div class="flex-1 mx-4">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="<?php echo $range['class']; ?> h-2 rounded-full transition-all duration-500" 
                                 style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                            </div>
                    <span class="text-sm font-medium text-gray-900"><?php echo $percentage; ?>%</span>
                        </div>
                <?php endforeach; ?>
                                </div>
            <?php if ($total_scores > 0): ?>
            <div class="mt-4 text-center text-sm text-gray-500">
                Based on <?php echo $total_scores; ?> completed attempts
                            </div>
            <?php endif; ?>
                                </div>
                            </div>

    <!-- Progress Over Time & Scenario Analysis -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Progress Over Time -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Progress Over Time</h3>
            <div class="h-64 flex items-center justify-center">
                <canvas id="progressChart" width="400" height="200"></canvas>
                        </div>
            <div class="mt-4 text-center text-sm text-gray-500">
                Last 30 days performance trend
                    </div>
                </div>

        <!-- Scenario Analysis -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Scenario Performance</h3>
                    <div class="space-y-4">
                <?php if (empty($scenario_progress)): ?>
                    <div class="text-center py-8">
                        <i class="fas fa-chart-bar text-gray-400 text-3xl mb-3"></i>
                        <p class="text-gray-500">No scenario data available</p>
                        <p class="text-sm text-gray-400">Complete some scenarios to see performance analysis</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($scenario_progress as $scenario): ?>
                        <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                <h4 class="font-medium text-gray-900"><?php echo htmlspecialchars($scenario['title']); ?></h4>
                                <span class="text-sm font-bold text-gray-900"><?php echo round($scenario['avg_score']); ?>%</span>
                                    </div>
                            <div class="flex items-center justify-between text-sm text-gray-600">
                                <span><?php echo $scenario['attempts']; ?> attempts</span>
                                <span><?php echo $scenario['best_score']; ?>% best</span>
                                <span class="px-2 py-1 text-xs rounded-full <?php echo $scenario['avg_score'] >= 80 ? 'bg-green-100 text-green-800' : ($scenario['avg_score'] >= 60 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                    <?php echo $scenario['avg_score'] >= 80 ? 'Excellent' : ($scenario['avg_score'] >= 60 ? 'Good' : 'Needs Improvement'); ?>
                                </span>
                                </div>
                            <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-2 rounded-full transition-all duration-500" 
                                     style="width: <?php echo min(100, $scenario['avg_score']); ?>%"></div>
                                </div>
                            </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                    </div>
                </div>
            </div>

    <!-- Recent Activity & Certificates -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
            <div class="space-y-4">
                <?php if (empty($recent_activity)): ?>
                    <div class="text-center py-8">
                        <i class="fas fa-history text-gray-400 text-3xl mb-3"></i>
                        <p class="text-gray-500">No recent activity</p>
                        <p class="text-sm text-gray-400">Start a training scenario to see your progress here</p>
                                        </div>
                <?php else: ?>
                    <?php foreach ($recent_activity as $activity): ?>
                        <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-play text-blue-600"></i>
                                        </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($activity['scenario_title']); ?></p>
                                <p class="text-xs text-gray-500">
                                    <?php echo ucfirst($activity['scenario_type']); ?> • 
                                    <?php echo date('M j, Y', strtotime($activity['created_at'])); ?>
                                </p>
                                    </div>
                                    <div class="text-right">
                                <p class="text-sm font-bold text-gray-900"><?php echo $activity['score']; ?>%</p>
                                <p class="text-xs text-gray-500"><?php echo ucfirst($activity['status']); ?></p>
                                    </div>
                                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                                        </div>
                </div>

        <!-- Certificates -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Certificates Earned</h3>
            <div class="space-y-4">
                <?php if (empty($certificates)): ?>
                    <div class="text-center py-8">
                        <i class="fas fa-certificate text-gray-400 text-3xl mb-3"></i>
                        <p class="text-gray-500">No certificates yet</p>
                        <p class="text-sm text-gray-400">Complete scenarios with high scores to earn certificates</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($certificates as $certificate): ?>
                        <div class="flex items-center space-x-4 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-certificate text-yellow-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($certificate['scenario_title']); ?></p>
                                <p class="text-xs text-gray-500">
                                    <?php echo ucfirst($certificate['scenario_type']); ?> • 
                                    <?php echo date('M j, Y', strtotime($certificate['earned_at'])); ?>
                                </p>
                                    </div>
                                    <div class="text-right">
                                <p class="text-sm font-bold text-gray-900"><?php echo $certificate['score']; ?>%</p>
                                <button onclick="downloadCertificate(<?php echo $certificate['id']; ?>)" 
                                        class="text-xs text-blue-600 hover:text-blue-800">
                                    Download
                                </button>
                                    </div>
                                </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                        </div>
                    </div>
                </div>

    <!-- Leaderboard -->
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Team Leaderboard</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completed</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Score</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time Spent</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($leaderboard)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                No leaderboard data available
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($leaderboard as $index => $member): ?>
                            <tr class="<?php echo $member['user_name'] === $user_name ? 'bg-blue-50' : ''; ?>">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php if ($index < 3): ?>
                                        <i class="fas fa-trophy text-yellow-500 mr-2"></i>
                                    <?php endif; ?>
                                    #<?php echo $index + 1; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($member['user_name']); ?>
                                    <?php if ($member['user_name'] === $user_name): ?>
                                        <span class="ml-2 px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">You</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $member['completed_attempts']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo round($member['avg_score']); ?>%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo round($member['total_minutes'] / 60, 1); ?>h
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
                                    </div>
                                </div>
        </main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
/* Export Modal Styles */
.export-modal {
    animation: fadeIn 0.3s ease-out;
}

.export-modal .modal-content {
    animation: slideIn 0.3s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideIn {
    from { 
        opacity: 0;
        transform: translateY(-20px);
    }
    to { 
        opacity: 1;
        transform: translateY(0);
    }
}

.export-format-btn {
    transition: all 0.2s ease;
}

.export-format-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.export-format-btn:active {
    transform: translateY(0);
}
</style>
    <script>
// Progress chart data
const progressData = <?php echo json_encode($progress_over_time); ?>;
const scoreDistribution = <?php echo json_encode($score_distribution); ?>;

// Initialize progress chart
document.addEventListener('DOMContentLoaded', function() {
    initializeProgressChart();
    initializeInteractiveFeatures();
});

function initializeProgressChart() {
    const chartElement = document.getElementById('progressChart');
    if (!chartElement) {
        console.warn('Progress chart element not found');
        return;
    }
    
    const ctx = chartElement.getContext('2d');
    
    // Prepare data for the last 30 days
    const labels = [];
    const scores = [];
    const today = new Date();
    
    for (let i = 29; i >= 0; i--) {
        const date = new Date(today);
        date.setDate(date.getDate() - i);
        labels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
        
        const dateStr = date.toISOString().split('T')[0];
        const dayData = progressData.find(d => d.date === dateStr);
        scores.push(dayData ? dayData.avg_score : null);
    }
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Average Score',
                data: scores,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: 'rgb(59, 130, 246)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4
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
                },
                x: {
                    ticks: {
                        maxTicksLimit: 10
                    }
                }
            },
            elements: {
                point: {
                    hoverRadius: 6
                }
            }
        }
    });
}

function initializeInteractiveFeatures() {
    // Add hover effects to progress cards
    const progressCards = document.querySelectorAll('.bg-white.rounded-lg.shadow-sm.border');
    progressCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.classList.add('shadow-lg', 'transform', 'scale-105');
        });
        
        card.addEventListener('mouseleave', function() {
            this.classList.remove('shadow-lg', 'transform', 'scale-105');
        });
    });
    
    // Add click handlers for scenario analysis
    const scenarioCards = document.querySelectorAll('[data-scenario-id]');
    scenarioCards.forEach(card => {
        card.addEventListener('click', function() {
            const scenarioId = this.dataset.scenarioId;
            showScenarioDetails(scenarioId);
            });
        });
}

function downloadCertificate(certificateId) {
    // Show loading state
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Downloading...';
    button.disabled = true;
    
    // Open certificate download in new window
    const downloadWindow = window.open(`../../modules/training/download-certificate.php?id=${certificateId}`, '_blank');
    
    // Reset button state after a delay
    setTimeout(() => {
        button.textContent = originalText;
        button.disabled = false;
    }, 2000);
}

function showScenarioDetails(scenarioId) {
    // This would open a modal with detailed scenario analysis
    alert('Scenario details for ID: ' + scenarioId);
}

function refreshProgressData() {
    // Refresh the page to get updated data
    location.reload();
}

function exportProgressReport() {
    // Show loading state
    const exportBtn = event.target;
    const originalText = exportBtn.innerHTML;
    exportBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Exporting...';
    exportBtn.disabled = true;
    
    try {
        // Generate comprehensive progress report
        const reportData = {
            user: {
                name: '<?php echo addslashes($user_name); ?>',
                id: <?php echo $user_id; ?>
            },
            stats: <?php echo json_encode($stats); ?>,
            scoreDistribution: scoreDistribution,
            progressOverTime: progressData,
            scenarioProgress: <?php echo json_encode($scenario_progress); ?>,
            recentActivity: <?php echo json_encode($recent_activity); ?>,
            certificates: <?php echo json_encode($certificates); ?>,
            reportInfo: {
                generatedAt: new Date().toISOString(),
                generatedBy: 'Hotel PMS Training System',
                version: '1.0'
            }
        };
        
        // Create multiple export formats
        const formats = [
            {
                name: 'JSON',
                data: JSON.stringify(reportData, null, 2),
                mimeType: 'application/json',
                extension: 'json'
            },
            {
                name: 'CSV',
                data: generateCSVReport(reportData),
                mimeType: 'text/csv',
                extension: 'csv'
            },
            {
                name: 'HTML',
                data: generateHTMLReport(reportData),
                mimeType: 'text/html',
                extension: 'html'
            }
        ];
        
        // Show format selection dialog
        showExportFormatDialog(formats, reportData);
        
    } catch (error) {
        console.error('Export error:', error);
        alert('Error generating export: ' + error.message);
    } finally {
        // Reset button state
        exportBtn.innerHTML = originalText;
        exportBtn.disabled = false;
    }
}

function generateCSVReport(data) {
    let csv = 'Training Progress Report\n';
    csv += `User,${data.user.name}\n`;
    csv += `Generated,${data.reportInfo.generatedAt}\n\n`;
    
    // Stats section
    csv += 'Overall Statistics\n';
    csv += 'Metric,Value\n';
    csv += `Total Attempts,${data.stats.total_attempts}\n`;
    csv += `Completed Attempts,${data.stats.completed_attempts}\n`;
    csv += `Average Score,${Math.round(data.stats.avg_score)}%\n`;
    csv += `Total Time Spent,${Math.round(data.stats.total_minutes / 60, 1)} hours\n\n`;
    
    // Score distribution
    csv += 'Score Distribution\n';
    csv += 'Range,Count,Percentage\n';
    const totalScores = Object.values(data.scoreDistribution).reduce((a, b) => a + b, 0);
    Object.entries(data.scoreDistribution).forEach(([range, count]) => {
        const percentage = totalScores > 0 ? Math.round((count / totalScores) * 100) : 0;
        csv += `${range},${count},${percentage}%\n`;
    });
    csv += '\n';
    
    // Scenario progress
    csv += 'Scenario Performance\n';
    csv += 'Scenario,Attempts,Average Score,Best Score,Status\n';
    data.scenarioProgress.forEach(scenario => {
        const status = scenario.avg_score >= 80 ? 'Excellent' : (scenario.avg_score >= 60 ? 'Good' : 'Needs Improvement');
        csv += `"${scenario.title}",${scenario.attempts},${Math.round(scenario.avg_score)}%,${scenario.best_score}%,${status}\n`;
    });
    
    return csv;
}

function generateHTMLReport(data) {
    const html = `
    <!DOCTYPE html>
    <html>
    <head>
        <title>Training Progress Report - ${data.user.name}</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
            .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .header { text-align: center; border-bottom: 2px solid #007bff; padding-bottom: 20px; margin-bottom: 30px; }
            .section { margin-bottom: 30px; }
            .section h2 { color: #007bff; border-left: 4px solid #007bff; padding-left: 10px; }
            .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
            .stat-card { background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; }
            .stat-value { font-size: 24px; font-weight: bold; color: #007bff; }
            .stat-label { color: #666; font-size: 14px; }
            table { width: 100%; border-collapse: collapse; margin: 15px 0; }
            th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background: #f8f9fa; font-weight: bold; }
            .status-excellent { color: #28a745; font-weight: bold; }
            .status-good { color: #ffc107; font-weight: bold; }
            .status-needs-improvement { color: #dc3545; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Training Progress Report</h1>
                <p><strong>User:</strong> ${data.user.name}</p>
                <p><strong>Generated:</strong> ${new Date(data.reportInfo.generatedAt).toLocaleString()}</p>
            </div>
            
            <div class="section">
                <h2>Overall Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value">${data.stats.total_attempts}</div>
                        <div class="stat-label">Total Attempts</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">${data.stats.completed_attempts}</div>
                        <div class="stat-label">Completed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">${Math.round(data.stats.avg_score)}%</div>
                        <div class="stat-label">Average Score</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">${Math.round(data.stats.total_minutes / 60, 1)}h</div>
                        <div class="stat-label">Time Spent</div>
                        </div>
                </div>
            </div>

            <div class="section">
                <h2>Scenario Performance</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Scenario</th>
                            <th>Attempts</th>
                            <th>Average Score</th>
                            <th>Best Score</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.scenarioProgress.map(scenario => `
                            <tr>
                                <td>${scenario.title}</td>
                                <td>${scenario.attempts}</td>
                                <td>${Math.round(scenario.avg_score)}%</td>
                                <td>${scenario.best_score}%</td>
                                <td class="status-${scenario.avg_score >= 80 ? 'excellent' : (scenario.avg_score >= 60 ? 'good' : 'needs-improvement')}">
                                    ${scenario.avg_score >= 80 ? 'Excellent' : (scenario.avg_score >= 60 ? 'Good' : 'Needs Improvement')}
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
            
            <div class="section">
                <h2>Recent Activity</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Scenario</th>
                            <th>Score</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.recentActivity.map(activity => `
                            <tr>
                                <td>${activity.scenario_title}</td>
                                <td>${activity.score}%</td>
                                <td>${activity.status}</td>
                                <td>${new Date(activity.created_at).toLocaleDateString()}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
</body>
</html>
    `;
    return html;
}

function showExportFormatDialog(formats, data) {
    // Create modal dialog
    const modal = document.createElement('div');
    modal.className = 'export-modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="modal-content bg-white rounded-lg p-8 max-w-md w-full mx-4 shadow-xl">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-download text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Export Progress Report</h3>
                <p class="text-gray-600">Choose your preferred export format</p>
                        </div>
            <div class="space-y-3">
                ${formats.map(format => `
                    <button onclick="downloadReport('${format.name}', '${format.mimeType}', '${format.extension}')" 
                            class="export-format-btn w-full px-4 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-left flex items-center">
                        <i class="fas fa-file-${format.extension === 'json' ? 'code' : format.extension === 'csv' ? 'csv' : 'code'} mr-3"></i>
                        <div>
                            <div class="font-medium">Export as ${format.name}</div>
                            <div class="text-sm text-blue-100">Download ${format.name.toLowerCase()} file</div>
                        </div>
                    </button>
                `).join('')}
                        </div>
            <div class="mt-6 flex justify-end">
                <button onclick="closeExportDialog()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                    <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                </div>
            </div>
    `;
    
    document.body.appendChild(modal);
    
    // Store data globally for download functions
    window.exportData = data;
    window.exportFormats = formats;
    window.exportModal = modal;
    
    // Add click outside to close
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeExportDialog();
        }
    });
    
    // Add escape key to close
    const escapeHandler = function(e) {
        if (e.key === 'Escape') {
            closeExportDialog();
            document.removeEventListener('keydown', escapeHandler);
        }
    };
    document.addEventListener('keydown', escapeHandler);
}

function downloadReport(formatName, mimeType, extension) {
    const format = window.exportFormats.find(f => f.name === formatName);
    if (!format) return;
    
    try {
        const dataBlob = new Blob([format.data], { type: mimeType });
        const url = URL.createObjectURL(dataBlob);
        
        const link = document.createElement('a');
        link.href = url;
        link.download = `progress_report_${new Date().toISOString().split('T')[0]}.${extension}`;
        link.click();
        
        URL.revokeObjectURL(url);
        closeExportDialog();
        
        // Show success notification
        showNotification(`Progress report exported successfully as ${formatName}`, 'success');
        
    } catch (error) {
        console.error('Export error:', error);
        showNotification('Error exporting report. Please try again.', 'error');
    }
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} mr-2"></i>
            <span>${message}</span>
    </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

function closeExportDialog() {
    if (window.exportModal) {
        document.body.removeChild(window.exportModal);
        window.exportModal = null;
    }
}

// Add keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey || e.metaKey) {
        switch(e.key) {
            case 'r':
                e.preventDefault();
                refreshProgressData();
                break;
            case 'e':
                e.preventDefault();
                exportProgressReport();
                break;
        }
    }
        });
    </script>

<?php include '../../includes/footer.php'; ?>