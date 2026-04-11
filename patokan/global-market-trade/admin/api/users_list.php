<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . "/../../config/app.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../config/schema.php";
require_once __DIR__ . "/../../wallet/_wallet.php";

require_admin();
header("Content-Type: application/json; charset=UTF-8");

// Ensure optional columns/tables exist (safe to call repeatedly)
try { ensure_user_profile_schema($pdo); } catch (Throwable $e) { /* ignore */ }
try { ensure_user_status_schema($pdo); } catch (Throwable $e) { /* ignore */ }
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

$q = trim((string)($_GET["q"] ?? ""));
$role = (string)($_GET["role"] ?? "all"); // all|admin|member
$status = (string)($_GET['status'] ?? 'all'); // all|active|disabled
$page = (int)($_GET["page"] ?? 1);
$limit = (int)($_GET["limit"] ?? 25);

if ($page < 1) $page = 1;
if ($limit < 1) $limit = 25;
if ($limit > 200) $limit = 200;

$where = [];
$params = [];

if ($q !== "") {
  $where[] = "(u.full_name LIKE :q OR u.email LIKE :q)";
  $params[":q"] = "%" . $q . "%";
}

if ($role === "admin") {
  $where[] = "u.is_admin = 1";
} elseif ($role === "member") {
  $where[] = "u.is_admin = 0";
} else {
  $role = "all";
}

if ($status === 'active') {
  $where[] = "COALESCE(u.is_disabled,0) = 0";
} elseif ($status === 'disabled') {
  $where[] = "COALESCE(u.is_disabled,0) = 1";
} else {
  $status = 'all';
}

$whereSql = $where ? ("WHERE " . implode(" AND ", $where)) : "";
$offset = ($page - 1) * $limit;

try {
  // total
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM users u $whereSql");
  $stmt->execute($params);
  $total = (int)$stmt->fetchColumn();

  // items
  $sql = "
    SELECT
      u.id,
      u.full_name,
      u.email,
      u.is_admin,
      COALESCE(u.is_disabled,0) AS is_disabled,
      u.disabled_at,
      u.phone,
      u.country_code,
      u.country_name,
      u.currency_code,
      u.currency_symbol,
      u.bank_name,
      u.bank_account,
      u.bank_locked_at,
      u.created_at
    FROM users u
    $whereSql
    ORDER BY u.id DESC
    LIMIT $limit OFFSET $offset
  ";
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

  // Enrich: wallet balance per user (uses transactions ledger)
  foreach ($items as &$u) {
    $uid = (int)($u['id'] ?? 0);
    $cur = wallet_key_for_user($u);
    try {
      if ($uid > 0) {
        ensure_wallet($pdo, $uid, $cur);
        $u['wallet_currency'] = $cur;
        $u['wallet_balance'] = wallet_balance($pdo, $uid, $cur);
      } else {
        $u['wallet_currency'] = $cur;
        $u['wallet_balance'] = '0.00';
      }
    } catch (Throwable $e) {
      $u['wallet_currency'] = $cur;
      $u['wallet_balance'] = '0.00';
    }
  }
  unset($u);

  echo json_encode([
    'ok' => true,
    'items' => $items,
    'total' => $total,
    'page' => $page,
    'limit' => $limit,
    'role' => $role,
    'status' => $status,
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Server error']);
}
