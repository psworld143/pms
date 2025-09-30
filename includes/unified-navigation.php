<?php
/**
 * Unified Navigation System for PMS
 * Provides consistent navigation across all modules
 */

// Include session manager
require_once __DIR__ . '/session-manager.php';

class UnifiedNavigation {
    private $session_manager;
    
    public function __construct() {
        $this->session_manager = PMSSessionManager::getInstance();
    }
    
    /**
     * Get navigation items based on user role
     */
    public function getNavigationItems() {
        if (!$this->session_manager->isLoggedIn()) {
            return [];
        }
        
        $user_role = $_SESSION['user_role'];
        
        $navigation = [
            'dashboard' => [
                'url' => '/seait/pms/booking/index.php',
                'icon' => 'fas fa-tachometer-alt',
                'label' => 'Dashboard',
                'roles' => ['manager', 'front_desk', 'housekeeping', 'pos_user', 'student']
            ],
            'booking' => [
                'url' => '/seait/pms/booking/',
                'icon' => 'fas fa-bed',
                'label' => 'Booking System',
                'roles' => ['manager', 'front_desk', 'housekeeping', 'student'],
                'submenu' => [
                    'reservations' => [
                        'url' => '/seait/pms/booking/modules/front-desk/manage-reservations.php',
                        'label' => 'Reservations',
                        'roles' => ['manager', 'front_desk', 'student']
                    ],
                    'check_in' => [
                        'url' => '/seait/pms/booking/modules/front-desk/check-in.php',
                        'label' => 'Check In',
                        'roles' => ['manager', 'front_desk', 'student']
                    ],
                    'check_out' => [
                        'url' => '/seait/pms/booking/modules/front-desk/check-out.php',
                        'label' => 'Check Out',
                        'roles' => ['manager', 'front_desk', 'student']
                    ],
                    'housekeeping' => [
                        'url' => '/seait/pms/booking/modules/housekeeping/',
                        'label' => 'Housekeeping',
                        'roles' => ['manager', 'housekeeping', 'student']
                    ]
                ]
            ],
            'inventory' => [
                'url' => '/seait/pms/inventory/',
                'icon' => 'fas fa-boxes',
                'label' => 'Inventory',
                'roles' => ['manager', 'housekeeping', 'student'],
                'submenu' => [
                    'items' => [
                        'url' => '/seait/pms/inventory/items.php',
                        'label' => 'Items',
                        'roles' => ['manager', 'housekeeping', 'student']
                    ],
                    'transactions' => [
                        'url' => '/seait/pms/inventory/transactions.php',
                        'label' => 'Transactions',
                        'roles' => ['manager', 'housekeeping', 'student']
                    ],
                    'requests' => [
                        'url' => '/seait/pms/inventory/requests.php',
                        'label' => 'Requests',
                        'roles' => ['manager', 'housekeeping', 'student']
                    ],
                    'training' => [
                        'url' => '/seait/pms/inventory/training.php',
                        'label' => 'Training',
                        'roles' => ['student']
                    ],
                    'reports' => [
                        'url' => '/seait/pms/inventory/reports.php',
                        'label' => 'Reports',
                        'roles' => ['manager', 'student']
                    ]
                ]
            ],
            'pos' => [
                'url' => '/seait/pms/pos/',
                'icon' => 'fas fa-cash-register',
                'label' => 'Point of Sale',
                'roles' => ['manager', 'front_desk', 'pos_user', 'student'],
                'submenu' => [
                    'restaurant' => [
                        'url' => '/seait/pms/pos/restaurant/',
                        'label' => 'Restaurant',
                        'roles' => ['manager', 'front_desk', 'pos_user', 'student']
                    ],
                    'room_service' => [
                        'url' => '/seait/pms/pos/room-service/',
                        'label' => 'Room Service',
                        'roles' => ['manager', 'front_desk', 'pos_user', 'student']
                    ],
                    'spa' => [
                        'url' => '/seait/pms/pos/spa/',
                        'label' => 'Spa Services',
                        'roles' => ['manager', 'front_desk', 'pos_user', 'student']
                    ],
                    'events' => [
                        'url' => '/seait/pms/pos/events/',
                        'label' => 'Events',
                        'roles' => ['manager', 'front_desk', 'pos_user', 'student']
                    ]
                ]
            ],
            'reports' => [
                'url' => '/seait/pms/booking/modules/management/reports-dashboard.php',
                'icon' => 'fas fa-chart-bar',
                'label' => 'Reports',
                'roles' => ['manager', 'student']
            ],
            'training' => [
                'url' => '/seait/pms/booking/modules/student/',
                'icon' => 'fas fa-graduation-cap',
                'label' => 'Training',
                'roles' => ['student'],
                'submenu' => [
                    'scenarios' => [
                        'url' => '/seait/pms/booking/modules/student/scenarios.php',
                        'label' => 'Scenarios',
                        'roles' => ['student']
                    ],
                    'progress' => [
                        'url' => '/seait/pms/booking/modules/student/progress.php',
                        'label' => 'Progress',
                        'roles' => ['student']
                    ],
                    'certificates' => [
                        'url' => '/seait/pms/booking/modules/student/certificates.php',
                        'label' => 'Certificates',
                        'roles' => ['student']
                    ]
                ]
            ]
        ];
        
        // Filter navigation based on user role
        $filtered_navigation = [];
        foreach ($navigation as $key => $item) {
            if (in_array($user_role, $item['roles'])) {
                $filtered_item = $item;
                
                // Filter submenu if exists
                if (isset($item['submenu'])) {
                    $filtered_submenu = [];
                    foreach ($item['submenu'] as $subkey => $subitem) {
                        if (in_array($user_role, $subitem['roles'])) {
                            $filtered_submenu[$subkey] = $subitem;
                        }
                    }
                    $filtered_item['submenu'] = $filtered_submenu;
                }
                
                $filtered_navigation[$key] = $filtered_item;
            }
        }
        
        return $filtered_navigation;
    }
    
