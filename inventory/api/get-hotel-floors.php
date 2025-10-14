<?php
/**
 * Get hotel floors
 */

require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    global $pdo;
    
    // Check if hotel_floors table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'hotel_floors'");
    $table_exists = $stmt->fetch();
    
    if ($table_exists) {
        // Use hotel_floors table
        $stmt = $pdo->query("SELECT * FROM hotel_floors WHERE active = 1 ORDER BY floor_number");
        $floors = $stmt->fetchAll();
    } else {
        // Generate floors from rooms table
        $stmt = $pdo->query("
            SELECT DISTINCT floor, 
                   CONCAT('Floor ', floor) as floor_name,
                   floor as floor_number
            FROM rooms 
            ORDER BY floor
        ");
        $floors = $stmt->fetchAll();
        
        // Add id field for consistency
        foreach ($floors as &$floor) {
            $floor['id'] = $floor['floor'];
        }
    }
    
    echo json_encode([
        'success' => true,
        'floors' => $floors
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>