<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Inventory Integration Test Page
 * Tests the connection between booking and inventory systems
 */

session_start();
require_once '../../../includes/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/booking-paths.php';

booking_initialize_paths();

// Check if user is logged in and has manager access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    header('Location: ' . booking_base() . 'login.php');
    exit(); }
// Set page title
$page_title = 'Inventory Integration Test';

// Include unified navigation
include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';
?>

<!-- Main Content -->
<main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
    <div class="flex justify-between items-center mb-8">
        <h2 class="text-3xl font-semibold text-gray-800">Inventory Integration Test</h2>
    </div>

    <!-- Integration Status -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg p-6 shadow-md">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Database Connection Test</h3>
            <div id="db-test-results" class="space-y-2">
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-gray-300 rounded-full mr-3" id="db-status"></div>
                    <span>Testing database connection...</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 shadow-md">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">API Endpoints Test</h3>
            <div id="api-test-results" class="space-y-2">
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-gray-300 rounded-full mr-3" id="api-status"></div>
                    <span>Testing API endpoints...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Status -->
    <div class="bg-white rounded-lg p-6 shadow-md mb-8">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Current Inventory Status</h3>
        <div id="inventory-status" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="text-center p-4 bg-gray-50 rounded">
                <div class="text-2xl font-bold text-gray-800" id="total-items">-</div>
                <div class="text-sm text-gray-600">Total Items</div>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded">
                <div class="text-2xl font-bold text-red-600" id="low-stock-items">-</div>
                <div class="text-sm text-gray-600">Low Stock Items</div>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded">
                <div class="text-2xl font-bold text-blue-600" id="total-value">-</div>
                <div class="text-sm text-gray-600">Total Value</div>
            </div>
        </div>
    </div>

    <!-- Room Inventory Test -->
    <div class="bg-white rounded-lg p-6 shadow-md mb-8">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Room Inventory Test</h3>
        <div class="flex items-center space-x-4 mb-4">
            <select id="room-select" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Select a room...</option>
            </select>
            <button id="load-room-inventory" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50" disabled>
                Load Room Inventory
            </button>
        </div>
        <div id="room-inventory-results" class="hidden">
            <h4 class="font-semibold text-gray-700 mb-2">Room Inventory Items:</h4>
            <div id="room-items-list" class="space-y-2"></div>
        </div>
    </div>

    <!-- Integration Actions -->
    <div class="bg-white rounded-lg p-6 shadow-md">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Integration Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <button id="sync-booking-inventory" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="fas fa-sync mr-2"></i>
                Sync Booking & Inventory
            </button>
            <button id="test-inventory-api" class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                <i class="fas fa-flask mr-2"></i>
                Test Inventory API
            </button>
        </div>
        <div id="integration-results" class="mt-4 hidden">
            <h4 class="font-semibold text-gray-700 mb-2">Results:</h4>
            <div id="integration-output" class="bg-gray-50 p-4 rounded text-sm font-mono"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Test database connection
    testDatabaseConnection();
    
    // Test API endpoints
    testApiEndpoints();
    
    // Load inventory status
    loadInventoryStatus();
    
    // Load rooms for testing
    loadRooms();
    
    // Add event listeners
    document.getElementById('load-room-inventory').addEventListener('click', loadRoomInventory);
    document.getElementById('sync-booking-inventory').addEventListener('click', syncBookingInventory);
    document.getElementById('test-inventory-api').addEventListener('click', testInventoryApi);
    document.getElementById('room-select').addEventListener('change', function() {
        document.getElementById('load-room-inventory').disabled = !this.value;
    });
});

