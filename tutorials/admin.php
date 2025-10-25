<?php
/**
 * Dynamic Training Admin Interface
 * Manage training content dynamically
 */

session_start();
require_once '../includes/database.php';
require_once 'includes/dynamic-training-manager.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header('Location: login.php');
    exit();
}

// Initialize dynamic training manager
$dynamic_training = new DynamicTrainingManager($pdo);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_content':
                $content_id = $dynamic_training->createTrainingContent(
                    $_POST['module_id'],
                    [
                        'step_number' => $_POST['step_number'],
                        'title' => $_POST['title'],
                        'content' => $_POST['content'],
                        'step_type' => $_POST['step_type'],
                        'duration_minutes' => $_POST['duration_minutes'],
                        'learning_objectives' => explode("\n", $_POST['learning_objectives']),
                        'interactive_data' => json_decode($_POST['interactive_data'] ?? '{}', true),
                        'order_index' => $_POST['order_index']
                    ]
                );
                if ($content_id) {
                    $success_message = "Training content created successfully!";
                } else {
                    $error_message = "Failed to create training content.";
                }
                break;
                
            case 'update_content':
                $result = $dynamic_training->updateTrainingContent(
                    $_POST['content_id'],
                    [
                        'title' => $_POST['title'],
                        'content' => $_POST['content'],
                        'step_type' => $_POST['step_type'],
                        'duration_minutes' => $_POST['duration_minutes'],
                        'learning_objectives' => explode("\n", $_POST['learning_objectives']),
                        'interactive_data' => json_decode($_POST['interactive_data'] ?? '{}', true),
                        'order_index' => $_POST['order_index']
                    ]
                );
                if ($result) {
                    $success_message = "Training content updated successfully!";
                } else {
                    $error_message = "Failed to update training content.";
                }
                break;
                
            case 'delete_content':
                $result = $dynamic_training->deleteTrainingContent($_POST['content_id']);
                if ($result) {
                    $success_message = "Training content deleted successfully!";
                } else {
                    $error_message = "Failed to delete training content.";
                }
                break;
        }
    }
}

// Get data for display
$modules = $dynamic_training->getAllModules();
$categories = $dynamic_training->getCategories();
$tags = $dynamic_training->getTags();

// Get selected module content
$selected_module_id = $_GET['module_id'] ?? null;
$module_steps = [];
if ($selected_module_id) {
    $module_steps = $dynamic_training->getModuleSteps($selected_module_id);
}

$page_title = 'Training Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Hotel PMS Training</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-gray-900">Training Admin</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-blue-600 hover:text-blue-700">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Tutorials
                    </a>
                    <a href="logout.php" class="text-red-600 hover:text-red-700">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Messages -->
        <?php if (isset($success_message)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?php echo $success_message; ?>
        </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>

        <!-- Module Selection -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Select Training Module</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($modules as $module): ?>
                <a href="?module_id=<?php echo $module['id']; ?>" 
                   class="block p-4 border rounded-lg hover:bg-gray-50 transition-colors <?php echo $selected_module_id == $module['id'] ? 'border-blue-500 bg-blue-50' : 'border-gray-200'; ?>">
                    <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($module['name']); ?></h3>
                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($module['description']); ?></p>
                    <div class="mt-2 text-xs text-gray-500">
                        <?php echo $module['step_count']; ?> steps • <?php echo $module['avg_duration']; ?> min avg
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($selected_module_id): ?>
        <!-- Module Content Management -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Training Steps</h2>
                <button onclick="showCreateForm()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Add New Step
                </button>
            </div>

            <!-- Steps List -->
            <div class="space-y-4">
                <?php foreach ($module_steps as $step): ?>
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900">Step <?php echo $step['step_number']; ?>: <?php echo htmlspecialchars($step['title']); ?></h3>
                            <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars(substr($step['content'], 0, 100)); ?>...</p>
                            <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                                <span><i class="fas fa-clock mr-1"></i><?php echo $step['duration_minutes']; ?> min</span>
                                <span><i class="fas fa-tag mr-1"></i><?php echo ucfirst($step['step_type']); ?></span>
                                <span><i class="fas fa-sort mr-1"></i>Order: <?php echo $step['order_index']; ?></span>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="editStep(<?php echo $step['id']; ?>)" class="text-blue-600 hover:text-blue-700">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteStep(<?php echo $step['id']; ?>)" class="text-red-600 hover:text-red-700">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Create/Edit Form -->
        <div id="contentForm" class="bg-white rounded-lg shadow-sm p-6 hidden">
            <h3 class="text-lg font-semibold text-gray-900 mb-4" id="formTitle">Add New Training Step</h3>
            <form method="POST" id="contentFormElement">
                <input type="hidden" name="action" value="create_content" id="formAction">
                <input type="hidden" name="content_id" id="contentId">
                <input type="hidden" name="module_id" value="<?php echo $selected_module_id; ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Step Number</label>
                        <input type="number" name="step_number" id="stepNumber" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Step Type</label>
                        <select name="step_type" id="stepType" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="introduction">Introduction</option>
                            <option value="learning">Learning</option>
                            <option value="practical">Practical</option>
                            <option value="quiz">Quiz</option>
                            <option value="simulation">Simulation</option>
                            <option value="assessment">Assessment</option>
                            <option value="summary">Summary</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Duration (minutes)</label>
                        <input type="number" name="duration_minutes" id="durationMinutes" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Order Index</label>
                        <input type="number" name="order_index" id="orderIndex" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                    <input type="text" name="title" id="title" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                    <textarea name="content" id="content" rows="6" required 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
                
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Learning Objectives (one per line)</label>
                    <textarea name="learning_objectives" id="learningObjectives" rows="4" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
                
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Interactive Data (JSON)</label>
                    <textarea name="interactive_data" id="interactiveData" rows="4" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"></textarea>
                </div>
                
                <div class="mt-6 flex justify-end space-x-4">
                    <button type="button" onclick="hideForm()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Save Step
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </main>

    <script>
        function showCreateForm() {
            document.getElementById('contentForm').classList.remove('hidden');
            document.getElementById('formTitle').textContent = 'Add New Training Step';
            document.getElementById('formAction').value = 'create_content';
            document.getElementById('contentFormElement').reset();
            document.getElementById('contentId').value = '';
        }

        function editStep(stepId) {
            // This would populate the form with existing step data
            // For now, just show the form
            document.getElementById('contentForm').classList.remove('hidden');
            document.getElementById('formTitle').textContent = 'Edit Training Step';
            document.getElementById('formAction').value = 'update_content';
            document.getElementById('contentId').value = stepId;
        }

        function deleteStep(stepId) {
            if (confirm('Are you sure you want to delete this training step?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_content">
                    <input type="hidden" name="content_id" value="${stepId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function hideForm() {
            document.getElementById('contentForm').classList.add('hidden');
        }
    </script>
</body>
</html>


