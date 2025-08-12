<?php
// Конфигурация базы данных
define('DB_HOST', 'localhost');
define('DB_NAME', 'alikf142_image');
define('DB_USER', 'alikf142_image');
define('DB_PASS', 'chVgj4U5');

// Создание подключения к базе данных
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>