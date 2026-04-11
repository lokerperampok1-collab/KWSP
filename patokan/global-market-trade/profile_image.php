<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . "/config/app.php";

require_login();

$BASE = "";
header("Location: {$BASE}/profile.php?tab=photo");
exit;
