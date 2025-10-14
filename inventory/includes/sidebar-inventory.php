<?php
// Dedicated Inventory Management Sidebar
// This sidebar is specifically for the inventory management system

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if user has appropriate role (only manager and housekeeping)
$user_role = $_SESSION['user_role'] ?? '';
if (!in_array($user_role, ['housekeeping', 'manager'])) {
    header('Location: login.php?error=access_denied');
    exit();
}
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

// Role-Based Inventory Management Navigation Items
$navigation_items = [
    // 🧹 HOUSEKEEPING MODULES
    'dashboard' => [
        'url' => '/pms/inventory/index.php',
        'icon' => 'fas fa-tachometer-alt',
        'label' => 'Inventory Dashboard',
        'description' => 'Limited View - Available items & low-stock alerts',
        'roles' => ['housekeeping', 'manager'],
        'access_level' => ['housekeeping' => 'limited', 'manager' => 'full'],
        'active' => isActiveUrl('/pms/inventory/index.php', $current_url)
    ],
    'requests' => [
        'url' => '/pms/inventory/requests.php',
        'icon' => 'fas fa-clipboard-list',
        'label' => 'Requests',
        'description' => 'Create requests for cleaning supplies, linens, amenities',
        'roles' => ['housekeeping', 'manager'],
        'access_level' => ['housekeeping' => 'create', 'manager' => 'approve'],
        'active' => strpos($current_url, 'requests') !== false
    ],
    'room-inventory' => [
        'url' => '/pms/inventory/room-inventory.php',
        'icon' => 'fas fa-bed',
        'label' => 'Room Inventory',
        'description' => 'View and update item usage per room',
        'roles' => ['housekeeping', 'manager'],
        'access_level' => ['housekeeping' => 'update', 'manager' => 'monitor'],
        'active' => strpos($current_url, 'room-inventory') !== false
    ],
    'transactions' => [
        'url' => '/pms/inventory/transactions.php',
        'icon' => 'fas fa-exchange-alt',
        'label' => 'Transactions',
        'description' => 'Record usage (e.g., "5 soaps used in Room 203")',
        'roles' => ['housekeeping', 'manager'],
        'access_level' => ['housekeeping' => 'record', 'manager' => 'full'],
        'active' => strpos($current_url, 'transactions') !== false
    ],
    'training' => [
        'url' => '/pms/inventory/training.php',
        'icon' => 'fas fa-graduation-cap',
        'label' => 'Training',
        'description' => 'Access training simulations for inventory handling',
        'roles' => ['housekeeping'],
        'access_level' => ['housekeeping' => 'access'],
        'active' => strpos($current_url, 'training') !== false
    ],
    'mobile' => [
        'url' => '/pms/inventory/mobile.php',
        'icon' => 'fas fa-mobile-alt',
        'label' => 'Mobile Interface',
        'description' => 'Quick updates via mobile while cleaning rooms',
        'roles' => ['housekeeping', 'manager'],
        'access_level' => ['housekeeping' => 'mobile', 'manager' => 'mobile'],
        'active' => strpos($current_url, 'mobile') !== false
    ],
    
    // 👨‍💼 MANAGER-ONLY MODULES
    'items' => [
        'url' => '/pms/inventory/items.php',
        'icon' => 'fas fa-box',
        'label' => 'Inventory Items',
        'description' => 'Add, edit, or remove items',
        'roles' => ['manager'],
        'access_level' => ['manager' => 'full'],
        'active' => strpos($current_url, 'items') !== false
    ],
    'reports' => [
        'url' => '/pms/inventory/reports.php',
        'icon' => 'fas fa-chart-bar',
        'label' => 'Reports',
        'description' => 'Generate and export monthly/weekly inventory reports',
        'roles' => ['manager'],
        'access_level' => ['manager' => 'full'],
        'active' => strpos($current_url, 'reports') !== false
    ],
    'enhanced-reports' => [
        'url' => '/pms/inventory/enhanced-reports.php',
        'icon' => 'fas fa-chart-line',
        'label' => 'Enhanced Reports',
        'description' => 'View detailed usage trends and cost analysis',
        'roles' => ['manager'],
        'access_level' => ['manager' => 'full'],
        'active' => strpos($current_url, 'enhanced-reports') !== false
    ],
    'automated-reordering' => [
        'url' => '/pms/inventory/automated-reordering.php',
        'icon' => 'fas fa-robot',
        'label' => 'Auto Reordering',
        'description' => 'Set reorder thresholds and supplier automation',
        'roles' => ['manager'],
        'access_level' => ['manager' => 'full'],
        'active' => strpos($current_url, 'automated-reordering') !== false
    ],
    'barcode-scanner' => [
        'url' => '/pms/inventory/barcode-scanner.php',
        'icon' => 'fas fa-barcode',
        'label' => 'Barcode Scanner',
        'description' => 'Manage item scanning for faster stock updates',
        'roles' => ['manager'],
        'access_level' => ['manager' => 'full'],
        'active' => strpos($current_url, 'barcode-scanner') !== false
    ],
    'accounting-integration' => [
        'url' => '/pms/inventory/accounting-integration.php',
        'icon' => 'fas fa-calculator',
        'label' => 'Accounting',
        'description' => 'Link inventory costs to overall hotel expenses',
        'roles' => ['manager'],
        'access_level' => ['manager' => 'optional'],
        'active' => strpos($current_url, 'accounting-integration') !== false
    ]
];

