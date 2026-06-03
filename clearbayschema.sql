-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 03, 2026 at 11:29 AM
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
(2, 2, 1, 'Wait Time Exceeded', '2026-05-28 10:03:51', NULL, NULL, '2026-05-28 10:03:51', '2026-05-28 10:03:51'),
(3, 3, 2, 'Wait Time Exceeded', '2026-05-28 10:03:51', NULL, NULL, '2026-05-28 10:03:51', '2026-05-28 10:03:51'),
(4, 4, 5, 'Wait Time Exceeded', '2026-05-28 10:03:51', NULL, NULL, '2026-05-28 10:03:51', '2026-05-28 10:03:51'),
(5, 6, 2, 'Wait Time Exceeded', '2026-06-01 18:32:59', NULL, NULL, '2026-06-01 18:32:59', '2026-06-01 18:32:59'),
(6, 1, 1, 'Wait Time Exceeded', '2026-06-01 18:59:24', NULL, NULL, '2026-06-01 18:59:24', '2026-06-01 18:59:24'),
(7, 1, 3, 'Wait Time Exceeded', '2026-06-02 13:35:54', NULL, NULL, '2026-06-02 13:35:54', '2026-06-02 13:35:54'),
(8, 2, 4, 'Wait Time Exceeded', '2026-06-02 13:35:54', '2026-06-02 13:45:39', 5, '2026-06-02 13:35:54', '2026-06-02 13:45:39'),
(9, 6, 1, 'Wait Time Exceeded', '2026-06-02 13:35:54', NULL, NULL, '2026-06-02 13:35:54', '2026-06-02 13:35:54'),
(10, 2, 4, 'Wait Time Exceeded', '2026-06-02 13:45:39', NULL, NULL, '2026-06-02 13:45:39', '2026-06-02 13:45:39');

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
(1, 'AAR-04', 'AAR Healthcare', 1, 'KBY 104A', -1.30800000, 36.80200000, 'Queued', '2026-06-02 11:32:43', '2026-05-21 17:21:47', '2026-05-21 17:21:47'),
(2, 'KRC-12', 'Kenya Red Cross', 2, 'KBZ 512B', -1.29800000, 36.81500000, 'Queued', '2026-06-02 13:26:37', '2026-05-21 17:21:47', '2026-06-02 12:48:33'),
(3, 'NBO-07', 'Nairobi County', 3, 'KCG 007G', -1.28800000, 36.88500000, 'Transporting', '2026-06-02 11:32:43', '2026-05-21 17:21:47', '2026-05-21 17:21:47'),
(4, 'AAR-09', 'AAR Healthcare', 1, 'KBY 109A', -1.29220000, 36.80900000, 'Transporting', '2026-06-02 11:32:43', '2026-05-21 17:21:47', '2026-05-21 17:21:47'),
(5, 'KRC-05', 'Kenya Red Cross', 2, 'KBZ 505B', -1.26100000, 36.80900000, 'Available', '2026-06-02 11:32:43', '2026-05-21 17:21:47', '2026-05-21 17:21:47'),
(6, 'AAR-02', 'AAR Healthcare', 1, 'KBY 102A', -1.30900000, 36.80100000, 'Queued', '2026-06-02 11:32:43', '2026-05-21 17:21:47', '2026-05-21 17:21:47');

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
(10, NULL, 'Automated alert generated: wait time > 30 min', 'alerts', 10, '2026-06-02 13:45:39');

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
(1, NULL, 1, 3, 58, 'M', 'Critical', 0, 148, 'Arrived', NULL, NULL, NULL, NULL, NULL, '2026-06-02 11:32:44', '2026-06-02 14:00:41'),
(2, NULL, 2, 1, 34, 'F', 'Serious', 6, 148, 'En route', NULL, NULL, NULL, NULL, NULL, '2026-06-02 11:32:44', '2026-06-02 14:00:41'),
(3, NULL, 3, 2, 71, 'M', 'Stable', 14, 148, 'En route', NULL, NULL, NULL, NULL, NULL, '2026-06-02 11:32:44', '2026-06-02 14:00:41'),
(4, NULL, 4, 5, 26, 'F', 'Serious', 22, 148, 'En route', NULL, NULL, NULL, NULL, NULL, '2026-06-02 11:32:44', '2026-06-02 14:00:41'),
(5, NULL, 1, 1, 36, 'M', 'Critical', 0, 40, 'Cleared', NULL, NULL, NULL, NULL, NULL, '2026-06-02 11:32:44', '2026-06-02 11:32:44'),
(6, NULL, 2, 2, 86, 'F', 'Serious', 0, 45, 'Cleared', NULL, NULL, NULL, NULL, NULL, '2026-06-02 11:32:44', '2026-06-02 11:32:44'),
(7, NULL, 3, 3, 62, 'M', 'Stable', 0, 50, 'Cleared', NULL, NULL, NULL, NULL, NULL, '2026-06-02 11:32:44', '2026-06-02 11:32:44'),
(8, NULL, 4, 4, 73, 'F', 'Critical', 0, 35, 'Cleared', NULL, NULL, NULL, NULL, NULL, '2026-06-02 11:32:44', '2026-06-02 11:32:44'),
(9, NULL, 5, 5, 41, 'M', 'Serious', 0, 38, 'Cleared', NULL, NULL, NULL, NULL, NULL, '2026-06-02 11:32:44', '2026-06-02 11:32:44'),
(10, NULL, 6, 1, 67, 'F', 'Stable', 0, 42, 'Cleared', NULL, NULL, NULL, NULL, NULL, '2026-06-02 11:32:44', '2026-06-02 11:32:44'),
(11, NULL, 1, 2, 49, 'M', 'Critical', 0, 55, 'Cleared', NULL, NULL, NULL, NULL, NULL, '2026-06-02 11:32:44', '2026-06-02 11:32:44'),
(12, NULL, 2, 3, 62, 'F', 'Serious', 0, 30, 'Cleared', NULL, NULL, NULL, NULL, NULL, '2026-06-02 11:32:44', '2026-06-02 11:32:44'),
(13, NULL, 3, 4, 21, 'M', 'Stable', 0, 48, 'Cleared', NULL, NULL, NULL, NULL, NULL, '2026-06-02 11:32:44', '2026-06-02 11:32:44'),
(14, NULL, 4, 5, 22, 'F', 'Critical', 0, 52, 'Cleared', NULL, NULL, NULL, NULL, NULL, '2026-06-02 11:32:44', '2026-06-02 11:32:44'),
(15, NULL, 5, 1, 29, 'M', 'Serious', 0, 40, 'Cleared', NULL, NULL, NULL, NULL, NULL, '2026-06-02 11:32:44', '2026-06-02 11:32:44'),
(16, NULL, 6, 2, 56, 'F', 'Stable', 0, 148, 'Acknowledged', NULL, NULL, NULL, NULL, NULL, '2026-06-02 11:32:44', '2026-06-02 14:00:41'),
(17, NULL, 1, 3, 23, 'M', 'Critical', 0, 50, 'Cleared', NULL, NULL, NULL, NULL, NULL, '2026-06-02 11:32:44', '2026-06-02 11:32:44'),
(18, NULL, 2, 4, 35, 'F', 'Serious', 0, 148, 'Arrived', NULL, NULL, NULL, NULL, NULL, '2026-06-02 11:32:44', '2026-06-02 14:00:41'),
(19, NULL, 6, 1, 150, 'M', 'Serious', 200, 85, 'En route', NULL, NULL, NULL, NULL, NULL, '2026-06-02 12:35:17', '2026-06-02 14:00:41'),
(20, 3, 2, 1, 5000, 'M', 'Critical', 4, 75, 'En route', NULL, NULL, NULL, NULL, NULL, '2026-06-02 12:45:49', '2026-06-02 14:00:41'),
(21, 4, 2, 3, 50002, 'M', 'Critical', 7, 38, 'Cleared', NULL, '2026-06-02 13:26:37', '2', 'vnchgxhdgsx', 2, '2026-06-02 12:48:33', '2026-06-02 13:26:37');

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
(3, 'MBG', 'Mbagathi County Hospital', 'County Referral · Public', 'Red', 0, -1.30900000, 36.80100000, 'Mbagathi Way, Nairobi', '+254202724712', 1, '2026-05-21 17:21:47', '2026-05-21 17:21:47'),
(4, 'AKU', 'Aga Khan University Hospital', 'Teaching Hospital · Private', 'Green', 5, -1.26100000, 36.80900000, '3rd Parklands Ave, Nairobi', '+254203662000', 1, '2026-05-21 17:21:47', '2026-05-21 17:21:47'),
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
(4, '2026-05-28-120000', 'App\\Modules\\Hospital\\Database\\Migrations\\UpgradeHospitalSchema', 'default', 'App\\Modules\\Hospital', 1779959059, 3);

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
(7, 'asdfghjkjbj', 'asdfg@mail.com', 'richie org richie org', 'Paramedic / EMT', '0794587533', 'zxcvbnm', '2026-05-28 15:10:59', '2026-06-01 10:09:51'),
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
(4, 2, 3, 4, 50002, 'Male', 'Cardiac Arrest', 'Critical', 'fdjhjfffuyt', 7, 'Handover Complete', '2026-06-02 12:48:33', '2026-06-02 13:26:37', '2026-06-02 12:48:33', '2026-06-02 12:48:33');

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
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `hospital_id`, `ems_provider_id`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Nurse Wanjiru', 'nurse@clearbay.com', '$2y$12$DGJplHGPhmGAzbjuPioCC.JpoUMOI0m.7O1bSAfcHUaPhj3CmMv6O', 'nurse', 1, NULL, 1, '2026-05-28 09:04:20', '2026-05-28 09:04:20'),
(2, 'Nurse Atieno', 'nurse2@clearbay.com', '$2y$12$DGJplHGPhmGAzbjuPioCC.JpoUMOI0m.7O1bSAfcHUaPhj3CmMv6O', 'nurse', 3, NULL, 1, '2026-05-28 09:04:20', '2026-05-28 09:04:20'),
(3, 'KNH Administrator', 'hospadmin@clearbay.com', '$2y$12$DGJplHGPhmGAzbjuPioCC.JpoUMOI0m.7O1bSAfcHUaPhj3CmMv6O', 'hospital_admin', 1, NULL, 1, '2026-05-28 09:04:20', '2026-05-28 09:04:20'),
(4, 'Paramedic Otieno', 'paramedic@clearbay.com', '$2y$12$DGJplHGPhmGAzbjuPioCC.JpoUMOI0m.7O1bSAfcHUaPhj3CmMv6O', 'paramedic', NULL, 2, 1, '2026-05-28 09:04:20', '2026-05-28 09:04:20'),
(5, 'Dispatcher Mwangi', 'dispatcher@clearbay.com', '$2y$12$DGJplHGPhmGAzbjuPioCC.JpoUMOI0m.7O1bSAfcHUaPhj3CmMv6O', 'dispatcher', NULL, NULL, 1, '2026-05-28 09:04:20', '2026-05-28 09:04:20'),
(6, 'System Admin', 'admin@clearbay.com', '$2y$12$DGJplHGPhmGAzbjuPioCC.JpoUMOI0m.7O1bSAfcHUaPhj3CmMv6O', 'admin', NULL, NULL, 1, '2026-05-28 09:04:20', '2026-05-28 09:04:20'),
(8, 'mbagathi admin', 'mbagathi@admi.com', '$2y$12$oD5Ai7bS0G2G4iHzddqkaulZzqD8GtEPRu8wrO3rcx2xzjuxfPTze', 'hospital_admin', 3, NULL, 1, '2026-06-02 13:30:47', '2026-06-02 13:30:47');

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
  ADD KEY `unit_id` (`unit_id`);

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
  ADD KEY `created_at` (`created_at`);

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
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `ambulances`
--
ALTER TABLE `ambulances`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `ems_providers`
--
ALTER TABLE `ems_providers`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `handovers`
--
ALTER TABLE `handovers`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `hospitals`
--
ALTER TABLE `hospitals`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `hospital_status`
--
ALTER TABLE `hospital_status`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pilot_signups`
--
ALTER TABLE `pilot_signups`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `pre_notifications`
--
ALTER TABLE `pre_notifications`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
