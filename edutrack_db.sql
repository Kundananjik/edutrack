-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 07, 2025 at 09:04 AM
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
-- Database: `edutrack_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `audience` enum('students','lecturers','all') NOT NULL DEFAULT 'all',
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `message`, `audience`, `created_by`, `created_at`) VALUES
(6, 'HI', 'On Monday there will be the maintenance of the system.', 'all', 1, '2025-11-21 16:54:12');

-- --------------------------------------------------------

--
-- Table structure for table `announcement_students`
--

CREATE TABLE `announcement_students` (
  `announcement_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_records`
--

CREATE TABLE `attendance_records` (
  `id` int(11) UNSIGNED NOT NULL,
  `session_id` int(11) UNSIGNED NOT NULL,
  `student_id` int(11) UNSIGNED NOT NULL,
  `signed_in_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance_records`
--

INSERT INTO `attendance_records` (`id`, `session_id`, `student_id`, `signed_in_at`) VALUES
(4, 23, 6, '2025-10-22 06:08:15'),
(5, 23, 3, '2025-10-22 06:08:58'),
(6, 38, 6, '2025-11-03 13:22:27'),
(7, 41, 6, '2025-11-07 12:58:49'),
(8, 42, 6, '2025-11-07 13:19:06'),
(9, 54, 6, '2025-11-15 12:27:41'),
(10, 54, 3, '2025-11-15 12:28:31'),
(11, 55, 3, '2025-11-15 12:31:40'),
(12, 56, 6, '2025-11-26 12:23:42');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_sessions`
--

