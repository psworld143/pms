<?php
/**
 * Walk-in Reservations
 * Hotel PMS Training System for Students
 */

session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Load dynamic data
$today = date('Y-m-d');
$recentWalkIns = getReservations('walk_in', '', 10) ?: [];
$todayWalkIns = array_filter($recentWalkIns, function($r) use ($today) { return substr($r['created_at'],0,10) === $today; });
$successfulCheckins = array_filter($recentWalkIns, function($r){ return ($r['status'] ?? '') === 'checked_in'; });
$pendingWalkIns = array_filter($recentWalkIns, function($r){ return ($r['status'] ?? '') === 'pending'; });
$walkInRevenue = 0;
foreach ($recentWalkIns as $r) { $walkInRevenue += (float)($r['total_amount'] ?? 0); }

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

// Set page title
$page_title = 'Walk-in Reservations';

// Include header
include '../../includes/header-unified.php';
// Include sidebar
include '../../includes/sidebar-unified.php';
?>

        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Walk-in Reservations</h2>
                <div class="flex items-center space-x-4">
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-plus mr-2"></i>New Walk-in
                    </button>
                </div>
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

            <!-- Walk-in Form (Dynamic options) -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">New Walk-in Reservation</h3>
                <form id="walkin-form" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Guest Name</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter guest name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="tel" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter phone number">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter email address">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Room Type</label>
                            <select name="room_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <?php foreach (getRoomTypes() as $key => $rt): ?>
                                    <option value="<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars(ucfirst($key)); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Check-in Date</label>
                            <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Check-out Date</label>
                            <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Adults</label>
                            <input type="number" name="adults" value="1" min="1" max="6" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Children</label>
                            <input type="number" name="children" value="0" min="0" max="6" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Special Requests</label>
                            <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3" placeholder="Any special requests or notes"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                            Create Walk-in Reservation
                        </button>
                    </div>
                </form>
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
                                    <button class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                                    <?php if (($row['status'] ?? '') !== 'checked_in'): ?>
                                    <button class="text-green-600 hover:text-green-900">Check-in</button>
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
        <?php include '../../includes/footer.php'; ?>
        <script>
        document.getElementById('walkin-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = e.currentTarget;
            const [firstName, lastName] = (form.querySelector('input[placeholder="Enter guest name"]').value || '').split(' ');
            const payload = {
                first_name: firstName || 'Walk',
                last_name: lastName || 'In',
                phone: form.querySelector('input[type="tel"]').value || '',
                email: form.querySelector('input[type="email"]').value || '',
                room_type: form.querySelector('select[name="room_type"]').value,
                check_in_date: form.querySelector('input[type="date"]').value,
                check_out_date: form.querySelectorAll('input[type="date"]')[1].value,
                adults: form.querySelector('input[name="adults"]').value || 1,
                children: form.querySelector('input[name="children"]').value || 0,
                special_requests: form.querySelector('textarea').value || '',
                booking_source: 'walk_in'
            };

            const btn = form.querySelector('button[type="submit"]');
            const original = btn.innerHTML; btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating...';
            try {
                const res = await fetch('../../api/create-reservation.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload)});
                const data = await res.json();
                if (data.success) { alert('Walk-in reservation created'); location.reload(); }
                else { alert(data.message || 'Failed to create reservation'); }
            } catch(err) {
                console.error(err); alert('Network error');
            } finally { btn.disabled = false; btn.innerHTML = original; }
        });
        </script>
    </body>
</html>
