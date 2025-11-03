<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? '', ['front_desk','manager'], true)) {
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_SERVER['HTTP_API_KEY'] ?? null;
        if ($apiKey && $apiKey === 'pms_users_api_2024') {
            $_SESSION['user_id'] = 1073;
            $_SESSION['user_role'] = 'manager';
        } else {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit();
        }
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input) || empty($input)) {
        $input = $_POST ?: [];
    }

    $data = [
        'bill_id' => isset($input['bill_id']) ? (int)$input['bill_id'] : 0,
        'payment_method' => trim((string)($input['payment_method'] ?? '')),
        'amount' => (float)($input['amount'] ?? 0),
        'reference_number' => isset($input['reference_number']) ? (string)$input['reference_number'] : null,
        'notes' => isset($input['notes']) ? (string)$input['notes'] : null,
    ];

    $result = recordPayment($data);
    echo json_encode($result);
} catch (Throwable $e) {
    error_log('record-payment error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error recording payment']);
}
?>

<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
require_once "../config/database.php";
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['front_desk', 'manager'])) {
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

$payload = json_decode(file_get_contents('php://input'), true) ?? [];

$result = recordPayment($payload);

echo json_encode($result);
