<?php
session_start();
// Error handling
ini_set('display_errors', 0);
ini_set('log_errors', 1);

/**
 * Get Discounts API
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in and has access (manager or front_desk); allow API key fallback
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['manager', 'front_desk'])) {
    $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_SERVER['HTTP_API_KEY'] ?? null;
    if ($apiKey && $apiKey === 'pms_users_api_2024') {
        $_SESSION['user_id'] = 1073;
        $_SESSION['user_role'] = 'manager';
        $_SESSION['name'] = 'API User';
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized access'
        ]);
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

try {
    // Get active discounts only
    $stmt = $pdo->query("
        SELECT 
            id,
            discount_name,
            discount_type,
            discount_value,
            description,
            valid_from,
            valid_until,
            is_active
        FROM discount_templates 
        WHERE is_active = 1 
        AND valid_from <= CURDATE() 
        AND valid_until >= CURDATE()
        ORDER BY discount_name ASC
    ");
    $discounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'discounts' => $discounts
    ]);
    
} catch (PDOException $e) {
    error_log('Error getting discounts: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log('Error getting discounts: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred'
    ]);
}
?>