async function testDatabaseConnection() {
    try {
        const response = await fetch('../../api/inventory-integration.php?action=get_inventory_status');
        const data = await response.json();
        
        const statusEl = document.getElementById('db-status');
        if (data.success) {
            statusEl.className = 'w-4 h-4 bg-green-500 rounded-full mr-3';
            statusEl.nextElementSibling.textContent = 'Database connection successful';
        } else {
            statusEl.className = 'w-4 h-4 bg-red-500 rounded-full mr-3';
            statusEl.nextElementSibling.textContent = 'Database connection failed: ' + data.message; }
    } catch (error) {
        const statusEl = document.getElementById('db-status');
        statusEl.className = 'w-4 h-4 bg-red-500 rounded-full mr-3';
        statusEl.nextElementSibling.textContent = 'Database connection failed: ' + error.message; }
}
async function testApiEndpoints() {
    try {
        const response = await fetch('../../api/inventory-integration.php?action=get_low_stock_alerts');
        const data = await response.json();
        
        const statusEl = document.getElementById('api-status');
        if (data.success) {
            statusEl.className = 'w-4 h-4 bg-green-500 rounded-full mr-3';
            statusEl.nextElementSibling.textContent = 'API endpoints working (' + data.alerts.length + ' alerts)';
        } else {
            statusEl.className = 'w-4 h-4 bg-red-500 rounded-full mr-3';
            statusEl.nextElementSibling.textContent = 'API endpoints failed: ' + data.message; }
    } catch (error) {
        const statusEl = document.getElementById('api-status');
        statusEl.className = 'w-4 h-4 bg-red-500 rounded-full mr-3';
        statusEl.nextElementSibling.textContent = 'API endpoints failed: ' + error.message; }
}
async function loadInventoryStatus() {
    try {
        const response = await fetch('../../api/inventory-integration.php?action=get_inventory_status');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('total-items').textContent = data.total_items || '0';
            document.getElementById('low-stock-items').textContent = data.low_stock_items || '0';
            document.getElementById('total-value').textContent = '₱' + (data.total_inventory_value || '0.00'); }
    } catch (error) {
        console.error(); }
}
async function loadRooms() {
    try {
        const response = await fetch('../../api/get-rooms.php');
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('room-select');
            data.rooms.forEach(room => {
                const option = document.createElement('option');
                option.value = room.id;
                option.textContent = `Room ${room.room_number} - ${room.room_type}`;
                select.appendChild(option);
            }); }
    } catch (error) {
        console.error(); }
}
async function loadRoomInventory() {
    const roomId = document.getElementById('room-select').value;
    if (!roomId) return;
    
    try {
        const response = await fetch(`../../api/inventory-integration.php?action=get_room_inventory&room_id=${roomId}`);
        const data = await response.json();
        
        const resultsDiv = document.getElementById('room-inventory-results');
        const itemsList = document.getElementById('room-items-list');
        
        if (data.success) {
            resultsDiv.classList.remove('hidden');
            
            if (data.items.length === 0) {
                itemsList.innerHTML = '<div class="text-gray-500">No inventory items assigned to this room</div>';
            } else {
                itemsList.innerHTML = ; }
        } else {
            resultsDiv.classList.remove('hidden');
            itemsList.innerHTML = ; }
    } catch (error) {
        console.error(); }
}
async function syncBookingInventory() {
    try {
        const response = await fetch('../../api/inventory-integration.php?action=sync_booking_inventory');
        const data = await response.json();
        
        const resultsDiv = document.getElementById('integration-results');
        const outputDiv = document.getElementById('integration-output');
        
        resultsDiv.classList.remove('hidden');
        outputDiv.textContent = JSON.stringify(data, null, 2);
    } catch (error) {
        console.error(); }
}
async function testInventoryApi() {
    try {
        const response = await fetch('../../inventory/api/index.php');
        const data = await response.json();
        
        const resultsDiv = document.getElementById('integration-results');
        const outputDiv = document.getElementById('integration-output');
        
        resultsDiv.classList.remove('hidden');
        outputDiv.textContent = JSON.stringify(data, null, 2);
    } catch (error) {
        console.error(); }
}
</script>

<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); include '../../includes/footer.php'; ?>
</body>
</html>
