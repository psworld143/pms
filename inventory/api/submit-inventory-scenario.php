<?php
/**
 * Submit Inventory Training Scenario API
 * Handles submission of training scenario answers and calculates scores
 */

// Suppress all warnings and errors
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering immediately
ob_start();

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database with output buffering
ob_start();
require_once __DIR__ . '/../../includes/database.php';
ob_end_clean();

// Clear any output that might have been generated
ob_clean();

// Set proper headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in');
    }
    
    $user_id = $_SESSION['user_id'];
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid input data');
    }
    
    $scenario_id = (int)($input['scenario_id'] ?? 0);
    $answers = $input['answers'] ?? [];
    
    if ($scenario_id <= 0) {
        throw new Exception('Invalid scenario ID');
    }
    
    $pdo->beginTransaction();
    
    // Get scenario details
    $stmt = $pdo->prepare("
        SELECT * FROM inventory_training_scenarios 
        WHERE id = ?
    ");
    $stmt->execute([$scenario_id]);
    $scenario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$scenario) {
        throw new Exception('Scenario not found');
    }
    
    // Get questions and correct answers
    $stmt = $pdo->prepare("
        SELECT id, correct_answer 
        FROM inventory_scenario_questions 
        WHERE scenario_id = ?
        ORDER BY question_order
    ");
    $stmt->execute([$scenario_id]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate score
    $correct_answers = 0;
    $total_questions = count($questions);
    
    foreach ($questions as $question) {
        $question_key = 'q' . $question['id'];
        if (isset($answers[$question_key]) && $answers[$question_key] === $question['correct_answer']) {
            $correct_answers++;
        }
    }
    
    $score = $total_questions > 0 ? round(($correct_answers / $total_questions) * 100) : 0;
    
    // Record attempt
    $stmt = $pdo->prepare("
        INSERT INTO inventory_training_attempts 
        (user_id, scenario_id, scenario_type, status, score, duration_minutes, answers, completed_at) 
        VALUES (?, ?, ?, 'completed', ?, 15, ?, NOW())
    ");
    $stmt->execute([
        $user_id, 
        $scenario_id, 
        $scenario['scenario_type'], 
        $score, 
        json_encode($answers)
    ]);
    
    $attempt_id = $pdo->lastInsertId();
    
    // Award certificate if score is high enough
    if ($score >= 80) {
        $certificate_name = $scenario['title'] . " Certificate";
        $stmt = $pdo->prepare("
            INSERT INTO inventory_training_certificates 
            (user_id, certificate_name, certificate_type, score, earned_at, status) 
            VALUES (?, ?, ?, ?, NOW(), 'earned')
            ON DUPLICATE KEY UPDATE score = VALUES(score), earned_at = NOW()
        ");
        $stmt->execute([
            $user_id, 
            $certificate_name, 
            $scenario['scenario_type'], 
            $score
        ]);
    }
    
    $pdo->commit();
    
    // Get questions with options for answer review
    $stmt = $pdo->prepare("
        SELECT 
            q.*,
            GROUP_CONCAT(
                CONCAT(
                    o.option_text, '|', o.option_value, '|', o.option_order
                ) 
                ORDER BY o.option_order 
                SEPARATOR '||'
            ) as options
        FROM inventory_scenario_questions q
        LEFT JOIN inventory_question_options o ON q.id = o.question_id
        WHERE q.scenario_id = ?
        GROUP BY q.id
        ORDER BY q.question_order
    ");
    $stmt->execute([$scenario_id]);
    $questions_with_options = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Parse options for each question
    foreach ($questions_with_options as &$question) {
        $question['options'] = [];
        if ($question['options']) {
            $option_pairs = explode('||', $question['options']);
            foreach ($option_pairs as $pair) {
                $parts = explode('|', $pair);
                if (count($parts) >= 2) {
                    $question['options'][] = [
                        'option_text' => $parts[0],
                        'option_value' => $parts[1],
                        'option_order' => $parts[2] ?? 0
                    ];
                }
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'score' => $score,
        'scenario_title' => $scenario['title'],
        'questions' => $questions_with_options,
        'user_answers' => $answers
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>