-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 30, 2025 at 07:26 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `monitoring`
--

-- --------------------------------------------------------

--
-- Table structure for table `borrowed_items`
--

CREATE TABLE `borrowed_items` (
  `borrowed_id` int(11) NOT NULL,
  `reservation_code` varchar(50) NOT NULL,
  `requested_by` varchar(100) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `product_name` varchar(100) NOT NULL,
  `date_borrowed` datetime NOT NULL DEFAULT current_timestamp(),
  `reservation_date` datetime DEFAULT NULL,
  `status` enum('borrowed','returned','overdue') DEFAULT 'borrowed',
  `product_qty` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) DEFAULT 0.00,
  `subtotal` decimal(10,2) GENERATED ALWAYS AS (`product_qty` * `unit_price`) STORED,
  `checked_by` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrowed_items`
--

INSERT INTO `borrowed_items` (`borrowed_id`, `reservation_code`, `requested_by`, `product_id`, `image`, `product_name`, `date_borrowed`, `reservation_date`, `status`, `product_qty`, `unit_price`, `checked_by`, `created_at`) VALUES
(118, 'RSV-6861FD66A4618', 'Carl Caraos', 1, 'uploads/685e22ea7b50e_White And Blue Modern Gadget product Promotion Instagram Post.png', 'Arduino Uno R3', '2025-06-30 10:59:07', '2025-06-30 10:58:46', 'borrowed', 37, 550.00, 'Ragnhild', '2025-06-30 10:59:11'),
(119, 'RSV-6861FDAC44329', 'Carl Caraos', 2, 'uploads/685e23106e820_6.png', 'LM2596 Buck Converter', '2025-06-30 11:00:10', '2025-07-12 10:59:56', 'borrowed', 45, 45.00, 'Ragnhild', '2025-06-30 11:00:14'),
(120, 'RSV-68621E5011676', 'Carl Caraos', 7, 'uploads/685e23a7d9f3c_Remove background project-2 (2).png', '16x2 LCD with I2C', '2025-06-30 13:21:06', '2025-07-08 13:19:12', 'borrowed', 50, 120.00, 'Ragnhild', '2025-06-30 13:21:12');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` datetime DEFAULT current_timestamp(),
  `direct_checkout` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `session_id`, `product_id`, `quantity`, `added_at`, `direct_checkout`) VALUES
(463, '7hd54ehkdebkpgn9saag2babjm', 7, 1, '2025-06-28 15:06:41', 0);

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `category_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`, `category_description`) VALUES
(1, 'Microcontrollers', 'Boards and chips used for controlling electronics projects'),
(2, 'Sensors', 'Modules and components used for detecting environmental changes'),
(3, 'Power & Display', 'Power modules, converters, and display components like LCDs');

-- --------------------------------------------------------

--
-- Table structure for table `damage_reports`
--

