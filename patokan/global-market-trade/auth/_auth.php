<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
function require_auth(): void {
  if (empty($_SESSION["user_id"])) {
    header("Location: /login.php");
    exit;
  }
}