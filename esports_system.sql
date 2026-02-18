-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Jan 26, 2026 at 06:22 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `esports_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `log_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `target_type` varchar(100) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_logs`
--

INSERT INTO `admin_logs` (`log_id`, `admin_id`, `action`, `target_type`, `target_id`, `description`, `ip_address`, `created_at`) VALUES
(1, 1, 'CREATE_TOURNAMENT', 'tournament', 1, 'Created tournament: Bom Bom fire', '::1', '2026-01-11 22:14:21'),
(2, 1, 'UPDATE_TOURNAMENT_STATUS', 'tournament', 1, 'Status changed: Upcoming → Ongoing', '::1', '2026-01-12 10:46:04'),
(3, 1, 'approve', 'participant', 1, 'APPROVE participant ID 1 in Tournament 1', '::1', '2026-01-12 10:46:21'),
(4, 1, 'approve', 'participant', 1, 'APPROVE participant ID 1 in Tournament 1', '::1', '2026-01-12 10:46:25'),
(5, 1, 'APPROVE_HOSTING', 'tournament', 3, 'Approved Hosting ID 1 -> Tournament 3', '::1', '2026-01-12 20:10:45'),
(6, 1, 'Opened conversation', 'Chat', 1, NULL, NULL, '2026-01-14 16:23:20'),
(7, 1, 'Opened conversation', 'Chat', 1, NULL, NULL, '2026-01-14 16:23:23'),
(8, 1, 'Opened conversation', 'Chat', 2, NULL, NULL, '2026-01-14 16:31:11'),
(9, 1, 'Opened conversation', 'Chat', 2, NULL, NULL, '2026-01-14 16:34:19'),
(10, 1, 'Opened conversation', 'Chat', 2, NULL, NULL, '2026-01-14 16:34:20'),
(11, 1, 'Opened conversation', 'Chat', 2, NULL, NULL, '2026-01-14 16:37:02'),
(12, 1, 'Opened conversation', 'Chat', 2, NULL, NULL, '2026-01-14 16:38:05'),
(13, 1, 'Opened conversation', 'Chat', 2, NULL, NULL, '2026-01-14 16:44:27'),
(14, 1, 'Opened conversation', 'Chat', 2, NULL, NULL, '2026-01-14 16:46:34'),
(15, 1, 'Opened conversation', 'Chat', 2, NULL, NULL, '2026-01-14 16:46:41'),
(16, 1, 'Opened conversation', 'Chat', 2, NULL, NULL, '2026-01-14 16:46:41'),
(17, 1, 'Opened conversation', 'Chat', 2, NULL, NULL, '2026-01-17 15:49:29'),
(18, 1, 'Opened conversation', 'Chat', 2, NULL, NULL, '2026-01-17 15:49:33'),
(19, 1, 'Opened conversation', 'Chat', 2, NULL, NULL, '2026-01-17 15:49:33'),
(20, 1, 'Opened conversation', 'Chat', 1, NULL, NULL, '2026-01-19 17:32:12'),
(21, 1, 'Opened conversation', 'Chat', 1, NULL, NULL, '2026-01-19 19:21:38'),
(22, 1, 'Opened conversation', 'Chat', 1, NULL, NULL, '2026-01-20 02:38:38'),
(23, 1, 'APPROVE_HOSTING', 'tournament', 4, 'Approved Hosting ID 2 -> Tournament 4', '::1', '2026-01-20 04:06:07'),
(24, 1, 'Opened conversation', 'Chat', 2, NULL, NULL, '2026-01-25 19:59:18'),
(25, 1, 'Opened conversation', 'Chat', 1, NULL, NULL, '2026-01-25 19:59:22'),
(26, 1, 'Opened conversation', 'Chat', 2, NULL, NULL, '2026-01-25 19:59:22'),
(27, 1, 'CREATE_TOURNAMENT', 'tournament', 5, 'Created tournament: bd pubg', '::1', '2026-01-25 20:09:18'),
(28, 1, 'CREATE_TOURNAMENT', 'tournament', 6, 'Created tournament: uiu ff', '::1', '2026-01-25 21:08:29'),
(29, 1, 'PRODUCT_ADD', NULL, NULL, 'Added new product ID 6', NULL, '2026-01-25 21:32:20'),
(30, 1, 'PRODUCT_ADD', NULL, NULL, 'Added new product ID 7', NULL, '2026-01-25 21:32:43'),
(31, 1, 'PRODUCT_ADD', NULL, NULL, 'Added new product ID 8', NULL, '2026-01-25 21:33:12'),
(32, 1, 'PRODUCT_ADD', NULL, NULL, 'Added new product ID 9', NULL, '2026-01-25 21:55:15'),
(33, 1, 'CREATE', 'Notification', 1, 'Created notification: upcoming tournament', '::1', '2026-01-26 05:26:35'),
(34, 1, 'CREATE', 'Notification', 2, 'Created notification: gfdsgs', '::1', '2026-01-26 06:04:51'),
(35, 1, 'CREATE', 'Notification', 3, 'Created notification: fgdgh', '::1', '2026-01-26 06:37:23'),
(36, 1, 'CREATE', 'Notification', 4, 'Created notification: sfdgsg', '::1', '2026-01-26 07:24:14'),
(37, 1, 'CREATE', 'Notification', 5, 'Created notification: sgdsfg', '::1', '2026-01-26 07:46:24'),
(38, 1, 'APPROVE_HOSTING', 'tournament', 7, 'Approved Hosting ID 3 -> Tournament 7', '::1', '2026-01-26 17:07:36'),
(39, 1, 'PRODUCT_ADD', NULL, NULL, 'Added new product ID 10', NULL, '2026-01-26 17:13:44');

-- --------------------------------------------------------

--
-- Table structure for table `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `admin_notification_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `short_message` varchar(255) NOT NULL,
  `full_message` text NOT NULL,
  `type` enum('System','Tournament','Hosting','Subscription','Product','Order','Security') NOT NULL,
  `target_type` enum('ALL','STUDENTS','SUBSCRIBED_STUDENTS','UNIVERSITY','PROFESSION','INDIVIDUAL') NOT NULL,
  `target_value` varchar(255) DEFAULT NULL,
  `redirect_url` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `status` enum('draft','sent') DEFAULT 'sent',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_notifications`
