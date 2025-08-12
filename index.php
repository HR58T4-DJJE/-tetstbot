<?php
// Используем демонстрационную конфигурацию для показа сайта
require_once 'demo_config.php';

// Получение данных из базы (пример)
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мой сайт</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <h1 class="logo">Мой сайт</h1>
            <nav class="nav">
                <a href="#home" class="nav-link">Главная</a>
                <a href="#about" class="nav-link">О нас</a>
                <a href="database_info.php" class="nav-link">База данных</a>
                <a href="#contact" class="nav-link">Контакты</a>
            </nav>
        </div>
    </header>

    <main>
        <section id="home" class="hero">
            <div class="container">
                <h2>Добро пожаловать на мой сайт</h2>
                <p>Сайт создан на PHP, JavaScript и CSS с подключением к MySQL</p>
                <button class="btn btn-primary" onclick="showMessage()">Нажми меня</button>
            </div>
        </section>

        <section id="about" class="about">
            <div class="container">
                <h2>О нас</h2>
                <p>Это простой веб-сайт, демонстрирующий работу с PHP, JavaScript и CSS.</p>
                <div class="features">
                    <div class="feature">
                        <h3>PHP</h3>
                        <p>Серверная часть с подключением к базе данных</p>
                    </div>
                    <div class="feature">
                        <h3>JavaScript</h3>
                        <p>Интерактивность на стороне клиента</p>
                    </div>
                    <div class="feature">
                        <h3>CSS</h3>
                        <p>Современный и красивый дизайн</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="database" class="database">
            <div class="container">
                <h2>Информация о базе данных</h2>
                <div class="db-info">
                    <p><strong>База данных:</strong> <?php echo DB_NAME; ?></p>
                    <p><strong>Хост:</strong> <?php echo DB_HOST; ?></p>
                    <p><strong>Пользователь:</strong> <?php echo DB_USER; ?></p>
                    <p><strong>Статус подключения:</strong> <span class="status-success">Подключено</span></p>
                </div>
                
                <?php if (!empty($tables)): ?>
                <div class="tables">
                    <h3>Таблицы в базе данных:</h3>
                    <ul>
                        <?php foreach ($tables as $table): ?>
                            <li><?php echo htmlspecialchars($table); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <section id="contact" class="contact">
            <div class="container">
                <h2>Свяжитесь с нами</h2>
                <form class="contact-form" onsubmit="submitForm(event)">
                    <div class="form-group">
                        <label for="name">Имя:</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Сообщение:</label>
                        <textarea id="message" name="message" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Отправить</button>
                </form>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Мой сайт. Все права защищены.</p>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>