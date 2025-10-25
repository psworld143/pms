<?php
/**
 * Booking Module Configuration
 * Hotel PMS Training System
 */

// Include the main PMS database configuration
require_once __DIR__ . '/../../includes/database.php';
// Booking module specific configuration
if (!defined('BOOKING_MODULE_NAME')) {
    define('BOOKING_MODULE_NAME', 'Hotel Booking System');
}
if (!defined('BOOKING_VERSION')) {
    define('BOOKING_VERSION', '1.0.0');
}

// Room types configuration
if (!defined('ROOM_TYPES')) {
    define('ROOM_TYPES', [
        'standard' => 'Standard Room',
        'deluxe' => 'Deluxe Room', 
        'suite' => 'Suite',
        'presidential' => 'Presidential Suite'
    ]);
}

// Room statuses
if (!defined('ROOM_STATUSES')) {
    define('ROOM_STATUSES', [
        'available' => 'Available',
        'occupied' => 'Occupied',
        'reserved' => 'Reserved',
        'maintenance' => 'Maintenance',
        'out_of_service' => 'Out of Service'
    ]);
}

// Reservation statuses
if (!defined('RESERVATION_STATUSES')) {
    define('RESERVATION_STATUSES', [
        'confirmed' => 'Confirmed',
        'checked_in' => 'Checked In',
        'checked_out' => 'Checked Out',
        'cancelled' => 'Cancelled',
        'no_show' => 'No Show'
    ]);
}

// User roles
if (!defined('USER_ROLES')) {
    define('USER_ROLES', [
        'manager' => 'Manager',
        'front_desk' => 'Front Desk',
        'housekeeping' => 'Housekeeping'
    ]);
}

// Pagination settings
if (!defined('ITEMS_PER_PAGE')) {
    define('ITEMS_PER_PAGE', 20);
}

// Date format
if (!defined('DATE_FORMAT')) {
    define('DATE_FORMAT', 'Y-m-d');
}
if (!defined('DATETIME_FORMAT')) {
    define('DATETIME_FORMAT', 'Y-m-d H:i:s');
}

// Currency settings
if (!defined('CURRENCY_SYMBOL')) {
    define('CURRENCY_SYMBOL', '$');
}
if (!defined('CURRENCY_CODE')) {
    define('CURRENCY_CODE', 'USD');
}

// Email settings (for notifications)
if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', 'localhost');
}
if (!defined('SMTP_PORT')) {
    define('SMTP_PORT', 587);
}
if (!defined('SMTP_USERNAME')) {
    define('SMTP_USERNAME', '');
}
if (!defined('SMTP_PASSWORD')) {
    define('SMTP_PASSWORD', '');
}
if (!defined('FROM_EMAIL')) {
    define('FROM_EMAIL', 'noreply@hotel.com');
}
if (!defined('FROM_NAME')) {
    define('FROM_NAME', 'Hotel PMS System');
}

// File upload settings
if (!defined('MAX_FILE_SIZE')) {
    define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
}
if (!defined('ALLOWED_FILE_TYPES')) {
    define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);
}

// Security settings
if (!defined('SESSION_TIMEOUT')) {
    define('SESSION_TIMEOUT', 3600); // 1 hour
}
if (!defined('MAX_LOGIN_ATTEMPTS')) {
    define('MAX_LOGIN_ATTEMPTS', 5);
}
if (!defined('LOGIN_LOCKOUT_TIME')) {
    define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes
}

// Training settings
if (!defined('TRAINING_ENABLED')) {
    define('TRAINING_ENABLED', true);
}
if (!defined('TRAINING_POINTS_PER_SCENARIO')) {
    define('TRAINING_POINTS_PER_SCENARIO', 10);
}
if (!defined('TRAINING_CERTIFICATE_THRESHOLD')) {
    define('TRAINING_CERTIFICATE_THRESHOLD', 80);
}

// Notification settings
if (!defined('NOTIFICATION_TYPES')) {
    define('NOTIFICATION_TYPES', [
        'reservation' => 'Reservation',
        'check_in' => 'Check In',
        'check_out' => 'Check Out',
        'maintenance' => 'Maintenance',
        'housekeeping' => 'Housekeeping',
        'billing' => 'Billing'
    ]);
}

// Dashboard settings
if (!defined('DASHBOARD_REFRESH_INTERVAL')) {
    define('DASHBOARD_REFRESH_INTERVAL', 30); // seconds
}
if (!defined('RECENT_ACTIVITIES_LIMIT')) {
    define('RECENT_ACTIVITIES_LIMIT', 10);
}
if (!defined('LOW_STOCK_THRESHOLD')) {
    define('LOW_STOCK_THRESHOLD', 10);
}

// API settings
if (!defined('API_ENABLED')) {
    define('API_ENABLED', true);
}
if (!defined('API_RATE_LIMIT')) {
    define('API_RATE_LIMIT', 100); // requests per hour
}
if (!defined('API_TIMEOUT')) {
    define('API_TIMEOUT', 30); // seconds
}

// Logging settings
if (!defined('LOG_LEVEL')) {
    define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR
}
if (!defined('LOG_RETENTION_DAYS')) {
    define('LOG_RETENTION_DAYS', 30);
}

// Cache settings
if (!defined('CACHE_ENABLED')) {
    define('CACHE_ENABLED', true);
}
if (!defined('CACHE_TTL')) {
    define('CACHE_TTL', 300); // 5 minutes
}

// Backup settings
if (!defined('BACKUP_ENABLED')) {
    define('BACKUP_ENABLED', true);
}
if (!defined('BACKUP_FREQUENCY')) {
    define('BACKUP_FREQUENCY', 'daily'); // daily, weekly, monthly
}
if (!defined('BACKUP_RETENTION_DAYS')) {
    define('BACKUP_RETENTION_DAYS', 30);
}

// Performance settings
if (!defined('QUERY_TIMEOUT')) {
    define('QUERY_TIMEOUT', 30); // seconds
}
if (!defined('MAX_CONNECTIONS')) {
    define('MAX_CONNECTIONS', 100);
}
if (!defined('CONNECTION_TIMEOUT')) {
    define('CONNECTION_TIMEOUT', 10); // seconds
}

// Feature flags
if (!defined('FEATURES')) {
    define('FEATURES', [
        'online_booking' => true,
        'mobile_app' => false,
        'api_integration' => true,
        'advanced_reporting' => true,
        'multi_language' => false,
        'social_login' => false,
        'two_factor_auth' => false,
        'audit_trail' => true,
        'real_time_notifications' => true,
        'automated_backup' => true
    ]);
}

// Error reporting
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('Asia/Manila');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS
ini_set('session.gc_maxlifetime', SESSION_TIMEOUT);

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auto-load classes
spl_autoload_register(function ($class_name) {
    $class_file = __DIR__ . '/classes/' . $class_name . '.php';
    if (file_exists($class_file)) {
        require_once $class_file;
    }
});

// Include common functions
require_once __DIR__ . '/functions.php';

// Initialize booking database
if (class_exists('BookingDatabase')) {
    $booking_db = new BookingDatabase();
}

// Set default timezone for the application
date_default_timezone_set('Asia/Manila');
?>
