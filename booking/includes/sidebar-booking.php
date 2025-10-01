<?php
// Dedicated Booking System Sidebar
// This sidebar is specifically for the booking system and does not include POS or Inventory

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_role = $_SESSION['user_role'];
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

// Booking System Navigation Items
$navigation_items = [
    'dashboard' => [
        'url' => '/pms/booking/index.php',
        'icon' => 'fas fa-tachometer-alt',
        'label' => 'Booking Dashboard',
        'roles' => ['manager', 'front_desk', 'housekeeping', 'student'],
        'active' => isActiveUrl('/pms/booking/index.php', $current_url)
    ],
    'reservations' => [
        'url' => '/pms/booking/modules/front-desk/manage-reservations.php',
        'icon' => 'fas fa-calendar-check',
        'label' => 'Reservations',
        'roles' => ['manager', 'front_desk', 'student'],
        'active' => strpos($current_url, 'manage-reservations') !== false
    ],
    'check_in' => [
        'url' => '/pms/booking/modules/front-desk/check-in.php',
        'icon' => 'fas fa-sign-in-alt',
        'label' => 'Check In',
        'roles' => ['manager', 'front_desk', 'student'],
        'active' => strpos($current_url, 'check-in') !== false
    ],
    'check_out' => [
        'url' => '/pms/booking/modules/front-desk/check-out.php',
        'icon' => 'fas fa-sign-out-alt',
        'label' => 'Check Out',
        'roles' => ['manager', 'front_desk', 'student'],
        'active' => strpos($current_url, 'check-out') !== false
    ],
    'guest_management' => [
        'url' => '/pms/booking/modules/front-desk/guest-management.php',
        'icon' => 'fas fa-users',
        'label' => 'Guest Management',
        'roles' => ['manager', 'front_desk', 'student'],
        'active' => strpos($current_url, 'guest-management') !== false
    ],
    'room_status' => [
        'url' => '/pms/booking/modules/housekeeping/room-status.php',
        'icon' => 'fas fa-bed',
        'label' => 'Room Status',
        'roles' => ['manager', 'housekeeping', 'student'],
        'active' => strpos($current_url, 'room-status') !== false
    ],
    'billing' => [
        'url' => '/pms/booking/modules/front-desk/billing-payment.php',
        'icon' => 'fas fa-credit-card',
        'label' => 'Billing & Payments',
        'roles' => ['manager', 'front_desk', 'student'],
        'active' => strpos($current_url, 'billing-payment') !== false
    ],
    'reports' => [
        'url' => '/pms/booking/modules/management/reports-dashboard.php',
        'icon' => 'fas fa-chart-line',
        'label' => 'Reports',
        'roles' => ['manager', 'student'],
        'active' => strpos($current_url, 'reports') !== false
    ],
    'training' => [
        'url' => '/pms/booking/modules/training/training-dashboard.php',
        'icon' => 'fas fa-graduation-cap',
        'label' => 'Training',
        'roles' => ['manager', 'front_desk', 'housekeeping', 'student'],
        'active' => strpos($current_url, 'training') !== false
    ]
];

// Filter navigation items based on user role
$user_navigation = array_filter($navigation_items, function($item) use ($user_role) {
    return in_array($user_role, $item['roles']);
});
?>

<!-- Booking System Sidebar -->
<nav id="sidebar" class="fixed left-0 top-16 w-64 h-[calc(100vh-4rem)] bg-white shadow-lg overflow-y-auto z-40 transform -translate-x-full lg:translate-x-0 transition-transform duration-300">
    <div class="p-4 border-b border-gray-200">
        <div class="flex items-center">
            <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center mr-3">
                <i class="fas fa-bed text-white text-sm"></i>
            </div>
            <div>
                <div class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                <div class="text-xs text-gray-500"><?php echo ucfirst(str_replace('_', ' ', $user_role)); ?></div>
            </div>
        </div>
    </div>
    
    <div class="p-4 border-b border-gray-200">
        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Booking System</h3>
    </div>
    
    <ul class="py-4">
        <?php foreach ($user_navigation as $key => $item): ?>
            <li class="mb-1">
                <a href="<?php echo $item['url']; ?>" 
                   class="flex items-center px-6 py-3 text-gray-600 hover:text-blue-600 hover:bg-blue-50 border-l-4 <?php echo $item['active'] ? 'border-blue-500 bg-blue-50 text-blue-600' : 'border-transparent'; ?> transition-colors">
                    <i class="<?php echo $item['icon']; ?> w-5 mr-3"></i>
                    <span><?php echo $item['label']; ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    
    <!-- Quick Actions Section -->
    <div class="p-4 border-t border-gray-200">
        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Quick Actions</h3>
        <div class="space-y-2">
            <a href="/pms/booking/modules/front-desk/new-reservation.php" 
               class="flex items-center px-3 py-2 text-sm text-green-600 hover:text-green-700 hover:bg-green-50 rounded transition-colors">
                <i class="fas fa-plus-circle mr-2"></i>
                New Reservation
            </a>
            <a href="/pms/booking/modules/front-desk/room-status.php" 
               class="flex items-center px-3 py-2 text-sm text-orange-600 hover:text-orange-700 hover:bg-orange-50 rounded transition-colors">
                <i class="fas fa-eye mr-2"></i>
                View Rooms
            </a>
        </div>
    </div>
    
</nav>

<!-- Sidebar Overlay for Mobile -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden" onclick="closeSidebar()"></div>
