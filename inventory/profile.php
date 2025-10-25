<?php
/**
 * Manager Profile Page
 * Hotel PMS Inventory Management System
 */

require_once __DIR__ . '/../vps_session_fix.php';
require_once __DIR__ . '/../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if user has appropriate role (only manager)
$user_role = $_SESSION['user_role'] ?? '';
if ($user_role !== 'manager') {
    header('Location: index.php?error=access_denied');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Manager';
$user_email = $_SESSION['user_email'] ?? '';

// Set page title
$page_title = 'Manager Profile';

// Handle form submissions
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $new_name = trim($_POST['name'] ?? '');
        $new_email = trim($_POST['email'] ?? '');
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        try {
            // Validate inputs
            if (empty($new_name)) {
                throw new Exception('Name is required');
            }
            
            if (empty($new_email)) {
                throw new Exception('Email is required');
            }
            
            if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email format');
            }
            
            // Check if password change is requested
            if (!empty($new_password)) {
                if (empty($current_password)) {
                    throw new Exception('Current password is required to change password');
                }
                
                if ($new_password !== $confirm_password) {
                    throw new Exception('New passwords do not match');
                }
                
                if (strlen($new_password) < 6) {
                    throw new Exception('New password must be at least 6 characters long');
                }
                
                // Verify current password
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
                
                if (!$user || !password_verify($current_password, $user['password'])) {
                    throw new Exception('Current password is incorrect');
                }
                
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $user_id]);
            }
            
            // Update profile information
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $stmt->execute([$new_name, $new_email, $user_id]);
            
            // Update session
            $_SESSION['user_name'] = $new_name;
            $_SESSION['user_email'] = $new_email;
            
            $success_message = 'Profile updated successfully!';
            
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
    }
}

