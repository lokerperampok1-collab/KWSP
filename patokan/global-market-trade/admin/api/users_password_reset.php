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

$userId = (int)($_POST['user_id'] ?? 0);
$password = (string)($_POST['password'] ?? '');
$mode = (string)($_POST['mode'] ?? 'manual'); // manual|generate

if ($userId <= 0) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'Invalid user_id']);
  exit;
}

function gen_password(int $len = 12): string {
  $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%';
  $max = strlen($alphabet) - 1;
  $out = '';
  for ($i=0; $i<$len; $i++) {
    $out .= $alphabet[random_int(0, $max)];
  }
  return $out;
}

try {
  $stmt = $pdo->prepare('SELECT id, email, full_name FROM users WHERE id=? LIMIT 1');
  $stmt->execute([$userId]);
  $u = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$u) {
    http_response_code(404);
    echo json_encode(['ok'=>false,'error'=>'User not found']);
    exit;
  }

  $generated = false;
  if ($mode === 'generate' || trim($password) === '') {
    $password = gen_password(12);
    $generated = true;
  }

  if (strlen($password) < 6) {
    http_response_code(422);
    echo json_encode(['ok'=>false,'error'=>'Password must be at least 6 characters']);
    exit;
  }

  $hash = password_hash($password, PASSWORD_DEFAULT);
  $pdo->prepare('UPDATE users SET password_hash=? WHERE id=? LIMIT 1')->execute([$hash, $userId]);

  // Optional: notify user (does not include password)
  try {
    if (function_exists('notify_user')) {
      notify_user($pdo, $userId, 'Password reset', 'Admin has reset your password. Please login again.', '/login.php');
    }
  } catch (Throwable $e) {}

  echo json_encode([
    'ok'=>true,
    'user_id'=>$userId,
    'generated'=>$generated,
    'new_password'=>$password,
  ]);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Server error']);
}
