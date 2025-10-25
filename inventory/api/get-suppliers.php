<?php
/**
 * Get Suppliers
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
    $suppliers = getSuppliers();
    
    echo json_encode([
        'success' => true,
        'suppliers' => $suppliers
    ]);
    
} catch (Exception $e) {
    error_log("Error getting suppliers: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get suppliers
 */
function getSuppliers() {
    global $pdo;
    
    try {
        // Check if suppliers table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'suppliers'");
        $table_exists = $stmt->rowCount() > 0;
        
        if (!$table_exists) {
            // Return demo data if table doesn't exist
            return [
                ['id' => 1, 'name' => 'ABC Supply Co.'],
                ['id' => 2, 'name' => 'XYZ Distributors'],
                ['id' => 3, 'name' => 'Hotel Essentials Ltd.'],
                ['id' => 4, 'name' => 'Quality Products Inc.'],
                ['id' => 5, 'name' => 'Bulk Supplies Corp.']
            ];
        }
        
        $stmt = $pdo->query("SELECT id, name FROM suppliers ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Error getting suppliers: " . $e->getMessage());
        // Return demo data on error
        return [
            ['id' => 1, 'name' => 'ABC Supply Co.'],
            ['id' => 2, 'name' => 'XYZ Distributors'],
            ['id' => 3, 'name' => 'Hotel Essentials Ltd.'],
            ['id' => 4, 'name' => 'Quality Products Inc.'],
            ['id' => 5, 'name' => 'Bulk Supplies Corp.']
        ];
    }
}
?>