<?php
/**
 * Redeem Loyalty Points API
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
    if (empty($input['guest_id']) || empty($input['reward_id']) || empty($input['points_used'])) {
        throw new Exception('Guest ID, reward ID, and points used are required');
    }
    
    $guest_id = (int)$input['guest_id'];
    $reward_id = (int)$input['reward_id'];
    $points_used = (int)$input['points_used'];
    
    // Check if guest exists and is a loyalty member
    $stmt = $pdo->prepare("SELECT id, first_name, last_name FROM guests WHERE id = ? AND loyalty_tier IS NOT NULL AND loyalty_tier != ''");
    $stmt->execute([$guest_id]);
    $guest = $stmt->fetch();
    
    if (!$guest) {
        throw new Exception('Guest not found or not a loyalty member');
    }
    
    // Check if reward exists
    $stmt = $pdo->prepare("SELECT id, name, points_required FROM loyalty_rewards WHERE id = ? AND is_active = 1");
    $stmt->execute([$reward_id]);
    $reward = $stmt->fetch();
    
    if (!$reward) {
        throw new Exception('Reward not found or inactive');
    }
    
    // Verify points match reward requirement
    if ($points_used != $reward['points_required']) {
        throw new Exception('Points used does not match reward requirement');
    }
    
    // Redeem points
    $result = redeemLoyaltyPoints($guest_id, $reward_id, $points_used);
    
    if ($result['success']) {
        // Log the activity
        logActivity($_SESSION['user_id'], 'loyalty_points_redeemed', 
            "Redeemed {$points_used} points for {$reward['name']} for guest {$guest['first_name']} {$guest['last_name']}");
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Error redeeming loyalty points: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
