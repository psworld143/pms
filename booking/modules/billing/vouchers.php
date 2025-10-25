<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Voucher System
 * Hotel PMS Training System for Students
 */

session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Load dynamic data
$voucherMetrics = getVoucherMetrics();
$vouchers = getVouchers('', null);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

// Set page title
$page_title = 'Voucher System';

// Include header
include '../../includes/header-unified.php';
// Include sidebar
include '../../includes/sidebar-unified.php';
?>

        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Voucher System</h2>
                <div class="flex items-center space-x-4">
                    <button onclick="scrollToCreateForm()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-plus mr-2"></i>Create Voucher
                    </button>
                    <button onclick="scrollToValidationForm()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-check mr-2"></i>Validate Voucher
                    </button>
                </div>
            </div>

            <!-- Voucher Statistics (Dynamic) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-ticket-alt text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Vouchers</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format($voucherMetrics['total_vouchers']); ?></p>
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
                            <p class="text-sm font-medium text-gray-500">Used</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format($voucherMetrics['used_vouchers']); ?></p>
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
                            <p class="text-sm font-medium text-gray-500">Active</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format($voucherMetrics['active_vouchers']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-times-circle text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Expired</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format($voucherMetrics['expired_vouchers']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Voucher Types -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Room Vouchers -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Room Vouchers</h3>
                        <i class="fas fa-bed text-blue-600 text-xl"></i>
                    </div>
                    <div class="space-y-3">
                        <?php
                        $roomVouchers = array_filter($vouchers, function($v) {
                            return in_array($v['voucher_type'], ['free_night', 'upgrade']);
                        });
                        $freeNightCount = count(array_filter($roomVouchers, function($v) { return $v['voucher_type'] === 'free_night'; }));
                        $upgradeCount = count(array_filter($roomVouchers, function($v) { return $v['voucher_type'] === 'upgrade'; }));
                        ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Free Night Voucher</p>
                                <p class="text-sm text-gray-500">1 free night stay</p>
                            </div>
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full"><?= $freeNightCount ?> Active</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Room Upgrade</p>
                                <p class="text-sm text-gray-500">Upgrade to next room type</p>
                            </div>
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full"><?= $upgradeCount ?> Active</span>
                        </div>
                    </div>
                </div>

                <!-- Service Vouchers -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Service Vouchers</h3>
                        <i class="fas fa-concierge-bell text-green-600 text-xl"></i>
                    </div>
                    <div class="space-y-3">
                        <?php
                        $serviceVouchers = array_filter($vouchers, function($v) {
                            return $v['voucher_type'] === 'fixed' && strpos(strtolower($v['description']), 'spa') !== false;
                        });
                        $diningVouchers = array_filter($vouchers, function($v) {
                            return $v['voucher_type'] === 'fixed' && strpos(strtolower($v['description']), 'dining') !== false;
                        });
                        $spaCount = count($serviceVouchers);
                        $diningCount = count($diningVouchers);
                        ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Spa Treatment</p>
                                <p class="text-sm text-gray-500">Complimentary spa service</p>
                            </div>
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full"><?= $spaCount ?> Active</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Dining Credit</p>
                                <p class="text-sm text-gray-500">Restaurant credit</p>
                            </div>
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full"><?= $diningCount ?> Active</span>
                        </div>
                    </div>
                </div>

                <!-- Experience Vouchers -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Experience Vouchers</h3>
                        <i class="fas fa-gift text-purple-600 text-xl"></i>
                    </div>
                    <div class="space-y-3">
                        <?php
                        $experienceVouchers = array_filter($vouchers, function($v) {
                            return $v['voucher_type'] === 'fixed' && (strpos(strtolower($v['description']), 'tour') !== false || strpos(strtolower($v['description']), 'transfer') !== false);
                        });
                        $tourVouchers = array_filter($experienceVouchers, function($v) {
                            return strpos(strtolower($v['description']), 'tour') !== false;
                        });
                        $transferVouchers = array_filter($experienceVouchers, function($v) {
                            return strpos(strtolower($v['description']), 'transfer') !== false;
                        });
                        $tourCount = count($tourVouchers);
                        $transferCount = count($transferVouchers);
                        ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">City Tour</p>
                                <p class="text-sm text-gray-500">Guided city sightseeing</p>
                            </div>
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full"><?= $tourCount ?> Active</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Airport Transfer</p>
                                <p class="text-sm text-gray-500">Complimentary airport pickup</p>
                            </div>
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full"><?= $transferCount ?> Active</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Create Voucher Form -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Create New Voucher</h3>
                <form id="create-voucher-form" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Voucher Name</label>
                            <input type="text" name="voucher_name" id="voucher_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter voucher name" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Voucher Type</label>
                            <select name="voucher_type" id="voucher_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Select Type</option>
                                <option value="percentage">Percentage Discount</option>
                                <option value="fixed">Fixed Amount</option>
                                <option value="free_night">Free Night</option>
                                <option value="upgrade">Room Upgrade</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Value</label>
                            <input type="number" name="voucher_value" id="voucher_value" step="0.01" min="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter voucher value" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Usage Limit</label>
                            <input type="number" name="usage_limit" id="usage_limit" min="1" value="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Number of uses allowed">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Valid From</label>
                            <input type="date" name="valid_from" id="valid_from" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Valid Until</label>
                            <input type="date" name="valid_until" id="valid_until" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" id="description" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3" placeholder="Enter voucher description and terms"></textarea>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="generate_codes" id="generate_codes" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" checked>
                        <label class="ml-2 block text-sm text-gray-900">
                            Generate unique voucher codes
                        </label>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" onclick="resetVoucherForm()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                            Create Voucher
                        </button>
                    </div>
                </form>
            </div>

            <!-- Voucher Validation -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Validate Voucher</h3>
                <form id="validate-voucher-form" class="flex space-x-4">
                    <div class="flex-1">
                        <input type="text" name="voucher_code" id="voucher_code" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter voucher code" required>
                    </div>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-search mr-2"></i>Validate
                    </button>
                </form>
                <div id="validation-result" class="mt-4 hidden">
                    <!-- Validation result will be displayed here -->
                </div>
            </div>

            <!-- Vouchers Table (Dynamic) -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">All Vouchers</h3>
                </div>
                <div class="overflow-x-auto">
                    <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); if (!empty($vouchers)): ?>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Voucher Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Used</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valid Period</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); foreach ($vouchers as $v): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($v['voucher_code']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars(ucfirst($v['voucher_type'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($v['voucher_value']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format($v['used_count']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($v['valid_from']); ?> to <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($v['valid_until']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); $cls = $v['status'] === 'active' ? 'bg-green-100 text-green-800' : ($v['status'] === 'used' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $cls; ?>"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars(ucfirst($v['status'])); ?></span>
                                    </td>
                                </tr>
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endforeach; ?>
                            </tbody>
                        </table>
                    <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); else: ?>
                        <div class="p-6 text-center text-gray-500">No vouchers found.</div>
                    <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endif; ?>
                </div>
            </div>
        </main>

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

            // Set default dates
            document.addEventListener('DOMContentLoaded', function() {
                const today = new Date().toISOString().split('T')[0];
                const nextMonth = new Date();
                nextMonth.setMonth(nextMonth.getMonth() + 1);
                const nextMonthStr = nextMonth.toISOString().split('T')[0];
                
                document.getElementById('valid_from').value = today;
                document.getElementById('valid_until').value = nextMonthStr;
            });

            // Reset voucher form
            function resetVoucherForm() {
                document.getElementById('create-voucher-form').reset();
                const today = new Date().toISOString().split('T')[0];
                const nextMonth = new Date();
                nextMonth.setMonth(nextMonth.getMonth() + 1);
                const nextMonthStr = nextMonth.toISOString().split('T')[0];
                
                document.getElementById('valid_from').value = today;
                document.getElementById('valid_until').value = nextMonthStr;
                document.getElementById('usage_limit').value = 1;
                document.getElementById('generate_codes').checked = true;
            }

            // Scroll to create form
            function scrollToCreateForm() {
                document.getElementById('create-voucher-form').scrollIntoView({ behavior: 'smooth' });
                document.getElementById('voucher_name').focus();
            }

            // Scroll to validation form
            function scrollToValidationForm() {
                document.getElementById('validate-voucher-form').scrollIntoView({ behavior: 'smooth' });
                document.getElementById('voucher_code').focus();
            }

            // Create voucher form submission
            document.getElementById('create-voucher-form').addEventListener('submit', async function(event) {
                event.preventDefault();

                const form = event.currentTarget;
                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating...';

                const formData = new FormData(form);
                const payload = Object.fromEntries(formData.entries());

                // Validate required fields
                if (!payload.voucher_name || !payload.voucher_type || !payload.voucher_value || !payload.valid_from || !payload.valid_until) {
                    alert('Please fill in all required fields.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    return;
                }

                // Validate dates
                if (new Date(payload.valid_from) >= new Date(payload.valid_until)) {
                    alert('Valid Until date must be after Valid From date.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    return;
                }

                // Validate voucher value
                if (parseFloat(payload.voucher_value) <= 0) {
                    alert('Please enter a valid voucher value greater than 0.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    return;
                }

                try {
                    const response = await fetch('../../api/create-voucher.php', {
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
                        alert('Voucher created successfully!');
                        resetVoucherForm();
                        location.reload();
                    } else {
                        alert('Error: ' + (result.message || 'Failed to create voucher.'));
                    }
                } catch (error) {
                    console.error('Error creating voucher:', error);
                    alert('An unexpected error occurred while creating the voucher.');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });

            // Validate voucher form submission
            document.getElementById('validate-voucher-form').addEventListener('submit', async function(event) {
                event.preventDefault();

                const form = event.currentTarget;
                const submitBtn = form.querySelector('button[type="submit"]');
                const voucherCode = document.getElementById('voucher_code').value.trim();
                const resultDiv = document.getElementById('validation-result');

                if (!voucherCode) {
                    alert('Please enter a voucher code.');
                    return;
                }

                submitBtn.disabled = true;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Validating...';

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
                            <div class="bg-green-50 border border-green-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-check-circle text-green-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-green-800">Valid Voucher</h3>
                                        <div class="mt-2 text-sm text-green-700">
                                            <p><strong>Code:</strong> ${voucher.voucher_code}</p>
                                            <p><strong>Type:</strong> ${voucher.voucher_type}</p>
                                            <p><strong>Value:</strong> ${voucher.voucher_value}</p>
                                            <p><strong>Status:</strong> ${voucher.status}</p>
                                            <p><strong>Valid Until:</strong> ${voucher.valid_until}</p>
                                            <p><strong>Usage Limit:</strong> ${voucher.usage_limit}</p>
                                            <p><strong>Used Count:</strong> ${voucher.used_count || 0}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        resultDiv.innerHTML = `
                            <div class="bg-red-50 border border-red-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-times-circle text-red-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800">Invalid Voucher</h3>
                                        <div class="mt-2 text-sm text-red-700">
                                            <p>${result.message}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                    resultDiv.classList.remove('hidden');
                } catch (error) {
                    console.error('Error validating voucher:', error);
                    alert('An unexpected error occurred while validating the voucher.');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });
        </script>
    </body>
</html>
