<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/schema.php";
require_once __DIR__ . "/../wallet/_wallet.php";
require_once __DIR__ . "/../auth/_csrf.php";

require_login();
csrf_check();
header("Content-Type: application/json; charset=UTF-8");

$fromId = (int)($_SESSION["user_id"] ?? 0);

if (!is_verified($pdo, $fromId)) {
  http_response_code(403);
  echo json_encode(["ok"=>false, "error"=>"Account not verified (KYC required)"]); 
  exit;
}
ensure_wallet($pdo, $fromId, "RM");

$toEmail = trim((string)($_POST["to_email"] ?? ""));
$amount  = trim((string)($_POST["amount"] ?? ""));
$note    = trim((string)($_POST["note"] ?? "Transfer"));

if ($toEmail === "" || !money_ok($amount)) {
  http_response_code(422);
  echo json_encode(["ok"=>false, "error"=>"Invalid input"]);
  exit;
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
$stmt->execute([$toEmail]);
$to = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$to) {
  http_response_code(404);
  echo json_encode(["ok"=>false, "error"=>"Target user not found"]);
  exit;
}

$toId = (int)$to["id"];
if ($toId === $fromId) {
  http_response_code(422);
  echo json_encode(["ok"=>false, "error"=>"Cannot transfer to yourself"]);
  exit;
}

try {
  $pdo->beginTransaction();
  $bal = (float)wallet_balance($pdo, $fromId, "RM");
  if ((float)$amount > $bal) {
    throw new RuntimeException("Insufficient balance");
  }

  ensure_wallet($pdo, $toId, "RM");

  $idempo = hash("sha256", $fromId.'|transfer|'.$toId.'|'.$amount.'|'.($_POST["csrf"] ?? ''));

  // transfer_out
  $stmt = $pdo->prepare("
    INSERT INTO wallet_transactions
      (user_id, currency, type, status, amount, note, counterparty_user_id, idempotency_key)
    VALUES
      (?, 'RM', 'transfer_out', 'approved', ?, ?, ?, ?)
  ");
  $stmt->execute([$fromId, $amount, $note, $toId, $idempo.'-out']);
  $outId = (int)$pdo->lastInsertId();

  // transfer_in
  $stmt = $pdo->prepare("
    INSERT INTO wallet_transactions
      (user_id, currency, type, status, amount, note, counterparty_user_id, related_tx_id, idempotency_key)
    VALUES
      (?, 'RM', 'transfer_in', 'approved', ?, ?, ?, ?, ?)
  ");
  $stmt->execute([$toId, $amount, $note, $fromId, $outId, $idempo.'-in']);
  $inId = (int)$pdo->lastInsertId();

  // link back
  $stmt = $pdo->prepare("UPDATE wallet_transactions SET related_tx_id=? WHERE id=?");
  $stmt->execute([$inId, $outId]);

  $pdo->commit();
  // notifications (best-effort)
  try { notify_user($pdo, $fromId, "Transfer sent", "Your transfer was processed successfully.", "/wallet_transfer.php"); } catch (Throwable $e) {}
  try { notify_user($pdo, $toId, "Transfer received", "You received a transfer.", "/wallet_transfer.php"); } catch (Throwable $e) {}
echo json_encode(["ok"=>true, "message"=>"Transfer success"]);
} catch (Throwable $e) {
  if ($pdo->inTransaction()) { $pdo->rollBack(); }
  http_response_code(422);
  echo json_encode(["ok"=>false, "error"=>$e->getMessage()]);
}
