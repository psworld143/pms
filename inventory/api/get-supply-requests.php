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

$roomNumber = isset($_GET['room_number']) ? trim((string)$_GET['room_number']) : '';
$requestedBy = isset($_GET['requested_by']) ? (int)$_GET['requested_by'] : 0;
$status = isset($_GET['status']) ? trim((string)$_GET['status']) : '';

// If housekeeping, force filter to own requests
if (strtolower($userRole) === 'housekeeping') {
    $requestedBy = $userId;
}

try {
    global $pdo;

    // Detect supply requests table
    $supplyTable = 'supply_requests';
    $candidates = ['supply_requests', 'inventory_supply_requests', 'room_supply_requests', 'housekeeping_supply_requests', 'room_item_requests'];
    foreach ($candidates as $candidate) {
        $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
        $stmt->execute([$candidate]);
        if ($stmt->fetchColumn()) { $supplyTable = $candidate; break; }
    }

    // Introspect columns for adaptive SQL
    $srColsStmt = $pdo->prepare("SHOW COLUMNS FROM {$supplyTable}");
    $srColsStmt->execute();
    $srCols = $srColsStmt->fetchAll(PDO::FETCH_COLUMN, 0);

    $qtyExprSql = in_array('quantity_requested', $srCols, true) ? 'sr.quantity_requested' : (in_array('quantity', $srCols, true) ? 'sr.quantity' : (in_array('qty_requested', $srCols, true) ? 'sr.qty_requested' : '0'));
    $reasonExprSql = in_array('reason', $srCols, true) ? 'sr.reason' : (in_array('request_reason', $srCols, true) ? 'sr.request_reason' : (in_array('issue_type', $srCols, true) ? 'sr.issue_type' : "''"));
    $statusExprSql = in_array('status', $srCols, true) ? 'sr.status' : "'pending'";
    $createdExprSql = in_array('created_at', $srCols, true) ? 'sr.created_at' : (in_array('request_date', $srCols, true) ? 'sr.request_date' : (in_array('date_created', $srCols, true) ? 'sr.date_created' : 'NOW()'));
    $requestedByColSql = in_array('requested_by', $srCols, true) ? 'sr.requested_by' : (in_array('user_id', $srCols, true) ? 'sr.user_id' : 'NULL');

    // Determine room join/select (force consistent collation in comparisons)
    $roomNumberSelectSql = 'sr.room_number AS room_number';
    $roomJoinSql = 'CONVERT(sr.room_number USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(r.room_number USING utf8mb4) COLLATE utf8mb4_unicode_ci';
    $roomNumberCol = in_array('room_number', $srCols, true) ? 'sr.room_number' : (in_array('room', $srCols, true) ? 'sr.room' : '');
    $roomIdCol = in_array('room_id', $srCols, true) ? 'sr.room_id' : '';
    if ($roomIdCol) { $roomNumberSelectSql = 'r.room_number AS room_number'; $roomJoinSql = "$roomIdCol = r.id"; }
    elseif ($roomNumberCol) { $roomNumberSelectSql = "$roomNumberCol AS room_number"; $roomJoinSql = "CONVERT($roomNumberCol USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(r.room_number USING utf8mb4) COLLATE utf8mb4_unicode_ci"; }
    else { $roomNumberSelectSql = 'r.room_number AS room_number'; $roomJoinSql = '0=1'; }

    // inventory_items adaptable name/unit
    $nameExprSql = 'ii.item_name'; $unitExprSql = "''";
    try {
        $columnsStmt = $pdo->query('SHOW COLUMNS FROM inventory_items');
        $columns = $columnsStmt->fetchAll(PDO::FETCH_COLUMN, 0);
        $nameExprSql = in_array('item_name', $columns, true) ? 'ii.item_name' : (in_array('name', $columns, true) ? 'ii.name' : 'IFNULL(ii.sku, ii.description)');
        $unitExprSql = in_array('unit', $columns, true) ? 'ii.unit' : "''";
    } catch (Throwable $ignore) {}

    $sql = "
        SELECT sr.id,
            {$roomNumberSelectSql},
            {$nameExprSql} AS item_name, {$unitExprSql} AS unit,
            {$qtyExprSql} AS quantity_requested,
            {$reasonExprSql} AS reason,
            {$statusExprSql} AS status,
            {$createdExprSql} AS created_at,
            u.name AS requested_by_name
        FROM {$supplyTable} sr
        LEFT JOIN users u ON {$requestedByColSql} = u.id
        LEFT JOIN inventory_items ii ON sr.item_id = ii.id
        LEFT JOIN rooms r ON {$roomJoinSql}
        WHERE 1=1";

    $params = [];
    if ($roomNumber !== '') {
        if (strpos($roomNumberSelectSql, 'r.room_number') !== false) { $sql .= " AND CONVERT(r.room_number USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(? USING utf8mb4) COLLATE utf8mb4_unicode_ci"; }
        else { $sql .= " AND CONVERT({$roomNumberSelectSql} USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(? USING utf8mb4) COLLATE utf8mb4_unicode_ci"; }
        $params[] = $roomNumber;
    }
    if ($requestedBy > 0 && $requestedByColSql !== 'NULL') {
        $sql .= " AND {$requestedByColSql} = ?";
        $params[] = $requestedBy;
    }
    if ($status !== '') {
        $sql .= " AND {$statusExprSql} = ?";
        $params[] = $status;
    }
    $sql .= " ORDER BY {$createdExprSql} DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'requests' => $rows]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
