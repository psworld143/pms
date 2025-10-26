<?php
// Error handling - enable for debugging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Include session bridge for POS users
require_once __DIR__ . '/../../booking/modules/training/training-session-bridge.php';

// Include database connection
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../booking/includes/functions.php';

// Check if user is logged in to POS
if (!isset($_SESSION['pos_user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id']; // From session bridge
$user_name = $_SESSION['user_name']; // From session bridge

// Get attempt ID from URL
$attempt_id = isset($_GET['attempt_id']) ? (int)$_GET['attempt_id'] : 0;

if (!$attempt_id) {
    header('Location: scenarios.php');
    exit();
}

try {
    // Fetch attempt details - POS ONLY
    $stmt = $pdo->prepare("
        SELECT ta.*, ts.title, ts.description, ts.category, ts.difficulty, ts.points
        FROM training_attempts ta
        JOIN training_scenarios ts ON ta.scenario_id = ts.scenario_id
        WHERE ta.id = ? AND ta.user_id = ? AND ta.system = 'pos'
    ");
    $stmt->execute([$attempt_id, $user_id]);
    $attempt = $stmt->fetch();

    if (!$attempt) {
        header('Location: scenarios.php');
        exit();
    }

    // Fetch questions and correct answers
    $stmt = $pdo->prepare("
        SELECT sq.*, qo.option_text, qo.option_value
        FROM scenario_questions sq
        LEFT JOIN question_options qo ON sq.id = qo.question_id
        WHERE sq.scenario_id = ?
        ORDER BY sq.question_order, qo.option_order
    ");
    $stmt->execute([$attempt['scenario_id']]);
    $questions_data = $stmt->fetchAll();

    // Organize questions and options
    $questions = [];
    foreach ($questions_data as $row) {
        $qid = $row['id'];
        if (!isset($questions[$qid])) {
            $questions[$qid] = [
                'id' => $row['id'],
                'question' => $row['question'],
                'question_order' => $row['question_order'],
                'correct_answer' => $row['correct_answer'],
                'options' => []
            ];
        }
        if ($row['option_text']) {
            $questions[$qid]['options'][$row['option_value']] = $row['option_text'];
        }
    }

    // Sort questions by order
    uasort($questions, function($a, $b) {
        return $a['question_order'] - $b['question_order'];
    });

    // Get user answers
    $user_answers = $attempt['answers'] ? json_decode($attempt['answers'], true) : [];

    // Calculate detailed results
    $total_questions = count($questions);
    $correct_answers = 0;
    $question_results = [];

    foreach ($questions as $qid => $question) {
        $user_answer = isset($user_answers[$question['question_order']]) ? $user_answers[$question['question_order']] : '';
        $is_correct = ($user_answer === $question['correct_answer']);
        
        if ($is_correct) {
            $correct_answers++;
        }

        $question_results[] = [
            'question' => $question['question'],
            'question_order' => $question['question_order'],
            'user_answer' => $user_answer,
            'correct_answer' => $question['correct_answer'],
            'is_correct' => $is_correct,
            'options' => $question['options']
        ];
    }

    $score = $total_questions > 0 ? ($correct_answers / $total_questions) * 100 : 0;
    $score_rounded = round($score, 1);

    // Determine performance level
    if ($score >= 90) {
        $performance_level = 'Excellent';
        $performance_color = 'text-green-600';
        $performance_bg = 'bg-green-100';
    } elseif ($score >= 80) {
        $performance_level = 'Good';
        $performance_color = 'text-blue-600';
        $performance_bg = 'bg-blue-100';
    } elseif ($score >= 70) {
        $performance_level = 'Satisfactory';
        $performance_color = 'text-yellow-600';
        $performance_bg = 'bg-yellow-100';
    } else {
        $performance_level = 'Needs Improvement';
        $performance_color = 'text-red-600';
        $performance_bg = 'bg-red-100';
    }

} catch (PDOException $e) {
    error_log("Error in POS training results: " . $e->getMessage());
    header('Location: scenarios.php');
    exit();
}

// Set page title
$page_title = 'POS Training Results - ' . $attempt['title'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Hotel POS System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/pos-styles.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#667eea',
                        secondary: '#764ba2'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <!-- Sidebar Overlay for Mobile -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden hidden" onclick="closeSidebar()"></div>
        
        <!-- Include POS-specific header and sidebar -->
        <?php include '../includes/pos-header.php'; ?>
        <?php include '../includes/pos-sidebar.php'; ?>
        
        <!-- Load sidebar JS after header to ensure proper function order -->
        <script src="../assets/js/pos-sidebar.js"></script>

        <!-- Main Content -->
        <main class="main-content pt-20 px-4 pb-4 lg:px-6 lg:pb-6 flex-1 transition-all duration-300">
    <!-- Results Header -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="text-center">
            <div class="text-6xl mb-4">
                <?php if ($score >= 80): ?>
                    <i class="fas fa-trophy text-yellow-500"></i>
                <?php elseif ($score >= 70): ?>
                    <i class="fas fa-medal text-gray-500"></i>
                <?php else: ?>
                    <i class="fas fa-redo text-red-500"></i>
                <?php endif; ?>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Training Complete!</h1>
            <p class="text-lg text-gray-600 mb-4"><?php echo htmlspecialchars($attempt['title']); ?></p>
            
            <!-- Score Display -->
            <div class="inline-flex items-center px-6 py-3 rounded-full <?php echo $performance_bg; ?> mb-6">
                <span class="text-2xl font-bold <?php echo $performance_color; ?> mr-2">
                    <?php echo $score_rounded; ?>%
                </span>
                <span class="text-lg font-medium <?php echo $performance_color; ?>">
                    <?php echo $performance_level; ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Score Breakdown -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <div class="text-3xl font-bold text-green-600 mb-2"><?php echo $correct_answers; ?></div>
            <div class="text-gray-600">Correct Answers</div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <div class="text-3xl font-bold text-red-600 mb-2"><?php echo $total_questions - $correct_answers; ?></div>
            <div class="text-gray-600">Incorrect Answers</div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <div class="text-3xl font-bold text-blue-600 mb-2"><?php echo $total_questions; ?></div>
            <div class="text-gray-600">Total Questions</div>
        </div>
    </div>

    <!-- Detailed Results -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Question Review</h2>
        
        <div class="space-y-6">
            <?php foreach ($question_results as $index => $result): ?>
                <div class="border border-gray-200 rounded-lg p-4 <?php echo $result['is_correct'] ? 'bg-green-50' : 'bg-red-50'; ?>">
                    <div class="flex items-start justify-between mb-3">
                        <h3 class="text-lg font-medium text-gray-900">
                            Question <?php echo $result['question_order']; ?>
                        </h3>
                        <div class="flex items-center">
                            <?php if ($result['is_correct']): ?>
                                <i class="fas fa-check-circle text-green-500 text-xl mr-2"></i>
                                <span class="text-green-600 font-medium">Correct</span>
                            <?php else: ?>
                                <i class="fas fa-times-circle text-red-500 text-xl mr-2"></i>
                                <span class="text-red-600 font-medium">Incorrect</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <p class="text-gray-700 mb-4"><?php echo htmlspecialchars($result['question']); ?></p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Your Answer:</h4>
                            <div class="p-2 bg-gray-100 rounded">
                                <?php if ($result['user_answer']): ?>
                                    <span class="font-medium"><?php echo $result['user_answer']; ?>.</span>
                                    <?php echo isset($result['options'][$result['user_answer']]) ? htmlspecialchars($result['options'][$result['user_answer']]) : 'Selected option'; ?>
                                <?php else: ?>
                                    <span class="text-gray-500 italic">No answer provided</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Correct Answer:</h4>
                            <div class="p-2 bg-green-100 rounded">
                                <span class="font-medium"><?php echo $result['correct_answer']; ?>.</span>
                                <?php echo isset($result['options'][$result['correct_answer']]) ? htmlspecialchars($result['options'][$result['correct_answer']]) : 'Correct option'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="scenarios.php" 
           class="inline-flex items-center px-6 py-3 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors">
            <i class="fas fa-list mr-2"></i>
            View All Scenarios
        </a>
        
        <a href="training-dashboard.php" 
           class="inline-flex items-center px-6 py-3 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors">
            <i class="fas fa-tachometer-alt mr-2"></i>
            Training Dashboard
        </a>
        
        <?php if ($score < 70): ?>
            <a href="scenario-training.php?id=<?php echo $attempt['scenario_id']; ?>" 
               class="inline-flex items-center px-6 py-3 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 transition-colors">
                <i class="fas fa-redo mr-2"></i>
                Retake Training
            </a>
        <?php endif; ?>
    </div>
        </main>
    </div>
</body>
</html>
