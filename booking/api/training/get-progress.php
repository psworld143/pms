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

$user_id = $_SESSION['user_id'];

try {
    // Get overall progress statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_attempts,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_scenarios,
            AVG(CASE WHEN status = 'completed' THEN score END) as avg_score,
            SUM(CASE WHEN status = 'completed' THEN duration_minutes END) as total_time
        FROM training_attempts 
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $overall_stats = $stmt->fetch();
    
    // Get recent activity
    $stmt = $pdo->prepare("
        SELECT 
            ta.*,
            CASE 
                WHEN ta.scenario_type = 'scenario' THEN ts.title
                WHEN ta.scenario_type = 'customer_service' THEN css.title
                WHEN ta.scenario_type = 'problem_solving' THEN ps.title
                ELSE 'Unknown Scenario'
            END as scenario_title,
            ta.scenario_type
        FROM training_attempts ta
        LEFT JOIN training_scenarios ts ON ta.scenario_id = ts.id AND ta.scenario_type = 'scenario'
        LEFT JOIN customer_service_scenarios css ON ta.scenario_id = css.id AND ta.scenario_type = 'customer_service'
        LEFT JOIN problem_scenarios ps ON ta.scenario_id = ps.id AND ta.scenario_type = 'problem_solving'
        WHERE ta.user_id = ? AND ta.status = 'completed'
        ORDER BY ta.completed_at DESC
        LIMIT 10
    ");
    $stmt->execute([$user_id]);
    $recent_activity = $stmt->fetchAll();
    
    // Get certificates earned
    $stmt = $pdo->prepare("
        SELECT 
            tc.*,
            CASE 
                WHEN tc.scenario_type = 'scenario' THEN ts.title
                WHEN tc.scenario_type = 'customer_service' THEN css.title
                WHEN tc.scenario_type = 'problem_solving' THEN ps.title
                ELSE 'Unknown Scenario'
            END as scenario_title
        FROM training_certificates tc
        LEFT JOIN training_attempts ta ON tc.attempt_id = ta.id
        LEFT JOIN training_scenarios ts ON ta.scenario_id = ts.id AND ta.scenario_type = 'scenario'
        LEFT JOIN customer_service_scenarios css ON ta.scenario_id = css.id AND ta.scenario_type = 'customer_service'
        LEFT JOIN problem_scenarios ps ON ta.scenario_id = ps.id AND ta.scenario_type = 'problem_solving'
        WHERE tc.user_id = ?
        ORDER BY tc.issued_date DESC
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $certificates = $stmt->fetchAll();
    
    // Calculate completion rate
    $completion_rate = $overall_stats['total_attempts'] > 0 ? 
        round(($overall_stats['completed_scenarios'] / $overall_stats['total_attempts']) * 100, 1) : 0;
    
    // Format response
    $response = [
        'success' => true,
        'completion_rate' => $completion_rate,
        'average_score' => round($overall_stats['avg_score'] ?? 0, 1),
        'total_points' => 0, // This would need to be calculated based on your points system
        'recent_activity' => array_map(function($activity) {
            return [
                'scenario_title' => $activity['scenario_title'],
                'completed_at' => $activity['completed_at'],
                'score' => $activity['score'],
                'points' => 0 // This would need to be calculated
            ];
        }, $recent_activity),
        'certificates' => array_map(function($cert) {
            return [
                'name' => $cert['scenario_title'],
                'earned_at' => $cert['issued_date']
            ];
        }, $certificates)
    ];
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    error_log("Error fetching progress: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log("Error in get-progress.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred'
    ]);
}
?>