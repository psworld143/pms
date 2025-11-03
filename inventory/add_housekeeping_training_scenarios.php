<?php
/**
 * Add Housekeeping Training Scenarios
 * Adds housekeeping-specific training scenarios to the database
 */

require_once __DIR__ . '/../includes/database.php';

try {
    $pdo->beginTransaction();
    
    // Housekeeping-specific scenarios
    $housekeeping_scenarios = [
        [
            'title' => 'Room Inventory Management',
            'description' => 'Learn how to effectively manage room inventory, track items, and maintain accurate stock levels for guest rooms.',
            'scenario_type' => 'room_inventory',
            'difficulty' => 'beginner',
            'estimated_time' => 15,
            'points' => 10
        ],
        [
            'title' => 'Supply Request Process',
            'description' => 'Master the process of requesting supplies, understanding approval workflows, and managing inventory requests.',
            'scenario_type' => 'approval',
            'difficulty' => 'beginner',
            'estimated_time' => 12,
            'points' => 8
        ],
        [
            'title' => 'Daily Inventory Check',
            'description' => 'Practice daily inventory checking procedures, identifying shortages, and reporting discrepancies.',
            'scenario_type' => 'inventory_management',
            'difficulty' => 'intermediate',
            'estimated_time' => 18,
            'points' => 12
        ],
        [
            'title' => 'Guest Room Setup',
            'description' => 'Learn proper procedures for setting up guest rooms with correct inventory items and maintaining standards.',
            'scenario_type' => 'room_inventory',
            'difficulty' => 'intermediate',
            'estimated_time' => 20,
            'points' => 15
        ],
        [
            'title' => 'Lost and Found Management',
            'description' => 'Understand how to handle lost and found items, proper documentation, and inventory tracking procedures.',
            'scenario_type' => 'inventory_management',
            'difficulty' => 'beginner',
            'estimated_time' => 10,
            'points' => 6
        ],
        [
            'title' => 'Emergency Supply Management',
            'description' => 'Learn how to handle emergency supply situations, urgent requests, and crisis inventory management.',
            'scenario_type' => 'approval',
            'difficulty' => 'advanced',
            'estimated_time' => 25,
            'points' => 18
        ]
    ];
    
    $scenario_ids = [];
    foreach ($housekeeping_scenarios as $scenario) {
        $stmt = $pdo->prepare("
            INSERT INTO inventory_training_scenarios 
            (title, description, scenario_type, difficulty, estimated_time, points) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $scenario['title'],
            $scenario['description'],
            $scenario['scenario_type'],
            $scenario['difficulty'],
            $scenario['estimated_time'],
            $scenario['points']
        ]);
        $scenario_ids[] = $pdo->lastInsertId();
    }
    
    // Sample questions for housekeeping scenarios
    $housekeeping_questions = [
        // Room Inventory Management
        [
            'scenario_id' => $scenario_ids[0],
            'questions' => [
                [
                    'question' => 'What is the first step when checking room inventory?',
                    'correct_answer' => 'verify_room_status',
                    'options' => [
                        ['text' => 'Verify the room status and guest checkout', 'value' => 'verify_room_status'],
                        ['text' => 'Start counting items immediately', 'value' => 'start_counting'],
                        ['text' => 'Ask the guest about missing items', 'value' => 'ask_guest'],
                        ['text' => 'Report to supervisor first', 'value' => 'report_supervisor']
                    ]
                ],
                [
                    'question' => 'How should you document missing inventory items?',
                    'correct_answer' => 'immediate_report',
                    'options' => [
                        ['text' => 'Report immediately with detailed documentation', 'value' => 'immediate_report'],
                        ['text' => 'Wait until end of shift', 'value' => 'end_shift'],
                        ['text' => 'Only report if expensive items', 'value' => 'expensive_only'],
                        ['text' => 'Ignore small items', 'value' => 'ignore_small']
                    ]
                ],
                [
                    'question' => 'What should you do if you find damaged inventory items?',
                    'correct_answer' => 'document_and_remove',
                    'options' => [
                        ['text' => 'Document the damage and remove from inventory', 'value' => 'document_and_remove'],
                        ['text' => 'Leave them in the room', 'value' => 'leave_room'],
                        ['text' => 'Hide them from view', 'value' => 'hide_items'],
                        ['text' => 'Ask the guest to pay', 'value' => 'ask_payment']
                    ]
                ]
            ]
        ],
        // Supply Request Process
        [
            'scenario_id' => $scenario_ids[1],
            'questions' => [
                [
                    'question' => 'When should you submit a supply request?',
                    'correct_answer' => 'before_shortage',
                    'options' => [
                        ['text' => 'Before items run out completely', 'value' => 'before_shortage'],
                        ['text' => 'Only when completely out of stock', 'value' => 'completely_out'],
                        ['text' => 'At the end of each week', 'value' => 'end_week'],
                        ['text' => 'When supervisor asks', 'value' => 'supervisor_asks']
                    ]
                ],
                [
                    'question' => 'What information is required for a supply request?',
                    'correct_answer' => 'all_details',
                    'options' => [
                        ['text' => 'Item name, quantity, reason, and urgency', 'value' => 'all_details'],
                        ['text' => 'Only item name and quantity', 'value' => 'name_quantity'],
                        ['text' => 'Just the item name', 'value' => 'name_only'],
                        ['text' => 'Only the quantity needed', 'value' => 'quantity_only']
                    ]
                ],
                [
                    'question' => 'How should you prioritize urgent supply requests?',
                    'correct_answer' => 'mark_urgent',
                    'options' => [
                        ['text' => 'Mark as urgent and provide justification', 'value' => 'mark_urgent'],
                        ['text' => 'Submit multiple requests', 'value' => 'multiple_requests'],
                        ['text' => 'Call the supplier directly', 'value' => 'call_supplier'],
                        ['text' => 'Wait for regular processing', 'value' => 'wait_regular']
                    ]
                ]
            ]
        ],
        // Daily Inventory Check
        [
            'scenario_id' => $scenario_ids[2],
            'questions' => [
                [
                    'question' => 'What is the best time to conduct daily inventory checks?',
                    'correct_answer' => 'morning_before_guests',
                    'options' => [
                        ['text' => 'Early morning before guest activities', 'value' => 'morning_before_guests'],
                        ['text' => 'During peak guest hours', 'value' => 'peak_hours'],
                        ['text' => 'Late at night', 'value' => 'late_night'],
                        ['text' => 'Whenever convenient', 'value' => 'when_convenient']
                    ]
                ],
                [
                    'question' => 'How should you handle discrepancies in inventory counts?',
                    'correct_answer' => 'investigate_and_report',
                    'options' => [
                        ['text' => 'Investigate the cause and report immediately', 'value' => 'investigate_and_report'],
                        ['text' => 'Ignore small discrepancies', 'value' => 'ignore_small'],
                        ['text' => 'Adjust counts without investigation', 'value' => 'adjust_counts'],
                        ['text' => 'Wait for next check', 'value' => 'wait_next']
                    ]
                ],
                [
                    'question' => 'What should you do if you find unauthorized items in a room?',
                    'correct_answer' => 'secure_and_report',
                    'options' => [
                        ['text' => 'Secure the items and report to security', 'value' => 'secure_and_report'],
                        ['text' => 'Remove items immediately', 'value' => 'remove_immediately'],
                        ['text' => 'Leave items where found', 'value' => 'leave_items'],
                        ['text' => 'Ask other staff members', 'value' => 'ask_staff']
                    ]
                ]
            ]
        ],
        // Guest Room Setup
        [
            'scenario_id' => $scenario_ids[3],
            'questions' => [
                [
                    'question' => 'What is the standard procedure for setting up a guest room?',
                    'correct_answer' => 'checklist_follow',
                    'options' => [
                        ['text' => 'Follow the standard room setup checklist', 'value' => 'checklist_follow'],
                        ['text' => 'Set up based on personal preference', 'value' => 'personal_preference'],
                        ['text' => 'Ask the guest what they want', 'value' => 'ask_guest'],
                        ['text' => 'Copy the previous room setup', 'value' => 'copy_previous']
                    ]
                ],
                [
                    'question' => 'How should you handle special guest requests for room items?',
                    'correct_answer' => 'document_and_fulfill',
                    'options' => [
                        ['text' => 'Document the request and fulfill if possible', 'value' => 'document_and_fulfill'],
                        ['text' => 'Ignore special requests', 'value' => 'ignore_requests'],
                        ['text' => 'Only fulfill expensive requests', 'value' => 'expensive_only'],
                        ['text' => 'Ask supervisor for every request', 'value' => 'ask_supervisor']
                    ]
                ],
                [
                    'question' => 'What should you do if standard room items are not available?',
                    'correct_answer' => 'find_alternatives',
                    'options' => [
                        ['text' => 'Find suitable alternatives and document', 'value' => 'find_alternatives'],
                        ['text' => 'Leave the room incomplete', 'value' => 'leave_incomplete'],
                        ['text' => 'Wait for items to be restocked', 'value' => 'wait_restock'],
                        ['text' => 'Ask the guest to provide items', 'value' => 'ask_guest_provide']
                    ]
                ]
            ]
        ],
        // Lost and Found Management
        [
            'scenario_id' => $scenario_ids[4],
            'questions' => [
                [
                    'question' => 'What should you do when you find a guest item in a room?',
                    'correct_answer' => 'secure_and_document',
                    'options' => [
                        ['text' => 'Secure the item and document all details', 'value' => 'secure_and_document'],
                        ['text' => 'Leave the item in the room', 'value' => 'leave_room'],
                        ['text' => 'Take the item to your supervisor', 'value' => 'take_supervisor'],
                        ['text' => 'Dispose of the item', 'value' => 'dispose_item']
                    ]
                ],
                [
                    'question' => 'How long should lost and found items be kept?',
                    'correct_answer' => 'policy_period',
                    'options' => [
                        ['text' => 'According to hotel policy (usually 30-90 days)', 'value' => 'policy_period'],
                        ['text' => 'Only 1 week', 'value' => 'one_week'],
                        ['text' => 'Until the guest returns', 'value' => 'until_return'],
                        ['text' => 'Indefinitely', 'value' => 'indefinitely']
                    ]
                ],
                [
                    'question' => 'What information should be recorded for lost items?',
                    'correct_answer' => 'complete_details',
                    'options' => [
                        ['text' => 'Complete description, location found, date, and room number', 'value' => 'complete_details'],
                        ['text' => 'Only item description', 'value' => 'description_only'],
                        ['text' => 'Just the room number', 'value' => 'room_only'],
                        ['text' => 'Only the date found', 'value' => 'date_only']
                    ]
                ]
            ]
        ],
        // Emergency Supply Management
        [
            'scenario_id' => $scenario_ids[5],
            'questions' => [
                [
                    'question' => 'What constitutes an emergency supply situation?',
                    'correct_answer' => 'guest_safety_impact',
                    'options' => [
                        ['text' => 'Any situation that impacts guest safety or comfort', 'value' => 'guest_safety_impact'],
                        ['text' => 'Only when completely out of stock', 'value' => 'completely_out'],
                        ['text' => 'When supervisor says it is', 'value' => 'supervisor_says'],
                        ['text' => 'Only for expensive items', 'value' => 'expensive_items']
                    ]
                ],
                [
                    'question' => 'How should you handle emergency supply requests?',
                    'correct_answer' => 'immediate_escalation',
                    'options' => [
                        ['text' => 'Escalate immediately to management and find alternatives', 'value' => 'immediate_escalation'],
                        ['text' => 'Wait for regular approval process', 'value' => 'regular_approval'],
                        ['text' => 'Handle it yourself', 'value' => 'handle_self'],
                        ['text' => 'Ask other departments', 'value' => 'ask_departments']
                    ]
                ],
                [
                    'question' => 'What should you do if emergency supplies are not available?',
                    'correct_answer' => 'communicate_alternatives',
                    'options' => [
                        ['text' => 'Communicate with guests and offer alternatives', 'value' => 'communicate_alternatives'],
                        ['text' => 'Pretend everything is normal', 'value' => 'pretend_normal'],
                        ['text' => 'Close the affected areas', 'value' => 'close_areas'],
                        ['text' => 'Wait for supplies to arrive', 'value' => 'wait_supplies']
                    ]
                ]
            ]
        ]
    ];
    
    // Insert questions and options for housekeeping scenarios
    foreach ($housekeeping_questions as $scenario_data) {
        $scenario_id = $scenario_data['scenario_id'];
        $questions = $scenario_data['questions'];
        
        foreach ($questions as $index => $question_data) {
            // Insert question
            $stmt = $pdo->prepare("
                INSERT INTO inventory_scenario_questions 
                (scenario_id, question, question_order, correct_answer) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $scenario_id,
                $question_data['question'],
                $index + 1,
                $question_data['correct_answer']
            ]);
            $question_id = $pdo->lastInsertId();
            
            // Insert options
            foreach ($question_data['options'] as $option_index => $option) {
                $stmt = $pdo->prepare("
                    INSERT INTO inventory_question_options 
                    (question_id, option_text, option_value, option_order) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    $question_id,
                    $option['text'],
                    $option['value'],
                    $option_index + 1
                ]);
            }
        }
    }
    
    $pdo->commit();
    
    echo "Housekeeping training scenarios added successfully!\n";
    echo "Created " . count($housekeeping_scenarios) . " housekeeping-specific scenarios with questions and options.\n";
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error: " . $e->getMessage() . "\n";
}
?>




