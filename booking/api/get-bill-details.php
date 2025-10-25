<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
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

$bill_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if (!$bill_id) {
    echo json_encode(['success' => false, 'message' => 'Bill ID is required']);
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT b.*, 
               CONCAT(g.first_name, ' ', g.last_name) as guest_name,
               g.email as guest_email,
               g.phone as guest_phone,
               r.room_number,
               r.room_type,
               res.check_in_date,
               res.check_out_date,
               res.status as reservation_status,
               res.adults,
               res.children
        FROM bills b
        JOIN reservations res ON b.reservation_id = res.id
        JOIN guests g ON res.guest_id = g.id
        JOIN rooms r ON res.room_id = r.id
        WHERE b.id = ?
    ");
    $stmt->execute([$bill_id]);
    $bill = $stmt->fetch();

    if (!$bill) {
        echo json_encode(['success' => false, 'message' => 'Bill not found']);
        exit();
    }

    // Ensure all required fields have default values
    $bill['guest_name'] = $bill['guest_name'] ?? 'Unknown Guest';
    $bill['guest_email'] = $bill['guest_email'] ?? 'Not provided';
    $bill['guest_phone'] = $bill['guest_phone'] ?? 'Not provided';
    $bill['room_number'] = $bill['room_number'] ?? 'N/A';
    $bill['room_type'] = $bill['room_type'] ?? 'standard';
    $bill['check_in_date'] = $bill['check_in_date'] ?? date('Y-m-d');
    $bill['check_out_date'] = $bill['check_out_date'] ?? date('Y-m-d');
    $bill['adults'] = $bill['adults'] ?? 1;
    $bill['children'] = $bill['children'] ?? 0;
    $bill['total_amount'] = $bill['total_amount'] ?? 0;
    $bill['status'] = $bill['status'] ?? 'pending';

    echo json_encode([
        'success' => true,
        'bill' => $bill
    ]);

} catch (PDOException $e) {
    error_log("Error getting bill details: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>
