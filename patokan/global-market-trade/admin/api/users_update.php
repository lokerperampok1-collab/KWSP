<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . "/../../config/app.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../auth/_csrf.php";

require_admin();
csrf_check();

header("Content-Type: application/json; charset=UTF-8");

$meId = (int)($_SESSION["user_id"] ?? 0);

$userId = (int)($_POST["user_id"] ?? 0);
$fullName = trim((string)($_POST["full_name"] ?? ""));
$email = trim((string)($_POST["email"] ?? ""));
$isAdmin = (int)($_POST["is_admin"] ?? 0);

if ($userId <= 0) {
  echo json_encode(["ok" => false, "error" => "Invalid user_id"]);
  exit;
}
if ($fullName === "") {
  echo json_encode(["ok" => false, "error" => "Full name is required"]);
  exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo json_encode(["ok" => false, "error" => "Invalid email"]);
  exit;
}
if ($isAdmin !== 0 && $isAdmin !== 1) {
  echo json_encode(["ok" => false, "error" => "Invalid admin flag"]);
  exit;
}

// Prevent locking yourself out
if ($userId === $meId && $isAdmin === 0) {
  echo json_encode(["ok" => false, "error" => "You cannot remove your own admin access"]);
  exit;
}

try {
  // Ensure user exists
  $stmt = $pdo->prepare("SELECT id, email FROM users WHERE id=? LIMIT 1");
  $stmt->execute([$userId]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$row) {
    echo json_encode(["ok" => false, "error" => "User not found"]);
    exit;
  }

  // Unique email check
  $stmt = $pdo->prepare("SELECT id FROM users WHERE email=? AND id<>? LIMIT 1");
  $stmt->execute([$email, $userId]);
  $dup = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($dup) {
    echo json_encode(["ok" => false, "error" => "Email already in use"]);
    exit;
  }

  $stmt = $pdo->prepare("UPDATE users SET full_name=?, email=?, is_admin=? WHERE id=? LIMIT 1");
  $stmt->execute([$fullName, $email, $isAdmin, $userId]);

  echo json_encode([
    "ok" => true,
    "user" => [
      "id" => $userId,
      "full_name" => $fullName,
      "email" => $email,
      "is_admin" => $isAdmin,
    ]
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["ok" => false, "error" => "Server error"]);
}
