<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in and has manager access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['manager', 'front_desk'])) {
    // Check for API key in headers (for AJAX requests)
    $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_SERVER['HTTP_API_KEY'] ?? null;
    
    if ($apiKey && $apiKey === 'pms_users_api_2024') {
        // Valid API key, create session
        $_SESSION['user_id'] = 1073; // Default manager user
        $_SESSION['user_role'] = 'manager';
        $_SESSION['name'] = 'API User';
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit();
    }
}

header('Content-Type: application/json');

try {
    // Get filter parameters
    $role = $_GET['role'] ?? '';
    $status = $_GET['status'] ?? '';
    $search = $_GET['search'] ?? '';
    
    // Implement getUsers function directly to avoid vps_session_fix.php issues
    $where_conditions = ["1=1"];
    $params = [];
    
    // Role filter
    if (!empty($role)) {
        $where_conditions[] = "role = ?";
        $params[] = $role;
    }
    
    // Status filter
    if (!empty($status)) {
        if ($status === 'active') {
            $where_conditions[] = "is_active = 1";
        } elseif ($status === 'inactive') {
            $where_conditions[] = "is_active = 0";
        }
    }
    
    // Search filter
    if (!empty($search)) {
        $where_conditions[] = "(name LIKE ? OR username LIKE ? OR email LIKE ?)";
        $search_param = "%{$search}%";
        $params = array_merge($params, [$search_param, $search_param, $search_param]);
    }
    
    $where_clause = implode(" AND ", $where_conditions);
    
    $stmt = $pdo->prepare("
        SELECT id, name, username, email, role, is_active, created_at
        FROM users
        WHERE {$where_clause}
        ORDER BY created_at DESC
    ");
    
    $stmt->execute($params);
    $users = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'users' => $users
    ]);
    
} catch (Exception $e) {
    error_log("Error getting users: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

?>
