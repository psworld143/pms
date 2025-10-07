<?php
/**
 * PMS Main Functions
 * Core functions used across all PMS modules
 */

require_once __DIR__ . '/database.php';

/**
 * Get users with filters
 */
function getUsers($role = '', $status = '', $search = '') {
    global $pdo;

    try {
        $where_conditions = ["1=1"];
        $params = [];

        // Role filter
        if (!empty($role)) {
            $where_conditions[] = "role = ?";
            $params[] = $role;
        }

        // Status filter
        if (!empty($status)) {
            if ($status === 'active') {
                $where_conditions[] = "is_active = 1";
            } elseif ($status === 'inactive') {
                $where_conditions[] = "is_active = 0";
            }
        }

        // Search filter
        if (!empty($search)) {
            $where_conditions[] = "(name LIKE ? OR username LIKE ? OR email LIKE ?)";
            $search_param = "%{$search}%";
            $params = array_merge($params, [$search_param, $search_param, $search_param]);
        }

        $where_clause = implode(" AND ", $where_conditions);

        $stmt = $pdo->prepare("
            SELECT id, name, username, email, role, is_active, created_at
            FROM users
            WHERE {$where_clause}
            ORDER BY created_at DESC
        ");

        $stmt->execute($params);
        return $stmt->fetchAll();

    } catch (PDOException $e) {
        error_log("Error getting users: " . $e->getMessage());
        return [];
    }
}

/**
 * Get user details by ID
 */
function getUserById($user_id) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error getting user by ID: " . $e->getMessage());
        return null;
    }
}

/**
 * Create new user
 */
function createUser($data) {
    global $pdo;

    try {
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO users (name, username, password, email, role, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, TRUE, NOW())
        ");

        $stmt->execute([
            $data['name'],
            $data['username'],
            $hashed_password,
            $data['email'],
            $data['role']
        ]);

        return [
            'success' => true,
            'user_id' => $pdo->lastInsertId()
        ];
    } catch (PDOException $e) {
        error_log("Error creating user: " . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Update user
 */
function updateUser($user_id, $data) {
    global $pdo;

    try {
        $update_fields = [];
        $params = [];

        if (isset($data['name'])) {
            $update_fields[] = "name = ?";
            $params[] = $data['name'];
        }

        if (isset($data['username'])) {
            $update_fields[] = "username = ?";
            $params[] = $data['username'];
        }

        if (isset($data['email'])) {
            $update_fields[] = "email = ?";
            $params[] = $data['email'];
        }

        if (isset($data['role'])) {
            $update_fields[] = "role = ?";
            $params[] = $data['role'];
        }

        if (isset($data['is_active'])) {
            $update_fields[] = "is_active = ?";
            $params[] = $data['is_active'] ? 1 : 0;
        }

        if (isset($data['password']) && !empty($data['password'])) {
            $update_fields[] = "password = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (empty($update_fields)) {
            return [
                'success' => false,
                'message' => 'No fields to update'
            ];
        }

        $update_fields[] = "updated_at = NOW()";
        $params[] = $user_id;

        $sql = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return [
            'success' => true
        ];
    } catch (PDOException $e) {
        error_log("Error updating user: " . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Delete user (soft delete)
 */
function deleteUser($user_id, $current_user_id) {
    global $pdo;

    try {
        // Prevent deleting own account
        if ($user_id === $current_user_id) {
            return [
                'success' => false,
                'message' => 'Cannot delete your own account'
            ];
        }

        // Get user details for logging
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }

        // Check if user has created reservations or other data
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM reservations WHERE created_by = ?
            UNION ALL
            SELECT COUNT(*) as count FROM reservations WHERE checked_in_by = ?
            UNION ALL
            SELECT COUNT(*) as count FROM reservations WHERE checked_out_by = ?
        ");
        $stmt->execute([$user_id, $user_id, $user_id]);
        $results = $stmt->fetchAll();

        $total_records = array_sum(array_column($results, 'count'));

        if ($total_records > 0) {
            // Soft delete - deactivate user
            $stmt = $pdo->prepare("
                UPDATE users
                SET is_active = FALSE,
                    username = CONCAT('deleted_', UNIX_TIMESTAMP(), '_', username),
                    email = CONCAT('deleted_', UNIX_TIMESTAMP(), '_', email),
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$user_id]);

            $action = 'deactivated';
        } else {
            // Hard delete if no related records
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);

            $action = 'deleted';
        }

        return [
            'success' => true,
            'message' => "User {$action} successfully"
        ];
    } catch (PDOException $e) {
        error_log("Error deleting user: " . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Log user activity
 */
function logActivity($user_id, $action, $details = '') {
    global $pdo;

    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$user_id, $action, $details]);
    } catch (PDOException $e) {
        error_log("Error logging activity: " . $e->getMessage());
    }
}

/**
 * Check if username exists
 */
function usernameExists($username, $exclude_id = null) {
    global $pdo;

    try {
        if ($exclude_id) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$username, $exclude_id]);
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
        }

        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        error_log("Error checking username: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if email exists
 */
function emailExists($email, $exclude_id = null) {
    global $pdo;

    try {
        if ($exclude_id) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $exclude_id]);
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
        }

        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        error_log("Error checking email: " . $e->getMessage());
        return false;
    }
}

/**
 * Get user roles
 */
function getUserRoles() {
    return [
        'manager' => 'Manager',
        'front_desk' => 'Front Desk',
        'housekeeping' => 'Housekeeping'
    ];
}

/**
 * Validate user role
 */
function isValidUserRole($role) {
    $valid_roles = ['manager', 'front_desk', 'housekeeping'];
    return in_array($role, $valid_roles);
}

/**
 * Check user permissions
 */
function hasPermission($user_role, $required_role) {
    $role_hierarchy = [
        'front_desk' => 1,
        'housekeeping' => 1,
        'manager' => 3
    ];

    return isset($role_hierarchy[$user_role]) &&
           isset($role_hierarchy[$required_role]) &&
           $role_hierarchy[$user_role] >= $role_hierarchy[$required_role];
}

/**
 * Get user statistics
 */
function getUserStats() {
    global $pdo;

    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
        $total = $stmt->fetch()['total'];

        $stmt = $pdo->query("SELECT COUNT(*) as active FROM users WHERE is_active = 1");
        $active = $stmt->fetch()['active'];

        $stmt = $pdo->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
        $role_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'total_users' => $total,
            'active_users' => $active,
            'inactive_users' => $total - $active,
            'role_distribution' => $role_stats
        ];
    } catch (PDOException $e) {
        error_log("Error getting user stats: " . $e->getMessage());
        return [
            'total_users' => 0,
            'active_users' => 0,
            'inactive_users' => 0,
            'role_distribution' => []
        ];
    }
}

/**
 * Send notification
 */
function sendNotification($user_id, $message, $type = 'info') {
    global $pdo;

    try {
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, type, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$user_id, $message, $type]);
    } catch (PDOException $e) {
        error_log("Error sending notification: " . $e->getMessage());
    }
}

/**
 * Get unread notifications count
 */
function getUnreadNotificationsCount($user_id) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        return $stmt->fetch()['count'];
    } catch (PDOException $e) {
        error_log("Error getting unread notifications count: " . $e->getMessage());
        return 0;
    }
}

/**
 * Validate email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Sanitize input
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

/**
 * Format date
 */
function formatDate($date, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($date));
}

/**
 * Generate random string
 */
function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length));
}
?>
