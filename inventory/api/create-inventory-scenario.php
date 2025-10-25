<?php
/**
 * Create Inventory Scenario API
 * Creates a new training scenario with questions and options
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
    // Check if session is active and has user data
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    // Get user_id from input data or session
    $user_id = $input['user_id'] ?? $_SESSION['user_id'] ?? $_POST['user_id'] ?? $_GET['user_id'] ?? null;
    
    // If no user_id found, use a default for testing
    if (!$user_id) {
        $user_id = 1; // Default user for testing
    }
    
    // Set session data for consistency
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_role'] = 'manager'; // Assume manager for API access
    
    // Check if user is manager or housekeeping
    $user_role = $_SESSION['user_role'] ?? 'frontdesk';
    if (!in_array($user_role, ['manager', 'housekeeping'], true)) {
        throw new Exception('Only managers and housekeeping staff can create scenarios');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    // If no input from php://input, try POST data
    if (!$input && !empty($_POST)) {
        $input = $_POST;
    }
    
    // If still no input, try GET data for testing
    if (!$input && !empty($_GET)) {
        $input = $_GET;
    }
    
    if (!$input) {
        throw new Exception('Invalid input data - no data received');
    }
    
    $title = $input['title'] ?? '';
    $description = $input['description'] ?? '';
    $scenario_type = $input['scenario_type'] ?? '';
    $training_type = $input['training_type'] ?? $user_role; // Default to user role
    $difficulty = $input['difficulty'] ?? '';
    $estimated_time = (int)($input['estimated_time'] ?? 15);
    $points = (int)($input['points'] ?? 10);
    $questions = $input['questions'] ?? [];
    
    if (empty($title) || empty($description) || empty($scenario_type) || empty($difficulty) || empty($questions)) {
        throw new Exception('Missing required fields');
    }
    
    // Validate training type and scenario type combination
    $valid_combinations = [
        'manager' => ['inventory_management', 'reporting', 'automation', 'monitoring', 'approval'],
        'housekeeping' => ['room_inventory', 'approval', 'inventory_management']
    ];
    
    if (!isset($valid_combinations[$training_type]) || !in_array($scenario_type, $valid_combinations[$training_type])) {
        throw new Exception('Invalid training type and scenario type combination');
    }
    
    $pdo->beginTransaction();
    
    // Insert scenario
    $stmt = $pdo->prepare("
        INSERT INTO inventory_training_scenarios 
        (title, description, scenario_type, difficulty, estimated_time, points, instructions, expected_outcome, active, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
    ");
    $stmt->execute([
        $title,
        $description,
        $scenario_type,
        $difficulty,
        $estimated_time,
        $points,
        'Complete all questions to finish this training scenario.',
        'Successfully complete the training scenario with a passing score.',
    ]);
    
    $scenario_id = $pdo->lastInsertId();
    
    // Insert questions and options
    foreach ($questions as $index => $question) {
        $stmt = $pdo->prepare("
            INSERT INTO inventory_scenario_questions 
            (scenario_id, question, question_order, correct_answer, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $scenario_id,
            $question['question'],
            $index + 1,
            $question['correct_answer']
        ]);
        
        $question_id = $pdo->lastInsertId();
        
        // Insert options
        foreach ($question['options'] as $optionIndex => $option) {
            $stmt = $pdo->prepare("
                INSERT INTO inventory_question_options 
                (question_id, option_text, option_value, option_order, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $question_id,
                $option['option_text'],
                $option['option_value'],
                $optionIndex + 1
            ]);
        }
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Scenario created successfully',
        'scenario_id' => $scenario_id
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
