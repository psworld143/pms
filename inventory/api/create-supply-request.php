<?php
/**
 * Create Supply Request
 * Allows housekeeping users to create supply requests
 */

require_once __DIR__ . '/../../vps_session_fix.php';
require_once __DIR__ . '/../../includes/database.php';

// Check if user is logged in and has housekeeping role
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$user_role = $_SESSION['user_role'] ?? '';
if ($user_role !== 'housekeeping') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied - Housekeeping role required']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Validate required fields
$required_fields = ['item_id', 'quantity', 'room_number', 'reason'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit();
    }
}

try {
    $user_id = $_SESSION['user_id'];
    $item_id = (int)$_POST['item_id'];
    $quantity = (int)$_POST['quantity'];
    $room_number = trim($_POST['room_number']);
    $reason = trim($_POST['reason']);
    $notes = trim($_POST['notes'] ?? '');
    
    // Validate item exists (schema-adaptive for name field)
    $stmt = $pdo->prepare("SELECT id FROM inventory_items WHERE id = ?");
    $stmt->execute([$item_id]);
    $itemRow = $stmt->fetch();
    if (!$itemRow) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid item selected']);
        exit();
    }
    // Try to fetch a human-readable name for logging
    $itemName = null;
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM inventory_items")->fetchAll(PDO::FETCH_COLUMN, 0);
        $hasItemName = in_array('item_name', $cols, true);
        $hasName = in_array('name', $cols, true);
        if ($hasItemName) {
            $stmt = $pdo->prepare("SELECT item_name FROM inventory_items WHERE id = ?");
            $stmt->execute([$item_id]);
            $itemName = $stmt->fetchColumn();
        } elseif ($hasName) {
            $stmt = $pdo->prepare("SELECT name FROM inventory_items WHERE id = ?");
            $stmt->execute([$item_id]);
            $itemName = $stmt->fetchColumn();
        } else {
            $stmt = $pdo->prepare("SELECT COALESCE(sku, description) FROM inventory_items WHERE id = ?");
            $stmt->execute([$item_id]);
            $itemName = $stmt->fetchColumn();
        }
    } catch (Throwable $e) {
        $itemName = 'Item #' . $item_id;
    }
    
    // Validate room exists
    $stmt = $pdo->prepare("SELECT id FROM rooms WHERE room_number = ?");
    $stmt->execute([$room_number]);
    $room = $stmt->fetch();
    
    if (!$room) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid room number']);
        exit();
    }
    
    // Detect target supply requests table and available columns
    $supplyTable = 'supply_requests';
    $candidates = ['supply_requests', 'inventory_supply_requests', 'room_supply_requests', 'housekeeping_supply_requests', 'room_item_requests'];
    foreach ($candidates as $candidate) {
        $st = $pdo->prepare('SHOW TABLES LIKE ?');
        $st->execute([$candidate]);
        if ($st->fetchColumn()) { $supplyTable = $candidate; break; }
    }

    $columnsStmt = $pdo->prepare("SHOW COLUMNS FROM {$supplyTable}");
    $columnsStmt->execute();
    $cols = $columnsStmt->fetchAll(PDO::FETCH_COLUMN, 0);

    // Map adaptive column names
    $qtyCol = in_array('quantity_requested', $cols, true) ? 'quantity_requested' : (in_array('quantity', $cols, true) ? 'quantity' : (in_array('qty_requested', $cols, true) ? 'qty_requested' : null));
    $reasonCol = in_array('reason', $cols, true) ? 'reason' : (in_array('request_reason', $cols, true) ? 'request_reason' : (in_array('issue_type', $cols, true) ? 'issue_type' : null));
    $notesCol = in_array('notes', $cols, true) ? 'notes' : (in_array('request_notes', $cols, true) ? 'request_notes' : null);
    $statusCol = in_array('status', $cols, true) ? 'status' : null;
    $createdCol = in_array('created_at', $cols, true) ? 'created_at' : (in_array('request_date', $cols, true) ? 'request_date' : (in_array('date_created', $cols, true) ? 'date_created' : null));
    $requestedByCol = in_array('requested_by', $cols, true) ? 'requested_by' : (in_array('user_id', $cols, true) ? 'user_id' : null);
    $roomNumberCol = in_array('room_number', $cols, true) ? 'room_number' : (in_array('room', $cols, true) ? 'room' : null);
    $roomIdCol = !$roomNumberCol && in_array('room_id', $cols, true) ? 'room_id' : null;

    // Build INSERT dynamically using available columns
    $insertCols = [];
    $placeholders = [];
    $bindValues = [];

    // item_id
    $insertCols[] = 'item_id';
    $placeholders[] = '?';
    $bindValues[] = $item_id;

    if ($qtyCol) { $insertCols[] = $qtyCol; $placeholders[] = '?'; $bindValues[] = $quantity; }
    if ($roomNumberCol) { $insertCols[] = $roomNumberCol; $placeholders[] = '?'; $bindValues[] = $room_number; }
    elseif ($roomIdCol) { $insertCols[] = $roomIdCol; $placeholders[] = '?'; $bindValues[] = (int)($room['id'] ?? 0); }
    if ($reasonCol) { $insertCols[] = $reasonCol; $placeholders[] = '?'; $bindValues[] = $reason; }
    if ($notesCol) { $insertCols[] = $notesCol; $placeholders[] = '?'; $bindValues[] = $notes; }
    if ($requestedByCol) { $insertCols[] = $requestedByCol; $placeholders[] = '?'; $bindValues[] = $user_id; }
    if ($statusCol) { $insertCols[] = $statusCol; $placeholders[] = '?'; $bindValues[] = 'pending'; }
    if ($createdCol) { $insertCols[] = $createdCol; $placeholders[] = 'NOW()'; }

    $columnsSql = implode(', ', $insertCols);
    $valuesSql = implode(', ', array_map(function($ph){ return $ph === 'NOW()' ? 'NOW()' : '?'; }, $placeholders));
    $sql = "INSERT INTO {$supplyTable} ({$columnsSql}) VALUES ({$valuesSql})";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($bindValues);
    
    $request_id = $pdo->lastInsertId();
    
    // Log the activity
    $stmt = $pdo->prepare("
        INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent, created_at) 
        VALUES (?, 'supply_request_created', ?, ?, ?, NOW())
    ");
    
    $safeItemName = $itemName ?: ('Item #' . $item_id);
    $details = "Supply request created for {$safeItemName} (Qty: $quantity) in Room $room_number";
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    $stmt->execute([$user_id, $details, $ip_address, $user_agent]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Supply request created successfully',
        'request_id' => $request_id
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in create-supply-request.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>
