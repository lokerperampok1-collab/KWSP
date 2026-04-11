<?php
declare(strict_types=1);

/**
 * Simple CSRF helper:
 * - csrf_token(): returns token string and stores in session
 * - csrf_input(): echoes hidden input field
 * - csrf_check(): verifies POST token (auto-run in handler)
 */

function csrf_token(): string {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  if (empty($_SESSION["_csrf"])) {
    $_SESSION["_csrf"] = bin2hex(random_bytes(32));
  }
  return (string)$_SESSION["_csrf"];
}

function csrf_input(): string {
  $t = htmlspecialchars(csrf_token(), ENT_QUOTES, "UTF-8");
  return '<input type="hidden" name="csrf" value="'.$t.'">';
}

function csrf_check(): void {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  $sent = (string)($_POST["csrf"] ?? "");
  $sess = (string)($_SESSION["_csrf"] ?? "");
  if ($sent === "" || $sess === "" || !hash_equals($sess, $sent)) {
    http_response_code(403);
    exit("CSRF check failed.");
  }
}
