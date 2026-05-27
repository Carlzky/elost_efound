-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 27, 2026 at 11:12 AM
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

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
