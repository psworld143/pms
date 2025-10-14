-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 14, 2025 at 03:45 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pms_pms_hotel`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounting_transactions`
--

CREATE TABLE `accounting_transactions` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `transaction_type` enum('inventory_transaction','purchase_order','room_transaction') NOT NULL,
  `account_code` varchar(20) NOT NULL,
  `debit_amount` decimal(10,2) DEFAULT 0.00,
  `credit_amount` decimal(10,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `status` enum('pending','posted','reversed') DEFAULT 'pending',
  `posted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 01:00:28'),
(2, 1, 'logout', 'User logged out successfully', NULL, NULL, '2025-10-07 01:00:37'),
(3, 1, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 01:00:46'),
(4, 1, 'logout', 'User logged out successfully', NULL, NULL, '2025-10-07 01:00:50'),
(5, 1005, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 01:01:05'),
(6, 1005, 'logout', 'User logged out successfully', NULL, NULL, '2025-10-07 01:03:01'),
(7, 1007, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 01:03:12'),
(8, 1007, 'logout', 'User logged out successfully', NULL, NULL, '2025-10-07 01:03:49'),
(9, 1, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 01:10:16'),
(10, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 02:47:27'),
(11, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 03:09:06'),
(12, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 03:29:35'),
(13, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 03:34:36'),
(14, 1005, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 03:37:52'),
(15, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 03:41:25'),
(16, 1005, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 03:47:38'),
(17, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 03:48:35'),
(18, 1, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 03:49:15'),
(19, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 03:50:30'),
(20, 1007, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 03:50:43'),
(21, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 03:59:00'),
(22, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 04:00:45'),
(23, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 04:43:59'),
(24, 1005, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 04:44:54'),
(25, 1007, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 04:45:18'),
(26, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 04:46:32'),
(27, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 04:55:44'),
(28, 1005, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 04:56:08'),
(29, 1007, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 05:00:01'),
(30, 1005, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 05:04:55'),
(31, 1005, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 05:07:40'),
(32, 1005, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 05:08:55'),
(33, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 05:11:22'),
(34, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 05:11:27'),
(35, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 05:15:10'),
(36, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 05:15:26'),
(37, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 05:18:25'),
(38, 1005, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 05:19:22'),
(39, 1005, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 05:20:14'),
(40, 1005, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 05:20:27'),
(41, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 05:20:40'),
(42, 1007, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 05:20:55'),
(43, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 05:21:02'),
(44, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 05:22:07'),
(45, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 05:25:14'),
(46, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 05:44:55'),
(47, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 05:45:05'),
(48, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 05:45:13'),
(49, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 05:54:04'),
(50, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 05:54:18'),
(51, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 05:54:38'),
(52, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 05:57:40'),
(53, 1005, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 05:57:48'),
(54, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 06:07:14'),
(55, 1005, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 06:07:34'),
(56, 1005, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 06:07:50'),
(57, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 06:08:01'),
(58, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 06:20:24'),
(59, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 07:05:52'),
(60, 1003, 'payment_recorded', 'Recorded payment PAY-20251007-70176 for bill 3', NULL, NULL, '2025-10-07 07:06:23'),
(61, 1003, 'broadcast_sent', 'Target: front_desk', NULL, NULL, '2025-10-07 07:18:00'),
(62, 1005, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 07:18:14'),
(63, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 07:18:54'),
(64, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 07:28:49'),
(65, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 08:06:56'),
(66, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 08:11:58'),
(67, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 08:30:03'),
(68, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 08:34:57'),
(69, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 08:40:31'),
(70, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 08:44:03'),
(71, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 09:34:37'),
(72, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 09:46:40'),
(73, 1003, 'user_created', 'Created user: wewwew', NULL, NULL, '2025-10-07 09:47:52'),
(74, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-07 10:28:54'),
(75, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-08 00:44:24'),
(76, 1003, 'user_created', 'Created user: yah', NULL, NULL, '2025-10-08 00:45:22'),
(77, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-08 00:52:12'),
(78, 1003, 'room_created', 'Created room: 512', NULL, NULL, '2025-10-08 02:27:04'),
(79, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-08 02:27:49'),
(80, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-08 02:30:24'),
(81, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-08 02:43:02'),
(82, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-08 02:45:06'),
(83, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-08 02:51:33'),
(84, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-08 03:11:33'),
(85, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-08 03:45:20'),
(86, 1003, 'guest_created', 'guest_created: ge ge', NULL, NULL, '2025-10-08 03:46:23'),
(87, 1003, 'vip_status_granted', 'VIP status granted for guest ge ge', NULL, NULL, '2025-10-08 04:00:35'),
(88, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-08 05:49:50'),
(89, 1003, 'guest_created', 'guest_created: noen none', NULL, NULL, '2025-10-08 05:50:35'),
(90, 1003, 'vip_status_granted', 'VIP status granted for guest noen none', NULL, NULL, '2025-10-08 05:50:41'),
(91, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-08 08:11:00'),
(92, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-08 08:11:32'),
(93, 1003, 'guest_created', 'guest_created: asd asd', NULL, NULL, '2025-10-08 08:39:24'),
(94, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-08 08:47:41'),
(95, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-08 09:10:35'),
(96, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-08 10:27:49'),
(97, 1003, 'feedback_added', 'Added compliment for guest Alexander Thompson', NULL, NULL, '2025-10-08 10:38:02'),
(98, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-09 06:34:04'),
(99, 1003, 'housekeeping_updated', 'Updated room 1 housekeeping status to clean', NULL, NULL, '2025-10-09 06:34:21'),
(100, 1003, 'housekeeping_notes', 'Updated room 1 with notes: wew', NULL, NULL, '2025-10-09 06:34:21'),
(101, 1003, 'housekeeping_updated', 'Updated room 1 housekeeping status to dirty', NULL, NULL, '2025-10-09 06:34:28'),
(102, 1003, 'housekeeping_notes', 'Updated room 1 with notes: w', NULL, NULL, '2025-10-09 06:34:29'),
(103, 1003, 'maintenance_request', 'Created maintenance request 1 for room 1', NULL, NULL, '2025-10-09 06:38:44'),
(104, 1003, 'maintenance_request', 'Created maintenance request 2 for room 1', NULL, NULL, '2025-10-09 06:39:17'),
(105, 1003, 'housekeeping_updated', 'Updated room 1 housekeeping status to maintenance', NULL, NULL, '2025-10-09 06:41:09'),
(106, 1003, 'housekeeping_notes', 'Updated room 1 with notes: ui', NULL, NULL, '2025-10-09 06:41:09'),
(107, 1003, 'service_request_completed', 'Completed service request #2', NULL, NULL, '2025-10-09 08:47:43'),
(108, 1003, 'service_request_completed', 'Completed service request #1', NULL, NULL, '2025-10-09 08:47:46'),
(109, 1003, 'service_request_created', 'Created service request #3', NULL, NULL, '2025-10-09 08:54:36'),
(110, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-09 10:58:22'),
(111, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-10 00:59:04'),
(112, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-10 01:06:38'),
(113, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-10 01:19:53'),
(114, 1005, 'login', 'User logged in successfully', NULL, NULL, '2025-10-10 02:46:06'),
(115, 1005, 'vip_status_granted', 'VIP status granted for guest asd asd', NULL, NULL, '2025-10-10 02:46:33'),
(116, 1005, 'feedback_added', 'Added general for guest Alexander Thompson', NULL, NULL, '2025-10-10 02:47:09'),
(117, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-10 04:03:36'),
(118, 1007, 'login', 'User logged in successfully', NULL, NULL, '2025-10-10 04:13:27'),
(119, 1007, 'login', 'User logged in successfully', NULL, NULL, '2025-10-10 04:42:52'),
(120, 1007, 'login', 'User logged in successfully', NULL, NULL, '2025-10-11 00:34:26'),
(121, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-11 01:10:22'),
(122, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-11 03:25:23'),
(123, 1005, 'login', 'User logged in successfully', NULL, NULL, '2025-10-11 05:01:07'),
(124, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-13 00:39:56'),
(125, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-13 02:11:18'),
(126, 1003, 'login', 'User logged in successfully', NULL, NULL, '2025-10-13 02:25:51'),
(127, 1003, 'reservation_created', 'Created reservation RES202510134774', NULL, NULL, '2025-10-13 03:18:05'),
(128, 1003, 'logout', 'User logged out successfully', NULL, NULL, '2025-10-13 04:50:03'),
(129, 1005, 'login', 'User logged in successfully', NULL, NULL, '2025-10-13 04:50:12');

-- --------------------------------------------------------

--
-- Table structure for table `additional_services`
--

CREATE TABLE `additional_services` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` enum('food_beverage','laundry','spa','transportation','other') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `additional_services`
--

INSERT INTO `additional_services` (`id`, `name`, `description`, `price`, `category`, `is_active`, `created_at`) VALUES
(1, 'Room Service Breakfast', 'Continental breakfast delivered to room', 25.00, 'food_beverage', 1, '2025-08-26 16:09:34'),
(2, 'Laundry Service', 'Same day laundry service', 15.00, 'laundry', 1, '2025-08-26 16:09:34'),
(3, 'Spa Treatment', 'Relaxing massage therapy', 80.00, 'spa', 1, '2025-08-26 16:09:34'),
(4, 'Airport Transfer', 'Round trip airport transportation', 50.00, 'transportation', 1, '2025-08-26 16:09:34'),
(5, 'Mini Bar Refill', 'Refill of mini bar items', 30.00, 'food_beverage', 1, '2025-08-26 16:09:34'),
(6, 'Room Service Breakfast', 'Continental breakfast delivered to room', 25.00, 'food_beverage', 1, '2025-08-27 10:01:06'),
(7, 'Laundry Service', 'Same day laundry service', 15.00, 'laundry', 1, '2025-08-27 10:01:06'),
(8, 'Spa Treatment', 'Relaxing massage therapy', 80.00, 'spa', 1, '2025-08-27 10:01:06'),
(9, 'Airport Transfer', 'Round trip airport transportation', 50.00, 'transportation', 1, '2025-08-27 10:01:06'),
(10, 'Mini Bar Refill', 'Refill of mini bar items', 30.00, 'food_beverage', 1, '2025-08-27 10:01:06'),
(11, 'Champagne Service', 'Bottle of champagne with glasses', 45.00, 'food_beverage', 1, '2025-08-27 10:01:06'),
(12, 'Pet Sitting Service', 'In-room pet care service', 20.00, 'other', 1, '2025-08-27 10:01:06'),
(13, 'Concierge Service', 'Personal concierge assistance', 35.00, 'other', 1, '2025-08-27 10:01:06'),
(14, 'Room Service Breakfast', 'Continental breakfast delivered to room', 25.00, 'food_beverage', 1, '2025-08-27 10:01:50'),
(15, 'Laundry Service', 'Same day laundry service', 15.00, 'laundry', 1, '2025-08-27 10:01:50'),
(16, 'Spa Treatment', 'Relaxing massage therapy', 80.00, 'spa', 1, '2025-08-27 10:01:50'),
(17, 'Airport Transfer', 'Round trip airport transportation', 50.00, 'transportation', 1, '2025-08-27 10:01:50'),
(18, 'Mini Bar Refill', 'Refill of mini bar items', 30.00, 'food_beverage', 1, '2025-08-27 10:01:50'),
(19, 'Champagne Service', 'Bottle of champagne with glasses', 45.00, 'food_beverage', 1, '2025-08-27 10:01:50'),
(20, 'Pet Sitting Service', 'In-room pet care service', 20.00, 'other', 1, '2025-08-27 10:01:50'),
(21, 'Concierge Service', 'Personal concierge assistance', 35.00, 'other', 1, '2025-08-27 10:01:50'),
(22, 'Room Service Breakfast', 'Continental breakfast delivered to room', 25.00, 'food_beverage', 1, '2025-08-27 10:02:44'),
(23, 'Laundry Service', 'Same day laundry service', 15.00, 'laundry', 1, '2025-08-27 10:02:44'),
(24, 'Spa Treatment', 'Relaxing massage therapy', 80.00, 'spa', 1, '2025-08-27 10:02:44'),
(25, 'Airport Transfer', 'Round trip airport transportation', 50.00, 'transportation', 1, '2025-08-27 10:02:44'),
(26, 'Mini Bar Refill', 'Refill of mini bar items', 30.00, 'food_beverage', 1, '2025-08-27 10:02:44'),
(27, 'Champagne Service', 'Bottle of champagne with glasses', 45.00, 'food_beverage', 1, '2025-08-27 10:02:44'),
(28, 'Pet Sitting Service', 'In-room pet care service', 20.00, 'other', 1, '2025-08-27 10:02:44'),
(29, 'Concierge Service', 'Personal concierge assistance', 35.00, 'other', 1, '2025-08-27 10:02:44'),
(30, 'Room Service Breakfast', 'Continental breakfast delivered to room', 25.00, 'food_beverage', 1, '2025-08-27 11:09:55'),
(31, 'Room Service Lunch', 'Hot lunch menu delivered to room', 35.00, 'food_beverage', 1, '2025-08-27 11:09:55'),
(32, 'Room Service Dinner', 'Gourmet dinner menu delivered to room', 45.00, 'food_beverage', 1, '2025-08-27 11:09:55'),
(33, 'Late Night Snacks', 'Assorted snacks and beverages', 20.00, 'food_beverage', 1, '2025-08-27 11:09:55'),
(34, 'Champagne Service', 'Bottle of champagne with glasses', 75.00, 'food_beverage', 1, '2025-08-27 11:09:55'),
(35, 'Coffee & Tea Service', 'Premium coffee and tea selection', 15.00, 'food_beverage', 1, '2025-08-27 11:09:55'),
(36, 'Wine Service', 'Bottle of house wine with glasses', 45.00, 'food_beverage', 1, '2025-08-27 11:09:55'),
(37, 'Birthday Cake', 'Custom birthday cake with candles', 30.00, 'food_beverage', 1, '2025-08-27 11:09:55'),
(38, 'Anniversary Package', 'Chocolate covered strawberries and champagne', 60.00, 'food_beverage', 1, '2025-08-27 11:09:55'),
(39, 'Same Day Laundry', 'Express laundry service (same day)', 25.00, 'laundry', 1, '2025-08-27 11:09:55'),
(40, 'Standard Laundry', 'Regular laundry service (next day)', 15.00, 'laundry', 1, '2025-08-27 11:09:55'),
(41, 'Dry Cleaning', 'Professional dry cleaning service', 20.00, 'laundry', 1, '2025-08-27 11:09:55'),
(42, 'Pressing Service', 'Quick pressing of garments', 12.00, 'laundry', 1, '2025-08-27 11:09:55'),
(43, 'Shoe Shine', 'Professional shoe shining service', 8.00, 'laundry', 1, '2025-08-27 11:09:55'),
(44, 'Ironing Service', 'Complete ironing service', 10.00, 'laundry', 1, '2025-08-27 11:09:55'),
(45, 'Swedish Massage', 'Relaxing Swedish massage (60 min)', 80.00, 'spa', 1, '2025-08-27 11:09:55'),
(46, 'Deep Tissue Massage', 'Therapeutic deep tissue massage (60 min)', 90.00, 'spa', 1, '2025-08-27 11:09:55'),
(47, 'Hot Stone Massage', 'Luxurious hot stone massage (75 min)', 100.00, 'spa', 1, '2025-08-27 11:09:55'),
(48, 'Facial Treatment', 'Rejuvenating facial treatment (45 min)', 70.00, 'spa', 1, '2025-08-27 11:09:55'),
(49, 'Manicure & Pedicure', 'Complete nail care service', 50.00, 'spa', 1, '2025-08-27 11:09:55'),
(50, 'Couples Massage', 'Romantic couples massage (90 min)', 150.00, 'spa', 1, '2025-08-27 11:09:55'),
(51, 'Aromatherapy Session', 'Relaxing aromatherapy treatment (30 min)', 45.00, 'spa', 1, '2025-08-27 11:09:55'),
(52, 'Airport Transfer', 'Round trip airport transportation', 50.00, 'transportation', 1, '2025-08-27 11:09:55'),
(53, 'City Tour', 'Guided city tour with professional guide', 75.00, 'transportation', 1, '2025-08-27 11:09:55'),
(54, 'Luxury Car Rental', 'Premium car rental service', 120.00, 'transportation', 1, '2025-08-27 11:09:55'),
(55, 'Shuttle Service', 'Hotel shuttle to local attractions', 25.00, 'transportation', 1, '2025-08-27 11:09:55'),
(56, 'Limousine Service', 'Luxury limousine transportation', 200.00, 'transportation', 1, '2025-08-27 11:09:55'),
(57, 'Valet Parking', 'Professional valet parking service', 15.00, 'transportation', 1, '2025-08-27 11:09:55'),
(58, 'Concierge Service', 'Personal concierge assistance', 30.00, 'other', 1, '2025-08-27 11:09:55'),
(59, 'Business Center Access', 'Access to business center facilities', 20.00, 'other', 1, '2025-08-27 11:09:55'),
(60, 'Gym Access', 'Access to hotel fitness center', 15.00, 'other', 1, '2025-08-27 11:09:55'),
(61, 'Pool Towel Service', 'Fresh pool towels and service', 5.00, 'other', 1, '2025-08-27 11:09:55'),
(62, 'Pet Sitting', 'Professional pet sitting service', 40.00, 'other', 1, '2025-08-27 11:09:55'),
(63, 'Photography Service', 'Professional photography service', 100.00, 'other', 1, '2025-08-27 11:09:55'),
(64, 'Translation Service', 'Professional translation assistance', 35.00, 'other', 1, '2025-08-27 11:09:55'),
(65, 'Medical Assistance', 'On-call medical assistance', 50.00, 'other', 1, '2025-08-27 11:09:55');

-- --------------------------------------------------------

--
-- Table structure for table `barcode_tracking`
--

CREATE TABLE `barcode_tracking` (
  `id` int(11) NOT NULL,
  `barcode` varchar(100) NOT NULL,
  `item_id` int(11) NOT NULL,
  `batch_number` varchar(50) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `status` enum('active','used','expired','damaged') DEFAULT 'active',
  `scanned_by` int(11) DEFAULT NULL,
  `last_scanned` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `billing`
--

CREATE TABLE `billing` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `guest_id` int(11) NOT NULL,
  `room_charges` decimal(10,2) DEFAULT 0.00,
  `additional_charges` decimal(10,2) DEFAULT 0.00,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','partial','paid','refunded') DEFAULT 'pending',
  `payment_method` enum('cash','credit_card','debit_card','bank_transfer','voucher') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `billing`
--

INSERT INTO `billing` (`id`, `reservation_id`, `guest_id`, `room_charges`, `additional_charges`, `tax_amount`, `discount_amount`, `total_amount`, `payment_status`, `payment_method`, `created_at`, `updated_at`) VALUES
(1, 22, 14, 250.00, 0.00, 25.00, 0.00, 275.00, 'pending', NULL, '2025-10-13 03:18:05', '2025-10-13 03:18:05');

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--

CREATE TABLE `bills` (
  `id` int(11) NOT NULL,
  `bill_number` varchar(20) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `bill_date` date NOT NULL,
  `due_date` date NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','overdue','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bills`
--

INSERT INTO `bills` (`id`, `bill_number`, `reservation_id`, `bill_date`, `due_date`, `subtotal`, `tax_amount`, `discount_amount`, `total_amount`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'BILL20241201001', 1, '2024-12-01', '2024-12-01', 325.00, 32.50, 0.00, 357.50, 'paid', 'Room charges and room service', 1, '2025-08-27 10:02:44', '2025-08-27 10:02:44'),
(2, 'BILL20241201002', 2, '2024-12-01', '2024-12-01', 1080.00, 108.00, 50.00, 1138.00, 'paid', 'Anniversary package with discount', 1, '2025-08-27 10:02:44', '2025-08-27 10:02:44'),
(3, 'BILL20241202001', 3, '2024-12-02', '2024-12-02', 800.00, 86.40, 0.00, 886.40, 'overdue', 'Business traveler', 4, '2025-08-27 10:02:44', '2025-10-07 07:06:23'),
(4, 'BILL0001', 1, '2025-10-13', '2025-10-20', 3150.00, 0.00, 0.00, 3150.00, 'paid', NULL, 1, '2025-10-13 01:28:10', '2025-10-13 01:28:10'),
(5, 'BILL0002', 2, '2025-10-13', '2025-10-20', 4577.00, 0.00, 0.00, 4577.00, 'paid', NULL, 1, '2025-10-13 01:28:10', '2025-10-13 01:28:10'),
(6, 'BILL0003', 3, '2025-10-13', '2025-10-20', 7447.00, 0.00, 0.00, 7447.00, 'paid', NULL, 1, '2025-10-13 01:28:10', '2025-10-13 01:28:10'),
(7, 'BILL0004', 4, '2025-10-13', '2025-10-20', 5540.00, 0.00, 0.00, 5540.00, 'paid', NULL, 1, '2025-10-13 01:28:10', '2025-10-13 01:28:10'),
(8, 'BILL0005', 5, '2025-10-13', '2025-10-20', 2916.00, 0.00, 0.00, 2916.00, 'paid', NULL, 1, '2025-10-13 01:28:10', '2025-10-13 01:28:10'),
(9, 'BILL0006', 6, '2025-10-13', '2025-10-20', 2514.00, 0.00, 0.00, 2514.00, 'paid', NULL, 1, '2025-10-13 01:28:10', '2025-10-13 01:28:10'),
(10, 'BILL0007', 7, '2025-10-13', '2025-10-20', 5091.00, 0.00, 0.00, 5091.00, 'paid', NULL, 1, '2025-10-13 01:28:10', '2025-10-13 01:28:10'),
(11, 'BILL0008', 8, '2025-10-13', '2025-10-20', 6851.00, 0.00, 0.00, 6851.00, 'paid', NULL, 1, '2025-10-13 01:28:10', '2025-10-13 01:28:10'),
(12, 'BILL0009', 9, '2025-10-13', '2025-10-20', 7833.00, 0.00, 0.00, 7833.00, 'paid', NULL, 1, '2025-10-13 01:28:10', '2025-10-13 01:28:10'),
(13, 'BILL0010', 10, '2025-10-13', '2025-10-20', 7011.00, 0.00, 0.00, 7011.00, 'paid', NULL, 1, '2025-10-13 01:28:10', '2025-10-13 01:28:10'),
(45, 'BILL0101', 1, '2025-09-13', '2025-09-20', 6343.00, 0.00, 0.00, 6343.00, 'paid', NULL, 1, '2025-09-13 01:56:51', '2025-10-13 01:56:51'),
(46, 'BILL0102', 2, '2025-09-24', '2025-10-01', 4569.00, 0.00, 0.00, 4569.00, 'paid', NULL, 1, '2025-09-24 01:56:51', '2025-10-13 01:56:51'),
(47, 'BILL0103', 3, '2025-09-25', '2025-10-02', 7060.00, 0.00, 0.00, 7060.00, 'paid', NULL, 1, '2025-09-25 01:56:51', '2025-10-13 01:56:51'),
(48, 'BILL0104', 4, '2025-10-03', '2025-10-10', 6132.00, 0.00, 0.00, 6132.00, 'paid', NULL, 1, '2025-10-03 01:56:51', '2025-10-13 01:56:51'),
(49, 'BILL0105', 5, '2025-09-29', '2025-10-06', 5304.00, 0.00, 0.00, 5304.00, 'paid', NULL, 1, '2025-09-29 01:56:51', '2025-10-13 01:56:51'),
(50, 'BILL0106', 6, '2025-09-14', '2025-09-21', 2844.00, 0.00, 0.00, 2844.00, 'paid', NULL, 1, '2025-09-14 01:56:51', '2025-10-13 01:56:51'),
(51, 'BILL0107', 7, '2025-09-14', '2025-09-21', 4426.00, 0.00, 0.00, 4426.00, 'paid', NULL, 1, '2025-09-14 01:56:51', '2025-10-13 01:56:51'),
(52, 'BILL0108', 8, '2025-09-21', '2025-09-28', 7536.00, 0.00, 0.00, 7536.00, 'paid', NULL, 1, '2025-09-21 01:56:51', '2025-10-13 01:56:51'),
(53, 'BILL0109', 9, '2025-09-30', '2025-10-07', 4853.00, 0.00, 0.00, 4853.00, 'paid', NULL, 1, '2025-09-30 01:56:51', '2025-10-13 01:56:51'),
(54, 'BILL0110', 10, '2025-09-23', '2025-09-30', 2850.00, 0.00, 0.00, 2850.00, 'paid', NULL, 1, '2025-09-23 01:56:51', '2025-10-13 01:56:51'),
(56, 'BILL0112', 12, '2025-10-04', '2025-10-11', 5479.00, 0.00, 0.00, 5479.00, 'paid', NULL, 1, '2025-10-04 01:56:51', '2025-10-13 01:56:51');

-- --------------------------------------------------------

--
-- Table structure for table `bill_items`
--

CREATE TABLE `bill_items` (
  `id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `description` varchar(200) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_inventory_items`
--

CREATE TABLE `cart_inventory_items` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity_loaded` int(11) NOT NULL DEFAULT 0,
  `quantity_used` int(11) NOT NULL DEFAULT 0,
  `quantity_remaining` int(11) NOT NULL DEFAULT 0,
  `last_loaded` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `check_ins`
--

CREATE TABLE `check_ins` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `room_key_issued` tinyint(1) DEFAULT 0,
  `welcome_amenities` tinyint(1) DEFAULT 0,
  `checked_in_by` int(11) NOT NULL,
  `checked_in_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `check_in_records`
--

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

--
-- Dumping data for table `check_in_records`
--

INSERT INTO `check_in_records` (`id`, `reservation_id`, `room_key_issued`, `welcome_amenities_provided`, `special_instructions`, `checked_in_by`, `checked_in_at`, `created_at`, `updated_at`) VALUES
(1, 22, 'yes', 'yes', '', 1003, '2025-10-13 03:43:11', '2025-10-13 03:43:11', '2025-10-13 03:43:11');

-- --------------------------------------------------------

--
-- Table structure for table `cost_analysis_reports`
--

CREATE TABLE `cost_analysis_reports` (
  `id` int(11) NOT NULL,
  `report_date` date NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `total_quantity` int(11) NOT NULL,
  `average_cost` decimal(10,2) NOT NULL,
  `turnover_rate` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_service_scenarios`
--

CREATE TABLE `customer_service_scenarios` (
  `id` int(11) NOT NULL,
  `scenario_id` varchar(50) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `situation` text NOT NULL,
  `guest_request` text NOT NULL,
  `type` enum('complaints','requests','emergencies') NOT NULL,
  `difficulty` enum('beginner','intermediate','advanced') NOT NULL,
  `estimated_time` int(11) NOT NULL DEFAULT 5,
  `points` int(11) NOT NULL DEFAULT 15,
  `status` enum('active','inactive','draft') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer_service_scenarios`
--

INSERT INTO `customer_service_scenarios` (`id`, `scenario_id`, `title`, `description`, `situation`, `guest_request`, `type`, `difficulty`, `estimated_time`, `points`, `status`, `created_at`, `updated_at`) VALUES
(1, 'CS001', 'Angry Guest Complaint', 'Handle an angry guest complaint about room cleanliness', 'A guest approaches the front desk visibly upset about their room condition. They claim the room was not properly cleaned and demand immediate action.', 'The guest wants a room change, compensation, and an apology from management.', 'complaints', 'beginner', 15, 100, 'active', '2025-10-10 01:34:18', '2025-10-10 01:34:18'),
(2, 'CS002', 'Special Dietary Request', 'Accommodate a guest with special dietary requirements', 'A guest with severe food allergies is checking in and needs special meal arrangements for their stay.', 'The guest needs gluten-free, dairy-free meals and wants to ensure the kitchen understands their restrictions.', 'requests', 'intermediate', 20, 150, 'active', '2025-10-10 01:34:18', '2025-10-10 01:34:18'),
(3, 'CS003', 'Medical Emergency', 'Respond to a medical emergency in the hotel', 'A guest collapses in the lobby and appears to be having a medical emergency. Other guests are panicking.', 'Immediate medical assistance and professional handling of the situation.', 'emergencies', 'advanced', 25, 200, 'active', '2025-10-10 01:34:18', '2025-10-10 01:34:18'),
(4, 'CS004', 'Noise Complaint', 'Resolve a noise complaint between guests', 'A guest complains about loud music from the room next door at 11 PM. The other guest refuses to turn down the music.', 'The complaining guest wants the noise stopped immediately and may want to change rooms.', 'complaints', 'intermediate', 18, 120, 'active', '2025-10-10 01:34:18', '2025-10-10 01:34:18'),
(5, 'CS005', 'Lost Luggage', 'Help a guest with lost luggage from their flight', 'A guest arrives at the hotel but their luggage was lost by the airline. They have no clothes for their business meeting tomorrow.', 'The guest needs immediate assistance with clothing and toiletries, and help contacting the airline.', 'requests', 'beginner', 12, 80, 'active', '2025-10-10 01:34:18', '2025-10-10 01:34:18');

-- --------------------------------------------------------

--
-- Table structure for table `discounts`
--

CREATE TABLE `discounts` (
  `id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `discount_type` enum('percentage','fixed','loyalty','promotional') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `reason` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `applied_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dynamic_module_tags`
--

CREATE TABLE `dynamic_module_tags` (
  `tutorial_module_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dynamic_training_categories`
--

CREATE TABLE `dynamic_training_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'fas fa-folder',
  `color` varchar(20) DEFAULT 'gray',
  `parent_id` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dynamic_training_categories`
--

INSERT INTO `dynamic_training_categories` (`id`, `name`, `description`, `icon`, `color`, `parent_id`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 'Point of Sale', 'POS system training modules', 'fas fa-cash-register', 'green', NULL, 0, 1, '2025-10-02 02:53:24'),
(2, 'Inventory Management', 'Inventory and stock management training', 'fas fa-boxes', 'blue', NULL, 0, 1, '2025-10-02 02:53:24'),
(3, 'Booking System', 'Reservation and booking management', 'fas fa-calendar-check', 'purple', NULL, 0, 1, '2025-10-02 02:53:24'),
(4, 'General Training', 'General hotel operations training', 'fas fa-graduation-cap', 'gray', NULL, 0, 1, '2025-10-02 02:53:24');

-- --------------------------------------------------------

--
-- Table structure for table `dynamic_training_content`
--

CREATE TABLE `dynamic_training_content` (
  `id` int(11) NOT NULL,
  `tutorial_module_id` int(11) NOT NULL,
  `step_number` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `step_type` enum('introduction','learning','practical','quiz','simulation','assessment','summary') DEFAULT 'learning',
  `duration_minutes` int(11) DEFAULT 5,
  `learning_objectives` text DEFAULT NULL,
  `interactive_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`interactive_data`)),
  `prerequisites` text DEFAULT NULL,
  `is_required` tinyint(1) DEFAULT 1,
  `order_index` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dynamic_training_content`
--

INSERT INTO `dynamic_training_content` (`id`, `tutorial_module_id`, `step_number`, `title`, `content`, `step_type`, `duration_minutes`, `learning_objectives`, `interactive_data`, `prerequisites`, `is_required`, `order_index`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Welcome to POS Training', 'Welcome to the Point of Sale (POS) System training! In this comprehensive module, you will learn the fundamentals of processing orders, handling payments, and managing transactions in a hotel environment. This training is designed to give you hands-on experience with real-world scenarios.', 'introduction', 3, '[\"Understand the POS interface\", \"Learn basic navigation\", \"Familiarize with system layout\"]', '{\"type\": \"welcome\", \"features\": [\"Interactive interface\", \"Real-time feedback\", \"Progress tracking\"]}', NULL, 1, 1, 1, '2025-10-02 02:53:24', '2025-10-02 02:53:24'),
(2, 1, 2, 'Understanding the POS Interface', 'The POS interface is designed for efficiency and ease of use. Key components include the menu display, order summary, payment processing, and customer information sections. Each area has specific functions that work together to create a seamless transaction experience.', 'learning', 4, '[\"Identify main interface components\", \"Understand menu layout\", \"Navigate between sections\"]', '{\"type\": \"interface_tour\", \"components\": [{\"name\": \"Menu Display\", \"description\": \"Shows available items with prices\"}, {\"name\": \"Order Summary\", \"description\": \"Displays current order items and total\"}]}', NULL, 1, 2, 1, '2025-10-02 02:53:24', '2025-10-02 02:53:24'),
(3, 1, 3, 'Creating a New Order', 'Starting a new order is the first step in the sales process. You will learn how to initiate orders, select items, and manage the order flow efficiently. This includes handling different customer types and special requests.', 'practical', 5, '[\"Start new orders\", \"Add items to orders\", \"Modify order contents\"]', '{\"type\": \"order_simulation\", \"steps\": [\"Click New Order\", \"Select customer type\", \"Add items\", \"Review order\"]}', NULL, 1, 3, 1, '2025-10-02 02:53:24', '2025-10-02 02:53:24'),
(4, 1, 4, 'Adding Items to Order', 'Learn how to efficiently add items to orders, apply modifications, and handle special requests from customers. This includes understanding menu categories, item variations, and pricing structures.', 'practical', 6, '[\"Add menu items\", \"Apply modifications\", \"Handle special requests\"]', '{\"type\": \"item_selection\", \"categories\": [\"Food\", \"Beverages\", \"Desserts\"]}', NULL, 1, 4, 1, '2025-10-02 02:53:24', '2025-10-02 02:53:24'),
(5, 1, 5, 'Processing Payments', 'Payment processing is crucial for completing transactions. Learn about different payment methods, handling cash, processing card payments, and applying discounts or promotions.', 'practical', 7, '[\"Process cash payments\", \"Handle card transactions\", \"Apply discounts and promotions\"]', '{\"type\": \"payment_simulation\", \"methods\": [\"Cash\", \"Credit Card\", \"Debit Card\", \"Mobile Payment\"]}', NULL, 1, 5, 1, '2025-10-02 02:53:24', '2025-10-02 02:53:24'),
(6, 1, 6, 'Generating Receipts', 'Receipts provide customers with transaction records and help with accounting. Learn about different receipt types, when to use them, and how to handle receipt reprints or modifications.', 'learning', 4, '[\"Generate customer receipts\", \"Print kitchen orders\", \"Handle receipt reprints\"]', '{\"type\": \"receipt_demo\", \"types\": [\"Customer Receipt\", \"Kitchen Order\", \"Manager Copy\"]}', NULL, 1, 6, 1, '2025-10-02 02:53:24', '2025-10-02 02:53:24'),
(7, 1, 7, 'Handling Refunds', 'Refunds are sometimes necessary for customer satisfaction. Learn the proper procedures for processing refunds, handling return items, and maintaining accurate records for accounting purposes.', 'practical', 5, '[\"Process refunds\", \"Handle return items\", \"Maintain refund records\"]', '{\"type\": \"refund_process\", \"scenarios\": [\"Item return\", \"Service issue\", \"Price adjustment\"]}', NULL, 1, 7, 1, '2025-10-02 02:53:24', '2025-10-02 02:53:24'),
(8, 1, 8, 'POS Best Practices', 'Learn essential best practices for efficient POS operations, security measures, and customer service excellence. This includes maintaining accuracy, following security protocols, and providing excellent customer service.', 'summary', 6, '[\"Follow security protocols\", \"Maintain accuracy\", \"Provide excellent service\"]', '{\"type\": \"best_practices\", \"checklist\": [\"Verify orders\", \"Secure cash drawer\", \"Follow policies\", \"Clean workspace\"]}', NULL, 1, 8, 1, '2025-10-02 02:53:24', '2025-10-02 02:53:24'),
(9, 10, 1, 'Test Step 1 - Introduction', 'This is the first test step to verify the dynamic training system is working correctly. You will learn about the new dynamic features and how they enhance the learning experience.', 'introduction', 5, '[\"Verify system functionality\", \"Test progress tracking\", \"Confirm user interface\"]', '{\"type\": \"test_intro\", \"features\": [\"Dynamic content\", \"Real-time updates\", \"Interactive elements\"]}', NULL, 1, 1, 1, '2025-10-02 02:53:24', '2025-10-02 02:53:24'),
(10, 10, 2, 'Test Step 2 - Interactive Elements', 'Testing interactive elements and user engagement features in the dynamic training system. This includes quizzes, simulations, and other interactive components.', 'learning', 6, '[\"Test interactions\", \"Verify responsiveness\", \"Check animations\"]', '{\"type\": \"interactive_test\", \"elements\": [\"Quizzes\", \"Simulations\", \"Progress tracking\"]}', NULL, 1, 2, 1, '2025-10-02 02:53:24', '2025-10-02 02:53:24'),
(11, 10, 3, 'Test Step 3 - Progress Tracking', 'Verifying that progress is being tracked correctly in the database with the new dynamic system. This includes real-time updates and analytics.', 'practical', 7, '[\"Test database updates\", \"Verify progress calculation\", \"Check status changes\"]', '{\"type\": \"progress_test\", \"metrics\": [\"Completion rate\", \"Time spent\", \"Accuracy\"]}', NULL, 1, 3, 1, '2025-10-02 02:53:24', '2025-10-02 02:53:24'),
(12, 10, 4, 'Test Step 4 - Completion Logic', 'Testing the completion logic and final step processing in the dynamic training system. This ensures proper workflow and user experience.', 'practical', 6, '[\"Test completion flow\", \"Verify final status\", \"Check redirects\"]', '{\"type\": \"completion_test\", \"workflow\": [\"Step completion\", \"Module completion\", \"Certificate generation\"]}', NULL, 1, 4, 1, '2025-10-02 02:53:24', '2025-10-02 02:53:24'),
(13, 10, 5, 'Test Step 5 - Final Verification', 'Final verification that all dynamic training system components are working properly. This includes all features and integrations.', 'summary', 6, '[\"Complete final tests\", \"Verify all functionality\", \"Confirm system readiness\"]', '{\"type\": \"final_test\", \"verification\": [\"All features\", \"Database integrity\", \"User experience\"]}', NULL, 1, 5, 1, '2025-10-02 02:53:24', '2025-10-02 02:53:24');

-- --------------------------------------------------------

--
-- Table structure for table `dynamic_training_quizzes`
--

CREATE TABLE `dynamic_training_quizzes` (
  `id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `question_type` enum('multiple_choice','true_false','fill_blank','matching') DEFAULT 'multiple_choice',
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `correct_answer` varchar(500) DEFAULT NULL,
  `explanation` text DEFAULT NULL,
  `points` int(11) DEFAULT 1,
  `time_limit` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dynamic_training_quizzes`
--

INSERT INTO `dynamic_training_quizzes` (`id`, `content_id`, `question`, `question_type`, `options`, `correct_answer`, `explanation`, `points`, `time_limit`, `is_active`, `created_at`) VALUES
(1, 4, 'What should you do when a customer requests a modification to a menu item?', 'multiple_choice', '[\"Tell them it is not possible\", \"Add the modification note to the order\", \"Charge extra without asking\", \"Ignore the request\"]', 'Add the modification note to the order', 'Always add modification notes to ensure the kitchen prepares the item correctly and meets customer expectations.', 1, 0, 1, '2025-10-02 02:53:24');

-- --------------------------------------------------------

--
-- Table structure for table `dynamic_training_resources`
--

CREATE TABLE `dynamic_training_resources` (
  `id` int(11) NOT NULL,
  `tutorial_module_id` int(11) DEFAULT NULL,
  `content_id` int(11) DEFAULT NULL,
  `resource_type` enum('document','video','image','link','file') NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `download_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dynamic_training_resources`
--

INSERT INTO `dynamic_training_resources` (`id`, `tutorial_module_id`, `content_id`, `resource_type`, `title`, `description`, `file_path`, `file_size`, `mime_type`, `is_public`, `download_count`, `created_at`) VALUES
(1, 1, NULL, 'document', 'POS Quick Reference Guide', 'A quick reference guide for common POS operations', '/resources/pos-quick-reference.pdf', NULL, NULL, 1, 0, '2025-10-02 02:53:24'),
(2, 1, NULL, 'video', 'POS System Overview', 'Video introduction to the POS system interface', '/resources/pos-overview.mp4', NULL, NULL, 1, 0, '2025-10-02 02:53:24'),
(3, 1, NULL, 'link', 'POS Support Documentation', 'Link to comprehensive POS documentation', 'https://support.example.com/pos', NULL, NULL, 1, 0, '2025-10-02 02:53:24');

-- --------------------------------------------------------

--
-- Table structure for table `dynamic_training_simulations`
--

CREATE TABLE `dynamic_training_simulations` (
  `id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `simulation_type` enum('pos_order','inventory_check','booking_process','payment_processing') NOT NULL,
  `simulation_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`simulation_data`)),
  `success_criteria` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`success_criteria`)),
  `instructions` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dynamic_training_simulations`
--

INSERT INTO `dynamic_training_simulations` (`id`, `content_id`, `simulation_type`, `simulation_data`, `success_criteria`, `instructions`, `is_active`, `created_at`) VALUES
(1, 3, 'pos_order', '{\"scenarios\": [{\"customer_type\": \"walk_in\", \"items\": [\"Coffee\", \"Sandwich\"], \"total\": 12.50}, {\"customer_type\": \"hotel_guest\", \"items\": [\"Breakfast\", \"Juice\"], \"total\": 18.75}]}', '{\"completion_time\": 300, \"accuracy\": 100}', 'Complete the order process for each scenario within the time limit and with 100% accuracy.', 1, '2025-10-02 02:53:24');

-- --------------------------------------------------------

--
-- Table structure for table `dynamic_training_tags`
--

CREATE TABLE `dynamic_training_tags` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `color` varchar(20) DEFAULT 'blue',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dynamic_training_tags`
--

INSERT INTO `dynamic_training_tags` (`id`, `name`, `color`, `description`, `created_at`) VALUES
(1, 'Beginner', 'green', 'Suitable for beginners', '2025-10-02 02:53:24'),
(2, 'Intermediate', 'yellow', 'Intermediate level training', '2025-10-02 02:53:24'),
(3, 'Advanced', 'red', 'Advanced level training', '2025-10-02 02:53:24'),
(4, 'Essential', 'blue', 'Essential training for all staff', '2025-10-02 02:53:24'),
(5, 'Optional', 'gray', 'Optional training modules', '2025-10-02 02:53:24');

-- --------------------------------------------------------

--
-- Table structure for table `floors`
--

CREATE TABLE `floors` (
  `id` int(11) NOT NULL,
  `floor_number` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `floors`
--

INSERT INTO `floors` (`id`, `floor_number`, `name`, `description`, `active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Ground Floor', NULL, 1, '2025-10-14 01:06:12', '2025-10-14 01:06:12'),
(2, 2, 'First Floor', NULL, 1, '2025-10-14 01:06:12', '2025-10-14 01:06:12'),
(3, 3, 'Second Floor', NULL, 1, '2025-10-14 01:06:12', '2025-10-14 01:06:12'),
(4, 4, 'Third Floor', NULL, 1, '2025-10-14 01:06:12', '2025-10-14 01:06:12'),
(5, 5, 'Fourth Floor', NULL, 1, '2025-10-14 01:06:12', '2025-10-14 01:06:12'),
(6, 6, 'Fifth Floor', NULL, 1, '2025-10-14 01:16:13', '2025-10-14 01:16:13');

-- --------------------------------------------------------

--
-- Table structure for table `group_bookings`
--

CREATE TABLE `group_bookings` (
  `id` int(11) NOT NULL,
  `group_name` varchar(100) NOT NULL,
  `group_size` int(11) NOT NULL,
  `group_discount` decimal(5,2) DEFAULT 10.00,
  `reservation_id` int(11) NOT NULL,
  `guest_name` varchar(100) NOT NULL,
  `room_number` varchar(10) NOT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guests`
--

CREATE TABLE `guests` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `id_type` enum('passport','driver_license','national_id','other') NOT NULL,
  `id_number` varchar(50) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `is_vip` tinyint(1) DEFAULT 0,
  `preferences` text DEFAULT NULL,
  `service_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `guests`
--

INSERT INTO `guests` (`id`, `first_name`, `last_name`, `email`, `phone`, `address`, `id_type`, `id_number`, `date_of_birth`, `nationality`, `is_vip`, `preferences`, `service_notes`, `created_at`, `updated_at`) VALUES
(1, 'Alexander', 'Thompson', 'alex.thompson@email.com', '+1-555-0123', '123 Park Avenue, New York, NY 10001', 'passport', 'US123456789', '1985-03-15', 'American', 0, 'Non-smoking room, High floor, King bed', 'Business traveler, prefers early check-in', '2025-08-27 10:02:44', '2025-10-02 03:37:17'),
(2, 'Isabella', 'Martinez', 'isabella.martinez@email.com', '+1-555-0456', '456 Sunset Boulevard, Los Angeles, CA 90210', 'driver_license', 'CA987654321', '1990-07-22', 'American', 1, 'Ocean view, Late check-out, Spa services', 'VIP guest, anniversary celebration, champagne on arrival', '2025-08-27 10:02:44', '2025-10-02 03:37:17'),
(3, 'Benjamin', 'Anderson', 'benjamin.anderson@email.com', '+1-555-0789', '789 Michigan Avenue, Chicago, IL 60601', 'national_id', 'IL456789123', '1978-11-08', 'American', 0, 'Quiet room, Business center access', 'Corporate client, frequent guest', '2025-08-27 10:02:44', '2025-10-02 03:37:17'),
(4, 'Sophia', 'Williams', 'sophia.williams@email.com', '+1-555-0321', '321 Collins Avenue, Miami, FL 33139', 'passport', 'US789123456', '1992-05-14', 'American', 1, 'Beach view, Pool access, Concierge services', 'VIP guest, honeymoon celebration', '2025-08-27 10:02:44', '2025-10-02 03:37:17'),
(5, 'William', 'Brown', 'william.brown@email.com', '+1-555-0654', '654 Market Street, San Francisco, CA 94102', 'driver_license', 'CA321654987', '1987-09-30', 'American', 0, 'City view, Fitness center access', 'Tech executive, prefers suite upgrades', '2025-08-27 10:02:44', '2025-10-02 03:37:17'),
(6, 'Emma', 'Davis', 'emma.davis@email.com', '+1-555-0987', '987 Broadway, Seattle, WA 98101', 'passport', 'US654987321', '1995-12-03', 'American', 0, 'Pet-friendly room, Room service', 'Traveling with small dog, requires pet amenities', '2025-08-27 10:02:44', '2025-10-02 03:37:17'),
(7, 'James', 'Wilson', 'james.wilson@email.com', '+1-555-0135', '135 Peachtree Street, Atlanta, GA 30309', 'national_id', 'GA135792468', '1983-04-18', 'American', 1, 'Executive floor, Business services', 'VIP guest, corporate account, requires meeting room access', '2025-08-27 10:02:44', '2025-10-02 03:37:17'),
(8, 'Olivia', 'Garcia', 'olivia.garcia@email.com', '+1-555-0246', '246 Bourbon Street, New Orleans, LA 70112', 'driver_license', 'LA246813579', '1991-08-25', 'American', 0, 'Historic district view, Local restaurant recommendations', 'Food blogger, interested in local cuisine', '2025-08-27 10:02:44', '2025-10-02 03:37:17'),
(9, 'Michael', 'Rodriguez', 'michael.rodriguez@email.com', '+1-555-0369', '369 Las Vegas Boulevard, Las Vegas, NV 89101', 'passport', 'US369258147', '1989-01-12', 'American', 1, 'Strip view, Casino access, VIP services', 'High roller, requires premium services', '2025-08-27 10:02:44', '2025-10-02 03:37:17'),
(10, 'Johnson', 'Taylor', 'taylor.johnson@email.com', '+1-555-0482', '482 Beacon Street, Boston, MA 02115', 'national_id', 'MA482591736', '1994-06-07', 'American', 0, 'Historic view, Walking distance to attractions', 'Student group leader, requires multiple room bookings', '2025-08-27 10:02:44', '2025-10-02 03:37:17'),
(11, 'ge', 'ge', 'ge@gmail.com', '09195512042', 'wew', 'other', '1212', '2002-05-12', 'pinoy', 1, '3', '2', '2025-10-08 03:46:23', '2025-10-08 04:00:35'),
(12, 'NOne', 'none', 'none@gmail.com', '09195512047', 'weq', 'national_id', '5235', '2000-05-12', 'pinoy', 1, 'qwe', 'e', '2025-10-08 05:50:35', '2025-10-10 03:54:06'),
(14, 'john', 'In', 'lcx21_pelle@yahoo.com', '09195512048', NULL, 'other', '5235', NULL, NULL, 0, NULL, NULL, '2025-10-13 03:18:05', '2025-10-13 03:18:05');

-- --------------------------------------------------------

--
-- Table structure for table `guest_feedback`
--

CREATE TABLE `guest_feedback` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `guest_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `feedback_type` enum('compliment','complaint','suggestion','general') NOT NULL,
  `category` enum('service','cleanliness','facilities','staff','food','other') NOT NULL,
  `comments` text DEFAULT NULL,
  `is_resolved` tinyint(1) DEFAULT 0,
  `resolved_by` int(11) DEFAULT NULL,
  `resolution_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `guest_feedback`
--

INSERT INTO `guest_feedback` (`id`, `reservation_id`, `guest_id`, `rating`, `feedback_type`, `category`, `comments`, `is_resolved`, `resolved_by`, `resolution_notes`, `created_at`, `updated_at`) VALUES
(4, 1, 1, 5, 'compliment', 'service', 'qw', 0, NULL, NULL, '2025-10-08 10:38:02', '2025-10-08 10:38:02'),
(5, 1, 1, 5, 'general', 'service', 'ww', 0, NULL, NULL, '2025-10-10 02:47:09', '2025-10-10 02:47:09');

-- --------------------------------------------------------

--
-- Table structure for table `hotel_floors`
--

CREATE TABLE `hotel_floors` (
  `id` int(11) NOT NULL,
  `floor_number` int(11) NOT NULL,
  `floor_name` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hotel_floors`
--

INSERT INTO `hotel_floors` (`id`, `floor_number`, `floor_name`, `description`, `active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Ground Floor', 'Lobby, restaurant, and common areas', 1, '2025-10-01 15:41:11', '2025-10-01 15:41:11'),
(2, 2, 'First Floor', 'Standard rooms and suites', 1, '2025-10-01 15:41:11', '2025-10-01 15:41:11'),
(3, 3, 'Second Floor', 'Standard rooms and suites', 1, '2025-10-01 15:41:11', '2025-10-01 15:41:11'),
(4, 4, 'Third Floor', 'Premium rooms and suites', 1, '2025-10-01 15:41:11', '2025-10-01 15:41:11'),
(5, 5, 'Fourth Floor', 'Executive suites and penthouse', 1, '2025-10-01 15:41:11', '2025-10-01 15:41:11');

-- --------------------------------------------------------

--
-- Table structure for table `hotel_rooms`
--

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

--
-- Dumping data for table `hotel_rooms`
--

INSERT INTO `hotel_rooms` (`id`, `room_number`, `floor_id`, `room_type`, `status`, `max_occupancy`, `description`, `active`, `created_at`, `updated_at`) VALUES
(1, '101', 2, 'standard', 'available', 2, NULL, 1, '2025-10-01 15:41:11', '2025-10-01 15:41:11'),
(2, '102', 2, 'standard', 'available', 2, NULL, 1, '2025-10-01 15:41:11', '2025-10-01 15:41:11'),
(3, '103', 2, 'standard', 'occupied', 2, NULL, 1, '2025-10-01 15:41:11', '2025-10-01 15:41:11'),
(4, '201', 3, 'standard', 'available', 2, NULL, 1, '2025-10-01 15:41:11', '2025-10-01 15:41:11'),
(5, '202', 3, 'standard', 'maintenance', 2, NULL, 1, '2025-10-01 15:41:11', '2025-10-01 15:41:11'),
(6, '301', 4, 'premium', 'available', 4, NULL, 1, '2025-10-01 15:41:11', '2025-10-01 15:41:11'),
(7, '302', 4, 'premium', 'occupied', 4, NULL, 1, '2025-10-01 15:41:11', '2025-10-01 15:41:11'),
(8, '401', 5, 'executive', 'available', 6, NULL, 1, '2025-10-01 15:41:11', '2025-10-01 15:41:11'),
(9, '501', 5, 'penthouse', 'available', 8, NULL, 1, '2025-10-01 15:41:11', '2025-10-01 15:41:11');

-- --------------------------------------------------------

--
-- Table structure for table `housekeeping_carts`
--

CREATE TABLE `housekeeping_carts` (
  `id` int(11) NOT NULL,
  `cart_number` varchar(20) NOT NULL,
  `floor_id` int(11) NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `status` enum('active','maintenance','retired') DEFAULT 'active',
  `last_updated` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `housekeeping_carts`
--

INSERT INTO `housekeeping_carts` (`id`, `cart_number`, `floor_id`, `assigned_to`, `status`, `last_updated`, `created_at`, `updated_at`) VALUES
(1, 'CART-001', 2, NULL, 'active', NULL, '2025-10-01 15:41:11', '2025-10-01 15:41:11'),
(2, 'CART-002', 3, NULL, 'active', NULL, '2025-10-01 15:41:11', '2025-10-01 15:41:11'),
(3, 'CART-003', 4, NULL, 'active', NULL, '2025-10-01 15:41:11', '2025-10-01 15:41:11'),
(4, 'CART-004', 5, NULL, 'active', NULL, '2025-10-01 15:41:11', '2025-10-01 15:41:11');

-- --------------------------------------------------------

--
-- Table structure for table `housekeeping_tasks`
--

CREATE TABLE `housekeeping_tasks` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `task_type` enum('daily_cleaning','turn_down','deep_cleaning','maintenance','inspection') NOT NULL,
  `status` enum('pending','in_progress','completed','verified') DEFAULT 'pending',
  `assigned_to` int(11) DEFAULT NULL,
  `scheduled_time` datetime DEFAULT NULL,
  `completed_time` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `housekeeping_tasks`
--

INSERT INTO `housekeeping_tasks` (`id`, `room_id`, `task_type`, `status`, `assigned_to`, `scheduled_time`, `completed_time`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'daily_cleaning', 'completed', 1003, NULL, NULL, NULL, 1003, '2025-10-09 06:34:21', '2025-10-09 06:34:21'),
(2, 1, 'maintenance', 'completed', 1003, NULL, NULL, NULL, 1003, '2025-10-09 06:34:28', '2025-10-09 06:34:28'),
(3, 1, 'maintenance', 'completed', 1003, NULL, NULL, NULL, 1003, '2025-10-09 06:41:09', '2025-10-09 06:41:09'),
(5, 4, 'turn_down', 'pending', 5, '2025-10-11 00:20:00', NULL, 'w', 1003, '2025-10-11 04:20:44', '2025-10-11 04:20:44'),
(9, 1, 'deep_cleaning', 'pending', 4, '2025-10-11 12:16:00', NULL, 'Test task creation', 1, '2025-10-11 04:22:34', '2025-10-11 04:22:34'),
(10, 2, 'daily_cleaning', 'pending', 4, '2025-10-11 00:24:00', NULL, 'w', 1003, '2025-10-11 04:24:04', '2025-10-11 04:24:04'),
(14, 3, 'inspection', 'pending', 4, '2025-10-11 12:27:00', NULL, '123', 1003, '2025-10-11 04:24:22', '2025-10-11 04:24:22'),
(16, 2, 'deep_cleaning', 'pending', 4, '2025-10-11 12:29:00', NULL, 'wew', 1003, '2025-10-11 04:28:48', '2025-10-11 04:28:48'),
(17, 3, 'turn_down', 'pending', 1008, '2025-10-11 12:29:00', NULL, 'wwww', 1003, '2025-10-11 04:29:16', '2025-10-11 04:29:16'),
(18, 3, 'turn_down', 'pending', 1008, '2025-10-11 12:29:00', NULL, 'wwww', 1, '2025-10-11 04:30:23', '2025-10-11 04:30:23'),
(19, 4, 'maintenance', 'pending', 5, '2025-10-11 12:33:00', NULL, 'sample', 1003, '2025-10-11 04:32:09', '2025-10-11 04:32:09');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` enum('linens','amenities','cleaning_supplies','maintenance','food_beverage') NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `unit` varchar(20) NOT NULL,
  `reorder_level` int(11) NOT NULL DEFAULT 10,
  `unit_cost` decimal(10,2) NOT NULL,
  `supplier` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `item_name`, `category`, `description`, `quantity`, `unit`, `reorder_level`, `unit_cost`, `supplier`, `created_at`, `updated_at`) VALUES
(1, 'Bath Towels', 'linens', 'White bath towels', 200, 'pieces', 50, 15.00, 'Linen Supply Co', '2025-08-27 10:02:44', '2025-08-27 10:02:44'),
(2, 'Bed Sheets', 'linens', 'King size bed sheets', 100, 'sets', 20, 45.00, 'Linen Supply Co', '2025-08-27 10:02:44', '2025-08-27 10:02:44'),
(3, 'Shampoo', 'amenities', 'Hotel brand shampoo', 500, 'bottles', 100, 2.50, 'Amenity Supply', '2025-08-27 10:02:44', '2025-08-27 10:02:44'),
(4, 'Soap', 'amenities', 'Hotel brand soap', 500, 'bars', 100, 1.50, 'Amenity Supply', '2025-08-27 10:02:44', '2025-08-27 10:02:44'),
(5, 'Cleaning Solution', 'cleaning_supplies', 'Multi-purpose cleaner', 50, 'bottles', 10, 8.00, 'Cleaning Supply Co', '2025-08-27 10:02:44', '2025-08-27 10:02:44'),
(6, 'Wrench Set', 'maintenance', 'Basic tool set', 5, 'sets', 2, 25.00, 'Tool Supply Co', '2025-08-27 10:02:44', '2025-08-27 10:02:44'),
(7, 'Mini Bar Coke', 'food_beverage', 'Coca Cola cans', 100, 'cans', 20, 3.00, 'Beverage Supply', '2025-08-27 10:02:44', '2025-08-27 10:02:44'),
(8, 'Mini Bar Water', 'food_beverage', 'Bottled water', 150, 'bottles', 30, 2.00, 'Beverage Supply', '2025-08-27 10:02:44', '2025-08-27 10:02:44'),
(9, 'Bath Towels', 'linens', 'High-quality Bath Towels for hotel operations', 150, 'pieces', 20, 8.50, 'Luxury Linens Inc.', '2025-10-02 03:37:17', '2025-10-02 03:37:17'),
(10, 'Hand Towels', 'linens', 'High-quality Hand Towels for hotel operations', 200, 'pieces', 30, 4.25, 'Luxury Linens Inc.', '2025-10-02 03:37:17', '2025-10-02 03:37:17'),
(11, 'Bathrobes', 'linens', 'High-quality Bathrobes for hotel operations', 75, 'pieces', 15, 35.00, 'Luxury Linens Inc.', '2025-10-02 03:37:17', '2025-10-02 03:37:17'),
(12, 'Shampoo Bottles', 'amenities', 'High-quality Shampoo Bottles for hotel operations', 300, 'bottles', 50, 2.75, 'Premium Amenities Co.', '2025-10-02 03:37:17', '2025-10-02 03:37:17'),
(13, 'Conditioner Bottles', 'amenities', 'High-quality Conditioner Bottles for hotel operations', 300, 'bottles', 50, 2.75, 'Premium Amenities Co.', '2025-10-02 03:37:17', '2025-10-02 03:37:17'),
(14, 'Body Lotion', 'amenities', 'High-quality Body Lotion for hotel operations', 250, 'bottles', 40, 3.25, 'Premium Amenities Co.', '2025-10-02 03:37:17', '2025-10-02 03:37:17'),
(15, 'All-Purpose Cleaner', 'cleaning_supplies', 'High-quality All-Purpose Cleaner for hotel operations', 25, 'bottles', 5, 12.50, 'Clean Solutions Ltd.', '2025-10-02 03:37:17', '2025-10-02 03:37:17'),
(16, 'Glass Cleaner', 'cleaning_supplies', 'High-quality Glass Cleaner for hotel operations', 20, 'bottles', 5, 8.75, 'Clean Solutions Ltd.', '2025-10-02 03:37:17', '2025-10-02 03:37:17'),
(17, 'Vacuum Bags', 'cleaning_supplies', 'High-quality Vacuum Bags for hotel operations', 100, 'bags', 20, 1.25, 'Clean Solutions Ltd.', '2025-10-02 03:37:17', '2025-10-02 03:37:17'),
(18, 'Light Bulbs', 'maintenance', 'High-quality Light Bulbs for hotel operations', 50, 'pieces', 10, 5.50, 'Maintenance Supplies Inc.', '2025-10-02 03:37:17', '2025-10-02 03:37:17'),
(19, 'Coffee Beans', 'food_beverage', 'High-quality Coffee Beans for hotel operations', 15, 'kg', 3, 25.00, 'Gourmet Coffee Co.', '2025-10-02 03:37:17', '2025-10-02 03:37:17'),
(20, 'Tea Bags', 'food_beverage', 'High-quality Tea Bags for hotel operations', 200, 'boxes', 30, 3.50, 'Gourmet Coffee Co.', '2025-10-02 03:37:17', '2025-10-02 03:37:17');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_alerts`
--

CREATE TABLE `inventory_alerts` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `alert_type` enum('low_stock','out_of_stock','expired','overstock') NOT NULL,
  `message` text NOT NULL,
  `severity` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolved_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_categories`
--

CREATE TABLE `inventory_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_categories`
--

INSERT INTO `inventory_categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Food & Beverage', 'Auto-created category for seeded items', '2025-10-01 16:20:20'),
(2, 'Amenities', 'Auto-created category for seeded items', '2025-10-01 16:20:20'),
(3, 'Cleaning Supplies', 'Auto-created category for seeded items', '2025-10-01 16:20:20'),
(4, 'Office Supplies', 'Auto-created category for seeded items', '2025-10-01 16:20:20'),
(5, 'Maintenance', 'Auto-created category for seeded items', '2025-10-01 16:20:20'),
(6, 'Furniture', 'Chairs, tables, and other furniture items', '2025-10-14 01:06:12'),
(7, 'Kitchen Supplies', 'Kitchen utensils and appliances', '2025-10-14 01:06:12'),
(10, 'Safety Equipment', 'Fire extinguishers, first aid kits, and safety items', '2025-10-14 01:06:12');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_integration_log`
--

CREATE TABLE `inventory_integration_log` (
  `id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `source_type` enum('pos','booking','manual') DEFAULT NULL,
  `synced_count` int(11) NOT NULL DEFAULT 0,
  `created_count` int(11) NOT NULL DEFAULT 0,
  `updated_count` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_integration_mapping`
--

CREATE TABLE `inventory_integration_mapping` (
  `id` int(11) NOT NULL,
  `source_type` enum('pos','booking') NOT NULL,
  `source_id` int(11) NOT NULL,
  `inventory_item_id` int(11) NOT NULL,
  `auto_sync` tinyint(1) NOT NULL DEFAULT 1,
  `last_synced` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE `inventory_items` (
  `id` int(11) NOT NULL,
  `item_name` varchar(200) NOT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `current_stock` int(11) NOT NULL DEFAULT 0,
  `minimum_stock` int(11) NOT NULL DEFAULT 0,
  `maximum_stock` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT 0.00,
  `cost_price` decimal(10,2) DEFAULT 0.00,
  `unit` varchar(20) DEFAULT 'pcs',
  `supplier` varchar(100) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_pos_product` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','inactive','discontinued') DEFAULT 'active',
  `description` text DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_items`
--

INSERT INTO `inventory_items` (`id`, `item_name`, `sku`, `category_id`, `current_stock`, `minimum_stock`, `maximum_stock`, `unit_price`, `cost_price`, `unit`, `supplier`, `location`, `barcode`, `image`, `is_pos_product`, `status`, `description`, `last_updated`, `created_at`) VALUES
(1, 'Coffee Beans (Arabica)', 'FB-COFFEE-001', 1, 50, 10, NULL, 450.00, 450.00, 'Kilogram', 'Premium Coffee Supply Co.', NULL, NULL, NULL, 1, 'active', 'Premium Arabica coffee beans for hotel restaurant | SKU: FB-COFFEE-001 | Unit: Kilogram | Supplier: Premium Coffee Supply Co.', '2025-10-14 01:27:24', '2025-10-01 16:20:20'),
(2, 'Tea Bags (Assorted)', 'FB-TEA-002', 1, 25, 5, NULL, 120.00, 120.00, 'Box', 'Tea Masters Inc.', NULL, NULL, NULL, 1, 'active', 'Assorted tea bags for room service | SKU: FB-TEA-002 | Unit: Box | Supplier: Tea Masters Inc.', '2025-10-14 01:27:24', '2025-10-01 16:20:20'),
(3, 'Bottled Water (500ml)', 'FB-WATER-003', 1, 200, 50, NULL, 15.00, 15.00, 'Bottle', 'Pure Water Solutions', NULL, NULL, NULL, 1, 'active', 'Premium bottled water for guest rooms | SKU: FB-WATER-003 | Unit: Bottle | Supplier: Pure Water Solutions', '2025-10-14 01:27:24', '2025-10-01 16:20:20'),
(4, 'Breakfast Cereal', 'FB-CEREAL-004', 1, 30, 8, NULL, 85.00, 85.00, 'Box', 'Morning Delights Co.', NULL, NULL, NULL, 1, 'active', 'Assorted breakfast cereals | SKU: FB-CEREAL-004 | Unit: Box | Supplier: Morning Delights Co.', '2025-10-14 01:27:24', '2025-10-01 16:20:20'),
(5, 'Fresh Milk (1L)', 'FB-MILK-005', 1, 40, 10, NULL, 65.00, 65.00, 'Liter', 'Dairy Fresh Farms', NULL, NULL, NULL, 1, 'active', 'Fresh whole milk for breakfast service | SKU: FB-MILK-005 | Unit: Liter | Supplier: Dairy Fresh Farms', '2025-10-14 01:27:24', '2025-10-01 16:20:20'),
(6, 'Bath Towels (White)', 'AM-TOWEL-001', 2, 150, 30, NULL, 180.00, 180.00, 'Piece', 'Luxury Linens Ltd.', NULL, NULL, NULL, 0, 'active', 'Premium white bath towels | SKU: AM-TOWEL-001 | Unit: Piece | Supplier: Luxury Linens Ltd.', '2025-10-14 01:16:13', '2025-10-01 16:20:20'),
(7, 'Hand Towels', 'AM-HANDTOWEL-002', 2, 200, 40, NULL, 95.00, 95.00, 'Piece', 'Luxury Linens Ltd.', NULL, NULL, NULL, 0, 'active', 'Soft hand towels for guest bathrooms | SKU: AM-HANDTOWEL-002 | Unit: Piece | Supplier: Luxury Linens Ltd.', '2025-10-14 01:16:13', '2025-10-01 16:20:20'),
(8, 'Bathrobes (Cotton)', 'AM-BATHROBE-003', 2, 80, 20, NULL, 450.00, 450.00, 'Piece', 'Luxury Linens Ltd.', NULL, NULL, NULL, 0, 'active', 'Premium cotton bathrobes | SKU: AM-BATHROBE-003 | Unit: Piece | Supplier: Luxury Linens Ltd.', '2025-10-14 01:16:13', '2025-10-01 16:20:20'),
(9, 'Shampoo (Hotel Size)', 'AM-SHAMPOO-004', 2, 300, 60, NULL, 25.00, 25.00, 'Bottle', 'Spa Essentials Co.', NULL, NULL, NULL, 0, 'active', 'Premium hotel-size shampoo bottles | SKU: AM-SHAMPOO-004 | Unit: Bottle | Supplier: Spa Essentials Co.', '2025-10-14 01:16:13', '2025-10-01 16:20:20'),
(10, 'Body Lotion', 'AM-LOTION-005', 2, 250, 50, NULL, 22.00, 22.00, 'Bottle', 'Spa Essentials Co.', NULL, NULL, NULL, 0, 'active', 'Moisturizing body lotion | SKU: AM-LOTION-005 | Unit: Bottle | Supplier: Spa Essentials Co.', '2025-10-14 01:16:13', '2025-10-01 16:20:20'),
(11, 'Soap Bars (Luxury)', 'AM-SOAP-006', 2, 400, 80, NULL, 20.00, 20.00, 'Piece', 'Spa Essentials Co.', NULL, NULL, NULL, 0, 'active', 'Luxury soap bars for guest bathrooms | SKU: AM-SOAP-006 | Unit: Piece | Supplier: Spa Essentials Co.', '2025-10-14 01:16:13', '2025-10-01 16:20:20'),
(12, 'All-Purpose Cleaner', 'CS-CLEANER-001', 3, 50, 10, NULL, 85.00, 85.00, 'Bottle', 'CleanPro Solutions', NULL, NULL, NULL, 0, 'active', 'Multi-surface cleaning solution | SKU: CS-CLEANER-001 | Unit: Bottle | Supplier: CleanPro Solutions', '2025-10-14 01:16:13', '2025-10-01 16:20:20'),
(13, 'Glass Cleaner', 'CS-GLASS-002', 3, 30, 8, NULL, 75.00, 75.00, 'Bottle', 'CleanPro Solutions', NULL, NULL, NULL, 0, 'active', 'Streak-free glass cleaning solution | SKU: CS-GLASS-002 | Unit: Bottle | Supplier: CleanPro Solutions', '2025-10-14 01:16:13', '2025-10-01 16:20:20'),
(14, 'Disinfectant Spray', 'CS-DISINFECT-003', 3, 40, 10, NULL, 120.00, 120.00, 'Bottle', 'CleanPro Solutions', NULL, NULL, NULL, 0, 'active', 'Hospital-grade disinfectant spray | SKU: CS-DISINFECT-003 | Unit: Bottle | Supplier: CleanPro Solutions', '2025-10-14 01:16:13', '2025-10-01 16:20:20'),
(15, 'Microfiber Cloths', 'CS-CLOTHS-004', 3, 25, 5, NULL, 150.00, 150.00, 'Pack', 'CleanPro Solutions', NULL, NULL, NULL, 0, 'active', 'Reusable microfiber cleaning cloths | SKU: CS-CLOTHS-004 | Unit: Pack | Supplier: CleanPro Solutions', '2025-10-14 01:16:13', '2025-10-01 16:20:20'),
(16, 'Vacuum Bags', 'CS-VACBAG-005', 3, 20, 5, NULL, 200.00, 200.00, 'Pack', 'CleanPro Solutions', NULL, NULL, NULL, 0, 'active', 'Replacement vacuum cleaner bags | SKU: CS-VACBAG-005 | Unit: Pack | Supplier: CleanPro Solutions', '2025-10-14 01:16:13', '2025-10-01 16:20:20'),
(17, 'A4 Paper (White)', 'OS-PAPER-001', 4, 15, 3, NULL, 180.00, 180.00, 'Ream', 'Office Depot Pro', NULL, NULL, NULL, 0, 'active', 'Premium white A4 copy paper | SKU: OS-PAPER-001 | Unit: Ream | Supplier: Office Depot Pro', '2025-10-14 01:16:13', '2025-10-01 16:20:20'),
(19, 'Stapler with Staples', 'OS-STAPLER-003', 4, 5, 1, NULL, 350.00, 350.00, 'Piece', 'Office Depot Pro', NULL, NULL, NULL, 0, 'active', 'Heavy-duty stapler with staple refills | SKU: OS-STAPLER-003 | Unit: Piece | Supplier: Office Depot Pro', '2025-10-14 01:16:13', '2025-10-01 16:20:20'),
(20, 'File Folders (Manila)', 'OS-FOLDERS-004', 4, 8, 2, NULL, 95.00, 95.00, 'Box', 'Office Depot Pro', NULL, NULL, NULL, 0, 'active', 'Manila file folders for guest records | SKU: OS-FOLDERS-004 | Unit: Box | Supplier: Office Depot Pro', '2025-10-14 01:16:13', '2025-10-01 16:20:20'),
(21, 'Light Bulbs (LED)', 'MT-LED-001', 5, 100, 20, NULL, 45.00, 45.00, 'Piece', 'Electrical Supply Co.', NULL, NULL, NULL, 0, 'active', 'Energy-efficient LED light bulbs | SKU: MT-LED-001 | Unit: Piece | Supplier: Electrical Supply Co.', '2025-10-14 01:16:13', '2025-10-01 16:20:20'),
(22, 'Air Filter (HVAC)', 'MT-FILTER-002', 5, 20, 5, NULL, 250.00, 250.00, 'Piece', 'HVAC Solutions Inc.', NULL, NULL, NULL, 0, 'active', 'Replacement air filters for HVAC system | SKU: MT-FILTER-002 | Unit: Piece | Supplier: HVAC Solutions Inc.', '2025-10-14 01:16:13', '2025-10-01 16:20:20'),
(23, 'Paint (White)', 'MT-PAINT-003', 5, 8, 2, NULL, 450.00, 450.00, 'Gallon', 'Paint & Decor Co.', NULL, NULL, NULL, 0, 'active', 'Interior white paint for touch-ups | SKU: MT-PAINT-003 | Unit: Gallon | Supplier: Paint & Decor Co.', '2025-10-14 01:16:13', '2025-10-01 16:20:20'),
(24, 'Door Handles (Brass)', 'MT-HANDLES-004', 5, 25, 5, NULL, 180.00, 180.00, 'Piece', 'Hardware Solutions', NULL, NULL, NULL, 0, 'active', 'Brass door handles for guest rooms | SKU: MT-HANDLES-004 | Unit: Piece | Supplier: Hardware Solutions', '2025-10-14 01:16:13', '2025-10-01 16:20:20'),
(25, 'wew', 'SKU-0025', 2, 5, 5, NULL, 20.00, 20.00, 'pcs', 'ABC Supplies Inc.', NULL, NULL, NULL, 0, 'active', 'we', '2025-10-14 01:27:24', '2025-10-13 08:01:50'),
(27, 'wew', '555', 2, 10, 3, NULL, 50.00, 50.00, 'pcs', NULL, NULL, NULL, NULL, 0, 'active', 'wew | SKU: 555', '2025-10-14 01:16:13', '2025-10-13 08:06:17'),
(28, 'toothpaste', 'SKU-0028', 3, 20, 5, NULL, 25.00, 25.00, 'pcs', 'ABC Supplies Inc.', NULL, NULL, NULL, 0, 'active', 'wew12', '2025-10-14 01:27:24', '2025-10-13 08:12:00'),
(29, 'toothpaste', 'SKU-0029', 3, 5, 5, NULL, 0.00, 0.00, 'pcs', 'ABC Supplies Inc.', NULL, NULL, NULL, 0, 'active', 'wew', '2025-10-14 01:27:24', '2025-10-13 08:17:25'),
(30, 'free', 'SKU-0030', 2, 22, 3, NULL, 0.00, 0.00, 'pcs', 'ABC Supplies Inc.', NULL, NULL, NULL, 0, 'active', 'wew', '2025-10-14 01:27:24', '2025-10-13 08:24:28'),
(31, 'free', 'SKU-0031', 4, 2, 3, NULL, 0.00, 0.00, 'pcs', 'ABC Supplies Inc.', NULL, NULL, NULL, 0, 'active', 'wew', '2025-10-14 01:27:24', '2025-10-13 08:25:07'),
(32, 'games', 'SKU-0032', 4, 21, 5, NULL, 0.00, 0.00, 'pcs', 'ABC Supplies Inc.', NULL, NULL, NULL, 0, 'active', 'qwe', '2025-10-14 01:27:24', '2025-10-13 08:27:13'),
(33, 'fggg', 'SKU-0033', 4, 20, 5, NULL, 0.00, 0.00, 'pcs', 'ABC Supplies Inc.', NULL, NULL, NULL, 0, 'active', 'wew', '2025-10-14 01:27:24', '2025-10-13 08:29:12'),
(34, 'kurt\n                                wew', 'SKU-0034', 4, 20, 5, NULL, 0.00, 0.00, 'pcs', 'ABC Supplies Inc.', NULL, NULL, NULL, 0, 'active', 'wew', '2025-10-14 01:27:24', '2025-10-13 08:30:22'),
(35, 'adriane\n                                qwe | SKU: 512 | Unit: Bottle | Supplier: noel', '512', 2, 50, 20, NULL, 2000.00, 2000.00, 'Bottle', 'noel', NULL, NULL, NULL, 0, 'active', 'qwe | SKU: 512 | Unit: Bottle | Supplier: noel', '2025-10-14 01:16:13', '2025-10-13 08:36:21');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_requests`
--

CREATE TABLE `inventory_requests` (
  `id` int(11) NOT NULL,
  `request_number` varchar(20) NOT NULL,
  `requested_by` int(11) NOT NULL,
  `department` varchar(50) NOT NULL,
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `status` enum('pending','approved','rejected','fulfilled','cancelled') DEFAULT 'pending',
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `required_date` date DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_request_items`
--

CREATE TABLE `inventory_request_items` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity_requested` int(11) NOT NULL,
  `quantity_approved` int(11) DEFAULT NULL,
  `quantity_issued` int(11) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_suppliers`
--

CREATE TABLE `inventory_suppliers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `payment_terms` varchar(50) DEFAULT NULL,
  `rating` decimal(2,1) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_training_progress`
--

CREATE TABLE `inventory_training_progress` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `scenario_id` int(11) NOT NULL,
  `status` enum('not_started','in_progress','completed','failed') DEFAULT 'not_started',
  `score` int(11) DEFAULT NULL,
  `time_taken` int(11) DEFAULT NULL,
  `attempts` int(11) DEFAULT 1,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_training_scenarios`
--

CREATE TABLE `inventory_training_scenarios` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `scenario_type` enum('stock_management','reorder_process','inventory_audit','supplier_management','cost_control') NOT NULL,
  `difficulty` enum('beginner','intermediate','advanced') NOT NULL,
  `estimated_time` int(11) NOT NULL DEFAULT 15,
  `points` int(11) NOT NULL DEFAULT 10,
  `instructions` text DEFAULT NULL,
  `expected_outcome` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_training_scenarios`
--

INSERT INTO `inventory_training_scenarios` (`id`, `title`, `description`, `scenario_type`, `difficulty`, `estimated_time`, `points`, `instructions`, `expected_outcome`, `active`, `created_at`) VALUES
(1, 'Stock Replenishment', 'A guest requests extra towels but housekeeping reports low stock. You need to check inventory levels and reorder supplies.', 'stock_management', 'beginner', 10, 15, '1. Check current towel inventory levels\n2. Identify which items are below minimum stock\n3. Create a reorder request\n4. Update inventory records', 'Successfully identify low stock items and create reorder request', 1, '2025-09-29 14:31:09'),
(2, 'Inventory Audit', 'Monthly inventory audit is due. You need to conduct a physical count and reconcile with system records.', 'inventory_audit', 'intermediate', 20, 25, '1. Print current inventory report\n2. Conduct physical count of all items\n3. Identify discrepancies\n4. Update system records\n5. Generate audit report', 'Complete accurate inventory audit with proper documentation', 1, '2025-09-29 14:31:09'),
(3, 'Supplier Management', 'A supplier has delivered damaged goods. You need to handle the return and find an alternative supplier.', 'supplier_management', 'advanced', 15, 20, '1. Document the damaged items\n2. Contact supplier for return\n3. Research alternative suppliers\n4. Update supplier records\n5. Process return transaction', 'Successfully handle supplier issue and maintain inventory flow', 1, '2025-09-29 14:31:09'),
(4, 'Cost Control Analysis', 'Management wants to reduce inventory costs. Analyze current spending and identify cost-saving opportunities.', 'cost_control', 'advanced', 25, 30, '1. Generate cost analysis report\n2. Identify high-cost items\n3. Research alternative suppliers\n4. Calculate potential savings\n5. Present recommendations', 'Provide detailed cost analysis with actionable recommendations', 1, '2025-09-29 14:31:09'),
(5, 'Stock Replenishment', 'A guest requests extra towels but housekeeping reports low stock. You need to check inventory levels and reorder supplies.', 'stock_management', 'beginner', 10, 15, '1. Check current towel inventory levels\n2. Identify which items are below minimum stock\n3. Create a reorder request\n4. Update inventory records', 'Successfully identify low stock items and create reorder request', 1, '2025-10-01 12:54:13'),
(6, 'Inventory Audit', 'Monthly inventory audit is due. You need to conduct a physical count and reconcile with system records.', 'inventory_audit', 'intermediate', 20, 25, '1. Print current inventory report\n2. Conduct physical count of all items\n3. Identify discrepancies\n4. Update system records\n5. Generate audit report', 'Complete accurate inventory audit with proper documentation', 1, '2025-10-01 12:54:13'),
(7, 'Supplier Management', 'A supplier has delivered damaged goods. You need to handle the return and find an alternative supplier.', 'supplier_management', 'advanced', 15, 20, '1. Document the damaged items\n2. Contact supplier for return\n3. Research alternative suppliers\n4. Update supplier records\n5. Process return transaction', 'Successfully handle supplier issue and maintain inventory flow', 1, '2025-10-01 12:54:13'),
(8, 'Cost Control Analysis', 'Management wants to reduce inventory costs. Analyze current spending and identify cost-saving opportunities.', 'cost_control', 'advanced', 25, 30, '1. Generate cost analysis report\n2. Identify high-cost items\n3. Research alternative suppliers\n4. Calculate potential savings\n5. Present recommendations', 'Provide detailed cost analysis with actionable recommendations', 1, '2025-10-01 12:54:13'),
(9, 'Stock Replenishment', 'A guest requests extra towels but housekeeping reports low stock. You need to check inventory levels and reorder supplies.', 'stock_management', 'beginner', 10, 15, '1. Check current towel inventory levels\n2. Identify which items are below minimum stock\n3. Create a reorder request\n4. Update inventory records', 'Successfully identify low stock items and create reorder request', 1, '2025-10-01 15:51:09'),
(10, 'Inventory Audit', 'Monthly inventory audit is due. You need to conduct a physical count and reconcile with system records.', 'inventory_audit', 'intermediate', 20, 25, '1. Print current inventory report\n2. Conduct physical count of all items\n3. Identify discrepancies\n4. Update system records\n5. Generate audit report', 'Complete accurate inventory audit with proper documentation', 1, '2025-10-01 15:51:09'),
(11, 'Supplier Management', 'A supplier has delivered damaged goods. You need to handle the return and find an alternative supplier.', 'supplier_management', 'advanced', 15, 20, '1. Document the damaged items\n2. Contact supplier for return\n3. Research alternative suppliers\n4. Update supplier records\n5. Process return transaction', 'Successfully handle supplier issue and maintain inventory flow', 1, '2025-10-01 15:51:09'),
(12, 'Cost Control Analysis', 'Management wants to reduce inventory costs. Analyze current spending and identify cost-saving opportunities.', 'cost_control', 'advanced', 25, 30, '1. Generate cost analysis report\n2. Identify high-cost items\n3. Research alternative suppliers\n4. Calculate potential savings\n5. Present recommendations', 'Provide detailed cost analysis with actionable recommendations', 1, '2025-10-01 15:51:09');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_transactions`
--

CREATE TABLE `inventory_transactions` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `transaction_type` enum('in','out','adjustment') NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_value` decimal(12,2) NOT NULL DEFAULT 0.00,
  `reason` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `performed_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_transactions`
--

INSERT INTO `inventory_transactions` (`id`, `item_id`, `transaction_type`, `quantity`, `unit_price`, `total_value`, `reason`, `user_id`, `performed_by`, `created_at`) VALUES
(6, 1, 'in', 50, 15.00, 750.00, 'Initial stock', NULL, 1, '2025-10-14 01:06:12'),
(7, 2, 'in', 30, 5.00, 150.00, 'Initial stock', NULL, 1, '2025-10-14 01:06:12'),
(8, 3, 'in', 25, 25.00, 625.00, 'Initial stock', NULL, 1, '2025-10-14 01:06:12'),
(9, 4, 'in', 40, 12.00, 480.00, 'Initial stock', NULL, 1, '2025-10-14 01:06:12'),
(10, 5, 'in', 20, 8.00, 160.00, 'Initial stock', NULL, 1, '2025-10-14 01:06:12'),
(11, 1, 'out', 2, 15.00, 30.00, 'Used in room 102', NULL, 1, '2025-10-14 01:06:12'),
(12, 2, 'out', 1, 5.00, 5.00, 'Used in room 102', NULL, 1, '2025-10-14 01:06:12');

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_points`
--

CREATE TABLE `loyalty_points` (
  `id` int(11) NOT NULL,
  `guest_id` int(11) NOT NULL,
  `action` enum('earn','redeem','adjust') NOT NULL,
  `points` int(11) NOT NULL,
  `reason` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `processed_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_requests`
--

CREATE TABLE `maintenance_requests` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `issue_type` enum('plumbing','electrical','hvac','furniture','appliance','other') NOT NULL,
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `description` text NOT NULL,
  `status` enum('reported','assigned','in_progress','completed','verified') DEFAULT 'reported',
  `reported_by` int(11) NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `estimated_cost` decimal(10,2) DEFAULT NULL,
  `actual_cost` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `maintenance_requests`
--

INSERT INTO `maintenance_requests` (`id`, `room_id`, `issue_type`, `priority`, `description`, `status`, `reported_by`, `assigned_to`, `estimated_cost`, `actual_cost`, `created_at`, `updated_at`) VALUES
(1, 1, 'plumbing', 'medium', 's', 'completed', 1003, NULL, NULL, NULL, '2025-10-09 06:38:44', '2025-10-09 08:47:46'),
(2, 1, 'plumbing', 'high', 's', 'completed', 1003, NULL, NULL, NULL, '2025-10-09 06:39:17', '2025-10-09 08:47:43'),
(3, 4, '', 'medium', 'dasd', '', 1003, NULL, NULL, NULL, '2025-10-09 08:54:36', '2025-10-09 08:54:36'),
(4, 2, 'hvac', 'urgent', 'weq', '', 1003, NULL, 5000.00, NULL, '2025-10-11 04:32:36', '2025-10-11 04:32:36'),
(5, 3, 'hvac', 'urgent', 'sample', '', 1003, NULL, 5000.00, NULL, '2025-10-11 04:34:21', '2025-10-11 04:34:21'),
(6, 3, 'hvac', 'urgent', 'sample', '', 1003, NULL, 5000.00, NULL, '2025-10-11 04:34:23', '2025-10-11 04:34:23'),
(7, 11, 'hvac', 'high', 'wew222', '', 1003, NULL, 10.00, NULL, '2025-10-11 04:34:39', '2025-10-11 04:34:39');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 2, 'eqw', 'qweq', 'info', 0, '2025-10-07 07:18:00'),
(2, 3, 'eqw', 'qweq', 'info', 0, '2025-10-07 07:18:00'),
(3, 6, 'eqw', 'qweq', 'info', 0, '2025-10-07 07:18:00'),
(4, 1005, 'eqw', 'qweq', 'info', 0, '2025-10-07 07:18:00'),
(5, 1006, 'eqw', 'qweq', 'info', 0, '2025-10-07 07:18:00'),
(6, 1, 'New VIP Guest', 'VIP status granted to ge ge', 'info', 0, '2025-10-08 04:00:35'),
(7, 1003, 'New VIP Guest', 'VIP status granted to ge ge', 'info', 0, '2025-10-08 04:00:35'),
(8, 1004, 'New VIP Guest', 'VIP status granted to ge ge', 'info', 0, '2025-10-08 04:00:35'),
(9, 1012, 'New VIP Guest', 'VIP status granted to ge ge', 'info', 0, '2025-10-08 04:00:35'),
(10, 1013, 'New VIP Guest', 'VIP status granted to ge ge', 'info', 0, '2025-10-08 04:00:35'),
(13, 1, 'New VIP Guest', 'VIP status granted to noen none', 'info', 0, '2025-10-08 05:50:41'),
(14, 1003, 'New VIP Guest', 'VIP status granted to noen none', 'info', 0, '2025-10-08 05:50:41'),
(15, 1004, 'New VIP Guest', 'VIP status granted to noen none', 'info', 0, '2025-10-08 05:50:41'),
(16, 1012, 'New VIP Guest', 'VIP status granted to noen none', 'info', 0, '2025-10-08 05:50:41'),
(17, 1013, 'New VIP Guest', 'VIP status granted to noen none', 'info', 0, '2025-10-08 05:50:41'),
(18, 1, 'New VIP Guest', 'VIP status granted to asd asd', 'info', 0, '2025-10-10 02:46:33'),
(19, 1003, 'New VIP Guest', 'VIP status granted to asd asd', 'info', 0, '2025-10-10 02:46:33'),
(20, 1004, 'New VIP Guest', 'VIP status granted to asd asd', 'info', 0, '2025-10-10 02:46:33'),
(21, 1012, 'New VIP Guest', 'VIP status granted to asd asd', 'info', 0, '2025-10-10 02:46:33'),
(22, 1013, 'New VIP Guest', 'VIP status granted to asd asd', 'info', 0, '2025-10-10 02:46:33');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `payment_number` varchar(20) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `payment_method` enum('cash','credit_card','debit_card','bank_transfer','check') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `reference_number` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `processed_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `payment_number`, `bill_id`, `payment_method`, `amount`, `payment_date`, `reference_number`, `notes`, `processed_by`) VALUES
(1, 'PAY-20251007-70176', 3, 'credit_card', 55.00, '2025-10-07 07:06:23', '55', '5', 1003);

-- --------------------------------------------------------

--
-- Table structure for table `pos_activity_log`
--

CREATE TABLE `pos_activity_log` (
  `id` int(11) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `action` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pos_activity_log`
--

INSERT INTO `pos_activity_log` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:24'),
(2, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:24'),
(3, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:24'),
(4, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:24'),
(5, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:24'),
(6, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:24'),
(7, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:24'),
(8, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:24'),
(9, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:24'),
(10, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:24'),
(11, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:25'),
(12, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:25'),
(13, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:25'),
(14, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:25'),
(15, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:25'),
(16, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:25'),
(17, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:25'),
(18, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:25'),
(19, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:25'),
(20, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:25'),
(21, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:30'),
(22, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:30'),
(23, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:30'),
(24, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:30'),
(25, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:31'),
(26, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:31'),
(27, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:31'),
(28, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:31'),
(29, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:31'),
(30, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:31'),
(31, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:39'),
(32, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:39'),
(33, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:39'),
(34, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:39'),
(35, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:39'),
(36, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:39'),
(37, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:39'),
(38, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:39'),
(39, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:39'),
(40, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:39'),
(41, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:40'),
(42, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:40'),
(43, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:40'),
(44, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:40'),
(45, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:40'),
(46, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:40'),
(47, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:40'),
(48, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:40'),
(49, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:40'),
(50, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:40'),
(51, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:45'),
(52, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:45'),
(53, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:45'),
(54, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:45'),
(55, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:45'),
(56, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:45'),
(57, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:45'),
(58, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:45'),
(59, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:45'),
(60, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:40:45'),
(61, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:15'),
(62, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:15'),
(63, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:15'),
(64, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:15'),
(65, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:15'),
(66, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:15'),
(67, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:15'),
(68, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:15'),
(69, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:15'),
(70, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:15'),
(71, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:20'),
(72, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:20'),
(73, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:20'),
(74, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:20'),
(75, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:20'),
(76, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:20'),
(77, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:20'),
(78, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:20'),
(79, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:20'),
(80, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:20'),
(81, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:22'),
(82, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:22'),
(83, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:22'),
(84, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:22'),
(85, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:22'),
(86, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:22'),
(87, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:22'),
(88, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:22'),
(89, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:22'),
(90, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:22'),
(91, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:24'),
(92, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:24'),
(93, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:24'),
(94, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:24'),
(95, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:24'),
(96, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:24'),
(97, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:24'),
(98, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:24'),
(99, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:24'),
(100, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:24'),
(101, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:25'),
(102, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:25'),
(103, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:25'),
(104, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:25'),
(105, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:25'),
(106, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:25'),
(107, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:25'),
(108, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:25'),
(109, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:25'),
(110, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:25'),
(111, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:26'),
(112, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:26'),
(113, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:26'),
(114, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:26'),
(115, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:26'),
(116, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:26'),
(117, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:26'),
(118, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:26'),
(119, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:26'),
(120, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:26'),
(121, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:31'),
(122, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:31'),
(123, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:31'),
(124, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:31'),
(125, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:31'),
(126, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:31'),
(127, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:31'),
(128, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:31'),
(129, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:31'),
(130, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:31'),
(131, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:33'),
(132, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:33'),
(133, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:33'),
(134, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:33'),
(135, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:33'),
(136, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:33'),
(137, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:33'),
(138, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:33'),
(139, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:33'),
(140, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:33'),
(141, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:34'),
(142, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:34'),
(143, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:34'),
(144, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:34'),
(145, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:34'),
(146, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:34'),
(147, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:34'),
(148, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:34'),
(149, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:34'),
(150, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:34'),
(151, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:39'),
(152, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:39'),
(153, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:40'),
(154, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:40'),
(155, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:40'),
(156, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:40'),
(157, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:40'),
(158, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:40'),
(159, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:40'),
(160, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:41:40'),
(161, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:42:10'),
(162, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:42:10'),
(163, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:42:10'),
(164, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:42:10'),
(165, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:42:10'),
(166, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:42:10'),
(167, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:42:10'),
(168, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:42:10'),
(169, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:42:10'),
(170, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:42:10'),
(171, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:06'),
(172, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:06'),
(173, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:06'),
(174, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:06'),
(175, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:06'),
(176, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:06'),
(177, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:06'),
(178, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:06'),
(179, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:06'),
(180, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:06'),
(181, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:07'),
(182, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:07'),
(183, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:07'),
(184, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:07'),
(185, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:07'),
(186, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:07'),
(187, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:07'),
(188, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:07'),
(189, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:07'),
(190, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:07'),
(191, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:08'),
(192, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:08'),
(193, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:08'),
(194, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:08'),
(195, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:08'),
(196, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:08'),
(197, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:08'),
(198, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:08'),
(199, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:08'),
(200, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:08'),
(201, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:09'),
(202, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:09'),
(203, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:09'),
(204, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:09'),
(205, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:09'),
(206, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:09'),
(207, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:09'),
(208, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:09'),
(209, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:09'),
(210, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:09'),
(211, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:11'),
(212, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:11'),
(213, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:11'),
(214, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:11'),
(215, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:11'),
(216, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:11'),
(217, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:11'),
(218, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:11'),
(219, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:11'),
(220, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:11'),
(221, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:12'),
(222, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:12'),
(223, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:12'),
(224, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:12'),
(225, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:12'),
(226, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:12'),
(227, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:12'),
(228, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:12'),
(229, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:12'),
(230, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:12'),
(231, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:17'),
(232, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:17'),
(233, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:17'),
(234, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:18'),
(235, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:18'),
(236, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:18'),
(237, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:18'),
(238, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:18'),
(239, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:18'),
(240, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:18'),
(241, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:48'),
(242, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:48'),
(243, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:48'),
(244, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:48'),
(245, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:48'),
(246, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:48'),
(247, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:48'),
(248, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:48'),
(249, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:48'),
(250, 'student_student1', 'session_expired', 'POS session expired due to inactivity', '::1', NULL, '2025-09-02 15:45:48'),
(251, 'student_student1', 'login', 'Student logged into POS simulation', '::1', NULL, '2025-09-29 15:21:29'),
(252, '3', 'login', 'PMS user logged into POS system', '::1', NULL, '2025-09-30 12:38:36'),
(253, '3', 'logout', 'User logged out from POS system', '::1', NULL, '2025-09-30 12:58:10'),
(254, '3', 'login', 'PMS user logged into POS system', '::1', NULL, '2025-09-30 16:01:30'),
(255, '3', 'logout', 'User logged out from POS system', '::1', NULL, '2025-10-01 00:55:25'),
(256, '3', 'login', 'PMS user logged into POS system', '::1', NULL, '2025-10-01 05:01:50'),
(257, 'student_student1', 'login', 'Student logged into POS simulation', '119.93.199.145', NULL, '2025-10-03 00:36:15'),
(258, '1005', 'login', 'PMS user logged into POS system', '::1', NULL, '2025-10-07 05:13:22');

-- --------------------------------------------------------

--
-- Table structure for table `pos_categories`
--

CREATE TABLE `pos_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `service_type` enum('restaurant','room-service','spa','gift-shop','events','quick-sales') NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pos_categories`
--

INSERT INTO `pos_categories` (`id`, `name`, `description`, `service_type`, `active`, `sort_order`, `created_at`) VALUES
(1, 'Appetizers', 'Starters and small plates', 'restaurant', 1, 0, '2025-09-02 13:29:31'),
(2, 'Main Courses', 'Primary dishes and entrees', 'restaurant', 1, 0, '2025-09-02 13:29:31'),
(3, 'Desserts', 'Sweet treats and pastries', 'restaurant', 1, 0, '2025-09-02 13:29:31'),
(4, 'Beverages', 'Drinks and refreshments', 'restaurant', 1, 0, '2025-09-02 13:29:31'),
(5, 'Spa Treatments', 'Wellness and relaxation services', 'spa', 1, 0, '2025-09-02 13:29:31'),
(6, 'Gift Items', 'Souvenirs and retail products', 'gift-shop', 1, 0, '2025-09-02 13:29:31'),
(7, 'Event Services', 'Conference and banquet services', 'events', 1, 0, '2025-09-02 13:29:31');

-- --------------------------------------------------------

--
-- Table structure for table `pos_discounts`
--

CREATE TABLE `pos_discounts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `discount_type` enum('percentage','fixed-amount') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_amount` decimal(10,2) DEFAULT 0.00,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pos_discounts`
--

INSERT INTO `pos_discounts` (`id`, `name`, `discount_type`, `discount_value`, `min_amount`, `max_discount`, `valid_from`, `valid_until`, `active`, `created_at`) VALUES
(1, 'Senior Citizen', 'percentage', 20.00, 0.00, NULL, NULL, NULL, 1, '2025-09-02 13:29:31'),
(2, 'PWD', 'percentage', 20.00, 0.00, NULL, NULL, NULL, 1, '2025-09-02 13:29:31'),
(3, 'Bulk Order', 'percentage', 10.00, 1000.00, NULL, NULL, NULL, 1, '2025-09-02 13:29:31');

-- --------------------------------------------------------

--
-- Table structure for table `pos_inventory`
--

CREATE TABLE `pos_inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `reorder_level` int(11) DEFAULT 10,
  `unit_cost` decimal(10,2) DEFAULT 0.00,
  `supplier` varchar(100) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pos_menu_items`
--

CREATE TABLE `pos_menu_items` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cost` decimal(10,2) DEFAULT 0.00,
  `image` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pos_menu_items`
--

INSERT INTO `pos_menu_items` (`id`, `name`, `description`, `category`, `price`, `cost`, `image`, `active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Spring Rolls', 'Fresh vegetables wrapped in rice paper with sweet chili sauce', 'appetizers', 180.00, 80.00, NULL, 1, 0, '2025-09-02 13:29:31', '2025-09-02 13:29:31'),
(2, 'Chicken Satay', 'Grilled chicken skewers with peanut sauce', 'appetizers', 220.00, 100.00, NULL, 1, 0, '2025-09-02 13:29:31', '2025-09-02 13:29:31'),
(3, 'Tom Yum Soup', 'Spicy and sour soup with shrimp and mushrooms', 'appetizers', 280.00, 120.00, NULL, 1, 0, '2025-09-02 13:29:31', '2025-09-02 13:29:31'),
(4, 'Beef Tenderloin', 'Grilled beef tenderloin with garlic mashed potatoes', 'main-courses', 850.00, 350.00, NULL, 1, 0, '2025-09-02 13:29:31', '2025-09-02 13:29:31'),
(5, 'Grilled Salmon', 'Fresh salmon with seasonal vegetables', 'main-courses', 720.00, 280.00, NULL, 1, 0, '2025-09-02 13:29:31', '2025-09-02 13:29:31'),
(6, 'Chicken Adobo', 'Traditional Filipino chicken adobo with rice', 'main-courses', 380.00, 150.00, NULL, 1, 0, '2025-09-02 13:29:31', '2025-09-02 13:29:31'),
(7, 'Pasta Carbonara', 'Creamy pasta with bacon and parmesan', 'main-courses', 420.00, 180.00, NULL, 1, 0, '2025-09-02 13:29:31', '2025-09-02 13:29:31'),
(8, 'Chocolate Lava Cake', 'Warm chocolate cake with molten center', 'desserts', 280.00, 120.00, NULL, 1, 0, '2025-09-02 13:29:31', '2025-09-02 13:29:31'),
(9, 'Tiramisu', 'Classic Italian dessert with coffee and mascarpone', 'desserts', 320.00, 140.00, NULL, 1, 0, '2025-09-02 13:29:31', '2025-09-02 13:29:31'),
(10, 'Ice Cream Selection', 'Vanilla, chocolate, and strawberry ice cream', 'desserts', 180.00, 80.00, NULL, 1, 0, '2025-09-02 13:29:31', '2025-09-02 13:29:31'),
(11, 'Fresh Orange Juice', 'Freshly squeezed orange juice', 'beverages', 120.00, 50.00, NULL, 1, 0, '2025-09-02 13:29:31', '2025-09-02 13:29:31'),
(12, 'Iced Tea', 'Refreshing iced tea with lemon', 'beverages', 80.00, 30.00, NULL, 1, 0, '2025-09-02 13:29:31', '2025-09-02 13:29:31'),
(13, 'Coffee', 'Freshly brewed coffee', 'beverages', 100.00, 40.00, NULL, 1, 0, '2025-09-02 13:29:31', '2025-09-02 13:29:31'),
(14, 'Mineral Water', '500ml bottled water', 'beverages', 60.00, 20.00, NULL, 1, 0, '2025-09-02 13:29:31', '2025-09-02 13:29:31');

-- --------------------------------------------------------

--
-- Table structure for table `pos_orders`
--

CREATE TABLE `pos_orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(20) NOT NULL,
  `table_id` int(11) DEFAULT NULL,
  `guest_id` int(11) DEFAULT NULL,
  `room_number` varchar(10) DEFAULT NULL,
  `guest_count` int(11) DEFAULT 1,
  `items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`items`)),
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','preparing','ready','served','cancelled') NOT NULL DEFAULT 'pending',
  `special_requests` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `served_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `pos_orders`
--
DELIMITER $$
CREATE TRIGGER `generate_order_number` BEFORE INSERT ON `pos_orders` FOR EACH ROW BEGIN
    IF NEW.order_number IS NULL OR NEW.order_number = '' THEN
        SET NEW.order_number = CONCAT('ORD', DATE_FORMAT(NOW(), '%Y%m%d'), LPAD((SELECT COUNT(*) + 1 FROM pos_orders WHERE DATE(created_at) = CURDATE()), 4, '0'));
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `pos_payments`
--

CREATE TABLE `pos_payments` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','credit-card','debit-card','mobile-payment','room-charge') NOT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `processed_by` int(11) NOT NULL,
  `processed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pos_tables`
--

CREATE TABLE `pos_tables` (
  `id` int(11) NOT NULL,
  `table_number` varchar(10) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 4,
  `location` varchar(50) DEFAULT 'main-floor',
  `status` enum('available','occupied','reserved','maintenance') NOT NULL DEFAULT 'available',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pos_tables`
--

INSERT INTO `pos_tables` (`id`, `table_number`, `capacity`, `location`, `status`, `active`, `created_at`, `updated_at`) VALUES
(1, '1', 4, 'main-floor', 'available', 1, '2025-09-02 13:29:31', '2025-09-02 13:29:31'),
(2, '2', 4, 'main-floor', 'available', 1, '2025-09-02 13:29:31', '2025-09-02 13:29:31'),
(3, '3', 6, 'main-floor', 'available', 1, '2025-09-02 13:29:31', '2025-09-02 13:29:31'),
(4, '4', 2, 'window', 'available', 1, '2025-09-02 13:29:31', '2025-09-02 13:29:31'),
(5, '5', 8, 'private-room', 'available', 1, '2025-09-02 13:29:31', '2025-09-02 13:29:31'),
(6, '6', 4, 'garden-view', 'available', 1, '2025-09-02 13:29:31', '2025-09-02 13:29:31'),
(7, '7', 6, 'main-floor', 'available', 1, '2025-09-02 13:29:31', '2025-09-02 13:29:31'),
(8, '8', 4, 'window', 'available', 1, '2025-09-02 13:29:31', '2025-09-02 13:29:31');

-- --------------------------------------------------------

--
-- Table structure for table `pos_tax_rates`
--

CREATE TABLE `pos_tax_rates` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `rate` decimal(5,2) NOT NULL,
  `description` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pos_tax_rates`
--

INSERT INTO `pos_tax_rates` (`id`, `name`, `rate`, `description`, `active`, `created_at`) VALUES
(1, 'VAT', 12.00, 'Value Added Tax', 1, '2025-09-02 13:29:31');

-- --------------------------------------------------------

--
-- Table structure for table `pos_transactions`
--

CREATE TABLE `pos_transactions` (
  `id` int(11) NOT NULL,
  `transaction_number` varchar(20) NOT NULL,
  `service_type` enum('restaurant','room-service','spa','gift-shop','events','quick-sales') NOT NULL,
  `guest_id` int(11) DEFAULT NULL,
  `room_number` varchar(10) DEFAULT NULL,
  `table_id` int(11) DEFAULT NULL,
  `items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`items`)),
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` enum('cash','credit-card','debit-card','mobile-payment','room-charge') DEFAULT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `status` enum('pending','preparing','ready','completed','cancelled') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `pos_transactions`
--
DELIMITER $$
CREATE TRIGGER `generate_transaction_number` BEFORE INSERT ON `pos_transactions` FOR EACH ROW BEGIN
    IF NEW.transaction_number IS NULL OR NEW.transaction_number = '' THEN
        SET NEW.transaction_number = CONCAT('TXN', DATE_FORMAT(NOW(), '%Y%m%d'), LPAD((SELECT COUNT(*) + 1 FROM pos_transactions WHERE DATE(created_at) = CURDATE()), 4, '0'));
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `problem_scenarios`
--

CREATE TABLE `problem_scenarios` (
  `id` int(11) NOT NULL,
  `scenario_id` varchar(50) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `resources` text DEFAULT NULL,
  `severity` enum('low','medium','high','critical') NOT NULL,
  `difficulty` enum('beginner','intermediate','advanced') NOT NULL,
  `time_limit` int(11) NOT NULL DEFAULT 5,
  `points` int(11) NOT NULL DEFAULT 20,
  `status` enum('active','inactive','draft') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `problem_scenarios`
--

INSERT INTO `problem_scenarios` (`id`, `scenario_id`, `title`, `description`, `resources`, `severity`, `difficulty`, `time_limit`, `points`, `status`, `created_at`, `updated_at`) VALUES
(1, 'PS001', 'Overbooked Hotel Crisis', 'The hotel is overbooked by 15 rooms due to a system error. All rooms are occupied and 15 guests with confirmed reservations are arriving in 2 hours. The hotel is at 100% capacity and there are no available rooms.', 'Hotel management system, guest contact information, local hotel partnerships, transportation options, compensation budget, front desk staff, manager on duty', 'critical', 'advanced', 30, 200, 'active', '2025-10-10 01:51:51', '2025-10-10 01:51:51'),
(2, 'PS002', 'Power Outage Emergency', 'A major power outage has affected the entire hotel. The backup generator is not working, and power is expected to be restored in 4-6 hours. Guests are complaining about no air conditioning, elevators not working, and food spoiling.', 'Emergency contact list, backup generator manual, flashlight supply, ice supply, guest room keys, maintenance team, local power company contacts', 'high', 'intermediate', 25, 150, 'active', '2025-10-10 01:51:51', '2025-10-10 01:51:51'),
(3, 'PS003', 'Guest Medical Emergency', 'A guest has collapsed in the lobby and appears to be having a heart attack. The hotel is busy with a conference, and there are many people around. Emergency services have been called but will take 10-15 minutes to arrive.', 'First aid kit, AED device, emergency contact numbers, guest registration information, security team, medical emergency protocols', 'critical', 'advanced', 20, 180, 'active', '2025-10-10 01:51:51', '2025-10-10 01:51:51'),
(4, 'PS004', 'Staff Shortage Crisis', 'Three key staff members called in sick on the busiest day of the year. The hotel is at 95% occupancy with a large wedding party and corporate event. Essential services are understaffed and guest complaints are increasing.', 'Staff roster, on-call list, temporary agency contacts, cross-training records, manager contact list, overtime budget approval', 'high', 'intermediate', 25, 160, 'active', '2025-10-10 01:51:51', '2025-10-10 01:51:51'),
(5, 'PS005', 'Guest Room Security Breach', 'A guest reports that their room was entered while they were away, and valuable items are missing. The guest is demanding immediate action and threatening to call the police. Security footage shows an unknown person entering the room.', 'Security footage, guest registration, room access logs, security team, local police contacts, insurance information, guest compensation options', 'high', 'advanced', 30, 170, 'active', '2025-10-10 01:51:51', '2025-10-10 01:51:51');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` int(11) NOT NULL,
  `po_number` varchar(20) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `status` enum('draft','pending','approved','ordered','received','cancelled') DEFAULT 'draft',
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `requested_by` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `order_date` date DEFAULT NULL,
  `expected_delivery` date DEFAULT NULL,
  `received_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

CREATE TABLE `purchase_order_items` (
  `id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity_ordered` int(11) NOT NULL,
  `quantity_received` int(11) DEFAULT 0,
  `unit_cost` decimal(10,2) NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quality_checks`
--

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

--
-- Table structure for table `question_options`
--

CREATE TABLE `question_options` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_text` text NOT NULL,
  `option_value` varchar(10) NOT NULL,
  `option_order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `question_options`
--

INSERT INTO `question_options` (`id`, `question_id`, `option_text`, `option_value`, `option_order`) VALUES
(1, 18, 'Offer room upgrade options', 'A', 1),
(2, 18, 'Greet the guest and verify their reservation/ID', 'B', 2),
(3, 18, 'Issue the room key immediately', 'C', 3),
(4, 18, 'Collect payment receipt first', 'D', 4),
(5, 19, 'To determine upgrade eligibility only', 'A', 1),
(6, 19, 'To get their loyalty number', 'B', 2),
(7, 19, 'To prevent fraud and match the reservation to the right person', 'C', 3),
(8, 19, 'It is optional if they paid online', 'D', 4),
(9, 20, 'Apologize, acknowledge the inconvenience, and begin checking alternative solutions', 'A', 1),
(10, 20, 'Explain that the system made a mistake and ask them to return later', 'B', 2),
(11, 20, 'Offer a complimentary drink but take no action', 'C', 3),
(12, 20, 'Ask the guest to call reservations', 'D', 4),
(13, 21, 'Ask the guest to wait until a room frees up', 'A', 1),
(14, 21, 'Walk the guest to a partnered nearby hotel at equal or higher category', 'B', 2),
(15, 21, 'Offer a late check-in the next day', 'C', 3),
(16, 21, 'Cancel the reservation and refund only', 'D', 4),
(17, 22, 'A verbal apology only', 'A', 1),
(18, 22, 'A complimentary drink voucher', 'B', 2),
(19, 22, 'Free parking', 'C', 3),
(20, 22, 'Transportation to partner hotel + rate match/upgrade + future stay discount', 'D', 4),
(21, 23, 'Only the guest name', 'A', 1),
(22, 23, 'Only the compensation offered', 'B', 2),
(23, 23, 'Guest details, action taken, partner hotel info, costs, and staff initials', 'C', 3),
(24, 23, 'Nothing if the guest accepted the solution', 'D', 4),
(25, 24, 'Offer room upgrade options', 'A', 1),
(26, 24, 'Greet the guest and verify their reservation/ID', 'B', 2),
(27, 24, 'Issue the room key immediately', 'C', 3),
(28, 24, 'Collect payment receipt first', 'D', 4),
(29, 25, 'To determine upgrade eligibility only', 'A', 1),
(30, 25, 'To get their loyalty number', 'B', 2),
(31, 25, 'To prevent fraud and match the reservation to the right person', 'C', 3),
(32, 25, 'It is optional if they paid online', 'D', 4),
(33, 26, 'Apologize, acknowledge the inconvenience, and begin checking alternative solutions', 'A', 1),
(34, 26, 'Explain that the system made a mistake and ask them to return later', 'B', 2),
(35, 26, 'Offer a complimentary drink but take no action', 'C', 3),
(36, 26, 'Ask the guest to call reservations', 'D', 4),
(37, 27, 'Ask the guest to wait until a room frees up', 'A', 1),
(38, 27, 'Walk the guest to a partnered nearby hotel at equal or higher category', 'B', 2),
(39, 27, 'Offer a late check-in the next day', 'C', 3),
(40, 27, 'Cancel the reservation and refund only', 'D', 4),
(41, 28, 'A verbal apology only', 'A', 1),
(42, 28, 'A complimentary drink voucher', 'B', 2),
(43, 28, 'Free parking', 'C', 3),
(44, 28, 'Transportation to partner hotel + rate match/upgrade + future stay discount', 'D', 4),
(45, 29, 'Only the guest name', 'A', 1),
(46, 29, 'Only the compensation offered', 'B', 2),
(47, 29, 'Guest details, action taken, partner hotel info, costs, and staff initials', 'C', 3),
(48, 29, 'Nothing if the guest accepted the solution', 'D', 4),
(49, 30, 'Offer room upgrade options', 'A', 1),
(50, 30, 'Greet the guest and verify their reservation/ID', 'B', 2),
(51, 30, 'Issue the room key immediately', 'C', 3),
(52, 30, 'Collect payment receipt first', 'D', 4),
(53, 31, 'To determine upgrade eligibility only', 'A', 1),
(54, 31, 'To get their loyalty number', 'B', 2),
(55, 31, 'To prevent fraud and match the reservation to the right person', 'C', 3),
(56, 31, 'It is optional if they paid online', 'D', 4),
(57, 32, 'Apologize, acknowledge the inconvenience, and begin checking alternative solutions', 'A', 1),
(58, 32, 'Explain that the system made a mistake and ask them to return later', 'B', 2),
(59, 32, 'Offer a complimentary drink but take no action', 'C', 3),
(60, 32, 'Ask the guest to call reservations', 'D', 4),
(61, 33, 'Ask the guest to wait until a room frees up', 'A', 1),
(62, 33, 'Walk the guest to a partnered nearby hotel at equal or higher category', 'B', 2),
(63, 33, 'Offer a late check-in the next day', 'C', 3),
(64, 33, 'Cancel the reservation and refund only', 'D', 4),
(65, 34, 'A verbal apology only', 'A', 1),
(66, 34, 'A complimentary drink voucher', 'B', 2),
(67, 34, 'Free parking', 'C', 3),
(68, 34, 'Transportation to partner hotel + rate match/upgrade + future stay discount', 'D', 4),
(69, 35, 'Only the guest name', 'A', 1),
(70, 35, 'Only the compensation offered', 'B', 2),
(71, 35, 'Guest details, action taken, partner hotel info, costs, and staff initials', 'C', 3),
(72, 35, 'Nothing if the guest accepted the solution', 'D', 4),
(73, 36, 'Offer room upgrade options', 'A', 1),
(74, 36, 'Greet the guest and verify their reservation/ID', 'B', 2),
(75, 36, 'Issue the room key immediately', 'C', 3),
(76, 36, 'Collect payment receipt first', 'D', 4),
(77, 37, 'To determine upgrade eligibility only', 'A', 1),
(78, 37, 'To get their loyalty number', 'B', 2),
(79, 37, 'To prevent fraud and match the reservation to the right person', 'C', 3),
(80, 37, 'It is optional if they paid online', 'D', 4),
(81, 38, 'Apologize, acknowledge the inconvenience, and begin checking alternative solutions', 'A', 1),
(82, 38, 'Explain that the system made a mistake and ask them to return later', 'B', 2),
(83, 38, 'Offer a complimentary drink but take no action', 'C', 3),
(84, 38, 'Ask the guest to call reservations', 'D', 4),
(85, 39, 'Ask the guest to wait until a room frees up', 'A', 1),
(86, 39, 'Walk the guest to a partnered nearby hotel at equal or higher category', 'B', 2),
(87, 39, 'Offer a late check-in the next day', 'C', 3),
(88, 39, 'Cancel the reservation and refund only', 'D', 4),
(89, 40, 'A verbal apology only', 'A', 1),
(90, 40, 'A complimentary drink voucher', 'B', 2),
(91, 40, 'Free parking', 'C', 3),
(92, 40, 'Transportation to partner hotel + rate match/upgrade + future stay discount', 'D', 4),
(93, 41, 'Only the guest name', 'A', 1),
(94, 41, 'Only the compensation offered', 'B', 2),
(95, 41, 'Guest details, action taken, partner hotel info, costs, and staff initials', 'C', 3),
(96, 41, 'Nothing if the guest accepted the solution', 'D', 4),
(97, 1, 'Listen actively and acknowledge their concern', 'A', 1),
(98, 1, 'Immediately offer compensation', 'B', 2),
(99, 1, 'Call the manager right away', 'C', 3),
(100, 1, 'Ask them to calm down', 'D', 4),
(101, 2, 'Promise immediate compensation', 'A', 1),
(102, 2, 'Investigate first, then offer appropriate solution', 'B', 2),
(103, 2, 'Refuse any compensation', 'C', 3),
(104, 2, 'Let the manager decide', 'D', 4),
(105, 6, 'Take detailed notes and coordinate with kitchen staff', 'A', 1),
(106, 6, 'Give them a list of safe restaurants', 'B', 2),
(107, 6, 'Ask them to bring their own food', 'C', 3),
(108, 6, 'Refer them to room service only', 'D', 4),
(109, 7, 'Just the main allergies', 'A', 1),
(110, 7, 'Complete list of allergies, severity, and cross-contamination concerns', 'B', 2),
(111, 7, 'Only what they tell you', 'C', 3),
(112, 7, 'Basic dietary preferences', 'D', 4),
(113, 8, 'Send a quick email', 'A', 1),
(114, 8, 'Write it on a sticky note', 'B', 2),
(115, 8, 'Tell them verbally only', 'C', 3),
(116, 8, 'Provide written documentation and discuss in person', 'D', 4),
(117, 9, 'Check once and assume it is handled', 'A', 1),
(118, 9, 'Follow up daily to ensure compliance', 'B', 2),
(119, 9, 'Only check if the guest complains', 'C', 3),
(120, 9, 'Monitor the first meal and then check periodically', 'D', 4),
(121, 10, 'Call emergency services immediately', 'A', 1),
(122, 10, 'Move the person to a private area', 'B', 2),
(123, 10, 'Ask other guests to help', 'C', 3),
(124, 10, 'Call the hotel manager first', 'D', 4),
(125, 11, 'Let them stay and watch', 'A', 1),
(126, 11, 'Ask them to step back and give space', 'B', 2),
(127, 11, 'Ask them to leave the lobby', 'C', 3),
(128, 11, 'Ignore them and focus on the emergency', 'D', 4),
(129, 12, 'Try to move the person', 'A', 1),
(130, 12, 'Give them water or food', 'B', 2),
(131, 12, 'Stay with them and monitor their condition', 'C', 3),
(132, 12, 'Search for their identification', 'D', 4),
(133, 13, 'Wait for emergency services to handle everything', 'A', 1),
(134, 13, 'Document the incident and notify management', 'B', 2),
(135, 13, 'Ask other guests to leave immediately', 'C', 3),
(136, 13, 'Call the guest\'s family members', 'D', 4),
(137, 5, 'Knock politely and explain the complaint', 'A', 1),
(138, 5, 'Barging in and demand they stop', 'B', 2),
(139, 5, 'Call security immediately', 'C', 3),
(140, 5, 'Ignore the complaint', 'D', 4),
(141, 14, 'Provide emergency toiletries and clothing assistance', 'A', 1),
(142, 14, 'Wait for the airline to resolve it', 'B', 2),
(143, 14, 'Ask them to buy new clothes', 'C', 3),
(144, 14, 'Refer them to a local store', 'D', 4),
(145, 15, 'Give them the phone number only', 'A', 1),
(146, 15, 'Provide contact info and help them make the call', 'B', 2),
(147, 15, 'Call the airline for them', 'C', 3),
(148, 15, 'Tell them to use their phone', 'D', 4),
(149, 16, 'Nothing, it\'s the airline\'s problem', 'A', 1),
(150, 16, 'Only if they ask', 'B', 2),
(151, 16, 'Regular check-ins and updates on their luggage status', 'C', 3),
(152, 16, 'Compensation for their inconvenience', 'D', 4),
(153, 17, 'Wait for the airline to contact them', 'A', 1),
(154, 17, 'Provide a list of local stores and services', 'B', 2),
(155, 17, 'Offer to arrange emergency shopping assistance', 'C', 3),
(156, 17, 'Ask them to handle it themselves', 'D', 4),
(157, 42, 'Send an email the next day', 'A', 1),
(158, 42, 'Call them in their room', 'B', 2),
(159, 42, 'Check with them personally before they leave', 'C', 3),
(160, 42, 'Send a written apology', 'D', 4),
(161, 43, 'Accept their refusal', 'A', 1),
(162, 43, 'Explain hotel policies and involve management', 'B', 2),
(163, 43, 'Threaten to evict them', 'C', 3),
(164, 43, 'Move the complaining guest instead', 'D', 4),
(165, 44, 'Assume the problem is solved', 'A', 1),
(166, 44, 'Wait for them to complain again', 'B', 2),
(167, 44, 'Send a written apology', 'C', 3),
(168, 44, 'Check with them to ensure the issue is resolved', 'D', 4),
(169, 45, 'Immediately contact local hotels to find alternative accommodations', 'A', 1),
(170, 45, 'Wait for the guests to arrive and deal with it then', 'B', 2),
(171, 45, 'Call the general manager and let them handle it', 'C', 3),
(172, 45, 'Start offering compensation to arriving guests', 'D', 4),
(173, 46, 'Relocate guests based on room rate paid', 'A', 1),
(174, 46, 'Prioritize VIP guests, then by check-in time and special needs', 'B', 2),
(175, 46, 'Relocate guests randomly to be fair', 'C', 3),
(176, 46, 'Relocate guests who complain the most', 'D', 4),
(177, 47, 'A simple apology and free breakfast', 'A', 1),
(178, 47, '50% discount on their next stay', 'B', 2),
(179, 47, 'Free night at the alternative hotel plus transportation and future discount', 'C', 3),
(180, 47, 'Full refund of their current reservation', 'D', 4),
(181, 48, 'Call them immediately with a sincere apology and solution', 'A', 1),
(182, 48, 'Wait until they arrive to explain the situation', 'B', 2),
(183, 48, 'Send an email explaining the overbooking', 'C', 3),
(184, 48, 'Let the front desk staff handle it when they check in', 'D', 4),
(185, 49, 'Ensure guest safety and provide emergency lighting', 'A', 1),
(186, 49, 'Contact the power company immediately', 'B', 2),
(187, 49, 'Start the backup generator', 'C', 3),
(188, 49, 'Inform all guests about the situation', 'D', 4),
(189, 50, 'Keep all food in refrigerators and hope power returns soon', 'A', 1),
(190, 50, 'Move perishable items to ice and monitor temperatures closely', 'B', 2),
(191, 50, 'Serve all food immediately to avoid waste', 'C', 3),
(192, 50, 'Close the restaurant and wait for power restoration', 'D', 4),
(193, 51, 'Wait for emergency services to handle it', 'A', 1),
(194, 51, 'Try to manually open the elevator doors', 'B', 2),
(195, 51, 'Contact emergency services and provide reassurance to trapped guests', 'C', 3),
(196, 51, 'Use the stairs to check on them', 'D', 4),
(197, 52, 'Call emergency services immediately and check for responsiveness', 'A', 1),
(198, 52, 'Move the guest to a more comfortable position', 'B', 2),
(199, 52, 'Ask other guests to help', 'C', 3),
(200, 52, 'Call the hotel manager first', 'D', 4),
(201, 53, 'Let everyone stay and watch', 'A', 1),
(202, 53, 'Ask people to step back and give space for medical attention', 'B', 2),
(203, 53, 'Ask everyone to leave the lobby', 'C', 3),
(204, 53, 'Use the crowd to help with the situation', 'D', 4),
(205, 54, 'Only their name and room number', 'A', 1),
(206, 54, 'Their medical history and medications', 'B', 2),
(207, 54, 'Basic identification, emergency contacts, and any visible medical information', 'C', 3),
(208, 54, 'Their credit card information', 'D', 4),
(209, 55, 'Wait for the guest to recover before contacting family', 'A', 1),
(210, 55, 'Call family immediately with all medical details', 'B', 2),
(211, 55, 'Let emergency services handle family communication', 'C', 3),
(212, 55, 'Contact family with basic information and hospital location', 'D', 4),
(213, 56, 'Assess critical positions and call in backup staff', 'A', 1),
(214, 56, 'Close non-essential services immediately', 'B', 2),
(215, 56, 'Ask remaining staff to work double shifts', 'C', 3),
(216, 56, 'Inform guests about reduced services', 'D', 4),
(217, 57, 'Maintain all services equally', 'A', 1),
(218, 57, 'Focus on guest safety, check-in/out, and essential services first', 'B', 2),
(219, 57, 'Keep the restaurant open as priority', 'C', 3),
(220, 57, 'Maintain housekeeping as the top priority', 'D', 4),
(221, 58, 'Cancel both events to reduce workload', 'A', 1),
(222, 58, 'Ask the groups to help with basic tasks', 'B', 2),
(223, 58, 'Communicate the situation and adjust service levels accordingly', 'C', 3),
(224, 58, 'Pretend everything is normal', 'D', 4),
(225, 59, 'Secure the room, preserve evidence, and contact security', 'A', 1),
(226, 59, 'Immediately offer compensation to the guest', 'B', 2),
(227, 59, 'Call the police right away', 'C', 3),
(228, 59, 'Move the guest to a different room', 'D', 4),
(229, 60, 'Try to convince them not to call police', 'A', 1),
(230, 60, 'Acknowledge their right to call police while cooperating fully', 'B', 2),
(231, 60, 'Offer immediate compensation to prevent police involvement', 'C', 3),
(232, 60, 'Let them call police and handle it themselves', 'D', 4),
(233, 61, 'Only the guest\'s statement', 'A', 1),
(234, 61, 'Security footage and room access logs only', 'B', 2),
(235, 61, 'Guest statement, security footage, access logs, and witness accounts', 'C', 3),
(236, 61, 'Just the missing items list', 'D', 4),
(237, 62, 'Inform all guests immediately about the security breach', 'A', 1),
(238, 62, 'Keep it completely confidential', 'B', 2),
(239, 62, 'Only tell guests who ask directly', 'C', 3),
(240, 62, 'Provide general security updates without specific details', 'D', 4);

-- --------------------------------------------------------

--
-- Table structure for table `reorder_rules`
--

CREATE TABLE `reorder_rules` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `reorder_point` int(11) NOT NULL,
  `reorder_quantity` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `lead_time_days` int(11) DEFAULT 7,
  `auto_generate_po` tinyint(1) DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `reservation_number` varchar(20) NOT NULL,
  `guest_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `check_in_date` date NOT NULL,
  `check_out_date` date NOT NULL,
  `adults` int(11) NOT NULL DEFAULT 1,
  `children` int(11) DEFAULT 0,
  `total_amount` decimal(10,2) NOT NULL,
  `special_requests` text DEFAULT NULL,
  `booking_source` enum('walk_in','online','phone','travel_agent') DEFAULT 'walk_in',
  `status` enum('confirmed','checked_in','checked_out','cancelled','no_show','walked') DEFAULT 'confirmed',
  `checked_in_at` timestamp NULL DEFAULT NULL,
  `checked_in_by` int(11) DEFAULT NULL,
  `checked_out_at` timestamp NULL DEFAULT NULL,
  `checked_out_by` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `reservation_number`, `guest_id`, `room_id`, `check_in_date`, `check_out_date`, `adults`, `children`, `total_amount`, `special_requests`, `booking_source`, `status`, `checked_in_at`, `checked_in_by`, `checked_out_at`, `checked_out_by`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'RES20250101001', 1, 2, '2025-01-15', '2025-10-13', 2, 0, 540.00, 'Late arrival after 10 PM, Non-smoking room', 'online', 'checked_out', '2025-01-15 14:30:00', 2, '2025-10-13 03:00:00', NULL, 1, '2025-08-27 10:02:44', '2025-10-13 05:10:19'),
(2, 'RES20250101002', 2, 6, '2025-01-15', '2025-10-13', 2, 1, 2250.00, 'Anniversary celebration, champagne on arrival, ocean view', 'phone', 'checked_out', '2025-01-15 07:00:00', 3, '2025-10-13 03:00:00', NULL, 1, '2025-08-27 10:02:44', '2025-10-13 05:10:41'),
(3, 'RES20250102001', 3, 8, '2025-01-16', '2025-01-19', 1, 0, 1350.00, 'Business trip, quiet room preferred, high-speed internet', 'walk_in', 'checked_in', '2025-01-16 06:00:00', 2, NULL, NULL, 4, '2025-08-27 10:02:44', '2025-10-02 03:37:17'),
(4, 'RES20250102002', 4, 9, '2025-01-16', '2025-01-22', 2, 0, 5100.00, 'Honeymoon celebration, beach view, spa services', 'online', 'checked_in', '2025-01-16 08:00:00', 3, NULL, NULL, 1, '2025-08-27 10:02:44', '2025-10-02 03:37:17'),
(5, 'RES20250103001', 5, 4, '2025-01-17', '2025-01-21', 2, 0, 1120.00, 'City view, fitness center access, late check-out', 'travel_agent', 'checked_in', '2025-01-17 04:00:00', 2, NULL, NULL, 1, '2025-08-27 10:02:44', '2025-10-02 03:37:17'),
(6, 'RES20250103002', 6, 1, '2025-01-18', '2025-01-20', 1, 0, 360.00, 'Pet-friendly room, ground floor preferred', 'online', 'checked_in', '2025-10-12 16:54:00', NULL, NULL, NULL, 4, '2025-08-27 10:02:44', '2025-10-13 05:10:19'),
(7, 'RES20250104001', 7, 10, '2025-01-19', '2025-01-25', 4, 2, 5100.00, 'Executive meeting room access, premium services', 'phone', 'checked_in', '2025-10-12 21:23:00', NULL, NULL, NULL, 1, '2025-08-27 10:02:44', '2025-10-13 05:10:19'),
(8, 'RES20250104002', 8, 3, '2025-01-20', '2025-01-23', 2, 0, 540.00, 'Historic district view, local restaurant recommendations', 'walk_in', 'checked_in', '2025-10-12 22:19:00', NULL, NULL, NULL, 4, '2025-08-27 10:02:44', '2025-10-13 05:10:41'),
(9, 'RES20250105001', 9, 7, '2025-01-21', '2025-01-24', 2, 0, 1350.00, 'Strip view, casino access, VIP services', 'online', 'checked_in', '2025-10-12 16:09:00', NULL, NULL, NULL, 1, '2025-08-27 10:02:44', '2025-10-13 05:10:41'),
(10, 'RES20250105002', 10, 5, '2025-01-22', '2025-01-25', 3, 1, 840.00, 'Historic view, walking distance to attractions', 'travel_agent', 'confirmed', NULL, NULL, NULL, NULL, 1, '2025-08-27 10:02:44', '2025-10-02 03:37:17'),
(12, '', 1, 1, '2025-10-14', '2025-10-17', 2, 0, 5767.00, NULL, 'walk_in', 'confirmed', NULL, NULL, NULL, NULL, 1, '2025-10-13 01:29:49', '2025-10-13 01:29:49'),
(22, 'RES202510134774', 14, 5, '2025-10-13', '2025-10-14', 1, 0, 275.00, 'w', 'walk_in', 'checked_in', '2025-10-13 03:43:11', 1003, NULL, NULL, 1003, '2025-10-13 03:18:05', '2025-10-13 03:43:11'),
(24, 'RES20251013001', 8, 3, '2025-10-14', '2025-10-16', 2, 0, 150.00, NULL, 'online', 'confirmed', NULL, NULL, NULL, NULL, 1, '2025-10-13 04:10:41', '2025-10-13 05:10:41'),
(25, 'RES20251013002', 9, 7, '2025-10-15', '2025-10-17', 2, 0, 200.00, NULL, 'online', 'confirmed', NULL, NULL, NULL, NULL, 1, '2025-10-13 03:10:41', '2025-10-13 05:10:41'),
(26, 'RES20251013003', 10, 5, '2025-10-16', '2025-10-18', 2, 0, 250.00, NULL, 'online', 'confirmed', NULL, NULL, NULL, NULL, 1, '2025-10-13 02:10:41', '2025-10-13 05:10:41'),
(27, 'RES20251013004', 8, 3, '2025-10-17', '2025-10-19', 2, 0, 300.00, NULL, 'online', 'confirmed', NULL, NULL, NULL, NULL, 1, '2025-10-13 01:10:41', '2025-10-13 05:10:41');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `room_number` varchar(10) NOT NULL,
  `room_type` enum('standard','deluxe','suite','presidential') NOT NULL,
  `floor` int(11) NOT NULL,
  `capacity` int(11) NOT NULL,
  `rate` decimal(10,2) NOT NULL,
  `status` enum('available','occupied','reserved','maintenance','out_of_service') DEFAULT 'available',
  `housekeeping_status` enum('clean','dirty','cleaning','maintenance') DEFAULT 'clean',
  `amenities` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `room_number`, `room_type`, `floor`, `capacity`, `rate`, `status`, `housekeeping_status`, `amenities`, `created_at`, `updated_at`) VALUES
(1, '101', 'standard', 1, 2, 180.00, 'available', 'maintenance', 'WiFi, Flat-screen TV, Air Conditioning, Mini Fridge, Coffee Maker, Safe', '2025-08-27 10:02:44', '2025-10-09 06:41:09'),
(2, '102', 'standard', 1, 2, 180.00, 'occupied', 'dirty', 'WiFi, Flat-screen TV, Air Conditioning, Mini Fridge, Coffee Maker, Safe', '2025-08-27 10:02:44', '2025-10-02 03:37:17'),
(3, '103', 'standard', 1, 2, 180.00, 'available', 'clean', 'WiFi, Flat-screen TV, Air Conditioning, Mini Fridge, Coffee Maker, Safe', '2025-08-27 10:02:44', '2025-10-02 03:37:17'),
(4, '201', 'deluxe', 2, 3, 280.00, 'occupied', 'dirty', 'WiFi, 55\" Smart TV, Air Conditioning, Mini Bar, Coffee Maker, Safe, Balcony, City View', '2025-08-27 10:02:44', '2025-10-02 03:37:17'),
(5, '202', 'deluxe', 2, 3, 280.00, 'occupied', 'clean', 'WiFi, 55\" Smart TV, Air Conditioning, Mini Bar, Coffee Maker, Safe, Balcony, City View', '2025-08-27 10:02:44', '2025-10-13 03:43:11'),
(6, '203', 'suite', 2, 4, 450.00, 'occupied', 'dirty', 'WiFi, 65\" Smart TV, Air Conditioning, Full Bar, Espresso Machine, Safe, Private Balcony, Ocean View, Separate Living Area', '2025-08-27 10:02:44', '2025-10-02 03:37:17'),
(7, '301', 'suite', 3, 4, 450.00, 'available', 'clean', 'WiFi, 65\" Smart TV, Air Conditioning, Full Bar, Espresso Machine, Safe, Private Balcony, Ocean View, Separate Living Area', '2025-08-27 10:02:44', '2025-10-02 03:37:17'),
(8, '302', 'suite', 3, 4, 450.00, 'occupied', 'dirty', 'WiFi, 65\" Smart TV, Air Conditioning, Full Bar, Espresso Machine, Safe, Private Balcony, Ocean View, Separate Living Area', '2025-08-27 10:02:44', '2025-10-02 03:37:17'),
(9, '401', 'presidential', 4, 6, 850.00, 'available', 'clean', 'WiFi, 75\" Smart TV, Air Conditioning, Premium Bar, Espresso Machine, Safe, Private Terrace, Panoramic View, Separate Living & Dining Areas, Butler Service', '2025-08-27 10:02:44', '2025-10-02 03:37:17'),
(10, '402', 'presidential', 4, 6, 850.00, 'available', 'clean', 'WiFi, 75\" Smart TV, Air Conditioning, Premium Bar, Espresso Machine, Safe, Private Terrace, Panoramic View, Separate Living & Dining Areas, Butler Service', '2025-08-27 10:02:44', '2025-10-02 03:37:17'),
(11, '512', 'standard', 5, 2, 500.00, 'available', 'clean', 'wew', '2025-10-08 02:27:04', '2025-10-08 02:27:04');

-- --------------------------------------------------------

--
-- Table structure for table `room_inventory`
--

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

--
-- Dumping data for table `room_inventory`
--

INSERT INTO `room_inventory` (`id`, `room_id`, `item_id`, `quantity_allocated`, `quantity_current`, `par_level`, `last_restocked`, `last_audited`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 4, 4, 4, NULL, NULL, NULL, '2025-10-14 01:06:12', '2025-10-14 01:06:12'),
(2, 1, 2, 2, 2, 2, NULL, NULL, NULL, '2025-10-14 01:06:12', '2025-10-14 01:06:12'),
(3, 1, 3, 2, 2, 2, NULL, NULL, NULL, '2025-10-14 01:06:12', '2025-10-14 01:06:12'),
(4, 1, 4, 4, 4, 4, NULL, NULL, NULL, '2025-10-14 01:06:12', '2025-10-14 01:06:12'),
(5, 1, 6, 1, 1, 1, NULL, NULL, NULL, '2025-10-14 01:06:12', '2025-10-14 01:06:12'),
(6, 1, 7, 1, 1, 1, NULL, NULL, NULL, '2025-10-14 01:06:12', '2025-10-14 01:06:12'),
(7, 1, 8, 1, 1, 1, NULL, NULL, NULL, '2025-10-14 01:06:12', '2025-10-14 01:06:12'),
(8, 2, 1, 4, 3, 4, NULL, NULL, NULL, '2025-10-14 01:06:12', '2025-10-14 01:06:12'),
(9, 2, 2, 2, 1, 2, NULL, NULL, NULL, '2025-10-14 01:06:12', '2025-10-14 01:06:12'),
(10, 2, 3, 2, 2, 2, NULL, NULL, NULL, '2025-10-14 01:06:12', '2025-10-14 01:06:12'),
(11, 2, 4, 4, 4, 4, NULL, NULL, NULL, '2025-10-14 01:06:12', '2025-10-14 01:06:12'),
(12, 2, 6, 1, 1, 1, NULL, NULL, NULL, '2025-10-14 01:06:12', '2025-10-14 01:06:12'),
(13, 2, 7, 1, 1, 1, NULL, NULL, NULL, '2025-10-14 01:06:12', '2025-10-14 01:06:12'),
(14, 2, 8, 1, 1, 1, NULL, NULL, NULL, '2025-10-14 01:06:12', '2025-10-14 01:06:12'),
(15, 1, 9, 2, 2, 2, NULL, NULL, NULL, '2025-10-14 01:16:13', '2025-10-14 01:16:13'),
(16, 1, 10, 2, 2, 2, NULL, NULL, NULL, '2025-10-14 01:16:13', '2025-10-14 01:16:13'),
(17, 1, 11, 4, 4, 4, NULL, NULL, NULL, '2025-10-14 01:16:13', '2025-10-14 01:16:13'),
(18, 2, 9, 2, 1, 2, NULL, NULL, NULL, '2025-10-14 01:16:13', '2025-10-14 01:16:13'),
(19, 2, 10, 2, 2, 2, NULL, NULL, NULL, '2025-10-14 01:16:13', '2025-10-14 01:16:13'),
(20, 2, 11, 4, 3, 4, NULL, NULL, NULL, '2025-10-14 01:16:13', '2025-10-14 01:16:13'),
(21, 3, 6, 4, 4, 4, NULL, NULL, NULL, '2025-10-14 01:16:13', '2025-10-14 01:16:13'),
(22, 3, 7, 2, 2, 2, NULL, NULL, NULL, '2025-10-14 01:16:13', '2025-10-14 01:16:13'),
(23, 3, 8, 2, 2, 2, NULL, NULL, NULL, '2025-10-14 01:16:13', '2025-10-14 01:16:13'),
(24, 3, 9, 2, 2, 2, NULL, NULL, NULL, '2025-10-14 01:16:13', '2025-10-14 01:16:13'),
(25, 3, 10, 2, 2, 2, NULL, NULL, NULL, '2025-10-14 01:16:13', '2025-10-14 01:16:13'),
(26, 3, 11, 4, 4, 4, NULL, NULL, NULL, '2025-10-14 01:16:13', '2025-10-14 01:16:13');

-- --------------------------------------------------------

--
-- Table structure for table `room_inventory_items`
--

CREATE TABLE `room_inventory_items` (
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

--
-- Table structure for table `room_inventory_transactions`
--

CREATE TABLE `room_inventory_transactions` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `transaction_type` enum('restock','usage','audit','adjustment','transfer') NOT NULL,
  `quantity_change` int(11) NOT NULL,
  `quantity_before` int(11) NOT NULL,
  `quantity_after` int(11) NOT NULL,
  `reason` varchar(200) DEFAULT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `scenario_questions`
--

CREATE TABLE `scenario_questions` (
  `id` int(11) NOT NULL,
  `scenario_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `question_order` int(11) NOT NULL,
  `correct_answer` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `scenario_questions`
--

INSERT INTO `scenario_questions` (`id`, `scenario_id`, `question`, `question_order`, `correct_answer`) VALUES
(1, 1, 'What is the first step in the check-in process?', 1, 'A'),
(2, 1, 'How should you handle a guest with special requests?', 2, 'B'),
(5, 4, 'What is the first action in an emergency?', 1, 'B'),
(6, 2, 'What is the first step when a guest reports lost luggage?', 1, 'A'),
(7, 2, 'How should you document the lost luggage report?', 2, 'B'),
(8, 2, 'What information should you collect from the guest?', 3, 'C'),
(9, 2, 'What is the appropriate follow-up action?', 4, 'A'),
(10, 3, 'What is the first step when facing a staff shortage?', 1, 'A'),
(11, 3, 'How should you prioritize tasks during a shortage?', 2, 'B'),
(12, 3, 'What communication is essential during this situation?', 3, 'C'),
(13, 3, 'How should you handle guest expectations?', 4, 'A'),
(14, 5, 'What is the first step when a guest disputes their bill?', 1, 'A'),
(15, 5, 'How should you handle the guest\'s concerns about charges?', 2, 'B'),
(16, 5, 'What documentation should you review during a billing dispute?', 3, 'C'),
(17, 5, 'What is the appropriate resolution approach for billing disputes?', 4, 'A'),
(18, 6, 'What is the first step in the check-in process?', 1, 'B'),
(19, 6, 'Why must you verify a guest\'s ID at check-in?', 2, 'C'),
(20, 7, 'What is the appropriate first response in an overbooking situation?', 1, 'A'),
(21, 7, 'Which is the best immediate solution if no room is available?', 2, 'B'),
(22, 7, 'What is a fair compensation to offer when walking a guest?', 3, 'D'),
(23, 7, 'What should be documented after resolving an overbooking?', 4, 'C'),
(24, 8, 'What is the first step in the check-in process?', 1, 'B'),
(25, 8, 'Why must you verify a guest\'s ID at check-in?', 2, 'C'),
(26, 9, 'What is the appropriate first response in an overbooking situation?', 1, 'A'),
(27, 9, 'Which is the best immediate solution if no room is available?', 2, 'B'),
(28, 9, 'What is a fair compensation to offer when walking a guest?', 3, 'D'),
(29, 9, 'What should be documented after resolving an overbooking?', 4, 'C'),
(30, 10, 'What is the first step in the check-in process?', 1, 'B'),
(31, 10, 'Why must you verify a guest\'s ID at check-in?', 2, 'C'),
(32, 11, 'What is the appropriate first response in an overbooking situation?', 1, 'A'),
(33, 11, 'Which is the best immediate solution if no room is available?', 2, 'B'),
(34, 11, 'What is a fair compensation to offer when walking a guest?', 3, 'D'),
(35, 11, 'What should be documented after resolving an overbooking?', 4, 'C'),
(36, 12, 'What is the first step in the check-in process?', 1, 'B'),
(37, 12, 'Why must you verify a guest\'s ID at check-in?', 2, 'C'),
(38, 13, 'What is the appropriate first response in an overbooking situation?', 1, 'A'),
(39, 13, 'Which is the best immediate solution if no room is available?', 2, 'B'),
(40, 13, 'What is a fair compensation to offer when walking a guest?', 3, 'D'),
(41, 13, 'What should be documented after resolving an overbooking?', 4, 'C'),
(42, 1, 'What is the best way to follow up after resolving the complaint?', 3, 'C'),
(43, 4, 'What if the noisy guest refuses to cooperate?', 2, 'B'),
(44, 4, 'How should you follow up with the complaining guest?', 3, 'D'),
(45, 1, 'What is the first step you should take when you discover the overbooking situation?', 1, 'A'),
(46, 1, 'How should you prioritize which guests to relocate?', 2, 'B'),
(47, 1, 'What compensation should you offer to relocated guests?', 3, 'C'),
(48, 1, 'How should you communicate the situation to affected guests?', 4, 'A'),
(49, 2, 'What is the immediate priority during a power outage?', 1, 'A'),
(50, 2, 'How should you handle food safety during the outage?', 2, 'B'),
(51, 2, 'What should you do about guests stuck in elevators?', 3, 'C'),
(52, 3, 'What is the first action you should take when a guest collapses?', 1, 'A'),
(53, 3, 'How should you handle the crowd around the medical emergency?', 2, 'B'),
(54, 3, 'What information should you gather about the guest?', 3, 'C'),
(55, 3, 'How should you communicate with the guest\'s family?', 4, 'D'),
(56, 4, 'What is the first step when facing a staff shortage crisis?', 1, 'A'),
(57, 4, 'How should you prioritize which services to maintain?', 2, 'B'),
(58, 4, 'What should you do about the wedding party and corporate event?', 3, 'C'),
(59, 5, 'What is the immediate response to a security breach report?', 1, 'A'),
(60, 5, 'How should you handle the guest who is threatening to call police?', 2, 'B'),
(61, 5, 'What information should you gather for the investigation?', 3, 'C'),
(62, 5, 'How should you communicate with other guests about the incident?', 4, 'D');

-- --------------------------------------------------------

--
-- Table structure for table `service_charges`
--

CREATE TABLE `service_charges` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `charged_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_charges`
--

INSERT INTO `service_charges` (`id`, `reservation_id`, `service_id`, `quantity`, `unit_price`, `total_price`, `notes`, `charged_by`, `created_at`) VALUES
(1, 1, 1, 1, 25.00, 25.00, 'Room Service - Breakfast', 1, '2025-10-13 04:00:03'),
(2, 2, 2, 1, 45.00, 45.00, 'Room Service - Dinner', 1, '2025-10-13 03:00:03'),
(3, 3, 3, 2, 15.00, 30.00, 'Laundry Service', 1, '2025-10-13 02:00:03'),
(4, 1, 4, 3, 12.50, 37.50, 'Minibar Items', 1, '2025-10-13 01:00:03'),
(5, 2, 5, 1, 20.00, 20.00, 'Concierge Service', 1, '2025-10-13 00:00:03'),
(6, 3, 6, 1, 10.00, 10.00, 'Housekeeping Extra', 1, '2025-10-12 23:00:03'),
(7, 1, 1, 1, 25.00, 25.00, 'Room Service - Breakfast', 1, '2025-10-13 04:00:38'),
(8, 2, 2, 1, 45.00, 45.00, 'Room Service - Dinner', 1, '2025-10-13 03:00:38'),
(9, 3, 3, 2, 15.00, 30.00, 'Laundry Service', 1, '2025-10-13 02:00:38'),
(10, 1, 4, 3, 12.50, 37.50, 'Minibar Items', 1, '2025-10-13 01:00:38'),
(11, 2, 5, 1, 20.00, 20.00, 'Concierge Service', 1, '2025-10-13 00:00:38'),
(12, 3, 6, 1, 10.00, 10.00, 'Housekeeping Extra', 1, '2025-10-12 23:00:38');

-- --------------------------------------------------------

--
-- Table structure for table `service_requests`
--

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

--
-- Dumping data for table `service_requests`
--

INSERT INTO `service_requests` (`id`, `reservation_id`, `room_id`, `guest_id`, `service_type`, `title`, `description`, `priority`, `status`, `requested_by`, `assigned_to`, `requested_at`, `completed_at`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 1, 'room_service', 'Room Service - Breakfast', 'Guest requested breakfast delivery to room', 'medium', 'pending', 1, 1, '2025-10-13 03:56:35', NULL, '2025-10-13 04:56:35', '2025-10-13 04:56:35'),
(2, 2, 6, 2, 'housekeeping', 'Housekeeping - Extra Towels', 'Guest needs additional towels', 'low', 'in_progress', 1, 1, '2025-10-13 02:56:35', NULL, '2025-10-13 04:56:35', '2025-10-13 04:56:35'),
(3, 3, 8, 3, 'maintenance', 'Maintenance - AC Issue', 'Air conditioning not working properly', 'high', 'completed', 1, 1, '2025-10-13 01:56:35', '2025-10-13 02:56:35', '2025-10-13 04:56:35', '2025-10-13 04:56:35'),
(4, 1, 2, 1, 'concierge', 'Concierge - Restaurant Booking', 'Guest wants dinner reservation', 'low', 'pending', 1, 1, '2025-10-13 00:56:35', NULL, '2025-10-13 04:56:35', '2025-10-13 04:56:35'),
(5, 2, 6, 2, 'laundry', 'Laundry Service', 'Guest needs clothes cleaned', 'medium', 'in_progress', 1, 1, '2025-10-12 23:56:35', NULL, '2025-10-13 04:56:35', '2025-10-13 04:56:35'),
(6, 3, 8, 3, 'minibar', 'Minibar Restock', 'Minibar needs to be restocked', 'low', 'completed', 1, 1, '2025-10-12 22:56:35', '2025-10-12 23:56:35', '2025-10-13 04:56:35', '2025-10-13 04:56:35'),
(7, 1, 2, 1, 'room_service', 'Room Service - Dinner', 'Guest ordered dinner', 'medium', 'pending', 1, 1, '2025-10-12 21:56:35', NULL, '2025-10-13 04:56:35', '2025-10-13 04:56:35'),
(8, 2, 6, 2, 'housekeeping', 'Housekeeping - Deep Clean', 'Room needs deep cleaning', 'high', 'in_progress', 1, 1, '2025-10-12 20:56:35', NULL, '2025-10-13 04:56:35', '2025-10-13 04:56:35'),
(9, 1, 2, 1, 'room_service', 'Room Service - Breakfast', 'Guest requested breakfast delivery to room', 'medium', 'pending', 1, 1, '2025-10-13 03:58:37', NULL, '2025-10-13 04:58:37', '2025-10-13 04:58:37'),
(10, 2, 6, 2, 'housekeeping', 'Housekeeping - Extra Towels', 'Guest needs additional towels', 'low', 'in_progress', 1, 1, '2025-10-13 02:58:37', NULL, '2025-10-13 04:58:37', '2025-10-13 04:58:37'),
(11, 3, 8, 3, 'maintenance', 'Maintenance - AC Issue', 'Air conditioning not working properly', 'high', 'completed', 1, 1, '2025-10-13 01:58:37', '2025-10-13 02:58:37', '2025-10-13 04:58:37', '2025-10-13 04:58:37'),
(12, 1, 2, 1, 'concierge', 'Concierge - Restaurant Booking', 'Guest wants dinner reservation', 'low', 'pending', 1, 1, '2025-10-13 00:58:37', NULL, '2025-10-13 04:58:37', '2025-10-13 04:58:37'),
(13, 2, 6, 2, 'laundry', 'Laundry Service', 'Guest needs clothes cleaned', 'medium', 'in_progress', 1, 1, '2025-10-12 23:58:37', NULL, '2025-10-13 04:58:37', '2025-10-13 04:58:37'),
(14, 3, 8, 3, 'minibar', 'Minibar Restock', 'Minibar needs to be restocked', 'low', 'completed', 1, 1, '2025-10-12 22:58:37', '2025-10-12 23:58:37', '2025-10-13 04:58:37', '2025-10-13 04:58:37'),
(15, 1, 2, 1, 'room_service', 'Room Service - Dinner', 'Guest ordered dinner', 'medium', 'pending', 1, 1, '2025-10-12 21:58:37', NULL, '2025-10-13 04:58:37', '2025-10-13 04:58:37'),
(16, 2, 6, 2, 'housekeeping', 'Housekeeping - Deep Clean', 'Room needs deep cleaning', 'high', 'in_progress', 1, 1, '2025-10-12 20:58:37', NULL, '2025-10-13 04:58:37', '2025-10-13 04:58:37'),
(17, 1, 2, 1, 'room_service', 'Room Service - Breakfast', 'Guest requested breakfast delivery to room', 'medium', 'pending', 1, 1, '2025-10-13 03:58:57', NULL, '2025-10-13 04:58:57', '2025-10-13 04:58:57'),
(18, 2, 6, 2, 'housekeeping', 'Housekeeping - Extra Towels', 'Guest needs additional towels', 'low', 'in_progress', 1, 1, '2025-10-13 02:58:57', NULL, '2025-10-13 04:58:57', '2025-10-13 04:58:57'),
(19, 3, 8, 3, 'maintenance', 'Maintenance - AC Issue', 'Air conditioning not working properly', 'high', 'completed', 1, 1, '2025-10-13 01:58:57', '2025-10-13 02:58:57', '2025-10-13 04:58:57', '2025-10-13 04:58:57'),
(20, 1, 2, 1, 'concierge', 'Concierge - Restaurant Booking', 'Guest wants dinner reservation', 'low', 'pending', 1, 1, '2025-10-13 00:58:57', NULL, '2025-10-13 04:58:57', '2025-10-13 04:58:57'),
(21, 2, 6, 2, 'laundry', 'Laundry Service', 'Guest needs clothes cleaned', 'medium', 'in_progress', 1, 1, '2025-10-12 23:58:57', NULL, '2025-10-13 04:58:57', '2025-10-13 04:58:57'),
(22, 3, 8, 3, 'minibar', 'Minibar Restock', 'Minibar needs to be restocked', 'low', 'completed', 1, 1, '2025-10-12 22:58:57', '2025-10-12 23:58:57', '2025-10-13 04:58:57', '2025-10-13 04:58:57'),
(23, 1, 2, 1, 'room_service', 'Room Service - Dinner', 'Guest ordered dinner', 'medium', 'pending', 1, 1, '2025-10-12 21:58:57', NULL, '2025-10-13 04:58:57', '2025-10-13 04:58:57'),
(24, 2, 6, 2, 'housekeeping', 'Housekeeping - Deep Clean', 'Room needs deep cleaning', 'high', 'in_progress', 1, 1, '2025-10-12 20:58:57', NULL, '2025-10-13 04:58:57', '2025-10-13 04:58:57'),
(25, 1, 2, 1, 'room_service', 'Room Service - Breakfast', 'Guest requested breakfast delivery to room', 'medium', 'pending', 1, 1, '2025-10-13 04:00:03', NULL, '2025-10-13 05:00:03', '2025-10-13 05:00:03'),
(26, 2, 6, 2, 'housekeeping', 'Housekeeping - Extra Towels', 'Guest needs additional towels', 'low', 'in_progress', 1, 1, '2025-10-13 03:00:03', NULL, '2025-10-13 05:00:03', '2025-10-13 05:00:03'),
(27, 3, 8, 3, 'maintenance', 'Maintenance - AC Issue', 'Air conditioning not working properly', 'high', 'completed', 1, 1, '2025-10-13 02:00:03', '2025-10-13 03:00:03', '2025-10-13 05:00:03', '2025-10-13 05:00:03'),
(28, 1, 2, 1, 'concierge', 'Concierge - Restaurant Booking', 'Guest wants dinner reservation', 'low', 'pending', 1, 1, '2025-10-13 01:00:03', NULL, '2025-10-13 05:00:03', '2025-10-13 05:00:03'),
(29, 2, 6, 2, 'laundry', 'Laundry Service', 'Guest needs clothes cleaned', 'medium', 'in_progress', 1, 1, '2025-10-13 00:00:03', NULL, '2025-10-13 05:00:03', '2025-10-13 05:00:03'),
(30, 3, 8, 3, 'minibar', 'Minibar Restock', 'Minibar needs to be restocked', 'low', 'completed', 1, 1, '2025-10-12 23:00:03', '2025-10-13 00:00:03', '2025-10-13 05:00:03', '2025-10-13 05:00:03'),
(31, 1, 2, 1, 'room_service', 'Room Service - Dinner', 'Guest ordered dinner', 'medium', 'pending', 1, 1, '2025-10-12 22:00:03', NULL, '2025-10-13 05:00:03', '2025-10-13 05:00:03'),
(32, 2, 6, 2, 'housekeeping', 'Housekeeping - Deep Clean', 'Room needs deep cleaning', 'high', 'in_progress', 1, 1, '2025-10-12 21:00:03', NULL, '2025-10-13 05:00:03', '2025-10-13 05:00:03'),
(33, 1, 2, 1, 'room_service', 'Room Service - Breakfast', 'Guest requested breakfast delivery to room', 'medium', 'pending', 1, 1, '2025-10-13 04:00:38', NULL, '2025-10-13 05:00:38', '2025-10-13 05:00:38'),
(34, 2, 6, 2, 'housekeeping', 'Housekeeping - Extra Towels', 'Guest needs additional towels', 'low', 'in_progress', 1, 1, '2025-10-13 03:00:38', NULL, '2025-10-13 05:00:38', '2025-10-13 05:00:38'),
(35, 3, 8, 3, 'maintenance', 'Maintenance - AC Issue', 'Air conditioning not working properly', 'high', 'completed', 1, 1, '2025-10-13 02:00:38', '2025-10-13 03:00:38', '2025-10-13 05:00:38', '2025-10-13 05:00:38'),
(36, 1, 2, 1, 'concierge', 'Concierge - Restaurant Booking', 'Guest wants dinner reservation', 'low', 'pending', 1, 1, '2025-10-13 01:00:38', NULL, '2025-10-13 05:00:38', '2025-10-13 05:00:38'),
(37, 2, 6, 2, 'laundry', 'Laundry Service', 'Guest needs clothes cleaned', 'medium', 'in_progress', 1, 1, '2025-10-13 00:00:38', NULL, '2025-10-13 05:00:38', '2025-10-13 05:00:38'),
(38, 3, 8, 3, 'minibar', 'Minibar Restock', 'Minibar needs to be restocked', 'low', 'completed', 1, 1, '2025-10-12 23:00:38', '2025-10-13 00:00:38', '2025-10-13 05:00:38', '2025-10-13 05:00:38'),
(39, 1, 2, 1, 'room_service', 'Room Service - Dinner', 'Guest ordered dinner', 'medium', 'pending', 1, 1, '2025-10-12 22:00:38', NULL, '2025-10-13 05:00:38', '2025-10-13 05:00:38'),
(40, 2, 6, 2, 'housekeeping', 'Housekeeping - Deep Clean', 'Room needs deep cleaning', 'high', 'in_progress', 1, 1, '2025-10-12 21:00:38', NULL, '2025-10-13 05:00:38', '2025-10-13 05:00:38');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

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

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `contact_person`, `email`, `phone`, `address`, `active`, `created_at`, `updated_at`) VALUES
(1, 'ABC Supply Co.', 'John Smith', 'john@abcsupply.com', '+1-555-0101', NULL, 1, '2025-10-14 01:06:12', '2025-10-14 01:06:12'),
(2, 'XYZ Distributors', 'Jane Doe', 'jane@xyzdist.com', '+1-555-0102', NULL, 1, '2025-10-14 01:06:12', '2025-10-14 01:06:12'),
(3, 'Hotel Essentials Ltd.', 'Mike Johnson', 'mike@hotelessentials.com', '+1-555-0103', NULL, 1, '2025-10-14 01:06:12', '2025-10-14 01:06:12'),
(4, 'Quality Products Inc.', 'Sarah Wilson', 'sarah@qualityproducts.com', '+1-555-0104', NULL, 1, '2025-10-14 01:06:12', '2025-10-14 01:06:12'),
(5, 'Bulk Supplies Corp.', 'David Brown', 'david@bulksupplies.com', '+1-555-0105', NULL, 1, '2025-10-14 01:06:12', '2025-10-14 01:06:12'),
(6, 'Luxury Linens Ltd.', 'Emma Davis', 'emma@luxurylinens.com', '+1-555-0106', NULL, 1, '2025-10-14 01:16:13', '2025-10-14 01:16:13'),
(7, 'Spa Essentials Co.', 'Robert Miller', 'robert@spaessentials.com', '+1-555-0107', NULL, 1, '2025-10-14 01:16:13', '2025-10-14 01:16:13'),
(8, 'CleanPro Solutions', 'Lisa Garcia', 'lisa@cleanpro.com', '+1-555-0108', NULL, 1, '2025-10-14 01:16:13', '2025-10-14 01:16:13'),
(9, 'Office Depot Pro', 'Michael Rodriguez', 'michael@officedepot.com', '+1-555-0109', NULL, 1, '2025-10-14 01:16:13', '2025-10-14 01:16:13'),
(10, 'Electrical Supply Co.', 'Jennifer Martinez', 'jennifer@electricalsupply.com', '+1-555-0110', NULL, 1, '2025-10-14 01:16:13', '2025-10-14 01:16:13'),
(11, 'HVAC Solutions Inc.', 'Christopher Anderson', 'chris@hvac.com', '+1-555-0111', NULL, 1, '2025-10-14 01:16:13', '2025-10-14 01:16:13'),
(12, 'Paint & Decor Co.', 'Amanda Taylor', 'amanda@paintdecor.com', '+1-555-0112', NULL, 1, '2025-10-14 01:16:13', '2025-10-14 01:16:13'),
(13, 'Hardware Solutions', 'Daniel Thomas', 'daniel@hardware.com', '+1-555-0113', NULL, 1, '2025-10-14 01:16:13', '2025-10-14 01:16:13'),
(14, 'ABC Supplies Inc.', 'John Smith', 'john@abcsupplies.com', '+1-555-0123', NULL, 1, '2025-10-14 01:27:24', '2025-10-14 01:27:24'),
(15, 'XYZ Hospitality', 'Jane Doe', 'jane@xyz.com', '+1-555-0456', NULL, 1, '2025-10-14 01:27:24', '2025-10-14 01:27:24'),
(16, 'Global Hotel Supplies', 'Mike Johnson', 'mike@global.com', '+1-555-0789', NULL, 1, '2025-10-14 01:27:24', '2025-10-14 01:27:24');

-- --------------------------------------------------------

--
-- Table structure for table `supply_requests`
--

CREATE TABLE `supply_requests` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `requested_by` int(11) NOT NULL,
  `quantity_requested` int(11) NOT NULL,
  `priority` enum('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  `notes` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','fulfilled') NOT NULL DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `fulfilled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `training_attempts`
--

CREATE TABLE `training_attempts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `scenario_id` int(11) NOT NULL,
  `scenario_type` enum('scenario','customer_service','problem_solving') NOT NULL,
  `answers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`answers`)),
  `score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `duration_minutes` int(11) DEFAULT 0,
  `status` enum('in_progress','completed','abandoned') DEFAULT 'in_progress',
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `training_attempts`
--

INSERT INTO `training_attempts` (`id`, `user_id`, `scenario_id`, `scenario_type`, `answers`, `score`, `duration_minutes`, `status`, `completed_at`, `created_at`) VALUES
(1, 1003, 1, 'scenario', NULL, 0.00, 0, 'in_progress', NULL, '2025-10-09 09:07:09'),
(2, 1003, 3, 'scenario', NULL, 0.00, 0, 'in_progress', NULL, '2025-10-09 09:18:51'),
(3, 1003, 3, 'scenario', NULL, 0.00, 0, 'in_progress', NULL, '2025-10-09 09:24:55'),
(4, 1003, 4, 'scenario', NULL, 0.00, 0, 'in_progress', NULL, '2025-10-09 10:07:20'),
(5, 1003, 2, 'scenario', NULL, 0.00, 0, 'in_progress', NULL, '2025-10-09 10:24:55'),
(6, 1003, 6, 'scenario', '{\"1\":\"B\",\"2\":\"C\"}', 100.00, 0, 'completed', '2025-10-10 09:30:51', '2025-10-09 10:51:21'),
(7, 1003, 8, 'scenario', NULL, 0.00, 0, 'in_progress', NULL, '2025-10-09 10:58:38'),
(8, 1003, 11, 'scenario', NULL, 0.00, 0, 'in_progress', NULL, '2025-10-10 01:13:55'),
(9, 1003, 5, 'customer_service', '{\"1\":\"A\"}', 0.00, 0, 'in_progress', NULL, '2025-10-10 01:37:30'),
(10, 1003, 5, 'customer_service', '{\"1\":\"A\",\"2\":\"C\",\"3\":\"D\",\"4\":\"C\"}', 25.00, 0, 'completed', '2025-10-10 09:47:21', '2025-10-10 01:37:35'),
(11, 1003, 6, 'scenario', NULL, 0.00, 0, 'in_progress', NULL, '2025-10-10 01:37:42'),
(12, 1003, 4, 'problem_solving', '{\"1\":\"B\",\"2\":\"B\",\"3\":\"C\",\"4\":\"C\",\"5\":\"C\",\"6\":\"B\"}', 66.67, 0, 'completed', '2025-10-10 09:54:32', '2025-10-10 01:54:15'),
(13, 1003, 5, 'problem_solving', NULL, 0.00, 0, 'in_progress', NULL, '2025-10-10 01:54:51'),
(14, 1003, 5, 'problem_solving', NULL, 0.00, 0, 'in_progress', NULL, '2025-10-10 01:55:00');

-- --------------------------------------------------------

--
-- Table structure for table `training_categories`
--

CREATE TABLE `training_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'fas fa-folder',
  `color` varchar(20) DEFAULT 'gray',
  `parent_id` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `training_categories`
--

INSERT INTO `training_categories` (`id`, `name`, `description`, `icon`, `color`, `parent_id`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 'Point of Sale', 'POS system training modules', 'fas fa-cash-register', 'green', NULL, 0, 1, '2025-10-02 02:51:58'),
(2, 'Inventory Management', 'Inventory and stock management training', 'fas fa-boxes', 'blue', NULL, 0, 1, '2025-10-02 02:51:58'),
(3, 'Booking System', 'Reservation and booking management', 'fas fa-calendar-check', 'purple', NULL, 0, 1, '2025-10-02 02:51:58'),
(4, 'General Training', 'General hotel operations training', 'fas fa-graduation-cap', 'gray', NULL, 0, 1, '2025-10-02 02:51:58');

-- --------------------------------------------------------

--
-- Table structure for table `training_certificates`
--

CREATE TABLE `training_certificates` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `type` enum('scenario','customer_service','problem_solving') NOT NULL,
  `status` enum('earned','expired') DEFAULT 'earned',
  `earned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `training_prerequisites`
--

CREATE TABLE `training_prerequisites` (
  `id` int(11) NOT NULL,
  `tutorial_module_id` int(11) NOT NULL,
  `prerequisite_module_id` int(11) NOT NULL,
  `prerequisite_type` enum('module_completion','step_completion','assessment_pass') DEFAULT 'module_completion',
  `required_score` decimal(5,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `training_resources`
--

CREATE TABLE `training_resources` (
  `id` int(11) NOT NULL,
  `tutorial_module_id` int(11) DEFAULT NULL,
  `tutorial_step_id` int(11) DEFAULT NULL,
  `resource_type` enum('document','video','image','link','file') NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `download_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `training_resources`
--

INSERT INTO `training_resources` (`id`, `tutorial_module_id`, `tutorial_step_id`, `resource_type`, `title`, `description`, `file_path`, `file_size`, `mime_type`, `is_public`, `download_count`, `created_at`) VALUES
(1, 1, NULL, 'document', 'POS Quick Reference Guide', 'A quick reference guide for common POS operations', '/resources/pos-quick-reference.pdf', NULL, NULL, 1, 0, '2025-10-02 02:51:58'),
(2, 1, NULL, 'video', 'POS System Overview', 'Video introduction to the POS system interface', '/resources/pos-overview.mp4', NULL, NULL, 1, 0, '2025-10-02 02:51:58'),
(3, 1, NULL, 'link', 'POS Support Documentation', 'Link to comprehensive POS documentation', 'https://support.example.com/pos', NULL, NULL, 1, 0, '2025-10-02 02:51:58');

-- --------------------------------------------------------

--
-- Table structure for table `training_scenarios`
--

CREATE TABLE `training_scenarios` (
  `id` int(11) NOT NULL,
  `scenario_id` varchar(50) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `instructions` text DEFAULT NULL,
  `category` enum('front_desk','housekeeping','management') NOT NULL,
  `difficulty` enum('beginner','intermediate','advanced') NOT NULL,
  `estimated_time` int(11) NOT NULL DEFAULT 10,
  `points` int(11) NOT NULL DEFAULT 10,
  `status` enum('active','inactive','draft') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `training_scenarios`
--

INSERT INTO `training_scenarios` (`id`, `scenario_id`, `title`, `description`, `instructions`, `category`, `difficulty`, `estimated_time`, `points`, `status`, `created_at`, `updated_at`) VALUES
(1, 'check_in_process', 'Check-in Process', 'Handle a guest check-in with special requests', 'Follow the standard check-in procedure while accommodating guest requests', 'front_desk', 'beginner', 10, 10, 'active', '2025-08-27 10:02:44', '2025-08-31 13:44:56'),
(2, 'overbooking_situation', 'Overbooking Situation', 'Manage an overbooking scenario', 'Handle the situation professionally and find alternative solutions', 'front_desk', 'intermediate', 15, 20, 'active', '2025-08-27 10:02:44', '2025-08-31 13:44:56'),
(3, 'guest_complaint', 'Guest Complaint', 'Resolve a guest complaint about room cleanliness', 'Listen actively and provide appropriate solutions', 'front_desk', 'beginner', 12, 15, 'active', '2025-08-27 10:02:44', '2025-08-31 13:44:56'),
(4, 'emergency_response', 'Emergency Response', 'Handle a medical emergency in the hotel', 'Follow emergency protocols and coordinate with medical services', 'management', 'advanced', 20, 30, 'active', '2025-08-27 10:02:44', '2025-08-31 13:44:56'),
(5, 'revenue_management', 'Revenue Management', 'Optimize room pricing for high demand period', 'Analyze market conditions and adjust pricing strategy', 'management', 'advanced', 25, 35, 'active', '2025-08-27 10:02:44', '2025-08-31 13:44:56'),
(6, NULL, 'Check-in Process', 'Handle a guest check-in with special requests', 'Follow SOPs while accommodating requests professionally.', 'front_desk', 'beginner', 10, 10, 'active', '2025-10-09 10:46:53', '2025-10-09 10:46:53'),
(7, NULL, 'Overbooking Situation', 'Manage a front-desk overbooking case professionally.', 'Acknowledge the issue, apologize, offer solutions and document actions.', 'front_desk', 'intermediate', 10, 10, 'active', '2025-10-09 10:47:05', '2025-10-09 10:47:05'),
(8, NULL, 'Check-in Process', 'Handle a guest check-in with special requests', 'Follow SOPs while accommodating requests professionally.', 'front_desk', 'beginner', 10, 10, 'active', '2025-10-09 10:48:55', '2025-10-09 10:48:55'),
(9, NULL, 'Overbooking Situation', 'Manage a front-desk overbooking case professionally.', 'Acknowledge the issue, apologize, offer solutions and document actions.', 'front_desk', 'intermediate', 10, 10, 'active', '2025-10-09 10:48:55', '2025-10-09 10:48:55'),
(10, NULL, 'Check-in Process', 'Handle a guest check-in with special requests', 'Follow SOPs while accommodating requests professionally.', 'front_desk', 'beginner', 10, 10, 'active', '2025-10-09 10:50:36', '2025-10-09 10:50:36'),
(11, NULL, 'Overbooking Situation', 'Manage a front-desk overbooking case professionally.', 'Acknowledge the issue, apologize, offer solutions and document actions.', 'front_desk', 'intermediate', 10, 10, 'active', '2025-10-09 10:50:36', '2025-10-09 10:50:36'),
(12, NULL, 'Check-in Process', 'Handle a guest check-in with special requests', 'Follow SOPs while accommodating requests professionally.', 'front_desk', 'beginner', 10, 10, 'active', '2025-10-09 10:51:05', '2025-10-09 10:51:05'),
(13, NULL, 'Overbooking Situation', 'Manage a front-desk overbooking case professionally.', 'Acknowledge the issue, apologize, offer solutions and document actions.', 'front_desk', 'intermediate', 10, 10, 'active', '2025-10-09 10:51:05', '2025-10-09 10:51:05');

-- --------------------------------------------------------

--
-- Table structure for table `training_tags`
--

CREATE TABLE `training_tags` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `color` varchar(20) DEFAULT 'blue',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `training_tags`
--

INSERT INTO `training_tags` (`id`, `name`, `color`, `description`, `created_at`) VALUES
(1, 'Beginner', 'green', 'Suitable for beginners', '2025-10-02 02:51:58'),
(2, 'Intermediate', 'yellow', 'Intermediate level training', '2025-10-02 02:51:58'),
(3, 'Advanced', 'red', 'Advanced level training', '2025-10-02 02:51:58'),
(4, 'Essential', 'blue', 'Essential training for all staff', '2025-10-02 02:51:58'),
(5, 'Optional', 'gray', 'Optional training modules', '2025-10-02 02:51:58');

-- --------------------------------------------------------

--
-- Table structure for table `tutorial_analytics`
--

CREATE TABLE `tutorial_analytics` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tutorial_module_id` int(11) NOT NULL,
  `action_type` enum('start','step_complete','assessment_complete','pause','resume','complete') NOT NULL,
  `step_id` int(11) DEFAULT NULL,
  `time_spent` int(11) DEFAULT 0 COMMENT 'Time in seconds',
  `score` decimal(5,2) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Additional tracking data' CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tutorial_analytics`
--

INSERT INTO `tutorial_analytics` (`id`, `user_id`, `tutorial_module_id`, `action_type`, `step_id`, `time_spent`, `score`, `metadata`, `created_at`) VALUES
(1, 1, 1, 'step_complete', 1, 120, 85.50, NULL, '2025-10-01 17:19:47');

-- --------------------------------------------------------

--
-- Table structure for table `tutorial_assessments`
--

CREATE TABLE `tutorial_assessments` (
  `id` int(11) NOT NULL,
  `tutorial_step_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `question_type` enum('multiple_choice','true_false','fill_blank','simulation') NOT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'For multiple choice questions' CHECK (json_valid(`options`)),
  `correct_answer` text NOT NULL,
  `explanation` text DEFAULT NULL,
  `points` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tutorial_assessments`
--

INSERT INTO `tutorial_assessments` (`id`, `tutorial_step_id`, `question`, `question_type`, `options`, `correct_answer`, `explanation`, `points`, `created_at`) VALUES
(1, 1, 'What is the first step in processing a POS order?', 'multiple_choice', NULL, 'Click New Order', 'Starting a new order is the first step in any POS transaction', 1, '2025-10-01 17:14:59'),
(2, 2, 'True or False: You can add multiple items to a single order', 'true_false', NULL, 'True', 'POS systems allow multiple items to be added to a single order', 1, '2025-10-01 17:14:59'),
(3, 3, 'What happens when you click \"Process Payment\"?', 'multiple_choice', NULL, 'Transaction is finalized', 'Processing payment completes the transaction and updates inventory', 1, '2025-10-01 17:14:59'),
(4, 5, 'What does the Inventory Dashboard show?', 'multiple_choice', NULL, 'Current stock levels', 'The dashboard displays real-time inventory quantities and status', 1, '2025-10-01 17:14:59'),
(5, 6, 'True or False: You can only view inventory items, not modify them', 'true_false', NULL, 'False', 'Inventory systems allow both viewing and modifying stock levels', 1, '2025-10-01 17:14:59'),
(6, 9, 'What is the first step in guest check-in?', 'multiple_choice', NULL, 'Click Check-in Guest', 'Starting the check-in process is the first step in guest arrival', 1, '2025-10-01 17:14:59'),
(7, 10, 'True or False: Room assignment is optional during check-in', 'true_false', NULL, 'False', 'Room assignment is required to complete the check-in process', 1, '2025-10-01 17:14:59');

-- --------------------------------------------------------

--
-- Table structure for table `tutorial_modules`
--

CREATE TABLE `tutorial_modules` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `module_type` enum('pos','inventory','booking') NOT NULL,
  `difficulty_level` enum('beginner','intermediate','advanced') NOT NULL,
  `estimated_duration` int(11) NOT NULL COMMENT 'Duration in minutes',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `icon` varchar(50) DEFAULT 'fas fa-graduation-cap',
  `color` varchar(20) DEFAULT 'blue',
  `prerequisites` text DEFAULT NULL,
  `learning_outcomes` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tutorial_modules`
--

INSERT INTO `tutorial_modules` (`id`, `name`, `description`, `module_type`, `difficulty_level`, `estimated_duration`, `is_active`, `created_at`, `updated_at`, `icon`, `color`, `prerequisites`, `learning_outcomes`, `category_id`) VALUES
(1, 'POS System Basics', 'Learn fundamental point of sale operations including order processing, payment handling, and receipt generation', 'pos', 'beginner', 30, 1, '2025-10-01 17:14:59', '2025-10-02 02:51:58', 'fas fa-graduation-cap', 'blue', NULL, NULL, 1),
(2, 'Inventory Management Fundamentals', 'Master stock control, supplier relations, and automated reordering systems', 'inventory', 'beginner', 45, 1, '2025-10-01 17:14:59', '2025-10-02 02:51:58', 'fas fa-graduation-cap', 'blue', NULL, NULL, 2),
(3, 'Front Desk Operations', 'Essential guest management, reservations, and check-in/out procedures', 'booking', 'beginner', 40, 1, '2025-10-01 17:14:59', '2025-10-02 02:51:58', 'fas fa-graduation-cap', 'blue', NULL, NULL, 3),
(4, 'Advanced POS Techniques', 'Complex order modifications, refunds, and multi-payment processing', 'pos', 'intermediate', 50, 1, '2025-10-01 17:14:59', '2025-10-02 02:51:58', 'fas fa-graduation-cap', 'blue', NULL, NULL, 1),
(5, 'Inventory Cost Analysis', 'Advanced cost management, profit margins, and supplier performance analysis', 'inventory', 'intermediate', 60, 1, '2025-10-01 17:14:59', '2025-10-02 02:51:58', 'fas fa-graduation-cap', 'blue', NULL, NULL, 2),
(6, 'Revenue Management', 'Advanced booking strategies, pricing optimization, and occupancy management', 'booking', 'intermediate', 55, 1, '2025-10-01 17:14:59', '2025-10-02 02:51:58', 'fas fa-graduation-cap', 'blue', NULL, NULL, 3),
(7, 'Enterprise POS Operations', 'Multi-location management, advanced reporting, and system integration', 'pos', 'advanced', 75, 1, '2025-10-01 17:14:59', '2025-10-02 02:51:58', 'fas fa-graduation-cap', 'blue', NULL, NULL, 1),
(8, 'Strategic Inventory Planning', 'Demand forecasting, procurement optimization, and supply chain management', 'inventory', 'advanced', 90, 1, '2025-10-01 17:14:59', '2025-10-02 02:51:58', 'fas fa-graduation-cap', 'blue', NULL, NULL, 2),
(9, 'Hotel Revenue Optimization', 'Advanced revenue management, market analysis, and competitive positioning', 'booking', 'advanced', 85, 1, '2025-10-01 17:14:59', '2025-10-02 02:51:58', 'fas fa-graduation-cap', 'blue', NULL, NULL, 3),
(10, 'E2E Test Module', 'Test module for end-to-end testing', 'pos', 'beginner', 30, 1, '2025-10-01 17:42:06', '2025-10-02 02:51:58', 'fas fa-graduation-cap', 'blue', NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tutorial_module_tags`
--

CREATE TABLE `tutorial_module_tags` (
  `tutorial_module_id` int(11) NOT NULL,
  `training_tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tutorial_progress`
--

CREATE TABLE `tutorial_progress` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tutorial_module_id` int(11) NOT NULL,
  `current_step` int(11) DEFAULT 1,
  `completion_percentage` decimal(5,2) DEFAULT 0.00,
  `time_spent` int(11) DEFAULT 0 COMMENT 'Time in seconds',
  `score` decimal(5,2) DEFAULT 0.00,
  `status` enum('not_started','in_progress','completed','paused') DEFAULT 'not_started',
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `last_accessed` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tutorial_progress`
--

INSERT INTO `tutorial_progress` (`id`, `user_id`, `tutorial_module_id`, `current_step`, `completion_percentage`, `time_spent`, `score`, `status`, `started_at`, `completed_at`, `last_accessed`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 25.00, 120, 85.50, 'in_progress', '2025-10-01 17:19:47', NULL, '2025-10-01 17:19:47', '2025-10-01 17:19:47', '2025-10-01 17:19:47');

-- --------------------------------------------------------

--
-- Table structure for table `tutorial_quizzes`
--

CREATE TABLE `tutorial_quizzes` (
  `id` int(11) NOT NULL,
  `tutorial_step_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `question_type` enum('multiple_choice','true_false','fill_blank','matching') DEFAULT 'multiple_choice',
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `correct_answer` varchar(500) DEFAULT NULL,
  `explanation` text DEFAULT NULL,
  `points` int(11) DEFAULT 1,
  `time_limit` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tutorial_quizzes`
--

INSERT INTO `tutorial_quizzes` (`id`, `tutorial_step_id`, `question`, `question_type`, `options`, `correct_answer`, `explanation`, `points`, `time_limit`, `is_active`, `created_at`) VALUES
(1, 4, 'What should you do when a customer requests a modification to a menu item?', 'multiple_choice', '[\"Tell them it is not possible\", \"Add the modification note to the order\", \"Charge extra without asking\", \"Ignore the request\"]', 'Add the modification note to the order', 'Always add modification notes to ensure the kitchen prepares the item correctly and meets customer expectations.', 1, 0, 1, '2025-10-02 02:51:58');

-- --------------------------------------------------------

--
-- Table structure for table `tutorial_simulations`
--

CREATE TABLE `tutorial_simulations` (
  `id` int(11) NOT NULL,
  `tutorial_step_id` int(11) NOT NULL,
  `simulation_type` enum('pos_order','inventory_check','booking_process','payment_processing') NOT NULL,
  `simulation_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`simulation_data`)),
  `success_criteria` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`success_criteria`)),
  `instructions` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tutorial_simulations`
--

INSERT INTO `tutorial_simulations` (`id`, `tutorial_step_id`, `simulation_type`, `simulation_data`, `success_criteria`, `instructions`, `is_active`, `created_at`) VALUES
(1, 3, 'pos_order', '{\"scenarios\": [{\"customer_type\": \"walk_in\", \"items\": [\"Coffee\", \"Sandwich\"], \"total\": 12.50}, {\"customer_type\": \"hotel_guest\", \"items\": [\"Breakfast\", \"Juice\"], \"total\": 18.75}]}', '{\"completion_time\": 300, \"accuracy\": 100}', 'Complete the order process for each scenario within the time limit and with 100% accuracy.', 1, '2025-10-02 02:51:58');

-- --------------------------------------------------------

--
-- Table structure for table `tutorial_steps`
--

CREATE TABLE `tutorial_steps` (
  `id` int(11) NOT NULL,
  `tutorial_module_id` int(11) NOT NULL,
  `step_number` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `instruction` text NOT NULL,
  `target_element` varchar(200) DEFAULT NULL COMMENT 'CSS selector for highlighting',
  `action_type` enum('click','input','select','navigate','simulate') NOT NULL,
  `expected_result` text DEFAULT NULL,
  `is_interactive` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tutorial_steps`
--

INSERT INTO `tutorial_steps` (`id`, `tutorial_module_id`, `step_number`, `title`, `description`, `instruction`, `target_element`, `action_type`, `expected_result`, `is_interactive`, `created_at`) VALUES
(1, 1, 1, 'Welcome to POS System', 'Introduction to point of sale operations', 'Click on the \"New Order\" button to start a new transaction', '#new-order-btn', 'click', NULL, 1, '2025-10-01 17:14:59'),
(2, 1, 2, 'Add Items to Order', 'Learn how to add menu items to an order', 'Select items from the menu by clicking on them', '.menu-item', 'click', NULL, 1, '2025-10-01 17:14:59'),
(3, 1, 3, 'Process Payment', 'Complete the transaction with payment processing', 'Click the \"Process Payment\" button to finalize the order', '#process-payment-btn', 'click', NULL, 1, '2025-10-01 17:14:59'),
(4, 1, 4, 'Generate Receipt', 'Print or display the transaction receipt', 'Click \"Print Receipt\" to complete the transaction', '#print-receipt-btn', 'click', NULL, 1, '2025-10-01 17:14:59'),
(5, 2, 1, 'Inventory Dashboard Overview', 'Understanding the inventory management interface', 'Navigate to the Inventory Dashboard to view current stock levels', '#inventory-dashboard', 'navigate', NULL, 1, '2025-10-01 17:14:59'),
(6, 2, 2, 'Check Stock Levels', 'Learn how to monitor inventory quantities', 'Click on any item to view detailed stock information', '.inventory-item', 'click', NULL, 1, '2025-10-01 17:14:59'),
(7, 2, 3, 'Add New Inventory', 'Add new items to the inventory system', 'Click \"Add Item\" to create a new inventory entry', '#add-item-btn', 'click', NULL, 1, '2025-10-01 17:14:59'),
(8, 2, 4, 'Update Stock Quantities', 'Modify existing inventory quantities', 'Use the \"Update Stock\" feature to adjust quantities', '#update-stock-btn', 'click', NULL, 1, '2025-10-01 17:14:59'),
(9, 3, 1, 'Guest Check-in Process', 'Learn the standard guest check-in procedure', 'Click \"Check-in Guest\" to start the check-in process', '#checkin-guest-btn', 'click', NULL, 1, '2025-10-01 17:14:59'),
(10, 3, 2, 'Room Assignment', 'Assign rooms to arriving guests', 'Select an available room from the room assignment interface', '.room-option', 'click', NULL, 1, '2025-10-01 17:14:59'),
(11, 3, 3, 'Guest Information Management', 'Update and manage guest profiles', 'Click on guest details to edit their information', '#guest-details', 'click', NULL, 1, '2025-10-01 17:14:59'),
(12, 3, 4, 'Check-out Process', 'Complete guest departure procedures', 'Click \"Check-out\" to finalize the guest stay', '#checkout-btn', 'click', NULL, 1, '2025-10-01 17:14:59');

-- --------------------------------------------------------

--
-- Table structure for table `tutorial_step_content`
--

CREATE TABLE `tutorial_step_content` (
  `id` int(11) NOT NULL,
  `tutorial_step_id` int(11) NOT NULL,
  `content_type` enum('text','image','video','interactive','quiz','simulation','file') NOT NULL,
  `content_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`content_data`)),
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('front_desk','housekeeping','manager','student') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `password`, `email`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Sarah Johnson', 'sarah.johnson', '$2y$10$He.ElOvUr./YkmRGWyM9zuWq3eTPKcQdPbI3vm/foIT/f3dfjkOXG', 'sarah.johnson@grandhotel.com', 'manager', 1, '2025-08-27 10:02:44', '2025-10-07 00:59:35'),
(2, 'Michael Chen', 'michael.chen', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'michael.chen@grandhotel.com', 'front_desk', 1, '2025-08-27 10:02:44', '2025-10-02 03:37:17'),
(3, 'Elena Rodriguez', 'elena.rodriguez', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'elena.rodriguez@grandhotel.com', 'front_desk', 1, '2025-08-27 10:02:44', '2025-10-02 03:37:17'),
(4, 'James Wilson', 'james.wilson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'james.wilson@grandhotel.com', 'housekeeping', 1, '2025-08-27 10:02:44', '2025-10-02 03:37:17'),
(5, 'Lisa Thompson', 'lisa.thompson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lisa.thompson@grandhotel.com', 'housekeeping', 1, '2025-08-27 10:02:44', '2025-10-02 03:37:17'),
(6, 'David Park', 'david.park', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'david.park@grandhotel.com', 'front_desk', 1, '2025-08-27 10:02:44', '2025-10-02 03:37:17'),
(999, 'Demo Student', '', '$2y$10$U7DDB.4.BKAjUhxXiGKnqO1ubiiZ7L6EJV0L1nbzZVjnjoe1/ncrG', 'demo@student.com', 'student', 1, '2025-10-02 01:28:33', '2025-10-02 01:29:06'),
(1000, 'John Student', 'john_student', '$2y$10$elYMolQbgo0qQKtQYRTAAOj3EyEnJiwyNhMVM82GYEqeaibOmHqdy', 'john@student.com', 'student', 1, '2025-10-04 03:03:49', '2025-10-04 03:03:49'),
(1001, 'Jane Learner', 'jane_learner', '$2y$10$QeGPki/r8i9UTjMAmOj/MuF671cV3V.lpshIizOsLnSZYXQOUOSPq', 'jane@student.com', 'student', 1, '2025-10-04 03:03:49', '2025-10-04 03:03:49'),
(1002, 'john doe', 'john_doe', '$2y$10$sNGQIJRor6meSeMeuP2tLuy8mXBygaJlv.U84Ci46iktAQMkG0upm', 'johndoe@gmail.com', 'student', 1, '2025-10-04 03:30:39', '2025-10-04 03:30:39'),
(1003, 'David Johnson', 'manager1', '$2y$10$hPQm3oCFHkUOX8FEbGQfRu570fjsley2Fkg0UT/LC58s8i1G3sTJO', 'david@hotel.com', 'manager', 1, '2025-10-07 00:58:28', '2025-10-07 00:58:28'),
(1004, 'Emily Chen', 'manager2', '$2y$10$Ufb9Gixflu9iZyKjnB3TRuHRFaYE8qBB2L8vpJ62MIU/JyULyn5.2', 'emily@hotel.com', 'manager', 1, '2025-10-07 00:58:28', '2025-10-07 00:58:28'),
(1005, 'John Smith', 'frontdesk1', '$2y$10$rQUHpdIH9W3HJRGuCUnlhuqM1yiPGo.KGQtuuvjfpBAmIyAFqYL4W', 'john@hotel.com', 'front_desk', 1, '2025-10-07 00:58:28', '2025-10-07 00:58:28'),
(1006, 'Sarah Wilson', 'frontdesk2', '$2y$10$w7t74t2aHZiDREHHh6TJwezo3K.K9yocaKgroGsvsp9/L8lA.504i', 'sarah@hotel.com', 'front_desk', 1, '2025-10-07 00:58:28', '2025-10-07 00:58:28'),
(1007, 'Maria Garcia', 'housekeeping1', '$2y$10$mqmRXfd97I4FlbvXrQvqwObF1cjyQxwJvR0cvSyTI6syawR/kZOu6', 'maria@hotel.com', 'housekeeping', 1, '2025-10-07 00:58:28', '2025-10-07 00:58:28'),
(1008, 'Carlos Rodriguez', 'housekeeping2', '$2y$10$myCvxm7Osi9VEKnQxgX7w.M2wCIG1QPY30dnH0gxwzssttPdm7S.m', 'carlos@hotel.com', 'housekeeping', 1, '2025-10-07 00:58:28', '2025-10-07 00:58:28'),
(1009, 'Student One', 'student1', '$2y$10$LZqBhG61OCVE4N9yiREHEOHG1wXqJYSjR.duMNnam60FSkPP45Z7m', 'student1@demo.com', 'student', 1, '2025-10-07 00:58:28', '2025-10-07 00:58:28'),
(1010, 'Student Two', 'student2', '$2y$10$UwyR6KYBbPfiuaxKAnfeqeYo4wXieTFgCgp74Kn6hMRpsq/ILx8Du', 'student2@demo.com', 'student', 1, '2025-10-07 00:58:28', '2025-10-07 00:58:28'),
(1011, 'Student Three', 'student3', '$2y$10$GvO1FNhmVfONnG4ndxrQhe0R9fER4MVlRlqYSBTSI33qQUK2Sey7a', 'student3@demo.com', 'student', 1, '2025-10-07 00:58:28', '2025-10-07 00:58:28'),
(1012, 'wew', 'wewwew', '$2y$10$r.ZV13RRrWLU0pzFhni0l.yjA5W/UogX9VJCafYbQ5lIpGIx7xfQi', 'das@gmail.com', 'manager', 1, '2025-10-07 09:47:51', '2025-10-07 09:47:51'),
(1013, 'wew', 'yah', '$2y$10$iB.IdA.lzAT0vbcEBqyWG.h7hnbyVZEtw3FJQVL/tTZHIjsnUaebG', 'seaitmis@gmail.com', 'manager', 1, '2025-10-08 00:45:22', '2025-10-08 00:45:22');

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `id` int(11) NOT NULL,
  `voucher_code` varchar(20) NOT NULL,
  `voucher_type` enum('percentage','fixed','free_night','upgrade') NOT NULL,
  `voucher_value` decimal(10,2) NOT NULL,
  `usage_limit` int(11) DEFAULT 1,
  `valid_from` date NOT NULL,
  `valid_until` date NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','used','expired') DEFAULT 'active',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vouchers`
--

INSERT INTO `vouchers` (`id`, `voucher_code`, `voucher_type`, `voucher_value`, `usage_limit`, `valid_from`, `valid_until`, `description`, `status`, `created_by`, `created_at`) VALUES
(1, 'WELCOME2024', 'percentage', 10.00, 100, '2024-01-01', '2024-12-31', 'Welcome discount for new guests', 'active', 3, '2025-08-27 10:02:44'),
(2, 'SUMMER2024', 'fixed', 50.00, 50, '2024-06-01', '2024-08-31', 'Summer promotion discount', 'active', 3, '2025-08-27 10:02:44'),
(3, 'VIP2024', 'percentage', 15.00, 25, '2024-01-01', '2024-12-31', 'VIP guest exclusive discount', 'active', 3, '2025-08-27 10:02:44');

-- --------------------------------------------------------

--
-- Table structure for table `voucher_usage`
--

CREATE TABLE `voucher_usage` (
  `id` int(11) NOT NULL,
  `voucher_id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `used_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `used_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounting_transactions`
--
ALTER TABLE `accounting_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `account_code` (`account_code`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `additional_services`
--
ALTER TABLE `additional_services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `barcode_tracking`
--
ALTER TABLE `barcode_tracking`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `scanned_by` (`scanned_by`);

--
-- Indexes for table `billing`
--
ALTER TABLE `billing`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `guest_id` (`guest_id`);

--
-- Indexes for table `bills`
--
ALTER TABLE `bills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bill_number` (`bill_number`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `bill_items`
--
ALTER TABLE `bill_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bill_id` (`bill_id`);

--
-- Indexes for table `cart_inventory_items`
--
ALTER TABLE `cart_inventory_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cart_item` (`cart_id`,`item_id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `check_ins`
--
ALTER TABLE `check_ins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `checked_in_by` (`checked_in_by`);

--
-- Indexes for table `check_in_records`
--
ALTER TABLE `check_in_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `checked_in_by` (`checked_in_by`),
  ADD KEY `idx_reservation_id` (`reservation_id`),
  ADD KEY `idx_checked_in_at` (`checked_in_at`);

--
-- Indexes for table `cost_analysis_reports`
--
ALTER TABLE `cost_analysis_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_date` (`report_date`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `customer_service_scenarios`
--
ALTER TABLE `customer_service_scenarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `scenario_id` (`scenario_id`);

--
-- Indexes for table `discounts`
--
ALTER TABLE `discounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bill_id` (`bill_id`),
  ADD KEY `applied_by` (`applied_by`);

--
-- Indexes for table `dynamic_module_tags`
--
ALTER TABLE `dynamic_module_tags`
  ADD PRIMARY KEY (`tutorial_module_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `dynamic_training_categories`
--
ALTER TABLE `dynamic_training_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `dynamic_training_content`
--
ALTER TABLE `dynamic_training_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_module_step` (`tutorial_module_id`,`step_number`);

--
-- Indexes for table `dynamic_training_quizzes`
--
ALTER TABLE `dynamic_training_quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `content_id` (`content_id`);

--
-- Indexes for table `dynamic_training_resources`
--
ALTER TABLE `dynamic_training_resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tutorial_module_id` (`tutorial_module_id`),
  ADD KEY `content_id` (`content_id`);

--
-- Indexes for table `dynamic_training_simulations`
--
ALTER TABLE `dynamic_training_simulations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `content_id` (`content_id`);

--
-- Indexes for table `dynamic_training_tags`
--
ALTER TABLE `dynamic_training_tags`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `floors`
--
ALTER TABLE `floors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `floor_number` (`floor_number`);

--
-- Indexes for table `group_bookings`
--
ALTER TABLE `group_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `guests`
--
ALTER TABLE `guests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `guest_feedback`
--
ALTER TABLE `guest_feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `guest_id` (`guest_id`),
  ADD KEY `resolved_by` (`resolved_by`);

--
-- Indexes for table `hotel_floors`
--
ALTER TABLE `hotel_floors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `floor_number` (`floor_number`);

--
-- Indexes for table `hotel_rooms`
--
ALTER TABLE `hotel_rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_number` (`room_number`),
  ADD KEY `floor_id` (`floor_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `housekeeping_carts`
--
ALTER TABLE `housekeeping_carts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cart_number` (`cart_number`),
  ADD KEY `floor_id` (`floor_id`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `housekeeping_tasks`
--
ALTER TABLE `housekeeping_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_alerts`
--
ALTER TABLE `inventory_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `alert_type` (`alert_type`),
  ADD KEY `is_read` (`is_read`),
  ADD KEY `resolved_by` (`resolved_by`);

--
-- Indexes for table `inventory_categories`
--
ALTER TABLE `inventory_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `inventory_integration_log`
--
ALTER TABLE `inventory_integration_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `action` (`action`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `inventory_integration_mapping`
--
ALTER TABLE `inventory_integration_mapping`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `source_mapping` (`source_type`,`source_id`),
  ADD KEY `inventory_item_id` (`inventory_item_id`);

--
-- Indexes for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_inventory_items_name` (`item_name`),
  ADD KEY `idx_inventory_items_category` (`category_id`),
  ADD KEY `idx_inventory_items_stock` (`current_stock`),
  ADD KEY `idx_inventory_items_sku` (`sku`),
  ADD KEY `idx_inventory_items_supplier` (`supplier`),
  ADD KEY `idx_inventory_items_status` (`status`),
  ADD KEY `idx_inventory_items_is_pos` (`is_pos_product`);

--
-- Indexes for table `inventory_requests`
--
ALTER TABLE `inventory_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `request_number` (`request_number`),
  ADD KEY `requested_by` (`requested_by`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `inventory_request_items`
--
ALTER TABLE `inventory_request_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `inventory_suppliers`
--
ALTER TABLE `inventory_suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_training_progress`
--
ALTER TABLE `inventory_training_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_scenario` (`user_id`,`scenario_id`),
  ADD KEY `scenario_id` (`scenario_id`);

--
-- Indexes for table `inventory_training_scenarios`
--
ALTER TABLE `inventory_training_scenarios`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `performed_by` (`performed_by`),
  ADD KEY `inventory_transactions_ibfk_user` (`user_id`),
  ADD KEY `idx_inventory_transactions_item` (`item_id`),
  ADD KEY `idx_inventory_transactions_type` (`transaction_type`),
  ADD KEY `idx_inventory_transactions_date` (`created_at`),
  ADD KEY `idx_inventory_transactions_unit_price` (`unit_price`),
  ADD KEY `idx_inventory_transactions_total_value` (`total_value`);

--
-- Indexes for table `loyalty_points`
--
ALTER TABLE `loyalty_points`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guest_id` (`guest_id`),
  ADD KEY `processed_by` (`processed_by`);

--
-- Indexes for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `reported_by` (`reported_by`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_number` (`payment_number`),
  ADD KEY `bill_id` (`bill_id`),
  ADD KEY `processed_by` (`processed_by`);

--
-- Indexes for table `pos_activity_log`
--
ALTER TABLE `pos_activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `action` (`action`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `pos_categories`
--
ALTER TABLE `pos_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_type` (`service_type`);

--
-- Indexes for table `pos_discounts`
--
ALTER TABLE `pos_discounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pos_inventory`
--
ALTER TABLE `pos_inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category` (`category`),
  ADD KEY `sku` (`sku`);

--
-- Indexes for table `pos_menu_items`
--
ALTER TABLE `pos_menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category` (`category`),
  ADD KEY `active` (`active`),
  ADD KEY `idx_pos_menu_items_category_active` (`category`,`active`);

--
-- Indexes for table `pos_orders`
--
ALTER TABLE `pos_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `table_id` (`table_id`),
  ADD KEY `guest_id` (`guest_id`),
  ADD KEY `status` (`status`),
  ADD KEY `idx_pos_orders_table_status` (`table_id`,`status`);

--
-- Indexes for table `pos_payments`
--
ALTER TABLE `pos_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `payment_method` (`payment_method`),
  ADD KEY `idx_pos_payments_transaction` (`transaction_id`);

--
-- Indexes for table `pos_tables`
--
ALTER TABLE `pos_tables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `table_number` (`table_number`);

--
-- Indexes for table `pos_tax_rates`
--
ALTER TABLE `pos_tax_rates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pos_transactions`
--
ALTER TABLE `pos_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_number` (`transaction_number`),
  ADD KEY `guest_id` (`guest_id`),
  ADD KEY `service_type` (`service_type`),
  ADD KEY `status` (`status`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `idx_pos_transactions_service_status` (`service_type`,`status`),
  ADD KEY `idx_pos_transactions_guest_room` (`guest_id`,`room_number`);

--
-- Indexes for table `problem_scenarios`
--
ALTER TABLE `problem_scenarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `scenario_id` (`scenario_id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `po_number` (`po_number`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `requested_by` (`requested_by`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `po_id` (`po_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `quality_checks`
--
ALTER TABLE `quality_checks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `inspector_id` (`inspector_id`);

--
-- Indexes for table `question_options`
--
ALTER TABLE `question_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `reorder_rules`
--
ALTER TABLE `reorder_rules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `item_id` (`item_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reservation_number` (`reservation_number`),
  ADD KEY `guest_id` (`guest_id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `checked_in_by` (`checked_in_by`),
  ADD KEY `checked_out_by` (`checked_out_by`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_number` (`room_number`);

--
-- Indexes for table `room_inventory`
--
ALTER TABLE `room_inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_item` (`room_id`,`item_id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `idx_room_inventory_room` (`room_id`),
  ADD KEY `idx_room_inventory_item` (`item_id`),
  ADD KEY `idx_room_inventory_stock` (`quantity_current`);

--
-- Indexes for table `room_inventory_items`
--
ALTER TABLE `room_inventory_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_item` (`room_id`,`item_id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `room_inventory_transactions`
--
ALTER TABLE `room_inventory_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `transaction_type` (`transaction_type`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `scenario_questions`
--
ALTER TABLE `scenario_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `scenario_id` (`scenario_id`);

--
-- Indexes for table `service_charges`
--
ALTER TABLE `service_charges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `charged_by` (`charged_by`);

--
-- Indexes for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `guest_id` (`guest_id`),
  ADD KEY `requested_by` (`requested_by`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_service_type` (`service_type`),
  ADD KEY `idx_requested_at` (`requested_at`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `supply_requests`
--
ALTER TABLE `supply_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `requested_by` (`requested_by`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `training_attempts`
--
ALTER TABLE `training_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `training_categories`
--
ALTER TABLE `training_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `training_certificates`
--
ALTER TABLE `training_certificates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `training_prerequisites`
--
ALTER TABLE `training_prerequisites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tutorial_module_id` (`tutorial_module_id`),
  ADD KEY `prerequisite_module_id` (`prerequisite_module_id`);

--
-- Indexes for table `training_resources`
--
ALTER TABLE `training_resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tutorial_module_id` (`tutorial_module_id`),
  ADD KEY `tutorial_step_id` (`tutorial_step_id`);

--
-- Indexes for table `training_scenarios`
--
ALTER TABLE `training_scenarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `scenario_id` (`scenario_id`);

--
-- Indexes for table `training_tags`
--
ALTER TABLE `training_tags`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tutorial_analytics`
--
ALTER TABLE `tutorial_analytics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `step_id` (`step_id`),
  ADD KEY `idx_tutorial_analytics_user_id` (`user_id`),
  ADD KEY `idx_tutorial_analytics_module_id` (`tutorial_module_id`),
  ADD KEY `idx_tutorial_analytics_action_type` (`action_type`),
  ADD KEY `idx_tutorial_analytics_created_at` (`created_at`);

--
-- Indexes for table `tutorial_assessments`
--
ALTER TABLE `tutorial_assessments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tutorial_step_id` (`tutorial_step_id`);

--
-- Indexes for table `tutorial_modules`
--
ALTER TABLE `tutorial_modules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `tutorial_module_tags`
--
ALTER TABLE `tutorial_module_tags`
  ADD PRIMARY KEY (`tutorial_module_id`,`training_tag_id`),
  ADD KEY `training_tag_id` (`training_tag_id`);

--
-- Indexes for table `tutorial_progress`
--
ALTER TABLE `tutorial_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_tutorial` (`user_id`,`tutorial_module_id`),
  ADD KEY `idx_tutorial_progress_user_id` (`user_id`),
  ADD KEY `idx_tutorial_progress_module_id` (`tutorial_module_id`),
  ADD KEY `idx_tutorial_progress_status` (`status`);

--
-- Indexes for table `tutorial_quizzes`
--
ALTER TABLE `tutorial_quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tutorial_step_id` (`tutorial_step_id`);

--
-- Indexes for table `tutorial_simulations`
--
ALTER TABLE `tutorial_simulations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tutorial_step_id` (`tutorial_step_id`);

--
-- Indexes for table `tutorial_steps`
--
ALTER TABLE `tutorial_steps`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_module_step` (`tutorial_module_id`,`step_number`),
  ADD KEY `idx_tutorial_steps_module_id` (`tutorial_module_id`),
  ADD KEY `idx_tutorial_steps_step_number` (`tutorial_module_id`,`step_number`);

--
-- Indexes for table `tutorial_step_content`
--
ALTER TABLE `tutorial_step_content`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tutorial_step_id` (`tutorial_step_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `voucher_code` (`voucher_code`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `voucher_usage`
--
ALTER TABLE `voucher_usage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `voucher_id` (`voucher_id`),
  ADD KEY `bill_id` (`bill_id`),
  ADD KEY `used_by` (`used_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounting_transactions`
--
ALTER TABLE `accounting_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT for table `additional_services`
--
ALTER TABLE `additional_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `barcode_tracking`
--
ALTER TABLE `barcode_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `billing`
--
ALTER TABLE `billing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bills`
--
ALTER TABLE `bills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `bill_items`
--
ALTER TABLE `bill_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_inventory_items`
--
ALTER TABLE `cart_inventory_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `check_ins`
--
ALTER TABLE `check_ins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `check_in_records`
--
ALTER TABLE `check_in_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cost_analysis_reports`
--
ALTER TABLE `cost_analysis_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_service_scenarios`
--
ALTER TABLE `customer_service_scenarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `discounts`
--
ALTER TABLE `discounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dynamic_training_categories`
--
ALTER TABLE `dynamic_training_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `dynamic_training_content`
--
ALTER TABLE `dynamic_training_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `dynamic_training_quizzes`
--
ALTER TABLE `dynamic_training_quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `dynamic_training_resources`
--
ALTER TABLE `dynamic_training_resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `dynamic_training_simulations`
--
ALTER TABLE `dynamic_training_simulations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `dynamic_training_tags`
--
ALTER TABLE `dynamic_training_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `floors`
--
ALTER TABLE `floors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `group_bookings`
--
ALTER TABLE `group_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guests`
--
ALTER TABLE `guests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `guest_feedback`
--
ALTER TABLE `guest_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `hotel_floors`
--
ALTER TABLE `hotel_floors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `hotel_rooms`
--
ALTER TABLE `hotel_rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `housekeeping_carts`
--
ALTER TABLE `housekeeping_carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `housekeeping_tasks`
--
ALTER TABLE `housekeeping_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `inventory_alerts`
--
ALTER TABLE `inventory_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_categories`
--
ALTER TABLE `inventory_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `inventory_integration_log`
--
ALTER TABLE `inventory_integration_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_integration_mapping`
--
ALTER TABLE `inventory_integration_mapping`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `inventory_requests`
--
ALTER TABLE `inventory_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_request_items`
--
ALTER TABLE `inventory_request_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_suppliers`
--
ALTER TABLE `inventory_suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_training_progress`
--
ALTER TABLE `inventory_training_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_training_scenarios`
--
ALTER TABLE `inventory_training_scenarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `loyalty_points`
--
ALTER TABLE `loyalty_points`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pos_activity_log`
--
ALTER TABLE `pos_activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=259;

--
-- AUTO_INCREMENT for table `pos_categories`
--
ALTER TABLE `pos_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pos_discounts`
--
ALTER TABLE `pos_discounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pos_inventory`
--
ALTER TABLE `pos_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pos_menu_items`
--
ALTER TABLE `pos_menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `pos_orders`
--
ALTER TABLE `pos_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pos_payments`
--
ALTER TABLE `pos_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pos_tables`
--
ALTER TABLE `pos_tables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `pos_tax_rates`
--
ALTER TABLE `pos_tax_rates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pos_transactions`
--
ALTER TABLE `pos_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `problem_scenarios`
--
ALTER TABLE `problem_scenarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quality_checks`
--
ALTER TABLE `quality_checks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_options`
--
ALTER TABLE `question_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=241;

--
-- AUTO_INCREMENT for table `reorder_rules`
--
ALTER TABLE `reorder_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `room_inventory`
--
ALTER TABLE `room_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `room_inventory_items`
--
ALTER TABLE `room_inventory_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `room_inventory_transactions`
--
ALTER TABLE `room_inventory_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `scenario_questions`
--
ALTER TABLE `scenario_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `service_charges`
--
ALTER TABLE `service_charges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `service_requests`
--
ALTER TABLE `service_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `supply_requests`
--
ALTER TABLE `supply_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `training_attempts`
--
ALTER TABLE `training_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `training_categories`
--
ALTER TABLE `training_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `training_certificates`
--
ALTER TABLE `training_certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `training_prerequisites`
--
ALTER TABLE `training_prerequisites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `training_resources`
--
ALTER TABLE `training_resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `training_scenarios`
--
ALTER TABLE `training_scenarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `training_tags`
--
ALTER TABLE `training_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tutorial_analytics`
--
ALTER TABLE `tutorial_analytics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tutorial_assessments`
--
ALTER TABLE `tutorial_assessments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tutorial_modules`
--
ALTER TABLE `tutorial_modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tutorial_progress`
--
ALTER TABLE `tutorial_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tutorial_quizzes`
--
ALTER TABLE `tutorial_quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tutorial_simulations`
--
ALTER TABLE `tutorial_simulations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tutorial_steps`
--
ALTER TABLE `tutorial_steps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tutorial_step_content`
--
ALTER TABLE `tutorial_step_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1017;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `voucher_usage`
--
ALTER TABLE `voucher_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `barcode_tracking`
--
ALTER TABLE `barcode_tracking`
  ADD CONSTRAINT `barcode_tracking_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `barcode_tracking_ibfk_2` FOREIGN KEY (`scanned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `billing`
--
ALTER TABLE `billing`
  ADD CONSTRAINT `billing_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`),
  ADD CONSTRAINT `billing_ibfk_2` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`);

--
-- Constraints for table `bills`
--
ALTER TABLE `bills`
  ADD CONSTRAINT `bills_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`),
  ADD CONSTRAINT `bills_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `bill_items`
--
ALTER TABLE `bill_items`
  ADD CONSTRAINT `bill_items_ibfk_1` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`);

--
-- Constraints for table `cart_inventory_items`
--
ALTER TABLE `cart_inventory_items`
  ADD CONSTRAINT `cart_inventory_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `housekeeping_carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_inventory_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `check_ins`
--
ALTER TABLE `check_ins`
  ADD CONSTRAINT `check_ins_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`),
  ADD CONSTRAINT `check_ins_ibfk_2` FOREIGN KEY (`checked_in_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `check_in_records`
--
ALTER TABLE `check_in_records`
  ADD CONSTRAINT `check_in_records_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `check_in_records_ibfk_2` FOREIGN KEY (`checked_in_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cost_analysis_reports`
--
ALTER TABLE `cost_analysis_reports`
  ADD CONSTRAINT `cost_analysis_reports_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `inventory_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `discounts`
--
ALTER TABLE `discounts`
  ADD CONSTRAINT `discounts_ibfk_1` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`),
  ADD CONSTRAINT `discounts_ibfk_2` FOREIGN KEY (`applied_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `dynamic_module_tags`
--
ALTER TABLE `dynamic_module_tags`
  ADD CONSTRAINT `dynamic_module_tags_ibfk_1` FOREIGN KEY (`tutorial_module_id`) REFERENCES `tutorial_modules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dynamic_module_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `dynamic_training_tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dynamic_training_categories`
--
ALTER TABLE `dynamic_training_categories`
  ADD CONSTRAINT `dynamic_training_categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `dynamic_training_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `dynamic_training_content`
--
ALTER TABLE `dynamic_training_content`
  ADD CONSTRAINT `dynamic_training_content_ibfk_1` FOREIGN KEY (`tutorial_module_id`) REFERENCES `tutorial_modules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dynamic_training_quizzes`
--
ALTER TABLE `dynamic_training_quizzes`
  ADD CONSTRAINT `dynamic_training_quizzes_ibfk_1` FOREIGN KEY (`content_id`) REFERENCES `dynamic_training_content` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dynamic_training_resources`
--
ALTER TABLE `dynamic_training_resources`
  ADD CONSTRAINT `dynamic_training_resources_ibfk_1` FOREIGN KEY (`tutorial_module_id`) REFERENCES `tutorial_modules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dynamic_training_resources_ibfk_2` FOREIGN KEY (`content_id`) REFERENCES `dynamic_training_content` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dynamic_training_simulations`
--
ALTER TABLE `dynamic_training_simulations`
  ADD CONSTRAINT `dynamic_training_simulations_ibfk_1` FOREIGN KEY (`content_id`) REFERENCES `dynamic_training_content` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `group_bookings`
--
ALTER TABLE `group_bookings`
  ADD CONSTRAINT `group_bookings_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`),
  ADD CONSTRAINT `group_bookings_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `guest_feedback`
--
ALTER TABLE `guest_feedback`
  ADD CONSTRAINT `guest_feedback_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`),
  ADD CONSTRAINT `guest_feedback_ibfk_2` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`),
  ADD CONSTRAINT `guest_feedback_ibfk_3` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `hotel_rooms`
--
ALTER TABLE `hotel_rooms`
  ADD CONSTRAINT `hotel_rooms_ibfk_1` FOREIGN KEY (`floor_id`) REFERENCES `hotel_floors` (`id`);

--
-- Constraints for table `housekeeping_carts`
--
ALTER TABLE `housekeeping_carts`
  ADD CONSTRAINT `housekeeping_carts_ibfk_1` FOREIGN KEY (`floor_id`) REFERENCES `hotel_floors` (`id`),
  ADD CONSTRAINT `housekeeping_carts_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `housekeeping_tasks`
--
ALTER TABLE `housekeeping_tasks`
  ADD CONSTRAINT `housekeeping_tasks_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`),
  ADD CONSTRAINT `housekeeping_tasks_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `housekeeping_tasks_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `inventory_alerts`
--
ALTER TABLE `inventory_alerts`
  ADD CONSTRAINT `inventory_alerts_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_alerts_ibfk_2` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inventory_integration_log`
--
ALTER TABLE `inventory_integration_log`
  ADD CONSTRAINT `inventory_integration_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_integration_mapping`
--
ALTER TABLE `inventory_integration_mapping`
  ADD CONSTRAINT `inventory_integration_mapping_ibfk_1` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD CONSTRAINT `inventory_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `inventory_categories` (`id`);

--
-- Constraints for table `inventory_requests`
--
ALTER TABLE `inventory_requests`
  ADD CONSTRAINT `inventory_requests_ibfk_1` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `inventory_requests_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inventory_request_items`
--
ALTER TABLE `inventory_request_items`
  ADD CONSTRAINT `inventory_request_items_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `inventory_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_request_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`);

--
-- Constraints for table `inventory_training_progress`
--
ALTER TABLE `inventory_training_progress`
  ADD CONSTRAINT `inventory_training_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_training_progress_ibfk_2` FOREIGN KEY (`scenario_id`) REFERENCES `inventory_training_scenarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD CONSTRAINT `inventory_transactions_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`),
  ADD CONSTRAINT `inventory_transactions_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `inventory_transactions_ibfk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `loyalty_points`
--
ALTER TABLE `loyalty_points`
  ADD CONSTRAINT `loyalty_points_ibfk_1` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`),
  ADD CONSTRAINT `loyalty_points_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD CONSTRAINT `maintenance_requests_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`),
  ADD CONSTRAINT `maintenance_requests_ibfk_2` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `maintenance_requests_ibfk_3` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `inventory_suppliers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_orders_ibfk_2` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `purchase_orders_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD CONSTRAINT `purchase_order_items_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_order_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`);

--
-- Constraints for table `quality_checks`
--
ALTER TABLE `quality_checks`
  ADD CONSTRAINT `quality_checks_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quality_checks_ibfk_2` FOREIGN KEY (`inspector_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `question_options`
--
ALTER TABLE `question_options`
  ADD CONSTRAINT `question_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `scenario_questions` (`id`);

--
-- Constraints for table `reorder_rules`
--
ALTER TABLE `reorder_rules`
  ADD CONSTRAINT `reorder_rules_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reorder_rules_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `inventory_suppliers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`),
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`),
  ADD CONSTRAINT `reservations_ibfk_3` FOREIGN KEY (`checked_in_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reservations_ibfk_4` FOREIGN KEY (`checked_out_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reservations_ibfk_5` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `room_inventory`
--
ALTER TABLE `room_inventory`
  ADD CONSTRAINT `room_inventory_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `room_inventory_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `room_inventory_items`
--
ALTER TABLE `room_inventory_items`
  ADD CONSTRAINT `room_inventory_items_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `hotel_rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `room_inventory_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `room_inventory_transactions`
--
ALTER TABLE `room_inventory_transactions`
  ADD CONSTRAINT `room_inventory_transactions_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `hotel_rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `room_inventory_transactions_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `room_inventory_transactions_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `scenario_questions`
--
ALTER TABLE `scenario_questions`
  ADD CONSTRAINT `scenario_questions_ibfk_1` FOREIGN KEY (`scenario_id`) REFERENCES `training_scenarios` (`id`);

--
-- Constraints for table `service_charges`
--
ALTER TABLE `service_charges`
  ADD CONSTRAINT `service_charges_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`),
  ADD CONSTRAINT `service_charges_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `additional_services` (`id`),
  ADD CONSTRAINT `service_charges_ibfk_3` FOREIGN KEY (`charged_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD CONSTRAINT `service_requests_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_requests_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_requests_ibfk_3` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_requests_ibfk_4` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_requests_ibfk_5` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `supply_requests`
--
ALTER TABLE `supply_requests`
  ADD CONSTRAINT `supply_requests_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `supply_requests_ibfk_2` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `supply_requests_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `training_attempts`
--
ALTER TABLE `training_attempts`
  ADD CONSTRAINT `training_attempts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `training_categories`
--
ALTER TABLE `training_categories`
  ADD CONSTRAINT `training_categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `training_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `training_certificates`
--
ALTER TABLE `training_certificates`
  ADD CONSTRAINT `training_certificates_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `training_prerequisites`
--
ALTER TABLE `training_prerequisites`
  ADD CONSTRAINT `training_prerequisites_ibfk_1` FOREIGN KEY (`tutorial_module_id`) REFERENCES `tutorial_modules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `training_prerequisites_ibfk_2` FOREIGN KEY (`prerequisite_module_id`) REFERENCES `tutorial_modules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `training_resources`
--
ALTER TABLE `training_resources`
  ADD CONSTRAINT `training_resources_ibfk_1` FOREIGN KEY (`tutorial_module_id`) REFERENCES `tutorial_modules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `training_resources_ibfk_2` FOREIGN KEY (`tutorial_step_id`) REFERENCES `tutorial_steps` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tutorial_analytics`
--
ALTER TABLE `tutorial_analytics`
  ADD CONSTRAINT `tutorial_analytics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tutorial_analytics_ibfk_2` FOREIGN KEY (`tutorial_module_id`) REFERENCES `tutorial_modules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tutorial_analytics_ibfk_3` FOREIGN KEY (`step_id`) REFERENCES `tutorial_steps` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tutorial_assessments`
--
ALTER TABLE `tutorial_assessments`
  ADD CONSTRAINT `tutorial_assessments_ibfk_1` FOREIGN KEY (`tutorial_step_id`) REFERENCES `tutorial_steps` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tutorial_modules`
--
ALTER TABLE `tutorial_modules`
  ADD CONSTRAINT `tutorial_modules_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `training_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tutorial_module_tags`
--
ALTER TABLE `tutorial_module_tags`
  ADD CONSTRAINT `tutorial_module_tags_ibfk_1` FOREIGN KEY (`tutorial_module_id`) REFERENCES `tutorial_modules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tutorial_module_tags_ibfk_2` FOREIGN KEY (`training_tag_id`) REFERENCES `training_tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tutorial_progress`
--
ALTER TABLE `tutorial_progress`
  ADD CONSTRAINT `tutorial_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tutorial_progress_ibfk_2` FOREIGN KEY (`tutorial_module_id`) REFERENCES `tutorial_modules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tutorial_quizzes`
--
ALTER TABLE `tutorial_quizzes`
  ADD CONSTRAINT `tutorial_quizzes_ibfk_1` FOREIGN KEY (`tutorial_step_id`) REFERENCES `tutorial_steps` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tutorial_simulations`
--
ALTER TABLE `tutorial_simulations`
  ADD CONSTRAINT `tutorial_simulations_ibfk_1` FOREIGN KEY (`tutorial_step_id`) REFERENCES `tutorial_steps` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tutorial_steps`
--
ALTER TABLE `tutorial_steps`
  ADD CONSTRAINT `tutorial_steps_ibfk_1` FOREIGN KEY (`tutorial_module_id`) REFERENCES `tutorial_modules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tutorial_step_content`
--
ALTER TABLE `tutorial_step_content`
  ADD CONSTRAINT `tutorial_step_content_ibfk_1` FOREIGN KEY (`tutorial_step_id`) REFERENCES `tutorial_steps` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD CONSTRAINT `vouchers_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `voucher_usage`
--
ALTER TABLE `voucher_usage`
  ADD CONSTRAINT `voucher_usage_ibfk_1` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`),
  ADD CONSTRAINT `voucher_usage_ibfk_2` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`),
  ADD CONSTRAINT `voucher_usage_ibfk_3` FOREIGN KEY (`used_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
