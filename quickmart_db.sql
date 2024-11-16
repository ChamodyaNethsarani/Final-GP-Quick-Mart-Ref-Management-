-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 12, 2024 at 03:20 PM
-- Server version: 8.3.0
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `quickmart`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `admin_id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(30) NOT NULL,
  `last_name` varchar(30) NOT NULL,
  `email` varchar(50) NOT NULL,
  `NIC_number` varchar(15) NOT NULL,
  `contact_number` int NOT NULL,
  `birthday` date NOT NULL,
  `gender` varchar(20) NOT NULL,
  `address` varchar(100) NOT NULL,
  `password` varchar(10) NOT NULL,
  PRIMARY KEY (`admin_id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `first_name`, `last_name`, `email`, `NIC_number`, `contact_number`, `birthday`, `gender`, `address`, `password`) VALUES
(18, 'chamodya', 'nethsarani', 'neth@gmail.com', '200264502371', 711151700, '2002-05-24', 'Female', 'horana', 'qwerty'),
(17, 'chamodya', 'yatawathura', 'nethsaranichamodya@gmail.com', '200264502371', 711151700, '2002-02-04', 'Female', 'horana', 'sandin123'),
(14, '', '', 'sandinkodagoda@gmail.com', '', 0, '0000-00-00', '', '', '123456789'),
(19, 'chamodya', 'yatawathura', 'cha111@gmail.com', '200264502371', 711151700, '2024-09-16', 'Male', 'Mawathgama,halthota', '111222');

-- --------------------------------------------------------

--
-- Table structure for table `commission`
--

DROP TABLE IF EXISTS `commission`;
CREATE TABLE IF NOT EXISTS `commission` (
  `commission_id` int NOT NULL AUTO_INCREMENT,
  `category` varchar(100) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `number_of_sales` int NOT NULL,
  `commission_rate` int NOT NULL,
  PRIMARY KEY (`commission_id`)
) ENGINE=MyISAM AUTO_INCREMENT=173 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `commission`
--

INSERT INTO `commission` (`commission_id`, `category`, `product_name`, `number_of_sales`, `commission_rate`) VALUES
(9, 'Biscuit', 'Tikiri Marie', 1000, 7),
(5, 'Biscuit', 'Choco choc', 1200, 5),
(6, 'Biscuit', 'Chick bitzz', 200, 12),
(170, 'Spices', 'Curry Powder', 1200, 4),
(171, 'Cooking essential', 'Coconut oil', 500, 10),
(172, 'Biscuit', 'Sugar', 2000, 8);

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

DROP TABLE IF EXISTS `employee`;
CREATE TABLE IF NOT EXISTS `employee` (
  `employee_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `contact_number` int NOT NULL,
  `address` varchar(100) NOT NULL,
  `birthday` date NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `position` enum('Admin','Employee') NOT NULL DEFAULT 'Employee',
  PRIMARY KEY (`employee_id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`employee_id`, `username`, `email`, `contact_number`, `address`, `birthday`, `password`, `position`) VALUES
