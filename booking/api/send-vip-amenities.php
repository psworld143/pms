<?php
// Error handling
ini_set('display_errors', 0);
ini_set('log_errors', 1);


/**
 * Send VIP Amenities API
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
    if (empty($input['vip_guest']) || empty($input['amenity_type'])) {
        throw new Exception('VIP guest and amenity type are required');
    }
    
    // Verify VIP guest exists
    $stmt = $pdo->prepare("SELECT first_name, last_name FROM guests WHERE id = ? AND is_vip = 1");
    $stmt->execute([$input['vip_guest']]);
    $guest = $stmt->fetch();
    
    if (!$guest) {
        throw new Exception('VIP guest not found');
    }
    
    // Create VIP amenity request (using service_requests table or create new table)
    // For now, we'll log it as an activity and create a simple record
    $amenity_types = [
        'welcome_basket' => 'Welcome Basket',
        'champagne' => 'Champagne Service',
        'flowers' => 'Fresh Flowers',
        'chocolates' => 'Premium Chocolates',
        'spa_treatment' => 'Spa Treatment',
        'airport_transfer' => 'Airport Transfer'
    ];
    
    $amenity_name = $amenity_types[$input['amenity_type']] ?? $input['amenity_type'];
    
    // Log the VIP amenity request
    logActivity($_SESSION['user_id'], 'vip_amenity_sent', 
        "Sent {$amenity_name} to VIP guest: {$guest['first_name']} {$guest['last_name']}");
    
    // Create a simple VIP service record (you might want to create a dedicated table)
    $stmt = $pdo->prepare("
        INSERT INTO service_requests (
            guest_id,
            service_type,
            description,
            priority,
            status,
            created_at
        ) VALUES (?, 'vip_amenity', ?, 'high', 'pending', NOW())
    ");
    
    $description = "VIP Amenity: {$amenity_name}";
    if (!empty($input['amenity_notes'])) {
        $description .= " - Notes: " . $input['amenity_notes'];
    }
    
    $stmt->execute([
        $input['vip_guest'],
        $description
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => "VIP amenities sent successfully to {$guest['first_name']} {$guest['last_name']}"
    ]);
    
} catch (Exception $e) {
    error_log("Error sending VIP amenities: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>