<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';

require_login();

$userId = (int)($_SESSION['user_id'] ?? 0);
$curKey = function_exists('wallet_currency_key') ? wallet_currency_key() : 'RM';
$curDisp = function_exists('currency_display') ? currency_display() : 'RM';
if ($userId <= 0) {
  http_response_code(401);
  echo json_encode(['ok'=>false,'error'=>'Not authenticated']);
  exit;
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

$limit = (int)($_GET['limit'] ?? 20);
if ($limit <= 0 || $limit > 100) $limit = 20;

try {
  // wallets columns
  $wcols = [];
  foreach (($pdo->query("SHOW COLUMNS FROM wallets")->fetchAll(PDO::FETCH_ASSOC) ?: []) as $r) $wcols[] = $r['Field'];
  $userCol     = pick_col($wcols, ['user_id','uid','userid','user']);
  $walletIdCol = pick_col($wcols, ['id','wallet_id','wid']);

  if (!$userCol) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'Server error','detail'=>'wallets table has no user_id/uid column']);
    exit;
  }

  // get wallet identifier
  $selWalletId = $walletIdCol ? $walletIdCol : $userCol;
  $st = $pdo->prepare("SELECT {$selWalletId} AS wallet_id FROM wallets WHERE {$userCol}=? LIMIT 1");
  $st->execute([$userId]);
  $w = $st->fetch(PDO::FETCH_ASSOC);
  $walletId = $w ? (string)$w['wallet_id'] : "";

  // tx columns
  $tcols = [];
  foreach (($pdo->query("SHOW COLUMNS FROM wallet_transactions")->fetchAll(PDO::FETCH_ASSOC) ?: []) as $r) $tcols[] = $r['Field'];

  $linkCol   = pick_col($tcols, ['wallet_id','user_id','uid','userid']);
  $typeCol   = pick_col($tcols, ['type','trx_type','action']);
  $amountCol = pick_col($tcols, ['amount','amount_rm','rm_amount','value']);
  $statusCol = pick_col($tcols, ['status','state']);
  $createdCol= pick_col($tcols, ['created_at','date','created']);
  $noteCol   = pick_col($tcols, ['note','description','remark']);

  if (!$linkCol) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'Server error','detail'=>'wallet_transactions has no wallet_id/user_id column']);
    exit;
  }
  if (!$amountCol) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'Server error','detail'=>'wallet_transactions has no amount column']);
    exit;
  }

  // Build SELECT list safely
  $fields = [];
  $fields[] = "id"; // if not exists, fallback below
  $hasId = in_array('id', array_map('strtolower',$tcols), true);
  if (!$hasId) $fields[0] = "{$linkCol} AS id"; // stable identifier

  $fields[] = ($typeCol ? "{$typeCol} AS type" : "'' AS type");
  $fields[] = "{$amountCol} AS amount";
  $fields[] = ($statusCol ? "{$statusCol} AS status" : "'' AS status");
  $fields[] = ($createdCol ? "{$createdCol} AS created_at" : "'' AS created_at");
  $fields[] = ($noteCol ? "{$noteCol} AS note" : "'' AS note");

  // Where clause
  $whereVal = ($linkCol === 'wallet_id' || strtolower($linkCol)==='wallet_id') ? $walletId : (string)$userId;
  if ($whereVal === "" || $whereVal === "0") {
    echo json_encode(['ok'=>true,'wallet_id'=>$walletId,'transactions'=>[]]);
    exit;
  }

  $sql = "SELECT ".implode(", ", $fields)." FROM wallet_transactions WHERE {$linkCol} = ? ORDER BY ".($hasId ? "id" : "{$createdCol}")." DESC LIMIT {$limit}";
  $q = $pdo->prepare($sql);
  $q->execute([$whereVal]);
  $rows = $q->fetchAll(PDO::FETCH_ASSOC) ?: [];

  foreach ($rows as &$r) {
    if (isset($r['amount']) && is_numeric($r['amount'])) $r['amount'] = number_format((float)$r['amount'], 2, '.', '');
  }

  echo json_encode(['ok'=>true,'wallet_id'=>$walletId,'transactions'=>$rows]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Server error','detail'=>$e->getMessage()]);
}
