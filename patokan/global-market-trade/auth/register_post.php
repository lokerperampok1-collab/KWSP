<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

require_once __DIR__ . "/_csrf.php";
csrf_check();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/app.php';

$fullName = trim((string)($_POST["full_name"] ?? ""));
$email    = trim((string)($_POST["email"] ?? ""));
$pass     = (string)($_POST["password"] ?? "");
$agree    = (string)($_POST["agree"] ?? "");
$ref      = trim((string)($_POST["ref"] ?? ""));

// Negara dipilih user (2 huruf)
$countryCode = strtoupper(trim((string)($_POST["country_code"] ?? "")));

if ($fullName === "" || $email === "" || $pass === "") {
  header("Location: /signup.php?err=" . urlencode("Invalid input"));
  exit;
}
if (strlen($pass) < 8) {
  header("Location: /signup.php?err=" . urlencode("Password must be at least 8 characters"));
  exit;
}
if ($agree !== "on") {
  header("Location: /signup.php?err=" . urlencode("You must agree to Terms"));
  exit;
}
if ($countryCode === "" || strlen($countryCode) !== 2) {
  header("Location: /signup.php?err=" . urlencode("Please select your country"));
  exit;
}

// Ambil currency dari tabel countries (server-side, biar tidak bisa dipalsukan dari browser)
$stmtC = $pdo->prepare("SELECT currency_code, currency_symbol FROM countries WHERE country_code=? LIMIT 1");
$stmtC->execute([$countryCode]);
$cRow = $stmtC->fetch(PDO::FETCH_ASSOC);

if (!$cRow) {
  header("Location: /signup.php?err=" . urlencode("Invalid country"));
  exit;
}

$currencyCode   = (string)($cRow["currency_code"] ?? "MYR");
$currencySymbol = (string)($cRow["currency_symbol"] ?? "RM");
if ($currencyCode === "") $currencyCode = "MYR";
if ($currencySymbol === "") $currencySymbol = $currencyCode;

try {
  // email unique?
  $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :e LIMIT 1");
  $stmt->execute([":e" => $email]);
  if ($stmt->fetch()) {
    header("Location: /signup.php?err=" . urlencode("Email already used"));
    exit;
  }

  $hash = password_hash($pass, PASSWORD_DEFAULT);

  // Kolom referral opsional tergantung schema lama
  $hasReferral = false;
  try {
    $cols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_ASSOC) ?: [];
    foreach ($cols as $r) {
      if (strtolower($r["Field"]) === "referrer") { $hasReferral = true; break; }
    }
  } catch (Throwable $e) {}

  if ($hasReferral) {
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, referrer, country_code, currency_code, currency_symbol) VALUES (:n,:e,:p,:r,:cc,:cur,:sym)");
    $stmt->execute([
      ":n" => $fullName,
      ":e" => $email,
      ":p" => $hash,
      ":r" => ($ref !== "" ? $ref : null),
      ":cc" => $countryCode,
      ":cur" => $currencyCode,
      ":sym" => $currencySymbol,
    ]);
  } else {
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, country_code, currency_code, currency_symbol) VALUES (:n,:e,:p,:cc,:cur,:sym)");
    $stmt->execute([
      ":n" => $fullName,
      ":e" => $email,
      ":p" => $hash,
      ":cc" => $countryCode,
      ":cur" => $currencyCode,
      ":sym" => $currencySymbol,
    ]);
  }

  $userId = (int)$pdo->lastInsertId();
  $_SESSION["user_id"] = $userId;
  $_SESSION["is_admin"] = 0;
  $_SESSION["country_code"] = $countryCode;
  $_SESSION["currency_code"] = $currencyCode;
  $_SESSION["currency_symbol"] = $currencySymbol;

  header("Location: /dashboard.php");
  exit;
} catch (Throwable $e) {
  header("Location: /signup.php?err=" . urlencode("Server error"));
  exit;
}
