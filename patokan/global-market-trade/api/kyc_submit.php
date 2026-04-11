<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/schema.php";
require_once __DIR__ . "/../auth/_csrf.php";

require_login();
csrf_check();
ensure_kyc_schema($pdo);

header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', '0');

$userId = (int)($_SESSION["user_id"] ?? 0);

function save_upload(string $field, int $userId, string $subdir): ?string {
  if (!isset($_FILES[$field]) || !is_array($_FILES[$field])) return null;
  $f = $_FILES[$field];
  if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return null;

  $tmp = (string)$f['tmp_name'];
  $name = (string)$f['name'];
  $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
  $allow = ['jpg','jpeg','png','webp','pdf'];
  if (!in_array($ext, $allow, true)) {
    throw new RuntimeException("File type not allowed for {$field}");
  }
  if (($f['size'] ?? 0) > 5 * 1024 * 1024) {
    throw new RuntimeException("File too large for {$field} (max 5MB)");
  }

  $baseDir = __DIR__ . "/../uploads/kyc/{$userId}";
  if (!is_dir($baseDir)) {
    @mkdir($baseDir, 0775, true);
  }
  $safe = preg_replace('/[^a-zA-Z0-9_-]+/', '_', pathinfo($name, PATHINFO_FILENAME));
  $fname = $subdir . '_' . $safe . '_' . date('Ymd_His') . '.' . $ext;
  $dest = $baseDir . '/' . $fname;

  if (!move_uploaded_file($tmp, $dest)) {
    throw new RuntimeException("Failed to save {$field}");
  }
  // store as web path
  return "uploads/kyc/{$userId}/{$fname}";
}

try {
  // require at least front + selfie
  $front = save_upload('id_front', $userId, 'id_front');
  $back  = save_upload('id_back',  $userId, 'id_back');
  $selfie= save_upload('selfie',   $userId, 'selfie');

  if (!$front || !$selfie) {
    http_response_code(422);
    echo json_encode(["ok"=>false, "error"=>"Please upload ID Front and Selfie."]);
    exit;
  }

  // upsert request (reset to pending)
  $stmt = $pdo->prepare("
    INSERT INTO kyc_requests (user_id, id_front_path, id_back_path, selfie_path, status)
    VALUES (?, ?, ?, ?, 'pending')
    ON DUPLICATE KEY UPDATE
      id_front_path = VALUES(id_front_path),
      id_back_path  = VALUES(id_back_path),
      selfie_path   = VALUES(selfie_path),
      status        = 'pending',
      note          = NULL
  ");
  $stmt->execute([$userId, $front, $back, $selfie]);

  try { notify_user($pdo, $userId, "KYC submitted", "Your verification request is pending review.", "/kyc.php"); } catch (Throwable $e) {}

  echo json_encode(["ok"=>true, "message"=>"KYC submitted (pending approval)."]);
} catch (Throwable $e) {
  http_response_code(422);
  echo json_encode(["ok"=>false, "error"=>$e->getMessage()]);
}
