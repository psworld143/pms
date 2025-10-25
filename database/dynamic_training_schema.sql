-- Dynamic Training System Schema
-- Hotel PMS Training System

-- Training modules table (already exists, but let's enhance it)
ALTER TABLE tutorial_modules ADD COLUMN IF NOT EXISTS icon VARCHAR(50) DEFAULT 'fas fa-graduation-cap';
ALTER TABLE tutorial_modules ADD COLUMN IF NOT EXISTS color VARCHAR(20) DEFAULT 'blue';
ALTER TABLE tutorial_modules ADD COLUMN IF NOT EXISTS prerequisites TEXT;
ALTER TABLE tutorial_modules ADD COLUMN IF NOT EXISTS learning_outcomes TEXT;

-- Training steps table
CREATE TABLE IF NOT EXISTS tutorial_steps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tutorial_module_id INT NOT NULL,
    step_number INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    step_type ENUM('introduction', 'learning', 'practical', 'quiz', 'simulation', 'assessment', 'summary') DEFAULT 'learning',
    duration_minutes INT DEFAULT 5,
    learning_objectives TEXT,
    interactive_data JSON,
    prerequisites TEXT,
    is_required BOOLEAN DEFAULT TRUE,
    order_index INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tutorial_module_id) REFERENCES tutorial_modules(id) ON DELETE CASCADE,
    UNIQUE KEY unique_module_step (tutorial_module_id, step_number)
);

-- Training step content types
CREATE TABLE IF NOT EXISTS tutorial_step_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tutorial_step_id INT NOT NULL,
    content_type ENUM('text', 'image', 'video', 'interactive', 'quiz', 'simulation', 'file') NOT NULL,
    content_data JSON NOT NULL,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tutorial_step_id) REFERENCES tutorial_steps(id) ON DELETE CASCADE
);

-- Training quizzes
CREATE TABLE IF NOT EXISTS tutorial_quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tutorial_step_id INT NOT NULL,
    question TEXT NOT NULL,
    question_type ENUM('multiple_choice', 'true_false', 'fill_blank', 'matching') DEFAULT 'multiple_choice',
    options JSON,
    correct_answer VARCHAR(500),
    explanation TEXT,
    points INT DEFAULT 1,
    time_limit INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tutorial_step_id) REFERENCES tutorial_steps(id) ON DELETE CASCADE
);

-- Training simulations
CREATE TABLE IF NOT EXISTS tutorial_simulations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tutorial_step_id INT NOT NULL,
    simulation_type ENUM('pos_order', 'inventory_check', 'booking_process', 'payment_processing') NOT NULL,
    simulation_data JSON NOT NULL,
    success_criteria JSON,
    instructions TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tutorial_step_id) REFERENCES tutorial_steps(id) ON DELETE CASCADE
);

-- Training resources
CREATE TABLE IF NOT EXISTS training_resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tutorial_module_id INT,
    tutorial_step_id INT,
    resource_type ENUM('document', 'video', 'image', 'link', 'file') NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    file_path VARCHAR(500),
    file_size INT,
    mime_type VARCHAR(100),
    is_public BOOLEAN DEFAULT FALSE,
    download_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tutorial_module_id) REFERENCES tutorial_modules(id) ON DELETE CASCADE,
    FOREIGN KEY (tutorial_step_id) REFERENCES tutorial_steps(id) ON DELETE CASCADE
);

-- Training categories
CREATE TABLE IF NOT EXISTS training_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50) DEFAULT 'fas fa-folder',
    color VARCHAR(20) DEFAULT 'gray',
    parent_id INT NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES training_categories(id) ON DELETE SET NULL
);

-- Add category to modules
ALTER TABLE tutorial_modules ADD COLUMN IF NOT EXISTS category_id INT;
ALTER TABLE tutorial_modules ADD FOREIGN KEY (category_id) REFERENCES training_categories(id) ON DELETE SET NULL;

-- Training tags
CREATE TABLE IF NOT EXISTS training_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    color VARCHAR(20) DEFAULT 'blue',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Module tags relationship
CREATE TABLE IF NOT EXISTS tutorial_module_tags (
    tutorial_module_id INT NOT NULL,
    training_tag_id INT NOT NULL,
    PRIMARY KEY (tutorial_module_id, training_tag_id),
    FOREIGN KEY (tutorial_module_id) REFERENCES tutorial_modules(id) ON DELETE CASCADE,
    FOREIGN KEY (training_tag_id) REFERENCES training_tags(id) ON DELETE CASCADE
);

-- Training prerequisites
CREATE TABLE IF NOT EXISTS training_prerequisites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tutorial_module_id INT NOT NULL,
    prerequisite_module_id INT NOT NULL,
    prerequisite_type ENUM('module_completion', 'step_completion', 'assessment_pass') DEFAULT 'module_completion',
    required_score DECIMAL(5,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tutorial_module_id) REFERENCES tutorial_modules(id) ON DELETE CASCADE,
    FOREIGN KEY (prerequisite_module_id) REFERENCES tutorial_modules(id) ON DELETE CASCADE
);

-- Insert sample categories
INSERT IGNORE INTO training_categories (id, name, description, icon, color) VALUES
(1, 'Point of Sale', 'POS system training modules', 'fas fa-cash-register', 'green'),
(2, 'Inventory Management', 'Inventory and stock management training', 'fas fa-boxes', 'blue'),
(3, 'Booking System', 'Reservation and booking management', 'fas fa-calendar-check', 'purple'),
(4, 'General Training', 'General hotel operations training', 'fas fa-graduation-cap', 'gray');

