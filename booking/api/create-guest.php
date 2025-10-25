<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Create Guest API
 * Hotel PMS - Guest Management Module
 */

session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in; allow API key fallback
if (!isset($_SESSION['user_id'])) {
    $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_SERVER['HTTP_API_KEY'] ?? null;
    if ($apiKey && $apiKey === 'pms_users_api_2024') {
        $_SESSION['user_id'] = 1073; // API user
        $_SESSION['user_role'] = 'manager';
        $_SESSION['name'] = 'API User';
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        exit();
    }
    
    // Validate required fields
    $requiredFields = ['first_name', 'last_name', 'phone', 'id_type', 'id_number'];
    foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '{$field}' is required"]);
            exit();
        }
    }
    
    // Check if email already exists (only if email is provided)
    if (!empty($input['email'])) {
        $emailCheckSql = "SELECT id FROM guests WHERE email = ?";
        $emailCheckStmt = $pdo->prepare($emailCheckSql);
        $emailCheckStmt->execute([$input['email']]);
        
        if ($emailCheckStmt->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            exit();
        }
    }
    
    // Insert guest
    $sql = "
        INSERT INTO guests (
            first_name, last_name, email, phone, id_type, id_number, is_vip,
            address, date_of_birth, nationality, preferences, service_notes, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $input['first_name'],
        $input['last_name'],
        $input['email'] ?? null,
        $input['phone'],
        $input['id_type'],
        $input['id_number'],
        isset($input['is_vip']) ? (int)$input['is_vip'] : 0,
        $input['address'] ?? null,
        $input['date_of_birth'] ?? null,
        $input['nationality'] ?? null,
        $input['preferences'] ?? null,
        $input['service_notes'] ?? null
    ]);
    
    $guestId = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Guest created successfully',
        'guest_id' => $guestId
    ]);
    
} catch (PDOException $e) {
    error_log("Error creating guest: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>
