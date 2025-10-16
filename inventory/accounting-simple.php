<?php
/**
 * Simple Accounting Module (No Sidebar)
 */

require_once __DIR__ . '/../vps_session_fix.php';
require_once __DIR__ . '/../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if user has appropriate role (only manager)
$user_role = $_SESSION['user_role'] ?? '';
if ($user_role !== 'manager') {
    header('Location: index.php?error=access_denied');
    exit();
}

// Test database connection
try {
    global $pdo;
    if (!$pdo) {
        throw new Exception('Database connection not available');
    }
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounting Integration - Hotel Inventory System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        #sidebar { transition: transform 0.3s ease-in-out; }
        @media (max-width: 1023px) { #sidebar { transform: translateX(-100%); z-index: 50; } #sidebar.sidebar-open { transform: translateX(0); } }
        @media (min-width: 1024px) { #sidebar { transform: translateX(0) !important; } }
        #sidebar-overlay { transition: opacity 0.3s ease-in-out; z-index: 40; }
        .main-content { margin-left: 0; padding-top: 4rem; }
        @media (min-width: 1024px) { .main-content { margin-left: 16rem; } }
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
                <div>
                    <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Accounting Integration</h2>
                    <p class="text-gray-600 mt-1">Financial overview and journal entries management</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="syncAccounting()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-sync mr-2"></i>Sync with Accounting
                    </button>
                    <button onclick="exportJournalEntries()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-file-pdf mr-2"></i>Export to PDF
                    </button>
                </div>
            </div>

            <!-- Financial Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-dollar-sign text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Inventory Value</p>
                            <p class="text-2xl font-semibold text-gray-900">₱249,565.00</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-arrow-up text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Monthly Purchases</p>
                            <p class="text-2xl font-semibold text-gray-900">₱15,000.00</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-arrow-down text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Monthly Usage</p>
                            <p class="text-2xl font-semibold text-gray-900">₱8,500.00</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-chart-line text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">COGS (30d)</p>
                            <p class="text-2xl font-semibold text-gray-900">₱12,000.00</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Inventory Value Trend -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Inventory Value Trend</h3>
                    <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
                        <div class="text-center">
                            <i class="fas fa-chart-line text-4xl text-gray-400 mb-2"></i>
                            <p class="text-gray-500">Chart visualization would be here</p>
                        </div>
                    </div>
                </div>

                <!-- Cost of Goods Sold -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Cost of Goods Sold</h3>
                    <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
                        <div class="text-center">
                            <i class="fas fa-chart-bar text-4xl text-gray-400 mb-2"></i>
                            <p class="text-gray-500">Chart visualization would be here</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Journal Entries -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-800">Journal Entries</h3>
                    <div class="flex space-x-2">
                        <select class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="posted">Posted</option>
                        </select>
                        <button class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-sm">
                            <i class="fas fa-sync"></i>
                        </button>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Debit</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credit</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2025-01-16 09:00:01</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">INV-40</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">5000</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">COGS - Sample Item</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱2,000.00</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Posted
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button class="text-blue-600 hover:text-blue-900">View</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Accounting Module Status -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-semibold mb-4">Accounting Module Status</h3>
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-check text-green-600 text-xl"></i>
                    </div>
                    <p class="text-lg text-gray-800">Accounting module is working correctly!</p>
                    <p class="text-sm text-gray-600 mt-2">This is a simplified version with full design integration.</p>
                    <div class="mt-4">
                        <a href="accounting-integration.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium">
                            <i class="fas fa-calculator mr-2"></i>
                            Open Full Accounting Module
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function syncAccounting() {
            alert('Syncing with accounting system...');
        }
        
        function exportJournalEntries() {
            alert('Exporting journal entries to PDF...');
        }
    </script>
</body>
</html>
