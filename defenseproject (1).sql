-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 18, 2026 at 05:11 AM
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
-- Database: `defenseproject`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `app_id` int(11) NOT NULL,
  `intern_id` int(11) DEFAULT NULL,
  `position` varchar(100) DEFAULT 'Intern Position',
  `department` varchar(100) DEFAULT 'General Department',
  `supervisor_id` int(11) DEFAULT NULL,
  `application_date` date DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`app_id`, `intern_id`, `position`, `department`, `supervisor_id`, `application_date`, `status`, `user_id`) VALUES
(3, 25, 'Intern Position', 'General Department', NULL, NULL, 'approved', NULL),
(4, 30, 'Intern Position', 'General Department', NULL, NULL, 'pending', NULL),
(6, 32, 'Intern Position', 'General Department', NULL, NULL, 'pending', NULL),
(7, 33, 'Intern Position', 'General Department', NULL, NULL, 'pending', NULL),
(8, 37, 'Intern Position', 'General Department', NULL, NULL, 'pending', NULL),
(9, 38, 'Intern Position', 'General Department', NULL, NULL, 'pending', NULL),
(10, 39, 'Intern Position', 'General Department', NULL, NULL, 'approved', NULL),
(11, 49, 'Intern Position', 'General Department', NULL, NULL, 'approved', NULL),
(12, 13, 'Intern Position', 'General Department', NULL, NULL, 'approved', NULL),
(13, 14, 'Intern Position', 'General Department', NULL, NULL, NULL, NULL),
(14, 51, 'Intern Position', 'General Department', NULL, NULL, 'pending', NULL),
(15, 15, 'Intern Position', 'General Department', NULL, NULL, 'approved', NULL),
(16, 52, 'Intern Position', 'General Department', NULL, NULL, 'pending', NULL),
(17, 53, 'Intern Position', 'General Department', NULL, NULL, 'pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `application_rules`
--

CREATE TABLE `application_rules` (
  `id` int(11) NOT NULL,
  `rule_name` varchar(100) NOT NULL,
  `rule_description` text DEFAULT NULL,
  `rule_type` enum('eligibility','duration','evaluation','other') DEFAULT 'other',
  `rule_value` text DEFAULT NULL,
  `applies_to` enum('all','interns','supervisors','admins') DEFAULT 'all',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `application_rules`
--

INSERT INTO `application_rules` (`id`, `rule_name`, `rule_description`, `rule_type`, `rule_value`, `applies_to`, `is_active`, `created_at`) VALUES
(1, 'Minimum Age', NULL, 'eligibility', '{\"min_age\": 15, \"max_age\": 60}', 'all', 1, '2026-02-04 10:21:58'),
(2, 'Education Requirement', NULL, 'eligibility', '{\"min_education\": \"advance\", \"required_courses\": [\"Computer Science or ICT\"]}', 'all', 1, '2026-02-04 10:21:58');

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `intern_id` int(255) NOT NULL,
  `supervisor_id` int(11) NOT NULL,
  `assigned_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`id`, `intern_id`, `supervisor_id`, `assigned_date`, `status`, `notes`, `created_at`) VALUES
(1, 15, 0, '2026-02-24', 'active', NULL, '2026-02-24 14:39:17'),
(2, 13, 46, '2026-02-24', 'active', NULL, '2026-02-24 14:57:08'),
(3, 12, 47, '2026-02-24', 'active', NULL, '2026-02-24 15:14:10'),
(4, 6, 46, '2026-03-17', 'active', NULL, '2026-03-17 13:23:03'),
(5, 3, 45, '2026-03-18', 'active', NULL, '2026-03-18 01:30:31');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `intern_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `total_hours` decimal(4,2) DEFAULT 0.00,
  `status` enum('present','absent','late','half-day') DEFAULT 'present',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `intern_id`, `date`, `check_in`, `check_out`, `total_hours`, `status`, `notes`, `created_at`) VALUES
(1, NULL, NULL, '11:34:00', NULL, 0.00, 'present', NULL, '2026-01-12 10:34:00');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `table_name`, `record_id`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, NULL, 'insert', 'users', 40, NULL, 'Created new supervisor user: Jaxen (jaxen@gmail.com)', NULL, NULL, '2026-02-04 14:30:02');

