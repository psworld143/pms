<?php
session_start();
// Error handling
ini_set('display_errors', 0);
ini_set('log_errors', 1);

/**
 * Validate Voucher API
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

    $voucher_code = trim($input['voucher_code'] ?? '');
    
    if (empty($voucher_code)) {
        echo json_encode([
            'success' => false,
            'message' => 'Voucher code is required'
        ]);
        exit();
    }

    // Get voucher details
    $stmt = $pdo->prepare("
        SELECT v.*, 
               COALESCE(vu.used_count, 0) as used_count
        FROM vouchers v
        LEFT JOIN (
            SELECT voucher_id, COUNT(*) as used_count 
            FROM voucher_usage 
            GROUP BY voucher_id
        ) vu ON v.id = vu.voucher_id
        WHERE v.voucher_code = ?
    ");
    $stmt->execute([$voucher_code]);
    $voucher = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$voucher) {
        echo json_encode([
            'success' => false,
            'message' => 'Voucher code not found'
        ]);
        exit();
    }

    // Check if voucher is expired
    $today = date('Y-m-d');
    if ($voucher['valid_until'] < $today) {
        // Update status to expired
        $stmt = $pdo->prepare("UPDATE vouchers SET status = 'expired' WHERE id = ?");
        $stmt->execute([$voucher['id']]);
        
        echo json_encode([
            'success' => false,
            'message' => 'Voucher has expired'
        ]);
        exit();
    }

    // Check if voucher is already used up
    if ($voucher['used_count'] >= $voucher['usage_limit']) {
        // Update status to used
        $stmt = $pdo->prepare("UPDATE vouchers SET status = 'used' WHERE id = ?");
        $stmt->execute([$voucher['id']]);
        
        echo json_encode([
            'success' => false,
            'message' => 'Voucher has been fully used'
        ]);
        exit();
    }

    // Check if voucher is not yet valid
    if ($voucher['valid_from'] > $today) {
        echo json_encode([
            'success' => false,
            'message' => 'Voucher is not yet valid'
        ]);
        exit();
    }

    // Check if voucher is active
    if ($voucher['status'] !== 'active') {
        echo json_encode([
            'success' => false,
            'message' => 'Voucher is not active'
        ]);
        exit();
    }

    // Voucher is valid
    echo json_encode([
        'success' => true,
        'message' => 'Voucher is valid',
        'voucher' => $voucher
    ]);

} catch (PDOException $e) {
    error_log('Error validating voucher: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log('Error validating voucher: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred'
    ]);
}
?>
