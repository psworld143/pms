-- Inventory Management System Database Schema
-- Hotel PMS Training System - Inventory Module
-- For Student Training in Hospitality Management
-- Uses hotel_pms_clean database

-- Create inventory categories table
CREATE TABLE IF NOT EXISTS `inventory_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `color` varchar(7) DEFAULT '#10B981',
  `icon` varchar(50) DEFAULT 'fas fa-box',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create inventory items table
CREATE TABLE IF NOT EXISTS `inventory_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `sku` varchar(50) UNIQUE,
  `category_id` int(11) NOT NULL,
  `description` text,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `minimum_stock` int(11) NOT NULL DEFAULT 10,
  `maximum_stock` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT 0.00,
  `cost_price` decimal(10,2) DEFAULT 0.00,
  `supplier` varchar(100) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `unit` varchar(20) DEFAULT 'pcs',
  `barcode` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','discontinued') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `sku` (`sku`),
  KEY `status` (`status`),
  FOREIGN KEY (`category_id`) REFERENCES `inventory_categories`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create inventory transactions table
CREATE TABLE IF NOT EXISTS `inventory_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `transaction_type` enum('in','out','adjustment','transfer','return') NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `total_value` decimal(10,2) DEFAULT NULL,
  `reason` varchar(200) DEFAULT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `notes` text,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `transaction_type` (`transaction_type`),
  KEY `user_id` (`user_id`),
  KEY `created_at` (`created_at`),
  FOREIGN KEY (`item_id`) REFERENCES `inventory_items`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create inventory requests table (for training scenarios)
CREATE TABLE IF NOT EXISTS `inventory_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_number` varchar(20) NOT NULL,
  `requested_by` int(11) NOT NULL,
  `department` varchar(50) NOT NULL,
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `status` enum('pending','approved','rejected','fulfilled','cancelled') DEFAULT 'pending',
  `request_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `required_date` date DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `request_number` (`request_number`),
  KEY `requested_by` (`requested_by`),
  KEY `approved_by` (`approved_by`),
  KEY `status` (`status`),
  FOREIGN KEY (`requested_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create inventory request items table
CREATE TABLE IF NOT EXISTS `inventory_request_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity_requested` int(11) NOT NULL,
  `quantity_approved` int(11) DEFAULT NULL,
  `quantity_issued` int(11) DEFAULT 0,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `request_id` (`request_id`),
  KEY `item_id` (`item_id`),
  FOREIGN KEY (`request_id`) REFERENCES `inventory_requests`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`item_id`) REFERENCES `inventory_items`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create inventory suppliers table
CREATE TABLE IF NOT EXISTS `inventory_suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `payment_terms` varchar(50) DEFAULT NULL,
  `rating` decimal(2,1) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create inventory training scenarios table
CREATE TABLE IF NOT EXISTS `inventory_training_scenarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `scenario_type` enum('stock_management','reorder_process','inventory_audit','supplier_management','cost_control') NOT NULL,
  `difficulty` enum('beginner','intermediate','advanced') NOT NULL,
  `estimated_time` int(11) NOT NULL DEFAULT 15,
  `points` int(11) NOT NULL DEFAULT 10,
  `instructions` text,
  `expected_outcome` text,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create inventory training progress table
CREATE TABLE IF NOT EXISTS `inventory_training_progress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `scenario_id` int(11) NOT NULL,
  `status` enum('not_started','in_progress','completed','failed') DEFAULT 'not_started',
  `score` int(11) DEFAULT NULL,
  `time_taken` int(11) DEFAULT NULL,
  `attempts` int(11) DEFAULT 1,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `feedback` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_scenario` (`user_id`, `scenario_id`),
  KEY `scenario_id` (`scenario_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`scenario_id`) REFERENCES `inventory_training_scenarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default categories
INSERT INTO `inventory_categories` (`name`, `description`, `color`, `icon`) VALUES
('Linens & Bedding', 'Bed sheets, pillowcases, blankets, towels', '#10B981', 'fas fa-bed'),
('Cleaning Supplies', 'Detergents, sanitizers, cleaning tools', '#3B82F6', 'fas fa-broom'),
('Amenities', 'Toiletries, coffee, tea, snacks', '#F59E0B', 'fas fa-gift'),
('Maintenance', 'Tools, spare parts, equipment', '#EF4444', 'fas fa-tools'),
('Food & Beverage', 'Restaurant supplies, kitchen items', '#8B5CF6', 'fas fa-utensils'),
('Office Supplies', 'Paper, pens, stationery', '#6B7280', 'fas fa-clipboard');

-- Insert sample inventory items
INSERT INTO `inventory_items` (`name`, `sku`, `category_id`, `description`, `quantity`, `minimum_stock`, `unit_price`, `cost_price`, `supplier`, `location`, `unit`) VALUES
('White Bath Towels', 'BT001', 1, 'Premium white bath towels, 100% cotton', 150, 20, 25.00, 15.00, 'Linen Supply Co.', 'Housekeeping Storage', 'pcs'),
('King Size Bed Sheets', 'BS001', 1, 'King size bed sheets, white, 300 thread count', 80, 15, 45.00, 30.00, 'Linen Supply Co.', 'Housekeeping Storage', 'sets'),
('All-Purpose Cleaner', 'AC001', 2, 'Multi-surface cleaner, 1 gallon', 25, 5, 12.00, 8.00, 'Cleaning Supplies Inc.', 'Housekeeping Storage', 'bottles'),
('Toilet Paper', 'TP001', 3, 'Premium 2-ply toilet paper, 12 rolls', 200, 30, 8.50, 5.50, 'Amenity Solutions', 'Housekeeping Storage', 'packs'),
('Coffee Pods', 'CP001', 3, 'Premium coffee pods, variety pack', 500, 50, 0.75, 0.45, 'Beverage Supply Co.', 'Kitchen Storage', 'pods'),
('Light Bulbs', 'LB001', 4, 'LED light bulbs, 60W equivalent', 100, 20, 5.00, 3.00, 'Maintenance Supply', 'Maintenance Room', 'pcs'),
('Printer Paper', 'PP001', 6, 'A4 white paper, 500 sheets', 50, 10, 4.50, 3.00, 'Office Depot', 'Office Storage', 'reams');

-- Insert training scenarios
INSERT INTO `inventory_training_scenarios` (`title`, `description`, `scenario_type`, `difficulty`, `estimated_time`, `points`, `instructions`, `expected_outcome`) VALUES
('Stock Replenishment', 'A guest requests extra towels but housekeeping reports low stock. You need to check inventory levels and reorder supplies.', 'stock_management', 'beginner', 10, 15, '1. Check current towel inventory levels\n2. Identify which items are below minimum stock\n3. Create a reorder request\n4. Update inventory records', 'Successfully identify low stock items and create reorder request'),
('Inventory Audit', 'Monthly inventory audit is due. You need to conduct a physical count and reconcile with system records.', 'inventory_audit', 'intermediate', 20, 25, '1. Print current inventory report\n2. Conduct physical count of all items\n3. Identify discrepancies\n4. Update system records\n5. Generate audit report', 'Complete accurate inventory audit with proper documentation'),
('Supplier Management', 'A supplier has delivered damaged goods. You need to handle the return and find an alternative supplier.', 'supplier_management', 'advanced', 15, 20, '1. Document the damaged items\n2. Contact supplier for return\n3. Research alternative suppliers\n4. Update supplier records\n5. Process return transaction', 'Successfully handle supplier issue and maintain inventory flow'),
('Cost Control Analysis', 'Management wants to reduce inventory costs. Analyze current spending and identify cost-saving opportunities.', 'cost_control', 'advanced', 25, 30, '1. Generate cost analysis report\n2. Identify high-cost items\n3. Research alternative suppliers\n4. Calculate potential savings\n5. Present recommendations', 'Provide detailed cost analysis with actionable recommendations');
