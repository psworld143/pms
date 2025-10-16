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
if (!$report_id) {
    echo json_encode(['success' => false, 'message' => 'Missing id']);
    exit();
}

try {
    global $pdo;

    // Authorization
    if ($user_role !== 'manager') {
        $stmt = $pdo->prepare('SELECT user_id FROM inventory_usage_reports WHERE id = ?');
        $stmt->execute([$report_id]);
        $owner = $stmt->fetchColumn();
        if ((int)$owner !== $user_id) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit();
        }
    }

    // Get details for potential cleanup
    $stmt = $pdo->prepare('SELECT item_id, quantity, created_at FROM inventory_usage_reports WHERE id = ?');
    $stmt->execute([$report_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Delete usage report
    $stmt = $pdo->prepare('DELETE FROM inventory_usage_reports WHERE id = ?');
    $stmt->execute([$report_id]);

    // Optionally reverse stock and delete matching transaction if schema allows
    if ($row) {
        // Add back the stock if column exists
        try {
            $cols = $pdo->query("SHOW COLUMNS FROM inventory_items")->fetchAll(PDO::FETCH_COLUMN, 0);
            if (in_array('current_stock', $cols, true)) {
                $sql = 'UPDATE inventory_items SET current_stock = current_stock + ?';
                if (in_array('last_updated', $cols, true)) { $sql .= ', last_updated = NOW()'; }
                $sql .= ' WHERE id = ?';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([abs((int)$row['quantity']), $row['item_id']]);
            }
        } catch (Throwable $ignore) {}

        // Best-effort delete of a matching transaction created around same time
        try {
            $txCols = $pdo->query("SHOW COLUMNS FROM inventory_transactions")->fetchAll(PDO::FETCH_COLUMN, 0);
            if (in_array('item_id', $txCols, true) && in_array('transaction_type', $txCols, true) && in_array('quantity', $txCols, true)) {
                $timeCol = in_array('created_at', $txCols, true) ? 'created_at' : null;
                $qty = -abs((int)$row['quantity']);
                if ($timeCol) {
                    $stmt = $pdo->prepare("DELETE FROM inventory_transactions WHERE item_id = ? AND transaction_type = 'out' AND quantity = ? AND $timeCol BETWEEN DATE_SUB(?, INTERVAL 2 MINUTE) AND DATE_ADD(?, INTERVAL 2 MINUTE) LIMIT 1");
                    $stmt->execute([$row['item_id'], $qty, $row['created_at'], $row['created_at']]);
                } else {
                    $stmt = $pdo->prepare("DELETE FROM inventory_transactions WHERE item_id = ? AND transaction_type = 'out' AND quantity = ? LIMIT 1");
                    $stmt->execute([$row['item_id'], $qty]);
                }
            }
        } catch (Throwable $ignore) {}
    }

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
