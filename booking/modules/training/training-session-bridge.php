<?php
/**
 * Training Session Bridge
 * Allows both Booking and POS users to access training modules
 * This file bridges the session differences between systems
 */

// VPS Session Fix - Robust session configuration
require_once __DIR__ . '/../../../vps_session_fix.php';

// Check if user is logged in from either Booking or POS system
$is_booking_user = isset($_SESSION['user_id']);
$is_pos_user = isset($_SESSION['pos_user_id']);
$is_inventory_user = isset($_SESSION['inventory_user_id']);

if (!$is_booking_user && !$is_pos_user && !$is_inventory_user) {
    // Not logged in to any system - redirect to booking login
    header('Location: ../../login.php');
    exit();
}

// Bridge POS session to booking session for training access
if ($is_pos_user && !$is_booking_user) {
    // Create temporary booking session variables from POS session
    $_SESSION['user_id'] = $_SESSION['pos_user_id'];
    $_SESSION['user_name'] = $_SESSION['pos_user_name'] ?? 'POS User';
    $_SESSION['user_role'] = $_SESSION['pos_user_role'] ?? 'student';
    $_SESSION['from_pos'] = true; // Flag to remember this is from POS
}

// Bridge Inventory session to booking session for training access
if ($is_inventory_user && !$is_booking_user) {
    // Create temporary booking session variables from Inventory session
    $_SESSION['user_id'] = $_SESSION['inventory_user_id'];
    $_SESSION['user_name'] = $_SESSION['inventory_user_name'] ?? 'Inventory User';
    $_SESSION['user_role'] = $_SESSION['inventory_user_role'] ?? 'student';
    $_SESSION['from_inventory'] = true; // Flag to remember this is from Inventory
}

// Now user_id should be set for training pages
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['user_role'];
?>

