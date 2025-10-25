<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in');
    }
    
    // Check if user is manager
    $user_role = $_SESSION['user_role'] ?? 'front_desk';
    if ($user_role !== 'manager') {
        throw new Exception('Only managers can create new scenarios');
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    $scenario_type = $input['scenario_type'] ?? '';
    $difficulty = $input['difficulty'] ?? '';
    $estimated_time = (int)($input['estimated_time'] ?? 15);
    $points = (int)($input['points'] ?? 100);
    $description = $input['description'] ?? '';
    $questions = $input['questions'] ?? [];
    
    if (empty($scenario_type) || empty($difficulty) || empty($description) || empty($questions)) {
        throw new Exception('Missing required fields');
    }
    
    $pdo->beginTransaction();
    
    // Generate scenario ID
    $scenario_id = strtolower($scenario_type) . '_' . uniqid();
    
    // Create scenario title based on type
    $titles = [
        'front_desk' => 'Front Desk Check-in Process',
        'customer_service' => 'Customer Service Excellence',
        'problem_solving' => 'Problem Solving & Crisis Management'
    ];
    
    $title = $titles[$scenario_type] ?? 'Training Scenario';
    
    // Insert scenario
    $stmt = $pdo->prepare("
        INSERT INTO training_scenarios 
        (scenario_id, title, description, category, difficulty, estimated_time, points, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())
    ");
    $stmt->execute([
        $scenario_id,
        $title,
        $description,
        $scenario_type,
        $difficulty,
        $estimated_time,
        $points
    ]);
    
    $scenario_db_id = $pdo->lastInsertId();
    
    // Insert questions and options
    foreach ($questions as $index => $question) {
        $stmt = $pdo->prepare("
            INSERT INTO scenario_questions 
            (scenario_id, question, question_order, correct_answer, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $scenario_db_id,
            $question['question'],
            $index + 1,
            $question['correct_answer']
        ]);
        
        $question_id = $pdo->lastInsertId();
        
        // Insert options
        foreach ($question['options'] as $optionIndex => $option) {
            $stmt = $pdo->prepare("
                INSERT INTO question_options 
                (question_id, option_text, option_value, option_order, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $question_id,
                $option['text'],
                $option['value'],
                $optionIndex + 1
            ]);
        }
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Scenario created successfully',
        'scenario_id' => $scenario_db_id
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('create-scenario: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
