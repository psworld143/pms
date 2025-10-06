<?php
/**
 * Unified Database Configuration for PMS System
 * This file provides database connection for all PMS modules:
 * - Booking System
 * - Inventory Management
 * - POS System
 * - Any future modules
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'pms_hotel');
define('DB_USER', 'pms_hotel');
define('DB_PASS', '020894HotelPMS');

// Application configuration
define('SITE_NAME', 'Hotel PMS Training System');
define('TIMEZONE', 'Asia/Manila');

// Set timezone
date_default_timezone_set(TIMEZONE);

// Database connection
try {
    // Preflight: ensure PDO MySQL driver is available
    if (!class_exists('PDO') || !in_array('mysql', PDO::getAvailableDrivers(), true)) {
        error_log('Database error: pdo_mysql driver is not available. Enable the PDO MySQL extension in php.ini.');
        die('Database connection failed. Please check your configuration.');
    }

    $pdo = null;
    $CONNECT_TIMEOUT = 3; // seconds (prevents long page hangs)
    $pdoOptions = [ PDO::ATTR_TIMEOUT => $CONNECT_TIMEOUT ];
    $defaultPort = DB_PORT ?: 3306;

    // Candidate db names (override + canonical names)
    $dbNames = array_values(array_unique([DB_NAME, 'hotel_pms_clean', 'pms_hotel']));

    // Optional socket (macOS XAMPP or user-defined)
    $socket_path = DB_SOCKET ?: '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock';
    if ($pdo === null && $socket_path && file_exists($socket_path)) {
        foreach ($dbNames as $db) {
            try {
                $pdo = new PDO("mysql:unix_socket=$socket_path;dbname={$db};charset=utf8mb4", DB_USER, DB_PASS, $pdoOptions);
                break;
            } catch (PDOException $ignored) { /* try next */ }
        }
    }

    // TCP attempts
    if ($pdo === null) {
        // If user provided explicit overrides (local.php/env), keep attempts minimal
        $hasOverride = file_exists(__DIR__ . '/database.local.php') || getenv('PMS_DB_HOST') || getenv('PMS_DB_PORT') || getenv('PMS_DB_SOCKET');
        $hosts = $hasOverride ? [DB_HOST ?: '127.0.0.1'] : ['127.0.0.1', DB_HOST, 'localhost', '::1'];
        $ports = $hasOverride ? [$defaultPort] : array_values(array_unique([$defaultPort, 3307, 33060]));
        $creds = [[DB_USER, DB_PASS]];
        foreach ($hosts as $host) {
            foreach ($ports as $port) {
                foreach ($creds as $cred) {
                    foreach ($dbNames as $db) {
                        try {
                            $pdo = new PDO("mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4", $cred[0], $cred[1], $pdoOptions);
                            break 4;
                        } catch (PDOException $ignored) { /* try next */ }
                    }
                }
            }
        }
    }

    // As a last resort: connect without DB and detect/create
    if ($pdo === null) {
        $hosts = isset($hosts) ? $hosts : ['127.0.0.1', 'localhost'];
        $ports = isset($ports) ? $ports : [$defaultPort];
        foreach ($hosts as $host) {
            foreach ($ports as $port) {
                try {
                    $serverPdo = new PDO("mysql:host={$host};port={$port}", DB_USER, DB_PASS, $pdoOptions);
                    $serverPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    // Detect candidate db
                    $schemas = $serverPdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
                    $chosen = null;
                    foreach ($dbNames as $cand) {
                        if (in_array($cand, $schemas, true)) { $chosen = $cand; break; }
                    }
                    if ($chosen === null) {
                        // Create default DB_NAME if none exist
                        $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
                        $chosen = DB_NAME;
                    }
                    // Connect to chosen
                    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$chosen};charset=utf8mb4", DB_USER, DB_PASS, $pdoOptions);
                    break 2;
                } catch (PDOException $ignored) { /* try next host */ }
            }
        }
        if ($pdo === null) {
            throw new PDOException('All connection attempts failed');
        }
    }

    $CONNECT_TIMEOUT = 3; // seconds (prevents long page hangs)
    $pdoOptions = [ PDO::ATTR_TIMEOUT => $CONNECT_TIMEOUT ];
    $defaultPort = DB_PORT ?: 3306;

    // Candidate db names (override + canonical names)
    $dbNames = array_values(array_unique([DB_NAME, 'hotel_pms_clean', 'pms_hotel']));

    // Optional socket (macOS XAMPP or user-defined)
    $socket_path = DB_SOCKET ?: '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock';
    if ($pdo === null && $socket_path && file_exists($socket_path)) {
        foreach ($dbNames as $db) {
            try {
                $pdo = new PDO("mysql:unix_socket=$socket_path;dbname={$db};charset=utf8mb4", DB_USER, DB_PASS, $pdoOptions);
                break;
            } catch (PDOException $ignored) { /* try next */ }
        }
    }

    // TCP attempts
    if ($pdo === null) {
        // If user provided explicit overrides (local.php/env), keep attempts minimal
        $hasOverride = file_exists(__DIR__ . '/database.local.php') || getenv('PMS_DB_HOST') || getenv('PMS_DB_PORT') || getenv('PMS_DB_SOCKET');
        $hosts = $hasOverride ? [DB_HOST ?: '127.0.0.1'] : ['127.0.0.1', DB_HOST, 'localhost', '::1'];
        $ports = $hasOverride ? [$defaultPort] : array_values(array_unique([$defaultPort, 3307, 33060]));
        $creds = [[DB_USER, DB_PASS]];
        foreach ($hosts as $host) {
            foreach ($ports as $port) {
                foreach ($creds as $cred) {
                    foreach ($dbNames as $db) {
                        try {
                            $pdo = new PDO("mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4", $cred[0], $cred[1], $pdoOptions);
                            break 4;
                        } catch (PDOException $ignored) { /* try next */ }
                    }
                }
            }
        }
    }

    // As a last resort: connect without DB and detect/create
    if ($pdo === null) {
        $hosts = isset($hosts) ? $hosts : ['127.0.0.1', 'localhost'];
        $ports = isset($ports) ? $ports : [$defaultPort];
        foreach ($hosts as $host) {
            foreach ($ports as $port) {
                try {
                    $serverPdo = new PDO("mysql:host={$host};port={$port}", DB_USER, DB_PASS, $pdoOptions);
                    $serverPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    // Detect candidate db
                    $schemas = $serverPdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
                    $chosen = null;
                    foreach ($dbNames as $cand) {
                        if (in_array($cand, $schemas, true)) { $chosen = $cand; break; }
                    }
                    if ($chosen === null) {
                        // Create default DB_NAME if none exist
                        $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
                        $chosen = DB_NAME;
                    }
                    // Connect to chosen
                    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$chosen};charset=utf8mb4", DB_USER, DB_PASS, $pdoOptions);
                    break 2;
                } catch (PDOException $ignored) { /* try next host */ }
            }
        }
        if ($pdo === null) {
            throw new PDOException('All connection attempts failed');
        }
    }

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Set charset to ensure proper encoding
    $pdo->exec("SET NAMES utf8mb4");
    
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
