-- Ultra Safe Schema Fix for Inventory Management System
-- Hotel PMS Training System - Inventory Module
-- This script handles all possible database states safely

-- Step 1: Create inventory_categories table if it doesn't exist
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

-- Step 2: Create inventory_items table if it doesn't exist
CREATE TABLE IF NOT EXISTS `inventory_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(200) NOT NULL,
  `sku` varchar(50) UNIQUE,
  `category_id` int(11) NOT NULL,
  `description` text,
  `current_stock` int(11) NOT NULL DEFAULT 0,
  `minimum_stock` int(11) NOT NULL DEFAULT 10,
  `maximum_stock` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT 0.00,
  `cost_price` decimal(10,2) DEFAULT 0.00,
  `supplier` varchar(100) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `unit` varchar(20) DEFAULT 'pcs',
  `barcode` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_pos_product` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','inactive','discontinued') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_updated` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `sku` (`sku`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 3: Create inventory_transactions table if it doesn't exist
CREATE TABLE IF NOT EXISTS `inventory_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `transaction_type` enum('in','out','transfer','adjustment') NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) DEFAULT 0.00,
  `total_value` decimal(10,2) DEFAULT 0.00,
  `reason` text,
  `performed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `transaction_type` (`transaction_type`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 4: Add missing columns to existing inventory_items table (if it exists)
-- This will only add columns that don't already exist
ALTER TABLE `inventory_items` 
ADD COLUMN IF NOT EXISTS `item_name` varchar(200) NOT NULL DEFAULT '';

ALTER TABLE `inventory_items` 
ADD COLUMN IF NOT EXISTS `current_stock` int(11) NOT NULL DEFAULT 0;

ALTER TABLE `inventory_items` 
ADD COLUMN IF NOT EXISTS `is_pos_product` tinyint(1) NOT NULL DEFAULT 0;

ALTER TABLE `inventory_items` 
ADD COLUMN IF NOT EXISTS `last_updated` timestamp NULL DEFAULT NULL;

-- Step 5: Create other required tables
CREATE TABLE IF NOT EXISTS `inventory_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(200) NOT NULL,
  `quantity_requested` int(11) NOT NULL,
  `department` varchar(100) NOT NULL,
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `requested_by` int(11) NOT NULL,
  `requested_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `processed_by` int(11) DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `requested_by` (`requested_by`),
  KEY `processed_by` (`processed_by`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `reorder_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `reorder_point` int(11) NOT NULL,
  `reorder_quantity` int(11) NOT NULL,
  `lead_time_days` int(11) NOT NULL DEFAULT 7,
  `supplier_id` int(11) DEFAULT NULL,
  `auto_generate_po` tinyint(1) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `supplier_id` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `purchase_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `po_number` varchar(50) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `status` enum('pending','approved','ordered','received','cancelled') DEFAULT 'pending',
  `order_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expected_delivery_date` timestamp NULL DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `notes` text,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `po_number` (`po_number`),
  KEY `supplier_id` (`supplier_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `purchase_order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `po_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `line_total` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `po_id` (`po_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `floors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `floor_number` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `description` text,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `floor_number` (`floor_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_number` varchar(20) NOT NULL,
  `floor_id` int(11) NOT NULL,
  `room_type` varchar(50) DEFAULT 'standard',
  `status` enum('available','occupied','maintenance','out_of_order') DEFAULT 'available',
  `max_occupancy` int(11) DEFAULT 2,
  `last_audited_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_number` (`room_number`),
  KEY `floor_id` (`floor_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `room_inventory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity_allocated` int(11) NOT NULL DEFAULT 0,
  `quantity_current` int(11) NOT NULL DEFAULT 0,
  `par_level` int(11) NOT NULL DEFAULT 1,
  `last_restocked` timestamp NULL DEFAULT NULL,
  `last_audited` timestamp NULL DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_item` (`room_id`, `item_id`),
  KEY `room_id` (`room_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 6: Insert sample data
INSERT IGNORE INTO `inventory_categories` (`id`, `name`, `description`) VALUES
(1, 'Bathroom Amenities', 'Towels, soap, shampoo, and other bathroom items'),
(2, 'Bedding', 'Sheets, pillows, blankets, and bed accessories'),
(3, 'Cleaning Supplies', 'Detergents, disinfectants, and cleaning tools'),
(4, 'Electronics', 'TVs, phones, and electronic devices'),
(5, 'Food & Beverage', 'Food items and beverages for room service'),
(6, 'Furniture', 'Chairs, tables, and other furniture items'),
(7, 'Kitchen Supplies', 'Kitchen utensils and appliances'),
(8, 'Maintenance', 'Tools and maintenance equipment'),
(9, 'Office Supplies', 'Paper, pens, and office equipment'),
(10, 'Safety Equipment', 'Fire extinguishers, first aid kits, and safety items');

INSERT IGNORE INTO `floors` (`id`, `floor_number`, `name`) VALUES
(1, 1, 'Ground Floor'),
(2, 2, 'First Floor'),
(3, 3, 'Second Floor'),
(4, 4, 'Third Floor'),
(5, 5, 'Fourth Floor');

INSERT IGNORE INTO `rooms` (`room_number`, `floor_id`, `room_type`, `status`) VALUES
('101', 1, 'Standard', 'available'),
('102', 1, 'Standard', 'available'),
('103', 1, 'Standard', 'available'),
('201', 2, 'Standard', 'available'),
('202', 2, 'Standard', 'available'),
('203', 2, 'Standard', 'available'),
('301', 3, 'Deluxe', 'available'),
('302', 3, 'Deluxe', 'available'),
('303', 3, 'Deluxe', 'available'),
('401', 4, 'Suite', 'available'),
('402', 4, 'Suite', 'available'),
('501', 5, 'Penthouse', 'available');

INSERT IGNORE INTO `suppliers` (`id`, `name`, `contact_person`, `email`, `phone`) VALUES
(1, 'ABC Supply Co.', 'John Smith', 'john@abcsupply.com', '+1-555-0101'),
(2, 'XYZ Distributors', 'Jane Doe', 'jane@xyzdist.com', '+1-555-0102'),
(3, 'Hotel Essentials Ltd.', 'Mike Johnson', 'mike@hotelessentials.com', '+1-555-0103'),
(4, 'Quality Products Inc.', 'Sarah Wilson', 'sarah@qualityproducts.com', '+1-555-0104'),
(5, 'Bulk Supplies Corp.', 'David Brown', 'david@bulksupplies.com', '+1-555-0105');

-- Insert sample inventory items (only if table is empty)
INSERT IGNORE INTO `inventory_items` (`id`, `item_name`, `sku`, `category_id`, `description`, `current_stock`, `minimum_stock`, `unit_price`, `unit`, `supplier`) VALUES
(1, 'Bath Towels', 'BT001', 1, 'White cotton bath towels', 50, 20, 15.00, 'pcs', 'ABC Supply Co.'),
(2, 'Hand Soap', 'HS001', 1, 'Liquid hand soap refill', 30, 10, 5.00, 'bottles', 'ABC Supply Co.'),
(3, 'Bed Sheets', 'BS001', 2, 'White cotton bed sheets', 25, 15, 25.00, 'sets', 'XYZ Distributors'),
(4, 'Pillows', 'P001', 2, 'Standard hotel pillows', 40, 20, 12.00, 'pcs', 'XYZ Distributors'),
(5, 'All-Purpose Cleaner', 'APC001', 3, 'Multi-surface cleaner', 20, 5, 8.00, 'bottles', 'Hotel Essentials Ltd.'),
(6, 'TV Remote', 'TVR001', 4, 'Universal TV remote control', 15, 5, 25.00, 'pcs', 'Quality Products Inc.'),
(7, 'Coffee Maker', 'CM001', 7, 'Single-serve coffee maker', 10, 3, 80.00, 'pcs', 'Bulk Supplies Corp.'),
(8, 'Desk Lamp', 'DL001', 6, 'LED desk lamp', 12, 5, 35.00, 'pcs', 'Quality Products Inc.'),
(9, 'Fire Extinguisher', 'FE001', 10, 'ABC fire extinguisher', 8, 2, 45.00, 'pcs', 'Safety Equipment Co.'),
(10, 'First Aid Kit', 'FAK001', 10, 'Complete first aid kit', 5, 2, 30.00, 'kits', 'Safety Equipment Co.');

-- Insert sample room inventory items
INSERT IGNORE INTO `room_inventory` (`room_id`, `item_id`, `quantity_allocated`, `quantity_current`, `par_level`) VALUES
(1, 1, 4, 4, 4), -- Room 101: 4 bath towels
(1, 2, 2, 2, 2), -- Room 101: 2 hand soap bottles
(1, 3, 2, 2, 2), -- Room 101: 2 bed sheet sets
(1, 4, 4, 4, 4), -- Room 101: 4 pillows
(1, 6, 1, 1, 1), -- Room 101: 1 TV remote
(1, 7, 1, 1, 1), -- Room 101: 1 coffee maker
(1, 8, 1, 1, 1), -- Room 101: 1 desk lamp
(2, 1, 4, 3, 4), -- Room 102: 4 bath towels (3 current)
(2, 2, 2, 1, 2), -- Room 102: 2 hand soap bottles (1 current)
(2, 3, 2, 2, 2), -- Room 102: 2 bed sheet sets
(2, 4, 4, 4, 4), -- Room 102: 4 pillows
(2, 6, 1, 1, 1), -- Room 102: 1 TV remote
(2, 7, 1, 1, 1), -- Room 102: 1 coffee maker
(2, 8, 1, 1, 1); -- Room 102: 1 desk lamp

-- Insert sample inventory transactions
INSERT IGNORE INTO `inventory_transactions` (`item_id`, `transaction_type`, `quantity`, `unit_price`, `total_value`, `reason`, `performed_by`, `created_at`) VALUES
(1, 'in', 50, 15.00, 750.00, 'Initial stock', 1, NOW()),
(2, 'in', 30, 5.00, 150.00, 'Initial stock', 1, NOW()),
(3, 'in', 25, 25.00, 625.00, 'Initial stock', 1, NOW()),
(4, 'in', 40, 12.00, 480.00, 'Initial stock', 1, NOW()),
(5, 'in', 20, 8.00, 160.00, 'Initial stock', 1, NOW()),
(1, 'out', 2, 15.00, 30.00, 'Used in room 102', 1, NOW()),
(2, 'out', 1, 5.00, 5.00, 'Used in room 102', 1, NOW());

-- Insert sample reorder rules
INSERT IGNORE INTO `reorder_rules` (`item_id`, `reorder_point`, `reorder_quantity`, `lead_time_days`, `supplier_id`, `auto_generate_po`) VALUES
(1, 20, 50, 7, 1, 1),
(2, 10, 30, 5, 1, 1),
(3, 15, 25, 7, 2, 1),
(4, 20, 40, 5, 2, 1),
(5, 5, 20, 3, 3, 0);

-- Insert sample purchase orders
INSERT IGNORE INTO `purchase_orders` (`po_number`, `supplier_id`, `status`, `total_amount`, `created_by`) VALUES
('PO-2024-001', 1, 'pending', 900.00, 1),
('PO-2024-002', 2, 'approved', 1200.00, 1);

-- Insert sample purchase order items
INSERT IGNORE INTO `purchase_order_items` (`po_id`, `item_id`, `quantity`, `unit_price`, `line_total`) VALUES
(1, 1, 50, 15.00, 750.00),
(1, 2, 30, 5.00, 150.00),
(2, 3, 25, 25.00, 625.00),
(2, 4, 40, 12.00, 480.00);

-- Insert sample inventory requests
INSERT IGNORE INTO `inventory_requests` (`item_name`, `quantity_requested`, `department`, `priority`, `status`, `requested_by`, `notes`) VALUES
('Bath Towels', 20, 'Housekeeping', 'High', 'Pending', 1, 'Need more towels for guest rooms'),
('Hand Soap', 50, 'Housekeeping', 'Medium', 'Approved', 1, 'Regular restock needed'),
('Bed Sheets', 15, 'Housekeeping', 'Medium', 'Pending', 1, 'Replacement for worn sheets'),
('Coffee Maker', 5, 'Maintenance', 'Low', 'Pending', 1, 'Backup coffee makers needed');

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS `idx_inventory_items_name` ON `inventory_items` (`item_name`);
CREATE INDEX IF NOT EXISTS `idx_inventory_items_sku` ON `inventory_items` (`sku`);
CREATE INDEX IF NOT EXISTS `idx_inventory_items_category` ON `inventory_items` (`category_id`);
CREATE INDEX IF NOT EXISTS `idx_inventory_items_stock` ON `inventory_items` (`current_stock`);
CREATE INDEX IF NOT EXISTS `idx_inventory_transactions_item` ON `inventory_transactions` (`item_id`);
CREATE INDEX IF NOT EXISTS `idx_inventory_transactions_type` ON `inventory_transactions` (`transaction_type`);
CREATE INDEX IF NOT EXISTS `idx_inventory_transactions_date` ON `inventory_transactions` (`created_at`);
CREATE INDEX IF NOT EXISTS `idx_room_inventory_room` ON `room_inventory` (`room_id`);
CREATE INDEX IF NOT EXISTS `idx_room_inventory_item` ON `room_inventory` (`item_id`);
CREATE INDEX IF NOT EXISTS `idx_room_inventory_stock` ON `room_inventory` (`quantity_current`);

-- Update statistics
ANALYZE TABLE `inventory_items`;
ANALYZE TABLE `inventory_transactions`;
ANALYZE TABLE `room_inventory`;
ANALYZE TABLE `inventory_requests`;
ANALYZE TABLE `purchase_orders`;
ANALYZE TABLE `reorder_rules`;

-- Final message
SELECT 'Ultra safe schema fix completed successfully!' as message;
