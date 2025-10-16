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

// Resolve base paths for localhost (/pms/inventory/) and production (/inventory/)
$isPmsPrefixed = strpos($current_url, '/pms/inventory/') !== false;
$BASE_INV = $isPmsPrefixed ? '/pms/inventory/' : '/inventory/';

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
        'url' => $BASE_INV . 'index.php',
        'icon' => 'fas fa-tachometer-alt',
        'label' => 'Inventory Dashboard',
        'description' => 'Limited View - Available items & low-stock alerts',
        'roles' => ['housekeeping', 'manager'],
        'access_level' => ['housekeeping' => 'limited', 'manager' => 'full'],
        'active' => isActiveUrl($BASE_INV . 'index.php', $current_url)
    ],
    'requests' => [
        'url' => $BASE_INV . 'requests.php',
        'icon' => 'fas fa-clipboard-list',
        'label' => 'Requests',
        'description' => 'Create requests for cleaning supplies, linens, amenities',
        'roles' => ['housekeeping', 'manager'],
        'access_level' => ['housekeeping' => 'create', 'manager' => 'approve'],
        'active' => strpos($current_url, 'requests') !== false
    ],
    'room-inventory' => [
        'url' => $BASE_INV . 'room-inventory.php',
        'icon' => 'fas fa-bed',
        'label' => 'Room Inventory',
        'description' => 'View and update item usage per room',
        'roles' => ['housekeeping', 'manager'],
        'access_level' => ['housekeeping' => 'update', 'manager' => 'monitor'],
        'active' => strpos($current_url, 'room-inventory') !== false
    ],
    'transactions' => [
        'url' => $BASE_INV . 'transactions.php',
        'icon' => 'fas fa-exchange-alt',
        'label' => 'Transactions',
        'description' => 'Record usage (e.g., "5 soaps used in Room 203")',
        'roles' => ['housekeeping', 'manager'],
        'access_level' => ['housekeeping' => 'record', 'manager' => 'full'],
        'active' => strpos($current_url, 'transactions') !== false
    ],
    'training' => [
        'url' => $BASE_INV . 'training.php',
        'icon' => 'fas fa-graduation-cap',
        'label' => 'Training',
        'description' => 'Access training simulations for inventory handling',
        'roles' => ['housekeeping'],
        'access_level' => ['housekeeping' => 'access'],
        'active' => strpos($current_url, 'training') !== false
    ],
    
    // 👨‍💼 MANAGER-ONLY MODULES
    'items' => [
        'url' => $BASE_INV . 'items.php',
        'icon' => 'fas fa-box',
        'label' => 'Inventory Items',
        'description' => 'Add, edit, or remove items',
        'roles' => ['manager'],
        'access_level' => ['manager' => 'full'],
        'active' => strpos($current_url, 'items') !== false
    ],
    'reports' => [
        'url' => $BASE_INV . 'reports.php',
        'icon' => 'fas fa-chart-bar',
        'label' => 'Reports',
        'description' => 'Generate and export monthly/weekly inventory reports',
        'roles' => ['manager'],
        'access_level' => ['manager' => 'full'],
        'active' => strpos($current_url, 'reports') !== false
    ],
    'enhanced-reports' => [
        'url' => $BASE_INV . 'enhanced-reports.php',
        'icon' => 'fas fa-chart-line',
        'label' => 'Enhanced Reports',
        'description' => 'View detailed usage trends and cost analysis',
        'roles' => ['manager'],
        'access_level' => ['manager' => 'full'],
        'active' => strpos($current_url, 'enhanced-reports') !== false
    ],
    'auto-reordering' => [
        'url' => $BASE_INV . 'auto-reordering.php',
        'icon' => 'fas fa-robot',
        'label' => 'Auto Reordering',
        'description' => 'Set reorder thresholds and supplier automation',
        'roles' => ['manager'],
        'access_level' => ['manager' => 'full'],
        'active' => strpos($current_url, 'auto-reordering') !== false
    ],
    'barcode-scanner' => [
        'url' => $BASE_INV . 'barcode-scanner.php',
        'icon' => 'fas fa-barcode',
        'label' => 'Barcode Scanner',
        'description' => 'Manage item scanning for faster stock updates',
        'roles' => ['manager'],
        'access_level' => ['manager' => 'full'],
        'active' => strpos($current_url, 'barcode-scanner') !== false
    ],
    'accounting-integration' => [
        'url' => $BASE_INV . 'accounting-simple.php',
        'icon' => 'fas fa-calculator',
        'label' => 'Accounting',
        'description' => 'Link inventory costs to overall hotel expenses',
        'roles' => ['manager'],
        'access_level' => ['manager' => 'optional'],
        'active' => strpos($current_url, 'accounting') !== false
    ],
    'profile' => [
        'url' => $BASE_INV . 'profile.php',
        'icon' => 'fas fa-user-circle',
        'label' => 'Profile',
        'description' => 'Manage your account settings and view statistics',
        'roles' => ['manager'],
        'access_level' => ['manager' => 'full'],
        'active' => strpos($current_url, 'profile') !== false
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
                <a href="<?php echo $BASE_INV; ?>requests.php" 
                   onclick="handleQuickAction('requests', 'create')"
                   class="flex items-center px-3 py-2 text-sm text-purple-600 hover:text-purple-700 hover:bg-purple-50 rounded transition-colors cursor-pointer">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Create Request
                </a>
                <a href="<?php echo $BASE_INV; ?>room-inventory.php" 
                   class="flex items-center px-3 py-2 text-sm text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded transition-colors">
                    <i class="fas fa-bed mr-2"></i>
                    Update Room Items
                </a>
                <a href="<?php echo $BASE_INV; ?>transactions.php" 
                   onclick="handleQuickAction('transactions', 'record')"
                   class="flex items-center px-3 py-2 text-sm text-green-600 hover:text-green-700 hover:bg-green-50 rounded transition-colors cursor-pointer">
                    <i class="fas fa-clipboard-check mr-2"></i>
                    Record Usage
                </a>
            <?php elseif ($user_role === 'manager'): ?>
                <!-- Manager Quick Actions -->
                <a href="<?php echo $BASE_INV; ?>items.php" 
                   onclick="handleQuickAction('items', 'add')"
                   class="flex items-center px-3 py-2 text-sm text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded transition-colors cursor-pointer">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Add Item
                </a>
                <a href="<?php echo $BASE_INV; ?>requests.php" 
                   onclick="handleQuickAction('requests', 'pending')"
                   class="flex items-center px-3 py-2 text-sm text-orange-600 hover:text-orange-700 hover:bg-orange-50 rounded transition-colors cursor-pointer">
                    <i class="fas fa-clock mr-2"></i>
                    Pending Requests
                </a>
                <a href="<?php echo $BASE_INV; ?>items.php" 
                   onclick="handleQuickAction('items', 'low_stock')"
                   class="flex items-center px-3 py-2 text-sm text-red-600 hover:text-red-700 hover:bg-red-50 rounded transition-colors cursor-pointer">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Low Stock Items
                </a>
                <a href="<?php echo $BASE_INV; ?>reports.php" 
                   class="flex items-center px-3 py-2 text-sm text-green-600 hover:text-green-700 hover:bg-green-50 rounded transition-colors">
                    <i class="fas fa-chart-bar mr-2"></i>
                    Generate Report
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    function handleQuickAction(page, action) {
        // Navigate to the page with the action parameter
        const url = new URL(window.location.origin + '<?php echo $BASE_INV; ?>' + page + '.php');
        url.searchParams.set('action', action);
        window.location.href = url.toString();
    }
    </script>
    
</nav>

<!-- Sidebar Overlay for Mobile -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden" onclick="closeSidebar()"></div>
