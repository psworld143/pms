-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 26, 2025 at 10:05 AM
-- Server version: 10.11.14-MariaDB
-- PHP Version: 8.0.30

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
(3, 1072, 'feedback_submitted', 'Guest feedback submitted', '172.16.0.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-21 00:19:33'),
(4, 1075, 'reservation_checked_out', 'Guest checked out - Reservation ID: 2', '10.0.0.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-21 13:36:16'),
(5, 1072, 'guest_updated', 'Guest profile updated - Guest ID: 51', '172.16.0.13', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-21 14:46:55'),
(6, 1073, 'inventory_updated', 'Inventory updated - Item: Shampoo', '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-21 09:03:50'),
(7, 1073, 'bill_updated', 'Bill updated', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-21 07:47:08'),
(8, 1075, 'failed_login', 'Failed login attempt', '10.0.0.50', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-21 09:30:10'),
(9, 1073, 'stock_adjusted', 'Stock level adjusted', '172.16.0.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-21 09:39:29'),
(10, 1072, 'room_maintenance', 'Room maintenance requested - Room: 753', '172.16.0.11', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-21 12:17:41'),
(11, 1073, 'reservation_cancelled', 'Reservation cancelled - Reservation ID: 57', '172.16.0.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-21 05:29:23'),
(12, 1075, 'user_logout', 'User logged out of the system', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-21 14:48:42'),
(13, 1074, 'failed_login', 'Failed login attempt', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-21 08:46:04'),
(14, 1074, 'permission_changed', 'User permissions changed', '172.16.0.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-21 09:47:37'),
(15, 1072, 'system_backup', 'System backup performed', '192.168.1.100', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-21 07:38:43'),
(16, 1072, 'payment_processed', 'Payment processed - Amount: $129.00', '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-21 12:03:45'),
(17, 1076, 'guest_created', 'New guest profile created - Guest ID: 4', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-21 07:13:48'),
(18, 1071, 'reservation_created', 'New reservation created - Reservation ID: 129', '10.0.0.50', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-21 02:35:53'),
(19, 1076, 'data_exported', 'Data exported', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-21 03:25:33'),
(20, 1073, 'purchase_order_created', 'Purchase order created', '10.0.0.50', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-21 06:51:15'),
(21, 1074, 'complaint_handled', 'Guest complaint handled', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-21 14:09:39'),
(22, 1074, 'guest_updated', 'Guest profile updated - Guest ID: 78', '172.16.0.10', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-21 09:22:26'),
(23, 1074, 'complaint_handled', 'Guest complaint handled', '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-21 08:55:29'),
(24, 1074, 'purchase_order_created', 'Purchase order created', '172.16.0.12', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-21 03:53:25'),
(25, 1076, 'room_assigned', 'Room assigned to guest - Room: 336', '10.0.0.51', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-21 03:26:45'),
(26, 1071, 'stock_adjusted', 'Stock level adjusted', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-20 22:00:56'),
(27, 1072, 'complaint_handled', 'Guest complaint handled', '172.16.0.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-21 10:41:00'),
(28, 1076, 'user_login', 'User logged into the system', '172.16.0.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-22 00:08:51'),
(29, 1075, 'permission_changed', 'User permissions changed', '172.16.0.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-21 23:25:17'),
(30, 1075, 'room_cleaned', 'Room marked as cleaned - Room: 596', '172.16.0.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-22 01:31:31'),
(31, 1076, 'bill_updated', 'Bill updated', '10.0.0.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-22 04:37:34'),
(32, 1074, 'room_cleaned', 'Room marked as cleaned - Room: 713', '10.0.0.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-22 05:01:47'),
(33, 1073, 'reservation_cancelled', 'Reservation cancelled - Reservation ID: 148', '172.16.0.10', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-22 14:06:06'),
(34, 1076, 'room_assigned', 'Room assigned to guest - Room: 215', '172.16.0.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-21 22:50:37'),
(35, 1075, 'complaint_handled', 'Guest complaint handled', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-21 22:57:40'),
(36, 1072, 'bill_updated', 'Bill updated', '172.16.0.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-22 02:09:58'),
(37, 1071, 'guest_deleted', 'Guest profile deleted - Guest ID: 39', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-23 04:05:31'),
(38, 1073, 'security_alert', 'Security alert triggered', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-23 10:56:15'),
(39, 1071, 'invoice_created', 'Invoice created', '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-23 09:46:26'),
(40, 1071, 'payment_processed', 'Payment processed - Amount: $72.00', '192.168.1.100', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-23 14:37:56'),
(41, 1075, 'user_login', 'User logged into the system', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-23 01:24:58'),
(42, 1071, 'room_assigned', 'Room assigned to guest - Room: 965', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-23 06:20:22'),
(43, 1073, 'guest_updated', 'Guest profile updated - Guest ID: 34', '172.16.0.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-22 22:04:22'),
(44, 1072, 'system_backup', 'System backup performed', '10.0.0.52', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-23 11:01:59'),
(45, 1076, 'user_login', 'User logged into the system', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-23 06:46:15'),
(46, 1074, 'complaint_handled', 'Guest complaint handled', '10.0.0.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-23 09:33:54'),
(47, 1073, 'reservation_created', 'New reservation created - Reservation ID: 176', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-23 10:32:11'),
(48, 1075, 'permission_changed', 'User permissions changed', '172.16.0.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-23 10:42:04'),
(49, 1074, 'report_generated', 'Report generated', '10.0.0.53', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-22 23:28:53'),
(50, 1075, 'reservation_created', 'New reservation created - Reservation ID: 149', '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-23 10:48:09'),
(51, 1075, 'purchase_order_created', 'Purchase order created', '172.16.0.12', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-23 09:19:00'),
(52, 1071, 'reservation_cancelled', 'Reservation cancelled - Reservation ID: 181', '172.16.0.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-23 13:09:52'),
(53, 1071, 'invoice_created', 'Invoice created', '10.0.0.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-23 04:58:58'),
(54, 1076, 'report_generated', 'Report generated', '172.16.0.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-23 13:01:16'),
(55, 1075, 'user_logout', 'User logged out of the system', '10.0.0.50', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-24 03:57:49'),
(56, 1074, 'room_cleaned', 'Room marked as cleaned - Room: 342', '172.16.0.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-24 09:06:21'),
(57, 1075, 'failed_login', 'Failed login attempt', '172.16.0.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-24 06:58:18'),
(58, 1076, 'feedback_reviewed', 'Feedback reviewed by staff', '10.0.0.53', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-24 08:16:45'),
(59, 1072, 'security_alert', 'Security alert triggered', '192.168.1.103', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-24 00:28:48'),
(60, 1071, 'room_status_changed', 'Room status changed - Room: 495', '172.16.0.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-24 04:10:52'),
(61, 1076, 'guest_viewed', 'Guest profile viewed - Guest ID: 15', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-24 13:39:36'),
(62, 1072, 'complaint_handled', 'Guest complaint handled', '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-24 03:42:46'),
(63, 1076, 'reservation_created', 'New reservation created - Reservation ID: 172', '10.0.0.52', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-23 22:13:25'),
(64, 1073, 'guest_viewed', 'Guest profile viewed - Guest ID: 68', '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-24 03:47:13'),
(65, 1073, 'feedback_submitted', 'Guest feedback submitted', '172.16.0.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-24 02:52:09'),
(66, 1075, 'room_status_changed', 'Room status changed - Room: 810', '172.16.0.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-24 05:42:05'),
(67, 1074, 'room_cleaned', 'Room marked as cleaned - Room: 633', '10.0.0.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-24 00:47:31'),
(68, 1075, 'analytics_viewed', 'Analytics dashboard viewed', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-24 01:59:52'),
(69, 1076, 'room_status_changed', 'Room status changed - Room: 874', '10.0.0.52', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-24 08:25:53'),
(70, 1071, 'room_maintenance', 'Room maintenance requested - Room: 186', '10.0.0.53', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-23 22:30:21'),
(71, 1072, 'permission_changed', 'User permissions changed', '172.16.0.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-24 06:29:51'),
(72, 1074, 'security_alert', 'Security alert triggered', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-25 09:00:20'),
(73, 1076, 'room_cleaned', 'Room marked as cleaned - Room: 220', '172.16.0.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-25 13:02:39'),
(74, 1076, 'bill_updated', 'Bill updated', '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-25 09:57:58'),
(75, 1074, 'report_generated', 'Report generated', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-24 23:59:57'),
(76, 1071, 'analytics_viewed', 'Analytics dashboard viewed', '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-24 23:21:34'),
(77, 1074, 'reservation_cancelled', 'Reservation cancelled - Reservation ID: 178', '10.0.0.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-25 09:46:19'),
(78, 1074, 'room_cleaned', 'Room marked as cleaned - Room: 208', '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-25 00:26:58'),
(79, 1072, 'room_assigned', 'Room assigned to guest - Room: 587', '192.168.1.103', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-25 07:25:06'),
(80, 1072, 'user_created', 'New user account created', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-25 05:15:56'),
(81, 1072, 'guest_created', 'New guest profile created - Guest ID: 83', '192.168.1.103', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-25 10:36:35'),
(82, 1074, 'guest_created', 'New guest profile created - Guest ID: 59', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-25 08:14:46'),
(83, 1074, 'guest_updated', 'Guest profile updated - Guest ID: 74', '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-25 00:55:41'),
(84, 1075, 'payment_processed', 'Payment processed - Amount: $218.00', '10.0.0.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-25 11:39:54'),
(85, 1075, 'bill_updated', 'Bill updated', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-25 05:53:26'),
(86, 1071, 'guest_updated', 'Guest profile updated - Guest ID: 47', '172.16.0.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-25 04:37:11'),
(87, 1073, 'feedback_reviewed', 'Feedback reviewed by staff', '172.16.0.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-25 02:18:56'),
(88, 1076, 'user_created', 'New user account created', '172.16.0.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-25 22:54:09'),
(89, 1074, 'reservation_cancelled', 'Reservation cancelled - Reservation ID: 60', '10.0.0.52', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-26 10:14:59'),
(90, 1074, 'complaint_handled', 'Guest complaint handled', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-25 22:04:05'),
(91, 1072, 'reservation_cancelled', 'Reservation cancelled - Reservation ID: 144', '172.16.0.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-26 11:25:02'),
(92, 1074, 'reservation_checked_in', 'Guest checked in - Reservation ID: 122', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-25 23:33:41'),
(93, 1074, 'analytics_viewed', 'Analytics dashboard viewed', '172.16.0.10', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-26 06:27:37'),
(94, 1075, 'feedback_reviewed', 'Feedback reviewed by staff', '172.16.0.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-26 13:51:35'),
(95, 1076, 'guest_viewed', 'Guest profile viewed - Guest ID: 64', '172.16.0.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-26 05:00:13'),
(96, 1075, 'analytics_viewed', 'Analytics dashboard viewed', '10.0.0.52', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-26 02:44:47'),
(97, 1075, 'reservation_checked_in', 'Guest checked in - Reservation ID: 78', '10.0.0.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-26 09:56:35'),
(98, 1074, 'system_backup', 'System backup performed', '172.16.0.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-25 22:39:16'),
(99, 1071, 'user_login', 'User logged into the system', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-26 06:37:28'),
(100, 1071, 'invoice_created', 'Invoice created', '172.16.0.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-26 00:33:30'),
(101, 1071, 'purchase_order_created', 'Purchase order created', '172.16.0.10', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-26 04:28:18'),
(102, 1073, 'failed_login', 'Failed login attempt', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-25 22:22:49'),
(103, 1073, 'room_status_changed', 'Room status changed - Room: 190', '172.16.0.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-26 04:57:33'),
(104, 1074, 'guest_deleted', 'Guest profile deleted - Guest ID: 59', '172.16.0.10', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-25 22:51:55'),
(105, 1072, 'supplier_added', 'New supplier added', '172.16.0.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-26 07:54:10'),
(106, 1072, 'payment_processed', 'Payment processed - Amount: $440.00', '172.16.0.13', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-26 05:03:54'),
(107, 1075, 'system_backup', 'System backup performed', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-26 04:40:18'),
(108, 1076, 'feedback_reviewed', 'Feedback reviewed by staff', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-26 01:43:03'),
(109, 1076, 'permission_changed', 'User permissions changed', '10.0.0.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-26 14:28:44'),
(110, 1073, 'room_cleaned', 'Room marked as cleaned - Room: 616', '172.16.0.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-26 09:46:24'),
(111, 1076, 'user_logout', 'User logged out of the system', '172.16.0.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-26 11:58:07'),
(112, 1071, 'report_generated', 'Report generated', '172.16.0.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-26 09:20:57'),
(113, 1075, 'payment_processed', 'Payment processed - Amount: $322.00', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-27 07:43:57'),
(114, 1073, 'report_generated', 'Report generated', '10.0.0.50', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-27 11:00:09'),
(115, 1072, 'supplier_added', 'New supplier added', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-27 00:14:19'),
(116, 1076, 'system_backup', 'System backup performed', '172.16.0.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-27 01:34:34'),
(117, 1073, 'permission_changed', 'User permissions changed', '172.16.0.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-27 02:20:31'),
(118, 1075, 'supplier_added', 'New supplier added', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-27 03:42:17'),
(119, 1074, 'bill_updated', 'Bill updated', '172.16.0.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-27 06:24:36'),
(120, 1072, 'report_generated', 'Report generated', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-27 05:45:43'),
(121, 1074, 'failed_login', 'Failed login attempt', '192.168.1.101', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-27 00:54:36'),
(122, 1072, 'room_status_changed', 'Room status changed - Room: 885', '172.16.0.11', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-27 04:35:23'),
(123, 1073, 'user_login', 'User logged into the system', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-26 23:38:00'),
(124, 1072, 'reservation_checked_in', 'Guest checked in - Reservation ID: 118', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-27 09:52:19'),
(125, 1071, 'inventory_updated', 'Inventory updated - Item: Shampoo', '172.16.0.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-27 01:36:17'),
(126, 1071, 'reservation_cancelled', 'Reservation cancelled - Reservation ID: 18', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-27 00:42:58'),
(127, 1075, 'guest_viewed', 'Guest profile viewed - Guest ID: 35', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-27 13:49:24'),
(128, 1076, 'reservation_cancelled', 'Reservation cancelled - Reservation ID: 91', '10.0.0.52', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-27 10:04:06'),
(129, 1075, 'user_updated', 'User account updated', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-27 14:54:53'),
(130, 1071, 'reservation_updated', 'Reservation updated - Reservation ID: 107', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-27 07:30:08'),
(131, 1073, 'security_alert', 'Security alert triggered', '10.0.0.53', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-27 13:10:41'),
(132, 1076, 'invoice_created', 'Invoice created', '172.16.0.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-27 02:42:34'),
(133, 1072, 'security_alert', 'Security alert triggered', '172.16.0.13', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-26 23:01:48'),
(134, 1072, 'room_assigned', 'Room assigned to guest - Room: 574', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-27 06:52:34'),
(135, 1072, 'user_created', 'New user account created', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-26 23:18:41'),
(136, 1075, 'room_maintenance', 'Room maintenance requested - Room: 491', '172.16.0.13', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-28 13:24:39'),
(137, 1075, 'report_generated', 'Report generated', '192.168.1.101', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-28 03:33:43'),
(138, 1076, 'bill_updated', 'Bill updated', '192.168.1.100', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-28 04:46:56'),
(139, 1075, 'complaint_handled', 'Guest complaint handled', '10.0.0.52', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-28 00:20:38'),
(140, 1074, 'user_updated', 'User account updated', '10.0.0.53', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-28 14:42:02'),
(141, 1073, 'bill_updated', 'Bill updated', '10.0.0.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-28 01:13:41'),
(142, 1075, 'permission_changed', 'User permissions changed', '172.16.0.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-28 01:55:51'),
(143, 1074, 'refund_processed', 'Refund processed', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-28 06:47:24'),
(144, 1075, 'report_generated', 'Report generated', '172.16.0.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-27 22:57:53'),
(145, 1076, 'system_backup', 'System backup performed', '172.16.0.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-28 11:15:26'),
(146, 1072, 'supplier_added', 'New supplier added', '10.0.0.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-28 04:02:31'),
(147, 1073, 'room_cleaned', 'Room marked as cleaned - Room: 321', '172.16.0.13', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-28 10:57:04'),
(148, 1075, 'report_generated', 'Report generated', '172.16.0.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-28 03:02:06'),
(149, 1076, 'user_login', 'User logged into the system', '172.16.0.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-28 14:54:09'),
(150, 1071, 'user_updated', 'User account updated', '10.0.0.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-28 12:52:14'),
(151, 1072, 'reservation_updated', 'Reservation updated - Reservation ID: 97', '172.16.0.12', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-28 02:16:07'),
(152, 1075, 'feedback_reviewed', 'Feedback reviewed by staff', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-28 04:42:18'),
(153, 1076, 'reservation_cancelled', 'Reservation cancelled - Reservation ID: 163', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-28 13:00:25'),
(154, 1072, 'reservation_cancelled', 'Reservation cancelled - Reservation ID: 143', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-28 11:38:45'),
(155, 1073, 'stock_adjusted', 'Stock level adjusted', '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-29 02:15:07'),
(156, 1074, 'payment_processed', 'Payment processed - Amount: $69.00', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-29 09:12:02'),
(157, 1071, 'invoice_created', 'Invoice created', '10.0.0.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-29 11:38:49'),
(158, 1074, 'data_exported', 'Data exported', '10.0.0.51', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-29 10:42:03'),
(159, 1071, 'feedback_submitted', 'Guest feedback submitted', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-29 06:51:07'),
(160, 1076, 'system_backup', 'System backup performed', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-29 04:03:27'),
(161, 1074, 'system_backup', 'System backup performed', '172.16.0.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-28 22:57:01'),
(162, 1075, 'security_alert', 'Security alert triggered', '10.0.0.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-29 10:32:11'),
(163, 1075, 'stock_adjusted', 'Stock level adjusted', '172.16.0.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-29 00:12:04'),
(164, 1071, 'guest_viewed', 'Guest profile viewed - Guest ID: 95', '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-29 12:33:46'),
(165, 1076, 'room_maintenance', 'Room maintenance requested - Room: 636', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-29 09:34:06'),
(166, 1075, 'stock_adjusted', 'Stock level adjusted', '172.16.0.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-29 10:07:32'),
(167, 1076, 'stock_adjusted', 'Stock level adjusted', '192.168.1.102', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-29 06:38:03'),
(168, 1071, 'feedback_reviewed', 'Feedback reviewed by staff', '172.16.0.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-29 13:44:10'),
(169, 1071, 'room_assigned', 'Room assigned to guest - Room: 350', '10.0.0.53', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-29 03:44:20'),
(170, 1072, 'analytics_viewed', 'Analytics dashboard viewed', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-29 12:45:06'),
(171, 1076, 'bill_updated', 'Bill updated', '10.0.0.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-29 11:48:01'),
(172, 1074, 'user_login', 'User logged into the system', '10.0.0.52', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-29 03:02:12'),
(173, 1075, 'room_cleaned', 'Room marked as cleaned - Room: 898', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-28 22:37:20'),
(174, 1075, 'room_assigned', 'Room assigned to guest - Room: 612', '10.0.0.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-09-29 08:20:21'),
(175, 1074, 'user_created', 'New user account created', '192.168.1.100', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-29 23:51:52'),
(176, 1076, 'reservation_updated', 'Reservation updated - Reservation ID: 10', '192.168.1.101', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-30 14:51:07'),
(177, 1073, 'complaint_handled', 'Guest complaint handled', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-30 03:50:57'),
(178, 1072, 'room_cleaned', 'Room marked as cleaned - Room: 898', '172.16.0.11', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-30 05:55:38'),
(179, 1072, 'feedback_submitted', 'Guest feedback submitted', '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-30 07:31:27'),
(180, 1075, 'complaint_handled', 'Guest complaint handled', '192.168.1.100', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-30 11:33:21'),
(181, 1071, 'security_alert', 'Security alert triggered', '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-30 04:14:39'),
(182, 1075, 'reservation_created', 'New reservation created - Reservation ID: 56', '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-30 13:25:21'),
(183, 1074, 'room_maintenance', 'Room maintenance requested - Room: 141', '10.0.0.50', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-30 00:25:03'),
(184, 1071, 'user_logout', 'User logged out of the system', '172.16.0.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-09-30 13:32:54'),
(185, 1073, 'system_backup', 'System backup performed', '172.16.0.10', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-30 03:21:19'),
(186, 1075, 'user_login', 'User logged into the system', '172.16.0.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-01 13:25:41'),
(187, 1075, 'refund_processed', 'Refund processed', '172.16.0.13', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-01 01:57:09'),
(188, 1072, 'guest_updated', 'Guest profile updated - Guest ID: 97', '10.0.0.51', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-01 13:31:17'),
(189, 1074, 'reservation_cancelled', 'Reservation cancelled - Reservation ID: 12', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-01 10:05:05'),
(190, 1072, 'system_backup', 'System backup performed', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-01 06:37:03'),
(191, 1075, 'stock_adjusted', 'Stock level adjusted', '172.16.0.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-01 11:35:26'),
(192, 1074, 'room_status_changed', 'Room status changed - Room: 115', '10.0.0.51', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-01 14:06:03'),
(193, 1076, 'reservation_checked_in', 'Guest checked in - Reservation ID: 76', '10.0.0.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-01 00:05:31'),
(194, 1072, 'payment_processed', 'Payment processed - Amount: $363.00', '10.0.0.51', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-01 11:12:17'),
(195, 1074, 'room_cleaned', 'Room marked as cleaned - Room: 852', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-01 12:10:06'),
(196, 1075, 'data_exported', 'Data exported', '192.168.1.100', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-30 22:38:15'),
(197, 1074, 'feedback_submitted', 'Guest feedback submitted', '10.0.0.53', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-09-30 23:05:49'),
(198, 1076, 'feedback_reviewed', 'Feedback reviewed by staff', '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-01 14:35:39'),
(199, 1073, 'purchase_order_created', 'Purchase order created', '192.168.1.100', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-01 00:28:12'),
(200, 1073, 'permission_changed', 'User permissions changed', '172.16.0.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-01 03:34:32'),
(201, 1073, 'bill_updated', 'Bill updated', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-01 09:04:34'),
(202, 1072, 'permission_changed', 'User permissions changed', '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-01 02:24:10'),
(203, 1076, 'guest_deleted', 'Guest profile deleted - Guest ID: 73', '10.0.0.51', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-01 07:03:56'),
(204, 1076, 'reservation_checked_out', 'Guest checked out - Reservation ID: 39', '172.16.0.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-01 10:32:37'),
(205, 1075, 'room_assigned', 'Room assigned to guest - Room: 395', '172.16.0.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-02 02:35:44'),
(206, 1076, 'report_generated', 'Report generated', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-01 22:27:21'),
(207, 1071, 'reservation_cancelled', 'Reservation cancelled - Reservation ID: 58', '10.0.0.53', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-02 14:16:28'),
(208, 1076, 'room_maintenance', 'Room maintenance requested - Room: 270', '10.0.0.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-01 22:17:54'),
(209, 1072, 'analytics_viewed', 'Analytics dashboard viewed', '172.16.0.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-02 12:08:19'),
(210, 1073, 'guest_updated', 'Guest profile updated - Guest ID: 13', '192.168.1.101', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-02 09:50:12'),
(211, 1076, 'reservation_checked_in', 'Guest checked in - Reservation ID: 146', '10.0.0.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-02 09:50:31'),
(212, 1072, 'inventory_updated', 'Inventory updated - Item: Soap', '172.16.0.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-02 14:24:51'),
(213, 1075, 'guest_viewed', 'Guest profile viewed - Guest ID: 8', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-02 02:53:39'),
(214, 1075, 'reservation_updated', 'Reservation updated - Reservation ID: 70', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-02 03:32:42'),
(215, 1075, 'user_login', 'User logged into the system', '192.168.1.100', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-02 03:28:05'),
(216, 1076, 'security_alert', 'Security alert triggered', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-02 07:03:59'),
(217, 1075, 'guest_viewed', 'Guest profile viewed - Guest ID: 53', '172.16.0.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-03 06:09:33'),
(218, 1076, 'analytics_viewed', 'Analytics dashboard viewed', '172.16.0.12', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-03 06:18:21'),
(219, 1072, 'supplier_added', 'New supplier added', '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-03 09:54:31'),
(220, 1076, 'invoice_created', 'Invoice created', '192.168.1.100', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-03 09:39:04'),
(221, 1072, 'user_login', 'User logged into the system', '10.0.0.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-03 09:25:16'),
(222, 1073, 'guest_updated', 'Guest profile updated - Guest ID: 91', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-03 05:11:29'),
(223, 1073, 'user_created', 'New user account created', '10.0.0.51', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-03 14:18:28'),
(224, 1073, 'analytics_viewed', 'Analytics dashboard viewed', '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-03 03:57:40'),
(225, 1074, 'guest_created', 'New guest profile created - Guest ID: 9', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-02 22:16:27'),
(226, 1074, 'user_created', 'New user account created', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-03 01:12:35'),
(227, 1075, 'room_cleaned', 'Room marked as cleaned - Room: 567', '10.0.0.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-03 13:26:13'),
(228, 1075, 'guest_updated', 'Guest profile updated - Guest ID: 72', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-03 06:10:46'),
(229, 1075, 'supplier_added', 'New supplier added', '172.16.0.10', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-03 11:11:19'),
(230, 1075, 'stock_adjusted', 'Stock level adjusted', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-03 09:38:24'),
(231, 1073, 'reservation_updated', 'Reservation updated - Reservation ID: 194', '10.0.0.52', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-04 06:22:17'),
(232, 1072, 'inventory_updated', 'Inventory updated - Item: Shampoo', '172.16.0.13', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-04 13:42:55'),
(233, 1073, 'bill_updated', 'Bill updated', '192.168.1.103', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-04 04:45:15'),
(234, 1075, 'room_maintenance', 'Room maintenance requested - Room: 243', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-03 23:42:49'),
(235, 1071, 'analytics_viewed', 'Analytics dashboard viewed', '10.0.0.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-04 01:30:20'),
(236, 1076, 'complaint_handled', 'Guest complaint handled', '172.16.0.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-04 06:35:51'),
(237, 1072, 'reservation_checked_out', 'Guest checked out - Reservation ID: 18', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-05 07:46:43'),
(238, 1076, 'reservation_checked_out', 'Guest checked out - Reservation ID: 122', '172.16.0.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-05 03:43:45'),
(239, 1075, 'user_login', 'User logged into the system', '10.0.0.53', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-05 03:26:46'),
(240, 1075, 'guest_deleted', 'Guest profile deleted - Guest ID: 97', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-04 23:48:08'),
(241, 1072, 'stock_adjusted', 'Stock level adjusted', '172.16.0.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-05 03:23:10'),
(242, 1076, 'guest_viewed', 'Guest profile viewed - Guest ID: 81', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-05 11:12:48'),
(243, 1072, 'invoice_created', 'Invoice created', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-05 03:57:10');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(244, 1073, 'feedback_reviewed', 'Feedback reviewed by staff', '172.16.0.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-05 02:59:51'),
(245, 1075, 'security_alert', 'Security alert triggered', '172.16.0.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-05 08:33:00'),
(246, 1071, 'room_assigned', 'Room assigned to guest - Room: 552', '10.0.0.53', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-05 06:21:51'),
(247, 1074, 'supplier_added', 'New supplier added', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-06 01:13:09'),
(248, 1073, 'reservation_updated', 'Reservation updated - Reservation ID: 64', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-06 12:38:09'),
(249, 1074, 'feedback_reviewed', 'Feedback reviewed by staff', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-06 09:51:55'),
(250, 1074, 'invoice_created', 'Invoice created', '172.16.0.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-06 05:15:08'),
(251, 1075, 'analytics_viewed', 'Analytics dashboard viewed', '172.16.0.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-06 11:41:11'),
(252, 1076, 'guest_updated', 'Guest profile updated - Guest ID: 78', '172.16.0.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-06 11:41:37'),
(253, 1073, 'guest_updated', 'Guest profile updated - Guest ID: 91', '172.16.0.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-06 01:03:39'),
(254, 1076, 'feedback_submitted', 'Guest feedback submitted', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-06 02:44:01'),
(255, 1074, 'reservation_cancelled', 'Reservation cancelled - Reservation ID: 188', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-06 07:21:43'),
(256, 1074, 'complaint_handled', 'Guest complaint handled', '10.0.0.51', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-05 22:50:22'),
(257, 1075, 'payment_processed', 'Payment processed - Amount: $381.00', '172.16.0.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-05 23:14:23'),
(258, 1073, 'analytics_viewed', 'Analytics dashboard viewed', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-06 13:50:37'),
(259, 1071, 'inventory_updated', 'Inventory updated - Item: Coffee', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-06 01:15:34'),
(260, 1076, 'reservation_checked_in', 'Guest checked in - Reservation ID: 48', '10.0.0.50', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-06 10:43:38'),
(261, 1072, 'user_updated', 'User account updated', '192.168.1.103', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-06 12:01:36'),
(262, 1071, 'user_logout', 'User logged out of the system', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-06 07:33:57'),
(263, 1072, 'reservation_checked_in', 'Guest checked in - Reservation ID: 115', '172.16.0.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-06 22:21:43'),
(264, 1072, 'reservation_cancelled', 'Reservation cancelled - Reservation ID: 47', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-07 04:31:34'),
(265, 1071, 'report_generated', 'Report generated', '10.0.0.51', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-07 11:38:22'),
(266, 1073, 'room_maintenance', 'Room maintenance requested - Room: 853', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-07 02:37:48'),
(267, 1076, 'purchase_order_created', 'Purchase order created', '192.168.1.103', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-07 07:56:36'),
(268, 1072, 'security_alert', 'Security alert triggered', '172.16.0.10', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-07 11:40:03'),
(269, 1071, 'stock_adjusted', 'Stock level adjusted', '10.0.0.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-07 11:16:36'),
(270, 1075, 'complaint_handled', 'Guest complaint handled', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-07 13:03:14'),
(271, 1072, 'reservation_checked_out', 'Guest checked out - Reservation ID: 119', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-07 00:19:12'),
(272, 1073, 'reservation_checked_out', 'Guest checked out - Reservation ID: 90', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-07 05:39:44'),
(273, 1071, 'inventory_updated', 'Inventory updated - Item: Towels', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-06 23:47:51'),
(274, 1074, 'permission_changed', 'User permissions changed', '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-07 11:32:44'),
(275, 1072, 'complaint_handled', 'Guest complaint handled', '10.0.0.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-08 05:26:39'),
(276, 1076, 'system_backup', 'System backup performed', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-08 03:01:17'),
(277, 1076, 'permission_changed', 'User permissions changed', '10.0.0.52', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-08 02:01:06'),
(278, 1073, 'invoice_created', 'Invoice created', '192.168.1.102', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-08 05:14:07'),
(279, 1071, 'data_exported', 'Data exported', '10.0.0.51', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-08 12:25:31'),
(280, 1074, 'failed_login', 'Failed login attempt', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-07 22:20:52'),
(281, 1075, 'purchase_order_created', 'Purchase order created', '192.168.1.100', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-08 12:58:40'),
(282, 1073, 'supplier_added', 'New supplier added', '172.16.0.12', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-07 23:41:35'),
(283, 1076, 'feedback_reviewed', 'Feedback reviewed by staff', '10.0.0.51', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-08 02:46:56'),
(284, 1075, 'permission_changed', 'User permissions changed', '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-08 14:27:30'),
(285, 1074, 'failed_login', 'Failed login attempt', '10.0.0.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-08 00:28:18'),
(286, 1076, 'reservation_checked_in', 'Guest checked in - Reservation ID: 185', '172.16.0.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-07 22:16:32'),
(287, 1076, 'security_alert', 'Security alert triggered', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-08 08:26:45'),
(288, 1075, 'bill_updated', 'Bill updated', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-08 04:13:14'),
(289, 1075, 'room_cleaned', 'Room marked as cleaned - Room: 117', '10.0.0.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-08 04:44:21'),
(290, 1075, 'guest_viewed', 'Guest profile viewed - Guest ID: 23', '172.16.0.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-08 08:40:47'),
(291, 1073, 'guest_deleted', 'Guest profile deleted - Guest ID: 56', '192.168.1.103', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-08 02:40:31'),
(292, 1074, 'user_login', 'User logged into the system', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-08 00:28:09'),
(293, 1074, 'refund_processed', 'Refund processed', '172.16.0.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-09 05:10:12'),
(294, 1071, 'invoice_created', 'Invoice created', '192.168.1.103', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-08 23:50:37'),
(295, 1073, 'invoice_created', 'Invoice created', '10.0.0.53', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-09 00:21:05'),
(296, 1076, 'refund_processed', 'Refund processed', '172.16.0.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-09 06:26:27'),
(297, 1073, 'room_assigned', 'Room assigned to guest - Room: 705', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-09 04:44:05'),
(298, 1074, 'inventory_updated', 'Inventory updated - Item: Coffee', '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-09 05:56:25'),
(299, 1073, 'user_updated', 'User account updated', '172.16.0.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-09 11:07:37'),
(300, 1075, 'supplier_added', 'New supplier added', '172.16.0.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-09 03:25:08'),
(301, 1074, 'feedback_reviewed', 'Feedback reviewed by staff', '10.0.0.51', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-09 03:38:54'),
(302, 1076, 'data_exported', 'Data exported', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-09 11:30:49'),
(303, 1072, 'feedback_reviewed', 'Feedback reviewed by staff', '10.0.0.50', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-09 12:10:19'),
(304, 1071, 'user_updated', 'User account updated', '10.0.0.52', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-09 01:39:37'),
(305, 1071, 'room_status_changed', 'Room status changed - Room: 201', '10.0.0.50', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-08 22:52:18'),
(306, 1071, 'room_cleaned', 'Room marked as cleaned - Room: 400', '10.0.0.53', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-09 03:47:45'),
(307, 1073, 'data_exported', 'Data exported', '172.16.0.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-08 22:54:14'),
(308, 1074, 'invoice_created', 'Invoice created', '172.16.0.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-09 06:23:42'),
(309, 1076, 'guest_updated', 'Guest profile updated - Guest ID: 70', '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-09 11:37:31'),
(310, 1075, 'purchase_order_created', 'Purchase order created', '192.168.1.102', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-09 09:55:45'),
(311, 1073, 'inventory_updated', 'Inventory updated - Item: Shampoo', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-09 09:19:45'),
(312, 1072, 'guest_deleted', 'Guest profile deleted - Guest ID: 39', '172.16.0.13', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-09 08:23:05'),
(313, 1076, 'feedback_reviewed', 'Feedback reviewed by staff', '10.0.0.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-09 12:24:31'),
(314, 1072, 'inventory_updated', 'Inventory updated - Item: Tea', '172.16.0.10', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-09 23:43:26'),
(315, 1075, 'feedback_submitted', 'Guest feedback submitted', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-10 09:51:07'),
(316, 1073, 'reservation_cancelled', 'Reservation cancelled - Reservation ID: 157', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-10 05:21:25'),
(317, 1072, 'failed_login', 'Failed login attempt', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-10 02:59:38'),
(318, 1073, 'inventory_updated', 'Inventory updated - Item: Tea', '172.16.0.12', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-10 12:53:24'),
(319, 1072, 'bill_updated', 'Bill updated', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-10 10:51:45'),
(320, 1073, 'failed_login', 'Failed login attempt', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-10 08:08:18'),
(321, 1072, 'user_login', 'User logged into the system', '10.0.0.52', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-10 07:14:42'),
(322, 1075, 'feedback_submitted', 'Guest feedback submitted', '172.16.0.13', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-10 14:17:50'),
(323, 1075, 'room_assigned', 'Room assigned to guest - Room: 878', '10.0.0.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-09 23:52:56'),
(324, 1072, 'inventory_updated', 'Inventory updated - Item: Tea', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-09 23:36:29'),
(325, 1074, 'report_generated', 'Report generated', '172.16.0.13', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-10 04:42:20'),
(326, 1074, 'reservation_created', 'New reservation created - Reservation ID: 112', '192.168.1.102', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-10 04:41:09'),
(327, 1073, 'system_backup', 'System backup performed', '10.0.0.50', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-10 11:35:09'),
(328, 1071, 'inventory_updated', 'Inventory updated - Item: Shampoo', '172.16.0.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-10 12:09:21'),
(329, 1072, 'user_updated', 'User account updated', '10.0.0.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-10 13:51:26'),
(330, 1074, 'guest_viewed', 'Guest profile viewed - Guest ID: 76', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-10 12:01:35'),
(331, 1071, 'room_assigned', 'Room assigned to guest - Room: 592', '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-10 04:02:53'),
(332, 1073, 'reservation_created', 'New reservation created - Reservation ID: 52', '10.0.0.50', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-10 09:53:33'),
(333, 1074, 'bill_updated', 'Bill updated', '172.16.0.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-11 01:13:17'),
(334, 1071, 'purchase_order_created', 'Purchase order created', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-11 09:29:19'),
(335, 1071, 'reservation_checked_in', 'Guest checked in - Reservation ID: 99', '192.168.1.100', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-11 04:29:54'),
(336, 1071, 'payment_processed', 'Payment processed - Amount: $103.00', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-11 08:25:31'),
(337, 1074, 'payment_processed', 'Payment processed - Amount: $273.00', '192.168.1.103', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-11 07:32:39'),
(338, 1071, 'reservation_checked_out', 'Guest checked out - Reservation ID: 12', '10.0.0.52', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-11 10:28:03'),
(339, 1076, 'payment_processed', 'Payment processed - Amount: $116.00', '172.16.0.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-11 14:33:54'),
(340, 1072, 'reservation_cancelled', 'Reservation cancelled - Reservation ID: 35', '172.16.0.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-11 00:58:36'),
(341, 1071, 'reservation_checked_in', 'Guest checked in - Reservation ID: 110', '10.0.0.52', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-11 08:21:37'),
(342, 1071, 'failed_login', 'Failed login attempt', '192.168.1.102', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-11 13:40:22'),
(343, 1073, 'data_exported', 'Data exported', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-11 12:38:50'),
(344, 1071, 'system_backup', 'System backup performed', '192.168.1.102', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-10 23:38:01'),
(345, 1071, 'reservation_created', 'New reservation created - Reservation ID: 163', '172.16.0.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-11 11:31:37'),
(346, 1071, 'reservation_cancelled', 'Reservation cancelled - Reservation ID: 87', '172.16.0.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-11 03:33:04'),
(347, 1074, 'failed_login', 'Failed login attempt', '10.0.0.53', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-11 04:20:07'),
(348, 1072, 'guest_created', 'New guest profile created - Guest ID: 68', '10.0.0.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-11 04:34:46'),
(349, 1075, 'room_cleaned', 'Room marked as cleaned - Room: 655', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-10 22:20:02'),
(350, 1072, 'invoice_created', 'Invoice created', '10.0.0.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-11 04:20:29'),
(351, 1071, 'guest_created', 'New guest profile created - Guest ID: 29', '172.16.0.13', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-11 12:37:49'),
(352, 1071, 'reservation_cancelled', 'Reservation cancelled - Reservation ID: 31', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-10 22:01:22'),
(353, 1072, 'reservation_checked_out', 'Guest checked out - Reservation ID: 36', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-12 06:48:06'),
(354, 1073, 'refund_processed', 'Refund processed', '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-12 06:12:14'),
(355, 1072, 'payment_processed', 'Payment processed - Amount: $404.00', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-12 07:03:20'),
(356, 1071, 'inventory_updated', 'Inventory updated - Item: Shampoo', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-12 02:28:06'),
(357, 1071, 'complaint_handled', 'Guest complaint handled', '10.0.0.50', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-12 06:55:38'),
(358, 1076, 'supplier_added', 'New supplier added', '172.16.0.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-12 00:44:10'),
(359, 1073, 'user_login', 'User logged into the system', '172.16.0.13', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-11 22:02:17'),
(360, 1075, 'room_status_changed', 'Room status changed - Room: 737', '10.0.0.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-11 23:55:06'),
(361, 1073, 'guest_viewed', 'Guest profile viewed - Guest ID: 8', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-12 11:48:26'),
(362, 1076, 'guest_created', 'New guest profile created - Guest ID: 52', '192.168.1.101', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-12 01:22:48'),
(363, 1076, 'complaint_handled', 'Guest complaint handled', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-12 01:33:55'),
(364, 1072, 'user_logout', 'User logged out of the system', '192.168.1.103', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-11 23:17:56'),
(365, 1075, 'inventory_updated', 'Inventory updated - Item: Coffee', '10.0.0.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-12 11:40:59'),
(366, 1074, 'reservation_created', 'New reservation created - Reservation ID: 147', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-12 14:50:23'),
(367, 1071, 'permission_changed', 'User permissions changed', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-12 01:28:01'),
(368, 1076, 'bill_updated', 'Bill updated', '10.0.0.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-12 04:23:10'),
(369, 1072, 'room_status_changed', 'Room status changed - Room: 249', '172.16.0.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-12 09:49:28'),
(370, 1076, 'reservation_updated', 'Reservation updated - Reservation ID: 16', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-12 13:21:41'),
(371, 1071, 'refund_processed', 'Refund processed', '172.16.0.13', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-12 02:15:30'),
(372, 1073, 'user_login', 'User logged into the system', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-12 08:08:46'),
(373, 1076, 'room_status_changed', 'Room status changed - Room: 826', '192.168.1.103', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-12 09:57:48'),
(374, 1073, 'purchase_order_created', 'Purchase order created', '172.16.0.12', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-12 09:56:13'),
(375, 1072, 'permission_changed', 'User permissions changed', '10.0.0.51', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-12 09:09:53'),
(376, 1075, 'guest_viewed', 'Guest profile viewed - Guest ID: 45', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-12 02:04:10'),
(377, 1072, 'permission_changed', 'User permissions changed', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-12 11:42:32'),
(378, 1073, 'feedback_submitted', 'Guest feedback submitted', '10.0.0.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-13 09:19:26'),
(379, 1073, 'stock_adjusted', 'Stock level adjusted', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-13 02:50:54'),
(380, 1071, 'payment_processed', 'Payment processed - Amount: $60.00', '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-13 14:42:15'),
(381, 1072, 'security_alert', 'Security alert triggered', '10.0.0.51', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-13 05:47:09'),
(382, 1075, 'data_exported', 'Data exported', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-13 12:24:21'),
(383, 1076, 'room_status_changed', 'Room status changed - Room: 166', '10.0.0.52', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-13 01:37:43'),
(384, 1075, 'guest_deleted', 'Guest profile deleted - Guest ID: 91', '172.16.0.10', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-13 04:12:23'),
(385, 1073, 'bill_updated', 'Bill updated', '172.16.0.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-13 09:31:05'),
(386, 1071, 'user_login', 'User logged into the system', '172.16.0.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-13 07:43:59'),
(387, 1073, 'reservation_updated', 'Reservation updated - Reservation ID: 124', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-12 22:13:36'),
(388, 1073, 'system_backup', 'System backup performed', '192.168.1.102', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-13 07:10:19'),
(389, 1076, 'feedback_reviewed', 'Feedback reviewed by staff', '172.16.0.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-13 13:34:04'),
(390, 1074, 'refund_processed', 'Refund processed', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-13 14:24:08'),
(391, 1076, 'reservation_updated', 'Reservation updated - Reservation ID: 128', '192.168.1.100', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-13 11:42:28'),
(392, 1071, 'reservation_updated', 'Reservation updated - Reservation ID: 196', '172.16.0.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-13 06:27:37'),
(393, 1075, 'security_alert', 'Security alert triggered', '192.168.1.100', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-13 11:37:33'),
(394, 1075, 'guest_updated', 'Guest profile updated - Guest ID: 51', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-14 04:20:12'),
(395, 1074, 'refund_processed', 'Refund processed', '10.0.0.53', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-14 07:08:54'),
(396, 1073, 'guest_created', 'New guest profile created - Guest ID: 41', '10.0.0.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-14 09:06:56'),
(397, 1075, 'supplier_added', 'New supplier added', '192.168.1.103', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-14 11:53:28'),
(398, 1075, 'report_generated', 'Report generated', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-14 11:16:54'),
(399, 1076, 'permission_changed', 'User permissions changed', '10.0.0.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-14 00:01:06'),
(400, 1073, 'inventory_updated', 'Inventory updated - Item: Tea', '192.168.1.103', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-14 01:39:06'),
(401, 1073, 'guest_updated', 'Guest profile updated - Guest ID: 86', '172.16.0.10', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-14 03:07:25'),
(402, 1072, 'purchase_order_created', 'Purchase order created', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-14 04:26:43'),
(403, 1072, 'user_created', 'New user account created', '10.0.0.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-14 08:59:56'),
(404, 1071, 'refund_processed', 'Refund processed', '10.0.0.51', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-13 23:52:29'),
(405, 1073, 'user_updated', 'User account updated', '10.0.0.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-14 23:28:25'),
(406, 1075, 'guest_updated', 'Guest profile updated - Guest ID: 33', '172.16.0.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-15 05:53:46'),
(407, 1076, 'reservation_created', 'New reservation created - Reservation ID: 81', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-14 23:46:30'),
(408, 1073, 'invoice_created', 'Invoice created', '10.0.0.52', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-15 06:50:56'),
(409, 1073, 'failed_login', 'Failed login attempt', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-15 12:55:35'),
(410, 1076, 'reservation_created', 'New reservation created - Reservation ID: 8', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-15 06:16:44'),
(411, 1073, 'user_updated', 'User account updated', '172.16.0.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-15 04:02:03'),
(412, 1071, 'payment_processed', 'Payment processed - Amount: $426.00', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-14 23:00:57'),
(413, 1074, 'user_login', 'User logged into the system', '172.16.0.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-15 13:14:21'),
(414, 1075, 'feedback_reviewed', 'Feedback reviewed by staff', '172.16.0.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-15 08:56:27'),
(415, 1072, 'reservation_checked_out', 'Guest checked out - Reservation ID: 133', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-14 23:28:08'),
(416, 1075, 'report_generated', 'Report generated', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-15 03:53:53'),
(417, 1073, 'guest_viewed', 'Guest profile viewed - Guest ID: 93', '10.0.0.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-14 22:41:15'),
(418, 1075, 'room_maintenance', 'Room maintenance requested - Room: 923', '172.16.0.12', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-15 13:31:40'),
(419, 1074, 'refund_processed', 'Refund processed', '172.16.0.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-15 00:33:15'),
(420, 1072, 'reservation_updated', 'Reservation updated - Reservation ID: 65', '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-16 07:33:24'),
(421, 1071, 'feedback_submitted', 'Guest feedback submitted', '192.168.1.103', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-16 11:45:46'),
(422, 1071, 'feedback_submitted', 'Guest feedback submitted', '192.168.1.100', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-16 02:53:12'),
(423, 1073, 'user_updated', 'User account updated', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-16 02:46:42'),
(424, 1075, 'failed_login', 'Failed login attempt', '172.16.0.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-16 03:01:01'),
(425, 1072, 'bill_updated', 'Bill updated', '10.0.0.53', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-15 22:51:30'),
(426, 1075, 'guest_created', 'New guest profile created - Guest ID: 78', '192.168.1.100', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-16 05:48:50'),
(427, 1076, 'invoice_created', 'Invoice created', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-16 05:09:45'),
(428, 1075, 'reservation_updated', 'Reservation updated - Reservation ID: 22', '10.0.0.52', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-16 01:14:04'),
(429, 1073, 'room_cleaned', 'Room marked as cleaned - Room: 726', '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-16 01:43:01'),
(430, 1075, 'bill_updated', 'Bill updated', '172.16.0.13', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-16 03:17:59'),
(431, 1071, 'stock_adjusted', 'Stock level adjusted', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-16 01:47:50'),
(432, 1075, 'invoice_created', 'Invoice created', '172.16.0.12', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-16 02:35:27'),
(433, 1072, 'guest_created', 'New guest profile created - Guest ID: 27', '172.16.0.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-16 06:03:52'),
(434, 1074, 'complaint_handled', 'Guest complaint handled', '192.168.1.100', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-16 13:35:46'),
(435, 1072, 'room_maintenance', 'Room maintenance requested - Room: 545', '172.16.0.13', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-16 08:23:39'),
(436, 1071, 'guest_updated', 'Guest profile updated - Guest ID: 18', '10.0.0.52', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-16 07:54:16'),
(437, 1076, 'complaint_handled', 'Guest complaint handled', '10.0.0.50', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-16 03:31:54'),
(438, 1072, 'purchase_order_created', 'Purchase order created', '192.168.1.101', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-16 10:44:46'),
(439, 1073, 'payment_processed', 'Payment processed - Amount: $252.00', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-17 07:20:02'),
(440, 1074, 'feedback_reviewed', 'Feedback reviewed by staff', '10.0.0.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-17 05:32:26'),
(441, 1073, 'invoice_created', 'Invoice created', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-17 04:58:38'),
(442, 1071, 'data_exported', 'Data exported', '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-17 13:27:52'),
(443, 1076, 'security_alert', 'Security alert triggered', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-17 14:56:26'),
(444, 1073, 'reservation_checked_in', 'Guest checked in - Reservation ID: 36', '192.168.1.102', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-17 09:10:40'),
(445, 1071, 'reservation_created', 'New reservation created - Reservation ID: 115', '172.16.0.12', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-16 23:05:35'),
(446, 1071, 'bill_updated', 'Bill updated', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-17 06:07:06'),
(447, 1076, 'supplier_added', 'New supplier added', '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-17 11:32:26'),
(448, 1072, 'reservation_cancelled', 'Reservation cancelled - Reservation ID: 137', '10.0.0.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-17 09:48:57'),
(449, 1075, 'payment_processed', 'Payment processed - Amount: $438.00', '172.16.0.13', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-18 05:42:02'),
(450, 1071, 'bill_updated', 'Bill updated', '172.16.0.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-18 08:04:40'),
(451, 1072, 'permission_changed', 'User permissions changed', '10.0.0.51', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-18 02:57:57'),
(452, 1075, 'supplier_added', 'New supplier added', '172.16.0.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-18 06:02:01'),
(453, 1074, 'room_status_changed', 'Room status changed - Room: 896', '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-18 14:02:33'),
(454, 1071, 'reservation_checked_in', 'Guest checked in - Reservation ID: 183', '172.16.0.13', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-18 14:49:09'),
(455, 1073, 'room_maintenance', 'Room maintenance requested - Room: 646', '10.0.0.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-18 00:39:20'),
(456, 1074, 'user_logout', 'User logged out of the system', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-18 03:26:12'),
(457, 1072, 'reservation_cancelled', 'Reservation cancelled - Reservation ID: 90', '172.16.0.12', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-18 00:26:56'),
(458, 1074, 'report_generated', 'Report generated', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-18 03:23:28'),
(459, 1074, 'security_alert', 'Security alert triggered', '10.0.0.52', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-17 22:39:32'),
(460, 1075, 'guest_deleted', 'Guest profile deleted - Guest ID: 91', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-18 11:12:08'),
(461, 1076, 'data_exported', 'Data exported', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-18 11:22:34'),
(462, 1071, 'reservation_cancelled', 'Reservation cancelled - Reservation ID: 5', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-19 03:50:14'),
(463, 1073, 'purchase_order_created', 'Purchase order created', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-19 10:43:33'),
(464, 1072, 'guest_viewed', 'Guest profile viewed - Guest ID: 13', '192.168.1.101', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-19 02:49:46'),
(465, 1072, 'failed_login', 'Failed login attempt', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-19 03:14:24'),
(466, 1073, 'room_status_changed', 'Room status changed - Room: 666', '10.0.0.50', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-19 08:40:23'),
(467, 1076, 'guest_deleted', 'Guest profile deleted - Guest ID: 90', '10.0.0.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-19 02:24:37'),
(468, 1074, 'complaint_handled', 'Guest complaint handled', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-19 00:38:42'),
(469, 1071, 'user_created', 'New user account created', '10.0.0.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-19 01:41:53'),
(470, 1071, 'complaint_handled', 'Guest complaint handled', '172.16.0.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-20 06:15:10'),
(471, 1074, 'data_exported', 'Data exported', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-20 14:53:51'),
(472, 1072, 'guest_updated', 'Guest profile updated - Guest ID: 75', '192.168.1.100', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-20 09:08:16'),
(473, 1075, 'payment_processed', 'Payment processed - Amount: $235.00', '192.168.1.102', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-20 03:30:08'),
(474, 1076, 'reservation_updated', 'Reservation updated - Reservation ID: 86', '172.16.0.12', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-20 02:34:09'),
(475, 1076, 'stock_adjusted', 'Stock level adjusted', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-20 08:58:22'),
(476, 1076, 'supplier_added', 'New supplier added', '10.0.0.52', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-20 07:42:08'),
(477, 1076, 'report_generated', 'Report generated', '10.0.0.53', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-19 23:29:32'),
(478, 1073, 'failed_login', 'Failed login attempt', '10.0.0.51', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-20 10:43:17'),
(479, 1073, 'feedback_reviewed', 'Feedback reviewed by staff', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-20 14:17:07'),
(480, 1076, 'user_updated', 'User account updated', '192.168.1.101', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-19 23:45:13'),
(481, 1075, 'guest_updated', 'Guest profile updated - Guest ID: 71', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-20 03:57:31'),
(482, 1075, 'room_maintenance', 'Room maintenance requested - Room: 241', '172.16.0.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-20 10:54:21'),
(483, 1075, 'security_alert', 'Security alert triggered', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-20 02:41:37'),
(484, 1071, 'guest_created', 'New guest profile created - Guest ID: 67', '10.0.0.53', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-20 10:17:38'),
(485, 1072, 'invoice_created', 'Invoice created', '172.16.0.11', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-20 14:42:11');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(486, 1074, 'room_status_changed', 'Room status changed - Room: 398', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-19 23:47:13'),
(487, 1073, 'room_status_changed', 'Room status changed - Room: 696', '172.16.0.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-20 08:22:18'),
(488, 1072, 'guest_updated', 'Guest profile updated - Guest ID: 19', '10.0.0.51', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-20 12:32:09'),
(489, 1072, 'inventory_updated', 'Inventory updated - Item: Tea', '172.16.0.12', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-21 09:18:43'),
(490, 1074, 'room_status_changed', 'Room status changed - Room: 229', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-21 00:01:03'),
(491, 1073, 'stock_adjusted', 'Stock level adjusted', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-21 07:15:47'),
(492, 1076, 'data_exported', 'Data exported', '172.16.0.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-21 11:41:15'),
(493, 1072, 'reservation_updated', 'Reservation updated - Reservation ID: 166', '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-21 14:21:31'),
(494, 1075, 'room_cleaned', 'Room marked as cleaned - Room: 538', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-21 09:13:04'),
(495, 1073, 'system_backup', 'System backup performed', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-21 11:17:33'),
(496, 1073, 'report_generated', 'Report generated', '172.16.0.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-21 11:54:58'),
(497, 1076, 'guest_viewed', 'Guest profile viewed - Guest ID: 34', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-21 14:00:30'),
(498, 1074, 'report_generated', 'Report generated', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-21 03:30:27'),
(499, 1075, 'permission_changed', 'User permissions changed', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', '2025-10-21 12:58:09'),
(500, 1075, 'inventory_updated', 'Inventory updated - Item: Soap', '10.0.0.51', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-20 23:36:49'),
(501, 1076, 'stock_adjusted', 'Stock level adjusted', '172.16.0.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-20 22:12:23'),
(502, 1074, 'permission_changed', 'User permissions changed', '10.0.0.52', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-21 07:33:47'),
(503, 1071, 'security_alert', 'Security alert triggered', '192.168.1.103', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-21 12:47:50'),
(504, 1071, 'permission_changed', 'User permissions changed', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-21 05:43:21'),
(505, 1075, 'purchase_order_created', 'Purchase order created', '10.0.0.51', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-21 11:23:44'),
(506, 1072, 'reservation_created', 'New reservation created - Reservation ID: 87', '172.16.0.10', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-20 23:00:12'),
(507, 1073, 'stock_adjusted', 'Stock level adjusted', '172.16.0.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-10-21 01:35:52'),
(508, 1076, 'feedback_reviewed', 'Feedback reviewed by staff', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-21 05:11:37'),
(509, 1073, 'user_login', 'User logged into the system', '172.16.0.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', '2025-10-21 08:55:03'),
(510, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-21 08:28:00'),
(511, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-22 00:15:46'),
(512, 1073, 'task_assigned', 'Assigned housekeeping task #10 (Room 101) to Maria Garcia', NULL, NULL, '2025-10-22 00:58:32'),
(513, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-22 01:33:07'),
(514, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-22 01:35:12'),
(515, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-22 01:46:21'),
(516, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-22 02:00:39'),
(517, 1073, 'reservation_cancelled', 'Cancelled reservation RES-001003 for John Doe', NULL, NULL, '2025-10-22 02:11:13'),
(518, 1073, 'guest_checked_out', 'Checked out guest for reservation RES-001002', NULL, NULL, '2025-10-22 02:54:38'),
(519, 1073, 'reservation_created', 'Created reservation RES202510223939', NULL, NULL, '2025-10-22 03:19:09'),
(520, 1073, 'guest_checked_in', 'Checked in guest for reservation RES202510223939', NULL, NULL, '2025-10-22 03:28:14'),
(521, 1073, 'service_request_completed', 'Completed service request #23', NULL, NULL, '2025-10-22 03:28:47'),
(522, 1073, 'service_request_created', 'Created service request #25 for room 55', NULL, NULL, '2025-10-22 03:42:21'),
(523, 1073, 'user_created', 'Created user: janx21', NULL, NULL, '2025-10-22 03:47:19'),
(524, 1073, 'guest_promoted_to_vip', 'Promoted guest James Taylor to gold VIP', NULL, NULL, '2025-10-22 05:20:47'),
(525, 1073, 'loyalty_member_added', 'Added guest sample12333 sample to silver loyalty tier', NULL, NULL, '2025-10-22 07:15:37'),
(526, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-22 07:17:39'),
(527, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-22 07:26:22'),
(528, 1073, 'room_updated', 'Updated room: 302', NULL, NULL, '2025-10-22 09:27:34'),
(529, 1073, 'maintenance_update', 'Updated maintenance request 23', NULL, NULL, '2025-10-22 09:41:24'),
(530, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-22 09:41:40'),
(531, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-22 09:41:58'),
(532, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-22 09:42:10'),
(533, 1073, 'user_updated', 'Updated user: janx21', NULL, NULL, '2025-10-22 09:45:54'),
(534, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-23 00:23:10'),
(535, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-23 00:23:19'),
(536, 1073, 'bill_created', 'Created bill BILL-20251023-29756 for reservation 63', NULL, NULL, '2025-10-23 00:43:48'),
(537, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-23 00:54:15'),
(538, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-23 00:54:29'),
(539, 1073, 'payment_recorded', 'Recorded payment PAY-20251023-54300 for bill 67', NULL, NULL, '2025-10-23 01:00:51'),
(540, 1073, 'create_discount', 'Created discount template: Debug Test (percentage)', NULL, NULL, '2025-10-23 01:33:37'),
(541, 1073, 'create_discount', 'Created discount template: API Test (percentage)', NULL, NULL, '2025-10-23 01:33:44'),
(542, 1073, 'create_discount', 'Created discount template: package (package_deal)', NULL, NULL, '2025-10-23 01:40:13'),
(543, 1073, 'create_discount', 'Created discount template: sample (percentage)', NULL, NULL, '2025-10-23 01:56:39'),
(544, 1073, 'create_voucher', 'Created voucher: TES20257325 (percentage)', NULL, NULL, '2025-10-23 02:08:08'),
(545, 1073, 'create_voucher', 'Created voucher: WEW20256679 (percentage)', NULL, NULL, '2025-10-23 02:16:40'),
(546, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-23 05:34:47'),
(547, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-23 06:38:34'),
(548, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-23 08:03:40'),
(549, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-23 08:04:10'),
(550, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-23 08:04:46'),
(551, 1072, 'login', 'User logged in successfully', NULL, NULL, '2025-10-23 08:10:32'),
(552, 1072, 'login', 'User logged in successfully', NULL, NULL, '2025-10-23 08:10:36'),
(553, 1071, 'login', 'User logged in successfully', NULL, NULL, '2025-10-23 08:10:45'),
(554, 1072, 'login', 'User logged in successfully', NULL, NULL, '2025-10-23 08:12:30'),
(555, 1071, 'login', 'User logged in successfully', NULL, NULL, '2025-10-23 08:50:51'),
(556, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-23 09:14:40'),
(557, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-24 00:28:44'),
(558, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-24 03:38:10'),
(559, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-24 03:39:13'),
(560, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-24 06:07:54'),
(561, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-24 08:04:30'),
(562, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-24 12:24:53'),
(563, 1071, 'login', 'User logged in successfully', NULL, NULL, '2025-10-25 02:03:21'),
(564, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-25 02:03:51'),
(565, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-25 03:42:03'),
(566, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-25 03:46:52'),
(567, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-25 03:48:09'),
(568, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-25 04:06:17'),
(569, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-25 04:46:20'),
(570, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-25 05:13:54'),
(571, 1072, 'single_room_check_started', 'Housekeeping user started room check for Room 402', '222.127.7.144', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 05:15:04'),
(572, 1072, 'supply_request_created', 'Supply request created for Coffee (Qty: 10) in Room 402', '222.127.7.151', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 05:18:48'),
(573, 1072, 'login', 'User logged in successfully', NULL, NULL, '2025-10-25 05:32:46'),
(574, 1071, 'login', 'User logged in successfully', NULL, NULL, '2025-10-25 05:33:01'),
(575, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-25 05:34:24'),
(576, 1073, 'user_created', 'Created user: jaythedog', NULL, NULL, '2025-10-25 05:38:21'),
(577, 1073, 'reservation_created', 'Created reservation RES202510254090', NULL, NULL, '2025-10-25 05:38:25'),
(578, 1073, 'reservation_created', 'Created reservation RES202510256078', NULL, NULL, '2025-10-25 05:40:43'),
(579, 1073, 'reservation_cancelled', 'Cancelled reservation RES202510254090 for jan aron fajardo', NULL, NULL, '2025-10-25 05:42:16'),
(580, 1073, 'guest_checked_out', 'Checked out guest for reservation RES-001000', NULL, NULL, '2025-10-25 05:42:26'),
(581, 1073, 'guest_checked_in', 'Checked in guest for reservation RES202510256078', NULL, NULL, '2025-10-25 05:43:24'),
(582, 1073, 'guest_checked_out', 'Checked out guest for reservation RES202510223939', NULL, NULL, '2025-10-25 05:43:25'),
(583, 1073, 'guest_checked_out', 'Checked out guest for reservation RES202510256078', NULL, NULL, '2025-10-25 05:43:33'),
(584, 1073, 'reservation_created', 'Created reservation RES202510257652', NULL, NULL, '2025-10-25 05:45:52'),
(585, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-25 06:17:56'),
(586, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-26 07:21:05'),
(587, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-26 07:45:48'),
(588, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-26 07:53:18'),
(589, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-26 07:58:53'),
(590, 1073, 'login', 'User logged in successfully', NULL, NULL, '2025-10-26 08:01:49');

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
(65, 'Medical Assistance', 'On-call medical assistance', 50.00, 'other', 1, '2025-08-27 11:09:55'),
(66, 'Room Upgrade', 'Room upgrade service charge', 0.00, 'other', 1, '2025-10-20 01:30:05');

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
(1, 57, 52, 548.67, 0.00, 54.87, 0.00, 603.54, 'paid', NULL, '2025-10-22 02:39:19', '2025-10-22 02:54:38'),
(2, 63, 61, 800.00, 0.00, 80.00, 0.00, 880.00, 'paid', NULL, '2025-10-22 03:19:09', '2025-10-23 01:00:51'),
(3, 64, 64, 800.00, 0.00, 80.00, 0.00, 880.00, 'pending', NULL, '2025-10-25 05:38:25', '2025-10-25 05:38:25'),
(4, 65, 51, 500.00, 0.00, 50.00, 0.00, 550.00, 'paid', NULL, '2025-10-25 05:40:43', '2025-10-25 05:43:33'),
(5, 55, 51, 348.64, 0.00, 34.86, 0.00, 383.50, 'paid', NULL, '2025-10-25 05:42:16', '2025-10-25 05:42:26'),
(6, 66, 64, 800.00, 0.00, 80.00, 0.00, 880.00, 'pending', NULL, '2025-10-25 05:45:52', '2025-10-25 05:45:52');

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
(14, 'BILL-000001', 1, '2024-01-15', '2024-01-22', 200.00, 20.00, 0.00, 220.00, 'paid', NULL, 1, '2025-10-21 06:09:31', '2025-10-21 06:09:31'),
(15, 'BILL-000002', 2, '2024-01-16', '2024-01-23', 450.00, 45.00, 0.00, 495.00, 'paid', NULL, 1, '2025-10-21 06:09:32', '2025-10-21 06:09:32'),
(16, 'BILL-000003', 3, '2024-01-18', '2024-01-25', 300.00, 30.00, 0.00, 330.00, 'pending', NULL, 1, '2025-10-21 06:09:32', '2025-10-21 06:09:32'),
(17, 'BILL-000004', 4, '2024-01-20', '2024-01-27', 300.00, 30.00, 0.00, 330.00, 'paid', NULL, 1, '2025-10-21 06:09:32', '2025-10-21 06:09:32'),
(18, 'BILL-000005', 5, '2024-01-22', '2024-01-29', 750.00, 75.00, 0.00, 825.00, 'pending', NULL, 1, '2025-10-21 06:09:32', '2025-10-21 06:09:32'),
(63, 'BILL-001000', 58, '2025-10-20', '2025-10-27', 196.20, 23.54, 28.00, 191.74, 'paid', NULL, 1071, '2025-10-20 07:37:00', '2025-10-21 07:20:53'),
(64, 'BILL-001001', 57, '2025-10-18', '2025-10-25', 493.80, 59.26, 2.00, 551.06, 'paid', NULL, 1071, '2025-10-18 07:00:00', '2025-10-21 07:20:53'),
(65, 'BILL-001002', 56, '2025-10-21', '2025-10-28', 312.89, 37.55, 1.00, 349.43, 'paid', NULL, 1071, '2025-10-21 00:08:00', '2025-10-21 07:20:53'),
(66, 'BILL-001003', 55, '2025-10-20', '2025-10-27', 313.78, 37.65, 23.00, 328.43, 'paid', NULL, 1071, '2025-10-20 03:04:00', '2025-10-21 07:20:53'),
(67, 'BILL-20251023-29756', 63, '2025-10-23', '2025-10-30', 1.41, 0.14, 0.00, 1.55, 'paid', 'wew', 1073, '2025-10-23 00:43:48', '2025-10-23 01:00:51');

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

--
-- Dumping data for table `bill_items`
--

INSERT INTO `bill_items` (`id`, `bill_id`, `description`, `quantity`, `unit_price`, `total_amount`) VALUES
(1, 67, '122', 1.00, 1.41, 1.41);

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
(1, 1, 'yes', 'yes', '', 1, '2025-10-14 01:52:17', '2025-10-14 01:52:17', '2025-10-14 01:52:17');

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
  `scenario_id` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `type` enum('complaints','requests','emergencies') NOT NULL,
  `difficulty` enum('beginner','intermediate','advanced') NOT NULL,
  `estimated_time` int(11) NOT NULL DEFAULT 20,
  `points` int(11) NOT NULL DEFAULT 150,
  `status` enum('active','inactive','draft') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `customer_service_scenarios`
--

INSERT INTO `customer_service_scenarios` (`id`, `scenario_id`, `title`, `description`, `type`, `difficulty`, `estimated_time`, `points`, `status`, `created_at`, `updated_at`) VALUES
(1, 'customer_service', 'Customer Service Excellence', 'Handle various customer service situations including complaints and special requests.', 'complaints', 'intermediate', 25, 200, 'active', '2025-10-23 05:37:58', '2025-10-23 05:37:58'),
(2, 'complaint_handling', 'Handling Guest Complaints', 'Practice responding to common guest complaints professionally.', 'complaints', 'beginner', 20, 150, 'active', '2025-10-23 05:37:58', '2025-10-23 05:37:58'),
(3, 'special_requests', 'Special Guest Requests', 'Handle unusual guest requests with professionalism and creativity.', 'requests', 'advanced', 30, 250, 'active', '2025-10-23 05:37:58', '2025-10-23 05:37:58');

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
-- Table structure for table `discount_templates`
--

CREATE TABLE `discount_templates` (
  `id` int(11) NOT NULL,
  `discount_name` varchar(255) NOT NULL,
  `discount_type` enum('percentage','fixed_amount','package_deal') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `minimum_stay` int(11) DEFAULT NULL,
  `valid_from` date NOT NULL,
  `valid_until` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  `room_type` varchar(50) DEFAULT NULL,
  `apply_to_all_rooms` tinyint(1) DEFAULT 1,
  `guest_categories` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `discount_templates`
--

INSERT INTO `discount_templates` (`id`, `discount_name`, `discount_type`, `discount_value`, `description`, `minimum_stay`, `valid_from`, `valid_until`, `is_active`, `created_by`, `room_id`, `room_type`, `apply_to_all_rooms`, `guest_categories`, `created_at`, `updated_at`) VALUES
(1, 'Early Bird Special', 'percentage', 20.00, '20% off for advance booking', 2, '2025-10-23', '2026-04-23', 1, 1073, NULL, NULL, 1, NULL, '2025-10-23 01:11:50', '2025-10-23 01:11:50'),
(2, 'Senior Citizen', 'percentage', 10.00, '10% off for 65+ guests', 1, '2025-10-23', '2026-10-23', 1, 1073, NULL, NULL, 1, NULL, '2025-10-23 01:11:50', '2025-10-23 01:11:50'),
(3, 'Weekend Special', 'fixed_amount', 50.00, '$50 off weekend stays', 2, '2025-10-23', '2026-01-23', 1, 1073, NULL, NULL, 1, NULL, '2025-10-23 01:11:50', '2025-10-23 01:11:50'),
(4, 'First Time Guest', 'fixed_amount', 25.00, '$25 off first booking', 1, '2025-10-23', '2026-10-23', 1, 1073, NULL, NULL, 1, NULL, '2025-10-23 01:11:50', '2025-10-23 01:11:50'),
(5, 'Spa Package', 'package_deal', 15.00, '15% off room + spa', 3, '2025-10-23', '2026-04-23', 1, 1073, NULL, NULL, 1, NULL, '2025-10-23 01:11:50', '2025-10-23 01:11:50'),
(6, 'Extended Stay', 'package_deal', 10.00, '10% off 7+ nights', 7, '2025-10-23', '2026-10-23', 1, 1073, NULL, NULL, 1, NULL, '2025-10-23 01:11:50', '2025-10-23 01:11:50'),
(10, 'Test Discount Live', 'percentage', 15.00, NULL, 2, '2025-10-23', '2025-11-23', 1, 1073, NULL, NULL, 1, NULL, '2025-10-23 01:21:59', '2025-10-23 01:21:59'),
(11, 'Test Discount Live 2', 'fixed_amount', 25.00, 'Test description', 1, '2025-10-23', '2025-11-23', 1, 1073, NULL, NULL, 1, NULL, '2025-10-23 01:21:59', '2025-10-23 01:21:59'),
(12, 'Debug Test', 'percentage', 10.00, 'Debug description', 2, '2025-10-23', '2025-11-23', 1, 1073, NULL, NULL, 1, NULL, '2025-10-23 01:22:39', '2025-10-23 01:22:39'),
(14, 'Debug Test', 'percentage', 10.00, 'Debug description', 2, '2025-10-23', '2025-11-23', 1, 1073, NULL, NULL, 1, NULL, '2025-10-23 01:23:21', '2025-10-23 01:23:21'),
(16, 'Debug Test', 'percentage', 10.00, 'Debug description', 2, '2025-10-23', '2025-11-23', 1, 1073, NULL, NULL, 1, NULL, '2025-10-23 01:24:48', '2025-10-23 01:24:48'),
(18, 'Simple Test', 'percentage', 15.00, NULL, 2, '2025-10-23', '2025-11-23', 1, 1073, NULL, NULL, 1, NULL, '2025-10-23 01:30:57', '2025-10-23 01:30:57'),
(19, 'Debug Test', 'percentage', 10.00, 'Debug description', 2, '2025-10-23', '2025-11-23', 1, 1073, NULL, NULL, 1, NULL, '2025-10-23 01:31:30', '2025-10-23 01:31:30'),
(22, 'Debug Test', 'percentage', 10.00, 'Debug description', 2, '2025-10-23', '2025-11-23', 1, 1073, NULL, NULL, 1, NULL, '2025-10-23 01:33:37', '2025-10-23 01:33:37'),
(23, 'Debug Test', 'percentage', 10.00, NULL, 2, '2025-10-23', '2025-11-23', 1, 1073, NULL, NULL, 1, NULL, '2025-10-23 01:33:37', '2025-10-23 01:33:37'),
(24, 'API Test', 'percentage', 15.00, NULL, 2, '2025-10-23', '2025-11-23', 1, 1073, NULL, NULL, 1, NULL, '2025-10-23 01:33:44', '2025-10-23 01:33:44'),
(25, 'package', 'package_deal', 100.00, NULL, 1, '2025-10-23', '2025-11-24', 1, 1073, NULL, NULL, 1, NULL, '2025-10-23 01:40:13', '2025-10-23 01:40:13'),
(26, 'sample', 'percentage', 10.00, 'we', 1, '2025-10-23', '2025-11-23', 1, 1073, 56, NULL, 0, NULL, '2025-10-23 01:56:39', '2025-10-23 01:56:39');

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
(1, 1, 'Ground Floor', NULL, 1, '2025-10-14 01:52:17', '2025-10-14 01:52:17'),
(2, 2, 'First Floor', NULL, 1, '2025-10-14 01:52:17', '2025-10-14 01:52:17'),
(3, 3, 'Second Floor', NULL, 1, '2025-10-14 01:52:17', '2025-10-14 01:52:17'),
(4, 4, 'Third Floor', NULL, 1, '2025-10-14 01:52:17', '2025-10-14 01:52:17'),
(5, 5, 'Fourth Floor', NULL, 1, '2025-10-14 01:52:17', '2025-10-14 01:52:17'),
(6, 6, 'Fifth Floor', NULL, 1, '2025-10-14 01:52:17', '2025-10-14 01:52:17');

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
  `loyalty_tier` enum('silver','gold','platinum') DEFAULT NULL,
  `preferences` text DEFAULT NULL,
  `service_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `loyalty_points` int(11) DEFAULT 0,
  `loyalty_join_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `guests`
--

INSERT INTO `guests` (`id`, `first_name`, `last_name`, `email`, `phone`, `address`, `id_type`, `id_number`, `date_of_birth`, `nationality`, `is_vip`, `loyalty_tier`, `preferences`, `service_notes`, `created_at`, `updated_at`, `loyalty_points`, `loyalty_join_date`) VALUES
(51, 'John', 'Doe', 'john.doe@email.com', '+1234567890', NULL, 'passport', '123456789', NULL, NULL, 0, 'gold', NULL, NULL, '2025-10-21 06:09:29', '2025-10-22 05:48:42', 0, '2024-01-15 00:00:00'),
(52, 'Jane', 'Smith', 'jane.smith@email.com', '+1234567891', NULL, 'passport', '123456790', NULL, NULL, 1, 'silver', NULL, NULL, '2025-10-21 06:09:30', '2025-10-22 05:48:42', 0, '2024-03-22 00:00:00'),
(53, 'Michael', 'Johnson', 'michael.johnson@email.com', '+1234567892', NULL, 'driver_license', '123456791', NULL, NULL, 0, 'silver', NULL, NULL, '2025-10-21 06:09:30', '2025-10-22 06:39:59', 0, '2024-06-15 00:00:00'),
(54, 'Sarah', 'Williams', 'sarah.williams@email.com', '+1234567893', NULL, 'passport', '123456792', NULL, NULL, 1, 'gold', NULL, NULL, '2025-10-21 06:09:30', '2025-10-22 05:48:42', 0, '2023-11-08 00:00:00'),
(55, 'David', 'Brown', 'david.brown@email.com', '+1234567894', NULL, 'national_id', '123456793', NULL, NULL, 0, 'silver', NULL, NULL, '2025-10-21 06:09:30', '2025-10-22 05:48:43', 0, '2024-05-10 00:00:00'),
(56, 'Lisa', 'Davis', 'lisa.davis@email.com', '+1234567895', NULL, 'driver_license', '123456794', NULL, NULL, 0, 'platinum', NULL, NULL, '2025-10-21 06:09:30', '2025-10-22 05:48:43', 0, '2023-08-15 00:00:00'),
(57, 'Robert', 'Wilson', 'robert.wilson@email.com', '+1234567896', NULL, 'passport', '123456795', NULL, NULL, 1, 'gold', NULL, NULL, '2025-10-21 06:09:30', '2025-10-22 06:40:00', 0, '2024-02-20 00:00:00'),
(58, 'Emily', 'Moore', 'emily.moore@email.com', '+1234567897', NULL, 'national_id', '123456796', NULL, NULL, 0, 'silver', NULL, NULL, '2025-10-21 06:09:30', '2025-10-22 05:48:44', 0, '2024-07-03 00:00:00'),
(59, 'James', 'Taylor', 'james.taylor@email.com', '+1234567898', NULL, 'driver_license', '123456797', NULL, NULL, 1, 'silver', NULL, NULL, '2025-10-21 06:09:30', '2025-10-22 06:40:01', 0, '2024-08-10 00:00:00'),
(60, 'Maria', 'Anderson', 'maria.anderson@email.com', '+1234567899', NULL, 'passport', '123456798', NULL, NULL, 1, 'platinum', NULL, NULL, '2025-10-21 06:09:30', '2025-10-22 06:40:01', 0, '2023-12-05 00:00:00'),
(61, 'sample12333', 'sample', 'sample21@gmail.com', '7456735463', 'qweqwe', 'driver_license', 'qweq', '2001-10-21', 'Filipino', 1, 'silver', 'qwe', 'qweq', '2025-10-21 06:18:55', '2025-10-22 07:15:37', 0, '2025-10-22 15:15:37'),
(62, 'marlou', 'acharon1612', 'acharon@gmail.com', '12564643', '12312', 'driver_license', '16585967', '2002-10-21', 'Filipino', 1, NULL, '1263', 'qweqwe', '2025-10-22 08:25:37', '2025-10-22 08:25:37', 0, '2025-10-22 16:25:37'),
(63, 'h2124', '125', 'ad1231min@example.com', '09195512041', 'qweq', 'driver_license', '125274', '2001-10-21', 'Filipino', 1, NULL, 'weq', 'qwe', '2025-10-22 08:51:09', '2025-10-22 08:51:09', 0, '2025-10-22 16:51:09'),
(64, 'jan aron', 'fajardo', 'janaronfajardo65@gmail.com', '09123456789', 'phaseone prk maunlad brgy apopong general santos city', 'national_id', '123364', '2006-01-16', 'filipino', 1, NULL, '100 floor', 'my guest want is breakfast and lunch for beverages milk then amenities ', '2025-10-25 05:28:19', '2025-10-25 05:28:19', 0, '2025-10-25 13:28:19');

-- --------------------------------------------------------

--
-- Table structure for table `guest_feedback`
--

CREATE TABLE `guest_feedback` (
  `id` int(11) NOT NULL,
  `guest_id` int(11) NOT NULL,
  `reservation_id` int(11) DEFAULT NULL,
  `feedback_type` varchar(50) NOT NULL,
  `category` varchar(50) NOT NULL,
  `rating` decimal(3,1) DEFAULT NULL,
  `comments` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `guest_feedback`
--

INSERT INTO `guest_feedback` (`id`, `guest_id`, `reservation_id`, `feedback_type`, `category`, `rating`, `comments`, `created_at`) VALUES
(1, 51, NULL, 'compliment', 'service', 5.0, 'Great service!', '2025-10-21 06:25:08'),
(2, 51, NULL, 'compliment', 'service', 5.0, 'Test feedback from debug script', '2025-10-21 06:31:38'),
(3, 51, NULL, 'compliment', 'service', 5.0, 'Test feedback', '2025-10-21 06:35:47'),
(4, 51, NULL, 'compliment', 'service', 5.0, 'Test feedback via direct database', '2025-10-21 06:43:36'),
(5, 51, NULL, 'compliment', 'service', 5.0, 'Test feedback with session bypass', '2025-10-21 06:44:33'),
(6, 51, NULL, 'compliment', 'service', 5.0, 'Test feedback via HTTP', '2025-10-21 06:44:58'),
(7, 51, NULL, 'compliment', 'service', 5.0, 'Test feedback database insert', '2025-10-21 06:46:55'),
(8, 51, NULL, 'compliment', 'service', 5.0, 'Test feedback via HTTP', '2025-10-21 06:47:16'),
(9, 51, NULL, 'compliment', 'service', 5.0, 'Test feedback via HTTP', '2025-10-21 06:47:59'),
(10, 51, NULL, 'compliment', 'service', 5.0, 'Test feedback via HTTP', '2025-10-21 06:50:34'),
(11, 51, NULL, 'compliment', 'service', 5.0, 'Test feedback via HTTP', '2025-10-21 06:51:01'),
(12, 51, NULL, 'compliment', 'service', 5.0, 'Test feedback', '2025-10-21 06:52:53'),
(13, 51, NULL, 'compliment', 'service', 5.0, 'Test feedback', '2025-10-21 06:56:09'),
(14, 61, NULL, 'suggestion', 'dining', 3.0, 'w', '2025-10-21 06:56:20'),
(15, 61, NULL, 'complaint', 'facilities', 3.0, 'Excellent service!', '2025-09-22 07:17:02'),
(16, 58, NULL, 'compliment', 'room', 5.0, 'Could use some improvements', '2025-09-26 07:17:02'),
(17, 56, NULL, 'complaint', 'staff', 5.0, 'Not satisfied with the service', '2025-10-12 07:17:02'),
(18, 52, NULL, 'compliment', 'dining', 1.0, 'Good value for money', '2025-10-16 07:17:02'),
(19, 54, NULL, 'suggestion', 'room', 5.0, 'Excellent service!', '2025-09-30 07:17:02'),
(20, 59, NULL, 'suggestion', 'dining', 4.0, 'Will definitely come back', '2025-09-29 07:17:02'),
(21, 51, NULL, 'complaint', 'facilities', 3.0, 'Room was clean and comfortable', '2025-10-13 07:17:02'),
(22, 58, NULL, 'complaint', 'dining', 4.0, 'Average experience', '2025-10-01 07:17:02'),
(23, 59, NULL, 'compliment', 'staff', 3.0, 'Excellent service!', '2025-09-26 07:17:03'),
(24, 57, NULL, 'suggestion', 'staff', 3.0, 'Could use some improvements', '2025-10-21 07:17:03'),
(25, 60, NULL, 'compliment', 'dining', 3.0, 'Room was clean and comfortable', '2025-10-17 07:17:03'),
(26, 56, NULL, 'suggestion', 'dining', 5.0, 'Average experience', '2025-09-25 07:17:03'),
(27, 57, NULL, 'suggestion', 'dining', 5.0, 'Great experience overall', '2025-10-10 07:17:03'),
(28, 54, NULL, 'suggestion', 'staff', 5.0, 'Will definitely come back', '2025-09-29 07:17:03'),
(29, 59, NULL, 'compliment', 'dining', 4.0, 'Great experience overall', '2025-10-17 07:17:03'),
(30, 60, NULL, 'compliment', 'dining', 5.0, 'Not satisfied with the service', '2025-10-08 07:17:03'),
(31, 60, NULL, 'suggestion', 'staff', 2.0, 'Could use some improvements', '2025-10-15 07:17:04'),
(32, 61, NULL, 'suggestion', 'service', 4.0, 'Food was delicious', '2025-10-21 07:17:04'),
(33, 57, NULL, 'compliment', 'dining', 4.0, 'Food was delicious', '2025-10-09 07:17:04'),
(34, 57, NULL, 'compliment', 'facilities', 5.0, 'Excellent service!', '2025-10-02 07:17:04'),
(35, 51, NULL, 'suggestion', 'service', 4.0, 'Room was clean and comfortable', '2025-10-09 07:17:04'),
(36, 54, NULL, 'complaint', 'facilities', 2.0, 'Average experience', '2025-09-26 07:17:04'),
(37, 61, NULL, 'suggestion', 'service', 2.0, 'Average experience', '2025-10-03 07:17:04'),
(38, 53, NULL, 'suggestion', 'service', 1.0, 'Will definitely come back', '2025-09-25 07:17:04'),
(39, 52, NULL, 'suggestion', 'room', 2.0, 'Excellent service!', '2025-10-20 07:17:05'),
(40, 59, NULL, 'suggestion', 'service', 1.0, 'Could use some improvements', '2025-10-15 07:17:05'),
(41, 56, NULL, 'suggestion', 'staff', 4.0, 'Average experience', '2025-10-07 07:17:05'),
(42, 57, NULL, 'compliment', 'service', 1.0, 'Average experience', '2025-10-08 07:17:05'),
(43, 60, NULL, 'compliment', 'room', 5.0, 'Good value for money', '2025-09-22 07:17:05'),
(44, 53, NULL, 'complaint', 'service', 1.0, 'Will definitely come back', '2025-10-13 07:17:05'),
(45, 8, 10, 'complaint', 'dining', 5.0, 'Concierge was very knowledgeable', '2025-10-13 07:18:04'),
(46, 12, 10, 'compliment', 'dining', 5.0, 'Concierge was very knowledgeable', '2025-09-26 07:18:04'),
(47, 6, 6, 'compliment', 'room', 1.0, 'Could use some improvements in the bathroom', '2025-10-06 07:18:04'),
(48, 1, 3, 'suggestion', 'room', 5.0, 'Could use some improvements in the bathroom', '2025-10-21 07:18:04'),
(49, 20, 1, 'compliment', 'facilities', 5.0, 'Good value for money, exceeded expectations', '2025-10-04 07:18:05'),
(50, 19, 5, 'complaint', 'facilities', 4.0, 'Pool area was fantastic', '2025-10-21 07:18:05'),
(51, 12, 7, 'complaint', 'service', 3.0, 'Excellent service and very friendly staff!', '2025-10-21 07:18:05'),
(52, 2, 6, 'compliment', 'dining', 5.0, 'Excellent service and very friendly staff!', '2025-10-10 07:18:05'),
(53, 3, 2, 'complaint', 'facilities', 5.0, 'Quick check-in and check-out process', '2025-10-16 07:18:05'),
(54, 8, 7, 'complaint', 'dining', 1.0, 'Excellent service and very friendly staff!', '2025-09-27 07:18:06'),
(55, 11, 10, 'compliment', 'facilities', 3.0, 'Concierge was very knowledgeable', '2025-09-27 07:18:06'),
(56, 3, 9, 'compliment', 'dining', 4.0, 'Not satisfied with the service quality', '2025-10-10 07:18:06'),
(57, 16, 5, 'compliment', 'dining', 5.0, 'Staff was very helpful and accommodating', '2025-10-13 07:18:06'),
(58, 6, 3, 'compliment', 'dining', 2.0, 'Room was clean and comfortable, perfect location', '2025-10-03 07:18:06'),
(59, 15, 10, 'suggestion', 'dining', 5.0, 'Food was delicious, especially the breakfast', '2025-10-12 07:18:06'),
(60, 19, 3, 'complaint', 'facilities', 5.0, 'Not satisfied with the service quality', '2025-09-24 07:18:06'),
(61, 5, 9, 'suggestion', 'room', 5.0, 'Staff was very helpful and accommodating', '2025-09-30 07:18:06'),
(62, 4, 1, 'suggestion', 'facilities', 3.0, 'Not satisfied with the service quality', '2025-10-11 07:18:06'),
(63, 10, 10, 'complaint', 'facilities', 3.0, 'Pool area was fantastic', '2025-10-04 07:18:07'),
(64, 20, 7, 'compliment', 'service', 5.0, 'Pool area was fantastic', '2025-10-09 07:18:07'),
(65, 14, 1, 'compliment', 'room', 2.0, 'Could use some improvements in the bathroom', '2025-10-09 07:18:07'),
(66, 1, 2, 'compliment', 'service', 5.0, 'Quick check-in and check-out process', '2025-10-06 07:18:07'),
(67, 11, 9, 'complaint', 'facilities', 3.0, 'Pool area was fantastic', '2025-09-25 07:18:07'),
(68, 7, 2, 'suggestion', 'room', 4.0, 'Quick check-in and check-out process', '2025-09-25 07:18:07'),
(69, 6, 2, 'complaint', 'room', 5.0, 'Room service was prompt and tasty', '2025-10-21 07:18:07'),
(70, 15, 5, 'suggestion', 'staff', 4.0, 'Food was delicious, especially the breakfast', '2025-09-30 07:18:07'),
(71, 3, 5, 'complaint', 'dining', 4.0, 'Food was delicious, especially the breakfast', '2025-10-04 07:18:07'),
(72, 15, 4, 'suggestion', 'dining', 5.0, 'Concierge was very knowledgeable', '2025-09-26 07:18:08'),
(73, 10, 9, 'suggestion', 'room', 5.0, 'Food was delicious, especially the breakfast', '2025-10-07 07:18:08'),
(74, 9, 7, 'suggestion', 'dining', 4.0, 'Average experience, nothing special', '2025-10-06 07:18:08'),
(75, 18, 1, 'suggestion', 'facilities', 5.0, 'Room was clean and comfortable, perfect location', '2025-10-08 07:18:08'),
(76, 16, 2, 'compliment', 'facilities', 1.0, 'Beautiful room with amazing views', '2025-09-28 07:18:08'),
(77, 3, 4, 'suggestion', 'dining', 1.0, 'Staff was very helpful and accommodating', '2025-10-03 07:18:08'),
(78, 3, 9, 'suggestion', 'room', 4.0, 'Beautiful room with amazing views', '2025-10-14 07:18:08'),
(79, 15, 2, 'suggestion', 'service', 5.0, 'Great experience overall, will definitely return', '2025-10-15 07:18:08'),
(80, 3, 3, 'compliment', 'staff', 5.0, 'Room was clean and comfortable, perfect location', '2025-10-09 07:18:08'),
(81, 6, 2, 'complaint', 'staff', 1.0, 'Not satisfied with the service quality', '2025-10-09 07:18:08'),
(82, 2, 8, 'compliment', 'facilities', 4.0, 'Will definitely come back, highly recommended', '2025-09-28 07:18:08'),
(83, 3, 8, 'complaint', 'dining', 4.0, 'Food was delicious, especially the breakfast', '2025-10-15 07:18:08'),
(84, 20, 3, 'complaint', 'service', 4.0, 'Great experience overall, will definitely return', '2025-10-20 07:18:08'),
(85, 3, 3, 'suggestion', 'dining', 4.0, 'Food was delicious, especially the breakfast', '2025-09-23 07:18:08'),
(86, 6, 7, 'complaint', 'staff', 2.0, 'Average experience, nothing special', '2025-10-16 07:18:08'),
(87, 9, 10, 'complaint', 'service', 3.0, 'Not satisfied with the service quality', '2025-10-19 07:18:08'),
(88, 10, 4, 'suggestion', 'facilities', 2.0, 'Great experience overall, will definitely return', '2025-10-10 07:18:08'),
(89, 15, 2, 'complaint', 'facilities', 5.0, 'Room service was prompt and tasty', '2025-10-15 07:18:09'),
(90, 1, 2, 'suggestion', 'room', 5.0, 'Room was clean and comfortable, perfect location', '2025-10-18 07:18:09'),
(91, 11, 2, 'compliment', 'dining', 4.0, 'Average experience, nothing special', '2025-09-25 07:18:09'),
(92, 10, 9, 'compliment', 'service', 1.0, 'Average experience, nothing special', '2025-10-09 07:18:09'),
(93, 10, 2, 'suggestion', 'dining', 5.0, 'Staff was very helpful and accommodating', '2025-10-04 07:18:09'),
(94, 11, 4, 'suggestion', 'service', 4.0, 'Room was clean and comfortable, perfect location', '2025-10-16 07:18:09'),
(95, 61, NULL, 'complaint', 'service', 5.0, 'wew', '2025-10-22 03:52:51');

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_audited` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hotel_rooms`
--

INSERT INTO `hotel_rooms` (`id`, `room_number`, `floor_id`, `room_type`, `status`, `max_occupancy`, `description`, `active`, `created_at`, `updated_at`, `last_audited`) VALUES
(1, '101', 2, 'standard', 'available', 2, NULL, 1, '2025-10-01 15:41:11', '2025-10-01 15:41:11', NULL),
(2, '102', 2, 'standard', 'available', 2, NULL, 1, '2025-10-01 15:41:11', '2025-10-01 15:41:11', NULL),
(3, '103', 2, 'standard', 'occupied', 2, NULL, 1, '2025-10-01 15:41:11', '2025-10-14 07:05:07', '2025-10-14 07:05:07'),
(4, '201', 3, 'standard', 'available', 2, NULL, 1, '2025-10-01 15:41:11', '2025-10-01 15:41:11', NULL),
(5, '202', 3, 'standard', 'maintenance', 2, NULL, 1, '2025-10-01 15:41:11', '2025-10-01 15:41:11', NULL),
(6, '301', 4, 'premium', 'available', 4, NULL, 1, '2025-10-01 15:41:11', '2025-10-01 15:41:11', NULL),
(7, '302', 4, 'premium', 'occupied', 4, NULL, 1, '2025-10-01 15:41:11', '2025-10-01 15:41:11', NULL),
(8, '401', 5, 'executive', 'available', 6, NULL, 1, '2025-10-01 15:41:11', '2025-10-01 15:41:11', NULL),
(9, '501', 5, 'penthouse', 'available', 8, NULL, 1, '2025-10-01 15:41:11', '2025-10-01 15:41:11', NULL);

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
(1, 47, 'turn_down', 'pending', 1072, '2025-10-21 13:54:00', NULL, 'wew', 1029, '2025-10-21 05:52:28', '2025-10-22 00:19:25'),
(2, 48, 'turn_down', 'pending', 1075, '2025-10-21 13:54:00', NULL, 'wew', 1029, '2025-10-21 05:52:28', '2025-10-22 00:19:25'),
(3, 50, 'maintenance', 'pending', 1072, '2025-10-22 20:18:00', NULL, 'ww', 1073, '2025-10-22 00:16:44', '2025-10-22 00:16:44'),
(4, 50, 'maintenance', 'pending', 1072, '2025-10-22 20:18:00', NULL, 'ww', 1073, '2025-10-22 00:16:44', '2025-10-22 00:16:44'),
(5, 47, 'daily_cleaning', 'in_progress', 1072, NULL, NULL, NULL, 1073, '2025-10-22 00:20:07', '2025-10-22 00:20:08'),
(6, 48, 'turn_down', 'pending', 1075, '2025-10-22 08:31:00', NULL, 'ww', 1073, '2025-10-22 00:28:11', '2025-10-22 00:28:11'),
(7, 48, 'turn_down', 'pending', 1075, '2025-10-22 08:31:00', NULL, 'ww', 1073, '2025-10-22 00:28:12', '2025-10-22 00:28:12'),
(8, 48, 'turn_down', 'pending', 1075, '2025-10-22 08:31:00', NULL, 'ww', 1073, '2025-10-22 00:28:51', '2025-10-22 00:28:51'),
(9, 48, 'turn_down', 'pending', 1075, '2025-10-22 08:31:00', NULL, 'ww', 1073, '2025-10-22 00:28:52', '2025-10-22 00:28:52'),
(10, 47, 'daily_cleaning', 'in_progress', 1072, '2025-10-22 10:00:00', NULL, 'Test unassigned task', 1073, '2025-10-22 00:32:44', '2025-10-22 00:58:32'),
(11, 48, 'inspection', 'completed', 1075, '2025-10-22 23:57:00', NULL, 'wew2', 1073, '2025-10-22 00:58:03', '2025-10-22 01:20:12'),
(12, 48, 'inspection', 'pending', 1075, '2025-10-22 23:57:00', NULL, 'wew2', 1073, '2025-10-22 00:58:03', '2025-10-22 00:58:03'),
(13, 49, 'deep_cleaning', 'completed', 1075, '2025-10-22 21:22:00', NULL, 'www', 1073, '2025-10-22 01:21:26', '2025-10-22 01:21:40'),
(14, 49, 'deep_cleaning', 'pending', 1075, '2025-10-22 21:22:00', NULL, 'www', 1073, '2025-10-22 01:21:26', '2025-10-22 01:21:26'),
(15, 49, 'deep_cleaning', 'completed', 1075, '2025-10-22 21:22:00', NULL, 'www', 1073, '2025-10-22 01:21:43', '2025-10-22 01:31:15'),
(16, 49, 'deep_cleaning', 'completed', 1075, '2025-10-22 21:22:00', NULL, 'www', 1073, '2025-10-22 01:21:43', '2025-10-22 01:33:54');

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
(14, 'Cleaning Supplies', 'Housekeeping cleaning products and supplies', '2025-10-21 06:09:30'),
(15, 'Amenities', 'Guest amenities and toiletries', '2025-10-21 06:09:30'),
(16, 'Maintenance', 'Maintenance tools and equipment', '2025-10-21 06:09:30'),
(17, 'Food & Beverage', 'Restaurant and room service items', '2025-10-21 06:09:30'),
(18, 'Linens', 'Bed linens, towels, and fabric items', '2025-10-21 06:09:30');

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
  `description` text DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_pos_product` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','inactive','discontinued') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_items`
--

INSERT INTO `inventory_items` (`id`, `item_name`, `sku`, `category_id`, `current_stock`, `minimum_stock`, `maximum_stock`, `unit_price`, `cost_price`, `unit`, `supplier`, `location`, `barcode`, `image`, `description`, `last_updated`, `created_at`, `is_pos_product`, `status`) VALUES
(50, 'Toilet Paper', 'TP001', 1, 100, 20, NULL, 2.50, 0.00, 'pcs', NULL, NULL, NULL, NULL, NULL, '2025-10-21 06:09:30', '2025-10-21 06:09:30', 0, 'active'),
(51, 'Hand Soap', 'HS001', 1, 50, 10, NULL, 5.00, 0.00, 'pcs', NULL, NULL, NULL, NULL, NULL, '2025-10-21 06:09:30', '2025-10-21 06:09:30', 0, 'active'),
(52, 'Shampoo', 'SH001', 2, 200, 30, NULL, 3.00, 0.00, 'pcs', NULL, NULL, NULL, NULL, NULL, '2025-10-21 06:09:30', '2025-10-21 06:09:30', 0, 'active'),
(53, 'Conditioner', 'CO001', 2, 200, 30, NULL, 3.00, 0.00, 'pcs', NULL, NULL, NULL, NULL, NULL, '2025-10-21 06:09:31', '2025-10-21 06:09:31', 0, 'active'),
(54, 'Screwdriver Set', 'SD001', 3, 5, 2, NULL, 25.00, 0.00, 'pcs', NULL, NULL, NULL, NULL, NULL, '2025-10-21 06:09:31', '2025-10-21 06:09:31', 0, 'active'),
(55, 'Wrench Set', 'WS001', 3, 3, 1, NULL, 45.00, 0.00, 'pcs', NULL, NULL, NULL, NULL, NULL, '2025-10-21 06:09:31', '2025-10-21 06:09:31', 0, 'active'),
(56, 'Coffee', 'CF001', 4, 20, 5, NULL, 15.00, 0.00, 'pcs', NULL, NULL, NULL, NULL, NULL, '2025-10-21 06:09:31', '2025-10-21 06:09:31', 0, 'active'),
(57, 'Tea Bags', 'TB001', 4, 100, 20, NULL, 0.50, 0.00, 'pcs', NULL, NULL, NULL, NULL, NULL, '2025-10-21 06:09:31', '2025-10-21 06:09:31', 0, 'active'),
(58, 'Towels', 'TW001', 5, 50, 10, NULL, 12.00, 0.00, 'pcs', NULL, NULL, NULL, NULL, NULL, '2025-10-21 06:09:31', '2025-10-21 06:09:31', 0, 'active'),
(59, 'Bed Sheets', 'BS001', 5, 30, 5, NULL, 25.00, 0.00, 'pcs', NULL, NULL, NULL, NULL, NULL, '2025-10-21 06:09:31', '2025-10-21 06:09:31', 0, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_journal_entries`
--

CREATE TABLE `inventory_journal_entries` (
  `id` int(11) NOT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `account_code` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `debit_amount` decimal(15,2) DEFAULT 0.00,
  `credit_amount` decimal(15,2) DEFAULT 0.00,
  `status` enum('pending','posted','reversed') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `inventory_journal_entries`
--

INSERT INTO `inventory_journal_entries` (`id`, `reference_number`, `account_code`, `description`, `debit_amount`, `credit_amount`, `status`, `created_at`, `updated_at`) VALUES
(1, 'INV-001', '1200', 'Inventory Purchase - Bath Towels', 5000.00, 0.00, 'posted', '2025-10-17 07:10:59', '2025-10-17 07:10:59'),
(2, 'INV-002', '5000', 'COGS - Room Service Supplies', 0.00, 2500.00, 'posted', '2025-10-17 07:10:59', '2025-10-17 07:10:59'),
(3, 'INV-003', '2000', 'Accounts Payable - Supplier Invoice', 0.00, 5000.00, 'pending', '2025-10-17 07:10:59', '2025-10-17 07:10:59'),
(4, 'INV-004', '5100', 'Supplies Expense - Cleaning Materials', 1200.00, 0.00, 'posted', '2025-10-17 07:10:59', '2025-10-17 07:10:59');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_question_options`
--

CREATE TABLE `inventory_question_options` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_text` text NOT NULL,
  `option_value` varchar(255) NOT NULL,
  `option_order` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_question_options`
--

INSERT INTO `inventory_question_options` (`id`, `question_id`, `option_text`, `option_value`, `option_order`, `created_at`) VALUES
(169, 43, 'Item name, SKU, and category', 'item_name', 1, '2025-10-24 04:25:57'),
(170, 43, 'Only the item name', 'name_only', 2, '2025-10-24 04:25:57'),
(171, 43, 'Just the SKU number', 'sku_only', 3, '2025-10-24 04:25:57'),
(172, 43, 'Only the category', 'category_only', 4, '2025-10-24 04:25:58'),
(173, 44, 'Item name', 'name', 1, '2025-10-24 04:25:58'),
(174, 44, 'SKU (Stock Keeping Unit)', 'sku', 2, '2025-10-24 04:25:58'),
(175, 44, 'Description', 'description', 3, '2025-10-24 04:25:58'),
(176, 44, 'Category', 'category', 4, '2025-10-24 04:25:58'),
(177, 45, 'Search for existing similar items', 'search_existing', 1, '2025-10-24 04:25:58'),
(178, 45, 'Add it immediately', 'add_immediately', 2, '2025-10-24 04:25:58'),
(179, 45, 'Ask a colleague', 'ask_colleague', 3, '2025-10-24 04:25:58'),
(180, 45, 'Skip the check', 'skip_check', 4, '2025-10-24 04:25:58'),
(181, 46, 'Receipt, Issue, and Adjustment', 'receipt_issue_adjustment', 1, '2025-10-24 04:25:58'),
(182, 46, 'Add, Delete, and Modify', 'add_delete_modify', 2, '2025-10-24 04:25:58'),
(183, 46, 'Buy, Sell, and Return', 'buy_sell_return', 3, '2025-10-24 04:25:58'),
(184, 46, 'In, Out, and Transfer', 'in_out_transfer', 4, '2025-10-24 04:25:58'),
(185, 47, 'When a physical count shows discrepancy', 'discrepancy_found', 1, '2025-10-24 04:25:59'),
(186, 47, 'Every day', 'every_day', 2, '2025-10-24 04:25:59'),
(187, 47, 'Only at month end', 'month_end', 3, '2025-10-24 04:25:59'),
(188, 47, 'Never', 'never', 4, '2025-10-24 04:25:59'),
(189, 48, 'Item, quantity, and reason', 'all_required', 1, '2025-10-24 04:25:59'),
(190, 48, 'Only item and quantity', 'item_quantity', 2, '2025-10-24 04:25:59'),
(191, 48, 'Just the item name', 'item_only', 3, '2025-10-24 04:25:59'),
(192, 48, 'Only the quantity', 'quantity_only', 4, '2025-10-24 04:25:59'),
(193, 49, 'Check current stock levels', 'check_stock', 1, '2025-10-24 04:25:59'),
(194, 49, 'Submit the request immediately', 'submit_immediately', 2, '2025-10-24 04:25:59'),
(195, 49, 'Ask your manager', 'ask_manager', 3, '2025-10-24 04:25:59'),
(196, 49, 'Wait for approval', 'wait_approval', 4, '2025-10-24 04:25:59'),
(197, 50, 'Routine restocking', 'routine_restock', 1, '2025-10-24 04:26:00'),
(198, 50, 'Emergency request', 'emergency', 2, '2025-10-24 04:26:00'),
(199, 50, 'Special event', 'special_event', 3, '2025-10-24 04:26:00'),
(200, 50, 'Damaged items', 'damaged', 4, '2025-10-24 04:26:00'),
(201, 51, 'Mark as urgent and provide justification', 'mark_urgent', 1, '2025-10-24 04:26:00'),
(202, 51, 'Submit multiple requests', 'multiple_requests', 2, '2025-10-24 04:26:00'),
(203, 51, 'Call the supplier directly', 'call_supplier', 3, '2025-10-24 04:26:00'),
(204, 51, 'Wait for regular processing', 'wait_regular', 4, '2025-10-24 04:26:00'),
(205, 52, 'Immediately after completing the room', 'immediately_after', 1, '2025-10-24 04:26:00'),
(206, 52, 'At the end of the day', 'end_day', 2, '2025-10-24 04:26:00'),
(207, 52, 'Once a week', 'weekly', 3, '2025-10-24 04:26:00'),
(208, 52, 'Only when requested', 'when_requested', 4, '2025-10-24 04:26:00'),
(209, 53, 'Report missing items immediately', 'report_missing', 1, '2025-10-24 04:26:00'),
(210, 53, 'Ignore and move on', 'ignore', 2, '2025-10-24 04:26:00'),
(211, 53, 'Wait for the next shift', 'wait_shift', 3, '2025-10-24 04:26:01'),
(212, 53, 'Ask the guest', 'ask_guest', 4, '2025-10-24 04:26:01'),
(213, 54, 'Record the damage and remove from inventory', 'record_damage', 1, '2025-10-24 04:26:01'),
(214, 54, 'Hide the damaged items', 'hide_items', 2, '2025-10-24 04:26:01'),
(215, 54, 'Continue using them', 'continue_using', 3, '2025-10-24 04:26:01'),
(216, 54, 'Blame the previous shift', 'blame_shift', 4, '2025-10-24 04:26:01'),
(217, 55, 'Inventory Status Report', 'inventory_status', 1, '2025-10-24 04:26:01'),
(218, 55, 'Transaction History', 'transaction_history', 2, '2025-10-24 04:26:01'),
(219, 55, 'Usage Report', 'usage_report', 3, '2025-10-24 04:26:01'),
(220, 55, 'Audit Trail', 'audit_trail', 4, '2025-10-24 04:26:01'),
(221, 56, 'Items need to be reordered', 'reorder_needed', 1, '2025-10-24 04:26:01'),
(222, 56, 'Items are out of stock', 'out_of_stock', 2, '2025-10-24 04:26:01'),
(223, 56, 'Items are overstocked', 'overstocked', 3, '2025-10-24 04:26:01'),
(224, 56, 'Items are discontinued', 'discontinued', 4, '2025-10-24 04:26:01'),
(225, 57, 'Daily for critical items', 'daily', 1, '2025-10-24 04:26:02'),
(226, 57, 'Weekly', 'weekly', 2, '2025-10-24 04:26:02'),
(227, 57, 'Monthly', 'monthly', 3, '2025-10-24 04:26:02'),
(228, 57, 'Only when needed', 'as_needed', 4, '2025-10-24 04:26:02'),
(229, 58, 'All inventory changes and user actions', 'all_changes', 1, '2025-10-24 04:26:02'),
(230, 58, 'Only deletions', 'deletions_only', 2, '2025-10-24 04:26:02'),
(231, 58, 'Only additions', 'additions_only', 3, '2025-10-24 04:26:02'),
(232, 58, 'Only modifications', 'modifications_only', 4, '2025-10-24 04:26:02'),
(233, 59, 'For compliance and tracking changes', 'compliance_tracking', 1, '2025-10-24 04:26:02'),
(234, 59, 'To increase system speed', 'increase_speed', 2, '2025-10-24 04:26:03'),
(235, 59, 'To reduce storage space', 'reduce_storage', 3, '2025-10-24 04:26:03'),
(236, 59, 'To improve user interface', 'improve_ui', 4, '2025-10-24 04:26:03'),
(237, 60, 'According to company policy (usually 1-7 years)', 'policy_period', 1, '2025-10-24 04:26:03'),
(238, 60, 'Only 30 days', '30_days', 2, '2025-10-24 04:26:03'),
(239, 60, 'Only 1 year', '1_year', 3, '2025-10-24 04:26:03'),
(240, 60, 'Indefinitely', 'indefinitely', 4, '2025-10-24 04:26:03'),
(241, 61, 'To prioritize items based on value and importance', 'prioritize_items', 1, '2025-10-24 04:37:25'),
(242, 61, 'To count inventory items alphabetically', 'alphabetical_count', 2, '2025-10-24 04:37:25'),
(243, 61, 'To organize items by category', 'category_organize', 3, '2025-10-24 04:37:25'),
(244, 61, 'To track item locations', 'track_locations', 4, '2025-10-24 04:37:25'),
(245, 62, 'Seasonal decomposition', 'seasonal_decomposition', 1, '2025-10-24 04:37:25'),
(246, 62, 'Simple moving average', 'moving_average', 2, '2025-10-24 04:37:25'),
(247, 62, 'Linear regression', 'linear_regression', 3, '2025-10-24 04:37:26'),
(248, 62, 'Exponential smoothing', 'exponential_smoothing', 4, '2025-10-24 04:37:26'),
(249, 63, 'How efficiently inventory is sold and replaced', 'efficiency', 1, '2025-10-24 04:37:26'),
(250, 63, 'How much inventory costs', 'cost', 2, '2025-10-24 04:37:26'),
(251, 63, 'How many items are in stock', 'stock_count', 3, '2025-10-24 04:37:26'),
(252, 63, 'How fast items are delivered', 'delivery_speed', 4, '2025-10-24 04:37:26'),
(253, 64, 'Analyze business requirements and access needs', 'analyze_requirements', 1, '2025-10-24 04:37:26'),
(254, 64, 'Create all users immediately', 'create_users', 2, '2025-10-24 04:37:26'),
(255, 64, 'Set up default permissions', 'default_permissions', 3, '2025-10-24 04:37:26'),
(256, 64, 'Configure system settings', 'system_settings', 4, '2025-10-24 04:37:26'),
(257, 65, 'Regular security audits and updates', 'regular_audits', 1, '2025-10-24 04:37:26'),
(258, 65, 'Using simple passwords', 'simple_passwords', 2, '2025-10-24 04:37:26'),
(259, 65, 'Sharing admin accounts', 'shared_accounts', 3, '2025-10-24 04:37:26'),
(260, 65, 'Disabling logging', 'disable_logging', 4, '2025-10-24 04:37:27'),
(261, 66, 'Create a system backup', 'backup_system', 1, '2025-10-24 04:37:27'),
(262, 66, 'Notify all users', 'notify_users', 2, '2025-10-24 04:37:27'),
(263, 66, 'Test in production', 'test_production', 3, '2025-10-24 04:37:27'),
(264, 66, 'Make changes immediately', 'immediate_changes', 4, '2025-10-24 04:37:27'),
(265, 67, 'System response time', 'response_time', 1, '2025-10-24 04:37:27'),
(266, 67, 'Number of users', 'user_count', 2, '2025-10-24 04:37:27'),
(267, 67, 'Database size', 'database_size', 3, '2025-10-24 04:37:27'),
(268, 67, 'Number of reports', 'report_count', 4, '2025-10-24 04:37:27'),
(269, 68, 'CPU usage, memory, disk space, and network', 'all_metrics', 1, '2025-10-24 04:37:27'),
(270, 68, 'Only CPU usage', 'cpu_only', 2, '2025-10-24 04:37:27'),
(271, 68, 'Only memory usage', 'memory_only', 3, '2025-10-24 04:37:27'),
(272, 68, 'Only disk space', 'disk_only', 4, '2025-10-24 04:37:27'),
(273, 69, 'Daily for critical systems', 'daily', 1, '2025-10-24 04:37:28'),
(274, 69, 'Weekly', 'weekly', 2, '2025-10-24 04:37:28'),
(275, 69, 'Monthly', 'monthly', 3, '2025-10-24 04:37:28'),
(276, 69, 'Only when problems occur', 'when_problems', 4, '2025-10-24 04:37:28'),
(277, 70, 'Analyze current inventory state and trends', 'analyze_current_state', 1, '2025-10-24 04:37:28'),
(278, 70, 'Set arbitrary targets', 'set_targets', 2, '2025-10-24 04:37:28'),
(279, 70, 'Purchase new equipment', 'purchase_equipment', 3, '2025-10-24 04:37:28'),
(280, 70, 'Hire more staff', 'hire_staff', 4, '2025-10-24 04:37:28'),
(281, 71, 'Accurate demand forecasting', 'demand_forecasting', 1, '2025-10-24 04:37:28'),
(282, 71, 'Current stock levels', 'current_stock', 2, '2025-10-24 04:37:28'),
(283, 71, 'Supplier prices', 'supplier_prices', 3, '2025-10-24 04:37:28'),
(284, 71, 'Storage capacity', 'storage_capacity', 4, '2025-10-24 04:37:28'),
(285, 72, 'Use multiple forecasting methods and compare results', 'multiple_methods', 1, '2025-10-24 04:37:28'),
(286, 72, 'Use only historical data', 'historical_only', 2, '2025-10-24 04:37:29'),
(287, 72, 'Guess based on intuition', 'intuition', 3, '2025-10-24 04:37:29'),
(288, 72, 'Copy from competitors', 'copy_competitors', 4, '2025-10-24 04:37:29'),
(289, 73, 'Segregation of duties and appropriate authorization levels', 'segregation_duties', 1, '2025-10-24 04:37:29'),
(290, 73, 'Speed over accuracy', 'speed_accuracy', 2, '2025-10-24 04:37:29'),
(291, 73, 'Single person approval', 'single_approval', 3, '2025-10-24 04:37:29'),
(292, 73, 'No documentation needed', 'no_documentation', 4, '2025-10-24 04:37:29'),
(293, 74, 'When approvers are temporarily unavailable', 'temporary_absence', 1, '2025-10-24 04:37:29'),
(294, 74, 'When you want to avoid responsibility', 'avoid_responsibility', 2, '2025-10-24 04:37:29'),
(295, 74, 'When processes are too slow', 'slow_processes', 3, '2025-10-24 04:37:29'),
(296, 74, 'When you want to reduce costs', 'reduce_costs', 4, '2025-10-24 04:37:29'),
(297, 75, 'All actions, decisions, and timestamps', 'all_actions', 1, '2025-10-24 04:37:29'),
(298, 75, 'Only approvals', 'approvals_only', 2, '2025-10-24 04:37:29'),
(299, 75, 'Only rejections', 'rejections_only', 3, '2025-10-24 04:37:29'),
(300, 75, 'Only final decisions', 'final_decisions', 4, '2025-10-24 04:37:30'),
(301, 76, 'Annual physical counts only', 'A', 1, '2025-10-24 04:46:21'),
(302, 76, 'Wait until stock is completely depleted', 'B', 2, '2025-10-24 04:46:21'),
(303, 76, 'To maximize storage space utilization', 'C', 3, '2025-10-24 04:46:21'),
(304, 76, 'Place a reorder immediately', 'D', 4, '2025-10-24 04:46:21'),
(305, 77, 'Wait until stock is completely depleted', 'A', 1, '2025-10-24 04:46:21'),
(306, 77, 'Annual physical counts only', 'B', 2, '2025-10-24 04:46:22'),
(307, 77, 'Regular cycle counting and automated tracking', 'C', 3, '2025-10-24 04:46:22'),
(308, 77, 'To maximize storage space utilization', 'D', 4, '2025-10-24 04:46:22'),
(309, 78, 'Annual physical counts only', 'A', 1, '2025-10-24 04:46:22'),
(310, 78, 'Monthly or quarterly depending on item value', 'B', 2, '2025-10-24 04:46:22'),
(311, 78, 'To maximize storage space utilization', 'C', 3, '2025-10-24 04:46:22'),
(312, 78, 'Wait until stock is completely depleted', 'D', 4, '2025-10-24 04:46:22'),
(313, 79, 'Annual physical counts only', 'A', 1, '2025-10-24 04:46:22'),
(314, 79, 'Wait until stock is completely depleted', 'B', 2, '2025-10-24 04:46:22'),
(315, 79, 'To maximize storage space utilization', 'C', 3, '2025-10-24 04:46:22'),
(316, 79, 'Monthly or quarterly depending on item value', 'D', 4, '2025-10-24 04:46:22'),
(317, 80, 'Annual physical counts only', 'A', 1, '2025-10-24 04:46:22'),
(318, 80, 'To maximize storage space utilization', 'B', 2, '2025-10-24 04:46:22'),
(319, 80, 'Wait until stock is completely depleted', 'C', 3, '2025-10-24 04:46:22'),
(320, 80, 'FIFO uses first-in-first-out, LIFO uses last-in-first-out', 'D', 4, '2025-10-24 04:46:23'),
(321, 81, 'Verify the room status and guest checkout', 'verify_room_status', 1, '2025-10-24 04:51:28'),
(322, 81, 'Start counting items immediately', 'start_counting', 2, '2025-10-24 04:51:28'),
(323, 81, 'Ask the guest about missing items', 'ask_guest', 3, '2025-10-24 04:51:28'),
(324, 81, 'Report to supervisor first', 'report_supervisor', 4, '2025-10-24 04:51:28'),
(325, 82, 'Report immediately with detailed documentation', 'immediate_report', 1, '2025-10-24 04:51:28'),
(326, 82, 'Wait until end of shift', 'end_shift', 2, '2025-10-24 04:51:28'),
(327, 82, 'Only report if expensive items', 'expensive_only', 3, '2025-10-24 04:51:28'),
(328, 82, 'Ignore small items', 'ignore_small', 4, '2025-10-24 04:51:28'),
(329, 83, 'Document the damage and remove from inventory', 'document_and_remove', 1, '2025-10-24 04:51:28'),
(330, 83, 'Leave them in the room', 'leave_room', 2, '2025-10-24 04:51:28'),
(331, 83, 'Hide them from view', 'hide_items', 3, '2025-10-24 04:51:28'),
(332, 83, 'Ask the guest to pay', 'ask_payment', 4, '2025-10-24 04:51:28'),
(333, 84, 'Before items run out completely', 'before_shortage', 1, '2025-10-24 04:51:29'),
(334, 84, 'Only when completely out of stock', 'completely_out', 2, '2025-10-24 04:51:29'),
(335, 84, 'At the end of each week', 'end_week', 3, '2025-10-24 04:51:29'),
(336, 84, 'When supervisor asks', 'supervisor_asks', 4, '2025-10-24 04:51:29'),
(337, 85, 'Item name, quantity, reason, and urgency', 'all_details', 1, '2025-10-24 04:51:29'),
(338, 85, 'Only item name and quantity', 'name_quantity', 2, '2025-10-24 04:51:29'),
(339, 85, 'Just the item name', 'name_only', 3, '2025-10-24 04:51:29'),
(340, 85, 'Only the quantity needed', 'quantity_only', 4, '2025-10-24 04:51:29'),
(341, 86, 'Mark as urgent and provide justification', 'mark_urgent', 1, '2025-10-24 04:51:30'),
(342, 86, 'Submit multiple requests', 'multiple_requests', 2, '2025-10-24 04:51:30'),
(343, 86, 'Call the supplier directly', 'call_supplier', 3, '2025-10-24 04:51:30'),
(344, 86, 'Wait for regular processing', 'wait_regular', 4, '2025-10-24 04:51:30'),
(345, 87, 'Early morning before guest activities', 'morning_before_guests', 1, '2025-10-24 04:51:30'),
(346, 87, 'During peak guest hours', 'peak_hours', 2, '2025-10-24 04:51:30'),
(347, 87, 'Late at night', 'late_night', 3, '2025-10-24 04:51:30'),
(348, 87, 'Whenever convenient', 'when_convenient', 4, '2025-10-24 04:51:30'),
(349, 88, 'Investigate the cause and report immediately', 'investigate_and_report', 1, '2025-10-24 04:51:30'),
(350, 88, 'Ignore small discrepancies', 'ignore_small', 2, '2025-10-24 04:51:30'),
(351, 88, 'Adjust counts without investigation', 'adjust_counts', 3, '2025-10-24 04:51:30'),
(352, 88, 'Wait for next check', 'wait_next', 4, '2025-10-24 04:51:30'),
(353, 89, 'Secure the items and report to security', 'secure_and_report', 1, '2025-10-24 04:51:31'),
(354, 89, 'Remove items immediately', 'remove_immediately', 2, '2025-10-24 04:51:31'),
(355, 89, 'Leave items where found', 'leave_items', 3, '2025-10-24 04:51:31'),
(356, 89, 'Ask other staff members', 'ask_staff', 4, '2025-10-24 04:51:31'),
(357, 90, 'Follow the standard room setup checklist', 'checklist_follow', 1, '2025-10-24 04:51:31'),
(358, 90, 'Set up based on personal preference', 'personal_preference', 2, '2025-10-24 04:51:31'),
(359, 90, 'Ask the guest what they want', 'ask_guest', 3, '2025-10-24 04:51:31'),
(360, 90, 'Copy the previous room setup', 'copy_previous', 4, '2025-10-24 04:51:31'),
(361, 91, 'Document the request and fulfill if possible', 'document_and_fulfill', 1, '2025-10-24 04:51:31'),
(362, 91, 'Ignore special requests', 'ignore_requests', 2, '2025-10-24 04:51:31'),
(363, 91, 'Only fulfill expensive requests', 'expensive_only', 3, '2025-10-24 04:51:32'),
(364, 91, 'Ask supervisor for every request', 'ask_supervisor', 4, '2025-10-24 04:51:32'),
(365, 92, 'Find suitable alternatives and document', 'find_alternatives', 1, '2025-10-24 04:51:32'),
(366, 92, 'Leave the room incomplete', 'leave_incomplete', 2, '2025-10-24 04:51:32'),
(367, 92, 'Wait for items to be restocked', 'wait_restock', 3, '2025-10-24 04:51:32'),
(368, 92, 'Ask the guest to provide items', 'ask_guest_provide', 4, '2025-10-24 04:51:32'),
(369, 93, 'Secure the item and document all details', 'secure_and_document', 1, '2025-10-24 04:51:32'),
(370, 93, 'Leave the item in the room', 'leave_room', 2, '2025-10-24 04:51:32'),
(371, 93, 'Take the item to your supervisor', 'take_supervisor', 3, '2025-10-24 04:51:32'),
(372, 93, 'Dispose of the item', 'dispose_item', 4, '2025-10-24 04:51:32'),
(373, 94, 'According to hotel policy (usually 30-90 days)', 'policy_period', 1, '2025-10-24 04:51:33'),
(374, 94, 'Only 1 week', 'one_week', 2, '2025-10-24 04:51:33'),
(375, 94, 'Until the guest returns', 'until_return', 3, '2025-10-24 04:51:33'),
(376, 94, 'Indefinitely', 'indefinitely', 4, '2025-10-24 04:51:33'),
(377, 95, 'Complete description, location found, date, and room number', 'complete_details', 1, '2025-10-24 04:51:33'),
(378, 95, 'Only item description', 'description_only', 2, '2025-10-24 04:51:33'),
(379, 95, 'Just the room number', 'room_only', 3, '2025-10-24 04:51:33'),
(380, 95, 'Only the date found', 'date_only', 4, '2025-10-24 04:51:33'),
(381, 96, 'Any situation that impacts guest safety or comfort', 'guest_safety_impact', 1, '2025-10-24 04:51:33'),
(382, 96, 'Only when completely out of stock', 'completely_out', 2, '2025-10-24 04:51:33'),
(383, 96, 'When supervisor says it is', 'supervisor_says', 3, '2025-10-24 04:51:33'),
(384, 96, 'Only for expensive items', 'expensive_items', 4, '2025-10-24 04:51:33'),
(385, 97, 'Escalate immediately to management and find alternatives', 'immediate_escalation', 1, '2025-10-24 04:51:33'),
(386, 97, 'Wait for regular approval process', 'regular_approval', 2, '2025-10-24 04:51:34'),
(387, 97, 'Handle it yourself', 'handle_self', 3, '2025-10-24 04:51:34'),
(388, 97, 'Ask other departments', 'ask_departments', 4, '2025-10-24 04:51:34'),
(389, 98, 'Communicate with guests and offer alternatives', 'communicate_alternatives', 1, '2025-10-24 04:51:34'),
(390, 98, 'Pretend everything is normal', 'pretend_normal', 2, '2025-10-24 04:51:34'),
(391, 98, 'Close the affected areas', 'close_areas', 3, '2025-10-24 04:51:34'),
(392, 98, 'Wait for supplies to arrive', 'wait_supplies', 4, '2025-10-24 04:51:34'),
(393, 99, 'Wait until stock is completely depleted', 'A', 1, '2025-10-25 05:23:50'),
(394, 99, 'To maximize storage space utilization', 'B', 2, '2025-10-25 05:23:50'),
(395, 99, 'Annual physical counts only', 'C', 3, '2025-10-25 05:23:50'),
(396, 99, 'Place a reorder immediately', 'D', 4, '2025-10-25 05:23:50'),
(397, 100, 'To maximize storage space utilization', 'A', 1, '2025-10-25 05:24:41'),
(398, 100, 'Turnover rate, carrying costs, and service level', 'B', 2, '2025-10-25 05:24:41'),
(399, 100, 'Wait until stock is completely depleted', 'C', 3, '2025-10-25 05:24:41'),
(400, 100, 'Annual physical counts only', 'D', 4, '2025-10-25 05:24:41'),
(401, 101, 'Annual physical counts only', 'A', 1, '2025-10-25 05:24:41'),
(402, 101, 'Through centralized systems with real-time visibility', 'B', 2, '2025-10-25 05:24:41'),
(403, 101, 'Wait until stock is completely depleted', 'C', 3, '2025-10-25 05:24:41'),
(404, 101, 'To maximize storage space utilization', 'D', 4, '2025-10-25 05:24:41'),
(405, 102, 'To maximize storage space utilization', 'A', 1, '2025-10-25 05:24:41'),
(406, 102, 'Annual physical counts only', 'B', 2, '2025-10-25 05:24:41'),
(407, 102, 'Wait until stock is completely depleted', 'C', 3, '2025-10-25 05:24:41'),
(408, 102, 'Implementing demand-driven replenishment strategies', 'D', 4, '2025-10-25 05:24:41'),
(409, 103, 'To maximize storage space utilization', 'A', 1, '2025-10-25 05:24:41'),
(410, 103, 'Wait until stock is completely depleted', 'B', 2, '2025-10-25 05:24:41'),
(411, 103, 'Turnover rate, carrying costs, and service level', 'C', 3, '2025-10-25 05:24:41'),
(412, 103, 'Annual physical counts only', 'D', 4, '2025-10-25 05:24:41'),
(413, 104, 'Wait until stock is completely depleted', 'A', 1, '2025-10-25 05:24:41'),
(414, 104, 'Annual physical counts only', 'B', 2, '2025-10-25 05:24:41'),
(415, 104, 'Turnover rate, carrying costs, and service level', 'C', 3, '2025-10-25 05:24:41'),
(416, 104, 'To maximize storage space utilization', 'D', 4, '2025-10-25 05:24:41'),
(417, 105, 'Wait until stock is completely depleted', 'A', 1, '2025-10-25 06:04:05'),
(418, 105, 'To maximize storage space utilization', 'B', 2, '2025-10-25 06:04:05'),
(419, 105, 'FIFO uses first-in-first-out, LIFO uses last-in-first-out', 'C', 3, '2025-10-25 06:04:05'),
(420, 105, 'Annual physical counts only', 'D', 4, '2025-10-25 06:04:05'),
(421, 106, 'Wait until stock is completely depleted', 'A', 1, '2025-10-25 06:04:05'),
(422, 106, 'Annual physical counts only', 'B', 2, '2025-10-25 06:04:05'),
(423, 106, 'To maximize storage space utilization', 'C', 3, '2025-10-25 06:04:05'),
(424, 106, 'To maintain optimal stock levels and reduce costs', 'D', 4, '2025-10-25 06:04:05'),
(425, 107, 'Regular cycle counting and automated tracking', 'A', 1, '2025-10-25 06:04:05'),
(426, 107, 'Wait until stock is completely depleted', 'B', 2, '2025-10-25 06:04:05'),
(427, 107, 'Annual physical counts only', 'C', 3, '2025-10-25 06:04:05'),
(428, 107, 'To maximize storage space utilization', 'D', 4, '2025-10-25 06:04:05'),
(429, 108, 'To maintain optimal stock levels and reduce costs', 'A', 1, '2025-10-25 06:04:05'),
(430, 108, 'Wait until stock is completely depleted', 'B', 2, '2025-10-25 06:04:05'),
(431, 108, 'To maximize storage space utilization', 'C', 3, '2025-10-25 06:04:05'),
(432, 108, 'Annual physical counts only', 'D', 4, '2025-10-25 06:04:05'),
(433, 109, 'To maintain optimal stock levels and reduce costs', 'A', 1, '2025-10-25 06:04:05'),
(434, 109, 'Annual physical counts only', 'B', 2, '2025-10-25 06:04:05'),
(435, 109, 'To maximize storage space utilization', 'C', 3, '2025-10-25 06:04:05'),
(436, 109, 'Wait until stock is completely depleted', 'D', 4, '2025-10-25 06:04:05');

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

--
-- Dumping data for table `inventory_requests`
--

INSERT INTO `inventory_requests` (`id`, `request_number`, `requested_by`, `department`, `priority`, `status`, `request_date`, `required_date`, `approved_by`, `approved_at`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'REQ-20251014-7682', 1033, 'housekeeping', 'high', 'pending', '2025-10-14 06:38:37', '2025-10-14', NULL, NULL, 'wew', '2025-10-14 06:38:37', '2025-10-14 06:38:37');

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

--
-- Dumping data for table `inventory_request_items`
--

INSERT INTO `inventory_request_items` (`id`, `request_id`, `item_id`, `quantity_requested`, `quantity_approved`, `quantity_issued`, `notes`, `created_at`) VALUES
(1, 1, 24, 10, NULL, 0, NULL, '2025-10-14 06:38:37');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_scenario_questions`
--

CREATE TABLE `inventory_scenario_questions` (
  `id` int(11) NOT NULL,
  `scenario_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `question_order` int(11) NOT NULL DEFAULT 1,
  `correct_answer` text NOT NULL,
  `explanation` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_scenario_questions`
--

INSERT INTO `inventory_scenario_questions` (`id`, `scenario_id`, `question`, `question_order`, `correct_answer`, `explanation`, `created_at`) VALUES
(43, 29, 'What is the most important information to include when adding a new inventory item?', 1, 'item_name', NULL, '2025-10-24 04:25:57'),
(44, 29, 'Which field is used to uniquely identify inventory items?', 2, 'sku', NULL, '2025-10-24 04:25:58'),
(45, 29, 'What should you do before adding a new item to ensure no duplicates?', 3, 'search_existing', NULL, '2025-10-24 04:25:58'),
(46, 30, 'What are the three main types of inventory transactions?', 1, 'receipt_issue_adjustment', NULL, '2025-10-24 04:25:58'),
(47, 30, 'When should you record an inventory adjustment?', 2, 'discrepancy_found', NULL, '2025-10-24 04:25:59'),
(48, 30, 'What information is required for a transaction entry?', 3, 'all_required', NULL, '2025-10-24 04:25:59'),
(49, 31, 'What is the first step when requesting supplies?', 1, 'check_stock', NULL, '2025-10-24 04:25:59'),
(50, 31, 'Which reason code should you use for routine restocking?', 2, 'routine_restock', NULL, '2025-10-24 04:25:59'),
(51, 31, 'How should you prioritize urgent supply requests?', 3, 'mark_urgent', NULL, '2025-10-24 04:26:00'),
(52, 32, 'When should you update room inventory after housekeeping?', 1, 'immediately_after', NULL, '2025-10-24 04:26:00'),
(53, 32, 'What should you do if items are missing from a room?', 2, 'report_missing', NULL, '2025-10-24 04:26:00'),
(54, 32, 'How do you handle damaged inventory items?', 3, 'record_damage', NULL, '2025-10-24 04:26:01'),
(55, 33, 'Which report shows current stock levels?', 1, 'inventory_status', NULL, '2025-10-24 04:26:01'),
(56, 33, 'What does a low stock alert indicate?', 2, 'reorder_needed', NULL, '2025-10-24 04:26:01'),
(57, 33, 'How often should you review inventory reports?', 3, 'daily', NULL, '2025-10-24 04:26:02'),
(58, 34, 'What information is tracked in audit logs?', 1, 'all_changes', NULL, '2025-10-24 04:26:02'),
(59, 34, 'Why are audit logs important for inventory management?', 2, 'compliance_tracking', NULL, '2025-10-24 04:26:02'),
(60, 34, 'How long should audit logs be retained?', 3, 'policy_period', NULL, '2025-10-24 04:26:03'),
(61, 35, 'What is the primary purpose of ABC analysis in inventory management?', 1, 'prioritize_items', NULL, '2025-10-24 04:37:25'),
(62, 35, 'Which forecasting method is best for seasonal inventory patterns?', 2, 'seasonal_decomposition', NULL, '2025-10-24 04:37:25'),
(63, 35, 'What does inventory turnover ratio measure?', 3, 'efficiency', NULL, '2025-10-24 04:37:26'),
(64, 36, 'What is the first step in setting up user roles and permissions?', 1, 'analyze_requirements', NULL, '2025-10-24 04:37:26'),
(65, 36, 'Which security practice is most important for system administration?', 2, 'regular_audits', NULL, '2025-10-24 04:37:26'),
(66, 36, 'What should you do before making system configuration changes?', 3, 'backup_system', NULL, '2025-10-24 04:37:27'),
(67, 37, 'Which metric is most important for inventory system performance?', 1, 'response_time', NULL, '2025-10-24 04:37:27'),
(68, 37, 'What should you monitor to prevent system downtime?', 2, 'all_metrics', NULL, '2025-10-24 04:37:27'),
(69, 37, 'How often should performance reports be reviewed?', 3, 'daily', NULL, '2025-10-24 04:37:27'),
(70, 38, 'What is the first step in strategic inventory planning?', 1, 'analyze_current_state', NULL, '2025-10-24 04:37:28'),
(71, 38, 'Which factor is most important in budget planning for inventory?', 2, 'demand_forecasting', NULL, '2025-10-24 04:37:28'),
(72, 38, 'How should you approach long-term inventory forecasting?', 3, 'multiple_methods', NULL, '2025-10-24 04:37:28'),
(73, 39, 'What is the key principle in designing approval workflows?', 1, 'segregation_duties', NULL, '2025-10-24 04:37:29'),
(74, 39, 'When should you implement delegation in approval workflows?', 2, 'temporary_absence', NULL, '2025-10-24 04:37:29'),
(75, 39, 'What should be documented in approval workflows?', 3, 'all_actions', NULL, '2025-10-24 04:37:29'),
(76, 40, 'What is the primary purpose of inventory management?', 1, 'A', NULL, '2025-10-24 04:46:21'),
(77, 40, 'Which method is most effective for tracking inventory levels?', 2, 'A', NULL, '2025-10-24 04:46:21'),
(78, 40, 'What should you do when stock levels fall below the minimum threshold?', 3, 'A', NULL, '2025-10-24 04:46:22'),
(79, 40, 'How often should inventory counts be performed?', 4, 'A', NULL, '2025-10-24 04:46:22'),
(80, 40, 'What is the difference between FIFO and LIFO inventory methods?', 5, 'A', NULL, '2025-10-24 04:46:22'),
(81, 41, 'What is the first step when checking room inventory?', 1, 'verify_room_status', NULL, '2025-10-24 04:51:28'),
(82, 41, 'How should you document missing inventory items?', 2, 'immediate_report', NULL, '2025-10-24 04:51:28'),
(83, 41, 'What should you do if you find damaged inventory items?', 3, 'document_and_remove', NULL, '2025-10-24 04:51:28'),
(84, 42, 'When should you submit a supply request?', 1, 'before_shortage', NULL, '2025-10-24 04:51:28'),
(85, 42, 'What information is required for a supply request?', 2, 'all_details', NULL, '2025-10-24 04:51:29'),
(86, 42, 'How should you prioritize urgent supply requests?', 3, 'mark_urgent', NULL, '2025-10-24 04:51:29'),
(87, 43, 'What is the best time to conduct daily inventory checks?', 1, 'morning_before_guests', NULL, '2025-10-24 04:51:30'),
(88, 43, 'How should you handle discrepancies in inventory counts?', 2, 'investigate_and_report', NULL, '2025-10-24 04:51:30'),
(89, 43, 'What should you do if you find unauthorized items in a room?', 3, 'secure_and_report', NULL, '2025-10-24 04:51:31'),
(90, 44, 'What is the standard procedure for setting up a guest room?', 1, 'checklist_follow', NULL, '2025-10-24 04:51:31'),
(91, 44, 'How should you handle special guest requests for room items?', 2, 'document_and_fulfill', NULL, '2025-10-24 04:51:31'),
(92, 44, 'What should you do if standard room items are not available?', 3, 'find_alternatives', NULL, '2025-10-24 04:51:32'),
(93, 45, 'What should you do when you find a guest item in a room?', 1, 'secure_and_document', NULL, '2025-10-24 04:51:32'),
(94, 45, 'How long should lost and found items be kept?', 2, 'policy_period', NULL, '2025-10-24 04:51:33'),
(95, 45, 'What information should be recorded for lost items?', 3, 'complete_details', NULL, '2025-10-24 04:51:33'),
(96, 46, 'What constitutes an emergency supply situation?', 1, 'guest_safety_impact', NULL, '2025-10-24 04:51:33'),
(97, 46, 'How should you handle emergency supply requests?', 2, 'immediate_escalation', NULL, '2025-10-24 04:51:33'),
(98, 46, 'What should you do if emergency supplies are not available?', 3, 'communicate_alternatives', NULL, '2025-10-24 04:51:34'),
(99, 47, 'What is the primary purpose of inventory management?', 1, 'A', NULL, '2025-10-25 05:23:50'),
(100, 48, 'How do you implement just-in-time inventory management effectively?', 1, 'A', NULL, '2025-10-25 05:24:41'),
(101, 48, 'What are the key performance indicators for inventory optimization?', 2, 'A', NULL, '2025-10-25 05:24:41'),
(102, 48, 'How do you integrate inventory management with demand forecasting?', 3, 'A', NULL, '2025-10-25 05:24:41'),
(103, 48, 'What strategies can reduce carrying costs while maintaining service levels?', 4, 'A', NULL, '2025-10-25 05:24:41'),
(104, 48, 'How do you handle inventory management across multiple locations?', 5, 'A', NULL, '2025-10-25 05:24:41'),
(105, 49, 'What is the primary purpose of inventory management?', 1, 'A', NULL, '2025-10-25 06:04:05'),
(106, 49, 'Which method is most effective for tracking inventory levels?', 2, 'A', NULL, '2025-10-25 06:04:05'),
(107, 49, 'What should you do when stock levels fall below the minimum threshold?', 3, 'A', NULL, '2025-10-25 06:04:05'),
(108, 49, 'How often should inventory counts be performed?', 4, 'A', NULL, '2025-10-25 06:04:05'),
(109, 49, 'What is the difference between FIFO and LIFO inventory methods?', 5, 'A', NULL, '2025-10-25 06:04:05');

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
-- Table structure for table `inventory_training_attempts`
--

CREATE TABLE `inventory_training_attempts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `scenario_id` int(11) NOT NULL,
  `scenario_type` varchar(50) NOT NULL,
  `status` enum('in_progress','completed') NOT NULL DEFAULT 'in_progress',
  `score` int(11) DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `answers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`answers`)),
  `started_at` timestamp NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_training_attempts`
--

INSERT INTO `inventory_training_attempts` (`id`, `user_id`, `scenario_id`, `scenario_type`, `status`, `score`, `duration_minutes`, `answers`, `started_at`, `completed_at`, `created_at`) VALUES
(3, 1072, 40, 'room_inventory', 'completed', 0, 15, '{\"q76\":\"B\",\"q77\":\"B\",\"q78\":\"B\",\"q79\":\"B\",\"q80\":\"B\"}', '2025-10-24 04:56:29', '2025-10-24 04:56:29', '2025-10-24 04:56:29'),
(4, 1072, 40, 'room_inventory', 'completed', 20, 15, '{\"q76\":\"C\",\"q77\":\"A\",\"q78\":\"D\",\"q79\":\"D\",\"q80\":\"D\"}', '2025-10-24 04:57:27', '2025-10-24 04:57:27', '2025-10-24 04:57:27'),
(5, 1073, 48, 'inventory_management', 'completed', 20, 15, '{\"q100\":\"B\",\"q101\":\"B\",\"q102\":\"A\",\"q103\":\"C\",\"q104\":\"C\"}', '2025-10-25 06:21:29', '2025-10-25 06:21:29', '2025-10-25 06:21:29'),
(6, 1073, 46, 'approval', 'completed', 0, 15, '{\"q96\":\"expensive_items\",\"q97\":\"regular_approval\",\"q98\":\"wait_supplies\"}', '2025-10-25 06:21:40', '2025-10-25 06:21:40', '2025-10-25 06:21:40');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_training_certificates`
--

CREATE TABLE `inventory_training_certificates` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `certificate_name` varchar(255) NOT NULL,
  `certificate_type` varchar(50) NOT NULL,
  `score` int(11) NOT NULL,
  `earned_at` timestamp NULL DEFAULT current_timestamp(),
  `status` enum('earned','revoked') NOT NULL DEFAULT 'earned',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `scenario_type` varchar(100) NOT NULL,
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
(29, 'Add Inventory Item', 'Learn how to effectively add new inventory items to the system with proper categorization and details.', 'inventory_management', 'beginner', 15, 10, NULL, NULL, 1, '2025-10-24 04:25:57'),
(30, 'Submit Transaction', 'Master the process of recording inventory transactions including receipts, issues, and adjustments.', 'inventory_management', 'intermediate', 20, 15, NULL, NULL, 1, '2025-10-24 04:25:57'),
(31, 'Request Supplies', 'Learn the proper procedure for requesting supplies and managing approval workflows.', 'approval', 'beginner', 10, 8, NULL, NULL, 1, '2025-10-24 04:25:57'),
(32, 'Update Room Inventory', 'Practice updating room inventory levels and tracking item usage in guest rooms.', 'room_inventory', 'intermediate', 18, 12, NULL, NULL, 1, '2025-10-24 04:25:57'),
(33, 'View Reports', 'Understand how to generate and interpret inventory reports for better decision making.', 'reporting', 'advanced', 25, 20, NULL, NULL, 1, '2025-10-24 04:25:57'),
(34, 'Review Audit Logs', 'Learn to review audit logs and track inventory changes for compliance and security.', 'monitoring', 'advanced', 22, 18, NULL, NULL, 1, '2025-10-24 04:25:57'),
(35, 'Advanced Inventory Analytics', 'Master advanced inventory analytics, forecasting, and strategic decision making for optimal inventory management.', 'reporting', 'advanced', 30, 25, NULL, NULL, 1, '2025-10-24 04:37:25'),
(36, 'System Administration', 'Learn system administration tasks including user management, system configuration, and security settings.', 'automation', 'advanced', 25, 20, NULL, NULL, 1, '2025-10-24 04:37:25'),
(37, 'Performance Monitoring', 'Understand how to monitor system performance, analyze metrics, and optimize inventory operations.', 'monitoring', 'intermediate', 20, 15, NULL, NULL, 1, '2025-10-24 04:37:25'),
(38, 'Strategic Planning', 'Develop skills in strategic inventory planning, budget management, and long-term forecasting.', 'inventory_management', 'advanced', 35, 30, NULL, NULL, 1, '2025-10-24 04:37:25'),
(39, 'Approval Workflow Management', 'Master complex approval workflows, delegation, and multi-level authorization processes.', 'approval', 'intermediate', 18, 12, NULL, NULL, 1, '2025-10-24 04:37:25'),
(40, 'housekeeping test', 'test', 'room_inventory', 'beginner', 10, 10, 'Complete all questions to finish this training scenario.', 'Successfully complete the training scenario with a passing score.', 1, '2025-10-24 04:46:21'),
(41, 'Room Inventory Management', 'Learn how to effectively manage room inventory, track items, and maintain accurate stock levels for guest rooms.', 'room_inventory', 'beginner', 15, 10, NULL, NULL, 1, '2025-10-24 04:51:27'),
(42, 'Supply Request Process', 'Master the process of requesting supplies, understanding approval workflows, and managing inventory requests.', 'approval', 'beginner', 12, 8, NULL, NULL, 1, '2025-10-24 04:51:27'),
(43, 'Daily Inventory Check', 'Practice daily inventory checking procedures, identifying shortages, and reporting discrepancies.', 'inventory_management', 'intermediate', 18, 12, NULL, NULL, 1, '2025-10-24 04:51:27'),
(44, 'Guest Room Setup', 'Learn proper procedures for setting up guest rooms with correct inventory items and maintaining standards.', 'room_inventory', 'intermediate', 20, 15, NULL, NULL, 1, '2025-10-24 04:51:27'),
(45, 'Lost and Found Management', 'Understand how to handle lost and found items, proper documentation, and inventory tracking procedures.', 'inventory_management', 'beginner', 10, 6, NULL, NULL, 1, '2025-10-24 04:51:27'),
(46, 'Emergency Supply Management', 'Learn how to handle emergency supply situations, urgent requests, and crisis inventory management.', 'approval', 'advanced', 25, 18, NULL, NULL, 1, '2025-10-24 04:51:28'),
(47, 'dexterkac', '1', 'room_inventory', 'advanced', 5, 5, 'Complete all questions to finish this training scenario.', 'Successfully complete the training scenario with a passing score.', 1, '2025-10-25 05:23:50'),
(48, 'dexterkac', '2', 'inventory_management', 'advanced', 5, 5, 'Complete all questions to finish this training scenario.', 'Successfully complete the training scenario with a passing score.', 1, '2025-10-25 05:24:41'),
(49, '10', '5', 'room_inventory', 'intermediate', 10, 50, 'Complete all questions to finish this training scenario.', 'Successfully complete the training scenario with a passing score.', 1, '2025-10-25 06:04:05');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_transactions`
--

CREATE TABLE `inventory_transactions` (
  `id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `transaction_type` enum('in','out','adjustment') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `performed_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_usage_reports`
--

CREATE TABLE `inventory_usage_reports` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `room` varchar(50) DEFAULT NULL,
  `date_used` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

--
-- Dumping data for table `loyalty_points`
--

INSERT INTO `loyalty_points` (`id`, `guest_id`, `action`, `points`, `reason`, `description`, `processed_by`, `created_at`) VALUES
(33, 51, 'earn', 100, 'Initial enrollment', 'Joined loyalty program', 1073, '2025-10-22 05:48:42'),
(34, 51, 'earn', 196, 'Stay bonus', 'Points earned from stay #1', 1073, '2025-10-22 05:48:42'),
(35, 51, 'earn', 194, 'Stay bonus', 'Points earned from stay #2', 1073, '2025-10-22 05:48:42'),
(36, 51, 'earn', 202, 'Stay bonus', 'Points earned from stay #3', 1073, '2025-10-22 05:48:42'),
(37, 51, 'earn', 200, 'Tier bonus', 'Gold tier bonus points', 1073, '2025-10-22 05:48:42'),
(38, 52, 'earn', 100, 'Initial enrollment', 'Joined loyalty program', 1073, '2025-10-22 05:48:42'),
(39, 52, 'earn', 280, 'Stay bonus', 'Points earned from stay #1', 1073, '2025-10-22 05:48:42'),
(40, 52, 'earn', 281, 'Stay bonus', 'Points earned from stay #2', 1073, '2025-10-22 05:48:42'),
(41, 54, 'earn', 100, 'Initial enrollment', 'Joined loyalty program', 1073, '2025-10-22 05:48:42'),
(42, 54, 'earn', 274, 'Stay bonus', 'Points earned from stay #1', 1073, '2025-10-22 05:48:43'),
(43, 54, 'earn', 159, 'Stay bonus', 'Points earned from stay #2', 1073, '2025-10-22 05:48:43'),
(44, 54, 'earn', 258, 'Stay bonus', 'Points earned from stay #3', 1073, '2025-10-22 05:48:43'),
(45, 54, 'earn', 150, 'Stay bonus', 'Points earned from stay #4', 1073, '2025-10-22 05:48:43'),
(46, 54, 'earn', 229, 'Stay bonus', 'Points earned from stay #5', 1073, '2025-10-22 05:48:43'),
(47, 54, 'earn', 200, 'Tier bonus', 'Gold tier bonus points', 1073, '2025-10-22 05:48:43'),
(48, 55, 'earn', 100, 'Initial enrollment', 'Joined loyalty program', 1073, '2025-10-22 05:48:43'),
(49, 55, 'earn', 246, 'Stay bonus', 'Points earned from stay #1', 1073, '2025-10-22 05:48:43'),
(50, 55, 'earn', 265, 'Stay bonus', 'Points earned from stay #2', 1073, '2025-10-22 05:48:43'),
(51, 56, 'earn', 100, 'Initial enrollment', 'Joined loyalty program', 1073, '2025-10-22 05:48:43'),
(52, 56, 'earn', 300, 'Stay bonus', 'Points earned from stay #1', 1073, '2025-10-22 05:48:44'),
(53, 56, 'earn', 194, 'Stay bonus', 'Points earned from stay #2', 1073, '2025-10-22 05:48:44'),
(54, 56, 'earn', 246, 'Stay bonus', 'Points earned from stay #3', 1073, '2025-10-22 05:48:44'),
(55, 56, 'earn', 204, 'Stay bonus', 'Points earned from stay #4', 1073, '2025-10-22 05:48:44'),
(56, 56, 'earn', 292, 'Stay bonus', 'Points earned from stay #5', 1073, '2025-10-22 05:48:44'),
(57, 56, 'earn', 298, 'Stay bonus', 'Points earned from stay #6', 1073, '2025-10-22 05:48:44'),
(58, 56, 'earn', 251, 'Stay bonus', 'Points earned from stay #7', 1073, '2025-10-22 05:48:44'),
(59, 56, 'earn', 208, 'Stay bonus', 'Points earned from stay #8', 1073, '2025-10-22 05:48:44'),
(60, 56, 'earn', 500, 'Tier bonus', 'Platinum tier bonus points', 1073, '2025-10-22 05:48:44'),
(61, 58, 'earn', 100, 'Initial enrollment', 'Joined loyalty program', 1073, '2025-10-22 05:48:44'),
(62, 58, 'earn', 177, 'Stay bonus', 'Points earned from stay #1', 1073, '2025-10-22 05:48:44'),
(63, 51, 'redeem', 1000, 'Reward redemption', 'Welcome Gift redeemed', 1073, '2025-10-22 05:48:44'),
(64, 54, 'redeem', 2000, 'Reward redemption', 'Dining Credit redeemed', 1073, '2025-10-22 05:48:44'),
(65, 56, 'redeem', 5000, 'Reward redemption', 'Free Night redemption pending', 1073, '2025-10-22 05:48:45'),
(66, 51, 'earn', 608, 'Balance adjustment', 'Points adjusted to ensure positive balance', 1073, '2025-10-22 05:53:22'),
(67, 54, 'earn', 1130, 'Balance adjustment', 'Points adjusted to ensure positive balance', 1073, '2025-10-22 05:53:22'),
(68, 56, 'earn', 2907, 'Balance adjustment', 'Points adjusted to ensure positive balance', 1073, '2025-10-22 05:53:22'),
(69, 53, 'earn', 100, 'Initial enrollment', 'Joined loyalty program', 1073, '2025-10-22 06:40:00'),
(70, 53, 'earn', 253, 'Stay bonus', 'Points earned from stay #1', 1073, '2025-10-22 06:40:00'),
(71, 53, 'earn', 283, 'Stay bonus', 'Points earned from stay #2', 1073, '2025-10-22 06:40:00'),
(72, 57, 'earn', 100, 'Initial enrollment', 'Joined loyalty program', 1073, '2025-10-22 06:40:00'),
(73, 57, 'earn', 187, 'Stay bonus', 'Points earned from stay #1', 1073, '2025-10-22 06:40:00'),
(74, 57, 'earn', 171, 'Stay bonus', 'Points earned from stay #2', 1073, '2025-10-22 06:40:00'),
(75, 57, 'earn', 275, 'Stay bonus', 'Points earned from stay #3', 1073, '2025-10-22 06:40:00'),
(76, 57, 'earn', 230, 'Stay bonus', 'Points earned from stay #4', 1073, '2025-10-22 06:40:01'),
(77, 57, 'earn', 200, 'Tier bonus', 'Gold tier bonus points', 1073, '2025-10-22 06:40:01'),
(78, 59, 'earn', 100, 'Initial enrollment', 'Joined loyalty program', 1073, '2025-10-22 06:40:01'),
(79, 59, 'earn', 201, 'Stay bonus', 'Points earned from stay #1', 1073, '2025-10-22 06:40:01'),
(80, 59, 'earn', 219, 'Stay bonus', 'Points earned from stay #2', 1073, '2025-10-22 06:40:01'),
(81, 60, 'earn', 100, 'Initial enrollment', 'Joined loyalty program', 1073, '2025-10-22 06:40:01'),
(82, 60, 'earn', 185, 'Stay bonus', 'Points earned from stay #1', 1073, '2025-10-22 06:40:01'),
(83, 60, 'earn', 156, 'Stay bonus', 'Points earned from stay #2', 1073, '2025-10-22 06:40:02'),
(84, 60, 'earn', 279, 'Stay bonus', 'Points earned from stay #3', 1073, '2025-10-22 06:40:02'),
(85, 60, 'earn', 170, 'Stay bonus', 'Points earned from stay #4', 1073, '2025-10-22 06:40:02'),
(86, 60, 'earn', 240, 'Stay bonus', 'Points earned from stay #5', 1073, '2025-10-22 06:40:02'),
(87, 60, 'earn', 280, 'Stay bonus', 'Points earned from stay #6', 1073, '2025-10-22 06:40:02'),
(88, 60, 'earn', 187, 'Stay bonus', 'Points earned from stay #7', 1073, '2025-10-22 06:40:02'),
(89, 60, 'earn', 500, 'Tier bonus', 'Platinum tier bonus points', 1073, '2025-10-22 06:40:02'),
(90, 61, 'earn', 0, 'Initial enrollment', 'Joined loyalty program', 1073, '2025-10-22 07:15:37');

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_redemptions`
--

CREATE TABLE `loyalty_redemptions` (
  `id` int(11) NOT NULL,
  `guest_id` int(11) NOT NULL,
  `reward_id` int(11) NOT NULL,
  `points_used` int(11) NOT NULL,
  `status` enum('pending','approved','fulfilled','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `loyalty_redemptions`
--

INSERT INTO `loyalty_redemptions` (`id`, `guest_id`, `reward_id`, `points_used`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(4, 51, 4, 1000, 'fulfilled', 'Welcome Gift redeemed', '2025-10-04 13:48:44', '2025-10-22 13:48:44'),
(5, 54, 2, 2000, 'approved', 'Dining Credit redeemed', '2025-10-10 13:48:44', '2025-10-22 13:48:44'),
(6, 56, 1, 5000, 'pending', 'Free Night redemption pending', '2025-10-17 13:48:44', '2025-10-22 13:48:44');

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_rewards`
--

CREATE TABLE `loyalty_rewards` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `points_required` int(11) NOT NULL,
  `reward_type` enum('free_night','dining_credit','spa_treatment','welcome_gift','discount') NOT NULL,
  `value` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `loyalty_rewards`
--

INSERT INTO `loyalty_rewards` (`id`, `name`, `description`, `points_required`, `reward_type`, `value`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Free Night', 'Complimentary one night stay', 5000, 'free_night', 150.00, 1, '2025-10-22 13:28:17', '2025-10-22 13:28:17'),
(2, 'Dining Credit', 'P50 dining credit at hotel restaurant', 2000, 'dining_credit', 50.00, 1, '2025-10-22 13:28:17', '2025-10-22 13:28:17'),
(3, 'Spa Treatment', 'Relaxing spa treatment session', 3500, 'spa_treatment', 100.00, 1, '2025-10-22 13:28:17', '2025-10-22 13:28:17'),
(4, 'Welcome Gift', 'Welcome amenity basket', 1000, 'welcome_gift', 25.00, 1, '2025-10-22 13:28:17', '2025-10-22 13:28:17');

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_transactions`
--

CREATE TABLE `loyalty_transactions` (
  `id` int(11) NOT NULL,
  `guest_id` int(11) NOT NULL,
  `transaction_type` enum('earned','redeemed','expired','bonus') NOT NULL,
  `points` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `reservation_id` int(11) DEFAULT NULL,
  `reward_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
(11, 47, 'plumbing', 'high', 'Leaky faucet in bathroom', 'reported', 1073, NULL, 50.00, NULL, '2025-10-21 06:09:32', '2025-10-21 08:42:26'),
(12, 48, 'electrical', 'medium', 'Light switch not working', 'assigned', 1073, NULL, 75.00, NULL, '2025-10-21 06:09:32', '2025-10-21 08:42:26'),
(13, 49, 'hvac', 'low', 'Air conditioning needs cleaning', 'completed', 1073, NULL, 100.00, NULL, '2025-10-21 06:09:32', '2025-10-21 08:42:26'),
(14, 50, 'furniture', 'medium', 'Bed frame needs repair', 'completed', 1073, NULL, 125.00, NULL, '2025-10-21 06:09:32', '2025-10-22 01:44:13'),
(15, 51, 'appliance', 'high', 'TV remote not working', 'reported', 1073, NULL, 25.00, NULL, '2025-10-21 06:09:32', '2025-10-21 08:42:26'),
(19, 47, 'hvac', 'high', 'Test maintenance request', 'completed', 1073, NULL, 50.00, NULL, '2025-10-21 08:42:58', '2025-10-22 01:37:29'),
(20, 55, 'hvac', 'urgent', 'w', 'in_progress', 1073, NULL, 10.00, NULL, '2025-10-22 01:37:09', '2025-10-22 01:44:05'),
(21, 55, 'hvac', 'urgent', 'w', 'in_progress', 1073, NULL, 10.00, NULL, '2025-10-22 01:37:10', '2025-10-22 01:43:57'),
(22, 49, 'electrical', 'high', '22', 'reported', 1073, NULL, 2.00, NULL, '2025-10-22 01:44:23', '2025-10-22 01:44:23'),
(23, 49, 'electrical', 'high', '22', 'completed', 1073, 1075, 2.00, NULL, '2025-10-22 01:44:25', '2025-10-22 09:41:24'),
(24, 49, 'electrical', 'high', '22', 'completed', 1073, NULL, 2.00, NULL, '2025-10-22 01:44:27', '2025-10-22 02:03:53'),
(25, 55, 'other', 'high', '121', 'reported', 1073, NULL, NULL, NULL, '2025-10-22 03:42:21', '2025-10-22 03:42:21'),
(26, 56, 'electrical', 'urgent', '1231', 'reported', 1073, NULL, 1090.00, NULL, '2025-10-22 03:48:37', '2025-10-22 03:48:37');

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
(1, 'PAY-001000', 14, 'cash', 299.51, '2025-10-05 16:00:00', NULL, NULL, 1071),
(2, 'PAY-001001', 15, 'cash', 471.18, '2025-10-10 16:00:00', NULL, NULL, 1071),
(3, 'PAY-001002', 17, 'cash', 236.34, '2025-09-25 16:00:00', NULL, NULL, 1071),
(4, 'PAY-20251023-54300', 67, 'cash', 500.00, '2025-10-23 01:00:51', '1241252', 'wew', 1073);

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
(258, '2', 'login', 'PMS user logged into POS system', '222.127.7.148', NULL, '2025-10-07 00:50:45'),
(259, '2', 'login', 'PMS user logged into POS system', '222.127.7.148', NULL, '2025-10-07 00:51:07'),
(260, '1029', 'login', 'PMS user logged into POS system', '222.127.7.148', NULL, '2025-10-07 01:18:44'),
(261, '1031', 'login', 'PMS user logged into POS system', '222.127.7.148', NULL, '2025-10-07 02:01:49'),
(262, '1029', 'login', 'PMS user logged into POS system', '222.127.7.148', NULL, '2025-10-07 02:08:49'),
(263, '1029', 'login', 'PMS user logged into POS system', '222.127.7.148', NULL, '2025-10-08 00:53:15'),
(264, '1029', 'login', 'PMS user logged into POS system', '222.127.7.148', NULL, '2025-10-08 04:58:39'),
(265, '1029', 'login', 'PMS user logged into POS system', '222.127.7.148', NULL, '2025-10-08 09:48:45'),
(266, 'student_student1', 'login', 'Student logged into POS simulation', '222.127.7.148', NULL, '2025-10-13 01:13:21'),
(267, 'student_student1', 'login', 'Student logged into POS simulation', '222.127.7.148', NULL, '2025-10-13 05:14:45'),
(268, '1029', 'login', 'PMS user logged into POS system', '222.127.7.148', NULL, '2025-10-13 07:58:43'),
(269, 'student_student1', 'login', 'Student logged into POS simulation', '49.146.43.21', NULL, '2025-10-13 11:11:01'),
(270, '1029', 'login', 'PMS user logged into POS system', '49.146.43.21', NULL, '2025-10-13 11:14:07'),
(271, '1029', 'login', 'PMS user logged into POS system', '222.127.7.148', NULL, '2025-10-14 00:44:10'),
(272, '1029', 'login', 'PMS user logged into POS system', '222.127.7.148', NULL, '2025-10-14 07:27:01'),
(273, '1029', 'login', 'PMS user logged into POS system', '222.127.7.148', NULL, '2025-10-17 05:06:07'),
(274, '1029', 'login', 'PMS user logged into POS system', '::1', NULL, '2025-10-17 05:20:59'),
(275, '1073', 'login', 'PMS user logged into POS system', '222.127.7.148', NULL, '2025-10-22 07:25:38'),
(276, '1073', 'login', 'PMS user logged into POS system', '49.146.38.240', NULL, '2025-10-24 12:05:37'),
(277, 'student_student1', 'login', 'Student logged into POS simulation', '49.146.38.240', NULL, '2025-10-24 12:20:12'),
(278, '1073', 'login', 'PMS user logged into POS system', '49.146.38.240', NULL, '2025-10-24 12:23:34'),
(279, '1073', 'login', 'PMS user logged into POS system', '222.127.7.148', NULL, '2025-10-25 04:01:35'),
(280, '1073', 'login', 'PMS user logged into POS system', '222.127.7.148', NULL, '2025-10-25 04:58:16'),
(281, '1073', 'login', 'PMS user logged into POS system', '::1', NULL, '2025-10-25 04:59:35'),
(282, '1073', 'login', 'PMS user logged into POS system', '::1', NULL, '2025-10-26 07:14:16'),
(283, '1073', 'login', 'PMS user logged into POS system', '210.1.106.12', NULL, '2025-10-26 07:14:44'),
(284, '1073', 'login', 'PMS user logged into POS system', '::1', NULL, '2025-10-26 07:46:43'),
(285, '1073', 'logout', 'User logged out from POS system', '::1', NULL, '2025-10-26 07:51:44'),
(286, '1073', 'login', 'PMS user logged into POS system', '::1', NULL, '2025-10-26 07:52:07'),
(287, '1073', 'logout', 'User logged out from POS system', '::1', NULL, '2025-10-26 07:53:00'),
(288, '1073', 'login', 'PMS user logged into POS system', '::1', NULL, '2025-10-26 07:56:58'),
(289, '1073', 'login', 'PMS user logged into POS system', '::1', NULL, '2025-10-26 07:59:25'),
(290, '1073', 'login', 'PMS user logged into POS system', '::1', NULL, '2025-10-26 08:07:08');

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
  `scenario_id` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `severity` enum('low','medium','high','critical') NOT NULL,
  `difficulty` enum('beginner','intermediate','advanced') NOT NULL,
  `time_limit` int(11) NOT NULL DEFAULT 15,
  `points` int(11) NOT NULL DEFAULT 200,
  `status` enum('active','inactive','draft') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `problem_scenarios`
--

INSERT INTO `problem_scenarios` (`id`, `scenario_id`, `title`, `description`, `severity`, `difficulty`, `time_limit`, `points`, `status`, `created_at`, `updated_at`) VALUES
(1, 'problem_solving', 'Problem Solving & Crisis Management', 'Handle various hotel problems and crisis situations that require quick thinking.', 'high', 'advanced', 15, 300, 'active', '2025-10-23 05:37:58', '2025-10-23 05:37:58');

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

--
-- Dumping data for table `quality_checks`
--

INSERT INTO `quality_checks` (`id`, `room_id`, `inspector_id`, `check_type`, `score`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'routine', 95, 'Room in excellent condition', '2025-10-14 01:52:17', '2025-10-14 01:52:17'),
(2, 2, 1, 'routine', 88, 'Minor cleaning needed', '2025-10-14 01:52:17', '2025-10-14 01:52:17'),
(3, 3, 2, 'deep_clean', 92, 'Deep clean completed successfully', '2025-10-14 01:52:17', '2025-10-14 01:52:17');

-- --------------------------------------------------------

--
-- Table structure for table `question_options`
--

CREATE TABLE `question_options` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_text` text NOT NULL,
  `option_value` varchar(255) NOT NULL,
  `option_order` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `question_options`
--

INSERT INTO `question_options` (`id`, `question_id`, `option_text`, `option_value`, `option_order`, `created_at`) VALUES
(1, 1, 'Greet them warmly and ask for their reservation', 'greet_warmly', 1, '2025-10-23 06:47:45'),
(2, 1, 'Ask for their ID immediately', 'ask_id', 2, '2025-10-23 06:47:45'),
(3, 1, 'Hand them the room key', 'hand_key', 3, '2025-10-23 06:47:45'),
(4, 1, 'Ask them to wait', 'ask_wait', 4, '2025-10-23 06:47:45'),
(5, 2, 'Only the guest name', 'name_only', 1, '2025-10-23 06:47:46'),
(6, 2, 'Name and room number', 'name_room', 2, '2025-10-23 06:47:46'),
(7, 2, 'All reservation details and ID', 'all_info', 3, '2025-10-23 06:47:46'),
(8, 2, 'Just the payment method', 'payment_only', 4, '2025-10-23 06:47:46'),
(9, 3, 'Tell them to contact housekeeping', 'contact_housekeeping', 1, '2025-10-23 06:47:46'),
(10, 3, 'Apologize immediately and arrange room cleaning', 'apologize_immediately', 2, '2025-10-23 06:47:46'),
(11, 3, 'Ask them to wait until tomorrow', 'wait_tomorrow', 3, '2025-10-23 06:47:46'),
(12, 3, 'Ignore the complaint', 'ignore', 4, '2025-10-23 06:47:46'),
(13, 4, 'Speaking quickly', 'speaking_fast', 1, '2025-10-23 06:47:46'),
(14, 4, 'Active listening', 'listening', 2, '2025-10-23 06:47:46'),
(15, 4, 'Being right', 'being_right', 3, '2025-10-23 06:47:46'),
(16, 4, 'Following procedures', 'procedures', 4, '2025-10-23 06:47:46'),
(17, 5, 'Match their anger', 'match_anger', 1, '2025-10-23 06:47:47'),
(18, 5, 'Stay calm and acknowledge their feelings', 'stay_calm', 2, '2025-10-23 06:47:47'),
(19, 5, 'Ignore them', 'ignore', 3, '2025-10-23 06:47:47'),
(20, 5, 'Call security', 'call_security', 4, '2025-10-23 06:47:47'),
(21, 6, 'Maximize profits', 'maximize_profits', 1, '2025-10-23 06:47:47'),
(22, 6, 'Guest satisfaction', 'guest_satisfaction', 2, '2025-10-23 06:47:47'),
(23, 6, 'Minimize costs', 'minimize_costs', 3, '2025-10-23 06:47:47'),
(24, 6, 'Staff efficiency', 'staff_efficiency', 4, '2025-10-23 06:47:47'),
(25, 7, 'Report immediately to maintenance', 'report_immediately', 1, '2025-10-23 06:47:47'),
(26, 7, 'Wait until the next day', 'wait_next_day', 2, '2025-10-23 06:47:47'),
(27, 7, 'Fix it yourself', 'fix_yourself', 3, '2025-10-23 06:47:47'),
(28, 7, 'Ignore the issue', 'ignore', 4, '2025-10-23 06:47:47'),
(33, 9, 'Apologize sincerely and offer a solution', 'correct_68f9da4386a72', 1, '2025-10-23 07:33:29'),
(34, 9, 'Ignore the situation and hope it resolves itself', 'incorrect_68f9da4386a94', 2, '2025-10-23 07:33:29'),
(35, 9, 'Blame the guest or external factors', 'incorrect_68f9da4386a95', 3, '2025-10-23 07:33:30'),
(36, 9, 'Panic and make hasty decisions', 'incorrect_68f9da4386a96', 4, '2025-10-23 07:33:30'),
(37, 10, 'Blame the guest or external factors', 'incorrect_68f9da4386a9b', 1, '2025-10-23 07:33:30'),
(38, 10, 'Ignore the situation and hope it resolves itself', 'incorrect_68f9da4386a9a', 2, '2025-10-23 07:33:30'),
(39, 10, 'Panic and make hasty decisions', 'incorrect_68f9da4386a9c', 3, '2025-10-23 07:33:30'),
(40, 10, 'Follow up to ensure satisfaction', 'correct_68f9da4386a99', 4, '2025-10-23 07:33:30'),
(41, 11, 'Ignore the situation and hope it resolves itself', 'incorrect_68f9da4386a9e', 1, '2025-10-23 07:33:30'),
(42, 11, 'Apologize sincerely and offer a solution', 'correct_68f9da4386a9d', 2, '2025-10-23 07:33:30'),
(43, 11, 'Blame the guest or external factors', 'incorrect_68f9da4386a9f', 3, '2025-10-23 07:33:31'),
(44, 11, 'Panic and make hasty decisions', 'incorrect_68f9da4386aa0', 4, '2025-10-23 07:33:32'),
(45, 12, 'Apologize sincerely and offer a solution', 'correct_68f9da4386aa1', 1, '2025-10-23 07:33:32'),
(46, 12, 'Panic and make hasty decisions', 'incorrect_68f9da4386aa4', 2, '2025-10-23 07:33:33'),
(47, 12, 'Ignore the situation and hope it resolves itself', 'incorrect_68f9da4386aa2', 3, '2025-10-23 07:33:41'),
(48, 12, 'Blame the guest or external factors', 'incorrect_68f9da4386aa3', 4, '2025-10-23 07:33:41'),
(49, 13, 'Panic and make hasty decisions', 'incorrect_68f9da4386aa8', 1, '2025-10-23 07:33:42'),
(50, 13, 'Blame the guest or external factors', 'incorrect_68f9da4386aa7', 2, '2025-10-23 07:33:42'),
(51, 13, 'Ignore the situation and hope it resolves itself', 'incorrect_68f9da4386aa6', 3, '2025-10-23 07:33:42'),
(52, 13, 'Listen actively and acknowledge their concerns', 'correct_68f9da4386aa5', 4, '2025-10-23 07:33:42'),
(53, 14, 'Ignore the situation and hope it resolves itself', 'incorrect_68f9da4386aaa', 1, '2025-10-23 07:33:42'),
(54, 14, 'Panic and make hasty decisions', 'incorrect_68f9da4386aac', 2, '2025-10-23 07:33:42'),
(55, 14, 'Follow up to ensure satisfaction', 'correct_68f9da4386aa9', 3, '2025-10-23 07:33:43'),
(56, 14, 'Blame the guest or external factors', 'incorrect_68f9da4386aab', 4, '2025-10-23 07:33:43'),
(57, 15, 'Remain calm and professional throughout', 'correct_68f9da4386aad', 1, '2025-10-23 07:33:43'),
(58, 15, 'Ignore the situation and hope it resolves itself', 'incorrect_68f9da4386aae', 2, '2025-10-23 07:33:43'),
(59, 15, 'Blame the guest or external factors', 'incorrect_68f9da4386aaf', 3, '2025-10-23 07:33:43'),
(60, 15, 'Panic and make hasty decisions', 'incorrect_68f9da4386ab0', 4, '2025-10-23 07:33:43'),
(61, 16, 'Panic and make hasty decisions', 'incorrect_68fc30870526e', 1, '2025-10-25 02:06:05'),
(62, 16, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc30870526c', 2, '2025-10-25 02:06:05'),
(63, 16, 'Blame the guest or external factors', 'incorrect_68fc30870526d', 3, '2025-10-25 02:06:05'),
(64, 16, 'Apologize immediately and take corrective action', 'correct_68fc30870526b', 4, '2025-10-25 02:06:05'),
(65, 17, 'Blame the guest or external factors', 'incorrect_68fc30870530b', 1, '2025-10-25 02:06:05'),
(66, 17, 'Panic and make hasty decisions', 'incorrect_68fc30870530c', 2, '2025-10-25 02:06:05'),
(67, 17, 'Greet the guest warmly and verify their reservation', 'correct_68fc308705309', 3, '2025-10-25 02:06:05'),
(68, 17, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc30870530a', 4, '2025-10-25 02:06:05'),
(69, 18, 'Blame the guest or external factors', 'incorrect_68fc30870530f', 1, '2025-10-25 02:06:05'),
(70, 18, 'Apologize immediately and take corrective action', 'correct_68fc30870530d', 2, '2025-10-25 02:06:05'),
(71, 18, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc30870530e', 3, '2025-10-25 02:06:05'),
(72, 18, 'Panic and make hasty decisions', 'incorrect_68fc308705310', 4, '2025-10-25 02:06:05'),
(73, 19, 'Blame the guest or external factors', 'incorrect_68fc308705313', 1, '2025-10-25 02:06:05'),
(74, 19, 'Panic and make hasty decisions', 'incorrect_68fc308705314', 2, '2025-10-25 02:06:05'),
(75, 19, 'Follow standard procedures and document the situation', 'correct_68fc308705311', 3, '2025-10-25 02:06:05'),
(76, 19, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc308705312', 4, '2025-10-25 02:06:05'),
(77, 20, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc308705316', 1, '2025-10-25 02:06:05'),
(78, 20, 'Follow standard procedures and document the situation', 'correct_68fc308705315', 2, '2025-10-25 02:06:05'),
(79, 20, 'Blame the guest or external factors', 'incorrect_68fc308705317', 3, '2025-10-25 02:06:05'),
(80, 20, 'Panic and make hasty decisions', 'incorrect_68fc308705318', 4, '2025-10-25 02:06:05'),
(81, 21, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc30870531a', 1, '2025-10-25 02:06:05'),
(82, 21, 'Blame the guest or external factors', 'incorrect_68fc30870531b', 2, '2025-10-25 02:06:05'),
(83, 21, 'Apologize immediately and take corrective action', 'correct_68fc308705319', 3, '2025-10-25 02:06:05'),
(84, 21, 'Panic and make hasty decisions', 'incorrect_68fc30870531c', 4, '2025-10-25 02:06:05'),
(85, 22, 'Greet the guest warmly and verify their reservation', 'correct_68fc30870531d', 1, '2025-10-25 02:06:05'),
(86, 22, 'Panic and make hasty decisions', 'incorrect_68fc308705320', 2, '2025-10-25 02:06:05'),
(87, 22, 'Blame the guest or external factors', 'incorrect_68fc30870531f', 3, '2025-10-25 02:06:05'),
(88, 22, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc30870531e', 4, '2025-10-25 02:06:05'),
(89, 23, 'Escalate to management if necessary', 'correct_68fc308705321', 1, '2025-10-25 02:06:05'),
(90, 23, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc308705322', 2, '2025-10-25 02:06:05'),
(91, 23, 'Blame the guest or external factors', 'incorrect_68fc308705323', 3, '2025-10-25 02:06:05'),
(92, 23, 'Panic and make hasty decisions', 'incorrect_68fc308705324', 4, '2025-10-25 02:06:05'),
(93, 24, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc308705326', 1, '2025-10-25 02:06:05'),
(94, 24, 'Blame the guest or external factors', 'incorrect_68fc308705327', 2, '2025-10-25 02:06:05'),
(95, 24, 'Panic and make hasty decisions', 'incorrect_68fc308705328', 3, '2025-10-25 02:06:05'),
(96, 24, 'Follow standard procedures and document the situation', 'correct_68fc308705325', 4, '2025-10-25 02:06:05'),
(97, 25, 'Apologize immediately and take corrective action', 'correct_68fc308705329', 1, '2025-10-25 02:06:05'),
(98, 25, 'Panic and make hasty decisions', 'incorrect_68fc30870532c', 2, '2025-10-25 02:06:05'),
(99, 25, 'Blame the guest or external factors', 'incorrect_68fc30870532b', 3, '2025-10-25 02:06:05'),
(100, 25, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc30870532a', 4, '2025-10-25 02:06:05'),
(101, 26, 'Panic and make hasty decisions', 'incorrect_68fc63ad44897', 1, '2025-10-25 05:44:14'),
(102, 26, 'Greet the guest warmly and verify their reservation', 'correct_68fc63ad44893', 2, '2025-10-25 05:44:14'),
(103, 26, 'Blame the guest or external factors', 'incorrect_68fc63ad44896', 3, '2025-10-25 05:44:14'),
(104, 26, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc63ad44895', 4, '2025-10-25 05:44:14'),
(105, 27, 'Apologize immediately and take corrective action', 'correct_68fc63ad4489a', 1, '2025-10-25 05:44:14'),
(106, 27, 'Panic and make hasty decisions', 'incorrect_68fc63ad4489d', 2, '2025-10-25 05:44:14'),
(107, 27, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc63ad4489b', 3, '2025-10-25 05:44:14'),
(108, 27, 'Blame the guest or external factors', 'incorrect_68fc63ad4489c', 4, '2025-10-25 05:44:14'),
(109, 28, 'Blame the guest or external factors', 'incorrect_68fc63ad448a0', 1, '2025-10-25 05:44:14'),
(110, 28, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc63ad4489f', 2, '2025-10-25 05:44:14'),
(111, 28, 'Panic and make hasty decisions', 'incorrect_68fc63ad448a1', 3, '2025-10-25 05:44:14'),
(112, 28, 'Escalate to management if necessary', 'correct_68fc63ad4489e', 4, '2025-10-25 05:44:14'),
(113, 29, 'Blame the guest or external factors', 'incorrect_68fc63ad448a4', 1, '2025-10-25 05:44:14'),
(114, 29, 'Panic and make hasty decisions', 'incorrect_68fc63ad448a5', 2, '2025-10-25 05:44:14'),
(115, 29, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc63ad448a3', 3, '2025-10-25 05:44:14'),
(116, 29, 'Follow standard procedures and document the situation', 'correct_68fc63ad448a2', 4, '2025-10-25 05:44:14'),
(117, 30, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc63ad448a7', 1, '2025-10-25 05:44:14'),
(118, 30, 'Panic and make hasty decisions', 'incorrect_68fc63ad448a9', 2, '2025-10-25 05:44:14'),
(119, 30, 'Blame the guest or external factors', 'incorrect_68fc63ad448a8', 3, '2025-10-25 05:44:14'),
(120, 30, 'Greet the guest warmly and verify their reservation', 'correct_68fc63ad448a6', 4, '2025-10-25 05:44:14'),
(121, 31, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc651ea9901', 1, '2025-10-25 05:50:26'),
(122, 31, 'Blame the guest or external factors', 'incorrect_68fc651ea9902', 2, '2025-10-25 05:50:26'),
(123, 31, 'Panic and make hasty decisions', 'incorrect_68fc651ea9903', 3, '2025-10-25 05:50:26'),
(124, 31, 'Assess the situation quickly and prioritize safety', 'correct_68fc651ea98ff', 4, '2025-10-25 05:50:26'),
(125, 32, 'Blame the guest or external factors', 'incorrect_68fc651ea9909', 1, '2025-10-25 05:50:26'),
(126, 32, 'Assess the situation quickly and prioritize safety', 'correct_68fc651ea9907', 2, '2025-10-25 05:50:26'),
(127, 32, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc651ea9908', 3, '2025-10-25 05:50:26'),
(128, 32, 'Panic and make hasty decisions', 'incorrect_68fc651ea990a', 4, '2025-10-25 05:50:26'),
(129, 33, 'Implement the most appropriate solution', 'correct_68fc651ea990b', 1, '2025-10-25 05:50:26'),
(130, 33, 'Blame the guest or external factors', 'incorrect_68fc651ea990d', 2, '2025-10-25 05:50:26'),
(131, 33, 'Panic and make hasty decisions', 'incorrect_68fc651ea990e', 3, '2025-10-25 05:50:26'),
(132, 33, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc651ea990c', 4, '2025-10-25 05:50:26'),
(133, 34, 'Blame the guest or external factors', 'incorrect_68fc651ea9911', 1, '2025-10-25 05:50:26'),
(134, 34, 'Panic and make hasty decisions', 'incorrect_68fc651ea9912', 2, '2025-10-25 05:50:26'),
(135, 34, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc651ea9910', 3, '2025-10-25 05:50:26'),
(136, 34, 'Implement the most appropriate solution', 'correct_68fc651ea990f', 4, '2025-10-25 05:50:26'),
(137, 35, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc651ea9914', 1, '2025-10-25 05:50:26'),
(138, 35, 'Blame the guest or external factors', 'incorrect_68fc651ea9915', 2, '2025-10-25 05:50:26'),
(139, 35, 'Communicate clearly with all stakeholders', 'correct_68fc651ea9913', 3, '2025-10-25 05:50:26'),
(140, 35, 'Panic and make hasty decisions', 'incorrect_68fc651ea9916', 4, '2025-10-25 05:50:26'),
(141, 36, 'Document the incident and lessons learned', 'correct_68fc65b4ed7d7', 1, '2025-10-25 05:52:57'),
(142, 36, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc65b4ed7d8', 2, '2025-10-25 05:52:57'),
(143, 36, 'Panic and make hasty decisions', 'incorrect_68fc65b4ed7da', 3, '2025-10-25 05:52:57'),
(144, 36, 'Blame the guest or external factors', 'incorrect_68fc65b4ed7d9', 4, '2025-10-25 05:52:57'),
(145, 37, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc65b4ed7de', 1, '2025-10-25 05:52:57'),
(146, 37, 'Panic and make hasty decisions', 'incorrect_68fc65b4ed7e0', 2, '2025-10-25 05:52:57'),
(147, 37, 'Blame the guest or external factors', 'incorrect_68fc65b4ed7df', 3, '2025-10-25 05:52:57'),
(148, 37, 'Implement the most appropriate solution', 'correct_68fc65b4ed7dd', 4, '2025-10-25 05:52:57'),
(149, 38, 'Implement the most appropriate solution', 'correct_68fc65b4ed7e1', 1, '2025-10-25 05:52:57'),
(150, 38, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc65b4ed7e2', 2, '2025-10-25 05:52:57'),
(151, 38, 'Panic and make hasty decisions', 'incorrect_68fc65b4ed7e4', 3, '2025-10-25 05:52:57'),
(152, 38, 'Blame the guest or external factors', 'incorrect_68fc65b4ed7e3', 4, '2025-10-25 05:52:57'),
(153, 39, 'Document the incident and lessons learned', 'correct_68fc65b4ed7e5', 1, '2025-10-25 05:52:57'),
(154, 39, 'Blame the guest or external factors', 'incorrect_68fc65b4ed7e7', 2, '2025-10-25 05:52:57'),
(155, 39, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc65b4ed7e6', 3, '2025-10-25 05:52:57'),
(156, 39, 'Panic and make hasty decisions', 'incorrect_68fc65b4ed7e8', 4, '2025-10-25 05:52:57'),
(157, 40, 'Blame the guest or external factors', 'incorrect_68fc65b4ed7eb', 1, '2025-10-25 05:52:57'),
(158, 40, 'Panic and make hasty decisions', 'incorrect_68fc65b4ed7ec', 2, '2025-10-25 05:52:57'),
(159, 40, 'Assess the situation quickly and prioritize safety', 'correct_68fc65b4ed7e9', 3, '2025-10-25 05:52:57'),
(160, 40, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc65b4ed7ea', 4, '2025-10-25 05:52:57'),
(161, 41, 'Blame the guest or external factors', 'incorrect_68fc65b4ed7ef', 1, '2025-10-25 05:52:57'),
(162, 41, 'Panic and make hasty decisions', 'incorrect_68fc65b4ed7f0', 2, '2025-10-25 05:52:57'),
(163, 41, 'Assess the situation quickly and prioritize safety', 'correct_68fc65b4ed7ed', 3, '2025-10-25 05:52:57'),
(164, 41, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc65b4ed7ee', 4, '2025-10-25 05:52:57'),
(165, 42, 'Communicate clearly with all stakeholders', 'correct_68fc65b4ed7f1', 1, '2025-10-25 05:52:57'),
(166, 42, 'Panic and make hasty decisions', 'incorrect_68fc65b4ed7f4', 2, '2025-10-25 05:52:57'),
(167, 42, 'Blame the guest or external factors', 'incorrect_68fc65b4ed7f3', 3, '2025-10-25 05:52:57'),
(168, 42, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc65b4ed7f2', 4, '2025-10-25 05:52:57'),
(169, 43, 'Implement the most appropriate solution', 'correct_68fc65b4ed7f5', 1, '2025-10-25 05:52:57'),
(170, 43, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc65b4ed7f6', 2, '2025-10-25 05:52:57'),
(171, 43, 'Blame the guest or external factors', 'incorrect_68fc65b4ed7f7', 3, '2025-10-25 05:52:57'),
(172, 43, 'Panic and make hasty decisions', 'incorrect_68fc65b4ed7f8', 4, '2025-10-25 05:52:57'),
(173, 44, 'Implement the most appropriate solution', 'correct_68fc65b4ed7f9', 1, '2025-10-25 05:52:57'),
(174, 44, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc65b4ed7fa', 2, '2025-10-25 05:52:57'),
(175, 44, 'Panic and make hasty decisions', 'incorrect_68fc65b4ed7fc', 3, '2025-10-25 05:52:57'),
(176, 44, 'Blame the guest or external factors', 'incorrect_68fc65b4ed7fb', 4, '2025-10-25 05:52:57'),
(177, 45, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc65b4ed7fe', 1, '2025-10-25 05:52:57'),
(178, 45, 'Communicate clearly with all stakeholders', 'correct_68fc65b4ed7fd', 2, '2025-10-25 05:52:57'),
(179, 45, 'Panic and make hasty decisions', 'incorrect_68fc65b4ed800', 3, '2025-10-25 05:52:57'),
(180, 45, 'Blame the guest or external factors', 'incorrect_68fc65b4ed7ff', 4, '2025-10-25 05:52:57'),
(181, 46, 'Blame the guest or external factors', 'incorrect_68fc66f8e9a49', 1, '2025-10-25 05:58:22'),
(182, 46, 'Panic and make hasty decisions', 'incorrect_68fc66f8e9a4a', 2, '2025-10-25 05:58:22'),
(183, 46, 'Greet the guest warmly and verify their reservation', 'correct_68fc66f8e9a47', 3, '2025-10-25 05:58:22'),
(184, 46, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc66f8e9a48', 4, '2025-10-25 05:58:22'),
(185, 47, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc66f8e9a4f', 1, '2025-10-25 05:58:22'),
(186, 47, 'Panic and make hasty decisions', 'incorrect_68fc66f8e9a51', 2, '2025-10-25 05:58:22'),
(187, 47, 'Blame the guest or external factors', 'incorrect_68fc66f8e9a50', 3, '2025-10-25 05:58:22'),
(188, 47, 'Follow standard procedures and document the situation', 'correct_68fc66f8e9a4e', 4, '2025-10-25 05:58:22'),
(189, 48, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc66f8e9a53', 1, '2025-10-25 05:58:22'),
(190, 48, 'Panic and make hasty decisions', 'incorrect_68fc66f8e9a55', 2, '2025-10-25 05:58:22'),
(191, 48, 'Blame the guest or external factors', 'incorrect_68fc66f8e9a54', 3, '2025-10-25 05:58:22'),
(192, 48, 'Apologize immediately and take corrective action', 'correct_68fc66f8e9a52', 4, '2025-10-25 05:58:22'),
(193, 49, 'Panic and make hasty decisions', 'incorrect_68fc66f8e9a59', 1, '2025-10-25 05:58:22'),
(194, 49, 'Blame the guest or external factors', 'incorrect_68fc66f8e9a58', 2, '2025-10-25 05:58:22'),
(195, 49, 'Follow standard procedures and document the situation', 'correct_68fc66f8e9a56', 3, '2025-10-25 05:58:22'),
(196, 49, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc66f8e9a57', 4, '2025-10-25 05:58:22'),
(197, 50, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc66f8e9a5b', 1, '2025-10-25 05:58:22'),
(198, 50, 'Blame the guest or external factors', 'incorrect_68fc66f8e9a5c', 2, '2025-10-25 05:58:22'),
(199, 50, 'Panic and make hasty decisions', 'incorrect_68fc66f8e9a5d', 3, '2025-10-25 05:58:22'),
(200, 50, 'Apologize immediately and take corrective action', 'correct_68fc66f8e9a5a', 4, '2025-10-25 05:58:22'),
(201, 51, 'Panic and make hasty decisions', 'incorrect_68fc66f8e9a61', 1, '2025-10-25 05:58:22'),
(202, 51, 'Apologize immediately and take corrective action', 'correct_68fc66f8e9a5e', 2, '2025-10-25 05:58:22'),
(203, 51, 'Blame the guest or external factors', 'incorrect_68fc66f8e9a60', 3, '2025-10-25 05:58:22'),
(204, 51, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc66f8e9a5f', 4, '2025-10-25 05:58:22'),
(205, 52, 'Panic and make hasty decisions', 'incorrect_68fc66f8e9a65', 1, '2025-10-25 05:58:22'),
(206, 52, 'Blame the guest or external factors', 'incorrect_68fc66f8e9a64', 2, '2025-10-25 05:58:22'),
(207, 52, 'Greet the guest warmly and verify their reservation', 'correct_68fc66f8e9a62', 3, '2025-10-25 05:58:22'),
(208, 52, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc66f8e9a63', 4, '2025-10-25 05:58:22'),
(209, 53, 'Greet the guest warmly and verify their reservation', 'correct_68fc66f8e9a66', 1, '2025-10-25 05:58:22'),
(210, 53, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc66f8e9a67', 2, '2025-10-25 05:58:22'),
(211, 53, 'Blame the guest or external factors', 'incorrect_68fc66f8e9a68', 3, '2025-10-25 05:58:22'),
(212, 53, 'Panic and make hasty decisions', 'incorrect_68fc66f8e9a69', 4, '2025-10-25 05:58:22'),
(213, 54, 'Blame the guest or external factors', 'incorrect_68fc66f8e9a6c', 1, '2025-10-25 05:58:22'),
(214, 54, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc66f8e9a6b', 2, '2025-10-25 05:58:22'),
(215, 54, 'Panic and make hasty decisions', 'incorrect_68fc66f8e9a6d', 3, '2025-10-25 05:58:22'),
(216, 54, 'Escalate to management if necessary', 'correct_68fc66f8e9a6a', 4, '2025-10-25 05:58:22'),
(217, 55, 'Panic and make hasty decisions', 'incorrect_68fc66f8e9a71', 1, '2025-10-25 05:58:22'),
(218, 55, 'Blame the guest or external factors', 'incorrect_68fc66f8e9a70', 2, '2025-10-25 05:58:22'),
(219, 55, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc66f8e9a6f', 3, '2025-10-25 05:58:22'),
(220, 55, 'Greet the guest warmly and verify their reservation', 'correct_68fc66f8e9a6e', 4, '2025-10-25 05:58:22'),
(221, 56, 'Communicate clearly with all stakeholders', 'correct_68fc675d4a8f0', 1, '2025-10-25 06:00:00'),
(222, 56, 'Blame the guest or external factors', 'incorrect_68fc675d4a8f3', 2, '2025-10-25 06:00:00'),
(223, 56, 'Panic and make hasty decisions', 'incorrect_68fc675d4a8f4', 3, '2025-10-25 06:00:00'),
(224, 56, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc675d4a8f2', 4, '2025-10-25 06:00:00'),
(225, 57, 'Blame the guest or external factors', 'incorrect_68fc675d4a8fa', 1, '2025-10-25 06:00:00'),
(226, 57, 'Panic and make hasty decisions', 'incorrect_68fc675d4a8fb', 2, '2025-10-25 06:00:00'),
(227, 57, 'Implement the most appropriate solution', 'correct_68fc675d4a8f8', 3, '2025-10-25 06:00:00'),
(228, 57, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc675d4a8f9', 4, '2025-10-25 06:00:00'),
(229, 58, 'Document the incident and lessons learned', 'correct_68fc675d4a8fd', 1, '2025-10-25 06:00:00'),
(230, 58, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc675d4a8fe', 2, '2025-10-25 06:00:00'),
(231, 58, 'Blame the guest or external factors', 'incorrect_68fc675d4a8ff', 3, '2025-10-25 06:00:00'),
(232, 58, 'Panic and make hasty decisions', 'incorrect_68fc675d4a900', 4, '2025-10-25 06:00:00'),
(233, 59, 'Panic and make hasty decisions', 'incorrect_68fc675d4a904', 1, '2025-10-25 06:00:00'),
(234, 59, 'Blame the guest or external factors', 'incorrect_68fc675d4a903', 2, '2025-10-25 06:00:00'),
(235, 59, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc675d4a902', 3, '2025-10-25 06:00:00'),
(236, 59, 'Implement the most appropriate solution', 'correct_68fc675d4a901', 4, '2025-10-25 06:00:00'),
(237, 60, 'Blame the guest or external factors', 'incorrect_68fc675d4a907', 1, '2025-10-25 06:00:00'),
(238, 60, 'Implement the most appropriate solution', 'correct_68fc675d4a905', 2, '2025-10-25 06:00:00'),
(239, 60, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc675d4a906', 3, '2025-10-25 06:00:00'),
(240, 60, 'Panic and make hasty decisions', 'incorrect_68fc675d4a908', 4, '2025-10-25 06:00:00'),
(241, 61, 'Blame the guest or external factors', 'incorrect_68fc691ef1bdc', 1, '2025-10-25 06:07:37'),
(242, 61, 'Greet the guest warmly and verify their reservation', 'correct_68fc691ef1bd9', 2, '2025-10-25 06:07:37'),
(243, 61, 'Panic and make hasty decisions', 'incorrect_68fc691ef1bdd', 3, '2025-10-25 06:07:37'),
(244, 61, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc691ef1bdb', 4, '2025-10-25 06:07:37'),
(245, 62, 'Panic and make hasty decisions', 'incorrect_68fc691ef1be3', 1, '2025-10-25 06:07:37'),
(246, 62, 'Greet the guest warmly and verify their reservation', 'correct_68fc691ef1be0', 2, '2025-10-25 06:07:37'),
(247, 62, 'Blame the guest or external factors', 'incorrect_68fc691ef1be2', 3, '2025-10-25 06:07:37'),
(248, 62, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc691ef1be1', 4, '2025-10-25 06:07:37'),
(249, 63, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc691ef1be5', 1, '2025-10-25 06:07:37'),
(250, 63, 'Apologize immediately and take corrective action', 'correct_68fc691ef1be4', 2, '2025-10-25 06:07:37'),
(251, 63, 'Panic and make hasty decisions', 'incorrect_68fc691ef1be7', 3, '2025-10-25 06:07:37'),
(252, 63, 'Blame the guest or external factors', 'incorrect_68fc691ef1be6', 4, '2025-10-25 06:07:37'),
(253, 64, 'Panic and make hasty decisions', 'incorrect_68fc691ef1beb', 1, '2025-10-25 06:07:37'),
(254, 64, 'Follow standard procedures and document the situation', 'correct_68fc691ef1be8', 2, '2025-10-25 06:07:37'),
(255, 64, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc691ef1be9', 3, '2025-10-25 06:07:37'),
(256, 64, 'Blame the guest or external factors', 'incorrect_68fc691ef1bea', 4, '2025-10-25 06:07:37'),
(257, 65, 'Blame the guest or external factors', 'incorrect_68fc691ef1bee', 1, '2025-10-25 06:07:37'),
(258, 65, 'Panic and make hasty decisions', 'incorrect_68fc691ef1bef', 2, '2025-10-25 06:07:37'),
(259, 65, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc691ef1bed', 3, '2025-10-25 06:07:37'),
(260, 65, 'Escalate to management if necessary', 'correct_68fc691ef1bec', 4, '2025-10-25 06:07:37'),
(261, 66, 'Blame the guest or external factors', 'incorrect_68fc691ef1bf2', 1, '2025-10-25 06:07:37'),
(262, 66, 'Greet the guest warmly and verify their reservation', 'correct_68fc691ef1bf0', 2, '2025-10-25 06:07:37'),
(263, 66, 'Panic and make hasty decisions', 'incorrect_68fc691ef1bf3', 3, '2025-10-25 06:07:37'),
(264, 66, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc691ef1bf1', 4, '2025-10-25 06:07:37'),
(265, 67, 'Panic and make hasty decisions', 'incorrect_68fc691ef1bf7', 1, '2025-10-25 06:07:37'),
(266, 67, 'Blame the guest or external factors', 'incorrect_68fc691ef1bf6', 2, '2025-10-25 06:07:37'),
(267, 67, 'Follow standard procedures and document the situation', 'correct_68fc691ef1bf4', 3, '2025-10-25 06:07:37'),
(268, 67, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc691ef1bf5', 4, '2025-10-25 06:07:37'),
(269, 68, 'Greet the guest warmly and verify their reservation', 'correct_68fc691ef1bf8', 1, '2025-10-25 06:07:37'),
(270, 68, 'Blame the guest or external factors', 'incorrect_68fc691ef1bfa', 2, '2025-10-25 06:07:37'),
(271, 68, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc691ef1bf9', 3, '2025-10-25 06:07:37'),
(272, 68, 'Panic and make hasty decisions', 'incorrect_68fc691ef1bfb', 4, '2025-10-25 06:07:37'),
(273, 69, 'Follow standard procedures and document the situation', 'correct_68fc691ef1bfc', 1, '2025-10-25 06:07:37'),
(274, 69, 'Blame the guest or external factors', 'incorrect_68fc691ef1bfe', 2, '2025-10-25 06:07:37'),
(275, 69, 'Panic and make hasty decisions', 'incorrect_68fc691ef1bff', 3, '2025-10-25 06:07:37'),
(276, 69, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc691ef1bfd', 4, '2025-10-25 06:07:37'),
(277, 70, 'Apologize immediately and take corrective action', 'correct_68fc691ef1c00', 1, '2025-10-25 06:07:37'),
(278, 70, 'Panic and make hasty decisions', 'incorrect_68fc691ef1c03', 2, '2025-10-25 06:07:37'),
(279, 70, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc691ef1c01', 3, '2025-10-25 06:07:37'),
(280, 70, 'Blame the guest or external factors', 'incorrect_68fc691ef1c02', 4, '2025-10-25 06:07:37'),
(281, 71, 'Follow up to ensure satisfaction', 'correct_68fc6bb0bf4cf', 1, '2025-10-25 06:18:58'),
(282, 71, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc6bb0bf4d5', 2, '2025-10-25 06:18:59'),
(283, 71, 'Panic and make hasty decisions', 'incorrect_68fc6bb0bf4d7', 3, '2025-10-25 06:18:59'),
(284, 71, 'Blame the guest or external factors', 'incorrect_68fc6bb0bf4d6', 4, '2025-10-25 06:18:59'),
(285, 72, 'Apologize sincerely and offer a solution', 'correct_68fc6bb0bf4df', 1, '2025-10-25 06:18:59'),
(286, 72, 'Blame the guest or external factors', 'incorrect_68fc6bb0bf4e1', 2, '2025-10-25 06:18:59'),
(287, 72, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc6bb0bf4e0', 3, '2025-10-25 06:18:59'),
(288, 72, 'Panic and make hasty decisions', 'incorrect_68fc6bb0bf4e2', 4, '2025-10-25 06:18:59'),
(289, 73, 'Panic and make hasty decisions', 'incorrect_68fc6bb0bf4e7', 1, '2025-10-25 06:18:59'),
(290, 73, 'Blame the guest or external factors', 'incorrect_68fc6bb0bf4e6', 2, '2025-10-25 06:18:59'),
(291, 73, 'Listen actively and acknowledge their concerns', 'correct_68fc6bb0bf4e4', 3, '2025-10-25 06:18:59'),
(292, 73, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc6bb0bf4e5', 4, '2025-10-25 06:18:59'),
(293, 74, 'Panic and make hasty decisions', 'incorrect_68fc6bb0bf4eb', 1, '2025-10-25 06:18:59'),
(294, 74, 'Blame the guest or external factors', 'incorrect_68fc6bb0bf4ea', 2, '2025-10-25 06:18:59'),
(295, 74, 'Listen actively and acknowledge their concerns', 'correct_68fc6bb0bf4e8', 3, '2025-10-25 06:18:59'),
(296, 74, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc6bb0bf4e9', 4, '2025-10-25 06:19:00'),
(297, 75, 'Blame the guest or external factors', 'incorrect_68fc6bb0bf4ee', 1, '2025-10-25 06:19:00'),
(298, 75, 'Ignore the situation and hope it resolves itself', 'incorrect_68fc6bb0bf4ed', 2, '2025-10-25 06:19:00'),
(299, 75, 'Remain calm and professional throughout', 'correct_68fc6bb0bf4ec', 3, '2025-10-25 06:19:00'),
(300, 75, 'Panic and make hasty decisions', 'incorrect_68fc6bb0bf4ef', 4, '2025-10-25 06:19:00'),
(305, 76, 'Immediately charge their credit card', 'A', 1, '2025-10-26 08:35:05'),
(306, 76, 'Greet the customer and confirm their table number', 'B', 2, '2025-10-26 08:35:05'),
(307, 76, 'Start preparing the food', 'C', 3, '2025-10-26 08:35:05'),
(308, 76, 'Ask for their ID', 'D', 4, '2025-10-26 08:35:05'),
(309, 77, 'Ignore them and process normally', 'A', 1, '2025-10-26 08:35:05'),
(310, 77, 'Add a surcharge automatically', 'B', 2, '2025-10-26 08:35:05'),
(311, 77, 'Add detailed notes and alert the kitchen', 'C', 3, '2025-10-26 08:35:05'),
(312, 77, 'Refuse to take the order', 'D', 4, '2025-10-26 08:35:05'),
(313, 78, 'Ask each person what they ordered and split accordingly', 'A', 1, '2025-10-26 08:35:06'),
(314, 78, 'Split the total equally among all diners', 'B', 2, '2025-10-26 08:35:06'),
(315, 78, 'Charge the person who made the reservation', 'C', 3, '2025-10-26 08:35:06'),
(316, 78, 'Refuse to split bills', 'D', 4, '2025-10-26 08:35:06'),
(317, 79, 'Only accept cash for split bills', 'A', 1, '2025-10-26 08:35:06'),
(318, 79, 'Process each payment method separately and clearly', 'B', 2, '2025-10-26 08:35:06'),
(319, 79, 'Combine all payments into one transaction', 'C', 3, '2025-10-26 08:35:06'),
(320, 79, 'Ask everyone to use the same payment method', 'D', 4, '2025-10-26 08:35:06'),
(321, 80, 'Take as many orders as possible quickly', 'A', 1, '2025-10-26 08:35:06'),
(322, 80, 'Focus on high-value orders only', 'B', 2, '2025-10-26 08:35:06'),
(323, 80, 'Maintain accuracy while being efficient', 'C', 3, '2025-10-26 08:35:06'),
(324, 80, 'Slow down to avoid mistakes', 'D', 4, '2025-10-26 08:35:06'),
(325, 81, 'Process modifications carefully and confirm with kitchen', 'A', 1, '2025-10-26 08:35:06'),
(326, 81, 'Refuse modifications during rush hour', 'B', 2, '2025-10-26 08:35:06'),
(327, 81, 'Charge extra for any modifications', 'C', 3, '2025-10-26 08:35:06'),
(328, 81, 'Ignore modifications to save time', 'D', 4, '2025-10-26 08:35:06'),
(329, 82, 'Only the room number', 'A', 1, '2025-10-26 08:35:06'),
(330, 82, 'Only the food items', 'B', 2, '2025-10-26 08:35:06'),
(331, 82, 'Only the delivery time', 'C', 3, '2025-10-26 08:35:06'),
(332, 82, 'Room number, guest name, food items, and delivery time', 'D', 4, '2025-10-26 08:35:06'),
(333, 83, 'Never charge delivery fees', 'A', 1, '2025-10-26 08:35:07'),
(334, 83, 'Apply delivery charges according to hotel policy', 'B', 2, '2025-10-26 08:35:07'),
(335, 83, 'Charge extra for all room service orders', 'C', 3, '2025-10-26 08:35:07'),
(336, 83, 'Only charge for orders under $50', 'D', 4, '2025-10-26 08:35:07'),
(337, 84, 'Ask detailed questions about the allergy and severity', 'A', 1, '2025-10-26 08:35:07'),
(338, 84, 'Recommend they order something else', 'B', 2, '2025-10-26 08:35:07'),
(339, 84, 'Charge extra for special dietary needs', 'C', 3, '2025-10-26 08:35:07'),
(340, 84, 'Ignore the allergy and process normally', 'D', 4, '2025-10-26 08:35:07'),
(341, 85, 'Write a quick note', 'A', 1, '2025-10-26 08:35:07'),
(342, 85, 'Tell them verbally only', 'B', 2, '2025-10-26 08:35:07'),
(343, 85, 'Use clear, detailed notes and alert the chef directly', 'C', 3, '2025-10-26 08:35:07'),
(344, 85, 'Assume they will remember', 'D', 4, '2025-10-26 08:35:07'),
(345, 86, 'Only the service type', 'A', 1, '2025-10-26 08:37:33'),
(346, 86, 'Only the appointment time', 'B', 2, '2025-10-26 08:37:33'),
(347, 86, 'Only the guest name', 'C', 3, '2025-10-26 08:37:33'),
(348, 86, 'Service type, time, guest details, and therapist availability', 'D', 4, '2025-10-26 08:37:33'),
(349, 87, 'The customer\'s credit limit', 'A', 1, '2025-10-26 08:37:33'),
(350, 87, 'The item\'s popularity', 'B', 2, '2025-10-26 08:37:33'),
(351, 87, 'Item availability and correct pricing', 'C', 3, '2025-10-26 08:37:33'),
(352, 87, 'The customer\'s hotel room number', 'D', 4, '2025-10-26 08:37:33');

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
(41, 'RES-000001', 1, 1, '2024-01-15', '2024-01-17', 2, 0, 200.00, NULL, 'walk_in', 'confirmed', NULL, NULL, NULL, NULL, 1, '2025-10-21 06:09:31', '2025-10-21 06:09:31'),
(42, 'RES-000002', 2, 2, '2024-01-16', '2024-01-19', 3, 0, 450.00, NULL, 'walk_in', 'checked_in', NULL, NULL, NULL, NULL, 1, '2025-10-21 06:09:31', '2025-10-21 06:09:31'),
(43, 'RES-000003', 3, 3, '2024-01-18', '2024-01-20', 2, 0, 300.00, NULL, 'walk_in', 'confirmed', NULL, NULL, NULL, NULL, 1, '2025-10-21 06:09:31', '2025-10-21 06:09:31'),
(44, 'RES-000004', 4, 4, '2024-01-20', '2024-01-22', 2, 0, 300.00, NULL, 'walk_in', 'checked_out', NULL, NULL, NULL, NULL, 1, '2025-10-21 06:09:31', '2025-10-21 06:09:31'),
(45, 'RES-000005', 5, 5, '2024-01-22', '2024-01-25', 3, 0, 750.00, NULL, 'walk_in', 'confirmed', NULL, NULL, NULL, NULL, 1, '2025-10-21 06:09:31', '2025-10-21 06:09:31'),
(46, 'RES-000006', 6, 6, '2024-01-25', '2024-01-27', 2, 0, 500.00, NULL, 'walk_in', 'confirmed', NULL, NULL, NULL, NULL, 1, '2025-10-21 06:09:31', '2025-10-21 06:09:31'),
(47, 'RES-000007', 7, 7, '2024-01-28', '2024-01-30', 2, 0, 1000.00, NULL, 'walk_in', 'confirmed', NULL, NULL, NULL, NULL, 1, '2025-10-21 06:09:31', '2025-10-21 06:09:31'),
(48, 'RES-000008', 8, 8, '2024-02-01', '2024-02-03', 2, 0, 200.00, NULL, 'walk_in', 'confirmed', NULL, NULL, NULL, NULL, 1, '2025-10-21 06:09:31', '2025-10-21 06:09:31'),
(49, 'RES-000009', 9, 9, '2024-02-05', '2024-02-07', 2, 0, 300.00, NULL, 'walk_in', 'confirmed', NULL, NULL, NULL, NULL, 1, '2025-10-21 06:09:31', '2025-10-21 06:09:31'),
(50, 'RES-000010', 10, 10, '2024-02-10', '2024-02-12', 2, 0, 300.00, NULL, 'walk_in', 'confirmed', NULL, NULL, NULL, NULL, 1, '2025-10-21 06:09:31', '2025-10-21 06:09:31'),
(55, 'RES-001000', 51, 49, '2025-10-20', '2025-10-26', 4, 2, 348.64, NULL, 'phone', 'checked_out', NULL, NULL, '2025-10-25 05:42:26', 1073, 1071, '2025-10-13 16:00:00', '2025-10-25 05:42:26'),
(56, 'RES-001001', 53, 51, '2025-10-21', '2025-10-23', 1, 2, 347.65, NULL, 'phone', 'confirmed', NULL, NULL, NULL, NULL, 1071, '2025-10-16 16:00:00', '2025-10-21 07:20:20'),
(57, 'RES-001002', 52, 50, '2025-10-18', '2025-10-24', 2, 2, 548.67, NULL, 'walk_in', 'checked_out', NULL, NULL, '2025-10-22 02:54:38', 1073, 1071, '2025-10-13 16:00:00', '2025-10-22 02:54:38'),
(58, 'RES-001003', 51, 50, '2025-10-20', '2025-10-22', 3, 1, 218.00, NULL, 'online', 'cancelled', NULL, NULL, NULL, NULL, 1071, '2025-10-15 16:00:00', '2025-10-22 02:11:13'),
(63, 'RES202510223939', 61, 53, '2025-10-22', '2025-10-23', 1, 0, 880.00, 'w', 'walk_in', 'checked_out', '2025-10-22 03:28:14', 1073, '2025-10-25 05:43:25', 1073, 1073, '2025-10-22 03:19:09', '2025-10-25 05:43:25'),
(64, 'RES202510254090', 64, 54, '2025-10-25', '2025-10-26', 1, 0, 880.00, 'the front desk must breath when entertaining', 'walk_in', 'cancelled', NULL, NULL, NULL, NULL, 1073, '2025-10-25 05:38:25', '2025-10-25 05:42:16'),
(65, 'RES202510256078', 51, 50, '2025-10-25', '2025-10-27', 1, 0, 550.00, '', 'walk_in', 'checked_out', '2025-10-25 05:43:24', 1073, '2025-10-25 05:43:33', 1073, 1073, '2025-10-25 05:40:43', '2025-10-25 05:43:33'),
(66, 'RES202510257652', 64, 53, '2025-10-25', '2025-10-26', 1, 0, 880.00, 'the front desk must not breath while entertaining the guest', 'walk_in', 'confirmed', NULL, NULL, NULL, NULL, 1073, '2025-10-25 05:45:52', '2025-10-25 05:45:52');

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_audited` timestamp NULL DEFAULT NULL,
  `assigned_housekeeping` int(11) DEFAULT NULL,
  `last_housekeeping_check` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `room_number`, `room_type`, `floor`, `capacity`, `rate`, `status`, `housekeeping_status`, `amenities`, `created_at`, `updated_at`, `last_audited`, `assigned_housekeeping`, `last_housekeeping_check`) VALUES
(47, '101', 'standard', 1, 2, 100.00, 'available', 'clean', NULL, '2025-10-21 06:09:29', '2025-10-21 06:09:29', NULL, NULL, NULL),
(48, '102', 'standard', 1, 2, 100.00, 'available', 'clean', NULL, '2025-10-21 06:09:29', '2025-10-21 06:09:29', NULL, NULL, NULL),
(49, '201', 'deluxe', 2, 2, 150.00, 'available', 'dirty', NULL, '2025-10-21 06:09:29', '2025-10-25 05:42:26', NULL, NULL, NULL),
(50, '202', 'deluxe', 2, 2, 150.00, 'available', 'dirty', NULL, '2025-10-21 06:09:29', '2025-10-25 05:43:33', NULL, NULL, NULL),
(51, '301', 'suite', 3, 4, 250.00, 'available', 'clean', NULL, '2025-10-21 06:09:29', '2025-10-21 06:09:29', NULL, NULL, NULL),
(52, '302', 'suite', 3, 4, 250.00, 'occupied', 'clean', '', '2025-10-21 06:09:29', '2025-10-22 09:27:34', NULL, NULL, NULL),
(53, '401', 'presidential', 4, 6, 500.00, 'reserved', 'dirty', NULL, '2025-10-21 06:09:29', '2025-10-25 05:45:52', NULL, NULL, NULL),
(54, '402', 'presidential', 4, 6, 500.00, 'available', 'clean', NULL, '2025-10-21 06:09:29', '2025-10-25 05:42:16', NULL, NULL, NULL),
(55, '103', 'standard', 1, 2, 100.00, 'available', 'clean', NULL, '2025-10-21 06:09:29', '2025-10-21 06:09:29', NULL, NULL, NULL),
(56, '203', 'deluxe', 2, 2, 150.00, 'available', 'clean', NULL, '2025-10-21 06:09:29', '2025-10-21 06:09:29', NULL, NULL, NULL);

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `room_inventory`
--

INSERT INTO `room_inventory` (`id`, `room_id`, `item_id`, `quantity_allocated`, `quantity_current`, `par_level`, `last_restocked`, `last_audited`, `notes`, `created_at`, `updated_at`, `last_updated`) VALUES
(1, 1, 1, 4, 4, 4, NULL, NULL, NULL, '2025-10-14 01:52:17', '2025-10-14 01:52:17', '2025-10-16 03:03:47'),
(2, 1, 2, 2, 2, 2, NULL, NULL, NULL, '2025-10-14 01:52:17', '2025-10-14 01:52:17', '2025-10-16 03:03:47'),
(3, 2, 1, 4, 4, 4, NULL, NULL, NULL, '2025-10-14 01:52:17', '2025-10-14 01:52:17', '2025-10-16 03:03:47'),
(4, 2, 3, 1, 1, 1, NULL, NULL, NULL, '2025-10-14 01:52:17', '2025-10-14 01:52:17', '2025-10-16 03:03:47'),
(5, 1, 3, 2, 2, 1, NULL, NULL, NULL, '2025-10-16 03:26:21', '2025-10-16 03:26:21', '2025-10-16 03:26:21'),
(6, 1, 4, 2, 2, 1, NULL, NULL, NULL, '2025-10-16 03:26:21', '2025-10-16 03:26:21', '2025-10-16 03:26:21'),
(7, 1, 5, 2, 2, 1, NULL, NULL, NULL, '2025-10-16 03:26:21', '2025-10-16 03:26:21', '2025-10-16 03:26:21'),
(8, 1, 6, 2, 2, 1, NULL, NULL, NULL, '2025-10-16 03:26:21', '2025-10-16 03:26:21', '2025-10-16 03:26:21'),
(9, 1, 7, 1, 1, 1, NULL, NULL, NULL, '2025-10-16 03:26:21', '2025-10-16 03:26:21', '2025-10-16 03:26:21'),
(10, 1, 8, 1, 1, 1, NULL, NULL, NULL, '2025-10-16 03:26:21', '2025-10-16 03:26:21', '2025-10-16 03:26:21'),
(11, 1, 9, 1, 1, 1, NULL, NULL, NULL, '2025-10-16 03:26:21', '2025-10-16 03:26:21', '2025-10-16 03:26:21'),
(12, 1, 10, 1, 1, 1, NULL, NULL, NULL, '2025-10-16 03:26:21', '2025-10-16 03:26:21', '2025-10-16 03:26:21'),
(13, 2, 2, 2, 2, 1, NULL, NULL, NULL, '2025-10-16 03:26:21', '2025-10-16 03:26:21', '2025-10-16 03:26:21'),
(14, 2, 4, 2, 2, 1, NULL, NULL, NULL, '2025-10-16 03:26:21', '2025-10-16 03:26:21', '2025-10-16 03:26:21'),
(15, 2, 5, 2, 1, 1, NULL, NULL, NULL, '2025-10-16 03:26:21', '2025-10-16 03:26:21', '2025-10-16 03:26:21'),
(16, 2, 6, 2, 2, 1, NULL, NULL, NULL, '2025-10-16 03:26:21', '2025-10-16 03:26:21', '2025-10-16 03:26:21'),
(17, 2, 7, 1, 0, 1, NULL, NULL, NULL, '2025-10-16 03:26:21', '2025-10-16 03:26:21', '2025-10-16 03:26:21'),
(18, 2, 8, 1, 1, 1, NULL, NULL, NULL, '2025-10-16 03:26:21', '2025-10-16 03:26:21', '2025-10-16 03:26:21'),
(19, 2, 9, 1, 1, 1, NULL, NULL, NULL, '2025-10-16 03:26:21', '2025-10-16 03:26:21', '2025-10-16 03:26:21'),
(20, 2, 10, 1, 1, 1, NULL, NULL, NULL, '2025-10-16 03:26:21', '2025-10-16 03:26:21', '2025-10-16 03:26:21'),
(21, 3, 1, 4, 4, 2, NULL, NULL, NULL, '2025-10-16 03:26:21', '2025-10-16 03:26:21', '2025-10-16 03:26:21'),
(22, 3, 2, 2, 2, 1, NULL, NULL, NULL, '2025-10-16 03:26:21', '2025-10-16 03:26:21', '2025-10-16 03:26:21'),
(23, 3, 3, 2, 2, 1, NULL, NULL, NULL, '2025-10-16 03:26:21', '2025-10-16 03:26:21', '2025-10-16 03:26:21'),
(24, 3, 4, 2, 2, 1, NULL, NULL, NULL, '2025-10-16 03:26:21', '2025-10-16 03:26:21', '2025-10-16 03:26:21'),
(25, 3, 5, 2, 2, 1, NULL, NULL, NULL, '2025-10-16 03:26:21', '2025-10-16 03:26:21', '2025-10-16 03:26:21'),
(26, 3, 6, 2, 2, 1, NULL, NULL, NULL, '2025-10-16 03:26:21', '2025-10-16 03:26:21', '2025-10-16 03:26:21'),
(27, 3, 7, 1, 1, 1, NULL, NULL, NULL, '2025-10-16 03:26:21', '2025-10-16 03:26:21', '2025-10-16 03:26:21'),
(28, 3, 8, 1, 1, 1, NULL, NULL, NULL, '2025-10-16 03:26:21', '2025-10-16 03:26:21', '2025-10-16 03:26:21'),
(29, 3, 9, 1, 1, 1, NULL, NULL, NULL, '2025-10-16 03:26:21', '2025-10-16 03:26:21', '2025-10-16 03:26:21'),
(30, 3, 10, 1, 1, 1, NULL, NULL, NULL, '2025-10-16 03:26:21', '2025-10-16 03:26:21', '2025-10-16 03:26:21');

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

--
-- Dumping data for table `room_inventory_items`
--

INSERT INTO `room_inventory_items` (`id`, `room_id`, `item_id`, `quantity_allocated`, `quantity_current`, `par_level`, `last_restocked`, `last_audited`, `notes`, `created_at`, `updated_at`) VALUES
(1, 3, 10, 0, 0, 0, NULL, NULL, NULL, '2025-10-14 07:22:52', '2025-10-14 07:22:52');

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

--
-- Dumping data for table `room_inventory_transactions`
--

INSERT INTO `room_inventory_transactions` (`id`, `room_id`, `item_id`, `transaction_type`, `quantity_change`, `quantity_before`, `quantity_after`, `reason`, `reference_number`, `notes`, `user_id`, `created_at`) VALUES
(1, 2, 1, '', -1, 4, 3, 'Bath towel missing after guest checkout', NULL, NULL, 1, '2025-10-16 03:26:21'),
(2, 2, 3, '', -1, 2, 1, 'Face towel missing after guest checkout', NULL, NULL, 1, '2025-10-16 03:26:21'),
(3, 2, 7, '', -1, 1, 0, 'Hair dryer not found in room', NULL, NULL, 1, '2025-10-16 03:26:21'),
(4, 2, 5, 'usage', -1, 2, 1, 'Shampoo used by guest', NULL, NULL, 1, '2025-10-16 03:26:21');

-- --------------------------------------------------------

--
-- Table structure for table `scenario_questions`
--

CREATE TABLE `scenario_questions` (
  `id` int(11) NOT NULL,
  `scenario_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `question_order` int(11) NOT NULL DEFAULT 1,
  `correct_answer` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `scenario_questions`
--

INSERT INTO `scenario_questions` (`id`, `scenario_id`, `question`, `question_order`, `correct_answer`, `created_at`) VALUES
(1, 1, 'What is the first thing you should do when a guest arrives for check-in?', 1, 'greet_warmly', '2025-10-23 06:47:45'),
(2, 1, 'What information do you need to verify during check-in?', 2, 'all_info', '2025-10-23 06:47:46'),
(3, 1, 'How should you handle a guest complaint about room cleanliness?', 3, 'apologize_immediately', '2025-10-23 06:47:46'),
(4, 2, 'What is the most important aspect of customer service?', 1, 'listening', '2025-10-23 06:47:46'),
(5, 2, 'How should you respond to an angry customer?', 2, 'stay_calm', '2025-10-23 06:47:47'),
(6, 3, 'What is the primary goal of hotel management?', 1, 'guest_satisfaction', '2025-10-23 06:47:47'),
(7, 3, 'What should you do if a guest reports a maintenance issue?', 2, 'report_immediately', '2025-10-23 06:47:47'),
(9, 5, 'What is the most appropriate response when dealing with basic billing issue in communication skills?', 1, 'correct_68f9da4386a72', '2025-10-23 07:33:29'),
(10, 5, 'What is the correct procedure for handling basic room problem during communication skills?', 2, 'incorrect_68f9da4386a9b', '2025-10-23 07:33:30'),
(11, 5, 'What is the most appropriate response when dealing with basic billing issue in guest satisfaction?', 3, 'incorrect_68f9da4386a9e', '2025-10-23 07:33:30'),
(12, 5, 'What is the best approach for managing straightforward room problem in service recovery?', 4, 'correct_68f9da4386aa1', '2025-10-23 07:33:32'),
(13, 5, 'How should you handle a basic special request situation related to service recovery?', 5, 'incorrect_68f9da4386aa8', '2025-10-23 07:33:41'),
(14, 5, 'Which action should be taken first when encountering basic special request in complaint handling?', 6, 'incorrect_68f9da4386aaa', '2025-10-23 07:33:42'),
(15, 5, 'Which action should be taken first when encountering simple billing issue in guest satisfaction?', 7, 'correct_68f9da4386aad', '2025-10-23 07:33:43'),
(16, 6, 'Which action should be taken first when encountering challenging guest arrival in guest registration?', 1, 'incorrect_68fc30870526e', '2025-10-25 02:06:05'),
(17, 6, 'How should you handle a challenging payment disputes situation related to guest services?', 2, 'incorrect_68fc30870530b', '2025-10-25 02:06:05'),
(18, 6, 'How should you handle a complex payment disputes situation related to payment processing?', 3, 'incorrect_68fc30870530f', '2025-10-25 02:06:05'),
(19, 6, 'What is the most appropriate response when dealing with challenging check-in process in guest services?', 4, 'incorrect_68fc308705313', '2025-10-25 02:06:05'),
(20, 6, 'How should you handle a complex check-in process situation related to guest services?', 5, 'incorrect_68fc308705316', '2025-10-25 02:06:05'),
(21, 6, 'What is the best approach for managing complex payment disputes in guest services?', 6, 'incorrect_68fc30870531a', '2025-10-25 02:06:05'),
(22, 6, 'How should you handle a complex room key issues situation related to payment processing?', 7, 'correct_68fc30870531d', '2025-10-25 02:06:05'),
(23, 6, 'How should you handle a sophisticated special requests situation related to check-in procedures?', 8, 'correct_68fc308705321', '2025-10-25 02:06:05'),
(24, 6, 'How should you handle a sophisticated special requests situation related to check-in procedures?', 9, 'incorrect_68fc308705326', '2025-10-25 02:06:05'),
(25, 6, 'What is the correct procedure for handling complex room key issues during check-in procedures?', 10, 'correct_68fc308705329', '2025-10-25 02:06:05'),
(26, 7, 'What is the best approach for managing simple payment disputes in room assignments?', 1, 'incorrect_68fc63ad44897', '2025-10-25 05:44:14'),
(27, 7, 'How should you handle a basic payment disputes situation related to payment processing?', 2, 'correct_68fc63ad4489a', '2025-10-25 05:44:14'),
(28, 7, 'What is the best approach for managing basic check-in process in check-in procedures?', 3, 'incorrect_68fc63ad448a0', '2025-10-25 05:44:14'),
(29, 7, 'What is the most appropriate response when dealing with straightforward check-in process in room assignments?', 4, 'incorrect_68fc63ad448a4', '2025-10-25 05:44:14'),
(30, 7, 'What is the best approach for managing straightforward check-in process in guest services?', 5, 'incorrect_68fc63ad448a7', '2025-10-25 05:44:14'),
(31, 8, 'What is the most appropriate response when dealing with complex emergency situation in emergency procedures?', 1, 'incorrect_68fc651ea9901', '2025-10-25 05:50:26'),
(32, 8, 'What is the most appropriate response when dealing with complex system outage in resource allocation?', 2, 'incorrect_68fc651ea9909', '2025-10-25 05:50:26'),
(33, 8, 'What is the correct procedure for handling sophisticated system outage during emergency procedures?', 3, 'correct_68fc651ea990b', '2025-10-25 05:50:26'),
(34, 8, 'What is the best approach for managing challenging system outage in emergency procedures?', 4, 'incorrect_68fc651ea9911', '2025-10-25 05:50:26'),
(35, 8, 'What is the most appropriate response when dealing with complex staff shortage in crisis management?', 5, 'incorrect_68fc651ea9914', '2025-10-25 05:50:26'),
(36, 9, 'What is the best approach for managing challenging system outage in emergency procedures?', 1, 'correct_68fc65b4ed7d7', '2025-10-25 05:52:57'),
(37, 9, 'What is the best approach for managing challenging guest emergency in staff coordination?', 2, 'incorrect_68fc65b4ed7de', '2025-10-25 05:52:57'),
(38, 9, 'What is the best approach for managing challenging system outage in resource allocation?', 3, 'correct_68fc65b4ed7e1', '2025-10-25 05:52:57'),
(39, 9, 'What is the correct procedure for handling challenging guest emergency during staff coordination?', 4, 'correct_68fc65b4ed7e5', '2025-10-25 05:52:57'),
(40, 9, 'How should you handle a complex staff shortage situation related to resource allocation?', 5, 'incorrect_68fc65b4ed7eb', '2025-10-25 05:52:57'),
(41, 9, 'Which action should be taken first when encountering complex system outage in emergency procedures?', 6, 'incorrect_68fc65b4ed7ef', '2025-10-25 05:52:57'),
(42, 9, 'What is the best approach for managing complex guest emergency in decision making?', 7, 'correct_68fc65b4ed7f1', '2025-10-25 05:52:57'),
(43, 9, 'What is the best approach for managing sophisticated emergency situation in decision making?', 8, 'correct_68fc65b4ed7f5', '2025-10-25 05:52:57'),
(44, 9, 'What is the best approach for managing sophisticated guest emergency in crisis management?', 9, 'correct_68fc65b4ed7f9', '2025-10-25 05:52:57'),
(45, 9, 'Which action should be taken first when encountering complex guest emergency in resource allocation?', 10, 'incorrect_68fc65b4ed7fe', '2025-10-25 05:52:57'),
(46, 10, 'What is the best approach for managing moderate room key issues in check-in procedures?', 1, 'incorrect_68fc66f8e9a49', '2025-10-25 05:58:22'),
(47, 10, 'What is the correct procedure for handling typical check-in process during guest registration?', 2, 'incorrect_68fc66f8e9a4f', '2025-10-25 05:58:22'),
(48, 10, 'What is the best approach for managing common special requests in guest registration?', 3, 'incorrect_68fc66f8e9a53', '2025-10-25 05:58:22'),
(49, 10, 'What is the correct procedure for handling common guest arrival during guest services?', 4, 'incorrect_68fc66f8e9a59', '2025-10-25 05:58:22'),
(50, 10, 'What is the best approach for managing common check-in process in guest registration?', 5, 'incorrect_68fc66f8e9a5b', '2025-10-25 05:58:22'),
(51, 10, 'How should you handle a common check-in process situation related to payment processing?', 6, 'incorrect_68fc66f8e9a61', '2025-10-25 05:58:22'),
(52, 10, 'What is the best approach for managing common guest arrival in check-in procedures?', 7, 'incorrect_68fc66f8e9a65', '2025-10-25 05:58:22'),
(53, 10, 'What is the best approach for managing typical guest arrival in check-in procedures?', 8, 'correct_68fc66f8e9a66', '2025-10-25 05:58:22'),
(54, 10, 'What is the correct procedure for handling moderate special requests during guest services?', 9, 'incorrect_68fc66f8e9a6c', '2025-10-25 05:58:22'),
(55, 10, 'Which action should be taken first when encountering common check-in process in check-in procedures?', 10, 'incorrect_68fc66f8e9a71', '2025-10-25 05:58:22'),
(56, 11, 'What is the best approach for managing typical guest emergency in decision making?', 1, 'correct_68fc675d4a8f0', '2025-10-25 06:00:00'),
(57, 11, 'How should you handle a moderate staff shortage situation related to decision making?', 2, 'incorrect_68fc675d4a8fa', '2025-10-25 06:00:00'),
(58, 11, 'Which action should be taken first when encountering moderate emergency situation in resource allocation?', 3, 'correct_68fc675d4a8fd', '2025-10-25 06:00:00'),
(59, 11, 'What is the best approach for managing moderate staff shortage in resource allocation?', 4, 'incorrect_68fc675d4a904', '2025-10-25 06:00:00'),
(60, 11, 'Which action should be taken first when encountering moderate system outage in emergency procedures?', 5, 'incorrect_68fc675d4a907', '2025-10-25 06:00:00'),
(61, 12, 'What is the correct procedure for handling complex special requests during guest services?', 1, 'incorrect_68fc691ef1bdc', '2025-10-25 06:07:37'),
(62, 12, 'Which action should be taken first when encountering complex payment disputes in room assignments?', 2, 'incorrect_68fc691ef1be3', '2025-10-25 06:07:37'),
(63, 12, 'Which action should be taken first when encountering complex check-in process in payment processing?', 3, 'incorrect_68fc691ef1be5', '2025-10-25 06:07:37'),
(64, 12, 'What is the correct procedure for handling sophisticated payment disputes during guest services?', 4, 'incorrect_68fc691ef1beb', '2025-10-25 06:07:37'),
(65, 12, 'What is the correct procedure for handling challenging special requests during guest registration?', 5, 'incorrect_68fc691ef1bee', '2025-10-25 06:07:37'),
(66, 12, 'How should you handle a complex check-in process situation related to guest services?', 6, 'incorrect_68fc691ef1bf2', '2025-10-25 06:07:37'),
(67, 12, 'Which action should be taken first when encountering challenging special requests in guest services?', 7, 'incorrect_68fc691ef1bf7', '2025-10-25 06:07:37'),
(68, 12, 'What is the correct procedure for handling challenging payment disputes during guest services?', 8, 'correct_68fc691ef1bf8', '2025-10-25 06:07:37'),
(69, 12, 'Which action should be taken first when encountering challenging guest arrival in guest registration?', 9, 'correct_68fc691ef1bfc', '2025-10-25 06:07:37'),
(70, 12, 'How should you handle a complex special requests situation related to guest registration?', 10, 'correct_68fc691ef1c00', '2025-10-25 06:07:37'),
(71, 13, 'How should you handle a simple room problem situation related to communication skills?', 1, 'correct_68fc6bb0bf4cf', '2025-10-25 06:18:58'),
(72, 13, 'What is the best approach for managing straightforward angry guest in communication skills?', 2, 'correct_68fc6bb0bf4df', '2025-10-25 06:18:59'),
(73, 13, 'How should you handle a straightforward billing issue situation related to communication skills?', 3, 'incorrect_68fc6bb0bf4e7', '2025-10-25 06:18:59'),
(74, 13, 'What is the correct procedure for handling straightforward service complaint during service recovery?', 4, 'incorrect_68fc6bb0bf4eb', '2025-10-25 06:18:59'),
(75, 13, 'What is the correct procedure for handling basic service complaint during communication skills?', 5, 'incorrect_68fc6bb0bf4ee', '2025-10-25 06:19:00'),
(76, 14, 'What is the first step when a customer places an order at the restaurant?', 1, 'B', '2025-10-26 08:35:05'),
(77, 14, 'How should you handle special dietary requests in the POS system?', 2, 'C', '2025-10-26 08:35:05'),
(78, 15, 'What is the best way to handle a split bill request?', 1, 'A', '2025-10-26 08:35:05'),
(79, 15, 'How should you handle different payment methods for a split bill?', 2, 'B', '2025-10-26 08:35:06'),
(80, 16, 'During rush hour, what should be your priority when processing orders?', 1, 'C', '2025-10-26 08:35:06'),
(81, 16, 'How should you handle order modifications during busy periods?', 2, 'A', '2025-10-26 08:35:06'),
(82, 17, 'What information is essential when taking a room service order?', 1, 'D', '2025-10-26 08:35:06'),
(83, 17, 'How should you handle room service delivery charges?', 2, 'B', '2025-10-26 08:35:07'),
(84, 18, 'When a guest has a food allergy, what should you do first?', 1, 'A', '2025-10-26 08:35:07'),
(85, 18, 'How should you communicate dietary restrictions to the kitchen?', 2, 'C', '2025-10-26 08:35:07'),
(86, 20, 'When booking a spa service, what information is most critical?', 1, 'D', '2025-10-26 08:37:32'),
(87, 23, 'When processing gift shop sales, what should you verify first?', 1, 'C', '2025-10-26 08:37:33');

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
(1, 1, 1, 1, 'room_service', 'Extra Towels Request', 'Guest requested extra towels for room', 'medium', 'pending', 1, NULL, '2025-10-14 01:52:17', NULL, '2025-10-14 01:52:17', '2025-10-14 01:52:17'),
(2, 1, 1, 1, 'housekeeping', 'Room Cleaning', 'Guest requested room cleaning service', 'high', 'in_progress', 1, 2, '2025-10-14 01:52:17', NULL, '2025-10-14 01:52:17', '2025-10-14 01:52:17');

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
(1, 'ABC Supply Co.', 'John Smith', 'john@abcsupply.com', '+1-555-0101', NULL, 1, '2025-10-14 01:52:17', '2025-10-14 01:52:17'),
(2, 'XYZ Distributors', 'Jane Doe', 'jane@xyzdist.com', '+1-555-0102', NULL, 1, '2025-10-14 01:52:17', '2025-10-14 01:52:17'),
(3, 'Hotel Essentials Ltd.', 'Mike Johnson', 'mike@hotelessentials.com', '+1-555-0103', NULL, 1, '2025-10-14 01:52:17', '2025-10-14 01:52:17'),
(4, 'Quality Products Inc.', 'Sarah Wilson', 'sarah@qualityproducts.com', '+1-555-0104', NULL, 1, '2025-10-14 01:52:17', '2025-10-14 01:52:17'),
(5, 'Premium Services Corp.', 'David Brown', 'david@premiumservices.com', '+1-555-0105', NULL, 1, '2025-10-14 01:52:17', '2025-10-14 01:52:17');

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
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `room_number` varchar(10) DEFAULT NULL,
  `reason` enum('missing','damaged','low_stock','replacement') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `supply_requests`
--

INSERT INTO `supply_requests` (`id`, `item_id`, `requested_by`, `quantity_requested`, `priority`, `notes`, `status`, `approved_by`, `approved_at`, `fulfilled_at`, `created_at`, `updated_at`, `room_number`, `reason`) VALUES
(1, 1, 1, 2, 'normal', 'Towels missing after guest checkout', 'pending', NULL, NULL, NULL, '2025-10-16 03:03:47', '2025-10-16 03:03:47', '201', 'missing'),
(2, 2, 1, 1, 'normal', 'Hair dryer not working', 'pending', NULL, NULL, NULL, '2025-10-16 03:03:47', '2025-10-16 03:03:47', '205', 'damaged'),
(3, 3, 1, 3, 'normal', 'Shampoo bottles running low', 'approved', NULL, NULL, NULL, '2025-10-16 03:03:47', '2025-10-16 03:03:47', '210', 'low_stock'),
(4, 1, 1, 1, 'normal', 'Bath towel missing after guest checkout', 'pending', NULL, NULL, NULL, '2025-10-16 03:26:21', '2025-10-16 03:26:21', '202', 'missing'),
(5, 3, 1, 1, 'normal', 'Face towel missing after guest checkout', 'pending', NULL, NULL, NULL, '2025-10-16 03:26:21', '2025-10-16 03:26:21', '202', 'missing'),
(6, 7, 1, 1, 'normal', 'Hair dryer not found in room', 'pending', NULL, NULL, NULL, '2025-10-16 03:26:21', '2025-10-16 03:26:21', '202', 'missing'),
(7, 5, 1, 1, 'normal', 'Shampoo running low', 'approved', NULL, NULL, NULL, '2025-10-16 03:26:21', '2025-10-16 03:26:21', '202', 'low_stock'),
(8, 56, 1072, 10, 'normal', 'room 402 requested coffee ^_^', 'pending', NULL, NULL, NULL, '2025-10-25 05:18:48', '2025-10-25 05:18:48', '402', 'replacement');

-- --------------------------------------------------------

--
-- Table structure for table `training_attempts`
--

CREATE TABLE `training_attempts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `scenario_id` varchar(50) NOT NULL,
  `scenario_type` enum('training','customer_service','problem') NOT NULL,
  `system` enum('pos','booking','inventory') DEFAULT NULL,
  `score` decimal(5,2) NOT NULL,
  `answers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`answers`)),
  `duration_minutes` int(11) DEFAULT 0,
  `status` enum('in_progress','completed','abandoned') DEFAULT 'in_progress',
  `started_at` timestamp NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `training_attempts`
--

INSERT INTO `training_attempts` (`id`, `user_id`, `scenario_id`, `scenario_type`, `system`, `score`, `answers`, `duration_minutes`, `status`, `started_at`, `completed_at`, `created_at`) VALUES
(1, 1071, '1', 'training', 'booking', 85.50, NULL, 15, 'completed', '2025-10-21 05:38:59', '2025-10-21 05:53:59', '2025-10-23 05:38:56'),
(2, 1071, '2', 'training', 'booking', 92.00, NULL, 20, 'completed', '2025-10-22 05:38:59', '2025-10-22 05:58:59', '2025-10-23 05:38:56'),
(3, 1071, '3', 'training', 'booking', 78.50, NULL, 25, 'completed', '2025-10-23 02:38:59', '2025-10-23 03:38:59', '2025-10-23 05:38:57'),
(4, 1, '1', 'training', 'booking', 66.67, '{\"q1\":\"greet_warmly\",\"q2\":\"wrong_answer\",\"q3\":\"apologize_immediately\"}', 15, 'completed', '2025-10-23 06:56:43', NULL, '2025-10-23 06:56:43'),
(5, 1073, '1', 'training', 'booking', 33.33, '{\"q1\":\"hand_key\",\"q2\":\"all_info\",\"q3\":\"contact_housekeeping\"}', 15, 'completed', '2025-10-23 06:58:09', NULL, '2025-10-23 06:58:09'),
(6, 1073, '5', 'training', 'booking', 42.86, '{\"q9\":\"correct_68f9da4386a72\",\"q10\":\"correct_68f9da4386a99\",\"q11\":\"correct_68f9da4386a9d\",\"q12\":\"correct_68f9da4386aa1\",\"q13\":\"correct_68f9da4386aa5\",\"q14\":\"correct_68f9da4386aa9\",\"q15\":\"correct_68f9da4386aad\"}', 15, 'completed', '2025-10-23 08:07:45', NULL, '2025-10-23 08:07:45'),
(7, 1073, '5', 'training', 'booking', 43.00, '{\"q9\":\"correct_68f9da4386a72\",\"q10\":\"correct_68f9da4386a99\",\"q11\":\"correct_68f9da4386a9d\",\"q12\":\"correct_68f9da4386aa1\",\"q13\":\"correct_68f9da4386aa5\",\"q14\":\"correct_68f9da4386aa9\",\"q15\":\"correct_68f9da4386aad\"}', 15, 'completed', '2025-10-23 08:37:44', NULL, '2025-10-23 08:37:44'),
(8, 1071, '5', 'training', 'booking', 29.00, '{\"q9\":\"correct_68f9da4386a72\",\"q10\":\"incorrect_68f9da4386a9a\",\"q11\":\"incorrect_68f9da4386a9f\",\"q13\":\"incorrect_68f9da4386aa8\",\"q15\":\"incorrect_68f9da4386aaf\"}', 15, 'completed', '2025-10-23 09:13:31', NULL, '2025-10-23 09:13:31'),
(9, 1073, '7', 'training', 'booking', 20.00, '{\"q26\":\"correct_68fc63ad44893\",\"q27\":\"correct_68fc63ad4489a\",\"q28\":\"correct_68fc63ad4489e\",\"q29\":\"correct_68fc63ad448a2\",\"q30\":\"correct_68fc63ad448a6\"}', 15, 'completed', '2025-10-25 05:45:50', NULL, '2025-10-25 05:45:50'),
(10, 1073, '7', 'training', 'booking', 20.00, '{\"q26\":\"correct_68fc63ad44893\",\"q27\":\"correct_68fc63ad4489a\",\"q28\":\"correct_68fc63ad4489e\",\"q29\":\"correct_68fc63ad448a2\",\"q30\":\"correct_68fc63ad448a6\"}', 15, 'completed', '2025-10-25 05:48:22', NULL, '2025-10-25 05:48:22'),
(11, 1073, '7', 'training', 'booking', 20.00, '{\"q26\":\"correct_68fc63ad44893\",\"q27\":\"correct_68fc63ad4489a\",\"q28\":\"correct_68fc63ad4489e\",\"q29\":\"correct_68fc63ad448a2\",\"q30\":\"correct_68fc63ad448a6\"}', 15, 'completed', '2025-10-25 05:49:33', NULL, '2025-10-25 05:49:33'),
(12, 1073, '7', 'training', 'booking', 20.00, '{\"q26\":\"correct_68fc63ad44893\",\"q27\":\"correct_68fc63ad4489a\",\"q28\":\"correct_68fc63ad4489e\",\"q29\":\"correct_68fc63ad448a2\",\"q30\":\"correct_68fc63ad448a6\"}', 15, 'completed', '2025-10-25 05:49:35', NULL, '2025-10-25 05:49:35'),
(13, 1073, '7', 'training', 'booking', 20.00, '{\"q26\":\"correct_68fc63ad44893\",\"q27\":\"correct_68fc63ad4489a\",\"q28\":\"correct_68fc63ad4489e\",\"q29\":\"correct_68fc63ad448a2\",\"q30\":\"correct_68fc63ad448a6\"}', 15, 'completed', '2025-10-25 05:49:37', NULL, '2025-10-25 05:49:37'),
(14, 1073, '7', 'training', 'booking', 20.00, '{\"q26\":\"correct_68fc63ad44893\",\"q27\":\"correct_68fc63ad4489a\",\"q28\":\"correct_68fc63ad4489e\",\"q29\":\"correct_68fc63ad448a2\",\"q30\":\"correct_68fc63ad448a6\"}', 15, 'completed', '2025-10-25 05:49:38', NULL, '2025-10-25 05:49:38'),
(15, 1073, '7', 'training', 'booking', 20.00, '{\"q26\":\"correct_68fc63ad44893\",\"q27\":\"correct_68fc63ad4489a\",\"q28\":\"correct_68fc63ad4489e\",\"q29\":\"correct_68fc63ad448a2\",\"q30\":\"correct_68fc63ad448a6\"}', 15, 'completed', '2025-10-25 05:49:39', NULL, '2025-10-25 05:49:39'),
(16, 1073, '7', 'training', 'booking', 20.00, '{\"q26\":\"correct_68fc63ad44893\",\"q27\":\"correct_68fc63ad4489a\",\"q28\":\"correct_68fc63ad4489e\",\"q29\":\"correct_68fc63ad448a2\",\"q30\":\"correct_68fc63ad448a6\"}', 15, 'completed', '2025-10-25 05:49:45', NULL, '2025-10-25 05:49:45'),
(17, 1073, '7', 'training', 'booking', 20.00, '{\"q26\":\"correct_68fc63ad44893\",\"q27\":\"correct_68fc63ad4489a\",\"q28\":\"correct_68fc63ad4489e\",\"q29\":\"correct_68fc63ad448a2\",\"q30\":\"correct_68fc63ad448a6\"}', 15, 'completed', '2025-10-25 05:49:45', NULL, '2025-10-25 05:49:45'),
(18, 1073, '7', 'training', 'booking', 20.00, '{\"q26\":\"correct_68fc63ad44893\",\"q27\":\"correct_68fc63ad4489a\",\"q28\":\"correct_68fc63ad4489e\",\"q29\":\"correct_68fc63ad448a2\",\"q30\":\"correct_68fc63ad448a6\"}', 15, 'completed', '2025-10-25 05:49:45', NULL, '2025-10-25 05:49:45'),
(19, 1073, '3', 'training', 'booking', 100.00, '{\"q6\":\"guest_satisfaction\",\"q7\":\"report_immediately\"}', 15, 'completed', '2025-10-25 05:51:11', NULL, '2025-10-25 05:51:11'),
(20, 1073, '9', 'training', 'booking', 60.00, '{\"q36\":\"correct_68fc65b4ed7d7\",\"q37\":\"correct_68fc65b4ed7dd\",\"q38\":\"correct_68fc65b4ed7e1\",\"q39\":\"correct_68fc65b4ed7e5\",\"q40\":\"correct_68fc65b4ed7e9\",\"q41\":\"correct_68fc65b4ed7ed\",\"q42\":\"correct_68fc65b4ed7f1\",\"q43\":\"correct_68fc65b4ed7f5\",\"q44\":\"correct_68fc65b4ed7f9\",\"q45\":\"correct_68fc65b4ed7fd\"}', 15, 'completed', '2025-10-25 05:53:46', NULL, '2025-10-25 05:53:46'),
(21, 1073, '9', 'training', 'booking', 60.00, '{\"q36\":\"correct_68fc65b4ed7d7\",\"q37\":\"correct_68fc65b4ed7dd\",\"q38\":\"correct_68fc65b4ed7e1\",\"q39\":\"correct_68fc65b4ed7e5\",\"q40\":\"correct_68fc65b4ed7e9\",\"q41\":\"correct_68fc65b4ed7ed\",\"q42\":\"correct_68fc65b4ed7f1\",\"q43\":\"correct_68fc65b4ed7f5\",\"q44\":\"correct_68fc65b4ed7f9\",\"q45\":\"correct_68fc65b4ed7fd\"}', 15, 'completed', '2025-10-25 05:53:48', NULL, '2025-10-25 05:53:48'),
(22, 1073, '8', 'training', 'booking', 20.00, '{\"q31\":\"correct_68fc651ea98ff\",\"q32\":\"correct_68fc651ea9907\",\"q33\":\"correct_68fc651ea990b\",\"q34\":\"correct_68fc651ea990f\",\"q35\":\"correct_68fc651ea9913\"}', 15, 'completed', '2025-10-25 05:54:00', NULL, '2025-10-25 05:54:00'),
(23, 1073, '10', 'training', 'booking', 10.00, '{\"q46\":\"correct_68fc66f8e9a47\",\"q47\":\"correct_68fc66f8e9a4e\",\"q48\":\"correct_68fc66f8e9a52\",\"q49\":\"correct_68fc66f8e9a56\",\"q50\":\"correct_68fc66f8e9a5a\",\"q51\":\"correct_68fc66f8e9a5e\",\"q52\":\"correct_68fc66f8e9a62\",\"q53\":\"correct_68fc66f8e9a66\",\"q54\":\"correct_68fc66f8e9a6a\",\"q55\":\"correct_68fc66f8e9a6e\"}', 15, 'completed', '2025-10-25 06:01:26', NULL, '2025-10-25 06:01:26'),
(24, 1073, '11', 'training', 'booking', 40.00, '{\"q56\":\"correct_68fc675d4a8f0\",\"q57\":\"correct_68fc675d4a8f8\",\"q58\":\"correct_68fc675d4a8fd\",\"q59\":\"correct_68fc675d4a901\",\"q60\":\"correct_68fc675d4a905\"}', 15, 'completed', '2025-10-25 06:01:44', NULL, '2025-10-25 06:01:44'),
(25, 1073, '2', 'training', 'booking', 100.00, '{\"q4\":\"listening\",\"q5\":\"stay_calm\"}', 15, 'completed', '2025-10-25 06:06:39', NULL, '2025-10-25 06:06:39'),
(26, 1073, '12', 'training', 'booking', 30.00, '{\"q61\":\"correct_68fc691ef1bd9\",\"q62\":\"correct_68fc691ef1be0\",\"q63\":\"correct_68fc691ef1be4\",\"q64\":\"correct_68fc691ef1be8\",\"q65\":\"correct_68fc691ef1bec\",\"q66\":\"correct_68fc691ef1bf0\",\"q67\":\"correct_68fc691ef1bf4\",\"q68\":\"correct_68fc691ef1bf8\",\"q69\":\"correct_68fc691ef1bfc\",\"q70\":\"correct_68fc691ef1c00\"}', 15, 'completed', '2025-10-25 06:09:47', NULL, '2025-10-25 06:09:47'),
(27, 1073, '13', 'training', 'booking', 40.00, '{\"q71\":\"correct_68fc6bb0bf4cf\",\"q72\":\"correct_68fc6bb0bf4df\",\"q73\":\"incorrect_68fc6bb0bf4e6\",\"q74\":\"correct_68fc6bb0bf4e8\",\"q75\":\"incorrect_68fc6bb0bf4ed\"}', 15, 'completed', '2025-10-25 06:19:15', NULL, '2025-10-25 06:19:15'),
(28, 1073, 'pos_restaurant_order', 'training', 'pos', 0.00, '{\"1\":\"A\",\"2\":\"B\"}', 0, 'completed', '2025-10-26 09:05:49', '2025-10-26 09:06:06', '2025-10-26 09:05:49'),
(29, 1073, 'pos_restaurant_order', 'training', 'pos', 0.00, NULL, 0, 'in_progress', '2025-10-26 09:06:20', NULL, '2025-10-26 09:06:20'),
(30, 1073, 'pos_restaurant_order', 'training', 'pos', 100.00, '{\"1\":\"B\",\"2\":\"C\"}', 0, 'completed', '2025-10-26 09:07:59', '2025-10-26 09:08:26', '2025-10-26 09:07:59'),
(31, 1073, 'pos_restaurant_order', 'training', 'pos', 100.00, '{\"1\":\"B\",\"2\":\"C\"}', 0, 'completed', '2025-10-26 09:10:07', '2025-10-26 09:10:18', '2025-10-26 09:10:07'),
(32, 1073, 'pos_restaurant_order', 'training', 'pos', 100.00, '{\"1\":\"B\",\"2\":\"C\"}', 0, 'completed', '2025-10-26 09:14:17', '2025-10-26 09:14:47', '2025-10-26 09:14:17');

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
  `certificate_name` varchar(255) NOT NULL,
  `certificate_type` varchar(100) NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `status` enum('earned','expired','revoked') DEFAULT 'earned',
  `earned_at` timestamp NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `training_certificates`
--

INSERT INTO `training_certificates` (`id`, `user_id`, `certificate_name`, `certificate_type`, `score`, `status`, `earned_at`, `expires_at`, `created_at`) VALUES
(1, 1071, 'Front Desk Operations Certificate', 'training', 85.50, 'earned', '2025-10-21 05:38:59', NULL, '2025-10-23 05:38:57'),
(2, 1071, 'Customer Service Excellence Certificate', 'customer_service', 92.00, 'earned', '2025-10-22 05:38:59', NULL, '2025-10-23 05:38:57'),
(3, 1071, 'Training Excellence Certificate', 'training', 92.00, 'earned', '2025-10-22 05:58:59', NULL, '2025-10-23 08:43:32'),
(4, 1073, 'Training Excellence Certificate', 'training', 100.00, 'earned', '2025-10-25 05:51:11', NULL, '2025-10-25 05:51:11'),
(5, 1073, 'Training Excellence Certificate', 'training', 100.00, 'earned', '2025-10-25 06:06:39', NULL, '2025-10-25 06:06:39');

-- --------------------------------------------------------

--
-- Table structure for table `training_logs`
--

CREATE TABLE `training_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `scenario_name` varchar(255) NOT NULL,
  `status` enum('In Progress','Completed') NOT NULL DEFAULT 'In Progress',
  `start_time` datetime NOT NULL DEFAULT current_timestamp(),
  `end_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `training_logs`
--

INSERT INTO `training_logs` (`id`, `user_id`, `scenario_name`, `status`, `start_time`, `end_time`) VALUES
(1, 1033, 'Add Inventory Item', 'Completed', '2025-10-17 15:58:44', '2025-10-17 15:59:17'),
(2, 1033, 'Update Room Inventory', 'Completed', '2025-10-17 16:00:33', '2025-10-17 16:01:06'),
(3, 1029, 'Manage Inventory Items', 'In Progress', '2025-10-17 16:11:11', NULL),
(4, 1029, 'Manage Inventory Items', 'Completed', '2025-10-17 16:11:59', '2025-10-17 16:12:13'),
(5, 1033, 'Update Room Inventory', 'In Progress', '2025-10-17 16:24:28', NULL),
(6, 1029, 'View Reports', 'In Progress', '2025-10-17 16:24:56', NULL),
(7, 1029, 'View Reports', 'In Progress', '2025-10-17 16:29:35', NULL),
(8, 1029, 'Enhanced Reports & Analysis', 'Completed', '2025-10-17 16:31:56', '2025-10-17 16:32:11'),
(9, 1029, 'Manage Inventory Items', 'Completed', '2025-10-17 16:33:32', '2025-10-17 16:34:12'),
(10, 1029, 'Configure Auto Reordering', 'In Progress', '2025-10-17 16:34:30', NULL),
(11, 1029, 'Configure Auto Reordering', 'In Progress', '2025-10-17 16:35:12', NULL),
(12, 1029, 'Enhanced Reports & Analysis', 'In Progress', '2025-10-17 16:36:56', NULL),
(13, 1029, 'Enhanced Reports & Analysis', 'In Progress', '2025-10-17 16:39:48', NULL),
(14, 1029, 'Use Barcode Scanner', 'In Progress', '2025-10-17 16:43:23', NULL),
(15, 1029, 'Manage Room Inventory (Manager)', 'Completed', '2025-10-17 16:43:38', '2025-10-17 16:43:48'),
(16, 1029, 'Configure Auto Reordering', 'In Progress', '2025-10-17 16:49:31', NULL),
(17, 1033, 'Request Supplies', 'In Progress', '2025-10-17 16:50:30', NULL),
(18, 1033, 'Submit Transaction', 'In Progress', '2025-10-17 16:55:03', NULL),
(19, 1033, 'Submit Transaction', 'In Progress', '2025-10-17 16:57:00', NULL),
(20, 1029, 'Enhanced Reports & Analysis', 'In Progress', '2025-10-17 16:57:28', NULL),
(21, 1029, 'View Reports', 'In Progress', '2025-10-21 13:45:57', NULL),
(22, 1073, 'View Reports', 'In Progress', '2025-10-23 14:17:25', NULL);

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
  `scenario_id` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category` enum('front_desk','housekeeping','management','pos_restaurant','pos_room_service','pos_spa','pos_gift_shop','pos_events','pos_quick_sales','pos_customer_service','pos_general','customer_service','problem_solving') DEFAULT 'front_desk',
  `difficulty` enum('beginner','intermediate','advanced') NOT NULL,
  `estimated_time` int(11) NOT NULL DEFAULT 15,
  `points` int(11) NOT NULL DEFAULT 100,
  `status` enum('active','inactive','draft') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `training_scenarios`
--

INSERT INTO `training_scenarios` (`id`, `scenario_id`, `title`, `description`, `category`, `difficulty`, `estimated_time`, `points`, `status`, `created_at`, `updated_at`) VALUES
(1, 'front_desk_basic', 'Front Desk Check-in Process', 'Learn essential check-in and check-out procedures with real scenarios.', 'front_desk', 'beginner', 15, 100, 'active', '2025-10-23 05:37:58', '2025-10-23 05:37:58'),
(2, 'customer_service', 'Customer Service Excellence', 'Handle various customer service situations including complaints and special requests.', 'customer_service', 'intermediate', 25, 200, 'active', '2025-10-23 05:37:58', '2025-10-23 05:37:58'),
(3, 'problem_solving', 'Problem Solving & Crisis Management', 'Handle various hotel problems and crisis situations that require quick thinking.', 'problem_solving', 'advanced', 30, 300, 'active', '2025-10-23 05:37:58', '2025-10-23 05:37:58'),
(5, 'customer_service_68f9da4bd4497', 'Customer Service Excellence', 'qweqw', 'customer_service', 'beginner', 15, 100, 'active', '2025-10-23 07:33:29', '2025-10-23 07:33:29'),
(6, 'front_desk_68fc308ddce83', 'Front Desk Check-in Process', 'wew', 'front_desk', 'advanced', 15, 50, 'active', '2025-10-25 02:06:05', '2025-10-25 02:06:05'),
(7, 'front_desk_68fc63ae79adc', 'Front Desk Check-in Process', '10', 'front_desk', 'beginner', 15, 50, 'active', '2025-10-25 05:44:14', '2025-10-25 05:44:14'),
(8, 'problem_solving_68fc65224a017', 'Problem Solving & Crisis Management', '10', 'problem_solving', 'advanced', 15, 50, 'active', '2025-10-25 05:50:26', '2025-10-25 05:50:26'),
(9, 'problem_solving_68fc65b900c50', 'Problem Solving & Crisis Management', '5', 'problem_solving', 'advanced', 20, 50, 'active', '2025-10-25 05:52:57', '2025-10-25 05:52:57'),
(10, 'front_desk_68fc66fe49c9a', 'Front Desk Check-in Process', 'free', 'front_desk', 'intermediate', 15, 50, 'active', '2025-10-25 05:58:22', '2025-10-25 05:58:22'),
(11, 'problem_solving_68fc676018c8a', 'Problem Solving & Crisis Management', 'make it hard so that my students will double guess their answer', 'problem_solving', 'intermediate', 15, 100, 'active', '2025-10-25 06:00:00', '2025-10-25 06:00:00'),
(12, 'front_desk_68fc69296def0', 'Front Desk Check-in Process', 'make it hard so that my students will double guess their answer', 'front_desk', 'advanced', 15, 500, 'active', '2025-10-25 06:07:37', '2025-10-25 06:07:37'),
(13, 'customer_service_68fc6bd3737d0', 'Customer Service Excellence', 'wew1231', 'customer_service', 'beginner', 15, 50, 'active', '2025-10-25 06:18:58', '2025-10-25 06:18:58'),
(14, 'pos_restaurant_order', 'Restaurant Order Processing', 'Process a dine-in order with multiple items and special requests. Take order, add items to POS, apply discounts if needed, and process payment accurately.', 'pos_restaurant', 'beginner', 15, 15, 'active', '2025-10-26 08:28:55', '2025-10-26 08:28:55'),
(15, 'pos_restaurant_split_bill', 'Split Bill Handling', 'Handle a table request to split the bill among multiple guests. Process split payments correctly and ensure accurate billing for each guest.', 'pos_restaurant', 'intermediate', 20, 25, 'active', '2025-10-26 08:28:55', '2025-10-26 08:28:55'),
(16, 'pos_restaurant_busy_hour', 'Rush Hour Service', 'Manage multiple orders during peak dining hours. Prioritize orders, maintain accuracy, and provide quick service while handling high volume.', 'pos_restaurant', 'advanced', 25, 35, 'active', '2025-10-26 08:28:55', '2025-10-26 08:28:55'),
(17, 'pos_room_service_order', 'Room Service Order', 'Process a room service order and assign for delivery. Take phone order, input to POS, coordinate with kitchen and delivery team.', 'pos_room_service', 'beginner', 12, 12, 'active', '2025-10-26 08:28:55', '2025-10-26 08:28:55'),
(18, 'pos_room_service_special', 'Special Dietary Request', 'Handle room service order with dietary restrictions. Accommodate special requests and ensure proper communication with kitchen staff.', 'pos_room_service', 'intermediate', 18, 22, 'active', '2025-10-26 08:28:55', '2025-10-26 08:28:55'),
(19, 'pos_room_service_complaint', 'Service Recovery', 'Handle a complaint about delayed room service. Resolve issue professionally and offer appropriate compensation to maintain guest satisfaction.', 'pos_room_service', 'advanced', 20, 30, 'active', '2025-10-26 08:28:55', '2025-10-26 08:28:55'),
(20, 'pos_spa_booking', 'Spa Service Booking', 'Book a spa appointment and process payment. Check availability, book service, collect payment, and confirm details with the guest.', 'pos_spa', 'beginner', 10, 10, 'active', '2025-10-26 08:28:55', '2025-10-26 08:28:55'),
(21, 'pos_spa_package', 'Spa Package Sale', 'Sell a multi-service spa package to a guest. Explain package benefits, process sale, and schedule appointments for all services.', 'pos_spa', 'intermediate', 15, 20, 'active', '2025-10-26 08:28:55', '2025-10-26 08:28:55'),
(22, 'pos_spa_upsell', 'Service Upselling', 'Upsell additional spa services to existing clients. Recommend complementary services based on guest needs and close the sale.', 'pos_spa', 'advanced', 18, 28, 'active', '2025-10-26 08:28:55', '2025-10-26 08:28:55'),
(23, 'pos_gift_shop_sale', 'Gift Shop Transaction', 'Process a gift shop purchase with multiple items. Scan items, apply discounts, process payment, and bag items properly.', 'pos_gift_shop', 'beginner', 8, 8, 'active', '2025-10-26 08:28:55', '2025-10-26 08:28:55'),
(24, 'pos_gift_shop_return', 'Product Return', 'Handle a product return request from a guest. Verify receipt, check product condition, and process refund according to hotel policy.', 'pos_gift_shop', 'intermediate', 12, 18, 'active', '2025-10-26 08:28:55', '2025-10-26 08:28:55'),
(25, 'pos_gift_shop_inventory', 'Stock Check', 'Assist customer while managing inventory levels. Check stock availability, suggest alternatives if needed, and update inventory records.', 'pos_gift_shop', 'intermediate', 15, 20, 'active', '2025-10-26 08:28:55', '2025-10-26 08:28:55'),
(26, 'pos_events_booking', 'Event Booking', 'Process an event booking with venue and services. Record event details, calculate costs, and collect deposit payment.', 'pos_events', 'intermediate', 20, 25, 'active', '2025-10-26 08:28:55', '2025-10-26 08:28:55'),
(27, 'pos_events_addon', 'Event Add-on Services', 'Add additional services to an existing event booking. Update booking details and calculate additional charges accurately.', 'pos_events', 'beginner', 12, 15, 'active', '2025-10-26 08:28:55', '2025-10-26 08:28:55'),
(28, 'pos_events_final_bill', 'Event Final Billing', 'Process final payment for completed event. Review all charges, apply any adjustments or credits, and collect final payment.', 'pos_events', 'advanced', 25, 35, 'active', '2025-10-26 08:28:55', '2025-10-26 08:28:55'),
(29, 'pos_quick_snacks', 'Quick Snack Sale', 'Rapidly process a quick sale transaction. Select items, process payment quickly and accurately while maintaining customer service.', 'pos_quick_sales', 'beginner', 5, 8, 'active', '2025-10-26 08:28:55', '2025-10-26 08:28:55'),
(30, 'pos_quick_multiple', 'Multiple Quick Sales', 'Handle multiple quick sale transactions in succession. Maintain accuracy and speed with back-to-back transactions during busy periods.', 'pos_quick_sales', 'intermediate', 10, 15, 'active', '2025-10-26 08:28:55', '2025-10-26 08:28:55'),
(31, 'pos_payment_issue', 'Payment Processing Issue', 'Resolve a declined credit card payment professionally. Handle the situation diplomatically and offer alternative payment methods.', 'pos_customer_service', 'intermediate', 12, 18, 'active', '2025-10-26 08:28:55', '2025-10-26 08:28:55'),
(32, 'pos_price_dispute', 'Price Dispute', 'Handle a guest disputing a charge on their bill. Review charges, explain billing clearly, and resolve the situation professionally.', 'pos_customer_service', 'advanced', 15, 25, 'active', '2025-10-26 08:28:55', '2025-10-26 08:28:55'),
(33, 'pos_system_down', 'System Downtime', 'Process transactions during POS system issues. Use manual backup procedures while maintaining accuracy and security.', 'pos_customer_service', 'advanced', 20, 30, 'active', '2025-10-26 08:28:55', '2025-10-26 08:28:55'),
(34, 'pos_opening_shift', 'Opening Shift Procedures', 'Complete all opening procedures for POS station. Perform cash count, system startup, and prepare workstation for the day.', 'pos_general', 'beginner', 15, 15, 'active', '2025-10-26 08:28:55', '2025-10-26 08:28:55'),
(35, 'pos_closing_shift', 'Closing Shift Procedures', 'Properly close out POS station at end of shift. Complete cash reconciliation, generate reports, and shut down system properly.', 'pos_general', 'beginner', 18, 18, 'active', '2025-10-26 08:28:55', '2025-10-26 08:28:55'),
(36, 'pos_cash_handling', 'Cash Handling Best Practices', 'Demonstrate proper cash handling procedures. Count money accurately, maintain cash drawer security, and follow cash policies.', 'pos_general', 'intermediate', 12, 15, 'active', '2025-10-26 08:28:55', '2025-10-26 08:28:55'),
(37, 'pos_security', 'POS Security Protocol', 'Follow security procedures for POS operations. Protect sensitive customer data and secure all payment transactions.', 'pos_general', 'intermediate', 15, 20, 'active', '2025-10-26 08:28:55', '2025-10-26 08:28:55');

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
(1071, 'John Smith', 'frontdesk1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'john@hotel.com', 'front_desk', 1, '2025-10-21 06:09:29', '2025-10-21 06:09:29'),
(1072, 'Maria Garcia', 'housekeeping1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'maria@hotel.com', 'housekeeping', 1, '2025-10-21 06:09:29', '2025-10-21 06:09:29'),
(1073, 'David Johnson', 'manager1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'david@hotel.com', 'manager', 1, '2025-10-21 06:09:29', '2025-10-21 06:09:29'),
(1074, 'Sarah Wilson', 'frontdesk2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'sarah@hotel.com', 'front_desk', 1, '2025-10-21 06:09:29', '2025-10-21 06:09:29'),
(1075, 'Carlos Rodriguez', 'housekeeping2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'carlos@hotel.com', 'housekeeping', 1, '2025-10-21 06:09:29', '2025-10-21 06:09:29'),
(1076, 'Emily Chen', 'manager2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'emily@hotel.com', 'manager', 1, '2025-10-21 06:09:29', '2025-10-21 06:09:29'),
(1077, 'jan aron2', 'janx21', '$2y$10$8dMw9Q623XLG0kvxi7L1zOHh0IjM5sXA8NDYgosNlKyX2rTzPUmda', 'jan123@gmail.com', 'front_desk', 1, '2025-10-22 03:47:19', '2025-10-22 09:45:54'),
(1078, 'jay villahermosa', 'jaythedog', '$2y$10$XrwadNrCC2p9kKutPEVNoeiVoPR.o1N1k5WKCJ77W.Y15S4G4/QLK', 'jayvillahermosa@gmail.com', 'front_desk', 1, '2025-10-25 05:38:21', '2025-10-25 05:38:21');

-- --------------------------------------------------------

--
-- Table structure for table `vip_amenities`
--

CREATE TABLE `vip_amenities` (
  `id` int(11) NOT NULL,
  `guest_id` int(11) NOT NULL,
  `amenity_type` varchar(50) NOT NULL,
  `special_message` text DEFAULT NULL,
  `delivery_instructions` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vip_amenities`
--

INSERT INTO `vip_amenities` (`id`, `guest_id`, `amenity_type`, `special_message`, `delivery_instructions`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 7, 'flowers', '123', 'wew', 'pending', 1029, '2025-10-21 01:45:34', '2025-10-21 01:45:34');

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
(1, 'WELCOME2024', 'percentage', 10.00, 100, '2024-01-01', '2024-12-31', 'Welcome discount for new guests', 'expired', 3, '2025-08-27 10:02:44'),
(2, 'SUMMER2024', 'fixed', 50.00, 50, '2024-06-01', '2024-08-31', 'Summer promotion discount', 'active', 3, '2025-08-27 10:02:44'),
(3, 'VIP2024', 'percentage', 15.00, 25, '2024-01-01', '2024-12-31', 'VIP guest exclusive discount', 'active', 3, '2025-08-27 10:02:44'),
(4, 'SPA2024', 'fixed', 100.00, 5, '2025-10-23', '2026-04-23', 'Complimentary spa treatment worth $100', 'active', 1073, '2025-10-23 02:07:39'),
(5, 'DINE2024', 'fixed', 50.00, 10, '2025-10-23', '2026-01-23', 'Dining credit for restaurant services', 'active', 1073, '2025-10-23 02:07:39'),
(6, 'TOUR2024', 'fixed', 75.00, 3, '2025-10-23', '2026-10-23', 'Guided city tour experience', 'active', 1073, '2025-10-23 02:07:39'),
(7, 'TRANSFER2024', 'fixed', 60.00, 2, '2025-10-23', '2026-04-23', 'Airport transfer service', 'active', 1073, '2025-10-23 02:07:39'),
(8, 'FREENIGHT2024', 'free_night', 150.00, 1, '2025-10-23', '2026-10-23', 'One free night stay', 'active', 1073, '2025-10-23 02:07:40'),
(9, 'UPGRADE2024', 'upgrade', 50.00, 1, '2025-10-23', '2026-04-23', 'Room upgrade to next category', 'active', 1073, '2025-10-23 02:07:40'),
(10, 'TES20257325', 'percentage', 15.00, 5, '2025-10-23', '2026-01-23', 'Test percentage voucher', 'active', 1073, '2025-10-23 02:08:08'),
(11, 'WEW20256679', 'percentage', 10.00, 1, '2025-10-23', '2025-11-23', 'wew', 'active', 1073, '2025-10-23 02:16:40');

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
  ADD KEY `check_in_records_ibfk_1` (`reservation_id`),
  ADD KEY `check_in_records_ibfk_2` (`checked_in_by`);

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
  ADD UNIQUE KEY `scenario_id` (`scenario_id`),
  ADD KEY `idx_customer_service_scenarios_type` (`type`),
  ADD KEY `idx_customer_service_scenarios_difficulty` (`difficulty`),
  ADD KEY `idx_customer_service_scenarios_status` (`status`);

--
-- Indexes for table `discounts`
--
ALTER TABLE `discounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bill_id` (`bill_id`),
  ADD KEY `applied_by` (`applied_by`);

--
-- Indexes for table `discount_templates`
--
ALTER TABLE `discount_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`discount_type`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_dates` (`valid_from`,`valid_until`),
  ADD KEY `idx_room_id` (`room_id`),
  ADD KEY `idx_room_type` (`room_type`),
  ADD KEY `idx_apply_all` (`apply_to_all_rooms`);

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
  ADD PRIMARY KEY (`id`);

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
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `inventory_journal_entries`
--
ALTER TABLE `inventory_journal_entries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_question_options`
--
ALTER TABLE `inventory_question_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `option_order` (`option_order`);

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
-- Indexes for table `inventory_scenario_questions`
--
ALTER TABLE `inventory_scenario_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `scenario_id` (`scenario_id`),
  ADD KEY `question_order` (`question_order`);

--
-- Indexes for table `inventory_suppliers`
--
ALTER TABLE `inventory_suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_training_attempts`
--
ALTER TABLE `inventory_training_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `scenario_id` (`scenario_id`),
  ADD KEY `status` (`status`),
  ADD KEY `started_at` (`started_at`);

--
-- Indexes for table `inventory_training_certificates`
--
ALTER TABLE `inventory_training_certificates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `certificate_type` (`certificate_type`),
  ADD KEY `earned_at` (`earned_at`);

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
  ADD KEY `performed_by` (`performed_by`);

--
-- Indexes for table `inventory_usage_reports`
--
ALTER TABLE `inventory_usage_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `date_used` (`date_used`),
  ADD KEY `room` (`room`);

--
-- Indexes for table `loyalty_points`
--
ALTER TABLE `loyalty_points`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guest_id` (`guest_id`),
  ADD KEY `processed_by` (`processed_by`);

--
-- Indexes for table `loyalty_redemptions`
--
ALTER TABLE `loyalty_redemptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_guest_id` (`guest_id`),
  ADD KEY `idx_reward_id` (`reward_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `loyalty_rewards`
--
ALTER TABLE `loyalty_rewards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_points_required` (`points_required`),
  ADD KEY `idx_reward_type` (`reward_type`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `loyalty_transactions`
--
ALTER TABLE `loyalty_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `idx_guest_id` (`guest_id`),
  ADD KEY `idx_transaction_type` (`transaction_type`),
  ADD KEY `idx_created_at` (`created_at`);

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
  ADD UNIQUE KEY `scenario_id` (`scenario_id`),
  ADD KEY `idx_problem_scenarios_severity` (`severity`),
  ADD KEY `idx_problem_scenarios_difficulty` (`difficulty`),
  ADD KEY `idx_problem_scenarios_status` (`status`);

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
  ADD UNIQUE KEY `room_number` (`room_number`),
  ADD KEY `idx_rooms_assigned_housekeeping` (`assigned_housekeeping`);

--
-- Indexes for table `room_inventory`
--
ALTER TABLE `room_inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_item` (`room_id`,`item_id`),
  ADD KEY `item_id` (`item_id`);

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
  ADD KEY `assigned_to` (`assigned_to`);

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
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `idx_supply_requests_room_number` (`room_number`),
  ADD KEY `idx_supply_requests_status` (`status`),
  ADD KEY `idx_supply_requests_requested_by` (`requested_by`);

--
-- Indexes for table `training_attempts`
--
ALTER TABLE `training_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_training_attempts_user_id` (`user_id`),
  ADD KEY `idx_training_attempts_scenario_id` (`scenario_id`),
  ADD KEY `idx_training_attempts_status` (`status`);

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
  ADD KEY `idx_training_certificates_user_id` (`user_id`),
  ADD KEY `idx_training_certificates_status` (`status`);

--
-- Indexes for table `training_logs`
--
ALTER TABLE `training_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `scenario_name` (`scenario_name`),
  ADD KEY `status` (`status`);

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
  ADD UNIQUE KEY `scenario_id` (`scenario_id`),
  ADD KEY `idx_training_scenarios_category` (`category`),
  ADD KEY `idx_training_scenarios_difficulty` (`difficulty`),
  ADD KEY `idx_training_scenarios_status` (`status`);

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
-- Indexes for table `vip_amenities`
--
ALTER TABLE `vip_amenities`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=591;

--
-- AUTO_INCREMENT for table `additional_services`
--
ALTER TABLE `additional_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `barcode_tracking`
--
ALTER TABLE `barcode_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `billing`
--
ALTER TABLE `billing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `bills`
--
ALTER TABLE `bills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `bill_items`
--
ALTER TABLE `bill_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- AUTO_INCREMENT for table `cost_analysis_reports`
--
ALTER TABLE `cost_analysis_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_service_scenarios`
--
ALTER TABLE `customer_service_scenarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `discounts`
--
ALTER TABLE `discounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `discount_templates`
--
ALTER TABLE `discount_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

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
-- AUTO_INCREMENT for table `group_bookings`
--
ALTER TABLE `group_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guests`
--
ALTER TABLE `guests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `guest_feedback`
--
ALTER TABLE `guest_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `inventory_journal_entries`
--
ALTER TABLE `inventory_journal_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `inventory_question_options`
--
ALTER TABLE `inventory_question_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=437;

--
-- AUTO_INCREMENT for table `inventory_requests`
--
ALTER TABLE `inventory_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inventory_request_items`
--
ALTER TABLE `inventory_request_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inventory_scenario_questions`
--
ALTER TABLE `inventory_scenario_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT for table `inventory_suppliers`
--
ALTER TABLE `inventory_suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_training_attempts`
--
ALTER TABLE `inventory_training_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `inventory_training_certificates`
--
ALTER TABLE `inventory_training_certificates`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_usage_reports`
--
ALTER TABLE `inventory_usage_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loyalty_points`
--
ALTER TABLE `loyalty_points`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `loyalty_redemptions`
--
ALTER TABLE `loyalty_redemptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `loyalty_rewards`
--
ALTER TABLE `loyalty_rewards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `loyalty_transactions`
--
ALTER TABLE `loyalty_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pos_activity_log`
--
ALTER TABLE `pos_activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=291;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `question_options`
--
ALTER TABLE `question_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=353;

--
-- AUTO_INCREMENT for table `reorder_rules`
--
ALTER TABLE `reorder_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `room_inventory`
--
ALTER TABLE `room_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `room_inventory_items`
--
ALTER TABLE `room_inventory_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `room_inventory_transactions`
--
ALTER TABLE `room_inventory_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `scenario_questions`
--
ALTER TABLE `scenario_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `service_charges`
--
ALTER TABLE `service_charges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_requests`
--
ALTER TABLE `service_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `supply_requests`
--
ALTER TABLE `supply_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `training_attempts`
--
ALTER TABLE `training_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `training_categories`
--
ALTER TABLE `training_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `training_certificates`
--
ALTER TABLE `training_certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `training_logs`
--
ALTER TABLE `training_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1079;

--
-- AUTO_INCREMENT for table `vip_amenities`
--
ALTER TABLE `vip_amenities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

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
  ADD CONSTRAINT `check_in_records_ibfk_2` FOREIGN KEY (`checked_in_by`) REFERENCES `users` (`id`);

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
-- Constraints for table `inventory_question_options`
--
ALTER TABLE `inventory_question_options`
  ADD CONSTRAINT `inventory_question_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `inventory_scenario_questions` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `inventory_scenario_questions`
--
ALTER TABLE `inventory_scenario_questions`
  ADD CONSTRAINT `inventory_scenario_questions_ibfk_1` FOREIGN KEY (`scenario_id`) REFERENCES `inventory_training_scenarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_training_attempts`
--
ALTER TABLE `inventory_training_attempts`
  ADD CONSTRAINT `inventory_training_attempts_ibfk_1` FOREIGN KEY (`scenario_id`) REFERENCES `inventory_training_scenarios` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `inventory_transactions_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`id`),
  ADD CONSTRAINT `inventory_transactions_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `inventory_usage_reports`
--
ALTER TABLE `inventory_usage_reports`
  ADD CONSTRAINT `inventory_usage_reports_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_usage_reports_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loyalty_points`
--
ALTER TABLE `loyalty_points`
  ADD CONSTRAINT `loyalty_points_ibfk_1` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`),
  ADD CONSTRAINT `loyalty_points_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `loyalty_redemptions`
--
ALTER TABLE `loyalty_redemptions`
  ADD CONSTRAINT `loyalty_redemptions_ibfk_1` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `loyalty_redemptions_ibfk_2` FOREIGN KEY (`reward_id`) REFERENCES `loyalty_rewards` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loyalty_transactions`
--
ALTER TABLE `loyalty_transactions`
  ADD CONSTRAINT `loyalty_transactions_ibfk_1` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `loyalty_transactions_ibfk_2` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL;

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
  ADD CONSTRAINT `quality_checks_ibfk_2` FOREIGN KEY (`inspector_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `question_options`
--
ALTER TABLE `question_options`
  ADD CONSTRAINT `question_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `scenario_questions` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `fk_rooms_assigned_housekeeping` FOREIGN KEY (`assigned_housekeeping`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`assigned_housekeeping`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
  ADD CONSTRAINT `scenario_questions_ibfk_1` FOREIGN KEY (`scenario_id`) REFERENCES `training_scenarios` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `service_requests_ibfk_4` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `service_requests_ibfk_5` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`);

--
-- Constraints for table `supply_requests`
--
ALTER TABLE `supply_requests`
  ADD CONSTRAINT `supply_requests_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `supply_requests_ibfk_2` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `supply_requests_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `training_categories`
--
ALTER TABLE `training_categories`
  ADD CONSTRAINT `training_categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `training_categories` (`id`) ON DELETE SET NULL;

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
