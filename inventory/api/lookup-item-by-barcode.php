<?php
/**
 * Lookup Item by Barcode
 * Hotel PMS Training System - Inventory Module
 */

// Suppress warnings and notices for clean JSON output
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

session_start();
require_once '../config/database.php';

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
        echo json_encode(['success' => false, 'message' => 'Barcode is required']);
        exit();
    }
    
    $item = lookupItemByBarcode($barcode);
    
    if ($item) {
        echo json_encode([
            'success' => true,
            'item' => $item
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
        'message' => $e->getMessage()
    ]);
}

/**
 * Lookup item by barcode
 */
function lookupItemByBarcode($barcode) {
    global $pdo;
    
    try {
        // Check if barcode column exists
        $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items LIKE 'barcode'");
        $has_barcode = $stmt->rowCount() > 0;
        
        if ($has_barcode) {
            // Search by barcode column
            $stmt = $pdo->prepare("
                SELECT 
                    id,
                    item_name as name,
                    barcode,
                    current_stock,
                    unit_price,
                    unit,
                    description
                FROM inventory_items 
                WHERE barcode = ?
            ");
            $stmt->execute([$barcode]);
        } else {
            // Search by SKU or item name
            $stmt = $pdo->prepare("
                SELECT 
                    id,
                    item_name as name,
                    sku as barcode,
                    current_stock,
                    unit_price,
                    unit,
                    description
                FROM inventory_items 
                WHERE sku = ? OR item_name LIKE ?
            ");
            $stmt->execute([$barcode, "%$barcode%"]);
        }
        
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($item) {
            // Add additional fields for barcode scanner
            $item['quantity'] = 1;
            $item['action'] = 'usage';
            $item['location'] = '';
            $item['notes'] = '';
        }
        
        return $item;
        
    } catch (PDOException $e) {
        error_log("Error looking up item by barcode: " . $e->getMessage());
        return false;
    }
}
?>