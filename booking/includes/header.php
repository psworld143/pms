<?php
// Header component for Hotel PMS
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_role = $_SESSION['user_role'];
$user_name = $_SESSION['user_name'];
$current_page = basename($_SERVER['PHP_SELF'], '.php');

if (!function_exists('booking_url')) {
    function booking_base() {
        $script = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\','/', $_SERVER['SCRIPT_NAME']) : '';
        $path = $script !== '' ? $script : (isset($_SERVER['PHP_SELF']) ? str_replace('\\','/', $_SERVER['PHP_SELF']) : '/');
        $pos = strpos($path, '/booking/');
        if ($pos !== false) {
            return rtrim(substr($path, 0, $pos + strlen('/booking/')), '/') . '/';
        }
        $dir = str_replace('\\','/', dirname($path));
        $guard = 0;
        while ($dir !== '/' && $dir !== '.' && basename($dir) !== 'booking' && $guard < 10) {
            $dir = dirname($dir);
            $guard++;
        }
        if (basename($dir) === 'booking') {
            return rtrim($dir, '/') . '/';
        }
        return '/booking/';
    }
    function booking_url($relative = '') {
        return rtrim(booking_base(), '/') . '/' . ltrim($relative, '/');
    }
}

// Get school logo and abbreviation from database
require_once '../../../config/database.php';
require_once '../includes/functions.php';
$school_logo = get_school_logo($conn);
$school_abbreviation = get_school_abbreviation($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo htmlspecialchars($school_abbreviation); ?> Hotel PMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#667eea',
                        secondary: '#764ba2',
                        success: '#28a745',
                        danger: '#dc3545',
                        warning: '#ffc107',
                        info: '#17a2b8'
                    }
                }
            }
        }
    </script>
    
    <!-- Mobile Sidebar CSS -->
    <style>
        #sidebar {
            transition: transform 0.3s ease-in-out;
        }
        @media (max-width: 1023px) {
            #sidebar {
                transform: translateX(-100%);
                z-index: 50;
            }
            #sidebar.sidebar-open {
                transform: translateX(0);
            }
        }
        @media (min-width: 1024px) {
            #sidebar {
                transform: translateX(0) !important;
            }
        }
        #sidebar-overlay {
            transition: opacity 0.3s ease-in-out;
            z-index: 40;
        }
    </style>
    <?php if (isset($additional_css)): ?>
        <?php echo $additional_css; ?>
    <?php endif; ?>
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <!-- Header/Navbar -->
        <header class="fixed top-0 left-0 right-0 h-16 bg-gradient-to-r from-primary to-secondary text-white flex justify-between items-center px-6 z-50 shadow-lg">
            <div class="flex items-center">
                <button id="sidebar-toggle" class="lg:hidden mr-4 text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <i class="fas fa-hotel text-yellow-400 mr-3 text-xl"></i>
                <h1 class="text-xl font-semibold"><?php echo htmlspecialchars($school_abbreviation); ?> Hotel PMS</h1>
                <span class="ml-4 text-sm bg-white bg-opacity-20 px-2 py-1 rounded-full">
                    Training System
                </span>
            </div>
            
            <div class="flex items-center space-x-4">
                <!-- Notifications -->
                <div class="relative">
                    <button id="notifications-toggle" class="relative p-2 text-white hover:text-gray-200 transition-colors">
                        <i class="fas fa-bell text-lg"></i>
                        <span id="notification-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden">0</span>
                    </button>
                    <!-- Notifications dropdown -->
                    <div id="notifications-dropdown" class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 hidden z-50">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Notifications</h3>
                        </div>
                        <div id="notifications-list" class="max-h-64 overflow-y-auto">
                            <!-- Notifications will be loaded here -->
                        </div>
                    </div>
                </div>
                
                <!-- User Menu -->
                <div class="relative">
                    <button id="user-menu-toggle" class="flex items-center space-x-2 text-white hover:text-gray-200 transition-colors">
                        <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-sm"></i>
                        </div>
                        <span class="hidden md:block"><?php echo htmlspecialchars($user_name); ?></span>
                        <i class="fas fa-chevron-down text-sm"></i>
                    </button>
                    
                    <!-- User dropdown menu -->
                    <div id="user-dropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 hidden z-50">
                        <div class="p-4 border-b border-gray-200">
                            <div class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($user_name); ?></div>
                            <div class="text-sm text-gray-500"><?php echo ucfirst(str_replace('_', ' ', $user_role)); ?></div>
                        </div>
                        <div class="py-2">
                            <a href="<?php echo booking_url('profile.php'); ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user-circle mr-3"></i>
                                Profile
                            </a>
                            <a href="<?php echo booking_url('settings.php'); ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-cog mr-3"></i>
                                Settings
                            </a>
                            <hr class="my-2">
                            <a href="<?php echo booking_url('logout.php'); ?>" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <i class="fas fa-sign-out-alt mr-3"></i>
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Role Badge -->
                <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-sm font-medium hidden lg:block">
                    <?php echo ucfirst(str_replace('_', ' ', $user_role)); ?>
                </span>
            </div>
        </header>
        
        <!-- Sidebar Overlay for Mobile -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden" onclick="closeSidebar()"></div>
        
        <!-- JavaScript for sidebar functionality -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // User dropdown functionality
            const userMenuToggle = document.getElementById('user-menu-toggle');
            const userDropdown = document.getElementById('user-dropdown');
            
            if (userMenuToggle && userDropdown) {
                userMenuToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userDropdown.classList.toggle('hidden');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(event) {
                    if (!event.target.closest('#user-menu-toggle') && !event.target.closest('#user-dropdown')) {
                        userDropdown.classList.add('hidden');
                    }
                });
            }
            
            // Sidebar toggle functionality
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebar-overlay');
            
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Sidebar toggle clicked');
                    sidebar.classList.toggle('sidebar-open');
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.toggle('hidden');
                    }
                });
            }
            
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    console.log('Sidebar overlay clicked');
                    sidebar.classList.remove('sidebar-open');
                    sidebarOverlay.classList.add('hidden');
                });
            }
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth < 1024) {
                    if (!event.target.closest('#sidebar') && !event.target.closest('#sidebar-toggle')) {
                        if (sidebar && sidebar.classList.contains('sidebar-open')) {
                            sidebar.classList.remove('sidebar-open');
                            if (sidebarOverlay) {
                                sidebarOverlay.classList.add('hidden');
                            }
                        }
                    }
                }
            });
            
            // Close sidebar on escape key
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    if (sidebar && sidebar.classList.contains('sidebar-open')) {
                        sidebar.classList.remove('sidebar-open');
                        if (sidebarOverlay) {
                            sidebarOverlay.classList.add('hidden');
                        }
                    }
                }
            });
        });

        // Global functions for sidebar control
        function openSidebar() {
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebar-overlay');
            if (sidebar) {
                sidebar.classList.add('sidebar-open');
                if (sidebarOverlay) {
                    sidebarOverlay.classList.remove('hidden');
                }
            }
        }

        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebar-overlay');
            if (sidebar) {
                sidebar.classList.remove('sidebar-open');
                if (sidebarOverlay) {
                    sidebarOverlay.classList.add('hidden');
                }
            }
        }
        </script>
