<?php
declare(strict_types=1);
$siteName = defined('APP_NAME') ? APP_NAME : 'Global Market Trade';
// Optional shared helpers (safe include)
$__currencyHelper = __DIR__ . '/../helpers/currency.php';
if (file_exists($__currencyHelper)) {
    require_once $__currencyHelper;
}

function require_login(): void {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();

  if (empty($_SESSION["user_id"])) {
    header("Location: /login.php");
    exit;
  }

  // If DB connection is available, enforce disabled-account logout.
  // (Safe no-op for older installs where the column doesn't exist.)
  try {
    if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
      $pdo = $GLOBALS['pdo'];
      if (!function_exists('ensure_user_status_schema')) {
        require_once __DIR__ . '/schema.php';
      }
      ensure_user_status_schema($pdo);
      // Load profile fields used for currency key/session
      if (function_exists('ensure_user_profile_schema')) {
        try { ensure_user_profile_schema($pdo); } catch (Throwable $e) { /* ignore */ }
      }

      $uid = (int)($_SESSION['user_id'] ?? 0);
      if ($uid > 0) {
        $st = $pdo->prepare("SELECT is_disabled, currency_code, currency_symbol FROM users WHERE id=? LIMIT 1");
        $st->execute([$uid]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        if ($r) {
          // Keep session currency info in sync with DB (used by wallet_currency_key / currency_display)
          $_SESSION['user']['currency_code']   = (string)($r['currency_code'] ?? ($_SESSION['user']['currency_code'] ?? ''));
          $_SESSION['user']['currency_symbol'] = (string)($r['currency_symbol'] ?? ($_SESSION['user']['currency_symbol'] ?? 'RM'));
          // Backwards-compat for older pages
          $_SESSION['currency_code']   = (string)$_SESSION['user']['currency_code'];
          $_SESSION['currency_symbol'] = (string)$_SESSION['user']['currency_symbol'];

          if ((int)($r['is_disabled'] ?? 0) === 1) {
          session_unset();
          session_destroy();
          header("Location: /login.php?err=" . urlencode("Account is disabled. Please contact admin."));
          exit;
        }
        }
      }
    }
  } catch (Throwable $e) { /* ignore */ }
}

function is_admin(): bool {
  return (int)($_SESSION["is_admin"] ?? 0) === 1;
}

function require_admin(): void {
  require_login();
  if (!is_admin()) {
    http_response_code(403);
    exit("Forbidden");
  }
}

function e($s): string {
  return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8");
}