-- --------------------------------------------------------

--
-- Table structure for table `backups`
--

CREATE TABLE `backups` (
  `id` int(11) NOT NULL,
  `backup_name` varchar(255) NOT NULL,
  `backup_path` varchar(500) DEFAULT NULL,
  `backup_size` int(11) DEFAULT NULL,
  `backup_type` enum('full','partial','log') DEFAULT 'full',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `evaluation`
--

CREATE TABLE `evaluation` (
  `id` int(50) NOT NULL,
  `score` int(50) NOT NULL,
  `comments` varchar(100) NOT NULL,
  `evaluation_date` varchar(12) NOT NULL,
  `intern_id` int(11) DEFAULT NULL,
  `supervisor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `intern`
--

CREATE TABLE `intern` (
  `intern_id` int(255) NOT NULL,
  `school` varchar(50) DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `internship_type` varchar(20) DEFAULT NULL,
  `file_path` varchar(40) DEFAULT NULL,
  `file_name` varchar(40) DEFAULT NULL,
  `Contact` int(15) DEFAULT NULL,
  `file_type` varchar(20) DEFAULT NULL,
  `Name` varchar(200) DEFAULT NULL,
  `supervisor_id` int(50) DEFAULT NULL,
  `Date_of_birth` varchar(12) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT NULL,
  `id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `intern`
--

INSERT INTO `intern` (`intern_id`, `school`, `department`, `start_date`, `end_date`, `internship_type`, `file_path`, `file_name`, `Contact`, `file_type`, `Name`, `supervisor_id`, `Date_of_birth`, `email`, `status`, `id`) VALUES
(3, 'istag', 'DEP', '2026-01-02', '2026-01-31', 'academic', '', NULL, 652545973, NULL, 'manga tracy', NULL, '2025-07-31', 'mangatracy81@gmail.com', NULL, NULL),
(12, 'Fonap', 'DEL', '2026-09-30', '2026-03-14', 'academic', '', NULL, 654576543, NULL, 'choh caily', NULL, '2026-02-01', 'caily@gmail.com', 'approved', NULL),
(13, 'Fonap', 'DEL', '2026-02-26', '2026-03-04', 'academic', '', NULL, 645343218, NULL, 'Edwin', NULL, '2026-02-01', 'Edwin@gmail.com', 'approved', NULL),
(15, 'ISPA', 'DTP', '2026-03-05', '2026-02-25', 'academic', '', NULL, 678546321, NULL, 'Junior', NULL, '2026-02-01', 'Junior@Gmail.com', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `intern_profiles`
--

CREATE TABLE `intern_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `school` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `level_of_study` enum('Undergraduate','Graduate','PhD','Other') DEFAULT NULL,
  `expected_graduation` date DEFAULT NULL,
  `gpa` varchar(10) DEFAULT NULL,
  `internship_start_date` date DEFAULT NULL,
  `internship_end_date` date DEFAULT NULL,
  `internship_type` enum('Academic','Professional','Summer','Part-time') DEFAULT NULL,
  `assigned_department` varchar(100) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `previous_experience` text DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `github_url` varchar(255) DEFAULT NULL,
  `portfolio_url` varchar(255) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `resume_path` varchar(255) DEFAULT NULL,
  `cover_letter_path` varchar(255) DEFAULT NULL,
  `emergency_contact_name` varchar(100) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `emergency_contact_relationship` varchar(50) DEFAULT NULL,
  `status` enum('Active','Inactive','Completed','Terminated') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `supervisor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `intern_reports`
--

CREATE TABLE `intern_reports` (
  `id` int(11) NOT NULL,
  `intern_id` int(11) NOT NULL,
  `supervisor_id` int(11) DEFAULT NULL,
  `report_title` varchar(200) NOT NULL,
  `report_description` text NOT NULL,
  `report_file` varchar(255) DEFAULT NULL,
  `report_type` enum('daily','weekly','monthly','final','other') DEFAULT 'weekly',
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','reviewed','approved','rejected') DEFAULT 'pending',
  `supervisor_feedback` text DEFAULT NULL,
  `feedback_date` timestamp NULL DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `week_number` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `intern_reports`
--

INSERT INTO `intern_reports` (`id`, `intern_id`, `supervisor_id`, `report_title`, `report_description`, `report_file`, `report_type`, `submission_date`, `status`, `supervisor_feedback`, `feedback_date`, `rating`, `title`, `content`, `file_path`, `week_number`) VALUES
(1, 39, NULL, '', '', NULL, 'weekly', '2026-03-16 13:21:45', 'pending', NULL, NULL, NULL, 'weekly', 'summary of chapter 2', 'uploads/reports/39_1773667305_2.docx', 2),
(2, 39, NULL, '', '', NULL, 'weekly', '2026-03-16 15:35:38', 'pending', NULL, NULL, NULL, 'week', 'report', 'uploads/reports/39_1773675338_3.docx', 3),
(3, 39, NULL, '', '', NULL, 'weekly', '2026-03-17 13:14:16', 'pending', NULL, NULL, NULL, 'week 4', 'check', 'uploads/reports/39_1773753256_4.docx', 4);

-- --------------------------------------------------------

--
-- Table structure for table `meetings`
--

CREATE TABLE `meetings` (
  `id` int(11) NOT NULL,
  `supervisor_id` int(11) NOT NULL,
  `intern_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `meeting_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `meeting_type` enum('in-person','virtual') DEFAULT 'virtual',
  `status` enum('scheduled','completed','cancelled') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meetings`
--

INSERT INTO `meetings` (`id`, `supervisor_id`, `intern_id`, `title`, `description`, `meeting_date`, `start_time`, `end_time`, `location`, `meeting_type`, `status`, `created_at`) VALUES
(1, 39, 39, 'Weekly check in', 'PHP', '2026-02-05', '09:00:00', '10:00:00', 'in my office', 'in-person', 'scheduled', '2026-02-04 09:36:03'),
(2, 47, 39, 'weekly', 'one chapter one', '2026-03-17', '00:00:00', '10:03:00', 'in my office', 'in-person', 'scheduled', '2026-03-16 13:24:09'),
(3, 47, 39, 'weekly meeting', 'about report', '2026-03-18', '09:00:00', '10:00:00', 'in my office', 'in-person', 'scheduled', '2026-03-17 13:15:28'),
(4, 47, 39, 'weekly', 'meeting', '2026-04-02', '09:02:00', '10:00:00', 'in my office', 'in-person', 'scheduled', '2026-03-17 16:11:53'),
(5, 47, 39, 'weekly', 'meeting', '2026-04-25', '01:08:00', '03:05:00', 'in my office', 'in-person', 'scheduled', '2026-03-17 16:13:20'),
(6, 47, 39, 'weekly', 'meeting', '2026-04-25', '04:11:00', '08:10:00', 'in my office', 'in-person', 'scheduled', '2026-03-17 16:14:17'),
(7, 47, 39, 'monthly', 'about report', '2026-06-19', '03:06:00', '04:42:00', 'in my office', 'in-person', 'scheduled', '2026-03-17 16:21:06'),
(8, 47, 39, 'monthly', 'about report', '2026-10-31', '03:06:00', '04:42:00', 'in my office', 'virtual', 'scheduled', '2026-03-17 16:24:12'),
(9, 47, 39, 'monthly', 'free', '2026-06-26', '02:05:00', '04:06:00', 'in my office', 'virtual', 'scheduled', '2026-03-17 16:26:49'),
(10, 47, 39, 'weekly', 'reports', '2026-05-30', '03:06:00', '16:06:00', 'in my office', 'virtual', 'scheduled', '2026-03-17 16:37:24');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `is_read`) VALUES
(2, 39, 45, 'hey', '2026-02-09 09:58:39', 0),
(3, 49, 45, 'ddear', '2026-02-09 10:00:16', 0);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `meeting_id` int(11) DEFAULT NULL,
  `read` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `meeting_id`, `read`, `created_at`) VALUES
(1, 39, 'meeting_scheduled', 'New Meeting Scheduled', 'Your supervisor scheduled a meeting with you!', 2, 1, '2026-03-16 13:24:09');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `intern_id` int(11) NOT NULL,
  `supervisor_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `status` enum('submitted','reviewed','approved','rejected') DEFAULT 'submitted',
  `feedback` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_comments`
--

CREATE TABLE `report_comments` (
  `id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `supervisor_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `report_comments`
--

INSERT INTO `report_comments` (`id`, `report_id`, `supervisor_id`, `comment`, `created_at`) VALUES
(1, 1, 47, 'that as a good start', '2026-03-16 14:22:27'),
(2, 1, 47, 'good', '2026-03-16 16:33:42'),
(3, 1, 47, 'good', '2026-03-16 16:33:58');

-- --------------------------------------------------------

--
-- Table structure for table `report_notifications`
--

CREATE TABLE `report_notifications` (
  `id` int(11) NOT NULL,
  `supervisor_id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supervisors`
--

CREATE TABLE `supervisors` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `department` varchar(50) DEFAULT NULL,
  `max_interns` int(11) DEFAULT 5,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supervisors`
--

INSERT INTO `supervisors` (`id`, `name`, `email`, `password`, `department`, `max_interns`, `created_at`) VALUES
(45, 'Dessap', 'dessap@gmail.com', '$2y$10$YsBqY6YI5.XEEPEx5t7dG.11G//y9AhKLXLtrtvBsWc..', NULL, 4, '2026-02-24 14:50:12'),
(46, 'Frida', 'frida@gmail.com', '$2y$10$566kp8K3xKevH87qusHmWOyFhCo0U/70oGLsaumNwXf.', NULL, 4, '2026-02-24 14:54:57'),
(47, 'phaline', 'phaline@gmail.com', '$2y$10$Ji80OEMXPqud4mZWcyPk6ehcnFYB2iwk2G/4t2Tsdpk.', NULL, 4, '2026-02-24 14:54:57');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','integer','boolean','json') DEFAULT 'string',
  `category` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `uploaded_file`
--

CREATE TABLE `uploaded_file` (
  `id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `file_data` longblob NOT NULL,
  `uploaded` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('intern','supervisor','admin') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('approved','pending','rejected') DEFAULT NULL,
  `supervisor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `Name`, `email`, `profile_picture`, `password`, `role`, `created_at`, `status`, `supervisor_id`) VALUES
(39, 'choh caily', 'caily@gmail.com', NULL, '$2y$10$YsBqY6YI5.XEEPEx5t7dG.11G//y9AhKLXLtrtvBsWcNVx9d5v.NO', 'intern', '2026-02-03 11:46:39', 'approved', NULL),
(45, 'Dessap', 'dessap@gmail.com', NULL, '$2y$10$Q0.eD8MYfgCLJIrui0572upMMTc8s2APm2.agwGx1U5SSbFlw5aLC', 'supervisor', '2026-02-05 11:21:20', NULL, NULL),
(46, 'Frida', 'frida@gmail.com', NULL, '$2y$10$566kp8K3xKevH87qusHmWOyFhCo0U/70oGLsaumNwXfFIDpLr2l6u', 'supervisor', '2026-02-05 11:21:20', NULL, NULL),
(47, 'Phaline', 'phaline@gmail.com', NULL, '$2y$10$sJS7NljXjsCm.w4tUE5v4.HJXuIYCW4H1KAeApEfx/v9a5ZvWek.6', 'supervisor', '2026-02-05 11:21:20', NULL, NULL),
(48, 'Tracy', 'tracy@gmail.com', NULL, '$2y$10$Ezy3mOnCh0lGBPpuFv5BrORtrE1FfY6701LZ0KCrg2jQkJBk/ojUG', 'admin', '2026-02-05 11:21:20', NULL, NULL),
(49, 'Edwin', 'Edwin@gmail.com', NULL, '$2y$10$.VmRawcBsXhd406qIa4xxO.HgjwRTlFnUtL/JLrnkxXLq7Zh1VPKO', 'intern', '2026-02-05 12:20:29', NULL, NULL),
(51, 'Junior', 'Junior@Gmail.com', NULL, '$2y$10$Ci0W6Vz.9ySq.JyRmhpJTOCLP9cEs/zNE9RgvVkhhwjH1C1yHmBju', 'intern', '2026-02-09 09:22:44', NULL, NULL),
(52, 'Martin', 'Martin@gmail.com', NULL, '$2y$10$1YHUyDq9d0v164FpwhnSH.kRXMTYi7FsH77JJCCkaYrlfGJomOZO.', 'intern', '2026-02-23 12:26:22', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`app_id`);

--
-- Indexes for table `application_rules`
--
ALTER TABLE `application_rules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_assignment` (`intern_id`,`supervisor_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attendance` (`intern_id`,`date`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `backups`
--
ALTER TABLE `backups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `evaluation`
--
ALTER TABLE `evaluation`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `intern`
--
ALTER TABLE `intern`
  ADD PRIMARY KEY (`intern_id`),
  ADD KEY `supervisor_id` (`supervisor_id`),
  ADD KEY `fk_intern_user` (`id`);

--
-- Indexes for table `intern_profiles`
--
ALTER TABLE `intern_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `intern_reports`
--
ALTER TABLE `intern_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `intern_id` (`intern_id`),
  ADD KEY `supervisor_id` (`supervisor_id`),
  ADD KEY `idx_intern` (`intern_id`),
  ADD KEY `idx_supervisor` (`supervisor_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `meetings`
--
ALTER TABLE `meetings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_read` (`read`),
  ADD KEY `idx_type` (`type`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `intern_id` (`intern_id`),
  ADD KEY `supervisor_id` (`supervisor_id`);

--
-- Indexes for table `report_comments`
--
ALTER TABLE `report_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_report` (`report_id`);

--
-- Indexes for table `report_notifications`
--
ALTER TABLE `report_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supervisor_id` (`supervisor_id`),
  ADD KEY `report_id` (`report_id`);

--
-- Indexes for table `supervisors`
--
ALTER TABLE `supervisors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `uploaded_file`
--
ALTER TABLE `uploaded_file`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `app_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `application_rules`
--
ALTER TABLE `application_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `backups`
--
ALTER TABLE `backups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `evaluation`
--
ALTER TABLE `evaluation`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `intern`
--
ALTER TABLE `intern`
  MODIFY `intern_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `intern_profiles`
--
ALTER TABLE `intern_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `intern_reports`
--
ALTER TABLE `intern_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `meetings`
--
ALTER TABLE `meetings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `report_comments`
--
ALTER TABLE `report_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `report_notifications`
--
ALTER TABLE `report_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supervisors`
--
ALTER TABLE `supervisors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `uploaded_file`
--
ALTER TABLE `uploaded_file`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`intern_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `backups`
--
ALTER TABLE `backups`
  ADD CONSTRAINT `backups_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `intern`
--
ALTER TABLE `intern`
  ADD CONSTRAINT `fk_intern_user` FOREIGN KEY (`id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_supervisor` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `intern_profiles`
--
ALTER TABLE `intern_profiles`
  ADD CONSTRAINT `intern_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `intern_reports`
--
ALTER TABLE `intern_reports`
  ADD CONSTRAINT `intern_reports_ibfk_1` FOREIGN KEY (`intern_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `intern_reports_ibfk_2` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`intern_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `report_notifications`
--
ALTER TABLE `report_notifications`
  ADD CONSTRAINT `report_notifications_ibfk_1` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `report_notifications_ibfk_2` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
