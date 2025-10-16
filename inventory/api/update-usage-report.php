<?php
require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = (int)($_SESSION['user_id'] ?? 0);
$user_role = strtolower($_SESSION['user_role'] ?? '');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$report_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
$room = trim($_POST['room'] ?? '');
$date_used = $_POST['date_used'] ?? date('Y-m-d');
$notes = trim($_POST['notes'] ?? '');

if (!$report_id || !$item_id || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Missing or invalid fields']);
    exit();
}

try {
    global $pdo;

    // Authorization: housekeeping can edit their own; manager can edit any
    if ($user_role !== 'manager') {
        $stmt = $pdo->prepare('SELECT user_id FROM inventory_usage_reports WHERE id = ?');
        $stmt->execute([$report_id]);
        $owner = $stmt->fetchColumn();
        if ((int)$owner !== $user_id) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit();
        }
    }

    $stmt = $pdo->prepare('UPDATE inventory_usage_reports SET item_id = ?, quantity = ?, room = ?, date_used = ?, notes = ?, updated_at = NOW() WHERE id = ?');
    $stmt->execute([$item_id, $quantity, $room, $date_used, $notes, $report_id]);

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
