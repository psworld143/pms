<?php
// POS-specific sidebar component that matches the exact booking system design
// This file should not contain session checks or redirects

$user_role = $_SESSION['pos_user_role'] ?? 'pos_user';
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Function to generate POS URLs dynamically based on environment
if (!function_exists('pos_url')) {
    function pos_base() {
        $script = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\','/', $_SERVER['SCRIPT_NAME']) : '';
        $path = $script !== '' ? $script : (isset($_SERVER['PHP_SELF']) ? str_replace('\\','/', $_SERVER['PHP_SELF']) : '/');
        
        // Check if we're on localhost or live server
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        $is_localhost = (strpos($host, 'localhost') !== false) || (strpos($host, '127.0.0.1') !== false);
        
        // Find /pos/ in the path
        $pos = strpos($path, '/pos/');
        if ($pos !== false) {
            $base = rtrim(substr($path, 0, $pos + strlen('/pos/')), '/') . '/';
            return $base;
        }
        
        // Try to find pos directory
        $dir = str_replace('\\','/', dirname($path));
        $guard = 0;
        while ($dir !== '/' && $dir !== '.' && basename($dir) !== 'pos' && $guard < 10) {
            $dir = dirname($dir);
            $guard++;
        }
        if (basename($dir) === 'pos') {
            return rtrim($dir, '/') . '/';
        }
        
        // Default fallback based on environment
        return $is_localhost ? '/pms/pos/' : '/pos/';
    }
    function pos_url($relative = '') {
        $base = pos_base();
        $relative = ltrim($relative, '/');
        
        // If base ends with /, just append relative
        if (substr($base, -1) === '/') {
            return $base . $relative;
        }
        // If base doesn't end with /, add it
        return $base . '/' . $relative;
    }
}

// Define POS navigation items based on user role
$navigation_items = [
    'dashboard' => [
        'url' => pos_url('index.php'),
        'icon' => 'fas fa-tachometer-alt',
        'label' => 'POS Dashboard',
        'roles' => ['manager', 'front_desk', 'pos_user', 'student']
    ],
    'restaurant' => [
        'url' => pos_url('restaurant/'),
        'icon' => 'fas fa-utensils',
        'label' => 'Restaurant POS',
        'roles' => ['manager', 'front_desk', 'pos_user', 'student'],
        'submenu' => [
            'menu' => ['url' => pos_url('restaurant/menu.php'), 'label' => 'Menu Management'],
            'orders' => ['url' => pos_url('restaurant/orders.php'), 'label' => 'Active Orders'],
            'tables' => ['url' => pos_url('restaurant/tables.php'), 'label' => 'Table Management'],
            'reports' => ['url' => pos_url('restaurant/reports.php'), 'label' => 'Restaurant Reports']
        ]
    ],
    'room_service' => [
        'url' => pos_url('room-service/'),
        'icon' => 'fas fa-bed',
        'label' => 'Room Service',
        'roles' => ['manager', 'front_desk', 'pos_user', 'student'],
        'submenu' => [
            'orders' => ['url' => pos_url('room-service/orders.php'), 'label' => 'Room Orders'],
            'delivery' => ['url' => pos_url('room-service/delivery.php'), 'label' => 'Delivery Status'],
            'menu' => ['url' => pos_url('room-service/menu.php'), 'label' => 'Room Service Menu'],
            'reports' => ['url' => pos_url('room-service/reports.php'), 'label' => 'Room Service Reports']
        ]
    ],
    'spa' => [
        'url' => pos_url('spa/'),
        'icon' => 'fas fa-spa',
        'label' => 'Spa & Wellness',
        'roles' => ['manager', 'front_desk', 'pos_user', 'student'],
        'submenu' => [
            'services' => ['url' => pos_url('spa/services.php'), 'label' => 'Spa Services'],
            'appointments' => ['url' => pos_url('spa/appointments.php'), 'label' => 'Appointments'],
            'therapists' => ['url' => pos_url('spa/therapists.php'), 'label' => 'Therapists'],
            'reports' => ['url' => pos_url('spa/reports.php'), 'label' => 'Spa Reports']
        ]
    ],
    'gift_shop' => [
        'url' => pos_url('gift-shop/'),
        'icon' => 'fas fa-gift',
        'label' => 'Gift Shop',
        'roles' => ['manager', 'front_desk', 'pos_user', 'student'],
        'submenu' => [
            'inventory' => ['url' => pos_url('gift-shop/inventory.php'), 'label' => 'Inventory'],
            'sales' => ['url' => pos_url('gift-shop/sales.php'), 'label' => 'Sales'],
            'products' => ['url' => pos_url('gift-shop/products.php'), 'label' => 'Products'],
            'reports' => ['url' => pos_url('gift-shop/reports.php'), 'label' => 'Gift Shop Reports']
        ]
    ],
    'events' => [
        'url' => pos_url('events/'),
        'icon' => 'fas fa-calendar-alt',
        'label' => 'Event Services',
        'roles' => ['manager', 'front_desk', 'pos_user', 'student'],
        'submenu' => [
            'bookings' => ['url' => pos_url('events/bookings.php'), 'label' => 'Event Bookings'],
            'services' => ['url' => pos_url('events/services.php'), 'label' => 'Event Services'],
            'venues' => ['url' => pos_url('events/venues.php'), 'label' => 'Venues'],
            'reports' => ['url' => pos_url('events/reports.php'), 'label' => 'Event Reports']
        ]
    ],
    'quick_sales' => [
        'url' => pos_url('quick-sales/'),
        'icon' => 'fas fa-bolt',
        'label' => 'Quick Sales',
        'roles' => ['manager', 'front_desk', 'pos_user', 'student'],
        'submenu' => [
            'transactions' => ['url' => pos_url('quick-sales/transactions.php'), 'label' => 'Transactions'],
            'items' => ['url' => pos_url('quick-sales/items.php'), 'label' => 'Quick Items'],
            'history' => ['url' => pos_url('quick-sales/history.php'), 'label' => 'Sales History'],
            'reports' => ['url' => pos_url('quick-sales/reports.php'), 'label' => 'Quick Sales Reports']
        ]
    ],
    'reports' => [
        'url' => pos_url('reports/'),
        'icon' => 'fas fa-chart-bar',
        'label' => 'Reports & Analytics',
        'roles' => ['manager', 'pos_user'],
        'submenu' => [
            'sales' => ['url' => pos_url('reports/sales.php'), 'label' => 'Sales Reports'],
            'inventory' => ['url' => pos_url('reports/inventory.php'), 'label' => 'Inventory Reports'],
            'performance' => ['url' => pos_url('reports/performance.php'), 'label' => 'Performance Reports'],
            'analytics' => ['url' => pos_url('reports/analytics.php'), 'label' => 'Analytics Dashboard']
        ]
    ],
    'training' => [
        'url' => pos_url('training/'),
        'icon' => 'fas fa-graduation-cap',
        'label' => 'Training & Simulations',
        'roles' => ['manager', 'front_desk', 'pos_user', 'student'],
        'submenu' => [
            'dashboard' => ['url' => pos_url('training/training-dashboard.php'), 'label' => 'Training Dashboard'],
            'scenarios' => ['url' => pos_url('training/scenarios.php'), 'label' => 'Scenarios'],
            'progress' => ['url' => pos_url('training/progress.php'), 'label' => 'My Progress'],
            'certificates' => ['url' => pos_url('training/certificates.php'), 'label' => 'Certificates']
        ]
    ]
];

