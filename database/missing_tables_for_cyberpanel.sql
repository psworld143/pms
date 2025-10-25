-- Missing Tables for CyberPanel Database
-- These tables exist in LOCALHOST but are missing in CYBERPANEL
-- Run this SQL script on your CyberPanel database to add the missing tables

-- --------------------------------------------------------
-- Table structure for table `check_in_records`
-- --------------------------------------------------------

CREATE TABLE `check_in_records` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `room_key_issued` enum('yes','no') NOT NULL DEFAULT 'yes',
  `welcome_amenities_provided` enum('yes','no') NOT NULL DEFAULT 'yes',
  `special_instructions` text DEFAULT NULL,
  `checked_in_by` int(11) NOT NULL,
  `checked_in_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `floors`
-- --------------------------------------------------------

CREATE TABLE `floors` (
  `id` int(11) NOT NULL,
  `floor_number` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `hotel_floors`
-- --------------------------------------------------------

CREATE TABLE `hotel_floors` (
  `id` int(11) NOT NULL,
  `floor_number` int(11) NOT NULL,
  `floor_name` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `hotel_rooms`
-- --------------------------------------------------------

CREATE TABLE `hotel_rooms` (
  `id` int(11) NOT NULL,
  `room_number` varchar(20) NOT NULL,
  `floor_id` int(11) NOT NULL,
  `room_type` varchar(50) DEFAULT 'standard',
  `status` enum('available','occupied','maintenance','out_of_order') DEFAULT 'available',
  `max_occupancy` int(11) DEFAULT 2,
  `description` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `quality_checks`
-- --------------------------------------------------------

CREATE TABLE `quality_checks` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `inspector_id` int(11) NOT NULL,
  `check_type` enum('routine','deep_clean','maintenance','guest_complaint') NOT NULL,
  `score` int(3) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `room_inventory`
-- --------------------------------------------------------

CREATE TABLE `room_inventory` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity_allocated` int(11) NOT NULL DEFAULT 0,
  `quantity_current` int(11) NOT NULL DEFAULT 0,
  `par_level` int(11) NOT NULL DEFAULT 1,
  `last_restocked` timestamp NULL DEFAULT NULL,
  `last_audited` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `service_requests`
-- --------------------------------------------------------

CREATE TABLE `service_requests` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `suppliers`
-- --------------------------------------------------------

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Add Primary Keys
-- --------------------------------------------------------

ALTER TABLE `check_in_records`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `floors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `floor_number` (`floor_number`);

ALTER TABLE `hotel_floors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `floor_number` (`floor_number`);

ALTER TABLE `hotel_rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_number` (`room_number`),
  ADD KEY `floor_id` (`floor_id`);

ALTER TABLE `quality_checks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `inspector_id` (`inspector_id`);

ALTER TABLE `room_inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_item` (`room_id`,`item_id`),
  ADD KEY `item_id` (`item_id`);

ALTER TABLE `service_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `guest_id` (`guest_id`),
  ADD KEY `requested_by` (`requested_by`),
  ADD KEY `assigned_to` (`assigned_to`);

ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

-- --------------------------------------------------------
-- Add Auto Increment
-- --------------------------------------------------------

ALTER TABLE `check_in_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `floors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `hotel_floors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `hotel_rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `quality_checks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `room_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `service_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
-- Add Foreign Key Constraints
-- --------------------------------------------------------

ALTER TABLE `check_in_records`
  ADD CONSTRAINT `check_in_records_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `check_in_records_ibfk_2` FOREIGN KEY (`checked_in_by`) REFERENCES `users` (`id`);

ALTER TABLE `hotel_rooms`
  ADD CONSTRAINT `hotel_rooms_ibfk_1` FOREIGN KEY (`floor_id`) REFERENCES `hotel_floors` (`id`) ON DELETE CASCADE;

ALTER TABLE `quality_checks`
  ADD CONSTRAINT `quality_checks_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quality_checks_ibfk_2` FOREIGN KEY (`inspector_id`) REFERENCES `users` (`id`);

ALTER TABLE `room_inventory`
  ADD CONSTRAINT `room_inventory_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `room_inventory_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE;

ALTER TABLE `service_requests`
  ADD CONSTRAINT `service_requests_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_requests_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_requests_ibfk_3` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_requests_ibfk_4` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `service_requests_ibfk_5` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`);

-- --------------------------------------------------------
-- Insert Sample Data
-- --------------------------------------------------------