// Filter navigation items based on user role
$user_navigation = array_filter($navigation_items, function($item) use ($user_role) {
    return in_array($user_role, $item['roles']);
});
?>

<!-- Inventory Management Sidebar -->
<nav id="sidebar" class="fixed left-0 top-16 w-64 h-[calc(100vh-4rem)] bg-white shadow-lg overflow-y-auto z-40 transform -translate-x-full lg:translate-x-0 transition-transform duration-300">
    <div class="p-4 border-b border-gray-200">
        <div class="flex items-center">
            <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-green-600 rounded-full flex items-center justify-center mr-3">
                <i class="fas fa-boxes text-white text-sm"></i>
            </div>
            <div>
                <div class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></div>
                <div class="text-xs text-gray-500"><?php echo ucfirst(str_replace('_', ' ', $user_role)); ?></div>
            </div>
        </div>
    </div>
    
    <div class="p-4 border-b border-gray-200">
        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
            <?php if ($user_role === 'housekeeping'): ?>
                🧹 Housekeeping Modules
            <?php elseif ($user_role === 'manager'): ?>
                👨‍💼 Manager Modules
            <?php else: ?>
                Inventory Management
            <?php endif; ?>
        </h3>
    </div>
    
    <!-- Role-based Module List -->
    <div class="py-4">
        <ul>
            <?php foreach ($user_navigation as $key => $item): ?>
                <li class="mb-1">
                    <a href="<?php echo $item['url']; ?>" 
                       class="flex items-center px-6 py-3 text-gray-600 hover:text-green-600 hover:bg-green-50 border-l-4 <?php echo $item['active'] ? 'border-green-500 bg-green-50 text-green-600' : 'border-transparent'; ?> transition-colors group">
                        <i class="<?php echo $item['icon']; ?> w-5 mr-3"></i>
                        <div class="flex-1">
                            <div class="font-medium"><?php echo $item['label']; ?></div>
                            <?php if (!empty($item['description'])): ?>
                                <div class="text-xs text-gray-500 group-hover:text-green-600"><?php echo $item['description']; ?></div>
                            <?php endif; ?>
                        </div>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    
    <!-- Role-Based Quick Actions Section -->
    <div class="p-4 border-t border-gray-200">
        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Quick Actions</h3>
        <div class="space-y-2">
            <?php if ($user_role === 'housekeeping'): ?>
                <!-- Housekeeping Quick Actions -->
                <a href="/pms/inventory/requests.php?action=create" 
                   class="flex items-center px-3 py-2 text-sm text-purple-600 hover:text-purple-700 hover:bg-purple-50 rounded transition-colors">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Create Request
                </a>
                <a href="/pms/inventory/room-inventory.php" 
                   class="flex items-center px-3 py-2 text-sm text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded transition-colors">
                    <i class="fas fa-bed mr-2"></i>
                    Update Room Items
                </a>
                <a href="/pms/inventory/transactions.php?action=record" 
                   class="flex items-center px-3 py-2 text-sm text-green-600 hover:text-green-700 hover:bg-green-50 rounded transition-colors">
                    <i class="fas fa-clipboard-check mr-2"></i>
                    Record Usage
                </a>
            <?php elseif ($user_role === 'manager'): ?>
                <!-- Manager Quick Actions -->
                <a href="/pms/inventory/items.php?action=add" 
                   class="flex items-center px-3 py-2 text-sm text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded transition-colors">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Add Item
                </a>
                <a href="/pms/inventory/requests.php?status=pending" 
                   class="flex items-center px-3 py-2 text-sm text-orange-600 hover:text-orange-700 hover:bg-orange-50 rounded transition-colors">
                    <i class="fas fa-clock mr-2"></i>
                    Pending Requests
                </a>
                <a href="/pms/inventory/items.php?filter=low_stock" 
                   class="flex items-center px-3 py-2 text-sm text-red-600 hover:text-red-700 hover:bg-red-50 rounded transition-colors">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Low Stock Items
                </a>
                <a href="/pms/inventory/reports.php" 
                   class="flex items-center px-3 py-2 text-sm text-green-600 hover:text-green-700 hover:bg-green-50 rounded transition-colors">
                    <i class="fas fa-chart-bar mr-2"></i>
                    Generate Report
                </a>
            <?php endif; ?>
        </div>
    </div>
    
</nav>

<!-- Sidebar Overlay for Mobile -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden" onclick="closeSidebar()"></div>
