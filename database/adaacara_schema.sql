-- Ada Acara database schema for phpMyAdmin.
-- Select your IDwebhost database first, then run this SQL.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `slug` VARCHAR(140) NOT NULL,
  `description` TEXT NULL,
  `sort_order` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `templates` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT(11) UNSIGNED NULL,
  `name` VARCHAR(160) NOT NULL,
  `slug` VARCHAR(180) NOT NULL,
  `description` TEXT NULL,
  `thumbnail` VARCHAR(255) NULL,
  `html` MEDIUMTEXT NULL,
  `css` MEDIUMTEXT NULL,
  `js` MEDIUMTEXT NULL,
  `grapesjs_json` LONGTEXT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `templates_slug_unique` (`slug`),
  KEY `templates_category_id_foreign` (`category_id`),
  CONSTRAINT `templates_category_id_foreign`
    FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `landing_pages` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `template_id` INT(11) UNSIGNED NULL,
  `title` VARCHAR(180) NOT NULL,
  `slug` VARCHAR(190) NOT NULL,
  `event_date` DATE NULL,
  `status` ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
  `html` MEDIUMTEXT NULL,
  `css` MEDIUMTEXT NULL,
  `js` MEDIUMTEXT NULL,
  `grapesjs_json` LONGTEXT NULL,
  `published_at` DATETIME NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `landing_pages_slug_unique` (`slug`),
  KEY `landing_pages_user_id_foreign` (`user_id`),
  KEY `landing_pages_template_id_foreign` (`template_id`),
  CONSTRAINT `landing_pages_user_id_foreign`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `landing_pages_template_id_foreign`
    FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `media` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `landing_page_id` INT(11) UNSIGNED NULL,
  `file_name` VARCHAR(190) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `file_type` VARCHAR(50) NOT NULL,
  `mime_type` VARCHAR(120) NULL,
  `file_size` INT(11) UNSIGNED NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `media_user_id_foreign` (`user_id`),
  KEY `media_landing_page_id_foreign` (`landing_page_id`),
  CONSTRAINT `media_user_id_foreign`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `media_landing_page_id_foreign`
    FOREIGN KEY (`landing_page_id`) REFERENCES `landing_pages` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `guest_books` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `landing_page_id` INT(11) UNSIGNED NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(190) NULL,
  `message` TEXT NULL,
  `attendance_status` ENUM('pending', 'attending', 'not_attending') NOT NULL DEFAULT 'pending',
  `guest_count` INT(11) UNSIGNED NOT NULL DEFAULT 1,
  `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `guest_books_landing_page_id_foreign` (`landing_page_id`),
  CONSTRAINT `guest_books_landing_page_id_foreign`
    FOREIGN KEY (`landing_page_id`) REFERENCES `landing_pages` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `categories` (`id`, `name`, `slug`, `description`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
  (1, 'Wedding', 'wedding', 'Template landing page untuk undangan pernikahan.', 1, 1, NOW(), NOW()),
  (2, 'Seminar', 'seminar', 'Template landing page untuk seminar, workshop, dan kelas.', 2, 1, NOW(), NOW()),
  (3, 'Birthday', 'birthday', 'Template landing page untuk ulang tahun dan gathering.', 3, 1, NOW(), NOW());

INSERT IGNORE INTO `templates` (`id`, `category_id`, `name`, `slug`, `description`, `thumbnail`, `html`, `css`, `js`, `grapesjs_json`, `is_active`, `created_at`, `updated_at`) VALUES
  (
    1,
    1,
    'Classic Wedding',
    'classic-wedding',
    'Template undangan pernikahan sederhana untuk starter GrapesJS.',
    NULL,
    '<section class="hero"><h1>Nama Pasangan</h1><p>Save the date</p></section>',
    '.hero{min-height:100vh;display:grid;place-items:center;text-align:center;font-family:serif;background:#f7f2ed;color:#2b2420}.hero h1{font-size:48px;margin:0}.hero p{font-size:18px}',
    '',
    NULL,
    1,
    NOW(),
    NOW()
  ),
  (
    2,
    2,
    'Clean Seminar',
    'clean-seminar',
    'Template event seminar dengan gaya bersih dan profesional.',
    NULL,
    '<section class="hero"><h1>Judul Seminar</h1><p>Daftar sekarang dan ikuti acaranya.</p></section>',
    '.hero{min-height:100vh;display:grid;place-items:center;text-align:center;font-family:Arial,sans-serif;background:#eef6f8;color:#102a36}.hero h1{font-size:44px;margin:0}.hero p{font-size:18px}',
    '',
    NULL,
    1,
    NOW(),
    NOW()
  );

SET FOREIGN_KEY_CHECKS = 1;
