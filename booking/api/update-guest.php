<?php
/**
 * Update Guest API
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
    
    $guestId = $input['id'] ?? null;
    
    if (!$guestId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Guest ID is required']);
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
    
    // Check if email already exists for another guest
    $emailCheckSql = "SELECT id FROM guests WHERE email = ? AND id != ?";
    $emailCheckStmt = $pdo->prepare($emailCheckSql);
    $emailCheckStmt->execute([$input['email'], $guestId]);
    
    if ($emailCheckStmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email already exists for another guest']);
        exit();
    }
    
    // Update guest
    $updateSql = "
        UPDATE guests SET 
            first_name = ?,
            last_name = ?,
            email = ?,
            phone = ?,
            is_vip = ?,
            id_number = ?,
            address = ?,
            city = ?,
            state = ?,
            country = ?,
            postal_code = ?,
            date_of_birth = ?,
            nationality = ?,
            preferences = ?,
            notes = ?,
            updated_at = NOW()
        WHERE id = ?
    ";
    
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute([
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
        $input['notes'] ?? null,
        $guestId
    ]);
    
    if ($updateStmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Guest not found or no changes made']);
        exit();
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Guest updated successfully'
    ]);
    
} catch (PDOException $e) {
    error_log("Error updating guest: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>
