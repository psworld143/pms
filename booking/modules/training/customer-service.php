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
$type = $_GET['type'] ?? null;
$difficulty = $_GET['difficulty'] ?? null;

// Get customer service scenarios based on filters
$scenarios = getCustomerServiceScenarios($type);

// Get user progress for each scenario
$user_progress = [];
try {
    $stmt = $pdo->prepare("
        SELECT scenario_id, MAX(score) as best_score, COUNT(*) as attempts
        FROM training_attempts 
        WHERE user_id = ? AND scenario_type = 'customer_service'
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

$page_title = 'Customer Service Training';
include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';
?>

<!-- Main Content -->
<main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Customer Service Training</h1>
                <p class="text-gray-600 mt-1">Master the art of exceptional customer service with interactive scenarios</p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <p class="text-sm text-gray-500">Welcome back,</p>
                    <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($user_name); ?></p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-500 rounded-full flex items-center justify-center">
                    <i class="fas fa-headset text-white text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
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
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-trophy text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Certificates</p>
                    <p class="text-2xl font-bold text-gray-900">
                        <?php 
                        $certificates = 0;
                        foreach ($user_progress as $progress) {
                            if ($progress['best_score'] >= 70) $certificates++;
                        }
                        echo $certificates;
                        ?>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Time Spent</p>
                    <p class="text-2xl font-bold text-gray-900">
                        <?php 
                        $total_time = count($user_progress) * 10; // 10 minutes per scenario
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
            <!-- Type Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Scenario Type</label>
                <select id="type-filter" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">All Types</option>
                    <option value="complaints" <?php echo $type === 'complaints' ? 'selected' : ''; ?>>Complaints</option>
                    <option value="requests" <?php echo $type === 'requests' ? 'selected' : ''; ?>>Special Requests</option>
                    <option value="emergencies" <?php echo $type === 'emergencies' ? 'selected' : ''; ?>>Emergencies</option>
                </select>
            </div>

            <!-- Difficulty Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Difficulty</label>
                <select id="difficulty-filter" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
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
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>
        </div>
    </div>

    <!-- Scenarios Grid -->
    <div id="scenarios-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (empty($scenarios)): ?>
            <div class="col-span-full text-center py-12">
                <i class="fas fa-headset text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No scenarios found</h3>
                <p class="text-gray-500">Try adjusting your filters or check back later for new content.</p>
            </div>
        <?php else: ?>
            <?php foreach ($scenarios as $scenario): ?>
                <div class="bg-white rounded-lg shadow-sm border hover:shadow-md transition-all duration-300 scenario-card" 
                     data-type="<?php echo htmlspecialchars($scenario['type']); ?>"
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
                                <!-- Type Badge -->
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    <?php 
                                    switch($scenario['type']) {
                                        case 'complaints': echo 'bg-red-100 text-red-800'; break;
                                        case 'requests': echo 'bg-blue-100 text-blue-800'; break;
                                        case 'emergencies': echo 'bg-orange-100 text-orange-800'; break;
                                        default: echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?php echo ucfirst($scenario['type']); ?>
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
                                <p class="text-2xl font-bold text-green-600"><?php echo $scenario['points']; ?></p>
                                <p class="text-xs text-gray-500">Points</p>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-blue-600"><?php echo $scenario['estimated_time']; ?>m</p>
                                <p class="text-xs text-gray-500">Duration</p>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-purple-600"><?php echo $scenario['attempt_count'] ?? 0; ?></p>
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
                                    <div class="bg-gradient-to-r from-green-500 to-emerald-500 h-2 rounded-full" 
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
                            <button onclick="startCustomerService(<?php echo $scenario['id']; ?>)" 
                                    class="flex-1 bg-gradient-to-r from-green-600 to-emerald-600 text-white px-4 py-2 rounded-md hover:from-green-700 hover:to-emerald-700 transition-all duration-300 font-medium">
                                <i class="fas fa-play mr-2"></i>
                                <?php echo isset($user_progress[$scenario['id']]) ? 'Retake' : 'Start'; ?>
                            </button>
                            <button onclick="previewCustomerService(<?php echo $scenario['id']; ?>)" 
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

<!-- Customer Service Preview Modal -->
<div id="cs-preview-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-8 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-900" id="cs-preview-title">Scenario Preview</h3>
            <button onclick="closeCSPreviewModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div id="cs-preview-content">
            <!-- Preview content will be loaded here -->
        </div>
        
        <div class="flex justify-end space-x-4 mt-6">
            <button onclick="closeCSPreviewModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                Close
            </button>
            <button onclick="startCustomerServiceFromPreview()" class="px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-md hover:from-green-700 hover:to-emerald-700 transition-all duration-300">
                Start Scenario
            </button>
        </div>
    </div>
</div>

<!-- Customer Service Training Modal -->
<div id="customer-service-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-8 max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-900" id="cs-title">Customer Service Scenario</h3>
            <div class="flex items-center space-x-4">
                <div class="text-sm text-gray-600">
                    <i class="fas fa-clock mr-1"></i>
                    <span id="cs-timer">10:00</span>
                </div>
                <button onclick="closeCustomerServiceModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <div id="customer-service-content">
            <!-- Scenario content will be loaded here -->
        </div>
        
        <div class="flex justify-between items-center mt-6 pt-6 border-t border-gray-200">
            <button onclick="skipCustomerService()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                <i class="fas fa-forward mr-2"></i>Skip
            </button>
            <button onclick="submitCustomerService()" class="px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-md hover:from-green-700 hover:to-emerald-700 transition-all duration-300">
                <i class="fas fa-check mr-2"></i>Submit Response
            </button>
        </div>
    </div>
</div>

<script>
let currentCustomerServiceScenario = null;
let csTimer = null;
let csTimeLeft = 0;

// Filter functionality
document.getElementById('type-filter').addEventListener('change', filterScenarios);
document.getElementById('difficulty-filter').addEventListener('change', filterScenarios);
document.getElementById('search-input').addEventListener('input', searchScenarios);

function filterScenarios() {
    const type = document.getElementById('type-filter').value;
    const difficulty = document.getElementById('difficulty-filter').value;
    
    // Update URL parameters
    const url = new URL(window.location);
    if (type) url.searchParams.set('type', type);
    else url.searchParams.delete('type');
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

function startCustomerService(scenarioId) {
    currentCustomerServiceScenario = scenarioId;
    
    fetch(`../../api/training/get-customer-service-details.php?id=${scenarioId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                openCustomerServiceModal(data.item);
            } else {
                alert('Error loading scenario: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error loading scenario:', error);
            alert('Error loading scenario');
        });
}

function previewCustomerService(scenarioId) {
    fetch(`../../api/training/get-customer-service-details.php?id=${scenarioId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                openCSPreviewModal(data.item);
            } else {
                alert('Error loading scenario preview');
            }
        })
        .catch(error => {
            console.error('Error loading scenario preview:', error);
            alert('Error loading scenario preview');
        });
}

function openCSPreviewModal(scenario) {
    document.getElementById('cs-preview-title').textContent = scenario.title;
    document.getElementById('cs-preview-content').innerHTML = `
        <div class="space-y-6">
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <h4 class="font-medium text-green-900 mb-2">Description</h4>
                <p class="text-green-800">${scenario.description}</p>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">Type</h4>
                    <p class="text-gray-700">${scenario.type}</p>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">Difficulty</h4>
                    <p class="text-gray-700">${scenario.difficulty}</p>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">Duration</h4>
                    <p class="text-gray-700">${scenario.estimated_time} minutes</p>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">Points</h4>
                    <p class="text-gray-700">${scenario.points}</p>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('cs-preview-modal').classList.remove('hidden');
}

function closeCSPreviewModal() {
    document.getElementById('cs-preview-modal').classList.add('hidden');
}

function startCustomerServiceFromPreview() {
    closeCSPreviewModal();
    startCustomerService(currentCustomerServiceScenario);
}

function openCustomerServiceModal(scenario) {
    if (!scenario) {
        console.error('Customer service scenario is undefined');
        alert('Error: Scenario data is missing');
        return;
    }
    
    window.currentCustomerServiceScenario = scenario.id;
    document.getElementById('cs-title').textContent = scenario.title || 'Customer Service Practice';
    document.getElementById('cs-difficulty').textContent = scenario.difficulty || 'beginner';
    document.getElementById('cs-points').textContent = scenario.points || 0;
    
    document.getElementById('customer-service-content').innerHTML = `
        <div class="space-y-6">
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <h4 class="font-medium text-green-900 mb-2">Scenario Description</h4>
                <p class="text-green-800">${scenario.description || 'No description available'}</p>
            </div>
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <h4 class="font-medium text-yellow-900 mb-2">Instructions</h4>
                <p class="text-yellow-800">Practice handling customer service situations professionally. Use the tips below to guide your response.</p>
            </div>
            
            <div class="space-y-4">
                <label class="block">
                    <span class="text-gray-700 font-medium">Your Response:</span>
                    <textarea id="customer-service-response" rows="4" 
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                              placeholder="Type your response here..."></textarea>
                </label>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-medium text-blue-900 mb-2">Tips for Success:</h4>
                    <ul class="text-blue-800 text-sm space-y-1">
                        <li>• Listen actively to the customer's concerns</li>
                        <li>• Show empathy and understanding</li>
                        <li>• Offer practical solutions</li>
                        <li>• Follow up to ensure satisfaction</li>
                    </ul>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('customer-service-modal').classList.remove('hidden');
    startCSTimer();
}

function startCSTimer() {
    csTimeLeft = 10 * 60; // 10 minutes in seconds
    csTimer = setInterval(() => {
        csTimeLeft--;
        const minutes = Math.floor(csTimeLeft / 60);
        const seconds = csTimeLeft % 60;
        document.getElementById('cs-timer').textContent = 
            `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        
        if (csTimeLeft <= 0) {
            stopCSTimer();
            alert('Time is up!');
        }
    }, 1000);
}

function stopCSTimer() {
    if (csTimer) {
        clearInterval(csTimer);
        csTimer = null;
    }
}

function skipCustomerService() {
    alert('Scenario skipped');
    closeCustomerServiceModal();
}

function submitCustomerService() {
    const response = document.getElementById('customer-service-response').value;
    
    if (!response.trim()) {
        alert('Please provide a response');
        return;
    }
    
    fetch('../../api/training/submit-customer-service.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({
            scenario_id: currentCustomerServiceScenario,
            response: response
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Response submitted! Score: ${data.score}%`);
            closeCustomerServiceModal();
            location.reload(); // Refresh to show updated progress
        } else {
            alert('Error submitting response: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error submitting response:', error);
        alert('Error submitting response');
    });
}

function closeCustomerServiceModal() {
    stopCSTimer();
    document.getElementById('customer-service-modal').classList.add('hidden');
}
</script>

<?php include '../../includes/footer.php'; ?>