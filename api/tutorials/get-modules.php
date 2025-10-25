<?php
/**
 * Get Tutorial Modules
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
    $difficulty = $_GET['difficulty'] ?? '';
    $moduleType = $_GET['module_type'] ?? '';
    
    $modules = getTutorialModules($userId, $difficulty, $moduleType);
    
    echo json_encode([
        'success' => true,
        'modules' => $modules
    ]);
    
} catch (Exception $e) {
    error_log("Error getting tutorial modules: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function getTutorialModules($userId, $difficulty = '', $moduleType = '') {
    global $pdo;
    
    try {
        $whereConditions = ["tm.is_active = 1"];
        $params = [];
        
        // Add difficulty filter
        if (!empty($difficulty)) {
            $whereConditions[] = "tm.difficulty_level = ?";
            $params[] = $difficulty;
        }
        
        // Add module type filter
        if (!empty($moduleType)) {
            $whereConditions[] = "tm.module_type = ?";
            $params[] = $moduleType;
        }
        
        $whereClause = implode(" AND ", $whereConditions);
        
        $stmt = $pdo->prepare("
            SELECT 
                tm.id,
                tm.name,
                tm.description,
                tm.module_type,
                tm.difficulty_level,
                tm.estimated_duration,
                tm.is_active,
                tm.created_at,
                tm.updated_at,
                COALESCE(tp.completion_percentage, 0) as completion_percentage,
                COALESCE(tp.time_spent, 0) as time_spent,
                COALESCE(tp.score, 0) as score,
                COALESCE(tp.status, 'not_started') as status,
                tp.started_at,
                tp.completed_at,
                tp.last_accessed
            FROM tutorial_modules tm
            LEFT JOIN tutorial_progress tp ON tm.id = tp.tutorial_module_id AND tp.user_id = ?
            WHERE {$whereClause}
            ORDER BY tm.module_type, tm.difficulty_level, tm.name
        ");
        
        $params = array_merge([$userId], $params);
        $stmt->execute($params);
        
        $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format the response
        foreach ($modules as &$module) {
            $module['completion_percentage'] = (float)$module['completion_percentage'];
            $module['time_spent'] = (int)$module['time_spent'];
            $module['score'] = (float)$module['score'];
            $module['estimated_duration'] = (int)$module['estimated_duration'];
            $module['is_active'] = (bool)$module['is_active'];
            
            // Calculate progress status
            if ($module['status'] === 'not_started' && $module['completion_percentage'] > 0) {
                $module['status'] = 'in_progress';
            }
        }
        
        return $modules;
        
    } catch (PDOException $e) {
        error_log("Error getting tutorial modules: " . $e->getMessage());
        throw new Exception("Failed to retrieve tutorial modules");
    }
}
?>
