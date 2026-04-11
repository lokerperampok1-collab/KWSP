<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/schema.php';
require_once __DIR__ . '/../auth/_csrf.php';
require_once __DIR__ . '/../wallet/_wallet.php';

require_login();

$userId = (int)($_SESSION['user_id'] ?? 0);
if ($userId <= 0) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'Not authenticated']); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok'=>false,'error'=>'Method not allowed']);
  exit;
}

try {
  csrf_check();

  ensure_investment_schema($pdo);
  ensure_wallet($pdo, $userId, 'RM');

  if (!is_verified($pdo, $userId)) {
    http_response_code(403);
    echo json_encode(['ok'=>false,'error'=>'UNVERIFIED','message'=>'Please complete KYC to invest.']);
    exit;
  }

  $planId = (int)($_POST['plan_id'] ?? 0);
  $amount = trim((string)($_POST['amount'] ?? ''));
  if ($planId <= 0 || !money_ok($amount)) {
    http_response_code(422);
    echo json_encode(['ok'=>false,'error'=>'Invalid input']);
    exit;
  }

  $stmt = $pdo->prepare("SELECT * FROM investment_plans WHERE id=? AND status=1 LIMIT 1");
  $stmt->execute([$planId]);
  $plan = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$plan) {
    http_response_code(404);
    echo json_encode(['ok'=>false,'error'=>'Plan not found']);
    exit;
  }

  $min = (float)$plan['min_amount'];
  $max = $plan['max_amount'] !== null ? (float)$plan['max_amount'] : null;
  $amt = (float)$amount;
  if ($amt < $min || ($max !== null && $amt > $max)) {
    http_response_code(422);
    echo json_encode(['ok'=>false,'error'=>'Amount out of range','min'=>$min,'max'=>$max]);
    exit;
  }

  $bal = (float)wallet_balance($pdo, $userId, 'RM');
  if ($bal < $amt) {
    http_response_code(422);
    echo json_encode(['ok'=>false,'error'=>'Insufficient balance','balance'=>$bal]);
    exit;
  }

  $roi = (float)$plan['roi_daily_percent'];
  $dur = (int)$plan['duration_days'];
  if ($dur <= 0) $dur = 30;

  $pdo->beginTransaction();

  // 1) create user investment (snapshot plan terms)
  $startAt = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');
  $endAt = (new DateTimeImmutable('now'))->modify('+' . $dur . ' days')->format('Y-m-d H:i:s');

  $stmt = $pdo->prepare("
    INSERT INTO user_investments (user_id, plan_id, plan_name, amount, roi_daily_percent, duration_days, start_at, end_at, status)
    VALUES (?,?,?,?,?,?,?,?, 'active')
  ");
  $stmt->execute([$userId, $planId, (string)$plan['name'], $amount, (string)$roi, (string)$dur, $startAt, $endAt]);
  $invId = (int)$pdo->lastInsertId();

  // 2) deduct wallet (approved immediately)
  $note = 'Investment #' . $invId . ' - ' . (string)$plan['name'];
  $stmt = $pdo->prepare("
    INSERT INTO wallet_transactions (user_id, currency, type, status, amount, note)
    VALUES (?, 'RM', 'investment', 'approved', ?, ?)
  ");
  $stmt->execute([$userId, $amount, $note]);

  $pdo->commit();

  // notify best-effort
  try { notify_user($pdo, $userId, 'Investment started', $note, '/investment.php'); } catch (Throwable $e) {}

  echo json_encode([
    'ok'=>true,
    'investment_id'=>$invId,
    'amount'=>number_format($amt,2,'.',''),
    'balance_after'=>wallet_balance($pdo, $userId, 'RM')
  ]);
} catch (Throwable $e) {
  if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) $pdo->rollBack();
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Server error','detail'=>$e->getMessage()]);
}
