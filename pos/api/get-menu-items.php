<?php
// VPS Session Fix - Robust session configuration
require_once __DIR__ . '/../../vps_session_fix.php';

require_once '../config/database.php';
require_once '../includes/pos-functions.php';

// Check if user is logged in to POS
if (!isset($_SESSION['pos_user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

try {
    // Get filter parameters
    $category = $_GET['category'] ?? '';
    $active_only = $_GET['active_only'] ?? 'true';
    
    $menu_items = getMenuItemsWithFilters($category, $active_only);
    
    echo json_encode([
        'success' => true,
        'menu_items' => $menu_items
    ]);
    
} catch (Exception $e) {
    error_log("Error getting menu items: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get menu items with filters
 */
function getMenuItemsWithFilters($category = '', $active_only = 'true') {
    global $pdo;
    
    try {
        $where_conditions = ["1=1"];
        $params = [];
        
        // Category filter
        if (!empty($category)) {
            $where_conditions[] = "category = ?";
            $params[] = $category;
        }
        
        // Active filter
        if ($active_only === 'true') {
            $where_conditions[] = "active = 1";
        }
        
        $where_clause = implode(" AND ", $where_conditions);
        
        $stmt = $pdo->prepare("
            SELECT id, name, description, category, price, cost, image, active, sort_order, created_at, updated_at
            FROM pos_menu_items
            WHERE {$where_clause}
            ORDER BY category, sort_order, name ASC
        ");
        
        $stmt->execute($params);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error getting menu items with filters: " . $e->getMessage());
        return [];
    }
}
?>
