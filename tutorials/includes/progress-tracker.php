<?php
/**
 * Tutorial Progress Tracker
 * Handles student progress tracking for Hotel PMS Training
 */

class TutorialProgressTracker {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get student progress for a specific module
     */
    public function getModuleProgress($user_id, $module_name) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT tp.*, tm.name as module_name, tm.module_type, tm.estimated_duration
                FROM tutorial_progress tp
                JOIN tutorial_modules tm ON tp.tutorial_module_id = tm.id
                WHERE tp.user_id = ? AND tm.name = ?
            ");
            $stmt->execute([$user_id, $module_name]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getting module progress: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update module progress
     */
    public function updateProgress($user_id, $module_name, $module_type, $progress_percentage, $current_step, $total_steps, $status = 'in_progress') {
        try {
            $this->pdo->beginTransaction();
            
            // Get module ID
            $stmt = $this->pdo->prepare("SELECT id FROM tutorial_modules WHERE name = ?");
            $stmt->execute([$module_name]);
            $module = $stmt->fetch();
            
            if (!$module) {
                throw new Exception("Module not found: " . $module_name);
            }
            
            $module_id = $module['id'];
            
            // Check if progress record exists
            $stmt = $this->pdo->prepare("SELECT id FROM tutorial_progress WHERE user_id = ? AND tutorial_module_id = ?");
            $stmt->execute([$user_id, $module_id]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Update existing record
                $stmt = $this->pdo->prepare("
                    UPDATE tutorial_progress 
                    SET completion_percentage = ?, current_step = ?, status = ?, 
                        started_at = CASE WHEN started_at IS NULL THEN NOW() ELSE started_at END,
                        completed_at = CASE WHEN ? = 'completed' THEN NOW() ELSE completed_at END,
                        updated_at = NOW()
                    WHERE user_id = ? AND tutorial_module_id = ?
                ");
                $stmt->execute([$progress_percentage, $current_step, $status, $status, $user_id, $module_id]);
            } else {
                // Create new record
                $stmt = $this->pdo->prepare("
                    INSERT INTO tutorial_progress 
                    (user_id, tutorial_module_id, completion_percentage, current_step, status, started_at, completed_at)
                    VALUES (?, ?, ?, ?, ?, NOW(), ?)
                ");
                $completed_at = ($status === 'completed') ? 'NOW()' : 'NULL';
                $stmt->execute([$user_id, $module_id, $progress_percentage, $current_step, $status, $completed_at]);
            }
            
            $this->pdo->commit();
            return true;
            
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error updating progress: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all student progress
     */
    public function getAllProgress($user_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM tutorial_progress 
                WHERE user_id = ? 
                ORDER BY last_accessed DESC
            ");
            $stmt->execute([$user_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting all progress: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get student statistics
     */
    public function getStudentStats($user_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    COUNT(*) as total_modules,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_modules,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_modules,
                    AVG(progress_percentage) as average_progress,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) / COUNT(*) * 100 as completion_rate
                FROM tutorial_progress 
                WHERE user_id = ?
            ");
            $stmt->execute([$user_id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getting student stats: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Record tutorial action for analytics
     */
    public function recordAction($user_id, $action_type, $module_name = null, $action_data = null) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO tutorial_analytics (user_id, action_type, module_name, action_data)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$user_id, $action_type, $module_name, json_encode($action_data)]);
            return true;
        } catch (PDOException $e) {
            error_log("Error recording action: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get student analytics
     */
    public function getStudentAnalytics($user_id, $days = 30) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    action_type,
                    module_name,
                    COUNT(*) as count,
                    DATE(created_at) as date
                FROM tutorial_analytics 
                WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY action_type, module_name, DATE(created_at)
                ORDER BY created_at DESC
            ");
            $stmt->execute([$user_id, $days]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting analytics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Start a new module
     */
    public function startModule($user_id, $module_name, $module_type, $total_steps) {
        return $this->updateProgress($user_id, $module_name, $module_type, 0, 1, $total_steps, 'in_progress');
    }
    
    /**
     * Complete a module
     */
    public function completeModule($user_id, $module_name) {
        return $this->updateProgress($user_id, $module_name, '', 100, 0, 0, 'completed');
    }
}
?>
