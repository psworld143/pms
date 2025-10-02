<?php
/**
 * Tutorial Analytics Dashboard
 * Hotel PMS Training System - Interactive Tutorials
 */

session_start();
require_once '../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // For testing
}

// Set page title
$page_title = 'Tutorial Analytics';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Hotel PMS Training System</title>
    <link rel="icon" type="image/png" href="../assets/images/seait-logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .analytics-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        .metric-card {
            transition: all 0.3s ease;
        }
        
        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .progress-ring {
            transform: rotate(-90deg);
        }
        
        .progress-ring-circle {
            transition: stroke-dashoffset 0.3s ease;
        }
        
        .filter-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .export-button {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .export-button:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <div class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center">
                        <a href="index.php" class="text-blue-600 hover:text-blue-800 mr-4">
                            <i class="fas fa-arrow-left"></i> Back to Tutorials
                        </a>
                        <h1 class="text-xl font-semibold text-gray-900">Tutorial Analytics</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button id="export-data" class="export-button text-white px-4 py-2 rounded-lg text-sm font-medium">
                            <i class="fas fa-download mr-2"></i>Export Data
                        </button>
                        <button id="refresh-analytics" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                            <i class="fas fa-sync-alt mr-2"></i>Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Container -->
        <div class="analytics-container py-8 px-4">
            <!-- Filters -->
            <div class="filter-section rounded-lg shadow p-6 mb-8 text-white">
                <h2 class="text-lg font-semibold mb-4">Analytics Filters</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Date Range</label>
                        <select id="date-range-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900">
                            <option value="">All Time</option>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                            <option value="quarter">This Quarter</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Module Type</label>
                        <select id="module-type-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900">
                            <option value="">All Modules</option>
                            <option value="pos">POS System</option>
                            <option value="inventory">Inventory Management</option>
                            <option value="booking">Booking System</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Difficulty Level</label>
                        <select id="difficulty-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900">
                            <option value="">All Levels</option>
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Student</label>
                        <select id="student-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900">
                            <option value="">All Students</option>
                            <!-- Student options will be loaded dynamically -->
                        </select>
                    </div>
                </div>
            </div>

            <!-- Overview Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="metric-card bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-users text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Students</p>
                            <p class="text-2xl font-semibold text-gray-900" id="total-students">Loading...</p>
                        </div>
                    </div>
                </div>

                <div class="metric-card bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-check-circle text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Completion Rate</p>
                            <p class="text-2xl font-semibold text-gray-900" id="completion-rate">Loading...</p>
                        </div>
                    </div>
                </div>

                <div class="metric-card bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Avg. Time Spent</p>
                            <p class="text-2xl font-semibold text-gray-900" id="avg-time-spent">Loading...</p>
                        </div>
                    </div>
                </div>

                <div class="metric-card bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-star text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Average Score</p>
                            <p class="text-2xl font-semibold text-gray-900" id="average-score">Loading...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Module Performance Chart -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Module Performance</h3>
                    <div class="chart-container">
                        <canvas id="module-performance-chart"></canvas>
                    </div>
                </div>

                <!-- Completion Status Chart -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Completion Status</h3>
                    <div class="chart-container">
                        <canvas id="completion-status-chart"></canvas>
                    </div>
                </div>

                <!-- Score Distribution Chart -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Score Distribution</h3>
                    <div class="chart-container">
                        <canvas id="score-distribution-chart"></canvas>
                    </div>
                </div>

                <!-- Time Analysis Chart -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Time Analysis</h3>
                    <div class="chart-container">
                        <canvas id="time-analysis-chart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Student Progress Table -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Student Progress</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Module</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time Spent</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody id="student-progress-tbody" class="bg-white divide-y divide-gray-200">
                            <!-- Student progress data will be loaded dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Global analytics state
        let analyticsData = null;
        let charts = {};
        
        // Initialize analytics
        loadAnalytics();
        
        // Filter event handlers
        $('#date-range-filter, #module-type-filter, #difficulty-filter, #student-filter').change(function() {
            loadAnalytics();
        });
        
        $('#refresh-analytics').click(function() {
            loadAnalytics();
        });
        
        $('#export-data').click(function() {
            exportAnalyticsData();
        });
        
        function loadAnalytics() {
            const filters = {
                date_range: $('#date-range-filter').val(),
                module_type: $('#module-type-filter').val(),
                difficulty: $('#difficulty-filter').val(),
                student: $('#student-filter').val()
            };
            
            $.ajax({
                url: '../api/tutorials/get-analytics.php',
                method: 'GET',
                data: filters,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        analyticsData = response.analytics;
                        updateOverviewMetrics();
                        updateCharts();
                        updateStudentProgressTable();
                        updateStudentFilter();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading analytics:', error);
                }
            });
        }
        
        function updateOverviewMetrics() {
            const overview = analyticsData.overview;
            $('#total-students').text(overview.total_students);
            $('#completion-rate').text(overview.average_completion.toFixed(1) + '%');
            $('#avg-time-spent').text(Math.round(overview.average_time_spent / 60) + ' min');
            $('#average-score').text(overview.average_score.toFixed(1) + '%');
        }
        
        function updateCharts() {
            // Module Performance Chart
            updateModulePerformanceChart();
            
            // Completion Status Chart
            updateCompletionStatusChart();
            
            // Score Distribution Chart
            updateScoreDistributionChart();
            
            // Time Analysis Chart
            updateTimeAnalysisChart();
        }
        
        function updateModulePerformanceChart() {
            const ctx = document.getElementById('module-performance-chart').getContext('2d');
            
            if (charts.modulePerformance) {
                charts.modulePerformance.destroy();
            }
            
            const moduleStats = analyticsData.module_stats;
            const labels = moduleStats.map(module => module.name);
            const completionData = moduleStats.map(module => module.average_completion);
            const scoreData = moduleStats.map(module => module.average_score);
            
            charts.modulePerformance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Completion Rate (%)',
                        data: completionData,
                        backgroundColor: 'rgba(59, 130, 246, 0.5)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1
                    }, {
                        label: 'Average Score (%)',
                        data: scoreData,
                        backgroundColor: 'rgba(16, 185, 129, 0.5)',
                        borderColor: 'rgba(16, 185, 129, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
        }
        
        function updateCompletionStatusChart() {
            const ctx = document.getElementById('completion-status-chart').getContext('2d');
            
            if (charts.completionStatus) {
                charts.completionStatus.destroy();
            }
            
            const overview = analyticsData.overview;
            const total = overview.total_completed + overview.total_in_progress + overview.total_paused;
            
            charts.completionStatus = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Completed', 'In Progress', 'Paused'],
                    datasets: [{
                        data: [overview.total_completed, overview.total_in_progress, overview.total_paused],
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(245, 158, 11, 0.8)'
                        ],
                        borderColor: [
                            'rgba(16, 185, 129, 1)',
                            'rgba(59, 130, 246, 1)',
                            'rgba(245, 158, 11, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
        
        function updateScoreDistributionChart() {
            const ctx = document.getElementById('score-distribution-chart').getContext('2d');
            
            if (charts.scoreDistribution) {
                charts.scoreDistribution.destroy();
            }
            
            // Calculate score distribution
            const studentProgress = analyticsData.student_progress;
            const scoreRanges = {
                '0-20': 0,
                '21-40': 0,
                '41-60': 0,
                '61-80': 0,
                '81-100': 0
            };
            
            studentProgress.forEach(student => {
                const score = student.score || 0;
                if (score <= 20) scoreRanges['0-20']++;
                else if (score <= 40) scoreRanges['21-40']++;
                else if (score <= 60) scoreRanges['41-60']++;
                else if (score <= 80) scoreRanges['61-80']++;
                else scoreRanges['81-100']++;
            });
            
            charts.scoreDistribution = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: Object.keys(scoreRanges),
                    datasets: [{
                        label: 'Number of Students',
                        data: Object.values(scoreRanges),
                        backgroundColor: 'rgba(139, 92, 246, 0.5)',
                        borderColor: 'rgba(139, 92, 246, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        
        function updateTimeAnalysisChart() {
            const ctx = document.getElementById('time-analysis-chart').getContext('2d');
            
            if (charts.timeAnalysis) {
                charts.timeAnalysis.destroy();
            }
            
            const moduleStats = analyticsData.module_stats;
            const labels = moduleStats.map(module => module.name);
            const timeData = moduleStats.map(module => Math.round(module.average_time_spent / 60)); // Convert to minutes
            
            charts.timeAnalysis = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Average Time Spent (minutes)',
                        data: timeData,
                        backgroundColor: 'rgba(245, 158, 11, 0.2)',
                        borderColor: 'rgba(245, 158, 11, 1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        
        function updateStudentProgressTable() {
            const tbody = $('#student-progress-tbody');
            tbody.empty();
            
            analyticsData.student_progress.forEach(student => {
                const statusClass = getStatusClass(student.status);
                const progressWidth = student.completion_percentage || 0;
                
                const row = `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8">
                                    <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                        <span class="text-sm font-medium text-gray-700">${student.student_name.charAt(0).toUpperCase()}</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">${student.student_name}</div>
                                    <div class="text-sm text-gray-500">${student.student_email}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">${student.module_name}</div>
                            <div class="text-sm text-gray-500">${student.module_type.toUpperCase()} • ${student.difficulty_level}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: ${progressWidth}%"></div>
                                </div>
                                <span class="text-sm text-gray-600">${progressWidth.toFixed(1)}%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${student.score ? student.score.toFixed(1) + '%' : 'N/A'}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${Math.round(student.time_spent / 60)} min
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                                ${student.status.replace('_', ' ').toUpperCase()}
                            </span>
                        </td>
                    </tr>
                `;
                
                tbody.append(row);
            });
        }
        
        function updateStudentFilter() {
            const studentFilter = $('#student-filter');
            const currentValue = studentFilter.val();
            
            // Get unique students
            const students = [...new Set(analyticsData.student_progress.map(s => s.student_name))];
            
            studentFilter.empty();
            studentFilter.append('<option value="">All Students</option>');
            
            students.forEach(student => {
                studentFilter.append(`<option value="${student}">${student}</option>`);
            });
            
            studentFilter.val(currentValue);
        }
        
        function getStatusClass(status) {
            switch (status) {
                case 'completed': return 'bg-green-100 text-green-800';
                case 'in_progress': return 'bg-blue-100 text-blue-800';
                case 'paused': return 'bg-yellow-100 text-yellow-800';
                default: return 'bg-gray-100 text-gray-800';
            }
        }
        
        function exportAnalyticsData() {
            // Create CSV data
            const csvData = createCSVData();
            
            // Download CSV
            const blob = new Blob([csvData], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'tutorial-analytics.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }
        
        function createCSVData() {
            let csv = 'Student Name,Module Name,Progress (%),Score (%),Time Spent (min),Status\n';
            
            analyticsData.student_progress.forEach(student => {
                csv += `"${student.student_name}","${student.module_name}",${student.completion_percentage},${student.score || 0},${Math.round(student.time_spent / 60)},"${student.status}"\n`;
            });
            
            return csv;
        }
    });
    </script>
</body>
</html>
