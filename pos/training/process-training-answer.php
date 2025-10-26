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
    error_log("POS Training Answer: Session check failed - pos_user_id not set. Session data: " . print_r($_SESSION, true));
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // From session bridge

// Log that we received a request
error_log("=== PROCESS-TRAINING-ANSWER.PHP STARTED ===");
error_log("Request method: " . $_SERVER["REQUEST_METHOD"]);

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    error_log("❌ Not a POST request - redirecting");
    // Gracefully bounce back to the last training page instead of dashboard
    $ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    if (strpos($ref, 'scenario-training.php') !== false) {
        header("Location: $ref");
    } else {
        header("Location: scenarios.php");
    }
    exit();
}

error_log("✅ POST request received");

// Get form data
$attempt_id = isset($_POST["attempt_id"]) ? (int)$_POST["attempt_id"] : 0;
$scenario_id = isset($_POST["scenario_id"]) ? (int)$_POST["scenario_id"] : 0;
$question_number = isset($_POST["question_number"]) ? (int)$_POST["question_number"] : 0;
$question_id = isset($_POST["question_id"]) ? (int)$_POST["question_id"] : 0;
$answer = isset($_POST["answer"]) ? $_POST["answer"] : "";
$response_text = isset($_POST['response']) ? trim($_POST['response']) : '';
$scenario_type = isset($_POST["scenario_type"]) ? $_POST["scenario_type"] : "";

// Debug logging
error_log("POS Training answer received: attempt_id=$attempt_id, scenario_id=$scenario_id, question_number=$question_number, answer='$answer', scenario_type='$scenario_type'");
error_log("POST data: " . print_r($_POST, true));
error_log("Session data: " . print_r($_SESSION, true));

if (!$attempt_id || !$scenario_id || !$question_number || !$question_id || !$scenario_type) {
    error_log("POS Training Answer: Missing required parameters - attempt_id=$attempt_id, scenario_id=$scenario_id, question_number=$question_number, question_id=$question_id, scenario_type='$scenario_type'");
    header("Location: scenarios.php");
    exit();
}

try {
    // First, get the scenario to get its scenario_id (VARCHAR)
    $stmt = $pdo->prepare("SELECT scenario_id FROM training_scenarios WHERE id = ?");
    $stmt->execute([$scenario_id]);
    $scenario = $stmt->fetch();
    
    if (!$scenario) {
        error_log("POS Training Answer: Scenario not found - scenario_id=$scenario_id");
        header("Location: scenarios.php");
        exit();
    }
    
    $scenario_string_id = $scenario['scenario_id'];
    
    // Verify the attempt belongs to this user and POS system (using string scenario_id)
    $stmt = $pdo->prepare("SELECT * FROM training_attempts WHERE id = ? AND user_id = ? AND scenario_id = ? AND scenario_type = ? AND system = 'pos'");
    $stmt->execute([$attempt_id, $user_id, $scenario_string_id, $scenario_type]);
    $attempt = $stmt->fetch();

    if (!$attempt) {
        error_log("POS Training Answer: Attempt not found or doesn't belong to user - attempt_id=$attempt_id, user_id=$user_id, scenario_string_id=$scenario_string_id");
        header("Location: scenarios.php");
        exit();
    }

    // Get current answers
    $answers = $attempt["answers"] ? json_decode($attempt["answers"], true) : [];
    
    // Update answers
    $answers[$question_number] = $answer !== '' ? $answer : $response_text;
    
    // Check if this is the last question
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_questions FROM scenario_questions WHERE scenario_id = ?");
    $stmt->execute([$scenario_id]);
    $rowCount = $stmt->fetch();
    $total_questions = (int)$rowCount["total_questions"];
    
    $status = "in_progress";
    $completed_at = null;
    
    // Debug logging
    error_log("POS Training Question check: question_number=$question_number, total_questions=$total_questions");
    
    // Check if this is the last question (use >= to handle edge cases)
    if ($question_number >= $total_questions) {
        // Training is complete
        $status = "completed";
        $completed_at = date("Y-m-d H:i:s");
        
        // Debug logging
        error_log("POS Training completed: question_number=$question_number, total_questions=$total_questions");
        
        // Calculate score: multiple choice uses correct_answer; free text scenarios (if any) get 100 on completion
        $stmt = $pdo->prepare("SELECT correct_answer, question_order FROM scenario_questions WHERE scenario_id = ? ORDER BY question_order");
        $stmt->execute([$scenario_id]);
        $rows = $stmt->fetchAll();
        $hasCorrect = false; $correct = 0;
        foreach ($rows as $row) {
            $hasCorrect = $hasCorrect || !empty($row['correct_answer']);
            $qn = (int)$row['question_order'];
            if (!empty($row['correct_answer']) && isset($answers[$qn]) && $answers[$qn] === $row['correct_answer']) {
                $correct++;
            }
        }
        if ($hasCorrect) {
            $score = ($total_questions > 0) ? ($correct / $total_questions) * 100 : 0;
        } else {
            // free-text scenario — mark 100 for completion (or use rubric later)
            $score = 100;
        }
        
        // Update attempt with final answers, score, and completion status
        error_log("✅ Updating training attempt with final results...");
        error_log("Attempt ID: $attempt_id");
        error_log("Score: $score");
        error_log("Correct answers: $correct out of $total_questions");
        error_log("Status: $status");
        
        $stmt = $pdo->prepare("
            UPDATE training_attempts 
            SET answers = ?, score = ?, status = ?, completed_at = ?
            WHERE id = ?
        ");
        $result = $stmt->execute([json_encode($answers), $score, $status, $completed_at, $attempt_id]);
        
        if ($result) {
            error_log("✅ Training attempt updated successfully!");
            error_log("Redirecting to: scenario-results.php?attempt_id=" . $attempt_id);
        } else {
            error_log("❌ Failed to update training attempt!");
        }
        
        // Redirect to results page
        header("Location: scenario-results.php?attempt_id=" . $attempt_id);
        exit();
    } else {
        // Update attempt with current answers
        $stmt = $pdo->prepare("
            UPDATE training_attempts 
            SET answers = ?
            WHERE id = ?
        ");
        $stmt->execute([json_encode($answers), $attempt_id]);
        
        // Redirect to next question
        $next_question = $question_number + 1;
        header("Location: scenario-training.php?id=" . $scenario_id . "&attempt_id=" . $attempt_id . "&question=" . $next_question);
        exit();
    }

} catch (PDOException $e) {
    error_log("POS Training Answer Error: " . $e->getMessage());
    header("Location: scenarios.php");
    exit();
}
?>