CREATE TABLE `damage_reports` (
  `damage_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `requested_by` varchar(255) NOT NULL,
  `number` varchar(50) DEFAULT NULL,
  `section` varchar(255) DEFAULT NULL,
  `quantity_damaged` int(11) DEFAULT 1,
  `damage_description` text DEFAULT NULL,
  `action_taken` varchar(255) DEFAULT NULL,
  `status` enum('Pending','In Progress','Resolved') DEFAULT 'Pending',
  `remarks` text DEFAULT NULL,
  `date_reported` datetime DEFAULT current_timestamp(),
  `date_resolved` datetime DEFAULT NULL,
  `checked_by` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `damage_reports`
--

INSERT INTO `damage_reports` (`damage_id`, `product_name`, `requested_by`, `number`, `section`, `quantity_damaged`, `damage_description`, `action_taken`, `status`, `remarks`, `date_reported`, `date_resolved`, `checked_by`) VALUES
(1, '5V Relay Module, ADXL345 Accelerometer, Arduino Uno R3', 'Carl Caraos', '09992392323', '2D', 3, 'sample\r\n', 'dance', 'In Progress', 'no remarks', '2025-06-21 15:51:36', '2025-06-21 15:51:00', 'sample'),
(2, 'wifi 5g globe at home', 'Carl Caraos', '0993123923', '2D', 1, 'sample', 'dance', 'In Progress', 'sdasd', '2025-06-23 10:52:18', '2025-06-23 10:52:00', 'Ragnhild');

-- --------------------------------------------------------

--
-- Table structure for table `email_notifications`
--

CREATE TABLE `email_notifications` (
  `email_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_products`
--

CREATE TABLE `inventory_products` (
  `product_id` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_description` text DEFAULT NULL,
  `quantity_in_stock` int(11) NOT NULL DEFAULT 0,
  `reorder_level` int(11) NOT NULL DEFAULT 0,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `date_added` datetime DEFAULT current_timestamp(),
  `last_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('Available','Not Available') DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_products`
--

INSERT INTO `inventory_products` (`product_id`, `image`, `product_name`, `product_description`, `quantity_in_stock`, `reorder_level`, `unit_price`, `category_id`, `date_added`, `last_updated`, `status`) VALUES
(1, 'uploads/685e22ea7b50e_White And Blue Modern Gadget product Promotion Instagram Post.png', 'Arduino Uno R3', 'Microcontroller board based on ATmega328P', 3, 3, 550.00, 1, '2025-06-27 00:00:00', '2025-06-30 10:59:11', 'Available'),
(2, 'uploads/685e23106e820_6.png', 'LM2596 Buck Converter', 'DC-DC buck converter for voltage regulation', 5, 5, 45.00, 1, '2025-06-27 00:00:00', '2025-06-30 11:00:14', 'Available'),
(3, 'uploads/685e231e6a798_5.png', 'HC-SR04 Ultrasonic Sensor', 'Distance measurement sensor module', 50, 5, 35.00, 2, '2025-06-27 00:00:00', '2025-06-30 10:39:29', 'Available'),
(4, 'uploads/685e232a3e5ea_1.png', 'SG90 Servo Motor', '9g micro servo motor for RC and robotics', 50, 5, 60.00, 3, '2025-06-27 00:00:00', '2025-06-30 10:39:29', 'Available'),
(5, 'uploads/685e23365e7e4_4.png', 'IR Sensor Module', 'Infrared obstacle detection sensor', 60, 5, 25.00, 2, '2025-06-27 00:00:00', '2025-06-30 10:33:52', 'Available'),
(6, 'uploads/685e2347ae6af_3.png', 'L298N Motor Driver', 'Dual H-Bridge DC motor driver module', 50, 5, 85.00, 1, '2025-06-27 00:00:00', '2025-06-30 10:39:29', 'Available'),
(7, 'uploads/685e23a7d9f3c_Remove background project-2 (2).png', '16x2 LCD with I2C', 'LCD display module with I2C interface', 50, 10, 120.00, 1, '2025-06-27 00:00:00', '2025-06-30 13:21:12', 'Available'),
(8, 'uploads/685e2f9087e2f_Remove background project.jpeg', '18650 Battery Pack', 'Rechargeable battery pack with holder', 50, 0, 150.00, 1, '2025-06-27 00:00:00', '2025-06-30 09:08:40', 'Available'),
(9, 'uploads/685e2fbb99033_Remove background project (1).png', 'Robot Chassis Kit', '2-wheel robot chassis frame with motors', 40, 0, 300.00, 1, '2025-06-27 00:00:00', '2025-06-30 09:36:37', 'Available'),
(10, 'uploads/685e2fd02c22a_Remove background project-5.png', 'Jumper Wires Pack', 'Male-to-male and male-to-female jumper wires', 50, 3, 20.00, 1, '2025-06-27 00:00:00', '2025-06-27 15:15:33', 'Available');

--
-- Triggers `inventory_products`
--
DELIMITER $$
CREATE TRIGGER `trg_low_stock_alert` AFTER UPDATE ON `inventory_products` FOR EACH ROW BEGIN
    IF NEW.quantity_in_stock < NEW.reorder_level THEN
        INSERT INTO low_stock_log (product_id, product_name, quantity_in_stock)
        VALUES (NEW.product_id, NEW.product_name, NEW.quantity_in_stock);
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `low_stock_log`
--

CREATE TABLE `low_stock_log` (
  `log_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `quantity_in_stock` int(11) DEFAULT NULL,
  `alert_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `low_stock_log`
--

INSERT INTO `low_stock_log` (`log_id`, `product_id`, `product_name`, `quantity_in_stock`, `alert_date`) VALUES
(0, 32, 'HC-SR04 Ultrasonic Sensor', 10, '2025-06-23 14:02:18'),
(1, 28, 'daded', 2, '2025-06-01 11:51:01'),
(2, 28, 'daded', -10, '2025-06-01 11:51:01'),
(3, 28, 'daded', 2, '2025-06-01 12:06:54'),
(4, 29, 'asd', 122, '2025-06-04 09:45:51'),
(5, 29, 'asd', 22, '2025-06-04 09:45:51'),
(6, 29, 'asd', -78, '2025-06-04 09:45:51'),
(7, 29, 'asd', 22, '2025-06-04 10:08:32'),
(8, 29, 'asd', 122, '2025-06-04 10:08:32'),
(9, 30, 'sam', 1, '2025-06-04 10:24:32'),
(10, 30, 'sam', -232, '2025-06-04 10:24:32'),
(11, 30, 'sam', -465, '2025-06-04 10:24:32'),
(12, 30, 'sam', -698, '2025-06-04 10:24:32'),
(13, 2, 'wifi 5g globe at home ', 4, '2025-06-04 10:37:55'),
(14, 2, 'wifi 5g globe at home ', -18, '2025-06-04 10:37:55'),
(15, 2, 'wifi 5g globe at home ', -40, '2025-06-04 10:37:55'),
(16, 2, 'wifi 5g globe at home ', -36, '2025-06-04 10:41:37'),
(17, 2, 'wifi 5g globe at home ', -32, '2025-06-04 10:41:37'),
(18, 2, 'wifi 5g globe at home ', -28, '2025-06-04 10:41:42'),
(19, 2, 'wifi 5g globe at home ', -24, '2025-06-04 10:41:42'),
(20, 2, 'wifi 5g globe at home ', -20, '2025-06-04 10:41:52'),
(21, 2, 'wifi 5g globe at home ', -16, '2025-06-04 10:41:52'),
(22, 2, 'wifi 5g globe at home ', 6, '2025-06-04 10:41:57'),
(23, 2, 'wifi 5g globe at home ', 18, '2025-06-04 10:54:24'),
(24, 2, 'wifi 5g globe at home ', 20, '2025-06-04 15:03:02'),
(25, 2, 'wifi 5g globe at home ', 19, '2025-06-04 15:05:02'),
(26, 2, 'wifi 5g globe at home ', 18, '2025-06-04 15:07:47'),
(27, 2, 'wifi 5g globe at home ', 17, '2025-06-04 15:09:23'),
(28, 2, 'wifi 5g globe at home ', 16, '2025-06-04 15:11:21'),
(29, 2, 'wifi 5g globe at home ', 15, '2025-06-04 15:11:24'),
(30, 2, 'wifi 5g globe at home ', 14, '2025-06-04 15:16:12'),
(31, 2, 'wifi 5g globe at home ', 17, '2025-06-04 15:18:29'),
(32, 2, 'wifi 5g globe at home ', 16, '2025-06-04 15:21:04'),
(33, 2, 'wifi 5g globe at home ', 17, '2025-06-04 15:21:14'),
(34, 2, 'wifi 5g globe at home ', 15, '2025-06-04 15:22:32'),
(35, 2, 'wifi 5g globe at home ', 13, '2025-06-04 15:25:45'),
(36, 2, 'wifi 5g globe at home ', 12, '2025-06-04 15:26:01'),
(37, 2, 'wifi 5g globe at home ', 13, '2025-06-04 15:32:26'),
(38, 2, 'wifi 5g globe at home ', 15, '2025-06-04 15:32:32'),
(39, 2, 'wifi 5g globe at home ', 17, '2025-06-04 15:32:36'),
(40, 2, 'wifi 5g globe at home ', 16, '2025-06-04 15:32:55'),
(41, 2, 'wifi 5g globe at home ', 15, '2025-06-04 15:33:15'),
(42, 2, 'wifi 5g globe at home ', 14, '2025-06-04 15:35:26'),
(43, 2, 'wifi 5g globe at home ', 16, '2025-06-04 15:35:42'),
(44, 2, 'wifi 5g globe at home ', 17, '2025-06-04 15:35:48'),
(45, 2, 'wifi 5g globe at home ', 3, '2025-06-04 15:38:05'),
(46, 2, 'wifi 5g globe at home ', 20, '2025-06-04 15:38:30'),
(47, 2, 'wifi 5g globe at home ', 15, '2025-06-04 15:39:31'),
(48, 2, 'wifi 5g globe at home ', 19, '2025-06-04 15:49:34'),
(49, 2, 'wifi 5g globe at home ', 19, '2025-06-04 15:50:18'),
(50, 2, 'wifi 5g globe at home ', 20, '2025-06-04 16:16:55'),
(51, 2, 'wifi 5g globe at home ', 19, '2025-06-04 17:16:16'),
(52, 2, 'wifi 5g globe at home ', 20, '2025-06-04 17:16:33'),
(53, 2, 'wifi 5g globe at home ', 19, '2025-06-04 17:21:55'),
(54, 2, 'wifi 5g globe at home ', 19, '2025-06-05 13:51:12'),
(55, 2, 'wifi 5g globe at home ', 15, '2025-06-05 14:40:15'),
(56, 2, 'wifi 5g globe at home ', 16, '2025-06-05 14:47:02'),
(57, 2, 'wifi 5g globe at home ', 20, '2025-06-05 14:48:03'),
(58, 2, 'wifi 5g globe at home ', 19, '2025-06-11 09:40:27'),
(59, 2, 'wifi 5g globe at home ', 18, '2025-06-11 10:02:33'),
(60, 2, 'wifi 5g globe at home ', 19, '2025-06-11 10:17:53'),
(61, 2, 'wifi 5g globe at home ', 18, '2025-06-11 10:18:22'),
(62, 2, 'wifi 5g globe at home ', 20, '2025-06-11 10:45:13');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('unread','read') DEFAULT 'unread',
  `reservation_code` varchar(32) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`, `status`, `reservation_code`, `product_id`) VALUES
(654, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-68621FAC6C2C6\'>#RSV-68621FAC6C2C6</a> by Carl Caraos for 2025-07-14 12:00:00.', 0, '2025-06-30 05:25:00', 'unread', 'RSV-68621FAC6C2C6', 4),
(655, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-68621FAC6C2C6\'>#RSV-68621FAC6C2C6</a> by Carl Caraos for 2025-07-14 12:00:00.', 0, '2025-06-30 05:25:00', 'unread', 'RSV-68621FAC6C2C6', 3),
(656, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-68621FAC6C2C6\'>#RSV-68621FAC6C2C6</a> by Carl Caraos for 2025-07-14 12:00:00.', 0, '2025-06-30 05:25:00', 'unread', 'RSV-68621FAC6C2C6', 2);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `code`, `expires_at`, `used`, `created_at`) VALUES
(2, 0, '169241', '2025-06-26 04:25:20', 0, '2025-06-26 10:15:20'),
(3, 0, '959144', '2025-06-26 04:28:47', 0, '2025-06-26 10:18:47'),
(4, 0, '120245', '2025-06-26 04:30:52', 0, '2025-06-26 10:20:52'),
(5, 0, '437761', '2025-06-26 04:32:40', 0, '2025-06-26 10:22:40'),
(6, 0, '421212', '2025-06-26 04:40:16', 0, '2025-06-26 10:30:16'),
(7, 0, '787387', '2025-06-26 04:40:41', 0, '2025-06-26 10:30:41'),
(8, 0, '159554', '2025-06-26 04:42:32', 1, '2025-06-26 10:32:32'),
(9, 0, '277670', '2025-06-26 04:53:56', 0, '2025-06-26 10:43:56'),
(10, 0, '349389', '2025-06-26 05:10:12', 0, '2025-06-26 11:00:12'),
(11, 0, '151751', '2025-06-26 05:11:19', 0, '2025-06-26 11:01:19'),
(12, 0, '158678', '2025-06-26 05:16:37', 0, '2025-06-26 11:06:37'),
(13, 0, '886881', '2025-06-26 05:20:16', 0, '2025-06-26 11:10:16'),
(14, 0, '144400', '2025-06-26 05:22:31', 0, '2025-06-26 11:12:31'),
(15, 0, '377112', '2025-06-26 05:25:15', 0, '2025-06-26 11:15:15'),
(16, 0, '444101', '2025-06-26 05:28:20', 0, '2025-06-26 11:18:20'),
(17, 0, '428463', '2025-06-26 05:29:36', 1, '2025-06-26 11:19:36'),
(18, 0, '120645', '2025-06-26 05:30:25', 0, '2025-06-26 11:20:25'),
(19, 0, '948361', '2025-06-26 05:35:12', 0, '2025-06-26 11:25:12'),
(20, 0, '721078', '2025-06-26 05:41:11', 0, '2025-06-26 11:31:11'),
(21, 0, '736835', '2025-06-26 05:43:16', 0, '2025-06-26 11:33:16'),
(22, 0, '319016', '2025-06-26 05:46:13', 0, '2025-06-26 11:36:13'),
(23, 0, '653239', '2025-06-26 05:49:36', 0, '2025-06-26 11:39:36'),
(24, 0, '125512', '2025-06-26 07:19:23', 0, '2025-06-26 13:09:23'),
(25, 0, '142361', '2025-06-26 07:35:11', 0, '2025-06-26 13:25:11'),
(26, 0, '261266', '2025-06-26 08:03:42', 1, '2025-06-26 13:53:42'),
(27, 0, '726155', '2025-06-26 08:08:51', 0, '2025-06-26 13:58:51'),
(28, 0, '942568', '2025-06-26 08:15:01', 1, '2025-06-26 14:05:01'),
(29, 0, '520598', '2025-06-26 08:41:02', 0, '2025-06-26 14:31:02'),
(30, 0, '671985', '2025-06-27 02:24:57', 0, '2025-06-27 08:14:58');

-- --------------------------------------------------------

--
-- Table structure for table `product_files`
--

CREATE TABLE `product_files` (
  `file_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` int(11) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_files`
--

INSERT INTO `product_files` (`file_id`, `product_id`, `file_name`, `file_path`, `file_type`, `file_size`, `upload_date`) VALUES
(9, 2, 'IMG_20240721_174819_241@-739806918.jpg', '2025/05/2/68244b674314b_IMG_20240721_174819_241-739806918.jpg', 'image/jpeg', 1891135, '2025-05-14 07:51:03');

-- --------------------------------------------------------

--
-- Table structure for table `reservation`
--

CREATE TABLE `reservation` (
  `req_id` int(11) NOT NULL,
  `reservation_code` varchar(32) DEFAULT NULL,
  `requested_by` varchar(255) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `reservation_date` datetime NOT NULL DEFAULT current_timestamp(),
  `reservation_timeslot` varchar(20) DEFAULT NULL,
  `status` enum('pending','approved','borrowed','returned') DEFAULT 'pending',
  `product_qty` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) GENERATED ALWAYS AS (`product_qty` * `unit_price`) STORED,
  `checked_by` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservation`
--

INSERT INTO `reservation` (`req_id`, `reservation_code`, `requested_by`, `product_id`, `image`, `product_name`, `reservation_date`, `reservation_timeslot`, `status`, `product_qty`, `unit_price`, `checked_by`, `created_at`) VALUES
(317, 'RSV-6861FF41A3F54', 'Carl Caraos', 7, 'uploads/685e23a7d9f3c_Remove background project-2 (2).png', '16x2 LCD with I2C', '2025-07-09 11:06:41', '08:00 AM - 12:00 PM', 'pending', 1, 120.00, 'Ragnhild', '2025-06-30 11:06:41'),
(318, 'RSV-6861FF41A3F54', 'Carl Caraos', 4, 'uploads/685e232a3e5ea_1.png', 'SG90 Servo Motor', '2025-07-09 11:06:41', '08:00 AM - 12:00 PM', 'pending', 2, 60.00, 'Ragnhild', '2025-06-30 11:06:41'),
(319, 'RSV-6861FF41A3F54', 'Carl Caraos', 3, 'uploads/685e231e6a798_5.png', 'HC-SR04 Ultrasonic Sensor', '2025-07-09 11:06:41', '08:00 AM - 12:00 PM', 'pending', 1, 35.00, 'Ragnhild', '2025-06-30 11:06:41'),
(320, 'RSV-6862036FAE2BF', 'Carl Caraos', 5, 'uploads/685e23365e7e4_4.png', 'IR Sensor Module', '2025-07-16 11:24:31', '08:00 AM - 12:00 PM', 'pending', 1, 25.00, 'Ragnhild', '2025-06-30 11:24:31'),
(322, 'RSV-68621FAC6C2C6', 'Carl Caraos', 4, 'uploads/685e232a3e5ea_1.png', 'SG90 Servo Motor', '2025-07-14 13:25:00', '12:00 PM - 6:00 PM', 'pending', 1, 60.00, 'Ragnhild', '2025-06-30 13:25:00'),
(323, 'RSV-68621FAC6C2C6', 'Carl Caraos', 3, 'uploads/685e231e6a798_5.png', 'HC-SR04 Ultrasonic Sensor', '2025-07-14 13:25:00', '12:00 PM - 6:00 PM', 'pending', 1, 35.00, 'Ragnhild', '2025-06-30 13:25:00'),
(324, 'RSV-68621FAC6C2C6', 'Carl Caraos', 2, 'uploads/685e23106e820_6.png', 'LM2596 Buck Converter', '2025-07-14 13:25:00', '12:00 PM - 6:00 PM', 'pending', 1, 45.00, 'Ragnhild', '2025-06-30 13:25:00');

-- --------------------------------------------------------

--
-- Table structure for table `returns`
--

CREATE TABLE `returns` (
  `return_id` int(11) NOT NULL,
  `reservation_code` varchar(100) NOT NULL,
  `requested_by` varchar(100) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `reservation_date_borrowed` varchar(100) NOT NULL,
  `reservation_date` datetime DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'returned',
  `borrowed_quantity` int(11) NOT NULL,
  `return_quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `returned_by` varchar(255) DEFAULT NULL,
  `checked_by` varchar(100) NOT NULL,
  `date_time_returned` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `returns`
--

INSERT INTO `returns` (`return_id`, `reservation_code`, `requested_by`, `product_id`, `image`, `product_name`, `reservation_date_borrowed`, `reservation_date`, `status`, `borrowed_quantity`, `return_quantity`, `unit_price`, `subtotal`, `returned_by`, `checked_by`, `date_time_returned`, `created_at`) VALUES
(60, 'RSV-6861E3AD6B706', 'Carl Caraos', 3, 'uploads/685e231e6a798_5.png', 'HC-SR04 Ultrasonic Sensor', '2025-06-30 10:38:31', '2025-07-04 09:09:01', 'Returned', 7, 7, 35.00, 0.00, 'Carl Caraos', 'Ragnhild', '2025-06-30 10:39:29', '2025-06-30 02:39:29'),
(61, 'RSV-6861E45FD8BAC', 'Carl Caraos', 3, 'uploads/685e231e6a798_5.png', 'HC-SR04 Ultrasonic Sensor', '2025-06-30 10:38:31', '2025-07-15 09:11:59', 'Returned', 15, 15, 35.00, 0.00, 'Carl Caraos', 'Ragnhild', '2025-06-30 10:39:29', '2025-06-30 02:39:29'),
(62, 'RSV-6861EA7D08157', 'Carl Caraos', 6, 'uploads/685e2347ae6af_3.png', 'L298N Motor Driver', '2025-06-30 10:38:31', '2025-07-12 09:38:05', 'Returned', 14, 14, 85.00, 0.00, 'Carl Caraos', 'Ragnhild', '2025-06-30 10:39:29', '2025-06-30 02:39:29'),
(63, 'RSV-6861EA7D08157', 'Carl Caraos', 4, 'uploads/685e232a3e5ea_1.png', 'SG90 Servo Motor', '2025-06-30 10:38:31', '2025-07-12 09:38:05', 'Returned', 12, 12, 60.00, 0.00, 'Carl Caraos', 'Ragnhild', '2025-06-30 10:39:29', '2025-06-30 02:39:29');

--
-- Triggers `returns`
--
DELIMITER $$
CREATE TRIGGER `set_reservation_time_before_insert` BEFORE INSERT ON `returns` FOR EACH ROW BEGIN
  IF NEW.reservation_date IS NULL THEN
    SET NEW.reservation_date = CURRENT_TIME();
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Viewer','Updater') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(0, 'Admin', 'projectimsqdmin576@gmail.com', '$2y$10$uP3axXeRM.VrGy52Qy4L6.6//DSaqcc/TasoeaGGVvJvuQf1umPi6', 'Admin', '2025-06-26 02:13:45');

-- --------------------------------------------------------

--
-- Table structure for table `view_request_items_with_cost`
--

CREATE TABLE `view_request_items_with_cost` (
  `request_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit_cost` decimal(10,2) DEFAULT NULL,
  `total_cost` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `borrowed_items`
--
ALTER TABLE `borrowed_items`
  ADD PRIMARY KEY (`borrowed_id`),
  ADD KEY `fk_product_id` (`product_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `damage_reports`
--
ALTER TABLE `damage_reports`
  ADD PRIMARY KEY (`damage_id`);

--
-- Indexes for table `email_notifications`
--
ALTER TABLE `email_notifications`
  ADD PRIMARY KEY (`email_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `inventory_products`
--
ALTER TABLE `inventory_products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `low_stock_log`
--
ALTER TABLE `low_stock_log`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `reservation_code` (`reservation_code`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `product_files`
--
ALTER TABLE `product_files`
  ADD PRIMARY KEY (`file_id`),
  ADD KEY `idx_product_files_product_id` (`product_id`);

--
-- Indexes for table `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`req_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`return_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `borrowed_items`
--
ALTER TABLE `borrowed_items`
  MODIFY `borrowed_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=588;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `damage_reports`
--
ALTER TABLE `damage_reports`
  MODIFY `damage_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `email_notifications`
--
ALTER TABLE `email_notifications`
  MODIFY `email_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=657;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `reservation`
--
ALTER TABLE `reservation`
  MODIFY `req_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=325;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `borrowed_items`
--
ALTER TABLE `borrowed_items`
  ADD CONSTRAINT `fk_product_id` FOREIGN KEY (`product_id`) REFERENCES `inventory_products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `inventory_products` (`product_id`);

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `reservation`
--
ALTER TABLE `reservation`
  ADD CONSTRAINT `reservation_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `inventory_products` (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
