-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 10, 2026 at 01:22 PM
-- Server version: 9.6.0
-- PHP Version: 8.5.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `Clear_Bay`
--

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--

CREATE TABLE `alerts` (
  `id` int UNSIGNED NOT NULL,
  `ambulance_id` int UNSIGNED NOT NULL,
  `hospital_id` int UNSIGNED NOT NULL,
  `alert_type` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `triggered_at` datetime NOT NULL,
  `acknowledged_at` datetime DEFAULT NULL,
  `acknowledged_by` int UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alerts`
--

INSERT INTO `alerts` (`id`, `ambulance_id`, `hospital_id`, `alert_type`, `triggered_at`, `acknowledged_at`, `acknowledged_by`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 'Wait Time Exceeded', '2026-05-28 10:03:51', '2026-06-01 18:18:15', 5, '2026-05-28 10:03:51', '2026-06-01 18:18:15'),
(2, 2, 1, 'Wait Time Exceeded', '2026-05-28 10:03:51', '2026-06-03 14:32:39', 5, '2026-05-28 10:03:51', '2026-06-03 14:32:39'),
(3, 3, 2, 'Wait Time Exceeded', '2026-05-28 10:03:51', '2026-06-03 14:32:39', 5, '2026-05-28 10:03:51', '2026-06-03 14:32:39'),
(4, 4, 5, 'Wait Time Exceeded', '2026-05-28 10:03:51', '2026-06-03 14:32:40', 5, '2026-05-28 10:03:51', '2026-06-03 14:32:40'),
(5, 6, 2, 'Wait Time Exceeded', '2026-06-01 18:32:59', '2026-06-03 14:32:39', 5, '2026-06-01 18:32:59', '2026-06-03 14:32:39'),
(6, 1, 1, 'Wait Time Exceeded', '2026-06-01 18:59:24', '2026-06-03 14:32:39', 5, '2026-06-01 18:59:24', '2026-06-03 14:32:39'),
(7, 1, 3, 'Wait Time Exceeded', '2026-06-02 13:35:54', '2026-06-03 14:32:39', 5, '2026-06-02 13:35:54', '2026-06-03 14:32:39'),
(8, 2, 4, 'Wait Time Exceeded', '2026-06-02 13:35:54', '2026-06-02 13:45:39', 5, '2026-06-02 13:35:54', '2026-06-02 13:45:39'),
(9, 6, 1, 'Wait Time Exceeded', '2026-06-02 13:35:54', '2026-06-03 14:32:39', 5, '2026-06-02 13:35:54', '2026-06-03 14:32:39'),
(10, 2, 4, 'Wait Time Exceeded', '2026-06-02 13:45:39', '2026-06-03 14:32:38', 5, '2026-06-02 13:45:39', '2026-06-03 14:32:38'),
(11, 2, 3, 'Wait Time Exceeded', '2026-06-08 07:41:38', NULL, NULL, '2026-06-08 07:41:38', '2026-06-08 07:41:38');

-- --------------------------------------------------------

--
-- Table structure for table `ambulances`
--

CREATE TABLE `ambulances` (
  `id` int UNSIGNED NOT NULL,
  `unit_id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `provider` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `ems_provider_id` int UNSIGNED DEFAULT NULL,
  `registration` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `current_lat` decimal(10,8) DEFAULT NULL,
  `current_lng` decimal(11,8) DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Available',
  `last_updated` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ambulances`
--

INSERT INTO `ambulances` (`id`, `unit_id`, `provider`, `ems_provider_id`, `registration`, `current_lat`, `current_lng`, `status`, `last_updated`, `created_at`, `updated_at`) VALUES
(1, 'AAR-04', 'AAR Healthcare', 1, 'KBY 104A', -1.30800000, 36.80200000, 'Available', '2026-06-10 13:19:27', '2026-05-21 17:21:47', '2026-06-10 13:14:29'),
(2, 'KRC-12', 'Kenya Red Cross', 2, 'KBZ 512B', -1.29800000, 36.81500000, 'Transporting', '2026-06-10 10:46:43', '2026-05-21 17:21:47', '2026-06-10 10:46:43'),
(3, 'NBO-07', 'Nairobi County', 3, 'KCG 007G', -1.28800000, 36.88500000, 'Transporting', '2026-06-10 13:19:58', '2026-05-21 17:21:47', '2026-06-10 13:19:58'),
(4, 'AAR-09', 'AAR Healthcare', 1, 'KBY 109A', -1.29220000, 36.80900000, 'Transporting', '2026-06-02 11:32:43', '2026-05-21 17:21:47', '2026-05-21 17:21:47'),
(5, 'KRC-05', 'Kenya Red Cross', 2, 'KBZ 505B', -1.26100000, 36.80900000, 'Transporting', '2026-06-03 14:19:19', '2026-05-21 17:21:47', '2026-06-03 14:19:19'),
(6, 'AAR-02', 'AAR Healthcare', 1, 'KBY 102A', -10.30900000, 36.80100000, 'Queued', '2026-06-02 11:32:43', '2026-05-21 17:21:47', '2026-06-06 12:43:19');

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `table_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `record_id` int UNSIGNED NOT NULL,
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`id`, `user_id`, `action`, `table_name`, `record_id`, `timestamp`) VALUES
(1, NULL, 'Automated alert generated: wait time > 30 min', 'alerts', 1, '2026-05-28 10:03:51'),
(2, NULL, 'Automated alert generated: wait time > 30 min', 'alerts', 2, '2026-05-28 10:03:51'),
(3, NULL, 'Automated alert generated: wait time > 30 min', 'alerts', 3, '2026-05-28 10:03:51'),
(4, NULL, 'Automated alert generated: wait time > 30 min', 'alerts', 4, '2026-05-28 10:03:51'),
(5, NULL, 'Automated alert generated: wait time > 30 min', 'alerts', 5, '2026-06-01 18:32:59'),
(6, NULL, 'Automated alert generated: wait time > 30 min', 'alerts', 6, '2026-06-01 18:59:24'),
(7, NULL, 'Automated alert generated: wait time > 30 min', 'alerts', 7, '2026-06-02 13:35:54'),
(8, NULL, 'Automated alert generated: wait time > 30 min', 'alerts', 8, '2026-06-02 13:35:54'),
(9, NULL, 'Automated alert generated: wait time > 30 min', 'alerts', 9, '2026-06-02 13:35:54'),
(10, NULL, 'Automated alert generated: wait time > 30 min', 'alerts', 10, '2026-06-02 13:45:39'),
(11, NULL, 'Automated alert generated: wait time > 30 min', 'alerts', 11, '2026-06-08 07:41:38');

