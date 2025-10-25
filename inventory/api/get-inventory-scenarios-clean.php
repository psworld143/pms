<?php
/**
 * Get Inventory Training Scenarios API (Clean Version)
 * Provides inventory training scenarios with robust error handling
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
        $user_id = $_POST['user_id'] ?? $_GET['user_id'] ?? null;
        if (!$user_id) {
            throw new Exception('User not logged in - no session or user_id provided');
        }
        $_SESSION['user_id'] = $user_id;
    }
    
    $user_id = $_SESSION['user_id'];
    $training_type = $_GET['training_type'] ?? $_POST['training_type'] ?? 'manager';
    
    // Define scenario types for each training type
    $manager_scenario_types = ['inventory_management', 'reporting', 'automation', 'monitoring', 'approval'];
    $housekeeping_scenario_types = ['room_inventory', 'approval', 'inventory_management'];
    
    // Build WHERE clause based on training type
    if ($training_type === 'manager') {
        $scenario_types = $manager_scenario_types;
    } else {
        $scenario_types = $housekeeping_scenario_types;
    }
    
    $placeholders = str_repeat('?,', count($scenario_types) - 1) . '?';
    
    // Get training scenarios filtered by type
    $stmt = $pdo->prepare("
        SELECT 
            s.*,
            COUNT(q.id) as question_count
        FROM inventory_training_scenarios s
        LEFT JOIN inventory_scenario_questions q ON s.id = q.scenario_id
        WHERE s.scenario_type IN ($placeholders)
        GROUP BY s.id
        ORDER BY s.created_at DESC
    ");
    $stmt->execute($scenario_types);
    $scenarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get user progress for each scenario
    $stmt = $pdo->prepare("
        SELECT 
            scenario_id,
            MAX(score) as best_score,
            COUNT(*) as attempts
        FROM inventory_training_attempts 
        WHERE user_id = ? AND status = 'completed'
        GROUP BY scenario_id
    ");
    $stmt->execute([$user_id]);
    $progress = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add progress data to scenarios
    foreach ($scenarios as &$scenario) {
        $scenario['user_progress'] = [
            'best_score' => 0,
            'attempts' => 0
        ];
        
        foreach ($progress as $prog) {
            if ($prog['scenario_id'] == $scenario['id']) {
                $scenario['user_progress'] = [
                    'best_score' => (int)$prog['best_score'],
                    'attempts' => (int)$prog['attempts']
                ];
                break;
            }
        }
    }
    
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