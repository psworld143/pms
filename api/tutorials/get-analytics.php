<?php
/**
 * Get Tutorial Analytics for Instructors
 * Hotel PMS Training System - Interactive Tutorials
 */

// Suppress warnings and notices for clean JSON output
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

session_start();
require_once '../../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

try {
    $userId = $_SESSION['user_id'];
    $moduleType = $_GET['module_type'] ?? '';
    $difficulty = $_GET['difficulty'] ?? '';
    $dateRange = $_GET['date_range'] ?? '';
    
    $analytics = getTutorialAnalytics($userId, $moduleType, $difficulty, $dateRange);
    
    echo json_encode([
        'success' => true,
        'analytics' => $analytics
    ]);
    
} catch (Exception $e) {
    error_log("Error getting tutorial analytics: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function getTutorialAnalytics($userId, $moduleType = '', $difficulty = '', $dateRange = '') {
    global $pdo;
    
    try {
        // Build where conditions
        $whereConditions = ["tm.is_active = 1"];
        $params = [];
        
        if (!empty($moduleType)) {
            $whereConditions[] = "tm.module_type = ?";
            $params[] = $moduleType;
        }
        
        if (!empty($difficulty)) {
            $whereConditions[] = "tm.difficulty_level = ?";
            $params[] = $difficulty;
        }
        
        if (!empty($dateRange)) {
            $dateConditions = [];
            switch ($dateRange) {
                case 'today':
                    $dateConditions[] = "DATE(tp.created_at) = CURDATE()";
                    break;
                case 'week':
                    $dateConditions[] = "tp.created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                    break;
                case 'month':
                    $dateConditions[] = "tp.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                    break;
            }
            if (!empty($dateConditions)) {
                $whereConditions[] = "(" . implode(" OR ", $dateConditions) . ")";
            }
        }
        
        $whereClause = implode(" AND ", $whereConditions);
        
        // Get module statistics
        $stmt = $pdo->prepare("
            SELECT 
                tm.id,
                tm.name,
                tm.module_type,
                tm.difficulty_level,
                tm.estimated_duration,
                COUNT(DISTINCT tp.user_id) as total_students,
                AVG(tp.completion_percentage) as avg_completion,
                AVG(tp.time_spent) as avg_time_spent,
                AVG(tp.score) as avg_score,
                COUNT(CASE WHEN tp.status = 'completed' THEN 1 END) as completed_count,
                COUNT(CASE WHEN tp.status = 'in_progress' THEN 1 END) as in_progress_count,
                COUNT(CASE WHEN tp.status = 'paused' THEN 1 END) as paused_count
            FROM tutorial_modules tm
            LEFT JOIN tutorial_progress tp ON tm.id = tp.tutorial_module_id
            WHERE {$whereClause}
            GROUP BY tm.id, tm.name, tm.module_type, tm.difficulty_level, tm.estimated_duration
            ORDER BY tm.module_type, tm.difficulty_level, tm.name
        ");
        $stmt->execute($params);
        $moduleStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get student progress details
        $stmt = $pdo->prepare("
            SELECT 
                u.id as student_id,
                u.username as student_name,
                u.email as student_email,
                tm.id as module_id,
                tm.name as module_name,
                tm.module_type,
                tm.difficulty_level,
                tp.completion_percentage,
                tp.time_spent,
                tp.score,
                tp.status,
                tp.started_at,
                tp.completed_at,
                tp.last_accessed
            FROM tutorial_progress tp
            JOIN users u ON tp.user_id = u.id
            JOIN tutorial_modules tm ON tp.tutorial_module_id = tm.id
            WHERE {$whereClause}
            ORDER BY tm.name, tp.completion_percentage DESC, u.username
        ");
        $stmt->execute($params);
        $studentProgress = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get overall statistics
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT tp.user_id) as total_students,
                COUNT(DISTINCT tp.tutorial_module_id) as total_modules,
                AVG(tp.completion_percentage) as overall_avg_completion,
                AVG(tp.time_spent) as overall_avg_time,
                AVG(tp.score) as overall_avg_score,
                COUNT(CASE WHEN tp.status = 'completed' THEN 1 END) as total_completed,
                COUNT(CASE WHEN tp.status = 'in_progress' THEN 1 END) as total_in_progress,
                COUNT(CASE WHEN tp.status = 'paused' THEN 1 END) as total_paused
            FROM tutorial_progress tp
            JOIN tutorial_modules tm ON tp.tutorial_module_id = tm.id
            WHERE {$whereClause}
        ");
        $stmt->execute($params);
        $overallStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get recent activity
        $stmt = $pdo->prepare("
            SELECT 
                ta.action_type,
                ta.created_at,
                u.username as student_name,
                tm.name as module_name,
                ta.time_spent,
                ta.score
            FROM tutorial_analytics ta
            JOIN users u ON ta.user_id = u.id
            JOIN tutorial_modules tm ON ta.tutorial_module_id = tm.id
            WHERE {$whereClause}
            ORDER BY ta.created_at DESC
            LIMIT 50
        ");
        $stmt->execute($params);
        $recentActivity = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format the response
        $analytics = [
            'overview' => [
                'total_students' => (int)$overallStats['total_students'],
                'total_modules' => (int)$overallStats['total_modules'],
                'average_completion' => $overallStats['overall_avg_completion'] ? (float)$overallStats['overall_avg_completion'] : 0,
                'average_time_spent' => $overallStats['overall_avg_time'] ? (int)$overallStats['overall_avg_time'] : 0,
                'average_score' => $overallStats['overall_avg_score'] ? (float)$overallStats['overall_avg_score'] : 0,
                'total_completed' => (int)$overallStats['total_completed'],
                'total_in_progress' => (int)$overallStats['total_in_progress'],
                'total_paused' => (int)$overallStats['total_paused']
            ],
            'module_stats' => array_map(function($module) {
                return [
                    'id' => (int)$module['id'],
                    'name' => $module['name'],
                    'module_type' => $module['module_type'],
                    'difficulty_level' => $module['difficulty_level'],
                    'estimated_duration' => (int)$module['estimated_duration'],
                    'total_students' => (int)$module['total_students'],
                    'average_completion' => $module['avg_completion'] ? (float)$module['avg_completion'] : 0,
                    'average_time_spent' => $module['avg_time_spent'] ? (int)$module['avg_time_spent'] : 0,
                    'average_score' => $module['avg_score'] ? (float)$module['avg_score'] : 0,
                    'completed_count' => (int)$module['completed_count'],
                    'in_progress_count' => (int)$module['in_progress_count'],
                    'paused_count' => (int)$module['paused_count']
                ];
            }, $moduleStats),
            'student_progress' => array_map(function($student) {
                return [
                    'student_id' => (int)$student['student_id'],
                    'student_name' => $student['student_name'],
                    'student_email' => $student['student_email'],
                    'module_id' => (int)$student['module_id'],
                    'module_name' => $student['module_name'],
                    'module_type' => $student['module_type'],
                    'difficulty_level' => $student['difficulty_level'],
                    'completion_percentage' => (float)$student['completion_percentage'],
                    'time_spent' => (int)$student['time_spent'],
                    'score' => $student['score'] ? (float)$student['score'] : null,
                    'status' => $student['status'],
                    'started_at' => $student['started_at'],
                    'completed_at' => $student['completed_at'],
                    'last_accessed' => $student['last_accessed']
                ];
            }, $studentProgress),
            'recent_activity' => array_map(function($activity) {
                return [
                    'action_type' => $activity['action_type'],
                    'created_at' => $activity['created_at'],
                    'student_name' => $activity['student_name'],
                    'module_name' => $activity['module_name'],
                    'time_spent' => (int)$activity['time_spent'],
                    'score' => $activity['score'] ? (float)$activity['score'] : null
                ];
            }, $recentActivity)
        ];
        
        return $analytics;
        
    } catch (PDOException $e) {
        error_log("Error getting tutorial analytics: " . $e->getMessage());
        throw new Exception("Failed to retrieve tutorial analytics");
    }
}
?>
