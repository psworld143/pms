<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
require_once "../config/database.php";
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['front_desk', 'manager'])) {
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

header('Content-Type: application/json');

$payload = json_decode(file_get_contents('php://input'), true) ?? [];

$result = applyDiscountToReservation($payload);

echo json_encode($result);

function applyDiscountToReservation($data) {
    global $pdo;

    $required_fields = ['discount_template_id', 'reservation_id'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            return [
                'success' => false,
                'message' => 'Missing required field: ' . $field
            ];
        }
    }

    $discount_template_id = (int)$data['discount_template_id'];
    $reservation_id = (int)$data['reservation_id'];
    $applied_by = $_SESSION['user_id'] ?? null;

    try {
        $pdo->beginTransaction();

        // Get discount template details
        $stmt = $pdo->prepare("SELECT * FROM discount_templates WHERE id = ? AND is_active = 1");
        $stmt->execute([$discount_template_id]);
        $discount = $stmt->fetch();

        if (!$discount) {
            $pdo->rollBack();
            return [
                'success' => false,
                'message' => 'Discount template not found or inactive'
            ];
        }

        // Get reservation details
        $stmt = $pdo->prepare("
            SELECT r.*, g.first_name, g.last_name, rm.room_number, rm.type as room_type
            FROM reservations r
            JOIN guests g ON r.guest_id = g.id
            JOIN rooms rm ON r.room_id = rm.id
            WHERE r.id = ?
        ");
        $stmt->execute([$reservation_id]);
        $reservation = $stmt->fetch();

        if (!$reservation) {
            $pdo->rollBack();
            return [
                'success' => false,
                'message' => 'Reservation not found'
            ];
        }

        // Check if discount applies to this reservation
        $applies = false;
        if ($discount['apply_to_all_rooms'] == 1) {
            $applies = true;
        } elseif ($discount['room_id'] && $discount['room_id'] == $reservation['room_id']) {
            $applies = true;
        } elseif ($discount['room_type'] && $discount['room_type'] == $reservation['room_type']) {
            $applies = true;
        }

        if (!$applies) {
            $pdo->rollBack();
            return [
                'success' => false,
                'message' => 'This discount does not apply to the selected reservation'
            ];
        }

        // Calculate discount amount
        $discount_amount = 0;
        if ($discount['discount_type'] === 'percentage') {
            $discount_amount = ($reservation['total_amount'] * $discount['discount_value']) / 100;
        } else {
            $discount_amount = $discount['discount_value'];
        }

        // Apply discount to reservation
        $new_total = $reservation['total_amount'] - $discount_amount;
        $stmt = $pdo->prepare("UPDATE reservations SET total_amount = ? WHERE id = ?");
        $stmt->execute([$new_total, $reservation_id]);

        // Log the discount application
        $stmt = $pdo->prepare("INSERT INTO discounts (
                bill_id, discount_type, discount_value, discount_amount, 
                reason, description, applied_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            null, // No specific bill
            $discount['discount_type'],
            $discount['discount_value'],
            $discount_amount,
            $discount['discount_name'],
            "Applied to Reservation #{$reservation_id}",
            $applied_by
        ]);

        // Log activity
        $activity_description = "Applied discount '{$discount['discount_name']}' to reservation #{$reservation_id}";
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, created_at) VALUES (?, 'apply_discount', ?, NOW())");
        $stmt->execute([$applied_by, $activity_description]);

        $pdo->commit();

        return [
            'success' => true,
            'message' => 'Discount applied successfully',
            'discount_amount' => $discount_amount,
            'new_total' => $new_total
        ];

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("applyDiscountToReservation error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
}
?>