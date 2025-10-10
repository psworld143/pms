<?php
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
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-plus mr-2"></i>Add Member
                    </button>
                    <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
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
                <!-- Bronze Tier -->
                <div class="bg-gradient-to-br from-orange-400 to-orange-600 rounded-lg shadow p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">Bronze</h3>
                        <i class="fas fa-medal text-orange-200 text-xl"></i>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-orange-100">Members:</span>
                            <span class="font-semibold"><?php echo $tierMap['bronze'] ?? 0; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-orange-100">Points Required:</span>
                            <span class="text-sm">0-999</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-orange-100">Benefits:</span>
                            <span class="text-sm">5% Discount</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-orange-100">Points per $:</span>
                            <span class="text-sm">1 point</span>
                        </div>
                    </div>
                </div>

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
                            <span class="text-sm">1,000-2,999</span>
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
                            <span class="text-sm">3,000+</span>
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
            </div>

            <!-- Rewards Catalog -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Rewards Catalog</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-bed text-blue-600 text-xl"></i>
                            </div>
                            <h4 class="font-semibold text-gray-800">Free Night</h4>
                            <p class="text-sm text-gray-600">5,000 points</p>
                            <button class="mt-2 bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                Redeem
                            </button>
                        </div>
                    </div>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-utensils text-green-600 text-xl"></i>
                            </div>
                            <h4 class="font-semibold text-gray-800">Dining Credit</h4>
                            <p class="text-sm text-gray-600">2,000 points</p>
                            <button class="mt-2 bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                                Redeem
                            </button>
                        </div>
                    </div>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-spa text-purple-600 text-xl"></i>
                            </div>
                            <h4 class="font-semibold text-gray-800">Spa Treatment</h4>
                            <p class="text-sm text-gray-600">3,500 points</p>
                            <button class="mt-2 bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-sm">
                                Redeem
                            </button>
                        </div>
                    </div>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-gift text-yellow-600 text-xl"></i>
                            </div>
                            <h4 class="font-semibold text-gray-800">Welcome Gift</h4>
                            <p class="text-sm text-gray-600">1,000 points</p>
                            <button class="mt-2 bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded text-sm">
                                Redeem
                            </button>
                        </div>
                    </div>
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
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo strtolower($row['tier']) === 'gold' ? 'bg-yellow-100 text-yellow-800' : (strtolower($row['tier']) === 'silver' ? 'bg-gray-100 text-gray-800' : 'bg-orange-100 text-orange-800'); ?>">
                                        <?php echo htmlspecialchars(ucfirst($row['tier'])); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo number_format($row['points']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo number_format($row['total_spent']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars(date('Y-m-d', strtotime($row['last_activity']))); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                                    <button class="text-green-600 hover:text-green-900">Manage</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Include footer -->
        <?php include '../../includes/footer.php'; ?>
    </body>
</html>
