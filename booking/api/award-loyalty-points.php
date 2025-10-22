<?php
// Error handling
ini_set('display_errors', 0);
ini_set('log_errors', 1);


/**
 * Award Loyalty Points API
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $guest_id = filter_var($input['guest_id'] ?? null, FILTER_VALIDATE_INT);
    $points = filter_var($input['points'] ?? null, FILTER_VALIDATE_INT);
    $description = filter_var($input['description'] ?? '', FILTER_SANITIZE_STRING);
    
    if (!$guest_id || !$points || $points <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid guest ID or points']);
        exit();
    }
    
    try {
        $result = awardLoyaltyPoints($guest_id, $points, $description);
        echo json_encode($result);
        
    } catch (Exception $e) {
        error_log("Error awarding loyalty points: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
}
?>
