<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';

$response = [
    'ok' => false,
    'mysql_version' => null,
    'time' => date('c'),
    'error' => null,
];

try {
    $pdo = getDatabaseConnection();
    $versionRow = $pdo->query('SELECT VERSION() AS v')->fetch();
    $response['mysql_version'] = $versionRow['v'] ?? null;
    $response['ok'] = true;
} catch (Throwable $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);