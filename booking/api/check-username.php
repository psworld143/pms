<?php
session_start();
require_once "../config/database.php";
require_once '../includes/functions.php';

// Check if user is logged in and has manager access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['username'])) {
        throw new Exception('Username is required');
    }

    $username = trim($input['username']);

    if (strlen($username) < 3) {
        echo json_encode(['available' => false, 'message' => 'Username too short']);
        exit();
    }

    $available = !usernameExists($username);

    echo json_encode([
        'available' => $available,
        'message' => $available ? 'Username is available' : 'Username already exists'
    ]);

} catch (Exception $e) {
    error_log("Error checking username: " . $e->getMessage());
    echo json_encode([
        'available' => false,
        'message' => $e->getMessage()
    ]);
}
?>
