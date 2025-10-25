<?php
/**
 * Training & Simulations (Manager) - Enhanced Version
 * Hotel PMS Training System - Inventory Module with Multiple Choice Questions and History
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
$user_name = $_SESSION['user_name'] ?? 'User';

// Allow both manager and housekeeping users
if (!in_array($user_role, ['manager', 'housekeeping'], true)) {
	header('Location: login.php?error=access_denied');
	exit();
}

$page_title = $user_role === 'manager' ? 'Training & Simulations (Manager)' : 'Training & Simulations (Housekeeping)';
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
		
		/* Scenario card animations */
		.scenario-card {
			transition: all 0.3s ease;
		}
		.scenario-card:hover {
			transform: translateY(-2px);
			box-shadow: 0 10px 25px rgba(0,0,0,0.1);
		}
		
		/* Progress bar animation */
		.progress-bar {
			transition: width 0.5s ease;
		}
		
		/* Modal animations */
		.modal-enter {
			animation: modalEnter 0.3s ease-out;
		}
		@keyframes modalEnter {
			from { opacity: 0; transform: scale(0.9); }
			to { opacity: 1; transform: scale(1); }
		}
		
		/* Custom scrollbar for the questions container */
		#generated-questions-content::-webkit-scrollbar {
			width: 8px;
		}
		
		#generated-questions-content::-webkit-scrollbar-track {
			background: #f1f5f9;
			border-radius: 4px;
		}
		
		#generated-questions-content::-webkit-scrollbar-thumb {
			background: #cbd5e1;
			border-radius: 4px;
		}
		
		#generated-questions-content::-webkit-scrollbar-thumb:hover {
			background: #94a3b8;
		}
		
		/* Ensure the modal content is scrollable */
		#create-scenario-modal .overflow-y-auto {
			scrollbar-width: thin;
			scrollbar-color: #cbd5e1 #f1f5f9;
		}
		
		/* Custom scrollbar for scenario modal content */
		#scenario-modal .overflow-y-auto::-webkit-scrollbar {
			width: 12px !important;
		}
		
		#scenario-modal .overflow-y-auto::-webkit-scrollbar-track {
			background: #f1f5f9 !important;
			border-radius: 6px !important;
		}
		
		#scenario-modal .overflow-y-auto::-webkit-scrollbar-thumb {
			background: #3b82f6 !important;
			border-radius: 6px !important;
		}
		
		#scenario-modal .overflow-y-auto::-webkit-scrollbar-thumb:hover {
			background: #1d4ed8 !important;
		}
		
		/* Force scrollbar to always be visible */
		#scenario-modal .overflow-y-auto {
			scrollbar-width: auto !important;
			scrollbar-color: #3b82f6 #f1f5f9 !important;
			overflow-y: scroll !important;
		}
		
		/* Ensure scenario content has proper spacing and scrolling */
		#scenario-content {
			min-height: 300px !important;
			padding-bottom: 10px !important;
		}
		
		/* Reduce spacing in scenario modal */
		#scenario-content .mb-4 {
			margin-bottom: 1rem !important;
		}
		
		#scenario-content .mb-3 {
			margin-bottom: 0.75rem !important;
		}
		
		#scenario-content .space-y-2 > * + * {
			margin-top: 0.5rem !important;
		}
		
		#scenario-content .p-4 {
			padding: 1rem !important;
		}
		
		#scenario-content .p-2 {
			padding: 0.5rem !important;
		}
		
		/* Add smooth scrolling to scenario modal */
		#scenario-modal .overflow-y-auto {
			scroll-behavior: smooth !important;
		}
		
		/* Force the modal content area to be scrollable */
		#scenario-modal .flex-1 {
			overflow-y: scroll !important;
			max-height: calc(90vh - 80px) !important;
		}
		
		/* Additional scrollbar forcing */
		#scenario-modal .p-6 {
			overflow-y: scroll !important;
			height: calc(90vh - 120px) !important;
		}
		
		/* Make sure scrollbar is always visible */
		#scenario-modal .overflow-y-auto {
			overflow-y: scroll !important;
			height: calc(90vh - 120px) !important;
		}
		
		/* Custom scrollbar for main content */
		main.overflow-y-auto::-webkit-scrollbar {
			width: 8px;
		}
		
		main.overflow-y-auto::-webkit-scrollbar-track {
			background: #f1f5f9;
			border-radius: 4px;
		}
		
		main.overflow-y-auto::-webkit-scrollbar-thumb {
			background: #cbd5e1;
			border-radius: 4px;
		}
		
		main.overflow-y-auto::-webkit-scrollbar-thumb:hover {
			background: #94a3b8;
		}
		
		/* Ensure scenarios grid has proper spacing */
		#scenarios-grid {
			min-height: 400px;
		}
		
		/* Training Scenarios container - no scrolling */
		#scenarios-container {
			/* Removed all scrollbar styling - container will expand to fit content */
		}
		
		/* Smooth scrolling for the entire page */
		html {
			scroll-behavior: smooth;
		}
		
		/* Ensure proper height and scrolling */
		body {
			height: 100vh;
			overflow: hidden;
		}
		
		/* Make sure the main content can scroll */
		main {
			height: calc(100vh - 4rem);
			overflow-y: auto;
		}
		
		/* Responsive adjustments */
		@media (max-width: 1024px) {
			main {
				height: calc(100vh - 3rem);
			}
		}
	</style>
