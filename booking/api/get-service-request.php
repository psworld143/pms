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
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) throw new Exception('Invalid request ID');

    $stmt = $pdo->prepare("SELECT mr.*, r.room_number FROM maintenance_requests mr JOIN rooms r ON mr.room_id = r.id WHERE mr.id = ?");
    $stmt->execute([$id]);
    $req = $stmt->fetch();
    if (!$req) throw new Exception('Service request not found');

    echo json_encode(['success'=>true,'request'=>$req]);
} catch (Exception $e) {
    error_log('get-service-request error: ' . $e->getMessage());
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
?>

