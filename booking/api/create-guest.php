<?php
/**
 * Create Guest API
 * Hotel PMS - Guest Management Module
 */

require_once dirname(__DIR__, 2) . '/vps_session_fix.php';
require_once dirname(__DIR__, 2) . '/includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
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
    $requiredFields = ['first_name', 'last_name', 'email', 'phone'];
    foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '{$field}' is required"]);
            exit();
        }
    }
    
    // Check if email already exists
    $emailCheckSql = "SELECT id FROM guests WHERE email = ?";
    $emailCheckStmt = $pdo->prepare($emailCheckSql);
    $emailCheckStmt->execute([$input['email']]);
    
    if ($emailCheckStmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit();
    }
    
    // Insert guest
    $sql = "
        INSERT INTO guests (
            first_name, last_name, email, phone, is_vip, id_number,
            address, city, state, country, postal_code, date_of_birth,
            nationality, preferences, notes, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $input['first_name'],
        $input['last_name'],
        $input['email'],
        $input['phone'],
        isset($input['is_vip']) ? (int)$input['is_vip'] : 0,
        $input['id_number'] ?? null,
        $input['address'] ?? null,
        $input['city'] ?? null,
        $input['state'] ?? null,
        $input['country'] ?? null,
        $input['postal_code'] ?? null,
        $input['date_of_birth'] ?? null,
        $input['nationality'] ?? null,
        $input['preferences'] ?? null,
        $input['notes'] ?? null
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
