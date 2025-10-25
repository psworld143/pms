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

// Function to check if a URL is active
function isActiveUrl($relativePath, $currentUrl)
{
    $normalizedCurrent = rtrim(parse_url($currentUrl, PHP_URL_PATH), '/');
    $normalizedTarget = rtrim(parse_url(booking_url($relativePath), PHP_URL_PATH), '/');
    return $normalizedCurrent === $normalizedTarget;
}

// Booking System Navigation Items
$navigation_items = [
    'dashboard' => [
        'url' => booking_url('index.php'),
        'icon' => 'fas fa-tachometer-alt',
        'label' => 'Booking Dashboard',
        'roles' => ['manager', 'front_desk', 'housekeeping', 'student'],
        'active' => isActiveUrl('index.php', $current_url)
    ],
    'reservations' => [
        'url' => booking_url('modules/front-desk/manage-reservations.php'),
        'icon' => 'fas fa-calendar-check',
        'label' => 'Reservations',
        'roles' => ['manager', 'front_desk', 'student'],
        'active' => isActiveUrl('modules/front-desk/manage-reservations.php', $current_url)
    ],
    'check_in' => [
        'url' => booking_url('modules/front-desk/check-in.php'),
        'icon' => 'fas fa-sign-in-alt',
        'label' => 'Check In',
        'roles' => ['manager', 'front_desk', 'student'],
        'active' => isActiveUrl('modules/front-desk/check-in.php', $current_url)
    ],
    'check_out' => [
        'url' => booking_url('modules/front-desk/check-out.php'),
        'icon' => 'fas fa-sign-out-alt',
        'label' => 'Check Out',
        'roles' => ['manager', 'front_desk', 'student'],
        'active' => isActiveUrl('modules/front-desk/check-out.php', $current_url)
    ],
    'guest_management' => [
        'url' => booking_url('modules/front-desk/guest-management.php'),
        'icon' => 'fas fa-users',
        'label' => 'Guest Management',
        'roles' => ['manager', 'front_desk', 'student'],
        'active' => isActiveUrl('modules/front-desk/guest-management.php', $current_url)
    ],
    'room_status' => [
        'url' => booking_url('modules/housekeeping/room-status.php'),
        'icon' => 'fas fa-bed',
        'label' => 'Room Status',
        'roles' => ['manager', 'housekeeping', 'student'],
        'active' => isActiveUrl('modules/housekeeping/room-status.php', $current_url)
    ],
    'billing' => [
        'url' => booking_url('modules/front-desk/billing-payment.php'),
        'icon' => 'fas fa-credit-card',
        'label' => 'Billing & Payments',
        'roles' => ['manager', 'front_desk', 'student'],
        'active' => isActiveUrl('modules/front-desk/billing-payment.php', $current_url)
    ],
    'reports' => [
        'url' => booking_url('modules/management/reports-dashboard.php'),
        'icon' => 'fas fa-chart-line',
        'label' => 'Reports',
        'roles' => ['manager', 'student'],
        'active' => isActiveUrl('modules/management/reports-dashboard.php', $current_url)
    ],
    'training' => [
        'url' => booking_url('modules/training/training-dashboard.php'),
        'icon' => 'fas fa-graduation-cap',
        'label' => 'Training',
        'roles' => ['manager', 'front_desk', 'housekeeping', 'student'],
        'active' => isActiveUrl('modules/training/training-dashboard.php', $current_url)
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
            <a href="<?php echo booking_url('modules/front-desk/new-reservation.php'); ?>" 
               class="flex items-center px-3 py-2 text-sm text-green-600 hover:text-green-700 hover:bg-green-50 rounded transition-colors">
                <i class="fas fa-plus-circle mr-2"></i>
                New Reservation
            </a>
            <a href="<?php echo booking_url('modules/housekeeping/room-status.php'); ?>" 
               class="flex items-center px-3 py-2 text-sm text-orange-600 hover:text-orange-700 hover:bg-orange-50 rounded transition-colors">
                <i class="fas fa-eye mr-2"></i>
                View Rooms
            </a>
        </div>
    </div>
</nav>

<!-- Sidebar Overlay for Mobile -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden" onclick="closeSidebar()"></div>
