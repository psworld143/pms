<?php
/**
 * Lookup Item by Barcode
 * Hotel PMS Training System - Inventory Module
 */

@error_reporting(E_ERROR | E_PARSE);
@ini_set('display_errors', 0);

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
    $barcode = $_GET['barcode'] ?? '';
    
    if (empty($barcode)) {
        echo json_encode(['success' => false, 'message' => 'Barcode required']);
        exit();
    }
    
    global $pdo;
    
    // Detect available columns
    $name_col = 'item_name';
    $sku_col = 'sku';
    $stock_col = 'current_stock';
    $price_col = 'unit_price';
    
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items LIKE 'name'");
        if ($stmt->fetch()) $name_col = 'name';
    } catch (PDOException $e) {}
    
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items LIKE 'barcode'");
        if ($stmt->fetch()) $sku_col = 'barcode';
    } catch (PDOException $e) {}
    
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items LIKE 'quantity'");
        if ($stmt->fetch()) $stock_col = 'quantity';
    } catch (PDOException $e) {}
    
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items LIKE 'price'");
        if ($stmt->fetch()) $price_col = 'price';
    } catch (PDOException $e) {}
    
    // Try to find item by barcode/SKU
    $stmt = $pdo->prepare("
        SELECT 
            id, 
            $name_col as name, 
            $sku_col as sku,
            $stock_col as current_stock,
            $price_col as unit_price
        FROM inventory_items 
        WHERE $sku_col = ? 
        LIMIT 1
    ");
    $stmt->execute([$barcode]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($item) {
        echo json_encode([
            'success' => true,
            'item' => [
                'id' => $item['id'],
                'name' => $item['name'],
                'barcode' => $item['sku'],
                'current_stock' => (int)$item['current_stock'],
                'unit_price' => (float)$item['unit_price'],
                'status' => 'found'
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Item not found'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error looking up item by barcode: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>