-- --------------------------------------------------------

--
-- Table structure for table `ci_sessions`
--

CREATE TABLE `ci_sessions` (
  `id` varchar(128) COLLATE utf8mb4_general_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  `timestamp` int UNSIGNED NOT NULL DEFAULT '0',
  `data` mediumblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ems_providers`
--

CREATE TABLE `ems_providers` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `type` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `contact_phone` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ems_providers`
--

INSERT INTO `ems_providers` (`id`, `name`, `type`, `contact_phone`, `active`, `created_at`, `updated_at`) VALUES
(1, 'AAR Healthcare', 'Private', '+254711090000', 1, '2026-05-28 09:04:19', '2026-05-28 09:04:19'),
(2, 'Kenya Red Cross', 'NGO', '+254700395395', 1, '2026-05-28 09:04:19', '2026-05-28 09:04:19'),
(3, 'Nairobi County Services', 'Public', '+254202222181', 1, '2026-05-28 09:04:19', '2026-05-28 09:04:19');

-- --------------------------------------------------------

--
-- Table structure for table `handovers`
--

CREATE TABLE `handovers` (
  `id` int UNSIGNED NOT NULL,
  `pre_notification_id` int UNSIGNED DEFAULT NULL,
  `ambulance_id` int UNSIGNED NOT NULL,
  `hospital_id` int UNSIGNED NOT NULL,
  `patient_age` int NOT NULL,
  `patient_gender` char(1) COLLATE utf8mb4_general_ci NOT NULL,
  `acuity` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `eta_minutes` int NOT NULL DEFAULT '0',
  `wait_time_minutes` int NOT NULL DEFAULT '0',
  `status` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'En route',
  `arrived_at` datetime DEFAULT NULL,
  `handover_complete_at` datetime DEFAULT NULL,
  `bay_number` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `notes` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `completed_by` int UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `handovers`
--

INSERT INTO `handovers` (`id`, `pre_notification_id`, `ambulance_id`, `hospital_id`, `patient_age`, `patient_gender`, `acuity`, `eta_minutes`, `wait_time_minutes`, `status`, `arrived_at`, `handover_complete_at`, `bay_number`, `notes`, `completed_by`, `created_at`, `updated_at`) VALUES
(24, 7, 2, 5, 678, 'M', 'Critical', 5, 0, 'Cleared', NULL, NULL, NULL, NULL, NULL, '2026-06-03 13:37:32', '2026-06-03 13:39:05'),
(25, 8, 2, 5, 234, 'M', 'Serious', 5, 0, 'Cleared', NULL, NULL, NULL, NULL, NULL, '2026-06-03 13:39:57', '2026-06-03 13:40:08'),
(26, 9, 2, 3, 123, 'F', 'Serious', 7, 0, 'Cleared', NULL, NULL, NULL, NULL, NULL, '2026-06-03 13:49:17', '2026-06-03 14:19:02'),
(27, 10, 5, 4, 678, 'F', 'Serious', 2, 0, 'Cleared', NULL, NULL, NULL, NULL, NULL, '2026-06-03 14:19:19', '2026-06-03 14:20:12'),
(28, 11, 2, 3, 790, 'M', 'Critical', 7, 0, 'Cleared', NULL, NULL, NULL, NULL, NULL, '2026-06-03 14:35:00', '2026-06-03 14:35:28'),
(29, 12, 2, 3, 4465, 'M', 'Serious', 7, 2, 'Cleared', NULL, '2026-06-03 15:21:46', 'zxcvb', 'zxcvbn', 2, '2026-06-03 15:20:01', '2026-06-03 15:21:47'),
(30, 13, 2, 1, 12345, 'F', 'Serious', 4, 2, 'Cleared', '2026-06-04 15:06:02', '2026-06-04 15:07:49', '', '', 1, '2026-06-04 15:01:12', '2026-06-04 15:07:49'),
(31, 14, 2, 3, 678, 'F', 'Serious', 7, 1, 'Cleared', '2026-06-04 15:09:50', '2026-06-04 15:10:42', '', 'fghjk', 2, '2026-06-04 15:09:01', '2026-06-04 15:10:42'),
(32, 15, 2, 3, 6789, 'F', 'Serious', 7, 0, 'Cleared', '2026-06-06 12:15:50', NULL, NULL, NULL, NULL, '2026-06-06 12:08:47', '2026-06-06 12:18:04'),
(33, 16, 2, 3, 1234543, 'F', 'Serious', 7, 0, 'Cleared', '2026-06-06 12:22:35', '2026-06-06 12:22:35', 'bay 7', 'asdfghjklkjh', 2, '2026-06-06 12:18:32', '2026-06-06 12:22:35'),
(34, 17, 2, 3, 119, 'M', 'Serious', 10003, 0, 'Cleared', '2026-06-08 06:59:50', '2026-06-08 07:00:05', '2345675', 'gfdfg', 2, '2026-06-08 06:53:20', '2026-06-08 07:00:05'),
(35, 18, 2, 3, 4555, 'M', 'Stable', 10003, 59, 'Cleared', '2026-06-08 07:10:58', '2026-06-08 08:09:56', 'bay5', 'dfghjkjh', 2, '2026-06-08 07:10:28', '2026-06-08 08:09:56'),
(36, 19, 2, 4, 4567, 'F', 'Serious', 18, 0, 'Cleared', NULL, NULL, NULL, NULL, NULL, '2026-06-08 09:26:04', '2026-06-08 09:27:00'),
(37, 20, 2, 3, 456345, 'F', 'Serious', 11, 19, 'Cleared', '2026-06-08 09:28:28', '2026-06-08 09:47:07', '67876', 'm,kjh', 2, '2026-06-08 09:28:00', '2026-06-08 09:47:07'),
(38, 21, 2, 1, 48, 'F', 'Serious', 6, 0, 'En route', NULL, NULL, NULL, NULL, NULL, '2026-06-10 10:46:43', '2026-06-10 10:46:43'),
(39, 22, 1, 1, 23, 'F', 'Serious', 5, 0, 'Cleared', '2026-06-10 13:13:24', '2026-06-10 13:13:31', 'vdvdv', 'dffv', 1, '2026-06-10 13:11:33', '2026-06-10 13:13:31'),
(40, 23, 1, 1, 654, 'M', 'Serious', 34, 0, 'Cleared', '2026-06-10 13:19:24', '2026-06-10 13:19:27', '', 'hjkllk', 1, '2026-06-10 13:14:29', '2026-06-10 13:19:27'),
(41, 24, 3, 1, 678, 'F', 'Serious', 34, 0, 'En route', NULL, NULL, NULL, NULL, NULL, '2026-06-10 13:19:58', '2026-06-10 13:19:58');

-- --------------------------------------------------------

--
-- Table structure for table `hospitals`
--

CREATE TABLE `hospitals` (
  `id` int UNSIGNED NOT NULL,
  `code` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Green',
  `bays_available` int DEFAULT '0',
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `address` text COLLATE utf8mb4_general_ci,
  `contact_phone` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hospitals`
--

INSERT INTO `hospitals` (`id`, `code`, `name`, `category`, `status`, `bays_available`, `lat`, `lng`, `address`, `contact_phone`, `active`, `created_at`, `updated_at`) VALUES
(1, 'KNH', 'Kenyatta National Hospital', 'National Referral · Public', 'Red', 3, -1.30130000, 36.80800000, 'Hospital Rd, Nairobi', '+254202726300', 1, '2026-05-21 17:21:47', '2026-05-21 17:21:47'),
(2, 'MLK', 'Mama Lucy Kibaki Hospital', 'County Referral · Public', 'Amber', 1, -1.27850000, 36.90300000, 'Kangundo Rd, Umoja', '+254202100922', 1, '2026-05-21 17:21:47', '2026-05-21 17:21:47'),
(3, 'MBG', 'Mbagathi County Hospital', 'County Referral · Public', 'AMBER', 0, -1.30900000, 36.80100000, 'Mbagathi Way, Nairobi', '+254202724712', 1, '2026-05-21 17:21:47', '2026-06-10 00:26:54'),
(4, 'AKU', 'Aga Khan University Hospital', 'Teaching Hospital  .  Private', 'Green', 4, -1.26500000, 36.80700000, '3rd Parklands Ave, Nairobi', '+254203662000', 1, '2026-05-21 17:21:47', '2026-06-08 08:52:15'),
(5, 'NBO', 'Nairobi Hospital', 'Referral Hospital · Private', 'Green', 4, -1.29520000, 36.80480000, 'Argwings Kodhek Rd, Nairobi', '+254202845000', 1, '2026-05-21 17:21:47', '2026-05-21 17:21:47');

-- --------------------------------------------------------

--
-- Table structure for table `hospital_status`
--

CREATE TABLE `hospital_status` (
  `id` int UNSIGNED NOT NULL,
  `hospital_id` int UNSIGNED NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `bays_available` int NOT NULL,
  `updated_by` int UNSIGNED NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hospital_status`
--

INSERT INTO `hospital_status` (`id`, `hospital_id`, `status`, `bays_available`, `updated_by`, `updated_at`) VALUES
(1, 3, 'AMBER', 0, 2, '2026-06-10 00:26:45'),
(2, 3, 'RED', 0, 2, '2026-06-10 00:26:50'),
(3, 3, 'AMBER', 0, 2, '2026-06-10 00:26:54');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint UNSIGNED NOT NULL,
  `version` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `class` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `group` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `namespace` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `time` int NOT NULL,
  `batch` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(1, '2026-05-18-204814', 'App\\Database\\Migrations\\CreateCiSessionsTable', 'default', 'App', 1779137635, 1),
(2, '2026-05-21-171800', 'App\\Modules\\Pilot\\Database\\Migrations\\CreatePilotSignupsTable', 'default', 'App\\Modules\\Pilot', 1779384092, 2),
(3, '2026-05-21-172000', 'App\\Modules\\Queue\\Database\\Migrations\\CreateQueueTables', 'default', 'App\\Modules\\Queue', 1779384092, 2),
(4, '2026-05-28-120000', 'App\\Modules\\Hospital\\Database\\Migrations\\UpgradeHospitalSchema', 'default', 'App\\Modules\\Hospital', 1779959059, 3),
(5, '2026-06-03-171300', 'App\\Database\\Migrations\\AddCompositeIndexes', 'default', 'App', 1780496168, 4),
(6, '2026-06-10-130000', 'App\\Database\\Migrations\\AddAmbulanceIdToUsers', 'default', 'App', 1781096862, 5);

-- --------------------------------------------------------

--
-- Table structure for table `pilot_signups`
--

CREATE TABLE `pilot_signups` (
  `id` int UNSIGNED NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email_address` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `organisation` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `user_role` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `phone_number` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pilot_signups`
--

INSERT INTO `pilot_signups` (`id`, `full_name`, `email_address`, `organisation`, `user_role`, `phone_number`, `message`, `created_at`, `updated_at`) VALUES
(2, 'James Otieno', 'j.otieno@kenyaredcross.or.ke', 'Kenya Red Cross', 'Ambulance Paramedic', '+254 722 100200', 'Our crews spend too long in bays. ClearBay could fix this.', '2026-05-21 21:30:52', '2026-05-21 21:30:52'),
(4, 'Peter Mwangi', 'pmwangi@aarhealth.co.ke', 'AAR Healthcare', 'Ambulance Dispatcher', '+254 700 998877', 'We currently track handovers manually. This would be a great improvement.', '2026-05-21 21:30:53', '2026-05-21 21:30:53'),
(5, 'TRAVIS LEHN', 'nehemiahobati@gmail.com', 'richie org richie org', 'Investor / Funder', '0794587533', 'richie orgrichie orgrichie org', '2026-05-21 22:50:03', '2026-05-21 22:50:03'),
(6, 'urich', 'gakizaulrich@gmail.com', 'tsdgsjwwydhwudwluywy', 'Hospital Administrator', '0117259152', 'uweuywouuowyswiodiowy', '2026-05-22 10:39:30', '2026-05-22 10:39:30'),
(8, 'Dr. Wanjiru Kamau', 'wanjiru@mbagathi.ke', 'Mbagathi County Hospital', 'ED Manager / Charge Nurse', '+254 712 345678', 'We would love to participate in the off-load management pilot!', '2026-06-02 11:32:38', '2026-06-02 11:32:38'),
(9, 'Dr. Amina Hassan', 'a.hassan@knh.or.ke', 'Kenyatta National Hospital', 'Hospital Administrator', '+254 733 456789', 'Interested in piloting the system across our emergency department.', '2026-06-02 11:32:38', '2026-06-02 11:32:38');

-- --------------------------------------------------------

--
-- Table structure for table `pre_notifications`
--

CREATE TABLE `pre_notifications` (
  `id` int UNSIGNED NOT NULL,
  `ambulance_id` int UNSIGNED NOT NULL,
  `hospital_id` int UNSIGNED NOT NULL,
  `paramedic_id` int UNSIGNED NOT NULL,
  `patient_age` int NOT NULL,
  `patient_sex` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `chief_complaint` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `acuity` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `notes` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `eta_minutes` int NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Pending',
  `sent_at` datetime NOT NULL,
  `received_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pre_notifications`
--

INSERT INTO `pre_notifications` (`id`, `ambulance_id`, `hospital_id`, `paramedic_id`, `patient_age`, `patient_sex`, `chief_complaint`, `acuity`, `notes`, `eta_minutes`, `status`, `sent_at`, `received_at`, `created_at`, `updated_at`) VALUES
(1, 2, 3, 4, 89, 'Male', 'Stroke / CVA', 'Serious', 'la', 7, 'Pending', '2026-05-31 15:17:20', NULL, '2026-05-31 15:17:20', '2026-05-31 15:17:20'),
(2, 2, 4, 4, 105, 'Male', 'Stroke / CVA', 'Serious', 'mnb', 13, 'Pending', '2026-05-31 18:04:12', NULL, '2026-05-31 18:04:12', '2026-05-31 18:04:12'),
(3, 2, 1, 4, 5000, 'Male', 'Cardiac Arrest', 'Critical', 'fdbjgksgjgsuigsgiopowgbkug', 4, 'Pending', '2026-06-02 12:45:49', NULL, '2026-06-02 12:45:49', '2026-06-02 12:45:49'),
(4, 2, 3, 4, 50002, 'Male', 'Cardiac Arrest', 'Critical', 'fdjhjfffuyt', 7, 'Handover Complete', '2026-06-02 12:48:33', '2026-06-02 13:26:37', '2026-06-02 12:48:33', '2026-06-02 12:48:33'),
(5, 2, 3, 4, 456, 'Male', 'Stroke / CVA', 'Serious', 'na', 7, 'Pending', '2026-06-03 12:59:39', NULL, '2026-06-03 12:59:39', '2026-06-03 12:59:39'),
(6, 2, 5, 4, 67, 'Male', 'Cardiac Arrest', 'Critical', 'ba', 5, 'Pending', '2026-06-03 13:00:11', NULL, '2026-06-03 13:00:11', '2026-06-03 13:00:11'),
(7, 2, 5, 4, 678, 'Male', 'Cardiac Arrest', 'Critical', 'na', 5, 'Pending', '2026-06-03 13:37:32', NULL, '2026-06-03 13:37:32', '2026-06-03 13:37:32'),
(8, 2, 5, 4, 234, 'Male', 'Cardiac Arrest', 'Serious', 'vbvc', 5, 'Pending', '2026-06-03 13:39:57', NULL, '2026-06-03 13:39:57', '2026-06-03 13:39:57'),
(9, 2, 3, 4, 123, 'Female', 'Stroke / CVA', 'Serious', '  xcbvc', 7, 'Pending', '2026-06-03 13:49:17', NULL, '2026-06-03 13:49:17', '2026-06-03 13:49:17'),
(10, 5, 4, 4, 678, 'Female', 'Acute Coronary Syndrome', 'Serious', 'hgjkljh', 2, 'Pending', '2026-06-03 14:19:19', NULL, '2026-06-03 14:19:19', '2026-06-03 14:19:19'),
(11, 2, 3, 4, 790, 'Not Specified', 'Acute Coronary Syndrome', 'Critical', 'xghf', 7, 'Pending', '2026-06-03 14:35:00', NULL, '2026-06-03 14:35:00', '2026-06-03 14:35:00'),
(12, 2, 3, 4, 4465, 'Not Specified', 'Stroke / CVA', 'Serious', 'vbngfgdf', 7, 'Handover Complete', '2026-06-03 15:20:01', '2026-06-03 15:21:46', '2026-06-03 15:20:01', '2026-06-03 15:20:01'),
(13, 2, 1, 4, 12345, 'Female', 'Acute Coronary Syndrome', 'Serious', 'dfghjkjhg', 4, 'Handover Complete', '2026-06-04 15:01:12', '2026-06-04 15:07:49', '2026-06-04 15:01:12', '2026-06-04 15:01:12'),
(14, 2, 3, 4, 678, 'Female', 'Acute Coronary Syndrome', 'Serious', 'sdfhgf', 7, 'Handover Complete', '2026-06-04 15:09:01', '2026-06-04 15:10:42', '2026-06-04 15:09:01', '2026-06-04 15:09:01'),
(15, 2, 3, 4, 6789, 'Female', 'Stroke / CVA', 'Serious', 'iuytreiuyt', 7, 'Pending', '2026-06-06 12:08:47', NULL, '2026-06-06 12:08:47', '2026-06-06 12:08:47'),
(16, 2, 3, 4, 1234543, 'Female', 'Acute Coronary Syndrome', 'Serious', 'rter453', 7, 'Handover Complete', '2026-06-06 12:18:32', '2026-06-06 12:22:35', '2026-06-06 12:18:32', '2026-06-06 12:18:32'),
(17, 2, 3, 4, 119, 'Not Specified', 'Cardiac Arrest', 'Serious', 'cvbnm,', 10003, 'Handover Complete', '2026-06-08 06:53:20', '2026-06-08 07:00:05', '2026-06-08 06:53:20', '2026-06-08 06:53:20'),
(18, 2, 3, 4, 4555, 'Not Specified', 'Acute Coronary Syndrome', 'Stable', 'bnbvc', 10003, 'Handover Complete', '2026-06-08 07:10:28', '2026-06-08 08:09:56', '2026-06-08 07:10:28', '2026-06-08 07:10:28'),
(19, 2, 4, 4, 4567, 'Female', 'Cardiac Arrest', 'Serious', 'fghjk', 18, 'Pending', '2026-06-08 09:26:04', NULL, '2026-06-08 09:26:04', '2026-06-08 09:26:04'),
(20, 2, 3, 4, 456345, 'Female', 'Stroke / CVA', 'Serious', '45678', 11, 'Handover Complete', '2026-06-08 09:28:00', '2026-06-08 09:47:07', '2026-06-08 09:28:00', '2026-06-08 09:28:00'),
(21, 2, 1, 4, 48, 'Female', 'Stroke / CVA', 'Serious', 'xcv', 6, 'Pending', '2026-06-10 10:46:43', NULL, '2026-06-10 10:46:43', '2026-06-10 10:46:43'),
(22, 1, 1, 10, 23, 'Female', 'Road Traffic Accident', 'Serious', 'xdbgfvc', 5, 'Handover Complete', '2026-06-10 13:11:33', '2026-06-10 13:13:31', '2026-06-10 13:11:33', '2026-06-10 13:11:33'),
(23, 1, 1, 10, 654, 'Male', 'Stroke / CVA', 'Serious', 'hgbvcx', 34, 'Handover Complete', '2026-06-10 13:14:29', '2026-06-10 13:19:27', '2026-06-10 13:14:29', '2026-06-10 13:14:29'),
(24, 3, 1, 10, 678, 'Female', 'Stroke / CVA', 'Serious', 'bkm,', 34, 'Pending', '2026-06-10 13:19:58', NULL, '2026-06-10 13:19:58', '2026-06-10 13:19:58');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `hospital_id` int UNSIGNED DEFAULT NULL,
  `ems_provider_id` int UNSIGNED DEFAULT NULL,
  `ambulance_id` int UNSIGNED DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `hospital_id`, `ems_provider_id`, `ambulance_id`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Nurse Wanjiru', 'nurse@clearbay.com', '$2y$12$DGJplHGPhmGAzbjuPioCC.JpoUMOI0m.7O1bSAfcHUaPhj3CmMv6O', 'nurse', 1, NULL, NULL, 1, '2026-05-28 09:04:20', '2026-05-28 09:04:20'),
(2, 'Nurse Atieno', 'nurse2@clearbay.com', '$2y$12$DGJplHGPhmGAzbjuPioCC.JpoUMOI0m.7O1bSAfcHUaPhj3CmMv6O', 'nurse', 3, NULL, NULL, 1, '2026-05-28 09:04:20', '2026-05-28 09:04:20'),
(3, 'KNH Administrator', 'hospadmin@clearbay.com', '$2y$12$DGJplHGPhmGAzbjuPioCC.JpoUMOI0m.7O1bSAfcHUaPhj3CmMv6O', 'hospital_admin', 1, NULL, NULL, 1, '2026-05-28 09:04:20', '2026-05-28 09:04:20'),
(4, 'Paramedic Otieno', 'paramedic@clearbay.com', '$2y$12$DGJplHGPhmGAzbjuPioCC.JpoUMOI0m.7O1bSAfcHUaPhj3CmMv6O', 'paramedic', NULL, 2, NULL, 1, '2026-05-28 09:04:20', '2026-06-09 07:03:36'),
(5, 'Dispatcher Mwangi', 'dispatcher@clearbay.com', '$2y$12$DGJplHGPhmGAzbjuPioCC.JpoUMOI0m.7O1bSAfcHUaPhj3CmMv6O', 'dispatcher', NULL, NULL, NULL, 1, '2026-05-28 09:04:20', '2026-05-28 09:04:20'),
(6, 'System Admin', 'admin@clearbay.com', '$2y$12$DGJplHGPhmGAzbjuPioCC.JpoUMOI0m.7O1bSAfcHUaPhj3CmMv6O', 'admin', NULL, NULL, NULL, 1, '2026-05-28 09:04:20', '2026-05-28 09:04:20'),
(8, 'mbagathiii admin', 'mbagathi@admi.com', '$2y$12$oD5Ai7bS0G2G4iHzddqkaulZzqD8GtEPRu8wrO3rcx2xzjuxfPTze', 'hospital_admin', 3, NULL, NULL, 1, '2026-06-02 13:30:47', '2026-06-03 13:23:11'),
(9, 'rtyuitrtyu', 'nehemiahobati@gmail.com', '$2y$12$n0O32.TLDlnCyMXRbPcRyuPuyOYtVMlygTJFItZQ5mhXRv1ACXAwi', 'paramedic', NULL, 2, NULL, 0, '2026-06-08 20:32:27', '2026-06-08 20:33:11'),
(10, 'zxcvbnm,.', 'paramedic2@clearbay.com', '$2y$12$jk3ZQGVG98CIxsPOtRmH9u2k8IgE4n8M9WJGGr2D8S5p7GOpIrmEm', 'paramedic', NULL, 1, 3, 1, '2026-06-10 12:06:02', '2026-06-10 13:13:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alerts`
--
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ambulance_id` (`ambulance_id`),
  ADD KEY `hospital_id` (`hospital_id`),
  ADD KEY `triggered_at` (`triggered_at`);

--
-- Indexes for table `ambulances`
--
ALTER TABLE `ambulances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `unit_id` (`unit_id`),
  ADD KEY `ambulances_provider_status` (`ems_provider_id`,`status`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `table_name` (`table_name`),
  ADD KEY `timestamp` (`timestamp`);

--
-- Indexes for table `ci_sessions`
--
ALTER TABLE `ci_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `timestamp` (`timestamp`);

--
-- Indexes for table `ems_providers`
--
ALTER TABLE `ems_providers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `active` (`active`);

--
-- Indexes for table `handovers`
--
ALTER TABLE `handovers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ambulance_id` (`ambulance_id`),
  ADD KEY `hospital_id` (`hospital_id`),
  ADD KEY `status` (`status`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `handovers_hosp_status` (`hospital_id`,`status`);

--
-- Indexes for table `hospitals`
--
ALTER TABLE `hospitals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `code` (`code`);

--
-- Indexes for table `hospital_status`
--
ALTER TABLE `hospital_status`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hospital_id` (`hospital_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pilot_signups`
--
ALTER TABLE `pilot_signups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_address` (`email_address`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `pre_notifications`
--
ALTER TABLE `pre_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ambulance_id` (`ambulance_id`),
  ADD KEY `hospital_id` (`hospital_id`),
  ADD KEY `paramedic_id` (`paramedic_id`),
  ADD KEY `status` (`status`),
  ADD KEY `sent_at` (`sent_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`),
  ADD KEY `role` (`role`),
  ADD KEY `active` (`active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `ambulances`
--
ALTER TABLE `ambulances`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `ems_providers`
--
ALTER TABLE `ems_providers`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `handovers`
--
ALTER TABLE `handovers`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `hospitals`
--
ALTER TABLE `hospitals`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `hospital_status`
--
ALTER TABLE `hospital_status`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pilot_signups`
--
ALTER TABLE `pilot_signups`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `pre_notifications`
--
ALTER TABLE `pre_notifications`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
