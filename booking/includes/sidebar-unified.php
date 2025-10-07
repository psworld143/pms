<?php
require_once __DIR__ . '/booking-paths.php';

booking_initialize_paths();

// Unified sidebar component that automatically selects the appropriate sidebar
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . booking_base() . 'login.php');
    exit();
}

$user_role = $_SESSION['user_role'];

// Include the appropriate sidebar based on user role
switch ($user_role) {
    case 'manager':
        include booking_base_dir() . 'includes/sidebar-manager.php';
        break;
    case 'front_desk':
        include booking_base_dir() . 'includes/sidebar-frontdesk.php';
        break;
    case 'housekeeping':
        include booking_base_dir() . 'includes/sidebar-housekeeping.php';
        break;
    default:
        // Fallback to generic sidebar
        include booking_base_dir() . 'includes/sidebar.php';
        break;
}
?>
