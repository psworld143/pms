
<?php
/**
 * Unified Database Configuration for PMS System
 * This file provides database connection for all PMS modules:
 * - Booking System
 * - Inventory Management
 * - POS System
 * - Any future modules
 */

// Local override (optional): create includes/database.local.php to define DB_* constants
if (file_exists(__DIR__ . '/database.local.php')) {
    include __DIR__ . '/database.local.php';
    // Debug: Check if constants are defined after include
    error_log("database.local.php included. DB_USER defined: " . (defined('DB_USER') ? 'YES' : 'NO'));
    if (defined('DB_USER')) {
        error_log("DB_USER value: '" . DB_USER . "'");
    }
} else {
    error_log("database.local.php not found at: " . __DIR__ . '/database.local.php');
}

// CyberPanel/Hostinger specific configuration
if (file_exists(__DIR__ . '/../booking/config/cyberpanel-config.php')) {
    include __DIR__ . '/../booking/config/cyberpanel-config.php';
}

// Database configuration (supports env overrides & conditional defines)
if (!defined('DB_HOST')) { define('DB_HOST', getenv('PMS_DB_HOST') ?: 'localhost'); }
if (!defined('DB_NAME')) { define('DB_NAME', getenv('PMS_DB_NAME') ?: 'pms_hotel'); }
if (!defined('DB_USER')) { define('DB_USER', getenv('PMS_DB_USER') ?: 'pms_hotel'); }
if (!defined('DB_PASS')) { define('DB_PASS', getenv('PMS_DB_PASS') ?: '020894HotelPMS'); }
// Optional overrides
if (!defined('DB_PORT')) { $envPort = getenv('PMS_DB_PORT'); define('DB_PORT', $envPort !== false && $envPort !== '' ? (int)$envPort : 3306); }
if (!defined('DB_SOCKET')) { define('DB_SOCKET', getenv('PMS_DB_SOCKET') ?: ''); }

// Application configuration
define('SITE_NAME', 'Hotel PMS Training System');
define('TIMEZONE', 'Asia/Manila');

// Set timezone
date_default_timezone_set(TIMEZONE);

// Database connection (simplified for VPS hosting)
try {
    // Preflight: ensure PDO MySQL driver is available
    if (!class_exists('PDO') || !in_array('mysql', PDO::getAvailableDrivers(), true)) {
        error_log('Database error: pdo_mysql driver is not available. Enable the PDO MySQL extension in php.ini.');
        die('Database connection failed. Please check your configuration.');
    }

    $pdo = null;
    $CONNECT_TIMEOUT = 5; // seconds (slightly longer for VPS)
    $pdoOptions = [
        PDO::ATTR_TIMEOUT => $CONNECT_TIMEOUT,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];

    // Use local.php configuration if available, otherwise fall back to environment variables
    $dbHost = DB_HOST ?: 'localhost';
    $dbPort = DB_PORT ?: 3306;
    $dbUser = DB_USER ?: 'root';
    $dbPass = DB_PASS ?: '';
    $dbName = DB_NAME ?: 'pms_hotel';

    // Debug: Log actual values being used
    error_log("Database connection attempt - Host: $dbHost, User: $dbUser, DB: $dbName, Pass: " . (empty($dbPass) ? 'EMPTY' : 'SET'));

    try {
        // Primary connection attempt with configured credentials
        $pdo = new PDO(
            "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4",
            $dbUser,
            $dbPass,
            $pdoOptions
        );

        // Set additional connection attributes
        $pdo->exec("SET NAMES utf8mb4");
        $pdo->exec("SET time_zone = '" . date('P') . "'");

    } catch(PDOException $e) {
        // If primary connection fails, try connecting without database name first
        try {
            $serverPdo = new PDO(
                "mysql:host={$dbHost};port={$dbPort};charset=utf8mb4",
                $dbUser,
                $dbPass,
                [PDO::ATTR_TIMEOUT => $CONNECT_TIMEOUT, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            // Check if database exists
            $databases = $serverPdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);

            if (in_array($dbName, $databases)) {
                // Database exists, connect to it
                $pdo = new PDO(
                    "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4",
                    $dbUser,
                    $dbPass,
                    $pdoOptions
                );
                $pdo->exec("SET NAMES utf8mb4");
                $pdo->exec("SET time_zone = '" . date('P') . "'");
            } else {
                // Create database if it doesn't exist
                $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
                $pdo = new PDO(
                    "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4",
                    $dbUser,
                    $dbPass,
                    $pdoOptions
                );
            }

            $serverPdo = null; // Close temporary connection

        } catch(PDOException $e2) {
            error_log("Database connection failed: " . $e2->getMessage());
            $errorMsg = "Database connection failed.\n\n";
            $errorMsg .= "Debug Information:\n";
            $errorMsg .= "- Host: " . $dbHost . "\n";
            $errorMsg .= "- Port: " . $dbPort . "\n";
            $errorMsg .= "- Database: " . $dbName . "\n";
            $errorMsg .= "- User: " . $dbUser . "\n";
            $errorMsg .= "- PDO Error: " . $e2->getMessage() . "\n\n";
            $errorMsg .= "Troubleshooting:\n";
            $errorMsg .= "1. Check your database credentials in includes/database.local.php\n";
            $errorMsg .= "2. Verify MySQL server is running on your VPS\n";
            $errorMsg .= "3. Ensure database user exists and has correct permissions\n";
            $errorMsg .= "4. Check VPS firewall settings\n";
            $errorMsg .= "5. Test connection: <a href='/test_db.php' target='_blank'>Run Database Test</a>\n";
            die($errorMsg);
        }
    }

} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please check your configuration.");
}

// Error reporting
error_reporting(E_ALL);

// Logs directory creation removed to avoid permission issues

// Function to get database connection (useful for modules that need to ensure connection)
function getDatabaseConnection() {
    global $pdo;
    return $pdo;
}

// Function to check if database is connected
function isDatabaseConnected() {
    global $pdo;
    try {
        $pdo->query('SELECT 1');
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Function to close database connection
function closeDatabaseConnection() {
    global $pdo;
    $pdo = null;
}
 
?>
