CREATE DATABASE IF NOT EXISTS `soben_cafe` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `soben_cafe`;

-- --------------------------------------------------------
-- 1. Table structure for table `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `fullname` VARCHAR(100) DEFAULT NULL,
  `role` ENUM('admin', 'cashier') NOT NULL DEFAULT 'cashier',
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample User Data
-- Password សម្រាប់ admin: 123 (MD5: 202cb962ac59075b964b07152d234b70)
-- Password សម្រាប់ cashier: 1234 (MD5: 81dc9bdb52d04dc20036dbd8313ed055)
INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `role`, `status`) VALUES
(1, 'admin', '202cb962ac59075b964b07152d234b70', 'Administrator', 'admin', 'active'),
(2, 'cashier', '81dc9bdb52d04dc20036dbd8313ed055', 'Main Cashier', 'cashier', 'active');

-- --------------------------------------------------------
-- 2. Table structure for table `categories`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `category_name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample Categories Data
INSERT INTO `categories` (`id`, `category_name`, `description`) VALUES
(1, 'Hot Coffee', 'Espresso, Americano, Latte'),
(2, 'Iced Coffee', 'Iced Latte, Iced Americano, Frappe'),
(3, 'Tea & Milk', 'Green Tea, Milk Tea, Lemon Tea'),
(4, 'Bakery & Cake', 'Croissant, Cheesecake, Muffin');

-- --------------------------------------------------------
-- 3. Table structure for table `suppliers`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE `suppliers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `supplier_name` VARCHAR(100) NOT NULL,
  `contact_name` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample Suppliers Data
INSERT INTO `suppliers` (`id`, `supplier_name`, `contact_name`, `phone`, `email`, `address`) VALUES
(1, 'Angkor Coffee Bean Co., Ltd', 'Sokha', '012345678', 'sokha@coffee.com', 'Phnom Penh'),
(2, 'Dairy Gold Cambodia', 'Bory', '098765432', 'bory@dairygold.com', 'Phnom Penh');

-- --------------------------------------------------------
-- 4. Table structure for table `customers`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `customer_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample Customers Data
INSERT INTO `customers` (`id`, `customer_name`, `phone`, `email`, `address`) VALUES
(1, 'General Customer', '000000000', 'general@gmail.com', 'Phnom Penh'),
(2, 'John Doe', '012999888', 'john@gmail.com', 'Phnom Penh');

-- --------------------------------------------------------
-- 5. Table structure for table `products`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_code` VARCHAR(50) DEFAULT NULL,
  `product_name` VARCHAR(150) NOT NULL,
  `category_id` INT(11) DEFAULT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `qty` INT(11) NOT NULL DEFAULT 0,
  `image` VARCHAR(255) DEFAULT 'default-product.png',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample Products Data
INSERT INTO `products` (`id`, `product_code`, `product_name`, `category_id`, `price`, `qty`, `image`) VALUES
(1, 'P001', 'Iced Latte', 2, 2.50, 50, 'default-product.png'),
(2, 'P002', 'Hot Americano', 1, 1.75, 40, 'default-product.png'),
(3, 'P003', 'Green Tea Latte', 3, 2.25, 30, 'default-product.png'),
(4, 'P004', 'Chocolate Cake', 4, 3.00, 15, 'default-product.png');

-- --------------------------------------------------------
-- 6. Table structure for table `sales`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `sales`;
CREATE TABLE `sales` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` VARCHAR(50) DEFAULT NULL,
  `product_name` VARCHAR(150) NOT NULL,
  `qty` INT(11) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `cashier` VARCHAR(100) DEFAULT 'Admin',
  `sale_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 7. Table structure for table `stock_history` (For Stock In / Out)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `stock_history`;
CREATE TABLE `stock_history` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL,
  `type` ENUM('in', 'out') NOT NULL,
  `qty` INT(11) NOT NULL,
  `note` TEXT DEFAULT NULL,
  `created_by` VARCHAR(100) DEFAULT 'Admin',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `fk_stock_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ១. លុប Table ចាស់ចោល (ប្រសិនបើចង់ Reset បង្កើតថ្មី)
DROP TABLE IF EXISTS `settings`;

-- ២. បង្កើត Table settings ជាមួយ Column គ្រប់គ្រាន់ពេញលេញ
CREATE TABLE `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `shop_name` VARCHAR(255) NOT NULL DEFAULT 'Soben Cafe',
  `phone` VARCHAR(50) DEFAULT '012 345 678',
  `address` TEXT NULL,
  `currency` VARCHAR(10) NOT NULL DEFAULT '$',
  `tax_rate` DECIMAL(5,2) DEFAULT 0.00,
  `logo` VARCHAR(255) DEFAULT NULL,
  `receipt_footer` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ៣. បន្ថែមទិន្នន័យ Default Initial ដំបូង (Row id = 1)
INSERT INTO `settings` (`id`, `shop_name`, `phone`, `address`, `currency`, `tax_rate`, `receipt_footer`) 
VALUES (1, 'Soben Cafe', '012 345 678', 'Phnom Penh, Cambodia', '$', 0.00, 'Thank you for your visit!');