-- =============================================================================
--  PoyrazTech Yönetim Paneli — Kurulum Veritabanı Şeması
--  Tek kurulum dosyası. Plesk / phpMyAdmin üzerinden import edilir.
--
--  Karakter seti : utf8mb4 (tam UTF-8)
--  Motor         : InnoDB (foreign key desteği)
--
--  Varsayılan giriş:
--    Kullanıcı adı : admin
--    E-posta       : admin@example.com
--    Şifre         : Admin1234!
--  (Şifre password_hash / BCRYPT ile üretilmiş güvenli hash olarak saklanır.)
-- =============================================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET FOREIGN_KEY_CHECKS = 0;

-- Yeniden kurulumda temiz başlangıç (bağımlılık sırasına dikkat).
DROP TABLE IF EXISTS `login_logs`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `currency_cache`;
DROP TABLE IF EXISTS `app_settings`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `roles`;

SET FOREIGN_KEY_CHECKS = 1;

-- -----------------------------------------------------------------------------
--  roles — kullanıcı rolleri
-- -----------------------------------------------------------------------------
CREATE TABLE `roles` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(80)  NOT NULL,
    `slug`        VARCHAR(80)  NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_roles_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
--  users — kullanıcılar
-- -----------------------------------------------------------------------------
CREATE TABLE `users` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`           VARCHAR(120) NOT NULL,
    `email`          VARCHAR(190) NOT NULL,
    `username`       VARCHAR(80)  NOT NULL,
    `password_hash`  VARCHAR(255) NOT NULL,
    `role_id`        INT UNSIGNED DEFAULT NULL,
    `status`         TINYINT(1)   NOT NULL DEFAULT 1,
    `remember_token` VARCHAR(255) DEFAULT NULL,
    `last_login_at`  DATETIME     DEFAULT NULL,
    `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email` (`email`),
    UNIQUE KEY `uq_users_username` (`username`),
    KEY `idx_users_role_id` (`role_id`),
    CONSTRAINT `fk_users_role`
        FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
--  password_resets — şifre sıfırlama token'ları
-- -----------------------------------------------------------------------------
CREATE TABLE `password_resets` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED    NOT NULL,
    `token`      VARCHAR(255)    NOT NULL,
    `expires_at` DATETIME        NOT NULL,
    `used_at`    DATETIME        DEFAULT NULL,
    `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_password_resets_token` (`token`),
    KEY `idx_password_resets_user` (`user_id`),
    CONSTRAINT `fk_password_resets_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
--  login_logs — giriş kayıtları (temel brute-force altyapısı)
-- -----------------------------------------------------------------------------
CREATE TABLE `login_logs` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED    DEFAULT NULL,
    `ip_address` VARCHAR(45)     DEFAULT NULL,
    `user_agent` VARCHAR(255)    DEFAULT NULL,
    `success`    TINYINT(1)      NOT NULL DEFAULT 0,
    `message`    VARCHAR(255)    DEFAULT NULL,
    `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_login_logs_user` (`user_id`),
    KEY `idx_login_logs_created` (`created_at`),
    CONSTRAINT `fk_login_logs_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
--  app_settings — uygulama ayarları (anahtar/değer)
-- -----------------------------------------------------------------------------
CREATE TABLE `app_settings` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key`   VARCHAR(120) NOT NULL,
    `setting_value` TEXT         DEFAULT NULL,
    `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_app_settings_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
--  currency_cache — döviz kuru önbelleği (API fallback)
-- -----------------------------------------------------------------------------
CREATE TABLE `currency_cache` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `base_currency` VARCHAR(10)     NOT NULL DEFAULT 'TRY',
    `rates_json`    TEXT            NOT NULL,
    `fetched_at`    DATETIME        NOT NULL,
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_currency_cache_fetched` (`fetched_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
--  Varsayılan veriler
-- =============================================================================

-- Roller (id sabit; kod "yonetici" slug'ını kritik rol olarak kullanır).
INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
    (1, 'Yönetici', 'yonetici', 'Tüm yetkilere sahip sistem yöneticisi.', NOW(), NOW()),
    (2, 'Muhasebe', 'muhasebe', 'Muhasebe ve finans işlemleri.',           NOW(), NOW()),
    (3, 'Satış',    'satis',    'Satış ve müşteri işlemleri.',             NOW(), NOW()),
    (4, 'Depo',     'depo',     'Depo ve stok işlemleri.',                 NOW(), NOW()),
    (5, 'Personel', 'personel', 'Genel personel erişimi.',                 NOW(), NOW());

-- Varsayılan yönetici kullanıcı.
--   Şifre: Admin1234!  (BCRYPT hash olarak saklanır)
INSERT INTO `users`
    (`name`, `email`, `username`, `password_hash`, `role_id`, `status`, `created_at`, `updated_at`)
VALUES
    ('Sistem Yöneticisi',
     'admin@example.com',
     'admin',
     '$2y$12$1ThwAR1f2xz74eT0YBZGtunKUK5ibP.UWKjKOwLCKUOkUSXXv2K0i',
     1,
     1,
     NOW(),
     NOW());

-- Başlangıç ayarları.
INSERT INTO `app_settings` (`setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
    ('site_name',   'PoyrazTech Yönetim Paneli', NOW(), NOW()),
    ('app_version', '1.0.0',                     NOW(), NOW());
