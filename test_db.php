<?php
// Простой тест подключения к базе данных
echo "<h1>Тест подключения к базе данных</h1>";

try {
    // Используем демонстрационную конфигурацию для показа сайта
    require_once 'demo_config.php';
    echo "<p style='color: green;'>✅ Подключение к базе данных успешно!</p>";
    
    // Тест запроса
    $stmt = $pdo->query("SELECT VERSION() as version");
    $version = $stmt->fetch(PDO::FETCH_ASSOC)['version'];
    echo "<p><strong>Версия MySQL:</strong> $version</p>";
    
    // Тест базы данных
    $stmt = $pdo->query("SELECT DATABASE() as db");
    $db = $stmt->fetch(PDO::FETCH_ASSOC)['db'];
    echo "<p><strong>Текущая база данных:</strong> $db</p>";
    
    // Список таблиц
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p><strong>Количество таблиц:</strong> " . count($tables) . "</p>";
    
    if (!empty($tables)) {
        echo "<p><strong>Таблицы:</strong></p><ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Ошибка подключения: " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Общая ошибка: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>← Вернуться на главную</a></p>";
echo "<p><a href='database_info.php'>Подробная информация о БД</a></p>";
?>