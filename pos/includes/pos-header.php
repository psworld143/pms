<?php
// POS-specific header component that matches the exact booking system design
// This file should only contain the header/navbar, not the complete HTML structure

// Get user information from session (should be set before including this file)
$user_role = $_SESSION['pos_user_role'] ?? 'pos_user';
$user_name = $_SESSION['pos_user_name'] ?? 'POS User';
$is_demo_mode = isset($_SESSION['pos_demo_mode']) && $_SESSION['pos_demo_mode'];

// Calculate relative path to POS root from current script
$script_dir = dirname($_SERVER['SCRIPT_NAME']);
$depth = substr_count(str_replace('/pms/pos', '', $script_dir), '/');
$pos_root = $depth > 0 ? str_repeat('../', $depth) : './';

// Helper function for POS URLs (similar to booking_url)
if (!function_exists('pos_url')) {
    function pos_url($path = '') {
        global $pos_root;
        return rtrim($pos_root, '/') . '/' . ltrim($path, '/');
    }
}

// Get school logo and abbreviation from database
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../booking/includes/functions.php';

// Create database connection variable for compatibility
$conn = $pdo;

// Get school information (with fallback values)
$school_logo = get_school_logo($conn);
$school_abbreviation = get_school_abbreviation($conn);
?>

<!-- Sidebar and Layout CSS -->
<style>
    /* Ensure smooth sidebar transitions */
    #sidebar {
        transition: transform 0.3s ease-in-out;
    }
    
    /* Overlay transitions */
    #sidebar-overlay {
        transition: opacity 0.3s ease-in-out;
    }
    
    /* Main content margin for desktop to account for sidebar */
    @media (min-width: 1024px) {
        .main-content {
            margin-left: 16rem; /* 64 * 0.25rem = 16rem for w-64 sidebar */
        }
    }
    
    /* Ensure sidebar is always visible on desktop */
    @media (min-width: 1024px) {
        #sidebar {
            transform: translateX(0) !important;
        }
    }
</style>

<!-- Header/Navbar - Matching booking system exactly -->
<header class="fixed top-0 left-0 right-0 h-16 bg-gradient-to-r from-primary to-secondary text-white flex justify-between items-center px-6 z-50 shadow-lg">
    <div class="flex items-center">
        <button id="sidebar-toggle" class="lg:hidden mr-4 text-white hover:text-gray-200 transition-colors">
            <i class="fas fa-bars text-xl"></i>
        </button>
        <i class="fas fa-cash-register text-yellow-400 mr-3 text-xl"></i>
        <h1 class="text-xl font-semibold"><?php echo htmlspecialchars($school_abbreviation); ?> Hotel POS</h1>
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
                    <a href="../../booking/profile.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-user-circle mr-3"></i>
                        Profile
                    </a>
                    <a href="../../booking/settings.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-cog mr-3"></i>
                        Settings
                    </a>
                    <hr class="my-2">
                    <a href="<?php echo pos_url('logout.php'); ?>" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
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

<!-- JavaScript for header functionality - Matching Booking System -->
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
    
    // Notifications dropdown functionality
    const notificationsToggle = document.getElementById('notifications-toggle');
    const notificationsDropdown = document.getElementById('notifications-dropdown');
    
    if (notificationsToggle && notificationsDropdown) {
        notificationsToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationsDropdown.classList.toggle('hidden');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('#notifications-toggle') && !event.target.closest('#notifications-dropdown')) {
                notificationsDropdown.classList.add('hidden');
            }
        });
    }
});
</script>
