<?php
session_start();
require_once "../config/database.php";
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['front_desk', 'manager'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');

try {
    $guests = getAllGuests();

    echo json_encode([
        'success' => true,
        'guests' => $guests
    ]);
} catch (Exception $e) {
    error_log('Error in get-all-guests.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading guests'
    ]);
}
