<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . "/../../config/app.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../config/schema.php";
require_once __DIR__ . "/../../auth/_csrf.php";

require_admin();
csrf_check();
header("Content-Type: application/json; charset=UTF-8");

try { ensure_user_status_schema($pdo); } catch (Throwable $e) { /* ignore */ }

$meId = (int)($_SESSION['user_id'] ?? 0);
$userId = (int)($_POST['user_id'] ?? 0);

if ($userId <= 0) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'Invalid user_id']);
  exit;
}

if ($userId === $meId) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'You cannot delete your own account']);
  exit;
}

function table_exists(PDO $pdo, string $name): bool {
  $st = $pdo->prepare("SHOW TABLES LIKE ?");
  $st->execute([$name]);
  return (bool)$st->fetchColumn();
}

try {
  $stmt = $pdo->prepare('SELECT id, is_admin, COALESCE(is_disabled,0) AS is_disabled FROM users WHERE id=? LIMIT 1');
  $stmt->execute([$userId]);
  $u = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$u) {
    http_response_code(404);
    echo json_encode(['ok'=>false,'error'=>'User not found']);
    exit;
  }

  // Prevent deleting the last admin (even if disabled)
  if ((int)($u['is_admin'] ?? 0) === 1) {
    $cnt = (int)$pdo->query('SELECT COUNT(*) FROM users WHERE is_admin=1')->fetchColumn();
    if ($cnt <= 1) {
      http_response_code(422);
      echo json_encode(['ok'=>false,'error'=>'Cannot delete the last admin']);
      exit;
    }
  }

  $pdo->beginTransaction();

  // Best-effort delete related records (if the tables exist)
  $tables = [
    'kyc_requests' => 'DELETE FROM kyc_requests WHERE user_id=?',
    'notifications' => 'DELETE FROM notifications WHERE user_id=?',
    'wallet_transactions' => 'DELETE FROM wallet_transactions WHERE user_id=?',
    'wallets' => 'DELETE FROM wallets WHERE user_id=?',
    'user_investments' => 'DELETE FROM user_investments WHERE user_id=?',
  ];
  foreach ($tables as $t => $sql) {
    try {
      if (table_exists($pdo, $t)) {
        $st = $pdo->prepare($sql);
        $st->execute([$userId]);
      }
    } catch (Throwable $e) { /* ignore */ }
  }

  // Finally delete user
  $pdo->prepare('DELETE FROM users WHERE id=? LIMIT 1')->execute([$userId]);

  $pdo->commit();

  echo json_encode(['ok'=>true,'user_id'=>$userId]);

} catch (Throwable $e) {
  if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) $pdo->rollBack();
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Server error']);
}
