<?php
/**
 * Update Tutorial Progress
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

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $userId = $_SESSION['user_id'];
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $requiredFields = ['tutorial_module_id', 'action_type'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            throw new Exception("Field '{$field}' is required");
        }
    }
    
    $tutorialModuleId = $input['tutorial_module_id'];
    $actionType = $input['action_type'];
    $stepId = $input['step_id'] ?? null;
    $timeSpent = $input['time_spent'] ?? 0;
    $score = $input['score'] ?? null;
    
    $result = updateTutorialProgress($userId, $tutorialModuleId, $actionType, $stepId, $timeSpent, $score);
    
    echo json_encode([
        'success' => true,
        'progress' => $result
    ]);
    
} catch (Exception $e) {
    error_log("Error updating tutorial progress: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function updateTutorialProgress($userId, $tutorialModuleId, $actionType, $stepId = null, $timeSpent = 0, $score = null) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Verify the tutorial module exists
        $stmt = $pdo->prepare("SELECT id, name FROM tutorial_modules WHERE id = ? AND is_active = 1");
        $stmt->execute([$tutorialModuleId]);
        $module = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$module) {
            throw new Exception('Tutorial module not found or inactive');
        }
        
        // Get total number of steps for completion calculation
        $stmt = $pdo->prepare("SELECT COUNT(*) as total_steps FROM tutorial_steps WHERE tutorial_module_id = ?");
        $stmt->execute([$tutorialModuleId]);
        $totalSteps = $stmt->fetch(PDO::FETCH_ASSOC)['total_steps'];
        
        // Handle different action types
        switch ($actionType) {
            case 'start':
                $status = 'in_progress';
                $currentStep = 1;
                $completionPercentage = 0;
                $startedAt = date('Y-m-d H:i:s');
                break;
                
            case 'step_complete':
                $status = 'in_progress';
                $currentStep = $stepId ? $stepId + 1 : 1;
                $completionPercentage = $totalSteps > 0 ? min(100, ($currentStep - 1) / $totalSteps * 100) : 0;
                $startedAt = null;
                break;
                
            case 'complete':
                $status = 'completed';
                $currentStep = $totalSteps;
                $completionPercentage = 100;
                $startedAt = null;
                break;
                
            case 'pause':
                $status = 'paused';
                $currentStep = $stepId ?? 1;
                $completionPercentage = $totalSteps > 0 ? min(100, ($currentStep - 1) / $totalSteps * 100) : 0;
                $startedAt = null;
                break;
                
            case 'resume':
                $status = 'in_progress';
                $currentStep = $stepId ?? 1;
                $completionPercentage = $totalSteps > 0 ? min(100, ($currentStep - 1) / $totalSteps * 100) : 0;
                $startedAt = null;
                break;
                
            default:
                throw new Exception('Invalid action type');
        }
        
        // Update or insert progress record
        $stmt = $pdo->prepare("
            INSERT INTO tutorial_progress 
            (user_id, tutorial_module_id, current_step, completion_percentage, time_spent, score, status, started_at, last_accessed)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
            current_step = VALUES(current_step),
            completion_percentage = VALUES(completion_percentage),
            time_spent = time_spent + VALUES(time_spent),
            score = CASE 
                WHEN VALUES(score) IS NOT NULL THEN 
                    CASE 
                        WHEN score IS NULL THEN VALUES(score)
                        ELSE (score + VALUES(score)) / 2
                    END
                ELSE score
            END,
            status = VALUES(status),
            started_at = CASE 
                WHEN VALUES(started_at) IS NOT NULL THEN VALUES(started_at)
                ELSE started_at
            END,
            completed_at = CASE 
                WHEN VALUES(status) = 'completed' AND completed_at IS NULL THEN NOW()
                ELSE completed_at
            END,
            last_accessed = NOW(),
            updated_at = NOW()
        ");
        
        $stmt->execute([
            $userId, 
            $tutorialModuleId, 
            $currentStep, 
            $completionPercentage, 
            $timeSpent, 
            $score, 
            $status, 
            $startedAt
        ]);
        
        // Log analytics
        $stmt = $pdo->prepare("
            INSERT INTO tutorial_analytics 
            (user_id, tutorial_module_id, action_type, step_id, time_spent, score, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$userId, $tutorialModuleId, $actionType, $stepId, $timeSpent, $score]);
        
        // Get updated progress
        $stmt = $pdo->prepare("
            SELECT 
                current_step,
                completion_percentage,
                time_spent,
                score,
                status,
                started_at,
                completed_at,
                last_accessed
            FROM tutorial_progress 
            WHERE user_id = ? AND tutorial_module_id = ?
        ");
        $stmt->execute([$userId, $tutorialModuleId]);
        $progress = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $pdo->commit();
        
        // Format response
        return [
            'current_step' => (int)$progress['current_step'],
            'completion_percentage' => (float)$progress['completion_percentage'],
            'time_spent' => (int)$progress['time_spent'],
            'score' => $progress['score'] ? (float)$progress['score'] : null,
            'status' => $progress['status'],
            'started_at' => $progress['started_at'],
            'completed_at' => $progress['completed_at'],
            'last_accessed' => $progress['last_accessed']
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error updating tutorial progress: " . $e->getMessage());
        throw $e;
    }
}
?>
