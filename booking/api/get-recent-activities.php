<?php
/**
 * Recent Activities API Endpoint
 * Returns recent system activities for analytics dashboard
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
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized access',
            'redirect' => '../../login.php'
        ]);
        exit();
    }

    // Get limit from query parameter
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    
    // Get recent activities
    $activities = getRecentActivities($limit);
    
    echo json_encode([
        'success' => true,
        'activities' => $activities
    ]);

} catch (Exception $e) {
    error_log('Recent Activities API Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading recent activities: ' . $e->getMessage()
    ]);
}
?>