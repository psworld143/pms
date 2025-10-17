-- Simple Room Inventory Fix - No Foreign Key Constraints
-- This script works with your existing database structure

-- Step 1: Add missing columns to inventory_items table (only if they don't exist)
ALTER TABLE `inventory_items` 
ADD COLUMN IF NOT EXISTS `sku` varchar(50) DEFAULT NULL AFTER `item_name`;

ALTER TABLE `inventory_items` 
ADD COLUMN IF NOT EXISTS `unit` varchar(20) DEFAULT 'pcs' AFTER `unit_price`;

ALTER TABLE `inventory_items` 
ADD COLUMN IF NOT EXISTS `supplier` varchar(100) DEFAULT NULL AFTER `unit`;

ALTER TABLE `inventory_items` 
ADD COLUMN IF NOT EXISTS `location` varchar(100) DEFAULT NULL AFTER `supplier`;

ALTER TABLE `inventory_items` 
ADD COLUMN IF NOT EXISTS `barcode` varchar(100) DEFAULT NULL AFTER `location`;

ALTER TABLE `inventory_items` 
ADD COLUMN IF NOT EXISTS `image` varchar(255) DEFAULT NULL AFTER `barcode`;

ALTER TABLE `inventory_items` 
ADD COLUMN IF NOT EXISTS `is_pos_product` tinyint(1) NOT NULL DEFAULT 0 AFTER `image`;

ALTER TABLE `inventory_items` 
ADD COLUMN IF NOT EXISTS `status` enum('active','inactive','discontinued') DEFAULT 'active' AFTER `is_pos_product`;

ALTER TABLE `inventory_items` 
ADD COLUMN IF NOT EXISTS `cost_price` decimal(10,2) DEFAULT 0.00 AFTER `unit_price`;

ALTER TABLE `inventory_items` 
ADD COLUMN IF NOT EXISTS `maximum_stock` int(11) DEFAULT NULL AFTER `minimum_stock`;

-- Step 2: Create room_inventory table if it doesn't exist
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

-- Step 3: Create supply_requests table if it doesn't exist
CREATE TABLE IF NOT EXISTS `supply_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `quantity_requested` int(11) NOT NULL,
  `room_number` varchar(10) NOT NULL,
  `reason` enum('missing', 'damaged', 'low_stock', 'replacement') NOT NULL,
  `notes` text,
  `requested_by` int(11) NOT NULL,
  `status` enum('pending', 'approved', 'rejected', 'in_progress', 'completed') DEFAULT 'pending',
  `approved_by` int(11) NULL,
  `approved_at` datetime NULL,
  `completed_at` datetime NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_requested_by` (`requested_by`),
  KEY `idx_room_number` (`room_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 4: Create room_inventory_transactions table if it doesn't exist
CREATE TABLE IF NOT EXISTS `room_inventory_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity_change` int(11) NOT NULL,
  `quantity_before` int(11) NOT NULL,
  `quantity_after` int(11) NOT NULL,
  `transaction_type` enum('usage', 'restock', 'missing', 'damaged', 'removed', 'audit') NOT NULL,
  `reason` text,
  `notes` text,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_transaction_type` (`transaction_type`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_room_id` (`room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 5: Add missing columns to rooms table
ALTER TABLE `rooms` 
ADD COLUMN IF NOT EXISTS `assigned_housekeeping` int(11) NULL,
ADD COLUMN IF NOT EXISTS `last_housekeeping_check` datetime NULL,
ADD COLUMN IF NOT EXISTS `floor` int(11) DEFAULT 1;

-- Step 6: Insert sample inventory items (using only existing columns)
INSERT IGNORE INTO `inventory_items` (`item_name`, `description`, `current_stock`, `minimum_stock`, `unit_price`) VALUES
('Bath Towel', 'White cotton bath towel', 100, 20, 15.00),
('Hand Towel', 'White cotton hand towel', 100, 20, 8.00),
('Face Towel', 'White cotton face towel', 100, 20, 5.00),
('Bath Soap', 'Luxury hotel bath soap', 200, 50, 3.00),
('Shampoo', 'Hotel shampoo 50ml', 200, 50, 2.50),
('Conditioner', 'Hotel conditioner 50ml', 200, 50, 2.50),
('Hair Dryer', 'Professional hair dryer', 50, 10, 45.00),
('Iron', 'Hotel iron with board', 30, 5, 25.00),
('Coffee Maker', 'Single serve coffee maker', 25, 5, 80.00),
('Television Remote', 'TV remote control', 50, 10, 15.00);

-- Step 6.5: Update existing rooms to have proper capacity values (using booking system approach)
UPDATE `rooms` SET `capacity` = 2 WHERE `capacity` IS NULL OR `capacity` = 0;

-- Step 7: Insert sample room inventory data
INSERT IGNORE INTO `room_inventory` (`room_id`, `item_id`, `quantity_allocated`, `quantity_current`, `par_level`) VALUES
-- Room 201 (assuming room_id = 1)
(1, 1, 4, 4, 2), -- Bath Towel
(1, 2, 2, 2, 1), -- Hand Towel
(1, 3, 2, 2, 1), -- Face Towel
(1, 4, 2, 2, 1), -- Bath Soap
(1, 5, 2, 2, 1), -- Shampoo
(1, 6, 2, 2, 1), -- Conditioner
(1, 7, 1, 1, 1), -- Hair Dryer
(1, 8, 1, 1, 1), -- Iron
(1, 9, 1, 1, 1), -- Coffee Maker
(1, 10, 1, 1, 1), -- TV Remote

-- Room 202 (assuming room_id = 2)
(2, 1, 4, 3, 2), -- Bath Towel (missing 1)
(2, 2, 2, 2, 1), -- Hand Towel
(2, 3, 2, 1, 1), -- Face Towel (missing 1)
(2, 4, 2, 2, 1), -- Bath Soap
(2, 5, 2, 1, 1), -- Shampoo (low stock)
(2, 6, 2, 2, 1), -- Conditioner
(2, 7, 1, 0, 1), -- Hair Dryer (missing)
(2, 8, 1, 1, 1), -- Iron
(2, 9, 1, 1, 1), -- Coffee Maker
(2, 10, 1, 1, 1), -- TV Remote

-- Room 203 (assuming room_id = 3)
(3, 1, 4, 4, 2), -- Bath Towel
(3, 2, 2, 2, 1), -- Hand Towel
(3, 3, 2, 2, 1), -- Face Towel
(3, 4, 2, 2, 1), -- Bath Soap
(3, 5, 2, 2, 1), -- Shampoo
(3, 6, 2, 2, 1), -- Conditioner
(3, 7, 1, 1, 1), -- Hair Dryer
(3, 8, 1, 1, 1), -- Iron
(3, 9, 1, 1, 1), -- Coffee Maker
(3, 10, 1, 1, 1); -- TV Remote

-- Step 8: Insert sample supply requests
INSERT IGNORE INTO `supply_requests` (`item_id`, `quantity_requested`, `room_number`, `reason`, `notes`, `requested_by`, `status`) VALUES
(1, 1, '202', 'missing', 'Bath towel missing after guest checkout', 1, 'pending'),
(3, 1, '202', 'missing', 'Face towel missing after guest checkout', 1, 'pending'),
(7, 1, '202', 'missing', 'Hair dryer not found in room', 1, 'pending'),
(5, 1, '202', 'low_stock', 'Shampoo running low', 1, 'approved');

-- Step 9: Assign some rooms to housekeeping user (assuming user_id = 1)
UPDATE `rooms` SET `assigned_housekeeping` = 1 WHERE `room_number` IN ('201', '202', '203', '204', '205');

-- Step 10: Create sample room inventory transactions
INSERT IGNORE INTO `room_inventory_transactions` (`room_id`, `item_id`, `quantity_change`, `quantity_before`, `quantity_after`, `transaction_type`, `reason`, `user_id`) VALUES
(2, 1, -1, 4, 3, 'missing', 'Bath towel missing after guest checkout', 1),
(2, 3, -1, 2, 1, 'missing', 'Face towel missing after guest checkout', 1),
(2, 7, -1, 1, 0, 'missing', 'Hair dryer not found in room', 1),
(2, 5, -1, 2, 1, 'usage', 'Shampoo used by guest', 1);
