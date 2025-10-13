-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 13, 2025 at 04:01 AM
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
-- Database: `eventia_users`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_admin`
--

CREATE TABLE `tbl_admin` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `time_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_bookings`
--

CREATE TABLE `tbl_bookings` (
  `booking_id` int(11) NOT NULL,
  `booking_reference` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `booking_type` enum('self','guided') NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `package_id` int(11) DEFAULT NULL,
  `package_name` varchar(255) DEFAULT NULL,
  `package_price` decimal(10,2) DEFAULT NULL,
  `venue_type` enum('own','rental') NOT NULL,
  `venue_id` int(11) DEFAULT NULL,
  `venue_address` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `event_time` varchar(50) NOT NULL,
  `event_location` varchar(255) NOT NULL,
  `full_address` text DEFAULT NULL,
  `contact_name` varchar(255) NOT NULL,
  `contact_email` varchar(255) NOT NULL,
  `contact_phone` varchar(50) NOT NULL,
  `alternate_phone` varchar(50) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `backup_email` varchar(255) DEFAULT NULL,
  `preferred_contact` enum('Any','Email','Phone','SMS') DEFAULT 'Any',
  `guest_count` int(11) DEFAULT NULL,
  `special_instructions` text DEFAULT NULL,
  `payment_method` enum('GCash','Bank Transfer','PayPal','Installment') NOT NULL,
  `payment_status` enum('pending','partial','paid','cancelled') DEFAULT 'pending',
  `receipt_path` varchar(255) DEFAULT NULL,
  `booking_status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_bookings`
--

INSERT INTO `tbl_bookings` (`booking_id`, `booking_reference`, `user_id`, `booking_type`, `event_type`, `package_id`, `package_name`, `package_price`, `venue_type`, `venue_id`, `venue_address`, `event_date`, `event_time`, `event_location`, `full_address`, `contact_name`, `contact_email`, `contact_phone`, `alternate_phone`, `company_name`, `backup_email`, `preferred_contact`, `guest_count`, `special_instructions`, `payment_method`, `payment_status`, `receipt_path`, `booking_status`, `total_amount`, `created_at`, `updated_at`) VALUES
(1, 'EVT-121499', 19, 'guided', 'Wedding Event Package', NULL, 'Wedding Event Package', NULL, 'rental', 1, 'The Garden Pavilion', '2025-10-12', 'To be determined', 'The Garden Pavilion', 'The Garden Pavilion', '', '', '', '+1 (347) 393-3076', '', 'wugufa@mailinator.com', 'Any', 87, 'Aliquid vel ut volup', 'GCash', 'pending', NULL, '', 136190.00, '2025-10-12 15:30:44', '2025-10-12 15:30:44'),
(3, 'EVT-654971717', 19, 'self', 'Engagement Party Package', NULL, 'Engagement Party Package', NULL, 'rental', 1, '', '2025-10-14', '01:09', 'The Garden Pavilion, Quezon City', 'Beautiful outdoor garden venue perfect for intimate gatherings and garden weddings', '', '', '', '+1 (998) 418-9621', 'Harvey Wilson Traders', 'genajutuby@mailinator.com', 'Email', 85, 'Adipisci dolor totam', '', 'pending', NULL, '', 98000.00, '2025-10-12 16:31:23', '2025-10-12 16:31:23'),
(5, 'EVT-760737173', 19, 'self', 'Engagement Party Package', NULL, 'Engagement Party Package', NULL, 'rental', 1, '', '2025-10-14', '06:19', 'The Garden Pavilion, Quezon City', 'Beautiful outdoor garden venue perfect for intimate gatherings and garden weddings', '', '', '', '+1 (888) 965-7492', 'Nichols Huff Trading', 'jyryvyky@mailinator.com', 'Phone', 900, 'Sit numquam sapient', '', 'pending', NULL, '', 77000.00, '2025-10-12 16:33:02', '2025-10-12 16:33:02'),
(6, 'EVT-868810593', 19, 'self', 'Engagement Party Package', NULL, 'Engagement Party Package', NULL, 'rental', 1, '', '2025-10-14', '11:13', 'The Garden Pavilion, Quezon City', 'Beautiful outdoor garden venue perfect for intimate gatherings and garden weddings', '', '', '', '+1 (568) 421-4971', 'Silva and Wong Associates', 'puvufaxawu@mailinator.com', 'Phone', 126, 'Cillum animi in ut ', '', 'pending', NULL, '', 75000.00, '2025-10-12 16:34:46', '2025-10-12 16:34:46');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_booking_services`
--

