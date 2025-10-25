<?php
session_start();
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);


session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/booking-paths.php';

booking_initialize_paths();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    header('Location: ' . booking_base() . 'login.php');
    exit();
}

// Page meta
$page_title = 'Financial Dashboard';

// Compute quick KPIs in PHP to avoid extra API calls
try {
    // Current month boundaries
    $first_day = date('Y-m-01');
    $next_month = date('Y-m-01', strtotime('+1 month'));

    // Monthly revenue (paid)
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount),0) AS rev FROM bills WHERE status='paid' AND created_at >= ? AND created_at < ?");
    $stmt->execute([$first_day, $next_month]);
    $monthly_revenue = (float)$stmt->fetch()['rev'];

    // Outstanding balance (pending or overdue)
    $stmt = $pdo->query("SELECT COALESCE(SUM(total_amount),0) AS outstanding FROM bills WHERE status IN ('pending','overdue')");
    $outstanding_balance = (float)$stmt->fetch()['outstanding'];

    // Paid transactions count this month
    $stmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM bills WHERE status='paid' AND created_at >= ? AND created_at < ?");
    $stmt->execute([$first_day, $next_month]);
    $paid_tx_count = (int)$stmt->fetch()['cnt'];

    // Unpaid bills count
    $stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM bills WHERE status != 'paid'");
    $unpaid_bills = (int)$stmt->fetch()['cnt'];
} catch (Exception $e) {
    error_log('Financial KPI error: ' . $e->getMessage());
    $monthly_revenue = 0; $outstanding_balance = 0; $paid_tx_count = 0; $unpaid_bills = 0;
}

include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';
?>

        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Financial Dashboard</h2>
                <div class="text-sm text-gray-600">
                    <i class="fas fa-calendar-alt mr-1"></i><?php
session_start(); echo date('F Y'); ?>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-md bg-green-100 text-green-700 flex items-center justify-center mr-3">
                            <i class="fas fa-peso-sign"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Monthly Revenue (Paid)</p>
                            <p class="text-2xl font-bold text-gray-900">₱<?php
session_start(); echo number_format($monthly_revenue, 2); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-md bg-yellow-100 text-yellow-700 flex items-center justify-center mr-3">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Unpaid Bills</p>
                            <p class="text-2xl font-bold text-gray-900"><?php
session_start(); echo number_format($unpaid_bills); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-md bg-blue-100 text-blue-700 flex items-center justify-center mr-3">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Paid Transactions</p>
                            <p class="text-2xl font-bold text-gray-900"><?php
session_start(); echo number_format($paid_tx_count); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-md bg-red-100 text-red-700 flex items-center justify-center mr-3">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Outstanding Balance</p>
                            <p class="text-2xl font-bold text-gray-900">₱<?php
session_start(); echo number_format($outstanding_balance, 2); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Revenue (Last 30 Days)</h3>
                    <div class="h-64 relative">
                        <div id="revLoading" class="absolute inset-0 flex items-center justify-center bg-gray-50 rounded">
                            <i class="fas fa-spinner fa-spin text-gray-400 text-2xl"></i>
                        </div>
                        <canvas id="revenueChart" class="hidden"></canvas>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Collections Snapshot</h3>
                    <ul id="collectionsSummary" class="space-y-2 text-sm text-gray-700">
                        <li>Loading...</li>
                    </ul>
                </div>
            </div>

            <!-- Recent Bills and Payments -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Recent Bills</h3>
                        <a href="../billing/reports.php" class="text-primary">View Reports</a>
                    </div>
                    <div id="recentBills" class="text-sm text-gray-700">Loading...</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Recent Payments</h3>
                    </div>
                    <div id="recentPayments" class="text-sm text-gray-700">Loading...</div>
                </div>
            </div>
        </main>

        <?php
session_start(); include '../../includes/footer.php'; ?>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', () => {
            loadRevenue();
            loadBills();
            loadPayments();
        });

        async function loadRevenue() {
            try {
                const res = await fetch('../../api/get-revenue-data.php');
                const data = await res.json();
                if (!data.success) throw new Error(data.message || 'Failed');
                document.getElementById('revLoading').classList.add('hidden');
                const ctx = document.getElementById('revenueChart');
                ctx.classList.remove('hidden');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.data.daily.map(i => new Date(i.date).toLocaleDateString()),
                        datasets: [{
                            label: 'Revenue',
                            data: data.data.daily.map(i => i.daily_revenue || i.revenue || 0),
                            borderColor: 'rgb(59,130,246)',
                            backgroundColor: 'rgba(59,130,246,0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
                document.getElementById('collectionsSummary').innerHTML = `
                    <li><strong>Monthly Total:</strong> ₱${Number(data.data?.monthly?.[0]?.monthly_revenue||0).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}</li>
                    <li><strong>Transactions:</strong> ${data.data?.monthly?.[0]?.transaction_count||0}</li>
                `;
            } catch (e) {
                document.getElementById('revLoading').innerHTML = '<div class="text-red-500">Error loading chart</div>';
            }
        }

        async function loadBills() {
            try {
                const res = await fetch('../../api/get-bills.php?status=');
                const json = await res.json();
                if (!json.success) throw new Error(json.message || 'Failed');
                const top = (json.bills || []).slice(0, 8);
                if (top.length === 0) { document.getElementById('recentBills').textContent = 'No bills found.'; return; }
                document.getElementById('recentBills').innerHTML = `
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr><th class="px-3 py-2 text-left">Reservation</th><th class="px-3 py-2 text-left">Total</th><th class="px-3 py-2 text-left">Status</th></tr>
                            </thead>
                            <tbody>
                                ${top.map(b => `
                                    <tr class="border-t">
                                        <td class="px-3 py-2">#${b.reservation_id}</td>
                                        <td class="px-3 py-2">₱${Number(b.total_amount||0).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                                        <td class="px-3 py-2"><span class="px-2 py-1 rounded text-xs ${b.status==='paid'?'bg-green-100 text-green-700':(b.status==='overdue'?'bg-yellow-100 text-yellow-700':'bg-red-100 text-red-700')}">${b.status||'pending'}</span></td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>`;
            } catch (e) {
                document.getElementById('recentBills').textContent = 'Error loading bills';
            }
        }

        async function loadPayments() {
            try {
                const res = await fetch('../../api/get-payments.php');
                const json = await res.json();
                if (!json.success) throw new Error(json.message || 'Failed');
                const top = (json.payments || []).slice(0, 8);
                if (top.length === 0) { document.getElementById('recentPayments').textContent = 'No payments found.'; return; }
                document.getElementById('recentPayments').innerHTML = `
                    <ul class="divide-y">
                        ${top.map(p => `
                            <li class="py-2 flex items-center justify-between">
                                <div>
                                    <div class="font-medium">₱${Number(p.amount||0).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2})}</div>
                                    <div class="text-gray-500 text-xs">${(p.payment_method||'').toString().replace('_',' ')}</div>
                                </div>
                                <div class="text-gray-500 text-xs">${new Date(p.payment_date||Date.now()).toLocaleString()}</div>
                            </li>
                        `).join('')}
                    </ul>`;
            } catch (e) {
                document.getElementById('recentPayments').textContent = 'Error loading payments';
            }
        }
        </script>
<?php
session_start(); // end file ?>

