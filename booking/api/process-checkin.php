<?php
/**
 * Process Check-in API
 * Handles guest check-in process
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
        throw new Exception('Invalid input data');
    }
    
    // Validate required fields
    $reservation_id = $input['reservation_id'] ?? null;
    $room_key_issued = $input['room_key_issued'] ?? null;
    $welcome_amenities = $input['welcome_amenities'] ?? null;
    $special_instructions = $input['special_instructions'] ?? '';
    
    if (!$reservation_id) {
        throw new Exception('Reservation ID is required');
    }
    
    if ($room_key_issued === null) {
        throw new Exception('Room key status is required');
    }
    
    if ($welcome_amenities === null) {
        throw new Exception('Welcome amenities status is required');
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
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Update reservation status
        $stmt = $pdo->prepare("
            UPDATE reservations 
            SET status = 'checked_in',
                checked_in_at = NOW(),
                checked_in_by = ?
            WHERE id = ?
        ");
        $stmt->execute([$_SESSION['user_id'], $reservation_id]);
        
        // Update room status
        $stmt = $pdo->prepare("
            UPDATE rooms 
            SET status = 'occupied'
            WHERE id = ?
        ");
        $stmt->execute([$reservation['room_id']]);
        
        // Insert check-in record
        $stmt = $pdo->prepare("
            INSERT INTO check_in_records (
                reservation_id,
                room_key_issued,
                welcome_amenities_provided,
                special_instructions,
                checked_in_by,
                checked_in_at
            ) VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $reservation_id,
            $room_key_issued,
            $welcome_amenities,
            $special_instructions,
            $_SESSION['user_id']
        ]);
        
        // Commit transaction
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Check-in completed successfully',
            'reservation_id' => $reservation_id
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Error processing check-in: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
