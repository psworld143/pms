<?php
/**
 * Barcode Scanner
 * Hotel PMS Training System - Inventory Module
 */

require_once __DIR__ . '/../vps_session_fix.php';
require_once __DIR__ . '/config/database.php';

// Check if user is logged in and has manager role
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'manager') {
    header('Location: login.php?error=access_denied');
    exit();
}

// Set page title
$page_title = 'Barcode Scanner';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Hotel Inventory System</title>
    <link rel="icon" type="image/png" href="../../assets/images/seait-logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <style>
        #sidebar { transition: transform 0.3s ease-in-out; }
        @media (max-width: 1023px) { #sidebar { transform: translateX(-100%); z-index: 50; } #sidebar.sidebar-open { transform: translateX(0); } }
        @media (min-width: 1024px) { #sidebar { transform: translateX(0) !important; } }
        #sidebar-overlay { transition: opacity 0.3s ease-in-out; z-index: 40; }
        .main-content { margin-left: 0; padding-top: 4rem; }
        @media (min-width: 1024px) { .main-content { margin-left: 16rem; } }
        #qr-reader { width: 100%; max-width: 500px; margin: 0 auto; }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#10B981',
                        secondary: '#059669',
                        success: '#28a745',
                        danger: '#dc3545',
                        warning: '#ffc107',
                        info: '#17a2b8'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <!-- Sidebar Overlay for Mobile -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden" onclick="closeSidebar()"></div>
        
        <!-- Include unified inventory header and sidebar -->
        <?php include 'includes/inventory-header.php'; ?>
        <?php include 'includes/sidebar-inventory.php'; ?>

        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Barcode Scanner</h2>
                <div class="flex items-center space-x-4">
                    <button id="start-scanner-btn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-play mr-2"></i>Start Scanner
                    </button>
                    <button id="stop-scanner-btn" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium" disabled>
                        <i class="fas fa-stop mr-2"></i>Stop Scanner
                    </button>
                </div>
            </div>

            <!-- Scanner Status -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Scanner Status</h3>
                        <p class="text-sm text-gray-600" id="scanner-status">Ready to scan</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-gray-400 rounded-full mr-2" id="status-indicator"></div>
                            <span class="text-sm text-gray-600" id="status-text">Inactive</span>
                        </div>
                        <div class="text-sm text-gray-600">
                            Scanned: <span id="scanned-count">0</span> items
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scanner Interface -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Camera Scanner -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Camera Scanner</h3>
                    <div id="qr-reader" class="mb-4"></div>
                    <div class="text-center">
                        <p class="text-sm text-gray-600 mb-4">Position the barcode within the camera view</p>
                        <button id="switch-camera-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                            <i class="fas fa-camera mr-2"></i>Switch Camera
                        </button>
                    </div>
                </div>

                <!-- Manual Input -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Manual Input</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Barcode/SKU</label>
                            <input type="text" id="manual-barcode" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter barcode or SKU">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Item Name</label>
                            <input type="text" id="manual-item-name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter item name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                            <input type="number" id="manual-quantity" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter quantity" value="1">
                        </div>
                        <button id="add-manual-item-btn" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                            <i class="fas fa-plus mr-2"></i>Add Item
                        </button>
                    </div>
                </div>
            </div>

            <!-- Scanned Items -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800">Scanned Items</h3>
                        <div class="flex items-center space-x-4">
                            <button id="clear-all-btn" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                <i class="fas fa-trash mr-2"></i>Clear All
                            </button>
                            <button id="process-items-btn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                <i class="fas fa-check mr-2"></i>Process Items
                            </button>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barcode/SKU</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="scanned-items-tbody" class="bg-white divide-y divide-gray-200">
                            <!-- Scanned items will be loaded dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Include footer -->
        <?php include '../includes/pos-footer.php'; ?>
    </div>
</body>
</html>

<script>
let html5QrcodeScanner = null;
let scannedItems = [];
let scannedCount = 0;

$(document).ready(function() {
    // Button event handlers
    $('#start-scanner-btn').click(function() {
        startScanner();
    });
    
    $('#stop-scanner-btn').click(function() {
        stopScanner();
    });
    
    $('#switch-camera-btn').click(function() {
        switchCamera();
    });
    
    $('#add-manual-item-btn').click(function() {
        addManualItem();
    });
    
    $('#clear-all-btn').click(function() {
        clearAllItems();
    });
    
    $('#process-items-btn').click(function() {
        processItems();
    });
    
    function startScanner() {
        if (html5QrcodeScanner) {
            return;
        }
        
        html5QrcodeScanner = new Html5Qrcode("qr-reader");
        
        const config = {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0
        };
        
        html5QrcodeScanner.start(
            { facingMode: "environment" },
            config,
            onScanSuccess,
            onScanFailure
        ).catch(err => {
            console.error("Error starting scanner:", err);
            alert("Error starting camera scanner. Please check camera permissions.");
        });
        
        updateScannerStatus(true);
    }
    
    function stopScanner() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.stop().then(() => {
                html5QrcodeScanner.clear();
                html5QrcodeScanner = null;
                updateScannerStatus(false);
            }).catch(err => {
                console.error("Error stopping scanner:", err);
            });
        }
    }
    
    function switchCamera() {
        if (html5QrcodeScanner) {
            stopScanner();
            setTimeout(() => {
                startScanner();
            }, 500);
        }
    }
    
    function onScanSuccess(decodedText, decodedResult) {
        console.log("Barcode scanned:", decodedText);
        
        // Look up item by barcode/SKU
        lookupItem(decodedText);
        
        // Update scanned count
        scannedCount++;
        $('#scanned-count').text(scannedCount);
    }
    
    function onScanFailure(error) {
        // Handle scan failure (optional)
        console.log("Scan failed:", error);
    }
    
    function lookupItem(barcode) {
        // Simulate item lookup
        $.ajax({
            url: 'api/lookup-item-by-barcode.php',
            method: 'GET',
            data: { barcode: barcode },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.item) {
                    addScannedItem(response.item, 1);
                } else {
                    // Item not found, add with barcode only
                    addScannedItem({
                        barcode: barcode,
                        name: 'Unknown Item',
                        quantity: 1,
                        status: 'not_found'
                    }, 1);
                }
            },
            error: function() {
                // Add item with barcode only
                addScannedItem({
                    barcode: barcode,
                    name: 'Unknown Item',
                    quantity: 1,
                    status: 'not_found'
                }, 1);
            }
        });
    }
    
    function addManualItem() {
        const barcode = $('#manual-barcode').val().trim();
        const name = $('#manual-item-name').val().trim();
        const quantity = parseInt($('#manual-quantity').val()) || 1;
        
        if (!barcode && !name) {
            alert('Please enter either a barcode/SKU or item name');
            return;
        }
        
        const item = {
            barcode: barcode || 'MANUAL-' + Date.now(),
            name: name || 'Manual Item',
            quantity: quantity,
            status: 'manual'
        };
        
        addScannedItem(item, quantity);
        
        // Clear form
        $('#manual-barcode, #manual-item-name, #manual-quantity').val('');
        $('#manual-quantity').val('1');
    }
    
    function addScannedItem(item, quantity) {
        // Check if item already exists
        const existingIndex = scannedItems.findIndex(i => i.barcode === item.barcode);
        
        if (existingIndex >= 0) {
            // Update quantity
            scannedItems[existingIndex].quantity += quantity;
        } else {
            // Add new item
            scannedItems.push({
                ...item,
                quantity: quantity,
                timestamp: new Date().toISOString()
            });
        }
        
        displayScannedItems();
    }
    
    function displayScannedItems() {
        const tbody = $('#scanned-items-tbody');
        tbody.empty();
        
        if (scannedItems.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                        No items scanned yet
                    </td>
                </tr>
            `);
            return;
        }
        
        scannedItems.forEach((item, index) => {
            const statusClass = getStatusClass(item.status);
            const statusText = getStatusText(item.status);
            
            const row = `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${item.barcode}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.quantity}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                            ${statusText}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button class="text-blue-600 hover:text-blue-900 mr-3" onclick="editItem(${index})">Edit</button>
                        <button class="text-red-600 hover:text-red-900" onclick="removeItem(${index})">Remove</button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }
    
    function getStatusClass(status) {
        switch(status) {
            case 'found': return 'bg-green-100 text-green-800';
            case 'not_found': return 'bg-yellow-100 text-yellow-800';
            case 'manual': return 'bg-blue-100 text-blue-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }
    
    function getStatusText(status) {
        switch(status) {
            case 'found': return 'Found';
            case 'not_found': return 'Not Found';
            case 'manual': return 'Manual';
            default: return 'Unknown';
        }
    }
    
    function updateScannerStatus(isActive) {
        const indicator = $('#status-indicator');
        const statusText = $('#status-text');
        const scannerStatus = $('#scanner-status');
        
        if (isActive) {
            indicator.removeClass('bg-gray-400').addClass('bg-green-500');
            statusText.text('Active');
            scannerStatus.text('Camera scanner is active');
            $('#start-scanner-btn').prop('disabled', true);
            $('#stop-scanner-btn').prop('disabled', false);
        } else {
            indicator.removeClass('bg-green-500').addClass('bg-gray-400');
            statusText.text('Inactive');
            scannerStatus.text('Ready to scan');
            $('#start-scanner-btn').prop('disabled', false);
            $('#stop-scanner-btn').prop('disabled', true);
        }
    }
    
    function clearAllItems() {
        if (confirm('Are you sure you want to clear all scanned items?')) {
            scannedItems = [];
            scannedCount = 0;
            $('#scanned-count').text('0');
            displayScannedItems();
        }
    }
    
    function processItems() {
        if (scannedItems.length === 0) {
            alert('No items to process');
            return;
        }
        
        // Process items (update inventory, create transactions, etc.)
        $.ajax({
            url: 'api/process-scanned-items.php',
            method: 'POST',
            data: { items: scannedItems },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Items processed successfully!');
                    clearAllItems();
                } else {
                    alert('Error processing items: ' + response.message);
                }
            },
            error: function() {
                alert('Error processing items');
            }
        });
    }
    
    // Global functions for inline onclick handlers
    window.editItem = function(index) {
        const item = scannedItems[index];
        const newQuantity = prompt('Enter new quantity:', item.quantity);
        
        if (newQuantity !== null && !isNaN(newQuantity) && newQuantity > 0) {
            scannedItems[index].quantity = parseInt(newQuantity);
            displayScannedItems();
        }
    };
    
    window.removeItem = function(index) {
        if (confirm('Remove this item from the list?')) {
            scannedItems.splice(index, 1);
            displayScannedItems();
        }
    };
});
</script>