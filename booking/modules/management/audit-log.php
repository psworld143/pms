<?php
/**
 * Audit Log
 * Hotel PMS Training System for Students
 */

require_once dirname(__DIR__, 3) . '/vps_session_fix.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/booking-paths.php';
require_once __DIR__ . '/../../includes/audit-helpers.php';

booking_initialize_paths();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    header('Location: ' . booking_base() . 'login.php');
    exit();
}

$page_title = 'Audit Log';

$limit = 25;
$initialPage = 1;
$initialLogs = getAuditLogs([
    'limit' => $limit,
    'offset' => 0,
]);
$totalLogs = countAuditLogs();
$totalPages = max(1, (int)ceil($totalLogs / $limit));
$summary = getAuditLogSummary();
$actions = getAuditLogActions();

$asset_version = time();
$bootstrap = [
    'logs' => $initialLogs,
    'pagination' => [
        'page' => $initialPage,
        'limit' => $limit,
        'total' => $totalLogs,
        'total_pages' => $totalPages,
    ],
    'summary' => $summary,
    'actions' => $actions,
    'filters' => [
        'action' => '',
        'date_from' => null,
        'date_to' => null,
        'search' => '',
    ],
];

$additional_js = '<script>window.auditLogBootstrap = ' . json_encode($bootstrap, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ';</script>' . "\n";
$additional_js .= '<script src="' . booking_url('assets/js/main.js?v=' . $asset_version) . '"></script>' . "\n";
$additional_js .= '<script src="' . booking_url('assets/js/audit-log.js?v=' . $asset_version) . '"></script>';

include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';
?>

        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Audit Log</h2>
                    <p class="text-sm text-gray-500">Track system activities, security events, and user actions.</p>
                </div>
                <div class="w-full lg:w-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                        <div class="flex flex-col">
                            <label for="audit-action-filter" class="text-xs text-gray-500 uppercase tracking-wide mb-1">Action</label>
                            <select id="audit-action-filter" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="">All Actions</option>
                                <?php foreach ($actions as $actionOption): ?>
                                    <option value="<?php echo htmlspecialchars($actionOption, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $actionOption)), ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex flex-col">
                            <label for="audit-date-from" class="text-xs text-gray-500 uppercase tracking-wide mb-1">Date From</label>
                            <input type="date" id="audit-date-from" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-primary" />
                        </div>
                        <div class="flex flex-col">
                            <label for="audit-date-to" class="text-xs text-gray-500 uppercase tracking-wide mb-1">Date To</label>
                            <input type="date" id="audit-date-to" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-primary" />
                        </div>
                        <div class="flex flex-col">
                            <label for="audit-search" class="text-xs text-gray-500 uppercase tracking-wide mb-1">Search</label>
                            <input type="text" id="audit-search" placeholder="Search actions or users" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-primary" />
                        </div>
                    </div>
                    <div class="flex justify-end mt-3">
                        <button id="audit-export" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
                            <i class="fas fa-download"></i>
                            <span>Export CSV</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-blue-500/10 text-blue-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-list text-lg"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Log Entries</p>
                            <p id="audit-total-entries" class="text-2xl font-semibold text-gray-900"><?php echo number_format($summary['total_entries'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-green-500/10 text-green-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-calendar-day text-lg"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Today's Activities</p>
                            <p id="audit-today-entries" class="text-2xl font-semibold text-gray-900"><?php echo number_format($summary['today_entries'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-amber-500/10 text-amber-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-lg"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Security Events</p>
                            <p id="audit-security-events" class="text-2xl font-semibold text-gray-900"><?php echo number_format($summary['security_events'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-purple-500/10 text-purple-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-users text-lg"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Active Users (24h)</p>
                            <p id="audit-active-users" class="text-2xl font-semibold text-gray-900"><?php echo number_format($summary['active_users'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <h3 class="text-lg font-semibold text-gray-800">System Activity Log</h3>
                    <p id="audit-pagination-summary" class="text-sm text-gray-500">Showing <?php echo count($initialLogs); ?> of <?php echo number_format($totalLogs); ?> entries</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody id="audit-table-body" class="bg-white divide-y divide-gray-200">
                            <?php if (empty($initialLogs)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-6 text-center text-sm text-gray-500">No audit logs recorded yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($initialLogs as $log): ?>
                                    <?php
                                        $actionText = (string)($log['action'] ?? '');
                                        $actionLower = strtolower($actionText);
                                        $isAlert = str_contains($actionLower, 'fail') || str_contains($actionLower, 'error') || str_contains($actionLower, 'denied') || str_contains($actionLower, 'warning');
                                        $statusLabel = $isAlert ? 'Attention' : 'Success';
                                        $statusClass = $isAlert ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700';
                                        $name = $log['user_name'] ?? 'System';
                                        $initials = strtoupper(mb_substr($name, 0, 1, 'UTF-8'));
                                        $roleLabel = $log['user_role'] ? ucwords(str_replace('_', ' ', $log['user_role'])) : '—';
                                    ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars(date('M j, Y g:i A', strtotime($log['created_at'] ?? 'now')), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-9 w-9">
                                                    <div class="h-9 w-9 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-semibold">
                                                        <?php echo htmlspecialchars($initials, ENT_QUOTES, 'UTF-8'); ?>
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <div class="text-xs text-gray-500"><?php echo htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $actionText)), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="px-6 py-4 text-sm text-gray-600 max-w-xs">
                                            <span class="line-clamp-2" title="<?php echo htmlspecialchars($log['details'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($log['details'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($log['ip_address'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>"><?php echo $statusLabel; ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <span>Rows per page</span>
                        <select id="audit-limit-select" class="border border-gray-300 rounded-md text-sm px-2 py-1 focus:outline-none focus:ring-2 focus:ring-primary">
                            <?php foreach ([10, 25, 50, 100] as $optionLimit): ?>
                                <option value="<?php echo $optionLimit; ?>" <?php echo $optionLimit === $limit ? 'selected' : ''; ?>><?php echo $optionLimit; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <button id="audit-prev-page" class="px-3 py-2 border border-gray-300 rounded-md text-sm text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span class="text-sm text-gray-600">Page <span id="audit-current-page"><?php echo $initialPage; ?></span> of <span id="audit-total-pages"><?php echo $totalPages; ?></span></span>
                        <button id="audit-next-page" class="px-3 py-2 border border-gray-300 rounded-md text-sm text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </main>

        <?php include '../../includes/footer.php'; ?>
