<?php
/**
 * Interactive Training Module
 * Complete Hotel PMS Training System
 */

session_start();
require_once '../includes/database.php';
require_once 'includes/progress-tracker.php';
require_once 'includes/dynamic-training-manager.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header('Location: login.php');
    exit();
}

// Get module parameters
$module_name = $_GET['module'] ?? '';
$module_type = $_GET['type'] ?? '';

if (empty($module_name)) {
    header('Location: index.php');
    exit();
}

// Initialize progress tracker and dynamic training manager
$progress_tracker = new TutorialProgressTracker($pdo);
$dynamic_training = new DynamicTrainingManager($pdo);
$user_id = $_SESSION['user_id'];

// Get module data from database
$module_data = $dynamic_training->getModuleByName($module_name);

if (!$module_data) {
    header('Location: index.php');
    exit();
}

// Handle step completion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'complete_step') {
        $step = (int)$_POST['step'];
        $total_steps = $module_data['step_count'];
        $progress_percentage = ($step / $total_steps) * 100;
        
        $status = ($step >= $total_steps) ? 'completed' : 'in_progress';
        
        $result = $progress_tracker->updateProgress(
            $user_id, 
            $module_name, 
            $module_data['module_type'], 
            $progress_percentage, 
            $step, 
            $total_steps, 
            $status
        );
        
        // Record analytics
        $progress_tracker->recordAction($user_id, 'module_start', $module_name, [
            'step' => $step,
            'progress' => $progress_percentage
        ]);
        
        if ($status === 'completed') {
            $progress_tracker->recordAction($user_id, 'module_complete', $module_name, [
                'completion_time' => time(),
                'total_steps' => $total_steps
            ]);
        }
        
        header('Location: training.php?module=' . urlencode($module_name) . '&type=' . urlencode($module_data['module_type']));
        exit();
    }
}

// Get updated progress
$current_progress = $progress_tracker->getModuleProgress($user_id, $module_name);
$current_step = $current_progress['current_step'] ?? 1;
$progress_percentage = $current_progress['completion_percentage'] ?? 0;
$status = $current_progress['status'] ?? 'not_started';

// Start module if not started
if ($status === 'not_started') {
    $progress_tracker->startModule($user_id, $module_name, $module_data['module_type'], $module_data['step_count']);
    $progress_tracker->recordAction($user_id, 'module_start', $module_name, ['step' => 1]);
    $current_step = 1;
    $progress_percentage = 0;
    $status = 'in_progress';
}

// Get current step data from database
$current_step_data = $dynamic_training->getStepContent($module_data['id'], $current_step);

// Get additional step content (quizzes, simulations, resources)
$step_quizzes = [];
$step_simulations = [];
$step_resources = [];

if ($current_step_data) {
    $step_quizzes = $dynamic_training->getStepQuizzes($current_step_data['id']);
    $step_simulations = $dynamic_training->getStepSimulations($current_step_data['id']);
    $step_resources = $dynamic_training->getResources($module_data['id'], $current_step_data['id']);
}

// Get all steps for overview
$all_steps = $dynamic_training->getModuleSteps($module_data['id']);

