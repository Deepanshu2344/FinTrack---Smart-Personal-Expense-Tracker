-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 13, 2025 at 07:25 AM
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
-- Database: `fintrack`
--

-- --------------------------------------------------------

--
-- Table structure for table `budgets`
--

CREATE TABLE `budgets` (
  `user_id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `budget_amount` decimal(10,2) NOT NULL,
  `frequency` varchar(10) NOT NULL DEFAULT 'monthly '
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `budgets`
--

INSERT INTO `budgets` (`user_id`, `category`, `budget_amount`, `frequency`) VALUES
(1, 'EMI', 15000.00, 'monthly '),
(1, 'Food', 5000.00, 'monthly'),
(1, 'Freelance Work', 50000.00, 'weekly'),
(1, 'Fuel', 100.00, 'weekly'),
(1, 'Groceries', 10000.00, 'monthly'),
(1, 'Grocery', 10000.00, 'monthly'),
(1, 'Laundry', 1000.00, 'monthly'),
(1, 'Rent', 20000.00, 'monthly '),
(1, 'Salary', 10000.00, 'yearly'),
(1, 'Share Market ', 10000.00, 'monthly'),
(1, 'Stationary', 500.00, 'weekly'),
(1, 'Transportation', 5000.00, 'yearly'),
(1, 'travel', 50000.00, 'yearly'),
(5, 'Fuel', 5000.00, 'monthly');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `expense_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `date` date NOT NULL,
  `transaction_type` enum('credit','debit') NOT NULL,
  `mode_of_payment` enum('cash','card','upi') NOT NULL DEFAULT 'cash'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`expense_id`, `user_id`, `category`, `amount`, `date`, `transaction_type`, `mode_of_payment`) VALUES
(4, 1, 'Salary', 90000.00, '2025-01-01', 'credit', 'cash'),
(5, 1, 'EMI', 15000.00, '2025-01-03', 'debit', 'cash'),
(6, 1, 'Rent', 10000.00, '2025-01-05', 'debit', 'cash'),
(8, 1, 'Grocery ', 5000.00, '2025-01-08', 'debit', 'cash'),
(9, 1, 'Travel', 2000.00, '2025-01-31', 'debit', 'cash'),
(10, 1, 'Electricity & Gas Bills', 3000.00, '2025-01-15', 'debit', 'cash'),
(11, 1, 'Food', 5800.00, '2025-01-18', 'debit', 'cash'),
(13, 1, 'Share Market ', 25000.00, '2025-01-22', 'credit', 'cash'),
(14, 1, 'Fuel', 1000.00, '2025-02-22', 'debit', 'cash'),
(15, 1, 'WiFi', 1000.00, '2025-03-01', 'debit', 'cash'),
(16, 1, 'Fuel', 500.00, '2025-03-09', 'debit', 'cash'),
(17, 1, 'Grocery', 2000.00, '2025-03-05', 'debit', 'cash'),
(18, 1, 'Salary', 10000.00, '2025-03-09', 'credit', 'cash'),
(19, 1, 'EMI', 5555.00, '2025-03-09', 'debit', 'cash'),
(20, 1, 'Freelance Work', 100000.00, '2025-03-10', 'debit', 'cash'),
(21, 1, 'Freelance Work', 1000000.00, '2025-03-11', 'credit', 'cash'),
(22, 1, 'Grocery ', 5000.00, '2025-03-07', 'debit', 'cash'),
(23, 1, 'Fuel', 10000.00, '2025-03-11', 'debit', 'card'),
(24, 1, 'EMI', 10000.00, '2025-03-11', 'debit', 'upi'),
(25, 1, 'Food', 150.00, '2025-03-11', 'debit', 'upi'),
(26, 1, 'Grocery ', 2000.00, '2025-03-11', 'debit', 'upi'),
(27, 5, 'Salary', 50000.00, '2025-03-12', 'credit', 'upi'),
(28, 5, 'EMI', 5000.00, '2025-03-04', 'debit', 'upi'),
(29, 5, 'Fuel', 1000.00, '2025-03-12', 'debit', 'cash'),
(30, 1, 'Stationary', 100.00, '2025-03-12', 'debit', 'cash'),
(31, 1, 'Salary', 50000.00, '2025-03-07', 'credit', 'upi'),
(32, 1, 'Laundry', 800.00, '2025-03-13', 'debit', 'upi');

