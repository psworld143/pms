<?php
/**
 * Get Inventory Scenario Details API
 * Provides detailed scenario information with questions and options
 */

// Suppress ALL output and errors
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 0);

// Start output buffering immediately
ob_start();

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database with complete output suppression
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
    // Check if session is active and has user data
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_id'])) {
        // Try to get user_id from POST data as fallback
        $user_id = $_POST['user_id'] ?? $_GET['user_id'] ?? 1;
        $_SESSION['user_id'] = $user_id;
    }
    
    $scenario_id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
    
    if ($scenario_id <= 0) {
        throw new Exception('Invalid scenario ID');
    }
    
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
    
    // Get questions for this scenario
    $stmt = $pdo->prepare("
        SELECT * FROM inventory_scenario_questions 
        WHERE scenario_id = ? 
        ORDER BY question_order
    ");
    $stmt->execute([$scenario_id]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get options for each question
    foreach ($questions as &$question) {
        $stmt = $pdo->prepare("
            SELECT * FROM inventory_question_options 
            WHERE question_id = ? 
            ORDER BY option_order
        ");
        $stmt->execute([$question['id']]);
        $question['options'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true,
        'scenario' => $scenario,
        'questions' => $questions
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>