-- Insert sample data for floors
INSERT INTO `floors` (`id`, `floor_number`, `name`, `description`, `active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Ground Floor', NULL, 1, NOW(), NOW()),
(2, 2, 'First Floor', NULL, 1, NOW(), NOW()),
(3, 3, 'Second Floor', NULL, 1, NOW(), NOW()),
(4, 4, 'Third Floor', NULL, 1, NOW(), NOW()),
(5, 5, 'Fourth Floor', NULL, 1, NOW(), NOW()),
(6, 6, 'Fifth Floor', NULL, 1, NOW(), NOW());

-- Insert sample data for hotel_floors
INSERT INTO `hotel_floors` (`id`, `floor_number`, `floor_name`, `description`, `active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Ground Floor', 'Lobby, restaurant, and common areas', 1, NOW(), NOW()),
(2, 2, 'First Floor', 'Standard rooms and suites', 1, NOW(), NOW()),
(3, 3, 'Second Floor', 'Standard rooms and suites', 1, NOW(), NOW()),
(4, 4, 'Third Floor', 'Premium rooms and suites', 1, NOW(), NOW()),
(5, 5, 'Fourth Floor', 'Executive suites and penthouse', 1, NOW(), NOW());

-- Insert sample data for hotel_rooms
INSERT INTO `hotel_rooms` (`id`, `room_number`, `floor_id`, `room_type`, `status`, `max_occupancy`, `description`, `active`, `created_at`, `updated_at`) VALUES
(1, '101', 2, 'standard', 'available', 2, NULL, 1, NOW(), NOW()),
(2, '102', 2, 'standard', 'available', 2, NULL, 1, NOW(), NOW()),
(3, '103', 2, 'standard', 'occupied', 2, NULL, 1, NOW(), NOW()),
(4, '201', 3, 'deluxe', 'available', 3, NULL, 1, NOW(), NOW()),
(5, '202', 3, 'deluxe', 'available', 3, NULL, 1, NOW(), NOW()),
(6, '301', 4, 'suite', 'available', 4, NULL, 1, NOW(), NOW()),
(7, '302', 4, 'suite', 'available', 4, NULL, 1, NOW(), NOW()),
(8, '401', 5, 'presidential', 'available', 6, NULL, 1, NOW(), NOW());

-- Insert sample data for suppliers
INSERT INTO `suppliers` (`id`, `name`, `contact_person`, `email`, `phone`, `address`, `active`, `created_at`, `updated_at`) VALUES
(1, 'ABC Supply Co.', 'John Smith', 'john@abcsupply.com', '+1-555-0101', NULL, 1, NOW(), NOW()),
(2, 'XYZ Distributors', 'Jane Doe', 'jane@xyzdist.com', '+1-555-0102', NULL, 1, NOW(), NOW()),
(3, 'Hotel Essentials Ltd.', 'Mike Johnson', 'mike@hotelessentials.com', '+1-555-0103', NULL, 1, NOW(), NOW()),
(4, 'Quality Products Inc.', 'Sarah Wilson', 'sarah@qualityproducts.com', '+1-555-0104', NULL, 1, NOW(), NOW()),
(5, 'Premium Services Corp.', 'David Brown', 'david@premiumservices.com', '+1-555-0105', NULL, 1, NOW(), NOW());

-- Insert sample data for room_inventory (if you have existing rooms and inventory items)
-- Note: Adjust the room_id and item_id values based on your existing data
INSERT INTO `room_inventory` (`id`, `room_id`, `item_id`, `quantity_allocated`, `quantity_current`, `par_level`, `last_restocked`, `last_audited`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 4, 4, 4, NULL, NULL, NULL, NOW(), NOW()),
(2, 1, 2, 2, 2, 2, NULL, NULL, NULL, NOW(), NOW()),
(3, 2, 1, 4, 4, 4, NULL, NULL, NULL, NOW(), NOW()),
(4, 2, 3, 1, 1, 1, NULL, NULL, NULL, NOW(), NOW());

-- Insert sample data for check_in_records (if you have existing reservations)
-- Note: Adjust the reservation_id and checked_in_by values based on your existing data
INSERT INTO `check_in_records` (`id`, `reservation_id`, `room_key_issued`, `welcome_amenities_provided`, `special_instructions`, `checked_in_by`, `checked_in_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'yes', 'yes', '', 1, NOW(), NOW(), NOW());

-- Insert sample data for service_requests (if you have existing reservations and guests)
-- Note: Adjust the IDs based on your existing data
INSERT INTO `service_requests` (`id`, `reservation_id`, `room_id`, `guest_id`, `service_type`, `title`, `description`, `priority`, `status`, `requested_by`, `assigned_to`, `requested_at`, `completed_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'room_service', 'Extra Towels Request', 'Guest requested extra towels for room', 'medium', 'pending', 1, NULL, NOW(), NULL, NOW(), NOW()),
(2, 1, 1, 1, 'housekeeping', 'Room Cleaning', 'Guest requested room cleaning service', 'high', 'in_progress', 1, 2, NOW(), NULL, NOW(), NOW());

-- Insert sample data for quality_checks (if you have existing rooms)
-- Note: Adjust the room_id and inspector_id values based on your existing data
INSERT INTO `quality_checks` (`id`, `room_id`, `inspector_id`, `check_type`, `score`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'routine', 95, 'Room in excellent condition', NOW(), NOW()),
(2, 2, 1, 'routine', 88, 'Minor cleaning needed', NOW(), NOW()),
(3, 3, 2, 'deep_clean', 92, 'Deep clean completed successfully', NOW(), NOW());

COMMIT;
