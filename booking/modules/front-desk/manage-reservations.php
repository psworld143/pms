<?php
session_start();
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);


require_once dirname(__DIR__, 2) . '/../vps_session_fix.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
// Check if user is logged in and has front desk access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['front_desk', 'manager'])) {
    header('Location: ../../login.php');
    exit();
}

$user_role = $_SESSION['user_role'];
$user_name = $_SESSION['user_name'];

// Set page title
$page_title = 'Manage Reservations';
$page_subtitle = 'View and manage guest reservations';

// Get dynamic statistics
try {
    // Get today's reservations count
    $stmt = $pdo->query("
        SELECT COUNT(*) as today_count 
        FROM reservations 
        WHERE DATE(check_in_date) = CURDATE()
    ");
    $today_reservations = $stmt->fetch()['today_count'];
    
    // Get total active reservations
    $stmt = $pdo->query("
        SELECT COUNT(*) as active_count 
        FROM reservations 
        WHERE status IN ('confirmed', 'checked_in')
    ");
    $active_reservations = $stmt->fetch()['active_count'];
    
    // Get check-ins today
    $stmt = $pdo->query("
        SELECT COUNT(*) as checkins_today 
        FROM reservations 
        WHERE DATE(check_in_date) = CURDATE() AND status = 'checked_in'
    ");
    $checkins_today = $stmt->fetch()['checkins_today'];
    
    // Get check-outs today
    $stmt = $pdo->query("
        SELECT COUNT(*) as checkouts_today 
        FROM reservations 
        WHERE DATE(check_out_date) = CURDATE() AND status = 'checked_out'
    ");
    $checkouts_today = $stmt->fetch()['checkouts_today'];
    
} catch (Exception $e) {
    error_log("Error getting reservation statistics: " . $e->getMessage());
    $today_reservations = 0;
    $active_reservations = 0;
    $checkins_today = 0;
    $checkouts_today = 0;
}

// Include JavaScript for manage reservations functionality
$additional_js = '<script src="../../assets/js/manage-reservations.js"></script>';

// Include unified navigation (automatically selects based on user role)
include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';
?>

        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-semibold text-gray-800">Manage Reservations</h2>
                <div class="text-right">
                    <div id="current-date" class="text-sm text-gray-600"></div>
                    <div id="current-time" class="text-sm text-gray-600"></div>
                </div>
            </div>

            <!-- Statistics Dashboard -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-calendar-day text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Today's Arrivals</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php
session_start(); echo $today_reservations; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-key text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Check-ins Today</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php
session_start(); echo $checkins_today; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-sign-out-alt text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Check-outs Today</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php
session_start(); echo $checkouts_today; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-bed text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Active Reservations</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php
session_start(); echo $active_reservations; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filters -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Search & Filter Reservations</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <label for="search_reservation" class="block text-sm font-medium text-gray-700 mb-2">Reservation Number</label>
                        <input type="text" id="search_reservation" placeholder="Enter reservation number" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="search_guest" class="block text-sm font-medium text-gray-700 mb-2">Guest Name</label>
                        <input type="text" id="search_guest" placeholder="Enter guest name" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="search_status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="search_status" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Statuses</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="checked_in">Checked In</option>
                            <option value="checked_out">Checked Out</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="no_show">No Show</option>
                        </select>
                    </div>
                    <div>
                        <label for="search_date_range" class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                        <select id="search_date_range" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Dates</option>
                            <option value="today">Today</option>
                            <option value="tomorrow">Tomorrow</option>
                            <option value="this_week">This Week</option>
                            <option value="next_week">Next Week</option>
                            <option value="this_month">This Month</option>
                        </select>
                    </div>
                    <div class="flex items-end space-x-2">
                        <button onclick="searchReservations()" 
                                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                            <i class="fas fa-search mr-2"></i>Search
                        </button>
                        <button onclick="clearFilters()" 
                                class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

                <!-- Reservations List -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold text-gray-800">Reservations</h2>
                        <div class="flex space-x-2">
                            <button onclick="loadReservations()" 
                                    class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors">
                                <i class="fas fa-refresh mr-2"></i>Refresh
                            </button>
                            <a href="new-reservation.php" 
                               class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark transition-colors">
                                <i class="fas fa-plus mr-2"></i>New Reservation
                            </a>
                        </div>
                    </div>
                    <div id="reservations-list" class="overflow-x-auto">
                        <!-- Reservations will be loaded here -->
                    </div>
                </div>
            </div>
        </main>

    <!-- Edit Reservation Modal -->
    <div id="edit-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
        <div class="bg-white rounded-lg p-8 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Edit Reservation</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="edit-reservation-form" class="space-y-6" data-uuid="<?php
session_start(); echo uniqid(); ?>">
                <input type="hidden" id="edit_reservation_id" name="reservation_id" data-uuid="<?php
session_start(); echo uniqid(); ?>">
                
                <!-- Guest Information -->
                <div class="border-b border-gray-200 pb-6">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Guest Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="edit_first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                            <input type="text" id="edit_first_name" name="first_name" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label for="edit_last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                            <input type="text" id="edit_last_name" name="last_name" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label for="edit_email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" id="edit_email" name="email" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label for="edit_phone" class="block text-sm font-medium text-gray-700 mb-2">Phone *</label>
                            <input type="tel" id="edit_phone" name="phone" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- Reservation Details -->
                <div class="border-b border-gray-200 pb-6">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Reservation Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="edit_check_in_date" class="block text-sm font-medium text-gray-700 mb-2">Check-in Date *</label>
                            <input type="date" id="edit_check_in_date" name="check_in_date" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label for="edit_check_out_date" class="block text-sm font-medium text-gray-700 mb-2">Check-out Date *</label>
                            <input type="date" id="edit_check_out_date" name="check_out_date" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label for="edit_adults" class="block text-sm font-medium text-gray-700 mb-2">Number of Adults *</label>
                            <input type="number" id="edit_adults" name="adults" min="1" max="10" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label for="edit_children" class="block text-sm font-medium text-gray-700 mb-2">Number of Children</label>
                            <input type="number" id="edit_children" name="children" min="0" max="10" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label for="edit_room_type" class="block text-sm font-medium text-gray-700 mb-2">Room Type *</label>
                            <select id="edit_room_type" name="room_type" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select Room Type</option>
                                <option value="standard">Standard Room</option>
                                <option value="deluxe">Deluxe Room</option>
                                <option value="suite">Suite</option>
                                <option value="presidential">Presidential Suite</option>
                            </select>
                        </div>
                        <div>
                            <label for="edit_special_requests" class="block text-sm font-medium text-gray-700 mb-2">Special Requests</label>
                            <textarea id="edit_special_requests" name="special_requests" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Discount & Voucher Section -->
                <div class="border-b border-gray-200 pb-6">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Discounts & Vouchers</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="edit_discount_template" class="block text-sm font-medium text-gray-700 mb-2">Apply Discount</label>
                            <select id="edit_discount_template" name="discount_template_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">No Discount</option>
                                <!-- Discounts will be loaded dynamically -->
                            </select>
                        </div>
                        <div>
                            <label for="edit_voucher_code" class="block text-sm font-medium text-gray-700 mb-2">Voucher Code</label>
                            <div class="flex space-x-2">
                                <input type="text" id="edit_voucher_code" name="voucher_code" placeholder="Enter voucher code" 
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <button type="button" onclick="validateVoucher()" 
                                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                                    <i class="fas fa-check"></i>
                                </button>
                            </div>
                            <div id="voucher-validation-result" class="mt-2 hidden">
                                <!-- Voucher validation result will be displayed here -->
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div id="applied-discounts" class="space-y-2">
                            <!-- Applied discounts will be displayed here -->
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="closeEditModal()" 
                            class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-6 py-2 bg-primary text-white rounded-md hover:bg-primary-dark transition-colors">
                        <i class="fas fa-save mr-2"></i>Update Reservation
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cancel Confirmation Modal -->
    <div id="cancel-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
            <div class="text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Cancel Reservation</h3>
                <p class="text-gray-600 mb-4">Are you sure you want to cancel this reservation? This action cannot be undone.</p>
                <div class="space-y-2 text-sm text-gray-600 mb-6">
                    <p><strong>Reservation:</strong> <span id="cancel_reservation_number"></span></p>
                    <p><strong>Guest:</strong> <span id="cancel_guest_name"></span></p>
                </div>
                <div class="flex justify-center space-x-4">
                    <button onclick="closeCancelModal()" 
                            class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                        Keep Reservation
                    </button>
                    <button onclick="confirmCancelReservation()" 
                            class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                        <i class="fas fa-times mr-2"></i>Cancel Reservation
                    </button>
                </div>
            </div>
        </div>
    </div>

<?php
session_start(); include '../../includes/footer.php'; ?>

<script>
    // Update current date and time
    function updateDateTime() {
        const now = new Date();
        document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    }

    // Update time every second
    setInterval(updateDateTime, 1000);
    updateDateTime();

    // Load discounts when modal opens
    async function loadDiscounts() {
        try {
            const response = await fetch('../../api/get-discounts.php', {
                method: 'GET',
                credentials: 'include',
                headers: {
                    'X-API-Key': 'pms_users_api_2024'
                }
            });
            const result = await response.json();
            
            if (result.success && result.discounts) {
                const discountSelect = document.getElementById('edit_discount_template');
                discountSelect.innerHTML = '<option value="">No Discount</option>';
                
                result.discounts.forEach(discount => {
                    const option = document.createElement('option');
                    option.value = discount.id;
                    option.textContent = `${discount.discount_name} (${discount.discount_type === 'percentage' ? discount.discount_value + '%' : '₱' + discount.discount_value})`;
                    discountSelect.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading discounts:', error);
        }
    }

    // Validate voucher code
    async function validateVoucher() {
        const voucherCode = document.getElementById('edit_voucher_code').value.trim();
        const resultDiv = document.getElementById('voucher-validation-result');
        
        if (!voucherCode) {
            alert('Please enter a voucher code.');
            return;
        }

        try {
            const response = await fetch('../../api/validate-voucher.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-API-Key': 'pms_users_api_2024'
                },
                body: JSON.stringify({ voucher_code: voucherCode })
            });

            const result = await response.json();

            if (result.success) {
                const voucher = result.voucher;
                resultDiv.innerHTML = `
                    <div class="bg-green-50 border border-green-200 rounded-md p-3">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-green-400"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-green-800">Valid Voucher</h4>
                                <div class="mt-1 text-sm text-green-700">
                                    <p><strong>Type:</strong> ${voucher.voucher_type}</p>
                                    <p><strong>Value:</strong> ${voucher.voucher_type === 'percentage' ? voucher.voucher_value + '%' : '₱' + voucher.voucher_value}</p>
                                    <p><strong>Valid Until:</strong> ${voucher.valid_until}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                resultDiv.classList.remove('hidden');
                
                // Add voucher to applied discounts
                addAppliedDiscount('voucher', voucher.voucher_code, voucher.voucher_type, voucher.voucher_value);
            } else {
                resultDiv.innerHTML = `
                    <div class="bg-red-50 border border-red-200 rounded-md p-3">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-times-circle text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-red-800">Invalid Voucher</h4>
                                <p class="mt-1 text-sm text-red-700">${result.message}</p>
                            </div>
                        </div>
                    </div>
                `;
                resultDiv.classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error validating voucher:', error);
            alert('An error occurred while validating the voucher.');
        }
    }

    // Add applied discount to display
    function addAppliedDiscount(type, name, discountType, value) {
        const container = document.getElementById('applied-discounts');
        const discountId = type + '_' + Date.now();
        
        const discountElement = document.createElement('div');
        discountElement.id = discountId;
        discountElement.className = 'flex items-center justify-between p-3 bg-blue-50 border border-blue-200 rounded-md';
        
        const valueText = discountType === 'percentage' ? value + '%' : '₱' + value;
        
        discountElement.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'voucher' ? 'ticket-alt' : 'tag'} text-blue-600 mr-2"></i>
                <span class="text-sm font-medium text-blue-900">${name}</span>
                <span class="ml-2 text-sm text-blue-700">(${valueText})</span>
            </div>
            <button onclick="removeAppliedDiscount('${discountId}')" class="text-red-600 hover:text-red-800">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        container.appendChild(discountElement);
    }

    // Remove applied discount
    function removeAppliedDiscount(discountId) {
        const element = document.getElementById(discountId);
        if (element) {
            element.remove();
        }
    }

    // Enhanced edit reservation function
    function editReservation(reservationId) {
        // Load discounts when opening modal
        loadDiscounts();
        
        // Clear previous voucher validation
        document.getElementById('voucher-validation-result').classList.add('hidden');
        document.getElementById('applied-discounts').innerHTML = '';
        
        // Call the actual edit function from the ReservationManager
        if (window.reservationManager) {
            window.reservationManager.editReservation(reservationId);
        } else {
            console.error('ReservationManager not found');
        }
    }

    // Enhanced search function with date range support
    function searchReservations() {
        const reservationNumber = document.getElementById('search_reservation').value;
        const guestName = document.getElementById('search_guest').value;
        const status = document.getElementById('search_status').value;
        const dateRange = document.getElementById('search_date_range').value;
        
        // Show loading
        const container = document.getElementById('reservations-list');
        container.innerHTML = '<div class="flex items-center justify-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div></div>';
        
        // Build query parameters
        const params = new URLSearchParams();
        if (reservationNumber) params.append('reservation_number', reservationNumber);
        if (guestName) params.append('guest_name', guestName);
        if (status) params.append('status', status);
        
        // Add date range filters
        if (dateRange) {
            const today = new Date();
            let dateFrom = '', dateTo = '';
            
            switch (dateRange) {
                case 'today':
                    dateFrom = dateTo = today.toISOString().split('T')[0];
                    break;
                case 'tomorrow':
                    const tomorrow = new Date(today);
                    tomorrow.setDate(tomorrow.getDate() + 1);
                    dateFrom = dateTo = tomorrow.toISOString().split('T')[0];
                    break;
                case 'this_week':
                    const startOfWeek = new Date(today);
                    startOfWeek.setDate(today.getDate() - today.getDay());
                    const endOfWeek = new Date(startOfWeek);
                    endOfWeek.setDate(startOfWeek.getDate() + 6);
                    dateFrom = startOfWeek.toISOString().split('T')[0];
                    dateTo = endOfWeek.toISOString().split('T')[0];
                    break;
                case 'next_week':
                    const nextWeekStart = new Date(today);
                    nextWeekStart.setDate(today.getDate() + (7 - today.getDay()));
                    const nextWeekEnd = new Date(nextWeekStart);
                    nextWeekEnd.setDate(nextWeekStart.getDate() + 6);
                    dateFrom = nextWeekStart.toISOString().split('T')[0];
                    dateTo = nextWeekEnd.toISOString().split('T')[0];
                    break;
                case 'this_month':
                    const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
                    const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                    dateFrom = startOfMonth.toISOString().split('T')[0];
                    dateTo = endOfMonth.toISOString().split('T')[0];
                    break;
            }
            
            if (dateFrom) params.append('date_from', dateFrom);
            if (dateTo) params.append('date_to', dateTo);
        }
        
        // Fetch search results
        fetch(`../../api/get-all-reservations.php?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayReservations(data.reservations);
                } else {
                    container.innerHTML = `
                        <div class="px-6 py-12 text-center">
                            <i class="fas fa-search text-gray-400 text-4xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No search results found</h3>
                            <p class="text-gray-500">Try adjusting your search criteria or filters.</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error searching reservations:', error);
                container.innerHTML = `
                    <div class="px-6 py-12 text-center">
                        <i class="fas fa-exclamation-triangle text-red-400 text-4xl mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Error searching reservations</h3>
                        <p class="text-gray-500">Unable to search reservations. Please try again.</p>
                    </div>
                `;
            });
    }

    // Auto-refresh statistics every 30 seconds
    setInterval(() => {
        // You could add an API call here to refresh the statistics
        // For now, we'll just reload the page to get fresh data
        // In a production environment, you'd want to use AJAX to update just the stats
    }, 30000);

    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + F to focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            e.preventDefault();
            document.getElementById('search_guest').focus();
        }
        
        // Escape to clear filters
        if (e.key === 'Escape') {
            clearFilters();
        }
    });
    
    // Prevent classifier.js UUID errors
    document.addEventListener('DOMContentLoaded', function() {
        // Add UUIDs to all form elements that might be processed by classifier.js
        const formElements = document.querySelectorAll('input, select, textarea, button');
        formElements.forEach(function(element) {
            if (!element.getAttribute('data-uuid')) {
                element.setAttribute('data-uuid', 'uuid-' + Math.random().toString(36).substr(2, 9));
            }
        });
        
        // Override any classifier.js functions that might cause errors
        if (typeof window.dre === 'function') {
            const originalDre = window.dre;
            window.dre = function(input) {
                if (!input || !input.uuid) {
                    input = input || {};
                    input.uuid = 'uuid-' + Math.random().toString(36).substr(2, 9);
                }
                return originalDre.call(this, input);
            };
        }
    });
</script>
