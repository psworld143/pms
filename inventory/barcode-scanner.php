<?php
/**
 * Barcode Scanner Interface
 * Hotel PMS Training System - Inventory Module
 */

session_start();
require_once __DIR__ . '/config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
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
    <script src="https://unpkg.com/quagga@0.12.1/dist/quagga.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        #sidebar { transition: transform 0.3s ease-in-out; }
        @media (max-width: 1023px) { #sidebar { transform: translateX(-100%); z-index: 50; } #sidebar.sidebar-open { transform: translateX(0); } }
        @media (min-width: 1024px) { #sidebar { transform: translateX(0) !important; } }
        #sidebar-overlay { transition: opacity 0.3s ease-in-out; z-index: 40; }
        .main-content { margin-left: 0; padding-top: 4rem; }
        @media (min-width: 1024px) { .main-content { margin-left: 16rem; } }
        
        #scanner-container {
            position: relative;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }
        
        #scanner {
            width: 100%;
            height: 300px;
            background: #000;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .scanner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            z-index: 10;
        }
        
        .scanner-line {
            position: absolute;
            top: 50%;
            left: 20%;
            right: 20%;
            height: 2px;
            background: #00ff00;
            animation: scan 2s linear infinite;
        }
        
        @keyframes scan {
            0% { top: 20%; }
            100% { top: 80%; }
        }
        
        .scanner-corners {
            position: absolute;
            top: 20%;
            left: 20%;
            right: 20%;
            bottom: 20%;
            border: 2px solid #00ff00;
            border-radius: 8px;
        }
        
        .scanner-corners::before,
        .scanner-corners::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 3px solid #00ff00;
        }
        
        .scanner-corners::before {
            top: -3px;
            left: -3px;
            border-right: none;
            border-bottom: none;
        }
        
        .scanner-corners::after {
            bottom: -3px;
            right: -3px;
            border-left: none;
            border-top: none;
        }
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
                    <button id="generate-barcodes-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-barcode mr-2"></i>Generate Barcodes
                    </button>
                    <button id="print-barcodes-btn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-print mr-2"></i>Print Barcodes
                    </button>
                </div>
            </div>

            <!-- Scanner Interface -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Barcode Scanner</h3>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Scanner Area -->
                    <div>
                        <div id="scanner-container">
                            <div id="scanner">
                                <div class="scanner-overlay">
                                    <div class="scanner-corners"></div>
                                    <div class="scanner-line"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <button id="start-scanner-btn" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium mr-4">
                                <i class="fas fa-play mr-2"></i>Start Scanner
                            </button>
                            <button id="stop-scanner-btn" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-medium" disabled>
                                <i class="fas fa-stop mr-2"></i>Stop Scanner
                            </button>
                        </div>
                        
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Manual Barcode Entry</label>
                            <div class="flex">
                                <input type="text" id="manual-barcode" class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter barcode manually">
                                <button id="manual-scan-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-r-md">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Scan Results -->
                    <div>
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Scan Results</h4>
                        <div id="scan-results" class="space-y-4">
                            <div class="text-center text-gray-500 py-8">
                                <i class="fas fa-qrcode text-4xl mb-4"></i>
                                <p>No barcodes scanned yet</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Scans -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Scans</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barcode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scanned By</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="recent-scans-tbody" class="bg-white divide-y divide-gray-200">
                            <!-- Recent scans will be loaded dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Barcode Management -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Barcode Management</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barcode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Scanned</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="barcode-management-tbody" class="bg-white divide-y divide-gray-200">
                            <!-- Barcode management data will be loaded dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Include footer -->
        <?php include '../includes/pos-footer.php'; ?>
    </body>
</html>

