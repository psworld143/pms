<?php
/**
 * Hotel PMS Training System - Main Entry Point
 * Redirects to the appropriate module based on user session or shows module selection
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    // User is logged in, redirect to their appropriate module
    $user_role = $_SESSION['user_role'] ?? 'front_desk';
    
    switch ($user_role) {
        case 'manager':
            header('Location: booking/modules/manager/');
            break;
        case 'housekeeping':
            header('Location: booking/modules/housekeeping/');
            break;
        case 'front_desk':
        default:
            header('Location: booking/');
            break;
    }
    exit();
}

// User is not logged in, show module selection page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel PMS Training System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .module-card {
            transition: all 0.3s ease;
        }
        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-6xl font-bold text-white mb-4">
                <i class="fas fa-hotel mr-4"></i>
                Hotel PMS Training System
            </h1>
            <p class="text-xl text-white opacity-90">
                Comprehensive Property Management System for Hotel Operations
            </p>
        </div>

        <!-- Module Selection -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-6xl mx-auto">
            <!-- Booking System -->
            <div class="module-card bg-white rounded-lg shadow-lg p-8 text-center">
                <div class="text-6xl text-blue-600 mb-4">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-4">Booking System</h3>
                <p class="text-gray-600 mb-6">
                    Manage reservations, check-ins, check-outs, and guest services
                </p>
                <a href="booking/login.php" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Access Booking System
                </a>
            </div>

            <!-- POS System -->
            <div class="module-card bg-white rounded-lg shadow-lg p-8 text-center">
                <div class="text-6xl text-green-600 mb-4">
                    <i class="fas fa-cash-register"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-4">POS System</h3>
                <p class="text-gray-600 mb-6">
                    Point of sale for restaurant, spa, gift shop, and room service
                </p>
                <a href="pos/login.php" class="inline-block bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Access POS System
                </a>
            </div>

            <!-- Inventory Management -->
            <div class="module-card bg-white rounded-lg shadow-lg p-8 text-center">
                <div class="text-6xl text-purple-600 mb-4">
                    <i class="fas fa-boxes"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-4">Inventory Management</h3>
                <p class="text-gray-600 mb-6">
                    Track inventory, manage stock, and handle procurement
                </p>
                <a href="inventory/login.php" class="inline-block bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition-colors">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Access Inventory System
                </a>
            </div>

            <!-- Tutorial System -->
            <div class="module-card bg-white rounded-lg shadow-lg p-8 text-center">
                <div class="text-6xl text-orange-600 mb-4">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-4">Interactive Tutorials</h3>
                <p class="text-gray-600 mb-6">
                    Learn hotel operations through guided tutorials and assessments
                </p>
                <a href="tutorials/index.php" class="inline-block bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-play mr-2"></i>
                    Start Tutorials
                </a>
            </div>
        </div>

        <!-- Features Section -->
        <div class="mt-16 text-center">
            <h2 class="text-3xl font-bold text-white mb-8">System Features</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-4xl mx-auto">
                <div class="bg-white bg-opacity-10 rounded-lg p-6">
                    <i class="fas fa-users text-3xl text-white mb-3"></i>
                    <h4 class="text-white font-semibold mb-2">Multi-Role Access</h4>
                    <p class="text-white opacity-80 text-sm">Manager, Front Desk, Housekeeping roles</p>
                </div>
                <div class="bg-white bg-opacity-10 rounded-lg p-6">
                    <i class="fas fa-chart-line text-3xl text-white mb-3"></i>
                    <h4 class="text-white font-semibold mb-2">Real-time Reports</h4>
                    <p class="text-white opacity-80 text-sm">Live dashboard and analytics</p>
                </div>
                <div class="bg-white bg-opacity-10 rounded-lg p-6">
                    <i class="fas fa-graduation-cap text-3xl text-white mb-3"></i>
                    <h4 class="text-white font-semibold mb-2">Training Mode</h4>
                    <p class="text-white opacity-80 text-sm">Interactive learning scenarios</p>
                </div>
                <div class="bg-white bg-opacity-10 rounded-lg p-6">
                    <i class="fas fa-mobile-alt text-3xl text-white mb-3"></i>
                    <h4 class="text-white font-semibold mb-2">Responsive Design</h4>
                    <p class="text-white opacity-80 text-sm">Works on all devices</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-16 text-center text-white opacity-75">
            <p>&copy; 2024 Hotel PMS Training System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
