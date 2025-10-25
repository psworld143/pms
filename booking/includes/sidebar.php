<?php
// Sidebar component for Hotel PMS
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_role = $_SESSION['user_role'];
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
        return '/pms/booking/';
    }
    function booking_url($relative = '') {
        return rtrim(booking_base(), '/') . '/' . ltrim($relative, '/');
    }
}

// Define navigation items based on user role
$navigation_items = [
    'dashboard' => [
        'url' => booking_url('index.php'),
        'icon' => 'fas fa-tachometer-alt',
        'label' => 'Dashboard',
        'roles' => ['manager', 'front_desk', 'housekeeping']
    ],
    'front_desk' => [
        'url' => booking_url('modules/front-desk/'),
        'icon' => 'fas fa-concierge-bell',
        'label' => 'Front Desk',
        'roles' => ['manager', 'front_desk'],
        'submenu' => [
            'reservations' => ['url' => booking_url('modules/front-desk/manage-reservations.php'), 'label' => 'Reservations'],
            'check_in' => ['url' => booking_url('modules/front-desk/check-in.php'), 'label' => 'Check In'],
            'check_out' => ['url' => booking_url('modules/front-desk/check-out.php'), 'label' => 'Check Out'],
            'walk_ins' => ['url' => booking_url('modules/front-desk/walk-ins.php'), 'label' => 'Walk-ins'],
            'guest_services' => ['url' => booking_url('modules/front-desk/guest-services.php'), 'label' => 'Guest Services']
        ]
    ],
    'housekeeping' => [
        'url' => booking_url('modules/housekeeping/'),
        'icon' => 'fas fa-broom',
        'label' => 'Housekeeping',
        'roles' => ['manager', 'front_desk'],
        'submenu' => [
            'room_status' => ['url' => booking_url('modules/housekeeping/room-status.php'), 'label' => 'Room Status'],
            'tasks' => ['url' => booking_url('modules/housekeeping/tasks.php'), 'label' => 'Tasks'],
            'maintenance' => ['url' => booking_url('modules/housekeeping/maintenance.php'), 'label' => 'Maintenance']
        ]
    ],
    'guests' => [
        'url' => booking_url('modules/guests/'),
        'icon' => 'fas fa-users',
        'label' => 'Guest Management',
        'roles' => ['manager', 'front_desk'],
        'submenu' => [
            'profiles' => ['url' => booking_url('modules/guests/profiles.php'), 'label' => 'Guest Profiles'],
            'vip' => ['url' => booking_url('modules/guests/vip-management.php'), 'label' => 'VIP Guests'],
            'feedback' => ['url' => booking_url('modules/guests/feedback.php'), 'label' => 'Feedback'],
            'loyalty' => ['url' => booking_url('modules/guests/loyalty.php'), 'label' => 'Loyalty Program']
        ]
    ],
    'billing' => [
        'url' => booking_url('modules/billing/'),
        'icon' => 'fas fa-credit-card',
        'label' => 'Billing & Payments',
        'roles' => ['manager', 'front_desk'],
        'submenu' => [
            'invoices' => ['url' => booking_url('modules/billing/invoices.php'), 'label' => 'Invoices'],
            'payments' => ['url' => booking_url('modules/billing/payments.php'), 'label' => 'Payments'],
            'discounts' => ['url' => booking_url('modules/billing/discounts.php'), 'label' => 'Discounts'],
            'vouchers' => ['url' => booking_url('modules/billing/vouchers.php'), 'label' => 'Vouchers'],
            'reports' => ['url' => booking_url('modules/billing/reports.php'), 'label' => 'Reports']
        ]
    ],
    'management' => [
        'url' => booking_url('modules/management/'),
        'icon' => 'fas fa-chart-line',
        'label' => 'Management',
        'roles' => ['manager'],
        'submenu' => [
            'reports' => ['url' => booking_url('modules/management/reports-dashboard.php'), 'label' => 'Reports'],
            'analytics' => ['url' => booking_url('modules/management/analytics.php'), 'label' => 'Analytics Dashboard'],
            'financial' => ['url' => booking_url('modules/management/financial-dashboard.php'), 'label' => 'Financial Dashboard'],
            'guest_communication' => ['url' => booking_url('modules/management/guest-communication.php'), 'label' => 'Guest Communication'],
            'maintenance_management' => ['url' => booking_url('modules/management/maintenance-management.php'), 'label' => 'Maintenance Management'],
            'staff_scheduling' => ['url' => booking_url('modules/management/staff-scheduling.php'), 'label' => 'Staff Scheduling'],
            'room_management' => ['url' => booking_url('modules/management/room-management.php'), 'label' => 'Room Management'],
            'user_management' => ['url' => booking_url('modules/management/user-management.php'), 'label' => 'User Management'],
            'staff' => ['url' => booking_url('modules/management/staff.php'), 'label' => 'Staff'],
            'settings' => ['url' => booking_url('modules/management/settings.php'), 'label' => 'Settings']
        ]
    ],
    'training' => [
        'url' => booking_url('modules/training/'),
        'icon' => 'fas fa-graduation-cap',
        'label' => 'Training & Simulations',
        'roles' => ['manager', 'front_desk', 'housekeeping'],
        'submenu' => [
            'dashboard' => ['url' => booking_url('modules/training/training-dashboard.php'), 'label' => 'Training Dashboard'],
            'scenarios' => ['url' => booking_url('modules/training/scenarios.php'), 'label' => 'Scenarios'],
            'progress' => ['url' => booking_url('modules/training/progress.php'), 'label' => 'My Progress'],
            'certificates' => ['url' => booking_url('modules/training/certificates.php'), 'label' => 'Certificates']
        ]
    ]
];

