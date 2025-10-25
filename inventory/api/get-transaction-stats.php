<?php
/**
 * Get Transaction Statistics
 * Hotel PMS Training System - Inventory Module
 */

// Suppress warnings and notices for clean JSON output
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

session_start();
require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

try {
    global $pdo;
    $stats = [ 'total_transactions' => 0, 'total_in' => 0, 'total_out' => 0, 'total_value' => 0, 'usage_reports' => 0 ];

    // Detect columns
    $cols = $pdo->query("SHOW COLUMNS FROM inventory_transactions")->fetchAll(PDO::FETCH_COLUMN, 0);
    $hasType = in_array('transaction_type', $cols, true);
    $hasQty  = in_array('quantity', $cols, true);
    $hasCost = in_array('unit_cost', $cols, true);

    // Total transactions
    $stats['total_transactions'] = (int)$pdo->query('SELECT COUNT(*) FROM inventory_transactions')->fetchColumn();

    if ($hasType) {
        $stmt = $pdo->query("SELECT transaction_type, COUNT(*) as cnt FROM inventory_transactions GROUP BY transaction_type");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['transaction_type'] === 'in') $stats['total_in'] += (int)$row['cnt'];
            if ($row['transaction_type'] === 'out') $stats['total_out'] += (int)$row['cnt'];
        }
    } elseif ($hasQty) {
        // Infer in/out by sign of quantity
        $stmt = $pdo->query('SELECT quantity FROM inventory_transactions');
        while ($q = $stmt->fetchColumn()) {
            if ($q > 0) $stats['total_in']++; else if ($q < 0) $stats['total_out']++;
        }
    }

    if ($hasQty && $hasCost) {
        $stats['total_value'] = (float)$pdo->query('SELECT SUM(ABS(quantity) * unit_cost) FROM inventory_transactions')->fetchColumn();
    }

    // Usage reports total (if table exists)
    try {
        $pdo->query("SELECT 1 FROM inventory_usage_reports LIMIT 1");
        $stats['usage_reports'] = (int)$pdo->query('SELECT COUNT(*) FROM inventory_usage_reports')->fetchColumn();
    } catch (Throwable $ignore) {
        // Table may not exist in some schemas; keep as 0
    }

    echo json_encode(['success' => true, 'stats' => $stats]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>