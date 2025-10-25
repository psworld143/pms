<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Update Guest API
 * Hotel PMS - Guest Management Module
 */

session_start();
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
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    // Debug logging
    error_log("Update guest - Raw input: " . $rawInput);
    error_log("Update guest - Decoded input: " . print_r($input, true));
    
    if (!$input) {
        error_log("Update guest - JSON decode failed: " . json_last_error_msg());
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input', 'debug' => ['raw_input' => $rawInput, 'json_error' => json_last_error_msg()]]);
        exit();
    }
    
    $guestId = $input['guest_id'] ?? $input['id'] ?? null;
    
    if (!$guestId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Guest ID is required']);
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
    
    // Check if email already exists for another guest (only if email is provided)
    if (!empty($input['email'])) {
        $emailCheckSql = "SELECT id FROM guests WHERE email = ? AND id != ?";
        $emailCheckStmt = $pdo->prepare($emailCheckSql);
        $emailCheckStmt->execute([$input['email'], $guestId]);
        
        if ($emailCheckStmt->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email already exists for another guest']);
            exit();
        }
    }
    
    // Update guest
    $updateSql = "
        UPDATE guests SET 
            first_name = ?,
            last_name = ?,
            email = ?,
            phone = ?,
            id_type = ?,
            id_number = ?,
            is_vip = ?,
            address = ?,
            date_of_birth = ?,
            nationality = ?,
            preferences = ?,
            service_notes = ?,
            updated_at = NOW()
        WHERE id = ?
    ";
    
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute([
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
        $input['service_notes'] ?? null,
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