</head>
<body class="bg-gray-50">
	<div class="flex min-h-screen h-screen">
		<!-- Sidebar Overlay for Mobile -->
		<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden" onclick="closeSidebar()"></div>

		<!-- Include unified inventory header and sidebar -->
		<?php include 'includes/inventory-header.php'; ?>
		<?php include 'includes/sidebar-inventory.php'; ?>

		<!-- Main Content -->
		<main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300 overflow-y-auto max-h-screen">
			<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
				<div>
					<h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">
						<i class="fas fa-graduation-cap mr-2 text-purple-600"></i>
						<?php echo $page_title; ?>
					</h2>
					<p class="text-sm text-gray-600 mt-1">
						<?php if ($user_role === 'manager'): ?>
							Practice inventory management skills with interactive scenarios and multiple choice questions. Access both manager and housekeeping training modules.
						<?php else: ?>
							Practice housekeeping workflows in a safe, guided simulation with interactive scenarios and multiple choice questions.
						<?php endif; ?>
					</p>
				</div>
				<div class="flex items-center space-x-4 text-sm">
					<span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full">
						<?php echo ucfirst($user_role); ?>
					</span>
					<div class="text-right">
						<p class="text-sm text-gray-500">Welcome back,</p>
						<p class="font-semibold text-gray-900"><?php echo htmlspecialchars($user_name); ?></p>
					</div>
					<div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-indigo-500 rounded-full flex items-center justify-center">
						<i class="fas fa-user text-white text-xl"></i>
					</div>
				</div>
			</div>

			<!-- Progress Summary -->
			<div id="progress-summary" class="bg-white rounded-lg shadow p-6 mb-6">
				<h3 class="text-lg font-semibold text-gray-800 mb-4">
					<i class="fas fa-chart-line mr-2 text-green-600"></i>Your Progress Summary
				</h3>
				<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
					<div class="text-center p-4 bg-blue-50 rounded-lg">
						<div class="text-2xl font-bold text-blue-600" id="total-scenarios">0</div>
						<div class="text-sm text-blue-800">Total Scenarios</div>
					</div>
					<div class="text-center p-4 bg-green-50 rounded-lg">
						<div class="text-2xl font-bold text-green-600" id="completed-scenarios">0</div>
						<div class="text-sm text-green-800">Completed</div>
					</div>
					<div class="text-center p-4 bg-yellow-50 rounded-lg">
						<div class="text-2xl font-bold text-yellow-600" id="avg-score">0%</div>
						<div class="text-sm text-yellow-800">Average Score</div>
				</div>
					<div class="text-center p-4 bg-purple-50 rounded-lg">
						<div class="text-2xl font-bold text-purple-600" id="certificates-earned">0</div>
						<div class="text-sm text-purple-800">Certificates</div>
					</div>
				</div>
			</div>

			<!-- Training History -->
			<div class="bg-white rounded-lg shadow p-6 mb-6">
				<div class="flex items-center justify-between mb-4">
					<h3 class="text-lg font-semibold text-gray-800">
						<i class="fas fa-history mr-2 text-blue-600"></i>Training History
					</h3>
					<button onclick="loadTrainingHistory()" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">
						<i class="fas fa-refresh mr-2"></i>Refresh
						</button>
					</div>
				<div id="training-history-content">
					<div class="text-center py-8 text-gray-500">
						<i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
						<p>Loading training history...</p>
				</div>
				</div>
			</div>

			<!-- Training Type Selector (Manager Only) -->
			<?php if ($user_role === 'manager'): ?>
			<div class="bg-white rounded-lg shadow p-6 mb-6">
				<div class="flex items-center justify-between mb-4">
					<h3 class="text-lg font-semibold text-gray-800">
						<i class="fas fa-layer-group mr-2 text-blue-600"></i>Training Module Selection
					</h3>
				</div>
				<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
					<button id="manager-training-btn" onclick="switchTrainingType('manager')" class="p-4 border-2 border-blue-500 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
						<div class="flex items-center mb-2">
							<i class="fas fa-user-tie text-blue-600 text-xl mr-3"></i>
							<h4 class="font-semibold text-gray-900">Manager Training</h4>
			</div>
						<p class="text-sm text-gray-600">Advanced inventory management, reporting, and system administration scenarios.</p>
					</button>
					<button id="housekeeping-training-btn" onclick="switchTrainingType('housekeeping')" class="p-4 border-2 border-gray-300 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
						<div class="flex items-center mb-2">
							<i class="fas fa-broom text-gray-600 text-xl mr-3"></i>
							<h4 class="font-semibold text-gray-900">Housekeeping Training</h4>
						</div>
						<p class="text-sm text-gray-600">Room inventory, supply management, and daily operational procedures.</p>
					</button>
				</div>
			</div>
			<?php endif; ?>

			<!-- Scenarios Grid -->
			<div class="bg-white rounded-lg shadow p-6 mb-6">
				<div class="flex items-center justify-between mb-6">
					<h3 class="text-lg font-semibold text-gray-800">
						<i class="fas fa-theater-masks mr-2 text-gray-600"></i>
						<span id="scenarios-title">
							<?php if ($user_role === 'manager'): ?>
								Manager Training Scenarios
							<?php else: ?>
								Housekeeping Training Scenarios
							<?php endif; ?>
						</span>
					</h3>
					<div class="flex items-center space-x-4">
						<select id="difficulty-filter" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
							<option value="">All Difficulties</option>
							<option value="beginner">Beginner</option>
							<option value="intermediate">Intermediate</option>
							<option value="advanced">Advanced</option>
						</select>
						<select id="type-filter" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
							<option value="">All Types</option>
							<option value="inventory_management">Inventory Management</option>
							<option value="reporting">Reporting</option>
							<option value="automation">Automation</option>
							<option value="monitoring">Monitoring</option>
							<option value="room_inventory">Room Inventory</option>
							<option value="approval">Approval</option>
						</select>
						<?php if ($user_role === 'manager'): ?>
						<button onclick="openCreateScenarioModal()" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700">
							<i class="fas fa-plus mr-2"></i>Create New Scenario
						</button>
						<?php endif; ?>
						<button onclick="loadTrainingHistory()" class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm hover:bg-gray-700">
							<i class="fas fa-history mr-2"></i>View History
						</button>
					</div>
				</div>
				<div id="scenarios-container" class="border border-gray-200 rounded-lg p-4 bg-gray-50">
					<div id="scenarios-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
						<!-- Scenarios will be loaded here -->
					</div>
				</div>
			</div>

			<!-- Training History Modal -->
			<div id="history-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
				<div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto modal-enter">
						<div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
						<h4 class="text-lg font-semibold text-gray-900">
							<i class="fas fa-history mr-2"></i>Training History
						</h4>
						<button onclick="closeHistoryModal()" class="text-gray-500 hover:text-gray-700">
							<i class="fas fa-times"></i>
						</button>
						</div>
						<div class="p-6">
						<div id="history-content">
							<!-- History content will be loaded here -->
						</div>
					</div>
				</div>
			</div>

			<!-- Scenario Modal -->
			<div id="scenario-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
				<div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col modal-enter">
					<div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between flex-shrink-0">
						<h4 id="scenario-modal-title" class="text-lg font-semibold text-gray-900">Scenario</h4>
						<button onclick="closeScenarioModal()" class="text-gray-500 hover:text-gray-700">
							<i class="fas fa-times"></i>
						</button>
				</div>
					<div class="p-6 overflow-y-auto flex-1">
						<div id="scenario-content">
							<!-- Scenario content will be loaded here -->
					</div>
				</div>
				</div>
			</div>

			<!-- Answer Review Modal -->
			<div id="answer-review-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
				<div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto modal-enter">
					<div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
						<h4 class="text-lg font-semibold text-gray-900">
							<i class="fas fa-check-circle mr-2"></i>Answer Review
						</h4>
						<button onclick="closeAnswerReviewModal()" class="text-gray-500 hover:text-gray-700">
							<i class="fas fa-times"></i>
						</button>
				</div>
					<div class="p-6">
						<div id="answer-review-content">
							<!-- Answer review content will be loaded here -->
					</div>
				</div>
				</div>
			</div>

			<!-- Create Scenario Modal -->
			<div id="create-scenario-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
				<div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col modal-enter">
					<div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between flex-shrink-0">
						<h4 class="text-lg font-semibold text-gray-900">
							<i class="fas fa-plus-circle mr-2"></i>Create New Training Scenario
						</h4>
						<button onclick="closeCreateScenarioModal()" class="text-gray-500 hover:text-gray-700">
							<i class="fas fa-times"></i>
						</button>
					</div>
					<div class="p-6 overflow-y-auto flex-1">
						<form id="create-scenario-form">
							<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
								<div>
									<label class="block text-sm font-medium text-gray-700 mb-2">Scenario Title</label>
									<input type="text" name="title" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
								</div>
								<?php if ($user_role === 'manager'): ?>
								<div>
									<label class="block text-sm font-medium text-gray-700 mb-2">Training Type</label>
									<select name="training_type" id="training-type-select" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500" onchange="updateScenarioTypes()">
										<option value="">Select Training Type</option>
										<option value="manager">Manager Training</option>
										<option value="housekeeping">Housekeeping Training</option>
									</select>
								</div>
								<?php endif; ?>
								<div>
									<label class="block text-sm font-medium text-gray-700 mb-2">Scenario Type</label>
									<select name="scenario_type" id="scenario-type-select" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
										<option value="">Select Type</option>
										<?php if ($user_role === 'manager'): ?>
											<!-- Manager scenario types will be populated by JavaScript -->
										<?php else: ?>
											<!-- Housekeeping scenario types -->
											<option value="room_inventory">Room Inventory</option>
											<option value="approval">Approval</option>
											<option value="inventory_management">Inventory Management</option>
										<?php endif; ?>
									</select>
								</div>
								<div>
									<label class="block text-sm font-medium text-gray-700 mb-2">Difficulty</label>
									<select name="difficulty" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
										<option value="">Select Difficulty</option>
										<option value="beginner">Beginner</option>
										<option value="intermediate">Intermediate</option>
										<option value="advanced">Advanced</option>
									</select>
								</div>
								<div>
									<label class="block text-sm font-medium text-gray-700 mb-2">Estimated Time (minutes)</label>
									<input type="number" name="estimated_time" min="5" max="60" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
								</div>
								<div>
									<label class="block text-sm font-medium text-gray-700 mb-2">Points</label>
									<input type="number" name="points" min="5" max="50" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
								</div>
								<div>
									<label class="block text-sm font-medium text-gray-700 mb-2">Number of Questions</label>
									<input type="number" name="question_count" min="2" max="10" value="3" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
								</div>
							</div>
							<div class="mt-6">
								<label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
								<textarea name="description" rows="3" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Describe what this scenario will teach..."></textarea>
							</div>
							<div class="mt-6">
								<label class="block text-sm font-medium text-gray-700 mb-2">Additional Context (Optional)</label>
								<textarea name="additional_context" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Any specific context or requirements for the AI to consider..."></textarea>
							</div>
							<div class="mt-6 flex justify-between">
								<button type="button" onclick="generateAIScenario()" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
									<i class="fas fa-robot mr-2"></i>Generate with AI
								</button>
								<div class="flex space-x-3">
									<button type="button" onclick="closeCreateScenarioModal()" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
										Cancel
									</button>
									<button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
										<i class="fas fa-save mr-2"></i>Create Scenario
									</button>
								</div>
							</div>
						</form>
						<div id="ai-generated-questions" class="mt-6 hidden">
							<h5 class="text-lg font-semibold text-gray-900 mb-4">AI Generated Questions</h5>
							<div id="generated-questions-content" class="max-h-96 overflow-y-auto border border-gray-200 rounded-lg p-4 bg-gray-50">
								<!-- Generated questions will be displayed here -->
							</div>
						</div>
				</div>
				</div>
			</div>
		</main>

		<!-- Include footer -->
		<?php include '../includes/pos-footer.php'; ?>
	</div>

