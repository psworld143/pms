<?php
require_once __DIR__ . '/../../vps_session_fix.php';
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

$currentUserId = $_SESSION['user_id'] ?? null; // Optional: if missing, we will return latest entries

$roomNumber = $_GET['room_number'] ?? '';

try {
    // Detect supply requests table name
    $supplyTable = 'supply_requests';
    $candidates = ['supply_requests', 'inventory_supply_requests', 'room_supply_requests', 'housekeeping_supply_requests', 'room_item_requests'];
    foreach ($candidates as $candidate) {
        $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
        $stmt->execute([$candidate]);
        if ($stmt->fetchColumn()) { $supplyTable = $candidate; break; }
    }

    // Detect inventory_items display columns
    $iiColumns = $pdo->query('SHOW COLUMNS FROM inventory_items')->fetchAll(PDO::FETCH_COLUMN, 0);
    $hasItemName = in_array('item_name', $iiColumns, true);
    $hasName = in_array('name', $iiColumns, true);
    $nameExpr = $hasItemName ? 'ii.item_name' : ($hasName ? 'ii.name' : 'IFNULL(ii.sku, ii.description)');
    $unitExpr = in_array('unit', $iiColumns, true) ? 'ii.unit' : "''";

    // Detect supply table columns flexibly
    $srColumnsStmt = $pdo->prepare("SHOW COLUMNS FROM {$supplyTable}");
    $srColumnsStmt->execute();
    $srColumns = $srColumnsStmt->fetchAll(PDO::FETCH_COLUMN, 0);
    $qtyExpr = in_array('quantity_requested', $srColumns, true) ? 'sr.quantity_requested' : (in_array('quantity', $srColumns, true) ? 'sr.quantity' : (in_array('qty_requested', $srColumns, true) ? 'sr.qty_requested' : '0'));
    $reasonExpr = in_array('reason', $srColumns, true) ? 'sr.reason' : (in_array('request_reason', $srColumns, true) ? 'sr.request_reason' : (in_array('issue_type', $srColumns, true) ? 'sr.issue_type' : "''"));
    $statusExpr = in_array('status', $srColumns, true) ? 'sr.status' : "'pending'";
    $createdExpr = in_array('created_at', $srColumns, true) ? 'sr.created_at' : (in_array('request_date', $srColumns, true) ? 'sr.request_date' : (in_array('date_created', $srColumns, true) ? 'sr.date_created' : "NULL"));
    $approvedExpr = in_array('approved_at', $srColumns, true) ? 'sr.approved_at' : (in_array('approved_date', $srColumns, true) ? 'sr.approved_date' : "NULL");

    $sql = "
        SELECT sr.id,
               sr.room_number,
               {$nameExpr} AS item_name,
               {$qtyExpr} AS quantity,
               {$reasonExpr} AS reason,
               {$statusExpr} AS status,
               {$unitExpr} AS unit,
               sr.notes,
               {$createdExpr} AS created_at,
               {$approvedExpr} AS approved_at
        FROM {$supplyTable} sr
        LEFT JOIN inventory_items ii ON sr.item_id = ii.id
        WHERE 1=1
    ";

    $params = [];
    if ($roomNumber !== '') {
        $sql .= " AND sr.room_number = ?";
        $params[] = $roomNumber;
    } else {
        // Fallback: show the most recent requests for this user if available; otherwise show latest across all (for initial load)
        if ($currentUserId) {
            $sql .= " AND sr.requested_by = ?";
            $params[] = $currentUserId;
        }
    }

    $sql .= " ORDER BY {$createdExpr} DESC LIMIT 200";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If we asked for current user's history and got nothing, show latest across all
    if (!$rows) {
        $stmt = $pdo->prepare(
            "SELECT sr.id, sr.room_number, {$nameExpr} AS item_name, {$qtyExpr} AS quantity, {$reasonExpr} AS reason, {$statusExpr} AS status, {$unitExpr} AS unit, sr.notes, {$createdExpr} AS created_at, {$approvedExpr} AS approved_at
             FROM {$supplyTable} sr
             LEFT JOIN inventory_items ii ON sr.item_id = ii.id
             ORDER BY {$createdExpr} DESC
             LIMIT 50"
        );
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode(['success' => true, 'requests' => $rows]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error', 'detail' => $e->getMessage()]);
}
?>