// Get user statistics
try {
    // Get inventory statistics
    $inventory_stats = [];
    
    // Total items
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM inventory_items");
    $inventory_stats['total_items'] = $stmt->fetchColumn();
    
    // Low stock items
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM inventory_items WHERE current_stock <= reorder_level");
    $inventory_stats['low_stock_items'] = $stmt->fetchColumn();
    
    // Total transactions this month
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM inventory_transactions WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
    $inventory_stats['monthly_transactions'] = $stmt->fetchColumn();
    
    // Pending requests
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM inventory_requests WHERE status = 'pending'");
    $inventory_stats['pending_requests'] = $stmt->fetchColumn();
    
} catch (Exception $e) {
    $inventory_stats = [
        'total_items' => 0,
        'low_stock_items' => 0,
        'monthly_transactions' => 0,
        'pending_requests' => 0
    ];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Hotel Inventory System</title>
    <link rel="icon" type="image/png" href="../../assets/images/seait-logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        #sidebar { transition: transform 0.3s ease-in-out; }
        @media (max-width: 1023px) { #sidebar { transform: translateX(-100%); z-index: 50; } #sidebar.sidebar-open { transform: translateX(0); } }
        @media (min-width: 1024px) { #sidebar { transform: translateX(0) !important; } }
        #sidebar-overlay { transition: opacity 0.3s ease-in-out; z-index: 40; }
        .main-content { margin-left: 0; padding-top: 4rem; }
        @media (min-width: 1024px) { .main-content { margin-left: 16rem; } }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#10B981',
                        secondary: '#059669',
                        success: '#28a745',
                        danger: '#dc3545',
                        warning: '#ffc107',
                        info: '#17a2b8'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <!-- Sidebar Overlay for Mobile -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden" onclick="closeSidebar()"></div>
        
        <!-- Include unified inventory header and sidebar -->
        <?php include 'includes/inventory-header.php'; ?>
        <?php include 'includes/sidebar-inventory.php'; ?>

        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
                <div>
                    <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Manager Profile</h2>
                    <p class="text-gray-600 mt-1">Manage your account settings and view statistics</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="window.history.back()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-arrow-left mr-2"></i>Back
                    </button>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <?php if ($success_message): ?>
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Profile Information -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-6">Profile Information</h3>
                        
                        <form method="POST" class="space-y-6">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                                    <input type="text" name="name" value="<?php echo htmlspecialchars($user_name); ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" required>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                                    <input type="email" name="email" value="<?php echo htmlspecialchars($user_email); ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" required>
                                </div>
                            </div>
                            
                            <div class="border-t pt-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4">Change Password</h4>
                                <p class="text-sm text-gray-600 mb-4">Leave password fields empty if you don't want to change your password.</p>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                                        <input type="password" name="current_password" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                                            <input type="password" name="new_password" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                                            <input type="password" name="confirm_password" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex justify-end pt-6">
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                    <i class="fas fa-save mr-2"></i>Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Statistics and Info -->
                <div class="space-y-6">
                    <!-- User Info Card -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Account Information</h3>
                        
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-green-600"></i>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-500">Role</p>
                                    <p class="text-sm text-gray-900">Manager</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-calendar text-blue-600"></i>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-500">Member Since</p>
                                    <p class="text-sm text-gray-900"><?php echo date('M Y'); ?></p>
                                </div>
                            </div>
                            
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-shield-alt text-purple-600"></i>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-500">Access Level</p>
                                    <p class="text-sm text-gray-900">Full Access</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory Statistics -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Inventory Statistics</h3>
                        
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center mr-3">
                                        <i class="fas fa-box text-blue-600"></i>
                                    </div>
                                    <span class="text-sm text-gray-600">Total Items</span>
                                </div>
                                <span class="text-lg font-semibold text-gray-900"><?php echo number_format($inventory_stats['total_items']); ?></span>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center mr-3">
                                        <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                                    </div>
                                    <span class="text-sm text-gray-600">Low Stock Items</span>
                                </div>
                                <span class="text-lg font-semibold text-gray-900"><?php echo number_format($inventory_stats['low_stock_items']); ?></span>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center mr-3">
                                        <i class="fas fa-exchange-alt text-green-600"></i>
                                    </div>
                                    <span class="text-sm text-gray-600">Monthly Transactions</span>
                                </div>
                                <span class="text-lg font-semibold text-gray-900"><?php echo number_format($inventory_stats['monthly_transactions']); ?></span>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-orange-100 rounded-md flex items-center justify-center mr-3">
                                        <i class="fas fa-clock text-orange-600"></i>
                                    </div>
                                    <span class="text-sm text-gray-600">Pending Requests</span>
                                </div>
                                <span class="text-lg font-semibold text-gray-900"><?php echo number_format($inventory_stats['pending_requests']); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
                        
                        <div class="space-y-3">
                            <a href="index.php" class="flex items-center p-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                                <i class="fas fa-tachometer-alt mr-3 text-green-600"></i>
                                <span class="text-sm">Dashboard</span>
                            </a>
                            
                            <a href="reports.php" class="flex items-center p-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                                <i class="fas fa-chart-bar mr-3 text-blue-600"></i>
                                <span class="text-sm">Generate Reports</span>
                            </a>
                            
                            <a href="accounting-integration.php" class="flex items-center p-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                                <i class="fas fa-calculator mr-3 text-purple-600"></i>
                                <span class="text-sm">Accounting Module</span>
                            </a>
                            
                            <a href="logout.php" class="flex items-center p-3 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                <i class="fas fa-sign-out-alt mr-3"></i>
                                <span class="text-sm">Sign Out</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Form validation
        $(document).ready(function() {
            $('form').on('submit', function(e) {
                const newPassword = $('input[name="new_password"]').val();
                const confirmPassword = $('input[name="confirm_password"]').val();
                const currentPassword = $('input[name="current_password"]').val();
                
                // If new password is provided, validate
                if (newPassword) {
                    if (!currentPassword) {
                        e.preventDefault();
                        alert('Please enter your current password to change your password.');
                        return false;
                    }
                    
                    if (newPassword !== confirmPassword) {
                        e.preventDefault();
                        alert('New passwords do not match.');
                        return false;
                    }
                    
                    if (newPassword.length < 6) {
                        e.preventDefault();
                        alert('New password must be at least 6 characters long.');
                        return false;
                    }
                }
            });
        });
    </script>
</body>
</html>
