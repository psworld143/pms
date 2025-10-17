<?php
require_once __DIR__ . '/../../vps_session_fix.php';
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$userId = (int)$_SESSION['user_id'];
$userRole = $_SESSION['user_role'] ?? '';
// Only housekeeping or manager can confirm receipt
if ($userRole !== 'housekeeping' && $userRole !== 'manager') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized role']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$requestId = (int)($_POST['request_id'] ?? 0);
$incrementRoomQty = isset($_POST['increment_room_quantity']) ? (int)$_POST['increment_room_quantity'] : 0;
$notes = trim($_POST['notes'] ?? '');

if (!$requestId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'request_id is required']);
    exit();
}

try {
    // detect supply table
    $supplyTable = 'supply_requests';
    $candidates = ['supply_requests','inventory_supply_requests','room_supply_requests','housekeeping_supply_requests','room_item_requests'];
    foreach ($candidates as $c) { $st = $pdo->prepare('SHOW TABLES LIKE ?'); $st->execute([$c]); if ($st->fetchColumn()) { $supplyTable = $c; break; } }

    // detect columns
    $cols = $pdo->query("SHOW COLUMNS FROM {$supplyTable}")->fetchAll(PDO::FETCH_COLUMN, 0);
    $qtyCol = in_array('quantity_requested',$cols,true)?'quantity_requested':(in_array('quantity',$cols,true)?'quantity':(in_array('qty_requested',$cols,true)?'qty_requested':null));
    $statusCol = in_array('status',$cols,true)?'status':null;
    $approvedAtCol = in_array('approved_at',$cols,true)?'approved_at':(in_array('approved_date',$cols,true)?'approved_date':null);
    $receivedAtCol = in_array('received_at',$cols,true)?'received_at':(in_array('date_received',$cols,true)?'date_received':null);
    $requestedByCol = in_array('requested_by',$cols,true)?'requested_by':(in_array('user_id',$cols,true)?'user_id':null);

    // fetch request
    $rq = $pdo->prepare("SELECT item_id, room_number, " . ($qtyCol?:'0') . " AS qty, " . ($requestedByCol?:'NULL') . " AS requested_by FROM {$supplyTable} WHERE id = ?");
    $rq->execute([$requestId]);
    $req = $rq->fetch(PDO::FETCH_ASSOC);
    if (!$req) { http_response_code(404); echo json_encode(['success'=>false,'message'=>'Request not found']); exit(); }

    if ($userRole === 'housekeeping' && $requestedByCol && (int)$req['requested_by'] !== $userId) {
        http_response_code(403);
        echo json_encode(['success'=>false,'message'=>'Cannot confirm another user\'s request']);
        exit();
    }

    // mark as received
    if ($statusCol) {
        $sql = "UPDATE {$supplyTable} SET {$statusCol} = 'received'";
        if ($receivedAtCol) { $sql .= ", {$receivedAtCol} = NOW()"; }
        if ($approvedAtCol && !empty($approvedAtCol)) { /* keep approved_at as-is if present */ }
        if (!empty($notes) && in_array('notes',$cols,true)) { $sql .= ", notes = CONCAT(IFNULL(notes,''), IF(notes IS NOT NULL AND notes!='', '\n', ''), ?)"; $stmt = $pdo->prepare($sql . " WHERE id = ?"); $stmt->execute([$notes, $requestId]); }
        else { $stmt = $pdo->prepare($sql . " WHERE id = ?"); $stmt->execute([$requestId]); }
    }

    // optional: increment room inventory current quantity
    if ($incrementRoomQty > 0) {
        // resolve room id from number
        $roomStmt = $pdo->prepare('SELECT id FROM rooms WHERE room_number = ?');
        $roomStmt->execute([$req['room_number']]);
        $roomId = (int)($roomStmt->fetchColumn() ?: 0);

        if ($roomId) {
            $riTable = 'room_inventory';
            $check = $pdo->prepare('SHOW TABLES LIKE ?');
            $check->execute([$riTable]);
            if (!$check->fetchColumn()) { $riTable = 'room_inventory_items'; }

            // ensure row exists
            $exists = $pdo->prepare("SELECT quantity_current FROM {$riTable} WHERE room_id = ? AND item_id = ?");
            $exists->execute([$roomId, (int)$req['item_id']]);
            $cur = $exists->fetchColumn();
            if ($cur === false) {
                $pdo->prepare("INSERT INTO {$riTable} (room_id, item_id, quantity_allocated, quantity_current, par_level, last_updated) VALUES (?, ?, 0, ?, 0, NOW())")
                    ->execute([$roomId, (int)$req['item_id'], $incrementRoomQty]);
                $before = 0; $after = $incrementRoomQty;
            } else {
                $before = (int)$cur; $after = $before + $incrementRoomQty;
                $pdo->prepare("UPDATE {$riTable} SET quantity_current = ? WHERE room_id = ? AND item_id = ?")
                    ->execute([$after, $roomId, (int)$req['item_id']]);
            }
            // log transaction
            try {
                $pdo->prepare("INSERT INTO room_inventory_transactions (room_id, item_id, transaction_type, quantity_change, quantity_before, quantity_after, user_id, notes) VALUES (?, ?, 'receive', ?, ?, ?, ?, ?)")
                    ->execute([$roomId, (int)$req['item_id'], $incrementRoomQty, $before, $after, $userId, $notes ?: 'Items received']);
            } catch (Throwable $e) { /* ignore */ }
        }
    }

    echo json_encode(['success'=>true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Database error','detail'=>$e->getMessage()]);
}

?>


