<?php
declare(strict_types=1);

/**
 * Lightweight "auto-migration" helpers.
 * Safe to call repeatedly. Uses CREATE TABLE IF NOT EXISTS and best-effort ALTERs.
 */

function ensure_wallet_schema(PDO $pdo): void {
  // wallets
  $pdo->exec("
    CREATE TABLE IF NOT EXISTS wallets (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      user_id BIGINT UNSIGNED NOT NULL,
      currency VARCHAR(8) NOT NULL DEFAULT 'RM',
      created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      UNIQUE KEY uniq_wallet_user_currency (user_id, currency),
      KEY idx_wallet_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  ");

  // wallet_transactions
  $pdo->exec("
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  ");

  // Best-effort ALTERs (ignore if already exists)
  $alters = [
  // Country + currency (older installs)
  "ALTER TABLE users ADD COLUMN country_code CHAR(2) NULL",
  "ALTER TABLE users ADD COLUMN country_name VARCHAR(100) NULL",
  "ALTER TABLE users ADD COLUMN currency_code VARCHAR(10) NULL",
  "ALTER TABLE users ADD COLUMN currency_symbol VARCHAR(12) NULL",

  // Profile fields
  "ALTER TABLE users ADD COLUMN phone VARCHAR(40) NULL",
  "ALTER TABLE users ADD COLUMN bank_name VARCHAR(80) NULL",
  "ALTER TABLE users ADD COLUMN bank_account VARCHAR(80) NULL",
  "ALTER TABLE users ADD COLUMN bank_locked_at TIMESTAMP NULL",

  // Wallet tx compatibility (older installs)
  "ALTER TABLE wallet_transactions ADD COLUMN note VARCHAR(255) NULL",
  "ALTER TABLE wallet_transactions ADD COLUMN idempotency_key VARCHAR(64) NULL",

];
  foreach ($alters as $sql) {
    try { $pdo->exec($sql); } catch (Throwable $e) { /* ignore */ }
  }

  // Compatibility: older installs may define wallet_transactions.type as ENUM/INT or a short VARCHAR.
  // In strict mode, inserting an unknown ENUM value (e.g. 'profit') can trigger "Data truncated" errors.
  // We normalize the column to VARCHAR(32) so new ledger types work reliably.
  try {
    $col = $pdo->query("SHOW COLUMNS FROM wallet_transactions LIKE 'type'")->fetch(PDO::FETCH_ASSOC);
    if ($col && isset($col['Type'])) {
      $t = strtolower((string)$col['Type']);
      $needsFix = false;

      if (strpos($t, 'varchar(') === 0) {
        if (preg_match('/varchar\((\d+)\)/i', $t, $m)) {
          $len = (int)$m[1];
          if ($len < 32) $needsFix = true;
        } else {
          $needsFix = true;
        }
      } elseif (strpos($t, 'enum(') === 0) {
        // If ENUM doesn't contain 'profit', insert will fail in strict mode.
        if (strpos($t, "'profit'") === false) $needsFix = true;
      } else {
        // int/text/other
        $needsFix = true;
      }

      if ($needsFix) {
        try { $pdo->exec("ALTER TABLE wallet_transactions MODIFY COLUMN type VARCHAR(32) NOT NULL"); } catch (Throwable $e) { /* ignore */ }
      }
    }
  } catch (Throwable $e) { /* ignore */ }
}

function ensure_notifications_schema(PDO $pdo): void {
  $pdo->exec("
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  ");

  $alters = [
  // Country + currency (older installs)
  "ALTER TABLE users ADD COLUMN country_code CHAR(2) NULL",
  "ALTER TABLE users ADD COLUMN country_name VARCHAR(100) NULL",
  "ALTER TABLE users ADD COLUMN currency_code VARCHAR(10) NULL",
  "ALTER TABLE users ADD COLUMN currency_symbol VARCHAR(12) NULL",

  // Profile fields
  "ALTER TABLE users ADD COLUMN phone VARCHAR(40) NULL",
  "ALTER TABLE users ADD COLUMN bank_name VARCHAR(80) NULL",
  "ALTER TABLE users ADD COLUMN bank_account VARCHAR(80) NULL",
  "ALTER TABLE users ADD COLUMN bank_locked_at TIMESTAMP NULL",

  // Wallet tx compatibility (older installs)
  "ALTER TABLE wallet_transactions ADD COLUMN note VARCHAR(255) NULL",
  "ALTER TABLE wallet_transactions ADD COLUMN idempotency_key VARCHAR(64) NULL",

];
  foreach ($alters as $sql) {
    try { $pdo->exec($sql); } catch (Throwable $e) { /* ignore */ }
  }
}

function notify_user(PDO $pdo, int $userId, string $title, string $body = '', string $link = ''): void {
  ensure_notifications_schema($pdo);
  $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, body, link, is_read) VALUES (?,?,?,?,0)");
  $stmt->execute([$userId, $title, $body !== '' ? $body : null, $link !== '' ? $link : null]);
}


function ensure_kyc_schema(PDO $pdo): void {
  $pdo->exec("
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  ");
}

function kyc_status(PDO $pdo, int $userId): string {
  ensure_kyc_schema($pdo);
  $stmt = $pdo->prepare("SELECT status FROM kyc_requests WHERE user_id=? LIMIT 1");
  $stmt->execute([$userId]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  return $row ? (string)$row['status'] : 'none';
}

function is_verified(PDO $pdo, int $userId): bool {
  return kyc_status($pdo, $userId) === 'approved';
}



// -------------------- User Profile --------------------

function ensure_user_profile_schema(PDO $pdo): void {
  // Best-effort ALTERs for profile fields. Ignore errors if already exist.
  $alters = [
  // Country + currency (older installs)
  "ALTER TABLE users ADD COLUMN country_code CHAR(2) NULL",
  "ALTER TABLE users ADD COLUMN country_name VARCHAR(100) NULL",
  "ALTER TABLE users ADD COLUMN currency_code VARCHAR(10) NULL",
  "ALTER TABLE users ADD COLUMN currency_symbol VARCHAR(12) NULL",

  // Profile fields
  "ALTER TABLE users ADD COLUMN phone VARCHAR(40) NULL",
  "ALTER TABLE users ADD COLUMN bank_name VARCHAR(80) NULL",
  "ALTER TABLE users ADD COLUMN bank_account VARCHAR(80) NULL",
  "ALTER TABLE users ADD COLUMN bank_locked_at TIMESTAMP NULL",

  // Wallet tx compatibility (older installs)
  "ALTER TABLE wallet_transactions ADD COLUMN note VARCHAR(255) NULL",
  "ALTER TABLE wallet_transactions ADD COLUMN idempotency_key VARCHAR(64) NULL",

  // Admin controls
  "ALTER TABLE users ADD COLUMN is_disabled TINYINT(1) NOT NULL DEFAULT 0",
  "ALTER TABLE users ADD COLUMN disabled_at TIMESTAMP NULL",
];
  foreach ($alters as $sql) {
    try { $pdo->exec($sql); } catch (Throwable $e) { /* ignore */ }
  }
}

// -------------------- User Status (Admin) --------------------

function ensure_user_status_schema(PDO $pdo): void {
  // Keep this small: only admin/account status columns.
  $alters = [
    "ALTER TABLE users ADD COLUMN is_disabled TINYINT(1) NOT NULL DEFAULT 0",
    "ALTER TABLE users ADD COLUMN disabled_at TIMESTAMP NULL",
  ];
  foreach ($alters as $sql) {
    try { $pdo->exec($sql); } catch (Throwable $e) { /* ignore */ }
  }
}

// -------------------- Investment --------------------

function ensure_investment_schema(PDO $pdo): void {
  $pdo->exec("
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  ");

  $pdo->exec("
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  ");

  // Seed default plans if empty
  $count = (int)$pdo->query("SELECT COUNT(*) AS c FROM investment_plans")->fetch(PDO::FETCH_ASSOC)['c'];
  if ($count === 0) {
    $stmt = $pdo->prepare("INSERT INTO investment_plans (name, description, min_amount, max_amount, roi_daily_percent, duration_days, status, sort_order) VALUES (?,?,?,?,?,?,1,?)");
    $stmt->execute(['Starter','Min <?= $CUR ?> 10', 10, null, 2.0, 30, 10]);
    $stmt->execute(['Standard','Min <?= $CUR ?> 100', 100, null, 3.0, 30, 20]);
    $stmt->execute(['Pro','Min <?= $CUR ?> 1000', 1000, null, 5.0, 30, 30]);
  }
}

