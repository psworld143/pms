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
if (empty($recentPayments)) {
    // Fallback to billing-derived synthetic payments for display only
    $recentPayments = getSyntheticPaymentsFromBilling(10);
}
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
                    <button onclick="openRefundModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-refresh mr-2"></i>Refund Payment
                    </button>
                    <button onclick="exportPayments()" class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-download mr-2"></i>Export Payments
                    </button>
                </div>
            </div>

            <!-- Payment Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div id="card-total-collected" role="button" tabindex="0" class="bg-white rounded-lg shadow p-6 cursor-pointer hover:shadow-md transition">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-credit-card text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Collected</p>
                            <p id="kpi-total-collected" class="text-2xl font-semibold text-gray-900"><?= formatCurrency($paymentMetrics['total_amount'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>

                <div id="card-transactions" role="button" tabindex="0" class="bg-white rounded-lg shadow p-6 cursor-pointer hover:shadow-md transition">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-list-ol text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Transactions</p>
                            <p id="kpi-transactions" class="text-2xl font-semibold text-gray-900"><?= number_format((int)($paymentMetrics['transaction_count'] ?? 0)); ?></p>
                        </div>
                    </div>
                </div>

                <div id="card-today-revenue" role="button" tabindex="0" class="bg-white rounded-lg shadow p-6 cursor-pointer hover:shadow-md transition">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-sun text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Today's Revenue</p>
                            <p id="kpi-today-amount" class="text-2xl font-semibold text-gray-900"><?= formatCurrency($paymentMetrics['today_amount'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>

                <div id="card-today-transactions" role="button" tabindex="0" class="bg-white rounded-lg shadow p-6 cursor-pointer hover:shadow-md transition">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Today's Transactions</p>
                            <p id="kpi-today-count" class="text-2xl font-semibold text-gray-900"><?= number_format((int)($paymentMetrics['today_count'] ?? 0)); ?></p>
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

            <!-- Process Payment Form removed by request -->

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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <button type="button" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded" onclick="openRefundModal('<?= htmlspecialchars($payment['payment_number']); ?>','<?= htmlspecialchars($payment['amount']); ?>')">Refund</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div id="recent-payments-empty" class="p-6 text-center text-gray-500">No payments recorded yet.</div>
                        <div class="hidden overflow-x-auto" id="recent-payments-table">
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
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="recent-payments-body" class="bg-white divide-y divide-gray-200">
                                    <tr><td class="px-6 py-4 text-sm text-gray-500" colspan="7">Loading payments…</td></tr>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Refund Modal -->
            <div id="refund-modal" class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center z-50">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Refund Payment</h3>
                    <form id="refund-form" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Number</label>
                            <input id="refund-payment-number" name="payment_number" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="PAY-YYYYMMDD-00001" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                            <input id="refund-amount" name="amount" type="number" step="0.01" min="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="0.00" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Reason (optional)</label>
                            <input id="refund-reason" name="reason" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Reason for refund" />
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" class="px-4 py-2 border border-gray-300 rounded-md" onclick="closeRefundModal()">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md">Confirm Refund</button>
                        </div>
                    </form>
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

            // Payment actions and helpers

            function openRefundModal(paymentNumber, amount) {
                const modal = document.getElementById('refund-modal');
                document.getElementById('refund-payment-number').value = paymentNumber || '';
                document.getElementById('refund-amount').value = amount || '';
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeRefundModal() {
                const modal = document.getElementById('refund-modal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.getElementById('refund-form').reset();
            }

            document.getElementById('refund-form').addEventListener('submit', async function(e){
                e.preventDefault();
                const payment_number = document.getElementById('refund-payment-number').value.trim();
                const amount = parseFloat(document.getElementById('refund-amount').value);
                const reason = document.getElementById('refund-reason').value.trim();
                if (!payment_number || !isFinite(amount) || amount <= 0) { alert('Enter a valid payment number and amount'); return; }
                try {
                    const res = await fetch('../../api/refund-payment.php', {
                        method: 'POST', headers: { 'Content-Type': 'application/json', 'X-API-Key': 'pms_users_api_2024' }, credentials: 'include',
                        body: JSON.stringify({ payment_number, amount, reason })
                    });
                    const json = await res.json();
                    if (!json.success) throw new Error(json.message||'Refund failed');
                    closeRefundModal();
                    alert('Refund created: ' + (json.refund_number||''));
                    window.location.reload();
                } catch (err) { alert('Error: ' + err.message); }
            });

            function exportPayments() {
                const url = '../../api/export-payments.php';
                window.open(url, '_blank');
            }

            // Load with filters and KPI interactions
            async function loadPaymentsWithFilter(params) {
                try {
                    const qs = new URLSearchParams(params||{}).toString();
                    const res = await fetch('../../api/get-payments.php' + (qs?('?' + qs):''), { credentials: 'include' });
                    const json = await res.json();
                    if (!json || !json.success) return;
                    const list = json.payments || [];
                    const empty = document.getElementById('recent-payments-empty');
                    const table = document.getElementById('recent-payments-table');
                    const body = document.getElementById('recent-payments-body');
                    if (body) { body.innerHTML = '<tr><td class="px-6 py-4 text-sm text-gray-500" colspan="7">Loading…</td></tr>'; }
                    if (empty) empty.classList.add('hidden');
                    if (table) table.classList.remove('hidden');
                    if (body) {
                        body.innerHTML = list.slice(0, 10).map(p => {
                            const method = String(p.payment_method||'').replace('_',' ');
                            const paidOn = p.payment_date ? new Date(p.payment_date).toLocaleString() : '';
                            const amount = Number(p.amount||0).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
                            const guest = (p.guest_name||'');
                            const billNo = p.bill_number || '';
                            const status = (p.bill_status||'').toString();
                            const statusClass = status==='paid' ? 'bg-green-100 text-green-700' : (status==='overdue' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700');
                            return `
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${p.payment_number||''}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${guest}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${billNo}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱${amount}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${method}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${paidOn}</td>
                                    <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">${status?status.charAt(0).toUpperCase()+status.slice(1):''}</span></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm"><button type="button" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded" onclick="openRefundModal('${p.payment_number||''}','${p.amount||''}')">Refund</button></td>
                                </tr>`;
                        }).join('');
                    }
                    const container = document.getElementById('recent-payments-table');
                    if (container && container.scrollIntoView) { container.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
                } catch (e) { /* ignore */ }
            }

            // Client fallback loader to ensure payments appear even if server-side list is empty
            (async function ensureRecentPayments() {
                try {
                    const res = await fetch('../../api/get-payments.php', { credentials: 'include' });
                    const json = await res.json();
                    if (!json || !json.success) return;

                    // KPI fallback update when server-side metrics were zero
                    if (json.totals) {
                        const fmt = (n) => (Number(n||0)).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
                        const peso = (n) => '₱' + fmt(n);
                        const tAmt = document.getElementById('kpi-total-collected');
                        const tCnt = document.getElementById('kpi-transactions');
                        const dAmt = document.getElementById('kpi-today-amount');
                        const dCnt = document.getElementById('kpi-today-count');
                        if (tAmt && Number((tAmt.textContent||'0').replace(/[^0-9.]/g,'')) === 0 && json.totals.total_amount > 0) tAmt.textContent = peso(json.totals.total_amount);
                        if (tCnt && Number((tCnt.textContent||'0').replace(/[^0-9]/g,'')) === 0 && json.totals.count > 0) tCnt.textContent = json.totals.count.toLocaleString();
                        // today-specific values are not provided; leave as-is unless backend updates later
                    }

                    const list = json.payments || [];
                    if (list.length === 0) return;
                    const empty = document.getElementById('recent-payments-empty');
                    const table = document.getElementById('recent-payments-table');
                    const body = document.getElementById('recent-payments-body');
                    if (empty) empty.classList.add('hidden');
                    if (table) table.classList.remove('hidden');
                    if (body) {
                        body.innerHTML = list.slice(0, 10).map(p => {
                            const method = String(p.payment_method||'').replace('_',' ');
                            const paidOn = p.payment_date ? new Date(p.payment_date).toLocaleString() : '';
                            const amount = Number(p.amount||0).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
                            const guest = (p.guest_name||'');
                            const billNo = p.bill_number || '';
                            const status = (p.bill_status||'').toString();
                            const statusClass = status==='paid' ? 'bg-green-100 text-green-700' : (status==='overdue' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700');
                            return `
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${p.payment_number||''}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${guest}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${billNo}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱${amount}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${method}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${paidOn}</td>
                                    <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">${status?status.charAt(0).toUpperCase()+status.slice(1):''}</span></td>
                                </tr>`;
                        }).join('');
                    }
                } catch (e) {
                    // silent fallback
                }
            })();

            // KPI card interactions
            function today() { const d=new Date(); return d.toISOString().slice(0,10); }
            ['card-total-collected','card-transactions'].forEach(id => {
                const el = document.getElementById(id); if (!el) return;
                el.addEventListener('click', () => loadPaymentsWithFilter({}));
                el.addEventListener('keypress', (e) => { if (e.key==='Enter') loadPaymentsWithFilter({}); });
            });
            const todayRevenue = document.getElementById('card-today-revenue');
            if (todayRevenue) {
                todayRevenue.addEventListener('click', () => loadPaymentsWithFilter({ date: today() }));
                todayRevenue.addEventListener('keypress', (e)=>{ if (e.key==='Enter') loadPaymentsWithFilter({ date: today() }); });
            }
            const todayTx = document.getElementById('card-today-transactions');
            if (todayTx) {
                todayTx.addEventListener('click', () => loadPaymentsWithFilter({ date: today() }));
                todayTx.addEventListener('keypress', (e)=>{ if (e.key==='Enter') loadPaymentsWithFilter({ date: today() }); });
            }
        </script>
    </body>
</html>
