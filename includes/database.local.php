<?php
/**
 * Local Database Configuration Override
 * This file contains the actual database credentials for your server
 * 
 * IMPORTANT: 
 * - For ONLINE/VPS server: Use your CyberPanel/cPanel database credentials
 * - For LOCAL XAMPP: Use localhost credentials
 * 
 * To get your online database credentials:
 * 1. Log into CyberPanel/cPanel
 * 2. Go to Databases > MySQL Databases
 * 3. Find your database name, username, and password
 */

// INSTRUCTIONS:
// 1. If this is your ONLINE server (pms.seait.edu.ph), uncomment and fill in the ONLINE section
// 2. If this is your LOCAL XAMPP, uncomment and fill in the LOCAL section
// 3. Delete the section you're NOT using

// ============================================================
// ONLINE SERVER CONFIGURATION (pms.seait.edu.ph)
// ============================================================
// Uncomment these lines and fill in your actual CyberPanel database credentials:

/*
define('DB_HOST', 'localhost');  // Usually 'localhost' for CyberPanel
define('DB_NAME', 'pms_pms_hotel');  // Your database name (check CyberPanel)
define('DB_USER', 'pms_pms_hotel');  // Your database username (check CyberPanel)
define('DB_PASS', 'YOUR_ACTUAL_PASSWORD_HERE');  // Your database password (check CyberPanel)
define('DB_PORT', 3306);  // Usually 3306
*/

// ============================================================
// LOCAL XAMPP CONFIGURATION
// ============================================================
// Uncomment these lines for local development:

/*
define('DB_HOST', 'localhost');
define('DB_NAME', 'pms_hotel');
define('DB_USER', 'root');
define('DB_PASS', '');  // Usually empty for XAMPP
define('DB_PORT', 3306);
*/

// ============================================================
// IMPORTANT NOTES:
// ============================================================
// 1. This file should NEVER be committed to Git for security
// 2. Create separate versions for local and online servers
// 3. Make sure the database user has ALL PRIVILEGES on the database
// 4. Test connection by visiting: http://yoursite.com/test_db_connection.php
?>

