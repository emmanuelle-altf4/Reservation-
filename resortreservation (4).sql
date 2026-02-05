-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 05, 2026 at 10:17 PM
-- Server version: 12.0.2-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `resortreservation`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('SuperAdmin','Manager','Staff') DEFAULT 'Manager',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `username`, `password`, `email`, `full_name`, `role`, `created_at`, `updated_at`) VALUES
(1, 'faith_admin', 'akosiemman', 'faith@example.com', 'FAITH', 'SuperAdmin', '2026-02-01 12:02:41', '2026-02-05 11:04:51');

-- --------------------------------------------------------

--
-- Table structure for table `customerreservation`
--

CREATE TABLE `customerreservation` (
  `reservation_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `checkin_date` date NOT NULL,
  `checkout_date` date NOT NULL,
  `room_type` varchar(50) NOT NULL,
  `guests` int(11) NOT NULL,
  `status` enum('Pending','Confirmed','Cancelled') DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `otp_code` varchar(10) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `customerreservation`
--

INSERT INTO `customerreservation` (`reservation_id`, `user_id`, `customer_name`, `checkin_date`, `checkout_date`, `room_type`, `guests`, `status`, `created_at`, `updated_at`, `otp_code`, `otp_expiry`) VALUES
(1, 1, 'example', '0999-09-09', '1000-10-10', 'Deluxe Two-Bedroom Villa', 5, 'Confirmed', '2026-02-01 11:21:27', '2026-02-01 11:21:27', NULL, NULL),
(4, 2, 'Ako', '2026-02-14', '2026-02-15', 'Deluxe Two-Bedroom Villa', 5, 'Confirmed', '2026-02-01 13:23:51', '2026-02-01 13:23:51', NULL, NULL),
(5, 3, 'emman', '2026-06-06', '2026-06-07', 'Deluxe Two-Bedroom Villa', 5, 'Confirmed', '2026-02-01 13:31:45', '2026-02-01 13:31:45', NULL, NULL),
(6, 3, 'emman', '2026-07-07', '2026-07-08', 'Deluxe Two-Bedroom Villa', 5, 'Pending', '2026-02-01 13:34:34', '2026-02-01 13:34:34', '901414', '2026-02-01 14:44:34'),
(7, 3, 'emman', '2026-10-10', '2026-10-11', 'Deluxe Two-Bedroom Villa', 5, 'Pending', '2026-02-01 13:41:13', '2026-02-01 13:41:13', '285451', '2026-02-01 14:51:13'),
(8, 2, 'Ako', '2026-08-08', '2026-08-09', 'Standard Villa', 5, 'Pending', '2026-02-02 01:51:39', '2026-02-02 01:51:39', '844213', '2026-02-02 03:01:39'),
(9, 3, 'emman', '2027-02-11', '2027-02-12', 'Standard Villa', 5, 'Pending', '2026-02-02 02:00:34', '2026-02-02 02:00:34', '517849', '2026-02-02 03:10:34'),
(10, 1, 'example', '2026-12-12', '2026-12-13', 'Standard Villa', 5, 'Pending', '2026-02-02 08:41:37', '2026-02-02 08:41:37', '582933', '2026-02-02 09:51:37'),
(11, 3, 'emman', '2026-07-12', '2026-07-22', 'Standard Villa', 5, 'Pending', '2026-02-02 11:15:57', '2026-02-02 11:15:57', '724250', '2026-02-02 12:25:57'),
(12, 3, 'emman', '2026-03-15', '2026-03-20', 'Deluxe Two-Bedroom Villa', 5, 'Pending', '2026-02-02 11:18:39', '2026-02-02 11:18:39', '467815', '2026-02-02 12:28:39'),
(13, 3, 'emman', '2026-02-05', '2026-02-12', 'Deluxe Two-Bedroom Villa', 5, 'Pending', '2026-02-02 11:20:11', '2026-02-02 11:20:11', '161065', '2026-02-02 12:30:11'),
(14, 3, 'emman', '2029-02-02', '2029-02-02', 'Deluxe Two-Bedroom Villa', 5, 'Pending', '2026-02-02 11:21:46', '2026-02-02 11:21:46', '480598', '2026-02-02 12:31:46'),
(15, 3, 'emman', '2028-04-20', '2028-04-21', 'Deluxe Two-Bedroom Villa', 5, 'Pending', '2026-02-02 11:38:53', '2026-02-02 11:38:53', '933012', '2026-02-02 12:48:53'),
(16, 3, 'emman', '2027-01-01', '2027-01-02', 'Deluxe Two-Bedroom Villa', 5, 'Pending', '2026-02-02 11:44:50', '2026-02-02 11:44:50', '907027', '2026-02-02 12:54:50'),
(17, 3, 'emman', '2028-03-03', '2028-03-04', 'Standard Villa', 5, 'Pending', '2026-02-02 11:48:22', '2026-02-02 11:48:22', '394373', '2026-02-02 12:58:22'),
(18, 1, 'example', '2067-06-07', '2067-06-07', 'Deluxe Two-Bedroom Villa', 5, 'Pending', '2026-02-04 12:35:55', '2026-02-04 12:35:55', '392841', '2026-02-04 13:45:55'),
(19, 5, 'john', '2056-02-03', '2056-02-04', 'Standard Villa', 5, 'Pending', '2026-02-05 07:37:15', '2026-02-05 07:37:15', '619236', '2026-02-05 08:47:15'),
(20, 5, 'john', '2027-02-02', '2027-02-03', 'Standard Villa', 5, 'Pending', '2026-02-05 08:52:16', '2026-02-05 08:52:16', '517819', '2026-02-05 10:02:16'),
(21, 5, 'john', '2099-02-05', '2099-02-06', 'Standard Villa', 5, 'Pending', '2026-02-05 09:00:39', '2026-02-05 09:00:39', '120410', '2026-02-05 10:10:39'),
(22, 5, 'john', '2099-04-04', '2099-04-05', 'Deluxe Two-Bedroom Villa', 5, 'Pending', '2026-02-05 09:02:39', '2026-02-05 09:02:39', '766224', '2026-02-05 10:12:39'),
(23, 5, 'john', '2099-04-06', '2099-04-07', 'Deluxe Two-Bedroom Villa', 5, 'Pending', '2026-02-05 09:03:29', '2026-02-05 09:03:29', '200617', '2026-02-05 10:13:29'),
(24, 5, 'john', '2023-01-01', '2023-01-02', 'Deluxe Two-Bedroom Villa', 5, 'Pending', '2026-02-05 09:08:54', '2026-02-05 09:08:54', '476456', '2026-02-05 10:18:54'),
(25, 5, 'john', '2002-01-26', '2002-01-26', 'Deluxe Two-Bedroom Villa', 5, 'Pending', '2026-02-05 09:18:38', '2026-02-05 09:18:38', '673677', '2026-02-05 10:28:38'),
(26, 5, 'john', '2002-01-26', '2002-01-26', 'Deluxe Two-Bedroom Villa', 5, 'Pending', '2026-02-05 09:22:01', '2026-02-05 09:22:01', '045429', '2026-02-05 10:32:01'),
(27, 5, 'john', '2222-02-02', '2222-02-02', 'Deluxe Two-Bedroom Villa', 5, 'Pending', '2026-02-05 09:22:47', '2026-02-05 09:22:47', '578215', '2026-02-05 10:32:47'),
(28, 5, 'john', '2002-01-26', '2002-12-07', 'Deluxe Two-Bedroom Villa', 5, 'Pending', '2026-02-05 09:24:10', '2026-02-05 09:24:10', '022801', '2026-02-05 10:34:10'),
(29, 5, 'john', '2002-01-26', '2002-12-07', 'Deluxe Two-Bedroom Villa', 5, 'Pending', '2026-02-05 09:25:45', '2026-02-05 09:25:45', '781687', '2026-02-05 10:35:45');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `employee_number` int(11) DEFAULT NULL,
  `payment_bundle` enum('Full Payment','Deposit (50%)','Deposit (30%)','Balance Payment','Final Payment','Refund','Penalty Fee','Service Charge') NOT NULL,
  `mode_of_payment` enum('Cash','Credit Card','Debit Card','Bank Transfer','GCash','Maya','PayPal','Cash App','Other Digital Wallet') NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `amount_paid` decimal(12,2) NOT NULL,
  `previous_balance` decimal(12,2) DEFAULT 0.00,
  `new_balance` decimal(12,2) DEFAULT 0.00,
  `payment_date` datetime DEFAULT current_timestamp(),
  `due_date` date DEFAULT NULL,
  `payment_status` enum('Pending','Completed','Failed','Cancelled','Refunded') DEFAULT 'Pending',
  `transaction_reference` varchar(100) DEFAULT NULL,
  `proof_image` varchar(255) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `account_name` varchar(100) DEFAULT NULL,
  `account_number` varchar(50) DEFAULT NULL,
  `receipt_number` varchar(50) DEFAULT NULL,
  `payment_notes` text DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `reservation_id`, `user_id`, `employee_number`, `payment_bundle`, `mode_of_payment`, `total_amount`, `amount_paid`, `previous_balance`, `new_balance`, `payment_date`, `due_date`, `payment_status`, `transaction_reference`, `proof_image`, `bank_name`, `account_name`, `account_number`, `receipt_number`, `payment_notes`, `processed_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 'Full Payment', 'GCash', 15000.00, 15000.00, 0.00, 0.00, '2026-02-05 15:56:19', NULL, 'Completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-05 07:56:19', '2026-02-05 07:56:19'),