--

INSERT INTO `admin_notifications` (`admin_notification_id`, `title`, `short_message`, `full_message`, `type`, `target_type`, `target_value`, `redirect_url`, `created_by`, `status`, `created_at`) VALUES
(1, 'upcoming tournament', 'match will be started soon', 'sfkalflda', 'Tournament', 'ALL', NULL, NULL, 1, 'sent', '2026-01-26 05:26:35'),
(2, 'gfdsgs', 'dsfgsd', 'dfgsfgsdfg', 'Tournament', 'ALL', 'bdfg', 'fdgbdfgb', 1, 'sent', '2026-01-26 06:04:51'),
(3, 'fgdgh', 'dgfhdfgh', 'dgfdhdgf', 'System', 'STUDENTS', 'fgdhg', 'gfhdfg', 1, 'sent', '2026-01-26 06:37:23'),
(4, 'sfdgsg', 'sfdgsdfg', 'sgfdgs', 'Tournament', 'STUDENTS', 'fdsgsdfg', 'sfdgsd', 1, 'sent', '2026-01-26 07:24:14'),
(5, 'sgdsfg', 'fdsgsdfg', 'sfdgsdfg', 'System', 'STUDENTS', 'dfgsdf', 'fdgsdf', 1, 'sent', '2026-01-26 07:46:24');

-- --------------------------------------------------------

--
-- Table structure for table `chat_conversations`
--

CREATE TABLE `chat_conversations` (
  `conversation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `last_message_at` datetime DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chat_conversations`
--

INSERT INTO `chat_conversations` (`conversation_id`, `user_id`, `admin_id`, `last_message_at`, `created_at`) VALUES
(1, 2, 1, '2026-01-19 23:31:44', '2026-01-14 22:23:19'),
(2, 3, 1, '2026-01-26 01:48:50', '2026-01-14 22:31:00');

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `message_id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `sender_role` enum('admin','user') NOT NULL,
  `message_text` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `sent_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`message_id`, `conversation_id`, `sender_id`, `sender_role`, `message_text`, `is_read`, `sent_at`) VALUES
