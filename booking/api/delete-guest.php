<?php
session_start();
require_once "../config/database.php";
require_once '../includes/functions.php';

// Check if user is logged in and has manager access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['manager', 'front_desk'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid input data');
    }
    
    // Validate required fields
    if (empty($input['guest_id'])) {
        throw new Exception('Guest ID is required');
    }
    
    $guest_id = intval($input['guest_id']);
    
    // Check if guest has active reservations
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as active_count
        FROM reservations 
        WHERE guest_id = ? AND status IN ('confirmed', 'checked_in')
    ");
    $stmt->execute([$guest_id]);
    $active_reservations = $stmt->fetch()['active_count'];
    
    if ($active_reservations > 0) {
        throw new Exception('Cannot delete guest with active reservations');
    }
    
    // Delete guest (soft delete - set as inactive)
    $stmt = $pdo->prepare("
        UPDATE guests 
        SET email = CONCAT('deleted_', UNIX_TIMESTAMP(), '_', email),
            phone = CONCAT('deleted_', UNIX_TIMESTAMP(), '_', phone),
            is_vip = FALSE,
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$guest_id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Guest not found');
    }
    
    // Log activity
    logActivity($_SESSION['user_id'], 'guest_deleted', "Deleted guest ID: {$guest_id}");
    
    echo json_encode([
        'success' => true,
        'message' => 'Guest deleted successfully'
    ]);
    
} catch (Exception $e) {
    error_log("Error deleting guest: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
