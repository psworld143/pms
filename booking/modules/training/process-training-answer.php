<?php
require_once "../../includes/session-config.php";
session_start();
require_once '../../config/database.php';;
require_once '../../includes/functions.php';
// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    error_log("Session check failed: user_id not set. Session data: " . print_r($_SESSION, true));
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // Gracefully bounce back to the last training page instead of dashboard
    $ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    if (strpos($ref, 'scenario-training.php') !== false || strpos($ref, 'customer-service-training.php') !== false || strpos($ref, 'problem-solving-training.php') !== false) {
        header("Location: $ref");
    } else {
        header("Location: scenarios.php");
    }
    exit();
}

// Get form data
$attempt_id = isset($_POST["attempt_id"]) ? (int)$_POST["attempt_id"] : 0;
$scenario_id = isset($_POST["scenario_id"]) ? (int)$_POST["scenario_id"] : 0;
$question_number = isset($_POST["question_number"]) ? (int)$_POST["question_number"] : 0;
$question_id = isset($_POST["question_id"]) ? (int)$_POST["question_id"] : 0;
$answer = isset($_POST["answer"]) ? $_POST["answer"] : "";
$response_text = isset($_POST['response']) ? trim($_POST['response']) : '';
$scenario_type = isset($_POST["scenario_type"]) ? $_POST["scenario_type"] : "";

// Debug logging
error_log("Training answer received: attempt_id=$attempt_id, scenario_id=$scenario_id, question_number=$question_number, answer='$answer', scenario_type='$scenario_type'");
error_log("POST data: " . print_r($_POST, true));
error_log("Session data: " . print_r($_SESSION, true));

if (!$attempt_id || !$scenario_id || !$question_number || !$question_id || !$scenario_type) {
    error_log("Missing required fields: attempt_id=$attempt_id, scenario_id=$scenario_id, question_number=$question_number, question_id=$question_id, scenario_type='$scenario_type'");
    $fallback = 'training-dashboard.php';
    if ($scenario_type === 'scenario') { $fallback = "scenario-training.php?id={$scenario_id}&attempt_id={$attempt_id}&question={$question_number}"; }
    elseif ($scenario_type === 'customer_service') { $fallback = "customer-service-training.php?id={$scenario_id}&attempt_id={$attempt_id}&question={$question_number}"; }
    elseif ($scenario_type === 'problem_solving') { $fallback = "problem-solving-training.php?id={$scenario_id}&attempt_id={$attempt_id}&question={$question_number}"; }
    header("Location: $fallback");
    exit();
}

// Check if answer is provided
if ($answer === '' && $response_text === '') {
    error_log("No answer provided, redirecting back to current question");
    $fallback = "scenario-training.php?id={$scenario_id}&attempt_id={$attempt_id}&question={$question_number}";
    header("Location: $fallback");
    exit();
}

