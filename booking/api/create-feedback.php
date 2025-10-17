<?php
/**
 * Create Feedback API
 * Hotel PMS - Guest Management Module
 */

require_once dirname(__DIR__, 2) . '/vps_session_fix.php';
require_once dirname(__DIR__, 2) . '/includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        exit();
    }
    
    // Validate required fields
    $requiredFields = ['guest_id', 'rating', 'comments'];
    foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '{$field}' is required"]);
            exit();
        }
    }
    
    // Insert feedback
    $sql = "
        INSERT INTO feedback (guest_id, rating, comments, created_at, status)
        VALUES (?, ?, ?, NOW(), 'pending')
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $input['guest_id'],
        $input['rating'],
        $input['comments']
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Feedback submitted successfully'
    ]);
    
} catch (PDOException $e) {
    error_log("Error creating feedback: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>
