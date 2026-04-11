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

try { ensure_user_profile_schema($pdo); } catch (Throwable $e) { /* ignore */ }
try { ensure_wallet_schema($pdo); } catch (Throwable $e) { /* ignore */ }

function wallet_key_for_user(array $u): string {
  $code = strtoupper(trim((string)($u['currency_code'] ?? '')));
  $sym  = strtoupper(trim((string)($u['currency_symbol'] ?? '')));
  if ($code !== '') {
    if ($code === 'MYR' && $sym === 'RM') return 'RM';
    return $code;
  }
  return $sym !== '' ? $sym : 'RM';
}

function pick_col(array $cols, array $candidates): ?string {
  $lower = [];
  foreach ($cols as $c) $lower[strtolower($c)] = $c;
  foreach ($candidates as $cand) {
    $k = strtolower($cand);
    if (isset($lower[$k])) return $lower[$k];
  }
  return null;
}

$meId = (int)($_SESSION['user_id'] ?? 0);

$userId = (int)($_POST['user_id'] ?? 0);
$op = (string)($_POST['op'] ?? 'add'); // add|sub
$amountStr = trim((string)($_POST['amount'] ?? ''));
$note = trim((string)($_POST['note'] ?? ''));

// Admin adjustment is treated as PROFIT by default.
// (Deposit is not profit; admin top-up should appear as profit on dashboard.)
$kind = 'profit';

if ($userId <= 0 || !in_array($op, ['add','sub'], true)) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'Invalid input']);
  exit;
}

if (!preg_match('/^\d+(\.\d{1,2})?$/', $amountStr)) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'Amount must be a number with up to 2 decimals']);
  exit;
}

$amount = (float)$amountStr;
if ($amount < 0.01) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'Amount must be at least 0.01']);
  exit;
}

try {
  // Load user + currency info
  $stmt = $pdo->prepare('SELECT id, currency_code, currency_symbol FROM users WHERE id=? LIMIT 1');
  $stmt->execute([$userId]);
  $u = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$u) {
    http_response_code(404);
    echo json_encode(['ok'=>false,'error'=>'User not found']);
    exit;
  }

  $curKey = wallet_key_for_user($u);
  ensure_wallet($pdo, $userId, $curKey);

  $signed = ($op === 'sub') ? (-1 * $amount) : $amount;
  $adminTag = 'ADMIN#' . ($meId > 0 ? (string)$meId : '0');
  $label = ($kind === 'profit') ? 'Profit adjust' : 'Balance adjust';
  $finalNote = trim($label . ' ' . ($signed >= 0 ? '+' : '') . number_format($signed, 2, '.', '') . ' ' . $curKey . ' | ' . $adminTag . ($note !== '' ? (' | ' . $note) : ''));

  $pdo->beginTransaction();

  // Insert an approved entry so it reflects immediately in wallet_balance()
  // Some older installs may not have the `note` column yet; build INSERT dynamically and safely.
  $tcols = [];
  foreach (($pdo->query('SHOW COLUMNS FROM wallet_transactions')->fetchAll(PDO::FETCH_ASSOC) ?: []) as $r) $tcols[] = $r['Field'];

  $colUser   = pick_col($tcols, ['user_id','uid','userid','user']);
  $colCur    = pick_col($tcols, ['currency','cur']);
  $colType   = pick_col($tcols, ['type','tx_type','transaction_type']);
  $colStatus = pick_col($tcols, ['status','tx_status']);
  $colAmount = pick_col($tcols, ['amount','amt','value']);
  $colNote   = pick_col($tcols, ['note','remarks','description','memo']);

  if (!$colUser || !$colCur || !$colType || !$colStatus || !$colAmount) {
    throw new RuntimeException('wallet_transactions schema missing required columns');
  }

  $insCols = [$colUser, $colCur, $colType, $colStatus, $colAmount];
  $insVals = array_fill(0, count($insCols), '?');
  $params  = [$userId, $curKey, $kind, 'approved', $signed];

  if ($colNote) {
    $insCols[] = $colNote;
    $insVals[] = '?';
    $params[]  = ($finalNote !== '' ? $finalNote : null);
  }

  $sqlIns = "INSERT INTO wallet_transactions (`" . implode("`,`", $insCols) . "`) VALUES (" . implode(",", $insVals) . ")";
  $pdo->prepare($sqlIns)->execute($params);

  $txId = (int)$pdo->lastInsertId();

  // Best-effort: sync legacy wallets balance column if present
  try {
    $wcols = [];
    foreach (($pdo->query('SHOW COLUMNS FROM wallets')->fetchAll(PDO::FETCH_ASSOC) ?: []) as $r) $wcols[] = $r['Field'];
    $balanceCol = pick_col($wcols, ['balance_rm','balance','rm_balance','saldo','amount']);
    $userCol    = pick_col($wcols, ['user_id','uid','userid','user']);
    $curCol     = pick_col($wcols, ['currency','cur']);
    if ($balanceCol && $userCol) {
      $newBal = wallet_balance($pdo, $userId, $curKey);
      if ($curCol) {
        $pdo->prepare("UPDATE wallets SET `{$balanceCol}`=? WHERE `{$userCol}`=? AND `{$curCol}`=?")->execute([$newBal, $userId, $curKey]);
      } else {
        $pdo->prepare("UPDATE wallets SET `{$balanceCol}`=? WHERE `{$userCol}`=?")->execute([$newBal, $userId]);
      }
    }
  } catch (Throwable $e) { /* ignore */ }

  $pdo->commit();

  // Notify user (optional)
  try {
    if (function_exists('notify_user')) {
      $title = ($kind === 'profit') ? 'Profit adjusted' : 'Balance adjusted';
      $body  = (($kind === 'profit') ? 'Your profit was adjusted by admin: ' : 'Your balance was adjusted by admin: ') . ($signed >= 0 ? '+' : '') . number_format($signed, 2, '.', '') . ' ' . $curKey;
      notify_user($pdo, $userId, $title, $body, '/wallet.php');
    }
  } catch (Throwable $e) {}

  echo json_encode([
    'ok'=>true,
    'tx_id'=>$txId,
    'user_id'=>$userId,
    'type'=>$kind,
    'currency'=>$curKey,
    'delta'=>number_format($signed, 2, '.', ''),
    'new_balance'=>wallet_balance($pdo, $userId, $curKey),
  ]);

} catch (Throwable $e) {
  if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) $pdo->rollBack();
  http_response_code(500);
  $msg = trim((string)$e->getMessage());
  if ($msg === '') $msg = 'Server error';
  echo json_encode(['ok'=>false,'error'=>$msg]);
}
