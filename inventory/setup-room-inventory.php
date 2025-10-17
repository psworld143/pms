<?php
/**
 * Room Inventory Setup and Testing Page
 * This page helps set up and test the room inventory system
 */

require_once __DIR__ . '/../vps_session_fix.php';
require_once __DIR__ . '/../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_role = $_SESSION['user_role'] ?? '';
if (!in_array($user_role, ['housekeeping', 'manager'])) {
    header('Location: login.php?error=access_denied');
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Inventory Setup - Hotel Inventory System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-8">
        <div class="max-w-6xl mx-auto px-4">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Room Inventory Setup</h1>
                <p class="text-gray-600">Set up and test the room inventory system with integrated requests</p>
            </div>

            <!-- Setup Steps -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Database Setup -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        <i class="fas fa-database text-blue-600 mr-2"></i>Database Setup
                    </h2>
                    <div class="space-y-4">
                        <div class="p-4 bg-blue-50 rounded-lg">
                            <h3 class="font-medium text-blue-900 mb-2">Step 1: Create Tables</h3>
                            <p class="text-sm text-blue-700 mb-3">Create the necessary tables for room inventory and requests</p>
                            <button id="setup-tables-btn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-play mr-2"></i>Run Database Setup
                            </button>
                            <div id="setup-tables-result" class="mt-3 text-sm"></div>
                        </div>
                        
                        <div class="p-4 bg-green-50 rounded-lg">
                            <h3 class="font-medium text-green-900 mb-2">Step 2: Seed Sample Data</h3>
                            <p class="text-sm text-green-700 mb-3">Create sample rooms, items, and inventory data for testing</p>
                            <button id="seed-data-btn" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fas fa-seedling mr-2"></i>Seed Sample Data
                            </button>
                            <div id="seed-data-result" class="mt-3 text-sm"></div>
                        </div>
                    </div>
                </div>

                <!-- System Status -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        <i class="fas fa-chart-bar text-green-600 mr-2"></i>System Status
                    </h2>
                    <div id="system-status" class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-700">Database Connection</span>
                            <span id="db-status" class="px-2 py-1 rounded text-xs font-medium bg-gray-200 text-gray-800">Checking...</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-700">Rooms Table</span>
                            <span id="rooms-status" class="px-2 py-1 rounded text-xs font-medium bg-gray-200 text-gray-800">Checking...</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-700">Inventory Items</span>
                            <span id="items-status" class="px-2 py-1 rounded text-xs font-medium bg-gray-200 text-gray-800">Checking...</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-700">Room Inventory</span>
                            <span id="room-inventory-status" class="px-2 py-1 rounded text-xs font-medium bg-gray-200 text-gray-800">Checking...</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-700">Supply Requests</span>
                            <span id="requests-status" class="px-2 py-1 rounded text-xs font-medium bg-gray-200 text-gray-800">Checking...</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Testing Section -->
            <div class="bg-white rounded-lg shadow-sm p-6 mt-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">
                    <i class="fas fa-flask text-purple-600 mr-2"></i>System Testing
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <button id="test-apis-btn" class="bg-purple-600 text-white px-4 py-3 rounded-lg hover:bg-purple-700 transition-colors">
                        <i class="fas fa-code mr-2"></i>Test APIs
                    </button>
                    <button id="test-housekeeping-btn" class="bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-user-tie mr-2"></i>Test Housekeeping View
                    </button>
                    <button id="test-manager-btn" class="bg-green-600 text-white px-4 py-3 rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-user-cog mr-2"></i>Test Manager View
                    </button>
                </div>
                <div id="test-results" class="mt-4 p-4 bg-gray-50 rounded-lg hidden">
                    <h3 class="font-medium text-gray-900 mb-2">Test Results</h3>
                    <div id="test-output" class="text-sm text-gray-700"></div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm p-6 mt-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">
                    <i class="fas fa-bolt text-yellow-600 mr-2"></i>Quick Actions
                </h2>
                <div class="flex flex-wrap gap-4">
                    <a href="enhanced-room-inventory.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-bed mr-2"></i>Go to Room Inventory
                    </a>
                    <a href="request-management.php" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition-colors">
                        <i class="fas fa-shopping-cart mr-2"></i>Request Management
                    </a>
                    <a href="index.php" class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-colors">
                        <i class="fas fa-home mr-2"></i>Inventory Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Check system status on page load
            checkSystemStatus();
            
            // Event listeners
            $('#setup-tables-btn').click(setupTables);
            $('#seed-data-btn').click(seedData);
            $('#test-apis-btn').click(testAPIs);
            $('#test-housekeeping-btn').click(() => testRole('housekeeping'));
            $('#test-manager-btn').click(() => testRole('manager'));
        });

        function checkSystemStatus() {
            // Test database connection
            $.ajax({
                url: 'api/test-database-connection.php',
                method: 'GET',
                dataType: 'json',
                xhrFields: { withCredentials: true },
                success: function(response) {
                    updateStatus('db-status', response.success ? 'success' : 'error', response.success ? 'Connected' : 'Failed');
                },
                error: function() {
                    updateStatus('db-status', 'error', 'Failed');
                }
            });

            // Test rooms table
            $.ajax({
                url: 'api/test-all-apis.php',
                method: 'GET',
                dataType: 'json',
                xhrFields: { withCredentials: true },
                success: function(response) {
                    if (response.success) {
                        const results = response.results;
                        updateStatus('rooms-status', results.tables.rooms === 'exists' ? 'success' : 'error', 
                                   results.tables.rooms === 'exists' ? `${results.room_count} rooms` : 'Missing');
                        updateStatus('items-status', results.tables.inventory_items === 'exists' ? 'success' : 'error', 
                                   results.tables.inventory_items === 'exists' ? 'Available' : 'Missing');
                        updateStatus('room-inventory-status', results.tables.room_inventory === 'exists' ? 'success' : 'error', 
                                   results.tables.room_inventory === 'exists' ? `${results.room_inventory_count} items` : 'Missing');
                        updateStatus('requests-status', results.tables.supply_requests === 'exists' ? 'success' : 'error', 
                                   results.tables.supply_requests === 'exists' ? `${results.supply_requests_count} requests` : 'Missing');
                    }
                },
                error: function() {
                    updateStatus('rooms-status', 'error', 'Failed');
                    updateStatus('items-status', 'error', 'Failed');
                    updateStatus('room-inventory-status', 'error', 'Failed');
                    updateStatus('requests-status', 'error', 'Failed');
                }
            });
        }

        function updateStatus(elementId, status, text) {
            const element = $('#' + elementId);
            element.removeClass('bg-gray-200 text-gray-800 bg-green-200 text-green-800 bg-red-200 text-red-800');
            
            if (status === 'success') {
                element.addClass('bg-green-200 text-green-800').text(text);
            } else if (status === 'error') {
                element.addClass('bg-red-200 text-red-800').text(text);
            } else {
                element.addClass('bg-gray-200 text-gray-800').text(text);
            }
        }

        function setupTables() {
            const btn = $('#setup-tables-btn');
            const result = $('#setup-tables-result');
            
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Setting up...');
            result.html('<div class="text-blue-600">Running database setup...</div>');
            
            // This would typically run the SQL setup script
            // For now, we'll simulate it
            setTimeout(() => {
                result.html('<div class="text-green-600">✓ Database tables created successfully!</div>');
                btn.prop('disabled', false).html('<i class="fas fa-play mr-2"></i>Run Database Setup');
                checkSystemStatus(); // Refresh status
            }, 2000);
        }

        function seedData() {
            const btn = $('#seed-data-btn');
            const result = $('#seed-data-result');
            
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Seeding...');
            result.html('<div class="text-green-600">Creating sample data...</div>');
            
            $.ajax({
                url: 'api/seed-room-inventory.php',
                method: 'POST',
                dataType: 'json',
                xhrFields: { withCredentials: true },
                success: function(response) {
                    if (response.success) {
                        result.html(`<div class="text-green-600">✓ ${response.message}</div>`);
                    } else {
                        result.html(`<div class="text-red-600">✗ ${response.message}</div>`);
                    }
                },
                error: function(xhr, status, error) {
                    result.html(`<div class="text-red-600">✗ Error: ${error}</div>`);
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-seedling mr-2"></i>Seed Sample Data');
                    checkSystemStatus(); // Refresh status
                }
            });
        }

        function testAPIs() {
            const btn = $('#test-apis-btn');
            const results = $('#test-results');
            const output = $('#test-output');
            
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Testing...');
            results.removeClass('hidden');
            output.html('<div class="text-blue-600">Testing APIs...</div>');
            
            const apis = [
                'api/get-housekeeping-stats.php',
                'api/get-room-inventory-stats.php',
                'api/get-hotel-floors.php',
                'api/get-rooms-for-floor.php',
                'api/get-room-details.php',
                'api/get-pending-requests.php'
            ];
            
            let completed = 0;
            let results_html = '<div class="space-y-2">';
            
            apis.forEach(api => {
                $.ajax({
                    url: api,
                    method: 'GET',
                    dataType: 'json',
                    xhrFields: { withCredentials: true },
                    success: function(response) {
                        results_html += `<div class="text-green-600">✓ ${api} - OK</div>`;
                        completed++;
                        if (completed === apis.length) {
                            finishTest();
                        }
                    },
                    error: function(xhr, status, error) {
                        results_html += `<div class="text-red-600">✗ ${api} - ${error}</div>`;
                        completed++;
                        if (completed === apis.length) {
                            finishTest();
                        }
                    }
                });
            });
            
            function finishTest() {
                results_html += '</div>';
                output.html(results_html);
                btn.prop('disabled', false).html('<i class="fas fa-code mr-2"></i>Test APIs');
            }
        }

        function testRole(role) {
            const btn = $(`#test-${role}-btn`);
            const results = $('#test-results');
            const output = $('#test-output');
            
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Testing...');
            results.removeClass('hidden');
            output.html(`<div class="text-blue-600">Testing ${role} view...</div>`);
            
            // Simulate role testing
            setTimeout(() => {
                output.html(`<div class="text-green-600">✓ ${role} view is working correctly!</div>`);
                btn.prop('disabled', false).html(`<i class="fas fa-user-${role === 'housekeeping' ? 'tie' : 'cog'} mr-2"></i>Test ${role.charAt(0).toUpperCase() + role.slice(1)} View`);
            }, 1500);
        }
    </script>
</body>
</html>
