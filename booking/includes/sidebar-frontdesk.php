<?php
// Front Desk-specific sidebar component
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'front_desk') {
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
function isActiveUrl($url, $current_url) {
    $clean_url = rtrim(parse_url($url, PHP_URL_PATH), '/');
    $clean_current = rtrim(parse_url($current_url, PHP_URL_PATH), '/');
    return $clean_current === $clean_url;
}

// Function to check if any submenu item is active
function hasActiveSubmenu($submenu, $current_url) {
    foreach ($submenu as $subitem) {
        if (isActiveUrl($subitem['url'], $current_url)) {
            return true;
        }
    }
    return false;
}

// Front Desk navigation items
$navigation_items = [
    'dashboard' => [
        'url' => booking_url('modules/front-desk/index.php'),
        'icon' => 'fas fa-tachometer-alt',
        'label' => 'Dashboard',
        'active' => isActiveUrl(booking_url('modules/front-desk/index.php'), $current_url)
    ],
    'front_desk' => [
        'icon' => 'fas fa-concierge-bell',
        'label' => 'Front Desk',
        'active' => hasActiveSubmenu([
            ['url' => booking_url('modules/front-desk/manage-reservations.php')],
            ['url' => booking_url('modules/front-desk/check-in.php')],
            ['url' => booking_url('modules/front-desk/check-out.php')],
            ['url' => booking_url('modules/front-desk/new-reservation.php')],
            ['url' => booking_url('modules/front-desk/service-management.php')]
        ], $current_url),
        'submenu' => [
            'reservations' => [
                'url' => booking_url('modules/front-desk/manage-reservations.php'), 
                'label' => 'Reservations', 
                'icon' => 'fas fa-calendar-check',
                'active' => isActiveUrl(booking_url('modules/front-desk/manage-reservations.php'), $current_url)
            ],
            'check_in' => [
                'url' => booking_url('modules/front-desk/check-in.php'), 
                'label' => 'Check In', 
                'icon' => 'fas fa-sign-in-alt',
                'active' => isActiveUrl(booking_url('modules/front-desk/check-in.php'), $current_url)
            ],
            'check_out' => [
                'url' => booking_url('modules/front-desk/check-out.php'), 
                'label' => 'Check Out', 
                'icon' => 'fas fa-sign-out-alt',
                'active' => isActiveUrl(booking_url('modules/front-desk/check-out.php'), $current_url)
            ],
            'new_reservation' => [
                'url' => booking_url('modules/front-desk/new-reservation.php'), 
                'label' => 'New Reservation', 
                'icon' => 'fas fa-plus',
                'active' => isActiveUrl(booking_url('modules/front-desk/new-reservation.php'), $current_url)
            ],
            'service_management' => [
                'url' => booking_url('modules/front-desk/service-management.php'), 
                'label' => 'Service Management', 
                'icon' => 'fas fa-hands-helping',
                'active' => isActiveUrl(booking_url('modules/front-desk/service-management.php'), $current_url)
            ]
        ]
    ],
    'guests' => [
        'icon' => 'fas fa-users',
        'label' => 'Guest Management',
        'active' => hasActiveSubmenu([
            ['url' => booking_url('modules/front-desk/guest-management.php')],
            ['url' => booking_url('modules/front-desk/vip-guests.php')],
            ['url' => booking_url('modules/front-desk/feedback.php')]
        ], $current_url),
        'submenu' => [
            'profiles' => [
                'url' => booking_url('modules/front-desk/guest-management.php'), 
                'label' => 'Guest Management', 
                'icon' => 'fas fa-user-circle',
                'active' => isActiveUrl(booking_url('modules/front-desk/guest-management.php'), $current_url)
            ],
            'vip' => [
                'url' => booking_url('modules/front-desk/vip-guests.php'), 
                'label' => 'VIP Guests', 
                'icon' => 'fas fa-crown',
                'active' => isActiveUrl(booking_url('modules/front-desk/vip-guests.php'), $current_url)
            ],
            'feedback' => [
                'url' => booking_url('modules/front-desk/feedback.php'), 
                'label' => 'Feedback', 
                'icon' => 'fas fa-comment-alt',
                'active' => isActiveUrl(booking_url('modules/front-desk/feedback.php'), $current_url)
            ]
        ]
    ],
    'billing' => [
        'icon' => 'fas fa-credit-card',
        'label' => 'Billing & Payments',
        'active' => hasActiveSubmenu([
            ['url' => booking_url('modules/front-desk/billing-payment.php')]
        ], $current_url),
        'submenu' => [
            'bills' => [
                'url' => booking_url('modules/front-desk/billing-payment.php'), 
                'label' => 'Bills and Payments', 
                'icon' => 'fas fa-file-invoice',
                'active' => isActiveUrl(booking_url('modules/front-desk/billing-payment.php'), $current_url)
            ]
        ]
    ],
    'housekeeping' => [
        'icon' => 'fas fa-broom',
        'label' => 'Housekeeping',
        'active' => hasActiveSubmenu([
            ['url' => booking_url('modules/front-desk/room-status.php')],
            ['url' => booking_url('modules/front-desk/requests.php')]
        ], $current_url),
        'submenu' => [
            'room_status' => [
                'url' => booking_url('modules/front-desk/room-status.php'), 
                'label' => 'Room Status', 
                'icon' => 'fas fa-clipboard-list',
                'active' => isActiveUrl(booking_url('modules/front-desk/room-status.php'), $current_url)
            ],
            'requests' => [
                'url' => booking_url('modules/front-desk/requests.php'), 
                'label' => 'Service Requests', 
                'icon' => 'fas fa-tools',
                'active' => isActiveUrl(booking_url('modules/front-desk/requests.php'), $current_url)
            ]
        ]
    ],
    'training' => [
        'icon' => 'fas fa-graduation-cap',
        'label' => 'Training & Simulations',
        'active' => hasActiveSubmenu([
            ['url' => booking_url('modules/training/training-dashboard.php')],
            ['url' => booking_url('modules/training/scenarios.php')],
            ['url' => booking_url('modules/training/customer-service.php')],
            ['url' => booking_url('modules/training/problem-solving.php')],
            ['url' => booking_url('modules/training/progress.php')],
            ['url' => booking_url('modules/training/certificates.php')]
        ], $current_url),
        'submenu' => [
            'dashboard' => [
                'url' => booking_url('modules/training/training-dashboard.php'), 
                'label' => 'Training Dashboard', 
                'icon' => 'fas fa-tachometer-alt',
                'active' => isActiveUrl(booking_url('modules/training/training-dashboard.php'), $current_url)
            ],
            'scenarios' => [
                'url' => booking_url('modules/training/scenarios.php'), 
                'label' => 'Scenarios', 
                'icon' => 'fas fa-theater-masks',
                'active' => isActiveUrl(booking_url('modules/training/scenarios.php'), $current_url)
            ],
            'customer_service' => [
                'url' => booking_url('modules/training/customer-service.php'), 
                'label' => 'Customer Service', 
                'icon' => 'fas fa-headset',
                'active' => isActiveUrl(booking_url('modules/training/customer-service.php'), $current_url)
            ],
            'problem_solving' => [
                'url' => booking_url('modules/training/problem-solving.php'), 
                'label' => 'Problem Solving', 
                'icon' => 'fas fa-lightbulb',
                'active' => isActiveUrl(booking_url('modules/training/problem-solving.php'), $current_url)
            ],
            'progress' => [
                'url' => booking_url('modules/training/progress.php'), 
                'label' => 'My Progress', 
                'icon' => 'fas fa-chart-line',
                'active' => isActiveUrl(booking_url('modules/training/progress.php'), $current_url)
            ],
            'certificates' => [
                'url' => booking_url('modules/training/certificates.php'), 
                'label' => 'Certificates', 
                'icon' => 'fas fa-certificate',
                'active' => isActiveUrl(booking_url('modules/training/certificates.php'), $current_url)
            ]
        ]
    ]
];
?>

<!-- Front Desk Sidebar -->
<style>
    /* Enhanced active state styling */
    .sidebar-nav-item.active {
        background: linear-gradient(135deg, #3B82F6 0%, #06B6D4 100%);
        color: white !important;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }
    
    .sidebar-nav-item.active:hover {
        background: linear-gradient(135deg, #2563EB 0%, #0891B2 100%);
    }
    
    .sidebar-submenu-item.active {
        background: linear-gradient(135deg, #DBEAFE 0%, #E0F2FE 100%);
        color: #1E40AF !important;
        border-right: 3px solid #3B82F6;
        font-weight: 600;
    }
    
    .sidebar-submenu-item.active:hover {
        background: linear-gradient(135deg, #BFDBFE 0%, #BAE6FD 100%);
    }
    
    /* Smooth transitions for all interactive elements */
    .sidebar-nav-item,
    .sidebar-submenu-item {
        transition: all 0.3s ease-in-out;
    }
    
    /* Enhanced chevron animation */
    .sidebar-chevron {
        transition: transform 0.3s ease-in-out;
    }
</style>

<nav id="sidebar" class="fixed left-0 top-16 w-64 h-[calc(100vh-4rem)] bg-white shadow-lg overflow-y-auto z-40 transition-all duration-300 transform -translate-x-full lg:translate-x-0" data-collapsed="false" data-mobile-open="false">
    <div class="p-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center sidebar-content">
                <div class="w-8 h-8 bg-gradient-to-r from-blue-600 to-cyan-600 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-concierge-bell text-white text-sm"></i>
                </div>
                <div class="sidebar-text">
                    <div class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                    <div class="text-xs text-gray-500">Front Desk Staff</div>
                </div>
            </div>

        </div>
    </div>
    
    <ul class="py-4">
        <?php foreach ($navigation_items as $key => $item): ?>
            <li class="mb-1">
                <?php if (isset($item['submenu'])): ?>
                    <!-- Menu item with submenu -->
                    <button class="w-full flex items-center justify-between px-6 py-3 text-gray-600 hover:text-blue-600 hover:bg-blue-50 border-l-4 border-transparent hover:border-blue-600 transition-colors sidebar-nav-item <?php echo $item['active'] ? 'active' : ''; ?>" 
                            onclick="toggleSubmenu('<?php echo $key; ?>')">
                        <div class="flex items-center">
                            <i class="<?php echo $item['icon']; ?> w-5 mr-3 sidebar-icon"></i>
                            <span class="sidebar-text"><?php echo $item['label']; ?></span>
                        </div>
                        <i class="fas fa-chevron-down text-xs sidebar-chevron sidebar-text <?php echo $item['active'] ? 'rotate-180' : ''; ?>" id="chevron-<?php echo $key; ?>"></i>
                    </button>
                    <ul id="submenu-<?php echo $key; ?>" class="<?php echo $item['active'] ? '' : 'hidden'; ?> bg-gray-50">
                        <?php foreach ($item['submenu'] as $subkey => $subitem): ?>
                            <li>
                                <a href="<?php echo $subitem['url']; ?>" 
                                   class="flex items-center px-6 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-100 pl-12 sidebar-submenu-item <?php echo $subitem['active'] ? 'active' : ''; ?>">
                                    <i class="<?php echo isset($subitem['icon']) ? $subitem['icon'] : 'fas fa-circle'; ?> text-xs mr-3"></i>
                                    <span class="sidebar-text"><?php echo $subitem['label']; ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <!-- Simple menu item -->
                    <a href="<?php echo $item['url']; ?>" 
                       class="flex items-center px-6 py-3 text-gray-600 hover:text-blue-600 hover:bg-blue-50 border-l-4 border-transparent hover:border-blue-600 transition-colors sidebar-nav-item <?php echo $item['active'] ? 'active' : ''; ?>">
                        <i class="<?php echo $item['icon']; ?> w-5 mr-3 sidebar-icon"></i>
                        <span class="sidebar-text"><?php echo $item['label']; ?></span>
                    </a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
    
    <!-- Front Desk Quick Actions -->
    <div class="p-4 border-t border-gray-200">
        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 sidebar-text">Quick Actions</h3>
        <div class="space-y-2">
            <a href="<?php echo booking_url('modules/front-desk/new-reservation.php'); ?>" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">
                <i class="fas fa-plus text-xs mr-2 sidebar-icon"></i>
                <span class="sidebar-text">New Reservation</span>
            </a>
            <a href="<?php echo booking_url('modules/front-desk/check-in.php'); ?>" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">
                <i class="fas fa-sign-in-alt text-xs mr-2 sidebar-icon"></i>
                <span class="sidebar-text">Check In</span>
            </a>
            <a href="<?php echo booking_url('modules/front-desk/check-out.php'); ?>" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">
                <i class="fas fa-sign-out-alt text-xs mr-2 sidebar-icon"></i>
                <span class="sidebar-text">Check Out</span>
            </a>
            <a href="<?php echo booking_url('modules/front-desk/guest-management.php'); ?>" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">
                <i class="fas fa-users text-xs mr-2 sidebar-icon"></i>
                <span class="sidebar-text">Guest Management</span>
            </a>
        </div>
    </div>
</nav>
