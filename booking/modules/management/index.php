<?php
require_once dirname(__DIR__, 3) . '/vps_session_fix.php';
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
