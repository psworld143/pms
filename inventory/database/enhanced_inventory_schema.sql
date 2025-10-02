-- Enhanced Inventory Management System Database Schema
-- Hotel PMS Training System - Inventory Module
-- Additional tables for full hotel operations support

-- Create hotel floors table
CREATE TABLE IF NOT EXISTS `hotel_floors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `floor_number` int(11) NOT NULL,
  `floor_name` varchar(50) DEFAULT NULL,
  `description` text,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `floor_number` (`floor_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create hotel rooms table
CREATE TABLE IF NOT EXISTS `hotel_rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_number` varchar(20) NOT NULL,
  `floor_id` int(11) NOT NULL,
  `room_type` varchar(50) DEFAULT 'standard',
  `status` enum('available','occupied','maintenance','out_of_order') DEFAULT 'available',
  `max_occupancy` int(11) DEFAULT 2,
  `description` text,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_number` (`room_number`),
  KEY `floor_id` (`floor_id`),
  KEY `status` (`status`),
  FOREIGN KEY (`floor_id`) REFERENCES `hotel_floors`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create room inventory items table
CREATE TABLE IF NOT EXISTS `room_inventory_items` (
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
  KEY `item_id` (`item_id`),
  FOREIGN KEY (`room_id`) REFERENCES `hotel_rooms`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`item_id`) REFERENCES `inventory_items`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create room inventory transactions table
CREATE TABLE IF NOT EXISTS `room_inventory_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `transaction_type` enum('restock','usage','audit','adjustment','transfer') NOT NULL,
  `quantity_change` int(11) NOT NULL,
  `quantity_before` int(11) NOT NULL,
  `quantity_after` int(11) NOT NULL,
  `reason` varchar(200) DEFAULT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `notes` text,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `room_id` (`room_id`),
  KEY `item_id` (`item_id`),
  KEY `user_id` (`user_id`),
  KEY `transaction_type` (`transaction_type`),
  KEY `created_at` (`created_at`),
  FOREIGN KEY (`room_id`) REFERENCES `hotel_rooms`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`item_id`) REFERENCES `inventory_items`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create housekeeping carts table
CREATE TABLE IF NOT EXISTS `housekeeping_carts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cart_number` varchar(20) NOT NULL,
  `floor_id` int(11) NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `status` enum('active','maintenance','retired') DEFAULT 'active',
  `last_updated` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cart_number` (`cart_number`),
  KEY `floor_id` (`floor_id`),
  KEY `assigned_to` (`assigned_to`),
  FOREIGN KEY (`floor_id`) REFERENCES `hotel_floors`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create cart inventory items table
CREATE TABLE IF NOT EXISTS `cart_inventory_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cart_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity_loaded` int(11) NOT NULL DEFAULT 0,
  `quantity_used` int(11) NOT NULL DEFAULT 0,
  `quantity_remaining` int(11) NOT NULL DEFAULT 0,
  `last_loaded` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cart_item` (`cart_id`, `item_id`),
  KEY `cart_id` (`cart_id`),
  KEY `item_id` (`item_id`),
  FOREIGN KEY (`cart_id`) REFERENCES `housekeeping_carts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`item_id`) REFERENCES `inventory_items`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create purchase orders table
CREATE TABLE IF NOT EXISTS `purchase_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `po_number` varchar(20) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `status` enum('draft','pending','approved','ordered','received','cancelled') DEFAULT 'draft',
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `requested_by` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `order_date` date DEFAULT NULL,
  `expected_delivery` date DEFAULT NULL,
  `received_date` date DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `po_number` (`po_number`),
  KEY `supplier_id` (`supplier_id`),
  KEY `requested_by` (`requested_by`),
  KEY `approved_by` (`approved_by`),
  KEY `status` (`status`),
  FOREIGN KEY (`supplier_id`) REFERENCES `inventory_suppliers`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`requested_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create purchase order items table
CREATE TABLE IF NOT EXISTS `purchase_order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `po_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity_ordered` int(11) NOT NULL,
  `quantity_received` int(11) DEFAULT 0,
  `unit_cost` decimal(10,2) NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `po_id` (`po_id`),
  KEY `item_id` (`item_id`),
  FOREIGN KEY (`po_id`) REFERENCES `purchase_orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`item_id`) REFERENCES `inventory_items`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create automated reorder rules table
CREATE TABLE IF NOT EXISTS `reorder_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `reorder_point` int(11) NOT NULL,
  `reorder_quantity` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `lead_time_days` int(11) DEFAULT 7,
  `auto_generate_po` tinyint(1) DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_id` (`item_id`),
  KEY `supplier_id` (`supplier_id`),
  FOREIGN KEY (`item_id`) REFERENCES `inventory_items`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`supplier_id`) REFERENCES `inventory_suppliers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create barcode tracking table