-- Insert sample tags
INSERT IGNORE INTO training_tags (id, name, color, description) VALUES
(1, 'Beginner', 'green', 'Suitable for beginners'),
(2, 'Intermediate', 'yellow', 'Intermediate level training'),
(3, 'Advanced', 'red', 'Advanced level training'),
(4, 'Essential', 'blue', 'Essential training for all staff'),
(5, 'Optional', 'gray', 'Optional training modules');

-- Update existing modules with categories
UPDATE tutorial_modules SET category_id = 1 WHERE module_type = 'pos';
UPDATE tutorial_modules SET category_id = 2 WHERE module_type = 'inventory';
UPDATE tutorial_modules SET category_id = 3 WHERE module_type = 'booking';

-- Insert sample dynamic steps for POS System Basics
INSERT IGNORE INTO tutorial_steps (tutorial_module_id, step_number, title, content, step_type, duration_minutes, learning_objectives, interactive_data, order_index) VALUES
(1, 1, 'Welcome to POS Training', 'Welcome to the Point of Sale (POS) System training! In this comprehensive module, you will learn the fundamentals of processing orders, handling payments, and managing transactions in a hotel environment. This training is designed to give you hands-on experience with real-world scenarios.', 'introduction', 3, '["Understand the POS interface", "Learn basic navigation", "Familiarize with system layout"]', '{"type": "welcome", "features": ["Interactive interface", "Real-time feedback", "Progress tracking"]}', 1),
(1, 2, 'Understanding the POS Interface', 'The POS interface is designed for efficiency and ease of use. Key components include the menu display, order summary, payment processing, and customer information sections. Each area has specific functions that work together to create a seamless transaction experience.', 'learning', 4, '["Identify main interface components", "Understand menu layout", "Navigate between sections"]', '{"type": "interface_tour", "components": [{"name": "Menu Display", "description": "Shows available items with prices"}, {"name": "Order Summary", "description": "Displays current order items and total"}]}', 2),
(1, 3, 'Creating a New Order', 'Starting a new order is the first step in the sales process. You will learn how to initiate orders, select items, and manage the order flow efficiently. This includes handling different customer types and special requests.', 'practical', 5, '["Start new orders", "Add items to orders", "Modify order contents"]', '{"type": "order_simulation", "steps": ["Click New Order", "Select customer type", "Add items", "Review order"]}', 3),
(1, 4, 'Adding Items to Order', 'Learn how to efficiently add items to orders, apply modifications, and handle special requests from customers. This includes understanding menu categories, item variations, and pricing structures.', 'practical', 6, '["Add menu items", "Apply modifications", "Handle special requests"]', '{"type": "item_selection", "categories": ["Food", "Beverages", "Desserts"]}', 4),
(1, 5, 'Processing Payments', 'Payment processing is crucial for completing transactions. Learn about different payment methods, handling cash, processing card payments, and applying discounts or promotions.', 'practical', 7, '["Process cash payments", "Handle card transactions", "Apply discounts and promotions"]', '{"type": "payment_simulation", "methods": ["Cash", "Credit Card", "Debit Card", "Mobile Payment"]}', 5),
(1, 6, 'Generating Receipts', 'Receipts provide customers with transaction records and help with accounting. Learn about different receipt types, when to use them, and how to handle receipt reprints or modifications.', 'learning', 4, '["Generate customer receipts", "Print kitchen orders", "Handle receipt reprints"]', '{"type": "receipt_demo", "types": ["Customer Receipt", "Kitchen Order", "Manager Copy"]}', 6),
(1, 7, 'Handling Refunds', 'Refunds are sometimes necessary for customer satisfaction. Learn the proper procedures for processing refunds, handling return items, and maintaining accurate records for accounting purposes.', 'practical', 5, '["Process refunds", "Handle return items", "Maintain refund records"]', '{"type": "refund_process", "scenarios": ["Item return", "Service issue", "Price adjustment"]}', 7),
(1, 8, 'POS Best Practices', 'Learn essential best practices for efficient POS operations, security measures, and customer service excellence. This includes maintaining accuracy, following security protocols, and providing excellent customer service.', 'summary', 6, '["Follow security protocols", "Maintain accuracy", "Provide excellent service"]', '{"type": "best_practices", "checklist": ["Verify orders", "Secure cash drawer", "Follow policies", "Clean workspace"]}', 8);

-- Insert sample quiz for step 4
INSERT IGNORE INTO tutorial_quizzes (tutorial_step_id, question, question_type, options, correct_answer, explanation, points) VALUES
(4, 'What should you do when a customer requests a modification to a menu item?', 'multiple_choice', '["Tell them it is not possible", "Add the modification note to the order", "Charge extra without asking", "Ignore the request"]', 'Add the modification note to the order', 'Always add modification notes to ensure the kitchen prepares the item correctly and meets customer expectations.', 1);

-- Insert sample simulation for step 3
INSERT IGNORE INTO tutorial_simulations (tutorial_step_id, simulation_type, simulation_data, success_criteria, instructions) VALUES
(3, 'pos_order', '{"scenarios": [{"customer_type": "walk_in", "items": ["Coffee", "Sandwich"], "total": 12.50}, {"customer_type": "hotel_guest", "items": ["Breakfast", "Juice"], "total": 18.75}]}', '{"completion_time": 300, "accuracy": 100}', 'Complete the order process for each scenario within the time limit and with 100% accuracy.');

-- Insert sample resources
INSERT IGNORE INTO training_resources (tutorial_module_id, resource_type, title, description, file_path, is_public) VALUES
(1, 'document', 'POS Quick Reference Guide', 'A quick reference guide for common POS operations', '/resources/pos-quick-reference.pdf', TRUE),
(1, 'video', 'POS System Overview', 'Video introduction to the POS system interface', '/resources/pos-overview.mp4', TRUE),
(1, 'link', 'POS Support Documentation', 'Link to comprehensive POS documentation', 'https://support.example.com/pos', TRUE);

