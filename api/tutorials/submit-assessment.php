<?php
/**
 * Submit Tutorial Assessment
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
    $requiredFields = ['assessment_id', 'answer'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            throw new Exception("Field '{$field}' is required");
        }
    }
    
    $assessmentId = $input['assessment_id'];
    $answer = $input['answer'];
    $timeSpent = $input['time_spent'] ?? 0;
    
    $result = submitAssessment($userId, $assessmentId, $answer, $timeSpent);
    
    echo json_encode([
        'success' => true,
        'result' => $result
    ]);
    
} catch (Exception $e) {
    error_log("Error submitting assessment: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function submitAssessment($userId, $assessmentId, $answer, $timeSpent = 0) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Get assessment details
        $stmt = $pdo->prepare("
            SELECT 
                ta.*,
                ts.tutorial_module_id,
                ts.step_number,
                tm.name as module_name
            FROM tutorial_assessments ta
            JOIN tutorial_steps ts ON ta.tutorial_step_id = ts.id
            JOIN tutorial_modules tm ON ts.tutorial_module_id = tm.id
            WHERE ta.id = ?
        ");
        $stmt->execute([$assessmentId]);
        $assessment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$assessment) {
            throw new Exception('Assessment not found');
        }
        
        // Evaluate the answer based on question type
        $evaluation = evaluateAnswer($assessment, $answer);
        
        // Log the assessment submission
        $stmt = $pdo->prepare("
            INSERT INTO tutorial_analytics 
            (user_id, tutorial_module_id, action_type, step_id, time_spent, score, metadata, created_at)
            VALUES (?, ?, 'assessment_complete', ?, ?, ?, ?, NOW())
        ");
        
        $metadata = json_encode([
            'assessment_id' => $assessmentId,
            'question_type' => $assessment['question_type'],
            'user_answer' => $answer,
            'correct_answer' => $assessment['correct_answer'],
            'is_correct' => $evaluation['is_correct']
        ]);
        
        $stmt->execute([
            $userId,
            $assessment['tutorial_module_id'],
            $assessment['tutorial_step_id'],
            $timeSpent,
            $evaluation['score'],
            $metadata
        ]);
        
        // Update tutorial progress if this is a significant assessment
        if ($evaluation['score'] > 0) {
            updateProgressFromAssessment($userId, $assessment['tutorial_module_id'], $assessment['step_number'], $evaluation['score']);
        }
        
        $pdo->commit();
        
        // Determine next step
        $nextStep = $assessment['step_number'] + 1;
        
        // Check if there are more steps in this module
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_steps 
            FROM tutorial_steps 
            WHERE tutorial_module_id = ?
        ");
        $stmt->execute([$assessment['tutorial_module_id']]);
        $totalSteps = $stmt->fetch(PDO::FETCH_ASSOC)['total_steps'];
        
        if ($nextStep > $totalSteps) {
            $nextStep = null; // No more steps
        }
        
        return [
            'is_correct' => $evaluation['is_correct'],
            'score' => $evaluation['score'],
            'explanation' => $assessment['explanation'],
            'next_step' => $nextStep,
            'module_name' => $assessment['module_name'],
            'question_type' => $assessment['question_type']
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error submitting assessment: " . $e->getMessage());
        throw $e;
    }
}

function evaluateAnswer($assessment, $answer) {
    $questionType = $assessment['question_type'];
    $correctAnswer = $assessment['correct_answer'];
    $points = $assessment['points'];
    
    $isCorrect = false;
    
    switch ($questionType) {
        case 'multiple_choice':
            $isCorrect = (strtolower(trim($answer)) === strtolower(trim($correctAnswer)));
            break;
            
        case 'true_false':
            $answerBool = in_array(strtolower(trim($answer)), ['true', '1', 'yes', 't']);
            $correctBool = in_array(strtolower(trim($correctAnswer)), ['true', '1', 'yes', 't']);
            $isCorrect = ($answerBool === $correctBool);
            break;
            
        case 'fill_blank':
            // For fill-in-the-blank, check if answer contains key words from correct answer
            $answerWords = array_map('strtolower', array_map('trim', explode(' ', $answer)));
            $correctWords = array_map('strtolower', array_map('trim', explode(' ', $correctAnswer)));
            $isCorrect = count(array_intersect($answerWords, $correctWords)) >= (count($correctWords) * 0.7);
            break;
            
        case 'simulation':
            // For simulation questions, check if the action matches expected result
            $isCorrect = (strtolower(trim($answer)) === strtolower(trim($correctAnswer)));
            break;
            
        default:
            $isCorrect = false;
    }
    
    return [
        'is_correct' => $isCorrect,
        'score' => $isCorrect ? $points : 0
    ];
}

function updateProgressFromAssessment($userId, $tutorialModuleId, $stepNumber, $score) {
    global $pdo;
    
    try {
        // Get current progress
        $stmt = $pdo->prepare("
            SELECT current_step, completion_percentage, score, time_spent
            FROM tutorial_progress
            WHERE user_id = ? AND tutorial_module_id = ?
        ");
        $stmt->execute([$userId, $tutorialModuleId]);
        $progress = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($progress) {
            // Update score (average with existing score)
            $newScore = $progress['score'] ? ($progress['score'] + $score) / 2 : $score;
            
            $stmt = $pdo->prepare("
                UPDATE tutorial_progress
                SET score = ?, updated_at = NOW()
                WHERE user_id = ? AND tutorial_module_id = ?
            ");
            $stmt->execute([$newScore, $userId, $tutorialModuleId]);
        }
        
    } catch (Exception $e) {
        error_log("Error updating progress from assessment: " . $e->getMessage());
        // Don't throw - this is not critical
    }
}
?>
