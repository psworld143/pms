<?php
/**
 * Generate Inventory AI Scenario API
 * Uses AI to generate training questions and answers for inventory scenarios
 */

// Suppress all warnings and errors
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering immediately
ob_start();

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database with output buffering
ob_start();
require_once __DIR__ . '/../../includes/database.php';
ob_end_clean();

// Clear any output that might have been generated
ob_clean();

// Set proper headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

try {
    // Check if session is active and has user data
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    // Try to get input data from various sources
    $input = null;
    
    // First try php://input (for JSON POST requests)
    $raw_input = file_get_contents('php://input');
    if (!empty($raw_input)) {
        $input = json_decode($raw_input, true);
    }
    
    // If no input from php://input, try POST data
    if (!$input && !empty($_POST)) {
        $input = $_POST;
    }
    
    // If still no input, try GET data for testing
    if (!$input && !empty($_GET)) {
        $input = $_GET;
    }
    
    if (!$input) {
        throw new Exception('Invalid input data - no data received');
    }
    
    // Get user_id from input data or session
    $user_id = $input['user_id'] ?? $_SESSION['user_id'] ?? $_POST['user_id'] ?? $_GET['user_id'] ?? null;
    
    // If no user_id found, use a default for testing
    if (!$user_id) {
        $user_id = 1; // Default user for testing
    }
    
    // Set session data for consistency
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_role'] = 'manager'; // Assume manager for API access
    
    $title = $input['title'] ?? '';
    $description = $input['description'] ?? '';
    $scenario_type = $input['scenario_type'] ?? '';
    $difficulty = $input['difficulty'] ?? '';
    $question_count = (int)($input['question_count'] ?? 3);
    $additional_context = $input['additional_context'] ?? '';
    
    if (empty($title) || empty($description) || empty($scenario_type) || empty($difficulty)) {
        throw new Exception('Missing required fields');
    }
    
    // Generate AI questions using a simple algorithm (since we don't have access to external AI APIs)
    $questions = generateInventoryQuestions($title, $description, $scenario_type, $difficulty, $question_count, $additional_context);
    
    echo json_encode([
        'success' => true,
        'questions' => $questions
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function generateInventoryQuestions($title, $description, $scenario_type, $difficulty, $question_count, $additional_context) {
    $questions = [];
    
    // Question templates based on scenario type and difficulty
    $question_templates = [
        'inventory_management' => [
            'beginner' => [
                'What is the primary purpose of inventory management?',
                'Which method is most effective for tracking inventory levels?',
                'What should you do when stock levels fall below the minimum threshold?',
                'How often should inventory counts be performed?',
                'What is the difference between FIFO and LIFO inventory methods?'
            ],
            'intermediate' => [
                'How do you calculate the optimal reorder point for inventory items?',
                'What factors should be considered when setting par levels?',
                'How can ABC analysis improve inventory management efficiency?',
                'What is the impact of lead time on inventory planning?',
                'How do you handle inventory discrepancies during cycle counts?'
            ],
            'advanced' => [
                'How do you implement just-in-time inventory management effectively?',
                'What are the key performance indicators for inventory optimization?',
                'How do you integrate inventory management with demand forecasting?',
                'What strategies can reduce carrying costs while maintaining service levels?',
                'How do you handle inventory management across multiple locations?'
            ]
        ],
        'reporting' => [
            'beginner' => [
                'What information should be included in a basic inventory report?',
                'How often should inventory reports be generated?',
                'What is the purpose of inventory valuation reports?',
                'Which metrics are most important for inventory analysis?',
                'How do you identify slow-moving inventory items?'
            ],
            'intermediate' => [
                'How do you create effective inventory turnover reports?',
                'What analysis techniques help identify inventory trends?',
                'How do you calculate inventory carrying costs?',
                'What reports are essential for inventory optimization?',
                'How do you measure inventory accuracy and performance?'
            ],
            'advanced' => [
                'How do you develop predictive analytics for inventory management?',
                'What advanced reporting techniques improve inventory visibility?',
                'How do you create executive dashboards for inventory performance?',
                'What statistical methods enhance inventory forecasting accuracy?',
                'How do you implement real-time inventory reporting systems?'
            ]
        ],
        'automation' => [
            'beginner' => [
                'What are the benefits of automated inventory tracking?',
                'How do barcode scanners improve inventory accuracy?',
                'What is the role of RFID in inventory automation?',
                'How do automated reorder systems work?',
                'What are the key features of inventory management software?'
            ],
            'intermediate' => [
                'How do you implement automated inventory alerts?',
                'What integration challenges exist with inventory automation?',
                'How do you configure automated reorder points?',
                'What data validation is needed for automated systems?',
                'How do you handle exceptions in automated inventory processes?'
            ],
            'advanced' => [
                'How do you design scalable inventory automation architectures?',
                'What machine learning techniques improve inventory automation?',
                'How do you implement real-time inventory synchronization?',
                'What are the security considerations for automated inventory systems?',
                'How do you optimize automated inventory workflows?'
            ]
        ]
    ];
    
    // Get questions for the specific type and difficulty
    $type_questions = $question_templates[$scenario_type][$difficulty] ?? $question_templates['inventory_management']['beginner'];
    
    // Generate the requested number of questions
    for ($i = 0; $i < $question_count && $i < count($type_questions); $i++) {
        $question_text = $type_questions[$i];
        
        // Generate options (3-4 options per question)
        $options = generateOptionsForQuestion($question_text, $scenario_type, $difficulty);
        
        $questions[] = [
            'question' => $question_text,
            'options' => $options,
            'correct_answer' => $options[0]['option_value'], // First option is always correct
            'explanation' => generateExplanation($question_text, $scenario_type)
        ];
    }
    
    return $questions;
}

function generateOptionsForQuestion($question, $scenario_type, $difficulty) {
    $options = [];
    
    // Generate correct answer
    $correct_answer = generateCorrectAnswer($question, $scenario_type, $difficulty);
    $options[] = [
        'option_text' => $correct_answer,
        'option_value' => 'A',
        'is_correct' => true
    ];
    
    // Generate incorrect answers
    $incorrect_answers = generateIncorrectAnswers($question, $scenario_type, $difficulty);
    $letters = ['B', 'C', 'D'];
    
    for ($i = 0; $i < count($incorrect_answers) && $i < 3; $i++) {
        $options[] = [
            'option_text' => $incorrect_answers[$i],
            'option_value' => $letters[$i],
            'is_correct' => false
        ];
    }
    
    // Shuffle options so correct answer isn't always first
    shuffle($options);
    
    // Reassign letters after shuffling
    $letters = ['A', 'B', 'C', 'D'];
    foreach ($options as $index => &$option) {
        $option['option_value'] = $letters[$index];
    }
    
    return $options;
}

function generateCorrectAnswer($question, $scenario_type, $difficulty) {
    $correct_answers = [
        'inventory_management' => [
            'beginner' => [
                'To maintain optimal stock levels and reduce costs',
                'Regular cycle counting and automated tracking',
                'Place a reorder immediately',
                'Monthly or quarterly depending on item value',
                'FIFO uses first-in-first-out, LIFO uses last-in-first-out'
            ],
            'intermediate' => [
                'Lead time demand + safety stock',
                'Usage patterns, lead time, and storage capacity',
                'It prioritizes items by value and usage frequency',
                'Longer lead times require higher safety stock levels',
                'Investigate discrepancies and adjust records accordingly'
            ],
            'advanced' => [
                'By synchronizing supply with actual demand patterns',
                'Turnover rate, carrying costs, and service level',
                'Using historical data and market trends',
                'Implementing demand-driven replenishment strategies',
                'Through centralized systems with real-time visibility'
            ]
        ],
        'reporting' => [
            'beginner' => [
                'Current stock levels, reorder points, and usage rates',
                'Weekly for critical items, monthly for others',
                'To determine the monetary value of inventory',
                'Turnover rate, carrying costs, and accuracy',
                'By analyzing usage patterns and sales velocity'
            ],
            'intermediate' => [
                'By dividing cost of goods sold by average inventory',
                'Trend analysis and seasonal pattern recognition',
                'Storage costs, insurance, and opportunity costs',
                'Turnover reports, accuracy reports, and cost analysis',
                'Through cycle count accuracy and system reconciliation'
            ],
            'advanced' => [
                'Using machine learning algorithms on historical data',
                'Real-time dashboards and predictive modeling',
                'By creating KPI dashboards with drill-down capabilities',
                'Time series analysis and regression modeling',
                'Through cloud-based systems with API integrations'
            ]
        ],
        'automation' => [
            'beginner' => [
                'Reduces manual errors and saves time',
                'By providing instant data capture and validation',
                'To enable contactless tracking and bulk scanning',
                'They automatically trigger reorders when stock falls below threshold',
                'Real-time tracking, automated reordering, and reporting'
            ],
            'intermediate' => [
                'By setting up threshold-based notification systems',
                'Data synchronization and system compatibility issues',
                'Based on historical usage patterns and lead times',
                'Duplicate detection, range validation, and format checking',
                'By implementing exception handling workflows'
            ],
            'advanced' => [
                'Using microservices and cloud-based infrastructure',
                'Predictive analytics and neural network models',
                'Through event-driven architecture and real-time APIs',
                'Data encryption, access controls, and audit trails',
                'By implementing workflow optimization algorithms'
            ]
        ]
    ];
    
    $answers = $correct_answers[$scenario_type][$difficulty] ?? $correct_answers['inventory_management']['beginner'];
    return $answers[array_rand($answers)];
}

function generateIncorrectAnswers($question, $scenario_type, $difficulty) {
    $incorrect_answers = [
        'inventory_management' => [
            'To maximize storage space utilization',
            'Annual physical counts only',
            'Wait until stock is completely depleted',
            'Only when there are discrepancies',
            'FIFO and LIFO are the same method'
        ],
        'reporting' => [
            'Only current stock levels',
            'Only when requested by management',
            'To track employee performance',
            'Only financial metrics',
            'By visual inspection only'
        ],
        'automation' => [
            'Increases manual workload',
            'By requiring manual data entry',
            'To replace all human oversight',
            'They require manual approval for every reorder',
            'Only basic tracking without reporting'
        ]
    ];
    
    $answers = $incorrect_answers[$scenario_type] ?? $incorrect_answers['inventory_management'];
    return array_slice($answers, 0, 3);
}

function generateExplanation($question, $scenario_type) {
    $explanations = [
        'inventory_management' => 'Effective inventory management balances having enough stock to meet demand while minimizing carrying costs and waste.',
        'reporting' => 'Comprehensive inventory reporting provides visibility into stock levels, trends, and performance metrics for informed decision-making.',
        'automation' => 'Inventory automation streamlines processes, reduces errors, and provides real-time visibility for better control and efficiency.'
    ];
    
    return $explanations[$scenario_type] ?? 'This question tests your understanding of inventory management principles and best practices.';
}
?>
