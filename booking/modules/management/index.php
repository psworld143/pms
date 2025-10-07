<?php
require_once dirname(__DIR__, 3) . '/vps_session_fix.php';
require_once '../../config/database.php';
require_once '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';

// Ensure booking paths are available for redirects
booking_initialize_paths();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    header('Location: ' . booking_base() . 'login.php');
    exit();
}

// Redirect to reports dashboard
header('Location: reports-dashboard.php');
exit();
?>