<script>
let scenarios = [];
let currentScenario = null;
let currentAnswers = {};
let userProgress = {};
let currentTrainingType = '<?php echo $user_role; ?>'; // Default to user's role

$(document).ready(function() {
    loadScenarios();
    loadProgressSummary();
    loadTrainingHistory();
    
    // Filter event listeners
    $('#difficulty-filter, #type-filter').on('change', filterScenarios);
    
    // Initialize training type buttons for managers
    <?php if ($user_role === 'manager'): ?>
    updateTrainingTypeButtons();
    <?php endif; ?>
});

// Switch training type (Manager only)
function switchTrainingType(type) {
    currentTrainingType = type;
    loadScenarios();
    updateTrainingTypeButtons();
}

// Update training type button states
function updateTrainingTypeButtons() {
    if (currentTrainingType === 'manager') {
        $('#manager-training-btn').removeClass('border-gray-300 bg-gray-50 hover:bg-gray-100').addClass('border-blue-500 bg-blue-50 hover:bg-blue-100');
        $('#housekeeping-training-btn').removeClass('border-blue-500 bg-blue-50 hover:bg-blue-100').addClass('border-gray-300 bg-gray-50 hover:bg-gray-100');
        $('#scenarios-title').text('Manager Training Scenarios');
    } else {
        $('#housekeeping-training-btn').removeClass('border-gray-300 bg-gray-50 hover:bg-gray-100').addClass('border-blue-500 bg-blue-50 hover:bg-blue-100');
        $('#manager-training-btn').removeClass('border-blue-500 bg-blue-50 hover:bg-blue-100').addClass('border-gray-300 bg-gray-50 hover:bg-gray-100');
        $('#scenarios-title').text('Housekeeping Training Scenarios');
    }
}

