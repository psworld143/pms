<?php
/**
 * Training & Simulations (Housekeeping)
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

// Restrict to housekeeping; allow manager to preview if needed
if (!in_array($user_role, ['housekeeping', 'manager'], true)) {
	header('Location: login.php?error=access_denied');
	exit();
}

// Ensure training_logs table exists
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
	// Silent fail; UI will still render, AJAX will report errors
}

// Handle AJAX actions for starting/completing scenarios and fetching logs
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['ajax']) && $_POST['ajax'] === '1') {
	header('Content-Type: application/json');
	$action = $_POST['action'] ?? '';
	try {
		if ($action === 'start_scenario') {
			$scenario = trim($_POST['scenario'] ?? '');
			if ($scenario === '' || !in_array($scenario, [
				'Add Inventory Item',
				'Submit Transaction',
				'Request Supplies',
				'Update Room Inventory',
				'View Reports',
				'Review Audit Logs',
			], true)) {
				throw new RuntimeException('Invalid scenario');
			}
			$stmt = $pdo->prepare("INSERT INTO training_logs (user_id, scenario_name, status, start_time) VALUES (?, ?, 'In Progress', NOW())");
			$stmt->execute([$user_id, $scenario]);
			$id = (int)$pdo->lastInsertId();
			echo json_encode(['success' => true, 'log_id' => $id]);
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
			$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
			echo json_encode(['success' => true, 'logs' => $logs]);
			exit();
		}
		echo json_encode(['success' => false, 'message' => 'Unknown action']);
		exit();
	} catch (Throwable $e) {
		echo json_encode(['success' => false, 'message' => $e->getMessage()]);
		exit();
	}
}

$page_title = 'Training & Simulations';
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
						<i class="fas fa-chalkboard-teacher mr-2 text-purple-600"></i>
						Training & Simulations
					</h2>
					<p class="text-sm text-gray-600 mt-1">
						Practice housekeeping workflows in a safe, guided simulation.
					</p>
				</div>
				<div class="flex items-center space-x-4 text-sm">
					<span class="px-2 py-1 bg-green-100 text-green-800 rounded-full">
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
							<option>Add Inventory Item</option>
							<option>Submit Transaction</option>
							<option>Request Supplies</option>
							<option>Update Room Inventory</option>
							<option>View Reports</option>
							<option>Review Audit Logs</option>
						</select>
					</div>
					<div>
						<button id="start-scenario-btn" class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
							<i class="fas fa-play mr-2"></i>Start Scenario
						</button>
					</div>
				</div>
				<p class="text-xs text-gray-500 mt-3">Note: This simulates actions from modules like transactions, requests, and room inventory without affecting real data.</p>
			</div>

            <!-- Scenario Previews (Mid Page) -->
            <div id="scenario-previews-card" class="bg-white rounded-lg shadow p-6 mb-6">
				<div class="flex items-center justify-between mb-4">
					<h3 class="text-lg font-semibold text-gray-800">
						<i class="fas fa-eye mr-2 text-gray-600"></i>Scenario Previews
					</h3>
					<span class="text-xs text-gray-500">Preview questions before starting</span>
				</div>
				<div id="scenario-preview-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4"></div>
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

		<!-- Include footer -->
		<?php include '../includes/pos-footer.php'; ?>
	</div>

<script>
$(document).ready(function(){
	const scenarios = {
		'Add Inventory Item': [
			'Select an item category and prepare basic details (name/SKU).',
			'Enter unit and par level; verify allocations for rooms.',
			'Review summary and confirm simulated add.'
		],
		'Submit Transaction': [
			'Choose transaction type: Stock In / Stock Out / Adjustment.',
			'Select item and input quantity and unit cost.',
			'Add notes and reference; confirm simulated submission.'
		],
		'Request Supplies': [
			'Select an item required for a room.',
			'Provide quantity, room number, and reason (missing/damaged/etc.).',
			'Add notes and submit the simulated request.'
		],
		'Update Room Inventory': [
			'Pick a room and review current items and par levels.',
			'Mark used/missing/damaged items as needed.',
			'Confirm simulated room inventory update.'
		],
		'View Reports': [
			'Open transaction/usage reports filtered for your activity.',
			'Adjust date ranges and export options as needed.',
			'Review simulated generated report.'
		],
		'Review Audit Logs': [
			'Open audit logs for inventory and room actions.',
			'Filter by date and action type for housekeeping.',
			'Validate simulated compliance review.'
		]
	};

	// Scenario Q&A content (questions only)
    const scenarioQA = {
		'Add Inventory Item': [
            { q: 'How do you standardize item naming, SKU formatting, and unit selection to ensure clarity and consistent reporting?', a: 'Use a consistent category-prefix SKU (e.g., HK-SOAP-30ML), prefer singular units that match stocking practice (pcs, bottle), and maintain a central item dictionary so labels and units render uniformly across forms and reports.' },
            { q: 'What method do you use to set room-level par that adapts to seasonal occupancy changes?', a: 'Start with baseline consumption per stay, multiply by expected turns per day, add a 10–15% buffer for variability, then review weekly against real usage and occupancy to adjust par levels up or down.' }
		],
		'Submit Transaction': [
            { q: 'What checks help prevent incorrect in/out/adjustment choice and quantity errors during busy periods?', a: 'Require explicit transaction type confirmation, default units from item master, validate positive quantities, and show a review line with type, item, unit and quantity before submit.' },
            { q: 'How should notes and references be recorded to support audits and corrections later?', a: 'Capture short intent notes and a reference (e.g., room#, PO/folio), and include your name; this forms an audit trail to trace and reconcile variances quickly.' }
		],
		'Request Supplies': [
            { q: 'Which reason codes should you choose for common scenarios to speed approvals and ensure consistency?', a: 'Use standardized reasons like missing, damaged, low_stock, or replacement so managers can filter and approve in batches with predictable actions.' },
            { q: 'How do you provide room context so managers can prioritize without back-and-forth?', a: 'Always include room number and brief context (e.g., “Room 305—2 towels missing after checkout”) to eliminate follow‑up questions and accelerate fulfillment.' }
		],
		'Update Room Inventory': [
            { q: 'What is the best practice for logging used, missing, and damaged items immediately after service?', a: 'Record immediately on the floor from a mobile device while details are fresh; mark status accurately to keep par calculations and audits reliable.' },
            { q: 'How do you schedule replenishment to minimize guest impact while keeping rooms at par?', a: 'Bundle replenishments by floor and service window, target vacant or just‑cleaned rooms, and prioritize items under critical par.' }
		],
		'View Reports': [
            { q: 'Which filters help you review your recent usage and detect anomalies quickly?', a: 'Filter by last 7–14 days and by item family; sort by highest variance from average consumption to reveal outliers fast.' },
            { q: 'How do you interpret consumption vs. par to decide if a room needs restocking sooner?', a: 'Compare current level to par and the recent daily burn rate; if projected to dip below par before next round, schedule an early top-up.' }
		],
		'Review Audit Logs': [
            { q: 'Which audit views help you verify your latest updates and supply requests?', a: 'Use the per-user filter for today/this week to see your actions; verify timestamps and item IDs match your notes.' },
            { q: 'How do you confirm that a correction you submitted has been applied properly?', a: 'Cross-check the audit record with item balance or request status; if mismatched, submit a follow-up note so the manager can reconcile.' }
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

    function renderSteps(){
		const list = $('#scenario-steps');
		list.empty();
        currentSteps.forEach((step, idx) => {
			const isActive = idx === currentIndex;
			const cls = isActive ? 'border-blue-200 bg-blue-50' : 'border-gray-200 bg-white';
            // If the step is an object with q/a, render nicely; otherwise treat as plain text
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
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${l.scenario_name}</td>
						<td class="px-6 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${badge}">${l.status}</span></td>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${l.start_time || ''}</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${l.end_time || ''}</td>
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
        // Immediately hide landing sections and set simulation-active to enforce CSS hide
        hideLandingSections();
        $('body').addClass('simulation-active');
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
                $('body').removeClass('simulation-active');
			}
		}, 'json');
	});

	$('#next-step').on('click', function(){
		if (currentIndex < currentSteps.length - 1) { currentIndex++; renderSteps(); }
	});
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
        $('body').removeClass('simulation-active');
		$('html, body').animate({ scrollTop: $('main').offset().top - 80 }, 300);
	});
	$('#skip-step').on('click', function(){
		if (currentIndex < currentSteps.length - 1) { currentIndex++; renderSteps(); }
	});
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

	// Initial load
	loadLogs();
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
});
</script>
</body>
</html>


