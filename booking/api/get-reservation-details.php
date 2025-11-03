<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
require_once "../config/database.php";
require_once '../includes/functions.php';
// Check if user is logged in and has access (manager or front_desk); allow API key fallback
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['manager', 'front_desk'])) {
    $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_SERVER['HTTP_API_KEY'] ?? null;
    if ($apiKey && $apiKey === 'pms_users_api_2024') {
        $_SESSION['user_id'] = 1073;
        $_SESSION['user_role'] = 'manager';
        $_SESSION['name'] = 'API User';
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit();
    }
}

header('Content-Type: application/json');

try {
    $raw_id = $_GET['id'] ?? null;
    $res_number = $_GET['reservation_number'] ?? null;
    
    if (!$raw_id && !$res_number) {
        throw new Exception('Reservation ID is required');
    }
    
    // Resolve numeric id from either id or reservation_number
    $reservation_id = null;
    if ($raw_id && preg_match('/^\d+$/', (string)$raw_id)) {
        $reservation_id = (int)$raw_id;
    } else if ($res_number) {
        $stmt = $pdo->prepare("SELECT id FROM reservations WHERE reservation_number = ? LIMIT 1");
        $stmt->execute([$res_number]);
        $row = $stmt->fetch();
        if ($row && isset($row['id'])) {
            $reservation_id = (int)$row['id'];
        }
    }
    if (!$reservation_id) {
        throw new Exception('Reservation not found');
    }
    
    $reservation = getReservationDetails($reservation_id);
    
    if (!$reservation) {
        throw new Exception('Reservation not found');
    }
    
    echo json_encode([
        'success' => true,
        'reservation' => $reservation
    ]);
    
} catch (Exception $e) {
    error_log("Error getting reservation details: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
