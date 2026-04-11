<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

$q = trim((string)($_GET['q'] ?? ''));

try {
  if ($q !== '') {
    $stmt = $pdo->prepare("SELECT country_code, country_name, currency_code, currency_symbol FROM countries WHERE country_name LIKE ? ORDER BY country_name LIMIT 80");
    $stmt->execute(['%' . $q . '%']);
  } else {
    $stmt = $pdo->query("SELECT country_code, country_name, currency_code, currency_symbol FROM countries ORDER BY country_name");
  }

  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode(['ok' => true, 'data' => $rows], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Failed to load countries']);
}
