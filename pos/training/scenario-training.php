<?php
// Error handling - enable for debugging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Log to console function
function console_log($data) {
    echo "<script>console.log(" . json_encode($data) . ");</script>";
}

// Start output buffering to catch any output before headers
ob_start();

echo "<!-- Debug: scenario-training.php loaded -->\n";
console_log("=== SCENARIO-TRAINING.PHP DEBUG START ===");

// Include session bridge for POS users
try {
    require_once __DIR__ . '/../../booking/modules/training/training-session-bridge.php';
    console_log("✅ Session bridge loaded");
} catch (Exception $e) {
    console_log("❌ Session bridge error: " . $e->getMessage());
    die("Session bridge error: " . $e->getMessage());
}

// Include database connection
try {
    require_once __DIR__ . '/../../includes/database.php';
    console_log("✅ Database connection loaded");
} catch (Exception $e) {
    console_log("❌ Database connection error: " . $e->getMessage());
    die("Database connection error: " . $e->getMessage());
}

try {
    require_once __DIR__ . '/../../booking/includes/functions.php';
    console_log("✅ Functions loaded");
} catch (Exception $e) {
    console_log("❌ Functions error: " . $e->getMessage());
    die("Functions error: " . $e->getMessage());
}

// Check if user is logged in to POS
console_log("Checking POS session...");
console_log("pos_user_id isset: " . (isset($_SESSION['pos_user_id']) ? 'YES' : 'NO'));
console_log("user_id isset: " . (isset($_SESSION['user_id']) ? 'YES' : 'NO'));

if (!isset($_SESSION['pos_user_id'])) {
    console_log("❌ Not logged in - redirecting to login");
    header('Location: ../login.php');
    exit();
}
console_log("✅ User logged in");

$user_id = $_SESSION['user_id']; // From session bridge
$user_name = $_SESSION['user_name']; // From session bridge

console_log("user_id from session: " . $user_id);
console_log("user_name from session: " . $user_name);

// Get scenario ID from URL
$scenario_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$attempt_id = isset($_GET['attempt_id']) ? (int)$_GET['attempt_id'] : 0;

console_log("GET parameters:");
console_log("scenario_id: " . $scenario_id);
console_log("attempt_id: " . $attempt_id);

if (!$scenario_id) {
    console_log("❌ No scenario_id provided - redirecting to scenarios.php");
    header('Location: scenarios.php');
    exit();
}

console_log("✅ Scenario ID is valid: " . $scenario_id);

