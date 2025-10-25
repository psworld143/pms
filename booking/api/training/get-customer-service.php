<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    // Get filter parameters
    $type = isset($_GET['type']) ? $_GET['type'] : '';
    
    // Build query
    $where_conditions = ["1=1"];
    $params = [];
    
    if (!empty($type)) {
        $where_conditions[] = "type = ?";
        $params[] = $type;
    }
    
    $where_clause = implode(" AND ", $where_conditions);
    
    $stmt = $pdo->prepare("
        SELECT 
            css.*,
            COALESCE(AVG(ta.score), 0) as avg_score,
            COUNT(ta.id) as attempt_count
        FROM customer_service_scenarios css
        LEFT JOIN training_attempts ta ON css.id = ta.scenario_id AND ta.scenario_type = 'customer_service'
        WHERE {$where_clause}
        GROUP BY css.id
        ORDER BY css.difficulty, css.title
    ");
    $stmt->execute($params);
    $scenarios = $stmt->fetchAll();
    
    // Format scenarios for frontend
    $formatted_scenarios = [];
    foreach ($scenarios as $scenario) {
        $formatted_scenarios[] = [
            'id' => $scenario['id'],
            'title' => $scenario['title'],
            'description' => $scenario['description'],
            'difficulty' => $scenario['difficulty'],
            'type' => $scenario['type'],
            'estimated_time' => $scenario['estimated_time'],
            'points' => $scenario['points'],
            'avg_score' => round($scenario['avg_score'], 1),
            'attempt_count' => $scenario['attempt_count']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'scenarios' => $formatted_scenarios
    ]);
    
} catch (PDOException $e) {
    error_log("Error fetching customer service scenarios: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log("Error in get-customer-service.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred'
    ]);
}
?>