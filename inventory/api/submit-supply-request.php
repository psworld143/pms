<?php
require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_role = $_SESSION['user_role'] ?? '';
if (strtolower($user_role) !== 'housekeeping') {
    echo json_encode(['success' => false, 'message' => 'Access denied. Only housekeeping staff can submit supply requests.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
$priority = $_POST['priority'] ?? 'medium';
$needed_by = $_POST['needed_by'] ?? date('Y-m-d', strtotime('+1 day'));
$reason = $_POST['reason'] ?? '';

if (!$item_id || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Missing or invalid fields']);
    exit();
}

try {
    global $pdo;
    $user_id = $_SESSION['user_id'];
    
    // Resolve item label from inventory_items using available name column
    $cols = $pdo->query("SHOW COLUMNS FROM inventory_items")->fetchAll(PDO::FETCH_COLUMN, 0);
    $nameCol = in_array('item_name', $cols, true) ? 'item_name' : (in_array('name', $cols, true) ? 'name' : null);
    $select = $nameCol ? ("id, `" . $nameCol . "` AS item_label") : 'id, CONCAT("Item ", id) AS item_label';
    $stmt = $pdo->prepare("SELECT $select FROM inventory_items WHERE id = ? LIMIT 1");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$item) {
        echo json_encode(['success' => false, 'message' => 'Item not found']);
        exit();
    }
    
    // Dynamic insert into inventory_requests (schema tolerant)
    $rqCols = $pdo->query("SHOW COLUMNS FROM inventory_requests")->fetchAll(PDO::FETCH_COLUMN, 0);
    $rqHas = array_flip($rqCols);

    $fields = [];
    $values = [];
    $ph = [];

    // Common fields
    if (isset($rqHas['request_number'])) { $fields[] = 'request_number'; $values[] = 'RQ-' . date('YmdHis') . '-' . substr(bin2hex(random_bytes(3)), 0, 6); $ph[] = '?'; }
    if (isset($rqHas['item_name'])) { $fields[] = 'item_name'; $values[] = $item['item_label']; $ph[] = '?'; }
    if (isset($rqHas['item_id'])) { $fields[] = 'item_id'; $values[] = $item_id; $ph[] = '?'; }

    // Quantity variations
    if (isset($rqHas['quantity_requested'])) { $fields[] = 'quantity_requested'; $values[] = $quantity; $ph[] = '?'; }
    if (isset($rqHas['quantity'])) { $fields[] = 'quantity'; $values[] = $quantity; $ph[] = '?'; }
    if (isset($rqHas['qty_requested'])) { $fields[] = 'qty_requested'; $values[] = $quantity; $ph[] = '?'; }
    if (isset($rqHas['qty'])) { $fields[] = 'qty'; $values[] = $quantity; $ph[] = '?'; }

    if (isset($rqHas['department'])) { $fields[] = 'department'; $values[] = 'Housekeeping'; $ph[] = '?'; }
    if (isset($rqHas['priority'])) { $fields[] = 'priority'; $values[] = $priority; $ph[] = '?'; }
    if (isset($rqHas['status'])) { $fields[] = 'status'; $values[] = 'pending'; $ph[] = '?'; }
    if (isset($rqHas['requested_by'])) { $fields[] = 'requested_by'; $values[] = $user_id; $ph[] = '?'; }
    if (isset($rqHas['requested_at'])) { $fields[] = 'requested_at'; $values[] = date('Y-m-d H:i:s'); $ph[] = '?'; }
    if (isset($rqHas['created_at'])) { $fields[] = 'created_at'; $values[] = date('Y-m-d H:i:s'); $ph[] = '?'; }
    if (isset($rqHas['needed_by'])) { $fields[] = 'needed_by'; $values[] = $needed_by; $ph[] = '?'; }
    if (isset($rqHas['notes'])) { $fields[] = 'notes'; $values[] = $reason; $ph[] = '?'; }

    if (empty($fields)) {
        throw new Exception('inventory_requests table has no compatible columns');
    }

    $sql = 'INSERT INTO inventory_requests (' . implode(',', $fields) . ') VALUES (' . implode(',', $ph) . ')';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($values);
    
    echo json_encode(['success' => true, 'message' => 'Supply request submitted successfully']);
    
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
