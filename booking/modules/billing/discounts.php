<?php
/**
 * Discount Management
 * Hotel PMS Training System for Students
 */

session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Load dynamic data
$discountMetrics = getDiscountMetrics();
$discounts = getDiscounts('', null);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

// Set page title
$page_title = 'Discount Management';

// Include header
include '../../includes/header-unified.php';
// Include sidebar
include '../../includes/sidebar-unified.php';
?>

        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Discount Management</h2>
                <div class="flex items-center space-x-4">
                    <button onclick="openCreateDiscountModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-plus mr-2"></i>Create Discount
                    </button>
                    <button onclick="openApplyDiscountModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-gift mr-2"></i>Apply Discount
                    </button>
                </div>
            </div>

            <!-- Discount Statistics (Dynamic) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-tags text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Discounts</p>
                            <p class="text-2xl font-semibold text-gray-900"><?= number_format($discountMetrics['total_discounts'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-dollar-sign text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Savings</p>
                            <p class="text-2xl font-semibold text-gray-900">₱<?= number_format($discountMetrics['total_amount'] ?? 0, 2); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-users text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Avg. Discount</p>
                            <p class="text-2xl font-semibold text-gray-900">₱<?= number_format($discountMetrics['average_amount'] ?? 0, 2); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-percentage text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Types Tracked</p>
                            <p class="text-2xl font-semibold text-gray-900"><?= count($discountMetrics['type_counts'] ?? []); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Discount Types -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <?php
                // Get discounts by type
                $percentageDiscounts = array_filter($discounts, function($d) { return $d['discount_type'] === 'percentage'; });
                $fixedAmountDiscounts = array_filter($discounts, function($d) { return $d['discount_type'] === 'fixed_amount'; });
                $packageDiscounts = array_filter($discounts, function($d) { return $d['discount_type'] === 'package_deal'; });
                ?>

                <!-- Percentage Discount -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Percentage Discounts</h3>
                        <i class="fas fa-percentage text-blue-600 text-xl"></i>
                    </div>
                    <div class="space-y-3">
                        <?php if (!empty($percentageDiscounts)): ?>
                            <?php foreach (array_slice($percentageDiscounts, 0, 3) as $discount): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-900"><?= htmlspecialchars($discount['discount_name'] ?? 'Unknown'); ?></p>
                                        <p class="text-sm text-gray-500"><?= htmlspecialchars($discount['description'] ?? 'No description'); ?></p>
                                    </div>
                                    <span class="px-2 py-1 <?= ($discount['is_active'] ?? 0) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?> text-xs font-semibold rounded-full">
                                        <?= ($discount['is_active'] ?? 0) ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-gray-500 py-4">No percentage discounts found</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Fixed Amount Discount -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Fixed Amount</h3>
                        <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                    </div>
                    <div class="space-y-3">
                        <?php if (!empty($fixedAmountDiscounts)): ?>
                            <?php foreach (array_slice($fixedAmountDiscounts, 0, 3) as $discount): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-900"><?= htmlspecialchars($discount['discount_name'] ?? 'Unknown'); ?></p>
                                        <p class="text-sm text-gray-500"><?= htmlspecialchars($discount['description'] ?? 'No description'); ?></p>
                                    </div>
                                    <span class="px-2 py-1 <?= ($discount['is_active'] ?? 0) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?> text-xs font-semibold rounded-full">
                                        <?= ($discount['is_active'] ?? 0) ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-gray-500 py-4">No fixed amount discounts found</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Package Discounts -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Package Deals</h3>
                        <i class="fas fa-gift text-purple-600 text-xl"></i>
                    </div>
                    <div class="space-y-3">
                        <?php if (!empty($packageDiscounts)): ?>
                            <?php foreach (array_slice($packageDiscounts, 0, 3) as $discount): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-900"><?= htmlspecialchars($discount['discount_name'] ?? 'Unknown'); ?></p>
                                        <p class="text-sm text-gray-500"><?= htmlspecialchars($discount['description'] ?? 'No description'); ?></p>
                                    </div>
                                    <span class="px-2 py-1 <?= ($discount['is_active'] ?? 0) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?> text-xs font-semibold rounded-full">
                                        <?= ($discount['is_active'] ?? 0) ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-gray-500 py-4">No package deals found</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Create Discount Form -->
            <div id="create-discount-form" class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Create New Discount</h3>
                <form id="discount-form" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Discount Name *</label>
                            <input type="text" name="discount_name" id="discount_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter discount name" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Discount Type *</label>
                            <select name="discount_type" id="discount_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Select Type</option>
                                <option value="percentage">Percentage</option>
                                <option value="fixed_amount">Fixed Amount</option>
                                <option value="package_deal">Package Deal</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Discount Value *</label>
                            <input type="number" name="discount_value" id="discount_value" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter discount value" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Stay</label>
                            <input type="number" name="minimum_stay" id="minimum_stay" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Minimum nights">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Valid From</label>
                            <input type="date" name="valid_from" id="valid_from" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Valid Until</label>
                                    <input type="date" name="valid_until" id="valid_until" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            
                            <!-- Room Selection Section -->
                            <div class="border-t pt-6">
                                <h4 class="text-lg font-medium text-gray-800 mb-4">Room Selection</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Apply To</label>
                                        <select name="apply_to_all_rooms" id="apply_to_all_rooms" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="toggleRoomSelection()">
                                            <option value="1">All Rooms</option>
                                            <option value="0">Specific Room</option>
                                            <option value="2">Room Type</option>
                                        </select>
                                    </div>
                                    <div id="room_selection" style="display: none;">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Room</label>
                                        <select name="room_id" id="room_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="">Loading rooms...</option>
                                        </select>
                                    </div>
                                    <div id="room_type_selection" style="display: none;">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Room Type</label>
                                        <select name="room_type" id="room_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="">Select Room Type</option>
                                            <option value="Standard">Standard</option>
                                            <option value="Deluxe">Deluxe</option>
                                            <option value="Suite">Suite</option>
                                            <option value="Presidential">Presidential</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea name="description" id="description" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3" placeholder="Enter discount description"></textarea>
                            </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label class="ml-2 block text-sm text-gray-900">
                            Make this discount active immediately
                        </label>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" onclick="closeCreateDiscountModal()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                            Create Discount
                        </button>
                    </div>
                </form>
            </div>

            <!-- Discounts Table (Dynamic) -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">All Discounts</h3>
                </div>
                <div class="overflow-x-auto">
                    <?php if (!empty($discounts)): ?>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bill</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applied</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($discounts as $d): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($d['reason'] ?? ($d['description'] ?? 'Discount')); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars(ucfirst($d['discount_type'] ?? 'Unknown')); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱<?= number_format($d['discount_amount'] ?? 0, 2); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#<?= htmlspecialchars($d['bill_number'] ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($d['guest_name'] ?? 'Unknown Guest'); ?> (Room <?= htmlspecialchars($d['room_number'] ?? 'N/A'); ?>)</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($d['created_at'] ?? 'N/A'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="p-6 text-center text-gray-500">No discounts found.</div>
                    <?php endif; ?>
                </div>
        </div>
    </main>

    <!-- Apply Discount Modal -->
    <div id="apply-discount-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Apply Discount</h3>
                    <button onclick="closeApplyDiscountModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="apply-discount-form" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Discount</label>
                        <select name="discount_template_id" id="discount_template_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Select Discount Template</option>
                            <?php foreach ($discounts as $discount): ?>
                                <option value="<?= $discount['id'] ?>">
                                    <?= htmlspecialchars($discount['discount_name']) ?> 
                                    (<?= $discount['discount_type'] === 'percentage' ? $discount['discount_value'] . '%' : '₱' . $discount['discount_value'] ?>)
                                    - <?= htmlspecialchars($discount['guest_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Reservation</label>
                        <select name="reservation_id" id="reservation_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Loading reservations...</option>
                        </select>
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeApplyDiscountModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            Apply Discount
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Include footer -->
    <?php include '../../includes/footer.php'; ?>

        <script>
            // Suppress classifier.js errors
            window.addEventListener('error', function(e) {
                if (e.message && e.message.includes('Failed to link vertex and fragment shaders')) {
                    e.preventDefault();
                    return false;
                }
            });

            // Suppress unhandled promise rejections
            window.addEventListener('unhandledrejection', function(e) {
                if (e.reason && e.reason.message && e.reason.message.includes('Failed to link vertex and fragment shaders')) {
                    e.preventDefault();
                    return false;
                }
            });

            // Modal functions
            function openCreateDiscountModal() {
                // Scroll to the form
                document.getElementById('create-discount-form').scrollIntoView({ behavior: 'smooth' });
                // Focus on the first input
                document.getElementById('discount_name').focus();
            }

            function closeCreateDiscountModal() {
                document.getElementById('discount-form').reset();
            }

            function openApplyDiscountModal() {
                // Show apply discount modal
                document.getElementById('apply-discount-modal').classList.remove('hidden');
                loadReservationsForDiscount();
            }

            function closeApplyDiscountModal() {
                document.getElementById('apply-discount-modal').classList.add('hidden');
                document.getElementById('apply-discount-form').reset();
            }

            // Load reservations for discount application
            async function loadReservationsForDiscount() {
                try {
                    const response = await fetch('../../api/get-reservations.php', {
                        method: 'GET',
                        credentials: 'include',
                        headers: {
                            'X-API-Key': 'pms_users_api_2024'
                        }
                    });
                    const result = await response.json();
                    
                    if (result.success && result.reservations) {
                        const reservationSelect = document.getElementById('reservation_id');
                        reservationSelect.innerHTML = '<option value="">Select Reservation</option>';
                        result.reservations.forEach(reservation => {
                            const option = document.createElement('option');
                            option.value = reservation.id;
                            option.textContent = `#${reservation.id} - ${reservation.guest_name} (Room ${reservation.room_number}) - ₱${reservation.total_amount}`;
                            reservationSelect.appendChild(option);
                        });
                    }
                } catch (error) {
                    console.error('Error loading reservations:', error);
                }
            }

            // Load rooms for selection
            async function loadRooms() {
                try {
                    const response = await fetch('../../api/get-rooms.php', {
                        method: 'GET',
                        credentials: 'include',
                        headers: {
                            'X-API-Key': 'pms_users_api_2024'
                        }
                    });
                    const result = await response.json();
                    
                    if (result.success && result.rooms) {
                        const roomSelect = document.getElementById('room_id');
                        roomSelect.innerHTML = '<option value="">Select Room</option>';
                        result.rooms.forEach(room => {
                            const option = document.createElement('option');
                            option.value = room.id;
                            const roomType = room.room_type.charAt(0).toUpperCase() + room.room_type.slice(1);
                            option.textContent = `Room ${room.room_number} (${roomType})`;
                            roomSelect.appendChild(option);
                        });
                    }
                } catch (error) {
                    console.error('Error loading rooms:', error);
                }
            }

            // Toggle room selection based on apply_to_all_rooms value
            function toggleRoomSelection() {
                const applyTo = document.getElementById('apply_to_all_rooms').value;
                const roomSelection = document.getElementById('room_selection');
                const roomTypeSelection = document.getElementById('room_type_selection');
                
                // Hide both initially
                roomSelection.style.display = 'none';
                roomTypeSelection.style.display = 'none';
                
                if (applyTo === '0') {
                    // Specific room
                    roomSelection.style.display = 'block';
                    loadRooms();
                } else if (applyTo === '2') {
                    // Room type
                    roomTypeSelection.style.display = 'block';
                }
            }

            // Form submission
            document.getElementById('discount-form').addEventListener('submit', async function(event) {
                event.preventDefault();

                const form = event.currentTarget;
                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating...';

                const formData = new FormData(form);
                const payload = Object.fromEntries(formData.entries());

                // Validate required fields
                if (!payload.discount_name || !payload.discount_type || !payload.discount_value) {
                    alert('Please fill in all required fields.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    return;
                }

                // Validate discount value
                if (parseFloat(payload.discount_value) <= 0) {
                    alert('Please enter a valid discount value greater than 0.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    return;
                }

                // Validate percentage discount
                if (payload.discount_type === 'percentage' && parseFloat(payload.discount_value) > 100) {
                    alert('Percentage discount cannot exceed 100%.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    return;
                }

                // Validate room selection
                const applyTo = payload.apply_to_all_rooms;
                if (applyTo === '0' && !payload.room_id) {
                    alert('Please select a specific room.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    return;
                }
                if (applyTo === '2' && !payload.room_type) {
                    alert('Please select a room type.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    return;
                }

                try {
                    const response = await fetch('../../api/create-discount.php', {
                        method: 'POST',
                        credentials: 'include',
                        headers: { 
                            'Content-Type': 'application/json',
                            'X-API-Key': 'pms_users_api_2024'
                        },
                        body: JSON.stringify(payload)
                    });

                    const result = await response.json();

                    if (result.success) {
                        alert('Discount created successfully!');
                        window.location.reload();
                    } else {
                        alert('Error: ' + (result.message || 'Failed to create discount.'));
                    }
                } catch (error) {
                    console.error('Error creating discount:', error);
                    alert('An unexpected error occurred while creating the discount.');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });

            // Apply Discount form submission
            document.getElementById('apply-discount-form').addEventListener('submit', async function(event) {
                event.preventDefault();

                const form = event.currentTarget;
                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Applying...';

                const formData = new FormData(form);
                const payload = Object.fromEntries(formData.entries());

                // Validate required fields
                if (!payload.discount_template_id || !payload.reservation_id) {
                    alert('Please select both discount and reservation.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    return;
                }

                try {
                    const response = await fetch('../../api/apply-discount.php', {
                        method: 'POST',
                        credentials: 'include',
                        headers: { 
                            'Content-Type': 'application/json',
                            'X-API-Key': 'pms_users_api_2024'
                        },
                        body: JSON.stringify(payload)
                    });

                    const result = await response.json();

                    if (result.success) {
                        alert(`Discount applied successfully! Amount: ₱${result.discount_amount}, New Total: ₱${result.new_total}`);
                        closeApplyDiscountModal();
                        location.reload();
                    } else {
                        alert('Error: ' + (result.message || 'Failed to apply discount.'));
                    }
                } catch (error) {
                    console.error('Error applying discount:', error);
                    alert('An unexpected error occurred while applying the discount.');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });

            // Set default dates
            document.addEventListener('DOMContentLoaded', function() {
                const today = new Date().toISOString().split('T')[0];
                const nextMonth = new Date();
                nextMonth.setMonth(nextMonth.getMonth() + 1);
                const nextMonthStr = nextMonth.toISOString().split('T')[0];
                
                document.getElementById('valid_from').value = today;
                document.getElementById('valid_until').value = nextMonthStr;
            });
        </script>
    </body>
</html>
