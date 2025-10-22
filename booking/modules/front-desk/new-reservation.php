<?php
require_once dirname(__DIR__, 2) . '/../vps_session_fix.php';
require_once dirname(__DIR__, 2) . '/../includes/database.php';
require_once '../../includes/functions.php';
// Check if user is logged in and has front desk access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['front_desk', 'manager'])) {
    header('Location: ../../login.php');
    exit();
}

$user_role = $_SESSION['user_role'];
$user_name = $_SESSION['user_name'];

// Get available rooms
$available_rooms = getAvailableRooms();
$room_types = getRoomTypes();

// Set page title
$page_title = 'New Reservation';
$page_subtitle = 'Create a new guest reservation';

// Include unified navigation (automatically selects based on user role)
include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';
?>

        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-semibold text-gray-800">New Reservation</h2>
                <div class="text-right">
                    <div id="current-date" class="text-sm text-gray-600"></div>
                    <div id="current-time" class="text-sm text-gray-600"></div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-6 shadow-md">
                <h3 class="text-xl font-semibold text-gray-800 mb-6">Create New Reservation</h3>
                    
                    <form id="reservation-form" class="space-y-6">
                        <!-- Guest Selection -->
                        <div class="border-b border-gray-200 pb-6">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-user mr-2 text-primary"></i>Guest Selection
                            </h4>
                            <div>
                                <label for="guest_id" class="block text-sm font-medium text-gray-700 mb-2">Select Guest *</label>
                                <select id="guest_id" name="guest_id" required 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">Select a guest...</option>
                                    <?php
                                    // Get all guests for dropdown
                                    $guests = getAllGuests();
                                    foreach ($guests as $guest) {
                                        $guest_name = htmlspecialchars($guest['first_name'] . ' ' . $guest['last_name']);
                                        $guest_email = htmlspecialchars($guest['email'] ?? '');
                                        $guest_phone = htmlspecialchars($guest['phone'] ?? '');
                                        $vip_badge = $guest['is_vip'] ? ' (VIP)' : '';
                                        $display_text = $guest_name . $vip_badge . ($guest_email ? ' - ' . $guest_email : '');
                                        echo "<option value=\"{$guest['id']}\">{$display_text}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- Reservation Details -->
                        <div class="border-b border-gray-200 pb-6">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-calendar-alt mr-2 text-primary"></i>Reservation Details
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div>
                                    <label for="check_in_date" class="block text-sm font-medium text-gray-700 mb-2">Check-in Date *</label>
                                    <input type="date" id="check_in_date" name="check_in_date" required 
                                           min="<?php echo date('Y-m-d'); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label for="check_out_date" class="block text-sm font-medium text-gray-700 mb-2">Check-out Date *</label>
                                    <input type="date" id="check_out_date" name="check_out_date" required 
                                           min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label for="adults" class="block text-sm font-medium text-gray-700 mb-2">Number of Adults *</label>
                                    <input type="number" id="adults" name="adults" min="1" max="10" value="1" required 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label for="children" class="block text-sm font-medium text-gray-700 mb-2">Number of Children</label>
                                    <input type="number" id="children" name="children" min="0" max="10" value="0" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label for="room_type" class="block text-sm font-medium text-gray-700 mb-2">Room Type *</label>
                                    <select id="room_type" name="room_type" required 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">Select Room Type</option>
                                        <?php foreach ($room_types as $type => $info): ?>
                                        <option value="<?php echo $type; ?>" data-rate="<?php echo $info['rate']; ?>">
                                            <?php echo $info['name']; ?> - $<?php echo $info['rate']; ?>/night
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label for="booking_source" class="block text-sm font-medium text-gray-700 mb-2">Booking Source *</label>
                                    <select id="booking_source" name="booking_source" required 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="walk_in">Walk-in</option>
                                        <option value="online">Online</option>
                                        <option value="phone">Phone</option>
                                        <option value="travel_agent">Travel Agent</option>
                                    </select>
                                </div>
                                <div class="md:col-span-2 lg:col-span-3">
                                    <label for="special_requests" class="block text-sm font-medium text-gray-700 mb-2">Special Requests</label>
                                    <textarea id="special_requests" name="special_requests" rows="3" 
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                              placeholder="Any special requests or preferences..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Room Assignment -->
                        <div class="border-b border-gray-200 pb-6">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-bed mr-2 text-primary"></i>Room Assignment
                            </h4>
                            <div id="available-rooms" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                <!-- Available rooms will be loaded here -->
                            </div>
                        </div>

                        <!-- Pricing Summary -->
                        <div class="border-b border-gray-200 pb-6">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-calculator mr-2 text-primary"></i>Pricing Summary
                            </h4>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-gray-600">Room Rate (per night):</span>
                                    <span id="room-rate" class="font-medium">₱0.00</span>
                                </div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-gray-600">Number of Nights:</span>
                                    <span id="nights" class="font-medium">0</span>
                                </div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span id="subtotal" class="font-medium">₱0.00</span>
                                </div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-gray-600">Tax (10%):</span>
                                    <span id="tax" class="font-medium">₱0.00</span>
                                </div>
                                <div class="border-t border-gray-300 pt-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-lg font-semibold text-gray-900">Total Amount:</span>
                                        <span id="total-amount" class="text-lg font-semibold text-primary">₱0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-4">
                            <button type="button" onclick="window.history.back()" 
                                    class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="px-6 py-2 bg-primary text-white rounded-md hover:bg-primary-dark transition-colors">
                                <i class="fas fa-save mr-2"></i>Create Reservation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const guestSelect = document.getElementById('guest_id');
    
    // Form submission
    document.getElementById('reservation-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!guestSelect.value) {
            alert('Please select a guest before creating the reservation.');
            guestSelect.focus();
            return false;
        }
        
        // Collect form data
        const formData = new FormData(this);
        const reservationData = {
            guest_id: parseInt(guestSelect.value), // Ensure it's an integer
            check_in_date: formData.get('check_in_date'),
            check_out_date: formData.get('check_out_date'),
            adults: parseInt(formData.get('adults')),
            children: parseInt(formData.get('children') || 0),
            room_type: formData.get('room_type'),
            booking_source: formData.get('booking_source'),
            special_requests: formData.get('special_requests') || ''
        };
        
        
        // Submit reservation
        submitReservation(reservationData);
    });
    
    // Submit reservation function
    async function submitReservation(data) {
        try {
            const response = await fetch('../../api/create-reservation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert(`Reservation created successfully!\nReservation Number: ${result.reservation_number}`);
                window.location.href = 'manage-reservations.php';
            } else {
                alert(`Error creating reservation: ${result.message}`);
            }
        } catch (error) {
            console.error('Error submitting reservation:', error);
            alert('Error creating reservation. Please try again.');
        }
    }
});
</script>

<?php include '../../includes/footer.php'; ?>
