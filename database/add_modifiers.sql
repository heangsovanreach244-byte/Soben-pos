-- =================================================================
-- SQL Script for SOBEN CAFE: Modifiers & Add-ons Module
-- =================================================================

-- ១. បង្កើត Table សម្រាប់ Group នៃ Modifiers (ឧទាហរណ៍៖ Size, Sugar, Ice, Toppings)
CREATE TABLE IF NOT EXISTS `modifier_groups` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `is_required` TINYINT(1) DEFAULT 0 COMMENT '1: ត្រូវតែជ្រើសរើស (Required), 0: មិនជ្រើសក៏បាន (Optional)',
  `is_multiple` TINYINT(1) DEFAULT 0 COMMENT '1: ជ្រើសរើសបានច្រើន (Checkbox), 0: ជ្រើសរើសបានតែមួយ (Radio)',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ២. បង្កើត Table សម្រាប់ Option/Modifier នីមួយៗ និងតម្លៃបន្ថែម
CREATE TABLE IF NOT EXISTS `modifiers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `group_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`group_id`) REFERENCES `modifier_groups`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ៣. បង្កើត Table សម្រាប់រក្សាទុក Modifiers ដែលបានលក់ចេញជាមួយ Order Detail នីមួយៗ
CREATE TABLE IF NOT EXISTS `sale_item_modifiers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sale_detail_id` INT NOT NULL,
  `modifier_id` INT DEFAULT NULL,
  `modifier_name` VARCHAR(100) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`modifier_id`) REFERENCES `modifiers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- បញ្ចូលទិន្នន័យគំរូ (Sample Data)
-- =================================================================

-- បញ្ចូល Modifier Groups
INSERT INTO `modifier_groups` (`id`, `name`, `is_required`, `is_multiple`) VALUES
(1, 'Size', 1, 0),
(2, 'Sugar Level', 1, 0),
(3, 'Ice Level', 1, 0),
(4, 'Extra Toppings', 0, 1);

-- បញ្ចូល Options/Modifiers
INSERT INTO `modifiers` (`group_id`, `name`, `price`, `status`) VALUES
-- Groups 1: Size
(1, 'Regular', 0.00, 'active'),
(1, 'Large', 0.50, 'active'),

-- Groups 2: Sugar Level
(2, '100% Sweetness', 0.00, 'active'),
(2, '50% Sweetness', 0.00, 'active'),
(2, 'No Sweetness (0%)', 0.00, 'active'),

-- Groups 3: Ice Level
(3, 'Normal Ice', 0.00, 'active'),
(3, 'Less Ice', 0.00, 'active'),
(3, 'No Ice', 0.00, 'active'),

-- Groups 4: Extra Toppings
(4, 'Extra Shot', 0.50, 'active'),
(4, 'Pearl', 0.30, 'active'),
(4, 'Cream Cheese', 0.50, 'active');