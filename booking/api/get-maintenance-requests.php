<?php
require_once dirname(__DIR__, 2) . '/vps_session_fix.php';
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

try {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = (int)($_GET['limit'] ?? 25);
    if ($limit < 1) {
        $limit = 25;
    }
    if ($limit > 200) {
        $limit = 200;
    }
    $offset = ($page - 1) * $limit;

    $filters = [
        'status' => isset($_GET['status']) ? trim((string)$_GET['status']) : '',
        'priority' => isset($_GET['priority']) ? trim((string)$_GET['priority']) : '',
        'assigned_to' => isset($_GET['assigned_to']) ? trim((string)$_GET['assigned_to']) : '',
        'search' => isset($_GET['search']) ? trim((string)$_GET['search']) : '',
        'date_from' => isset($_GET['date_from']) ? trim((string)$_GET['date_from']) : '',
        'date_to' => isset($_GET['date_to']) ? trim((string)$_GET['date_to']) : '',
    ];

    $queryOptions = array_merge($filters, [
        'limit' => $limit,
        'offset' => $offset,
    ]);

    $requests = getMaintenanceRequests($queryOptions);
    $total = countMaintenanceRequests($filters);
    $summary = getMaintenanceSummary();
    $assignees = getMaintenanceAssignees();

    $totalPages = $limit > 0 ? (int)ceil($total / $limit) : 1;

    echo json_encode([
        'success' => true,
        'requests' => $requests,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => $totalPages,
        ],
        'summary' => $summary,
        'assignees' => $assignees
    ]);
} catch (Throwable $e) {
    error_log('Maintenance fetch error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to load maintenance requests.'
    ]);
}
