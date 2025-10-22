<?php
// Error handling
ini_set('display_errors', 0);
ini_set('log_errors', 1);


/**
 * Check-in Reservation API
 * Handles guest check-in process for walk-in reservations
 */

session_start();
require_once "../config/database.php";
require_once '../includes/functions.php';

// Check if user is logged in and has front desk access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['front_desk', 'manager'])) {
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
    $reservation_id = $input['reservation_id'] ?? null;
    
    if (!$reservation_id) {
        throw new Exception('Reservation ID is required');
    }
    
    // Get reservation details
    $reservation = getReservationDetails($reservation_id);
    if (!$reservation) {
        throw new Exception('Reservation not found');
    }
    
    // Check if reservation is already checked in
    if ($reservation['status'] === 'checked_in') {
        throw new Exception('Guest is already checked in');
    }
    
    // Check if reservation is confirmed or pending
    if (!in_array($reservation['status'], ['confirmed', 'pending'])) {
        throw new Exception('Reservation must be confirmed or pending to check in');
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Update reservation status to checked_in
        $stmt = $pdo->prepare("
            UPDATE reservations 
            SET status = 'checked_in',
                checked_in_at = NOW(),
                checked_in_by = ?
            WHERE id = ?
        ");
        $stmt->execute([$_SESSION['user_id'], $reservation_id]);
        
        // Update room status to occupied
        $stmt = $pdo->prepare("
            UPDATE rooms 
            SET status = 'occupied',
                housekeeping_status = 'dirty'
            WHERE id = ?
        ");
        $stmt->execute([$reservation['room_id']]);
        
        // Log the check-in activity
        logActivity($_SESSION['user_id'], 'guest_checked_in', "Checked in guest for reservation {$reservation['reservation_number']}");
        
        // Commit transaction
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Guest checked in successfully',
            'reservation_id' => $reservation_id,
            'reservation_number' => $reservation['reservation_number']
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Error checking in reservation: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
