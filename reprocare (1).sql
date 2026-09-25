-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 03, 2026 at 03:35 PM
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
-- Database: `reprocare`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_role` varchar(255) DEFAULT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `action` enum('login','logout','create','update','archive','restore','approve','reject','submit','print','export','view_sensitive','send_message','request_supply','delete','other') NOT NULL DEFAULT 'other',
  `model_type` varchar(255) DEFAULT NULL,
  `model_id` bigint(20) UNSIGNED DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `user_role`, `user_name`, `action`, `model_type`, `model_id`, `description`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 5, 'midwife', 'Admin Midwife', 'create', NULL, NULL, 'Sent custom SMS to madona f. rhodes (09384548234)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-03 09:45:51', '2026-08-03 09:45:51'),
(2, 5, 'midwife', 'Admin Midwife', 'create', NULL, NULL, 'Sent custom SMS to madona f. rhodes (09384548234)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-03 09:46:47', '2026-08-03 09:46:47'),
(3, 5, 'midwife', 'Admin Midwife', 'create', NULL, NULL, 'Sent custom SMS to madona f. rhodes (09384548234)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-03 09:57:44', '2026-08-03 09:57:44'),
(4, 5, 'midwife', 'Admin Midwife', 'create', NULL, NULL, 'Sent custom SMS to madona f. rhodes (09384548234)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-22 13:50:09', '2026-08-22 13:50:09'),
(5, 5, 'midwife', 'Admin Midwife', 'create', NULL, NULL, 'Sent custom SMS to madona f. rhodes (09384548234)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 14:40:20', '2026-09-01 14:40:20'),
(6, 5, 'midwife', 'Admin Midwife', 'create', NULL, NULL, 'Sent custom SMS to madona f. rhodes (09384548234)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 15:00:05', '2026-09-01 15:00:05'),
(7, 5, 'midwife', 'Admin Midwife', 'create', NULL, NULL, 'Sent custom SMS to madona f. rhodes (09384548234)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 15:00:06', '2026-09-01 15:00:06'),
(8, 5, 'midwife', 'Admin Midwife', 'create', NULL, NULL, 'Created learning material/video: What to expect in you First Trimester of pregnancy', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 15:08:10', '2026-09-02 15:08:10');

-- --------------------------------------------------------

--
-- Table structure for table `bhw_assignments`
--

CREATE TABLE `bhw_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bhw_id` bigint(20) UNSIGNED NOT NULL,
  `purok_id` bigint(20) UNSIGNED NOT NULL,
  `assigned_by_id` bigint(20) UNSIGNED NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bhw_assignments`
--

