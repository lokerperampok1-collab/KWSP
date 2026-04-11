<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . "/../../config/app.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../config/schema.php";
require_once __DIR__ . "/../../wallet/_wallet.php";
require_once __DIR__ . "/../../auth/_csrf.php";

require_admin();
csrf_check();
header("Content-Type: application/json; charset=UTF-8");

$txId      = (int)($_POST["tx_id"] ?? 0);
$action    = (string)($_POST["action"] ?? ""); // approve | reject | void
$adminNote = trim((string)($_POST["admin_note"] ?? ""));

if ($txId <= 0 || !in_array($action, ["approve","reject","void"], true)) {
  http_response_code(422);
  echo json_encode(["ok"=>false, "error"=>"Invalid input"]);
  exit;
}

$newStatus = match ($action) {
  "approve" => "approved",
  "reject"  => "rejected",
  "void"    => "void",
};

function pick_col(array $cols, array $candidates): ?string {
  $lower = [];
  foreach ($cols as $c) $lower[strtolower($c)] = $c;
  foreach ($candidates as $cand) {
    $k = strtolower($cand);
    if (isset($lower[$k])) return $lower[$k];
  }
  return null;
}

try {
  $pdo->beginTransaction();

  // lock tx row
  $stmt = $pdo->prepare("
    SELECT id, user_id, currency, type, status, amount, note
    FROM wallet_transactions
    WHERE id = ?
    FOR UPDATE
  ");
  $stmt->execute([$txId]);
  $tx = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$tx) throw new RuntimeException("Transaction not found");
  if ((string)$tx["status"] !== "pending") throw new RuntimeException("Only pending tx can be updated");

  $type = (string)$tx["type"];
  if (!in_array($type, ["deposit","withdraw"], true)) {
    throw new RuntimeException("Only deposit/withdraw can be updated here");
  }

  $userId   = (int)$tx["user_id"];
  $currency = (string)$tx["currency"];
  $amount   = (float)$tx["amount"];

  // merge admin note
  $note = trim((string)($tx["note"] ?? ""));
  if ($adminNote !== "") {
    $note .= ($note !== "" ? " | " : "");
    $note .= "ADMIN: " . $adminNote;
  }

  // update status (+note if changed)
  if ($note !== (string)($tx["note"] ?? "")) {
    $stmt = $pdo->prepare("UPDATE wallet_transactions SET status=?, note=? WHERE id=?");
    $stmt->execute([$newStatus, $note !== "" ? $note : null, $txId]);
  } else {
    $stmt = $pdo->prepare("UPDATE wallet_transactions SET status=? WHERE id=?");
    $stmt->execute([$newStatus, $txId]);
  }

  // Best-effort: if legacy wallets table has a balance column, keep it in sync
  // (important now that pending withdraw affects wallet_balance()).
  $wcols = [];
  foreach (($pdo->query("SHOW COLUMNS FROM wallets")->fetchAll(PDO::FETCH_ASSOC) ?: []) as $r) $wcols[] = $r['Field'];
  $balanceCol = pick_col($wcols, ['balance_rm','balance','rm_balance','saldo','amount']);
  $userCol    = pick_col($wcols, ['user_id','uid','userid','user']);
  $curCol     = pick_col($wcols, ['currency','cur']);

  if ($balanceCol && $userCol) {
    $newBal = wallet_balance($pdo, $userId, $currency);
    if ($curCol) {
      $st = $pdo->prepare("UPDATE wallets SET `{$balanceCol}`=? WHERE `{$userCol}`=? AND `{$curCol}`=?");
      $st->execute([$newBal, $userId, $currency]);
    } else {
      $st = $pdo->prepare("UPDATE wallets SET `{$balanceCol}`=? WHERE `{$userCol}`=?");
      $st->execute([$newBal, $userId]);
    }
  }

  $pdo->commit();

  // Notify user (outside txn)
  try {
    if (function_exists('notify_user')) {
      $title = "Wallet update";
      $body  = strtoupper($type) . " " . strtoupper($currency) . " " . number_format($amount, 2, '.', '') . " => " . strtoupper($newStatus);
      notify_user($pdo, $userId, $title, $body, "/wallet.php");
    }
  } catch (Throwable $e) {}

  echo json_encode([
    "ok" => true,
    "message" => "Updated",
    "tx_id" => $txId,
    "status" => $newStatus
  ]);

} catch (Throwable $e) {
  if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) { $pdo->rollBack(); }
  $msg = $e->getMessage();
  $isServer = $e instanceof PDOException;
  http_response_code($isServer ? 500 : 422);
  echo json_encode(["ok"=>false, "error"=>$msg]);
}
