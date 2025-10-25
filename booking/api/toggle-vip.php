<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Toggle VIP Status API
 * Hotel PMS - Guest Management Module
 */

session_start();
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
    
    $guestId = $input['id'] ?? null;
    $isVip = $input['is_vip'] ?? null;
    
    if (!$guestId || $isVip === null) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Guest ID and VIP status are required']);
        exit();
    }
    
    // Update VIP status
    $updateSql = "UPDATE guests SET is_vip = ?, updated_at = NOW() WHERE id = ?";
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute([(int)$isVip, $guestId]);
    
    if ($updateStmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Guest not found']);
        exit();
    }
    
    $status = $isVip ? 'VIP' : 'Regular';
    
    echo json_encode([
        'success' => true,
        'message' => "Guest status updated to {$status}",
        'is_vip' => (bool)$isVip
    ]);
    
} catch (PDOException $e) {
    error_log("Error toggling VIP status: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>
