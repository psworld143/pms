<?php
// Error handling
ini_set('display_errors', 0);
ini_set('log_errors', 1);


/**
 * Promote Guest to VIP API
 */

session_start();
require_once "../config/database.php";
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in and has access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['manager', 'front_desk'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (empty($input['guest_id']) || empty($input['loyalty_tier'])) {
        throw new Exception('Guest ID and VIP tier are required');
    }
    
    $guest_id = (int)$input['guest_id'];
    $loyalty_tier = $input['loyalty_tier'];
    
    // Validate loyalty tier
    $valid_tiers = ['silver', 'gold', 'platinum'];
    if (!in_array($loyalty_tier, $valid_tiers)) {
        throw new Exception('Invalid VIP tier');
    }
    
    // Check if guest exists and is not already VIP
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, is_vip FROM guests WHERE id = ?");
    $stmt->execute([$guest_id]);
    $guest = $stmt->fetch();
    
    if (!$guest) {
        throw new Exception('Guest not found');
    }
    
    if ($guest['is_vip'] == 1) {
        throw new Exception('Guest is already a VIP member');
    }
    
    // Promote guest to VIP
    $stmt = $pdo->prepare("
        UPDATE guests 
        SET is_vip = 1, 
            loyalty_tier = ?, 
            updated_at = NOW()
        WHERE id = ?
    ");
    
    $stmt->execute([$loyalty_tier, $guest_id]);
    
    // Log the activity
    logActivity($_SESSION['user_id'], 'guest_promoted_to_vip', 
        "Promoted guest {$guest['first_name']} {$guest['last_name']} to {$loyalty_tier} VIP");
    
    echo json_encode([
        'success' => true,
        'message' => "Guest {$guest['first_name']} {$guest['last_name']} promoted to {$loyalty_tier} VIP successfully",
        'guest_id' => $guest_id
    ]);
    
} catch (Exception $e) {
    error_log("Error promoting guest to VIP: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
