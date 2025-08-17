-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 03, 2025 at 03:54 PM
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
-- Database: `hostelmess`
--

-- --------------------------------------------------------

--
-- Table structure for table `fixed_costs`
--

CREATE TABLE `fixed_costs` (
  `id` int(11) NOT NULL,
  `cost_type` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `month` varchar(7) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fixed_costs`
--

INSERT INTO `fixed_costs` (`id`, `cost_type`, `amount`, `month`, `created_at`) VALUES
(1, 'ansina cook salary', 10000.00, '2025-03', '2025-03-28 12:34:51'),
(2, 'cook sufi', 2000.00, '2025-03', '2025-03-28 12:35:45'),
(3, 'ansina cook salary', 20000.00, '2025-04', '2025-04-02 18:13:16'),
(4, 'ansina cook salary', 20000.00, '2025-04', '2025-04-02 18:15:01'),
(5, 'cook sufi', 18000.00, '2025-04', '2025-04-02 18:15:51');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_costs`
--

CREATE TABLE `inventory_costs` (
  `id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_costs`
--

INSERT INTO `inventory_costs` (`id`, `item_id`, `purchase_date`, `quantity`, `cost`, `created_at`) VALUES
(1, 7, '2025-03-28', 5.00, 100.00, '2025-03-28 12:33:45'),
(2, 8, '2025-04-02', 5.00, 100.00, '2025-04-02 18:09:45');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE `inventory_items` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `last_purchase_quantity` decimal(10,2) DEFAULT NULL,
  `unit` varchar(20) NOT NULL,
  `vendor` varchar(100) NOT NULL,
  `purchase_date` date NOT NULL,
  `last_purchase_date` date DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_purchase_cost` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_items`
--

INSERT INTO `inventory_items` (`id`, `item_name`, `quantity`, `last_purchase_quantity`, `unit`, `vendor`, `purchase_date`, `last_purchase_date`, `last_updated`, `last_purchase_cost`) VALUES
(7, 'Salt', 5.00, 5.00, 'kg', 'painav supermarket', '2025-03-28', '2025-03-28', '2025-03-28 12:33:45', 100.00),
(8, 'sugar', 5.00, 5.00, 'kg', 'cheruthoni supermarket', '2025-04-02', '2025-04-02', '2025-04-02 18:09:45', 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_transactions`
--

CREATE TABLE `inventory_transactions` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `transaction_type` enum('purchase','usage') NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mess_bills`
--

CREATE TABLE `mess_bills` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `days_present` int(11) NOT NULL,
  `fixed_cost` decimal(10,2) NOT NULL,
  `variable_cost` decimal(10,2) NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `authorized` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mess_menu`
--

CREATE TABLE `mess_menu` (
  `id` int(11) NOT NULL,
  `day` varchar(10) NOT NULL,
  `category` varchar(20) NOT NULL,
  `meal_option_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mess_menu`
--

INSERT INTO `mess_menu` (`id`, `day`, `category`, `meal_option_id`) VALUES
(1, 'Monday', 'breakfast', 86),
(2, 'Monday', 'lunch', 56),
(3, 'Monday', 'snacks', 64),
(4, 'Monday', 'dinner', 78),
(5, 'Tuesday', 'breakfast', 89),
(6, 'Tuesday', 'lunch', 47),
(7, 'Tuesday', 'snacks', 66),
(8, 'Tuesday', 'dinner', 74),
(9, 'Wednesday', 'breakfast', 90),
(10, 'Wednesday', 'lunch', 48),
(11, 'Wednesday', 'snacks', 67),
(12, 'Wednesday', 'dinner', 77),
(13, 'Thursday', 'breakfast', 82),
(14, 'Thursday', 'lunch', 52),
(15, 'Thursday', 'snacks', 58),
(16, 'Thursday', 'dinner', 76),
(17, 'Friday', 'breakfast', 83),
(18, 'Friday', 'lunch', 46),
(19, 'Friday', 'snacks', 60),
(20, 'Friday', 'dinner', 68),
(21, 'Saturday', 'breakfast', 85),
(22, 'Saturday', 'lunch', 55),
(23, 'Saturday', 'snacks', 63),
(24, 'Saturday', 'dinner', 70),
(25, 'Sunday', 'breakfast', 88),
(26, 'Sunday', 'lunch', 51),
(27, 'Sunday', 'snacks', 65),
(28, 'Sunday', 'dinner', 75);

-- --------------------------------------------------------

--
-- Table structure for table `mess_payments`
--

CREATE TABLE `mess_payments` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `receipt_image` varchar(255) NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mess_payments`
--

INSERT INTO `mess_payments` (`id`, `username`, `transaction_id`, `amount`, `account_name`, `receipt_image`, `month`, `year`, `submission_date`, `status`) VALUES
(1, 'irfan', '1242352', 4000.00, 'irfan shah', 'uploads/irfan_1741712767.jpg', 2, 2025, '2025-03-11 17:06:07', 'pending'),
(2, 'irfan', '678942199', 3590.00, 'irfan shah', 'uploads/irfan_1741765426.jpg', 3, 2025, '2025-03-12 07:43:46', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `mess_poll_options`
--

CREATE TABLE `mess_poll_options` (
  `id` int(11) NOT NULL,
  `category` enum('breakfast','lunch','snacks','dinner') NOT NULL,
  `option_text` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mess_poll_options`
--

INSERT INTO `mess_poll_options` (`id`, `category`, `option_text`) VALUES
(46, 'lunch', 'fish/tomato curry'),
(47, 'lunch', 'avyal and curd'),
(48, 'lunch', 'sambar cabbage'),
(50, 'lunch', 'fish/cauliflower fry'),
(51, 'lunch', 'chicken curry/veg curry'),
(52, 'lunch', 'egg biriyani'),
(55, 'lunch', 'soyabean fry'),
(56, 'lunch', 'betroot upperi'),
(57, 'snacks', 'banana fry'),
(58, 'snacks', 'cutlet'),
(60, 'snacks', 'egg bajji'),
(61, 'lunch', 'paripp vada'),
(62, 'lunch', 'puffs'),
(63, 'snacks', 'puffs'),
(64, 'snacks', 'paripp vada'),
(65, 'snacks', 'sandwich'),
(66, 'snacks', 'samosa'),
(67, 'snacks', 'uzhn vada'),
(68, 'dinner', 'beef biriyani'),
(69, 'dinner', 'chappathi-(chicken curry/cauliflower fry)'),
(70, 'dinner', 'chicken biriyani'),
(71, 'dinner', 'fried rice (veg/chicken)'),
(72, 'dinner', 'rice- (potato fry/fish fry)'),
(74, 'dinner', 'porotta (paneer/beef)'),
(75, 'dinner', 'kanji'),
(76, 'dinner', 'pasta(veg/chicken)'),
(77, 'dinner', 'noodles(veg/chicken)'),
(78, 'dinner', 'Pizza(veg/chicken)'),
(79, 'snacks', 'potato bajji'),
(80, 'snacks', 'avil '),
(81, 'snacks', 'biscuit'),
(82, 'breakfast', 'Dosa sambar'),
(83, 'breakfast', 'idli chutney'),
(84, 'breakfast', 'uppmav pazham'),
(85, 'breakfast', 'putt kadla'),
(86, 'breakfast', 'idiyappam stew'),
(87, 'breakfast', 'appam egg curry'),
(88, 'breakfast', 'bread omlette'),
(89, 'breakfast', 'masala dosa'),
(90, 'breakfast', 'pathiri chicken/paneer'),
(91, 'breakfast', 'masala dosa');

-- --------------------------------------------------------

--
-- Table structure for table `mess_poll_status`
--

CREATE TABLE `mess_poll_status` (
  `id` int(11) NOT NULL,
  `poll_date` date NOT NULL,
  `opened_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','open','closed') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mess_poll_status`
--

INSERT INTO `mess_poll_status` (`id`, `poll_date`, `opened_at`, `closed_at`, `status`) VALUES
(1, '2025-03-13', '2025-03-13 03:56:20', '2025-03-13 03:59:09', 'closed'),
(2, '2025-03-14', '2025-03-14 06:11:50', NULL, 'open'),
(3, '2025-03-18', '2025-03-18 13:20:45', NULL, 'open'),
(4, '2025-04-03', '2025-04-03 05:43:01', NULL, 'open');

-- --------------------------------------------------------

--
-- Table structure for table `monthly_attendance`
--

CREATE TABLE `monthly_attendance` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `mess_cut_from` date DEFAULT NULL,
  `mess_cut_to` date DEFAULT NULL,
  `mess_cut_status` enum('pending','approved','rejected') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `monthly_attendance`
--

INSERT INTO `monthly_attendance` (`id`, `username`, `month`, `year`, `mess_cut_from`, `mess_cut_to`, `mess_cut_status`) VALUES
(23446, 'kenz', 4, 2025, '2025-04-30', '2025-05-01', 'approved'),
(23447, 'kenz', 4, 2025, '2025-04-05', '2025-04-10', 'approved'),
(23448, 'kenz', 4, 2025, '2025-04-07', '2025-04-11', 'approved'),
(23449, 'ansina', 4, 2025, '2025-04-06', '2025-04-09', 'approved'),
(23450, 'ansina', 4, 2025, '2025-04-06', '2025-04-10', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `poll_responses`
--

CREATE TABLE `poll_responses` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `option_id` int(11) NOT NULL,
  `category` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `poll_responses`
--

INSERT INTO `poll_responses` (`id`, `username`, `option_id`, `category`) VALUES
(197, 'dilna', 82, 'breakfast'),
(198, 'dilna', 83, 'breakfast'),
(199, 'dilna', 84, 'breakfast'),
(200, 'dilna', 86, 'breakfast'),
(201, 'dilna', 87, 'breakfast'),
(202, 'dilna', 89, 'breakfast'),
(203, 'dilna', 90, 'breakfast'),
(204, 'dilna', 46, 'lunch'),
(205, 'dilna', 47, 'lunch'),
(206, 'dilna', 51, 'lunch'),
(207, 'dilna', 52, 'lunch'),
(208, 'dilna', 55, 'lunch'),
(209, 'dilna', 56, 'lunch'),
(210, 'dilna', 61, 'lunch'),
(211, 'dilna', 57, 'snacks'),
(212, 'dilna', 58, 'snacks'),
(213, 'dilna', 60, 'snacks'),
(214, 'dilna', 65, 'snacks'),
(215, 'dilna', 66, 'snacks'),
(216, 'dilna', 67, 'snacks'),
(217, 'dilna', 79, 'snacks'),
(218, 'dilna', 68, 'dinner'),
(219, 'dilna', 69, 'dinner'),
(220, 'dilna', 70, 'dinner'),
(221, 'dilna', 71, 'dinner'),
(222, 'dilna', 74, 'dinner'),
(223, 'dilna', 75, 'dinner'),
(224, 'dilna', 76, 'dinner'),
(225, 'ansina', 82, 'breakfast'),
(226, 'ansina', 84, 'breakfast'),
(227, 'ansina', 85, 'breakfast'),
(228, 'ansina', 87, 'breakfast'),
(229, 'ansina', 88, 'breakfast'),
(230, 'ansina', 89, 'breakfast'),
(231, 'ansina', 90, 'breakfast'),
(232, 'ansina', 46, 'lunch'),
(233, 'ansina', 47, 'lunch'),
(234, 'ansina', 48, 'lunch'),
(235, 'ansina', 51, 'lunch'),
(236, 'ansina', 55, 'lunch'),
(237, 'ansina', 56, 'lunch'),
(238, 'ansina', 61, 'lunch'),
(239, 'ansina', 57, 'snacks'),
(240, 'ansina', 60, 'snacks'),
(241, 'ansina', 64, 'snacks'),
(242, 'ansina', 65, 'snacks'),
(243, 'ansina', 67, 'snacks'),
(244, 'ansina', 80, 'snacks'),
(245, 'ansina', 81, 'snacks'),
(246, 'ansina', 68, 'dinner'),
(247, 'ansina', 69, 'dinner'),
(248, 'ansina', 72, 'dinner'),
(249, 'ansina', 74, 'dinner'),
(250, 'ansina', 76, 'dinner'),
(251, 'ansina', 77, 'dinner'),
(252, 'ansina', 78, 'dinner'),
(253, 'irfan', 82, 'breakfast'),
(254, 'irfan', 83, 'breakfast'),
(255, 'irfan', 84, 'breakfast'),
(256, 'irfan', 85, 'breakfast'),
(257, 'irfan', 86, 'breakfast'),
(258, 'irfan', 87, 'breakfast'),
(259, 'irfan', 90, 'breakfast'),
(260, 'irfan', 46, 'lunch'),
(261, 'irfan', 47, 'lunch'),
(262, 'irfan', 48, 'lunch'),
(263, 'irfan', 55, 'lunch'),
(264, 'irfan', 56, 'lunch'),
(265, 'irfan', 61, 'lunch'),
(266, 'irfan', 62, 'lunch'),
(267, 'irfan', 57, 'snacks'),
(268, 'irfan', 60, 'snacks'),
(269, 'irfan', 64, 'snacks'),
(270, 'irfan', 65, 'snacks'),
(271, 'irfan', 67, 'snacks'),
(272, 'irfan', 80, 'snacks'),
(273, 'irfan', 81, 'snacks'),
(274, 'irfan', 68, 'dinner'),
(275, 'irfan', 69, 'dinner'),
(276, 'irfan', 70, 'dinner'),
(277, 'irfan', 74, 'dinner'),
(278, 'irfan', 76, 'dinner'),
(279, 'irfan', 77, 'dinner'),
(280, 'irfan', 78, 'dinner'),
(281, 'kenz', 82, 'breakfast'),
(282, 'kenz', 83, 'breakfast'),
(283, 'kenz', 85, 'breakfast'),
(284, 'kenz', 86, 'breakfast'),
(285, 'kenz', 87, 'breakfast'),
(286, 'kenz', 88, 'breakfast'),
(287, 'kenz', 89, 'breakfast'),
(288, 'kenz', 46, 'lunch'),
(289, 'kenz', 47, 'lunch'),
(290, 'kenz', 48, 'lunch'),
(291, 'kenz', 51, 'lunch'),
(292, 'kenz', 52, 'lunch'),
(293, 'kenz', 55, 'lunch'),
(294, 'kenz', 61, 'lunch'),
(295, 'kenz', 57, 'snacks'),
(296, 'kenz', 58, 'snacks'),
(297, 'kenz', 60, 'snacks'),
(298, 'kenz', 63, 'snacks'),
(299, 'kenz', 66, 'snacks'),
(300, 'kenz', 80, 'snacks'),
(301, 'kenz', 81, 'snacks'),
(302, 'kenz', 68, 'dinner'),
(303, 'kenz', 69, 'dinner'),
(304, 'kenz', 70, 'dinner'),
(305, 'kenz', 71, 'dinner'),
(306, 'kenz', 72, 'dinner'),
(307, 'kenz', 75, 'dinner'),
(308, 'kenz', 76, 'dinner');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(30) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `name`, `password`, `role`, `email`) VALUES
(100, 'kenz', 'Kenz Salih', 'kenz@1234', 'Resident', 'kenz4ppsalih@gmail.com'),
(101, 'irfan', 'Irfan Shah', 'irfan@123', 'Resident', 'irfanshah@gmail.com'),
(102, 'ansina', 'Ansina APK', 'ansina@123', 'Resident', 'ansinaapk22@gmail.com'),
(103, 'dilna', 'Dilna Nath', 'dilna@123', 'Resident', 'dilnavnath28@gmail.com'),
(201, 'Ms1', 'Boomika', 'Ms1@123', 'Mess_sec', 'bhoomika@gmail.com'),
(301, 'Mat1', 'Smitha', 'Mat1@123', 'Matron', 'smitha@gmail.com'),
(400, 'Warden', 'Anwar', 'Ward@123', 'Warden', 'anwar@gmail.com');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `fixed_costs`
--
ALTER TABLE `fixed_costs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_costs`
--
ALTER TABLE `inventory_costs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `mess_bills`
--
ALTER TABLE `mess_bills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `mess_menu`
--
ALTER TABLE `mess_menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `meal_option_id` (`meal_option_id`);

--
-- Indexes for table `mess_payments`
--
ALTER TABLE `mess_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mess_poll_options`
--
ALTER TABLE `mess_poll_options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mess_poll_status`
--
ALTER TABLE `mess_poll_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_date` (`poll_date`);

--
-- Indexes for table `monthly_attendance`
--
ALTER TABLE `monthly_attendance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `poll_responses`
--
ALTER TABLE `poll_responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `option_id` (`option_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `fixed_costs`
--
ALTER TABLE `fixed_costs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `inventory_costs`
--
ALTER TABLE `inventory_costs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `mess_bills`
--
ALTER TABLE `mess_bills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mess_menu`
--
ALTER TABLE `mess_menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `mess_payments`
--
ALTER TABLE `mess_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `mess_poll_options`
--
ALTER TABLE `mess_poll_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `mess_poll_status`
--
ALTER TABLE `mess_poll_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `monthly_attendance`
--
ALTER TABLE `monthly_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23451;

--
-- AUTO_INCREMENT for table `poll_responses`
--
ALTER TABLE `poll_responses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=309;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=402;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory_costs`
--
ALTER TABLE `inventory_costs`
  ADD CONSTRAINT `inventory_costs_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`);

--
-- Constraints for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD CONSTRAINT `inventory_transactions_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`);

--
-- Constraints for table `mess_bills`
--
ALTER TABLE `mess_bills`
  ADD CONSTRAINT `mess_bills_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `mess_menu`
--
ALTER TABLE `mess_menu`
  ADD CONSTRAINT `mess_menu_ibfk_1` FOREIGN KEY (`meal_option_id`) REFERENCES `mess_poll_options` (`id`);

--
-- Constraints for table `poll_responses`
--
ALTER TABLE `poll_responses`
  ADD CONSTRAINT `poll_responses_ibfk_1` FOREIGN KEY (`option_id`) REFERENCES `mess_poll_options` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
