<?php
// Inventory-specific header component that matches the POS and Booking system design
// This file should only contain the header/navbar, not the complete HTML structure

// Get user information from session (should be set before including this file)
$user_role = $_SESSION['user_role'] ?? '';
$user_name = $_SESSION['user_name'] ?? 'User';

// Get school logo and abbreviation from database
require_once __DIR__ . '/../../includes/database.php';

// Create database connection variable for compatibility
$conn = $pdo;

// Get school information (with fallback values)
$school_logo = null; // Will be set later if needed
$school_abbreviation = 'Hotel PMS'; // Default abbreviation
?>

<!-- Header/Navbar - Matching POS and Booking systems -->
<header class="fixed top-0 left-0 right-0 h-16 bg-gradient-to-r from-primary to-secondary text-white flex justify-between items-center px-4 sm:px-6 z-50 shadow-lg">
    <div class="flex items-center min-w-0 flex-1">
        <button id="sidebar-toggle" class="lg:hidden mr-3 sm:mr-4 text-white hover:text-gray-200 transition-colors flex-shrink-0">
            <i class="fas fa-bars text-lg sm:text-xl"></i>
        </button>
        <i class="fas fa-boxes text-yellow-400 mr-2 sm:mr-3 text-lg sm:text-xl flex-shrink-0"></i>
        <h1 class="text-sm sm:text-xl font-semibold truncate"><?php echo htmlspecialchars($school_abbreviation); ?> Inventory</h1>
        <span class="ml-2 sm:ml-4 text-xs sm:text-sm bg-white bg-opacity-20 px-2 py-1 rounded-full flex-shrink-0 hidden sm:flex">
            <i class="fas fa-graduation-cap mr-1"></i>Training Mode
        </span>
    </div>
    
    <div class="flex items-center space-x-2 sm:space-x-4 flex-shrink-0">
        <!-- Notifications -->
        <div class="relative">
            <button id="notifications-toggle" class="relative p-2 text-white hover:text-gray-200 transition-colors">
                <i class="fas fa-bell text-base sm:text-lg"></i>
                <span id="notification-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 sm:h-5 sm:w-5 flex items-center justify-center hidden">0</span>
            </button>
            <!-- Notifications dropdown -->
            <div id="notifications-dropdown" class="absolute right-0 mt-2 w-72 sm:w-80 bg-white rounded-lg shadow-xl border border-gray-200 hidden z-50">
                <div class="p-3 sm:p-4 border-b border-gray-200">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-800">Inventory Notifications</h3>
                </div>
                <div id="notifications-list" class="max-h-64 overflow-y-auto">
                    <!-- Notifications will be loaded here -->
                </div>
            </div>
        </div>
        
        <!-- User Menu -->
        <div class="relative">
            <button id="user-menu-toggle" class="flex items-center space-x-1 sm:space-x-2 text-white hover:text-gray-200 transition-colors">
                <div class="w-7 h-7 sm:w-8 sm:h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-user text-xs sm:text-sm"></i>
                </div>
                <span class="hidden sm:block text-sm sm:text-base truncate max-w-24"><?php echo htmlspecialchars($user_name); ?></span>
                <i class="fas fa-chevron-down text-xs sm:text-sm"></i>
            </button>
            
            <!-- User dropdown menu -->
            <div id="user-dropdown" class="absolute right-0 mt-2 w-44 sm:w-48 bg-white rounded-lg shadow-xl border border-gray-200 hidden z-50">
                <div class="p-3 sm:p-4 border-b border-gray-200">
                    <div class="text-sm font-medium text-gray-800 truncate"><?php echo htmlspecialchars($user_name); ?></div>
                    <div class="text-xs sm:text-sm text-gray-500">
                        <i class="fas fa-graduation-cap mr-1"></i>Student Trainee
                    </div>
                </div>
                <div class="py-1 sm:py-2">
                    <?php if ($user_role === 'manager'): ?>
                        <a href="profile.php" class="flex items-center px-3 sm:px-4 py-2 text-xs sm:text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-user-circle mr-2 sm:mr-3 text-sm"></i>
                            Profile
                        </a>
                    <?php else: ?>
                        <a href="../booking/profile.php" class="flex items-center px-3 sm:px-4 py-2 text-xs sm:text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-user-circle mr-2 sm:mr-3 text-sm"></i>
                            Profile
                        </a>
                    <?php endif; ?>
                    <hr class="my-1 sm:my-2">
                    <a href="logout.php" class="flex items-center px-3 sm:px-4 py-2 text-xs sm:text-sm text-red-600 hover:bg-red-50">
                        <i class="fas fa-sign-out-alt mr-2 sm:mr-3 text-sm"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Inline JavaScript for immediate dropdown functionality -->
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
    }
    
    // Sidebar toggle functionality
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('sidebar-open');
            sidebarOverlay.classList.toggle('hidden');
        });
    }
    
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('sidebar-open');
            sidebarOverlay.classList.add('hidden');
        });
    }
});

function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    if (sidebar) sidebar.classList.remove('sidebar-open');
    if (sidebarOverlay) sidebarOverlay.classList.add('hidden');
}
</script>
