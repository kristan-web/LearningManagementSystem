-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 28, 2026 at 10:53 PM
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
-- Database: `enrollment_management_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE DATABASE IF NOT EXISTS `enrollment_management_system` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `enrollment_management_system`;

CREATE TABLE `announcements` (
  `announcement_id` int(10) UNSIGNED NOT NULL,
  `posted_by` int(10) UNSIGNED NOT NULL,
  `section_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `posted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `assignment_id` int(10) UNSIGNED NOT NULL,
  `schedule_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `instructions` text DEFAULT NULL,
  `due_date` datetime NOT NULL,
  `max_score` decimal(6,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`assignment_id`, `schedule_id`, `title`, `instructions`, `due_date`, `max_score`, `created_at`) VALUES
(2, 2, 'Essay: My Summer Vacation', 'Placeholder instructions for Essay: My Summer Vacation.', '2026-09-25 20:30:55', 100.00, '2026-09-28 20:30:55'),
(3, 2, 'Problem Set 3: Linear Equations', 'Placeholder instructions for Problem Set 3: Linear Equations.', '2026-09-30 20:30:55', 100.00, '2026-09-28 20:30:55'),
(4, 2, 'Lab Report: Photosynthesis', 'Placeholder instructions for Lab Report: Photosynthesis.', '2026-10-03 20:30:55', 100.00, '2026-09-28 20:30:55'),
(5, 2, 'Research Outline Draft', 'Placeholder instructions for Research Outline Draft.', '2026-10-07 20:30:55', 100.00, '2026-09-28 20:30:55'),
(6, 2, 'Group Project Proposal', 'Placeholder instructions for Group Project Proposal.', '2026-10-12 20:30:55', 100.00, '2026-09-28 20:30:55');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_records`
--

CREATE TABLE `attendance_records` (
  `attendance_id` int(10) UNSIGNED NOT NULL,
  `schedule_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('Present','Late','Absent','Excused') NOT NULL,
  `logged_by` int(10) UNSIGNED NOT NULL,
  `logged_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendance_records`
--

INSERT INTO `attendance_records` (`attendance_id`, `schedule_id`, `student_id`, `attendance_date`, `status`, `logged_by`, `logged_at`) VALUES
(2, 1, 1, '2026-09-01', 'Present', 3, '2026-09-21 11:57:16'),
(4, 1, 1, '2026-10-01', 'Late', 3, '2026-09-21 11:57:16'),
(5, 1, 1, '2026-11-01', 'Excused', 3, '2026-09-21 11:57:16'),
(6, 1, 1, '2026-09-02', 'Present', 3, '2026-09-21 11:57:16');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(100) NOT NULL,
  `entity_id` int(10) UNSIGNED NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_sections`
--

CREATE TABLE `class_sections` (
  `section_id` int(10) UNSIGNED NOT NULL,
  `strand_id` int(10) UNSIGNED NOT NULL,
  `adviser_id` int(10) UNSIGNED DEFAULT NULL,
  `grade_level` enum('11','12') NOT NULL,
  `section_name` varchar(50) NOT NULL,
  `school_year` varchar(9) NOT NULL,
  `max_slots` int(11) NOT NULL,
  `status` enum('Open','Closed','Cancelled') NOT NULL DEFAULT 'Open',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `class_sections`
--

INSERT INTO `class_sections` (`section_id`, `strand_id`, `adviser_id`, `grade_level`, `section_name`, `school_year`, `max_slots`, `status`, `created_at`) VALUES
(1, 1, 1, '11', 'STEM 11-A', '2026-2027', 40, 'Open', '2026-07-12 17:38:05'),
(2, 1, 1, '11', 'STEM 11-B', '2026-2027', 40, 'Open', '2026-07-12 17:38:05'),
(3, 1, 1, '12', 'STEM 12-A', '2026-2027', 40, 'Open', '2026-07-12 17:38:05'),
(4, 1, 1, '12', 'STEM 12-B', '2026-2027', 38, 'Closed', '2026-07-12 17:38:05'),
(5, 2, 1, '11', 'ABM 11-A', '2026-2027', 42, 'Open', '2026-07-12 17:38:05'),
(6, 2, 1, '11', 'ABM 11-B', '2026-2027', 42, 'Open', '2026-07-12 17:38:05'),
(7, 2, 1, '12', 'ABM 12-A', '2026-2027', 40, 'Open', '2026-07-12 17:38:05'),
(8, 2, 1, '12', 'ABM 12-B', '2026-2027', 40, 'Closed', '2026-07-12 17:38:05'),
(9, 3, 1, '11', 'HUMSS 11-A', '2026-2027', 45, 'Open', '2026-07-12 17:38:05'),
(10, 3, 1, '11', 'HUMSS 11-B', '2026-2027', 45, 'Open', '2026-07-12 17:38:05'),
(11, 3, 1, '12', 'HUMSS 12-A', '2026-2027', 42, 'Open', '2026-07-12 17:38:05'),
(12, 3, 1, '12', 'HUMSS 12-B', '2026-2027', 42, 'Closed', '2026-07-12 17:38:05'),
(13, 5, 1, '11', 'ICT 11-A', '2026-2027', 35, 'Open', '2026-07-12 17:38:05'),
(14, 5, 1, '11', 'ICT 11-B', '2026-2027', 35, 'Open', '2026-07-12 17:38:05'),
(15, 5, 1, '12', 'ICT 12-A', '2026-2027', 35, 'Open', '2026-07-12 17:38:05'),
(16, 5, 1, '12', 'ICT 12-B', '2026-2027', 30, 'Closed', '2026-07-12 17:38:05'),
(17, 6, 1, '11', 'HE 11-A', '2026-2027', 38, 'Open', '2026-07-12 17:38:05'),
(18, 6, 1, '11', 'HE 11-B', '2026-2027', 38, 'Open', '2026-07-12 17:38:05'),
(19, 6, 1, '12', 'HE 12-A', '2026-2027', 36, 'Open', '2026-07-12 17:38:05'),
(20, 6, 1, '12', 'HE 12-B', '2026-2027', 36, 'Closed', '2026-07-12 17:38:05'),
(21, 1, NULL, '11', 'Calendar Demo', '2025-2026', 40, 'Open', '2026-09-28 20:30:55');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `enrollment_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `section_id` int(10) UNSIGNED NOT NULL,
  `school_year` varchar(9) NOT NULL,
  `school_year_id` int(10) UNSIGNED NOT NULL,
  `semester` enum('1st Semester','2nd Semester') NOT NULL,
  `date_enrolled` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('Enrolled','Dropped','Pending') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`enrollment_id`, `student_id`, `section_id`, `school_year`, `school_year_id`, `semester`, `date_enrolled`, `status`) VALUES
(3, 1, 1, '2026-2027', 1, '1st Semester', '2026-09-21 11:54:19', 'Enrolled'),
(4, 1, 1, '2026-2027', 1, '1st Semester', '2026-09-21 12:55:14', 'Enrolled'),
(5, 2, 3, '2026-2027', 1, '1st Semester', '2026-09-21 12:55:14', 'Enrolled'),
(6, 3, 2, '2026-2027', 1, '1st Semester', '2026-09-21 12:55:14', 'Pending'),
(7, 9, 7, '2026-2027', 1, '1st Semester', '2026-09-21 12:55:14', 'Enrolled'),
(8, 10, 1, '2023-2024', 2, '1st Semester', '2023-06-15 08:00:00', 'Enrolled'),
(9, 10, 1, '2023-2024', 2, '2nd Semester', '2023-11-15 08:00:00', 'Enrolled'),
(10, 11, 5, '2023-2024', 2, '1st Semester', '2023-06-16 09:00:00', 'Enrolled'),
(11, 10, 3, '2024-2025', 3, '1st Semester', '2024-06-10 08:00:00', 'Enrolled'),
(12, 12, 1, '2024-2025', 3, '1st Semester', '2024-06-11 09:30:00', 'Enrolled'),
(13, 13, 11, '2024-2025', 3, '1st Semester', '2024-06-12 10:15:00', 'Enrolled'),
(14, 13, 11, '2024-2025', 3, '2nd Semester', '2024-11-10 08:00:00', 'Enrolled'),
(15, 12, 3, '2025-2026', 4, '1st Semester', '2025-06-05 08:30:00', 'Enrolled'),
(16, 14, 13, '2025-2026', 4, '1st Semester', '2025-06-06 09:00:00', 'Enrolled'),
(17, 15, 3, '2025-2026', 4, '1st Semester', '2025-06-07 11:00:00', 'Enrolled'),
(18, 15, 3, '2025-2026', 4, '2nd Semester', '2025-11-12 09:00:00', 'Enrolled'),
(19, 16, 5, '2026-2027', 1, '1st Semester', '2026-06-15 08:00:00', 'Enrolled'),
(20, 17, 11, '2026-2027', 1, '1st Semester', '2026-06-16 08:30:00', 'Enrolled'),
(21, 18, 13, '2027-2028', 5, '1st Semester', '2027-06-10 08:00:00', 'Pending'),
(22, 19, 1, '2027-2028', 5, '1st Semester', '2027-06-11 08:30:00', 'Pending'),
(23, 21, 5, '2026-2027', 1, '1st Semester', '2026-09-29 04:11:30', 'Enrolled'),
(24, 22, 5, '2026-2027', 1, '1st Semester', '2026-09-29 04:24:26', 'Enrolled'),
(25, 21, 21, '2025-2026', 4, '1st Semester', '2026-09-29 04:30:55', 'Enrolled'),
(26, 22, 21, '2026-2027', 1, '1st Semester', '2026-09-29 04:30:55', 'Enrolled');

