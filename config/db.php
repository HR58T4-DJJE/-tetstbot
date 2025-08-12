<?php
declare(strict_types=1);

// Database configuration
const DB_HOST = '127.0.0.1';
const DB_PORT = 3306;
const DB_NAME = 'alikf142_image';
const DB_USER = 'alikf142_image';
const DB_PASS = 'chVgj4U5';
const DB_CHARSET = 'utf8mb4';

/**
 * Create a new PDO instance configured for MySQL.
 */
function createPdo(): PDO {
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    return new PDO($dsn, DB_USER, DB_PASS, $options);
}

/**
 * Get a shared PDO connection. Reuses the same connection within the request lifecycle.
 */
function getDatabaseConnection(): PDO {
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $pdo = createPdo();
    return $pdo;
}