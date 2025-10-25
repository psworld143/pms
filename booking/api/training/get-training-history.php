<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in');
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Get training history for the user
    $stmt = $pdo->prepare("
        SELECT 
            ta.*,
            CASE 
                WHEN ta.scenario_type = 'training' THEN ts.title
                WHEN ta.scenario_type = 'customer_service' THEN css.title
                WHEN ta.scenario_type = 'problem' THEN ps.title
                ELSE 'Unknown Scenario'
            END as scenario_title,
            ta.scenario_type
        FROM training_attempts ta
        LEFT JOIN training_scenarios ts ON ta.scenario_id = ts.id AND ta.scenario_type = 'training'
        LEFT JOIN customer_service_scenarios css ON ta.scenario_id = css.id AND ta.scenario_type = 'customer_service'
        LEFT JOIN problem_scenarios ps ON ta.scenario_id = ps.id AND ta.scenario_type = 'problem'
        WHERE ta.user_id = ?
        ORDER BY ta.created_at DESC
        LIMIT 50
    ");
    $stmt->execute([$user_id]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'history' => $history
    ]);
    
} catch (Exception $e) {
    error_log('get-training-history: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

