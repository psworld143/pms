<?php
/**
 * Get Inventory Training History API
 * Returns user's training history and progress
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
    
    $user_id = $_SESSION['user_id'];
    
    // Get training attempts
    $stmt = $pdo->prepare("
        SELECT 
            a.*,
            s.title as scenario_title,
            s.scenario_type,
            s.difficulty,
            s.points
        FROM inventory_training_attempts a
        JOIN inventory_training_scenarios s ON a.scenario_id = s.id
        WHERE a.user_id = ?
        ORDER BY a.started_at DESC
        LIMIT 50
    ");
    $stmt->execute([$user_id]);
    $attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get certificates
    $stmt = $pdo->prepare("
        SELECT * FROM inventory_training_certificates 
        WHERE user_id = ? AND status = 'earned'
        ORDER BY earned_at DESC
    ");
    $stmt->execute([$user_id]);
    $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate statistics
    $total_attempts = count($attempts);
    $completed_attempts = count(array_filter($attempts, function($a) { return $a['status'] === 'completed'; }));
    $avg_score = $total_attempts > 0 ? round(array_sum(array_column($attempts, 'score')) / $total_attempts) : 0;
    $certificates_earned = count($certificates);
    
    echo json_encode([
        'success' => true,
        'attempts' => $attempts,
        'certificates' => $certificates,
        'stats' => [
            'total_attempts' => $total_attempts,
            'completed_attempts' => $completed_attempts,
            'avg_score' => $avg_score,
            'certificates_earned' => $certificates_earned
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
