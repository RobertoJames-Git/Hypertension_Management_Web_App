-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 23, 2025 at 07:30 PM
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
-- Database: `hypmonitor`
--

-- --------------------------------------------------------

--
-- Table structure for table `request`
--

CREATE TABLE `request` (
  `request_id` int(11) NOT NULL,
  `sender_userid` int(11) NOT NULL,
  `sender_username` varchar(50) NOT NULL,
  `recipient_userid` int(11) NOT NULL,
  `recipient_username` varchar(50) NOT NULL,
  `request_status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `request_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `request`
--

INSERT INTO `request` (`request_id`, `sender_userid`, `sender_username`, `recipient_userid`, `recipient_username`, `request_status`, `request_date`) VALUES
(31, 10, 'Fre_Lew10', 7, 'Wil_Sam7', 'accepted', '2025-03-23 14:37:47'),
(32, 2, 'San_Ros2', 10, 'Fre_Lew10', 'accepted', '2025-03-23 18:12:03'),
(41, 11, 'Ada_Ros11', 15, 'Dia_Pot15', 'pending', '2025-04-17 17:41:29'),
(49, 2, 'San_Ros2', 1, 'Dav_Rob1', 'pending', '2025-04-18 19:41:19'),
(50, 1, 'Dav_Rob1', 14, 'Fre_Smi14', 'accepted', '2025-04-18 20:38:36'),
(54, 14, 'Fre_Smi14', 15, 'Dia_Pot15', 'accepted', '2025-04-21 19:28:05'),
(55, 7, 'Wil_Sam7', 15, 'Dia_Pot15', 'pending', '2025-04-22 13:11:47'),
(58, 16, 'Kay_Jac16', 1, 'Dav_Rob1', 'accepted', '2025-04-23 11:26:30'),
(62, 1, 'Dav_Rob1', 8, 'Nat_Dre8', 'pending', '2025-04-23 11:54:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `request`
--
ALTER TABLE `request`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `sender_userid` (`sender_userid`,`sender_username`),
  ADD KEY `recipient_userid` (`recipient_userid`,`recipient_username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `request`
--
ALTER TABLE `request`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `request`
--
ALTER TABLE `request`
  ADD CONSTRAINT `request_ibfk_1` FOREIGN KEY (`sender_userid`,`sender_username`) REFERENCES `web_users` (`userID`, `username`),
  ADD CONSTRAINT `request_ibfk_2` FOREIGN KEY (`recipient_userid`,`recipient_username`) REFERENCES `web_users` (`userID`, `username`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
