<?php
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
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-plus mr-2"></i>Add VIP Guest
                    </button>
                    <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-gift mr-2"></i>Send VIP Amenities
                    </button>
                </div>
            </div>

            <!-- VIP Statistics -->
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
                            <p class="text-2xl font-semibold text-gray-900">89</p>
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
                            <p class="text-2xl font-semibold text-gray-900">12</p>
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
                            <p class="text-2xl font-semibold text-gray-900">4.9/5</p>
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
                            <p class="text-2xl font-semibold text-gray-900">$45K</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- VIP Tiers -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Platinum VIP -->
                <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-lg shadow p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">Platinum VIP</h3>
                        <i class="fas fa-crown text-yellow-400 text-xl"></i>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-300">Members:</span>
                            <span class="font-semibold">15</span>
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

                <!-- Gold VIP -->
                <div class="bg-gradient-to-br from-yellow-600 to-yellow-700 rounded-lg shadow p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">Gold VIP</h3>
                        <i class="fas fa-medal text-yellow-200 text-xl"></i>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-yellow-100">Members:</span>
                            <span class="font-semibold">32</span>
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

                <!-- Silver VIP -->
                <div class="bg-gradient-to-br from-gray-400 to-gray-500 rounded-lg shadow p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">Silver VIP</h3>
                        <i class="fas fa-award text-gray-200 text-xl"></i>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-100">Members:</span>
                            <span class="font-semibold">42</span>
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

            <!-- VIP Guests Table -->
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
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12">
                                            <div class="h-12 w-12 rounded-full bg-gradient-to-r from-yellow-400 to-yellow-600 flex items-center justify-center">
                                                <span class="text-white font-bold">JD</span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">John Doe</div>
                                            <div class="text-sm text-gray-500">CEO, Tech Corp</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-800 text-white">
                                        Platinum
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Currently Staying
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Presidential Suite</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex items-center">
                                        <i class="fas fa-wine-glass text-purple-500 mr-1"></i>
                                        <span>Champagne Service</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                                    <button class="text-green-600 hover:text-green-900">Service</button>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12">
                                            <div class="h-12 w-12 rounded-full bg-gradient-to-r from-yellow-500 to-yellow-700 flex items-center justify-center">
                                                <span class="text-white font-bold">JS</span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">Jane Smith</div>
                                            <div class="text-sm text-gray-500">Celebrity Guest</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-600 text-white">
                                        Gold
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Arriving Today
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Deluxe Suite</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex items-center">
                                        <i class="fas fa-car text-blue-500 mr-1"></i>
                                        <span>Airport Transfer</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                                    <button class="text-green-600 hover:text-green-900">Prepare</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Include footer -->
        <?php include '../../includes/footer.php'; ?>
    </body>
</html>
