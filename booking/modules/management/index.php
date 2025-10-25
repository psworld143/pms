<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

booking_initialize_paths();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    header('Location: ' . booking_base() . 'login.php');
    exit();
}

header('Location: ' . booking_url('modules/management/reports-dashboard.php'));
exit();
?>
