# PhotoShare - Современный фото хостинг

Современная веб-платформа для загрузки, хранения и обмена фотографиями с красивым адаптивным дизайном.

## 🚀 Особенности

- **Современный дизайн** - Красивый и интуитивный интерфейс
- **Полная адаптивность** - Отлично работает на всех устройствах
- **Drag & Drop загрузка** - Простая загрузка файлов
- **Автоматические миниатюры** - Создание оптимизированных изображений
- **Категоризация** - Организация по темам
- **Поиск и фильтрация** - Быстрый поиск нужных изображений
- **Пользовательские аккаунты** - Регистрация и авторизация
- **Безопасность** - Защита файлов и данных
- **API** - RESTful API для интеграции

## 🛠 Технологии

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 7.4+
- **База данных**: MySQL 5.7+
- **Сервер**: Apache/Nginx
- **Изображения**: GD Library для обработки

## 📋 Требования

- PHP 7.4 или выше
- MySQL 5.7 или выше
- Apache с mod_rewrite или Nginx
- GD Library для PHP
- Минимум 100MB свободного места

## 🚀 Установка

### 1. Клонирование репозитория

```bash
git clone https://github.com/yourusername/photoshare.git
cd photoshare
```

### 2. Настройка веб-сервера

#### Apache
Убедитесь, что включен модуль `mod_rewrite`:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Nginx
Добавьте в конфигурацию:
```nginx
location / {
    try_files $uri $uri/ /index.html;
}
```

### 3. Настройка базы данных

1. Создайте базу данных MySQL:
```sql
CREATE DATABASE photoshare CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Создайте пользователя (опционально):
```sql
CREATE USER 'photoshare'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON photoshare.* TO 'photoshare'@'localhost';
FLUSH PRIVILEGES;
```

### 4. Конфигурация

1. Откройте файл `config/database.php`
2. Измените параметры подключения:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'photoshare');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### 5. Права доступа

Установите правильные права для папок:
```bash
chmod 755 uploads/
chmod 755 uploads/thumbnails/
chmod 644 config/database.php
```

### 6. Первый запуск

Откройте сайт в браузере. База данных и таблицы будут созданы автоматически при первом посещении.

## 📁 Структура проекта

```
photoshare/
├── api/                    # API endpoints
│   ├── auth.php          # Аутентификация
│   ├── images.php        # Управление изображениями
│   └── upload.php        # Загрузка файлов
├── config/                # Конфигурация
│   └── database.php      # Настройки БД
├── css/                   # Стили
│   └── style.css         # Основные стили
├── js/                    # JavaScript
│   └── main.js           # Основная логика
├── uploads/               # Загруженные файлы
│   └── thumbnails/       # Миниатюры
├── .htaccess             # Apache конфигурация
├── index.html            # Главная страница
└── README.md             # Документация
```

## 🔧 API Endpoints

### Аутентификация
- `POST /api/auth?action=register` - Регистрация
- `POST /api/auth?action=login` - Вход
- `POST /api/auth?action=profile` - Профиль пользователя

### Изображения
- `GET /api/images?action=list` - Список изображений
- `GET /api/images?action=single&id={id}` - Одно изображение
- `GET /api/images?action=categories` - Категории
- `GET /api/images?action=trending` - Популярные

### Загрузка
- `POST /api/upload` - Загрузка изображения

## 🎨 Кастомизация

### Изменение цветовой схемы
Отредактируйте CSS переменные в `css/style.css`:
```css
:root {
    --primary-color: #6366f1;
    --secondary-color: #f59e0b;
    --accent-color: #10b981;
}
```

### Добавление новых категорий
Отредактируйте массив в `config/database.php`:
```php
$defaultCategories = [
    ['name' => 'Новая категория', 'slug' => 'new-category', 'description' => 'Описание']
];
```

## 🔒 Безопасность

- Все пользовательские данные валидируются
- Пароли хешируются с использованием `password_hash()`
- Защита от SQL-инъекций через подготовленные запросы
- Ограничение типов и размеров файлов
- Защита конфигурационных файлов

## 📱 Адаптивность

Сайт полностью адаптивен и поддерживает:
- Мобильные устройства (320px+)
- Планшеты (768px+)
- Десктопы (1024px+)
- Большие экраны (1200px+)

## 🚀 Производительность

- Сжатие файлов (gzip)
- Кэширование статических ресурсов
- Оптимизированные изображения
- Ленивая загрузка (lazy loading)
- Миниатюры для быстрой загрузки

## 🐛 Устранение неполадок

### Ошибка подключения к БД
- Проверьте параметры в `config/database.php`
- Убедитесь, что MySQL запущен
- Проверьте права доступа пользователя

### Ошибка загрузки файлов
- Проверьте права на папку `uploads/`
- Убедитесь, что GD Library установлен
- Проверьте максимальный размер файла в PHP

### Проблемы с .htaccess
- Убедитесь, что `mod_rewrite` включен
- Проверьте права на файл `.htaccess`

## 📈 Мониторинг

### Логи
- Apache: `/var/log/apache2/error.log`
- PHP: `/var/log/php/error.log`
- MySQL: `/var/log/mysql/error.log`

### Статистика
- Количество загруженных изображений
- Популярные категории
- Активные пользователи

## 🤝 Вклад в проект

1. Форкните репозиторий
2. Создайте ветку для новой функции
3. Внесите изменения
4. Создайте Pull Request

## 📄 Лицензия

Этот проект распространяется под лицензией MIT. См. файл `LICENSE` для подробностей.

## 📞 Поддержка

Если у вас есть вопросы или проблемы:
- Создайте Issue в GitHub
- Напишите на email: support@photoshare.com
- Документация: https://docs.photoshare.com

## 🔄 Обновления

Для обновления до последней версии:
```bash
git pull origin main
```

## 📊 Статистика

- **Версия**: 1.0.0
- **Последнее обновление**: Январь 2024
- **PHP**: 7.4+
- **MySQL**: 5.7+
- **Размер**: ~2MB

---

**PhotoShare** - Делитесь своими моментами с миром! 📸✨