<?php
/**
 * Get Suppliers
 * Hotel PMS Training System - Inventory Module
 */

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
        $stmt = $pdo->query("
            SELECT 
                id,
                name,
                contact_person,
                email,
                phone,
                address,
                payment_terms,
                rating,
                active,
                created_at,
                updated_at
            FROM inventory_suppliers
            WHERE active = 1
            ORDER BY name ASC
        ");
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error getting suppliers: " . $e->getMessage());
        return [];
    }
}
?>
