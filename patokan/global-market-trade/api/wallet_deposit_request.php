<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/schema.php";
require_once __DIR__ . "/../wallet/_wallet.php";
require_once __DIR__ . "/../auth/_csrf.php";

header("Content-Type: application/json; charset=UTF-8");

require_login();
csrf_check();

if (($_SERVER["REQUEST_METHOD"] ?? "GET") !== "POST") {
  http_response_code(405);
  echo json_encode(["ok"=>false, "error"=>"Method not allowed"]);
  exit;
}

$userId = (int)($_SESSION["user_id"] ?? 0);

$curKey  = function_exists('wallet_currency_key') ? wallet_currency_key() : 'RM';
$curDisp = function_exists('currency_display') ? currency_display() : $curKey;

try {
  ensure_wallet($pdo, $userId, $curKey);

  $amount = trim((string)($_POST["amount"] ?? ""));
  $note   = trim((string)($_POST["note"] ?? "Deposit request"));

  if (!money_ok($amount)) {
    http_response_code(422);
    echo json_encode(["ok"=>false, "error"=>"Invalid amount"]);
    exit;
  }

  $idempo = bin2hex(random_bytes(16));

  $stmt = $pdo->prepare("
    INSERT INTO wallet_transactions (user_id, currency, type, status, amount, note, idempotency_key)
    VALUES (?, ?, 'deposit', 'pending', ?, ?, ?)
  ");
  $stmt->execute([$userId, $curKey, $amount, $note, $idempo]);

  // notif best-effort (jangan bikin request dianggap gagal)
  try {
    notify_user($pdo, $userId, "Deposit request submitted", $amount . " " . $curDisp, "/wallet_deposit.php");
  } catch (Throwable $e) {}

  echo json_encode(["ok"=>true, "message"=>"Deposit requested (pending approval)."]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["ok"=>false, "error"=>"Server error", "detail"=>$e->getMessage()]);
}
