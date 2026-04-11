<?php
declare(strict_types=1);

// IMPORTANT: API endpoint must ALWAYS return valid JSON (no PHP notices/warnings in output)
@ini_set('display_errors', '0');
@ini_set('html_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

if (session_status() === PHP_SESSION_NONE) {
  @session_start();
}

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/currency.php';
require_once __DIR__ . '/../wallet/_wallet.php';

header('Content-Type: application/json; charset=utf-8');

// helper: always return JSON
$jsonOut = function(array $payload): void {
  if (ob_get_length()) { @ob_clean(); }
  echo json_encode($payload);
  exit;
};

try {
  require_login();

  $userId = (int)($_SESSION['user_id'] ?? 0);
  if ($userId <= 0) {
    $jsonOut(['ok' => false, 'error' => 'unauthorized']);
  }

  $currencyCode = user_currency_code($pdo);

  // Ensure wallet exists for this user+currency
  $wallet = ensure_wallet($pdo, $userId, $currencyCode);

  // Transactions
  $stmt = $pdo->prepare(
    'SELECT id, type, amount, note, status, created_at
       FROM wallet_transactions
      WHERE user_id = ? AND currency = ?
      ORDER BY id DESC
      LIMIT 200'
  );
  $stmt->execute([$userId, $currencyCode]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $data = [];
  foreach ($rows as $r) {
    $data[] = [
      'id' => (int)$r['id'],
      'date' => (string)$r['created_at'],
      'type' => (string)$r['type'],
      'amount' => number_format((float)$r['amount'], 2, '.', ''),
      'note' => (string)$r['note'],
      'status' => (string)$r['status'],
    ];
  }

  $jsonOut([
    'ok' => true,
    'currency_code' => $currencyCode,
    'wallet_balance' => (float)$wallet['balance'],
    'data' => $data,
  ]);
} catch (Throwable $e) {
  $jsonOut([
    'ok' => false,
    'error' => 'server_error',
    'message' => $e->getMessage(),
  ]);
}
