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

$guest_id = filter_var($_GET['guest_id'] ?? null, FILTER_VALIDATE_INT);

if (!$guest_id) {
    echo json_encode(['success' => false, 'message' => 'Guest ID is required']);
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT r.id, r.status, rm.room_number, r.check_in_date, r.check_out_date
        FROM reservations r
        JOIN rooms rm ON r.room_id = rm.id
        WHERE r.guest_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$guest_id]);
    $reservations = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'reservations' => $reservations
    ]);

} catch (PDOException $e) {
    error_log("Error getting guest reservations: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>