<?php
require_once 'config/database.php';

$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

$results = [];
$totalResults = 0;
$categories = [];

try {
    $db = Database::getInstance();
    
    // Get categories for filter
    $catSql = "SELECT * FROM categories ORDER BY name ASC";
    $catStmt = $db->query($catSql);
    $categories = $catStmt->fetchAll();
    
    if ($searchQuery) {
        // Build search query
        $whereConditions = ['is_public = 1'];
        $params = [];
        
        if ($searchQuery) {
            $whereConditions[] = '(title LIKE ? OR description LIKE ? OR original_name LIKE ?)';
            $searchTerm = '%' . $searchQuery . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if ($category && $category !== 'all') {
            $whereConditions[] = 'category = ?';
            $params[] = $category;
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM images WHERE $whereClause";
        $countStmt = $db->query($countSql, $params);
        $totalResults = $countStmt->fetch()['total'];
        
        // Get search results
        $searchSql = "SELECT i.*, u.name as user_name, u.avatar as user_avatar,
                             (SELECT COUNT(*) FROM likes WHERE image_id = i.id) as likes_count,
                             (SELECT COUNT(*) FROM comments WHERE image_id = i.id) as comments_count
                      FROM images i 
                      LEFT JOIN users u ON i.user_id = u.id 
                      WHERE $whereClause 
                      ORDER BY i.created_at DESC 
                      LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $searchStmt = $db->query($searchSql, $params);
        $results = $searchStmt->fetchAll();
    }
    
} catch (Exception $e) {
    $error = 'Ошибка поиска: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Поиск - PhotoShare</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .search-page {
            padding: 120px 0 80px;
            min-height: 100vh;
            background: var(--bg-secondary);
        }
        
        .search-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .search-form {
            max-width: 600px;
            margin: 0 auto 3rem;
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
        }
        
        .search-input-group {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .search-input {
            flex: 1;
            padding: 1rem;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        
        .search-filters {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .filter-select {
            padding: 0.5rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            background: white;
            font-size: 0.875rem;
        }
        
        .search-results {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 0 1rem;
        }
        
        .results-count {
            color: var(--text-secondary);
        }
        
        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        
        .no-results {
            text-align: center;
            padding: 3rem;
            color: var(--text-secondary);
        }
        
        .no-results i {
            font-size: 4rem;
            color: var(--text-light);
            margin-bottom: 1rem;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }
        
        .page-btn {
            padding: 0.5rem 1rem;
            border: 1px solid var(--border-color);
            background: white;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
        }
        
        .page-btn:hover,
        .page-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .page-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        @media (max-width: 768px) {
            .search-input-group {
                flex-direction: column;
            }
            
            .search-filters {
                justify-content: stretch;
            }
            
            .filter-select {
                flex: 1;
            }
            
            .results-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="nav">
            <div class="nav-container">
                <div class="nav-logo">
                    <a href="/" style="text-decoration: none; color: inherit;">
                        <i class="fas fa-camera"></i>
                        <span>PhotoShare</span>
                    </a>
                </div>
                <div class="nav-menu">
                    <a href="/#home">Главная</a>
                    <a href="/#gallery">Галерея</a>
                    <a href="/#upload">Загрузить</a>
                    <a href="/#about">О нас</a>
                </div>
                <div class="nav-actions">
                    <a href="/" class="btn btn-primary">
                        <i class="fas fa-home"></i>
                        На главную
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <section class="search-page">
        <div class="container">
            <div class="search-header">
                <h1>Поиск изображений</h1>
                <p>Найдите нужные фотографии по названию, описанию или категории</p>
            </div>

            <form class="search-form" method="GET" action="search.php">
                <div class="search-input-group">
                    <input type="text" 
                           name="q" 
                           class="search-input" 
                           placeholder="Введите поисковый запрос..."
                           value="<?php echo htmlspecialchars($searchQuery); ?>"
                           required>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                        Найти
                    </button>
                </div>
                
                <div class="search-filters">
                    <select name="category" class="filter-select">
                        <option value="all">Все категории</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['slug']); ?>" 
                                    <?php echo $category === $cat['slug'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>

            <?php if ($searchQuery): ?>
                <div class="search-results">
                    <div class="results-header">
                        <div class="results-count">
                            Найдено <?php echo number_format($totalResults); ?> изображений
                            <?php if ($searchQuery): ?>
                                по запросу "<?php echo htmlspecialchars($searchQuery); ?>"
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (empty($results)): ?>
                        <div class="no-results">
                            <i class="fas fa-search"></i>
                            <h3>Ничего не найдено</h3>
                            <p>Попробуйте изменить поисковый запрос или категорию</p>
                        </div>
                    <?php else: ?>
                        <div class="results-grid">
                            <?php foreach ($results as $image): ?>
                                <div class="gallery-item">
                                    <img src="<?php echo htmlspecialchars($image['file_path']); ?>" 
                                         alt="<?php echo htmlspecialchars($image['title']); ?>"
                                         loading="lazy">
                                    <div class="gallery-item-info">
                                        <div class="gallery-item-title">
                                            <?php echo htmlspecialchars($image['title']); ?>
                                        </div>
                                        <div class="gallery-item-meta">
                                            <span><?php echo date('d.m.Y', strtotime($image['created_at'])); ?></span>
                                            <span><?php echo $this->formatFileSize($image['file_size']); ?></span>
                                        </div>
                                        <div class="gallery-item-meta">
                                            <span>Автор: <?php echo htmlspecialchars($image['user_name'] ?? 'Anonymous'); ?></span>
                                            <span>Категория: <?php echo htmlspecialchars($image['category']); ?></span>
                                        </div>
                                        <div class="gallery-item-meta">
                                            <span><i class="fas fa-eye"></i> <?php echo number_format($image['views']); ?></span>
                                            <span><i class="fas fa-download"></i> <?php echo number_format($image['downloads']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if ($totalResults > $limit): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?q=<?php echo urlencode($searchQuery); ?>&category=<?php echo urlencode($category); ?>&page=<?php echo $page - 1; ?>" 
                                       class="page-btn">
                                        <i class="fas fa-chevron-left"></i> Назад
                                    </a>
                                <?php endif; ?>
                                
                                <span class="page-btn active">
                                    Страница <?php echo $page; ?> из <?php echo ceil($totalResults / $limit); ?>
                                </span>
                                
                                <?php if ($page < ceil($totalResults / $limit)): ?>
                                    <a href="?q=<?php echo urlencode($searchQuery); ?>&category=<?php echo urlencode($category); ?>&page=<?php echo $page + 1; ?>" 
                                       class="page-btn">
                                        Вперед <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="search-results">
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <h3>Введите поисковый запрос</h3>
                        <p>Используйте форму выше для поиска изображений</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <script>
        // Auto-submit form when category changes
        document.querySelector('.filter-select').addEventListener('change', function() {
            if (document.querySelector('.search-input').value.trim()) {
                this.form.submit();
            }
        });
        
        // Add click events to gallery items
        document.querySelectorAll('.gallery-item').forEach(item => {
            item.addEventListener('click', function() {
                // TODO: Open image in full view
                console.log('Image clicked');
            });
        });
    </script>
</body>
</html>