function loadScenarios() {
    $.get('./api/get-inventory-scenarios-clean.php', {
        user_id: <?php echo $user_id; ?>,
        training_type: currentTrainingType
    })
        .done(function(response) {
            if (response.success) {
                scenarios = response.scenarios;
                loadUserProgress();
            } else {
                console.error('Error loading scenarios:', response.message);
            }
        })
        .fail(function() {
            console.error('Failed to load scenarios');
        });
}

// Load user progress for all scenarios
function loadUserProgress() {
    $.ajax({
        url: './api/get-inventory-training-history-clean.php',
        method: 'GET',
        data: { user_id: <?php echo $user_id; ?> },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.attempts && Array.isArray(response.attempts)) {
                // Process user progress data
                userProgress = {};
                response.attempts.forEach(attempt => {
                    const scenarioId = attempt.scenario_id;
                    if (!userProgress[scenarioId]) {
                        userProgress[scenarioId] = {
                            best_score: 0,
                            attempts: 0
                        };
                    }
                    
                    userProgress[scenarioId].attempts++;
                    if (attempt.score > userProgress[scenarioId].best_score) {
                        userProgress[scenarioId].best_score = attempt.score;
                    }
                });
                
                // Render scenarios with progress data
                renderScenarios();
            } else {
                // Render scenarios without progress data
                renderScenarios();
            }
        },
        error: function() {
            // Render scenarios without progress data
            renderScenarios();
        }
    });
}

