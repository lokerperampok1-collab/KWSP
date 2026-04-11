-- Global Market Trade - Blank schema (no production data)
-- Import this into an EMPTY database.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

-- USERS
CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  is_admin TINYINT(1) NOT NULL DEFAULT 0,
  referrer VARCHAR(190) NULL,
  phone VARCHAR(40) NULL,
  country_code CHAR(2) NULL,
  country_name VARCHAR(100) NULL,
  currency_code VARCHAR(10) NULL,
  currency_symbol VARCHAR(12) NULL,
  bank_name VARCHAR(80) NULL,
  bank_account VARCHAR(80) NULL,
  bank_locked_at TIMESTAMP NULL,
  is_disabled TINYINT(1) NOT NULL DEFAULT 0,
  disabled_at TIMESTAMP NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_users_email (email),
  KEY idx_users_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- COUNTRIES (optional but recommended for signup dropdown)
CREATE TABLE IF NOT EXISTS countries (
  country_code CHAR(2) NOT NULL,
  country_name VARCHAR(100) NOT NULL,
  currency_code CHAR(3) NOT NULL,
  currency_name VARCHAR(80) NOT NULL,
  currency_symbol VARCHAR(12) NOT NULL DEFAULT '',
  PRIMARY KEY (country_code),
  KEY idx_currency_code (currency_code),
  KEY idx_country_name (country_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- WALLETS
CREATE TABLE IF NOT EXISTS wallets (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  currency VARCHAR(8) NOT NULL DEFAULT 'RM',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_wallet_user_currency (user_id, currency),
  KEY idx_wallet_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- WALLET TRANSACTIONS (ledger)
CREATE TABLE IF NOT EXISTS wallet_transactions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  currency VARCHAR(8) NOT NULL DEFAULT 'RM',
  type VARCHAR(32) NOT NULL,
  status VARCHAR(32) NOT NULL DEFAULT 'pending',
  amount DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  note VARCHAR(255) NULL,
  idempotency_key VARCHAR(64) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_wtx_user (user_id),
  KEY idx_wtx_user_cur (user_id, currency),
  KEY idx_wtx_status (status),
  UNIQUE KEY uniq_wtx_idempo (idempotency_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- NOTIFICATIONS
CREATE TABLE IF NOT EXISTS notifications (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(120) NOT NULL,
  body VARCHAR(255) NULL,
  link VARCHAR(255) NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_notif_user (user_id),
  KEY idx_notif_read (user_id, is_read),
  KEY idx_notif_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- KYC REQUESTS
CREATE TABLE IF NOT EXISTS kyc_requests (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  id_front_path VARCHAR(255) NULL,
  id_back_path VARCHAR(255) NULL,
  selfie_path VARCHAR(255) NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'pending',
  note VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_kyc_user (user_id),
  KEY idx_kyc_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- INVESTMENT PLANS
CREATE TABLE IF NOT EXISTS investment_plans (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  description VARCHAR(255) NULL,
  min_amount DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  max_amount DECIMAL(18,2) NULL,
  roi_daily_percent DECIMAL(8,4) NOT NULL DEFAULT 0.0000,
  duration_days INT NOT NULL DEFAULT 0,
  status TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_plan_status (status),
  KEY idx_plan_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- USER INVESTMENTS
CREATE TABLE IF NOT EXISTS user_investments (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  plan_id BIGINT UNSIGNED NOT NULL,
  plan_name VARCHAR(120) NOT NULL,
  amount DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  roi_daily_percent DECIMAL(8,4) NOT NULL DEFAULT 0.0000,
  duration_days INT NOT NULL DEFAULT 0,
  start_at DATETIME NOT NULL,
  end_at DATETIME NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_inv_user (user_id),
  KEY idx_inv_status (status),
  KEY idx_inv_plan (plan_id),
  KEY idx_inv_end (end_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS=1;
