<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
require_once '../config/database.php';
require_once '../includes/booking-paths.php';
require_once '../includes/audit-helpers.php';

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

    $conditions = [];
    $bindings = [];

    $action = isset($_GET['action']) ? trim((string)$_GET['action']) : '';
    if ($action !== '') {
        $conditions[] = 'al.action = :action';
        $bindings[':action'] = $action;
    }

    $search = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
    if ($search !== '') {
        $conditions[] = '(al.details LIKE :search OR al.action LIKE :search OR u.name LIKE :search OR u.username LIKE :search)';
        $bindings[':search'] = '%' . $search . '%';
    }

    $dateFrom = isset($_GET['date_from']) ? trim((string)$_GET['date_from']) : '';
    if ($dateFrom !== '' && ($parsed = DateTime::createFromFormat('Y-m-d', $dateFrom))) {
        $conditions[] = 'al.created_at >= :date_from';
        $bindings[':date_from'] = $parsed->format('Y-m-d 00:00:00');
    }

    $dateTo = isset($_GET['date_to']) ? trim((string)$_GET['date_to']) : '';
    if ($dateTo !== '' && ($parsed = DateTime::createFromFormat('Y-m-d', $dateTo))) {
        $conditions[] = 'al.created_at <= :date_to';
        $bindings[':date_to'] = $parsed->format('Y-m-d 23:59:59');
    }

    $baseQuery = 'FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id';
    if ($conditions) {
        $baseQuery .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $countStmt = $pdo->prepare('SELECT COUNT(*) ' . $baseQuery);
    foreach ($bindings as $placeholder => $value) {
        $countStmt->bindValue($placeholder, $value);
    }
    $countStmt->execute();
    $total = (int)$countStmt->fetchColumn();

    $dataStmt = $pdo->prepare('SELECT al.id, al.user_id, al.action, al.details, al.ip_address, al.user_agent, al.created_at, u.name AS user_name, u.username AS user_username, u.role AS user_role ' . $baseQuery . ' ORDER BY al.created_at DESC LIMIT :limit OFFSET :offset');
    foreach ($bindings as $placeholder => $value) {
        $dataStmt->bindValue($placeholder, $value);
    }
    $dataStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $dataStmt->execute();

    $logs = [];
    while ($row = $dataStmt->fetch(PDO::FETCH_ASSOC)) {
        $logs[] = [
            'id' => (int)$row['id'],
            'user_id' => $row['user_id'] !== null ? (int)$row['user_id'] : null,
            'user_name' => $row['user_name'] ?: ($row['user_username'] ?: 'System'),
            'user_role' => $row['user_role'] ?? null,
            'action' => $row['action'],
            'details' => $row['details'],
            'ip_address' => $row['ip_address'],
            'user_agent' => $row['user_agent'],
            'created_at' => $row['created_at']
        ];
    }

    $totalPages = $limit > 0 ? (int)ceil($total / $limit) : 1;

    echo json_encode([
        'success' => true,
        'logs' => $logs,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => $totalPages,
        ],
        'summary' => getAuditLogSummary(),
        'actions' => getAuditLogActions()
    ]);
} catch (Throwable $e) {
    error_log('Audit log fetch error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to load audit logs.'
    ]);
}
