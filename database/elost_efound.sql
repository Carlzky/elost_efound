-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 27, 2026 at 11:38 AM
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
-- Database: `elost_efound`
--

-- --------------------------------------------------------

--
-- Table structure for table `claims`
--

CREATE TABLE `claims` (
  `claim_id` int(11) NOT NULL,
  `found_item_id` int(11) NOT NULL,
  `claimant_user_id` int(11) NOT NULL,
  `claimant_name` varchar(255) DEFAULT NULL,
  `claimant_contact` varchar(50) DEFAULT NULL,
  `proof_image` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `claim_status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `claimed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `found_items`
--

CREATE TABLE `found_items` (
  `found_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `location_found` varchar(255) NOT NULL,
  `date_found` date NOT NULL,
  `time_found` time DEFAULT NULL,
  `description` text DEFAULT NULL,
  `item_image` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `status` enum('Available','Claim Requested','Claimed') DEFAULT 'Available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `found_reports`
--

CREATE TABLE `found_reports` (
  `report_id` int(11) NOT NULL,
  `lost_item_id` int(11) NOT NULL,
  `finder_user_id` int(11) NOT NULL,
  `finder_name` varchar(255) DEFAULT NULL,
  `finder_contact` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `proof_image` varchar(255) DEFAULT NULL,
  `report_status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `found_reports`
--

INSERT INTO `found_reports` (`report_id`, `lost_item_id`, `finder_user_id`, `finder_name`, `finder_contact`, `message`, `proof_image`, `report_status`, `created_at`) VALUES
(1, 2, 9, 'Hoshino', 'asd', 'asda', NULL, 'Approved', '2026-05-27 08:12:48');

-- --------------------------------------------------------

--
-- Table structure for table `item_matches`
--

CREATE TABLE `item_matches` (
  `match_id` int(11) NOT NULL,
  `lost_item_id` int(11) NOT NULL,
  `found_item_id` int(11) NOT NULL,
  `match_percentage` int(11) DEFAULT NULL,
  `matched_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lost_items`
--

CREATE TABLE `lost_items` (
  `lost_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `location_lost` varchar(255) NOT NULL,
  `date_lost` date NOT NULL,
  `time_lost` time DEFAULT NULL,
  `description` text DEFAULT NULL,
  `item_image` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `status` enum('Pending','Matched','Claimed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lost_items`
--

INSERT INTO `lost_items` (`lost_id`, `user_id`, `item_name`, `category`, `location_lost`, `date_lost`, `time_lost`, `description`, `item_image`, `contact_number`, `status`, `created_at`) VALUES
(2, 9, '33', 'bags', '33', '2311-12-31', NULL, 'aqdaw', 'uploads/1779869524_AOSPromiseatsunset2.png', NULL, 'Pending', '2026-05-27 08:12:04');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `claim_id` int(11) DEFAULT NULL,
  `report_id` int(11) DEFAULT NULL,
  `message_type` enum('normal','claim','found_report') DEFAULT 'normal',
  `message_text` text NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `sender_id`, `receiver_id`, `item_id`, `claim_id`, `report_id`, `message_type`, `message_text`, `sent_at`) VALUES
(1, 9, 9, 2, NULL, 1, 'found_report', 'Found report submitted for item ID: 2', '2026-05-27 08:12:48'),
(2, 9, 9, 2, NULL, NULL, 'normal', 'weh', '2026-05-27 08:13:20'),
(3, 9, 9, 2, NULL, NULL, 'normal', 'omsoms', '2026-05-27 08:13:25'),
(4, 9, 9, 2, NULL, NULL, 'normal', 'aaa', '2026-05-27 08:13:27'),
(5, 9, 9, 2, NULL, NULL, 'normal', 'asasd', '2026-05-27 08:13:31'),
(6, 9, 9, 2, NULL, NULL, 'normal', 'asda', '2026-05-27 08:14:14'),
(7, 9, 9, 2, NULL, 1, '', 'Your found item report was APPROVED.', '2026-05-27 08:14:41');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `notification_text` text NOT NULL,
  `notification_type` varchar(100) DEFAULT NULL,
  `is_read` enum('Yes','No') DEFAULT 'No',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `notification_text`, `notification_type`, `is_read`, `created_at`) VALUES
(1, 9, 'Someone reported they found your lost item.', 'found_report', 'No', '2026-05-27 08:12:48'),
(2, 9, 'Your found item report was approved.', 'found_report', 'No', '2026-05-27 08:14:41');

-- --------------------------------------------------------

--
-- Table structure for table `report_history`
--

CREATE TABLE `report_history` (
  `history_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `report_type` enum('Lost','Found') DEFAULT NULL,
  `report_id` int(11) NOT NULL,
  `action_done` varchar(255) DEFAULT NULL,
  `action_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `report_history`
--

INSERT INTO `report_history` (`history_id`, `user_id`, `report_type`, `report_id`, `action_done`, `action_date`) VALUES
(2, 9, 'Lost', 2, 'Reported a lost item: 33', '2026-05-27 08:12:04');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `security_question` varchar(255) DEFAULT NULL,
  `security_answer` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_image` varchar(255) DEFAULT NULL,
  `cvsu_email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `username`, `password`, `security_question`, `security_answer`, `created_at`, `profile_image`, `cvsu_email`) VALUES
(9, 'Sorasaki Hina', 'carlosjoseph.eudela@cvsu.edu.ph', 'Hinaa', '$2y$10$/YBtmB3amH86KdtU46ACzuH1bDvx3hzoixxassAmavQpqLrjC9MNG', 'What is your elementary school name?', '$2y$10$qlO7OWNd6Nmv2V4i76DrsOjNfOxyyTzpLDDP5No9SuDDHUCWE6eVm', '2026-05-27 07:12:31', 'assets/img/profile_9_1779869637.jpg', 'carlosjoseph.eudela@cvsu.edu.ph'),
(10, 'SoraHina TAKASHINO', 'carloseudela19@cvsu.edu.ph', 'yunna', '$2y$10$EEr056sdwTBb/TwRxEfQUu0NJ6zjdRIXLGodZAAoMEGOdTKMw5ajC', 'What is your elementary school name?', '$2y$10$d5WYJnkYM.6QSM3/uNRnF.6geyFOIPzXJZqw2aKrD2v4iOjO6b1/y', '2026-05-27 08:04:03', NULL, 'carloseudela19@cvsu.edu.ph');

-- --------------------------------------------------------

--
-- Table structure for table `user_tokens`
--

CREATE TABLE `user_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `selector` char(12) NOT NULL,
  `hashed_validator` char(64) NOT NULL,
  `expiry` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `claims`
--
ALTER TABLE `claims`
  ADD PRIMARY KEY (`claim_id`),
  ADD KEY `found_item_id` (`found_item_id`),
  ADD KEY `claimant_user_id` (`claimant_user_id`);

--
-- Indexes for table `found_items`
--
ALTER TABLE `found_items`
  ADD PRIMARY KEY (`found_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `found_reports`
--
ALTER TABLE `found_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `lost_item_id` (`lost_item_id`),
  ADD KEY `finder_user_id` (`finder_user_id`);

--
-- Indexes for table `item_matches`
--
ALTER TABLE `item_matches`
  ADD PRIMARY KEY (`match_id`),
  ADD KEY `lost_item_id` (`lost_item_id`),
  ADD KEY `found_item_id` (`found_item_id`);

--
-- Indexes for table `lost_items`
--
ALTER TABLE `lost_items`
  ADD PRIMARY KEY (`lost_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `idx_sender` (`sender_id`),
  ADD KEY `idx_receiver` (`receiver_id`),
  ADD KEY `idx_item` (`item_id`),
  ADD KEY `idx_claim` (`claim_id`),
  ADD KEY `idx_report` (`report_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `report_history`
--
ALTER TABLE `report_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `claims`
--
ALTER TABLE `claims`
  MODIFY `claim_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `found_items`
--
ALTER TABLE `found_items`
  MODIFY `found_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `found_reports`
--
ALTER TABLE `found_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `item_matches`
--
ALTER TABLE `item_matches`
  MODIFY `match_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lost_items`
--
ALTER TABLE `lost_items`
  MODIFY `lost_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `report_history`
--
ALTER TABLE `report_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `user_tokens`
--
ALTER TABLE `user_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `claims`
--
ALTER TABLE `claims`
  ADD CONSTRAINT `claims_ibfk_1` FOREIGN KEY (`found_item_id`) REFERENCES `found_items` (`found_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `claims_ibfk_2` FOREIGN KEY (`claimant_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `found_items`
--
ALTER TABLE `found_items`
  ADD CONSTRAINT `found_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `found_reports`
--
ALTER TABLE `found_reports`
  ADD CONSTRAINT `found_reports_ibfk_1` FOREIGN KEY (`lost_item_id`) REFERENCES `lost_items` (`lost_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `found_reports_ibfk_2` FOREIGN KEY (`finder_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `item_matches`
--
ALTER TABLE `item_matches`
  ADD CONSTRAINT `item_matches_ibfk_1` FOREIGN KEY (`lost_item_id`) REFERENCES `lost_items` (`lost_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `item_matches_ibfk_2` FOREIGN KEY (`found_item_id`) REFERENCES `found_items` (`found_id`) ON DELETE CASCADE;

--
-- Constraints for table `lost_items`
--
ALTER TABLE `lost_items`
  ADD CONSTRAINT `lost_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`claim_id`) REFERENCES `claims` (`claim_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_4` FOREIGN KEY (`report_id`) REFERENCES `found_reports` (`report_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `report_history`
--
ALTER TABLE `report_history`
  ADD CONSTRAINT `report_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD CONSTRAINT `user_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
