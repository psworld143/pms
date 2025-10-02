-- Tutorial System Database Schema
-- Hotel PMS Training System - Interactive Tutorials

-- Create tutorial_modules table
CREATE TABLE IF NOT EXISTS tutorial_modules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    module_type ENUM('pos', 'inventory', 'booking') NOT NULL,
    difficulty_level ENUM('beginner', 'intermediate', 'advanced') NOT NULL,
    estimated_duration INT NOT NULL COMMENT 'Duration in minutes',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Constraints
    CONSTRAINT chk_estimated_duration CHECK (estimated_duration > 0),
    CONSTRAINT chk_name_length CHECK (CHAR_LENGTH(name) >= 3)
);

-- Create tutorial_steps table
CREATE TABLE IF NOT EXISTS tutorial_steps (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tutorial_module_id INT NOT NULL,
    step_number INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    instruction TEXT NOT NULL,
    target_element VARCHAR(200) COMMENT 'CSS selector for highlighting',
    action_type ENUM('click', 'input', 'select', 'navigate', 'simulate') NOT NULL,
    expected_result TEXT,
    is_interactive BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign key constraint
    FOREIGN KEY (tutorial_module_id) REFERENCES tutorial_modules(id) ON DELETE CASCADE,
    
    -- Constraints
    CONSTRAINT chk_step_number CHECK (step_number > 0),
    CONSTRAINT chk_title_length CHECK (CHAR_LENGTH(title) >= 3),
    CONSTRAINT chk_instruction_length CHECK (CHAR_LENGTH(instruction) >= 10),
    
    -- Unique constraint for module and step combination
    UNIQUE KEY unique_module_step (tutorial_module_id, step_number)
);

-- Create tutorial_progress table
CREATE TABLE IF NOT EXISTS tutorial_progress (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    tutorial_module_id INT NOT NULL,
    current_step INT DEFAULT 1,
    completion_percentage DECIMAL(5,2) DEFAULT 0.00,
    time_spent INT DEFAULT 0 COMMENT 'Time in seconds',
    score DECIMAL(5,2) DEFAULT 0.00,
    status ENUM('not_started', 'in_progress', 'completed', 'paused') DEFAULT 'not_started',
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    last_accessed TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key constraints
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (tutorial_module_id) REFERENCES tutorial_modules(id) ON DELETE CASCADE,
    
    -- Constraints
    CONSTRAINT chk_completion_percentage CHECK (completion_percentage >= 0 AND completion_percentage <= 100),
    CONSTRAINT chk_time_spent CHECK (time_spent >= 0),
    CONSTRAINT chk_score CHECK (score >= 0 AND score <= 100),
    CONSTRAINT chk_current_step CHECK (current_step > 0),
    
    -- Unique constraint for user and module combination
    UNIQUE KEY unique_user_tutorial (user_id, tutorial_module_id)
);

-- Create tutorial_assessments table
CREATE TABLE IF NOT EXISTS tutorial_assessments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tutorial_step_id INT NOT NULL,
    question TEXT NOT NULL,
    question_type ENUM('multiple_choice', 'true_false', 'fill_blank', 'simulation') NOT NULL,
    options JSON COMMENT 'For multiple choice questions',
    correct_answer TEXT NOT NULL,
    explanation TEXT,
    points INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign key constraint
    FOREIGN KEY (tutorial_step_id) REFERENCES tutorial_steps(id) ON DELETE CASCADE,
    
    -- Constraints
    CONSTRAINT chk_question_length CHECK (CHAR_LENGTH(question) >= 10),
    CONSTRAINT chk_points CHECK (points > 0)
);

-- Create tutorial_analytics table
CREATE TABLE IF NOT EXISTS tutorial_analytics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    tutorial_module_id INT NOT NULL,
    action_type ENUM('start', 'step_complete', 'assessment_complete', 'pause', 'resume', 'complete') NOT NULL,
    step_id INT NULL,
    time_spent INT DEFAULT 0 COMMENT 'Time in seconds',
    score DECIMAL(5,2) NULL,
    metadata JSON COMMENT 'Additional tracking data',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign key constraints
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (tutorial_module_id) REFERENCES tutorial_modules(id) ON DELETE CASCADE,
    FOREIGN KEY (step_id) REFERENCES tutorial_steps(id) ON DELETE SET NULL,
    
    -- Constraints
    CONSTRAINT chk_analytics_time_spent CHECK (time_spent >= 0),
    CONSTRAINT chk_analytics_score CHECK (score IS NULL OR (score >= 0 AND score <= 100))
);

-- Create indexes for performance optimization
CREATE INDEX idx_tutorial_progress_user_id ON tutorial_progress(user_id);
CREATE INDEX idx_tutorial_progress_module_id ON tutorial_progress(tutorial_module_id);
CREATE INDEX idx_tutorial_progress_status ON tutorial_progress(status);
CREATE INDEX idx_tutorial_steps_module_id ON tutorial_steps(tutorial_module_id);
CREATE INDEX idx_tutorial_steps_step_number ON tutorial_steps(tutorial_module_id, step_number);
CREATE INDEX idx_tutorial_analytics_user_id ON tutorial_analytics(user_id);
CREATE INDEX idx_tutorial_analytics_module_id ON tutorial_analytics(tutorial_module_id);
CREATE INDEX idx_tutorial_analytics_action_type ON tutorial_analytics(action_type);
CREATE INDEX idx_tutorial_analytics_created_at ON tutorial_analytics(created_at);