function renderScenarios() {
    const grid = $('#scenarios-grid');
    grid.empty();
    
    scenarios.forEach(scenario => {
        const difficultyColors = {
            'beginner': 'bg-green-100 text-green-800',
            'intermediate': 'bg-yellow-100 text-yellow-800',
            'advanced': 'bg-red-100 text-red-800'
        };
        
        // Get user progress for this scenario
        const userProgress = getUserProgressForScenario(scenario.id);
        const bestScore = userProgress ? userProgress.best_score : 0;
        const attempts = userProgress ? userProgress.attempts : 0;
        const isPassed = bestScore >= 80; // 80% pass percentage
        const statusColor = isPassed ? 'text-green-600' : (attempts > 0 ? 'text-yellow-600' : 'text-gray-600');
        const statusText = isPassed ? 'Passed' : (attempts > 0 ? 'In Progress' : 'Not Started');
        
        const card = $(`
            <div class="scenario-card bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-all duration-300" data-scenario-id="${scenario.id}">
                <div class="flex items-start justify-between mb-4">
                    <h4 class="text-lg font-semibold text-gray-900">${scenario.title}</h4>
                    <span class="px-2 py-1 text-xs font-medium rounded-full ${difficultyColors[scenario.difficulty]}">
                        ${scenario.difficulty.charAt(0).toUpperCase() + scenario.difficulty.slice(1)}
                    </span>
                </div>
                <p class="text-sm text-gray-600 mb-4">${scenario.description}</p>
                
                <!-- Progress Section -->
                <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Progress</span>
                        <span class="text-sm ${statusColor} font-medium">${statusText}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: ${Math.min(bestScore, 100)}%"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500">
                        <span>Best Score: ${bestScore}%</span>
                        <span>Attempts: ${attempts}</span>
                        <span>Pass: 80%</span>
                    </div>
                </div>
                
                <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                    <span><i class="fas fa-clock mr-1"></i>${scenario.estimated_time} min</span>
                    <span><i class="fas fa-star mr-1"></i>${scenario.points} pts</span>
                    <span><i class="fas fa-question-circle mr-1"></i>${scenario.question_count} questions</span>
                </div>
                <div class="flex items-center justify-between">
                    <button onclick="startScenario(${scenario.id})" class="px-4 py-2 ${isPassed ? 'bg-green-600 hover:bg-green-700' : 'bg-purple-600 hover:bg-purple-700'} text-white rounded-md text-sm transition-colors">
                        <i class="fas fa-${isPassed ? 'redo' : 'play'} mr-2"></i>${isPassed ? 'Retake' : 'Start Training'}
                    </button>
                    <button onclick="previewScenario(${scenario.id})" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-50 transition-colors">
                        <i class="fas fa-eye mr-2"></i>Preview
                    </button>
                </div>
            </div>
        `);
        
        grid.append(card);
    });
}

function filterScenarios() {
    const difficulty = $('#difficulty-filter').val();
    const type = $('#type-filter').val();
    
    $('.scenario-card').each(function() {
        const scenarioId = $(this).data('scenario-id');
        const scenario = scenarios.find(s => s.id == scenarioId);
        
        let show = true;
        if (difficulty && scenario.difficulty !== difficulty) show = false;
        if (type && scenario.scenario_type !== type) show = false;
        
        $(this).toggle(show);
    });
}

function startScenario(scenarioId) {
    $.get(`./api/get-inventory-scenario-details.php?id=${scenarioId}`)
        .done(function(response) {
            if (response.success) {
                currentScenario = response.scenario;
                showScenarioModal(response.scenario, response.questions);
            } else {
                alert('Error loading scenario: ' + response.message);
            }
        })
        .fail(function() {
            alert('Failed to load scenario details');
        });
}

function previewScenario(scenarioId) {
    $.get(`./api/get-inventory-scenario-details.php?id=${scenarioId}`)
        .done(function(response) {
            if (response.success) {
                showScenarioPreview(response.scenario, response.questions);
            } else {
                alert('Error loading scenario: ' + response.message);
            }
        })
        .fail(function() {
            alert('Failed to load scenario details');
        });
}

function showScenarioModal(scenario, questions) {
    $('#scenario-modal-title').text(scenario.title);
    
    let content = `
        <div class="mb-6">
            <h5 class="text-lg font-semibold text-gray-900 mb-2">${scenario.title}</h5>
            <p class="text-gray-600 mb-4">${scenario.description}</p>
            <div class="flex items-center space-x-4 text-sm text-gray-500">
                <span><i class="fas fa-clock mr-1"></i>${scenario.estimated_time} minutes</span>
                <span><i class="fas fa-star mr-1"></i>${scenario.points} points</span>
                <span><i class="fas fa-question-circle mr-1"></i>${questions.length} questions</span>
            </div>
        </div>
        <form id="scenario-form">
    `;
    
    questions.forEach((question, index) => {
        content += `
            <div class="mb-4 p-4 border border-gray-200 rounded-lg bg-white shadow-sm">
                <h6 class="font-semibold text-gray-900 mb-3 text-base">Question ${index + 1}: ${question.question}</h6>
                <div class="space-y-2">
        `;
        
        question.options.forEach((option, optionIndex) => {
            content += `
                <label class="flex items-center p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer transition-colors">
                    <input type="radio" name="q${question.id}" value="${option.option_value}" class="mr-3" required>
                    <span class="w-6 h-6 bg-blue-100 text-blue-800 rounded-full flex items-center justify-center text-xs font-medium mr-3 flex-shrink-0">${String.fromCharCode(65 + optionIndex)}</span>
                    <span class="text-sm text-gray-700 flex-1">${option.option_text}</span>
                </label>
            `;
        });
        
        content += `
                </div>
            </div>
        `;
    });
    
    content += `
            <div class="flex justify-end space-x-4 mt-4">
                <button type="button" onclick="closeScenarioModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                    <i class="fas fa-check mr-2"></i>Submit Answers
                </button>
            </div>
        </form>
    `;
    
    $('#scenario-content').html(content);
    $('#scenario-modal').removeClass('hidden');
    
    // Force scrollbar to be visible
    setTimeout(() => {
        const scrollContainer = $('#scenario-modal .overflow-y-auto')[0];
        if (scrollContainer) {
            scrollContainer.style.overflowY = 'scroll';
            scrollContainer.style.scrollbarWidth = 'auto';
        }
    }, 100);
    
    // Handle form submission
    $('#scenario-form').on('submit', function(e) {
        e.preventDefault();
        submitScenario();
    });
}

