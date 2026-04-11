<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../wallet/_wallet.php";

require_login();
header("Content-Type: application/json; charset=UTF-8");

$userId = (int)($_SESSION["user_id"] ?? 0);
if ($userId <= 0) {
  echo json_encode(["ok"=>false, "error"=>"Not logged in"]);
  exit;
}

try {
  // wallet balance (approved only)
  $walletRM = wallet_balance($pdo, $userId, "RM");

  // total profit (approved only)
  $stmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount),0) AS s
    FROM wallet_transactions
    WHERE user_id=? AND currency='RM' AND status='approved' AND type='profit'
  ");
  $stmt->execute([$userId]);
  $profitRM = (string)$stmt->fetchColumn();

  // bonus & referral belum ada schema -> sementara 0 (nanti tinggal nyambungin)
  $bonusRM = "0";
  $referrals = 0;

  echo json_encode([
    "ok" => true,
    "stats" => [
      "referrals" => $referrals,
      "bonus_rm"  => $bonusRM,
      "profit_rm" => $profitRM,
      "wallet_rm" => $walletRM,
    ]
  ]);
} catch (Throwable $e) {
  echo json_encode(["ok"=>false, "error"=>$e->getMessage()]);
}
