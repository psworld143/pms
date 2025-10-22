<?php
/**
 * Process Check-out API
 * Handles guest check-out process
 */

require_once dirname(__DIR__, 2) . '/vps_session_fix.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in and has front desk access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['front_desk', 'manager'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit();
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    error_log('Check-out input data: ' . json_encode($input));
    
    // Validate required fields
    $reservation_id = $input['reservation_id'] ?? null;
    $room_key_returned = $input['room_key_returned'] ?? null;
    $payment_status = $input['payment_status'] ?? null;
    $checkout_notes = $input['checkout_notes'] ?? '';
    
    if (!$reservation_id) {
        throw new Exception('Reservation ID is required');
    }
    
    if (!$room_key_returned) {
        throw new Exception('Room key status is required');
    }
    
    if (!$payment_status) {
        throw new Exception('Payment status is required');
    }
    
    // Get reservation details
    $reservation = getReservationDetails($reservation_id);
    if (!$reservation) {
        throw new Exception('Reservation not found');
    }
    // Check if reservation is checked in
    if ($reservation['status'] !== 'checked_in') {
        throw new Exception('Reservation is not checked in');
    }
    // Start transaction
    $pdo->beginTransaction();
    error_log("Starting check-out transaction for reservation ID: $reservation_id");
    
    try {
        // Update reservation status
        error_log("Updating reservation status to 'checked_out' for ID: $reservation_id");
        $stmt = $pdo->prepare("
            UPDATE reservations 
            SET status = 'checked_out',
                checked_out_at = NOW(),
                checked_out_by = ?
            WHERE id = ?
        ");
        $stmt->execute([$_SESSION['user_id'], $reservation_id]);
        error_log("Reservation status updated successfully");
        
        // Update room status to available and housekeeping status to dirty (needs cleaning)
        error_log("Updating room status to 'available' and housekeeping_status to 'dirty' for room ID: " . $reservation['room_id']);
        $stmt = $pdo->prepare("
            UPDATE rooms 
            SET status = 'available',
                housekeeping_status = 'dirty'
            WHERE id = ?
        ");
        $stmt->execute([$reservation['room_id']]);
        error_log("Room status updated successfully");
        
        // Update billing payment status if provided
        if ($payment_status) {
            $stmt = $pdo->prepare("
                UPDATE billing 
                SET payment_status = ?
                WHERE reservation_id = ?
            ");
            $stmt->execute([$payment_status, $reservation_id]);
        }
        
        // Log the checkout activity
        logActivity($_SESSION['user_id'], 'guest_checked_out', "Checked out guest for reservation {$reservation['reservation_number']}");
        
        // Commit transaction
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Check-out completed successfully',
            'reservation_id' => $reservation_id
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
} catch (Exception $e) {
    error_log("Error processing check-out: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
