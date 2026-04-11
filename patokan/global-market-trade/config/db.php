<?php
declare(strict_types=1);

/**
 * Database connection (TEMPLATE)
 *
 * This file intentionally contains NO production credentials.
 *
 * Option A (recommended): set environment variables (DB_HOST, DB_NAME, DB_USER, DB_PASS)
 * Option B: edit the placeholders below.
 */

$DB_HOST = getenv('DB_HOST') ?: '127.0.0.1';
$DB_NAME = getenv('DB_NAME') ?: 'CHANGE_ME_DBNAME';
$DB_USER = getenv('DB_USER') ?: 'CHANGE_ME_DBUSER';
$DB_PASS = getenv('DB_PASS') ?: 'CHANGE_ME_DBPASS';

$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES => false,
];

try {
  $pdo = new PDO(
    "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
    $DB_USER,
    $DB_PASS,
    $options
  );
} catch (Throwable $e) {
  http_response_code(500);
  echo 'DB connection failed.';
  exit;
}
