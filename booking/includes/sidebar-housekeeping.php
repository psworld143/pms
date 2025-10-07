<?php
// Housekeeping-specific sidebar component
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['housekeeping', 'manager'])) {
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
        return ($dir === '/' || $dir === '.' ? '/booking/' : rtrim($dir, '/') . '/');
    }
    function booking_url($relative = '') {
        $base_path = dirname(dirname($_SERVER['PHP_SELF']));
        return rtrim($base_path, '/') . '/' . ltrim($relative, '/');
    }
}

// Housekeeping navigation items
$navigation_items = [
    'dashboard' => [
        'url' => booking_url('modules/housekeeping/index.php'),
        'icon' => 'fas fa-tachometer-alt',
        'label' => 'Dashboard',
        'active' => ($current_page === 'index')
    ],
    'housekeeping' => [
        'icon' => 'fas fa-broom',
        'label' => 'Housekeeping',
        'submenu' => [
            'room_status' => ['url' => booking_url('modules/housekeeping/room-status.php'), 'label' => 'Room Status', 'icon' => 'fas fa-bed'],
            'tasks' => ['url' => booking_url('modules/housekeeping/tasks.php'), 'label' => 'My Tasks', 'icon' => 'fas fa-tasks'],
            'maintenance' => ['url' => booking_url('modules/housekeeping/maintenance.php'), 'label' => 'Maintenance', 'icon' => 'fas fa-tools'],
            'cleaning_schedule' => ['url' => booking_url('modules/housekeeping/cleaning-schedule.php'), 'label' => 'Cleaning Schedule', 'icon' => 'fas fa-calendar-alt'],
            'quality_check' => ['url' => booking_url('modules/housekeeping/quality-check.php'), 'label' => 'Quality Check', 'icon' => 'fas fa-clipboard-check']
        ]
    ],
    'training' => [
        'icon' => 'fas fa-graduation-cap',
        'label' => 'Training & Simulations',
        'submenu' => [
            'dashboard' => ['url' => booking_url('modules/training/training-dashboard.php'), 'label' => 'Training Dashboard', 'icon' => 'fas fa-tachometer-alt'],
            'scenarios' => ['url' => booking_url('modules/training/scenarios.php'), 'label' => 'Scenarios', 'icon' => 'fas fa-theater-masks'],
            'customer_service' => ['url' => booking_url('modules/training/customer-service.php'), 'label' => 'Customer Service', 'icon' => 'fas fa-headset'],
            'problem_solving' => ['url' => booking_url('modules/training/problem-solving.php'), 'label' => 'Problem Solving', 'icon' => 'fas fa-lightbulb'],
            'progress' => ['url' => booking_url('modules/training/progress.php'), 'label' => 'My Progress', 'icon' => 'fas fa-chart-line'],
            'certificates' => ['url' => booking_url('modules/training/certificates.php'), 'label' => 'Certificates', 'icon' => 'fas fa-certificate']
        ]
    ]
];
?>

<!-- Housekeeping Sidebar -->
<nav id="sidebar" class="fixed left-0 top-16 w-64 h-[calc(100vh-4rem)] bg-white shadow-lg overflow-y-auto z-40 transition-all duration-300" data-collapsed="false">
    <div class="p-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center sidebar-content">
                <div class="w-8 h-8 bg-gradient-to-r from-green-600 to-emerald-600 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-broom text-white text-sm"></i>
                </div>
                <div class="sidebar-text">
                    <div class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                    <div class="text-xs text-gray-500">Housekeeping Staff</div>
                </div>
            </div>
        </div>
    </div>
    
    <ul class="py-4">
        <?php foreach ($navigation_items as $key => $item): ?>
            <li class="mb-1">
                <?php if (isset($item['submenu'])): ?>
                    <!-- Menu item with submenu -->
                    <button class="w-full flex items-center justify-between px-6 py-3 text-gray-600 hover:text-green-600 hover:bg-green-50 border-l-4 border-transparent hover:border-green-600 transition-colors" 
                            onclick="toggleSubmenu('<?php echo $key; ?>')">
                        <div class="flex items-center">
                            <i class="<?php echo $item['icon']; ?> w-5 mr-3 sidebar-icon"></i>
                            <span class="sidebar-text"><?php echo $item['label']; ?></span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform sidebar-text" id="chevron-<?php echo $key; ?>"></i>
                    </button>
                    <ul id="submenu-<?php echo $key; ?>" class="hidden bg-gray-50">
                        <?php foreach ($item['submenu'] as $subkey => $subitem): ?>
                            <li>
                                <a href="<?php echo $subitem['url']; ?>" 
                                   class="flex items-center px-6 py-2 text-sm text-gray-600 hover:text-green-600 hover:bg-green-100 pl-12">
                                    <i class="<?php echo isset($subitem['icon']) ? $subitem['icon'] : 'fas fa-circle'; ?> text-xs mr-3"></i>
                                    <span class="sidebar-text"><?php echo $subitem['label']; ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <!-- Simple menu item -->
                    <a href="<?php echo $item['url']; ?>" 
                       class="flex items-center px-6 py-3 text-gray-600 hover:text-green-600 hover:bg-green-50 border-l-4 border-transparent hover:border-green-600 transition-colors <?php echo $item['active'] ? 'text-green-600 bg-green-50 border-green-600' : ''; ?>">
                        <i class="<?php echo $item['icon']; ?> w-5 mr-3 sidebar-icon"></i>
                        <span class="sidebar-text"><?php echo $item['label']; ?></span>
                    </a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
    
    <!-- Housekeeping Quick Actions -->
    <div class="p-4 border-t border-gray-200">
        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 sidebar-text">Quick Actions</h3>
        <div class="space-y-2">
            <a href="<?php echo booking_url('modules/housekeeping/room-status.php'); ?>" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-green-600 hover:bg-green-50 rounded transition-colors">
                <i class="fas fa-clipboard-list text-xs mr-2 sidebar-icon"></i>
                <span class="sidebar-text">Room Status</span>
            </a>
            <a href="<?php echo booking_url('modules/housekeeping/tasks.php'); ?>" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-green-600 hover:bg-green-50 rounded transition-colors">
                <i class="fas fa-tasks text-xs mr-2 sidebar-icon"></i>
                <span class="sidebar-text">My Tasks</span>
            </a>
            <a href="<?php echo booking_url('modules/housekeeping/maintenance.php'); ?>" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-green-600 hover:bg-green-50 rounded transition-colors">
                <i class="fas fa-tools text-xs mr-2 sidebar-icon"></i>
                <span class="sidebar-text">Maintenance</span>
            </a>
            <a href="<?php echo booking_url('modules/housekeeping/inventory.php'); ?>" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-green-600 hover:bg-green-50 rounded transition-colors">
                <i class="fas fa-boxes text-xs mr-2 sidebar-icon"></i>
                <span class="sidebar-text">Inventory</span>
            </a>
        </div>
    </div>
</nav>

<!-- Mobile overlay -->