(2, 4, 2, NULL, 'Deposit (50%)', 'Bank Transfer', 25000.00, 12500.00, 0.00, 0.00, '2026-02-05 15:56:19', NULL, 'Completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-05 07:56:19', '2026-02-05 07:56:19'),
(3, 5, 3, NULL, 'Deposit (30%)', 'Cash', 20000.00, 6000.00, 0.00, 0.00, '2026-02-05 15:56:19', NULL, 'Completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-05 07:56:19', '2026-02-05 07:56:19'),
(4, 19, 5, NULL, 'Full Payment', 'GCash', 15000.00, 15000.00, 15000.00, 0.00, '2026-02-05 00:00:00', '2026-02-08', 'Completed', '22222', 'payment_proofs/payment_4_1770281188.PNG', NULL, NULL, NULL, NULL, '', NULL, '2026-02-05 08:36:42', '2026-02-05 08:46:28');

-- --------------------------------------------------------

--
-- Stand-in structure for view `payment_summary`
-- (See below for the actual view)
--
CREATE TABLE `payment_summary` (
`payment_id` int(11)
,`reservation_id` int(11)
,`user_id` int(11)
,`employee_number` int(11)
,`payment_bundle` enum('Full Payment','Deposit (50%)','Deposit (30%)','Balance Payment','Final Payment','Refund','Penalty Fee','Service Charge')
,`mode_of_payment` enum('Cash','Credit Card','Debit Card','Bank Transfer','GCash','Maya','PayPal','Cash App','Other Digital Wallet')
,`total_amount` decimal(12,2)
,`amount_paid` decimal(12,2)
,`previous_balance` decimal(12,2)
,`new_balance` decimal(12,2)
,`payment_date` datetime
,`due_date` date
,`payment_status` enum('Pending','Completed','Failed','Cancelled','Refunded')
,`transaction_reference` varchar(100)
,`bank_name` varchar(100)
,`account_name` varchar(100)
,`account_number` varchar(50)
,`receipt_number` varchar(50)
,`payment_notes` text
,`processed_by` int(11)
,`created_at` timestamp
,`updated_at` timestamp
,`customer_name` varchar(100)
,`customer_email` varchar(150)
,`checkin_date` date
,`checkout_date` date
,`room_type` varchar(50)
,`reservation_status` enum('Pending','Confirmed','Cancelled')
,`processed_by_name` varchar(100)
);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `employee_number` int(11) NOT NULL,
  `employee_name` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('Admin','Employee') DEFAULT 'Employee'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`employee_number`, `employee_name`, `password_hash`, `role`) VALUES