(1, 2, 3, 'user', 'hi', 1, '2026-01-14 22:31:01'),
(2, 2, 1, 'admin', 'ki khbr', 1, '2026-01-14 22:46:41'),
(3, 2, 3, 'user', 'valo', 1, '2026-01-14 23:11:12'),
(4, 2, 1, 'admin', 'ki khbr', 1, '2026-01-17 21:49:33'),
(5, 1, 2, 'user', 'hiiiiii', 1, '2026-01-19 23:31:44'),
(6, 2, 3, 'user', 'hiii', 1, '2026-01-26 01:48:50');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(255) NOT NULL,
  `department_code` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`, `department_code`, `created_at`) VALUES
(1, 'Computer Science and Engineering', 'CSE', '2026-01-11 19:22:26'),
(2, 'Software Engineering', 'SWE', '2026-01-11 19:22:26'),
(3, 'Electrical and Electronic Engineering', 'EEE', '2026-01-11 19:22:26'),
(4, 'Business Administration', 'BBA', '2026-01-11 19:22:26'),
(5, 'Economics', 'ECON', '2026-01-11 19:22:26'),
(6, 'English', 'ENG', '2026-01-11 19:22:26'),
(7, 'Law', 'LAW', '2026-01-11 19:22:26'),
(8, 'Pharmacy', 'PHAR', '2026-01-11 19:22:26'),
(9, 'Civil Engineering', 'CE', '2026-01-11 19:22:26'),
(10, 'Mechanical Engineering', 'ME', '2026-01-11 19:22:26');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `host_id` int(11) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `game_category` varchar(100) NOT NULL,
  `event_type` enum('University Only','Open For All') NOT NULL,
  `hosting_university_id` int(11) DEFAULT NULL,
  `prize_pool` decimal(12,2) DEFAULT NULL,
  `rules` text DEFAULT NULL,
  `max_participants` int(11) DEFAULT NULL,
  `current_participants` int(11) DEFAULT 0,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('Upcoming','Running','Completed','Cancelled') DEFAULT 'Upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `request_id`, `host_id`, `event_name`, `game_category`, `event_type`, `hosting_university_id`, `prize_pool`, `rules`, `max_participants`, `current_participants`, `start_date`, `end_date`, `status`, `created_at`) VALUES
(2, 1, 3, 'BPGL', 'PUBG Mobile', 'University Only', NULL, 5000.00, NULL, NULL, 0, '2026-01-14', '2026-01-16', '', '2026-01-12 20:10:45'),
(3, 2, 2, 'sdafa', 'Free Fire', 'University Only', NULL, 555555.00, NULL, NULL, 0, '2026-01-21', '2026-01-24', '', '2026-01-20 04:06:07'),
(4, 3, 2, 'sdafatyutryjt', 'PUBG Mobile', 'University Only', NULL, 5000.00, NULL, NULL, 0, '2026-01-27', '2026-01-29', '', '2026-01-26 17:07:36');

-- --------------------------------------------------------

--
-- Table structure for table `hosting_requests`
--

CREATE TABLE `hosting_requests` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `game_category` varchar(100) NOT NULL,
  `event_type` enum('University Only','Open For All') NOT NULL,
  `hosting_university_id` int(11) DEFAULT NULL,
  `expected_participants` int(11) DEFAULT NULL,
  `prize_pool` decimal(12,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `rules` text DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('Pending','Approved','Rejected','Modified') DEFAULT 'Pending',
  `admin_notes` text DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hosting_requests`
--

INSERT INTO `hosting_requests` (`request_id`, `user_id`, `event_name`, `game_category`, `event_type`, `hosting_university_id`, `expected_participants`, `prize_pool`, `description`, `rules`, `start_date`, `end_date`, `status`, `admin_notes`, `requested_at`, `reviewed_at`, `reviewed_by`) VALUES
(1, 3, 'BPGL', 'PUBG Mobile', 'University Only', 1, 10, 5000.00, NULL, 'kon rules nai', '2026-01-14', '2026-01-16', 'Approved', NULL, '2026-01-12 20:03:18', '2026-01-12 20:10:45', 1),
(2, 2, 'sdafa', 'Free Fire', 'University Only', 1, 555, 555555.00, NULL, 'sadfasd', '2026-01-21', '2026-01-24', 'Approved', NULL, '2026-01-20 03:47:13', '2026-01-20 04:06:07', 1),
(3, 2, 'sdafatyutryjt', 'PUBG Mobile', 'University Only', 1, 50, 5000.00, NULL, 'gsdadgsgsdf', '2026-01-27', '2026-01-29', 'Approved', NULL, '2026-01-26 17:06:44', '2026-01-26 17:07:36', 1);

