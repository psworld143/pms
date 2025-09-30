<?php
/**
 * Unified Session Management for PMS
 * Handles authentication and session management across all modules
 */

class PMSSessionManager {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Login user to PMS system
     */
    public function login($username, $password) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, name, username, password, role, is_active 
                FROM users 
                WHERE username = ? AND is_active = 1
            ");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Set unified session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['login_time'] = time();
                $_SESSION['last_activity'] = time();
                
                // Set module-specific session variables for compatibility
                $_SESSION['pos_user_id'] = $user['id'];
                $_SESSION['pos_user_name'] = $user['name'];
                $_SESSION['pos_user_role'] = $user['role'];
                
                // Log the login
                $this->logActivity($user['id'], 'login', 'User logged into PMS system');
                
                return [
                    'success' => true,
                    'user' => [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'role' => $user['role']
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Invalid username or password'
                ];
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'System error. Please try again.'
            ];
        }
    }
    
    /**
     * Logout user from PMS system
     */
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $this->logActivity($_SESSION['user_id'], 'logout', 'User logged out of PMS system');
        }
        
        // Clear all session variables
        $_SESSION = array();
        
        // Destroy the session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        return ['success' => true];
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Get current user information
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'role' => $_SESSION['user_role'],
            'username' => $_SESSION['username']
        ];
    }
    
    /**
     * Check if user has access to a module
     */
    public function hasModuleAccess($module) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        $user_role = $_SESSION['user_role'];
        
        $module_access = [
            'manager' => ['booking', 'inventory', 'pos', 'reports'],
            'front_desk' => ['booking', 'pos'],
            'housekeeping' => ['booking', 'inventory'],
            'pos_user' => ['pos'],
            'student' => ['booking', 'inventory', 'pos']
        ];
        
        $allowed_modules = $module_access[$user_role] ?? [];
        return in_array($module, $allowed_modules);
    }
    
    /**
     * Redirect to login if not authenticated
     */
    public function requireAuth($redirect_url = null) {
        if (!$this->isLoggedIn()) {
            $login_url = $redirect_url ?: '/seait/pms/booking/login.php';
            header("Location: $login_url");
            exit();
        }
    }
    
    /**
     * Redirect to login if user doesn't have module access
     */
    public function requireModuleAccess($module, $redirect_url = null) {
        $this->requireAuth($redirect_url);
        
        if (!$this->hasModuleAccess($module)) {
            $error_url = $redirect_url ?: '/seait/pms/booking/login.php';
            header("Location: $error_url?error=access_denied");
            exit();
        }
    }
    
    /**
     * Update last activity time
     */
    public function updateActivity() {
        if ($this->isLoggedIn()) {
            $_SESSION['last_activity'] = time();
        }
    }
    
    /**
     * Check session timeout
     */
    public function checkSessionTimeout($timeout_minutes = 60) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        $last_activity = $_SESSION['last_activity'] ?? 0;
        $timeout_seconds = $timeout_minutes * 60;
        
        if ((time() - $last_activity) > $timeout_seconds) {
            $this->logout();
            return false;
        }
        
        return true;
    }
    
    /**
     * Log user activity
     */
    private function logActivity($user_id, $action, $details = '') {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            
            $stmt->execute([$user_id, $action, $details, $ip_address, $user_agent]);
        } catch (PDOException $e) {
            error_log("Activity logging error: " . $e->getMessage());
        }
    }
    
    /**
     * Get user permissions
     */
    public function getUserPermissions() {
        if (!$this->isLoggedIn()) {
            return [];
        }
        
        $user_role = $_SESSION['user_role'];
        
        $permissions = [
            'manager' => [
                'view_all_reports', 'manage_users', 'manage_rooms', 'manage_inventory',
                'process_payments', 'view_analytics', 'manage_settings'
            ],
            'front_desk' => [
                'manage_reservations', 'check_in_guests', 'check_out_guests',
                'process_payments', 'view_guest_info'
            ],
            'housekeeping' => [
                'manage_room_status', 'manage_tasks', 'request_inventory',
                'update_room_condition'
            ],
            'pos_user' => [
                'process_orders', 'manage_menu', 'process_payments',
                'view_sales_reports'
            ],
            'student' => [
                'view_dashboard', 'practice_scenarios', 'view_reports',
                'access_training'
            ]
        ];
        
        return $permissions[$user_role] ?? [];
    }
    
    /**
     * Check if user has specific permission
     */
    public function hasPermission($permission) {
        $permissions = $this->getUserPermissions();
        return in_array($permission, $permissions);
    }
}

// Initialize session manager
$session_manager = PMSSessionManager::getInstance();

// Helper functions for backward compatibility
function isLoggedIn() {
    global $session_manager;
    return $session_manager->isLoggedIn();
}

function getCurrentUser() {
    global $session_manager;
    return $session_manager->getCurrentUser();
}

function hasModuleAccess($module) {
    global $session_manager;
    return $session_manager->hasModuleAccess($module);
}

function hasPermission($permission) {
    global $session_manager;
    return $session_manager->hasPermission($permission);
}

function requireAuth($redirect_url = null) {
    global $session_manager;
    $session_manager->requireAuth($redirect_url);
}

function requireModuleAccess($module, $redirect_url = null) {
    global $session_manager;
    $session_manager->requireModuleAccess($module, $redirect_url);
}
?>
