-- ═══════════════════════════════════════════════════
--  VirtualHub Pro — Database Schema
--  Run this in phpMyAdmin or MySQL CLI:
--  mysql -u root -p virtualhub_pro < schema.sql
-- ═══════════════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS virtualhub_pro
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE virtualhub_pro;

-- ─────────────────────────────────────────────────
--  USERS TABLE
-- ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name    VARCHAR(80)  NOT NULL,
    last_name     VARCHAR(80)  NOT NULL,
    username      VARCHAR(60)  NOT NULL UNIQUE,
    email         VARCHAR(180) NOT NULL UNIQUE,
    phone         VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    balance       DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    profile_image VARCHAR(255) DEFAULT NULL,
    is_active     TINYINT(1)  NOT NULL DEFAULT 1,
    created_at    DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────
--  ADMINS TABLE
-- ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS admins (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(120) NOT NULL,
    email         VARCHAR(180) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_active     TINYINT(1)  NOT NULL DEFAULT 1,
    created_at    DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Insert default admin (password: Admin@1234 — change immediately!)
INSERT IGNORE INTO admins (name, email, password_hash)
VALUES ('Admin', 'admin@virtualhub.com',
        '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- ─────────────────────────────────────────────────
--  FORMATS TABLE  (admin uploads: title, desc, link, price)
-- ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS formats (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200) NOT NULL,
    description TEXT,
    link        VARCHAR(500) NOT NULL,
    price       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    is_active   TINYINT(1)  NOT NULL DEFAULT 1,
    created_at  DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────
--  TOOLS TABLE  (update & tools)
-- ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS tools (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200) NOT NULL,
    description TEXT,
    link        VARCHAR(500) NOT NULL,
    price       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    is_active   TINYINT(1)  NOT NULL DEFAULT 1,
    created_at  DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────
--  WORKING PICTURES TABLE
-- ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS working_pictures (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(200) NOT NULL,
    sample_image VARCHAR(500) NOT NULL,   -- public preview image URL/path
    link         VARCHAR(500) NOT NULL,   -- hidden until purchased
    price        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    is_active    TINYINT(1)   NOT NULL DEFAULT 1,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────
--  SOCIAL LOGINS TABLE  (each row = one login slot)
-- ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS social_logins (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    platform    VARCHAR(60)  NOT NULL,   -- e.g. 'Facebook', 'Instagram'
    title       VARCHAR(200) NOT NULL,
    description TEXT,
    credentials TEXT         NOT NULL,   -- JSON or plain text, shown only after purchase
    price       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    is_sold     TINYINT(1)  NOT NULL DEFAULT 0,   -- removed from store once sold
    created_at  DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────
--  TRANSACTIONS TABLE
-- ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS transactions (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       INT UNSIGNED NOT NULL,
    type          ENUM('topup','purchase','refund','admin_topup') NOT NULL,
    item_type     VARCHAR(60)  DEFAULT NULL,   -- 'format','tool','picture','social_login','virtual_number','boost'
    item_id       INT UNSIGNED DEFAULT NULL,
    amount        DECIMAL(12,2) NOT NULL,
    balance_before DECIMAL(12,2) NOT NULL,
    balance_after  DECIMAL(12,2) NOT NULL,
    reference     VARCHAR(120) DEFAULT NULL,   -- Flutterwave tx ref or system ref
    status        ENUM('pending','success','failed','refunded') NOT NULL DEFAULT 'pending',
    note          TEXT         DEFAULT NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_type (type)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────
--  VIRTUAL NUMBERS TABLE  (active purchases)
-- ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS virtual_numbers (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNSIGNED NOT NULL,
    transaction_id INT UNSIGNED DEFAULT NULL,
    country      VARCHAR(60)  NOT NULL,
    service      VARCHAR(120) NOT NULL,
    phone_number VARCHAR(40)  NOT NULL,
    activation_id VARCHAR(120) NOT NULL,   -- ID from smsproxy API
    sms_code     VARCHAR(50)  DEFAULT NULL,
    status       ENUM('waiting','received','cancelled','expired') NOT NULL DEFAULT 'waiting',
    price        DECIMAL(10,2) NOT NULL,
    expires_at   DATETIME     NOT NULL,    -- 10 minutes from purchase
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────
--  PURCHASED ITEMS TABLE  (formats, tools, pictures)
-- ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS purchases (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id        INT UNSIGNED NOT NULL,
    item_type      ENUM('format','tool','working_picture','social_login') NOT NULL,
    item_id        INT UNSIGNED NOT NULL,
    transaction_id INT UNSIGNED DEFAULT NULL,
    amount_paid    DECIMAL(10,2) NOT NULL,
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_item (user_id, item_type)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────
--  PASSWORD RESET TOKENS
-- ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS password_resets (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    email      VARCHAR(180) NOT NULL,
    token      VARCHAR(100) NOT NULL,
    expires_at DATETIME     NOT NULL,
    used       TINYINT(1)   NOT NULL DEFAULT 0,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_token (token)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────
--  FLUTTERWAVE PAYMENT RECORDS
-- ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS payments (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    flw_ref         VARCHAR(120) NOT NULL,
    tx_ref          VARCHAR(120) NOT NULL UNIQUE,
    amount          DECIMAL(12,2) NOT NULL,
    currency        VARCHAR(10)  NOT NULL DEFAULT 'NGN',
    status          ENUM('pending','successful','failed') NOT NULL DEFAULT 'pending',
    verified_at     DATETIME DEFAULT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_tx_ref (tx_ref)
) ENGINE=InnoDB;