-- --------------------------------------------------------

--
-- Table structure for table `job_holder_profiles`
--

CREATE TABLE `job_holder_profiles` (
  `job_profile_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `designation` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `message_body` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `sent_at` datetime DEFAULT current_timestamp(),
  `read_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `notification_type` enum('System','Tournament','Subscription','Hosting','Message','Event') NOT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `title`, `message`, `notification_type`, `link_url`, `is_read`, `created_at`) VALUES
(1, 2, 'Welcome to ESportsHub!', 'Your account has been created successfully. Start exploring tournaments and teams!', 'System', NULL, 1, '2026-01-11 19:24:08'),
(2, 3, 'Welcome to ESportsHub!', 'Your account has been created successfully. Start exploring tournaments and teams!', 'System', NULL, 1, '2026-01-12 10:24:04'),
(3, 2, 'Tournament Joined!', 'Wait for admin approval.', 'Tournament', NULL, 1, '2026-01-12 10:42:05'),
(4, 2, 'Tournament Update', 'Tournament \"Bom Bom fire\" status updated to Ongoing', 'Tournament', NULL, 1, '2026-01-12 10:46:04'),
(5, 3, 'Subscription Activated!', 'Your 1 month subscription has been activated successfully.', 'Subscription', NULL, 1, '2026-01-12 20:01:06'),
(6, 3, 'Hosting Request Submitted', 'Your event hosting request has been submitted for admin review.', 'Hosting', NULL, 1, '2026-01-12 20:03:18'),
(7, 3, 'Hosting Request Approved', 'Your hosting request \'BPGL\' has been approved! Your tournament is now live.', 'Tournament', NULL, 1, '2026-01-12 20:10:45'),
(8, 3, 'Tournament Joined!', 'Wait for admin approval.', 'Tournament', NULL, 1, '2026-01-12 20:11:36'),
(9, 2, 'New Team!', 'You have been added to team \'pagol\' by its captain.', '', NULL, 1, '2026-01-12 21:47:11'),
(10, 2, 'Subscription Activated!', 'Your 1 month subscription has been activated successfully.', 'Subscription', NULL, 1, '2026-01-20 03:46:17'),
(11, 2, 'Hosting Request Submitted', 'Your event hosting request has been submitted for admin review.', 'Hosting', NULL, 1, '2026-01-20 03:47:13'),
(12, 2, 'Hosting Request Approved', 'Your hosting request \'sdafa\' has been approved! Your tournament is now live.', 'Tournament', NULL, 1, '2026-01-20 04:06:07'),
(13, 3, 'Tournament Joined!', 'Success! Payment: bkash', 'Tournament', NULL, 1, '2026-01-25 20:46:56'),
(14, 3, 'Tournament Joined!', 'Success! Payment: Free', 'Tournament', NULL, 1, '2026-01-25 21:06:17'),
(15, 3, 'Tournament Joined!', 'Success! Payment: bkash', 'Tournament', NULL, 1, '2026-01-25 21:08:57'),
(16, 2, 'Tournament Joined!', 'Success! Payment: nagad', 'Tournament', NULL, 1, '2026-01-26 17:03:38'),
(17, 2, 'Hosting Request Submitted', 'Your event hosting request has been submitted for admin review.', 'Hosting', NULL, 1, '2026-01-26 17:06:44'),
(18, 2, 'Hosting Request Approved', 'Your hosting request \'sdafatyutryjt\' has been approved! Your tournament is now live.', 'Tournament', NULL, 1, '2026-01-26 17:07:36');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('bKash','Nagad','Card','Cash on Delivery') NOT NULL,
  `payment_status` enum('Pending','Paid','Failed','Refunded') DEFAULT 'Pending',
  `order_status` enum('Processing','Shipped','Delivered','Cancelled') DEFAULT 'Processing',
  `shipping_address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `participants`
--

CREATE TABLE `participants` (
  `participant_id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `tournament_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `team_id` int(11) DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_status` enum('Pending','Paid','Free') DEFAULT 'Free',
  `payment_method` varchar(50) DEFAULT NULL,
  `status` enum('Registered','Active','Disqualified') DEFAULT 'Registered'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `participants`
