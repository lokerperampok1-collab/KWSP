<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/schema.php";
require_once __DIR__ . "/../auth/_auth.php";

header("Content-Type: application/json; charset=UTF-8");
require_login();

$userId = (int)($_SESSION["user_id"] ?? 0);
if ($userId <= 0) {
  http_response_code(401);
  echo json_encode(["ok"=>false, "error"=>"Not authenticated"]);
  exit;
}

try {
  // KYC status
  $kycStatus = function_exists('kyc_status') ? (string)kyc_status($pdo, $userId) : "none";
  $verified  = function_exists('is_verified') ? (bool)is_verified($pdo, $userId) : ($kycStatus === "approved");

  // Ambil user + bank fields (langsung dari DB)
  $stmt = $pdo->prepare("
    SELECT id, full_name, email, is_admin, created_at,
           country_code, country_name,
           currency_code, currency_symbol,
           phone, bank_name, bank_account, bank_locked_at
    FROM users
    WHERE id=? LIMIT 1
  ");
  $stmt->execute([$userId]);
  $u = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$u) {
    http_response_code(404);
    echo json_encode(["ok"=>false, "error"=>"User not found"]);
    exit;
  }

  $email = (string)($u["email"] ?? "");
  $username = ($email !== "" && strpos($email, "@") !== false) ? explode("@", $email)[0] : "user";

  echo json_encode([
    "ok" => true,
    "user_id" => (int)$u["id"],
    "kyc_status" => $kycStatus,
    "verified" => $verified,

    "full_name" => $u["full_name"] ?? null,
    "email" => $email,
    "username" => $username,
    "is_admin" => (int)($u["is_admin"] ?? 0),

    "country_code" => $u["country_code"] ?? null,
    "country_name" => $u["country_name"] ?? null,

    "currency_code" => $u["currency_code"] ?? null,
    "currency_symbol" => $u["currency_symbol"] ?? null,

    "phone" => $u["phone"] ?? null,
    "bank_name" => $u["bank_name"] ?? null,
    "bank_account" => $u["bank_account"] ?? null,
    "bank_locked_at" => $u["bank_locked_at"] ?? null,

    "created_at" => $u["created_at"] ?? null,
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["ok"=>false, "error"=>"Server error", "detail"=>$e->getMessage()]);
}
