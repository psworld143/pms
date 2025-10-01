<?php
// Sidebar component for Hotel PMS
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_role = $_SESSION['user_role'];
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Define navigation items based on user role
$navigation_items = [
    'dashboard' => [
        'url' => '/pms/booking/index.php',
        'icon' => 'fas fa-tachometer-alt',
        'label' => 'Dashboard',
        'roles' => ['manager', 'front_desk', 'housekeeping']
    ],
    'front_desk' => [
        'url' => '/pms/booking/modules/front-desk/',
        'icon' => 'fas fa-concierge-bell',
        'label' => 'Front Desk',
        'roles' => ['manager', 'front_desk'],
        'submenu' => [
            'reservations' => ['url' => '/pms/booking/modules/front-desk/manage-reservations.php', 'label' => 'Reservations'],
            'check_in' => ['url' => '/pms/booking/modules/front-desk/check-in.php', 'label' => 'Check In'],
            'check_out' => ['url' => '/pms/booking/modules/front-desk/check-out.php', 'label' => 'Check Out'],
            'walk_ins' => ['url' => '/pms/booking/modules/front-desk/walk-ins.php', 'label' => 'Walk-ins'],
            'guest_services' => ['url' => '/pms/booking/modules/front-desk/guest-services.php', 'label' => 'Guest Services']
        ]
    ],
    'housekeeping' => [
        'url' => '/pms/booking/modules/housekeeping/',
        'icon' => 'fas fa-broom',
        'label' => 'Housekeeping',
        'roles' => ['manager', 'housekeeping'],
        'submenu' => [
            'room_status' => ['url' => '/pms/booking/modules/housekeeping/room-status.php', 'label' => 'Room Status'],
            'tasks' => ['url' => '/pms/booking/modules/housekeeping/tasks.php', 'label' => 'Tasks'],
            'maintenance' => ['url' => '/pms/booking/modules/housekeeping/maintenance.php', 'label' => 'Maintenance'],
            'inventory' => ['url' => '/pms/booking/modules/housekeeping/inventory.php', 'label' => 'Inventory']
        ]
    ],
    'guests' => [
        'url' => '/pms/booking/modules/guests/',
        'icon' => 'fas fa-users',
        'label' => 'Guest Management',
        'roles' => ['manager', 'front_desk'],
        'submenu' => [
            'profiles' => ['url' => '/pms/booking/modules/guests/profiles.php', 'label' => 'Guest Profiles'],
            'vip' => ['url' => '/pms/booking/modules/guests/vip-management.php', 'label' => 'VIP Guests'],
            'feedback' => ['url' => '/pms/booking/modules/guests/feedback.php', 'label' => 'Feedback'],
            'loyalty' => ['url' => '/pms/booking/modules/guests/loyalty.php', 'label' => 'Loyalty Program']
        ]
    ],
    'billing' => [
        'url' => '/pms/booking/modules/billing/',
        'icon' => 'fas fa-credit-card',
        'label' => 'Billing & Payments',
        'roles' => ['manager', 'front_desk'],
        'submenu' => [
            'invoices' => ['url' => '/pms/booking/modules/billing/invoices.php', 'label' => 'Invoices'],
            'payments' => ['url' => '/pms/booking/modules/billing/payments.php', 'label' => 'Payments'],
            'discounts' => ['url' => '/pms/booking/modules/billing/discounts.php', 'label' => 'Discounts'],
            'vouchers' => ['url' => '/pms/booking/modules/billing/vouchers.php', 'label' => 'Vouchers'],
            'reports' => ['url' => '/pms/booking/modules/billing/reports.php', 'label' => 'Reports']
        ]
    ],
    'management' => [
        'url' => '/pms/booking/modules/management/',
        'icon' => 'fas fa-chart-line',
        'label' => 'Management',
        'roles' => ['manager'],
        'submenu' => [
            'reports' => ['url' => '/pms/booking/modules/management/reports-dashboard.php', 'label' => 'Reports'],
            'analytics' => ['url' => '/pms/booking/modules/management/analytics.php', 'label' => 'Analytics'],
            'staff' => ['url' => '/pms/booking/modules/management/staff.php', 'label' => 'Staff Management'],
            'settings' => ['url' => '/pms/booking/modules/management/settings.php', 'label' => 'Settings']
        ]
    ],
    'training' => [
        'url' => '/pms/booking/modules/training/',
        'icon' => 'fas fa-graduation-cap',
        'label' => 'Training & Simulations',
        'roles' => ['manager', 'front_desk', 'housekeeping'],
        'submenu' => [
            'dashboard' => ['url' => '/pms/booking/modules/training/training-dashboard.php', 'label' => 'Training Dashboard'],
            'scenarios' => ['url' => '/pms/booking/modules/training/scenarios.php', 'label' => 'Scenarios'],
            'customer_service' => ['url' => '/pms/booking/modules/training/customer-service.php', 'label' => 'Customer Service'],
            'problem_solving' => ['url' => '/pms/booking/modules/training/problem-solving.php', 'label' => 'Problem Solving'],
            'progress' => ['url' => '/pms/booking/modules/training/progress.php', 'label' => 'My Progress'],
            'certificates' => ['url' => '/pms/booking/modules/training/certificates.php', 'label' => 'Certificates']
        ]
    ]
];

// Filter navigation items based on user role
$user_navigation = array_filter($navigation_items, function($item) use ($user_role) {
    return in_array($user_role, $item['roles']);
});
?>

<!-- Sidebar -->
<nav id="sidebar" class="fixed left-0 top-16 w-64 h-[calc(100vh-4rem)] bg-white shadow-lg overflow-y-auto z-40 transform -translate-x-full lg:translate-x-0 transition-transform duration-300">
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
                <a href="../modules/front-desk/new-reservation.php" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-primary hover:bg-gray-50 rounded transition-colors">
                    <i class="fas fa-plus text-xs mr-2"></i>
                    New Reservation
                </a>
            <?php endif; ?>
            
            <?php if (in_array($user_role, ['manager', 'housekeeping'])): ?>
                <a href="/pms/booking/modules/housekeeping/room-status.php" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-primary hover:bg-gray-50 rounded transition-colors">
                    <i class="fas fa-clipboard-list text-xs mr-2"></i>
                    Room Status
                </a>
            <?php endif; ?>
            
            <a href="/pms/booking/modules/training/training-dashboard.php" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-primary hover:bg-gray-50 rounded transition-colors">
                <i class="fas fa-play text-xs mr-2"></i>
                Start Training
            </a>
        </div>
    </div>
</nav>

<!-- Mobile overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden hidden" onclick="closeSidebar()"></div>
