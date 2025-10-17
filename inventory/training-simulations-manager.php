<?php
/**
 * Training & Simulations (Manager)
 * Hotel PMS Training System - Inventory Module
 */

require_once __DIR__ . '/../vps_session_fix.php';
require_once __DIR__ . '/../includes/database.php';

// Require login
if (!isset($_SESSION['user_id'])) {
	header('Location: login.php');
	exit();
}

$user_id = (int)($_SESSION['user_id'] ?? 0);
$user_role = $_SESSION['user_role'] ?? '';

// Restrict to manager
if ($user_role !== 'manager') {
	header('Location: login.php?error=access_denied');
	exit();
}

// Ensure training_logs table exists (shared with housekeeping)
try {
	global $pdo;
	$pdo->exec("CREATE TABLE IF NOT EXISTS training_logs (
		id INT AUTO_INCREMENT PRIMARY KEY,
		user_id INT NOT NULL,
		scenario_name VARCHAR(255) NOT NULL,
		status ENUM('In Progress','Completed') NOT NULL DEFAULT 'In Progress',
		start_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		end_time DATETIME NULL,
		INDEX (user_id),
		INDEX (scenario_name),
		INDEX (status)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
} catch (Throwable $e) {
	// Silent; AJAX will report errors
}

// AJAX handlers
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['ajax']) && $_POST['ajax'] === '1') {
	header('Content-Type: application/json');
	$action = $_POST['action'] ?? '';
	try {
		if ($action === 'start_scenario') {
			$scenario = trim($_POST['scenario'] ?? '');
			$allowed = [
				'Manage Inventory Items',
				'View Reports',
				'Enhanced Reports & Analysis',
				'Configure Auto Reordering',
				'Use Barcode Scanner',
				'Accounting Integration',
				'Monitor Transactions',
				'Manage Room Inventory (Manager)',
				'Approve Supply Requests'
			];
			if ($scenario === '' || !in_array($scenario, $allowed, true)) {
				throw new RuntimeException('Invalid scenario');
			}
			$stmt = $pdo->prepare("INSERT INTO training_logs (user_id, scenario_name, status, start_time) VALUES (?, ?, 'In Progress', NOW())");
			$stmt->execute([$user_id, $scenario]);
			echo json_encode(['success' => true, 'log_id' => (int)$pdo->lastInsertId()]);
			exit();
		}
		if ($action === 'complete_scenario') {
			$log_id = (int)($_POST['log_id'] ?? 0);
			if ($log_id <= 0) throw new RuntimeException('Missing log ID');
			$stmt = $pdo->prepare("UPDATE training_logs SET status = 'Completed', end_time = NOW() WHERE id = ? AND user_id = ?");
			$stmt->execute([$log_id, $user_id]);
			echo json_encode(['success' => true]);
			exit();
		}
		if ($action === 'get_logs') {
			$stmt = $pdo->prepare("SELECT id, scenario_name, status, start_time, end_time FROM training_logs WHERE user_id = ? ORDER BY start_time DESC LIMIT 50");
			$stmt->execute([$user_id]);
			echo json_encode(['success' => true, 'logs' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
			exit();
		}
		echo json_encode(['success' => false, 'message' => 'Unknown action']);
		exit();
	} catch (Throwable $e) {
		echo json_encode(['success' => false, 'message' => $e->getMessage()]);
		exit();
	}
}

$page_title = 'Training & Simulations (Manager)';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo $page_title; ?> - Hotel Inventory System</title>
	<link rel="icon" type="image/png" href="../../assets/images/seait-logo.png">
	<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
	<style>
		#sidebar { transition: transform 0.3s ease-in-out; }
		@media (max-width: 1023px) { #sidebar { transform: translateX(-100%); z-index: 50; } #sidebar.sidebar-open { transform: translateX(0); } }
		@media (min-width: 1024px) { #sidebar { transform: translateX(0) !important; } }
		#sidebar-overlay { transition: opacity 0.3s ease-in-out; z-index: 40; }
		.main-content { margin-left: 0; padding-top: 4rem; }
		@media (min-width: 1024px) { .main-content { margin-left: 16rem; } }
		/* Ensure landing sections disappear while simulation is active */
		.simulation-active #scenario-previews-card,
		.simulation-active #training-progress-card {
			display: none !important;
		}
	</style>
</head>
<body class="bg-gray-50">
	<div class="flex min-h-screen">
		<!-- Sidebar Overlay for Mobile -->
		<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden" onclick="closeSidebar()"></div>

		<!-- Include unified inventory header and sidebar -->
		<?php include 'includes/inventory-header.php'; ?>
		<?php include 'includes/sidebar-inventory.php'; ?>

		<!-- Main Content -->
		<main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
			<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
				<div>
					<h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">
						<i class="fas fa-graduation-cap mr-2 text-purple-600"></i>
						Training & Simulations (Manager)
					</h2>
					<p class="text-sm text-gray-600 mt-1">
						Practice manager workflows in a safe, guided simulation.
					</p>
				</div>
				<div class="flex items-center space-x-4 text-sm">
					<span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full">
						<?php echo ucfirst($user_role); ?>
					</span>
				</div>
			</div>

            <!-- Start Scenario Card -->
            <div id="start-scenario-card" class="bg-white rounded-lg shadow p-6 mb-6">
				<h3 class="text-lg font-semibold text-gray-800 mb-4">Start Scenario</h3>
				<div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
					<div class="md:col-span-2">
						<label class="block text-sm font-medium text-gray-700 mb-2">Choose a Simulation Scenario</label>
						<select id="scenario-select" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
							<option value="">Select Scenario</option>
							<option>Manage Inventory Items</option>
							<option>View Reports</option>
							<option>Enhanced Reports & Analysis</option>
							<option>Configure Auto Reordering</option>
							<option>Use Barcode Scanner</option>
							<option>Accounting Integration</option>
							<option>Monitor Transactions</option>
							<option>Manage Room Inventory (Manager)</option>
							<option>Approve Supply Requests</option>
						</select>
					</div>
					<div>
						<button id="start-scenario-btn" class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
							<i class="fas fa-play mr-2"></i>Start Scenario
						</button>
					</div>
				</div>
				<p class="text-xs text-gray-500 mt-3">Note: Simulates manager actions across items, reports, automation, and oversight without changing real data.</p>
			</div>

            <!-- Scenario Previews (Mid Page) -->
            <div id="scenario-previews-card" class="bg-white rounded-lg shadow p-6 mb-6">
				<div class="flex items-center justify-between mb-4">
					<h3 class="text-lg font-semibold text-gray-800">
						<i class="fas fa-eye mr-2 text-gray-600"></i>Scenario Previews
					</h3>
					<span class="text-xs text-gray-500">Preview questions and exemplar answers before starting</span>
				</div>
				<div id="scenario-preview-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4"></div>
			</div>

			<!-- Scenario Full Preview Modal -->
			<div id="scenario-full-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
				<div class="flex items-center justify-center min-h-screen p-4">
					<div class="bg-white rounded-lg shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
						<div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
							<h4 id="modal-scenario-title" class="text-lg font-semibold text-gray-900">Scenario</h4>
							<button id="close-scenario-modal" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
						</div>
						<div class="p-6">
							<div id="modal-questions" class="space-y-3"></div>
						</div>
					</div>
				</div>
			</div>

			<!-- Simulation Steps / Instructions -->
			<div id="simulation-panel" class="bg-white rounded-lg shadow p-6 mb-6 hidden">
				<div class="flex items-center justify-between mb-4">
					<h3 class="text-lg font-semibold text-gray-800">
						<span id="scenario-title">Scenario</span>
					</h3>
					<span id="scenario-status" class="px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">In Progress</span>
				</div>
				<ol id="scenario-steps" class="list-decimal ml-6 space-y-3 text-sm text-gray-700"></ol>
				<div class="mt-6 flex justify-between">
					<button id="prev-step" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-arrow-left mr-2"></i>Previous</button>
					<div class="space-x-2">
						<button id="skip-step" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Skip</button>
						<button id="next-step" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">Next<i class="fas fa-arrow-right ml-2"></i></button>
						<button id="complete-scenario" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700 hidden"><i class="fas fa-check mr-2"></i>Complete Scenario</button>
					</div>
				</div>
				<div id="completion-feedback" class="mt-6 hidden">
					<div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-md"><i class="fas fa-check-circle mr-2"></i>Scenario Completed Successfully!</div>
				</div>
			</div>

			<!-- Recent Progress -->
			<div id="training-progress-card" class="bg-white rounded-lg shadow">
				<div class="px-6 py-4 border-b border-gray-200">
					<h3 class="text-lg font-semibold text-gray-800">My Training Progress</h3>
				</div>
				<div class="overflow-x-auto">
					<table class="min-w-full divide-y divide-gray-200">
						<thead class="bg-gray-50">
							<tr>
								<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scenario</th>
								<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
								<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Time</th>
								<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Time</th>
							</tr>
						</thead>
						<tbody id="logs-tbody" class="bg-white divide-y divide-gray-200"></tbody>
					</table>
				</div>
			</div>
		</main>

		<!-- Include footer -->
		<?php include '../includes/pos-footer.php'; ?>
	</div>

<script>
$(document).ready(function(){
	const scenarios = {
		'Manage Inventory Items': [
			'Open Items module and review existing items list.',
			'Add a new item with unit and par level (simulated).',
			'Edit and archive items as needed (simulated).'
		],
		'View Reports': [
			'Open Reports and set date filters.',
			'Generate a report and review KPIs (simulated).',
			'Export the report to PDF/CSV (simulated).'
		],
		'Enhanced Reports & Analysis': [
			'Open Enhanced Reports to explore trends.',
			'Filter by department/items for cost analysis.',
			'Interpret simulated insights and actions.'
		],
		'Configure Auto Reordering': [
			'Open Auto Reordering and set thresholds.',
			'Choose suppliers and lead times (simulated).',
			'Confirm automation rules (simulated).'
		],
		'Use Barcode Scanner': [
			'Open Barcode Scanner and scan sample items (simulated).',
			'Validate item match and quantity update flow.',
			'Confirm simulated stock adjustment.'
		],
		'Accounting Integration': [
			'Open Accounting module and map item categories.',
			'Preview journal entry exports (simulated).',
			'Confirm reconciliation workflow (simulated).'
		],
		'Monitor Transactions': [
			'Open Transactions and review inflow/outflow badges.',
			'Filter by date and user for oversight.',
			'Flag anomalies for follow-up (simulated).'
		],
		'Manage Room Inventory (Manager)': [
			'Open Room Inventory and select a floor.',
			'View room stock statuses and assign items (simulated).',
			'Launch a simulated audit/restock process.'
		],
		'Approve Supply Requests': [
			'Open Requests and filter pending items.',
			'Review details and approve/reject (simulated).',
			'Log decision rationale (simulated).'
		]
	};

	let currentSteps = [];
	let currentIndex = 0;
	let currentLogId = null;

	function hideLandingSections(){
		$('#scenario-previews-card').addClass('hidden');
		$('#training-progress-card').addClass('hidden');
		$('#scenario-full-modal').addClass('hidden');
	}

	function showLandingSections(){
		$('#scenario-previews-card').removeClass('hidden');
		$('#training-progress-card').removeClass('hidden');
	}

	// Scenario Q&A content (professional, long-form)
	const scenarioQA = {
		'Manage Inventory Items': [
			{
				q: 'When onboarding a new consumable (e.g., premium shampoo), how should you structure SKU, unit, and par levels to balance housekeeping availability with cost controls?',
				a: 'Establish a human-readable SKU tied to category and size (e.g., SHP-PRM-250ML), define a consistent unit (e.g., bottle), and set par levels per room type informed by average stay and service standard. Calibrate initial par by historical consumption plus a buffer for occupancy spikes, then refine monthly using actual usage reports to minimize overstock while avoiding service lapses.'
			},
			{
				q: 'What prerequisites and validations should be captured before archiving an item that may still exist in rooms or pending transactions?',
				a: 'Confirm zero outstanding purchase orders, no negative impact on open room assignments, and migrate or substitute room-linked records to an active equivalent where applicable. Require manager acknowledgement of downstream effects (reporting continuity, replacements) and log an audit entry with rationale and reviewer identity.'
			}
		],
		'View Reports': [
			{
				q: 'How do you configure reporting periods and segmentation to surface actionable variance between forecasted and actual consumption?',
				a: 'Use aligned monthly and weekly cuts with occupancy-adjusted baselines. Segment by department, item family, and room class. Apply moving averages to smooth volatility and highlight exceptions via variance thresholds; schedule automated deliveries of exception reports to managers for timely remediation.'
			}
		],
		'Enhanced Reports & Analysis': [
			{
				q: 'Which analytical lenses help uncover root causes of chronic low-stock incidents across floors?',
				a: 'Correlate low-stock flags with housekeeping staffing patterns, guest mix (e.g., family vs. business), and seasonality. Visualize item turn by floor and shift; overlay supplier lead times and delivery adherence. Pilot targeted increases to par on problem floors and measure resolution rates before scaling.'
			}
		],
		'Configure Auto Reordering': [
			{
				q: 'What safeguards should be configured to prevent runaway auto-orders during anomalies (e.g., data entry errors or one-off events)?',
				a: 'Set min/max reorder bounds per item, require manager approval above a dynamic spend threshold, and implement anomaly detectors (e.g., 3x deviation from rolling average). Maintain vendor caps and cooldowns, and route flagged events for manual review with contextual dashboards.'
			}
		],
		'Use Barcode Scanner': [
			{
				q: 'How should barcode scanning workflows be designed to minimize mis-scans and latency during peak operations?',
				a: 'Adopt large high-contrast labels with unique item-level encoding, enable audible/visual feedback, debounce rapid scans, and cache top SKUs locally. Gracefully handle offline operation with queued sync and provide a quick-correct UI for misreads with recent-history undo.'
			}
		],
		'Accounting Integration': [
			{
				q: 'What mapping strategy aligns inventory consumption to the chart of accounts while preserving granular auditability?',
				a: 'Map item families to expense accounts and attach dimensions for department and property. Preserve item-level detail in auxiliary ledgers while summarizing to GL via batched journal entries. Retain reference keys to transactions to support end-to-end traceability and reconciliation.'
			}
		],
		'Monitor Transactions': [
			{
				q: 'Which indicators reliably distinguish normal consumption from potential shrinkage or misuse?',
				a: 'Track per-occupied-room consumption against seasonal baselines, flag spikes exceeding set standard deviations, and correlate with shift logs and room statuses. Combine anomaly scores with user-level patterns (e.g., repeated adjustments) and require manager acknowledgment and follow-up notes.'
			}
		],
		'Manage Room Inventory (Manager)': [
			{
				q: 'What criteria should drive restocking priorities across floors to minimize guest-impact while optimizing labor?',
				a: 'Prioritize rooms with occupancy turnovers, VIP segments, and items below critical par. Batch tasks by contiguous rooms/floors to reduce travel time. Schedule replenishment windows aligned to housekeeping shifts and delivery availability, with live updates flowing from completed tasks.'
			}
		],
		'Approve Supply Requests': [
			{
				q: 'How should approval notes articulate reasoning to ensure repeatable decisions and training value?',
				a: 'Use structured notes that reference reason codes, item criticality, room impact, and alternatives considered. Include SLA targets and follow-up actions when applicable. This transparency standardizes decisions, accelerates onboarding, and improves audit confidence.'
			}
		]
	};

    function renderSteps(){
		const list = $('#scenario-steps');
		list.empty();
        currentSteps.forEach((step, idx) => {
			const isActive = idx === currentIndex;
			const cls = isActive ? 'border-blue-200 bg-blue-50' : 'border-gray-200 bg-white';
            if (typeof step === 'object' && step.q) {
                let content = `<div class=\"font-medium text-gray-900 mb-1\">${step.q}</div>`;
                if (step.a) content += `<div class=\"mt-2 text-sm text-gray-800 bg-green-50 border-l-4 border-green-500 rounded p-2 scenario-answer\">${step.a}</div>`;
                list.append(`<li class=\"p-3 border ${cls} rounded\">${content}</li>`);
            } else {
                list.append(`<li class=\"p-3 border ${cls} rounded\">${step}</li>`);
            }
		});
		$('#prev-step').prop('disabled', currentIndex === 0);
		$('#next-step').toggle(currentIndex < currentSteps.length - 1);
		$('#complete-scenario').toggle(currentIndex >= currentSteps.length - 1);
	}

	function loadLogs(){
		$.post('', { ajax: '1', action: 'get_logs' }, function(resp){
			if (!resp || !resp.success) return;
			const rows = (resp.logs || []).map(l => {
				const badge = l.status === 'Completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
				return `
					<tr>
						<td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-900\">${l.scenario_name}</td>
						<td class=\"px-6 py-4 whitespace-nowrap\"><span class=\"px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${badge}\">${l.status}</span></td>
						<td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-900\">${l.start_time || ''}</td>
						<td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-900\">${l.end_time || ''}</td>
					</tr>`;
			}).join('');
			$('#logs-tbody').html(rows);
		}, 'json');
	}

	function buildScenarioCards(){
		const grid = $('#scenario-preview-grid');
		grid.empty();
		Object.keys(scenarios).forEach(function(name){
			const qa = scenarioQA[name] || [];
			const qaBlocks = qa.map(({q}) => `
				<div class=\"p-3 bg-gray-50 rounded border border-gray-200\"> 
					<div class=\"font-semibold text-gray-800\">${q}</div>
				</div>`).join('');
			const firstOne = (qa.slice(0, 1) || []).map(({q}) => `<div class=\"text-xs text-gray-700 truncate\">• ${q}</div>`).join('');
			const card = `
				<button class=\"w-full text-left border rounded-lg p-3 shadow-sm hover:shadow transition bg-white focus:outline-none focus:ring-2 focus:ring-purple-500 mini-card\" data-scenario=\"${name.replace(/\"/g,'&quot;')}\">\n\
					<div class=\"flex items-center justify-between mb-0.5\">\n\
						<h4 class=\"font-medium text-gray-900 text-sm\">${name}</h4>\n\
						<span class=\"text-2xs text-gray-500\">Tap to view</span>\n\
					</div>\n\
					<div>${firstOne || '<div class=\\"text-gray-500 text-xs\\">No questions</div>'}</div>\n\
				</button>`;
			grid.append(card);
		});
	}

	$('#start-scenario-btn').on('click', function(){
		const scenario = $('#scenario-select').val();
		if (!scenario) { alert('Please select a scenario'); return; }
		if (!scenarios[scenario]) { alert('Unsupported scenario'); return; }
        // Immediately hide landing sections for responsive UX
        hideLandingSections();
        $('body').addClass('simulation-active');
		$.post('', { ajax: '1', action: 'start_scenario', scenario }, function(resp){
			if (resp && resp.success) {
				// Ensure landing sections stay hidden
				hideLandingSections();
				currentLogId = resp.log_id;
				// Populate steps from preview questions when available
                const qa = scenarioQA[scenario] || [];
                currentSteps = (qa.length ? qa : (scenarios[scenario] || []));
				currentIndex = 0;
				$('#scenario-title').text(scenario);
				$('#scenario-status').removeClass('bg-green-100 text-green-800').addClass('bg-yellow-100 text-yellow-800').text('In Progress');
				$('#completion-feedback').addClass('hidden');
				$('#simulation-panel').removeClass('hidden');
				// Scroll to simulation panel for focus
				$('html, body').animate({ scrollTop: $('#simulation-panel').offset().top - 80 }, 300);
				renderSteps();
				loadLogs();
			} else {
				alert('Error starting scenario: ' + (resp && resp.message ? resp.message : 'Unknown error'));
                // Restore landing sections on error
                showLandingSections();
                $('body').removeClass('simulation-active');
			}
		}, 'json');
	});


	// Build mid-page cards on load
	buildScenarioCards();

	// Click handler to open full preview modal
	$(document).on('click', '.mini-card', function(){
		const scenario = $(this).data('scenario');
		const qa = scenarioQA[scenario] || [];
		$('#modal-scenario-title').text(scenario + ' — Preview Questions');
		const blocks = qa.map(({q}) => `
			<div class="p-3 bg-gray-50 rounded border border-gray-200"> 
				<div class="font-medium text-gray-900">${q}</div>
			</div>`).join('');
		$('#modal-questions').html(blocks || '<div class="text-gray-500">No questions available.</div>');
		$('#scenario-full-modal').removeClass('hidden');
	});

	$('#close-scenario-modal').on('click', function(){
		$('#scenario-full-modal').addClass('hidden');
	});

	$('#scenario-full-modal').on('click', function(e){
		if (e.target === this) { $(this).addClass('hidden'); }
	});

	$('#next-step').on('click', function(){ if (currentIndex < currentSteps.length - 1) { currentIndex++; renderSteps(); } });
	$('#prev-step').on('click', function(){
		if (currentIndex > 0) {
			currentIndex--;
			renderSteps();
			return;
		}
        // If at first step, return to landing: show previews and progress again, hide simulation panel
        $('#simulation-panel').addClass('hidden');
        showLandingSections();
        $('body').removeClass('simulation-active');
		$('html, body').animate({ scrollTop: $('main').offset().top - 80 }, 300);
	});
	$('#skip-step').on('click', function(){ if (currentIndex < currentSteps.length - 1) { currentIndex++; renderSteps(); } });
	$('#complete-scenario').on('click', function(){
		if (!currentLogId) { alert('No active scenario'); return; }
		$.post('', { ajax: '1', action: 'complete_scenario', log_id: currentLogId }, function(resp){
			if (resp && resp.success) {
				$('#scenario-status').removeClass('bg-yellow-100 text-yellow-800').addClass('bg-green-100 text-green-800').text('Completed');
				$('#completion-feedback').removeClass('hidden');
				loadLogs();
			} else {
				alert('Error completing scenario: ' + (resp && resp.message ? resp.message : 'Unknown error'));
			}
		}, 'json');
	});

	// Initial
	loadLogs();
});
</script>
</body>
</html>


