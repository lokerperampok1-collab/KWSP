<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

require_once __DIR__ . "/_csrf.php";
csrf_check();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/schema.php';

// Ensure optional account-status columns exist (safe to call repeatedly)
try { ensure_user_status_schema($pdo); } catch (Throwable $e) { /* ignore */ }

$email = trim((string)($_POST["email"] ?? ""));
$pass  = (string)($_POST["password"] ?? "");

if ($email === "" || $pass === "") {
  header("Location: /login.php?err=" . urlencode("Invalid input"));
  exit;
}

$stmt = $pdo->prepare("SELECT id, password_hash, is_admin, is_disabled, full_name, country_code, currency_code, currency_symbol FROM users WHERE email = :e LIMIT 1");
$stmt->execute([":e" => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
  header("Location: /login.php?err=" . urlencode("Email or password incorrect"));
  exit;
}

if (!password_verify($pass, (string)$user["password_hash"])) {
  header("Location: /login.php?err=" . urlencode("Email or password incorrect"));
  exit;
}

if ((int)($user['is_disabled'] ?? 0) === 1) {
  header("Location: /login.php?err=" . urlencode("Account is disabled. Please contact admin."));
  exit;
}

$_SESSION["user_id"]  = (int)$user["id"];
$_SESSION["is_admin"] = (int)($user["is_admin"] ?? 0);

// Currency session (fallback MYR/RM)
$_SESSION["country_code"] = (string)($user["country_code"] ?? "MY");
$_SESSION["currency_code"] = (string)($user["currency_code"] ?? "MYR");
$_SESSION["currency_symbol"] = (string)($user["currency_symbol"] ?? "RM");
if ($_SESSION["currency_code"] === "") $_SESSION["currency_code"] = "MYR";
if ($_SESSION["currency_symbol"] === "") $_SESSION["currency_symbol"] = $_SESSION["currency_code"];

if ($_SESSION["is_admin"] == 1) {
  header("Location: /admin.php");
  exit;
}
header("Location: /dashboard.php");
exit;
