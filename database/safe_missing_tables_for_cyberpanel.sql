-- Safe Missing Tables for CyberPanel Database
-- These tables exist in LOCALHOST but may be missing in CYBERPANEL
-- This script uses CREATE TABLE IF NOT EXISTS to avoid errors

-- --------------------------------------------------------
-- Table structure for table `check_in_records`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `check_in_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reservation_id` int(11) NOT NULL,
  `room_key_issued` enum('yes','no') NOT NULL DEFAULT 'yes',
  `welcome_amenities_provided` enum('yes','no') NOT NULL DEFAULT 'yes',
  `special_instructions` text DEFAULT NULL,
  `checked_in_by` int(11) NOT NULL,
  `checked_in_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `reservation_id` (`reservation_id`),
  KEY `checked_in_by` (`checked_in_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `floors`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `floors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `floor_number` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `floor_number` (`floor_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `hotel_floors` (if not exists)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `hotel_floors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `floor_number` int(11) NOT NULL,
  `floor_name` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `floor_number` (`floor_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `hotel_rooms`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `hotel_rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_number` varchar(20) NOT NULL,
  `floor_id` int(11) NOT NULL,
  `room_type` varchar(50) DEFAULT 'standard',
  `status` enum('available','occupied','maintenance','out_of_order') DEFAULT 'available',
  `max_occupancy` int(11) DEFAULT 2,
  `description` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_number` (`room_number`),
  KEY `floor_id` (`floor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `quality_checks`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `quality_checks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  `inspector_id` int(11) NOT NULL,
  `check_type` enum('routine','deep_clean','maintenance','guest_complaint') NOT NULL,
  `score` int(3) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `room_id` (`room_id`),
  KEY `inspector_id` (`inspector_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `room_inventory`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `room_inventory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity_allocated` int(11) NOT NULL DEFAULT 0,
  `quantity_current` int(11) NOT NULL DEFAULT 0,
  `par_level` int(11) NOT NULL DEFAULT 1,
  `last_restocked` timestamp NULL DEFAULT NULL,
  `last_audited` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_item` (`room_id`,`item_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `service_requests`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `service_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reservation_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `guest_id` int(11) NOT NULL,
  `service_type` enum('room_service','housekeeping','maintenance','concierge','laundry','minibar','other') NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `requested_by` int(11) NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `reservation_id` (`reservation_id`),
  KEY `room_id` (`room_id`),
  KEY `guest_id` (`guest_id`),
  KEY `requested_by` (`requested_by`),
  KEY `assigned_to` (`assigned_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `suppliers`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Add Foreign Key Constraints (only if they don't exist)
-- --------------------------------------------------------

-- Check and add foreign key for check_in_records
SET @constraint_exists = (
  SELECT COUNT(*) 
  FROM information_schema.KEY_COLUMN_USAGE 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'check_in_records' 
  AND CONSTRAINT_NAME = 'check_in_records_ibfk_1'
);

SET @sql = IF(@constraint_exists = 0, 
  'ALTER TABLE `check_in_records` ADD CONSTRAINT `check_in_records_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE',
  'SELECT "Foreign key check_in_records_ibfk_1 already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @constraint_exists = (
  SELECT COUNT(*) 
  FROM information_schema.KEY_COLUMN_USAGE 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'check_in_records' 
  AND CONSTRAINT_NAME = 'check_in_records_ibfk_2'
);

SET @sql = IF(@constraint_exists = 0, 
  'ALTER TABLE `check_in_records` ADD CONSTRAINT `check_in_records_ibfk_2` FOREIGN KEY (`checked_in_by`) REFERENCES `users` (`id`)',
  'SELECT "Foreign key check_in_records_ibfk_2 already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add foreign key for hotel_rooms
SET @constraint_exists = (
  SELECT COUNT(*) 
  FROM information_schema.KEY_COLUMN_USAGE 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'hotel_rooms' 
  AND CONSTRAINT_NAME = 'hotel_rooms_ibfk_1'
);

SET @sql = IF(@constraint_exists = 0, 
  'ALTER TABLE `hotel_rooms` ADD CONSTRAINT `hotel_rooms_ibfk_1` FOREIGN KEY (`floor_id`) REFERENCES `hotel_floors` (`id`) ON DELETE CASCADE',
  'SELECT "Foreign key hotel_rooms_ibfk_1 already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add foreign key for quality_checks
SET @constraint_exists = (
  SELECT COUNT(*) 
  FROM information_schema.KEY_COLUMN_USAGE 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'quality_checks' 
  AND CONSTRAINT_NAME = 'quality_checks_ibfk_1'
);

SET @sql = IF(@constraint_exists = 0, 
  'ALTER TABLE `quality_checks` ADD CONSTRAINT `quality_checks_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE',
  'SELECT "Foreign key quality_checks_ibfk_1 already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @constraint_exists = (
  SELECT COUNT(*) 
  FROM information_schema.KEY_COLUMN_USAGE 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'quality_checks' 
  AND CONSTRAINT_NAME = 'quality_checks_ibfk_2'
);

SET @sql = IF(@constraint_exists = 0, 
  'ALTER TABLE `quality_checks` ADD CONSTRAINT `quality_checks_ibfk_2` FOREIGN KEY (`inspector_id`) REFERENCES `users` (`id`)',
  'SELECT "Foreign key quality_checks_ibfk_2 already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add foreign key for room_inventory
SET @constraint_exists = (
  SELECT COUNT(*) 
  FROM information_schema.KEY_COLUMN_USAGE 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'room_inventory' 
  AND CONSTRAINT_NAME = 'room_inventory_ibfk_1'
);

SET @sql = IF(@constraint_exists = 0, 
  'ALTER TABLE `room_inventory` ADD CONSTRAINT `room_inventory_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE',
  'SELECT "Foreign key room_inventory_ibfk_1 already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @constraint_exists = (
  SELECT COUNT(*) 
  FROM information_schema.KEY_COLUMN_USAGE 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'room_inventory' 
  AND CONSTRAINT_NAME = 'room_inventory_ibfk_2'
);

SET @sql = IF(@constraint_exists = 0, 
  'ALTER TABLE `room_inventory` ADD CONSTRAINT `room_inventory_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE',
  'SELECT "Foreign key room_inventory_ibfk_2 already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add foreign key for service_requests
SET @constraint_exists = (
  SELECT COUNT(*) 
  FROM information_schema.KEY_COLUMN_USAGE 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'service_requests' 
  AND CONSTRAINT_NAME = 'service_requests_ibfk_1'
);

SET @sql = IF(@constraint_exists = 0, 
  'ALTER TABLE `service_requests` ADD CONSTRAINT `service_requests_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE',
  'SELECT "Foreign key service_requests_ibfk_1 already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @constraint_exists = (
  SELECT COUNT(*) 
  FROM information_schema.KEY_COLUMN_USAGE 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'service_requests' 
  AND CONSTRAINT_NAME = 'service_requests_ibfk_2'
);

SET @sql = IF(@constraint_exists = 0, 
  'ALTER TABLE `service_requests` ADD CONSTRAINT `service_requests_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE',
  'SELECT "Foreign key service_requests_ibfk_2 already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @constraint_exists = (
  SELECT COUNT(*) 
  FROM information_schema.KEY_COLUMN_USAGE 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'service_requests' 
  AND CONSTRAINT_NAME = 'service_requests_ibfk_3'
);

SET @sql = IF(@constraint_exists = 0, 
  'ALTER TABLE `service_requests` ADD CONSTRAINT `service_requests_ibfk_3` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`) ON DELETE CASCADE',
  'SELECT "Foreign key service_requests_ibfk_3 already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @constraint_exists = (
  SELECT COUNT(*) 
  FROM information_schema.KEY_COLUMN_USAGE 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'service_requests' 
  AND CONSTRAINT_NAME = 'service_requests_ibfk_4'
);

SET @sql = IF(@constraint_exists = 0, 
  'ALTER TABLE `service_requests` ADD CONSTRAINT `service_requests_ibfk_4` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`)',
  'SELECT "Foreign key service_requests_ibfk_4 already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @constraint_exists = (
  SELECT COUNT(*) 
  FROM information_schema.KEY_COLUMN_USAGE 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'service_requests' 
  AND CONSTRAINT_NAME = 'service_requests_ibfk_5'
);

SET @sql = IF(@constraint_exists = 0, 
  'ALTER TABLE `service_requests` ADD CONSTRAINT `service_requests_ibfk_5` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`)',
  'SELECT "Foreign key service_requests_ibfk_5 already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- --------------------------------------------------------
-- Insert Sample Data (only if tables are empty)
-- --------------------------------------------------------

-- Insert sample data for floors (only if empty)
INSERT IGNORE INTO `floors` (`id`, `floor_number`, `name`, `description`, `active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Ground Floor', NULL, 1, NOW(), NOW()),
(2, 2, 'First Floor', NULL, 1, NOW(), NOW()),
(3, 3, 'Second Floor', NULL, 1, NOW(), NOW()),
(4, 4, 'Third Floor', NULL, 1, NOW(), NOW()),
(5, 5, 'Fourth Floor', NULL, 1, NOW(), NOW()),
(6, 6, 'Fifth Floor', NULL, 1, NOW(), NOW());

-- Insert sample data for hotel_floors (only if empty)
INSERT IGNORE INTO `hotel_floors` (`id`, `floor_number`, `floor_name`, `description`, `active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Ground Floor', 'Lobby, restaurant, and common areas', 1, NOW(), NOW()),
(2, 2, 'First Floor', 'Standard rooms and suites', 1, NOW(), NOW()),
(3, 3, 'Second Floor', 'Standard rooms and suites', 1, NOW(), NOW()),
(4, 4, 'Third Floor', 'Premium rooms and suites', 1, NOW(), NOW()),
(5, 5, 'Fourth Floor', 'Executive suites and penthouse', 1, NOW(), NOW());

-- Insert sample data for hotel_rooms (only if empty)
INSERT IGNORE INTO `hotel_rooms` (`id`, `room_number`, `floor_id`, `room_type`, `status`, `max_occupancy`, `description`, `active`, `created_at`, `updated_at`) VALUES
(1, '101', 2, 'standard', 'available', 2, NULL, 1, NOW(), NOW()),
(2, '102', 2, 'standard', 'available', 2, NULL, 1, NOW(), NOW()),
(3, '103', 2, 'standard', 'occupied', 2, NULL, 1, NOW(), NOW()),
(4, '201', 3, 'deluxe', 'available', 3, NULL, 1, NOW(), NOW()),
(5, '202', 3, 'deluxe', 'available', 3, NULL, 1, NOW(), NOW()),
(6, '301', 4, 'suite', 'available', 4, NULL, 1, NOW(), NOW()),
(7, '302', 4, 'suite', 'available', 4, NULL, 1, NOW(), NOW()),
(8, '401', 5, 'presidential', 'available', 6, NULL, 1, NOW(), NOW());

-- Insert sample data for suppliers (only if empty)
INSERT IGNORE INTO `suppliers` (`id`, `name`, `contact_person`, `email`, `phone`, `address`, `active`, `created_at`, `updated_at`) VALUES
(1, 'ABC Supply Co.', 'John Smith', 'john@abcsupply.com', '+1-555-0101', NULL, 1, NOW(), NOW()),
(2, 'XYZ Distributors', 'Jane Doe', 'jane@xyzdist.com', '+1-555-0102', NULL, 1, NOW(), NOW()),
(3, 'Hotel Essentials Ltd.', 'Mike Johnson', 'mike@hotelessentials.com', '+1-555-0103', NULL, 1, NOW(), NOW()),
(4, 'Quality Products Inc.', 'Sarah Wilson', 'sarah@qualityproducts.com', '+1-555-0104', NULL, 1, NOW(), NOW()),
(5, 'Premium Services Corp.', 'David Brown', 'david@premiumservices.com', '+1-555-0105', NULL, 1, NOW(), NOW());

-- Insert sample data for room_inventory (only if empty and you have existing rooms/items)
-- Note: Adjust the room_id and item_id values based on your existing data
INSERT IGNORE INTO `room_inventory` (`id`, `room_id`, `item_id`, `quantity_allocated`, `quantity_current`, `par_level`, `last_restocked`, `last_audited`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 4, 4, 4, NULL, NULL, NULL, NOW(), NOW()),
(2, 1, 2, 2, 2, 2, NULL, NULL, NULL, NOW(), NOW()),
(3, 2, 1, 4, 4, 4, NULL, NULL, NULL, NOW(), NOW()),
(4, 2, 3, 1, 1, 1, NULL, NULL, NULL, NOW(), NOW());

-- Insert sample data for check_in_records (only if empty and you have existing reservations)
-- Note: Adjust the reservation_id and checked_in_by values based on your existing data
INSERT IGNORE INTO `check_in_records` (`id`, `reservation_id`, `room_key_issued`, `welcome_amenities_provided`, `special_instructions`, `checked_in_by`, `checked_in_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'yes', 'yes', '', 1, NOW(), NOW(), NOW());

-- Insert sample data for service_requests (only if empty and you have existing reservations/guests)
-- Note: Adjust the IDs based on your existing data
INSERT IGNORE INTO `service_requests` (`id`, `reservation_id`, `room_id`, `guest_id`, `service_type`, `title`, `description`, `priority`, `status`, `requested_by`, `assigned_to`, `requested_at`, `completed_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'room_service', 'Extra Towels Request', 'Guest requested extra towels for room', 'medium', 'pending', 1, NULL, NOW(), NULL, NOW(), NOW()),
(2, 1, 1, 1, 'housekeeping', 'Room Cleaning', 'Guest requested room cleaning service', 'high', 'in_progress', 1, 2, NOW(), NULL, NOW(), NOW());

-- Insert sample data for quality_checks (only if empty and you have existing rooms)
-- Note: Adjust the room_id and inspector_id values based on your existing data
INSERT IGNORE INTO `quality_checks` (`id`, `room_id`, `inspector_id`, `check_type`, `score`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'routine', 95, 'Room in excellent condition', NOW(), NOW()),
(2, 2, 1, 'routine', 88, 'Minor cleaning needed', NOW(), NOW()),
(3, 3, 2, 'deep_clean', 92, 'Deep clean completed successfully', NOW(), NOW());

COMMIT;
