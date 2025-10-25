<?php
// VPS Database Configuration - ACTUAL CREDENTIALS
// Database credentials for VPS hosting (Hostinger/CyberPanel)
// These are your actual database credentials

define('DB_HOST', 'seait.edu.ph'); // Usually 'localhost' for Hostinger

define('DB_NAME', 'pms_pms_hotel'); // Database name
// Common format: username_dbname or similar

define('DB_USER', 'pms_pms_hotel'); // Database username
// This is usually your CyberPanel username or a specific DB user

define('DB_PASS', '020894HotelPMS'); // Database password
// This should match the password you set when creating the database

define('DB_PORT', 3306); // Standard MySQL port for Hostinger

// Optional: If your database uses a different port, uncomment and modify:
// define('DB_PORT', 3306);

// Optional: If you need to use a socket connection, uncomment and modify:
// define('DB_SOCKET', '/var/lib/mysql/mysql.sock');

// These credentials are now set for your VPS database
?>
