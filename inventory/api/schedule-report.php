<?php
/**
 * Schedule Report
 * Hotel PMS Training System - Inventory Module
 */

session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

try {
    $report_type = $_POST['report_type'] ?? '';
    $frequency = $_POST['frequency'] ?? '';
    
    if (empty($report_type) || empty($frequency)) {
        echo json_encode(['success' => false, 'message' => 'Report type and frequency are required']);
        exit();
    }
    
    $result = scheduleReport($report_type, $frequency);
    
    echo json_encode([
        'success' => true,
        'message' => 'Report scheduled successfully',
        'schedule_id' => $result['schedule_id']
    ]);
    
} catch (Exception $e) {
    error_log("Error scheduling report: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Schedule a report
 */
function scheduleReport($report_type, $frequency) {
    global $pdo;
    
    try {
        // Create scheduled report entry
        $stmt = $pdo->prepare("
            INSERT INTO scheduled_reports 
            (report_type, frequency, status, created_by, created_at) 
            VALUES (?, ?, 'active', ?, NOW())
        ");
        $stmt->execute([$report_type, $frequency, $_SESSION['user_id']]);
        
        $schedule_id = $pdo->lastInsertId();
        
        // In a real system, you would also:
        // 1. Set up a cron job or scheduled task
        // 2. Create email notifications
        // 3. Store user preferences for delivery
        
        return [
            'schedule_id' => $schedule_id
        ];
        
    } catch (PDOException $e) {
        error_log("Error scheduling report: " . $e->getMessage());
        throw new Exception("Database error while scheduling report");
    }
}
?>