--

INSERT INTO `participants` (`participant_id`, `event_id`, `tournament_id`, `user_id`, `team_id`, `registration_date`, `payment_status`, `payment_method`, `status`) VALUES
(1, NULL, 1, 2, NULL, '2026-01-12 10:42:05', 'Free', NULL, ''),
(2, NULL, 3, 3, NULL, '2026-01-12 20:11:36', 'Free', NULL, 'Registered'),
(3, NULL, 5, 3, NULL, '2026-01-25 20:46:56', 'Paid', 'bkash', 'Registered'),
(4, NULL, 4, 3, NULL, '2026-01-25 21:06:17', 'Free', 'Free', 'Registered'),
(5, NULL, 6, 3, NULL, '2026-01-25 21:08:57', 'Paid', 'bkash', 'Registered'),
(6, NULL, 6, 2, NULL, '2026-01-26 17:03:38', 'Paid', 'nagad', 'Registered');

--
-- Triggers `participants`
--
DELIMITER $$
CREATE TRIGGER `update_event_participants_after_delete` AFTER DELETE ON `participants` FOR EACH ROW BEGIN
    IF OLD.event_id IS NOT NULL THEN
        UPDATE events 
        SET current_participants = current_participants - 1 
        WHERE event_id = OLD.event_id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_event_participants_after_insert` AFTER INSERT ON `participants` FOR EACH ROW BEGIN
    IF NEW.event_id IS NOT NULL THEN
        UPDATE events 
        SET current_participants = current_participants + 1 
        WHERE event_id = NEW.event_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `subscription_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('bKash','Nagad','Card') NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_status` enum('Pending','Success','Failed') DEFAULT 'Pending',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `product_image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `description`, `category`, `price`, `stock_quantity`, `product_image`, `is_active`, `created_at`) VALUES
(9, 'keyboard', 'hdfgsfg', 'Digital Items', 500.00, 10, 'product_1769378115.jpg', 1, '2026-01-25 21:55:15'),
(10, 'Gaming chair', 'dfghd', 'Digital Items', 500.00, 10, 'product_1769447624.jpg', 1, '2026-01-26 17:13:44');

-- --------------------------------------------------------

