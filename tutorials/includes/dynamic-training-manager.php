<?php
/**
 * Dynamic Training Manager
 * Manages dynamic training content from database
 */

class DynamicTrainingManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get all training modules with dynamic content
     */
    public function getAllModules() {
        try {
            $stmt = $this->pdo->query("
                SELECT tm.*, 
                       COUNT(dtc.id) as step_count,
                       AVG(dtc.duration_minutes) as avg_duration
                FROM tutorial_modules tm
                LEFT JOIN dynamic_training_content dtc ON tm.id = dtc.tutorial_module_id AND dtc.is_active = 1
                WHERE tm.is_active = 1
                GROUP BY tm.id
                ORDER BY tm.name ASC
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting modules: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get module with dynamic content
     */
    public function getModule($module_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT tm.*, 
                       COUNT(dtc.id) as step_count,
                       SUM(dtc.duration_minutes) as total_duration
                FROM tutorial_modules tm
                LEFT JOIN dynamic_training_content dtc ON tm.id = dtc.tutorial_module_id AND dtc.is_active = 1
                WHERE tm.id = ? AND tm.is_active = 1
                GROUP BY tm.id
            ");
            $stmt->execute([$module_id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getting module: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get module by name
     */
    public function getModuleByName($module_name) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT tm.*, 
                       COUNT(dtc.id) as step_count,
                       SUM(dtc.duration_minutes) as total_duration
                FROM tutorial_modules tm
                LEFT JOIN dynamic_training_content dtc ON tm.id = dtc.tutorial_module_id AND dtc.is_active = 1
                WHERE tm.name = ? AND tm.is_active = 1
                GROUP BY tm.id
            ");
            $stmt->execute([$module_name]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getting module by name: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all steps for a module
     */
    public function getModuleSteps($module_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT dtc.*, 
                       tm.name as module_name,
                       tm.module_type,
                       tm.difficulty_level
                FROM dynamic_training_content dtc
                JOIN tutorial_modules tm ON dtc.tutorial_module_id = tm.id
                WHERE dtc.tutorial_module_id = ? AND dtc.is_active = 1
                ORDER BY dtc.order_index ASC, dtc.step_number ASC
            ");
            $stmt->execute([$module_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting module steps: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get specific step content
     */
    public function getStepContent($module_id, $step_number) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT dtc.*, 
                       tm.name as module_name,
                       tm.module_type,
                       tm.difficulty_level
                FROM dynamic_training_content dtc
                JOIN tutorial_modules tm ON dtc.tutorial_module_id = tm.id
                WHERE dtc.tutorial_module_id = ? AND dtc.step_number = ? AND dtc.is_active = 1
            ");
            $stmt->execute([$module_id, $step_number]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getting step content: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get quizzes for a step
     */
    public function getStepQuizzes($content_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM dynamic_training_quizzes 
                WHERE content_id = ? AND is_active = 1
                ORDER BY id ASC
            ");
            $stmt->execute([$content_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting step quizzes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get simulations for a step
     */
    public function getStepSimulations($content_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM dynamic_training_simulations 
                WHERE content_id = ? AND is_active = 1
                ORDER BY id ASC
            ");
            $stmt->execute([$content_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting step simulations: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get resources for a module or step
     */
    public function getResources($module_id = null, $content_id = null) {
        try {
            $where_conditions = [];
            $params = [];
            
            if ($module_id) {
                $where_conditions[] = "tutorial_module_id = ?";
                $params[] = $module_id;
            }
            
            if ($content_id) {
                $where_conditions[] = "content_id = ?";
                $params[] = $content_id;
            }
            
            $where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";
            
            $stmt = $this->pdo->prepare("
                SELECT * FROM dynamic_training_resources 
                $where_clause
                ORDER BY created_at DESC
            ");
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting resources: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get training categories
     */
    public function getCategories() {
        try {
            $stmt = $this->pdo->query("
                SELECT * FROM dynamic_training_categories 
                WHERE is_active = 1
                ORDER BY sort_order ASC, name ASC
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting categories: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get training tags
     */
    public function getTags() {
        try {
            $stmt = $this->pdo->query("
                SELECT * FROM dynamic_training_tags 
                ORDER BY name ASC
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting tags: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get modules by category
     */
    public function getModulesByCategory($category_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT tm.*, 
                       COUNT(dtc.id) as step_count,
                       AVG(dtc.duration_minutes) as avg_duration
                FROM tutorial_modules tm
                LEFT JOIN dynamic_training_content dtc ON tm.id = dtc.tutorial_module_id AND dtc.is_active = 1
                WHERE tm.category_id = ? AND tm.is_active = 1
                GROUP BY tm.id
                ORDER BY tm.name ASC
            ");
            $stmt->execute([$category_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting modules by category: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Search modules
     */
    public function searchModules($search_term) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT tm.*, 
                       COUNT(dtc.id) as step_count,
                       AVG(dtc.duration_minutes) as avg_duration
                FROM tutorial_modules tm
                LEFT JOIN dynamic_training_content dtc ON tm.id = dtc.tutorial_module_id AND dtc.is_active = 1
                WHERE tm.is_active = 1 AND (
                    tm.name LIKE ? OR 
                    tm.description LIKE ? OR
                    tm.module_type LIKE ?
                )
                GROUP BY tm.id
                ORDER BY tm.name ASC
            ");
            $search_param = "%$search_term%";
            $stmt->execute([$search_param, $search_param, $search_param]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error searching modules: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get module statistics
     */
    public function getModuleStats($module_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    COUNT(dtc.id) as total_steps,
                    SUM(dtc.duration_minutes) as total_duration,
                    COUNT(dtq.id) as total_quizzes,
                    COUNT(dts.id) as total_simulations,
                    COUNT(dtr.id) as total_resources
                FROM tutorial_modules tm
                LEFT JOIN dynamic_training_content dtc ON tm.id = dtc.tutorial_module_id AND dtc.is_active = 1
                LEFT JOIN dynamic_training_quizzes dtq ON dtc.id = dtq.content_id AND dtq.is_active = 1
                LEFT JOIN dynamic_training_simulations dts ON dtc.id = dts.content_id AND dts.is_active = 1
                LEFT JOIN dynamic_training_resources dtr ON tm.id = dtr.tutorial_module_id
                WHERE tm.id = ?
                GROUP BY tm.id
            ");
            $stmt->execute([$module_id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getting module stats: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create new training content
     */
    public function createTrainingContent($module_id, $step_data) {
        try {
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->prepare("
                INSERT INTO dynamic_training_content 
                (tutorial_module_id, step_number, title, content, step_type, duration_minutes, 
                 learning_objectives, interactive_data, prerequisites, is_required, order_index)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $module_id,
                $step_data['step_number'],
                $step_data['title'],
                $step_data['content'],
                $step_data['step_type'] ?? 'learning',
                $step_data['duration_minutes'] ?? 5,
                json_encode($step_data['learning_objectives'] ?? []),
                json_encode($step_data['interactive_data'] ?? []),
                $step_data['prerequisites'] ?? '',
                $step_data['is_required'] ?? true,
                $step_data['order_index'] ?? 0
            ]);
            
            $content_id = $this->pdo->lastInsertId();
            
            $this->pdo->commit();
            return $content_id;
            
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error creating training content: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update training content
     */
    public function updateTrainingContent($content_id, $step_data) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE dynamic_training_content 
                SET title = ?, content = ?, step_type = ?, duration_minutes = ?, 
                    learning_objectives = ?, interactive_data = ?, prerequisites = ?, 
                    is_required = ?, order_index = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([
                $step_data['title'],
                $step_data['content'],
                $step_data['step_type'] ?? 'learning',
                $step_data['duration_minutes'] ?? 5,
                json_encode($step_data['learning_objectives'] ?? []),
                json_encode($step_data['interactive_data'] ?? []),
                $step_data['prerequisites'] ?? '',
                $step_data['is_required'] ?? true,
                $step_data['order_index'] ?? 0,
                $content_id
            ]);
            
            return true;
            
        } catch (PDOException $e) {
            error_log("Error updating training content: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete training content
     */
    public function deleteTrainingContent($content_id) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE dynamic_training_content 
                SET is_active = 0, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$content_id]);
            return true;
            
        } catch (PDOException $e) {
            error_log("Error deleting training content: " . $e->getMessage());
            return false;
        }
    }
}
?>

