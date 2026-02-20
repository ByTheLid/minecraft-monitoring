-- =====================================================
-- Minecraft Server Monitoring — Database Initialization
-- Запустите этот SQL в phpMyAdmin
-- =====================================================

-- Создание базы данных
CREATE DATABASE IF NOT EXISTS `monitoring`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `monitoring`;

-- =====================================================
-- 1. Пользователи
-- =====================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `username` VARCHAR(32) UNIQUE NOT NULL,
    `email` VARCHAR(255) UNIQUE NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('user','admin') DEFAULT 'user',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. Сессии
-- =====================================================
CREATE TABLE IF NOT EXISTS `sessions` (
    `id` CHAR(64) PRIMARY KEY,
    `user_id` INT NOT NULL,
    `ip_address` VARCHAR(45),
    `user_agent` VARCHAR(255),
    `expires_at` TIMESTAMP NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. Серверы
-- =====================================================
CREATE TABLE IF NOT EXISTS `servers` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `ip` VARCHAR(255) NOT NULL,
    `port` SMALLINT UNSIGNED DEFAULT 25565,
    `description` TEXT,
    `website` VARCHAR(255),
    `banner_url` VARCHAR(255),
    `tags` JSON,
    `is_active` TINYINT(1) DEFAULT 1,
    `is_approved` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_server` (`ip`, `port`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. Статистика пингов (детальная)
-- =====================================================
CREATE TABLE IF NOT EXISTS `server_stats` (
    `id` BIGINT PRIMARY KEY AUTO_INCREMENT,
    `server_id` INT NOT NULL,
    `is_online` TINYINT(1) NOT NULL,
    `players_online` INT UNSIGNED DEFAULT 0,
    `players_max` INT UNSIGNED DEFAULT 0,
    `version` VARCHAR(64),
    `ping_ms` SMALLINT UNSIGNED,
    `motd` VARCHAR(512),
    `checked_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`server_id`) REFERENCES `servers`(`id`) ON DELETE CASCADE,
    INDEX `idx_server_checked` (`server_id`, `checked_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. Кеш текущего состояния сервера
-- =====================================================
CREATE TABLE IF NOT EXISTS `server_status_cache` (
    `server_id` INT PRIMARY KEY,
    `is_online` TINYINT(1),
    `players_online` INT UNSIGNED,
    `players_max` INT UNSIGNED,
    `version` VARCHAR(64),
    `ping_ms` SMALLINT UNSIGNED,
    `motd` VARCHAR(512),
    `favicon_base64` TEXT,
    `last_checked_at` TIMESTAMP NULL,
    FOREIGN KEY (`server_id`) REFERENCES `servers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. Почасовые агрегаты статистики
-- =====================================================
CREATE TABLE IF NOT EXISTS `server_stats_hourly` (
    `server_id` INT,
    `hour` TIMESTAMP,
    `avg_players` DECIMAL(8,2),
    `max_players` SMALLINT,
    `uptime_percent` DECIMAL(5,2),
    `avg_ping` DECIMAL(8,2),
    PRIMARY KEY (`server_id`, `hour`),
    FOREIGN KEY (`server_id`) REFERENCES `servers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. Голоса
-- =====================================================
CREATE TABLE IF NOT EXISTS `votes` (
    `id` BIGINT PRIMARY KEY AUTO_INCREMENT,
    `server_id` INT NOT NULL,
    `user_id` INT,
    `ip_address` VARCHAR(45) NOT NULL,
    `voted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`server_id`) REFERENCES `servers`(`id`) ON DELETE CASCADE,
    INDEX `idx_vote_lookup` (`server_id`, `ip_address`, `voted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. Пакеты boost
-- =====================================================
CREATE TABLE IF NOT EXISTS `boost_packages` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(64) NOT NULL,
    `points` INT NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `duration_days` INT NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 9. Покупки boost
-- =====================================================
CREATE TABLE IF NOT EXISTS `boost_purchases` (
    `id` BIGINT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `server_id` INT NOT NULL,
    `package_id` INT NOT NULL,
    `points` INT NOT NULL,
    `activated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP NOT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
    FOREIGN KEY (`server_id`) REFERENCES `servers`(`id`),
    FOREIGN KEY (`package_id`) REFERENCES `boost_packages`(`id`),
    INDEX `idx_active_boosts` (`server_id`, `expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 10. Кеш рейтинга
-- =====================================================
CREATE TABLE IF NOT EXISTS `server_rankings` (
    `server_id` INT PRIMARY KEY,
    `rank_score` DECIMAL(12,4) NOT NULL DEFAULT 0,
    `vote_count` INT DEFAULT 0,
    `boost_points` INT DEFAULT 0,
    `avg_online` DECIMAL(8,2) DEFAULT 0,
    `uptime_percent` DECIMAL(5,2) DEFAULT 0,
    `calculated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`server_id`) REFERENCES `servers`(`id`) ON DELETE CASCADE,
    INDEX `idx_rank` (`rank_score` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 11. Посты / новости
-- =====================================================
CREATE TABLE IF NOT EXISTS `posts` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `author_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) UNIQUE NOT NULL,
    `content` TEXT NOT NULL,
    `cover_image` VARCHAR(255),
    `category` ENUM('news','guide','update') DEFAULT 'news',
    `is_published` TINYINT(1) DEFAULT 0,
    `published_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`author_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 12. Настройки платформы
-- =====================================================
CREATE TABLE IF NOT EXISTS `settings` (
    `key` VARCHAR(64) PRIMARY KEY,
    `value` TEXT NOT NULL,
    `description` VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 13. Rate Limiting
-- =====================================================
CREATE TABLE IF NOT EXISTS `rate_limits` (
    `key` VARCHAR(128) PRIMARY KEY,
    `hits` INT NOT NULL DEFAULT 0,
    `expires_at` TIMESTAMP NOT NULL,
    INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 14. Таблица миграций (для совместимости с системой миграций)
-- =====================================================
CREATE TABLE IF NOT EXISTS `migrations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL UNIQUE,
    `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Отметить все миграции как выполненные
INSERT INTO `migrations` (`name`) VALUES
    ('001_create_users'),
    ('002_create_sessions'),
    ('003_create_servers'),
    ('004_create_server_stats'),
    ('005_create_server_status_cache'),
    ('006_create_server_stats_hourly'),
    ('007_create_votes'),
    ('008_create_boost_tables'),
    ('009_create_server_rankings'),
    ('010_create_posts'),
    ('011_create_settings'),
    ('012_create_rate_limits');

-- =====================================================
-- 15. Индексы для поиска и производительности
-- =====================================================
CREATE FULLTEXT INDEX `idx_servers_search` ON `servers`(`name`, `description`);
CREATE INDEX `idx_servers_active` ON `servers`(`is_active`, `is_approved`);
CREATE INDEX `idx_votes_server_time` ON `votes`(`server_id`, `voted_at`);
CREATE INDEX `idx_boosts_active` ON `boost_purchases`(`server_id`, `expires_at`);

-- =====================================================
-- 16. Настройки по умолчанию
-- =====================================================
INSERT INTO `settings` (`key`, `value`, `description`) VALUES
    ('rank_kv', '1.0', 'Votes coefficient'),
    ('rank_kb', '0.5', 'Boost coefficient'),
    ('rank_ko', '0.3', 'Online coefficient'),
    ('rank_ku', '0.2', 'Uptime coefficient'),
    ('max_servers_per_user', '5', 'Maximum servers per user');

-- =====================================================
-- 17. Администратор (пароль: admin123)
-- =====================================================
INSERT INTO `users` (`username`, `email`, `password_hash`, `role`) VALUES
    ('admin', 'admin@monitoring.local', '$2y$10$FA484deaUE7kNdEL3d4z0OHP9WIlw/1AYN0oDuuZgp.NgyEh.FtSy', 'admin');

-- =====================================================
-- 18. Тестовый пользователь (пароль: password123)
-- =====================================================
INSERT INTO `users` (`username`, `email`, `password_hash`, `role`) VALUES
    ('player1', 'player1@example.com', '$2y$10$nJEaUkNe.hS00MYf1P8llOcy4yMYdoq9ZAU0iAkOR1W7D3tzuaBae', 'user');

-- =====================================================
-- 19. Тестовые серверы
-- =====================================================
INSERT INTO `servers` (`user_id`, `name`, `ip`, `port`, `description`, `tags`, `is_approved`) VALUES
    (1, 'Hypixel', 'mc.hypixel.net', 25565, 'The largest Minecraft server network. Featuring SkyWars, BedWars, SkyBlock, and more!', '["minigames","pvp","skyblock","bedwars"]', 1),
    (1, 'CubeCraft', 'play.cubecraft.net', 25565, 'Unique Minecraft minigames including EggWars, Tower Defence, and SkyWars.', '["minigames","eggwars","skywars"]', 1),
    (1, 'Mineplex', 'us.mineplex.com', 25565, 'One of the largest Minecraft servers with a variety of minigames.', '["minigames","survival","creative"]', 1),
    (1, 'MC Central', 'mccentral.org', 25565, 'Minecraft server with Prison, Factions, Survival and more.', '["prison","factions","survival"]', 1),
    (1, '2b2t', '2b2t.org', 25565, 'The oldest anarchy server in Minecraft.', '["anarchy","survival","vanilla"]', 1);

-- Кеш рейтинга для тестовых серверов
INSERT INTO `server_rankings` (`server_id`, `rank_score`, `vote_count`) VALUES
    (1, 95.5, 0),
    (2, 78.3, 0),
    (3, 65.1, 0),
    (4, 52.7, 0),
    (5, 41.2, 0);

-- Кеш статуса для тестовых серверов
INSERT INTO `server_status_cache` (`server_id`, `is_online`, `players_online`, `players_max`, `last_checked_at`) VALUES
    (1, 0, 0, 0, NOW()),
    (2, 0, 0, 0, NOW()),
    (3, 0, 0, 0, NOW()),
    (4, 0, 0, 0, NOW()),
    (5, 0, 0, 0, NOW());

-- =====================================================
-- 20. Boost-пакеты
-- =====================================================
INSERT INTO `boost_packages` (`name`, `points`, `price`, `duration_days`) VALUES
    ('Bronze Boost', 50, 2.99, 7),
    ('Silver Boost', 150, 6.99, 14),
    ('Gold Boost', 400, 14.99, 30),
    ('Diamond Boost', 1000, 29.99, 30);

-- =====================================================
-- 21. Тестовый пост
-- =====================================================
INSERT INTO `posts` (`author_id`, `title`, `slug`, `content`, `category`, `is_published`, `published_at`) VALUES
    (1, 'Welcome to MC Monitor!', 'welcome-to-mc-monitor',
     'Welcome to the MC Monitor platform!\n\nHere you can discover and track the best Minecraft servers, vote for your favorites, and monitor real-time statistics.\n\nFeatures:\n- Real-time server monitoring\n- Player count tracking\n- Voting system\n- Server rankings\n- Boost system\n\nAdd your server today and start growing your community!',
     'news', 1, NOW());

-- =====================================================
-- Готово! Данные для входа:
-- Admin: admin / admin123
-- User:  player1 / password123
-- =====================================================