CREATE TABLE `attendance_sessions` (
  `id` int(11) UNSIGNED NOT NULL,
  `course_id` int(11) UNSIGNED NOT NULL,
  `lecturer_id` int(11) UNSIGNED NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0 for inactive, 1 for active',
  `session_code` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance_sessions`
--

INSERT INTO `attendance_sessions` (`id`, `course_id`, `lecturer_id`, `is_active`, `session_code`, `created_at`, `updated_at`) VALUES
(23, 1, 5, 0, 'KUG00GJ', '2025-10-22 06:07:12', '2025-10-22 06:09:25'),
(24, 1, 5, 0, 'YCYXFXD', '2025-10-30 13:25:56', '2025-11-02 19:10:51'),
(29, 1, 5, 0, 'VSN3XPT', '2025-11-02 20:34:58', '2025-11-02 20:45:27'),
(30, 1, 5, 0, '1T6BIQO', '2025-11-02 20:45:34', '2025-11-02 20:45:54'),
(31, 1, 5, 0, 'WB5WJKT', '2025-11-02 20:46:06', '2025-11-02 20:46:19'),
(32, 1, 5, 0, '1ZSRFE4', '2025-11-02 20:51:57', '2025-11-02 20:52:00'),
(38, 1, 5, 0, 'TFA7Y5P', '2025-11-03 13:03:17', '2025-11-04 08:11:28'),
(39, 1, 5, 0, '4VIABFI', '2025-11-04 08:21:45', '2025-11-04 08:43:16'),
(40, 1, 5, 0, '9PILAIY', '2025-11-04 08:43:22', '2025-11-04 08:43:25'),
(41, 1, 5, 0, '6NP9AOC', '2025-11-07 12:57:54', '2025-11-07 12:59:12'),
(42, 1, 5, 0, 'AI4GXVP', '2025-11-07 13:17:56', '2025-11-09 23:33:47'),
(43, 3, 5, 0, 'VAUOMUK', '2025-11-09 23:33:47', '2025-11-09 23:33:52'),
(44, 1, 5, 0, '1AYHO05', '2025-11-09 23:57:25', '2025-11-09 23:57:31'),
(45, 3, 5, 0, '2X345YB', '2025-11-09 23:57:38', '2025-11-09 23:57:41'),
(46, 3, 5, 0, 'PUOMTME', '2025-11-10 00:07:00', '2025-11-10 00:07:08'),
(47, 3, 5, 0, 'B1HL6KP', '2025-11-10 01:30:34', '2025-11-10 01:30:46'),
(48, 1, 5, 0, 'WW3DT4I', '2025-11-10 01:30:53', '2025-11-10 01:33:43'),
(49, 3, 5, 0, 'RJV3XO8', '2025-11-10 01:34:04', '2025-11-10 01:34:30'),
(50, 1, 5, 0, 'NS1RUXL', '2025-11-10 01:34:12', '2025-11-10 01:34:25'),
(51, 1, 5, 0, 'CMH7I9J', '2025-11-12 12:39:44', '2025-11-12 12:40:12'),
(52, 1, 5, 0, '1GEQCO7', '2025-11-13 11:36:20', '2025-11-13 20:47:01'),
(53, 1, 5, 0, 'ODRAYXV', '2025-11-13 20:47:16', '2025-11-13 20:48:49'),
(54, 1, 5, 0, '9RAJRLQ', '2025-11-15 12:27:05', '2025-11-15 12:30:59'),
(55, 1, 5, 0, 'QPD67FC', '2025-11-15 12:31:08', '2025-11-15 12:41:49'),
(56, 12, 20, 1, 'OZ4TQZ5', '2025-11-26 12:21:32', '2025-11-26 12:21:32');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `created_at`, `updated_at`) VALUES
(1, 'kk', 'kundananjisimukondak@gmail.com', 'kkk', 'kkkkkk', '2025-11-04 09:19:05', '2025-11-04 09:19:05'),
(2, 'kk', 'kundananjisimukondak@gmail.com', 'kkk', 'kkkkkk', '2025-11-04 09:27:55', '2025-11-04 09:27:55'),
(3, 'hhhjhib', 'kundananjisimukonda@gmail.com', 'jibhuh', 'sjhqduib', '2025-11-04 09:35:23', '2025-11-04 09:35:23');

-- --------------------------------------------------------

--
-- Table structure for table `contact_replies`
--

CREATE TABLE `contact_replies` (
  `id` int(11) UNSIGNED NOT NULL,
  `message_id` int(11) UNSIGNED NOT NULL,
  `responder_id` int(11) UNSIGNED NOT NULL,
  `reply_message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `course_code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive','archived') NOT NULL DEFAULT 'active',
  `class_schedule` varchar(255) DEFAULT NULL,
  `programme_id` int(11) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `name`, `course_code`, `description`, `status`, `class_schedule`, `programme_id`, `created_at`, `updated_at`) VALUES
(1, 'CYBER LAW', 'ICT 413', 'This course provides an introduction to the legal frameworks and ethical issues that govern digital activities. Students will explore key topics such as data privacy and protection, intellectual property rights in the digital age, electronic commerce regulations, and the laws surrounding cybercrimes like hacking and identity theft. The course also examines the legal implications of emerging technologies and the role of law in creating a safe and secure online environment.', 'active', 'Mondays and Wednesdays 11:00 AM -12:00 PM for both days.', 1, '2025-08-18 06:30:30', '2025-08-18 06:31:50'),
(3, 'Bank Law', 'BF 411', 'kkkk', 'active', 'monday', 3, '2025-11-09 23:13:29', '2025-11-09 23:13:29'),
(9, 'E-COMMERCE', 'ICT 411', 'This course introduces students to the concepts, technologies, and business models that power electronic commerce. Topics include online retailing, digital marketing, payment systems, cybersecurity in e-commerce, and legal considerations for online business operations. Students will also explore strategies for designing and managing e-commerce platforms.', 'active', 'Mondays and Wednesdays 08:00 AM - 09:00 AM for both days.', 1, '2025-11-11 22:06:50', '2025-11-11 22:06:50'),
(10, 'INFORMATION SECURITY', 'ICT 412', 'This course focuses on protecting information assets by identifying vulnerabilities, assessing threats, and implementing security controls. Key topics include cryptography, risk management, security policies, firewalls, intrusion detection systems, and incident response. Students will gain hands-on experience in securing networked environments.', 'active', 'Tuesdays and Thursdays 09:00 AM - 10:30 AM for both days.', 1, '2025-11-11 22:06:50', '2025-11-11 22:06:50'),
(11, 'INFORMATION SYSTEMS MANAGEMENT', 'ICT 414', 'This course explores the strategic role of information systems in organizations. Students learn how to manage IT resources, align technology with business objectives, and analyze system performance. Topics include enterprise systems, IT governance, data-driven decision making, and emerging trends in digital transformation.', 'active', 'Tuesdays and Thursdays 01:00 PM - 02:30 PM for both days.', 1, '2025-11-11 22:06:50', '2025-11-11 22:06:50'),
(12, 'FIBER OPTICS', 'ICT 415', 'This course introduces the principles and applications of fiber optic communication. It covers the fundamentals of light transmission, optical components, fiber types, splicing techniques, and testing procedures. Students gain hands-on experience in designing, installing, and maintaining fiber optic networks for modern communication systems.', 'active', 'Fridays 09:00 AM - 12:00 PM.', 1, '2025-11-11 22:06:50', '2025-11-11 22:06:50');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) UNSIGNED NOT NULL,
  `student_id` int(11) UNSIGNED NOT NULL,
  `course_id` int(11) UNSIGNED NOT NULL,
  `enrollment_status` enum('active','withdrawn','completed') NOT NULL DEFAULT 'active',
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `course_id`, `enrollment_status`, `enrolled_at`) VALUES
(2, 3, 1, 'active', '2025-08-18 06:52:12'),
(3, 6, 1, 'active', '2025-08-21 12:09:25'),
(8, 3, 9, 'active', '2025-11-11 22:18:00'),
(9, 3, 10, 'active', '2025-11-11 22:18:00'),
(10, 3, 11, 'active', '2025-11-11 22:18:00'),
(11, 3, 12, 'active', '2025-11-11 22:18:00'),
(12, 6, 9, 'active', '2025-11-11 22:18:18'),
(13, 6, 10, 'active', '2025-11-11 22:18:18'),
(14, 6, 11, 'active', '2025-11-11 22:18:18'),
(15, 6, 12, 'active', '2025-11-11 22:18:18'),
(16, 19, 1, 'active', '2025-11-12 12:43:56'),
(17, 19, 9, 'active', '2025-11-12 12:43:56'),
(18, 19, 10, 'active', '2025-11-12 12:43:56'),
(19, 19, 11, 'active', '2025-11-12 12:43:56'),
(20, 19, 12, 'active', '2025-11-12 12:43:56');

-- --------------------------------------------------------

--
-- Table structure for table `lecturer_courses`
--

CREATE TABLE `lecturer_courses` (
  `lecturer_id` int(11) UNSIGNED NOT NULL,
  `course_id` int(11) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lecturer_courses`
--

INSERT INTO `lecturer_courses` (`lecturer_id`, `course_id`, `created_at`) VALUES
(5, 1, '2025-08-18 14:45:58'),
(5, 3, '2025-11-09 23:13:29'),
(20, 10, '2025-11-26 09:29:32'),
(20, 12, '2025-11-26 09:29:32');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `programmes`
--

CREATE TABLE `programmes` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(50) NOT NULL,
  `department` varchar(255) NOT NULL,
  `duration` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `programmes`
--

INSERT INTO `programmes` (`id`, `name`, `code`, `department`, `duration`, `created_at`, `updated_at`) VALUES
(1, 'Bachelor of Science in ICT', 'BICT', 'ICT', 4, '2025-08-18 06:01:38', '2025-08-18 06:06:50'),
(3, 'Bachelor oF Arts in Banking & Finance', 'BF', 'Humanities', 4, '2025-11-09 23:09:58', '2025-11-09 23:11:39');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `user_id` int(11) UNSIGNED NOT NULL,
  `student_number` varchar(255) NOT NULL,
  `programme_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`user_id`, `student_number`, `programme_id`) VALUES
(3, 'ST001', 1),
(6, 'ST002', 1),
(19, 'ST0020', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','lecturer','admin') NOT NULL DEFAULT 'student',
  `phone` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `status`, `created_at`) VALUES
(1, 'Admin Kundananji', 'admin@edutrack.com', '$2y$10$Qui6EqH8yAZJbRzKh2CL/O4SNfGed5IU.hg5qUyzMxbm1GJ3nCypO', 'admin', NULL, 'active', '2025-08-18 14:45:58'),
(3, 'Kundananji Simukonda', 'kaka@edutrack.com', '$2y$10$FnpetGd9xnDuay2SfFGdxOlb8Ns1oMtgkEelNKKWm6O6E.Su00AIq', 'student', NULL, 'active', '2025-08-18 14:45:58'),
(5, 'Kundananji Simukonda', 'kundananjisimukonda@gmail.com', '$2y$10$mucauCVgidcha/5J./PDLuecQjrV5STjZR07UvoGWdSfHC15Sgv5m', 'lecturer', '+260967591264', 'active', '2025-08-18 14:45:58'),
(6, 'kaka', 'kunda@edutrack.com', '$2y$10$n2N14Ot1EsawkHUBH9J0E.djJ37PMIPW0A6fQEjnBYnzwRpOZ/adC', 'student', NULL, 'active', '2025-08-18 19:11:33'),
(19, 'Verah Muchinga', 'verah@gmail.com', '$2y$10$ZTY2y9EL5gBykU76wQHquuDUQE3f6MoHLe3V4/bUwdaJRlAZgdZ8S', 'student', NULL, 'active', '2025-11-12 12:42:47'),
(20, 'Alfred Chibwinja', 'alfredchibwinja@gmail.com', '$2y$10$sbi6Bt8pE2bhHsEPROTb/eFlnDJvivbR1vqqsg9cSP36XRm6TVRJW', 'lecturer', '+260967591264', 'active', '2025-11-26 09:29:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `announcement_students`
--
ALTER TABLE `announcement_students`
  ADD PRIMARY KEY (`announcement_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_student_unique` (`session_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `attendance_sessions`
--
ALTER TABLE `attendance_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_code` (`session_code`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `lecturer_id` (`lecturer_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `contact_replies`
--
ALTER TABLE `contact_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_id` (`message_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_code` (`course_code`),
  ADD KEY `programme_id` (`programme_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `lecturer_courses`
--
ALTER TABLE `lecturer_courses`
  ADD PRIMARY KEY (`lecturer_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `programmes`
--
ALTER TABLE `programmes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `student_number` (`student_number`),
  ADD KEY `programme_id` (`programme_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `attendance_records`
--
ALTER TABLE `attendance_records`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `attendance_sessions`
--
ALTER TABLE `attendance_sessions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `contact_replies`
--
ALTER TABLE `contact_replies`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `programmes`
--
ALTER TABLE `programmes`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `announcement_students`
--
ALTER TABLE `announcement_students`
  ADD CONSTRAINT `announcement_students_ibfk_1` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `announcement_students_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD CONSTRAINT `attendance_records_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `attendance_sessions` (`id`),
  ADD CONSTRAINT `attendance_records_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `attendance_sessions`
--
ALTER TABLE `attendance_sessions`
  ADD CONSTRAINT `attendance_sessions_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `attendance_sessions_ibfk_2` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `contact_replies`
--
ALTER TABLE `contact_replies`
  ADD CONSTRAINT `contact_replies_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `contact_messages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`programme_id`) REFERENCES `programmes` (`id`);

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lecturer_courses`
--
ALTER TABLE `lecturer_courses`
  ADD CONSTRAINT `lecturer_courses_ibfk_1` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lecturer_courses_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `students_ibfk_2` FOREIGN KEY (`programme_id`) REFERENCES `programmes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
