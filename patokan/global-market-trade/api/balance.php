<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/schema.php';
require_once __DIR__ . '/../wallet/_wallet.php';
require_once __DIR__ . '/../auth/_auth.php';

require_login();

$userId = (int)($_SESSION['user_id'] ?? 0);
if ($userId <= 0) {
  http_response_code(401);
  echo json_encode(['ok' => false, 'error' => 'Not authenticated']);
  exit;
}

$curKey  = function_exists('wallet_currency_key') ? wallet_currency_key() : 'RM';
$curDisp = function_exists('currency_display') ? currency_display() : $curKey;

try {
  ensure_wallet($pdo, $userId, $curKey);
    $balance = wallet_balance($pdo, $userId, $curKey);

  // Total profit shown on dashboard (approved profit transactions only)
  $stp = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM wallet_transactions WHERE user_id=? AND currency=? AND type='profit' AND status='approved'");
  $stp->execute([$userId, $curKey]);
  $profit = (float)$stp->fetchColumn();

  echo json_encode([
    'ok' => true,
    'currency' => $curDisp,
    'currency_key' => $curKey,
    // backwards-compat for existing JS
    'balance_rm' => $balance,
    'balance' => $balance,
    'profit' => $profit,
    'source' => 'wallet_transactions'
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Server error', 'detail' => $e->getMessage()]);
}
