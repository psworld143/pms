-- Create Demo Student Account
-- Hotel PMS Training System

-- Insert demo student account
INSERT IGNORE INTO users (id, name, email, password, role, is_active, created_at) 
VALUES (
    999, 
    'Demo Student', 
    'demo@student.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'student', 
    1, 
    NOW()
);

-- The password hash above corresponds to 'demo123'
-- You can verify this with: password_verify('demo123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')

-- Insert sample tutorial progress for demo student
INSERT IGNORE INTO tutorial_progress (user_id, module_name, module_type, progress_percentage, current_step, total_steps, status, started_at) 
VALUES 
(999, 'POS System Basics', 'pos', 25.00, 2, 8, 'in_progress', NOW()),
(999, 'Inventory Management Fundamentals', 'inventory', 0.00, 1, 10, 'not_started', NULL),
(999, 'Booking System Training', 'booking', 0.00, 1, 12, 'not_started', NULL),
(999, 'Enterprise POS Operations', 'pos', 0.00, 1, 15, 'not_started', NULL);

-- Insert sample tutorial analytics
INSERT IGNORE INTO tutorial_analytics (user_id, action_type, module_name, action_data, created_at) 
VALUES 
(999, 'module_start', 'POS System Basics', '{"step": 1, "timestamp": "2024-01-15 10:00:00"}', '2024-01-15 10:00:00'),
(999, 'module_start', 'POS System Basics', '{"step": 2, "timestamp": "2024-01-15 10:30:00"}', '2024-01-15 10:30:00'),
(999, 'time_spent', 'POS System Basics', '{"duration": 1800, "step": 2}', '2024-01-15 11:00:00');
