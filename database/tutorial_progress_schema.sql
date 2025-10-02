-- Tutorial Progress Tracking Schema
-- Hotel PMS Training System

-- Student tutorial progress table
CREATE TABLE IF NOT EXISTS tutorial_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    module_name VARCHAR(100) NOT NULL,
    module_type ENUM('pos', 'inventory', 'booking', 'management') NOT NULL,
    progress_percentage DECIMAL(5,2) DEFAULT 0.00,
    current_step INT DEFAULT 1,
    total_steps INT DEFAULT 1,
    status ENUM('not_started', 'in_progress', 'completed') DEFAULT 'not_started',
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    last_accessed TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_module (user_id, module_name)
);

-- Tutorial assessments table
CREATE TABLE IF NOT EXISTS tutorial_assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    module_name VARCHAR(100) NOT NULL,
    score DECIMAL(5,2) DEFAULT 0.00,
    total_questions INT DEFAULT 0,
    correct_answers INT DEFAULT 0,
    passed BOOLEAN DEFAULT FALSE,
    time_taken INT DEFAULT 0, -- in seconds
    assessment_data JSON, -- store detailed assessment results
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tutorial analytics table
CREATE TABLE IF NOT EXISTS tutorial_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action_type ENUM('module_start', 'module_complete', 'assessment_taken', 'time_spent') NOT NULL,
    module_name VARCHAR(100),
    action_data JSON, -- store additional action data
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert demo student account
INSERT IGNORE INTO users (id, name, email, password, role, created_at) 
VALUES (999, 'Demo Student', 'demo@student.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', NOW());

-- Insert sample tutorial progress for demo student
INSERT IGNORE INTO tutorial_progress (user_id, module_name, module_type, progress_percentage, current_step, total_steps, status, started_at) 
VALUES 
(999, 'POS System Basics', 'pos', 25.00, 2, 8, 'in_progress', NOW()),
(999, 'Inventory Management Fundamentals', 'inventory', 0.00, 1, 10, 'not_started', NULL),
(999, 'Booking System Training', 'booking', 0.00, 1, 12, 'not_started', NULL);