function showScenarioPreview(scenario, questions) {
    $('#scenario-modal-title').text('Preview: ' + scenario.title);
    
    let content = `
        <div class="mb-6">
            <h5 class="text-lg font-semibold text-gray-900 mb-2">${scenario.title}</h5>
            <p class="text-gray-600 mb-4">${scenario.description}</p>
            <div class="flex items-center space-x-4 text-sm text-gray-500">
                <span><i class="fas fa-clock mr-1"></i>${scenario.estimated_time} minutes</span>
                <span><i class="fas fa-star mr-1"></i>${scenario.points} points</span>
                <span><i class="fas fa-question-circle mr-1"></i>${questions.length} questions</span>
            </div>
        </div>
        <div class="space-y-4">
    `;
    
    questions.forEach((question, index) => {
        content += `
            <div class="p-4 border border-gray-200 rounded-lg">
                <h6 class="font-semibold text-gray-900 mb-3">Question ${index + 1}: ${question.question}</h6>
                <div class="space-y-2">
        `;
        
        question.options.forEach((option, optionIndex) => {
            content += `
                <div class="flex items-center p-2 border border-gray-200 rounded">
                    <span class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center text-xs font-medium mr-3">${String.fromCharCode(65 + optionIndex)}</span>
                    <span class="text-sm">${option.option_text}</span>
                </div>
            `;
        });
        
        content += `
                </div>
            </div>
        `;
    });
    
    content += `
        </div>
        <div class="flex justify-end mt-6">
            <button onclick="closeScenarioModal()" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                <i class="fas fa-play mr-2"></i>Start Training
            </button>
        </div>
    `;
    
    $('#scenario-content').html(content);
    $('#scenario-modal').removeClass('hidden');
}

function submitScenario() {
    const formData = new FormData(document.getElementById('scenario-form'));
    const answers = {};
    
    for (let [key, value] of formData.entries()) {
        answers[key] = value;
    }
    
    const submitBtn = $('#scenario-form button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Submitting...').prop('disabled', true);
    
    $.ajax({
        url: './api/submit-inventory-scenario.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            scenario_id: currentScenario.id,
            answers: answers
        }),
        success: function(response) {
            if (response.success) {
                closeScenarioModal();
                showAnswerReview(response);
                loadProgressSummary();
                loadUserProgress(); // Refresh progress data
                loadTrainingHistory(); // Refresh training history
            } else {
                alert('Error submitting scenario: ' + response.message);
            }
        },
        error: function() {
            alert('Failed to submit scenario');
        },
        complete: function() {
            submitBtn.html(originalText).prop('disabled', false);
        }
    });
}

