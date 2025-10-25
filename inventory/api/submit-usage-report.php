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
    echo json_encode(['success' => false, 'message' => 'Access denied. Only housekeeping staff can submit usage reports.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
$room = $_POST['room'] ?? '';
$date_used = $_POST['date_used'] ?? date('Y-m-d');
$notes = $_POST['notes'] ?? '';

if (!$item_id || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Missing or invalid fields']);
    exit();
}

try {
    global $pdo;
    $user_id = $_SESSION['user_id'];

    $pdo->beginTransaction();

    // Always store usage report in our table (fixed schema)
    $stmt = $pdo->prepare("
        INSERT INTO inventory_usage_reports (
            item_id, user_id, quantity, room, date_used, notes, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$item_id, $user_id, $quantity, $room, $date_used, $notes]);

    // Insert into inventory_transactions using dynamic columns (schema-safe)
    $txCols = $pdo->query("SHOW COLUMNS FROM inventory_transactions")->fetchAll(PDO::FETCH_COLUMN, 0);
    $txHas = array_flip($txCols);

    $fields = [];
    $values = [];
    $ph = [];

    if (isset($txHas['item_id'])) { $fields[] = 'item_id'; $values[] = $item_id; $ph[] = '?'; }
    if (isset($txHas['transaction_type'])) { $fields[] = 'transaction_type'; $values[] = 'out'; $ph[] = '?'; }
    if (isset($txHas['quantity'])) { $fields[] = 'quantity'; $values[] = -abs($quantity); $ph[] = '?'; }
    if (isset($txHas['unit_cost'])) { $fields[] = 'unit_cost'; $values[] = 0; $ph[] = '?'; }
    if (isset($txHas['unit_price'])) { $fields[] = 'unit_price'; $values[] = 0; $ph[] = '?'; }
    if (isset($txHas['reason'])) { $fields[] = 'reason'; $values[] = 'Usage report - Room: ' . $room; $ph[] = '?'; }
    if (isset($txHas['user_id'])) { $fields[] = 'user_id'; $values[] = $user_id; $ph[] = '?'; }
    if (isset($txHas['performed_by'])) { $fields[] = 'performed_by'; $values[] = $user_id; $ph[] = '?'; }
    if (isset($txHas['created_at'])) { $fields[] = 'created_at'; $values[] = date('Y-m-d H:i:s'); $ph[] = '?'; }

    if (!empty($fields)) {
        $sql = 'INSERT INTO inventory_transactions (' . implode(',', $fields) . ') VALUES (' . implode(',', $ph) . ')';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
    }

    // Update inventory_items stock if column exists
    $itemCols = $pdo->query("SHOW COLUMNS FROM inventory_items")->fetchAll(PDO::FETCH_COLUMN, 0);
    $itemHas = array_flip($itemCols);
    if (isset($itemHas['current_stock'])) {
        $sql = 'UPDATE inventory_items SET current_stock = current_stock - ?';
        $params = [$quantity];
        if (isset($itemHas['last_updated'])) { $sql .= ', last_updated = NOW()'; }
        $sql .= ' WHERE id = ?';
        $params[] = $item_id;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Usage report submitted successfully']);

} catch (Throwable $e) {
    if ($pdo && $pdo->inTransaction()) { $pdo->rollBack(); }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
