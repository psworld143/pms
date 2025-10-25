<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';
$user_role = $_SESSION['user_role'] ?? 'front_desk';

// Get filter parameters
$category = $_GET['category'] ?? null;
$difficulty = $_GET['difficulty'] ?? null;

// Get scenarios based on filters
$scenarios = getTrainingScenarios($category, $difficulty);

// Get user progress for each scenario with enhanced details
$user_progress = [];
try {
    $stmt = $pdo->prepare("
        SELECT 
            scenario_id,
            MAX(score) as best_score,
            MIN(score) as worst_score,
            AVG(score) as avg_score,
            COUNT(*) as attempts,
            MAX(created_at) as last_attempt,
            MIN(created_at) as first_attempt,
            COUNT(CASE WHEN score >= 80 THEN 1 END) as passed_attempts,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_attempts,
            MAX(CASE WHEN status = 'completed' THEN score END) as latest_score
        FROM training_attempts 
        WHERE user_id = ? AND scenario_type = 'training' AND status = 'completed'
        GROUP BY scenario_id
    ");
    $stmt->execute([$user_id]);
    $progress_data = $stmt->fetchAll();
    
    foreach ($progress_data as $data) {
        // Ensure scores are properly formatted as numbers
        $data['best_score'] = floatval($data['best_score']);
        $data['avg_score'] = floatval($data['avg_score']);
        $data['worst_score'] = floatval($data['worst_score']);
        $data['latest_score'] = floatval($data['latest_score']);
        
        $user_progress[$data['scenario_id']] = $data;
    }
} catch (Exception $e) {
    error_log("Error getting user progress: " . $e->getMessage());
}

$page_title = 'Training Scenarios';
include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';
?>

        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <!-- Page Header -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                <h1 class="text-2xl font-bold text-gray-900">Training Scenarios</h1>
                <p class="text-gray-600 mt-1">Practice hotel management skills with interactive scenarios</p>
                    </div>
            <div class="flex items-center space-x-4">
                <?php if ($user_role === 'manager'): ?>
                <button onclick="openCreateScenarioModal()" class="px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-md hover:from-green-700 hover:to-emerald-700 transition-all duration-300 font-medium">
                    <i class="fas fa-plus mr-2"></i>Create New Scenario
                        </button>
                <?php endif; ?>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Welcome back,</p>
                    <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($user_name); ?></p>
                    <p class="text-xs text-gray-400"><?php echo ucfirst(str_replace('_', ' ', $user_role)); ?></p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-indigo-500 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-white text-xl"></i>
                </div>
                    </div>
                </div>
            </div>

            <!-- Progress Summary -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Your Training Progress</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <?php
            $total_scenarios = count($scenarios);
            $completed_scenarios = count($user_progress);
            $certified_scenarios = 0;
            $total_attempts = 0;
            $overall_best_score = 0;
            
            foreach ($user_progress as $progress) {
                if ($progress['best_score'] >= 80) {
                    $certified_scenarios++;
                }
                $total_attempts += $progress['attempts'];
                $overall_best_score = max($overall_best_score, $progress['best_score']);
            }
            
            $completion_percentage = $total_scenarios > 0 ? round(($completed_scenarios / $total_scenarios) * 100) : 0;
            ?>
            
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-chart-pie text-blue-600 text-2xl"></i>
                        </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-blue-900">Completion</p>
                        <p class="text-2xl font-bold text-blue-600"><?php echo $completion_percentage; ?>%</p>
                        <p class="text-xs text-blue-700"><?php echo $completed_scenarios; ?>/<?php echo $total_scenarios; ?> scenarios</p>
                        </div>
                    </div>
                </div>
            
            <div class="bg-gradient-to-r from-green-50 to-green-100 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-certificate text-green-600 text-2xl"></i>
                        </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-900">Certified</p>
                        <p class="text-2xl font-bold text-green-600"><?php echo $certified_scenarios; ?></p>
                        <p class="text-xs text-green-700">80%+ scores</p>
                        </div>
                    </div>
                </div>
            
            <div class="bg-gradient-to-r from-purple-50 to-purple-100 border border-purple-200 rounded-lg p-4">
                    <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-trophy text-purple-600 text-2xl"></i>
                        </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-purple-900">Best Score</p>
                        <p class="text-2xl font-bold text-purple-600"><?php echo round($overall_best_score); ?>%</p>
                        <p class="text-xs text-purple-700">Overall best</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-orange-50 to-orange-100 border border-orange-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-redo text-orange-600 text-2xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-orange-900">Total Attempts</p>
                        <p class="text-2xl font-bold text-orange-600"><?php echo $total_attempts; ?></p>
                        <p class="text-xs text-orange-700">Practice sessions</p>
                    </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Filter Scenarios</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Category Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select id="category-filter" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="">All Categories</option>
                    <option value="front_desk" <?php echo $category === 'front_desk' ? 'selected' : ''; ?>>Front Desk</option>
                    <option value="housekeeping" <?php echo $category === 'housekeeping' ? 'selected' : ''; ?>>Housekeeping</option>
                    <option value="management" <?php echo $category === 'management' ? 'selected' : ''; ?>>Management</option>
                    <option value="maintenance" <?php echo $category === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                    <option value="food_service" <?php echo $category === 'food_service' ? 'selected' : ''; ?>>Food Service</option>
                    </select>
            </div>

            <!-- Difficulty Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Difficulty</label>
                <select id="difficulty-filter" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">All Levels</option>
                    <option value="beginner" <?php echo $difficulty === 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                    <option value="intermediate" <?php echo $difficulty === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                    <option value="advanced" <?php echo $difficulty === 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                    </select>
            </div>

            <!-- Sort Options -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                <select id="sort-filter" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="difficulty">Difficulty</option>
                    <option value="title">Title</option>
                    <option value="points">Points</option>
                    <option value="time">Time</option>
                </select>
            </div>

            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" id="search-input" placeholder="Search scenarios..." 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            </div>
                </div>
            </div>

            <!-- Scenarios Grid -->
    <div id="scenarios-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (empty($scenarios)): ?>
                    <div class="col-span-full text-center py-12">
                <i class="fas fa-search text-gray-400 text-4xl mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No scenarios found</h3>
                <p class="text-gray-500">Try adjusting your filters or check back later for new content.</p>
                    </div>
        <?php else: ?>
            <?php foreach ($scenarios as $scenario): ?>
                <div class="bg-white rounded-lg shadow-sm border hover:shadow-md transition-all duration-300 scenario-card" 
                     data-category="<?php echo htmlspecialchars($scenario['category']); ?>"
                     data-difficulty="<?php echo htmlspecialchars($scenario['difficulty']); ?>"
                     data-title="<?php echo htmlspecialchars(strtolower($scenario['title'])); ?>"
                     data-points="<?php echo $scenario['points']; ?>"
                     data-time="<?php echo $scenario['estimated_time']; ?>">
                            
                            <!-- Scenario Header -->
                            <div class="p-6 border-b border-gray-200">
                                <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($scenario['title']); ?></h3>
                                <p class="text-sm text-gray-600 mb-3"><?php echo htmlspecialchars($scenario['description']); ?></p>
                                        </div>
                            <div class="flex flex-col items-end space-y-2">
                                <!-- Difficulty Badge -->
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    <?php
                                    switch($scenario['difficulty']) {
                                        case 'beginner': echo 'bg-green-100 text-green-800'; break;
                                        case 'intermediate': echo 'bg-yellow-100 text-yellow-800'; break;
                                        case 'advanced': echo 'bg-red-100 text-red-800'; break;
                                        default: echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?php echo ucfirst($scenario['difficulty']); ?>
                                </span>
                                
                                <!-- Category Badge -->
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800">
                                    <?php echo ucfirst(str_replace('_', ' ', $scenario['category'])); ?>
                                    </span>
                                    </div>
                                </div>

                        <!-- Scenario Stats -->
                        <div class="grid grid-cols-3 gap-4 text-center">
                                        <div>
                                <p class="text-2xl font-bold text-purple-600"><?php echo $scenario['points']; ?></p>
                                <p class="text-xs text-gray-500">Points</p>
                                        </div>
                            <div>
                                <p class="text-2xl font-bold text-blue-600"><?php echo $scenario['estimated_time']; ?>m</p>
                                <p class="text-xs text-gray-500">Duration</p>
                                    </div>
                            <div>
                                <p class="text-2xl font-bold text-green-600"><?php echo $scenario['attempt_count'] ?? 0; ?></p>
                                <p class="text-xs text-gray-500">Attempts</p>
                                        </div>
                                    </div>
                                </div>
                                
                    <!-- User Progress -->
                    <div class="p-6">
                        <?php if (isset($user_progress[$scenario['id']])): 
                            $progress = $user_progress[$scenario['id']];
                            $best_score = round($progress['best_score'], 1);
                            $avg_score = round($progress['avg_score'], 1);
                            $attempts = $progress['attempts'];
                            $passed_attempts = $progress['passed_attempts'];
                            $is_certified = $best_score >= 80;
                            $progress_percentage = min(100, $best_score);
                        ?>
                            <div class="mb-4">
                                <div class="flex justify-between text-sm text-gray-600 mb-2">
                                    <span>Your Progress</span>
                                    <div class="text-right">
                                        <span class="font-semibold <?php echo $is_certified ? 'text-green-600' : 'text-purple-600'; ?>">
                                            <?php echo $best_score; ?>% Best
                                        </span>
                                        <?php if ($is_certified): ?>
                                            <i class="fas fa-certificate text-green-500 ml-1" title="Certificate Earned"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Progress Bar -->
                                <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                                    <div class="bg-gradient-to-r from-purple-500 to-indigo-500 h-2 rounded-full transition-all duration-500" 
                                         style="width: <?php echo $progress_percentage; ?>%"></div>
                                </div>
                                
                                <!-- Progress Details -->
                                <div class="grid grid-cols-2 gap-2 text-xs text-gray-500">
                                    <div>
                                        <i class="fas fa-chart-line mr-1"></i>
                                        Avg: <?php echo $avg_score; ?>%
                                    </div>
                                    <div>
                                        <i class="fas fa-trophy mr-1"></i>
                                        Passed: <?php echo $passed_attempts; ?>/<?php echo $attempts; ?>
                                    </div>
                                </div>
                                
                                <!-- Status Badge -->
                                <div class="mt-2">
                                    <?php if ($is_certified): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Certified
                                    </span>
                                    <?php elseif ($best_score >= 60): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-clock mr-1"></i>
                                            In Progress
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-play mr-1"></i>
                                            Started
                                        </span>
                                    <?php endif; ?>
                                    </div>
                                
                                <!-- Last Attempt -->
                                <p class="text-xs text-gray-400 mt-1">
                                    Last attempt: <?php echo date('M j, Y', strtotime($progress['last_attempt'])); ?>
                                </p>
                                </div>
                        <?php else: ?>
                            <div class="mb-4">
                                <div class="flex justify-between text-sm text-gray-600 mb-2">
                                    <span>Your Progress</span>
                                    <span class="text-gray-400">Not Started</span>
                            </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                                    <div class="bg-gray-300 h-2 rounded-full" style="width: 0%"></div>
                                    </div>
                                    <div class="text-center">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                        <i class="fas fa-play mr-1"></i>
                                        Ready to Start
                                    </span>
                                    </div>
                                <p class="text-xs text-gray-400 mt-1 text-center">Complete this scenario to track your progress</p>
                                </div>
                        <?php endif; ?>
                                
                        <!-- Action Buttons -->
                        <div class="flex space-x-3">
                            <button onclick="startScenario(<?php echo $scenario['id']; ?>)" 
                                    class="flex-1 bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-4 py-2 rounded-md hover:from-purple-700 hover:to-indigo-700 transition-all duration-300 font-medium">
                                <i class="fas fa-play mr-2"></i>
                                <?php
                                if (isset($user_progress[$scenario['id']])) {
                                    $progress = $user_progress[$scenario['id']];
                                    if ($progress['best_score'] >= 80) {
                                        echo 'Retake (Certified)';
                                    } elseif ($progress['best_score'] >= 60) {
                                        echo 'Improve Score';
                                    } else {
                                        echo 'Try Again';
                                    }
                                } else {
                                    echo 'Start Training';
                                }
                                ?>
                            </button>
                            <button onclick="previewScenario(<?php echo $scenario['id']; ?>)" 
                                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                                <i class="fas fa-eye"></i>
                            </button>
                                        </div>
                                    </div>
                                        </div>
            <?php endforeach; ?>
        <?php endif; ?>
                                    </div>

    <!-- Pagination -->
    <div class="mt-8 flex justify-center">
        <div class="pagination-container">
            <!-- Pagination buttons will be generated here by JavaScript -->
        </div>
                            </div>
                            
    <!-- Training History Section -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mt-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Training History</h2>
            <button onclick="toggleHistoryView()" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors">
                <i class="fas fa-history mr-2"></i>View History
                                        </button>
        </div>
        
        <div id="training-history" class="hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scenario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="history-tbody" class="bg-white divide-y divide-gray-200">
                        <!-- History data will be loaded here -->
                    </tbody>
                </table>
            </div>
            
            <div id="no-history" class="text-center py-8 hidden">
                <i class="fas fa-history text-gray-400 text-3xl mb-3"></i>
                <p class="text-gray-500">No training history yet</p>
                <p class="text-sm text-gray-400">Complete some scenarios to see your progress here</p>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-8 flex justify-center">
        <nav class="flex items-center space-x-2">
            <button class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                Previous
                                        </button>
            <button class="px-3 py-2 text-sm font-medium text-white bg-purple-600 border border-purple-600 rounded-md">
                1
                                        </button>
            <button class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                2
                                        </button>
            <button class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                3
            </button>
            <button class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                Next
            </button>
        </nav>
                                </div>
        </main>

<!-- Scenario Preview Modal -->
<div id="scenario-preview-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-8 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-900" id="preview-title">Scenario Preview</h3>
            <button onclick="closePreviewModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
                            </div>
        
        <div id="preview-content">
            <!-- Preview content will be loaded here -->
                        </div>
        
        <div class="flex justify-end space-x-4 mt-6">
            <button onclick="closePreviewModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                Close
            </button>
            <button onclick="startScenarioFromPreview()" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-md hover:from-purple-700 hover:to-indigo-700 transition-all duration-300">
                Start Scenario
            </button>
            </div>
    </div>
    </div>

<!-- Scenario Training Modal -->
<div id="scenario-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-8 max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-900" id="scenario-title">Training Scenario</h3>
            <div class="flex items-center space-x-4">
                <div class="text-sm text-gray-600">
                    <i class="fas fa-clock mr-1"></i>
                    <span id="scenario-timer">15:00</span>
                </div>
                <button onclick="closeScenarioModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <div id="scenario-content">
            <!-- Scenario content will be loaded here -->
        </div>
        
        <div class="flex justify-between items-center mt-6 pt-6 border-t border-gray-200">
            <button onclick="pauseScenario()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                <i class="fas fa-pause mr-2"></i>Pause
            </button>
            <button onclick="submitScenario()" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-md hover:from-purple-700 hover:to-indigo-700 transition-all duration-300">
                <i class="fas fa-check mr-2"></i>Submit Answers
            </button>
        </div>
    </div>
</div>

<?php if ($user_role === 'manager'): ?>
<!-- Create Scenario Modal -->
<div id="create-scenario-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-8 max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Create New Training Scenario</h3>
            <button onclick="closeCreateScenarioModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form id="create-scenario-form">
            <div class="space-y-6">
                <!-- Basic Information -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                    <h4 class="font-medium text-gray-900 mb-4">Basic Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Scenario Type</label>
                            <select id="scenario-type" name="scenario_type" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500" required>
                                <option value="">Select Scenario Type</option>
                                <option value="front_desk">Front Desk Check-in Process</option>
                                <option value="customer_service">Customer Service Excellence</option>
                                <option value="problem_solving">Problem Solving & Crisis Management</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Difficulty Level</label>
                            <select id="difficulty" name="difficulty" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500" required>
                                <option value="">Select Difficulty</option>
                                <option value="beginner">Beginner</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="advanced">Advanced</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Estimated Time (minutes)</label>
                            <input type="number" id="estimated-time" name="estimated_time" min="5" max="60" value="15" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Points</label>
                            <input type="number" id="points" name="points" min="50" max="500" value="100" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500" required>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Scenario Description</label>
                        <textarea id="description" name="description" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="Describe the scenario situation..." required></textarea>
                    </div>
                </div>

                <!-- AI Generation Options -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h4 class="font-medium text-blue-900 mb-4">AI Question Generation</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Number of Questions</label>
                            <select id="question-count" name="question_count" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500" required>
                                <option value="3">3 Questions</option>
                                <option value="5" selected>5 Questions</option>
                                <option value="7">7 Questions</option>
                                <option value="10">10 Questions</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Question Complexity</label>
                            <select id="question-complexity" name="question_complexity" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500" required>
                                <option value="basic">Basic</option>
                                <option value="intermediate" selected>Intermediate</option>
                                <option value="advanced">Advanced</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Additional Context (Optional)</label>
                        <textarea id="ai-context" name="ai_context" rows="2" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="Provide additional context for AI to generate more relevant questions..."></textarea>
                    </div>
                </div>

                <!-- Preview Section -->
                <div id="ai-preview" class="bg-green-50 border border-green-200 rounded-lg p-6 hidden">
                    <h4 class="font-medium text-green-900 mb-4">Generated Questions Preview</h4>
                    <div id="preview-questions">
                        <!-- Generated questions will appear here -->
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end space-x-4 mt-6 pt-6 border-t border-gray-200">
                <button type="button" onclick="closeCreateScenarioModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="button" onclick="generateAIScenario()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                    <i class="fas fa-magic mr-2"></i>Generate with AI
                </button>
                <button type="submit" id="create-scenario-btn" class="px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-md hover:from-green-700 hover:to-emerald-700 transition-all duration-300 hidden">
                    <i class="fas fa-save mr-2"></i>Create Scenario
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

    <script>
let currentScenarioId = null;
let scenarioTimer = null;
let timeLeft = 0;
const userRole = '<?php echo $user_role; ?>';

// Filter functionality
document.getElementById('category-filter').addEventListener('change', filterScenarios);
document.getElementById('difficulty-filter').addEventListener('change', filterScenarios);
document.getElementById('sort-filter').addEventListener('change', sortScenarios);
document.getElementById('search-input').addEventListener('input', searchScenarios);

function filterScenarios() {
    const category = document.getElementById('category-filter').value;
    const difficulty = document.getElementById('difficulty-filter').value;
    
    // Update URL parameters
    const url = new URL(window.location);
    if (category) url.searchParams.set('category', category);
    else url.searchParams.delete('category');
    if (difficulty) url.searchParams.set('difficulty', difficulty);
    else url.searchParams.delete('difficulty');
    
    window.history.pushState({}, '', url);
    window.location.reload();
}

function sortScenarios() {
    const sortBy = document.getElementById('sort-filter').value;
    const container = document.getElementById('scenarios-container');
    const cards = Array.from(container.querySelectorAll('.scenario-card'));
    
    cards.sort((a, b) => {
        switch(sortBy) {
            case 'title':
                return a.dataset.title.localeCompare(b.dataset.title);
            case 'points':
                return parseInt(b.dataset.points) - parseInt(a.dataset.points);
            case 'time':
                return parseInt(a.dataset.time) - parseInt(b.dataset.time);
            case 'difficulty':
                const difficultyOrder = { 'beginner': 1, 'intermediate': 2, 'advanced': 3 };
                return difficultyOrder[a.dataset.difficulty] - difficultyOrder[b.dataset.difficulty];
            default:
                return 0;
        }
    });
    
    cards.forEach(card => container.appendChild(card));
}

function searchScenarios() {
    const searchTerm = document.getElementById('search-input').value.toLowerCase();
    const cards = document.querySelectorAll('.scenario-card');
    
    cards.forEach(card => {
        const title = card.dataset.title;
        const description = card.querySelector('p').textContent.toLowerCase();
                    
                    if (title.includes(searchTerm) || description.includes(searchTerm)) {
            card.style.display = 'block';
                    } else {
            card.style.display = 'none';
        }
    });
}

function startScenario(scenarioId) {
    currentScenarioId = scenarioId;
    
    fetch(`../../api/training/get-scenario-details.php?id=${scenarioId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Combine scenario data with questions
                const scenario = {
                    ...data.scenario,
                    questions: data.questions || []
                };
                openScenarioModal(scenario);
            } else {
                alert('Error loading scenario: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error loading scenario:', error);
            alert('Error loading scenario');
        });
}

function previewScenario(scenarioId) {
    fetch(`../../api/training/get-scenario-details.php?id=${scenarioId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Combine scenario data with questions
                const scenario = {
                    ...data.scenario,
                    questions: data.questions || []
                };
                openPreviewModal(scenario);
            } else {
                alert('Error loading scenario preview');
            }
        })
        .catch(error => {
            console.error('Error loading scenario preview:', error);
            alert('Error loading scenario preview');
        });
}

function openPreviewModal(scenario) {
    if (!scenario || !scenario.title) {
        console.error('Invalid scenario data:', scenario);
        alert('Error: Invalid scenario data');
                        return;
                    }
    
    document.getElementById('preview-title').textContent = scenario.title;
    document.getElementById('preview-content').innerHTML = `
        <div class="space-y-6">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-medium text-blue-900 mb-2">Description</h4>
                <p class="text-blue-800">${scenario.description || 'No description available'}</p>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">Difficulty</h4>
                    <p class="text-gray-700">${scenario.difficulty || 'Not specified'}</p>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">Duration</h4>
                    <p class="text-gray-700">${scenario.estimated_time || 15} minutes</p>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">Points</h4>
                    <p class="text-gray-700">${scenario.points || 0}</p>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">Questions</h4>
                    <p class="text-gray-700">${scenario.questions ? scenario.questions.length : 0}</p>
                        </div>
                      </div>
                    </div>
    `;
    
    document.getElementById('scenario-preview-modal').classList.remove('hidden');
}

function closePreviewModal() {
    document.getElementById('scenario-preview-modal').classList.add('hidden');
}

function startScenarioFromPreview() {
    closePreviewModal();
    startScenario(currentScenarioId);
}

function openScenarioModal(scenario) {
    if (!scenario || !scenario.id) {
        console.error('Invalid scenario data:', scenario);
        alert('Error: Invalid scenario data');
        return;
    }
    
    currentScenarioId = scenario.id;
    document.getElementById('scenario-title').textContent = scenario.title || 'Training Scenario';
    
    let questionsHtml = '';
    if (scenario.questions && scenario.questions.length > 0) {
        questionsHtml = scenario.questions.map((question, index) => `
            <div class="border border-gray-200 rounded-lg p-4 mb-4">
                <h5 class="font-medium text-gray-900 mb-3">Question ${index + 1}</h5>
                <p class="text-gray-700 mb-4">${question.question || 'No question text available'}</p>
                <div class="space-y-2">
                    ${question.options && question.options.length > 0 ? question.options.map(option => `
                        <label class="flex items-center">
                            <input type="radio" name="q${question.id}" value="${option.option_value}" class="mr-3">
                            <span class="text-gray-700">${option.option_text || option.option_value}</span>
                        </label>
                    `).join('') : '<p class="text-gray-500 italic">No options available</p>'}
                        </div>
                      </div>
        `).join('');
    } else {
        questionsHtml = `
            <div class="border border-gray-200 rounded-lg p-4 text-center">
                <p class="text-gray-500 italic">No questions available for this scenario. This is a practice scenario where you can learn from the description above.</p>
                    </div>
        `;
    }
    
    document.getElementById('scenario-content').innerHTML = `
        <div class="space-y-6">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-medium text-blue-900 mb-2">Scenario Description</h4>
                <p class="text-blue-800">${scenario.description || 'No description available'}</p>
                    </div>
            
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-2">Instructions</h4>
                <p class="text-gray-700">Complete the scenario by answering the questions below. Choose the best response for each situation.</p>
                  </div>
            
            <div class="space-y-4">
                ${questionsHtml}
                    </div>
                  </div>
    `;
    
    document.getElementById('scenario-modal').classList.remove('hidden');
    startScenarioTimer();
}

function startScenarioTimer() {
    timeLeft = 15 * 60; // 15 minutes in seconds
    scenarioTimer = setInterval(() => {
        timeLeft--;
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        document.getElementById('scenario-timer').textContent = 
            `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        
        if (timeLeft <= 0) {
            stopScenarioTimer();
            alert('Time is up!');
        }
    }, 1000);
}

function stopScenarioTimer() {
    if (scenarioTimer) {
        clearInterval(scenarioTimer);
        scenarioTimer = null;
    }
}

function pauseScenario() {
    if (scenarioTimer) {
        stopScenarioTimer();
        document.querySelector('button[onclick="pauseScenario()"]').innerHTML = '<i class="fas fa-play mr-2"></i>Resume';
        document.querySelector('button[onclick="pauseScenario()"]').setAttribute('onclick', 'resumeScenario()');
    }
}

function resumeScenario() {
    startScenarioTimer();
    document.querySelector('button[onclick="resumeScenario()"]').innerHTML = '<i class="fas fa-pause mr-2"></i>Pause';
    document.querySelector('button[onclick="resumeScenario()"]').setAttribute('onclick', 'pauseScenario()');
}

function submitScenario() {
    const answers = {};
    const questions = document.querySelectorAll('[name^="q"]');
    questions.forEach(question => {
        const selected = document.querySelector(`input[name="${question.name}"]:checked`);
        if (selected) {
            answers[question.name] = selected.value;
        }
    });
    
    fetch('../../api/training/submit-scenario.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({
            scenario_id: currentScenarioId,
            answers: answers
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAnswerReview(data);
        } else {
            alert('Error submitting scenario: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error submitting scenario:', error);
        alert('Error submitting scenario');
    });
}

function closeScenarioModal() {
    stopScenarioTimer();
    document.getElementById('scenario-modal').classList.add('hidden');
}

function showAnswerReview(data) {
    // Store the current scenario data for review
    window.currentScenarioData = data;
    
    // Update the modal content to show results
    document.getElementById('scenario-title').textContent = 'Scenario Results - ' + (data.scenario_title || 'Training Scenario');
    
    // Hide the timer
    document.querySelector('#scenario-timer').parentElement.style.display = 'none';
    
    // Update the content to show results
    document.getElementById('scenario-content').innerHTML = `
        <div class="space-y-6">
            <!-- Score Summary -->
            <div class="bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-200 rounded-lg p-6">
                <div class="text-center">
                    <h4 class="text-2xl font-bold text-purple-900 mb-2">Your Score</h4>
                    <div class="text-4xl font-bold text-purple-600 mb-2">${data.score}%</div>
                    <p class="text-purple-700">${data.correct_answers} out of ${data.total_questions} questions correct</p>
                    ${data.certificate_earned ? '<p class="text-green-600 font-semibold mt-2"><i class="fas fa-certificate mr-1"></i>Certificate Earned!</p>' : ''}
                </div>
            </div>
            
            <!-- Answer Review -->
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Answer Review</h4>
                <div id="answer-review-content">
                    <!-- Answer review will be loaded here -->
                </div>
            </div>
        </div>
    `;
    
    // Load the answer review
    loadAnswerReview();
    
    // Update the buttons
    document.querySelector('button[onclick="pauseScenario()"]').style.display = 'none';
    document.querySelector('button[onclick="submitScenario()"]').innerHTML = '<i class="fas fa-check mr-2"></i>Close Results';
    document.querySelector('button[onclick="submitScenario()"]').setAttribute('onclick', 'closeAnswerReview()');
}

function loadAnswerReview() {
    // Get the current scenario data
    const scenarioData = window.currentScenarioData;
    
    if (!scenarioData || !scenarioData.questions) {
        document.getElementById('answer-review-content').innerHTML = '<p class="text-gray-500">No answer review available.</p>';
        return;
    }
    
    let reviewHtml = '';
    
    scenarioData.questions.forEach((question, index) => {
        const userAnswer = scenarioData.user_answers ? scenarioData.user_answers[`q${question.id}`] : null;
        const isCorrect = userAnswer === question.correct_answer;
        
        reviewHtml += `
            <div class="border border-gray-200 rounded-lg p-4 mb-4 ${isCorrect ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'}">
                <div class="flex items-start justify-between mb-3">
                    <h5 class="font-medium text-gray-900">Question ${index + 1}</h5>
                    <span class="px-2 py-1 text-xs font-medium rounded-full ${isCorrect ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        ${isCorrect ? 'Correct' : 'Incorrect'}
                    </span>
                </div>
                <p class="text-gray-700 mb-4">${question.question}</p>
                
                <div class="space-y-2">
                    ${question.options.map(option => {
                        const isUserAnswer = userAnswer === option.option_value;
                        const isCorrectAnswer = option.option_value === question.correct_answer;
                        
                        let optionClass = 'p-2 rounded border ';
                        let icon = '';
                        
                        if (isCorrectAnswer) {
                            optionClass += 'bg-green-100 border-green-300 text-green-800';
                            icon = '<i class="fas fa-check-circle text-green-600 mr-2"></i>';
                        } else if (isUserAnswer && !isCorrectAnswer) {
                            optionClass += 'bg-red-100 border-red-300 text-red-800';
                            icon = '<i class="fas fa-times-circle text-red-600 mr-2"></i>';
                        } else {
                            optionClass += 'bg-gray-50 border-gray-200 text-gray-700';
                        }
                        
                        return `
                            <div class="${optionClass}">
                                ${icon}${option.option_text}
                                ${isCorrectAnswer ? '<span class="text-green-600 font-semibold ml-2">(Correct Answer)</span>' : ''}
                                ${isUserAnswer && !isCorrectAnswer ? '<span class="text-red-600 font-semibold ml-2">(Your Answer)</span>' : ''}
                    </div>
                        `;
                    }).join('')}
                    </div>
            </div>
        `;
    });
    
    document.getElementById('answer-review-content').innerHTML = reviewHtml;
}

function closeAnswerReview() {
    closeScenarioModal();
    location.reload(); // Refresh to show updated progress
}

// Training History Functions
function toggleHistoryView() {
    const historyDiv = document.getElementById('training-history');
    const button = document.querySelector('button[onclick="toggleHistoryView()"]');
    
    if (historyDiv.classList.contains('hidden')) {
        historyDiv.classList.remove('hidden');
        button.innerHTML = '<i class="fas fa-eye-slash mr-2"></i>Hide History';
        loadTrainingHistory();
    } else {
        historyDiv.classList.add('hidden');
        button.innerHTML = '<i class="fas fa-history mr-2"></i>View History';
    }
}

function loadTrainingHistory() {
    fetch('../../api/training/get-training-history.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.history && data.history.length > 0) {
                displayTrainingHistory(data.history);
            } else {
                showNoHistory();
            }
        })
        .catch(error => {
            console.error('Error loading training history:', error);
            showNoHistory();
        });
}

function displayTrainingHistory(history) {
    const tbody = document.getElementById('history-tbody');
    const noHistory = document.getElementById('no-history');
    
    tbody.innerHTML = '';
    noHistory.classList.add('hidden');
    
    history.forEach(attempt => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">${attempt.scenario_title || 'Unknown Scenario'}</div>
                <div class="text-sm text-gray-500">${attempt.scenario_type || 'training'}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                    attempt.score >= 80 ? 'bg-green-100 text-green-800' : 
                    attempt.score >= 60 ? 'bg-yellow-100 text-yellow-800' : 
                    'bg-red-100 text-red-800'
                }">
                    ${attempt.score || 0}%
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                    attempt.status === 'completed' ? 'bg-green-100 text-green-800' : 
                    attempt.status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                    'bg-gray-100 text-gray-800'
                }">
                    ${attempt.status || 'unknown'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${new Date(attempt.created_at).toLocaleDateString()}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${attempt.duration_minutes || 0} min
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button onclick="viewAttemptDetails(${attempt.id})" class="text-purple-600 hover:text-purple-900 mr-3">
                    <i class="fas fa-eye"></i>
                </button>
                <button onclick="retakeScenario(${attempt.scenario_id}, '${attempt.scenario_type}')" class="text-blue-600 hover:text-blue-900">
                    <i class="fas fa-redo"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function showNoHistory() {
    const tbody = document.getElementById('history-tbody');
    const noHistory = document.getElementById('no-history');
    
    tbody.innerHTML = '';
    noHistory.classList.remove('hidden');
}

function viewAttemptDetails(attemptId) {
    // This would open a modal showing detailed results
    alert('View attempt details for ID: ' + attemptId);
}

function retakeScenario(scenarioId, scenarioType) {
    if (scenarioType === 'training') {
        startScenario(scenarioId);
    } else if (scenarioType === 'customer_service') {
        // Call customer service function if available
        alert('Retaking customer service scenario: ' + scenarioId);
    } else if (scenarioType === 'problem') {
        // Call problem scenario function if available
        alert('Retaking problem scenario: ' + scenarioId);
    }
}

// Pagination functionality
let currentPage = 1;
const scenariosPerPage = 6;

function initializePagination() {
    updatePaginationButtons();
}

function updatePaginationButtons() {
    const totalScenarios = document.querySelectorAll('.scenario-card').length;
    const totalPages = Math.ceil(totalScenarios / scenariosPerPage);
    
    // Update pagination display
    const paginationContainer = document.querySelector('.pagination-container');
    if (paginationContainer) {
        paginationContainer.innerHTML = generatePaginationHTML(currentPage, totalPages);
    }
}

function generatePaginationHTML(currentPage, totalPages) {
    let html = '<nav class="flex items-center space-x-2">';
    
    // Previous button
    html += `<button onclick="goToPage(${currentPage - 1})" 
              class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 ${currentPage <= 1 ? 'opacity-50 cursor-not-allowed' : ''}"
              ${currentPage <= 1 ? 'disabled' : ''}>
              Previous
              </button>`;
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        const isActive = i === currentPage;
        html += `<button onclick="goToPage(${i})" 
                  class="px-3 py-2 text-sm font-medium ${isActive ? 'text-white bg-purple-600 border-purple-600' : 'text-gray-500 bg-white border-gray-300 hover:bg-gray-50'} border rounded-md">
                  ${i}
                  </button>`;
    }
    
    // Next button
    html += `<button onclick="goToPage(${currentPage + 1})" 
              class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 ${currentPage >= totalPages ? 'opacity-50 cursor-not-allowed' : ''}"
              ${currentPage >= totalPages ? 'disabled' : ''}>
              Next
              </button>`;
    
    html += '</nav>';
    return html;
}

function goToPage(page) {
    const totalScenarios = document.querySelectorAll('.scenario-card').length;
    const totalPages = Math.ceil(totalScenarios / scenariosPerPage);
    
    if (page < 1 || page > totalPages) return;
    
    currentPage = page;
    showPage(page);
    updatePaginationButtons();
}

function showPage(page) {
    const scenarios = document.querySelectorAll('.scenario-card');
    const startIndex = (page - 1) * scenariosPerPage;
    const endIndex = startIndex + scenariosPerPage;
    
    scenarios.forEach((scenario, index) => {
        if (index >= startIndex && index < endIndex) {
            scenario.style.display = 'block';
        } else {
            scenario.style.display = 'none';
        }
    });
}

// Initialize pagination when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializePagination();
    showPage(1);
});

// Create Scenario Modal Functions
function openCreateScenarioModal() {
    if (userRole !== 'manager') {
        alert('Only managers can create new scenarios.');
        return;
    }
    const modal = document.getElementById('create-scenario-modal');
    const form = document.getElementById('create-scenario-form');
    const aiPreview = document.getElementById('ai-preview');
    const createBtn = document.getElementById('create-scenario-btn');
    
    if (!modal || !form || !aiPreview || !createBtn) {
        console.warn('Create scenario elements not found');
        return;
    }
    
            modal.classList.remove('hidden');
    // Reset form
    form.reset();
    aiPreview.classList.add('hidden');
    createBtn.classList.add('hidden');
    // Clear any previously generated questions
    window.generatedQuestions = [];
    console.log('Modal opened - cleared generated questions');
}

function closeCreateScenarioModal() {
    const modal = document.getElementById('create-scenario-modal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

function generateAIScenario() {
    if (userRole !== 'manager') {
        alert('Only managers can generate AI scenarios.');
        return;
    }
    const form = document.getElementById('create-scenario-form');
    if (!form) {
        console.warn('Create scenario form not found');
        return;
    }
    
    const formData = new FormData(form);
    const scenarioType = formData.get('scenario_type');
    const difficulty = formData.get('difficulty');
    const questionCount = formData.get('question_count');
    const complexity = formData.get('question_complexity');
    const description = formData.get('description');
    const context = formData.get('ai_context');
    
    if (!scenarioType || !difficulty || !description) {
        alert('Please fill in all required fields before generating questions.');
        return;
    }
    
    // Show loading state
    const generateBtn = document.querySelector('button[onclick="generateAIScenario()"]');
    const originalText = generateBtn.innerHTML;
    generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Generating...';
    generateBtn.disabled = true;
    
    // Call AI API
    fetch('../../api/training/generate-ai-scenario.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({
            scenario_type: scenarioType,
            difficulty: difficulty,
            question_count: parseInt(questionCount),
            complexity: complexity,
            description: description,
            context: context
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('AI Generation response:', data);
        if (data.success) {
            // Store generated questions globally
            window.generatedQuestions = data.questions;
            console.log('Stored generated questions:', window.generatedQuestions);
            displayGeneratedQuestions(data.questions);
            
            const aiPreview = document.getElementById('ai-preview');
            const createBtn = document.getElementById('create-scenario-btn');
            if (aiPreview) aiPreview.classList.remove('hidden');
            if (createBtn) createBtn.classList.remove('hidden');
        } else {
            alert('Error generating questions: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error generating AI scenario:', error);
        alert('Error generating questions. Please try again.');
    })
    .finally(() => {
        // Reset button state
        generateBtn.innerHTML = originalText;
        generateBtn.disabled = false;
    });
}

function displayGeneratedQuestions(questions) {
    const previewContainer = document.getElementById('preview-questions');
    let html = '';
    
    questions.forEach((question, index) => {
        html += `
            <div class="border border-gray-200 rounded-lg p-4 mb-4">
                <h5 class="font-medium text-gray-900 mb-2">Question ${index + 1}</h5>
                <p class="text-gray-700 mb-3">${question.question}</p>
                <div class="space-y-2">
                    ${question.options.map((option, optIndex) => `
                        <div class="flex items-center">
                            <span class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center text-xs font-medium mr-3">${String.fromCharCode(65 + optIndex)}</span>
                            <span class="text-gray-700">${option.text}</span>
                            ${option.is_correct ? '<span class="ml-2 text-green-600 font-semibold">(Correct)</span>' : ''}
                    </div>
                    `).join('')}
                    </div>
            </div>
        `;
    });
    
    previewContainer.innerHTML = html;
    
    // Store questions in the correct format for API submission
    window.generatedQuestions = questions.map(q => ({
        question: q.question,
        options: q.options,
        correct_answer: q.correct_answer
    }));
    
    console.log('Questions stored in displayGeneratedQuestions:', window.generatedQuestions);
}

// Handle form submission
const createScenarioForm = document.getElementById('create-scenario-form');
if (createScenarioForm) {
    createScenarioForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (userRole !== 'manager') {
            alert('Only managers can create new scenarios.');
            return;
        }
    
    const formData = new FormData(this);
    const questions = window.generatedQuestions || [];
    
    console.log('Form submission - Generated questions:', questions);
    console.log('Questions length:', questions.length);
    
    if (questions.length === 0) {
        alert('Please generate questions first using the AI generator.');
        return;
    }
    
    // Validate that we have the required form data
    const scenarioType = formData.get('scenario_type');
    const difficulty = formData.get('difficulty');
    const description = formData.get('description');
    
    if (!scenarioType || !difficulty || !description) {
        alert('Please fill in all required fields.');
        return;
    }
    
    // Show loading state
    const submitBtn = document.getElementById('create-scenario-btn');
    if (!submitBtn) {
        console.warn('Create scenario button not found');
        return;
    }
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating...';
    submitBtn.disabled = true;
    
    // Submit scenario
    fetch('../../api/training/create-scenario.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({
            ...Object.fromEntries(formData),
            questions: questions
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Scenario created successfully!');
            closeCreateScenarioModal();
            location.reload(); // Refresh to show new scenario
        } else {
            alert('Error creating scenario: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error creating scenario:', error);
        alert('Error creating scenario. Please try again.');
    })
    .finally(() => {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});
}
    </script>

<?php include '../../includes/footer.php'; ?>