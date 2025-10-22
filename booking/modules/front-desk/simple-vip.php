<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
require_once dirname(__DIR__, 2) . '/../vps_session_fix.php';
require_once dirname(__DIR__, 2) . '/../includes/database.php';
require_once '../../includes/functions.php';
// Check if user is logged in and has front desk role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'front_desk') {
    header('Location: ../../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Set page title
$page_title = 'VIP Guests - Simple Test';

// Include unified navigation (automatically selects based on user role)
include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';
?>

        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">VIP Guests Management</h2>
                
                <!-- Test Navigation -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Test Navigation</h3>
                    <div class="space-y-2">
                        <a href="vip-guests.php" class="block text-blue-600 hover:text-blue-800">
                            <i class="fas fa-crown mr-2"></i>Full VIP Guests Page
                        </a>
                        <a href="feedback.php" class="block text-blue-600 hover:text-blue-800">
                            <i class="fas fa-comment-alt mr-2"></i>Feedback Page
                        </a>
                        <a href="guest-management.php" class="block text-blue-600 hover:text-blue-800">
                            <i class="fas fa-users mr-2"></i>Guest Management
                        </a>
                        <a href="index.php" class="block text-blue-600 hover:text-blue-800">
                            <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                        </a>
                    </div>
                </div>

                <!-- VIP Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-100 rounded-lg">
                                <i class="fas fa-crown text-blue-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total VIP Guests</p>
                                <p class="text-2xl font-bold text-gray-900">24</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                        <div class="flex items-center">
                            <div class="p-3 bg-green-100 rounded-lg">
                                <i class="fas fa-check-circle text-green-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Currently Staying</p>
                                <p class="text-2xl font-bold text-gray-900">8</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                        <div class="flex items-center">
                            <div class="p-3 bg-yellow-100 rounded-lg">
                                <i class="fas fa-clock text-yellow-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Arriving Today</p>
                                <p class="text-2xl font-bold text-gray-900">3</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                        <div class="flex items-center">
                            <div class="p-3 bg-purple-100 rounded-lg">
                                <i class="fas fa-star text-purple-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Special Requests</p>
                                <p class="text-2xl font-bold text-gray-900">12</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Simple VIP Table -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">VIP Guest List</h3>
                    </div>
                    <div class="p-6">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Guest</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Room</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">VIP Level</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center">
                                                <span class="text-white font-medium">JS</span>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">John Smith</div>
                                                <div class="text-sm text-gray-500">john.smith@email.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">Suite 301</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Staying
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <i class="fas fa-crown text-yellow-500 mr-1"></i>
                                            <span class="text-sm text-gray-900">Platinum</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full flex items-center justify-center">
                                                <span class="text-white font-medium">MJ</span>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">Maria Johnson</div>
                                                <div class="text-sm text-gray-500">maria.j@email.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">Room 205</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Arriving
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <i class="fas fa-crown text-yellow-500 mr-1"></i>
                                            <span class="text-sm text-gray-900">Gold</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Debug Info -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Debug Information</h3>
                    <div class="text-sm text-gray-600">
                        <p><strong>User ID:</strong> <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo $user_id; ?></p>
                        <p><strong>User Name:</strong> <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo htmlspecialchars($user_name); ?></p>
                        <p><strong>User Role:</strong> <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo $_SESSION['user_role']; ?></p>
                        <p><strong>Current URL:</strong> <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo $_SERVER['REQUEST_URI']; ?></p>
                        <p><strong>Session Status:</strong> <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive'; ?></p>
                    </div>
                </div>
            </div>
        </main>

    <script src="../../assets/js/main.js"></script>
    
    <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); include '../../includes/footer.php'; ?>