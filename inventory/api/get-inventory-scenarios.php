<?php
/**
 * Get Inventory Training Scenarios API
 * Returns all available training scenarios with their details
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
    
    $stmt = $pdo->prepare("
        SELECT 
            s.*,
            COUNT(q.id) as question_count,
            COALESCE(AVG(a.score), 0) as avg_score,
            COUNT(a.id) as attempt_count
        FROM inventory_training_scenarios s
        LEFT JOIN inventory_scenario_questions q ON s.id = q.scenario_id
        LEFT JOIN inventory_training_attempts a ON s.id = a.scenario_id AND a.user_id = ? AND a.status = 'completed'
        GROUP BY s.id
        ORDER BY s.difficulty, s.title
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $scenarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'scenarios' => $scenarios
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