// Filter navigation items based on user role
$user_navigation = array_filter($navigation_items, function($item) use ($user_role) {
    return in_array($user_role, $item['roles']);
});
?>

<!-- Sidebar - Matching booking system exactly -->
<nav id="sidebar" class="fixed left-0 top-16 w-64 sm:w-72 lg:w-64 h-[calc(100vh-4rem)] bg-white shadow-lg overflow-y-auto z-50 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
    <div class="p-4 border-b border-gray-200">
        <div class="flex items-center">
            <div class="w-8 h-8 bg-gradient-to-r from-primary to-secondary rounded-full flex items-center justify-center mr-3">
                <i class="fas fa-cash-register text-white text-sm"></i>
            </div>
            <div>
                <div class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($_SESSION['pos_user_name'] ?? 'POS User'); ?></div>
                <div class="text-xs text-gray-500">
                    <?php if (isset($_SESSION['pos_demo_mode']) && $_SESSION['pos_demo_mode']): ?>
                        <i class="fas fa-graduation-cap mr-1"></i>Student Trainee
                    <?php else: ?>
                        <?php echo ucfirst(str_replace('_', ' ', $user_role)); ?>
                    <?php endif; ?>
                </div>
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
            <a href="<?php echo pos_url('quick-sales/'); ?>" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-primary hover:bg-gray-50 rounded transition-colors">
                <i class="fas fa-plus text-xs mr-2"></i>
                New Transaction
            </a>
            
            <a href="<?php echo pos_url('restaurant/'); ?>" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-primary hover:bg-gray-50 rounded transition-colors">
                <i class="fas fa-utensils text-xs mr-2"></i>
                Restaurant Orders
            </a>
            
            <a href="<?php echo pos_url('room-service/'); ?>" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-primary hover:bg-gray-50 rounded transition-colors">
                <i class="fas fa-bed text-xs mr-2"></i>
                Room Service
            </a>
            
            <a href="<?php echo pos_url('training/training-dashboard.php'); ?>" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-primary hover:bg-gray-50 rounded transition-colors">
                <i class="fas fa-play text-xs mr-2"></i>
                Start Training
            </a>
        </div>
    </div>
    
    <!-- Module Links Section -->
    <div class="p-4 border-t border-gray-200">
        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Other Modules</h3>
        <div class="space-y-2">
            <a href="<?php echo pos_url('../booking/'); ?>" 
               class="flex items-center px-3 py-2 text-sm text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded transition-colors">
                <i class="fas fa-bed mr-2"></i>
                Booking System
            </a>
            <a href="<?php echo pos_url('../inventory/'); ?>" 
               class="flex items-center px-3 py-2 text-sm text-green-600 hover:text-green-700 hover:bg-green-50 rounded transition-colors">
                <i class="fas fa-boxes mr-2"></i>
                Inventory
            </a>
        </div>
    </div>
</nav>

<!-- Mobile overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden hidden"></div>
