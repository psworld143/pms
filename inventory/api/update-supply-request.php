<?php
require_once __DIR__ . '/../../vps_session_fix.php';
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$userId = (int)($_SESSION['user_id'] ?? 0);
$userRole = $_SESSION['user_role'] ?? '';

if (strtolower($userRole) !== 'manager') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$requestId = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;
$action = isset($_POST['action']) ? trim((string)$_POST['action']) : '';
$notes = isset($_POST['notes']) ? trim((string)$_POST['notes']) : '';

if (!$requestId || !in_array($action, ['approve', 'reject'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

try {
    global $pdo;

    // Detect supply table
    $supplyTable = 'supply_requests';
    $candidates = ['supply_requests', 'inventory_supply_requests', 'room_supply_requests', 'housekeeping_supply_requests', 'room_item_requests'];
    foreach ($candidates as $candidate) {
        $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
        $stmt->execute([$candidate]);
        if ($stmt->fetchColumn()) { $supplyTable = $candidate; break; }
    }

    $status = $action === 'approve' ? 'approved' : 'rejected';

    // Introspect columns to build adaptive UPDATE
    $colsStmt = $pdo->prepare("SHOW COLUMNS FROM {$supplyTable}");
    $colsStmt->execute();
    $cols = $colsStmt->fetchAll(PDO::FETCH_COLUMN, 0);

    $setParts = [];
    $params = [':status' => $status, ':id' => $requestId];

    // status column
    if (in_array('status', $cols, true)) {
        $setParts[] = 'status = :status';
    }

    // approved_by variants
    if (in_array('approved_by', $cols, true)) {
        $setParts[] = 'approved_by = :uid';
        $params[':uid'] = $userId;
    } elseif (in_array('approver_id', $cols, true)) {
        $setParts[] = 'approver_id = :uid';
        $params[':uid'] = $userId;
    }

    // approved_at variants
    if (in_array('approved_at', $cols, true)) {
        $setParts[] = 'approved_at = NOW()';
    } elseif (in_array('approved_date', $cols, true)) {
        $setParts[] = 'approved_date = NOW()';
    }

    // notes variants
    if (in_array('approval_notes', $cols, true)) {
        $setParts[] = 'approval_notes = :notes';
        $params[':notes'] = $notes;
    } elseif (in_array('notes', $cols, true)) {
        $setParts[] = 'notes = :notes';
        $params[':notes'] = $notes;
    }

    // If nothing but status is available, ensure we still have at least that
    if (!$setParts) {
        $setParts[] = 'status = :status';
    }

    $sql = 'UPDATE ' . $supplyTable . ' SET ' . implode(', ', $setParts) . ' WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
