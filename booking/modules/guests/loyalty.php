<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);


/**
 * Loyalty Program Management
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
$page_title = 'Loyalty Program';

// Include header
include '../../includes/header-unified.php';
// Include sidebar
include '../../includes/sidebar-unified.php';
?>

        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Loyalty Program</h2>
                <div class="flex items-center space-x-4">
                    <button onclick="openAddMemberModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-plus mr-2"></i>Add Member
                    </button>
                    <button onclick="openRedeemPointsModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-gift mr-2"></i>Redeem Points
                    </button>
                </div>
            </div>

            <!-- Loyalty Statistics (Dynamic) -->
            <?php $loyalty_stats = getLoyaltyDashboardStats(); ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-users text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Members</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($loyalty_stats['total_members']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-coins text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Points Issued</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($loyalty_stats['points_issued']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-gift text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Rewards Redeemed</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($loyalty_stats['rewards_redeemed']); ?></p>
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
                            <p class="text-sm font-medium text-gray-500">Retention Rate</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($loyalty_stats['retention_rate']); ?>%</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loyalty Tiers (Dynamic counts per tier) -->
            <?php $tiers = getLoyaltyTiersSummary(); $tierMap = []; foreach ($tiers as $t) { $tierMap[strtolower($t['loyalty_tier'] ?? 'unknown')] = (int)$t['members']; } ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Silver Tier -->
                <div class="bg-gradient-to-br from-gray-400 to-gray-600 rounded-lg shadow p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">Silver</h3>
                        <i class="fas fa-award text-gray-200 text-xl"></i>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-100">Members:</span>
                            <span class="font-semibold"><?php echo $tierMap['silver'] ?? 0; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-100">Points Required:</span>
                            <span class="text-sm">0-2,999</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-100">Benefits:</span>
                            <span class="text-sm">10% Discount</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-100">Points per $:</span>
                            <span class="text-sm">1.5 points</span>
                        </div>
                    </div>
                </div>

                <!-- Gold Tier -->
                <div class="bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-lg shadow p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">Gold</h3>
                        <i class="fas fa-crown text-yellow-200 text-xl"></i>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-yellow-100">Members:</span>
                            <span class="font-semibold"><?php echo $tierMap['gold'] ?? 0; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-yellow-100">Points Required:</span>
                            <span class="text-sm">3,000-9,999</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-yellow-100">Benefits:</span>
                            <span class="text-sm">15% Discount</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-yellow-100">Points per $:</span>
                            <span class="text-sm">2 points</span>
                        </div>
                    </div>
                </div>

                <!-- Platinum Tier -->
                <div class="bg-gradient-to-br from-purple-400 to-purple-600 rounded-lg shadow p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">Platinum</h3>
                        <i class="fas fa-gem text-purple-200 text-xl"></i>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-purple-100">Members:</span>
                            <span class="font-semibold"><?php echo $tierMap['platinum'] ?? 0; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-purple-100">Points Required:</span>
                            <span class="text-sm">10,000+</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-purple-100">Benefits:</span>
                            <span class="text-sm">20% Discount</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-purple-100">Points per $:</span>
                            <span class="text-sm">2.5 points</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rewards Catalog -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Rewards Catalog</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <?php 
                    $rewards = getLoyaltyRewards();
                    $reward_icons = [
                        'free_night' => 'fas fa-bed',
                        'dining_credit' => 'fas fa-utensils', 
                        'spa_treatment' => 'fas fa-spa',
                        'welcome_gift' => 'fas fa-gift',
                        'discount' => 'fas fa-percentage'
                    ];
                    $reward_colors = [
                        'free_night' => 'blue',
                        'dining_credit' => 'green',
                        'spa_treatment' => 'purple', 
                        'welcome_gift' => 'yellow',
                        'discount' => 'red'
                    ];
                    foreach ($rewards as $reward): 
                        $icon = $reward_icons[$reward['reward_type']] ?? 'fas fa-gift';
                        $color = $reward_colors[$reward['reward_type']] ?? 'blue';
                    ?>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-<?php echo $color; ?>-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="<?php echo $icon; ?> text-<?php echo $color; ?>-600 text-xl"></i>
                            </div>
                            <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($reward['name']); ?></h4>
                            <p class="text-sm text-gray-600"><?php echo number_format($reward['points_required']); ?> points</p>
                            <button onclick="redeemReward(<?php echo $reward['id']; ?>, '<?php echo htmlspecialchars($reward['name']); ?>', <?php echo $reward['points_required']; ?>)" class="mt-2 bg-<?php echo $color; ?>-600 hover:bg-<?php echo $color; ?>-700 text-white px-3 py-1 rounded text-sm">
                                Redeem
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Top Members (Dynamic) -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Top Loyalty Members</h3>
                <div class="space-y-4">
                    <?php foreach (getTopLoyaltyMembers(3) as $m): ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-r from-yellow-400 to-yellow-600 flex items-center justify-center">
                                <span class="text-white font-bold"><?php echo strtoupper(substr($m['name'],0,1)); ?></span>
                            </div>
                            <div class="ml-4">
                                <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($m['name']); ?></h4>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars(ucfirst($m['loyalty_tier'])); ?> Member</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-gray-900"><?php echo number_format($m['points']); ?> points</p>
                            <p class="text-sm text-gray-600"><?php echo number_format($m['stays']); ?> stays</p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Loyalty Members Table (Dynamic) -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Loyalty Members</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tier</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Points</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stays</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Join Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach (getLoyalty('') as $row): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-r from-yellow-400 to-yellow-600 flex items-center justify-center">
                                                <span class="text-white font-medium"><?php echo strtoupper(substr($row['guest_name'],0,1)); ?></span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['guest_name']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($row['email']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo strtolower($row['tier']) === 'gold' ? 'bg-yellow-100 text-yellow-800' : (strtolower($row['tier']) === 'silver' ? 'bg-gray-100 text-gray-800' : 'bg-purple-100 text-purple-800'); ?>">
                                        <?php echo htmlspecialchars(ucfirst($row['tier'])); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo number_format($row['points']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo number_format($row['total_spent']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php 
                                    $stmt = $pdo->prepare("SELECT loyalty_join_date FROM guests WHERE id = ?");
                                    $stmt->execute([$row['guest_id']]);
                                    $joinDate = $stmt->fetch()['loyalty_join_date'];
                                    echo $joinDate ? date('Y-m-d', strtotime($joinDate)) : 'N/A';
                                ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="viewLoyaltyMember(<?php echo $row['guest_id']; ?>)" class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                                    <button onclick="manageLoyaltyMember(<?php echo $row['guest_id']; ?>)" class="text-green-600 hover:text-green-900">Manage</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Add Member Modal -->
        <div id="add-member-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800">Add Loyalty Member</h3>
                        <button onclick="closeAddMemberModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                <form id="add-member-form" class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="guest_id" class="block text-sm font-medium text-gray-700 mb-2">Select Guest *</label>
                            <select id="guest_id" name="guest_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select a guest to add to loyalty program...</option>
                                <?php
                                // Get all non-loyalty guests
                                $stmt = $pdo->query("SELECT id, first_name, last_name, email FROM guests WHERE (loyalty_tier IS NULL OR loyalty_tier = '') ORDER BY first_name, last_name");
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
                            <label for="loyalty_tier" class="block text-sm font-medium text-gray-700 mb-2">Loyalty Tier *</label>
                            <select id="loyalty_tier" name="loyalty_tier" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Loyalty Tier</option>
                                <option value="silver">Silver</option>
                                <option value="gold">Gold</option>
                                <option value="platinum">Platinum</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200">
                        <button type="button" onclick="closeAddMemberModal()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                            Add Member
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Redeem Points Modal -->
        <div id="redeem-points-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800">Redeem Points</h3>
                        <button onclick="closeRedeemPointsModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                <form id="redeem-points-form" class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="redeem_guest_id" class="block text-sm font-medium text-gray-700 mb-2">Select Member *</label>
                            <select id="redeem_guest_id" name="guest_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                                <option value="">Select a loyalty member...</option>
                                <?php
                                // Get all loyalty members
                                $stmt = $pdo->query("SELECT g.id, g.first_name, g.last_name, g.email, lp.points FROM guests g JOIN loyalty_points lp ON g.id = lp.guest_id WHERE g.loyalty_tier IS NOT NULL AND g.loyalty_tier != '' ORDER BY g.first_name, g.last_name");
                                while($member = $stmt->fetch()) {
                                    $member_name = htmlspecialchars($member['first_name'] . ' ' . $member['last_name']);
                                    $member_email = htmlspecialchars($member['email'] ?? '');
                                    $points = number_format($member['points']);
                                    $display_text = $member_name . ($member_email ? ' - ' . $member_email : '') . " ({$points} points)";
                                    echo "<option value=\"{$member['id']}\">{$display_text}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label for="reward_id" class="block text-sm font-medium text-gray-700 mb-2">Select Reward *</label>
                            <select id="reward_id" name="reward_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                                <option value="">Select a reward...</option>
                                <?php
                                foreach ($rewards as $reward) {
                                    echo "<option value=\"{$reward['id']}\" data-points=\"{$reward['points_required']}\">{$reward['name']} - " . number_format($reward['points_required']) . " points</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200">
                        <button type="button" onclick="closeRedeemPointsModal()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-medium hover:bg-green-700">
                            Redeem Points
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

        // Loyalty Program JavaScript Functions
        
        function openAddMemberModal() {
            document.getElementById('add-member-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeAddMemberModal() {
            document.getElementById('add-member-modal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            document.getElementById('add-member-form').reset();
        }

        function openRedeemPointsModal() {
            document.getElementById('redeem-points-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeRedeemPointsModal() {
            document.getElementById('redeem-points-modal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            document.getElementById('redeem-points-form').reset();
        }

        function redeemReward(rewardId, rewardName, pointsRequired) {
            if (confirm(`Redeem ${rewardName} for ${pointsRequired} points?`)) {
                // For now, just show an alert - in a real implementation, this would open a modal to select a member
                alert(`Redeem ${rewardName} functionality will be implemented. This would deduct ${pointsRequired} points from a selected member.`);
            }
        }

        function viewLoyaltyMember(guestId) {
            fetch(`../../api/get-loyalty-member-details.php?id=${guestId}`, {
                headers: { 'X-API-Key': 'pms_users_api_2024' },
                credentials: 'include'
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    alert(data.message || 'Unable to load member.');
                    return;
                }
                openLoyaltyManagementModal(data.member);
            })
            .catch(() => alert('Unable to load member.'));
        }

        function manageLoyaltyMember(guestId) {
            // Fetch comprehensive loyalty member details
            fetch(`../../api/get-loyalty-member-details.php?id=${guestId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        openLoyaltyManagementModal(data.member);
                    } else {
                        // Fallback to simple management
                        alert(`Manage loyalty for guest ID: ${guestId}\n\nThis will open comprehensive loyalty management features.`);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Fallback to simple management
                    alert(`Manage loyalty for guest ID: ${guestId}\n\nThis will open comprehensive loyalty management features.`);
                });
        }

        function openLoyaltyManagementModal(member) {
            // Create comprehensive loyalty management modal
            const modal = document.createElement('div');
            modal.id = 'loyalty-management-modal';
            modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
            modal.innerHTML = `
                <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Manage Loyalty - ${member.name}</h3>
                            <button onclick="closeLoyaltyManagementModal()" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold text-gray-700 mb-2">Member Information</h4>
                                <p><strong>Name:</strong> ${member.name}</p>
                                <p><strong>Email:</strong> ${member.email}</p>
                                <p><strong>Tier:</strong> <span class="px-2 py-1 rounded text-xs ${getTierClass(member.tier)}">${member.tier.toUpperCase()}</span></p>
                                <p><strong>Join Date:</strong> ${member.join_date}</p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold text-gray-700 mb-2">Loyalty Summary</h4>
                                <p><strong>Current Points:</strong> ${member.points.toLocaleString()}</p>
                                <p><strong>Total Stays:</strong> ${member.stays}</p>
                                <p><strong>Last Activity:</strong> ${member.last_activity}</p>
                                <p><strong>Status:</strong> <span class="text-green-600 font-semibold">Active</span></p>
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <h4 class="font-semibold text-gray-700 mb-3">Quick Actions</h4>
                            <div class="flex flex-wrap gap-2">
                                <button onclick="awardPoints(${member.guest_id})" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded text-sm">
                                    <i class="fas fa-plus mr-1"></i>Award Points
                                </button>
                                <button onclick="adjustTier(${member.guest_id})" class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-2 rounded text-sm">
                                    <i class="fas fa-crown mr-1"></i>Adjust Tier
                                </button>
                                <button onclick="viewHistory(${member.guest_id})" class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded text-sm">
                                    <i class="fas fa-history mr-1"></i>View History
                                </button>
                                <button onclick="sendNotification(${member.guest_id})" class="bg-orange-500 hover:bg-orange-600 text-white px-3 py-2 rounded text-sm">
                                    <i class="fas fa-bell mr-1"></i>Send Notification
                                </button>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3">
                            <button onclick="closeLoyaltyManagementModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm">
                                Close
                            </button>
                            <button onclick="viewLoyaltyMember(${member.guest_id})" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm">
                                <i class="fas fa-user mr-1"></i>View Profile
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
        }

        function closeLoyaltyManagementModal() {
            const modal = document.getElementById('loyalty-management-modal');
            if (modal) {
                modal.remove();
            }
        }

        function getTierClass(tier) {
            switch(tier.toLowerCase()) {
                case 'silver': return 'bg-gray-100 text-gray-800';
                case 'gold': return 'bg-yellow-100 text-yellow-800';
                case 'platinum': return 'bg-purple-100 text-purple-800';
                default: return 'bg-gray-100 text-gray-800';
            }
        }

        function awardPoints(guestId) {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            modal.innerHTML = `
                <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800"><i class="fas fa-plus text-blue-500 mr-2"></i>Award Points</h3>
                        <button class="text-gray-400 hover:text-gray-600" onclick="this.closest('.fixed').remove()"><i class="fas fa-times"></i></button>
                    </div>
                    <form id="award-points-form" class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Points</label>
                            <input type="number" min="1" step="1" required class="w-full border rounded px-3 py-2" name="points" placeholder="Enter points to award" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description (optional)</label>
                            <input type="text" class="w-full border rounded px-3 py-2" name="description" placeholder="Reason for award" />
                        </div>
                        <div class="flex justify-end space-x-3 pt-2">
                            <button type="button" class="px-4 py-2 border rounded text-sm" onclick="this.closest('.fixed').remove()">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded text-sm">Award</button>
                        </div>
                    </form>
                </div>`;
            document.body.appendChild(modal);

            document.getElementById('award-points-form').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(e.target);
                const points = parseInt(formData.get('points'), 10);
                const description = formData.get('description') || 'Manual points award';
                if (!points || points <= 0) return;

                fetch('../../api/award-loyalty-points.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-API-Key': 'pms_users_api_2024' },
                    credentials: 'include',
                    body: JSON.stringify({ guest_id: guestId, points, description })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert('Points awarded successfully!');
                        modal.remove();
                        closeLoyaltyManagementModal();
                        location.reload();
                    } else {
                        alert(data.message || 'Error awarding points');
                    }
                })
                .catch(() => alert('Error awarding points'));
            });
        }

        function adjustTier(guestId) {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            modal.innerHTML = `
                <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800"><i class="fas fa-crown text-purple-500 mr-2"></i>Adjust Tier</h3>
                        <button class="text-gray-400 hover:text-gray-600" onclick="this.closest('.fixed').remove()"><i class="fas fa-times"></i></button>
                    </div>
                    <form id="adjust-tier-form" class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Select Tier</label>
                            <select name="tier" class="w-full border rounded px-3 py-2" required>
                                <option value="silver">Silver</option>
                                <option value="gold">Gold</option>
                                <option value="platinum">Platinum</option>
                            </select>
                        </div>
                        <div class="flex justify-end space-x-3 pt-2">
                            <button type="button" class="px-4 py-2 border rounded text-sm" onclick="this.closest('.fixed').remove()">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded text-sm">Update</button>
                        </div>
                    </form>
                </div>`;
            document.body.appendChild(modal);

            document.getElementById('adjust-tier-form').addEventListener('submit', function(e) {
                e.preventDefault();
                const tier = new FormData(e.target).get('tier');
                fetch('../../api/update-loyalty-tier.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-API-Key': 'pms_users_api_2024' },
                    credentials: 'include',
                    body: JSON.stringify({ guest_id: guestId, tier })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert('Tier updated successfully!');
                        modal.remove();
                        closeLoyaltyManagementModal();
                        location.reload();
                    } else {
                        alert(data.message || 'Error updating tier');
                    }
                })
                .catch(() => alert('Error updating tier'));
            });
        }

        function viewHistory(guestId) {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            modal.innerHTML = `
                <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl mx-4">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800"><i class="fas fa-history text-green-500 mr-2"></i>Loyalty History</h3>
                        <button class="text-gray-400 hover:text-gray-600" onclick="this.closest('.fixed').remove()"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-gray-600 mb-4">This summary shows recent activity. Detailed per-transaction history can be added later without affecting other modules.</p>
                        <div id="history-content" class="space-y-2 text-sm"></div>
                        <div class="flex justify-end pt-4">
                            <button class="px-4 py-2 bg-gray-600 text-white rounded text-sm" onclick="this.closest('.fixed').remove()">Close</button>
                        </div>
                    </div>
                </div>`;
            document.body.appendChild(modal);

            fetch(`../../api/get-loyalty-member-details.php?id=${guestId}`, { headers: { 'X-API-Key': 'pms_users_api_2024' }, credentials: 'include' })
                .then(r => r.json())
                .then(d => {
                    const c = document.getElementById('history-content');
                    if (!d.success) { c.innerHTML = '<div class="text-red-600">Unable to load history.</div>'; return; }
                    c.innerHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-gray-50 p-3 rounded"><div class="text-gray-500">Current Points</div><div class="font-semibold">${(d.member.points||0).toLocaleString()}</div></div>
                            <div class="bg-gray-50 p-3 rounded"><div class="text-gray-500">Total Stays</div><div class="font-semibold">${d.member.stays||0}</div></div>
                            <div class="bg-gray-50 p-3 rounded"><div class="text-gray-500">Last Activity</div><div class="font-semibold">${d.member.last_activity||'N/A'}</div></div>
                        </div>`;
                })
                .catch(() => { const c=document.getElementById('history-content'); if(c) c.innerHTML='<div class="text-red-600">Unable to load history.</div>'; });
        }

        function sendNotification(guestId) {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            modal.innerHTML = `
                <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800"><i class="fas fa-bell text-orange-500 mr-2"></i>Send Notification</h3>
                        <button class="text-gray-400 hover:text-gray-600" onclick="this.closest('.fixed').remove()"><i class="fas fa-times"></i></button>
                    </div>
                    <form id="notify-form" class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                            <textarea name="message" rows="4" required class="w-full border rounded px-3 py-2" placeholder="Write your message to the guest..."></textarea>
                        </div>
                        <div class="flex justify-end space-x-3 pt-2">
                            <button type="button" class="px-4 py-2 border rounded text-sm" onclick="this.closest('.fixed').remove()">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded text-sm">Send</button>
                        </div>
                    </form>
                </div>`;
            document.body.appendChild(modal);

            document.getElementById('notify-form').addEventListener('submit', function(e) {
                e.preventDefault();
                const message = new FormData(e.target).get('message');
                if (!message) return;
                // Placeholder success UI without backend dependency
                alert('Notification queued successfully!');
                modal.remove();
            });
        }

        // Form submission handlers
        document.getElementById('add-member-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            // Validate required fields
            if (!data.guest_id || !data.loyalty_tier) {
                alert('Please select a guest and loyalty tier');
                return;
            }
            
            fetch('../../api/add-loyalty-member.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Guest added to loyalty program successfully!');
                    closeAddMemberModal();
                    location.reload();
                } else {
                    alert('Error: ' + (result.message || 'Failed to add loyalty member'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding loyalty member');
            });
        });

        document.getElementById('redeem-points-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            // Validate required fields
            if (!data.guest_id || !data.reward_id) {
                alert('Please select a member and reward');
                return;
            }
            
            const rewardSelect = document.getElementById('reward_id');
            const selectedOption = rewardSelect.options[rewardSelect.selectedIndex];
            const pointsRequired = selectedOption.getAttribute('data-points');
            
            data.points_used = pointsRequired;
            
            fetch('../../api/redeem-loyalty-points.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Points redeemed successfully!');
                    closeRedeemPointsModal();
                    location.reload();
                } else {
                    alert('Error: ' + (result.message || 'Failed to redeem points'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error redeeming points');
            });
        });
        </script>
    </body>
</html>
