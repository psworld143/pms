<?php
// Error handling
ini_set('display_errors', 0);
ini_set('log_errors', 1);


/**
 * Add Loyalty Member API
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
        throw new Exception('Guest ID and loyalty tier are required');
    }
    
    $guest_id = (int)$input['guest_id'];
    $loyalty_tier = $input['loyalty_tier'];
    
    // Validate loyalty tier
    $valid_tiers = ['bronze', 'silver', 'gold', 'platinum'];
    if (!in_array($loyalty_tier, $valid_tiers)) {
        throw new Exception('Invalid loyalty tier');
    }
    
    // Check if guest exists and is not already in loyalty program
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, loyalty_tier FROM guests WHERE id = ?");
    $stmt->execute([$guest_id]);
    $guest = $stmt->fetch();
    
    if (!$guest) {
        throw new Exception('Guest not found');
    }
    
    if (!empty($guest['loyalty_tier'])) {
        throw new Exception('Guest is already a loyalty member');
    }
    
    // Add guest to loyalty program
    $result = addLoyaltyMember($guest_id, $loyalty_tier);
    
    if ($result['success']) {
        // Log the activity
        logActivity($_SESSION['user_id'], 'loyalty_member_added', 
            "Added guest {$guest['first_name']} {$guest['last_name']} to {$loyalty_tier} loyalty tier");
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Error adding loyalty member: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
