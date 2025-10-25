<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);


/**
 * VIP Guest Management
 * Hotel PMS Training System for Students
 */

session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

// Set page title
$page_title = 'VIP Guest Management';

// Include header
include '../../includes/header-unified.php';
// Include sidebar
include '../../includes/sidebar-unified.php';
?>

        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">VIP Guest Management</h2>
                <div class="flex items-center space-x-4">
                    <button onclick="openAddVipModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-plus mr-2"></i>Add VIP Guest
                    </button>
                    <button onclick="openVipAmenitiesModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-gift mr-2"></i>Send VIP Amenities
                    </button>
                </div>
            </div>

            <!-- VIP Statistics (Dynamic) -->
            <?php $vip_stats = getVipDashboardStats(); ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-crown text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total VIP Guests</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($vip_stats['total_vip']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-bed text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Currently Staying</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($vip_stats['currently_staying']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-star text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Average Rating</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $vip_stats['average_rating'] !== null ? $vip_stats['average_rating'] . '/5' : 'N/A'; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-dollar-sign text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Monthly Revenue</p>
                            <p class="text-2xl font-semibold text-gray-900">₱<?php echo number_format($vip_stats['monthly_revenue'], 2); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- VIP Tiers (Dynamic counts per tier) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <?php $tiers = getVipTiersSummary(); $tierMap = [];
                foreach ($tiers as $t) { $tierMap[strtolower($t['loyalty_tier'] ?? 'unknown')] = (int)$t['members']; }
                ?>
                <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-lg shadow p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">Platinum VIP</h3>
                        <i class="fas fa-crown text-yellow-400 text-xl"></i>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-300">Members:</span>
                            <span class="font-semibold"><?php echo $tierMap['platinum'] ?? 0; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-300">Benefits:</span>
                            <span class="text-sm">Suite Upgrade</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-300">Concierge:</span>
                            <span class="text-sm">24/7</span>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-yellow-600 to-yellow-700 rounded-lg shadow p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">Gold VIP</h3>
                        <i class="fas fa-medal text-yellow-200 text-xl"></i>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-yellow-100">Members:</span>
                            <span class="font-semibold"><?php echo $tierMap['gold'] ?? 0; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-yellow-100">Benefits:</span>
                            <span class="text-sm">Room Upgrade</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-yellow-100">Concierge:</span>
                            <span class="text-sm">Business Hours</span>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-gray-400 to-gray-500 rounded-lg shadow p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">Silver VIP</h3>
                        <i class="fas fa-award text-gray-200 text-xl"></i>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-100">Members:</span>
                            <span class="font-semibold"><?php echo $tierMap['silver'] ?? 0; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-100">Benefits:</span>
                            <span class="text-sm">Priority Service</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-100">Concierge:</span>
                            <span class="text-sm">On Request</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- VIP Services -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">VIP Services & Amenities</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-car text-blue-600 mr-2"></i>
                            <h4 class="font-semibold text-gray-800">Airport Transfer</h4>
                        </div>
                        <p class="text-sm text-gray-600">Complimentary airport pickup and drop-off</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-wine-glass text-purple-600 mr-2"></i>
                            <h4 class="font-semibold text-gray-800">Welcome Amenities</h4>
                        </div>
                        <p class="text-sm text-gray-600">Champagne, fruits, and personalized welcome</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-concierge-bell text-green-600 mr-2"></i>
                            <h4 class="font-semibold text-gray-800">Personal Concierge</h4>
                        </div>
                        <p class="text-sm text-gray-600">Dedicated concierge for all requests</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-utensils text-orange-600 mr-2"></i>
                            <h4 class="font-semibold text-gray-800">Room Service</h4>
                        </div>
                        <p class="text-sm text-gray-600">Priority room service and special menu</p>
                    </div>
                </div>
            </div>

            <!-- VIP Guests Table (Dynamic) -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">VIP Guest Directory</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">VIP Guest</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tier</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Special Requests</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach (getVipGuests() as $vip): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12">
                                            <div class="h-12 w-12 rounded-full bg-gradient-to-r from-yellow-400 to-yellow-600 flex items-center justify-center">
                                                <span class="text-white font-bold"><?php echo strtoupper(substr($vip['name'],0,1)); ?></span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($vip['name']); ?></div>
                                            <div class="text-sm text-gray-500">VIP Guest</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo strtolower($vip['loyalty_tier']) === 'platinum' ? 'bg-gray-800 text-white' : (strtolower($vip['loyalty_tier']) === 'gold' ? 'bg-yellow-600 text-white' : 'bg-gray-500 text-white'); ?>">
                                        <?php echo $vip['loyalty_tier'] ? htmlspecialchars(ucfirst($vip['loyalty_tier'])) : '—'; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php $stay_map = ['checked_in' => ['Currently Staying','bg-green-100 text-green-800'], 'not_staying' => ['Not Staying','bg-gray-100 text-gray-800']];
                                    $stay = $stay_map[$vip['stay_status']] ?? ['Unknown','bg-gray-100 text-gray-800']; ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $stay[1]; ?>">
                                        <?php echo $stay[0]; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php 
                                    if ($vip['room_number']) {
                                        echo htmlspecialchars('Room ' . $vip['room_number']);
                                        if ($vip['room_type']) {
                                            echo ' (' . htmlspecialchars($vip['room_type']) . ')';
                                        }
                                    } else {
                                        echo '—';
                                    }
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php 
                                    if ($vip['special_requests']) {
                                        $requests = htmlspecialchars($vip['special_requests']);
                                        echo strlen($requests) > 30 ? substr($requests, 0, 30) . '...' : $requests;
                                    } else {
                                        echo '—';
                                    }
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="viewVipGuest(<?php echo $vip['id']; ?>)" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-eye mr-1"></i>View
                                    </button>
                                    <?php if ($vip['reservation_id']): ?>
                                    <button onclick="manageVipServices(<?php echo $vip['id']; ?>, <?php echo $vip['reservation_id']; ?>)" class="text-green-600 hover:text-green-900">
                                        <i class="fas fa-concierge-bell mr-1"></i>Services
                                    </button>
                                    <?php else: ?>
                                    <button onclick="createVipReservation(<?php echo $vip['id']; ?>)" class="text-purple-600 hover:text-purple-900">
                                        <i class="fas fa-calendar-plus mr-1"></i>Book
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Add VIP Guest Modal -->
        <div id="add-vip-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800">Promote Guest to VIP</h3>
                        <button onclick="closeAddVipModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                <form id="add-vip-form" class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="guest_id" class="block text-sm font-medium text-gray-700 mb-2">Select Guest *</label>
                            <select id="guest_id" name="guest_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select a guest to promote to VIP...</option>
                                <?php
                                // Get all non-VIP guests
                                $stmt = $pdo->query("SELECT id, first_name, last_name, email FROM guests WHERE is_vip = 0 ORDER BY first_name, last_name");
                                while($guest = $stmt->fetch()) {
                                    $guest_name = htmlspecialchars($guest['first_name'] . ' ' . $guest['last_name']);
                                    $guest_email = htmlspecialchars($guest['email'] ?? '');
                                    $display_text = $guest_name . ($guest_email ? ' - ' . $guest_email : '');
                                    echo "<option value=\"{$guest['id']}\">{$display_text}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label for="loyalty_tier" class="block text-sm font-medium text-gray-700 mb-2">VIP Tier *</label>
                            <select id="loyalty_tier" name="loyalty_tier" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select VIP Tier</option>
                                <option value="silver">Silver VIP</option>
                                <option value="gold">Gold VIP</option>
                                <option value="platinum">Platinum VIP</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200">
                        <button type="button" onclick="closeAddVipModal()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                            Promote to VIP
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- VIP Amenities Modal -->
        <div id="vip-amenities-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800">Send VIP Amenities</h3>
                        <button onclick="closeVipAmenitiesModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                <form id="vip-amenities-form" class="p-6 space-y-6">
                    <div>
                        <label for="vip_guest" class="block text-sm font-medium text-gray-700 mb-2">Select VIP Guest *</label>
                        <select id="vip_guest" name="vip_guest" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select VIP Guest</option>
                            <?php foreach (getVipGuests() as $vip): ?>
                            <option value="<?php echo $vip['id']; ?>"><?php echo htmlspecialchars($vip['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="amenity_type" class="block text-sm font-medium text-gray-700 mb-2">Amenity Type *</label>
                        <select id="amenity_type" name="amenity_type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Amenity</option>
                            <option value="welcome_basket">Welcome Basket</option>
                            <option value="champagne">Champagne Service</option>
                            <option value="flowers">Fresh Flowers</option>
                            <option value="chocolates">Premium Chocolates</option>
                            <option value="spa_treatment">Spa Treatment</option>
                            <option value="airport_transfer">Airport Transfer</option>
                        </select>
                    </div>
                    <div>
                        <label for="amenity_notes" class="block text-sm font-medium text-gray-700 mb-2">Special Instructions</label>
                        <textarea id="amenity_notes" name="amenity_notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200">
                        <button type="button" onclick="closeVipAmenitiesModal()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-medium hover:bg-green-700">
                            Send Amenities
                        </button>
                    </div>
                </form>
            </div>
        </div>

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
        
        // VIP Management JavaScript Functions
        
        function openAddVipModal() {
            document.getElementById('add-vip-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closeAddVipModal() {
            document.getElementById('add-vip-modal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            document.getElementById('add-vip-form').reset();
        }
        
        function openVipAmenitiesModal() {
            document.getElementById('vip-amenities-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closeVipAmenitiesModal() {
            document.getElementById('vip-amenities-modal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            document.getElementById('vip-amenities-form').reset();
        }
        
        function viewVipGuest(guestId) {
            window.open(`../../modules/guests/profiles.php?id=${guestId}`, '_blank');
        }
        
        function manageVipServices(guestId, reservationId) {
            alert(`Managing VIP services for Guest ID: ${guestId}, Reservation ID: ${reservationId}`);
            // TODO: Implement VIP services management
        }
        
        function createVipReservation(guestId) {
            window.open(`../../modules/front-desk/new-reservation.php?guest_id=${guestId}`, '_blank');
        }
        
        // Form submission handlers
        document.getElementById('add-vip-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            // Validate required fields
            if (!data.guest_id || !data.loyalty_tier) {
                alert('Please select a guest and VIP tier');
                return;
            }
            
            fetch('../../api/promote-to-vip.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Guest promoted to VIP successfully!');
                    closeAddVipModal();
                    location.reload();
                } else {
                    alert('Error: ' + (result.message || 'Failed to promote guest to VIP'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error promoting guest to VIP');
            });
        });
        
        document.getElementById('vip-amenities-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            fetch('../../api/send-vip-amenities.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('VIP amenities sent successfully!');
                    closeVipAmenitiesModal();
                } else {
                    alert('Error: ' + (result.message || 'Failed to send amenities'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error sending VIP amenities');
            });
        });
        </script>
    </body>
</html>