CREATE TABLE `tbl_booking_services` (
  `booking_service_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `service_description` text DEFAULT NULL,
  `base_price` decimal(10,2) DEFAULT 0.00,
  `final_price` decimal(10,2) DEFAULT 0.00,
  `customization_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`customization_details`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_booking_services`
--

INSERT INTO `tbl_booking_services` (`booking_service_id`, `booking_id`, `service_id`, `service_name`, `service_description`, `base_price`, `final_price`, `customization_details`, `created_at`) VALUES
(1, 1, 2, 'Decoration Setup', 'Transform your venue with beautiful decor and themes', 10000.00, 10000.00, NULL, '2025-10-12 15:30:44'),
(2, 1, 4, 'Sound System & Lights', 'Professional audio and lighting for perfect ambiance', 8000.00, 8000.00, NULL, '2025-10-12 15:30:44'),
(3, 1, 5, 'Entertainment', 'Keep your guests entertained throughout the event', 10000.00, 10000.00, NULL, '2025-10-12 15:30:44'),
(4, 3, 1, 'Catering Service', 'Professional food and beverage service for your event', 15000.00, 15000.00, NULL, '2025-10-12 16:31:23'),
(5, 3, 2, 'Decoration Setup', 'Transform your venue with beautiful decor and themes', 10000.00, 10000.00, NULL, '2025-10-12 16:31:23'),
(6, 3, 4, 'Sound System & Lights', 'Professional audio and lighting for perfect ambiance', 8000.00, 8000.00, NULL, '2025-10-12 16:31:23'),
(7, 5, 3, 'Photography & Videography', 'Capture your special moments professionally', 12000.00, 12000.00, NULL, '2025-10-12 16:33:02'),
(8, 6, 5, 'Entertainment', 'Keep your guests entertained throughout the event', 10000.00, 10000.00, NULL, '2025-10-12 16:34:46');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_events`
--

CREATE TABLE `tbl_events` (
  `event_id` int(11) NOT NULL,
  `event_title` varchar(255) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `event_description` varchar(255) NOT NULL,
  `event_price` decimal(2,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_events`
--

INSERT INTO `tbl_events` (`event_id`, `event_title`, `event_name`, `event_description`, `event_price`) VALUES
(3, 'DEBUTSS\r\n', 'happy bdays', 'very good birthday', 99),
(4, 'Birthday', 'happy bdays', 'very good birthday', 99),
(5, 'Birthday', 'happy bdays', 'very good birthday', 99),
(6, 'Birthday', 'happy bdays', 'very good birthday', 99),
(7, 'Birthday', 'happy bdays', 'very good birthday', 99),
(8, 'Birthday', 'happy bdays', 'very good birthday', 99),
(9, 'Birthday', 'happy bdays', 'very good birthday', 99);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_packages`
--

CREATE TABLE `tbl_packages` (
  `package_id` int(11) NOT NULL,
  `package_name` varchar(255) NOT NULL,
  `package_description` text DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `event_type` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_packages`
--

INSERT INTO `tbl_packages` (`package_id`, `package_name`, `package_description`, `base_price`, `event_type`, `status`, `created_at`) VALUES
(1, 'Wedding Event Package', 'Complete wedding planning with catering, floral design, and full coordination.', 108190.00, 'Wedding', 'active', '2025-10-12 08:47:20'),
(2, 'Birthday Celebration Package', 'Perfect for kids or adults, with cake, decorations, and entertainment add-ons.', 50000.00, 'Birthday', 'active', '2025-10-12 08:47:20'),
(3, 'Corporate Event Package', 'Professional setup for seminars, product launches, or company parties.', 140500.00, 'Corporate', 'active', '2025-10-12 08:47:20'),
(4, 'Debut Package', 'Includes 18 roses & candles setup, photography, and custom stage backdrop.', 95000.00, 'Debut', 'active', '2025-10-12 08:47:20'),
(5, 'Christening Package', 'Includes catering for 50 guests, souvenirs, and floral setup.', 45000.00, 'Christening', 'active', '2025-10-12 08:47:20'),
(6, 'Anniversary Celebration', 'Romantic dinner setting with live music and custom themes.', 75000.00, 'Anniversary', 'active', '2025-10-12 08:47:20'),
(7, 'Holiday Party Package', 'Christmas or New Year party with buffet and entertainment options.', 85000.00, 'Holiday', 'active', '2025-10-12 08:47:20'),
(8, 'Graduation Party Package', 'Includes simple catering, tarpaulin, and sound system.', 35000.00, 'Graduation', 'active', '2025-10-12 08:47:20'),
(9, 'Engagement Party Package', 'Intimate gathering with floral arrangements and photography.', 65000.00, 'Engagement', 'active', '2025-10-12 08:47:20'),
(10, 'Reunion Event Package', 'Perfect for family or class reunions with buffet and photo booth.', 55000.00, 'Reunion', 'active', '2025-10-12 08:47:20');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_services`
--

CREATE TABLE `tbl_services` (
  `service_id` int(11) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `service_description` text DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `customizable` tinyint(1) DEFAULT 0,
  `customization_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`customization_options`)),
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_services`
--

INSERT INTO `tbl_services` (`service_id`, `service_name`, `service_description`, `base_price`, `category`, `customizable`, `customization_options`, `status`, `created_at`) VALUES
(1, 'Catering Service', 'Professional food and beverage service for your event', 15000.00, 'Catering', 1, '{\"options\": {\"guests\": {\"type\": \"number\", \"min\": 50, \"max\": 200, \"price_per_unit\": 300}, \"premium_menu\": {\"type\": \"boolean\", \"price\": 8000}, \"dessert_station\": {\"type\": \"boolean\", \"price\": 5000}}}', 'active', '2025-10-12 08:47:20'),
(2, 'Decoration Setup', 'Transform your venue with beautiful decor and themes', 10000.00, 'Decorations', 1, '{\"options\": {\"centerpieces\": {\"type\": \"number\", \"min\": 5, \"max\": 20, \"price_per_unit\": 800}, \"arch_decoration\": {\"type\": \"boolean\", \"price\": 5000}, \"premium_flowers\": {\"type\": \"boolean\", \"price\": 3000}}}', 'active', '2025-10-12 08:47:20'),
(3, 'Photography & Videography', 'Capture your special moments professionally', 12000.00, 'Photography', 1, '{\"options\": {\"hours\": {\"type\": \"number\", \"min\": 4, \"max\": 12, \"price_per_unit\": 1875}, \"photographers\": {\"type\": \"number\", \"min\": 1, \"max\": 3, \"price_per_unit\": 5000}, \"album\": {\"type\": \"boolean\", \"price\": 8000}}}', 'active', '2025-10-12 08:47:20'),
(4, 'Sound System & Lights', 'Professional audio and lighting for perfect ambiance', 8000.00, 'Audio-Visual', 0, NULL, 'active', '2025-10-12 08:47:20'),
(5, 'Entertainment', 'Keep your guests entertained throughout the event', 10000.00, 'Entertainment', 0, NULL, 'active', '2025-10-12 08:47:20'),
(6, 'Event Coordination', 'Professional event management and coordination', 15000.00, 'Coordination', 0, NULL, 'active', '2025-10-12 08:47:20');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_service_details`
--

CREATE TABLE `tbl_service_details` (
  `detail_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `detail_name` varchar(255) NOT NULL,
  `price_min` decimal(10,2) DEFAULT NULL,
  `price_max` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_service_details`
--

INSERT INTO `tbl_service_details` (`detail_id`, `service_id`, `detail_name`, `price_min`, `price_max`) VALUES
(1, 1, 'Buffet Style', 15000.00, 35000.00),
(2, 1, 'Plated Service', 25000.00, 50000.00),
(3, 1, 'Cocktail Setup', 20000.00, 40000.00),
(4, 2, 'Classic Theme', 10000.00, 25000.00),
(5, 2, 'Modern Theme', 20000.00, 40000.00),
(6, 2, 'Floral Arrangements', 5000.00, 20000.00),
(7, 3, 'Basic Package', 12000.00, 25000.00),
(8, 3, 'Premium Package', 30000.00, 50000.00),
(9, 3, 'Drone Coverage', 8000.00, 15000.00),
(10, 4, 'Basic Sound Setup', 8000.00, 15000.00),
(11, 4, 'Full DJ Equipment', 15000.00, 25000.00);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_service_features`
--

CREATE TABLE `tbl_service_features` (
  `feature_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `feature_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_service_features`
--

INSERT INTO `tbl_service_features` (`feature_id`, `service_id`, `feature_name`) VALUES
(1, 1, 'Menu Planning'),
(2, 1, 'Professional Staff'),
(3, 1, 'Food Safety Certified'),
(4, 2, 'Theme Consultation'),
(5, 2, 'Setup & Teardown'),
(6, 2, 'Custom Designs'),
(7, 3, 'Professional Equipment'),
(8, 3, 'Edited Photos/Videos'),
(9, 3, 'Online Gallery'),
(10, 4, 'Professional Audio'),
(11, 4, 'Lighting Effects'),
(12, 4, 'Technician Support'),
(13, 5, 'Professional Performers'),
(14, 5, 'Equipment Included'),
(15, 5, 'Music Coordination'),
(16, 6, 'Timeline Management'),
(17, 6, 'Vendor Coordination'),
(18, 6, 'Day-of Supervision');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_transactions`
--

CREATE TABLE `tbl_transactions` (
  `transaction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ref_id` varchar(50) DEFAULT NULL,
  `date_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('PAID','PENDING','CANCELLED') NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_transactions`
--

INSERT INTO `tbl_transactions` (`transaction_id`, `user_id`, `ref_id`, `date_time`, `status`, `price`) VALUES
(1, 19, 'TX8M1P3Q', '2025-10-12 13:49:39', 'PAID', 0.00),
(2, 19, 'REF-45821', '2025-10-12 13:49:39', 'PAID', 0.00),
(3, 19, 'ID-9J7A2L5', '2025-10-12 13:49:39', 'PAID', 0.00),
(4, 1, 'REF-7B2K9F4R', '2024-01-15 02:30:00', 'PAID', 150.00),
(5, 1, 'REF-TX8M1P3Q', '2024-01-16 06:45:00', 'PAID', 300.00),
(6, 1, 'REF-9J7A2L5M', '2024-01-17 01:15:00', 'PENDING', 76.00),
(7, 1, 'REF-45821903', '2024-01-18 08:20:00', 'PAID', 420.00),
(8, 1, 'REF-16408572', '2024-01-19 03:00:00', 'PAID', 200.00),
(9, 1, 'REF-73049216', '2024-01-20 05:30:00', 'PAID', 90.00),
(10, 1, 'REF-28517364', '2024-01-21 07:45:00', 'PENDING', 325.00),
(11, 1, 'REF-57291834', '2024-01-22 00:00:00', 'PAID', 156.00),
(12, 19, 'REF-7B2K9F4R', '2024-01-15 02:30:00', 'PAID', 150.00),
(13, 19, 'REF-TX8M1P3Q', '2024-01-16 06:45:00', 'PAID', 300.00),
(14, 19, 'REF-9J7A2L5M', '2024-01-17 01:15:00', 'PENDING', 76.00),
(15, 19, 'REF-45821903', '2024-01-18 08:20:00', 'PAID', 420.00),
(16, 19, 'REF-16408572', '2024-01-19 03:00:00', 'PAID', 200.00),
(17, 19, 'REF-73049216', '2024-01-20 05:30:00', 'PAID', 90.00),
(18, 19, 'REF-28517364', '2024-01-21 07:45:00', 'PENDING', 325.00),
(19, 19, 'REF-57291834', '2024-01-22 00:00:00', 'PAID', 156.00),
(20, 19, 'EVT-20251013-031953-68ec53b97710a', '2025-10-13 01:20:09', 'PAID', 9500000.00),
(21, 19, 'EVT-20251013-031953-68ec53b97710a', '2025-10-13 01:20:09', 'PAID', 9500000.00),
(22, 19, 'EVT-20251013-032107-68ec5403ef022', '2025-10-13 01:21:12', 'CANCELLED', 7500000.00),
(23, 19, 'EVT-20251013-032107-68ec5403ef022', '2025-10-13 01:21:12', 'CANCELLED', 7500000.00);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `zip` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `otp` int(11) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'inactive',
  `otp_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`user_id`, `first_name`, `last_name`, `email`, `phone`, `city`, `zip`, `address`, `password`, `otp`, `status`, `otp_expiry`) VALUES
(1, 'Pearl', 'Hobbs', 'hefanivuw@mailinator.com', '278', 'Dolore quo eiusmod e', '87142', 'Quaerat qui Nam est ', 'Pa$$w0rd!', NULL, 'inactive', NULL),
(2, 'Kitra', 'Levine', 'mixumaxan@mailinator.com', '868', 'Asperiores laudantiu', '43903', 'Quidem beatae in des', '$2y$10$lGxzxkea8jO25tOM2Be3xe74eApw7p0lMIW2/rkmafZLvqUQPIhjW', NULL, 'inactive', NULL),
(3, 'Amethyst', 'Bush', 'lejiligy@mailinator.com', '611', 'Sit modi irure verit', '22985', 'Deleniti officiis qu', '$2y$10$2eOtwsXOxZgl/cilPXaH5eFGXCsvOwNbCSjIIZ8hDfxvqhyFlqLQa', NULL, 'inactive', NULL),
(4, 'Whitney', 'Valencia', 'guxypi@mailinator.com', '974', 'Occaecat aut quasi n', '13318', 'Est aliquid facere n', '$2y$10$GSinIqlOPqpttZWFdV2zluke9HolLlHSMmmHJbNq.M3q221HVTkUC', NULL, 'inactive', NULL),
(5, 'Brooke', 'Francis', 'givebufe@mailinator.com', '202', 'Dolore id enim beata', '63259', 'Assumenda doloremque', '$2y$10$52e6gwKnMZFeOyarB2FQk.qkyHbj59NuYYx5Oh5mW2/PK8lXSd.2C', NULL, 'inactive', NULL),
(6, 'kristan', 'almario', 'kristan@gmail.com', '2', 'Ea est doloremque om', '92927', 'Architecto expedita ', '$2y$10$M0mTGq/2iK5NggcIot/iaubd.HnJnvT5cRMH0gYg01nFdIVQUzY02', 9951, 'inactive', '2025-10-09 17:54:04'),
(7, 'Aladdin', 'Potts', 'leka@mailinator.com', '255', 'Quia omnis laborum q', '15734', 'Architecto et quo au', '$2y$10$sHxMF4XObXc8vsfvU2FMaOf.lQrgKkwMz0IulmUX76b8BS3amd6Bu', 5881, 'inactive', NULL),
(8, 'Caldwell', 'Wong', 'riwubevu@mailinator.com', '706', 'Sint veritatis asper', '89258', 'Fugiat placeat qui', '$2y$10$xzVKlFza/EPpOqlWzORTS./C5MmFXzcth5cNjRsN9C5osnHPff6gm', 8772, 'inactive', NULL),
(9, 'Sloane', 'Burris', 'tewez@mailinator.com', '702', 'Tempora reprehenderi', '22435', 'Laboriosam odio non', '$2y$10$AboQ8KWyDHVYv5g/HxdVreAcf4yFMHSf.trOfY016X4duYovIhh3G', 5492, 'inactive', NULL),
(14, 'Mannix', 'Hancock', 'kyku@mailinator.com', '340', 'Minim libero proiden', '16476', 'Voluptatem laboriosa', '$2y$10$87fsMgUsZL2ecihhFUmJ/O0Otw1eo0EtYlqx0ZGJ/Py3x/YhYnbuS', 3034, 'inactive', '2025-10-09 11:50:49'),
(15, 'Alisa', 'Kirk', 'fany@mailinator.com', '189', 'Aute dolor nesciunt', '35980', 'Qui placeat ad dolo', '$2y$10$okN.p.s8vzMzu6qhvD9oRecvtq3kzjMvwJkJTrKAf4vUq7oD.Q1sW', 9988, 'inactive', '2025-10-09 17:52:42'),
(19, 'Lee', 'Burgess', 'kristancharles67@gmail.com', '130', 'Id non delectus ra', '56289', 'Sed adipisicing aspe', '$2y$10$irPinsyfcaGe0ErsIVW5ve9TIeoRbpMoA2EZ4WzMh.ImJhdwhEpFm', NULL, 'active', NULL),
(20, 'Timon', 'Chapman', 'civazuqar@mailinator.com', '993', 'Aut cumque sed autem', '10330', 'Molestiae non quibus', '$2y$10$v7N5MxsY99BBoRzX/B6dzOaxo7RLOvtWnrrohCtgGtd89LGWMq.dy', NULL, 'inactive', NULL),
(21, 'Silas', 'Silva', 'nopu@mailinator.com', '107', 'Culpa dolor quo offi', '46834', 'Similique perferendi', '$2y$10$05AZzj8PxwS3CDVoYW87zOxPGpFqdmp5w8.rLXsPMsdwQupbdvTo2', 7228, 'inactive', '2025-10-10 18:01:11'),
(22, 'Gloria', 'Schwartz', 'godefosiwa@mailinator.com', '915', 'Aut architecto ut co', '62654', 'Quia et do debitis q', '$2y$10$KObeY7aWywX4cypwXBT7QO4oTuTIPqS8xIrsv1fuwYWpieaGAWSqO', 6406, 'inactive', '2025-10-10 18:02:02'),
(23, 'Tobias', 'Cooper', 'pesywymuva@mailinator.com', '841', 'Nostrum eos dolore ', '96555', 'Laboris ducimus sit', '$2y$10$3TCA2ZshhinBLC.eom76FOcMpGa9WAl/FbVCoHDPJ250hNaSqjn9a', 6283, 'inactive', '2025-10-10 18:02:46'),
(24, 'Rajah', 'Davenport', 'kegurugeb@mailinator.com', '783', 'Esse rem veniam dol', '53054', 'Consequatur Quis an', '$2y$10$LLKFIuY4hF1LOXyWohNuE.ptz19OxcdDb4t8KUlC9A8r.pW1tY0Ma', 7047, 'inactive', '2025-10-10 18:03:17'),
(25, 'Jelani', 'Meadows', 'gokop@mailinator.com', '783', 'Esse rem veniam dol', '53054', 'Consequatur Quis an', '$2y$10$LXNnsFov1c8Syp5T0AYgE.drLrIl8/21Kp3.iWPZW15OPlgLYPa7W', 5784, 'inactive', '2025-10-10 18:04:29'),
(26, 'Allen', 'Avila', 'wenahamiwy@mailinator.com', '814', 'Voluptatem architec', '72843', 'Natus officia nihil ', '$2y$10$OF7ShGGae5ht3986hTYgieXbfDtgcCA66otNudTHE98k9sYP8SF1W', 8030, 'inactive', '2025-10-10 18:05:20'),
(27, 'Owen', 'Terry', 'bylefef@mailinator.com', '610', 'Tenetur aut expedita', '25543', 'Eiusmod hic quod ame', '$2y$10$hNxOXzfpH53601lIOb9iL.ngIx4rNneaCq/D4WBu31Kr2zR67BfWi', 3883, 'inactive', '2025-10-10 18:08:56'),
(28, 'Georgia', 'Barton', 'xojotam@mailinator.com', '721', 'Amet aut amet plac', '99878', 'Aut eveniet velit d', '$2y$10$NGmZQXK/mQYLfD7am6hjFOMuOSvP/fJ1b6my.px.PvQupwlZ8CtM6', 2549, 'inactive', '2025-10-10 18:09:41'),
(29, 'Fitzgerald', 'Talley', 'bivufugo@mailinator.com', '335', 'Iste rem enim quis n', '42694', 'Eius eveniet animi', '$2y$10$ywI3IzyoCsiwTzAbUvgmMuRsrlRX.IaHHkvdlObH2yy2EMwcqCLS.', 3351, 'inactive', '2025-10-10 18:10:34'),
(30, 'Mallory', 'Romero', 'jiqijeluwy@mailinator.com', '425', 'Sit assumenda irure ', '97099', 'Voluptatem maiores r', '$2y$10$RRPGmN6KMBnNtNo2.qQrX.quRi3Zi9mfsZMCdiZvNx5SfM2p9ow0W', 3331, 'inactive', '2025-10-10 18:11:26'),
(31, 'Neville', 'Kane', 'riwyrogib@mailinator.com', '704', 'Deleniti perspiciati', '94642', 'Dolorem optio quos ', '$2y$10$XOhLIrwijrGl9ZSiMmumD.BffWPpkRhx.d1TU7o/lmAdjAjNWRkw.', 3435, 'inactive', '2025-10-10 18:11:47'),
(32, 'Admin', 'Eventia', 'admin@eventia.com', '09171234567', 'Manila', '1000', '123 Admin Street', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'active', NULL),
(33, 'Pandora', 'King', 'bagese@mailinator.com', '709', 'Laborum Quas earum ', '30549', 'Maiores magnam excep', '$2y$10$ezZTaDHNNS4vIIbtcO.qMe2vQEBocBnoDRzbAJyxjaBzysGN3Bjdm', 8632, 'inactive', '2025-10-12 23:01:07'),
(34, 'Cade', 'Stone', 'qyme@mailinator.com', '894', 'Voluptas duis omnis ', '46302', 'Possimus unde bland', '$2y$10$0YqfuA6YQq4feroUy0jD9.sSeaY7fRWpfE7/PwHr/5BMPkMV99Cjy', 2786, 'inactive', '2025-10-12 23:06:25'),
(35, 'Scarlett', 'Saunders', 'sefe@mailinator.com', '944', 'Cupiditate dolorem o', '53941', 'Ut reprehenderit quo', '$2y$10$pSXTQTI1zqfduXndWGnFmeT./SMtyxJ8/KwziwrzTcH0/wrOTgycW', 5429, 'inactive', '2025-10-12 23:19:30'),
(36, 'Eugenia', 'Sloan', 'kina@mailinator.com', '501', 'Enim duis ea dolorum', '74078', 'Id veniam commodo p', '$2y$10$UfE5M2jIvonoZFfZUTquN.s9U3rhfkIi.gOEp9CZAyoJN00OAODsi', 5021, 'inactive', '2025-10-12 23:27:26'),
(37, 'Eleanor', 'Lott', 'nujufuceso@mailinator.com', '442', 'Ipsum nesciunt do t', '21945', 'Do est labore incidi', '$2y$10$va2d7JlnZwN9SYEP8J8K7edXCYwwJdH8JLoLcWviJam3qQga.MrbW', 2690, 'inactive', '2025-10-12 23:28:17'),
(38, 'Len', 'Bartlett', 'gosyboqomu@mailinator.com', '923', 'Labore autem est in ', '12803', 'Eum explicabo Ipsum', '$2y$10$WiG9aupilb0rWPUONfeZZuuoZnUPwyDkxhcX1xvmprKPMuIo8SQEC', 7644, 'inactive', '2025-10-12 23:35:52'),
(39, 'Tamara', 'Kidd', 'zygupo@mailinator.com', '511', 'Similique unde rem s', '29997', 'Tempor mollit soluta', '$2y$10$ILQXHjyRhziNpotUPnPKYuuobM1m//auqkMQfRgAnsIyO5tGDzLMi', 2995, 'inactive', '2025-10-12 23:36:52'),
(40, 'Cain', 'Tran', 'kywedumisi@mailinator.com', '651', 'Quo laboris error ci', '99738', 'Ipsum vel officia qu', '$2y$10$vTPkx2kUx7HJXmFJDHe8GuCgPe.P4Nk9VF1LN4qmbrzvZ2.DROL4q', 3251, 'inactive', '2025-10-12 23:38:59');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_venues`
--

CREATE TABLE `tbl_venues` (
  `venue_id` int(11) NOT NULL,
  `venue_name` varchar(255) NOT NULL,
  `venue_type` varchar(100) NOT NULL,
  `capacity` int(11) NOT NULL,
  `location` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `amenities` text DEFAULT NULL,
  `status` enum('available','unavailable') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_venues`
--

INSERT INTO `tbl_venues` (`venue_id`, `venue_name`, `venue_type`, `capacity`, `location`, `price`, `description`, `image_url`, `amenities`, `status`, `created_at`) VALUES
(1, 'The Garden Pavilion', 'Open-air', 100, 'Quezon City', 15000.00, 'Beautiful outdoor garden venue perfect for intimate gatherings and garden weddings', NULL, 'Garden seating, Natural lighting, Outdoor stage, Parking space', 'available', '2025-10-12 08:47:21'),
(2, 'Grand Ballroom', 'Indoor Hall', 300, 'Makati City', 35000.00, 'Elegant indoor ballroom with crystal chandeliers and sophisticated ambiance', NULL, 'Air conditioning, Sound system, Stage, Bridal room, Parking', 'available', '2025-10-12 08:47:21'),
(3, 'Seaside View Hall', 'Beachfront', 150, 'Batangas', 25000.00, 'Stunning beachfront venue with panoramic ocean views', NULL, 'Beach access, Sunset view, Outdoor seating, Parking, Changing rooms', 'available', '2025-10-12 08:47:21'),
(4, 'Executive Conference Hall', 'Conference', 150, 'Taguig City', 40000.00, 'Modern conference facility with AV equipment', NULL, 'AV equipment, Conference tables, WiFi, Catering kitchen', 'available', '2025-10-12 08:47:21'),
(5, 'Rooftop Terrace', 'Rooftop', 100, 'Mandaluyong City', 45000.00, 'Panoramic city views with modern amenities', NULL, 'City views, Modern furniture, Bar setup, Lighting', 'available', '2025-10-12 08:47:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_admin`
--
ALTER TABLE `tbl_admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_bookings`
--
ALTER TABLE `tbl_bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD UNIQUE KEY `booking_reference` (`booking_reference`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `venue_id` (`venue_id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `idx_booking_reference` (`booking_reference`),
  ADD KEY `idx_booking_email` (`contact_email`),
  ADD KEY `idx_booking_date` (`event_date`),
  ADD KEY `idx_booking_status` (`booking_status`);

--
-- Indexes for table `tbl_booking_services`
--
ALTER TABLE `tbl_booking_services`
  ADD PRIMARY KEY (`booking_service_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `tbl_events`
--
ALTER TABLE `tbl_events`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `tbl_packages`
--
ALTER TABLE `tbl_packages`
  ADD PRIMARY KEY (`package_id`),
  ADD KEY `idx_package_status` (`status`);

--
-- Indexes for table `tbl_services`
--
ALTER TABLE `tbl_services`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `idx_service_status` (`status`);

--
-- Indexes for table `tbl_service_details`
--
ALTER TABLE `tbl_service_details`
  ADD PRIMARY KEY (`detail_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `tbl_service_features`
--
ALTER TABLE `tbl_service_features`
  ADD PRIMARY KEY (`feature_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `tbl_transactions`
--
ALTER TABLE `tbl_transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `tbl_venues`
--
ALTER TABLE `tbl_venues`
  ADD PRIMARY KEY (`venue_id`),
  ADD KEY `idx_venue_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_admin`
--
ALTER TABLE `tbl_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_bookings`
--
ALTER TABLE `tbl_bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_booking_services`
--
ALTER TABLE `tbl_booking_services`
  MODIFY `booking_service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_events`
--
ALTER TABLE `tbl_events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tbl_packages`
--
ALTER TABLE `tbl_packages`
  MODIFY `package_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbl_services`
--
ALTER TABLE `tbl_services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_service_details`
--
ALTER TABLE `tbl_service_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tbl_service_features`
--
ALTER TABLE `tbl_service_features`
  MODIFY `feature_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `tbl_transactions`
--
ALTER TABLE `tbl_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `tbl_venues`
--
ALTER TABLE `tbl_venues`
  MODIFY `venue_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_bookings`
--
ALTER TABLE `tbl_bookings`
  ADD CONSTRAINT `tbl_bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tbl_bookings_ibfk_2` FOREIGN KEY (`venue_id`) REFERENCES `tbl_venues` (`venue_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tbl_bookings_ibfk_3` FOREIGN KEY (`package_id`) REFERENCES `tbl_packages` (`package_id`) ON DELETE SET NULL;

--
-- Constraints for table `tbl_booking_services`
--
ALTER TABLE `tbl_booking_services`
  ADD CONSTRAINT `tbl_booking_services_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `tbl_bookings` (`booking_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_booking_services_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `tbl_services` (`service_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_service_details`
--
ALTER TABLE `tbl_service_details`
  ADD CONSTRAINT `tbl_service_details_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `tbl_services` (`service_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_service_features`
--
ALTER TABLE `tbl_service_features`
  ADD CONSTRAINT `tbl_service_features_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `tbl_services` (`service_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_transactions`
--
ALTER TABLE `tbl_transactions`
  ADD CONSTRAINT `tbl_transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;