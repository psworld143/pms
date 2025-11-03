<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);


/**
 * Guest Services
 * Hotel PMS Training System for Students
 */

session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Load dynamic stats and lists
$stats = getServiceRequestStats();
$requests = getServiceRequests('', '');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

// Set page title
$page_title = 'Guest Services';

// Include header
include '../../includes/header-unified.php';
// Include sidebar
include '../../includes/sidebar-unified.php';
?>

        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Guest Services</h2>
                <div class="flex items-center space-x-4">
                    <button onclick="openServiceRequestModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-plus mr-2"></i>New Service Request
                    </button>
                </div>
            </div>

            <!-- Current Charges for Selected Reservation -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Current Charges</h3>
                    <div class="text-sm text-gray-600">Total: <span id="current-charges-total">₱0.00</span></div>
                </div>
                <div id="current-charges" class="text-sm text-gray-700">Select a reservation below and click View Charges or Add Charge to load.</div>
            </div>

            <!-- Service Statistics (Dynamic) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-concierge-bell text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Pending Requests</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['pending'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-check-circle text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Completed Today</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['completed'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Average Response</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['avg_response_time'] ?? 0); ?> min</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-star text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Urgent Open</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['urgent'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Service Categories -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Room Service -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Room Service</h3>
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">3 Active</span>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Room 205 - Dinner Order</p>
                                <p class="text-sm text-gray-500">Ordered 15 minutes ago</p>
                            </div>
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">Preparing</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Room 301 - Breakfast</p>
                                <p class="text-sm text-gray-500">Ordered 5 minutes ago</p>
                            </div>
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">New</span>
                        </div>
                    </div>
                </div>

                <!-- Housekeeping -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Housekeeping</h3>
                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">2 Active</span>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Room 102 - Extra Towels</p>
                                <p class="text-sm text-gray-500">Requested 10 minutes ago</p>
                            </div>
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">In Progress</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Room 208 - Room Cleaning</p>
                                <p class="text-sm text-gray-500">Requested 20 minutes ago</p>
                            </div>
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">Scheduled</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Services (Billable) -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Additional Services</h3>
                    <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs font-semibold rounded-full">Billable</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div class="border rounded-lg p-4 flex flex-col">
                        <div class="font-medium text-gray-900 mb-1">Room Cleaning</div>
                        <div class="text-sm text-gray-500 mb-3">Standard cleaning service</div>
                        <div class="flex items-center justify-between mt-auto">
                            <span class="text-green-700 font-semibold">₱300.00</span>
                            <button class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded" onclick="openAddServiceModal('Room Cleaning',300,'housekeeping')">Add</button>
                        </div>
                    </div>
                    <div class="border rounded-lg p-4 flex flex-col">
                        <div class="font-medium text-gray-900 mb-1">Extra Towels</div>
                        <div class="text-sm text-gray-500 mb-3">Additional towel set</div>
                        <div class="flex items-center justify-between mt-auto">
                            <span class="text-green-700 font-semibold">₱150.00</span>
                            <button class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded" onclick="openAddServiceModal('Extra Towels',150,'housekeeping')">Add</button>
                        </div>
                    </div>
                    <div class="border rounded-lg p-4 flex flex-col">
                        <div class="font-medium text-gray-900 mb-1">Room Service: Dinner Order</div>
                        <div class="text-sm text-gray-500 mb-3">Fixed dinner package</div>
                        <div class="flex items-center justify-between mt-auto">
                            <span class="text-green-700 font-semibold">₱650.00</span>
                            <button class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded" onclick="openAddServiceModal('Room Service: Dinner Order',650,'food_beverage')">Add</button>
                        </div>
                    </div>
                    <div class="border rounded-lg p-4 flex flex-col">
                        <div class="font-medium text-gray-900 mb-1">Room Service: Breakfast</div>
                        <div class="text-sm text-gray-500 mb-3">Set breakfast meal</div>
                        <div class="flex items-center justify-between mt-auto">
                            <span class="text-green-700 font-semibold">₱400.00</span>
                            <button class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded" onclick="openAddServiceModal('Room Service: Breakfast',400,'food_beverage')">Add</button>
                        </div>
                    </div>
                    <!-- More quick services can be added here -->
                </div>
            </div>

            <!-- Service Request Details Modal -->
            <div id="service-details-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800">Service Request Details</h3>
                            <button onclick="closeServiceDetailsModal()" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                    </div>
                    <div id="service-details-content" class="p-6">
                        <!-- Content will be loaded dynamically -->
                    </div>
                </div>
            </div>

            <!-- Service Request Modal -->
            <div id="service-request-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800">New Service Request</h3>
                            <button onclick="closeServiceRequestModal()" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                    </div>
                    <form id="service-request-form" class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="room_id" class="block text-sm font-medium text-gray-700 mb-2">Guest Room *</label>
                                <select id="room_id" name="room_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select Room</option>
                                </select>
                            </div>
                            <div>
                                <label for="request_type" class="block text-sm font-medium text-gray-700 mb-2">Service Type *</label>
                                <select id="request_type" name="request_type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select Service</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="housekeeping">Housekeeping</option>
                                    <option value="concierge">Concierge</option>
                                    <option value="technical">Technical</option>
                                </select>
                            </div>
                            <div>
                                <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">Priority *</label>
                                <select id="priority" name="priority" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                            <div>
                                <label for="estimated_time" class="block text-sm font-medium text-gray-700 mb-2">Estimated Time</label>
                                <select id="estimated_time" name="estimated_time" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="15 minutes">15 minutes</option>
                                    <option value="30 minutes">30 minutes</option>
                                    <option value="1 hour">1 hour</option>
                                    <option value="2 hours">2 hours</option>
                                    <option value="Same day">Same day</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Service Description *</label>
                            <textarea id="description" name="description" required rows="4" placeholder="Describe the service request in detail" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        <div>
                            <label for="special_instructions" class="block text-sm font-medium text-gray-700 mb-2">Special Instructions</label>
                            <textarea id="special_instructions" name="special_instructions" rows="3" placeholder="Any special instructions or notes" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200">
                            <button type="button" onclick="closeServiceRequestModal()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                                Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Checked-in Guests with Add Charges -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">Checked-in Guests</h3>
                    <span class="text-sm text-gray-500">Click Add Charge to post additional services</span>
                </div>
                <div class="overflow-x-auto" id="checkin-table">
                    <div class="p-6 text-gray-500">Loading checked-in guests…</div>
                </div>
            </div>
        
            <script>
            async function viewServiceRequest(id) {
                try {
                    const res = await fetch(`../../api/get-service-request.php?id=${id}`);
                    const data = await res.json();
                    if (!data.success) throw new Error(data.message || 'Failed');
                    
                    // Display in beautiful modal
                    displayServiceRequestDetails(data.request);
                } catch (e) {
                    showNotification('Unable to load service request details.', 'error');
                }
            }
            
            function displayServiceRequestDetails(request) {
                const content = document.getElementById('service-details-content');
                
                const statusColors = {
                    'reported': 'bg-yellow-100 text-yellow-800',
                    'assigned': 'bg-blue-100 text-blue-800', 
                    'in_progress': 'bg-purple-100 text-purple-800',
                    'completed': 'bg-green-100 text-green-800',
                    'verified': 'bg-gray-100 text-gray-800'
                };
                
                const priorityColors = {
                    'low': 'bg-gray-100 text-gray-800',
                    'medium': 'bg-yellow-100 text-yellow-800',
                    'high': 'bg-orange-100 text-orange-800',
                    'urgent': 'bg-red-100 text-red-800'
                };
                
                const issueTypeLabels = {
                    'plumbing': 'Plumbing',
                    'electrical': 'Electrical',
                    'hvac': 'HVAC',
                    'furniture': 'Furniture',
                    'appliance': 'Appliance',
                    'other': 'Other'
                };
                
                content.innerHTML = `
                    <div class="space-y-6">
                        <!-- Header Info -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-gray-500 mb-2">Request ID</h4>
                                <p class="text-lg font-semibold text-gray-900">#${request.id}</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-gray-500 mb-2">Room</h4>
                                <p class="text-lg font-semibold text-gray-900">Room ${request.room_number}</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-gray-500 mb-2">Created</h4>
                                <p class="text-lg font-semibold text-gray-900">${new Date(request.created_at).toLocaleDateString()}</p>
                            </div>
                        </div>
                        
                        <!-- Status and Priority -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-gray-500 mb-2">Status</h4>
                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full ${statusColors[request.status] || 'bg-gray-100 text-gray-800'}">
                                    ${request.status.replace('_', ' ').toUpperCase()}
                                </span>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-gray-500 mb-2">Priority</h4>
                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full ${priorityColors[request.priority] || 'bg-gray-100 text-gray-800'}">
                                    ${request.priority.toUpperCase()}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Service Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-gray-500 mb-2">Service Type</h4>
                                <p class="text-lg font-semibold text-gray-900">${issueTypeLabels[request.issue_type] || request.issue_type}</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-gray-500 mb-2">Reported By</h4>
                                <p class="text-lg font-semibold text-gray-900">${request.reported_by_name || 'System'}</p>
                            </div>
                        </div>
                        
                        <!-- Description -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Description</h4>
                            <p class="text-gray-900 whitespace-pre-wrap">${request.description || 'No description provided'}</p>
                        </div>
                        
                        <!-- Guest Information (if available) -->
                        ${request.guest_name ? `
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-blue-700 mb-2">Guest Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-blue-600">Guest Name</p>
                                    <p class="font-semibold text-blue-900">${request.guest_name}</p>
                                </div>
                                ${request.guest_phone ? `
                                <div>
                                    <p class="text-sm text-blue-600">Phone</p>
                                    <p class="font-semibold text-blue-900">${request.guest_phone}</p>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                        ` : ''}
                        
                        <!-- Cost Information (if available) -->
                        ${request.estimated_cost || request.actual_cost ? `
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-green-700 mb-2">Cost Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                ${request.estimated_cost ? `
                                <div>
                                    <p class="text-sm text-green-600">Estimated Cost</p>
                                    <p class="font-semibold text-green-900">$${parseFloat(request.estimated_cost).toFixed(2)}</p>
                                </div>
                                ` : ''}
                                ${request.actual_cost ? `
                                <div>
                                    <p class="text-sm text-green-600">Actual Cost</p>
                                    <p class="font-semibold text-green-900">$${parseFloat(request.actual_cost).toFixed(2)}</p>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                        ` : ''}
                        
                        <!-- Timeline -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Timeline</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Created:</span>
                                    <span class="text-sm font-medium text-gray-900">${new Date(request.created_at).toLocaleString()}</span>
                                </div>
                                ${request.updated_at && request.updated_at !== request.created_at ? `
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Last Updated:</span>
                                    <span class="text-sm font-medium text-gray-900">${new Date(request.updated_at).toLocaleString()}</span>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `;
                
                // Show the modal
                document.getElementById('service-details-modal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
            
            function closeServiceDetailsModal() {
                document.getElementById('service-details-modal').classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
            async function completeServiceRequest(id) {
                if (!confirm('Mark this service request as completed?')) return;
                try {
                    const res = await fetch('../../api/complete-service-request.php', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ id })});
                    const data = await res.json();
                    if (data.success) location.reload(); else alert(data.message || 'Failed to complete request');
                } catch(e) { alert('Network error'); }
            }
            </script>
        </main>

        <!-- Include footer -->
        <?php include '../../includes/footer.php'; ?>

        <script>
        // Suppress classifier.js shader errors
        window.addEventListener('error', function(e) {
            if (e.message && e.message.includes('Failed to link vertex and fragment shaders')) {
                e.preventDefault();
                return false;
            }
        });
        
        // Suppress unhandled promise rejections from classifier.js
        window.addEventListener('unhandledrejection', function(e) {
            if (e.reason && e.reason.message && e.reason.message.includes('Failed to link vertex and fragment shaders')) {
                e.preventDefault();
                return false;
            }
        });
        
        document.addEventListener('DOMContentLoaded', function() {
            loadRooms();
            initializeServiceRequestForm();
            loadCheckedInGuests();
        });

        // Add Service Modal state
function openAddServiceModal(name, price, category, reservationId) {
    try {
        const saved = localStorage.getItem('svc_price_' + name);
        if (saved !== null && !isNaN(parseFloat(saved))) {
            price = parseFloat(saved);
        }
    } catch (_) {}
            // Build a lightweight prompt modal
            const modal = document.createElement('div');
            modal.id = 'add-service-modal';
            modal.className = 'fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50';
            modal.innerHTML = `
                <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Add Service Charge</h3>
                    <form id="add-service-form" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Service</label>
                            <input id="svc-name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md" value="${name}" />
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                                <input id="svc-price" type="number" step="0.01" min="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md" value="${price}" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                <input id="svc-qty" type="number" min="1" value="1" class="w-full px-3 py-2 border border-gray-300 rounded-md" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Reservation ID or Number</label>
                            <input id="svc-reservation" placeholder="e.g., 123 or RES2025..." class="w-full px-3 py-2 border border-gray-300 rounded-md" value="${reservationId||''}" ${reservationId?'readonly':''} />
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" class="px-4 py-2 border border-gray-300 rounded-md" onclick="closeAddServiceModal()">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">Add Charge</button>
                        </div>
                    </form>
                </div>`;
            document.body.appendChild(modal);
            document.body.style.overflow = 'hidden';

            document.getElementById('add-service-form').addEventListener('submit', async function(e){
                e.preventDefault();
                const amount = parseFloat(document.getElementById('svc-price').value);
                const quantity = parseInt(document.getElementById('svc-qty').value, 10);
                const reservation_id = document.getElementById('svc-reservation').value.trim();
                const svc_name = (document.getElementById('svc-name')?.value || name).trim();
                if (!reservation_id || !isFinite(amount) || amount <= 0 || !isFinite(quantity) || quantity < 1) {
                    showNotification('Please provide valid reservation, price, and quantity', 'error');
                    return;
                }
                try { localStorage.setItem('svc_price_' + name, String(amount)); } catch (_) {}
                try {
                    const res = await fetch('../../api/add-service-charge.php', {
                        method: 'POST', credentials: 'include',
                        headers: { 'Content-Type': 'application/json', 'X-API-Key': 'pms_users_api_2024' },
                        body: JSON.stringify({ reservation_id, service_name: svc_name, amount, quantity, category })
                    });
                    const json = await res.json();
                    if (!json.success) throw new Error(json.message || 'Failed to add service');
                    showNotification('Service charge added and invoice updated', 'success');
                    closeAddServiceModal();
                    // Refresh page so All Service Requests and summaries reflect
                    setTimeout(()=> window.location.reload(), 800);
                } catch (err) {
                    showNotification(err.message, 'error');
                }
            });
        }

        function closeAddServiceModal() {
            const modal = document.getElementById('add-service-modal');
            if (modal) modal.remove();
            document.body.style.overflow = 'auto';
        }

        async function loadCheckedInGuests() {
            try {
                const res = await fetch('../../api/get-checked-in-guests.php', { credentials: 'include' });
                const json = await res.json();
                const container = document.getElementById('checkin-table');
                if (!json || !json.success) { container.innerHTML = '<div class="p-6 text-gray-500">No data.</div>'; return; }
                const guests = json.guests || [];
                if (guests.length === 0) { container.innerHTML = '<div class="p-6 text-gray-500">No guests are currently checked in.</div>'; return; }
                const rows = guests.map(g => `
                    <tr>
                        <td class="px-6 py-3 text-sm text-gray-900">#${g.id||g.reservation_id||''}</td>
                        <td class="px-6 py-3 text-sm text-gray-900">${g.guest_name||''}</td>
                        <td class="px-6 py-3 text-sm text-gray-900">Room ${g.room_number||''}</td>
                        <td class="px-6 py-3 text-sm text-gray-900">${g.check_in_date?new Date(g.check_in_date).toLocaleDateString():''}</td>
                        <td class="px-6 py-3 text-sm text-gray-900">${g.check_out_date?new Date(g.check_out_date).toLocaleDateString():''}</td>
                        <td class="px-6 py-3 text-sm">
                            <button class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded mr-2" onclick="openAddServiceModal('Room Cleaning',300,'housekeeping','${g.id||g.reservation_id||''}')">Add Charge</button>
                            <button class="px-3 py-1 bg-gray-600 hover:bg-gray-700 text-white rounded" onclick="loadCurrentCharges('${g.id||g.reservation_id||''}')">View Charges</button>
                        </td>
                    </tr>`).join('');
                container.innerHTML = `
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reservation</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-out</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">${rows}</tbody>
                    </table>`;
            } catch(e) {
                const container = document.getElementById('checkin-table');
                container.innerHTML = '<div class="p-6 text-gray-500">Error loading checked-in guests.</div>';
            }
        }

        function loadRooms() {
            fetch('../../api/get-rooms.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const roomSelect = document.getElementById('room_id');
                        roomSelect.innerHTML = '<option value="">Select Room</option>';
                        
                        data.rooms.forEach(room => {
                            const option = document.createElement('option');
                            option.value = room.id;
                            option.textContent = `Room ${room.room_number} - ${room.room_type || 'Standard'}`;
                            roomSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading rooms:', error);
                    showNotification('Error loading rooms', 'error');
                });
        }

        function openServiceRequestModal() {
            document.getElementById('service-request-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeServiceRequestModal() {
            document.getElementById('service-request-modal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            document.getElementById('service-request-form').reset();
        }

        function initializeServiceRequestForm() {
            const form = document.getElementById('service-request-form');
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(form);
                const data = {
                    room_id: formData.get('room_id'),
                    request_type: formData.get('request_type'),
                    priority: formData.get('priority'),
                    description: formData.get('description'),
                    special_instructions: formData.get('special_instructions') || ''
                };

                // Validate required fields
                if (!data.room_id || !data.request_type || !data.description) {
                    showNotification('Please fill in all required fields', 'error');
                    return;
                }

                // Submit the form
                submitServiceRequest(data);
            });
        }

        function submitServiceRequest(data) {
            const submitBtn = document.querySelector('#service-request-form button[type="submit"]');
            const originalText = submitBtn.textContent;
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';

            fetch('../../api/create-service-request.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showNotification('Service request created successfully!', 'success');
                    closeServiceRequestModal();
                    // Optionally refresh the page or reload service requests
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showNotification(result.message || 'Error creating service request', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error creating service request', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        }

        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white max-w-sm transform transition-all duration-300 translate-x-full`;
            
            // Set background color based on type
            switch(type) {
                case 'success':
                    notification.classList.add('bg-green-500');
                    break;
                case 'error':
                    notification.classList.add('bg-red-500');
                    break;
                case 'warning':
                    notification.classList.add('bg-yellow-500');
                    break;
                default:
                    notification.classList.add('bg-blue-500');
            }
            
            notification.textContent = message;
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);
            
            // Remove after 5 seconds
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 5000);
        }

        // Close modal when clicking outside
        document.getElementById('service-request-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeServiceRequestModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeServiceRequestModal();
            }
        });
        </script>
    </body>
</html>
