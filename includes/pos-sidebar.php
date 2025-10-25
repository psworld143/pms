<?php
// POS-specific sidebar component
// Note: Session checks should be done in the main page, not in included components
// This allows for more flexible inclusion

$user_role = $_SESSION['pos_user_role'] ?? $_SESSION['user_role'] ?? 'pos_user';
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$current_url = $_SERVER['REQUEST_URI'];

// Use absolute paths for navigation
$base_path = '/pms/pos/';

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

// POS navigation items
$navigation_items = [
    'dashboard' => [
        'url' => $base_path . 'index.php',
        'icon' => 'fas fa-tachometer-alt',
        'label' => 'POS Dashboard',
        'active' => isActiveUrl($base_path . 'index.php', $current_url)
    ],
    'restaurant' => [
        'url' => $base_path . 'restaurant/',
        'icon' => 'fas fa-utensils',
        'label' => 'Restaurant POS',
        'active' => strpos($current_url, '/restaurant/') !== false
    ],
    'room_service' => [
        'url' => $base_path . 'room-service/',
        'icon' => 'fas fa-bed',
        'label' => 'Room Service',
        'active' => strpos($current_url, '/room-service/') !== false
    ],
    'spa' => [
        'url' => $base_path . 'spa/',
        'icon' => 'fas fa-spa',
        'label' => 'Spa & Wellness',
        'active' => strpos($current_url, '/spa/') !== false
    ],
    'gift_shop' => [
        'url' => $base_path . 'gift-shop/',
        'icon' => 'fas fa-gift',
        'label' => 'Gift Shop',
        'active' => strpos($current_url, '/gift-shop/') !== false
    ],
    'events' => [
        'url' => $base_path . 'events/',
        'icon' => 'fas fa-calendar-alt',
        'label' => 'Event Services',
        'active' => strpos($current_url, '/events/') !== false
    ],
    'quick_sales' => [
        'url' => $base_path . 'quick-sales/',
        'icon' => 'fas fa-bolt',
        'label' => 'Quick Sales',
        'active' => strpos($current_url, '/quick-sales/') !== false
    ]
];
?>

<!-- Sidebar -->
<aside id="sidebar" class="fixed left-0 top-16 h-full w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out lg:translate-x-0 -translate-x-full z-50">
    <div class="flex flex-col h-full">
        <!-- Sidebar Header -->
        <div class="p-4 border-b border-gray-200">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-gradient-to-r from-primary to-secondary rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-cash-register text-white text-sm"></i>
                </div>
                <h2 class="text-lg font-semibold text-gray-800">POS System</h2>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1 px-3">
                <?php foreach ($navigation_items as $key => $item): ?>
                    <li>
                        <a href="<?php echo $item['url']; ?>" 
                           class="flex items-center px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors <?php echo $item['active'] ? 'bg-blue-50 text-blue-600' : ''; ?>">
                            <i class="<?php echo $item['icon']; ?> w-5 h-5 mr-3"></i>
                            <span class="font-medium"><?php echo $item['label']; ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <!-- Sidebar Footer -->
        <div class="p-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <a href="../../booking/" class="flex items-center text-sm text-gray-600 hover:text-blue-600 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to PMS
                </a>
            </div>
        </div>
    </div>
</aside>

<!-- Mobile Sidebar Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden"></div>

<script>
// Sidebar functionality - ensure it works immediately
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    
    if (sidebar && overlay) {
        sidebar.classList.toggle('-translate-x-full');
        sidebar.classList.toggle('translate-x-0');
        overlay.classList.toggle('hidden');
    }
}

function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    
    if (sidebar && overlay) {
        sidebar.classList.add('-translate-x-full');
        sidebar.classList.remove('translate-x-0');
        overlay.classList.add('hidden');
    }
}

// Close sidebar when clicking overlay
document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('sidebar-overlay');
    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }
});
</script>