-- Insert sample tutorial modules
INSERT INTO tutorial_modules (name, description, module_type, difficulty_level, estimated_duration) VALUES
('POS System Basics', 'Learn fundamental point of sale operations including order processing, payment handling, and receipt generation', 'pos', 'beginner', 30),
('Inventory Management Fundamentals', 'Master stock control, supplier relations, and automated reordering systems', 'inventory', 'beginner', 45),
('Front Desk Operations', 'Essential guest management, reservations, and check-in/out procedures', 'booking', 'beginner', 40),
('Advanced POS Techniques', 'Complex order modifications, refunds, and multi-payment processing', 'pos', 'intermediate', 50),
('Inventory Cost Analysis', 'Advanced cost management, profit margins, and supplier performance analysis', 'inventory', 'intermediate', 60),
('Revenue Management', 'Advanced booking strategies, pricing optimization, and occupancy management', 'booking', 'intermediate', 55),
('Enterprise POS Operations', 'Multi-location management, advanced reporting, and system integration', 'pos', 'advanced', 75),
('Strategic Inventory Planning', 'Demand forecasting, procurement optimization, and supply chain management', 'inventory', 'advanced', 90),
('Hotel Revenue Optimization', 'Advanced revenue management, market analysis, and competitive positioning', 'booking', 'advanced', 85);

-- Insert sample tutorial steps for POS System Basics
INSERT INTO tutorial_steps (tutorial_module_id, step_number, title, description, instruction, target_element, action_type, is_interactive) VALUES
(1, 1, 'Welcome to POS System', 'Introduction to point of sale operations', 'Click on the "New Order" button to start a new transaction', '#new-order-btn', 'click', true),
(1, 2, 'Add Items to Order', 'Learn how to add menu items to an order', 'Select items from the menu by clicking on them', '.menu-item', 'click', true),
(1, 3, 'Process Payment', 'Complete the transaction with payment processing', 'Click the "Process Payment" button to finalize the order', '#process-payment-btn', 'click', true),
(1, 4, 'Generate Receipt', 'Print or display the transaction receipt', 'Click "Print Receipt" to complete the transaction', '#print-receipt-btn', 'click', true);

-- Insert sample tutorial steps for Inventory Management Fundamentals
INSERT INTO tutorial_steps (tutorial_module_id, step_number, title, description, instruction, target_element, action_type, is_interactive) VALUES
(2, 1, 'Inventory Dashboard Overview', 'Understanding the inventory management interface', 'Navigate to the Inventory Dashboard to view current stock levels', '#inventory-dashboard', 'navigate', true),
(2, 2, 'Check Stock Levels', 'Learn how to monitor inventory quantities', 'Click on any item to view detailed stock information', '.inventory-item', 'click', true),
(2, 3, 'Add New Inventory', 'Add new items to the inventory system', 'Click "Add Item" to create a new inventory entry', '#add-item-btn', 'click', true),
(2, 4, 'Update Stock Quantities', 'Modify existing inventory quantities', 'Use the "Update Stock" feature to adjust quantities', '#update-stock-btn', 'click', true);

-- Insert sample tutorial steps for Front Desk Operations
INSERT INTO tutorial_steps (tutorial_module_id, step_number, title, description, instruction, target_element, action_type, is_interactive) VALUES
(3, 1, 'Guest Check-in Process', 'Learn the standard guest check-in procedure', 'Click "Check-in Guest" to start the check-in process', '#checkin-guest-btn', 'click', true),
(3, 2, 'Room Assignment', 'Assign rooms to arriving guests', 'Select an available room from the room assignment interface', '.room-option', 'click', true),
(3, 3, 'Guest Information Management', 'Update and manage guest profiles', 'Click on guest details to edit their information', '#guest-details', 'click', true),
(3, 4, 'Check-out Process', 'Complete guest departure procedures', 'Click "Check-out" to finalize the guest stay', '#checkout-btn', 'click', true);

-- Insert sample assessments
INSERT INTO tutorial_assessments (tutorial_step_id, question, question_type, correct_answer, explanation, points) VALUES
(1, 'What is the first step in processing a POS order?', 'multiple_choice', 'Click New Order', 'Starting a new order is the first step in any POS transaction', 1),
(2, 'True or False: You can add multiple items to a single order', 'true_false', 'True', 'POS systems allow multiple items to be added to a single order', 1),
(3, 'What happens when you click "Process Payment"?', 'multiple_choice', 'Transaction is finalized', 'Processing payment completes the transaction and updates inventory', 1),
(5, 'What does the Inventory Dashboard show?', 'multiple_choice', 'Current stock levels', 'The dashboard displays real-time inventory quantities and status', 1),
(6, 'True or False: You can only view inventory items, not modify them', 'true_false', 'False', 'Inventory systems allow both viewing and modifying stock levels', 1),
(9, 'What is the first step in guest check-in?', 'multiple_choice', 'Click Check-in Guest', 'Starting the check-in process is the first step in guest arrival', 1),
(10, 'True or False: Room assignment is optional during check-in', 'true_false', 'False', 'Room assignment is required to complete the check-in process', 1);
