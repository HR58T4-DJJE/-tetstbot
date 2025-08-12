<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

$databaseConnected = false;
$databaseError = null;
$serverTime = date('c');
$databaseNow = null;

try {
    $pdo = getDatabaseConnection();
    $databaseConnected = true;
    $databaseNow = $pdo->query('SELECT NOW() AS now_value')->fetch()['now_value'] ?? null;
} catch (Throwable $e) {
    $databaseError = $e->getMessage();
}
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PHP JS CSS Site</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
  <header class="site-header">
    <div class="container">
      <h1>Минимальный сайт на PHP / JS / CSS</h1>
      <nav class="nav">
        <a href="/">Главная</a>
        <a href="/health.php">Проверка БД</a>
      </nav>
    </div>
  </header>

  <main class="container">
    <section class="card">
      <h2>Состояние сервера</h2>
      <ul>
        <li><strong>Время сервера (PHP):</strong> <?php echo htmlspecialchars($serverTime, ENT_QUOTES, 'UTF-8'); ?></li>
        <li>
          <strong>Подключение к БД:</strong>
          <?php if ($databaseConnected): ?>
            <span class="status ok">OK</span>
          <?php else: ?>
            <span class="status fail">Ошибка</span>
          <?php endif; ?>
        </li>
        <li>
          <strong>Время из БД (SELECT NOW()):</strong>
          <?php echo $databaseNow ? htmlspecialchars($databaseNow, ENT_QUOTES, 'UTF-8') : '—'; ?>
        </li>
      </ul>
      <?php if ($databaseError): ?>
        <pre class="error"><?php echo htmlspecialchars($databaseError, ENT_QUOTES, 'UTF-8'); ?></pre>
      <?php endif; ?>
    </section>

    <section class="card">
      <h2>Фронтенд JS</h2>
      <p>Откройте консоль браузера — скрипт инициализирован.</p>
      <button id="pingButton" class="button">Проверить /health</button>
      <pre id="pingResult" class="code"></pre>
    </section>
  </main>

  <footer class="site-footer">
    <div class="container">
      <small>© <?php echo date('Y'); ?> Простая заготовка сайта на чистом PHP, JS и CSS.</small>
    </div>
  </footer>

  <script src="/assets/js/app.js"></script>
</body>
</html>