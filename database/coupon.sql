-- 1. Table សម្រាប់រក្សាទុកព័ត៌មានទំនិញ (Products)
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_name` VARCHAR(255) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `qty` INT NOT NULL DEFAULT 0,
  `category` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Table សម្រាប់រក្សាទុកព័ត៌មានតុ (Tables)
CREATE TABLE IF NOT EXISTS `tables` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `table_number` VARCHAR(20) NOT NULL UNIQUE,
  `seats` INT DEFAULT 4,
  `status` ENUM('Available', 'Occupied', 'Reserved') DEFAULT 'Available',
  `current_invoice` VARCHAR(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- បញ្ចូលតុគំរូ (Sample Tables)
INSERT INTO `tables` (`table_number`, `seats`, `status`) VALUES
('Table 01', 4, 'Available'),
('Table 02', 2, 'Available'),
('Table 03', 6, 'Available'),
('Table 04', 4, 'Available'),
('Table 05', 4, 'Available')
ON DUPLICATE KEY UPDATE `seats` = VALUES(`seats`);

-- 3. Table សម្រាប់រក្សាទុកប្រវត្តិនៃការលក់ (Sales)
CREATE TABLE IF NOT EXISTS `sales` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `invoice_number` VARCHAR(50) NOT NULL,
  `order_type` ENUM('Dine-in', 'Takeaway', 'Delivery') DEFAULT 'Dine-in',
  `table_id` INT NULL,
  `product_name` VARCHAR(255) NOT NULL,
  `qty` INT NOT NULL DEFAULT 1,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `item_discount` DECIMAL(10,2) DEFAULT 0.00,
  `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `coupon_code` VARCHAR(50) DEFAULT NULL,
  `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
  `cashier` VARCHAR(100) NOT NULL,
  `sale_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`table_id`) REFERENCES `tables`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Table សម្រាប់រក្សាទុក Options / Add-ons / Modifiers (Sale Item Modifiers)
CREATE TABLE IF NOT EXISTS `sale_item_modifiers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sale_detail_id` INT NOT NULL,
  `modifier_name` VARCHAR(255) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  FOREIGN KEY (`sale_detail_id`) REFERENCES `sales`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Table សម្រាប់រក្សាទុកកម្មវិធីប្រូម៉ូសិន (Promotions - Buy X Get Y & Cart Discounts)
CREATE TABLE IF NOT EXISTS `promotions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `promo_name` VARCHAR(100) NOT NULL,
  `promo_type` ENUM('buy_x_get_y', 'cart_discount') NOT NULL,
  `buy_product_id` INT NULL,
  `buy_qty` INT DEFAULT 1,
  `get_product_id` INT NULL,
  `get_qty` INT DEFAULT 1,
  `discount_type` ENUM('percent', 'fixed') DEFAULT 'percent',
  `discount_value` DECIMAL(10,2) DEFAULT 0.00,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  FOREIGN KEY (`buy_product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`get_product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Table សម្រាប់រក្សាទុក កូដប្រូម៉ូសិន / គូប៉ុង (Coupons)
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `discount_type` ENUM('percent', 'fixed') NOT NULL,
  `discount_value` DECIMAL(10,2) NOT NULL,
  `min_spend` DECIMAL(10,2) DEFAULT 0.00,
  `expiry_date` DATE NOT NULL,
  `usage_limit` INT DEFAULT 100,
  `used_count` INT DEFAULT 0,
  `status` ENUM('active', 'inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;