(1, 'ako', '$2y$10$TL6Vo6zkS5m2hRzCavQYke/ezY.vs6a5f.QRED.s0no3PPtfYKuSC', 'Admin'),
(3, 'Akolangto', '$2y$10$TL6Vo6zkS5m2hRzCavQYke/ezY.vs6a5f.QRED.s0no3PPtfYKuSC', 'Employee'),
(4, 'exampleaddparthree', '$2y$10$nTm6YAiWQRMQUA2rmm.0XevYiYh4WeJ.dni5jBCbgaYNMTBdlLhxm', 'Employee'),
(5, 'exampleaddparpor', '$2y$10$fP1STClVAIWhi3U.00uSdu66iHYdenTolU6WdxkA0fmk2NVH.PrTW', 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `customer_name`, `customer_email`, `password_hash`, `created_at`) VALUES
(1, 'example', 'example@gmail.com', '$2y$10$SNihsdx1ApU/RyPcy4QV/u4/p1YNmcux0cAYdopTXkRvj4RRYJMC6', '2026-02-01 10:01:38'),
(2, 'Ako', 'Ako@gmail.com', '$2y$10$RXXTUtDrUN072cPdiDGMsuEExQZMhB7ZppINvV2usE7YnwCM5RheS', '2026-02-01 10:13:54'),
(3, 'emman', 'faith2miyuki@gmail.com', '$2y$10$nOHVX2isxYNNexPxuzaA8.P.aVMjVV//LqZjJLOBoa3EmZLvbnsdC', '2026-02-01 13:24:28'),
(5, 'john', 'gfaith209@gmail.com', '$2y$10$Z5yQzV/yZcWXf7ThbaUJlubTsgLqEcJwqRwJfnwqsEUMX/iN45RwK', '2026-02-05 07:36:35');

