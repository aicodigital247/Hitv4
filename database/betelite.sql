-- BETELITE MySQL Database Schema
-- Optimized for MySQL 5.7+ / PHP 8+ and shared hosting environments

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

-- Table: users
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'predictor', 'user') NOT NULL DEFAULT 'user',
  `status` ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
  `referral_code` VARCHAR(20) NOT NULL UNIQUE,
  `referred_by` INT DEFAULT NULL,
  `vip_until` DATETIME DEFAULT NULL,
  `telegram_id` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`referred_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: wallets
CREATE TABLE IF NOT EXISTS `wallets` (
  `user_id` INT PRIMARY KEY,
  `balance` DECIMAL(15, 2) NOT NULL DEFAULT '0.00',
  `pending_withdrawals` DECIMAL(15, 2) NOT NULL DEFAULT '0.00',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: matches
CREATE TABLE IF NOT EXISTS `matches` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `home_team` VARCHAR(100) NOT NULL,
  `away_team` VARCHAR(100) NOT NULL,
  `home_logo` VARCHAR(255) DEFAULT NULL,
  `away_logo` VARCHAR(255) DEFAULT NULL,
  `league` VARCHAR(100) NOT NULL,
  `sport_type` VARCHAR(50) NOT NULL DEFAULT 'Football',
  `kickoff_time` DATETIME NOT NULL,
  `status` ENUM('scheduled', 'live', 'finished', 'cancelled') NOT NULL DEFAULT 'scheduled',
  `home_score` INT DEFAULT 0,
  `away_score` INT DEFAULT 0,
  `live_minute` INT DEFAULT 0,
  `momentum_home` INT DEFAULT 50,
  `possession_home` INT DEFAULT 50,
  `yellow_home` INT DEFAULT 0,
  `yellow_away` INT DEFAULT 0,
  `red_home` INT DEFAULT 0,
  `red_away` INT DEFAULT 0,
  `event_feed` TEXT DEFAULT NULL, -- JSON formatted live updates
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: predictions (Bundles created by predictors)
CREATE TABLE IF NOT EXISTS `predictions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `predictor_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `price` DECIMAL(10, 2) NOT NULL DEFAULT '0.00',
  `sport_type` VARCHAR(50) NOT NULL DEFAULT 'Football',
  `total_odds` DECIMAL(8, 2) NOT NULL DEFAULT '1.00',
  `confidence` INT NOT NULL DEFAULT 85, -- Percentage
  `status` ENUM('pending', 'won', 'lost', 'refunded') NOT NULL DEFAULT 'pending',
  `is_vip` TINYINT(1) NOT NULL DEFAULT 0,
  `is_hot` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`predictor_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: prediction_items (Individual selection in a prediction bundle)
CREATE TABLE IF NOT EXISTS `prediction_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `prediction_id` INT NOT NULL,
  `match_id` INT NOT NULL,
  `selection` VARCHAR(100) NOT NULL, -- e.g. "Home Win", "Over 2.5"
  `odds` DECIMAL(6, 2) NOT NULL,
  `analysis` TEXT DEFAULT NULL,
  FOREIGN KEY (`prediction_id`) REFERENCES `predictions`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`match_id`) REFERENCES `matches`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: transactions (Wallet logs)
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `amount` DECIMAL(15, 2) NOT NULL,
  `type` ENUM('deposit', 'withdrawal', 'purchase', 'earnings', 'referral_bonus', 'refund') NOT NULL,
  `status` ENUM('pending', 'completed', 'failed', 'cancelled') NOT NULL DEFAULT 'pending',
  `description` VARCHAR(255) NOT NULL,
  `reference` VARCHAR(100) NOT NULL UNIQUE,
  `gateway` VARCHAR(50) DEFAULT 'wallet',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: orders (Purchases of predictions)
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `prediction_id` INT NOT NULL,
  `price_paid` DECIMAL(10, 2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`prediction_id`) REFERENCES `predictions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: cart
CREATE TABLE IF NOT EXISTS `cart` (
  `user_id` INT NOT NULL,
  `prediction_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`, `prediction_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`prediction_id`) REFERENCES `predictions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: referrals
CREATE TABLE IF NOT EXISTS `referrals` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `referrer_id` INT NOT NULL,
  `referred_id` INT NOT NULL UNIQUE,
  `reward_paid` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`referrer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`referred_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: ratings (Predictor feedback)
CREATE TABLE IF NOT EXISTS `ratings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `predictor_id` INT NOT NULL,
  `rating` INT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
  `review` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`predictor_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: ads
CREATE TABLE IF NOT EXISTS `ads` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `image_url` VARCHAR(255) NOT NULL,
  `link_url` VARCHAR(255) NOT NULL,
  `position` VARCHAR(50) NOT NULL DEFAULT 'dashboard_banner',
  `clicks` INT NOT NULL DEFAULT 0,
  `views` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: platform_settings
CREATE TABLE IF NOT EXISTS `platform_settings` (
  `key` VARCHAR(50) PRIMARY KEY,
  `value` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Default Admin & Initial Settings
-- Admin credentials: admin / AdminPass123!
-- Predictor credentials: tipster / TipsterPass123!
INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `referral_code`) VALUES 
(1, 'admin', 'admin@betelite.com', '$2y$10$7zB2K36A6gGf.KbeTfCskedOqgUepG7Q97x/N17805X9pDk3R2XwG', 'admin', 'BETELITEADMIN'),
(2, 'tipster', 'tipster@betelite.com', '$2y$10$7zB2K36A6gGf.KbeTfCskedOqgUepG7Q97x/N17805X9pDk3R2XwG', 'predictor', 'BETELITETIPSTER');

INSERT INTO `wallets` (`user_id`, `balance`) VALUES 
(1, 1000000.00),
(2, 500.00);

INSERT INTO `platform_settings` (`key`, `value`) VALUES 
('platform_fee_percent', '20'),
('referral_bonus_amount', '5.00'),
('vip_subscription_price', '49.99'),
('telegram_bot_token', '');

COMMIT;