// Filter navigation items based on user role
$user_navigation = array_filter($navigation_items, function($item) use ($user_role) {
    return in_array($user_role, $item['roles']);
});
?>

<!-- Sidebar -->
<nav id="sidebar" class="fixed left-0 top-16 w-64 sm:w-72 lg:w-64 h-[calc(100vh-4rem)] bg-white shadow-lg overflow-y-auto z-50">
    <div class="p-4 border-b border-gray-200">
        <div class="flex items-center">
            <div class="w-8 h-8 bg-gradient-to-r from-primary to-secondary rounded-full flex items-center justify-center mr-3">
                <i class="fas fa-user text-white text-sm"></i>
            </div>
            <div>
                <div class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                <div class="text-xs text-gray-500"><?php echo ucfirst(str_replace('_', ' ', $user_role)); ?></div>
            </div>
        </div>
    </div>
    
    <ul class="py-4">
        <?php foreach ($user_navigation as $key => $item): ?>
            <li class="mb-1">
                <?php if (isset($item['submenu'])): ?>
                    <!-- Menu item with submenu -->
                    <button class="w-full flex items-center justify-between px-6 py-3 text-gray-600 hover:text-primary hover:bg-gray-50 border-l-4 border-transparent hover:border-primary transition-colors" 
                            onclick="toggleSubmenu('<?php echo $key; ?>')">
                        <div class="flex items-center">
                            <i class="<?php echo $item['icon']; ?> w-5 mr-3"></i>
                            <span><?php echo $item['label']; ?></span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform" id="chevron-<?php echo $key; ?>"></i>
                    </button>
                    <ul id="submenu-<?php echo $key; ?>" class="hidden bg-gray-50">
                        <?php foreach ($item['submenu'] as $subkey => $subitem): ?>
                            <li>
                                <a href="<?php echo $subitem['url']; ?>" 
                                   class="flex items-center px-6 py-2 text-sm text-gray-600 hover:text-primary hover:bg-gray-100 pl-12">
                                    <i class="fas fa-circle text-xs mr-3"></i>
                                    <?php echo $subitem['label']; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <!-- Simple menu item -->
                    <a href="<?php echo $item['url']; ?>" 
                       class="flex items-center px-6 py-3 text-gray-600 hover:text-primary hover:bg-gray-50 border-l-4 border-transparent hover:border-primary transition-colors <?php echo ($current_page === $key || ($key === 'dashboard' && $current_page === 'index')) ? 'text-primary bg-blue-50 border-primary' : ''; ?>">
                        <i class="<?php echo $item['icon']; ?> w-5 mr-3"></i>
                        <?php echo $item['label']; ?>
                    </a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
    
    <!-- Quick Actions Section -->
    <div class="p-4 border-t border-gray-200">
        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Quick Actions</h3>
        <div class="space-y-2">
            <?php if (in_array($user_role, ['manager', 'front_desk'])): ?>
                <a href="<?php echo booking_url('modules/front-desk/new-reservation.php'); ?>" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-primary hover:bg-gray-50 rounded transition-colors">
                    <i class="fas fa-plus text-xs mr-2"></i>
                    New Reservation
                </a>
            <?php endif; ?>
            
            <?php if (in_array($user_role, ['manager', 'housekeeping'])): ?>
                <a href="<?php echo booking_url('modules/housekeeping/room-status.php'); ?>" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-primary hover:bg-gray-50 rounded transition-colors">
                    <i class="fas fa-clipboard-list text-xs mr-2"></i>
                    Room Status
                </a>
            <?php endif; ?>
            
            <a href="<?php echo booking_url('modules/training/training-dashboard.php'); ?>" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-primary hover:bg-gray-50 rounded transition-colors">
                <i class="fas fa-play text-xs mr-2"></i>
                Start Training
            </a>
        </div>
    </div>
</nav>

<!-- Mobile overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden hidden" onclick="closeSidebar()"></div>
