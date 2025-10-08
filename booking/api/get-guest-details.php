<?php
session_start();
require_once "../config/database.php";
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

try {
    $guest_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($guest_id <= 0) {
        throw new Exception('Guest ID is required');
    }

    $guest_details = getGuestDetails($guest_id);

    if (!$guest_details) {
        throw new Exception('Guest not found');
    }

    echo json_encode([
        'success' => true,
        'guest' => $guest_details,
    ]);
} catch (Exception $e) {
    error_log('Error getting guest details: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}
