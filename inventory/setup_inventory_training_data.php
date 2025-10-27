<?php
/**
 * Setup Inventory Training Data
 * Populates the inventory training tables with sample scenarios and questions
 */

require_once __DIR__ . '/../includes/database.php';

try {
    $pdo->beginTransaction();
    
    // Clear existing data
    $pdo->exec("DELETE FROM inventory_question_options");
    $pdo->exec("DELETE FROM inventory_scenario_questions");
    $pdo->exec("DELETE FROM inventory_training_scenarios");
    
    // Insert sample scenarios
    $scenarios = [
        [
            'title' => 'Add Inventory Item',
            'description' => 'Learn how to effectively add new inventory items to the system with proper categorization and details.',
            'scenario_type' => 'inventory_management',
            'difficulty' => 'beginner',
            'estimated_time' => 15,
            'points' => 10
        ],
        [
            'title' => 'Submit Transaction',
            'description' => 'Master the process of recording inventory transactions including receipts, issues, and adjustments.',
            'scenario_type' => 'inventory_management',
            'difficulty' => 'intermediate',
            'estimated_time' => 20,
            'points' => 15
        ],
        [
            'title' => 'Request Supplies',
            'description' => 'Learn the proper procedure for requesting supplies and managing approval workflows.',
            'scenario_type' => 'approval',
            'difficulty' => 'beginner',
            'estimated_time' => 10,
            'points' => 8
        ],
        [
            'title' => 'Update Room Inventory',
            'description' => 'Practice updating room inventory levels and tracking item usage in guest rooms.',
            'scenario_type' => 'room_inventory',
            'difficulty' => 'intermediate',
            'estimated_time' => 18,
            'points' => 12
        ],
        [
            'title' => 'View Reports',
            'description' => 'Understand how to generate and interpret inventory reports for better decision making.',
            'scenario_type' => 'reporting',
            'difficulty' => 'advanced',
            'estimated_time' => 25,
            'points' => 20
        ],
        [
            'title' => 'Review Audit Logs',
            'description' => 'Learn to review audit logs and track inventory changes for compliance and security.',
            'scenario_type' => 'monitoring',
            'difficulty' => 'advanced',
            'estimated_time' => 22,
            'points' => 18
        ]
    ];
    
    $scenario_ids = [];
    foreach ($scenarios as $scenario) {
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
    
    // Sample questions and answers for each scenario
    $questions_data = [
        // Add Inventory Item
        [
            'scenario_id' => $scenario_ids[0],
            'questions' => [
                [
                    'question' => 'What is the most important information to include when adding a new inventory item?',
                    'correct_answer' => 'item_name',
                    'options' => [
                        ['text' => 'Item name, SKU, and category', 'value' => 'item_name'],
                        ['text' => 'Only the item name', 'value' => 'name_only'],
                        ['text' => 'Just the SKU number', 'value' => 'sku_only'],
                        ['text' => 'Only the category', 'value' => 'category_only']
                    ]
                ],
                [
                    'question' => 'Which field is used to uniquely identify inventory items?',
                    'correct_answer' => 'sku',
                    'options' => [
                        ['text' => 'Item name', 'value' => 'name'],
                        ['text' => 'SKU (Stock Keeping Unit)', 'value' => 'sku'],
                        ['text' => 'Description', 'value' => 'description'],
                        ['text' => 'Category', 'value' => 'category']
                    ]
                ],
                [
                    'question' => 'What should you do before adding a new item to ensure no duplicates?',
                    'correct_answer' => 'search_existing',
                    'options' => [
                        ['text' => 'Search for existing similar items', 'value' => 'search_existing'],
                        ['text' => 'Add it immediately', 'value' => 'add_immediately'],
                        ['text' => 'Ask a colleague', 'value' => 'ask_colleague'],
                        ['text' => 'Skip the check', 'value' => 'skip_check']
                    ]
                ]
            ]
        ],
        // Submit Transaction
        [
            'scenario_id' => $scenario_ids[1],
            'questions' => [
                [
                    'question' => 'What are the three main types of inventory transactions?',
                    'correct_answer' => 'receipt_issue_adjustment',
                    'options' => [
                        ['text' => 'Receipt, Issue, and Adjustment', 'value' => 'receipt_issue_adjustment'],
                        ['text' => 'Add, Delete, and Modify', 'value' => 'add_delete_modify'],
                        ['text' => 'Buy, Sell, and Return', 'value' => 'buy_sell_return'],
                        ['text' => 'In, Out, and Transfer', 'value' => 'in_out_transfer']
                    ]
                ],
                [
                    'question' => 'When should you record an inventory adjustment?',
                    'correct_answer' => 'discrepancy_found',
                    'options' => [
                        ['text' => 'When a physical count shows discrepancy', 'value' => 'discrepancy_found'],
                        ['text' => 'Every day', 'value' => 'every_day'],
                        ['text' => 'Only at month end', 'value' => 'month_end'],
                        ['text' => 'Never', 'value' => 'never']
                    ]
                ],
                [
                    'question' => 'What information is required for a transaction entry?',
                    'correct_answer' => 'all_required',
                    'options' => [
                        ['text' => 'Item, quantity, and reason', 'value' => 'all_required'],
                        ['text' => 'Only item and quantity', 'value' => 'item_quantity'],
                        ['text' => 'Just the item name', 'value' => 'item_only'],
                        ['text' => 'Only the quantity', 'value' => 'quantity_only']
                    ]
                ]
            ]
        ],
        // Request Supplies
        [
            'scenario_id' => $scenario_ids[2],
            'questions' => [
                [
                    'question' => 'What is the first step when requesting supplies?',
                    'correct_answer' => 'check_stock',
                    'options' => [
                        ['text' => 'Check current stock levels', 'value' => 'check_stock'],
                        ['text' => 'Submit the request immediately', 'value' => 'submit_immediately'],
                        ['text' => 'Ask your manager', 'value' => 'ask_manager'],
                        ['text' => 'Wait for approval', 'value' => 'wait_approval']
                    ]
                ],
                [
                    'question' => 'Which reason code should you use for routine restocking?',
                    'correct_answer' => 'routine_restock',
                    'options' => [
                        ['text' => 'Routine restocking', 'value' => 'routine_restock'],
                        ['text' => 'Emergency request', 'value' => 'emergency'],
                        ['text' => 'Special event', 'value' => 'special_event'],
                        ['text' => 'Damaged items', 'value' => 'damaged']
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
        // Update Room Inventory
        [
            'scenario_id' => $scenario_ids[3],
            'questions' => [
                [
                    'question' => 'When should you update room inventory after housekeeping?',
                    'correct_answer' => 'immediately_after',
                    'options' => [
                        ['text' => 'Immediately after completing the room', 'value' => 'immediately_after'],
                        ['text' => 'At the end of the day', 'value' => 'end_day'],
                        ['text' => 'Once a week', 'value' => 'weekly'],
                        ['text' => 'Only when requested', 'value' => 'when_requested']
                    ]
                ],
                [
                    'question' => 'What should you do if items are missing from a room?',
                    'correct_answer' => 'report_missing',
                    'options' => [
                        ['text' => 'Report missing items immediately', 'value' => 'report_missing'],
                        ['text' => 'Ignore and move on', 'value' => 'ignore'],
                        ['text' => 'Wait for the next shift', 'value' => 'wait_shift'],
                        ['text' => 'Ask the guest', 'value' => 'ask_guest']
                    ]
                ],
                [
                    'question' => 'How do you handle damaged inventory items?',
                    'correct_answer' => 'record_damage',
                    'options' => [
                        ['text' => 'Record the damage and remove from inventory', 'value' => 'record_damage'],
                        ['text' => 'Hide the damaged items', 'value' => 'hide_items'],
                        ['text' => 'Continue using them', 'value' => 'continue_using'],
                        ['text' => 'Blame the previous shift', 'value' => 'blame_shift']
                    ]
                ]
            ]
        ],
        // View Reports
        [
            'scenario_id' => $scenario_ids[4],
            'questions' => [
                [
                    'question' => 'Which report shows current stock levels?',
                    'correct_answer' => 'inventory_status',
                    'options' => [
                        ['text' => 'Inventory Status Report', 'value' => 'inventory_status'],
                        ['text' => 'Transaction History', 'value' => 'transaction_history'],
                        ['text' => 'Usage Report', 'value' => 'usage_report'],
                        ['text' => 'Audit Trail', 'value' => 'audit_trail']
                    ]
                ],
                [
                    'question' => 'What does a low stock alert indicate?',
                    'correct_answer' => 'reorder_needed',
                    'options' => [
                        ['text' => 'Items need to be reordered', 'value' => 'reorder_needed'],
                        ['text' => 'Items are out of stock', 'value' => 'out_of_stock'],
                        ['text' => 'Items are overstocked', 'value' => 'overstocked'],
                        ['text' => 'Items are discontinued', 'value' => 'discontinued']
                    ]
                ],
                [
                    'question' => 'How often should you review inventory reports?',
                    'correct_answer' => 'daily',
                    'options' => [
                        ['text' => 'Daily for critical items', 'value' => 'daily'],
                        ['text' => 'Weekly', 'value' => 'weekly'],
                        ['text' => 'Monthly', 'value' => 'monthly'],
                        ['text' => 'Only when needed', 'value' => 'as_needed']
                    ]
                ]
            ]
        ],
        // Review Audit Logs
        [
            'scenario_id' => $scenario_ids[5],
            'questions' => [
                [
                    'question' => 'What information is tracked in audit logs?',
                    'correct_answer' => 'all_changes',
                    'options' => [
                        ['text' => 'All inventory changes and user actions', 'value' => 'all_changes'],
                        ['text' => 'Only deletions', 'value' => 'deletions_only'],
                        ['text' => 'Only additions', 'value' => 'additions_only'],
                        ['text' => 'Only modifications', 'value' => 'modifications_only']
                    ]
                ],
                [
                    'question' => 'Why are audit logs important for inventory management?',
                    'correct_answer' => 'compliance_tracking',
                    'options' => [
                        ['text' => 'For compliance and tracking changes', 'value' => 'compliance_tracking'],
                        ['text' => 'To increase system speed', 'value' => 'increase_speed'],
                        ['text' => 'To reduce storage space', 'value' => 'reduce_storage'],
                        ['text' => 'To improve user interface', 'value' => 'improve_ui']
                    ]
                ],
                [
                    'question' => 'How long should audit logs be retained?',
                    'correct_answer' => 'policy_period',
                    'options' => [
                        ['text' => 'According to company policy (usually 1-7 years)', 'value' => 'policy_period'],
                        ['text' => 'Only 30 days', 'value' => '30_days'],
                        ['text' => 'Only 1 year', 'value' => '1_year'],
                        ['text' => 'Indefinitely', 'value' => 'indefinitely']
                    ]
                ]
            ]
        ]
    ];
    
    // Insert questions and options
    foreach ($questions_data as $scenario_data) {
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
    
    // Question counts will be calculated dynamically in the API
    
    $pdo->commit();
    
    echo "Inventory training data setup completed successfully!\n";
    echo "Created " . count($scenarios) . " scenarios with questions and options.\n";
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error: " . $e->getMessage() . "\n";
}
?>
