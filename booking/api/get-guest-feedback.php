<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/vps_session_fix.php';
require_once '../config/database.php';
require_once '../includes/booking-paths.php';
require_once '../includes/guest-feedback-helpers.php';

booking_initialize_paths();

$allowedRoles = ['manager', 'front_desk'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? null, $allowedRoles, true)) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access',
        'redirect' => booking_base() . 'login.php',
    ]);
    exit();
}

header('Content-Type: application/json');

try {
    global $pdo;
    if (!$pdo instanceof PDO) {
        throw new RuntimeException('Database connection unavailable');
    }

    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = (int)($_GET['limit'] ?? 20);
    if ($limit < 1) {
        $limit = 20;
    } elseif ($limit > 100) {
        $limit = 100;
    }

    $sort = (string)($_GET['sort'] ?? 'newest');
    $allowedSorts = ['newest', 'oldest', 'rating_high', 'rating_low'];
    if (!in_array($sort, $allowedSorts, true)) {
        $sort = 'newest';
    }

    $filters = [
        'rating' => $_GET['rating'] ?? null,
        'status' => $_GET['status'] ?? '',
        'category' => $_GET['category'] ?? '',
        'feedback_type' => $_GET['feedback_type'] ?? '',
        'search' => $_GET['search'] ?? '',
        'date_from' => $_GET['date_from'] ?? '',
        'date_to' => $_GET['date_to'] ?? '',
    ];

    $offset = ($page - 1) * $limit;

    $items = getGuestFeedbackList($pdo, $filters, $limit, $offset, $sort);
    $total = countGuestFeedback($pdo, $filters);
    $totalPages = $limit > 0 ? (int)ceil($total / $limit) : 1;

    echo json_encode([
        'success' => true,
        'data' => $items,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => $totalPages,
        ],
        'filters' => normalizeGuestFeedbackFilters($filters),
        'sort' => $sort,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    error_log('Guest feedback API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to load guest feedback.',
    ]);
}
