<?php
/**
 * Request Management
 * Manager interface for approving/rejecting supply requests
 */

require_once __DIR__ . '/../vps_session_fix.php';
require_once __DIR__ . '/../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check user role and permissions
$user_role = $_SESSION['user_role'] ?? '';
if ($user_role !== 'manager') {
    header('Location: login.php?error=access_denied');
    exit();
}

$page_title = 'Request Management';
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
                    <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">
                        <i class="fas fa-clipboard-list mr-2 text-purple-600"></i>Request Management
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">Review and approve supply requests from housekeeping staff</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button id="refresh-requests-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-sync-alt mr-2"></i>Refresh
                    </button>
                </div>
            </div>

            <!-- Request Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-lg p-6 border border-blue-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-blue-700 uppercase tracking-wide">Total Pending</p>
                            <p class="text-3xl font-bold text-blue-900" id="total-pending">Loading...</p>
                        </div>
                        <div class="bg-blue-500 p-3 rounded-lg">
                            <i class="fas fa-clock text-white text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-lg p-6 border border-green-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-green-700 uppercase tracking-wide">Today's Requests</p>
                            <p class="text-3xl font-bold text-green-900" id="today-requests">Loading...</p>
                        </div>
                        <div class="bg-green-500 p-3 rounded-lg">
                            <i class="fas fa-calendar-day text-white text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl shadow-lg p-6 border border-yellow-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-yellow-700 uppercase tracking-wide">Missing Items</p>
                            <p class="text-3xl font-bold text-yellow-900" id="missing-requests">Loading...</p>
                        </div>
                        <div class="bg-yellow-500 p-3 rounded-lg">
                            <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl shadow-lg p-6 border border-red-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-red-700 uppercase tracking-wide">Damaged Items</p>
                            <p class="text-3xl font-bold text-red-900" id="damaged-requests">Loading...</p>
                        </div>
                        <div class="bg-red-500 p-3 rounded-lg">
                            <i class="fas fa-times-circle text-white text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Requests List -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-800">Pending Requests</h3>
                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium" id="request-count">Loading...</span>
                </div>

                <div id="requests-list">
                    <!-- Requests will be loaded here -->
                    <div class="text-center py-12">
                        <i class="fas fa-spinner fa-spin text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500">Loading requests...</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Include footer -->
        <?php include '../includes/pos-footer.php'; ?>
    </div>
</body>
</html>

