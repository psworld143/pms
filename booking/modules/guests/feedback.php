<?php
/**
 * Guest Feedback Intelligence
 * Hotel PMS Training System for Students
 */
require_once dirname(__DIR__, 3) . '/vps_session_fix.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';
require_once dirname(__DIR__, 2) . '/includes/booking-paths.php';
require_once dirname(__DIR__, 2) . '/includes/guest-feedback-helpers.php';

booking_initialize_paths();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['manager', 'front_desk'], true)) {
    header('Location: ' . booking_base() . 'login.php');
    exit();
}

global $pdo;

$page_title = 'Guest Feedback Intelligence';

$defaultFilters = getGuestFeedbackFilterDefaults();
$initialLimit = 10;
$initialPage = 1;
$initialSort = 'newest';

$summary = getGuestFeedbackSummary($pdo);
$distribution = getGuestFeedbackRatingDistribution($pdo);
$categories = getGuestFeedbackCategoryBreakdown($pdo);
$recentFeedback = getRecentGuestFeedback($pdo, 4);
$initialFeedback = getGuestFeedbackList($pdo, $defaultFilters, $initialLimit, 0, $initialSort);
$totalFeedback = countGuestFeedback($pdo, $defaultFilters);
$totalPages = max(1, (int)ceil($totalFeedback / $initialLimit));

$bootstrap = [
    'summary' => $summary,
    'distribution' => $distribution,
    'categories' => $categories,
    'recent' => $recentFeedback,
    'table' => [
        'items' => $initialFeedback,
        'pagination' => [
            'page' => $initialPage,
            'limit' => $initialLimit,
            'total' => $totalFeedback,
            'total_pages' => $totalPages,
        ],
        'filters' => $defaultFilters,
        'sort' => $initialSort,
    ],
];

