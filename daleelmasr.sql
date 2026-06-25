-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 29, 2026 at 05:01 PM
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
-- Database: `daleelmasr`
--

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `governorate_id` bigint(20) UNSIGNED NOT NULL,
  `entity_type` enum('education','azhar') NOT NULL DEFAULT 'education',
  `main_warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `operation_warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `manager_name` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `code`, `name`, `governorate_id`, `entity_type`, `main_warehouse_id`, `operation_warehouse_id`, `manager_name`, `phone`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '11', 'المنزلة التعليمية', 1, 'education', 1, 2, NULL, NULL, 1, NULL, NULL, NULL),
(3, '110', 'بني عبيد التعليمية', 1, 'education', 1, 3, NULL, NULL, 1, NULL, NULL, NULL),
(4, '117', 'دكرنس التعليمية', 1, 'education', 1, 5, NULL, NULL, 1, NULL, NULL, NULL),
(5, '47', 'منية النصر التعليمية', 1, 'education', 1, 9, NULL, NULL, 1, NULL, NULL, NULL),
(6, '470', 'الجمالية التعليمية', 1, 'education', 1, 7, NULL, NULL, 1, NULL, NULL, NULL),
(7, '48', 'ميت سلسيل التعليمية', 1, 'education', 1, 8, NULL, NULL, 1, NULL, NULL, NULL),
(9, '482', 'المطرية التعليمية', 1, 'education', 1, 6, NULL, NULL, 1, NULL, NULL, NULL),
(10, '481', 'طلخا التعليمية', 1, 'education', 1, 4, NULL, NULL, 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `department_allocations`
--

CREATE TABLE `department_allocations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `receite_date` date NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `total_meals` int(11) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `department_allocations`
--

INSERT INTO `department_allocations` (`id`, `receite_date`, `department_id`, `created_by`, `total_meals`, `notes`, `created_at`, `updated_at`) VALUES
(9, '2026-05-29', 5, 1, 23880, NULL, '2026-05-29 04:30:27', '2026-05-29 04:30:27');

-- --------------------------------------------------------

--
-- Table structure for table `department_allocation_items`
--

CREATE TABLE `department_allocation_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `allocation_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_meals` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `department_allocation_items`
--

INSERT INTO `department_allocation_items` (`id`, `allocation_id`, `product_id`, `quantity`, `total_meals`, `created_at`, `updated_at`) VALUES
(7, 9, 1, 199, 23880, '2026-05-29 04:30:27', '2026-05-29 04:30:27');

-- --------------------------------------------------------

--
-- Table structure for table `distribution_orders`
--

CREATE TABLE `distribution_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `receite_number` varchar(255) DEFAULT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `receite_date` date NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `notes` text DEFAULT NULL,
  `delivery_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `distribution_order_details`
--

CREATE TABLE `distribution_order_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `distribution_order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `governorates`
--

CREATE TABLE `governorates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `manager_name` varchar(255) DEFAULT NULL,
  `manager_phone` varchar(20) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `governorates`
--

INSERT INTO `governorates` (`id`, `name`, `code`, `manager_name`, `manager_phone`, `status`, `sort_order`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'الدقهلية', '1', NULL, NULL, 1, 0, NULL, '2026-05-03 02:41:22', '2026-05-03 02:41:22');

-- --------------------------------------------------------

--
-- Table structure for table `inventories`
--

CREATE TABLE `inventories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `last_movement_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventories`
--

INSERT INTO `inventories` (`id`, `product_id`, `warehouse_id`, `quantity`, `last_movement_at`, `created_at`, `updated_at`) VALUES
(43, 4, 1, 600, '2026-05-29 11:57:40', '2026-05-29 11:57:40', '2026-05-29 11:57:40'),
(44, 3, 4, 600, '2026-05-29 11:58:55', '2026-05-29 11:58:55', '2026-05-29 11:58:55'),
(45, 3, 5, 600, '2026-05-29 11:59:55', '2026-05-29 11:59:55', '2026-05-29 11:59:55');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_transactions`
--

CREATE TABLE `inventory_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('in','out','transfer_in','transfer_out') NOT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_transactions`
--

INSERT INTO `inventory_transactions` (`id`, `product_id`, `warehouse_id`, `type`, `reference_number`, `quantity`, `notes`, `user_id`, `created_at`, `updated_at`) VALUES
(59, 4, 1, 'in', '224489', 600, 'استلام شحنة - 224489 (كانت: 0، أصبحت: 600)', 1, '2026-05-29 11:57:40', '2026-05-29 11:57:40'),
(60, 3, 4, 'in', '24488', 600, 'استلام شحنة - 24488 (كانت: 0، أصبحت: 600)', 1, '2026-05-29 11:58:55', '2026-05-29 11:58:55'),
(61, 3, 5, 'in', '224486', 600, 'استلام شحنة - 224486 (كانت: 0، أصبحت: 600)', 1, '2026-05-29 11:59:55', '2026-05-29 11:59:55');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2026_05_03_051653_create_department_allocations_table', 1),
(2, '2026_05_03_233511_create_department_allocation_items_table', 2),
(3, '2026_05_28_123055_create_stock_transfers_table', 3),
(4, '2026_05_28_123225_create_stock_transfer_items_table', 4);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `sku` varchar(255) NOT NULL,
  `purchase_unit` varchar(255) NOT NULL DEFAULT 'كرتونة',
  `issue_unit` varchar(255) NOT NULL DEFAULT 'وجبة',
  `conversion_factor` int(11) NOT NULL,
  `expiry_duration` int(11) NOT NULL DEFAULT 6,
  `companion_product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_base` tinyint(1) NOT NULL DEFAULT 0,
  `total_quantity_pax` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `sku`, `purchase_unit`, `issue_unit`, `conversion_factor`, `expiry_duration`, `companion_product_id`, `is_base`, `total_quantity_pax`, `created_at`, `updated_at`) VALUES
(1, 'سادة 40 جم', '1', 'كرتونة', 'وجبة', 120, 6, 1, 1, 0, NULL, NULL),
(2, 'سادة 80 جم', '2', 'كرتونة', 'وجبة', 120, 6, 1, 0, 120, NULL, NULL),
(3, 'ويفر 45 جم', '3', 'كرتونة', 'وجبة', 120, 3, 1, 0, 120, NULL, NULL),
(4, 'عجوة 80 جم', '4', 'كرتونة', 'وجبة', 120, 3, 1, 0, 120, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_supplier`
--

CREATE TABLE `product_supplier` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `supplier_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `receiving_orders`
--

CREATE TABLE `receiving_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `document_number` varchar(50) NOT NULL,
  `batch_number` varchar(255) DEFAULT NULL,
  `warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `supplier_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `samples_quantity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `arrival_time` datetime DEFAULT NULL,
  `departure_time` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `receiving_orders`
--

INSERT INTO `receiving_orders` (`id`, `document_number`, `batch_number`, `warehouse_id`, `product_id`, `supplier_id`, `quantity`, `samples_quantity`, `arrival_time`, `departure_time`, `notes`, `user_id`, `created_at`, `updated_at`) VALUES
(57, '224489', NULL, 1, 4, 1, 600, 0, '2026-01-21 17:56:00', '2026-01-21 17:57:00', NULL, 1, '2026-05-29 11:57:40', '2026-05-29 11:57:40'),
(58, '24488', NULL, 4, 3, 1, 600, 0, '2026-01-21 17:58:00', '2026-01-21 17:58:00', NULL, 1, '2026-05-29 11:58:55', '2026-05-29 11:58:55'),
(59, '224486', NULL, 5, 3, 1, 600, 0, '2026-01-21 17:59:00', '2026-01-21 17:59:00', NULL, 1, '2026-05-29 11:59:55', '2026-05-29 11:59:55');

-- --------------------------------------------------------

--
-- Table structure for table `schools`
--

CREATE TABLE `schools` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `silocode` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `type` enum('ابتدائي','اعدادي','رياض اطفال','تعليم مجتمعي') DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_transfers`
--

CREATE TABLE `stock_transfers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `from_warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `to_warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('permanent','custody') NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_transfer_items`
--

CREATE TABLE `stock_transfer_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `stock_transfer_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `code`, `contact_person`, `phone`, `status`, `created_at`, `updated_at`) VALUES
(1, 'سايلو', '1', NULL, NULL, 1, NULL, NULL),
(2, 'الناصر', '2', NULL, NULL, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'ahmed', '', NULL, '', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `warehouses`
--

CREATE TABLE `warehouses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` bigint(20) UNSIGNED DEFAULT NULL,
  `governorate_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` enum('main','sub','dispatch_point') NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `manager_name` varchar(255) DEFAULT NULL,
  `manager_phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `warehouses`
--

INSERT INTO `warehouses` (`id`, `name`, `code`, `governorate_id`, `type`, `parent_id`, `manager_name`, `manager_phone`, `address`, `status`, `created_at`, `updated_at`) VALUES
(1, 'المنزلة الرئيسي', NULL, 1, 'main', NULL, NULL, NULL, NULL, 1, '2026-05-03 02:41:48', '2026-05-03 02:41:48'),
(2, 'المنزلة ', NULL, 1, 'dispatch_point', 1, NULL, NULL, NULL, 1, '2026-05-03 02:41:48', '2026-05-03 02:41:48'),
(3, 'بني عبيد', NULL, NULL, 'sub', 1, NULL, NULL, NULL, 1, '2026-05-03 02:45:18', '2026-05-03 02:45:18'),
(4, 'طلخا', NULL, NULL, 'sub', 1, NULL, NULL, NULL, 1, '2026-05-09 17:34:22', '2026-05-09 17:34:22'),
(5, 'دكرنس', NULL, NULL, 'sub', 1, NULL, NULL, NULL, 1, '2026-05-09 17:34:53', '2026-05-09 17:34:53'),
(6, 'المطرية', NULL, NULL, 'dispatch_point', 1, NULL, NULL, NULL, 1, '2026-05-09 17:35:22', '2026-05-09 17:35:22'),
(7, 'الجمالية', NULL, NULL, 'dispatch_point', 1, NULL, NULL, NULL, 1, '2026-05-09 17:35:52', '2026-05-09 17:35:52'),
(8, 'ميت سلسيل', NULL, NULL, 'dispatch_point', 1, NULL, NULL, NULL, 1, '2026-05-09 17:36:07', '2026-05-09 17:36:07'),
(9, 'منية النصر', NULL, NULL, 'dispatch_point', 1, NULL, NULL, NULL, 1, '2026-05-09 17:36:23', '2026-05-09 17:36:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `departments_code_unique` (`code`),
  ADD KEY `departments_governorate_id_foreign` (`governorate_id`),
  ADD KEY `departments_main_warehouse_id_foreign` (`main_warehouse_id`),
  ADD KEY `departments_operation_warehouse_id_foreign` (`operation_warehouse_id`);

--
-- Indexes for table `department_allocations`
--
ALTER TABLE `department_allocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_allocations_created_by_foreign` (`created_by`),
  ADD KEY `department_allocations_receite_date_index` (`receite_date`),
  ADD KEY `department_allocations_department_id_index` (`department_id`);

--
-- Indexes for table `department_allocation_items`
--
ALTER TABLE `department_allocation_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_allocation_items_allocation_id_foreign` (`allocation_id`),
  ADD KEY `department_allocation_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `distribution_orders`
--
ALTER TABLE `distribution_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `distribution_orders_created_by_foreign` (`created_by`),
  ADD KEY `distribution_orders_receite_number_index` (`receite_number`),
  ADD KEY `distribution_orders_receite_date_index` (`receite_date`),
  ADD KEY `distribution_orders_school_id_index` (`school_id`);

--
-- Indexes for table `distribution_order_details`
--
ALTER TABLE `distribution_order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `distribution_order_details_distribution_order_id_foreign` (`distribution_order_id`),
  ADD KEY `distribution_order_details_product_id_foreign` (`product_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `governorates`
--
ALTER TABLE `governorates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `governorates_code_unique` (`code`),
  ADD KEY `governorates_name_index` (`name`);

--
-- Indexes for table `inventories`
--
ALTER TABLE `inventories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `inventories_product_id_warehouse_id_unique` (`product_id`,`warehouse_id`),
  ADD KEY `inventories_warehouse_id_foreign` (`warehouse_id`);

--
-- Indexes for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_transactions_product_id_foreign` (`product_id`),
  ADD KEY `inventory_transactions_warehouse_id_foreign` (`warehouse_id`),
  ADD KEY `inventory_transactions_user_id_foreign` (`user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_sku_unique` (`sku`),
  ADD KEY `products_companion_product_id_foreign` (`companion_product_id`);

--
-- Indexes for table `product_supplier`
--
ALTER TABLE `product_supplier`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_supplier_product_id_foreign` (`product_id`),
  ADD KEY `product_supplier_supplier_id_foreign` (`supplier_id`);

--
-- Indexes for table `receiving_orders`
--
ALTER TABLE `receiving_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `receiving_orders_document_number_unique` (`document_number`),
  ADD KEY `receiving_orders_warehouse_id_foreign` (`warehouse_id`),
  ADD KEY `receiving_orders_product_id_foreign` (`product_id`),
  ADD KEY `receiving_orders_supplier_id_foreign` (`supplier_id`),
  ADD KEY `receiving_orders_user_id_foreign` (`user_id`);

--
-- Indexes for table `schools`
--
ALTER TABLE `schools`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schools_department_id_foreign` (`department_id`);

--
-- Indexes for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_transfers_from_warehouse_id_foreign` (`from_warehouse_id`),
  ADD KEY `stock_transfers_to_warehouse_id_foreign` (`to_warehouse_id`);

--
-- Indexes for table `stock_transfer_items`
--
ALTER TABLE `stock_transfer_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_transfer_items_stock_transfer_id_foreign` (`stock_transfer_id`),
  ADD KEY `stock_transfer_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `suppliers_code_unique` (`code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `warehouses_code_unique` (`code`),
  ADD KEY `warehouses_governorate_id_foreign` (`governorate_id`),
  ADD KEY `warehouses_parent_id_foreign` (`parent_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `department_allocations`
--
ALTER TABLE `department_allocations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `department_allocation_items`
--
ALTER TABLE `department_allocation_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `distribution_orders`
--
ALTER TABLE `distribution_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `distribution_order_details`
--
ALTER TABLE `distribution_order_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `governorates`
--
ALTER TABLE `governorates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inventories`
--
ALTER TABLE `inventories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `product_supplier`
--
ALTER TABLE `product_supplier`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `receiving_orders`
--
ALTER TABLE `receiving_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `schools`
--
ALTER TABLE `schools`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `stock_transfer_items`
--
ALTER TABLE `stock_transfer_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `departments_governorate_id_foreign` FOREIGN KEY (`governorate_id`) REFERENCES `governorates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `departments_main_warehouse_id_foreign` FOREIGN KEY (`main_warehouse_id`) REFERENCES `warehouses` (`id`),
  ADD CONSTRAINT `departments_operation_warehouse_id_foreign` FOREIGN KEY (`operation_warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `department_allocations`
--
ALTER TABLE `department_allocations`
  ADD CONSTRAINT `department_allocations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `department_allocations_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `department_allocation_items`
--
ALTER TABLE `department_allocation_items`
  ADD CONSTRAINT `department_allocation_items_allocation_id_foreign` FOREIGN KEY (`allocation_id`) REFERENCES `department_allocations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `department_allocation_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `distribution_orders`
--
ALTER TABLE `distribution_orders`
  ADD CONSTRAINT `distribution_orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `distribution_orders_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `distribution_order_details`
--
ALTER TABLE `distribution_order_details`
  ADD CONSTRAINT `distribution_order_details_distribution_order_id_foreign` FOREIGN KEY (`distribution_order_id`) REFERENCES `distribution_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `distribution_order_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `inventories`
--
ALTER TABLE `inventories`
  ADD CONSTRAINT `inventories_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventories_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD CONSTRAINT `inventory_transactions_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `inventory_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `inventory_transactions_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_companion_product_id_foreign` FOREIGN KEY (`companion_product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `product_supplier`
--
ALTER TABLE `product_supplier`
  ADD CONSTRAINT `product_supplier_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_supplier_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `receiving_orders`
--
ALTER TABLE `receiving_orders`
  ADD CONSTRAINT `receiving_orders_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `receiving_orders_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `receiving_orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `receiving_orders_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `schools`
--
ALTER TABLE `schools`
  ADD CONSTRAINT `schools_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  ADD CONSTRAINT `stock_transfers_from_warehouse_id_foreign` FOREIGN KEY (`from_warehouse_id`) REFERENCES `warehouses` (`id`),
  ADD CONSTRAINT `stock_transfers_to_warehouse_id_foreign` FOREIGN KEY (`to_warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `stock_transfer_items`
--
ALTER TABLE `stock_transfer_items`
  ADD CONSTRAINT `stock_transfer_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `stock_transfer_items_stock_transfer_id_foreign` FOREIGN KEY (`stock_transfer_id`) REFERENCES `stock_transfers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD CONSTRAINT `warehouses_governorate_id_foreign` FOREIGN KEY (`governorate_id`) REFERENCES `governorates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `warehouses_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
