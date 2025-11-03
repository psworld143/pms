<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
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
    $raw_id = $_GET['reservation_id'] ?? null;
    
    if (!$raw_id) {
        throw new Exception('Reservation ID is required');
    }
    
    // Accept either numeric reservation id or reservation_number (e.g., RES2025...) from the frontend
    $reservation_id = is_numeric($raw_id) ? (int)$raw_id : 0;
    if ($reservation_id === 0) {
        // Try to resolve by reservation_number
        $lookup = $pdo->prepare("SELECT id FROM reservations WHERE reservation_number = ? LIMIT 1");
        $lookup->execute([$raw_id]);
        $row = $lookup->fetch();
        if ($row && isset($row['id'])) {
            $reservation_id = (int)$row['id'];
        }
    }
    
    if ($reservation_id === 0) {
        throw new Exception('Reservation not found');
    }
    
    $billing = getBillingSummary($reservation_id);
    
    if (!$billing) {
        // Last-resort safe payload to avoid UI failure
        error_log('Billing information not found for reservation ' . (int)$reservation_id . ' — returning empty summary');
        $billing = [
            'id' => null,
            'reservation_id' => (int)$reservation_id,
            'guest_id' => null,
            'room_rate' => 0.0,
            'room_charges' => 0.0,
            'subtotal' => 0.0,
            'additional_charges' => 0.0,
            'tax' => 0.0,
            'tax_amount' => 0.0,
            'discounts' => 0.0,
            'total_amount' => 0.0,
            'payment_status' => 'pending',
            'nights' => 1
        ];
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
    
    // Prefer using shared helper (ensures consistent schema across modules)
    if (function_exists('getReservationDetails')) {
        try {
            $res = getReservationDetails($reservation_id);
            if ($res && !empty($res['room_type'])) {
                // 1) Prefer explicit room rate stored on the room record
                $room_rate = 0.0;
                try {
                    $rr = $pdo->prepare("SELECT rate FROM rooms WHERE id = ?");
                    $rr->execute([$res['room_id'] ?? 0]);
                    $rateRow = $rr->fetch();
                    if ($rateRow && isset($rateRow['rate'])) {
                        $room_rate = (float)$rateRow['rate'];
                    }
                } catch (Throwable $e) {}

                // 2) Fallback to type-based pricing from Room Pricing module
                if ($room_rate <= 0) {
                    $DEFAULT_TYPE_RATES = [
                        'standard' => 500,
                        'deluxe' => 250,
                        'suite' => 400,
                        'presidential' => 800,
                    ];
                    $room_types = getRoomTypes();
                    $typeKeyLower = strtolower((string)$res['room_type']);
                    $room_rate = (float)($room_types[$typeKeyLower]['rate'] ?? $room_types[$res['room_type']]['rate'] ?? 0);
                    if ($room_rate <= 0) {
                        $room_rate = (float)($DEFAULT_TYPE_RATES[$typeKeyLower] ?? 0);
                    }
                }

                // 3) Final fallback: derive from reservation's stored total_amount (reverse of room_rate * nights * 1.10 tax)
                $nights = max(1, (int)ceil((strtotime($res['check_out_date']) - strtotime($res['check_in_date'])) / (60*60*24)));
                if ($room_rate <= 0 && !empty($res['total_amount']) && $nights > 0) {
                    $preTaxTotal = ((float)$res['total_amount']) / 1.10; // reverse 10% tax
                    $derivedRate = $preTaxTotal / $nights;
                    if ($derivedRate > 0) {
                        $room_rate = round($derivedRate, 2);
                    }
                }
                $nights = $nights ?? max(1, (int)ceil((strtotime($res['check_out_date']) - strtotime($res['check_in_date'])) / (60*60*24)));
                $room_charges = $room_rate * $nights;

                // Additional services from normalized service_charges
                $additional_charges = 0.0;
                try {
                    // Try by numeric reservation_id
                    $svcStmt = $pdo->prepare("SELECT COALESCE(SUM(sc.total_price), 0) as services_total FROM service_charges sc WHERE sc.reservation_id = ?");
                    $svcStmt->execute([$reservation_id]);
                    $additional_charges = (float)($svcStmt->fetch()['services_total'] ?? 0);
                    // If still zero, try by reservation_number column (some schemas store reservation_number)
                    if ($additional_charges <= 0.0001) {
                        $svcStmt = $pdo->prepare("SELECT COALESCE(SUM(sc.total_price), 0) as services_total FROM service_charges sc WHERE sc.reservation_number = (SELECT reservation_number FROM reservations WHERE id = ? LIMIT 1)");
                        $svcStmt->execute([$reservation_id]);
                        $additional_charges = (float)($svcStmt->fetch()['services_total'] ?? 0);
                    }
                } catch (PDOException $e) {
                    // Fallback for older schema
                    try {
                        $svcStmt = $pdo->prepare("SELECT COALESCE(SUM(COALESCE(rs.amount, 0) * COALESCE(rs.quantity,1)), 0) as services_total FROM reservation_services rs WHERE rs.reservation_id = ?");
                        $svcStmt->execute([$reservation_id]);
                        $additional_charges = (float)($svcStmt->fetch()['services_total'] ?? 0);
                        if ($additional_charges <= 0.0001) {
                            $svcStmt = $pdo->prepare("SELECT COALESCE(SUM(COALESCE(rs.amount, 0) * COALESCE(rs.quantity,1)), 0) as services_total FROM reservation_services rs WHERE rs.reservation_number = (SELECT reservation_number FROM reservations WHERE id = ? LIMIT 1)");
                            $svcStmt->execute([$reservation_id]);
                            $additional_charges = (float)($svcStmt->fetch()['services_total'] ?? 0);
                        }
                    } catch (PDOException $ignore) {}
                }

                // Discounts / vouchers
                $discStmt = $pdo->prepare("SELECT COALESCE(SUM(discount_amount),0) AS total_discounts
                                           FROM discounts
                                           WHERE description LIKE ?");
                $discStmt->execute(['%Reservation #' . (int)$reservation_id . '%']);
                $discounts_total = (float)($discStmt->fetch()['total_discounts'] ?? 0);

                // Business rule: Tax applies to room charges only (additional services are tax-exempt here)
                $tax_amount = round($room_charges * 0.10, 2);
                $total_amount = round($room_charges + $additional_charges + $tax_amount - $discounts_total, 2);

                return [
                    'id' => null,
                    'reservation_id' => $reservation_id,
                    'guest_id' => $res['guest_id'] ?? null,
                    'room_rate' => $room_rate,
                    'room_charges' => $room_charges,
                    // Subtotal shows room charges only to match UI expectation
                    'subtotal' => $room_charges,
                    'additional_charges' => $additional_charges,
                    'tax' => $tax_amount,
                    'tax_amount' => $tax_amount,
                    'discounts' => $discounts_total,
                    'total_amount' => $total_amount,
                    'payment_status' => 'pending',
                    'nights' => $nights
                ];
            }
        } catch (Throwable $e) {
            error_log('getReservationDetails path failed: ' . $e->getMessage());
        }
    }
    try {
        // Always compute from live reservation data first
        $resStmt = $pdo->prepare("SELECT r.*, rm.room_type, g.id as guest_id
                                  FROM reservations r
                                  JOIN rooms rm ON r.room_id = rm.id
                                  JOIN guests g ON r.guest_id = g.id
                                  WHERE r.id = ?");
        $resStmt->execute([$reservation_id]);
        $reservation = $resStmt->fetch();
        if (!$reservation) {
            return null;
        }

        $room_types = getRoomTypes();
        $room_rate = (float)($room_types[$reservation['room_type'] ?? 'standard']['rate'] ?? 0);
        $nights = max(1, (int)ceil((strtotime($reservation['check_out_date']) - strtotime($reservation['check_in_date'])) / (60*60*24)));
        $room_charges = $room_rate * $nights;

        $additional_charges = 0.0;
        try {
            $svcStmt = $pdo->prepare("SELECT COALESCE(SUM(sc.total_price), 0) as services_total FROM service_charges sc WHERE sc.reservation_id = ?");
            $svcStmt->execute([$reservation_id]);
            $additional_charges = (float)($svcStmt->fetch()['services_total'] ?? 0);
            if ($additional_charges <= 0.0001) {
                $svcStmt = $pdo->prepare("SELECT COALESCE(SUM(sc.total_price), 0) as services_total FROM service_charges sc WHERE sc.reservation_number = (SELECT reservation_number FROM reservations WHERE id = ? LIMIT 1)");
                $svcStmt->execute([$reservation_id]);
                $additional_charges = (float)($svcStmt->fetch()['services_total'] ?? 0);
            }
        } catch (PDOException $e) {
            try {
                $svcStmt = $pdo->prepare("SELECT COALESCE(SUM(COALESCE(rs.amount, 0) * COALESCE(rs.quantity,1)), 0) as services_total FROM reservation_services rs WHERE rs.reservation_id = ?");
                $svcStmt->execute([$reservation_id]);
                $additional_charges = (float)($svcStmt->fetch()['services_total'] ?? 0);
                if ($additional_charges <= 0.0001) {
                    $svcStmt = $pdo->prepare("SELECT COALESCE(SUM(COALESCE(rs.amount, 0) * COALESCE(rs.quantity,1)), 0) as services_total FROM reservation_services rs WHERE rs.reservation_number = (SELECT reservation_number FROM reservations WHERE id = ? LIMIT 1)");
                    $svcStmt->execute([$reservation_id]);
                    $additional_charges = (float)($svcStmt->fetch()['services_total'] ?? 0);
                }
            } catch (PDOException $ignore) {}
        }

        $discStmt = $pdo->prepare("SELECT COALESCE(SUM(discount_amount),0) AS total_discounts
                                   FROM discounts
                                   WHERE description LIKE ?");
        $discStmt->execute(['%Reservation #' . (int)$reservation_id . '%']);
        $discounts_total = (float)($discStmt->fetch()['total_discounts'] ?? 0);

        // Business rule: Tax applies to room charges only
        $tax_amount = round($room_charges * 0.10, 2);
        $total_amount = round($room_charges + $additional_charges + $tax_amount - $discounts_total, 2);

        // Try to upsert into billing, but return results regardless
        try {
            $find = $pdo->prepare("SELECT id FROM billing WHERE reservation_id = ?");
            $find->execute([$reservation_id]);
            $billRow = $find->fetch();
            if ($billRow) {
                $upd = $pdo->prepare("UPDATE billing SET room_charges = ?, additional_charges = ?, tax_amount = ?, total_amount = ?, payment_status = COALESCE(payment_status,'pending') WHERE id = ?");
                $upd->execute([$room_charges, $additional_charges, $tax_amount, $total_amount, $billRow['id']]);
            } else {
                $ins = $pdo->prepare("INSERT INTO billing (reservation_id, guest_id, room_charges, additional_charges, tax_amount, total_amount, payment_status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
                $ins->execute([$reservation_id, $reservation['guest_id'], $room_charges, $additional_charges, $tax_amount, $total_amount]);
            }
        } catch (PDOException $ignore) {}

        return [
            'id' => $billRow['id'] ?? null,
            'reservation_id' => $reservation_id,
            'guest_id' => $reservation['guest_id'],
            'room_rate' => $room_rate,
            'room_charges' => $room_charges,
            'subtotal' => $room_charges,
            'additional_charges' => $additional_charges,
            'tax' => $tax_amount,
            'tax_amount' => $tax_amount,
            'discounts' => $discounts_total,
            'total_amount' => $total_amount,
            'payment_status' => 'pending',
            'nights' => $nights
        ];
    } catch (PDOException $e) {
        error_log("Final live compute failed: " . $e->getMessage());
        return null;
    }
}
?>
