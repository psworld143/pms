<?php
require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_role = $_SESSION['user_role'] ?? '';
if (strtolower($user_role) !== 'manager') {
    echo json_encode(['success' => false, 'message' => 'Access denied. Only managers can approve requests.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$request_id = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;
$action = $_POST['action'] ?? ''; // 'approve' or 'reject'
$notes = $_POST['notes'] ?? '';

if (!$request_id || !in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'message' => 'Missing or invalid parameters']);
    exit();
}

try {
    global $pdo;
    $manager_id = $_SESSION['user_id'];
    
    $pdo->beginTransaction();
    
    // Get request details (schema tolerant)
    $rqCols = $pdo->query("SHOW COLUMNS FROM inventory_requests")->fetchAll(PDO::FETCH_COLUMN, 0);
    $rqHas = array_flip($rqCols);
    $qtyCol = isset($rqHas['quantity_requested']) ? 'quantity_requested' : (isset($rqHas['quantity']) ? 'quantity' : null);
    $itemIdCol = isset($rqHas['item_id']) ? 'item_id' : null;
    $itemNameCol = isset($rqHas['item_name']) ? 'item_name' : null;

    $select = "requested_by, $qtyCol AS quantity";
    if ($itemIdCol) { $select .= ", $itemIdCol AS item_id"; }
    if ($itemNameCol) { $select .= ", $itemNameCol AS item_name"; }
    $stmt = $pdo->prepare("SELECT $select FROM inventory_requests WHERE id = ? AND status = 'pending' LIMIT 1");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        echo json_encode(['success' => false, 'message' => 'Request not found or already processed']);
        exit();
    }
    
    // Update request status
    $new_status = $action === 'approve' ? 'approved' : 'rejected';
    $stmt = $pdo->prepare("
        UPDATE inventory_requests 
        SET status = ?, processed_by = ?, processed_at = NOW(), notes = CONCAT(COALESCE(notes, ''), '\n', ?)
        WHERE id = ?
    ");
    $manager_notes = "Processed by manager: " . $notes;
    $stmt->execute([$new_status, $manager_id, $manager_notes, $request_id]);
    
    // If approved, create transaction and update stock
    if ($action === 'approve') {
        // Resolve item id and unit price
        if (!isset($request['item_id'])) {
            // look up by name if needed
            $itemCols = $pdo->query("SHOW COLUMNS FROM inventory_items")->fetchAll(PDO::FETCH_COLUMN, 0);
            $nameCol = in_array('item_name', $itemCols, true) ? 'item_name' : (in_array('name', $itemCols, true) ? 'name' : null);
            $select = $nameCol ? ("id, unit_price, `".$nameCol."` AS item_label") : 'id, unit_price, CONCAT("Item ", id) AS item_label';
            $stmt = $pdo->prepare("SELECT $select FROM inventory_items WHERE $nameCol = ? LIMIT 1");
            $stmt->execute([$request['item_name']]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $stmt = $pdo->prepare("SELECT id, unit_price FROM inventory_items WHERE id = ? LIMIT 1");
            $stmt->execute([$request['item_id']]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if ($item) {
            // Dynamic transaction insert
            $txCols = $pdo->query("SHOW COLUMNS FROM inventory_transactions")->fetchAll(PDO::FETCH_COLUMN, 0);
            $txHas = array_flip($txCols);
            $fields = [];$values=[];$ph=[];
            if (isset($txHas['item_id'])) { $fields[]='item_id'; $values[]=$item['id']; $ph[]='?'; }
            if (isset($txHas['transaction_type'])) { $fields[]='transaction_type'; $values[]='in'; $ph[]='?'; }
            if (isset($txHas['quantity'])) { $fields[]='quantity'; $values[]=abs((int)$request['quantity']); $ph[]='?'; }
            if (isset($txHas['unit_cost'])) { $fields[]='unit_cost'; $values[]=$item['unit_price'] ?: 0; $ph[]='?'; }
            if (isset($txHas['unit_price'])) { $fields[]='unit_price'; $values[]=$item['unit_price'] ?: 0; $ph[]='?'; }
            if (isset($txHas['reason'])) { $fields[]='reason'; $values[]='Approved supply request from housekeeping'; $ph[]='?'; }
            if (isset($txHas['performed_by'])) { $fields[]='performed_by'; $values[]=$manager_id; $ph[]='?'; }
            if (isset($txHas['user_id'])) { $fields[]='user_id'; $values[]=$manager_id; $ph[]='?'; }
            if (isset($txHas['created_at'])) { $fields[]='created_at'; $values[]=date('Y-m-d H:i:s'); $ph[]='?'; }
            if (!empty($fields)) { $sql='INSERT INTO inventory_transactions('.implode(',', $fields).') VALUES ('.implode(',', $ph).')'; $stmt=$pdo->prepare($sql); $stmt->execute($values); }

            // Update inventory stock
            $stmt = $pdo->prepare("
                UPDATE inventory_items 
                SET current_stock = current_stock + ?, last_updated = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([abs((int)$request['quantity']), $item['id']]);
        }
    }
    
    $pdo->commit();
    
    $message = $action === 'approve' ? 'Request approved successfully' : 'Request rejected';
    echo json_encode(['success' => true, 'message' => $message]);
    
} catch (Throwable $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