    /**
     * Render navigation HTML
     */
    public function renderNavigation() {
        $navigation = $this->getNavigationItems();
        $current_page = basename($_SERVER['PHP_SELF'], '.php');
        
        echo '<nav class="bg-white shadow-sm">';
        echo '<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">';
        echo '<div class="flex space-x-8">';
        
        foreach ($navigation as $key => $item) {
            $is_active = $this->isActiveItem($item, $current_page);
            $active_class = $is_active ? 'border-b-2 border-primary text-primary' : 'text-gray-500 hover:text-gray-700';
            
            echo '<div class="relative group">';
            echo '<a href="' . $item['url'] . '" class="' . $active_class . ' py-4 px-1 text-sm font-medium flex items-center">';
            echo '<i class="' . $item['icon'] . ' mr-2"></i>';
            echo $item['label'];
            if (isset($item['submenu']) && !empty($item['submenu'])) {
                echo '<i class="fas fa-chevron-down ml-1"></i>';
            }
            echo '</a>';
            
            // Render submenu if exists
            if (isset($item['submenu']) && !empty($item['submenu'])) {
                echo '<div class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">';
                echo '<div class="py-1">';
                foreach ($item['submenu'] as $subkey => $subitem) {
                    echo '<a href="' . $subitem['url'] . '" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">';
                    echo $subitem['label'];
                    echo '</a>';
                }
                echo '</div>';
                echo '</div>';
            }
            
            echo '</div>';
        }
        
        echo '</div>';
        echo '</div>';
        echo '</nav>';
    }
    
    /**
     * Check if navigation item is active
     */
    private function isActiveItem($item, $current_page) {
        // Check main item
        if (strpos($_SERVER['REQUEST_URI'], $item['url']) !== false) {
            return true;
        }
        
        // Check submenu items
        if (isset($item['submenu'])) {
            foreach ($item['submenu'] as $subitem) {
                if (strpos($_SERVER['REQUEST_URI'], $subitem['url']) !== false) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Get breadcrumb navigation
     */
    public function getBreadcrumbs() {
        $breadcrumbs = [
            ['label' => 'Home', 'url' => '/seait/pms/booking/index.php']
        ];
        
        $current_url = $_SERVER['REQUEST_URI'];
        
        // Add module-specific breadcrumbs
        if (strpos($current_url, '/inventory/') !== false) {
            $breadcrumbs[] = ['label' => 'Inventory', 'url' => '/seait/pms/inventory/'];
        } elseif (strpos($current_url, '/pos/') !== false) {
            $breadcrumbs[] = ['label' => 'POS', 'url' => '/seait/pms/pos/'];
        }
        
        // Add current page
        $current_page = basename($_SERVER['PHP_SELF'], '.php');
        $breadcrumbs[] = ['label' => ucfirst(str_replace('-', ' ', $current_page)), 'url' => ''];
        
        return $breadcrumbs;
    }
    
    /**
     * Render breadcrumb HTML
     */
    public function renderBreadcrumbs() {
        $breadcrumbs = $this->getBreadcrumbs();
        
        echo '<nav class="flex" aria-label="Breadcrumb">';
        echo '<ol class="inline-flex items-center space-x-1 md:space-x-3">';
        
        foreach ($breadcrumbs as $index => $breadcrumb) {
            if ($index === 0) {
                echo '<li class="inline-flex items-center">';
                echo '<a href="' . $breadcrumb['url'] . '" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary">';
                echo '<i class="fas fa-home mr-2"></i>';
                echo $breadcrumb['label'];
                echo '</a>';
                echo '</li>';
            } else {
                echo '<li>';
                echo '<div class="flex items-center">';
                echo '<i class="fas fa-chevron-right text-gray-400 mx-2"></i>';
                if (!empty($breadcrumb['url'])) {
                    echo '<a href="' . $breadcrumb['url'] . '" class="ml-1 text-sm font-medium text-gray-700 hover:text-primary md:ml-2">';
                    echo $breadcrumb['label'];
                    echo '</a>';
                } else {
                    echo '<span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">';
                    echo $breadcrumb['label'];
                    echo '</span>';
                }
                echo '</div>';
                echo '</li>';
            }
        }
        
        echo '</ol>';
        echo '</nav>';
    }
}

// Initialize navigation
$navigation = new UnifiedNavigation();
?>