try {
    // Verify the attempt belongs to the user
    $stmt = $pdo->prepare("
        SELECT * FROM training_attempts 
        WHERE id = ? AND user_id = ? AND scenario_id = ? AND scenario_type = ?
    ");
    $stmt->execute([$attempt_id, $user_id, $scenario_id, $scenario_type]);
    $attempt = $stmt->fetch();

    if (!$attempt) {
        // Create a fresh attempt if the passed attempt_id is invalid or expired
        $stmtCreate = $pdo->prepare("INSERT INTO training_attempts (user_id, scenario_id, scenario_type, status, created_at) VALUES (?, ?, ?, 'in_progress', NOW())");
        $stmtCreate->execute([$user_id, $scenario_id, $scenario_type]);
        $attempt_id = $pdo->lastInsertId();
        $attempt = [ 'id' => $attempt_id, 'answers' => null ];
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
    error_log("Question check: question_number=$question_number, total_questions=$total_questions");
    
    // Check if this is the last question (use >= to handle edge cases)
    if ($question_number >= $total_questions) {
        // Training is complete
        $status = "completed";
        $completed_at = date("Y-m-d H:i:s");
        
        // Debug logging
        error_log("Training completed: question_number=$question_number, total_questions=$total_questions");
        
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
    } else {
        $score = null;
    }
    
        // Update the attempt
        if ($status === "completed") {
            $stmt = $pdo->prepare("
                UPDATE training_attempts
                SET answers = ?, status = ?, score = ?, completed_at = ?
                WHERE id = ?
            ");
            $stmt->execute([
                json_encode($answers),
                $status,
                $score,
                $completed_at,
                $attempt_id
            ]);
            
            // Generate certificate for completed training
            generateTrainingCertificate($user_id, $attempt_id, $scenario_type);
        } else {
            $stmt = $pdo->prepare("
                UPDATE training_attempts
                SET answers = ?, status = ?
                WHERE id = ?
            ");
            $stmt->execute([
                json_encode($answers),
                $status,
                $attempt_id
            ]);
        }
    
    // Redirect based on scenario type and completion status
    if ($status === "completed") {
        // Training completed, redirect to results
        error_log("Training completed, redirecting to results");
        
        switch ($scenario_type) {
            case "customer_service":
                $redir = "customer-service-results.php?attempt_id=" . $attempt_id;
                break;
            case "problem_solving":
                $redir = "problem-solving-results.php?attempt_id=" . $attempt_id;
                break;
            case "scenario":
                $redir = "scenario-results.php?attempt_id=" . $attempt_id;
                break;
            default:
                $redir = "training-dashboard.php";
        }
        
        // Force redirect with multiple methods
        header("Location: $redir");
        echo "<meta http-equiv='refresh' content='0;url=$redir'>";
        echo "<script>window.location.href='$redir';</script>";
        echo "<p>Redirecting to results... <a href='$redir'>Click here if not redirected automatically</a></p>";
        exit();
    } else {
        // Continue to next question
        $next_question = $question_number + 1;
        
        // Debug logging
        error_log("Continuing to next question: current=$question_number, next=$next_question, total=$total_questions");
        
        switch ($scenario_type) {
            case "customer_service":
                $redir = "customer-service-training.php?id=" . $scenario_id . "&attempt_id=" . $attempt_id . "&question=" . $next_question;
                break;
            case "problem_solving":
                $redir = "problem-solving-training.php?id=" . $scenario_id . "&attempt_id=" . $attempt_id . "&question=" . $next_question;
                break;
            case "scenario":
                $redir = "scenario-training.php?id=" . $scenario_id . "&attempt_id=" . $attempt_id . "&question=" . $next_question;
                break;
            default:
                $redir = "training-dashboard.php";
        }
        
        // Force redirect with multiple methods
        error_log("Redirecting to next question: $redir");
        header("Location: $redir");
        echo "<meta http-equiv='refresh' content='0;url=$redir'>";
        echo "<script>window.location.href='$redir';</script>";
        echo "<p>Redirecting to next question... <a href='$redir'>Click here if not redirected automatically</a></p>";
        exit();
    }

} catch (PDOException $e) {
    error_log("Error processing training answer: " . $e->getMessage());
    // Try to return the user to the current scenario/question
    $qn = isset($question_number) ? (int)$question_number : 1;
    switch ($scenario_type ?? '') {
        case 'customer_service':
            header("Location: customer-service-training.php?id=" . ($scenario_id ?? 0) . "&attempt_id=" . ($attempt_id ?? 0) . "&question=" . $qn);
            break;
        case 'problem_solving':
            header("Location: problem-solving-training.php?id=" . ($scenario_id ?? 0) . "&attempt_id=" . ($attempt_id ?? 0) . "&question=" . $qn);
            break;
        case 'scenario':
        default:
            header("Location: scenario-training.php?id=" . ($scenario_id ?? 0) . "&attempt_id=" . ($attempt_id ?? 0) . "&question=" . $qn);
            break;
    }
    exit();
}
?>

