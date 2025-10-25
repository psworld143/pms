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

// Ensure inventory training tables exist
try {
	global $pdo;
	
	// Create inventory training scenarios table
	$pdo->exec("CREATE TABLE IF NOT EXISTS inventory_training_scenarios (
		id INT AUTO_INCREMENT PRIMARY KEY,
		title VARCHAR(255) NOT NULL,
		description TEXT,
		scenario_type VARCHAR(100) NOT NULL,
		difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
		estimated_time INT DEFAULT 15,
		points INT DEFAULT 10,
		question_count INT DEFAULT 0,
		created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
	
	// Create inventory scenario questions table
	$pdo->exec("CREATE TABLE IF NOT EXISTS inventory_scenario_questions (
		id INT AUTO_INCREMENT PRIMARY KEY,
		scenario_id INT NOT NULL,
		question TEXT NOT NULL,
		question_order INT DEFAULT 1,
		correct_answer TEXT NOT NULL,
		created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
		FOREIGN KEY (scenario_id) REFERENCES inventory_training_scenarios(id) ON DELETE CASCADE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
	
	// Create inventory question options table
	$pdo->exec("CREATE TABLE IF NOT EXISTS inventory_question_options (
		id INT AUTO_INCREMENT PRIMARY KEY,
		question_id INT NOT NULL,
		option_text TEXT NOT NULL,
		option_value VARCHAR(255) NOT NULL,
		option_order INT DEFAULT 1,
		created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
		FOREIGN KEY (question_id) REFERENCES inventory_scenario_questions(id) ON DELETE CASCADE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
	
	// Create inventory training attempts table
	$pdo->exec("CREATE TABLE IF NOT EXISTS inventory_training_attempts (
		id INT AUTO_INCREMENT PRIMARY KEY,
		user_id INT NOT NULL,
		scenario_id INT NOT NULL,
		scenario_type VARCHAR(100) NOT NULL,
		status ENUM('in_progress', 'completed') DEFAULT 'in_progress',
		score INT DEFAULT 0,
		duration_minutes INT DEFAULT 0,
		answers JSON,
		started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
		completed_at TIMESTAMP NULL,
		FOREIGN KEY (scenario_id) REFERENCES inventory_training_scenarios(id) ON DELETE CASCADE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
	
	// Create inventory training certificates table
	$pdo->exec("CREATE TABLE IF NOT EXISTS inventory_training_certificates (
		id INT AUTO_INCREMENT PRIMARY KEY,
		user_id INT NOT NULL,
		certificate_name VARCHAR(255) NOT NULL,
		certificate_type VARCHAR(100) NOT NULL,
		score INT NOT NULL,
		earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
		status ENUM('earned', 'revoked') DEFAULT 'earned'
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
	
} catch (Throwable $e) {
	// Silent fail; UI will still render
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Training & Simulations - Hotel PMS Inventory</title>
	<script src="https://cdn.tailwindcss.com"></script>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
	<style>
		#sidebar { transition: transform 0.3s ease-in-out; }
		@media (max-width: 1023px) { #sidebar { transform: translateX(-100%); z-index: 50; } #sidebar.sidebar-open { transform: translateX(0); } }
		@media (min-width: 1024px) { #sidebar { transform: translateX(0) !important; } }
		#sidebar-overlay { transition: opacity 0.3s ease-in-out; z-index: 40; }
		.main-content { margin-left: 0; padding-top: 4rem; }
		@media (min-width: 1024px) { .main-content { margin-left: 16rem; } }
		
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
		<main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300 overflow-y-auto max-h-screen">
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

			<!-- Scenarios Grid -->
			<div class="bg-white rounded-lg shadow p-6 mb-6">
				<div class="flex items-center justify-between mb-6">
					<h3 class="text-lg font-semibold text-gray-800">
						<i class="fas fa-theater-masks mr-2 text-gray-600"></i>Training Scenarios
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
						<h4 class="text-lg font-semibold text-gray-900">Answer Review</h4>
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
		</main>

		<!-- Include footer -->
		<?php include '../includes/pos-footer.php'; ?>
	</div>

<script>
let scenarios = [];
let currentScenario = null;
let currentAnswers = {};
let userProgress = {};

$(document).ready(function() {
    loadScenarios();
    loadProgressSummary();
    loadTrainingHistory();
    
    // Filter event listeners
    $('#difficulty-filter, #type-filter').on('change', filterScenarios);
});

function loadScenarios() {
    $.get('./api/get-inventory-scenarios-clean.php', {
        user_id: <?php echo $user_id; ?>,
        training_type: 'housekeeping'
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
        const userProgressData = getUserProgressForScenario(scenario.id);
        const bestScore = userProgressData ? userProgressData.best_score : 0;
        const attempts = userProgressData ? userProgressData.attempts : 0;
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
        const card = $(this);
        const scenarioId = card.data('scenario-id');
        const scenario = scenarios.find(s => s.id == scenarioId);
        
        if (!scenario) return;
        
        const difficultyMatch = !difficulty || scenario.difficulty === difficulty;
        const typeMatch = !type || scenario.scenario_type === type;
        
        if (difficultyMatch && typeMatch) {
            card.show();
			} else {
            card.hide();
        }
    });
}

function startScenario(scenarioId) {
    currentScenario = scenarios.find(s => s.id == scenarioId);
    if (!currentScenario) return;
    
    $.ajax({
        url: './api/get-inventory-scenario-details.php',
        method: 'GET',
        data: { id: scenarioId, user_id: <?php echo $user_id; ?> },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showScenarioModal(response.scenario, response.questions);
            } else {
                alert('Failed to load scenario details');
            }
        },
        error: function() {
            alert('Failed to load scenario details');
        }
    });
}

function previewScenario(scenarioId) {
    const scenario = scenarios.find(s => s.id == scenarioId);
    if (!scenario) return;
    
    $.ajax({
        url: './api/get-inventory-scenario-details.php',
        method: 'GET',
        data: { id: scenarioId, user_id: <?php echo $user_id; ?> },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showScenarioPreview(response.scenario, response.questions);
            } else {
                alert('Failed to load scenario details');
            }
        },
        error: function() {
            alert('Failed to load scenario details');
        }
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
            <div class="p-4 border border-gray-200 rounded-lg bg-gray-50">
                <h6 class="font-semibold text-gray-900 mb-3">Question ${index + 1}: ${question.question}</h6>
                <div class="space-y-2">
        `;
        
        question.options.forEach((option, optionIndex) => {
            content += `
                <div class="flex items-center p-2 border border-gray-200 rounded bg-white">
                    <span class="w-6 h-6 bg-blue-100 text-blue-800 rounded-full flex items-center justify-center text-xs font-medium mr-3 flex-shrink-0">${String.fromCharCode(65 + optionIndex)}</span>
                    <span class="text-sm text-gray-700">${option.option_text}</span>
                </div>
            `;
        });
        
        content += `
                </div>
            </div>
        `;
    });
    
    content += `
            <div class="flex justify-end mt-6">
                <button onclick="closeScenarioModal()" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                    Close Preview
                </button>
            </div>
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
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Training Complete!</h3>
            <div class="text-4xl font-bold ${response.score >= 80 ? 'text-green-600' : 'text-red-600'} mb-2">
                ${response.score}%
            </div>
            <p class="text-gray-600">${response.score >= 80 ? 'Congratulations! You passed!' : 'Keep practicing to improve your score.'}</p>
        </div>
        <div class="space-y-4">
            ${response.questions.map((question, index) => `
                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 mb-3">Question ${index + 1}: ${question.question}</h4>
                    <div class="space-y-2">
                        ${question.options.map(option => `
                            <div class="flex items-center p-2 rounded ${
                                option.option_value === question.correct_answer ? 'bg-green-100 border-green-300' :
                                option.option_value === response.user_answers[`q${question.id}`] ? 'bg-red-100 border-red-300' :
                                'bg-gray-50 border-gray-200'
                            } border">
                                <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-medium mr-3 ${
                                    option.option_value === question.correct_answer ? 'bg-green-500 text-white' :
                                    option.option_value === response.user_answers[`q${question.id}`] ? 'bg-red-500 text-white' :
                                    'bg-gray-200 text-gray-600'
                                }">${String.fromCharCode(65 + question.options.indexOf(option))}</span>
                                <span class="text-sm">${option.option_text}</span>
                                ${option.option_value === question.correct_answer ? '<span class="ml-auto text-green-600 font-medium">Correct</span>' : ''}
                                ${option.option_value === response.user_answers[`q${question.id}`] && option.option_value !== question.correct_answer ? '<span class="ml-auto text-red-600 font-medium">Your Answer</span>' : ''}
                            </div>
                        `).join('')}
                    </div>
                </div>
            `).join('')}
        </div>
    `);
    
    $('#answer-review-modal').removeClass('hidden');
}

function closeScenarioModal() {
    $('#scenario-modal').addClass('hidden');
}

function closeAnswerReviewModal() {
    $('#answer-review-modal').addClass('hidden');
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

function loadProgressSummary() {
    $.get('./api/get-inventory-training-history-clean.php', {
        user_id: <?php echo $user_id; ?>
    })
        .done(function(response) {
            if (response.success) {
                $('#total-scenarios').text(response.stats.total_attempts || 0);
                $('#completed-scenarios').text(response.stats.completed_attempts || 0);
                $('#avg-score').text((response.stats.avg_score || 0) + '%');
                $('#certificates-earned').text(response.stats.certificates_earned || 0);
            }
        })
        .fail(function() {
            console.error('Failed to load progress summary');
        });
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
</script>
</body>
</html>