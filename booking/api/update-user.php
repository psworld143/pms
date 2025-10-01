<?php
session_start();
require_once "../config/database.php";
require_once '../includes/functions.php';

// Check if user is logged in and has manager access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid input data');
    }
    
    // Validate required fields
    if (empty($input['user_id'])) {
        throw new Exception('User ID is required');
    }
    
    $user_id = intval($input['user_id']);
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    // Validate role if provided
    if (isset($input['role'])) {
        $valid_roles = ['front_desk', 'housekeeping', 'manager'];
        if (!in_array($input['role'], $valid_roles)) {
            throw new Exception('Invalid user role');
        }
    }
    
    // Validate email format if provided
    if (isset($input['email']) && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }
    
    // Check if username already exists (excluding current user)
    if (isset($input['username'])) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$input['username'], $user_id]);
        if ($stmt->fetch()) {
            throw new Exception('Username already exists');
        }
    }
    
    // Check if email already exists (excluding current user)
    if (isset($input['email'])) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$input['email'], $user_id]);
        if ($stmt->fetch()) {
            throw new Exception('Email already exists');
        }
    }
    
    // Build update query dynamically
    $update_fields = [];
    $params = [];
    
    if (isset($input['name'])) {
        $update_fields[] = "name = ?";
        $params[] = $input['name'];
    }
    
    if (isset($input['username'])) {
        $update_fields[] = "username = ?";
        $params[] = $input['username'];
    }
    
    if (isset($input['email'])) {
        $update_fields[] = "email = ?";
        $params[] = $input['email'];
    }
    
    if (isset($input['role'])) {
        $update_fields[] = "role = ?";
        $params[] = $input['role'];
    }
    
    if (isset($input['is_active'])) {
        $update_fields[] = "is_active = ?";
        $params[] = $input['is_active'] ? 1 : 0;
    }
    
    if (isset($input['password']) && !empty($input['password'])) {
        $update_fields[] = "password = ?";
        $params[] = password_hash($input['password'], PASSWORD_DEFAULT);
    }
    
    if (empty($update_fields)) {
        throw new Exception('No fields to update');
    }
    
    $update_fields[] = "updated_at = NOW()";
    $params[] = $user_id;
    
    $sql = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    // Log activity
    logActivity($_SESSION['user_id'], 'user_updated', "Updated user: {$user['username']}");
    
    echo json_encode([
        'success' => true,
        'message' => 'User updated successfully'
    ]);
    
} catch (Exception $e) {
    error_log("Error updating user: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
