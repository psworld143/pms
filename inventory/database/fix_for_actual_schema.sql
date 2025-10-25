-- Fix for Actual PMS Database Schema
-- Hotel PMS Training System - Inventory Module
-- Based on the actual pms_pms_hotel.sql database structure

-- Step 1: Add missing columns to existing tables

-- Add missing columns to inventory_items table
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

-- Add missing columns to inventory_transactions table
ALTER TABLE `inventory_transactions` 
ADD COLUMN IF NOT EXISTS `unit_price` decimal(10,2) DEFAULT 0.00 AFTER `quantity`;

ALTER TABLE `inventory_transactions` 
ADD COLUMN IF NOT EXISTS `total_value` decimal(10,2) DEFAULT 0.00 AFTER `unit_price`;

-- Step 2: Create missing tables that don't exist

-- Create suppliers table (if it doesn't exist)
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

-- Create floors table (if it doesn't exist)
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

-- Create room_inventory table (if it doesn't exist)
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

-- Step 3: Update existing data to populate new columns

-- Extract SKU from description and populate sku column
UPDATE `inventory_items` 
SET `sku` = TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(`description`, 'SKU:', -1), '|', 1))
WHERE `sku` IS NULL 
AND `description` LIKE '%SKU:%';

-- Extract unit from description and populate unit column
UPDATE `inventory_items` 
SET `unit` = TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(`description`, 'Unit:', -1), '|', 1))
WHERE `unit` = 'pcs' 
AND `description` LIKE '%Unit:%';

-- Extract supplier from description and populate supplier column
UPDATE `inventory_items` 
SET `supplier` = TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(`description`, 'Supplier:', -1), '|', 1))
WHERE `supplier` IS NULL 
AND `description` LIKE '%Supplier:%';

-- Set cost_price to unit_price for existing items
UPDATE `inventory_items` 
SET `cost_price` = `unit_price` 
WHERE `cost_price` = 0.00;

-- Step 4: Insert sample data for missing tables

-- Insert sample suppliers
INSERT IGNORE INTO `suppliers` (`id`, `name`, `contact_person`, `email`, `phone`) VALUES
(1, 'Premium Coffee Supply Co.', 'John Smith', 'john@premiumcoffee.com', '+1-555-0101'),
(2, 'Tea Masters Inc.', 'Jane Doe', 'jane@teamasters.com', '+1-555-0102'),
(3, 'Pure Water Solutions', 'Mike Johnson', 'mike@purewater.com', '+1-555-0103'),
(4, 'Morning Delights Co.', 'Sarah Wilson', 'sarah@morningdelights.com', '+1-555-0104'),
(5, 'Dairy Fresh Farms', 'David Brown', 'david@dairyfresh.com', '+1-555-0105'),
(6, 'Luxury Linens Ltd.', 'Emma Davis', 'emma@luxurylinens.com', '+1-555-0106'),
(7, 'Spa Essentials Co.', 'Robert Miller', 'robert@spaessentials.com', '+1-555-0107'),
(8, 'CleanPro Solutions', 'Lisa Garcia', 'lisa@cleanpro.com', '+1-555-0108'),
(9, 'Office Depot Pro', 'Michael Rodriguez', 'michael@officedepot.com', '+1-555-0109'),
(10, 'Electrical Supply Co.', 'Jennifer Martinez', 'jennifer@electricalsupply.com', '+1-555-0110'),
(11, 'HVAC Solutions Inc.', 'Christopher Anderson', 'chris@hvac.com', '+1-555-0111'),
(12, 'Paint & Decor Co.', 'Amanda Taylor', 'amanda@paintdecor.com', '+1-555-0112'),
(13, 'Hardware Solutions', 'Daniel Thomas', 'daniel@hardware.com', '+1-555-0113');

-- Insert sample floors
INSERT IGNORE INTO `floors` (`id`, `floor_number`, `name`) VALUES
(1, 1, 'Ground Floor'),
(2, 2, 'First Floor'),
(3, 3, 'Second Floor'),
(4, 4, 'Third Floor'),
(5, 5, 'Fourth Floor'),
(6, 6, 'Fifth Floor');

-- Insert sample room inventory items
INSERT IGNORE INTO `room_inventory` (`room_id`, `item_id`, `quantity_allocated`, `quantity_current`, `par_level`) VALUES
-- Room 101 (assuming it exists)
(1, 6, 4, 4, 4), -- Bath Towels
(1, 7, 2, 2, 2), -- Hand Towels
(1, 8, 2, 2, 2), -- Bathrobes
(1, 9, 2, 2, 2), -- Shampoo
(1, 10, 2, 2, 2), -- Body Lotion
(1, 11, 4, 4, 4), -- Soap Bars
-- Room 102
(2, 6, 4, 3, 4), -- Bath Towels (3 current)
(2, 7, 2, 1, 2), -- Hand Towels (1 current)
(2, 8, 2, 2, 2), -- Bathrobes
(2, 9, 2, 1, 2), -- Shampoo (1 current)
(2, 10, 2, 2, 2), -- Body Lotion
(2, 11, 4, 3, 4), -- Soap Bars (3 current)
-- Room 201
(3, 6, 4, 4, 4), -- Bath Towels
(3, 7, 2, 2, 2), -- Hand Towels
(3, 8, 2, 2, 2), -- Bathrobes
(3, 9, 2, 2, 2), -- Shampoo
(3, 10, 2, 2, 2), -- Body Lotion
(3, 11, 4, 4, 4); -- Soap Bars

-- Step 5: Update inventory_transactions with unit_price and total_value
UPDATE `inventory_transactions` it
JOIN `inventory_items` ii ON it.item_id = ii.id
SET it.unit_price = ii.unit_price,
    it.total_value = it.quantity * ii.unit_price
WHERE it.unit_price = 0.00;

-- Step 6: Create indexes for better performance
CREATE INDEX IF NOT EXISTS `idx_inventory_items_sku` ON `inventory_items` (`sku`);
CREATE INDEX IF NOT EXISTS `idx_inventory_items_supplier` ON `inventory_items` (`supplier`);
CREATE INDEX IF NOT EXISTS `idx_inventory_items_status` ON `inventory_items` (`status`);
CREATE INDEX IF NOT EXISTS `idx_inventory_items_is_pos` ON `inventory_items` (`is_pos_product`);
CREATE INDEX IF NOT EXISTS `idx_inventory_transactions_unit_price` ON `inventory_transactions` (`unit_price`);
CREATE INDEX IF NOT EXISTS `idx_inventory_transactions_total_value` ON `inventory_transactions` (`total_value`);
CREATE INDEX IF NOT EXISTS `idx_room_inventory_room` ON `room_inventory` (`room_id`);
CREATE INDEX IF NOT EXISTS `idx_room_inventory_item` ON `room_inventory` (`item_id`);
CREATE INDEX IF NOT EXISTS `idx_room_inventory_stock` ON `room_inventory` (`quantity_current`);

-- Step 7: Update statistics
ANALYZE TABLE `inventory_items`;
ANALYZE TABLE `inventory_transactions`;
ANALYZE TABLE `room_inventory`;
ANALYZE TABLE `suppliers`;
ANALYZE TABLE `floors`;

-- Final message
SELECT 'Database schema fix completed successfully!' as message;
