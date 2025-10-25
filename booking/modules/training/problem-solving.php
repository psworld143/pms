<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';

// Get filter parameters
$severity = $_GET['severity'] ?? null;
$difficulty = $_GET['difficulty'] ?? null;

// Get problem scenarios based on filters
$scenarios = getProblemScenarios($severity);

// Get user progress for each scenario
$user_progress = [];
try {
    $stmt = $pdo->prepare("
        SELECT scenario_id, MAX(score) as best_score, COUNT(*) as attempts
        FROM training_attempts 
        WHERE user_id = ? AND scenario_type = 'problem'
        GROUP BY scenario_id
    ");
    $stmt->execute([$user_id]);
    $progress_data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    foreach ($progress_data as $scenario_id => $data) {
        $user_progress[$scenario_id] = $data;
    }
} catch (Exception $e) {
    // Handle error silently
}

$page_title = 'Problem Solving Training';
include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';
?>

<!-- Main Content -->
<main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Problem Solving Training</h1>
                <p class="text-gray-600 mt-1">Develop critical thinking skills with real-world hotel problem scenarios</p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <p class="text-sm text-gray-500">Welcome back,</p>
                    <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($user_name); ?></p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center">
                    <i class="fas fa-lightbulb text-white text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Completed</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo count($user_progress); ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-star text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Avg Score</p>
                    <p class="text-2xl font-bold text-gray-900">
                        <?php 
                        $avg_score = count($user_progress) > 0 ? 
                            round(array_sum(array_column($user_progress, 'best_score')) / count($user_progress)) : 0;
                        echo $avg_score;
                        ?>%
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-trophy text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Certificates</p>
                    <p class="text-2xl font-bold text-gray-900">
                        <?php 
                        $certificates = 0;
                        foreach ($user_progress as $progress) {
                            if ($progress['best_score'] >= 75) $certificates++;
                        }
                        echo $certificates;
                        ?>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Time Spent</p>
                    <p class="text-2xl font-bold text-gray-900">
                        <?php 
                        $total_time = count($user_progress) * 20; // 20 minutes per scenario
                        echo round($total_time / 60, 1);
                        ?>h
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Filter Scenarios</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Severity Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Severity Level</label>
                <select id="severity-filter" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">All Severities</option>
                    <option value="low" <?php echo $severity === 'low' ? 'selected' : ''; ?>>Low Priority</option>
                    <option value="medium" <?php echo $severity === 'medium' ? 'selected' : ''; ?>>Medium Priority</option>
                    <option value="high" <?php echo $severity === 'high' ? 'selected' : ''; ?>>High Priority</option>
                    <option value="critical" <?php echo $severity === 'critical' ? 'selected' : ''; ?>>Critical</option>
                </select>
            </div>

            <!-- Difficulty Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Difficulty</label>
                <select id="difficulty-filter" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">All Levels</option>
                    <option value="beginner" <?php echo $difficulty === 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                    <option value="intermediate" <?php echo $difficulty === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                    <option value="advanced" <?php echo $difficulty === 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                </select>
            </div>

            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" id="search-input" placeholder="Search scenarios..." 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            </div>
        </div>
    </div>

    <!-- Scenarios Grid -->
    <div id="scenarios-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (empty($scenarios)): ?>
            <div class="col-span-full text-center py-12">
                <i class="fas fa-lightbulb text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No scenarios found</h3>
                <p class="text-gray-500">Try adjusting your filters or check back later for new content.</p>
            </div>
        <?php else: ?>
            <?php foreach ($scenarios as $scenario): ?>
                <div class="bg-white rounded-lg shadow-sm border hover:shadow-md transition-all duration-300 scenario-card" 
                     data-severity="<?php echo htmlspecialchars($scenario['severity']); ?>"
                     data-difficulty="<?php echo htmlspecialchars($scenario['difficulty']); ?>"
                     data-title="<?php echo htmlspecialchars(strtolower($scenario['title'])); ?>">
                    
                    <!-- Scenario Header -->
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($scenario['title']); ?></h3>
                                <p class="text-sm text-gray-600 mb-3"><?php echo htmlspecialchars($scenario['description']); ?></p>
                            </div>
                            <div class="flex flex-col items-end space-y-2">
                                <!-- Severity Badge -->
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    <?php 
                                    switch($scenario['severity']) {
                                        case 'low': echo 'bg-green-100 text-green-800'; break;
                                        case 'medium': echo 'bg-yellow-100 text-yellow-800'; break;
                                        case 'high': echo 'bg-orange-100 text-orange-800'; break;
                                        case 'critical': echo 'bg-red-100 text-red-800'; break;
                                        default: echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?php echo ucfirst($scenario['severity']); ?>
                                </span>
                                
                                <!-- Difficulty Badge -->
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    <?php 
                                    switch($scenario['difficulty']) {
                                        case 'beginner': echo 'bg-green-100 text-green-800'; break;
                                        case 'intermediate': echo 'bg-yellow-100 text-yellow-800'; break;
                                        case 'advanced': echo 'bg-red-100 text-red-800'; break;
                                        default: echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?php echo ucfirst($scenario['difficulty']); ?>
                                </span>
                            </div>
                        </div>

                        <!-- Scenario Stats -->
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <p class="text-2xl font-bold text-purple-600"><?php echo $scenario['points']; ?></p>
                                <p class="text-xs text-gray-500">Points</p>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-blue-600"><?php echo $scenario['time_limit']; ?>m</p>
                                <p class="text-xs text-gray-500">Time Limit</p>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-green-600"><?php echo $scenario['attempt_count'] ?? 0; ?></p>
                                <p class="text-xs text-gray-500">Attempts</p>
                            </div>
                        </div>
                    </div>

                    <!-- User Progress -->
                    <div class="p-6">
                        <?php if (isset($user_progress[$scenario['id']])): ?>
                            <div class="mb-4">
                                <div class="flex justify-between text-sm text-gray-600 mb-2">
                                    <span>Your Progress</span>
                                    <span><?php echo $user_progress[$scenario['id']]['best_score']; ?>% Best Score</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-purple-500 to-pink-500 h-2 rounded-full" 
                                         style="width: <?php echo min(100, $user_progress[$scenario['id']]['best_score']); ?>%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1"><?php echo $user_progress[$scenario['id']]['attempts']; ?> attempt(s)</p>
                            </div>
                        <?php else: ?>
                            <div class="mb-4">
                                <div class="flex justify-between text-sm text-gray-600 mb-2">
                                    <span>Your Progress</span>
                                    <span>Not Started</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-gray-300 h-2 rounded-full" style="width: 0%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Ready to start</p>
                            </div>
                        <?php endif; ?>

                        <!-- Action Buttons -->
                        <div class="flex space-x-3">
                            <button onclick="startProblemScenario(<?php echo $scenario['id']; ?>)" 
                                    class="flex-1 bg-gradient-to-r from-purple-600 to-pink-600 text-white px-4 py-2 rounded-md hover:from-purple-700 hover:to-pink-700 transition-all duration-300 font-medium">
                                <i class="fas fa-play mr-2"></i>
                                <?php echo isset($user_progress[$scenario['id']]) ? 'Retake' : 'Start'; ?>
                            </button>
                            <button onclick="previewProblemScenario(<?php echo $scenario['id']; ?>)" 
                                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<!-- Problem Preview Modal -->
<div id="problem-preview-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-8 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-900" id="problem-preview-title">Scenario Preview</h3>
            <button onclick="closeProblemPreviewModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div id="problem-preview-content">
            <!-- Preview content will be loaded here -->
        </div>
        
        <div class="flex justify-end space-x-4 mt-6">
            <button onclick="closeProblemPreviewModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                Close
            </button>
            <button onclick="startProblemScenarioFromPreview()" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-md hover:from-purple-700 hover:to-pink-700 transition-all duration-300">
                Start Scenario
            </button>
        </div>
    </div>
</div>

<!-- Problem Solving Training Modal -->
<div id="problem-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-8 max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-900" id="problem-title">Problem Scenario</h3>
            <div class="flex items-center space-x-4">
                <div class="text-sm text-gray-600">
                    <i class="fas fa-clock mr-1"></i>
                    <span id="problem-timer">20:00</span>
                </div>
                <button onclick="closeProblemModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <div id="problem-content">
            <!-- Scenario content will be loaded here -->
        </div>
        
        <div class="flex justify-between items-center mt-6 pt-6 border-t border-gray-200">
            <button onclick="requestHint()" class="px-4 py-2 border border-yellow-300 text-yellow-700 rounded-md hover:bg-yellow-50 transition-colors">
                <i class="fas fa-lightbulb mr-2"></i>Hint
            </button>
            <button onclick="submitProblem()" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-md hover:from-purple-700 hover:to-pink-700 transition-all duration-300">
                <i class="fas fa-check mr-2"></i>Submit Solution
            </button>
        </div>
    </div>
</div>

<script>
let currentProblemScenario = null;
let problemTimer = null;
let problemTimeLeft = 0;

// Filter functionality
document.getElementById('severity-filter').addEventListener('change', filterScenarios);
document.getElementById('difficulty-filter').addEventListener('change', filterScenarios);
document.getElementById('search-input').addEventListener('input', searchScenarios);

function filterScenarios() {
    const severity = document.getElementById('severity-filter').value;
    const difficulty = document.getElementById('difficulty-filter').value;
    
    // Update URL parameters
    const url = new URL(window.location);
    if (severity) url.searchParams.set('severity', severity);
    else url.searchParams.delete('severity');
    if (difficulty) url.searchParams.set('difficulty', difficulty);
    else url.searchParams.delete('difficulty');
    
    window.history.pushState({}, '', url);
    window.location.reload();
}

function searchScenarios() {
    const searchTerm = document.getElementById('search-input').value.toLowerCase();
    const cards = document.querySelectorAll('.scenario-card');
    
    cards.forEach(card => {
        const title = card.dataset.title;
        const description = card.querySelector('p').textContent.toLowerCase();
        
        if (title.includes(searchTerm) || description.includes(searchTerm)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function startProblemScenario(scenarioId) {
    currentProblemScenario = scenarioId;
    
    fetch(`../../api/training/get-problem-details.php?id=${scenarioId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                openProblemModal(data.item);
            } else {
                alert('Error loading scenario: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error loading scenario:', error);
            alert('Error loading scenario');
        });
}

function previewProblemScenario(scenarioId) {
    fetch(`../../api/training/get-problem-details.php?id=${scenarioId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                openProblemPreviewModal(data.item);
            } else {
                alert('Error loading scenario preview');
            }
        })
        .catch(error => {
            console.error('Error loading scenario preview:', error);
            alert('Error loading scenario preview');
        });
}

function openProblemPreviewModal(scenario) {
    document.getElementById('problem-preview-title').textContent = scenario.title;
    document.getElementById('problem-preview-content').innerHTML = `
        <div class="space-y-6">
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <h4 class="font-medium text-red-900 mb-2">Problem Description</h4>
                <p class="text-red-800">${scenario.description}</p>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">Severity</h4>
                    <p class="text-gray-700">${scenario.severity}</p>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">Difficulty</h4>
                    <p class="text-gray-700">${scenario.difficulty}</p>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">Time Limit</h4>
                    <p class="text-gray-700">${scenario.time_limit} minutes</p>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">Points</h4>
                    <p class="text-gray-700">${scenario.points}</p>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('problem-preview-modal').classList.remove('hidden');
}

function closeProblemPreviewModal() {
    document.getElementById('problem-preview-modal').classList.add('hidden');
}

function startProblemScenarioFromPreview() {
    closeProblemPreviewModal();
    startProblemScenario(currentProblemScenario);
}

function openProblemModal(scenario) {
    if (!scenario) {
        console.error('Problem scenario is undefined');
        alert('Error: Scenario data is missing');
        return;
    }
    
    window.currentProblemScenario = scenario.id;
    document.getElementById('problem-title').textContent = scenario.title || 'Problem Scenario';
    document.getElementById('problem-severity').textContent = scenario.severity || 'medium';
    
    document.getElementById('problem-content').innerHTML = `
        <div class="space-y-6">
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <h4 class="font-medium text-red-900 mb-2">Problem Description</h4>
                <p class="text-red-800">${scenario.description || 'No description available'}</p>
            </div>
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <h4 class="font-medium text-yellow-900 mb-2">Instructions</h4>
                <p class="text-yellow-800">Analyze the problem and provide a comprehensive solution. Consider the impact, urgency, and available options before proposing your approach.</p>
            </div>
            
            <div class="space-y-4">
                <label class="block">
                    <span class="text-gray-700 font-medium">Your Solution:</span>
                    <textarea id="problem-solution" rows="6" 
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500"
                              placeholder="Describe your solution approach here..."></textarea>
                </label>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-medium text-blue-900 mb-2">Problem-Solving Framework:</h4>
                    <ul class="text-blue-800 text-sm space-y-1">
                        <li>• Identify the root cause of the problem</li>
                        <li>• Consider multiple solution approaches</li>
                        <li>• Evaluate pros and cons of each option</li>
                        <li>• Choose the most effective solution</li>
                        <li>• Plan implementation steps</li>
                    </ul>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('problem-modal').classList.remove('hidden');
    startProblemTimer();
}

function startProblemTimer() {
    problemTimeLeft = 20 * 60; // 20 minutes in seconds
    problemTimer = setInterval(() => {
        problemTimeLeft--;
        const minutes = Math.floor(problemTimeLeft / 60);
        const seconds = problemTimeLeft % 60;
        document.getElementById('problem-timer').textContent = 
            `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        
        if (problemTimeLeft <= 0) {
            stopProblemTimer();
            alert('Time is up!');
        }
    }, 1000);
}

function stopProblemTimer() {
    if (problemTimer) {
        clearInterval(problemTimer);
        problemTimer = null;
    }
}

function requestHint() {
    alert('Hint: Consider the guest\'s perspective and hotel policies');
}

function submitProblem() {
    const solution = document.getElementById('problem-solution').value;
    
    if (!solution.trim()) {
        alert('Please provide a solution');
        return;
    }
    
    fetch('../../api/training/submit-problem.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({
            scenario_id: currentProblemScenario,
            solution: solution
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Solution submitted! Score: ${data.score}%`);
            closeProblemModal();
            location.reload(); // Refresh to show updated progress
        } else {
            alert('Error submitting solution: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error submitting solution:', error);
        alert('Error submitting solution');
    });
}

function closeProblemModal() {
    stopProblemTimer();
    document.getElementById('problem-modal').classList.add('hidden');
}
</script>

<?php include '../../includes/footer.php'; ?>