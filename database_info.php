<?php
// Используем демонстрационную конфигурацию для показа сайта
require_once 'demo_config.php';

// Получение информации о базе данных
$dbInfo = [];
$tables = [];

try {
    // Информация о базе данных
    $stmt = $pdo->query("SELECT DATABASE() as current_db");
    $dbInfo['current_db'] = $stmt->fetch(PDO::FETCH_ASSOC)['current_db'];
    
    // Версия MySQL
    $stmt = $pdo->query("SELECT VERSION() as version");
    $dbInfo['version'] = $stmt->fetch(PDO::FETCH_ASSOC)['version'];
    
    // Список таблиц
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Информация о каждой таблице
    $tableInfo = [];
    foreach ($tables as $table) {
        $stmt = $pdo->query("DESCRIBE `$table`");
        $tableInfo[$table] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Количество записей
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
        $tableInfo[$table]['count'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }
    
} catch (PDOException $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Информация о базе данных</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .db-details {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .table-structure {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .table-structure table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        .table-structure th,
        .table-structure td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .table-structure th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 2rem;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <h1 class="logo">Информация о БД</h1>
            <nav class="nav">
                <a href="index.php" class="nav-link">Главная</a>
                <a href="#info" class="nav-link">Информация</a>
                <a href="#tables" class="nav-link">Таблицы</a>
            </nav>
        </div>
    </header>

    <main style="margin-top: 80px;">
        <div class="container">
            <a href="index.php" class="back-link">← Вернуться на главную</a>
            
            <section id="info">
                <h2>Информация о базе данных</h2>
                
                <?php if (isset($error)): ?>
                    <div class="db-details">
                        <p style="color: #dc3545;"><strong>Ошибка:</strong> <?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php else: ?>
                    <div class="db-details">
                        <h3>Основная информация</h3>
                        <p><strong>Текущая база данных:</strong> <?php echo htmlspecialchars($dbInfo['current_db']); ?></p>
                        <p><strong>Версия MySQL:</strong> <?php echo htmlspecialchars($dbInfo['version']); ?></p>
                        <p><strong>Количество таблиц:</strong> <?php echo count($tables); ?></p>
                        <p><strong>Статус подключения:</strong> <span class="status-success">Подключено</span></p>
                    </div>
                <?php endif; ?>
            </section>

            <?php if (!empty($tables)): ?>
            <section id="tables">
                <h2>Структура таблиц</h2>
                
                <?php foreach ($tables as $table): ?>
                    <div class="table-structure">
                        <h3>Таблица: <?php echo htmlspecialchars($table); ?></h3>
                        <p><strong>Количество записей:</strong> <?php echo $tableInfo[$table]['count']; ?></p>
                        
                        <table>
                            <thead>
                                <tr>
                                    <th>Поле</th>
                                    <th>Тип</th>
                                    <th>NULL</th>
                                    <th>Ключ</th>
                                    <th>По умолчанию</th>
                                    <th>Дополнительно</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tableInfo[$table] as $field): ?>
                                    <?php if (is_array($field)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($field['Field']); ?></td>
                                            <td><?php echo htmlspecialchars($field['Type']); ?></td>
                                            <td><?php echo htmlspecialchars($field['Null']); ?></td>
                                            <td><?php echo htmlspecialchars($field['Key']); ?></td>
                                            <td><?php echo htmlspecialchars($field['Default'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($field['Extra']); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            </section>
            <?php endif; ?>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Мой сайт. Все права защищены.</p>
        </div>
    </footer>
</body>
</html>