<script>
$(document).ready(function() {
    loadPendingRequests();
    
    $('#refresh-requests-btn').click(function() {
        loadPendingRequests();
    });
    
    function loadPendingRequests() {
        console.log('Loading pending requests...');
        $.ajax({
            url: 'api/get-pending-requests.php',
            method: 'GET',
            dataType: 'json',
            xhrFields: {
                withCredentials: true
            },
            success: function(response) {
                console.log('Response received:', response);
                if (response.success) {
                    displayRequests(response.requests);
                    updateStatistics(response.statistics);
                } else {
                    console.error('Error loading requests:', response.message);
                    $('#requests-list').html('<div class="text-center py-12 text-red-500">Error loading requests: ' + response.message + '</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error loading requests:', error);
                console.error('Response text:', xhr.responseText);
                $('#requests-list').html(`
                    <div class="text-center py-12 text-red-500">
                        <h4 class="text-lg font-semibold mb-2">Error loading requests</h4>
                        <p class="text-sm mb-4">Status: ${status}, Error: ${error}</p>
                        <p class="text-xs text-gray-500">Response: ${xhr.responseText}</p>
                        <button onclick="testDatabaseConnection()" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded text-sm">
                            Test Database Connection
                        </button>
                    </div>
                `);
            }
        });
    }
    
    function testDatabaseConnection() {
        console.log('Testing database connection...');
        $.ajax({
            url: 'api/test-database-connection.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('Database test response:', response);
                if (response.success) {
                    alert('Database connection test successful!\n\n' + JSON.stringify(response.tests, null, 2));
                } else {
                    alert('Database connection test failed!\n\n' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Database test error:', error);
                alert('Database test failed: ' + error + '\n\nResponse: ' + xhr.responseText);
            }
        });
    }
    
    function displayRequests(requests) {
        const container = $('#requests-list');
        
        if (requests.length === 0) {
            container.html(`
                <div class="text-center py-12">
                    <i class="fas fa-check-circle text-4xl text-green-300 mb-4"></i>
                    <h4 class="text-lg font-semibold text-gray-600 mb-2">No Pending Requests</h4>
                    <p class="text-gray-500">All supply requests have been processed</p>
                </div>
            `);
            return;
        }
        
        let html = '<div class="space-y-4">';
        
        requests.forEach(function(request) {
            const reasonClass = getReasonClass(request.reason);
            const reasonIcon = getReasonIcon(request.reason);
            
            html += `
                <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-3">
                                <h4 class="text-lg font-semibold text-gray-800">${request.item_name}</h4>
                                <span class="px-3 py-1 rounded-full text-xs font-medium ${reasonClass}">
                                    <i class="fas ${reasonIcon} mr-1"></i>${request.reason.replace('_', ' ').toUpperCase()}
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <p class="text-sm text-gray-600">Quantity Requested</p>
                                    <p class="font-semibold text-gray-800">${request.quantity_requested} ${request.unit}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Room Number</p>
                                    <p class="font-semibold text-gray-800">${request.room_number}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Requested By</p>
                                    <p class="font-semibold text-gray-800">${request.requested_by_full_name || request.requested_by_name}</p>
                                </div>
                            </div>
                            
                            ${request.notes ? `
                                <div class="mb-4">
                                    <p class="text-sm text-gray-600">Notes</p>
                                    <p class="text-gray-800 bg-gray-50 p-3 rounded">${request.notes}</p>
                                </div>
                            ` : ''}
                            
                            <div class="flex items-center justify-between">
                                <p class="text-sm text-gray-500">
                                    <i class="fas fa-clock mr-1"></i>
                                    Requested ${new Date(request.created_at).toLocaleString()}
                                </p>
                                
                                <div class="flex space-x-2">
                                    <button onclick="approveRequest(${request.id})" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm font-medium">
                                        <i class="fas fa-check mr-1"></i>Approve
                                    </button>
                                    <button onclick="rejectRequest(${request.id})" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm font-medium">
                                        <i class="fas fa-times mr-1"></i>Reject
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        container.html(html);
    }
    
    function updateStatistics(stats) {
        $('#total-pending').text(stats.total_pending || 0);
        $('#today-requests').text(stats.today_requests || 0);
        $('#missing-requests').text(stats.missing_requests || 0);
        $('#damaged-requests').text(stats.damaged_requests || 0);
        $('#request-count').text(`${stats.total_pending || 0} Pending`);
    }
    
    function getReasonClass(reason) {
        switch(reason) {
            case 'missing': return 'bg-yellow-100 text-yellow-800';
            case 'damaged': return 'bg-red-100 text-red-800';
            case 'low_stock': return 'bg-orange-100 text-orange-800';
            case 'replacement': return 'bg-blue-100 text-blue-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }
    
    function getReasonIcon(reason) {
        switch(reason) {
            case 'missing': return 'fa-exclamation-triangle';
            case 'damaged': return 'fa-times-circle';
            case 'low_stock': return 'fa-exclamation-circle';
            case 'replacement': return 'fa-sync-alt';
            default: return 'fa-question-circle';
        }
    }
    
    window.approveRequest = function(requestId) {
        if (confirm('Approve this supply request?')) {
            processRequest(requestId, 'approve');
        }
    };
    
    window.rejectRequest = function(requestId) {
        const notes = prompt('Please provide a reason for rejection (optional):');
        if (notes !== null) {
            processRequest(requestId, 'reject', notes);
        }
    };
    
    function processRequest(requestId, action, notes = '') {
        $.ajax({
            url: 'api/approve-supply-request.php',
            method: 'POST',
            dataType: 'json',
            data: {
                request_id: requestId,
                action: action,
                notes: notes
            },
            xhrFields: {
                withCredentials: true
            },
            success: function(response) {
                if (response.success) {
                    alert(`Request ${action}d successfully!`);
                    loadPendingRequests();
                } else {
                    alert('Error processing request: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error processing request: ' + xhr.responseText);
            }
        });
    }
});
</script>
