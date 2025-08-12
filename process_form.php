<?php
// Используем демонстрационную конфигурацию для показа сайта
require_once 'demo_config.php';

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получение данных из формы
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Валидация данных
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Имя обязательно для заполнения';
    }
    
    if (empty($email)) {
        $errors[] = 'Email обязателен для заполнения';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Некорректный формат email';
    }
    
    if (empty($message)) {
        $errors[] = 'Сообщение обязательно для заполнения';
    }
    
    // Если нет ошибок, сохраняем в базу данных
    if (empty($errors)) {
        try {
            // Создание таблицы для сообщений, если её нет
            $createTable = "CREATE TABLE IF NOT EXISTS contact_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL,
                message TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            
            $pdo->exec($createTable);
            
            // Вставка данных
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $message]);
            
            // Успешный ответ
            $response = [
                'success' => true,
                'message' => 'Сообщение успешно отправлено!'
            ];
            
        } catch (PDOException $e) {
            $response = [
                'success' => false,
                'message' => 'Ошибка при сохранении сообщения: ' . $e->getMessage()
            ];
        }
    } else {
        $response = [
            'success' => false,
            'message' => 'Ошибки валидации: ' . implode(', ', $errors)
        ];
    }
    
    // Отправка JSON ответа
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Если запрос не POST, перенаправляем на главную
header('Location: index.php');
exit;
?>