<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Billing Reports
 * Hotel PMS Training System for Students
 */

session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Load dynamic data for cards and sections
$invoiceMetrics = getInvoiceMetrics();
$paymentMetrics = getPaymentMetrics();
$revenueTrend = getRevenueTrend(30); // last 30 days totals by date
$methodDistribution = getPaymentMethodDistribution();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

// Set page title
$page_title = 'Billing Reports';

// Include header
include '../../includes/header-unified.php';
// Include sidebar
include '../../includes/sidebar-unified.php';
?>

        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Billing Reports</h2>
                <div class="flex items-center space-x-4">
                    <button onclick="exportReport()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-download mr-2"></i>Export Report
                    </button>
                    <button onclick="printReport()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-print mr-2"></i>Print Report
                    </button>
                </div>
            </div>

            <!-- Revenue Summary (Dynamic) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-dollar-sign text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Revenue</p>
                            <p class="text-2xl font-semibold text-gray-900">₱<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format($invoiceMetrics['total_revenue'], 2); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-file-invoice text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Invoices Generated</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format($invoiceMetrics['total_invoices']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-percentage text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Collection Rate</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); 
                                $paid = (int)$invoiceMetrics['paid_count'];
                                $total = max(1, (int)$invoiceMetrics['total_invoices']);
                                echo number_format(($paid / $total) * 100, 1); 
                            ?>%</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Outstanding</p>
                            <p class="text-2xl font-semibold text-gray-900">₱<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format($invoiceMetrics['outstanding_amount'], 2); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Filters -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Report Filters</h3>
                <form id="report-filters" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                        <select id="report-type" name="report_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="revenue">Revenue Report</option>
                            <option value="invoice">Invoice Report</option>
                            <option value="payment">Payment Report</option>
                            <option value="outstanding">Outstanding Report</option>
                            <option value="discount">Discount Report</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                        <select id="date-range" name="date_range" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="7">Last 7 Days</option>
                            <option value="30" selected>Last 30 Days</option>
                            <option value="90">Last 3 Months</option>
                            <option value="180">Last 6 Months</option>
                            <option value="365">This Year</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                        <select id="payment-method" name="payment_method" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Methods</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="cash">Cash</option>
                            <option value="digital_wallet">Digital Wallet</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="debit_card">Debit Card</option>
                            <option value="check">Check</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="button" onclick="generateReport()" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-chart-bar mr-2"></i>Generate
                        </button>
                    </div>
                </form>
            </div>

            <!-- Revenue Chart -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Revenue Trend (Dynamic) -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Revenue Trend</h3>
                <div class="h-64 bg-gray-50 rounded-lg p-4 overflow-y-auto">
                    <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); if (!empty($revenueTrend)): ?>
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-gray-500">
                                    <th class="text-left py-1">Date</th>
                                    <th class="text-right py-1">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); foreach ($revenueTrend as $row): ?>
                                <tr class="border-t border-gray-200">
                                    <td class="py-1"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($row['revenue_date']); ?></td>
                                    <td class="py-1 text-right">₱<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format($row['total'], 2); ?></td>
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
                        <div class="h-full flex items-center justify-center text-gray-500">No revenue data for the selected period.</div>
                    <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endif; ?>
                </div>
                </div>

                <!-- Payment Methods (Dynamic) -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Payment Methods Distribution</h3>
                    <div class="space-y-4">
                        <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); 
                            $totalCnt = 0; foreach ($methodDistribution as $m) { $totalCnt += (int)$m['count']; }
                            foreach ($methodDistribution as $m): 
                                $pct = $totalCnt > 0 ? round(((int)$m['count'] / $totalCnt) * 100) : 0;
                                $label = ucwords(str_replace('_',' ', $m['payment_method'] ?? 'Unknown'));
                                $bar = min(100, max(0, $pct));
                        ?>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-blue-500 rounded mr-3"></div>
                                <span class="text-sm text-gray-700"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($label); ?></span>
                            </div>
                            <div class="flex items-center">
                                <span class="text-sm font-medium text-gray-900 mr-2"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $pct; ?>%</span>
                                <div class="w-20 bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full" style="width: <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $bar; ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endforeach; ?>
                        <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); if (empty($methodDistribution)): ?>
                        <div class="text-center text-gray-500">No payment method data.</div>
                        <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Reports (Dynamic from metrics) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Daily Revenue</h3>
                        <i class="fas fa-calendar-day text-blue-600"></i>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-2">₱<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format($paymentMetrics['today_amount'] ?? 0, 2); ?></div>
                    <div class="flex items-center text-sm text-green-600">
                        <i class="fas fa-arrow-up mr-1"></i>
                        <span><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format((float)0, 1); ?>% vs. yesterday</span>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Weekly Revenue</h3>
                        <i class="fas fa-calendar-week text-green-600"></i>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-2">₱<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format($invoiceMetrics['total_revenue'], 2); ?></div>
                    <div class="flex items-center text-sm text-green-600">
                        <i class="fas fa-arrow-up mr-1"></i>
                        <span>Summary based on paid bills</span>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Monthly Revenue</h3>
                        <i class="fas fa-calendar-alt text-purple-600"></i>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-2">₱<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format($invoiceMetrics['total_amount'], 2); ?></div>
                    <div class="flex items-center text-sm text-green-600">
                        <i class="fas fa-arrow-up mr-1"></i>
                        <span>Total billed amount (all statuses)</span>
                    </div>
                </div>
            </div>

            <!-- Detailed Reports Table (Dynamic recent bills) -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Detailed Billing Report</h3>
                </div>
                <div class="overflow-x-auto">
                    <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); $recentBills = getBills('', '', 10); if (!empty($recentBills)): ?>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); foreach ($recentBills as $b): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($b['bill_date']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($b['bill_number']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($b['guest_name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format($b['total_amount'], 2); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php
                                    // Error handling for production
                                    ini_set('display_errors', 0);
                                    ini_set('log_errors', 1);
                                    $paymentMethod = $b['payment_method'] ?? null;
                                    if ($paymentMethod) {
                                        $methodLabels = [
                                            'cash' => 'Cash',
                                            'credit_card' => 'Credit Card',
                                            'debit_card' => 'Debit Card',
                                            'bank_transfer' => 'Bank Transfer',
                                            'check' => 'Check',
                                            'digital_wallet' => 'Digital Wallet'
                                        ];
                                        echo htmlspecialchars($methodLabels[$paymentMethod] ?? ucwords(str_replace('_', ' ', $paymentMethod)));
                                    } else {
                                        echo '—';
                                    }
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); $cls = $b['status']==='paid'?'bg-green-100 text-green-800':($b['status']==='pending'?'bg-yellow-100 text-yellow-800':($b['status']==='overdue'?'bg-red-100 text-red-800':'bg-gray-100 text-gray-800')); ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $cls; ?>"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars(ucfirst($b['status'])); ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="viewBill(<?php echo $b['id']; ?>)" class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                                    <button onclick="exportBill(<?php echo $b['id']; ?>)" class="text-green-600 hover:text-green-900">Export</button>
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
                        <div class="p-6 text-center text-gray-500">No billing data available.</div>
                    <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endif; ?>
                </div>
            </div>
        </main>

        <!-- Include footer -->
        <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); include '../../includes/footer.php'; ?>

<script>
// Export Report Function
function exportReport() {
    const reportType = document.getElementById('report-type').value;
    const dateRange = document.getElementById('date-range').value;
    const paymentMethod = document.getElementById('payment-method').value;
    
    // Build query parameters
    const params = new URLSearchParams();
    params.append('action', 'export');
    params.append('report_type', reportType);
    params.append('date_range', dateRange);
    if (paymentMethod) params.append('payment_method', paymentMethod);
    
    // Create download link
    const url = `../../api/export-billing-report.php?${params.toString()}`;
    window.open(url, '_blank');
}

// Print Report Function
function printReport() {
    window.print();
}

// Generate Report Function
function generateReport() {
    const reportType = document.getElementById('report-type').value;
    const dateRange = document.getElementById('date-range').value;
    const paymentMethod = document.getElementById('payment-method').value;
    
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Generating...';
    button.disabled = true;
    
    // Build query parameters
    const params = new URLSearchParams();
    params.append('report_type', reportType);
    params.append('date_range', dateRange);
    if (paymentMethod) params.append('payment_method', paymentMethod);
    
    // Reload page with filters
    window.location.href = `?${params.toString()}`;
}

// View Bill Function
function viewBill(billId) {
    window.open(`../../api/get-bill-details.php?id=${billId}`, '_blank');
}

// Export Bill Function
function exportBill(billId) {
    window.open(`../../api/generate-bill-pdf.php?id=${billId}`, '_blank');
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Set current date and time
    updateDateTime();
    setInterval(updateDateTime, 1000);
    
    // Load filters from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('report_type')) {
        document.getElementById('report-type').value = urlParams.get('report_type');
    }
    if (urlParams.get('date_range')) {
        document.getElementById('date-range').value = urlParams.get('date_range');
    }
    if (urlParams.get('payment_method')) {
        document.getElementById('payment-method').value = urlParams.get('payment_method');
    }
});

// Update date and time
function updateDateTime() {
    const now = new Date();
    const dateTimeElement = document.getElementById('current-datetime');
    if (dateTimeElement) {
        dateTimeElement.textContent = now.toLocaleString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    }
}
</script>

    </body>
</html>
