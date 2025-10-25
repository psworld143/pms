<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in');
    }
    
    // Check if user is manager
    $user_role = $_SESSION['user_role'] ?? 'front_desk';
    if ($user_role !== 'manager') {
        throw new Exception('Only managers can generate AI scenarios');
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    $scenario_type = $input['scenario_type'] ?? '';
    $difficulty = $input['difficulty'] ?? '';
    $question_count = (int)($input['question_count'] ?? 5);
    $complexity = $input['question_complexity'] ?? 'intermediate';
    $description = $input['description'] ?? '';
    $context = $input['context'] ?? '';
    
    if (empty($scenario_type) || empty($difficulty) || empty($description)) {
        throw new Exception('Missing required fields');
    }
    
    // Generate AI questions using OpenAI API
    $questions = generateAIQuestions($scenario_type, $difficulty, $question_count, $complexity, $description, $context);
    
    echo json_encode([
        'success' => true,
        'questions' => $questions
    ]);
    
} catch (Exception $e) {
    error_log('generate-ai-scenario: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function generateAIQuestions($scenario_type, $difficulty, $question_count, $complexity, $description, $context) {
    // For now, we'll generate questions locally without external AI API
    // In production, you would integrate with OpenAI, Claude, or similar API
    
    $scenario_templates = [
        'front_desk' => [
            'topics' => ['check-in procedures', 'guest registration', 'room assignments', 'payment processing', 'guest services'],
            'situations' => ['guest arrival', 'check-in process', 'room key issues', 'payment disputes', 'special requests']
        ],
        'customer_service' => [
            'topics' => ['complaint handling', 'guest satisfaction', 'service recovery', 'communication skills', 'problem resolution'],
            'situations' => ['angry guest', 'service complaint', 'special request', 'billing issue', 'room problem']
        ],
        'problem_solving' => [
            'topics' => ['crisis management', 'emergency procedures', 'staff coordination', 'decision making', 'resource allocation'],
            'situations' => ['emergency situation', 'staff shortage', 'equipment failure', 'guest emergency', 'system outage']
        ]
    ];
    
    $template = $scenario_templates[$scenario_type] ?? $scenario_templates['front_desk'];
    $questions = [];
    
    for ($i = 0; $i < $question_count; $i++) {
        $topic = $template['topics'][array_rand($template['topics'])];
        $situation = $template['situations'][array_rand($template['situations'])];
        
        $question_text = generateQuestionText($scenario_type, $topic, $situation, $difficulty, $complexity);
        $options = generateQuestionOptions($scenario_type, $topic, $situation, $difficulty);
        
        $questions[] = [
            'question' => $question_text,
            'options' => $options,
            'correct_answer' => $options[0]['value'] // First option is always correct in our simple generator
        ];
    }
    
    return $questions;
}

function generateQuestionText($scenario_type, $topic, $situation, $difficulty, $complexity) {
    $difficulty_modifiers = [
        'beginner' => ['basic', 'simple', 'straightforward'],
        'intermediate' => ['moderate', 'common', 'typical'],
        'advanced' => ['complex', 'challenging', 'sophisticated']
    ];
    
    $modifier = $difficulty_modifiers[$difficulty][array_rand($difficulty_modifiers[$difficulty])];
    
    $question_templates = [
        "What is the most appropriate response when dealing with {$modifier} {$situation} in {$topic}?",
        "How should you handle a {$modifier} {$situation} situation related to {$topic}?",
        "What is the best approach for managing {$modifier} {$situation} in {$topic}?",
        "Which action should be taken first when encountering {$modifier} {$situation} in {$topic}?",
        "What is the correct procedure for handling {$modifier} {$situation} during {$topic}?"
    ];
    
    return $question_templates[array_rand($question_templates)];
}

function generateQuestionOptions($scenario_type, $topic, $situation, $difficulty) {
    $correct_responses = [
        'front_desk' => [
            'Greet the guest warmly and verify their reservation',
            'Apologize immediately and take corrective action',
            'Follow standard procedures and document the situation',
            'Escalate to management if necessary'
        ],
        'customer_service' => [
            'Listen actively and acknowledge their concerns',
            'Apologize sincerely and offer a solution',
            'Remain calm and professional throughout',
            'Follow up to ensure satisfaction'
        ],
        'problem_solving' => [
            'Assess the situation quickly and prioritize safety',
            'Communicate clearly with all stakeholders',
            'Implement the most appropriate solution',
            'Document the incident and lessons learned'
        ]
    ];
    
    $incorrect_responses = [
        'Ignore the situation and hope it resolves itself',
        'Blame the guest or external factors',
        'Panic and make hasty decisions',
        'Avoid taking responsibility',
        'Use inappropriate language or tone',
        'Delay action unnecessarily',
        'Make promises you cannot keep'
    ];
    
    $correct = $correct_responses[$scenario_type][array_rand($correct_responses[$scenario_type])];
    $incorrect = array_slice($incorrect_responses, 0, 3);
    
    $options = [
        ['text' => $correct, 'value' => 'correct_' . uniqid(), 'is_correct' => true]
    ];
    
    foreach ($incorrect as $response) {
        $options[] = ['text' => $response, 'value' => 'incorrect_' . uniqid(), 'is_correct' => false];
    }
    
    // Shuffle options so correct answer isn't always first
    shuffle($options);
    
    return $options;
}
?>
