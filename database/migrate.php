<?php

/**
 * Rous Cheat — MySQL Migration
 * Run: php database/migrate.php
 */

require_once __DIR__ . '/../app/Helpers/Env.php';
require_once __DIR__ . '/../app/Helpers/DB.php';

use App\Helpers\{Env, DB};

Env::load(__DIR__ . '/../.env');
DB::init();

$pdo = DB::pdo();

echo "⏳ Running MySQL migrations...\n\n";

$statements = [

// ── users ──────────────────────────────────────────────────────────────────
'users' => 'CREATE TABLE IF NOT EXISTS `users` (
    `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(120)    NOT NULL,
    `email`      VARCHAR(180)    NOT NULL,
    `password`   VARCHAR(255)    NOT NULL,
    `bio`        TEXT,
    `location`   VARCHAR(120)    DEFAULT NULL,
    `avatar`     VARCHAR(255)    DEFAULT NULL,
    `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

// ── categories ─────────────────────────────────────────────────────────────
'categories' => 'CREATE TABLE IF NOT EXISTS `categories` (
    `id`         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name_en`    VARCHAR(120)     NOT NULL,
    `name_kh`    VARCHAR(120)     NOT NULL,
    `emoji`      VARCHAR(10)      DEFAULT NULL,
    `sort_order` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

// ── regions ────────────────────────────────────────────────────────────────
'regions' => 'CREATE TABLE IF NOT EXISTS `regions` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name_en`     VARCHAR(120) NOT NULL,
    `name_kh`     VARCHAR(120) NOT NULL,
    `description` TEXT         DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

// ── ingredients ────────────────────────────────────────────────────────────
'ingredients' => 'CREATE TABLE IF NOT EXISTS `ingredients` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name_en`     VARCHAR(120) NOT NULL,
    `name_kh`     VARCHAR(120) NOT NULL,
    `description` TEXT         DEFAULT NULL,
    `image`       VARCHAR(255) DEFAULT NULL,
    `category`    VARCHAR(60)  DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

// ── ingredient_substitutes ─────────────────────────────────────────────────
'ingredient_substitutes' => 'CREATE TABLE IF NOT EXISTS `ingredient_substitutes` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ingredient_id` INT UNSIGNED NOT NULL,
    `substitute_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_is_ingredient` FOREIGN KEY (`ingredient_id`)
        REFERENCES `ingredients` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_is_substitute` FOREIGN KEY (`substitute_id`)
        REFERENCES `ingredients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

// ── recipes ────────────────────────────────────────────────────────────────
'recipes' => 'CREATE TABLE IF NOT EXISTS `recipes` (
    `id`                 INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    `user_id`            INT UNSIGNED      NOT NULL,
    `category_id`        INT UNSIGNED      DEFAULT NULL,
    `region_id`          INT UNSIGNED      DEFAULT NULL,
    `title_en`           VARCHAR(255)      NOT NULL,
    `title_kh`           VARCHAR(255)      NOT NULL,
    `description_en`     TEXT              DEFAULT NULL,
    `description_kh`     TEXT              DEFAULT NULL,
    `difficulty`         ENUM("easy","medium","hard") NOT NULL DEFAULT "easy",
    `cook_time_minutes`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `prep_time_minutes`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `servings`           TINYINT UNSIGNED  NOT NULL DEFAULT 2,
    `ingredients`        JSON              DEFAULT NULL,
    `instructions_en`    JSON              DEFAULT NULL,
    `instructions_kh`    JSON              DEFAULT NULL,
    `tags`               JSON              DEFAULT NULL,
    `images`             JSON              DEFAULT NULL,
    `thumbnail`          VARCHAR(255)      DEFAULT NULL,
    `created_at`         DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`         DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_recipes_user`     FOREIGN KEY (`user_id`)
        REFERENCES `users`      (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_recipes_category` FOREIGN KEY (`category_id`)
        REFERENCES `categories` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_recipes_region`   FOREIGN KEY (`region_id`)
        REFERENCES `regions`    (`id`) ON DELETE SET NULL,
    KEY `idx_recipes_category` (`category_id`),
    KEY `idx_recipes_region`   (`region_id`),
    KEY `idx_recipes_user`     (`user_id`),
    FULLTEXT KEY `ft_recipes_search` (`title_en`, `description_en`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

// ── reviews ────────────────────────────────────────────────────────────────
'reviews' => 'CREATE TABLE IF NOT EXISTS `reviews` (
    `id`         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `recipe_id`  INT UNSIGNED     NOT NULL,
    `user_id`    INT UNSIGNED     NOT NULL,
    `rating`     TINYINT UNSIGNED NOT NULL,
    `comment`    TEXT             DEFAULT NULL,
    `created_at` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_review_user_recipe` (`recipe_id`, `user_id`),
    CONSTRAINT `fk_reviews_recipe` FOREIGN KEY (`recipe_id`)
        REFERENCES `recipes` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`)
        REFERENCES `users`   (`id`) ON DELETE CASCADE,
    CONSTRAINT `chk_rating` CHECK (`rating` BETWEEN 1 AND 5),
    KEY `idx_reviews_recipe` (`recipe_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

// ── favorites ──────────────────────────────────────────────────────────────
'favorites' => 'CREATE TABLE IF NOT EXISTS `favorites` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED NOT NULL,
    `recipe_id`  INT UNSIGNED NOT NULL,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_favorites_user_recipe` (`user_id`, `recipe_id`),
    CONSTRAINT `fk_fav_user`   FOREIGN KEY (`user_id`)
        REFERENCES `users`   (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fav_recipe` FOREIGN KEY (`recipe_id`)
        REFERENCES `recipes` (`id`) ON DELETE CASCADE,
    KEY `idx_favorites_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

];

foreach ($statements as $table => $sql) {
    $pdo->exec($sql);
    echo "  ✓ $table\n";
}

echo "\n✅ All tables created successfully.\n";