$asset_version = time();
$additional_js = '<script>window.guestFeedbackBootstrap = ' . json_encode($bootstrap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ';</script>' . "\n";
$additional_js .= '<script src="' . booking_url('assets/js/main.js?v=' . $asset_version) . '"></script>' . "\n";
$additional_js .= '<script src="' . booking_url('assets/js/guest-feedback.js?v=' . $asset_version) . '"></script>';

include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';

$averageRatingDisplay = $summary['average_rating'] !== null ? number_format((float)$summary['average_rating'], 1) . '/5' : '—';
$totalReviewsDisplay = number_format((int)($summary['total_reviews'] ?? 0));
$pendingDisplay = number_format((int)($summary['pending_response'] ?? 0));
$responseRateDisplay = $summary['response_rate'] !== null ? number_format((float)$summary['response_rate'], 1) . '%' : '0.0%';
$satisfactionDisplay = number_format((float)($summary['satisfaction_rate'] ?? 0), 1) . '%';
$complaintsDisplay = number_format((int)($summary['complaints'] ?? 0));
$resolvedDisplay = number_format((int)($summary['resolved'] ?? 0));
?>
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Guest Feedback Intelligence</h2>
                    <p class="text-sm text-gray-500">Monitor sentiment trends, track guest satisfaction, and respond to feedback faster.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button id="feedback-refresh" type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md shadow">
                        <i class="fas fa-sync-alt"></i>
                        <span>Refresh Data</span>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Average Rating</p>
                            <p id="feedback-average-rating" class="mt-2 text-3xl font-semibold text-gray-900"><?php echo $averageRatingDisplay; ?></p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-yellow-500/10 text-yellow-500 flex items-center justify-center">
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Reviews</p>
                            <p id="feedback-total-reviews" class="mt-2 text-3xl font-semibold text-gray-900"><?php echo $totalReviewsDisplay; ?></p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-blue-500/10 text-blue-500 flex items-center justify-center">
                            <i class="fas fa-comment-dots"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Pending Response</p>
                            <p id="feedback-pending-response" class="mt-2 text-3xl font-semibold text-gray-900"><?php echo $pendingDisplay; ?></p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-amber-500/10 text-amber-500 flex items-center justify-center">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Response Rate</p>
                            <p id="feedback-response-rate" class="mt-2 text-3xl font-semibold text-gray-900"><?php echo $responseRateDisplay; ?></p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-green-500/10 text-green-500 flex items-center justify-center">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mt-4">
                <div class="bg-white rounded-lg shadow p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Satisfaction Rate</p>
                            <p id="feedback-satisfaction-rate" class="mt-2 text-2xl font-semibold text-gray-900"><?php echo $satisfactionDisplay; ?></p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-purple-500/10 text-purple-500 flex items-center justify-center">
                            <i class="fas fa-smile-beam"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Open Complaints</p>
                            <p id="feedback-complaints" class="mt-2 text-2xl font-semibold text-gray-900"><?php echo $complaintsDisplay; ?></p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-red-500/10 text-red-500 flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Resolved Items</p>
                            <p id="feedback-resolved" class="mt-2 text-2xl font-semibold text-gray-900"><?php echo $resolvedDisplay; ?></p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800">Rating Distribution</h3>
                        <span class="text-xs uppercase tracking-wide text-gray-400">Last 90 Days</span>
                    </div>
                    <div id="feedback-distribution" class="mt-6 space-y-4">
                        <?php if (!empty($distribution)) : ?>
                            <?php foreach ($distribution as $item) :
                                $percentage = isset($item['percentage']) ? max(0.0, min(100.0, (float)$item['percentage'])) : 0.0;
                                ?>
                                <div class="flex items-center gap-3">
                                    <div class="w-12 text-sm font-semibold text-gray-600"><?php echo (int)$item['rating']; ?>★</div>
                                    <div class="flex-1">
                                        <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="h-2 bg-blue-500 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                                        </div>
                                    </div>
                                    <div class="w-12 text-right text-sm text-gray-500"><?php echo number_format((int)($item['count'] ?? 0)); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <p class="text-sm text-gray-500">No ratings recorded yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800">Category Performance</h3>
                        <span class="text-xs uppercase tracking-wide text-gray-400">Top insights</span>
                    </div>
                    <div class="mt-6">
                        <table class="min-w-full">
                            <thead>
                                <tr class="text-xs text-gray-500 uppercase tracking-wide">
                                    <th class="py-2 text-left">Category</th>
                                    <th class="py-2 text-right">Volume</th>
                                    <th class="py-2 text-right">Share</th>
                                    <th class="py-2 text-right">Avg. Rating</th>
                                </tr>
                            </thead>
                            <tbody id="feedback-categories-body" class="text-sm text-gray-700">
                                <?php if (!empty($categories)) : ?>
                                    <?php foreach ($categories as $category) :
                                        $categoryLabel = ucfirst(str_replace('_', ' ', $category['category'] ?? 'other'));
                                        $share = isset($category['percentage']) ? number_format((float)$category['percentage'], 1) . '%' : '—';
                                        $avg = isset($category['average_rating']) && $category['average_rating'] !== null ? number_format((float)$category['average_rating'], 1) : '—';
                                        ?>
                                        <tr class="border-b last:border-b-0 border-gray-100">
                                            <td class="py-2 font-medium text-gray-800"><?php echo htmlspecialchars($categoryLabel, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="py-2 text-right"><?php echo number_format((int)($category['count'] ?? 0)); ?></td>
                                            <td class="py-2 text-right"><?php echo $share; ?></td>
                                            <td class="py-2 text-right"><?php echo $avg; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="4" class="py-4 text-center text-gray-500">No category insights available yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 mt-8">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">Recent Voice of the Guest</h3>
                    <button id="feedback-view-all" type="button" class="text-sm text-blue-600 hover:text-blue-700" data-action="scroll-table">View all</button>
                </div>
                <div id="feedback-recent-list" class="mt-6 space-y-4">
                    <?php if (!empty($recentFeedback)) : ?>
                        <?php foreach ($recentFeedback as $item) :
                            $rating = $item['rating'] !== null ? (int)$item['rating'] : null;
                            $guestName = $item['guest_name'] ?? 'Guest';
                            $roomNumber = $item['room_number'] ? ' • Room ' . htmlspecialchars($item['room_number'], ENT_QUOTES, 'UTF-8') : '';
                            $comments = $item['comments'] ?? '';
                            $createdAt = $item['created_at'] ? date('M j, Y • g:i A', strtotime($item['created_at'])) : 'Recently';
                            ?>
                            <article class="border border-gray-100 rounded-lg p-4 hover:border-blue-200 transition group">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <div class="flex items-center gap-2 text-sm text-gray-600">
                                            <div class="flex text-yellow-400">
                                                <?php if ($rating !== null) : ?>
                                                    <?php for ($i = 1; $i <= 5; $i++) : ?>
                                                        <i class="<?php echo $i <= $rating ? 'fas' : 'far'; ?> fa-star"></i>
                                                    <?php endfor; ?>
                                                <?php else : ?>
                                                    <span class="text-xs text-gray-400">No rating</span>
                                                <?php endif; ?>
                                            </div>
                                            <span class="font-medium text-gray-700"><?php echo htmlspecialchars($guestName, ENT_QUOTES, 'UTF-8'); ?></span>
                                            <span class="text-gray-300"><?php echo $roomNumber; ?></span>
                                        </div>
                                        <p class="mt-2 text-sm text-gray-700 leading-relaxed">“<?php echo htmlspecialchars(mb_strimwidth($comments, 0, 160, '…'), ENT_QUOTES, 'UTF-8'); ?>”</p>
                                    </div>
                                    <div class="text-xs text-gray-400 whitespace-nowrap"><?php echo htmlspecialchars($createdAt, ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p class="text-sm text-gray-500">We haven’t received any guest feedback yet. Encourage guests to share their experience.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div id="feedback-explorer" class="bg-white rounded-lg shadow mt-8">
                <div class="px-6 py-5 border-b border-gray-200">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Feedback Explorer</h3>
                            <p class="text-sm text-gray-500">Filter, search, and triage guest submissions.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button id="feedback-reset" type="button" class="text-sm px-3 py-2 border border-gray-300 rounded-md text-gray-600 hover:bg-gray-50">
                                <i class="fas fa-undo mr-1"></i>Reset Filters
                            </button>
                        </div>
                    </div>
                    <form id="feedback-filter-form" class="mt-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                        <div>
                            <label for="feedback-search" class="text-xs uppercase tracking-wide text-gray-500">Search</label>
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400"><i class="fas fa-search"></i></span>
                                <input id="feedback-search" type="text" placeholder="Guest, email, room, or comment" value="<?php echo htmlspecialchars($defaultFilters['search'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" />
                            </div>
                        </div>
                        <div>
                            <label for="feedback-rating-filter" class="text-xs uppercase tracking-wide text-gray-500">Rating</label>
                            <select id="feedback-rating-filter" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Ratings</option>
                                <?php for ($i = 5; $i >= 1; $i--) : ?>
                                    <option value="<?php echo $i; ?>" <?php echo ($defaultFilters['rating'] === $i) ? 'selected' : ''; ?>><?php echo $i; ?> Stars</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div>
                            <label for="feedback-status-filter" class="text-xs uppercase tracking-wide text-gray-500">Status</label>
                            <select id="feedback-status-filter" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Statuses</option>
                                <option value="new">New</option>
                                <option value="in_progress">In Progress</option>
                                <option value="resolved">Resolved</option>
                            </select>
                        </div>
                        <div>
                            <label for="feedback-category-filter" class="text-xs uppercase tracking-wide text-gray-500">Category</label>
                            <select id="feedback-category-filter" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus-border-blue-500">
                                <option value="">All Categories</option>
                                <?php foreach (getGuestFeedbackAllowedCategories() as $category) : ?>
                                    <option value="<?php echo $category; ?>"><?php echo ucfirst($category); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="feedback-type-filter" class="text-xs uppercase tracking-wide text-gray-500">Feedback Type</label>
                            <select id="feedback-type-filter" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Types</option>
                                <?php foreach (getGuestFeedbackAllowedTypes() as $type) : ?>
                                    <option value="<?php echo $type; ?>"><?php echo ucfirst($type); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="feedback-date-from" class="text-xs uppercase tracking-wide text-gray-500">From</label>
                            <input id="feedback-date-from" type="date" value="" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label for="feedback-date-to" class="text-xs uppercase tracking-wide text-gray-500">To</label>
                            <input id="feedback-date-to" type="date" value="" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label for="feedback-sort" class="text-xs uppercase tracking-wide text-gray-500">Sort By</label>
                            <select id="feedback-sort" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="newest">Newest first</option>
                                <option value="oldest">Oldest first</option>
                                <option value="rating_high">Highest rating</option>
                                <option value="rating_low">Lowest rating</option>
                            </select>
                        </div>
                    </form>
                </div>

                <div class="px-6 py-4 border-b border-gray-200 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <div class="text-sm text-gray-600" id="feedback-table-count">
                        Showing <?php echo min($initialLimit, $totalFeedback); ?> of <?php echo number_format($totalFeedback); ?> feedback entries
                    </div>
                    <div class="flex items-center gap-3">
                        <label for="feedback-page-size" class="text-sm text-gray-500">Rows</label>
                        <select id="feedback-page-size" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="10" <?php echo $initialLimit === 10 ? 'selected' : ''; ?>>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <div class="flex items-center gap-2">
                            <button id="feedback-prev" type="button" class="px-3 py-2 border border-gray-300 rounded-md text-sm text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button id="feedback-next" type="button" class="px-3 py-2 border border-gray-300 rounded-md text-sm text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <th class="px-6 py-3 text-left">Guest</th>
                                <th class="px-6 py-3 text-left">Rating</th>
                                <th class="px-6 py-3 text-left">Category</th>
                                <th class="px-6 py-3 text-left">Type</th>
                                <th class="px-6 py-3 text-left">Comments</th>
                                <th class="px-6 py-3 text-left">Status</th>
                                <th class="px-6 py-3 text-left">Date</th>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="feedback-table-body" class="bg-white divide-y divide-gray-100">
                            <?php if (!empty($initialFeedback)) : ?>
                                <?php foreach ($initialFeedback as $row) :
                                    $guest = $row['guest'] ?? [];
                                    $reservation = $row['reservation'] ?? [];
                                    $statusClass = '';
                                    $statusLabel = ucfirst(str_replace('_', ' ', $row['status'] ?? 'new'));
                                    switch ($row['status'] ?? 'new') {
                                        case 'resolved':
                                            $statusClass = 'bg-green-100 text-green-800';
                                            break;
                                        case 'in_progress':
                                            $statusClass = 'bg-amber-100 text-amber-800';
                                            break;
                                        default:
                                            $statusClass = 'bg-blue-100 text-blue-800';
                                            break;
                                    }
                                    $rating = $row['rating'];
                                    $createdAt = $row['created_at'] ? date('M j, Y g:i A', strtotime($row['created_at'])) : '—';
                                    $categoryLabel = ucfirst(str_replace('_', ' ', $row['category'] ?? 'other'));
                                    $typeLabel = ucfirst(str_replace('_', ' ', $row['feedback_type'] ?? 'general'));
                                    $commentPreview = mb_strimwidth((string)($row['comments'] ?? ''), 0, 120, '…');
                                    ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 text-white flex items-center justify-center font-semibold">
                                                    <?php echo htmlspecialchars(strtoupper($guest['initials'] ?? 'GF'), ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($guest['name'] ?? 'Guest', ENT_QUOTES, 'UTF-8'); ?></p>
                                                    <?php if (!empty($guest['email'])) : ?>
                                                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($guest['email'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center text-yellow-400 text-sm">
                                                <?php if ($rating !== null) : ?>
                                                    <?php for ($i = 1; $i <= 5; $i++) : ?>
                                                        <i class="<?php echo $i <= $rating ? 'fas' : 'far'; ?> fa-star"></i>
                                                    <?php endfor; ?>
                                                    <span class="ml-2 text-gray-500"><?php echo number_format((float)$rating, 1); ?></span>
                                                <?php else : ?>
                                                    <span class="text-gray-400">No rating</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700"><?php echo htmlspecialchars($categoryLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($typeLabel, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="px-6 py-4 text-sm text-gray-700">
                                            <?php echo htmlspecialchars($commentPreview, ENT_QUOTES, 'UTF-8'); ?>
                                            <?php if (!empty($reservation['room_number'])) : ?>
                                                <span class="block text-xs text-gray-400 mt-1">Room <?php echo htmlspecialchars($reservation['room_number'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo $statusClass; ?>"><?php echo htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($createdAt, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button type="button" class="text-blue-600 hover:text-blue-800 feedback-view" data-feedback-id="<?php echo (int)$row['id']; ?>">View</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="8" class="px-6 py-10 text-center text-gray-500">
                                        <div class="flex flex-col items-center gap-3">
                                            <i class="fas fa-comments text-2xl text-gray-300"></i>
                                            <p class="font-medium">No feedback captured yet</p>
                                            <p class="text-sm">Guest submissions will appear here once available.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <?php include '../../includes/footer.php'; ?>
    </body>
</html>
