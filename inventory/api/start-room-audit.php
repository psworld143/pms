<?php
/**
 * Start room audit process
 */

require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    global $pdo;
    
    // Update last audited timestamp for all rooms
    $stmt = $pdo->prepare("
        UPDATE rooms 
        SET last_audited = NOW() 
        WHERE id IN (
            SELECT DISTINCT room_id 
            FROM room_inventory_items
        )
    ");
    $stmt->execute();
    
    // Log audit activity
    $stmt = $pdo->prepare("
        INSERT INTO inventory_transactions (item_id, transaction_type, quantity, reason, user_id, performed_by, created_at)
        VALUES (NULL, 'adjustment', 0, 'Room audit completed for all rooms', ?, ?, NOW())
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Room audit completed successfully'
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>