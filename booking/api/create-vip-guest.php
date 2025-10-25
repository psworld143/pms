<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Create VIP Guest API
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
    if (empty($input['first_name']) || empty($input['last_name']) || empty($input['email'])) {
        throw new Exception('First name, last name, and email are required');
    }
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM guests WHERE email = ?");
    $stmt->execute([$input['email']]);
    if ($stmt->fetch()) {
        throw new Exception('Email already exists');
    }
    
    // Create VIP guest
    $stmt = $pdo->prepare("
        INSERT INTO guests (
            first_name, 
            last_name, 
            email, 
            phone, 
            loyalty_tier, 
            is_vip, 
            created_at
        ) VALUES (?, ?, ?, ?, ?, 1, NOW())
    ");
    
    $stmt->execute([
        $input['first_name'],
        $input['last_name'],
        $input['email'],
        $input['phone'] ?? null,
        $input['loyalty_tier'] ?? null
    ]);
    
    $guest_id = $pdo->lastInsertId();
    
    // Log the activity
    logActivity($_SESSION['user_id'], 'vip_guest_created', "Created VIP guest: {$input['first_name']} {$input['last_name']}");
    
    echo json_encode([
        'success' => true,
        'message' => 'VIP Guest created successfully',
        'guest_id' => $guest_id
    ]);
    
} catch (Exception $e) {
    error_log("Error creating VIP guest: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>