$page_title = $module_name;
$user_name = $_SESSION['user_name'] ?? 'Student';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Hotel PMS Training</title>
    <link rel="icon" type="image/png" href="../assets/images/seait-logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .progress-bar {
            transition: width 0.3s ease;
        }
        
        .step-card {
            transition: all 0.3s ease;
        }
        
        .step-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .step-completed {
            background: linear-gradient(135deg, #10B981, #059669);
            color: white;
        }
        
        .step-current {
            background: linear-gradient(135deg, #3B82F6, #1D4ED8);
            color: white;
        }
        
        .step-pending {
            background: #F3F4F6;
            color: #6B7280;
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Include Tutorial Sidebar -->
    <?php include 'includes/tutorial-sidebar.php'; ?>

    <!-- Main Content -->
    <main class="lg:ml-64 p-4 lg:p-6">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center mb-2">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-graduation-cap text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900"><?php echo $module_name; ?></h1>
                            <p class="text-gray-600"><?php echo $module_data['description'] ?? 'Dynamic training module'; ?></p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <div class="text-sm text-gray-500">Progress</div>
                        <div class="text-2xl font-bold text-blue-600"><?php echo number_format($progress_percentage, 1); ?>%</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500">Step</div>
                        <div class="text-2xl font-bold text-gray-900"><?php echo $current_step; ?> / <?php echo $module_data['step_count']; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Training Progress</h3>
                <span class="px-3 py-1 bg-blue-100 text-blue-600 rounded-full text-sm font-medium">
                    <?php echo ucfirst($status); ?>
                </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="bg-blue-600 h-3 rounded-full progress-bar" style="width: <?php echo $progress_percentage; ?>%"></div>
            </div>
            <div class="flex justify-between text-sm text-gray-600 mt-2">
                <span><?php echo number_format($progress_percentage, 1); ?>% Complete</span>
                <span><?php echo $module_data['total_duration'] ?? $module_data['avg_duration'] ?? 30; ?> min estimated</span>
            </div>
        </div>

        <!-- Current Step Content -->
        <?php if ($current_step_data): ?>
        <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Step <?php echo $current_step; ?>: <?php echo $current_step_data['title']; ?></h2>
                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                        <span><i class="fas fa-clock mr-1"></i><?php echo $current_step_data['duration']; ?> min</span>
                        <span><i class="fas fa-tag mr-1"></i><?php echo ucfirst($current_step_data['type']); ?></span>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">Current Step</div>
                    <div class="text-lg font-bold text-blue-600"><?php echo $current_step; ?> of <?php echo $module_data['step_count']; ?></div>
                </div>
            </div>

            <!-- Step Content -->
            <div class="prose max-w-none mb-6">
                <p class="text-lg text-gray-700 leading-relaxed"><?php echo $current_step_data['content']; ?></p>
            </div>

            <!-- Learning Objectives -->
            <?php 
            $objectives = json_decode($current_step_data['learning_objectives'] ?? '[]', true);
            if (!empty($objectives)): 
            ?>
            <div class="bg-blue-50 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-blue-900 mb-3">
                    <i class="fas fa-target mr-2"></i>Learning Objectives
                </h3>
                <ul class="space-y-2">
                    <?php foreach ($objectives as $objective): ?>
                    <li class="flex items-center text-blue-800">
                        <i class="fas fa-check-circle mr-2 text-blue-600"></i>
                        <?php echo htmlspecialchars($objective); ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Dynamic Quizzes -->
            <?php if (!empty($step_quizzes)): ?>
            <div class="bg-yellow-50 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-yellow-900 mb-3">
                    <i class="fas fa-question-circle mr-2"></i>Interactive Quiz
                </h3>
                <?php foreach ($step_quizzes as $quiz): ?>
                <div class="mb-4">
                    <p class="text-yellow-800 mb-4"><?php echo htmlspecialchars($quiz['question']); ?></p>
                    <?php 
                    $options = json_decode($quiz['options'] ?? '[]', true);
                    if (!empty($options)):
                    ?>
                    <div class="space-y-2">
                        <?php foreach ($options as $index => $option): ?>
                        <div class="quiz-option p-3 rounded-lg border border-yellow-200 cursor-pointer" 
                             onclick="selectQuizOption(<?php echo $index; ?>, <?php echo $quiz['correct_answer']; ?>)">
                            <?php echo htmlspecialchars($option); ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <div id="quiz-explanation-<?php echo $quiz['id']; ?>" class="mt-4 p-3 bg-yellow-100 rounded-lg hidden">
                        <p class="text-yellow-800"><strong>Explanation:</strong> <?php echo htmlspecialchars($quiz['explanation']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Dynamic Simulations -->
            <?php if (!empty($step_simulations)): ?>
            <div class="bg-purple-50 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-purple-900 mb-3">
                    <i class="fas fa-play-circle mr-2"></i>Interactive Simulation
                </h3>
                <?php foreach ($step_simulations as $simulation): ?>
                <div class="mb-4">
                    <p class="text-purple-800 mb-4"><?php echo htmlspecialchars($simulation['instructions']); ?></p>
                    <div class="bg-white rounded-lg p-4 border border-purple-200">
                        <h4 class="font-semibold text-purple-900 mb-2">Simulation: <?php echo ucfirst(str_replace('_', ' ', $simulation['simulation_type'])); ?></h4>
                        <div class="text-sm text-purple-700">
                            <p><strong>Type:</strong> <?php echo $simulation['simulation_type']; ?></p>
                            <p><strong>Success Criteria:</strong> Complete within time limit with high accuracy</p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Dynamic Resources -->
            <?php if (!empty($step_resources)): ?>
            <div class="bg-green-50 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-green-900 mb-3">
                    <i class="fas fa-download mr-2"></i>Learning Resources
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($step_resources as $resource): ?>
                    <div class="bg-white rounded-lg p-4 border border-green-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-semibold text-green-900"><?php echo htmlspecialchars($resource['title']); ?></h4>
                                <p class="text-sm text-green-700"><?php echo htmlspecialchars($resource['description']); ?></p>
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-600 rounded text-xs mt-2">
                                    <?php echo ucfirst($resource['resource_type']); ?>
                                </span>
                            </div>
                            <div class="text-right">
                                <?php if ($resource['resource_type'] === 'link'): ?>
                                    <a href="<?php echo htmlspecialchars($resource['file_path']); ?>" target="_blank" 
                                       class="text-green-600 hover:text-green-700">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo htmlspecialchars($resource['file_path']); ?>" download 
                                       class="text-green-600 hover:text-green-700">
                                        <i class="fas fa-download"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Step Completion Button -->
            <div class="text-center">
                <form method="POST" class="inline-block">
                    <input type="hidden" name="action" value="complete_step">
                    <input type="hidden" name="step" value="<?php echo $current_step; ?>">
                    <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold text-lg">
                        <i class="fas fa-check mr-2"></i>Complete Step <?php echo $current_step; ?>
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Training Steps Overview -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Training Steps Overview</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($all_steps as $step): ?>
                <?php 
                $step_number = $step['step_number'];
                $step_class = '';
                $step_icon = '';
                
                if ($step_number < $current_step) {
                    $step_class = 'step-completed';
                    $step_icon = 'fas fa-check-circle';
                } elseif ($step_number == $current_step) {
                    $step_class = 'step-current';
                    $step_icon = 'fas fa-play-circle';
                } else {
                    $step_class = 'step-pending';
                    $step_icon = 'fas fa-circle';
                }
                ?>
                <div class="step-card p-4 rounded-lg border border-gray-200 <?php echo $step_class; ?>">
                    <div class="flex items-center mb-2">
                        <i class="<?php echo $step_icon; ?> mr-2"></i>
                        <span class="font-semibold">Step <?php echo $step_number; ?></span>
                    </div>
                    <h4 class="font-medium mb-1"><?php echo htmlspecialchars($step['title']); ?></h4>
                    <p class="text-sm opacity-75"><?php echo $step['duration_minutes']; ?> min • <?php echo ucfirst($step['step_type']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Navigation -->
        <div class="mt-8 flex justify-between">
            <a href="index.php" class="bg-gray-100 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-200 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Tutorials
            </a>
            
            <?php if ($status === 'completed'): ?>
                <div class="flex items-center space-x-4">
                    <span class="text-green-600 font-medium">
                        <i class="fas fa-check-circle mr-2"></i>Module Completed!
                    </span>
                    <a href="index.php" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-trophy mr-2"></i>View Certificate
                    </a>
                </div>
            <?php else: ?>
                <div class="text-sm text-gray-500">
                    Complete the current step to continue your training
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Quiz functionality
        function selectQuizOption(selectedIndex, correctAnswer) {
            const options = document.querySelectorAll('.quiz-option');
            const explanations = document.querySelectorAll('[id^="quiz-explanation-"]');
            
            // Remove previous selections
            options.forEach(option => {
                option.classList.remove('selected', 'correct', 'incorrect');
            });
            
            // Mark selected option
            options[selectedIndex].classList.add('selected');
            
            // Mark correct/incorrect
            if (selectedIndex == correctAnswer) {
                options[selectedIndex].classList.add('correct');
            } else {
                options[selectedIndex].classList.add('incorrect');
                options[correctAnswer].classList.add('correct');
            }
            
            // Show all explanations
            explanations.forEach(explanation => {
                explanation.classList.remove('hidden');
            });
        }

        // Auto-save progress (optional)
        function autoSaveProgress() {
            console.log('Auto-saving progress...');
        }
        
        // Set up auto-save every 30 seconds
        setInterval(autoSaveProgress, 30000);

        // Add smooth scrolling for better UX
        document.addEventListener('DOMContentLoaded', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    </script>
</body>
</html>
