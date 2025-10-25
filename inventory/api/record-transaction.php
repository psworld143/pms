<?php
require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_role = $_SESSION['user_role'] ?? '';
if (!in_array($user_role, ['manager', 'housekeeping'])) {
    echo json_encode(['success' => false, 'message' => 'Access denied. Only managers and housekeeping can record transactions.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$type = $_POST['type'] ?? '';
$item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
$unit_cost = isset($_POST['unit_cost']) ? (float)$_POST['unit_cost'] : 0.0;

if (!$type || !$item_id || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Missing or invalid fields']);
    exit();
}

try {
    global $pdo;

    // Detect columns of inventory_transactions
    $cols = $pdo->query("SHOW COLUMNS FROM inventory_transactions")->fetchAll(PDO::FETCH_COLUMN, 0);
    $available = array_flip($cols);

    $fields = [];
    $values = [];

    if (isset($available['item_id'])) { $fields[] = 'item_id'; $values[] = $item_id; }
    if (isset($available['transaction_type'])) { $fields[] = 'transaction_type'; $values[] = $type; }
    if (isset($available['quantity'])) {
        // If type is out, store negative quantity; otherwise positive
        $fields[] = 'quantity';
        $values[] = $type === 'out' ? -abs($quantity) : abs($quantity);
    }
    if (isset($available['unit_cost'])) { $fields[] = 'unit_cost'; $values[] = $unit_cost; }
    if (isset($available['reason'])) { $fields[] = 'reason'; $values[] = 'Recorded via UI'; }
    if (isset($available['user_id'])) { $fields[] = 'user_id'; $values[] = $_SESSION['user_id']; }
    if (isset($available['performed_by'])) { $fields[] = 'performed_by'; $values[] = $_SESSION['user_id']; }
    if (isset($available['created_at'])) { $fields[] = 'created_at'; $values[] = date('Y-m-d H:i:s'); }

    if (!$fields) {
        echo json_encode(['success' => false, 'message' => 'No matching columns in inventory_transactions']);
        exit();
    }

    $placeholders = implode(',', array_fill(0, count($fields), '?'));
    $sql = 'INSERT INTO inventory_transactions (' . implode(',', $fields) . ') VALUES (' . $placeholders . ')';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($values);

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
