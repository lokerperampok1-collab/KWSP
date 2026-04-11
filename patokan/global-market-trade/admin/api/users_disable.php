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
$isDisabled = (int)($_POST['is_disabled'] ?? 0);

if ($userId <= 0 || ($isDisabled !== 0 && $isDisabled !== 1)) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'Invalid input']);
  exit;
}

// Prevent locking yourself out
if ($userId === $meId && $isDisabled === 1) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'You cannot disable your own account']);
  exit;
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

  // Prevent disabling the last admin
  if ((int)($u['is_admin'] ?? 0) === 1 && $isDisabled === 1) {
    $cnt = (int)$pdo->query('SELECT COUNT(*) FROM users WHERE is_admin=1 AND COALESCE(is_disabled,0)=0')->fetchColumn();
    if ($cnt <= 1) {
      http_response_code(422);
      echo json_encode(['ok'=>false,'error'=>'Cannot disable the last active admin']);
      exit;
    }
  }

  if ($isDisabled === 1) {
    $pdo->prepare('UPDATE users SET is_disabled=1, disabled_at=NOW() WHERE id=? LIMIT 1')->execute([$userId]);
  } else {
    $pdo->prepare('UPDATE users SET is_disabled=0, disabled_at=NULL WHERE id=? LIMIT 1')->execute([$userId]);
  }

  // Notify user (optional)
  try {
    if (function_exists('notify_user')) {
      $title = $isDisabled ? 'Account disabled' : 'Account enabled';
      $body  = $isDisabled ? 'Your account has been disabled by admin.' : 'Your account has been enabled by admin.';
      notify_user($pdo, $userId, $title, $body, '/login.php');
    }
  } catch (Throwable $e) {}

  echo json_encode(['ok'=>true,'user_id'=>$userId,'is_disabled'=>$isDisabled]);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Server error']);
}