--
-- Stand-in structure for view `stats_overview`
-- (See below for the actual view)
--
CREATE TABLE `stats_overview` (
`total_users` bigint(21)
,`total_students` bigint(21)
,`active_subscriptions` bigint(21)
,`total_tournaments` bigint(21)
,`running_events` bigint(21)
,`total_revenue` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `student_profiles`
--

CREATE TABLE `student_profiles` (
  `student_profile_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `university_id` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `student_id_number` varchar(50) DEFAULT NULL,
  `enrollment_year` year(4) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_profiles`
--

INSERT INTO `student_profiles` (`student_profile_id`, `user_id`, `university_id`, `department_id`, `student_id_number`, `enrollment_year`, `created_at`) VALUES
(1, 2, 1, 1, '54546456', NULL, '2026-01-11 19:24:08'),
(2, 3, 1, 1, '0112310001', NULL, '2026-01-12 10:24:04');

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `subscription_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan_duration` enum('1_month','3_months','6_months','1_year') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('bKash','Nagad','Card') NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`subscription_id`, `user_id`, `plan_duration`, `amount`, `payment_method`, `transaction_id`, `start_date`, `end_date`, `is_active`, `created_at`, `expires_at`) VALUES
(1, 3, '1_month', 200.00, 'bKash', '3425dew', '2026-01-12', '2026-02-12', 1, '2026-01-12 20:01:06', NULL),
(2, 2, '1_month', 200.00, 'bKash', '5465465', '2026-01-20', '2026-02-20', 1, '2026-01-20 03:46:17', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `team_id` int(11) NOT NULL,
  `team_name` varchar(255) NOT NULL,
  `team_tag` varchar(50) DEFAULT NULL,
  `captain_id` int(11) NOT NULL,
  `university_id` int(11) DEFAULT NULL,
  `game_category` varchar(100) DEFAULT NULL,
  `team_logo` varchar(255) DEFAULT 'default-team.png',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','deleted') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`team_id`, `team_name`, `team_tag`, `captain_id`, `university_id`, `game_category`, `team_logo`, `description`, `created_at`, `status`) VALUES
(1, 'pagol', NULL, 3, NULL, 'Free Fire', 'default-team.png', 'valo na team members ra', '2026-01-12 20:58:25', 'active'),
(2, 'safd', NULL, 2, NULL, 'Free Fire', 'default-team.png', 'sdafasd', '2026-01-20 03:43:45', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `member_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` varchar(100) DEFAULT 'Player',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `team_members`
--

INSERT INTO `team_members` (`member_id`, `team_id`, `user_id`, `role`, `joined_at`) VALUES
(1, 1, 3, 'Captain', '2026-01-12 20:58:25'),
(2, 1, 2, 'Member', '2026-01-12 21:47:11'),
(3, 2, 2, 'Captain', '2026-01-20 03:43:45');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `category` enum('Technical','Payment','Tournament','Account','Other') NOT NULL,
  `description` text NOT NULL,
  `priority` enum('Low','Medium','High') DEFAULT 'Medium',
  `status` enum('Open','In Progress','Resolved','Closed') DEFAULT 'Open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tournaments`
--

CREATE TABLE `tournaments` (
  `tournament_id` int(11) NOT NULL,
  `tournament_name` varchar(255) NOT NULL,
  `game_category` varchar(100) NOT NULL,
  `tournament_type` enum('Public','University','Invitational') NOT NULL,
  `organizer_id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `prize_pool` decimal(12,2) DEFAULT NULL,
  `max_participants` int(11) DEFAULT NULL,
  `max_slots` int(11) NOT NULL DEFAULT 0,
  `registration_fee` decimal(10,2) DEFAULT 0.00,
  `rules` text DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('Upcoming','Registration Open','Ongoing','Completed','Cancelled') DEFAULT 'Upcoming',
  `banner_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_suspended` tinyint(1) DEFAULT 0,
  `join_locked` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tournaments`
--

INSERT INTO `tournaments` (`tournament_id`, `tournament_name`, `game_category`, `tournament_type`, `organizer_id`, `event_id`, `prize_pool`, `max_participants`, `max_slots`, `registration_fee`, `rules`, `start_date`, `end_date`, `status`, `banner_image`, `created_at`, `is_suspended`, `join_locked`) VALUES
(1, 'Bom Bom fire', 'free fire', '', 1, NULL, 5000.00, 0, 0, 0.00, 'bitlami kora jabe na', '2026-01-16', '2026-01-20', 'Ongoing', NULL, '2026-01-11 22:14:21', 0, 0),
(3, 'BPGL', 'PUBG Mobile', '', 3, NULL, 5000.00, 10, 0, 0.00, 'kon rules nai', '2026-01-14', '2026-01-16', 'Upcoming', NULL, '2026-01-12 20:10:45', 0, 0),
(4, 'sdafa', 'Free Fire', '', 2, NULL, 555555.00, 555, 0, 0.00, 'sadfasd', '2026-01-21', '2026-01-24', 'Upcoming', NULL, '2026-01-20 04:06:07', 0, 0),
(5, 'bd pubg', 'pubg', 'University', 1, NULL, 5000.00, 16, 0, 500.00, 'no rules', '2026-01-29', '2026-01-31', 'Upcoming', NULL, '2026-01-25 20:09:18', 0, 0),
(6, 'uiu ff', 'free fire', 'Public', 1, NULL, 5050.00, 36, 9, 400.00, 'no rules', '2026-01-30', '2026-01-31', 'Upcoming', NULL, '2026-01-25 21:08:29', 0, 0),
(7, 'sdafatyutryjt', 'PUBG Mobile', '', 2, NULL, 5000.00, 50, 0, 0.00, 'gsdadgsgsdf', '2026-01-27', '2026-01-29', 'Upcoming', NULL, '2026-01-26 17:07:36', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `universities`
--

CREATE TABLE `universities` (
  `university_id` int(11) NOT NULL,
  `university_name` varchar(255) NOT NULL,
  `short_name` varchar(50) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `universities`
--

INSERT INTO `universities` (`university_id`, `university_name`, `short_name`, `location`, `created_at`) VALUES
(1, 'United International University', 'UIU', 'Dhaka', '2026-01-11 19:22:26'),
(2, 'North South University', 'NSU', 'Dhaka', '2026-01-11 19:22:26'),
(3, 'BRAC University', 'BRACU', 'Dhaka', '2026-01-11 19:22:26'),
(4, 'East West University', 'EWU', 'Dhaka', '2026-01-11 19:22:26'),
(5, 'American International University-Bangladesh', 'AIUB', 'Dhaka', '2026-01-11 19:22:26'),
(6, 'Independent University Bangladesh', 'IUB', 'Dhaka', '2026-01-11 19:22:26'),
(7, 'Ahsanullah University of Science and Technology', 'AUST', 'Dhaka', '2026-01-11 19:22:26'),
(8, 'Daffodil International University', 'DIU', 'Dhaka', '2026-01-11 19:22:26'),
(9, 'Bangladesh University of Engineering and Technology', 'BUET', 'Dhaka', '2026-01-11 19:22:26'),
(10, 'Dhaka University', 'DU', 'Dhaka', '2026-01-11 19:22:26');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `date_of_birth` date NOT NULL,
  `profession` enum('Student','Job Holder','Freelancer','Entrepreneur','Content Creator','None') NOT NULL,
  `role` enum('user','admin','host') DEFAULT 'user',
  `is_active` tinyint(1) DEFAULT 1,
  `profile_image` varchar(255) DEFAULT 'default-avatar.png',
  `terms_agreed` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `username`, `email`, `phone`, `password_hash`, `gender`, `date_of_birth`, `profession`, `role`, `is_active`, `profile_image`, `terms_agreed`, `created_at`, `last_login`) VALUES
(1, 'Alvi Admin', '@admin', 'adminalvi@gmail.com', '+8801700000000', '5f249e6f5367a84cab13c8bb4cb7440f', 'Male', '1990-01-01', 'None', 'admin', 1, 'default-avatar.png', 1, '2026-01-11 19:22:26', '2026-01-26 17:12:47'),
(2, 'AS Abdullah Alvi', '@alviboss', 'alviariyan1101@gmail.com', '01318171642', 'e807f1fcf82d132f9bb018ca6738a19f', 'Male', '2000-01-11', 'Student', 'user', 1, 'default-avatar.png', 1, '2026-01-11 19:24:08', '2026-01-26 17:07:49'),
(3, 'jim', '@jim', 'jim@gmail.com', '01575627761', 'e807f1fcf82d132f9bb018ca6738a19f', 'Male', '2000-11-11', 'Student', 'user', 1, 'default-avatar.png', 1, '2026-01-12 10:24:04', '2026-01-26 17:14:28');

-- --------------------------------------------------------

--
-- Table structure for table `user_notifications`
--

CREATE TABLE `user_notifications` (
  `id` int(11) NOT NULL,
  `admin_notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure for view `stats_overview`
--
DROP TABLE IF EXISTS `stats_overview`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `stats_overview`  AS SELECT (select count(0) from `users` where `users`.`role` = 'user') AS `total_users`, (select count(0) from `users` where `users`.`profession` = 'Student') AS `total_students`, (select count(0) from `subscriptions` where `subscriptions`.`is_active` = 1) AS `active_subscriptions`, (select count(0) from `tournaments`) AS `total_tournaments`, (select count(0) from `events` where `events`.`status` = 'Running') AS `running_events`, (select coalesce(sum(`subscriptions`.`amount`),0) from `subscriptions`) AS `total_revenue` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD PRIMARY KEY (`admin_notification_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `type` (`type`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  ADD PRIMARY KEY (`conversation_id`),
  ADD UNIQUE KEY `uq_user_admin` (`user_id`,`admin_id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`message_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`),
  ADD KEY `idx_department_name` (`department_name`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `hosting_university_id` (`hosting_university_id`),
  ADD KEY `idx_host_id` (`host_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_event_type` (`event_type`),
  ADD KEY `idx_game_category` (`game_category`);

--
-- Indexes for table `hosting_requests`
--
ALTER TABLE `hosting_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `reviewed_by` (`reviewed_by`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_hosting_university_id` (`hosting_university_id`);

--
-- Indexes for table `job_holder_profiles`
--
ALTER TABLE `job_holder_profiles`
  ADD PRIMARY KEY (`job_profile_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_notification_type` (`notification_type`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_order_status` (`order_status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `participants`
--
ALTER TABLE `participants`
  ADD PRIMARY KEY (`participant_id`),
  ADD KEY `idx_event_id` (`event_id`),
  ADD KEY `idx_tournament_id` (`tournament_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_team_id` (`team_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `subscription_id` (`subscription_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_transaction_id` (`transaction_id`),
  ADD KEY `idx_payment_status` (`payment_status`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD PRIMARY KEY (`student_profile_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_university_id` (`university_id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`subscription_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_end_date` (`end_date`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`team_id`),
  ADD KEY `idx_captain_id` (`captain_id`),
  ADD KEY `idx_university_id` (`university_id`),
  ADD KEY `idx_game_category` (`game_category`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`member_id`),
  ADD UNIQUE KEY `unique_team_user` (`team_id`,`user_id`),
  ADD KEY `idx_team_id` (`team_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`ticket_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_priority` (`priority`);

--
-- Indexes for table `tournaments`
--
ALTER TABLE `tournaments`
  ADD PRIMARY KEY (`tournament_id`),
  ADD KEY `idx_organizer_id` (`organizer_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_game_category` (`game_category`),
  ADD KEY `idx_tournament_type` (`tournament_type`);

--
-- Indexes for table `universities`
--
ALTER TABLE `universities`
  ADD PRIMARY KEY (`university_id`),
  ADD UNIQUE KEY `university_name` (`university_name`),
  ADD KEY `idx_university_name` (`university_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_profession` (`profession`),
  ADD KEY `idx_role` (`role`);

--
-- Indexes for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `is_read` (`is_read`),
  ADD KEY `admin_notification_id` (`admin_notification_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `admin_notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  MODIFY `conversation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `hosting_requests`
--
ALTER TABLE `hosting_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `job_holder_profiles`
--
ALTER TABLE `job_holder_profiles`
  MODIFY `job_profile_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `participants`
--
ALTER TABLE `participants`
  MODIFY `participant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `student_profiles`
--
ALTER TABLE `student_profiles`
  MODIFY `student_profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `subscription_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `team_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `ticket_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tournaments`
--
ALTER TABLE `tournaments`
  MODIFY `tournament_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `universities`
--
ALTER TABLE `universities`
  MODIFY `university_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_notifications`
--
ALTER TABLE `user_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD CONSTRAINT `admin_notifications_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `hosting_requests` (`request_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`host_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `events_ibfk_3` FOREIGN KEY (`hosting_university_id`) REFERENCES `universities` (`university_id`) ON DELETE SET NULL;

--
-- Constraints for table `hosting_requests`
--
ALTER TABLE `hosting_requests`
  ADD CONSTRAINT `hosting_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hosting_requests_ibfk_2` FOREIGN KEY (`hosting_university_id`) REFERENCES `universities` (`university_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `hosting_requests_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `job_holder_profiles`
--
ALTER TABLE `job_holder_profiles`
  ADD CONSTRAINT `job_holder_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `participants`
--
ALTER TABLE `participants`
  ADD CONSTRAINT `participants_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `participants_ibfk_2` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`tournament_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `participants_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `participants_ibfk_4` FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`subscription_id`) ON DELETE SET NULL;

--
-- Constraints for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD CONSTRAINT `student_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_profiles_ibfk_2` FOREIGN KEY (`university_id`) REFERENCES `universities` (`university_id`),
  ADD CONSTRAINT `student_profiles_ibfk_3` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `teams_ibfk_1` FOREIGN KEY (`captain_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teams_ibfk_2` FOREIGN KEY (`university_id`) REFERENCES `universities` (`university_id`) ON DELETE SET NULL;

--
-- Constraints for table `team_members`
--
ALTER TABLE `team_members`
  ADD CONSTRAINT `team_members_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `tournaments`
--
ALTER TABLE `tournaments`
  ADD CONSTRAINT `tournaments_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD CONSTRAINT `user_notifications_ibfk_1` FOREIGN KEY (`admin_notification_id`) REFERENCES `admin_notifications` (`admin_notification_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_notifications_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