-- --------------------------------------------------------

--
-- Table structure for table `final_grades`
--

CREATE TABLE `final_grades` (
  `final_grade_id` int(10) UNSIGNED NOT NULL,
  `enrollment_id` int(10) UNSIGNED NOT NULL,
  `subject_id` int(10) UNSIGNED NOT NULL,
  `final_rating` decimal(5,2) NOT NULL,
  `remarks` enum('Passed','Failed','Incomplete') DEFAULT NULL,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `computed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grade_components`
--

CREATE TABLE `grade_components` (
  `component_id` int(10) UNSIGNED NOT NULL,
  `enrollment_id` int(10) UNSIGNED NOT NULL,
  `subject_id` int(10) UNSIGNED NOT NULL,
  `component_type` enum('written_work','performance_task','exam') NOT NULL,
  `source_type` enum('submission','quiz_attempt','manual') DEFAULT NULL,
  `source_id` int(10) UNSIGNED DEFAULT NULL,
  `raw_score` decimal(6,2) NOT NULL,
  `max_score` decimal(6,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grading_templates`
--

CREATE TABLE `grading_templates` (
  `template_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `written_work_weight` decimal(4,2) NOT NULL,
  `performance_task_weight` decimal(4,2) NOT NULL,
  `exam_weight` decimal(4,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guardians`
--

CREATE TABLE `guardians` (
  `guardian_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `relationship` varchar(50) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guidance_records`
--

CREATE TABLE `guidance_records` (
  `record_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `logged_by` int(10) UNSIGNED NOT NULL,
  `category` varchar(50) NOT NULL,
  `notes` text NOT NULL,
  `is_restricted` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `learning_materials`
--

CREATE TABLE `learning_materials` (
  `material_id` int(10) UNSIGNED NOT NULL,
  `schedule_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_url` varchar(500) NOT NULL,
  `status` enum('Draft','Published','Archived') NOT NULL DEFAULT 'Draft',
  `uploaded_by` int(10) UNSIGNED NOT NULL,
  `uploaded_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(10) UNSIGNED NOT NULL,
  `sender_id` int(10) UNSIGNED NOT NULL,
  `receiver_id` int(10) UNSIGNED NOT NULL,
  `body` text NOT NULL,
  `sent_at` datetime NOT NULL DEFAULT current_timestamp(),
  `read_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, '2026_09_20_000001_create_users_table', 1),
(2, '2026_09_20_000002_create_tracks_table', 1),
(3, '2026_09_20_000003_create_strands_table', 1),
(4, '2026_09_20_000004_create_guardians_table', 1),
(5, '2026_09_20_000005_create_teachers_table', 1),
(6, '2026_09_20_000006_create_students_table', 1),
(7, '2026_09_20_000007_create_school_years_table', 1),
(8, '2026_09_20_000008_create_semesters_table', 1),
(9, '2026_09_20_000009_create_rooms_table', 1),
(10, '2026_09_20_000010_create_class_sections_table', 1),
(11, '2026_09_20_000011_create_subjects_table', 1),
(12, '2026_09_20_000012_create_schedules_table', 1),
(13, '2026_09_20_000013_create_enrollments_table', 1),
(14, '2026_09_20_000014_create_assignments_table', 1),
(15, '2026_09_20_000015_create_quizzes_table', 1),
(16, '2026_09_20_000016_create_quiz_questions_table', 1),
(17, '2026_09_20_000017_create_quiz_attempts_table', 1),
(18, '2026_09_20_000018_create_submissions_table', 1),
(19, '2026_09_20_000019_create_attendance_records_table', 1),
(20, '2026_09_20_000020_create_grading_templates_table', 1),
(21, '2026_09_20_000021_create_final_grades_table', 1),
(22, '2026_09_20_000022_create_grade_components_table', 1),
(23, '2026_09_20_000023_create_subject_grading_templates_table', 1),
(24, '2026_09_20_000024_create_learning_materials_table', 1),
(25, '2026_09_20_000025_create_announcements_table', 1),
(26, '2026_09_20_000026_create_audit_logs_table', 1),
(27, '2026_09_20_000027_create_guidance_records_table', 1),
(28, '2026_09_20_000028_create_messages_table', 1),
(29, '2026_09_20_000029_create_notifications_table', 1),
(30, '2026_09_20_000030_create_schedule_events_table', 1),
(31, '2026_09_20_000031_create_student_guardians_table', 1),
(32, '2026_09_22_000032_change_teachers_specialization_to_enum', 2),
(33, '2026_09_22_064143_change_specialization_to_string_on_teachers_table', 3),
(34, '2026_09_22_064824_add_updated_at_to_teachers_table', 4),
(35, '2026_09_23_100000_add_teacher_id_to_subjects_table', 5),
(36, '2026_09_23_145926_create_sessions_table', 5),
(37, '2026_09_23_182114_add_must_change_password_to_users_table', 5),
(38, '2026_09_24_000001_add_due_date_to_quizzes_table', 5);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` varchar(500) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `quiz_id` int(10) UNSIGNED NOT NULL,
  `schedule_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `time_limit_minutes` int(11) DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`quiz_id`, `schedule_id`, `title`, `time_limit_minutes`, `due_date`, `created_at`) VALUES
(1, 2, 'Quiz 1: Vocabulary Check', 20, '2026-09-29 20:30:55', '2026-09-28 20:30:55'),
(2, 2, 'Quiz 2: Chapter 3 Review', 20, '2026-10-04 20:30:55', '2026-09-28 20:30:55'),
(3, 2, 'Quiz 3: Formulas and Units', 20, '2026-10-09 20:30:55', '2026-09-28 20:30:55'),
(4, 2, 'Quiz 4: Midterm Practice', 20, '2026-10-14 20:30:55', '2026-09-28 20:30:55');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `attempt_id` int(10) UNSIGNED NOT NULL,
  `quiz_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `score` decimal(6,2) DEFAULT NULL,
  `started_at` datetime NOT NULL DEFAULT current_timestamp(),
  `submitted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `question_id` int(10) UNSIGNED NOT NULL,
  `quiz_id` int(10) UNSIGNED NOT NULL,
  `question_text` text NOT NULL,
  `question_type` enum('multiple_choice','true_false','short_answer') NOT NULL,
  `options` longtext DEFAULT NULL,
  `correct_answer` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int(10) UNSIGNED NOT NULL,
  `room_name` varchar(50) NOT NULL,
  `building` varchar(50) NOT NULL,
  `capacity` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `room_name`, `building`, `capacity`, `created_at`) VALUES
(1, 'SHS-101', 'Main Building', 40, '2026-07-14 03:07:53'),
(2, 'SHS-102', 'Main Building', 40, '2026-07-14 03:07:53'),
(3, 'SHS-103', 'Main Building', 45, '2026-07-14 03:07:53'),
(4, 'SHS-104', 'Main Building', 45, '2026-07-14 03:07:53'),
(5, 'SHS-105', 'Main Building', 40, '2026-07-14 03:07:53'),
(6, 'SHS-106', 'Main Building', 35, '2026-07-14 03:07:53'),
(7, 'SHS-201', 'Main Building', 40, '2026-07-14 03:07:53'),
(8, 'SHS-202', 'Main Building', 40, '2026-07-14 03:07:53'),
(9, 'SHS-203', 'Main Building', 45, '2026-07-14 03:07:53'),
(10, 'SHS-204', 'Main Building', 40, '2026-07-14 03:07:53'),
(11, 'SHS-301', 'Science Building', 35, '2026-07-14 03:07:53'),
(12, 'SHS-302', 'Science Building', 35, '2026-07-14 03:07:53'),
(13, 'SHS-303', 'Science Building', 30, '2026-07-14 03:07:53'),
(14, 'SHS-401', 'TVL Building', 30, '2026-07-14 03:07:53'),
(15, 'SHS-402', 'TVL Building', 30, '2026-07-14 03:07:53'),
(16, 'SHS-403', 'TVL Building', 25, '2026-07-14 03:07:53'),
(17, 'SHS-404', 'TVL Building', 25, '2026-07-14 03:07:53'),
(18, 'SHS-501', 'Annex Building', 40, '2026-07-14 03:07:53'),
(19, 'SHS-502', 'Annex Building', 40, '2026-07-14 03:07:53'),
(20, 'SHS-503', 'Annex Building', 35, '2026-07-14 03:07:53'),
(21, 'Room 1', 'Main', 40, '2026-09-28 20:30:55');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `schedule_id` int(10) UNSIGNED NOT NULL,
  `section_id` int(10) UNSIGNED NOT NULL,
  `subject_id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED DEFAULT NULL,
  `room_id` int(10) UNSIGNED NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`schedule_id`, `section_id`, `subject_id`, `teacher_id`, `room_id`, `day_of_week`, `start_time`, `end_time`, `created_at`) VALUES
(1, 14, 38, 1, 1, 'Monday', '11:56:22', '11:56:25', '2026-09-21 11:56:58'),
(2, 21, 62, 6, 21, 'Monday', '08:00:00', '09:00:00', '2026-09-28 20:30:55');

-- --------------------------------------------------------

--
-- Table structure for table `schedule_events`
--

CREATE TABLE `schedule_events` (
  `event_id` int(10) UNSIGNED NOT NULL,
  `created_by_role` enum('Student','Teacher') NOT NULL,
  `created_by_id` int(10) UNSIGNED NOT NULL,
  `section_id` int(10) UNSIGNED DEFAULT NULL,
  `subject_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `event_type` enum('Personal','Quiz','Review','Announcement') NOT NULL DEFAULT 'Personal',
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `status` enum('Scheduled','Cancelled','Done') NOT NULL DEFAULT 'Scheduled',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `school_years`
--

CREATE TABLE `school_years` (
  `school_year_id` int(10) UNSIGNED NOT NULL,
  `year` varchar(9) NOT NULL,
  `status` enum('active','closed') NOT NULL DEFAULT 'closed',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `school_years`
--

INSERT INTO `school_years` (`school_year_id`, `year`, `status`, `created_at`, `updated_at`) VALUES
(1, '2026-2027', 'active', '2026-07-05 02:32:50', '2026-07-05 02:32:50'),
(2, '2023-2024', 'closed', '2023-06-01 08:00:00', '2024-05-30 17:00:00'),
(3, '2024-2025', 'closed', '2024-06-01 08:00:00', '2025-05-30 17:00:00'),
(4, '2025-2026', 'closed', '2025-06-01 08:00:00', '2026-05-30 17:00:00'),
(5, '2027-2028', 'closed', '2027-06-01 08:00:00', '2027-06-01 08:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `semesters`
--

CREATE TABLE `semesters` (
  `semester_id` int(10) UNSIGNED NOT NULL,
  `school_year_id` int(10) UNSIGNED NOT NULL,
  `semester_label` enum('1st Semester','2nd Semester') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_grading_locked` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
('fjuMlgh3boMJ6WQKa3lc45TQmHPmd5TqkBz3spio', 30, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiVWxmaTI2bzM4ckx2WXpOTThZVXRWaE5JakZhVGNlV0FDVHhsVmNUUyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9zdHVkZW50L2Fzc2lnbm1lbnRzIjtzOjU6InJvdXRlIjtzOjI1OiJzdHVkZW50LmFzc2lnbm1lbnRzLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MzA7fQ==', 1790628287),
('fwUwEpfnrMXvS4HsYes6W62ru78sYKKMKiTQvvUh', 31, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiMTdIYUxIaGdPcFp2OWx0UVFQNlhWRUZ1bDBad2RrV3VnZndwblVxMSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDg6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC90ZWFjaGVyL2Fzc2lnbm1lbnRzL2NyZWF0ZSI7czo1OiJyb3V0ZSI7czoyNjoidGVhY2hlci5hc3NpZ25tZW50cy5jcmVhdGUiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTozMTt9', 1790627528),
('r967Ohmmr9n6bULC5abKdwnDZv4k7itbvM7b6Iv8', 30, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiM1E4RDJrTFBXbnBWMEtvb2V0eURkRTNTcVo5dlRSdnB3YU5HRXdsVyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9zdHVkZW50IjtzOjU6InJvdXRlIjtzOjE3OiJzdHVkZW50LmRhc2hib2FyZCI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjMwO30=', 1790628545);

-- --------------------------------------------------------

--
-- Table structure for table `strands`
--

CREATE TABLE `strands` (
  `strand_id` int(10) UNSIGNED NOT NULL,
  `track_id` int(10) UNSIGNED NOT NULL,
  `strand_code` varchar(20) NOT NULL,
  `strand_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `strands`
--

INSERT INTO `strands` (`strand_id`, `track_id`, `strand_code`, `strand_name`, `description`, `created_at`) VALUES
(1, 1, 'STEM', 'Science, Technology, Engineering and Mathematics', 'For students inclined toward science and engineering courses', '2026-07-04 03:08:34'),
(2, 1, 'ABM', 'Accountancy, Business and Management', 'For students inclined toward business and finance-related courses', '2026-07-04 03:08:34'),
(3, 1, 'HUMSS', 'Humanities and Social Sciences', 'For students inclined toward law, education, and social science courses', '2026-07-04 03:08:34'),
(5, 2, 'ICT', 'Information and Communications Technology', 'Focuses on computer systems servicing, programming, and animation', '2026-07-04 03:08:34'),
(6, 2, 'HE', 'Home Economics', 'Focuses on cookery, food & beverage services, and tourism-related skills', '2026-07-04 03:08:34');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `lrn` varchar(12) NOT NULL,
  `student_number` varchar(20) NOT NULL,
  `grade_level` enum('11','12') NOT NULL,
  `strand_id` int(10) UNSIGNED DEFAULT NULL,
  `guardian_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `user_id`, `lrn`, `student_number`, `grade_level`, `strand_id`, `guardian_id`, `created_at`, `updated_at`) VALUES
(1, 1, '123456789012', '2026-0001', '11', 1, NULL, '2026-09-21 11:33:47', '2026-09-21 11:33:47'),
(2, 2, '123456789013', '2026-0002', '12', 1, NULL, '2026-09-21 11:33:47', '2026-09-21 11:33:47'),
(3, 6, '123456789014', '2026-0003', '11', 1, NULL, '2026-09-21 11:54:19', '2026-09-21 11:54:19'),
(9, 12, '123456789024', '2026-0005', '12', 2, NULL, '2026-10-21 11:54:19', '2026-09-21 12:50:53'),
(10, 13, '123456789025', '2023-0001', '11', 1, NULL, '2023-06-10 09:00:00', '2023-06-10 09:00:00'),
(11, 14, '123456789026', '2023-0002', '11', 2, NULL, '2023-06-10 09:15:00', '2023-06-10 09:15:00'),
(12, 15, '123456789027', '2024-0001', '11', 1, NULL, '2024-06-11 10:00:00', '2024-06-11 10:00:00'),
(13, 16, '123456789028', '2024-0002', '12', 3, NULL, '2024-06-11 10:30:00', '2024-06-11 10:30:00'),
(14, 17, '123456789029', '2025-0001', '11', 5, NULL, '2025-06-12 11:00:00', '2025-06-12 11:00:00'),
(15, 18, '123456789030', '2025-0002', '12', 1, NULL, '2025-06-12 11:20:00', '2025-06-12 11:20:00'),
(16, 19, '123456789031', '2026-0006', '11', 2, NULL, '2026-06-15 08:30:00', '2026-06-15 08:30:00'),
(17, 20, '123456789032', '2026-0007', '12', 3, NULL, '2026-06-15 09:00:00', '2026-06-15 09:00:00'),
(18, 21, '123456789033', '2027-0001', '11', 5, NULL, '2027-06-10 08:00:00', '2027-06-10 08:00:00'),
(19, 22, '123456789034', '2027-0002', '11', 1, NULL, '2027-06-10 08:30:00', '2027-06-10 08:30:00'),
(20, 24, 'Id enim qui', '52', '11', NULL, NULL, '2026-09-22 06:52:58', '2026-09-22 06:52:58'),
(21, 30, '108153120095', '2024-59212', '11', 1, NULL, '2026-09-28 20:06:45', '2026-09-28 20:30:55'),
(22, 32, '', '', '11', NULL, NULL, '2026-09-28 20:22:00', '2026-09-28 20:22:00');

-- --------------------------------------------------------

--
-- Table structure for table `student_guardians`
--

CREATE TABLE `student_guardians` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `guardian_id` int(10) UNSIGNED NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int(10) UNSIGNED NOT NULL,
  `strand_id` int(10) UNSIGNED DEFAULT NULL,
  `teacher_id` int(10) UNSIGNED DEFAULT NULL,
  `subject_code` varchar(20) NOT NULL,
  `subject_name` varchar(150) NOT NULL,
  `subject_type` enum('Core','Applied','Specialized') NOT NULL DEFAULT 'Core',
  `grade_level` enum('11','12') NOT NULL,
  `semester` enum('1st Semester','2nd Semester') NOT NULL,
  `units` decimal(3,1) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `strand_id`, `teacher_id`, `subject_code`, `subject_name`, `subject_type`, `grade_level`, `semester`, `units`, `description`, `status`, `created_at`) VALUES
(3, NULL, NULL, 'ORALCOM', 'Oral Communication', 'Core', '11', '1st Semester', 1.0, 'Develops effective oral communication skills in various contexts.', 'Active', '2026-07-12 17:27:13'),
(4, NULL, NULL, 'KOMPAN', 'Komunikasyon at Pananaliksik sa Wika at Kulturang Pilipino', 'Core', '11', '1st Semester', 1.0, 'Kasanayan sa komunikasyon at pananaliksik gamit ang wikang Filipino.', 'Active', '2026-07-12 17:27:13'),
(5, NULL, 5, 'GENMATH', 'General Mathematics', 'Core', '11', '1st Semester', 1.0, 'Covers functions, business math, and logic.', 'Active', '2026-07-12 17:27:13'),
(6, NULL, NULL, 'EARTHLIFE', 'Earth and Life Science', 'Core', '11', '1st Semester', 1.0, 'Introductory earth science and biology concepts.', 'Active', '2026-07-12 17:27:13'),
(7, NULL, NULL, 'PERSDEV', 'Personal Development', 'Core', '11', '1st Semester', 1.0, 'Self-awareness and personal growth topics.', 'Active', '2026-07-12 17:27:13'),
(8, NULL, NULL, 'PHILO', 'Introduction to the Philosophy of the Human Person', 'Core', '11', '1st Semester', 1.0, 'Philosophical reflection on the human condition.', 'Active', '2026-07-12 17:27:13'),
(9, NULL, NULL, 'PE1', 'Physical Education and Health 1', 'Core', '11', '1st Semester', 1.0, 'Physical fitness and health education.', 'Active', '2026-07-12 17:27:13'),
(10, NULL, NULL, 'READWRITE', 'Reading and Writing Skills', 'Core', '11', '2nd Semester', 1.0, 'Develops critical reading and academic writing skills.', 'Active', '2026-07-12 17:27:13'),
(11, NULL, NULL, 'PAGBASA', 'Pagbasa at Pagsusuri ng Iba\'t Ibang Teksto Tungo sa Pananaliksik', 'Core', '11', '2nd Semester', 1.0, 'Kritikal na pagbasa at pagsusuri ng teksto.', 'Active', '2026-07-12 17:27:13'),
(12, NULL, NULL, 'STATPROB', 'Statistics and Probability', 'Core', '11', '2nd Semester', 1.0, 'Basic statistical concepts and probability.', 'Active', '2026-07-12 17:27:13'),
(13, NULL, NULL, 'PHYSCI', 'Physical Science', 'Core', '11', '2nd Semester', 1.0, 'Introductory chemistry and physics concepts.', 'Active', '2026-07-12 17:27:13'),
(14, NULL, NULL, 'UCSP', 'Understanding Culture, Society and Politics', 'Core', '11', '2nd Semester', 1.0, 'Anthropological, sociological, and political perspectives.', 'Active', '2026-07-12 17:27:13'),
(15, NULL, NULL, 'LIT21', '21st Century Literature from the Philippines and the World', 'Core', '11', '2nd Semester', 1.0, 'Contemporary literary works and genres.', 'Active', '2026-07-12 17:27:13'),
(16, NULL, NULL, 'PE2', 'Physical Education and Health 2', 'Core', '11', '2nd Semester', 1.0, 'Physical fitness and health education.', 'Active', '2026-07-12 17:27:13'),
(17, NULL, NULL, 'EAPP', 'English for Academic and Professional Purposes', 'Applied', '12', '1st Semester', 1.0, 'Academic and workplace communication in English.', 'Active', '2026-07-12 17:27:13'),
(18, NULL, NULL, 'EMPTECH', 'Empowerment Technologies', 'Applied', '12', '1st Semester', 1.0, 'ICT skills for professional and personal use.', 'Active', '2026-07-12 17:27:13'),
(19, NULL, NULL, 'PRACRES1', 'Practical Research 1', 'Applied', '12', '1st Semester', 1.0, 'Introduction to qualitative research methods.', 'Active', '2026-07-12 17:27:13'),
(20, NULL, NULL, 'PE3', 'Physical Education and Health 3', 'Core', '12', '1st Semester', 1.0, 'Physical fitness and health education.', 'Active', '2026-07-12 17:27:13'),
(21, NULL, NULL, 'MIL', 'Media and Information Literacy', 'Applied', '12', '2nd Semester', 1.0, 'Critical evaluation and use of media and information.', 'Active', '2026-07-12 17:27:13'),
(22, NULL, NULL, 'PRACRES2', 'Practical Research 2', 'Applied', '12', '2nd Semester', 1.0, 'Introduction to quantitative research methods.', 'Active', '2026-07-12 17:27:13'),
(23, NULL, NULL, 'CPAR', 'Contemporary Philippine Arts from the Regions', 'Core', '12', '2nd Semester', 1.0, 'Survey of Philippine regional art forms.', 'Active', '2026-07-12 17:27:13'),
(24, NULL, NULL, 'PE4', 'Physical Education and Health 4', 'Core', '12', '2nd Semester', 1.0, 'Physical fitness and health education.', 'Active', '2026-07-12 17:27:13'),
(25, 1, NULL, 'PRECALC', 'Pre-Calculus', 'Specialized', '11', '1st Semester', 1.0, 'Functions, trigonometry, and analytic geometry.', 'Active', '2026-07-12 17:27:13'),
(26, 1, NULL, 'GENBIO1', 'General Biology 1', 'Specialized', '11', '1st Semester', 1.0, 'Cell biology, genetics, and evolution.', 'Active', '2026-07-12 17:27:13'),
(27, 1, NULL, 'GENCHEM1', 'General Chemistry 1', 'Specialized', '11', '1st Semester', 1.0, 'Atomic structure, bonding, and stoichiometry.', 'Active', '2026-07-12 17:27:13'),
(28, 1, NULL, 'BASICCALC', 'Basic Calculus', 'Specialized', '11', '2nd Semester', 1.0, 'Limits, derivatives, and integrals.', 'Active', '2026-07-12 17:27:13'),
(29, 1, NULL, 'GENBIO2', 'General Biology 2', 'Specialized', '11', '2nd Semester', 1.0, 'Physiology, ecology, and biodiversity.', 'Active', '2026-07-12 17:27:13'),
(30, 1, NULL, 'GENCHEM2', 'General Chemistry 2', 'Specialized', '11', '2nd Semester', 1.0, 'Organic chemistry and reaction kinetics.', 'Active', '2026-07-12 17:27:13'),
(31, 1, NULL, 'GENPHYS1', 'General Physics 1', 'Specialized', '12', '1st Semester', 1.0, 'Mechanics, kinematics, and dynamics.', 'Active', '2026-07-12 17:27:13'),
(32, 1, NULL, 'STEMRES1', 'Research Project 1 (STEM)', 'Specialized', '12', '1st Semester', 1.0, 'Design and proposal of a STEM-based research/capstone project.', 'Active', '2026-07-12 17:27:13'),
(33, 1, NULL, 'GENPHYS2', 'General Physics 2', 'Specialized', '12', '2nd Semester', 1.0, 'Electricity, magnetism, and modern physics.', 'Active', '2026-07-12 17:27:13'),
(34, 1, NULL, 'STEMRES2', 'Research Project 2 (STEM)', 'Specialized', '12', '2nd Semester', 1.0, 'Implementation and defense of the STEM capstone project.', 'Active', '2026-07-12 17:27:13'),
(35, 2, NULL, 'ABM1', 'Fundamentals of Accountancy, Business and Management 1', 'Specialized', '11', '1st Semester', 1.0, 'Basic accounting principles and the accounting cycle.', 'Active', '2026-07-12 17:27:13'),
(36, 2, NULL, 'BUSMATH', 'Business Mathematics', 'Specialized', '11', '1st Semester', 1.0, 'Mathematical tools for business decision-making.', 'Active', '2026-07-12 17:27:13'),
(37, 2, NULL, 'ORGMAN', 'Organization and Management', 'Specialized', '11', '1st Semester', 1.0, 'Principles of management and organizational behavior.', 'Active', '2026-07-12 17:27:13'),
(38, 2, NULL, 'ABM2', 'Fundamentals of Accountancy, Business and Management 2', 'Specialized', '11', '2nd Semester', 1.0, 'Financial statement analysis and reporting.', 'Active', '2026-07-12 17:27:13'),
(39, 2, NULL, 'APPECON', 'Applied Economics', 'Specialized', '11', '2nd Semester', 1.0, 'Micro and macroeconomic concepts applied to real markets.', 'Active', '2026-07-12 17:27:13'),
(40, 2, NULL, 'BUSFIN', 'Business Finance', 'Specialized', '11', '2nd Semester', 1.0, 'Financial planning, budgeting, and investment basics.', 'Active', '2026-07-12 17:27:13'),
(41, 2, NULL, 'MKTG', 'Principles of Marketing', 'Specialized', '12', '1st Semester', 1.0, 'Marketing mix, consumer behavior, and market research.', 'Active', '2026-07-12 17:27:13'),
(42, 2, NULL, 'BUSETHICS', 'Business Ethics and Social Responsibility', 'Specialized', '12', '1st Semester', 1.0, 'Ethical decision-making and corporate social responsibility.', 'Active', '2026-07-12 17:27:13'),
(43, 2, NULL, 'BUSSIM1', 'Business Enterprise Simulation 1', 'Specialized', '12', '2nd Semester', 1.0, 'Hands-on simulation of running a small business.', 'Active', '2026-07-12 17:27:13'),
(44, 2, NULL, 'BUSSIM2', 'Work Immersion / Business Enterprise Simulation', 'Specialized', '12', '2nd Semester', 1.0, 'Practical business/work experience component.', 'Active', '2026-07-12 17:27:13'),
(45, 3, NULL, 'CREWRITE', 'Creative Writing', 'Specialized', '11', '1st Semester', 1.0, 'Introduction to creative writing across genres.', 'Active', '2026-07-12 17:27:13'),
(46, 3, NULL, 'DISCSOC', 'Disciplines and Ideas in the Social Sciences', 'Specialized', '11', '1st Semester', 1.0, 'Overview of anthropology, sociology, political science, psychology, and economics.', 'Active', '2026-07-12 17:27:13'),
(47, 3, NULL, 'PHILGOV', 'Philippine Politics and Governance', 'Specialized', '11', '2nd Semester', 1.0, 'Philippine political structures and governance issues.', 'Active', '2026-07-12 17:27:13'),
(48, 3, NULL, 'CREATNONFIC', 'Creative Nonfiction: The Literary Essay', 'Specialized', '11', '2nd Semester', 1.0, 'Writing nonfiction narrative and essay forms.', 'Active', '2026-07-12 17:27:13'),
(49, 3, NULL, 'DISCAPPSOC', 'Disciplines and Ideas in the Applied Social Sciences', 'Specialized', '12', '1st Semester', 1.0, 'Applied concepts in counseling, communication, and social work.', 'Active', '2026-07-12 17:27:13'),
(50, 3, NULL, 'COMMENGAGE', 'Community Engagement, Solidarity, and Citizenship', 'Specialized', '12', '1st Semester', 1.0, 'Civic engagement and community-based projects.', 'Active', '2026-07-12 17:27:13'),
(51, 3, NULL, 'TRENDSNAT', 'Trends, Networks, and Critical Thinking in the 21st Century Culture', 'Specialized', '12', '2nd Semester', 1.0, 'Analysis of global cultural and social trends.', 'Active', '2026-07-12 17:27:13'),
(52, 3, NULL, 'SOCSCIRES', 'Social Science Research', 'Specialized', '12', '2nd Semester', 1.0, 'Capstone research project in the social sciences.', 'Active', '2026-07-12 17:27:13'),
(53, 5, NULL, 'CSSINTRO', 'Introduction to Computer Systems Servicing', 'Specialized', '11', '1st Semester', 1.0, 'Computer hardware, assembly, and basic troubleshooting.', 'Active', '2026-07-12 17:27:13'),
(54, 5, NULL, 'COMPPROG1', 'Computer Programming 1', 'Specialized', '11', '2nd Semester', 1.0, 'Fundamentals of programming logic and design.', 'Active', '2026-07-12 17:27:13'),
(55, 5, NULL, 'COMPPROG2', 'Computer Programming 2', 'Specialized', '12', '1st Semester', 1.0, 'Object-oriented programming and application development.', 'Active', '2026-07-12 17:27:13'),
(56, 5, NULL, 'ANIMATION', 'Animation', 'Specialized', '12', '2nd Semester', 1.0, 'Principles of 2D/3D animation and digital media production.', 'Active', '2026-07-12 17:27:13'),
(57, 6, NULL, 'COOKERY', 'Cookery', 'Specialized', '11', '1st Semester', 1.0, 'Basic food preparation and cooking methods.', 'Active', '2026-07-12 17:27:13'),
(58, 6, NULL, 'BREADPAS', 'Bread and Pastry Production', 'Specialized', '11', '2nd Semester', 1.0, 'Baking techniques for bread, cakes, and pastries.', 'Active', '2026-07-12 17:27:13'),
(59, 6, NULL, 'FBSERV', 'Food and Beverage Services', 'Specialized', '12', '1st Semester', 1.0, 'Dining service standards and hospitality skills.', 'Active', '2026-07-12 17:27:13'),
(60, 6, NULL, 'HOUSEKEEP', 'Housekeeping', 'Specialized', '12', '2nd Semester', 1.0, 'Housekeeping operations for the hospitality industry.', 'Active', '2026-07-12 17:27:13'),
(61, NULL, 2, 'PUKE101', 'Puke ng Ina Mo', 'Core', '11', '1st Semester', 3.0, NULL, 'Active', '2026-09-28 17:13:51'),
(62, 1, NULL, 'CAL101', 'Calendar Demo Subject', 'Core', '11', '1st Semester', 1.0, NULL, 'Active', '2026-09-28 20:30:55');

-- --------------------------------------------------------

--
-- Table structure for table `subject_grading_templates`
--

CREATE TABLE `subject_grading_templates` (
  `id` int(10) UNSIGNED NOT NULL,
  `subject_id` int(10) UNSIGNED NOT NULL,
  `template_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE `submissions` (
  `submission_id` int(10) UNSIGNED NOT NULL,
  `assignment_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `file_url` varchar(500) DEFAULT NULL,
  `score` decimal(6,2) DEFAULT NULL,
  `status` enum('Pending','Submitted','Late','Graded') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `submissions`
--

INSERT INTO `submissions` (`submission_id`, `assignment_id`, `student_id`, `submitted_at`, `file_url`, `score`, `status`) VALUES
(1, 2, 21, '2026-09-28 20:44:22', 'submissions/2/HoBq2dV6XGrtQMWQGNayAFUWlUYswv7M59VUPjMg.pdf', 25.00, 'Graded');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `teacher_number` varchar(20) NOT NULL,
  `specialization` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`teacher_id`, `user_id`, `teacher_number`, `specialization`, `created_at`, `updated_at`) VALUES
(1, 3, 'TCH-2026-0001', 'Mathematics', '2026-09-21 11:33:47', '2026-09-22 14:48:38'),
(2, 4, 'TCH-2026-0002', 'Science', '2026-09-21 11:33:47', '2026-09-22 14:48:38'),
(5, 29, '2024-59211', 'Mathematics', '2026-09-28 20:08:35', '2026-09-28 20:08:35'),
(6, 31, '', '', '2026-09-28 20:21:03', '2026-09-28 20:21:03');

-- --------------------------------------------------------

--
-- Table structure for table `tracks`
--

CREATE TABLE `tracks` (
  `track_id` int(10) UNSIGNED NOT NULL,
  `track_code` varchar(10) NOT NULL,
  `track_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tracks`
--

INSERT INTO `tracks` (`track_id`, `track_code`, `track_name`, `description`, `created_at`) VALUES
(1, 'ACAD', 'Academic Track', 'Prepares students for college/university education', '2026-07-04 03:08:34'),
(2, 'TVL', 'Technical-Vocational-Livelihood Track', 'Prepares students for employment, entrepreneurship, or middle-level skills development', '2026-07-04 03:08:34');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  `email` varchar(100) NOT NULL,
  `role` enum('Admin','Staff','Registrar','Accounting','Teacher','Student','Guardian') NOT NULL DEFAULT 'Student',
  `status` enum('Active','Inactive','Suspended','Locked') NOT NULL DEFAULT 'Active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `contact_number` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `middle_name`, `password`, `must_change_password`, `email`, `role`, `status`, `is_deleted`, `contact_number`, `address`, `birthdate`, `gender`, `created_at`, `updated_at`) VALUES
(1, 'jason', 'begornia', 'verzosa', '$2y$12$kx57NeLjHA0uhu2G.ttXuuORwPnTp8LyqNRCX4lWO9GRsn6l8Hrxu', 0, 'jasonbegornia57@gmail.com', 'Admin', 'Active', 0, '09945646355', 'blk 34 lot 3 cluster 4 bella vista, general trias cavite', '2002-11-11', 'Male', '2026-09-21 00:37:16', '2026-09-21 08:41:51'),
(2, 'Tung ', 'Tung', 'Sahur', '$2y$12$kx57NeLjHA0uhu2G.ttXuuORwPnTp8LyqNRCX4lWO9GRsn6l8Hrxu', 0, 'sahur26f@gmail.com', 'Student', 'Active', 0, '09945646355', 'blk 35 lot 3 cluster 4 bella vista, general trias cavite', '2003-11-11', 'Male', '2026-09-21 00:37:16', '2026-09-21 09:53:05'),
(3, 'Bombardino', 'Crocodilo', 'Tung', 'Admin123', 0, 'bombardino.crocodilo@example.com', 'Teacher', 'Inactive', 0, '09191234569', 'Brainrot Street, Pasig', '1998-07-10', 'Male', '2026-09-21 10:04:49', '2026-09-21 14:27:28'),
(4, 'Ballerina', 'Cappuccina', 'Tralala', 'password123', 0, 'ballerina.cappuccina@example.com', 'Teacher', 'Active', 0, '09201234570', 'Latte Road, Makati', '1999-11-05', 'Female', '2026-09-21 10:04:49', '2026-09-21 12:59:49'),
(5, 'Cappuccino', 'Assassino', 'Sigma', 'password123', 0, 'cappuccino.assassino@example.com', 'Teacher', 'Suspended', 0, '09211234571', 'Espresso Street, Taguig', '1997-05-18', 'Male', '2026-09-21 10:04:49', '2026-09-21 10:04:49'),
(6, 'Trippi', 'Troppi', 'Tung', 'password123', 0, 'trippi.troppi@example.com', 'Student', 'Active', 1, '09221234572', 'Brainrot Village, Imus', '2005-09-12', 'Other', '2026-09-21 10:04:49', '2026-09-21 10:28:16'),
(7, 'Frigo', 'Camelo', 'Bombardino', 'password123', 0, 'frigo.camelo@example.com', 'Teacher', 'Active', 0, '09231234573', 'Camel Road, Dasmarinas', '1985-02-28', 'Male', '2026-09-21 10:04:49', '2026-09-21 12:59:27'),
(8, 'Chimpanzini', 'Bananini', 'Tralala', 'password123', 0, 'chimpanzini.bananini@example.com', 'Student', 'Locked', 0, '09241234574', 'Banana Street, Cavite', '2006-06-30', 'Male', '2026-09-21 10:04:49', '2026-09-21 10:04:49'),
(9, 'Brr Brr', 'Patapim', 'Cappuccina', 'password123', 0, 'brr.patapim@example.com', 'Teacher', 'Active', 0, '09251234575', 'Patapim Road, Bacoor', '1995-12-20', 'Female', '2026-09-21 10:04:49', '2026-09-21 10:04:49'),
(10, 'Lirili', 'Larila', 'Bananini', 'password123', 0, 'lirili.larila@example.com', 'Teacher', 'Inactive', 0, '09261234576', 'Lalala Street, General Trias', '2002-08-08', 'Female', '2026-09-21 10:04:49', '2026-09-21 12:58:44'),
(11, 'blud', 'Sahur', 'Sigma', 'password123', 0, 'tung.sahur@example.com', 'Admin', 'Active', 0, '09171234567', 'Ohio Street, Manila', '2000-01-15', 'Male', '2026-09-21 10:04:49', '2026-09-21 10:04:49'),
(12, 'Tralalero', 'Tralala', 'Bombardino', 'password123', 0, 'tralalero.tralala@example.com', 'Student', 'Active', 1, '09181234568', 'Skibidi Avenue, Quezon City', '2001-03-22', 'Male', '2026-09-21 10:04:49', '2026-09-22 08:46:47'),
(13, 'Juan', 'Dela Cruz', 'Santos', '$2y$12$kx57NeLjHA0uhu2G.ttXuuORwPnTp8LyqNRCX4lWO9GRsn6l8Hrxu', 0, 'juan.delacruz@example.com', 'Student', 'Active', 0, NULL, NULL, NULL, NULL, '2023-06-10 09:00:00', '2026-09-21 12:57:43'),
(14, 'Maria', 'Clara', 'Rizal', '$2y$12$kx57NeLjHA0uhu2G.ttXuuORwPnTp8LyqNRCX4lWO9GRsn6l8Hrxu', 0, 'maria.clara@example.com', 'Student', 'Active', 0, NULL, NULL, NULL, NULL, '2023-06-10 09:15:00', '2026-09-21 12:57:43'),
(15, 'Andres', 'Bonifacio', 'Castro', '$2y$12$kx57NeLjHA0uhu2G.ttXuuORwPnTp8LyqNRCX4lWO9GRsn6l8Hrxu', 0, 'andres.b@example.com', 'Student', 'Active', 0, NULL, NULL, NULL, NULL, '2024-06-11 10:00:00', '2026-09-21 12:57:43'),
(16, 'Jose', 'Rizal', 'Mercado', '$2y$12$kx57NeLjHA0uhu2G.ttXuuORwPnTp8LyqNRCX4lWO9GRsn6l8Hrxu', 0, 'jose.rizal@example.com', 'Student', 'Active', 0, NULL, NULL, NULL, NULL, '2024-06-11 10:30:00', '2026-09-21 12:57:43'),
(17, 'Emilio', 'Aguinaldo', 'Famy', '$2y$12$kx57NeLjHA0uhu2G.ttXuuORwPnTp8LyqNRCX4lWO9GRsn6l8Hrxu', 0, 'emilio.a@example.com', 'Student', 'Active', 0, NULL, NULL, NULL, NULL, '2025-06-12 11:00:00', '2026-09-21 12:57:43'),
(18, 'Apolinario', 'Mabini', 'Maranan', '$2y$12$kx57NeLjHA0uhu2G.ttXuuORwPnTp8LyqNRCX4lWO9GRsn6l8Hrxu', 0, 'apolinario.m@example.com', 'Student', 'Active', 0, NULL, NULL, NULL, NULL, '2025-06-12 11:20:00', '2026-09-21 12:57:43'),
(19, 'Melchora', 'Aquino', 'Ramos', '$2y$12$kx57NeLjHA0uhu2G.ttXuuORwPnTp8LyqNRCX4lWO9GRsn6l8Hrxu', 0, 'melchora.a@example.com', 'Student', 'Active', 0, NULL, NULL, NULL, NULL, '2026-06-15 08:30:00', '2026-09-21 12:57:43'),
(20, 'Gabriela', 'Silang', 'Cariño', '$2y$12$kx57NeLjHA0uhu2G.ttXuuORwPnTp8LyqNRCX4lWO9GRsn6l8Hrxu', 0, 'gabriela.s@example.com', 'Student', 'Active', 0, NULL, NULL, NULL, NULL, '2026-06-15 09:00:00', '2026-09-21 12:57:43'),
(21, 'Antonio', 'Luna', 'Novicio', '$2y$12$kx57NeLjHA0uhu2G.ttXuuORwPnTp8LyqNRCX4lWO9GRsn6l8Hrxu', 0, 'antonio.luna@example.com', 'Student', 'Active', 0, NULL, NULL, NULL, NULL, '2027-06-10 08:00:00', '2026-09-21 12:57:43'),
(22, 'Marcelo', 'Del Pilar', 'Hilario', '$2y$12$kx57NeLjHA0uhu2G.ttXuuORwPnTp8LyqNRCX4lWO9GRsn6l8Hrxu', 0, 'marcelo.delpilar@example.com', 'Student', 'Active', 0, NULL, NULL, NULL, NULL, '2027-06-10 08:30:00', '2026-09-21 12:57:43'),
(24, 'Jack', 'Nelson', 'Wing Hubbard', '$2y$12$IDXpnr6zLQlvV9rphgtkvet6YggzpTG4oBmwII8vJpkyVunoEn6rm', 0, 'jivecituc@mailinator.com', 'Student', 'Active', 0, '248', 'Officia harum dolore', '1998-10-01', 'Male', '2026-09-22 06:52:58', '2026-09-22 08:46:37'),
(27, 'Herman', 'Ford', 'Alfonso Anderson', '$2y$12$RCIrKp2RmR1iMMH2AqDDkeJ2iqi7C/Bw9BBw/JTAfjq8pUw444kLK', 0, 'vubybiqud@mailinator.com', 'Admin', 'Suspended', 0, '542', 'Consequuntur aut cul', '2013-06-12', 'Other', '2026-09-22 07:14:40', '2026-09-22 07:14:40'),
(28, 'Felix', 'Dunlap', 'Kaye Bush', '$2y$12$5tTo57cVirDG7MjYe10rz.AXSRcCUVD36UWo3TmZb/KN0LMI.IW1K', 0, 'lancealexisv@gmail.com', 'Admin', 'Active', 0, '679', 'Veniam nisi non ist', '1973-03-17', 'Other', '2026-09-28 17:00:09', '2026-09-28 17:09:49'),
(29, 'Jocelyn', 'Anderson', 'Alexis Gross', '$2y$12$fF4JCPRFk8PoMJDY8qQL0u58y5to3ayTdqGxiMc.VZr.ArQCPV9Ua', 0, 'teacher@gmail.com', 'Teacher', 'Active', 0, '240', 'Quia explicabo Tene', '1993-06-07', 'Male', '2026-09-28 20:05:00', '2026-09-28 20:08:58'),
(30, 'Tatyana', 'Wilder', 'Keith Francis', '$2y$12$iixlkcVrqmuKdvEewVkEvuAxoP0qJadJuG/k0/K/S.90S.TX3jHT6', 0, 'student1@gmail.com', 'Student', 'Active', 0, '671', 'Dolor debitis minim', '2013-02-08', 'Other', '2026-09-28 20:06:45', '2026-09-28 20:15:49'),
(31, 'Titser Lance', 'Vill', 'Allegra Hensley', '$2y$12$tGXzUGHuk2mfNZ6nnDzd3O9wxGT5XSZxqQyEih7Q7C/Fxz.vXS9s6', 0, 'teacher1@demo.com', 'Teacher', 'Active', 0, '525', 'Ipsum sed voluptate', '1994-08-08', 'Female', '2026-09-28 20:21:03', '2026-09-28 20:25:36'),
(32, 'Studint Lans', 'Avill', 'Ryan Greene', '$2y$12$IwL8I/xyFnAmzh8Qm0zJ8.77TakaejxJHfrZrJ/XMz/26PG47THPu', 1, 'student1@demo.com', 'Student', 'Active', 0, '333', 'Ut possimus modi cu', '2013-04-01', 'Other', '2026-09-28 20:22:00', '2026-09-28 20:22:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `announcements_posted_by_foreign` (`posted_by`),
  ADD KEY `announcements_section_id_foreign` (`section_id`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `assignments_schedule_id_foreign` (`schedule_id`);

--
-- Indexes for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD PRIMARY KEY (`attendance_id`),
  ADD UNIQUE KEY `uq_attendance_once` (`schedule_id`,`student_id`,`attendance_date`),
  ADD KEY `attendance_records_student_id_foreign` (`student_id`),
  ADD KEY `attendance_records_logged_by_foreign` (`logged_by`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_audit_entity` (`entity_type`,`entity_id`),
  ADD KEY `audit_logs_user_id_foreign` (`user_id`);

--
-- Indexes for table `class_sections`
--
ALTER TABLE `class_sections`
  ADD PRIMARY KEY (`section_id`),
  ADD KEY `class_sections_strand_id_foreign` (`strand_id`),
  ADD KEY `class_sections_adviser_id_foreign` (`adviser_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD KEY `enrollments_student_id_foreign` (`student_id`),
  ADD KEY `enrollments_section_id_foreign` (`section_id`),
  ADD KEY `enrollments_school_year_id_foreign` (`school_year_id`);

--
-- Indexes for table `final_grades`
--
ALTER TABLE `final_grades`
  ADD PRIMARY KEY (`final_grade_id`),
  ADD UNIQUE KEY `uq_final_grade` (`enrollment_id`,`subject_id`),
  ADD KEY `idx_final_grade_locked` (`is_locked`),
  ADD KEY `final_grades_subject_id_foreign` (`subject_id`);

--
-- Indexes for table `grade_components`
--
ALTER TABLE `grade_components`
  ADD PRIMARY KEY (`component_id`),
  ADD KEY `grade_components_enrollment_id_foreign` (`enrollment_id`),
  ADD KEY `grade_components_subject_id_foreign` (`subject_id`);

--
-- Indexes for table `grading_templates`
--
ALTER TABLE `grading_templates`
  ADD PRIMARY KEY (`template_id`);

--
-- Indexes for table `guardians`
--
ALTER TABLE `guardians`
  ADD PRIMARY KEY (`guardian_id`),
  ADD UNIQUE KEY `guardians_user_id_unique` (`user_id`);

--
-- Indexes for table `guidance_records`
--
ALTER TABLE `guidance_records`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `guidance_records_student_id_foreign` (`student_id`),
  ADD KEY `guidance_records_logged_by_foreign` (`logged_by`);

--
-- Indexes for table `learning_materials`
--
ALTER TABLE `learning_materials`
  ADD PRIMARY KEY (`material_id`),
  ADD KEY `learning_materials_schedule_id_foreign` (`schedule_id`),
  ADD KEY `learning_materials_uploaded_by_foreign` (`uploaded_by`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `idx_message_receiver_unread` (`receiver_id`,`read_at`),
  ADD KEY `messages_sender_id_foreign` (`sender_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_notification_unread` (`user_id`,`is_read`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`quiz_id`),
  ADD KEY `quizzes_schedule_id_foreign` (`schedule_id`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`attempt_id`),
  ADD UNIQUE KEY `uq_attempt_once` (`quiz_id`,`student_id`),
  ADD KEY `quiz_attempts_student_id_foreign` (`student_id`);

--
-- Indexes for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `quiz_questions_quiz_id_foreign` (`quiz_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`),
  ADD UNIQUE KEY `rooms_room_name_unique` (`room_name`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `schedules_section_id_foreign` (`section_id`),
  ADD KEY `schedules_subject_id_foreign` (`subject_id`),
  ADD KEY `schedules_teacher_id_foreign` (`teacher_id`),
  ADD KEY `schedules_room_id_foreign` (`room_id`);

--
-- Indexes for table `schedule_events`
--
ALTER TABLE `schedule_events`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `school_years`
--
ALTER TABLE `school_years`
  ADD PRIMARY KEY (`school_year_id`),
  ADD UNIQUE KEY `school_years_year_unique` (`year`);

--
-- Indexes for table `semesters`
--
ALTER TABLE `semesters`
  ADD PRIMARY KEY (`semester_id`),
  ADD UNIQUE KEY `uq_semester_per_year` (`school_year_id`,`semester_label`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `strands`
--
ALTER TABLE `strands`
  ADD PRIMARY KEY (`strand_id`),
  ADD UNIQUE KEY `strands_strand_code_unique` (`strand_code`),
  ADD KEY `strands_track_id_foreign` (`track_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `students_user_id_unique` (`user_id`),
  ADD UNIQUE KEY `students_lrn_unique` (`lrn`),
  ADD UNIQUE KEY `students_student_number_unique` (`student_number`),
  ADD KEY `students_strand_id_foreign` (`strand_id`),
  ADD KEY `students_guardian_id_foreign` (`guardian_id`);

--
-- Indexes for table `student_guardians`
--
ALTER TABLE `student_guardians`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_student_guardian` (`student_id`,`guardian_id`),
  ADD KEY `student_guardians_guardian_id_foreign` (`guardian_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`),
  ADD UNIQUE KEY `subjects_subject_code_unique` (`subject_code`),
  ADD KEY `subjects_strand_id_foreign` (`strand_id`),
  ADD KEY `subjects_teacher_id_foreign` (`teacher_id`);

--
-- Indexes for table `subject_grading_templates`
--
ALTER TABLE `subject_grading_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_subject_template` (`subject_id`),
  ADD KEY `subject_grading_templates_template_id_foreign` (`template_id`);

--
-- Indexes for table `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`submission_id`),
  ADD UNIQUE KEY `uq_submission_once` (`assignment_id`,`student_id`),
  ADD KEY `idx_submission_status` (`status`),
  ADD KEY `submissions_student_id_foreign` (`student_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`teacher_id`),
  ADD UNIQUE KEY `teachers_user_id_unique` (`user_id`),
  ADD UNIQUE KEY `teachers_teacher_number_unique` (`teacher_number`);

--
-- Indexes for table `tracks`
--
ALTER TABLE `tracks`
  ADD PRIMARY KEY (`track_id`),
  ADD UNIQUE KEY `tracks_track_code_unique` (`track_code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcement_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `assignment_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `attendance_records`
--
ALTER TABLE `attendance_records`
  MODIFY `attendance_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class_sections`
--
ALTER TABLE `class_sections`
  MODIFY `section_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `enrollment_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `final_grades`
--
ALTER TABLE `final_grades`
  MODIFY `final_grade_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grade_components`
--
ALTER TABLE `grade_components`
  MODIFY `component_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grading_templates`
--
ALTER TABLE `grading_templates`
  MODIFY `template_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guardians`
--
ALTER TABLE `guardians`
  MODIFY `guardian_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guidance_records`
--
ALTER TABLE `guidance_records`
  MODIFY `record_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `learning_materials`
--
ALTER TABLE `learning_materials`
  MODIFY `material_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `quiz_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `attempt_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `question_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `schedule_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `schedule_events`
--
ALTER TABLE `schedule_events`
  MODIFY `event_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `school_years`
--
ALTER TABLE `school_years`
  MODIFY `school_year_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `semesters`
--
ALTER TABLE `semesters`
  MODIFY `semester_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `strands`
--
ALTER TABLE `strands`
  MODIFY `strand_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `student_guardians`
--
ALTER TABLE `student_guardians`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `subject_grading_templates`
--
ALTER TABLE `subject_grading_templates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `submission_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `teacher_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tracks`
--
ALTER TABLE `tracks`
  MODIFY `track_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_posted_by_foreign` FOREIGN KEY (`posted_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `announcements_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `class_sections` (`section_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`schedule_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD CONSTRAINT `attendance_records_logged_by_foreign` FOREIGN KEY (`logged_by`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `attendance_records_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`schedule_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `attendance_records_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `class_sections`
--
ALTER TABLE `class_sections`
  ADD CONSTRAINT `class_sections_adviser_id_foreign` FOREIGN KEY (`adviser_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `class_sections_strand_id_foreign` FOREIGN KEY (`strand_id`) REFERENCES `strands` (`strand_id`) ON UPDATE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_school_year_id_foreign` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`school_year_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `enrollments_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `class_sections` (`section_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `enrollments_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON UPDATE CASCADE;

--
-- Constraints for table `final_grades`
--
ALTER TABLE `final_grades`
  ADD CONSTRAINT `final_grades_enrollment_id_foreign` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`enrollment_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `final_grades_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON UPDATE CASCADE;

--
-- Constraints for table `grade_components`
--
ALTER TABLE `grade_components`
  ADD CONSTRAINT `grade_components_enrollment_id_foreign` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`enrollment_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `grade_components_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON UPDATE CASCADE;

--
-- Constraints for table `guardians`
--
ALTER TABLE `guardians`
  ADD CONSTRAINT `guardians_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `guidance_records`
--
ALTER TABLE `guidance_records`
  ADD CONSTRAINT `guidance_records_logged_by_foreign` FOREIGN KEY (`logged_by`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `guidance_records_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `learning_materials`
--
ALTER TABLE `learning_materials`
  ADD CONSTRAINT `learning_materials_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`schedule_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `learning_materials_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_receiver_id_foreign` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`schedule_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `quiz_attempts_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`quiz_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `quiz_attempts_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD CONSTRAINT `quiz_questions_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`quiz_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `schedules_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `class_sections` (`section_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `schedules_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `schedules_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `semesters`
--
ALTER TABLE `semesters`
  ADD CONSTRAINT `semesters_school_year_id_foreign` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`school_year_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `strands`
--
ALTER TABLE `strands`
  ADD CONSTRAINT `strands_track_id_foreign` FOREIGN KEY (`track_id`) REFERENCES `tracks` (`track_id`) ON UPDATE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_guardian_id_foreign` FOREIGN KEY (`guardian_id`) REFERENCES `guardians` (`guardian_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `students_strand_id_foreign` FOREIGN KEY (`strand_id`) REFERENCES `strands` (`strand_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `students_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_guardians`
--
ALTER TABLE `student_guardians`
  ADD CONSTRAINT `student_guardians_guardian_id_foreign` FOREIGN KEY (`guardian_id`) REFERENCES `guardians` (`guardian_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_guardians_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `subjects_strand_id_foreign` FOREIGN KEY (`strand_id`) REFERENCES `strands` (`strand_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subjects_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `subject_grading_templates`
--
ALTER TABLE `subject_grading_templates`
  ADD CONSTRAINT `subject_grading_templates_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `subject_grading_templates_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `grading_templates` (`template_id`) ON UPDATE CASCADE;

--
-- Constraints for table `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `submissions_assignment_id_foreign` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`assignment_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `submissions_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
