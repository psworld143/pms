<?php
session_start();

// Set test session variables if not set
if (!isset($_SESSION['pos_user_id'])) {
    $_SESSION['pos_user_id'] = 999;
    $_SESSION['pos_user_name'] = 'Test User';
    $_SESSION['pos_user_role'] = 'manager';
    $_SESSION['pos_demo_mode'] = true;
}

$page_title = 'Navigation Test';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Hotel POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#667eea',
                        secondary: '#764ba2',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <?php include 'includes/pos-header.php'; ?>
        <?php include 'includes/pos-sidebar.php'; ?>
        
        <main class="main-content pt-20 px-4 pb-4 lg:px-6 lg:pb-6 flex-1 transition-all duration-300">
            <div class="mb-6">
                <h2 class="text-3xl font-semibold text-gray-800 mb-4">POS Navigation Test Page</h2>
                <p class="text-gray-600">Use this page to test the sidebar navigation</p>
            </div>

            <!-- Test Instructions -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Test Instructions</h3>
                <ol class="list-decimal list-inside space-y-2 text-gray-700">
                    <li>Open your browser's Developer Tools (Press F12)</li>
                    <li>Click on the "Console" tab</li>
                    <li>Click on "Restaurant POS" in the sidebar</li>
                    <li>Watch the console for messages</li>
                    <li>The submenu should expand showing "Menu Management"</li>
                    <li>Click on "Menu Management"</li>
                    <li>You should navigate to the Menu Management page</li>
                </ol>
            </div>

            <!-- Navigation Links Test -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Quick Navigation Links</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <a href="index.php" class="block p-4 border border-gray-300 rounded-lg hover:bg-blue-50 hover:border-blue-500 transition-colors">
                        <i class="fas fa-tachometer-alt text-blue-600 mr-2"></i>
                        <strong>POS Dashboard</strong>
                        <p class="text-sm text-gray-600 mt-1">Should show "Point of Sale Dashboard"</p>
                    </a>
                    
                    <a href="restaurant/index.php" class="block p-4 border border-gray-300 rounded-lg hover:bg-orange-50 hover:border-orange-500 transition-colors">
                        <i class="fas fa-utensils text-orange-600 mr-2"></i>
                        <strong>Restaurant POS</strong>
                        <p class="text-sm text-gray-600 mt-1">Should show "Restaurant POS System"</p>
                    </a>
                    
                    <a href="restaurant/menu.php" class="block p-4 border border-green-300 rounded-lg hover:bg-green-50 hover:border-green-500 transition-colors bg-green-50">
                        <i class="fas fa-list text-green-600 mr-2"></i>
                        <strong>Menu Management</strong>
                        <p class="text-sm text-gray-600 mt-1">Should show "Restaurant Menu Management"</p>
                    </a>
                    
                    <a href="restaurant/orders.php" class="block p-4 border border-gray-300 rounded-lg hover:bg-blue-50 hover:border-blue-500 transition-colors">
                        <i class="fas fa-receipt text-blue-600 mr-2"></i>
                        <strong>Active Orders</strong>
                        <p class="text-sm text-gray-600 mt-1">Restaurant orders page</p>
                    </a>
                    
                    <a href="restaurant/tables.php" class="block p-4 border border-gray-300 rounded-lg hover:bg-purple-50 hover:border-purple-500 transition-colors">
                        <i class="fas fa-chair text-purple-600 mr-2"></i>
                        <strong>Table Management</strong>
                        <p class="text-sm text-gray-600 mt-1">Restaurant tables page</p>
                    </a>
                    
                    <a href="restaurant/reports.php" class="block p-4 border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-500 transition-colors">
                        <i class="fas fa-chart-bar text-gray-600 mr-2"></i>
                        <strong>Restaurant Reports</strong>
                        <p class="text-sm text-gray-600 mt-1">Restaurant reports page</p>
                    </a>
                </div>
            </div>

            <!-- Sidebar State Test -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Sidebar State Test</h3>
                <button onclick="testSidebarFunctions()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors mb-4">
                    Run Sidebar Tests
                </button>
                <div id="test-results" class="bg-gray-50 rounded-lg p-4 font-mono text-sm"></div>
            </div>

            <!-- Console Monitor -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Console Monitor</h3>
                <p class="text-gray-600 mb-2">Recent console messages will appear here:</p>
                <div id="console-monitor" class="bg-gray-900 text-green-400 rounded-lg p-4 font-mono text-sm min-h-[200px] max-h-[400px] overflow-y-auto">
                    <div class="text-gray-500">Waiting for console activity...</div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Console interceptor
        const consoleMonitor = document.getElementById('console-monitor');
        const originalLog = console.log;
        const originalError = console.error;
        
        function addToMonitor(message, type = 'log') {
            const timestamp = new Date().toLocaleTimeString();
            const color = type === 'error' ? 'text-red-400' : 'text-green-400';
            const entry = document.createElement('div');
            entry.className = color;
            entry.textContent = `[${timestamp}] ${message}`;
            consoleMonitor.appendChild(entry);
            consoleMonitor.scrollTop = consoleMonitor.scrollHeight;
        }
        
        console.log = function(...args) {
            originalLog.apply(console, args);
            addToMonitor(args.join(' '), 'log');
        };
        
        console.error = function(...args) {
            originalError.apply(console, args);
            addToMonitor(args.join(' '), 'error');
        };
        
        // Test sidebar functions
        function testSidebarFunctions() {
            const results = document.getElementById('test-results');
            results.innerHTML = '';
            
            function addResult(message, success = true) {
                const color = success ? 'text-green-600' : 'text-red-600';
                results.innerHTML += `<div class="${color}">${success ? '✓' : '✗'} ${message}</div>`;
            }
            
            // Test 1: Check if toggleSubmenu exists
            if (typeof window.toggleSubmenu === 'function') {
                addResult('toggleSubmenu function exists');
            } else {
                addResult('toggleSubmenu function NOT found', false);
            }
            
            // Test 2: Check if sidebar exists
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                addResult('Sidebar element found');
            } else {
                addResult('Sidebar element NOT found', false);
            }
            
            // Test 3: Check if restaurant submenu exists
            const restaurantSubmenu = document.getElementById('submenu-restaurant');
            if (restaurantSubmenu) {
                addResult('Restaurant submenu element found');
                addResult(`Submenu is currently: ${restaurantSubmenu.classList.contains('hidden') ? 'HIDDEN' : 'VISIBLE'}`);
            } else {
                addResult('Restaurant submenu element NOT found', false);
            }
            
            // Test 4: Check if chevron exists
            const chevron = document.getElementById('chevron-restaurant');
            if (chevron) {
                addResult('Chevron element found');
            } else {
                addResult('Chevron element NOT found', false);
            }
            
            // Test 5: Test toggle function
            if (typeof window.toggleSubmenu === 'function' && restaurantSubmenu) {
                results.innerHTML += '<div class="mt-2 text-blue-600">Attempting to toggle submenu...</div>';
                try {
                    window.toggleSubmenu('restaurant');
                    setTimeout(() => {
                        if (!restaurantSubmenu.classList.contains('hidden')) {
                            addResult('Submenu successfully opened!');
                            addResult('You should now see the submenu links in the sidebar');
                        } else {
                            addResult('Submenu is still hidden', false);
                        }
                    }, 100);
                } catch (error) {
                    addResult(`Error toggling submenu: ${error.message}`, false);
                }
            }
        }
        
        // Auto-run tests on load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Page loaded - Navigation Test');
            console.log('Current URL:', window.location.href);
        });
    </script>

    <?php include 'includes/pos-footer.php'; ?>
</body>
</html>

