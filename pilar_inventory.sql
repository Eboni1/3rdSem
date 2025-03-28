-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 28, 2025 at 04:18 AM
-- Server version: 10.6.15-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pilar_inventory`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `action` text NOT NULL,
  `action_type` enum('report','RIS','memo','user','inventory','other') NOT NULL DEFAULT 'other',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `user_name`, `action`, `action_type`, `created_at`) VALUES
(1, 5, 'admin2', 'created RIS #RIS-1742478851', '', '2025-03-20 13:54:11'),
(2, 5, 'admin2', 'approved RIS report #3', 'report', '2025-03-20 14:18:21'),
(3, 5, 'admin2', 'rejected RIS report #3', 'report', '2025-03-20 14:18:47'),
(4, 5, 'admin2', 'approved RIS report #3', 'report', '2025-03-20 14:25:01'),
(5, 5, 'admin2', 'approved RIS report #3', 'RIS', '2025-03-20 14:30:54');

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` int(11) NOT NULL,
  `asset_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `status` enum('Available','In Use','Maintenance','Retired') NOT NULL DEFAULT 'Available',
  `description` text DEFAULT NULL,
  `date_acquired` date NOT NULL,
  `asset_value` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `unit` varchar(50) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `office_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`id`, `asset_name`, `category`, `status`, `description`, `date_acquired`, `asset_value`, `quantity`, `unit`, `location`, `office_id`) VALUES
(1, 'Blue Chair', 'Office Furniture', 'Available', 'uratex, durable', '2025-03-15', 400.00, 1, 'pc', 'Supply Office', 1),
(17, 'HP Laptop', 'IT Equipment', 'Maintenance', 'vibrant 15.6-inch display, a snappy Intel Core i5 processor', '2025-03-17', 15000.00, 1, 'pc', 'IT office', 1),
(18, 'Lenovo Laptop', 'IT Equipment', 'In Use', 'gloss 14.6-inch display, a snappy AMD Ryzen5 processor', '2025-03-18', 38000.00, 3, 'pcs', 'IT office', 1);

-- --------------------------------------------------------

--
-- Table structure for table `asset_categories`
--

CREATE TABLE `asset_categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `asset_categories`
--

INSERT INTO `asset_categories` (`id`, `category_name`, `created_at`) VALUES
(1, 'Office Furniture', '2025-03-15 04:20:50'),
(2, 'Government Facilities', '2025-03-17 07:04:18'),
(3, 'Emergency Vehicles', '2025-03-17 07:04:18'),
(4, 'Service Vehicles', '2025-03-17 07:04:18'),
(5, 'Health & Medical', '2025-03-17 07:04:18'),
(6, 'IT & Security', '2025-03-17 07:04:18'),
(7, 'IT Equipment', '2025-03-17 07:04:18'),
(8, 'Infrastructure', '2025-03-17 07:04:18'),
(9, 'Public Infrastructure', '2025-03-17 07:04:18'),
(10, 'Disaster Management', '2025-03-17 07:04:18'),
(11, 'Utilities', '2025-03-17 07:04:18');

-- --------------------------------------------------------

--
-- Table structure for table `audit_trail`
--

CREATE TABLE `audit_trail` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `action` varchar(255) NOT NULL,
  `module` varchar(100) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_trail`
--

