<?php
require_once 'config/database.php';

$categorySlug = isset($_GET['slug']) ? $_GET['slug'] : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

$category = null;
$images = [];
$totalImages = 0;

try {
    $db = Database::getInstance();
    
    // Get category information
    $catSql = "SELECT * FROM categories WHERE slug = ?";
    $catStmt = $db->query($catSql, [$categorySlug]);
    $category = $catStmt->fetch();
    
    if (!$category) {
        http_response_code(404);
        die('Category not found');
    }
    
    // Get images in this category
    $whereConditions = ['is_public = 1', 'category = ?'];
    $params = [$categorySlug];
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM images WHERE " . implode(' AND ', $whereConditions);
    $countStmt = $db->query($countSql, $params);
    $totalImages = $countStmt->fetch()['total'];
    
    // Get images
    $imagesSql = "SELECT i.*, u.name as user_name, u.avatar as user_avatar,
                         (SELECT COUNT(*) FROM likes WHERE image_id = i.id) as likes_count,
                         (SELECT COUNT(*) FROM comments WHERE image_id = i.id) as comments_count
                  FROM images i 
                  LEFT JOIN users u ON i.user_id = u.id 
                  WHERE " . implode(' AND ', $whereConditions) . "
                  ORDER BY i.created_at DESC 
                  LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $imagesStmt = $db->query($imagesSql, $params);
    $images = $imagesStmt->fetchAll();
    
} catch (Exception $e) {
    $error = 'Ошибка загрузки категории: ' . $e->getMessage();
}

// Helper function for file size formatting
function formatFileSize($bytes) {
    if ($bytes === 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category['name']); ?> - PhotoShare</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .category-page {
            padding: 120px 0 80px;
            min-height: 100vh;
            background: var(--bg-secondary);
        }
        
        .category-header {
            text-align: center;
            margin-bottom: 3rem;
            background: white;
            padding: 3rem 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            margin: 0 1rem 3rem;
        }
        
        .category-title {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .category-description {
            font-size: 1.125rem;
            color: var(--text-secondary);
            margin-bottom: 1rem;
        }
        
        .category-stats {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: var(--text-light);
        }
        
        .category-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .content-title {
            font-size: 1.5rem;
            color: var(--text-primary);
        }
        
        .sort-options {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .sort-select {
            padding: 0.5rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            background: white;
            font-size: 0.875rem;
        }
        
        .images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        
        .no-images {
            text-align: center;
            padding: 3rem;
            color: var(--text-secondary);
        }
        
        .no-images i {
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
            text-decoration: none;
            color: var(--text-primary);
        }
        
        .page-btn:hover,
        .page-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
            padding: 0 1rem;
            color: var(--text-secondary);
        }
        
        .breadcrumb a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .category-header {
                margin: 0 0.5rem 2rem;
                padding: 2rem 1rem;
            }
            
            .category-title {
                font-size: 2rem;
            }
            
            .category-stats {
                gap: 1rem;
            }
            
            .content-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .sort-options {
                justify-content: center;
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

    <section class="category-page">
        <div class="container">
            <div class="breadcrumb">
                <a href="/">Главная</a>
                <i class="fas fa-chevron-right"></i>
                <a href="/#gallery">Галерея</a>
                <i class="fas fa-chevron-right"></i>
                <span><?php echo htmlspecialchars($category['name']); ?></span>
            </div>

            <div class="category-header">
                <h1 class="category-title"><?php echo htmlspecialchars($category['name']); ?></h1>
                <p class="category-description"><?php echo htmlspecialchars($category['description']); ?></p>
                
                <div class="category-stats">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo number_format($totalImages); ?></span>
                        <span class="stat-label">Изображений</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo number_format($totalImages * 2.5); ?></span>
                        <span class="stat-label">Просмотров</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo number_format($totalImages * 0.8); ?></span>
                        <span class="stat-label">Скачиваний</span>
                    </div>
                </div>
            </div>

            <div class="category-content">
                <div class="content-header">
                    <h2 class="content-title">Изображения в категории "<?php echo htmlspecialchars($category['name']); ?>"</h2>
                    
                    <div class="sort-options">
                        <label for="sort">Сортировка:</label>
                        <select id="sort" class="sort-select" onchange="changeSort(this.value)">
                            <option value="newest">Сначала новые</option>
                            <option value="oldest">Сначала старые</option>
                            <option value="popular">По популярности</option>
                            <option value="name">По названию</option>
                        </select>
                    </div>
                </div>

                <?php if (empty($images)): ?>
                    <div class="no-images">
                        <i class="fas fa-images"></i>
                        <h3>В этой категории пока нет изображений</h3>
                        <p>Будьте первым, кто загрузит фотографию в эту категорию!</p>
                        <a href="/#upload" class="btn btn-primary" style="margin-top: 1rem;">
                            <i class="fas fa-cloud-upload-alt"></i>
                            Загрузить изображение
                        </a>
                    </div>
                <?php else: ?>
                    <div class="images-grid">
                        <?php foreach ($images as $image): ?>
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
                                        <span><?php echo formatFileSize($image['file_size']); ?></span>
                                    </div>
                                    <div class="gallery-item-meta">
                                        <span>Автор: <?php echo htmlspecialchars($image['user_name'] ?? 'Anonymous'); ?></span>
                                    </div>
                                    <div class="gallery-item-meta">
                                        <span><i class="fas fa-eye"></i> <?php echo number_format($image['views']); ?></span>
                                        <span><i class="fas fa-download"></i> <?php echo number_format($image['downloads']); ?></span>
                                        <span><i class="fas fa-heart"></i> <?php echo number_format($image['likes_count']); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($totalImages > $limit): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?slug=<?php echo urlencode($categorySlug); ?>&page=<?php echo $page - 1; ?>" 
                                   class="page-btn">
                                    <i class="fas fa-chevron-left"></i> Назад
                                </a>
                            <?php endif; ?>
                            
                            <span class="page-btn active">
                                Страница <?php echo $page; ?> из <?php echo ceil($totalImages / $limit); ?>
                            </span>
                            
                            <?php if ($page < ceil($totalImages / $limit)): ?>
                                <a href="?slug=<?php echo urlencode($categorySlug); ?>&page=<?php echo $page + 1; ?>" 
                                   class="page-btn">
                                    Вперед <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script>
        function changeSort(sortType) {
            // TODO: Implement sorting functionality
            console.log('Sort changed to:', sortType);
        }
        
        // Add click events to gallery items
        document.querySelectorAll('.gallery-item').forEach(item => {
            item.addEventListener('click', function() {
                // TODO: Open image in full view
                console.log('Image clicked');
            });
        });
        
        // Add hover effects
        document.querySelectorAll('.gallery-item').forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>