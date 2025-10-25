<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
// Redirect to the correct file name
// This file serves as an alias for request.php to handle URL routing
include_once 'request.php';
?>
