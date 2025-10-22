<?php
/**
 * Update Loyalty Tier API
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $guest_id = filter_var($input['guest_id'] ?? null, FILTER_VALIDATE_INT);
    $tier = filter_var($input['tier'] ?? null, FILTER_SANITIZE_STRING);
    
    if (!$guest_id || !$tier) {
        echo json_encode(['success' => false, 'message' => 'Invalid guest ID or tier']);
        exit();
    }
    
    if (!in_array($tier, ['silver', 'gold', 'platinum'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid tier. Must be silver, gold, or platinum']);
        exit();
    }
    
    try {
        $pdo->beginTransaction();
        
        // Update guest loyalty tier
        $stmt = $pdo->prepare("UPDATE guests SET loyalty_tier = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$tier, $guest_id]);
        
        // Log the tier change
        logActivity($_SESSION['user_id'] ?? 0, 'loyalty_tier_change', "Updated loyalty tier for guest {$guest_id} to {$tier}");
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Loyalty tier updated successfully'
        ]);
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error updating loyalty tier: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
}
?>
