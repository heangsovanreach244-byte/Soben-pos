CREATE TABLE IF NOT EXISTS `ingredients` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ingredient_name` VARCHAR(150) NOT NULL,
  `unit` VARCHAR(50) NOT NULL,
  `stock_qty` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `alert_qty` DECIMAL(10, 2) NOT NULL DEFAULT 10.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `product_recipes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `ingredient_id` INT NOT NULL,
  `quantity` DECIMAL(10, 2) NOT NULL,
  FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `ingredients` (`id`, `ingredient_name`, `unit`, `stock_qty`, `alert_qty`) VALUES
(1, 'គ្រាប់កាហ្វេ (Coffee Beans)', 'g', 1000.00, 200.00),
(2, 'ទឹកដោះគោស្រស់ (Fresh Milk)', 'ml', 5000.00, 1000.00),
(3, 'ទឹកដោះគោខាប់ (Condensed Milk)', 'ml', 2000.00, 500.00);

INSERT INTO `product_recipes` (`product_id`, `ingredient_id`, `quantity`) VALUES
(1, 1, 18.00),
(1, 2, 120.00),
(1, 3, 30.00);