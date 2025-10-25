<?php
/**
 * Payment Processing
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

// Load dynamic data
$paymentMetrics = getPaymentMetrics();
$recentPayments = getPayments('', '', 10);
$pendingBills = getPendingBills();
$paymentMethods = $paymentMetrics['methods'] ?? [];
$paymentMethodOptions = [
    'cash' => 'Cash',
    'credit_card' => 'Credit Card',
    'debit_card' => 'Debit Card',
    'bank_transfer' => 'Bank Transfer',
    'check' => 'Check'
];

$resolvePaymentMethodLabel = static function (?string $methodKey) use ($paymentMethodOptions): string {
    $methodKey = $methodKey ?? '';
    if (isset($paymentMethodOptions[$methodKey])) {
        return $paymentMethodOptions[$methodKey];
    }

    $methodKey = str_replace('_', ' ', $methodKey);
    $methodKey = trim($methodKey);

    return $methodKey !== '' ? ucwords($methodKey) : 'Unknown';
};

// Set page title
$page_title = 'Payment Processing';

// Include header
include '../../includes/header-unified.php';
// Include sidebar
include '../../includes/sidebar-unified.php';
?>

        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Payment Processing</h2>
                <div class="flex items-center space-x-4">
                    <button onclick="openProcessPaymentModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-plus mr-2"></i>Process Payment
                    </button>
                    <button onclick="openRefundModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-refresh mr-2"></i>Refund Payment
                    </button>
                </div>
            </div>

            <!-- Payment Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-credit-card text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Collected</p>
                            <p class="text-2xl font-semibold text-gray-900"><?= formatCurrency($paymentMetrics['total_amount'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-list-ol text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Transactions</p>
                            <p class="text-2xl font-semibold text-gray-900"><?= number_format((int)($paymentMetrics['transaction_count'] ?? 0)); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-sun text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Today's Revenue</p>
                            <p class="text-2xl font-semibold text-gray-900"><?= formatCurrency($paymentMetrics['today_amount'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Today's Transactions</p>
                            <p class="text-2xl font-semibold text-gray-900"><?= number_format((int)($paymentMetrics['today_count'] ?? 0)); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <?php if (!empty($paymentMethods)): ?>
                    <?php foreach ($paymentMethods as $method): ?>
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($resolvePaymentMethodLabel($method['method'] ?? '')); ?></h3>
                                <i class="fas fa-wallet text-blue-600 text-xl"></i>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Transactions:</span>
                                    <span class="font-semibold"><?= number_format((int)($method['count'] ?? 0)); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Total Amount:</span>
                                    <span class="font-semibold"><?= formatCurrency($method['total'] ?? 0); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-1 lg:col-span-3 bg-white rounded-lg shadow p-6 text-center text-gray-500">
                        No payment method data available yet.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Process Payment Form -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Process New Payment</h3>
                <form id="process-payment-form" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Guest/Invoice</label>
                            <select name="bill_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Select Outstanding Bill</option>
                                <?php foreach ($pendingBills as $bill): ?>
                                    <option value="<?= (int)$bill['id']; ?>">
                                        <?= htmlspecialchars($bill['bill_number']); ?> — <?= htmlspecialchars($bill['guest_name']); ?> (<?= formatCurrency($bill['total_amount']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                            <select name="payment_method" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Select Payment Method</option>
                                <?php foreach ($paymentMethodOptions as $value => $label): ?>
                                    <option value="<?= htmlspecialchars($value); ?>"><?= htmlspecialchars($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                            <input type="number" name="amount" step="0.01" min="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter amount" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reference Number</label>
                            <input type="text" name="reference_number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter reference number">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Notes</label>
                        <textarea name="notes" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3" placeholder="Enter payment notes or comments"></textarea>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" onclick="closeProcessPaymentModal()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                            Process Payment
                        </button>
                    </div>
                </form>
            </div>

            <!-- Recent Payments -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Recent Payments</h3>
                </div>
                <div class="overflow-x-auto">
                    <?php if (!empty($recentPayments)): ?>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bill</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paid On</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bill Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($recentPayments as $payment): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($payment['payment_number']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($payment['guest_name']); ?>
                                            <div class="text-xs text-gray-500">Room <?= htmlspecialchars($payment['room_number']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($payment['bill_number']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= formatCurrency($payment['amount']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($resolvePaymentMethodLabel($payment['payment_method'] ?? '')); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars(formatDate($payment['payment_date'], 'M d, Y h:i A')); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= getBillStatusClass($payment['bill_status']); ?>">
                                                <?= htmlspecialchars(ucfirst($payment['bill_status'])); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="p-6 text-center text-gray-500">No payments recorded yet.</div>
                    <?php endif; ?>
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

            // Modal functions
            function openProcessPaymentModal() {
                // Scroll to the form
                document.getElementById('process-payment-form').scrollIntoView({ behavior: 'smooth' });
                // Focus on the first input
                document.querySelector('#process-payment-form input, #process-payment-form select').focus();
            }

            function closeProcessPaymentModal() {
                document.getElementById('process-payment-form').reset();
            }

            function openRefundModal() {
                alert('Refund functionality will be implemented soon. Please contact support for refunds.');
            }

            // Form submission
            document.getElementById('process-payment-form').addEventListener('submit', async function (event) {
                event.preventDefault();

                const form = event.currentTarget;
                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';

                const formData = new FormData(form);
                const payload = Object.fromEntries(formData.entries());

                // Validate required fields
                if (!payload.bill_id || !payload.payment_method || !payload.amount) {
                    alert('Please fill in all required fields.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    return;
                }

                // Validate amount
                if (parseFloat(payload.amount) <= 0) {
                    alert('Please enter a valid amount greater than 0.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    return;
                }

                try {
                    const response = await fetch('../../api/record-payment.php', {
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
                        alert('Payment recorded successfully!');
                        window.location.reload();
                    } else {
                        alert('Error: ' + (result.message || 'Failed to record payment.'));
                    }
                } catch (error) {
                    console.error('Error recording payment:', error);
                    alert('An unexpected error occurred while recording the payment.');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });
        </script>
    </body>
</html>
