<?php
session_start();
// Error handling
ini_set('display_errors', 0);
ini_set('log_errors', 1);

/**
 * Create Voucher API
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON input'
        ]);
        exit();
    }

    // Validate required fields
    $required_fields = ['voucher_name', 'voucher_type', 'voucher_value', 'valid_from', 'valid_until'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            echo json_encode([
                'success' => false,
                'message' => 'Missing required field: ' . $field
            ]);
            exit();
        }
    }

    $voucher_name = trim($input['voucher_name']);
    $voucher_type = $input['voucher_type'];
    $voucher_value = (float)$input['voucher_value'];
    $usage_limit = isset($input['usage_limit']) ? (int)$input['usage_limit'] : 1;
    $valid_from = $input['valid_from'];
    $valid_until = $input['valid_until'];
    $description = $input['description'] ?? '';
    $generate_codes = isset($input['generate_codes']) ? (bool)$input['generate_codes'] : true;
    $created_by = $_SESSION['user_id'];

    // Validate voucher value
    if ($voucher_value <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Voucher value must be greater than 0'
        ]);
        exit();
    }

    // Validate percentage voucher
    if ($voucher_type === 'percentage' && $voucher_value > 100) {
        echo json_encode([
            'success' => false,
            'message' => 'Percentage voucher cannot exceed 100%'
        ]);
        exit();
    }

    // Validate dates
    if (strtotime($valid_from) >= strtotime($valid_until)) {
        echo json_encode([
            'success' => false,
            'message' => 'Valid Until date must be after Valid From date'
        ]);
        exit();
    }

    // Generate voucher code
    $voucher_code = '';
    if ($generate_codes) {
        // Generate unique voucher code
        $prefix = strtoupper(substr($voucher_name, 0, 3));
        $year = date('Y');
        $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $voucher_code = $prefix . $year . $random;
        
        // Ensure uniqueness
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM vouchers WHERE voucher_code = ?");
        $stmt->execute([$voucher_code]);
        $count = $stmt->fetchColumn();
        
        while ($count > 0) {
            $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $voucher_code = $prefix . $year . $random;
            $stmt->execute([$voucher_code]);
            $count = $stmt->fetchColumn();
        }
    } else {
        $voucher_code = strtoupper(str_replace(' ', '', $voucher_name));
    }

    // Insert voucher
    $stmt = $pdo->prepare("
        INSERT INTO vouchers (
            voucher_code, voucher_type, voucher_value, usage_limit,
            valid_from, valid_until, description, status, created_by, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'active', ?, NOW())
    ");

    $result = $stmt->execute([
        $voucher_code,
        $voucher_type,
        $voucher_value,
        $usage_limit,
        $valid_from,
        $valid_until,
        $description,
        $created_by
    ]);

    if ($result) {
        $voucher_id = $pdo->lastInsertId();
        
        // Log activity
        $activity_description = "Created voucher: {$voucher_code} ({$voucher_type})";
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, created_at) VALUES (?, 'create_voucher', ?, NOW())");
        $stmt->execute([$created_by, $activity_description]);

        echo json_encode([
            'success' => true,
            'message' => 'Voucher created successfully',
            'voucher_id' => $voucher_id,
            'voucher_code' => $voucher_code
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create voucher'
        ]);
    }

} catch (PDOException $e) {
    error_log('Error creating voucher: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log('Error creating voucher: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred'
    ]);
}
?>