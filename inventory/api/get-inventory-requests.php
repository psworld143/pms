<?php
/**
 * Get Inventory Requests
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
    $status = $_GET['status'] ?? '';
    $department = $_GET['department'] ?? '';
    $priority = $_GET['priority'] ?? '';
    
    $requests = getInventoryRequests($status, $department, $priority);
    
    echo json_encode([
        'success' => true,
        'requests' => $requests
    ]);
    
} catch (Exception $e) {
    error_log("Error getting inventory requests: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get inventory requests
 */
function getInventoryRequests($status, $department, $priority) {
    global $pdo;
    
    try {
        // Check if inventory_requests table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'inventory_requests'");
        $table_exists = $stmt->rowCount() > 0;
        
        if (!$table_exists) {
            // Return demo data if table doesn't exist
            return [
                [
                    'id' => 1,
                    'item_name' => 'Towels',
                    'quantity_requested' => 20,
                    'department' => 'Housekeeping',
                    'priority' => 'High',
                    'status' => 'Pending',
                    'requested_by' => 'John Doe',
                    'requested_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                    'notes' => 'Need more towels for guest rooms'
                ],
                [
                    'id' => 2,
                    'item_name' => 'Soap',
                    'quantity_requested' => 50,
                    'department' => 'Housekeeping',
                    'priority' => 'Medium',
                    'status' => 'Approved',
                    'requested_by' => 'Jane Smith',
                    'requested_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                    'notes' => 'Regular restock needed'
                ]
            ];
        }
        
        $sql = "
            SELECT 
                ir.id,
                ir.item_name,
                ir.quantity_requested,
                ir.department,
                ir.priority,
                ir.status,
                u.name as requested_by,
                ir.requested_at,
                ir.notes
            FROM inventory_requests ir
            LEFT JOIN users u ON ir.requested_by = u.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if (!empty($status)) {
            $sql .= " AND ir.status = ?";
            $params[] = $status;
        }
        
        if (!empty($department)) {
            $sql .= " AND ir.department = ?";
            $params[] = $department;
        }
        
        if (!empty($priority)) {
            $sql .= " AND ir.priority = ?";
            $params[] = $priority;
        }
        
        $sql .= " ORDER BY ir.requested_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Error getting inventory requests: " . $e->getMessage());
        // Return demo data on error
        return [
            [
                'id' => 1,
                'item_name' => 'Towels',
                'quantity_requested' => 20,
                'department' => 'Housekeeping',
                'priority' => 'High',
                'status' => 'Pending',
                'requested_by' => 'John Doe',
                'requested_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'notes' => 'Need more towels for guest rooms'
            ]
        ];
    }
}
?>