(20, 'Chamodya', 'chamodya@gmail.com', 711151700, 'Mawathgama,Halthota', '2002-05-24', '$2y$10$U.IfD4DhV3Hea9f.yL4dC.LY.7i0QuWI8kR3DKPg7YyDDVDXzDYqC', 'Admin'),
(18, 'Dulyana', 'dul@gmail.com', 756248757, 'Galanigama,Bandaragama', '2002-07-29', '$2y$10$VSuCAr56SRYXw27wutRObOBHlusaLLFBH1qgbFf.MK1UdiAoe1C.K', 'Employee'),
(21, 'Sandin Kodagoda', 'sandin@gmail.com', 711093799, '179dszknbvjxsdb', '2010-06-30', '$2y$10$.OpMVowDw.7pcAJNVBwcR.mg1/ZoLMMtMYrKpiGzw1JgrHq9.y11O', 'Admin'),
(22, 'Dewindi', 'dew@gmail.com', 711093799, 'Govinna,Horana', '2002-09-27', '$2y$10$Rd.lNtd5sddFm0keE1AU.eAvhfGRQeMWKO3eKUZohUr82OOLGn1Sm', 'Employee'),
(23, 'mithila malshan', 'mithila@gmail.com', 702050608, '157/1,Mawathgama,Halthota', '1998-02-12', '$2y$10$x1Ff2qeqb3UKbVDqVgmtqumDvEmK2YEzVZkPxYUKgeKVDIwQKRp3W', 'Employee'),
(24, 'admin', 'admin@quickmart.com', 0, '', '0000-00-00', '$2y$10$AIBfvZ1wyLoS5DQmPr4sbep0H30Jk.QKetqH6KTQCqZ2M84vjxstG', 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

DROP TABLE IF EXISTS `product`;
CREATE TABLE IF NOT EXISTS `product` (
  `product_id` int NOT NULL AUTO_INCREMENT,
  `product_name` varchar(50) NOT NULL,
  `category` varchar(50) NOT NULL,
  `size` varchar(50) NOT NULL,
  `profit_price` decimal(10,0) NOT NULL,
  `shop_price` decimal(10,0) NOT NULL,
  `retail_price` decimal(10,0) NOT NULL,
  `qty` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `product_name`, `category`, `size`, `profit_price`, `shop_price`, `retail_price`, `qty`) VALUES
(62, 'Tikiri Marie', 'Biscuit', '360g', 40, 300, 330, 2496),
(60, 'Curry Powder', 'Spices', '100g', 20, 120, 150, 1499),
(61, 'Coconut oil', 'Cooking Essential', '1L', 50, 600, 640, 2398),
(64, 'Chiliie Powder', 'Spices', '100g', 20, 130, 150, 2498),
(65, 'Choco choc', 'Choclate', '75g', 30, 200, 240, 2499),
(66, 'Chick bitzz', 'Snacks', '100g', 25, 100, 130, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
CREATE TABLE IF NOT EXISTS `sales` (
  `sale_id` int NOT NULL AUTO_INCREMENT,
  `shop_name` varchar(255) DEFAULT NULL,
  `shop_location` varchar(255) DEFAULT NULL,
  `shop_contact` varchar(50) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `paid_amount` decimal(10,2) DEFAULT NULL,
  `change_amount` decimal(10,2) DEFAULT NULL,
  `credit_balance` decimal(10,2) DEFAULT NULL,
  `cheque_number` varchar(100) DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `sale_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `invoice_number` varchar(20) NOT NULL,
  `status` varchar(1000) NOT NULL,
  `product_details` text,
  `added_by` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`sale_id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`sale_id`, `shop_name`, `shop_location`, `shop_contact`, `payment_method`, `paid_amount`, `change_amount`, `credit_balance`, `cheque_number`, `reference_number`, `total_amount`, `sale_date`, `invoice_number`, `status`, `product_details`, `added_by`) VALUES
(34, 'S1', '111eh', '711151700', 'Cash', 10000.00, 9300.00, NULL, NULL, NULL, 700.00, '2024-10-12 12:29:46', 'INV-20241012-86908', 'paid', '[{\"product_name\":\"Chick bitzz\",\"quantity\":2,\"price\":\"100\",\"total_price\":200},{\"product_name\":\"Coconut oil\",\"quantity\":1,\"price\":\"600\",\"total_price\":600}]', 'Chamodya');

-- --------------------------------------------------------

--
-- Table structure for table `sales_products`
--

DROP TABLE IF EXISTS `sales_products`;
CREATE TABLE IF NOT EXISTS `sales_products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sale_id` int DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `shop_price` decimal(10,2) DEFAULT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_id` (`sale_id`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sales_products`
--

INSERT INTO `sales_products` (`id`, `sale_id`, `product_name`, `quantity`, `shop_price`, `sale_price`, `size`, `product_id`) VALUES
(82, 34, 'Chick bitzz', 2, 100.00, 130.00, '100g', NULL),
(83, 34, 'Coconut oil', 1, 600.00, 640.00, '1L', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `shop`
--

DROP TABLE IF EXISTS `shop`;
CREATE TABLE IF NOT EXISTS `shop` (
  `shop_id` int NOT NULL AUTO_INCREMENT,
  `shop_name` varchar(50) NOT NULL,
  `owner_name` varchar(50) NOT NULL,
  `location` varchar(250) NOT NULL,
  `address` varchar(100) NOT NULL,
  `contact_number` int NOT NULL,
  `register_date` date NOT NULL,
  `register_time` time NOT NULL,
  `shop_type` varchar(20) NOT NULL,
  PRIMARY KEY (`shop_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `shop`
--

INSERT INTO `shop` (`shop_id`, `shop_name`, `owner_name`, `location`, `address`, `contact_number`, `register_date`, `register_time`, `shop_type`) VALUES
(1, 'S1', 'chamodya', '111eh', 'horana', 711151700, '2024-09-13', '18:34:00', 'Retail'),
(2, 'S1', 'chamodya', '111eh', 'horana', 711151700, '2024-10-04', '21:35:00', 'Retail'),
(3, 'S1', 'chamodya', '111eh', 'horana', 711151700, '2024-10-04', '21:35:00', 'Retail'),
(4, 'S2', 'CHA', '111eh', 'horana', 711151700, '2024-09-20', '20:22:00', 'Wholesale'),
(5, 'cha', 'chd', 'https://maps.app.goo.gl/gki2d4p3p1AVGoC66', 'horana', 711151700, '2024-10-08', '00:55:00', 'Whole Sale');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sales_products`
--
ALTER TABLE `sales_products`
  ADD CONSTRAINT `sales_products_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