-- --------------------------------------------------------

--
-- Structure for view `payment_summary`
--
DROP TABLE IF EXISTS `payment_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `payment_summary`  AS SELECT `p`.`payment_id` AS `payment_id`, `p`.`reservation_id` AS `reservation_id`, `p`.`user_id` AS `user_id`, `p`.`employee_number` AS `employee_number`, `p`.`payment_bundle` AS `payment_bundle`, `p`.`mode_of_payment` AS `mode_of_payment`, `p`.`total_amount` AS `total_amount`, `p`.`amount_paid` AS `amount_paid`, `p`.`previous_balance` AS `previous_balance`, `p`.`new_balance` AS `new_balance`, `p`.`payment_date` AS `payment_date`, `p`.`due_date` AS `due_date`, `p`.`payment_status` AS `payment_status`, `p`.`transaction_reference` AS `transaction_reference`, `p`.`bank_name` AS `bank_name`, `p`.`account_name` AS `account_name`, `p`.`account_number` AS `account_number`, `p`.`receipt_number` AS `receipt_number`, `p`.`payment_notes` AS `payment_notes`, `p`.`processed_by` AS `processed_by`, `p`.`created_at` AS `created_at`, `p`.`updated_at` AS `updated_at`, `u`.`customer_name` AS `customer_name`, `u`.`customer_email` AS `customer_email`, `r`.`checkin_date` AS `checkin_date`, `r`.`checkout_date` AS `checkout_date`, `r`.`room_type` AS `room_type`, `r`.`status` AS `reservation_status`, `s`.`employee_name` AS `processed_by_name` FROM (((`payments` `p` join `user` `u` on(`p`.`user_id` = `u`.`user_id`)) join `customerreservation` `r` on(`p`.`reservation_id` = `r`.`reservation_id`)) left join `staff` `s` on(`p`.`processed_by` = `s`.`employee_number`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `customerreservation`
--
ALTER TABLE `customerreservation`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `fk_user` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `employee_number` (`employee_number`),
  ADD KEY `processed_by` (`processed_by`),
  ADD KEY `idx_reservation` (`reservation_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_payment_date` (`payment_date`),
  ADD KEY `idx_status` (`payment_status`),
  ADD KEY `idx_mode` (`mode_of_payment`),
  ADD KEY `idx_reference` (`transaction_reference`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`employee_number`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `customer_email` (`customer_email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customerreservation`
--
ALTER TABLE `customerreservation`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `employee_number` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customerreservation`
--
ALTER TABLE `customerreservation`
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `customerreservation` (`reservation_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`employee_number`) REFERENCES `staff` (`employee_number`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_ibfk_4` FOREIGN KEY (`processed_by`) REFERENCES `staff` (`employee_number`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
