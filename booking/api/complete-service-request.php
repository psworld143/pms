<?php
session_start();
require_once "../config/database.php";
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }
    $input = json_decode(file_get_contents('php://input'), true);
    if (empty($input['id'])) throw new Exception('Request ID is required');

    $stmt = $pdo->prepare("UPDATE maintenance_requests SET status = 'completed', updated_at = NOW() WHERE id = ?");
    $stmt->execute([(int)$input['id']]);

    logActivity($_SESSION['user_id'], 'service_request_completed', 'Completed service request #' . (int)$input['id']);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log('complete-service-request error: ' . $e->getMessage());
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
?>

