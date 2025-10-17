<?php
/**
 * Delete Guest API
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
    
    $guestId = $input['id'] ?? null;
    
    if (!$guestId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Guest ID is required']);
        exit();
    }
    
    // Check if guest has active reservations
    $activeReservationsSql = "
        SELECT COUNT(*) as count 
        FROM reservations 
        WHERE guest_id = ? AND status IN ('confirmed', 'checked_in')
    ";
    
    $activeStmt = $pdo->prepare($activeReservationsSql);
    $activeStmt->execute([$guestId]);
    $activeCount = $activeStmt->fetch()['count'];
    
    if ($activeCount > 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Cannot delete guest with active reservations'
        ]);
        exit();
    }
    
    // Delete guest
    $deleteSql = "DELETE FROM guests WHERE id = ?";
    $deleteStmt = $pdo->prepare($deleteSql);
    $deleteStmt->execute([$guestId]);
    
    if ($deleteStmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Guest not found']);
        exit();
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Guest deleted successfully'
    ]);
    
} catch (PDOException $e) {
    error_log("Error deleting guest: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>