<?php
// Tutorial-specific sidebar component
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // For testing purposes
}

$user_role = $_SESSION['user_role'] ?? 'student';
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$current_url = $_SERVER['REQUEST_URI'];

// Function to check if a URL is active
function isActiveUrl($url, $current_url) {
    $clean_url = strtok($url, '?');
    $clean_current = strtok($current_url, '?');
    
    if ($clean_current === $clean_url) {
        return true;
    }
    
    if (strpos($clean_current, $clean_url) !== false) {
        return true;
    }
    
    return false;
}

// Tutorial navigation items
$navigation_items = [
    'tutorials' => [
        'url' => '/pms/tutorials/index.php',
        'icon' => 'fas fa-graduation-cap',
        'label' => 'Tutorials',
        'active' => isActiveUrl('/pms/tutorials/index.php', $current_url)
    ],
    'mobile_tutorials' => [
        'url' => '/pms/tutorials/mobile.php',
        'icon' => 'fas fa-mobile-alt',
        'label' => 'Mobile Learning',
        'active' => isActiveUrl('/pms/tutorials/mobile.php', $current_url)
    ],
    'assessments' => [
        'url' => '/pms/tutorials/assessment.php',
        'icon' => 'fas fa-clipboard-check',
        'label' => 'Assessments',
        'active' => isActiveUrl('/pms/tutorials/assessment.php', $current_url)
    ],
    'analytics' => [
        'url' => '/pms/tutorials/analytics.php',
        'icon' => 'fas fa-chart-line',
        'label' => 'Analytics',
        'active' => isActiveUrl('/pms/tutorials/analytics.php', $current_url)
    ]
];

// System navigation items (links to other PMS modules)
$system_items = [
    'pos_system' => [
        'url' => '/pms/pos/index.php',
        'icon' => 'fas fa-cash-register',
        'label' => 'POS System',
        'description' => 'Point of Sale Operations'
    ],
    'inventory' => [
        'url' => '/pms/inventory/index.php',
        'icon' => 'fas fa-boxes',
        'label' => 'Inventory',
        'description' => 'Inventory Management'
    ],
    'booking' => [
        'url' => '/pms/booking/index.php',
        'icon' => 'fas fa-calendar-check',
        'label' => 'Booking',
        'description' => 'Reservation System'
    ]
];
?>

<!-- Mobile Sidebar Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden" onclick="toggleSidebar()"></div>

<!-- Mobile Toggle Button is handled by the main page navigation -->

<!-- Sidebar -->
<aside id="sidebar" class="fixed left-0 top-0 w-64 h-full bg-white shadow-lg z-50 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
    <!-- User Info Header -->
    <div class="p-3 border-b border-gray-200 bg-gradient-to-r from-orange-50 to-red-50">
        <div class="flex items-center">
            <div class="w-10 h-10 bg-gradient-to-r from-orange-500 to-red-500 rounded-full flex items-center justify-center mr-3">
                <i class="fas fa-graduation-cap text-white"></i>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Student'); ?></h3>
                <p class="text-xs text-gray-500">Learning Student</p>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="py-4">
        <?php foreach ($navigation_items as $key => $item): ?>
            <a href="<?php echo $item['url']; ?>" 
               class="flex items-center px-6 py-3 text-gray-600 hover:bg-blue-50 hover:text-blue-600 border-l-4 border-transparent hover:border-blue-500 transition-colors <?php echo $item['active'] ? 'bg-blue-50 text-blue-600 border-blue-500' : ''; ?>">
                <i class="<?php echo $item['icon']; ?> w-5 mr-3"></i>
                <span class="font-medium"><?php echo $item['label']; ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- Quick Actions Section -->
    <div class="px-6 py-2">
        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Quick Actions</h4>
        <div class="space-y-1">
            <a href="/pms/tutorials/mobile.php" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:bg-purple-50 hover:text-purple-600 rounded-lg transition-colors">
                <i class="fas fa-mobile-alt w-4 mr-2"></i>
                <span>Mobile Learning</span>
            </a>
            <a href="/pms/tutorials/analytics.php" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:bg-green-50 hover:text-green-600 rounded-lg transition-colors">
                <i class="fas fa-chart-line w-4 mr-2"></i>
                <span>Progress Report</span>
            </a>
            <a href="/pms/tutorials/assessment.php" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:bg-yellow-50 hover:text-yellow-600 rounded-lg transition-colors">
                <i class="fas fa-clipboard-check w-4 mr-2"></i>
                <span>Take Assessment</span>
            </a>
        </div>
    </div>

    <!-- System Integration Section -->
    <div class="px-6 py-2">
        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Practice Systems</h4>
        <div class="space-y-1">
            <?php foreach ($system_items as $key => $item): ?>
                <a href="<?php echo $item['url']; ?>" target="_blank" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:bg-green-50 hover:text-green-600 rounded-lg transition-colors group">
                    <i class="<?php echo $item['icon']; ?> w-4 mr-2"></i>
                    <span><?php echo $item['label']; ?></span>
                    <i class="fas fa-external-link-alt text-xs text-gray-400 group-hover:text-green-600 ml-auto"></i>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Footer -->
    <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200 bg-gray-50">
        <div class="space-y-2">
            <a href="/pms/" class="flex items-center text-sm text-gray-600 hover:text-blue-600 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                <span>Back to PMS</span>
            </a>
            <a href="logout.php" class="flex items-center text-sm text-red-600 hover:text-red-700 transition-colors">
                <i class="fas fa-sign-out-alt mr-2"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
</aside>

<!-- JavaScript is handled by the main page -->