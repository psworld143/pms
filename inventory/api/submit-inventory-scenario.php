<?php
/**
 * Submit Inventory Training Scenario API
 * Handles submission of training scenario answers and calculates scores
 */

// Suppress all warnings and errors
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering immediately
ob_start();

// Start session
if (session_status() !== PHP_SESSION_ACTIVE) {
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
    // Check if PDO is available
    if (!isset($pdo)) {
        throw new Exception('Database connection not available');
    }
    
    // Try to get input data first
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid input data');
    }
    
    // Get user_id from session or input data with fallback
    if (!isset($_SESSION['user_id'])) {
        // Try to get user_id from input data as fallback
        $user_id = $input['user_id'] ?? $_POST['user_id'] ?? $_GET['user_id'] ?? null;
        if (!$user_id) {
            throw new Exception('User not logged in');
        }
        $_SESSION['user_id'] = $user_id;
    } else {
        $user_id = $_SESSION['user_id'];
    }
    
    error_log('User ID: ' . $user_id);
    
    $scenario_id = (int)($input['scenario_id'] ?? 0);
    $answers = $input['answers'] ?? [];
    
    error_log('Scenario ID: ' . $scenario_id);
    error_log('Answers: ' . json_encode($answers));
    
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
    
    // Record attempt - Update or insert into inventory_training_progress
    $stationsql = "
        INSERT INTO inventory_training_progress 
        (user_id, scenario_id, status, score, time_taken, attempts, completed_at) 
        VALUES (?, ?, 'completed', ?, 15, 1, NOW())
        ON DUPLICATE KEY UPDATE 
            status = 'completed',
            score = ?,
            time_taken = 15,
            attempts = attempts + 1,
            completed_at = NOW()
    ";
    error_log('SQL: ' . $stationsql);
    $stmt = $pdo->prepare($stationsql);
    
    $execParams = [
        $user_id, 
        $scenario_id, 
        $score,
        $score // For ON DUPLICATE KEY UPDATE
    ];
    error_log('Executing with params: ' . json_encode($execParams));
    
    $stmt->execute($execParams);
    
    // Note: Certificate awarding can be implemented later if needed
    // For now, just recording the progress is sufficient
    
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
        $options_raw = $question['options']; // Store the raw GROUP_CONCAT result
        $question['options'] = []; // Initialize empty array
        
        if ($options_raw) { // Check the raw data, not the empty array
            $option_pairs = explode('||', $options_raw);
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
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Inventory Scenario Submit Error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => explode("\n", $e->getTraceAsString())
        ]
    ]);
}

// Flush output buffer
ob_end_flush();
?>