-- --------------------------------------------------------

--
-- Table structure for table `insights`
--

CREATE TABLE `insights` (
  `insight_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `analysis_type` varchar(50) DEFAULT NULL,
  `prediction` text NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recurring_expenses`
--

CREATE TABLE `recurring_expenses` (
  `recurring_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `frequency` enum('daily','weekly','monthly','yearly') NOT NULL,
  `start_date` date NOT NULL,
  `next_payment_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recurring_expenses`
--

INSERT INTO `recurring_expenses` (`recurring_id`, `user_id`, `category`, `amount`, `frequency`, `start_date`, `next_payment_date`) VALUES
(1, 1, 'Netflix', 300.00, 'monthly', '2025-02-01', '2025-03-12'),
(2, 1, 'Transportation', 100.00, 'daily', '2025-03-01', NULL),
(3, 1, 'Home Loan EMI', 200000.00, 'monthly', '2025-01-01', '2025-02-05'),
(4, 1, 'Home Rent', 15000.00, 'monthly', '2025-02-01', '2025-03-06'),
(5, 1, 'Freelance Work', 50000.00, 'yearly', '2025-01-01', '2026-01-01'),
(6, 1, 'Prime video', 1700.00, 'yearly', '2025-01-11', '2026-01-11'),
(7, 1, 'Car Loan EMI', 20000.00, 'monthly', '2025-03-01', '2025-04-05'),
(10, 1, 'Fuel', 5000.00, 'monthly', '2025-01-01', '2025-03-12'),
(11, 1, 'Trading', 10000.00, 'weekly', '2025-03-01', '2025-03-13'),
(12, 5, 'GYM Membership', 3000.00, 'monthly', '2025-01-01', '2025-03-13');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'INR',
  `budget_alerts` tinyint(1) NOT NULL DEFAULT 1,
  `recurring_reminders` tinyint(1) NOT NULL DEFAULT 0,
  `theme` varchar(10) NOT NULL DEFAULT 'light'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `phone`, `password`, `currency`, `budget_alerts`, `recurring_reminders`, `theme`) VALUES
(1, 'Deepanshu Pawar', 'deeppwr07@gmail.com', '9890016975', '$2y$10$l7ZD4OKs7VtVetf4ZeXqzO1Ar1eZcEEZGmuvp74oOwsXSvk.DkLaC', 'INR', 1, 0, 'light'),
(4, 'Ayush Jadhav', 'ayushjadhav@gmail.com', '8888888888', '$2y$10$1OvZ3kilRzjr0Ele6zModOJb3FUeHu1/7YttvUujT/39gAIX7tPEi', 'INR', 1, 0, 'light'),
(5, 'Soham Adsare', 'soham@gmail.com', '5151162626', '$2y$10$0HlaA7ABKe7uHTTsly7uyO9426/9pXkHrv04tSjRcbT6szgGYUDc6', 'INR', 1, 0, 'light');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `budgets`
--
ALTER TABLE `budgets`
  ADD PRIMARY KEY (`user_id`,`category`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`expense_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `insights`
--
ALTER TABLE `insights`
  ADD PRIMARY KEY (`insight_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `recurring_expenses`
--
ALTER TABLE `recurring_expenses`
  ADD PRIMARY KEY (`recurring_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `expense_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `insights`
--
ALTER TABLE `insights`
  MODIFY `insight_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recurring_expenses`
--
ALTER TABLE `recurring_expenses`
  MODIFY `recurring_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `budgets`
--
ALTER TABLE `budgets`
  ADD CONSTRAINT `budgets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `insights`
--
ALTER TABLE `insights`
  ADD CONSTRAINT `insights_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `recurring_expenses`
--
ALTER TABLE `recurring_expenses`
  ADD CONSTRAINT `recurring_expenses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