// Fetch scenario details
try {
    console_log("Fetching scenario from database...");
    $stmt = $pdo->prepare("SELECT * FROM training_scenarios WHERE id = ?");
    $stmt->execute([$scenario_id]);
    $scenario = $stmt->fetch();

    if (!$scenario) {
        console_log("❌ Scenario not found in database - ID: " . $scenario_id);
        console_log("Redirecting to scenarios.php");
        header('Location: scenarios.php');
        exit();
    }

    console_log("✅ Scenario found: " . $scenario['title']);

    // Fetch questions for this scenario
    console_log("Fetching questions for scenario...");
    $stmt = $pdo->prepare("SELECT * FROM scenario_questions WHERE scenario_id = ? ORDER BY question_order");
    $stmt->execute([$scenario_id]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    console_log("Questions found: " . count($questions));

    // Fetch all options for these questions in one query and map them
    $questionIds = array_map(function($q){ return (int)$q['id']; }, $questions);
    $optionsMap = [];
    if (!empty($questionIds)) {
        $in = implode(',', array_fill(0, count($questionIds), '?'));
        $optStmt = $pdo->prepare("SELECT question_id, option_value, option_text FROM question_options WHERE question_id IN ($in) ORDER BY question_id, option_order");
        $optStmt->execute($questionIds);
        while ($row = $optStmt->fetch(PDO::FETCH_ASSOC)) {
            $qid = (int)$row['question_id'];
            if (!isset($optionsMap[$qid])) $optionsMap[$qid] = [];
            $optionsMap[$qid][$row['option_value']] = $row['option_text'];
        }
    }
    
    // Attach options to each question
    foreach ($questions as &$question) {
        $qid = (int)$question['id'];
        $question['options_array'] = $optionsMap[$qid] ?? [];
        
        // If no options found, create default options A-D based on correct_answer
        if (empty($question['options_array'])) {
            $question['options_array'] = [
                'A' => 'Option A',
                'B' => 'Option B', 
                'C' => 'Option C',
                'D' => 'Option D'
            ];
        }
    }

    // Handle attempt creation or retrieval
    if ($attempt_id) {
        // Continue existing attempt
        console_log("Continuing existing attempt...");
        $stmt = $pdo->prepare("
            SELECT * FROM training_attempts 
            WHERE id = ? AND user_id = ? AND scenario_id = ? AND scenario_type = 'training' AND system = 'pos'
        ");
        $stmt->execute([$attempt_id, $user_id, $scenario['scenario_id']]);  // Use scenario['scenario_id']
        $attempt = $stmt->fetch();
        
        console_log("Attempt found: " . ($attempt ? "YES" : "NO"));
        
        if (!$attempt) {
            header('Location: scenario-start.php?id=' . $scenario_id);
            exit();
        }
        
        $answers = $attempt['answers'] ? json_decode($attempt['answers'], true) : [];
        $current_question = isset($_GET['question']) ? (int)$_GET['question'] : 1;
        
        // Ensure current_question is within valid range
        if ($current_question < 1) $current_question = 1;
        if ($current_question > count($questions)) {
            // If we're past the last question, redirect to results
            header('Location: scenario-results.php?attempt_id=' . $attempt_id);
            exit();
        }
    } else {
        // Create new attempt
        console_log("Creating new training attempt...");
        console_log("user_id: " . $user_id);
        console_log("scenario_id (string): " . $scenario['scenario_id']);
        
        $stmt = $pdo->prepare("
            INSERT INTO training_attempts (user_id, scenario_id, scenario_type, system, status, score, created_at)
            VALUES (?, ?, 'training', 'pos', 'in_progress', 0.00, NOW())
        ");
        $stmt->execute([$user_id, $scenario['scenario_id']]);  // Use scenario['scenario_id'] not $scenario_id
        $attempt_id = $pdo->lastInsertId();
        
        console_log("✅ Training attempt created with ID: " . $attempt_id);
        
        $answers = [];
        $current_question = 1;
    }

} catch (PDOException $e) {
    console_log("❌ DATABASE ERROR: " . $e->getMessage());
    console_log("Error trace: " . $e->getTraceAsString());
    error_log("Error in POS training page: " . $e->getMessage());
    echo "<div style='background: red; color: white; padding: 20px; margin: 20px;'>";
    echo "<h1>Database Error</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
    exit();
}

// Set page title
$page_title = 'POS Training - ' . $scenario['title'];

console_log("✅ All checks passed - rendering page");
console_log("=== SCENARIO-TRAINING.PHP DEBUG END ===");

// Flush output buffer to send console logs
ob_end_flush();
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
    <!-- Progress Bar -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-lg font-semibold text-gray-900">
                <?php echo htmlspecialchars($scenario['title']); ?>
            </h2>
            <div class="text-sm text-gray-600">
                Question <?php echo $current_question; ?> of <?php echo count($questions); ?>
            </div>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="bg-purple-600 h-2 rounded-full transition-all duration-300" 
                 style="width: <?php echo ($current_question - 1) / count($questions) * 100; ?>%"></div>
        </div>
    </div>

    <!-- Question Container -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <?php if ($current_question <= count($questions)): ?>
            <?php $question = $questions[$current_question - 1]; ?>
            
            <div class="mb-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    Question <?php echo $current_question; ?>
                </h3>
                <p class="text-lg text-gray-700 leading-relaxed">
                    <?php echo htmlspecialchars($question['question']); ?>
                </p>
            </div>

            <form id="questionForm" method="POST" action="process-training-answer.php">
                <input type="hidden" name="attempt_id" value="<?php echo $attempt_id; ?>">
                <input type="hidden" name="scenario_id" value="<?php echo $scenario_id; ?>">
                <input type="hidden" name="question_number" value="<?php echo $current_question; ?>">
                <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                <input type="hidden" name="scenario_type" value="training">
                
                <div class="space-y-4">
                    <?php if (!empty($question['options_array'])): ?>
                        <?php foreach ($question['options_array'] as $value => $text): ?>
                            <label class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                <input type="radio" name="answer" value="<?php echo $value; ?>" 
                                       class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300"
                                   <?php echo (isset($answers[$current_question]) && $answers[$current_question] === $value) ? 'checked' : ''; ?>
                                   required>
                                <span class="ml-3 text-gray-700">
                                    <span class="font-medium"><?php echo $value; ?>.</span>
                                    <?php echo htmlspecialchars($text); ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-4 border border-yellow-200 bg-yellow-50 text-yellow-800 rounded">
                            This question has no options configured yet. Please contact an administrator.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="flex justify-between mt-8">
                    <?php if ($current_question > 1): ?>
                        <button type="button" onclick="previousQuestion()" 
                                class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Previous
                        </button>
                    <?php else: ?>
                        <div></div>
                    <?php endif; ?>

                    <?php if ($current_question < count($questions)): ?>
                        <button type="submit" 
                                class="px-6 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors">
                            Next
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    <?php else: ?>
                        <button type="submit" 
                                class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                            <i class="fas fa-check mr-2"></i>
                            Complete Training
                        </button>
                    <?php endif; ?>
                </div>
            </form>

        <?php else: ?>
            <!-- Training Complete -->
            <div class="text-center py-12">
                <div class="text-6xl text-green-500 mb-4">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Training Complete!</h3>
                <p class="text-gray-600 mb-6">
                    You have completed all questions for this scenario. Click below to view your results.
                </p>
                <a href="scenario-results.php?attempt_id=<?php echo $attempt_id; ?>" 
                   class="inline-flex items-center px-6 py-3 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors">
                    <i class="fas fa-chart-bar mr-2"></i>
                    View Results
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Scenario Context -->
    <div class="bg-gray-50 rounded-lg p-4">
        <h4 class="text-sm font-semibold text-gray-700 mb-2">
            <i class="fas fa-info-circle mr-2"></i>
            Scenario Context
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <strong>Description:</strong> <?php echo htmlspecialchars($scenario['description']); ?>
            </div>
            <div>
                <strong>Category:</strong> <?php echo ucwords(str_replace('_', ' ', $scenario['category'])); ?>
            </div>
        </div>
    </div>
</main>

<!-- Include POS sidebar script -->
<script src="../assets/js/pos-sidebar.js"></script>

<script>
function previousQuestion() {
    const currentQuestion = <?php echo $current_question; ?>;
    if (currentQuestion > 1) {
        window.location.href = `?id=<?php echo $scenario_id; ?>&attempt_id=<?php echo $attempt_id; ?>&question=${currentQuestion - 1}`;
    }
}

// Form submission handler
document.getElementById('questionForm').addEventListener('submit', function(e) {
    console.log('Form submit event triggered');
    
    const selectedAnswer = document.querySelector('input[name="answer"]:checked');
    console.log('Selected answer:', selectedAnswer);
    
    if (!selectedAnswer) {
        e.preventDefault();
        alert('Please select an answer before continuing.');
        return false;
    }
    
    console.log('Form submitting with answer:', selectedAnswer.value);
    
    // Show loading state
    const submitBtn = document.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
    }
    
    // Allow form to submit - don't prevent default
    console.log('Allowing form submission');
});
</script>
        </main>
    </div>
</body>
</html>
