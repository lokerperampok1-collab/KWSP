<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/schema.php';
require_once __DIR__ . '/../wallet/_wallet.php';
require_once __DIR__ . '/../auth/_csrf.php';
require_once __DIR__ . '/../auth/_auth.php';

header('Content-Type: application/json; charset=UTF-8');

require_login();
csrf_check();

$userId = (int)($_SESSION['user_id'] ?? 0);
if ($userId <= 0) {
  http_response_code(401);
  echo json_encode(['ok'=>false,'error'=>'Not authenticated']);
  exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok'=>false,'error'=>'Method not allowed']);
  exit;
}

function column_exists(PDO $pdo, string $table, string $col): bool {
  try {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
    $stmt->execute([$col]);
    return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
  } catch (Throwable $e) {
    return false;
  }
}

$curKey  = function_exists('wallet_currency_key') ? wallet_currency_key() : 'RM';
$curDisp = function_exists('currency_display') ? currency_display() : $curKey;

// KYC gate
$verified = function_exists('is_verified') ? (bool)is_verified($pdo, $userId) : (
  function_exists('kyc_status') ? (string)kyc_status($pdo, $userId) === 'approved' : false
);
if (!$verified) {
  http_response_code(403);
  echo json_encode(['ok'=>false,'error'=>'Account not verified (KYC required)']);
  exit;
}

$amount = trim((string)($_POST['amount'] ?? ''));
$note   = trim((string)($_POST['note'] ?? ''));

// validate amount
if (!function_exists('money_ok') || !money_ok($amount)) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'Invalid amount']);
  exit;
}

try {
  ensure_wallet($pdo, $userId, $curKey);
  $bal = (float)wallet_balance($pdo, $userId, $curKey);
  if ($bal < (float)$amount) {
    http_response_code(422);
    echo json_encode(['ok'=>false,'error'=>'Insufficient balance']);
    exit;
  }

  // Prefer bank/account from profile, fallback to posted pay for compatibility
  $bankName = '';
  $bankAcc  = '';
  if (column_exists($pdo, 'users', 'bank_name') && column_exists($pdo, 'users', 'bank_account')) {
    $stmt = $pdo->prepare("SELECT bank_name, bank_account FROM users WHERE id=? LIMIT 1");
    $stmt->execute([$userId]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    $bankName = trim((string)($u['bank_name'] ?? ''));
    $bankAcc  = trim((string)($u['bank_account'] ?? ''));
  }

  $payFallback = trim((string)($_POST['pay'] ?? ''));
  if ($bankName === '' || $bankAcc === '') {
    if ($payFallback === '') {
      http_response_code(422);
      echo json_encode(['ok'=>false,'error'=>'Set Bank & No Rek di Profile dulu']);
      exit;
    }
    $bankInfo = $payFallback;
  } else {
    $bankInfo = "Bank: {$bankName} | Acc: {$bankAcc}";
  }

  $fullNote = $bankInfo;
  if ($note !== '') $fullNote .= " | Note: {$note}";

  $idempo = bin2hex(random_bytes(16));

  $stmt = $pdo->prepare("
    INSERT INTO wallet_transactions (user_id, currency, type, status, amount, note, idempotency_key)
    VALUES (?, ?, 'withdraw', 'pending', ?, ?, ?)
  ");
  $stmt->execute([$userId, $curKey, $amount, $fullNote, $idempo]);

  try {
    notify_user($pdo, $userId, 'Withdraw request submitted', $amount.' '.$curDisp, '/wallet_withdraw.php');
  } catch (Throwable $e) {}

  echo json_encode(['ok'=>true,'message'=>'Withdraw requested (pending approval).']);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Server error','detail'=>$e->getMessage()]);
}
