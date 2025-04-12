-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 12, 2025 at 07:54 PM
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
-- Table structure for table `web_users`
--

CREATE TABLE `web_users` (
  `userID` int(11) NOT NULL,
  `username` varchar(10) NOT NULL,
  `fname` varchar(50) DEFAULT NULL,
  `lname` varchar(50) DEFAULT NULL,
  `gender` enum('male','female','other','rather not say') DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `token` varchar(100) DEFAULT NULL,
  `token_expiration` datetime DEFAULT NULL,
  `account_status` enum('pending','active') DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `phone_number` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `web_users`
--

INSERT INTO `web_users` (`userID`, `username`, `fname`, `lname`, `gender`, `dob`, `email`, `token`, `token_expiration`, `account_status`, `password`, `phone_number`) VALUES
(1, 'Dav_Rob1', 'Dave', 'Robinson', 'male', '2006-03-12', 'daveRob@gmail.com', 'f34c57e2072afbf409b21c2fb7328a5adb7f2025d8a73035665e41cfeab0a2da', '2025-03-07 07:08:36', 'active', '$2y$10$wgmT0s1sR6LyllZDPD/Siu8ahaYzjCKXDCXUUmm6z1EVNunp8tUJm', NULL),
(2, 'San_Ros2', 'Sandra', 'Rose', 'female', '1989-02-22', 'sanrose@mail.com', '3f1d4d7d234e9f83cf3c1e50717daa9664a01d27112ac6dc3a2565cfc1724f44', '2025-03-10 19:03:44', 'active', '$2y$10$R0m9LdDrYFfcezZe9Os2ZeAX2j8E5s2DQDjfVMFbLC9f3VA1ADE9y', NULL),
(7, 'Wil_Sam7', 'Will', 'Samwells', 'female', '1972-03-12', 'wsamwells@mail.com', 'cd857d45b3003103316f607b8de20c0e3b657febe1811726895ebce7493fa09b', '2025-03-13 07:58:14', 'active', '$2y$10$DvU7iwJAIW2NCzDTcpIxdOr1TmRNdGSxkkOP9oldDLasCZNnfBvXW', NULL),
(8, 'Nat_Dre8', 'Natasha', 'Drews', 'female', '2005-02-18', 'san_drw@mail.com', '1afe55ff22d624dd72eb5d6665285a3f2f36291524a7df104d5b08056c9e639c', '2025-03-20 12:49:58', 'active', '$2y$10$YWyutWNHEIZ3lFXLXOJSw.w6z8e9nN0Z1r7EEgWkbf4e6KBmLeD5a', NULL),
(10, 'Fre_Lew10', 'Fred', 'Lewis', 'male', '1992-02-27', 'flewis@gmail.com', 'c33fbe0fb13f2b167324e92f0dabf99dd8721af77fd299a3c782364dbe904060', '2025-03-23 13:51:27', 'active', '$2y$10$qrHzUbrkFS9012UMBdPZYe4WnaK9OoMAlPFdiOA1IZOLmjIZpN24m', NULL),
(11, 'Ada_Ros11', 'Adam', 'Rose', 'male', '2000-12-22', 'adamrose@mail.com', 'ff3411d5970b244ac0db44a2d6e6bd67b7f4d16d345e237c51d06d9d1a7ce219', '2025-04-12 11:18:35', 'active', '$2y$10$DYsDH1xqpSK7U421WiDtL.2kUOJK7wr9npXogR7bUBOLC2Ab0EojG', NULL),
(12, 'Gar_Fer12', 'Garville', 'Fergson', 'male', '1999-12-28', 'gferguson@mail.com', 'cdb02b0c7a4e8937aaa4271b2f10176bd2ad3cdb6c6e709cb74191b22e08bf6a', '2025-04-12 12:44:00', 'pending', '$2y$10$mgNBXak.SDAFTQTbno0y6eaUXbnsKUWeERGoBQ9MEdZjMH4wKlhBq', NULL),
(13, 'Rog_bla13', 'Roger', 'blake', 'male', '1987-09-21', 'rogbalke@mail.com', '3509f95ab502fbf1571ab794e200be5f2d10588ab51ce1a18e2078616508eab2', '2025-04-12 12:48:24', 'active', '$2y$10$pNtMAiZMh12uUELkdBAe3.xvp6U0RIW1k2Qtp5RotpqfaL65.zMHm', '8761234567');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `web_users`
--
ALTER TABLE `web_users`
  ADD PRIMARY KEY (`userID`,`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `token` (`token`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `web_users`
--
ALTER TABLE `web_users`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
