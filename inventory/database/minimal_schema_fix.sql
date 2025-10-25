-- Minimal Schema Fix for pms_pms_hotel Database
-- This script adds only the essential missing columns and tables

-- Add missing columns to inventory_items table
ALTER TABLE `inventory_items` 
ADD COLUMN IF NOT EXISTS `sku` VARCHAR(100) DEFAULT NULL AFTER `item_name`,
ADD COLUMN IF NOT EXISTS `unit` VARCHAR(50) DEFAULT 'pcs' AFTER `unit_price`,
ADD COLUMN IF NOT EXISTS `supplier` VARCHAR(255) DEFAULT NULL AFTER `unit`,
ADD COLUMN IF NOT EXISTS `is_pos_product` TINYINT(1) DEFAULT 0 AFTER `supplier`,
ADD COLUMN IF NOT EXISTS `status` ENUM('active', 'inactive', 'discontinued') DEFAULT 'active' AFTER `is_pos_product`;

-- Add missing columns to inventory_transactions table
ALTER TABLE `inventory_transactions` 
ADD COLUMN IF NOT EXISTS `unit_price` DECIMAL(10,2) DEFAULT 0.00 AFTER `quantity_change`,
ADD COLUMN IF NOT EXISTS `total_value` DECIMAL(10,2) DEFAULT 0.00 AFTER `unit_price`;

-- Create suppliers table if it doesn't exist
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create floors table if it doesn't exist
CREATE TABLE IF NOT EXISTS `floors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `floor_number` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `floor_number` (`floor_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create rooms table if it doesn't exist
CREATE TABLE IF NOT EXISTS `rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_number` varchar(20) NOT NULL,
  `floor_id` int(11) DEFAULT NULL,
  `room_type` varchar(100) DEFAULT NULL,
  `status` enum('available','occupied','maintenance','out_of_order') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_number` (`room_number`),
  KEY `floor_id` (`floor_id`),
  CONSTRAINT `rooms_floor_fk` FOREIGN KEY (`floor_id`) REFERENCES `floors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create room_inventory table if it doesn't exist
CREATE TABLE IF NOT EXISTS `room_inventory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `last_checked` timestamp NULL DEFAULT NULL,
  `status` enum('present','missing','damaged') DEFAULT 'present',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_item` (`room_id`,`item_id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `room_inventory_room_fk` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `room_inventory_item_fk` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create room_inventory_transactions table if it doesn't exist
CREATE TABLE IF NOT EXISTS `room_inventory_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity_change` int(11) NOT NULL,
  `transaction_type` enum('add','remove','check','damage','repair') NOT NULL,
  `reason` text DEFAULT NULL,
  `performed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `room_id` (`room_id`),
  KEY `item_id` (`item_id`),
  KEY `performed_by` (`performed_by`),
  CONSTRAINT `room_trans_room_fk` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `room_trans_item_fk` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `room_trans_user_fk` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data for floors and rooms
INSERT IGNORE INTO `floors` (`floor_number`, `name`, `description`) VALUES
(1, 'Ground Floor', 'Lobby and reception area'),
(2, 'First Floor', 'Standard rooms'),
(3, 'Second Floor', 'Deluxe rooms'),
(4, 'Third Floor', 'Suite rooms');

-- Insert sample rooms
INSERT IGNORE INTO `rooms` (`room_number`, `floor_id`, `room_type`) VALUES
('101', 1, 'Standard'),
('102', 1, 'Standard'),
('103', 1, 'Standard'),
('201', 2, 'Deluxe'),
('202', 2, 'Deluxe'),
('203', 2, 'Deluxe'),
('301', 3, 'Suite'),
('302', 3, 'Suite'),
('303', 3, 'Suite');

-- Insert sample suppliers
INSERT IGNORE INTO `suppliers` (`name`, `contact_person`, `email`, `phone`) VALUES
('ABC Supplies Inc.', 'John Smith', 'john@abcsupplies.com', '+1-555-0123'),
('XYZ Hospitality', 'Jane Doe', 'jane@xyz.com', '+1-555-0456'),
('Global Hotel Supplies', 'Mike Johnson', 'mike@global.com', '+1-555-0789');

-- Update inventory items with sample data
UPDATE `inventory_items` SET 
  `sku` = CONCAT('SKU-', LPAD(id, 4, '0')),
  `unit` = 'pcs',
  `supplier` = 'ABC Supplies Inc.',
  `is_pos_product` = 0,
  `status` = 'active'
WHERE `sku` IS NULL OR `sku` = '';

-- Update some items to be POS products
UPDATE `inventory_items` SET 
  `is_pos_product` = 1
WHERE `id` IN (1, 2, 3, 4, 5);

-- Update inventory transactions with unit prices
UPDATE `inventory_transactions` SET 
  `unit_price` = (
    SELECT `unit_price` 
    FROM `inventory_items` 
    WHERE `inventory_items`.`id` = `inventory_transactions`.`item_id`
  ),
  `total_value` = `quantity_change` * `unit_price`
WHERE `unit_price` = 0.00;

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS `idx_inventory_items_sku` ON `inventory_items` (`sku`);
CREATE INDEX IF NOT EXISTS `idx_inventory_items_status` ON `inventory_items` (`status`);
CREATE INDEX IF NOT EXISTS `idx_inventory_items_pos` ON `inventory_items` (`is_pos_product`);
CREATE INDEX IF NOT EXISTS `idx_inventory_transactions_value` ON `inventory_transactions` (`total_value`);

-- Add some sample room inventory
INSERT IGNORE INTO `room_inventory` (`room_id`, `item_id`, `quantity`) 
SELECT 
  r.id as room_id,
  i.id as item_id,
  FLOOR(RAND() * 5) + 1 as quantity
FROM `rooms` r
CROSS JOIN `inventory_items` i
WHERE i.id <= 10  -- Only add first 10 items to rooms
LIMIT 50;

-- Success message
SELECT 'Schema migration completed successfully!' as message;