INSERT INTO `bhw_assignments` (`id`, `bhw_id`, `purok_id`, `assigned_by_id`, `assigned_at`, `is_active`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
(5, 13, 3, 7, '2026-04-26 09:17:44', 1, 'mag hanap ng juntisss', '2026-04-26 09:17:44', '2026-04-26 09:17:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bhw_monthly_reports`
--

CREATE TABLE `bhw_monthly_reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bhw_id` bigint(20) UNSIGNED NOT NULL,
  `report_type` enum('health_records','pregnancies') NOT NULL DEFAULT 'health_records',
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `report_month` int(11) NOT NULL,
  `report_year` int(11) NOT NULL,
  `filters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`filters`)),
  `total_records` int(11) NOT NULL DEFAULT 0,
  `status` enum('draft','completed') NOT NULL DEFAULT 'draft',
  `submission_status` enum('draft','submitted_to_president','approved_by_president','submitted_to_midwife','approved_by_midwife','rejected') NOT NULL DEFAULT 'draft',
  `printed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `submitted_to_president_by` bigint(20) UNSIGNED DEFAULT NULL,
  `submitted_to_president_at` timestamp NULL DEFAULT NULL,
  `approved_by_president` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_by_president_at` timestamp NULL DEFAULT NULL,
  `president_notes` text DEFAULT NULL,
  `submitted_to_midwife_by` bigint(20) UNSIGNED DEFAULT NULL,
  `submitted_to_midwife_at` timestamp NULL DEFAULT NULL,
  `approved_by_midwife` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_by_midwife_at` timestamp NULL DEFAULT NULL,
  `midwife_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bhw_monthly_reports`
--

INSERT INTO `bhw_monthly_reports` (`id`, `bhw_id`, `report_type`, `title`, `description`, `report_month`, `report_year`, `filters`, `total_records`, `status`, `submission_status`, `printed_at`, `created_at`, `updated_at`, `deleted_at`, `submitted_to_president_by`, `submitted_to_president_at`, `approved_by_president`, `approved_by_president_at`, `president_notes`, `submitted_to_midwife_by`, `submitted_to_midwife_at`, `approved_by_midwife`, `approved_by_midwife_at`, `midwife_notes`) VALUES
(2, 13, 'health_records', 'Health rcord by me', NULL, 4, 2026, NULL, 1, 'completed', 'draft', '2026-04-26 08:49:22', '2026-04-26 08:47:59', '2026-04-26 08:49:22', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 13, 'health_records', 'bvc', NULL, 4, 2026, NULL, 1, 'completed', 'approved_by_midwife', '2026-04-26 09:03:34', '2026-04-26 09:01:44', '2026-04-26 15:21:22', NULL, 13, '2026-04-26 14:52:27', 7, '2026-04-26 15:11:08', 'report this month', 7, '2026-04-26 15:11:08', 5, '2026-04-26 15:21:22', 'okay na');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-midwife_dashboard_stats_5', 'a:5:{s:13:\"totalPatients\";i:3;s:17:\"activePregnancies\";i:3;s:17:\"scheduledCheckups\";i:4;s:14:\"missedCheckups\";i:0;s:16:\"highRiskPatients\";i:0;}', 1788360436);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `checkups`
--

CREATE TABLE `checkups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `pregnancy_id` bigint(20) UNSIGNED DEFAULT NULL,
  `walk_in_patient_id` bigint(20) UNSIGNED DEFAULT NULL,
  `midwife_id` bigint(20) UNSIGNED DEFAULT NULL,
  `scheduled_by_id` bigint(20) UNSIGNED DEFAULT NULL,
  `scheduled_date` date NOT NULL,
  `scheduled_time` time DEFAULT NULL,
  `actual_date` date DEFAULT NULL,
  `purpose` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('scheduled','completed','missed','cancelled','Rescheduled') DEFAULT 'scheduled',
  `trimester` enum('first','second','third') DEFAULT NULL,
  `vitamins_given` tinyint(1) NOT NULL DEFAULT 0,
  `vitamin_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `bhw_president_id` bigint(20) UNSIGNED DEFAULT NULL,
  `bhw_president_approved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `checkups`
--

INSERT INTO `checkups` (`id`, `user_id`, `pregnancy_id`, `walk_in_patient_id`, `midwife_id`, `scheduled_by_id`, `scheduled_date`, `scheduled_time`, `actual_date`, `purpose`, `notes`, `status`, `trimester`, `vitamins_given`, `vitamin_notes`, `created_at`, `updated_at`, `deleted_at`, `bhw_president_id`, `bhw_president_approved_at`) VALUES
(17, 6, 4, NULL, 5, 5, '2026-04-29', NULL, NULL, '1st tri check up', NULL, 'scheduled', NULL, 0, NULL, '2026-04-24 20:41:15', '2026-04-24 20:41:15', NULL, NULL, NULL),
(19, 6, NULL, NULL, NULL, NULL, '2026-08-17', NULL, NULL, 'Follow-up: Risk-based preventive care', 'Medium risk patient follow-up recommended', 'scheduled', NULL, 0, NULL, '2026-08-10 10:19:14', '2026-08-10 10:19:14', NULL, NULL, NULL),
(20, 6, NULL, NULL, NULL, NULL, '2026-08-29', NULL, NULL, 'Follow-up: Risk-based preventive care', 'Medium risk patient follow-up recommended', 'scheduled', NULL, 0, NULL, '2026-08-22 07:59:02', '2026-08-22 07:59:02', NULL, NULL, NULL),
(21, 11, NULL, NULL, NULL, NULL, '2026-08-29', NULL, NULL, 'Follow-up: Risk-based clinical care', 'Medium risk patient follow-up recommended within 1 week', 'scheduled', NULL, 0, NULL, '2026-08-22 15:23:11', '2026-08-22 15:23:11', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `checkup_referrals`
--

CREATE TABLE `checkup_referrals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `referred_by_bhw_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `walk_in_patient_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_midwife_id` bigint(20) UNSIGNED DEFAULT NULL,
  `converted_checkup_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `urgency` enum('routine','urgent','emergency') NOT NULL DEFAULT 'routine',
  `bhw_notes` text DEFAULT NULL,
  `midwife_notes` text DEFAULT NULL,
  `status` enum('pending','reviewed','scheduled','completed','declined') NOT NULL DEFAULT 'pending',
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `checkup_referrals`
--

INSERT INTO `checkup_referrals` (`id`, `referred_by_bhw_id`, `user_id`, `walk_in_patient_id`, `assigned_midwife_id`, `converted_checkup_id`, `reason`, `urgency`, `bhw_notes`, `midwife_notes`, `status`, `reviewed_at`, `scheduled_at`, `completed_at`, `created_at`, `updated_at`) VALUES
(1, NULL, 11, NULL, 5, NULL, 'pregnant need check up', 'routine', NULL, NULL, 'scheduled', NULL, '2026-04-24 23:14:28', NULL, '2026-04-24 22:53:15', '2026-04-24 23:14:28');

-- --------------------------------------------------------

--
-- Table structure for table `child_assessments`
--

CREATE TABLE `child_assessments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `child_care_target_client_id` bigint(20) UNSIGNED NOT NULL,
  `age_group` enum('1_3_months','6_11_months','12_months') NOT NULL,
  `age_months` varchar(255) DEFAULT NULL,
  `length_cm` decimal(6,2) DEFAULT NULL,
  `length_date` date DEFAULT NULL,
  `weight_kg` decimal(6,2) DEFAULT NULL,
  `weight_date` date DEFAULT NULL,
  `status` enum('S','W-MAM','W-SAM','O','N') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `child_care_target_clients`
--

CREATE TABLE `child_care_target_clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `child_id` bigint(20) UNSIGNED NOT NULL,
  `date_of_registration` date DEFAULT NULL,
  `family_serial_number` varchar(255) DEFAULT NULL,
  `cpab_tt2_tt4` tinyint(1) DEFAULT NULL,
  `cpab_tt3_tt5` tinyint(1) DEFAULT NULL,
  `newborn_status` enum('low','normal','unknown') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `child_checkups`
--

CREATE TABLE `child_checkups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `child_id` bigint(20) UNSIGNED NOT NULL,
  `checkup_date` date NOT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `head_circumference` decimal(5,2) DEFAULT NULL,
  `developmental_milestones` text DEFAULT NULL,
  `vaccinations_given` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`vaccinations_given`)),
  `notes` text DEFAULT NULL,
  `conducted_by_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `child_feeding_milestones`
--

CREATE TABLE `child_feeding_milestones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `child_care_target_client_id` bigint(20) UNSIGNED NOT NULL,
  `exclusive_breastfed_up_to_6_months` tinyint(1) DEFAULT NULL,
  `complementary_feeding_introduced` tinyint(1) DEFAULT NULL,
  `breastfeeding_initiated_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `child_management_outcomes`
--

CREATE TABLE `child_management_outcomes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `child_care_target_client_id` bigint(20) UNSIGNED NOT NULL,
  `program_type` enum('man_sfp','sam_otc') NOT NULL,
  `admitted` tinyint(1) DEFAULT NULL,
  `cured` tinyint(1) DEFAULT NULL,
  `defaulted` tinyint(1) DEFAULT NULL,
  `died` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `child_nutrition_tracking`
--

CREATE TABLE `child_nutrition_tracking` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `child_care_target_client_id` bigint(20) UNSIGNED NOT NULL,
  `month_range` enum('1_5','2_5','3_5','4_5','5_9') NOT NULL,
  `exclusive_breastfeeding` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `child_records`
--

CREATE TABLE `child_records` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `mother_id` bigint(20) UNSIGNED NOT NULL,
  `pregnancy_id` bigint(20) UNSIGNED DEFAULT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `birth_weight` decimal(5,2) DEFAULT NULL,
  `birth_length` decimal(5,2) DEFAULT NULL,
  `apgar_score` varchar(255) DEFAULT NULL,
  `delivery_type` enum('normal','cesarean','assisted') NOT NULL DEFAULT 'normal',
  `complications` text DEFAULT NULL,
  `purok_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('active','transferred','deceased') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `child_supplements`
--

CREATE TABLE `child_supplements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `child_care_target_client_id` bigint(20) UNSIGNED NOT NULL,
  `supplement_type` enum('low_birth_weight_iron','vitamin_a','mnp') NOT NULL,
  `month_number` int(11) DEFAULT NULL,
  `given_date` date DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `child_vaccinations`
--

CREATE TABLE `child_vaccinations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `child_care_target_client_id` bigint(20) UNSIGNED NOT NULL,
  `vaccine_type` enum('bcg','hepa_b_bd','dpt_hepb_hib','opv','pcv','ipv','mmr','fic','cic') NOT NULL,
  `dose_number` int(11) DEFAULT NULL,
  `vaccination_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cycles`
--

CREATE TABLE `cycles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `period_start_date` date NOT NULL,
  `period_end_date` date DEFAULT NULL,
  `cycle_length` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cycles`
--

INSERT INTO `cycles` (`id`, `user_id`, `period_start_date`, `period_end_date`, `cycle_length`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
(14, 6, '2026-01-01', '2026-01-07', NULL, NULL, '2026-04-25 13:54:54', '2026-04-25 13:54:54', NULL),
(15, 6, '2026-02-01', '2026-02-07', 31, NULL, '2026-04-25 13:56:47', '2026-04-25 13:56:47', NULL),
(17, 6, '2026-04-01', '2026-04-07', 30, NULL, '2026-04-25 14:00:11', '2026-04-25 14:00:11', NULL),
(18, 6, '2026-03-02', '2026-03-08', 29, NULL, '2026-04-25 14:01:15', '2026-04-25 14:01:15', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `emergency_contacts`
--

CREATE TABLE `emergency_contacts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `relationship` varchar(255) NOT NULL,
  `contact_number` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 1,
  `contact_order` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `emergency_contacts`
--

INSERT INTO `emergency_contacts` (`id`, `user_id`, `name`, `relationship`, `contact_number`, `address`, `is_primary`, `contact_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 14, 'Emmanuel Lapasaran', 'Friend', '09461417414', NULL, 1, 1, '2026-08-03 09:40:16', '2026-08-03 09:40:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `forum_comments`
--

CREATE TABLE `forum_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forum_likes`
--

CREATE TABLE `forum_likes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `forum_likes`
--

INSERT INTO `forum_likes` (`id`, `user_id`, `post_id`, `created_at`, `updated_at`) VALUES
(3, 7, 19, '2026-04-26 15:31:40', '2026-04-26 15:31:40'),
(4, 7, 16, '2026-04-26 15:33:42', '2026-04-26 15:33:42');

-- --------------------------------------------------------

--
-- Table structure for table `forum_posts`
--

CREATE TABLE `forum_posts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `content` text NOT NULL,
  `post_image` varchar(255) DEFAULT NULL,
  `status` enum('active','deleted') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `forum_posts`
--

INSERT INTO `forum_posts` (`id`, `user_id`, `content`, `post_image`, `status`, `created_at`, `updated_at`) VALUES
(16, 5, 'iuytrewq', NULL, 'active', '2026-04-24 23:21:49', '2026-04-24 23:21:49'),
(17, 7, 'kdfs', NULL, 'deleted', '2026-04-24 23:22:24', '2026-04-25 13:40:33'),
(18, 11, 'trying to post here', NULL, 'deleted', '2026-04-25 06:27:20', '2026-04-25 13:40:16'),
(19, 7, '.,mjhgfduytrdhrdhytrd', NULL, 'active', '2026-04-26 15:27:18', '2026-04-26 15:27:18');

-- --------------------------------------------------------

--
-- Table structure for table `health_records`
--

CREATE TABLE `health_records` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `walk_in_patient_id` bigint(20) UNSIGNED DEFAULT NULL,
  `pregnancy_id` bigint(20) UNSIGNED DEFAULT NULL,
  `recorded_by_id` bigint(20) UNSIGNED DEFAULT NULL,
  `bp` varchar(255) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `bmi` decimal(5,2) DEFAULT NULL,
  `heart_rate` int(11) DEFAULT NULL,
  `temperature` decimal(4,1) DEFAULT NULL,
  `hemoglobin` decimal(5,1) DEFAULT NULL COMMENT 'g/dL',
  `gestational_age` int(11) DEFAULT NULL COMMENT 'weeks',
  `immunization_status` varchar(255) DEFAULT NULL,
  `contraceptive_use` varchar(255) DEFAULT NULL,
  `lab_results` varchar(255) DEFAULT NULL,
  `smoking_status` varchar(255) DEFAULT NULL,
  `alcohol_use` varchar(255) DEFAULT NULL,
  `drug_use` varchar(255) DEFAULT NULL,
  `lifestyle_notes` text DEFAULT NULL,
  `obstetric_history` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `risk_level` enum('Low','Medium','High') NOT NULL DEFAULT 'Low',
  `risk_assessment_mode` varchar(255) NOT NULL DEFAULT 'automatic',
  `risk_notes` text DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `archived_at` timestamp NULL DEFAULT NULL,
  `archived_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `bhw_president_id` bigint(20) UNSIGNED DEFAULT NULL,
  `bhw_president_approved_at` timestamp NULL DEFAULT NULL,
  `workflow_status` varchar(50) NOT NULL DEFAULT 'accepted_by_midwife',
  `submitted_to_bhw_president_at` timestamp NULL DEFAULT NULL,
  `submitted_to_midwife_at` timestamp NULL DEFAULT NULL,
  `midwife_accepted_at` timestamp NULL DEFAULT NULL,
  `workflow_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `health_records`
--

INSERT INTO `health_records` (`id`, `user_id`, `walk_in_patient_id`, `pregnancy_id`, `recorded_by_id`, `bp`, `weight`, `height`, `bmi`, `heart_rate`, `temperature`, `hemoglobin`, `gestational_age`, `immunization_status`, `contraceptive_use`, `lab_results`, `smoking_status`, `alcohol_use`, `drug_use`, `lifestyle_notes`, `obstetric_history`, `notes`, `risk_level`, `risk_assessment_mode`, `risk_notes`, `recommendations`, `is_archived`, `archived_at`, `archived_reason`, `created_at`, `updated_at`, `deleted_at`, `bhw_president_id`, `bhw_president_approved_at`, `workflow_status`, `submitted_to_bhw_president_at`, `submitted_to_midwife_at`, `midwife_accepted_at`, `workflow_notes`) VALUES
(12, 6, NULL, 4, NULL, '120/80', 90.00, 155.00, 37.46, NULL, NULL, NULL, 4, NULL, NULL, NULL, 'none', 'none', 'none', NULL, NULL, NULL, 'Medium', 'automatic', 'Blood pressure is elevated. BMI is outside the usual low-risk range.', '🩺 Weekly BP checks recommended. Emphasize low-sodium diet and adequate rest.', 0, NULL, NULL, '2026-04-24 19:18:45', '2026-08-22 15:23:09', NULL, NULL, NULL, 'accepted_by_midwife', NULL, NULL, '2026-04-24 19:18:45', NULL),
(13, 11, NULL, 5, NULL, '120/80', 90.00, 160.00, 35.16, NULL, NULL, NULL, 8, NULL, NULL, NULL, 'none', 'none', 'none', NULL, NULL, NULL, 'Medium', 'automatic', 'Blood pressure is elevated. BMI is outside the usual low-risk range.', '🩺 Weekly BP checks recommended. Emphasize low-sodium diet and adequate rest.', 0, NULL, NULL, '2026-04-24 21:51:12', '2026-08-22 15:23:11', NULL, NULL, NULL, 'accepted_by_midwife', NULL, NULL, '2026-04-24 21:51:12', NULL),
(17, NULL, 1, NULL, 13, '128/81', 65.00, 160.00, 25.39, 69, 36.6, NULL, NULL, NULL, NULL, NULL, 'none', 'none', 'none', NULL, NULL, NULL, 'Medium', 'automatic', NULL, NULL, 0, NULL, NULL, '2026-04-26 08:28:29', '2026-04-26 08:28:29', NULL, NULL, NULL, 'recorded_by_bhw', '2026-04-26 08:28:29', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `health_records_archived`
--

CREATE TABLE `health_records_archived` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `walk_in_patient_id` bigint(20) UNSIGNED DEFAULT NULL,
  `pregnancy_id` bigint(20) UNSIGNED DEFAULT NULL,
  `recorded_by_id` bigint(20) UNSIGNED DEFAULT NULL,
  `bp` varchar(255) DEFAULT NULL,
  `weight` decimal(8,2) DEFAULT NULL,
  `height` decimal(8,2) DEFAULT NULL,
  `bmi` decimal(8,2) DEFAULT NULL,
  `heart_rate` int(11) DEFAULT NULL,
  `temperature` decimal(5,2) DEFAULT NULL,
  `hemoglobin` decimal(5,2) DEFAULT NULL,
  `gestational_age` int(11) DEFAULT NULL,
  `immunization_status` varchar(255) DEFAULT NULL,
  `contraceptive_use` varchar(255) DEFAULT NULL,
  `lab_results` text DEFAULT NULL,
  `smoking_status` enum('none','former','current') DEFAULT NULL,
  `alcohol_use` enum('none','former','current') DEFAULT NULL,
  `drug_use` enum('none','former','current') DEFAULT NULL,
  `lifestyle_notes` text DEFAULT NULL,
  `obstetric_history` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `risk_level` enum('Low','Medium','High') DEFAULT NULL,
  `risk_assessment_mode` enum('automatic','manual') DEFAULT NULL,
  `risk_notes` text DEFAULT NULL,
  `bhw_president_id` bigint(20) UNSIGNED DEFAULT NULL,
  `bhw_president_approved_at` timestamp NULL DEFAULT NULL,
  `workflow_status` enum('recorded_by_bhw','submitted_to_bhw_president','accepted_by_midwife','submitted_to_midwife') NOT NULL DEFAULT 'recorded_by_bhw',
  `submitted_to_bhw_president_at` timestamp NULL DEFAULT NULL,
  `submitted_to_midwife_at` timestamp NULL DEFAULT NULL,
  `midwife_accepted_at` timestamp NULL DEFAULT NULL,
  `workflow_notes` text DEFAULT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `archived_at` timestamp NULL DEFAULT NULL,
  `archived_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `health_records_archived`
--

INSERT INTO `health_records_archived` (`id`, `user_id`, `walk_in_patient_id`, `pregnancy_id`, `recorded_by_id`, `bp`, `weight`, `height`, `bmi`, `heart_rate`, `temperature`, `hemoglobin`, `gestational_age`, `immunization_status`, `contraceptive_use`, `lab_results`, `smoking_status`, `alcohol_use`, `drug_use`, `lifestyle_notes`, `obstetric_history`, `notes`, `recommendations`, `risk_level`, `risk_assessment_mode`, `risk_notes`, `bhw_president_id`, `bhw_president_approved_at`, `workflow_status`, `submitted_to_bhw_president_at`, `submitted_to_midwife_at`, `midwife_accepted_at`, `workflow_notes`, `is_archived`, `archived_at`, `archived_reason`, `created_at`, `updated_at`) VALUES
(19, 6, NULL, 4, 5, '120/80', 56.00, 170.00, 19.38, 72, 35.00, NULL, NULL, NULL, NULL, NULL, 'none', 'none', 'none', NULL, NULL, NULL, NULL, 'Low', 'automatic', 'Blood pressure is elevated.', NULL, NULL, 'accepted_by_midwife', NULL, NULL, '2026-04-27 16:05:21', NULL, 1, '2026-04-27 16:05:28', 'Archived by midwife', '2026-04-27 16:05:21', '2026-04-27 16:05:21');

-- --------------------------------------------------------

--
-- Table structure for table `learning_materials`
--

CREATE TABLE `learning_materials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category` varchar(100) NOT NULL DEFAULT 'general',
  `material_type` enum('article','link','file','video','quiz') DEFAULT 'article',
  `link_url` varchar(255) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL COMMENT 'YouTube or direct URL',
  `quiz_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`quiz_data`)),
  `week_number` tinyint(3) UNSIGNED DEFAULT NULL COMMENT 'For pregnancy week guides',
  `image` varchar(255) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `learning_materials`
--

INSERT INTO `learning_materials` (`id`, `title`, `content`, `category`, `material_type`, `link_url`, `video_url`, `quiz_data`, `week_number`, `image`, `file`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Healthy Pregnancy Tips', 'Maintain a balanced diet rich in folic acid, iron, and calcium. Exercise regularly and get enough rest. Avoid alcohol, smoking, and drugs. Attend all prenatal checkups and follow your healthcare provider\'s advice.', 'general', 'article', NULL, NULL, NULL, NULL, 'learning_materials/RVMrQPIdMlwIBIZHvOUnSKIdsAFZjq9ORUmafCnH.jpg', NULL, '2026-03-26 07:58:44', '2026-04-15 15:36:42', NULL),
(2, 'Prenatal Care Guidelines', 'Regular prenatal checkups are essential for monitoring maternal and fetal health. Schedule monthly visits until 28 weeks, then bi-weekly until 36 weeks, and weekly until delivery. Bring questions to each appointment.', 'general', 'article', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-26 07:58:44', '2026-03-26 07:58:44', NULL),
(3, 'Nutrition During Pregnancy', 'Eat a variety of fruits, vegetables, whole grains, and lean proteins. Take prenatal vitamins daily. Stay hydrated with 8-10 glasses of water. Avoid raw fish, unpasteurized dairy, and deli meats.', 'general', 'article', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-26 07:58:44', '2026-03-26 07:58:44', NULL),
(4, 'Exercise for Expectant Mothers', 'Low-impact exercises like walking, swimming, and prenatal yoga are beneficial. Aim for 30 minutes of moderate activity most days. Avoid exercises that involve lying flat on your back after the first trimester.', 'general', 'article', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-26 07:58:44', '2026-03-26 07:58:44', NULL),
(5, 'Common Pregnancy Discomforts', 'Morning sickness, fatigue, and back pain are common. Eat small, frequent meals for nausea. Use pillows for support when sleeping. Practice good posture and wear comfortable shoes.', 'general', 'article', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-26 07:58:44', '2026-03-26 07:58:44', NULL),
(6, 'WHO Pregnancy Guidelines', 'Official World Health Organization guidelines for prenatal care and maternal health.', 'general', 'link', 'https://www.who.int/health-topics/pregnancy', NULL, NULL, NULL, NULL, NULL, '2026-03-26 07:58:44', '2026-03-26 07:58:44', NULL),
(7, 'CDC Pregnancy Resources', 'Centers for Disease Control and Prevention comprehensive pregnancy resources.', 'general', 'link', 'https://www.cdc.gov/reproductive-health/pregnancy/index.htm', NULL, NULL, NULL, NULL, NULL, '2026-03-26 07:58:44', '2026-03-26 07:58:44', NULL),
(8, 'March of Dimes Pregnancy Guide', 'Educational resources and support for healthy pregnancies from March of Dimes.', 'general', 'link', 'https://www.marchofdimes.org/pregnancy', NULL, NULL, NULL, NULL, NULL, '2026-03-26 07:58:44', '2026-03-26 07:58:44', NULL),
(9, 'American College of Obstetricians and Gynecologists', 'ACOG provides authoritative guidance on women\'s health and pregnancy care.', 'general', 'link', 'https://www.acog.org/womens-health/pregnancy', NULL, NULL, NULL, NULL, NULL, '2026-03-26 07:58:44', '2026-03-26 07:58:44', NULL),
(10, 'Pregnancy Checklist', 'Downloadable checklist for each trimester including appointments, tests, and preparations needed. This file helps you stay organized throughout your pregnancy journey.', 'general', 'file', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-26 07:58:44', '2026-03-26 07:58:44', NULL),
(11, 'Birth Plan Template', 'Customizable birth plan template to help you communicate your preferences for labor and delivery with your healthcare team. Includes options for pain management, birthing positions, and postpartum care.', 'general', 'file', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-26 07:58:44', '2026-03-26 07:58:44', NULL),
(12, 'Newborn Care Guide', 'Comprehensive guide for newborn care including feeding, sleeping, bathing, and health monitoring. Essential information for first-time parents preparing for their baby\'s arrival.', 'general', 'file', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-26 07:58:44', '2026-03-26 07:58:44', NULL),
(13, 'Postpartum Recovery Tips', 'Downloadable guide for postpartum recovery including physical healing, emotional wellness, and when to seek medical help. Covers the first 6 weeks after delivery.', 'general', 'file', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-26 07:58:44', '2026-03-26 07:58:44', NULL),
(27, 'test material', 'uyescvbhuytrd', 'general', 'article', NULL, NULL, NULL, NULL, 'learning_materials/g7Os9ASadYOUtC215Xm8k8o5RasxN3Bc3Bd0hRtt.jpg', NULL, '2026-04-19 16:34:03', '2026-04-19 16:34:03', NULL),
(28, 'What to expect in you First Trimester of pregnancy', 'for educational purposes', 'prenatal-care', 'video', NULL, 'https://www.youtube.com/watch?v=cfn04QUO4B8', NULL, 1, NULL, 'learning_files/JriexARvAU5pz5C724zreaUk2Sed6e7Iv2ivbbX1.mp4', '2026-09-02 15:08:09', '2026-09-02 15:08:09', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `maternal_assessments`
--

CREATE TABLE `maternal_assessments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `maternal_care_target_client_id` bigint(20) UNSIGNED NOT NULL,
  `assessment_type` enum('nutritional','postpartum_24h','postpartum_7d') NOT NULL,
  `age_months` varchar(255) DEFAULT NULL,
  `length_cm` decimal(6,2) DEFAULT NULL,
  `length_date` date DEFAULT NULL,
  `weight_kg` decimal(6,2) DEFAULT NULL,
  `weight_date` date DEFAULT NULL,
  `status` enum('low','normal','high','S','W-MAM','W-SAM','O','N') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maternal_care_target_clients`
--

CREATE TABLE `maternal_care_target_clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `pregnancy_id` bigint(20) UNSIGNED NOT NULL,
  `date_of_registration` date DEFAULT NULL,
  `family_serial_no` varchar(255) DEFAULT NULL,
  `fim_status` tinyint(1) DEFAULT NULL,
  `nutritional_assessment_status` enum('low','normal','high') DEFAULT NULL,
  `deworming_date` date DEFAULT NULL,
  `health_conditions` longtext DEFAULT NULL,
  `health_condition_other` varchar(255) DEFAULT NULL,
  `pregnancy_terminated_date` date DEFAULT NULL,
  `pregnancy_outcome` enum('ft','pt','fd','ab') DEFAULT NULL,
  `outcome_details` text DEFAULT NULL,
  `pregnancy_outcome_sex` enum('M','F') DEFAULT NULL,
  `delivery_type` enum('cs','vd') DEFAULT NULL,
  `birth_weight_category` enum('low','normal','unknown') DEFAULT NULL,
  `facility_delivery_type` varchar(255) DEFAULT NULL,
  `facility_bemonc_capable` varchar(255) DEFAULT NULL,
  `facility_ownership` enum('public','private') DEFAULT NULL,
  `non_health_facility_code` varchar(255) DEFAULT NULL,
  `birth_attendant_code` varchar(50) DEFAULT NULL,
  `delivery_remarks` text DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `delivery_time` time DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `gravida` int(10) UNSIGNED DEFAULT NULL,
  `parity` int(10) UNSIGNED DEFAULT NULL,
  `gtpal_term` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `gtpal_preterm` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `gtpal_abortions` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `gtpal_living_children` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `recorded_by_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `maternal_care_target_clients`
--

INSERT INTO `maternal_care_target_clients` (`id`, `user_id`, `pregnancy_id`, `date_of_registration`, `family_serial_no`, `fim_status`, `nutritional_assessment_status`, `deworming_date`, `health_conditions`, `health_condition_other`, `pregnancy_terminated_date`, `pregnancy_outcome`, `outcome_details`, `pregnancy_outcome_sex`, `delivery_type`, `birth_weight_category`, `facility_delivery_type`, `facility_bemonc_capable`, `facility_ownership`, `non_health_facility_code`, `birth_attendant_code`, `delivery_remarks`, `delivery_date`, `delivery_time`, `created_at`, `updated_at`, `gravida`, `parity`, `gtpal_term`, `gtpal_preterm`, `gtpal_abortions`, `gtpal_living_children`, `recorded_by_id`) VALUES
(1, 6, 4, '2026-03-23', NULL, 0, NULL, NULL, '[]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-24 20:20:57', '2026-04-24 20:20:57', 1, NULL, 0, 0, 0, 0, NULL),
(2, 11, 5, NULL, NULL, 0, NULL, NULL, '[]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-25 06:53:38', '2026-04-25 06:53:38', 1, 0, 0, 0, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `maternal_deaths`
--

CREATE TABLE `maternal_deaths` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `walk_in_patient_id` bigint(20) UNSIGNED DEFAULT NULL,
  `pregnancy_id` bigint(20) UNSIGNED DEFAULT NULL,
  `recorded_by_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_by_id` bigint(20) UNSIGNED DEFAULT NULL,
  `purok_id` bigint(20) UNSIGNED DEFAULT NULL,
  `barangay` varchar(255) DEFAULT NULL,
  `death_date` date NOT NULL,
  `death_time` time DEFAULT NULL,
  `age_at_death` int(11) DEFAULT NULL,
  `place_of_death` enum('home','barangay_health_station','rhu','city_hospital','provincial_hospital','private_hospital','in_transit','other') NOT NULL,
  `cause_of_death` varchar(255) DEFAULT NULL,
  `cause_category` enum('hemorrhage','hypertension_eclampsia','sepsis','obstructed_labor','unsafe_abortion','embolism','other_direct','indirect_cause','unknown') DEFAULT NULL,
  `death_timing` enum('during_pregnancy','during_delivery','within_24_hours_postpartum','within_7_days_postpartum','within_42_days_postpartum','unknown') DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `audit_status` enum('pending','under_review','reviewed','closed') NOT NULL DEFAULT 'pending',
  `audit_notes` text DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maternal_morbidities`
--

CREATE TABLE `maternal_morbidities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `walk_in_patient_id` bigint(20) UNSIGNED DEFAULT NULL,
  `pregnancy_id` bigint(20) UNSIGNED DEFAULT NULL,
  `recorded_by_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_by_id` bigint(20) UNSIGNED DEFAULT NULL,
  `purok_id` bigint(20) UNSIGNED DEFAULT NULL,
  `barangay` varchar(255) DEFAULT NULL,
  `event_date` date NOT NULL,
  `event_time` time DEFAULT NULL,
  `complication_type` enum('severe_hemorrhage','eclampsia','severe_preeclampsia','sepsis','ruptured_uterus','severe_anemia','obstructed_labor','placenta_previa','placental_abruption','other') NOT NULL,
  `place_of_event` enum('home','barangay_health_station','rhu','city_hospital','provincial_hospital','private_hospital','in_transit','other') NOT NULL,
  `outcome` enum('survived_no_intervention','survived_with_intervention','transferred_to_higher_facility','died') NOT NULL DEFAULT 'survived_with_intervention',
  `maternal_death_id` bigint(20) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `interventions_done` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `review_status` enum('pending','under_review','reviewed','closed') NOT NULL DEFAULT 'pending',
  `review_notes` text DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maternal_postpartum_care`
--

CREATE TABLE `maternal_postpartum_care` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `maternal_care_target_client_id` bigint(20) UNSIGNED NOT NULL,
  `iron_month` enum('first','second','third') NOT NULL,
  `iron_given` varchar(255) DEFAULT NULL,
  `vitamin_a_date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maternal_prenatal_visits`
--

CREATE TABLE `maternal_prenatal_visits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `maternal_care_target_client_id` bigint(20) UNSIGNED NOT NULL,
  `visit_number` int(11) NOT NULL,
  `trimester` enum('first','second','third') NOT NULL,
  `visit_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maternal_screenings`
--

CREATE TABLE `maternal_screenings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `maternal_care_target_client_id` bigint(20) UNSIGNED NOT NULL,
  `screening_type` enum('syphilis','hepatitis_b','hiv','gestational_diabetes','cbc') NOT NULL,
  `screening_date` date DEFAULT NULL,
  `result` enum('positive','negative','with_anemia','without_anemia') DEFAULT NULL,
  `given_iron` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maternal_supplements`
--

CREATE TABLE `maternal_supplements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `maternal_care_target_client_id` bigint(20) UNSIGNED NOT NULL,
  `supplement_type` enum('iron_folic','calcium','iodine') NOT NULL,
  `visit_number` int(11) NOT NULL,
  `distribution_date` date DEFAULT NULL,
  `tablets_given` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maternal_vaccinations`
--

CREATE TABLE `maternal_vaccinations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `maternal_care_target_client_id` bigint(20) UNSIGNED NOT NULL,
  `vaccine_type` enum('td','dpt_hepb_hib','opv','pcv','ipv','mmr') NOT NULL,
  `dose_number` int(11) NOT NULL,
  `vaccination_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menstruation_dailies`
--

CREATE TABLE `menstruation_dailies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `date` date NOT NULL,
  `is_period` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sender_id` bigint(20) UNSIGNED DEFAULT NULL,
  `receiver_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `body` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `reply_to_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `subject`, `body`, `is_read`, `read_at`, `reply_to_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(7, 5, 11, 'try', 'try', 1, '2026-04-24 22:36:16', NULL, '2026-04-24 22:35:55', '2026-04-24 22:36:16', NULL),
(8, 11, 5, NULL, 'wao', 1, '2026-04-24 22:36:34', 7, '2026-04-24 22:36:22', '2026-04-24 22:36:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_03_26_141707_modify_users_table_add_roles', 1),
(5, '2026_03_26_141712_create_midwives_table', 1),
(6, '2026_03_26_141843_create_bhw_table', 1),
(7, '2026_03_26_141916_create_pregnancies_table', 1),
(8, '2026_03_26_141918_create_menstruation_records_table', 1),
(9, '2026_03_26_141920_create_checkups_table', 1),
(10, '2026_03_26_141921_create_health_records_table', 1),
(11, '2026_03_26_141923_create_learning_materials_table', 1),
(12, '2026_03_26_141924_create_forum_posts_table', 1),
(13, '2026_03_26_141928_create_forum_comments_table', 1),
(14, '2026_03_26_141930_create_forum_likes_table', 1),
(15, '2026_03_26_141932_create_notifications_table', 1),
(16, '2026_03_28_155924_add_flow_type_to_menstruation_records_table', 2),
(17, '2026_03_28_160308_make_end_date_nullable_in_menstruation_records_table', 3),
(18, '2026_03_28_163010_add_image_to_learning_materials_table', 4),
(19, '2026_04_02_001500_add_pregnancy_fields', 5),
(20, '2026_04_02_010000_create_menstruation_dailies_table', 6),
(21, '2026_04_03_000001_create_cycles_table', 7),
(22, '2026_04_03_000002_create_fertility_logs_table', 8),
(23, '2026_04_10_000001_create_bhw_monthly_reports_table', 9),
(25, '2026_04_11_010333_add_date_of_birth_to_users_table', 10),
(26, '2026_04_13_200000_add_profile_fields_to_users_table', 11),
(28, '2026_04_15_000001_prepare_staff_merge_into_users', 12),
(29, '2026_04_15_000002_fix_data_consistency_issues', 13),
(30, '2026_04_15_000003_fix_health_records_cascade', 14),
(31, '2026_04_15_000004_drop_all_legacy_tables_and_columns', 15),
(32, '2026_04_13_031400_add_performance_indexes', 16),
(33, '2026_04_13_200001_add_post_image_to_forum_posts_table', 16),
(34, '2026_04_15_000005_create_symptoms_and_moods_tables', 17),
(35, '2026_04_15_000006_create_menstruation_daily_pivot_tables', 18),
(36, '2026_04_15_000007_migrate_menstruation_dailies_json', 18),
(37, '2026_04_15_000008_drop_json_columns_from_menstruation_dailies', 18),
(39, '2026_04_16_004804_sync_menstruation_records_to_cycles', 19),
(40, '2026_04_16_010824_fix_user6_period_date', 20),
(41, '2026_04_16_020000_add_fertility_tracking_to_menstruation_records', 21),
(42, '2026_04_16_035347_create_menstruation_record_symptoms_table', 22),
(43, '2026_04_16_035431_migrate_menstruation_symptoms_to_pivot', 22),
(44, '2026_04_16_040000_drop_legacy_symptoms_column', 23),
(45, '2026_04_16_050000_drop_unused_menstruation_dailies_columns', 24),
(46, '2026_04_16_070000_drop_unused_system_tables', 25),
(47, '2026_04_16_080000_drop_mood_tables', 26),
(48, '2026_04_18_100001_add_status_to_users_table', 27),
(49, '2026_04_18_100002_add_scheduled_by_to_checkups', 27),
(50, '2026_04_18_100003_add_extended_fields_to_health_records', 27),
(51, '2026_04_18_100004_enhance_notifications_table', 27),
(52, '2026_04_18_100005_enhance_learning_materials', 28),
(53, '2026_04_18_100006_create_messages_table', 28),
(54, '2026_04_19_001519_add_rescheduled_status_to_checkups_table', 29),
(56, '2026_04_19_015719_create_preventive_interventions_table', 30),
(57, '2026_04_19_023406_add_recommendations_to_health_records_table', 31),
(58, '2026_04_19_032108_add_file_to_learning_materials_table', 32),
(59, '2026_04_19_045320_add_scheduled_at_to_messages_table', 33),
(60, '2026_04_19_100001_create_midwife_profiles_table', 34),
(61, '2026_04_19_100002_create_bhw_profiles_table', 34),
(62, '2026_04_19_100003_create_patient_profiles_table', 34),
(63, '2026_04_19_100004_migrate_health_fields_to_patient_profiles', 34),
(64, '2026_04_19_150000_create_midwives_table', 35),
(65, '2026_04_19_150001_create_patients_table', 35),
(66, '2026_04_19_150002_create_bhws_table', 35),
(67, '2026_04_19_150003_migrate_users_to_role_tables', 36),
(68, '2026_04_19_150004_convert_foreign_keys_to_polymorphic', 37),
(69, '2026_04_19_150005_migrate_polymorphic_data', 38),
(70, '2026_04_19_150006_drop_users_table', 39),
(71, '2026_04_19_150007_add_profile_image_to_role_tables', 40),
(72, '2026_04_19_150008_convert_menstruation_records_to_patient_id', 41),
(73, '2026_04_19_201500_repair_cycles_polymorphic_schema', 42),
(74, '2026_04_19_203000_rename_patients_table_to_women', 43),
(75, '2026_04_19_204000_rename_patient_profiles_table_to_women_profiles', 44),
(76, '2026_04_19_211000_drop_users_backup_and_legacy_old_columns', 45),
(77, '2026_04_20_000001_add_blue_green_profile_fk_columns', 46),
(78, '2026_04_20_000002_add_blue_green_archived_health_record_patient_id', 46),
(79, '2026_04_20_000001_normalize_checkups_table', 1),
(80, '2026_04_20_000002_normalize_health_records_table', 47),
(81, '2026_04_20_000003_normalize_pregnancies_table', 47),
(82, '2026_04_20_000004_normalize_menstruation_tables', 1),
(83, '2026_04_20_000005_normalize_preventive_interventions_table', 48),
(84, '2026_04_20_000006_normalize_forum_tables', 1),
(85, '2026_04_20_000007_normalize_messages_table', 49),
(86, '2026_04_20_000008_normalize_notifications_table', 49),
(87, '2026_04_20_000009_normalize_health_records_archived_table', 1),
(88, '2026_04_20_000010_cleanup_remaining_polymorphic_columns', 50),
(89, '2026_04_20_000001_drop_profile_tables', 51),
(90, '2026_04_21_111113_add_rejection_reason_to_women_table', 52),
(91, '2026_04_24_000001_create_bhw_presidents_table', 53),
(97, '2026_04_24_000002_create_puroks_table', 54),
(98, '2026_04_24_000003_create_bhw_assignments_table', 54),
(99, '2026_04_24_000004_create_tasks_table', 54),
(100, '2026_04_24_000005_add_bhw_president_workflow_to_tables', 54),
(112, '2026_04_24_000006_create_vitamins_table', 55),
(113, '2026_04_24_000007_create_vitamin_distributions_table', 56),
(114, '2026_04_24_000008_create_immunizations_table', 56),
(115, '2026_04_24_000009_create_immunization_schedules_table', 56),
(116, '2026_04_24_000011_create_child_records_table', 57),
(117, '2026_04_24_000012_create_patient_immunizations_table', 57),
(118, '2026_04_24_000013_create_child_checkups_table', 57),
(119, '2026_04_24_000014_create_patient_transfers_table', 57),
(120, '2026_04_24_000015_add_trimester_to_pregnancies_table', 57),
(121, '2026_04_24_000016_add_trimester_and_vitamin_tracking_to_checkups_table', 57),
(122, '2026_04_24_000017_create_trimester_checkups_table', 57),
(123, '2026_04_24_000019_create_user_barangays_table', 58),
(124, '2026_04_24_000020_create_bhw_monthly_report_filters_table', 58),
(125, '2026_04_24_000021_create_quiz_questions_table', 58),
(126, '2026_04_24_000022_create_quiz_answers_table', 58),
(127, '2026_04_24_000023_create_lookup_values_table', 58),
(128, '2026_04_24_000024_add_lookup_foreign_keys_to_users_table', 58),
(129, '2026_04_24_000025_migrate_and_cleanup_denormalized_data', 58),
(130, '2026_04_24_000026_consolidate_health_records_archived', 59),
(131, '2026_04_24_000027_remove_unused_normalization_tables', 60),
(137, '2026_04_24_000028_create_bhws_table', 61),
(138, '2026_04_24_000029_create_midwives_table', 61),
(139, '2026_04_24_000030_create_women_table', 61),
(140, '2026_04_24_000031_create_bhw_presidents_table', 61),
(141, '2026_04_24_000032_consolidate_user_tables', 61),
(142, '2026_04_24_135811_add_gender_to_users_table', 61),
(143, '2026_04_24_141541_add_archived_at_to_users_table', 62),
(144, '2026_04_24_151710_update_role_enum_fix_woman_to_user', 63),
(145, '2026_04_24_160000_add_maternal_assessment_fields', 64),
(146, '2026_04_24_210000_seed_burgos_padlan_puroks', 65),
(147, '2026_04_24_230001_create_maternal_care_target_clients_table', 66),
(148, '2026_04_25_000537_split_name_column_to_first_middle_last', 67),
(149, '2026_04_25_001408_add_id_image_to_users_table', 68),
(150, '2026_04_25_002247_change_id_image_to_front_and_back', 69),
(151, '2026_04_25_003505_add_purok_id_to_users_table', 70),
(153, '2026_04_25_000100_create_child_care_target_clients_table', 71),
(154, '2026_04_25_020500_add_gtpal_and_health_conditions_to_pregnancies_table', 72),
(155, '2026_04_25_000001_add_multiple_prenatal_dates_to_maternal_care_target_clients', 73),
(157, '2026_04_25_044917_add_gravida_parity_to_maternal_care_target_clients_table', 74),
(158, '2026_04_25_050413_create_checkup_referrals_table', 74),
(159, '2026_04_25_050416_create_walk_in_patients_table', 74),
(160, '2026_04_25_050516_add_foreign_keys_to_checkup_referrals_table', 75),
(161, '2026_04_25_120000_add_pregnancy_id_to_health_records_table', 76),
(162, '2026_04_25_130000_make_core_health_record_measurements_nullable', 76),
(163, '2026_04_25_140000_backfill_health_records_from_pregnancies', 76),
(164, '2026_04_25_150000_add_phone_to_users_table', 77),
(165, '2026_04_25_070000_add_scheduled_time_to_checkups_table', 78),
(166, '2026_04_25_080000_add_contact_number_to_users_table', 79),
(167, '2026_04_25_150000_add_workflow_and_walk_in_support_to_records', 80),
(168, '2026_04_25_060000_fix_broken_foreign_keys_and_drop_unused_tables', 81),
(169, '2026_04_25_181622_drop_legacy_user_tables', 82),
(170, '2026_04_25_183854_add_workflow_fields_to_pregnancies_table', 82),
(171, '2026_04_25_184911_add_report_type_to_bhw_monthly_reports_table', 83),
(172, '2026_04_25_195805_drop_mood_and_menstruation_daily_moods_tables', 84),
(173, '2026_04_19_150005_5_recreate_users_table_with_separated_names', 85),
(174, '2026_04_25_230000_remove_basal_temp_from_menstruation_dailies', 85),
(180, '2026_04_25_230001_remove_basal_body_temp_from_fertility_logs', 86),
(181, '2026_04_25_230002_remove_basal_temp_from_menstruation_records', 86),
(182, '2026_04_26_000001_simplify_messages_table', 87),
(185, '2026_04_26_000003_consolidate_forum_tables', 88),
(186, '2026_04_26_000004_consolidate_forum_likes_table', 89),
(187, '2026_04_26_000004_consolidate_checkups_table', 90),
(188, '2026_04_26_000005_consolidate_health_records_table', 91),
(189, '2026_04_26_000001_normalize_maternal_care_target_clients', 92),
(190, '2026_04_26_000001_remove_symptoms_notes_fertility_from_menstruation', 93),
(191, '2026_04_26_000002_consolidate_notifications_table', 93),
(192, '2026_04_26_000002_normalize_child_care_target_clients', 93),
(194, '2026_04_26_000003_drop_denormalized_columns', 94),
(195, '2026_04_26_154106_rename_woman_id_to_user_id', 94),
(196, '2026_04_26_000006_rename_recorded_by_bhw_id_to_recorded_by_id_in_walk_in_patients_table', 95),
(198, '2026_04_26_000007_add_filters_column_to_bhw_monthly_reports_table', 96),
(199, '2026_04_26_000008_fix_checkups_scheduled_by_columns', 96),
(200, '2026_04_26_000009_remove_flow_columns_from_menstruation_tables', 97),
(202, '2026_04_26_000010_migrate_and_drop_menstruation_records', 98),
(203, '2026_04_26_000011_add_pregnancy_id_to_maternal_care_target_clients', 99),
(205, '2026_04_26_000012_move_obstetric_fields_to_maternal_care', 100),
(206, '2026_04_26_000013_clean_redundant_columns', 101),
(207, '2026_04_26_000014_add_walk_in_patient_to_pregnancies', 102),
(208, '2026_04_26_000015_make_user_id_nullable_in_maternal_care_target_clients', 103),
(209, '2026_04_26_000020_add_unique_constraint_on_pregnancy_id_in_maternal_care_target_clients', 104),
(210, '2026_04_26_000021_delete_orphaned_cycles_with_null_user_id', 105),
(211, '2026_04_26_000022_drop_legacy_columns_from_users_table', 106),
(212, '2026_04_26_000023_add_pregnancy_id_to_checkups_table', 107),
(213, '2026_04_26_220601_drop_trimester_checkups_table', 108),
(214, '2026_04_26_120000_restore_user_id_foreign_keys', 109),
(215, '2026_04_26_130000_drop_fertility_logs_table', 110),
(216, '2026_04_26_000016_remove_overstrict_unique_constraint_from_learning_materials', 111),
(217, '2026_04_26_140000_add_submission_workflow_to_bhw_monthly_reports', 112),
(218, '2026_04_26_232910_add_missing_foreign_keys', 113),
(219, '2026_04_27_000001_create_health_records_archived_table', 114),
(220, '2026_04_29_092300_add_created_by_columns_to_users_table', 115),
(221, '2026_04_29_094900_add_quiz_data_to_learning_materials', 115),
(222, '2026_04_29_121934_add_recorded_by_id_to_maternal_care_target_clients_table', 115),
(223, '2026_05_23_000001_add_cho_rhu_roles_and_fields_to_users', 115),
(224, '2026_05_23_000002_create_emergency_contacts_table', 115),
(225, '2026_05_23_000003_create_supply_requests_table', 115),
(226, '2026_05_23_000004_create_activity_logs_table', 115),
(227, '2026_05_23_000005_create_maternal_deaths_table', 115),
(228, '2026_05_23_000006_create_maternal_morbidities_table', 115),
(229, '2026_05_23_000007_add_facility_delivery_to_pregnancies', 115),
(230, '2026_07_10_000001_add_sms_opt_out_to_users', 115),
(231, '2026_07_10_000002_create_sms_logs_table', 115),
(232, '2026_08_10_153338_add_partner_contact_to_users_table', 116),
(233, '2026_08_10_160000_add_soft_deletes_to_major_tables', 117),
(234, '2026_08_10_160001_add_contact_order_to_emergency_contacts_table', 118),
(235, '2026_08_10_160002_add_soft_deletes_to_emergency_contacts_table', 119),
(236, '2026_08_11_000000_add_soft_deletes_to_auxiliary_tables', 120),
(237, '2026_08_22_213400_rename_twilio_sid_to_provider_sid_in_sms_logs', 121),
(238, '2026_09_02_225500_change_category_in_learning_materials_table', 122);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `type` enum('info','warning','danger','success') NOT NULL DEFAULT 'info',
  `message` varchar(255) NOT NULL,
  `action_url` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `type`, `message`, `action_url`, `is_read`, `read_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(66, 6, '??? Registration Approved', 'success', 'Your registration has been approved! You can now log in to ReproCare.', 'http://localhost/user/dashboard', 1, NULL, '2026-04-24 07:17:57', '2026-04-24 09:39:44', NULL),
(68, 6, 'You have a checkup scheduled on April 29, 2026 at 9:00 AM for: 1st tri check up', 'info', 'You have a checkup scheduled on April 29, 2026 at 9:00 AM for: 1st tri check up', NULL, 1, NULL, '2026-04-24 20:41:15', '2026-04-25 06:33:40', NULL),
(69, 11, '??? Registration Approved', 'success', 'Your registration has been approved! You can now log in to ReproCare.', 'http://127.0.0.1:8000/user/dashboard', 1, NULL, '2026-04-24 21:49:23', '2026-04-25 06:38:46', NULL),
(70, 11, 'New Message', 'info', 'New Message from Admin Midwife: try', '/user/messages/7', 1, NULL, '2026-04-24 22:35:55', '2026-04-25 06:38:46', NULL),
(71, 5, 'New Message', 'info', 'New Message from Madonna M. De Vera: wao', '/midwife/messages/7', 1, NULL, '2026-04-24 22:36:22', '2026-04-24 23:11:44', NULL),
(73, 6, '?????? Medium Risk Alert', 'warning', 'Our system has detected a health concern: High blood pressure detected: 150/95 Please contact your health worker immediately.', '/user/health-records', 0, NULL, '2026-04-25 09:08:05', '2026-04-25 09:08:05', NULL),
(74, 5, '?????? Patient Risk Alert ??? Maria M. Santa', 'warning', 'Medium risk detected for Maria M. Santa: High blood pressure detected: 150/95', '/midwife/patients/6', 1, NULL, '2026-04-25 09:08:05', '2026-04-25 14:23:51', NULL),
(75, 6, '?????? Medium Risk Alert', 'warning', 'Our system has detected a health concern: High blood pressure detected: 150/95 Please contact your health worker immediately.', '/user/health-records', 0, NULL, '2026-04-25 13:50:45', '2026-04-25 13:50:45', NULL),
(77, 14, 'Registration Approved', 'success', 'Your registration has been approved. You can now log in to ReproCare.', 'http://127.0.0.1:8000/user/dashboard', 0, NULL, '2026-08-03 09:42:43', '2026-08-03 09:42:43', NULL),
(78, 6, '⚠️ Medium Risk Alert', 'warning', 'Our system has detected a health concern: 1 scheduled checkup(s) are overdue. Please contact your health worker immediately.', '/user/health-records', 0, NULL, '2026-08-10 10:19:14', '2026-08-10 10:19:14', NULL),
(79, NULL, '⚠️ Patient Risk Alert – Maria M. Santa', 'warning', 'Medium risk detected for Maria M. Santa: 1 scheduled checkup(s) are overdue.', '/midwife/patients/6', 0, NULL, '2026-08-10 10:19:14', '2026-08-10 10:19:14', NULL),
(80, 6, '⚠️ Medium Risk Alert', 'warning', 'Our system has detected a health concern: 1 scheduled checkup(s) are overdue. Please contact your health worker immediately.', '/user/health-records', 0, NULL, '2026-08-10 17:14:28', '2026-08-10 17:14:28', NULL),
(81, NULL, '⚠️ Patient Risk Alert – Maria M. Santa', 'warning', 'Medium risk detected for Maria M. Santa: 1 scheduled checkup(s) are overdue.', '/midwife/patients/6', 0, NULL, '2026-08-10 17:14:29', '2026-08-10 17:14:29', NULL),
(82, 6, '⚠️ Medium Risk Alert', 'warning', 'Our system has detected a health concern: 1 scheduled checkup(s) are overdue. Please contact your health worker immediately.', '/user/health-records', 0, NULL, '2026-08-10 17:14:41', '2026-08-10 17:14:41', NULL),
(83, NULL, '⚠️ Patient Risk Alert – Maria M. Santa', 'warning', 'Medium risk detected for Maria M. Santa: 1 scheduled checkup(s) are overdue.', '/midwife/patients/6', 0, NULL, '2026-08-10 17:14:41', '2026-08-10 17:14:41', NULL),
(84, 6, '⚠️ Medium Risk Alert', 'warning', 'Our system has detected a health concern: 2 scheduled checkup(s) are overdue. Please contact your health worker immediately.', '/user/health-records', 0, NULL, '2026-08-22 07:59:02', '2026-08-22 07:59:02', NULL),
(85, NULL, '⚠️ Patient Risk Alert – Maria M. Santa', 'warning', 'Medium risk detected for Maria M. Santa: 2 scheduled checkup(s) are overdue.', '/midwife/patients/6', 0, NULL, '2026-08-22 07:59:04', '2026-08-22 07:59:04', NULL),
(86, 6, '⚠️ Medium Risk Alert', 'warning', 'Our system has detected a health concern: 2 scheduled checkup(s) are overdue. Please contact your health worker immediately.', '/user/health-records', 0, NULL, '2026-08-22 14:01:36', '2026-08-22 14:01:36', NULL),
(87, NULL, '⚠️ Patient Risk Alert – Maria M. Santa', 'warning', 'Medium risk detected for Maria M. Santa: 2 scheduled checkup(s) are overdue.', '/midwife/patients/6', 0, NULL, '2026-08-22 14:01:37', '2026-08-22 14:01:37', NULL),
(88, 6, '⚠️ Medium Risk Alert', 'warning', 'Our system has detected a health concern: 2 scheduled checkup(s) are overdue. Please contact your health worker immediately.', '/user/health-records', 0, NULL, '2026-08-22 14:10:13', '2026-08-22 14:10:13', NULL),
(89, NULL, '⚠️ Patient Risk Alert – Maria M. Santa', 'warning', 'Medium risk detected for Maria M. Santa: 2 scheduled checkup(s) are overdue.', '/midwife/patients/6', 0, NULL, '2026-08-22 14:10:14', '2026-08-22 14:10:14', NULL),
(90, 6, '⚠️ Medium Risk Alert', 'warning', 'Our system has detected a health concern: 2 scheduled checkup(s) are overdue. Please contact your health worker immediately.', '/user/health-records', 0, NULL, '2026-08-22 14:31:13', '2026-08-22 14:31:13', NULL),
(91, NULL, '⚠️ Patient Risk Alert – Maria M. Santa', 'warning', 'Medium risk detected for Maria M. Santa: 2 scheduled checkup(s) are overdue.', '/midwife/patients/6', 0, NULL, '2026-08-22 14:31:14', '2026-08-22 14:31:14', NULL),
(92, 6, '⚠️ Medium Risk Alert', 'warning', 'Our system has detected a health concern: 2 scheduled checkup(s) are overdue. Elevated blood pressure: 120/80 mmHg. Please contact your health worker immediately.', '/user/health-records', 0, NULL, '2026-08-22 15:23:09', '2026-08-22 15:23:09', NULL),
(93, NULL, '⚠️ Patient Risk Alert – Maria M. Santa', 'warning', 'Medium risk detected for Maria M. Santa: 2 scheduled checkup(s) are overdue. Elevated blood pressure: 120/80 mmHg.', '/midwife/patients/6', 0, NULL, '2026-08-22 15:23:10', '2026-08-22 15:23:10', NULL),
(94, 11, '⚠️ Medium Risk Alert', 'warning', 'Our system has detected a health concern: Elevated blood pressure: 120/80 mmHg. Please contact your health worker immediately.', '/user/health-records', 0, NULL, '2026-08-22 15:23:11', '2026-08-22 15:23:11', NULL),
(95, 6, '⚠️ Medium Risk Alert', 'warning', 'Our system has detected a health concern: 2 scheduled checkup(s) are overdue. Elevated blood pressure: 120/80 mmHg. Please contact your health worker immediately.', '/user/health-records', 0, NULL, '2026-08-22 15:50:06', '2026-08-22 15:50:06', NULL),
(96, NULL, '⚠️ Patient Risk Alert – Maria M. Santa', 'warning', 'Medium risk detected for Maria M. Santa: 2 scheduled checkup(s) are overdue. Elevated blood pressure: 120/80 mmHg.', '/midwife/patients/6', 0, NULL, '2026-08-22 15:50:08', '2026-08-22 15:50:08', NULL),
(97, 11, '⚠️ Medium Risk Alert', 'warning', 'Our system has detected a health concern: Elevated blood pressure: 120/80 mmHg. Please contact your health worker immediately.', '/user/health-records', 0, NULL, '2026-08-22 15:50:08', '2026-08-22 15:50:08', NULL),
(98, 6, '⚠️ Medium Risk Alert', 'warning', 'Our system has detected a health concern: 2 scheduled checkup(s) are overdue. Elevated blood pressure: 120/80 mmHg. Please contact your health worker immediately.', '/user/health-records', 0, NULL, '2026-08-22 15:56:28', '2026-08-22 15:56:28', NULL),
(99, NULL, '⚠️ Patient Risk Alert – Maria M. Santa', 'warning', 'Medium risk detected for Maria M. Santa: 2 scheduled checkup(s) are overdue. Elevated blood pressure: 120/80 mmHg.', '/midwife/patients/6', 0, NULL, '2026-08-22 15:56:29', '2026-08-22 15:56:29', NULL),
(100, 11, '⚠️ Medium Risk Alert', 'warning', 'Our system has detected a health concern: Elevated blood pressure: 120/80 mmHg. Please contact your health worker immediately.', '/user/health-records', 0, NULL, '2026-08-22 15:56:29', '2026-08-22 15:56:29', NULL),
(101, 6, '⚠️ Medium Risk Alert', 'warning', 'Our system has detected a health concern: 2 scheduled checkup(s) are overdue. Elevated blood pressure: 120/80 mmHg. Please contact your health worker immediately.', '/user/health-records', 0, NULL, '2026-08-22 16:12:53', '2026-08-22 16:12:53', NULL),
(102, NULL, '⚠️ Patient Risk Alert – Maria M. Santa', 'warning', 'Medium risk detected for Maria M. Santa: 2 scheduled checkup(s) are overdue. Elevated blood pressure: 120/80 mmHg.', '/midwife/patients/6', 0, NULL, '2026-08-22 16:12:54', '2026-08-22 16:12:54', NULL),
(103, 11, '⚠️ Medium Risk Alert', 'warning', 'Our system has detected a health concern: Elevated blood pressure: 120/80 mmHg. Please contact your health worker immediately.', '/user/health-records', 0, NULL, '2026-08-22 16:12:54', '2026-08-22 16:12:54', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pregnancies`
--

CREATE TABLE `pregnancies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `walk_in_patient_id` bigint(20) UNSIGNED DEFAULT NULL,
  `lmp` date NOT NULL,
  `edd` date NOT NULL,
  `trimester` enum('first','second','third') DEFAULT NULL,
  `trimester_calculated_at` timestamp NULL DEFAULT NULL,
  `current_trimester_start_date` date DEFAULT NULL,
  `aog` int(11) NOT NULL,
  `risk_level` varchar(255) NOT NULL DEFAULT 'Low',
  `risk_assessment_mode` varchar(255) NOT NULL DEFAULT 'automatic',
  `risk_notes` text DEFAULT NULL,
  `is_high_risk` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `workflow_status` enum('draft','submitted_to_bhw_president','bhw_president_review','bhw_president_approved','bhw_president_rejected','completed') NOT NULL DEFAULT 'draft',
  `submitted_to_bhw_president_at` timestamp NULL DEFAULT NULL,
  `bhw_president_reviewed_at` timestamp NULL DEFAULT NULL,
  `workflow_notes` text DEFAULT NULL,
  `bhw_president_notes` text DEFAULT NULL,
  `ended_at` date DEFAULT NULL,
  `facility_delivery_place` enum('home','barangay_health_station','rhu_birth_center','city_hospital','provincial_hospital','private_hospital','other') DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `delivery_time` time DEFAULT NULL,
  `delivery_attendant` varchar(255) DEFAULT NULL,
  `delivery_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pregnancies`
--

INSERT INTO `pregnancies` (`id`, `user_id`, `walk_in_patient_id`, `lmp`, `edd`, `trimester`, `trimester_calculated_at`, `current_trimester_start_date`, `aog`, `risk_level`, `risk_assessment_mode`, `risk_notes`, `is_high_risk`, `notes`, `workflow_status`, `submitted_to_bhw_president_at`, `bhw_president_reviewed_at`, `workflow_notes`, `bhw_president_notes`, `ended_at`, `facility_delivery_place`, `delivery_date`, `delivery_time`, `delivery_attendant`, `delivery_notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
(4, 6, NULL, '2026-03-23', '2026-12-28', NULL, NULL, NULL, 4, 'Medium', 'automatic', 'Blood pressure is elevated. BMI is outside the usual low-risk range.', 0, NULL, 'draft', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-24 19:18:45', '2026-04-24 19:18:45', NULL),
(5, 11, NULL, '2026-02-23', '2026-11-30', NULL, NULL, NULL, 8, 'Medium', 'automatic', 'Blood pressure is elevated. BMI is outside the usual low-risk range.', 0, NULL, 'draft', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-24 21:51:12', '2026-04-24 21:51:12', NULL),
(6, NULL, 1, '2026-03-23', '2026-12-28', NULL, NULL, NULL, 4, 'Low', 'automatic', 'Blood pressure is elevated.', 0, NULL, 'draft', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-26 13:33:11', '2026-04-26 13:33:11', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `preventive_interventions`
--

CREATE TABLE `preventive_interventions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `risk_level` enum('Low','Medium','High') NOT NULL,
  `trigger_reason` text NOT NULL,
  `recommendations` text DEFAULT NULL,
  `intervention_type` varchar(255) NOT NULL,
  `status` enum('triggered','in_progress','completed','cancelled') NOT NULL DEFAULT 'triggered',
  `triggered_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `preventive_interventions`
--

INSERT INTO `preventive_interventions` (`id`, `user_id`, `risk_level`, `trigger_reason`, `recommendations`, `intervention_type`, `status`, `triggered_at`, `completed_at`, `notes`, `created_at`, `updated_at`) VALUES
(17, NULL, 'Medium', 'Low hemoglobin: 9.5 g/dL', '???? Increase iron-rich foods (red meat, leafy greens, beans). Take iron supplements if prescribed.\n???? Include vitamin C with meals to enhance iron absorption.\n???? Contact health worker if symptoms worsen or new concerns arise.', 'anemia_prevention', 'triggered', '2026-04-19 13:47:32', NULL, NULL, '2026-04-19 13:47:32', '2026-04-19 13:47:32'),
(18, NULL, 'Medium', 'Low hemoglobin: 9.5 g/dL', '???? Increase iron-rich foods (red meat, leafy greens, beans). Take iron supplements if prescribed.\n???? Include vitamin C with meals to enhance iron absorption.\n???? Contact health worker if symptoms worsen or new concerns arise.', 'anemia_prevention', 'triggered', '2026-04-19 16:52:08', NULL, NULL, '2026-04-19 16:52:08', '2026-04-19 16:52:08'),
(19, NULL, 'Medium', 'Low hemoglobin: 9.5 g/dL', '???? Increase iron-rich foods (red meat, leafy greens, beans). Take iron supplements if prescribed.\n???? Include vitamin C with meals to enhance iron absorption.\n???? Contact health worker if symptoms worsen or new concerns arise.', 'anemia_prevention', 'triggered', '2026-04-19 16:56:12', NULL, NULL, '2026-04-19 16:56:12', '2026-04-19 16:56:12'),
(20, NULL, 'Medium', 'High blood pressure detected: 150/95', '???? Monitor BP daily. Reduce salt intake. Consider referral to physician for hypertension management.\n???? May need antihypertensive medication if BP remains elevated.\n???? Contact health worker if symptoms worsen or new concerns arise.', 'hypertension_management', 'triggered', '2026-04-25 09:07:52', NULL, NULL, '2026-04-25 09:07:52', '2026-04-25 09:07:52'),
(21, NULL, 'Medium', 'High blood pressure detected: 150/95', '???? Monitor BP daily. Reduce salt intake. Consider referral to physician for hypertension management.\n???? May need antihypertensive medication if BP remains elevated.\n???? Contact health worker if symptoms worsen or new concerns arise.', 'hypertension_management', 'triggered', '2026-04-25 09:08:05', NULL, NULL, '2026-04-25 09:08:05', '2026-04-25 09:08:05'),
(22, NULL, 'Medium', 'High blood pressure detected: 150/95', '???? Monitor BP daily. Reduce salt intake. Consider referral to physician for hypertension management.\n???? May need antihypertensive medication if BP remains elevated.\n???? Contact health worker if symptoms worsen or new concerns arise.', 'hypertension_management', 'triggered', '2026-04-25 13:50:45', NULL, NULL, '2026-04-25 13:50:45', '2026-04-25 13:50:45'),
(23, NULL, 'Medium', '1 scheduled checkup(s) are overdue.', '⏰ Reschedule missed appointment as soon as possible.\n📞 Contact health worker if symptoms worsen or new concerns arise.', 'care_continuity', 'triggered', '2026-08-10 10:19:14', NULL, NULL, '2026-08-10 10:19:14', '2026-08-10 10:19:14'),
(24, NULL, 'Medium', '1 scheduled checkup(s) are overdue.', '⏰ Reschedule missed appointment as soon as possible.\n📞 Contact health worker if symptoms worsen or new concerns arise.', 'care_continuity', 'triggered', '2026-08-10 17:14:28', NULL, NULL, '2026-08-10 17:14:28', '2026-08-10 17:14:28'),
(25, NULL, 'Medium', '1 scheduled checkup(s) are overdue.', '⏰ Reschedule missed appointment as soon as possible.\n📞 Contact health worker if symptoms worsen or new concerns arise.', 'care_continuity', 'triggered', '2026-08-10 17:14:41', NULL, NULL, '2026-08-10 17:14:41', '2026-08-10 17:14:41'),
(26, NULL, 'Medium', '2 scheduled checkup(s) are overdue.', '⏰ Reschedule missed appointment as soon as possible.\n📞 Contact health worker if symptoms worsen or new concerns arise.', 'care_continuity', 'triggered', '2026-08-22 07:59:02', NULL, NULL, '2026-08-22 07:59:02', '2026-08-22 07:59:02'),
(27, NULL, 'Medium', '2 scheduled checkup(s) are overdue.', '⏰ Reschedule missed appointment as soon as possible.\n📞 Contact health worker if symptoms worsen or new concerns arise.', 'care_continuity', 'triggered', '2026-08-22 14:01:35', NULL, NULL, '2026-08-22 14:01:35', '2026-08-22 14:01:35'),
(28, NULL, 'Medium', '2 scheduled checkup(s) are overdue.', '⏰ Reschedule missed appointment as soon as possible.\n📞 Contact health worker if symptoms worsen or new concerns arise.', 'care_continuity', 'triggered', '2026-08-22 14:10:12', NULL, NULL, '2026-08-22 14:10:12', '2026-08-22 14:10:12'),
(29, NULL, 'Medium', '2 scheduled checkup(s) are overdue.', '⏰ Reschedule missed appointment as soon as possible.\n📞 Contact health worker if symptoms worsen or new concerns arise.', 'care_continuity', 'triggered', '2026-08-22 14:31:13', NULL, NULL, '2026-08-22 14:31:13', '2026-08-22 14:31:13'),
(30, NULL, 'Medium', '2 scheduled checkup(s) are overdue.; Elevated blood pressure: 120/80 mmHg.', '🩺 Weekly BP checks recommended. Emphasize low-sodium diet and adequate rest.', 'hypertension_management', 'triggered', '2026-08-22 15:23:09', NULL, NULL, '2026-08-22 15:23:09', '2026-08-22 15:23:09'),
(31, NULL, 'Medium', 'Elevated blood pressure: 120/80 mmHg.', '🩺 Weekly BP checks recommended. Emphasize low-sodium diet and adequate rest.', 'hypertension_management', 'triggered', '2026-08-22 15:23:11', NULL, NULL, '2026-08-22 15:23:11', '2026-08-22 15:23:11'),
(32, NULL, 'Medium', '2 scheduled checkup(s) are overdue.; Elevated blood pressure: 120/80 mmHg.', '🩺 Weekly BP checks recommended. Emphasize low-sodium diet and adequate rest.', 'hypertension_management', 'triggered', '2026-08-22 15:50:06', NULL, NULL, '2026-08-22 15:50:06', '2026-08-22 15:50:06'),
(33, NULL, 'Medium', 'Elevated blood pressure: 120/80 mmHg.', '🩺 Weekly BP checks recommended. Emphasize low-sodium diet and adequate rest.', 'hypertension_management', 'triggered', '2026-08-22 15:50:08', NULL, NULL, '2026-08-22 15:50:08', '2026-08-22 15:50:08'),
(34, NULL, 'Medium', '2 scheduled checkup(s) are overdue.; Elevated blood pressure: 120/80 mmHg.', '🩺 Weekly BP checks recommended. Emphasize low-sodium diet and adequate rest.', 'hypertension_management', 'triggered', '2026-08-22 15:56:28', NULL, NULL, '2026-08-22 15:56:28', '2026-08-22 15:56:28'),
(35, NULL, 'Medium', 'Elevated blood pressure: 120/80 mmHg.', '🩺 Weekly BP checks recommended. Emphasize low-sodium diet and adequate rest.', 'hypertension_management', 'triggered', '2026-08-22 15:56:29', NULL, NULL, '2026-08-22 15:56:29', '2026-08-22 15:56:29'),
(36, NULL, 'Medium', '2 scheduled checkup(s) are overdue.; Elevated blood pressure: 120/80 mmHg.', '🩺 Weekly BP checks recommended. Emphasize low-sodium diet and adequate rest.', 'hypertension_management', 'triggered', '2026-08-22 16:12:53', NULL, NULL, '2026-08-22 16:12:53', '2026-08-22 16:12:53'),
(37, NULL, 'Medium', 'Elevated blood pressure: 120/80 mmHg.', '🩺 Weekly BP checks recommended. Emphasize low-sodium diet and adequate rest.', 'hypertension_management', 'triggered', '2026-08-22 16:12:54', NULL, NULL, '2026-08-22 16:12:54', '2026-08-22 16:12:54');

-- --------------------------------------------------------

--
-- Table structure for table `puroks`
--

CREATE TABLE `puroks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `barangay` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `puroks`
--

INSERT INTO `puroks` (`id`, `name`, `barangay`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Test Purok', 'Test Barangay', 'Test purok for foreign key testing', '2026-04-23 17:44:22', '2026-04-23 17:44:22'),
(2, 'Purok 1', 'Barangay Burgos Padlan, San Carlos City, Pangasinan', 'Residential area for Barangay Burgos Padlan, San Carlos City, Pangasinan', '2026-04-24 14:32:49', '2026-04-24 14:32:49'),
(3, 'Purok 2', 'Barangay Burgos Padlan, San Carlos City, Pangasinan', 'Residential area for Barangay Burgos Padlan, San Carlos City, Pangasinan', '2026-04-24 14:32:49', '2026-04-24 14:32:49'),
(4, 'Purok 3', 'Barangay Burgos Padlan, San Carlos City, Pangasinan', 'Residential area for Barangay Burgos Padlan, San Carlos City, Pangasinan', '2026-04-24 14:32:49', '2026-04-24 14:32:49'),
(5, 'Purok 4', 'Barangay Burgos Padlan, San Carlos City, Pangasinan', 'Residential area for Barangay Burgos Padlan, San Carlos City, Pangasinan', '2026-04-24 14:32:49', '2026-04-24 14:32:49'),
(6, 'Purok 5', 'Barangay Burgos Padlan, San Carlos City, Pangasinan', 'Residential area for Barangay Burgos Padlan, San Carlos City, Pangasinan', '2026-04-24 14:32:49', '2026-04-24 14:32:49');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('8K7hOrQAEbEJ9l3EHxiRfN68mdMlW5SACwuCJ1Cs', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibEQ3YnoyNzNEdERHRGdQVVJpbldOTzJJODdiVWRyT0VWdEl5QzdQbiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzI6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hdXRoL2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9fQ==', 1788275576),
('btoyXpbquqeuzLHKu3AWeRFSZMvmcLxkDbMjWhvT', 5, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiaXdMVW5yc01VRTRPWGtsWlNXR3ZDVFd4Q2tneTNGWkt0Nml1aHd4NCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9taWR3aWZlL2xlYXJuaW5nLzI4IjtzOjU6InJvdXRlIjtzOjIxOiJtaWR3aWZlLmxlYXJuaW5nLnNob3ciO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo1O30=', 1788361698),
('SgAeKJOVuRNuZ1wNwyIGuIoZMX7qhD3jEoNknWIC', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWHlUZDN4NTdrVHJJRTQzTTlKaHRVYWtmMEg2TTRMbkZRdmlYOGRGRCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzI6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hdXRoL2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1788286527);

-- --------------------------------------------------------

--
-- Table structure for table `sms_logs`
--

CREATE TABLE `sms_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `phone_number` varchar(30) NOT NULL,
  `message` text NOT NULL,
  `type` enum('appointment_reminder','high_risk_alert','missed_checkup','custom','broadcast') NOT NULL DEFAULT 'custom',
  `status` enum('sent','failed','pending') NOT NULL DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `provider_sid` varchar(64) DEFAULT NULL COMMENT 'Twilio Message SID for tracking',
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sms_logs`
--

INSERT INTO `sms_logs` (`id`, `user_id`, `phone_number`, `message`, `type`, `status`, `error_message`, `provider_sid`, `sent_at`, `created_at`, `updated_at`) VALUES
(1, 14, '+639384548234', 'ang inyong check up ay malapit na\n\nang inyong check up ay malapit na', 'custom', 'failed', 'Twilio credentials not configured.', NULL, NULL, '2026-08-03 09:45:51', '2026-08-03 09:45:51'),
(2, 14, '+639384548234', 'Your check-up is coming up soon.\n\nang inyong check up ay nalalapit na', 'custom', 'failed', 'Twilio credentials not configured.', NULL, NULL, '2026-08-03 09:46:46', '2026-08-03 09:46:47'),
(3, 14, '+639384548234', 'Your check-up is coming up soon.\n\nang inyong check up ay nalalapit na', 'custom', 'sent', NULL, 'MOCK_gjLMTVqoKS', '2026-08-03 09:57:44', '2026-08-03 09:57:44', '2026-08-03 09:57:44'),
(4, 6, '+639123456785', '🚨 REPROCARE HEALTH ALERT: Hi Maria, our system has detected a health concern: 1 scheduled checkup(s) are overdue.. Please contact your health worker immediately or visit the nearest health facility.\n\n🚨 REPROCARE BABALA SA KALUSUGAN: Kumusta Maria, natukoy ng aming sistema ang isang alalahanin sa kalusugan: 1 scheduled checkup(s) are overdue.. Makipag-ugnayan kaagad sa iyong health worker o pumunta sa pinakamalapit na health facility.', 'high_risk_alert', 'sent', NULL, 'MOCK_9QfoC6pCg6', '2026-08-10 10:19:14', '2026-08-10 10:19:14', '2026-08-10 10:19:14'),
(5, 6, '+639123456785', '🚨 REPROCARE HEALTH ALERT: Hi Maria, our system has detected a health concern: 1 scheduled checkup(s) are overdue.. Please contact your health worker immediately or visit the nearest health facility.\n\n🚨 REPROCARE BABALA SA KALUSUGAN: Kumusta Maria, natukoy ng aming sistema ang isang alalahanin sa kalusugan: 1 scheduled checkup(s) are overdue.. Makipag-ugnayan kaagad sa iyong health worker o pumunta sa pinakamalapit na health facility.', 'high_risk_alert', 'sent', NULL, 'MOCK_nCII7KTPHf', '2026-08-10 17:14:28', '2026-08-10 17:14:28', '2026-08-10 17:14:28'),
(6, 6, '+639123456785', '🚨 REPROCARE HEALTH ALERT: Hi Maria, our system has detected a health concern: 1 scheduled checkup(s) are overdue.. Please contact your health worker immediately or visit the nearest health facility.\n\n🚨 REPROCARE BABALA SA KALUSUGAN: Kumusta Maria, natukoy ng aming sistema ang isang alalahanin sa kalusugan: 1 scheduled checkup(s) are overdue.. Makipag-ugnayan kaagad sa iyong health worker o pumunta sa pinakamalapit na health facility.', 'high_risk_alert', 'sent', NULL, 'MOCK_gwZPemJt7R', '2026-08-10 17:14:41', '2026-08-10 17:14:41', '2026-08-10 17:14:41'),
(7, 6, '+639123456785', '🚨 REPROCARE HEALTH ALERT: Hi Maria, our system has detected a health concern: 2 scheduled checkup(s) are overdue.. Please contact your health worker immediately or visit the nearest health facility.\n\n🚨 REPROCARE BABALA SA KALUSUGAN: Kumusta Maria, natukoy ng aming sistema ang isang alalahanin sa kalusugan: 2 scheduled checkup(s) are overdue.. Makipag-ugnayan kaagad sa iyong health worker o pumunta sa pinakamalapit na health facility.', 'high_risk_alert', 'sent', NULL, 'MOCK_ltVGCzHKiV', '2026-08-22 07:59:03', '2026-08-22 07:59:02', '2026-08-22 07:59:03'),
(8, 14, '+639384548234', 'hey your check up dated is up', 'custom', 'sent', NULL, '15780', '2026-08-22 13:50:08', '2026-08-22 13:50:07', '2026-08-22 13:50:08'),
(9, 6, '+639123456785', '🚨 REPROCARE HEALTH ALERT: Hi Maria, our system has detected a health concern: 2 scheduled checkup(s) are overdue.. Please contact your health worker immediately or visit the nearest health facility.\n\n🚨 REPROCARE BABALA SA KALUSUGAN: Kumusta Maria, natukoy ng aming sistema ang isang alalahanin sa kalusugan: 2 scheduled checkup(s) are overdue.. Makipag-ugnayan kaagad sa iyong health worker o pumunta sa pinakamalapit na health facility.', 'high_risk_alert', 'failed', 'Only 160 characters are allowed per single message. Please shorten your message.', NULL, NULL, '2026-08-22 14:01:36', '2026-08-22 14:01:37'),
(10, 6, '+639123456785', '🚨 REPROCARE HEALTH ALERT: Hi Maria, our system has detected a health concern: 2 scheduled checkup(s) are overdue.. Please contact your health worker immediately or visit the nearest health facility.\n\n🚨 REPROCARE BABALA SA KALUSUGAN: Kumusta Maria, natukoy ng aming sistema ang isang alalahanin sa kalusugan: 2 scheduled checkup(s) are overdue.. Makipag-ugnayan kaagad sa iyong health worker o pumunta sa pinakamalapit na health facility.', 'high_risk_alert', 'failed', 'Only 160 characters are allowed per single message. Please shorten your message.', NULL, NULL, '2026-08-22 14:10:13', '2026-08-22 14:10:14'),
(11, 6, '+639123456785', '🚨 REPROCARE HEALTH ALERT: Hi Maria, our system has detected a health concern: 2 scheduled checkup(s) are overdue.. Please contact your health worker immediately or visit the nearest health facility.\n\n🚨 REPROCARE BABALA SA KALUSUGAN: Kumusta Maria, natukoy ng aming sistema ang isang alalahanin sa kalusugan: 2 scheduled checkup(s) are overdue.. Makipag-ugnayan kaagad sa iyong health worker o pumunta sa pinakamalapit na health facility.', 'high_risk_alert', 'failed', 'Only 160 characters are allowed per single message. Please shorten your message.', NULL, NULL, '2026-08-22 14:31:13', '2026-08-22 14:31:14'),
(12, 6, '+639123456785', '🚨 REPROCARE HEALTH ALERT: Hi Maria, our system has detected a health concern: 2 scheduled checkup(s) are overdue. Elevated blood pressure: 120/80 mmHg.. Please contact your health worker immediately or visit the nearest health facility.\n\n🚨 REPROCARE BABALA SA KALUSUGAN: Kumusta Maria, natukoy ng aming sistema ang isang alalahanin sa kalusugan: 2 scheduled checkup(s) are overdue. Elevated blood pressure: 120/80 mmHg.. Makipag-ugnayan kaagad sa iyong health worker o pumunta sa pinakamalapit na health facility.', 'high_risk_alert', 'failed', 'Only 160 characters are allowed per single message. Please shorten your message.', NULL, NULL, '2026-08-22 15:23:10', '2026-08-22 15:23:10'),
(13, 11, '+639123456679', '🚨 REPROCARE HEALTH ALERT: Hi Madonna, our system has detected a health concern: Elevated blood pressure: 120/80 mmHg.. Please contact your health worker immediately or visit the nearest health facility.\n\n🚨 REPROCARE BABALA SA KALUSUGAN: Kumusta Madonna, natukoy ng aming sistema ang isang alalahanin sa kalusugan: Elevated blood pressure: 120/80 mmHg.. Makipag-ugnayan kaagad sa iyong health worker o pumunta sa pinakamalapit na health facility.', 'high_risk_alert', 'failed', 'Only 160 characters are allowed per single message. Please shorten your message.', NULL, NULL, '2026-08-22 15:23:11', '2026-08-22 15:23:12'),
(14, 6, '+639123456785', '🚨 REPROCARE HEALTH ALERT: Hi Maria, our system has detected a health concern: 2 scheduled checkup(s) are overdue. Elevated blood pressure: 120/80 mmHg.. Please contact your health worker immediately or visit the nearest health facility.\n\n🚨 REPROCARE BABALA SA KALUSUGAN: Kumusta Maria, natukoy ng aming sistema ang isang alalahanin sa kalusugan: 2 scheduled checkup(s) are overdue. Elevated blood pressure: 120/80 mmHg.. Makipag-ugnayan kaagad sa iyong health worker o pumunta sa pinakamalapit na health facility.', 'high_risk_alert', 'failed', 'Only 160 characters are allowed per single message. Please shorten your message.', NULL, NULL, '2026-08-22 15:50:06', '2026-08-22 15:50:07'),
(15, 11, '+639123456679', '🚨 REPROCARE HEALTH ALERT: Hi Madonna, our system has detected a health concern: Elevated blood pressure: 120/80 mmHg.. Please contact your health worker immediately or visit the nearest health facility.\n\n🚨 REPROCARE BABALA SA KALUSUGAN: Kumusta Madonna, natukoy ng aming sistema ang isang alalahanin sa kalusugan: Elevated blood pressure: 120/80 mmHg.. Makipag-ugnayan kaagad sa iyong health worker o pumunta sa pinakamalapit na health facility.', 'high_risk_alert', 'failed', 'Only 160 characters are allowed per single message. Please shorten your message.', NULL, NULL, '2026-08-22 15:50:08', '2026-08-22 15:50:09'),
(16, 6, '+639123456785', '🚨 REPROCARE HEALTH ALERT: Hi Maria, our system has detected a health concern: 2 scheduled checkup(s) are overdue. Elevated blood pressure: 120/80 mmHg.. Please contact your health worker immediately or visit the nearest health facility.\n\n🚨 REPROCARE BABALA SA KALUSUGAN: Kumusta Maria, natukoy ng aming sistema ang isang alalahanin sa kalusugan: 2 scheduled checkup(s) are overdue. Elevated blood pressure: 120/80 mmHg.. Makipag-ugnayan kaagad sa iyong health worker o pumunta sa pinakamalapit na health facility.', 'high_risk_alert', 'failed', 'Only 160 characters are allowed per single message. Please shorten your message.', NULL, NULL, '2026-08-22 15:56:28', '2026-08-22 15:56:29'),
(17, 11, '+639123456679', '🚨 REPROCARE HEALTH ALERT: Hi Madonna, our system has detected a health concern: Elevated blood pressure: 120/80 mmHg.. Please contact your health worker immediately or visit the nearest health facility.\n\n🚨 REPROCARE BABALA SA KALUSUGAN: Kumusta Madonna, natukoy ng aming sistema ang isang alalahanin sa kalusugan: Elevated blood pressure: 120/80 mmHg.. Makipag-ugnayan kaagad sa iyong health worker o pumunta sa pinakamalapit na health facility.', 'high_risk_alert', 'failed', 'Only 160 characters are allowed per single message. Please shorten your message.', NULL, NULL, '2026-08-22 15:56:30', '2026-08-22 15:56:30'),
(18, 6, '+639123456785', '🚨 REPROCARE HEALTH ALERT: Hi Maria, our system has detected a health concern: 2 scheduled checkup(s) are overdue. Elevated blood pressure: 120/80 mmHg.. Please contact your health worker immediately or visit the nearest health facility.\n\n🚨 REPROCARE BABALA SA KALUSUGAN: Kumusta Maria, natukoy ng aming sistema ang isang alalahanin sa kalusugan: 2 scheduled checkup(s) are overdue. Elevated blood pressure: 120/80 mmHg.. Makipag-ugnayan kaagad sa iyong health worker o pumunta sa pinakamalapit na health facility.', 'high_risk_alert', 'failed', 'Only 160 characters are allowed per single message. Please shorten your message.', NULL, NULL, '2026-08-22 16:12:53', '2026-08-22 16:12:54'),
(19, 11, '+639123456679', '🚨 REPROCARE HEALTH ALERT: Hi Madonna, our system has detected a health concern: Elevated blood pressure: 120/80 mmHg.. Please contact your health worker immediately or visit the nearest health facility.\n\n🚨 REPROCARE BABALA SA KALUSUGAN: Kumusta Madonna, natukoy ng aming sistema ang isang alalahanin sa kalusugan: Elevated blood pressure: 120/80 mmHg.. Makipag-ugnayan kaagad sa iyong health worker o pumunta sa pinakamalapit na health facility.', 'high_risk_alert', 'failed', 'Only 160 characters are allowed per single message. Please shorten your message.', NULL, NULL, '2026-08-22 16:12:55', '2026-08-22 16:12:55'),
(20, 14, '09384548234', 'your check up is tommorow please go to rhu1 morning', 'custom', 'sent', NULL, '178827362016307952340535', '2026-09-01 14:40:20', '2026-09-01 14:40:16', '2026-09-01 14:40:20'),
(21, 14, '09384548234', 'good morning maam youre check up is tommorow please go to the rhu1', 'custom', 'sent', NULL, 'GCByxa9s81lJoaLg7uukjR17882748053829', '2026-09-01 15:00:04', '2026-09-01 15:00:04', '2026-09-01 15:00:04'),
(22, 14, '09384548234', 'good morning maam youre check up is tommorow please go to the rhu1', 'custom', 'sent', NULL, 'XQdW8dIzGhKGab4M4CmxS517882748073448', '2026-09-01 15:00:06', '2026-09-01 15:00:05', '2026-09-01 15:00:06');

-- --------------------------------------------------------

--
-- Table structure for table `supply_requests`
--

CREATE TABLE `supply_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `requested_by_id` bigint(20) UNSIGNED NOT NULL,
  `approved_by_id` bigint(20) UNSIGNED DEFAULT NULL,
  `supply_category` enum('vitamins','vaccines','birthing_kits','medicines','equipment','ppe','other') NOT NULL DEFAULT 'other',
  `supply_name` varchar(255) NOT NULL,
  `quantity_requested` int(11) NOT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `urgency` enum('routine','urgent','emergency') NOT NULL DEFAULT 'routine',
  `reason` text DEFAULT NULL,
  `status` enum('draft','submitted','under_review','approved','declined','delivered') NOT NULL DEFAULT 'draft',
  `cho_notes` text DEFAULT NULL,
  `expected_delivery_date` date DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `assigned_to_id` bigint(20) UNSIGNED NOT NULL,
  `assigned_by_id` bigint(20) UNSIGNED NOT NULL,
  `task_type` enum('patient_visit','data_collection','follow_up','report_submission','other') NOT NULL DEFAULT 'other',
  `status` enum('pending','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending',
  `due_date` date DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_initial` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL,
  `role` enum('cho','rhu','midwife','bhw','bhw_president','user') DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `partner_name` varchar(255) DEFAULT NULL,
  `partner_contact` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `barangay` varchar(255) DEFAULT NULL,
  `purok_id` bigint(20) UNSIGNED DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `id_image_front` varchar(255) DEFAULT NULL,
  `id_image_back` varchar(255) DEFAULT NULL,
  `medical_history` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `license_number` varchar(255) DEFAULT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  `license_expiry` date DEFAULT NULL,
  `assigned_barangay` varchar(255) DEFAULT NULL,
  `rhu_assignment` varchar(255) DEFAULT NULL,
  `cho_office` varchar(255) DEFAULT NULL,
  `registered_by_rhu_id` bigint(20) UNSIGNED DEFAULT NULL,
  `registered_by_cho_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sms_opt_out` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'If true, patient has opted out of SMS notifications',
  `certification_number` varchar(255) DEFAULT NULL,
  `certification_date` date DEFAULT NULL,
  `term_start` date DEFAULT NULL,
  `term_end` date DEFAULT NULL,
  `status` enum('approved','pending','suspended') NOT NULL DEFAULT 'pending',
  `archived_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by_bhw_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by_midwife_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `middle_initial`, `last_name`, `role`, `email`, `partner_name`, `partner_contact`, `email_verified_at`, `password`, `remember_token`, `address`, `barangay`, `purok_id`, `date_of_birth`, `gender`, `contact_number`, `profile_image`, `id_image_front`, `id_image_back`, `medical_history`, `rejection_reason`, `license_number`, `specialization`, `license_expiry`, `assigned_barangay`, `rhu_assignment`, `cho_office`, `registered_by_rhu_id`, `registered_by_cho_id`, `sms_opt_out`, `certification_number`, `certification_date`, `term_start`, `term_end`, `status`, `archived_at`, `created_at`, `updated_at`, `deleted_at`, `created_by_bhw_id`, `created_by_midwife_id`) VALUES
(5, 'Admin', NULL, 'Midwife', 'midwife', 'midwife@reprocare.com', NULL, NULL, NULL, '$2y$12$dGPi4Qp5MeM9/EVgdWRO9u1ZQ/QHe8GcGR2srenUK5.hylRGp8tIO', NULL, 'Rural Health Unit', 'Poblacion', NULL, NULL, NULL, NULL, 'uploads/profile/69eb08aec6856_1777010862.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'approved', NULL, '2026-04-23 18:49:14', '2026-04-24 06:07:42', NULL, NULL, NULL),
(6, 'Maria', 'M', 'Santa', 'user', 'mariasanta@gmail.com', NULL, NULL, NULL, '$2y$12$3ie7s3QfR98/Tdw5saq6d.FMywQOAHXBRPwqQLlRr5JC77a0lIWkS', NULL, 'Barangay Burgos San Carlos City Pangasinan', 'Barangay Burgos Padlan, San Carlos City, Pangasinan', 4, '1995-03-21', NULL, '09123456785', 'uploads/profile/69ebb2eb859fb_1777054443.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'approved', NULL, '2026-04-23 20:44:25', '2026-04-25 07:17:15', NULL, NULL, NULL),
(7, 'bhw', NULL, 'pres', 'bhw_president', 'pres@gmail.com', NULL, NULL, NULL, '$2y$12$r7zalZJud4imoqhOLZOx0.JyMY1uoj2GmpqofyOMl7/qtJW/FVicu', NULL, ',mvc bnm', 'Burgos', NULL, '1998-02-18', 'male', '09123456789', 'uploads/profile/69eb108e2d3f4_1777012878.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'approved', NULL, '2026-04-24 05:58:59', '2026-08-10 12:31:56', NULL, NULL, NULL),
(11, 'Madonna', 'M', 'De Vera', 'user', 'madonna@gmail.com', NULL, NULL, NULL, '$2y$12$pdC5c8bBRSdwt0Kp5/hvjuzGYsFkbZlux4K0IMeZSmeui9Re5cAcS', NULL, NULL, 'Barangay Burgos Padlan, San Carlos City, Pangasinan', 2, '1997-11-21', NULL, '09123456679', 'uploads/profile/69ec5fd894d87_1777098712.jpg', 'uploads/ids/id_front_1777067345_69ebe55141352.jpeg', 'uploads/ids/id_back_1777067345_69ebe55148b27.jpeg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'approved', NULL, '2026-04-24 21:49:05', '2026-04-25 07:16:12', NULL, NULL, NULL),
(13, 'Ana', 'M', 'Malasan', 'bhw', 'ana@gmail.com', NULL, NULL, NULL, '$2y$12$wtfldQoDd8yDcBlzYwI9m.W9owMCY/bYsgC/SSpH69WtS0Nw7AHH.', NULL, NULL, 'Barangay Burgos Padlan, San Carlos City, Pangasinan', NULL, '1981-02-02', 'female', '09234567891', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'approved', NULL, '2026-04-25 10:34:46', '2026-04-25 10:34:46', NULL, NULL, NULL),
(14, 'madona', 'f', 'rhodes', 'user', 'madona@gmail.com', NULL, NULL, NULL, '$2y$12$6T9Z/Qd/z3/bPsr7MoCR2ezlpZlFjtc6Q0KYlfZrzW3wnXTCPvyG2', NULL, NULL, 'Barangay Burgos Padlan, San Carlos City, Pangasinan', 2, '2000-01-01', NULL, '09384548234', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'approved', NULL, '2026-08-03 09:40:15', '2026-08-03 09:42:43', NULL, NULL, NULL),
(15, 'City Health', 'O', 'Officer', 'cho', 'cho@reprocare.com', NULL, NULL, NULL, '$2y$12$i1eBO06Mv1lVC/k0zD2S.ewXPREkf5hmTOyRxZDnzDBexFcGHFrUa', NULL, 'City Health Office, San Carlos City', 'Poblacion', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'approved', NULL, '2026-08-10 12:31:56', '2026-08-10 12:31:56', NULL, NULL, NULL),
(16, 'RHU', '1', 'Admin', 'rhu', 'rhu@reprocare.com', NULL, NULL, NULL, '$2y$12$mj4iyXu4bNRJvsiq6r5UPOQFTh2vDv6xpMPdWVRscurvgwkpjwi2y', NULL, 'Rural Health Unit 1, San Carlos City', 'Poblacion', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'approved', NULL, '2026-08-10 12:31:56', '2026-08-10 12:31:56', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `walk_in_patients`
--

CREATE TABLE `walk_in_patients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `recorded_by_id` bigint(20) UNSIGNED DEFAULT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_initial` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `barangay` varchar(255) DEFAULT NULL,
  `purok_id` bigint(20) UNSIGNED DEFAULT NULL,
  `contact_number` varchar(255) DEFAULT NULL,
  `reason_for_visit` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `converted_to_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `converted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `walk_in_patients`
--

INSERT INTO `walk_in_patients` (`id`, `recorded_by_id`, `first_name`, `middle_initial`, `last_name`, `date_of_birth`, `address`, `barangay`, `purok_id`, `contact_number`, `reason_for_visit`, `notes`, `converted_to_user_id`, `converted_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, NULL, 'Milyn', 'T.', 'Garcia', '1980-08-09', NULL, 'Barangay Burgos', 2, '098765432112', 'Pregnant, add to monitor and check up', NULL, NULL, NULL, '2026-04-24 21:19:04', '2026-04-27 08:30:00', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_logs_user_id_index` (`user_id`),
  ADD KEY `activity_logs_action_index` (`action`),
  ADD KEY `activity_logs_model_type_model_id_index` (`model_type`,`model_id`),
  ADD KEY `activity_logs_created_at_index` (`created_at`),
  ADD KEY `activity_logs_user_role_index` (`user_role`);

--
-- Indexes for table `bhw_assignments`
--
ALTER TABLE `bhw_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bhw_assignments_purok_id_foreign` (`purok_id`),
  ADD KEY `bhw_assignments_bhw_id_purok_id_index` (`bhw_id`,`purok_id`),
  ADD KEY `bhw_assignments_bhw_id_is_active_index` (`bhw_id`,`is_active`),
  ADD KEY `bhw_assignments_assigned_by_id_index` (`assigned_by_id`);

--
-- Indexes for table `bhw_monthly_reports`
--
ALTER TABLE `bhw_monthly_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bhw_monthly_reports_bhw_id_report_month_report_year_index` (`bhw_id`,`report_month`,`report_year`),
  ADD KEY `bhw_monthly_reports_bhw_id_status_index` (`bhw_id`,`status`),
  ADD KEY `bhw_monthly_reports_report_year_report_month_index` (`report_year`,`report_month`),
  ADD KEY `idx_bhw_reports_bhw_id` (`bhw_id`),
  ADD KEY `idx_bhw_reports_month` (`report_month`),
  ADD KEY `bhw_monthly_reports_submitted_to_president_by_foreign` (`submitted_to_president_by`),
  ADD KEY `bhw_monthly_reports_approved_by_president_foreign` (`approved_by_president`),
  ADD KEY `bhw_monthly_reports_submitted_to_midwife_by_foreign` (`submitted_to_midwife_by`),
  ADD KEY `bhw_monthly_reports_approved_by_midwife_foreign` (`approved_by_midwife`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `checkups`
--
ALTER TABLE `checkups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_checkups_scheduled_date` (`scheduled_date`),
  ADD KEY `idx_checkups_status` (`status`),
  ADD KEY `checkups_woman_id_index` (`user_id`),
  ADD KEY `checkups_walk_in_patient_id_foreign` (`walk_in_patient_id`),
  ADD KEY `checkups_midwife_id_index` (`midwife_id`),
  ADD KEY `checkups_bhw_president_user_id_index` (`bhw_president_id`),
  ADD KEY `checkups_pregnancy_id_foreign` (`pregnancy_id`);

--
-- Indexes for table `checkup_referrals`
--
ALTER TABLE `checkup_referrals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `checkup_referrals_referred_by_bhw_id_foreign` (`referred_by_bhw_id`),
  ADD KEY `checkup_referrals_woman_id_foreign` (`user_id`),
  ADD KEY `checkup_referrals_assigned_midwife_id_foreign` (`assigned_midwife_id`),
  ADD KEY `checkup_referrals_walk_in_patient_id_foreign` (`walk_in_patient_id`),
  ADD KEY `checkup_referrals_converted_checkup_id_foreign` (`converted_checkup_id`);

--
-- Indexes for table `child_assessments`
--
ALTER TABLE `child_assessments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `child_assess_unique` (`child_care_target_client_id`,`age_group`),
  ADD KEY `child_assess_client_idx` (`child_care_target_client_id`);

--
-- Indexes for table `child_care_target_clients`
--
ALTER TABLE `child_care_target_clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `child_care_target_clients_child_id_unique` (`child_id`);

--
-- Indexes for table `child_checkups`
--
ALTER TABLE `child_checkups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `child_checkups_conducted_by_id_foreign` (`conducted_by_id`),
  ADD KEY `child_checkups_child_id_index` (`child_id`),
  ADD KEY `child_checkups_checkup_date_index` (`checkup_date`);

--
-- Indexes for table `child_feeding_milestones`
--
ALTER TABLE `child_feeding_milestones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `child_feeding_unique` (`child_care_target_client_id`);

--
-- Indexes for table `child_management_outcomes`
--
ALTER TABLE `child_management_outcomes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `child_outcome_unique` (`child_care_target_client_id`,`program_type`),
  ADD KEY `child_outcome_client_idx` (`child_care_target_client_id`);

--
-- Indexes for table `child_nutrition_tracking`
--
ALTER TABLE `child_nutrition_tracking`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `child_nutrition_unique` (`child_care_target_client_id`,`month_range`),
  ADD KEY `child_nutrition_client_idx` (`child_care_target_client_id`);

--
-- Indexes for table `child_records`
--
ALTER TABLE `child_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `child_records_mother_id_index` (`mother_id`),
  ADD KEY `child_records_pregnancy_id_index` (`pregnancy_id`),
  ADD KEY `child_records_purok_id_index` (`purok_id`);

--
-- Indexes for table `child_supplements`
--
ALTER TABLE `child_supplements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `child_supp_unique` (`child_care_target_client_id`,`supplement_type`,`month_number`),
  ADD KEY `child_supp_client_idx` (`child_care_target_client_id`);

--
-- Indexes for table `child_vaccinations`
--
ALTER TABLE `child_vaccinations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `child_vax_unique` (`child_care_target_client_id`,`vaccine_type`,`dose_number`),
  ADD KEY `child_vax_client_idx` (`child_care_target_client_id`);

--
-- Indexes for table `cycles`
--
ALTER TABLE `cycles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cycles_start_date` (`period_start_date`),
  ADD KEY `cycles_patient_id_patient_type_period_start_date_index` (`period_start_date`),
  ADD KEY `cycles_woman_id_foreign` (`user_id`);

--
-- Indexes for table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `emergency_contacts_user_id_index` (`user_id`);

--
-- Indexes for table `forum_comments`
--
ALTER TABLE `forum_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `forum_comments_post_id_foreign` (`post_id`),
  ADD KEY `forum_comments_user_id_index` (`user_id`);

--
-- Indexes for table `forum_likes`
--
ALTER TABLE `forum_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `forum_likes_post_id_user_id_unique` (`post_id`,`user_id`),
  ADD KEY `forum_likes_user_id_index` (`user_id`);

--
-- Indexes for table `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `forum_posts_user_id_index` (`user_id`);

--
-- Indexes for table `health_records`
--
ALTER TABLE `health_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_health_records_created_at` (`created_at`),
  ADD KEY `idx_health_records_risk_level` (`risk_level`),
  ADD KEY `health_records_woman_id_index` (`user_id`),
  ADD KEY `health_records_is_archived_index` (`is_archived`),
  ADD KEY `health_records_archived_at_index` (`archived_at`),
  ADD KEY `health_records_pregnancy_id_foreign` (`pregnancy_id`),
  ADD KEY `health_records_woman_id_pregnancy_id_index` (`user_id`,`pregnancy_id`),
  ADD KEY `health_records_walk_in_patient_id_foreign` (`walk_in_patient_id`),
  ADD KEY `health_records_recorded_by_user_id_index` (`recorded_by_id`),
  ADD KEY `health_records_bhw_president_user_id_index` (`bhw_president_id`);

--
-- Indexes for table `health_records_archived`
--
ALTER TABLE `health_records_archived`
  ADD PRIMARY KEY (`id`),
  ADD KEY `health_records_archived_pregnancy_id_foreign` (`pregnancy_id`),
  ADD KEY `health_records_archived_bhw_president_id_foreign` (`bhw_president_id`),
  ADD KEY `health_records_archived_user_id_index` (`user_id`),
  ADD KEY `health_records_archived_walk_in_patient_id_index` (`walk_in_patient_id`),
  ADD KEY `health_records_archived_recorded_by_id_index` (`recorded_by_id`),
  ADD KEY `health_records_archived_archived_at_index` (`archived_at`),
  ADD KEY `health_records_archived_is_archived_index` (`is_archived`);

--
-- Indexes for table `learning_materials`
--
ALTER TABLE `learning_materials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `maternal_assessments`
--
ALTER TABLE `maternal_assessments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mat_assess_unique` (`maternal_care_target_client_id`,`assessment_type`),
  ADD KEY `mat_assess_client_idx` (`maternal_care_target_client_id`);

--
-- Indexes for table `maternal_care_target_clients`
--
ALTER TABLE `maternal_care_target_clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `maternal_care_pregnancy_unique` (`pregnancy_id`),
  ADD KEY `maternal_care_target_clients_user_id_foreign` (`user_id`),
  ADD KEY `maternal_care_target_clients_recorded_by_id_foreign` (`recorded_by_id`);

--
-- Indexes for table `maternal_deaths`
--
ALTER TABLE `maternal_deaths`
  ADD PRIMARY KEY (`id`),
  ADD KEY `maternal_deaths_walk_in_patient_id_foreign` (`walk_in_patient_id`),
  ADD KEY `maternal_deaths_recorded_by_id_foreign` (`recorded_by_id`),
  ADD KEY `maternal_deaths_reviewed_by_id_foreign` (`reviewed_by_id`),
  ADD KEY `maternal_deaths_user_id_index` (`user_id`),
  ADD KEY `maternal_deaths_pregnancy_id_index` (`pregnancy_id`),
  ADD KEY `maternal_deaths_purok_id_index` (`purok_id`),
  ADD KEY `maternal_deaths_death_date_index` (`death_date`),
  ADD KEY `maternal_deaths_cause_category_index` (`cause_category`),
  ADD KEY `maternal_deaths_audit_status_index` (`audit_status`);

--
-- Indexes for table `maternal_morbidities`
--
ALTER TABLE `maternal_morbidities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `maternal_morbidities_walk_in_patient_id_foreign` (`walk_in_patient_id`),
  ADD KEY `maternal_morbidities_recorded_by_id_foreign` (`recorded_by_id`),
  ADD KEY `maternal_morbidities_reviewed_by_id_foreign` (`reviewed_by_id`),
  ADD KEY `maternal_morbidities_maternal_death_id_foreign` (`maternal_death_id`),
  ADD KEY `maternal_morbidities_user_id_index` (`user_id`),
  ADD KEY `maternal_morbidities_pregnancy_id_index` (`pregnancy_id`),
  ADD KEY `maternal_morbidities_event_date_index` (`event_date`),
  ADD KEY `maternal_morbidities_complication_type_index` (`complication_type`),
  ADD KEY `maternal_morbidities_review_status_index` (`review_status`),
  ADD KEY `maternal_morbidities_purok_id_index` (`purok_id`);

--
-- Indexes for table `maternal_postpartum_care`
--
ALTER TABLE `maternal_postpartum_care`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mat_postpartum_unique` (`maternal_care_target_client_id`,`iron_month`),
  ADD KEY `mat_postpartum_client_idx` (`maternal_care_target_client_id`);

--
-- Indexes for table `maternal_prenatal_visits`
--
ALTER TABLE `maternal_prenatal_visits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mat_pre_visit_unique` (`maternal_care_target_client_id`,`visit_number`),
  ADD KEY `mat_pre_visit_trim_idx` (`maternal_care_target_client_id`,`trimester`);

--
-- Indexes for table `maternal_screenings`
--
ALTER TABLE `maternal_screenings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mat_screen_unique` (`maternal_care_target_client_id`,`screening_type`),
  ADD KEY `mat_screen_client_idx` (`maternal_care_target_client_id`);

--
-- Indexes for table `maternal_supplements`
--
ALTER TABLE `maternal_supplements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mat_supp_unique` (`maternal_care_target_client_id`,`supplement_type`,`visit_number`),
  ADD KEY `mat_supp_client_idx` (`maternal_care_target_client_id`);

--
-- Indexes for table `maternal_vaccinations`
--
ALTER TABLE `maternal_vaccinations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mat_vax_unique` (`maternal_care_target_client_id`,`vaccine_type`,`dose_number`),
  ADD KEY `mat_vax_client_idx` (`maternal_care_target_client_id`);

--
-- Indexes for table `menstruation_dailies`
--
ALTER TABLE `menstruation_dailies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `menstruation_dailies_user_id_foreign` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `messages_reply_to_id_foreign` (`reply_to_id`),
  ADD KEY `messages_receiver_type_read_index` (`is_read`),
  ADD KEY `messages_sender_id_index` (`sender_id`),
  ADD KEY `messages_receiver_id_index` (`receiver_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_user_id_index` (`user_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `pregnancies`
--
ALTER TABLE `pregnancies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pregnancies_high_risk` (`is_high_risk`),
  ADD KEY `idx_pregnancies_ended_at` (`ended_at`),
  ADD KEY `pregnancies_woman_id_index` (`user_id`),
  ADD KEY `pregnancies_walk_in_patient_id_foreign` (`walk_in_patient_id`);

--
-- Indexes for table `preventive_interventions`
--
ALTER TABLE `preventive_interventions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `preventive_interventions_woman_id_index` (`user_id`);

--
-- Indexes for table `puroks`
--
ALTER TABLE `puroks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `puroks_barangay_index` (`barangay`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `sms_logs`
--
ALTER TABLE `sms_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sms_logs_user_id_status_index` (`user_id`,`status`),
  ADD KEY `sms_logs_type_created_at_index` (`type`,`created_at`);

--
-- Indexes for table `supply_requests`
--
ALTER TABLE `supply_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supply_requests_requested_by_id_index` (`requested_by_id`),
  ADD KEY `supply_requests_approved_by_id_index` (`approved_by_id`),
  ADD KEY `supply_requests_status_index` (`status`),
  ADD KEY `supply_requests_urgency_index` (`urgency`),
  ADD KEY `supply_requests_created_at_index` (`created_at`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tasks_assigned_to_id_index` (`assigned_to_id`),
  ADD KEY `tasks_assigned_by_id_index` (`assigned_by_id`),
  ADD KEY `tasks_status_index` (`status`),
  ADD KEY `tasks_due_date_index` (`due_date`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_role_index` (`role`),
  ADD KEY `users_status_index` (`status`),
  ADD KEY `users_barangay_index` (`barangay`),
  ADD KEY `users_created_by_bhw_id_foreign` (`created_by_bhw_id`),
  ADD KEY `users_created_by_midwife_id_foreign` (`created_by_midwife_id`),
  ADD KEY `users_registered_by_rhu_id_index` (`registered_by_rhu_id`),
  ADD KEY `users_registered_by_cho_id_index` (`registered_by_cho_id`);

--
-- Indexes for table `walk_in_patients`
--
ALTER TABLE `walk_in_patients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `walk_in_patients_recorded_by_bhw_id_foreign` (`recorded_by_id`),
  ADD KEY `walk_in_patients_purok_id_foreign` (`purok_id`),
  ADD KEY `walk_in_patients_converted_to_user_id_foreign` (`converted_to_user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `bhw_assignments`
--
ALTER TABLE `bhw_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `bhw_monthly_reports`
--
ALTER TABLE `bhw_monthly_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `checkups`
--
ALTER TABLE `checkups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `checkup_referrals`
--
ALTER TABLE `checkup_referrals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `child_assessments`
--
ALTER TABLE `child_assessments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `child_care_target_clients`
--
ALTER TABLE `child_care_target_clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `child_checkups`
--
ALTER TABLE `child_checkups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `child_feeding_milestones`
--
ALTER TABLE `child_feeding_milestones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `child_management_outcomes`
--
ALTER TABLE `child_management_outcomes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `child_nutrition_tracking`
--
ALTER TABLE `child_nutrition_tracking`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `child_records`
--
ALTER TABLE `child_records`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `child_supplements`
--
ALTER TABLE `child_supplements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `child_vaccinations`
--
ALTER TABLE `child_vaccinations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cycles`
--
ALTER TABLE `cycles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `forum_comments`
--
ALTER TABLE `forum_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `forum_likes`
--
ALTER TABLE `forum_likes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `forum_posts`
--
ALTER TABLE `forum_posts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `health_records`
--
ALTER TABLE `health_records`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `health_records_archived`
--
ALTER TABLE `health_records_archived`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `learning_materials`
--
ALTER TABLE `learning_materials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `maternal_assessments`
--
ALTER TABLE `maternal_assessments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maternal_care_target_clients`
--
ALTER TABLE `maternal_care_target_clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `maternal_deaths`
--
ALTER TABLE `maternal_deaths`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maternal_morbidities`
--
ALTER TABLE `maternal_morbidities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maternal_postpartum_care`
--
ALTER TABLE `maternal_postpartum_care`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maternal_prenatal_visits`
--
ALTER TABLE `maternal_prenatal_visits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maternal_screenings`
--
ALTER TABLE `maternal_screenings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maternal_supplements`
--
ALTER TABLE `maternal_supplements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maternal_vaccinations`
--
ALTER TABLE `maternal_vaccinations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menstruation_dailies`
--
ALTER TABLE `menstruation_dailies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=239;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `pregnancies`
--
ALTER TABLE `pregnancies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `preventive_interventions`
--
ALTER TABLE `preventive_interventions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `puroks`
--
ALTER TABLE `puroks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sms_logs`
--
ALTER TABLE `sms_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `supply_requests`
--
ALTER TABLE `supply_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `walk_in_patients`
--
ALTER TABLE `walk_in_patients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `bhw_assignments`
--
ALTER TABLE `bhw_assignments`
  ADD CONSTRAINT `bhw_assignments_assigned_by_id_foreign` FOREIGN KEY (`assigned_by_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bhw_assignments_bhw_id_foreign` FOREIGN KEY (`bhw_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bhw_assignments_purok_id_foreign` FOREIGN KEY (`purok_id`) REFERENCES `puroks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bhw_monthly_reports`
--
ALTER TABLE `bhw_monthly_reports`
  ADD CONSTRAINT `bhw_monthly_reports_approved_by_midwife_foreign` FOREIGN KEY (`approved_by_midwife`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bhw_monthly_reports_approved_by_president_foreign` FOREIGN KEY (`approved_by_president`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bhw_monthly_reports_bhw_id_foreign` FOREIGN KEY (`bhw_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bhw_monthly_reports_submitted_to_midwife_by_foreign` FOREIGN KEY (`submitted_to_midwife_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bhw_monthly_reports_submitted_to_president_by_foreign` FOREIGN KEY (`submitted_to_president_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `checkups`
--
ALTER TABLE `checkups`
  ADD CONSTRAINT `checkups_bhw_president_user_id_foreign` FOREIGN KEY (`bhw_president_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `checkups_midwife_id_foreign` FOREIGN KEY (`midwife_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `checkups_pregnancy_id_foreign` FOREIGN KEY (`pregnancy_id`) REFERENCES `pregnancies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `checkups_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `checkups_walk_in_patient_id_foreign` FOREIGN KEY (`walk_in_patient_id`) REFERENCES `walk_in_patients` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `checkup_referrals`
--
ALTER TABLE `checkup_referrals`
  ADD CONSTRAINT `checkup_referrals_assigned_midwife_id_foreign` FOREIGN KEY (`assigned_midwife_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `checkup_referrals_converted_checkup_id_foreign` FOREIGN KEY (`converted_checkup_id`) REFERENCES `checkups` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `checkup_referrals_referred_by_bhw_id_foreign` FOREIGN KEY (`referred_by_bhw_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `checkup_referrals_walk_in_patient_id_foreign` FOREIGN KEY (`walk_in_patient_id`) REFERENCES `walk_in_patients` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `checkup_referrals_woman_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `child_assessments`
--
ALTER TABLE `child_assessments`
  ADD CONSTRAINT `child_assessments_child_care_target_client_id_foreign` FOREIGN KEY (`child_care_target_client_id`) REFERENCES `child_care_target_clients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `child_care_target_clients`
--
ALTER TABLE `child_care_target_clients`
  ADD CONSTRAINT `child_care_target_clients_child_id_foreign` FOREIGN KEY (`child_id`) REFERENCES `child_records` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `child_checkups`
--
ALTER TABLE `child_checkups`
  ADD CONSTRAINT `child_checkups_child_id_foreign` FOREIGN KEY (`child_id`) REFERENCES `child_records` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `child_checkups_conducted_by_id_foreign` FOREIGN KEY (`conducted_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `child_feeding_milestones`
--
ALTER TABLE `child_feeding_milestones`
  ADD CONSTRAINT `child_feeding_milestones_child_care_target_client_id_foreign` FOREIGN KEY (`child_care_target_client_id`) REFERENCES `child_care_target_clients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `child_management_outcomes`
--
ALTER TABLE `child_management_outcomes`
  ADD CONSTRAINT `child_management_outcomes_child_care_target_client_id_foreign` FOREIGN KEY (`child_care_target_client_id`) REFERENCES `child_care_target_clients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `child_nutrition_tracking`
--
ALTER TABLE `child_nutrition_tracking`
  ADD CONSTRAINT `child_nutrition_tracking_child_care_target_client_id_foreign` FOREIGN KEY (`child_care_target_client_id`) REFERENCES `child_care_target_clients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `child_records`
--
ALTER TABLE `child_records`
  ADD CONSTRAINT `child_records_mother_id_foreign` FOREIGN KEY (`mother_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `child_records_pregnancy_id_foreign` FOREIGN KEY (`pregnancy_id`) REFERENCES `pregnancies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `child_records_purok_id_foreign` FOREIGN KEY (`purok_id`) REFERENCES `puroks` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `child_supplements`
--
ALTER TABLE `child_supplements`
  ADD CONSTRAINT `child_supplements_child_care_target_client_id_foreign` FOREIGN KEY (`child_care_target_client_id`) REFERENCES `child_care_target_clients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `child_vaccinations`
--
ALTER TABLE `child_vaccinations`
  ADD CONSTRAINT `child_vaccinations_child_care_target_client_id_foreign` FOREIGN KEY (`child_care_target_client_id`) REFERENCES `child_care_target_clients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cycles`
--
ALTER TABLE `cycles`
  ADD CONSTRAINT `cycles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  ADD CONSTRAINT `emergency_contacts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `forum_comments`
--
ALTER TABLE `forum_comments`
  ADD CONSTRAINT `forum_comments_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `forum_posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `forum_likes`
--
ALTER TABLE `forum_likes`
  ADD CONSTRAINT `forum_likes_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `forum_posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_likes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD CONSTRAINT `forum_posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `health_records`
--
ALTER TABLE `health_records`
  ADD CONSTRAINT `health_records_bhw_president_user_id_foreign` FOREIGN KEY (`bhw_president_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `health_records_pregnancy_id_foreign` FOREIGN KEY (`pregnancy_id`) REFERENCES `pregnancies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `health_records_recorded_by_user_id_foreign` FOREIGN KEY (`recorded_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `health_records_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `health_records_walk_in_patient_id_foreign` FOREIGN KEY (`walk_in_patient_id`) REFERENCES `walk_in_patients` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `health_records_archived`
--
ALTER TABLE `health_records_archived`
  ADD CONSTRAINT `health_records_archived_bhw_president_id_foreign` FOREIGN KEY (`bhw_president_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `health_records_archived_pregnancy_id_foreign` FOREIGN KEY (`pregnancy_id`) REFERENCES `pregnancies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `health_records_archived_recorded_by_id_foreign` FOREIGN KEY (`recorded_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `health_records_archived_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `health_records_archived_walk_in_patient_id_foreign` FOREIGN KEY (`walk_in_patient_id`) REFERENCES `walk_in_patients` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `maternal_assessments`
--
ALTER TABLE `maternal_assessments`
  ADD CONSTRAINT `maternal_assessments_maternal_care_target_client_id_foreign` FOREIGN KEY (`maternal_care_target_client_id`) REFERENCES `maternal_care_target_clients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `maternal_care_target_clients`
--
ALTER TABLE `maternal_care_target_clients`
  ADD CONSTRAINT `maternal_care_target_clients_pregnancy_id_foreign` FOREIGN KEY (`pregnancy_id`) REFERENCES `pregnancies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `maternal_care_target_clients_recorded_by_id_foreign` FOREIGN KEY (`recorded_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `maternal_care_target_clients_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `maternal_deaths`
--
ALTER TABLE `maternal_deaths`
  ADD CONSTRAINT `maternal_deaths_pregnancy_id_foreign` FOREIGN KEY (`pregnancy_id`) REFERENCES `pregnancies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `maternal_deaths_purok_id_foreign` FOREIGN KEY (`purok_id`) REFERENCES `puroks` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `maternal_deaths_recorded_by_id_foreign` FOREIGN KEY (`recorded_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `maternal_deaths_reviewed_by_id_foreign` FOREIGN KEY (`reviewed_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `maternal_deaths_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `maternal_deaths_walk_in_patient_id_foreign` FOREIGN KEY (`walk_in_patient_id`) REFERENCES `walk_in_patients` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `maternal_morbidities`
--
ALTER TABLE `maternal_morbidities`
  ADD CONSTRAINT `maternal_morbidities_maternal_death_id_foreign` FOREIGN KEY (`maternal_death_id`) REFERENCES `maternal_deaths` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `maternal_morbidities_pregnancy_id_foreign` FOREIGN KEY (`pregnancy_id`) REFERENCES `pregnancies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `maternal_morbidities_purok_id_foreign` FOREIGN KEY (`purok_id`) REFERENCES `puroks` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `maternal_morbidities_recorded_by_id_foreign` FOREIGN KEY (`recorded_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `maternal_morbidities_reviewed_by_id_foreign` FOREIGN KEY (`reviewed_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `maternal_morbidities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `maternal_morbidities_walk_in_patient_id_foreign` FOREIGN KEY (`walk_in_patient_id`) REFERENCES `walk_in_patients` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `maternal_postpartum_care`
--
ALTER TABLE `maternal_postpartum_care`
  ADD CONSTRAINT `maternal_postpartum_care_maternal_care_target_client_id_foreign` FOREIGN KEY (`maternal_care_target_client_id`) REFERENCES `maternal_care_target_clients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `maternal_prenatal_visits`
--
ALTER TABLE `maternal_prenatal_visits`
  ADD CONSTRAINT `maternal_prenatal_visits_maternal_care_target_client_id_foreign` FOREIGN KEY (`maternal_care_target_client_id`) REFERENCES `maternal_care_target_clients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `maternal_screenings`
--
ALTER TABLE `maternal_screenings`
  ADD CONSTRAINT `maternal_screenings_maternal_care_target_client_id_foreign` FOREIGN KEY (`maternal_care_target_client_id`) REFERENCES `maternal_care_target_clients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `maternal_supplements`
--
ALTER TABLE `maternal_supplements`
  ADD CONSTRAINT `maternal_supplements_maternal_care_target_client_id_foreign` FOREIGN KEY (`maternal_care_target_client_id`) REFERENCES `maternal_care_target_clients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `maternal_vaccinations`
--
ALTER TABLE `maternal_vaccinations`
  ADD CONSTRAINT `maternal_vaccinations_maternal_care_target_client_id_foreign` FOREIGN KEY (`maternal_care_target_client_id`) REFERENCES `maternal_care_target_clients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `menstruation_dailies`
--
ALTER TABLE `menstruation_dailies`
  ADD CONSTRAINT `menstruation_dailies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_receiver_id_foreign` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_reply_to_id_foreign` FOREIGN KEY (`reply_to_id`) REFERENCES `messages` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pregnancies`
--
ALTER TABLE `pregnancies`
  ADD CONSTRAINT `pregnancies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pregnancies_walk_in_patient_id_foreign` FOREIGN KEY (`walk_in_patient_id`) REFERENCES `walk_in_patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `preventive_interventions`
--
ALTER TABLE `preventive_interventions`
  ADD CONSTRAINT `preventive_interventions_woman_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sms_logs`
--
ALTER TABLE `sms_logs`
  ADD CONSTRAINT `sms_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `supply_requests`
--
ALTER TABLE `supply_requests`
  ADD CONSTRAINT `supply_requests_approved_by_id_foreign` FOREIGN KEY (`approved_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `supply_requests_requested_by_id_foreign` FOREIGN KEY (`requested_by_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_assigned_by_id_foreign` FOREIGN KEY (`assigned_by_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tasks_assigned_to_id_foreign` FOREIGN KEY (`assigned_to_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_created_by_bhw_id_foreign` FOREIGN KEY (`created_by_bhw_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_created_by_midwife_id_foreign` FOREIGN KEY (`created_by_midwife_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_registered_by_cho_id_foreign` FOREIGN KEY (`registered_by_cho_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_registered_by_rhu_id_foreign` FOREIGN KEY (`registered_by_rhu_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `walk_in_patients`
--
ALTER TABLE `walk_in_patients`
  ADD CONSTRAINT `walk_in_patients_converted_to_user_id_foreign` FOREIGN KEY (`converted_to_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `walk_in_patients_purok_id_foreign` FOREIGN KEY (`purok_id`) REFERENCES `puroks` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `walk_in_patients_recorded_by_bhw_id_foreign` FOREIGN KEY (`recorded_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
