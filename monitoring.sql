-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 30, 2025 at 05:26 AM
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
(119, 'RSV-6861FDAC44329', 'Carl Caraos', 2, 'uploads/685e23106e820_6.png', 'LM2596 Buck Converter', '2025-06-30 11:00:10', '2025-07-12 10:59:56', 'borrowed', 45, 45.00, 'Ragnhild', '2025-06-30 11:00:14');

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
(463, '7hd54ehkdebkpgn9saag2babjm', 7, 1, '2025-06-28 15:06:41', 0),
(581, 'i24o4uio1t80e3oda2bcrs6bt2', 2, 1, '2025-06-30 11:06:15', 0);

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
(7, 'uploads/685e23a7d9f3c_Remove background project-2 (2).png', '16x2 LCD with I2C', 'LCD display module with I2C interface', 100, 10, 120.00, 1, '2025-06-27 00:00:00', '2025-06-30 09:08:40', 'Available'),
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
(1, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685672AD2BECA\'>#RSV-685672AD2BECA</a> by No no for 2025-06-21.', 1, '2025-06-21 08:51:57', 'read', 'RSV-685672AD2BECA', 37),
(2, NULL, 'You have logged in successfully.', 1, '2025-06-23 00:30:06', 'read', NULL, NULL),
(3, NULL, 'You have logged in successfully.', 1, '2025-06-23 00:30:08', 'read', NULL, NULL),
(4, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6858A17C3A9EC\'>#RSV-6858A17C3A9EC</a> by Carl Caraos for 2025-06-23.', 1, '2025-06-23 00:36:12', 'read', 'RSV-6858A17C3A9EC', 43),
(5, NULL, 'You have logged in successfully.', 1, '2025-06-23 00:58:01', 'read', NULL, NULL),
(6, NULL, 'You have logged in successfully.', 1, '2025-06-23 00:58:56', 'read', NULL, NULL),
(7, NULL, 'You have logged in successfully.', 1, '2025-06-23 01:16:08', 'read', NULL, NULL),
(8, NULL, 'Product added (batch): sample', 1, '2025-06-23 01:36:44', 'read', NULL, 0),
(9, NULL, 'Product deleted (batch): ID 0', 1, '2025-06-23 01:37:16', 'read', NULL, 0),
(10, NULL, 'Product added (batch): sample', 1, '2025-06-23 01:46:53', 'read', NULL, 0),
(11, NULL, 'Product status changed: sample from \'Available\' to \'Not Available\'', 1, '2025-06-23 01:47:21', 'read', NULL, 0),
(12, NULL, 'Product deleted: sample (ID 0)', 1, '2025-06-23 01:47:38', 'read', NULL, 0),
(13, NULL, 'Reservation deleted: Code RSV-68565AAC2501D, Product: SG90 Servo Motor, Requested by: Carl Caraos', 1, '2025-06-23 01:54:24', 'read', NULL, NULL),
(14, NULL, 'Reservations deleted: Code RSV-68565AAC2501D, Product: Sound Sensor Module, Requested by: Carl Caraos; Code RSV-68565AAC2501D, Product: RTC DS3231 Module, Requested by: Carl Caraos; Code RSV-68565AAC2501D, Product: ESP32 Dev Board, Requested by: Carl Caraos', 1, '2025-06-23 01:54:53', 'read', NULL, NULL),
(15, NULL, 'Reservation status changed: Code RSV-68565AAC2501D, Product: DHT11 Temperature Sensor, Requested by: Carl Caraos from \'approved\' to \'pending\'', 1, '2025-06-23 01:58:36', 'read', NULL, NULL),
(16, NULL, 'Reservation status changed: Code RSV-68565AAC2501D, Product: DHT11 Temperature Sensor, Requested by: Carl Caraos from \'pending\' to \'approved\'', 1, '2025-06-23 01:59:03', 'read', NULL, NULL),
(17, NULL, 'Reservation status changed: Code RSV-68565CDAC1DAB, Product: Tilt Sensor Module, Requested by: Jade from \'pending\' to \'approved\'', 1, '2025-06-23 01:59:03', 'read', NULL, NULL),
(18, NULL, 'Reservation status changed: Code RSV-68565AAC2501D, Product: DHT11 Temperature Sensor, Requested by: Carl Caraos from \'approved\' to \'pending\'', 1, '2025-06-23 02:02:33', 'read', NULL, NULL),
(19, NULL, 'Reservation status changed: Code RSV-68565CDAC1DAB, Product: Tilt Sensor Module, Requested by: Jade from \'approved\' to \'pending\'', 1, '2025-06-23 02:02:33', 'read', NULL, NULL),
(20, NULL, 'Reservation status changed: Code RSV-68565AAC2501D, Product: DHT11 Temperature Sensor, Requested by: Carl Caraos from \'pending\' to \'borrowed\'', 1, '2025-06-23 02:03:03', 'read', NULL, NULL),
(21, NULL, 'Reservation status changed: Code RSV-68565CDAC1DAB, Product: Tilt Sensor Module, Requested by: Jade from \'pending\' to \'borrowed\'', 1, '2025-06-23 02:03:03', 'read', NULL, NULL),
(22, NULL, 'Reservation status changed: Code RSV-68565CDAC1DAB, Product: ESP32 Dev Board, Requested by: Jade from \'pending\' to \'borrowed\'', 1, '2025-06-23 02:03:03', 'read', NULL, NULL),
(23, NULL, 'Returned 1 of DHT11 Temperature Sensor (Reservation Code: RSV-68565AAC2501D, Requested by: Carl Caraos)', 1, '2025-06-23 02:05:48', 'read', NULL, NULL),
(24, NULL, 'Returned 1 of Tilt Sensor Module (Reservation Code: RSV-68565CDAC1DAB, Requested by: Jade)', 1, '2025-06-23 02:06:16', 'read', NULL, NULL),
(25, NULL, 'Returned 1 of ESP32 Dev Board (Reservation Code: RSV-68565CDAC1DAB, Requested by: Jade)', 1, '2025-06-23 02:06:16', 'read', NULL, NULL),
(26, NULL, 'Reservation status changed: Code RSV-68565CDAC1DAB, Product: Relay 4-Channel Module, Requested by: Jade from \'pending\' to \'borrowed\'', 1, '2025-06-23 02:07:22', 'read', NULL, NULL),
(27, NULL, 'Returned 1 of Relay 4-Channel Module (Reservation Code: RSV-68565CDAC1DAB, Requested by: Jade)', 1, '2025-06-23 02:07:47', 'read', NULL, NULL),
(28, NULL, 'Returned 1 of Relay 4-Channel Module (Reservation Code: RSV-68565CDAC1DAB, Requested by: Jade)', 1, '2025-06-23 02:08:20', 'read', NULL, NULL),
(29, NULL, 'Reservation status changed: Code RSV-68565CDAC1DAB, Product: Laser Module 5mW, Requested by: Jade from \'pending\' to \'borrowed\'', 1, '2025-06-23 02:08:51', 'read', NULL, NULL),
(30, NULL, 'Reservation status changed: Code RSV-68565CDAC1DAB, Product: Fingerprint Sensor Module, Requested by: Jade from \'pending\' to \'borrowed\'', 1, '2025-06-23 02:08:51', 'read', NULL, NULL),
(31, NULL, 'Reservation status changed: Code RSV-68565CDAC1DAB, Product: 5V Relay Module, Requested by: Jade from \'pending\' to \'borrowed\'', 1, '2025-06-23 02:11:27', 'read', NULL, NULL),
(32, NULL, 'Reservation status changed: Code RSV-68565D9446D67, Product: ESP32 Dev Board, Requested by: Arlyn  from \'pending\' to \'borrowed\'', 1, '2025-06-23 02:11:27', 'read', NULL, NULL),
(33, NULL, 'Reservations deleted: Code RSV-68565CDAC1DAB, Product: 5V Relay Module, Requested by: Jade; Code RSV-68565D9446D67, Product: ESP32 Dev Board, Requested by: Arlyn ', 1, '2025-06-23 02:11:38', 'read', NULL, NULL),
(34, NULL, 'Reservation status changed: Code RSV-68565D9446D67, Product: NodeMCU ESP8266, Requested by: Arlyn  from \'pending\' to \'borrowed\'', 1, '2025-06-23 02:24:45', 'read', NULL, NULL),
(35, NULL, 'Reservation status changed: Code RSV-68565E66A351D, Product: Raspberry Pi 4 Model B, Requested by: Sample from \'pending\' to \'borrowed\'', 1, '2025-06-23 02:24:45', 'read', NULL, NULL),
(36, NULL, 'Returned 1 of NodeMCU ESP8266 (Reservation Code: RSV-68565D9446D67, Requested by: Arlyn )', 1, '2025-06-23 02:25:04', 'read', NULL, NULL),
(37, NULL, 'Returned 1 of Raspberry Pi 4 Model B (Reservation Code: RSV-68565E66A351D, Requested by: Sample)', 1, '2025-06-23 02:25:04', 'read', NULL, NULL),
(38, NULL, 'Return logs deleted: Code RSV-68565E66A351D, Product: Raspberry Pi 4 Model B, Requested by: Sample', 1, '2025-06-23 02:25:16', 'read', NULL, NULL),
(39, NULL, 'Reservation status changed: Code RSV-68565E66A351D, Product: ESP32 Dev Board, Requested by: Sample from \'pending\' to \'borrowed\'', 1, '2025-06-23 02:25:37', 'read', NULL, NULL),
(40, NULL, 'Reservation status changed: Code RSV-68567118542DC, Product: NodeMCU ESP8266, Requested by: Carl Caraos from \'pending\' to \'borrowed\'', 1, '2025-06-23 02:25:37', 'read', NULL, NULL),
(41, NULL, 'Returned 1 of ESP32 Dev Board (Reservation Code: RSV-68565E66A351D, Requested by: Sample)', 1, '2025-06-23 02:25:57', 'read', NULL, NULL),
(42, NULL, 'Returned 1 of NodeMCU ESP8266 (Reservation Code: RSV-68567118542DC, Requested by: Carl Caraos)', 1, '2025-06-23 02:25:57', 'read', NULL, NULL),
(43, NULL, 'Return logs deleted: Code RSV-68565D9446D67, Product: NodeMCU ESP8266, Requested by: Arlyn ; Code RSV-68565E66A351D, Product: ESP32 Dev Board, Requested by: Sample; Code RSV-68567118542DC, Product: NodeMCU ESP8266, Requested by: Carl Caraos', 1, '2025-06-23 02:26:07', 'read', NULL, NULL),
(44, NULL, 'Category updated: ID 6, Name changed from \'HOUSEHOLD AND CLEANING\' to \'ALCOHOL & BREVERAGE\', Description changed from \'sample\' to \'sample\'', 1, '2025-06-23 02:35:36', 'read', NULL, NULL),
(45, NULL, 'Category deleted: ID 6: ALCOHOL & BREVERAGE', 1, '2025-06-23 02:35:58', 'read', NULL, NULL),
(46, NULL, 'Category added: ID 7, Name \'FOOD & GROCERIES\'', 1, '2025-06-23 02:36:26', 'read', NULL, NULL),
(47, NULL, 'Damage report updated: ID 1, Product: 5V Relay Module, ADXL345 Accelerometer, Arduino Uno R3', 1, '2025-06-23 02:51:00', 'read', NULL, NULL),
(48, NULL, 'Damage report added: ID 2, Product: wifi 5g globe at home', 1, '2025-06-23 02:52:18', 'read', NULL, NULL),
(49, NULL, 'Reservation status changed: Code RSV-685672AD2BECA, Product: Raspberry Pi 4 Model B, Requested by: No no from \'pending\' to \'borrowed\'', 1, '2025-06-23 02:52:44', 'read', NULL, NULL),
(50, NULL, 'Categories deleted: ID 7: FOOD & GROCERIES', 1, '2025-06-23 03:09:03', 'read', NULL, NULL),
(51, NULL, 'Category added: ID 8, Name \'dad\'', 1, '2025-06-23 05:32:03', 'read', NULL, NULL),
(52, NULL, 'Returned 1 of Raspberry Pi 4 Model B (Reservation Code: RSV-685672AD2BECA, Requested by: No no)', 1, '2025-06-23 05:33:49', 'read', NULL, NULL),
(53, NULL, 'Product status changed: wifi 5g globe at home  from \'Available\' to \'Not Available\'', 1, '2025-06-23 05:34:28', 'read', NULL, 2),
(54, NULL, 'Product status changed: wifi 5g globe at home  from \'Not Available\' to \'Available\'', 1, '2025-06-23 05:38:01', 'read', NULL, 2),
(55, NULL, 'Product updated (ID 31): Stock: 50 → 15; Category: \'Motors\' → \'Accessories\'; Status: \'Available\' → \'Not Available\'', 1, '2025-06-23 05:43:10', 'read', NULL, 31),
(56, NULL, 'Reservation updated: Code 98, Product: NRF24L01 Module, Requested by: ', 1, '2025-06-23 05:43:38', 'read', NULL, NULL),
(57, NULL, 'Reservation status changed: Code RSV-6858A17C3A9EC, Product: NRF24L01 Module, Requested by: Carl Caraos from \'pending\' to \'\'', 1, '2025-06-23 05:43:38', 'read', NULL, NULL),
(58, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6858EB53922E7\'>#RSV-6858EB53922E7</a> by Carl Caraos for 2025-06-23.', 1, '2025-06-23 05:51:15', 'read', 'RSV-6858EB53922E7', 37),
(59, NULL, 'Reservations deleted: Code RSV-6858A17C3A9EC, Product: NRF24L01 Module, Requested by: ', 1, '2025-06-23 05:56:24', 'read', NULL, NULL),
(60, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6858EDB8EF682\'>#RSV-6858EDB8EF682</a> by Carl Caraos for 2025-06-23.', 1, '2025-06-23 06:01:29', 'read', 'RSV-6858EDB8EF682', 41),
(61, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6858EDB8EF682\'>#RSV-6858EDB8EF682</a> by Carl Caraos for 2025-06-23.', 1, '2025-06-23 06:01:29', 'read', 'RSV-6858EDB8EF682', 36),
(62, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6858EDB8EF682\'>#RSV-6858EDB8EF682</a> by Carl Caraos for 2025-06-23.', 1, '2025-06-23 06:01:29', 'read', 'RSV-6858EDB8EF682', 37),
(63, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6858EDB8EF682\'>#RSV-6858EDB8EF682</a> by Carl Caraos for 2025-06-23.', 1, '2025-06-23 06:01:29', 'read', 'RSV-6858EDB8EF682', 35),
(64, NULL, 'Product updated (ID 32): Stock: 117 → 10', 1, '2025-06-23 06:02:18', 'read', NULL, 32),
(65, NULL, 'Product deleted: SG90 Servo Motor (ID 33)', 1, '2025-06-23 06:03:02', 'read', NULL, 33),
(66, NULL, 'Product updated (ID 35): Stock: 100 → 10; Reorder Level: 30 → 5', 1, '2025-06-23 06:03:16', 'read', NULL, 35),
(67, NULL, 'Product updated (ID 31): Status: \'Not Available\' → \'Available\'', 1, '2025-06-23 06:06:04', 'read', NULL, 31),
(68, NULL, 'Product updated (ID 39): Stock: 150 → 10; Reorder Level: 50 → 10', 1, '2025-06-23 06:06:24', 'read', NULL, 39),
(69, NULL, 'Product updated (ID 39): Status: \'Not Available\' → \'Available\'', 1, '2025-06-23 06:06:32', 'read', NULL, 39),
(70, NULL, 'Product updated (ID 40): Stock: 80 → 10; Reorder Level: 20 → 5; Status: \'Not Available\' → \'Available\'', 1, '2025-06-23 06:06:45', 'read', NULL, 40),
(71, NULL, 'Product updated (ID 34): Stock: 20 → 10; Status: \'Not Available\' → \'Available\'', 1, '2025-06-23 06:06:59', 'read', NULL, 34),
(72, NULL, 'Product updated (ID 36): Stock: 104 → 10; Reorder Level: 20 → 5', 1, '2025-06-23 06:07:14', 'read', NULL, 36),
(73, NULL, 'Reservations deleted: Code RSV-6858EB53922E7, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos; Code RSV-6858EDB8EF682, Product: 5V Relay Module, Requested by: Carl Caraos; Code RSV-6858EDB8EF682, Product: ESP32 Dev Board, Requested by: Carl Caraos; Code RSV-6858EDB8EF682, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos; Code RSV-6858EDB8EF682, Product: Jumper Wires (M-M, M-F, F-F), Requested by: Carl Caraos', 1, '2025-06-23 06:08:52', 'read', NULL, NULL),
(74, NULL, 'Product updated (ID 37): Stock: 41 → 10', 1, '2025-06-23 06:09:15', 'read', NULL, 37),
(75, NULL, 'Product updated (ID 38): Stock: 123 → 10; Reorder Level: 30 → 5', 1, '2025-06-23 06:09:27', 'read', NULL, 38),
(76, NULL, 'Product updated (ID 41): Stock: 200 → 10; Reorder Level: 50 → 10; Category: \'Accessories\' → \'Microcontrollers\'', 1, '2025-06-23 06:09:56', 'read', NULL, 41),
(77, NULL, 'Product updated (ID 42): Stock: 60 → 10; Reorder Level: 15 → 5', 1, '2025-06-23 06:10:21', 'read', NULL, 42),
(78, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6858F01CB6555\'>#RSV-6858F01CB6555</a> by Carl Caraos for 2025-06-23.', 1, '2025-06-23 06:11:40', 'read', 'RSV-6858F01CB6555', 40),
(79, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6858F01CB6555\'>#RSV-6858F01CB6555</a> by Carl Caraos for 2025-06-23.', 1, '2025-06-23 06:11:40', 'read', 'RSV-6858F01CB6555', 36),
(80, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6858F01CB6555\'>#RSV-6858F01CB6555</a> by Carl Caraos for 2025-06-23.', 1, '2025-06-23 06:11:40', 'read', 'RSV-6858F01CB6555', 37),
(81, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6858F01CB6555\'>#RSV-6858F01CB6555</a> by Carl Caraos for 2025-06-23.', 1, '2025-06-23 06:11:40', 'read', 'RSV-6858F01CB6555', 39),
(82, NULL, 'Reservation status changed: Code RSV-6858F01CB6555, Product: L298N Motor Driver, Requested by: Carl Caraos from \'pending\' to \'approved\'', 1, '2025-06-23 06:40:35', 'read', NULL, NULL),
(83, NULL, 'Reservation status changed: Code RSV-6858F01CB6555, Product: ESP32 Dev Board, Requested by: Carl Caraos from \'pending\' to \'approved\'', 1, '2025-06-23 06:40:35', 'read', NULL, NULL),
(84, NULL, 'Reservation status changed: Code RSV-6858F01CB6555, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos from \'pending\' to \'approved\'', 1, '2025-06-23 06:40:35', 'read', NULL, NULL),
(85, NULL, 'Reservation status changed: Code RSV-6858F01CB6555, Product: LM2596 Buck Converter, Requested by: Carl Caraos from \'pending\' to \'approved\'', 1, '2025-06-23 06:40:35', 'read', NULL, NULL),
(86, NULL, 'Reservation status changed: Code RSV-6858F01CB6555, Product: L298N Motor Driver, Requested by: Carl Caraos from \'approved\' to \'borrowed\'', 1, '2025-06-23 06:40:55', 'read', NULL, NULL),
(87, NULL, 'Reservation status changed: Code RSV-6858F01CB6555, Product: ESP32 Dev Board, Requested by: Carl Caraos from \'approved\' to \'borrowed\'', 1, '2025-06-23 06:40:55', 'read', NULL, NULL),
(88, NULL, 'Reservation status changed: Code RSV-6858F01CB6555, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos from \'approved\' to \'borrowed\'', 1, '2025-06-23 06:40:55', 'read', NULL, NULL),
(89, NULL, 'Reservation status changed: Code RSV-6858F01CB6555, Product: LM2596 Buck Converter, Requested by: Carl Caraos from \'approved\' to \'borrowed\'', 1, '2025-06-23 06:40:55', 'read', NULL, NULL),
(90, NULL, 'Reservations deleted: Code RSV-6858F01CB6555, Product: L298N Motor Driver, Requested by: Carl Caraos; Code RSV-6858F01CB6555, Product: ESP32 Dev Board, Requested by: Carl Caraos; Code RSV-6858F01CB6555, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos; Code RSV-6858F01CB6555, Product: LM2596 Buck Converter, Requested by: Carl Caraos', 1, '2025-06-23 06:44:40', 'read', NULL, NULL),
(91, NULL, 'Return logs deleted: Code RSV-685672AD2BECA, Product: Raspberry Pi 4 Model B, Requested by: No no', 1, '2025-06-23 06:46:12', 'read', NULL, NULL),
(92, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6858F87CA0AC5\'>#RSV-6858F87CA0AC5</a> by Carl Caraos for 2025-06-23.', 1, '2025-06-23 06:47:24', 'read', 'RSV-6858F87CA0AC5', 39),
(93, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6858F87CA0AC5\'>#RSV-6858F87CA0AC5</a> by Carl Caraos for 2025-06-23.', 1, '2025-06-23 06:47:24', 'read', 'RSV-6858F87CA0AC5', 38),
(94, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6858F87CA0AC5\'>#RSV-6858F87CA0AC5</a> by Carl Caraos for 2025-06-23.', 1, '2025-06-23 06:47:24', 'read', 'RSV-6858F87CA0AC5', 37),
(95, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6858F87CA0AC5\'>#RSV-6858F87CA0AC5</a> by Carl Caraos for 2025-06-23.', 1, '2025-06-23 06:47:24', 'read', 'RSV-6858F87CA0AC5', 36),
(96, NULL, 'Reservation status changed: Code RSV-6858F87CA0AC5, Product: LM2596 Buck Converter, Requested by: Carl Caraos from \'pending\' to \'borrowed\'', 1, '2025-06-23 06:48:06', 'read', NULL, NULL),
(97, NULL, 'Reservation status changed: Code RSV-6858F87CA0AC5, Product: DHT11 Temperature Sensor, Requested by: Carl Caraos from \'pending\' to \'borrowed\'', 1, '2025-06-23 06:48:06', 'read', NULL, NULL),
(98, NULL, 'Reservation status changed: Code RSV-6858F87CA0AC5, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos from \'pending\' to \'borrowed\'', 1, '2025-06-23 06:48:06', 'read', NULL, NULL),
(99, NULL, 'Reservation status changed: Code RSV-6858F87CA0AC5, Product: ESP32 Dev Board, Requested by: Carl Caraos from \'pending\' to \'borrowed\'', 1, '2025-06-23 06:48:06', 'read', NULL, NULL),
(100, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6858F96ED1F5F\'>#RSV-6858F96ED1F5F</a> by Carl Caraos for 2025-06-23.', 1, '2025-06-23 06:51:26', 'read', 'RSV-6858F96ED1F5F', 36),
(101, NULL, 'Reservation status changed: Code RSV-6858F96ED1F5F, Product: ESP32 Dev Board, Requested by: Carl Caraos from \'pending\' to \'approved\'', 1, '2025-06-23 06:51:37', 'read', NULL, NULL),
(102, NULL, 'Reservation checked_by changed: Code RSV-6858F96ED1F5F, Product: ESP32 Dev Board, Requested by: Carl Caraos from \'sample\' to \'Ragnhild\'', 1, '2025-06-23 06:51:59', 'read', NULL, NULL),
(103, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6858FA3F58CB2\'>#RSV-6858FA3F58CB2</a> by Carl Caraos for 2025-06-23.', 1, '2025-06-23 06:54:55', 'read', 'RSV-6858FA3F58CB2', 38),
(104, NULL, 'Reservations deleted: Code RSV-6858F96ED1F5F, Product: ESP32 Dev Board, Requested by: Carl Caraos; Code RSV-6858FA3F58CB2, Product: DHT11 Temperature Sensor, Requested by: Carl Caraos', 1, '2025-06-23 06:55:29', 'read', NULL, NULL),
(105, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6858FA76F2341\'>#RSV-6858FA76F2341</a> by Carl Caraos for 2025-06-23.', 1, '2025-06-23 06:55:50', 'read', 'RSV-6858FA76F2341', 37),
(106, NULL, 'Reservations deleted: Code RSV-6858F87CA0AC5, Product: LM2596 Buck Converter, Requested by: Carl Caraos; Code RSV-6858F87CA0AC5, Product: DHT11 Temperature Sensor, Requested by: Carl Caraos; Code RSV-6858F87CA0AC5, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos; Code RSV-6858F87CA0AC5, Product: ESP32 Dev Board, Requested by: Carl Caraos', 1, '2025-06-23 06:56:41', 'read', NULL, NULL),
(107, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6858FB005AC3A\'>#RSV-6858FB005AC3A</a> by Carl Caraos for 2025-06-23.', 1, '2025-06-23 06:58:08', 'read', 'RSV-6858FB005AC3A', 36),
(108, NULL, 'Reservation status changed: Code RSV-6858FA76F2341, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos from \'pending\' to \'borrowed\'', 1, '2025-06-23 06:58:48', 'read', NULL, NULL),
(109, NULL, 'Reservation status changed: Code RSV-6858FB005AC3A, Product: ESP32 Dev Board, Requested by: Carl Caraos from \'pending\' to \'borrowed\'', 1, '2025-06-23 06:58:48', 'read', NULL, NULL),
(110, NULL, 'Reservations deleted: Code RSV-6858FA76F2341, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos; Code RSV-6858FB005AC3A, Product: ESP32 Dev Board, Requested by: Carl Caraos', 1, '2025-06-23 07:00:17', 'read', NULL, NULL),
(111, NULL, 'You have logged in successfully.', 1, '2025-06-23 07:00:38', 'read', NULL, NULL),
(112, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6858FD04DBFB4\'>#RSV-6858FD04DBFB4</a> by Carl Caraos for 2025-06-23.', 1, '2025-06-23 07:06:44', 'read', 'RSV-6858FD04DBFB4', 37),
(113, NULL, 'Reservation status changed: Code RSV-6858FD04DBFB4, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos from \'pending\' to \'borrowed\'', 1, '2025-06-23 07:07:01', 'read', NULL, NULL),
(114, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6858FDDD138C7\'>#RSV-6858FDDD138C7</a> by Carl Caraos for 2025-06-23.', 1, '2025-06-23 07:10:21', 'read', 'RSV-6858FDDD138C7', 38),
(115, NULL, 'Reservation status changed: Code RSV-6858FDDD138C7, Product: DHT11 Temperature Sensor, Requested by: Carl Caraos from \'pending\' to \'borrowed\'', 1, '2025-06-23 07:10:32', 'read', NULL, NULL),
(116, NULL, 'Product added (batch): new', 1, '2025-06-23 07:12:25', 'read', NULL, 0),
(117, NULL, 'Products deleted: new (ID 0)', 1, '2025-06-23 07:14:07', 'read', NULL, NULL),
(118, NULL, 'Product added (batch): wow', 1, '2025-06-23 07:14:32', 'read', NULL, 0),
(119, NULL, 'Product updated (ID 0): Image updated', 1, '2025-06-23 07:14:57', 'read', NULL, 0),
(120, NULL, 'You have logged in successfully.', 1, '2025-06-23 07:22:19', 'read', NULL, NULL),
(121, NULL, 'Products deleted: wow (ID 0)', 1, '2025-06-23 07:26:15', 'read', NULL, NULL),
(122, NULL, 'Product added (batch): sample', 1, '2025-06-23 07:27:58', 'read', NULL, 102932),
(123, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685902602E2FC\'>#RSV-685902602E2FC</a> by Carl Caraos for 2025-06-23.', 1, '2025-06-23 07:29:36', 'read', 'RSV-685902602E2FC', 102932),
(124, NULL, 'Reservation checked_by changed: Code RSV-685902602E2FC, Product: sample, Requested by: Carl Caraos from \'\' to \'\'', 1, '2025-06-23 07:33:11', 'read', NULL, NULL),
(125, NULL, 'Reservation status changed: Code RSV-685902602E2FC, Product: sample, Requested by: Carl Caraos from \'pending\' to \'approved\'', 1, '2025-06-23 07:33:11', 'read', NULL, NULL),
(126, NULL, 'Reservation checked_by changed: Code RSV-685902602E2FC, Product: sample, Requested by: Carl Caraos from \'\' to \'me\'', 1, '2025-06-23 07:33:18', 'read', NULL, NULL),
(127, NULL, 'You have logged in successfully.', 1, '2025-06-23 08:05:11', 'read', NULL, NULL),
(128, NULL, 'Reservation status changed: Code RSV-6858FD04DBFB4, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos from \'borrowed\' to \'pending\'', 1, '2025-06-23 08:24:51', 'read', NULL, NULL),
(129, NULL, 'Reservation status changed: Code RSV-6858FDDD138C7, Product: DHT11 Temperature Sensor, Requested by: Carl Caraos from \'borrowed\' to \'pending\'', 1, '2025-06-23 08:24:51', 'read', NULL, NULL),
(130, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685910E8EBF07\'>#RSV-685910E8EBF07</a> by Carl Caraos for 2025-06-23.', 1, '2025-06-23 08:31:36', 'read', 'RSV-685910E8EBF07', 40),
(131, NULL, 'You have logged in successfully.', 1, '2025-06-23 08:38:06', 'read', NULL, NULL),
(132, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685912CD85861\'>#RSV-685912CD85861</a> by Carl Caraos for 2025-06-23.', 1, '2025-06-23 08:39:41', 'read', 'RSV-685912CD85861', 36),
(133, NULL, 'Returned 10 of L298N Motor Driver (Reservation Code: RSV-685910E8EBF07, Requested by: Carl Caraos)', 1, '2025-06-23 08:41:21', 'read', NULL, NULL),
(134, NULL, 'Returned 10 of ESP32 Dev Board (Reservation Code: RSV-685912CD85861, Requested by: Carl Caraos)', 1, '2025-06-23 08:41:21', 'read', NULL, NULL),
(135, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685915BE46D82\'>#RSV-685915BE46D82</a> by Carl Caraos for 2025-06-23.', 1, '2025-06-23 08:52:14', 'read', 'RSV-685915BE46D82', 40),
(136, NULL, 'Reservation status changed: Code RSV-685915BE46D82, Product: L298N Motor Driver, Requested by: Carl Caraos from \'pending\' to \'borrowed\'', 1, '2025-06-23 08:55:57', 'read', NULL, NULL),
(137, NULL, 'Returned 10 of L298N Motor Driver (Reservation Code: RSV-685915BE46D82, Requested by: Carl Caraos)', 1, '2025-06-23 08:56:38', 'read', NULL, NULL),
(138, NULL, 'Product deleted: sample (ID 102932)', 1, '2025-06-23 08:59:36', 'read', NULL, 102932),
(139, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685917A910A89\'>#RSV-685917A910A89</a> by Carl Caraos for 2025-06-23.', 1, '2025-06-23 09:00:25', 'read', 'RSV-685917A910A89', 36),
(140, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685917A910A89\'>#RSV-685917A910A89</a> by Carl Caraos for 2025-06-23.', 1, '2025-06-23 09:00:25', 'read', 'RSV-685917A910A89', 39),
(141, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685917A910A89\'>#RSV-685917A910A89</a> by Carl Caraos for 2025-06-23.', 1, '2025-06-23 09:00:25', 'read', 'RSV-685917A910A89', 37),
(142, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685917A910A89\'>#RSV-685917A910A89</a> by Carl Caraos for 2025-06-23.', 1, '2025-06-23 09:00:25', 'read', 'RSV-685917A910A89', 38),
(143, NULL, 'Reservation status changed: Code RSV-685917A910A89, Product: ESP32 Dev Board, Requested by: Carl Caraos from \'pending\' to \'approved\'', 1, '2025-06-23 09:11:26', 'read', NULL, NULL),
(144, NULL, 'Reservation status changed: Code RSV-685917A910A89, Product: LM2596 Buck Converter, Requested by: Carl Caraos from \'pending\' to \'approved\'', 1, '2025-06-23 09:11:26', 'read', NULL, NULL),
(145, NULL, 'Reservation status changed: Code RSV-685917A910A89, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos from \'pending\' to \'approved\'', 1, '2025-06-23 09:11:26', 'read', NULL, NULL),
(146, NULL, 'Reservation status changed: Code RSV-685917A910A89, Product: DHT11 Temperature Sensor, Requested by: Carl Caraos from \'pending\' to \'approved\'', 1, '2025-06-23 09:11:26', 'read', NULL, NULL),
(147, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6859EF455E229\'>#RSV-6859EF455E229</a> by Carl Caraos for 2025-06-24.', 1, '2025-06-24 00:20:21', 'read', 'RSV-6859EF455E229', 39),
(148, NULL, 'You have logged in successfully.', 1, '2025-06-24 00:20:29', 'read', NULL, NULL),
(149, NULL, 'You have logged in successfully.', 1, '2025-06-24 00:23:08', 'read', NULL, NULL),
(150, NULL, 'Reservation updated: Code RSV-685917A910A89, Product: ESP32 Dev Board, Requested by: Carl Caraos', 1, '2025-06-24 01:10:29', 'read', NULL, NULL),
(151, NULL, 'Reservation status changed: Code RSV-685917A910A89, Product: ESP32 Dev Board, Requested by: Carl Caraos from \'approved\' to \'pending\'', 1, '2025-06-24 01:10:29', 'read', NULL, NULL),
(152, NULL, 'Reservation updated: Code RSV-685917A910A89, Product: LM2596 Buck Converter, Requested by: Carl Caraos', 1, '2025-06-24 01:10:29', 'read', NULL, NULL),
(153, NULL, 'Reservation status changed: Code RSV-685917A910A89, Product: LM2596 Buck Converter, Requested by: Carl Caraos from \'approved\' to \'pending\'', 1, '2025-06-24 01:10:29', 'read', NULL, NULL),
(154, NULL, 'Reservation updated: Code RSV-685917A910A89, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos', 1, '2025-06-24 01:10:29', 'read', NULL, NULL),
(155, NULL, 'Reservation status changed: Code RSV-685917A910A89, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos from \'approved\' to \'pending\'', 1, '2025-06-24 01:10:29', 'read', NULL, NULL),
(156, NULL, 'Reservation updated: Code RSV-685917A910A89, Product: DHT11 Temperature Sensor, Requested by: Carl Caraos', 1, '2025-06-24 01:10:29', 'read', NULL, NULL),
(157, NULL, 'Reservation status changed: Code RSV-685917A910A89, Product: DHT11 Temperature Sensor, Requested by: Carl Caraos from \'approved\' to \'pending\'', 1, '2025-06-24 01:10:29', 'read', NULL, NULL),
(158, NULL, 'Reservation updated: Code RSV-6859EF455E229, Product: LM2596 Buck Converter, Requested by: Carl Caraos', 1, '2025-06-24 01:10:29', 'read', NULL, NULL),
(159, NULL, 'Reservation updated: Code RSV-685917A910A89, Product: ESP32 Dev Board, Requested by: Carl Caraos', 1, '2025-06-24 01:37:03', 'read', NULL, NULL),
(160, NULL, 'Reservation status changed: Code RSV-685917A910A89, Product: ESP32 Dev Board, Requested by: Carl Caraos from \'pending\' to \'approved\'', 1, '2025-06-24 01:37:03', 'read', NULL, NULL),
(161, NULL, 'Reservation updated: Code RSV-685917A910A89, Product: LM2596 Buck Converter, Requested by: Carl Caraos', 1, '2025-06-24 01:37:03', 'read', NULL, NULL),
(162, NULL, 'Reservation status changed: Code RSV-685917A910A89, Product: LM2596 Buck Converter, Requested by: Carl Caraos from \'pending\' to \'approved\'', 1, '2025-06-24 01:37:03', 'read', NULL, NULL),
(163, NULL, 'Reservation updated: Code RSV-685917A910A89, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos', 1, '2025-06-24 01:37:03', 'read', NULL, NULL),
(164, NULL, 'Reservation status changed: Code RSV-685917A910A89, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos from \'pending\' to \'approved\'', 1, '2025-06-24 01:37:03', 'read', NULL, NULL),
(165, NULL, 'Reservation updated: Code RSV-685917A910A89, Product: DHT11 Temperature Sensor, Requested by: Carl Caraos', 1, '2025-06-24 01:37:03', 'read', NULL, NULL),
(166, NULL, 'Reservation status changed: Code RSV-685917A910A89, Product: DHT11 Temperature Sensor, Requested by: Carl Caraos from \'pending\' to \'approved\'', 1, '2025-06-24 01:37:03', 'read', NULL, NULL),
(167, NULL, 'Reservation updated: Code RSV-6859EF455E229, Product: LM2596 Buck Converter, Requested by: Carl Caraos', 1, '2025-06-24 01:37:03', 'read', NULL, NULL),
(168, NULL, 'Reservation status changed: Code RSV-6859EF455E229, Product: LM2596 Buck Converter, Requested by: Carl Caraos from \'pending\' to \'approved\'', 1, '2025-06-24 01:37:03', 'read', NULL, NULL),
(169, NULL, 'Reservation updated: Code RSV-685917A910A89, Product: ESP32 Dev Board, Requested by: Carl Caraos', 1, '2025-06-24 01:47:18', 'read', NULL, NULL),
(170, NULL, 'Reservation updated: Code RSV-685917A910A89, Product: LM2596 Buck Converter, Requested by: Carl Caraos', 1, '2025-06-24 01:47:18', 'read', NULL, NULL),
(171, NULL, 'Reservation updated: Code RSV-685917A910A89, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos', 1, '2025-06-24 01:47:18', 'read', NULL, NULL),
(172, NULL, 'Reservation updated: Code RSV-685917A910A89, Product: DHT11 Temperature Sensor, Requested by: Carl Caraos', 1, '2025-06-24 01:47:19', 'read', NULL, NULL),
(173, NULL, 'Reservation updated: Code RSV-6859EF455E229, Product: LM2596 Buck Converter, Requested by: Carl Caraos', 1, '2025-06-24 01:47:19', 'read', NULL, NULL),
(174, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A0BB13FC9F\'>#RSV-685A0BB13FC9F</a> by Carl Caraos for 2025-06-24.', 1, '2025-06-24 02:21:37', 'read', 'RSV-685A0BB13FC9F', 41),
(175, NULL, 'Reservation updated (ID 127, Code RSV-685A0BB13FC9F, Product: 5V Relay Module, Requested by: Carl Caraos): Checked By: \'Ragnhild\' → \'Mylove Ragnhild\'', 1, '2025-06-24 02:45:06', 'read', NULL, NULL),
(176, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A12126156B\'>#RSV-685A12126156B</a> by Ragnhild for 2025-06-24.', 1, '2025-06-24 02:48:50', 'read', 'RSV-685A12126156B', 37),
(177, NULL, 'Reservation updated (ID 127, Code RSV-685A0BB13FC9F, Product: 5V Relay Module, Requested by: Carl Caraos): Status: \'borrowed\' → \'pending\'; Checked By: \'Mylove Ragnhild\' → \'Carl\'', 1, '2025-06-24 02:51:18', 'read', NULL, NULL),
(178, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A13CA9415C\'>#RSV-685A13CA9415C</a> by Carl Caraos for 2025-06-24.', 1, '2025-06-24 02:56:10', 'read', 'RSV-685A13CA9415C', 39),
(179, NULL, 'Reservation updated (ID 129, Code RSV-685A13CA9415C, Product: LM2596 Buck Converter, Requested by: Carl Caraos): Status: \'pending\' → \'borrowed\'', 1, '2025-06-24 02:56:25', 'read', NULL, NULL),
(180, NULL, 'Reservation updated (ID 129, Code RSV-685A13CA9415C, Product: LM2596 Buck Converter, Requested by: Carl Caraos): Status: \'borrowed\' → \'pending\'', 1, '2025-06-24 03:18:08', 'read', NULL, NULL),
(181, NULL, 'Reservation updated (ID 129, Code RSV-685A13CA9415C, Product: LM2596 Buck Converter, Requested by: Carl Caraos): Status: \'pending\' → \'borrowed\'', 1, '2025-06-24 03:18:15', 'read', NULL, NULL),
(182, NULL, 'Reservation updated (ID 129, Code RSV-685A13CA9415C, Product: LM2596 Buck Converter, Requested by: Carl Caraos): Status: \'borrowed\' → \'pending\'', 1, '2025-06-24 03:18:40', 'read', NULL, NULL),
(183, NULL, 'Reservation updated (ID 128, Code RSV-685A12126156B, Product: Raspberry Pi 4 Model B, Requested by: Ragnhild): Checked By: \'Carl\' → \'Ragnhild\'; Quantity: 1 → 10', 1, '2025-06-24 03:18:40', 'read', NULL, NULL),
(184, NULL, 'Reservation updated (ID 127, Code RSV-685A0BB13FC9F, Product: 5V Relay Module, Requested by: Carl Caraos): Checked By: \'Carl\' → \'Ragnhild\'; Quantity: 1 → 10', 1, '2025-06-24 03:18:40', 'read', NULL, NULL),
(185, NULL, 'Reservation updated (ID 129, Code RSV-685A13CA9415C, Product: LM2596 Buck Converter, Requested by: Carl Caraos): Status: \'pending\' → \'borrowed\'', 1, '2025-06-24 03:26:15', 'read', NULL, NULL),
(186, NULL, 'Reservation updated (ID 129, Code RSV-685A13CA9415C, Product: LM2596 Buck Converter, Requested by: Carl Caraos): Status: \'borrowed\' → \'pending\'', 1, '2025-06-24 03:26:40', 'read', NULL, NULL),
(187, NULL, 'Reservations moved to borrowed: Code RSV-685A13CA9415C, Product: LM2596 Buck Converter, Requested by: Carl Caraos; Code RSV-685A12126156B, Product: Raspberry Pi 4 Model B, Requested by: Ragnhild; Code RSV-685A0BB13FC9F, Product: 5V Relay Module, Requested by: Carl Caraos', 1, '2025-06-24 03:33:36', 'read', NULL, NULL),
(188, NULL, 'Reservations deleted: Code RSV-685A0BB13FC9F, Product: 5V Relay Module, Requested by: Carl Caraos; Code RSV-685A12126156B, Product: Raspberry Pi 4 Model B, Requested by: Ragnhild; Code RSV-685A13CA9415C, Product: LM2596 Buck Converter, Requested by: Carl Caraos', 1, '2025-06-24 03:39:20', 'read', NULL, NULL),
(189, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A1DFE07AB0\'>#RSV-685A1DFE07AB0</a> by Carl Caraos for 2025-06-24.', 1, '2025-06-24 03:39:42', 'read', 'RSV-685A1DFE07AB0', 37),
(190, NULL, 'Reservations moved to borrowed: Code RSV-685A1DFE07AB0, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos', 1, '2025-06-24 03:39:52', 'read', NULL, NULL),
(191, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A1F4E1444E\'>#RSV-685A1F4E1444E</a> by Carl Caraos for 2025-06-24.', 1, '2025-06-24 03:45:18', 'read', 'RSV-685A1F4E1444E', 40),
(192, NULL, 'Reservation updated (ID 131, Code RSV-685A1F4E1444E, Product: L298N Motor Driver, Requested by: Carl Caraos): Status: \'pending\' → \'approved\'', 1, '2025-06-24 03:52:03', 'read', NULL, NULL),
(193, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A333A7B81A\'>#RSV-685A333A7B81A</a> by Ragnhild for 2025-06-24.', 1, '2025-06-24 05:10:18', 'read', 'RSV-685A333A7B81A', 39),
(194, NULL, 'Reservation updated (ID 132, Code RSV-685A333A7B81A, Product: LM2596 Buck Converter, Requested by: Ragnhild): Checked By: \'\' → \'Array\'; Quantity: 10 → Array', 1, '2025-06-24 05:21:39', 'read', NULL, NULL),
(195, NULL, 'Reservation updated (ID 131, Code RSV-685A1F4E1444E, Product: L298N Motor Driver, Requested by: Carl Caraos): Status: \'approved\' → \'pending\'; Checked By: \'Ragnhild\' → \'Array\'; Quantity: 20 → Array', 1, '2025-06-24 05:21:39', 'read', NULL, NULL),
(196, NULL, 'Reservation updated (ID 132, Code RSV-685A333A7B81A, Product: LM2596 Buck Converter, Requested by: Ragnhild): Checked By: \'\' → \'Array\'; Quantity: 10 → Array', 1, '2025-06-24 05:21:40', 'read', NULL, NULL),
(197, NULL, 'Reservation updated (ID 131, Code RSV-685A1F4E1444E, Product: L298N Motor Driver, Requested by: Carl Caraos): Checked By: \'Ragnhild\' → \'Array\'; Quantity: 20 → Array', 1, '2025-06-24 05:21:40', 'read', NULL, NULL),
(198, NULL, 'Reservation updated (ID 132, Code RSV-685A333A7B81A, Product: LM2596 Buck Converter, Requested by: Ragnhild): Status: \'pending\' → \'approved\'; Checked By: \'\' → \'Array\'; Quantity: 10 → Array', 1, '2025-06-24 05:22:24', 'read', NULL, NULL),
(199, NULL, 'Reservation updated (ID 132, Code RSV-685A333A7B81A, Product: LM2596 Buck Converter, Requested by: Ragnhild): Checked By: \'\' → \'Array\'; Quantity: 10 → Array', 1, '2025-06-24 05:22:26', 'read', NULL, NULL),
(200, NULL, 'Reservation updated (ID 132, Code RSV-685A333A7B81A, Product: LM2596 Buck Converter, Requested by: Ragnhild): Checked By: \'\' → \'Ragnhild\'; Quantity: 10 → 1020', 1, '2025-06-24 05:27:37', 'read', NULL, NULL),
(201, NULL, 'Reservation updated (ID 132, Code RSV-685A333A7B81A, Product: LM2596 Buck Converter, Requested by: Ragnhild): Status: \'approved\' → \'pending\'', 1, '2025-06-24 05:30:47', 'read', NULL, NULL),
(202, NULL, 'Reservation updated (ID 131, Code RSV-685A1F4E1444E, Product: L298N Motor Driver, Requested by: Carl Caraos): Quantity: 20 → 1020', 1, '2025-06-24 05:30:47', 'read', NULL, NULL),
(203, NULL, 'Reservation updated (ID 132, Code RSV-685A333A7B81A, Product: LM2596 Buck Converter, Requested by: Ragnhild): Status: \'pending\' → \'approved\'', 1, '2025-06-24 05:35:10', 'read', NULL, NULL),
(204, NULL, 'Reservation updated (ID 131, Code RSV-685A1F4E1444E, Product: L298N Motor Driver, Requested by: Carl Caraos): Status: \'pending\' → \'approved\'', 1, '2025-06-24 05:35:10', 'read', NULL, NULL),
(205, NULL, 'Reservation updated (ID 132, Code RSV-685A333A7B81A, Product: LM2596 Buck Converter, Requested by: Ragnhild): Status: \'approved\' → \'borrowed\'', 1, '2025-06-24 05:35:17', 'read', NULL, NULL),
(206, NULL, 'Reservation updated (ID 131, Code RSV-685A1F4E1444E, Product: L298N Motor Driver, Requested by: Carl Caraos): Status: \'approved\' → \'borrowed\'', 1, '2025-06-24 05:35:17', 'read', NULL, NULL),
(207, NULL, 'Reservation updated (ID 132, Code RSV-685A333A7B81A, Product: LM2596 Buck Converter, Requested by: Ragnhild): Status: \'borrowed\' → \'pending\'', 1, '2025-06-24 05:37:26', 'read', NULL, NULL),
(208, NULL, 'Reservation updated (ID 131, Code RSV-685A1F4E1444E, Product: L298N Motor Driver, Requested by: Carl Caraos): Status: \'borrowed\' → \'pending\'', 1, '2025-06-24 05:37:26', 'read', NULL, NULL),
(209, NULL, 'Reservation updated (ID 132, Code RSV-685A333A7B81A, Product: LM2596 Buck Converter, Requested by: Ragnhild): Status: \'pending\' → \'approved\'', 1, '2025-06-24 05:39:24', 'read', NULL, NULL),
(210, NULL, 'Reservation updated (ID 131, Code RSV-685A1F4E1444E, Product: L298N Motor Driver, Requested by: Carl Caraos): Status: \'pending\' → \'approved\'', 1, '2025-06-24 05:39:24', 'read', NULL, NULL),
(211, NULL, 'Reservation updated (ID 132, Code RSV-685A333A7B81A, Product: LM2596 Buck Converter, Requested by: Ragnhild): Status: \'approved\' → \'borrowed\'', 1, '2025-06-24 05:39:37', 'read', NULL, NULL),
(212, NULL, 'Reservation updated (ID 131, Code RSV-685A1F4E1444E, Product: L298N Motor Driver, Requested by: Carl Caraos): Status: \'approved\' → \'borrowed\'', 1, '2025-06-24 05:39:37', 'read', NULL, NULL),
(213, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A3B108DE16\'>#RSV-685A3B108DE16</a> by Carl Caraos for 2025-06-24.', 1, '2025-06-24 05:43:44', 'read', 'RSV-685A3B108DE16', 35),
(214, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A3B108DE16\'>#RSV-685A3B108DE16</a> by Carl Caraos for 2025-06-24.', 1, '2025-06-24 05:43:44', 'read', 'RSV-685A3B108DE16', 34),
(215, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A3B108DE16\'>#RSV-685A3B108DE16</a> by Carl Caraos for 2025-06-24.', 1, '2025-06-24 05:43:44', 'read', 'RSV-685A3B108DE16', 39),
(216, NULL, '[AUDIT] Bulk edit: Reservation updated (ID 135, Code RSV-685A3B108DE16, Product: LM2596 Buck Converter, Requested by: Carl Caraos): Status: \'pending\' → \'approved\' | Reservation updated (ID 134, Code RSV-685A3B108DE16, Product: Breadboard 830 Tie-points, Requested by: Carl Caraos): Status: \'pending\' → \'approved\'; Quantity: 9 → 10 | Reservation updated (ID 133, Code RSV-685A3B108DE16, Product: Jumper Wires (M-M, M-F, F-F), Requested by: Carl Caraos): Status: \'pending\' → \'approved\'', 1, '2025-06-24 05:43:55', 'read', NULL, NULL),
(217, NULL, 'Reservation updated (ID 135, Code RSV-685A3B108DE16, Product: LM2596 Buck Converter, Requested by: Carl Caraos): Status: \'pending\' → \'approved\'', 1, '2025-06-24 05:43:56', 'read', NULL, NULL),
(218, NULL, 'Reservation updated (ID 134, Code RSV-685A3B108DE16, Product: Breadboard 830 Tie-points, Requested by: Carl Caraos): Status: \'pending\' → \'approved\'; Quantity: 9 → 10', 1, '2025-06-24 05:43:56', 'read', NULL, NULL),
(219, NULL, 'Reservation updated (ID 133, Code RSV-685A3B108DE16, Product: Jumper Wires (M-M, M-F, F-F), Requested by: Carl Caraos): Status: \'pending\' → \'approved\'', 1, '2025-06-24 05:43:56', 'read', NULL, NULL),
(220, NULL, 'Reservation status changed: Code RSV-685A3B108DE16, Product: LM2596 Buck Converter, Requested by: Carl Caraos from \'approved\' to \'borrowed\'', 1, '2025-06-24 05:44:10', 'read', 'RSV-685A3B108DE16', 39),
(221, NULL, '[AUDIT] Reservation moved to borrowed_items: Code RSV-685A3B108DE16, Product: LM2596 Buck Converter, Requested by: Carl Caraos, Qty: 10, Checked by: Ragnhild', 1, '2025-06-24 05:44:10', 'read', 'RSV-685A3B108DE16', 39),
(222, NULL, '[AUDIT] Bulk edit: Reservation updated (ID 135, Code RSV-685A3B108DE16, Product: LM2596 Buck Converter, Requested by: Carl Caraos): Status: \'approved\' → \'borrowed\'', 1, '2025-06-24 05:44:10', 'read', NULL, NULL),
(223, NULL, 'Reservation updated (ID 135, Code RSV-685A3B108DE16, Product: LM2596 Buck Converter, Requested by: Carl Caraos): Status: \'approved\' → \'borrowed\'', 1, '2025-06-24 05:44:10', 'read', NULL, NULL),
(224, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A3B5CB13B1\'>#RSV-685A3B5CB13B1</a> by Carl Caraos for 2025-06-24.', 1, '2025-06-24 05:45:00', 'read', 'RSV-685A3B5CB13B1', 38),
(225, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A3B5CB13B1\'>#RSV-685A3B5CB13B1</a> by Carl Caraos for 2025-06-24.', 1, '2025-06-24 05:45:00', 'read', 'RSV-685A3B5CB13B1', 35),
(226, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A404C70D25\'>#RSV-685A404C70D25</a> by Carl Caraos for 2025-06-24.', 1, '2025-06-24 06:06:04', 'read', 'RSV-685A404C70D25', 36),
(227, NULL, 'Reservation status changed: Code RSV-685A404C70D25, Product: ESP32 Dev Board, Requested by: Carl Caraos from \'pending\' to \'borrowed\'', 1, '2025-06-24 06:06:18', 'read', 'RSV-685A404C70D25', 36),
(228, NULL, '[AUDIT] Reservation moved to borrowed_items: Code RSV-685A404C70D25, Product: ESP32 Dev Board, Requested by: Carl Caraos, Qty: 10, Checked by: Ragnhild', 1, '2025-06-24 06:06:18', 'read', 'RSV-685A404C70D25', 36),
(229, NULL, '[AUDIT] Bulk edit: Reservation updated (ID 138, Code RSV-685A404C70D25, Product: ESP32 Dev Board, Requested by: Carl Caraos): Status: \'pending\' → \'borrowed\'', 1, '2025-06-24 06:06:18', 'read', NULL, NULL),
(230, NULL, 'Reservation updated (ID 138, Code RSV-685A404C70D25, Product: ESP32 Dev Board, Requested by: Carl Caraos): Status: \'pending\' → \'borrowed\'', 1, '2025-06-24 06:06:18', 'read', NULL, NULL),
(231, NULL, 'Reservations deleted: Code RSV-685A3B108DE16, Product: Jumper Wires (M-M, M-F, F-F), Requested by: Carl Caraos; Code RSV-685A3B108DE16, Product: Breadboard 830 Tie-points, Requested by: Carl Caraos; Code RSV-685A3B5CB13B1, Product: DHT11 Temperature Sensor, Requested by: Carl Caraos; Code RSV-685A3B5CB13B1, Product: Jumper Wires (M-M, M-F, F-F), Requested by: Carl Caraos', 1, '2025-06-24 06:10:14', 'read', NULL, NULL),
(232, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A4163554D6\'>#RSV-685A4163554D6</a> by Carl Caraos for 2025-06-24.', 1, '2025-06-24 06:10:43', 'read', 'RSV-685A4163554D6', 37),
(233, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A41F1C0B91\'>#RSV-685A41F1C0B91</a> by Carl Caraos for 2025-06-24.', 1, '2025-06-24 06:13:05', 'read', 'RSV-685A41F1C0B91', 37),
(234, NULL, 'Reservations deleted: Code RSV-685A4163554D6, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos; Code RSV-685A41F1C0B91, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos', 1, '2025-06-24 06:13:25', 'read', NULL, NULL),
(235, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A424C8B400\'>#RSV-685A424C8B400</a> by Carl Caraos for 2025-06-24.', 1, '2025-06-24 06:14:36', 'read', 'RSV-685A424C8B400', 36),
(236, NULL, 'Reservation status changed: Code RSV-685A424C8B400, Product: ESP32 Dev Board, Requested by: Carl Caraos from \'pending\' to \'borrowed\'', 1, '2025-06-24 06:14:47', 'read', 'RSV-685A424C8B400', 36),
(237, NULL, '[AUDIT] Reservation moved to borrowed_items: Code RSV-685A424C8B400, Product: ESP32 Dev Board, Requested by: Carl Caraos, Qty: 5, Checked by: Ragnhild', 1, '2025-06-24 06:14:47', 'read', 'RSV-685A424C8B400', 36),
(238, NULL, '[AUDIT] Bulk edit: Reservation updated (ID 141, Code RSV-685A424C8B400, Product: ESP32 Dev Board, Requested by: Carl Caraos): Status: \'pending\' → \'borrowed\'', 1, '2025-06-24 06:14:47', 'read', NULL, NULL),
(239, NULL, 'Reservation updated (ID 141, Code RSV-685A424C8B400, Product: ESP32 Dev Board, Requested by: Carl Caraos): Status: \'pending\' → \'borrowed\'', 1, '2025-06-24 06:14:47', 'read', NULL, NULL),
(240, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A431C37AF4\'>#RSV-685A431C37AF4</a> by Carl Caraos for 2025-06-24.', 1, '2025-06-24 06:18:04', 'read', 'RSV-685A431C37AF4', 36),
(241, NULL, '[AUDIT] Bulk edit: Reservation updated (ID 142, Code RSV-685A431C37AF4, Product: ESP32 Dev Board, Requested by: Carl Caraos): Status: \'borrowed\' → \'pending\'', 1, '2025-06-24 06:21:43', 'read', NULL, NULL),
(242, NULL, 'Reservation updated (ID 142, Code RSV-685A431C37AF4, Product: ESP32 Dev Board, Requested by: Carl Caraos): Status: \'borrowed\' → \'pending\'', 1, '2025-06-24 06:21:43', 'read', NULL, NULL),
(243, NULL, '[AUDIT] Bulk edit: Reservation updated (ID 142, Code RSV-685A431C37AF4, Product: ESP32 Dev Board, Requested by: Carl Caraos): Status: \'borrowed\' → \'approved\'', 1, '2025-06-24 06:25:42', 'read', NULL, NULL),
(244, NULL, 'Reservation updated (ID 142, Code RSV-685A431C37AF4, Product: ESP32 Dev Board, Requested by: Carl Caraos): Status: \'borrowed\' → \'approved\'', 1, '2025-06-24 06:25:42', 'read', NULL, NULL),
(245, NULL, 'Reservations deleted: Code RSV-685A431C37AF4, Product: ESP32 Dev Board, Requested by: Carl Caraos', 1, '2025-06-24 06:26:12', 'read', NULL, NULL),
(246, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A4510A0E4A\'>#RSV-685A4510A0E4A</a> by Carl Caraos for 2025-06-24.', 1, '2025-06-24 06:26:24', 'read', 'RSV-685A4510A0E4A', 36),
(247, NULL, '[AUDIT] Bulk edit: Reservation updated (ID 143, Code RSV-685A4510A0E4A, Product: ESP32 Dev Board, Requested by: Carl Caraos): Status: \'borrowed\' → \'pending\'', 1, '2025-06-24 06:31:17', 'read', NULL, NULL),
(248, NULL, 'Reservation updated (ID 143, Code RSV-685A4510A0E4A, Product: ESP32 Dev Board, Requested by: Carl Caraos): Status: \'borrowed\' → \'pending\'', 1, '2025-06-24 06:31:17', 'read', NULL, NULL),
(249, NULL, '[AUDIT] Bulk edit: Reservation updated (ID 143, Code RSV-685A4510A0E4A, Product: ESP32 Dev Board, Requested by: Carl Caraos): Status: \'borrowed\' → \'pending\'', 1, '2025-06-24 06:36:29', 'read', NULL, NULL),
(250, NULL, 'Reservation updated (ID 143, Code RSV-685A4510A0E4A, Product: ESP32 Dev Board, Requested by: Carl Caraos): Status: \'borrowed\' → \'pending\'', 1, '2025-06-24 06:36:29', 'read', NULL, NULL),
(251, NULL, '[AUDIT] Bulk edit: Reservation updated (ID 143, Code RSV-685A4510A0E4A, Product: ESP32 Dev Board, Requested by: Carl Caraos): Status: \'borrowed\' → \'approved\'', 1, '2025-06-24 06:38:56', 'read', NULL, NULL),
(252, NULL, 'Reservation updated (ID 143, Code RSV-685A4510A0E4A, Product: ESP32 Dev Board, Requested by: Carl Caraos): Status: \'borrowed\' → \'approved\'', 1, '2025-06-24 06:38:56', 'read', NULL, NULL),
(253, NULL, '[AUDIT] Bulk edit: Reservation updated (ID 143, Code RSV-685A4510A0E4A, Product: ESP32 Dev Board, Requested by: Carl Caraos): Status: \'borrowed\' → \'pending\'', 1, '2025-06-24 06:39:39', 'read', NULL, NULL),
(254, NULL, 'Reservation updated (ID 143, Code RSV-685A4510A0E4A, Product: ESP32 Dev Board, Requested by: Carl Caraos): Status: \'borrowed\' → \'pending\'', 1, '2025-06-24 06:39:39', 'read', NULL, NULL),
(255, NULL, '[AUDIT] Bulk edit: Reservation updated (ID 143, Code RSV-685A4510A0E4A, Product: ESP32 Dev Board, Requested by: Carl Caraos): Status: \'borrowed\' → \'pending\'; Quantity: 7 → 4', 1, '2025-06-24 06:43:02', 'read', NULL, NULL),
(256, NULL, 'Reservation updated (ID 143, Code RSV-685A4510A0E4A, Product: ESP32 Dev Board, Requested by: Carl Caraos): Status: \'borrowed\' → \'pending\'; Quantity: 7 → 4', 1, '2025-06-24 06:43:02', 'read', NULL, NULL),
(257, NULL, '[AUDIT] Bulk edit: Reservation updated (ID 143, Code RSV-685A4510A0E4A, Product: ESP32 Dev Board, Requested by: Carl Caraos): Status: \'borrowed\' → \'approved\'', 1, '2025-06-24 06:43:27', 'read', NULL, NULL),
(258, NULL, 'Reservation updated (ID 143, Code RSV-685A4510A0E4A, Product: ESP32 Dev Board, Requested by: Carl Caraos): Status: \'borrowed\' → \'approved\'', 1, '2025-06-24 06:43:27', 'read', NULL, NULL),
(259, NULL, '[AUDIT] Bulk edit: Reservation updated (ID 143, Code RSV-685A4510A0E4A, Product: ESP32 Dev Board, Requested by: Carl Caraos): Quantity: 4 → 3', 1, '2025-06-24 06:44:45', 'read', NULL, NULL),
(260, NULL, 'Reservation updated (ID 143, Code RSV-685A4510A0E4A, Product: ESP32 Dev Board, Requested by: Carl Caraos): Quantity: 4 → 3', 1, '2025-06-24 06:44:45', 'read', NULL, NULL),
(261, NULL, '[AUDIT] Bulk edit: Reservation updated (ID 143, Code RSV-685A4510A0E4A, Product: ESP32 Dev Board, Requested by: Carl Caraos): Status: \'borrowed\' → \'approved\'', 1, '2025-06-24 06:45:17', 'read', NULL, NULL),
(262, NULL, 'Reservation updated (ID 143, Code RSV-685A4510A0E4A, Product: ESP32 Dev Board, Requested by: Carl Caraos): Status: \'borrowed\' → \'approved\'', 1, '2025-06-24 06:45:17', 'read', NULL, NULL),
(263, NULL, '[AUDIT] Bulk edit: Reservation updated (ID 143, Code RSV-685A4510A0E4A, Product: ESP32 Dev Board, Requested by: Carl Caraos): Quantity: 5 → 4', 1, '2025-06-24 06:45:33', 'read', NULL, NULL);
INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`, `status`, `reservation_code`, `product_id`) VALUES
(264, NULL, 'Reservation updated (ID 143, Code RSV-685A4510A0E4A, Product: ESP32 Dev Board, Requested by: Carl Caraos): Quantity: 5 → 4', 1, '2025-06-24 06:45:33', 'read', NULL, NULL),
(265, NULL, 'Reservations deleted: Code RSV-685A4510A0E4A, Product: ESP32 Dev Board, Requested by: Carl Caraos', 1, '2025-06-24 06:46:25', 'read', NULL, NULL),
(266, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A49D5352D4\'>#RSV-685A49D5352D4</a> by Carl Caraos for 2025-06-24.', 1, '2025-06-24 06:46:45', 'read', 'RSV-685A49D5352D4', 39),
(267, NULL, 'Reservations deleted: Code RSV-685A49D5352D4, Product: LM2596 Buck Converter, Requested by: Carl Caraos', 1, '2025-06-24 06:47:30', 'read', NULL, NULL),
(268, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A4A642D2DE\'>#RSV-685A4A642D2DE</a> by Carl Caraos for 2025-06-24.', 1, '2025-06-24 06:49:08', 'read', 'RSV-685A4A642D2DE', 37),
(269, NULL, '[AUDIT] Bulk edit: Reservation updated (ID 145, Code RSV-685A4A642D2DE, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos): Status: \'borrowed\' → \'pending\'', 1, '2025-06-24 06:51:23', 'read', NULL, NULL),
(270, NULL, 'Reservation updated (ID 145, Code RSV-685A4A642D2DE, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos): Status: \'borrowed\' → \'pending\'', 1, '2025-06-24 06:51:23', 'read', NULL, NULL),
(271, NULL, '[AUDIT] Bulk edit: Reservation updated (ID 145, Code RSV-685A4A642D2DE, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos): Status: \'borrowed\' → \'approved\'', 1, '2025-06-24 06:55:22', 'read', NULL, NULL),
(272, NULL, 'Reservation updated (ID 145, Code RSV-685A4A642D2DE, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos): Status: \'borrowed\' → \'approved\'', 1, '2025-06-24 06:55:22', 'read', NULL, NULL),
(273, NULL, '[AUDIT] Bulk edit: Reservation updated (ID 145, Code RSV-685A4A642D2DE, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos): Status: \'borrowed\' → \'pending\'', 1, '2025-06-24 06:59:29', 'read', NULL, NULL),
(274, NULL, 'Reservation updated (ID 145, Code RSV-685A4A642D2DE, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos): Status: \'borrowed\' → \'pending\'', 1, '2025-06-24 06:59:29', 'read', NULL, NULL),
(275, NULL, 'Reservation status changed: Code RSV-685A4A642D2DE, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos from \'pending\' to \'borrowed\'', 1, '2025-06-24 06:59:37', 'read', 'RSV-685A4A642D2DE', 37),
(276, NULL, '[AUDIT] Reservation moved to borrowed_items: Code RSV-685A4A642D2DE, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos, Qty: 5, Checked by: Ragnhild', 1, '2025-06-24 06:59:37', 'read', 'RSV-685A4A642D2DE', 37),
(277, NULL, '[AUDIT] Bulk edit: Reservation updated (ID 145, Code RSV-685A4A642D2DE, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos): Status: \'pending\' → \'borrowed\'', 1, '2025-06-24 06:59:37', 'read', NULL, NULL),
(278, NULL, 'Reservation updated (ID 145, Code RSV-685A4A642D2DE, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos): Status: \'pending\' → \'borrowed\'', 1, '2025-06-24 06:59:37', 'read', NULL, NULL),
(279, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A4DC6CA64F\'>#RSV-685A4DC6CA64F</a> by Carl Caraos for 2025-06-24.', 1, '2025-06-24 07:03:34', 'read', 'RSV-685A4DC6CA64F', 37),
(280, NULL, '[AUDIT] Bulk edit: Reservation updated (ID 146, Code RSV-685A4DC6CA64F, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos): Status: \'pending\' → \'borrowed\'', 1, '2025-06-24 07:03:44', 'read', NULL, NULL),
(281, NULL, 'Reservation updated (ID 146, Code RSV-685A4DC6CA64F, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos): Status: \'pending\' → \'borrowed\'', 1, '2025-06-24 07:03:44', 'read', NULL, NULL),
(282, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A4E9DAC0BE\'>#RSV-685A4E9DAC0BE</a> by Carl Caraos for 2025-06-24.', 1, '2025-06-24 07:07:09', 'read', 'RSV-685A4E9DAC0BE', 37),
(283, NULL, '[AUDIT] Bulk edit: Reservation updated (ID 147, Code RSV-685A4E9DAC0BE, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos): Status: \'pending\' → \'borrowed\'', 1, '2025-06-24 07:07:19', 'read', NULL, NULL),
(284, NULL, 'Reservation updated (ID 147, Code RSV-685A4E9DAC0BE, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos): Status: \'pending\' → \'borrowed\'', 1, '2025-06-24 07:07:19', 'read', NULL, NULL),
(285, NULL, '[AUDIT] Bulk edit: Reservation updated (ID 147, Code RSV-685A4E9DAC0BE, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos): Status: \'borrowed\' → \'approved\'', 1, '2025-06-24 07:08:40', 'read', NULL, NULL),
(286, NULL, 'Reservation updated (ID 147, Code RSV-685A4E9DAC0BE, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos): Status: \'borrowed\' → \'approved\'', 1, '2025-06-24 07:08:40', 'read', NULL, NULL),
(287, NULL, '[AUDIT] Bulk edit: Reservation updated (ID 147, Code RSV-685A4E9DAC0BE, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos): Status: \'approved\' → \'borrowed\'', 1, '2025-06-24 07:08:51', 'read', NULL, NULL),
(288, NULL, 'Reservation updated (ID 147, Code RSV-685A4E9DAC0BE, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos): Status: \'approved\' → \'borrowed\'', 1, '2025-06-24 07:08:51', 'read', NULL, NULL),
(289, NULL, '[AUDIT] Bulk edit: Reservation updated (ID 147, Code RSV-685A4E9DAC0BE, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos): Status: \'borrowed\' → \'pending\'', 1, '2025-06-24 07:12:16', 'read', NULL, NULL),
(290, NULL, 'Reservation updated (ID 147, Code RSV-685A4E9DAC0BE, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos): Status: \'borrowed\' → \'pending\'', 1, '2025-06-24 07:12:16', 'read', NULL, NULL),
(291, NULL, '[AUDIT] Bulk edit: Reservation updated (ID 147, Code RSV-685A4E9DAC0BE, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos): Status: \'pending\' → \'borrowed\'', 1, '2025-06-24 07:12:24', 'read', NULL, NULL),
(292, NULL, 'Reservation updated (ID 147, Code RSV-685A4E9DAC0BE, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos): Status: \'pending\' → \'borrowed\'', 1, '2025-06-24 07:12:24', 'read', NULL, NULL),
(293, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A520833789\'>#RSV-685A520833789</a> by Carl Caraos for 2025-06-24.', 1, '2025-06-24 07:21:44', 'read', 'RSV-685A520833789', 31),
(294, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A520833789\'>#RSV-685A520833789</a> by Carl Caraos for 2025-06-24.', 1, '2025-06-24 07:21:44', 'read', 'RSV-685A520833789', 35),
(295, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A520833789\'>#RSV-685A520833789</a> by Carl Caraos for 2025-06-24.', 1, '2025-06-24 07:21:44', 'read', 'RSV-685A520833789', 39),
(296, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A520833789\'>#RSV-685A520833789</a> by Carl Caraos for 2025-06-24.', 1, '2025-06-24 07:21:44', 'read', 'RSV-685A520833789', 38),
(297, NULL, 'Reservation status changed: Code RSV-685A520833789, Product: DHT11 Temperature Sensor, Requested by: Carl Caraos from \'pending\' to \'borrowed\'', 1, '2025-06-24 07:41:20', 'read', 'RSV-685A520833789', 38),
(298, NULL, '[AUDIT] Reservation moved to borrowed_items: Code RSV-685A520833789, Product: DHT11 Temperature Sensor, Requested by: Carl Caraos, Qty: 10, Checked by: Ragnhild', 1, '2025-06-24 07:41:20', 'read', 'RSV-685A520833789', 38),
(299, NULL, '[AUDIT] Bulk edit: Reservation updated (ID 151, Code RSV-685A520833789, Product: DHT11 Temperature Sensor, Requested by: Carl Caraos): Status: \'pending\' → \'borrowed\'', 1, '2025-06-24 07:41:20', 'read', NULL, NULL),
(300, NULL, 'Reservation updated (ID 151, Code RSV-685A520833789, Product: DHT11 Temperature Sensor, Requested by: Carl Caraos): Status: \'pending\' → \'borrowed\'', 1, '2025-06-24 07:41:20', 'read', NULL, NULL),
(301, NULL, 'You have logged in successfully.', 1, '2025-06-24 07:56:50', 'read', NULL, NULL),
(302, NULL, 'Reservation status changed: Code RSV-685A520833789, Product: LM2596 Buck Converter, Requested by: Carl Caraos from \'pending\' to \'borrowed\'', 1, '2025-06-24 07:57:18', 'read', 'RSV-685A520833789', 39),
(303, NULL, '[AUDIT] Reservation moved to borrowed_items: Code RSV-685A520833789, Product: LM2596 Buck Converter, Requested by: Carl Caraos, Qty: 10, Checked by: Ragnhild', 1, '2025-06-24 07:57:18', 'read', 'RSV-685A520833789', 39),
(304, NULL, '[AUDIT] Bulk edit: Reservation updated (ID 150, Code RSV-685A520833789, Product: LM2596 Buck Converter, Requested by: Carl Caraos): Status: \'pending\' → \'borrowed\'', 1, '2025-06-24 07:57:18', 'read', NULL, NULL),
(305, NULL, 'Reservation updated (ID 150, Code RSV-685A520833789, Product: LM2596 Buck Converter, Requested by: Carl Caraos): Status: \'pending\' → \'borrowed\'', 1, '2025-06-24 07:57:18', 'read', NULL, NULL),
(306, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A5B1B6ACAB\'>#RSV-685A5B1B6ACAB</a> by Carl Caraos for 2025-06-24.', 1, '2025-06-24 08:00:27', 'read', 'RSV-685A5B1B6ACAB', 41),
(307, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A5B1B6ACAB\'>#RSV-685A5B1B6ACAB</a> by Carl Caraos for 2025-06-24.', 1, '2025-06-24 08:00:27', 'read', 'RSV-685A5B1B6ACAB', 40),
(308, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A5B1B6ACAB\'>#RSV-685A5B1B6ACAB</a> by Carl Caraos for 2025-06-24.', 1, '2025-06-24 08:00:27', 'read', 'RSV-685A5B1B6ACAB', 42),
(309, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A5D5B0772D\'>#RSV-685A5D5B0772D</a> by Carl Caraos for 2025-06-24.', 1, '2025-06-24 08:10:03', 'read', 'RSV-685A5D5B0772D', 42),
(310, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A5F63ABDA9\'>#RSV-685A5F63ABDA9</a> by Carl Caraos for 2025-06-24.', 1, '2025-06-24 08:18:43', 'read', 'RSV-685A5F63ABDA9', 37),
(311, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685A5F7EEA086\'>#RSV-685A5F7EEA086</a> by Carl Caraos for 2025-06-24.', 1, '2025-06-24 08:19:10', 'read', 'RSV-685A5F7EEA086', 37),
(312, NULL, '[AUDIT] Bulk edit: Reservation updated (ID 157, Code RSV-685A5F7EEA086, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos): Status: \'pending\' → \'approved\' | Reservation updated (ID 156, Code RSV-685A5F63ABDA9, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos): Status: \'pending\' → \'approved\' | Reservation updated (ID 155, Code RSV-685A5D5B0772D, Product: OLED Display 0.96\", Requested by: Carl Caraos): Status: \'pending\' → \'approved\'; Quantity: 5 → 1 | Reservation updated (ID 153, Code RSV-685A5B1B6ACAB, Product: L298N Motor Driver, Requested by: Carl Caraos): Status: \'pending\' → \'approved\'; Quantity: 10 → 1 | Reservation updated (ID 152, Code RSV-685A5B1B6ACAB, Product: 5V Relay Module, Requested by: Carl Caraos): Status: \'pending\' → \'approved\'; Quantity: 10 → 1 | Reservation updated (ID 149, Code RSV-685A520833789, Product: Jumper Wires (M-M, M-F, F-F), Requested by: Carl Caraos): Status: \'pending\' → \'approved\'; Quantity: 10 → 1 | Reservation updated (ID 148, Code RSV-685A520833789, Product: Arduino Uno R3, Requested by: Carl Caraos): Status: \'pending\' → \'approved\'; Quantity: 10 → 1', 1, '2025-06-24 09:00:46', 'read', NULL, NULL),
(313, NULL, 'Reservation updated (ID 157, Code RSV-685A5F7EEA086, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos): Status: \'pending\' → \'approved\'', 1, '2025-06-24 09:00:46', 'read', NULL, NULL),
(314, NULL, 'Reservation updated (ID 156, Code RSV-685A5F63ABDA9, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos): Status: \'pending\' → \'approved\'', 1, '2025-06-24 09:00:46', 'read', NULL, NULL),
(315, NULL, 'Reservation updated (ID 155, Code RSV-685A5D5B0772D, Product: OLED Display 0.96\", Requested by: Carl Caraos): Status: \'pending\' → \'approved\'; Quantity: 5 → 1', 1, '2025-06-24 09:00:46', 'read', NULL, NULL),
(316, NULL, 'Reservation updated (ID 153, Code RSV-685A5B1B6ACAB, Product: L298N Motor Driver, Requested by: Carl Caraos): Status: \'pending\' → \'approved\'; Quantity: 10 → 1', 1, '2025-06-24 09:00:46', 'read', NULL, NULL),
(317, NULL, 'Reservation updated (ID 152, Code RSV-685A5B1B6ACAB, Product: 5V Relay Module, Requested by: Carl Caraos): Status: \'pending\' → \'approved\'; Quantity: 10 → 1', 1, '2025-06-24 09:00:46', 'read', NULL, NULL),
(318, NULL, 'Reservation updated (ID 149, Code RSV-685A520833789, Product: Jumper Wires (M-M, M-F, F-F), Requested by: Carl Caraos): Status: \'pending\' → \'approved\'; Quantity: 10 → 1', 1, '2025-06-24 09:00:46', 'read', NULL, NULL),
(319, NULL, 'Reservation updated (ID 148, Code RSV-685A520833789, Product: Arduino Uno R3, Requested by: Carl Caraos): Status: \'pending\' → \'approved\'; Quantity: 10 → 1', 1, '2025-06-24 09:00:46', 'read', NULL, NULL),
(320, NULL, 'You have logged in successfully.', 1, '2025-06-25 00:19:16', 'read', NULL, NULL),
(321, NULL, '[AUDIT] Bulk edit: Reservation updated (ID 157, Code RSV-685A5F7EEA086, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos): Status: \'approved\' → \'pending\' | Reservation updated (ID 156, Code RSV-685A5F63ABDA9, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos): Status: \'approved\' → \'pending\' | Reservation updated (ID 155, Code RSV-685A5D5B0772D, Product: OLED Display 0.96\", Requested by: Carl Caraos): Status: \'approved\' → \'pending\' | Reservation updated (ID 153, Code RSV-685A5B1B6ACAB, Product: L298N Motor Driver, Requested by: Carl Caraos): Status: \'approved\' → \'pending\' | Reservation updated (ID 152, Code RSV-685A5B1B6ACAB, Product: 5V Relay Module, Requested by: Carl Caraos): Status: \'approved\' → \'pending\' | Reservation updated (ID 149, Code RSV-685A520833789, Product: Jumper Wires (M-M, M-F, F-F), Requested by: Carl Caraos): Status: \'approved\' → \'pending\' | Reservation updated (ID 148, Code RSV-685A520833789, Product: Arduino Uno R3, Requested by: Carl Caraos): Status: \'approved\' → \'pending\'', 1, '2025-06-25 00:33:35', 'read', NULL, NULL),
(322, NULL, 'Reservation updated (ID 157, Code RSV-685A5F7EEA086, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos): Status: \'approved\' → \'pending\'', 1, '2025-06-25 00:33:35', 'read', NULL, NULL),
(323, NULL, 'Reservation updated (ID 156, Code RSV-685A5F63ABDA9, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos): Status: \'approved\' → \'pending\'', 1, '2025-06-25 00:33:35', 'read', NULL, NULL),
(324, NULL, 'Reservation updated (ID 155, Code RSV-685A5D5B0772D, Product: OLED Display 0.96\", Requested by: Carl Caraos): Status: \'approved\' → \'pending\'', 1, '2025-06-25 00:33:35', 'read', NULL, NULL),
(325, NULL, 'Reservation updated (ID 153, Code RSV-685A5B1B6ACAB, Product: L298N Motor Driver, Requested by: Carl Caraos): Status: \'approved\' → \'pending\'', 1, '2025-06-25 00:33:35', 'read', NULL, NULL),
(326, NULL, 'Reservation updated (ID 152, Code RSV-685A5B1B6ACAB, Product: 5V Relay Module, Requested by: Carl Caraos): Status: \'approved\' → \'pending\'', 1, '2025-06-25 00:33:35', 'read', NULL, NULL),
(327, NULL, 'Reservation updated (ID 149, Code RSV-685A520833789, Product: Jumper Wires (M-M, M-F, F-F), Requested by: Carl Caraos): Status: \'approved\' → \'pending\'', 1, '2025-06-25 00:33:35', 'read', NULL, NULL),
(328, NULL, 'Reservation updated (ID 148, Code RSV-685A520833789, Product: Arduino Uno R3, Requested by: Carl Caraos): Status: \'approved\' → \'pending\'', 1, '2025-06-25 00:33:35', 'read', NULL, NULL),
(329, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685B4A842C126\'>#RSV-685B4A842C126</a> by Carl Caraos for 2025-06-25.', 1, '2025-06-25 01:01:56', 'read', 'RSV-685B4A842C126', 38),
(330, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685B4A98752AE\'>#RSV-685B4A98752AE</a> by Carl Caraos for 2025-06-25.', 1, '2025-06-25 01:02:16', 'read', 'RSV-685B4A98752AE', 38),
(331, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685B52604134C\'>#RSV-685B52604134C</a> by Carl Caraos for 2025-06-25.', 1, '2025-06-25 01:35:28', 'read', 'RSV-685B52604134C', 39),
(332, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685B52A80B825\'>#RSV-685B52A80B825</a> by Carl Caraos for 2025-06-25.', 1, '2025-06-25 01:36:40', 'read', 'RSV-685B52A80B825', 39),
(333, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685B52A80B825\'>#RSV-685B52A80B825</a> by Carl Caraos for 2025-06-25.', 1, '2025-06-25 01:36:40', 'read', 'RSV-685B52A80B825', 37),
(334, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685B555840E5C\'>#RSV-685B555840E5C</a> by Carl Caraos for 2025-06-25.', 1, '2025-06-25 01:48:08', 'read', 'RSV-685B555840E5C', 37),
(335, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685B555840E5C\'>#RSV-685B555840E5C</a> by Carl Caraos for 2025-06-25.', 1, '2025-06-25 01:48:08', 'read', 'RSV-685B555840E5C', 39),
(336, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685B560137BCC\'>#RSV-685B560137BCC</a> by Carl Caraos for 2025-06-25.', 1, '2025-06-25 01:50:57', 'read', 'RSV-685B560137BCC', 36),
(337, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685B560137BCC\'>#RSV-685B560137BCC</a> by Carl Caraos for 2025-06-25.', 1, '2025-06-25 01:50:58', 'read', 'RSV-685B560137BCC', 38),
(338, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685B560137BCC\'>#RSV-685B560137BCC</a> by Carl Caraos for 2025-06-25.', 1, '2025-06-25 01:50:58', 'read', 'RSV-685B560137BCC', 39),
(339, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685B560137BCC\'>#RSV-685B560137BCC</a> by Carl Caraos for 2025-06-25.', 1, '2025-06-25 01:50:58', 'read', 'RSV-685B560137BCC', 37),
(340, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685B56B04F349\'>#RSV-685B56B04F349</a> by Carl Caraos for 2025-06-25.', 1, '2025-06-25 01:53:52', 'read', 'RSV-685B56B04F349', 37),
(341, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685B56B04F349\'>#RSV-685B56B04F349</a> by Carl Caraos for 2025-06-25.', 1, '2025-06-25 01:53:52', 'read', 'RSV-685B56B04F349', 40),
(342, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685B57620163F\'>#RSV-685B57620163F</a> by Carl Caraos for 2025-06-25.', 1, '2025-06-25 01:56:50', 'read', 'RSV-685B57620163F', 39),
(343, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685B57791DB26\'>#RSV-685B57791DB26</a> by Carl Caraos for 2025-06-25.', 1, '2025-06-25 01:57:13', 'read', 'RSV-685B57791DB26', 34),
(344, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685B5AB36EB25\'>#RSV-685B5AB36EB25</a> by Ragnhild for 2025-06-25.', 1, '2025-06-25 02:10:59', 'read', 'RSV-685B5AB36EB25', 35),
(345, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685B5AB36EB25\'>#RSV-685B5AB36EB25</a> by Ragnhild for 2025-06-25.', 1, '2025-06-25 02:10:59', 'read', 'RSV-685B5AB36EB25', 38),
(346, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685B5AB36EB25\'>#RSV-685B5AB36EB25</a> by Ragnhild for 2025-06-25.', 1, '2025-06-25 02:10:59', 'read', 'RSV-685B5AB36EB25', 37),
(347, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685B5AB36EB25\'>#RSV-685B5AB36EB25</a> by Ragnhild for 2025-06-25.', 1, '2025-06-25 02:10:59', 'read', 'RSV-685B5AB36EB25', 39),
(348, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685B5E4F52C79\'>#RSV-685B5E4F52C79</a> by Carl Caraos for 2025-06-25.', 1, '2025-06-25 02:26:23', 'read', 'RSV-685B5E4F52C79', 40),
(349, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685B5F0F0C56A\'>#RSV-685B5F0F0C56A</a> by Ragnhild for 2025-06-25.', 1, '2025-06-25 02:29:35', 'read', 'RSV-685B5F0F0C56A', 39),
(350, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685B6358A6A57\'>#RSV-685B6358A6A57</a> by Carl Caraos for 2025-06-25.', 1, '2025-06-25 02:47:52', 'read', 'RSV-685B6358A6A57', 41),
(351, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685B641A79B99\'>#RSV-685B641A79B99</a> by Carl Caraos for 2025-06-25.', 1, '2025-06-25 02:51:06', 'read', 'RSV-685B641A79B99', 2),
(352, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685C9953E1198\'>#RSV-685C9953E1198</a> by Carl Caraos for 2025-06-26.', 1, '2025-06-26 00:50:27', 'read', 'RSV-685C9953E1198', 39),
(353, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685C99E1EF4F5\'>#RSV-685C99E1EF4F5</a> by Carl Caraos for 2025-06-26.', 1, '2025-06-26 00:52:49', 'read', 'RSV-685C99E1EF4F5', 40),
(354, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685C9B82BFA7E\'>#RSV-685C9B82BFA7E</a> by Carl Caraos for 2025-06-26.', 1, '2025-06-26 00:59:46', 'read', 'RSV-685C9B82BFA7E', 36),
(355, NULL, 'You have logged in successfully.', 1, '2025-06-26 01:04:38', 'read', NULL, NULL),
(356, NULL, 'You have logged in successfully.', 1, '2025-06-26 02:14:06', 'read', NULL, NULL),
(357, NULL, 'You have logged in successfully.', 1, '2025-06-26 02:33:52', 'read', NULL, NULL),
(358, NULL, 'You have logged in successfully.', 1, '2025-06-26 03:21:03', 'read', NULL, NULL),
(359, NULL, 'You have logged in successfully.', 1, '2025-06-26 06:06:08', 'read', NULL, NULL),
(360, NULL, 'You have logged in successfully.', 1, '2025-06-26 06:06:45', 'read', NULL, NULL),
(361, NULL, 'You have logged in successfully.', 1, '2025-06-26 06:10:19', 'read', NULL, NULL),
(362, NULL, 'Categories deleted: ID 8: dad', 1, '2025-06-26 06:17:01', 'read', NULL, NULL),
(363, NULL, 'You have logged in successfully.', 1, '2025-06-26 06:21:56', 'read', NULL, NULL),
(364, NULL, 'You have logged in successfully.', 1, '2025-06-26 06:27:38', 'read', NULL, NULL),
(365, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685CECA0A7438\'>#RSV-685CECA0A7438</a> by Carl Caraos for 2025-06-26.', 1, '2025-06-26 06:45:52', 'read', 'RSV-685CECA0A7438', 37),
(366, NULL, 'You have logged in successfully.', 1, '2025-06-26 06:50:44', 'read', NULL, NULL),
(367, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685D07261EEC7\'>#RSV-685D07261EEC7</a> by Carl Caraos for 2025-06-26.', 1, '2025-06-26 08:39:02', 'read', 'RSV-685D07261EEC7', 39),
(368, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685D073D53F66\'>#RSV-685D073D53F66</a> by Ragnhild for 2025-06-26.', 1, '2025-06-26 08:39:25', 'read', 'RSV-685D073D53F66', 39),
(369, NULL, 'You have logged in successfully.', 1, '2025-06-26 08:45:10', 'read', NULL, NULL),
(370, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685D0C80A40C5\'>#RSV-685D0C80A40C5</a> by Carl Caraos for 2025-06-26.', 1, '2025-06-26 09:01:52', 'read', 'RSV-685D0C80A40C5', 31),
(371, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685D0C80A40C5\'>#RSV-685D0C80A40C5</a> by Carl Caraos for 2025-06-26.', 1, '2025-06-26 09:01:52', 'read', 'RSV-685D0C80A40C5', 35),
(372, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685D0C80A40C5\'>#RSV-685D0C80A40C5</a> by Carl Caraos for 2025-06-26.', 1, '2025-06-26 09:01:52', 'read', 'RSV-685D0C80A40C5', 34),
(373, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685D0C80A40C5\'>#RSV-685D0C80A40C5</a> by Carl Caraos for 2025-06-26.', 1, '2025-06-26 09:01:52', 'read', 'RSV-685D0C80A40C5', 36),
(374, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685D0C80A40C5\'>#RSV-685D0C80A40C5</a> by Carl Caraos for 2025-06-26.', 1, '2025-06-26 09:01:52', 'read', 'RSV-685D0C80A40C5', 41),
(375, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685D0CD7C4EFB\'>#RSV-685D0CD7C4EFB</a> by Carl Caraos for 2025-06-26.', 1, '2025-06-26 09:03:19', 'read', 'RSV-685D0CD7C4EFB', 45),
(376, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685D0CD7C4EFB\'>#RSV-685D0CD7C4EFB</a> by Carl Caraos for 2025-06-26.', 1, '2025-06-26 09:03:19', 'read', 'RSV-685D0CD7C4EFB', 34),
(377, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685D0CD7C4EFB\'>#RSV-685D0CD7C4EFB</a> by Carl Caraos for 2025-06-26.', 1, '2025-06-26 09:03:19', 'read', 'RSV-685D0CD7C4EFB', 36),
(378, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685D0CD7C4EFB\'>#RSV-685D0CD7C4EFB</a> by Carl Caraos for 2025-06-26.', 1, '2025-06-26 09:03:19', 'read', 'RSV-685D0CD7C4EFB', 38),
(379, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685D0CD7C4EFB\'>#RSV-685D0CD7C4EFB</a> by Carl Caraos for 2025-06-26.', 1, '2025-06-26 09:03:19', 'read', 'RSV-685D0CD7C4EFB', 41),
(380, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685D11BD0C325\'>#RSV-685D11BD0C325</a> by Carl Caraos for 2025-06-26.', 1, '2025-06-26 09:24:13', 'read', 'RSV-685D11BD0C325', 38),
(381, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685D11BD0C325\'>#RSV-685D11BD0C325</a> by Carl Caraos for 2025-06-26.', 1, '2025-06-26 09:24:13', 'read', 'RSV-685D11BD0C325', 39),
(382, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685D11BD0C325\'>#RSV-685D11BD0C325</a> by Carl Caraos for 2025-06-26.', 1, '2025-06-26 09:24:13', 'read', 'RSV-685D11BD0C325', 40),
(383, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685D11BD0C325\'>#RSV-685D11BD0C325</a> by Carl Caraos for 2025-06-26.', 1, '2025-06-26 09:24:13', 'read', 'RSV-685D11BD0C325', 36),
(384, NULL, 'You have logged in successfully.', 1, '2025-06-27 00:16:24', 'read', NULL, NULL),
(385, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685DE3F40FF65\'>#RSV-685DE3F40FF65</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 00:21:08', 'read', 'RSV-685DE3F40FF65', 36),
(386, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685DE7CF9E09A\'>#RSV-685DE7CF9E09A</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 00:37:35', 'read', 'RSV-685DE7CF9E09A', 38),
(387, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685DE7DD67560\'>#RSV-685DE7DD67560</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 00:37:49', 'read', 'RSV-685DE7DD67560', 39),
(388, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685DEEFFB8039\'>#RSV-685DEEFFB8039</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 01:08:15', 'read', 'RSV-685DEEFFB8039', 38),
(389, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685DEFDE0F32C\'>#RSV-685DEFDE0F32C</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 01:11:58', 'read', 'RSV-685DEFDE0F32C', 37),
(390, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685DF0C8B3263\'>#RSV-685DF0C8B3263</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 01:15:52', 'read', 'RSV-685DF0C8B3263', 36),
(391, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685DF0DC5B171\'>#RSV-685DF0DC5B171</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 01:16:12', 'read', 'RSV-685DF0DC5B171', 37),
(392, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685DF0ED4CA08\'>#RSV-685DF0ED4CA08</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 01:16:29', 'read', 'RSV-685DF0ED4CA08', 38),
(393, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685DF648D4D3E\'>#RSV-685DF648D4D3E</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 01:39:20', 'read', 'RSV-685DF648D4D3E', 36),
(394, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685DF81F10396\'>#RSV-685DF81F10396</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 01:47:11', 'read', 'RSV-685DF81F10396', 39),
(395, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685DFA8D9FED6\'>#RSV-685DFA8D9FED6</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 01:57:33', 'read', 'RSV-685DFA8D9FED6', 37),
(396, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685DFACD56A38\'>#RSV-685DFACD56A38</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 01:58:37', 'read', 'RSV-685DFACD56A38', 38),
(397, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685DFADD90E1F\'>#RSV-685DFADD90E1F</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 01:58:53', 'read', 'RSV-685DFADD90E1F', 38),
(398, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685DFAFC3048F\'>#RSV-685DFAFC3048F</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 01:59:24', 'read', 'RSV-685DFAFC3048F', 35),
(399, NULL, 'Product added (batch): sample', 1, '2025-06-27 02:55:26', 'read', NULL, 830772),
(400, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E0837BEE77\'>#RSV-685E0837BEE77</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 02:55:51', 'read', 'RSV-685E0837BEE77', 830772),
(401, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E0BF8A36A1\'>#RSV-685E0BF8A36A1</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 03:11:52', 'read', 'RSV-685E0BF8A36A1', 37),
(402, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E0C1093C1D\'>#RSV-685E0C1093C1D</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 03:12:16', 'read', 'RSV-685E0C1093C1D', 38),
(403, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E0C3923628\'>#RSV-685E0C3923628</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 03:12:57', 'read', 'RSV-685E0C3923628', 39),
(404, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E0C5D6CFA1\'>#RSV-685E0C5D6CFA1</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 03:13:33', 'read', 'RSV-685E0C5D6CFA1', 31),
(405, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E0C758E373\'>#RSV-685E0C758E373</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 03:13:57', 'read', 'RSV-685E0C758E373', 39),
(406, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E0C9187E8D\'>#RSV-685E0C9187E8D</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 03:14:25', 'read', 'RSV-685E0C9187E8D', 2),
(407, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E0CB461368\'>#RSV-685E0CB461368</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 03:15:00', 'read', 'RSV-685E0CB461368', 32),
(408, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E0CCB30985\'>#RSV-685E0CCB30985</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 03:15:23', 'read', 'RSV-685E0CCB30985', 31),
(409, NULL, 'You have logged in successfully.', 1, '2025-06-27 04:05:34', 'read', NULL, NULL),
(410, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E0CCB30985, Product: Arduino Uno R3, Requested by: Carl Caraos, Qty: 5, Checked by: Ragnhild', 1, '2025-06-27 04:17:38', 'read', 'RSV-685E0CCB30985', 31),
(411, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E0CB461368, Product: HC-SR04 Ultrasonic Sensor, Requested by: Carl Caraos, Qty: 5, Checked by: Ragnhild', 1, '2025-06-27 04:17:38', 'read', 'RSV-685E0CB461368', 32),
(412, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E0C9187E8D, Product: wifi 5g globe at home , Requested by: Carl Caraos, Qty: 5, Checked by: Ragnhild', 1, '2025-06-27 04:17:38', 'read', 'RSV-685E0C9187E8D', 2),
(413, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E1B8C95210\'>#RSV-685E1B8C95210</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 04:18:20', 'read', 'RSV-685E1B8C95210', 38),
(414, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E1B8C95210, Product: DHT11 Temperature Sensor, Requested by: Carl Caraos, Qty: 5, Checked by: Ragnhild', 1, '2025-06-27 04:18:32', 'read', 'RSV-685E1B8C95210', 38),
(415, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E1C407E35D\'>#RSV-685E1C407E35D</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 04:21:20', 'read', 'RSV-685E1C407E35D', 37),
(416, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E1C407E35D, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos, Qty: 10, Checked by: Ragnhild', 1, '2025-06-27 04:21:30', 'read', 'RSV-685E1C407E35D', 37),
(417, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685DFA8D9FED6, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos, Qty: 10, Checked by: Ragnhild', 1, '2025-06-27 04:24:05', 'read', 'RSV-685DFA8D9FED6', 37),
(418, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E1DE2C19D7\'>#RSV-685E1DE2C19D7</a> by Carl Caraos for 2025-07-09.', 1, '2025-06-27 04:28:18', 'read', 'RSV-685E1DE2C19D7', 47),
(419, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E1DE2C19D7, Product: MQ-2 Gas Sensor, Requested by: Carl Caraos, Qty: 100, Checked by: Ragnhild', 1, '2025-06-27 04:28:30', 'read', 'RSV-685E1DE2C19D7', 47),
(420, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E1E399E6EB\'>#RSV-685E1E399E6EB</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 04:29:45', 'read', 'RSV-685E1E399E6EB', 110),
(421, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E1E399E6EB, Product: Relay 4-Channel Module, Requested by: Carl Caraos, Qty: 101, Checked by: Ragnhild', 1, '2025-06-27 04:30:06', 'read', 'RSV-685E1E399E6EB', 110),
(422, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E0837BEE77, Product: sample, Requested by: Carl Caraos, Qty: 9, Checked by: Ragnhild', 1, '2025-06-27 04:37:02', 'read', 'RSV-685E0837BEE77', 830772),
(423, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E0C758E373, Product: LM2596 Buck Converter, Requested by: Carl Caraos, Qty: 10, Checked by: Ragnhild', 1, '2025-06-27 04:39:20', 'read', 'RSV-685E0C758E373', 39),
(424, NULL, 'Reservations deleted: Code RSV-685DFACD56A38, Product: DHT11 Temperature Sensor, Requested by: Carl Caraos; Code RSV-685DFADD90E1F, Product: DHT11 Temperature Sensor, Requested by: Carl Caraos; Code RSV-685DFAFC3048F, Product: Jumper Wires (M-M, M-F, F-F), Requested by: Carl Caraos; Code RSV-685E0BF8A36A1, Product: Raspberry Pi 4 Model B, Requested by: Carl Caraos; Code RSV-685E0C1093C1D, Product: DHT11 Temperature Sensor, Requested by: Carl Caraos; Code RSV-685E0C3923628, Product: LM2596 Buck Converter, Requested by: Carl Caraos; Code RSV-685E0C5D6CFA1, Product: Arduino Uno R3, Requested by: Carl Caraos', 1, '2025-06-27 04:40:03', 'read', NULL, NULL),
(425, NULL, 'Categories deleted: ID 1: Microcontrollers; ID 2: Sensors; ID 3: Motors; ID 4: Accessories; ID 5: Power Supplies', 1, '2025-06-27 04:40:36', 'read', NULL, NULL),
(426, NULL, 'Products deleted: wifi 5g globe at home  (ID 2), Arduino Uno R3 (ID 31), HC-SR04 Ultrasonic Sensor (ID 32), Breadboard 830 Tie-points (ID 34), Jumper Wires (M-M, M-F, F-F) (ID 35), ESP32 Dev Board (ID 36), Raspberry Pi 4 Model B (ID 37), DHT11 Temperature Sensor (ID 38), LM2596 Buck Converter (ID 39), L298N Motor Driver (ID 40)', 1, '2025-06-27 04:41:57', 'read', NULL, NULL),
(427, NULL, 'Products deleted: 5V Relay Module (ID 41), OLED Display 0.96\" (ID 42), NRF24L01 Module (ID 43), NodeMCU ESP8266 (ID 44), RTC DS3231 Module (ID 45), IR Obstacle Sensor (ID 46), MQ-2 Gas Sensor (ID 47), Rotary Encoder Module (ID 48), Photoresistor Module (ID 49), Sound Sensor Module (ID 50)', 1, '2025-06-27 04:42:07', 'read', NULL, NULL),
(428, NULL, 'Products deleted: Soil Moisture Sensor (ID 51), Laser Module 5mW (ID 52), Heat Sink 20mm (ID 53), Thermistor NTC 10K (ID 54), Piezo Buzzer Module (ID 55), Stepper Motor 28BYJ-48 (ID 56), ULN2003 Driver Board (ID 57), Tilt Sensor Module (ID 58), Flame Sensor Module (ID 59), Water Level Sensor (ID 60)', 1, '2025-06-27 04:42:15', 'read', NULL, NULL),
(429, NULL, 'Products deleted: Vibration Sensor Module (ID 61), Bluetooth HC-05 Module (ID 62), Joystick Module (ID 63), Micro SD Card Module (ID 64), Rain Sensor Module (ID 65), Fingerprint Sensor Module (ID 66), GPS Module NEO-6M (ID 67), MQ-135 Air Quality Sensor (ID 68), ADXL345 Accelerometer (ID 69), GY-521 MPU6050 Module (ID 70)', 1, '2025-06-27 04:42:23', 'read', NULL, NULL),
(430, NULL, 'Products deleted: Relay 4-Channel Module (ID 110), sample (ID 830772)', 1, '2025-06-27 04:42:29', 'read', NULL, NULL),
(431, NULL, 'Product updated (ID 1): Status: \'\' → \'Available\'', 1, '2025-06-27 04:49:13', 'read', NULL, 1),
(432, NULL, 'Product updated (ID 1): Image updated', 1, '2025-06-27 04:49:46', 'read', NULL, 1),
(433, NULL, 'Product updated (ID 2): Status: \'\' → \'Available\'; Image updated', 1, '2025-06-27 04:50:24', 'read', NULL, 2),
(434, NULL, 'Product updated (ID 3): Status: \'\' → \'Available\'; Image updated', 1, '2025-06-27 04:50:38', 'read', NULL, 3),
(435, NULL, 'Product updated (ID 4): Status: \'\' → \'Available\'; Image updated', 1, '2025-06-27 04:50:50', 'read', NULL, 4),
(436, NULL, 'Product updated (ID 5): Status: \'\' → \'Available\'; Image updated', 1, '2025-06-27 04:51:02', 'read', NULL, 5),
(437, NULL, 'Product updated (ID 6): Category: \'Power & Display\' → \'Microcontrollers\'; Status: \'\' → \'Available\'; Image updated', 1, '2025-06-27 04:51:19', 'read', NULL, 6),
(438, NULL, 'Product updated (ID 7): Category: \'\' → \'Microcontrollers\'; Status: \'\' → \'Not Available\'; Image updated', 1, '2025-06-27 04:52:55', 'read', NULL, 7),
(439, NULL, 'Product updated (ID 8): Category: \'\' → \'Microcontrollers\'; Status: \'\' → \'Available\'; Image updated', 1, '2025-06-27 05:43:44', 'read', NULL, 8),
(440, NULL, 'Product updated (ID 7): Status: \'Not Available\' → \'Available\'', 1, '2025-06-27 05:43:57', 'read', NULL, 7),
(441, NULL, 'Product updated (ID 9): Category: \'\' → \'Microcontrollers\'; Status: \'\' → \'Available\'; Image updated', 1, '2025-06-27 05:44:27', 'read', NULL, 9),
(442, NULL, 'Product updated (ID 10): Category: \'\' → \'Microcontrollers\'; Status: \'\' → \'Available\'; Image updated', 1, '2025-06-27 05:44:48', 'read', NULL, 10),
(443, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E2FFA28D57\'>#RSV-685E2FFA28D57</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 05:45:30', 'read', 'RSV-685E2FFA28D57', 5),
(444, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E2FFA28D57, Product: IR Sensor Module, Requested by: Carl Caraos, Qty: 100, Checked by: Ragnhild', 1, '2025-06-27 05:46:09', 'read', 'RSV-685E2FFA28D57', 5),
(445, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E3072DAD23\'>#RSV-685E3072DAD23</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 05:47:30', 'read', 'RSV-685E3072DAD23', 2),
(446, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E3072DAD23, Product: LM2596 Buck Converter, Requested by: Carl Caraos, Qty: 75, Checked by: Ragnhild', 1, '2025-06-27 05:48:09', 'read', 'RSV-685E3072DAD23', 2),
(447, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E30E0F076C\'>#RSV-685E30E0F076C</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 05:49:20', 'read', 'RSV-685E30E0F076C', 1),
(448, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E30E0F076C\'>#RSV-685E30E0F076C</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 05:49:20', 'read', 'RSV-685E30E0F076C', 4),
(449, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E30E0F076C\'>#RSV-685E30E0F076C</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 05:49:21', 'read', 'RSV-685E30E0F076C', 3),
(450, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E30E0F076C\'>#RSV-685E30E0F076C</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 05:49:21', 'read', 'RSV-685E30E0F076C', 2),
(451, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E30E0F076C, Product: LM2596 Buck Converter, Requested by: Carl Caraos, Qty: 25, Checked by: Ragnhild', 1, '2025-06-27 05:50:27', 'read', 'RSV-685E30E0F076C', 2),
(452, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E30E0F076C, Product: HC-SR04 Ultrasonic Sensor, Requested by: Carl Caraos, Qty: 40, Checked by: Ragnhild', 1, '2025-06-27 05:56:13', 'read', 'RSV-685E30E0F076C', 3),
(453, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E30E0F076C, Product: SG90 Servo Motor, Requested by: Carl Caraos, Qty: 50, Checked by: Ragnhild', 1, '2025-06-27 05:56:13', 'read', 'RSV-685E30E0F076C', 4),
(454, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E30E0F076C, Product: Arduino Uno R3, Requested by: Carl Caraos, Qty: 50, Checked by: Ragnhild', 1, '2025-06-27 05:56:13', 'read', 'RSV-685E30E0F076C', 1),
(455, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E32CDCC448\'>#RSV-685E32CDCC448</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 05:57:33', 'read', 'RSV-685E32CDCC448', 4),
(456, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E32CDCC448\'>#RSV-685E32CDCC448</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 05:57:33', 'read', 'RSV-685E32CDCC448', 1),
(457, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E32CDCC448\'>#RSV-685E32CDCC448</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 05:57:33', 'read', 'RSV-685E32CDCC448', 3),
(458, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E32CDCC448, Product: HC-SR04 Ultrasonic Sensor, Requested by: Carl Caraos, Qty: 10, Checked by: myRagnhild', 1, '2025-06-27 05:58:42', 'read', 'RSV-685E32CDCC448', 3),
(459, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E32CDCC448, Product: Arduino Uno R3, Requested by: Carl Caraos, Qty: 10, Checked by: Ragnhild', 1, '2025-06-27 05:58:42', 'read', 'RSV-685E32CDCC448', 1),
(460, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E32CDCC448, Product: SG90 Servo Motor, Requested by: Carl Caraos, Qty: 50, Checked by: myRagnhild', 1, '2025-06-27 05:58:42', 'read', 'RSV-685E32CDCC448', 4),
(461, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E334A99CBA\'>#RSV-685E334A99CBA</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 05:59:38', 'read', 'RSV-685E334A99CBA', 2),
(462, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E334A99CBA, Product: LM2596 Buck Converter, Requested by: Carl Caraos, Qty: 50, Checked by: Ragnhild', 1, '2025-06-27 06:00:09', 'read', 'RSV-685E334A99CBA', 2),
(463, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E33941DA62\'>#RSV-685E33941DA62</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 06:00:52', 'read', 'RSV-685E33941DA62', 4),
(464, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E33941DA62, Product: SG90 Servo Motor, Requested by: Carl Caraos, Qty: 49, Checked by: Ragnhild', 1, '2025-06-27 06:01:08', 'read', 'RSV-685E33941DA62', 4),
(465, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E351CB06B1\'>#RSV-685E351CB06B1</a> by Carl Caraos for 2025-07-02.', 1, '2025-06-27 06:07:24', 'read', 'RSV-685E351CB06B1', 2),
(466, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E3536442DA\'>#RSV-685E3536442DA</a> by Ragnhild for 2025-06-27.', 1, '2025-06-27 06:07:50', 'read', 'RSV-685E3536442DA', 4),
(467, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E351CB06B1, Product: LM2596 Buck Converter, Requested by: Carl Caraos, Qty: 50, Checked by: my loveRagnhild', 1, '2025-06-27 06:08:10', 'read', 'RSV-685E351CB06B1', 2),
(468, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E3536442DA, Product: SG90 Servo Motor, Requested by: Ragnhild, Qty: 45, Checked by: Ragnhild', 1, '2025-06-27 06:09:07', 'read', 'RSV-685E3536442DA', 4),
(469, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E35E8D755D\'>#RSV-685E35E8D755D</a> by Carl Caraos for 2025-07-11.', 1, '2025-06-27 06:10:48', 'read', 'RSV-685E35E8D755D', 4),
(470, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E35E8D755D\'>#RSV-685E35E8D755D</a> by Carl Caraos for 2025-07-11.', 1, '2025-06-27 06:10:48', 'read', 'RSV-685E35E8D755D', 3),
(471, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E35E8D755D, Product: HC-SR04 Ultrasonic Sensor, Requested by: Carl Caraos, Qty: 15, Checked by: Ragnhild', 1, '2025-06-27 06:11:06', 'read', 'RSV-685E35E8D755D', 3),
(472, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E35E8D755D, Product: SG90 Servo Motor, Requested by: Carl Caraos, Qty: 20, Checked by: Ragnhild', 1, '2025-06-27 06:11:06', 'read', 'RSV-685E35E8D755D', 4),
(473, NULL, 'Product updated (ID 1): Reorder Level: 10 → 0', 1, '2025-06-27 06:11:42', 'read', NULL, 1),
(474, NULL, 'Product updated (ID 2): Reorder Level: 10 → 0', 1, '2025-06-27 06:11:52', 'read', NULL, 2),
(475, NULL, 'Product updated (ID 3): Reorder Level: 20 → 0', 1, '2025-06-27 06:12:04', 'read', NULL, 3),
(476, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E364BD4FF3\'>#RSV-685E364BD4FF3</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 06:12:27', 'read', 'RSV-685E364BD4FF3', 1),
(477, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E364BD4FF3, Product: Arduino Uno R3, Requested by: Carl Caraos, Qty: 40, Checked by: Ragnhild', 1, '2025-06-27 06:12:42', 'read', 'RSV-685E364BD4FF3', 1),
(478, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E37E889588\'>#RSV-685E37E889588</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 06:19:20', 'read', 'RSV-685E37E889588', 2),
(479, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E37E889588, Product: LM2596 Buck Converter, Requested by: Carl Caraos, Qty: 50, Checked by: Ragnhild', 1, '2025-06-27 06:19:36', 'read', 'RSV-685E37E889588', 2),
(480, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E381962EC1\'>#RSV-685E381962EC1</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 06:20:09', 'read', 'RSV-685E381962EC1', 5),
(481, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E381962EC1, Product: IR Sensor Module, Requested by: Carl Caraos, Qty: 20, Checked by: Ragnhild', 1, '2025-06-27 06:20:25', 'read', 'RSV-685E381962EC1', 5),
(482, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E39135F585\'>#RSV-685E39135F585</a> by Carl Caraos for 2025-06-28.', 1, '2025-06-27 06:24:19', 'read', 'RSV-685E39135F585', 5),
(483, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E39135F585, Product: IR Sensor Module, Requested by: Carl Caraos, Qty: 20, Checked by: Ragnhild', 1, '2025-06-27 06:24:33', 'read', 'RSV-685E39135F585', 5),
(484, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E3A94CC0EA\'>#RSV-685E3A94CC0EA</a> by Carl Caraos for 2025-06-28.', 1, '2025-06-27 06:30:44', 'read', 'RSV-685E3A94CC0EA', 5),
(485, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E3A94CC0EA, Product: IR Sensor Module, Requested by: Carl Caraos, Qty: 20, Checked by: Ragnhild', 1, '2025-06-27 06:34:19', 'read', 'RSV-685E3A94CC0EA', 5),
(486, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E3D72B7848\'>#RSV-685E3D72B7848</a> by Carl Caraos for 2025-07-11.', 1, '2025-06-27 06:42:58', 'read', 'RSV-685E3D72B7848', 5),
(487, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E3D72B7848, Product: IR Sensor Module, Requested by: Carl Caraos, Qty: 20, Checked by: Ragnhild', 1, '2025-06-27 06:43:13', 'read', 'RSV-685E3D72B7848', 5),
(488, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E3EF0BC640\'>#RSV-685E3EF0BC640</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 06:49:20', 'read', 'RSV-685E3EF0BC640', 3),
(489, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E3EF0BC640, Product: HC-SR04 Ultrasonic Sensor, Requested by: Carl Caraos, Qty: 30, Checked by: Ragnhild', 1, '2025-06-27 06:49:34', 'read', 'RSV-685E3EF0BC640', 3),
(490, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E3F82873FD\'>#RSV-685E3F82873FD</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 06:51:46', 'read', 'RSV-685E3F82873FD', 4),
(491, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E3F82873FD, Product: SG90 Servo Motor, Requested by: Carl Caraos, Qty: 30, Checked by: Ragnhild', 1, '2025-06-27 06:52:00', 'read', 'RSV-685E3F82873FD', 4),
(492, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E4070D5DF3\'>#RSV-685E4070D5DF3</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 06:55:44', 'read', 'RSV-685E4070D5DF3', 5),
(493, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E4070D5DF3, Product: IR Sensor Module, Requested by: Carl Caraos, Qty: 20, Checked by: Ragnhild', 1, '2025-06-27 06:56:04', 'read', 'RSV-685E4070D5DF3', 5),
(494, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E4292099E2\'>#RSV-685E4292099E2</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 07:04:50', 'read', 'RSV-685E4292099E2', 10),
(495, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E4292099E2\'>#RSV-685E4292099E2</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 07:04:50', 'read', 'RSV-685E4292099E2', 6),
(496, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E4292099E2\'>#RSV-685E4292099E2</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 07:04:50', 'read', 'RSV-685E4292099E2', 8),
(497, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E4292099E2\'>#RSV-685E4292099E2</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 07:04:50', 'read', 'RSV-685E4292099E2', 7),
(498, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E4292099E2\'>#RSV-685E4292099E2</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 07:04:50', 'read', 'RSV-685E4292099E2', 4),
(499, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E4292099E2, Product: SG90 Servo Motor, Requested by: Carl Caraos, Qty: 30, Checked by: Ragnhild', 1, '2025-06-27 07:13:34', 'read', 'RSV-685E4292099E2', 4),
(500, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E4292099E2, Product: 16x2 LCD with I2C, Requested by: Carl Caraos, Qty: 60, Checked by: Ragnhild', 1, '2025-06-27 07:13:34', 'read', 'RSV-685E4292099E2', 7),
(501, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E4292099E2, Product: 18650 Battery Pack, Requested by: Carl Caraos, Qty: 68, Checked by: Ragnhild', 1, '2025-06-27 07:13:34', 'read', 'RSV-685E4292099E2', 8),
(502, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E4292099E2, Product: L298N Motor Driver, Requested by: Carl Caraos, Qty: 90, Checked by: Ragnhild', 1, '2025-06-27 07:13:34', 'read', 'RSV-685E4292099E2', 6),
(503, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E4292099E2, Product: Jumper Wires Pack, Requested by: Carl Caraos, Qty: 200, Checked by: Ragnhild', 1, '2025-06-27 07:13:34', 'read', 'RSV-685E4292099E2', 10),
(504, NULL, 'Product updated (ID 1): Stock: 0 → 50', 1, '2025-06-27 07:14:22', 'read', NULL, 1),
(505, NULL, 'Product updated (ID 2): Stock: 0 → 50; Reorder Level: 0 → 5; Category: \'Sensors\' → \'Microcontrollers\'', 1, '2025-06-27 07:14:40', 'read', NULL, 2);
INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`, `status`, `reservation_code`, `product_id`) VALUES
(506, NULL, 'Product updated (ID 3): Stock: 0 → 50; Reorder Level: 0 → 1', 1, '2025-06-27 07:14:55', 'read', NULL, 3),
(507, NULL, 'Product updated (ID 4): Stock: 0 → 50; Reorder Level: 0 → 1', 1, '2025-06-27 07:15:08', 'read', NULL, 4),
(508, NULL, 'Product updated (ID 5): Stock: 0 → 50', 1, '2025-06-27 07:15:19', 'read', NULL, 5),
(509, NULL, 'Product updated (ID 10): Stock: 0 → 50; Reorder Level: 0 → 3', 1, '2025-06-27 07:15:33', 'read', NULL, 10),
(510, NULL, 'Product updated (ID 6): Stock: 0 → 50; Reorder Level: 0 → 5', 1, '2025-06-27 07:15:51', 'read', NULL, 6),
(511, NULL, 'Product updated (ID 7): Stock: 5 → 50', 1, '2025-06-27 07:16:06', 'read', NULL, 7),
(512, NULL, 'Product updated (ID 8): Stock: 2 → 50', 1, '2025-06-27 07:16:16', 'read', NULL, 8),
(513, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E459D8D88B\'>#RSV-685E459D8D88B</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 07:17:49', 'read', 'RSV-685E459D8D88B', 6),
(514, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E459D8D88B\'>#RSV-685E459D8D88B</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 07:17:49', 'read', 'RSV-685E459D8D88B', 4),
(515, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E459D8D88B\'>#RSV-685E459D8D88B</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 07:17:49', 'read', 'RSV-685E459D8D88B', 3),
(516, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E459D8D88B\'>#RSV-685E459D8D88B</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 07:17:49', 'read', 'RSV-685E459D8D88B', 2),
(517, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E459D8D88B\'>#RSV-685E459D8D88B</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 07:17:49', 'read', 'RSV-685E459D8D88B', 1),
(518, NULL, 'Product updated (ID 1): Reorder Level: 0 → 3', 1, '2025-06-27 07:18:17', 'read', NULL, 1),
(519, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E459D8D88B, Product: Arduino Uno R3, Requested by: Carl Caraos, Qty: 47, Checked by: Ragnhild', 1, '2025-06-27 07:19:24', 'read', 'RSV-685E459D8D88B', 1),
(520, NULL, 'Product updated (ID 1): Stock: 3 → 50', 1, '2025-06-27 07:19:41', 'read', NULL, 1),
(521, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E459D8D88B, Product: LM2596 Buck Converter, Requested by: Carl Caraos, Qty: 45, Checked by: Ragnhild', 1, '2025-06-27 07:50:56', 'read', 'RSV-685E459D8D88B', 2),
(522, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E459D8D88B, Product: HC-SR04 Ultrasonic Sensor, Requested by: Carl Caraos, Qty: 49, Checked by: Ragnhild', 1, '2025-06-27 07:50:56', 'read', 'RSV-685E459D8D88B', 3),
(523, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E459D8D88B, Product: SG90 Servo Motor, Requested by: Carl Caraos, Qty: 49, Checked by: Ragnhild', 1, '2025-06-27 07:50:56', 'read', 'RSV-685E459D8D88B', 4),
(524, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E459D8D88B, Product: L298N Motor Driver, Requested by: Carl Caraos, Qty: 45, Checked by: myRagnhild', 1, '2025-06-27 07:50:56', 'read', 'RSV-685E459D8D88B', 6),
(525, NULL, 'Product updated (ID 3): Stock: 1 → 50; Reorder Level: 1 → 5', 1, '2025-06-27 07:51:21', 'read', NULL, 3),
(526, NULL, 'Product updated (ID 2): Stock: 5 → 50', 1, '2025-06-27 07:51:30', 'read', NULL, 2),
(527, NULL, 'Product updated (ID 4): Stock: 1 → 50; Reorder Level: 1 → 5', 1, '2025-06-27 07:51:41', 'read', NULL, 4),
(528, NULL, 'Product updated (ID 6): Stock: 5 → 50', 1, '2025-06-27 07:51:50', 'read', NULL, 6),
(529, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E4DE5A1D30\'>#RSV-685E4DE5A1D30</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 07:53:09', 'read', 'RSV-685E4DE5A1D30', 2),
(530, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E4E337DB59\'>#RSV-685E4E337DB59</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 07:54:27', 'read', 'RSV-685E4E337DB59', 5),
(531, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E4E337DB59\'>#RSV-685E4E337DB59</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 07:54:27', 'read', 'RSV-685E4E337DB59', 1),
(532, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E4E337DB59\'>#RSV-685E4E337DB59</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 07:54:27', 'read', 'RSV-685E4E337DB59', 2),
(533, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E4E337DB59\'>#RSV-685E4E337DB59</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 07:54:27', 'read', 'RSV-685E4E337DB59', 7),
(534, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E4E337DB59\'>#RSV-685E4E337DB59</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 07:54:27', 'read', 'RSV-685E4E337DB59', 4),
(535, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685E4E337DB59\'>#RSV-685E4E337DB59</a> by Carl Caraos for 2025-06-27.', 1, '2025-06-27 07:54:27', 'read', 'RSV-685E4E337DB59', 3),
(536, NULL, 'You have logged in successfully.', 1, '2025-06-27 08:08:59', 'read', NULL, NULL),
(537, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E4E337DB59, Product: HC-SR04 Ultrasonic Sensor, Requested by: Carl Caraos, Qty: 45, Checked by: myRagnhild', 1, '2025-06-27 08:11:11', 'read', 'RSV-685E4E337DB59', 3),
(538, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E4E337DB59, Product: SG90 Servo Motor, Requested by: Carl Caraos, Qty: 45, Checked by: myRagnhild', 1, '2025-06-27 08:11:11', 'read', 'RSV-685E4E337DB59', 4),
(539, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E4E337DB59, Product: 16x2 LCD with I2C, Requested by: Carl Caraos, Qty: 50, Checked by: myRagnhild', 1, '2025-06-27 08:11:11', 'read', 'RSV-685E4E337DB59', 7),
(540, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E4DE5A1D30, Product: LM2596 Buck Converter, Requested by: Carl Caraos, Qty: 45, Checked by: myRagnhild', 1, '2025-06-27 08:12:29', 'read', 'RSV-685E4DE5A1D30', 2),
(541, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E4E337DB59, Product: IR Sensor Module, Requested by: Carl Caraos, Qty: 50, Checked by: myRagnhild', 1, '2025-06-27 08:12:42', 'read', 'RSV-685E4E337DB59', 5),
(542, NULL, 'Reservations deleted: Code RSV-685E4E337DB59, Product: LM2596 Buck Converter, Requested by: Carl Caraos', 1, '2025-06-27 08:13:16', 'read', NULL, NULL),
(543, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685E4E337DB59, Product: Arduino Uno R3, Requested by: Carl Caraos, Qty: 47, Checked by: myRagnhild', 1, '2025-06-27 08:13:27', 'read', 'RSV-685E4E337DB59', 1),
(544, NULL, 'Product updated (ID 1): Stock: 3 → 50', 1, '2025-06-27 08:14:44', 'read', NULL, 1),
(545, NULL, 'Product updated (ID 2): Stock: 5 → 50', 1, '2025-06-27 08:14:53', 'read', NULL, 2),
(546, NULL, 'Product updated (ID 3): Stock: 5 → 50', 1, '2025-06-27 08:15:02', 'read', NULL, 3),
(547, NULL, 'Product updated (ID 4): Stock: 5 → 50', 1, '2025-06-27 08:15:10', 'read', NULL, 4),
(548, NULL, 'Product updated (ID 7): Stock: 0 → 100; Reorder Level: 0 → 10', 1, '2025-06-27 08:15:24', 'read', NULL, 7),
(549, NULL, 'Product updated (ID 5): Stock: 0 → 60; Reorder Level: 0 → 5', 1, '2025-06-27 08:16:24', 'read', NULL, 5),
(550, NULL, 'You have logged in successfully.', 1, '2025-06-28 00:20:36', 'read', NULL, NULL),
(551, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685F35AFE33F5\'>#RSV-685F35AFE33F5</a> by Carl Caraos for 2025-06-28.', 1, '2025-06-28 00:22:07', 'read', 'RSV-685F35AFE33F5', 1),
(552, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685F35AFE33F5, Product: Arduino Uno R3, Requested by: Carl Caraos, Qty: 5, Checked by: Ragnhild', 1, '2025-06-28 00:26:21', 'read', 'RSV-685F35AFE33F5', 1),
(553, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685F394DBB315\'>#RSV-685F394DBB315</a> by Carl Caraos for 2025-06-28.', 1, '2025-06-28 00:37:33', 'read', 'RSV-685F394DBB315', 3),
(554, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685F62D10E934\'>#RSV-685F62D10E934</a> by Carl Caraos for 2025-06-28.', 1, '2025-06-28 03:34:41', 'read', 'RSV-685F62D10E934', 1),
(555, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685F62D10E934, Product: Arduino Uno R3, Requested by: Carl Caraos, Qty: 5, Checked by: Ragnhild', 1, '2025-06-28 03:34:59', 'read', 'RSV-685F62D10E934', 1),
(556, NULL, 'Return logs deleted: Code RSV-685910E8EBF07, Product: L298N Motor Driver, Requested by: Carl Caraos; Code RSV-685912CD85861, Product: ESP32 Dev Board, Requested by: Carl Caraos; Code RSV-685915BE46D82, Product: L298N Motor Driver, Requested by: Carl Caraos', 1, '2025-06-28 03:35:29', 'read', NULL, NULL),
(557, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685F394DBB315, Product: HC-SR04 Ultrasonic Sensor, Requested by: Carl Caraos, Qty: 5, Checked by: Ragnhild', 1, '2025-06-28 03:41:15', 'read', 'RSV-685F394DBB315', 3),
(558, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685F66920027D\'>#RSV-685F66920027D</a> by Carl Caraos for 2025-06-28.', 1, '2025-06-28 03:50:42', 'read', 'RSV-685F66920027D', 1),
(559, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685F66920027D, Product: Arduino Uno R3, Requested by: Carl Caraos, Qty: 5, Checked by: Ragnhild', 1, '2025-06-28 03:50:54', 'read', 'RSV-685F66920027D', 1),
(560, NULL, 'Return logs deleted: Code RSV-685F394DBB315, Product: HC-SR04 Ultrasonic Sensor, Requested by: Carl Caraos', 1, '2025-06-28 04:43:09', 'read', NULL, NULL),
(561, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685F93F99E304\'>#RSV-685F93F99E304</a> by Carl Caraos for 2025-06-28.', 1, '2025-06-28 07:04:25', 'read', 'RSV-685F93F99E304', 1),
(562, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685F93F99E304, Product: Arduino Uno R3, Requested by: Carl Caraos, Qty: 10, Checked by: myRagnhild', 1, '2025-06-28 07:05:25', 'read', 'RSV-685F93F99E304', 1),
(563, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685F9A3E02CC1\'>#RSV-685F9A3E02CC1</a> by Carl Caraos for 2025-06-28 08:00 AM.', 1, '2025-06-28 07:31:10', 'read', 'RSV-685F9A3E02CC1', 6),
(564, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685F9C786D605\'>#RSV-685F9C786D605</a> by Carl Caraos for 2025-06-28 08:00 AM.', 1, '2025-06-28 07:40:40', 'read', 'RSV-685F9C786D605', 1),
(565, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685F9E1FA3D4B\'>#RSV-685F9E1FA3D4B</a> by Carl Caraos for 2025-06-28 12:00:00.', 1, '2025-06-28 07:47:43', 'read', 'RSV-685F9E1FA3D4B', 2),
(566, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685FA1216C0FE\'>#RSV-685FA1216C0FE</a> by Carl Caraos for 2025-06-28 08:00:00.', 1, '2025-06-28 08:00:33', 'read', 'RSV-685FA1216C0FE', 2),
(567, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685FA21341C53\'>#RSV-685FA21341C53</a> by Carl Caraos for 2025-06-28 08:00:00.', 1, '2025-06-28 08:04:35', 'read', 'RSV-685FA21341C53', 1),
(568, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685FAAECC3788\'>#RSV-685FAAECC3788</a> by Carl Caraos for 2025-06-28 08:00:00.', 1, '2025-06-28 08:42:20', 'read', 'RSV-685FAAECC3788', 1),
(569, NULL, 'Reservations deleted: Code RSV-685F9A3E02CC1, Product: L298N Motor Driver, Requested by: Carl Caraos; Code RSV-685F9C786D605, Product: Arduino Uno R3, Requested by: Carl Caraos; Code RSV-685F9E1FA3D4B, Product: LM2596 Buck Converter, Requested by: Carl Caraos; Code RSV-685FA1216C0FE, Product: LM2596 Buck Converter, Requested by: Carl Caraos; Code RSV-685FA21341C53, Product: Arduino Uno R3, Requested by: Carl Caraos; Code RSV-685FAAECC3788, Product: Arduino Uno R3, Requested by: Carl Caraos', 1, '2025-06-28 08:42:41', 'read', NULL, NULL),
(570, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685FAB15E0730\'>#RSV-685FAB15E0730</a> by Carl Caraos for 2025-06-28 08:00:00.', 1, '2025-06-28 08:43:01', 'read', 'RSV-685FAB15E0730', 1),
(571, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685FAB15E0730, Product: Arduino Uno R3, Requested by: Carl Caraos, Qty: 5, Checked by: Ragnhild', 1, '2025-06-28 08:43:13', 'read', 'RSV-685FAB15E0730', 1),
(572, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685FAE94DDE72\'>#RSV-685FAE94DDE72</a> by Carl Caraos for 2025-06-28 08:00:00.', 1, '2025-06-28 08:57:56', 'read', 'RSV-685FAE94DDE72', 1),
(573, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685FAE94DDE72, Product: Arduino Uno R3, Requested by: Carl Caraos, Qty: 5, Checked by: 2750.00', 1, '2025-06-28 09:04:59', 'read', 'RSV-685FAE94DDE72', 1),
(574, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-685FB051A2ABC\'>#RSV-685FB051A2ABC</a> by Carl Caraos for 2025-06-28 08:00:00.', 1, '2025-06-28 09:05:21', 'read', 'RSV-685FB051A2ABC', 1),
(575, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-685FB051A2ABC, Product: Arduino Uno R3, Requested by: Carl Caraos, Qty: 3, Checked by: Ragnhild', 1, '2025-06-28 09:05:56', 'read', 'RSV-685FB051A2ABC', 1),
(576, NULL, 'You have logged in successfully.', 1, '2025-06-30 00:02:11', 'read', NULL, NULL),
(577, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861D4373BE87\'>#RSV-6861D4373BE87</a> by Carl Caraos for 2025-06-30 08:00:00.', 1, '2025-06-30 00:03:03', 'read', 'RSV-6861D4373BE87', 1),
(578, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861D44F5AD00\'>#RSV-6861D44F5AD00</a> by Carl Caraos for 2025-06-30 08:00:00.', 1, '2025-06-30 00:03:27', 'read', 'RSV-6861D44F5AD00', 2),
(579, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-6861D44F5AD00, Product: LM2596 Buck Converter, Requested by: Carl Caraos, Qty: 45, Checked by: Ragnhild', 1, '2025-06-30 00:04:08', 'read', 'RSV-6861D44F5AD00', 2),
(580, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-6861D4373BE87, Product: Arduino Uno R3, Requested by: Carl Caraos, Qty: 17, Checked by: Ragnhild', 1, '2025-06-30 00:04:08', 'read', 'RSV-6861D4373BE87', 1),
(581, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861D4C50496C\'>#RSV-6861D4C50496C</a> by Carl Caraos for 2025-06-30 08:00:00.', 1, '2025-06-30 00:05:25', 'read', 'RSV-6861D4C50496C', 2),
(582, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861D4D554920\'>#RSV-6861D4D554920</a> by Carl Caraos for 2025-06-30 12:00:00.', 1, '2025-06-30 00:05:41', 'read', 'RSV-6861D4D554920', 1),
(583, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861D4E501CD6\'>#RSV-6861D4E501CD6</a> by Carl Caraos for 2025-06-30 12:00:00.', 1, '2025-06-30 00:05:57', 'read', 'RSV-6861D4E501CD6', 4),
(584, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861D51436FCB\'>#RSV-6861D51436FCB</a> by Carl Caraos for 2025-06-30 08:00:00.', 1, '2025-06-30 00:06:44', 'read', 'RSV-6861D51436FCB', 8),
(585, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-6861D51436FCB, Product: 18650 Battery Pack, Requested by: Carl Caraos, Qty: 50, Checked by: Ragnhild', 1, '2025-06-30 00:06:55', 'read', 'RSV-6861D51436FCB', 8),
(586, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-6861D4D554920, Product: Arduino Uno R3, Requested by: Carl Caraos, Qty: 5, Checked by: Ragnhild', 0, '2025-06-30 00:39:04', 'unread', 'RSV-6861D4D554920', 1),
(587, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861DCD93A7DF\'>#RSV-6861DCD93A7DF</a> by Carl Caraos for 2025-06-30 08:00:00.', 0, '2025-06-30 00:39:53', 'unread', 'RSV-6861DCD93A7DF', 1),
(588, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861DCF9F1C11\'>#RSV-6861DCF9F1C11</a> by Carl Caraos for 2025-06-30 08:00:00.', 0, '2025-06-30 00:40:25', 'unread', 'RSV-6861DCF9F1C11', 5),
(589, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861DD0F2116D\'>#RSV-6861DD0F2116D</a> by Carl Caraos for 2025-06-30 08:00:00.', 0, '2025-06-30 00:40:47', 'unread', 'RSV-6861DD0F2116D', 2),
(590, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-6861DD0F2116D, Product: LM2596 Buck Converter, Requested by: Carl Caraos, Qty: 45, Checked by: Ragnhild', 0, '2025-06-30 00:41:25', 'unread', 'RSV-6861DD0F2116D', 2),
(591, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-6861DCF9F1C11, Product: IR Sensor Module, Requested by: Carl Caraos, Qty: 10, Checked by: Ragnhild', 0, '2025-06-30 00:41:25', 'unread', 'RSV-6861DCF9F1C11', 5),
(592, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-6861DCD93A7DF, Product: Arduino Uno R3, Requested by: Carl Caraos, Qty: 5, Checked by: Ragnhild', 0, '2025-06-30 00:41:25', 'unread', 'RSV-6861DCD93A7DF', 1),
(593, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861DFDAA59A6\'>#RSV-6861DFDAA59A6</a> by Carl Caraos for 2025-06-30 08:00:00.', 0, '2025-06-30 00:52:42', 'unread', 'RSV-6861DFDAA59A6', 4),
(594, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861E0C3C630A\'>#RSV-6861E0C3C630A</a> by Carl Caraos for 2025-06-30 08:00:00.', 0, '2025-06-30 00:56:35', 'unread', 'RSV-6861E0C3C630A', 3),
(595, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861E16B222AD\'>#RSV-6861E16B222AD</a> by Carl Caraos for 2025-06-30 08:00:00.', 0, '2025-06-30 00:59:23', 'unread', 'RSV-6861E16B222AD', 3),
(596, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-6861DFDAA59A6, Product: SG90 Servo Motor, Requested by: Carl Caraos, Qty: 5, Checked by: Ragnhild', 0, '2025-06-30 01:00:30', 'unread', 'RSV-6861DFDAA59A6', 4),
(597, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-6861D4E501CD6, Product: SG90 Servo Motor, Requested by: Carl Caraos, Qty: 5, Checked by: Ragnhild', 0, '2025-06-30 01:00:30', 'unread', 'RSV-6861D4E501CD6', 4),
(598, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-6861E16B222AD, Product: HC-SR04 Ultrasonic Sensor, Requested by: Carl Caraos, Qty: 5, Checked by: Ragnhild', 0, '2025-06-30 01:03:16', 'unread', 'RSV-6861E16B222AD', 3),
(599, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-6861E0C3C630A, Product: HC-SR04 Ultrasonic Sensor, Requested by: Carl Caraos, Qty: 45, Checked by: Ragnhild', 0, '2025-06-30 01:06:19', 'unread', 'RSV-6861E0C3C630A', 3),
(600, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861E33204662\'>#RSV-6861E33204662</a> by Carl Caraos for 2025-07-12 08:00:00.', 0, '2025-06-30 01:06:58', 'unread', 'RSV-6861E33204662', 7),
(601, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861E3466345D\'>#RSV-6861E3466345D</a> by Carl Caraos for 2025-07-09 08:00:00.', 0, '2025-06-30 01:07:18', 'unread', 'RSV-6861E3466345D', 6),
(602, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-6861E3466345D, Product: L298N Motor Driver, Requested by: Carl Caraos, Qty: 10, Checked by: Ragnhild', 0, '2025-06-30 01:07:40', 'unread', 'RSV-6861E3466345D', 6),
(603, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-6861E33204662, Product: 16x2 LCD with I2C, Requested by: Carl Caraos, Qty: 50, Checked by: Ragnhild', 0, '2025-06-30 01:07:40', 'unread', 'RSV-6861E33204662', 7),
(604, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861E3AD6B706\'>#RSV-6861E3AD6B706</a> by Carl Caraos for 2025-07-04 08:00:00.', 0, '2025-06-30 01:09:01', 'unread', 'RSV-6861E3AD6B706', 3),
(605, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861E3CC083B4\'>#RSV-6861E3CC083B4</a> by Carl Caraos for 2025-07-10 08:00:00.', 0, '2025-06-30 01:09:32', 'unread', 'RSV-6861E3CC083B4', 2),
(606, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861E3CC083B4\'>#RSV-6861E3CC083B4</a> by Carl Caraos for 2025-07-10 08:00:00.', 0, '2025-06-30 01:09:32', 'unread', 'RSV-6861E3CC083B4', 2),
(607, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861E40A7BDD2\'>#RSV-6861E40A7BDD2</a> by Carl Caraos for 2025-06-30 12:00:00.', 0, '2025-06-30 01:10:34', 'unread', 'RSV-6861E40A7BDD2', 9),
(608, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861E40A7BDD2\'>#RSV-6861E40A7BDD2</a> by Carl Caraos for 2025-06-30 12:00:00.', 0, '2025-06-30 01:10:34', 'unread', 'RSV-6861E40A7BDD2', 1),
(609, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861E40A7BDD2\'>#RSV-6861E40A7BDD2</a> by Carl Caraos for 2025-06-30 12:00:00.', 0, '2025-06-30 01:10:34', 'unread', 'RSV-6861E40A7BDD2', 4),
(610, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861E40A7BDD2\'>#RSV-6861E40A7BDD2</a> by Carl Caraos for 2025-06-30 12:00:00.', 0, '2025-06-30 01:10:34', 'unread', 'RSV-6861E40A7BDD2', 2),
(611, NULL, 'Reservations deleted: Code RSV-6861E40A7BDD2, Product: Arduino Uno R3, Requested by: Carl Caraos; Code RSV-6861E40A7BDD2, Product: SG90 Servo Motor, Requested by: Carl Caraos; Code RSV-6861E40A7BDD2, Product: LM2596 Buck Converter, Requested by: Carl Caraos', 0, '2025-06-30 01:10:58', 'unread', NULL, NULL),
(612, NULL, 'Reservations deleted: Code RSV-6861E40A7BDD2, Product: Robot Chassis Kit, Requested by: Carl Caraos', 0, '2025-06-30 01:11:04', 'unread', NULL, NULL),
(613, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861E45FD8BAC\'>#RSV-6861E45FD8BAC</a> by Carl Caraos for 2025-07-15 12:00:00.', 0, '2025-06-30 01:11:59', 'unread', 'RSV-6861E45FD8BAC', 3),
(614, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861E45FD8BAC\'>#RSV-6861E45FD8BAC</a> by Carl Caraos for 2025-07-15 12:00:00.', 0, '2025-06-30 01:11:59', 'unread', 'RSV-6861E45FD8BAC', 2),
(615, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861E45FD8BAC\'>#RSV-6861E45FD8BAC</a> by Carl Caraos for 2025-07-15 12:00:00.', 0, '2025-06-30 01:11:59', 'unread', 'RSV-6861E45FD8BAC', 1),
(616, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861E45FD8BAC\'>#RSV-6861E45FD8BAC</a> by Carl Caraos for 2025-07-15 12:00:00.', 0, '2025-06-30 01:11:59', 'unread', 'RSV-6861E45FD8BAC', 4),
(617, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861E45FD8BAC\'>#RSV-6861E45FD8BAC</a> by Carl Caraos for 2025-07-15 12:00:00.', 0, '2025-06-30 01:11:59', 'unread', 'RSV-6861E45FD8BAC', 9),
(618, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-6861E45FD8BAC, Product: Robot Chassis Kit, Requested by: Carl Caraos, Qty: 1, Checked by: Ragnhild', 0, '2025-06-30 01:12:28', 'unread', 'RSV-6861E45FD8BAC', 9),
(619, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-6861E45FD8BAC, Product: SG90 Servo Motor, Requested by: Carl Caraos, Qty: 1, Checked by: Ragnhild', 0, '2025-06-30 01:17:51', 'unread', 'RSV-6861E45FD8BAC', 4),
(620, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-6861E45FD8BAC, Product: Arduino Uno R3, Requested by: Carl Caraos, Qty: 10, Checked by: Ragnhild', 0, '2025-06-30 01:34:48', 'unread', 'RSV-6861E45FD8BAC', 1),
(621, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-6861E45FD8BAC, Product: LM2596 Buck Converter, Requested by: Carl Caraos, Qty: 1, Checked by: Ragnhild', 0, '2025-06-30 01:35:48', 'unread', 'RSV-6861E45FD8BAC', 2),
(622, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-6861E3CC083B4, Product: LM2596 Buck Converter, Requested by: Carl Caraos, Qty: 1, Checked by: Ragnhild', 0, '2025-06-30 01:35:48', 'unread', 'RSV-6861E3CC083B4', 2),
(623, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-6861E3CC083B4, Product: LM2596 Buck Converter, Requested by: Carl Caraos, Qty: 1, Checked by: Ragnhild', 0, '2025-06-30 01:35:48', 'unread', 'RSV-6861E3CC083B4', 2),
(624, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-6861D4C50496C, Product: LM2596 Buck Converter, Requested by: Carl Caraos, Qty: 5, Checked by: Ragnhild', 0, '2025-06-30 01:35:48', 'unread', 'RSV-6861D4C50496C', 2),
(625, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861EA7D08157\'>#RSV-6861EA7D08157</a> by Carl Caraos for 2025-07-12 08:00:00.', 0, '2025-06-30 01:38:05', 'unread', 'RSV-6861EA7D08157', 6),
(626, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861EA7D08157\'>#RSV-6861EA7D08157</a> by Carl Caraos for 2025-07-12 08:00:00.', 0, '2025-06-30 01:38:05', 'unread', 'RSV-6861EA7D08157', 5),
(627, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861EA7D08157\'>#RSV-6861EA7D08157</a> by Carl Caraos for 2025-07-12 08:00:00.', 0, '2025-06-30 01:38:05', 'unread', 'RSV-6861EA7D08157', 4),
(628, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861EA7D08157\'>#RSV-6861EA7D08157</a> by Carl Caraos for 2025-07-12 08:00:00.', 0, '2025-06-30 01:38:05', 'unread', 'RSV-6861EA7D08157', 3),
(629, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861EA7D08157\'>#RSV-6861EA7D08157</a> by Carl Caraos for 2025-07-12 08:00:00.', 0, '2025-06-30 01:38:05', 'unread', 'RSV-6861EA7D08157', 2),
(630, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861EA7D08157\'>#RSV-6861EA7D08157</a> by Carl Caraos for 2025-07-12 08:00:00.', 0, '2025-06-30 01:38:05', 'unread', 'RSV-6861EA7D08157', 1),
(631, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-6861EA7D08157, Product: Arduino Uno R3, Requested by: Carl Caraos, Qty: 1, Checked by: Ragnhild', 0, '2025-06-30 01:38:55', 'unread', 'RSV-6861EA7D08157', 1),
(632, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-6861EA7D08157, Product: LM2596 Buck Converter, Requested by: Carl Caraos, Qty: 1, Checked by: Ragnhild', 0, '2025-06-30 01:38:55', 'unread', 'RSV-6861EA7D08157', 2),
(633, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-6861EA7D08157, Product: HC-SR04 Ultrasonic Sensor, Requested by: Carl Caraos, Qty: 5, Checked by: Ragnhild', 0, '2025-06-30 01:50:47', 'unread', 'RSV-6861EA7D08157', 3),
(634, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-6861EA7D08157, Product: IR Sensor Module, Requested by: Carl Caraos, Qty: 3, Checked by: Ragnhild', 0, '2025-06-30 02:21:17', 'unread', 'RSV-6861EA7D08157', 5),
(635, NULL, 'Return logs deleted: Code RSV-6861E3CC083B4, Product: LM2596 Buck Converter, Requested by: Carl Caraos', 0, '2025-06-30 02:37:23', 'unread', NULL, NULL),
(636, NULL, 'Return logs deleted: Code RSV-6861D4C50496C, Product: LM2596 Buck Converter, Requested by: Carl Caraos; Code RSV-6861E3CC083B4, Product: LM2596 Buck Converter, Requested by: Carl Caraos; Code RSV-6861E45FD8BAC, Product: LM2596 Buck Converter, Requested by: Carl Caraos; Code RSV-6861E45FD8BAC, Product: Arduino Uno R3, Requested by: Carl Caraos; Code RSV-6861E45FD8BAC, Product: SG90 Servo Motor, Requested by: Carl Caraos; Code RSV-6861E45FD8BAC, Product: Robot Chassis Kit, Requested by: Carl Caraos; Code RSV-6861EA7D08157, Product: HC-SR04 Ultrasonic Sensor, Requested by: Carl Caraos; Code RSV-6861EA7D08157, Product: LM2596 Buck Converter, Requested by: Carl Caraos; Code RSV-6861EA7D08157, Product: Arduino Uno R3, Requested by: Carl Caraos; Code RSV-6861EA7D08157, Product: IR Sensor Module, Requested by: Carl Caraos', 0, '2025-06-30 02:37:44', 'unread', NULL, NULL),
(637, NULL, 'Return logs deleted: Code RSV-6861E16B222AD, Product: HC-SR04 Ultrasonic Sensor, Requested by: Carl Caraos; Code RSV-6861D4E501CD6, Product: SG90 Servo Motor, Requested by: Carl Caraos; Code RSV-6861DFDAA59A6, Product: SG90 Servo Motor, Requested by: Carl Caraos; Code RSV-6861DCD93A7DF, Product: Arduino Uno R3, Requested by: Carl Caraos; Code RSV-6861DCF9F1C11, Product: IR Sensor Module, Requested by: Carl Caraos; Code RSV-6861E33204662, Product: 16x2 LCD with I2C, Requested by: Carl Caraos; Code RSV-6861E3466345D, Product: L298N Motor Driver, Requested by: Carl Caraos; Code RSV-6861E0C3C630A, Product: HC-SR04 Ultrasonic Sensor, Requested by: Carl Caraos; Code RSV-6861DD0F2116D, Product: LM2596 Buck Converter, Requested by: Carl Caraos; Code RSV-6861D51436FCB, Product: 18650 Battery Pack, Requested by: Carl Caraos', 0, '2025-06-30 02:37:49', 'unread', NULL, NULL),
(638, NULL, 'Return logs deleted: Code RSV-685F66920027D, Product: Arduino Uno R3, Requested by: Carl Caraos; Code RSV-685FB051A2ABC, Product: Arduino Uno R3, Requested by: Carl Caraos; Code RSV-6861D4373BE87, Product: Arduino Uno R3, Requested by: Carl Caraos; Code RSV-6861D44F5AD00, Product: LM2596 Buck Converter, Requested by: Carl Caraos; Code RSV-685FAE94DDE72, Product: Arduino Uno R3, Requested by: Carl Caraos; Code RSV-685FAB15E0730, Product: Arduino Uno R3, Requested by: Carl Caraos; Code RSV-685F93F99E304, Product: Arduino Uno R3, Requested by: Carl Caraos; Code RSV-6861D4D554920, Product: Arduino Uno R3, Requested by: Carl Caraos', 0, '2025-06-30 02:37:54', 'unread', NULL, NULL),
(639, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-6861EA7D08157, Product: SG90 Servo Motor, Requested by: Carl Caraos, Qty: 12, Checked by: Ragnhild', 0, '2025-06-30 02:38:35', 'unread', 'RSV-6861EA7D08157', 4),
(640, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-6861EA7D08157, Product: L298N Motor Driver, Requested by: Carl Caraos, Qty: 14, Checked by: Ragnhild', 0, '2025-06-30 02:38:35', 'unread', 'RSV-6861EA7D08157', 6),
(641, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-6861E45FD8BAC, Product: HC-SR04 Ultrasonic Sensor, Requested by: Carl Caraos, Qty: 15, Checked by: Ragnhild', 0, '2025-06-30 02:38:35', 'unread', 'RSV-6861E45FD8BAC', 3),
(642, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-6861E3AD6B706, Product: HC-SR04 Ultrasonic Sensor, Requested by: Carl Caraos, Qty: 7, Checked by: Ragnhild', 0, '2025-06-30 02:38:35', 'unread', 'RSV-6861E3AD6B706', 3),
(643, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861FD66A4618\'>#RSV-6861FD66A4618</a> by Carl Caraos for 2025-06-30 12:00:00.', 0, '2025-06-30 02:58:46', 'unread', 'RSV-6861FD66A4618', 1),
(644, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-6861FD66A4618, Product: Arduino Uno R3, Requested by: Carl Caraos, Qty: 37, Checked by: Ragnhild', 0, '2025-06-30 02:59:11', 'unread', 'RSV-6861FD66A4618', 1),
(645, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861FDAC44329\'>#RSV-6861FDAC44329</a> by Carl Caraos for 2025-07-12 12:00:00.', 0, '2025-06-30 02:59:56', 'unread', 'RSV-6861FDAC44329', 2),
(646, NULL, '[AUDIT] Reservation moved to borrowed: Code RSV-6861FDAC44329, Product: LM2596 Buck Converter, Requested by: Carl Caraos, Qty: 45, Checked by: Ragnhild', 0, '2025-06-30 03:00:14', 'unread', 'RSV-6861FDAC44329', 2),
(647, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861FF41A3F54\'>#RSV-6861FF41A3F54</a> by Carl Caraos for 2025-07-09 08:00:00.', 0, '2025-06-30 03:06:41', 'unread', 'RSV-6861FF41A3F54', 7),
(648, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861FF41A3F54\'>#RSV-6861FF41A3F54</a> by Carl Caraos for 2025-07-09 08:00:00.', 0, '2025-06-30 03:06:41', 'unread', 'RSV-6861FF41A3F54', 4),
(649, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6861FF41A3F54\'>#RSV-6861FF41A3F54</a> by Carl Caraos for 2025-07-09 08:00:00.', 0, '2025-06-30 03:06:41', 'unread', 'RSV-6861FF41A3F54', 3),
(650, NULL, 'New reservation <a href=\'reservation_view.php?code=RSV-6862036FAE2BF\'>#RSV-6862036FAE2BF</a> by Carl Caraos for 2025-07-16 08:00:00.', 0, '2025-06-30 03:24:31', 'unread', 'RSV-6862036FAE2BF', 5);

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
(320, 'RSV-6862036FAE2BF', 'Carl Caraos', 5, 'uploads/685e23365e7e4_4.png', 'IR Sensor Module', '2025-07-16 11:24:31', '08:00 AM - 12:00 PM', 'pending', 1, 25.00, 'Ragnhild', '2025-06-30 11:24:31');

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
  MODIFY `borrowed_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=583;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=651;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `reservation`
--
ALTER TABLE `reservation`
  MODIFY `req_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=321;

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
