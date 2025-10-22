<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
require_once '../config/database.php';
require_once '../includes/booking-paths.php';
require_once '../includes/maintenance-helpers.php';

booking_initialize_paths();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access',
        'redirect' => booking_base() . 'login.php'
    ]);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit();
}

try {
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!is_array($payload)) {
        throw new RuntimeException('Invalid request payload.');
    }

    $requestId = isset($payload['id']) ? (int)$payload['id'] : 0;
    if ($requestId <= 0) {
        throw new RuntimeException('Maintenance request ID is required.');
    }

    $updateData = [];
    if (isset($payload['status'])) {
        $status = trim((string)$payload['status']);
        $validStatuses = ['reported', 'assigned', 'in_progress', 'completed'];
        if (!in_array($status, $validStatuses, true)) {
            throw new RuntimeException('Invalid maintenance status.');
        }
        $updateData['status'] = $status;
    }

    if (array_key_exists('assigned_to', $payload)) {
        $assigned = $payload['assigned_to'];
        if ($assigned === null || $assigned === '') {
            $updateData['assigned_to'] = null;
        } else {
            $updateData['assigned_to'] = (int)$assigned;
        }
    }

    if (isset($payload['notes'])) {
        $updateData['notes'] = trim((string)$payload['notes']);
    }

    $result = updateMaintenanceRequest($requestId, $updateData);

    echo json_encode($result);
} catch (Throwable $e) {
    error_log('Maintenance update error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update maintenance request.'
    ]);
}
