<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

if (!isset($_SESSION["user_id"])) {
  header("Location: /login.php");
  exit;
}

$uid = (int)$_SESSION["user_id"];

// Pastikan $pdo sudah ada dari require db.php (admin.php sudah require db.php dulu)
if (!isset($pdo)) {
  // fallback kalau suatu saat file ini dipakai tanpa db.php
  require_once __DIR__ . "/../config/db.php";
}

// Ensure optional disabled-account columns exist (safe to call repeatedly)
try {
  require_once __DIR__ . "/../config/schema.php";
  if (function_exists('ensure_user_status_schema')) {
    ensure_user_status_schema($pdo);
  }
} catch (Throwable $e) { /* ignore */ }

// Cek admin + status langsung dari DB
$stmt = $pdo->prepare("SELECT is_admin, COALESCE(is_disabled,0) AS is_disabled FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$uid]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$isAdmin = (int)($row["is_admin"] ?? 0);

// Block disabled accounts
if ((int)($row["is_disabled"] ?? 0) === 1) {
  session_unset();
  session_destroy();
  header("Location: /login.php?err=" . urlencode("Account is disabled. Please contact admin."));
  exit;
}

if ($isAdmin !== 1) {
  // Lempar user biasa ke dashboard (lebih enak daripada 403)
  header("Location: /dashboard.php");
  exit;
}