INSERT INTO `audit_trail` (`id`, `user_id`, `username`, `action`, `module`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 4, 'superadmin', 'Updated settings: system_theme: from \'default\' to \'dark\'', 'System Settings', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', '2025-03-20 01:58:27'),
(2, 4, 'superadmin', 'Updated settings: system_theme: from \'dark\' to \'default\'', 'System Settings', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', '2025-03-20 01:58:36'),
(3, 4, 'superadmin', 'Updated settings: date_format: from \'Y-m-d\' to \'m/d/Y\'', 'System Settings', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', '2025-03-24 07:03:27'),
(4, 4, 'superadmin', 'Updated settings: company_name: from \'Inventory Management System\' to \'Pilar Inventory Management System\'', 'System Settings', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', '2025-03-24 07:04:21');

-- --------------------------------------------------------

--
-- Table structure for table `memorandum_reports`
--

CREATE TABLE `memorandum_reports` (
  `id` int(11) NOT NULL,
  `memo_number` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_by` int(11) DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `memorandum_reports`
--

INSERT INTO `memorandum_reports` (`id`, `memo_number`, `title`, `description`, `created_by`, `created_at`, `approved_by`, `approved_at`, `rejected_by`, `rejected_at`, `status`) VALUES
(1, 'MEMO-20240320-001', 'Office Equipment Update', 'Request for new office chairs and tables.', 1, '2025-03-20 05:19:00', NULL, NULL, NULL, NULL, 'pending'),
(2, 'MEMO-20240320-002', 'IT Asset Request', 'Need additional laptops and printers.', 2, '2025-03-20 05:19:00', NULL, NULL, NULL, NULL, 'approved'),
(3, 'MEMO-20240320-003', 'Maintenance Request', 'Repair air conditioning units in main office.', 3, '2025-03-20 05:19:00', NULL, NULL, NULL, NULL, 'rejected'),
(4, 'MEMO-20240320-004', 'Inventory Check', 'Conduct physical count of assets.', 4, '2025-03-20 05:19:00', NULL, NULL, NULL, NULL, 'pending'),
(5, 'MEMO-20240320-005', 'Vehicle Repair Request', 'Request to service government vehicles.', 5, '2025-03-20 05:19:00', NULL, NULL, NULL, NULL, 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `memo_items`
--

CREATE TABLE `memo_items` (
  `id` int(11) NOT NULL,
  `memo_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `memo_items`
--

INSERT INTO `memo_items` (`id`, `memo_id`, `item_name`, `quantity`, `unit`) VALUES
(1, 1, 'Office Chair', 10, 'pcs'),
(2, 1, 'Office Table', 5, 'pcs'),
(3, 2, 'Laptop', 3, 'units'),
(4, 2, 'Printer', 2, 'units'),
(5, 3, 'Air Conditioner Repair', 1, 'service'),
(6, 4, 'Barcode Scanner', 2, 'units'),
(7, 5, 'Vehicle Maintenance', 1, 'service');

-- --------------------------------------------------------

--
-- Table structure for table `offices`
--

CREATE TABLE `offices` (
  `id` int(11) NOT NULL,
  `office_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `offices`
--

INSERT INTO `offices` (`id`, `office_name`) VALUES
(1, 'OMPDC'),
(2, 'Supply Office'),
(3, 'IT Office'),
(4, 'Warehouse'),
(5, 'Default Office');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `report_number` varchar(50) NOT NULL,
  `type` enum('RIS','Memorandum') NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_by` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `rejected_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `approved_at` datetime DEFAULT NULL,
  `rejected_at` datetime DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `entity_name` varchar(255) DEFAULT NULL,
  `fund_cluster` varchar(100) DEFAULT NULL,
  `responsibility_center` varchar(100) DEFAULT NULL,
  `rcc_code` varchar(50) DEFAULT NULL,
  `division` varchar(100) DEFAULT NULL,
  `office` varchar(100) DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `requested_by` varchar(255) DEFAULT NULL,
  `requested_by_position` varchar(100) DEFAULT NULL,
  `approved_by_name` varchar(255) DEFAULT NULL,
  `approved_by_position` varchar(100) DEFAULT NULL,
  `issued_by` varchar(255) DEFAULT NULL,
  `issued_by_position` varchar(100) DEFAULT NULL,
  `received_by` varchar(255) DEFAULT NULL,
  `received_by_position` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `report_number`, `type`, `title`, `description`, `content`, `status`, `created_by`, `approved_by`, `rejected_by`, `created_at`, `updated_at`, `approved_at`, `rejected_at`, `department`, `entity_name`, `fund_cluster`, `responsibility_center`, `rcc_code`, `division`, `office`, `purpose`, `requested_by`, `requested_by_position`, `approved_by_name`, `approved_by_position`, `issued_by`, `issued_by_position`, `received_by`, `received_by_position`) VALUES
(1, 'RIS-20240320-001', 'RIS', 'Office Supplies Request', 'Requesting office supplies for HR.', NULL, 'pending', 1, NULL, NULL, '2025-03-20 13:37:51', '2025-03-20 13:37:51', NULL, NULL, 'Human Resources', 'LGU - City Hall', 'FC-101', 'RC-102', 'RCC-001', 'Finance Division', 'Procurement Office', 'Office supplies for daily operations', 'John Doe', 'HR Officer', 'Jane Smith', 'Department Head', 'Mark Johnson', 'Warehouse Officer', 'Emily Clark', 'Staff'),
(2, 'RIS-20240320-002', 'RIS', 'IT Equipment Request', 'Need new computers for IT department.', NULL, 'approved', 2, 3, NULL, '2025-03-20 13:37:51', '2025-03-20 13:37:51', '2025-03-20 13:37:51', NULL, 'Information Technology', 'LGU - IT Dept.', 'FC-202', 'RC-203', 'RCC-002', 'Tech Division', 'IT Office', 'Upgrade IT infrastructure', 'Michael Lee', 'IT Supervisor', 'Anna White', 'IT Director', 'David Brown', 'Warehouse Manager', 'Sarah Davis', 'IT Technician'),
(3, 'MEMO-20240320-001', '', 'New Office Policies', 'Policy update regarding attendance and conduct.', 'Policy document content...', 'pending', 1, NULL, NULL, '2025-03-20 13:37:51', '2025-03-20 13:37:51', NULL, NULL, 'Administration', 'LGU - Main Office', 'FC-303', 'RC-304', 'RCC-003', 'Admin Division', 'HR Office', 'Inform staff about new policies', 'Lisa Wilson', 'Admin Manager', 'Robert Green', 'City Administrator', NULL, NULL, NULL, NULL),
(4, 'MEMO-20240320-002', '', 'Work-from-Home Guidelines', 'New rules for remote work.', 'Guidelines content...', 'approved', 3, 1, NULL, '2025-03-20 13:37:51', '2025-03-20 13:37:51', '2025-03-20 13:37:51', NULL, 'Executive Office', 'LGU - Executive Branch', 'FC-404', 'RC-405', 'RCC-004', 'Executive Division', 'Mayor\'s Office', 'Set rules for remote work', 'Sophia Martinez', 'Exec Assistant', 'Thomas Brown', 'Mayor', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ris_items`
--

CREATE TABLE `ris_items` (
  `id` int(11) NOT NULL,
  `ris_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ris_items`
--

INSERT INTO `ris_items` (`id`, `ris_id`, `item_name`, `quantity`, `unit`) VALUES
(1, 3, 'Asus Vivobook', 2, 'pcs');

-- --------------------------------------------------------

--
-- Table structure for table `ris_reports`
--

CREATE TABLE `ris_reports` (
  `id` int(11) NOT NULL,
  `ris_number` varchar(50) NOT NULL,
  `requesting_office` varchar(255) NOT NULL,
  `purpose` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_by` int(11) DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ris_reports`
--

INSERT INTO `ris_reports` (`id`, `ris_number`, `requesting_office`, `purpose`, `created_by`, `created_at`, `updated_at`, `approved_by`, `approved_at`, `rejected_by`, `rejected_at`, `status`, `title`) VALUES
(3, 'RIS-1742478851', 'IT Office', 'For the IT office.', 5, '2025-03-20 13:54:11', '2025-03-20 14:30:54', 5, '2025-03-20 14:30:54', NULL, NULL, 'approved', 'Request for Laptop');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_name` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_name`, `setting_value`, `setting_description`, `created_at`, `updated_at`) VALUES
(1, 'company_name', 'Pilar Inventory Management System', 'Company or organization name', '2025-03-18 04:53:46', '2025-03-24 07:04:21'),
(2, 'system_email', 'admin@example.com', 'System email for notifications', '2025-03-18 04:53:46', '2025-03-18 04:53:46'),
(3, 'items_per_page', '10', 'Number of items to display per page in tables', '2025-03-18 04:53:46', '2025-03-18 04:53:46'),
(4, 'enable_email_notifications', 'false', 'Enable or disable email notifications', '2025-03-18 04:53:46', '2025-03-18 04:53:46'),
(5, 'maintenance_reminder_days', '30', 'Days before maintenance due to send reminder', '2025-03-18 04:53:46', '2025-03-18 04:53:46'),
(6, 'default_currency', 'PHP', 'Default currency symbol for the system', '2025-03-18 04:53:46', '2025-03-18 04:53:46'),
(7, 'date_format', 'm/d/Y', 'PHP date format for displaying dates', '2025-03-18 04:53:46', '2025-03-24 07:03:27'),
(8, 'enable_user_registration', 'false', 'Allow users to register accounts', '2025-03-18 04:53:46', '2025-03-18 04:53:46'),
(9, 'system_theme', 'default', 'UI theme for the system', '2025-03-18 04:53:46', '2025-03-20 01:58:36');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','user') NOT NULL DEFAULT 'user',
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `office_id` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `fullname`, `email`, `password`, `role`, `status`, `created_at`, `reset_token`, `reset_token_expiry`, `office_id`) VALUES
(1, 'user2', '', 'User2@gmail.com', '12345678', 'user', 'active', '2025-03-14 04:44:31', NULL, NULL, 1),
(2, 'admin1', 'josh', 'admin1@gmail.com', '$2y$10$12345hashedpasswordexample', 'admin', 'active', '2025-03-14 04:44:31', NULL, NULL, 1),
(3, 'user1', '', 'waltielappy@gmail.com', '$2y$10$12345hashedpasswordexample', 'user', 'inactive', '2025-03-14 04:44:31', NULL, NULL, 2),
(4, 'superadmin', '', 'waltielappy@gmail.com', '$2y$10$IDqjMt8ot3lbelw3qq82k.RBjB2n4L3CpR2ED5XTd6o0TMWT/oZl2', 'super_admin', 'active', '2025-03-14 04:44:31', NULL, NULL, 1),
(5, 'admin2', '', 'waltielappy@gmail.com', '$2y$10$BITmiE9mTHgkXWg8iNq2ceQRIl1ETdntVfzbXI3ABNM4f7exuX.Vm', 'admin', 'active', '2025-03-20 04:34:45', NULL, NULL, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_index` (`category`),
  ADD KEY `status_index` (`status`),
  ADD KEY `fk_office` (`office_id`);

--
-- Indexes for table `asset_categories`
--
ALTER TABLE `asset_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `audit_trail`
--
ALTER TABLE `audit_trail`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `memorandum_reports`
--
ALTER TABLE `memorandum_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `memo_number` (`memo_number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `rejected_by` (`rejected_by`);

--
-- Indexes for table `memo_items`
--
ALTER TABLE `memo_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `memo_id` (`memo_id`);

--
-- Indexes for table `offices`
--
ALTER TABLE `offices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `report_number` (`report_number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `rejected_by` (`rejected_by`),
  ADD KEY `type_status_idx` (`type`,`status`),
  ADD KEY `created_at_idx` (`created_at`);

--
-- Indexes for table `ris_items`
--
ALTER TABLE `ris_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ris_id` (`ris_id`);

--
-- Indexes for table `ris_reports`
--
ALTER TABLE `ris_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ris_number` (`ris_number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `rejected_by` (`rejected_by`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_name` (`setting_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `fk_users_office` (`office_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `asset_categories`
--
ALTER TABLE `asset_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `audit_trail`
--
ALTER TABLE `audit_trail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `memorandum_reports`
--
ALTER TABLE `memorandum_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `memo_items`
--
ALTER TABLE `memo_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `offices`
--
ALTER TABLE `offices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ris_items`
--
ALTER TABLE `ris_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ris_reports`
--
ALTER TABLE `ris_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `fk_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `memorandum_reports`
--
ALTER TABLE `memorandum_reports`
  ADD CONSTRAINT `memorandum_reports_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `memorandum_reports_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `memorandum_reports_ibfk_3` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `memo_items`
--
ALTER TABLE `memo_items`
  ADD CONSTRAINT `memo_items_ibfk_1` FOREIGN KEY (`memo_id`) REFERENCES `memorandum_reports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_approved_by_fk` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reports_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_rejected_by_fk` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ris_items`
--
ALTER TABLE `ris_items`
  ADD CONSTRAINT `ris_items_ibfk_1` FOREIGN KEY (`ris_id`) REFERENCES `ris_reports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ris_reports`
--
ALTER TABLE `ris_reports`
  ADD CONSTRAINT `ris_reports_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ris_reports_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ris_reports_ibfk_3` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
