<?php
// Suppress all errors and warnings
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering
ob_start();

// Direct database connection without debug output
try {
    // Use the same database configuration as the main system
    require_once __DIR__ . '/../../includes/database.php';
    
    if (!isset($pdo) || !$pdo) {
        throw new Exception('Database connection not established');
    }
} catch (Exception $e) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Clean any output before sending JSON
ob_clean();
header('Content-Type: application/json');

try {

    $item_id = (int)($_POST['item_id'] ?? 0);
    $min = (int)($_POST['reorder_point'] ?? 0);
    $qty = (int)($_POST['reorder_quantity'] ?? 0);
    $supplier_id = isset($_POST['supplier_id']) && $_POST['supplier_id'] !== '' ? (int)$_POST['supplier_id'] : null;

    if (!$item_id) { echo json_encode(['success'=>false,'message'=>'Item required']); exit; }

    // Handle supplier_id constraint - try to use supplier_id from suppliers table
    $final_supplier_id = null;
    if ($supplier_id) {
        // Check if supplier exists in suppliers table
        $stmt = $pdo->prepare("SELECT id FROM suppliers WHERE id = ?");
        $stmt->execute([$supplier_id]);
        if ($stmt->fetch()) {
            // Supplier exists in suppliers table, use it
            $final_supplier_id = $supplier_id;
        }
    }

    // Use existing table structure
    $stmt = $pdo->prepare("REPLACE INTO reorder_rules (item_id, reorder_point, reorder_quantity, supplier_id, active) VALUES (?,?,?,?,1)");
    $stmt->execute([$item_id, $min, $qty, $final_supplier_id]);

    echo json_encode(['success'=>true]);
} catch (Throwable $e) {
    echo json_encode(['success'=>false,'message'=>'Server error: '.$e->getMessage()]);
}
