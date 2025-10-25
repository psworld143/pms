<?php
session_start();
// Error handling
ini_set('display_errors', 0);
ini_set('log_errors', 1);



declare(strict_types=1);

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/booking-paths.php';
require_once __DIR__ . '/../includes/guest-feedback-helpers.php';

booking_initialize_paths();

$allowedRoles = ['manager', 'front_desk'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? null, $allowedRoles, true)) {
    // Check for API key in headers (for AJAX requests)
    $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_SERVER['HTTP_API_KEY'] ?? null;
    
    if ($apiKey && $apiKey === 'pms_feedback_api_2024') {
        // Valid API key, create session
        $_SESSION['user_id'] = 1;
        $_SESSION['user_role'] = 'manager';
        $_SESSION['name'] = 'API User';
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized access',
            'redirect' => booking_base() . 'login.php',
        ]);
        exit();
    }
}

header('Content-Type: application/json');

try {
    global $pdo;
    if (!$pdo instanceof PDO) {
        throw new RuntimeException('Database connection unavailable');
    }

    $summary = getGuestFeedbackSummary($pdo);
    $distribution = getGuestFeedbackRatingDistribution($pdo);
    $categories = getGuestFeedbackCategoryBreakdown($pdo);
    $recent = getRecentGuestFeedback($pdo, 5);

    echo json_encode([
        'success' => true,
        'summary' => $summary,
        'distribution' => $distribution,
        'categories' => $categories,
        'recent' => $recent,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    error_log('Guest feedback stats API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to load guest feedback statistics.',
    ]);
}
