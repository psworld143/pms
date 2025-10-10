<?php
require_once dirname(__DIR__, 2) . '/../vps_session_fix.php';
require_once dirname(__DIR__, 2) . '/../includes/database.php';
require_once '../../includes/functions.php';
// Check if user is logged in and has front desk access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['front_desk', 'manager'])) {
    header('Location: ../../login.php');
    exit();
}

// Get dynamic statistics
try {
    // Get checked-in guests count
    $stmt = $pdo->query("
        SELECT COUNT(*) as checked_in_count 
        FROM reservations 
        WHERE status = 'checked_in'
    ");
    $checked_in_count = $stmt->fetch()['checked_in_count'];
    
    // Get check-outs today count
    $stmt = $pdo->query("
        SELECT COUNT(*) as checkouts_today 
        FROM reservations 
        WHERE status = 'checked_out' AND DATE(checked_out_at) = CURDATE()
    ");
    $checkouts_today = $stmt->fetch()['checkouts_today'];
    
    // Get pending check-outs count (guests due to check out today)
    $stmt = $pdo->query("
        SELECT COUNT(*) as pending_checkouts 
        FROM reservations 
        WHERE status = 'checked_in' AND DATE(check_out_date) = CURDATE()
    ");
    $pending_checkouts = $stmt->fetch()['pending_checkouts'];
    
    // Get overdue check-outs count
    $stmt = $pdo->query("
        SELECT COUNT(*) as overdue_checkouts 
        FROM reservations 
        WHERE status = 'checked_in' AND DATE(check_out_date) < CURDATE()
    ");
    $overdue_checkouts = $stmt->fetch()['overdue_checkouts'];
    
} catch (Exception $e) {
    error_log("Error getting check-out statistics: " . $e->getMessage());
    $checked_in_count = 0;
    $checkouts_today = 0;
    $pending_checkouts = 0;
    $overdue_checkouts = 0;
}

// Get checked-in guests
$checked_in_guests = getCheckedInGuests();

// Set page title
$page_title = 'Check Out';

// Include unified header (automatically selects appropriate navbar)
include '../../includes/header-unified.php';
// Include unified sidebar (automatically selects appropriate sidebar)
include '../../includes/sidebar-unified.php';
?>

        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <!-- Statistics Dashboard -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-bed text-green-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Checked-in Guests</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $checked_in_count; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-sign-out-alt text-blue-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Check-outs Today</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $checkouts_today; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-clock text-yellow-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Pending Check-outs</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $pending_checkouts; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-red-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Overdue Check-outs</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $overdue_checkouts; ?></p>
                        </div>
                    </div>
                </div>
            </div>

                <!-- Search Section -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold text-gray-800">Search Checked-in Guests</h2>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-500" id="current-time"></span>
                            <button onclick="refreshData()" class="text-gray-400 hover:text-gray-600 transition-colors" title="Refresh">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div>
                            <label for="search_reservation" class="block text-sm font-medium text-gray-700 mb-2">Reservation Number</label>
                            <input type="text" id="search_reservation" placeholder="Enter reservation number" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label for="search_guest" class="block text-sm font-medium text-gray-700 mb-2">Guest Name</label>
                            <input type="text" id="search_guest" placeholder="Enter guest name" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label for="search_room" class="block text-sm font-medium text-gray-700 mb-2">Room Number</label>
                            <input type="text" id="search_room" placeholder="Enter room number" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label for="search_status" class="block text-sm font-medium text-gray-700 mb-2">Check-out Status</label>
                            <select id="search_status" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">All Status</option>
                                <option value="due_today">Due Today</option>
                                <option value="overdue">Overdue</option>
                                <option value="vip">VIP Guests</option>
                            </select>
                        </div>
                        <div class="flex items-end space-x-2">
                            <button onclick="searchCheckedInGuests()" 
                                    class="flex-1 px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark transition-colors">
                                <i class="fas fa-search mr-2"></i>Search
                            </button>
                            <button onclick="clearFilters()" 
                                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Checked-in Guests -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Currently Checked-in Guests</h2>
                    <div id="checked-in-guests" class="overflow-x-auto">
                        <!-- Checked-in guests will be loaded here -->
                    </div>
                </div>

                <!-- Check-out Form -->
                <div id="checkout-form-container" class="bg-white rounded-lg shadow-md p-6 hidden">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Check-out Details</h2>
                    <form id="checkout-form" class="space-y-6">
                        <input type="hidden" id="reservation_id" name="reservation_id">
                        
                        <!-- Guest Information -->
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Guest Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Guest Name</label>
                                    <input type="text" id="guest_name" readonly 
                                           class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Reservation Number</label>
                                    <input type="text" id="reservation_number" readonly 
                                           class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Room Number</label>
                                    <input type="text" id="room_number" readonly 
                                           class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Check-out Date</label>
                                    <input type="text" id="checkout_date" readonly 
                                           class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md">
                                </div>
                            </div>
                        </div>

                        <!-- Billing Summary -->
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Billing Summary</h3>
                            <div id="billing-summary" class="bg-gray-50 p-4 rounded-lg">
                                <!-- Billing details will be loaded here -->
                            </div>
                        </div>

                        <!-- Check-out Details -->
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Check-out Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="room_key_returned" class="block text-sm font-medium text-gray-700 mb-2">Room Key Returned</label>
                                    <select id="room_key_returned" name="room_key_returned" required 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">Select</option>
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="payment_status" class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
                                    <select id="payment_status" name="payment_status" required 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">Select</option>
                                        <option value="paid">Paid</option>
                                        <option value="pending">Pending</option>
                                        <option value="partial">Partial</option>
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="checkout_notes" class="block text-sm font-medium text-gray-700 mb-2">Check-out Notes</label>
                                    <textarea id="checkout_notes" name="checkout_notes" rows="3" 
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                              placeholder="Any notes about the check-out..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-4">
                            <button type="button" onclick="cancelCheckout()" 
                                    class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                                <i class="fas fa-sign-out-alt mr-2"></i>Complete Check-out
                            </button>
                        </div>
                    </form>
                </div>
        </main>
    </div>

    <script src="../../assets/js/main.js"></script>
    <script src="../../assets/js/checkout.js"></script>
    
    <script>
        // Update current time
        function updateDateTime() {
            const now = new Date();
            const timeString = now.toLocaleString('en-US', {
                weekday: 'short',
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            const timeElement = document.getElementById('current-time');
            if (timeElement) {
                timeElement.textContent = timeString;
            }
        }

        // Clear filters function
        function clearFilters() {
            document.getElementById('search_reservation').value = '';
            document.getElementById('search_guest').value = '';
            document.getElementById('search_room').value = '';
            document.getElementById('search_status').value = '';
            loadCheckedInGuests(); // Reload all checked-in guests
        }

        // Refresh data function
        function refreshData() {
            const refreshBtn = document.querySelector('button[onclick="refreshData()"]');
            const icon = refreshBtn.querySelector('i');
            
            // Add spinning animation
            icon.classList.add('fa-spin');
            refreshBtn.disabled = true;
            
            // Reload checked-in guests
            loadCheckedInGuests();
            
            // Remove spinning animation after a delay
            setTimeout(() => {
                icon.classList.remove('fa-spin');
                refreshBtn.disabled = false;
            }, 1000);
        }

        // Initialize time update
        updateDateTime();
        setInterval(updateDateTime, 1000);

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + F to focus on guest name search
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                document.getElementById('search_guest').focus();
            }
            
            // Escape to clear filters
            if (e.key === 'Escape') {
                clearFilters();
            }
            
            // Enter to search (when in search fields)
            if (e.key === 'Enter' && (e.target.id.includes('search_') || e.target.id.includes('search'))) {
                e.preventDefault();
                searchCheckedInGuests();
            }
        });

        // Auto-refresh statistics every 30 seconds
        setInterval(() => {
            // This would ideally make an API call to refresh statistics
            // For now, we'll just refresh the checked-in guests
            loadCheckedInGuests();
        }, 30000);
    </script>
    
    <?php include '../../includes/footer.php'; ?>