CREATE TABLE IF NOT EXISTS `barcode_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `barcode` varchar(100) NOT NULL,
  `item_id` int(11) NOT NULL,
  `batch_number` varchar(50) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `status` enum('active','used','expired','damaged') DEFAULT 'active',
  `scanned_by` int(11) DEFAULT NULL,
  `last_scanned` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `barcode` (`barcode`),
  KEY `item_id` (`item_id`),
  KEY `scanned_by` (`scanned_by`),
  FOREIGN KEY (`item_id`) REFERENCES `inventory_items`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`scanned_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create accounting integration table
CREATE TABLE IF NOT EXISTS `accounting_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` int(11) NOT NULL,
  `transaction_type` enum('inventory_transaction','purchase_order','room_transaction') NOT NULL,
  `account_code` varchar(20) NOT NULL,
  `debit_amount` decimal(10,2) DEFAULT 0.00,
  `credit_amount` decimal(10,2) DEFAULT 0.00,
  `description` text,
  `reference_number` varchar(50) DEFAULT NULL,
  `status` enum('pending','posted','reversed') DEFAULT 'pending',
  `posted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `transaction_id` (`transaction_id`),
  KEY `account_code` (`account_code`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create cost analysis reports table
CREATE TABLE IF NOT EXISTS `cost_analysis_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_date` date NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `total_quantity` int(11) NOT NULL,
  `average_cost` decimal(10,2) NOT NULL,
  `turnover_rate` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `report_date` (`report_date`),
  KEY `category_id` (`category_id`),
  FOREIGN KEY (`category_id`) REFERENCES `inventory_categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data for hotel floors
INSERT INTO `hotel_floors` (`floor_number`, `floor_name`, `description`) VALUES
(1, 'Ground Floor', 'Lobby, restaurant, and common areas'),
(2, 'First Floor', 'Standard rooms and suites'),
(3, 'Second Floor', 'Standard rooms and suites'),
(4, 'Third Floor', 'Premium rooms and suites'),
(5, 'Fourth Floor', 'Executive suites and penthouse');

-- Insert sample hotel rooms
INSERT INTO `hotel_rooms` (`room_number`, `floor_id`, `room_type`, `status`, `max_occupancy`) VALUES
('101', 2, 'standard', 'available', 2),
('102', 2, 'standard', 'available', 2),
('103', 2, 'standard', 'occupied', 2),
('201', 3, 'standard', 'available', 2),
('202', 3, 'standard', 'maintenance', 2),
('301', 4, 'premium', 'available', 4),
('302', 4, 'premium', 'occupied', 4),
('401', 5, 'executive', 'available', 6),
('501', 5, 'penthouse', 'available', 8);

-- Insert sample housekeeping carts
INSERT INTO `housekeeping_carts` (`cart_number`, `floor_id`, `status`) VALUES
('CART-001', 2, 'active'),
('CART-002', 3, 'active'),
('CART-003', 4, 'active'),
('CART-004', 5, 'active');

-- Note: Sample reorder rules should be inserted after inventory items exist
-- Use the following SQL after basic inventory items are populated:
-- INSERT INTO `reorder_rules` (`item_id`, `reorder_point`, `reorder_quantity`, `lead_time_days`, `auto_generate_po`) VALUES
-- (1, 20, 50, 3, 1),
-- (2, 10, 30, 5, 1),
-- (3, 5, 20, 7, 0),
-- (4, 30, 100, 2, 1),
-- (5, 50, 200, 1, 1);
