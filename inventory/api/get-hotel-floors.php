<?php
/**
 * Get Hotel Floors
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
    $floors = getHotelFloors();
    
    echo json_encode([
        'success' => true,
        'floors' => $floors
    ]);
    
} catch (Exception $e) {
    error_log("Error getting hotel floors: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get hotel floors
 */
function getHotelFloors() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("
            SELECT 
                id,
                floor_number,
                floor_name,
                description,
                active,
                created_at,
                updated_at
            FROM hotel_floors
            WHERE active = 1
            ORDER BY floor_number ASC
        ");
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error getting hotel floors: " . $e->getMessage());
        return [];
    }
}
?>
