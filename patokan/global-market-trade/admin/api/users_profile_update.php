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

try { ensure_user_profile_schema($pdo); } catch (Throwable $e) { /* ignore */ }
try { ensure_user_status_schema($pdo); } catch (Throwable $e) { /* ignore */ }

$meId = (int)($_SESSION['user_id'] ?? 0);

$userId = (int)($_POST['user_id'] ?? 0);

$fullName = trim((string)($_POST['full_name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));
$countryCode = strtoupper(trim((string)($_POST['country_code'] ?? '')));
$countryName = trim((string)($_POST['country_name'] ?? ''));
$currencyCode = strtoupper(trim((string)($_POST['currency_code'] ?? '')));
$currencySymbol = trim((string)($_POST['currency_symbol'] ?? ''));
$bankName = trim((string)($_POST['bank_name'] ?? ''));
$bankAccount = trim((string)($_POST['bank_account'] ?? ''));
$bankLocked = isset($_POST['bank_locked']) ? (int)$_POST['bank_locked'] : null; // 0|1|null

$isAdmin = isset($_POST['is_admin']) ? (int)$_POST['is_admin'] : null; // 0|1|null

if ($userId <= 0) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'Invalid user_id']);
  exit;
}

if ($fullName === '') {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'Full name is required']);
  exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'Invalid email']);
  exit;
}

if ($phone !== '' && strlen($phone) > 40) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'Phone too long']);
  exit;
}

if ($countryCode !== '' && !preg_match('/^[A-Z]{2}$/', $countryCode)) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'country_code must be 2 letters']);
  exit;
}

if ($currencyCode !== '' && !preg_match('/^[A-Z]{2,10}$/', $currencyCode)) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'Invalid currency_code']);
  exit;
}

if ($currencySymbol !== '' && strlen($currencySymbol) > 12) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'currency_symbol too long']);
  exit;
}

if ($bankName !== '' && strlen($bankName) > 80) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'bank_name too long']);
  exit;
}
if ($bankAccount !== '' && strlen($bankAccount) > 80) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'bank_account too long']);
  exit;
}
if ($bankAccount !== '' && !preg_match('/^[0-9A-Za-z\-\s\.]{4,80}$/', $bankAccount)) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'Invalid bank_account format']);
  exit;
}

if ($isAdmin !== null && $isAdmin !== 0 && $isAdmin !== 1) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'Invalid is_admin']);
  exit;
}

// Prevent removing own admin access (if you pass is_admin)
if ($isAdmin !== null && $userId === $meId && $isAdmin === 0) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'You cannot remove your own admin access']);
  exit;
}

try {
  // Ensure user exists
  $stmt = $pdo->prepare('SELECT id, is_admin, bank_locked_at FROM users WHERE id=? LIMIT 1');
  $stmt->execute([$userId]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$row) {
    http_response_code(404);
    echo json_encode(['ok'=>false,'error'=>'User not found']);
    exit;
  }

  // Unique email check
  $stmt = $pdo->prepare('SELECT id FROM users WHERE email=? AND id<>? LIMIT 1');
  $stmt->execute([$email, $userId]);
  if ($stmt->fetch(PDO::FETCH_ASSOC)) {
    http_response_code(422);
    echo json_encode(['ok'=>false,'error'=>'Email already in use']);
    exit;
  }

  // Build update statement (set all fields; safe as columns exist from ensure_user_profile_schema)
  $fields = [
    'full_name' => $fullName,
    'email' => $email,
    'phone' => ($phone !== '' ? $phone : null),
    'country_code' => ($countryCode !== '' ? $countryCode : null),
    'country_name' => ($countryName !== '' ? $countryName : null),
    'currency_code' => ($currencyCode !== '' ? $currencyCode : null),
    'currency_symbol' => ($currencySymbol !== '' ? $currencySymbol : null),
    'bank_name' => ($bankName !== '' ? $bankName : null),
    'bank_account' => ($bankAccount !== '' ? $bankAccount : null),
  ];

  if ($isAdmin !== null) {
    $fields['is_admin'] = $isAdmin;
  }

  // Bank lock handling
  if ($bankLocked === 0) {
    $fields['bank_locked_at'] = null;
  } elseif ($bankLocked === 1) {
    // if bank info filled and no lock yet, lock now; otherwise keep existing
    if ($bankName !== '' && $bankAccount !== '' && empty($row['bank_locked_at'])) {
      $fields['bank_locked_at'] = date('Y-m-d H:i:s');
    }
  }

  $set = [];
  $vals = [];
  foreach ($fields as $k => $v) { $set[] = "`{$k}`=?"; $vals[] = $v; }
  $vals[] = $userId;

  $sql = 'UPDATE users SET ' . implode(', ', $set) . ' WHERE id=? LIMIT 1';
  $pdo->prepare($sql)->execute($vals);

  echo json_encode(['ok'=>true,'user_id'=>$userId]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Server error']);
}
