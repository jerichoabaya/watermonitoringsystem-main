-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql100.infinityfree.com
-- Generation Time: Dec 01, 2025 at 05:32 AM
-- Server version: 10.6.22-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_40530460_water_monitoring`
--

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `device_id` int(11) NOT NULL,
  `device_sensor_id` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `device_name` varchar(100) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `device_type` enum('personal','station') NOT NULL DEFAULT 'station',
  `lgu_location_id` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `registered_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `devices`
--

INSERT INTO `devices` (`device_id`, `device_sensor_id`, `created_at`, `device_name`, `owner_id`, `device_type`, `lgu_location_id`, `location`, `registered_by`) VALUES
(1, 'WQMS-DEV-00123', '2025-11-05 11:49:18', 'Adrian\'s Water Refilling Station', 9, 'station', NULL, 'Anao, Cabagan, Isabela', NULL),
(2, 'AHSD-JWD-14825', '2025-11-05 11:49:18', 'Jericho\'s Water Market', 9, 'station', NULL, 'Cubag, Cabagan, Isabela', NULL),
(3, 'SAKX-SFG-34632', '2025-11-05 11:49:18', 'Juan\'s Water Station', 9, 'station', NULL, 'Catabayungan, Cabagan, Isabela', NULL),
(4, 'SKLHN-AHU-18267', '2025-11-05 11:49:18', 'Municipal Water Filtration', NULL, 'station', NULL, 'Catabayungan, Cabagan, Isabela', NULL),
(5, 'KSND-HJG-47478', '2025-11-05 11:49:18', 'Home Water Filtration', 9, 'station', NULL, 'Catabayungan, Cabagan, Isabela', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lgu_locations`
--

CREATE TABLE `lgu_locations` (
  `location_id` int(11) NOT NULL,
  `location_name` varchar(100) NOT NULL,
  `region` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lgu_locations`
--

INSERT INTO `lgu_locations` (`location_id`, `location_name`, `region`) VALUES
(1, 'CABAGAN, ISABELA', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `refilling_stations`
--

CREATE TABLE `refilling_stations` (
  `station_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('super_admin','lgu_menro','refilling_station_owner','personal_user') DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `lgu_location` varchar(255) DEFAULT NULL,
  `owner_name` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contact_no` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `device_sensor_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `refilling_stations`
--

INSERT INTO `refilling_stations` (`station_id`, `user_id`, `role`, `name`, `location`, `lgu_location`, `owner_name`, `address`, `contact_no`, `email`, `device_sensor_id`) VALUES
(9, 0, 'refilling_station_owner', 'Adrian\'s Water Refilling Station', 'Anao, Cabagan, Isabela', 'CABAGAN, ISABELA', 'Adrian Orbong', NULL, NULL, NULL, 'WQMS-DEV-00123'),
(11, 0, 'refilling_station_owner', 'Jericho\'s Water Market', 'Cubag, Cabagan, Isabela', 'CABAGAN, ISABELA', 'Jericho Abaya', 'Tumauini, Isabela', '03927843287', 'jericho@gmail.com', 'AHSD-JWD-14825'),
(13, 0, 'refilling_station_owner', 'Juan\'s Water Station', 'Catabayungan, Cabagan, Isabela', 'CABAGAN, ISABELA', 'Juan Cruz', NULL, NULL, NULL, 'SAKX-SFG-34632'),
(19, 0, 'refilling_station_owner', 'Municipal Water Filtration', 'Catabayungan, Cabagan, Isabela', 'TUGUEGARAO, CAGAYAN', NULL, NULL, NULL, NULL, 'SKLHN-AHU-18267'),
(27, 9, 'refilling_station_owner', '3M Water Station', 'Catabayungan, Cabagan, Isabela', 'CABAGAN, ISABELA', 'Marcus M. Montenegro', NULL, NULL, NULL, 'KSND-HJG-47478'),
(29, 14, 'refilling_station_owner', 'Water Market', 'Catabayungan, Cabagan, Isabela', 'CABAGAN, ISABELA', 'Adrian Orbong', 'San Pablo, Isabela', '098764214657', 'adrianorbong@gmail.com', 'ZJGP-JWD-14232'),
(31, 14, 'personal_user', 'House Filtration System', 'Calanigan, Santo Tomas, Isabela', NULL, 'Jonnie Escario', 'Calanigan, Santo Tomas, Isabela', '09273615287', 'jonniehousefiltration@gmail.com', 'TSFS-WEV-04123'),
(35, 14, 'refilling_station_owner', 'abc water station', 'Catabayungan, Cabagan, Isabela', 'CABAGAN, ISABELA', 'abc', 'Catabayungan, Cabagan, Isabela', '09765432184', 'abc@gmail.com', 'ABC123'),
(36, 14, 'personal_user', 'My Home Filtraion', 'Catabayungan, Cabagan, Isabela', 'CABAGAN, ISABELA', 'xyz', 'Catabayungan, Cabagan, Isabela', '09765432184', 'xyz@gmail.com', 'XYZ123'),
(40, 14, 'refilling_station_owner', 'Job Water Market', 'Anao, Cabagan, Isabela', 'CABAGAN, ISABELA', 'Job Cruz', 'Catabayungan, Cabagan, Isabela', '09765432184', 'jobcruz@gmail.com', 'JOB123'),
(41, 14, 'refilling_station_owner', 'CAPSTONE', 'Catabayungan, Cabagan, Isabela', 'CABAGAN, ISABELA', '2J&A', 'Catabayungan, Cabagan, Isabela', '09458765569', 'jonnie.g.escario@isu.edu.ph', 'ISUIT-WQTAMS-0001');

-- --------------------------------------------------------

--
-- Table structure for table `station_autotest_settings`
--

CREATE TABLE `station_autotest_settings` (
  `station_id` int(11) NOT NULL,
  `mode` varchar(10) NOT NULL,
  `interval_hours` int(11) DEFAULT NULL,
  `interval_days` int(11) DEFAULT NULL,
  `interval_months` int(11) DEFAULT NULL,
  `day_of_month` int(11) DEFAULT NULL,
  `time_of_day` time DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `fullname` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `number` varchar(255) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `role` enum('super_admin','lgu_menro','refilling_station_owner','personal_user') DEFAULT 'personal_user',
  `lgu_location` varchar(255) DEFAULT NULL,
  `lgu_location_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `fullname`, `password`, `email`, `number`, `address`, `profile_pic`, `role`, `lgu_location`, `lgu_location_id`) VALUES
(1, 'admin', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', '', '', NULL, NULL, 'personal_user', NULL, NULL),
(4, 'abcd', '$2y$10$uRZlIAkr76rCQDWxr1Zn6OaDg.hCN9TPB2WN9eAJloq1bWv046Y/y', 'abcd@gmail.com', '09123456789', NULL, NULL, 'personal_user', NULL, NULL),
(9, 'jonnie escario', '$2y$10$EQQfQZV4l1YGzzjTNG/2deeDWZvmaqu8iqC/omWn/vVOT.rBzaUPG', 'jonnieescario@gmail.com', '09887654321', NULL, '/water-quality-monitoring-system/uploads/1758120649_9b6059361f193679a0862c6f89778d9a.jpg', 'personal_user', NULL, NULL),
(10, 'jericho abaya', '$2y$10$3lFLC5haWihw0oLpW1GjX.g7sE4aRWwVkeN27Er7yyBQd.whdSFSO', 'jericho_abaya16@yahoo.com', '09163266837', NULL, NULL, 'personal_user', NULL, NULL),
(11, 'Juan Cruz', '$2y$10$vqXYzjtodE1ZMwGQrW3DXOAunXV8aw4J0poSeNV6iMuYnXkfSV4Oe', 'juancruz@gmail.com', '09192837465', NULL, NULL, 'personal_user', NULL, NULL),
(14, 'H2O', '$2y$10$.vHsjzoHJj4iRx5CNg/lqusbn7il.U6yGJhpHOKZx8hjyynFlDwRi', 'jerichoabaya9@gmail.com', '09915512610', 'Catabayungan, Cabagan, Isabela', '/uploads/1764549818_4_Image.jpg', 'super_admin', NULL, NULL),
(22, 'LGU MENRO CABAGAN', '$2y$10$dHTwCOjHoE6emAsoXBNIquAqc4SWEReiosVHnTbiWl21hGmv0Uet.', 'lgumenrocabagan@gmail.com', '09123456789', NULL, NULL, 'lgu_menro', 'CABAGAN, ISABELA', 1),
(23, 'abc', '$2y$10$cgIuXVjACSyENBPtv2luAObUdmHp./GIfyj1yhr4IcgIOB0wMygp6', 'abc@gmail.com', '09163266837', 'Catabayungan, Cabagan, Isabela', '/uploads/1764549719_c7d51b69e121c9b9dc17628c1ab5651b.jpg', 'refilling_station_owner', NULL, NULL),
(24, 'xyz', '$2y$10$QKAumLI0UN9WSP9heHF95eJEyQmD3j2nLNBR3I0y62XRYMiX7LPdS', 'xyz@gmail.com', '09163266837', 'Catabayungan, Cabagan, Isabela', '/uploads/1764549775_9b6059361f193679a0862c6f89778d9a.jpg', 'personal_user', NULL, NULL),
(28, '2J&A', '$2y$10$M32Nvnnro4EdU2FYfXU8Ru2hBxE6Md3n4M7rBRTu3GrRj/SRVwkdu', 'jonnie.g.escario@isu.edu.ph', '09458765569', 'Catabayungan, Cabagan, Isabela', NULL, 'refilling_station_owner', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_stations`
--

CREATE TABLE `user_stations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `station_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_stations`
--

INSERT INTO `user_stations` (`id`, `user_id`, `station_id`) VALUES
(1, 9, 9),
(2, 9, 11),
(5, 9, 27),
(8, 9, 13),
(10, 11, 27),
(20, 23, 9),
(21, 23, 35),
(25, 23, 27),
(26, 24, 36),
(27, 28, 41);

-- --------------------------------------------------------

--
-- Table structure for table `water_data`
--

CREATE TABLE `water_data` (
  `waterdata_id` int(11) NOT NULL,
  `station_id` int(11) DEFAULT NULL,
  `color` float DEFAULT NULL,
  `ph_level` float DEFAULT NULL,
  `turbidity` float DEFAULT NULL,
  `tds` float DEFAULT NULL,
  `residual_chlorine` float DEFAULT NULL,
  `lead` float DEFAULT NULL,
  `cadmium` float DEFAULT NULL,
  `arsenic` float DEFAULT NULL,
  `nitrate` float DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `water_data`
--

INSERT INTO `water_data` (`waterdata_id`, `station_id`, `color`, `ph_level`, `turbidity`, `tds`, `residual_chlorine`, `lead`, `cadmium`, `arsenic`, `nitrate`, `timestamp`) VALUES
(4, 29, 4, 5.9, 1, 7, 0.3, 0.008, 0.002, 0.004, 7, '2025-09-17 09:00:00'),
(8, 27, 4.9, 6.3, 0.7, 5, 0.4, 0.009, 0.001, 0.004, 9, '2025-09-17 09:00:00'),
(9, 35, 10, 5.9, 0.9, 8.5, 0.5, 0.008, 0.002, 0.006, 7, '2025-09-17 09:00:00'),
(14, 11, 5, 6.7, 0.8, 8, 0.5, 0.008, 0.002, 0.005, 10, '2025-09-17 09:00:00'),
(15, 13, 6, 7.1, 1, 6, 0.4, 0.008, 0.002, 0.006, 12, '2025-09-17 09:00:00'),
(17, 9, 5.5, 6, 6, 12, 0.6, 0.008, 0.003, 0.009, 11, '2025-09-17 09:00:00'),
(18, 40, 12, 6.8, 6.5, 14, 0.6, 0.02, 0.003, 0.009, 11, '2025-09-17 09:00:00'),
(19, 19, 5, 6.5, 2, 6, 0.8, 0.005, 0.0005, 0.005, 35, '2025-09-17 09:00:00'),
(20, 36, 8.5, 5.8, 2.5, 8, NULL, 0.005, NULL, NULL, NULL, '2025-09-17 09:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`device_id`),
  ADD UNIQUE KEY `device_sensor_id` (`device_sensor_id`),
  ADD UNIQUE KEY `uniq_device_sensor_id` (`device_sensor_id`),
  ADD KEY `registered_by` (`registered_by`);

--
-- Indexes for table `lgu_locations`
--
ALTER TABLE `lgu_locations`
  ADD PRIMARY KEY (`location_id`);

--
-- Indexes for table `refilling_stations`
--
ALTER TABLE `refilling_stations`
  ADD PRIMARY KEY (`station_id`),
  ADD UNIQUE KEY `device_sensor_id` (`device_sensor_id`);

--
-- Indexes for table `station_autotest_settings`
--
ALTER TABLE `station_autotest_settings`
  ADD PRIMARY KEY (`station_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `lgu_location_id` (`lgu_location_id`);

--
-- Indexes for table `user_stations`
--
ALTER TABLE `user_stations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user` (`user_id`),
  ADD KEY `fk_station` (`station_id`);

--
-- Indexes for table `water_data`
--
ALTER TABLE `water_data`
  ADD PRIMARY KEY (`waterdata_id`),
  ADD KEY `fk_waterdata_station` (`station_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `device_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `lgu_locations`
--
ALTER TABLE `lgu_locations`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `refilling_stations`
--
ALTER TABLE `refilling_stations`
  MODIFY `station_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `user_stations`
--
ALTER TABLE `user_stations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `water_data`
--
ALTER TABLE `water_data`
  MODIFY `waterdata_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `devices`
--
ALTER TABLE `devices`
  ADD CONSTRAINT `devices_ibfk_1` FOREIGN KEY (`registered_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`lgu_location_id`) REFERENCES `lgu_locations` (`location_id`) ON DELETE SET NULL;

--
-- Constraints for table `user_stations`
--
ALTER TABLE `user_stations`
  ADD CONSTRAINT `fk_station` FOREIGN KEY (`station_id`) REFERENCES `refilling_stations` (`station_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `water_data`
--
ALTER TABLE `water_data`
  ADD CONSTRAINT `fk_waterdata_station` FOREIGN KEY (`station_id`) REFERENCES `refilling_stations` (`station_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
