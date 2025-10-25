<?php
/**
 * Add Manager Training Scenarios
 * Adds advanced manager-specific training scenarios to the database
 */

require_once __DIR__ . '/../includes/database.php';

try {
    $pdo->beginTransaction();
    
    // Manager-specific scenarios
    $manager_scenarios = [
        [
            'title' => 'Advanced Inventory Analytics',
            'description' => 'Master advanced inventory analytics, forecasting, and strategic decision making for optimal inventory management.',
            'scenario_type' => 'reporting',
            'difficulty' => 'advanced',
            'estimated_time' => 30,
            'points' => 25
        ],
        [
            'title' => 'System Administration',
            'description' => 'Learn system administration tasks including user management, system configuration, and security settings.',
            'scenario_type' => 'automation',
            'difficulty' => 'advanced',
            'estimated_time' => 25,
            'points' => 20
        ],
        [
            'title' => 'Performance Monitoring',
            'description' => 'Understand how to monitor system performance, analyze metrics, and optimize inventory operations.',
            'scenario_type' => 'monitoring',
            'difficulty' => 'intermediate',
            'estimated_time' => 20,
            'points' => 15
        ],
        [
            'title' => 'Strategic Planning',
            'description' => 'Develop skills in strategic inventory planning, budget management, and long-term forecasting.',
            'scenario_type' => 'inventory_management',
            'difficulty' => 'advanced',
            'estimated_time' => 35,
            'points' => 30
        ],
        [
            'title' => 'Approval Workflow Management',
            'description' => 'Master complex approval workflows, delegation, and multi-level authorization processes.',
            'scenario_type' => 'approval',
            'difficulty' => 'intermediate',
            'estimated_time' => 18,
            'points' => 12
        ]
    ];
    
    $scenario_ids = [];
    foreach ($manager_scenarios as $scenario) {
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
    
    // Sample questions for manager scenarios
    $manager_questions = [
        // Advanced Inventory Analytics
        [
            'scenario_id' => $scenario_ids[0],
            'questions' => [
                [
                    'question' => 'What is the primary purpose of ABC analysis in inventory management?',
                    'correct_answer' => 'prioritize_items',
                    'options' => [
                        ['text' => 'To prioritize items based on value and importance', 'value' => 'prioritize_items'],
                        ['text' => 'To count inventory items alphabetically', 'value' => 'alphabetical_count'],
                        ['text' => 'To organize items by category', 'value' => 'category_organize'],
                        ['text' => 'To track item locations', 'value' => 'track_locations']
                    ]
                ],
                [
                    'question' => 'Which forecasting method is best for seasonal inventory patterns?',
                    'correct_answer' => 'seasonal_decomposition',
                    'options' => [
                        ['text' => 'Seasonal decomposition', 'value' => 'seasonal_decomposition'],
                        ['text' => 'Simple moving average', 'value' => 'moving_average'],
                        ['text' => 'Linear regression', 'value' => 'linear_regression'],
                        ['text' => 'Exponential smoothing', 'value' => 'exponential_smoothing']
                    ]
                ],
                [
                    'question' => 'What does inventory turnover ratio measure?',
                    'correct_answer' => 'efficiency',
                    'options' => [
                        ['text' => 'How efficiently inventory is sold and replaced', 'value' => 'efficiency'],
                        ['text' => 'How much inventory costs', 'value' => 'cost'],
                        ['text' => 'How many items are in stock', 'value' => 'stock_count'],
                        ['text' => 'How fast items are delivered', 'value' => 'delivery_speed']
                    ]
                ]
            ]
        ],
        // System Administration
        [
            'scenario_id' => $scenario_ids[1],
            'questions' => [
                [
                    'question' => 'What is the first step in setting up user roles and permissions?',
                    'correct_answer' => 'analyze_requirements',
                    'options' => [
                        ['text' => 'Analyze business requirements and access needs', 'value' => 'analyze_requirements'],
                        ['text' => 'Create all users immediately', 'value' => 'create_users'],
                        ['text' => 'Set up default permissions', 'value' => 'default_permissions'],
                        ['text' => 'Configure system settings', 'value' => 'system_settings']
                    ]
                ],
                [
                    'question' => 'Which security practice is most important for system administration?',
                    'correct_answer' => 'regular_audits',
                    'options' => [
                        ['text' => 'Regular security audits and updates', 'value' => 'regular_audits'],
                        ['text' => 'Using simple passwords', 'value' => 'simple_passwords'],
                        ['text' => 'Sharing admin accounts', 'value' => 'shared_accounts'],
                        ['text' => 'Disabling logging', 'value' => 'disable_logging']
                    ]
                ],
                [
                    'question' => 'What should you do before making system configuration changes?',
                    'correct_answer' => 'backup_system',
                    'options' => [
                        ['text' => 'Create a system backup', 'value' => 'backup_system'],
                        ['text' => 'Notify all users', 'value' => 'notify_users'],
                        ['text' => 'Test in production', 'value' => 'test_production'],
                        ['text' => 'Make changes immediately', 'value' => 'immediate_changes']
                    ]
                ]
            ]
        ],
        // Performance Monitoring
        [
            'scenario_id' => $scenario_ids[2],
            'questions' => [
                [
                    'question' => 'Which metric is most important for inventory system performance?',
                    'correct_answer' => 'response_time',
                    'options' => [
                        ['text' => 'System response time', 'value' => 'response_time'],
                        ['text' => 'Number of users', 'value' => 'user_count'],
                        ['text' => 'Database size', 'value' => 'database_size'],
                        ['text' => 'Number of reports', 'value' => 'report_count']
                    ]
                ],
                [
                    'question' => 'What should you monitor to prevent system downtime?',
                    'correct_answer' => 'all_metrics',
                    'options' => [
                        ['text' => 'CPU usage, memory, disk space, and network', 'value' => 'all_metrics'],
                        ['text' => 'Only CPU usage', 'value' => 'cpu_only'],
                        ['text' => 'Only memory usage', 'value' => 'memory_only'],
                        ['text' => 'Only disk space', 'value' => 'disk_only']
                    ]
                ],
                [
                    'question' => 'How often should performance reports be reviewed?',
                    'correct_answer' => 'daily',
                    'options' => [
                        ['text' => 'Daily for critical systems', 'value' => 'daily'],
                        ['text' => 'Weekly', 'value' => 'weekly'],
                        ['text' => 'Monthly', 'value' => 'monthly'],
                        ['text' => 'Only when problems occur', 'value' => 'when_problems']
                    ]
                ]
            ]
        ],
        // Strategic Planning
        [
            'scenario_id' => $scenario_ids[3],
            'questions' => [
                [
                    'question' => 'What is the first step in strategic inventory planning?',
                    'correct_answer' => 'analyze_current_state',
                    'options' => [
                        ['text' => 'Analyze current inventory state and trends', 'value' => 'analyze_current_state'],
                        ['text' => 'Set arbitrary targets', 'value' => 'set_targets'],
                        ['text' => 'Purchase new equipment', 'value' => 'purchase_equipment'],
                        ['text' => 'Hire more staff', 'value' => 'hire_staff']
                    ]
                ],
                [
                    'question' => 'Which factor is most important in budget planning for inventory?',
                    'correct_answer' => 'demand_forecasting',
                    'options' => [
                        ['text' => 'Accurate demand forecasting', 'value' => 'demand_forecasting'],
                        ['text' => 'Current stock levels', 'value' => 'current_stock'],
                        ['text' => 'Supplier prices', 'value' => 'supplier_prices'],
                        ['text' => 'Storage capacity', 'value' => 'storage_capacity']
                    ]
                ],
                [
                    'question' => 'How should you approach long-term inventory forecasting?',
                    'correct_answer' => 'multiple_methods',
                    'options' => [
                        ['text' => 'Use multiple forecasting methods and compare results', 'value' => 'multiple_methods'],
                        ['text' => 'Use only historical data', 'value' => 'historical_only'],
                        ['text' => 'Guess based on intuition', 'value' => 'intuition'],
                        ['text' => 'Copy from competitors', 'value' => 'copy_competitors']
                    ]
                ]
            ]
        ],
        // Approval Workflow Management
        [
            'scenario_id' => $scenario_ids[4],
            'questions' => [
                [
                    'question' => 'What is the key principle in designing approval workflows?',
                    'correct_answer' => 'segregation_duties',
                    'options' => [
                        ['text' => 'Segregation of duties and appropriate authorization levels', 'value' => 'segregation_duties'],
                        ['text' => 'Speed over accuracy', 'value' => 'speed_accuracy'],
                        ['text' => 'Single person approval', 'value' => 'single_approval'],
                        ['text' => 'No documentation needed', 'value' => 'no_documentation']
                    ]
                ],
                [
                    'question' => 'When should you implement delegation in approval workflows?',
                    'correct_answer' => 'temporary_absence',
                    'options' => [
                        ['text' => 'When approvers are temporarily unavailable', 'value' => 'temporary_absence'],
                        ['text' => 'When you want to avoid responsibility', 'value' => 'avoid_responsibility'],
                        ['text' => 'When processes are too slow', 'value' => 'slow_processes'],
                        ['text' => 'When you want to reduce costs', 'value' => 'reduce_costs']
                    ]
                ],
                [
                    'question' => 'What should be documented in approval workflows?',
                    'correct_answer' => 'all_actions',
                    'options' => [
                        ['text' => 'All actions, decisions, and timestamps', 'value' => 'all_actions'],
                        ['text' => 'Only approvals', 'value' => 'approvals_only'],
                        ['text' => 'Only rejections', 'value' => 'rejections_only'],
                        ['text' => 'Only final decisions', 'value' => 'final_decisions']
                    ]
                ]
            ]
        ]
    ];
    
    // Insert questions and options for manager scenarios
    foreach ($manager_questions as $scenario_data) {
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
    
    echo "Manager training scenarios added successfully!\n";
    echo "Created " . count($manager_scenarios) . " manager-specific scenarios with questions and options.\n";
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error: " . $e->getMessage() . "\n";
}
?>