function showAnswerReview(response) {
    $('#answer-review-content').html(`
        <div class="text-center mb-6">
            <div class="text-4xl font-bold ${response.score >= 80 ? 'text-green-600' : 'text-red-600'} mb-2">
                ${response.score}%
            </div>
            <div class="text-lg text-gray-600 mb-4">${response.scenario_title}</div>
            ${response.certificate_earned ? '<div class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm font-medium"><i class="fas fa-certificate mr-2"></i>Certificate Earned!</div>' : ''}
        </div>
        <div class="space-y-4">
    `);
    
    response.questions.forEach((question, index) => {
        const userAnswer = response.user_answers['q' + question.id];
        const isCorrect = userAnswer === question.correct_answer;
        
        $('#answer-review-content').append(`
            <div class="p-4 border ${isCorrect ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50'} rounded-lg">
                <h6 class="font-semibold text-gray-900 mb-3">Question ${index + 1}: ${question.question}</h6>
                <div class="space-y-2">
        `);
        
        question.options.forEach((option, optionIndex) => {
            let optionClass = 'flex items-center p-2 border rounded';
            let icon = '';
            
            if (option.option_value === question.correct_answer) {
                optionClass += ' border-green-300 bg-green-100';
                icon = '<i class="fas fa-check-circle text-green-600 mr-2"></i>';
            } else if (option.option_value === userAnswer) {
                optionClass += ' border-red-300 bg-red-100';
                icon = '<i class="fas fa-times-circle text-red-600 mr-2"></i>';
			} else {
                optionClass += ' border-gray-200 bg-white';
            }
            
            $('#answer-review-content').append(`
                <div class="${optionClass}">
                    ${icon}
                    <span class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center text-xs font-medium mr-3">${String.fromCharCode(65 + optionIndex)}</span>
                    <span class="text-sm">${option.option_text}</span>
                    ${option.option_value === question.correct_answer ? '<span class="ml-auto text-xs font-medium text-green-600">Correct Answer</span>' : ''}
                </div>
            `);
        });
        
        $('#answer-review-content').append(`
                </div>
                ${question.explanation ? `<div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded text-sm text-blue-800"><strong>Explanation:</strong> ${question.explanation}</div>` : ''}
            </div>
        `);
    });
    
    $('#answer-review-content').append(`
        </div>
        <div class="flex justify-end mt-6">
            <button onclick="closeAnswerReviewModal()" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                <i class="fas fa-check mr-2"></i>Close
            </button>
        </div>
    `);
    
    $('#answer-review-modal').removeClass('hidden');
}

function loadTrainingHistory() {
    $.get('./api/get-inventory-training-history.php')
        .done(function(response) {
            if (response.success) {
                showTrainingHistory(response);
			} else {
                alert('Error loading training history: ' + response.message);
            }
        })
        .fail(function() {
            alert('Failed to load training history');
        });
}

function showTrainingHistory(data) {
    let content = `
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <div class="text-2xl font-bold text-blue-600">${data.stats.total_attempts}</div>
                <div class="text-sm text-blue-800">Total Attempts</div>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <div class="text-2xl font-bold text-green-600">${data.stats.completed_attempts}</div>
                <div class="text-sm text-green-800">Completed</div>
            </div>
            <div class="text-center p-4 bg-yellow-50 rounded-lg">
                <div class="text-2xl font-bold text-yellow-600">${data.stats.avg_score}%</div>
                <div class="text-sm text-yellow-800">Average Score</div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scenario</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
    `;
    
    data.attempts.forEach(attempt => {
        const scoreColor = attempt.score >= 80 ? 'text-green-600' : attempt.score >= 60 ? 'text-yellow-600' : 'text-red-600';
        const statusColor = attempt.status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
        
        content += `
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${attempt.scenario_title}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm ${scoreColor} font-medium">${attempt.score || 'N/A'}%</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusColor}">${attempt.status}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${new Date(attempt.started_at).toLocaleDateString()}</td>
            </tr>
        `;
    });
    
    content += `
                </tbody>
            </table>
        </div>
    `;
    
    $('#history-content').html(content);
    $('#history-modal').removeClass('hidden');
}

function loadProgressSummary() {
    $.get('./api/get-inventory-training-history-clean.php', {
        user_id: <?php echo $user_id; ?>
    })
        .done(function(response) {
            if (response.success) {
                $('#total-scenarios').text(scenarios.length);
                $('#completed-scenarios').text(response.stats.completed_attempts);
                $('#avg-score').text(response.stats.avg_score + '%');
                $('#certificates-earned').text(response.stats.certificates_earned);
            }
        });
}

function closeScenarioModal() {
    $('#scenario-modal').addClass('hidden');
}

function closeAnswerReviewModal() {
    $('#answer-review-modal').addClass('hidden');
}

function closeHistoryModal() {
    $('#history-modal').addClass('hidden');
}

// Mobile sidebar functions
function toggleSidebar() {
    $('#sidebar').toggleClass('sidebar-open');
    $('#sidebar-overlay').toggleClass('hidden');
}

function closeSidebar() {
    $('#sidebar').removeClass('sidebar-open');
    $('#sidebar-overlay').addClass('hidden');
}

// Create Scenario Modal Functions
function openCreateScenarioModal() {
    $('#create-scenario-modal').removeClass('hidden');
    // Clear form
    $('#create-scenario-form')[0].reset();
    $('#ai-generated-questions').addClass('hidden');
    window.generatedQuestions = [];
    
    // Initialize scenario types based on current training type
    <?php if ($user_role === 'manager'): ?>
    updateScenarioTypes();
    <?php endif; ?>
}

// Update scenario types based on training type selection
function updateScenarioTypes() {
    const trainingType = $('#training-type-select').val();
    const scenarioTypeSelect = $('#scenario-type-select');
    
    // Clear existing options
    scenarioTypeSelect.empty();
    scenarioTypeSelect.append('<option value="">Select Type</option>');
    
    if (trainingType === 'manager') {
        // Manager scenario types
        const managerTypes = [
            { value: 'inventory_management', text: 'Inventory Management' },
            { value: 'reporting', text: 'Reporting' },
            { value: 'automation', text: 'Automation' },
            { value: 'monitoring', text: 'Monitoring' },
            { value: 'approval', text: 'Approval' }
        ];
        
        managerTypes.forEach(type => {
            scenarioTypeSelect.append(`<option value="${type.value}">${type.text}</option>`);
        });
    } else if (trainingType === 'housekeeping') {
        // Housekeeping scenario types
        const housekeepingTypes = [
            { value: 'room_inventory', text: 'Room Inventory' },
            { value: 'approval', text: 'Approval' },
            { value: 'inventory_management', text: 'Inventory Management' }
        ];
        
        housekeepingTypes.forEach(type => {
            scenarioTypeSelect.append(`<option value="${type.value}">${type.text}</option>`);
        });
    }
}

function closeCreateScenarioModal() {
    $('#create-scenario-modal').addClass('hidden');
    $('#ai-generated-questions').addClass('hidden');
    window.generatedQuestions = [];
}

// Helper function to get user progress for a specific scenario
function getUserProgressForScenario(scenarioId) {
    return userProgress[scenarioId] || { best_score: 0, attempts: 0 };
}

// Load training history
function loadTrainingHistory() {
    $.ajax({
        url: './api/get-inventory-training-history-clean.php',
        method: 'GET',
        data: { user_id: <?php echo $user_id; ?> },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.attempts && Array.isArray(response.attempts)) {
                displayTrainingHistory(response.attempts);
            } else {
                $('#training-history-content').html('<div class="text-center py-8 text-gray-500"><i class="fas fa-exclamation-circle text-2xl mb-2"></i><p>No training history found</p></div>');
            }
        },
        error: function() {
            $('#training-history-content').html('<div class="text-center py-8 text-gray-500"><i class="fas fa-exclamation-circle text-2xl mb-2"></i><p>Failed to load training history</p></div>');
        }
    });
}

// Display training history
function displayTrainingHistory(history) {
    if (!history || history.length === 0) {
        $('#training-history-content').html('<div class="text-center py-8 text-gray-500"><i class="fas fa-history text-2xl mb-2"></i><p>No training attempts yet</p></div>');
			return;
		}
    
    let historyHtml = '<div class="space-y-4">';
    
    history.forEach(attempt => {
        const isPassed = attempt.score >= 80;
        const statusColor = isPassed ? 'text-green-600' : 'text-red-600';
        const statusText = isPassed ? 'Passed' : 'Failed';
        const statusIcon = isPassed ? 'fa-check-circle' : 'fa-times-circle';
        
        historyHtml += `
            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-semibold text-gray-900">${attempt.scenario_title}</h4>
                    <div class="flex items-center space-x-2">
                        <span class="px-2 py-1 text-xs font-medium rounded-full ${isPassed ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            <i class="fas ${statusIcon} mr-1"></i>${statusText}
                        </span>
                        <span class="text-sm font-medium ${statusColor}">${attempt.score}%</span>
                    </div>
                </div>
                <div class="flex items-center justify-between text-sm text-gray-500">
                    <span><i class="fas fa-calendar mr-1"></i>${new Date(attempt.completed_at).toLocaleDateString()}</span>
                    <span><i class="fas fa-clock mr-1"></i>${new Date(attempt.completed_at).toLocaleTimeString()}</span>
                    <span><i class="fas fa-trophy mr-1"></i>${attempt.points_earned || 0} points</span>
                </div>
            </div>
        `;
    });
    
    historyHtml += '</div>';
    $('#training-history-content').html(historyHtml);
}

function generateAIScenario() {
    const formData = new FormData(document.getElementById('create-scenario-form'));
    const scenarioData = {};
    
    for (let [key, value] of formData.entries()) {
        scenarioData[key] = value;
    }
    
    if (!scenarioData.title || !scenarioData.description) {
        alert('Please fill in the title and description before generating questions.');
        return;
    }
    
    <?php if ($user_role === 'manager'): ?>
    if (!scenarioData.training_type) {
        alert('Please select a training type before generating questions.');
        return;
    }
    <?php else: ?>
    // For housekeeping users, set training type to housekeeping
    scenarioData.training_type = 'housekeeping';
    <?php endif; ?>
    
    const generateBtn = $('button[onclick="generateAIScenario()"]');
    const originalText = generateBtn.html();
    generateBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Generating...').prop('disabled', true);
    
    // Add user_id to the data
    scenarioData.user_id = <?php echo $user_id; ?>;
    
    $.ajax({
        url: './api/generate-inventory-ai-scenario.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(scenarioData),
        success: function(response) {
            if (response.success) {
                displayGeneratedQuestions(response.questions);
                window.generatedQuestions = response.questions;
			} else {
                alert('Error generating questions: ' + response.message);
            }
        },
        error: function() {
            alert('Failed to generate questions');
        },
        complete: function() {
            generateBtn.html(originalText).prop('disabled', false);
        }
    });
}

function displayGeneratedQuestions(questions) {
    let content = '';
    
    questions.forEach((question, index) => {
        content += `
            <div class="mb-6 p-4 border border-gray-200 rounded-lg bg-white shadow-sm">
                <h6 class="font-semibold text-gray-900 mb-4 text-base">Question ${index + 1}: ${question.question}</h6>
                <div class="space-y-3">
        `;
        
        question.options.forEach((option, optionIndex) => {
            content += `
                <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <span class="w-7 h-7 bg-blue-100 text-blue-800 rounded-full flex items-center justify-center text-sm font-medium mr-4 flex-shrink-0">${String.fromCharCode(65 + optionIndex)}</span>
                    <span class="text-sm text-gray-700 flex-1">${option.option_text}</span>
                    ${option.is_correct ? '<span class="ml-3 text-xs font-medium text-green-600 bg-green-100 px-2 py-1 rounded-full flex-shrink-0">Correct Answer</span>' : ''}
                </div>
            `;
        });
        
        content += `
                </div>
                ${question.explanation ? `<div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded text-sm text-blue-800"><strong>Explanation:</strong> ${question.explanation}</div>` : ''}
            </div>
        `;
    });
    
    $('#generated-questions-content').html(content);
    $('#ai-generated-questions').removeClass('hidden');
}

// Form submission handler
$(document).ready(function() {
    $('#create-scenario-form').on('submit', function(e) {
        e.preventDefault();
        
        if (window.generatedQuestions.length === 0) {
            alert('Please generate questions first using the AI generator.');
			return;
		}
        
        const formData = new FormData(this);
        const scenarioData = {};
        
        for (let [key, value] of formData.entries()) {
            scenarioData[key] = value;
        }
        
        <?php if ($user_role === 'housekeeping'): ?>
        // For housekeeping users, set training type to housekeeping
        scenarioData.training_type = 'housekeeping';
        <?php endif; ?>
        
        scenarioData.questions = window.generatedQuestions;
        scenarioData.user_id = <?php echo $user_id; ?>;
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Creating...').prop('disabled', true);
        
        $.ajax({
            url: './api/create-inventory-scenario.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(scenarioData),
            success: function(response) {
                if (response.success) {
                    alert('Scenario created successfully!');
                    closeCreateScenarioModal();
                    loadScenarios(); // Reload scenarios
			} else {
                    alert('Error creating scenario: ' + response.message);
                }
            },
            error: function() {
                alert('Failed to create scenario');
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
});
</script>
</body>
</html>