<script>
$(document).ready(function() {
    let scannerActive = false;
    let recentScans = [];
    
    // Load initial data
    loadRecentScans();
    loadBarcodeManagement();
    
    // Event handlers
    $('#start-scanner-btn').click(function() {
        startScanner();
    });
    
    $('#stop-scanner-btn').click(function() {
        stopScanner();
    });
    
    $('#manual-scan-btn').click(function() {
        const barcode = $('#manual-barcode').val().trim();
        if (barcode) {
            processBarcode(barcode);
            $('#manual-barcode').val('');
        }
    });
    
    $('#manual-barcode').keypress(function(e) {
        if (e.which === 13) {
            $('#manual-scan-btn').click();
        }
    });
    
    $('#generate-barcodes-btn').click(function() {
        generateBarcodes();
    });
    
    $('#print-barcodes-btn').click(function() {
        printBarcodes();
    });
    
    function startScanner() {
        if (scannerActive) return;
        
        Quagga.init({
            inputStream: {
                name: "Live",
                type: "LiveStream",
                target: document.querySelector('#scanner'),
                constraints: {
                    width: 500,
                    height: 300,
                    facingMode: "environment"
                }
            },
            decoder: {
                readers: [
                    "code_128_reader",
                    "ean_reader",
                    "ean_8_reader",
                    "code_39_reader",
                    "code_39_vin_reader",
                    "codabar_reader",
                    "upc_reader",
                    "upc_e_reader",
                    "i2of5_reader"
                ]
            },
            locate: true,
            locator: {
                patchSize: "medium",
                halfSample: true
            }
        }, function(err) {
            if (err) {
                console.error('Scanner initialization error:', err);
                alert('Failed to initialize scanner. Please check camera permissions.');
                return;
            }
            
            Quagga.start();
            scannerActive = true;
            $('#start-scanner-btn').prop('disabled', true);
            $('#stop-scanner-btn').prop('disabled', false);
            
            // Listen for successful scans
            Quagga.onDetected(function(result) {
                const barcode = result.codeResult.code;
                processBarcode(barcode);
            });
        });
    }
    
    function stopScanner() {
        if (!scannerActive) return;
        
        Quagga.stop();
        scannerActive = false;
        $('#start-scanner-btn').prop('disabled', false);
        $('#stop-scanner-btn').prop('disabled', true);
    }
    
    function processBarcode(barcode) {
        $.ajax({
            url: 'api/process-barcode.php',
            method: 'POST',
            data: { barcode: barcode },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayScanResult(response.item);
                    addToRecentScans(response.item);
                    loadRecentScans();
                    loadBarcodeManagement();
                } else {
                    displayScanError(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error processing barcode:', error);
                displayScanError('Error processing barcode');
            }
        });
    }
    
    function displayScanResult(item) {
        const resultsDiv = $('#scan-results');
        const resultHtml = `
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-green-800">Item Found</h4>
                        <div class="mt-1 text-sm text-green-700">
                            <p><strong>Name:</strong> ${item.name}</p>
                            <p><strong>SKU:</strong> ${item.sku}</p>
                            <p><strong>Category:</strong> ${item.category_name}</p>
                            <p><strong>Current Stock:</strong> ${item.quantity} ${item.unit}</p>
                            <p><strong>Location:</strong> ${item.location || 'N/A'}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        resultsDiv.html(resultHtml);
        
        // Auto-clear after 5 seconds
        setTimeout(function() {
            resultsDiv.html(`
                <div class="text-center text-gray-500 py-8">
                    <i class="fas fa-qrcode text-4xl mb-4"></i>
                    <p>Ready for next scan</p>
                </div>
            `);
        }, 5000);
    }
    
    function displayScanError(message) {
        const resultsDiv = $('#scan-results');
        const errorHtml = `
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-red-800">Scan Error</h4>
                        <div class="mt-1 text-sm text-red-700">
                            <p>${message}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        resultsDiv.html(errorHtml);
        
        // Auto-clear after 5 seconds
        setTimeout(function() {
            resultsDiv.html(`
                <div class="text-center text-gray-500 py-8">
                    <i class="fas fa-qrcode text-4xl mb-4"></i>
                    <p>Ready for next scan</p>
                </div>
            `);
        }, 5000);
    }
    
    function addToRecentScans(item) {
        const scan = {
            barcode: item.barcode,
            item_name: item.name,
            category_name: item.category_name,
            location: item.location,
            scanned_by: '<?php echo $_SESSION['user_name'] ?? 'User'; ?>',
            scanned_at: new Date().toLocaleString()
        };
        
        recentScans.unshift(scan);
        if (recentScans.length > 10) {
            recentScans = recentScans.slice(0, 10);
        }
    }
    
    function loadRecentScans() {
        $.ajax({
            url: 'api/get-recent-scans.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayRecentScans(response.scans);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading recent scans:', error);
            }
        });
    }
    
    function displayRecentScans(scans) {
        const tbody = $('#recent-scans-tbody');
        tbody.empty();
        
        if (scans.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        No recent scans
                    </td>
                </tr>
            `);
            return;
        }
        
        scans.forEach(function(scan) {
            const row = `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${scan.barcode}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${scan.item_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${scan.category_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${scan.location || 'N/A'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${scan.scanned_by}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${scan.scanned_at}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button class="text-blue-600 hover:text-blue-900">View</button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }
    
    function loadBarcodeManagement() {
        $.ajax({
            url: 'api/get-barcode-management.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayBarcodeManagement(response.barcodes);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading barcode management:', error);
            }
        });
    }
    
    function displayBarcodeManagement(barcodes) {
        const tbody = $('#barcode-management-tbody');
        tbody.empty();
        
        if (barcodes.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                        No barcodes found
                    </td>
                </tr>
            `);
            return;
        }
        
        barcodes.forEach(function(barcode) {
            const statusClass = getBarcodeStatusClass(barcode.status);
            const statusText = barcode.status.charAt(0).toUpperCase() + barcode.status.slice(1);
            
            const row = `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${barcode.barcode}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${barcode.item_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${barcode.batch_number || 'N/A'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${barcode.expiry_date || 'N/A'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${barcode.location || 'N/A'}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                            ${statusText}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${barcode.last_scanned || 'Never'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                        <button class="text-red-600 hover:text-red-900">Delete</button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }
    
    function getBarcodeStatusClass(status) {
        switch(status) {
            case 'active': return 'bg-green-100 text-green-800';
            case 'used': return 'bg-blue-100 text-blue-800';
            case 'expired': return 'bg-red-100 text-red-800';
            case 'damaged': return 'bg-yellow-100 text-yellow-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }
    
    function generateBarcodes() {
        $.ajax({
            url: 'api/generate-barcodes.php',
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Barcodes generated successfully!');
                    loadBarcodeManagement();
                } else {
                    alert('Error generating barcodes: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error generating barcodes:', error);
                alert('Error generating barcodes');
            }
        });
    }
    
    function printBarcodes() {
        $.ajax({
            url: 'api/print-barcodes.php',
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Barcodes sent to printer!');
                } else {
                    alert('Error printing barcodes: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error printing barcodes:', error);
                alert('Error printing barcodes');
            }
        });
    }
    
    // Cleanup on page unload
    $(window).on('beforeunload', function() {
        if (scannerActive) {
            stopScanner();
        }
    });
});
</script>
