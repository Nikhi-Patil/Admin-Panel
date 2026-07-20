-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 20, 2026 at 01:59 PM
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
-- Database: `admin`
--

-- --------------------------------------------------------

--
-- Table structure for table `category_master`
--

CREATE TABLE `category_master` (
  `id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category_master`
--

INSERT INTO `category_master` (`id`, `category_name`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(1, 'safd', 'Nikhil', '2026-07-16 07:39:45', 'Nikhil', '2026-07-16 09:27:14'),
(2, 'ertyuio', 'Nikhil', '2026-07-16 09:04:43', NULL, NULL),
(5, 'esrdfg', 'Nikhil', '2026-07-20 05:15:08', 'Nikhil', '2026-07-20 06:17:30'),
(6, 'sa', 'Nikhil', '2026-07-20 06:07:36', 'Nikhil', '2026-07-20 06:07:36'),
(8, 'xc', 'Nikhil', '2026-07-20 06:12:15', 'Nikhil', '2026-07-20 06:17:27'),
(9, 'safdg', 'Nikhil', '2026-07-20 06:12:35', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `compound_master`
--

CREATE TABLE `compound_master` (
  `id` int(11) NOT NULL,
  `compound_code` varchar(20) NOT NULL,
  `polymer` varchar(20) NOT NULL,
  `im_code` varchar(20) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `currency_master`
--

CREATE TABLE `currency_master` (
  `id` int(11) NOT NULL,
  `currency_name` varchar(100) NOT NULL,
  `currency_symbol` varchar(20) NOT NULL,
  `exchange_rate` decimal(18,6) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `currency_master`
--

INSERT INTO `currency_master` (`id`, `currency_name`, `currency_symbol`, `exchange_rate`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(1, 'asd', 'asd', 461.000000, 'Rutuja', '2026-07-17 05:24:39', NULL, NULL),
(2, 'adsd', 'sda', 94613.000000, 'Rutuja', '2026-07-17 05:24:48', NULL, NULL),
(3, 'USD', '$', 95.000000, 'Nikhil', '2026-07-18 08:38:51', 'Nikhil', '2026-07-18 08:40:56'),
(4, 'asd', 'asd', 0.000600, 'Nikhil', '2026-07-20 04:57:02', 'Nikhil', '2026-07-20 06:16:46');

-- --------------------------------------------------------

--
-- Table structure for table `customer_master`
--

CREATE TABLE `customer_master` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(50) NOT NULL,
  `sub_customer` varchar(50) NOT NULL,
  `geo_type` varchar(20) NOT NULL,
  `zone` varchar(20) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_master`
--

INSERT INTO `customer_master` (`id`, `customer_name`, `sub_customer`, `geo_type`, `zone`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(2, 'ASDFHFGJG', '', '', '', 'Nikhil', '2026-07-18 11:37:29', 'Nikhil', '2026-07-18 11:38:11'),
(3, 'fsdfsdf', 'fsdfsdf', 'Domastic', 'South', 'Nikhil', '2026-07-18 11:37:06', 'Nikhil', '2026-07-18 11:49:53'),
(4, 'ASDFGHJK', 'ASDFGHJK', 'Domastic', 'Central', 'Nikhil', '2026-07-20 06:13:23', 'Nikhil', '2026-07-20 06:13:23'),
(5, 'ASDFGHJK', 'ASDFHFGJG', 'Domastic', 'North', 'Nikhil', '2026-07-20 04:56:44', 'Nikhil', '2026-07-20 06:13:19'),
(6, 'ASDFGHJK', 'ASDFHFGJG', 'Export', 'Export', 'Nikhil', '2026-07-20 06:13:33', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `department_master`
--

CREATE TABLE `department_master` (
  `id` int(11) NOT NULL,
  `department_name` varchar(50) NOT NULL,
  `created_by` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department_master`
--

INSERT INTO `department_master` (`id`, `department_name`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(2, 'IT', 'Nikhil', '2026-07-14 09:53:11', NULL, NULL),
(3, 'Marketing', 'Nikhil', '2026-07-14 09:53:28', NULL, NULL),
(4, 'HR and Admin', 'Nikhil', '2026-07-14 09:54:17', 'Nikhil', NULL),
(5, 'Molding Extrogen', 'Nikhil', '2026-07-14 09:56:44', 'Nikhil', NULL),
(6, 'Molding', 'Nikhil', '2026-07-20 06:08:59', 'Nikhil', NULL),
(7, 'as', 'Nikhil', '2026-07-20 06:09:03', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `designation_master`
--

CREATE TABLE `designation_master` (
  `id` int(11) NOT NULL,
  `designation` varchar(50) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `designation_master`
--

INSERT INTO `designation_master` (`id`, `designation`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(2, 'Employee', 'Nikhil', '2026-07-16 11:15:39', NULL, NULL),
(3, 'Manager', 'Nikhil', '2026-07-16 11:20:13', 'Nikhil', '2026-07-16 11:20:13'),
(4, 'Plant Head', 'Nikhil', '2026-07-16 11:20:06', 'Nikhil', '2026-07-16 11:20:06'),
(5, 'Top Manager', 'Nikhil', '2026-07-16 11:19:55', 'Nikhil', '2026-07-16 11:19:55'),
(6, 'HOD', 'Nikhil', '2026-07-16 11:19:46', 'Nikhil', '2026-07-16 11:19:46'),
(7, 'Opration', 'Nikhil', '2026-07-20 06:09:47', 'Nikhil', '2026-07-20 06:09:47'),
(9, 'Opration', 'Nikhil', '2026-07-20 06:09:54', 'Nikhil', '2026-07-20 06:16:10');

-- --------------------------------------------------------

--
-- Table structure for table `employee_master`
--

CREATE TABLE `employee_master` (
  `id` int(11) NOT NULL,
  `employee_name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `location` varchar(20) NOT NULL,
  `contact_no` varchar(20) NOT NULL,
  `designation_id` varchar(10) NOT NULL,
  `level` varchar(20) NOT NULL,
  `department_id` varchar(10) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_master`
--

INSERT INTO `employee_master` (`id`, `employee_name`, `email`, `location`, `contact_no`, `designation_id`, `level`, `department_id`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(1, 'Nikhil Patil', 'adityp1812@gmail.com', 'pune', '+24 31649633331', '2', 'Level 1', '2', 'Nikhil', '2026-07-18 06:24:55', 'Nikhil', '2026-07-20 06:39:18'),
(2, 'Nikhil Patil', 'adityp1812@gmail.com', 'pune', '+24 31649633331', '2', 'Level 1', '2', 'Nikhil', '2026-07-20 06:39:02', 'Nikhil', '2026-07-20 06:39:21'),
(3, 'rutuja', 'rutuja@jppl.com', 'Moi', '+24 31649633331', '3', 'Level 2', '2', 'Nikhil', '2026-07-20 06:38:44', 'Nikhil', '2026-07-20 06:38:44'),
(4, 'rutuja', 'adityp1812@gmail.com', 'moi', '+24 3164963333131', '3', 'Level 1', '2', 'Nikhil', '2026-07-20 06:38:49', 'Nikhil', '2026-07-20 06:38:49'),
(5, 'Nikhil Patil', 'adityp1812@gmail.com', 'pune', '+24 31649633331', '2', 'Level 1', '2', 'Nikhil', '2026-07-18 08:37:44', NULL, NULL),
(6, 'rutuja', 'adityp1812@gmail.com', 'punea', '+24 31649633331', '6', 'Level 1', '4', 'Nikhil', '2026-07-20 04:59:14', 'Nikhil', '2026-07-20 06:16:29');

-- --------------------------------------------------------

--
-- Table structure for table `hist_category_master`
--

CREATE TABLE `hist_category_master` (
  `id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hist_category_master`
--

INSERT INTO `hist_category_master` (`id`, `category_name`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(4, 'sdsa', 'Nikhil', '2026-07-20 05:15:22', 'Nikhil', '2026-07-20 05:29:43'),
(3, 'abc', 'Nikhil', '2026-07-16 12:35:29', 'Nikhil', '2026-07-20 05:56:49'),
(7, 'a', 'Nikhil', '2026-07-20 06:07:08', 'Nikhil', '2026-07-20 06:07:32');

-- --------------------------------------------------------

--
-- Table structure for table `hist_compound_master`
--

CREATE TABLE `hist_compound_master` (
  `id` int(11) NOT NULL,
  `compound_code` varchar(20) NOT NULL,
  `polymer` varchar(20) NOT NULL,
  `im_code` varchar(20) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hist_currency_master`
--

CREATE TABLE `hist_currency_master` (
  `id` int(11) NOT NULL,
  `currency_name` varchar(100) NOT NULL,
  `currency_symbol` varchar(20) NOT NULL,
  `exchange_rate` decimal(18,6) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hist_currency_master`
--

INSERT INTO `hist_currency_master` (`id`, `currency_name`, `currency_symbol`, `exchange_rate`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(5, 'USD', 'asd', 943.000000, 'Nikhil', '2026-07-20 06:10:51', 'Nikhil', '2026-07-20 06:33:39');

-- --------------------------------------------------------

--
-- Table structure for table `hist_customer_master`
--

CREATE TABLE `hist_customer_master` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(50) NOT NULL,
  `sub_customer` varchar(50) NOT NULL,
  `geo_type` varchar(20) NOT NULL,
  `zone` varchar(20) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hist_customer_master`
--

INSERT INTO `hist_customer_master` (`id`, `customer_name`, `sub_customer`, `geo_type`, `zone`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(1, 'ASDFGHJK', '', '', '', 'Nikhil', '2026-07-16 07:19:55', 'Nikhil', '2026-07-16 09:05:23');

-- --------------------------------------------------------

--
-- Table structure for table `hist_department_master`
--

CREATE TABLE `hist_department_master` (
  `id` int(11) NOT NULL,
  `department_name` varchar(50) NOT NULL,
  `created_by` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hist_designation_master`
--

CREATE TABLE `hist_designation_master` (
  `id` int(11) NOT NULL,
  `designation` varchar(50) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hist_designation_master`
--

INSERT INTO `hist_designation_master` (`id`, `designation`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(1, 'fafaf', 'Nikhil', '2026-07-16 11:15:23', 'Nikhil', '2026-07-16 11:15:42'),
(8, 'MIS Manager', 'Nikhil', '2026-07-16 11:19:33', 'Nikhil', '2026-07-20 06:09:33');

-- --------------------------------------------------------

--
-- Table structure for table `hist_employee_master`
--

CREATE TABLE `hist_employee_master` (
  `id` int(11) NOT NULL,
  `employee_name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `location` varchar(20) NOT NULL,
  `contact_no` varchar(20) NOT NULL,
  `designation_id` varchar(10) NOT NULL,
  `level` varchar(20) NOT NULL,
  `department_id` varchar(10) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hist_employee_master`
--

INSERT INTO `hist_employee_master` (`id`, `employee_name`, `email`, `location`, `contact_no`, `designation_id`, `level`, `department_id`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(7, 'dsa', 'sadas@sada.com', 'asdasd', 'asdas', '2', 'Level 1', '4', 'Nikhil', '2026-07-20 06:38:21', 'Nikhil', '2026-07-20 06:38:26');

-- --------------------------------------------------------

--
-- Table structure for table `hist_incoterms_master`
--

CREATE TABLE `hist_incoterms_master` (
  `id` int(11) NOT NULL,
  `incoterms` varchar(100) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hist_part_master`
--

CREATE TABLE `hist_part_master` (
  `id` int(11) NOT NULL,
  `part_name` varchar(100) NOT NULL,
  `part_no` varchar(20) NOT NULL,
  `fg_code` varchar(50) NOT NULL,
  `im_code` varchar(50) NOT NULL,
  `department_id` varchar(20) NOT NULL,
  `sub_department_id` varchar(20) NOT NULL,
  `customer_id` varchar(20) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hist_plant_master`
--

CREATE TABLE `hist_plant_master` (
  `id` int(11) NOT NULL,
  `unit_id` varchar(255) NOT NULL,
  `plant_name` varchar(255) NOT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(100) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hist_plant_master`
--

INSERT INTO `hist_plant_master` (`id`, `unit_id`, `plant_name`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(1, '19,16', 'safa', 'Nikhil', '2026-07-15 14:38:08', 'Nikhil', '2026-07-15 14:39:22'),
(5, '19,18,21,16,17,22', 'qwertyuiop', 'Nikhil', '2026-07-16 15:19:42', 'Nikhil', '2026-07-20 11:34:55');

-- --------------------------------------------------------

--
-- Table structure for table `hist_sub_category_master`
--

CREATE TABLE `hist_sub_category_master` (
  `id` int(11) NOT NULL,
  `supplier_id` varchar(20) DEFAULT NULL,
  `category_id` varchar(20) NOT NULL,
  `sub_category_name` varchar(50) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hist_sub_category_master`
--

INSERT INTO `hist_sub_category_master` (`id`, `supplier_id`, `category_id`, `sub_category_name`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(3, '1', '2', 'wesdrcftvgb', 'Nikhil', '2026-07-16 09:50:47', 'Nikhil', '2026-07-16 09:54:59'),
(4, '1', '1', 'aDSADASDA', 'Nikhil', '2026-07-16 09:54:28', 'Nikhil', '2026-07-16 09:55:04');

-- --------------------------------------------------------

--
-- Table structure for table `hist_sub_department_master`
--

CREATE TABLE `hist_sub_department_master` (
  `id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `sub_department_name` varchar(50) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hist_sub_department_master`
--

INSERT INTO `hist_sub_department_master` (`id`, `unit_id`, `department_id`, `sub_department_name`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(11, 16, 2, 'asdasd', 'Nikhil', '2026-07-15 07:22:50', NULL, NULL),
(13, 16, 2, 's', 'Nikhil', '2026-07-15 07:38:01', 'Nikhil', '2026-07-15 07:38:01');

-- --------------------------------------------------------

--
-- Table structure for table `hist_supplier_master`
--

CREATE TABLE `hist_supplier_master` (
  `id` int(11) NOT NULL,
  `supplier_name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `contact_no` varchar(20) NOT NULL,
  `location` varchar(50) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hist_unit_master`
--

CREATE TABLE `hist_unit_master` (
  `id` int(11) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `address` varchar(100) NOT NULL,
  `created_by` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `location` varchar(50) NOT NULL,
  `updated_by` varchar(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `incoterms_master`
--

CREATE TABLE `incoterms_master` (
  `id` int(11) NOT NULL,
  `incoterms` varchar(100) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `incoterms_master`
--

INSERT INTO `incoterms_master` (`id`, `incoterms`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(1, 'FOB', 'Nikhil', '2026-07-16 12:15:32', 'Nikhil', '2026-07-20 06:16:39'),
(2, 'aaxax', 'Nikhil', '2026-07-20 06:10:06', 'Nikhil', '2026-07-20 06:10:17');

-- --------------------------------------------------------

--
-- Table structure for table `login`
--

CREATE TABLE `login` (
  `id` int(20) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `first_login` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `user_id` varchar(10) NOT NULL,
  `created_by` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login`
--

INSERT INTO `login` (`id`, `username`, `password`, `first_login`, `created_at`, `user_id`, `created_by`) VALUES
(1, 'nikhilpatil@jayshreepolymer.com', '$2y$10$vHfObzPn3AZ5ovUnMfGRiuEMbXd8E9qd0XkKncpn1IOksSFl1ZTqi', 0, '2026-07-18 07:20:04', 'Nikhil', 'NIkhil'),
(2, 'rutuja@jayashreepolymer.com', '$2y$10$vHfObzPn3AZ5ovUnMfGRiuEMbXd8E9qd0XkKncpn1IOksSFl1ZTqi', 0, '2026-07-18 07:20:04', 'rutuja', 'rutuja'),
(3, 'rutuja@jppl.com', '$2y$10$HULtQH5r/jzospNJbYyN0OWJChwVz.LRkL0CfZqprLlnSer.OtfV.', 1, '2026-07-18 07:38:53', '3', 'Nikhil'),
(4, 'adityp1812@gmail.com', '$2y$10$iLW7oLlBXgAYlMpVh1coFuwtz.i8WRh3RRS5nkR2cSa.tjQcPwFc2', 1, '2026-07-18 07:54:15', '4', 'Nikhil'),
(5, 'adityp1812@gmail.com', '$2y$10$i3305l8KZrHpgiJ/ELi9teWHrz7qKXsrracn8plCIQcc52nKzrpLi', 1, '2026-07-18 07:56:38', '1', '0'),
(6, 'adityp1812@gmail.com', '$2y$10$RpfsLuRljrrhPR1tYpBBGuudF8ksdWDNCcFtIGEs2sfWLQO3QaE7e', 1, '2026-07-18 08:37:44', '5', 'Nikhil'),
(7, 'adityp1812@gmail.com', '$2y$10$NWb1MWea11Dn22l.KnWBWujp5cetjIrsJh7Ta3tyaj77pgwnfgtRK', 1, '2026-07-20 04:59:14', '6', 'Nikhil'),
(8, 'sadas@sada.com', '$2y$10$YQ3rbJlZha0KUzbaHMO9m.jDY7X.vgZ9mITZ.ZoS/BcXLALQMEhd.', 1, '2026-07-20 06:14:35', '7', 'Nikhil'),
(9, 'adityp1812@gmail.com', '$2y$10$E9BsM/SaTkP.Lqt.9SxiwOaGbQjdz1X5iXBs7DvNxZWGASvnhDxD6', 1, '2026-07-20 06:38:33', '2', 'Nikhil');

-- --------------------------------------------------------

--
-- Table structure for table `part_master`
--

CREATE TABLE `part_master` (
  `id` int(11) NOT NULL,
  `part_name` varchar(100) NOT NULL,
  `part_no` varchar(20) NOT NULL,
  `fg_code` varchar(50) NOT NULL,
  `im_code` varchar(50) NOT NULL,
  `department_id` varchar(20) NOT NULL,
  `sub_department_id` varchar(20) NOT NULL,
  `customer_id` varchar(20) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `part_master`
--

INSERT INTO `part_master` (`id`, `part_name`, `part_no`, `fg_code`, `im_code`, `department_id`, `sub_department_id`, `customer_id`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(1, 'adsa', '0', 'asda', 'sd', '3', '4', '3', 'Nikhil', '2026-07-20 04:51:05', 'Nikhil', '2026-07-20 04:52:07'),
(2, 'asasafs', 'asasasa', 'dsadas', 'dasdada', '3', '4', '4', 'Nikhil', '2026-07-20 04:55:36', 'Nikhil', '2026-07-20 04:55:51'),
(3, 'asasafs', 'sda', 'asda', 'sd', '3', '4', '6', 'Nikhil', '2026-07-20 06:14:00', 'Nikhil', '2026-07-20 06:17:58');

-- --------------------------------------------------------

--
-- Table structure for table `plant_master`
--

CREATE TABLE `plant_master` (
  `id` int(11) NOT NULL,
  `unit_id` varchar(255) NOT NULL,
  `plant_name` varchar(255) NOT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(100) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plant_master`
--

INSERT INTO `plant_master` (`id`, `unit_id`, `plant_name`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(2, '17', 'safa', 'Nikhil', '2026-07-15 14:54:03', 'Nikhil', '2026-07-20 11:32:45'),
(3, '18,16,17', 'sa', 'Nikhil', '2026-07-15 16:06:25', 'Nikhil', '2026-07-20 11:35:19'),
(4, '21,16', 'jppl extrogen', 'Nikhil', '2026-07-15 16:07:40', 'Nikhil', '2026-07-20 11:35:42'),
(6, '18,21,16', 'safaf', 'Nikhil', '2026-07-20 11:35:09', 'Nikhil', '2026-07-20 11:36:04'),
(7, '18', 'qwertyui', 'Nikhil', '2026-07-20 11:35:50', 'Nikhil', '2026-07-20 11:45:48');

-- --------------------------------------------------------

--
-- Table structure for table `sub_category_master`
--

CREATE TABLE `sub_category_master` (
  `id` int(11) NOT NULL,
  `supplier_id` varchar(20) DEFAULT NULL,
  `category_id` varchar(20) NOT NULL,
  `sub_category_name` varchar(50) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sub_category_master`
--

INSERT INTO `sub_category_master` (`id`, `supplier_id`, `category_id`, `sub_category_name`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(1, '1', '2', 'asfsf', 'Nikhil', '2026-07-16 09:27:55', NULL, NULL),
(2, '1', '1', 'wesdrcftvgb', 'Nikhil', '2026-07-16 09:50:19', NULL, NULL),
(5, '1,2', '2', 'aDSADASDA', 'Nikhil', '2026-07-16 09:56:16', 'Nikhil', '2026-07-16 09:56:30'),
(6, '1,3', '3', 'AbCD', 'Nikhil', '2026-07-16 12:36:13', NULL, NULL),
(7, NULL, '3', 'das', 'Nikhil', '2026-07-18 09:25:21', NULL, NULL),
(8, '2', '6', 'aDSADASDA', 'Nikhil', '2026-07-20 06:12:56', 'Nikhil', '2026-07-20 06:17:39');

-- --------------------------------------------------------

--
-- Table structure for table `sub_department_master`
--

CREATE TABLE `sub_department_master` (
  `id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `sub_department_name` varchar(50) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sub_department_master`
--

INSERT INTO `sub_department_master` (`id`, `unit_id`, `department_id`, `sub_department_name`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(1, 18, 2, 'sowtware', 'Nikhil', '2026-07-15 05:07:46', NULL, NULL),
(2, 18, 6, 'MTRB', 'Nikhil', '2026-07-15 05:55:20', NULL, NULL),
(3, 17, 4, 'hr', 'Nikhil', '2026-07-15 06:06:25', NULL, NULL),
(4, 18, 3, 'assa', 'Nikhil', '2026-07-15 06:07:06', NULL, NULL),
(5, 17, 2, 'sowtware', 'Nikhil', '2026-07-15 06:10:06', NULL, NULL),
(6, 18, 3, 'assa', 'Nikhil', '2026-07-15 06:15:50', NULL, NULL),
(7, 17, 3, 'MTRB', 'Nikhil', '2026-07-15 06:16:14', NULL, NULL),
(8, 16, 2, 'sowtware', 'Nikhil', '2026-07-15 06:43:46', NULL, NULL),
(9, 16, 6, 'sowtware', 'Nikhil', '2026-07-15 06:49:03', NULL, NULL),
(10, 18, 6, 'safasfsadas', 'Nikhil', '2026-07-15 07:18:54', NULL, NULL),
(12, 18, 6, 'asdasdasdasdas', 'Nikhil', '2026-07-20 06:11:23', 'Nikhil', '2026-07-20 06:11:23'),
(14, 17, 4, 'c', 'Nikhil', '2026-07-15 07:38:11', NULL, NULL),
(15, 19, 4, 'MTRB', 'Nikhil', '2026-07-20 06:11:31', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `supplier_master`
--

CREATE TABLE `supplier_master` (
  `id` int(11) NOT NULL,
  `supplier_name` varchar(50) NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `location` varchar(50) DEFAULT NULL,
  `created_by` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier_master`
--

INSERT INTO `supplier_master` (`id`, `supplier_name`, `email`, `contact_no`, `location`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(1, 'asdasfas', 'zczx@sjbksj.com', '+24 31649633331', 'asdasdad', 'Nikhil', '2026-07-16 06:50:02', 'Nikhil', '2026-07-16 09:04:11'),
(2, 'assd', 'czxcx@gmail.com', '+91 4653168461', 'zczcz', 'Nikhil', '2026-07-16 09:04:04', NULL, NULL),
(3, 'nikhil', '', '', '', 'Nikhil', '2026-07-20 06:11:41', 'Nikhil', '2026-07-20 06:11:46'),
(4, 'asdasfas', 'adityp1812@gmail.com', '+24 31649633331', 'pune', 'Nikhil', '2026-07-20 06:11:54', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `unit_master`
--

CREATE TABLE `unit_master` (
  `id` int(11) NOT NULL,
  `unit` varchar(10) DEFAULT NULL,
  `address` varchar(255) NOT NULL,
  `created_by` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `location` varchar(50) DEFAULT NULL,
  `updated_by` varchar(10) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `unit_master`
--

INSERT INTO `unit_master` (`id`, `unit`, `address`, `created_by`, `created_at`, `location`, `updated_by`, `updated_at`) VALUES
(16, 'unit 4', 'qwer', 'Nikhil', '2026-07-13 07:01:35', 'pune', 'Nikhil', '2026-07-15 05:38:55'),
(17, 'unit 5', 'werfdczx', 'Nikhil', '2026-07-13 07:04:10', 'pune', 'Nikhil', '2026-07-15 05:41:38'),
(18, 'unit 16', 'Moi chhakan', 'Nikhil', '2026-07-13 07:31:10', 'Moi', 'Nikhil', NULL),
(19, 'unit 1', 'pimpr', 'Nikhil', '2026-07-13 10:37:22', 'pune', 'Nikhil', NULL),
(20, 'unit 9', 'karve nagar', 'Nikhil', '2026-07-15 04:29:44', 'pune', 'Nikhil', '2026-07-15 05:51:25'),
(21, 'unit 3', 'zxcv', 'Nikhil', '2026-07-15 09:07:24', 'moi', 'Nikhil', '2026-07-20 06:08:26'),
(22, 'unit 5', 'asdfh', 'Nikhil', '2026-07-16 07:14:15', 'moi', 'Nikhil', '2026-07-20 06:08:19'),
(23, 'unit 2', 'aSDASD', 'Nikhil', '2026-07-20 06:08:32', 'pune', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category_master`
--
ALTER TABLE `category_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `compound_master`
--
ALTER TABLE `compound_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `currency_master`
--
ALTER TABLE `currency_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer_master`
--
ALTER TABLE `customer_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `department_master`
--
ALTER TABLE `department_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `designation_master`
--
ALTER TABLE `designation_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employee_master`
--
ALTER TABLE `employee_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hist_part_master`
--
ALTER TABLE `hist_part_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hist_sub_department_master`
--
ALTER TABLE `hist_sub_department_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `incoterms_master`
--
ALTER TABLE `incoterms_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login`
--
ALTER TABLE `login`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `part_master`
--
ALTER TABLE `part_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `plant_master`
--
ALTER TABLE `plant_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sub_category_master`
--
ALTER TABLE `sub_category_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sub_department_master`
--
ALTER TABLE `sub_department_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supplier_master`
--
ALTER TABLE `supplier_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `unit_master`
--
ALTER TABLE `unit_master`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category_master`
--
ALTER TABLE `category_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `compound_master`
--
ALTER TABLE `compound_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `currency_master`
--
ALTER TABLE `currency_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `customer_master`
--
ALTER TABLE `customer_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `department_master`
--
ALTER TABLE `department_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `designation_master`
--
ALTER TABLE `designation_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `employee_master`
--
ALTER TABLE `employee_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `incoterms_master`
--
ALTER TABLE `incoterms_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `login`
--
ALTER TABLE `login`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `part_master`
--
ALTER TABLE `part_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `plant_master`
--
ALTER TABLE `plant_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `sub_category_master`
--
ALTER TABLE `sub_category_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `sub_department_master`
--
ALTER TABLE `sub_department_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `supplier_master`
--
ALTER TABLE `supplier_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `unit_master`
--
ALTER TABLE `unit_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
