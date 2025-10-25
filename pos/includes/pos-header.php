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

// Get school logo and abbreviation from database
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../booking/includes/functions.php';

// Create database connection variable for compatibility
$conn = $pdo;

// Get school information (with fallback values)
$school_logo = get_school_logo($conn);
$school_abbreviation = get_school_abbreviation($conn);
?>

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

<!-- Header/Navbar - Matching booking system exactly -->
<header class="fixed top-0 left-0 right-0 h-16 bg-gradient-to-r from-primary to-secondary text-white flex justify-between items-center px-6 shadow-lg" style="z-index: 1000;">
    <div class="flex items-center">
        <button id="sidebar-toggle" class="lg:hidden mr-4 text-white hover:text-gray-200 transition-colors">
            <i class="fas fa-bars text-xl"></i>
        </button>
        <i class="fas fa-cash-register text-yellow-400 mr-3 text-xl"></i>
        <h1 class="text-xl font-semibold"><?php echo htmlspecialchars($school_abbreviation); ?> Hotel POS</h1>
        <span class="ml-4 text-sm bg-white bg-opacity-20 px-2 py-1 rounded-full">
            <?php if ($is_demo_mode): ?>
                <i class="fas fa-graduation-cap mr-1"></i>Training Mode
            <?php else: ?>
                Point of Sale
            <?php endif; ?>
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
            <div id="notifications-dropdown" class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 hidden" style="z-index: 1001;">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">POS Notifications</h3>
                </div>
                <div id="notifications-list" class="max-h-64 overflow-y-auto">
                    <!-- Notifications will be loaded here -->
                </div>
            </div>
        </div>
        
        <!-- User Menu -->
        <div class="relative">
            <button id="user-menu-toggle" type="button" class="flex items-center space-x-2 text-white hover:text-gray-200 transition-colors cursor-pointer" style="z-index: 60;">
                <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-sm"></i>
                </div>
                <span class="hidden md:block"><?php echo htmlspecialchars($user_name); ?></span>
                <i class="fas fa-chevron-down text-sm"></i>
            </button>
            
            <!-- User dropdown menu -->
            <div id="user-dropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 hidden" style="z-index: 1001;">
                <div class="p-4 border-b border-gray-200">
                    <div class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($user_name); ?></div>
                    <div class="text-sm text-gray-500">
                        <?php if ($is_demo_mode): ?>
                            <i class="fas fa-graduation-cap mr-1"></i>Student Trainee
                        <?php else: ?>
                            <?php echo ucfirst(str_replace('_', ' ', $user_role)); ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="py-2">
                    <?php if (!$is_demo_mode): ?>
                        <a href="../../booking/profile.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-user-circle mr-3"></i>
                            Profile
                        </a>
                        <a href="../../booking/" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-tachometer-alt mr-3"></i>
                            PMS Dashboard
                        </a>
                        <hr class="my-2">
                    <?php endif; ?>
                    <a href="<?php echo $pos_root; ?>logout.php" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                        <i class="fas fa-sign-out-alt mr-3"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Inline JavaScript for immediate dropdown functionality -->
<script>
// User dropdown - immediate execution with multiple event listeners
(function() {
    function initUserDropdown() {
        const userMenuToggle = document.getElementById('user-menu-toggle');
        const userDropdown = document.getElementById('user-dropdown');
        
        if (userMenuToggle && userDropdown) {
            // Remove any existing listeners
            userMenuToggle.onclick = null;
            
            // Add click handler
            userMenuToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('User menu clicked');
                userDropdown.classList.toggle('hidden');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!event.target.closest('#user-menu-toggle') && !event.target.closest('#user-dropdown')) {
                    userDropdown.classList.add('hidden');
                }
            });
            
            console.log('User dropdown initialized');
        }
    }
    
    // Try to initialize immediately
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initUserDropdown);
    } else {
        initUserDropdown();
    }
})();

document.addEventListener('DOMContentLoaded', function() {
    // Re-initialize to ensure it works
    const userMenuToggle = document.getElementById('user-menu-toggle');
    const userDropdown = document.getElementById('user-dropdown');
    
    if (userMenuToggle && userDropdown) {
        userMenuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            userDropdown.classList.toggle('hidden');
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
