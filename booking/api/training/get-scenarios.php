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
    $difficulty = isset($_GET['difficulty']) ? $_GET['difficulty'] : '';
    $category = isset($_GET['category']) ? $_GET['category'] : '';
    
    // Build query
    $where_conditions = ["1=1"];
    $params = [];
    
    if (!empty($difficulty)) {
        $where_conditions[] = "difficulty = ?";
        $params[] = $difficulty;
    }
    
    if (!empty($category)) {
        $where_conditions[] = "category = ?";
        $params[] = $category;
    }
    
    $where_clause = implode(" AND ", $where_conditions);
    
    $stmt = $pdo->prepare("
        SELECT 
            ts.*,
            COALESCE(AVG(ta.score), 0) as avg_score,
            COUNT(ta.id) as attempt_count
        FROM training_scenarios ts
        LEFT JOIN training_attempts ta ON ts.id = ta.scenario_id AND ta.scenario_type = 'training'
        WHERE {$where_clause}
        GROUP BY ts.id
        ORDER BY ts.difficulty, ts.title
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
            'category' => $scenario['category'],
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
    error_log("Error fetching scenarios: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log("Error in get-scenarios.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred'
    ]);
}
?>