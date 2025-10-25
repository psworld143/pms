<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Check for API key in headers (for AJAX requests)
    $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_SERVER['HTTP_API_KEY'] ?? null;

    if ($apiKey && $apiKey === 'pms_users_api_2024') {
        // Valid API key, create session
        $_SESSION['user_id'] = 1073; // Default manager user
        $_SESSION['user_role'] = 'manager';
        $_SESSION['name'] = 'API User';
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $bill_id = filter_var($input['bill_id'] ?? null, FILTER_VALIDATE_INT);
    $status = filter_var($input['status'] ?? null, FILTER_SANITIZE_STRING);
    
    if (!$bill_id || !$status) {
        echo json_encode(['success' => false, 'message' => 'Bill ID and status are required']);
        exit();
    }
    
    $allowed_statuses = ['pending', 'paid', 'overdue', 'draft'];
    if (!in_array($status, $allowed_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit();
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE bills SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $bill_id]);
        
        if ($stmt->rowCount() > 0) {
            logActivity($_SESSION['user_id'], 'bill_status_updated', "Bill #{$bill_id} status updated to '{$status}'");
            echo json_encode(['success' => true, 'message' => 'Bill status updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Bill not found']);
        }
        
    } catch (PDOException $e) {
        error_log("Error updating bill status: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
}
?>

