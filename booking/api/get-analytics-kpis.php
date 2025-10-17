<?php
/**
 * Analytics KPIs API Endpoint
 * Returns key performance indicators for the analytics dashboard
 */

require_once dirname(__DIR__, 2) . '/vps_session_fix.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json');

try {
    // TEMPORARY: Bypass authentication for testing
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['user_id'] = 1;
        $_SESSION['user_role'] = 'manager';
        $_SESSION['name'] = 'David Johnson';
    }
    
    // Check if user is logged in and has manager access
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
        error_log('Analytics KPIs API - Unauthorized access. Session ID: ' . session_id() . ', User ID: ' . ($_SESSION['user_id'] ?? 'NOT SET') . ', Role: ' . ($_SESSION['user_role'] ?? 'NOT SET'));
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized access - Session: ' . (isset($_SESSION['user_id']) ? 'user_id=' . $_SESSION['user_id'] . ', role=' . $_SESSION['user_role'] : 'no session'),
            'redirect' => '../../login.php'
        ]);
        exit();
    }

    // Get window days from query parameter
    $windowDays = isset($_GET['days']) ? (int)$_GET['days'] : 30;
    
    // Get analytics KPIs
    $kpis = getAnalyticsKpis($windowDays);
    
    // Get guest sentiment metrics
    $guestSentiment = getGuestSentimentMetrics(90);
    
    echo json_encode([
        'success' => true,
        'data' => $kpis,
        'guest_sentiment' => $guestSentiment
    ]);

} catch (Exception $e) {
    error_log('Analytics KPIs API Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading analytics KPIs: ' . $e->getMessage()
    ]);
}
?>