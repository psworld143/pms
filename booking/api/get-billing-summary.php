<?php
session_start();
require_once "../config/database.php";
require_once '../includes/functions.php';
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

try {
    $reservation_id = $_GET['reservation_id'] ?? null;
    
    if (!$reservation_id) {
        throw new Exception('Reservation ID is required');
    }
    
    $billing = getBillingSummary($reservation_id);
    
    if (!$billing) {
        throw new Exception('Billing information not found');
    }
    
    echo json_encode([
        'success' => true,
        'billing' => $billing
    ]);
    
} catch (Exception $e) {
    error_log("Error getting billing summary: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get billing summary for a reservation
 */
function getBillingSummary($reservation_id) {
    global $pdo;
    
    try {
        // First, try to get existing billing record
        $stmt = $pdo->prepare("
            SELECT b.*, 
                   COALESCE(b.additional_charges, 0) as additional_charges,
                   COALESCE(b.room_charges, 0) as room_charges,
                   COALESCE(b.tax_amount, 0) as tax_amount,
                   COALESCE(b.total_amount, 0) as total_amount,
                   COALESCE(b.payment_status, 'pending') as payment_status
            FROM billing b
            WHERE b.reservation_id = ?
        ");
        $stmt->execute([$reservation_id]);
        $billing = $stmt->fetch();
        
        if ($billing) {
            // Ensure all required fields are present with proper types
            return [
                'id' => $billing['id'],
                'reservation_id' => $billing['reservation_id'],
                'guest_id' => $billing['guest_id'],
                'room_rate' => (float)$billing['room_charges'],
                'room_charges' => (float)$billing['room_charges'],
                'subtotal' => (float)$billing['room_charges'],
                'additional_charges' => (float)$billing['additional_charges'],
                'tax' => (float)$billing['tax_amount'],
                'tax_amount' => (float)$billing['tax_amount'],
                'discounts' => 0,
                'total_amount' => (float)$billing['total_amount'],
                'payment_status' => $billing['payment_status'],
                'nights' => 1
            ];
        }
        
        // If no billing record exists, create one based on reservation data
        $stmt = $pdo->prepare("
            SELECT r.*, g.id as guest_id, rm.room_type, rm.room_number
            FROM reservations r
            JOIN guests g ON r.guest_id = g.id
            JOIN rooms rm ON r.room_id = rm.id
            WHERE r.id = ?
        ");
        $stmt->execute([$reservation_id]);
        $reservation = $stmt->fetch();
        
        if (!$reservation) {
            return null;
        }
        
        // Calculate billing information
        $room_charges = $reservation['total_amount'];
        $additional_charges = 0;
        $tax_rate = 0.10; // 10% tax rate
        $tax_amount = $room_charges * $tax_rate;
        $total_amount = $room_charges + $additional_charges + $tax_amount;
        
        // Create billing record
        $stmt = $pdo->prepare("
            INSERT INTO billing (reservation_id, guest_id, room_charges, additional_charges, tax_amount, total_amount, payment_status)
            VALUES (?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([
            $reservation_id,
            $reservation['guest_id'],
            $room_charges,
            $additional_charges,
            $tax_amount,
            $total_amount
        ]);
        
        // Return the created billing record with JavaScript-compatible field names
        return [
            'id' => $pdo->lastInsertId(),
            'reservation_id' => $reservation_id,
            'guest_id' => $reservation['guest_id'],
            'room_rate' => $room_charges,
            'room_charges' => $room_charges,
            'subtotal' => $room_charges,
            'additional_charges' => $additional_charges,
            'tax' => $tax_amount,
            'tax_amount' => $tax_amount,
            'discounts' => 0,
            'total_amount' => $total_amount,
            'payment_status' => 'pending',
            'nights' => 1
        ];
        
    } catch (PDOException $e) {
        error_log("Error getting billing summary: " . $e->getMessage());
        return null;
    }
}
?>
