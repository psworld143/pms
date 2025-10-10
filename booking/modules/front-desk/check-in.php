<?php
require_once dirname(__DIR__, 2) . '/../vps_session_fix.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once dirname(__DIR__, 2) . '/../includes/database.php';
require_once '../../includes/functions.php';
// Check if user is logged in and has front desk access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['front_desk', 'manager'])) {
    header('Location: ../../login.php');
    exit();
}

// Get dynamic statistics
try {
    // Get pending check-ins count
    $stmt = $pdo->query("
        SELECT COUNT(*) as pending_count 
        FROM reservations 
        WHERE status = 'confirmed' AND DATE(check_in_date) = CURDATE()
    ");
    $pending_checkins_count = $stmt->fetch()['pending_count'];
    
    // Get checked-in today count
    $stmt = $pdo->query("
        SELECT COUNT(*) as checked_in_today 
        FROM reservations 
        WHERE status = 'checked_in' AND DATE(checked_in_at) = CURDATE()
    ");
    $checked_in_today = $stmt->fetch()['checked_in_today'];
    
    // Get early arrivals count
    $stmt = $pdo->query("
        SELECT COUNT(*) as early_arrivals 
        FROM reservations 
        WHERE status = 'confirmed' AND DATE(check_in_date) > CURDATE()
    ");
    $early_arrivals = $stmt->fetch()['early_arrivals'];
    
    // Get VIP guests count
    $stmt = $pdo->query("
        SELECT COUNT(*) as vip_guests 
        FROM reservations r
        JOIN guests g ON r.guest_id = g.id
        WHERE r.status = 'confirmed' AND DATE(r.check_in_date) = CURDATE() AND g.is_vip = 1
    ");
    $vip_guests = $stmt->fetch()['vip_guests'];
    
} catch (Exception $e) {
    error_log("Error getting check-in statistics: " . $e->getMessage());
    $pending_checkins_count = 0;
    $checked_in_today = 0;
    $early_arrivals = 0;
    $vip_guests = 0;
}

// Get pending check-ins
$pending_checkins = getPendingCheckins();

// Set page title
$page_title = 'Check In';

// Include unified navigation (automatically selects based on user role)
include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';
?>

        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <!-- Statistics Dashboard -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-clock text-yellow-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Pending Check-ins</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $pending_checkins_count; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-check-circle text-green-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Checked-in Today</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $checked_in_today; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-calendar-plus text-blue-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Early Arrivals</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $early_arrivals; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-crown text-purple-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">VIP Guests</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $vip_guests; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Section -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold text-gray-800">Search Reservations</h2>
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
                            <label for="search_date" class="block text-sm font-medium text-gray-700 mb-2">Check-in Date</label>
                            <input type="date" id="search_date" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label for="search_status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select id="search_status" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">All Status</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="checked_in">Checked In</option>
                                <option value="early_arrival">Early Arrival</option>
                            </select>
                        </div>
                        <div class="flex items-end space-x-2">
                            <button onclick="searchReservations()" 
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

                <!-- Pending Check-ins -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Pending Check-ins</h2>
                    <div id="pending-checkins" class="overflow-x-auto">
                        <!-- Pending check-ins will be loaded here -->
                    </div>
                </div>

                <!-- Check-in Form -->
                <div id="checkin-form-container" class="bg-white rounded-lg shadow-md p-6 hidden">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Check-in Details</h2>
                    <form id="checkin-form" class="space-y-6">
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
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Check-in Date</label>
                                    <input type="text" id="checkin_date" readonly 
                                           class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md">
                                </div>
                            </div>
                        </div>

                        <!-- Check-in Details -->
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Check-in Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="room_key_issued" class="block text-sm font-medium text-gray-700 mb-2">Room Key Issued</label>
                                    <select id="room_key_issued" name="room_key_issued" required 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">Select</option>
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="welcome_amenities" class="block text-sm font-medium text-gray-700 mb-2">Welcome Amenities Provided</label>
                                    <select id="welcome_amenities" name="welcome_amenities" required 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">Select</option>
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="special_instructions" class="block text-sm font-medium text-gray-700 mb-2">Special Instructions</label>
                                    <textarea id="special_instructions" name="special_instructions" rows="3" 
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                              placeholder="Any special instructions for the guest..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-4">
                            <button type="button" onclick="cancelCheckin()" 
                                    class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                                <i class="fas fa-check mr-2"></i>Complete Check-in
                            </button>
                        </div>
                    </form>
                </div>
        </main>
    </div>

    <script src="../../assets/js/main.js"></script>
    <script src="../../assets/js/checkin.js"></script>
    
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
            document.getElementById('search_date').value = '';
            document.getElementById('search_status').value = '';
            loadPendingCheckins(); // Reload all pending check-ins
        }

        // Refresh data function
        function refreshData() {
            const refreshBtn = document.querySelector('button[onclick="refreshData()"]');
            const icon = refreshBtn.querySelector('i');
            
            // Add spinning animation
            icon.classList.add('fa-spin');
            refreshBtn.disabled = true;
            
            // Reload pending check-ins
            loadPendingCheckins();
            
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
                searchReservations();
            }
        });

        // Auto-refresh statistics every 30 seconds
        setInterval(() => {
            // This would ideally make an API call to refresh statistics
            // For now, we'll just refresh the pending check-ins
            loadPendingCheckins();
        }, 30000);
    </script>
    
    <?php include '../../includes/footer.php'; ?>
