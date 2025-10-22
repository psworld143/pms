<?php
/**
 * Walk-in Reservations
 * Hotel PMS Training System for Students
 */

require_once dirname(__DIR__, 3) . '/vps_session_fix.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';
require_once dirname(__DIR__, 2) . '/includes/booking-paths.php';

booking_initialize_paths();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . booking_base() . 'login.php');
    exit();
}

// Load dynamic data
$today = date('Y-m-d');
$recentWalkIns = getReservations('walk_in', '', 10) ?: [];
$todayWalkIns = array_filter($recentWalkIns, function($r) use ($today) { return substr($r['created_at'],0,10) === $today; });
$successfulCheckins = array_filter($recentWalkIns, function($r){ return ($r['status'] ?? '') === 'checked_in'; });
$pendingWalkIns = array_filter($recentWalkIns, function($r){ return ($r['status'] ?? '') === 'pending'; });
$walkInRevenue = 0;
foreach ($recentWalkIns as $r) { $walkInRevenue += (float)($r['total_amount'] ?? 0); }

// Set page title
$page_title = 'Walk-in Reservations';

// Include header
include dirname(__DIR__, 2) . '/includes/header-unified.php';
// Include sidebar
include dirname(__DIR__, 2) . '/includes/sidebar-unified.php';
?>

        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Walk-in Reservations</h2>
            </div>

            <!-- Walk-in Statistics (Dynamic) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-walking text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Today's Walk-ins</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo number_format(count($todayWalkIns)); ?></p>
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
                            <p class="text-sm font-medium text-gray-500">Successful Check-ins</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo number_format(count($successfulCheckins)); ?></p>
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
                            <p class="text-sm font-medium text-gray-500">Pending</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo number_format(count($pendingWalkIns)); ?></p>
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
                            <p class="text-sm font-medium text-gray-500">Revenue</p>
                            <p class="text-2xl font-semibold text-gray-900">₱<?php echo number_format($walkInRevenue, 2); ?></p>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Recent Walk-ins (Dynamic) -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Recent Walk-ins</h3>
                </div>
                <div class="overflow-x-auto">
                    <?php if (!empty($recentWalkIns)): ?>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-out</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($recentWalkIns as $row): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center">
                                                <span class="text-white font-medium"><?php echo strtoupper(substr($row['guest_name'],0,2)); ?></span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['guest_name']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($row['email'] ?? ''); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Room <?php echo htmlspecialchars($row['room_number']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['check_in_date']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['check_out_date']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php $cls = $row['status']==='checked_in'?'bg-green-100 text-green-800':($row['status']==='pending'?'bg-yellow-100 text-yellow-800':'bg-gray-100 text-gray-800'); ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $cls; ?>"><?php echo htmlspecialchars(getStatusLabel($row['status'])); ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="viewWalkIn(<?php echo $row['id']; ?>)" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-eye mr-1"></i>View
                                    </button>
                                    <?php if (($row['status'] ?? '') !== 'checked_in'): ?>
                                    <button onclick="checkInWalkIn(<?php echo $row['id']; ?>)" class="text-green-600 hover:text-green-900">
                                        <i class="fas fa-sign-in-alt mr-1"></i>Check-in
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <div class="p-6 text-center text-gray-500">No recent walk-ins.</div>
                    <?php endif; ?>
                </div>
            </div>
        </main>

        <!-- Include footer -->
        <?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
        <script>
        // View walk-in reservation details
        function viewWalkIn(reservationId) {
            // Open reservation details in a new tab or modal
            window.open(`view-reservation.php?id=${reservationId}`, '_blank');
        }
        
        // Check-in walk-in reservation
        async function checkInWalkIn(reservationId) {
            if (!confirm('Are you sure you want to check in this guest?')) {
                return;
            }
            
            try {
                const response = await fetch('../../api/check-in-reservation.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        reservation_id: reservationId
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Guest checked in successfully!');
                    location.reload(); // Refresh the page to show updated status
                } else {
                    alert('Error checking in guest: ' + (result.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error checking in guest:', error);
                alert('Error checking in guest. Please try again.');
            }
        }
        </script>
    </body>
</html>
