<?php
/**
 * Get Tutorial Steps
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
    $moduleId = $_GET['module_id'] ?? '';
    
    if (empty($moduleId)) {
        throw new Exception('Module ID is required');
    }
    
    $steps = getTutorialSteps($moduleId, $userId);
    
    echo json_encode([
        'success' => true,
        'steps' => $steps
    ]);
    
} catch (Exception $e) {
    error_log("Error getting tutorial steps: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function getTutorialSteps($moduleId, $userId) {
    global $pdo;
    
    try {
        // First verify the module exists and user has access
        $stmt = $pdo->prepare("
            SELECT tm.id, tm.name, tm.module_type, tm.difficulty_level 
            FROM tutorial_modules tm 
            WHERE tm.id = ? AND tm.is_active = 1
        ");
        $stmt->execute([$moduleId]);
        $module = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$module) {
            throw new Exception('Tutorial module not found or inactive');
        }
        
        // Get all steps for the module with assessments
        $stmt = $pdo->prepare("
            SELECT 
                ts.id,
                ts.tutorial_module_id,
                ts.step_number,
                ts.title,
                ts.description,
                ts.instruction,
                ts.target_element,
                ts.action_type,
                ts.expected_result,
                ts.is_interactive,
                ts.created_at,
                ta.id as assessment_id,
                ta.question,
                ta.question_type,
                ta.options,
                ta.correct_answer,
                ta.explanation,
                ta.points
            FROM tutorial_steps ts
            LEFT JOIN tutorial_assessments ta ON ts.id = ta.tutorial_step_id
            WHERE ts.tutorial_module_id = ?
            ORDER BY ts.step_number, ta.id
        ");
        $stmt->execute([$moduleId]);
        $rawSteps = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Group steps by step_number and include assessments
        $groupedSteps = [];
        foreach ($rawSteps as $row) {
            $stepNumber = $row['step_number'];
            
            if (!isset($groupedSteps[$stepNumber])) {
                $groupedSteps[$stepNumber] = [
                    'id' => $row['id'],
                    'tutorial_module_id' => $row['tutorial_module_id'],
                    'step_number' => $row['step_number'],
                    'title' => $row['title'],
                    'description' => $row['description'],
                    'instruction' => $row['instruction'],
                    'target_element' => $row['target_element'],
                    'action_type' => $row['action_type'],
                    'expected_result' => $row['expected_result'],
                    'is_interactive' => (bool)$row['is_interactive'],
                    'created_at' => $row['created_at'],
                    'assessments' => []
                ];
            }
            
            // Add assessment if it exists
            if ($row['assessment_id']) {
                $assessment = [
                    'id' => $row['assessment_id'],
                    'question' => $row['question'],
                    'question_type' => $row['question_type'],
                    'options' => $row['options'] ? json_decode($row['options'], true) : null,
                    'correct_answer' => $row['correct_answer'],
                    'explanation' => $row['explanation'],
                    'points' => (int)$row['points']
                ];
                
                $groupedSteps[$stepNumber]['assessments'][] = $assessment;
            }
        }
        
        // Convert to indexed array
        $steps = array_values($groupedSteps);
        
        // Get user's current progress for this module
        $stmt = $pdo->prepare("
            SELECT current_step, completion_percentage, status 
            FROM tutorial_progress 
            WHERE user_id = ? AND tutorial_module_id = ?
        ");
        $stmt->execute([$userId, $moduleId]);
        $progress = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Add progress information to response
        $response = [
            'module' => $module,
            'steps' => $steps,
            'progress' => $progress ?: [
                'current_step' => 1,
                'completion_percentage' => 0,
                'status' => 'not_started'
            ]
        ];
        
        return $response;
        
    } catch (PDOException $e) {
        error_log("Error getting tutorial steps: " . $e->getMessage());
        throw new Exception("Failed to retrieve tutorial steps");
    }
}
?>
